<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Tagwords Admin
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/tagwords/admin_tagwords_config.php,v $
 * $Revision$
 * $Date$
 * $Author$
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

require_once(e_PLUGIN."tagwords/tagwords_class.php");
$tag = new tagwords();

//update preferences
if(isset($_POST['updatesettings']))
{
	$tag->update_prefs();
	$message = LAN_TAG_ADMIN_1;
}

if(isset($message))
{
	$tag->show_message($message, $caption='', $type='ns');
}

$tag->tagwords_options();

require_once(e_ADMIN."footer.php");
?>