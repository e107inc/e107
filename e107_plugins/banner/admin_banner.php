<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Banner Administration
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/banner/admin_banner.php,v $
 * $Revision: 1.4 $
 * $Date: 2009-11-18 01:05:22 $
 * $Author: e107coders $
 *
*/

require_once("../../class2.php");
if (!getperms("D"))
{
	header("location:".e_BASE."index.php");
	exit;
}

$e_sub_cat = 'banner';

require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."form_handler.php");
$frm = new e_form();

require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."file_class.php");
$fl = new e_file;

require_once(e_HANDLER."message_handler.php");
$emessage = eMessage::getInstance();

//@FIXME mix up in banner language files
include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_menus.php");
include_lan(e_PLUGIN."banner/languages/".e_LANGUAGE.".php");


if(e_QUERY)
{
	list($action, $sub_action, $id) = explode(".", e_QUERY);
}

$images = $fl->get_files(e_IMAGE."banners/","","standard");

if (isset($_POST['update_menu']))
{
	$menu_pref['banner_caption']	= $e107->tp->toDB($_POST['banner_caption']);
	$menu_pref['banner_amount']		= intval($_POST['banner_amount']);
	$menu_pref['banner_rendertype']	= intval($_POST['banner_rendertype']);

	if (isset($_POST['multiaction_cat_active']))
	{
		/*$array_cat = explode("-", $_POST['catid']);
		$cat='';
		for($i = 0; $i < count($array_cat); $i++)
		{
			$cat .= $e107->tp->toDB($array_cat[$i])."|";
		}
		$cat = substr($cat, 0, -1);*/
		$cat = implode('|', $e107->tp->toDB($_POST['multiaction_cat_active']));
		$menu_pref['banner_campaign'] = $cat;
	}

	$sysprefs->setArray('menu_pref');
	banners_adminlog('01', $menu_pref['banner_caption'].'[!br!]'.$menu_pref['banner_amount'].', '.$menu_pref['banner_rendertype'].'[!br!]'.$menu_pref['banner_campaign']);
	$emessage->add(BANNER_MENU_L2, E_MESSAGE_SUCCESS);
}



if ($_POST['createbanner'] || $_POST['updatebanner'])
{
	$start_date = (!$_POST['startmonth'] || !$_POST['startday'] || !$_POST['startyear'] ? 0 : mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
	$end_date = (!$_POST['endmonth'] || !$_POST['endday'] || !$_POST['endyear'] ? 0 : mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));
	$cli = $e107->tp->toDB($_POST['client_name'] ? $_POST['client_name'] : $_POST['banner_client_sel']);
	$cLogin = $e107->tp->toDB($_POST['client_login']);
	$cPassword = $e107->tp->toDB($_POST['client_password']);
	$banImage = $e107->tp->toDB($_POST['banner_image']);
	$banURL = $e107->tp->toDB($_POST['click_url']);

	if ($_POST['banner_pages'])
	{	// Section redundant?
		$postcampaign = $e107->tp->toDB($_POST['banner_campaign'] ? $_POST['banner_campaign'] : $_POST['banner_campaign_sel']);
		$pagelist = explode("\r", $_POST['banner_pages']);
		for($i = 0 ; $i < count($pagelist) ; $i++)
		{
			$pagelist[$i] = trim($pagelist[$i]);
		}
		$plist = implode("|", $pagelist);
		$pageparms = $postcampaign."^".$_POST['banner_listtype']."-".$plist;
		$pageparms = preg_replace("#\|$#", "", $pageparms);
		$pageparms = (trim($_POST['banner_pages']) == '') ? '' : $pageparms;
		$cam = $pageparms;
		$logString = $postcampaign.'[!br!]';
	}
	else
	{
		$cam = $e107->tp->toDB($_POST['banner_campaign'] ? $_POST['banner_campaign'] : $_POST['banner_campaign_sel']);
	}

	$logString .= $cam.'[!br!]'.$cli.'[!br!]'.$banImage.'[!br!]'.$banURL;
	if ($_POST['createbanner'])
	{
		admin_update($sql->db_Insert("banner", "0, '".$cli."', '".$cLogin."', '".$cPassword."', '".$banImage."', '".$banURL."', '".intval($_POST['impressions_purchased'])."', '{$start_date}', '{$end_date}', '".intval($_POST['banner_class'])."', 0, 0, '', '".$cam."'"), 'insert', BNRLAN_63, false, false);
		banners_adminlog('02',$logString);
	}
	else
	{
		admin_update($sql->db_Update("banner", "banner_clientname='".$cli."', banner_clientlogin='".$cLogin."', banner_clientpassword='".$cPassword."', banner_image='".$banImage."', banner_clickurl='".$banURL."', banner_impurchased='".intval($_POST['impressions_purchased'])."', banner_startdate='{$start_date}', banner_enddate='{$end_date}', banner_active='".intval($_POST['banner_class'])."', banner_campaign='".$cam."' WHERE banner_id=".intval($_POST['eid'])), 'update', BNRLAN_64, false, false);
		banners_adminlog('03',$logString);
	}
	unset($_POST['client_name'], $_POST['client_login'], $_POST['client_password'], $_POST['banner_image'], $_POST['click_url'], $_POST['impressions_purchased'], $start_date, $end_date, $_POST['banner_enabled'], $_POST['startday'], $_POST['startmonth'], $_POST['startyear'], $_POST['endday'], $_POST['endmonth'], $_POST['endyear'], $_POST['banner_class'], $_POST['banner_pages'], $_POST['banner_listtype']);
}

