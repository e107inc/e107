<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/banner.php
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
if(!getperms("D")){ header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");

$qs = explode(".", e_QUERY);
$action = $qs[0]; $id = $qs[1];

$edit = ($action == "edit" ? TRUE : FALSE);
$delete = ($action == "delete" ? TRUE : FALSE);

$handle=opendir(e_IMAGE."banners/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".."){
		$images[] = $file;
	}
}

if($_POST['createbanner'] || $_POST['updatebanner']){
	if(!$_POST['startmonth'] || !$_POST['startday'] || !$_POST['startyear'] ? $start_date = 0 : $start_date = mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
	if(!$_POST['endmonth'] || !$_POST['endday'] || !$_POST['endyear'] ? $end_date = 0 : $end_date = mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));

	$cam = ($_POST['banner_campaign'] ? $_POST['banner_campaign'] : $_POST['banner_campaign_sel']);
	$cli = ($_POST['client_name'] ? $_POST['client_name'] : $_POST['banner_client_sel']);

	if($_POST['createbanner']){
		$sql -> db_Insert("banner", "0, '".$cli."', '".$_POST['client_login']."', '".$_POST['client_password']."', '".$_POST['banner_image']."', '".$_POST['click_url']."', '".$_POST['impressions_purchased']."', '$start_date', '$end_date', '".$_POST['banner_enabled']."', 0, 0, '', '".$cam."' ");
		$message = "Banner Created";
	}else{
		$sql -> db_Update("banner", "banner_clientname='".$cli."', banner_clientlogin='".$_POST['client_login']."', banner_clientpassword='".$_POST['client_password']."', banner_image='".$_POST['banner_image']."', banner_clickurl='".$_POST['click_url']."', banner_impurchased='".$_POST['impressions_purchased']."', banner_startdate='$start_date', banner_enddate='$end_date', banner_active='".$_POST['banner_enabled']."', banner_campaign='".$cam."' WHERE banner_id='".$_POST['eid']."' ");
		$message = "Banner Updated";
	}
	unset($_POST['client_name'], $_POST['client_login'], $_POST['client_password'], $_POST['banner_image'], $_POST['click_url'], $_POST['impressions_purchased'], $start_date, $end_date, $_POST['banner_enabled'], $_POST['startday'], $_POST['startmonth'], $_POST['startyear'], $_POST['endday'], $_POST['endmonth'], $_POST['endyear']);
}

if(IsSet($_POST['confirm'])){
	$sql -> db_Delete("banner", "banner_id='".$_POST['id']."' ");
	$message = BNRLAN_1;
}

