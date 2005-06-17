<?php

	if($mode == "new_page" || $mode == "new_menu" ){
		$lvisit = $this -> getlvisit();
		$qry = " news_datestamp>".$lvisit;
	}else{
		$qry = " (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().") ";
	}

	$bullet = $this -> getBullet($arr[6], $mode);
	
	$LIST_CAPTION = $arr[0];
	$LIST_DISPLAYSTYLE = ($arr[2] ? "" : "none");

	$qry = "
	SELECT n.*, c.category_id AS news_category_id, c.category_name AS news_category_name, u.user_id AS news_author_id, u.user_name AS news_author_name
	FROM #news AS n
	LEFT JOIN #news_category AS c ON c.category_id = n.news_category
	LEFT JOIN #user AS u ON n.news_author = u.user_id
	WHERE ".$qry." AND n.news_class REGEXP '".e_CLASS_REGEXP."'
	ORDER BY n.news_datestamp DESC LIMIT 0,".$arr[7]." 
	";

	if(!$sql -> db_Select_gen($qry)){
		$LIST_DATA = LIST_NEWS_2;
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

			$rowheading	= $this -> parse_heading($news_title, $mode);
			$ICON		= $bullet;
			$HEADING	= "<a href='".e_BASE."comment.php?comment.news.".$row['news_id']."' title='".$news_title."'>".$rowheading."</a>";
			$AUTHOR		= ($arr[3] ? ($row['news_author'] == 0 ? $row['news_author'] : ($row['news_author_name'] ? "<a href='".e_BASE."user.php?id.".$row['news_author_id']."'>".$row['news_author_name']."</a>" : "") ) : "");
			$CATEGORY	= ($arr[4] ? "<a href='".e_BASE."news.php?cat.".$row['news_category_id']."'>".$row['news_category_name']."</a>" : "");
			$DATE		= ($arr[5] ? $this -> getListDate($row['news_datestamp'], $mode) : "");
			$INFO		= "";
			$LIST_DATA[$mode][] = array( $ICON, $HEADING, $AUTHOR, $CATEGORY, $DATE, $INFO );
		}
	}

?>