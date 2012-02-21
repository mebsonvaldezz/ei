<?php

define('IN_EX', true);
require('../includes/common.php');
require('./class.php');

$user->session_start();

//
// ODBC database
//
$update = new update();

if (!$update->connect('exiva_unis01'))
{
	die('DB>> No connection');
}
/*
$sql = "SELECT *
	FROM constacia
	WHERE nit = '18733-0'";
$result = odbc_exec($update->dbid, sql);

while ($row = odbc_fetch_array($result))
{
	print_r($row);
	echo '<br /><br />';
}
*/

$sql = "DELETE FROM provedor
	WHERE nit = '300407-9'";
$update->query($sql);

?>