<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Banner Administration
 *
*/

/**
 *	e107 Banner management plugin
 *
 *	Handles the display and sequencing of banners on web pages, including counting impressions
 *
 *	@package	e107_plugins
 *	@subpackage	banner
 *
 */


// TODO FIXME needs validation (e.g. Click URL field is not checked  to be sure it's an URL) - also required fields?

require_once('../../class2.php');
if (!getperms('D'))
{
	header('location:'.e_BASE.'index.php');
	exit;
}

$e_sub_cat = 'banner';

require_once(e_ADMIN.'auth.php');
require_once(e_HANDLER.'userclass_class.php');
require_once(e_HANDLER.'file_class.php');
$fl = e107::getFile();
$frm = e107::getForm();
$mes = e107::getMessage();
$tp = e107::getParser();

include_lan(e_PLUGIN.'banner/languages/'.e_LANGUAGE.'_admin_banner.php');


if(e_QUERY)
{
	list($action, $sub_action, $id) = explode('.', e_QUERY);
}

$images = $fl->get_files(e_IMAGE.'banners/','','standard');


$menu_pref = e107::getConfig('menu')->getPref('');
if (isset($_POST['update_menu']))
{
	$temp['banner_caption']		= $tp->toDB($_POST['banner_caption']);
	$temp['banner_amount']		= intval($_POST['banner_amount']);
	$temp['banner_rendertype']	= intval($_POST['banner_rendertype']);

	if (isset($_POST['multiaction_cat_active']))
	{
		/*$array_cat = explode("-", $_POST['catid']);
		$cat='';
		for($i = 0; $i < count($array_cat); $i++)
		{
			$cat .= $tp->toDB($array_cat[$i])."|";
		}
		$cat = substr($cat, 0, -1);*/
		$cat = implode('|', $tp->toDB($_POST['multiaction_cat_active']));
		$temp['banner_campaign'] = $cat;
	}
	if ($admin_log->logArrayDiffs($temp,$menu_pref,'BANNER_01'))
	{
		$menuPref = e107::getConfig('menu');
		//e107::getConfig('menu')->setPref('', $menu_pref);
		//e107::getConfig('menu')->save(false, true, false);
		foreach ($temp as $k => $v)
		{
			$menuPref->setPref($k, $v);
		}
		$menuPref->save(false, true, false);

		//banners_adminlog('01', $menu_pref['banner_caption'].'[!br!]'.$menu_pref['banner_amount'].', '.$menu_pref['banner_rendertype'].'[!br!]'.$menu_pref['banner_campaign']);
	}
}



if (vartrue($_POST['createbanner']) || vartrue($_POST['updatebanner']))
{
	$start_date = vartrue(e107::getDate()->convert($_POST['banner_startdate'],'inputdate'), 0);
	$end_date 	= vartrue(e107::getDate()->convert($_POST['banner_enddate'],'inputdate'), 0);
	$cli 		= $tp->toDB($_POST['client_name'] ? $_POST['client_name'] : $_POST['banner_client_sel']);
	$cLogin 	= $tp->toDB($_POST['client_login']);
	$cPassword 	= $tp->toDB($_POST['client_password']);
	$banImage 	= $tp->toDB($_POST['banner_image']);
	$banURL 	= $tp->toDB($_POST['click_url']);
	$cam 		= $tp->toDB($_POST['banner_campaign'] ? $_POST['banner_campaign'] : $_POST['banner_campaign_sel']);

	$logString .= $cam.'[!br!]'.$cli.'[!br!]'.$banImage.'[!br!]'.$banURL;
	if ($_POST['createbanner'])
	{
		e107::getMessage()->addAuto($sql->db_Insert("banner", "0, '".$cli."', '".$cLogin."', '".$cPassword."', '".$banImage."', '".$banURL."', '".intval($_POST['impressions_purchased'])."', '".$start_date."', '".$end_date."', '".intval($_POST['banner_class'])."', 0, 0, '', '".$cam."'"), 'insert', LAN_CREATED, false, false);
		banners_adminlog('02',$logString);
	}
	else // updating, not creating
	{
		e107::getMessage()->addAuto($sql->db_Update("banner", "banner_clientname='".$cli."', banner_clientlogin='".$cLogin."', banner_clientpassword='".$cPassword."', banner_image='".$banImage."', banner_clickurl='".$banURL."', banner_impurchased='".intval($_POST['impressions_purchased'])."', banner_startdate='".$start_date."', banner_enddate='".$end_date."', banner_active='".intval($_POST['banner_class'])."', banner_campaign='".$cam."' WHERE banner_id=".intval($_POST['eid'])), 'update', LAN_UPDATED, false, false);
		banners_adminlog('03',$logString);
	}

	unset($_POST['client_name'], $_POST['client_login'], $_POST['client_password'], $_POST['banner_image'], $_POST['click_url'], $_POST['impressions_purchased'], $start_date, $end_date, $_POST['banner_enabled'], $_POST['banner_startdate'], $_POST['banner_enddate'], $_POST['banner_class'], $_POST['banner_listtype']);
}

