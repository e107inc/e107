<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/admin/prefs.php															|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/
require_once("../class2.php");
$sql -> db_Select("e107");
list($e107_author, $e107_url, $e107_version, $e107_build, $e107_datestamp) = $sql-> db_Fetch();
if(IsSet($_POST['newver'])){ header("location:http://jalist.com/check.php?".$e107_version."-".$e107_build); }

if(!getperms("1")){ header("location:../index.php"); }

//if($_POST['user_reg'] == 1){
//	if(!$sql -> db_Select("links", "*", "link_name='Members' ")){
//		$sql -> db_Insert("links",  "0, 'Members', 'user.php', '', '', '1', '0', '0' ");
//	}
//}

if(IsSet($_POST['updateprefs'])){
	$sql -> db_Update("prefs", "pref_value='".$_POST['sitename']."' WHERE pref_name='sitename' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['siteurl']."' WHERE pref_name='siteurl' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['sitebutton']."' WHERE pref_name='sitebutton' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['sitetag']."' WHERE pref_name='sitetag' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['sitedescription']."' WHERE pref_name='sitedescription' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['siteadmin']."' WHERE pref_name='siteadmin' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['siteadminemail']."' WHERE pref_name='siteadminemail' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['sitetheme']."' WHERE pref_name='sitetheme' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['sitedisclaimer']."' WHERE pref_name='sitedisclaimer' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['newsposts']."' WHERE pref_name='newsposts' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['flood_protect']."' WHERE pref_name='flood_protect' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['anon_post']."' WHERE pref_name='anon_post' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['user_reg']."' WHERE pref_name='user_reg' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['profanity_filter']."' WHERE pref_name='profanity_filter' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['profanity_replace']."' WHERE pref_name='profanity_replace' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['use_coppa']."' WHERE pref_name='use_coppa' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['shortdate']."' WHERE pref_name='shortdate' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['longdate']."' WHERE pref_name='longdate' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['forumdate']."' WHERE pref_name='forumdate' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['sitelanguage']."' WHERE pref_name='sitelanguage' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['sitelocale']."' WHERE pref_name='sitelocale' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['time_offset']."' WHERE pref_name='time_offset' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['flood_hits']."' WHERE pref_name='flood_hits' ");
	$sql -> db_Update("prefs", "pref_value='".$_POST['flood_time']."' WHERE pref_name='flood_time' ");
	header("location:prefs.php");
}

//added prefs since v2.0 ...
$flood_protect=  $pref['flood_protect'][1];
$anon_post = $pref['anon_post'][1];
$user_reg = $pref['user_reg'][1];
$profanity_filter = $pref['profanity_filter'][1];
$profanity_replace = $pref['profanity_replace'][1];
$use_coppa = $pref['use_coppa'][1];
$shortdate = $pref['shortdate'][1];
$longdate = $pref['longdate'][1];
$forumdate = $pref['forumdate'][1];
$sitelocale = $pref['sitelocale'][1];
$time_offset = $pref['time_offset'][1];
$flood_hits = $pref['flood_hits'][1];
$flood_time = $pref['flood_time'][1];

require_once("auth.php");

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$handle=opendir("../themes/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".." && $file != "templates" && $file != "shared"){
		$dirlist[] = $file;
	}
}
closedir($handle);

$handle=opendir("../languages/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".."){
		$lanlist[] = eregi_replace("lan_|.php", "", $file);
	}
}
closedir($handle);


$text = "<div style=\"text-align:center\">
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<table style=\"width:95%\">
<tr>

<td colspan=\"2\">
<div class=\"border\"><div class=\"caption\">Site Information</div></div>
</td>
</tr><tr>

<td style=\"width:40%\">Site Name: </td>
<td style=\"width:60%\">
<input class=\"tbox\" type=\"text\" name=\"sitename\" size=\"60\" value=\"".SITENAME."\" maxlength=\"100\" />
</td>
</tr>
<tr>

