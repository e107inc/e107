<?php
/*
* e107 website system
*
* Copyright (C) 2008-2016 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* RSS Feed management
*
*/

/*
Query string: feed_key.rss_type.[topic id]

feed_key matches an rss_url value in the rss table, and rss_path on that row
names the plugin folder whose e_rss.php builds the feed. Feeds were keyed by
number before v0.7.6; a plugin declares the numbers it still answers to in the
legacy() method of its e_rss.php, which rss_addons::legacyKeys() collects.

Plugins should use an e_rss.php file in their plugin folder
----------------------------------------------------------------
*/
if (!defined('e107_INIT'))
{
	if(!empty($_GET) || !empty($argv))
	{
		$_E107['no_online'] = true;
		$_E107['no_forceuserupdate'] = true;
		$_E107['no_menus'] = true;
		$_E107['allow_guest'] = true; // run while in members-only mode.
		$_E107['no_maintenance'] = true;
	}

	require_once(__DIR__.'/../../class2.php');
}

$e107 = e107::getInstance();

if (!e107::isInstalled('rss_menu'))
{
	e107::redirect();
	exit;
}

$tp = e107::getParser();

//require_once(e_PLUGIN.'rss_menu/rss_shortcodes.php');
require_once(e_HANDLER.'userclass_class.php');

// Get language file
e107::includeLan(e_PLUGIN.'rss_menu/languages/'.e_LANGUAGE.'_admin_rss_menu.php');


// Query handler
if(!empty($_GET['type']))
{
	$content_type 	= $tp->toDB($_GET['cat']);
	$rss_type 		= intval(varset($_GET['type'],0));
	$topic_id 		= $tp->toDB($_GET['topic'],'');
}
elseif(e_QUERY)
{
	$tmp = explode('.', e_QUERY);

	$content_type 	= $tp->toDB($tmp[0]);
	$rss_type		= intval(varset($tmp[1],0));
	$topic_id 		= $tp->toDB($tmp[2],'');
}
else
{
	$content_type 	= false;
	$topic_id 		= false;
}

// List available rss feeds
if (empty($rss_type))
{	
	// Display list of all feeds
	require_once(HEADERF);

	require_once(e_PLUGIN.'rss_menu/rss_shortcodes.php');
	$sc = e107::getScBatch('rss_menu', true);
	$sc->wrapper('rss/page');

	$qb = $sql->createQueryBuilder();
	$rssFeeds = $qb->select('*')->from('rss')
		->where('rss_class', 0)
		->where($qb->expr()->gt('rss_limit', 0))
		->where($qb->expr()->not($qb->expr()->regexp('rss_topicid', "\\\\*")))
		->orderBy('rss_name', 'ASC')
		->fetchAll();

	if(empty($rssFeeds))
	{
		$ns->tablerender(LAN_ERROR, RSS_LAN_ERROR_4);
	}
	else
	{
		if($template = e107::getTemplate('rss_menu', 'rss', 'page'))
		{
			$RSS_LIST_HEADER    = $template['start'];
			$RSS_LIST_TABLE     = $template['item'];
			$RSS_LIST_FOOTER    = $template['end'];
		}
		else
		{
			// Get Legacy template
			if (is_readable(THEME.'rss_template.php'))
			{
				require_once(THEME.'rss_template.php');
			}
			elseif (is_readable(e_PLUGIN.'rss_menu/rss_template.php'))
			{
				require_once(e_PLUGIN.'rss_menu/rss_template.php');
			}

			$RSS_LIST_HEADER = varset($RSS_LIST_HEADER, '');
			$RSS_LIST_TABLE  = varset($RSS_LIST_TABLE, '');
			$RSS_LIST_FOOTER = varset($RSS_LIST_FOOTER, '');
		}

		$text = $tp->parseTemplate($RSS_LIST_HEADER);

		foreach($rssFeeds as $row)
		{
			$sc->setVars($row);
			$text .= $tp->parseTemplate($RSS_LIST_TABLE, false, $sc);
		}

		$text .= $tp->parseTemplate($RSS_LIST_FOOTER);

		$ns->tablerender(RSS_MENU_L2, $text);
	}

 	require_once(FOOTERF);
	exit;
}


	while (ob_get_length() !== false)  // destroy all ouput buffering
	{
        ob_end_clean();
	}

