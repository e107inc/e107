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
|     $Source: /cvs_backup/e107_0.7/e107_admin/upload.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:21 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("6")){ header("location:".e_BASE."index.php"); exit; }
if(e_QUERY)
{
        $tmp = explode(".", e_QUERY);
        $action = $tmp[0];
        $id = $tmp[1];
}


if($action == "dis")
{
        if($_POST['confirm']){
                $sql -> db_Update("upload", "upload_active='1' WHERE upload_id='$id' ");
                $message = UPLLAN_1;
        } elseif($_POST['cancel'])
        {
                $action = "";
        } else
        {
                $sql -> db_Select("upload", "*", "upload_id='$id'");
                $row = $sql -> db_Fetch(); extract($row);
                require_once(e_HANDLER."form_handler.php");
                $rs = new form;
                include_once("header.php");
                $caption = UPLLAN_17;
                $txt = UPLLAN_45."<br /><br />";
                $txt .= UPLLAN_10.": ".(is_numeric($upload_file) ? "Binary file ID ".$upload_file :  $upload_file);
                $txt .= "<br />".UPLLAN_5.": ".substr($upload_poster, strpos($upload_poster, ".")+1);
                $txt .= "<br /><br />
                ".$rs -> form_open("post", e_SELF."?".e_QUERY)."
                ".$rs -> form_button("submit", "confirm", UPLLAN_46)."
                ".$rs -> form_button("submit", "cancel", UPLLAN_47)."
                ".$rs -> form_close();
                $ns -> tablerender($caption,$txt);
                include_once("footer.php");
                exit;
        }
}

if($action == "dlm"){
        header("location: ".e_ADMIN."download.php?dlm.".$id);
        exit;
}

if($action == "news"){
        header("location: ".e_ADMIN."newspost.php?create.upload.".$id);
        exit;
}


if($action == "dl"){

        $id = str_replace("%20", " ", $id);

        if(preg_match("/Binary\s(.*?)\/.*/", $id, $result)){
                $bid = $result[1];
                $result = @mysql_query("SELECT * FROM ".MPREFIX."rbinary WHERE binary_id='$bid' ");
                $binary_data = @mysql_result($result, 0, "binary_data");
                $binary_filetype = @mysql_result($result, 0, "binary_filetype");
                $binary_name = @mysql_result($result, 0, "binary_name");
                header("Content-type: ".$binary_filetype);
                header("Content-length: ".$download_filesize);
                header("Content-Disposition: attachment; filename=".$binary_name);
                header("Content-Description: PHP Generated Data");
                echo $binary_data;
                exit;
        }else{
                header("location:".e_FILE."public/".str_replace("dl.", "", e_QUERY));
                exit;
        }
}

require_once("auth.php");
require_once(e_HANDLER."userclass_class.php");
$gen = new convert;
require_once(e_HANDLER."form_handler.php");
$rs = new form;

if(IsSet($_POST['optionsubmit'])){

        $pref['upload_storagetype'] = $_POST['upload_storagetype'];
        $pref['upload_maxfilesize'] = $_POST['upload_maxfilesize'];
        $pref['upload_allowedfiletype'] = $_POST['upload_allowedfiletype'];
        $pref['upload_class'] = $_POST['upload_class'];
        $pref['upload_enabled'] = (FILE_UPLOADS ? $_POST['upload_enabled'] : 0);
        if($pref['upload_enabled'] && !$sql -> db_Select("links", "*", "link_url='upload.php' ")){
                $sql -> db_Insert("links", "0, '".UPLLAN_44."', 'upload.php', '', '', 1,0,0,0,0");
        }

        if(!$pref['upload_enabled'] && $sql -> db_Select("links", "*", "link_url='upload.php' ")){
                $sql -> db_Delete("links", "link_url='upload.php' ");
        }

        save_prefs();
        $message = UPLLAN_2;
}

if(IsSet($message)){
        require_once(e_HANDLER."message_handler.php");
        message_handler("ADMIN_MESSAGE", $message);
}

if(!FILE_UPLOADS){
        message_handler("ADMIN_MESSAGE", UPLLAN_41);
}


// view -------------------------------------------------------------------------------------------------------------------------------------------------------------------

