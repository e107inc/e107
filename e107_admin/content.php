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
|     $Source: /cvs_backup/e107_0.7/e107_admin/content.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:20 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
    if($pref['htmlarea']){
        require_once(e_HANDLER."htmlarea/htmlarea.inc.php");
       $htmlarea_js =  htmlarea("data");
    }
if(!getperms("J") && !getperms("K") && !getperms("L")){
        header("location:".e_HTTP."index.php");
        exit;
}

require_once(e_HANDLER."textparse/basic.php");
$etp = new e107_basicparse;

require_once("auth.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."form_handler.php");


$rs = new form;

if(e_QUERY){
        $tmp = explode(".", e_QUERY);
        $action = $tmp[0];
        $sub_action = $tmp[1];
        $id = $tmp[2];
        unset($tmp);
}

foreach($_POST as $k => $v){
        if(preg_match("#^main_delete_(\d*)$#",$k,$matches) && $_POST[$k] == $etp->unentity(CNTLAN_7))
        {
                $delete_content=$matches[1];
        }
}

$aj = new textparse;

If(IsSet($_POST['submit'])){
        if($_POST['data'] != ""){
                $content_subheading = $aj -> formtpa($_POST['content_subheading'], "admin");
                $content_heading = $aj -> formtpa($_POST['content_heading'], "admin");
                $content_content = $aj -> formtpa($_POST['data'],"admin");
                $sql -> db_Insert("content", "0, '".$content_heading."', '".$content_subheading."', '$content_content', '".$_POST['auto_line_breaks']."', '".time()."', '".ADMINID."', '".$_POST['content_comment']."', '', '1', 0, ".$_POST['add_icons'].",  {$_POST['c_class']}");
                if($_POST['content_heading']){
                        $sql -> db_Select("content", "*", "ORDER BY content_datestamp DESC LIMIT 0,1 ", $mode="no_where");
                        list($content_id, $content_heading) = $sql-> db_Fetch();
                        $sql -> db_Insert("links", "0, '".$content_heading."', 'content.php?content.$content_id', '', '', '1', '0', '0', '0', {$_POST['c_class']} ");
                        clear_cache("sitelinks");
                        $message = CNTLAN_24;
                } else {
                        $sql -> db_Select("content", "*", "ORDER BY content_datestamp DESC LIMIT 0,1 ", $mode="no_where");
                        list($content_id, $content_heading) = $sql-> db_Fetch();
                        $message = CNTLAN_23." - 'article.php?".$content_id.".255'.";
                }
                clear_cache("content");
                unset($content_heading, $content_subheading, $content_content, $content_parent);
        } else {
                $message = CNTLAN_1;
        }
}

if(IsSet($_POST['update'])){
        $content_subheading = $aj -> formtpa($_POST['content_subheading'], "admin");
        $content_heading = $aj -> formtpa($_POST['content_heading'], "admin");
        $content_content = $aj -> formtpa($_POST['data'], "admin");
        $sql -> db_Update("content", " content_heading='$content_heading', content_subheading='$content_subheading', content_content='$content_content', content_parent='".$_POST['auto_line_breaks']."',  content_comment='".$_POST['content_comment']."', content_type='1', content_class='{$_POST['c_class']}', content_pe_icon='".$_POST['add_icons']."' WHERE content_id='".$_POST['content_id']."'");
        $sql -> db_Update("links", "link_class='".$_POST['c_class']."' WHERE link_name='$content_heading' ");
        unset($content_heading, $content_subheading, $content_content, $content_parent);
        $message = CNTLAN_2;
        clear_cache("content");
        clear_cache("sitelinks");
}

if($delete_content)
{
        $sql = new db;
        $sql -> db_Select("content", "*", "content_id=$delete_content");
        $row = $sql -> db_Fetch(); extract($row);
        $sql -> db_Delete("links", "link_name='".$content_heading."' ");
        $sql -> db_Delete("content", "content_id=$delete_content");
        $message = CNTLAN_20;
        unset($content_heading, $content_subheading, $content_content);
        clear_cache("content");
        clear_cache("sitelinks");
}

if(IsSet($message))
{
        $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$text = "<div style='text-align:center'><div style='border : solid 1px #000; padding : 4px; width : auto; height : 100px; overflow : auto; '>\n";
if(!$content_total = $sql -> db_Select("content", "*", "content_type='254' OR content_type='255' OR content_type='1' ORDER BY content_datestamp DESC"))
{
        $text .= "<div style='text-align:center'>".CNTLAN_4."</div>";
} else
{
        $text .= "
        <form method='post' action='".e_SELF."' onsubmit=\"return confirm_()\">
        <table class='fborder' style='width:100%'>
        <tr>
        <td style='width:5%' class='forumheader2'>&nbsp;</td>
        <td style='width:65%' class='forumheader2'>".CNTLAN_25."</td>
        <td style='width:30%' class='forumheader2'>".CNTLAN_26."</td>
        </tr>";
        while($row = $sql -> db_Fetch())
        {
                extract($row);
                $content_title = ($content_heading) ? $content_heading : $content_subheading;
                $text .= "<tr><td style='width:5%; text-align:center' class='forumheader3'>$content_id</td>
                <td style='width:65%' class='forumheader3'>$content_title</td>
                <td style='width:30%; text-align:center' class='forumheader3'>
                ".$rs -> form_button("button", "main_edit_{$content_id}", CNTLAN_6, "onclick=\"document.location='".e_SELF."?edit.$content_id'\"")."
                ".$rs -> form_button("submit", "main_delete_{$content_id}", CNTLAN_7)."

                </td>\n</tr>";
        }
        $text .= "</table>\n</form>";
}
$text .= "</div></div>";

$ns -> tablerender(CNTLAN_5, $text);

unset($content_heading, $content_subheading, $content_content, $content_parent, $content_comment);

if($action == "edit"){
        if($sql -> db_Select("content", "*", "content_id=$sub_action")){
                $row = $sql -> db_Fetch(); extract($row);
        }
}else{
        $content_comment = TRUE;
}

$article_total = $sql -> db_Select("content", "*", "content_type='254' OR content_type='255' OR content_type='1' ");

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."' id='dataform'>
<table style='width:80%' class='fborder'>

<tr>
<td style='width:20%; vertical-align:top' class='forumheader3'>".CNTLAN_10.":</td>
<td style='width:80%' class='forumheader3'>
<input class='tbox' type='text' name='content_heading' size='60' value='$content_heading' maxlength='100' />

</td>
</tr>
<tr>
<td style='width:20%' class='forumheader3'>".CNTLAN_11.":</td>
<td style='width:80%' class='forumheader3'>
<input class='tbox' type='text' name='content_subheading' size='60' value='$content_subheading' maxlength='100' />
</td>
</tr>
<tr>
<td style='width:20%' class='forumheader3'><span style='text-decoration: underline'>".CNTLAN_12.": </span></td>
<td style='width:80%' class='forumheader3'>";
$insertjs = (!$pref['htmlarea'])? "onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'":"";
$text .="<textarea class='tbox' id='data' name='data' style='width:100%' rows='30' cols='60' $insertjs >$content_content</textarea>";

if(!$pref['htmlarea']){
    $text .="
      <br />
      <input class='helpbox' type='text' name='helpb' size='100' />
      <br />";
      require_once(e_HANDLER."ren_help.php");
      $text .= ren_help();
}
$text .="
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".CNTLAN_21."?:</td>
<td style='width:80%' class='forumheader3'>";

if($content_parent){
        $text .= CNTLAN_14.": <input type='radio' name='auto_line_breaks' value='0' />
        ".CNTLAN_15.": <input type='radio' name='auto_line_breaks' value='1' checked='checked' />";
}else{
        $text .= CNTLAN_14.": <input type='radio' name='auto_line_breaks' value='0' checked='checked' />
        ".CNTLAN_15.": <input type='radio' name='auto_line_breaks' value='1' />";
}
$text .= "<span class='smalltext'>".CNTLAN_22."</span>
</td></tr>
<tr>
<td style='width:20%' class='forumheader3'>".CNTLAN_13."?:</td>
<td style='width:80%' class='forumheader3'>";


if(!$content_comment){
        $text .= CNTLAN_14.": <input type='radio' name='content_comment' value='1' />
        ".CNTLAN_15.": <input type='radio' name='content_comment' value='0' checked='checked' />";
}else{
        $text .= CNTLAN_14.": <input type='radio' name='content_comment' value='1' checked='checked' />
        ".CNTLAN_15.": <input type='radio' name='content_comment' value='0' />";
}


$text .= "
</td></tr>


        <tr>
        <td class='forumheader3'>".CNTLAN_28.":&nbsp;&nbsp;</td><td class='forumheader3'>".
        ($content_pe_icon ? CNTLAN_29.": <input type='radio' name='add_icons' value='1' checked='checked' />".CNTLAN_30.": <input type='radio' name='add_icons' value='0' />" : CNTLAN_29.": <input type='radio' name='add_icons' value='1' />".CNTLAN_30.": <input type='radio' name='add_icons' value='0' checked='checked' />")."
        </td>
        </tr>

";

$text.="
<tr>
<td style='width:20%' class='forumheader3'>".CNTLAN_19.":</td>
<td style='width:80%' class='forumheader3'>".r_userclass("c_class",$content_class)."
</td>
</tr>
<tr style='vertical-align:top'>
<td colspan='2'  style='text-align:center' class='forumheader'>";


if($action == "edit"){
        $text .= "<input class='button' type='submit' name='update' value='".CNTLAN_16."' />
        <input type='hidden' name='content_id' value='$content_id' />";
}else{
        $text .= "<input class='button' type='submit' name='submit' value='".CNTLAN_17."' />";
}

$text .= "</td>
</tr>
</table>
</form>
</div>";


$ns -> tablerender("<div style='text-align:center'>".CNTLAN_18."</div>", $text);

echo "<script type=\"text/javascript\">
function confirm_(content_id){
        return  confirm(\"".$etp->unentity(CNTLAN_27)."\");
}
</script>";

require_once("footer.php");
?>