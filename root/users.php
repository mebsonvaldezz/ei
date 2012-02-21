<?php

define('IN_EX', true);
include('../includes/common.php');

$user->session_start();
$user->plogin();
$user->session_auth();

//
$user->allow_access('users');

//
$error = array();
$userid = request_var('userid', 0);

$submit = (isset($_POST['submit'])) ? TRUE : FALSE;
$search = (isset($_POST['search'])) ? TRUE : FALSE;
$create = (isset($_POST['create'])) ? TRUE : FALSE;
$screen = ($userid) ? 1 : 2;

if ($create) {
	$u_username = request_var('u_username', '');
	$u_password = request_var('u_password', '');
	$u_email = request_var('u_email', '');
	
	$v_auth = array('auth_access' => 0, 'auth_insert' => 0, 'auth_edit' => 0, 'auth_null' => 0, 'auth_delete' => 0, 'auth_search' => 0, 'auth_ranks' => 0, 'auth_print' => 0, 'auth_log' => 0);
	foreach ($v_auth as $k => $v) {
		$v_auth[$k] = request_var($k, $v);
	}
	
	if (!empty($u_username)) {
		if (!preg_match('#([a-zA-Z0-9]+)#si', $u_username)) {
			$error[] = 'El nombre de usuario, &uacute;nicamente puede contener letras y n&uacute;meros.';
		}
	} else {
		$error[] = 'Debe completar el nombre de usuario.';
	}
	
	if (sizeof($error)) {
		layout($screen, $error);
	}
	
	if (empty($u_password)) {
		$error[] = 'Debe completar la clave de usuario.';
	}
	
	if (empty($u_email)) {
		$error[] = 'Debe completar el correo electr&oacute;nico.';
	}
	
	if (!empty($u_password)) {
		$u_key = sha1($u_password);
	}
	
	$insert = array(
		'username' => $u_username,
		'user_password' => $u_key,
		'user_email' => $u_email
	);
	$sql = 'INSERT INTO _users' . sql_build('INSERT', $insert);
	$v_auth['user_id'] = sql_query_nextid($sql);
	
	$sql = 'INSERT INTO _auth' . sql_build('INSERT', $v_auth);
	sql_query($sql);
	
	redirect('users');
}