// Returning feeds here

require_once(e_PLUGIN.'rss_menu/rss_addons.php');
require_once(e_PLUGIN.'rss_menu/rss_resolver.php');

// Look up the feed for this content type, optionally constrained to a topic id.
$rssFeedLookup = function($contentType, $topicValue) use ($sql)
{
	$qb = $sql->createQueryBuilder();
	$qb->select('*')->from('rss')
		->where('rss_class', '!=', 2)
		->where('rss_url', $contentType)
		->where($qb->expr()->gt('rss_limit', 0));

	if($topicValue)
	{
		$qb->where('rss_topicid', $topicValue);
	}

	return $qb->fetchRow();
};

$resolver = new rss_feed_resolver($rssFeedLookup);
$resolved = $resolver->resolve($content_type, $topic_id);

if($resolved === false)
{
	require_once(HEADERF);

	$repl  		= array("<br /><br /><a href='".e_REQUEST_SELF."'>", "</a>");
	$message 	= str_replace(array("[","]"), $repl, RSS_LAN_ERROR_1);
	e107::getRender()->tablerender('', $message);

	require_once(FOOTERF);
	exit;
}

$row          = $resolved['row'];
$content_type = $resolved['key'];


// ----------------------------------------------------------------------------

if($rss = new rssCreate($content_type, $rss_type, $topic_id, $row))
{
	$rss_title = ($rss->contentType ? $rss->contentType : ucfirst($content_type));

	if(defset('E107_DEBUG_LEVEL') > 0)
	{
		define('e_IFRAME',true);
		require_once(HEADERF);
		$rss->debug();
		require_once(FOOTERF);
		exit;
	}
	else
	{
		$rss->buildRss($rss_title);
	}
}
else
{
	require_once(HEADERF);
	$ns->tablerender(LAN_ERROR, RSS_LAN_ERROR_1);
	require_once(FOOTERF);

}

class rssCreate
{
	protected $e107;

	var $contentType;
	var $rssType;
	var $path;
	var $parm;
	var $rssItems;
	var $rssQuery;
	var $topicid;
	var $offset;
	var $rssNamespace;
	var $rssCustomChannel;

	private $limit;

