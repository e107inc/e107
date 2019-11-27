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
Query string: content_type.rss_type.[topic id]
1: news
5: comments
12: downloads (option: specify category)

Plugins should use an e_rss.php file in their plugin folder
----------------------------------------------------------------
*/
if (!defined('e107_INIT'))
{
	require_once('../../class2.php');
}

$e107 = e107::getInstance();

if (!e107::isInstalled('rss_menu'))
{
	e107::redirect();
	exit;
}

$tp = e107::getParser();

require_once(e_PLUGIN.'rss_menu/rss_shortcodes.php');
require_once(e_HANDLER.'userclass_class.php');

/*
global $tp;
if (!is_object($tp->e_bb))
{
	require_once(e_HANDLER.'bbcode_handler.php');
	$tp->e_bb = new e_bbcode;
}
*/

// Get language file
e107::includeLan(e_PLUGIN.'rss_menu/languages/'.e_LANGUAGE.'_admin_rss_menu.php');

// Get template
if (is_readable(THEME.'rss_template.php'))
{
	require_once(THEME.'rss_template.php');
}
else
{
	require_once(e_PLUGIN.'rss_menu/rss_template.php');
}

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

	// require_once(e_PLUGIN.'rss_menu/rss_template.php');		Already loaded

	if(!$sql->select('rss', '*', "`rss_class` = 0 AND `rss_limit` > 0 AND `rss_topicid` NOT REGEXP ('\\\*') ORDER BY `rss_name`"))
	{
		$ns->tablerender(LAN_ERROR, RSS_LAN_ERROR_4);
	}
	else
	{
		$text = $RSS_LIST_HEADER;
		while($row = $sql->fetch())
		{
			$text .= $tp->parseTemplate($RSS_LIST_TABLE, FALSE, $rss_shortcodes);
		}
		$text .= $RSS_LIST_FOOTER;
		$ns->tablerender(RSS_MENU_L2, $text);
	}

 	require_once(FOOTERF);
	exit;
}


while (@ob_end_clean());

// Returning feeds here
// Conversion table for old urls -------
$conversion[1] 	= 'news';
$conversion[5] 	= 'comments';
$conversion[10] = 'bugtracker';
$conversion[12] = 'download';
//-------------------------------------

// Convert certain old urls so we can check the db entries
// Rss.php?1.2 (news, rss-2) --> check = news (check conversion table)

if(is_numeric($content_type) && isset($conversion[$content_type]) )
{
	$content_type = $conversion[$content_type];
}


$check_topic = ($topic_id ? " AND rss_topicid = '".$topic_id."' " : "");

if(!$sql->select('rss', '*', "rss_class != 2 AND rss_url='".$content_type."' ".$check_topic." AND rss_limit > 0 "))
{	// Check if wildcard present for topic_id
	$check_topic = ($topic_id ? " AND rss_topicid = '".str_replace($topic_id, "*", $topic_id)."' " : "");
	if(!$sql->select('rss', '*', "rss_class != 2 AND rss_url='".$content_type."' ".$check_topic." AND rss_limit > 0 "))
	{
		require_once(HEADERF);
		
		$repl  		= array("<br /><br /><a href='".e_REQUEST_SELF."'>", "</a>");
		$message 	= str_replace(array("[","]"), $repl, RSS_LAN_ERROR_1);
		$ns->tablerender('', $message);
		
		require_once(FOOTERF);
		exit;
	}
	else
	{
		$row = $sql->fetch();
	}
}
else
{
	$row = $sql->fetch();
}


// ----------------------------------------------------------------------------

