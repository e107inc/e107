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
|     $Source: /cvs_backup/e107_0.7/e107_admin/review.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-12-01 14:41:39 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
    if($pref['htmlarea']){
        require_once(e_HANDLER."htmlarea/htmlarea.inc.php");
       $htmlarea_js = htmlarea("data");
    }
if(!getperms("J") && !getperms("K") && !getperms("L")){
        header("location:".e_BASE."index.php");
        exit;
}

require_once(e_HANDLER."textparse/basic.php");
$etp = new e107_basicparse;

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
if(preg_match("#(.*?)_delete_(\d+)#",$deltest[$etp->unentity(REVLAN_9)],$matches))
{
        $delete = $matches[1];
        $del_id = $matches[2];
}

// ##### DB --------------------------------------------------------------------------------------------------------------------------------------------------------------------

if(IsSet($_POST['create_category'])){
        $_POST['category_name'] = $aj -> formtpa($_POST['category_name'], "admin");
        $_POST['category_description'] = $aj -> formtpa($_POST['category_description'], "admin");
        $sql -> db_Insert("content", " '0', '".$_POST['category_name']."', '".$_POST['category_description']."', 0, 0, ".time().", '".ADMINID."', 0, '".$_POST['category_button']."', 10, 0, 0, 0");
        $message = REVLAN_25;
        $e107cache->clear("review");
        $action = "cat";
}

if(IsSet($_POST['update_category'])){
        $_POST['category_name'] = $aj -> formtpa($_POST['category_name'], "admin");
        $_POST['category_description'] = $aj -> formtpa($_POST['category_description'], "admin");
        $sql -> db_Update("content", "content_heading='".$_POST['category_name']."', content_subheading='".$_POST['category_description']."', content_summary='".$_POST['category_button']."' WHERE content_id='".$_POST['category_id']."' ");
        $message = REVLAN_26;
        $e107cache->clear("review");
        $action = "cat";
}

if(IsSet($_POST['create_review'])){
        if($_POST['data'] != ""){
                $content_subheading = $aj -> formtpa($_POST['content_subheading'], "admin");
                $content_heading = $aj -> formtpa($_POST['content_heading'], "admin");
                $content_content = $aj -> formtpa($_POST['data'], "admin");
                $content_author = (!$_POST['content_author'] || $_POST['content_author'] == REVLAN_53 ? ADMINID : $_POST['content_author']."^".$_POST['content_author_email']);
                 $sql -> db_Insert("content", "0, '".$content_heading."', '".$content_subheading."', '$content_content', '".$_POST['category']."', '".time()."', '".$content_author."', '".$_POST['content_comment']."', '".$_POST['content_summary']."', '3', ".$_POST['content_rating'].",".$_POST['add_icons']." ,".$_POST['r_class']);
                unset($content_heading, $content_subheading, $data, $content_summary);
                $message = REVLAN_1;
                $e107cache->clear("review");
        }else{
                $message = REVLAN_2;
        }
        unset($action);
}

If(IsSet($_POST['update_review'])){
        if($_POST['category'] == -1){ unset($_POST['category']); }
        $content_subheading = $aj -> formtpa($_POST['content_subheading'], "admin");
        $content_heading = $aj -> formtpa($_POST['content_heading'], "admin");
        $content_content = $aj -> formtpa($_POST['data'], "admin");
        $content_author = ($_POST['content_author'] && $_POST['content_author'] != ARLAN_84 ? $_POST['content_author']."^".$_POST['content_author_email'] : ADMINID);
        $sql -> db_Update("content", " content_heading='$content_heading', content_subheading='$content_subheading', content_content='$content_content', content_parent='".$_POST['category']."', content_datestamp='".time()."', content_author='$content_author', content_comment='".$_POST['content_comment']."', content_summary='".$_POST['content_summary']."', content_type='3', content_review_score=".$_POST['content_rating'].", content_pe_icon=".$_POST['add_icons'].", content_class='{$_POST['r_class']}' WHERE content_id='".$_POST['content_id']."'");
        unset($action);
        $message = REVLAN_3;
        $e107cache->clear("review");
}

