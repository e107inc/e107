<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_files/shortcode/batch/news_shortcodes.php,v $
|     $Revision: 1.41 $
|     $Date: 2009-09-28 21:00:00 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
include_once(e_HANDLER.'shortcode_handler.php');
$news_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);
if(!$GLOBALS['NEWS_CSSMODE']){ $GLOBALS['NEWS_CSSMODE'] = "news"; }
/*
SC_BEGIN NEWSTITLE
global $tp;
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
return $tp -> toHTML($news_item['news_title'], TRUE, 'TITLE');
SC_END

SC_BEGIN NEWSBODY
global $tp;
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
$news_body = $tp -> toHTML($news_item['news_body'], TRUE, 'BODY, fromadmin', $news_item['news_author']);
if($news_item['news_extended'] && (isset($_POST['preview']) || strpos(e_QUERY, 'extend') !== FALSE) && $parm != "noextend") 
{
    $news_extended = $tp -> toHTML($news_item['news_extended'], TRUE, 'BODY, fromadmin', $news_item['news_author']);
    $news_body .= "<br /><br />".$news_extended;
}
return $news_body;
SC_END

SC_BEGIN NEWSICON
global $tp, $news_shortcodes;
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
$category_icon = $tp -> parseTemplate('{NEWSHEADER}', FALSE, $news_shortcodes);
if (!$category_icon) return '';
return "<a class='".$GLOBALS['NEWS_CSSMODE']."_icon' href='".e_HTTP."news.php?cat.".$news_item['news_category']."'><img style='".$param['caticon']."'  src='".$category_icon."' alt='' /></a>";
SC_END

SC_BEGIN NEWSHEADER
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
$category_icon = str_replace("../", "", trim($news_item['category_icon']));
if (!$category_icon) return '';
if ($category_icon && strstr("images", $category_icon)) {
	return THEME_ABS.$category_icon;
} else {
	return e_IMAGE_ABS."icons/".$category_icon;
}
SC_END

SC_BEGIN NEWSCATEGORY
global $tp;
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
$category_name = $tp -> toHTML($news_item['category_name'],FALSE,"defs");
return "<a class='".$GLOBALS['NEWS_CSSMODE']."_category' style='".(isset($param['catlink']) ? $param['catlink'] : "#")."' href='".e_HTTP."news.php?cat.".$news_item['news_category']."'>".$category_name."</a>";
SC_END

SC_BEGIN NEWSAUTHOR
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
if($news_item['user_id'])
{
	if($parm == 'nolink')
	{
		return $news_item['user_name'];
	}
	else
	{
		return "<a class='".$GLOBALS['NEWS_CSSMODE']."_author' href='".e_HTTP."user.php?id.".$news_item['user_id']."'>".$news_item['user_name']."{$parm}</a>";
	}
}
return "<a href='http://e107.org'>e107</a>";
SC_END

SC_BEGIN NEWSDATE
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
$con = new convert;
if($parm == "")
{
	return  $con -> convert_date($news_item['news_datestamp'], 'long');
}
switch($parm)
{
	case 'long':
		return  $con -> convert_date($news_item['news_datestamp'], 'long');
		break;
	case 'short':
		return  $con -> convert_date($news_item['news_datestamp'], 'short');
		break;
	case 'forum':
		return  $con -> convert_date($news_item['news_datestamp'], 'forum');
		break;
	default :
		return date($parm, $news_item['news_datestamp']);
		break;
}
SC_END

SC_BEGIN NEWSCOMMENTS
global $pref, $sql;
if($pref['comments_disabled'] == 1)
{
	return;
}
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');

if (varsettrue($pref['multilanguage']))
{	// Can have multilanguage news table, monlingual comment table. If the comment table is multilingual, it'll only count entries in the current language
	$news_item['news_comment_total'] = $sql->db_Select("comments", "*", "comment_item_id='".$news_item['news_id']."' AND comment_type='0' ");
}

if ($pref['comments_icon'] && $news_item['news_comment_total'])
{
	$sql->db_Select('comments', 'comment_datestamp', "comment_item_id='".intval($news_item['news_id'])."' AND comment_type='0' ORDER BY comment_datestamp DESC LIMIT 0,1");
	list($comments['comment_datestamp']) = $sql->db_Fetch();
	$latest_comment = $comments['comment_datestamp'];
	if ($latest_comment > USERLV )
	{
		$NEWIMAGE = $param['image_new_small'];
	}
	else
	{
		$NEWIMAGE = $param['image_nonew_small'];
	}
}
else
{
	$NEWIMAGE = $param['image_nonew_small'];
}
return ($news_item['news_allow_comments'] ? $param['commentoffstring'] 
:
 ''.($pref['comments_icon'] ? $NEWIMAGE : '')." <a href='".e_HTTP."comment.php?comment.news.".$news_item['news_id']."'>".$param['commentlink'].$news_item['news_comment_total'].'</a>');
SC_END

SC_BEGIN NEWSCOMMENTLINK
global $pref, $sql;
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
return ($news_item['news_allow_comments'] ? $param['commentoffstring'] : " <a href='".e_HTTP."comment.php?comment.news.".$news_item['news_id']."'>".$param['commentlink']."</a>");
SC_END

SC_BEGIN NEWSCOMMENTCOUNT
global $pref, $sql;
$news_item = getcachedvars('current_news_item');
return $news_item['news_comment_total'];
SC_END


SC_BEGIN EMAILICON
if (!check_class(varset($pref['email_item_class'],e_UC_MEMBER)))
{
	return '';
}
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
require_once(e_HANDLER.'emailprint_class.php');
return emailprint::render_emailprint('news', $news_item['news_id'], 1);
SC_END

SC_BEGIN PRINTICON
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
require_once(e_HANDLER.'emailprint_class.php');
return emailprint::render_emailprint('news', $news_item['news_id'], 2);
SC_END

SC_BEGIN PDFICON
global $tp, $pref;
if (!$pref['plug_installed']['pdf']) return '';
$news_item = getcachedvars('current_news_item');
return $tp -> parseTemplate("{PDF=".LAN_NEWS_24."^news.".$news_item['news_id']."}");
SC_END

SC_BEGIN NEWSID
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
return $news_item['news_id'];
SC_END

SC_BEGIN ADMINOPTIONS
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
if (ADMIN && getperms("H")) {
	$adop_icon = (file_exists(THEME."images/newsedit.png") ? THEME_ABS."images/newsedit.png" : e_IMAGE_ABS."generic/".IMODE."/newsedit.png");
	return " <a href='".e_ADMIN_ABS."newspost.php?create.edit.".$news_item['news_id']."'><img src='".$adop_icon."' alt='' style='border:0' /></a>\n";
} else {
	return '';
}
SC_END

SC_BEGIN EXTENDED
global $tp;
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
if ($news_item['news_extended'] && (strpos(e_QUERY, 'extend') === FALSE || $parm == "force")) 
{
	if (defined("PRE_EXTENDEDSTRING")) 
	{
		$es1 = PRE_EXTENDEDSTRING;
	}
	if (defined("POST_EXTENDEDSTRING")) 
	{
		$es2 = POST_EXTENDEDSTRING;
	}
	if (isset($_POST['preview'])) 
	{
		return $es1.EXTENDEDSTRING.$es2."<br />".$tp->toHTML($news_item['news_extended'], TRUE, 'BODY, fromadmin', $news_item['news_author']);
	} 
	else 
	{
		return $es1."<a class='".$GLOBALS['NEWS_CSSMODE']."_extendstring' href='".e_HTTP."news.php?extend.".$news_item['news_id']."'>".EXTENDEDSTRING."</a>".$es2;
	}
} 
else 
{
	return "";
}
SC_END

SC_BEGIN CAPTIONCLASS
global $tp;
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
$news_title = $tp -> toHTML($news_item['news_title'], TRUE,'no_hook,emotes_off, no_make_clickable');
return "<div class='category".$news_item['news_category']."'>".($news_item['news_render_type'] == 1 ? "<a href='".e_HTTP."comment.php?comment.news.".$news_item['news_id']."'>".$news_title."</a>" : $news_title)."</div>";
SC_END

SC_BEGIN ADMINCAPTION
global $tp;
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
$news_title = $tp -> toHTML($news_item['news_title'], TRUE,'no_hook,emotes_off, no_make_clickable');
return "<div class='".(defined(ADMINNAME) ? ADMINNAME : "null")."'>".($news_item['news_render_type'] == 1 ? "<a href='".e_HTTP."comment.php?comment.news.".$news_item['news_id']."'>".$news_title."</a>" : $news_title)."</div>";
SC_END

SC_BEGIN ADMINBODY
global $tp, $news_shortcodes;
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
$news_body = $tp -> parseTemplate('{NEWSBODY}', FALSE, $news_shortcodes);
return "<div class='".(defined(ADMINNAME) ? ADMINNAME : "null")."'>".$news_body."</div>";
SC_END

SC_BEGIN NEWSSUMMARY
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
return ($news_item['news_summary']) ? $news_item['news_summary']."<br />" : "";
SC_END

SC_BEGIN NEWSTHUMBNAIL
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
return (isset($news_item['news_thumbnail']) && $news_item['news_thumbnail']) ? "<a href='".e_HTTP."news.php?item.".$news_item['news_id'].".".$news_item['news_category']."'><img class='".$GLOBALS['NEWS_CSSMODE']."_image' src='".e_IMAGE_ABS."newspost_images/".$news_item['news_thumbnail']."' alt='' style='".$param['thumbnail']."' /></a>" : "";
SC_END

SC_BEGIN NEWSIMAGE
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
return (isset($news_item['news_thumbnail']) && $news_item['news_thumbnail']) ? "<a href='".e_HTTP."news.php?item.".$news_item['news_id'].".".$news_item['news_category']."'><img class='".$GLOBALS['NEWS_CSSMODE']."_image' src='".e_IMAGE_ABS."newspost_images/".$news_item['news_thumbnail']."' alt='' style='".$param['thumbnail']."' /></a>" : "";
SC_END

SC_BEGIN STICKY_ICON
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
return $news_item['news_sticky'] ? $param['image_sticky'] : "";
SC_END

SC_BEGIN NEWSTITLELINK
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
$mode = ($parm == "extend") ? "extend" : "item";
return "<a class='".$GLOBALS['NEWS_CSSMODE']."_titlelink' style='".(isset($param['itemlink']) ? $param['itemlink'] : "null")."' href='".e_HTTP."news.php?".$mode.".".$news_item['news_id'].".".$news_item['news_category']."' title=\"".$news_item['news_title']."\" >".$news_item['news_title']."</a>";
SC_END

SC_BEGIN NEWSCATICON
global $tp, $news_shortcodes;
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
$category_icon = $tp -> parseTemplate('{NEWSHEADER}', FALSE, $news_shortcodes);
if (!$category_icon) return '';
if($param['caticon'] == ""){$param['caticon'] = "border:0px";}
return "<a class='".$GLOBALS['NEWS_CSSMODE']."_caticon' href='".e_HTTP."news.php?cat.".$news_item['news_category']."'><img style='".$param['caticon']."' src='".$category_icon."' alt='' /></a>";
SC_END

SC_BEGIN TRACKBACK
global $pref;
if(!varsettrue($pref['trackbackEnabled'])) return '';
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
return ($param['trackbackbeforestring'] ? $param['trackbackbeforestring'] : "")."<a href='".e_HTTP."comment.php?comment.news.".$news_item['news_id']."#track'>".$param['trackbackstring'].$news_item['tb_count']."</a>".($param['trackbackafterstring'] ? $param['trackbackafterstring'] : "");
SC_END

SC_BEGIN NEWSINFO
global $ns;
$news_item = getcachedvars('current_news_item');
$param = getcachedvars('current_news_param');
$con = new convert;
$news_item['news_start'] = (isset($news_item['news_start']) && $news_item['news_start'] ? str_replace(" - 00:00:00", "", $con -> convert_date($news_item['news_start'], "long")) : LAN_NEWS_19);
$news_item['news_end'] = (isset($news_item['news_end']) && $news_item['news_end'] ? " to ".str_replace(" - 00:00:00", "", $con -> convert_date($news_item['news_end'], "long")) : "");
$info = $news_item['news_render_type'] == 1 ? LAN_NEWS_9 : "";
$info .= $news_item['news_class'] == 255 ? LAN_NEWS_10 : LAN_NEWS_11;
$info .= $news_item['news_sticky'] ? "<br />".LAN_NEWS_31 : "";
$info .= "<br />".($news_item['news_allow_comments'] ? LAN_NEWS_13 : LAN_NEWS_12);
$info .= LAN_NEWS_14.$news_item['news_start'].$news_item['news_end']."<br />";
$info .= LAN_NEWS_15.strlen($news_item['news_body']).LAN_NEWS_16.strlen($news_item['news_extended']).LAN_NEWS_17."<br /><br />";
return $ns -> tablerender(LAN_NEWS_18, $info);
SC_END
*/
?>