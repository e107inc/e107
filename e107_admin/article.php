<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /admin/review.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
$Id: article.php,v 1.23 2004-08-11 09:44:46 e107coders Exp $
*/
require_once("../class2.php");
if($pref['htmlarea']){
    require_once(e_HANDLER."htmlarea/htmlarea.inc.php");
   $htmlarea_js =  htmlarea("data");
  //  htmlarea("content_summary");
}
if(!getperms("J") && !getperms("K") && !getperms("L")){header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");
$aj = new textparse;
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."userclass_class.php");

$rs = new form;

$deltest = array_flip($_POST);
if(e_QUERY){
        $tmp = explode(".", e_QUERY);
        $action = $tmp[0];
        $sub_action = $tmp[1];
        $id = $tmp[2];
        unset($tmp);
}
if(preg_match("#(.*?)_delete_(\d+)#",$deltest['Delete'],$matches))
{
        $delete = $matches[1];
        $del_id = $matches[2];
}

// ##### DB --------------------------------------------------------------------------------------------------------------------------------------------------------------------

if(IsSet($_POST['create_category'])){
        $_POST['category_name'] = $aj -> formtpa($_POST['category_name'], "admin");
        $_POST['category_description'] = $aj -> formtpa($_POST['category_description'], "admin");
        $sql -> db_Insert("content", " '0', '".$_POST['category_name']."', '".$_POST['category_description']."', '', 0, ".time().", '".ADMINID."', 0, '".$_POST['category_button']."', 6, 0, 0, 0");
        $message = ARLAN_56;
        clear_cache("article");
        $action = "cat";
}

if(IsSet($_POST['update_category'])){
        $_POST['category_name'] = $aj -> formtpa($_POST['category_name'], "admin");
        $_POST['category_description'] = $aj -> formtpa($_POST['category_description'], "admin");
        $sql -> db_Update("content", "content_heading='".$_POST['category_name']."', content_subheading='".$_POST['category_description']."', content_summary='".$_POST['category_button']."' WHERE content_id='".$_POST['category_id']."' ");
        $message = ARLAN_57;
        clear_cache("article");
        $action = "cat";
}

if(IsSet($_POST['create_article'])){
        if($_POST['data'] && $_POST['content_heading']){
                $content_subheading = $aj -> formtpa($_POST['content_subheading'], "admin");
                $content_heading = $aj -> formtpa($_POST['content_heading'], "admin");
                $content_content = $aj -> formtpa($_POST['data'], "admin");
                $content_author = (!$_POST['content_author'] || $_POST['content_author'] == ARLAN_84 ? ADMINID : $_POST['content_author']."^".$_POST['content_author_email']);
                $sql -> db_Insert("content", "0, '$content_heading', '$content_subheading', '$content_content', '".$_POST['category']."', '".time()."', '$content_author', '".$_POST['content_comment']."', '".$_POST['content_summary']."', '0' ,'0' ,".$_POST['add_icons'].", ".$_POST['a_class']);
                unset($content_heading, $content_subheading, $data, $content_summary, $content_author);
                $message = ARLAN_0;
                clear_cache("article");
        }else{
                $message = ARLAN_1;
        }
        unset($action, $sub_action);
}

If(IsSet($_POST['sa_article'])){

        if($_POST['data'] && $_POST['content_heading']){
                if($_POST['category'] == -1){ unset($_POST['category']); }
                $content_subheading = $aj -> formtpa($_POST['content_subheading'], "admin");
                $content_heading = $aj -> formtpa($_POST['content_heading'], "admin");
                $content_content = $aj -> formtpa($_POST['data'], "admin");
                $content_summary = $aj -> formtpa($_POST['content_summary'], "admin");
                $content_author = ($_POST['content_author'] && $_POST['content_author'] != ARLAN_84 ? $_POST['content_author']."^".$_POST['content_author_email'] : ADMINID);
                $sql -> db_Update("content", " content_heading='$content_heading', content_subheading='$content_subheading', content_content='$content_content', content_parent='".$_POST['category']."', content_datestamp='".time()."', content_author='$content_author', content_comment='".$_POST['content_comment']."', content_summary='$content_summary', content_type='0',  content_pe_icon=".$_POST['add_icons'].", content_class='{$_POST['a_class']}' WHERE content_id=$id");
                unset($content_heading, $content_subheading, $data, $content_summary);
                $message = ARLAN_99;
                unset($action);
        }else{
                $message = ARLAN_1;
        }
}


