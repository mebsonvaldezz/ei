<?php

if (!defined('IN_EX')) exit;

class user {
	var $session_id = '';
	var $cookie_data = array();
	var $data = array();
	var $auth = array();
	var $ip = '';
	var $page = '';
	var $time_now = 0;
	
	public function __construct() {
		return;
	}
	
	function session_start() {
		global $db, $config;
		
		$this->time_now = time();
		$this->page = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '';
		
		$this->cookie_data = array();
		if (isset($_COOKIE[$config['cookie_name'] . '_sid']) || isset($_COOKIE[$config['cookie_name'] . '_u'])) {
			// Switch to request_var ... can this cause issues, can a _GET/_POST param
			// be used to poison this? Not sure that it makes any difference in terms of
			// the end result, be it a cookie or param.
			$this->cookie_data['u'] = request_var($config['cookie_name'] . '_u', 0);
			$this->session_id = request_var($config['cookie_name'] . '_sid', '');
		} else {
			$this->session_id = '';
		}
		
		$this->ip = (!empty($_SERVER['REMOTE_ADDR'])) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '';
		
		// Is session_id is set or session_id is set and matches the url param if required
		if (!empty($this->session_id)) {
			$sql = 'SELECT u.*, s.*
				FROM _sessions s, _users u
				WHERE s.session_id = ?
					AND u.user_id = s.session_user_id';
			$this->data = sql_fieldrow(sql_filter($sql, $this->session_id));
			
			// Did the session exist in the DB?
			if (isset($this->data['user_id'])) {
				$s_ip = implode('.', array_slice(explode('.', $this->data['session_ip']), 0, $config['ip_check']));
				$u_ip = implode('.', array_slice(explode('.', $this->ip), 0, $config['ip_check']));
				
				if ($u_ip == $s_ip) {
					// Only update session DB a minute or so after last update or if page changes
					if ($this->time_now - $this->data['session_time'] > 60 || $this->data['session_page'] != $this->page)
					{
						$sql = 'UPDATE _sessions SET session_time = ?, session_page = ?
							WHERE session_id = ?';
						sql_query(sql_filter($sql, $this->time_now, $this->page, $this->session_id));
					}
					
					// Ultimately to be removed
					$this->data['is_user'] = ($this->data['user_id'] != 1) ? true : false;
					
					return true;
				}
			}
		}
		
		// If we reach here then no (valid) session exists. So we'll create a new one
		return $this->session_create();
	}
	
	function session_create($user_id = false) {
		global $db, $config;

		$this->data = array();
		
		// Garbage collection ... remove old sessions updating user information
		// if necessary. It means (potentially) 11 queries but only infrequently
		if ($this->time_now > $config['session_last_gc'] + $config['session_gc']) {
			$this->session_gc();
		}
		
		if ($user_id !== false) {
			$this->cookie_data['u'] = $user_id;

			$sql = 'SELECT *
				FROM _users
				WHERE user_id = ?';
			$this->data = sql_fieldrow(sql_filter($sql, $this->cookie_data['u']));
		}
		
		// If no data was returned one or more of the following occured:
		// Key didn't match one in the DB
		// User does not exist
		if (!sizeof($this->data)) {
			$this->cookie_data['u'] = 1;

			$sql = 'SELECT *
				FROM _users
				WHERE user_id = ?';
			$this->data = sql_fieldrow(sql_filter($sql, $this->cookie_data['u']));
		}
		
		if ($this->data['user_id'] != 1) {
			$sql = 'SELECT session_time, session_id
				FROM _sessions
				WHERE session_user_id = ?
				ORDER BY session_time DESC
				LIMIT 1';
			if ($sdata = sql_fieldrow(sql_filter($sql, $this->data['user_id']))) {
				$this->data = array_merge($sdata, $this->data);
				unset($sdata);
				$this->session_id = $this->data['session_id'];
			}
			
			$this->data['session_last_visit'] = (isset($this->data['session_time']) && $this->data['session_time']) ? $this->data['session_time'] : (($this->data['user_lastvisit']) ? $this->data['user_lastvisit'] : time());
		} else {
			$this->data['session_last_visit'] = time();
		}

		//
		// Do away with ultimately?
		$this->data['is_user'] = ($this->data['user_id'] != 1) ? true : false;
		//
		//
		
		// Create or update the session
		$sql_ary = array(
			'session_user_id' => (int) $this->data['user_id'],
			'session_start' => (int) $this->time_now,
			'session_last_visit' => (int) $this->data['session_last_visit'],
			'session_time' => (int) $this->time_now,
			'session_page' => (string) $this->page,
			'session_ip' => (string) $this->ip
		);

		$sql = 'UPDATE _sessions SET ??
			WHERE session_id = ?';
		sql_query(sql_filter($sql, sql_build('UPDATE', $sql_ary), $this->session_id));
			
		if (!$this->session_id || !sql_affectedrows()) {
			$this->session_id = $this->data['session_id'] = md5(unique_id());
			$sql_ary['session_id'] = (string) $this->session_id;
			
			sql_query('INSERT INTO _sessions' . sql_build('INSERT', $sql_ary));
		}

		$cookie_expire = $this->time_now + 31536000;
		
		$this->set_cookie('u', $this->cookie_data['u'], $cookie_expire);
		$this->set_cookie('sid', $this->session_id, 0);

		return true;
	}
	
