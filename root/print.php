<?php

define('IN_EX', true);
include('../includes/common.php');

$user->session_start();
$user->plogin();
$user->session_auth();

//
$user->allow_access('print');
$print_auth = $user->get_print_auth($user->data['user_id']);

//
set_time_limit(0);

$error = array();
$submit = (isset($_POST['submit'])) ? TRUE : FALSE;
$screen = request_var('screen', 0);

$_submenu = array(
	1 => 'Exenciones',
	2 => 'Revisi&oacute;n',
	3 => 'Reporte de SAT'
);
if (!isset($_submenu[$screen])) {
	$screen = 1;
}

if (!isset($print_auth[$screen]) || !$print_auth[$screen])
{
	print_header();
	
	echo 'Usted no tiene acceso a este modo de impresi&oacute;n.<br /><br /><a href="' . s_link('print') . '">Click para regresar a la p&aacute;gina principal</a>';
	
	print_footer();
}

$exc = $user->private_data();
$private_groups = '';
if (is_array($exc)) {
	$private_groups = sql_filter(' AND user_id IN (??)', implode(',', $exc));
}

$sql = 'SELECT *
	FROM _users
	WHERE user_id <> 1
		AND user_adm = 0' . $private_groups . '
	ORDER BY username';
$result = sql_rowset($sql);
	
$i = 0;
$groups = array();
$groups_legend = array();
foreach ($result as $row) {
	$i++;
	$groups[$i] = array($row['user_id']);
	$groups_legend[$i] = $row['username'];
}

if (!is_array($exc)) {
	$groups[] = array(2, 3, 4, 5, 11, 12, 13, 14);
	$groups[] = array(6, 7, 8);
	$groups[] = array(9, 10, 15);
	$groups_legend[] = 'Grupo 1';
	$groups_legend[] = 'Grupo 2';
	$groups_legend[] = 'Grupo 3';
}

$sgroup = request_var('sgroup', 0);
$tpcs = request_var('tpcs', 0);

