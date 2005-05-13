<?php

	global $sql;
	global $RECENT_MENU, $RECENT_MENU_START, $RECENT_MENU_END, $RECENT_PAGE_START, $RECENT_PAGE, $RECENT_PAGE_END;
	global $RECENT_ICON, $RECENT_DATE, $RECENT_HEADING, $RECENT_AUTHOR, $RECENT_CATEGORY, $RECENT_INFO;
	global $RECENT_DISPLAYSTYLE, $RECENT_CAPTION, $RECENT_STYLE_CAPTION, $RECENT_STYLE_BODY;

	$bullet = $this -> getBullet($arr[6], $mode);
	
	$RECENT_CAPTION = $arr[0];
	$RECENT_DISPLAYSTYLE = ($arr[2] ? "" : "none");

	$sql2 = new db; $sql3 = new db;
	if(!$sql -> db_Select("news", "*", "news_class REGEXP '".e_CLASS_REGEXP."' AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().") ORDER BY news_datestamp DESC LIMIT 0,".$arr[7]." ")){ 
		$RECENT_DATA = "no news items";
	}else{
		while($row = $sql -> db_Fetch()){
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
				$news_title = preg_replace($search, $replace, $row['news_title']);
				// End of code from Lisa

				//get author name
				$sql2 -> db_Select("user", "user_name", "user_id = '".$row['news_author']."' ");
				list($news_author_name) = $sql2 -> db_Fetch();

				//get category
				$sql3 -> db_Select("news_category", "category_name", "category_id = '".$row['news_category']."' ");
				list($news_category_name) = $sql3 -> db_Fetch();

				$rowheading = $this -> parse_heading($news_title, $mode);

				$ICON = $bullet;
				$HEADING = "<a href='".e_BASE."comment.php?comment.news.".$row['news_id']."' title='".$news_title."'>".$rowheading."</a>";
				$AUTHOR = ($arr[3] ? ($row['news_author'] == 0 ? $row['news_author'] : ($news_author_name ? "<a href='".e_BASE."user.php?id.".$row['news_author']."'>".$news_author_name."</a>" : "") ) : "");
				$CATEGORY = ($arr[4] ? $news_category_name : "");
				$DATE = ($arr[5] ? $this -> getRecentDate($row['news_datestamp'], $mode) : "");
				$INFO = "";

				$RECENT_DATA[$mode][] = array( $ICON, $HEADING, $AUTHOR, $CATEGORY, $DATE, $INFO );
		}
	}

?>