	function plogin() {
		if (!$this->data['is_user'])
		{
			login();
		}
		return;
	}
	
	function session_kill() {
		global $db, $config;

		$sql = 'DELETE FROM _sessions
			WHERE session_id = ?
				AND session_user_id = ?';
		sql_query(sql_filter($sql, $this->session_id, $this->data['user_id']));

		if ($this->data['user_id'] != 1) {
			// Delete existing session, update last visit info first!
			if (!isset($this->data['session_time'])) {
				$this->data['session_time'] = time();
			}
			
			$sql = 'UPDATE _users SET user_lastvisit = ?
				WHERE user_id = ?';
			sql_query(sql_filter($sql, $this->data['session_time'], $this->data['user_id']));

			// Reset the data array
			$this->data = array();			
			
			$sql = 'SELECT *
				FROM _users
				WHERE user_id = 1';
			$this->data = sql_fieldrow($sql);
		}
		
		$cookie_expire = $this->time_now - 31536000;
		$this->set_cookie('u', '', $cookie_expire);
		$this->set_cookie('sid', '', $cookie_expire);
		unset($cookie_expire);
		
		$this->session_id = '';

		return true;
	}
	
	function session_gc() {
		global $db, $config;

		if (!$this->time_now)
		{
			$this->time_now = time();
		}
		
		// Get expired sessions, only most recent for each user
		$sql = 'SELECT session_user_id, session_page, MAX(session_time) AS recent_time
			FROM _sessions
			WHERE session_time < ??
			GROUP BY session_user_id, session_page
			LIMIT 5';
		$result = sql_rowset(sql_filter($sql, $this->time_now - $config['session_length']));
		
		$del_user_id = '';
		$del_sessions = 0;
		foreach ($result as $row) {
			if ($row['session_user_id'] != 1) {
				$sql = 'UPDATE _users SET user_lastvisit = ?
					WHERE user_id = ?';
				sql_query(sql_filter($sql, $row['recent_time'], $row['session_user_id']));
			}
			
			$del_user_id .= (($del_user_id != '') ? ', ' : '') . (int) $row['session_user_id'];
			$del_sessions++;
		}
		
		if ($del_user_id != '') {
			// Delete expired sessions
			$sql = "DELETE FROM _sessions
				WHERE session_user_id IN ($del_user_id)
					AND session_time < ??";
			sql_query(sql_filter($sql, $this->time_now - $config['session_length']));
		}
		
		if ($del_sessions < 5) {
			// Less than 5 sessions, update gc timer ... else we want gc
			// called again to delete other sessions
			set_config('session_last_gc', $this->time_now);
		}

		return;
	}
	
	function allow_access($page) {
		if ($this->data['user_adm'] || (isset($this->auth['auth_' . $page]) && $this->auth['auth_' . $page])) {
			return true;
		}
		
		global $config;
		
		print_header();
		
		echo 'Usted no tiene acceso a esta p&aacute;gina.<br /><br /><a href="' . s_link('cover') . '">Click para regresar a la p&aacute;gina principal</a>';
		
		print_footer();
	}
	
	function session_auth() {
		if (!sizeof($this->auth)) {
			$this->auth = $this->get_auth($this->data['user_id']);
		}
		
		if (!$this->auth['auth_access']) {
			print_header();
			echo 'Usted no tiene acceso a este sistema.';
			print_footer();
		}
		
		return true;
	}
	
	function get_print_auth($user_id) {
		$auth = $this->get_auth($user_id);
		
		if (!$auth || !sizeof($auth)) {
			return false;
		}
		
		if (!$auth['auth_print']) {
			return false;
		}
		
		$return_ary = array('1' => true, '2' => true);
		
		if ($auth['auth_print'] == 2) {
			$return_ary += array('3' => true);
		}
		
		return $return_ary;
	}
	
	function get_auth($user_id) {
		global $db;
		
		$sql = 'SELECT a.*
			FROM _auth a
			LEFT JOIN _users u ON a.user_id = u.user_id
			WHERE a.user_id = ?';
		if ($row = sql_fieldrow(sql_filter($sql, $user_id))) {
			$row['auth_users'] = ($this->data['user_adm']) ? TRUE : FALSE;
			
			return $row;
		}
		
		return false;
	}
	
	function set_cookie($name, $cookiedata, $cookietime) {
		global $config;

		setcookie($config['cookie_name'] . '_' . $name, $cookiedata, $cookietime, '/');
	}
	
	function private_data() {
		$global_users = array(2, 3, 4, 5, 11, 12, 13, 14);
		if (in_array($this->data['user_id'], $global_users)) {
			return false;
		}
		
		$single_users = array(
			array(6, 7, 8),
			array(9, 10)
		);
		
		foreach ($single_users as $i) {
			if (in_array($this->data['user_id'], $i)) {
				return $i;
			}
		}
		
		return;
	}
}

?>