<tr>
<td style=\"width:40%\">Site URL: </td>
<td style=\"width:60%\">
<input class=\"tbox\" type=\"text\" name=\"siteurl\" size=\"60\" value=\"".SITEURL."\" maxlength=\"150\" />
</td>
</tr>
<tr>

<tr>
<td style=\"width:40%\">Site Link Button: </td>
<td style=\"width:60%\">
<input class=\"tbox\" type=\"text\" name=\"sitebutton\" size=\"60\" value=\"".SITEBUTTON."\" maxlength=\"150\" />
</td>
</tr>
<tr>

<td style=\"width:40%\">Site Tagline: </td>
<td style=\"width:60%\">
<textarea class=\"tbox\" name=\"sitetag\" cols=\"59\" rows=\"3\">".SITETAG."</textarea>
</td>
</tr>
<tr>

<td style=\"width:40%\">Site Description: </td>
<td style=\"width:60%\">
<textarea class=\"tbox\" name=\"sitedescription\" cols=\"59\" rows=\"3\">".SITEDESCRIPTION."</textarea>
</td>
</tr>
<tr>

<td style=\"width:40%\">Main site admin: </td>
<td style=\"width:60%\">
<input class=\"tbox\" type=\"text\" name=\"siteadmin\" size=\"60\" value=\"".SITEADMIN."\" maxlength=\"100\" />
</td>
</tr>
<tr>

<td style=\"width:40%\">Main site admin email: </td>
<td style=\"width:60%\">
<input class=\"tbox\" type=\"text\" name=\"siteadminemail\" size=\"60\" value=\"".SITEADMINEMAIL."\" maxlength=\"100\" />
</td>
</tr>
<tr>

<td style=\"width:40%\">Site Disclaimer: </td>
<td style=\"width:60%\">
<textarea class=\"tbox\" name=\"sitedisclaimer\" cols=\"59\" rows=\"3\">".SITEDISCLAIMER."</textarea>
</td>
</tr>


<tr><td colspan=\"2\"><br /></td></tr>

<tr>
<td colspan=\"2\">
<div class=\"border\"><div class=\"caption\">Theme</div></div>
</td>
</tr><tr>

<td style=\"width:40%\">Site Theme: </td>
<td style=\"width:60%\">
<select name=\"sitetheme\" class=\"tbox\">\n";
$counter = 0;
while($dirlist[$counter]){
	if($dirlist[$counter] == $pref['sitetheme'][1]){
		$text .= "<option selected>".$dirlist[$counter]."</option>\n";
	}else{
		$text .= "<option>".$dirlist[$counter]."</option>\n";
	}
	$counter++;
}
$text .= "</select>
</td>
</tr>

<tr><td colspan=\"2\"><br /></td></tr>

<td colspan=\"2\">
<div class=\"border\"><div class=\"caption\">Language</div></div>
</td>
</tr>
<tr>

<td style=\"width:40%\">Site Language: </td>
<td style=\"width:60%\">
<select name=\"sitelanguage\" class=\"tbox\">\n";
$counter = 0;
$sellan = eregi_replace("lan_*.php", "", $pref['sitelanguage'][1]);
while($lanlist[$counter]){
	if($lanlist[$counter] == $sellan){
		$text .= "<option selected>".$lanlist[$counter]."</option>\n";
	}else{
		$text .= "<option>".$lanlist[$counter]."</option>\n";
	}
	$counter++;
}
$text .= "</select>
</td>
</tr>

