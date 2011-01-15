<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     Copyright (C) 2008-2011 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

global $tp,$PLUGINS_DIRECTORY;

include_lan(e_PLUGIN."rss_menu/languages/".e_LANGUAGE.".php"); // multilanguage fix. 

$rssQuery = "rss_class='0' AND rss_limit>0 ";

// Feed links should display on their corresponding pages. 

// News and it's Categories
if(e_PAGE  == "news.php")
{
	if(e_QUERY)
	{
		$var = explode(".",e_QUERY);
	}
	
	$rssQuery .= "AND (rss_url = 'news' OR rss_url = '1') ";	
	
	if(($var[0] == "list" || $var[0] == "cat") && isset($var[1]))
	{
		$rssQuery .= " AND rss_topicid = ".intval($var[1]);
	}
	else
	{
		$rssQuery .= " AND rss_topicid = 0";	
	}
}

if(e_PAGE == "download.php")
{
	if(e_QUERY)
	{
		$var = explode(".",e_QUERY);
	}
	
	
	$rssQuery .= "AND (rss_url = 'download' OR rss_url = '12') ";	
	
	if(($var[0] == "list") && isset($var[1]))
	{
		$rssQuery .= " AND rss_topicid = ".intval($var[1]);
	}
	else
	{
		$rssQuery .= " AND rss_topicid = 0";	
	}
	
}


$rssQuery .= " ORDER BY rss_url,rss_topicid";	

	if($sql->db_Select("rss", "*", $rssQuery))
	{
   		while($row=$sql->db_Fetch()){
	  		//wildcard topic_id's should not be listed
	   		if(strpos($row['rss_url'], "*")===FALSE){
		  		$url = SITEURL.$PLUGINS_DIRECTORY."rss_menu/rss.php?".$tp->toHTML($row['rss_url'], TRUE, 'constants, no_hook, emotes_off').".2";
				$url .= ($row['rss_topicid']) ? ".".$row['rss_topicid'] : "";
		  		$name = $tp->toHTML($row['rss_name'], TRUE, 'no_hook, emotes_off,defs');
		   		echo "<link rel='alternate' type='application/rss+xml' title='".htmlspecialchars(SITENAME, ENT_QUOTES, CHARSET)." ".htmlspecialchars($name, ENT_QUOTES, CHARSET)."' href='".$url."' />\n";
				
			}
		}
	}
unset($rssQuery,$var);	
echo "\n\n";
?>