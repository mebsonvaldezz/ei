<?php

define('IN_EX', true);
include('../includes/common.php');

$user->session_start();
$user->plogin();
$user->session_auth();

//
$user->allow_access('insert');

$error = array();
$submit = (isset($_POST['submit'])) ? TRUE : FALSE;
$screen = request_var('screen', '');
$selected = request_var('selected', '');
$return_this = request_var('return_this', 0);
$cwe = request_var('cwe', 0);

$_submenu = array(
	'c' => 'Constancias',
	'f' => 'Facturas',
	'p' => 'Proveedores'
);

if ($submit) {
	if (!isset($_submenu[$screen])) {
		redirect('insert');
	}
	
	$nit = request_var('nit', '');
	$proveedor = request_var('proveedor', '');
	$descripcion = request_var('descripcion', '');
	
	$exencion = request_var('exencion', 0);
	$serie = request_var('serie', '');
	$factura = request_var('factura', '');
	$day = request_var('day', 0);
	$month = request_var('month', 0);
	$year = request_var('year', 0);
	$total = request_var('total', 0.00);
	
	if (!empty($nit))
	{
		$nit = str_replace(array('-', '_', ' '), '', $nit);
	}
	
	switch ($screen) {
		case 'c':
			if (!$exencion) {
				$error[] = 'Debe ingresar el n&uacute;mero de exenci&oacute;n.';
			} else {
				$sql = 'SELECT *
					FROM _constancia
					WHERE c_exe = ?';
				if ($row = sql_fieldrow(sql_filter($sql, $exencion))) {
					$error[] = 'La exencion ingresada ya existe.';
				}
				
				if (!sizeof($error)) {
					if (!$user->data['user_adm']) {
						if ($exencion < $user->data['user_rank_min'] || $exencion > $user->data['user_rank_max']) {
							$error[] = 'No se permite ingresar la exenci&oacute;n, porque no est&aacute; en su rango permitido.';
						}
					}
				}
			}
			
			if (empty($descripcion)) {
				$error[] = 'Debe ingresar la descripci&oacute;n.';
			}
			
			if (empty($nit)) {
				$error[] = 'Debe ingresar el NIT.';
			} else {
				$sql = 'SELECT *
					FROM _prov
					WHERE p_nit = ?';
				if (!$row = sql_fieldrow(sql_filter($sql, $nit))) {
					if (empty($proveedor)) {
						$error[] = 'Debe ingresar el nombre del proveedor.';
					}
				} else {
					if (!sizeof($error)) {
						$proveedor = '';
					}
				}
			}
			
			check_date($error, $month, $day, $year);
			
			if (!sizeof($error))
			{
				// Proveedor
				if (!empty($proveedor))
				{
					$insert_prov = array(
						'p_nit' => $nit,
						'p_name' => strtoupper($proveedor)
					);
					sql_query('INSERT INTO _prov' . sql_build('INSERT', $insert_prov));
					
					xlog('pi.' . $nit, 0, 0);
				}
				
				$new_date = gmmktime(6, 0, 0, $month, $day, $year);
				
				$insert_data = array(
					'c_exe' => $exencion,
					'c_date' => $new_date,
					'c_nit' => $nit,
					'c_text' => $descripcion
				);
				sql_query('INSERT INTO _constancia' . sql_build('INSERT', $insert_data));
				
				xlog('i', $exencion);
			}
			break;
		case 'f':
			if (!$exencion) {
				$error[] = 'Debe ingresar el n&uacute;mero de exenci&oacute;n.';
			} else {
				$sql = 'SELECT *
					FROM _constancia
					WHERE c_exe = ?';
				if (!$row = sql_fieldrow(sql_filter($sql, $exencion))) {
					$error[] = 'La exenci&oacute;n ingresada no existe, por favor verificar.';
				}
			}
			
			if (empty($factura)) {
				$error[] = 'Debe ingresar el n&uacute;mero de factura.';
			} else {
				$sql = 'SELECT p.p_nit, c.*, f.*
					FROM _prov p, _constancia c, _factura f
					WHERE p.p_nit = c.c_nit
						AND c.c_exe = f.f_exe
						AND c.c_null = 0
						AND f.f_fact = ?
						AND f.f_serie = ?
						AND c.c_nit = ?';
				if ($rowq = sql_fieldrow(sql_filter($sql, $factura, $serie, $row['c_nit']))) {
					$error[] = 'No se puede guardar, existe una factura de este proveedor, no se puede duplicar el n&uacute;mero de factura.';
				}
			}
			
			check_date($error, $month, $day, $year);
			
			if (!$total)
			{
				$cpsf = true;
				
				$sql = 'SELECT p.*
					FROM _prov p, _constancia c
					WHERE c.c_exe = ?';
				if ($row = sql_fieldrow(sql_filter($sql, $exencion))) {
					$cpsf = ($row['p_sf']) ? false : true;
				}
				
				if ($cpsf) {
					$error[] = 'Debe ingresar el total.';
				}
			}
			
			if (!sizeof($error)) {
				$new_date = gmmktime(6, 0, 0, $month, $day, $year);
				
				$insert_data = array(
					'f_exe' => (int) $exencion,
					'f_serie' => $serie,
					'f_fact' => $factura,
					'f_date' => $new_date,
					'f_total' => $total
				);
				$sql = 'INSERT INTO _factura' . sql_build('INSERT', $insert_data);
				sql_query($sql);
				
				xlog('i', $exencion, $factura);
			}
			break;
		case 'p':
			if (empty($nit)) {
				$error[] = 'Debe ingregar el n&uacute;mero de NIT.';
			}
			
			if (empty($proveedor)) {
				$error[] = 'Debe ingresar el nombre del proveedor.';
			}
			
			if (!sizeof($error)) {
				$sql = 'SELECT *
					FROM _prov
					WHERE p_nit = ? OR p_name = ?';
				if ($row = sql_fieldrow(sql_filter($sql, $nit, strtoupper($proveedor)))) {
					$error[] = 'El NIT o proveedor ya existe.';
				}
			}
			
			if (!sizeof($error)) {
				$psf = request_var('psf', 0);
				
				$insert_prov = array(
					'p_nit' => $nit,
					'p_name' => strtoupper($proveedor),
					'p_sf' => $psf
				);
				sql_query('INSERT INTO _prov' . sql_build('INSERT', $insert_prov));
				
				xlog('pi.' . $nit, 0, 0);
			}
			break;
	}
	
	if (!sizeof($error)) {
		if ($screen == 'p') {
			redirect(array('insert', 'p'));
		}
		
		if ($return_this != $user->data['user_return_insert']) {
			$sql = 'UPDATE _users SET user_return_insert = ?
				WHERE user_id = ?';
			sql_query(sql_filter($sql, $return_this, $user->data['user_id']));
		}
		
		if ($screen == 'c') {
			$return_this = 1;
		}
		
		switch ($return_this) {
			case 1:
				$redt = array('insert', 'f');
				
				if ($screen == 'c' || $cwe) {
					$redt[] = $exencion;
				}
				
				redirect($redt);
				break;
			default:
				redirect(array('insert', 'c'));
				break;
		}
	}
}

