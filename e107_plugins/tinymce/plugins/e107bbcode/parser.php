<?php
/*
 * e107 website system
 *
 * Copyright (C) e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.8/e107_handlers/bbcode_handler.php $
 * $Id: bbcode_handler.php 12778 2012-06-02 08:12:16Z e107coders $
 */
require_once("../../../../class2.php");



if($_POST['mode'] == 'tohtml')
{
	// XXX @Cam possible fix - convert to BB first, see news admin AJAX request/response values for reference why
	$content = stripslashes($_POST['content']);
	$content = e107::getBB()->htmltoBBcode($content);	
	
	$content = $tp->toDB($content);
	e107::getBB()->setClass($_SESSION['media_category']);
	// XXX @Cam this breaks new lines, currently we use \n instead [br]
	//echo $tp->toHtml(str_replace("\n","",$content), true);
	echo $tp->toHtml($content, true);
	e107::getBB()->clearClass();	
}

if($_POST['mode'] == 'tobbcode')
{
	 //echo $_POST['content'];
	$content = stripslashes($_POST['content']);
	echo e107::getBB()->htmltoBBcode($content);	
}



?>