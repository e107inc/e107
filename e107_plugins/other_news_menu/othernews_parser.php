<?php
function othernews_parser($news_array,$template,$category_array,$style){
global $tp;


extract($news_array);
if (check_class($news_class)) {

			$search[0] = "/\{OTHERNEWS_LINK\}(.*?)/si";
			$replace[0] = ($news_id) ? "<a style='".$style['itemlink']."' href='".e_BASE."news.php?item.$news_id' title=\"$news_title\" >$news_title</a>" : "";

			$search[1] = "/\{OTHERNEWS_SUMMARY\}(.*?)/si";
			$replace[1] =  ($news_summary) ? $tp->toHTML($news_summary) : "" ;

			$search[2] = "/\{OTHERNEWS_THUMBNAIL\}(.*?)/si";
			$replace[2] = ($news_thumb) ? "<a href='".e_BASE."news.php?item.".$news_id."' title=\"$news_title\"><img src='".e_IMAGE."newspost_images/".$news_thumb."' alt='' style='".$style['thumb']."' title=\"$news_title\" /></a>" : "";

			$search[3] = "/\{OTHERNEWS_CATEGORY\}(.*?)/si";
			$replace[3] = "<a style='".$style['catlink']."' href='".e_BASE."news.php?cat.".$news_category."'>".$category_array['name'][$news_category]."</a>" ;

			$search[4] = "/\{OTHERNEWS_CATICON\}(.*?)/si";
			$replace[4] = "<a href='".e_BASE."news.php?cat.".$news_category."'><img style='".$style['caticon']."' src='".e_IMAGE."icons/".$category_array['icon'][$news_category]."' alt='' style='border:0px' /></a>";

			return preg_replace($search, $replace,$template);
		}
}
?>