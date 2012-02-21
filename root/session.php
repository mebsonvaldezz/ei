<?php

define('IN_EX', true);
include('../includes/common.php');

$user->session_start();

$mode = request_var('mode', '');

if (!in_array($mode, array('login', 'logout'))) {
	redirect('cover');
}

if ($user->data['is_user'] || $mode == 'logout') {
	if ($mode == 'logout') {
		$user->session_kill();
	}
	
	redirect('cover');
}

$errors = array();
$username = request_var('un', '');
$password = request_var('upw', '');

if (empty($username) || empty($password)) {
	$errors[] = 'Debe completar todos los datos requeridos.';
}

if (!sizeof($errors))
{
	$sql = 'SELECT *
		FROM _users
		WHERE username = ?';
	if (!$userdata = sql_fieldrow(sql_filter($sql, $username))) {
		$errors[] = 'El nombre de usuario es inv&aacute;lido.';
	}
}

if (isset($userdata) && sizeof($userdata) && !sizeof($errors)) {
	if (ValidatePassword($password, $userdata['user_password'])) {
		$user->session_create($userdata['user_id']);
		$user->auth = $user->get_auth($user->data['user_id']);
		
		if (!$user->auth['auth_access']) {
			$user->session_kill();
		}
		
		$user->session_auth();
		
		redirect('cover');
	} else {
		$errors[] = 'La contrase&ntilde;a es inv&aacute;lida.';
	}
}

//
if (sizeof($errors)) {
	login($errors);
}

?>