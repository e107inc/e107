<?php
/*
* e107 website system
*
* Copyright (c) 2008-2009 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Call Shortcodes via AJAX request
*
* $Source: /cvs_backup/e107_0.8/e107_files/e_ajax.php,v $
* $Revision$
* $Date$
* $Author$
*
*/
$_E107['minimal'] = TRUE;
require_once("../class2.php");
//ob_start();
ob_implicit_flush(0);

	// Ajax Short-code-Replacer Routine.

	$shortcodes = "";
	// FIXME - new .php shortcodes & security (require_once)
	if($_POST['ajax_sc'] && $_POST['ajax_scfile'])
	{
		//include_once(e_HANDLER.'shortcode_handler.php');
	 	$file = e107::getParser()->replaceConstants($_POST['ajax_scfile']);
		$shortcodes = e107::getScParser()->parse_scbatch($file);
	}

	if(vartrue($_POST['ajax_sc']) && e_AJAX_REQUEST)
	{
		// temporary fix
		global $register_sc;
		if(isset($register_sc) && is_array($register_sc)) // Fix for missing THEME shortcodes.
		{
			 // parse errror fix from the previous commit
			 e107::getScParser()->loadThemeShortcodes();
		}
		list($fld,$parm) = explode("=", $_POST['ajax_sc'], 2);
		$prm = ($parm) ? "=".rawurldecode($parm) : ""; //var_dump($_GET);
		echo e107::getParser()->parseTemplate("{".strtoupper($fld).$prm."}", true, $shortcodes);
		exit;
	}
?>