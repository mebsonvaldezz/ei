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

if ($fid) {
	$sql = 'SELECT *
		FROM _factura
		WHERE f_id = ?';
	
	if (!$f_data = sql_fieldrow(sql_filter($sql, $fid))) {
		$error[] = 'La factura no existe, por favor verificar.';
	}
	
	if (!sizeof($error)) {
		$sql = 'DELETE FROM _factura
			WHERE f_id = ?';
		sql_query(sql_filter($sql, $fid));
		
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