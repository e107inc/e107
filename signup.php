<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /signup.php
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
require_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_usersettings.php");
$use_imagecode = ($pref['signcode'] && extension_loaded("gd"));

if($pref['membersonly_enabled']){
        $HEADER = "<div style='width:70%;margin-left:auto;margin-right:auto'><div style='text-align:center;'><br>";
        if(file_exists(THEME."images/login_logo.png")){
        $HEADER .= "<img src='".THEME."images/login_logo.png'>\n";
        } else{
        $HEADER .= "<img src='".e_IMAGE."logo.png'>\n";
        }
        $HEADER .= "<br />";
        $FOOTER = "</div></div>";
}

if($use_imagecode){
        require_once(e_HANDLER."secure_img_handler.php");
        $sec_img = new secure_image;
}

if($pref['user_reg'] == 0){header("location:".e_BASE."index.php"); exit; }

if(USER){header("location:".e_BASE."index.php"); exit; }

if(e_QUERY){
        $qs = explode(".", e_QUERY);
        if($qs[0] == "activate"){
                if($sql -> db_Select("user", "*", "user_sess='".$qs[2]."' ")){
                        if($row = $sql -> db_Fetch()){
                                $sql -> db_Update("user", "user_ban='0', user_sess='' WHERE user_sess='".$qs[2]."' ");
                                require_once(HEADERF);
                                $text = LAN_401." ".SITENAME;
                                $ns -> tablerender(LAN_402, $text);
                                require_once(FOOTERF);
                                exit;
                        }
                }else{
                header("location: ".e_BASE."index.php");
                exit;
        }
}
}

$signupval = explode(".",$pref['signup_options']);
$signup_title = array(LAN_308,LAN_144,LAN_115,LAN_116,LAN_117,LAN_118,LAN_119,LAN_120,LAN_121,LAN_122);
$signup_name = array("realname","website","icq","aim","msn","birth_year","location","signature","image","timezone");


