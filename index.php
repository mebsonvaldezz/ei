<?php
// -------------------------------------------------------------
// $Id: index.php,v 1.2 2005/05/02 13:02:00 Psychopsia Exp $
//
// COPYRIGHT : © 2006 UNIS
// WWW       : http://www.unis.edu.gt/
// -------------------------------------------------------------

define('IN_EX', true);
define('SROOT', './');
include(SROOT . 'includes/common.php');

header('Location: ' . $config['saddress'] . s_link('cover'));
exit();

?>