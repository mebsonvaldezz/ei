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

$exe = request_var('exencion', 0);
if (!$exe)
{
	die('<ul></ul>');
}

$str = '';

$sql = "SELECT *
	FROM _constancia
	WHERE c_exe LIKE '??%'
	ORDER BY c_exe";
$result = sql_rowset($sql, $exe);

foreach ($result as $row) {
	$str .= '<li>' . $row['c_exe'] . '</li>';
}

echo '<ul>' . $str . '</ul>';
	
?>