//
// Display forms
//
page_header();

$page_e = explode('/', requested_url());
$folder = array_splice($page_e, 1, 1);
$folder = (!empty($screen)) ? $screen : $folder[0];

$_buildmenu = array();
foreach ($_submenu as $k => $v) {
	$_buildmenu[] = ($k == $folder) ? '<strong class="gray">' . $v . '</strong>' : '<a href="' . s_link('insert', $k) . '">' . $v . '</a>';
}

echo '<div class="vsep-pre"><div class="vsep1">&nbsp;</div></div>';
echo '<div class="subtitle-link-container"><div>' . implode(' <span class="soft">|</span> ', $_buildmenu) . '</div></div>';

//
// Show requested screen
//
$call_func = 'f_print_' . $screen;
if (function_exists($call_func))
{
?>
<div class="vsep-pre"><div class="vsep1">&nbsp;</div></div>
<form action="<?php echo s_link('insert'); ?>#error" method="post" name="insert">
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
<br /><br />
<?php

if ($screen == 'f')
{
?>
Luego de guardar informaci&oacute;n regresar a: <input type="radio" onClick="hideb('cw');" name="return_this" value="0"<?php if (!$user->data['user_return_insert']) { echo ' checked'; } ?> /> Exenciones <input type="radio" onClick="showb('cw');" name="return_this" value="1"<?php if ($user->data['user_return_insert']) { echo ' checked'; } ?> /> Facturas<br /><br />
<div id="cw"<?php if (!$user->data['user_return_insert']) { echo ' style="display: none"'; } ?>>
Continuar ingresando con la misma exenci&oacute;n: <input type="radio" name="cwe" checked value="1" /> Si <input type="radio" name="cwe" value="0" /> no<br /><br />
</div>
<?php
}
?>
<input type="submit" class="submitdata" name="submit" value="Guardar informaci&oacute;n" />
</div>
</div>
</div>
</form>
<?php
}

