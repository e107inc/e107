<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/featurebox/featurebox.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-04-01 11:03:58 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

if($sql -> db_Select("featurebox", "*", "fb_mode=1 AND fb_class IN (".USERCLASS_LIST.") ORDER BY fb_id DESC LIMIT 0,1"))
{
	$entry = $sql -> db_Fetch();
	extract($entry);
}
else if($sql -> db_Select("featurebox", "*", "fb_mode!=1 AND fb_class IN (".USERCLASS_LIST.")"))
{
	$nfArray = $sql -> db_getList();
	$entry = $nfArray[array_rand($nfArray)];
	extract($entry);
}

$fbcc = $fb_title;
$fb_title = $tp -> toHTML($fb_title, TRUE);
$fb_text = $tp -> toHTML($fb_text, TRUE);
if(!$fb_rendertype)
{
	$ns -> tablerender($fb_title, $fb_text);
}
else 
{
	require_once(e_PLUGIN."featurebox/templates/".$fb_template.".php");
	echo $FB_TEMPLATE;
}
?>