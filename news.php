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
|     $Source: /cvs_backup/e107_0.7/news.php,v $
|     $Revision: 1.8 $
|     $Date: 2004-11-26 20:33:58 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(e_HANDLER."news_class.php");
require_once(HEADERF);

if($NEWSHEADER)
{
        require_once(FOOTERF);
        exit;
}
$cacheString = 'news.php_'.e_QUERY;
// ML
if(e_MLANG == 1)
{
        require_once(e_HANDLER."multilang/ml_panel.php");
        e107ml_panel("news");
}
// END ML

if(Empty($pref['newsposts']) ? define("ITEMVIEW", 15) : define("ITEMVIEW", $pref['newsposts']));
if(file_exists("install.php") && ADMIN){ echo "<div class='installe' style='text-align:center'><b>*** ".LAN_NEWS_3." ***</b><br />".LAN_NEWS_4."</div><br /><br />"; }

if(!is_object($tp)){ $tp = new e_parse; }

if(e_QUERY)
{
        $tmp = explode(".", e_QUERY);
        $action = $tmp[0];
        $sub_action = $tmp[1];
        $id = $tmp[2];
}

$from = (!is_numeric($action) || !e_QUERY ? 0 : ($action ? $action : e_QUERY));
if($tmp[1]=='list')
{
        $action='list';
        $from=intval($tmp[0]);
        $sub_action=intval($tmp[2]);
}

$ix = new news;
if($action == "cat")
{
        checkNewsCache($cacheString);
        ob_start();
        $qs = explode(".", e_QUERY);
        $category = $qs[1];
        if($category != 0)
        {
                $gen = new convert;
                $sql2 = new db;
                // ML
                if(e_MLANG == 1)
                {
                        $ml -> e107_ml_Select("news_category", "*", "category_id='$category'");
                }
                else
                {
                        $sql -> db_Select("news_category", "*", "category_id='$category'");
                }
                // END ML
                list($category_id, $category_name, $category_icon) = $sql-> db_Fetch();
                $category_name = $tp -> toHTML($category_name);
                $category_icon = e_IMAGE."newsicons/".$category_icon;

                // ML
                if(e_MLANG == 1)
                {
                        $count = $ml -> e107_ml_Select("news", "*",  "news_category='$category' AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().") ORDER BY news_datestamp DESC");
                }
                else // END ML
                {
                        $count = $sql -> db_SELECT("news", "*",  "news_category='$category' AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().") ORDER BY news_datestamp DESC");
                }
                while($row = $sql-> db_Fetch())
                {
                        extract($row);
                        if(check_class($news_class))
                        {
                                $news_title = $tp -> toHTML($news_title);
                                if($news_title == ""){ $news_title = "Untitled"; }
                                $datestamp = $gen->convert_date($news_datestamp, "short");
                                $comment_total = $sql2 -> db_Count("comments", "(*)",  "WHERE comment_item_id='$news_id' AND comment_type='0' ");
                                $text .= "
                                <img src='".THEME."images/bullet2.gif' alt='bullet' /> <b>
                                <a href='news.php?item.".$news_id."'>".$news_title."</a></b>
                                <br />&nbsp;&nbsp;
                                <span class='smalltext'>
                                ".$datestamp.", ".LAN_99.": ".
                                ($news_allow_comments ? COMMENTOFFSTRING : $comment_total)."
                                </span>
                                <br />\n";
                        }
                        else
                        {
                                $count --;
                        }

                }
                $text = "<img src='$category_icon' alt='' /><br />".
                LAN_307.$count."
                <br /><br />".$text;
                $ns -> tablerender(LAN_82." '".$category_name."'", $text);
                setNewsCache($cacheString);
                require_once(FOOTERF);
                exit;
        }
}

