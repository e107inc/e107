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
if(USER == FALSE && ADMIN == FALSE){ header("location:".e_HTTP."index.php"); }
require_once(e_BASE."classes/ren_help.php");


if(e_QUERY && !ADMIN){
	header("location:usersettings.php");
	exit;
}

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

	if($error == ""){
		if($_uid != ""){ $inp = $_uid; }else{ $inp = USERID; }
		$sql -> db_Update("user", "user_password='$password', user_email='".$_POST['email']."', user_homepage='".$_POST['website']."', user_icq='".$_POST['icq']."', user_aim='".$_POST['aim']."', user_msn='".$_POST['msn']."', user_location='".$_POST['location']."', user_birthday='".$_POST['birthday']."', user_signature='".$_POST['signature']."', user_image='".$_POST['image']."', user_timezone='".$_POST['user_timezone']."', user_hideemail='".$_POST['hideemail']."', user_login='".$_POST['realname']."' WHERE user_id='".$inp."' ");

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

$text = "
<form  name='settings' method='post' action='".e_SELF;
if(e_QUERY){
	$text .= "?".$user_id;
}
$text .= "'>\n
<table style='width:95%'>
<tr>
<td style='width:20%'>".LAN_7."</td>
<td style='width:80%'>
$name
</td>
</tr>

<tr>
<td style='width:20%'>".LAN_308."</td>
<td style='width:80%'>
<input class='tbox' type='text' name='realname' size='60' value='$user_login' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:20%'>".LAN_152."</td>
<td style='width:80%'>
<input class='tbox' type='password' name='password1' size='40' value='' maxlength='20' /> (case sensitive)
</td>
</tr>

<tr>
<td style='width:20%'>".LAN_153."</td>
<td style='width:80%'>
<input class='tbox' type='password' name='password2' size='40' value='' maxlength='20' /> (case sensitive)
</td>
</tr>

<tr>
<td style='width:20%'>".LAN_112."</td>
<td style='width:80%'>
<input class='tbox' type='text' name='email' size='60' value='$email' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:20%'>".LAN_113."</td>
<td style='width:80%'>";
if($hideemail == 1){
	$text .= "<input type='checkbox' name='hideemail' value='1'  checked>";
}else{
	$text .= "<input type='checkbox' name='hideemail' value='1'>";
}

$text .= LAN_114."</td></tr><tr>
<td style='width:20%'>".LAN_144."</td>
<td style='width:80%'>
<input class='tbox' type='text' name='website' size='60' value='$website' maxlength='150' />
</td>
</tr>

<tr>
<td style='width:20%'>".LAN_115."</td>
<td style='width:80%'>
<input class='tbox' type='text' name='icq' size='20' value='$icq' maxlength='10' />
</td>
</tr>

<tr>
<td style='width:20%'>".LAN_116."</td>
<td style='width:80%'>
<input class='tbox' type='text' name='aim' size='30' value='$aim' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:20%'>".LAN_117."</td>
<td style='width:80%'>
<input class='tbox' type='text' name='msn' size='30' value='$msn' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:20%'>".LAN_118."</td>
<td style='width:80%'>
<input class='tbox' type='text' name='birthday' size='12' value='$birthday' maxlength='20' /> (yyyy/mm/dd)
</td>
</tr>

<tr>
<td style='width:20%'>".LAN_119."</td>
<td style='width:80%'>
<input class='tbox' type='text' name='location' size='60' value='$location' maxlength='200' />
</td>
</tr>

<tr>
<td style='width:20%' style='vertical-align:top'>".LAN_120."</td>
<td style='width:80%'>
<textarea class='tbox' name='signature' cols='70' rows='4'>$signature</textarea>
<br />
<input class='helpbox' type='text' name='helpb' size='90' />
<br />
".ren_help("addtext")."
</td>
</tr>

<tr>
<td style='width:20%; vertical-align:top'>".LAN_121."<br /><span class='smalltext'>(Type path or choose avatar)</span></td>
<td style='width:80%'>
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
	$text .= "<a href='javascript:addtext2(\"$avatarlist[$c]\")'><img src='themes/shared/avatars/".$avatarlist[$c]."' style='border:0' alt='' /> ";
//	$text .= "<a href='javascript:addtext2('avatar_$c')'><img src='themes/shared/avatars/".$avatarlist[$c]."' style='border:0' alt='' />\n";
}

$text .= "<br />
</div>
</td>
</tr>

<tr>
<td style='width:20%'>".LAN_122."</td>
<td style='width:80%'>
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
<td colspan='2'  style='text-align:center'>
<br />
<input class='button' type='submit' name='updatesettings' value='".LAN_154."' />
</td>
</tr>
</table>
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