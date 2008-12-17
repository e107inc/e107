<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - Site Preferences
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/prefs.php,v $
 * $Revision: 1.18 $
 * $Date: 2008-12-17 17:27:07 $
 * $Author: secretr $
 *
*/
require_once ("../class2.php");

require_once (e_HANDLER."userclass_class.php");
require_once (e_HANDLER."user_extended_class.php");
$e_userclass = new user_class();
$ue = new e107_user_extended();

if(isset($_POST['newver']))
{
	header("location:http://e107.org/index.php");
	exit();
}

if(! getperms("1"))
{
	header("location:".e_BASE."index.php");
	exit();
}

$e_sub_cat = 'prefs';
if(! $pref['timezone'])
{
	$pref['timezone'] = "GMT";
}

require_once (e_HANDLER."form_handler.php");
require_once (e_HANDLER."message_handler.php");
$rs = new form();
$frm = new e_form(true); //enable inner tabindex counter
$emessage = &eMessage::getInstance();

/*	RESET DISPLAY NAMES	*/
if($_POST['submit_resetdisplaynames'])
{
	$e107->sql->db_Update("user", "user_name=user_loginname");
	$emessage->add(PRFLAN_157);
}

/*	UPDATE PREFERENCES */
if(isset($_POST['updateprefs']))
{
	unset($_POST['updateprefs'], $_POST['sitelanguage']);

	$_POST['cookie_name'] = str_replace(array(" ", "."), "_", $_POST['cookie_name']);
	$_POST['cookie_name'] = preg_replace("#[^a-zA-Z0-9_]#", "", $_POST['cookie_name']);

	$_POST['siteurl'] = trim($_POST['siteurl']) ? trim($_POST['siteurl']) : SITEURL;
	$_POST['siteurl'] = substr($_POST['siteurl'], - 1) == "/" ? $_POST['siteurl'] : $_POST['siteurl']."/";

	// If email verification, email address is required!
	if($_POST['user_reg_veri'] == 1)
		$_POST['disable_emailcheck'] = 0;

	// Table of range checking values - min and max for numerics. Only do the important ones
	$pref_limits = array('loginname_maxlength' => array('min' => 10, 'max' => 100, 'default' => 30), 'displayname_maxlength' => array('min' => 5, 'max' => 30, 'default' => 15), 'antiflood_timeout' => array('min' => 3, 'max' => 300, 'default' => 10));

	$prefChanges = array();
	foreach($_POST as $key => $value)
	{
		if(isset($pref_limits[$key]))
		{ // Its a numeric value to check
			if(is_numeric($value))
			{
				if($value < $pref_limits[$key]['min'])
					$value = $pref_limits[$key]['min'];
				if($value > $pref_limits[$key]['max'])
					$value = $pref_limits[$key]['max'];
			}
			else
			{
				$value = $pref_limits[$key]['default'];
			}
			$newValue = $value;
		}
		else
		{
			$newValue = $tp->toDB($value);
		}
		if($newValue != $pref[$key])
		{ // Changed value
			$pref[$key] = $newValue;
			$prefChanges[$key] = $newValue;
		}
	}

	if(count($prefChanges))
	{ // Values have changed
		$e107cache->clear('', TRUE);
		$saved = save_prefs();
		$logStr = '';
		foreach($prefChanges as $k => $v)
		{
			$logStr .= "[!br!]{$k} => {$v}";
		}
		$admin_log->log_event('PREFS_01', PRFLAN_195.$logStr, '');
		$e107->sql->db_Select_gen("TRUNCATE ".MPREFIX."online");
	}
	if($saved)
	{
		$emessage->addSession(PRFLAN_106, E_MESSAGE_SUCCESS);
		header("location:".e_ADMIN."prefs.php?u");
		exit();
	}
	else
	{
		include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_admin.php');
		$emessage->add(LAN_NO_CHANGE);
	}
}

if($e107->sql->db_Select("plugin", "plugin_path", "plugin_installflag='1' AND plugin_path='alt_auth'"))
{
	$authlist[] = "e107";
	$handle = opendir(e_PLUGIN."alt_auth");
	while($file = readdir($handle))
	{
		if(preg_match("/^(.*)_auth\.php/", $file, $match))
		{
			$authlist[] = $match[1];
		}
	}
}

if($authlist)
{
	$auth_dropdown .= "<select class='tbox' name='auth_method'>";
	foreach($authlist as $a)
	{
		$s = ($pref['auth_method'] == $a ? " selected='selected' " : "");
		$auth_dropdown .= "<option{$s}>".$a."</option>";
	}
	$auth_dropdown .= "</select>";
}
else
{
	$auth_dropdown = "<input type='hidden' name='auth_method' value='' />".PRFLAN_151;
	$pref['auth_method'] = "";
}

require_once (e_ADMIN."auth.php");
/*
if(isset($message))
{
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

if(e_QUERY == "u")
{
	$ns->tablerender("", "<div style='text-align:center'><b>".PRFLAN_106."</b></div>");
}
*/
$handle = opendir(e_ADMIN.'includes/');
while($file = readdir($handle))
{
	if($file != "." && $file != "..")
	{
		$file = str_replace(".php", "", $file);
		$adminlist[] = $file;
	}
}
closedir($handle);