if($action == "extend")
{
        checkNewsCache($cacheString);
        ob_start();
        $extend_id = (int)substr(e_QUERY, (strpos(e_QUERY, ".")+1));
        // ML
        if(e_MLANG == 1)
        {
                $ml -> e107_ml_Select("news", "*", "news_id='$extend_id' AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().")");
        }
        else
        {
                $sql -> db_Select("news", "*", "news_id='$extend_id' AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().")");
        }
        // END ML
        list($news['news_id'], $news['news_title'], $news['data'], $news['news_extended'], $news['news_datestamp'], $news['admin_id'], $news_category, $news['news_allow_comments'],  $news['news_start'], $news['news_end'], $news['news_class']) = $sql -> db_Fetch();
        if(!check_class($news['news_class'])){ header("location:".e_BASE."news.php"); }
        // ML
        if(e_MLANG == 1)
        {
                $ml -> e107_ml_Select("news_category", "*",  "category_id='$news_category' ");
        }
        else
        {
                $sql -> db_Select("news_category", "*",  "category_id='$news_category' ");
        }
        // END ML
        list($news['category_id'], $news['category_name'], $news['category_icon']) = $sql-> db_Fetch();
        $news['comment_total'] = $sql -> db_Count("comments", "(*)",  "WHERE comment_item_id='".$news['news_id']."' AND comment_type='0' ");
        $sql -> db_Select("user", "user_name", "user_id='".$news['admin_id']."' ");
        list($news['admin_name']) = $sql -> db_Fetch();
        $ix -> render_newsitem($news);
        setNewsCache($cacheString);
        require_once(FOOTERF);
        exit;
}

if($pref['nfp_display'] == 1)
{
        require_once(e_PLUGIN."newforumposts_main/newforumposts_main.php");
}

if(Empty($order)){ $order = "news_datestamp"; }

// ---> wmessage
if(!defined("WMFLAG"))
{
        $sql -> db_Select("wmessage");
        list($wm_guest, $guestmessage, $wm_active1) = $sql-> db_Fetch();
        list($wm_member, $membermessage, $wm_active2) = $sql-> db_Fetch();
        list($wm_admin, $adminmessage, $wm_active3) = $sql-> db_Fetch();
        if(ADMIN == TRUE && $wm_active3)
        {
                $adminmessage = $tp -> toHTML($adminmessage, TRUE);
                $ns -> tablerender("", "<div style='text-align:center'><b>Administrators</b><br />".$adminmessage."</div>", "wm");
        }
        else if(USER == TRUE && $wm_active2 && !ADMIN)
        {
                $membermessage = $tp -> toHTML($membermessage,TRUE);
                $ns -> tablerender("", "<div style='text-align:center'>".$membermessage."</div>", "wm");
        }
        else if(USER == FALSE && $wm_active1 && !ADMIN)
        {
                $guestmessage = $tp -> toHTML($guestmessage, TRUE);
                $ns -> tablerender("", "<div style='text-align:center'>".$guestmessage."</div>", "wm");
        }
}
// ---> wmessage end

if($action == "list")
{
        $sub_action=intval($sub_action);
        $news_total = $sql -> db_Count("news", "(*)", "WHERE news_category=$sub_action");
        $query = "news_class<255 AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().") AND news_render_type!=2 AND news_category=$sub_action ORDER BY ".$order." DESC LIMIT $from,".ITEMVIEW;
}
else if($action == "item")
{
        $sub_action=intval($sub_action);
        $news_total = $sql -> db_Count("news", "(*)", "WHERE news_class<255 AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().") AND news_render_type!=2" );
        $query = "news_id=$sub_action AND news_class<255 AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().")";
}
else if(strstr(e_QUERY, "month"))
{
        $tmp = explode(".", e_QUERY);
        $item = $tmp[1];
        $year = substr($item, 0, 4);
        $month = substr($item, 4);
        $startdate = mktime(0,0,0,$month,1,$year);
        $lastday = date("t", $startdate);
        $enddate = mktime(23,59,59,$month,$lastday,$year);
        $query = "news_datestamp > $startdate AND news_datestamp < $enddate AND news_class<255 AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().") ORDER BY ".$order." DESC";;
}
else if(strstr(e_QUERY, "day"))
{
        $tmp = explode(".", e_QUERY);
        $item = $tmp[1];
        $year = substr($item, 0, 4);
        $month = substr($item, 4, 2);
        $day = substr($item, 6, 2);
        $startdate = mktime(0,0,0,$month,$day,$year);
        $lastday = date("t", $startdate);
        $enddate = mktime(23,59,59,$month,$day,$year);
        $query = "news_datestamp > $startdate AND news_datestamp < $enddate AND news_class<255 AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().") ORDER BY ".$order." DESC";
}
else
{
        $news_total = $sql -> db_Count("news", "(*)", "WHERE news_class != '255' AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().") AND news_render_type!=2" );

        // #### changed for news archive ------------------------------------------------------------------------------
        $interval = $pref['newsposts']-$pref['newsposts_archive'];
        $from2 = $interval+$from;
        $ITEMVIEW2 = ITEMVIEW-$interval;
        $ITEMVIEW1 = $interval;


        // normal newsitems
        $query = "news_class != '255' AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().") AND news_render_type!=2 ORDER BY ".$order." DESC LIMIT $from,".$ITEMVIEW1;

        // news archive
        $query2 = "news_class != '255' AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().") AND news_render_type!=2 ORDER BY ".$order." DESC LIMIT $from2,".$ITEMVIEW2;
        // #### END ---------------------------------------------------------------------------------------------------
}

