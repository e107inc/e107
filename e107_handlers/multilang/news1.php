<?php
/*
*
* Code used by news.php for multilanguage
*
*/
// $Id: news1.php,v 1.1 2004-10-10 21:20:07 loloirie Exp $ 

if(!$ml -> e107_ml_Select("news", "*", $query)){
	echo "<br /><br /><div style='text-align:center'><b>".(strstr(e_QUERY, "month") ? LAN_462 : LAN_83)."</b></div><br /><br />";
}else{
	$sql2 = new db;
	while(list($news['news_id'], $news['news_title'], $news['data'], $news['news_extended'], $news['news_datestamp'], $news['admin_id'], $news_category, $news['news_allow_comments'],  $news['news_start'], $news['news_end'], $news['news_class'], $news['news_rendertype']) = $sql -> db_Fetch()){
	
	                if(check_class($news['news_class'])){
	                        if($news['admin_id'] == 1 && $pref['siteadmin']){
	                                $news['admin_name'] = $pref['siteadmin'];
	                        }else if(!$news['admin_name'] = getcachedvars($news['admin_id'])){
	                                $sql2 -> db_Select("user", "user_name", "user_id='".$news['admin_id']."' ");
	                                list($news['admin_name']) = $sql2 -> db_Fetch();
	                                cachevars($news['admin_id'], $news['admin_name']);
	                        }
	                        $sql2 -> db_Select("news_category", "*",  "category_id='$news_category' ");
	                        list($news['category_id'], $news['category_name'], $news['category_icon']) = $sql2-> db_Fetch();
	                        $news['comment_total'] = $sql2 -> db_Count("comments", "(*)",  "WHERE comment_item_id='".$news['news_id']."' AND comment_type='0' ");
	                        if($action == "item"){ unset($news['news_rendertype']); }
	                        $ix -> render_newsitem($news);
	                }
	}
}


?>
