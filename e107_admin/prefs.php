<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	//prefs.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");

if(IsSet($_POST['newver'])){
	header("location:http://e107.org/index.php");
	exit;
}

if(!getperms("1")){ header("location:".e_BASE."index.php"); exit;}

if(IsSet($_POST['updateprefs'])){
	$aj = new textparse;
	$pref['sitename'] = $aj -> formtpa($_POST['sitename']);
	$pref['siteurl'] = $aj -> formtpa($_POST['siteurl']);
	$pref['sitebutton'] = $aj -> formtpa($_POST['sitebutton']);
	$pref['sitetag'] = $aj -> formtpa($_POST['sitetag']);
	$pref['sitedescription'] = $aj -> formtpa($_POST['sitedescription']);
	$pref['siteadmin'] = $aj -> formtpa($_POST['siteadmin']);
	$pref['siteadminemail'] = $aj -> formtpa($_POST['siteadminemail']);
	$pref['sitetheme'] = $_POST['sitetheme'];
	$pref['admintheme'] = $_POST['admintheme'];
	$pref['sitedisclaimer'] = $aj -> formtpa($_POST['sitedisclaimer']);
	$pref['newsposts'] = $_POST['newsposts'];
	$pref['flood_protect'] = $_POST['flood_protect'];
	$pref['anon_post'] = $_POST['anon_post'];
	$pref['user_reg'] = $_POST['user_reg'];
	$pref['profanity_filter'] = $_POST['profanity_filter'];
	$pref['profanity_replace'] = $aj -> formtpa($_POST['profanity_replace']);
	$pref['profanity_words'] = $aj -> formtpa($_POST['profanity_words']);
	$pref['use_coppa'] = $_POST['use_coppa'];
	$pref['shortdate'] = $_POST['shortdate'];
	$pref['longdate'] = $_POST['longdate'];
	$pref['forumdate'] = $_POST['forumdate'];
	$pref['sitelanguage'] = $_POST['sitelanguage'];
	$pref['time_offset'] = $_POST['time_offset'];
	$pref['flood_hits'] = $_POST['flood_hits'];
	$pref['flood_time'] = $_POST['flood_time'];
	$pref['user_reg_veri'] = $_POST['user_reg_veri'];
	$pref['user_tracking'] = $_POST['user_tracking'];
	$pref['displaythemeinfo'] = $_POST['displaythemeinfo'];
	$pref['displayrendertime'] = $_POST['displayrendertime'];
	$pref['displaysql'] = $_POST['displaysql'];
	$sql -> db_Delete("cache");
	save_prefs();
	header("location:".e_SELF);
	exit;
}


//added prefs since v2.0 ...
$flood_protect = $pref['flood_protect'];
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
$flood_hits = $pref['flood_hits'];
$flood_time = $pref['flood_time'];
$user_reg_veri = $pref['user_reg_veri'];
$user_tracking = $pref['user_tracking'];

require_once(e_ADMIN."auth.php");

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


$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:95%' class='fborder' cellspacing='1' cellpadding='0'>
<tr>

<td colspan='2'>
<div class='caption'>".PRFLAN_1."</div>
</td>
</tr><tr>

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

<tr>
<td colspan='2'>
<div class='border'><div class='caption'>".PRFLAN_10."</div></div>
</td>
</tr><tr>

<td style='width:50%' class='forumheader3'>".PRFLAN_11.": </td>
<td style='width:50%; text-align:right' class='forumheader3'><a href='".e_ADMIN."theme_prev.php'>".PRFLAN_12."</a> 
<select name='sitetheme' class='tbox'>\n";
$counter = 0;
while(IsSet($dirlist[$counter])){
	$text .= ($dirlist[$counter] == $pref['sitetheme'] ? "<option selected>".$dirlist[$counter]."</option>\n" : "<option>".$dirlist[$counter]."</option>\n");
	$counter++;
}
$text .= "</select>
</td>
</tr>

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
<td colspan='2'>
<div class='border'><div class='caption'>".PRFLAN_13."</div></div>
</td>
</tr>

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

<td colspan='2'>
<div class='border'><div class='caption'>".PRFLAN_17."</div></div>
</td>
</tr>
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
</tr>

<tr>
<td colspan='2'>
<div class='border'><div class='caption'>".PRFLAN_19."</div></div>
</td>
</tr><tr>

