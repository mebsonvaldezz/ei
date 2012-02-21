<?php

define('IN_EX', true);
include('../includes/common.php');

$user->session_start();
$user->plogin();
$user->session_auth();
$user->allow_access('edit');

//
$error = array();
$submit1 = (isset($_POST['submit1'])) ? true : false;
$submit2 = (isset($_POST['submit2'])) ? true : false;
$screen = request_var('screen', '');

$_submenu = array('p' => 'Proveedores', 'c' => 'Constancias', 'f' => 'Facturas');
if (!isset($_submenu[$screen])) {
	edit_layout();
}

if ($submit1 || $submit2) {
	//
	// Submit 1 && 2
	//
	$search = request_var('search', '');
	
	if ($search !== '')
	{
		switch ($screen)
		{
			case 'p':
				$table = array('_prov', 'p_nit');
				break;
			case 'c':
				$table = array('_constancia', 'c_exe');
				break;
			case 'f':
				$table = array('_factura', 'f_fact');
				break;
		}
		
		$sql = "SELECT *
			FROM ??
			WHERE ?? = '??'";
		$sql = sql_filter($sql, $table[0], $table[1], $search);
		
		switch ($screen) {
			case 'f':
				$search2 = request_var('search2', '');
				$search3 = request_var('search3', '');
				$sql .= sql_filter(' AND f_exe = ? AND f_serie = ?', $search2, $search3);
				break;
		}
		
		if (!$table_data = sql_fieldrow($sql)) {
			$error[] = 'La b&uacute;squeda no produjo resultados.';
		}
		
		//
		if (!sizeof($error)) {
			$exc = $user->private_data();
			if (exc === NULL) {
				$error[] = 'Error en permisos de usuario.';
			} else if (is_array($exc)) {
				$temp_exe = ($screen == 'c') ? $search : $search2;
				
				$sql = 'SELECT *
					FROM _log
					WHERE log_exe = ?
						AND log_action = ?
						AND log_user_id IN (??)';
				if (!$row = sql_fieldrow(sql_filter($sql, $temp_exe, 'i', implode(',', $exc)))) {
					$error[] = 'No tiene permisos de editar esta exenci&oacute;n.';
				}
			}
		}
	} else {
		$error[] = 'Debe ingresar un valor para la b&uacute;squeda.';
	}
	
	if (!sizeof($error)) {
		switch ($screen) {
			case 'p':
				break;
			case 'c':
				list($day, $month, $year) = explode('.', date('d.m.Y', $table_data['c_date']));
				break;
			case 'f':
				list($day, $month, $year) = explode('.', date('d.m.Y', $table_data['f_date']));
				break;
		}
	} else {
		edit_layout($error);
	}
	
	//
	// Submit 2
	if ($submit2) {
		switch ($screen) {
			case 'p':
				$nit = request_var('nit', '');
				$name = request_var('name', '');
				
				if (empty($nit)) {
					$error[] = 'Debe completar el NIT.';
				}
				
				if (empty($name)) {
					$error[] = 'Debe completar el Proveedor.';
				}
				break;
			case 'c':
				$exencion = request_var('exencion', 0);
				$nit = request_var('nit', '');
				$desc = request_var('desc', '');
				
				$day = request_var('day', 0);
				$month = request_var('month', 0);
				$year = request_var('year', 0);
				
				if (!$exencion) {
					$error[] = 'Debe completar la exenci&oacute;n.';
				}
				
				if (empty($nit)) {
					$error[] = 'Debe completar el NIT.';
				} else {
					$sql = 'SELECT *
						FROM _prov
						WHERE p_nit = ?';
					if (!$row = sql_fieldrow(sql_filter($sql, $nit))) {
						$error[] = 'El NIT ingresado no existe, por favor verificar en la pantalla de Proveedores.';
					}
				}
				
				if (empty($desc)) {
					$error[] = 'Debe completar la descripci&oacute;n.';
				}
				
				if (!$day || !$month || !$year) {
					$error[] = 'Debe completar correctamente la fecha.';
				}
				break;
			case 'f':
				$exencion = request_var('exencion', 0);
				$serie = request_var('serie', '');
				$factura = request_var('factura', '');
				$total = request_var('total', 0.00);
				
				$day = request_var('day', 0);
				$month = request_var('month', 0);
				$year = request_var('year', 0);
				
				if (!$exencion) {
					$error[] = 'Debe completar la exenci&oacute;n.';
				}
				
				if (empty($factura)) {
					$error[] = 'Debe completar la Factura.';
				}
				
				if (!$total) {
					$error[] = 'Debe ingresar el total.';
				}
				break;
		}
		
		if ($screen != 'p') {
			check_date($error, $month, $day, $year);
		}
		
		if (!sizeof($error)) {
			if ($screen != 'p') {
				$new_date = createdate($month, $day, $year);
			}
			
			switch ($screen) {
				case 'p':
					$update_data = array(
						'p_nit' => $nit,
						'p_name' => $name
					);
					$table = '_prov';
					$sql_where = sql_filer('p_nit = ?', $search);
					
					xlog('pe.' . $search, 0, 0);
					break;
				case 'c':
					$update_data = array(
						'c_exe' => (int) $exencion,
						'c_date' => (int) $new_date,
						'c_nit' => $nit,
						'c_text' => $desc
					);
					$table = '_constancia';
					$sql_where = sql_filter('c_exe = ?', $search);
					
					xlog('e', $search);
					break;
				case 'f':
					$update_data = array(
						'f_serie' => $serie,
						'f_date' => $new_date,
						'f_total' => $total,
						'f_exe' => $exencion,
						'f_fact' => $factura
					);
					$table = '_factura';
					$sql_where = sql_filter('f_exe = ? AND f_fact = ?', $search2, $search);
					
					xlog('e', $search2, $search);
					break;
			}
			
			$sql = 'UPDATE ' . $table . ' SET ' . sql_build('UPDATE', $update_data) . '
				WHERE ' . $sql_where;
			sql_query($sql);
			
			//
			// End update
			page_header();
			submenu();
?>
<div class="vsep-pre"><div class="vsep1">&nbsp;</div></div>
<div class="colorbox darkborder pad10" align="center">
<strong>La informaci&oacute;n fue actualizada.</strong><br /><br /><a class="red bold" href="<?php echo s_link('edit', $screen); ?>">Click para regresar</a>.
</div>
<?php
			page_footer();
		} // IF
	} // submit2
	
	if (($submit1 && !sizeof($error)) || ($submit2 && sizeof($error)))
	{
		page_header();
		submenu();
?>
<div class="vsep-pre"><div class="vsep1">&nbsp;</div></div>
<form action="<?php echo s_link('edit'); ?>" method="post" name="edit">
<div class="colorbox darkborder pad10"><br /><div align="center" class="h2"><?php echo $_submenu[$screen]; ?></div><br /><div class="ie-widthfix">

<?php show_error($error); ?>

<div align="center" class="ie-widthfix">
<table cellpadding="5" cellspacing="0" border="1" bordercolor="#999999" class="table-collapse" align="center">
<?php
		switch ($screen)
		{
			case 'p':
?>
	<tr>
		<td width="75" nowrap>NIT</td>
		<td align="left"><input type="text" name="nit" size="25" value="<?php echo ($nit != '') ? $nit : $table_data['p_nit']; ?>" /></td>
	</tr>
	<tr>
		<td width="75" nowrap>Proveedor</td>
		<td align="left"><input type="text" name="name" size="60" value="<?php echo ($name != '') ? $name : $table_data['p_name']; ?>" /></td>
	</tr>
<?php
				break;
			case 'c':
?>
	<tr>
		<td>Exenci&oacute;n</td>
		<td align="left"><input type="text" name="exencion" value="<?php echo ($exencion) ? $exencion : $table_data['c_exe']; ?>" /></td>
	</tr>
	<tr>
		<td>Fecha</td>
		<td align="left"><?php print_date_select(); ?></td>
	</tr>
	<tr>
		<td>NIT</td>
		<td align="left">
		<input type="text" id="nit" name="nit" size="30" value="<?php echo ($nit) ? $nit : $table_data['c_nit']; ?>" /><br /><br />
		<input type="text" id="prov" name="proveedor" size="54" value="<?php echo $proveedor; ?>" />
		<div id="s_nit" class="autocomplete darkborder bgcolor2"></div>
		<script type="text/javascript">
		<!--
		new Ajax.Autocompleter('nit', 's_nit', call_ajx + 'prov/', {frequency: 0.01, minChars: 5, afterUpdateElement: function hdls(a, b) { match = b.innerHTML.match(/\<span.*?\>(.*?)\<\/span\>/i); $('prov').value = match[1]; $('desc').focus(); }});
		-->
		</script>
		</td>
	</tr>
	<tr>
		<td>Descripci&oacute;n</td>
		<td align="left"><textarea id="desc" name="desc" cols="40" rows="5"><?php echo ($desc != '') ? $desc : $table_data['c_text']; ?></textarea></td>
	</tr>
<?php
				break;
			case 'f':
?>
	<tr>
		<td width="100" nowrap>Exenci&oacute;n</td>
		<td>
		<input type="text" id="exencion" name="exencion" size="25" value="<?php echo ($exencion) ? $exencion : $table_data['f_exe']; ?>" />
		<div id="s_exe" class="autocomplete darkborder bgcolor2"></div>
		<script type="text/javascript">
		<!--
		new Ajax.Autocompleter('exencion', 's_exe', call_ajx + 'exe/', {frequency: 0.01, minChars: 6, afterUpdateElement: function() { $('serie').focus(); }});
		-->
		</script>
		</td>
	</tr>
	<tr>
		<td>Serie</td>
		<td><input type="text" id="serie" name="serie" value="<?php echo ($serie != '') ? $serie : $table_data['f_serie']; ?>" size="30" /></td>
	</tr>
	<tr>
		<td>No. de factura</td>
		<td><input type="text" name="factura" value="<?php echo ($factura != '') ? $factura : $table_data['f_fact']; ?>" size="30" /></td>
	</tr>
	<tr>
		<td>Fecha</td>
		<td>
		<?php print_date_select(); ?>
		</td>
	</tr>
	<tr>
		<td>Total</td>
		<td><input type="text" name="total" value="<?php echo ($total) ? $total : $table_data['f_total']; ?>" size="30" /></td>
	</tr>
<?php
				break;
		}
?>
</table>
</div>
<br /></div><div align="center">
<?php

$h_s2 = ($screen != 'p') ? array('search2' => $search2, 'search3' => $search3) : array();
echo s_hidden(array('screen' => $screen, 'search' => $search) + $h_s2);

?>
<input type="submit" class="submitdata" name="submit2" value="Guardar modificaciones" /></div></div>
</form>

<?php
		page_footer();
	}
}

