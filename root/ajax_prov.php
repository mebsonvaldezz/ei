<?php

define('IN_EX', true);
include('../includes/common.php');

$user->session_start();

$is_nit = false;
$val = request_var('nit', '');
if (!empty($val)) {
	$val = str_replace(array('-', '_', ' '), '', $val);
	$is_nit = true;
}

if (empty($val)) {
	$val = request_var('search', '');
	if (empty($val)) {
		die('<ul></ul>');
	}
}

if (is_numeric($val)) {
	$is_nit = true;
}

$sql_field = ($is_nit) ? 'nit' : 'name';

$sql = "SELECT *
	FROM _prov
	WHERE p_?? LIKE '??%'
	ORDER BY p_name";
$result = sql_rowset(sql_filter($sql, $sql_field, $val));

$str = '';
foreach ($result as $row) {
	$str .= '<li>' . $row['p_nit'] . '<br /><span class="informal">' . htmlentities($row['p_name']) . '</span></li>';
}

echo '<ul>' . $str . '</ul>';

?>