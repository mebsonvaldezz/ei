<?php

define('IN_EX', true);
include('../includes/common.php');

$user->session_start();
$user->plogin();
$user->session_auth();

$sql = "SELECT *
	FROM _prov
	WHERE p_nit LIKE '%-%'";
$result = sql_rowset($sql);

foreach ($result as $row) {
	$new_nit = str_replace(array('-', ' '), array('', ''), $row['p_nit']);
	
	$sql = 'SELECT *
		FROM _prov
		WHERE p_nit = ?';
	if (!$row2 = sql_fieldrow(sql_filter($sql, $new_nit))) {
		$sql_insert = array(
			'p_nit' => $new_nit,
			'p_name' => $row['p_name']
		);
		$sql = 'INSERT INTO _prov' . sql_build('INSERT', $sql_insert);
		sql_query($sql);
		
		echo $sql . '*' . sql_affectedrows() . '<br />';
	}
	
	$sql = 'UPDATE _constancia SET c_nit = ?
		WHERE c_nit = ?';
	sql_query(sql_filter($sql, $new_nit, $row['p_nit']));
	
	echo $sql . '*' . sql_affectedrows() . '<br />';
	
	$sql = 'DELETE FROM _prov
		WHERE p_nit = ?';
	sql_query(sql_filter($sql, $row['p_nit']));
	
	echo $sql . '*' . sql_affectedrows() . '<br />';
	
	echo '<br />';
}

?>