checkNewsCache($cacheString,TRUE,TRUE);

// #### normal newsitems, rendered via render_newsitem(), the $query is changed above (no other changes made) ---------
ob_start();
// ML
if(e_MLANG == 1)
{
        require_once(e_HANDLER."multilang/news1.php");

}// END ML
else if(!$sql -> db_Select("news", "*", $query))
{
        echo "<br /><br /><div style='text-align:center'><b>".(strstr(e_QUERY, "month") ? LAN_462 : LAN_83)."</b></div><br /><br />";
}
else
{
        $sql2 = new db;
        while(list($news['news_id'], $news['news_title'], $news['data'], $news['news_extended'], $news['news_datestamp'], $news['admin_id'], $news_category, $news['news_allow_comments'],  $news['news_start'], $news['news_end'], $news['news_class'], $news['news_rendertype']) = $sql -> db_Fetch())
        {
                if(check_class($news['news_class']))
                {
                        if($news['admin_id'] == 1 && $pref['siteadmin'])
                        {
                                $news['admin_name'] = $pref['siteadmin'];
                        }
                        else if(!$news['admin_name'] = getcachedvars($news['admin_id']))
                        {
                                $sql2 -> db_Select("user", "user_name", "user_id='".$news['admin_id']."' ");
                                list($news['admin_name']) = $sql2 -> db_Fetch();
                                cachevars($news['admin_id'], $news['admin_name']);
                        }
                        $news['category_id']=$news_category;

                        if(getcachedvars("news_cat_{$news_category}"))
                        {
                                $news['category_name'] = getcachedvars("news_cat_name_{$news_category}");
                                $news['category_icon'] = getcachedvars("news_cat_icon_{$news_category}");
                        }
                        else
                        {
                                // ML
                                if(e_MLANG == 1)
                                {
                                        $ml -> e107_ml_Select("news_category", "*",  "category_id='$news_category' ", "default", FALSE, "sql2");
                                }
                                else
                                {
                                        $sql2 -> db_Select("news_category", "*",  "category_id='$news_category' ");
                                }
                                // END ML
                                list($news['category_id'], $news['category_name'], $news['category_icon']) = $sql2-> db_Fetch();
                                cachevars("news_cat_$news_category",$news_category);
                                cachevars("news_cat_name_$news_category",$news['category_name']);
                                cachevars("news_cat_icon_$news_category",$news['category_icon']);
                        }
                        $news['comment_total'] = $sql2 -> db_Count("comments", "(*)",  "WHERE comment_item_id='".$news['news_id']."' AND comment_type='0' ");
                        if($action == "item"){ unset($news['news_rendertype']); }
                        $ix -> render_newsitem($news);
                }
                // To hide messages for restricted news and only display valid news, comment the following else statement
                else
                {
                        if($pref['subnews_hide_news']==1)
                        {
                                if($news['admin_id'] == 1 && $pref['siteadmin'])
                                {
                                        $news['admin_name'] = $pref['siteadmin'];
                                }
                                else if(!$news['admin_name'] = getcachedvars($news['admin_id']))
                                {
                                        $sql2 -> db_Select("user", "user_name", "user_id='".$news['admin_id']."' ");
                                        list($news['admin_name']) = $sql2 -> db_Fetch();
                                        cachevars($news['admin_id'], $news['admin_name']);
                                }
                                // ML
                                if(e_MLANG == 1)
                                {
                                        $ml -> e107_ml_Select("news_category", "*",  "category_id='$news_category' ", "default", FALSE, "sql2");
                                }
                                else
                                {
                                        $sql2 -> db_Select("news_category", "*",  "category_id='$news_category' ");
                                }
                                // END ML
                                list($news['category_id'], $news['category_name'], $news['category_icon']) = $sql2-> db_Fetch();
                                $ix -> render_newsitem($news,"","userclass");
                        }
                }
        }
}
// ##### --------------------------------------------------------------------------------------------------------------