	public function __construct($content_type, $rss_type, $topic_id, $row)
	{	// Constructor
		$sql_rs = new db;
		global $rssgen;
		$sql = e107::getDb();
		$tp = e107::getParser();

		$this->e107 = e107::getInstance();

		$this -> rssItems = array();
		$this -> path = e_PLUGIN."rss_menu/";
		$this -> rssType = $rss_type;
		$this -> topicid = $topic_id;
		$this -> limit = $row['rss_limit'];
		$this -> contentType = $row['rss_name'];

		if(!is_numeric($content_type))
		{
			$path = $this->addonPath($content_type, $row);
		}
		if(strpos($row['rss_path'],'|')!==FALSE) //FIXME remove this check completely.
		{
			$tmp = explode("|", $row['rss_path']);
			$path = e_PLUGIN.$tmp[0]."/e_rss.php";
			$this->parm = $tmp[1];	// FIXME @Deprecated - use $parm['url'] instead in data() method within e_rss.php.  Parm is used in e_rss.php to define which feed you need to prepare
		}

		// Feed keys a plugin serves are resolved from the row above. What is left
		// here is what core still answers to itself: three content types from 0.7
		// that never became plugins, and the inline comments feed.
		switch ($content_type)
		{
			case 2:
				$path='';
				$this -> contentType = "articles";
				break;
			case 3:
				$path='';
				$this -> contentType = "reviews";
				break;
			case 4:
				$path='';
				$this -> contentType = "content";
				break;
			case 'comments' : //TODO Eventually move to e107_plugins/comments
				$path='';
				$this -> rssItems = $this->commentItems((int) $this -> limit);
				break;
		}

		if(isset($path) && $path!='')
		{	// New rss reader from e_rss.php in plugin folder
			if (is_readable($path))
			{
				// A feed that has nothing to serve says so by returning nothing,
				// and a v1 e_rss.php sets this itself while being included.
				$eplug_rss_data = array();

				require_once($path);

				$className = basename(dirname($path)).'_rss';

				// v2.x standard
				if($data = e107::callMethod($className,'data', array('url' => $content_type, 'id' => $this->topicid, 'limit' => $this->limit)))
				{
					$eplug_rss_data = array(0 => $data);
					unset($data);
				}

				foreach($eplug_rss_data as $key=>$rs)
				{
					foreach($rs as $k=>$row)
					{
						$this -> rssItems[$k]['author'] = $row['author'];
						$this -> rssItems[$k]['author_email'] = $row['author_email'];
						$this -> rssItems[$k]['title'] = $row['title'];

						if($row['link'])
						{
							if(stripos($row['link'], 'http') !== FALSE)
							{
								$this -> rssItems[$k]['link'] = $row['link'];
							}
							else
							{
								$this -> rssItems[$k]['link'] = SITEURLBASE.e_PLUGIN_ABS.$row['link'];
							}
						}

						$this -> rssItems[$k]['description'] = $row['description'];
						
						if($row['enc_url'])
						{
							$this -> rssItems[$k]['enc_url'] = SITEURLBASE.e_PLUGIN_ABS.$row['enc_url'].$row['item_id'];
						}
						
						if($row['enc_leng'])
						{
							$this -> rssItems[$k]['enc_leng'] = $row['enc_leng'];
						}

						if(!empty($eplug_rss['enc_type']))
						{
							$this -> rssItems[$k]['enc_type'] = $this->getmime($eplug_rss['enc_type']);
						}
						elseif($row['enc_type'])
						{
							$this -> rssItems[$k]['enc_type'] = $row['enc_type'];
						}

						$this -> rssItems[$k]['category_name'] = $row['category_name'];
						
						if($row['category_link'])
						{
							if(stripos($row['category_link'], 'http') !== FALSE)
							{
								$this -> rssItems[$k]['category_link'] = $row['category_link'];
							}
							else
							{
								$this -> rssItems[$k]['category_link'] = SITEURLBASE.e_PLUGIN_ABS.$row['category_link'];
							}
						}
						
						if(!empty($row['datestamp']))
						{
							$this -> rssItems[$k]['pubdate'] = $row['datestamp'];
						}
						else
						{
							$this -> rssItems[$k]['pubdate'] = time();
						}

						if($row['custom'])
						{
							$this -> rssItems[$k]['custom'] = $row['custom'];
						}

						if($row['media'])
						{
							$this -> rssItems[$k]['media'] = $row['media'];
						}
					}
				}
			}
		}
	}

	/**
	 * The things a comment in this feed can be attached to.
	 *
	 * A type this list does not describe is one whose visibility the feed has no
	 * way to establish, so comments of that type are not served. Comments of type
	 * 'profile' are among them: they belong to a member's profile page, which is
	 * not public, so the feed has no version of them it could publish.
	 *
	 * @return array
	 */
	private function commentParents()
	{
		return array(
			'news' => array(
				'types'  => array('0', 'news'),
				'table'  => 'news',
				'key'    => 'news_id',
				'plugin' => '',
			),
			'download' => array(
				'types'  => array('2', 'download'),
				'table'  => 'download',
				'key'    => 'download_id',
				'plugin' => 'download',
			),
			'poll' => array(
				'types'  => array('4', 'poll'),
				'table'  => 'polls',
				'key'    => 'poll_id',
				'plugin' => 'poll',
			),
			'page' => array(
				'types'  => array('page'),
				'table'  => 'page',
				'key'    => 'page_id',
				'plugin' => '',
			),
		);
	}