if(IsSet($_POST['register'])){

        require_once(e_HANDLER."message_handler.php");

//        if(strlen($_POST['email']) > 100){exit;}
        if($use_imagecode){
                if(!$sec_img -> verify_code($_POST['rand_num'],$_POST['code_verify'])){
                        message_handler("P_ALERT", LAN_SIGNUP_3);
                        $error = TRUE;
                }
        }

        if(strstr($_POST['name'], "#") || strstr($_POST['name'], "=")){
                message_handler("P_ALERT", LAN_409);
                $error = TRUE;
        }

        $_POST['name'] = trim(chop(ereg_replace("&nbsp;|\#|\=", "", $_POST['name'])));
        if($_POST['name'] == "Anonymous"){
                message_handler("P_ALERT", LAN_103);
                $error = TRUE;
                $name = "";
        }

        if(strlen($_POST['name']) > 30){ exit; }

        if($sql -> db_Select("user", "*", "user_name='".$_POST['name']."' ")){
                message_handler("P_ALERT", LAN_104);
                $error = TRUE;
                $name = "";
        }


        if($_POST['password1'] != $_POST['password2']){
                message_handler("P_ALERT", LAN_105);
                $error = TRUE;
                $password1 = "";
                $password2 = "";
        }

        if(strlen($_POST['password1']) < $pref['signup_pass_len']){
                message_handler("P_ALERT", LAN_SIGNUP_4.$pref['signup_pass_len'].LAN_SIGNUP_5);
                $error = TRUE;
                $password1 = "";
                $password2 = "";
        }

        if($_POST['name'] == "" || $_POST['password1'] =="" || $_POST['password2'] = ""){
                message_handler("P_ALERT", LAN_185);
                $error = TRUE;
        }

        // ========== Verify Custom Signup options if selected ========================

        for ($i=0; $i<count($signup_title); $i++) {
                $postvalue = $signup_name[$i];
                if($signupval[$i]==2 && $_POST[$postvalue] == ""){
                        message_handler("P_ALERT", LAN_SIGNUP_6.$signup_title[$i].LAN_SIGNUP_7);
                        $error = TRUE;
                }
        };

        if($sql -> db_Select("user", "user_email", "user_email='".$_POST['email']."' ")){
                message_handler("P_ALERT", LAN_408);
                $error = TRUE;
        }


        if($sql -> db_Select("core", " e107_value", " e107_name='user_entended'")){
                $row = $sql -> db_Fetch();
                $user_entended = unserialize($row[0]);
                $c=0;
                $user_pref = unserialize($user_prefs);
                while(list($key, $u_entended) = each($user_entended)){
                        if($u_entended){

                                if($pref['signup_ext'.$key] ==2 && $_POST[str_replace(" ", "_", $u_entended)] == ""){
                                        $ut = explode("|",$u_entended);
                                        $u_name = ($ut[0] != "") ? trim($ut[0]) : trim($u_entended);
                                        $error_ext = LAN_SIGNUP_6.$u_name.LAN_SIGNUP_7;
                                        message_handler("P_ALERT", $error_ext);
                                        $error = TRUE;
                                }

                        }
                }
        }

        // ========== End of verification.. ====================================================

        if(!preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]{1,50}@([-0-9A-Z]+\.){1,50}([0-9A-Z]){2,4}$/i', $_POST['email'])){
                message_handler("P_ALERT", LAN_106);
                $error = TRUE;
        }
        if (preg_match('#^www\.#si', $_POST['website'])) {
                $_POST['website'] = "http://$homepage";
        }else if (!preg_match('#^[a-z0-9]+://#si', $_POST['website'])){
                $_POST['website'] = "";
        }
        if(!$error){
                $fp = new floodprotect;
                if($fp -> flood("user", "user_join") == FALSE){
                        header("location:index.php");
                        exit;
                }

                if($sql -> db_Select("user", "*", "user_email='".$_POST['email']."' AND user_ban='1' ")){
                        exit;
                }

                $wc = "*".substr($_POST['email'], strpos($_POST['email'], "@"));
                if($sql -> db_Select("banlist", "*", "banlist_ip='".$_POST['email']."' OR banlist_ip='$wc'")){
                        exit;
                }


                $username = strip_tags($_POST['name']);
                $time=time();
                $ip = getip();
                $birthday = $_POST['birth_year']."/".$_POST['birth_month']."/".$_POST['birth_day'];

                if($pref['user_reg_veri']){
                        $key = md5(uniqid(rand(),1));
                        $sql -> db_Insert("user", "0, \"".$username."\", '', \"".md5($_POST['password1'])."\", '$key', \"".$_POST['email']."\",         \"".$_POST['website']."\", \"".$_POST['icq']."\", \"".$_POST['aim']."\", \"".$_POST['msn']."\", \"".$_POST['location']."\", \"".$birthday."\", \"".$_POST['signature']."\", \"".$_POST['image']."\", \"".$_POST['timezone']."\", \"".$_POST['hideemail']."\", \"".$time."\", '0', \"".$time."\", '0', '0', '0', '0', '".$ip."', '2', '0', '', '', '', '0', \"".$_POST['realname']."\", '', '', '', '' ");
                        $sql -> db_Select("user", "*", "user_name='".$_POST['name']."' AND user_join='".$time."' ");
                        $row = $sql -> db_Fetch();
                        $id = $row['user_id'];


                        define("RETURNADDRESS", (substr(SITEURL, -1) == "/" ? SITEURL."signup.php?activate.".$id.".".$key : SITEURL."/signup.php?activate.".$id.".".$key));

                        $message = LAN_403.RETURNADDRESS.LAN_407." ".SITENAME."\n".SITEURL;

                        require_once(e_HANDLER."mail.php");
                        if(file_exists(THEME."emails.php")){
                            require_once(THEME."emails.php");
                            $message = ($SIGNUPEMAIL)? $SIGNUPEMAIL:$message;
                        }
                        sendemail($_POST['email'], LAN_404." ".SITENAME, $message);
        // ================== save extended fields as serialized data.

        if($sql -> db_Select("core", " e107_value", " e107_name='user_entended'")){
            $aj = new textparse;
            $row = $sql -> db_Fetch();
            $user_entended = unserialize($row[0]);
            $c=0;
            while(list($key, $u_entended) = each($user_entended)){
                $val = $aj -> formtpa($_POST[str_replace(" ", "_", $u_entended)], "public");
                $user_pref[$u_entended] = $val;
                $c++;
            }
            $tmp = addslashes(serialize($user_pref));
            $sql -> db_Update("user", "user_prefs='$tmp' WHERE user_id='".$id."' ");
        }
        // ==========================================================


                        require_once(HEADERF);
                        $text = LAN_405;
                        $ns -> tablerender("<div style='text-align:center'>".LAN_406."</div>", $text);
                        require_once(FOOTERF);
                        exit;
                }else{
                require_once(HEADERF);
                $sql -> db_Insert("user", "0, '".$username."', '', '".md5($_POST['password1'])."', '$key', '".$_POST['email']."',         '".$_POST['website']."', '".$_POST['icq']."', '".$_POST['aim']."', '".$_POST['msn']."', '".$_POST['location']."', '".$birthday."', '".$_POST['signature']."', '".$_POST['image']."', '".$_POST['timezone']."', '".$_POST['hideemail']."', '".$time."', '0', '".$time."', '0', '0', '0', '0', '".$ip."', '0', '0', '', '', '', '0', '".$_POST['realname']."', '', '', '', '' ");
                $ns -> tablerender("<div style='text-align:center'>".LAN_SIGNUP_8."</div>", LAN_107);
                require_once(FOOTERF);
                exit;
        }
}

}
require_once(HEADERF);

