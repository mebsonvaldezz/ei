<?php

function w($a = '', $d = false) {
	if (!f($a) || !is_string($a)) return array();
	
	$e = explode(' ', $a);
	if ($d !== false) {
		foreach ($e as $i => $v) {
			$e[$v] = $d;
			unset($e[$i]);
		}
	}
	
	return $e;
}

function f($s) {
	return !empty($s);
}

function _pre($a, $d = false) {
	echo '<pre>';
	print_r($a);
	echo '</pre>';
	
	if ($d === true) {
		exit;
	}
}

function _extension($file) {
	return strtolower(str_replace('.', '', substr($file, strrpos($file, '.'))));
}

//
// Main System functions
//
function round_month($where, $number)
{
	static $start = array(1, 4, 7, 10);
	
	foreach ($start as $i => $item)
	{
		$s = $item;
		$e = $start[$i + 1];
		$e = ($e) ? $e : 13;
		
		$number2 = ($number == 1) ? 2 : $number;
		
		if ($number2 > $s && $number <= $e)
		{
			switch ($where)
			{
				case 's':
					return $s;
					break;
				case 'e':
					return ($e != 13) ? $e - 1 : 12;
					break;
			}
		}
	}
}

function strtoupper_tilde($a)
{
	$a = strtoupper($a);
	$a = preg_replace('#&(A|E|I|O|U)ACUTE;#is', '&\1acute;', $a);
	$a = preg_replace('#&(N)TILDE;#is', '&\1tilde;', $a);
	return $a;
}

function round_day($where, $day, $month, $year)
{
	return date('t', gmmktime(0, 0, 0, $month, $day, $year));
}

function createdate($month, $day, $year)
{
	return gmmktime(6, 0, 0, $month, $day, $year);
}