if ($userid)
{
	$sql = 'SELECT u.*, a.*
		FROM _users u, _auth a
		WHERE u.user_id = ?
			AND u.user_id = a.user_id';
	if (!$userdata = sql_fieldrow(sql_filter($sql, $userid))) {
		$error[] = 'El usuario seleccionado no existe.';
	}
	
	if (sizeof($error)) {
		layout($screen, $error);
	}
	
	//
	// Save changes
	//
	if ($submit) {
		$user_adm = request_var('user_adm', 0);
		$username = request_var('username', '');
		$user_password1 = request_var('user_password1', '');
		$user_password2 = request_var('user_password2', '');
		$user_email = request_var('user_email', '');
		$user_rank_min = request_var('user_rank_min', 0);
		$user_rank_max = request_var('user_rank_max', 0);
		$user_print_copies = request_var('user_print_copies', 0);
		
		// auth
		$auth_access = request_var('auth_access', 0);
		$auth_insert = request_var('auth_insert', 0);
		$auth_edit = request_var('auth_edit', 0);
		$auth_null = request_var('auth_null', 0);
		$auth_delete = request_var('auth_delete', 0);
		$auth_search = request_var('auth_search', 0);
		$auth_ranks = request_var('auth_ranks', 0);
		$auth_print = request_var('auth_print', 0);
		$auth_log = request_var('auth_log', 0);
		
		/*-----------
		auth_print
		0- none
		1- all
		2- ex, report
		-----------*/
		
		// Check user_adm
		if ($user_adm && !$user->data['user_adm']) {
			$error[] = 'S&oacute;lo un administrador puede promover a <b>Administrador</b>.';
		}
		
		// Check username
		if (!empty($username)) {
			if (!preg_match('#([a-zA-Z0-9]+)#si', $username)) {
				$error[] = 'El nombre de usuario, &uacute;nicamente puede contener letras y n&uacute;meros.';
			}
		} else {
			$error[] = 'Debe completar el nombre de usuario.';
		}
		
		// Check passwords
		if (!empty($user_password1) && !empty($user_password2)) {
			if ($user_password1 != $user_password2) {
				$error[] = 'Las contrase&ntilde;as ingresadas no coinciden, por favor verificar.';
			}
		} else {
//			$error[] = 'Debe ingresar la contrase&ntilde;a y la confirmaci&oacute;n.';
		}
		
		// Check email
		if (empty($user_email)) {
			$error[] = 'Debe completar el correo electr&oacute;nico.';
		}
		
		// Check ranks
		if ($user_rank_min > $user_rank_max) {
			$error[] = 'El rango m&iacute;nimo no puede ser mayor al rango m&aacute;ximo.';
		}
		
		if (!sizeof($error)) {
			$update = array();
			$changes = array('user_adm', 'username', 'user_email', 'user_rank_min', 'user_rank_max', 'user_print_copies');
			foreach ($changes as $item) {
				if ($$item != $userdata[$item]) {
					$update[$item] = $$item;
				}
			}
			
			$update_auth = array();
			$changes = array('auth_access', 'auth_insert', 'auth_edit', 'auth_null', 'auth_delete', 'auth_search', 'auth_ranks', 'auth_print', 'auth_log');
			foreach ($changes as $item) {
				if ($$item != $userdata[$item]) {
					$update_auth[$item] = $$item;
				}
			}
			
			if (!empty($user_password1)) {
				$upass = sha1($user_password1);
				if ($upass != $userdata['user_password']) {
					$update['user_password'] = $upass;
				}
			}
			
			if (sizeof($update)) {
				$sql = 'UPDATE _users SET ?? 
					WHERE user_id = ?';
				sql_query(sql_filter($sql, sql_build('UPDATE', $update), $userid));
			}
			
			if (sizeof($update_auth))
			{
				$sql = 'UPDATE _auth SET ??
					WHERE user_id = ?';
				sql_query(sql_filter($sql, sql_build('UPDATE', $update_auth), $userid));
			}
			
			redirect('users');
		} else {
			$bypass_vars = array(
				'user_adm' => $user_adm,
				'username' => $username,
				'user_password1' => $user_password1,
				'user_password2' => $user_password2,
				'user_email' => $user_email,
				'user_rank_min' => $user_rank_min,
				'user_rank_max' => $user_rank_max,
				'user_print_copies' => $user_print_copies,
				
				'auth_access' => $auth_access,
				'auth_insert' => $auth_insert,
				'auth_edit' => $auth_edit,
				'auth_null' => $auth_null,
				'auth_delete' => $auth_delete,
				'auth_search' => $auth_search,
				'auth_ranks' => $auth_ranks,
				'auth_print' => $auth_print,
				'auth_log' => $auth_log
			);
			
			layout($screen, $error, $bypass_vars);
		} // IF @so: !$error
	}
}

layout($screen);

