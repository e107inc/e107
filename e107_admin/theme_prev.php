<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/theme preview by Chavo
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

if (IsSet($_POST['updateprefs'])) {
    require_once("../class2.php");
    require_once(e_ADMIN."auth.php");
    $pref['sitetheme'] = $_POST['sitetheme'];
    save_prefs();
	$message = TPVLAN_12;
    header("location : ".e_SELF."");
    exit;
}

if(isSet($_POST['sitetheme'])) {
    $sitetheme = $_POST['sitetheme'];
    define("USERTHEME", $sitetheme);
    require_once("../class2.php");
    require_once(HEADERF);
    $text = "<form method='post' action='".e_SELF."'>
	<table style='width:95%' >
	<tr>
		<td >".TPVLAN_2."</td>
	</tr>
	<tr>
		<td >".TPVLAN_10."
		<input type ='hidden' name='sitetheme' value='".$sitetheme."'>
		<input class='button' type='submit' name='updateprefs' value='".TPVLAN_13."'>
		</td>
	</tr>
	<tr>
		<td ><a href='".e_SELF."'>".TPVLAN_9."</a></td>
	</tr>
	</table>
	</form>
		<br /><b>".TPVLAN_13."</b><br />
	".$themename." v".$themeversion." by ".$themeauthor." (".$themedate.")";
		$text .= (!empty($themeinfo) ? "<br />".TPVLAN_11.": ".$themeinfo."<br />" : "");
		$ns->tablerender(TPVLAN_1, $text);
		require_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_news.php");
		require_once(e_HANDLER."news_class.php");
		if (!is_object($aj)) {
			$aj = new textparse;
		}
    $ix = new news;
    ob_start();
    $news_total = $sql -> db_Count("news");
    if (!$sql -> db_Select("news", "*", "(news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().") ORDER BY news_datestamp DESC LIMIT 0,".ITEMVIEW)) {
        echo "<br /><br /><div style='text-align:center'><b>".LAN_83."</b></div><br /><br />";
    } else {
        $sql2 = new db;
        while (list($news['news_id'], $news['news_title'], $news['data'], $news['news_extended'], $news['news_datestamp'], $news['admin_id'], $news_category, $news['news_allow_comments'],  $news['news_start'], $news['news_end'], $news_active, $news['news_class']) = $sql -> db_Fetch()) {
            if (strstr(USERCLASS, $news['news_class'].".") || !$news['news_class']) {
                if (!$news_active) {
                    if ($news['admin_id'] == 1) {
                        $news['admin_name'] = $pref['siteadmin'];
                    } else if (!$news['admin_name'] = getcachedvars($news['admin_id'])) {
                        $sql2 -> db_Select("user", "user_name", "user_id='".$news['admin_id']."' ");
                        list($news['admin_name']) = $sql2 -> db_Fetch();
                        cachevars($news['admin_id'], $news['admin_name']);
                    }
                    $sql2 -> db_Select("news_category", "*",  "category_id='$news_category' ");
                    list($news['category_id'], $news['category_name'], $news['category_icon']) = $sql2-> db_Fetch();
                    $news['comment_total'] = $sql2 -> db_Count("comments", "(*)",  "WHERE comment_item_id='".$news['news_id']."' AND comment_type='0' ");
                    $ix -> render_newsitem($news);
                }
            }
        }
    }
    require_once(FOOTERF);
    exit;
}
require_once("../class2.php");

if(!getperms("1")){ header("location:".e_HTTP."index.php"); exit;}

require_once("auth.php");
$handle=opendir(e_THEME);
while ($file = readdir($handle)){
	if($file != "." && $file != ".." && $file != "templates" && $file != "shared"){
		if (is_readable(e_THEME.$file."/theme.php") && is_readable(e_THEME.$file."/style.css")){
			$dirlist[] = $file;
		}
	}
}
closedir($handle);

$text = (IsSet($message) ? $message : "");

$text .= "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:95%' class='fborder' cellspacing='1' cellpadding='0'>
<tr>
<td style='width:50%' class='forumheader3'>".TPVLAN_5.":<br /><span class='smalltext'>".TPVLAN_14."</span></td>
<td style='width:50%; text-align:right' class='forumheader3'>
<select name='sitetheme' class='tbox'>\n";
$counter = 0;
while(IsSet($dirlist[$counter])){
	if($dirlist[$counter] == $pref['sitetheme'][1]){
		$text .= "<option selected>".$dirlist[$counter]."</option>\n";
	}else{
		$text .= "<option>".$dirlist[$counter]."</option>\n";
	}
	$counter++;
}
$text .= "</select>
<input class='button' type='submit' name='previewtheme' value='".TPVLAN_6."'>
</td>
</tr>
<tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>".TPVLAN_7."</div>", $text);

require_once("footer.php");
?>	