<td style='width:50%' class='forumheader3'>".PRFLAN_20.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<select name='newsposts' class='tbox'>";
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

<tr>
<td colspan='2'>
<div class='border'><div class='caption'>".PRFLAN_21."</div></div>
</td>
</tr>
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
$toffset = array("-12", "-11", "-10", "-9", "-8", "-7", "-6", "-5", "-4", "-3", "-2", "-1", "0", "+1", "+2", "+3", "+4", "+5", "+6", "+7", "+8", "+9", "+10", "+11", "+12", "+13");
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
<td colspan='2'>
<div class='border'><div class='caption'>".PRFLAN_28."</div></div>
</td>
</tr><tr>

<td style='width:50%' class='forumheader3'>".PRFLAN_29.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>";
if($user_reg == 1){
	$text .= "<input type='checkbox' name='user_reg' value='1'  checked>";
}else{
	$text .= "<input type='checkbox' name='user_reg' value='1'>";
}

$text .= " (".PRFLAN_30.")



</tr><tr>

<td style='width:50%' class='forumheader3'>".PRFLAN_31.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>";
if($user_reg_veri == 1){
	$text .= "<input type='checkbox' name='user_reg_veri' value='1'  checked>";
}else{
	$text .= "<input type='checkbox' name='user_reg_veri' value='1'>";
}

$text .= "
</td>
</tr>
<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_32.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>";
if($anon_post == 1){
	$text .= "<input type='checkbox' name='anon_post' value='1'  checked>";
}else{
	$text .= "<input type='checkbox' name='anon_post' value='1'>";
}

$text .= "(".PRFLAN_33.")
</td>
</tr>
<tr>

<td colspan='2'>
<div class='border'><div class='caption'>".PRFLAN_34."</div></div>
</td>
</tr><tr>

<td style='width:50%' class='forumheader3'>".PRFLAN_35.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>";
if($flood_protect == 1){
	$text .= "<input type='checkbox' name='flood_protect' value='1'  checked>";
}else{
	$text .= "<input type='checkbox' name='flood_protect' value='1'>";
}

$text .= "
</td>
</tr>
<tr>
<td style='width:50%' class='forumheader3'>".PRFLAN_36.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input class='tbox' type='text' name='flood_hits' size='10' value='$flood_hits' maxlength='4' />
</td>
</tr>

</td>
</tr>
<tr>
<td style='width:50%; vertical-align:top' class='forumheader3'>".PRFLAN_37.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input class='tbox' type='text' name='flood_time' size='10' value='$flood_time' maxlength='20' />
<br />
".PRFLAN_38." 
</td>
</tr>

<tr>

<td colspan='2'>
<div class='border'><div class='caption'>".PRFLAN_39."</div></div>
</td>
</tr><tr>

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


<td colspan='2'>
<div class='border'><div class='caption'>".PRFLAN_47."</div></div>
</td>
</tr><tr>

<td style='width:50%' class='forumheader3'>".PRFLAN_48.": </td>
<td style='width:50%; text-align:right' class='forumheader3'>".
($user_tracking == "cookie" ? "<input type='radio' name='user_tracking' value='cookie' checked> ".PRFLAN_49 : "<input type='radio' name='user_tracking' value='cookie'> ".PRFLAN_49).
($user_tracking == "session" ? "<input type='radio' name='user_tracking' value='session' checked> ".PRFLAN_50 : "<input type='radio' name='user_tracking' value='session'> ".PRFLAN_50)."

</td>
</tr>






<tr>
<td colspan='2' class='forumheader3'>
<div class='border'><div class='caption'>e107</div></div>
<br />
<div style='text-align:center'><input class='button' type='submit' name='newver' value='".PRFLAN_51."' /></div>
</td>
</tr><tr>

<tr><td colspan='2'  class='forumheader3'><br /></td></tr>

<tr style='vertical-align:top'> 
<td colspan='2'  style='text-align:center' class='forumheader3'>
<br />
<input class='caption' type='submit' name='updateprefs' value='".PRFLAN_52."' />
</td>
</tr>
</table>
</form>
</div>";




$ns -> tablerender("<div style='text-align:center'>".PRFLAN_53."</div>", $text);

require_once("footer.php");
?>	