$qs = ($error ? "stage" : e_QUERY);

if($pref['use_coppa'] == 1 && !ereg("stage", $qs)){
        if(eregi("stage", LAN_109)){
                $text .= LAN_109."</b></div>";
        }else{
                $text .= LAN_109."<form method='post' action='signup.php?stage1'>
        <br />
        <input type='radio' name='coppa' value='0' checked> ".LAN_200."
        <input type='radio' name='coppa' value='1'> ".LAN_201."<br>
        <br />
        <input class='button' type='submit' name='newver' value='".LAN_399."' />
        </form>
        </div>";
        }

        $ns -> tablerender("<div style='text-align:center'>".LAN_110."</div>", $text);
        require_once(FOOTERF);
        exit;
}

if(!$website){
        $website = "http://";
}

if(!eregi("stage", LAN_109)){
        if(IsSet($_POST['newver'])){
                if(!$_POST['coppa']){
                        $ns -> tablerender("<div style='text-align:center'>".LAN_202."</div>", "<div style='text-align:center'>".LAN_SIGNUP_9."</div>");
                        require_once(FOOTERF);
                        exit;
                }
        }
}
$text .= "<div style='text-align:center'>";
if($pref['user_reg_veri']){
        $text .= LAN_309."<br /><br />";
}

$text .= LAN_400;
require_once(e_HANDLER."form_handler.php");
$rs = new form;
$text .= $rs -> form_open("post", e_SELF, "signupform")."
<table class='fborder' style='width:70%'>
<tr>
<td class='forumheader3' style='width:30%;white-space:nowrap' >".LAN_7."<span style='font-size:15px; color:red'> *</span></td>
<td class='forumheader3' style='width:70%'>
".$rs -> form_text("name", 40, $name, 30)."
</td>
</tr>";

 if($signupval[0]){
$text .="
<tr>
<td class='forumheader3' style='width:30%;white-space:nowrap'>".LAN_308."".req($signupval[0])."</td>
<td class='forumheader3' style='width:70%' >
".$rs -> form_text("realname", 40, $realname, 100)."
</td>
</tr>";
}

