<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin - newsfeeds
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/newsfeed/newsfeed_functions.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

/*
If cache is disabled, first call to this object reads the complete database of active feeds, including the actual news feed. So all data is available for
the rest of the page.

If cache is enabled, only the feed list (excluding the news feed data) is loaded. Individual news feeds are stored in separate cache files, and loaded on demand. If the
feed refresh time has expired, the cache is updated.
*/

if (!defined('e107_INIT')) { exit; }
if (!e107::isInstalled('newsfeed')) 
{
	return;
}

define('NEWSFEED_LIST_CACHE_TAG', 'nomd5_newsfeeds');
define('NEWSFEED_NEWS_CACHE_TAG', 'nomd5_newsfeeds_news_');

define('NEWSFEED_DEBUG', FALSE);


class newsfeedClass
{
	var $validFeedList;					// True once feeds read
	var	$feedList = array();			// List of available feeds read from DB - everything from DB apart from the actual news
	var $feedIcon = array();			// Pre-calculated link to each feed's icon
	var $newsList = array();			// Actual news element for each feed
	var $lastProcessed;					// Note time when processFeeds() last run
	var $truncateCount;					// Number of characters to show in feeds in menus
	var $truncateMore;					// '...more' string
	var	$useCache;						// Set if cache is available

	// Constructor
	function newsfeedClass()
	{
		global $e107;
		$this->validFeedList = FALSE;
		$this->newsList = array();
		$this->feedList = array();
		$this->feedIcon = array();
		$this->lastProcessed = 0;
		$this->truncateCount = 150;			// Set a pref for these two later
		$this->truncateMore = '...';
		$this->useCache = $e107->ecache->UserCacheActive;		// Have our own local copy - should be faster to access
	}

	// Ensures the feed list is loaded - uses cache if available
	function readFeedList($force=FALSE)
	{
		$sql = e107::getDb();
		
		if ($this->validFeedList && !$force)
		{
			return;		// Already got list
		}
		if($this->useCache) // Cache enabled - try to read from that first
		{	
		
			$eArrayStorage = e107::getArrayStorage();
			
			global $e107;
			
			
			if (!$force && $temp = $e107->ecache->retrieve(NEWSFEED_LIST_CACHE_TAG))
			{
				$this->feedList = e107::unserialize($temp);
				return;
			}
		}

		$fieldList = '*';
		
		if ($this->useCache)
		{	// Get all fields except the actual news
			$fieldList = 'newsfeed_id, newsfeed_name, newsfeed_url, newsfeed_timestamp, newsfeed_description, newsfeed_image, newsfeed_active, newsfeed_updateint';
		}
		
		if ($sql->select("newsfeed", $fieldList, '`newsfeed_active` > 0'))		// Read in all the newsfeed info on the first go
		{
			while ($row = $sql->fetch(MYSQL_ASSOC))
			{
				$nfID = $row['newsfeed_id'];
				
				if (isset($row['newsfeed_data']))
				{
					$this->newsList[$nfID]['newsfeed_data'] = $row['newsfeed_data'];		// Pull out the actual news - might as well since we're here
					$this->newsList[$nfID]['newsfeed_timestamp'] = $row['newsfeed_timestamp'];	
					
					unset($row['newsfeed_data']);			// Don't keep this in memory twice!
				}
				
				$this->feedList[$nfID] = $row;						// Put the rest into the feed data
			}
			$this->validFeedList = TRUE;
		}
		
		if ($this->useCache)
		{	// Cache enabled - we need to save some updated info
			$temp = e107::serialize($this->feedList, FALSE);
			e107::getCache()->set(NEWSFEED_LIST_CACHE_TAG,$temp);
		}
	}


