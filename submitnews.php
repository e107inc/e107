<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /submitnews.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/


require_once("class2.php");
require_once(HEADERF);

if(IsSet($_POST['submit'])){
        if($_POST['item'] == ""){
        $message .= "You must include some text in the News Item";
        $error = TRUE;
        }

        if($_POST['itemtitle'] == ""){
        $message .= "You must include a title";
        $error = TRUE;
        }

                $user = (USER ? USERNAME : $_POST['author_name']);
                $email = (USER ? USEREMAIL : $_POST['author_email']);

                if($user && $email){

                        $ip = getip();
                        $fp = new floodprotect;
                        if($fp -> flood("submitnews", "submitnews_datestamp") == FALSE){
                                header("location:".e_BASE."index.php");
                                exit;
                        }
                        $aj = new textparse;

                        $itemtitle = $aj -> formtpa($_POST['itemtitle'], "public");
                        $item = $aj -> formtpa($_POST['item'], "public");


// Process File Upload    =================================================
if($_FILES['file_userfile']){
        require_once(e_HANDLER."upload_handler.php");
        $uploaded = file_upload(e_IMAGE."newspost_images/");
        $file = $uploaded[0]['name'];
        $filetype = $uploaded[0]['type'];
        $filesize = $uploaded[0]['size'];
        $fileext = substr(strrchr($file, "."), 1);

        if(!$pref['upload_maxfilesize']){
            $pref['upload_maxfilesize'] = ini_get('upload_max_filesize')*1048576;
        }

        if($uploaded && $fileext != "jpg" && $fileext != "gif" && $fileext != "png"){
            $message = "Your attachment must be either a jpg, gif or png file";
            $error = TRUE;
        }

        if($filesize > $pref['upload_maxfilesize']){
            $message = "File too Large";
            $error = TRUE;
        }

        if(!$error){
         //   $numberoffiles = count($uploaded);
            $today = getdate();
            $newname = USERID."_".$today[0]."_".str_replace(" ","_",substr($_POST['itemtitle'],0,6)).".".$fileext;
            if($file && $pref['subnews_resize']){
                require_once(e_HANDLER."resize_handler.php");
                $rezwidth = $pref['subnews_resize'];
                if(!resize_image(e_IMAGE."newspost_images/".$file,e_IMAGE."newspost_images/". $newname,$rezwidth)){
                    rename(e_IMAGE."newspost_images/".$file,e_IMAGE."newspost_images/". $newname);
                }
            }elseif($file){
                rename(e_IMAGE."newspost_images/".$file,e_IMAGE."newspost_images/". $newname);
            }

       }

}

  // ==========================================================
               if($error == FALSE){
                $sql -> db_Insert("submitnews", "0, '$user', '$email', '$itemtitle', '".$_POST['cat_id']."','$item', '".time()."', '$ip', '0', '$newname' ");
                $ns -> tablerender(LAN_133, "<div style='text-align:center'>".LAN_134."</div>");
                require_once(FOOTERF);
                exit;
                }else{
                require_once(e_HANDLER."message_handler.php");
                message_handler("ALERT", "",$message);
                }
        }
}


$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."' enctype='multipart/form-data' >\n
<table style='width:95%' class='fborder'>";
if(!USER){
        $text .= "<tr>\n<td style='width:20%' class='forumheader3'>".LAN_7."</td>\n<td style='width:80%' class='forumheader3'>\n<input class='tbox' type='text' name='author_name' size='60' value='$author_name' maxlength='100' />\n</td>\n</tr>\n<tr>\n<td style='width:20%' class='forumheader3'>".LAN_112."</td>\n<td style='width:80%' class='forumheader3'>\n<input class='tbox' type='text' name='author_email' size='60' value='$author_email' maxlength='100' />\n</td>\n</tr>";
}

$text .= " <tr>
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
                $text .= "</td>
                </tr><tr>
<td style='width:20%' class='forumheader3'>".LAN_62."</td>
<td style='width:80%' class='forumheader3'>
<input class='tbox' type='text' name='itemtitle' size='60' value='$itemtitle' maxlength='200' style='width:90%' />
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_135."</td>
<td style='width:80%' class='forumheader3'>
<textarea class='tbox' name='item' cols='70' rows='10'></textarea>
</td>
</tr>\n";

if($pref['subnews_attach']){
    $text .="
    <tr>
    <td style='width:20%' class='forumheader3'>Image File<br><span class='smalltext'>(jpg, gif or png)</span></td>
    <td style='width:80%' class='forumheader3'>
    <input class='tbox' type='file' name='file_userfile[]' style='width:90%' >
    </td>
    </tr>\n";
}

$text .="



<tr>
<td colspan='2' style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='submit' value='".LAN_136."' />
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender(LAN_136, $text);

require_once(FOOTERF);
?>