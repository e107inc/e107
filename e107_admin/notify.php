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
|     $Source: /cvs_backup/e107_0.7/e107_admin/notify.php,v $
|     $Revision: 1.8 $
|     $Date: 2005-08-22 20:31:28 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once('../class2.php');
if (!getperms('O')) {
	header('location:'.e_BASE.'index.php');
	exit;
}

$e_sub_cat = 'notify';

require_once('auth.php');
require_once(e_HANDLER.'userclass_class.php');
require_once(e_HANDLER.'form_handler.php');
$rs = new form;
$nc = new notify_config;
if (isset($_POST['update'])) {
	$nc -> update();
}
$nc -> config();

class notify_config {
	
	var $notify_prefs;
	
	function notify_config() {
		global $sysprefs, $eArrayStorage, $tp, $sql;
		$this -> notify_prefs = $sysprefs -> get('notify_prefs');
		$this -> notify_prefs = $eArrayStorage -> ReadArray($this -> notify_prefs);
		
		$handle = opendir(e_PLUGIN);
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && is_dir(e_PLUGIN.$file)) {
				$plugin_handle = opendir(e_PLUGIN.$file."/");
				while (false !== ($file2 = readdir($plugin_handle))) {
					if ($file2 == "e_notify.php") {
						if ($sql -> db_Select("plugin", "plugin_path", "plugin_path='".$file."' AND plugin_installflag='1'")) {
							if (!isset($this -> notify_prefs['plugins'][$file])) {
								$this -> notify_prefs['plugins'][$file] = TRUE;
								require_once(e_PLUGIN.$file.'/e_notify.php');
								foreach ($config_events as $event_id => $event_text) {
									$this -> notify_prefs['event'][$event_id] = array('type' => 'off', 'class' => '254', 'email' => '');
								}
								$recalibrate = true;
							}
						}
					}
				}
			}
		}
		
		if ($recalibrate) {
			$s_prefs = $tp -> recurse_toDB($this -> notify_prefs, true);
			$s_prefs = $eArrayStorage -> WriteArray($s_prefs);
			$sql -> db_Update("core", "e107_value='".$s_prefs."' WHERE e107_name='notify_prefs'");
		}
	}
	
	function config() {
		global $ns, $rs;

		$text = "<div style='text-align: center'>
		<form action='".e_SELF."?results' method='post' id='scanform'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td class='fcaption' colspan='2'>".NT_LAN_2.":</td>
		</tr>";
		
		$text .= "<tr>
		<td colspan='2' class='forumheader'>".NU_LAN_1."</td>
		</tr>";
		
		$text .= $this -> render_event('usersup', NU_LAN_2);		
		$text .= $this -> render_event('userveri', NU_LAN_3);
		$text .= $this -> render_event('login', NU_LAN_4);
		$text .= $this -> render_event('logout', NU_LAN_5);
		
		$text .= "<tr>
		<td colspan='2' class='forumheader'>".NS_LAN_1."</td>
		</tr>";
		
		$text .= $this -> render_event('flood', NS_LAN_2);

		
		$text .= "<tr>
		<td colspan='2' class='forumheader'>".NN_LAN_1."</td>
		</tr>";
		
		$text .= $this -> render_event('subnews', NN_LAN_2);
		$text .= $this -> render_event('newspost', NN_LAN_3);
		$text .= $this -> render_event('newsupd', NN_LAN_4);
		$text .= $this -> render_event('newsdel', NN_LAN_5);
		
		foreach ($this -> notify_prefs['plugins'] as $plugin_id => $plugin_settings) {
			require(e_PLUGIN.$plugin_id.'/e_notify.php');
			$text .= "<tr>
			<td colspan='2' class='forumheader'>".$config_category."</td>
			</tr>";
			foreach ($config_events as $event_id => $event_text) {
				$text .= $this -> render_event($event_id, $event_text);
			}
		}

		$text .= "<tr>
		<td colspan='2' style='text-align:center' class='forumheader'>".$rs -> form_button('submit', 'update', LAN_UPDATE)."</td>
		</tr>
		</table>
		</form>
		</div>";

		$ns -> tablerender(NT_LAN_1, $text);
	}
	
	function render_event($id, $description) {
		global $rs, $tp;
		$text .= "<tr>
		<td class='forumheader3' style='width: 30%'>
		".$description.":
		</td>
		<td class='forumheader3' style='width: 70%; white-space: nowrap'>
		<input type='radio' name='event[".$id."][type]' value='off' ".(($this -> notify_prefs['event'][$id]['type'] == 'off' || !$this -> notify_prefs['event'][$id]['type']) ? " checked='checked'" : "")." /> ".NT_LAN_3." 
		<input type='radio' name='event[".$id."][type]' value='main' ".($this -> notify_prefs['event'][$id]['type'] == 'main' ? " checked='checked'" : "")." /> ".NT_LAN_4." 
		<input type='radio' name='event[".$id."][type]' value='class' ".($this -> notify_prefs['event'][$id]['type'] == 'class' ? " checked='checked'" : "")." /> ".NT_LAN_5.": 
		".r_userclass('event['.$id.'][class]', $this -> notify_prefs['event'][$id]['class'], 'off', 'member,admin,classes')." 
		<input type='radio' name='event[".$id."][type]' value='email' ".($this -> notify_prefs['event'][$id]['type'] == 'email' ? " checked='checked'" : "")." /> ".NT_LAN_6.": 
		".$rs -> form_text('event['.$id.'][email]', 17, $tp -> toForm($this -> notify_prefs['event'][$id]['email']))."
		</td>
		</tr>";
		return $text;
	}
	
	function update() {
		global $sql, $pref, $tp, $eArrayStorage;
		foreach ($_POST['event'] as $key => $value) {
			if ($this -> update_event($key)) {
				$active = TRUE;
			}
		}
		
		$s_prefs = $tp -> recurse_toDB($this -> notify_prefs, true);
		$s_prefs = $eArrayStorage -> WriteArray($s_prefs);
		$sql -> db_Update("core", "e107_value='".$s_prefs."' WHERE e107_name='notify_prefs'");
		if ($active) {
			$pref['notify'] = TRUE;
		} else {
			$pref['notify'] = FALSE;
		}
		save_prefs();
	}
	
	function update_event($id) {
		$this -> notify_prefs['event'][$id]['type'] = $_POST['event'][$id]['type'];
		$this -> notify_prefs['event'][$id]['class'] = $_POST['event'][$id]['class'];
		$this -> notify_prefs['event'][$id]['email'] = $_POST['event'][$id]['email'];
		if ($this -> notify_prefs['event'][$id]['type'] != 'off') {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

require_once('footer.php');

?>