/* DELETE ACTIONS */
if (isset($_POST['delete_cancel']))
{
	$emessage->addSession(BNRLAN_6);

	//redirect to main
	session_write_close();
	header('Location:'.e_SELF);
	exit;
}
if ($action == "delete" && $sub_action && varsettrue($_POST['delete_confirm']))
{
	if($sql->db_Delete("banner", "banner_id=".intval($sub_action)))
	{
		$emessage->addSession(sprintf(BNRLAN_1, $sub_action), E_MESSAGE_SUCCESS);
		banners_adminlog('04','Id: '.intval($sub_action));
	}
	else $emessage->addSession(LAN_DELETED_FAILED, E_MESSAGE_WARNING);

	//redirect to main
	session_write_close();
	header('Location:'.e_SELF);
	exit;
}
elseif ($action == "delete" && $sub_action)
{ // shown only if JS is disabled or by direct url hit (?delete.banner_id)
	$emessage->add(BNRLAN_2, E_MESSAGE_WARNING);
	$text = "
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<fieldset id='core-banner-delete-confirm'>
		<legend class='e-hideme'>".BNRLAN_5."</legend>
			<div class='buttons-bar center'>
				".$frm->admin_button('delete_confirm', LAN_CONFDELETE, 'delete no-confirm')."
				".$frm->admin_button('delete_cancel', LAN_CANCEL, 'cancel')."
				<input type='hidden' name='id' value='".$sub_action."' />
			</div>
		</fieldset>
		</form>
	";
	$e107->ns->tablerender(BNRLAN_5, $emessage->render().$text);

	require_once(e_ADMIN."footer.php");
	exit;
}


if ($sql->db_Select("banner"))
{
	while ($banner_row = $sql->db_Fetch())
	{
		//extract($row); - killed by SecretR

		if (strpos($banner_row['banner_campaign'], "^") !== FALSE) {
			$campaignsplit = explode("^", $banner_row['banner_campaign']);
			$banner_row['banner_campaign'] = $campaignsplit[0];
		}

		if ($banner_row['banner_campaign']) {
			$campaigns[] = $banner_row['banner_campaign'];
		}
		if ($banner_row['banner_clientname']) {
			$clients[] = $banner_row['banner_clientname'];
		}
		if ($banner_row['banner_clientlogin']) {
			$logins[] = $banner_row['banner_clientlogin'];
		}
		if ($banner_row['banner_clientpassword']) {
			$passwords[] = $banner_row['banner_clientpassword'];
		}
	}
}


