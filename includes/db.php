<?php

if (!defined('IN_EX')) exit;

class sql_db
{
	var $db_connect_id;
	var $query_result;
	
	function sql_db()
	{
		return;
	}
	
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database)
	{
		$this->db_connect_id = @mysql_pconnect($sqlserver, $sqluser, $sqlpassword);

		if ($this->db_connect_id && $database != '')
		{
			if (@mysql_select_db($database))
			{
				return $this->db_connect_id;
			}
		}
		
		die('No connection');
	}
	
	function sql_query($query, $ron = false)
	{
		if (($this->query_result = @mysql_query($query, $this->db_connect_id)) === false)
		{
			if (!$ron)
			{
				die('SQL Error: ' . $query . '<br /><br />' . mysql_error());
			}
		}
		
		return ($this->query_result) ? $this->query_result : false;
	}
	
	function sql_query_limit($query, $total, $offset = 0)
	{ 
		if ($query != '') 
		{
			$this->query_result = false; 

			// if $total is set to 0 we do not want to limit the number of rows
			if ($total == 0)
			{
				$total = -1;
			}

			$query .= "\n LIMIT " . ((!empty($offset)) ? $offset . ', ' . $total : $total);

			return $this->sql_query($query); 
		}
		
		return false; 
	}
	
	function sql_fetchrow($query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}
		
		return ($query_id) ? @mysql_fetch_assoc($query_id) : false;
	}
	
	function sql_freeresult($query_id)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}
		
		if (!$query_id)
		{
			return false;
		}
		
		return @mysql_free_result($query_id);
	}
	
	function sql_affectedrows()
	{
		return ($this->db_connect_id) ? @mysql_affected_rows($this->db_connect_id) : false;
	}
	
	function sql_numrows($query_id = false)
	{
		if (!$query_id)
		{
			$query_id = $this->query_result;
		}

		return ($query_id) ? @mysql_num_rows($query_id) : false;
	}
	
	function sql_build_array($query, $assoc_ary = false)
	{
		if (!is_array($assoc_ary))
		{
			return false;
		}

		$fields = array();
		$values = array();
		if ($query == 'INSERT' || $query == 'INSERT_SELECT')
		{
			foreach ($assoc_ary as $key => $var)
			{
				$fields[] = $key;

				if (is_null($var))
				{
					$values[] = 'NULL';
				}
				else if (is_string($var))
				{
					$values[] = "'" . $this->sql_escape($var) . "'";
				}
				else if (is_array($var) && is_string($var[0]))
				{
					$values[] = $var[0];
				}
				else
				{
					$values[] = (is_bool($var)) ? intval($var) : $var;
				}
			}

			$query = ($query == 'INSERT') ? ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')' : ' (' . implode(', ', $fields) . ') SELECT ' . implode(', ', $values) . ' ';
		}
		else if ($query == 'MULTI_INSERT')
		{
			$ary = array();
			foreach ($assoc_ary as $id => $sql_ary)
			{
				$values = array();
				foreach ($sql_ary as $key => $var)
				{
					if (is_null($var))
					{
						$values[] = 'NULL';
					}
					else if (is_string($var))
					{
						$values[] = "'" . $this->sql_escape($var) . "'";
					}
					else
					{
						$values[] = (is_bool($var)) ? intval($var) : $var;
					}
				}
				$ary[] = '(' . implode(', ', $values) . ')';
			}

			$query = ' (' . implode(', ', array_keys($assoc_ary[0])) . ') VALUES ' . implode(', ', $ary);
		}
		else if ($query == 'UPDATE' || $query == 'SELECT')
		{
			$values = array();
			foreach ($assoc_ary as $key => $var)
			{
				if (is_null($var))
				{
					$values[] = "$key = NULL";
				}
				else if (is_string($var))
				{
					$values[] = "$key = '" . $this->sql_escape($var) . "'";
				}
				else
				{
					$values[] = (is_bool($var)) ? "$key = " . intval($var) : "$key = $var";
				}
			}
			$query = implode(($query == 'UPDATE') ? ', ' : ' AND ', $values);
		}

		return $query;
	}
	
	function sql_escape($msg)
	{
		return mysql_real_escape_string($msg);
	}
	
	function sql_nextid ()
	{
		return ($this->db_connect_id) ? @mysql_insert_id($this->db_connect_id) : false;
	}
	
	function sql_close()
	{
		return @mysql_close($this->db_connect_id);
	}
}

?>