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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/newsfeed/newsfeed_functions.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-03-03 04:00:18 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

function checkUpdate($query = "newsfeed_active=2 OR newsfeed_active=3")
{
	global $sql, $tp;
	require_once(e_HANDLER."xml_class.php");
	$xml = new parseXml;
	require_once(e_HANDLER."magpie_rss.php");
	
	if ($sql -> db_Select("newsfeed", "*", $query))
	{
		$feedArray = $sql -> db_getList();
		foreach($feedArray as $feed)
		{
			extract ($feed);
			if($newsfeed_timestamp + $newsfeed_updateint < time())
			{
				if($rawData = $xml -> getRemoteXmlFile($newsfeed_url))
				{
					$rss = new MagpieRSS( $rawData );

					$serializedArray = addslashes(serialize($rss));

					$newsfeed_des = FALSE;
					if($newsfeed_description == "default")
					{
						if($rss -> channel['description'])
						{
							$newsfeed_des = $tp -> toDB($rss -> channel['description']);
						}
						else if($rss -> channel['tagline'])
						{
							$newsfeed_des = $tp -> toDB($rss -> channel['tagline']);
						}
					}
	
					if(!$sql->db_Update('newsfeed', "newsfeed_data='$serializedArray', newsfeed_timestamp=".time().($newsfeed_des ? ", newsfeed_description='$newsfeed_des'": "")." WHERE newsfeed_id=$newsfeed_id"))
					{
						echo "Unable to save raw data in database.<br /><br />".$serializedArray;
					}
				}
				else
				{
					echo $xml -> error;
				}
			}
		}
	}
}


function newsfeed_info($which)
{
	global $tp, $sql;
	if($which == 'all')
	{
		$qry = "newsfeed_active=1 OR newsfeed_active=3";
	}
	else
	{
		$qry = "newsfeed_id = {$which}";
	}
	
	$text = "";

	checkUpdate($qry);

	/* get template */
	if (file_exists(THEME."newsfeed_menu_template.php"))
	{
		include(THEME."newsfeed_menu_template.php");
	}
	else
	{
		include(e_PLUGIN."newsfeed/templates/newsfeed_menu_template.php");
	}
	
	if ($feeds = $sql -> db_Select("newsfeed", "*", $qry))
	{
		while($row = $sql->db_Fetch())
		{
			extract ($row);
			$rss = unserialize($newsfeed_data);
			$FEEDNAME = "<a href='".e_SELF."?show.$newsfeed_id'>$newsfeed_name</a>";
			$FEEDDESCRIPTION = $newsfeed_description;
			if($newsfeed_image == "default")
			{
				if($file = fopen ($rss -> image['url'], "r"))
				{
					/* remote image exists - use it! */
					$FEEDIMAGE = "<a href='".$rss -> image['link']."' rel='external'><img src='".$rss -> image['url']."' alt='".$rss -> image['title']."' style='border: 0; vertical-align: middle;' /></a>";
				}
				else
				{
					/* remote image doesn't exist - ghah! */
					$FEEDIMAGE = "";
				}
			}
			else if ($newsfeed_image)
			{
				$FEEDIMAGE = $newsfeed_image;
			}
			else
			{
				$FEEDIMAGE = "";
			}
			$FEEDLANGUAGE = $rss -> channel['language'];
			$FEEDLASTBUILDDATE = NFLAN_33.($rss -> channel['lastbuilddate'] ? $rss -> channel['lastbuilddate'] : NFLAN_34);
			$FEEDCOPYRIGHT = $tp -> toHTML($rss -> channel['copyright'], TRUE);
			$FEEDTITLE = "<a href='".$rss -> channel['link']."' rel='external'>".$rss -> channel['title']."</a>";
			$FEEDLINK = $rss -> channel['link'];
			if($newsfeed_active == 2 or $newsfeed_active == 3)
			{
				$LINKTOMAIN = "<a href='".e_PLUGIN."newsfeed/newsfeed.php?show.$newsfeed_id'>".NFLAN_39."</a>";
			}
			else
			{
				$LINKTOMAIN = "";
			}

			$data = "";

			$items = array_slice($rss->items, 0, 10);

			foreach ($items as $item)
			{
				$FEEDITEMLINK = "<a href='".$item['link']."' rel='external'>".$tp -> toHTML($item['title'], TRUE)."</a>";
				$feeditemtext = strip_tags(ereg_replace("&#091;.*]", "", $tp -> toHTML($item['description'], TRUE)));
				$FEEDITEMTEXT = (strlen($feeditemtext) > $truncate ? substr($feeditemtext, 0, $truncate).$truncate_string : $feeditemtext);
				$FEEDITEMCREATOR = $tp -> toHTML($item['author'], TRUE);
				$data .= preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MENU);
			}

			$BACKLINK = "<a href='".e_SELF."'>".NFLAN_31."</a>";
			$text .= preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MENU_START) . $data . preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MENU_END);
		}
	}
	
	if($which == 'all')
	{
		$ret['title'] = NFLAN_38;
	}
	else
	{
		$ret['title'] = $newsfeed_name." ".NFLAN_38;
	}
	$ret['text'] = $text;
	
	
	return $ret;
}



?>