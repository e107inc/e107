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
|     $Source: /cvs_backup/e107_0.7/subcontent.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-10-09 03:32:17 $
|     $Author: chavo $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");

$aj = new textparse;
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."userclass_class.php");
$rs = new form;

if(e_QUERY){
        $tmp = explode(".", e_QUERY);
        $action = $tmp[0];
        $sub_action = $tmp[1];
        $id = $tmp[2];
        unset($tmp);
}
if($action == "article"){
        define("e_PAGETITLE", ARLAN_15.(IsSet($_POST['preview']) ? " / ".ARLAN_28 : ""));
}elseif($action == "review"){
        define("e_PAGETITLE", ARLAN_89);
}elseif($action == ""){
        define("e_PAGETITLE", ARLAN_106);
}

require_once(HEADERF);

if($action == ""){
	$events_installed = ($sql -> db_Select("event", "*", "") && (check_class($pref['eventpost_admin']) || getperms('0')) ? TRUE : FALSE);
	$text = "<div style='text-align:center'><strong>".ARLAN_99."</strong><br/><br/><span class='mediumtext'>".
	  (check_class($pref['subnews_class']) ? "| <a href='".e_BASE."submitnews.php'>".ARLAN_100."</a> " : "").
	  ($events_installed ? "| <a href='".e_PLUGIN."calendar_menu/event.php?ne.1086062400 '>".ARLAN_101."</a> " : "").
	  ($pref['article_submit'] && check_class($pref['article_submit_class']) ? "| <a href='".e_BASE."subcontent.php?article'>".ARLAN_102."</a> " : "").
	  ($pref['review_submit'] && check_class($pref['review_submit_class']) ? "| <a href='".e_BASE."subcontent.php?review'>".ARLAN_103."</a> " : "").
	  ($pref['link_submit'] && check_class($pref['link_submit_class']) ? "| <a href='".e_BASE."links.php?submit'>".ARLAN_104."</a> " : "").
	  ($pref['upload_enabled'] && (!$pref['upload_class'] || check_class($pref['upload_class'])) ? "| <a href='".e_BASE."upload.php'>".ARLAN_105."</a> " : "")."| </span> </div>";
	 $ns -> tablerender(ARLAN_106, $text);
}

if(IsSet($_POST['preview'])){
        $obj = new convert;
        $datestamp = $obj->convert_date(time(), "long");

        $content_heading = $aj -> formtpa($_POST['content_heading']);
        $content_subheading = $aj -> formtpa($_POST['content_subheading']);
        $content_summary = $aj -> formtpa($_POST['content_summary']);
        $data = $aj -> formtpa($_POST['data']);

                if(USER === TRUE)
                {
                        $content_author = USERNAME;
                }
                else
                {
                        if(!$_POST['content_author'] || $_POST['content_author'] == ARLAN_84)
                        {
                                $content_author = "<b>".ARLAN_92."<b>";
                        }
                        else
                        {
                                $content_author = $_POST['content_author'];
                                $content_author_email = $_POST['content_author_email'];
                        }
                }

        $text = "<i>by $content_author</i><br /><span class='smalltext'>".$datestamp."</span><br /><br />Subheading: ".$aj -> tpa($content_subheading)."<br />".ARLAN_19.": ".$aj -> tpa($content_summary)."<br /><br />".$aj -> tpa($data);
        $ns -> tablerender($aj -> tpa($content_heading), $text);
        echo "<br /><br />";

        $content_parent = $_POST['category'];
        if($content_author == "<b>".ARLAN_92."<b>"){ $content_author = ""; }
}

if(IsSet($_POST['create_article'])){
        if(USER){
                $_POST['content_author'] = USERNAME;
                $_POST['content_author_email'] = USEREMAIL;
        }
        if($_POST['data'] && $_POST['content_author'] && $_POST['content_heading']){
                $content_subheading = $aj -> formtpa($_POST['content_subheading'], "admin");
                $content_heading = $aj -> formtpa($_POST['content_heading'], "admin");
                $content_content = $aj -> formtpa($_POST['data'], "admin");
                $content_author = (!$_POST['content_author'] || $_POST['content_author'] == ARLAN_84 ? ADMINID : $_POST['content_author']."^".$_POST['content_author_email']);
                if($content_author == "ADMINID"){
                        $message = ARLAN_90;
                }else{
                        $sql -> db_Insert("content", "0, '$content_heading', '$content_subheading', '$content_content', '".$_POST['category']."', '".time()."', '$content_author', '".$_POST['content_comment']."', '".$_POST['content_summary']."', '15' ,'0' ,0 , 0");
                        unset($content_heading, $content_subheading, $data, $content_summary, $content_author);
                        $message = ARLAN_0;
                }
        }else{
                $message = ARLAN_1;
        }
}

