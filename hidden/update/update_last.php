<?php

define('IN_EX', true);
require('../includes/common.php');
require('./class.php');

$user->session_start();

$deleted = array(2882395, 2882351, 2882352, 2882353, 2882354, 2882355, 2882356, 2882357, 2882358, 2882359, 2882360, 2882361, 2882362, 2882363, 2882364, 2882365, 2882366, 2882367, 2882368, 2882369, 2882370, 2882371, 2882372, 2882373, 2882374, 2882375, 2882376, 2882377, 2882378, 2882379, 2882380, 2882381, 2882382, 2882383, 2882385, 2882386, 2882387, 2882388, 2882389, 2882390, 2882391, 2882392, 2882393, 2882394, 2882501);

sort($deleted);

//
// ODBC database
//
$update = new update();

if (!$update->connect('exiva_unis02'))
{
	die('DB>> No connection');
}

$update->import_mysql();

$each_page = 5;
$end = sizeof($deleted) / $each_page;
$data = array();

for ($i = 0; $i < $end; $i++)
{
	$d = array_slice($deleted, $i * $each_page, $each_page);
	
	$sql = 'SELECT c.*, p.*
		FROM constancia c, provedor p
		WHERE c.exencion IN (' . implode(',', $d) . ')
			AND c.nit = p.nit
		ORDER BY c.exencion';
	$result = $update->query($sql);
	
	while ($row = odbc_fetch_array($result))
	{
		$data[] = $row;
	}
	odbc_free_result($result);
}

$prov = array();

$ce = $cp = $cf = 0;

foreach ($data as $row)
{
	$row = $update->trim($row);
	extract($row);
	
	list(, $year, $month, $day) = $update->preg_match('#([0-9]+)\-([0-9]+)\-([0-9]+)#si', $fec_exe);
	$cdate = gmmktime(6, 0, 0, $month, $day, $year);
	$cdate = ($cdate > 0) ? $cdate : 0;
	
	$null = (empty($concepto) || strstr(strtolower($concepto), 'nula') || !$nit) ? true : false;
	$nit = (!$null) ? $nit : '0';
	$concepto = (!$null) ? strtoupper($concepto) : 'ANULADO';
	
	$insert = array(
		'c_exe' => (int) $exencion,
		'c_null' => $null,
		'c_date' => (int) $cdate,
		'c_nit' => $nit,
		'c_text' => $concepto
	);
	$sql = 'INSERT INTO _constancia' . $db->sql_build_array('INSERT', $insert);
	echo $sql . '<br />';
//	$db->sql_query($sql);
	
	$ce++;
	
	//
	if ($nit)
	{
		if (!$update->nit_exists($nit, $nombre))
		{
			$insert = array(
				'p_nit' => $nit,
				'p_name' => $nombre
			);
			$sql = 'INSERT INTO _prov' . $db->sql_build_array('INSERT', $insert);
			echo $sql . '<br />';
//			$db->sql_query($sql);
			
			$cp++;
		}
	}
}

echo 'Exenciones: ' . $ce . ' ---- Proveedores: ' . $cp;

echo '<br /><br /><br />';

//
// Factura
//
foreach ($deleted as $item)
{
	$sql = 'SELECT *
		FROM factura
		WHERE exencion = ' . (int) $item;
	$result = $update->query($sql);
	
	while ($row = odbc_fetch_array($result))
	{
		$row = $update->trim($row);
		extract($row);
		
		list(, $year, $month, $day) = $update->preg_match('#([0-9]+)\-([0-9]+)\-([0-9]+)#si', $fec_fac);
		$qdate = gmmktime(6, 0, 0, $month, $day, $year);
		$qdate = ($qdate > 0) ? $qdate : 0;
		
		$insert = array(
			'f_exe' => (int) $exencion,
			'f_serie' => $serie,
			'f_fact' => $factura,
			'f_date' => (int) $qdate,
			'f_total' => (double) $total
		);
		$sql = 'INSERT INTO _factura' . $db->sql_build_array('INSERT', $insert);
		echo $sql . '<br />';
		$db->sql_query($sql);
		
		$cf++;
	}
	odbc_free_result($result);
}

echo 'Facturas: ' . $cf;

$update->export_mysql();

?>