<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/prefs.php																	|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/
require_once("class2.php");
if(USER == FALSE && ADMIN == FALSE){ header("location:index.php"); }

$_uid = $_SERVER['QUERY_STRING'];

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
		$sql -> db_Update("user", "user_password='$password', user_email='".$_POST['email']."', user_homepage='".$_POST['website']."', user_icq='".$_POST['icq']."', user_aim='".$_POST['aim']."', user_msn='".$_POST['msn']."', user_location='".$_POST['location']."', user_birthday='".$_POST['birthday']."', user_signature='".$_POST['signature']."', user_image='".$_POST['image']."', user_timezone='".$_POST['user_timezone']."', user_hideemail='".$_POST['hideemail']."' WHERE user_id='".$inp."' ");

		$text = "<div style=\"text-align:center\">".LAN_150."</div>";
		$ns -> tablerender(LAN_151, $text);
		require_once(FOOTERF);
		exit;
	}
}

if($error != ""){
	$ns -> tablerender("<div style=\"text-align:center\">".LAN_20."</div>", $error);
}

if($_uid != ""){
	$sql -> db_Select("user", "*", "user_id='".$_uid."' ");
}else{
	$sql -> db_Select("user", "*", "user_id='".USERID."' ");
}
list($user_id, $name, $user_password, $user_sess, $email, $website, $icq, $aim, $msn, $location, $birthday, $signature, $image, $user_timezone, $hideemail, $user_join, $user_lastvisit, $user_currentvisit, $user_lastpost, $user_chats, $user_comments, $user_forums, $user_ip, $user_ban, $user_new, $user_viewed, $user_prefs, $user_new, $user_viewed, $user_visits, $user_admin)  = $sql -> db_Fetch();

$text = "
<form  name=\"settings\" method=\"post\" action=\"".$_SERVER['PHP_SELF']."?stage2\">\n
<table style=\"width:95%\">
<tr>
<td style=\"width:20%\">".LAN_7."</td>
<td style=\"width:80%\">
$name
</td>
</tr>

<tr>
<td style=\"width:20%\"><u>".LAN_152."</u></td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"password\" name=\"password1\" size=\"40\" value=\"\" maxlength=\"20\" /> (case sensitive)
</td>
</tr>

<tr>
<td style=\"width:20%\"><u>".LAN_153."</u></td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"password\" name=\"password2\" size=\"40\" value=\"\" maxlength=\"20\" /> (case sensitive)
</td>
</tr>

<tr>
<td style=\"width:20%\"><u>".LAN_112."</u></td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"email\" size=\"60\" value=\"$email\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:20%\">".LAN_113."</td>
<td style=\"width:80%\">";
if($hide_email == 1){
	$text .= "<input type=\"checkbox\" name=\"hideemail\" value=\"1\"  checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"hideemail\" value=\"1\">";
}

$text .= LAN_114."</td></tr><tr>
<td style=\"width:20%\">".LAN_144."</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"website\" size=\"60\" value=\"$website\" maxlength=\"150\" />
</td>
</tr>

<tr>
<td style=\"width:20%\">".LAN_115."</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"icq\" size=\"20\" value=\"$icq\" maxlength=\"10\" />
</td>
</tr>

<tr>
<td style=\"width:20%\">".LAN_116."</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"aim\" size=\"30\" value=\"$aim\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:20%\">".LAN_117."</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"msn\" size=\"30\" value=\"$msn\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:20%\">".LAN_118."</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"birthday\" size=\"12\" value=\"$birthday\" maxlength=\"20\" /> (yyyy/mm/dd)
</td>
</tr>

<tr>
<td style=\"width:20%\">".LAN_119."</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"location\" size=\"60\" value=\"$location\" maxlength=\"200\" />
</td>
</tr>

<tr>
<td style=\"width:20%\">".LAN_120."</td>
<td style=\"width:80%\">
<textarea class=\"tbox\" name=\"signature\" cols=\"70\" rows=\"4\">$signature</textarea>
<br />
<input class=\"fhelpbox\" type=\"text\" name=\"helpb\" size=\"90\" />
<br />
<input class=\"button\" type=\"button\" style=\"font-weight:bold; width: 35px\" value=\"b\" onclick=\"addtext('[b][/b]')\" onMouseOver=\"help('Bold text: [b]This text will be bold[/b]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" style=\"font-style:italic; width: 35px\" value=\"i\" onclick=\"addtext('[i][/i]')\" onMouseOver=\"help('Italic text: [i]This text will be italicised[/i]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" style=\"text-decoration: underline; width: 35px\" value=\"u\" onclick=\"addtext('[u][/u]')\" onMouseOver=\"help('Underline text: [u]This text will be underlined[/u]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" style=\"width: 35px\" value=\"img\" onclick=\"addtext('[img][/img]')\" onMouseOver=\"help('Insert image: [img]mypicture.jpg[/img]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" style=\"width: 35px\" value=\"cen\" onclick=\"addtext('[center][/center]')\" onMouseOver=\"help('Center align: [center]This text will be centered[/center]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"link\" onclick=\"addtext('[link=hyperlink url]hyperlink text[/link]')\" onMouseOver=\"help('Insert link: [link]http://mysite.com[/link] or [link=http://yoursite.com]Visit My Site[/link]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" style=\"width: 35px\" value=\"code\" onclick=\"addtext('[code][/code]')\" onMouseOver=\"help('Code - preformatted text: [code]\$var = foobah;[/code]')\" onMouseOut=\"help('')\">
</td>
</tr>

<tr>
<td style=\"width:20%\">".LAN_121."</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"image\" size=\"60\" value=\"$image\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:20%\">".LAN_122."</td>
<td style=\"width:80%\">
<select name=\"user_timezone\" class=\"tbox\">\n";

timezone();
$count = 0;
while($timezone[$count]){
	if($timezone[$count] == $user_timezone){
		$text .= "<option value=\"".$timezone[$count]."\" selected>(GMT".$timezone[$count].") ".$timearea[$count]."</option>\n";
	}else{
		$text .= "<option value=\"".$timezone[$count]."\">(GMT".$timezone[$count].") ".$timearea[$count]."</option>\n";
	}
	$count++;
}

$text .= "</select>
</td>
</tr>
</tr>
<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\">
<br />
<input class=\"button\" type=\"submit\" name=\"updatesettings\" value=\"".LAN_154."\" />
</td>
</tr>
</table>
<input type=\"hidden\" name=\"_uid\" value=\"$_uid\">
<input type=\"hidden\" name=\"_pw\" value=\"$user_password\">
</form>
<br />
<br />
<span class=\"smalltext\">
".LAN_10."
</span>
";

$ns -> tablerender(LAN_155, $text);

require_once(FOOTERF);
// shortcut code added by Chris McLeod 01.11.02 //
?>
<script type="text/javascript">
function addtext(sc){
	document.settings.signature.value += sc;
}
function help(help){
	document.settings.helpb.value = help;
}
</script>