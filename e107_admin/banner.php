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
|     $Source: /cvs_backup/e107_0.7/e107_admin/banner.php,v $
|     $Revision: 1.11 $
|     $Date: 2005-01-27 19:52:24 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("D")) {
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'banner';
require_once("auth.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;
require_once(e_HANDLER."userclass_class.php");
	
$lan_file = e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_menus.php";
include(file_exists($lan_file) ? $lan_file : e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_menus.php");
	
$qs = explode(".", e_QUERY);
$action = $qs[0];
$sub_action = $qs[1];
$id = $qs[2];
	
$handle = opendir(e_IMAGE."banners/");
while ($file = readdir($handle)) {
	if (!strstr($file, "._") && $file != "." && $file != ".." && $file != "Thumbs.db" && $file != ".DS_Store") {
		$images[] = $file;
	}
}
	
if ($_POST['createbanner'] || $_POST['updatebanner']) {
	if (!$_POST['startmonth'] || !$_POST['startday'] || !$_POST['startyear'] ? $start_date = 0 : $start_date = mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
	if (!$_POST['endmonth'] || !$_POST['endday'] || !$_POST['endyear'] ? $end_date = 0 : $end_date = mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));
	 
	$cli = ($_POST['client_name'] ? $_POST['client_name'] : $_POST['banner_client_sel']);
	 
	if ($_POST['banner_pages']) {
		$postcampaign = ($_POST['banner_campaign'] ? $_POST['banner_campaign'] : $_POST['banner_campaign_sel']);
		$pagelist = explode("\r\n", $_POST['banner_pages']);
		for($i = 0 ; $i < count($pagelist) ; $i++) {
			$pagelist[$i] = trim($pagelist[$i]);
		}
		$plist = implode("|", $pagelist);
		$pageparms = $postcampaign."^".$_POST['banner_listtype']."-".$plist;
		$pageparms = preg_replace("#\|$#", "", $pageparms);
		$pageparms = (trim($_POST['banner_pages']) == '') ? '' :
		 $pageparms;
		$cam = $pageparms;
	} else {
		$cam = ($_POST['banner_campaign'] ? $_POST['banner_campaign'] : $_POST['banner_campaign_sel']);
	}
	 
	if ($_POST['createbanner']) {
		$sql->db_Insert("banner", "0, '".$cli."', '".$_POST['client_login']."', '".$_POST['client_password']."', '".$_POST['banner_image']."', '".$_POST['click_url']."', '".$_POST['impressions_purchased']."', '$start_date', '$end_date', '".$_POST['banner_class']."', 0, 0, '', '".$cam."' ");
		$message = "Banner Created";
	} else {
		$sql->db_Update("banner", "banner_clientname='".$cli."', banner_clientlogin='".$_POST['client_login']."', banner_clientpassword='".$_POST['client_password']."', banner_image='".$_POST['banner_image']."', banner_clickurl='".$_POST['click_url']."', banner_impurchased='".$_POST['impressions_purchased']."', banner_startdate='$start_date', banner_enddate='$end_date', banner_active='".$_POST['banner_class']."', banner_campaign='".$cam."' WHERE banner_id='".$_POST['eid']."' ");
		$message = "Banner Updated";
	}
	unset($_POST['client_name'], $_POST['client_login'], $_POST['client_password'], $_POST['banner_image'], $_POST['click_url'], $_POST['impressions_purchased'], $start_date, $end_date, $_POST['banner_enabled'], $_POST['startday'], $_POST['startmonth'], $_POST['startyear'], $_POST['endday'], $_POST['endmonth'], $_POST['endyear'], $_POST['banner_class'], $_POST['banner_pages'], $_POST['banner_listtype']);
}
	
if (isset($_POST['confirm'])) {
	$sql->db_Delete("banner", "banner_id='".$_POST['id']."' ");
	$message = BNRLAN_1;
}
	
if ($action == "delete" && $sub_action) {
	$text = "<div style='text-align:center'>
		<b>".BNRLAN_2."</b>
		<br /><br />
		<form method='post' action='".e_SELF."'>
		<input class='button' type='submit' name='cancel' value='".BNRLAN_3."' />
		<input class='button' type='submit' name='confirm' value='".BNRLAN_4."' />
		<input type='hidden' name='id' value='".$sub_action."'>
		</form>
		</div>";
	$ns->tablerender(BNRLAN_5, $text);
	 
	require_once("footer.php");
	exit;
}
if (isset($_POST['cancel'])) {
	$message = BNRLAN_6;
}
	
if (isset($_POST['updatevisibility'])) {
	 
	foreach($_POST as $k => $v) {
		if (preg_match("#^banner_#", $k)) {
			$menu_pref[$k] = $v;
		}
		if (preg_match("#^banner_pages#", $k)) {
			 
			$pagelist = explode("\r\n", $v);
			for($i = 0 ; $i < count($pagelist) ; $i++) {
				$pagelist[$i] = trim($pagelist[$i]);
			}
			$plist = implode("|", $pagelist);
			$pageparms = $_POST['listtype']."-".$plist;
			 
			$pageparms = preg_replace("#\|$#", "", $pageparms);
			 
			$pageparms = (trim($v) == '') ? '' :
			 $pageparms;
			$menu_pref[$k] = $pageparms;
		}
	}
	$sysprefs->setArray('menu_pref');
	 
	$message = BNRLAN_47." ".$sub_action."";
}
	
if (isset($_POST['updateoptions'])) {
	foreach($_POST as $k => $v) {
		if (preg_match("#^banner_#", $k)) {
			$menu_pref[$k] = $v;
		}
	}
	$tmp = addslashes(serialize($menu_pref));
	$sql->db_Update("core", "e107_value='$tmp' WHERE e107_name='menu_pref' ");
	 
	$message = BNRLAN_48;
}
	
if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}
	
if ($sql->db_Select("banner")) {
	while ($row = $sql->db_Fetch()) {
		extract($row);
		 
		if (preg_match("#\^#", $banner_campaign)) {
			$campaignsplit = explode("^", $banner_campaign);
			$banner_campaign = $campaignsplit[0];
		}
		 
		if ($banner_campaign) {
			$campaigns[] = $banner_campaign;
		}
		if ($banner_clientname) {
			$clients[] = $banner_clientname;
		}
		if ($banner_clientlogin) {
			$logins[] = $banner_clientlogin;
		}
		if ($banner_clientpassword) {
			$passwords[] = $banner_clientpassword;
		}
	}
}
	
	
if (!$action) {
	$text = "
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr><td colspan='7' style='text-align:center' class='fcaption'>".BNRLAN_7."</td></tr>
		<tr>
		<td class='forumheader' style='text-align:center'>".BNRLAN_8."</td>
		<td class='forumheader' style='text-align:center'>".BNRLAN_9."</td>
		<td class='forumheader' style='text-align:center'>".BNRLAN_10."</td>
		<td class='forumheader' style='text-align:center'>".BNRLAN_11."</td>
		<td class='forumheader' style='text-align:center'>".BNRLAN_12."</td>
		<td class='forumheader' style='text-align:center'>".BNRLAN_13."</td>
		<td class='forumheader' style='text-align:center'>".BNRLAN_14."</td>
		</tr>";
	 
	if (!$banner_total = $sql->db_Select("banner")) {
		$text .= "<tr><td colspan='7' class='forumheader' style='text-align:center'>".BNRLAN_15."</td></tr>";
	} else {
		while ($row = $sql->db_Fetch()) {
			extract($row);
			 
			$clickpercentage = ($banner_clicks && $banner_impressions ? round(($banner_clicks / $banner_impressions) * 100)."%" : "-");
			$impressions_left = ($banner_impurchased ? $banner_impurchased - $banner_impressions : BNRLAN_16);
			$impressions_purchased = ($banner_impurchased ? $banner_impurchased : BNRLAN_16);
			 
			$start_date = ($banner_startdate ? strftime("%d %B %Y", $banner_startdate) : BNRLAN_17);
			$end_date = ($banner_enddate ? strftime("%d %B %Y", $banner_enddate) : BNRLAN_17);
			 
			if (preg_match("#\^#", $banner_campaign)) {
				$campaignsplit = explode("^", $banner_campaign);
				$banner_campaign = $campaignsplit[0];
				$textvisivilitychanged = "(*)";
			} else {
				$textvisivilitychanged = "";
			}
			 
			$text .= "<tr>
				<td class='forumheader3' style='text-align:center'>".$banner_id."</td>
				<td class='forumheader3' style='text-align:center'>".$banner_clientname."</td>
				<td class='forumheader3' style='text-align:center'>".$banner_clicks."</td>
				<td class='forumheader3' style='text-align:center'>".$clickpercentage."</td>
				<td class='forumheader3' style='text-align:center'>".$impressions_purchased."</td>
				<td class='forumheader3' style='text-align:center'>".$impressions_left."</td>
				<td class='forumheader3' style='text-align:center'><a href='".e_SELF."?create.edit.".$banner_id."'>".BNRLAN_44."</a> - <a href='".e_SELF."?delete.".$banner_id."'>".BNRLAN_18."</a></td>
				</tr>
				<tr>
				<td class='forumheader3' style='text-align:center'>&nbsp;</td>
				<td class='forumheader3' style='text-align:center'>".$banner_campaign."</td>
				<td colspan='2' class='forumheader3' style='text-align:center'>".r_userclass_name($banner_active)." ".$textvisivilitychanged."</td>
				<td colspan='3' class='forumheader3' style='text-align:center'>".BNRLAN_45.": ".$start_date." &lt;&gt; ".BNRLAN_21.": ".$end_date."</td>
				</tr>
				<tr><td colspan='8'>&nbsp;</td></tr>";
		}
	}
	$text .= "</table></div>";
	 
	$ns->tablerender(BNRLAN_42, $text);
}
	
if ($action == "create") {
	 
	if ($sub_action == "edit" && $id) {
		if (!$sql->db_Select("banner", "*", "banner_id = '".$id."' " )) {
			$text .= "<div style='text-align:center;'>".BNRLAN_15."</div>";
		} else {
			while ($row = $sql->db_Fetch()) {
				extract($row);
				 
				$_POST['client_name'] = $banner_clientname;
				$_POST['client_login'] = $banner_clientlogin;
				$_POST['client_password'] = $banner_clientpassword;
				$_POST['banner_image'] = $banner_image;
				$_POST['click_url'] = $banner_clickurl;
				$_POST['impressions_purchased'] = $banner_impurchased;
				$_POST['banner_campaign'] = $banner_campaign;
				$_POST['banner_active'] = $banner_active;
				 
				if ($banner_startdate) {
					$tmp = getdate($banner_startdate);
					$_POST['startmonth'] = $tmp['mon'];
					$_POST['startday'] = $tmp['mday'];
					$_POST['startyear'] = $tmp['year'];
				}
				if ($banner_enddate) {
					$tmp = getdate($banner_enddate);
					$_POST['endmonth'] = $tmp['mon'];
					$_POST['endday'] = $tmp['mday'];
					$_POST['endyear'] = $tmp['year'];
				}
				 
				if (preg_match("#\^#", $_POST['banner_campaign'])) {
					 
					$campaignsplit = explode("^", $_POST['banner_campaign']);
					$listtypearray = explode("-", $campaignsplit[1]);
					$listtype = $listtypearray[0];
					$campaign_pages = preg_replace("#\|#", "\n", $listtypearray[1]);
					$_POST['banner_campaign'] = $campaignsplit[0];
				} else {
					$_POST['banner_campaign'] = $banner_campaign;
				}
				 
			}
		}
	}
	 
	$text = "
		<div style='text-align: center;'>
		<form method='post' action='".e_SELF."'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr><td colspan='2' style='text-align:center' class='fcaption'>".($sub_action == "edit" ? BNRLAN_22 : BNRLAN_23)."</td></tr>
		<tr>
		<td class='forumheader3'>".BNRLAN_24."</td>
		<td class='forumheader3'>";
	if (count($campaigns)) {
		$text .= "<select name='banner_campaign_sel' class='tbox'><option></option>";
		$c = 0;
		while ($campaigns[$c]) {
			if (!isset($for_var[$campaigns[$c]])) {
				$text .= ($_POST['banner_campaign'] == $campaigns[$c] ? "<option selected='selected'>".$campaigns[$c]."</option>" : "<option>".$campaigns[$c]."</option>");
				$for_var[$campaigns[$c]] = $campaigns[$c];
			}
			$c++;
		}
		unset($for_var);
		 
		$text .= "</select> ".BNRLAN_25."&nbsp;&nbsp;";
	}
	$text .= " <input class='tbox' type='text' size='30' maxlength='100' name='banner_campaign' value='' />
		".BNRLAN_26."
		</td>
		</tr>
		 
		<tr>
		<td class='forumheader3'>".BNRLAN_27."</td>
		<td class='forumheader3'>";
	 
	if (count($clients)) {
		$text .= "<select name='banner_client_sel' class='tbox' onchange=\"Change_Details(this.form)\"><option></option>";
		$c = 0;
		while ($clients[$c]) {
			if (!isset($for_var[$clients[$c]])) {
				$text .= ($_POST['client_name'] == $clients[$c] ? "<option selected='selected'>".$clients[$c]."</option>" : "<option>".$clients[$c]."</option>");
				$for_var[$clients[$c]] = $clients[$c];
			}
			$c++;
		}
		unset($for_var);
		 
		$text .= "</select> ".BNRLAN_28."&nbsp;&nbsp;";
		$text .= "<script type='text/javascript'>
			function Change_Details(form){
			var login_field = (document.all) ? document.all(\"clientlogin\") : document.getElementById(\"clientlogin\");
			var password_field = (document.all) ? document.all(\"clientpassword\") : document.getElementById(\"clientpassword\");
			switch(form.banner_client_sel.selectedIndex-1){";
		 
		$c = 0;
		$i = 0;
		while ($logins[$c]) {
			if (!isset($for_var[$logins[$c]])) {
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
	 
	$text .= "<input class='tbox' type='text' size='30' maxlength='100' name='client_name' value='' />
		".BNRLAN_29."
		</td></tr>
		 
		<tr>
		<td class='forumheader3'>".BNRLAN_30."</td>
		<td class='forumheader3'>
		<input class='tbox' type='text' size='30' maxlength='20' id='clientlogin' name='client_login' value='".$_POST['client_login']."' />
		</td></tr>
		 
		<tr>
		<td class='forumheader3'>".BNRLAN_31."</td>
		<td class='forumheader3'>
		<input class='tbox' type='text' size='30' maxlength='50' id='clientpassword' name='client_password' value='".$_POST['client_password']."' />
		</td></tr>
		 
		<tr>
		<td class='forumheader3'>".BNRLAN_32."</td>
		<td class='forumheader3'>
		<input class='button' type ='button' value='".BNRLAN_43."' onclick='expandit(this)' />
		<div style='display:none'><br />";
	$c = 0;
	while ($images[$c]) {
		$fileext1 = substr(strrchr($images[$c], "."), 1);
		$fileext2 = substr(strrchr($images[$c], "."), 0);
		 
		$text .= "<input type='radio' name='banner_image' value='".$images[$c]."'";
		if ($images[$c] == $_POST['banner_image']) {
			$text .= "checked='checked'";
		}
		 
		if ($fileext1 == swf) {
			$text .= " /> <br><object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0' width='468' height='60'>
				<param name='movie' value='".e_IMAGE."banners/".$images[$c]."'>
				<param name='quality' value='high'><param name='SCALE' value='noborder'>
				<embed src='".e_IMAGE."banners/".$images[$c]."' width='468' height='60' scale='noborder' quality='high' pluginspage='http://www.macromedia.com/go/getflashplayer' type='application/x-shockwave-flash'></embed></object>
				<br />";
		}
		else if($fileext1 == "php" || $fileext1 == "html" || $fileext1 == "js") {
			$text .= " /> ".BNRLAN_46.": ".$images[$c];
		} else {
			$text .= " /> <img src='".e_IMAGE."banners/".$images[$c]."' alt='' /><br />";
		}
		$c++;
	}
	$text .= "</div></td></tr>
		 
		<tr>
		<td class='forumheader3'>".BNRLAN_33."</td>
		<td class='forumheader3'>
		<input class='tbox' type='text' size='70' maxlength='150' name='click_url' value='".$_POST['click_url']."' />
		</td></tr>
		 
		<tr>
		<td class='forumheader3'>".BNRLAN_34."</td>
		<td class='forumheader3'>
		<input class='tbox' type='text' size='10' maxlength='10' name='impressions_purchased' value='".$_POST['impressions_purchased']."' /> 0 = ".BNRLAN_35."
		</td></tr>
		 
		<tr>
		<td class='forumheader3'>".BNRLAN_36."</td>
		<td class='forumheader3'><select name='startday' class='tbox'><option selected='selected'> </option>";
	for($a = 1; $a <= 31; $a++) {
		$text .= ($a == $_POST['startday'] ? "<option selected='selected'>".$a."</option>" : "<option>".$a."</option>");
	}
	$text .= "</select> <select name='startmonth' class='tbox'><option selected='selected'> </option>";
	for($a = 1; $a <= 12; $a++) {
		$text .= ($a == $_POST['startmonth'] ? "<option selected='selected'>".$a."</option>" : "<option>".$a."</option>");
	}
	$text .= "</select> <select name='startyear' class='tbox'><option selected='selected'> </option>";
	for($a = 2003; $a <= 2010; $a++) {
		$text .= ($a == $_POST['startyear'] ? "<option selected='selected'>".$a."</option>" : "<option>".$a."</option>");
	}
	$text .= "</select> ".BNRLAN_38."
		</td></tr>
		 
		<tr>
		<td class='forumheader3'>".BNRLAN_37."</td>
		<td class='forumheader3'>
		<select name='endday' class='tbox'><option selected='selected'> </option>";
	for($a = 1; $a <= 31; $a++) {
		$text .= ($a == $_POST['endday'] ? "<option selected='selected'>".$a."</option>" : "<option>".$a."</option>");
	}
	$text .= "</select> <select name='endmonth' class='tbox'><option selected='selected'> </option>";
	for($a = 1; $a <= 12; $a++) {
		$text .= ($a == $_POST['endmonth'] ? "<option selected='selected'>".$a."</option>" : "<option>".$a."</option>");
	}
	$text .= "</select> <select name='endyear' class='tbox'><option selected='selected'> </option>";
	for($a = 2003; $a <= 2010; $a++) {
		$text .= ($a == $_POST['endyear'] ? "<option selected='selected'>".$a."</option>" : "<option>".$a."</option>");
	}
	$text .= "</select> ".BNRLAN_38."
		</td>
		</tr>
		 
		<tr>
		<td class='forumheader3'>".BNRLAN_39."</td>
		<td class='forumheader3'>
		".MENLAN_4."
		".r_userclass("banner_class", $_POST['banner_active'], "off", "public,member,guest,admin,classes,nobody,classes")."
		</td></tr>
		 
		<tr>
		<td class='forumheader3'>".BNRLAN_63."</td>
		<td class='forumheader3'>";
	 
	$checked1 = ($listtype == 1) ? " checked='checked' " :
	 "";
	$checked2 = ($listtype == 2) ? " checked='checked' " :
	 "";
	 
	$text .= "
		<input type='radio' {$checked1} name='banner_listtype' value='1' /> ".MENLAN_26."<br />
		<input type='radio' {$checked2} name='banner_listtype' value='2' /> ".MENLAN_27."<br /><br />".MENLAN_28."<br />
		<textarea name='banner_pages' cols='60' rows='10' class='tbox'>$campaign_pages</textarea>
		</td>
		</tr>
		 
		<tr><td colspan='2' style='text-align:center' class='forumheader'>";
	$text .= ($sub_action == "edit" && $id ? "<input class='button' type='submit' name='updatebanner' value='".BNRLAN_40."' /><input type='hidden' name='eid' value='".$id."'" : "<input class='button' type='submit' name='createbanner' value='".BNRLAN_41."' />");
	 
	$text .= "</td></tr></table>
		</form>";
	 
	$ns->tablerender(BNRLAN_42, $text);
	 
}
	
if ($action == "cvis") {
	$text = "<br />
		<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."' name='menu_conf_form'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		 
		<tr>
		<td style='width:40%' class='forumheader3'>".BNRLAN_49."</td>
		<td style='width:60%' class='forumheader3'>
		<select id='visibility' name='visibility' class='tbox' onchange=\"document.location=this.options[this.selectedIndex].value;\" >
		".$rs->form_option(BNRLAN_50, TRUE, " ");
	$sql2 = new db;
	$category_total = $sql2->db_Select("banner", "DISTINCT(SUBSTRING_INDEX(banner_campaign, '^', 1)) as banner_campaign", "ORDER BY banner_campaign", "mode=no_where");
	while ($row = $sql2->db_Fetch()) {
		extract($row);
		$text .= $rs->form_option($banner_campaign, "", e_SELF."?cvis.".$banner_campaign);
	}
	$text .= $rs->form_select_close()."
		</td>
		</tr>
		 
		</table>
		</form>
		</div>";
	 
	$ns->tablerender(BNRLAN_51, $text);
	 
	if ($action == "cvis" && $sub_action) {
		 
		foreach($menu_pref as $k => $v) {
			if (preg_match("#^banner_pages-$sub_action#", $k)) {
				$listtypearray = explode("-", $v);
				$listtype = $listtypearray[0];
				$campaign_pages = preg_replace("#\|#", "\n", $listtypearray[1]);
			}
		}
		 
		$bannerclassname = "banner_class-".$sub_action;
		$bannerclassvalue = ($menu_pref[$bannerclassname] ? $menu_pref[$bannerclassname] : "0" );
		$bannerpagesname = "banner_pages-".$sub_action;
		$checked1 = ($listtype == 1) ? " checked='checked' " :
		 "";
		$checked2 = ($listtype == 2) ? " checked='checked' " :
		 "";
		 
		$text = "
			<div style='text-align:center'>
			<form method='post' action='".e_SELF."?".e_QUERY."'>\n
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td style='width:40%' class='forumheader3'>".BNRLAN_39."</td>
			<td style='width:60%' class='forumheader3'>
			<input type='hidden' name='campaign' value='".$sub_action."' />
			".MENLAN_4."
			".r_userclass($bannerclassname, $bannerclassvalue, "off", "public,member,guest,admin,classes,nobody")."
			</td>
			</tr>
			<tr>
			<td style='width:40%' class='forumheader3'>".BNRLAN_63."</td>
			<td style='width:60%' class='forumheader3'><br />
			<input type='radio' {$checked1} name='listtype' value='1' /> ".MENLAN_26."<br />
			<input type='radio' {$checked2} name='listtype' value='2' /> ".MENLAN_27."<br /><br />".MENLAN_28."<br />
			<textarea name='$bannerpagesname' cols='60' rows='10' class='tbox'>$campaign_pages</textarea><br /><br />
			</td>
			</tr>
			<tr>
			<td colspan='2' class='forumheader' style='text-align:center'><input class='button' type='submit' name='updatevisibility' value='".MENLAN_6."' /></td>
			</tr>
			</table>
			</form>
			</div>";
		 
		$caption = BNRLAN_52." : ".$sub_action;
		 
		$ns->tablerender($caption, $text);
	}
	 
}
	
if ($action == "opt") {
	 
	$menu_pref['banner_visibilitytype'] = ($menu_pref['banner_visibilitytype'] ? $menu_pref['banner_visibilitytype'] : "1");
	 
	$checked1 = ($menu_pref['banner_visibilitytype'] == 1) ? " checked='checked' " :
	 "";
	$checked2 = ($menu_pref['banner_visibilitytype'] == 2) ? " checked='checked' " :
	 "";
	 
	$text = "
		<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>\n
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td style='width:40%' class='forumheader3'>".BNRLAN_53."<br />".BNRLAN_64."<br /><br />".BNRLAN_65."</td>
		<td style='width:60%' class='forumheader3'>
		<input type='radio' {$checked1} name='banner_visibilitytype' value='1' /> ".BNRLAN_54."<br />
		<input type='radio' {$checked2} name='banner_visibilitytype' value='2' /> ".BNRLAN_55."<br />
		<tr>
		<td class='forumheader' style='text-align:center' colspan='2'>
		<input class='button' type='submit' name='updateoptions' value='".BNRLAN_56."' />
		</td>
		</tr>
		</table>
		</form>
		</div>";
	 
	$caption = BNRLAN_57;
	$ns->tablerender($caption, $text);
}
	
	
function banner_adminmenu() {
	 
	global $action, $sql, $sub_action, $id;
	$act = $action;
	if ($act == "") {
		$act = "main";
	}
	$var['main']['text'] = BNRLAN_58;
	$var['main']['link'] = e_SELF;
	 
	$var['create']['text'] = BNRLAN_59;
	$var['create']['link'] = e_SELF."?create";
	 
	$var['cat']['text'] = BNRLAN_60;
	$var['cat']['link'] = e_SELF."?cvis";
	 
	$var['opt']['text'] = BNRLAN_57;
	$var['opt']['link'] = e_SELF."?opt";
	 
	show_admin_menu(BNRLAN_62, $act, $var);
}
	
require_once("footer.php");
	
?>