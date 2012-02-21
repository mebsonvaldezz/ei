<?php

define('IN_EX', true);
include('../includes/common.php');

$user->session_start();
$user->plogin();
$user->session_auth();

//
$user->allow_access('log');
$page = request_var('page', 0);

page_header();

$sql = 'SELECT l.*, m.*
	FROM _log l, _users m
	WHERE l.log_user_id = m.user_id
	ORDER BY log_date DESC';
// LIMIT ' . (int) $page . ', 50
$result = $db->sql_query($sql);

if ($row = $db->sql_fetchrow($result))
{
?>
<br /><br />
<table width="100%" cellpadding="5" cellspacing="0" border="1" bordercolor="#999999" class="table-collapse ajax-results" align="center">
	<tr class="subtitle">
		<td align="center" width="1%" nowrap><strong>#</strong></td>
		<td align="center"><strong>Usuario</strong></td>
		<td align="center"><strong>Fecha</strong></td>
		<td align="center"><strong>Acci&oacute;n</strong></td>
		<td align="center"><strong>Exenci&oacute;n</strong></td>
		<td align="center"><strong>Factura</strong></td>
	</tr>
<?php

	$i = 0;
	do
	{
		$f_date = ($row['log_date']) ? date('d M Y h:i:s a', $row['log_date']) : '-';
		
		if (preg_match('#([a-z]+)\.([0-9a-z]+)#is', $row['log_action'], $match))
		{
			$action = $match[1];
			$factura = $match[2];
		}
		else
		{
			$action = $row['log_action'];
			$factura = '-';
		}
		
		switch ($action)
		{
			case 'i':
				$action = 'Ingreso';
				break;
			case 'n':
				$action = 'Anulaci&oacute;n';
				break;
			case 'f':
				$action = 'Borrar Factura';
				break;
			case 'e':
				$action = 'Modificaci&oacute;n';
				break;
			case 'pe':
			case 'pi':
				$action = (($action == 'pe') ? 'Editar' : 'Ingreso') . ' Proveedor';
				$factura = 'NIT > ' . $factura;
				break;
		}
		
?>
	<tr class="<?php echo get_row_class($row, $i); ?>">
		<td width="1%" nowrap><?php echo $i + 1; ?></td>
		<td><?php echo $row['username']; ?></td>
		<td><?php echo $f_date; ?></td>
		<td><?php echo $action; ?></td>
		<td><?php echo $row['log_exe']; ?></td>
		<td><?php echo $factura; ?></td>
	</tr>
<?php
		$i++;
	}
	while ($row = $db->sql_fetchrow($result));
	
?>
</table>
<?php
}
else
{
	echo '<br /><br /><br /><strong>El registro est&aacute; vac&iacute;o.</strong>';
}
$db->sql_freeresult($result);

page_footer();

?>