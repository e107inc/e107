<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
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

/*
Query string: content_type.rss_type.[topic id]
1: news
5: comments
12: downloads (option: specify category)

Plugins should use an e_rss.php file in their plugin folder
----------------------------------------------------------------
*/

require_once("../../class2.php");
if (!isset($pref['plug_installed']['rss_menu']))
{
	header('Location: '.e_BASE.'index.php');
	exit;
}

global $tp;

require_once(e_PLUGIN."rss_menu/rss_shortcodes.php");
require_once(e_HANDLER."userclass_class.php");

if (!is_object($tp->e_bb)) {
	require_once(e_HANDLER.'bbcode_handler.php');
	$tp->e_bb = new e_bbcode;
}

//get language file
if (is_readable(e_PLUGIN."rss_menu/languages/".e_LANGUAGE.".php")) {
	include_once(e_PLUGIN."rss_menu/languages/".e_LANGUAGE.".php");
} else {
	include_once(e_PLUGIN."rss_menu/languages/English.php");
}

//get template
if (is_readable(THEME."rss_template.php")) {
	require_once(THEME."rss_template.php");
	} else {
	require_once(e_PLUGIN."rss_menu/rss_template.php");
}

//query handler
if (e_QUERY)
{
	$tmp = explode(".", e_QUERY);
	$content_type = $tp->toDB($tmp[0]);
	$rss_type = intval(varset($tmp[1],0));
	$topic_id = $tp->toDB($tmp[2],'');
}

//list available rss feeds
if (!$rss_type) 
{
	require_once(HEADERF);
 	require_once(e_PLUGIN."rss_menu/rss_template.php");

	if(!$sql->db_Select("rss", "*", "`rss_class`='0' AND `rss_limit`>0 AND `rss_topicid` NOT REGEXP ('\\\*') ORDER BY `rss_name`"))
	{
		$ns->tablerender(LAN_ERROR, RSS_LAN_ERROR_4);
	}
	else
	{
		$text = $RSS_LIST_HEADER;
		while($row=$sql->db_Fetch())
		{
			$text .= $tp -> parseTemplate($RSS_LIST_TABLE, FALSE, $rss_shortcodes);
		}
		$text .= $RSS_LIST_FOOTER;
		$ns->tablerender(RSS_MENU_L2, $text);
	}

 	require_once(FOOTERF);
	exit;
}

//conversion table for old urls -------
$conversion[1] = "news";
$conversion[5] = "comments";
$conversion[10] = "bugtracker";
$conversion[12] = "download";
//-------------------------------------

//convert certain old urls so we can check the db entries ---------------------
if($topic_id)
{	//rss.php?1.2.14 (news, rss-2, cat=14)
	if(is_numeric($content_type) && isset($conversion[$content_type]) )
	{
		$content_type = $conversion[$content_type];
	}
}
else
{	//rss.php?1.2 (news, rss-2) --> check = news (check conversion table)
	if(is_numeric($content_type) && isset($conversion[$content_type]) )
	{
		$content_type = $conversion[$content_type];
	}
}

$check_topic = ($topic_id ? " AND rss_topicid = '".$topic_id."' " : "");

if(!$sql -> db_Select("rss", "*", "rss_class!='2' AND rss_url='".$content_type."' ".$check_topic." AND rss_limit>0 "))
{
	//check if wildcard present for topic_id
	$check_topic = ($topic_id ? " AND rss_topicid = '".str_replace($topic_id, "*", $topic_id)."' " : "");
	if(!$sql -> db_Select("rss", "*", "rss_class!='2' AND rss_url='".$content_type."' ".$check_topic." AND rss_limit>0 "))
	{
		require_once(HEADERF);
		$ns->tablerender("", RSS_LAN_ERROR_1);
		require_once(FOOTERF);
		exit;
	}
	else
	{
		$row = $sql->db_Fetch();
	}
}
else
{
	$row = $sql->db_Fetch();
}

//debugging
//echo $check." - ".$content_type." - ".$rss_type." - ".$topic_id."<br />";
//exit;

// ----------------------------------------------------------------------------

if($rss = new rssCreate($content_type, $rss_type, $topic_id, $row)){
	$rss_title = ($rss->contentType ? $rss->contentType : ucfirst($content_type));
	$rss->buildRss ($rss_title);
}else{
	require_once(HEADERF);
	$ns->tablerender(RSS_LAN_ERROR_0, RSS_LAN_ERROR_1);
	require_once(FOOTERF);
	exit;
}