	/**
	 * Comments the visitor could have reached through the page they were left on.
	 *
	 * comment_blocked is a property of the comment. Whether the item it belongs
	 * to has been published, and who may read it, are properties of that item, so
	 * the feed joins to it and asks there.
	 *
	 * @param int $limit
	 * @return array rss items
	 */
	private function commentItems($limit)
	{
		$http = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
		$base = $http.$_SERVER['HTTP_HOST'].e_HTTP."comment.php?comment.";

		$items = array();

		foreach($this->commentParents() as $name => $parent)
		{
			if(!empty($parent['plugin']) && !e107::isInstalled($parent['plugin']))
			{
				continue;
			}

			foreach($this->visibleComments($name, $parent, $limit) as $row)
			{
				$author = varset($row['comment_author'], '');

				$items[] = array(
					'title'       => $row['comment_subject'],
					'pubdate'     => $row['comment_datestamp'],
					'link'        => $base.$name.".".$row['comment_item_id'],
					'description' => $row['comment_comment'],
					'author'      => substr($author, (strpos($author, ".") + 1)),
				);
			}
		}

		usort($items, array($this, 'byNewestFirst'));

		return array_slice($items, 0, $limit);
	}

	/**
	 * @param array $left
	 * @param array $right
	 * @return int
	 */
	private function byNewestFirst($left, $right)
	{
		if($left['pubdate'] == $right['pubdate'])
		{
			return 0;
		}

		return ($left['pubdate'] > $right['pubdate']) ? -1 : 1;
	}

	/**
	 * The userclass predicate core states for a comma separated class column.
	 *
	 * The column holds a list, so it is matched as one: an IN () would make
	 * MySQL read '254,0' as the number 254, and would admit a list that names
	 * both a class the visitor holds and e_UC_NOBODY.
	 *
	 * @param \e107\Database\QueryBuilder $qb
	 * @param string $column
	 * @return void
	 */
	private function whereClassPermits($qb, $column)
	{
		$qb->where($qb->expr()->regexp($column, e_CLASS_REGEXP))
			->where($qb->expr()->not($qb->expr()->regexp($column, e_NOBODY_REGEXP)));
	}

	/**
	 * @param string $name key from commentParents()
	 * @param array $parent its description
	 * @param int $limit
	 * @return array comment rows
	 */
	private function visibleComments($name, $parent, $limit)
	{
		$now = time();
		$userclass = array_map('intval', explode(',', USERCLASS_LIST));

		$qb = e107::getDb()->createQueryBuilder();
		$qb->select('c.*')
			->from('comments', 'c')
			->innerJoin($parent['table'], 'p', $qb->expr()->compareColumns('p.'.$parent['key'], 'c.comment_item_id'))
			->where('c.comment_blocked', 0)
			->whereIn('c.comment_type', $parent['types']);

		switch($name)
		{
			case 'news':
				$this->whereClassPermits($qb, 'p.news_class');
				$qb->where('p.news_start', '<', $now)
					->where($qb->expr()->anyOf(
						$qb->expr()->eq('p.news_end', 0),
						$qb->expr()->gt('p.news_end', $now)
					));
				break;

			case 'download':
				// download_visible is who may see the item listed, which is what
				// a feed is; download_class is who may then fetch the file.
				$qb->innerJoin('download_category', 'dc',
						$qb->expr()->compareColumns('dc.download_category_id', 'p.download_category'))
					->whereIn('p.download_visible', $userclass)
					->whereIn('p.download_class', $userclass)
					->whereIn('dc.download_category_class', $userclass)
					->where('p.download_active', '!=', 0);
				break;

			case 'poll':
				$qb->where('p.poll_start_datestamp', '<=', $now)
					->where($qb->expr()->anyOf(
						$qb->expr()->eq('p.poll_end_datestamp', 0),
						$qb->expr()->gt('p.poll_end_datestamp', $now)
					));
				break;

			case 'page':
				$this->whereClassPermits($qb, 'p.page_class');
				$qb->where('p.page_password', '');
				break;
		}

		return $qb->orderBy('c.comment_datestamp', 'DESC')
			->setFirstResult(0)->setMaxResults($limit)
			->fetchAll();
	}