If(IsSet($_POST['sa_article'])){
        if($_POST['category'] == -1){ unset($_POST['category']); }
        $content_subheading = $aj -> formtpa($_POST['content_subheading'], "admin");
        $content_heading = $aj -> formtpa($_POST['content_heading'], "admin");
        $content_content = $aj -> formtpa($_POST['data'], "admin");
        $content_author = ($_POST['content_author'] && $_POST['content_author'] != ARLAN_84 ? $_POST['content_author']."^".$_POST['content_author_email'] : ADMINID);
        $sql -> db_Update("content", " content_heading='$content_heading', content_subheading='$content_subheading', content_content='$content_content', content_parent='".$_POST['category']."', content_author='$content_author', content_comment='".$_POST['content_comment']."', content_summary='".$_POST['content_summary']."',  content_type='3', content_review_score=".$_POST['content_rating'].", content_pe_icon=".$_POST['add_icons'].", content_class='{$_POST['r_class']}' WHERE content_id='".$_POST['content_id']."'");
        unset($action);
        $message = REVLAN_68;
}

if(IsSet($_POST['updateoptions'])){
        $pref['review_submit'] = $_POST['review_submit'];
        $pref['review_submit_class'] = $_POST['review_submit_class'];
        save_prefs();
                if($pref['review_submit'] ){
                        $sql -> db_Update("links", "link_class=".$pref['review_submit_class']." WHERE link_url='subcontent.php?review' ");
                }else{
                        $sql -> db_Update("links", "link_class='255' WHERE link_url='subcontent.php?review' ");
                }
        $message = REVLAN_61;
}

if($delete == 'category')
{
if($sql -> db_Delete("content", "content_id='$del_id' "))
        {
                $message = REVLAN_27;
                unset($sub_action, $id);
                $action = "cat";
        }
}

if($delete == "main")
{
        if($sql -> db_Delete("content", "content_id='$del_id' "))
        {
                $message = REVLAN_4;
                $e107cache->clear("article");
                unset($action, $sub_action, $id);
        }
}

/*
if($action == "cat" && $sub_action == "confirm"){
        if($sql -> db_Delete("content", "content_id='$id' ")){
                $message = REVLAN_27;
                                unset($sub_action,        $id);
                                $action = "cat";
        }
}


if($action == "confirm"){
        if($sql -> db_Delete("content", "content_id='$sub_action' ")){
                $message = REVLAN_4;
                $e107cache->clear("review");
                                unset($action, $sub_action,        $id);
        }
}
*/

// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------


if(IsSet($message)){
        $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}


// ##### Categories ------------------------------------------------------------------------------------------------------------------------------------------------------------

if($action == "cat"){
        $text = "<div style='border : solid 1px #000; padding : 4px; width : auto; height : 100px; overflow : auto; '>\n";
        if($category_total = $sql -> db_Select("content", "*", "content_type='10' ")){
                $text .= "<table class='fborder' style='width:100%'>
                <tr>
                <td style='width:5%' class='forumheader2'>ID</td>
                <td style='width:75%' class='forumheader2'>".REVLAN_28."</td>
                <td style='width:20%; text-align:center' class='forumheader2'>".REVLAN_29."</td>
                </tr>";
                while($row = $sql -> db_Fetch()){
                        extract($row);
                                                $delete_heading = str_replace("&#39;", "\'", $content_heading);
                        $text .= "<tr>
                        <td style='width:5%; text-align:center' class='forumheader3'>".($content_summary ? "<img src='".e_IMAGE."link_icons/$content_summary' alt='' style='vertical-align:middle' />" : "&nbsp;")."</td>
                        <td style='width:75%' class='forumheader3'>$content_heading [$content_subheading]</td>
                        <td style='width:20%; text-align:center' class='forumheader3'>
                        ".$rs -> form_button("submit", "category_edit", REVLAN_30, "onclick=\"document.location='".e_SELF."?cat.edit.$content_id'\"")."
                                                                ".$rs -> form_open("post", e_SELF,"myform_{$content_id}","",""," onsubmit=\"return confirm_('cat','$delete_heading','$content_id')\"")."
                                                                ".$rs -> form_button("submit", "category_delete_{$content_id}", REVLAN_31)."
                                                                ".$rs -> form_close()."
                        </td>
                        </tr>";
                }
                $text .= "
                </table>";
        }else{
                $text .= "<div style='text-align:center'>".REVLAN_32."</div>";
        }
        $text .= "</div>";
        $ns -> tablerender(REVLAN_33, $text);

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
        <td class='forumheader3' style='width:30%'><span class='defaulttext'>".REVLAN_34."</span></td>
        <td class='forumheader3' style='width:70%'>".$rs -> form_text("category_name", 30, $content_heading, 25)."</td>
        </tr>
        <tr>
        <td class='forumheader3' style='width:30%'><span class='defaulttext'>".REVLAN_35."</span></td>
        <td class='forumheader3' style='width:70%'>
        ".$rs -> form_text("category_button", 60, $content_summary, 100)."
        <br />
        <input class='button' type ='button' style='cursor:hand' size='30' value='".REVLAN_36."' onclick='expandit(this)' />
        <div style='display:none;{head} ' >";
        while(list($key, $icon) = each($iconlist)){
                $text .= "<a href='javascript:addtext2(\"$icon\")'><img src='".e_IMAGE."link_icons/".$icon."' style='border:0' alt='' /></a> ";
        }
        $text .= "</div></td>
        </tr>
        <tr>
        <td class='forumheader3' style='width:30%'><span class='defaulttext'>".REVLAN_37."</span></td>
        <td class='forumheader3' style='width:70%'>".$rs->form_textarea("category_description", 59, 3, $content_subheading)."</td>
        </tr>
        <tr><td colspan='2' style='text-align:center' class='forumheader'>";
        if($id){
                $text .= "<input class='button' type='submit' name='update_category' value='".REVLAN_38."' />
                ".$rs -> form_button("submit", "category_clear", REVLAN_69).
                $rs -> form_hidden("category_id", $id)."
                </td></tr>";
        }else{
                $text .= "<input class='button' type='submit' name='create_category' value='".REVLAN_39."' /></td></tr>";
        }
        $text .= "</table>
        ".$rs -> form_close()."
        </div>";

        $ns -> tablerender(REVLAN_39, $text);
}

// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------


// ##### Display scrolling list of existing reviews ----------------------------------------------------------------------------------------------------------------------------
if(!$action || $action == "confirm"){
        $sql2 = new db;
        $text = "<div style='border : solid 1px #000; padding : 4px; width : auto; height : 200px; overflow : auto; '>";
        if($article_total = $sql -> db_Select("content", "*", "content_type='3' ORDER BY content_datestamp DESC")){
                $text .= "<table class='fborder' style='width:100%'>
                <tr>
                <td style='width:5%' class='forumheader2'>&nbsp;</td>
                <td style='width:50%' class='forumheader2'>".REVLAN_15."</td>
                <td style='width:45%' class='forumheader2'>".REVLAN_29."</td>
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
                        <td style='width:75%' class='forumheader3'><a href='".e_BASE."content.php?review.$content_id'>$content_heading</a> [".preg_replace("/-.*-/", "", $content_subheading)."]</td>
                        <td style='width:20%; text-align:center' class='forumheader3'>

                                                                ".$rs -> form_open("post", e_SELF,"myform_{$content_id}","",""," onsubmit=\"return confirm_('create','$delete_heading','$content_id')\"")."
                                                                <div>".$rs -> form_button("button", "main_edit_{$content_id}", REVLAN_30, "onclick=\"document.location='".e_SELF."?create.edit.$content_id'\"")."
                                                                ".$rs -> form_button("submit", "main_delete_{$content_id}", REVLAN_31)."</div>
                                                                ".$rs -> form_close()."

                        </td>
                        </tr>";
                }
// ".$rs -> form_button("submit", "main_edit", REVLAN_30, "onclick=\"document.location='".e_SELF."?create.edit.$content_id'\"")."
// ".$rs -> form_button("submit", "main_delete", REVLAN_31, "onclick=\"confirm_('create', '$delete_heading', $content_id)\"")."
                $text .= "</table>";
        }else{
                $text .= "<div style='text-align:center'>".REVLAN_40."</div>";
        }
        $text .= "</div>";
        $ns -> tablerender(REVLAN_41, $text);
}

// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------


// ##### Display the create review entry screen ------------------------------------------------------------------------------------------------------------------------------