class rssCreate {

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

	function rssCreate($content_type, $rss_type, $topic_id, $row) {
		// constructor
		$sql_rs = new db;
		global $tp, $sql, $e107, $PLUGINS_DIRECTORY, $pref, $rssgen;
		$this -> path = e_PLUGIN."rss_menu/";
		$this -> rssType = $rss_type;
		$this -> topicid = $topic_id;
		$this -> offset = $pref['time_offset'] * 3600;
		$this -> limit = $row['rss_limit'];
		$this -> contentType = $row['rss_name'];

		if(!is_numeric($content_type)){
			$path = e_PLUGIN.$row['rss_path']."/e_rss.php";
		}
		if(strpos($row['rss_path'],'|')!==FALSE){
			$tmp = explode("|", $row['rss_path']);
			$path = e_PLUGIN.$tmp[0]."/e_rss.php";
			$this -> parm = $tmp[1];	//parm is used in e_rss.php to define which feed you need to prepare
		}

		switch ($content_type) {
			case 'news' :
			case 1:
				if($topic_id && is_numeric($topic_id))
				{
					$topic = " AND news_category = ".intval($topic_id);
				}
				else
				{
					$topic = '';
				}
				$path='';
				$render = ($pref['rss_othernews'] != 1) ? "AND n.news_render_type < 2" : "";
				$nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";

				$this -> rssQuery = "
				SELECT n.*, u.user_id, u.user_name, u.user_email, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
				LEFT JOIN #user AS u ON n.news_author = u.user_id
				LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
				WHERE n.news_class IN (".USERCLASS_LIST.") AND NOT (n.news_class REGEXP ".$nobody_regexp.") AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") {$render} {$topic} ORDER BY n.news_datestamp DESC LIMIT 0,".$this -> limit;
				$sql->db_Select_gen($this -> rssQuery);
				$tmp = $sql->db_getList();
				
				$this -> rssItems = array();
				$loop=0;
				foreach($tmp as $value) {
					$this -> rssItems[$loop]['title'] = $value['news_title'];
					$this -> rssItems[$loop]['link'] = "http://".$_SERVER['HTTP_HOST'].e_HTTP."news.php?item.".$value['news_id'].".".$value['news_category'];
                    if($value['news_summary'])
                    {
                        	$this -> rssItems[$loop]['description'] = $value['news_summary'];
							$this -> rssItems[$loop]['content_encoded'] = ($value['news_body']."<br />".$value['news_extended']);
					}
					else
					{
						$this -> rssItems[$loop]['description'] = ($value['news_body']."<br />".$value['news_extended']);
						$this -> rssItems[$loop]['content_encoded'] = ($value['news_body']."<br />".$value['news_extended']);
                    }
					
					if($value['news_thumbnail'])
					{
						$this -> rssItems[$loop]['media_content_url'][] = "http://".$_SERVER['HTTP_HOST'].e_HTTP.e_IMAGE."newspost_images/".$news_item['news_thumbnail'];
						$this -> rssItems[$loop]['media_content_type'][] = "image";
					}
					
					$this -> rssItems[$loop]['author'] = $value['user_name'];
                    $this -> rssItems[$loop]['author_email'] = $value['user_email'];

					$this -> rssItems[$loop]['category_name'] = $tp->toHTML($value['category_name'],TRUE,'defs');
                    $this -> rssItems[$loop]['category_link'] = $e107->base_path."news.php?cat.".$value['news_category'];

					if($value['news_allow_comments'] && $pref['comments_disabled'] != 1){
						$this -> rssItems[$loop]['comment'] = "http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?comment.news.".$value['news_id'];
                    }
					$this -> rssItems[$loop]['pubdate'] = $value['news_datestamp'];

					$loop++;
				}
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
			case 'comments' :
			case 5:
				$path='';
				$this -> rssQuery = "SELECT * FROM `#comments` WHERE `comment_blocked` = 0 ORDER BY `comment_datestamp` DESC LIMIT 0,".$this -> limit;
				$sql->db_Select_gen($this -> rssQuery);
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
				if(!$this -> topicid) {
					return FALSE;
				}
				$path = e_PLUGIN."forum/e_rss.php";
				break;

			/*
			case 10:
				$this -> limit = '9';
				$path='';
				$this -> contentType = "bugtracker reports";
				$sql->db_Select("bugtrack2_bugs", "*", "bugtrack2_bugs_status=0 ORDER BY bugtrack2_bugs_datestamp LIMIT 0,".$this -> limit);
				$tmp = $sql->db_getList();
				$this -> rssItems = array();
				$loop=0;
				foreach($tmp as $value) {
					$nick = preg_replace("/[0-9]+\./", "", $value['bugtrack2_bugs_poster']);
					$this -> rssItems[$loop]['author'] = $nick;
					$this -> rssItems[$loop]['title'] = $value['bugtrack2_bugs_summary'];
					$this -> rssItems[$loop]['link'] = $e107->base_path.$PLUGINS_DIRECTORY."bugtracker2/bugtracker2.php?0.bug.".$value['bugtrack2_bugs_id'];
					$this -> rssItems[$loop]['description'] = $value['bugtrack2_bugs_description'];
					$loop++;
				}
				break;
			*/

			case 11:
				if(!$this -> topicid) {
					return FALSE;
				}
				$path = e_PLUGIN."forum/e_rss.php";
				break;

			case download:
			case 12:
				if($topic_id && is_numeric($topic_id)){
					$topic = "d.download_category='".intval($topic_id)."' AND ";
				}else{
					$topic = "";
				}
				$path='';
				$class_list = "0,251,252,253";
                $query = "SELECT d.*, dc.* FROM #download AS d LEFT JOIN #download_category AS dc ON d.download_category = dc.download_category_id WHERE {$topic} d.download_active > 0 AND d.download_class IN (".$class_list.") ORDER BY d.download_datestamp DESC LIMIT 0,".$this -> limit;
                $sql -> db_Select_gen($query);

			 //	$sql->db_Select("download", "*", "{$topic} download_active > 0 AND download_class IN (".$class_list.") ORDER BY download_datestamp DESC LIMIT 0,".$this -> limit);
				$tmp = $sql->db_getList();
				$this -> rssItems = array();
				$loop=0;
				foreach($tmp as $value) {
					if($value['download_author']){
				   		$nick = preg_replace("/[0-9]+\./", "", $value['download_author']);
						$this -> rssItems[$loop]['author'] = $nick;
					}
					$this -> rssItems[$loop]['author_email'] = $value['download_author_email'];
					$this -> rssItems[$loop]['title'] = $value['download_name'];
					$this -> rssItems[$loop]['link'] = $e107->base_path."download.php?view.".$value['download_id'];
					$this -> rssItems[$loop]['description'] = ($rss_type == 3 ? $value['download_description'] : $value['download_description']);
                    $this -> rssItems[$loop]['category_name'] = $value['download_category_name'];
                    $this -> rssItems[$loop]['category_link'] = $e107->base_path."download.php?list.".$value['download_category_id'];
					$this -> rssItems[$loop]['enc_url'] = $e107->base_path."request.php?".$value['download_id'];
					$this -> rssItems[$loop]['enc_leng'] = $value['download_filesize'];
					$this -> rssItems[$loop]['enc_type'] = $this->getmime($value['download_url']);
					$this -> rssItems[$loop]['pubdate'] = $value['download_datestamp'];
					$loop++;
				}
				break;
		}


		if(isset($path) && $path!=''){

			//new rss reader from e_rss.php in plugin folder
			if (is_readable($path)) {
				require_once($path);
				foreach($eplug_rss_data as $key=>$rs){
					foreach($rs as $k=>$row){
						$this -> rssItems[$k]['author'] = $row['author'];
						$this -> rssItems[$k]['author_email'] = $row['author_email'];
						$this -> rssItems[$k]['title'] = $row['title'];
						if($row['link']){
							if(eregi("http",$row['link'])){
								$this -> rssItems[$k]['link'] = $row['link'];
							}else{
								$this -> rssItems[$k]['link'] = $e107->base_path.$PLUGINS_DIRECTORY.$row['link'];
							}
						}
						$this -> rssItems[$k]['description'] = $row['description'];
						if($row['enc_url']){
							$this -> rssItems[$k]['enc_url'] = $e107->base_path.$PLUGINS_DIRECTORY.$row['enc_url'].$row['item_id'];
						}
						if($row['enc_leng']){
							$this -> rssItems[$k]['enc_leng'] = $row['enc_leng'];
						}

						if($eplug_rss['enc_type']){
							$this -> rssItems[$k]['enc_type'] = $this->getmime($eplug_rss['enc_type']);
						}elseif($row['enc_type']){
							$this -> rssItems[$k]['enc_type'] = $row['enc_type'];
						}

						$this -> rssItems[$k]['category_name'] = $row['category_name'];
						if($row['category_link']){
							if(eregi("http",$row['category_link'])){
								$this -> rssItems[$k]['category_link'] = $row['category_link'];
							}else{
								$this -> rssItems[$k]['category_link'] = $e107->base_path.$PLUGINS_DIRECTORY.$row['category_link'];
							}
						}
						if($row['datestamp']){
							$this -> rssItems[$k]['pubdate'] = $row['datestamp'];
						}
						
						if($row['custom']){
							$this -> rssItems[$k]['custom'] = $row['custom'];
						}
					}
				}
			}
		}

	}

