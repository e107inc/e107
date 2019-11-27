<?php 
/*
 * e107 website system
 *
 * Copyright (C) 2008-2024 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - Site Maintenance
 *
 */
require_once ('../class2.php');

if(!getperms('9'))
{
	e107::redirect('admin');
	exit();
}

$e_sub_cat = 'maintain';

e107::coreLan('ugflag', true);

$mes = e107::getMessage();
$frm = e107::getForm();

if(isset($_POST['updatesettings']))
{
	$changed = FALSE;
	$temp = intval($_POST['maintainance_flag']);
	if($pref['maintainance_flag'] != $temp)
	{
		$pref['maintainance_flag'] = $temp;
		$changed = TRUE;
	}
	$temp = $tp->toDB($_POST['maintainance_text']);
	if($pref['maintainance_text'] != $temp)
	{
		$pref['maintainance_text'] = $temp;
		$changed = TRUE;
	}
	
	$temp = intval($_POST['main_admin_only']);
	if(getperms('0') && $pref['main_admin_only'] != $temp)
	{
		$pref['main_admin_only'] = $temp;
		$changed = TRUE;
	}
	
	if($changed)
	{
		e107::getLog()->add(($pref['maintainance_flag'] == 0) ? 'MAINT_02' : 'MAINT_01', $pref['maintainance_text'], E_LOG_INFORMATIVE, '');
		save_prefs();
	//	$mes->addSuccess(UGFLAN_1);
	}
	else
	{
		$mes->addInfo(LAN_NO_CHANGE);
	}

	$pref = e107::getConfig('core', true, true)->getPref();
		
	if(!e_AJAX_REQUEST)
	{
	//	header("location:".e_SELF);
	//	exit();
	}
}

require_once("auth.php");

$text = "
	<form method='post' action='".e_SELF."' id='core-ugflag-form'>
		<fieldset id='core-ugflag'>
			<legend class='e-hideme'>".UGFLAN_4."</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>";
				
$elements = array(
	e_UC_PUBLIC		=> LAN_DISABLED,
	e_UC_MEMBER		=> ADLAN_110,
	e_UC_ADMIN		=> UGFLAN_8,
	e_UC_MAINADMIN	=> UGFLAN_9
);
	 
$text .= "
					<tr>
						<td>".UGFLAN_2.": </td>
						<td>".$frm->radio('maintainance_flag', $elements, $pref['maintainance_flag'], TRUE)."</td>
					</tr>";

//TODO multilanguage pref					
$text .= "
					<tr>
						<td>".UGFLAN_5."</td>
						<td>".$frm->bbarea('maintainance_text', vartrue($pref['maintainance_text']), 'maintenance', '_common', 'small')."<div class='smalltext clear'>".UGFLAN_6."</div></td>
					</tr>
				</tbody>
			</table>
			<div class='buttons-bar center'>
				".$frm->admin_button('updatesettings', LAN_UPDATE, 'update')."
			</div>
		</fieldset>
	</form>

";

//Ajax Support
if(!e_AJAX_REQUEST)
{
	echo "<div id='ajax-container'>\n";
	$ns->tablerender(UGFLAN_4, $mes->render().$text, 'core-ugflag');
	echo "\n</div>";
	require_once (e_ADMIN."footer.php");
	exit();
}

$ns->tablerender(UGFLAN_4, $mes->render().$text, 'core-ugflag');

/**
 * Handle page DOM within the page header
 *
 * @return string JS source
 */

 /*
function headerjs()
{
	$ret = "
		<script type='text/javascript'>
			//Ajax Support
			var CoreUgflagAjaxPage = function(e_event) {
				\$('updatesettings').observe('click', function(event) {
					var form = \$('core-ugflag-form');
					if(form) {
						event.stop();
						form.submitForm('ajax-container', { overlayPage: 'core-ugflag', parameters: { updatesettings: 1 } });
					}
				});
			}
			e107.runOnLoad(CoreUgflagAjaxPage, 'ajax-container', true);

			//Admin JS Init Rules
			var e107Admin = {}
			e107Admin.initRules = {
				'Helper': true,
				'AdminMenu': false
			}

		</script>
		<script type='text/javascript' src='".e_JS."core/admin.js'></script>
	";
	
	return $ret;
}

 */