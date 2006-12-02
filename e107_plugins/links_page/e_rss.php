<?php

if (!defined('e107_INIT')) { exit; }


//------  create feed for admin, return array $eplug_rss_feed ------------------

$feed['name']		= 'Links Page';
$feed['url']		= 'links';			//the identifier for the rss feed url
$feed['topic_id']	= '';					//the topic_id, empty on default (to select a certain category)
$feed['path']		= 'links_page';		//this is the plugin path location
$feed['text']		= 'this is the rss feed for the links_page entries';
$feed['class']		= '0';
$feed['limit']		= '9';

//------ create rss data, return as array $eplug_rss_data -----------------------

$qry = "
SELECT l.*, c.link_category_id, c.link_category_name
FROM #links_page AS l
LEFT JOIN #links_page_cat AS c ON c.link_category_id = l.link_category
WHERE l.link_class REGEXP '".e_CLASS_REGEXP."' AND c.link_category_class REGEXP '".e_CLASS_REGEXP."'
ORDER BY l.link_datestamp DESC LIMIT 0,".$this->limit;

$rss = array();
$sqlrss = new db;
if($items = $sqlrss->db_Select_gen($qry)){
	$i=0;
	while($rowrss = $sqlrss -> db_Fetch()){
		$tmp						= '';
		$rss[$i]['author']			= $tmp[1];
		$rss[$i]['author_email']	= $tmp[2];
		$rss[$i]['link']			= $rowrss['link_url'];
		$rss[$i]['linkid']			= $rowrss['link_id'];
		$rss[$i]['title']			= $rowrss['link_name'];
		$rss[$i]['description']		= '';
		$rss[$i]['category_name']	= $rowrss['link_category_name'];
		$rss[$i]['category_link']	= $e107->base_path.$PLUGINS_DIRECTORY."links_page/links.php?cat.".$rowrss['link_category_id'];
		$rss[$i]['datestamp']		= $rowrss['link_datestamp'];
		$rss[$i]['enc_url']			= "";
		$rss[$i]['enc_leng']		= "";
		$rss[$i]['enc_type']		= "";
		$i++;
	}
}

//##### ------------------------------------------------------------------------------------
$eplug_rss_data[] = $rss;
$eplug_rss_feed[] = $feed;
?>