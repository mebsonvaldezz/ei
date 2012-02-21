<?php

if (!defined('IN_EX')) exit;

$starttime = explode(' ', microtime());
$starttime = $starttime[1] + $starttime[0];

//error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(E_ALL);

if (@ini_get('register_globals')) {
	foreach ($_REQUEST as $var_name => $void) {
		unset(${$var_name});
	}
}

if (!defined('SROOT')) {
	define('SROOT', '../');
}

//require_once(SROOT . 'includes/db.php');
require_once(SROOT . 'includes/user.php');
require_once(SROOT . 'includes/functions.php');
require_once(SROOT . 'includes/db.mysql.php');

define('STRIP', (get_magic_quotes_gpc()) ? true : false);
set_error_handler('error_handler');

$user = new user();
$db = new database();

//$db->sql_connect('localhost', 'root', '', 'ei');

$sql = 'SELECT *
	FROM _config';
$result = sql_rowset($sql, 'config_name', 'config_value');

$config['saddress'] = 'http://localhost';

?>