if($action == "view"){
        $sql -> db_Select("upload", "*", "upload_id='$id'");
        $row = $sql -> db_Fetch(); extract($row);

        $post_author_id = substr($upload_poster, 0, strpos($upload_poster, "."));
        $post_author_name = substr($upload_poster, (strpos($upload_poster, ".")+1));
        $poster = (!$post_author_id ? "<b>".$post_author_name."</b>" : "<a href='".e_BASE."user.php?id.".$post_author_id."'><b>".$post_author_name."</b></a>");
        $upload_datestamp = $gen->convert_date($upload_datestamp, "long");

        $text = "<div style='text-align:center'>
        <table style='width:85%' class='fborder'>

        <tr>
        <td style='width:30%' class='forumheader3'>".UPLLAN_3."</td>
        <td style='width:70%' class='forumheader3'>$upload_id</td>
        </tr>

        <tr>
        <td style='width:30%' class='forumheader3'>".UPLLAN_4."</td>
        <td style='width:70%' class='forumheader3'>$upload_datestamp</td>
        </tr>

        <tr>
        <td style='width:30%' class='forumheader3'>".UPLLAN_5."</td>
        <td style='width:70%' class='forumheader3'>$poster</td>
        </tr>

        <tr>
        <td style='width:30%' class='forumheader3'>".UPLLAN_6."</td>
        <td style='width:70%' class='forumheader3'><a href='mailto:$upload_email'>$upload_email</td>
        </tr>

        <tr>
        <td style='width:30%' class='forumheader3'>".UPLLAN_7."</td>
        <td style='width:70%' class='forumheader3'>".($upload_website ? "<a href='$upload_website'>$upload_website</a>" : " - ")."</td>
        </tr>

        <tr>
        <td style='width:30%' class='forumheader3'>".UPLLAN_8."</td>
        <td style='width:70%' class='forumheader3'>".($upload_name ? $upload_name: " - ")."</td>
        </tr>

        <tr>
        <td style='width:30%' class='forumheader3'>".UPLLAN_9."</td>
        <td style='width:70%' class='forumheader3'>".($upload_version ? $upload_version : " - ")."</td>
        </tr>

        <tr>
        <td style='width:30%' class='forumheader3'>".UPLLAN_10."</td>
        <td style='width:70%' class='forumheader3'>".(is_numeric($upload_file) ? "Binary file ID ".$upload_file :  "<a href='".e_SELF."?dl.$upload_file'>$upload_file</a>")."</td>
        </tr>

        <tr>
        <td style='width:30%' class='forumheader3'>".UPLLAN_11."</td>
        <td style='width:70%' class='forumheader3'>".parsesize($upload_filesize)."</td>
        </tr>

        <tr>
        <td style='width:30%' class='forumheader3'>".UPLLAN_12."</td>
        <td style='width:70%' class='forumheader3'>".($upload_ss ? "<a href='".e_BASE."request.php?upload.".$upload_id."'>".$upload_ss."</a>" : " - ")."</td>
        </tr>

        <tr>
        <td style='width:30%' class='forumheader3'>".UPLLAN_13."</td>
        <td style='width:70%' class='forumheader3'>$upload_description</td>
        </tr>

        <tr>
        <td style='width:30%' class='forumheader3'>".UPLLAN_14."</td>
        <td style='width:70%' class='forumheader3'>".($upload_demo ? $upload_demo : " - ")."</td>
        </tr>

        <tr>
        <td style='width:30%' class='forumheader3'>".UPLLAN_15."</td>
        <td style='width:70%' class='forumheader3'><a href='".e_SELF."?dlm.$upload_id'>".UPLAN_COPYTODLM."</a> | <a href='".e_SELF."?news.$upload_id'>".UPLLAN_16."</a> | <a href='".e_SELF."?dis.$upload_id'>".UPLLAN_17."</a></td>
        </tr>

        </table>
        </div>";

        $ns -> tablerender(UPLLAN_18, $text);

}


// list -------------------------------------------------------------------------------------------------------------------------------------------------------------------
$imgd = e_BASE.$IMAGES_DIRECTORY;
$text = "<div style='text-align:center'>
<table style='width:85%' class='fborder'>
<tr><td class='forumheader' style='text-align:center' colspan='6'>";

