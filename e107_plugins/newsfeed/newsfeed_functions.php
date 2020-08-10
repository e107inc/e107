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

define('NEWSFEED_LIST_CACHE_TAG', 'newsfeeds'.e_LAN."_");
define('NEWSFEED_NEWS_CACHE_TAG', 'newsfeeds_news_'.e_LAN."_");

define('NEWSFEED_DEBUG', false);


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
	function __construct()
	{
		$this->validFeedList = FALSE;
		$this->newsList = array();
		$this->feedList = array();
		$this->feedIcon = array();
		$this->lastProcessed = 0;
		$this->truncateCount = 150;			// Set a pref for these two later
		$this->truncateMore = '...';
		$this->useCache = true; // e107::getCache()->UserCacheActive;		// Have our own local copy - should be faster to access
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

			if (!$force && $temp = e107::getCache()->retrieve(NEWSFEED_LIST_CACHE_TAG))
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
			while ($row = $sql->fetch())
			{
				$nfID = $row['newsfeed_id'];
				
				if (!empty($row['newsfeed_data']))
				{
					$this->newsList[$nfID]['newsfeed_data'] = $row['newsfeed_data'];		// Pull out the actual news - might as well since we're here

					
					unset($row['newsfeed_data']);			// Don't keep this in memory twice!
				}

				$this->newsList[$nfID]['newsfeed_timestamp'] = $row['newsfeed_timestamp'];
				
				$this->feedList[$nfID] = $row;						// Put the rest into the feed data
			}
			$this->validFeedList = TRUE;
		}
		
		if ($this->useCache) // Cache enabled - we need to save some updated info
		{
			$temp = e107::serialize($this->feedList, FALSE);
			e107::getCache()->set(NEWSFEED_LIST_CACHE_TAG,$temp);
		}
	}


	// Returns the info for a single feed - from cache or memory as appropriate. If time expired, updates the feed.
	function getFeed($feedID, $force = FALSE)
	{
		$tp = e107::getParser();
		$sql = e107::getDb();

		$this->readFeedList();				// Make sure we've got the feed data.

		if (!isset($this->feedList[$feedID]))
		{
			if (NEWSFEED_DEBUG) echo "Invalid feed number: {$feedID}<br />";
			return FALSE;
		}

		$maxAge =  ($this->feedList[$feedID]['newsfeed_updateint']/60);

		if($maxAge < 1){ $maxAge = 1; }

	//	e107::getDebug()->log("NewsFeed #".$feedID." MaxAge: ".$maxAge);

		$cachedData  = e107::getCache()->retrieve(NEWSFEED_NEWS_CACHE_TAG.$feedID,$maxAge, true);

		if(empty($this->newsList[$feedID]['newsfeed_timestamp']) || empty($cachedData) || strpos($this->newsList[$feedID]['newsfeed_data'],'MagpieRSS')) //BC Fix to update newsfeed_data from v1 to v2 spec.
		{
			$force = true;
			// e107::getDebug()->log("NewsFeed Force");
		}

		if($cachedData !== false && $force === false)
		{
			e107::getDebug()->log("NewsFeed Cache Used");
			$this->newsList[$feedID]['newsfeed_data'] = $cachedData;
		}

		if ($force === true) // Need to re-read from source - either no cached data yet, or cache expired
		{
				e107::getDebug()->log("NewsFeed Update: Item #".$feedID." ".NEWSFEED_NEWS_CACHE_TAG);

				if (NEWSFEED_DEBUG)
				{
					 e107::getLog()->e_log_event(10,debug_backtrace(),"DEBUG","Newsfeed update","Refresh item: ".$feedID,FALSE,LOG_TO_ROLLING);
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
								e107::getCache()->clear(NEWSFEED_LIST_CACHE_TAG);		// Clear the newsfeed cache so its re-read next time
							}
						}
					}

					if ($newsfeed_image == 'default')
					{
						$temp['newsfeed_image_link'] =  "<a href='".$rss->image['link']."' rel='external'><img src='".$rss->image['url']."' alt='".$rss->image['title']."' style='vertical-align: middle;' /></a>";
					}
					else
					{
						$temp['newsfeed_image_link'] = !empty($newsfeed_image) ? "<img src='".$newsfeed_image."' alt='' />" : '';
					}
					
					$serializedArray = e107::serialize($temp, false);

					$now = time();
					$this->newsList[$feedID]['newsfeed_data'] = $serializedArray;
					$this->newsList[$feedID]['newsfeed_timestamp'] = $now;

					if ($this->useCache)
					{
					//	e107::getDebug()->log("Saving Cache");
						e107::getCache()->set(NEWSFEED_NEWS_CACHE_TAG.$feedID, $serializedArray, true);
					}

					$dbData['newsfeed_data'] = $serializedArray;
					$dbData['newsfeed_timestamp'] = $now;

					
					if (count($dbData)) // Only write the feed data to DB if not using cache. Write description if changed
					{

						$dbData['WHERE'] = "newsfeed_id=".$feedID;



						if(FALSE === $sql->update('newsfeed', $dbData))
						{
							// e107::getDebug()->log("NewsFeed DB Update Failed");
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

		return  e107::unserialize($this->newsList[$feedID]['newsfeed_data']);
	}




	// Return text for the required news feeds (loads info as necessary)
	// Uses different templates for main and menu areas
	function newsfeedInfo($which, $where = 'main')
	{

		$tp = e107::getParser();

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

		if(file_exists(THEME."templates/newsfeed/newsfeed_menu_template.php")) //v2.x
		{
			include(THEME."templates/newsfeed/newsfeed_menu_template.php");
		}
		elseif(file_exists(THEME."newsfeed_menu_template.php")) //v1.x
		{
			include(THEME."newsfeed_menu_template.php");
		}
		else
		{
			include(e_PLUGIN."newsfeed/templates/newsfeed_menu_template.php");
		}

		$vars = array();

		foreach($this->feedList as $nfID => $feed)
		{

			$feed['newsfeed_sef'] = eHelper::title2sef($feed['newsfeed_name'], 'dashl');

			if (($filter == 0) || ($filter == $feed['newsfeed_id']))
			{
				if (($rss = $this->getFeed($nfID)))	// Call ensures that feed is updated if necessary
				{
					list($newsfeed_image, $newsfeed_showmenu, $newsfeed_showmain) = explode("::", $feed['newsfeed_image']);
					
					$numtoshow = intval($where == 'main' ? $newsfeed_showmain : $newsfeed_showmenu);
					$numtoshow = ($numtoshow > 0 ? $numtoshow : 999);

					// $url = e_PLUGIN_ABS."newsfeed/newsfeed.php?show.".$feed['newsfeed_id'];
					$url = e107::url('newsfeed','source',$feed);

					$vars['FEEDNAME'] = "<a href='".$url."'>".$tp->toHTML($feed['newsfeed_name'],false,'TITLE')."</a>";
					$vars['FEEDDESCRIPTION'] = $feed['newsfeed_description'];
					$vars['FEEDIMAGE'] = $rss['newsfeed_image_link'];
					$vars['FEEDLANGUAGE'] = $rss['channel']['language'];
					
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

					if(empty($rss['channel']['link']) || ($rss['channel']['link'] === '/'))
					{
					    $rss['channel']['link'] = $feed['newsfeed_url'];
					}

					$vars['FEEDLASTBUILDDATE']  = NFLAN_33.$pubbed;
					$vars['FEEDCOPYRIGHT']      = $tp -> toHTML(vartrue($rss['channel']['copyright']), FALSE);
					$vars['FEEDTITLE']          = "<a href='".$rss['channel']['link']."' rel='external'>".vartrue($rss['channel']['title'])."</a>";
					$vars['FEEDLINK']           = $rss['channel']['link'] ;


					if($feed['newsfeed_active'] == 2 or $feed['newsfeed_active'] == 3)
					{
						$vars['LINKTOMAIN'] = "<a href='".$url."'>".NFLAN_39."</a>";
					}
					else
					{
						$vars['LINKTOMAIN'] = "";
					}
	
					$data = "";
	
					$numtoshow = min($numtoshow, count($rss['items']));
					$i = 0;
					while($i < $numtoshow)
					{
						$item = $rss['items'][$i];


						
						$vars['FEEDITEMLINK']       = "<a href='".$item['link']."' rel='external'>".$tp -> toHTML($item['title'], FALSE)."</a>\n";
						$vars['FEEDITEMLINK']       = str_replace('&', '&amp;', $vars['FEEDITEMLINK']);
						$feeditemtext               = preg_replace("#\[[a-z0-9=]+\]|\[\/[a-z]+\]|\{[A-Z_]+\}#si", "", strip_tags($item['description']));
						$vars['FEEDITEMCREATOR']    = $tp -> toHTML(vartrue($item['author']), FALSE);
						
						if ($where == 'main')
						{
							if(!empty($NEWSFEED_COLLAPSE))
							{
								$vars['FEEDITEMLINK'] = "<a href='#' onclick='expandit(this)'>".$tp -> toHTML($item['title'], FALSE)."</a>
								<div style='display:none' >
								";

								$vars['FEEDITEMTEXT'] = preg_replace("/&#091;.*]/", "", $tp -> toHTML($item['description'], FALSE))."
								<br /><br /><a href='".$item['link']."' rel='external'>".LAN_CLICK_TO_VIEW."</a><br /><br />
								</div>";
							}
							else
							{
								$vars['FEEDITEMLINK']   = "<a href='".$item['link']."' rel='external'>".$tp -> toHTML($item['title'], FALSE)."</a>\n";
								$vars['FEEDITEMLINK']   = str_replace('&', '&amp;', $vars['FEEDITEMLINK']);
								$feeditemtext           = preg_replace("#\[[a-z0-9=]+\]|\[\/[a-z]+\]|\{[A-Z_]+\}#si", "", $item['description']);
								$vars['FEEDITEMTEXT']   = $tp -> toHTML($feeditemtext, FALSE)."\n";
							}
							$data .= $tp->simpleParse( $NEWSFEED_MAIN, $vars);
						}
						else
						{
							if ($this->truncateCount)
							{
								$vars['FEEDITEMTEXT'] = $tp->text_truncate($feeditemtext, $this->truncateCount, $this->truncateMore);
							}
							else
							{
								$vars['FEEDITEMTEXT'] = '';			// Might just want title
							}
							$data .= $tp->simpleParse($NEWSFEED_MENU, $vars);
						}
						$i++;
					}
				}
			}

			if ($where == 'main')
			{
				$vars['BACKLINK'] = "<a href='".e_SELF."'>".NFLAN_31."</a>";
				$text = $tp->simpleParse($NEWSFEED_MAIN_START, $vars).$data.$tp->simpleParse( $NEWSFEED_MAIN_END, $vars);
			}
			else
			{
				$text .= $tp->simpleParse($NEWSFEED_MENU_START, $vars) . $data . $tp->simpleParse($NEWSFEED_MENU_END, $vars);
			}

			//TODO Move the $vars into their own shortcode class and change simpleParse to parseTemplate();
		}

		if($which == 'all')
		{
			$ret['title'] = (!empty($NEWSFEED_MENU_CAPTION)) ? $NEWSFEED_MENU_CAPTION : '';
		}
		else
		{
			$ret['title'] = $feed['newsfeed_name']." ".varset($NEWSFEED_MAIN_CAPTION);
		}
		$ret['text'] = $text;

		return $ret;
	}
}



