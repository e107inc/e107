<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/classes/news_class.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).	
+---------------------------------------------------------------+
*/
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
class news{

	function edit_item($existing){
		/*
		# Retrieve news item for editing
		#
		# - parameter #1		string $existing, id of respective table entry
		# - return				array of news item fields
		# - scope					public
		*/
		$cls = new db;
		if($cls -> db_Select("news", "*", "news_id='$existing' ")){
			$row = $cls-> db_Fetch();
		}
		$tp = new textparse;
		$row['news_title'] = $tp -> editparse($row['news_title'], $mode="on");
		$row['news_body'] = $tp -> editparse($row['news_body'], $mode="on");
		$row['news_extended'] = $tp -> editparse($row['news_extended'], $mode="on");
		$row['news_source'] = $tp -> editparse($row['news_source'], $mode="on");
		$row['news_url'] = $tp -> editparse($row['news_url'], $mode="on");
		return $row;
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//	
	function delete_item($news_id){
		/*
		# Delete a news item
		#
		# - parameter #1		string $news_id, id of respective table entry
		# - return				comfort message
		# - scope					public
		*/
		$cls = new db;
		if($cls -> db_Delete("news",  "news_id='$news_id' ")){
			$cls -> db_Delete("comments", "comment_item_id='$news_id' ");
			return  LAN_13;
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function submit_item($news_id, $news_title, $news_body, $news_extended, $news_source, $news_url, $category_id, $allow_comments, $news_start, $news_end, $news_active){
		/*	
		# Enter news item into database
		#
		# - parameter #1		string $news_id, id of news item if already exists (edit), else null
		# - parameter #2		string $news_title
		# - parameter #3		string $news_body
		# - parameter #4		string $news_extended
		# - parameter #5		string $news_source
		# - parameter #6		string $news_url
		# - parameter #7		string $cat_name
		# - parameter #8		string $allow_comments
		# - return				comfort message
		# - scope					public
		*/
		$aj = new textparse;
		$news_title = $aj -> tp($news_title, $mode="on", 0);
		$news_body = $aj -> tp($news_body, $mode="on", 0);
		$news_extended = $aj -> tp($news_extended, $mode="on", 0);
		$news_source = $aj -> tp($news_source, $mode="on", 0);
		$news_url = $aj -> tp($news_url, $mode="on", 0);
		$cls = new db;

		if($news_id != ""){
			if($cls -> db_Update("news", "news_title='$news_title', news_body='$news_body', news_extended='$news_extended', news_source='$news_source', news_url='$news_url', news_category='$category_id', news_allow_comments='$allow_comments', news_start='$news_start', news_end='$news_end', news_active='".$_POST['news_active']."' WHERE news_id='$news_id' ")){
				$message = LAN_14;
			}else{
				$search = array("\"", "'", "\\");
				$replace = array("&quot;", "&#39;", "&#92;");
				$news_title = str_replace($search, $replace, $news_title);
				$news_body = str_replace($search, $replace, $news_body);
				$news_extended = str_replace($search, $replace, $news_extended);

				if($cls -> db_Update("news", "news_title='$news_title', news_body='$news_body', news_extended='$news_extended', news_source='$news_source', news_url='$news_url', news_category='$category_id', news_allow_comments='$allow_comments', news_start='$news_start', news_end='$news_end', news_active='".$_POST['news_active']."' WHERE news_id='$news_id' ")){
					$message = "<b>Had to modify quotemarks and apostrophies to update news item into database - item now entered.</b>";
				}else{
					$message = "<b>Error!</b> Was unable to update news item into database!</b>";
				}
			}
		}else{
			$datestamp = time();
			if($cls -> db_Insert("news","0, '$news_title', '$news_body', '$news_extended', '$datestamp', '".ADMINID."', '$news_source', '$news_url', '$category_id', '$allow_comments', '$news_start', '$news_end', '".$_POST['news_active']."' ")){
				$message = LAN_15;
			}else{
				$search = array("\"", "'", "\\");
				$replace = array("&quot;", "&#39;", "&#92;");
				$news_title = str_replace($search, $replace, $news_title);
				$news_body = str_replace($search, $replace, $news_body);
				$news_extended = str_replace($search, $replace, $news_extended);

				if($cls -> db_Insert("news","0, '$news_title', '$news_body', '$news_extended', '$datestamp', '".ADMINID."', '$news_source', '$news_url', '$category_id', '$allow_comments', '$news_start', '$news_end', '$news_active' ")){
					$message = "<b>Had to modify quotemarks and apostrophies to enter news item into database - item now entered.</b>";
				}else{
					$message = "<b>Error!</b> Was unable to enter news item into database!</b>";
				}
			}
		}
		return $message;
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function preview($news_id, $news_title, $news_body, $news_extended, $news_source, $news_url, $cat_id, $allow_comments, $active_start, $active_end, $news_active){
		/*
		# Preview news item
		#
		# - parameter #1		string $news_id, id of news item if already exists (edit), else null
		# - parameter #2		string $news_title
		# - parameter #3		string $news_body
		# - parameter #4		string $news_extended
		# - parameter #5		string $news_source
		# - parameter #6		string $news_url
		# - parameter #7		string $cat_name
		# - return					null
		# - scope					public
		*/

		$aj = new textparse;
		$news_title = $aj -> tp($news_title, "on");
		$news_body = $aj -> tp($news_body, "on");
		$news_extended = $aj -> tp($news_extended, "on");
		$news_source = $aj -> tp($news_source, "on");
		$news_url = $aj -> tp($news_url, "on");
		
		$this -> render_newsitem($news_id, $news_title, $news_body, $news_extended, $news_source, $news_url, ADMINID, "0", $cat_id,  time(), $allow_comments, $active_start, $active_end, $news_active, "preview");
		return array($cat_id, $news_title, $news_body, $news_extended, $news_source, $news_url);
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function render_newsitem($news_id, $news_title, $news_body, $news_extended, $news_source, $news_url, $news_author, $comment_total, $category_id, $datestamp, $allow_comments, $active_start, $active_end, $news_active, $modex=""){
		/*
		# Render news item to screen
		#
		# - parameter #1		string $news_id, id of news item if already exists (edit), else null
		# - parameter #2		string $news_title
		# - parameter #3		string $news_body
		# - parameter #4		string $news_extended
		# - parameter #5		string $news_source
		# - parameter #6		string $news_url
		# - parameter #7		string $news_author, admin_id of author
		# - parameter #8		string $comment_total, comment count of news item
		# - parameter #9		string $category_name, category name of news item
		# - parameter #10		string $datestamp, post date of news item
		# - parameter #11		string $preview, boolean, true if preview, false if index.php
		# - parameter #12		string $cat_name
		# - return				null
		# - scope					public
		*/
		$aj = new textparse;

		global $NEWSSTYLE;
		
		$news_title = $aj -> tpa($news_title, $mode="on");
		$news_body = $aj -> tpa($news_body, $mode="off");
		$news_extended = $aj -> tpa($news_extended, $mode="off");
		$news_source = $aj -> tpa($news_source, $mode="off");
		$news_url = $aj -> tpa($news_url, $mode="off");

		if(Empty($comment_total)) $comment_total = "0";
		$con = new convert;
		$datestamp = $con -> convert_date($datestamp, "long");
		$cls = new db;
		$cls -> db_Select("user", "*", "user_id='$news_author' ");
		list($a_id, $a_name, $null, $null, $a_email) = $cls-> db_Fetch();

		if($news_title == "Welcome to e107"){
			$a_name = "e107";
			$a_email = "e107@jalist.com";
			$category_name = "e107 welcome message";
			$category_id = 0;
			$category_icon = e_HTTP."button.png";
		}else{
			
			$cls -> db_Select("news_category", "*",  "category_id='$category_id' ");
			list($category_id, $category_name, $category_icon) = $cls-> db_Fetch();

			$sql2 = new db;
			if($sql2 -> db_Select("userclass_classes", "*", "userclass_name='PRIVATENEWS_".strtoupper($category_name)."' ")){
				if(!check_class("PRIVATENEWS_".strtoupper($category_name))){
					return;
				}
			}

			if(eregi("images", $category_icon)){
				$category_icon = THEME.$category_icon;
			}else{
				$category_icon = e_HTTP.$category_icon;
			}
		}

		$active_start = ($active_start ? $con -> convert_date($active_start, "long") : "Now");
		$active_end = ($active_end ?  " to ".$con -> convert_date($active_end, "long") : "");
		$info = "<div class=\"smalltext\"><br /><br /><b>Info:</b><br />";
		$info .= ($news_active ? "This news post is <b>inactive</b> (It will be not shown on front page). " : "This news post is <b>active</b> (it will be shown on front page). ");
		$info .= ($allow_comments ? "Comments are turned <b>off</b>. " : "Comments are turned <b>on</b>. ");
		$info .= "<br />Activation period: ".$active_start.$active_end."<br />";
		$info .= "Body length: ".strlen($news_body)."b. Extended length: ".strlen($news_entended)."b.<br /><br /></div>";

		if($NEWSSTYLE != ""){

			$news_category = "<a href='".e_SELF."?cat.".$category_id."'>".$category_name."</a>";
			$news_author = "<a href='user.php?id.".$a_id."'>".$a_name."</a>";
			$etext = " <a href=\"email.php?news.".$news_id."\"><img src=\"".e_BASE."themes/shared/generic/friend.gif\" style=\"border:0\" alt=\"email to someone\" /></a>";
			$ptext = " <a href=\"print.php?news.".$news_id."\"><img src=\"".e_BASE."themes/shared/generic/printer.gif\" style=\"border:0\" alt=\"printer friendly\" /></a>";

			if(ADMIN && getperms("H")){
				$adminoptions .= "<a href=\"".e_BASE.e_ADMIN."newspost.php?ne.".$news_id."\"><img src=\"".e_BASE."themes/shared/generic/newsedit.png\" alt=\"\" style=\"border:0\" /></a>
				<a href=\"".e_BASE.e_ADMIN."newspost.php?nd.".$news_id."\"><img src=\"".e_BASE."themes/shared/generic/newsdelete.png\" alt=\"\" style=\"border:0\" /></a>";
			}

			$search[0] = "/\{NEWSTITLE\}(.*?)/si";
			$replace[0] = $news_title;

			$search[1] = "/\{NEWSBODY\}(.*?)/si";
			if(eregi("extend", e_QUERY)){
				$replace[1] = $news_body."<br /><br />".$news_extended;
			}else{
				$replace[1] = $news_body;
			}

			$search[2] = "/\{NEWSICON\}(.*?)/si";
			$replace[2] = "<a href='".e_SELF."?cat.$category_id'><img style='".ICONSTYLE."'  src='$category_icon' alt='' /></a>";
			$search[3] = "/\{NEWSHEADER\}(.*?)/si";
			$replace[3] = $category_icon;
			$search[4] = "/\{NEWSCATEGORY\}(.*?)/si";
			$replace[4] = "<a href='".e_SELF."?cat.$category_id'>".$category_name."</a>";
			$search[5] = "/\{NEWSAUTHOR\}(.*?)/si";
			$replace[5] = $news_author;
			$search[6] = "/\{NEWSDATE\}(.*?)/si";
			$replace[6] = $datestamp;
			$search[7] = "/\{NEWSCOMMENTS\}(.*?)/si";
			if(!$allow_comments){
				$replace[7] = "<a href='comment.php?$news_id'>".COMMENTLINK.$comment_total."</a>";
			}else{
				$replace[7] = COMMENTOFFSTRING;
			}
			$search[8] = "/\{EMAILICON\}(.*?)/si";
			$replace[8] = $etext;
			$search[9] = "/\{PRINTICON\}(.*?)/si";
			$replace[9] = $ptext;
			$search[10] = "/\{NEWSID\}(.*?)/si";
			$replace[10] = $news_id;

			$search[11] = "/\{ADMINOPTIONS\}(.*?)/si";
			$replace[11] = $adminoptions;

			$search[12] = "/\{EXTENDED\}(.*?)/si";
			if($news_extended && !eregi("extend", e_QUERY)){
				if(defined("PRE_EXTENDEDSTRING")){ $es1 = PRE_EXTENDEDSTRING; }
				if(defined("POST_EXTENDEDSTRING")){ $es2 = POST_EXTENDEDSTRING; }
				$replace[12] = $es1."<a href='".e_BASE."news.php?extend.".$news_id."'>".EXTENDEDSTRING."</a>".$es2;
			}

			$search[13] = "/\{NEWSSOURCE\}(.*?)/si";
			if($news_source){
				if(defined("PRE_SOURCESTRING")){ $es1 = PRE_SOURCESTRING; }
				if(defined("POST_SOURCESTRING")){ $es2 = POST_SOURCESTRING; }
				$replace[13] = $es1.SOURCESTRING.$news_source.$es2;
			}

			$search[14] = "/\{NEWSURL\}(.*?)/si";
			if($news_url){
				if(defined("PRE_URLSTRING")){ $es1 = PRE_URLSTRING; }
				if(defined("POST_URLSTRING")){ $es2 = POST_URLSTRING; }
				$replace[14] = $es1.URLSTRING.$news_url.$es2;
			}

			$text = preg_replace($search, $replace, $NEWSSTYLE);

			echo $text;
			if($modex == "preview"){ echo $info; }

			return TRUE;
		}

// ---------------- old newsstyle code, depracated but left for -5.3 themes
		$search = array("[administrator]", "[date and time]", "[count]", "[l]", "[/l]", "[nc]");
		if($allow_comments == 1){
			$replace = array("<a href=\"mailto:$a_email\">$a_name</a>", $datestamp, COMMENT_OFF_TEXT, "", "", "<a href=\"".e_SELF."?cat.".$category_id."\">".$category_name."</a>");
		}else{
			$replace = array("<a href=\"mailto:$a_email\">$a_name</a>", $datestamp, $comment_total, "<a href=\"comment.php?".$news_id."\">", "</a>", "<a href=\"".e_SELF."?cat.".$category_id."\">".$category_name."</a>");
		}
		$info_text = str_replace($search,$replace, INFO_TEXT);

		if(SHOW_EMAIL_PRINT == TRUE){
			if(eregi("admin", $_SERVER['PHP_SELF'])){
				$ptext = " <a href=\"email.php?".$news_id."\"><img src=\"../themes/shared/generic/friend.gif\" style=\"border:0\" alt=\"email to someone\" /></a> <a href=\"print.php?".$news_id."\"><img src=\"../themes/shared/generic/printer.gif\" style=\"border:0\" alt=\"printer friendly\" /></a>";
			}else{
				$ptext = " <a href=\"email.php?".$news_id."\"><img src=\"themes/shared/generic/friend.gif\" style=\"border:0\" alt=\"email to someone\" /></a> <a href=\"print.php?news.".$news_id."\"><img src=\"themes/shared/generic/printer.gif\" style=\"border:0\" alt=\"printer friendly\" /></a>";
			}
		}
		if(ICON_SHOW == TRUE && ICON_POSITION == "caption" && $category_icon != ""){
			$caption = "<table style=\"width:95%\"><tr><td style=\"width:50%\">";
		}
		if(TITLE_POSITION == "caption"){
			$caption .= "<div style=\"text-align:".TITLE_ALIGN."\">".TITLE_STYLE_START.$news_title.TITLE_STYLE_END."</div>";
		}
		if(INFO_POSITION == "caption"){
			$caption .= "<div style=\"text-align:".INFO_ALIGN."\">".$info_text." ".$ptext."</div>";
		}
		if(ICON_SHOW == TRUE && ICON_POSITION == "caption" && $category_icon != ""){
			$tmp = "<table style=\"width:95%\"><tr><td style=\"width:50%\">";
			if(ICON_ALIGN == "left"){
				$tmp = "<a href=\"".e_SELF."?cat.".$category_id."\"><img style=\"float: ".ICON_ALIGN."; border:0\"  src=\"".$category_icon."\" alt=\"\" /></a>";
				$caption = $tmp.$caption."</td></tr></table>";
			}else{
				$caption .= "</td><td style=\"text-align:right; width:50%\"><a href=\"".e_SELF."?cat.".$category_id."\"><img style=\"float: ".ICON_ALIGN."; border:0\"  src=\"".$category_icon."\" alt=\"\" /></a></td></tr></table>";
			}
		}
		if(INFO_POSITION == "belowcaption"){
			$text = "<div style=\"text-align:".INFO_ALIGN."\">".$info_text." ".$ptext."</div>";
		}else{
			unset($text);
		}
		if(ICON_SHOW == TRUE && ICON_POSITION == "body" && $category_icon != ""){
			$text .= "<a href=\"".e_SELF."?cat.".$category_id."\"><img style=\"float: ".ICON_ALIGN."; border:0\"  src=\"".$category_icon."\" alt=\"\" /></a>";
		}
		if(TITLE_POSITION == "body"){
			$text .= "<div style=\"text-align:".TITLE_ALIGN."\">".TITLE_STYLE_START.$news_title.TITLE_STYLE_END."</div><br />";
		}
		$text .= "<div style=\"text-align:".TEXT_ALIGN."\">".
		stripslashes($news_body);
		$text .= "</div>";
		if($modex == "preview" && $news_extended != ""){
			$text .= "<br />[Extended text]: ".$news_extended;
		}else if($news_extended != "" && $modex != "extend"){
			$text .= "<br /><a href=\"".e_SELF."?extend.".$news_id."\">".EXTENDED_STRING."</a>";
		}		
		if($modex == "extend"){
			$text .= "<br />".$news_extended;
		}
		if($news_url != ""){
			$text .= "<br />".URL_TEXT.$news_url."<br />";
		}
		if($news_source != ""){
			$text .= SOURCE_TEXT.$news_source."<br />";
		}

		if(INFO_POSITION == "body"){
			$text .= "<div style=\"text-align:".INFO_ALIGN."\">";
			$text .= $info_text." ".$ptext."</div>";
		}

		$ns = new table;
		$ns -> tablerender($caption, $text, $category_id);

		if($modex == "preview"){ echo $info; }
	}
// ------------ end old newsstyle code
}
?>