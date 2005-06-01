<?php

@include_once(e_PLUGIN.'content/languages/'.e_LANGUAGE.'/lan_content_frontpage.php');
@include_once(e_PLUGIN.'content/languages/English/lan_content_frontpage.php');

$sql2 = new db;
if ($sql2 -> db_Select("pcontent", "content_id, content_heading", "content_parent='0'")) {
	while ($row = $sql2 -> db_Fetch()) {
		$front_page['content_'.$row['content_id']]['title'] = CONT_FP_1.': '.$row['content_heading'];
		$front_page['content_'.$row['content_id']]['page'][] = array('page' => $PLUGINS_DIRECTORY.'content/content.php?type.'.$row['content_id'], 'title' => $row['content_heading'].' '.CONT_FP_2);
		if ($sql -> db_Select("pcontent", "content_id, content_heading", "LEFT(content_parent,".(strlen($row['content_id'])).") = '".$row['content_id']."' ORDER BY content_heading")){
			while ($row2 = $sql -> db_Fetch()) {
				$front_page['content_'.$row['content_id']]['page'][] = array('page' => $PLUGINS_DIRECTORY.'content/content.php?type.'.$row['content_id'].'.content.'.$row2['content_id'], 'title' => $row2['content_heading']);
			}
		}
	}
}

?>