<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        //prefs.php
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

if(IsSet($_POST['newver'])){
        header("location:http://e107.org/index.php");
        exit;
}

if(!getperms("1")){ header("location:".e_BASE."index.php"); exit;}
if(!$pref['timezone']){ $pref['timezone'] = "GMT"; }

$signup_title = array(CUSTSIG_2,CUSTSIG_3,"ICQ","Aim","MSN",CUSTSIG_4,CUSTSIG_5,CUSTSIG_6,CUSTSIG_7,CUSTSIG_8);
$signup_name = array("real","url","icq","aim","msn","dob","loc","sig","avt","zone");

if(IsSet($_POST['updateprefs'])){
        $aj = new textparse;
        $pref['sitename'] = $aj -> formtpa($_POST['sitename']);
        $pref['siteurl'] = $aj -> formtpa($_POST['siteurl']);
        $pref['sitebutton'] = $aj -> formtpa($_POST['sitebutton']);
        $pref['sitetag'] = $aj -> formtpa($_POST['sitetag']);
        $pref['sitedescription'] = $aj -> formtpa($_POST['sitedescription']);
        $pref['siteadmin'] = $aj -> formtpa($_POST['siteadmin']);
        $pref['siteadminemail'] = $aj -> formtpa($_POST['siteadminemail']);
        $pref['sitetheme'] = ($_POST['sitetheme'] && $_POST['sitetheme'] != "/" ? $_POST['sitetheme'] : "leap of faith");
        $pref['admintheme'] = ($_POST['admintheme'] && $_POST['admintheme'] != "/" ? $_POST['admintheme'] : "leap of faith");
        $pref['sitedisclaimer'] = $aj -> formtpa($_POST['sitedisclaimer']);

        $pref['anon_post'] = $_POST['anon_post'];
        $pref['user_reg'] = $_POST['user_reg'];
        $pref['profanity_filter'] = $_POST['profanity_filter'];
        $pref['profanity_replace'] = $aj -> formtpa($_POST['profanity_replace']);
        $pref['profanity_words'] = $aj -> formtpa($_POST['profanity_words']);
        $pref['use_coppa'] = $_POST['use_coppa'];
        $pref['shortdate'] = $aj -> formtpa($_POST['shortdate']);
        $pref['longdate'] = $aj -> formtpa($_POST['longdate']);
        $pref['forumdate'] = $aj -> formtpa($_POST['forumdate']);
        $pref['sitelanguage'] = $_POST['sitelanguage'];
        $pref['time_offset'] = $_POST['time_offset'];
        $pref['user_reg_veri'] = $_POST['user_reg_veri'];
        $pref['user_tracking'] = $_POST['user_tracking'];
        $pref['cookie_name'] = ereg_replace("[^[:alpha:]]", "", $_POST['cookie_name']);
        $pref['auth_method'] = $_POST['auth_method'];
        $pref['displaythemeinfo'] = $_POST['displaythemeinfo'];
        $pref['displayrendertime'] = $_POST['displayrendertime'];
        $pref['displaysql'] = $_POST['displaysql'];
        $pref['timezone'] = $_POST['timezone'];
        $pref['adminstyle'] = $_POST['adminstyle'];
        $pref['membersonly_enabled'] = $_POST['membersonly_enabled'];
        $pref['ssl_enabled'] = $_POST['ssl_enabled'];

        // Signup.
          $pref['signup_real'] = $_POST['signup_real'];
        $pref['signup_url'] = $_POST['signup_url'];
        $pref['signup_icq'] = $_POST['signup_icq'];
        $pref['signup_aim'] = $_POST['signup_aim'];
        $pref['signup_msn'] = $_POST['signup_msn'];
        $pref['signup_dob'] = $_POST['signup_dob'];
        $pref['signup_loc'] = $_POST['signup_loc'];
        $pref['signup_sig'] = $_POST['signup_sig'];
        $pref['signup_avt'] = $_POST['signup_avt'];
        $pref['signup_zone'] = $_POST['signup_zone'];

        $pref['signup_pass_len'] = $_POST['signup_pass_len'];

        $pref['signup_real_req'] = $_POST['signup_real_req'];
        $pref['signup_url_req'] = $_POST['signup_url_req'];
        $pref['signup_icq_req'] = $_POST['signup_icq_req'];
        $pref['signup_aim_req'] = $_POST['signup_aim_req'];
        $pref['signup_msn_req'] = $_POST['signup_msn_req'];
        $pref['signup_dob_req'] = $_POST['signup_dob_req'];
        $pref['signup_loc_req'] = $_POST['signup_loc_req'];
        $pref['signup_sig_req'] = $_POST['signup_sig_req'];
        $pref['signup_avt_req'] = $_POST['signup_avt_req'];
        $pref['signup_zone_req'] = $_POST['signup_zone_req'];

        // Create prefs to custom fields.
        if($sql -> db_Select("core", " e107_value", " e107_name='user_entended'")){
        $row = $sql -> db_Fetch();
        $user_entended = unserialize($row[0]);
        $c=0;
        $user_pref = unserialize($user_prefs);
            while(list($key, $u_entended) = each($user_entended)){
                    if($u_entended){
                    $signup_ext =  "signup_ext".$key;
                    $pref[$signup_ext] = $_POST[$signup_ext];
                    $signup_ext_req =  "signup_ext_req".$key;
                    $pref[$signup_ext_req] = $_POST[$signup_ext_req];
                    }
            }
        }





        $pref['signcode'] = $_POST['signcode'];
         $pref['logcode'] = $_POST['logcode'];




        $pref['htmlarea'] = $_POST['htmlarea'];

        $pref['smtp_enable'] = $_POST['smtp_enable'];
        $pref['smtp_server'] = $aj -> formtpa($_POST['smtp_server']);
        $pref['smtp_username'] = $aj -> formtpa($_POST['smtp_username']);
        $pref['smtp_password'] = $aj -> formtpa($_POST['smtp_password']);

        for ($i=0; $i<count($signup_title); $i++) {
        $valuesignup =  $signup_name[$i];
        $signup_options .= $_POST[$valuesignup];
        $signup_options .= $i < (count($signup_title)-1)?".":"";
        }
        $pref['signup_options'] = $signup_options;

        
        $sql -> db_Delete("cache");
        save_prefs();
        header("location:prefs.php");
       echo "<script type='text/javascript'>document.location.href='prefs.php'</script>\n";
        exit;
}