// #### new: news archive ---------------------------------------------------------------------------------------------
if($action != "item" && $action != 'list') // do not show the newsarchive on the news.php?item.X page (but only on the news mainpage)
{
        $sql2b = new db;
        require_once(e_HANDLER."multilang/mysql_queries.php");
        if(!is_object($ml)){$ml = new e107_ml;}
        $tmp_ok = 1;
        if(e_MLANG == 1 && !$ml -> e107_ml_Select("news", "*", $query2, "default", FALSE, "sql2b"))
        {
                $tmp_ok = 0;
        }
        else if(!$sql2b -> db_Select("news", "*", $query2))
        {
                $tmp_ok = 0;
        }

        if($tmp_ok == 1)
        {
                while(list($news2['news_id'], $news2['news_title'], $news2['data'], $news2['news_extended'], $news2['news_datestamp'], $news2['admin_id'], $news2_category, $news2['news_allow_comments'],  $news2['news_start'], $news2['news_end'], $news2['news_class'], $news2['news_rendertype']) = $sql2b -> db_Fetch()){

                        if(check_class($news2['news_class']))
                        {
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

                                if(getcachedvars("news_cat_{$news2_category}"))
                                {
                                        $news2['category_name'] = getcachedvars("news_cat_name_{$news_category}");
                                        $news2['category_icon'] = getcachedvars("news_cat_icon_{$news_category}");
                                }
                                else
                                {
                                        // ML
                                        if(e_MLANG == 1)
                                        {
                                                $ml -> e107_ml_Select("news_category", "*",  "category_id='$news2_category' ", "default", false, "sql2");
                                        }
                                        else
                                        { // END ML
                                                $sql2 -> db_Select("news_category", "*",  "category_id='$news2_category' ");
                                        }

                                        list($news['category_id'], $news['category_name'], $news['category_icon']) = $sql2-> db_Fetch();
                                        cachevars("news_cat_$news_category",$news_category);
                                        cachevars("news_cat_name_$news_category",$news['category_name']);
                                        cachevars("news_cat_icon_$news_category",$news['category_icon']);
                                }
                                $news2['comment_total'] = $sql2 -> db_Count("comments", "(*)",  "WHERE comment_item_id='".$news['news_id']."' AND comment_type='0' ");
                                //                        if(!is_object($sql2)){$sql2 = new db;}
                                //                        $sql2 -> db_Select("news_category", "*",  "category_id='$news2_category' ");
                                //                        list($news2['category_id'], $news2['category_name'], $news2['category_icon']) = $sql2-> db_Fetch();

                                $textnewsarchive .= "
                                <div>
                                <table style='width:98%;'>
                                <tr>
                                <td>
                                <div><img src='".THEME."images/bullet2.gif' style='border:0px' alt='' /> <b><a href='news.php?item.".$news2['news_id']."'>".$news2['news_title']."</a></b> <span class='smalltext' ><i>(".$news2['news_datestamp'].") (".$news2['category_name'].")</i></span></div>
                                </td>
                                </tr>
                                </table>
                                </div>";
                        }
                }
                $ns -> tablerender($pref['newsposts_archive_title'], $textnewsarchive);
        }
}
// #### END -----------------------------------------------------------------------------------------------------------