If(IsSet($_POST['update_article'])){
        if($_POST['data'] && $_POST['content_heading']){
                if($_POST['category'] == -1){ unset($_POST['category']); }
                $content_subheading = $aj -> formtpa($_POST['content_subheading'], "admin");
                $content_heading = $aj -> formtpa($_POST['content_heading'], "admin");
                $content_content = $aj -> formtpa($_POST['data'], "admin");
                $content_summary = $aj -> formtpa($_POST['content_summary'], "admin");
                $content_author = ($_POST['content_author'] && $_POST['content_author'] != ARLAN_84 ? $_POST['content_author']."^".$_POST['content_author_email'] : ADMINID);
                $sql -> db_Update("content", " content_heading='$content_heading', content_subheading='$content_subheading', content_content='$content_content', content_parent='".$_POST['category']."', content_datestamp='".time()."', content_author='$content_author', content_comment='".$_POST['content_comment']."', content_summary='$content_summary', content_type='0', content_pe_icon=".$_POST['add_icons'].", content_class='{$_POST['a_class']}' WHERE content_id='".$_POST['content_id']."'");
                unset($content_heading, $content_subheading, $data, $content_summary);
                $message = ARLAN_2;
                                 unset($action, $sub_action);
                clear_cache("article");
        }else{
                $message = ARLAN_1;
        }
}

if(IsSet($_POST['updateoptions'])){
        $pref['article_submit'] = $_POST['article_submit'];
        $pref['article_submit_class'] = $_POST['article_submit_class'];
        save_prefs();
                if($pref['article_submit'] ){
                        $sql -> db_Update("links", "link_class=".$pref['article_submit_class']." WHERE link_url='subcontent.php?article' ");
                }else{
                        $sql -> db_Update("links", "link_class='255' WHERE link_url='subcontent.php?article' ");
                }
        $message = ARLAN_92;
}

if($delete == 'category')
{
        if($sql -> db_Delete("content", "content_id='$del_id' "))
        {
                $message = ARLAN_58;
                unset($sub_action, $id);
                $action = "cat";
        }
}

if($delete == "main")
{
        if($sql -> db_Delete("content", "content_id='$del_id' "))
        {
                $message = ARLAN_30;
                clear_cache("article");
                unset($action, $sub_action, $id);
        }
}

if(IsSet($_POST['preview'])){
        $obj = new convert;
        $datestamp = $obj->convert_date(time(), "long");
        $ch = $aj -> formtpa($_POST['content_heading'],"admin"); $ch = $aj -> tpa($ch,"nobreak","admin");
        $cs = $aj -> formtpa($_POST['content_subheading'],"admin"); $cs = $aj -> tpa($ch,"nobreak","admin");
        $dt = (strstr($_POST['data'], "[img]http") ? $_POST['data'] : str_replace("[img]", "[img]../", $_POST['data']));
        $dt = $aj -> formtpa($dt,"admin"); $dt = $aj -> tpa($dt,"off","admin");
        $cu= $aj -> formtpa($_POST['content_summary'],"admin"); $cu= $aj -> tpa($cu,"nobreak","admin");
        $ca = ($_POST['content_author'] && $_POST['content_author'] != ARLAN_84 ? $_POST['content_author'] : ADMINNAME);
        $text = "<i>by $ca</i><br /><span class='smalltext'>".$datestamp."</span><br /><br />".ARLAN_18.": $cs<br />".ARLAN_19.": $cu<br /><br />$dt";

                 /* 9 Aug 2004 - unknown - the article preview looks more like the actual article */
                 $text = '<div style="text-align: center"><table class="fborder" style="width:95%" border="0"><tr><td>'.$text.'</td></tr></table></div>';

        $ns -> tablerender($content_heading, $text);
        echo "<br /><br />";
        // make form friendly ...
        /* Before bug fixing for apostroph
                $content_heading = $aj -> formtparev($_POST['content_heading']);
        $content_subheading = $aj -> formtparev($_POST['content_subheading']);
        $data = $aj -> formtparev(str_replace("../", "", $_POST['data']));
        $content_summary = $aj -> formtparev($_POST['content_summary']);
                */
                $content_heading = $aj -> formtpa($_POST['content_heading'],"admin");
        $content_subheading = $aj -> formtpa($_POST['content_subheading'],"admin");
        $data = $aj -> formtpa(str_replace("../", "", $_POST['data']),"admin");
        $content_summary = $aj -> formtpa($_POST['content_summary'],"admin");

        $content_parent = $_POST['category'];
                $content_class = $_POST['a_class'];
}


// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------


if(IsSet($message)){
        $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}


// ##### Categories ------------------------------------------------------------------------------------------------------------------------------------------------------------

if($action == "cat"){
        $text = "<div style='border : solid 1px #000; padding : 4px; width : auto; height : 100px; overflow : auto; '>\n";
        if($category_total = $sql -> db_Select("content", "*", "content_type='6' ")){
                $text .= "<table class='fborder' style='width:100%'>
                <tr>
                <td style='width:5%' class='forumheader2'>&nbsp;</td>
                <td style='width:75%' class='forumheader2'>".ARLAN_59."</td>
                <td style='width:20%; text-align:center' class='forumheader2'>".ARLAN_60."</td>
                </tr>";
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        $delete_heading = str_replace("&#39;", "\'", $content_heading);
                        $text .= "<tr>
                        <td style='width:5%; text-align:center' class='forumheader3'>".($content_summary ? "<img src='".e_IMAGE."link_icons/$content_summary' alt='' style='vertical-align:middle' />" : "&nbsp;")."</td>
                        <td style='width:75%' class='forumheader3'>$content_heading [$content_subheading]</td>
                        <td style='width:20%; text-align:center' class='forumheader3'>
                        ".$rs -> form_button("submit", "category_edit", ARLAN_61, "onclick=\"document.location='".e_SELF."?cat.edit.$content_id'\"")."
                               ".$rs -> form_open("post", e_SELF,"myform_{$content_id}","",""," onsubmit=\"return confirm_('cat','$delete_heading','$content_id')\"")."
                                              ".$rs -> form_button("submit", "category_delete_{$content_id}", ARLAN_62)."
                                              ".$rs -> form_close()."

                        </td>
                        </tr>";
                }
                $text .= "
                </table>";
        }else{
                $text .= "<div style='text-align:center'>".ARLAN_63."</div>";
        }
        $text .= "</div>";
        $ns -> tablerender(ARLAN_64, $text);

        $handle=opendir(e_IMAGE."link_icons");
        while ($file = readdir($handle)){
                if($file != "." && $file != ".." && $file != "/"){
                        $iconlist[] = $file;
                }
        }
        closedir($handle);

        unset($content_heading, $content_summary, $content_subheading);

        if($sub_action == "edit"){
                if($sql -> db_Select("content", "*", "content_id='$id' ")){
                        $row = $sql -> db_Fetch(); extract($row);
                }
        }

        $text = "<div style='text-align:center'>
        ".$rs -> form_open("post", e_SELF."?cat", "dataform")."
        <table class='fborder' style='width:auto'>
        <tr>
        <td class='forumheader3' style='width:30%'><span class='defaulttext'>".ARLAN_65."</span></td>
        <td class='forumheader3' style='width:70%'>".$rs -> form_text("category_name", 30, $content_heading, 25)."</td>
        </tr>
        <tr>
        <td class='forumheader3' style='width:30%'><span class='defaulttext'>".ARLAN_66."</span></td>
        <td class='forumheader3' style='width:70%'>
        ".$rs -> form_text("category_button", 60, $content_summary, 100)."
        <br />
        <input class='button' type ='button' style='cursor:hand' size='30' value='".ARLAN_67."' onclick='expandit(this)' />
        <div style='{head}; display:none'>";
        while(list($key, $icon) = each($iconlist)){
                $text .= "<a href='javascript:addtext2(\"$icon\")'><img src='".e_IMAGE."link_icons/".$icon."' style='border:0' alt='' /></a> ";
        }
        $text .= "</div></td>
        </tr>
        <tr>
        <td class='forumheader3' style='width:30%'><span class='defaulttext'>".ARLAN_68."</span></td>
        <td class='forumheader3' style='width:70%'>".$rs->form_textarea("category_description", 59, 3, $content_subheading)."</td>
        </tr>
        <tr><td colspan='2' style='text-align:center' class='forumheader'>";
        if($id){
                $text .= "<input class='button' type='submit' name='update_category' value='".ARLAN_69."' />
                ".$rs -> form_button("submit", "category_clear", "".ARLAN_70."").
                $rs -> form_hidden("category_id", $id)."
                </td></tr>";
        }else{
                $text .= "<input class='button' type='submit' name='create_category' value='".ARLAN_71."' /></td></tr>";
        }
        $text .= "</table>
        ".$rs -> form_close()."
        </div>";

        $ns -> tablerender(ARLAN_71, $text);
}

// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------


// ##### Display scrolling list of existing articles --------------------------------------------------------------------------------------------------------------------------
if(!$action || $action == "confirm" || $action == 'c'){

        $text = "";
        // -------- SHOW FIRST LETTERS FIRSTNAMES ------------------------------------
        $sql = new db;
        $distinctfirstletter = $sql -> db_Select("content", "DISTINCT(LEFT(content_heading,1)) as letter", "content_type='0' ORDER BY content_heading ASC ");

        if ($distinctfirstletter != 1){

                $text .= "<form method='post' action='".e_SELF."'>
                <table class='fborder' style='width:100%'>

                <tr><td colspan='2' class='forumheader'>".ARLAN_31."</td></tr>
                <tr><td colspan='2' class='forumheader3'>";

                while($row = $sql-> db_Fetch()){
                extract($row);
                        $text .= "<input class='button' style='width:20' type='submit' name='letter' value='".strtoupper($letter)."' />";
                }

                $text .= "
                <input class='button' style='width:20' type='submit' name='letter' value='all' />
                </td></tr>
                </table></form> ";
        }
        // ---------------------------------------------------------------------------

        // -------- CHECK FOR FIRST LETTER SUBMISSION --------------------------------
        $sql = new db;
        $letter=$_POST['letter'];
        if ($_POST['letter'] != "" && $_POST['letter'] != "all" ) {
                $letter = $_POST['letter'];
                $query = "content_type='0' AND content_heading LIKE '".$letter."%' ORDER BY content_datestamp DESC";
        } else {
                if($sub_action){
                        $cat=intval($sub_action);
                        if($cat=="none"){$cat='0';}
                        $query = "content_type='0' AND content_parent={$cat} ORDER BY content_datestamp DESC";
                } else {
                        $query = "content_type='0' ORDER BY content_datestamp DESC";
                }
        }
        // ---------------------------------------------------------------------------

        $sql2 = new db;
        $text .= "<div style='border : solid 1px #000; padding : 4px; width : auto; height : 400px; overflow : auto; '>";
        if($article_total = $sql -> db_Select("content", "*", $query)){
                if($article_total < 50 || $letter || $cat){
                        $text .= "<table class='fborder' style='width:100%'>
                        <tr>
                        <td style='width:5%' class='forumheader2'>&nbsp;</td>
                        <td style='width:50%' class='forumheader2'>".ARLAN_20."</td>
                        <td style='width:45%' class='forumheader2'>".ARLAN_60."</td>
                        </tr>";
                        while($row = $sql -> db_Fetch()){
                                extract($row);
                                unset($cs);
                                $delete_heading = str_replace("&#39;", "\'", $content_heading);
                                if($sql2 -> db_Select("content", "content_summary", "content_id=$content_parent")){
                                        $row = $sql2 -> db_Fetch(); $cs = $row[0];
                                }
                                $text .= "<tr>
                                <td style='width:5%; text-align:center' class='forumheader3'>".($cs ? "<img src='".e_IMAGE."link_icons/$cs' alt='' style='vertical-align:middle' />" : "&nbsp;")."</td>
                                <td style='width:75%' class='forumheader3'><a href='".e_BASE."content.php?article.$content_id'>$content_heading</a> [$content_subheading]</td>
                                <td style='width:20%; text-align:center' class='forumheader3'>
                                ".$rs -> form_open("post", e_SELF,"myform_{$content_id}","",""," onsubmit=\"return confirm_('create','$delete_heading','$content_id')\"")."
                                <div>".$rs -> form_button("button", "main_edit_{$content_id}", ARLAN_61, "onclick=\"document.location='".e_SELF."?create.edit.$content_id'\"")."
                                ".$rs -> form_button("submit", "main_delete_{$content_id}", ARLAN_62)."</div>
                                ".$rs -> form_close()."

                                </td>
                                </tr>";
                        }
//                                ".$rs -> form_button("submit", "main_delete", ARLAN_62, "onclick=\"confirm_('create', '$delete_heading', $content_id)\"")."
                        $text .= "</table>";
                } else {
                        $text .= "<br /><div style='text-align:center'>".ARLAN_32."</div>";
                }
        } else {
                $text .= "<div style='text-align:center'>".ARLAN_14."</div>";
        }
        $text .= "</div>";
        $ns -> tablerender(ARLAN_72, $text);
        }

// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------


// ##### Display the create article entry screen ------------------------------------------------------------------------------------------------------------------------------


if($action == "create"){
        if($sub_action == "edit" && !$_POST['preview']){
                if($sql -> db_Select("content", "*", "content_id='$id' ")){
                        $row = $sql -> db_Fetch(); extract($row);
                        $data = str_replace("<br />", "", $aj -> formtparev($content_content));
                        if(is_numeric($content_author)){
                                $content_author = "";
                                $content_author_email = "";
                        }else{
                                $tmp = explode("^", $content_author);
                                $content_author = $tmp[0];
                                $content_author_email = $tmp[1];
                        }
                }
        }

        if($sub_action == "sa" && !$_POST['preview']){
                if($sql -> db_Select("content", "*", "content_id=$id")){
                        $row = $sql -> db_Fetch(); extract($row);
                        $data = $content_content;
                        $tmp = explode("^", $content_author);
                        $content_author = $tmp[0];
                        $content_author_email = $tmp[1];
                }
        }

        require_once(e_HANDLER."userclass_class.php");

        $text = "<div style='text-align:center'>
        ".$rs -> form_open("post", e_SELF."?".e_QUERY."", "dataform")."

        <table style='width:95%' class='fborder'>

        <tr>
        <td style='width:20%; vertical-align:top' class='forumheader3'>".ARLAN_74.":</td>
        <td style='width:80%' class='forumheader3'>";

        $sql -> db_Select("content", "*", "content_type=6 ");
        $text .= $rs->form_select_open("category");
        $text .= (!$content_parent ? $rs -> form_option("- ".ARLAN_75." -", 1, -1) : $rs -> form_option("- ".ARLAN_75." -", 0, -1));
        while(list($category_id, $category_name) = $sql-> db_Fetch()){
                $text .= ($category_id == $content_parent ? $rs -> form_option($category_name, 1, $category_id) : $rs -> form_option($category_name, 0, $category_id));
        }
        $text .= $rs -> form_select_close()."
        </td>
        </tr>

        <tr>
        <td style='width:20%; vertical-align:top' class='forumheader3'>".ARLAN_82.":<br /><span class='smalltext'>(".ARLAN_83.")</span></td>
        <td style='width:80%' class='forumheader3'>
                <a href=\"javascript:void(0);\" onclick=\"expandit(this);\" >".ARLAN_100."</a>\n
        <span style=\"display: none;\" >
                <br /><br />
                <input class='tbox' type='text' name='content_author' size='60' value='".($content_author ? $content_author : ARLAN_84)."' maxlength='100' ".($content_author ? "" : "onfocus=\"if(document.dataform.content_author.value=='".ARLAN_84."'){document.dataform.content_author.value='';}\"")." /><br />
        <input class='tbox' type='text' name='content_author_email' size='60' value='".($content_author_email ? $content_author_email : ARLAN_85)."' maxlength='100' ".($content_author_email ? "" : "onfocus=\"if(document.dataform.content_author_email.value=='".ARLAN_85."'){document.dataform.content_author_email.value='';}\"")." /><br />
        </span>
                </td>
        </tr>

        <tr>
        <td style='width:20%; vertical-align:top' class='forumheader3'>".ARLAN_17.":</td>
        <td style='width:80%' class='forumheader3'>
        <input class='tbox' type='text' name='content_heading' size='60' value='$content_heading' maxlength='100' />
        </td>
        </tr>

        <tr>
        <td style='width:20%' class='forumheader3'>".ARLAN_18.":</td>
        <td style='width:80%' class='forumheader3'>
        <input class='tbox' type='text' name='content_subheading' size='60' value='$content_subheading' maxlength='100' />
        </td>
        </tr>

        <tr>
        <td style='width:20%' class='forumheader3'>".ARLAN_19.":</td>
        <td style='width:80%' class='forumheader3'>
        <textarea class='tbox' id='content_summary' name='content_summary' cols='90' rows='5'>$content_summary</textarea>
        </td>
        </tr>

        <tr>
        <td style='width:20%' class='forumheader3'>".ARLAN_20.": </td>
        <td style='width:80%' class='forumheader3'>
        <textarea class='tbox' id='data' name='data' cols='90' rows='30' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>$data</textarea>
        ";
        if(!$pref['htmlarea']){
            $text .="<br />
            <input class='helpbox' type='text' name='helpb' size='100' />
            <br />";

            require_once(e_HANDLER."ren_help.php");
            $text .= ren_help();
            }
        $text .="
        </td>
        </tr>

        <tr>
        <td colspan='2' class='forumheader3'>".ARLAN_21.":&nbsp;&nbsp;".

        ($content_comment ? ARLAN_22.": <input type='radio' name='content_comment' value='1' checked='checked' />".ARLAN_23.": <input type='radio' name='content_comment' value='0' />" : ARLAN_22.": <input type='radio' name='content_comment' value='1' />".ARLAN_23.": <input type='radio' name='content_comment' value='0' checked='checked' />")."
        </td>
        </tr>

        <tr>
        <td colspan='2' class='forumheader3'>".ARLAN_24.":&nbsp;&nbsp;".
        ($content_pe_icon ? ARLAN_25.": <input type='radio' name='add_icons' value='1' checked='checked' />".ARLAN_26.": <input type='radio' name='add_icons' value='0' />" : ARLAN_25.": <input type='radio' name='add_icons' value='1' />".ARLAN_26.": <input type='radio' name='add_icons' value='0' checked='checked' />")."
        </td>
        </tr>

        <tr>
        <td style='width:20%' class='forumheader3'>".ARLAN_55.":</td>
        <td style='width:80%' class='forumheader3'>
        ".r_userclass("a_class",$content_class)."
        </td>
        </tr>

        <tr style='vertical-align:top'>
        <td colspan='2'  style='text-align:center' class='forumheader'>".
        (!$_POST['preview'] ? "<input class='button' type='submit' name='preview' value='".ARLAN_28."' />" : "<input class='button' type='submit' name='preview' value='".ARLAN_27."' />")." ".
        ($sub_action == "edit" || $_POST['editp']? "<input class='button' type='submit' name='update_article' value='".ADLAN_81." ".ARLAN_20."' />\n<input type='hidden' name='content_id' value='$id' />" : ($sub_action == "sa" ? "<input class='button' type='submit' name='sa_article' value='".ARLAN_98."' />" : "<input class='button' type='submit' name='create_article' value='".ADLAN_85." ".ARLAN_20."' />"))."
        </td>
        </tr>
        </table>
        </form>
        </div>";

        $ns -> tablerender("<div style='text-align:center'>".ARLAN_15."</div>", $text);

}
// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------

