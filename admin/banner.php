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
if(!getperms("P")){ header("location:".e_HTTP."index.php"); }
require_once("auth.php");

$qs = explode(".", e_QUERY);
$action = $qs[0]; $id = $qs[1];

$edit = ($action == "edit" ? TRUE : FALSE);
$delete = ($action == "delete" ? TRUE : FALSE);

$handle=opendir("../themes/shared/banners/");
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
		$sql -> db_Update("banner", "banner_clientname='".$_POST['client_name']."', banner_clientlogin='".$_POST['client_login']."', banner_clientpassword='".$_POST['client_password']."', banner_image='".$_POST['banner_image']."', banner_clickurl='".$_POST['click_url']."', banner_impurchased='".$_POST['impressions_purchased']."', banner_startdate='$start_date', banner_enddate='$end_date', banner_active='".$_POST['banner_enabled']."', banner_campaign='".$cam."' WHERE banner_id='".$_POST['eid']."' ");
		$message = "Banner Updated";
	}
	unset($_POST['client_name'], $_POST['client_login'], $_POST['client_password'], $_POST['banner_image'], $_POST['click_url'], $_POST['impressions_purchased'], $start_date, $end_date, $_POST['banner_enabled'], $_POST['startday'], $_POST['startmonth'], $_POST['startyear'], $_POST['endday'], $_POST['endmonth'], $_POST['endyear']);
}

if(IsSet($_POST['confirm'])){
	$sql -> db_Delete("banner", "banner_id='".$_POST['id']."' ");
	$message = "Banner deleted.";
}

if($delete){
	$text = "<div style=\"text-align:center\">
	<b>Please confirm you wish to delete this banner - once deleted it cannot be retrieved</b>
<br /><br />
<form method=\"post\" action=\"".e_SELF."\">
<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"Cancel\" /> 
<input class=\"button\" type=\"submit\" name=\"confirm\" value=\"Confirm Delete\" /> 
<input type=\"hidden\" name=\"id\" value=\"".$id."\">
</form>
</div>";
$ns -> tablerender("Confirm Delete Banner", $text);
	
	require_once("footer.php");
	exit;
}
if(IsSet($_POST['cancel'])){
	$message = "Delete cancelled.";
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

if($sql -> db_Select("banner")){
	while($row = $sql -> db_Fetch()){
		extract($row);
		if($banner_campaign){ $campaigns[] = $banner_campaign; }
		if($banner_clientname){ $clients[] = $banner_clientname; }
	}
}

$text = "<table class=\"fborder\" style=\"width:98%\">
<tr><td colspan=\"8\" style=\"text-align:center\" class=\"fcaption\">Existing Banners</td></tr>
<tr>
<td class=\"forumheader\" style=\"text-align:center\"><span class=\"smallblacktext\">Banner ID</span></td>
<td class=\"forumheader\" style=\"text-align:center\"><span class=\"smallblacktext\">Client</span></td>
<td class=\"forumheader\" style=\"text-align:center\"><span class=\"smallblacktext\">Clickthroughs</span></td>
<td class=\"forumheader\" style=\"text-align:center\"><span class=\"smallblacktext\">Click %</span></td>
<td class=\"forumheader\" style=\"text-align:center\"><span class=\"smallblacktext\">Impressions</span></td>
<td class=\"forumheader\" style=\"text-align:center\"><span class=\"smallblacktext\">Impressions Left</span></td>
<td class=\"forumheader\" style=\"text-align:center\"><span class=\"smallblacktext\">Options</span></td>
</tr>";

if(!$banner_total = $sql -> db_Select("banner")){
	$text .= "<tr>
	<td colspan=\"8\" class=\"forumheader2\" style=\"text-align:center\">No banners yet.</td>";
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
		$impressions_left = ($banner_impurchased ? $banner_impurchased - $banner_impressions : "Unlimited");
		$impressions_purchased = ($banner_impurchased ? $banner_impurchased : "Unlimited");

		$start_date = ($banner_startdate ? strftime("%d %B %Y", $banner_startdate) : "None");
		$end_date = ($banner_enddate ? strftime("%d %B %Y", $banner_enddate) : "None");

		$text.="<tr>
		<td class=\"forumheader3\" style=\"text-align:center\">".$banner_id."</td>
		<td class=\"forumheader3\" style=\"text-align:center\">".$banner_clientname."</td>
		<td class=\"forumheader3\" style=\"text-align:center\">".$banner_clicks."</td>
		<td class=\"forumheader3\" style=\"text-align:center\">".$clickpercentage."</td>
		<td class=\"forumheader3\" style=\"text-align:center\">".$impressions_purchased."</td>
		<td class=\"forumheader3\" style=\"text-align:center\">".$impressions_left."</td>
		<td class=\"forumheader3\" style=\"text-align:center\"><a href=\"".e_SELF."?edit.".$banner_id."\">Edit</a> - <a href=\"".e_SELF."?delete.".$banner_id."\">Delete</a></td>
		</tr>
		<td colspan=\"8\" class=\"forumheader3\" style=\"text-align:center\">

		Active: ". ($banner_active ? "Yes" : "<b>No</b>")." | 

		Starts: ".$start_date." <> Ends: ".$end_date."</td>
		<tr><td colspan=\"8\">&nbsp;</td></tr>";
	}
}

$text .= "</table>
<br /><br />

<form method=\"post\" action=\"".e_SELF."\">

<table class=\"fborder\" style=\"width:98%\">
<tr><td colspan=\"2\" style=\"text-align:center\" class=\"fcaption\">";