$text = "
	<script type=\"text/javascript\">
	<!--

	var e107Admin.AdminMenu = {
		init: function() {
			this.location = document.location.hash.substring(1);
			this.activeTab = \$(this.location);
			if(this.activeTab) {
				this.activeTab.show();
			}

			this->_observer = this.observe.bindAsEventListener();
		},

		switch: function(show) {
			show = \$(show);
			if(!show) return;
			if(this.activeTab && this.activeTab.identify() != show.identify()) {
				this.activeTab.hide();
				this.activeTab = show.show();
			}
		},

		observe: function(event) {

		}
	}
	var hideid=\"core-prefs-main\";
	function showhideit(showid){
		if (hideid!=showid){
			show=document.getElementById(showid).style;
			hide=document.getElementById(hideid).style;
			show.display=\"\";
			hide.display=\"none\";
			hideid = showid;
		}
	}
	//-->
	</script>
	<div id='core-prefs'>
	<form method='post' action='".e_SELF."'>
		<fieldset id='core-prefs-main'>
			<legend class='e-hideme'>".PRFLAN_1."</legend>
			<table cellpadding='0' cellspacing='0' class='adminform'>
				<colgroup span='2'>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td class='label'>".PRFLAN_2."</td>
						<td class='control'>
							".$frm->text('sitename', $pref['sitename'], 100)."
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_3."</td>
						<td class='control'>
							".$frm->text('siteurl', $pref['siteurl'], 150)."
							".($pref['siteurl'] == SITEURL ? "" : "<div class='smalltext'>( ".PRFLAN_159.": <strong>".SITEURL."</strong> )</div>")."
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_134."</td>
						<td class='control'>
							".$frm->radio('redirectsiteurl', 1, $pref['redirectsiteurl']).$frm->label(LAN_ENABLED, 'redirectsiteurl', 1)."&nbsp;&nbsp;
							".$frm->radio('redirectsiteurl', 0, !$pref['redirectsiteurl']).$frm->label(LAN_DISABLED, 'redirectsiteurl', 0)."
							<div class='field-help'>".PRFLAN_135."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_4."</td>
						<td class='control'>
";

$parms = "name=sitebutton";
$parms .= "&path=".e_THEME.$pref['sitetheme']."/images/|".e_IMAGE;
$parms .= "&filter=0";
$parms .= "&fullpath=1";
$parms .= "&default=".urlencode($pref['sitebutton']);
//$parms .= "&width=128px";
//$parms .= "&height=128px";
$parms .= "&multiple=FALSE";
$parms .= "&label=-- No Image --";
$parms .= "&subdirs=1";
$parms .= "&tabindex=".$frm->getNext();

$text .= $tp->parseTemplate("{IMAGESELECTOR={$parms}&scaction=select}");
$text .= "<div class='field-help'>".$tp->parseTemplate("{IMAGESELECTOR={$parms}&scaction=preview}")."</div>";

$text .= "
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_5."</td>
						<td class='control'>
							".$frm->textarea('sitetag', $pref['sitetag'], 3, 59)."
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_6."</td>
						<td class='control'>
							".$frm->textarea('sitedescription', $pref['sitedescription'], 6, 59)."
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_7."</td>
						<td class='control'>
							".$frm->text('siteadmin', SITEADMIN, 100)."
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_8."</td>
						<td class='control'>
							".$frm->text('siteadminemail', SITEADMINEMAIL, 100)."
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_174."</td>
						<td class='control'>
							".$frm->text('replyto_name', $pref['replyto_name'], 100)."
							<div class='smalltext field-help'>".PRFLAN_175."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_176."</td>
						<td class='control'>
							".$frm->text('replyto_email', $pref['replyto_email'], 100)."
							<div class='smalltext field-help'>".PRFLAN_177."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_162."</td>
						<td class='control'>
							".$frm->textarea('sitecontactinfo', $pref['sitecontactinfo'], 6, 59)."
							<div class='smalltext field-help'>".PRFLAN_163."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_168."</td>
						<td class='control'>
							".$e_userclass->uc_dropdown('sitecontacts', $pref['sitecontacts'], 'nobody,main,admin,userclasses', "tabindex='".$frm->getNext()."'")."
							<div class='smalltext field-help'>".PRFLAN_169."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_164."</td>
						<td class='control'>
							".$frm->radio('contact_emailcopy', 1, $pref['contact_emailcopy'])."
							".$frm->label(LAN_ENABLED, 'contact_emailcopy', 1)."&nbsp;&nbsp;
							".$frm->radio('contact_emailcopy', 0, !$pref['contact_emailcopy'])."
							".$frm->label(LAN_DISABLED, 'contact_emailcopy', 0)."
							<div class='smalltext field-help'>".PRFLAN_165."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_9."</td>
						<td class='control'>
							".$frm->textarea('sitedisclaimer', str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $pref['sitedisclaimer']), 6, 59)."
						</td>
					</tr>
				</tbody>
			</table>
			".pref_submit('main')."
		</fieldset>
";

