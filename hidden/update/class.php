<?php

class update
{
	var $dbid;
	var $my;
	var $p = array();
	var $e = array();
	var $f = array();
	
	function connect($dbname)
	{
		if (empty($dbname))
		{
			return false;
		}
		
		if (!$this->dbid = odbc_connect($dbname, '', ''))
		{
			return false;
		}
		
		return true;
	}
	
	function import()
	{
		if (empty($this->p) && empty($this->e) && empty($this->f))
		{
			$this->_import_db();
		}
		
		$call = array('p', 'e', 'f');
		foreach ($call as $item)
		{
			$this->sizeof_call($item, $item);
		}
		
		return;
	}
	
	function sizeof_call($var, $func)
	{
		if (sizeof($this->$var))
		{
			return $this->{'_import_' . $func}();
		}
	}
	
	function _import_db()
	{
		//
		$sql = 'SELECT *
			FROM provedor
			ORDER BY nit';
		$result = $this->query($sql);
		
		while ($row = odbc_fetch_array($result))
		{
			$this->p[] = $row;
		}
		odbc_free_result($result);
		unset($row);
		
		//
		$sql = 'SELECT *
			FROM constancia
			ORDER BY exencion';
		$result = $this->query($sql);
		
		while ($row = odbc_fetch_array($result))
		{
			$this->e[] = $row;
		}
		odbc_free_result($result);
		unset($row);
		
		//
		$sql = 'SELECT *
			FROM factura
			ORDER BY exencion, factura';
		$result = $this->query($sql);
		
		while ($row = odbc_fetch_array($result))
		{
			$this->f[] = $row;
		}
		odbc_free_result($result);
		
		//
		return;
	}
	
	function _import_p()
	{
		$p = array();
		foreach ($this->p as $row)
		{
			$row = $this->trim($row);
			extract($row);
			//echo $nit . ' * ' . $nombre . '<br />';
			
			if ($this->nit_exists($nit, $nombre))
			{
				continue;
			}
			
			if (strpos($nit, '-'))
			{
				continue;
			}
			
			if (isset($p[$nombre]))
			{
				continue;
			}
			
			$p[][$nombre] = $nit;
		}
		
		if (sizeof($p))
		{
			$i = 0;
			foreach ($p as $item)
			{
				list($nombre, $nit) = each($item);
				$insert = array(
					'p_nit' => $nit,
					'p_name' => $nombre
				);
				$sql = 'INSERT INTO _prov' . $this->my->sql_build_array('INSERT', $insert);
				$this->my->sql_query($sql);
				
				$i++;
			}
			
			echo 'Registros importados: Proveedores: ' . $i . ' de ' . sizeof($this->p);
		}
		
		return;
	}
	
	function _import_e()
	{
		$e = array();
		foreach ($this->e as $row)
		{
			$row = $this->trim($row);
			extract($row);
			
			if (!$exencion)
			{
				continue;
			}
			
			if ($this->exe_exists($exencion))
			{
				continue;
			}
			
			if (isset($e[$exencion]))
			{
				continue;
			}
			
			$e[$exencion] = $row;
		}
		
		if (sizeof($e))
		{
			$i = 0;
			foreach ($e as $exencion => $data)
			{
				extract($data);
				
				list(, $year, $month, $day) = $this->preg_match('#([0-9]+)\-([0-9]+)\-([0-9]+)#si', $fec_exe);
				$cdate = gmmktime(6, 0, 0, $month, $day, $year);
				$cdate = ($cdate > 0) ? $cdate : 0;
				
				$null = (empty($concepto) || strstr(strtolower($concepto), 'nula')) ? true : false;
				$nit = (!$null) ? $nit : '0';
				$concepto = (!$null) ? $concepto : 'ANULADO';
				
				$insert = array(
					'c_exe' => (int) $exencion,
					'c_null' => $null,
					'c_date' => (int) $cdate,
					'c_nit' => $nit,
					'c_text' => $concepto
				);
				$sql = 'INSERT INTO _constancia' . $this->my->sql_build_array('INSERT', $insert);
				$this->my->sql_query($sql);
				
				$i++;
			}
			
			echo '<br />Registros importados: Exenciones: ' . $i . ' de ' . sizeof($this->e);
		}
		
		return;
	}
	