<td style=\"width:40%; vertical-align:top\">Site Locale: </td>
<td style=\"width:60%\">
<input class=\"tbox\" type=\"text\" name=\"sitelocale\" size=\"15\" value=\"$sitelocale\" maxlength=\"5\" /> 
<br /> (Please note, not all servers supports locales - for more information see <a href=\"http://www.php.net/manual/en/function.setlocale.php\">the setlocale page at php.net</a>)
</td>
</tr>

<tr><td colspan=\"2\"><br /></td></tr>

<tr>
<td colspan=\"2\">
<div class=\"border\"><div class=\"caption\">News options</div></div>
</td>
</tr><tr>

<td style=\"width:40%\">News posts to display per page?: </td>
<td style=\"width:60%\">
<select name=\"newsposts\" class=\"tbox\">";
if(ITEMVIEW == 5){
	$text .= "<option selected>5</option>\n";
}else{
	$text .= "<option>5</option>\n";
}
if(ITEMVIEW == 10){
	$text .= "<option selected>10</option>\n";
}else{
	$text .= "<option>10</option>\n";
}
if(ITEMVIEW == 15){
	$text .= "<option selected>15</option>\n";
}else{
	$text .= "<option>15</option>\n";
}
if(ITEMVIEW == 20){
	$text .= "<option selected>20</option>\n";
}else{
	$text .= "<option>20</option>\n";
}
if(ITEMVIEW == 25){
	$text .= "<option selected>5</option>\n";
}else{
	$text .= "<option>25</option>\n";
}

$text .= "</select>
</td>
</tr>

<tr><td colspan=\"2\"><br /></td></tr>

<tr>
<td colspan=\"2\">
<div class=\"border\"><div class=\"caption\">Date display options</div></div>
</td>
</tr>
<tr>";

$ga = new convert;
$date1 = $ga -> convert_date(time(), "short");
$date2 = $ga -> convert_date(time(), "long");
$date3 = $ga -> convert_date(time(), "forum");


$text .= "<td style=\"width:40%\">Short date format: </td>
<td style=\"width:60%\">
<input class=\"tbox\" type=\"text\" name=\"shortdate\" size=\"15\" value=\"$shortdate\" maxlength=\"50\" /> 
example: $date1
</td>
</tr>

<tr>
<td style=\"width:40%\">Long date format: </td>
<td style=\"width:60%\">
<input class=\"tbox\" type=\"text\" name=\"longdate\" size=\"15\" value=\"$longdate\" maxlength=\"50\" /> 
example: $date2
</td>
</tr>

<tr>
<td style=\"width:40%\">Forum date format: </td>
<td style=\"width:60%\">
<input class=\"tbox\" type=\"text\" name=\"forumdate\" size=\"15\" value=\"$forumdate\" maxlength=\"50\" /> 
example: $date3
</td>
</tr>

<tr>
<td colspan=\"2\" style=\"text-align:center\">
(For more information on date formats see the <a href=\"http://www.php.net/manual/en/function.date.php\" target=\"_blank\">date function page at php.net</a>)
</td>
</tr>

<tr>
<td style=\"width:40%\">Time offset: </td>
<td style=\"width:60%\">
<select name=\"time_offset\" class=\"tbox\">\n";
$toffset = array("-12", "-11", "-10", "-9", "-8", "-7", "-6", "-5", "-4", "-3", "-2", "-1", "0", "+1", "+2", "+3", "+4", "+5", "+6", "+7", "+8", "+9", "+10", "+11", "+12", "+13");
$counter = 0;
while($toffset[$counter] != ""){
	if($toffset[$counter] == $pref['time_offset'][1]){
		$text .= "<option selected>".$toffset[$counter]."</option>\n";
	}else{
		$text .= "<option>".$toffset[$counter]."</option>\n";
	}
	$counter++;
}
$text .= "</select>
</td></tr>

<tr>
<td colspan=\"2\" style=\"text-align:center\">
(Example, if you set this to +2, all times on your site will have two hours added to them)
</td>
</tr>

<tr><td colspan=\"2\"><br /></td></tr>

<tr>
<td colspan=\"2\">
<div class=\"border\"><div class=\"caption\">User registration/posting</div></div>
</td>
</tr><tr>

<td style=\"width:40%\">Activate user registration system?: </td>
<td style=\"width:60%\">";
if($user_reg == 1){
	$text .= "<input type=\"checkbox\" name=\"user_reg\" value=\"1\"  checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"user_reg\" value=\"1\">";
}

$text .= " (allow users to register as members on your site)

</td>
</tr>
<tr>
<td style=\"width:40%\">Allow anonymous posting?: </td>
<td style=\"width:60%\">";
if($anon_post == 1){
	$text .= "<input type=\"checkbox\" name=\"anon_post\" value=\"1\"  checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"anon_post\" value=\"1\">";
}

$text .= "(if left unchecked only registered members can post comments etc)
</td>
</tr>
<tr>

<tr><td colspan=\"2\"><br /></td></tr>

<td colspan=\"2\">
<div class=\"border\"><div class=\"caption\">Security</div></div>
</td>
</tr><tr>

<td style=\"width:40%\">Enable flood protection?: </td>
<td style=\"width:60%\">";
if($flood_protect == 1){
	$text .= "<input type=\"checkbox\" name=\"flood_protect\" value=\"1\"  checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"flood_protect\" value=\"1\">";
}

$text .= "
</td>
</tr>
<tr>
<td style=\"width:40%\">Flood hits: </td>
<td style=\"width:60%\">
<input class=\"tbox\" type=\"text\" name=\"flood_hits\" size=\"10\" value=\"$flood_hits\" maxlength=\"4\" />
</td>
</tr>

</td>
</tr>
<tr>
<td style=\"width:40%; vertical-align:top\">Flood time: </td>
<td style=\"width:60%\">
<input class=\"tbox\" type=\"text\" name=\"flood_time\" size=\"10\" value=\"$flood_time\" maxlength=\"20\" />
<br />
example, flood hits set to 100 and flood time set to 60: if any single page on your site gets 100 hits in 60 seconds the page will be inaccessable for a further 60 seconds. 
</td>
</tr>

<tr>

<tr><td colspan=\"2\"><br /></td></tr>

<td colspan=\"2\">
<div class=\"border\"><div class=\"caption\">Protection of minors options</div></div>
</td>
</tr><tr>

<td style=\"width:40%\">Filter profanities?: </td>
<td style=\"width:60%\">";
if($profanity_filter == 1){
	$text .= "<input type=\"checkbox\" name=\"profanity_filter\" value=\"1\"  checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"profanity_filter\" value=\"1\">";
}

$text .= "(if checked swearing will be replaced with string below)
</td>
</tr>

<tr>
<td style=\"width:40%\">Replace string: </td>
<td style=\"width:60%\">
<input class=\"tbox\" type=\"text\" name=\"profanity_replace\" size=\"30\" value=\"$profanity_replace\" maxlength=\"20\" />
</td>
</tr>

<tr>

<td style=\"width:40%\">Use COPPA on signup page?: </td>
<td style=\"width:60%\">";
if($use_coppa == 1){
	$text .= "<input type=\"checkbox\" name=\"use_coppa\" value=\"1\"  checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"use_coppa\" value=\"1\">";
}


$text .= "(for more info on COPPA see <a href=\"http://www.cdt.org/legislation/105th/privacy/coppa.html\">here</a>)
</td>
</tr>

<tr><td colspan=\"2\"><br /></td></tr>

<tr>
<td colspan=\"2\">
<div class=\"border\"><div class=\"caption\">e107</div></div>
<br />
<div style=\"text-align:center\"><input class=\"button\" type=\"submit\" name=\"newver\" value=\"Click here to check latest version of e107\" /></div>
</td>
</tr><tr>

<tr><td colspan=\"2\"><br /></td></tr>

<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\">
<br />
<input class=\"button\" type=\"submit\" name=\"updateprefs\" value=\"Update Prefs\" />
</td>
</tr>
</table>
</form>
</div>";




$ns -> tablerender("<div style=\"text-align:center\">Site Preferences</div>", $text);

require_once("footer.php");
?>	