$text .= "
		<fieldset class='e-hideme' id='core-prefs-display'>
			<legend class='e-hideme'>".PRFLAN_13."</legend>
			<table cellpadding='0' cellspacing='0' class='adminform'>
				<colgroup span='2'>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td class='label'>".PRFLAN_14." </td>
						<td class='control'>
							<input type='radio' class='radio' id='displaythemeinfo-1' name='displaythemeinfo' value='1'".($pref['displaythemeinfo'] ? " checked='checked'" : "")." /><label for='displaythemeinfo-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='displaythemeinfo-0' name='displaythemeinfo' value='0'".(! $pref['displaythemeinfo'] ? " checked='checked'" : "")." /><label for='displaythemeinfo-0'>".PRFLAN_113."</label>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_15." </td>
						<td class='control'>
							<input type='radio' class='radio' id='displayrendertime-1' name='displayrendertime' value='1'".($pref['displayrendertime'] ? " checked='checked'" : "")." /><label for='displayrendertime-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='displayrendertime-0' name='displayrendertime' value='0'".(! $pref['displayrendertime'] ? " checked='checked'" : "")." /><label for='displayrendertime-0'>".PRFLAN_113."</label>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_16." </td>
						<td class='control'>
							<input type='radio' class='radio' id='displaysql-1' name='displaysql' value='1'".($pref['displaysql'] ? " checked='checked'" : "")." /><label for='displaysql-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='displaysql-0' name='displaysql' value='0'".(! $pref['displaysql'] ? " checked='checked'" : "")." /><label for='displaysql-0'>".PRFLAN_113."</label>
						</td>
					</tr>
	";
if(function_exists("memory_get_usage"))
{
	$text .= "
					<tr>
						<td class='label'>".PRFLAN_137." </td>
						<td class='control'>
							<input type='radio' class='radio' id='display_memory_usage-1' name='display_memory_usage' value='1'".($pref['display_memory_usage'] ? " checked='checked'" : "")." /><label for='display_memory_usage-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='display_memory_usage-0' name='display_memory_usage' value='0'".(! $pref['display_memory_usage'] ? " checked='checked'" : "")." /><label for='display_memory_usage-0'>".PRFLAN_113."</label>
						</td>
					</tr>
	";
}
$text .= "
				</tbody>
			</table>
			".pref_submit('display')."
		</fieldset>
";

// Admin Display Areas
$text .= "
		<fieldset class='e-hideme' id='core-prefs-admindisp'>
			<legend class='e-hideme'>".PRFLAN_77."</legend>
			<table cellpadding='0' cellspacing='0' class='adminform'>
				<colgroup span='2'>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td class='label'>".PRFLAN_95."</td>
						<td class='control'>
							<input type='radio' class='radio' id='admin_alerts_ok-1' name='admin_alerts_ok' value='1'".($pref['admin_alerts_ok'] ? " checked='checked'" : "")." /><label for='admin_alerts_ok-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='admin_alerts_ok-0' name='admin_alerts_ok' value='0'".(! $pref['admin_alerts_ok'] ? " checked='checked'" : "")." /><label for='admin_alerts_ok-0'>".PRFLAN_113."</label>
							<div class='smalltext field-help'>".PRFLAN_96."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_97."</td>
						<td class='control'>
							<input type='radio' class='radio' id='admin_alerts_uniquemenu-1' name='admin_alerts_uniquemenu' value='1'".($pref['admin_alerts_uniquemenu'] ? " checked='checked'" : "")." /><label for='admin_alerts_uniquemenu-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='admin_alerts_uniquemenu-0' name='admin_alerts_uniquemenu' value='0'".(! $pref['admin_alerts_uniquemenu'] ? " checked='checked'" : "")." /><label for='admin_alerts_uniquemenu-0'>".PRFLAN_113."</label>
							<div class='smalltext field-help'>".PRFLAN_98."</div>
						</td>
					</tr>
				</tbody>
			</table>
			".pref_submit('admindisp')."
		</fieldset>

	";

// Date options.
$ga = new convert();
$date1 = $ga->convert_date(time(), "short");
$date2 = $ga->convert_date(time(), "long");
$date3 = $ga->convert_date(time(), "forum");

$text .= "
		<fieldset class='e-hideme' id='core-prefs-date'>
			<legend class='e-hideme'>".PRFLAN_21."</legend>
			<table cellpadding='0' cellspacing='0' class='adminform'>
				<colgroup span='2'>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td class='label'>".PRFLAN_22.": </td>
						<td class='control'>
							<input class='tbox input-text' type='text' name='shortdate' size='40' value='".$pref['shortdate']."' maxlength='50' />
							<br />".PRFLAN_83.": {$date1}
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_23.": </td>
						<td class='control'>
							<input class='tbox input-text' type='text' name='longdate' size='40' value='".$pref['longdate']."' maxlength='50' />
							<br />".PRFLAN_83.": {$date2}
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_24."</td>
						<td class='control'>
							<input class='tbox input-text' type='text' name='forumdate' size='40' value='".$pref['forumdate']."' maxlength='50' />
							<br />".PRFLAN_83.": {$date3}
							<div class='smalltext field-help'>".PRFLAN_25." <a href='http://www.php.net/manual/en/function.strftime.php' rel='external'>".PRFLAN_93."</a></div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_26."</td>
						<td class='control'>
							<select name='time_offset' class='tbox select time-offset'>";
