<?php

define('IN_EX', true);
include('../includes/common.php');

$user->session_start();
$user->plogin();
$user->session_auth();

page_header();

echo '<br /><div class="hr">&nbsp;</div>';

online();

page_footer();

?>