page_footer();

//
// Functions
//
function f_print_p()
{
	global $db, $config;
	
	foreach (array('nit', 'proveedor') as $var)
	{
		global ${$var};
		if (!isset(${$var}))
		{
			${$var} = '';
		}
	}
?>
	<tr>
		<td width="10" nowrap>NIT</td>
		<td><input type="text" name="nit" value="<?php echo $nit; ?>" size="30" /></td>
	</tr>
	<tr>
		<td>Nombre</td>
		<td><input type="text" name="proveedor" value="<?php echo $proveedor; ?>" size="30" /></td>
	</tr>
	<tr>
		<td colspan="2" align="center">Verificar valor de facturas para este proveedor<br /><input type="radio" name="psf" checked value="0" /> Si <input type="radio" name="psf" value="1" /> no</td>
	</tr>

<?php
}

function f_print_c() {
	global $db, $selected, $config;
	
	foreach (array('exencion', 'nit', 'proveedor', 'descripcion') as $var) {
		global $$var;
		if (!isset($$var)) {
			$$var = '';
		}
	}
	
	if (empty($exencion)) {
		global $user;
		
		$sql = 'SELECT MAX(c.c_exe) AS last
			FROM _constancia c, _log l
			WHERE l.log_exe = c.c_exe
				AND l.log_user_id = ?
				AND l.log_action = ?
			GROUP BY l.log_user_id';
		if ($row = sql_fieldrow(sql_filter($sql, $user->data['user_id'], 'i'))) {
			$exencion = $row['last'] + 1;
			if ($user->data['user_rank_min'] && $user->data['user_rank_max'] && ($exencion < $user->data['user_rank_min'] || $exencion > $user->data['user_rank_max'])) {
				$exencion = '';
			}
		}
	}
	
	if ($nit) {
		$selected = $nit;
	}
	
?>
	<tr>
		<td width="10" nowrap>Exenci&oacute;n</td>
		<td><input type="text" name="exencion" value="<?php echo $exencion; ?>" size="30" /></td>
	</tr>
	<tr>
		<td>Fecha</td>
		<td>
		<?php print_date_select(); ?>
		</td>
	</tr>
	<tr>
		<td valign="top">NIT<br /><br />Proveedor</td>
		<td>
		<input type="text" id="nit" name="nit" size="30" value="<?php echo $nit; ?>" /><br /><br />
		<input type="text" id="prov" name="proveedor" size="75" value="<?php echo $proveedor; ?>" />
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
		<td><input type="text" id="desc" name="descripcion" value="<?php echo $descripcion; ?>" size="75" /></td>
	</tr>
<?php
}

function f_print_f() {
	global $db, $config, $selected;
	
	foreach (array('exencion', 'serie', 'factura', 'total', 'exento') as $var) {
		global $$var;
		
		if (!isset($$var)) {
			$$var = '';
		}
	}
	
	if ($selected) {
		$exencion = $selected;
	}
	
?>
	<tr>
		<td width="100" nowrap>Exenci&oacute;n</td>
		<td><input type="text" id="exencion" name="exencion" size="25" value="<?php echo $exencion; ?>" />
		<div id="s_exe" class="autocomplete darkborder" style="display: none;"></div>
		<script type="text/javascript">
		<!--
		new Ajax.Autocompleter('exencion', 's_exe', call_ajx + 'exe/', {frequency: 0.01, minChars: 6, afterUpdateElement: function() { $('serie').focus(); }});
		-->
		</script>
		</td>
	</tr>
	<tr>
		<td>Serie</td>
		<td><input type="text" id="serie" name="serie" value="<?php echo $serie; ?>" size="30" /></td>
	</tr>
	<tr>
		<td>No. de factura</td>
		<td><input type="text" name="factura" value="<?php echo $factura; ?>" size="30" /></td>
	</tr>
	<tr>
		<td>Fecha</td>
		<td>
		<?php print_date_select(); ?>
		</td>
	</tr>
	<tr>
		<td>Total</td>
		<td><input type="text" name="total" value="<?php echo $total; ?>" size="30" /></td>
	</tr>
<?php
}

?>