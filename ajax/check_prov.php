<?php
/*
<NPT, a web development framework.>
Copyright (C) <2012>  <NPT>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

define('IN_EX', true);
require('../includes/common.php');

$user->session_start();

$val = request_var('nit', '');
if (empty($val))
{
	$val = request_var('search', '');
	if (empty($val))
	{
		die('<ul></ul>');
	}
}

$sql_field = (is_numeric($val)) ? 'nit' : 'name';
$str = '';

$sql = "SELECT *
	FROM _prov
	WHERE p_?? LIKE '??%'
	ORDER BY p_name";
$result = sql_rowset(sql_filter($sql, $sql_field, $val));

foreach ($result as $row) {
	$str .= '<li>' . $row['p_nit'] . '<br /><span class="informal">' . htmlentities($row['p_name']) . '</span></li>';
}

echo '<ul>' . $str . '</ul>';

?>