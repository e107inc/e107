<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ?Steve Dunstan 2001-2002
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
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

if(!function_exists("checkUpdate"))
{
	function checkUpdate($query = "newsfeed_active=2 OR newsfeed_active=3")
	{
		global $sql, $tp;
		require_once(e_HANDLER."xml_class.php");
		$xml = new parseXml;
		require_once(e_HANDLER."magpie_rss.php");

		if ($sql -> db_Select("newsfeed", "*", $tp -> toDB($query, true)))
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

						if(!$sql->db_Update('newsfeed', "newsfeed_data='{$serializedArray}', newsfeed_timestamp=".time().($newsfeed_des ? ", newsfeed_description='{$newsfeed_des}'": "")." WHERE newsfeed_id=".intval($newsfeed_id)))
						{
							echo NFLAN_48."<br /><br />".$serializedArray;
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
}

if(!function_exists("newsfeed_info"))
{
	function newsfeed_info($which, $where = 'main')
	{
		global $tp, $sql;
		if($which == 'all')
		{
			$qry = "newsfeed_active=1 OR newsfeed_active=3";
		}
		else
		{
			$qry = "newsfeed_id = ".intval($which);
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
				list($newsfeed_image, $newsfeed_showmenu, $newsfeed_showmain) = explode("::", $newsfeed_image);
				$numtoshow = ($where == 'main' ? $newsfeed_showmain : $newsfeed_showmenu);
				$numtoshow = (intval($numtoshow) > 0 ? $numtoshow : 999);
				$rss = unserialize($newsfeed_data);

				$FEEDNAME = "<a class='newsfeed-name' href='".e_SELF."?show.$newsfeed_id'>$newsfeed_name</a>";
				$FEEDDESCRIPTION = $newsfeed_description;
				if($newsfeed_image == "default")
				{
					if($file = fopen ($rss -> image['url'], "r"))
					{
						/* remote image exists - use it! */
						$FEEDIMAGE = "<a class='newsfeed-image' href='".$rss -> image['link']."' rel='external'><img src='".$rss -> image['url']."' alt='".$rss -> image['title']."' style='border: 0; vertical-align: middle;' /></a>";
					}
					else
					{
						/* remote image doesn't exist - ghah! */
						$FEEDIMAGE = "";
					}
				}
				else if ($newsfeed_image)
				{
					$FEEDIMAGE = "<img class='newsfeed-image' src='".$newsfeed_image."' alt='' />";
				}
				else
				{
					$FEEDIMAGE = "";
				}
				$FEEDLANGUAGE = $rss -> channel['language'];

				if($rss -> channel['lastbuilddate'])
				{
					$pubbed = $rss -> channel['lastbuilddate'];
				}
				else if($rss -> channel['dc']['date'])
				{
					$pubbed = $rss -> channel['dc']['date'];
				}
				else
				{
					$pubbed = NFLAN_34;
				}

				$FEEDLASTBUILDDATE = NFLAN_33.$pubbed;
				$FEEDCOPYRIGHT = $tp -> toHTML($rss -> channel['copyright'], TRUE);
				$FEEDTITLE = "<a class='newsfeed-title' href='".$rss -> channel['link']."' rel='external'>".$rss -> channel['title']."</a>";
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

				$amount = ($items) ? $items :  $numtoshow;

				$item_total = array_slice($rss->items, 0, $amount);

				$i = 0;
				while($i < $numtoshow && $item_total[$i])
				{
					$item = $item_total[$i];
					$FEEDITEMLINK = "<a class='newsfeed-itemlink' href='".$item['link']."' rel='external'>".$tp -> toHTML($item['title'], TRUE)."</a>\n";
					$FEEDITEMLINK = str_replace('&', '&amp;', $FEEDITEMLINK);
					$feeditemtext = preg_replace("#\[[a-z0-9=]+\]|\[\/[a-z]+\]|\{[A-Z_]+\}#si", "", strip_tags($item['description']));
					$FEEDITEMTEXT = $tp->text_truncate($feeditemtext, $truncate, $truncate_string);

					$FEEDITEMCREATOR = $tp -> toHTML($item['author'], TRUE);
					$data .= preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MENU);
					$i++;
				}

				$BACKLINK = "<a class='newsfeed-backlink' href='".e_SELF."'>".NFLAN_31."</a>";
				$text .= preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MENU_START) . $data . preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MENU_END);
			}
		}

		if($which == 'all')
		{
			$ret['title'] = $NEWSFEED_MENU_CAPTION;
		}
		else
		{
			$ret['title'] = $newsfeed_name." ".$NEWSFEED_MAIN_CAPTION;
		}
		$ret['text'] = $text;

		return $ret;
	}
}


?>