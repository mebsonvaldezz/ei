<?php

define('IN_EX', true);
include('../includes/common.php');

$user->session_start();
$user->plogin();
$user->session_auth();

//
$user->allow_access('search');

$error = array();
$submit = (isset($_POST['submit'])) ? TRUE : FALSE;
$screen = request_var('screen', '');
$search_type = request_var('search_type', '');

$_submenu = array(
	'p' => 'Proveedores',
	'c' => 'Constancias',
	'f' => 'Facturas'
);

if (!isset($_submenu[$screen])) {
	search_layout();
}

$default = '';
switch ($screen) {
	case 'p':
		$default = '';
		break;
	case 'c':
		if ($search_type == 'desc') {
			$default = '';
		} else {
			$default = 0;
		}
		break;
	case 'f':
		$default = '';
		break;
}

$search = request_var('search', $default);

if ($search_type == 'date') {
	$month = request_var('month', 0);
	$day = request_var('day', 0);
	$year = request_var('year', 0);
	$search = gmmktime(6, 0, 0, ($month + 1), $day, $year);
	
	$month2 = request_var('month2', 0);
	$day2 = request_var('day2', 0);
	$year2 = request_var('year2', 0);
	$search2 = gmmktime(6, 0, 0, ($month2 + 1), $day2, $year2);
}

if ($submit && (empty($search) && (!$month || !$day || !$year))) {
	$error[] = 'Debe ingresar un valor para la b&uacute;squeda.';
}