if($rss = new rssCreate($content_type, $rss_type, $topic_id, $row))
{
	$rss_title = ($rss->contentType ? $rss->contentType : ucfirst($content_type));

	if(E107_DEBUG_LEVEL > 0)
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
	exit;
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

	public function __construct($content_type, $rss_type, $topic_id, $row)
	{	// Constructor
		$sql_rs = new db;
		global $rssgen;
		$sql = e107::getDb();
		$tp = e107::getParser();

		$this->e107 = e107::getInstance();

		$this -> path = e_PLUGIN."rss_menu/";
		$this -> rssType = $rss_type;
		$this -> topicid = $topic_id;
		$this -> limit = $row['rss_limit'];
		$this -> contentType = $row['rss_name'];

		if(!is_numeric($content_type))
		{
			$path = e_PLUGIN.$row['rss_path'].'/e_rss.php';
		}
		if(strpos($row['rss_path'],'|')!==FALSE) //FIXME remove this check completely. 
		{
			$tmp = explode("|", $row['rss_path']);
			$path = e_PLUGIN.$tmp[0]."/e_rss.php";
			$this->parm = $tmp[1];	// FIXME @Deprecated - use $parm['url'] instead in data() method within e_rss.php.  Parm is used in e_rss.php to define which feed you need to prepare
		}

		switch ($content_type)
		{
			case 'news' :
			case 1:
				$path = e_PLUGIN."news/e_rss.php";
				$this->contentType = "news";
				break;
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
			case 5:
				$path='';
				$this -> rssQuery = "SELECT * FROM `#comments` WHERE `comment_blocked` = 0 ORDER BY `comment_datestamp` DESC LIMIT 0,".$this -> limit;
				$sql->gen($this -> rssQuery);
				$tmp = $sql->db_getList();
				$this -> rssItems = array();
				$loop=0;
				foreach($tmp as $value)
				{
					$this -> rssItems[$loop]['title'] = $value['comment_subject'];
					$this -> rssItems[$loop]['pubdate'] = $value['comment_datestamp'];

					switch ($value['comment_type'])
					{
						case 0 :
						case 'news' :
							$this -> rssItems[$loop]['link'] = "http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?comment.news.".$value['comment_item_id'];
							break;
						case 2 :
						case 'download' :
							$this -> rssItems[$loop]['link'] = "http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?comment.download.".$value['comment_item_id'];
							break;
						case 4:
						case 'poll' :
							$this -> rssItems[$loop]['link'] = "http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?comment.poll.".$value['comment_item_id'];
							break;
					}

					$this -> rssItems[$loop]['description'] = $value['comment_comment'];
					$this -> rssItems[$loop]['author'] = substr($value['comment_author'], (strpos($value['comment_author'], ".")+1));
					$loop++;
				}
				break;

			case 6:
			case 7:
				$path = e_PLUGIN."forum/e_rss.php";
				break;

			case 8:
				if(!$this -> topicid)
				{
					return FALSE;
				}
				$path = e_PLUGIN."forum/e_rss.php";
				break;

			// case 10 was bugtracker
			case 11:
				if(!$this -> topicid)
				{
					return FALSE;
				}
				$path = e_PLUGIN."forum/e_rss.php";
				break;

			case 'download':
			case 12:
				$path = e_PLUGIN."download/e_rss.php";
				break;
		}

		if(isset($path) && $path!='')
		{	// New rss reader from e_rss.php in plugin folder
			if (is_readable($path))
			{
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

		header('Content-type: application/xml', TRUE);

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
						$link 		= (e_LANQRY) ? str_replace("?","?".e_LANQRY,$value['link']) : $value['link'];

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
				$sitebutton = (strstr(SITEBUTTON, "http:") ? SITEBUTTON : SITEURL.str_replace("../", "", SITEBUTTON));
				echo "<?xml version=\"1.0\" encoding=\"utf-8\"?".">
				<!-- generator=\"e107\" -->
				<!-- content type=\"".$this->contentType."\" -->
				<rss {$rss_namespace} version=\"2.0\"
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

				echo $tp->toHTML($rss_custom_channel,FALSE)."\n"; // must not convert to CDATA.

				echo "
				<language>".CORE_LC.(defined("CORE_LC2") ? "-".CORE_LC2 : "")."</language>
				<copyright>".$tp->toRss(SITEDISCLAIMER)."</copyright>
				<managingEditor>".$this->nospam($pref['siteadminemail'])." (".$pref['siteadmin'].")</managingEditor>
				<webMaster>".$this->nospam($pref['siteadminemail'])." (".$pref['siteadmin'].")</webMaster>
				<pubDate>".date("r",($time))."</pubDate>
				<lastBuildDate>".date("r",($time))."</lastBuildDate>
				<docs>http://backend.userland.com/rss</docs>
				<generator>e107 (http://e107.org)</generator>
				<sy:updatePeriod>hourly</sy:updatePeriod>
				<sy:updateFrequency>1</sy:updateFrequency>
				<ttl>60</ttl>";

				echo "
				<atom:link href=\"".$tp->toRss(e107::url('rss_menu','atom', array('rss_url'=>$this->contentType, 'rss_topicid'=>$this->topicid),'full'))."\" rel=\"self\" type=\"application/rss+xml\" />\n";

				if (trim(SITEBUTTON))
				{
					echo "
					<image>
					<title>".$tp->toRss($rss_title)."</title>
					<url>".(strstr(SITEBUTTON, "http:")!==FALSE ? SITEBUTTON : SITEURL.str_replace("../", "",SITEBUTTON))."</url>
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
					$link 		= (e_LANQRY) ? str_replace("?","?".e_LANQRY,$value['link']) : $value['link'];
                    $catlink	= (e_LANQRY) ? str_replace("?","?".e_LANQRY,$value['category_link']) : $value['category_link'];

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
				<dc:language>".CORE_LC.(defined("CORE_LC2") ? "-".CORE_LC2 : "")."</dc:language>
				<dc:date>".$this->get_iso_8601_date($time). "</dc:date>
				<dc:creator>".$this->nospam($pref['siteadminemail'])."</dc:creator>
				<admin:generatorAgent rdf:resource=\"http://e107.org\" />
				<admin:errorReportsTo rdf:resource=\"mailto:".$this->nospam($pref['siteadminemail'])."\" />
				<sy:updatePeriod>hourly</sy:updatePeriod>
				<sy:updateFrequency>1</sy:updateFrequency>
				<sy:updateBase>2000-01-01T12:00+00:00</sy:updateBase>
				<items>
				<rdf:Seq>";

				foreach($this -> rssItems as $value)
				{   // Multi-language rss links.
					$link = (e_LANQRY) ? str_replace("?","?".e_LANQRY,$value['link']) : $value['link'];

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
					$link = (e_LANQRY) ? str_replace("?","?".e_LANQRY,$value['link']) : $value['link']; // Multi-language rss links.

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
				<feed xmlns='http://www.w3.org/2005/Atom'>\n";
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
						<uri>http://e107.org/</uri>\n
					</author>\n
					<link rel='self' href='".$tp->toRss(e107::url('rss_menu','atom', array('rss_url'=>$this->contentType, 'rss_topicid'=>$this->topicid),'full'))."' />\n";

					// Optional
				//	include(e_ADMIN."ver.php");
					echo "
					<category term='e107'/>\n
					<contributor>\n
						<name>e107</name>\n
					</contributor>\n
					<generator uri='http://e107.org/' version='".e_VERSION."'>e107</generator>\n";
					//<icon>/icon.jpg</icon>\n
					echo "
					<logo>".(strstr(SITEBUTTON, "http:") ? SITEBUTTON : SITEURL.str_replace("../", "", SITEBUTTON))."</logo>\n
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
