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
|     $Source: /cvs_backup/e107_0.7/print.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:12:45 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");

$qs = explode(".", e_QUERY);
if($qs[0] == ""){ header("location:".e_BASE."index.php"); exit;}
$table = $qs[0];
$id = $qs[1];
$con = new convert;
$aj = new textparse;


$print_info[] = array( 'table' => 'news',  'handler' => '
        $sql -> db_Select("news", "*", "news_id=\"$id\" ");
        $row = $sql -> db_Fetch(); extract($row);
        $news_body = $aj -> tpa($news_body);
        $news_extended = $aj -> tpa($news_extended);
        if($news_author == 0){
                $a_name = "e107";
                $category_name = "e107 welcome message";
        }else{
                $sql -> db_Select("news_category", "*", "category_id=\"$news_category\" ");
                list($category_id, $category_name) = $sql-> db_Fetch();
                $sql -> db_Select("user", "*", "user_id=\"$news_author\" ");
                list($a_id, $a_name) = $sql-> db_Fetch();
        }
        $news_datestamp = $con -> convert_date($news_datestamp, "long");

        $text = "<font style=\"font-size: 11px; color: black; font-family: tahoma, verdana, arial, helvetica; text-decoration: none\">
        <b>".LAN_135.": ".$news_title."</b>
        <br />
        (".LAN_86." ".$category_name.")
        <br />
        ".LAN_94." ".$a_name."<br />
        ".$news_datestamp."
        <br /><br />".
        $news_body;
        if($news_extended != ""){ $text .= "<br /><br />".$news_extended; }
        if($news_source != ""){ $text .= "<br /><br />".$news_source; }
        if($news_url != ""){ $text .= "<br />".$news_url; }

        $text .= "<br /><br /><hr />".
        LAN_303.SITENAME."
        <br />
        ( http://".$_SERVER[HTTP_HOST].e_HTTP."comment.php?comment.news.comment.".$news_id." )
        </font>";
');

$print_info[] = array( 'table' => 'content', 'handler' => '
        $sql -> db_Select("content", "*", "content_id=\"$id\" ");
        $row = $sql -> db_Fetch();
        extract($row);
        $content_heading = $aj -> tpa($content_heading);
        $content_subheading = $aj -> tpa($content_subheading);
        $content_content = ereg_replace("\{EMAILPRINT\}|\[newpage\]", "", $aj -> tpa($content_content));
        if(is_numeric($content_author)) {
                $sql -> db_Select("user", "*", "user_id=\"$content_author\" ");
                list($a_id, $a_name) = $sql-> db_Fetch();
        } else {
                $tmp = explode("^",$content_author);
                $a_name = $tmp[0];
                $user_email = $tmp[1];
        }
        $content_datestamp = $con -> convert_date($content_datestamp, "long");
        $text = "<font style=\"FONT-SIZE: 11px; COLOR: black; FONT-FAMILY: Tahoma, Verdana, Arial, Helvetica; TEXT-DECORATION: none\">
        <b>".LAN_304.$content_heading."</b>
        <br />
        ".LAN_305.$content_subheading."
        <br />
        ".LAN_87.$a_name."<br />
        ".$content_datestamp."
        <br /><br />".
        $content_content."
        <br /><br /><hr />
        ".LAN_306.SITENAME."
        <br />
        ( http://".$_SERVER[HTTP_HOST].e_HTTP."article.php?article.".$content_id." )
        </font>";
');


//load the others from plugins
$handle=opendir(e_PLUGIN);
while(false !== ($file = readdir($handle))){
        if($file != "." && $file != ".." && is_dir(e_PLUGIN.$file)){
                $plugin_handle=opendir(e_PLUGIN.$file."/");
                while(false !== ($file2 = readdir($plugin_handle))){
                        if($file2 == "e_print.php"){
                                require_once(e_PLUGIN.$file."/".$file2);
                        }
                }
        }
}

//eval the handler
foreach($print_info as $key => $si){
        if($print_info[$key]['table'] == $table){
                eval($print_info[$key]['handler']);
        }
}
echo "<div style=\"text-align:center\">
        <img src=\"".e_IMAGE."logo.png\" alt=\"Logo\" />
        </div>
        <hr />
        <br />";

echo $text;

echo "<br /><br /><div style='text-align:center'><form><input type='button' value='".LAN_307."' onClick='window.print()'></form></div>";

?>