$text .="
<tr>
<td class='forumheader3' style='width:30%;white-space:nowrap'>".LAN_17."<span style='font-size:15px; color:red'> *</span></td>
<td class='forumheader3' style='width:70%'>
".$rs -> form_password("password1", 40, $password1, 20)."
";
if($pref['signup_pass_len']){
$text .= "<br><span class='smalltext'>  (".LAN_SIGNUP_1." {$pref['signup_pass_len']} ".LAN_SIGNUP_2.")</span>";
}
$text .="
</td>
</tr>
<tr>
<td class='forumheader3' style='width:30%;white-space:nowrap'>".LAN_111."<span style='font-size:15px; color:red'> *</span></td>
<td class='forumheader3' style='width:70%'>
".$rs -> form_password("password2", 40, $password2, 20)."
</td>
</tr>
<tr>
<td class='forumheader3' style='width:30%;white-space:nowrap'>".LAN_112."<span style='font-size:15px; color:red'> *</span></td>
<td class='forumheader3' style='width:70%'>
".$rs -> form_text("email", 40, $email, 100)."
</td>
</tr>
<tr>
<td class='forumheader3' style='width:30%;white-space:nowrap'>".LAN_113."</td>
<td class='forumheader3' style='width:70%'>".
$rs ->form_radio("hideemail", 1)." ".LAN_SIGNUP_10."&nbsp;&nbsp;".$rs ->form_radio("hideemail", 0, 1)." ".LAN_200."
</td>
</tr>";



if($signupval[1]){
        $text.="
        <tr>
        <td class='forumheader3' style='width:30%;white-space:nowrap' >".LAN_144.req($signupval[1])."</td>
        <td class='forumheader3' style='width:70%' >
        ".$rs -> form_text("website", 60, $website, 150)."
        </td>
        </tr>";
}


if($signupval[2]){
        $text.="
        <tr>
        <td class='forumheader3' style='width:30%;white-space:nowrap' >".LAN_115.req($signupval[2])."</td>
        <td class='forumheader3' style='width:70%' >
        ".$rs -> form_text("icq", 20, $icq, 10)."
        </td>
        </tr>";
}

if($signupval[3]){
        $text.="
        <tr>
        <td class='forumheader3' style='width:30%;white-space:nowrap' >".LAN_116.req($signupval[3])."</td>
        <td class='forumheader3' style='width:70%; ' >
        <input class='tbox' type='text' name='aim' size='30' value='$aim' maxlength='100' />
        </td>
        </tr>";
}


if($signupval[4]){
        $text.="
        <tr>
        <td class='forumheader3' style='width:30%;white-space:nowrap' >".LAN_117.req($signupval[4])."</td>
        <td class='forumheader3' style='width:70%;'>
        <input class='tbox' type='text' name='msn' size='30' value='$msn' maxlength='100' />
        </td>
        </tr>";
}


if($signupval[5]){
        $text.="
        <tr>
        <td class='forumheader3' style='width:30%;white-space:nowrap' >".LAN_118.req($signupval[5])."</td>
        <td class='forumheader3' style='width:70%; ' nowrap>".

        $rs -> form_select_open("birth_day").
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

        $text .= "</td></tr>";
}



if($signupval[6]){
        $text.="
        <tr>
        <td class='forumheader3' style='width:30%;white-space:nowrap' >".LAN_119."</td>
        <td class='forumheader3' style='width:70%' >
        <input class='tbox' type='text' name='location' size='60' value='$location' maxlength='200' />
        ".req($signupval[6])."</td>
        </tr>";
}



if($sql -> db_Select("core", " e107_value", " e107_name='user_entended'")){
        $row = $sql -> db_Fetch();
        $user_entended = unserialize($row[0]);
        $c=0;
        $user_pref = unserialize($user_prefs);
        require_once(e_HANDLER."user_extended.php");
        while(list($key, $u_entended) = each($user_entended)){
                if($u_entended){
                        $signup_ext = "signup_ext".$key;
                        if($pref[$signup_ext]){
                                $text .= user_extended_edit($u_entended,"forumheader3","left");
                                $c++;
                        }
                }
        }
}

if($signupval[7]){
        require_once(e_HANDLER."ren_help.php");
        $text .= "<tr>
        <td class='forumheader3' style='width:30%;white-space:nowrap;vertical-align:top' >".LAN_120."</td>
        <td class='forumheader3' style='width:70%' >
        <textarea class='tbox' name='signature' cols='70' rows='4'>$signature</textarea>
        ".req($signupval[7])."<input class='helpbox' type='text' name='helpb' size='90' />
        ".ren_help("addtext");
}



