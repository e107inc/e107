<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc (e107.org)
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/rss_menu/e_meta.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

global $PLUGINS_DIRECTORY;

$tp = e107::getParser();
$sql = e107::getDb();

if(USER_AREA && $sql->db_Select("rss", "*", "rss_class='0' AND rss_limit>0 ORDER BY rss_name"))
{
	while($row=$sql->db_Fetch())
	{	// Wildcard topic_id's should not be listed
		if(strpos($row['rss_url'], "*")===FALSE)
		{
			$url = SITEURL.$PLUGINS_DIRECTORY."rss_menu/rss.php?".$tp->toHTML($row['rss_url'], TRUE, 'constants, no_hook, emotes_off').".2";
			$url .= ($row['rss_topicid']) ? ".".$row['rss_topicid'] : "";
			$name = $tp->toHTML($row['rss_name'], TRUE, 'no_hook, emotes_off');
			echo "<link rel='alternate' type='application/rss+xml' title='".htmlspecialchars(SITENAME, ENT_QUOTES, 'utf-8')." ".htmlspecialchars($name, ENT_QUOTES, 'utf-8')."' href='".$url."' />\n";
		}
	}
}
?>