$toffset = array("-12", "-11", "-10", "-9", "-8", "-7", "-6", "-5", "-4", "-3", "-2", "-1", "0", "+1", "+2", "+3", "+4", "+5", "+6", "+7", "+8", "+9", "+10", "+11", "+12", "+13", "+14", "+15", "+16");
if(! isset($pref['time_offset']))
{
	$pref['time_offset'] = "0";
}
foreach($toffset as $o)
{
	$text .= "
								<option".((! isset($pref['time_offset']) || $o == $pref['time_offset']) ? " selected='selected'" : "").">{$o}</option>
	";
}
$text .= "
							</select>
							<div class='smalltext field-help'>".PRFLAN_27."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_56.": </td>
						<td class='control'>
							<input class='tbox input-text' type='text' name='timezone' size='20' value='".$pref['timezone']."' maxlength='50' />
						</td>
					</tr>
				</tbody>
			</table>
			".pref_submit('date')."
		</fieldset>
";

// =========== Registration Preferences. ==================
$text .= "
		<fieldset class='e-hideme' id='core-prefs-registration'>
			<legend class='e-hideme'>".PRFLAN_28."</legend>
			<table cellpadding='0' cellspacing='0' class='adminform'>
				<colgroup span='2'>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td class='label'>".PRFLAN_29."</td>
						<td class='forumheader3'>
							<input type='radio' class='radio' id='user_reg-1' name='user_reg' value='1'".($pref['user_reg'] ? " checked='checked'" : "")." /><label for='user_reg-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='user_reg-0' name='user_reg' value='0'".(! $pref['user_reg'] ? " checked='checked'" : "")." /><label for='user_reg-0'>".PRFLAN_113."</label>
							<div class='smalltext field-help'>".PRFLAN_30."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_141."</td>
						<td class='forumheader3'>
							<input type='radio' class='radio' id='xup_enabled-1' name='xup_enabled' value='1'".($pref['xup_enabled'] ? " checked='checked'" : "")." /><label for='xup_enabled-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='xup_enabled-0' name='xup_enabled' value='0'".(! $pref['xup_enabled'] ? " checked='checked'" : "")." /><label for='xup_enabled-0'>".PRFLAN_113."</label>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_154."</td>
						<td class='forumheader3'>
							<select name='user_reg_veri' class='tbox select'>
";

$veri_list[0] = PRFLAN_152;
$veri_list[1] = PRFLAN_31;
$veri_list[2] = PRFLAN_153;

foreach($veri_list as $v => $v_title)
{
	$sel = ($pref['user_reg_veri'] == $v) ? "selected='selected'" : "";
	$text .= "
								<option value='$v' $sel>".$v_title."</option>
	";
}

$text .= "
							</select>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_160."</td>
						<td class='control'>
							<input type='radio' class='radio' id='signup_remote_emailcheck-1' name='signup_remote_emailcheck' value='1'".($pref['signup_remote_emailcheck'] ? " checked='checked'" : "")." /><label for='signup_remote_emailcheck-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='signup_remote_emailcheck-0' name='signup_remote_emailcheck' value='0'".(! $pref['signup_remote_emailcheck'] ? " checked='checked'" : "")." /><label for='signup_remote_emailcheck-0'>".PRFLAN_113."</label>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_167."</td>
						<td class='control'>
							<input type='radio' class='radio' id='disable_emailcheck-1' name='disable_emailcheck' value='1'".($pref['disable_emailcheck'] ? " checked='checked'" : "")." /><label for='disable_emailcheck-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='disable_emailcheck-0' name='disable_emailcheck' value='0'".(! $pref['disable_emailcheck'] ? " checked='checked'" : "")." /><label for='disable_emailcheck-0'>".PRFLAN_113."</label>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_32."</td>
						<td class='control'>
							<input type='radio' class='radio' id='anon_post-1' name='anon_post' value='1'".($pref['anon_post'] ? " checked='checked'" : "")." /><label for='anon_post-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='anon_post-0' name='anon_post' value='0'".(! $pref['anon_post'] ? " checked='checked'" : "")." /><label for='anon_post-0'>".PRFLAN_113."</label>
							<div class='smalltext field-help'>".PRFLAN_33."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_45."</td>
						<td class='control'>
							<input type='radio' class='radio' id='use_coppa-1' name='use_coppa' value='1'".($pref['use_coppa'] ? " checked='checked'" : "")." /><label for='use_coppa-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='use_coppa-0' name='use_coppa' value='0'".(! $pref['use_coppa'] ? " checked='checked'" : "")." /><label for='use_coppa-0'>".PRFLAN_113."</label>
							<div class='smalltext field-help'>".PRFLAN_46." <a href='http://www.cdt.org/legislation/105th/privacy/coppa.html'>".PRFLAN_94."</a></div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_58."</td>
						<td class='control'>
							<input type='radio' class='radio' id='membersonly_enabled-1' name='membersonly_enabled' value='1'".($pref['membersonly_enabled'] ? " checked='checked'" : "")." /><label for='membersonly_enabled-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='membersonly_enabled-0' name='membersonly_enabled' value='0'".(! $pref['membersonly_enabled'] ? " checked='checked'" : "")." /><label for='membersonly_enabled-0'>".PRFLAN_113."</label>
							<div class='smalltext field-help'>".PRFLAN_59."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".CUSTSIG_16."</td>
						<td class='control'>
							<input type='text' class='tbox input-text' size='3' name='signup_pass_len' value='".$pref['signup_pass_len']."' />
							<div class='smalltext field-help'>".PRFLAN_78."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_136."</td>
						<td class='control'>
							<input type='text' class='tbox input-text' size='3' name='signup_maxip' value='".$pref['signup_maxip']."' />
						</td>
					</tr>
					<tr>
						<td class='label'>".CUSTSIG_18."</td>
						<td class='control'>
							<textarea class='tbox textarea' name='signup_disallow_text' cols='1' rows='3'>".$pref['signup_disallow_text']."</textarea>
							<div class='smalltext field-help'>".CUSTSIG_19."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_155.":</td>
						<td class='control'>
							".r_userclass('displayname_class', $pref['displayname_class'], 'off', 'nobody,public,admin,classes')."
							<button class='submit' type='submit' name='submit_resetdisplaynames' value='".PRFLAN_156."'><span>".PRFLAN_156."</span></button>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_192.":</td>
						<td class='control'>
							<input type='text' class='tbox input-text' size='30' name='predefinedLoginName' value='".varset($pref['predefinedLoginName'], '')."' /><br />".PRFLAN_194."
							<div class='smalltext field-help'>".PRFLAN_193."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_158.":</td>
						<td class='control'>
							<input type='text' class='tbox input-text' size='3' name='displayname_maxlength' value='".varset($pref['displayname_maxlength'], 15)."' />
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_172.":</td>
						<td class='control'>
							<input type='text' class='tbox input-text' size='3' name='loginname_maxlength' value='".varset($pref['loginname_maxlength'], 30)."' />
						</td>
					</tr>
				</tbody>
			</table>
			".pref_submit('registration')."
		</fieldset>

	";