if ($submit) {
	switch ($screen) {
		case 1:
			$search = request_var('search', '');
			
			if (empty($search)) {
				$error[] = 'Debe ingresar al menos un valor.';
			}
			break;
		case 2:
		case 3:
			$ary_dates = array('month', 'day', 'year');
			for ($i = 1; $i < 4; $i++) {
				foreach ($ary_dates as $xvar) {
					${$xvar . $i} = request_var($xvar . $i, 0);
				}
			}
			
			if (!$month1 || !$day1 || !$year1) {
				$error[] = 'Debe ingresar la fecha inicial correctamente.';
			}
			
			if (!$month2 || !$day2 || !$year2) {
				$error[] = 'Debe ingresar la fecha final correctamente.';
			}
			
			if (!$month3 || !$day3 || !$year3) {
				$error[] = 'Debe ingresar la fecha del reporte correctamente.';
			}
			
			if (!sizeof($error)) {
				$s_date = gmmktime(6, 0, 0, $month1, $day1, $year1);
				$e_date = gmmktime(6, 0, 0, $month2, $day2, $year2);
				$t_date = gmmktime(6, 0, 0, $month3, $day3, $year3);
				
				if ($s_date > $e_date) {
					$error[] = 'La fecha inicial no puede ser mayor a la fecha final';
				}
				
				if ($t_date < $s_date) {
					$error[] = 'La fecha del reporte no puede ser menor a la fecha inicial.';
				}
			}
			break;
	}
	
	if (!sizeof($error)) {
		//
		// Load PDF class
		//
		include('../includes/pdf/class.ezpdf.php');
		include('../includes/convert.php');
		include('../includes/pdf_aop.php');
		
		switch ($screen) {
			case 1:
				$search = str_replace(' ', '', trim($search));
				
				$sql_rnk_match = array();
				$sql_in_match = array();
				
				$sc_e = explode(',', $search);
				foreach ($sc_e as $sc_i) {
					if (strpos($sc_i, '-')) {
						$hyp_e = explode('-', $sc_i);
						
						if ($hyp_e[0] > $hyp_e[1]) {
							$error[] = 'Un rango de exenciones tiene error, el n&uacute;mero de inicio es mayor al n&uacute;mero final, <strong>' . $hyp_e[0] . ' - ' . $hyp_e[1] . '</strong>';
							break;
						}
						
						$sql_rnk_match[] = '((c_exe >= ' . (int) $hyp_e[0] . ') AND (c_exe <= ' . (int) $hyp_e[1] . '))';
					} else {
						$sc_i = intval($sc_i);
						if ($sc_i) {
							$sql_in_match[] = $sc_i;
						}
					}
				}
				
				if (!sizeof($error) && (sizeof($sql_rnk_match) || sizeof($sql_in_match))) {
					$sql_stm_rnk_match = implode(' OR ', $sql_rnk_match);
					$sql_stm_in_match = implode(',', array_map('intval', $sql_in_match));
					$sql_stm_in_match = ($sql_stm_in_match != '') ? ' ' . (($sql_stm_rnk_match != '') ? 'OR ' : '') . ' c_exe IN (' . $sql_stm_in_match . ')' : '';
					
					$sql = 'SELECT DISTINCT *
						FROM _constancia
						WHERE ' . $sql_stm_rnk_match . $sql_stm_in_match . '
						ORDER BY c_exe';
					$result = sql_rowset($sql);
					
					$data = array();
					foreach ($result as $row) {
						$data['c'][$row['c_exe']] = $row;
					}
					
					if (sizeof($data['c']) && is_array($exc)) {
						$sql_u = 'SELECT *
							FROM _log
							WHERE log_exe = %d
								AND log_action = ?
								AND log_user_id IN (??)';
						$sql_u = sql_filter($sql_u, 'i', implode(',', $exc));
						
						foreach ($data['c'] as $i => $row) {
							$sql = sprintf($sql_u, $i);
							if (!$row = sql_fieldrow($sql)) {
								unset($data['c'][$i]);
							}
						}
					}
					
					if (sizeof($data['c']))
					{
						$sql = 'SELECT *
							FROM _factura
							WHERE f_exe IN (??)
							ORDER BY f_exe';
						$result = sql_rowset(sql_filter($sql, implode(',', array_keys($data['c']))));
						
						foreach ($result as $row) {
							$data['f'][] = $row;
						}
						
						$p_index = array();
						foreach ($data['c'] as $c_data) {
							$p_index[] = $c_data['c_nit'];
						}
						
						$sql = "SELECT *
							FROM _prov
							WHERE p_nit IN ('??')
							ORDER BY p_nit";
						if ($result = sql_rowset(sql_filter($sql, implode("','", $p_index)))) {
							$p_data = array();
							
							foreach ($result as $row) {
								$p_data[$row['p_nit']] = $row['p_name'];
							}
						}
						
						$sql = 'UPDATE _constancia SET c_np = 1
							WHERE c_exe IN (??)';
						sql_query(sql_filter($sql, implode(',', array_keys($data['c']))));
						
						$selected_fontafm = request_var('selected_fontafm', '');
						if (empty($selected_fontafm)) {
							$selected_fontafm = 'verdana';
						}
						
						$d =& new Cezpdf(array(0, 0, 612.00, 396.00));
						$d->selectFont('../includes/pdf/' . $selected_fontafm . '.afm');
						
						$d2 = new Cpdf();
						$aop = new aop();
						
						$aop->import_pdf();
						
						//
						// Fact coords
						//
						$fact_coords = array(
							80 => 'Serie',
							132 => 'Factura',
							230 => 'Fec/Fac',
							310 => 'Total',
							375 => 'Neto',
							455 => 'IVA',
						);
						
						//
						// Do the process
						//
						$cv = new convert();
						
						$int_page = 0;
						foreach ($data['c'] as $c_exe => $c_data) {
							for ($b = 0; $b < $tpcs; $b++) {
								if ($int_page) {
									$aop->pdf->ezNewPage();
								}
								
								parse_nulled($c_data);
								
								//
								// Print objects
								//
								$c_date = 'Guatemala, ' . date('d/m/Y', $c_data['c_date']);
								$c_nit = $c_data['c_nit'];
								$p_name = ($c_data['c_nit']) ? $p_data[$c_nit] : $c_data['c_text'];
								$p_name = html_entity_decode($p_name);
								
								if ($c_nit != '') {
									if (!strpos($c_nit, '-')) {
										$c_nit = substr($c_nit, 0, strlen($c_nit) - 1) . '' . '-' . substr($c_nit, -1, 1);
									}
								}
								
								$aop->pdf->addTextWrap(480, 325, $d->getTextWidth(10, $c_exe)+100, 10, $c_exe);
								$aop->pdf->addTextWrap(130, 296, $d->getTextWidth(10, $c_date), 10, $c_date);
								
								$aop->blocks(300, 0, $p_name, '', 111, 10, 130, 0, 11);
								
								$aop->pdf->addTextWrap(460, 272, $d->getTextWidth(10, $c_nit) + 100, 10, $c_nit);
								
								//
								// Facturas
								//
								foreach ($fact_coords as $coord => $t_text) {
									$aop->pdf->addTextWrap($coord, 139, $d->getTextWidth(9, $t_text) + 10, 9, $t_text);
								}
								
								$total_sum = $neto_sum = $iva_sum = $coord_sum = $fi = 0;
								$nulled = array();
								$cypm = 0;
								
								foreach ($data['f'] as $f_data) {
									if ($c_exe != $f_data['f_exe']) {
										continue;
									}
									
									parse_nulled_f($c_data['c_null'], $f_data);
									
									if ($c_data['c_null']) {
										if (isset($nulled[$c_data['c_exe']])) {
											continue;
										}
										
										$nulled[$c_data['c_exe']] = true;
									}
									
									$total = $f_data['f_total'];
									$neto = $total / 1.12;
									$iva = $neto * 0.12;
									
									$total_sum += round($total, 2);
									$neto_sum += round($neto, 2);
									$iva_sum += round($iva, 2);
									
									$print_f = array(
										80 => $f_data['f_serie'],
										132 => $f_data['f_fact'],
										230 => ($f_data['f_date']) ? date('d/m/y', $f_data['f_date']) : '',
										310 => number_format($total, 2),
										375 => number_format($neto, 2),
										455 => number_format($iva, 2)
									);
									
									$coord_sum += ($fi) ? 10 : 0;
									
									foreach ($print_f as $coord => $value) {
										// function addTextWrap($x, $y, $width, $size, $text, $justification = 'left', $angle = 0, $test = 0)
										// function text($x, $y, $text, $size, $align = '')
										// function right($width, $size, $text)
										
										// $aop->pdf->addTextWrap($coord, 125 - $coord_sum, $d->getTextWidth(8, $value), 8, $value);
										
										if ($coord == 80 || $coord == 230) {
											$aop->pdf->addTextWrap($coord, 125 - $coord_sum, $d->getTextWidth(8, $value), 8, $value);
										} else {
											$aop->text($coord + $aop->right(40, 8, $value), $d->cy(125) - $coord_sum + (20 * $cypm), $value, 8);
										}
									}
									$cypm++;
									$fi++;
								}
								
								$c_letters = '***' . ucwords($cv->cv($iva_sum)) . '***';
								$temp_iva_sum = number_format($iva_sum, 2);
								$iva_sum = '***' . $temp_iva_sum . '***';
								
								$total_sum = number_format($total_sum, 2);
								$neto_sum = number_format($neto_sum, 2);
								
								$aop->pdf->addTextWrap(130, 241, $d->getTextWidth(10, $c_letters)+500, 10, $c_letters);
								$aop->pdf->addTextWrap(460, 296, $d->getTextWidth(10, $iva_sum)+100, 10, $iva_sum);
								
								$aop->pdf->line(300, 125 - $coord_sum - 7, 500, 125 - $coord_sum - 7);
								
								//
								$aop->text(310 + $aop->right(40, 8, $total_sum), $d->cy(125 - $coord_sum - 20), $total_sum, 8);
								$aop->text(375 + $aop->right(40, 8, $neto_sum), $d->cy(125 - $coord_sum - 20), $neto_sum, 8);
								$aop->text(455 + $aop->right(40, 8, $temp_iva_sum), $d->cy(125 - $coord_sum - 20), $temp_iva_sum, 8);
								//
								
								$aop->pdf->line(300, 125 - $coord_sum - 27, 500, 125 - $coord_sum - 27);
								$aop->pdf->line(300, 125 - $coord_sum - 29, 500, 125 - $coord_sum - 29);
								
								$int_page++;
							} // FOR: USER_PRINT_COPIES
						}
						
						$aop->export_pdf();
						
						//
						// Output to browser
						//
						$d->ezOutput();
						$d->stream();
						die();
					} else {
						$error[] = 'No se encontr&oacute; ninguna exenci&oacute;n con la b&uacute;squeda especificada.';
					}
				}
				break;
			case 2:
				$data = array();
				$sql_c_group = '';
				if ($sgroup)
				{
					$sql = 'SELECT *
						FROM _log
						WHERE log_user_id IN (??)
							AND log_action = ?
						ORDER BY log_exe';
					$result = sql_rowset(sql_filter($sql, implode(',', $groups[$sgroup]), 'i'));
					
					foreach ($result as $row) {
						$sql_c_group .= (($sql_c_group != '') ? ',' : '') . $row['log_exe'];
					}
					
					if (empty($sql_c_group)) {
						$error[] = 'No se encontr&oacute; ninguna exenci&oacute;n con la b&uacute;squeda especificada';
					}
					
					$sql_c_group = sql_filter(' AND c.c_exe IN (??)', $sql_c_group);
				}
				
				if (!sizeof($error)) {
					$sql = 'SELECT p.*, c.*, f.*
						FROM _prov p, _constancia c, _factura f
						WHERE p.p_nit = c.c_nit
							AND c.c_exe = f.f_exe
							AND c.c_date >= ?
							AND c.c_date <= ?' .  
							$sql_c_group . '
						ORDER BY c.c_exe';
					$result = sql_rowset(sql_filter($sql, $s_data, $e_data));
					
					foreach ($result as $row) {
						$data[] = $row;
					}
					
					if (sizeof($data) && is_array($exc)) {
						$sql_u = 'SELECT *
							FROM _log
							WHERE log_exe = %d
								AND log_action = ?
								AND log_user_id IN (??)';
						$sql_u = sql_filter($sql_u, 'i', implode(',', $exc));
						
						foreach ($data as $i => $row) {
							$sql = sprintf($sql_u, $row['c_exe']);
							if (!$row = sql_fieldrow($sql)) {
								unset($data[$i]);
							}
						}
					}
				}
				
				if (sizeof($data)) {
					$d =& new Cezpdf('LETTER');
					$d->selectFont('../includes/pdf/verdana.afm');
					
					$d2 = new Cpdf();
					$aop = new aop();
					
					$aop->import_pdf();
					
					$total_rows = sizeof($data);
					$total = 0;
					$total_fp = 0;
					$line_height = 11;
					
					$first_page = true;
					$new_page = true;
					$create_page = false;
					
					$nulled = array();
					
					//
					// Process all data
					//
					foreach ($data as $i => $row) {
						if ($new_page) {
							if ($create_page) {
								$aop->pdf->ezNewPage();
							}
							
							$print_height = 25;
							
							$aop->header_preview($first_page, $print_height);
							
							$new_page = false;
							$create_page = false;
							
							if ($first_page)
							{
								$first_page = false;
							}
						}
						
						//
						// Blank all data if null
						//
						parse_nulled($row, true);
						
						if ($row['c_null']) {
							if (isset($nulled[$row['c_exe']])) {
								continue;
							}
							
							$nulled[$row['c_exe']] = true;
						}
						
						if ($row['c_null']) {
							$row['p_name'] = $row['c_text'];
						}
						
						if ($row['f_serie'] === '0') {
							$row['f_serie'] = '';
						}
						
						//
						// Print objects
						//
						$aop->text(22 + $aop->right(62, 6, $row['c_exe']), $print_height, $row['c_exe'], 6);
						$aop->text(97, $print_height, date('d/m/y', $row['c_date']), 6);
						$aop->text(148 + $aop->right(50, 6, $row['p_nit']), $print_height, $row['p_nit'], 6);
						
						if ($row['f_serie']) {
							$aop->text(345, $print_height, $row['f_serie'], 6);
						}
						
						$print_height_add = $aop->blocks(125, 125, $row['p_name'], $row['c_text'], $print_height, 6, 205, 400, $line_height);
						
						$aop->text(346 + $aop->right(50, 6, $row['f_fact']), $print_height, $row['f_fact'], 6);
						$aop->text(525 + $aop->right(50, 6, $row['f_total']), $print_height, $row['f_total'], 6);
						
						$print_height += $line_height * $print_height_add;
						
						if ($print_height > 770) {
							$new_page = true;
							$create_page = true;
						}
					}
					
					$aop->export_pdf();
					
					//
					// Output to browser
					//
					$d->ezOutput();
					$d->stream();
					die();
				}
				break;
			case 3:
				$sql = 'SELECT p.*, c.*, f.*
					FROM _prov p, _constancia c, _factura f
					WHERE p.p_nit = c.c_nit
						AND c.c_exe = f.f_exe
						AND c.c_date >= ?
						AND c.c_date <= ?
					ORDER BY c.c_exe';
				if ($data = sql_rowset(sql_filter($sql, $s_date, $e_date))) {
					$d =& new Cezpdf('LETTER');
					$d->selectFont('../includes/pdf/verdana.afm');
					
					$d2 = new Cpdf();
					$aop = new aop();
					
					$aop->import_pdf();
					
					$total_rows = sizeof($data);
					$total = 0;
					$total_fp = 0;
					$line_height = 11;
					
					$new_page = true;
					$create_page = false;
					$page_created = true;
					
					$nulled = array();
					
					//
					// Process all data
					//
					foreach ($data as $i => $row) {
						if ($row['c_null']) {
							if (isset($nulled[$row['c_exe']])) {
								continue;
							}
							
							$nulled[$row['c_exe']] = true;
						}
						
						if ($new_page) {
							if ($create_page) {
								$aop->pdf->ezNewPage();
							}
							
							$print_height = 295;
							
							if ($total_fp) {
								$write_total_fp = '<b>VIENEN</b>';
								$new_total_fp = '<b>' . number_format($total_fp, 2) . '</b>';
								
								$aop->text(490 + $aop->right($aop->pdf->getTextWidth(6, $write_total_fp), 6, $write_total_fp), $print_height, $write_total_fp, 6);
								$aop->text(529 + $aop->right(50, 7, $new_total_fp), $print_height, $new_total_fp, 7);
								
								$print_height += $line_height;
							}
							
							$aop->header();
							
							$page_created = true;
							$new_page = false;
							$create_page = false;
						}
						
						//
						// Blank all data if null
						//
						parse_nulled($row, true);
						
						if ($row['c_null']) {
							$row['p_name'] = $row['c_text'];
						}
						
						$total_fp += $row['f_total'];
						$row['f_total'] = number_format($row['f_total'], 2);
						
						if ($row['f_serie'] === '0') {
							$row['f_serie'] = '';
						}
						
						//
						// Print objects
						//
						$aop->text(22 + $aop->right(62, 6, $row['c_exe']), $print_height, $row['c_exe'], 6);
						$aop->text(97, $print_height, date('d/m/y', $row['c_date']), 6);
						$aop->text(148 + $aop->right(50, 6, $row['p_nit']), $print_height, $row['p_nit'], 6);
						
						if ($row['f_serie']) {
							$aop->text(345, $print_height, $row['f_serie'], 6);
						}
						
						$print_height_add = $aop->blocks(125, 125, $row['p_name'], $row['c_text'], $print_height, 6, 205, 400, $line_height);
						
						$aop->text(346 + $aop->right(50, 6, $row['f_fact']), $print_height, $row['f_fact'], 6);
						$aop->text(529 + $aop->right(50, 6, $row['f_total']), $print_height, $row['f_total'], 6);
						
						$print_height += $line_height * $print_height_add;
						
						if ($print_height > 625 || $i + 1 == $total_rows) {
							if ($i + 1 < $total_rows) {
								$write_total_fp = 'VAN';
								$aop->text(482 + $aop->right($aop->pdf->getTextWidth(6, $write_total_fp), 6, $write_total_fp), 636, $write_total_fp, 6);
							}
							
							$new_total_fp = number_format($total_fp, 2);
							$aop->text(507 + $aop->right(70, 7, $new_total_fp), 645, $new_total_fp, 7);
							
							if ($print_height > 625) {
								$new_page = true;
								$create_page = true;
							}
						}
					}
					
					$aop->export_pdf();
					
					//
					// Output to browser
					//
					$d->ezOutput();
					$d->stream();
					die();
				}
				break;
		}
	}
}