if(IsSet($_POST['create_review'])){
        if(USER){
                $_POST['content_author'] = USERNAME;
                $_POST['content_author_email'] = USEREMAIL;
        }
        if($_POST['data'] && $_POST['content_author'] && $_POST['content_heading']){
                $content_subheading = $aj -> formtpa($_POST['content_subheading'], "admin");
                $content_heading = $aj -> formtpa($_POST['content_heading'], "admin");
                $content_content = $aj -> formtpa($_POST['data'], "admin");
                $content_author = (!$_POST['content_author'] || $_POST['content_author'] == ARLAN_84 ? ADMINID : $_POST['content_author']."^".$_POST['content_author_email']);
                if($content_author == "ADMINID"){
                        $message = ARLAN_90;
                }else{
                         $sql -> db_Insert("content", "0, '".$content_heading."', '".$content_subheading."', '$content_content', '".$_POST['category']."', '".time()."', '".$content_author."', '".$_POST['content_comment']."', '".$_POST['content_summary']."', '16', ".$_POST['content_rating'].", 0, 0");
                        unset($content_heading, $content_subheading, $data, $content_summary);
                        $message = ARLAN_2;
                }
        }else{
                $message = ARLAN_1;
        }
}

if(IsSet($message)){
        $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
        require_once(FOOTERF);
        exit;
}

if($action == "article"){

        if (!($pref['article_submit'])){
                $articlesubmit = ARLAN_95;
        }

        if (!check_class($pref['article_submit_class'])){
                $articlesubmit = ARLAN_97;
        }

        if(IsSet($articlesubmit)){
                $ns -> tablerender("", "<div style='text-align:center'><b>".$articlesubmit."</b></div>");
                require_once(FOOTERF);
                exit;
        }
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
        </tr>";

        if(!USER){

        $text .= "<tr>
        <td style='width:20%; vertical-align:top' class='forumheader3'>".ARLAN_82.":</td>
        <td style='width:80%' class='forumheader3'>
        <input class='tbox' type='text' name='content_author' size='60' value='".($content_author ? $content_author : ARLAN_84)."' maxlength='100' ".($content_author ? "" : "onFocus=\"document.dataform.content_author.value='';\"")." /><br />
        <input class='tbox' type='text' name='content_author_email' size='60' value='".($content_author_email ? $content_author_email : ARLAN_85)."' maxlength='100' ".($content_author_email ? "" : "onFocus=\"document.dataform.content_author_email.value='';\"")." /><br />
        </td>
        </tr>";
        }

        $text .= "<tr>
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
        <textarea class='tbox' name='content_summary' cols='90' rows='5'>$content_summary</textarea>
        </td>
        </tr>

        <tr>
        <td style='width:20%' class='forumheader3'>".ARLAN_20.": </td>
        <td style='width:80%' class='forumheader3'>
        <textarea class='tbox' name='data' cols='90' rows='30'>$data</textarea>
        <br />
        <input class='helpbox' type='text' name='helpb' size='100' />
        <br />";

        require_once(e_HANDLER."ren_help.php");
        $text .= ren_help(1, "addtext2", "help")."
        </td>
        </tr>

        <tr style='vertical-align:top'>
        <td colspan='2'  style='text-align:center' class='forumheader'>".
        (!$_POST['preview'] ? "<input class='button' type='submit' name='preview' value='".ARLAN_28."' />" : "<input class='button' type='submit' name='preview' value='".ARLAN_91."' />")." ".
        ($sub_action == "edit" || $_POST['editp']? "<input class='button' type='submit' name='update_article' value='".ADLAN_81." ".ARLAN_20."' />\n<input type='hidden' name='content_id' value='$id'>" : "<input class='button' type='submit' name='create_article' value='".ARLAN_27."' />")."
        </td>
        </tr>
        </table>
        </form>
        </div>";

        $ns -> tablerender("<div style='text-align:center'>".ARLAN_15."</div>", $text);
}
// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------