if($delete){
	$text = "<div style=\"text-align:center\">
	<b>".BNRLAN_2."</b>
<br /><br />
<form method=\"post\" action=\"".e_SELF."\">
<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"".BNRLAN_3."\" /> 
<input class=\"button\" type=\"submit\" name=\"confirm\" value=\"".BNRLAN_4."\" /> 
<input type=\"hidden\" name=\"id\" value=\"".$id."\">
</form>
</div>";
$ns -> tablerender(BNRLAN_5, $text);
	
	require_once("footer.php");
	exit;
}
if(IsSet($_POST['cancel'])){
	$message = BNRLAN_6;
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

if($sql -> db_Select("banner")){
	while($row = $sql -> db_Fetch()){
		extract($row);
		if($banner_campaign){ $campaigns[] = $banner_campaign; }
		if($banner_clientname){ $clients[] = $banner_clientname; }
		if($banner_clientlogin){ $logins[] = $banner_clientlogin; }
		if($banner_clientpassword){ $passwords[] = $banner_clientpassword; }
	}
}

$text = "<table class=\"fborder\" style=\"width:98%\">
<tr><td colspan=\"8\" style=\"text-align:center\" class=\"fcaption\">".BNRLAN_7."</td></tr>
<tr>
<td class=\"forumheader\" style=\"text-align:center\"><span class=\"smallblacktext\">".BNRLAN_8."</span></td>
<td class=\"forumheader\" style=\"text-align:center\"><span class=\"smallblacktext\">".BNRLAN_9."</span></td>
<td class=\"forumheader\" style=\"text-align:center\"><span class=\"smallblacktext\">".BNRLAN_10."</span></td>
<td class=\"forumheader\" style=\"text-align:center\"><span class=\"smallblacktext\">".BNRLAN_11."</span></td>
<td class=\"forumheader\" style=\"text-align:center\"><span class=\"smallblacktext\">".BNRLAN_12."</span></td>
<td class=\"forumheader\" style=\"text-align:center\"><span class=\"smallblacktext\">".BNRLAN_13."</span></td>
<td class=\"forumheader\" style=\"text-align:center\"><span class=\"smallblacktext\">".BNRLAN_14."</span></td>
</tr>";

if(!$banner_total = $sql -> db_Select("banner")){
	$text .= "<tr>
	<td colspan=\"8\" class=\"forumheader2\" style=\"text-align:center\">".BNRLAN_15."</td>";
}else{
	while($row = $sql-> db_Fetch()){
		extract($row);
		if($edit && $id == $banner_id){

			$_POST['client_name'] = $banner_clientname;
			$_POST['client_login'] = $banner_clientlogin;
			$_POST['client_password'] = $banner_clientpassword;
			$_POST['banner_image'] = $banner_image;
			$_POST['click_url'] = $banner_clickurl;
			$_POST['impressions_purchased'] = $banner_impurchased;
			$_POST['banner_campaign'] = $banner_campaign;
			
			if($banner_startdate){$tmp = getdate($banner_startdate);$_POST['startmonth'] = $tmp['mon'];$_POST['startday'] = $tmp['mday'];$_POST['startyear'] = $tmp['year'];}
			if($banner_enddate){$tmp = getdate($banner_enddate);$_POST['endmonth'] = $tmp['mon'];$_POST['endday'] = $tmp['mday'];$_POST['endyear'] = $tmp['year'];}

		}

		$clickpercentage = ($banner_clicks && $banner_impressions ? round(($banner_clicks / $banner_impressions) * 100)."%" : "-");
		$impressions_left = ($banner_impurchased ? $banner_impurchased - $banner_impressions : BNRLAN_16);
		$impressions_purchased = ($banner_impurchased ? $banner_impurchased : BNRLAN_16);

		$start_date = ($banner_startdate ? strftime("%d %B %Y", $banner_startdate) : BNRLAN_17);
		$end_date = ($banner_enddate ? strftime("%d %B %Y", $banner_enddate) : BNRLAN_17);

		$text.="<tr>
		<td class=\"forumheader3\" style=\"text-align:center\">".$banner_id."</td>
		<td class=\"forumheader3\" style=\"text-align:center\">".$banner_clientname."</td>
		<td class=\"forumheader3\" style=\"text-align:center\">".$banner_clicks."</td>
		<td class=\"forumheader3\" style=\"text-align:center\">".$clickpercentage."</td>
		<td class=\"forumheader3\" style=\"text-align:center\">".$impressions_purchased."</td>
		<td class=\"forumheader3\" style=\"text-align:center\">".$impressions_left."</td>
		<td class=\"forumheader3\" style=\"text-align:center\"><a href=\"".e_SELF."?edit.".$banner_id."\">".BNRLAN_44."</a> - <a href=\"".e_SELF."?delete.".$banner_id."\">".BNRLAN_18."</a></td>
		</tr>
		<td colspan=\"8\" class=\"forumheader3\" style=\"text-align:center\">

		".BNRLAN_39.": ". ($banner_active ? BNRLAN_19 : "<b>".BNRLAN_20."</b>")." | 

		".BNRLAN_45.": ".$start_date." <> ".BNRLAN_21.": ".$end_date."</td>
		<tr><td colspan=\"8\">&nbsp;</td></tr>";
	}
}

$text .= "</table>
<br /><br />

<form method=\"post\" action=\"".e_SELF."\">

<table class=\"fborder\" style=\"width:98%\">
<tr><td colspan=\"2\" style=\"text-align:center\" class=\"fcaption\">";

$text .= ($edit ? BNRLAN_22 : BNRLAN_23);

$text .= "</td></tr>
<tr>
<td class=\"forumheader3\">".BNRLAN_24."</td>
<td class=\"forumheader3\">";
if(count($campaigns)){
	$text .= "<select name=\"banner_campaign_sel\" class=\"tbox\"><option></option>";
	$c=0;
	while($campaigns[$c]){
		if (!isset($for_var[$campaigns[$c]])){
			$text .=($_POST['banner_campaign'] == $campaigns[$c] ? "<option selected='selected'>".$campaigns[$c]."</option>" : "<option>".$campaigns[$c]."</option>");
			$for_var[$campaigns[$c]] = $campaigns[$c];
		}
		$c++;
	}
	unset($for_var);

	$text .= "</select> ".BNRLAN_25."&nbsp;&nbsp;";
}
$text .= " <input class=\"tbox\" type=\"text\" size=\"30\" maxlength=\"100\" name=\"banner_campaign\" value=\"\">
".BNRLAN_26."
</td>
</tr>

<tr>
<td class=\"forumheader3\">".BNRLAN_27."</td>
<td class=\"forumheader3\">";

if(count($clients)){
	$text .= "<select name=\"banner_client_sel\" class=\"tbox\" onChange=\"Change_Details(this.form)\"><option></option>";
	$c=0;
	while($clients[$c]){
		if (!isset($for_var[$clients[$c]])){
			$text .=($_POST['client_name'] == $clients[$c] ? "<option selected='selected'>".$clients[$c]."</option>" : "<option>".$clients[$c]."</option>");
			$for_var[$clients[$c]] = $clients[$c];
		}
		$c++;
	}
	unset($for_var);

	$text .= "</select> ".BNRLAN_28."&nbsp;&nbsp;";
	$text .= "<script> 
	function Change_Details(form){
		var login_field = (document.all) ? document.all(\"clientlogin\") : document.getElementById(\"clientlogin\");
		var password_field = (document.all) ? document.all(\"clientpassword\") : document.getElementById(\"clientpassword\");
  		switch(form.banner_client_sel.selectedIndex-1){";

		$c = 0;
		$i = 0;
		while($logins[$c]){
			if (!isset($for_var[$logins[$c]])){
				$text .= "
				case ".$i.":
					login_field.value = \"".$logins[$c]."\";
					password_field.value = \"".$passwords[$c]."\";
					break;";
				$for_var[$logins[$c]] = $logins[$c];
				$i++;
			}
			$c++;
		}
		unset($for_var);
	
	$text .= "
				default:
					login_field.value = \"\";
					password_field.value = \"\";
					break;	
		}
	}
	</script>";
}

$text .= "<input class=\"tbox\" type=\"text\" size=\"30\" maxlength=\"100\" name=\"client_name\" value=\"\">
".BNRLAN_29."
</tr>

<tr>
<td class=\"forumheader3\">".BNRLAN_30."</td>
<td class=\"forumheader3\">
<input class=\"tbox\" type=\"text\" size=\"30\" maxlength=\"20\" ID=\"clientlogin\" name=\"client_login\" value=\"".$_POST['client_login']."\">
</tr>

<tr>
<td class=\"forumheader3\">".BNRLAN_31."</td>
<td class=\"forumheader3\">
<input class=\"tbox\" type=\"text\" size=\"30\" maxlength=\"50\" ID=\"clientpassword\" name=\"client_password\" value=\"".$_POST['client_password']."\">
</tr>

<tr>
<td class=\"forumheader3\">".BNRLAN_32."</td>
<td class=\"forumheader3\">
<input class=\"button\" type =\"button\" value=\"".BNRLAN_43."\" onClick=\"expandit(this)\">
<div style=\"display:none\"><br />";
$c=0;
while($images[$c]){
	$fileext1 = substr(strrchr($images[$c], "."), 1);
	$fileext2 = substr(strrchr($images[$c], "."), 0);

	$text .= "<input type=\"radio\" name=\"banner_image\" value=\"".$images[$c]."\"";
	if($images[$c] == $_POST['banner_image']){
		$text .= "checked";
	}

	if ($fileext1 == swf){
		$text .= "> <br><object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0\" width=\"468\" height=\"60\">
		<param name=\"movie\" value=\"".e_IMAGE."banners/".$images[$c]."\">
		 <param name=\"quality\" value=\"high\"><param name=\"SCALE\" value=\"noborder\">
		 <embed src=\"".e_IMAGE."banners/".$images[$c]."\" width=\"468\" height=\"60\" scale=\"noborder\" quality=\"high\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\"></embed></object>
		<br />";
	}else if($fileext1 == "php" || $fileext1 == "html" || $fileext1 == "js"){
		$text .= "> ".BNRLAN_46.": ".$images[$c];
	}else{
		$text .= "> <img src=\"".e_IMAGE."banners/".$images[$c]."\" alt=\"\" /><br />";
	}
	$c++;
}
$text .= "</div></td></tr>

<tr>
<td class=\"forumheader3\">".BNRLAN_33."</td>
<td class=\"forumheader3\">
<input class=\"tbox\" type=\"text\" size=\"70\" maxlength=\"150\" name=\"click_url\" value=\"".$_POST['click_url']."\">
</tr>

<tr>
<td class=\"forumheader3\">".BNRLAN_34."</td>
<td class=\"forumheader3\">
<input class=\"tbox\" type=\"text\" size=\"10\" maxlength=\"10\" name=\"impressions_purchased\" value=\"".$_POST['impressions_purchased']."\"> 0 = ".BNRLAN_35."
</tr>

<tr>
<td class=\"forumheader3\">".BNRLAN_36."</td>
<td class=\"forumheader3\"><select name=\"startday\" class=\"tbox\"><option selected='selected'> </option>";
for($a=1; $a<=31; $a++){
	$text .= ($a == $_POST['startday'] ? "<option selected='selected'>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> <select name=\"startmonth\" class=\"tbox\"><option selected='selected'> </option>";
for($a=1; $a<=12; $a++){
	$text .= ($a == $_POST['startmonth'] ? "<option selected='selected'>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> <select name=\"startyear\" class=\"tbox\"><option selected='selected'> </option>";
for($a=2003; $a<=2010; $a++){
	$text .= ($a == $_POST['startyear'] ? "<option selected='selected'>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> ".BNRLAN_38."
</td></tr>

<tr>
<td class=\"forumheader3\">".BNRLAN_37."</td>
<td class=\"forumheader3\">
<select name=\"endday\" class=\"tbox\"><option selected='selected'> </option>";
for($a=1; $a<=31; $a++){
	$text .= ($a == $_POST['endday'] ? "<option selected='selected'>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> <select name=\"endmonth\" class=\"tbox\"><option selected='selected'> </option>";
for($a=1; $a<=12; $a++){
	$text .= ($a == $_POST['endmonth'] ? "<option selected='selected'>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> <select name=\"endyear\" class=\"tbox\"><option selected='selected'> </option>";
for($a=2003; $a<=2010; $a++){
	$text .= ($a == $_POST['endyear'] ? "<option selected='selected'>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> ".BNRLAN_38."
</td>
</tr>

<tr>
<td class=\"forumheader3\">".BNRLAN_39."?</td>
<td class=\"forumheader3\">
<input name=\"banner_enabled\" type=\"radio\" value=\"1\" checked='checked' />".BNRLAN_19."&nbsp;&nbsp;<input name=\"banner_enabled\" type=\"radio\" value=\"0\">".BNRLAN_20."
</tr>

<tr><td colspan=\"2\" style=\"text-align:center\" class=\"forumheader\">";
$text .= ($edit ? "<input class=\"button\" type=\"submit\" name=\"updatebanner\" value=\"".BNRLAN_40."\"><input type=\"hidden\" name=\"eid\" value=\"".$id."\"" : "<input class=\"button\" type=\"submit\" name=\"createbanner\" value=\"".BNRLAN_41."\">");

$text .= "</td></tr></table>
</form>";

$ns -> tablerender(BNRLAN_42, $text);

require_once("footer.php");
?>	