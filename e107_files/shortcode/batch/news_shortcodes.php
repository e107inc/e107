<?php
include_once(e_HANDLER.'shortcode_handler.php');
$news_shortcodes = e_shortcode::parse_scbatch(__FILE__);
/*
SC_BEGIN NEWSTITLE
global $ix, $tp;
return $tp -> toHTML($ix -> news_item['news_title'], TRUE);
SC_END

SC_BEGIN NEWSBODY
global $ix, $tp;
$news_body = $tp -> toHTML($ix -> news_item['news_body'], TRUE);
if($ix -> news_item['news_extended'] && (isset($_POST['preview']) || strpos(e_QUERY, 'extend') !== FALSE)) {
    $news_extended = $tp -> toHTML($ix -> news_item['news_extended'], TRUE);
    $news_body .= "<br /><br />".$news_extended;
}
return $news_body;
SC_END

SC_BEGIN NEWSICON
global $ix, $tp;
$category_icon = $tp -> parseTemplate('{NEWSHEADER}', FALSE, $news_shortcodes);
return "<a href='".e_BASE."news.php?cat.".$ix -> news_item['news_category']."'><img style='".$ix -> param['caticon']."'  src='".$category_icon."' alt='' /></a>";
SC_END
	
SC_BEGIN NEWSHEADER
global $ix;
$category_icon = str_replace("../", "", $ix -> news_item['category_icon']);
if ($category_icon && strstr("images", $category_icon)) {
	return THEME.$category_icon;
} else {
	return e_IMAGE."icons/".$category_icon;
}
SC_END
	
SC_BEGIN NEWSCATEGORY
global $ix, $tp;
$category_name = $tp -> toHTML($ix -> news_item['category_name']);
return "<a style='".(isset($ix -> param['catlink']) ? $ix -> param['catlink'] : "#")."' href='".e_BASE."news.php?cat.".$ix -> news_item['news_category']."'>".$category_name."</a>";
SC_END
	
SC_BEGIN NEWSAUTHOR
global $ix;
return  "<a href='".e_BASE."user.php?id.".$ix -> news_item['user_id']."'>".$ix -> news_item['user_name']."</a>";
SC_END
	
SC_BEGIN NEWSDATE
global $ix;
$con = new convert;
return  $con -> convert_date($ix -> news_item['news_datestamp'], 'long');
SC_END
	
SC_BEGIN NEWSCOMMENTS
global $ix, $pref, $sql;
if ($ix -> news_item['news_comment_total']) {
	$sql->db_Select("comments", "comment_datestamp", "comment_item_id='".$ix -> news_item['news_id']."' AND comment_type='0' ORDER BY comment_datestamp DESC LIMIT 0,1");
	list($comments['comment_datestamp']) = $sql->db_Fetch();
	$latest_comment = $comments['comment_datestamp'];
	if ($latest_comment > USERLV ) {
		$NEWIMAGE = $ix -> param['image_new_small'];
	} else {
		$NEWIMAGE = $ix -> param['image_nonew_small'];
	}
} else {
	$NEWIMAGE = $ix -> param['image_nonew_small'];
}
return ($ix -> news_item['news_allow_comments'] ? $ix -> param['commentoffstring'] : "".($pref['comments_icon'] ? $NEWIMAGE : "")." <a href='".e_BASE."comment.php?comment.news.".$ix -> news_item['news_id']."'>".$ix -> param['commentlink'].$ix -> news_item['news_comment_total']."</a>");
SC_END
	
SC_BEGIN EMAILICON
global $ix;
require_once(e_HANDLER.'emailprint_class.php');
return emailprint::render_emailprint('news', $ix -> news_item['news_id'], 1);
SC_END
	
SC_BEGIN PRINTICON
global $ix;
require_once(e_HANDLER.'emailprint_class.php');
return emailprint::render_emailprint('news', $ix -> news_item['news_id'], 2);
SC_END

SC_BEGIN NEWSID
global $ix;
return $ix -> news_item['news_id'];
SC_END
	
SC_BEGIN ADMINOPTIONS
global $ix;
if (ADMIN && getperms("H")) {
	return "<a href='".e_BASE.e_ADMIN."newspost.php?create.edit.".$ix -> news_item['news_id']."'><img src='".e_IMAGE."generic/".IMODE."/newsedit.png' alt='' style='border:0' /></a>\n";
} else {
	return '';
}
SC_END
	
SC_BEGIN EXTENDED
global $ix, $preview;
if ($ix -> news_item['news_extended'] && strpos(e_QUERY, 'extend') === FALSE) {
	if (defined("PRE_EXTENDEDSTRING")) {
		$es1 = PRE_EXTENDEDSTRING;
	}
	if (defined("POST_EXTENDEDSTRING")) {
		$es2 = POST_EXTENDEDSTRING;
	}
	if (isset($_POST['preview'])) {
		return $es1.EXTENDEDSTRING.$es2."<br />".$ix -> news_item['news_extended'];
	} else {
		return $es1."<a href='".e_BASE."news.php?extend.".$ix -> news_item['news_id']."'>".EXTENDEDSTRING."</a>".$es2;
	}
} else {
	return "";
}
SC_END
	
SC_BEGIN CAPTIONCLASS
global $ix, $tp;
$news_title = $tp -> toHTML($ix -> news_item['news_title'], TRUE);
return "<div class='category".$ix -> news_item['news_category']."'>".($ix -> news_item['news_render_type'] == 1 ? "<a href='".e_BASE."comment.php?comment.news.".$ix -> news_item['news_id']."'>".$news_title."</a>" : $news_title)."</div>";
SC_END
	
SC_BEGIN ADMINCAPTION
global $ix, $tp;
$news_title = $tp -> toHTML($ix -> news_item['news_title'], TRUE);
return "<div class='".(defined(ADMINNAME) ? ADMINNAME : "null")."'>".($ix -> news_item['news_render_type'] == 1 ? "<a href='".e_BASE."comment.php?comment.news.".$ix -> news_item['news_id']."'>".$news_title."</a>" : $news_title)."</div>";
SC_END
	
SC_BEGIN ADMINBODY
global $ix, $tp;
$news_body = $tp -> parseTemplate('{NEWSBODY}', FALSE, $news_shortcodes);
return "<div class='".(defined(ADMINNAME) ? ADMINNAME : "null")."'>".$news_body."</div>";
SC_END
	
SC_BEGIN NEWSSUMMARY
global $ix;
return ($ix -> news_item['news_summary']) ? $ix -> news_item['news_summary']."<br />" : "";
SC_END
	
SC_BEGIN NEWSTHUMBNAIL
global $ix;
return (isset($ix -> news_item['news_thumbnail'])) ? "<a href='".e_BASE."news.php?item.".$ix -> news_item['news_id'].$ix -> news_item['news_category']."'><img src='".e_IMAGE."newspost_images/".$ix -> news_item['news_thumbnail']."' alt='' style='".$ix -> param['thumbnail']."' /></a>" : "";
SC_END
	
SC_BEGIN STICKY_ICON
global $ix;
return $ix -> news_item['news_sticky'] ? $ix -> param['image_sticky'] : "";
SC_END
	
SC_BEGIN NEWSTITLELINK
global $ix;
return "<a style='".(isset($ix -> param['itemlink']) ? $ix -> param['itemlink'] : "null")."' href='".e_BASE."news.php?item.".$ix -> news_item['news_id'].".".$ix -> news_item['news_category']."'>".$ix -> news_item['news_title']."</a>";
SC_END
	
SC_BEGIN NEWSCATICON
global $ix, $tp;
$category_icon = $tp -> parseTemplate('{NEWSHEADER}', FALSE, $news_shortcodes);
if($ix -> param['caticon'] == ""){$ix -> param['caticon'] = "border:0px";}
return "<a href='".e_BASE."news.php?cat.".$ix -> news_item['news_category']."'><img style='".$ix -> param['caticon']."' src='".$category_icon."' alt='' /></a>";
SC_END
	
SC_BEGIN TRACKBACK
global $pref, $ix;
if(isset($pref['trackbackEnabled'])) {
	return ($ix -> param['trackbackbeforestring'] ? $ix -> param['trackbackbeforestring'] : "")."<a href='".e_BASE."comment.php?comment.news.".$ix -> news_item['news_id']."#track'>".$ix -> param['trackbackstring'].$ix -> news_item['tb_count']."</a>".($ix -> param['trackbackafterstring'] ? $ix -> param['trackbackafterstring'] : "");
} else {
	return "";
}
SC_END
*/
?>