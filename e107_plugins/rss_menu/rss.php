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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/rss_menu/rss.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-31 21:49:21 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
	
/*
Query string: content_type.rss_type.[topic id]
1: news
2: articles
3: reviews
4: content pages
5: comments
6: forum threads
7: forum posts
8: forum specific post (specify id)
*/
	
require_once("../../class2.php");

list($content_type, $rss_type, $topic_id) = explode(".", e_QUERY);
if (intval($content_type) == false || intval($rss_type) == false) {
	echo "No type specified";
	exit;
}
	
$rss = new rssCreate($content_type, $rss_type, $topic_id);
$rss->buildRss ();

class rssCreate {

	var $contentType;
	var $rssType;
	var $path;
	var $rssItems;
	var $rssQuery;
	var $topicid;

	function rssCreate($content_type, $rss_type, $topic_id) {
		// constructor
		global $sql, $e107, $PLUGINS_DIRECTORY;
		$this -> path = e_PLUGIN."rss_menu/";
		$this -> rssType = $rss_type;
		$this -> topicid = $topic_id;
		switch ($content_type) {
			case 1:
				$this -> contentType = "news";
				$this -> rssQuery = "
				SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
				LEFT JOIN #user AS u ON n.news_author = u.user_id
				LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
				WHERE n.news_class IN (".USERCLASS_LIST.") AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") AND n.news_render_type!=2 ORDER BY news_datestamp DESC LIMIT 0,9";
				$sql->db_Select_gen($this -> rssQuery);
				
				$tmp = $sql->db_getList();



				$this -> rssItems = array();
				$loop=0;
				foreach($tmp as $value) {

					

					$this -> rssItems[$loop]['title'] = htmlspecialchars($value['news_title']);
					$this -> rssItems[$loop]['link'] = "http://".$_SERVER['HTTP_HOST'].e_HTTP."news.php?".$value['news_id'];
					$this -> rssItems[$loop]['description'] = ($rss_type == 3 ? htmlspecialchars($value['news_body']) : htmlspecialchars(substr($value['news_body'], 0, 100)));
					
					$this -> rssItems[$loop]['author'] = $value['user_name'] . "( http://".$_SERVER['HTTP_HOST'].e_HTTP."user.php?id.".$value['news_author']." )";
					$this -> rssItems[$loop]['category'] = "<category domain='".SITEURL."news.php?cat.".$value['news_category']."'>".$value['category_name']."</category>";

					$this -> rssItems[$loop]['comment'] = ( $value['news_allow_comments'] ? "http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?comment.news.".$news_id : "Comments are turned off for this item");
	
					$this -> rssItems[$loop]['pubdate'] = strftime("%a, %d %b %Y %I:%M:00 GMT", $value['news_datestamp']);

					$loop++;
				}


				break;
			case 2:
				$this -> contentType = "articles";
				break;
			case 3:
				$this -> contentType = "reviews";
				break;
			case 4:
				$this -> contentType = "content";
				break;
			case 5:
				$this -> contentType = "comments";
				$this -> rssQuery = "SELECT * FROM #comments ORDER BY comment_datestamp DESC LIMIT 0,9";
				$sql->db_Select_gen($this -> rssQuery);

				$tmp = $sql->db_getList();

				$this -> rssItems = array();
				$loop=0;
				foreach($tmp as $value) {
					$this -> rssItems[$loop]['title'] = htmlspecialchars($value['comment_subject']);

					switch ($value['comment_type']) {
						case 0:
							$this -> rssItems[$loop]['link'] = "http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?comment.news.".$value['comment_item_id'];
							break;
						case 4:
							$this -> rssItems[$loop]['link'] = "http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?comment.poll.".$value['comment_item_id'];
							break;
					}

					$this -> rssItems[$loop]['description'] = htmlspecialchars($value['comment_comment']);
					$this -> rssItems[$loop]['author'] = substr($value['comment_author'], (strpos($value['comment_author'], ".")+1));
					$loop++;
				}

				break;
			case 6:
				$this -> contentType = "forum threads";
				$this -> rssQuery = "SELECT tp.thread_name AS parent_name, t.thread_thread, t.thread_id, t.thread_name, t.thread_datestamp, t.thread_parent, t.thread_user, t.thread_views, t.thread_lastpost, t.thread_anon, t.thread_lastuser, t.thread_total_replies, f.forum_id, f.forum_name, f.forum_class, u.user_name FROM e107_forum_t AS t 
				LEFT JOIN e107_user AS u ON t.thread_user = u.user_id 
				LEFT JOIN e107_forum_t AS tp ON t.thread_parent = tp.thread_id 
				LEFT JOIN e107_forum AS f ON f.forum_id = t.thread_forum_id 
				WHERE f.forum_class  IN (0, 255) AND t.thread_parent=0 
				ORDER BY t.thread_datestamp DESC LIMIT 0, 9";
				$sql->db_Select_gen($this -> rssQuery);
				$tmp = $sql->db_getList();

				$this -> rssItems = array();
				$loop=0;
				foreach($tmp as $value) {
					if($value['thread_user']) {
						$this -> rssItems[$loop]['author'] = $value['user_name'] . " ( ".$e107->HTTPPath."user.php?id.".$value['thread_user']." )";
					} else {
						list($this -> rssItems[$loop]['author'], $ip) = explode(chr(1), $value['thread_anon']);
					}
					$this -> rssItems[$loop]['title'] = htmlspecialchars($value['thread_name']);
					$this -> rssItems[$loop]['link'] = $e107->HTTPPath.$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?".$value['thread_id'];
					$this -> rssItems[$loop]['description'] = htmlspecialchars($value['thread_thread']);
					$loop++;
				}
				break;

			case 7:
				$this -> contentType = "forum posts";
				$this -> rssQuery = "SELECT tp.thread_name AS parent_name, t.thread_thread, t.thread_id, t.thread_name, t.thread_datestamp, t.thread_parent, t.thread_user, t.thread_views, t.thread_lastpost, t.thread_anon, t.thread_lastuser, t.thread_total_replies, f.forum_id, f.forum_name, f.forum_class, u.user_name FROM e107_forum_t AS t 
				LEFT JOIN e107_user AS u ON t.thread_user = u.user_id 
				LEFT JOIN e107_forum_t AS tp ON t.thread_parent = tp.thread_id 
				LEFT JOIN e107_forum AS f ON f.forum_id = t.thread_forum_id 
				WHERE f.forum_class  IN (0, 255)
				ORDER BY t.thread_datestamp DESC LIMIT 0, 9";
				$sql->db_Select_gen($this -> rssQuery);
				$tmp = $sql->db_getList();
				$this -> rssItems = array();
				$loop=0;
				foreach($tmp as $value) {
					if($value['thread_user']) {
						$this -> rssItems[$loop]['author'] = $value['user_name'] . " ( ".$e107->HTTPPath."user.php?id.".$value['thread_user']." )";
					} else {
						list($this -> rssItems[$loop]['author'], $ip) = explode(chr(1), $value['thread_anon']);
					}

					if($value['parent_name']) {
						$this -> rssItems[$loop]['title'] = "Re: ".htmlspecialchars($value['parent_name']);
						$this -> rssItems[$loop]['link'] = $e107->HTTPPath.$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?".$value['thread_parent'];
					} else {
						$this -> rssItems[$loop]['title'] = htmlspecialchars($value['thread_name']);
						$this -> rssItems[$loop]['link'] = $e107->HTTPPath.$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?".$value['thread_id'];
					}
					$this -> rssItems[$loop]['description'] = htmlspecialchars($value['thread_thread']);
					$loop++;
				}
				break;

			case 8:
				$this -> contentType = "forum topic / replies";

				/* get thread ...  */
				$this -> rssQuery = "SELECT t.thread_name, t.thread_thread, t.thread_id, t.thread_name, t.thread_datestamp, t.thread_parent, t.thread_user, t.thread_views, t.thread_lastpost, t.thread_anon, f.forum_id, f.forum_name, f.forum_class, u.user_name 
				FROM e107_forum_t AS t 
				LEFT JOIN e107_user AS u ON t.thread_user = u.user_id 
				LEFT JOIN e107_forum AS f ON f.forum_id = t.thread_forum_id 
				WHERE f.forum_class  IN (0, 255) AND t.thread_id=".$this -> topicid;
				$sql->db_Select_gen($this -> rssQuery);
				$topic = $sql->db_Fetch();
				
				/* get replies ...  */
				$this -> rssQuery = "SELECT t.thread_name, t.thread_thread, t.thread_id, t.thread_name, t.thread_datestamp, t.thread_parent, t.thread_user, t.thread_views, t.thread_lastpost, t.thread_anon, f.forum_id, f.forum_name, f.forum_class, u.user_name 
				FROM e107_forum_t AS t 
				LEFT JOIN e107_user AS u ON t.thread_user = u.user_id 
				LEFT JOIN e107_forum AS f ON f.forum_id = t.thread_forum_id 
				WHERE f.forum_class  IN (0, 255) AND t.thread_parent=".$this -> topicid;
				$sql->db_Select_gen($this -> rssQuery);
				$replies = $sql->db_getList();

				$this -> rssItems = array();
				$loop = 0;
				if($topic['thread_user']) {
					$this -> rssItems[$loop]['author'] = $topic['user_name'] . " ( ".$e107->HTTPPath."user.php?id.".$topic['thread_user']." )";
				} else {
					list($this -> rssItems[$loop]['author'], $ip) = explode(chr(1), $topic['thread_anon']);
				}

				
				$this -> rssItems[$loop]['title'] = htmlspecialchars($topic['thread_name']);
				$this -> rssItems[$loop]['link'] = $e107->HTTPPath.$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?".$topic['thread_id'];
				
				$this -> rssItems[$loop]['description'] = htmlspecialchars($topic['thread_thread']);
				
				$loop ++;

				foreach($replies as $value) {
					if($value['thread_user']) {
						$this -> rssItems[$loop]['author'] = $value['user_name'] . " ( ".$e107->HTTPPath."user.php?id.".$value['thread_user']." )";
					} else {
						list($this -> rssItems[$loop]['author'], $ip) = explode(chr(1), $value['thread_anon']);
					}

					
					$this -> rssItems[$loop]['title'] = "Re: ".htmlspecialchars($topic['thread_name']);
					$this -> rssItems[$loop]['link'] = $e107->HTTPPath.$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?".$this -> topicid;
					
					$this -> rssItems[$loop]['description'] = htmlspecialchars($value['thread_thread']);
					$loop++;
				}


				//echo "<pre>"; print_r($topic); echo "</pre>";
				//echo "<pre>"; print_r($replies); echo "</pre>";

				break;

		}
	}