if($action == "create"){
        if($sub_action == "edit"){
                if($sql -> db_Select("content", "*", "content_id='$id' ")){
                        $row = $sql -> db_Fetch(); extract($row);
                        $data = $content_content;
                        $content_rating = $content_review_score;
                        $category = $content_parent;
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
                        $content_rating = $content_review_score;
                }
        }

        require_once(e_HANDLER."userclass_class.php");
        $text = "<div style='text-align:center'>
        ".$rs -> form_open("post", e_SELF."?create", "dataform")."

        <table style='width:95%' class='fborder'>

        <tr>
        <td style='width:20%; vertical-align:top' class='forumheader3'><span style='text-decoration:underline'>".REVLAN_43."</span>:</td>
        <td style='width:80%' class='forumheader3'>";

        $sql -> db_Select("content", "*", "content_type=10 ");
        $text .= $rs->form_select_open("category");
        $text .= (!$category ? $rs -> form_option("- ".REVLAN_44." -", 1, -1) : $rs -> form_option("- ".REVLAN_44." -", 0, -1));
        while(list($category_id, $category_name) = $sql-> db_Fetch()){
                $text .= ($category_id == $category ? $rs -> form_option($category_name, 1, $category_id) : $rs -> form_option($category_name, 0, $category_id));
        }
        $text .= $rs -> form_select_close()."
        </td>
        </tr>

        <tr>
        <td style='width:20%; vertical-align:top' class='forumheader3'>".REVLAN_51.":<br /><span class='smalltext'>(".REVLAN_52.")</span></td>
        <td style='width:80%' class='forumheader3'>
        <a href=\"javascript:void(0);\" onclick=\"expandit(this);\" >".REVLAN_70."</a>\n
        <span style=\"display: none;\" >
                <br /><br />
                <input class='tbox' type='text' name='content_author' size='60' value='".($content_author ? $content_author : REVLAN_53)."' maxlength='100' ".($content_author ? "" : "onfocus=\"document.dataform.content_author.value='';\"")." /><br />
        <input class='tbox' type='text' name='content_author_email' size='60' value='".($content_author_email ? $content_author_email : REVLAN_54)."' maxlength='100' ".($content_author_email ? "" : "onfocus=\"document.dataform.content_author_email.value='';\"")." /><br />
        </span>
                </td>
        </tr>

        <tr>
        <td style='width:20%; vertical-align:top' class='forumheader3'><span style='text-decoration:underline'>".REVLAN_12."</span>:</td>
        <td style='width:80%' class='forumheader3'>
        <input class='tbox' type='text' name='content_heading' size='60' value='$content_heading' maxlength='100' />

        </td>
        </tr>
        <tr>
        <td style='width:20%' class='forumheader3'>".REVLAN_13.":</td>
        <td style='width:80%' class='forumheader3'>
        <input class='tbox' type='text' name='content_subheading' size='60' value='$content_subheading' maxlength='200' />
        </td>
        </tr>

        <tr>
        <td style='width:20%' class='forumheader3'>".REVLAN_14.":</td>
        <td style='width:80%' class='forumheader3'>
        <textarea class='tbox' name='content_summary' cols='70' rows='5'>$content_summary</textarea>
        </td>
        </tr>

        <tr>
        <td style='width:20%' class='forumheader3'><span style='text-decoration:underline'>".REVLAN_15."</span>: </td>
        <td style='width:80%' class='forumheader3'>
        <textarea class='tbox' id='data' name='data' cols='70' rows='30' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>$data</textarea>";

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
        <td style='width:20%' class='forumheader3'>".REVLAN_16.":</td>
        <td style='width:80%' class='forumheader3'>
        <select name='content_rating' class='tbox'>
        <option value='0'>".REVLAN_17." ...</option>";
        for($a=1; $a<=100; $a++){
                $text .= ($content_rating == $a ? "<option value='$a' selected='selected'>$a</option>" : "<option value='$a'>$a</option>");
        }
        $text .= "</select>
        </td>
        </tr>

        <tr>
        <td style='width:20%' class='forumheader3'>".REVLAN_18.":</td>
        <td style='width:80%' class='forumheader3'>
        ";


        if($content_comment == "0"){
                $text .= REVLAN_19.": <input type='radio' name='content_comment' value='1' />
                ".REVLAN_20.": <input type='radio' name='content_comment' value='0' checked='checked' />";
        }else{
                $text .= REVLAN_19.": <input type='radio' name='content_comment' value='1' checked='checked' />
                ".REVLAN_20.": <input type='radio' name='content_comment' value='0' />";
        }

        $text .= "</td></tr>";

        $text.="
        <tr>
        <td  class='forumheader3'>".REVLAN_71.":&nbsp;&nbsp;</td><td class='forumheader3'>".
        ($content_pe_icon ? REVLAN_72.": <input type='radio' name='add_icons' value='1' checked='checked' />".REVLAN_73.": <input type='radio' name='add_icons' value='0' />" : REVLAN_72.": <input type='radio' name='add_icons' value='1' />".REVLAN_73.": <input type='radio' name='add_icons' value='0' checked='checked' />")."
        </td>
        </tr>

        <tr><td style='width:20%' class='forumheader3'>".REVLAN_21.":</td>
        <td style='width:80%' class='forumheader3'>".r_userclass("r_class",$content_class)."
        ";

        $text.="</td></tr>
        <tr style='vertical-align:top'>
        <td colspan='2' style='text-align:center' class='forumheader'>
        ";


        if($sub_action == "edit"){
                $text .= "<input class='button' type='submit' name='update_review' value='".REVLAN_22."' />
                <input type='hidden' name='content_id' value='$id'>";
        }else if($sub_action == "sa"){
                $text .= "<input class='button' type='submit' name='sa_article' value='".REVLAN_67."' />
                <input type='hidden' name='content_id' value='$id'>";
        }else{
                $text .= "<input class='button' type='submit' name='create_review' value='".REVLAN_23."' />";
        }

        $text .= "</td>
        </tr>
        </table>
        </form>
        </div>";

        $ns -> tablerender("<div style='text-align:center'>".REVLAN_24."</div>", $text);

}


// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------

if($action == "opt"){
        global $pref, $ns;
        $text = "<div style='text-align:center'>
        <form method='post' action='".e_SELF."?".e_QUERY."'>\n
        <table style='width:auto' class='fborder'>
        <tr>

        <td style='width:70%' class='forumheader3'>
        ".REVLAN_55."<br />
        <span class='smalltext'>".REVLAN_56."</span>
        </td>
        <td class='forumheader2' style='width:30%;text-align:center'>".
        ($pref['review_submit'] ? "<input type='checkbox' name='review_submit' value='1' checked='checked' />" : "<input type='checkbox' name='review_submit' value='1' />")."
        </td>
        </tr>

        <tr>
        <td style='width:70%' class='forumheader3'>
        ".REVLAN_57."<br />
        <span class='smalltext'>".REVLAN_58."</span>
        </td>
        <td class='forumheader2' style='width:30%;text-align:center'>".r_userclass("review_submit_class", $pref['review_submit_class'])."</td>
        </tr>

        <tr style='vertical-align:top'>
        <td colspan='2'  style='text-align:center' class='forumheader'>
        <input class='button' type='submit' name='updateoptions' value='".REVLAN_59."' />
        </td>
        </tr>

        </table>
        </form>
        </div>";
        $ns -> tablerender(REVLAN_60, $text);
}


if($action == "sa"){
        global $sql, $rs, $ns, $aj;
        $text = "<div style='border : solid 1px #000; padding : 4px; width :auto; height : 200px; overflow : auto; '>\n";
        if($article_total = $sql -> db_Select("content", "*", "content_type=16")){
                $text .= "<table class='fborder' style='width:100%'>
                <tr>
                <td style='width:5%' class='forumheader2'>ID</td>
                <td style='width:75%' class='forumheader2'>".REVLAN_65."</td>
                <td style='width:20%; text-align:center' class='forumheader2'>".REVLAN_29."</td>
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
                        ".$rs -> form_button("submit", "category_edit", REVLAN_66, "onclick=\"document.location='".e_SELF."?create.sa.$content_id'\"")."
                        ".$rs -> form_button("submit", "category_delete", REVLAN_9, "onclick=\"confirm_('sa', $content_id);\"")."
                        </td>
                        </tr>\n";
                }
                $text .= "</table>";
        }else{
                $text .= "<div style='text-align:center'>".REVLAN_63."</div>";
        }
        $text .= "</div>";
        $ns -> tablerender(REVLAN_62, $text);
}

// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------

function review_adminmenu(){

                global $action,$sql;
                $act=$action;
                if($act==""){$act="main";}
                $var['main']['text']=REVLAN_45;
                $var['main']['link']=e_SELF;

                $var['create']['text']=REVLAN_46;
                $var['create']['link']=e_SELF."?create";

                $var['cat']['text']=REVLAN_47;
                $var['cat']['link']=e_SELF."?cat";

                $var['opt']['text']=REVLAN_29;
                $var['opt']['link']=e_SELF."?opt";
                if($sql -> db_Select("content", "*", "content_type ='16' ")){
                        $var['sa']['text']=REVLAN_62;
                        $var['sa']['link']=e_SELF."?sa";
                }

                show_admin_menu(REVLAN_48,$act,$var);
}

require_once("footer.php");

function headerjs(){
global $etp;
$script = "<script type=\"text/javascript\">
function addtext2(sc){
        document.getElementById('dataform').category_button.value = sc;
}
</script>\n";




$script .= "<script type=\"text/javascript\">
function confirm_(mode, content_heading, content_id){
        if(mode == 'cat'){
                return confirm(\"".$etp->unentity(REVLAN_49)." [ID \" + content_id + \": \" + content_heading + \"]\");
        }else{
                return confirm(\"".$etp->unentity(REVLAN_50)." [ID \" + content_id + \": \" + content_heading + \"]\");
        }
}
</script>";
return $script;
}
?>