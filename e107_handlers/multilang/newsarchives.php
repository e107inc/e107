<?php
/*
*
* Code used by news.php for multilanguage archives
*
*/
// $Id: newsarchives.php,v 1.1 2004-10-10 21:20:07 loloirie Exp $ 
while(list($news2['news_id'], $news2['news_title'], $news2['data'], $news2['news_extended'], $news2['news_datestamp'], $news2['admin_id'], $news2_category, $news2['news_allow_comments'],  $news2['news_start'], $news2['news_end'], $news2['news_class'], $news2['news_rendertype']) = $sql2b -> db_Fetch()){
				
		if(check_class($news2['news_class'])){
			if($action == "item"){ unset($news2['news_rendertype']); }

			// Code from Lisa
			// copied from the rss creation, but added here to make sure the url for the newsitem is to the news.php?item.X
			// instead of the actual hyperlink that may have been added to a newstitle on creation
			$search = array();
			$replace = array();
			$search[0] = "/\<a href=\"(.*?)\">(.*?)<\/a>/si";
			$replace[0] = '\\2';
			$search[1] = "/\<a href='(.*?)'>(.*?)<\/a>/si";
			$replace[1] = '\\2';
			$search[2] = "/\<a href='(.*?)'>(.*?)<\/a>/si";
			$replace[2] = '\\2';
			$search[3] = "/\<a href=&quot;(.*?)&quot;>(.*?)<\/a>/si";
			$replace[3] = '\\2';
			$search[4] = "/\<a href=&#39;(.*?)&#39;>(.*?)<\/a>/si";
			$replace[4] = '\\2';
			$news2['news_title'] = preg_replace($search, $replace, $news2['news_title']);
			// End of code from Lisa

			$gen = new convert;
			$news2['news_datestamp'] = $gen->convert_date($news2['news_datestamp'], "short");
			if(!is_object($sql2)){$sql2 = new db;}
			if(!is_object($ml2)){$ml2 = new e107_ml;}
			// ML original by Lolo Irie
			if(e_MLANG == 1){
        $ml2 -> e107_ml_Select("news_category", "*",  "category_id='$news2_category' ", "default", FALSE, "sql2");
      }else{
        $sql2 -> db_Select("news_category", "*",  "category_id='$news2_category' ");
      }
      // END ML
      
			list($news2['category_id'], $news2['category_name'], $news2['category_icon']) = $sql2-> db_Fetch();
			$news2['comment_total'] = $sql2 -> db_Count("comments", "(*)",  "WHERE comment_item_id='".$news2['news_id']."' AND comment_type='0' ");

			$textnewsarchive .= "
			<div>
			<table style='width:98%;'>
				<tr>
					<td>
						<div><img src='".THEME."images/bullet2.gif' border='0' style='border:0;' alt='' /> <b><a href='news.php?item.".$news2['news_id']."'>".$news2['news_title']."</a></b> <span class='smalltext' ><i>(".$news2['news_datestamp'].") (".$news2['category_name'].")</i></span></div>
					</td>
				</tr>
			</table>
			</div>";
		}
}

?>