	function buildRss() {
		global $sql, $pref;

	header('Content-type: text/xml', TRUE);

		switch ($this -> rssType) {

			case 1:
				echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>
						<!-- generator=\"e107\" -->
						<!-- content type=\"".$this -> contentType."\" -->
						<rss version=\"0.92\">
						<channel>
						<title>".$pref['sitename']."</title>
						<link>".$pref['siteurl']."</link>
						<description>".$pref['sitedescription']."</description>
						<lastBuildDate>".$itemdate = strftime("%a, %d %b %Y %I:%M:00 GMT", time())."</lastBuildDate>
						<docs>http://backend.userland.com/rss092</docs>";
					foreach($this -> rssItems as $value) {
						echo "
							<item>
							<title>".$value['title']."</title>
							<description>".$value['description']."</description>
							<author>".$value['author']."</author>
							<link>".$value['link']."</link>
							</item>";
					}
					echo "
						</channel>
						</rss>";
					break;
				
				case 2: // rss 2.0
			$sitebutton = (strstr(SITEBUTTON, "http:") ? SITEBUTTON : SITEURL.str_replace("../", "", e_IMAGE).SITEBUTTON);
			echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>
				<!-- generator=\"e107\" -->
				<!-- content type=\"".$this -> contentType."\" -->
				<rss version=\"2.0\">
				<channel>
				<title>".$pref['sitename']."</title>
				<link>".$pref['siteurl']."</link>
				<description>".$pref['sitedescription']."</description>
				<language>en-gb</language>
				<copyright>".ereg_replace("<br />|\n", "", SITEDISCLAIMER)."</copyright>
				<managingEditor>".$pref['siteadmin']." - ".$pref['siteadminemail']."</managingEditor>
				<webMaster>".$pref['siteadminemail']."</webMaster>
				<pubDate>".strftime("%a, %d %b %Y %I:%M:00 GMT", time())."</pubDate>
				<lastBuildDate>".strftime("%a, %d %b %Y %I:%M:00 GMT", time())."</lastBuildDate>
				<docs>http://backend.userland.com/rss</docs>
				<generator>e107 (http://e107.org)</generator>
				<ttl>60</ttl>
				 
				<image>
				<title>".$pref['sitename']."</title>
				<url>".(strstr(SITEBUTTON, "http:") ? SITEBUTTON : SITEURL.str_replace("../", "", e_IMAGE).SITEBUTTON)."</url>
				<link>".$pref['siteurl']."</link>
				<width>88</width>
				<height>31</height>
				<description>".$pref['sitedescription']."</description>
				</image>
				 
				<textInput>
				<title>Search</title>
				<description>Search ".$pref['sitename']."</description>
				<name>query</name>
				<link>".SITEURL.(substr(SITEURL, -1) == "/" ? "" : "/")."search.php</link>
				</textInput>";
			foreach($this -> rssItems as $value) {
				echo "
					<item>
					<title>".$value['title']."</title>
					<link>".$value['link']."</link>
					<description>".$value['description']."</description>
					".$value['category']."
					<comments>".$value['comment']."</comments>
					<author>".$value['author']."</author>
					<pubDate>".$value['pubdate']."</pubDate>
					<guid isPermaLink=\"true\">".$value['link']."</guid>
					</item>";
			}
			echo "
				</channel>
				</rss>";
			break;

			case 3: // rdf
			echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
				<!-- generator=\"e107\" -->
				<!-- content type=\"".$this -> contentType."\" -->
				<rdf:RDF xmlns=\"http://purl.org/rss/1.0/\" xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:sy=\"http://purl.org/rss/1.0/modules/syndication/\" xmlns:admin=\"http://webns.net/mvcb/\" xmlns:content=\"http://purl.org/rss/1.0/modules/content/\">
				<channel rdf:about=\"".$pref['siteurl']."\">
				<title>".$pref['sitename']."</title>
				<link>".$pref['siteurl']."</link>
				<description>".$pref['sitedescription']."</description>
				<dc:language>en</dc:language>
				<dc:date>".strftime("%a, %d %b %Y %I:%M:00 GMT", time())."</dc:date>
				<dc:creator>".$pref['siteadminemail']."</dc:creator>
				<admin:generatorAgent rdf:resource=\"http://e107.org\" />
				<admin:errorReportsTo rdf:resource=\"mailto:".$pref['siteadminemail']."\" />
				<sy:updatePeriod>dynamic</sy:updatePeriod>
				<sy:updateFrequency>1</sy:updateFrequency>
				<sy:updateBase>2000-01-01T12:00+00:00</sy:updateBase>
				<items>
				<rdf:Seq>";
			 
			foreach($this -> rssItems as $value) {
				echo "
					<rdf:li rdf:resource=\"".$value['link']."\" />";
			}
			 
			echo "
				</rdf:Seq>
				</items>
				</channel>";
			 
			reset($this -> rssItems);
			foreach($this -> rssItems as $value) {
				echo "
					<item rdf:about=\"".$value['link']."\">
					<title>".$value['title']."</title>
					<link>".$value['link']."</link>
					<dc:date>".strftime("%a, %d %b %Y %I:%M:00 GMT", time())."</dc:date>
					<dc:creator>".$value['author']."</dc:creator>
					<dc:subject>".$value['category_name']."</dc:subject>
					<description>".$value['description']."</description>
					</item>";
			}
			echo "
				</rdf:RDF>";
			break;

		}


	}






}
	
?>