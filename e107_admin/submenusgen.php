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
|     $Source: /cvs_backup/e107_0.7/e107_admin/submenusgen.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:21 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("I")){ header("location:".e_BASE."index.php"); }
$lan_file = e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_links.php";
if(file_exists($lan_file)){@include_once($lan_file);}
else{@include_once(e_LANGUAGEDIR."english/admin/lan_links.php");}

require_once("auth.php");
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."submenus_handler.php");
$rs = new form;
//$aj = new textparse;



unset($message);
// Add links
if($_POST['subNews']==1){
        $sub_url = "news.php";
        $sub_name = LAN_MENGEN_4; // News
        $sub_getcat = "news_category";
        $sub_getcatfield = "category_id, category_name";
        $sub_getcatsql = "category_id !='-2' ORDER BY category_name ASC";
        $sub_suburl = "news.php?cat.";
        $message .= create_submenu();
}

if($_POST['subLinks']==1){
        $sub_url = "links.php";
        $sub_name = LAN_MENGEN_41; // Links
        $sub_getcat = "link_category";
        $sub_getcatfield = "link_category_id, link_category_name";
        $sub_getcatsql = "link_category_id !='1' ORDER BY link_category_name ASC";
        $sub_suburl = "links.php?cat.";
        $message .= create_submenu();
}

if($_POST['subForums']==1){
        $sub_url = "forum.php";
        $sub_name = LAN_MENGEN_5; // Forum
        $sub_getcat = "forum";
        $sub_getcatfield = "forum_id, forum_name";
        $sub_getcatsql = "forum_parent !='0' ORDER BY forum_order ASC";
        $sub_suburl = "forum_viewforum.php?";
        $message .= create_submenu();
}

if($_POST['subArticles']==1){
        $sub_url = "content.php?article";
        $sub_name = LAN_MENGEN_6; // Articles
        $sub_getcat = "content";
        $sub_getcatfield = "content_id, content_heading";
        $sub_getcatsql = "content_type ='6' ORDER BY content_heading ASC";
        $sub_suburl = "content.php?article.cat.";
        $message .= create_submenu();
}

if($_POST['subDownloads']==1){
        $sub_url = "download.php";
        $sub_name = LAN_MENGEN_7; // Download
        $sub_getcat = "download_category";
        $sub_getcatfield = "download_category_id, download_category_name";
        $sub_getcatsql = "download_category_parent ='0' ORDER BY download_category_name ASC";
        $sub_suburl = "download.php#cat";
        $message .= create_submenu();
}

// Add specific links
if(isset($_POST['sublinkname'])){
        for($i=0;$i<count($_POST['sublinkname']);$i++){
                if($_POST['sublinkname'][$i]!=""){
                        $namelink = explode(".",$_POST['sublinkname'][$i]);
                        $name_link = str_replace("submenu.".$namelink[1].".","",$_POST['sublinkname'][$i]);
                        if($_POST['sublinkurl'][$i]!=""){
                                $sql -> db_Insert("links", "0, '".$_POST['sublinkname'][$i]."', '".$_POST['sublinkurl'][$i]."', '', '', '1', '".$i."', '0', '0', '0'");
                                $message .= LAN_MENGEN_26."<b>".$name_link."</b>".LAN_MENGEN_30."<b>".$namelink[1]."</b><br />";
                        }else{
                                $message .= "<b>".$name_link."</b>".LAN_MENGEN_40."<br />";
                        }
                }
        }
}

// Delete links
if($_POST['delNews']==1){
        $sub_suburl = "news.php?cat.";
        $message .= delete_submenu();
}
if($_POST['delLinks']==1){
        $sub_suburl = "links.php?cat.";
        $message .= delete_submenu();
}
if($_POST['delForums']==1){
        $sub_suburl = "forum_viewforum.php?";
        $message .= delete_submenu();
}
if($_POST['delArticles']==1){
        $sub_suburl = "content.php?article.cat.";
        $message .= delete_submenu();
}
if($_POST['delDownloads']==1){
        $sub_suburl = "download.php#cat";
        $message .= delete_submenu();
}
if($_POST['delAll']==1){
        $sub_delall = 1;
        $message .= delete_submenu();
}

// Display messages
if(isset($message)){
        $ns -> tablerender(LAN_MENGEN_15,$message);
}

