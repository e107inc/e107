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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/pm_menu/pm_inc.php,v $
|     $Revision: 1.8 $
|     $Date: 2005-03-18 01:57:01 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
define("e_PM", e_PLUGIN."pm_menu/");
@include_once(e_PLUGIN."pm_menu/languages/".e_LANGUAGE.".php");
@include_once(e_PLUGIN."pm_menu/languages/English.php");
$pm_stat_store = array();
$pm_stored_stat_user = -1; // cache at least for this page!
$pm_stored_stat_time = -1;

function pm_show_icon($to_id, $pm_icon = "") {
	global $pref;
	if (!$pref['pm_title']) {
		return "";
	}
	if (!$pm_icon) {
		$pm_icon = e_IMAGE."forum/pm.png";
	}
	$sql = new db;
	if (check_class($pref['pm_userclass'], USERCLASS)) {
		if ($sql->db_Select("user", "user_class", "user_id={$to_id}")) {
			$row = $sql->db_Fetch();
			if (check_class($pref['pm_userclass'], $row['user_class'])) {
				return "<a href='".e_PLUGIN."pm_menu/pm.php?send.{$to_id}'><img src='".$pm_icon."' title='".PMLAN_PM."' alt='' style='border:0' /></a>";
			}
		}
	}
	return "";
}

function pm_get_stats($user = USERNAME, $time = USERLV) {
	global $pm_stat_store, $pm_stored_stat_user, $pm_stored_stat_time, $sql;
	if ($user == $pm_stored_stat_user && $time == $pm_stored_stat_time) {
		return $pm_stat_store;
	}
	$ret['new'] = $sql->db_Count("pm_messages", "(*)", "WHERE pm_to_user = '{$user}' AND pm_sent_datestamp > {$time} AND pm_rcv_datestamp = 0");
	$ret['received'] = $sql->db_Count("pm_messages", "(*)", "WHERE pm_to_user = '{$user}' ");
	$ret['unread_rcv_pm'] = $sql->db_Count("pm_messages", "(*)", "WHERE pm_to_user = '{$user}' AND pm_rcv_datestamp = 0");
	$ret['sent_pm'] = $sql->db_Count("pm_messages", "(*)", "WHERE pm_from_user = '{$user}' ");
	$ret['unread_send_pm'] = $sql->db_Count("pm_messages", "(*)", "WHERE pm_from_user = '{$user}' AND pm_rcv_datestamp = 0");
	$ret['blocks'] = $sql->db_Count("pm_blocks", "(*)", "WHERE block_to = '{$user}' ");
	$pm_stat_store = $ret;
	$pm_stored_stat_user = $user;
	$pm_stored_stat_time = $time;
	return $ret;
}

function pm_show_stats($no_show_br = 0) {
	global $ns;
	global $pref;
	$pmstats = pm_get_stats();
	if (USER == TRUE || ADMIN == TRUE) {
		$time = USERLV;
		$text = (!$no_show_br) ? "<br /><br />" : "";
		if ($pmstats['unread_rcv_pm'] > 0) {
			if ($pref['pm_show_animated']) {
				$newpm_image = (file_exists(THEME."images/newpm.gif") ? THEME."images/newpm.gif" : e_PM."images/newpm.gif");
				$text .= "<a href='".e_PM."pm.php?read'><img src='".$newpm_image."' style='border:0' alt='' /></a><br />";
			}
			if ($pref['pm_popup'] && !preg_match("/pm\.php/", e_SELF) && $_COOKIE["pm-alert"] != "ON") {
				$alertdelay = $pref['pm_popdelay'];
				setcookie("pm-alert", "ON", time()+$alertdelay);
				$popuptext = "<html><head ><title>".$pmstats['new']." ".PMLAN_0."</title><link rel=stylesheet href=" . THEME . "style.css></head><body style=padding-left:2px;padding-right:2px;padding:2px;padding-bottom:2px;margin:0px;align;center marginheight=0 marginleft=0 topmargin=0 leftmargin=0><table width=100% align=center style=width:100%;height:99%padding-bottom:2px class=bodytable height=99% ><tr><td width=100% ><center><b>--- ".PMLAN_PM." ---</b><br />".$pmstats['new']." ".PMLAN_0."<br />".$pmstats['unread_rcv_pm']." ".PMLAN_45."<br><br /><form><input class=button type=submit onclick=\\\\\"self.close()\\\\\" value = \\\\\"ok\\\\\" >< /form >< /center >< /td >< /tr >< /table >< /body >< /html > ";
				$text.="
				<script type='text/javascript'>
				winl=(screen.width-200)/2;
				wint = (screen.height-100)/2;
				winProp = 'width=200,height=100,left='+winl+',top='+wint+',scrollbars=no';
				window.open('javascript:document.write(\"".$popuptext."\");', \"pm_popup\", winProp);
				< /script > ";
			}
		}
		if ($pmstats['new']){$text.=$pmstats['new']." ".PMLAN_0." <br /> \n";}
		$text .= ($pmstats['received'] > 0) ? "<a class='smalltext' href='".e_PM."pm.php?read'>{$pmstats['received']} ".PMLAN_1."</a> ({$pmstats['unread_rcv_pm']})<br />" : $pmstats['received']." ".PMLAN_1."<br />";
		$blocks = $pmstats['blocks'];
		$text .= ($pmstats['sent_pm']>0) ? "<a class='smalltext' href='".e_PM."pm.php?sent'>{$pmstats['sent_pm']} ".PMLAN_2."</a> ({$pmstats['unread_send_pm']})<br />" : $pmstats['sent_pm']." ".PMLAN_2."<br />";
		$text .= ($pmstats['blocks'] == 1) ? $pmstats['blocks']." ".PMLAN_3 : $pmstats['blocks']." ".PMLAN_4;
		if ($pmstats['blocks']>0){$text.=" - <a class = 'smalltext' href = '".e_PM."pm.php?vb' > ".PMLAN_6." </a> ";}
		$text.=" <br /> \n";
		$text.="[ <a href = '".e_PM."pm.php?send'> ".PMLAN_5." </a> ]";
		$text.=" <br /> \n";
		return " <span class = 'smalltext' > ".$text." </span> ";
	}
}
?>