<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /admin/newspost.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("H")){ header("location:".e_BASE."index.php"); exit;}
require_once("auth.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."news_class.php");
require_once(e_HANDLER."ren_help.php");
require_once(e_HANDLER."form_handler.php");
    if($pref['htmlarea']){
    require_once(e_HANDLER."htmlarea/htmlarea.inc.php");
    htmlarea("data");
    htmlarea("news_extended");
    }
$rs = new form;
$aj = new textparse;
$ix = new news;
$newspost = new newspost;

if(e_QUERY){
        list($action, $sub_action, $id, $from) = explode(".", e_QUERY);
        unset($tmp);
}

$from = ($from ? $from : 0);
$amount = 50;

// ##### Main loop -----------------------------------------------------------------------------------------------------------------------

if($action == "main" && $sub_action == "confirm"){
        if($sql -> db_Delete("news", "news_id='$id' ")){
                $newspost -> show_message(NWSLAN_31." #".$id." ".NWSLAN_32);
                clear_cache("news.php");
        }
        unset($sub_action, $id);
}

if($action == "cat" && $sub_action == "confirm"){
        if($sql -> db_Delete("news_category", "category_id='$id' ")){
                $newspost -> show_message(NWSLAN_33." #".$id." ".NWSLAN_32);
                unset($id);
        }
}

if($action == "sn" && $sub_action == "confirm"){
        if($sql -> db_Delete("submitnews", "submitnews_id='$id' ")){
                $newspost -> show_message(NWSLAN_34." #".$id." ".NWSLAN_32);
                unset($id);
        }
}

if(IsSet($_POST['submitupload'])){
        $pref['upload_storagetype'] = "1";
        require_once(e_HANDLER."upload_handler.php");
        $uploaded = file_upload(($_POST['uploadtype'] == NWSLAN_67 ? e_IMAGE."newspost_images/" : e_FILE."downloads/"));
        if($_POST['uploadtype'] == "Image" && $_POST['imagecrethumb']){
                require_once(e_HANDLER."resize_handler.php");
                resize_image(e_IMAGE."newspost_images/".$uploaded[0]['name'], e_IMAGE."newspost_images/".$uploaded[0]['name'], 250, "copy");
        }
}

$_POST['news_title'] = $aj -> formtpa($_POST['news_title']);
if(IsSet($_POST['preview'])){
        $newspost -> preview_item($id);
}

if(IsSet($_POST['submit'])){
        $newspost -> submit_item($sub_action, $id);
        $action = "main";
        unset($sub_action, $id);
}

if(IsSet($_POST['create_category'])){
        if($_POST['category_name']){
                $_POST['category_name'] = $aj -> formtpa($_POST['category_name'], "admin");
                $sql -> db_Insert("news_category", " '0', '".$_POST['category_name']."', '".$_POST['category_button']."'");
                $newspost -> show_message(NWSLAN_35);
        }
}

if(IsSet($_POST['update_category'])){
        if($_POST['category_name']){
                $_POST['category_name'] = $aj -> formtpa($_POST['category_name'], "admin");
                $sql -> db_Update("news_category", "category_name='".$_POST['category_name']."', category_icon='".$_POST['category_button']."' WHERE category_id='".$_POST['category_id']."'");
                $newspost -> show_message(NWSLAN_36);
        }
}

if(IsSet($_POST['save_prefs'])){
        $pref['newsposts'] = $_POST['newsposts'];
        $pref['news_cats'] = $_POST['news_cats'];
        $pref['nbr_cols'] = $_POST['nbr_cols'];
        $pref['subnews_attach'] = $_POST['subnews_attach'];
        $pref['subnews_resize'] = $_POST['subnews_resize'];
        $pref['subnews_class'] = $_POST['subnews_class'];
        $pref['subnews_htmlarea'] = $_POST['subnews_htmlarea'];

        save_prefs();
        $newspost -> show_message("Settings Saved");
}




if(!e_QUERY || $action == "main"){
        $newspost -> show_existing_items($action, $sub_action, $id, $from, $amount);
}

if($action == "create"){
        if($sub_action == "edit" && !$_POST['preview']  && !$_POST['submit']){
                if($sql -> db_Select("news", "*", "news_id='$id' ")){
                        $row = $sql-> db_Fetch();
                        extract($row);
                        $_POST['news_title'] = $news_title;
                        $_POST['data'] = $aj -> formtparev($news_body);
                        $_POST['news_extended'] = $aj -> formtparev($news_extended);
                        $_POST['news_allow_comments'] = $news_allow_comments;
                        $_POST['news_class'] = $news_class;
                        $_POST['cat_id'] = $news_category;
                        if($news_start){$tmp = getdate($news_start);$_POST['startmonth'] = $tmp['mon'];$_POST['startday'] = $tmp['mday'];$_POST['startyear'] = $tmp['year'];}
                        if($news_end){$tmp = getdate($news_end);$_POST['endmonth'] = $tmp['mon'];$_POST['endday'] = $tmp['mday'];$_POST['endyear'] = $tmp['year'];}
                        $_POST['comment_total'] = $sql -> db_Count("comments", "(*)", " WHERE comment_item_id='$news_id' AND comment_type='0' ");
                        $_POST['news_rendertype'] = $news_render_type;
                }
        }
        $newspost -> create_item($sub_action, $id);
}

if($action == "cat"){
        $newspost -> show_categories($sub_action, $id);
}

if($action == "sn"){
        $newspost -> submitted_news($sub_action, $id);
}

if($action == "pref"){
        $newspost -> show_news_prefs($sub_action, $id);
}




// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------

// ##### Display options --------------------------------------------------------------------------------------------------------------------------------------------------------

//$newspost -> show_options($action);

// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------

require_once("footer.php");
?>
<script type="text/javascript">

function addtext3(str){
        document.dataform.category_button.value = str;
}
function help2(help){
	document.dataform.help_ext.value = help;
}
function fclear(){
        document.dataform.data.value = "";
        document.dataform.news_extended.value = "";
}

</script>
<?php
echo "<script type=\"text/javascript\">
function confirm_(mode, news_id){
        if(mode == 'cat'){
                var x=confirm(\"".NWSLAN_37." [ID: \" + news_id + \"]\");
        }else if(mode == 'sn'){
                var x=confirm(\"".NWSLAN_38." [ID: \" + news_id + \"]\");
        }else{
                var x=confirm(\"".NWSLAN_39." [ID: \" + news_id + \"]\");
        }
if(x)
        if(mode == 'cat'){
                window.location='".e_SELF."?cat.confirm.' + news_id;
        }else if(mode == 'sn'){
                window.location='".e_SELF."?sn.confirm.' + news_id;
        }else{
                window.location='".e_SELF."?main.confirm.' + news_id;
        }
}
</script>";

exit;

class newspost{


        function show_existing_items($action, $sub_action, $id, $from, $amount){
                // ##### Display scrolling list of existing news items ---------------------------------------------------------------------------------------------------------
                global $sql, $rs, $ns, $aj;
                $text = "<div style='text-align:center'><div style='border : solid 1px #000; padding : 4px; width : auto; height : 300px; overflow : auto; '>";

                if(IsSet($_POST['searchquery'])){
                        $query = "news_title REGEXP('".$_POST['searchquery']."') OR news_body REGEXP('".$_POST['searchquery']."') OR news_extended REGEXP('".$_POST['searchquery']."') ORDER BY news_datestamp DESC";
                }else{
                        $query = "ORDER BY ".($sub_action ? $sub_action : "news_datestamp")." ".($id ? $id : "DESC")."  LIMIT $from, $amount";
                }

                if($sql -> db_Select("news", "*", $query, ($_POST['searchquery'] ? 0 : "nowhere"))){
                        $text .= "<table class='fborder' style='width:100%'>
                        <tr>

                        <td style='width:5%' class='forumheader2'><a href='".e_SELF."?main.news_id.".($id == "desc" ? "asc" : "desc").".$from'>ID</a></td>
                        <td style='width:5%' class='forumheader2'><a href='".e_SELF."?main.news_title.".($id == "desc" ? "asc" : "desc").".$from'>".NWSLAN_40."</a></td>
                        <td style='width:45%' class='forumheader2'>".NWSLAN_41."</td>
                        </tr>";
                        while($row = $sql -> db_Fetch()){
                                extract($row);
                                $text .= "<tr>
                                <td style='width:5%' class='forumheader3'>$news_id</td>
                                <td style='width:75%' class='forumheader3'><a href='".e_BASE."comment.php?comment.news.$news_id'>".($news_title ? $aj -> tpa($news_title) : "[".NWSLAN_42."]")."</a></td>
                                <td style='width:20%; text-align:center' class='forumheader3'>
                                ".$rs -> form_button("submit", "main_edit", NWSLAN_7, "onClick=\"document.location='".e_SELF."?create.edit.$news_id'\"")."
                                ".$rs -> form_button("submit", "main_delete", NWSLAN_8, "onClick=\"confirm_('create', $news_id)\"")."
                                </td>
                                </tr>";
                        }
                        $text .= "</table>";
                }else{
                        $text .= "<div style='text-align:center'>".NWSLAN_43."</div>";
                }
                $text .= "</div>";

                $newsposts = $sql -> db_Count("news");

                if($newsposts > $amount && !$_POST['searchquery']){
                        $a = $newsposts/$amount;
                        $r = explode(".", $a);
                        if($r[1] != 0 ? $pages = ($r[0]+1) : $pages = $r[0]);
                        if($pages){
                                $current = ($from/$amount)+1;
                                $text .= "<br />".NWSLAN_62." ";
                                for($a=1; $a<=$pages; $a++){
                                        $text .= ($current == $a ? " <b>[$a]</b>" : " [<a href='".e_SELF."?".(e_QUERY ? "$action.$sub_action.$id." : "main.news_datestamp.desc.").(($a-1)*$amount)."'>$a</a>] ");
                                }
                                $text .= "<br />";
                        }
                }

                $text .= "<br /><form method='post' action='".e_SELF."'>\n<p>\n<input class='tbox' type='text' name='searchquery' size='20' value='' maxlength='50' />\n<input class='button' type='submit' name='searchsubmit' value='".NWSLAN_63."' />\n</p>\n</form>\n</div>";



                $ns -> tablerender(NWSLAN_43, $text);
        }

        function show_options($action){
			global $sql;

                if($action==""){$action="main";}
                $var['main']['text']=NWSLAN_44;
                $var['main']['link']=e_SELF;

                $var['create']['text']=NWSLAN_45;
                $var['create']['link']=e_SELF."?create";

                $var['cat']['text']=NWSLAN_46;
                $var['cat']['link']=e_SELF."?cat";
                $var['cat']['perm']="7";

                $var['pref']['text']=NWSLAN_90;
                $var['pref']['link']=e_SELF."?pref";
                $var['pref']['perm']="N";
				if($sql -> db_Select("submitnews", "*", "submitnews_auth ='0' ")){
					$var['sn']['text']=NWSLAN_47;
					$var['sn']['link']=e_SELF."?sn";
					$var['sn']['perm']="N";
				}

                show_admin_menu(NWSLAN_48,$action,$var);


        }

        function create_item($sub_action, $id){
                // ##### Display creation form ---------------------------------------------------------------------------------------------------------
                global $sql, $rs, $ns,$pref;

                $handle=opendir(e_IMAGE."newspost_images");
                while ($file = readdir($handle)){
                        if($file != "." && $file != ".." && $file != "/" && $file != "index.html" && $file != "null.txt" && $file != "CVS"){
                                if(!strstr($file, "thumb_")){
                                        $imagelist[] = $file;
                                }else{
                                        $thumblist[] = $file;
                                }
                        }
                }
                closedir($handle);

                $sql -> db_Select("download");
                $c=0;
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        $filelist[$c][0] = $download_id;
                        $filelist[$c][1] = $download_url;
                        $c++;
                }


                $handle=opendir(e_FILE."downloads");
                while ($file = readdir($handle)){
                        if($file != "." && $file != ".." && $file != "/" && $file != "index.html" && $file != "null.txt" && $file != "CVS"){
                                $filelist[$c][0] = "";
                                $filelist[$c][1] = $file;
                                $c++;
                        }
                }
                closedir($handle);

                if($sub_action == "sn" && !$_POST['preview']){
                        if($sql -> db_Select("submitnews", "*", "submitnews_id=$id", TRUE)){
                                list($id, $submitnews_name, $submitnews_email, $_POST['news_title'], $submitnews_category, $_POST['data'], $submitnews_datestamp, $submitnews_ip, $submitnews_auth, $submitnews_file) = $sql-> db_Fetch();

                                if($pref['htmlarea']){
                                $_POST['data'] .= "<br /><b>".NWSLAN_49." ".$submitnews_name."</b>";
                                $_POST['data'] .= ($submitnews_file)? "<br /><br /><img src='".e_IMAGE."newspost_images/$submitnews_file' style='float:right; margin-left:5px;margin-right:5px;margin-top:5px;margin-bottom:5px; border:1px solid' />":"";
                                }else {
                                $_POST['data'] .= "\n[[b]".NWSLAN_49." ".$submitnews_name."[/b]]";
                                $_POST['data'] .= ($submitnews_file)?"\n\n[img]".e_IMAGE."newspost_images/".$submitnews_file." [/img]":"";
                                }
                                                                $_POST['cat_id'] = $submitnews_category;
                        }
                }

                if($sub_action == "upload" && !$_POST['preview']){
                        if($sql -> db_Select("upload", "*", "upload_id=$id")){
                                $row = $sql -> db_Fetch(); extract($row);

                                $post_author_id = substr($upload_poster, 0, strpos($upload_poster, "."));
                                $post_author_name = substr($upload_poster, (strpos($upload_poster, ".")+1));

                                $_POST['news_title'] = NWSLAN_66.": ".$upload_name;
                                $_POST['data'] = $upload_description."\n[b]".NWSLAN_49." <a href='user.php?id.".$post_author_id."'>".$post_author_name."</a>[/b]\n\n[file=request.php?".$id."]".$upload_name."[/file]\n";
                        }
                }

                $text = "<div style='text-align:center'>
                <form ".(FILE_UPLOADS ? "enctype='multipart/form-data'" : "")." method='post' action='".e_SELF."?".e_QUERY."' name='dataform'>
                <table style='width:95%' class='fborder'>

                <tr>
                <td style='width:20%' class='forumheader3'>".NWSLAN_6.": </td>
                <td style='width:80%' class='forumheader3'>";

                if(!$sql -> db_Select("news_category")){
                        $text .= NWSLAN_10;
                }else{

                        $text .= "
                        <select name='cat_id' class='tbox'>";

                        while(list($cat_id, $cat_name, $cat_icon) = $sql-> db_Fetch()){
                                $text .= ($_POST['cat_id'] == $cat_id ? "<option value='$cat_id' selected>".$cat_name."</option>" : "<option value='$cat_id'>".$cat_name."</option>");
                        }
                        $text .= "</select>";
                }
//                                        $_POST['news_title'] = htmlentities($_POST['news_title'],ENT_QUOTES);
                $text .= "</td>
                </tr>
                <tr>
                <td style='width:20%' class='forumheader3'>".NWSLAN_12.":</td>
                <td style='width:80%' class='forumheader3'>
                <input class='tbox' type='text' name='news_title' size='80' value='".$_POST['news_title']."' maxlength='200' />
                </td>
                </tr>
                <tr>
                <td style='width:20%' class='forumheader3'>".NWSLAN_13.":<br /></td>
                <td style='width:80%' class='forumheader3'>
                <textarea class='tbox' id='data' name='data' cols='80' rows='15' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>".(strstr($_POST['data'], "[img]http") ? $_POST['data'] : str_replace("[img]../", "[img]", $_POST['data']))."</textarea>
                ";
//Main news body textarea
        if(!$pref['htmlarea']){$text .= "
                <input class='helpbox' type='text' name='helpb' size='100' />
                <br />".
        			ren_help()."
                <select class='tbox' name='thumbps' onChange=\"addtext('[link=".e_IMAGE."/newspost_images/' + this.form.thumbps.options[this.form.thumbps.selectedIndex].value + '][img]".e_IMAGE."/newspost_images/thumb_' + this.form.thumbps.options[this.form.thumbps.selectedIndex].value + '[/img][/link]');this.selectedIndex=0;\" onMouseOver=\"help('".NWSLAN_50."')\" onMouseOut=\"help('')\">
                <option>".NWSLAN_80." ...</option>\n";
                while(list($key, $image) = each($thumblist)){
                        $image2 = str_replace("thumb_", "", $image);
                        $text .= "<option value='".$image2."'>thumb_".$image2."</option>\n";
                }
                $text .= "</select>

                <select class='tbox' name='imageps' onChange=\"addtext('[img]' + this.form.imageps.options[this.form.imageps.selectedIndex].value + '[/img]');this.selectedIndex=0;\" onMouseOver=\"help('".NWSLAN_50."')\" onMouseOut=\"help('')\">
                <option>".NWSLAN_81." ...</option>\n";
                while(list($key, $image) = each($imagelist)){
                        $text .= "<option value='".e_IMAGE."/newspost_images/".$image."'>".$image."</option>\n";
                }
                $text .= "</select>

                <select class='tbox' name='fileps' onChange=\"addtext('[file=request.php?' + this.form.fileps.options[this.form.fileps.selectedIndex].value + ']' + this.form.fileps.options[this.form.fileps.selectedIndex].value + '[/file]');this.selectedIndex=0;\" onMouseOver=\"help('".NWSLAN_64."')\" onMouseOut=\"help('')\">
                <option>".NWSLAN_82." ...</option>\n";
                while(list($key, $file) = each($filelist)){
                        $text .= "<option value='".$file[1]."'>".$file[1]."</option>\n";
                }
                $text .= "</select>";

        } // end of htmlarea check.
//Extended news form textarea
                $text .="
                </td>
                </tr>
                <tr>
                <td style='width:20%' class='forumheader3'>".NWSLAN_14.":</td>
                <td style='width:80%' class='forumheader3'>
                <a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>".NWSLAN_83."</a>
                <div style='display: none;'>
                <textarea class='tbox' id='news_extended' name='news_extended' cols='80' rows='15' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>".(strstr($_POST['data'], "[img]http") ? $_POST['data'] : str_replace("[img]../", "[img]", $_POST['news_extended']))."</textarea>
                ";
        if(!$pref['htmlarea']){ $text .="<br />
                <input class='helpbox' type='text' name='help_ext' size='100' />
                <br />
        			".ren_help("","addtext","help2")."
                <select class='tbox' name='imageps2' onChange=\"addtext('[img]' + this.form.imageps2.options[this.form.imageps2.selectedIndex].value + '[/img]');this.selectedIndex=0;\" onMouseOver=\"help2('".NWSLAN_50."')\" onMouseOut=\"help2('')\">
                <option>".NWSLAN_81." ...</option>\n";
                reset($imagelist);
                while(list($key, $image) = each($imagelist)){
                        $text .= "<option value='".e_IMAGE."/newspost_images/".$image."'>".$image."</option>\n";
                }
                $text .= "</select>";
                }
                $text .="
                </div>
                </td>
                </tr>

                <tr>
                <td style='width:20%' class='forumheader3'>".NWSLAN_66."</td>
                <td style='width:80%' class='forumheader3'>
                <a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>".NWSLAN_69."</a>
                <div style='display: none;'>";

                if(!FILE_UPLOADS){
                        $text .= "<b>".NWSLAN_78."</b>";
                }else{

                        if(!is_writable(e_FILE."downloads")){
                                $text .= "<b>".NWSLAN_70."</b><br />";
                        }
                        if(!is_writable(e_IMAGE."newspost_images")){
                                $text .= "<b>".NWSLAN_71."</b><br />";
                        }

                        $text .= "<input class='tbox' type='file' name='file_userfile[]' size='50'>
                        <select class='tbox' name='uploadtype'>
                        <option>".NWSLAN_67."</option>
                        <option>".NWSLAN_68."</option>
                        </select>
                        <br />
                        <input type='checkbox' name='imagecrethumb' value='1'><span class='smalltext'>".NWSLAN_65."</span>&nbsp;&nbsp;
                        <input class='button' type='submit' name='submitupload' value='".NWSLAN_66."' />\n";
                }
                $text .= "</div>
                </td>
                </tr>




                <tr>
                <td style='width:20%' class='forumheader3'>".NWSLAN_15."</td>
                <td style='width:80%' class='forumheader3'>
                <a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>".NWSLAN_18."</a>
                <div style='display: none;'>


                ".
                ($_POST['news_allow_comments'] ? "<input name='news_allow_comments' type='radio' value='0'>".NWSLAN_16."&nbsp;&nbsp;<input name='news_allow_comments' type='radio' value='1' checked>".NWSLAN_17 : "<input name='news_allow_comments' type='radio' value='0' checked>".NWSLAN_16."&nbsp;&nbsp;<input name='news_allow_comments' type='radio' value='1'>".NWSLAN_17)."
                </div>
                 </td>
                </tr>

                <tr>
                <td style='width:20%' class='forumheader3'>".NWSLAN_73.":</td>
                <td style='width:80%' class='forumheader3'>
                <a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>".NWSLAN_74."</a>
                <div style='display: none;'>".

                (!$_POST['news_rendertype'] ? "<input name='news_rendertype' type='radio' value='0' checked />" : "<input name='news_rendertype' type='radio' value='0' />").NWSLAN_75."<br />".
                ($_POST['news_rendertype'] == 1 ? "<input name='news_rendertype' type='radio' value='1' checked />" : "<input name='news_rendertype' type='radio' value='1' />").NWSLAN_76."<br />".
                ($_POST['news_rendertype'] == 2 ? "<input name='news_rendertype' type='radio' value='2' checked />" : "<input name='news_rendertype' type='radio' value='2' />").NWSLAN_77."

                </div>
                </td>
                </tr>


                <tr>
                <td style='width:20%' class='forumheader3'>".NWSLAN_19.":</td>
                <td style='width:80%' class='forumheader3'>

                <a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>".NWSLAN_72."</a>
                <div style='display: none;'>

                <br />


                ".NWSLAN_21.": <select name='startday' class='tbox'><option selected> </option>";
                for($a=1; $a<=31; $a++){
                        $text .= ($a == $_POST['startday'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
                }
                $text .= "</select> <select name='startmonth' class='tbox'><option selected> </option>";
                for($a=1; $a<=12; $a++){
                        $text .= ($a == $_POST['startmonth'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
                }
                $text .= "</select> <select name='startyear' class='tbox'><option selected> </option>";
                for($a=2003; $a<=2010; $a++){
                        $text .= ($a == $_POST['startyear'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
                }
                $text .= "</select> ".NWSLAN_83." <select name='endday' class='tbox'><option selected> </option>";
                for($a=1; $a<=31; $a++){
                        $text .= ($a == $_POST['endday'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
                }
                $text .= "</select> <select name='endmonth' class='tbox'><option selected> </option>";
                for($a=1; $a<=12; $a++){
                        $text .= ($a == $_POST['endmonth'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
                }
                $text .= "</select> <select name='endyear' class='tbox'><option selected> </option>";
                for($a=2003; $a<=2010; $a++){
                        $text .= ($a == $_POST['endyear'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
                }

                $text .= "</select>
                </div>
                </td>
                </tr>

                <tr>
                <td class='forumheader3'>
                ".NWSLAN_22.":
                </td>
                <td class='forumheader3'>

                <a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>".NWSLAN_84."</a>
                <div style='display: none;'>
                ".r_userclass("news_class",$_POST['news_class'])."
                </div>
                </td></tr>

                <tr style='vertical-align: top;'>
                <td colspan='2'  style='text-align:center' class='forumheader'>";

                if(IsSet($_POST['preview'])){
                        $text .= "<input class='button' type='submit' name='preview' value='".NWSLAN_24."' /> ";
                        if($id && $sub_action != "sn" && $sub_action != "upload"){
                                $text .= "<input class='button' type='submit' name='submit' value='".NWSLAN_25."' /> ";
                                                                $text .= "<br /><span class='smalltext'><input type='checkbox' class='tbox' name='update_datestamp' /> ".NWSLAN_105."</span>";
                        } else {
                         $text .= "<input class='button' type='submit' name='submit' value='".NWSLAN_26."' /> ";
                        }
                } else {
                        $text .= "<input class='button' type='submit' name='preview' value='".NWSLAN_27."' />";
                }
                $text .= "\n</td>
                </tr>
                </table>
                <input type='hidden' name='news_id' value='$news_id'>
                </form>
                </div>";
                $ns -> tablerender("<div style='text-align:center'>".NWSLAN_29."</div>", $text);
        }


        function preview_item($id){
                // ##### Display news preview ---------------------------------------------------------------------------------------------------------
                global $aj, $sql, $ix;
                $_POST['news_id'] = $id;
                $_POST['active_start'] = (!$_POST['startmonth'] || !$_POST['startday'] || !$_POST['startyear'] ? 0 : mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
                $_POST['active_end'] = (!$_POST['endmonth'] || !$_POST['endday'] || !$_POST['endyear'] ? 0 : mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));
                $sql -> db_Select("news_category", "*",  "category_id='".$_POST['cat_id']."' ");
                list($_POST['category_id'], $_POST['category_name'], $_POST['category_icon']) = $sql-> db_Fetch();
                $_POST['admin_id'] = USERID;
                $_POST['admin_name'] = USERNAME;
                $_POST['comment_total'] = $comment_total;
                $_POST['news_datestamp'] = time();

                $_POST['data'] = (strstr($_POST['data'], "[img]http") ? $_POST['data'] : str_replace("[img]", "[img]../", $_POST['data']));
                $_POST['data'] = $aj -> formtpa($_POST['data']);
                $_POST['news_extended'] = $aj -> formtpa($_POST['news_extended']);

                $ix -> render_newsitem($_POST);
                $_POST['news_title'] = $aj -> formtpa($_POST['news_title']);
                $_POST['data'] = str_replace("../", "", $aj -> formtparev($_POST['data']));
                $_POST['news_extended'] = $aj -> formtparev($_POST['news_extended']);
        }

        function submit_item($sub_action, $id){
                // ##### Format and submit item ---------------------------------------------------------------------------------------------------------
                global $aj, $ix,$sql;
                $_POST['active_start'] = (!$_POST['startmonth'] || !$_POST['startday'] || !$_POST['startyear'] ? 0 : mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
                $_POST['active_end'] = (!$_POST['endmonth'] || !$_POST['endday'] || !$_POST['endyear'] ? 0 : mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));
                $_POST['admin_id'] = USERID;
                $_POST['admin_name'] = USERNAME;
                $_POST['news_datestamp'] = time();
                if($id && $sub_action != "sn" && $sub_action != "upload"){
                        $_POST['news_id'] = $id;
                 } else {
                         $sql -> db_Update("submitnews", "submitnews_auth='1' WHERE submitnews_id ='".$id."' ");
                 }
                if(!$_POST['cat_id']){ $_POST['cat_id'] = 1; }
                $this->show_message($ix -> submit_item($_POST));
                unset($_POST['news_title'], $_POST['cat_id'], $_POST['data'], $_POST['news_extended'], $_POST['news_allow_comments'], $_POST['startday'], $_POST['startmonth'], $_POST['startyear'], $_POST['endday'], $_POST['endmonth'], $_POST['endyear'], $_POST['news_id'], $_POST['news_class']);
        }

        function show_message($message){
                // ##### Display comfort ---------------------------------------------------------------------------------------------------------
                global $ns;
                $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
        }

        function show_categories($sub_action, $id){
                // ##### Display scrolling list of existing news items ---------------------------------------------------------------------------------------------------------
                global $sql, $rs, $ns, $aj;
                $text = "<div style='border : solid 1px #000; padding : 4px; width :auto; height : 200px; overflow : auto; '>\n";
                if($category_total = $sql -> db_Select("news_category")){
                        $text .= "<table class='fborder' style='width:100%'>
                        <tr>
                        <td style='width:5%' class='forumheader2'>&nbsp;</td>
                        <td style='width:75%' class='forumheader2'>".NWSLAN_6."</td>
                        <td style='width:20%; text-align:center' class='forumheader2'>".NWSLAN_41."</td>
                        </tr>";
                        while($row = $sql -> db_Fetch()){
                                extract($row);

                                if($category_icon){
                                        $icon = (strstr($category_icon, "images/") ? THEME."$category_icon" : e_IMAGE."newsicons/$category_icon");
                                }

                                $text .= "<tr>
                                <td style='width:5%; text-align:center' class='forumheader3'><img src='$icon' alt='' style='vertical-align:middle' /></td>
                                <td style='width:75%' class='forumheader3'>$category_name</td>
                                <td style='width:20%; text-align:center' class='forumheader3'>
                                ".$rs -> form_button("submit", "category_edit", NWSLAN_7, "onClick=\"document.location='".e_SELF."?cat.edit.$category_id'\"")."
                                ".$rs -> form_button("submit", "category_delete", NWSLAN_8, "onClick=\"confirm_('cat', '$category_id');\"")."
                                </td>
                                </tr>\n";
                        }
                        $text .= "</table>";
                }else{
                        $text .= "<div style='text-align:center'><div style='vertical-align:center'>".NWSLAN_10."</div>";
                }
                $text .= "</div>";
                $ns -> tablerender(NWSLAN_51, $text);

                $handle=opendir(e_IMAGE."newsicons");
                while ($file = readdir($handle)){
                        if($file != "." && $file != ".." && $file != "/" && $file != "null.txt" && $file != "CVS"){
                                $iconlist[] = $file;
                        }
                }
                closedir($handle);

                unset($category_name, $category_icon);

                if($sub_action == "edit"){
                        if($sql -> db_Select("news_category", "*", "category_id='$id' ")){
                                $row = $sql -> db_Fetch(); extract($row);
                        }
                }

                $text = "<div style='text-align:center'>
                ".$rs -> form_open("post", e_SELF."?cat", "dataform")."
                <table class='fborder' style='width:auto'>
                <tr>
                <td class='forumheader3' style='width:30%'><span class='defaulttext'>".NWSLAN_52."</span></td>
                <td class='forumheader3' style='width:70%'>".$rs -> form_text("category_name", 30, $category_name, 200)."</td>
                </tr>
                <tr>
                <td class='forumheader3' style='width:30%'><span class='defaulttext'>".NWSLAN_53."</span></td>
                <td class='forumheader3' style='width:70%'>
                ".$rs -> form_text("category_button", 60, $category_icon, 100)."
                <br />
                <input class='button' type ='button' style=''width: 35px'; cursor:hand' size='30' value='".NWSLAN_54."' onClick='expandit(this)'>
                <div style='display:none'>";
                while(list($key, $icon) = each($iconlist)){
                        $text .= "<a href='javascript:addtext3(\"$icon\")'><img src='".e_IMAGE."newsicons/".$icon."' style='border:0' alt='' /></a> ";
                }
                $text .= "</td>
                </tr>

                <tr><td colspan='2' style='text-align:center' class='forumheader'>";
                if($id){
                        $text .= "<input class='button' type='submit' name='update_category' value='".NWSLAN_55."'>
                        ".$rs -> form_button("submit", "category_clear", NWSLAN_79).
                        $rs -> form_hidden("category_id", $id)."
                        </td></tr>";
                }else{
                        $text .= "<input class='button' type='submit' name='create_category' value='".NWSLAN_56."'></td></tr>";
                }
                $text .= "</table>
                ".$rs -> form_close()."
                </div>";

                $ns -> tablerender(NWSLAN_56, $text);
        }

        function show_news_prefs(){
        global $sql, $rs, $ns,$pref;

                $text = "<div style='text-align:center'>
                ".$rs -> form_open("post", e_SELF."?pref", "dataform")."
                <table class='fborder' style='width:94%'>
                <tr>
                <td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_86."</span></td>
                <td class='forumheader3' style='width:40%'>
                <input type='checkbox' name='news_cats' value='1' ".($pref['news_cats']==1 ? " checked" : "").">
                        </td>

                </tr>

                <tr>
                <td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_87."</span></td>
                <td class='forumheader3' style='width:40%'>
                <select class='tbox' name='nbr_cols'>
                <option value='1' ".($pref['nbr_cols']==1 ? "selected" : "").">1</option>
                <option value='2' ".($pref['nbr_cols']==2 ? "selected" : "").">2</option>
                <option value='3' ".($pref['nbr_cols']==3 ? "selected" : "").">3</option>
                <option value='4' ".($pref['nbr_cols']==4 ? "selected" : "").">4</option>
                <option value='5' ".($pref['nbr_cols']==5 ? "selected" : "").">5</option>
                <option value='6' ".($pref['nbr_cols']==6 ? "selected" : "").">6</option>
                </select></td>
                </tr>

                <tr>
                <td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_88."</span></td>
                <td class='forumheader3' style='width:40%'>
                <select class='tbox' name='newsposts'>
                <option value='1' ".($pref['newsposts']==1 ? "selected" : "").">1</option>
                <option value='2' ".($pref['newsposts']==2 ? "selected" : "").">2</option>
                <option value='3' ".($pref['newsposts']==3 ? "selected" : "").">3</option>
                <option value='5' ".($pref['newsposts']==5 ? "selected" : "").">5</option>
                <option value='10' ".($pref['newsposts']==10 ? "selected" : "").">10</option>
                <option value='15' ".($pref['newsposts']==15 ? "selected" : "").">15</option>
                <option value='20' ".($pref['newsposts']==20 ? "selected" : "").">20</option>

                </select></td>
                </tr>";

                require_once(e_HANDLER."userclass_class.php");
                $text .= " <tr>
                <td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_106."</span></td>
                <td class='forumheader3' style='width:40%'>
                ".r_userclass("subnews_class",$pref['subnews_class']).
                "</td></tr>";


                $text .="
                <tr>
                <td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_107."</span></td>
                <td class='forumheader3' style='width:40%'>
                <input type='checkbox' name='subnews_htmlarea' value='1' ".($pref['subnews_htmlarea']==1 ? " checked='checked'" : "")." />
                </td>
                </tr>";


                  $text .="
                <tr>
                <td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_100."</span></td>
                <td class='forumheader3' style='width:40%'>
                <input type='checkbox' name='subnews_attach' value='1' ".($pref['subnews_attach']==1 ? " checked" : "").">
                </td>
                </tr>

                <tr>
                <td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_101."</span></td>
                <td class='forumheader3' style='width:40%'>
                <input class='tbox' type='text' style='width:50px' name='subnews_resize' value='".$pref['subnews_resize']."' >
                <span class='smalltext'>".NWSLAN_102."</span></td>
                </tr>

                <tr><td colspan='2' style='text-align:center' class='forumheader'>";
                $text .= "<input class='button' type='submit' name='save_prefs' value='".NWSLAN_89."'></td></tr>";

                $text .= "</table>
                ".$rs -> form_close()."
                </div>";

                $ns -> tablerender(NWSLAN_90, $text);
        }




        function submitted_news($sub_action, $id){


                global $sql, $rs, $ns, $aj;
                $text = "<div style='border : solid 1px #000; padding : 4px; width :auto; height : 300px; overflow : auto; '>\n";
                if($category_total = $sql -> db_Select("submitnews","*","submitnews_id !='' ORDER BY submitnews_id DESC")){
                        $text .= "<table class='fborder' style='width:100%'>
                        <tr>
                        <td style='width:5%' class='forumheader2'>ID</td>
                        <td style='width:70%' class='forumheader2'>".NWSLAN_57."</td>
                        <td style='width:25%; text-align:center' class='forumheader2'>".NWSLAN_41."</td>
                        </tr>";
                        while($row = $sql -> db_Fetch()){
                                extract($row);
                                $text .= "<tr>
                                <td style='width:5%; text-align:center; vertical-align:top' class='forumheader3'>$submitnews_id</td>
                                <td style='width:70%' class='forumheader3'>";
                                $text .=($submitnews_auth == 0)? "<b>".$aj -> tpa($submitnews_title)."</b>":$aj -> tpa($submitnews_title);
                                $text .=" [ ".NWSLAN_104." $submitnews_name on ".date("D dS M y, g:ia",$submitnews_datestamp)."]<br />".$aj -> tpa($submitnews_item)."</td>
                                <td style='width:25%; text-align:right; vertical-align:top' class='forumheader3'>";
                                $buttext = ($submitnews_auth == 0)? NWSLAN_58 : NWSLAN_103;
                                $text .= $rs -> form_button("submit", "category_edit", $buttext, "onClick=\"document.location='".e_SELF."?create.sn.$submitnews_id'\"")."
                                ".$rs -> form_button("submit", "category_delete", NWSLAN_8, "onClick=\"confirm_('sn', $submitnews_id);\"")."
                                </td>
                                </tr>\n";
                        }
                        $text .= "</table>";
                }else{
                        $text .= "<div style='text-align:center'>".NWSLAN_59."</div>";
                }
                $text .= "</div>";
                $ns -> tablerender(NWSLAN_60, $text);

        }

}

function newspost_adminmenu(){
        global $newspost;
        global $action;
        $newspost -> show_options($action);
}

?>