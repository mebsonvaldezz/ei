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

$update->import_mysql();
$update->import();
$update->export_mysql();

?>