<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

define('e107_INIT', true);
require_once(realpath(dirname(__FILE__)."/secure_img_handler.php"));

$sim = new secure_image();
$sim->render($_SERVER['QUERY_STRING']);

exit;


?>