//
// Functions
//
function layout($where = 1, $error = array(), $params = array()) {
	global $db, $user, $config, $userdata;
	
	page_header();
	
	echo '<div class="vsep-pre"><div class="vsep1">&nbsp;</div></div>';
	
	switch ($where) {
		case 1:
			if (!sizeof($params)) {
				$params = $userdata;
			}
?>

<div class="vsep-pre"><div class="vsep1">&nbsp;</div></div>
<form action="<?php echo s_link('users'); ?>" method="post">
<div class="colorbox darkborder pad10">
<div align="center" class="h2"><?php echo $userdata['username']; ?></div>
<div class="ie-widthfix">
<?php show_error($error); ?>
<br />
<table cellpadding="5" cellspacing="0" border="1" bordercolor="#999999" class="table-collapse" align="center">
	<tr>
		<td>Administrador</td>
		<td><input type="radio" name="user_adm" value="0"<?php echo ((!$params['user_adm']) ? ' checked' : ''); ?> /> No <input type="radio" name="user_adm" value="1"<?php echo (($params['user_adm']) ? ' checked' : ''); ?> /> Si</td>
	</tr>
	<tr>
		<td>Nombre de Usuario</td>
		<td><input type="text" name="username" value="<?php echo $params['username']; ?>" /></td>
	</tr>
	<tr>
		<td>Contrase&ntilde;a</td>
		<td><input type="text" name="user_password1" value="" /></td>
	</tr>
	<tr>
		<td>Confirmaci&oacute;n de contrase&ntilde;a</td>
		<td><input type="text" name="user_password2" value="" /></td>
	</tr>
	<tr>
		<td>Correo electr&oacute;nico</td>
		<td><input type="text" name="user_email" value="<?php echo $params['user_email']; ?>" /></td>
	</tr>
	<tr>
		<td>Rango m&iacute;nimo</td>
		<td><input type="text" name="user_rank_min" value="<?php echo $params['user_rank_min']; ?>" /></td>
	</tr>
	<tr>
		<td>Rango m&aacute;ximo</td>
		<td><input type="text" name="user_rank_max" value="<?php echo $params['user_rank_max']; ?>" /></td>
	</tr>
	<tr>
		<td>Copias de impresi&oacute;n</td>
		<td><input type="text" name="user_print_copies" value="<?php echo $params['user_print_copies']; ?>" /></td>
	</tr>
	<tr>
		<td class="red" colspan="2" align="center"><strong><h3>Autorizaci&oacute;n</h3></strong></td>
	</tr>
	<tr>
		<td>Acceso al sistema</td>
		<td><input type="radio" name="auth_access" value="0"<?php echo ((!$params['auth_access']) ? ' checked' : ''); ?> /> No <input type="radio" name="auth_access" value="1"<?php echo (($params['auth_access']) ? ' checked' : ''); ?> /> Si</td>
	</tr>
	<tr>
		<td>Ingresar</td>
		<td><input type="radio" name="auth_insert" value="0"<?php echo ((!$params['auth_insert']) ? ' checked' : ''); ?> /> No <input type="radio" name="auth_insert" value="1"<?php echo (($params['auth_insert']) ? ' checked' : ''); ?> /> Si</td>
	</tr>
	<tr>
		<td>Editar</td>
		<td><input type="radio" name="auth_edit" value="0"<?php echo ((!$params['auth_edit']) ? ' checked' : ''); ?> /> No <input type="radio" name="auth_edit" value="1"<?php echo (($params['auth_edit']) ? ' checked' : ''); ?> /> Si</td>
	</tr>
	<tr>
		<td>Anular</td>
		<td><input type="radio" name="auth_null" value="0"<?php echo ((!$params['auth_null']) ? ' checked' : ''); ?> /> No <input type="radio" name="auth_null" value="1"<?php echo (($params['auth_null']) ? ' checked' : ''); ?> /> Si</td>
	</tr>
	<tr>
		<td>Eliminar</td>
		<td><input type="radio" name="auth_delete" value="0"<?php echo ((!$params['auth_delete']) ? ' checked' : ''); ?> /> No <input type="radio" name="auth_delete" value="1"<?php echo (($params['auth_delete']) ? ' checked' : ''); ?> /> Si</td>
	</tr>
	<tr>
		<td>B&uacute;squeda</td>
		<td><input type="radio" name="auth_search" value="0"<?php echo ((!$params['auth_search']) ? ' checked' : ''); ?> /> No <input type="radio" name="auth_search" value="1"<?php echo (($params['auth_search']) ? ' checked' : ''); ?> /> Si</td>
	</tr>
	<tr>
		<td>Asignar rangos de ingreso</td>
		<td><input type="radio" name="auth_ranks" value="0"<?php echo ((!$params['auth_ranks']) ? ' checked' : ''); ?> /> No <input type="radio" name="auth_ranks" value="1"<?php echo (($params['auth_ranks']) ? ' checked' : ''); ?> /> Si</td>
	</tr>
	<tr>
		<td>Imprimir</td>
		<td><input type="radio" name="auth_print" value="0"<?php echo ((!$params['auth_print']) ? ' checked' : ''); ?> /> No <input type="radio" name="auth_print" value="1"<?php echo (((int) $params['auth_print'] === 1) ? ' checked' : ''); ?> /> Exenciones y revisi&oacute;n <input type="radio" name="auth_print" value="2"<?php echo (((int) $params['auth_print'] === 2) ? ' checked' : ''); ?> /> Todo (SAT)</td>
	</tr>
	<tr>
		<td>Ver registro de cambios</td>
		<td><input type="radio" name="auth_log" value="0"<?php echo ((!$params['auth_log']) ? ' checked' : ''); ?> /> No <input type="radio" name="auth_log" value="1"<?php echo (($params['auth_log']) ? ' checked' : ''); ?> /> Si</td>
	</tr>
</table>
<div align="center">
<?php echo s_hidden(array('userid' => $userdata['user_id'])); ?>
<br />
<input type="submit" class="submitdata" name="submit" value="Realizar consulta" />
</div>
</div>
</div>
</form>

<?php
			break;
		case 2:
			$sql = 'SELECT *
				FROM _users
				WHERE user_id <> 1
				ORDER BY username';
			if ($result = sql_rowset($sql)) {
				echo '<div class="tdisb pad10 red colorbox dsm ie-widthfix">';
				
				foreach ($result as $row) {
					echo '<div class="pad4">&bull; <a href="' . s_link('users', $row['user_id']) . '">' . $row['username'] . '</a></div>';
				}
			}
			
			if ($user->data['user_adm'])
			{

?>
</div>
<div class="vsep-pre"><div class="vsep1">&nbsp;</div></div>
<form action="<?php echo s_link('users'); ?>" method="post">
<div class="colorbox darkborder pad10">
<div align="center" class="h2"><?php echo $userdata['username']; ?></div>
<div class="ie-widthfix">
<?php show_error($error); ?>
<br />
<table cellpadding="5" cellspacing="0" border="1" bordercolor="#999999" class="table-collapse" align="center">
	<tr>
		<td>Nombre de Usuario</td>
		<td><input type="text" name="u_username" value="" /></td>
	</tr>
	<tr>
		<td>Contrase&ntilde;a</td>
		<td><input type="text" name="u_password" value="" /></td>
	</tr>
	<tr>
		<td>Correo electr&oacute;nico</td>
		<td><input type="text" name="u_email" value="" /></td>
	</tr>
	<tr>
		<td class="red" colspan="2" align="center"><strong><h3>Autorizaci&oacute;n</h3></strong></td>
	</tr>
	<tr>
		<td>Acceso al sistema</td>
		<td><input type="radio" name="auth_access" value="0" checked="checked" /> No <input type="radio" name="auth_access" value="1" checked="checked" /> Si</td>
	</tr>
	<tr>
		<td>Ingresar</td>
		<td><input type="radio" name="auth_insert" value="0" /> No <input type="radio" name="auth_insert" value="1" checked="checked" /> Si</td>
	</tr>
	<tr>
		<td>Editar</td>
		<td><input type="radio" name="auth_edit" value="0" /> No <input type="radio" name="auth_edit" value="1" checked="checked" /> Si</td>
	</tr>
	<tr>
		<td>Anular</td>
		<td><input type="radio" name="auth_null" value="0" /> No <input type="radio" name="auth_null" value="1" checked="checked" /> Si</td>
	</tr>
	<tr>
		<td>Eliminar</td>
		<td><input type="radio" name="auth_delete" value="0" /> No <input type="radio" name="auth_delete" value="1" checked="checked" /> Si</td>
	</tr>
	<tr>
		<td>B&uacute;squeda</td>
		<td><input type="radio" name="auth_search" value="0" /> No <input type="radio" name="auth_search" value="1" checked="checked" /> Si</td>
	</tr>
	<tr>
		<td>Asignar rangos de ingreso</td>
		<td><input type="radio" name="auth_ranks" value="0" /> No <input type="radio" name="auth_ranks" value="1" checked="checked" /> Si</td>
	</tr>
	<tr>
		<td>Imprimir</td>
		<td><input type="radio" name="auth_print" value="0" /> No <input type="radio" name="auth_print" value="1" checked="checked" /> Exenciones y revisi&oacute;n <input type="radio" name="auth_print" value="2" /> Todo (SAT)</td>
	</tr>
	<tr>
		<td>Ver registro de cambios</td>
		<td><input type="radio" name="auth_log" value="0" /> No <input type="radio" name="auth_log" value="1" checked="checked" /> Si</td>
	</tr>
</table>
<div align="center"><br /><input type="submit" class="submitdata" name="create" value="Crear usuario" /></div>
</div>
</div>
</form>
<?php
			}
			
			break;
	}
	
	echo '</div>';
	
	page_footer();
}

?>