	/**
	 * Locates the e_rss.php that serves a feed.
	 *
	 * rss_path names the plugin folder and is normally enough. It is empty on
	 * rows that predate the column, including the news feed the installer still
	 * ships, and those are the rows the removed hardcoded arms were catching. So
	 * fall back to the canonical keys the addons declare in legacy(), which names
	 * the same plugins without core holding the list.
	 *
	 * {@see rss_addons::feeds()} is deliberately not used for this. Addons label
	 * their feeds with admin language constants, so calling config() from a front
	 * end request is fatal. legacy() returns plain arrays and is safe anywhere.
	 *
	 * @param string $content_type feed key
	 * @param array  $row          rss table row
	 * @return string path to the addon, or '' when none serves the feed
	 */
	private function addonPath($content_type, $row)
	{
		$folder = (string) varset($row['rss_path'], '');

		if($folder !== '' && is_readable(e_PLUGIN.$folder.'/e_rss.php'))
		{
			return e_PLUGIN.$folder.'/e_rss.php';
		}

		foreach(rss_addons::legacyKeys() as $owner)
		{
			if($owner['url'] !== (string) $content_type || empty($owner['plugin']))
			{
				continue;
			}

			if(is_readable(e_PLUGIN.$owner['plugin'].'/e_rss.php'))
			{
				return e_PLUGIN.$owner['plugin'].'/e_rss.php';
			}
		}

		return '';
	}

	function debug()
	{
		unset($this->e107);
		print_a($this);
	//	print_a($this -> rssItems);
	}

