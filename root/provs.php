<?php

define('IN_EX', true);
include('../includes/common.php');

$user->session_start();
$user->plogin();
$user->session_auth();

$sql = "SELECT *
	FROM _prov
	WHERE p_nit LIKE '%-%'";
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$new_nit = str_replace(array('-', ' '), array('', ''), $row['p_nit']);
	
	$sql = "SELECT *
		FROM _prov
		WHERE p_nit = '" . $db->sql_escape($new_nit) . "'";
	$result2 = $db->sql_query($sql);
	
	if (!$row2 = $db->sql_fetchrow($result2))
	{
		$sql = "INSERT INTO _prov (p_nit, p_name)
			VALUES ('" . $db->sql_escape($new_nit) . "', '" . $db->sql_escape($row['p_name']) . "')";
		$db->sql_query($sql);
		echo $sql . '*' . $db->sql_affectedrows() . '<br />';
	}
	$db->sql_freeresult($result2);
	
	$sql = "UPDATE _constancia SET c_nit = '" . $db->sql_escape($new_nit) . "' WHERE c_nit = '" . $db->sql_escape($row['p_nit']) . "'";
	$db->sql_query($sql);
	echo $sql . '*' . $db->sql_affectedrows() . '<br />';
	
	$sql = "DELETE FROM _prov WHERE p_nit = '" . $db->sql_escape($row['p_nit']) . "'";
	$db->sql_query($sql);
	echo $sql . '*' . $db->sql_affectedrows() . '<br />';
	
	echo '<br />';
}
$db->sql_freeresult($result);

?>