/* DELETE ACTIONS */
if (isset($_POST['delete_cancel'])) // delete cancelled - redirect back to 'manage'
{
	session_write_close();
	header('Location:'.e_SELF);
	exit;
}

if (vartrue($action) == "delete" && $sub_action && varsettrue($_POST['delete_confirm'])) // delete has been confirmed, process
{
	if($sql->db_Delete("banner", "banner_id=".intval($sub_action)))
	{
		$mes->addSuccess(LAN_DELETED);
		banners_adminlog('04','Id: '.intval($sub_action));
		header('Location:'.e_SELF);
		exit;
	}
	else  // delete failed - redirect back to 'manage' and display message
	{
		$mes->addWarning(LAN_DELETED_FAILED);
		session_write_close();
		header('Location:'.e_SELF);
		exit;
	}
}
elseif ($action == "delete" && $sub_action) // confirm delete
{ // shown only if JS is disabled or by direct url hit (?delete.banner_id)
	$mes->addWarning(LAN_CONFIRMDEL);
	$text = "
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<fieldset id='core-banner-delete-confirm'>
		<legend class='e-hideme'>".LAN_CONFIRMDEL."</legend>
			<div class='buttons-bar center'>
				".$frm->admin_button('delete_confirm', LAN_CONFDELETE, 'delete')."
				".$frm->admin_button('delete_cancel', LAN_CANCEL, 'cancel')."
				<input type='hidden' name='id' value='".$sub_action."' />
			</div>
		</fieldset>
		</form>
	";
	$ns->tablerender(LAN_CONFDELETE, $mes->render() . $text);

	require_once(e_ADMIN."footer.php");
	exit;
}


if ($sql->select("banner"))
{
	while ($banner_row = $sql->fetch())
	{
		if (strpos($banner_row['banner_campaign'], "^") !== FALSE) {
			$campaignsplit = explode("^", $banner_row['banner_campaign']);
			$banner_row['banner_campaign'] = $campaignsplit[0];
		}

		if ($banner_row['banner_campaign']) 
		{
			$campaigns[$banner_row['banner_campaign']] = $banner_row['banner_campaign'];
		}
		
		if ($banner_row['banner_clientname']) 
		{
			$clients[$banner_row['banner_clientname']] = $banner_row['banner_clientname'];
		}

		if ($banner_row['banner_clientlogin']) 
		{
			$logins[] = $banner_row['banner_clientlogin'];
		}
		
		if ($banner_row['banner_clientpassword']) 
		{
			$passwords[] = $banner_row['banner_clientpassword'];
		}
	}
}