if($action == "opt"){
        global $pref, $ns;
        $text = "<div style='text-align:center'>
        <form method='post' action='".e_SELF."?".e_QUERY."'>\n
        <table style='width:auto' class='fborder'>
        <tr>

        <td style='width:70%' class='forumheader3'>
        ".ARLAN_86."<br />
        <span class='smalltext'>".ARLAN_87."</span>
        </td>
        <td class='forumheader2' style='width:30%;text-align:center'>".
        ($pref['article_submit'] ? "<input type='checkbox' name='article_submit' value='1' checked='checked' />" : "<input type='checkbox' name='article_submit' value='1' />")."
        </td>
        </tr>

        <tr>
        <td style='width:70%' class='forumheader3'>
        ".ARLAN_88."<br />
        <span class='smalltext'>".ARLAN_89."</span>
        </td>
        <td class='forumheader2' style='width:30%; text-align:center'>".r_userclass("article_submit_class", $pref['article_submit_class'])."</td>
        </tr>

        <tr style='vertical-align:top'>
        <td colspan='2'  style='text-align:center' class='forumheader'>
        <input class='button' type='submit' name='updateoptions' value='".ARLAN_90."' />
        </td>
        </tr>

        </table>
        </form>
        </div>";
        $ns -> tablerender(ARLAN_91, $text);
}

