<?php

if(IsSet($_POST['sub_news'])){
        header("location:submitnews.php");
        exit;
}

if(IsSet($_POST['sub_link'])){
        header("location:links.php?submit");
        exit;
}

if(IsSet($_POST['sub_download'])){
        header("location:upload.php");
        exit;
}

if(IsSet($_POST['sub_article'])){
        header("location:subcontent.php?article");
        exit;
}

if(IsSet($_POST['sub_review'])){
        header("location:subcontent.php?review");
        exit;
}

/*
+---------------------------------------------------------------+
|        e107 website system
|        /usersettings.php
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

if(!USER && !ADMIN){ header("location:".e_BASE."index.php"); exit; }
require_once(e_HANDLER."ren_help.php");

if(e_QUERY && !ADMIN){
        header("location:usersettings.php");
        exit;
}
$aj = new textparse;
$_uid = e_QUERY;

require_once(HEADERF);

if(IsSet($_POST['updatesettings'])){

        if($_POST['password1'] != $_POST['password2']){
                $error .= LAN_105."<br />";
        }

        if(strlen($_POST['password1']) < $pref['signup_pass_len']){
           
                $error .= LAN_SIGNUP_4.$pref['signup_pass_len'].LAN_SIGNUP_5;
                $password1 = "";
                $password2 = "";
        }

        if($_POST['password1'] =="" || $_POST['password2'] == ""){
                $password = $_POST['_pw'];
        }else{
                $password = md5($_POST['password1']);
        }

         if(!preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $_POST['email'])){
                 $error .= LAN_106;
         }

         if (preg_match('#^www\.#si', $_POST['website'])) {
                $_POST['website'] = "http://$homepage";
        }else if (!preg_match('#^[a-z0-9]+://#si', $_POST['website'])){
                $_POST['website'] = "";
    }

        $birthday = $_POST['birth_year']."/".$_POST['birth_month']."/".$_POST['birth_day'];

        if($file_userfile['error'] != 4){
                require_once(e_HANDLER."upload_handler.php");
                require_once(e_HANDLER."resize_handler.php");
                if($uploaded = file_upload(e_FILE."public/avatars/", TRUE)){
                        if($uploaded[0]['name'] && $pref['avatar_upload']){
                                // avatar uploaded
                                $_POST['image'] = "-upload-".$uploaded[0]['name'];
                                resize_image(e_FILE."public/avatars/".$uploaded[0]['name'], e_FILE."public/avatars/".$uploaded[0]['name'], "avatar");
                        }else{
                                // photograph uploaded
                                $user_sess = ($pref['avatar_upload'] ? $uploaded[1]['name'] : $uploaded[0]['name']);
                                resize_image(e_FILE."public/avatars/".$user_sess, e_FILE."public/avatars/".$user_sess, 180);

                        }
                }
        }
        if(!$user_sess){ $user_sess = $_POST['_user_sess']; }
        if(!$error){
                if($_uid && ADMIN){ $inp = $_uid; $remflag = TRUE; }else{ $inp = USERID; }
                $_POST['signature'] = $aj -> formtpa($_POST['signature'], "public");
                $_POST['location'] = $aj -> formtpa($_POST['location'], "public");
                $sql -> db_Update("user", "user_password='$password', user_sess='$user_sess', user_email='".$_POST['email']."', user_homepage='".$_POST['website']."', user_icq='".$_POST['icq']."', user_aim='".$_POST['aim']."', user_msn='".$_POST['msn']."', user_location='".$_POST['location']."', user_birthday='".$birthday."', user_signature='".$_POST['signature']."', user_image='".$_POST['image']."', user_timezone='".$_POST['user_timezone']."', user_hideemail='".$_POST['hideemail']."', user_login='".$_POST['realname']."' WHERE user_id='".$inp."' ");

                if($sql -> db_Select("core", " e107_value", " e107_name='user_entended'")){
                        $row = $sql -> db_Fetch();
                        $user_entended = unserialize($row[0]);
                        $c=0;
                        while(list($key, $u_entended) = each($user_entended)){
                                $val = $aj -> formtpa($_POST[str_replace(" ", "_", $u_entended)], "public");
                                $user_pref[$u_entended] = $val;
                                $c++;
                        }
                        save_prefs("user", $inp);
                }

                if($remflag){
                        header("location:".e_ADMIN."users.php?main.$inp");
                        exit;
                }

                $text = "<div style='text-align:center'>".LAN_150."</div>";
                $ns -> tablerender(LAN_151, $text);
        }
}

if($error){
        $ns -> tablerender("<div style='text-align:center'>".LAN_20."</div>", $error);
}

if($_uid){
        $sql -> db_Select("user", "*", "user_id='".$_uid."' ");
}else{
        $sql -> db_Select("user", "*", "user_id='".USERID."' ");
}
list($user_id, $name, $user_password, $user_sess, $email, $website, $icq, $aim, $msn, $location, $birthday, $signature, $image, $user_timezone, $hideemail, $user_join, $user_lastvisit, $user_currentvisit, $user_lastpost, $user_chats, $user_comments, $user_forums, $user_ip, $user_ban, $user_prefs, $user_new, $user_viewed, $user_visits, $user_admin, $user_login) = $sql -> db_Fetch();

$signature = $aj -> editparse($signature);
$tmp = explode("-", $birthday);
$birth_day = $tmp[2];
$birth_month = $tmp[1];
$birth_year = $tmp[0];

require_once(e_HANDLER."form_handler.php");
$rs = new form;

$text = (e_QUERY ? $rs -> form_open("post", e_SELF."?".$user_id, "dataform", "", " enctype='multipart/form-data'") : $rs -> form_open("post", e_SELF, "dataform", "", " enctype='multipart/form-data'"));

$text .= "<div style='text-align:center'>
<table style='width:auto' class='fborder'>

<tr>
<td colspan='2' class='forumheader'>".LAN_418."</td>
</tr>


<tr>
<td style='width:40%' class='forumheader3'>".LAN_7."</td>
<td style='width:60%' class='forumheader2'>".
$rs -> form_text("name", 20, $name, 100, "tbox", TRUE)
."</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>".LAN_308."</td>
<td style='width:70%' class='forumheader2'>
".$rs -> form_text("realname", 40, $user_login, 100)."
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_152."<br /><span class='smalltext'>".LAN_401."</span></td>
<td style='width:80%' class='forumheader2'>
".$rs -> form_password("password1", 40, "", 20);
if($pref['signup_pass_len']){
$text .= "<br><span class='smalltext'>  (".LAN_SIGNUP_1." {$pref['signup_pass_len']} ".LAN_SIGNUP_2.")</span>";
}
$text .="
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_153."<br /><span class='smalltext'>".LAN_401."</span></td>
<td style='width:80%' class='forumheader2'>
".$rs -> form_password("password2", 40, "", 20)."
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_112."</td>
<td style='width:80%' class='forumheader2'>
".$rs -> form_text("email", 40, $email, 100)."
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_113."<br /><span class='smalltext'>".LAN_114."</span></td>
<td style='width:80%' class='forumheader2'><span class='defaulttext'>".
($hideemail ? $rs ->form_radio("hideemail", 1, 1)." ".LAN_416."&nbsp;&nbsp;".$rs ->form_radio("hideemail", 0)." ".LAN_417 : $rs ->form_radio("hideemail", 1)." ".LAN_416."&nbsp;&nbsp;".$rs ->form_radio("hideemail", 0, 1)." ".LAN_417)."</span>
<br />
</td>
</tr>

<tr>
<td colspan='2' class='forumheader'>".LAN_419."</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_144."</td>
<td style='width:80%' class='forumheader2'>
".$rs -> form_text("website", 60, $website, 150)."
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_115."</td>
<td style='width:80%' class='forumheader2'>
".$rs -> form_text("icq", 20, $icq, 10)."
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_116."</td>
<td style='width:80%' class='forumheader2'>
<input class='tbox' type='text' name='aim' size='30' value='$aim' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_117."</td>
<td style='width:80%' class='forumheader2'>
<input class='tbox' type='text' name='msn' size='30' value='$msn' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_118."</td>
<td style='width:80%' class='forumheader2'>

".$rs -> form_select_open("birth_day").
$rs -> form_option("", 0);
$today = getdate();
$year = $today['year'];
for($a=1; $a<=31; $a++){
        $text .= ($birth_day == $a ? $rs -> form_option($a, 1) : $rs -> form_option($a, 0));
}
$text .= $rs -> form_select_close().
$rs -> form_select_open("birth_month").
$rs -> form_option("", 0);
for($a=1; $a<=12; $a++){
        $text .= ($birth_month == $a ? $rs -> form_option($a, 1, $a) : $rs -> form_option($a, 0, $a));
}
$text .= $rs -> form_select_close().
$rs -> form_select_open("birth_year").
$rs -> form_option("", 0);
for($a=1950; $a<=$year; $a++){
        $text .= ($birth_year == $a ? $rs -> form_option($a, 1) : $rs -> form_option($a, 0));
}

$text .= "</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_119."</td>
<td style='width:80%' class='forumheader2'>
<input class='tbox' type='text' name='location' size='60' value='$location' maxlength='200' />
</td>
</tr>";

if($sql -> db_Select("core", " e107_value", " e107_name='user_entended'")){
        $row = $sql -> db_Fetch();
        $user_entended = unserialize($row[0]);
        $c=0;

        $user_pref = unserialize($user_prefs);

        while(list($key, $u_entended) = each($user_entended)){
                if($u_entended){
                //        $text .= "<tr>
                //        <td style='width:20%' class='forumheader3'>".$u_entended."</td>
                //        <td style='width:80%' class='forumheader3'>
                //        <input class='tbox' type='text' name='".$u_entended."' size='60' value='".$user_pref[$u_entended]."' maxlength='200' />
                //        </td>
               //         </tr>";
                //        $c++;

              require_once(e_HANDLER."user_extended.php");
               $text .= user_extended_edit($u_entended,"forumheader3","left");

                }
        }
}

$text .= "<tr>
<td style='width:20%' style='vertical-align:top' class='forumheader3'>".LAN_120."</td>
<td style='width:80%' class='forumheader2'>
<textarea class='tbox' name='signature' cols='58' rows='4' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>$signature</textarea>
<br />
<input class='helpbox' type='text' name='helpb' size='90' />
<br />
".ren_help()."
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_122."</td>
<td style='width:80%' class='forumheader2'>
<select name='user_timezone' class='tbox'>\n";
timezone();
$count = 0;
while($timezone[$count]){
        if($timezone[$count] == $user_timezone){
                $text .= "<option value='".$timezone[$count]."' selected>(GMT".$timezone[$count].") ".$timearea[$count]."</option>\n";
        }else{
                $text .= "<option value='".$timezone[$count]."'>(GMT".$timezone[$count].") ".$timearea[$count]."</option>\n";
        }
        $count++;
}

$text .= "</select>
</td>
</tr>

<tr>
<td colspan='2' class='forumheader'>".LAN_420."</td>
</tr>

<tr>
<td colspan='2' class='forumheader3' style='text-align:center'>".LAN_404."</td>
</tr>


<tr>
<td style='width:20%; vertical-align:top' class='forumheader3'>".LAN_422."<br /><span class='smalltext'>".LAN_423."</span></td>
<td style='width:80%' class='forumheader2'>
<input class='tbox' type='text' name='image' size='60' value='$image' maxlength='100' />
</td>
</tr>



<tr>
<td style='width:20%; vertical-align:top' class='forumheader3'>".LAN_421."<br /><span class='smalltext'>".LAN_424."</span></td>
<td style='width:80%' class='forumheader2'>
<input class='button' type ='button' style=''width: 35px'; cursor:hand' size='30' value='".LAN_403."' onClick='expandit(this)'>
<div style='display:none' style=&{head};>";
$avatarlist[0] = "";
$handle=opendir(e_IMAGE."avatars/");
while ($file = readdir($handle)){
        if($file != "." && $file != ".." && $file != "index.html" && $file != "CVS"){
                $avatarlist[] = $file;
        }
}
closedir($handle);

for($c=1; $c<=(count($avatarlist)-1); $c++){
        $text .= "<a href='javascript:addtext2(\"$avatarlist[$c]\")'><img src='".e_IMAGE."avatars/".$avatarlist[$c]."' style='border:0' alt='' /></a> ";
}

$text .= "<br />
</div>
</td>
</tr>";

if($pref['avatar_upload'] && FILE_UPLOADS){

        $text .= "<tr>
<td style='width:20%; vertical-align:top' class='forumheader3'>".LAN_415."<br /></td>
<td style='width:80%' class='forumheader2'>
<input class='tbox' name='file_userfile[]' type='file' size='47'>
</td>
</tr>";
}

if($pref['photo_upload'] && FILE_UPLOADS){
        $text .= "<tr>
<td colspan='2' class='forumheader'>".LAN_425."</td>
</tr>

<tr>
<td style='width:20%; vertical-align:top' class='forumheader3'>".LAN_414."<br /><span class='smalltext'>".LAN_426."</span></td>
<td style='width:80%' class='forumheader2'>
<input class='tbox' name='file_userfile[]' type='file' size='47'>
</td>
</tr>";
}

$text .= "</td>
</tr>

<tr>
<td colspan='2' class='forumheader'>".LAN_427."</td>
</tr>

<tr>
<td colspan='2' class='forumheader3' style='text-align:center'>

<input class='button' type='submit' name='sub_news' value='".LAN_428."' />&nbsp;&nbsp;";
if($pref['link_submit'] && check_class($pref['link_submit_class'])){
        $text .= "<input class='button' type='submit' name='sub_link' value='".LAN_429."' />&nbsp;&nbsp;";
}
if($pref['upload_enabled'] && (!$pref['upload_class'] || check_class($pref['upload_class']))){
        $text .= "<input class='button' type='submit' name='sub_download' value='".LAN_430."' />&nbsp;&nbsp;";
}

if($pref['article_submit'] && check_class($pref['article_submit_class'])){
        $text .= "<input class='button' type='submit' name='sub_article' value='".LAN_431."' />&nbsp;&nbsp;";
}
if($pref['review_submit'] && check_class($pref['review_submit_class'])){
        $text .= "<input class='button' type='submit' name='sub_review' value='".LAN_432."' />&nbsp;&nbsp;";
}

$text .= "</td>
</tr>



<tr style='vertical-align:top'>
<td colspan='2' style='text-align:center' class='forumheader'><input class='button' type='submit' name='updatesettings' value='".LAN_154."' /></td>
</tr>
</table>
</div>
<input type='hidden' name='_uid' value='$_uid'>
<input type='hidden' name='_pw' value='$user_password'>
<input type='hidden' name='_user_sess' value='$user_sess'>
</form>
";

$ns -> tablerender(LAN_155, $text);
require_once(FOOTERF);

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

function timezone(){
        /*
        # Render style table
        # - parameters                none
        # - return                                timezone arrays
        # - scope                                        public
        */
        global $timezone, $timearea;
        $timezone = array("-12", "-11", "-10", "-9", "-8", "-7", "-6", "-5", "-4", "-3", "-2", "-1", "GMT", "+1", "+2", "+3", "+4", "+5", "+6", "+7", "+8", "+9", "+10", "+11", "+12", "+13");
        $timearea = array("International DateLine West", "Samoa", "Hawaii", "Alaska", "Pacific Time (US and Canada)", "Mountain Time (US and Canada)", "Central Time (US and Canada), Central America", "Eastern Time (US and Canada)", "Atlantic Time (Canada)", "Greenland, Brasilia, Buenos Aires, Georgetown", "Mid-Atlantic", "Azores", "GMT - UK, Ireland, Lisbon", "West Central Africa, Western Europe", "Greece, Egypt, parts of Africa", "Russia, Baghdad, Kuwait, Nairobi", "Abu Dhabi, Kabul", "Islamabad, Karachi", "Astana, Dhaka", "Bangkok, Rangoon", "Hong Kong, Singapore, Perth, Beijing", "Tokyo, Seoul", "Brisbane, Canberra, Sydney, Melbourne", "Soloman Islands", "New Zealand", "Nuku'alofa");
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

?>
<script type="text/javascript">
function addtext2(sc){
        document.dataform.image.value = sc;
}

</script>