function print_date_select($pref = false, $three_months = false)
{
	$months = array(1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre');
	
	$prefix = '';
	if ($three_months)
	{
		if ($pref !== false && $pref !== true)
		{
			$today_year = date('Y');
			
			switch ($pref)
			{
				case 1:
					$today_month = round_month('s', date('m'));
					$today_day = 1;
					break;
				case 2:
					$today_month = round_month('e', date('m'));
					$today_day = round_day('e', date('d'), $today_month);
					break;
			}
		}
	}
	else
	{
		$today_day = (int) date('d');
		$today_month = (int) date('m');
		$today_year = (int) date('Y');
	}
	
	//
	if ($pref !== false)
	{
		$prefix = $pref;
	}
	
	if ($pref === true)
	{
		$prefix = '2';
	}
	
	$vars = array('month'.$prefix, 'day'.$prefix, 'year'.$prefix);
	foreach ($vars as $var)
	{
		global ${$var};
		if (isset(${$var}))
		{
			${'today_' . str_replace($prefix, '', $var)} = ${$var};
		}
	}
	
	echo '<select name="day' . $prefix . '">';
	
	for ($i = 1; $i < 32; $i++)
	{
		echo '<option value="' . $i . '"' . (($i == $today_day) ? ' selected' : '') . '>' . $i . '</option>';
	}
	
	echo '</select><select name="month' . $prefix . '">';
	
	foreach ($months as $k => $v)
	{
		echo '<option value="' . $k . '"' . (($k == $today_month) ? ' selected' : '') . '>' . $v . '</option>';
	}
	
	echo '</select><select name="year' . $prefix . '">';

	for ($i = 1997, $end = date('Y') + 1; $i < $end; $i++)
	{
		echo '<option value="' . $i . '"' . (($i == $today_year) ? ' selected' : '') . '>' . $i . '</option>';
	}
	
	echo '</select>';
}

function build_groups($k, $v)
{
	echo '<select name="sgroup"><option value="0">Mostrar todos</option>';
	
	foreach ($k as $i => $t)
	{
		echo '<option value="' . $i . '">' . $v[$i] . '</option>';
	}
	
	echo '</select>';
}

function get_row_class($row, $i)
{
	if (!isset($row['c_null']) || !$row['c_null'])
	{
		return (!($i % 2) ? 'bgcolor2' : 'bgcolor3');
	}
	else
	{
		return (!($i % 2) ? 'bgnull1' : 'bgnull2');
	}
}

function parse_nulled_f($c_null, &$row)
{
	if ($c_null)
	{
		$row['f_date'] = $row['f_total'] = 0;
		$row['f_nit'] = '';
		$row['f_serie'] = '';
		$row['f_fact'] = '';
	}
}

function parse_nulled(&$row, $all = false)
{
	if ($row['c_null'])
	{
		$row['c_text'] = 'A N U L A D O';
		$row['c_nit'] = $row['p_name'] = '';
		$row['p_nit'] = 0;
		
		if ($all)
		{
			$row['f_date'] = $row['f_fact'] = $row['f_total'] = $row['f_serie'] = 0;
		}
	}
	else
	{
		$row['p_name'] = strtoupper_tilde($row['p_name']);
		$row['p_name'] = html_entity_decode($row['p_name']);
		
		$row['c_text'] = strtoupper($row['c_text']);
		//$row['p_name'] = strtoupper($row['p_name']);
	}
}

function parse_nulled2(&$row)
{
	if (isset($row['c_null']) && $row['c_null'])
	{
		$row['c_text'] = 'A N U L A D O';
		$row['c_nit'] = $row['p_nit'] = $row['p_name'] = '';
		$row['f_date'] = $row['f_fact'] = $row['f_total'] = $row['f_serie'] = 0;
	}
}

function show_error(&$error)
{
	if (!is_array($error) || !sizeof($error))
	{
		return;
	}
?>
<a name="error"></a><br />
<div align="center">
<div align="center" class="pad10 red colorbox dsm box ie-widthfix">
<strong>Error</strong><br /><br /><?php echo implode('<br />', $error); ?></div>
</div>
<br />
<?php
}

function get_net($number)
{
	return number_format(($number / 1.12), 2);
}

function get_iva($number)
{
	$number = (($number / 1.12) * 0.12);
	return number_format($number, 2);
}

function check_date(&$error, $month, $day, $year)
{
	if (!$day || !$month || !$year)
	{
		$error[] = 'Debe completar correctamente la fecha.';
	}
	
	if (!sizeof($error))
	{
		if (!checkdate($month, $day, $year))
		{
			$error[] = 'La fecha ingresada es inv&aacute;lida.';
		}
	}
	
	return;
}

function build_results_table($data)
{
	global $user, $config;
	
?>
<script type="text/javascript">
<!--
function cdf()
{
	if (confirm('Realmente desea borrar esta factura?'))
	{
		return true;
	}
	return false;
}
-->
</script>
<table width="100%" cellpadding="5" cellspacing="0" border="1" bordercolor="#999999" class="table-collapse ajax-results" align="center">
	<tr class="subtitle">
		<td align="center"><strong>Exenci&oacute;n</strong></td>
		<td align="center"><strong>Fecha de Exenci&oacute;n</strong></td>
		<td align="center"><strong>Fecha de Factura</strong></td>
		<td align="center"><strong>NIT</strong></td>
		<td align="center"><strong>Proveedor</strong></td>
		<td align="center"><strong>Descripci&oacute;n</strong></td>
		<td align="center"><strong>Serie</strong></td>
		<td align="center"><strong>Factura</strong></td>
		<td align="center"><strong>Total</strong></td>
		<td align="center"><strong>Exento</strong></td>
		<td align="center"><strong>IVA</strong></td>
		<td align="center"><strong>Impreso</strong></td>
		<?php if ($user->auth['auth_delete']) { ?><td align="center"><strong>Borrar factura</strong></td><?php } ?>
	</tr>
<?php
		$nulled = array();
		
		foreach ($data as $i => $row)
		{
			parse_nulled2($row);
			
			if ($row['c_null'])
			{
				if (isset($nulled[$row['c_exe']]))
				{
					continue;
				}
				
				$nulled[$row['c_exe']] = true;
			}
			
			$c_date = ($row['c_date']) ? date('d M Y', $row['c_date']) : '-';
			$f_date = ($row['f_date']) ? date('d M Y', $row['f_date']) : '-';
?>
	<tr class="<?php echo get_row_class($row, $i); ?>">
		<td><?php echo $row['c_exe']; ?></td>
		<td><?php echo $c_date; ?></td>
		<td><?php echo $f_date; ?></td>
		<td><?php echo $row['p_nit']; ?></td>
		<td><?php echo $row['p_name']; ?></td>
		<td><?php echo $row['c_text']; ?></td>
		<td><?php echo $row['f_serie']; ?></td>
		<td><?php echo $row['f_fact']; ?></td>
		<td>Q<?php echo number_format($row['f_total'], 2); ?></td>
		<td>Q<?php echo get_net($row['f_total']); ?></td>
		<td>Q<?php echo get_iva($row['f_total']); ?></td>
		<td style="color: #FFFFFF;" bgcolor="#<?php echo (!$row['c_np']) ? '990000' : '339933'; ?>"><?php echo ($row['c_np']) ? 'Si' : 'No'; ?></td>
		<?php
		
		if ($user->auth['auth_delete'] && $row['f_fact'])
		{
			?><td align="center"><a onClick="return cdf();" href="<?php echo s_link('delete', $row['f_id']); ?>"><img src="<?php echo rp(); ?>scripts/cross.png" border="0" alt="Borrar factura" title="Borrar factura" /></a></td><?php
		}
		
		?>
	</tr>
<?php
		}
?>
</table>
<?php
}

/**
* set_var
*
* Set variable, used by {@link request_var the request_var function}
*
* @private
*/
function set_var(&$result, $var, $type, $multibyte = false)
{
	settype($var, $type);
	$result = $var;

	if ($type == 'string')
	{
		$result = trim(htmlspecialchars(str_replace(array("\r\n", "\r", "\xFF"), array("\n", "\n", ' '), $result)));
		$result = (STRIP) ? stripslashes($result) : $result;
		if ($multibyte)
		{
			$result = preg_replace('#&amp;(\#[0-9]+;)#', '&\1', $result);
		}
	}
}

/**
* request_var
*
* Used to get passed variable
*/
function request_var($var_name, $default, $multibyte = false)
{
	if (REQC) {
		global $config;
		
		if ((strpos($var_name, $config['cookie_name']) !== false) && isset($_COOKIE[$var_name])) {
			$_REQUEST[$var_name] = $_COOKIE[$var_name];
		}
	}
	
	if (!isset($_REQUEST[$var_name]) || (is_array($_REQUEST[$var_name]) && !is_array($default)) || (is_array($default) && !is_array($_REQUEST[$var_name])))
	{
		return (is_array($default)) ? array() : $default;
	}

	$var = $_REQUEST[$var_name];
	if (!is_array($default))
	{
		$type = gettype($default);
	}
	else
	{
		list($key_type, $type) = each($default);
		$type = gettype($type);
		$key_type = gettype($key_type);
	}

	if (is_array($var))
	{
		$_var = $var;
		$var = array();

		foreach ($_var as $k => $v)
		{
			if (is_array($v))
			{
				foreach ($v as $_k => $_v)
				{
					set_var($k, $k, $key_type);
					set_var($_k, $_k, $key_type);
					set_var($var[$k][$_k], $_v, $type, $multibyte);
				}
			}
			else
			{
				set_var($k, $k, $key_type);
				set_var($var[$k], $v, $type, $multibyte);
			}
		}
	}
	else
	{
		set_var($var, $var, $type, $multibyte);
	}
		
	return $var;
}

function set_config($config_name, $config_value)
{
	global $db, $config;

	$sql = 'UPDATE _config SET config_value = ?
		WHERE config_name = ?';
	sql_query(sql_filter($sql, $config_value, $config_name));

	if (!sql_affectedrows() && !isset($config[$config_name]))
	{
		$sql = 'INSERT INTO _config' . sql_build('INSERT', array('config_name' => $config_name, 'config_value' => $config_value));
		sql_query($sql);
	}

	$config[$config_name] = $config_value;
}

function unique_id($extra = 0)
{
	list($usec, $sec) = explode(' ', microtime());
	mt_srand((float) $extra + (float) $sec + ((float) $usec * 100000));
	return uniqid(mt_rand(), true);
}

function hook($name, $args = array(), $arr = false) {
	switch ($name) {
		case 'isset':
			eval('$a = ' . $name . '($args' . ((is_array($args)) ? '[0]' . $args[1] : '') . ');');
			return $a;
			break;
		case 'in_array':
			if (is_array($args[1])) {
				if (hook('isset', array($args[1][0], $args[1][1]))) {
					eval('$a = ' . $name . '($args[0], $args[1][0]' . $args[1][1] . ');');
				}
			} else {
				eval('$a = ' . $name . '($args[0], $args[1]);');
			}
			
			return (isset($a)) ? $a : false;
			break;
	}
	
	$f = 'call_user_func' . ((!$arr) ? '_array' : '');
	return $f($name, $args);
}

function rp()
{
	global $config;
	return $config['root'];
}

function s_link($module, $data = false)
{
	global $config;
	
	$url = $config['root'] . $module . '/';
	if ($data !== false)
	{
		if (is_array($data))
		{
			foreach ($data as $value)
			{
				if ($value != '') $url .= $value . '/';
			}
		}
		else
		{
			$url .= $data . '/';
		}
	}
	
	return $url;
}

function s_hidden($ary)
{
	if (!is_array($ary) || !sizeof($ary))
	{
		return;
	}
	
	$s_hiddenf = '';
	foreach ($ary as $k => $v)
	{
		$s_hiddenf .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
	}
	
	return $s_hiddenf;
}

function redirect($page)
{
	global $config, $db;

	if (isset($db)) {
		sql_close();
	}
	
	if (is_array($page))
	{
		$_page = '';
		foreach ($page as $item)
		{
			$_page .= $item . '/';
		}
		$page = (string) $_page;
	}
	else
	{
		$page .= '/';
	}
	
	header('Location: ' . $config['saddress'] . rp() . $page);
	exit();
}

function print_header()
{
	global $config;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Exenciones de IVA</title>
<link rel="stylesheet" type="text/css" href="<?php echo rp(); ?>scripts/style.css">
<script src="<?php echo rp(); ?>scripts/prototype.js" type="text/javascript"></script>
<script src="<?php echo rp(); ?>scripts/scriptaculous.js" type="text/javascript"></script>
<script type="text/javascript">
<!--
var call_ajx = "<?php echo s_link('ajax'); ?>";
-->
</script>
</head>

<body>
<noscript><div align="center"><div align="center" class="pad10 red colorbox dsm box ie-widthfix"><strong>Para poder utilizar este sistema, usted debe tener habilitado -Javascript-. Por favor verifique la configuraci&oacute;n de su navegador.</strong></div></div></noscript>

<div align="center"><img src="/ei/scripts/exiva.gif" alt="" /></div>
<div id="main-container">
<?php
}

function print_footer()
{
	global $config;
?>

</div>
<script src="<?php echo rp(); ?>scripts/footer.js" type="text/javascript"></script>
</body>
</html>
<?php
	exit();
}

function login($errors = false)
{
	global $config, $user;
	
	print_header();
	
?>
<form action="<?php echo s_link('login') ?>" method="post">
<div align="center">
<?php

if (is_array($errors) && sizeof($errors))
{
?>
<div align="center" class="pad10 red colorbox dsm ie-widthfix"><?php echo implode('<br />', $errors); ?></div>
<div>&nbsp;</div>
<?php
}
?>
<div align="center" class="colorbox dsm ie-widthfix">
<table cellpadding="6" cellspacing="0" border="0">
	<tr>
		<td>Nombre de Usuario</td>
		<td><input type="text" id="un" name="un" maxlength="20" size="20" /></td>
	</tr>
	<tr>
		<td>Contrase&ntilde;a</td>
		<td><input type="password" name="upw" maxlength="20" size="20" /></td>
	</tr>
	<tr>
		<td colspan="2" align="center"><input type="submit" name="submit" value="Entrar" /></td>
	</tr>
</table>
</div>
</div>
</form>

<?php
	print_footer();
}

function error_handler()
{
	return;
}

function requested_url()
{
	global $config;
	
	$page = str_replace(rp(), '', $_SERVER['REQUEST_URI']);
	$page = (!empty($_SERVER['REQUEST_URI'])) ? preg_replace('#^/?(.*?)/?$#', '\1', $page) : '';
	return $page;
}

function page_header()
{
	global $user, $config;
	
	if (strstr($user->browser, 'compatible') || strstr($user->browser, 'Gecko'))
	{
		ob_start('ob_gzhandler');
	}

	// Headers
	header('Cache-Control: private, no-cache="set-cookie", pre-check=0, post-check=0');
	header('Expires: 0');
	header('Pragma: no-cache');

	$_menu = array(
		'search' => 'Consulta',
		'insert' => 'Ingreso',
		'edit' => 'Modificaci&oacute;n',
		'null' => 'Anulaci&oacute;n',
		'print' => 'Impresi&oacute;n',
		'log' => 'Registro de cambios',
		'users|ranks' => 'Administrar usuarios'
	);
	
	$page_e = explode('/', requested_url());
	$folder = array_splice($page_e, 0, 1);
	$folder = $folder[0];
	
	print_header();
	
?>
<div><a class="bold" href="<?php echo s_link('cover'); ?>">Men&uacute; Principal</a> <span class="soft">|</span> <a class="red bold" href="<?php echo s_link('logout'); ?>">Salir del sistema</a></div><br />

<div>
<?php

$title = '';

$_buildmenu = array();
foreach ($_menu as $k => $v)
{
	$item_auth = false;
	
	if (strpos($k, '|'))
	{
		foreach (explode('|', $k) as $item_a)
		{
			if ($user->auth['auth_' . $item_a] || $user->data['user_adm'])
			{
				$item_auth = true;
				$k = $item_a;
				break;
			}
		}
	}
	else
	{
		$item_auth = $user->auth['auth_' . $k];
	}
	
	if (!$item_auth && !$user->data['user_adm'])
	{
		continue;
	}
	
	$is_pane = ($k == $folder);
	if ($is_pane)
	{
		$title = $v;
	}
	
	$_buildmenu[] = (($is_pane) ? '<strong class="gray">' : '') . '<a href="' . s_link($k) . '">' . $v . '</a>' . (($is_pane) ? '</strong>' : '');
}

if (sizeof($_buildmenu))
{
	echo '<div class="hr">&nbsp;</div><br />';
	echo implode(' <span class="soft">|</span> ', $_buildmenu);
	if ($title != '')
	{
?>
<br /><br /><div class="hr">&nbsp;</div>
<div class="subtitle h3 ie-widthfix"><?php echo $title; ?></div>
<?php
	}
}
else
{
	echo 'Usted no est&aacute; autorizado para realizar operaciones, contacte al administrador del sistema.';
}

?>
</div>
<?php
}

function page_footer()
{
	print_footer();
	die();
}

function online()
{
	global $user, $db, $config;
	
	$total_users = 0;
	
	$sql = 'SELECT u.*, s.*
		FROM _users u, _sessions s
		WHERE s.session_time >= ??
			AND s.session_user_id <> 1
			AND u.user_id = s.session_user_id
		ORDER BY u.username ASC, s.session_ip ASC';
	$result = sql_rowset(sql_filter($sql, time() - 3600));
	
	foreach ($result as $i => $row) {
		if (!$i) {
			echo '<br /><br /><div class="bold">Usuarios conectados</div><br />';
			
			$last_userid = '';
		}
		
		if ($row['user_id'] != $last_userid)
		{
			$total_users++;
			echo '<div>' . (($user->data['user_adm']) ? '<a href="' . s_link('users', $row['user_id']) . '">' : '') . $row['username'] . (($user->data['user_adm']) ? '</a>' : '') . '</div>';
		}
		
		$last_userid = $row['user_id'];
	}
	
	if (!$total_users) {
		echo '<div>No hay usuarios conectados.</div>';
	}
}

function xlog($a, $e, $f = -9)
{
	global $db, $user;
	
	$action = $a . (($f != -9) ? '.' . $f : '');
	
	$insert = array(
		'log_user_id' => (int) $user->data['user_id'],
		'log_date' => (int) time(),
		'log_exe' => (int) $e,
		'log_action' => $action
	);
	$sql = 'INSERT INTO _log' . sql_build('INSERT', $insert);
	sql_query($sql);
}

function fatal_error($code, $nu1, $nu2, $ary, $errno) {
	echo '<pre>';
	print_r(array($code, $nu1, $nu2, $ary, $errno));
	echo '</pre>';
	exit;
}

function get_file($f) {
	if (!f($f)) return false;
	
	if (!@file_exists($f)) {
		return w();
	}
	
	return array_map('trim', @file($f));
}

function decode_ht($path) {
	$da_path = './../' . $path;
	
	if (!@file_exists($da_path)) {
		die('no');
	}
	
	if (!@file_exists($da_path) || !$a = @file($da_path)) exit;
	
	return explode(',', _decode($a[0]));
}

function hex2asc($str) {
	$newstring = '';
	for ($n = 0, $end = strlen($str); $n < $end; $n+=2) {
		$newstring .=  pack('C', hexdec(substr($str, $n, 2)));
	}
	
	return $newstring;
}

function _encode($msg) {
	for ($i = 0; $i < 1; $i++) {
		$msg = base64_encode($msg);
	}
	
	return bin2hex($msg);
}

function _decode($msg) {
	$msg = hex2asc($msg);
	for ($i = 0; $i < 1; $i++) {
		$msg = base64_decode($msg);
	}
	
	return $msg;
}

//Takes a password and returns the salted hash
//$password - the password to hash
//returns - the hash of the password (128 hex characters)
function HashPassword($password) {
	$salt = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)); //get 256 random bits in hex
	
	$hash = hash('sha256', $salt . $password); //prepend the salt, then hash
	//store the salt and hash in the same string, so only 1 DB column is needed
	$final = $salt . $hash; 
	return $final;
}

//Validates a password
//returns true if hash is the correct hash for that password
//$hash - the hash created by HashPassword (stored in your DB)
//$password - the password to verify
//returns - true if the password is valid, false otherwise.
function ValidatePassword($password, $correctHash) {
	$salt = substr($correctHash, 0, 64); //get the salt from the front of the hash
	$validHash = substr($correctHash, 64, 64); //the SHA256

	$testHash = hash('sha256', $salt . $password); //hash the password being tested
	
	//if the hashes are exactly the same, the password is valid
	return $testHash === $validHash;
}

?>