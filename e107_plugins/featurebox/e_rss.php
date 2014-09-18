<?php

if (!defined('e107_INIT')) { exit; }

//FIXME TODO - Use v2 method. See chatbox_menu/e_rss.php

//##### create feed for admin, return array $eplug_rss_feed --------------------------------
$feed['name']		= 'Featurebox';
$feed['url']		= 'featurebox';			//the identifier for the rss feed url
$feed['topic_id']	= '';					//the topic_id, empty on default (to select a certain category)
$feed['path']		= 'featurebox';			//this is the plugin path location
$feed['text']		= 'this is the rss feed for the featurebox entries';
$feed['class']		= '0';
$feed['limit']		= '9';
$eplug_rss_feed[] = $feed;
//##### ------------------------------------------------------------------------------------


//##### create rss data, return as array $eplug_rss_data -----------------------------------
$rss = array();
$sqlrss = new db;
if($items = $sqlrss->select('featurebox', "*", "fb_class = 0 DESC LIMIT 0,".$this->limit )){
	$i=0;
	while($rowrss = $sqlrss->fetch()){
		$rss[$i]['author']			= '';
		$rss[$i]['author_email']	= '';
		$rss[$i]['link']			= '';
		$rss[$i]['linkid']			= '';
		$rss[$i]['title']			= $rowrss['fb_title'];
		$rss[$i]['description']		= $rowrss['fb_text'];
		$rss[$i]['category_name']	= '';
		$rss[$i]['category_link']	= '';
		$rss[$i]['datestamp']		= '';
		$rss[$i]['enc_url']			= '';
		$rss[$i]['enc_leng']		= '';
		$rss[$i]['enc_type']		= '';
		$i++;
	}
}
$eplug_rss_data[] = $rss;
//##### ------------------------------------------------------------------------------------

?>