// Signup options ===========================.


$text .= "
		<fieldset class='e-hideme' id='core-prefs-signup'>
			<legend class='e-hideme'>".PRFLAN_19."</legend>
			<table cellpadding='0' cellspacing='0' class='adminform'>
				<colgroup span='2'>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td class='label'>".PRFLAN_126."</td>
						<td class='control'>
							<textarea class='tbox textarea' name='signup_text' cols='1' rows='3'>".$pref['signup_text']."</textarea>
						</td>
					</tr>

					<tr>
						<td class='label'>".PRFLAN_140."</td>
						<td class='control'>
							<textarea class='tbox textarea' name='signup_text_after' cols='1' rows='3'>".$pref['signup_text_after']."</textarea>
						</td>
					</tr>
					<!--
					<tr>
						<td class='label'>".CUSTSIG_13."</td>
						<td class='control'>".CUSTSIG_14."</td>
					</tr>
					-->
";

$signup_option_title = array(CUSTSIG_2, CUSTSIG_6, CUSTSIG_7, CUSTSIG_17, CUSTSIG_20);
$signup_option_names = array("signup_option_realname", "signup_option_signature", "signup_option_image", "signup_option_class", 'signup_option_customtitle');

foreach($signup_option_names as $key => $value)
{
	$text .= "
					<tr>
						<td class='label'>".$signup_option_title[$key]."</td>
						<td class='label'>
							<input type='radio' class='radio' id='{$value}-0' name='{$value}' value='0'".((! $pref[$value]) ? " checked='checked'" : "")." /><label for='{$value}-0'>".CUSTSIG_12."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='{$value}-1' name='{$value}' value='1'".(($pref[$value] == "1") ? " checked='checked'" : "")." /><label for='{$value}-1'>".CUSTSIG_14."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='{$value}-2' name='{$value}' value='2'".(($pref[$value] == "2") ? " checked='checked'" : "")." /><label for='{$value}-2'>".CUSTSIG_15."</label>&nbsp;&nbsp;
						</td>
					</tr>
	";
}

$text .= "
				</tbody>
			</table>
			".pref_submit('signup')."
		</fieldset>
";

// Custom Fields.


/* text render options */

if(! isset($pref['post_html']))
{
	$pref['post_html'] = '250';
	save_prefs();
}

