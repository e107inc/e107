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
|     $Source: /cvs_backup/e107/e107_admin/links.php,v $
|     $Revision: 1.28 $
|     $Date: 2005-01-24 13:46:09 $
|     $Author: mrpete $
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");
if(!getperms("I")){ header("location:".e_BASE."index.php"); }

require_once(e_HANDLER."textparse/basic.php");
$etp = new e107_basicparse;

require_once("auth.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;
$aj = new textparse;
$linkpost = new links;

$deltest = array_flip($_POST);

if(e_QUERY){
        $tmp = explode(".", e_QUERY);
        $action = $tmp[0];
        $sub_action = $tmp[1];
        $id = $tmp[2];
        unset($tmp);
}
if(preg_match("#(.*?)_delete_(\d+)#",$deltest[$etp->unentity(LCLAN_10)],$matches))
{
        $delete = $matches[1];
        $del_id = $matches[2];
}

if(preg_match("#create_sn_(\d+)#",$deltest[$etp->unentity(LCLAN_14)],$matches))
{
        $action='create';
        $sub_action='sn';
        $id=$matches[1];
}

// ##### Main loop -----------------------------------------------------------------------------------------------------------------------

if($action == "dec" && strpos(e_SELF,"links"))
{
        $qs = explode(".", e_QUERY);
        $action = $qs[0];
        $linkid = $qs[1];
        $link_order = $qs[2];
        $location = $qs[3];
        $sql -> db_Update("links", "link_order=link_order-1 WHERE link_order='".($link_order+1)."' AND link_category='$location' ");
        $sql -> db_Update("links", "link_order=link_order+1 WHERE link_id='$linkid' AND link_category='$location' ");
        clear_cache("sitelinks");
        header("location: ".e_ADMIN."links.php?order");
        exit;
}

if($action == "inc" && strpos(e_SELF,"links"))
{
        $qs = explode(".", e_QUERY);
        $action = $qs[0];
        $linkid = $qs[1];
        $link_order = $qs[2];
        $location = $qs[3];
        $sql -> db_Update("links", "link_order=link_order+1 WHERE link_order='".($link_order-1)."' AND link_category='$location' ");
        $sql -> db_Update("links", "link_order=link_order-1 WHERE link_id='$linkid' AND link_category='$location' ");
        clear_cache("sitelinks");
        header("location: ".e_ADMIN."links.php?order");
        exit;
}

if(IsSet($_POST['create_category'])){
        $_POST['link_category_name'] = $aj -> formtpa($_POST['link_category_name'], "admin");
        $sql -> db_Insert("link_category", " '0', '".$_POST['link_category_name']."', '".$_POST['link_category_description']."', '".$_POST['link_category_icon']."'");
        $linkpost -> show_message(LCLAN_51);
}

if(IsSet($_POST['update_category'])){
        $_POST['category_name'] = $aj -> formtpa($_POST['category_name'], "admin");
        $sql -> db_Update("link_category", "link_category_name ='".$_POST['link_category_name']."', link_category_description='".$_POST['link_category_description']."',  link_category_icon='".$_POST['link_category_icon']."' WHERE link_category_id='".$_POST['link_category_id']."'");
        $linkpost -> show_message(LCLAN_52);
}

if(IsSet($_POST['update_order'])){
        extract($_POST);
        while(list($key, $id) = each($link_order)){
                $tmp = explode(".", $id);
                $sql -> db_Update("links", "link_order=".$tmp[1]." WHERE link_id=".$tmp[0]);
        }
        clear_cache("sitelinks");
        $linkpost -> show_message(LCLAN_6);
}

if(IsSet($_POST['updateoptions'])){
        $pref['linkpage_categories'] = $_POST['linkpage_categories'];
        $pref['link_submit'] = $_POST['link_submit'];
        $pref['link_submit_class'] = $_POST['link_submit_class'];
        $pref['linkpage_screentip'] = $_POST['linkpage_screentip'];
        save_prefs();
        $linkpost -> show_message(LCLAN_1);
}

if($action == "order"){
        $linkpost -> set_order();
}

if($delete == 'main')
{
        if($sql -> db_Delete("links", "link_id='$del_id' "))
        {
                clear_cache("sitelinks");
                $linkpost -> show_message(LCLAN_53." #".$del_id." ".LCLAN_54);
        }
}

if($delete == 'category')
{
        if($sql -> db_Delete("link_category", "link_category_id='$del_id' "))
        {
                $linkpost -> show_message(LCLAN_55." #".$del_id." ".LCLAN_54);
                unset($id);
        }
}

if($delete == "sn")
{
        if($sql -> db_Delete("tmp", "tmp_time='$del_id' "))
        {
                $linkpost -> show_message(LCLAN_77);
        }
}

if($action == "sn")
{
        $linkpost -> show_submitted($sub_action, $id);
}


if(IsSet($_POST['add_link'])){
        $linkpost -> submit_link($sub_action, $id);
        unset($id);
}

if($action == "create"){
        $linkpost -> create_link($sub_action, $id);
}

if(!e_QUERY || $action == "main"){
        $linkpost -> show_existing_items();
}

if($action == "cat"){
        $linkpost -> show_categories($sub_action, $id);
}


if($action == "opt"){
        $linkpost -> show_pref_options();
}



//$linkpost -> show_options($action);

require_once("footer.php");
function headerjs(){
global $etp;
$headerjs  = "<script type=\"text/javascript\">
function addtext(sc){
        document.getElementById('linkform').link_button.value = sc;
}
function addtext2(sc){
        document.getElementById('linkform').link_category_icon.value = sc;
}
</script>\n";

$headerjs .= "<script type=\"text/javascript\">
function confirm_(mode, link_id){
        if(mode == 'cat'){
                return confirm(\"".$etp->unentity(LCLAN_56)." [ID: \" + link_id + \"]\");
        }else if(mode == 'sn'){
                return confirm(\"".$etp->unentity(LCLAN_57)." [ID: \" + link_id + \"]\");
        }else{
                return confirm(\"".$etp->unentity(LCLAN_58)." [ID: \" + link_id + \"]\");
        }
}
</script>";
return $headerjs;
}

exit;

// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------


class links{

        function show_existing_items(){
                global $sql;
                if($sql -> db_Select("link_category")){
                        while($row = $sql -> db_Fetch()){
                                extract($row);
                                $cat[$link_category_id] = $link_category_name;
                        }
                }else{
                        $sql -> db_Insert("link_category", "0, 'Main', 'Any links with this category will be displayed in main navigation bar.', '' ");
                        $sql -> db_Insert("link_category", "0, 'Misc', 'Miscellaneous links.', '' ");
                }

                // ##### Display scrolling list of existing links ---------------------------------------------------------------------------------------------------------
                global $sql, $rs, $ns, $aj;
                $text = "<div style='text-align:center'><div style='border : solid 1px #000; padding : 4px; width : auto; height : 400px; overflow : auto; '>";
                if($link_total = $sql -> db_Select("links", "*", "ORDER BY link_category, link_id ASC", "nowhere")){
                        $text .= "<table class='fborder' style='width:100%'>
                        <tr>
                        <td style='width:5%' class='forumheader2'>ID</td>
                        <td style='width:10%' class='forumheader2'>".LCLAN_59."</td>
                        <td style='width:50%' class='forumheader2'>".LCLAN_53."</td>
                        <td style='width:18%' class='forumheader2'>".LCLAN_60."</td>
                        </tr>";
                        while($row = $sql -> db_Fetch()){
                                extract($row);
                                $text .= "<tr>
                                <td style='width:5%' class='forumheader3'>$link_id</td>
                                <td style='width:10%' class='forumheader3'>".$cat[$link_category]."</td>
                                <td style='width:60%' class='forumheader3'><a href='".e_BASE."comment.php?comment.news.$link_id'></a>$link_name<br /><div style='overflow:auto'>({$link_url})</div><span class='smalltext'><i>$link_description</i></span></td>
                                <td style='width:25%; text-align:center' class='forumheader3'>".
                                $rs -> form_open("post", e_SELF,"myform_{$link_id}","",""," onsubmit=\"return confirm_('create',$link_id)\"")."<div>".
                                $rs -> form_button("button", "main_edit_{$link_id}", LCLAN_9, "onclick=\"document.location='".e_SELF."?create.edit.$link_id'\"")."

                                ".$rs -> form_button("submit", "main_delete_{$link_id}", LCLAN_10)."
                                </div>".$rs -> form_close()."

                                </td>
                                </tr>";
                        }
//                                $rs -> form_button("submit", "main_delete_{$link_id}", LCLAN_10, "onclick=\"confirm_('create', $link_id)\"")."
                        $text .= "</table>";
                }else{
                        $text .= "<div style='text-align:center'>".LCLAN_61."</div>";
                }
                $text .= "</div></div>";
                $ns -> tablerender(LCLAN_8, $text);
        }

        function show_options($action){
                global $sql;
                // ##### Display options ---------------------------------------------------------------------------------------------------------
                if($action==""){$action="main";}
                $var['main']['text']=LCLAN_62;
                $var['main']['link']=e_SELF;

                $var['create']['text']=LCLAN_63;
                $var['create']['link']=e_SELF."?create";

                $var['order']['text']=LCLAN_64;
                $var['order']['link']=e_SELF."?order";

                $var['cat']['text']=LCLAN_65;
                $var['cat']['link']=e_SELF."?cat";
                $var['cat']['perm']="8";
                if($sql -> db_Select("tmp", "*", "tmp_ip='submitted_link' ")){
                        $var['sn']['text']=LCLAN_66;
                        $var['sn']['link']=e_SELF."?sn";
                }

                $var['opt']['text']=LCLAN_67;
                $var['opt']['link']=e_SELF."?opt";

                $var['sub']['text']=LCLAN_83;
                $var['sub']['link']="submenusgen.php";

                show_admin_menu(LCLAN_68,$action,$var);
        }

        function show_message($message){
                // ##### Display comfort ---------------------------------------------------------------------------------------------------------
                global $ns;
                $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
                if($message==LCLAN_3){echo "<script>\nfunction zou(){\nwindow.location='links.php';\n}\nsetTimeout(\"zou()\",10);\n</script>";}
        }


        function create_link($sub_action, $id){
                global $sql, $rs, $ns;

                if($sub_action == "edit" && !$_POST['submit']){
                        if($sql -> db_Select("links", "*", "link_id='$id' ")){
                                $row = $sql-> db_Fetch();
                                extract($row);
                        }
                }

                if($sub_action == "sn"){
                        if($sql -> db_Select("tmp", "*", "tmp_time='$id'")){
                                $row = $sql-> db_Fetch();
                                extract($row);
                                $submitted = explode("^", $tmp_info);
                                $link_category = $submitted[0];
                                $link_name = $submitted[1];
                                $link_url = $submitted[2];
                                $link_description = $submitted[3]."\n[i]".LCLAN_82." ".$submitted[5]."[/i]";
                                $link_button = $submitted[4];
                        }
                }



                $handle=opendir(e_IMAGE."link_icons");
                while ($file = readdir($handle)){
                        if($file != "." && $file != ".." && $file != "/"){
                                $iconlist[] = $file;
                        }
                }
                closedir($handle);

                $text = "<div style='text-align:center'>
                <form method='post' action='".e_SELF."?".e_QUERY."' id='linkform'>
                <table style='width:95%' class='fborder'>
                <tr>
                <td style='width:30%' class='forumheader3'>".LCLAN_12.": </td>
                <td style='width:70%' class='forumheader3'>";

                if(!$link_cats = $sql -> db_Select("link_category")){
                        $text .= LCLAN_13."<br />";
                }else{
                        $text .= "
                        <select name='cat_id' class='tbox'>";

                        while(list($cat_id, $cat_name, $cat_description) = $sql-> db_Fetch()){
                                if($link_category == $cat_id || ($cat_id == $linkid && $action == "add")){
                                        $text .= "<option value='$cat_id' selected='selected'>".$cat_name."</option>";
                                }else{
                                        $text .= "<option value='$cat_id'>".$cat_name."</option>";
                                }
                        }
                        $text .= "</select>";
                }
                $text .= "</td></tr><tr>
                <td style='width:30%' class='forumheader3'>".LCLAN_15.": </td>
                <td style='width:70%' class='forumheader3'>
                <input class='tbox' type='text' name='link_name' size='60' value='$link_name' maxlength='100' />
                </td>
                </tr>

                <tr>
                <td style='width:30%' class='forumheader3'>".LCLAN_16.": </td>
                <td style='width:70%' class='forumheader3'>
                <input class='tbox' type='text' name='link_url' size='60' value='$link_url' maxlength='200' />
                </td>
                </tr>

                <tr>
                <td style='width:30%' class='forumheader3'>".LCLAN_17.": </td>
                <td style='width:70%' class='forumheader3'>
                <textarea class='tbox' name='link_description' cols='59' rows='3'>$link_description</textarea>
                </td>
                </tr>

                <tr>
                <td style='width:30%' class='forumheader3'>".LCLAN_18.": </td>
                <td style='width:70%' class='forumheader3'>
                <input class='tbox' type='text' name='link_button' size='60' value='$link_button' maxlength='100' />

                <br />
                <input class='button' type ='button' style='cursor:hand' size='30' value='".LCLAN_39."' onclick='expandit(this)' />
                <div style='display:none;{head}'>";



                while(list($key, $icon) = each($iconlist)){
                        $text .= "<a href='javascript:addtext(\"$icon\")'><img src='".e_IMAGE."link_icons/".$icon."' style='border:0' alt='' /></a> ";
                }

// 0 = same window
// 1 = _blank
// 2 = _parent
// 3 = _top
// 4 = miniwindow

                $text .= "</div></td>
                </tr>
                <tr>
                <td style='width:30%' class='forumheader3'>".LCLAN_19.": </td>
                <td style='width:70%' class='forumheader3'>
                <select name='linkopentype' class='tbox'>".
                ($link_open == 0 ? "<option value='0' selected='selected'>".LCLAN_20."</option>" : "<option value='0'>".LCLAN_20."</option>").
                ($link_open == 1 ? "<option value='1' selected='selected'>".LCLAN_23."</option>" : "<option value='1'>".LCLAN_23."</option>").
                ($link_open == 4 ? "<option value='4' selected='selected'>".LCLAN_24."</option>" : "<option value='4'>".LCLAN_24."</option>")."
                </select>
                </td>
                </tr>
                <tr>
                <td style='width:30%' class='forumheader3'>".LCLAN_25.":<br /><span class='smalltext'>(".LCLAN_26.")</span></td>
                <td style='width:70%' class='forumheader3'>".r_userclass("link_class",$link_class,"off","public,guest,nobody,member,admin,classes")."

                </td></tr>


                <tr style='vertical-align:top'>
                <td colspan='2' style='text-align:center' class='forumheader'>";
                if($id && $sub_action == "edit"){
                        $text .= "<input class='button' type='submit' name='add_link' value='".LCLAN_27."' />\n<input type='hidden' name='link_id' value='$link_id'>";
                }else{
                        $text .= "<input class='button' type='submit' name='add_link' value='".LCLAN_28."' />";
                }
                $text .= "</td>
                </tr>
                </table>
                </form>
                </div>";
                $ns -> tablerender("<div style='text-align:center'>".LCLAN_29."</div>", $text);

        }


        function submit_link($sub_action, $id){
                // ##### Format and submit link ---------------------------------------------------------------------------------------------------------
                global $aj, $sql;
                $link_name = $aj -> formtpa($_POST['link_name'], "admin");
                $link_url = $aj -> formtpa($_POST['link_url'], "admin");
                $link_description = $aj -> formtpa($_POST['link_description'], "admin");
                $link_button = $aj -> formtpa($_POST['link_button'], "admin");

                $link_t = $sql -> db_Count("links", "(*)", "WHERE link_category='".$_POST['cat_id']."'");

                if($id && $sub_action != "sn"){
                        $sql -> db_Update("links", "link_name='$link_name', link_url='$link_url', link_description='$link_description', link_button= '$link_button', link_category='".$_POST['cat_id']."', link_open='".$_POST['linkopentype']."', link_class='".$_POST['link_class']."' WHERE link_id='$id'");
                        clear_cache("sitelinks");
                        $this->show_message(LCLAN_3);
                }else{
                        $sql -> db_Insert("links", "0, '$link_name', '$link_url', '$link_description', '$link_button', '".$_POST['cat_id']."', '".($link_t+1)."', '0', '".$_POST['linkopentype']."', '".$_POST['link_class']."'");
                        clear_cache("sitelinks");
                        $this->show_message(LCLAN_2);
                }
                if($sub_action == "sn"){
                        $sql -> db_Delete("tmp", "tmp_time='$id' ");
                }
        }


        function set_order(){
                global $sql, $ns, $aj;
                $text = "<div style='text-align:center'>
                <form method='post' action='".e_SELF."?order'>
                <table style='width:100%' class='fborder'>";

                $sql -> db_Select("link_category");
                $sql2 = new db;
		$sql3 = new db;
                while(list($link_category_id, $link_category_name, $link_category_description) = $sql-> db_Fetch()){
                        if($lamount = $sql2 -> db_Select("links", "*", "link_category ='$link_category_id' ORDER BY link_order ASC ")){
                                $text .= "<tr><td colspan='3' width='100%' class='forumheader'>$link_category_name ".LCLAN_59."</td></tr>";
                                $local_link_sequence = 1;
                                while(list($link_id, $link_name, $link_url, $link_description, $link_button, $link_category, $link_order, $link_refer) = $sql2-> db_Fetch()){
                                        if ($link_order != $local_link_sequence) {
                                                            $sql3 -> db_Update("links", "link_order=".$local_link_sequence." WHERE link_id=".$link_id);
                                        }
                                        $text .= "<tr>\n<td style='width:30%' class='forumheader3'>".$local_link_sequence." - ".$link_name."</td>\n<td style='width:30%; text-align:center' class='forumheader3'>\n<table><tr><td><select name='link_order[]' class='tbox'>";
                                        for($a=1; $a<= $lamount; $a++){
                                                $text .= "<option value='$link_id.$a' ".
                                                ($local_link_sequence == $a ? "selected='selected'":'').">$a</option>\n";
                                        }

                                        $text .= "</select></td><td>&nbsp;</td><td>";

                                        $text .= "<a href='links.php?inc.".$link_id.".".$local_link_sequence.".".$link_category."' ><img src='".e_IMAGE."generic/up.png' style='border:0px' alt='".LCLAN_30."' title='".LCLAN_30."' /></a>";
                                        $text .= "<br />";
                                        $text .= "<a href='links.php?dec.".$link_id.".".$local_link_sequence.".".$link_category."' ><img src='".e_IMAGE."generic/down.png' style='border:0px' alt='".LCLAN_31."' title='".LCLAN_31."' /></a>";
                                        $text .="</td><td>&nbsp;</td>\n";
                                        $text .="<td><input class='button' type='button' onclick=\"document.location='".e_SELF."?create.edit.$link_id'\" value='".LCLAN_9."' />";
                                        $text .= "</td></tr></table>";

                                        $text .="
                                        </td>
                                        <td style='width:40%' class='forumheader3'>&nbsp;".$aj->tpa($link_description)."</td>
                                        </tr>";
                                        $local_link_sequence++;
                                }
                        }
                }
                $text .= "
                <tr>
                <td colspan='3' width='100%' style='text-align:center' class='forumheader'>
                <input class='button' type='submit' name='update_order' value='".LCLAN_32."' />
                </td>
                </tr>
                </table>
                </form>
                </div>";

                $ns -> tablerender(LCLAN_33, $text);
        }



        function show_categories($sub_action, $id){
                // ##### Display scrolling list of existing categories ---------------------------------------------------------------------------------------------------------
                global $sql, $rs, $ns, $aj;
                $text = "<div style='border : solid 1px #000; padding : 4px; width :auto; height : 200px; overflow : auto; '>\n";
                if($category_total = $sql -> db_Select("link_category")){
                        $text .= "<table class='fborder' style='width:100%'>
                        <tr>
                        <td style='width:5%' class='forumheader2'>&nbsp;</td>
                        <td style='width:75%' class='forumheader2'>".LCLAN_59."</td>
                        <td style='width:20%; text-align:center' class='forumheader2'>".LCLAN_60."</td>
                        </tr>";
                        while($row = $sql -> db_Fetch()){
                                extract($row);

                                $text .= "<tr>
                                <td style='width:5%; text-align:center' class='forumheader3'>".($link_category_icon ? "<img src='".e_IMAGE."link_icons/$link_category_icon' alt='' style='vertical-align:middle' />" : "&nbsp;")."</td>
                                <td style='width:75%' class='forumheader3'>$link_category_name<br /><span class='smalltext'>$link_category_description</span></td>
                                <td style='width:20%; text-align:center' class='forumheader3'>
                                ".$rs -> form_button("submit", "category_edit_{$link_category_id}", LCLAN_9, "onclick=\"document.location='".e_SELF."?cat.edit.$link_category_id'\"")."

                                ".$rs -> form_open("post", e_SELF,"","",""," onsubmit=\"return confirm_('cat',$link_category_id)\"")."
                                ".$rs -> form_button("submit", "category_delete_{$link_category_id}", LCLAN_10)."
                                ".$rs -> form_close()."

                                </td>
                                </tr>\n";
                        }
//                                ".$rs -> form_button("submit", "category_delete_{$link_category_id}", LCLAN_10, "onclick=\"confirm_('cat', '$link_category_id');\"")."
                        $text .= "</table>";
                }else{
                        $text .= "<div style='text-align:center'>".LCLAN_69."</div>";
                }
                $text .= "</div>";
                $ns -> tablerender(LCLAN_70, $text);

                unset($link_category_name, $link_category_description, $link_category_icon);

                $handle=opendir(e_IMAGE."link_icons");
                while ($file = readdir($handle)){
                        if($file != "." && $file != ".."){
                                $iconlist[] = $file;
                        }
                }
                closedir($handle);

                if($sub_action == "edit"){
                        if($sql -> db_Select("link_category", "*", "link_category_id ='$id' ")){
                                $row = $sql -> db_Fetch(); extract($row);
                        }
                }

                $text = "<div style='text-align:center'>
                ".$rs -> form_open("post", e_SELF."?cat", "linkform")."
                <table class='fborder' style='width:auto'>
                <tr>
                <td class='forumheader3' style='width:30%'><span class='defaulttext'>".LCLAN_71."</span></td>
                <td class='forumheader3' style='width:70%'>".$rs -> form_text("link_category_name", 50, $link_category_name, 200)."</td>
                </tr>
                <tr>
                <td class='forumheader3' style='width:30%'><span class='defaulttext'>".LCLAN_72."</span></td>
                <td class='forumheader3' style='width:70%'>".$rs -> form_text("link_category_description", 60, $link_category_description, 200)."</td>
                </tr>
                <tr>
                <td class='forumheader3' style='width:30%'><span class='defaulttext'>".LCLAN_73."</span></td>
                <td class='forumheader3' style='width:70%'>
                ".$rs -> form_text("link_category_icon", 60, $link_category_icon, 100)."
                <br />
                <input class='button' type ='button' style='cursor:hand' size='30' value='".LCLAN_80."' onclick='expandit(this)' />
                <div style='display:none'>";
                while(list($key, $icon) = each($iconlist)){
                        $text .= "<a href='javascript:addtext2(\"$icon\")'><img src='".e_IMAGE."link_icons/".$icon."' style='border:0' alt='' /></a> ";
                }
                $text .= "</div></td>
                </tr>

                <tr><td colspan='2' style='text-align:center' class='forumheader'>";
                if($id){
                        $text .= "<input class='button' type='submit' name='update_category' value='".LCLAN_74."'>
                        ".$rs -> form_button("submit", "category_clear", LCLAN_81).
                        $rs -> form_hidden("link_category_id", $id)."
                        </td></tr>";
                }else{
                        $text .= "<input class='button' type='submit' name='create_category' value='".LCLAN_75."' /></td></tr>";
                }
                $text .= "</table>
                ".$rs -> form_close()."
                </div>";

                $ns -> tablerender(LCLAN_75, $text);
        }


        function show_submitted($sub_action, $id){
                global $sql, $rs, $ns, $aj;
                $text = "<div style='border : solid 1px #000; padding : 4px; width :auto; height : 200px; overflow : auto; '>\n";
                if($submitted_total = $sql -> db_Select("tmp", "*", "tmp_ip='submitted_link' ")){
                        $text .= "<table class='fborder' style='width:100%'>
                        <tr>
                        <td style='width:50%' class='forumheader2'>".LCLAN_53."</td>
                        <td style='width:30%' class='forumheader2'>".LCLAN_45."</td>
                        <td style='width:20%; text-align:center' class='forumheader2'>".LCLAN_60."</td>
                        </tr>";
                        while($row = $sql -> db_Fetch()){
                                extract($row);
                                $submitted = explode("^", $tmp_info);
                                if(!strstr($submitted[2], "http")){ $submitted[2] = "http://".$submitted[2]; }
                                $text .= "<tr>
                                <td style='width:50%' class='forumheader3'><a href='".$submitted[2]."' rel='external'>".$submitted[2]."</a></td>
                                <td style='width:30%' class='forumheader3'>".$submitted[5]."</td>
                                <td style='width:20%; text-align:center; vertical-align:top' class='forumheader3'><div>
                                ".$rs -> form_open("post", e_SELF."?sn","submitted_links")."
                                ".$rs -> form_button("submit", "create_sn_{$tmp_time}", LCLAN_14, "onclick=\"document.location='".e_SELF."?create.sn.$tmp_time'\"")."
                                ".$rs -> form_button("submit", "sn_delete_{$tmp_time}", LCLAN_10, "onclick=\"confirm_('sn', $tmp_time);\"")."
                                </div>".$rs -> form_close()."
                                </td>
                                </tr>\n";
                        }
                        $text .= "</table>";
                }else{
                        $text .= "<div style='text-align:center'>".LCLAN_76."</div>";
                }
                $text .= "</div>";
                $ns -> tablerender(LCLAN_66, $text);
        }

        function show_pref_options(){
                global $pref, $ns;
                $text = "<div style='text-align:center'>
                <form method='post' action='".e_SELF."?".e_QUERY."'>\n
                <table style='width:auto' class='fborder'>
                <tr>
                <td style='width:70%' class='forumheader3'>
                ".LCLAN_40."<br />
                <span class='smalltext'>".LCLAN_34."</span>
                </td>
                <td class='forumheader2' style='width:30%;text-align:center'>".
                ($pref['linkpage_categories'] ? "<input type='checkbox' name='linkpage_categories' value='1' checked='checked' />" : "<input type='checkbox' name='linkpage_categories' value='1' />")."
                </td>
                </tr>

                <tr>
                <td style='width:70%' class='forumheader3'>
                ".LCLAN_78."<br />
                <span class='smalltext'>".LCLAN_79."</span>
                </td>
                <td class='forumheader2' style='width:30%;text-align:center'>".
                ($pref['linkpage_screentip'] ? "<input type='checkbox' name='linkpage_screentip' value='1' checked='checked' />" : "<input type='checkbox' name='linkpage_screentip' value='1' />")."
                </td>
                </tr>

                <tr>
                <td style='width:70%' class='forumheader3'>
                ".LCLAN_41."<br />
                <span class='smalltext'>".LCLAN_42."</span>
                </td>
                <td class='forumheader2' style='width:30%;text-align:center'>".
                ($pref['link_submit'] ? "<input type='checkbox' name='link_submit' value='1' checked='checked' />" : "<input type='checkbox' name='link_submit' value='1' />")."
                </td>
                </tr>

                <tr>
                <td style='width:70%' class='forumheader3'>
                ".LCLAN_43."<br />
                <span class='smalltext'>".LCLAN_44."</span>
                </td>
                <td class='forumheader2' style='width:30%;text-align:center'>".r_userclass("link_submit_class", $pref['link_submit_class'])."</td>
                </tr>

                <tr style='vertical-align:top'>
                <td colspan='2'  style='text-align:center' class='forumheader'>
                <input class='button' type='submit' name='updateoptions' value='".LCLAN_35."' />
                </td>
                </tr>

                </table>
                </form>
                </div>";
                $ns -> tablerender(LCLAN_36, $text);
        }


}

function links_adminmenu(){
        global $linkpost;
        global $action;
        $linkpost -> show_options($action);
}

?>