print_layout($error);

//
// Functions
//
function print_layout($error = array()) {
	global $_submenu, $print_auth, $db, $user, $config, $screen;
	
	page_header();
	
	$page_e = explode('/', requested_url());
	$folder = array_splice($page_e, 1, 1);
	$folder = (!empty($screen)) ? $screen : $folder[0];
	
	$_buildmenu = array();
	foreach ($_submenu as $k => $v) {
		if (!$print_auth[$k]) {
			continue;
		}
		
		$_buildmenu[] = ($k == $folder) ? '<strong class="gray">' . $v . '</strong>' : '<a href="' . s_link('print', $k) . '">' . $v . '</a>';
	}
?>
<div class="vsep-pre"><div class="vsep1">&nbsp;</div></div>
<div class="subtitle-link-container"><div><?php echo implode(' <span class="soft">|</span> ', $_buildmenu); ?></div></div>
<?php
	
	// Show requested screen
	$call_func = 's_print_' . $screen;
	if (function_exists($call_func))
	{
?>
<div class="vsep-pre"><div class="vsep1">&nbsp;</div></div>
<form action="<?php echo s_link('print'); ?>" method="post">
<div class="colorbox darkborder pad10">
<div align="center" class="h2"><?php echo $_submenu[$screen]; ?></div>
<div class="ie-widthfix">
<?php show_error($error); ?>
<br />
<table cellpadding="5" cellspacing="0" border="1" bordercolor="#999999" class="table-collapse" align="center">
<?php $call_func(); ?>
</table>
<div align="center">
<?php echo s_hidden(array('screen' => $screen)); ?>
<br />
<input type="submit" class="submitdata" name="submit" value="Realizar consulta" />
</div>
</div>
</div>
</form>
<?php
	}
	
	page_footer();
}

