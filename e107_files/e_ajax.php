<?php
/*
+ ----------------------------------------------------------------------------+
||     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_files/e_ajax.php,v $
|     $Revision: 1.5 $
|     $Date: 2008-11-09 20:33:24 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/
$_E107['minimal'] = TRUE;
require_once("../class2.php");
//ob_start();
ob_implicit_flush(0);

// -----------------------------------------------------------------------------
	// Ajax Short-code-Replacer Routine.

	$shortcodes = "";

	if($_POST['ajax_sc'] && $_POST['ajax_scfile'])
	{
		include_once(e_HANDLER.'shortcode_handler.php');
	 	$file = $tp->replaceConstants($_POST['ajax_scfile']);
		$shortcodes = $tp -> e_sc -> parse_scbatch($file);
	}

	if($_POST['ajax_sc'] && $_POST['ajax_used'])
	{
		list($fld,$parm) = explode("=",$_POST['ajax_sc']);
		$prm = ($parm) ? "=".$parm : "";
		echo $tp->parseTemplate("{".strtoupper($fld).$prm."}",TRUE,$shortcodes);
		exit;
	}
?>