$text .= "
		<fieldset class='e-hideme' id='core-prefs-textpost'>
			<legend class='e-hideme'>".PRFLAN_101."</legend>
			<table cellpadding='0' cellspacing='0' class='adminform'>
				<colgroup span='2'>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td class='label'>".PRFLAN_127.":</td>
						<td class='control'>
							<input type='radio' class='radio' id='make_clickable-1' name='make_clickable' value='1'".($pref['make_clickable'] ? " checked='checked'" : "")." /><label for='make_clickable-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='make_clickable-0' name='make_clickable' value='0'".(! $pref['make_clickable'] ? " checked='checked'" : "")." /><label for='make_clickable-0'>".PRFLAN_113."</label>
							<div class='smalltext field-help'>".PRFLAN_128."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_102."?:</td>
						<td class='control'>
							<input type='radio' class='radio' id='link_replace-1' name='link_replace' value='1'".($pref['link_replace'] ? " checked='checked'" : "")." /><label for='link_replace-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='link_replace-0' name='link_replace' value='0'".(! $pref['link_replace'] ? " checked='checked'" : "")." /><label for='link_replace-0'>".PRFLAN_113."</label>
							<div class='smalltext field-help'>".PRFLAN_103."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_145."?:</td>
						<td class='control'>
							<input type='radio' class='radio' id='links_new_window-1' name='links_new_window' value='1'".($pref['links_new_window'] ? " checked='checked'" : "")." /><label for='links_new_window-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='links_new_window-0' name='links_new_window' value='0'".(! $pref['links_new_window'] ? " checked='checked'" : "")." /><label for='links_new_window-0'>".PRFLAN_113."</label>
							<div class='smalltext field-help'>".PRFLAN_146."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_104.":</td>
						<td class='control'>
							<input class='tbox input-text' type='text' name='link_text' size='50' value='".$tp->post_toForm($pref['link_text'])."' maxlength='200' />
							<div class='smalltext field-help'>".PRFLAN_105."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_107.":</td>
						<td class='control'>
							<input class='tbox input-text' type='text' name='email_text' size='50' value='".$tp->post_toForm($pref['email_text'])."' maxlength='200' />
							<div class='smalltext field-help'>".PRFLAN_108."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_109.":</td>
						<td class='control'>
							<input class='tbox input-text' type='text' name='main_wordwrap' size='5' value='".$pref['main_wordwrap']."' maxlength='3' />
							<div class='smalltext field-help'>".PRFLAN_110."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_111.":</td>
						<td class='control'>
							<input class='tbox input-text' type='text' name='menu_wordwrap' size='5' value='".$pref['menu_wordwrap']."' maxlength='3' />
							<div class='smalltext field-help'>".PRFLAN_110."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_116.":</td>
						<td class='control'>
							".r_userclass('post_html', $pref['post_html'], 'off', 'nobody,public,member,admin,main,classes')."
							<div class='smalltext field-help'>".PRFLAN_117."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_122.":</td>
						<td class='control'>
							<input type='radio' class='radio' id='wysiwyg-1' name='wysiwyg' value='1'".($pref['wysiwyg'] ? " checked='checked'" : "")." /><label for='wysiwyg-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='wysiwyg-0' name='wysiwyg' value='0'".(! $pref['wysiwyg'] ? " checked='checked'" : "")." /><label for='wysiwyg-0'>".PRFLAN_113."</label>
							<div class='smalltext field-help'>".PRFLAN_123."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_124.":</td>
						<td class='control'>
							<input type='radio' class='radio' id='old_np-1' name='old_np' value='1'".($pref['old_np'] ? " checked='checked'" : "")." /><label for='old_np-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='old_np-0' name='old_np' value='0'".(! $pref['old_np'] ? " checked='checked'" : "")." /><label for='old_np-0'>".PRFLAN_113."</label>
							<div class='smalltext field-help'>".PRFLAN_125."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_131.":</td>
						<td class='control'>
							".r_userclass('php_bbcode', $pref['php_bbcode'], 'off', 'nobody,admin,main,classes')."
							<div class='smalltext field-help'>".PRFLAN_132."</div>
						</td>
					</tr>
";

if(file_exists(e_PLUGIN."geshi/geshi.php"))
{
	$text .= "
					<tr>
						<td class='label'>".PRFLAN_118."?:</div></td>
						<td class='control'>
							<input type='radio' class='radio' id='useGeshi-1' name='useGeshi' value='1'".($pref['useGeshi'] ? " checked='checked'" : "")." /><label for='useGeshi-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='useGeshi-0' name='useGeshi' value='0'".(! $pref['useGeshi'] ? " checked='checked'" : "")." /><label for='useGeshi-0'>".PRFLAN_113."</label>
							<div class='smalltext field-help'>".PRFLAN_119."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_120."?:</td>
						<td class='control'>
							<input class='tbox input-text' type='text' name='defaultLanGeshi' size='10' value='".($pref['defaultLanGeshi'] ? $pref['defaultLanGeshi'] : "php")."' maxlength='20' />
							<div class='smalltext field-help'>".PRFLAN_121."</div>
						</td>
					</tr>
	";
}
$text .= "
				</tbody>
			</table>
			".pref_submit('textpost')."
		</fieldset>
";

function multi_radio($name, $textsVals, $currentval = '')
{
	$ret = '';
	$gap = '';
	foreach($textsVals as $v => $t)
	{
		$sel = ($v == $currentval) ? " checked='checked'" : "";
		$ret .= $gap."<input type='radio' name='{$name}' value='{$v}'{$sel} /> ".$t."";
		$gap = "&nbsp;&nbsp;";
	}
	return $ret;
}

// Security Options. .
$hasGD = extension_loaded("gd");

$text .= "
		<fieldset class='e-hideme' id='core-prefs-security'>
			<legend class='e-hideme'>".PRFLAN_47."</legend>
			<table cellpadding='0' cellspacing='0' class='adminform'>
				<colgroup span='2'>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td class='label'>".PRFLAN_60."</td>
						<td class='control'>".multi_radio('ssl_enabled', array('1' => PRFLAN_112, '0' => PRFLAN_113), $pref['ssl_enabled'])."
							<div class='smalltext field-help'>".PRFLAN_61."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_76.": </td>
						<td class='control'>
";

if($hasGD)
{
	$text .= multi_radio('signcode', array('1' => PRFLAN_112, '0' => PRFLAN_113), $pref['signcode']);
}
else
{
	$text .= PRFLAN_133;
}
$text .= "
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_81.": </td>
						<td class='control'>
";