$sql -> db_Select("plugin","*","plugin_installflag='1' ");
while($row = $sql -> db_Fetch()){
        extract($row);
        if(preg_match("/^auth_(.*)/",$plugin_path,$match)){
                $authlist[]=$match[1];
        }
}
if($authlist){
        $authlist[] = "e107";
        $auth_dropdown = "\n<tr>
        <td style='width:50%' class='forumheader3'>".PRFLAN_56.": </td>
        <td style='width:50%; text-align:right;' class='forumheader3'>";
        $auth_dropdown .= "<select class='tbox' name='auth_method'>\n";
        foreach($authlist as $a){
                $s = ($pref['auth_method'] == $a) ? " SELECTED" : "";
                $auth_dropdown .= "<option {$s}>".$a."</option>\n";
        }
        $auth_dropdown .= "</select>\n";
        $auth_dropdown .= "</td></tr>";
} else {
        $auth_dropdown="<input type='hidden' name='auth_method' value=''>";
        $pref['auth_method']="";
}


//added prefs since v2.0 ...
$anon_post = $pref['anon_post'];
$user_reg = $pref['user_reg'];
$profanity_filter = $pref['profanity_filter'];
$profanity_replace = $pref['profanity_replace'];
$profanity_words = $pref['profanity_words'];
$use_coppa = $pref['use_coppa'];
$shortdate = $pref['shortdate'];
$longdate = $pref['longdate'];
$forumdate = $pref['forumdate'];
$sitelocale = $pref['sitelocale'];
$time_offset = $pref['time_offset'];
$user_reg_veri = $pref['user_reg_veri'];
$user_tracking = $pref['user_tracking'];

require_once("auth.php");


