<?php

define('IN_EX', true);
include('../includes/common.php');

$user->session_start();
$user->plogin();
$user->session_auth();
$user->allow_access('delete');

//
$error = array();
$fid = request_var('fid', 0);

if ($fid)
{
	$sql = 'SELECT *
		FROM _factura
		WHERE f_id = ' . (int) $fid;
	$result = $db->sql_query($sql);
	
	if (!$f_data = $db->sql_fetchrow($result))
	{
		$error[] = 'La factura no existe, por favor verificar.';
	}
	$db->sql_freeresult($result);
	
	if (!sizeof($error))
	{
		$sql = 'DELETE FROM _factura
			WHERE f_id = ' . (int) $fid;
		$db->sql_query($sql);
		
		xlog('f', $f_data['f_exe'], $f_data['f_fact']);
		
		//
		page_header();
?>
<div class="vsep-pre"><div class="vsep1">&nbsp;</div></div>
<div class="colorbox darkborder pad10" align="center">
<strong>La factura fue borrada.</strong><br /><br /><a class="red bold" href="<?php echo s_link('search'); ?>">Click para regresar</a>.
</div>
<?php
		page_footer();
	}
}

exit();

?>