if ($submit && !sizeof($error)) {
	$p_data = $c_data = $f_data = array();
	
	switch ($screen) {
		case 'p':
			if ($search == '*') {
				/*
				REMOVED FEATURE: Show all information stored with * char.
				
				$sql = 'SELECT *
					FROM _prov
					ORDER BY p_name';*/
			} else {
				$sql = "SELECT *
					FROM _prov
					WHERE p_nit LIKE '??%'
					ORDER BY p_name";
				$sql = sql_filter($sql, $search);
			}
			$p_data = sql_rowset($sql);
			
			if (sizeof($p_data))
			{
				$nit_values = array();
				foreach ($p_data as $item) {
					$nit_values[] = $item['p_nit'];
				}
				
				$sql = 'SELECT *
					FROM _constancia
					WHERE c_nit IN (\'' . implode('\',\'', $nit_values) . '\')
					ORDER BY c_exe';
				$c_data = sql_rowset($sql);
				
				if (sizeof($c_data))
				{
					$exe_values = array();
					foreach ($c_data as $item) {
						$exe_values[] = $item['c_exe'];
					}
					
					$sql = 'SELECT *
						FROM _factura
						WHERE f_exe IN (\'' . implode('\',\'', $exe_values) . '\')
						ORDER BY f_exe';
					$f_data = sql_rowset($sql);
				}
			}
			break;
		case 'c':
			switch ($search_type)
			{
				case 'date':
					$search_field = 'c_date';
					$sql_where = sql_filter(' >= ? AND ?? <= ?', $search, $search_field, $search2);
					break;
				case 'nit':
					$search_field = 'c_nit';
					$sql_where = sql_filter(' = ?', $search);
					break;
				case 'desc':
					$search_field = 'c_text';
					$sql_where = sql_filter(" LIKE '%??%'", $search);
					break;
				default:
					$search_field = 'c_exe';
					$sql_where = sql_filter(' = ?', $search);
					break;
			}
			
			$sql = 'SELECT *
				FROM _constancia
				WHERE ' . $search_field . $sql_where . '
				ORDER BY c_exe';
			$c_data = sql_rowset($sql);
			
			if (sizeof($c_data)) {
				$nit_values = array();
				foreach ($c_data as $item) {
					$nit_values[] = $item['c_nit'];
				}
				
				$sql = 'SELECT *
					FROM _prov
					WHERE p_nit IN (\'' . implode('\',\'', $nit_values) . '\')
					ORDER BY p_nit';
				$p_data = sql_rowset($sql);
				
				if (sizeof($p_data))
				{
					$exe_values = array();
					foreach ($c_data as $item)
					{
						$exe_values[] = $item['c_exe'];
					}
					
					$sql = 'SELECT *
						FROM _factura
						WHERE f_exe IN (\'' . implode('\',\'', $exe_values) . '\')
						ORDER BY f_exe';
					$f_data = sql_rowset($sql);
				}
			}
			break;
		case 'f':
			switch ($search_type)
			{
				case 'date':
					$search_field = 'f_date';
					$sql_where = sql_filter('>= ? AND ?? <= ?', $search, $search_field, $search2);
					break;
				case 'fact':
					$search_field = 'f_fact';
					$sql_where = sql_filter(' = ?', $search);
					break;
				default:
					$search_field = 'f_exe';
					$sql_where = sql_filter(' = ?', $search);
					break;
			}
			
			$sql = 'SELECT *
				FROM _factura
				WHERE ' . $search_field . $sql_where . '
				ORDER BY f_exe';
			$f_data = sql_rowset($sql);
			
			if (sizeof($f_data)) {
				$exe_values = array();
				foreach ($f_data as $item) {
					$exe_values[] = $item['f_exe'];
				}
				
				$sql = 'SELECT *
					FROM _constancia
					WHERE c_exe IN (\'' . implode('\',\'', $exe_values) . '\')
					ORDER BY c_exe';
				$c_data = sql_rowset($sql);
				
				if (sizeof($c_data)) {
					$nit_values = array();
					foreach ($c_data as $item) {
						$nit_values[] = $item['c_nit'];
					}
					
					$sql = 'SELECT *
						FROM _prov
						WHERE p_nit IN (\'' . implode('\',\'', $nit_values) . '\')
						ORDER BY p_nit';
					$p_data = sql_rowset($sql);
				}
			}
			break;
	}
	
	if (!sizeof($p_data) && sizeof($c_data)) {
		$p_data = $c_data;
		$c_data = array();
	}
	
	$x_data = array();
	foreach ($p_data as $i => $first) {
		if (!sizeof($c_data)) {
			$x_data[] = $first;
		}
		
		foreach ($c_data as $k => $second) {
			if ($first['p_nit'] != $second['c_nit']) {
				continue;
			}
	
			if (!sizeof($f_data)) {
				$x_data[] = $first + $second;
			}
			
			foreach ($f_data as $l => $third) {
				if ($third['f_exe'] != $second['c_exe']) {
					continue;
				}
				
				$x_data[] = $first + $second + $third;
			}
		}
	}
	
	if (sizeof($x_data)) {
		$exc = $user->private_data();
		if (exc === NULL) {
			$error[] = 'Error en permisos de usuario.';
		} else if (is_array($exc)) {
			$sql_u = 'SELECT *
				FROM _log
				WHERE log_exe = %d
					AND log_action = ?
					AND log_user_id IN (??)';
			$sql_u = sql_filter($sql, 'i', implode(',', $exc));
			
			foreach ($x_data as $i => $row)
			{
				$sql = sprintf($sql_u, $row['c_exe']);
				if (!$row = sql_fieldrow($sql)) {
					unset($x_data[$i]);
				}
			}
		}
	}
	
	if (sizeof($x_data))
	{
		page_header();
?>
<div>&nbsp;</div>

<?php build_results_table($x_data); ?>

<div align="center">
<br /><br />
<input type="button" class="submitdata" value="Realizar nueva b&uacute;squeda" onClick="redirect('<?php echo s_link('search'); ?>');" />
</div>
<?php
		page_footer();
	} else {
		$error[] = 'La b&uacute;squeda no produjo resultados.';
	}
}

search_layout($error);

//
// Functions
//
function search_layout($error = array()) {
	global $_submenu, $db, $config, $screen;
	
	page_header();
	
	$page_e = explode('/', requested_url());
	$folder = array_splice($page_e, 1, 1);
	$folder = (!empty($screen)) ? $screen : $folder[0];
	
	$_buildmenu = array();
	foreach ($_submenu as $k => $v) {
		$_buildmenu[] = ($k == $folder) ? '<strong class="gray">' . $v . '</strong>' : '<a href="' . s_link('search', $k) . '">' . $v . '</a>';
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
<form action="<?php echo s_link('search'); ?>" method="post">
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
function s_print_c()
{
	global $user, $db, $config, $table_properties;
?>
	<tr>
		<td width="25" nowrap>Tipo de b&uacute;squeda:</td>
		<td><input type="radio" id="s" name="search_type" value="exe" onClick="showb('tb');hideb('dates');setText('t', 'Exencion');" checked />Exenci&oacute;n &nbsp; <input type="radio" name="search_type" value="nit" onClick="showb('tb');hideb('dates');setText('t', 'NIT');" />NIT &nbsp; <input type="radio" name="search_type" value="desc" onClick="showb('tb');hideb('dates');setText('t', 'Descripci�n');" />Descripci&oacute;n &nbsp; <input type="radio" name="search_type" value="date" onClick="showb('dates');hideb('tb');" />Fecha</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tbody id="tb">
	<tr>
		<td><div id="t">Exenci&oacute;n</div></td>
		<td><input type="text" name="search" value="" size="40" /></td>
	</tr>
	</tbody>
	<tbody id="dates" style="display: none;">
	<tr>
		<td>Fecha Inicio</td>
		<td>
		<?php print_date_select(); ?>
		</td>
	</tr>
	<tr>
		<td>Fecha Final</td>
		<td>
		<?php print_date_select(true); ?>
		</td>
	</tr>
	</tbody>
</table>
<?php
}

function s_print_p()
{
	global $user, $db, $config;
?>
	<tr>
		<td width="25" nowrap>Tipo de b&uacute;squeda:</td>
		<td><input type="hidden" name="search_type" value="nit" />NIT</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td><div id="t">NIT</div></td>
		<td>
		<input type="text" id="nit" name="search" size="30" value="" />
		<div id="s_nit" class="autocomplete"></div>
		<script type="text/javascript">
		<!--
		new Ajax.Autocompleter('nit', 's_nit', call_ajx + 'prov/', {frequency: 0.01, minChars: 5});
		-->
		</script>
		</td>
	</tr>
<?php
}

function s_print_f()
{
	global $user, $db, $config;
?>
	<tr>
		<td width="25" nowrap>Tipo de b&uacute;squeda:</td>
		<td><input type="radio" name="search_type" value="exe" onClick="showb('tb');hideb('dates');setText('t', 'Exencion');" checked />Exenci&oacute;n &nbsp; <input type="radio" name="search_type" value="fact" onClick="showb('tb');hideb('dates');setText('t', 'Factura');" />Factura &nbsp; <input type="radio" name="search_type" value="date" onClick="showb('dates');hideb('tb');" />Fecha</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tbody id="tb">
	<tr>
		<td><div id="t">Exencion</div></td>
		<td><input type="text" name="search" value="" /></td>
	</tr>
	</tbody>
	<tbody id="dates" style=" display: none;">
	<tr>
		<td>Fecha Inicio</td>
		<td>
		<?php print_date_select(); ?>
		</td>
	</tr>
	<tr>
		<td>Fecha Final</td>
		<td>
		<?php print_date_select(true); ?>
		</td>
	</tr>
	</tbody>
<?php
}

?>