if(IsSet($_POST['testemail'])){
        require_once(e_HANDLER."mail.php");
        if(!sendemail(SITEADMINEMAIL, PRFLAN_66." ".SITENAME, PRFLAN_67)){
                $message = ($pref['smtp_enable'] ? PRFLAN_75 : PRFLAN_68);
        }else{
                $message = PRFLAN_69;
        }
}



if(IsSet($message)){
        $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$handle=opendir(e_THEME);
while ($file = readdir($handle)){
        if($file != "." && $file != ".." && $file != "templates" && $file != "/"){
                $dirlist[] = $file;
        }
}
closedir($handle);

$handle=opendir(e_LANGUAGEDIR);
while ($file = readdir($handle)){
        if($file != "." && $file != ".." && $file != "/"){
                $lanlist[] = $file;
        }
}
closedir($handle);

$handle=opendir(e_ADMIN);
while ($file = readdir($handle)){
        if(strstr($file, "admin") && $file != "administrator.php" && $file != "updateadmin.php"){
                $file = str_replace(".php", "", $file);
                if($file == "admin"){ $file = "default"; }
                $adminlist[] = $file;
        }
}
closedir($handle);
// new
/*
$text .= "<div style='text-align:center'><div style='text-align:center; width:97%'>";
$text .="<input type='button' class='button' style='width:280px' value='".PRFLAN_1."' onClick=\"expandit('main')\"><br/>";
$text .="<input type='button' class='button' style='width:280px' value='".PRFLAN_21."' onClick=\"expandit('date')\"><br/>";
$text .="<input type='button' class='button' style='width:280px' value='".PRFLAN_77."' onClick=\"expandit('admindisp')\"><br/>";
$text .="<input type='button' class='button' style='width:280px' value='".PRFLAN_28."' onClick=\"expandit('registration')\"><br/>";
$text .="<input type='button' class='button' style='width:280px' value='".PRFLAN_47."' onClick=\"expandit('security')\"><br/>";
$text .="<input type='button' class='button' style='width:280px' value='".PRFLAN_62."' onClick=\"expandit('mail')\"><br/>";
$text .="</div>";
*/
// end new.


$text .= "
<div style='text-align:center'><div class='caption' title='".PRFLAN_80."' style='cursor:hand;text-align:left;border:1px solid black;width:95%' onClick=\"expandit(this)\">".PRFLAN_1."</div>
<div id='main' style='text-align:center'>
<form method='post' action='prefs.php'>
<table style='width:95%' class='fborder' cellspacing='1' cellpadding='0'>
<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_2.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input class='tbox' type='text' name='sitename' size='50' value='".SITENAME."' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_3.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input class='tbox' type='text' name='siteurl' size='50' value='".SITEURL."' maxlength='150' />
</td>
</tr>


<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_4.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input class='tbox' type='text' name='sitebutton' size='50' value='".SITEBUTTON."' maxlength='150' />
</td>
</tr>
<tr>

<td style='width:50%' class='forumheader3'>".PRFLAN_5.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<textarea class='tbox' name='sitetag' cols='59' rows='3'>".SITETAG."</textarea>
</td>
</tr>
<tr>

<td style='width:50%' class='forumheader3'>".PRFLAN_6.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<textarea class='tbox' name='sitedescription' cols='59' rows='3'>".SITEDESCRIPTION."</textarea>
</td>
</tr>
<tr>

<td style='width:50%' class='forumheader3'>".PRFLAN_7.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input class='tbox' type='text' name='siteadmin' size='50' value='".SITEADMIN."' maxlength='100' />
</td>
</tr>
<tr>

<td style='width:50%' class='forumheader3'>".PRFLAN_8.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input class='tbox' type='text' name='siteadminemail' size='50' value='".SITEADMINEMAIL."' maxlength='100' />
</td>
</tr>
<tr>

<td style='width:50%' class='forumheader3'>".PRFLAN_9.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<textarea class='tbox' name='sitedisclaimer' cols='59' rows='3'>".SITEDISCLAIMER."</textarea>
</td>
</tr>

</table></div>
<div style='text-align:center'><div class='caption' title='".PRFLAN_80."' style='cursor:hand;text-align:left;border:1px solid black;width:95%' onClick=\"expandit(this)\">".PRFLAN_10."</div>
<div id='theme' style='display:none'>
<table style='width:95%' class='fborder' cellspacing='1' cellpadding='0'>

<td style='width:50%' class='forumheader3'>".PRFLAN_11.": </td>
<td style='width:50%; text-align:right' class='forumheader3'><a href='".e_ADMIN."theme_prev.php'>".PRFLAN_12."</a>
<select name='display' class='tbox'>\n";
$counter = 0;
while(IsSet($dirlist[$counter])){
        $text .= ($dirlist[$counter] == $pref['sitetheme'] ? "<option selected>".$dirlist[$counter]."</option>\n" : "<option>".$dirlist[$counter]."</option>\n");
        $counter++;
}
$text .= "</select>
</td>
</tr>

</table></div>
<div style='text-align:center'><div class='caption' title='".PRFLAN_80."' style='cursor:hand;text-align:left;border:1px solid black;width:95%' onClick=\"expandit(this)\">".PRFLAN_13."</div>
<div id='theme' style='display:none'>
<table style='width:95%' class='fborder' cellspacing='1' cellpadding='0'>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_14." </td>
<td style='width:50%; text-align:right' class='forumheader3'>".
($pref['displaythemeinfo'] ? "<input type='checkbox' name='displaythemeinfo' value='1' checked>" : "<input type='checkbox' name='displaythemeinfo' value='1'>")." </td>
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_15." </td>
<td style='width:50%; text-align:right' class='forumheader3'>".
($pref['displayrendertime'] ? "<input type='checkbox' name='displayrendertime' value='1' checked>" : "<input type='checkbox' name='displayrendertime' value='1'>")."
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_16." </td>
<td style='width:50%; text-align:right' class='forumheader3'>".
($pref['displaysql'] ? "<input type='checkbox' name='displaysql' value='1' checked>" : "<input type='checkbox' name='displaysql' value='1'>")."
</td>
</tr>

</table></div>
<div style='text-align:center'><div class='caption' title='".PRFLAN_80."' style='cursor:hand;text-align:left;border:1px solid black;width:95%' onClick=\"expandit(this)\">".PRFLAN_17."</div>
<div id='theme' style='display:none'>
<table style='width:95%' class='fborder' cellspacing='1' cellpadding='0'>

<tr>

<td style='width:50%' class='forumheader3'>".PRFLAN_18.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<select name='sitelanguage' class='tbox'>\n";
$counter = 0;
$sellan = eregi_replace("lan_*.php", "", $pref['sitelanguage']);
while(IsSet($lanlist[$counter])){
        if($lanlist[$counter] == $sellan){
                $text .= "<option selected>".$lanlist[$counter]."</option>\n";
        }else{
                $text .= "<option>".$lanlist[$counter]."</option>\n";
        }
        $counter++;
}
$text .= "</select>
</td>
</tr>";
$text .="</table></div>";

// Admin Display Areas. .

$text .="
<div style='text-align:center'><div class='caption' title='".PRFLAN_80."' style='cursor:hand;text-align:left;border:1px solid black;width:95%' onClick=\"expandit(this)\">".PRFLAN_77."</div>
<div id='admindisp' style='display:none'>
<table style='width:95%' class='fborder' cellspacing='1' cellpadding='0'>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_54.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<select name='admintheme' class='tbox'>\n";
$counter = 0;
while(IsSet($dirlist[$counter])){
        $text .= ($dirlist[$counter] == $pref['admintheme'] ? "<option selected>".$dirlist[$counter]."</option>\n" : "<option>".$dirlist[$counter]."</option>\n");
        $counter++;
}
$text .= "</select>
</td>
</tr>




<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_57.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<select name='adminstyle' class='tbox'>\n";
$counter = 0;
while(IsSet($adminlist[$counter])){
        $text .= ($adminlist[$counter] == $pref['adminstyle'] ? "<option selected>".$adminlist[$counter]."</option>\n" : "<option>".$adminlist[$counter]."</option>\n");
        $counter++;
}
$text .= "</select>
</td>
</tr>


<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_79.":</td>
<td style='width:50%; text-align:right' class='forumheader3'>".
($pref['htmlarea'] ? "<input type='checkbox' name='htmlarea' value='1'  checked>" : "<input type='checkbox' name='htmlarea' value='1'>")."
</td>
</tr></table></div>";

// Date options.
$text .="

<div style='text-align:center'><div class='caption' title='".PRFLAN_80."' style='cursor:hand;text-align:left;border:1px solid black;width:95%' onClick=\"expandit(this)\">".PRFLAN_21."</div>
<div id='date' style='text-align:center; display:none' >
<table style='width:95%' class='fborder' cellspacing='1' cellpadding='0'>
<tr>";

$ga = new convert;
$date1 = $ga -> convert_date(time(), "short");
$date2 = $ga -> convert_date(time(), "long");
$date3 = $ga -> convert_date(time(), "forum");


$text .= "<td style='width:50%' class='forumheader3'>".PRFLAN_22.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input class='tbox' type='text' name='shortdate' size='40' value='$shortdate' maxlength='50' />
<br />example: $date1
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_23.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input class='tbox' type='text' name='longdate' size='40' value='$longdate' maxlength='50' />
<br />example: $date2
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_24.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input class='tbox' type='text' name='forumdate' size='40' value='$forumdate' maxlength='50' />
<br />example: $date3
</td>
</tr>

<tr>
<td colspan='2' style='text-align:center' class='forumheader3'>
(".PRFLAN_25.")
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_26.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<select name='time_offset' class='tbox'>\n";
$toffset = array("-12", "-11", "-10", "-9", "-8", "-7", "-6", "-5", "-4", "-3", "-2", "-1", "0", "+1", "+2", "+3", "+4", "+5", "+6", "+7", "+8", "+9", "+10", "+11", "+12", "+13", "+14");
$counter = 0;
while(IsSet($toffset[$counter])){
        if($toffset[$counter] == $pref['time_offset']){
                $text .= "<option selected>".$toffset[$counter]."</option>\n";
        }else{
                $text .= "<option>".$toffset[$counter]."</option>\n";
        }
        $counter++;
}
$text .= "</select>
</td></tr>

<tr>
<td colspan='2' style='text-align:center' class='forumheader3'>
(".PRFLAN_27.")
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_56.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input class='tbox' type='text' name='timezone' size='20' value='".$pref['timezone']."' maxlength='50' />
</td>
</tr></table></div>";

// =========== Registration Preferences. ==================

$text .="

<div style='text-align:center'><div class='caption' title='".PRFLAN_80."' style='cursor:hand;text-align:left;border:1px solid black;width:95%' onClick=\"expandit(this)\">".PRFLAN_28."</div>
<div id='registration' style='text-align:center; display:none'>
<table style='width:95%' class='fborder' cellspacing='1' cellpadding='0'>
<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_29.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>".
($user_reg ? "<input type='checkbox' name='user_reg' value='1'  checked>" : "<input type='checkbox' name='user_reg' value='1'>")."
 (".PRFLAN_30.")
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_31.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>".
($user_reg_veri ? "<input type='checkbox' name='user_reg_veri' value='1'  checked>" : "<input type='checkbox' name='user_reg_veri' value='1'>")."
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_32.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>".
($anon_post ? "<input type='checkbox' name='anon_post' value='1'  checked>" : "<input type='checkbox' name='anon_post' value='1'>")."
(".PRFLAN_33.")
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_45.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>";
if($use_coppa == 1){
        $text .= "<input type='checkbox' name='use_coppa' value='1'  checked>";
}else{
        $text .= "<input type='checkbox' name='use_coppa' value='1'>";
}


$text .= "(".PRFLAN_46.")
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_58.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>".
($pref['membersonly_enabled'] ? "<input type='checkbox' name='membersonly_enabled' value='1'  checked>" : "<input type='checkbox' name='membersonly_enabled' value='1'>")."
(".PRFLAN_59.")
</td>
</tr>


";

$text .="
<tr>
<td style='width:50%' class='forumheader3'>".CUSTSIG_16."</td>
<td class='forumheader3' style='width:50%;text-align:right' >
<input type='text' class='tbox' size='3' name='signup_pass_len' value='".$pref['signup_pass_len']."'>
(".PRFLAN_78.") </td>
</tr></table></div>";
// Signup options.



$text .= "
<div style='text-align:center'><div class='caption' title='".PRFLAN_80."' title='".PRFLAN_80."' style='cursor:hand;text-align:left;border:1px solid black;width:95%' onClick=\"expandit(this)\">".PRFLAN_19."</div>
<div id='signup' style='text-align:center; display:none'>
<table style='width:95%' class='fborder' cellspacing='1' cellpadding='0'>
<tr >
<td class=\"fcaption\">".CUSTSIG_13."</td>
<td class=\"fcaption\">".CUSTSIG_14."</td>
</tr>";

    for ($i=0; $i<count($signup_title); $i++) {

    $text .="
    <tr>
<td style='width:50%' class='forumheader3'>".$signup_title[$i]."</td>
<td style='width:50%' class='forumheader3'>".
($$signup_name[$i] == "" || $$signup_name[$i]=="" ? "<input type='radio' name='".$signup_name[$i]."' value='0' checked> ".CUSTSIG_12."&nbsp;" : "<input type='radio' name='".$signup_name[$i]."' value='0'> ".CUSTSIG_12."&nbsp;").
($$signup_name[$i] == "1" ? "<input type='radio' name='".$signup_name[$i]."' value='1' checked> ".CUSTSIG_14."&nbsp;" : "<input type='radio' name='".$signup_name[$i]."' value='1'> ".CUSTSIG_14."&nbsp;").
($$signup_name[$i] == "2" ? "<input type='radio' name='".$signup_name[$i]."' value='2' checked> ".CUSTSIG_15."&nbsp;" : "<input type='radio' name='".$signup_name[$i]."' value='2'> ".CUSTSIG_15."&nbsp;")."

</td>

</tr>";

    }

/*

$text .="
<tr>
<td style='width:50%' class='forumheader3'>".CUSTSIG_2."</td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_real'] ? "<input type='checkbox' name='signup_real' value='1'  checked>" : "<input type='checkbox' name='signup_real' value='1'>")."
</td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_real_req'] ? "<input type='checkbox' name='signup_real_req' value='1'  checked>" : "<input type='checkbox' name='signup_real_req' value='1'>")."
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".CUSTSIG_3."</td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_url'] ? "<input type='checkbox' name='signup_url' value='1'  checked>" : "<input type='checkbox' name='signup_url' value='1'>")."
</td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_url_req'] ? "<input type='checkbox' name='signup_url_req' value='1'  checked>" : "<input type='checkbox' name='signup_url_req' value='1'>")."
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>ICQ: </td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_icq'] ? "<input type='checkbox' name='signup_icq' value='1'  checked>" : "<input type='checkbox' name='signup_icq' value='1'>")."
</td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_icq_req'] ? "<input type='checkbox' name='signup_icq_req' value='1'  checked>" : "<input type='checkbox' name='signup_icq_req' value='1'>")."
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>Aim: </td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_aim'] ? "<input type='checkbox' name='signup_aim' value='1'  checked>" : "<input type='checkbox' name='signup_aim' value='1'>")."
</td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_aim_req'] ? "<input type='checkbox' name='signup_aim_req' value='1'  checked>" : "<input type='checkbox' name='signup_aim_req' value='1'>")."
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>MSN: </td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_msn'] ? "<input type='checkbox' name='signup_msn' value='1'  checked>" : "<input type='checkbox' name='signup_msn' value='1'>")."
</td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_msn_req'] ? "<input type='checkbox' name='signup_msn_req' value='1'  checked>" : "<input type='checkbox' name='signup_msn_req' value='1'>")."
</td>
</tr>




<tr>
<td style='width:50%' class='forumheader3'>".CUSTSIG_4."</td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_dob'] ? "<input type='checkbox' name='signup_dob' value='1'  checked>" : "<input type='checkbox' name='signup_dob' value='1'>")."
</td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_dob_req'] ? "<input type='checkbox' name='signup_dob_req' value='1'  checked>" : "<input type='checkbox' name='signup_dob_req' value='1'>")."
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".CUSTSIG_5."</td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_loc'] ? "<input type='checkbox' name='signup_loc' value='1'  checked>" : "<input type='checkbox' name='signup_loc' value='1'>")."
</td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_loc_req'] ? "<input type='checkbox' name='signup_loc_req' value='1'  checked>" : "<input type='checkbox' name='signup_loc_req' value='1'>")."
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".CUSTSIG_6."</td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_sig'] ? "<input type='checkbox' name='signup_sig' value='1'  checked>" : "<input type='checkbox' name='signup_sig' value='1'>")."
</td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_sig_req'] ? "<input type='checkbox' name='signup_sig_req' value='1'  checked>" : "<input type='checkbox' name='signup_sig_req' value='1'>")."
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".CUSTSIG_7."</td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_avt'] ? "<input type='checkbox' name='signup_avt' value='1'  checked>" : "<input type='checkbox' name='signup_avt' value='1'>")."
</td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_avt_req'] ? "<input type='checkbox' name='signup_avt_req' value='1'  checked>" : "<input type='checkbox' name='signup_avt_req' value='1'>")."
</td>
</tr>";

$text .="
<tr>
<td style='width:50%' class='forumheader3'>".CUSTSIG_8."</td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_zone'] ? "<input type='checkbox' name='signup_zone' value='1'  checked>" : "<input type='checkbox' name='signup_zone' value='1'>")."
</td>
<td style='width:25%' class='forumheader3'>".
($pref['signup_zone_req'] ? "<input type='checkbox' name='signup_zone_req' value='1'  checked>" : "<input type='checkbox' name='signup_zone_req' value='1'>")."
</td>
</tr>";

*/
// Custom Fields.

if($sql -> db_Select("core", " e107_value", " e107_name='user_entended'")){
        $row = $sql -> db_Fetch();
        $user_entended = unserialize($row[0]);
        $c=0;

        $user_pref = unserialize($user_prefs);

        while(list($key, $u_entended) = each($user_entended)){
                if($u_entended){
                                $ut = explode("|",$u_entended);
                                $u_name = ($ut[0] != "") ? $ut[0] : $u_entended;
                                $u_type = $ut[1];
                                $u_value = $ut[2];

        $signup_ext = "signup_ext";
        $text .="
                <tr>
                <td style='width:50%' class='forumheader3'>".$u_name." <span class='smalltext'>(custom field)</span></td>
                <td style='width:25%' class='forumheader3'>".
                ($pref['signup_ext'.$key] ? "<input type='checkbox' name='signup_ext".$key."' value='1'  checked>" : "<input type='checkbox' name='signup_ext".$key."' value='1'>")."
                </td>
        <td style='width:25%' class='forumheader3'>".
                ($pref['signup_ext_req'.$key] ? "<input type='checkbox' name='signup_ext_req".$key."' value='1'  checked>" : "<input type='checkbox' name='signup_ext_req".$key."' value='1'>")."
                </td>
                </tr>";

              }
           }
           }





$text .="</table></div>";



 // Security Options. .



$text .="

<div style='text-align:center'><div class='caption' title='".PRFLAN_80."' style='cursor:hand;text-align:left;border:1px solid black;width:95%' onClick=\"expandit(this)\">".PRFLAN_47."</div>
<div id='security' style='text-align:center; display:none'>
<table style='width:95%' class='fborder' cellspacing='1' cellpadding='0'>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_60."<br /><span class='smalltext'>".PRFLAN_61."</span> </td>
<td style='width:50%; text-align:right' class='forumheader3'>".
($pref['ssl_enabled'] ? "<input type='checkbox' name='ssl_enabled' value='1'  checked>" : "<input type='checkbox' name='ssl_enabled' value='1'>")."
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_76.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>".
($pref['signcode'] ? "<input type='checkbox' name='signcode' value='1'  checked>" : "<input type='checkbox' name='signcode' value='1'>")."
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_81.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>".
($pref['logcode'] ? "<input type='checkbox' name='logcode' value='1'  checked>" : "<input type='checkbox' name='logcode' value='1'>")."
</td>
</tr>


<td style='width:50%' class='forumheader3'>".PRFLAN_48.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>".
($user_tracking == "cookie" ? "<input type='radio' name='user_tracking' value='cookie' checked> ".PRFLAN_49 : "<input type='radio' name='user_tracking' value='cookie'> ".PRFLAN_49).
($user_tracking == "session" ? "<input type='radio' name='user_tracking' value='session' checked> ".PRFLAN_50 : "<input type='radio' name='user_tracking' value='session'> ".PRFLAN_50)."
<br />
".PRFLAN_55.": <input class='tbox' type='text' name='cookie_name' size='20' value='".$pref['cookie_name']."' maxlength='20' />
</td>
</tr>


<td style='width:50%' class='forumheader3'>".PRFLAN_40.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>";
if($profanity_filter == 1){
        $text .= "<input type='checkbox' name='profanity_filter' value='1'  checked>";
}else{
        $text .= "<input type='checkbox' name='profanity_filter' value='1'>";
}

$text .= "(".PRFLAN_41.")
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_42.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input class='tbox' type='text' name='profanity_replace' size='30' value='$profanity_replace' maxlength='20' />
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_43.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<textarea class='tbox' name='profanity_words' cols='59' rows='2'>".$profanity_words."</textarea>
<br />".PRFLAN_44."
</td>
</tr>

</table></div>";

$text .="
<div style='text-align:center'><div class='caption' title='".PRFLAN_80."' style='cursor:hand;text-align:left;border:1px solid black;width:95%' onClick=\"expandit(this)\">".PRFLAN_62."</div>
<div id='mail' style='text-align:center; display:none'>
<table style='width:95%' class='fborder' cellspacing='1' cellpadding='0'>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_63."<br /><span class='smalltext'>".PRFLAN_64."</span></td>
<td style='width:50%; text-align:right' class='forumheader3'><input class='button' type='submit' name='testemail' value='".PRFLAN_65." ".SITEADMINEMAIL."' />
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_70."<br /><span class='smalltext'>".PRFLAN_71."</span></td>
<td style='width:50%; text-align:right' class='forumheader3'>".
($pref['smtp_enable'] ? "<input type='checkbox' name='smtp_enable' value='1' checked>" : "<input type='checkbox' name='smtp_enable' value='1'>")." </td>
</tr>


<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_72.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input class='tbox' type='text' name='smtp_server' size='30' value='".$pref['smtp_server']."' maxlength='50' />
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_73.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input class='tbox' type='text' name='smtp_username' size='30' value='".$pref['smtp_username']."' maxlength='50' />
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_74.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input class='tbox' type='password' name='smtp_password' size='30' value='".$pref['smtp_password']."' maxlength='50' />
</td>
</tr></table></div>";

$text .="<div id='cookie' style='text-align:center; display:none'>
<table style='width:95%' class='fborder' cellspacing='1' cellpadding='0'>




</table></div>";


$text .="

<div style='text-align:center'><div class='caption' title='".PRFLAN_80."' style='cursor:hand;text-align:left;border:1px solid black;width:95%' onClick=\"expandit(this)\">e107</div>
<div id='update' style='text-align:center; display:none'>
<table style='width:95%' class='fborder' cellspacing='1' cellpadding='0'>".$auth_dropdown."

<tr>
<td colspan='2' class='forumheader3'>
<div style='text-align:center'><input class='button' type='submit' name='newver' value='".PRFLAN_51."' /></div>
</td>
</tr></table></div>";

$text .="<div style='text-align:center;'>
<table style='width:95%' class='fborder' cellspacing='1' cellpadding='0'>  <tr style='vertical-align:top'>
<td colspan='2'  style='text-align:center' class='forumheader3'>
<br />
<input class='caption' type='submit' name='updateprefs' value='".PRFLAN_52."' />
</td>
</tr>
</table>
</form>
</div></div>";




$ns -> tablerender("<div style='text-align:center'>".PRFLAN_53."</div>", $text);

require_once("footer.php");
?>