<?php

define('IN_EX', true);
include('../includes/common.php');

$user->session_start();
$user->plogin();
$user->session_auth();

//
$user->allow_access('null');

$error = array();
$submit = (isset($_POST['submit'])) ? true : false;
$confirm = (isset($_POST['confirm'])) ? true : false;

$exe = request_var('exe', 0);

if ($submit || $confirm)
{
	if (!$exe)
	{
		$error[] = 'Debe ingresar un valor para la exenci&oacute;n.';
	}
	else
	{
		$sql = 'SELECT *
			FROM _constancia
			WHERE c_exe = ' . (int) $exe;
		$result = $db->sql_query($sql);
		
		if (!$exe_data = $db->sql_fetchrow($result))
		{
			$error[] = 'La exencion ingresada no existe, por favor verificar.';
		}
		$db->sql_freeresult($result);
		
		$exc = $user->private_data();
		if (exc === NULL)
		{
			$error[] = 'Error en permisos de usuario.';
		}
		else if (is_array($exc))
		{
			$sql = 'SELECT *
				FROM _log
				WHERE log_exe = ' . (int) $exe . '
					AND log_action = \'i\'
					AND log_user_id IN (' . implode(',', $exc) . ')';
			$result = $db->sql_query($sql);
			
			if (!$row = $db->sql_fetchrow($result))
			{
				$error[] = 'No tiene permisos de anular esta exenci&oacute;n.';
			}
			$db->sql_freeresult($result);
		}
	}
	
	if (!sizeof($error) && $exe_data['c_null'])
	{
		$error[] = 'La exenci&oacute;n ya fue anulada.';
	}
	
	if (!sizeof($error) && $confirm)
	{
		$sql = 'UPDATE _constancia
			SET c_null = 1
			WHERE c_exe = ' . (int) $exe;
		$db->sql_query($sql);
		
		xlog('n', $exe);
		
		//
		// End update
		//
		page_header();
?>
<div class="vsep-pre"><div class="vsep1">&nbsp;</div></div>
<div class="colorbox darkborder pad10" align="center">
<strong>La exenci&oacute;n fue anulada.</strong><br /><br /><a class="red bold" href="<?php echo s_link('null'); ?>">Click para regresar</a>.
</div>
<?php
		page_footer();
	}
}

if ($submit && !sizeof($error))
{
	//
	page_header();
	
	$sql = 'SELECT p.*, c.*, f.*
		FROM _prov p, _constancia c, _factura f
		WHERE f.f_exe = ' . (int) $exe . '
			AND f.f_exe = c.c_exe
			AND p.p_nit = c.c_nit
		ORDER BY f.f_fact';
	$result = $db->sql_query($sql);
	
	$data = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$data[] = $row;
	}
	$db->sql_freeresult($result);
	
	echo '<div>&nbsp;</div>';
	
	build_results_table($data);
	
?>
<div align="center">
<form action="<?php echo s_link('null'); ?>" method="post">
<input type="hidden" name="exe" value="<?php echo $exe_data['c_exe']; ?>" />
<div>&nbsp;</div>
<input type="submit" class="submitdata" name="confirm" value="Anular Exenci&oacute;n" />
</form>
</div>
<?php
	//
	page_footer();
}

null_layout($error);

function null_layout($error = array())
{
	global $db, $config;
	global $exe, $search2, $search3;
	
	page_header();
	
	// Show requested screen
?>
<div class="vsep-pre"><div class="vsep1">&nbsp;</div></div>
<form action="<?php echo s_link('null'); ?>" method="post">
<div class="colorbox darkborder pad10">
<div align="center" class="h2"><?php echo $_submenu[$screen]; ?></div>
<div class="ie-widthfix">
<?php show_error(&$error); ?>
<br />
<table cellpadding="5" cellspacing="0" border="1" bordercolor="#999999" class="table-collapse" align="center">
	<tr>
		<td width="75" nowrap>Exenci&oacute;n</td>
		<td><input type="text" name="exe" value="<?php echo $exe; ?>" /></td>
	</tr>
</table>
<div align="center">
<br />
<input type="submit" class="submitdata" name="submit" value="Realizar consulta" />
</div>
</div>
</div>
</form>
<?php
	page_footer();
}
?>