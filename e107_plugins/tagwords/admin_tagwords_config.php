<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Tagwords Admin
 *
*/

require_once("../../class2.php");
if (!getperms("P"))
{
	header("location:".e_BASE."index.php");
	exit ;
}
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."userclass_class.php");
$mes = e107::getMessage();

require_once(e_PLUGIN."tagwords/tagwords_class.php");
$tag = new tagwords();

//update preferences
if(isset($_POST['updatesettings']))
{
	$tag->update_prefs();
	$mes->addSuccess(LAN_UPDATED);
}

$ns->tablerender($caption, $mes->render() . $text);

$tag->tagwords_options();

require_once(e_ADMIN."footer.php");
?>