	// Returns the info for a single feed - from cache or memory as appropriate. If time expired, updates the feed.
	function getFeed($feedID, $force = FALSE)
	{
		global $e107, $admin_log;
		
		$tp = e107::getParser();
		$sql = e107::getDb();
		$eArrayStorage = e107::getArrayStorage();

		$this->readFeedList();				// Make sure we've got the feed data.

		if (!isset($this->feedList[$feedID]))
		{
			if (NEWSFEED_DEBUG) echo "Invalid feed number: {$feedID}<br />";
			return FALSE;
		}
		
		if(strpos($this->newsList[$feedID]['newsfeed_data'],'MagpieRSS')) //BC Fix to update newsfeed_data from v1 to v2 spec. 
		{
			$force = true;
		}
		
		if ($force || !isset($this->newsList[$feedID]['newsfeed_data']) || !$this->newsList[$feedID]['newsfeed_data'])
		{	// No data already in memory
			if ($force || !($this->newsList[$feedID]['newsfeed_data'] = $e107->ecache->retrieve(NEWSFEED_NEWS_CACHE_TAG.$feedID, $this->feedList[$feedID]['newsfeed_updateint']/60)))
			{	// Need to re-read from source - either no cached data yet, or cache expired
			
				if (NEWSFEED_DEBUG)
				{
					 $admin_log->e_log_event(10,debug_backtrace(),"DEBUG","Newsfeed update","Refresh item: ".$feedID,FALSE,LOG_TO_ROLLING);
				}
				
				require_once(e_HANDLER.'xml_class.php');
				$xml = new xmlClass;
				require_once(e_HANDLER.'magpie_rss.php');
				
				$dbData = array();		// In case we need to update DB
				
				if($rawData = $xml->getRemoteFile($this->feedList[$feedID]['newsfeed_url'])) // Need to update feed
				{	
					$rss = new MagpieRSS( $rawData );
					list($newsfeed_image, $newsfeed_showmenu, $newsfeed_showmain) = explode("::", $this->feedList[$feedID]['newsfeed_image']);
					
					$temp['channel'] = $rss->channel;
					
					if (($newsfeed_showmenu == 0) || ($newsfeed_showmain == 0))
					{
						$temp['items'] = $rss->items;		// Unlimited items
					}
					else
					{
						$temp['items'] = array_slice($rss->items, 0, max($newsfeed_showmenu, $newsfeed_showmain));		// Limited items
					}

					$newsfeed_des = FALSE;
					
					if($this->feedList[$feedID]['newsfeed_description'] == 'default')
					{
						$temp['newsfeed_description'] = 'default';		// This prevents db writes if no better data found
						
						if($rss->channel['description'])
						{
							$newsfeed_des = $tp -> toDB($rss->channel['description']);
							$temp['newsfeed_description'] = $newsfeed_des;
						}
						elseif($rss->channel['tagline'])
						{
							$newsfeed_des = $tp -> toDB($rss -> channel['tagline']);
							$temp['newsfeed_description'] = $newsfeed_des;
						}
					
						if ($temp['newsfeed_description'] != $this->feedList[$feedID]['newsfeed_description'])
						{	// Need to write updated feed name to DB
							$this->feedList[$feedID]['newsfeed_description'] = $temp['newsfeed_description'];
							$dbData['newsfeed_description'] = $temp['newsfeed_description'];
							if ($this->useCache)
							{
								$e107->ecache->clear(NEWSFEED_LIST_CACHE_TAG);		// Clear the newsfeed cache so its re-read next time
							}
						}
					}

					if ($newsfeed_image == 'default')
					{
						$temp['newsfeed_image_link'] =  "<a href='".$rss->image['link']."' rel='external'><img src='".$rss->image['url']."' alt='".$rss->image['title']."' style='vertical-align: middle;' /></a>";
					}
					else
					{
						$temp['newsfeed_image_link'] = "<img src='".$this->feedList[$feedID]['newsfeed_image']."' alt='' />";
					}
					
					$serializedArray = $eArrayStorage->WriteArray($temp, FALSE);

					$now = time();
					$this->newsList[$feedID]['newsfeed_data'] = $serializedArray;
					$this->newsList[$feedID]['newsfeed_timestamp'] = $now;

					if ($this->useCache)
					{
						$e107->ecache->set(NEWSFEED_NEWS_CACHE_TAG.$feedID,$serializedArray);
					}
					else
					{
						$dbData['newsfeed_data'] =addslashes($serializedArray);
						$dbData['newsfeed_timestamp'] = $now;
					}
					
					if (count($dbData)) // Only write the feed data to DB if not using cache. Write description if changed
					{	
						if(FALSE === $sql->db_UpdateArray('newsfeed', $dbData, " WHERE newsfeed_id=".$feedID))
						{
							if (NEWSFEED_DEBUG) echo NFLAN_48."<br /><br />".var_dump($dbData);
						}
					}
					unset($rss);
				}
				else
				{
					if (NEWSFEED_DEBUG) echo $xml -> error;
					return FALSE;
				}
			}
		}

		return  e107::unserialize($this->newsList[$feedID]['newsfeed_data']);
	}




