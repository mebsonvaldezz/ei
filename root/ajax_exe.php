<?php

define('IN_EX', true);
include('../includes/common.php');

$user->session_start();

$exe = request_var('exencion', 0);
if (!$exe) {
	die('<ul></ul>');
}

$sql = "SELECT *
	FROM _constancia
	WHERE c_exe LIKE '??%'
	ORDER BY c_exe";
$result = sql_rowset(sql_filter($sql, $exe));

$str = '';
foreach ($result as $row) {
	$str .= '<li>' . $row['c_exe'] . '</li>';
}

echo '<ul>' . $str . '</ul>';
	
?>