<?php










/*
Query string: content_type.rss_type
1: news
2: articles
3: reviews
4: content pages
5: comments
6: forum threads
*/
require_once("class2.php");

list($content_type, $rss_type) = explode(".", e_QUERY);
if(intval($content_type) == false || intval($rss_type) == false) {
	echo "No type specified";
	exit;
}

$rss = new rssCreate();

switch ($content_type) {
	case 1:	// news
	$query = "SELECT #news.*, user_id, user_name, user_customtitle, user_email, category_name, category_icon FROM #news
LEFT JOIN #user ON #news.news_author = #user.user_id 
LEFT JOIN #news_category ON #news.news_category = #news_category.category_id 
WHERE news_class != '255' AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().") AND news_render_type!=2 ORDER BY news_datestamp DESC LIMIT 0, 9";
	break;

	case 2:	// articles

	// etc etc

	break;
}


$rss -> buildRss ($query, $rss_type);
//echo "<pre>"; print_r($pref); echo "</pre>";



/**
 * Handles output of RSS Data Feeds. Inc: 0.92, 2.0 and RDF
 *
 */
class rssCreate {
	function buildRss($query, $rss_type) {
		global $sql, $pref, $rsslink;

		$sql -> db_Select_gen($query);

		$items = $sql -> db_getList();
		header('Content-type: text/xml', TRUE);

		switch ($rss_type)
		{
			case 1:	//	rss 0.92
			echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>
<!-- generator=\"e107\" -->
<rss version=\"0.92\">
    <channel>
        <title>".$pref['sitename']."</title>
        <link>".$pref['siteurl']."</link>
        <description>".$pref['sitedescription']."</description>
		<lastBuildDate>".$itemdate = strftime("%a, %d %b %Y %I:%M:00 GMT", time())."</lastBuildDate>
        <docs>http://backend.userland.com/rss092</docs>";
			foreach($items as $key => $value)
			{
				echo "
		<item>
            <title>".htmlspecialchars($value['news_title'])."</title>
			<description>".htmlspecialchars(substr($value['news_body'], 0, 100))."</description>
			<link>http://".$_SERVER['HTTP_HOST'].e_HTTP."news.php?".$value['news_id']."</link>
		</item>";
			}
			echo "
	</channel>
</rss>";
			break;

			case 2:	//	rss 2.0
			$sitebutton = (strstr(SITEBUTTON, "http:") ? SITEBUTTON : SITEURL.str_replace("../", "", e_IMAGE).SITEBUTTON);
			echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>
<!-- generator=\"e107\" -->
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
			foreach($items as $key => $value)
			{
				echo "
		<item>
			<title>".htmlspecialchars($value['news_title'])."</title>
			<link>http://".$_SERVER['HTTP_HOST'].e_HTTP."news.php?".$value['news_id']."</link>
			<description>".htmlspecialchars(substr($value['news_body'], 0, 100))."</description>
			<category domain='".SITEURL."news.php?cat.".$value['news_category']."'>".$value['category_name']."</category>";
				if($value['news_allow_comments'])
				{
					echo "<comments>http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?comment.news.".$news_id."</comments>\n";
				}
				else
				{
					echo "<comments>turned off for this item</comments>\n";
				}
				echo "<author>".$value['user_name']."</author>
			<pubDate>".strftime("%a, %d %b %Y %I:%M:00 GMT", $value['news_datestamp'])."</pubDate>
			<guid isPermaLink=\"true\">http://".$_SERVER['HTTP_HOST'].e_HTTP."news.php?".$value['news_id']."</guid>
		</item>";
			}
			echo "
	</channel>
</rss>";
			break;


			case 3:	//	rdf
			echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<!-- generator=\"e107\" -->
<rdf:RDF xmlns=\"http://purl.org/rss/1.0/\" xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:sy=\"http://purl.org/rss/1.0/modules/syndication/\" xmlns:admin=\"http://webns.net/mvcb/\" xmlns:content=\"http://purl.org/rss/1.0/modules/content/\">
	<channel rdf:about=\"".$pref['siteurl']."\">
		<title>".$pref['sitename']."</title>
		<link>".$pref['siteurl']."</link>
		<description>".$pref['sitedescription']."</description>
		<dc:language>en</dc:language>
		<dc:date>".strftime("%a, %d %b %Y %I:%M:00 GMT", time())."</dc:date>
		<dc:creator>".$pref['siteadminemail']."</dc:creator>
		<admin:generatorAgent rdf:resource=\"http://jalist.com\" />
		<admin:errorReportsTo rdf:resource=\"mailto:".$pref['siteadminemail']."\" />
		<sy:updatePeriod>dynamic</sy:updatePeriod> 
		<sy:updateFrequency>1</sy:updateFrequency>
		<sy:updateBase>2000-01-01T12:00+00:00</sy:updateBase>
		<items>
			<rdf:Seq>";

			foreach($items as $key => $value)
			{
				echo "
				<rdf:li rdf:resource=\"http://".$_SERVER['HTTP_HOST'].e_HTTP."news.php?".$value['news_id']."\" />";
			}

			echo "
			</rdf:Seq>
		</items>
	</channel>";

			reset($value);
			foreach($items as $key => $value)
			{
				echo "
	<item rdf:about=\"http://".$_SERVER['HTTP_HOST'].e_HTTP."news.php?".$value['news_id']."\">
		<title>".htmlspecialchars($value['news_title'])."</title>
		<link>http://".$_SERVER['HTTP_HOST'].e_HTTP."news.php?".$value['news_id']."</link>
		<dc:date>".strftime("%a, %d %b %Y %I:%M:00 GMT", time())."</dc:date>
		<dc:creator>".$value['user_name']." (".$value['user_email'].")</dc:creator>
		<dc:subject>".$value['category_name']."</dc:subject>
		<description>".htmlspecialchars($value['news_body'])."</description>
	</item>";
			}
			echo "
</rdf:RDF>";
			break;

		}
	}
}

?>