if($action == "sa"){
        global $sql, $rs, $ns, $aj;
        $text = "<div style='border : solid 1px #000; padding : 4px; width :auto; height : 200px; overflow : auto; '>\n";
        if($article_total = $sql -> db_Select("content", "*", "content_type=15")){
                $text .= "<table class='fborder' style='width:100%'>
                <tr>
                <td style='width:5%' class='forumheader2'>ID</td>
                <td style='width:75%' class='forumheader2'>".ARLAN_96."</td>
                <td style='width:20%; text-align:center' class='forumheader2'>".ARLAN_60."</td>
                </tr>";
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        $tmp = explode("^", $content_author);
                        $content_author = $tmp[0];
                        $content_author_email = ($tmp[1] ? $tmp[1] : ARLAN_95);
                        $text .= "<tr>
                        <td style='width:5%; text-align:center; vertical-align:top' class='forumheader3'>$content_id</td>
                        <td style='width:75%' class='forumheader3'><b>".$aj -> tpa($content_heading)."</b> [".$aj -> tpa($content_subheading)."]<br />$content_author ($content_author_email)</td>
                        <td style='width:20%; text-align:center; vertical-align:top' class='forumheader3'>
                        ".$rs -> form_button("submit", "category_edit", ARLAN_97, "onclick=\"document.location='".e_SELF."?create.sa.$content_id'\"")."
                        ".$rs -> form_button("submit", "category_delete", ARLAN_62, "onclick=\"confirm_('sa', '$content_heading', $content_id);\"")."
                        </td>
                        </tr>\n";
                }
                $text .= "</table>";
        }else{
                $text .= "<div style='text-align:center'>".ARLAN_94."</div>";
        }
        $text .= "</div>";
        $ns -> tablerender(ARLAN_93, $text);
}

// ##### Display options --------------------------------------------------------------------------------------------------------------------------------------------------------

function article_adminmenu(){

                global $action,$sql,$sub_action;
                $act=$action;
                if($act==""){$act="main";}
                $var['main']['text']=ARLAN_76;
                $var['main']['link']=e_SELF;

                $var['create']['text']=ARLAN_77;
                $var['create']['link']=e_SELF."?create";

                $var['cat']['text']=ARLAN_78;
                $var['cat']['link']=e_SELF."?cat";

                $var['opt']['text']=ARLAN_60;
                $var['opt']['link']=e_SELF."?opt";
                if($sql -> db_Select("content", "*", "content_type ='15' ")){
                        $var['sa']['text']=ARLAN_93;
                        $var['sa']['link']=e_SELF."?sa";
                }

                show_admin_menu(ARLAN_79,$act,$var);

                                        unset($var);
                                        $var=array();
                                        if($sql -> db_Select("content","content_id,content_heading","content_type='6'")){
                                                 $var['cnone']['text']=ARLAN_75;
                                                 $var['cnone']['link']=e_SELF."?c.none";
                                                 while($row = $sql -> db_Fetch()){
                                                         extract($row);
                                                        $txt = substr($content_heading,0,15);
                                                        if(strlen($content_heading)>15){
                                                                $txt .= " ...";
                                                        }
                                                         $var['c'.$content_id]['text']=$txt;
                                                         $var['c'.$content_id]['link']=e_SELF."?c.{$content_id}";
                                                 }
                                                 show_admin_menu(ARLAN_78,'c'.$sub_action,$var);
                                         }



}
// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------


require_once("footer.php");

function headerjs(){
$script = "<script type=\"text/javascript\">
function addtext2(sc){
        document.getElementById('dataform').category_button.value = sc;
}

</script>\n";

$script .= "<script type=\"text/javascript\">
function confirm_(mode, content_heading, content_id){
        if(mode == 'cat'){
                return confirm(\"".ARLAN_80." [ID \" + content_id + \": \" + content_heading + \"]\");
        }else{
                return confirm(\"".ARLAN_81." [ID \" + content_id + \": \" + content_heading + \"]\");
        }
}
</script>";
return $script;

}
?>