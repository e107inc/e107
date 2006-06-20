<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/rss_menu/rss_meta.php,v $
|     $Revision: 1.10 $
|     $Date: 2006-06-20 06:50:09 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

global $tp;


if($sql->db_Select("rss", "*", "rss_class='0' AND rss_limit>0 ORDER BY rss_name")){
	while($row=$sql->db_Fetch()){
		//wildcard topic_id's should not be listed
		if(strpos($row['rss_url'], "*")===FALSE){
			$url = $tp->toHTML($row['rss_url'], TRUE, 'constants');
			$name = $tp->toHTML($row['rss_name'], TRUE);
			echo "<link rel='alternate' type='application/rss+xml' title='".htmlspecialchars(SITENAME, ENT_QUOTES, CHARSET)." ".$name."' href='".$url."' />\n";
		}
	}
}

?>