	function buildRss($rss_title)
	{
		global $pref;

		$tp = e107::getParser();

		header('Content-type: application/xml');

		$rss_title = $tp->toRss($tp->toHTML($pref['sitename'],'','defs')." : ".$tp->toHTML($rss_title,'','defs'));
        $rss_namespace = ($this->rssNamespace) ? "xmlns:".$this->rssNamespace : '';
        $rss_custom_channel = ($this->rssCustomChannel) ? $this->rssCustomChannel : '';
		$time = time();
		switch ($this -> rssType)
		{
			case 1:		// RSS 1.0
				echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?".">
						<!-- generator=\"e107\" -->
						<!-- content type=\"".$this -> contentType."\" -->
						<rss version=\"0.92\">
						<channel>
						<title>".$tp->toRss($rss_title)."</title>
						<link>".$pref['siteurl']."</link>
						<description>".$tp->toRss($pref['sitedescription'])."</description>
						<lastBuildDate>".$itemdate = date("r", ($time))."</lastBuildDate>
						<docs>http://backend.userland.com/rss092</docs>\n";

					foreach($this -> rssItems as $value)
					{	// Multi-language rss links.
						$link 		= deftrue('e_LANQRY') ? str_replace("?","?".e_LANQRY,$value['link']) : $value['link'];

						echo "
							<item>
							<title>".$tp->toRss($value['title'])."</title>
							<description>".substr($tp->toRss($value['description']),0,150);
						if($pref['rss_shownewsimage'] == 1 && strlen(trim($value['news_thumbnail'])) > 0)
						{
							$news_thumbnail = SITEURLBASE.e_IMAGE_ABS."newspost_images/".$tp->toRss($value['news_thumbnail']);
							echo "&lt;a href=&quot;".$link."&quot;&gt;&lt;img src=&quot;".$news_thumbnail."&quot; height=&quot;50&quot; border=&quot;0&quot; hspace=&quot;10&quot; vspace=&quot;10&quot; align=&quot;right&quot;&gt;&lt;/a&gt;";
							unset($news_thumbail);
						}
						echo "</description>
							<author>".$value['author']."&lt;".$this->nospam($value['author_email'])."&gt;</author>
							<link>".$link."</link>
							</item>";
					}
					echo "
						</channel>
						</rss>";
			break;

			case 2:	// RSS 2.0
				$sitebutton = (strpos(SITEBUTTON, "http:") !== false ? SITEBUTTON : SITEURL.str_replace("../", "", SITEBUTTON));
				echo "<?xml version=\"1.0\" encoding=\"utf-8\"?".">
				<!-- generator=\"e107\" -->
				<!-- content type=\"".$this->contentType."\" -->
				<rss $rss_namespace version=\"2.0\"
					xmlns:content=\"http://purl.org/rss/1.0/modules/content/\"
					xmlns:atom=\"http://www.w3.org/2005/Atom\"
					xmlns:dc=\"http://purl.org/dc/elements/1.1/\"
					xmlns:sy=\"http://purl.org/rss/1.0/modules/syndication/\"
					xmlns:media=\"http://search.yahoo.com/mrss/\"
				>
				<channel>
				<title>".$tp->toRss($rss_title)."</title>
				<link>".$pref['siteurl']."</link>
				<description>".$tp->toRss($pref['sitedescription'])."</description>\n";

				echo $tp->toHTML($rss_custom_channel)."\n"; // must not convert to CDATA.

				echo "
				<language>".defset('CORE_LC').(defined("CORE_LC2") ? "-".CORE_LC2 : "")."</language>
				<copyright>".$tp->toRss(SITEDISCLAIMER)."</copyright>
				<managingEditor>".$this->nospam($pref['siteadminemail'])." (".$pref['siteadmin'].")</managingEditor>
				<webMaster>".$this->nospam($pref['siteadminemail'])." (".$pref['siteadmin'].")</webMaster>
				<pubDate>".date("r",($time))."</pubDate>
				<lastBuildDate>".date("r",($time))."</lastBuildDate>
				<docs>http://backend.userland.com/rss</docs>
				<generator>e107 (https://e107.org)</generator>
				<sy:updatePeriod>hourly</sy:updatePeriod>
				<sy:updateFrequency>1</sy:updateFrequency>
				<ttl>60</ttl>";

				echo "
				<atom:link href=\"".$tp->toRss(e107::url('rss_menu','atom', array('rss_url'=>$this->contentType, 'rss_topicid'=>$this->topicid),'full'))."\" rel=\"self\" type=\"application/rss+xml\" />\n";

				if (trim(SITEBUTTON))
				{
					$path = e107::getConfig()->get('sitebutton');
					$imgPath = e107::getParser()->thumbUrl($path, array(), false, true);					
					echo "
					<image>
					<title>".$tp->toRss($rss_title)."</title>
					<url>" . $imgPath . "</url>
					<link>".$pref['siteurl']."</link>
					<width>88</width>
					<height>31</height>
					<description>".$tp->toRss($pref['sitedescription'])."</description>
					</image>\n";
				}

				// Generally Ignored by 99% of readers.
				/*
				echo "
				<textInput>
				<title>Search</title>
				<description>Search ".$tp->toRss($pref['sitename'])."</description>
				<name>query</name>
				<link>".SITEURL.(substr(SITEURL, -1) == "/" ? "" : "/")."search.php</link>
				</textInput>";
				*/

				foreach($this -> rssItems as $value)
				{   // Multi-language rss links.
					$link 		= deftrue('e_LANQRY') ? str_replace("?","?".e_LANQRY,$value['link']) : $value['link'];
                    $catlink	= deftrue('e_LANQRY') ? str_replace("?","?".e_LANQRY,$value['category_link']) : $value['category_link'];

					echo "<item>\n";
					echo "<title>".$tp->toRss($value['title'])."</title>\n";

					if($link)
					{
						echo "<link>".$link."</link>\n";
					}

					echo "<description>".$tp->toRss($value['description'],true). "</description>\n";

					if($value['content_encoded'])
					{
						echo "<content:encoded>".$tp->toRss($value['content_encoded'],true)."</content:encoded>\n";
					}

					if($value['category_name'] && $catlink)
					{
						echo "<category domain='".$catlink."'>".$tp->toRss($value['category_name'])."</category>\n";
					}

					if($value['comment'])
					{
						echo "<comments>".$value['comment']."</comments>\n";
					}

					if($value['author'])
					{
						echo "<dc:creator>".$value['author']."</dc:creator>\n"; // correct tag for author without email.
					}

					// Enclosure support for podcasting etc.
					if($value['enc_url'] && $value['enc_leng'] && $value['enc_type'])
					{
						echo "<enclosure url=\"".$value['enc_url']."\" length=\"".$value['enc_leng']."\" type=\"".$value['enc_type']."\"   />\n";
					}

					echo "<pubDate>".date("r", ($value['pubdate']))."</pubDate>\n";

					if($link)
					{
						echo "<guid isPermaLink=\"true\">".$link."</guid>\n";
					}

					if(isset($value['custom'])) // custom tags. (podcasts etc)
					{
						foreach($value['custom'] as $cKey => $cVal)
						{
							echo "<".$cKey.">".$tp->toRss($cVal)."</".$cKey.">\n";
						}
					}

					if(!empty($value['media']))
					{

						foreach($value['media'] as $cVal)
						{
							foreach($cVal as $k=>$v)
							{
								echo $this->buildTag($k,$v);
							}
						}

					}


					echo "</item>\n\n";
				}
				// echo "<atom:link href=\"".e_SELF."?".($this -> contentType).".4.".$this -> topicId ."\" rel=\"self\" type=\"application/rss+xml\" />";
				echo "
				</channel>
				</rss>";
			break;

			case 3: 	// RDF
				echo "<?xml version=\"1.0\" encoding=\"utf-8\" ?".">
				<!-- generator=\"e107\" -->
				<!-- content type=\"".$this -> contentType."\" -->
				<rdf:RDF xmlns=\"http://purl.org/rss/1.0/\" xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:sy=\"http://purl.org/rss/1.0/modules/syndication/\" xmlns:admin=\"http://webns.net/mvcb/\" xmlns:content=\"http://purl.org/rss/1.0/modules/content/\">
				<channel rdf:about=\"".$pref['siteurl']."\">
				<title>".$tp->toRss($rss_title)."</title>
				<link>".$pref['siteurl']."</link>
				<description>".$tp->toRss($pref['sitedescription'])."</description>
				<dc:language>".defset('CORE_LC').(defined("CORE_LC2") ? "-".CORE_LC2 : "")."</dc:language>
				<dc:date>".$this->get_iso_8601_date($time). "</dc:date>
				<dc:creator>".$this->nospam($pref['siteadminemail'])."</dc:creator>
				<admin:generatorAgent rdf:resource=\"https://e107.org\" />
				<admin:errorReportsTo rdf:resource=\"mailto:".$this->nospam($pref['siteadminemail'])."\" />
				<sy:updatePeriod>hourly</sy:updatePeriod>
				<sy:updateFrequency>1</sy:updateFrequency>
				<sy:updateBase>2000-01-01T12:00+00:00</sy:updateBase>
				<items>
				<rdf:Seq>";

				foreach($this -> rssItems as $value)
				{   // Multi-language rss links.
					$link = deftrue('e_LANQRY') ? str_replace("?","?".e_LANQRY,$value['link']) : $value['link'];

					echo "
						<rdf:li rdf:resource=\"".$link."\" />";
				}

				echo "
				</rdf:Seq>
				</items>
				</channel>";

				reset($this -> rssItems);
				unset($link);
				foreach($this -> rssItems as $value)
				{
					$link = deftrue('e_LANQRY') ? str_replace("?","?".e_LANQRY,$value['link']) : $value['link']; // Multi-language rss links.

					echo "
						<item rdf:about=\"".$link."\">
						<title>".$tp->toRss($value['title'])."</title>
						<link>".$link."</link>
						<dc:date>".$this->get_iso_8601_date($time)."</dc:date>
						<dc:creator>".$value['author']."</dc:creator>
						<dc:subject>".$tp->toRss($value['category_name'])."</dc:subject>
						<description>".$tp->toRss($value['description']). "</description>
						</item>";
				}
				echo "
				</rdf:RDF>";
			break;

			// Atom
			case 4:
				echo "<?xml version='1.0' encoding='utf-8'?".">\n
				<feed xmlns='https://www.w3.org/2005/Atom'>\n";
				/*
				<feed version='0.3'
				xmlns='http://purl.org/atom/ns#'
				xmlns:dc='http://purl.org/dc/elements/1.1/'
				xml:lang='".CORE_LC.(defined("CORE_LC2") ? "-".CORE_LC2 : "")."'>\n";
				*/
					// Required
					echo "
					<id>".$pref['siteurl']."</id>\n
					<title type='text'>".$tp->toRss($rss_title)."</title>\n
					<updated>".$this->get_iso_8601_date($time)."</updated>\n";

					// Recommended
					echo "
					<author>\n
						<name>e107</name>\n";
						//<email></email>\n
						echo "
						<uri>https://e107.org/</uri>\n
					</author>\n
					<link rel='self' href='".$tp->toRss(e107::url('rss_menu','atom', array('rss_url'=>$this->contentType, 'rss_topicid'=>$this->topicid),'full'))."' />\n";

					// Optional
				//	include(e_ADMIN."ver.php");
					echo "
					<category term='e107'/>\n
					<contributor>\n
						<name>e107</name>\n
					</contributor>\n
					<generator uri='https://e107.org/' version='".defset('e_VERSION')."'>e107</generator>\n";
					//<icon>/icon.jpg</icon>\n
					echo "
					<logo>".(strpos(SITEBUTTON, "http:") !== false ? SITEBUTTON : SITEURL.str_replace("../", "", SITEBUTTON))."</logo>\n
					<rights type='html'>".$pref['siteadmin']." - ".$this->nospam($pref['siteadminemail'])."</rights>\n";
					if($pref['sitedescription']){
					echo "
					<subtitle type='text'>".$pref['sitedescription']."</subtitle>\n";
                    }
					foreach($this -> rssItems as $value) {
					echo "
					<entry>\n";

						// Required
						echo "
						<id>".$value['link']."</id>\n
						<title type='text'>".$tp->toRss($value['title'])."</title>\n
						<updated>".$this->get_iso_8601_date($value['pubdate'])."</updated>\n";

						// Recommended
                        $author = ($value['author']) ? $value['author'] : "unknown";

						echo "
						<author>\n";
						echo "
						<name>".$author."</name>\n";
						echo ($value['author_email']) ? "\t\t\t\t\t\t<email>".$this->nospam($value['author_email'])."</email>\n" : "";
						echo "</author>\n";
						//<content>complete story here</content>\n
						echo "
						<link rel='alternate' type='text/html' href='".$value['link']."' />\n
						<summary type='text'>".$tp->toRss($tp->toText($value['description'])). "</summary>\n";

						// Optional
						if(!empty($value['category_name']))
						{
							echo "<category term='".$tp->toRss($value['category_name'])."'/>\n";
						}
						//<contributor>
						//	<name>Jane Doe</name>
						//</contributor>
						echo "<published>".$this->get_iso_8601_date($value['pubdate'])."</published>\n";
						//<source>
						//	<id>http://example.org/</id>
						//	<title>Fourty-Two</title>
						//	<updated>2003-12-13T18:30:02Z</updated>
						//	<rights>© 2005 Example, Inc.</rights>
						//</source>
						//<rights type='html'>&amp;copy; 2005 John Doe</rights>
						echo "
					</entry>\n";
					}
				echo "
				</feed>\n";
			break;
		}
	}


	/**
	 * Build an XML Tag
	 * @param string $name
	 * @param array $attributes
	 * @param bool $closing
	 * @return string
	 */
	function buildTag($name='', $attributes=array())
	{
		$tp = e107::getParser();

		if(empty($name))
		{
			return '';
		}

		if(isset($attributes['value']))
		{
			$value = $attributes['value'];
			unset($attributes['value']);
		}

		$text = "\n<".$name;

		foreach($attributes as $att=>$attVal)
		{

			$text .= " ".$att."=\"".$tp->toRss($attVal)."\"";
		}

		$text .= ">";

		if(!empty($value))
		{
			if(is_array($value))
			{
				foreach($value as $t=>$r)
				{
					$text .= $this->buildTag($t,$r);
				}

			}
			else
			{
				$text .= $tp->toRss($value);
			}

		}

		$text .= "</".$name.">\n";

		return $text;
	}




	function getmime($file)
	{
		$ext = strtolower(str_replace(".","",strrchr(basename($file), ".")));
		$mime["mp3"] = "audio/mpeg";
		return $mime[$ext];
	}

	function get_iso_8601_date($int_date)
	{	//$int_date: current date in UNIX timestamp
		$date_mod = date('Y-m-d\TH:i:s', $int_date);
		$pre_timezone = date('O', $int_date);
		$time_zone = substr($pre_timezone, 0, 3).":".substr($pre_timezone, 3, 2);
		$date_mod .= $time_zone;
		return $date_mod;
	}

	function nospam($text)
	{
		$tmp = explode("@",$text);
		return ($tmp[0] != "") ? $tmp[0].RSS_LAN_2 : RSS_LAN_3;
	}
} // End class rssCreate