//
// Functions
//
function s_print_1() {
	global $user, $db, $config, $search;
?>
	<tr>
		<td><div>Exenciones</div></td>
		<td><input id="t" type="text" name="search" value="<?php if (!empty($search)) { echo $search; } ?>" size="75" /></td>
	</tr>
	<tr>
		<td>Copias de impresi&oacute;n</td>
		<td><input id="tpcs" type="text" name="tpcs" value="1" size="3" /></td>
	</tr>
	<tr>
		<td>Tipo de letra</td>
		<td><select name="selected_fontafm"><?php
		
		$dir = './../includes/pdf/';
		$fp = @opendir($dir);
		while ($file = readdir($fp)) {
			if (preg_match('#^([a-z0-9\-]+)\.afm$#is', $file, $ps))
			{
				print '<option value="' . $ps[1] . '">' . ucwords($ps[1]) . '</option>';
			}
		}
		@closedir($fp);
		
		?></select></td>
	</tr>
	<tr>
		<td colspan="2">
		Se pueden especificar rangos de impresi&oacute;n, ejemplos:<br /><br />
		1) 1-10 Para imprimir todas las exenciones de la <strong>1</strong> a la <strong>10</strong>.<br />
		2) 1,2,3 Para imprimir &uacute;nicamente las exenciones seleccionadas <strong>1</strong>, <strong>2</strong> y <strong>3</strong>.<br />
		3) 1-10,13,18,25 Combinaci&oacute;n de las dos anteriores, por rangos e independientes, <strong>1</strong> a la <strong>10</strong> y <strong>13</strong>, <strong>18</strong>, <strong>25</strong>
		</td>
	</tr>
<?php
}

function s_print_2() {
	s_print_3(true);
}

function s_print_3($show_groups = false) {
	global $user, $db, $config;
	
	if ($show_groups) {
		global $groups, $groups_legend;
	}
?>
	<tr>
		<td>Fecha Inicio</td>
		<td><?php print_date_select(1, true); ?></td>
	</tr>
	<tr>
		<td>Fecha Final</td>
		<td><?php print_date_select(2, true); ?></td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td>Fecha del reporte</td>
		<td><?php print_date_select(3); ?></td>
	</tr>
<?php
	if ($show_groups)
	{
		?>
		<tr>
			<td>Grupo</td>
			<td><?php build_groups($groups, $groups_legend); ?></td>
		</tr>
		<?php
	}
}

?>