if($signupval[8]){
        $text.="
        <tr>
        <td class='forumheader3' style='width:30%; vertical-align:top;white-space:nowrap' >".LAN_121.req($signupval[8])."<br /><span class='smalltext'>(".LAN_402.")</span></td>
        <td class='forumheader3' style='width:70%' >
        <input class='tbox' type='text' name='image' size='60' value='$image' maxlength='100' />

        <input class='button' type ='button' style=''width: 35px'; cursor:hand' size='30' value='Show' onClick='expandit(this)'>
        <div style='display:none' style=&{head};>";
        $avatarlist[0] = "";
        $handle=opendir(e_IMAGE."avatars/");
        while ($file = readdir($handle)){
                if($file != "." && $file != ".."){
                        $avatarlist[] = $file;
                }
        }
closedir($handle);

for($c=1; $c<=(count($avatarlist)-1); $c++){
        $text .= "<a href='javascript:addtext3(\"$avatarlist[$c]\")'><img src='".e_IMAGE."avatars/".$avatarlist[$c]."' style='border:0' alt='' /></a> ";
}

$text .= "<br />
</div>";

if($pref['avatar_upload'] && FILE_UPLOADS){
        $text .= "<br /><span class='smalltext'>Upload your avatar</span> <input class='tbox' name='file_userfile[]' type='file' size='47'>
        <br /><div class='smalltext'>".LAN_404."</div>";
}

if($pref['photo_upload'] && FILE_UPLOADS){
        $text .= "<br /><span class='smalltext'>Upload your photograph</span> <input class='tbox' name='file_userfile[]' type='file' size='47'>
        <br /><div class='smalltext'>".LAN_404."</div>";
}


$text .= "</td>
</tr>";
}

if($signupval[9]){
        $text.="
        <tr>
        <td class='forumheader3' style='width:30%' >".LAN_122.req($signupval[9])."</td>
        <td class='forumheader3' style='width:70%' nowrap>
        <select name='timezone' class='tbox'>\n";

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
</tr>";
}

if($use_imagecode){
        $text .= " <tr>
        <td class='forumheader3' style='width:30%'>".LAN_410."</td>
        <td class='forumheader3' style='width:70%'>".
        $rs ->form_hidden("rand_num", $sec_img -> random_number).
        $sec_img -> r_image()."<br />".$rs -> form_text("code_verify", 20, "", 20)."
        </td>
        </tr>";
}

$text.="
<tr style='vertical-align:top'>
<td class='forumheader' colspan='2'  style='text-align:center'>
<input class='button' type='submit' name='register' value='".LAN_123."' />
<br />
</td>
</tr>
</table>
</form>
</div>
";

$ns -> tablerender(LAN_123, $text);

require_once(FOOTERF);

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

function timezone(){
        /*
        # Render style table
        # - parameters                none
        # - return                    timezone arrays
        # - scope                     public
        */
        global $timezone, $timearea;
        $timezone = array("-12", "-11", "-10", "-9", "-8", "-7", "-6", "-5", "-4", "-3", "-2", "-1", "GMT", "+1", "+2", "+3", "+4", "+5", "+6", "+7", "+8", "+9", "+10", "+11", "+12", "+13");
        $timearea = array("International DateLine West", "Samoa", "Hawaii", "Alaska", "Pacific Time (US and Canada)", "Mountain Time (US and Canada)", "Central Time (US and Canada), Central America", "Eastern Time (US and Canada)", "Atlantic Time (Canada)", "Greenland, Brasilia, Buenos Aires, Georgetown", "Mid-Atlantic", "Azores", "GMT - UK, Ireland, Lisbon", "West Central Africa, Western Europe", "Greece, Egypt, parts of Africa", "Russia, Baghdad, Kuwait, Nairobi", "Abu Dhabi, Kabul", "Islamabad, Karachi", "Astana, Dhaka", "Bangkok, Rangoon", "Hong Kong, Singapore, Perth, Beijing", "Tokyo, Seoul", "Brisbane, Canberra, Sydney, Melbourne", "Soloman Islands", "New Zealand", "Nuku'alofa");
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

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

?>
<script type="text/javascript">
function addtext(sc){
        document.signupform.image.value = sc;
}

function addsig(sc){
        document.signupform.signature.value += sc;

}
function help(help){
        document.signupform.helpb.value = help;


}
</script>