if (!$action) {
	$text = "
		<form method='post' action='".e_SELF."' id='core-banner-list-form'>
			<fieldset id='core-banner-list'>
				<legend class='e-hideme'>".BNRLAN_7."</legend>
				<table cellpadding='0' cellspacing='0' class='adminlist'>
					<colgroup span='7'>
						<col style='width: 5%'></col>
						<col style='width: 35%'></col>
						<col style='width: 10%'></col>
						<col style='width: 10%'></col>
						<col style='width: 15%'></col>
						<col style='width: 15%'></col>
						<col style='width: 10%'></col>
					</colgroup>
					<thead>
						<tr>
							<th class='center'>ID</th>
							<th>".BNRLAN_9."</th>
							<th class='center'>".BNRLAN_10."</th>
							<th class='center'>".BNRLAN_11."</th>
							<th class='center'>".BNRLAN_12."</th>
							<th class='center'>".BNRLAN_13."</th>
							<th class='center last'>".LAN_OPTIONS."</th>
						</tr>
					</thead>
					<tbody>
	";

	if (!$banner_total = $sql->db_Select("banner")) {
		$text .= "<tr><td colspan='7' class='center'>".BNRLAN_15."</td></tr>";
	} else {
		while ($banner_row = $sql->db_Fetch()) {
			//extract($row); - killed by SecretR

			$clickpercentage = ($banner_row['banner_clicks'] && $banner_row['banner_impressions'] ? round(($banner_row['banner_clicks'] / $banner_row['banner_impressions']) * 100)."%" : "-");
			$impressions_left = ($banner_row['banner_impurchased'] ? $banner_row['banner_impurchased'] - $banner_row['banner_impressions'] : BNRLAN_16);
			$impressions_purchased = ($banner_row['banner_impurchased'] ? $banner_row['banner_impurchased'] : BNRLAN_16);

			$start_date = ($banner_row['banner_startdate'] ? strftime("%d %B %Y", $banner_row['banner_startdate']) : BNRLAN_17);
			$end_date = ($banner_row['banner_enddate'] ? strftime("%d %B %Y", $banner_row['banner_enddate']) : BNRLAN_17);

			if (strpos($banner_row['banner_campaign'], "^") !== FALSE) {
				$campaignsplit = explode("^", $banner_row['banner_campaign']);
				$banner_row['banner_campaign'] = $campaignsplit[0];
				$textvisivilitychanged = "(*)";
			} else {
				$textvisivilitychanged = "";
			}

			$text .= "
						<tr>
							<td class='center'>".$banner_row['banner_id']."</td>
							<td class='e-pointer' onclick=\"e107Helper.toggle('banner-infocell-{$banner_row['banner_id']}')\">
								<a href='#banner-infocell-{$banner_row['banner_id']}' class='action e-expandit f-right' title='".BNRLAN_65."'><img class='action info S16' src='".e_IMAGE_ABS."admin_images/info_16.png' alt='' /></a>
								".($banner_row['banner_clientname'] ? $banner_row['banner_clientname'] : BNRLAN_66)."
								<div class='e-hideme clear' id='banner-infocell-{$banner_row['banner_id']}'>
									<div class='indent'>
										<div class='field-spacer'><strong>".BNRLAN_24.": </strong>".$banner_row['banner_campaign']."</div>
										<div class='field-spacer'><strong>".MENLAN_4." </strong>".r_userclass_name($banner_row['banner_active'])." ".$textvisivilitychanged."</div>
										<div class='field-spacer'><strong>".BNRLAN_45.": </strong>".$start_date."</div>
										<div class='field-spacer'><strong>".BNRLAN_21.": </strong>".$end_date."</div>
									</div>
								</div>
							</td>
							<td class='center'>".$banner_row['banner_clicks']."</td>
							<td class='center'>".$clickpercentage."</td>
							<td class='center'>".$impressions_purchased."</td>
							<td class='center'>".$impressions_left."</td>
							<td class='center'>

								<a href='".e_SELF."?create.edit.".$banner_row['banner_id']."'>".ADMIN_EDIT_ICON."</a>
								<a class='action delete' id='banner-delete-{$banner_row['banner_id']}' href='".e_SELF."?delete.".$banner_row['banner_id']."' rel='no-confirm' title='".BNRLAN_5."'>".ADMIN_DELETE_ICON."</a>
							</td>
						</tr>
				";
		}
	}
	$text .= "
					</tbody>
				</table>
				<input type='hidden' id='delete_confirm' name='delete_confirm' value='0' />
			</fieldset>
		</form>
		<script type='text/javascript'>
			\$\$('a[id^=banner-delete-]').each( function(element) {
				element.observe('click', function(e) {
					var el = e.findElement('a.delete'), msg = el.readAttribute('title') || e107.getModLan('delete_confirm');
					 e.stop();
					if( !e107Helper.confirm(msg) ) return;
					else {
						\$('delete_confirm').value = 1;
						\$('core-banner-list-form').writeAttribute('action', el.href).submit();
					}
				});
			});
		</script>
	";

	$e107->ns->tablerender(BNRLAN_42.' - '.BNRLAN_7, $emessage->render().$text);
}