if($action == "review"){

        if (!($pref['review_submit'])){
                $reviewsubmit = ARLAN_96;
        }

        if (!check_class($pref['review_submit_class'])){
                $reviewsubmit = ARLAN_98;
        }

        if(IsSet($reviewsubmit)){
                $ns -> tablerender("", "<div style='text-align:center'><b>".$reviewsubmit."</b></div>");
                require_once(FOOTERF);
                exit;
        }
        $text = "<div style='text-align:center'>
        ".$rs -> form_open("post", e_SELF."?create", "dataform")."

        <table style='width:95%' class='fborder'>
        <tr>

        <td style='width:20%; vertical-align:top' class='forumheader3'><span style='text-decoration: underline;'>".ARLAN_74."</span>:</td>
        <td style='width:80%' class='forumheader3'>";

        $sql -> db_Select("content", "*", "content_type=10 ");
        $text .= $rs->form_select_open("category");
        $text .= (!$category ? $rs -> form_option("- ".ARLAN_75." -", 1, -1) : $rs -> form_option("- ".ARLAN_75." -", 0, -1));
        while(list($category_id, $category_name) = $sql-> db_Fetch()){
                $text .= ($category_id == $category ? $rs -> form_option($category_name, 1, $category_id) : $rs -> form_option($category_name, 0, $category_id));
        }
        $text .= $rs -> form_select_close()."
        </td>
        </tr>";

        if(!USER){

        $text .= "<tr>
        <td style='width:20%; vertical-align:top' class='forumheader3'>".ARLAN_82.":</td>
        <td style='width:80%' class='forumheader3'>
        <input class='tbox' type='text' name='content_author' size='60' value='".($content_author ? $content_author : ARLAN_84)."' maxlength='100' ".($content_author ? "" : "onFocus=\"document.dataform.content_author.value='';\"")." /><br />
        <input class='tbox' type='text' name='content_author_email' size='60' value='".($content_author_email ? $content_author_email : ARLAN_85)."' maxlength='100' ".($content_author_email ? "" : "onFocus=\"document.dataform.content_author_email.value='';\"")." /><br />
        </td>
        </tr>";
        }

        $text .= "<tr>
        <td style='width:20%; vertical-align:top' class='forumheader3'><span style='text-decoration: underline;'>".ARLAN_17."</span>:</td>
        <td style='width:80%' class='forumheader3'>
        <input class='tbox' type='text' name='content_heading' size='60' value='$content_heading' maxlength='100' />

        </td>
        </tr>
        <tr>
        <td style='width:20%' class='forumheader3'>".ARLAN_18.":</td>
        <td style='width:80%' class='forumheader3'>
        <input class='tbox' type='text' name='content_subheading' size='60' value='$content_subheading' maxlength='200' />
        </td>
        </tr>

        <tr>
        <td style='width:20%' class='forumheader3'>".ARLAN_19.":</td>
        <td style='width:80%' class='forumheader3'>
        <textarea class='tbox' name='content_summary' cols='70' rows='5'>$content_summary</textarea>
        </td>
        </tr>

        <tr>
        <td style='width:20%' class='forumheader3'><span style='text-decoration: underline;'>".ARLAN_86."</span>: </td>
        <td style='width:80%' class='forumheader3'>
        <textarea class='tbox' name='data' cols='70' rows='30'>$data</textarea>
        <br />
        <input class='helpbox' type='text' name='helpb' size='100' />
        <br />";
        require_once(e_HANDLER."ren_help.php");
        $text .= ren_help(1, "addtext2", "help")."
        </td>
        </tr>

        <tr>
        <td style='width:20%' class='forumheader3'>".ARLAN_87.":</td>
        <td style='width:80%' class='forumheader3'>
        <select name='content_rating' class='tbox'>
        <option value='0'>".ARLAN_88." ...</option>";
        for($a=1; $a<=100; $a++){
                $text .= ($content_rating == $a ? "<option value='$a' selected>$a</option>" : "<option value='$a'>$a</option>");
        }
        $text .= "</select>
        </td>
        </tr>


        <tr style='vertical-align:top'>
        <td colspan='2' style='text-align:center' class='forumheader'>
        <input class='button' type='submit' name='create_review' value='".ARLAN_89."' />

        </td>
        </tr>
        </table>
        </form>
        </div>";

        $ns -> tablerender("<div style='text-align:center'>".ARLAN_89."</div>", $text);

}
require_once(FOOTERF);

function headerjs(){
$script_txt= "<script type=\"text/javascript\">
function addtext2(sc){
        document.getElementById('dataform').data.value += sc;
}
function help(help){
        document.getElementById('dataform').helpb.value = help;
}
</script>\n";
return $script_txt;
}

?>