// Function to display HTML to add links
function display_addlink($hsub_label, $hsub_element, $hsub_help){
        $rs = new form;
        $textform ="<tr>
        <td style=\"width:30%; vertical-align:top\" class=\"forumheader3\">".$hsub_label."</td>\n
        <td style=\"width:70%\" class=\"forumheader3\">\n";
        $textform .= $rs -> form_checkbox($hsub_element,1,0);
        $textform .= "<br /><b class=\"smalltext\" >".$hsub_help."</b></td>\n
        </tr>\n";
        return $textform;
}
// Function to display HTML to delete links
function display_dellink($hsub_label, $hsub_element, $hsub_help){
        $rs = new form;
        $textform ="<tr>\n
        <td style=\"width:30%; vertical-align:top\" class=\"forumheader3\">".$hsub_label."</td>\n
        <td style=\"width:70%\" class=\"forumheader3\">\n";
        $textform .= $rs -> form_checkbox($hsub_element,1,0);
        $textform .= "<br /><b class=\"smalltext\" >".$hsub_help."</b></td>\n
        </tr>\n";
        return $textform;
}
// Automatic Menu Generator HTML

$caption = LAN_MENGEN_1;
$text = "";
$text .= $rs -> form_open("post", e_SELF, "submenus_config");

$text .= "<table style=\"width:94%\" class=\"fborder\" >\n";

$text .="<tr>\n
<td style=\"vertical-align:top\" colspan=\"2\"  >\n
<img src=\"".e_IMAGE."generic/English/but_create.png\" width=\"55\" height=\"16\" style=\"vertical-align: middle; margin: 0px 5px 0px 5px;\" >
<b>".LAN_MENGEN_2."</b><br /><b class=\"smalltext\" >".LAN_MENGEN_3."</b></td>\n
</tr>\n";


// Add news links
$text .= display_addlink(LAN_MENGEN_4, "subNews", LAN_MENGEN_8);

// Add news links
$text .= display_addlink(LAN_MENGEN_41, "subLinks", LAN_MENGEN_42);

// Add forums links
$text .= display_addlink(LAN_MENGEN_5, "subForums", LAN_MENGEN_9);

// Add articles links
$text .= display_addlink(LAN_MENGEN_6, "subArticles", LAN_MENGEN_11);

// Add downloads links
$text .= display_addlink(LAN_MENGEN_7, "subDownloads", LAN_MENGEN_12);


// Add other sublinks
if($sql -> db_Select("links", "link_id,link_name,link_url", "link_category='1' AND link_name NOT REGEXP('submenu') AND (link_url NOT LIKE '%news.php%' AND link_url NOT LIKE '%forum.php%' AND link_url NOT LIKE '%content.php%' AND link_url NOT LIKE '%download.php%' AND link_url NOT LIKE '%links.php%')")){
        $text .="<tr>
        <td style=\"vertical-align:top\" class=\"forumheader3\" colspan=\"2\" >".LAN_MENGEN_34."\n";
        $text .= "<br /><b class=\"smalltext\" >".LAN_MENGEN_36."</b></td>\n
        </tr>\n";
        while($row = $sql -> db_Fetch()){
                extract($row);
                $text .="<tr>
                <td style=\"width:30%; vertical-align:top\" class=\"forumheader3\">".$row[1]."</td>\n
                <td style=\"width:70%\" class=\"forumheader3\">".LAN_MENGEN_35."<ul>\n<li style=\"margin: 2px 0px 2px 0px;\" >1 ".LAN_MENGEN_37;
                $text .= $rs -> form_text("sublinkname[]", 20, "", 100, "tbox", FALSE, LAN_MENGEN_37, " onchange=\"this.value='submenu.".$row[1].".'+this.value\" ");
                $text .= " - ".LAN_MENGEN_39." ";
                $text .= $rs -> form_text("sublinkurl[]", 20, "", 100, "tbox", FALSE, LAN_MENGEN_39);
                $text .= "</li><li style=\"margin: 2px 0px 2px 0px;\">2 ".LAN_MENGEN_37;
                $text .= $rs -> form_text("sublinkname[]", 20, "", 100, "tbox", FALSE, LAN_MENGEN_37, " onchange=\"this.value='submenu.".$row[1].".'+this.value\" ");
                $text .= " - ".LAN_MENGEN_39." ";
                $text .= $rs -> form_text("sublinkurl[]", 20, "", 100, "tbox", FALSE, LAN_MENGEN_39);
                $text .= "</li><li style=\"margin: 2px 0px 2px 0px;\">3 ".LAN_MENGEN_37;
                $text .= $rs -> form_text("sublinkname[]", 20, "", 100, "tbox", FALSE, LAN_MENGEN_37, " onchange=\"this.value='submenu.".$row[1].".'+this.value\" ");
                $text .= " - ".LAN_MENGEN_39." ";
                $text .= $rs -> form_text("sublinkurl[]", 20, "", 100, "tbox", FALSE, LAN_MENGEN_39);
                $text .= "</li><li style=\"margin: 2px 0px 2px 0px;\">4 ".LAN_MENGEN_37;
                $text .= $rs -> form_text("sublinkname[]", 20, "", 100, "tbox", FALSE, LAN_MENGEN_37, " onchange=\"this.value='submenu.".$row[1].".'+this.value\" ");
                $text .= " - ".LAN_MENGEN_39." ";
                $text .= $rs -> form_text("sublinkurl[]", 20, "", 100, "tbox", FALSE, LAN_MENGEN_39);
                $text .= "</li><li style=\"margin: 2px 0px 2px 0px;\">5 ".LAN_MENGEN_37;
                $text .= $rs -> form_text("sublinkname[]", 20, "", 100, "tbox", FALSE, LAN_MENGEN_37, " onchange=\"this.value='submenu.".$row[1].".'+this.value\" ");
                $text .= " - ".LAN_MENGEN_39." ";
                $text .= $rs -> form_text("sublinkurl[]", 20, "", 100, "tbox", FALSE, LAN_MENGEN_39);
                $text .= "</li></ul>\n<b class=\"smalltext\" >".LAN_MENGEN_38."</b></td>\n
                </tr>\n";
        }
}

