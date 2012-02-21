<?php

define('IN_EX', true);
include('../includes/common.php');

$user->session_start();

$exe = request_var('exencion', 0);
if (!$exe)
{
	die('<ul></ul>');
}

$sql = "SELECT *
	FROM _constancia
	WHERE c_exe LIKE '" . (int) $exe . "%'
	ORDER BY c_exe";
$result = $db->sql_query($sql);

$str = '';
while ($row = $db->sql_fetchrow($result))
{
	$str .= '<li>' . $row['c_exe'] . '</li>';
}
$db->sql_freeresult($result);

echo '<ul>' . $str . '</ul>';
	
?>