$text .= ($edit ? "Update Banner" : "Add New Banner");

$text .= "</td></tr>
<tr>
<td class=\"forumheader3\">Campaign</td>
<td class=\"forumheader3\">";
if(count($campaigns)){
	$text .= "<select name=\"banner_campaign_sel\" class=\"tbox\"><option></option>";
	$c=0;
	while($campaigns[$c]){
		$text .=($_POST['banner_campaign'] == $campaigns[$c] ? "<option selected>".$campaigns[$c]."</option>" : "<option>".$campaigns[$c]."</option>");
		$c++;
	}

	$text .= "</select> choose existing campaign&nbsp;&nbsp;";
}
$text .= " <input class=\"tbox\" type=\"text\" size=\"30\" maxlength=\"100\" name=\"banner_campaign\" value=\"\">
enter new campaign
</td>
</tr>

<tr>
<td class=\"forumheader3\">Client</td>
<td class=\"forumheader3\">";

if(count($clients)){
	$text .= "<select name=\"banner_client_sel\" class=\"tbox\"><option></option>";
	$c=0;
	while($clients[$c]){
		$text .=($_POST['client_name'] == $clients[$c] ? "<option selected>".$clients[$c]."</option>" : "<option>".$clients[$c]."</option>");
		$c++;
	}

	$text .= "</select> choose existing client&nbsp;&nbsp;";
}

$text .= "<input class=\"tbox\" type=\"text\" size=\"30\" maxlength=\"100\" name=\"client_name\" value=\"\">
enter new client
</tr>

<tr>
<td class=\"forumheader3\">Client Login</td>
<td class=\"forumheader3\">
<input class=\"tbox\" type=\"text\" size=\"30\" maxlength=\"20\" name=\"client_login\" value=\"".$_POST['client_login']."\">
</tr>

<tr>
<td class=\"forumheader3\">Client Password</td>
<td class=\"forumheader3\">
<input class=\"tbox\" type=\"text\" size=\"30\" maxlength=\"50\" name=\"client_password\" value=\"".$_POST['client_password']."\">
</tr>

<tr>
<td class=\"forumheader3\">Banner Image</td>
<td class=\"forumheader3\">
<input class=\"button\" type =\"button\" value=\"Choose banner image\" onClick=\"expandit(this)\">
<div style=\"display:none\"><br />";
$c=0;
while($images[$c]){
	$text .= "<input type=\"radio\" name=\"banner_image\" value=\"".$images[$c]."\"";
	if($images[$c] == $_POST['banner_image']){
		$text .= "checked";
	}

	$text .= "> <img src=\"../themes/shared/banners/".$images[$c]."\" alt=\"\" /><br />";
	$c++;
}
//<input class=\"tbox\" type=\"text\" size=\"70\" maxlength=\"150\" name=\"banner_image\" value=\"".$_POST['banner_image']."\">
$text .= "</div></td></tr>

<tr>
<td class=\"forumheader3\">Click URL</td>
<td class=\"forumheader3\">
<input class=\"tbox\" type=\"text\" size=\"70\" maxlength=\"150\" name=\"click_url\" value=\"".$_POST['click_url']."\">
</tr>

<tr>
<td class=\"forumheader3\">Impressions Purchased</td>
<td class=\"forumheader3\">
<input class=\"tbox\" type=\"text\" size=\"10\" maxlength=\"10\" name=\"impressions_purchased\" value=\"".$_POST['impressions_purchased']."\"> 0 = unlimited
</tr>

<tr>
<td class=\"forumheader3\">Start Date</td>
<td class=\"forumheader3\"><select name=\"startday\" class=\"tbox\"><option selected> </option>";
for($a=1; $a<=31; $a++){
	$text .= ($a == $_POST['startday'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> <select name=\"startmonth\" class=\"tbox\"><option selected> </option>";
for($a=1; $a<=12; $a++){
	$text .= ($a == $_POST['startmonth'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> <select name=\"startyear\" class=\"tbox\"><option selected> </option>";
for($a=2003; $a<=2010; $a++){
	$text .= ($a == $_POST['startyear'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> blank = no limit
</td></tr>

<tr>
<td class=\"forumheader3\">End Date</td>
<td class=\"forumheader3\">
<select name=\"endday\" class=\"tbox\"><option selected> </option>";
for($a=1; $a<=31; $a++){
	$text .= ($a == $_POST['endday'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> <select name=\"endmonth\" class=\"tbox\"><option selected> </option>";
for($a=1; $a<=12; $a++){
	$text .= ($a == $_POST['endmonth'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> <select name=\"endyear\" class=\"tbox\"><option selected> </option>";
for($a=2003; $a<=2010; $a++){
	$text .= ($a == $_POST['endyear'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> blank = no limit
</td>
</tr>

<tr>
<td class=\"forumheader3\">Active?</td>
<td class=\"forumheader3\">
<input name=\"banner_enabled\" type=\"radio\" value=\"1\" checked>Yes&nbsp;&nbsp;<input name=\"banner_enabled\" type=\"radio\" value=\"0\">No
</tr>

<tr><td colspan=\"2\" style=\"text-align:center\" class=\"forumheader\">";
$text .= ($edit ? "<input class=\"button\" type=\"submit\" name=\"updatebanner\" value=\"Update Banner\"><input type=\"hidden\" name=\"eid\" value=\"".$id."\"" : "<input class=\"button\" type=\"submit\" name=\"createbanner\" value=\"Create New Banner\">");

$text .= "</td></tr></table>
</form>";

$ns -> tablerender("Banner Rotation System", $text);

require_once("footer.php");
?>	