if ($action == "create") {

	if ($sub_action == "edit" && $id) {

		if (!$sql->db_Select("banner", "*", "banner_id = '".$id."' " )) {
			$text .= "<div class='center'>".BNRLAN_15."</div>";
		} else {
			while ($banner_row = $sql->db_Fetch()) {
				//extract($row); - killed by SecretR

				$_POST['client_name'] = $banner_row['banner_clientname'];
				$_POST['client_login'] = $banner_row['banner_clientlogin'];
				$_POST['client_password'] = $banner_row['banner_clientpassword'];
				$_POST['banner_image'] = $banner_row['banner_image'];
				$_POST['click_url'] = $banner_row['banner_clickurl'];
				$_POST['impressions_purchased'] = $banner_row['banner_impurchased'];
				$_POST['banner_campaign'] = $banner_row['banner_campaign'];
				$_POST['banner_active'] = $banner_row['banner_active'];

				if ($banner_row['banner_startdate']) {
					$tmp = getdate($banner_row['banner_startdate']);
					$_POST['startmonth'] = $tmp['mon'];
					$_POST['startday'] = $tmp['mday'];
					$_POST['startyear'] = $tmp['year'];
				}
				if ($banner_row['banner_enddate']) {
					$tmp = getdate($banner_row['banner_enddate']);
					$_POST['endmonth'] = $tmp['mon'];
					$_POST['endday'] = $tmp['mday'];
					$_POST['endyear'] = $tmp['year'];
				}

				if (strpos($_POST['banner_campaign'], "^") !== FALSE) {
					$campaignsplit = explode("^", $_POST['banner_campaign']);
					$listtypearray = explode("-", $campaignsplit[1]);
					$listtype = $listtypearray[0];
					$campaign_pages = str_replace("|", "", $listtypearray[1]);
					$_POST['banner_campaign'] = $campaignsplit[0];
				} else {
					$_POST['banner_campaign'] = $banner_row['banner_campaign'];
				}

			}
		}
	}

	$text = "
	<form method='post' action='".e_SELF."'>
		<fieldset id='core-banner-edit'>
			<legend class='e-hideme'>".($sub_action == "edit" ? BNRLAN_22 : BNRLAN_23)."</legend>
			<table cellpadding='0' cellspacing='0' class='adminedit'>
				<colgroup span='2'>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td class='label'>".BNRLAN_24."<div class='label-note'>".BNRLAN_25."</div></td>
						<td class='control'>
	";

	if (count($campaigns)) {
		$for_var = array();
		$text .= "
							<div class='field-spacer'>
							<select name='banner_campaign_sel' id='banner_campaign_sel' class='tbox'>
								<option>".LAN_SELECT."</option>
		";
		$c = 0;
		while ($campaigns[$c]) {
			if (!isset($for_var[$campaigns[$c]])) {
				$text .= "<option".(($_POST['banner_campaign'] == $campaigns[$c]) ? " selected='selected'" : "").">".$campaigns[$c]."</option>";
				$for_var[$campaigns[$c]] = $campaigns[$c];
			}
			$c++;
		}
		unset($for_var);
		//TODO - ajax add campaign
		$text .= "
							</select> ".$frm->admin_button('add_new_campaign', BNRLAN_26a, 'action', '', array('other' => "onclick=\"e107Helper.toggle('add-new-campaign-cont', false); \$('banner_campaign_sel').selectedIndex=0; return false;\""))."
							</div>

							<div class='field-spacer e-hideme' id='add-new-campaign-cont'>
								<input class='tbox' type='text' size='30' maxlength='100' name='banner_campaign' value='' />
								<div class='field-help'>".BNRLAN_26."</div>
							</div>
		";
	}
	else
	{
		$text .= "<input class='tbox' type='text' size='30' maxlength='100' name='banner_campaign' value='' />";
	}
	$text .= "
						</td>
					</tr>
					<tr>
					<td class='label'>".BNRLAN_27."<div class='label-note'>".BNRLAN_28."</div></td>
					<td class='control'>
	";

	if (count($clients)) {
		$text .= "
						<div class='field-spacer'>
						<select name='banner_client_sel' id='banner_client_sel' class='tbox' onchange=\"Banner_Change_Details()\">
							<option>".LAN_SELECT."</option>
		";
		$c = 0;
		while ($clients[$c]) {
			if (!isset($for_var[$clients[$c]])) {
				$text .= "<option".(($_POST['client_name'] == $clients[$c]) ? " selected='selected'" : "").">".$clients[$c]."</option>";
				$for_var[$clients[$c]] = $clients[$c];
			}
			$c++;
		}
		unset($for_var);
		//TODO - ajax add client
		$text .= "
						</select> ".$frm->admin_button('add_new_client', BNRLAN_29a, 'action', '', array('other' => "onclick=\"e107Helper.toggle('add-new-client-cont', false); \$('banner_client_sel').selectedIndex=0; return false;\""))."
						</div>

						<div class='field-spacer e-hideme' id='add-new-client-cont'>
							<input class='tbox' type='text' size='30' maxlength='100' name='client_name' value='' />
							<div class='field-help'>".BNRLAN_29."</div>
						</div>
						<script type='text/javascript'>
							function Banner_Change_Details() {
								var login_field = \$('clientlogin'), password_field = \$('clientpassword'), client_field = \$('banner_client_sel');
								switch(client_field.selectedIndex-1)
								{
		";

		$c = 0;
		$i = 0;
		while ($logins[$c])
		{
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
						</script>
		";
	}
	else
	{
		$text .= "
							<input class='tbox' type='text' size='30' maxlength='100' name='client_name' value='' />
							<div class='field-help'>".BNRLAN_29."</div>
		";
	}

	$text .= "
						</td>
					</tr>
					<tr>
						<td class='label'>".BNRLAN_30."</td>
						<td class='control'>
							<input class='tbox input-text' type='text' size='30' maxlength='20' id='clientlogin' name='client_login' value='".$_POST['client_login']."' />
						</td>
					</tr>
					<tr>
						<td class='label'>".BNRLAN_31."</td>
						<td class='control'>
							<input class='tbox input-text' type='text' size='30' maxlength='50' id='clientpassword' name='client_password' value='".$_POST['client_password']."' />
						</td>
					</tr>
					<tr>
						<td class='label'>".BNRLAN_32."</td>
						<td class='control'>
							<div class='field-spacer'>
								<button class='action' type='button' value='no-value' onclick='e107Helper.toggle(\"banner-repo\")'><span>".BNRLAN_43."</span></button>
							</div>
							<div class='e-hideme' id='banner-repo'>
	";
	$c = 0;
	while ($images[$c])
	{

		$image = $images[$c]['path'].$images[$c]['fname'];

		$fileext1 = substr(strrchr($image, "."), 1);
		$fileext2 = substr(strrchr($image, "."), 0);

		$text .= "
								<div class='field-spacer'>
									".$frm->radio('banner_image', $images[$c]['fname'], (basename($image) == $_POST['banner_image']))."
		";

		if ($fileext1 == 'swf')
		{ //FIXME - swfObject
			$text .= "
									<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0' width='468' height='60'>
										<param name='movie' value='".e_IMAGE."banners/".$images[$c]['fname']."'>
										<param name='quality' value='high'><param name='SCALE' value='noborder'>
										<embed src='".e_IMAGE."banners/".$images[$c]['fname']."' width='468' height='60' scale='noborder' quality='high' pluginspage='http://www.macromedia.com/go/getflashplayer' type='application/x-shockwave-flash'></embed>
									</object>
			";
		}
		else if($fileext1 == "php" || $fileext1 == "html" || $fileext1 == "js")
		{
			$text .= $frm->label(BNRLAN_46.": ".$images[$c]['fname'],'banner_image', $images[$c]['fname']);
		}
		else
		{
			$text .= $frm->label("<img src='$image' alt='' />", 'banner_image', $images[$c]['fname']);
		}
		$text .= "
								</div>
		";

		$c++;
	}
	$text .= "
							</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".BNRLAN_33."</td>
						<td class='control'>
							<input class='tbox input-text' type='text' size='50' maxlength='150' name='click_url' value='".$_POST['click_url']."' />
						</td>
					</tr>
					<tr>
						<td class='label'>".BNRLAN_34."</td>
						<td class='control'>
							<input class='tbox input-text' type='text' size='10' maxlength='10' name='impressions_purchased' value='".$_POST['impressions_purchased']."' />
							<div class='field-help'>0 = ".BNRLAN_35."</div>
						</td>
					</tr>
					<tr>
					<td class='label'>".BNRLAN_36."</td>
					<td class='control'>
						<select name='startday' class='tbox'>
							<option value='0'>&nbsp;</option>
	";

	for($a = 1; $a <= 31; $a++) {
		$text .= "<option value='{$a}'".(($a == $_POST['startday']) ? " selected='selected'" : "").">".$a."</option>";
	}

	$text .= "
						</select>
						<select name='startmonth' class='tbox'>
							<option value='0'>&nbsp;</option>
	";
	for($a = 1; $a <= 12; $a++) {
		$text .= "<option value='{$a}'".(($a == $_POST['startmonth']) ? " selected='selected'" : "").">".$a."</option>";
	}
	$text .= "
						</select>
						<select name='startyear' class='tbox'>
							<option value='0'>&nbsp;</option>
	";
	for($a = 2003; $a <= 2010; $a++) {
		$text .= "<option value='{$a}'".(($a == $_POST['startyear']) ? " selected='selected'" : "").">".$a."</option>";
	}
	$text .= "
						</select>
						<div class='field-help'>".BNRLAN_38."</div>
					</td>
				</tr>
				<tr>
					<td class='label'>".BNRLAN_37."</td>
					<td class='control'>
						<select name='endday' class='tbox'>
							<option value='0'>&nbsp;</option>
	";
	for($a = 1; $a <= 31; $a++) {
		$text .= "<option value='{$a}'".(($a == $_POST['endday']) ? " selected='selected'" : "").">".$a."</option>";
	}
	$text .= "
						</select>
						<select name='endmonth' class='tbox'>
							<option value='0'>&nbsp;</option>";
	for($a = 1; $a <= 12; $a++) {
		$text .= "<option value='{$a}'".(($a == $_POST['endmonth']) ? " selected='selected'" : "").">".$a."</option>";
	}
	$text .= "
						</select>
						<select name='endyear' class='tbox'>
							<option value='0}'>&nbsp;</option>
	";
	for($a = 2003; $a <= 2010; $a++) {
		$text .= "<option value='{$a}'".(($a == $_POST['endyear']) ? " selected='selected'" : "").">".$a."</option>";
	}
	$text .= "
						</select>
						<div class='field-help'>".BNRLAN_38."</div>
					</td>
				</tr>
				<tr>
					<td class='label'>".MENLAN_4."</td>
					<td class='control'>
						".$e_userclass->uc_dropdown('banner_class', $_POST['banner_active'], 'public,member,guest,admin,classes,nobody,classes')."
					</td>
				</tr>
				</tbody>
			</table>
			<div class='buttons-bar center'>

	";
	if 	($sub_action == "edit" && $id) {
		$text .= "
				<input type='hidden' name='eid' value='".$id."' />
				<button class='update' type='submit' name='updatebanner' value='no-value'><span>".BNRLAN_40."</span></button>
		";
	} else {
		$text .= "
				<button class='create' type='submit' name='createbanner' value='no-value'><span>".BNRLAN_41."</span></button>
		";
	}
	$text .= "
			</div>
		</fieldset>
	</form>
		";

	$e107->ns->tablerender(BNRLAN_42.' - '.($sub_action == "edit" ? BNRLAN_22 : BNRLAN_23), $text);

}



if ($action == "menu")
{
  $in_catname = array();		// Notice removal
  $all_catname = array();

	$array_cat_in = explode("|", $menu_pref['banner_campaign']);
	if (!$menu_pref['banner_caption'])
	{
		$menu_pref['banner_caption'] = BANNER_MENU_L1;
	}

	$category_total = $sql -> db_Select("banner", "DISTINCT(banner_campaign) as banner_campaign", "ORDER BY banner_campaign", "mode=no_where");
	while ($banner_row = $sql -> db_Fetch())
	{
		//extract($row); - killed by SecretR
		$all_catname[] = $banner_row['banner_campaign'];

		if (in_array($banner_row['banner_campaign'], $array_cat_in))
		{
			$in_catname[] = $banner_row['banner_campaign'];
		}
	}


	$text = "
		<form method='post' action='".e_SELF."?menu' id='menu_conf_form'>
			<fieldset id='core-banner-menu'>
				<legend class='e-hideme'>".BANNER_MENU_L5."</legend>
				<table cellpadding='0' cellspacing='0' class='adminform'>
					<colgroup span='2'>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
					<tbody>
						<tr>
							<td class='label'>".BANNER_MENU_L3.": </td>
							<td class='control'>
								<input class='tbox input-text' type='text' name='banner_caption' size='20' value='".$menu_pref['banner_caption']."' maxlength='100' />
							</td>
						</tr>
						<tr>
							<td class='label'>".BANNER_MENU_L6."</td>
							<td class='control'>
	";
	//removed by SecretR; Reason - BAD UI, null usability
	//".BANNER_MENU_L7."<br />
	//<select class='tbox' id='catout' name='catout' size='10' style='width:180px' multiple='multiple' onchange='moveOver();'>

/*
	$catidvalues = "";
	foreach($in_catname as $name)
	{
		$text .= "<option value='{$name}'>{$name}</option>";
		$catidvalues .= $name."-";
	}


									<input class='button' type='button' value='".BANNER_MENU_L9."' onclick='removeMe();' />
									<input type='hidden' name='catid' id='catid' value='".$catidvalues."' />
								</div>
*/
	if($all_catname)
	{
		foreach($all_catname as $name)
		{
			//$text .= "<option value='{$name}'>{$name}</option>";
			$text .= "
									<div class='field-spacer'>
										".$frm->checkbox('multiaction_cat_active[]', $name, in_array($name, $in_catname)).$frm->label($name, 'multiaction_cat_active[]', $name)."
									</div>
			";
		}
		$text .= "
									<div class='field-spacer'>
										".$frm->admin_button('check_all', LAN_CHECKALL, 'action')."
										".$frm->admin_button('uncheck_all', LAN_UNCHECKALL, 'action')."
									</div>
		";
	}
	else
	{
		$text .= '<span class="warning">'.BNRLAN_67.'</span>';
	}
	$text .= "

							</td>
						</tr>
						<tr>
							<td class='label'>".BANNER_MENU_L19."</td>
							<td class='control'>
								<input class='tbox input-text' type='text' name='banner_amount' size='10' value='".$menu_pref['banner_amount']."' maxlength='2' />
							</td>
						</tr>
						<tr>
							<td class='label'>".BANNER_MENU_L10."</td>
							<td class='control'>
								<select class='tbox select' id='banner_rendertype' name='banner_rendertype'>
									".$frm->option(BANNER_MENU_L11, 0, (empty($menu_pref['banner_rendertype'])))."
									".$frm->option("1 - ".BANNER_MENU_L12, 1, ($menu_pref['banner_rendertype'] == "1"))."
									".$frm->option("2 - ".BANNER_MENU_L13, 2, ($menu_pref['banner_rendertype'] == "2"))."
									".$frm->option("3 - ".BANNER_MENU_L14, 3, ($menu_pref['banner_rendertype'] == "3"))."
								</select>
							</td>
						</tr>
					</tbody>
				</table>
				<div class='buttons-bar center'>
					<button class='update' type='submit' name='update_menu' value='no-value'><span>".BANNER_MENU_L18."</span></button>
				</div>
			</fieldset>
		</form>
	";

	/* removed - checkboxes are OK
	$text .= "

	<script type=\"text/javascript\">
		//<!--
			// Adapted from original:  Kathi O'Shea (Kathi.O'Shea@internet.com)

		function moveOver(){
		var boxLength = document.getElementById('catin').length;
		var selectedItem = document.getElementById('catout').selectedIndex;
		var selectedText = document.getElementById('catout').options[selectedItem].text;
		var selectedValue = document.getElementById('catout').options[selectedItem].value;

		var i;
		var isNew = true;
		if (boxLength != 0) {
		for (i = 0; i < boxLength; i++) {
		thisitem = document.getElementById('catin').options[i].text;
		if (thisitem == selectedText) {
		isNew = false;
		break;
		}
		}
		}
		if (isNew) {
		newoption = new Option(selectedText, selectedValue, false, false);
		document.getElementById('catin').options[boxLength] = newoption;
		document.getElementById('catout').options[selectedItem].text = '';
		}
		document.getElementById('catout').selectedIndex=-1;

		saveMe();
		}

		function removeMe() {
		var boxLength = document.getElementById('catin').length;
		var boxLength2 = document.getElementById('catout').length;
		arrSelected = new Array();
		var count = 0;
		for (i = 0; i < boxLength; i++) {
		if (document.getElementById('catin').options[i].selected) {
		arrSelected[count] = document.getElementById('catin').options[i].value;
		var valname = document.getElementById('catin').options[i].text;
		for (j = 0; j < boxLength2; j++) {
		if (document.getElementById('catout').options[j].value == arrSelected[count]){
		document.getElementById('catout').options[j].text = valname;
		}
		}
		}
		count++;
		}
		var x;
		for (i = 0; i < boxLength; i++) {
		for (x = 0; x < arrSelected.length; x++) {
		if (document.getElementById('catin').options[i].value == arrSelected[x]) {
		document.getElementById('catin').options[i] = null;
		}
		}
		boxLength = document.getElementById('catin').length;
		}

		saveMe();
		}

		//function clearMe(clid){
		// location.href = document.location + \"?clear.\" + clid;
		//}

		function saveMe(clid) {
		var strValues = \"\";
		var boxLength = document.getElementById('catin').length;
		var count = 0;
		if (boxLength != 0) {
		for (i = 0; i < boxLength; i++) {
		if (count == 0) {
		strValues = document.getElementById('catin').options[i].value;
		}
		else {
		strValues = strValues + \"-\" + document.getElementById('catin').options[i].value;
		}
		count++;
		}
		}
		if (strValues.length == 0) {
		//alert(\"You have not made any selections\");
		document.getElementById('catid').value = \"\";
		}
		else {
		document.getElementById('catid').value = strValues;
		}
		}

		// -->
		</script>
	";
*/
	$e107->ns->tablerender(BNRLAN_68, $emessage->render().$text);
}


function admin_banner_adminmenu() {

	global $action;
	$act = $action;
	if ($act == "") {
		$act = "main";
	}
	$var['main']['text'] = BNRLAN_58;
	$var['main']['link'] = e_SELF;

	$var['create']['text'] = BNRLAN_59;
	$var['create']['link'] = e_SELF."?create";

	$var['menu']['text'] = BNRLAN_61;
	$var['menu']['link'] = e_SELF."?menu";

	e_admin_menu(BNRLAN_62, $act, $var);
}

require_once(e_ADMIN."footer.php");


// Log event to admin log
function banners_adminlog($msg_num='00', $woffle='')
{
  global $pref, $admin_log;
//  if (!varset($pref['admin_log_log']['admin_banners'],0)) return;
  $admin_log->log_event('BANNER_'.$msg_num,$woffle,E_LOG_INFORMATIVE,'');
}

/**
 * Handle page DOM within the page header
 *
 * @return string JS source
 */
function headerjs()
{
	require_once(e_HANDLER.'js_helper.php');
	$ret = "
		<script type='text/javascript'>
			//add required core lan - delete confirm message
			(".e_jshelper::toString(LAN_JSCONFIRM).").addModLan('core', 'delete_confirm');
			if(typeof e107Admin == 'undefined') var e107Admin = {}

			/**
			 * OnLoad Init Control
			 */
			e107Admin.initRules = {
				'Helper': true,
				'AdminMenu': false
			}
		</script>
		<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>
	";

	return $ret;
}

?>