<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/usersettings.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");
if(USER == FALSE && ADMIN == FALSE){ header("location:".e_HTTP."index.php"); exit; }
require_once(e_BASE."classes/ren_help.php");


if(e_QUERY && !ADMIN){
	header("location:usersettings.php");
	exit;
}
$aj = new textparse;
$_uid = e_QUERY;

if(IsSet($_POST['_uid'])){ $_uid = $_POST['_uid']; }
require_once(HEADERF);

if(IsSet($_POST['updatesettings'])){

	if($_POST['password1'] != $_POST['password2']){
		$error .= LAN_105."<br />";
	}

	if($_POST['password1'] =="" || $_POST['password2'] = ""){
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

// ------------------------------------------------------------------------------------------------------------------------------------- avatar upload


	
		require_once(e_BASE."classes/upload_handler.php");
		if($uploaded = file_upload("files/public/avatars/", TRUE)){
			$_POST['image'] = "-upload-".$uploaded[0]['name'];
		}


// -------------------------------------------------------------------------------------------------------------------------------------


	if(!$error){
		if($_uid != ""){ $inp = $_uid; }else{ $inp = USERID; }
		$_POST['signature'] = $aj -> tp($_POST['signature'], "off");
		$sql -> db_Update("user", "user_password='$password', user_email='".$_POST['email']."', user_homepage='".$_POST['website']."', user_icq='".$_POST['icq']."', user_aim='".$_POST['aim']."', user_msn='".$_POST['msn']."', user_location='".$_POST['location']."', user_birthday='".$birthday."', user_signature='".$_POST['signature']."', user_image='".$_POST['image']."', user_timezone='".$_POST['user_timezone']."', user_hideemail='".$_POST['hideemail']."', user_login='".$_POST['realname']."' WHERE user_id='".$inp."' ");

		$text = "<div style='text-align:center'>".LAN_150."</div>";
		$ns -> tablerender(LAN_151, $text);
	}
}

if($error != ""){
	$ns -> tablerender("<div style='text-align:center'>".LAN_20."</div>", $error);
}

if($_uid != ""){
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

require_once(e_BASE."classes/form_handler.php");
$rs = new form;

$text = (e_QUERY ? $rs -> form_open("post", e_SELF."?".$user_id, "settings", "", "enctype='multipart/form-data'") : $rs -> form_open("post", e_SELF, "settings", "", "enctype='multipart/form-data'"));

$text .= "<div style='text-align:center'>
<table style='width:85%'>
<tr>
<td style='width:20%' class='forumheader3'>".LAN_7."</td>
<td style='width:80%; text-align:right' class='forumheader3'>".
$rs -> form_text("name", 20, $name, 100, "tbox", TRUE)
."</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_308."</td>
<td style='width:80%; text-align:right' class='forumheader3'>
".$rs -> form_text("realname", 60, $user_login, 100)."
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_152."</td>
<td style='width:80%; text-align:right' class='forumheader3'>
".$rs -> form_password("password1", 40, "", 20)."
<br />
<span class='smalltext'>".LAN_401."</span>
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_153."</td>
<td style='width:80%; text-align:right' class='forumheader3'>
".$rs -> form_password("password2", 40, "", 20)."
<br />
<span class='smalltext'>".LAN_401."</span>
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_112."</td>
<td style='width:80%; text-align:right' class='forumheader3'>
".$rs -> form_text("email", 60, $email, 100)."
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_113."</td>
<td style='width:80%; text-align:right' class='forumheader3'>".
($hideemail ? $rs ->form_radio("hideemail", 1, 1)." Yes&nbsp;&nbsp;".$rs ->form_radio("hideemail", 0)." No" : $rs ->form_radio("hideemail", 1)." Yes&nbsp;&nbsp;".$rs ->form_radio("hideemail", 0, 1)." No")."
<br />
<span class='smalltext'>".LAN_114."

</td></tr><tr>
<td style='width:20%' class='forumheader3'>".LAN_144."</td>
<td style='width:80%; text-align:right' class='forumheader3'>
".$rs -> form_text("website", 60, $website, 150)."
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_115."</td>
<td style='width:80%; text-align:right' class='forumheader3'>
".$rs -> form_text("icq", 20, $icq, 10)."
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_116."</td>
<td style='width:80%; text-align:right' class='forumheader3'>
<input class='tbox' type='text' name='aim' size='30' value='$aim' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_117."</td>
<td style='width:80%; text-align:right' class='forumheader3'>
<input class='tbox' type='text' name='msn' size='30' value='$msn' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_118."</td>
<td style='width:80%; text-align:right' class='forumheader3'>



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
<td style='width:80%; text-align:right' class='forumheader3'>
<input class='tbox' type='text' name='location' size='60' value='$location' maxlength='200' />
</td>
</tr>

<tr>
<td style='width:20%' style='vertical-align:top' class='forumheader3'>".LAN_120."</td>
<td style='width:80%; text-align:right' class='forumheader3'>
<textarea class='tbox' name='signature' cols='70' rows='4'>$signature</textarea>
<br />
<input class='helpbox' type='text' name='helpb' size='90' />
<br />
".ren_help("addtext")."
</td>
</tr>

<tr>
<td style='width:20%; vertical-align:top' class='forumheader3'>".LAN_121."<br /><span class='smalltext'>(Type path or choose avatar)</span></td>
<td style='width:80%; text-align:right' class='forumheader3'>
<input class='tbox' type='text' name='image' size='60' value='$image' maxlength='100' />

<input class='button' type ='button' style=''width: 35px'; cursor:hand' size='30' value='Choose avatar' onClick='expandit(this)'>
<div style='display:none' style=&{head};>";
$avatarlist[0] = "";
$handle=opendir("themes/shared/avatars/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".."){
		$avatarlist[] = $file;
	}
}
closedir($handle);

for($c=1; $c<=(count($avatarlist)-1); $c++){
	$text .= "<a href='javascript:addtext2(\"$avatarlist[$c]\")'><img src='themes/shared/avatars/".$avatarlist[$c]."' style='border:0' alt='' /></a> ";
}

$text .= "<br />
</div>";

$pref['avatar_upload'][1] = 1;

if($pref['avatar_upload'][1]){
	$text .= "<br /><span class='smalltext'>Upload your avatar</span> <input class='tbox' name='file_userfile[]' type='file' size='47'>";
}





$text .= "</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_122."</td>
<td style='width:80%; text-align:right' class='forumheader3'>
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

<tr style='vertical-align:top'> 
<td colspan='2' style='text-align:center' class='forumheader3'>
<br />
<input class='button' type='submit' name='updatesettings' value='".LAN_154."' />
</td>
</tr>
</table>
</div>
<input type='hidden' name='_uid' value='$_uid'>
<input type='hidden' name='_pw' value='$user_password'>
</form>
";

$ns -> tablerender(LAN_155, $text);

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

function timezone(){
	/*
	# Render style table
	# - parameters		none
	# - return				timezone arrays
	# - scope					public
	*/
	global $timezone, $timearea;
	$timezone = array("-12", "-11", "-10", "-9", "-8", "-7", "-6", "-5", "-4", "-3", "-2", "-1", "GMT", "+1", "+2", "+3", "+4", "+5", "+6", "+7", "+8", "+9", "+10", "+11", "+12", "+13");
	$timearea = array("International DateLine West", "Samoa", "Hawaii", "Alaska", "Pacific Time (US and Canada)", "Mountain Time (US and Canada)", "Central Time (US and Canada), Central America", "Eastern Time (US and Canada)", "Atlantic Time (Canada)", "Greenland, Brasilia, Buenos Aires, Georgetown", "Mid-Atlantic", "Azores", "GMT - UK, Ireland, Lisbon", "West Central Africa, Western Europe", "Greece, Egypt, parts of Africa", "Russia, Baghdad, Kuwait, Nairobi", "Abu Dhabi, Kabul", "Islamabad, Karachi", "Astana, Dhaka", "Bangkok, Rangoon", "Hong Kong, Singapore, Perth, Beijing", "Tokyo, Seoul", "Brisbane, Canberra, Sydney, Melbourne", "Soloman Islands", "New Zealand", "Nuku'alofa");
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

require_once(FOOTERF);
// shortcut code added by Chris McLeod 01.11.02 //
?>
<script type="text/javascript">
function addtext(sc){
	document.settings.signature.value += sc;
}
function addtext2(sc){
	document.settings.image.value = sc;
}
function help(help){
	document.settings.helpb.value = help;
}
</script>