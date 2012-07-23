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
	$content = $tp->toDB($_POST['content']);
	e107::getBB()->setClass($_SESSION['media_category']);
	echo $tp->toHtml($content, true, 'wysiwyg');
	e107::getBB()->clearClass();	
}

if($_POST['mode'] == 'tobbcode')
{
	// echo $_POST['content'];
	$content = stripslashes($_POST['content']);
	echo e107::getBB()->htmltoBBcode($content);	
}



?>