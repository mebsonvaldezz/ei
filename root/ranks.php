<?php

define('IN_EX', true);
include('../includes/common.php');

$user->session_start();
$user->plogin();
$user->session_auth();

//
$user->allow_access('ranks');

//
$error = array();
$userid = request_var('userid', 0);

$submit = (isset($_POST['submit'])) ? TRUE : FALSE;
$search = (isset($_POST['search'])) ? TRUE : FALSE;
$screen = ($userid) ? 1 : 2;

if ($userid) {
	$sql = 'SELECT *
		FROM _users
		WHERE user_id = ?
			AND user_adm = 0';
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
		$user_rank_min = request_var('user_rank_min', 0);
		$user_rank_max = request_var('user_rank_max', 0);
		
		// Check ranks
		if ($user_rank_min > $user_rank_max) {
			$error[] = 'El rango m&iacute;nimo no puede ser mayor al rango m&aacute;ximo.';
		}
		
		if (!sizeof($error)) {
			$update = array();
			$changes = array('user_rank_min', 'user_rank_max');
			
			foreach ($changes as $item) {
				if ($$item != $userdata[$item]) {
					$update[$item] = $$item;
				}
			}
			
			if (sizeof($update)) {
				$sql = 'UPDATE _users SET ?? 
					WHERE user_id = ?';
				sql_query(sql_filter($sql, sql_build('UPDATE', $update), $userid));
			}
			
			redirect('ranks');
		} else {
			$bypass_vars = array(
				'user_rank_min' => $user_rank_min,
				'user_rank_max' => $user_rank_max
			);
			
			layout($screen, $error, $bypass_vars);
		} // IF @so: !$error
	}
}

layout($screen);

//
// Functions
//
function layout($where = 1, $error = array(), $params = array())
{
	global $db, $user, $config, $userdata;
	
	page_header();
	
	echo '<div class="vsep-pre"><div class="vsep1">&nbsp;</div></div>';
	
	switch ($where)
	{
		case 1:
			if (!sizeof($params))
			{
				$params = $userdata;
			}
?>

<div class="vsep-pre"><div class="vsep1">&nbsp;</div></div>
<form action="<?php echo s_link('ranks'); ?>" method="post">
<div class="colorbox darkborder pad10">
<div align="center" class="h2"><?php echo $userdata['username']; ?></div>
<div class="ie-widthfix">
<?php show_error($error); ?>
<br />
<table cellpadding="5" cellspacing="0" border="1" bordercolor="#999999" class="table-collapse" align="center">
	<tr>
		<td>Rango M&iacute;nimo</td>
		<td><input type="text" name="user_rank_min" value="<?php echo $params['user_rank_min']; ?>" /></td>
	</tr>
	<tr>
		<td>Rango M&aacute;ximo</td>
		<td><input type="text" name="user_rank_max" value="<?php echo $params['user_rank_max']; ?>" /></td>
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
					AND user_adm = 0
				ORDER BY username';
			if ($result = sql_rowset($sql)) {
				echo '<div class="tdisb pad10 red colorbox dsm ie-widthfix">';
				
				foreach ($result as $row) {
					echo '<div class="pad4">&bull; <a href="' . s_link('ranks', $row['user_id']) . '">' . $row['username'] . '</a></div>';
				}
			}
			break;
	}
	
	echo '</div>';
	
	page_footer();
}

?>