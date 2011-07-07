<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 * $URL$
 * $Id$
 */

/*   Example Custom secure_image_custom.php file:
	<?php

	$secureimg['image'] = "code_bg_custom";  // filename excluding the .ext
	$secureimg['size']	= "15";
	$secureimg['angle']	= "0";
	$secureimg['x']		= "6";
	$secureimg['y']		= "22";
	$secureimg['font'] 	= "imagecode.ttf";
	$secureimg['color'] = "90,90,90"; // red,green,blue

	 ?>
*/

define('e107_INIT', true);
require_once(realpath(dirname(__FILE__)."/secure_img_handler.php"));

$sim = new secure_image();
$sim->render($_SERVER['QUERY_STRING']);

exit;

?>