// Button Submit
$text .="<tr>\n
<td style=\"vertical-align:top; text-align: center;\" class=\"forumheader3\" colspan=\"2\" >\n";
$text .= $rs -> form_button("submit", "submenus_submit", LAN_MENGEN_13." / ".LAN_MENGEN_18);
$text .= "</td>\n
</tr>\n";



// Delete submenus
$text .="<tr>\n
<td style=\"vertical-align:top\" colspan=\"2\"  >\n
<img src=\"".e_IMAGE."generic/English/but_delete.png\" width=\"55\" height=\"16\" style=\"vertical-align: middle; margin: 0px 5px 0px 5px;\" >\n
<b>".LAN_MENGEN_19."</b><br /><b class=\"smalltext\" >".LAN_MENGEN_20."</b></td>
</tr>\n";

// Delete news links
$text .= display_dellink(LAN_MENGEN_4, "delNews", LAN_MENGEN_21." ".LAN_MENGEN_4.LAN_MENGEN_22);

// Delete Links sublinks
$text .= display_dellink(LAN_MENGEN_41, "delLinks", LAN_MENGEN_43);

// Delete forums links
$text .= display_dellink(LAN_MENGEN_5, "delForums", LAN_MENGEN_21." ".LAN_MENGEN_5.LAN_MENGEN_22);

// Delete articles links
$text .= display_dellink(LAN_MENGEN_6, "delArticles", LAN_MENGEN_21." ".LAN_MENGEN_6.LAN_MENGEN_22);

// Delete downloads links
$text .= display_dellink(LAN_MENGEN_7, "delDownloads", LAN_MENGEN_21." ".LAN_MENGEN_7.LAN_MENGEN_22);

// Delete ALL submenu links
$text .="<tr>\n
<td style=\"width:30%; vertical-align:top\" class=\"forumheader3\">".LAN_MENGEN_14."</td>\n
<td style=\"width:70%\" class=\"forumheader3\">";
$text .= $rs -> form_checkbox("delAll",1,0);
$text .= "<br /><b class=\"smalltext\" >".LAN_MENGEN_17." ".LAN_MENGEN_4.", ".LAN_MENGEN_5.", ".LAN_MENGEN_6.", ".LAN_MENGEN_7.")<br />".LAN_MENGEN_23. " <a href=\"".e_ADMIN."links.php\" >".LAN_MENGEN_24."</a></b></td>\n
</tr>\n";

// Button Submit
$text .="<tr>\n
<td style=\"vertical-align:top; text-align: center;\" class=\"forumheader3\" colspan=\"2\" >\n";
$text .= $rs -> form_button("submit", "delete_submit", LAN_MENGEN_13." / ".LAN_MENGEN_18);
$text .= "</td>\n
</tr>\n";

$text .= "</table>\n";

$text .= $rs -> form_close();

$ns -> tablerender($caption, $text);

require_once("footer.php");
?>