if($hasGD)
{
	$text .= multi_radio('logcode', array('1' => PRFLAN_112, '0' => PRFLAN_113), $pref['logcode']);
}
else
{
	$text .= PRFLAN_133;
}
$text .= "
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_138.": </td>
						<td class='control'>
";
if($hasGD)
{
	$text .= multi_radio('fpwcode', array('1' => PRFLAN_112, '0' => PRFLAN_113), $pref['fpwcode']);
}
else
{
	$text .= PRFLAN_133;
}

$text .= "
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_92.": </td>
						<td class='control'>".multi_radio('user_reg_secureveri', array('1' => PRFLAN_112, '0' => PRFLAN_113), $pref['user_reg_secureveri'])."
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_129.":</td>
						<td class='control'>".multi_radio('disallowMultiLogin', array('1' => PRFLAN_112, '0' => PRFLAN_113), $pref['disallowMultiLogin'])."
							<div class='smalltext field-help'>".PRFLAN_130."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_184.":</td>
						<td class='control'>".multi_radio('allowEmailLogin', array('1' => PRFLAN_186, '0' => PRFLAN_187), varset($pref['allowEmailLogin'], 0))."
							<div class='smalltext field-help'>".PRFLAN_185."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_48.":</td>
						<td class='control'>".multi_radio('user_tracking', array('cookie' => PRFLAN_49, 'session' => PRFLAN_50), $pref['user_tracking'])."
							<br />
							".PRFLAN_55.": <input class='tbox input-text' type='text' name='cookie_name' size='20' value='".$pref['cookie_name']."' maxlength='20' />
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_188.":</td>
						<td class='control'>".multi_radio('passwordEncoding', array('0' => PRFLAN_189, '1' => PRFLAN_190), varset($pref['passwordEncoding'], 0))."
							<div class='smalltext field-help'>".PRFLAN_191."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_178."</td>
						<td class='control'>
							<select name='password_CHAP' class='tbox select'>
";
$CHAP_list[0] = PRFLAN_180;
$CHAP_list[1] = PRFLAN_181;
$CHAP_list[2] = PRFLAN_182;

foreach($CHAP_list as $ab => $ab_title)
{
	$sel = ($pref['password_CHAP'] == $ab) ? "selected='selected'" : "";
	$text .= "
								<option value='$ab' $sel>".$ab_title."</option>
	";
}

$text .= "
							</select>
							<div class='smalltext field-help'>".PRFLAN_183."<br />".PRFLAN_179."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_40."</td>
						<td class='control'>".multi_radio('profanity_filter', array('1' => PRFLAN_112, '0' => PRFLAN_113), $pref['profanity_filter'])."
							<div class='smalltext field-help'>".PRFLAN_41."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_42.":</td>
						<td class='control'>
							<input class='tbox input-text' type='text' name='profanity_replace' size='30' value='".$pref['profanity_replace']."' maxlength='20' />
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_43.":</td>
						<td class='control'>
							<textarea class='tbox textarea' name='profanity_words' cols='59' rows='2'>".$pref['profanity_words']."</textarea>
							<br />".PRFLAN_44."
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_35.":</td>
						<td class='control'>".multi_radio('antiflood1', array('1' => PRFLAN_112, '0' => PRFLAN_113), $pref['antiflood1'])."
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_36.":</td>
						<td class='control'>
							<input class='tbox input-text' type='text' name='antiflood_timeout' size='3' value='".$pref['antiflood_timeout']."' maxlength='3' />
							<div class='smalltext field-help'>".PRFLAN_38."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_37."</td>
						<td class='control'>
							<select name='autoban' class='tbox select'>
";

$autoban_list[0] = PRFLAN_113;
$autoban_list[1] = PRFLAN_144;
$autoban_list[2] = PRFLAN_142;
$autoban_list[3] = PRFLAN_143;

foreach($autoban_list as $ab => $ab_title)
{
	$sel = ($pref['autoban'] == $ab) ? "selected='selected'" : "";
	$text .= "
								<option value='$ab' $sel>".$ab_title."</option>
	";
}

$text .= "
							</select>
							<div class='smalltext field-help'>".PRFLAN_91."</div>
						</td>
					</tr>
					<tr>
						<td class='forumheader3'>".PRFLAN_139.":</td>
						<td class='control'>".multi_radio('adminpwordchange', array('1' => PRFLAN_112, '0' => PRFLAN_113), $pref['adminpwordchange'])."
						</td>
					</tr>
				</tbody>
			</table>
			".pref_submit('security')."
		</fieldset>
";

