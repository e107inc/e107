<?php
/*
+ ----------------------------------------------------------------------------+
||     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc (e107.org)
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_files/e_ajax.php,v $
|     $Revision: 1.8 $
|     $Date: 2009-11-18 01:04:42 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
$_E107['minimal'] = TRUE;
require_once("../class2.php");
//ob_start();
ob_implicit_flush(0);

// -----------------------------------------------------------------------------
	// Ajax Short-code-Replacer Routine.

	$shortcodes = "";
	// FIXME - new .php shortcodes & security (require_once)
	if($_POST['ajax_sc'] && $_POST['ajax_scfile'])
	{
		include_once(e_HANDLER.'shortcode_handler.php');
	 	$file = $tp->replaceConstants($_POST['ajax_scfile']);
		$shortcodes = $tp -> e_sc -> parse_scbatch($file);
	}

	if(vartrue($_POST['ajax_sc']) && e_AJAX_REQUEST)
	{
		list($fld,$parm) = explode("=", $_POST['ajax_sc'], 2);
		$prm = ($parm) ? "=".rawurldecode($parm) : ""; //var_dump($_GET);
		echo e107::getParser()->parseTemplate("{".strtoupper($fld).$prm."}", true, $shortcodes);
		exit;
	}
?>