	function _import_f()
	{
		$t = array();
		$f = array();
		foreach ($this->f as $row)
		{
			$row = $this->trim($row);
			extract($row);
			
			$exencion = (int) $exencion;
			
			if (!$exencion || $exencion < 0)
			{
				continue;
			}
			
			if ($this->f_data_exists($exencion, $factura, $serie))
			{
				continue;
			}
			
			if ($this->array_search(array($factura, $serie), $f[$exencion]))
			{
				$t[] = $row;
				continue;
			}
			
			$f[$exencion][$factura][$serie] = $row;
		}
		
		if (sizeof($f))
		{
			$i = 0;
			foreach ($f as $exencion => $e_data)
			{
				foreach ($e_data as $factura => $f_data)
				{
					foreach ($f_data as $serie => $s_data)
					{
						list(, $year, $month, $day) = $this->preg_match('#([0-9]+)\-([0-9]+)\-([0-9]+)#si', $s_data['fec_fac']);
						$qdate = gmmktime(6, 0, 0, $month, $day, $year);
						$qdate = ($qdate > 0) ? $qdate : 0;
						
						$insert = array(
							'f_exe' => (int) $s_data['exencion'],
							'f_serie' => $s_data['serie'],
							'f_fact' => $s_data['factura'],
							'f_date' => $qdate,
							'f_total' => $s_data['total']
						);
						$sql = 'INSERT INTO _factura' . $this->my->sql_build_array('INSERT', $insert);
						$this->my->sql_query($sql);
						
						$i++;
					} // s_data
				} // f_data
			} // e_data
			
			echo '<br />Registros importados: Facturas: ' . $i . ' de ' . sizeof($this->f);
		}
		
		return;
	}
	
	function nit_exists($nit, $nombre)
	{
		$sql = "SELECT *
			FROM _prov
			WHERE p_nit = '" . $this->my->sql_escape($nit) . "'
				AND p_name = '" . $this->my->sql_escape($nombre) . "'";
		$result = $this->my->sql_query($sql);
		
		if ($row = $this->my->sql_fetchrow($result))
		{
			return true;
		}
		$this->my->sql_freeresult($result);
		
		return false;
	}
	
	function exe_exists($exencion)
	{
		$sql = 'SELECT *
			FROM _constancia
			WHERE c_exe = ' . (int) $exencion;
		$result = $this->my->sql_query($sql);
		
		if ($row = $this->my->sql_fetchrow($result))
		{
			return true;
		}
		$this->my->sql_freeresult($result);
		
		return false;
	}
	
	function f_data_exists($exencion, $factura, $serie)
	{
		$sql = 'SELECT *
			FROM _factura
			WHERE f_exe = ' . (int) $exencion . "
				AND f_fact = '" . $this->my->sql_escape($factura) . "'
				AND f_serie = '" . $this->my->sql_escape($serie) . "'";
		$result = $this->my->sql_query($sql);
		
		if ($row = $this->my->sql_fetchrow($result))
		{
			return true;
		}
		$this->my->sql_freeresult($result);
		
		return false;
	}
	
	//
	// Thanks to (congaz at yahoo dot dk) in the PHP array_search manual
	//
	function array_search($needles, $haystack)
	{
		foreach ($needles as $keySearch)
		{
			$haystack = $haystack[$keySearch];
			$result = $haystack;
		}
		
		// Check $result
		if (is_array($haystack))
		{
			// An array was found at the end of the search. Return true
			$result = true;
		}
		else if ($result == '')
		{
			// There was nothing found at the end of the search. Return false
			$result = false;
		}
		
		return $result;
	}
	
	function preg_match($pattern, $subject)
	{
		if (preg_match($pattern, $subject, $matches))
		{
			return $matches;
		}
		
		return false;
	}
	
	function trim(&$str)
	{
		return array_map('trim', $str);
	}
	
	//
	// Databases
	//
	function query($sql)
	{
		global $odb;
		
		if (!$result = odbc_exec($this->dbid, $sql))
		{
			die('DB>> Error: ' . odbc_errormsg() . '<br /><br />' . $sql);
		}
		
		return $result;
	}
	
	function import_mysql()
	{
		global $db;
		
		$this->my = $db;
	}
	
	function export_mysql()
	{
		global $db;
		
		$db = $this->my;
	}
}

?>