if(!$active_uploads = $sql -> db_Select("upload", "*", "upload_active=0")){
        $text .= UPLLAN_19.".\n</td>\n</tr>";
}else{
        $text .= UPLLAN_20." ".($active_uploads == 1 ? UPLAN_IS : UPLAN_ARE).$active_uploads." ".UPLLAN_21.($active_uploads == 1 ? "" : "s")." ...";

        $text .= "</td></tr>";
        $text .= "<tr>
        <td style='width:5%' class='forumheader3'>".UPLLAN_22."</td>
        <td style='width:10%' class='forumheader3'>".UPLLAN_4."</td>
        <td style='width:20%' class='forumheader3'>".UPLLAN_5."</td>
        <td style='width:20%' class='forumheader3'>".UPLLAN_23."</td>
        <td style='width:30%' class='forumheader3'>".UPLLAN_24."</td>
        <td style='width:18px' class='forumheader3'>".UPLLAN_42."</td>
        </tr>";
        while($row = $sql -> db_Fetch()){
                extract($row);
                $post_author_id = substr($upload_poster, 0, strpos($upload_poster, "."));
                $post_author_name = substr($upload_poster, (strpos($upload_poster, ".")+1));
                $poster = (!$post_author_id ? "<b>".$post_author_name."</b>" : "<a href='".e_BASE."user.php?id.".$post_author_id."'><b>".$post_author_name."</b></a>");
                $upload_datestamp = $gen->convert_date($upload_datestamp, "short");
                $text .= "<tr>
                <td style='width:5%' class='forumheader3'>".$upload_id ."</td>
                <td style='width:20%' class='forumheader3'>".$upload_datestamp."</td>
                <td style='width:20%' class='forumheader3'>".$poster."</td>
                <td style='width:20%' class='forumheader3'><a href='".e_SELF."?view.".$upload_id."'>".$upload_name ."</a></td>
                <td style='width:20%' class='forumheader3'>".$upload_file ."</td>
                <td style='width:18px' class='forumheader3'>
                                <a href='".e_SELF."?dlm.$upload_id'><img src='".$imgd."filemanager/exe.png' alt='".UPLAN_COPYTODLS."' title='".UPLAN_COPYTODLS."' style='border:0' /></a><br />
                                <a href='".e_SELF."?news.$upload_id'><img src='".$imgd."filemanager/htm.png' alt='".UPLLAN_16."' title='".UPLLAN_16."' style='border:0' /></a><br />
                                <a href='".e_SELF."?dis.$upload_id'><img src='".$imgd."filemanager/del.png' alt='".UPLLAN_17."' title='".UPLLAN_17."' style='border:0' /></a>
                </td>
                </tr>";
        }
}
$text .= "</table>\n</div>";

$ns -> tablerender(UPLLAN_43, $text);


// options -------------------------------------------------------------------------------------------------------------------------------------------------------------------

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>
<tr>
<td style='width:50%' class='forumheader3'>".UPLLAN_25."<br />
<span class='smalltext'>".UPLLAN_26."</span></td>
<td style='width:50%' class='forumheader3'>".
($pref['upload_enabled'] == 1 ? $rs -> form_radio("upload_enabled", 1, 1)." ".UPLLAN_27.$rs -> form_radio("upload_enabled", 0)." ".UPLLAN_28 : $rs -> form_radio("upload_enabled", 1)." ".UPLLAN_27.$rs -> form_radio("upload_enabled", 0, 1)." ".UPLLAN_28)."
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".UPLLAN_29."<br />
<span class='smalltext'>".UPLLAN_30."</span></td>
<td style='width:50%' class='forumheader3'>".
($pref['upload_storagetype'] == 1 ? $rs -> form_radio("upload_storagetype", 1, 1)." ".UPLLAN_31."<br />".$rs -> form_radio("upload_storagetype", 2)." ".UPLLAN_32 : $rs -> form_radio("upload_storagetype", 1)." ".UPLLAN_31."<br />".$rs -> form_radio("upload_storagetype", 2,1)." ".UPLLAN_32)."
</td>
</tr>

<tr>
<td style='width:70%' class='forumheader3'>".UPLLAN_33."<br />
<span class='smalltext'>".UPLLAN_34." ".ini_get('upload_max_filesize')." )</span></td>
<td style='width:30%' class='forumheader3'>".
$rs -> form_text("upload_maxfilesize", 10, $pref['upload_maxfilesize'], 10)."
</td>
</tr>

<tr>
<td style='width:70%' class='forumheader3'>".UPLLAN_35."<br />
<span class='smalltext'>".UPLLAN_36."</span></td>
<td style='width:30%' class='forumheader3'>".
$rs -> form_textarea("upload_allowedfiletype", 20, 5, $pref['upload_allowedfiletype'])."
</td>
</tr>

<tr>
<td style='width:70%' class='forumheader3'>".UPLLAN_37."<br />
<span class='smalltext'>".UPLLAN_38."</span></td>
<td style='width:30%' class='forumheader3'>".r_userclass("upload_class",$pref['upload_class'])."

</td>
</tr>

<tr>
<td colspan='2' class='forumheader' style='text-align:center'>".
$rs -> form_button("submit", "optionsubmit", UPLLAN_39)."
</td>
</tr>
</table>".
$rs -> form_close()."
</div>";

$ns -> tablerender(UPLLAN_40, $text);



function parsesize($size){
        $kb = 1024;
        $mb = 1024 * $kb;
        $gb = 1024 * $mb;
        $tb = 1024 * $gb;
        if($size < $kb) {
                return $size." b";
        }else if($size < $mb) {
                return round($size/$kb,2)." kb";
        }else if($size < $gb) {
                return round($size/$mb,2)." mb";
        }else if($size < $tb) {
                return round($size/$gb,2)." gb";
        }else {
                return round($size/$tb,2)." tb";
        }
}


require_once("footer.php");
?>