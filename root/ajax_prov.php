<?php

define('IN_EX', true);
include('../includes/common.php');

$user->session_start();

$is_nit = false;
$val = request_var('nit', '');
if (!empty($val))
{
	$val = str_replace(array('-', '_', ' '), '', $val);
	$is_nit = true;
}

if (empty($val))
{
	$val = request_var('search', '');
	if (empty($val))
	{
		die('<ul></ul>');
	}
}

$sql = 'SELECT *
	FROM _prov
	WHERE p_' . (($is_nit) ? 'nit' : 'name') . " LIKE '" . $db->sql_escape($val) . "%'
	ORDER BY p_name";
$result = $db->sql_query($sql);

$str = '';
while ($row = $db->sql_fetchrow($result))
{
	$str .= '<li>' . $row['p_nit'] . '<br /><span class="informal">' . htmlentities($row['p_name']) . '</span></li>';
}
$db->sql_freeresult($result);

echo '<ul>' . $str . '</ul>';

?>