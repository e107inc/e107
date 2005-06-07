<?php

@include_once(e_PLUGIN.'newsfeed/languages/'.e_LANGUAGE.'_frontpage.php');
@include_once(e_PLUGIN.'newsfeed/languages/English_frontpage.php');

$front_page['newsfeed']['title'] = NWSF_FP_1.': '.$row['content_heading'];
$front_page['newsfeed']['page'][] = array('page' => $PLUGINS_DIRECTORY.'newsfeed/newsfeed.php', 'title' => NWSF_FP_2);

if ($sql -> db_Select("newsfeed", "newsfeed_id, newsfeed_name")) {
	while ($row = $sql -> db_Fetch()) {
		$front_page['newsfeed']['page'][] = array('page' => $PLUGINS_DIRECTORY.'newsfeed/newsfeed.php?show.'.$row['newsfeed_id'], 'title' => $row['newsfeed_name']);
	}
}

?>