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

	
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function submit_item($news){
		
		global $sql, $aj;
		extract($news);
		if($news_id){
			$news_title = $aj -> formtpa($news_title);
			$news_body = $aj -> formtpa($data);
			$news_extended = $aj -> formtpa($news_extended);
			if($sql -> db_Update("news", "news_title='$news_title', news_body='$news_body', news_extended='$news_extended', news_category='$cat_id', news_allow_comments='$news_allow_comments', news_start='$active_start', news_end='$active_end', news_class='$news_class', news_render_type='$news_rendertype' WHERE news_id='$news_id' ")){
				$message = "News updated in database.";
				$sql -> db_Delete("cache", "cache_url='news.php' ");
			}else{
				$message = "<b>Error!</b> Was unable to update news item into database!</b>";
			}
		}else{
			$news_title = $aj -> formtpa($news_title);
			$news_body = $aj -> formtpa($data);
			$news_extended = $aj -> formtpa($news_extended);
			if($sql -> db_Insert("news", "0, '$news_title', '$news_body', '$news_extended', ".time().", ".USERID.", $cat_id, $news_allow_comments, $active_start, $active_end, '$news_class', '$news_rendertype' ")){
				$message = "News entered into database.";
				$sql -> db_Delete("cache", "cache_url='news.php' ");
			}else{
				$message = "<b>Error!</b> Was unable to enter news item into database!</b>";
			}
		}
		return $message;
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function render_newsitem($news, $mode="default"){

		global $NEWSSTYLE, $NEWSLISTSTYLE, $aj;
		if(!is_object($aj)) $aj = new textparse;
		extract($news);

		if(!$NEWSLISTSTYLE){
			$NEWSLISTSTYLE = "
<img src='".THEME."images/bullet2.gif' alt='bullet' /> 
<b>
{NEWSTITLE}
</b>
<div class='smalltext'>
{NEWSAUTHOR}
on
{NEWSDATE}
{NEWSCOMMENTS}
</div>
<hr />
";
	}

		$news_title = $aj -> tpa($news_title, "off", "admin");
		$news_body = $aj -> tpa($data, "off", "admin");
		$news_extended = trim(chop($aj -> tpa($news_extended, "off", "admin")));

		if(!$comment_total) $comment_total = "0";
		$con = new convert;
		$datestamp = $con -> convert_date($news_datestamp, "long");

		$titleonly = (substr($news_body, 0, 6) == "&nbsp;" && $NEWSLISTSTYLE ? TRUE : FALSE);

		if($news_title == "Welcome to e107"){
			$admin_name = "e107";
			$admin_email = "e107@jalist.com";
			$category_name = "e107 welcome message";
			$category_id = 0;
			$category_icon = (strstr(SITEBUTTON, "http") ? SITEBUTTON : e_IMAGE.SITEBUTTON);
		}else{
			$category_icon = str_replace("../", "", $category_icon);
			if(strstr("images", $category_icon)){
				$category_icon = THEME.$category_icon;
			}else{
				$category_icon = e_IMAGE."newsicons/".$category_icon;
			}
		}

		$active_start = ($active_start ? str_replace(" - 00:00:00", "", $con -> convert_date($active_start, "long")) : "Now");
		$active_end = ($active_end ?  " to ".str_replace(" - 00:00:00", "", $con -> convert_date($active_end, "long")) : "");
		$info = "<div class='smalltext'><br /><br /><b>Info:</b><br />";
		$info .= ($titleonly ? "Title only is set - <b>only the news title will be shown</b><br />" : "");
		$info .= ($news_class==255 ? "This news post is <b>inactive</b> (It will be not shown on front page). " : "This news post is <b>active</b> (it will be shown on front page). ");
		$info .= ($news_allow_comments ? "Comments are turned <b>off</b>. " : "Comments are turned <b>on</b>. ");
		$info .= "<br />Activation period: ".$active_start.$active_end."<br />";
		$info .= "Body length: ".strlen($news_body)."b. Extended length: ".strlen($news_extended)."b.<br /><br /></div>";


		$news_category = "<a href='".e_BASE."news.php?cat.".$category_id."'>".$category_name."</a>";
		$news_author = "<a href='".e_BASE."user.php?id.".$admin_id."'>".$admin_name."</a>";
		$etext = " <a href='".e_BASE."email.php?news.".$news_id."'><img src='".e_IMAGE."generic/friend.gif' style='border:0' alt='email to someone' title='email to someone'/></a>";
		$ptext = " <a href='".e_BASE."print.php?news.".$news_id."'><img src='".e_IMAGE."generic/printer.gif' style='border:0' alt='printer friendly' title='printer friendly'/></a>";

		if(ADMIN && getperms("H")){
			$adminoptions .= "<a href='".e_BASE.e_ADMIN."newspost.php?create.edit.".$news_id."'><img src='".e_IMAGE."generic/newsedit.png' alt='' style='border:0' /></a>\n";
		}

		$search[0] = "/\{NEWSTITLE\}(.*?)/si";
		$replace[0] = ($news_rendertype == 1 ? "<a href='".e_BASE."news.php?item.$news_id'>".$news_title."</a>" : $news_title);

		$search[13] = "/\{CAPTIONCLASS\}(.*?)/si";
		$replace[13] = "<div class='category".$category_id."'>".($titleonly ? "&nbsp;<a href='".e_BASE."comment.php?$news_id'>".$news_title."</a>" : "&nbsp;".$news_title)."</div>";

		$search[14] = "/\{ADMINCAPTION\}(.*?)/si";
		$replace[14] = "<div class='$admin_name'>".($titleonly ? "&nbsp;<a href='".e_BASE."comment.php?$news_id'>".$news_title."</a>" : "&nbsp;".$news_title)."</div>";

		$search[15] = "/\{ADMINBODY\}(.*?)/si";
		$replace[15] = "<div class='$admin_name'>".(strstr(e_QUERY, "extend") ? $news_body."<br /><br />".$news_extended : $news_body)."</div>";

		$search[1] = "/\{NEWSBODY\}(.*?)/si";
		$replace[1] = (strstr(e_QUERY, "extend") ? $news_body."<br /><br />".$news_extended : $news_body);
		$search[2] = "/\{NEWSICON\}(.*?)/si";
		$replace[2] = "<a href='".e_BASE."news.php?cat.$category_id'><img style='".ICONSTYLE."'  src='$category_icon' alt='' /></a>";
		$search[3] = "/\{NEWSHEADER\}(.*?)/si";
		$replace[3] = $category_icon;
		$search[4] = "/\{NEWSCATEGORY\}(.*?)/si";
		$replace[4] = "<a href='".e_BASE."news.php?cat.$category_id'>".$category_name."</a>";
		$search[5] = "/\{NEWSAUTHOR\}(.*?)/si";
		$replace[5] = $news_author;
		$search[6] = "/\{NEWSDATE\}(.*?)/si";
		$replace[6] = $datestamp;
		$search[7] = "/\{NEWSCOMMENTS\}(.*?)/si";
		$replace[7] = ($news_allow_comments ? COMMENTOFFSTRING : "<a href='".e_BASE."comment.php?$news_id'>".COMMENTLINK.$comment_total."</a>");
		$search[8] = "/\{EMAILICON\}(.*?)/si";
		$replace[8] = $etext;
		$search[9] = "/\{PRINTICON\}(.*?)/si";
		$replace[9] = $ptext;
		$search[10] = "/\{NEWSID\}(.*?)/si";
		$replace[10] = $news_id;

		$search[11] = "/\{ADMINOPTIONS\}(.*?)/si";
		$replace[11] = $adminoptions;

		$search[12] = "/\{EXTENDED\}(.*?)/si";

		if($preview == "Preview" && $news_extended && !strstr(e_QUERY, "extend")){
			if(defined("PRE_EXTENDEDSTRING")){ $es1 = PRE_EXTENDEDSTRING; }
			if(defined("POST_EXTENDEDSTRING")){ $es2 = POST_EXTENDEDSTRING; }
			$replace[12] = $es1.EXTENDEDSTRING.$es2."<br />".$news_extended;
		}else if($news_extended && !strstr(e_QUERY, "extend")){
			if(defined("PRE_EXTENDEDSTRING")){ $es1 = PRE_EXTENDEDSTRING; }
			if(defined("POST_EXTENDEDSTRING")){ $es2 = POST_EXTENDEDSTRING; }
			$replace[12] = $es1."<a href='".e_BASE."news.php?extend.".$news_id."'>".EXTENDEDSTRING."</a>".$es2;
		}
		
		$text = preg_replace($search, $replace, ($news_rendertype == 1 && strstr(e_SELF, "news.php") ? $NEWSLISTSTYLE : $NEWSSTYLE));
		echo $text;
		if($preview == "Preview"){ echo $info; }

		return TRUE;
	}
}
?>