	// Return text for the required news feeds (loads info as necessary)
	// Uses different templates for main and menu areas
	function newsfeedInfo($which, $where = 'main')
	{

		$tp = e107::getParser();
		$sql = e107::getDb();
		
		global $NEWSFEED_MAIN_START, $NEWSFEED_MAIN, $NEWSFEED_MAIN_END;
		global $NEWSFEED_MENU_START, $NEWSFEED_MENU, $NEWSFEED_MENU_END;

		if($which == 'all')
		{
			$filter = 0;
		}
		else
		{
			$filter = intval($which);
		}

		$text = "";
		$this->readFeedList();			// Make sure we've got all the news feeds loaded

		/* get template */
		if (file_exists(THEME."newsfeed_menu_template.php"))
		{
			include(THEME."newsfeed_menu_template.php");
		}
		else
		{
			include(e_PLUGIN."newsfeed/templates/newsfeed_menu_template.php");
		}

		foreach($this->feedList as $nfID => $feed)
		{
			if (($filter == 0) || ($filter == $feed['newsfeed_id']))
			{
				if (($rss = $this->getFeed($nfID)))	// Call ensures that feed is updated if necessary
				{
					list($newsfeed_image, $newsfeed_showmenu, $newsfeed_showmain) = explode("::", $feed['newsfeed_image']);
					
					$numtoshow = intval($where == 'main' ? $newsfeed_showmain : $newsfeed_showmenu);
					$numtoshow = ($numtoshow > 0 ? $numtoshow : 999);

					$FEEDNAME = "<a href='".e_SELF."?show.{$feed['newsfeed_id']}'>".$tp->toHtml($feed['newsfeed_name'],false,'TITLE')."</a>";
					$FEEDDESCRIPTION = $feed['newsfeed_description'];
					$FEEDIMAGE = $rss['newsfeed_image_link'];
					$FEEDLANGUAGE = $rss['channel']['language'];
	
					if($rss['channel']['lastbuilddate'])
					{
						$pubbed = $rss['channel']['lastbuilddate'];
					}
					else if($rss['channel']['dc']['date'])
					{
						$pubbed = $rss['channel']['dc']['date'];
					}
					else
					{
						$pubbed = NFLAN_34;
					}
	
					$FEEDLASTBUILDDATE = NFLAN_33.$pubbed;
					$FEEDCOPYRIGHT = $tp -> toHTML(vartrue($rss['channel']['copyright']), FALSE);
					$FEEDTITLE = "<a href='".$rss['channel']['link']."' rel='external'>".vartrue($rss['channel']['title'])."</a>";
					$FEEDLINK = $rss['channel']['link'];
					
					if($feed['newsfeed_active'] == 2 or $feed['newsfeed_active'] == 3)
					{
						$LINKTOMAIN = "<a href='".e_PLUGIN."newsfeed/newsfeed.php?show.".$feed['newsfeed_id']."'>".NFLAN_39."</a>";
					}
					else
					{
						$LINKTOMAIN = "";
					}
	
					$data = "";
	
					$numtoshow = min($numtoshow, count($rss['items']));
					$i = 0;
					while($i < $numtoshow)
					{
						$item = $rss['items'][$i];
						
						$FEEDITEMLINK = "<a href='".$item['link']."' rel='external'>".$tp -> toHTML($item['title'], FALSE)."</a>\n";
						$FEEDITEMLINK = str_replace('&', '&amp;', $FEEDITEMLINK);
						$feeditemtext = preg_replace("#\[[a-z0-9=]+\]|\[\/[a-z]+\]|\{[A-Z_]+\}#si", "", strip_tags($item['description']));
						$FEEDITEMCREATOR = $tp -> toHTML(vartrue($item['author']), FALSE);
						
						if ($where == 'main')
						{
							if($NEWSFEED_COLLAPSE)
							{
								$FEEDITEMLINK = "<a href='#' onclick='expandit(this)'>".$tp -> toHTML($item['title'], FALSE)."</a>
								<div style='display:none' >
								";
								$FEEDITEMTEXT = preg_replace("/&#091;.*]/", "", $tp -> toHTML($item['description'], FALSE))."
								<br /><br /><a href='".$item['link']."' rel='external'>".NFLAN_44."</a><br /><br />
								</div>";
							}
							else
							{
								$FEEDITEMLINK = "<a href='".$item['link']."' rel='external'>".$tp -> toHTML($item['title'], FALSE)."</a>\n";
								$FEEDITEMLINK = str_replace('&', '&amp;', $FEEDITEMLINK);
								$feeditemtext = preg_replace("#\[[a-z0-9=]+\]|\[\/[a-z]+\]|\{[A-Z_]+\}#si", "", $item['description']);
								$FEEDITEMTEXT = $tp -> toHTML($feeditemtext, FALSE)."\n";
							}
							$data .= preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MAIN);
						}
						else
						{
							if ($this->truncateCount)
							{
								$FEEDITEMTEXT = $tp->text_truncate($feeditemtext, $this->truncateCount, $this->truncateMore);
							}
							else
							{
								$FEEDITEMTEXT = '';			// Might just want title
							}
							$data .= preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MENU);
						}
						$i++;
					}
				}
			}

			if ($where == 'main')
			{
				$BACKLINK = "<a href='".e_SELF."'>".NFLAN_31."</a>";
				$text = preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MAIN_START).$data.preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MAIN_END);
			}
			else
			{
				$text .= preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MENU_START) . $data . preg_replace("/\{(.*?)\}/e", '$\1', $NEWSFEED_MENU_END);
			}
		}

		if($which == 'all')
		{
			$ret['title'] = $NEWSFEED_MENU_CAPTION;
		}
		else
		{
			$ret['title'] = $feed['newsfeed_name']." ".$NEWSFEED_MAIN_CAPTION;
		}
		$ret['text'] = $text;

		return $ret;
	}
}


?>