edit_layout($error);

//
// Functions
//
function submenu()
{
	global $config, $screen, $_submenu;
	
	$page_e = explode('/', requested_url());
	$folder = array_splice($page_e, 1, 1);
	$folder = (!empty($screen)) ? $screen : $folder[0];
	
	$_buildmenu = array();
	foreach ($_submenu as $k => $v)
	{
		$_buildmenu[] = ($k == $folder) ? '<strong class="gray">' . $v . '</strong>' : '<a href="' . s_link('edit', $k) . '">' . $v . '</a>';
	}
?>
<div class="vsep-pre"><div class="vsep1">&nbsp;</div></div>
<div class="subtitle-link-container"><div><?php echo implode(' <span class="soft">|</span> ', $_buildmenu); ?></div></div>
<?php
}

function edit_layout($error = false) {
	global $_submenu, $db, $config, $screen;
	
	page_header();
	submenu();
	
	// Show requested screen
	$call_func = 'e_print_' . $screen;
	if (function_exists($call_func))
	{
?>
<div class="vsep-pre"><div class="vsep1">&nbsp;</div></div>
<form action="<?php echo s_link('edit'); ?>" method="post">
<div class="colorbox darkborder pad10">
<div align="center" class="h2"><?php echo $_submenu[$screen]; ?></div>
<div class="ie-widthfix">
<?php

if ($error !== false && sizeof($error))
{
?>
<a name="error"></a><br />
<div align="center">
<div align="center" class="pad10 red colorbox dsm box ie-widthfix">
<strong>B&uacute;squeda</strong><br /><br /><?php echo implode('<br />', $error); ?></div>
</div>
<?php
}
?>
<br />
<table cellpadding="5" cellspacing="0" border="1" bordercolor="#999999" class="table-collapse" align="center">
<?php $call_func(); ?>
</table>
<div align="center">
<?php echo s_hidden(array('screen' => $screen)); ?>
<br />
<input type="submit" class="submitdata" name="submit1" value="Realizar consulta" />
</div>
</div>
</div>
</form>
<?php
	}
	
	page_footer();
}

function e_print_p() {
?>
	<tr>
		<td width="75" nowrap>NIT</td>
		<td><input type="text" name="search" value="" /></td>
	</tr>
<?php
}

function e_print_c() {
?>
	<tr>
		<td width="75" nowrap>Exenci&oacute;n</td>
		<td><input type="text" name="search" value="" /></td>
	</tr>
<?php
}

function e_print_f() {
?>
	<tr>
		<td width="75" nowrap>Exenci&oacute;n</td>
		<td><input type="text" name="search2" value="" /></td>
	</tr>
	<tr>
		<td width="75" nowrap>Factura</td>
		<td><input type="text" name="search" value="" /></td>
	</tr>
	<tr>
		<td width="75" nowrap>Serie</td>
		<td><input type="text" name="search3" value="" /></td>
	</tr>
<?php
}

page_header();
page_footer();

?>