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
|     $Source: /cvs_backup/e107_0.7/usersettings.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-01-07 10:57:26 $
|     $Author: pholzmann $
+----------------------------------------------------------------------------+
*/


require_once("class2.php");

if(IsSet($_POST['sub_news'])){
        header("location:".e_BASE."submitnews.php");
        exit;
}

if(IsSet($_POST['sub_link'])){
        header("location:".e_BASE."links.php?submit");
        exit;
}

if(IsSet($_POST['sub_download'])){
        header("location:".e_BASE."upload.php");
        exit;
}

if(IsSet($_POST['sub_article'])){
        header("location:".e_BASE."subcontent.php?article");
        exit;
}

if(IsSet($_POST['sub_review'])){
        header("location:".e_BASE."subcontent.php?review");
        exit;
}
if(!USER && !ADMIN){ header("location:".e_BASE."index.php"); exit; }
require_once(e_HANDLER."ren_help.php");

if(e_QUERY && !ADMIN){
        header("location:".e_BASE."usersettings.php");
        exit;
}
$aj = new textparse;
$_uid = e_QUERY;

require_once(HEADERF);

if(IsSet($_POST['updatesettings'])){

        // check prefs for required fields =================================.
    $signupval = explode(".",$pref['signup_options']);
    $signup_title = array(LAN_308,LAN_144,LAN_115,LAN_116,LAN_117,LAN_118,LAN_119,LAN_120,LAN_121,LAN_122);
    $signup_name = array("realname","website","icq","aim","msn","birth_year","location","signature","image","user_timezone");

        if($_POST['image'] && $size = getimagesize($_POST['image'])){
                $avwidth = $size[0];
                $avheight = $size[1];
                $avmsg="";

                $pref['im_width'] = ($pref['im_width']) ? $pref['im_width'] : 120;
                $pref['im_height'] = ($pref['im_height']) ? $pref['im_height'] : 100;
                if($avwidth > $pref['im_width']){
                        $avmsg .= LAN_USET_1."<br />".LAN_USET_2.": {$pref['im_width']}<br /><br />";
                }
                if($avheight > $pref['im_height']){
                        $avmsg .= LAN_USET_3."<br />".LAN_USET_4.": {$pref['im_height']}";
                }
                if($avmsg){
                        $_POST['image']="";
                        $ns -> tablerender(" ",$avmsg);
                }
        }

        for ($i=0; $i<count($signup_title); $i++) {
                $postvalue = $signup_name[$i];
                if($signupval[$i]==2 && $_POST[$postvalue] == ""){
                    $error .= LAN_SIGNUP_6."<b>".$signup_title[$i]."</b>".LAN_SIGNUP_7."<br />";
                }
        };


	if(($user_entended = $sysprefs->getArray('user_entended'))){
                $c=0;
                while(list($key, $u_entended) = each($user_entended)){
                    if($u_entended){

                        if($_POST[str_replace(" ", "_", $u_entended)] === "" && $pref['signup_ext'.$key] == 2){
                        $ut = explode("|",$u_entended);
                        $u_name = ($ut[0] != "") ? trim($ut[0]) : trim($u_entended);
                        $error_ext = LAN_SIGNUP_6."<b>".$u_name."</b>".LAN_SIGNUP_7;
                        $error .= $error_ext."<br />";
                        }

                    }
                }
        }

        // ====================================================================

        if($_POST['password1'] != $_POST['password2']){
                $error .= LAN_105."<br />";
        }

        if(strlen($_POST['password1']) < $pref['signup_pass_len'] && $_POST['password1'] !=""){

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


        if($sql -> db_Select("user", "user_email", "user_email='".$_POST['email']." AND user_id!=".USERID."' ")){
                message_handler("P_ALERT", LAN_408);
                $error = TRUE;
        }


	if(($user_entended = $sysprefs->getArray('user_entended'))){
                        $c=0;
                        while(list($key, $u_entended) = each($user_entended)){
                                if($u_entended){
                                        if($pref['signup_ext'.$key] ==2 && $_POST["ue_{$key}"] == ""){
                                                $ut = explode("|",$u_entended);
                                                $u_name = ($ut[0] != "") ? trim($ut[0]) : trim($u_entended);
                                                $error_ext = LAN_SIGNUP_6.$u_name.LAN_SIGNUP_7;
                                                message_handler("P_ALERT", $error_ext);
                                                $error = TRUE;
                                        }

                                }
                        }
                }

         if (preg_match('#^www\.#si', $_POST['website'])) {
                $_POST['website'] = "http://$homepage";
        }else if (!preg_match('#^[a-z0-9]+://#si', $_POST['website'])){
                $_POST['website'] = "";
    }

        if($_POST['icq'] && !is_numeric($_POST['icq'])){
                $error = LAN_ICQNUMBER;
                $_POST['icq'] = "";
        }

        $birthday = $_POST['birth_year']."/".$_POST['birth_month']."/".$_POST['birth_day'];
        if($file_userfile['error'] != 4){
                require_once(e_HANDLER."upload_handler.php");
                require_once(e_HANDLER."resize_handler.php");
                if($uploaded = file_upload(e_FILE."public/avatars/", "avatar")){
                        if($uploaded[0]['name'] && $pref['avatar_upload']){
                                // avatar uploaded
                                $_POST['image'] = "-upload-".$uploaded[0]['name'];
                                if(!resize_image(e_FILE."public/avatars/".$uploaded[0]['name'], e_FILE."public/avatars/".$uploaded[0]['name'], "avatar")){
                                                                        unset($message);
                                                                        $error = RESIZE_NOT_SUPPORTED;
                                                                        @unlink(e_FILE."public/avatars/".$uploaded[0]['name']);
                                                                }
                        }
                        if($uploaded[1]['name'] || (!$pref['avatar_upload'] && $uploaded[0]['name'])) {
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
                $_POST['website'] = $aj -> formtpa($_POST['website'], "public");
                $_POST['msn'] = $aj -> formtpa($_POST['msn'], "public");
                $_POST['aim'] = $aj -> formtpa($_POST['aim'], "public");
                $_POST['realname'] = $aj -> formtpa($_POST['realname'], "public");
                $_POST['customtitle'] = $aj -> formtpa($_POST['customtitle'], "public");
                $sql -> db_Update("user", "user_password='$password', user_sess='$user_sess', user_email='".$_POST['email']."', user_homepage='".$_POST['website']."', user_icq='".$_POST['icq']."', user_aim='".$_POST['aim']."', user_msn='".$_POST['msn']."', user_location='".$_POST['location']."', user_birthday='".$birthday."', user_signature='".$_POST['signature']."', user_image='".$_POST['image']."', user_timezone='".$_POST['user_timezone']."', user_hideemail='".$_POST['hideemail']."', user_login='".$_POST['realname']."', user_customtitle='".$_POST['customtitle']."' WHERE user_id='".$inp."' ");

		if(($user_entended = $sysprefs->getArray('user_entended'))){
                        while(list($key, $u_entended) = each($user_entended)){
                                if($_POST["ue_{$key}"]){
                                $val = $aj -> formtpa($_POST["ue_{$key}"], "public");
                                $user_pref["ue_{$key}"] = $val;
                                } else {
                                        unset($user_pref["ue_{$key}"]);
                                }
                        }
                        save_prefs("user", $inp);
                }


  // Update Userclass =======


                if($_POST['usrclass']){
                   unset($insert_class);
                   sort($_POST['usrclass']);
                   foreach($_POST['usrclass'] as $value){
                   $insert_class .= $value.".";

                   }
                    $sql -> db_Update("user", "user_class='$insert_class' WHERE user_id='".USERID."' ");

                }
          // =======================

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
list($user_id, $name, $user_customtitle, $user_password, $user_sess, $email, $website, $icq, $aim, $msn, $location, $birthday, $signature, $image, $user_timezone, $hideemail, $user_join, $user_lastvisit, $user_currentvisit, $user_lastpost, $user_chats, $user_comments, $user_forums, $user_ip, $user_ban, $user_prefs, $user_new, $user_viewed, $user_visits, $user_admin, $user_login) = $sql -> db_Fetch();

$signature = $aj -> editparse($signature);
$tmp = explode("-", $birthday);
$user_customtitle = ($_POST['customtitle'])? $_POST['customtitle']:$user_customtitle;
$birth_day = ($_POST['birth_day'])? $_POST['birth_day']:$tmp[2];
$birth_month = ($_POST['birth_month'])? $_POST['birth_month']:$tmp[1];
$birth_year = ($_POST['birth_year'])? $_POST['birth_year']:$tmp[0];
$user_login = ($_POST['realname'])? $_POST['realname']:$user_login;
$location = ($_POST['location'])? $_POST['location']:$location;
$icq = ($_POST['icq'])? $_POST['icq']:$icq;
$msn = ($_POST['msn'])? $_POST['msn']:$msn;
$aim = ($_POST['aim'])? $_POST['aim']:$aim;
$website = ($_POST['website'])? $_POST['website']:$website;
$signature = ($_POST['signature'])? $_POST['signature']:$signature;
$user_timezone = ($_POST['user_timezone'])? $_POST['user_timezone']:$user_timezone;

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
</tr>";
if($pref['forum_user_customtitle'] || ADMIN){
        $text .= "
        <tr>
        <td style='width:30%' class='forumheader3'>".LAN_CUSTOMTITLE."</td>
        <td style='width:70%' class='forumheader2'>
        ".$rs -> form_text("customtitle", 40, $user_customtitle, 100)."
        </td>
        </tr>";
}

$text .= "

<tr>
<td style='width:20%' class='forumheader3'>".LAN_152."<br /><span class='smalltext'>".LAN_401."</span></td>
<td style='width:80%' class='forumheader2'>
".$rs -> form_password("password1", 40, "", 20);
if($pref['signup_pass_len']){
$text .= "<br /><span class='smalltext'>  (".LAN_SIGNUP_1." {$pref['signup_pass_len']} ".LAN_SIGNUP_2.")</span>";
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
</tr>";


// -------------------------------------------------------------
// public userclass subcription.
if($sql -> db_Select("userclass_classes","*","userclass_editclass =0")){
    $hide ="";
    $text .= "
    <tr>
    <td style='width:20%;vertical-align:top' class='forumheader3'>".LAN_USET_5.":
    <br /><span class='smalltext'>".LAN_USET_6."</span>
    </td>
    <td style='width:80%' class='forumheader2'>";
    $text .= "<table style='width:100%'>";
    $sql -> db_Select("userclass_classes","*","userclass_id !='' order by userclass_name");
    while($row3 = $sql-> db_Fetch()){
    extract($row3);
       if($userclass_editclass ==0){
          $frm_checked = check_class($userclass_id) ? "checked='checked'" : "";
          $text .= "<tr><td class='defaulttext'>";
          $text .= "<input type='checkbox' name='usrclass[]' value='$userclass_id' $frm_checked />\n";
       //   $text .= $rs -> form_checkbox("usrclass[]", $userclass_id, $frm_checked);
          $text .= $row3['userclass_name']."</td>";
          $text .= "<td class='smalltext'>".$row3['userclass_description']."</td>";
          $text .= "</tr>\n";
       }else{
          $hide .= check_class($userclass_id) ? "<input type='hidden' name='usrclass[]' value='$userclass_id' />\n" : "";
       }
    }
    $text .= "</table>\n";
    $text .= $hide;
    $text .= "</td></tr>\n";
}

// ---------------------------------------------------


$text .="<tr>
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
for($a=1900; $a<=$year; $a++){
        $text .= ($birth_year == $a ? $rs -> form_option($a, 1) : $rs -> form_option($a, 0));
}

$text .= $rs -> form_select_close()."</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_119."</td>
<td style='width:80%' class='forumheader2'>
<input class='tbox' type='text' name='location' size='60' value='$location' maxlength='200' />
</td>
</tr>";

if(($user_entended = $sysprefs->getArray('user_entended'))){
        $c=0;

        $user_pref = unserialize($user_prefs);

        while(list($key, $u_entended) = each($user_entended)){
                if($u_entended){
                require_once(e_HANDLER."user_extended.php");
                $text .= user_extended_edit($key,$u_entended,"forumheader3","left");
                }
        }
}

$text .= "<tr>
<td style='width:20%;vertical-align:top' class='forumheader3'>".LAN_120."</td>
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
                $text .= "<option value='".$timezone[$count]."' selected='selected'>(GMT".$timezone[$count].") ".$timearea[$count]."</option>\n";
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
<td colspan='2' class='forumheader3' style='text-align:center'>".LAN_404.($pref['im_width'] || $pref['im_height'] ? "<br />".($pref['im_width'] ? MAX_AVWIDTH.$pref['im_width']." pixels. " : "").($pref['im_height'] ? MAX_AVHEIGHT.$pref['im_height']." pixels." : "") : "")."</td>
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
<input class='button' type ='button' style=' cursor:hand' size='30' value='".LAN_403."' onclick='expandit(this)' />
<div style='display:none' >";
$avatarlist[0] = "";
$handle=opendir(e_IMAGE."avatars/");
while ($file = readdir($handle)){
        if($file != "." && $file != ".." && $file != "index.html" && $file != "CVS"){
                $avatarlist[] = $file;
        }
}
closedir($handle);

for($c=1; $c<=(count($avatarlist)-1); $c++){
        $text .= "<a href='javascript:addtext_us(\"$avatarlist[$c]\")'><img src='".e_IMAGE."avatars/".$avatarlist[$c]."' style='border:0' alt='' /></a> ";
}

$text .= "<br />
</div>
</td>
</tr>";

if($pref['avatar_upload'] && FILE_UPLOADS){

        $text .= "<tr>
<td style='width:20%; vertical-align:top' class='forumheader3'>".LAN_415."<br /></td>
<td style='width:80%' class='forumheader2'>
<input class='tbox' name='file_userfile[]' type='file' size='47' />
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
<input class='tbox' name='file_userfile[]' type='file' size='47' />
</td>
</tr>";
}


if(!e_QUERY){
        $text .= "


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
</tr>";
}
$text .= "



<tr style='vertical-align:top'>
<td colspan='2' style='text-align:center' class='forumheader'><input class='button' type='submit' name='updatesettings' value='".LAN_154."' /></td>
</tr>
</table>
</div><div>
<input type='hidden' name='_uid' value='$_uid' />
<input type='hidden' name='_pw' value='$user_password' />
<input type='hidden' name='_user_sess' value='$user_sess' /></div>
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


function req($field){
        global $pref;
        if($field ==2){
                $ret = "<span style='text-align:right;font-size:15px; color:red'> *</span>";
        } else {
                $ret = "";
        }
        return $ret;
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

function headerjs(){
$script = "<script type=\"text/javascript\">
function addtext_us(sc){
        document.getElementById('dataform').image.value = sc;
}

</script>\n";
return $script;

}

?>