	function buildRss($rss_title) {
		global $sql, $pref, $tp, $e107, $PLUGINS_DIRECTORY,$topic_id,$content_type ;
		header('Content-type: application/xml', TRUE);

		$rss_title = $tp->toRss($tp->toHtml($pref['sitename'],'','defs')." : ".$tp->toHtml($rss_title,'','defs'));
        $rss_namespace = ($this->rssNamespace) ? "xmlns:".$this->rssNamespace : "";
        $rss_custom_channel = ($this->rssCustomChannel) ? $this->rssCustomChannel : "";
		
	
		$time = time();
		switch ($this -> rssType) {
			case 1:		// Rss 1.0
				echo "<?xml version=\"1.0\" encoding=\"".CHARSET."\" ?>
						<!-- generator=\"e107\" -->
						<!-- content type=\"".$this -> contentType."\" -->
						<rss version=\"0.92\">
						<channel>
						<title>".$tp->toRss($rss_title)."</title>
						<link>".$pref['siteurl']."</link>
						<description>".$tp->toRss($pref['sitedescription'])."</description>
						<lastBuildDate>".$itemdate = date("r", ($time + $this -> offset))."</lastBuildDate>
						<docs>http://backend.userland.com/rss092</docs>\n";

					foreach($this -> rssItems as $value)
					{

                    	// Multi-language rss links.
						$link 		= (e_LANQRY) ? str_replace("?","?".e_LANQRY,$value['link']) : $value['link'];

						echo "
							<item>
							<title>".$tp->toRss($value['title'])."</title>
							<description>".substr($tp->toRss($value['description']),0,150)."</description>
							<author>".$value['author']."&lt;".$this->nospam($value['author_email'])."&gt;</author>
							<link>".$link."</link>
							</item>";
					}
					echo "
						</channel>
						</rss>";
			break;

			case 2: // rss 2.0
				$sitebutton = (strstr(SITEBUTTON, "http:") ? SITEBUTTON : SITEURL.str_replace("../", "", e_IMAGE).SITEBUTTON);
				echo "<?xml version=\"1.0\" encoding=\"".CHARSET."\"?>
				<!-- generator=\"e107\" -->
				<!-- content type=\"".$this -> contentType."\" -->
				<rss {$rss_namespace} version=\"2.0\" xmlns:content=\"http://purl.org/rss/1.0/modules/content/\" xmlns:atom=\"http://www.w3.org/2005/Atom\">
				<channel>
				<title>".$tp->toRss($rss_title)."</title>
				<link>".$pref['siteurl']."</link>
				<description>".$tp->toRss($pref['sitedescription'])."</description>\n";

				echo $tp->toHtml($rss_custom_channel,FALSE)."\n"; // CDATA and toRss() must not be used for $rss_custom_channel. 

				echo "<language>".CORE_LC.(defined("CORE_LC2") ? "-".CORE_LC2 : "")."</language>
				<copyright>".$tp->toRss(SITEDISCLAIMER)."</copyright>
				<managingEditor>".$this->nospam($pref['siteadminemail'])." (".$pref['siteadmin'].")</managingEditor>
				<webMaster>".$this->nospam($pref['siteadminemail'])." (".$pref['siteadmin'].")</webMaster>
				<pubDate>".date("r",($time + $this -> offset))."</pubDate>
				<lastBuildDate>".date("r",($time + $this -> offset))."</lastBuildDate>
				<docs>http://backend.userland.com/rss</docs>
				<generator>e107 (http://e107.org)</generator>
				<ttl>60</ttl>\n";
				
				echo "<atom:link href=\"".e_SELF."?".$content_type.".4.".$this->topicid."\" rel=\"self\" type=\"application/rss+xml\" />\n";

				if (trim(SITEBUTTON))
				{
					echo "
					<image>
					<title>".$tp->toRss($rss_title)."</title>
					<url>".(strstr(SITEBUTTON, "http:") ? SITEBUTTON : SITEURL.str_replace("../", "", e_IMAGE).SITEBUTTON)."</url>
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
				{
                    // Multi-language rss links.
					$link 		= (e_LANQRY) ? str_replace("?","?".e_LANQRY,$value['link']) : $value['link'];
                    $catlink	= (e_LANQRY) ? str_replace("?","?".e_LANQRY,$value['category_link']) : $value['category_link'];

					echo "<item>\n";
					echo "<title>".$tp->toRss($value['title'])."</title>\n";

					if($link){
						echo "<link>".$link."</link>\n";
					}

					echo "<description>".$tp->toRss($value['description'],TRUE)."</description>\n";
					
					if($value['content_encoded'])
					{
						echo "<content:encoded>".$tp->toRss($value['content_encoded'],TRUE)."</content:encoded>\n";	
					}
					
					/*if($value['media_content_url'])
					{
						foreach($value['media_content_url'] as $k=>$mcu)
						{
							echo "<media:content url=\"".$tp->toRss($mcu)."\" medium=\"".$value['media_content_type'][$k]."\">";	
						}
					}*/
					
					
					if($value['category_name'] && $catlink){
						echo "<category domain='".$catlink."'>".$tp -> toRss($value['category_name'])."</category>\n";
					}

					if($value['comment']){
						//echo "<comments>".$tp->toRss($value['comment'])."</comments>\n";
						echo "<comments>".$value['comment']."</comments>\n";
					}

					if($value['author']){
						echo "<author>".$this->nospam($value['author_email'])." (".$value['author'].")</author>\n";
					}

					// enclosure support for podcasting etc.
					if($value['enc_url'] && $value['enc_leng'] && $value['enc_type']){
						echo "<enclosure url=\"".$value['enc_url']."\" length=\"".$value['enc_leng']."\" type=\"".$value['enc_type']."\"   />\n";
					}

					echo "<pubDate>".date("r", ($value['pubdate'] + $this -> offset))."</pubDate>\n";

					if($link){
						echo "<guid isPermaLink=\"true\">".$link."</guid>\n";
					}
					
					if(isset($value['custom'])) // custom tags. (podcasts etc)
					{
						foreach($value['custom'] as $cKey => $cVal)
						{
							echo "<".$cKey.">".$tp->toRss($cVal)."</".$cKey.">\n";	
						}		
					}

					echo "</item>\n\n";
				}
		   //		echo "<atom:link href=\"".e_SELF."?".($this -> contentType).".4.".$this -> topicId ."\" rel=\"self\" type=\"application/rss+xml\" />";
				echo "
				</channel>
				</rss>";
			break;

			case 3: // rdf
				echo "<?xml version=\"1.0\" encoding=\"".CHARSET."\" ?>
				<!-- generator=\"e107\" -->
				<!-- content type=\"".$this -> contentType."\" -->
				<rdf:RDF xmlns=\"http://purl.org/rss/1.0/\" xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:sy=\"http://purl.org/rss/1.0/modules/syndication/\" xmlns:admin=\"http://webns.net/mvcb/\" xmlns:content=\"http://purl.org/rss/1.0/modules/content/\">
				<channel rdf:about=\"".$pref['siteurl']."\">
				<title>".$tp->toRss($rss_title)."</title>
				<link>".$pref['siteurl']."</link>
				<description>".$tp->toRss($pref['sitedescription'])."</description>
				<dc:language>".CORE_LC.(defined("CORE_LC2") ? "-".CORE_LC2 : "")."</dc:language>
				<dc:date>".$this->get_iso_8601_date($time + $this -> offset). "</dc:date>
				<dc:creator>".$this->nospam($pref['siteadminemail'])."</dc:creator>
				<admin:generatorAgent rdf:resource=\"http://e107.org\" />
				<admin:errorReportsTo rdf:resource=\"mailto:".$this->nospam($pref['siteadminemail'])."\" />
				<sy:updatePeriod>hourly</sy:updatePeriod>
				<sy:updateFrequency>1</sy:updateFrequency>
				<sy:updateBase>2000-01-01T12:00+00:00</sy:updateBase>
				<items>
				<rdf:Seq>";

				foreach($this -> rssItems as $value)
				{

                    // Multi-language rss links.
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

                    // Multi-language rss links.
					$link = (e_LANQRY) ? str_replace("?","?".e_LANQRY,$value['link']) : $value['link'];

					echo "
						<item rdf:about=\"".$link."\">
						<title>".$tp->toRss($value['title'])."</title>
						<link>".$link."</link>
						<dc:date>".$this->get_iso_8601_date($time + $this -> offset)."</dc:date>
						<dc:creator>".$value['author']."</dc:creator>
						<dc:subject>".$tp->toRss($value['category_name'])."</dc:subject>
						<description>".$tp->toRss($value['description'])."</description>
						</item>";
				}
				echo "
				</rdf:RDF>";
			break;

			//new feed for atom - still in development, and not yet tested
			case 4:
				echo "<?xml version='1.0' encoding='".CHARSET."'?>\n
				<feed xmlns='http://www.w3.org/2005/Atom'>\n";
				/*
				<feed version='0.3'
				  xmlns='http://purl.org/atom/ns#'
				  xmlns:dc='http://purl.org/dc/elements/1.1/'
				  xml:lang='".CORE_LC.(defined("CORE_LC2") ? "-".CORE_LC2 : "")."'>\n";
				  */

					//required
					echo "
					<id>".$pref['siteurl']."</id>\n
					<title type='text'>".$tp->toRss($rss_title)."</title>\n
					<updated>".$this->get_iso_8601_date($time + $this -> offset)."</updated>\n";

					//recommended
					echo "
					<author>\n
						<name>e107</name>\n";
						//<email></email>\n
						echo "
						<uri>http://e107.org/</uri>\n
					</author>\n
					<link rel='self' href='".$e107->base_path.$PLUGINS_DIRECTORY."rss_menu/".e_PAGE."?".e_QUERY."' />\n";

					//optional
					include(e_ADMIN."ver.php");
					echo "
					<category term='e107'/>\n
					<contributor>\n
						<name>e107</name>\n
					</contributor>\n
					<generator uri='http://e107.org/' version='".$e107info['e107_version']."'>e107</generator>\n";
					//<icon>/icon.jpg</icon>\n
					echo "
					<logo>".(strstr(SITEBUTTON, "http:") ? SITEBUTTON : SITEURL.str_replace("../", "", e_IMAGE).SITEBUTTON)."</logo>\n
					<rights type='html'>".$pref['siteadmin']." - ".$this->nospam($pref['siteadminemail'])."</rights>\n";
					if($pref['sitedescription']){
					echo "
					<subtitle type='text'>".$pref['sitedescription']."</subtitle>\n";
                    }
					foreach($this -> rssItems as $value) {
					echo "
					<entry>\n";

						//required
						echo "
						<id>".$value['link']."</id>\n
						<title type='text'>".$tp->toRss($value['title'])."</title>\n
						<updated>".$this->get_iso_8601_date($value['pubdate'] + $this -> offset)."</updated>\n";

						//recommended
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
						<summary type='text'>".strip_tags($tp->toRss($value['description'],FALSE))."</summary>\n";

						//optional
						if($value['category_name']){
							echo "<category term='".$tp -> toRss($value['category_name'])."'/>\n";
						}
						//<contributor>
						//	<name>Jane Doe</name>
						//</contributor>
						echo "<published>".$this->get_iso_8601_date($value['pubdate'] + $this -> offset)."</published>\n";
						//<source>
						//	<id>http://example.org/</id>
						//	<title>Fourty-Two</title>
						//	<updated>2003-12-13T18:30:02Z</updated>
						//	<rights>� 2005 Example, Inc.</rights>
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

	function getmime($file){
		$ext = strtolower(str_replace(".","",strrchr(basename($file), ".")));
		$mime["mp3"] = "audio/mpeg";
		return $mime[$ext];
	}

	function get_iso_8601_date($int_date) {
		//$int_date: current date in UNIX timestamp
		$date_mod = date('Y-m-d\TH:i:s', $int_date);
		$pre_timezone = date('O', $int_date);
		$time_zone = substr($pre_timezone, 0, 3).":".substr($pre_timezone, 3, 2);
		$date_mod .= $time_zone;
		return $date_mod;
	}

	function nospam($text){
		$tmp = explode("@",$text);
		return ($tmp[0] != "") ? $tmp[0].RSS_LAN_2 : RSS_LAN_3;
	}

}

?>