$text .= "
		<fieldset class='e-hideme' id='core-prefs-comments'>
			<legend class='e-hideme'>".PRFLAN_87."</legend>
			<table cellpadding='0' cellspacing='0' class='adminform'>
				<colgroup span='2'>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td class='label'>".PRFLAN_89.": </td>
						<td class='control'>
							<input type='radio' class='radio' id='comments_icon-1' name='comments_icon' value='1'".($pref['comments_icon'] ? " checked='checked'" : "")." /><label for='comments_icon-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='comments_icon-0' name='comments_icon' value='0'".(! $pref['comments_icon'] ? " checked='checked'" : "")." /><label for='comments_icon-0'>".PRFLAN_113."</label>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_88.": </td>
						<td class='control'>
							<input type='radio' class='radio' id='nested_comments-1' name='nested_comments' value='1'".($pref['nested_comments'] ? " checked='checked'" : "")." /><label for='nested_comments-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='nested_comments-0' name='nested_comments' value='0'".(! $pref['nested_comments'] ? " checked='checked'" : "")." /><label for='nested_comments-0'>".PRFLAN_113."</label>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_90.": </td>
						<td class='control'>
							<input type='radio' class='radio' id='allowCommentEdit-1' name='allowCommentEdit' value='1'".($pref['allowCommentEdit'] ? " checked='checked'" : "")." /><label for='allowCommentEdit-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='allowCommentEdit-0' name='allowCommentEdit' value='0'".(! $pref['allowCommentEdit'] ? " checked='checked'" : "")." /><label for='allowCommentEdit-0'>".PRFLAN_113."</label>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_161.": </td>
						<td class='control'>
							<input type='radio' class='radio' id='comments_disabled-1' name='comments_disabled' value='1'".($pref['comments_disabled'] ? " checked='checked'" : "")." /><label for='comments_disabled-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='comments_disabled-0' name='comments_disabled' value='0'".(! $pref['comments_disabled'] ? " checked='checked'" : "")." /><label for='comments_disabled-0'>".PRFLAN_113."</label>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_166.": </td>
						<td class='control'>
							<input type='radio' class='radio' id='comments_emoticons-1' name='comments_emoticons' value='1'".($pref['comments_emoticons'] ? " checked='checked'" : "")." /><label for='comments_emoticons-1'>".PRFLAN_112."</label>&nbsp;&nbsp;
							<input type='radio' class='radio' id='comments_emoticons-0' name='comments_emoticons' value='0'".(! $pref['comments_emoticons'] ? " checked='checked'" : "")." /><label for='comments_emoticons-0'>".PRFLAN_113."</label>
						</td>
					</tr>
				</tbody>
			</table>
			".pref_submit('comments')."
		</fieldset>
	";

//Advanced Features
$text .= "
		<fieldset class='e-hideme' id='core-prefs-advanced'>
			<legend class='e-hideme'>".PRFLAN_149."</legend>
			<table cellpadding='0' cellspacing='0' class='adminform'>
				<colgroup span='2'>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td class='label'>".PRFLAN_147.":</td>
						<td class='control'>".multi_radio('developer', array('1' => PRFLAN_112, '0' => PRFLAN_113), $pref['developer'])."
							<div class='smalltext field-help'>".PRFLAN_148."</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_196."</td>
						<td class='control'>".multi_radio('log_page_accesses', array('1' => PRFLAN_112, '0' => PRFLAN_113), $pref['log_page_accesses'])."
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_17."</td>
						<td class='control'>".multi_radio('compress_output', array('1' => PRFLAN_112, '0' => PRFLAN_113), $pref['compress_output'])."
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_150."</td>
						<td class='control'>
							{$auth_dropdown}
						</td>
					</tr>
					<tr>
						<td class='label'>".PRFLAN_173."</td>
						<td class='control'>".multi_radio('check_updates', array('1' => PRFLAN_112, '0' => PRFLAN_113), $pref['check_updates'])."
						</td>
					</tr>
				</tbody>
			</table>
			".pref_submit('advanced')."
		</fieldset>
	";

// END Advanced Features


$text .= "
	</form>
</div>
";

$e107->ns->tablerender(PRFLAN_53, $emessage->render().$text);

require_once(e_ADMIN."footer.php");

function pref_submit($post_id = '')
{
	global $frm;
	if($post_id) $post_id = '-'.$post_id;
	$text = "
		<div class='buttons-bar center'>";

	// ML
	/* if(e_MLANG == 1){
	//$text .="<input class='fcaption' type='submit' name='updateprefs' value='".PRFLAN_52."' />
	$but_typ = array(""); // empty = submit
	$but_nam = array("updateprefs"); // empty = autobutX with X autoincrement
	$but_val = array("updateprefs"); // empty = Submit
	$but_class = array("caption"); // empty = button
	$butjs = array(""); // empty = ""
	$buttitle = array(""); // empty = ""
	$text .= e107ml_adpanel(1,$but_typ,$but_nam,$but_val,$but_class,$butjs,$buttitle);
	}else{*/
	$text .= $frm->admin_button('updateprefs', PRFLAN_52, 'update', '', "id=updateprefs{$post_id}");
	// }
	$text .= "\n</div>";

	// END ML
	return $text;
}

function prefs_adminmenu()
{
	$var['core-prefs-main']['text'] = PRFLAN_1;
	$var['core-prefs-display']['text'] = PRFLAN_13;
	$var['core-prefs-admindisp']['text'] = PRFLAN_77;
	$var['core-prefs-date']['text'] = PRFLAN_21;
	$var['core-prefs-registration']['text'] = PRFLAN_28;
	$var['core-prefs-signup']['text'] = PRFLAN_19;
	$var['core-prefs-textpost']['text'] = PRFLAN_101;
	$var['core-prefs-security']['text'] = PRFLAN_47;
	$var['core-prefs-comments']['text'] = PRFLAN_87;
	$var['core-prefs-advanced']['text'] = PRFLAN_149;
	show_admin_menu(LAN_OPTIONS, '', $var, TRUE);
}
?>