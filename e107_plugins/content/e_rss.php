<?php

if (!defined('e107_INIT')) { exit; }



require_once(e_PLUGIN."content/handlers/content_class.php");
$aa = new content;

//##### create feed for admin, return array $eplug_rss_feed --------------------------------
$feed = get_rss();
foreach($feed as $k=>$v){
	$eplug_rss_feed[] = $v;
}

function get_rss(){
	global $aa;

	require_once(e_PLUGIN."content/handlers/content_class.php");
	$aa = new content;
	$rss = array();
	$array = $aa -> getCategoryTree('', '', FALSE);
	foreach($array as $k=>$v){
		$name = '';
		for($i=0;$i<count($array[$k]);$i++){
			$name .= $array[$k][$i+1]." > ";
			$i++;
		}
		$name = substr($name,0,-3);
		$feed['name']			= $name;
		$feed['url']			= 'content';		//the identifier for the rss feed url
		$feed['topic_id']		= $k;				//the topic_id, empty on default (to select a certain category)
		$feed['path']			= 'content';		//this is the plugin path location
		$feed['text']			= 'this is the rss feed for content category : '.$name;
		$feed['class']			= '0';
		$feed['limit']			= '9';
		$rss[] = $feed;
	}
	return $rss;
}
//##### ------------------------------------------------------------------------------------


//##### create rss data, return as array $eplug_rss_data -----------------------------------
$mainparent		= $aa -> getMainParent($this->topicid);
$content_pref	= $aa -> getContentPref($mainparent);
$array			= $aa -> getCategoryTree("", $mainparent, TRUE);
$datequery		= " AND content_datestamp < ".time()." AND (content_enddate=0 || content_enddate>".time().") ";
$qry			= " content_refer !='sa' ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' AND content_parent = '".$this->topicid."' ORDER BY content_datestamp DESC LIMIT 0,".$this -> limit;

$rss = array();
$sqlrss = new db;
if($items = $sqlrss -> db_Select('pcontent', "*", $qry )){
	$i=0;
	while($rowrss = $sqlrss -> db_Fetch()){
		//$author = array($author_id, $author_name, $author_email, $content_author);
		$author = $aa -> getAuthor($rowrss['content_author']);
		$rss[$i]['author']			= $author[1];
		$rss[$i]['author_email']	= $author[2];
		$rss[$i]['link']			= $e107->base_path.$PLUGINS_DIRECTORY."content/content.php?content.".$rowrss['content_id'];
		$rss[$i]['linkid']			= $rowrss['content_id'];
		$rss[$i]['title']			= $rowrss['content_heading'];
		$rss[$i]['description']		= $rowrss['content_subheading'];
		$rss[$i]['category_name']	= $array[$rowrss['content_parent']][count($array[$rowrss['content_parent']])-1];
		$rss[$i]['category_link']	= $e107->base_path.$PLUGINS_DIRECTORY."content/content.php?cat.".$rowrss['content_parent'];
		$rss[$i]['datestamp']		= $rowrss['content_datestamp'];
		$rss[$i]['enc_url']			= "";
		$rss[$i]['enc_leng']		= "";
		$rss[$i]['enc_type']		= "";
		$i++;
	}
}
$eplug_rss_data[] = $rss;
//##### ------------------------------------------------------------------------------------

?>