require_once(e_HANDLER."np_class.php");
if($action != "item"){ $ix = new nextprev("news.php", $from, ITEMVIEW, $news_total, LAN_84, ($action == "list" ? $action.".".$sub_action : "")); }

if($pref['nfp_display'] == 2)
{
        require_once(e_PLUGIN."newforumposts_main/newforumposts_main.php");
}


// ==CNN Style Categories. ============================================================
$nbr_cols = $pref['nbr_cols'];
$nbr_lst = 5;
$line_clr = "black";

if($pref['news_cats']=='1')
{
        $sql2 = new db;
        // ML
        if(e_MLANG == 1)
        {
                $ml -> e107_ml_Select("news_category","*",  "category_id!='' ORDER BY category_name ASC", "default", false, "sql2");
        }
        else
        { // END ML
                $sql2 -> db_Select("news_category","*",  "category_id!='' ORDER BY category_name ASC");
        }

        $text3 .="<table border='0' style='width:96%' align='center' cellpadding='3' cellspacing='3'><tr>\n";
        $t = 0;
        while($row3 = $sql2-> db_Fetch())
        {
                extract($row3);
                $category_name = $tp -> toHTML($category_name);
                $category_icon = e_IMAGE."newsicons/".$category_icon;
                $wid = floor(100/$nbr_cols);
                $text3 .= "<td style='vertical-align:top; width:$wid%;'>\n";
                $text3 .= "<div style='border-bottom:1px inset $line_clr; font-weight:bold;padding-bottom:1px;margin-bottom:5px'><img src='$category_icon' alt='' />&nbsp;<a href='news.php?cat.".$category_id."' style='text-decoration:none' >$category_name</a></div>";
                //  $text3 .= "</td>";

                // ML
                if(e_MLANG == 1)
                {
                        $ml -> e107_ml_Select("news", "*",  "news_category='$category_id' AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().")  ORDER BY news_datestamp DESC LIMIT 0,$nbr_lst");
                }
                else
                { // END ML
                        $count = $sql -> db_SELECT("news", "*",  "news_category='$category_id' AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().")  ORDER BY news_datestamp DESC LIMIT 0,$nbr_lst");
                }

                while($row = $sql-> db_Fetch())
                {
                        extract($row);
                        $text3 .="<div style='width:100%'><table style='width:100%' cellpadding='0' cellspacing='0' border='0'>\n";
                        if(check_class($news_class)){
                                $news_title = $tp -> toHTML($news_title);
                                if($news_title == ""){ $news_title = "Untitled"; }
                                //      $datestamp = $gen->convert_date($news_datestamp, "short");
                                $text3 .= "<tr><td style='width:2px;vertical-align:top'>•</td>\n";
                                $text3 .= "<td style='text-align:left;vertical-align:top;padding-left:2px'><a href='news.php?extend.".$news_id."'>".$news_title."</a></td></tr>\n";
                        }
                        $text3 .="</table></div>\n";
                }

                $text3 .="</td>";
                if($t == ($nbr_cols-1)){$text3 .="</tr><tr><td colspan='$nbr_cols' style='height:15px'></td></tr><tr>";
                $t =0;}else{
                $t++;
        }
}
$text3 .="</tr></table>";
$ns -> tablerender("News Categories", $text3);
}
setNewsCache($cacheString);
require_once(FOOTERF);
// =========================================================================
function setNewsCache($cacheString)
{
        global $pref, $aj;
        if($pref['cachestatus'])
        {
                $cache = ob_get_contents();
                echo $cache;
                set_cache($cacheString, $cache);
        }
}

function checkNewsCache($cacheString, $np = FALSE, $nfp = FALSE)
{
        global $pref, $aj;
        if($cache_data = retrieve_cache($cacheString))
        {
                echo $cache_data;
                if($np)
                {
                        require_once(e_HANDLER."np_class.php");
                        $ix = new nextprev("news.php", $from, ITEMVIEW, $news_total, LAN_84);
                }
                if($nfp && $pref['nfp_display'] == 2)
                {
                        require_once(e_PLUGIN."newforumposts_main/newforumposts_main.php");
                }
                require_once(FOOTERF);
                exit;
        }
}

?>