if (!$action) 
{	
	if (!$banner_total = $sql->select("banner")) 
	{
		$mes->addInfo(BNRLAN_00); 
	} 
	else 
	{
		$text = "
		<form method='post' action='".e_SELF."' id='core-banner-list-form'>
			<fieldset id='core-banner-list'>
				<legend class='e-hideme'>".LAN_MANAGE."</legend>
				<table class='table adminlist'>
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
							<th class='center'>".LAN_ID."</th>
							<th>".BNRLAN_1."</th>
							<th class='center'>".BNRLAN_2."</th>
							<th class='center'>".BNRLAN_3."</th>
							<th class='center'>".BNRLAN_4."</th>
							<th class='center'>".BNRLAN_5."</th>
							<th class='center'>".LAN_OPTIONS."</th>
						</tr>
					</thead>
					<tbody>";

		while ($banner_row = $sql->fetch())
		{

			$clickpercentage = ($banner_row['banner_clicks'] && $banner_row['banner_impressions'] ? round(($banner_row['banner_clicks'] / $banner_row['banner_impressions']) * 100)."%" : "-");
			$impressions_left = ($banner_row['banner_impurchased'] ? $banner_row['banner_impurchased'] - $banner_row['banner_impressions'] : BNRLAN_6);
			$impressions_purchased = ($banner_row['banner_impurchased'] ? $banner_row['banner_impurchased'] : BNRLAN_6);

			$start_date = ($banner_row['banner_startdate'] ? strftime("%d %B %Y", $banner_row['banner_startdate']) : LAN_NONE);
			$end_date = ($banner_row['banner_enddate'] ? strftime("%d %B %Y", $banner_row['banner_enddate']) : LAN_NONE);

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
								<a href='#banner-infocell-{$banner_row['banner_id']}' class='action e-expandit f-right' title='".BNRLAN_7."'><img class='action info S16' src='".E_16_CAT_ABOUT."' alt='' /></a>
								".($banner_row['banner_clientname'] ? $banner_row['banner_clientname'] : BNRLAN_8)."
								<div class='e-hideme clear' id='banner-infocell-{$banner_row['banner_id']}'>
									<div class='indent'>
										<div class='field-spacer'><strong>".BNRLAN_24.": </strong>".$banner_row['banner_campaign']."</div>
										<div class='field-spacer'><strong>".LAN_VISIBILITY." </strong>".r_userclass_name($banner_row['banner_active'])." ".$textvisivilitychanged."</div>
										<div class='field-spacer'><strong>".BNRLAN_9.": </strong>".$start_date."</div>
										<div class='field-spacer'><strong>".BNRLAN_10.": </strong>".$end_date."</div>
									</div>
								</div>
							</td>
							<td class='center'>".$banner_row['banner_clicks']."</td>
							<td class='center'>".$clickpercentage."</td>
							<td class='center'>".$impressions_purchased."</td>
							<td class='center'>".$impressions_left."</td>
							<td class='center options'>

								<a class='btn btn-large' href='".e_SELF."?create.edit.".$banner_row['banner_id']."'>".ADMIN_EDIT_ICON."</a>
								<a class='btn btn-large action delete' id='banner-delete-{$banner_row['banner_id']}' href='".e_SELF."?delete.".$banner_row['banner_id']."' rel='no-confirm' title='".LAN_CONFDELETE."'>".ADMIN_DELETE_ICON."</a>
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

	$ns->tablerender(LAN_PLUGIN_BANNER_NAME.SEP.LAN_MANAGE, $mes->render() . $text);
}

if ($action == "create") 
{
	if ($sub_action == "edit" && $id) 
	{
		if (!$sql->select("banner", "*", "banner_id = '".$id."'")) // FIXME check not working for some reason
		{ 
			$mes->addWarning(BNRLAN_01); 
		} 
		else 
		{
			while ($banner_row = $sql->fetch()) 
			{
				$_POST['client_name'] = $banner_row['banner_clientname'];
				$_POST['client_login'] = $banner_row['banner_clientlogin'];
				$_POST['client_password'] = $banner_row['banner_clientpassword'];
				$_POST['banner_image'] = $banner_row['banner_image'];
				$_POST['click_url'] = $banner_row['banner_clickurl'];
				$_POST['impressions_purchased'] = $banner_row['banner_impurchased'];
				$_POST['banner_campaign'] = $banner_row['banner_campaign'];
				$_POST['banner_active'] = $banner_row['banner_active'];
				$_POST['banner_startdate'] = $banner_row['banner_startdate'];
				$_POST['banner_enddate'] = $banner_row['banner_enddate'];

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
			<legend class='e-hideme'>".($sub_action == "edit" ? LAN_UPDATE : LAN_CREATE)."</legend>
			<table class='table adminform'>
				<colgroup span='2'>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>".BNRLAN_11."</td>
						<td>
	";


	if (count($campaigns)) 
	{
		$text .= $frm->selectbox('banner_campaign_sel',$campaigns,$_POST['banner_campaign'],'',LAN_SELECT);
		$text .= $frm->text('banner_campaign','','',array('placeholder'=> 'Or enter a new campaign'));	
	}
	else
	{
		$text .= $frm->text('banner_campaign');	
	}
	$text .= "<span class='field-help'>".BNRLAN_25."</span>
		</td>
	</tr>

	<tr>
		<td>".BNRLAN_1."</td>
		<td>
	";

	if (count($clients)) 
	{
		$text .= $frm->selectbox('banner_client_sel',$clients, $_POST['client_name'],'', LAN_SELECT);
		$text .= $frm->text('client_name','','',array('placeholder'=> 'Or enter a new client'));	
		
		
		/*
		
		
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
		//TODO - ajax add client FIXME - currently not working as intended
		$text .= "
						</select> ".$frm->admin_button('add_new_client', BNRLAN_30, 'other', '', array('other' => "onclick=\"e107Helper.toggle('add-new-client-cont', false); \$('banner_client_sel').selectedIndex=0; return false;\""))."
						</div>

						<div class='field-spacer e-hideme' id='add-new-client-cont'>
							<input class='tbox' type='text' size='30' maxlength='100' name='client_name' value='' />
							<span class='field-help'>".BNRLAN_29."</span>
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
		 
		 */
	}
	else
	{
		
		$text .= $frm->text('client_name',$_POST['client_name']);	
		$text .= "<span class='field-help'>".BNRLAN_29."</span>";
	}

	$text .= "
						<span class='field-help'>".BNRLAN_28."</span></td>
					</tr>
					<tr>
						<td>".BNRLAN_12."</td>
						<td>".$frm->text('client_login', $_POST['client_login'], '20')."</td>
					</tr>
					<tr>
						<td>".BNRLAN_13."</td>
						<td>".$frm->password('client_password', $_POST['client_password'], '50','strength=1&generate=1&required=0')."</td>
					</tr>
					<tr>
						<td>".BNRLAN_14."</td>
						<td>".$frm->imagepicker('banner_image', $_POST['banner_image'], '', 'media=banner&w=600');
						
						
						
	/*					
						$text .= "
							<div class='field-spacer'>
								<button class='btn button action' type='button' value='no-value' onclick='e107Helper.toggle(\"banner-repo\")'><span>".BNRLAN_32."</span></button> 
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
			$text .= $frm->label(BNRLAN_33.": ".$images[$c]['fname'],'banner_image', $images[$c]['fname']);
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
							</div>";
		*/					
							
			$text .= "
						</td>
					</tr>
					<tr>
						<td>".BNRLAN_15."</td>
						<td>".$frm->text('click_url', $_POST['click_url'], '150')."</td>
					</tr>
					<tr>
						<td>".BNRLAN_16."</td>
						<td>".$frm->number('impressions_purchased', $_POST['impressions_purchased'], 10)."<span class='field-help'>".BNRLAN_31."</span></td>
					</tr>
					<tr>
						<td>".BNRLAN_17."</td>
						<td>".$frm->datepicker('banner_startdate', $_POST['banner_startdate'],'type=date')."</td>
					</tr>
					<tr>
						<td>".BNRLAN_18."</td>
						<td>".$frm->datepicker('banner_enddate', $_POST['banner_enddate'],'type=date')."</td>			
					</tr>
					<tr>
						<td>".LAN_VISIBILITY."</td>
						<td>
							".$e_userclass->uc_dropdown('banner_class', $_POST['banner_active'], 'public,member,guest,admin,classes,nobody,classes')."
						</td>
					</tr>
					</tbody>
				</table>
				
				<div class='buttons-bar center'>";
				
	if 	($sub_action == "edit" && $id) 
	{
		$text .= $frm->admin_button('updatebanner','no-value','create', LAN_UPDATE);
		$text .= "<input type='hidden' name='eid' value='".$id."' />";
	} 
	else 
	{
		$text .= $frm->admin_button('createbanner','no-value','create', LAN_CREATE);
	}

	$text .= "
			</div>
		</fieldset>
	</form>";

	$ns->tablerender(LAN_PLUGIN_BANNER_NAME.SEP.($sub_action == "edit" ? LAN_UPDATE : LAN_CREATE), $text);

}



if ($action == "menu")
{
  $in_catname = array();		// Notice removal
  $all_catname = array();

	$array_cat_in = explode("|", $menu_pref['banner_campaign']);
	if (!$menu_pref['banner_caption'])
	{
		$menu_pref['banner_caption'] = BNRLAN_38;
	}

	$category_total = $sql -> select("banner", "DISTINCT(banner_campaign) as banner_campaign", "ORDER BY banner_campaign", "mode=no_where");
	while ($banner_row = $sql -> fetch())
	{
		$all_catname[] = $banner_row['banner_campaign'];

		if (in_array($banner_row['banner_campaign'], $array_cat_in))
		{
			$in_catname[] = $banner_row['banner_campaign'];
		}
	}


	$text = "
		<form method='post' action='".e_SELF."?menu' id='menu_conf_form'>
			<fieldset id='core-banner-menu'>
				<legend class='e-hideme'>".BNRLAN_36."</legend>
				<table class='table adminform'>
					<colgroup span='2'>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
					<tbody>
						<tr>
							<td>".BNRLAN_37."</td>
							<td>".$frm->text('banner_caption', $menu_pref['banner_caption'])."</td>
						</tr>
						<tr>
							<td>".BNRLAN_39."</td>
							<td>
	";

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
										".$frm->admin_button('check_all', LAN_CHECKALL, 'other')."
										".$frm->admin_button('uncheck_all', LAN_UNCHECKALL, 'other')."
									</div>
		";
	}
	else
	{
		$text .= BNRLAN_40;
	}
	$text .= "

							</td>
						</tr>
						<tr>
							<td>".BNRLAN_41."</td>
							<td>".$frm->text('banner_amount', $menu_pref['banner_amount'], 2, array ('class' => 'tbox input-text'))."<span class='field-help'>".BNRLAN_42."</span></td>
						</tr>
						<tr>
							<td>".BNRLAN_43."</td>
							<td>
								<select class='tbox select' id='banner_rendertype' name='banner_rendertype'>
									".$frm->option(BNRLAN_44, 0, (empty($menu_pref['banner_rendertype'])))."
									".$frm->option("1 - ".BNRLAN_45, 1, ($menu_pref['banner_rendertype'] == "1"))."
									".$frm->option("2 - ".BNRLAN_46, 2, ($menu_pref['banner_rendertype'] == "2"))."
									".$frm->option("3 - ".BNRLAN_47, 3, ($menu_pref['banner_rendertype'] == "3"))."
								</select>
							</td>
						</tr>
					</tbody>
				</table>
				<div class='buttons-bar center'>".
					$frm->admin_button('update_menu','no-value','update', LAN_UPDATE)."
				</div>
			</fieldset>
		</form>
	";

	$ns->tablerender(LAN_PLUGIN_BANNER_NAME.SEP.BNRLAN_36, $mes->render() . $text);
}


function admin_banner_adminmenu() 
{

	$qry = e_QUERY;
	$act = vartrue($qry,'main');
	
	$var['main']['text'] = LAN_MANAGE;
	$var['main']['link'] = e_SELF;

	$var['create']['text'] = LAN_CREATE;
	$var['create']['link'] = e_SELF."?create";

	$var['menu']['text'] = BNRLAN_35;
	$var['menu']['link'] = e_SELF."?menu";

	e107::getNav()->admin(LAN_PLUGIN_BANNER_NAME, $act, $var);
}

require_once(e_ADMIN."footer.php");


// Log event to admin log
function banners_adminlog($msg_num='00', $woffle='')
{
  global $admin_log;
  $pref = e107::getPref();

//  if (!varset($pref['admin_log_log']['admin_banners'],0)) return;
  $admin_log->log_event('BANNER_'.$msg_num,$woffle, E_LOG_INFORMATIVE,'');
}

?>