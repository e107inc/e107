<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/chatbox_menu/admin_chatbox.php,v $
 * $Revision: 1.9 $
 * $Date: 2009-11-18 01:05:23 $
 * $Author: e107coders $
 */

require_once("../../class2.php");

if (!plugInstalled('chatbox_menu') || !getperms("P")) 
{
	header("Location: ".e_BASE."index.php");
	exit;
}

include_lan( e_PLUGIN."chatbox_menu/languages/".e_LANGUAGE."/admin_chatbox_menu.php");

require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."userclass_class.php");

if (isset($_POST['updatesettings'])) 
{
	$temp = array();
	$temp['chatbox_posts'] = min(intval($_POST['chatbox_posts']), 5);
	$temp['cb_layer'] = intval($_POST['cb_layer']);
	$temp['cb_layer_height'] = max(varset($_POST['cb_layer_height'], 200), 150);
	$temp['cb_emote'] = intval($_POST['cb_emote']);
	$temp['cb_mod'] = intval($_POST['cb_mod']);
	if ($admin_log->logArrayDiffs($temp, $pref, 'CHBLAN_01'))
	{
		save_prefs();		// Only save if changes
		$e107cache->clear("nq_chatbox");
		$message = CHBLAN_1;
	}
	else
	{
		$message = CHBLAN_39;
	}
}


if (isset($_POST['prune'])) 
{
	$chatbox_prune = intval($_POST['chatbox_prune']);
	$prunetime = time() - $chatbox_prune;

	$sql->db_Delete("chatbox", "cb_datestamp < '{$prunetime}' ");
	$admin_log->log_event('CHBLAN_02', $chatbox_prune.', '.$prunetime, E_LOG_INFORMATIVE, '');
	$e107cache->clear("nq_chatbox");
	$message = CHBLAN_28;
}

if (isset($_POST['recalculate'])) 
{
	$sql->db_Update("user", "user_chats = 0");
	$qry = "SELECT u.user_id AS uid, count(c.cb_nick) AS count FROM #chatbox AS c
		LEFT JOIN #user AS u ON SUBSTRING_INDEX(c.cb_nick,'.',1) = u.user_id
		WHERE u.user_id > 0
		GROUP BY uid";

		if ($sql -> db_Select_gen($qry)) 
		{
			$ret = array();
			while($row = $sql -> db_Fetch())
			{
				$list[$row['uid']] = $row['count'];
			}
		}

		foreach($list as $uid => $cnt)
		{
			$sql->db_Update("user", "user_chats = '{$cnt}' WHERE user_id = '{$uid}'");
		}
	$admin_log->log_event('CHBLAN_03','', E_LOG_INFORMATIVE, '');
	$message = CHBLAN_33;
}

if (isset($message)) 
{
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$chatbox_posts = $pref['chatbox_posts'];

$text = "<div>
	<form method='post' action='".e_SELF."' id='cbform'>
    <table cellpadding='0' cellspacing='0' class='adminform'>
    	<colgroup span='2'>
    		<col class='col-label' />
    		<col class='col-control' />
    	</colgroup>
	<tr>
	<td>".CHBLAN_11.":  <div class='smalltext'>".CHBLAN_12."</div></td>
	<td>
	<select name='chatbox_posts' class='tbox'>";
if ($chatbox_posts == 5) {
	$text .= "<option selected='selected'>5</option>\n";
} else {
	$text .= "<option>5</option>\n";
}
if ($chatbox_posts == 10) {
	$text .= "<option selected='selected'>10</option>\n";
} else {
	$text .= "<option>10</option>\n";
}
if ($chatbox_posts == 15) {
	$text .= "<option selected='selected'>15</option>\n";
} else {
	$text .= "<option>15</option>\n";
}
if ($chatbox_posts == 20) {
	$text .= "<option selected='selected'>20</option>\n";
} else {
	$text .= "<option>20</option>\n";
}
if ($chatbox_posts == 25) {
	$text .= "<option selected='selected'>25</option>\n";
} else {
	$text .= "<option>25</option>\n";
}

if(!isset($pref['cb_mod']))
{
	$pref['cb_mod'] = e_UC_ADMIN;
}

$text .= "</select>
	</td>
	</tr>

	<tr><td>".CHBLAN_32.": </td>
	<td>". r_userclass("cb_mod", $pref['cb_mod'], 'off', "admin, classes")."
	</td>
	</tr>

	<tr><td>".CHBLAN_36."</td>
	<td>".
	($pref['cb_layer'] == 0 ? "<input type='radio' name='cb_layer' value='0' checked='checked' />" : "<input type='radio' name='cb_layer' value='0' />")."&nbsp;&nbsp;". CHBLAN_37."<br />".
	($pref['cb_layer'] == 1 ? "<input type='radio' name='cb_layer' value='1' checked='checked' />" : "<input type='radio' name='cb_layer' value='1' />")."&nbsp;".CHBLAN_29."&nbsp;--&nbsp;". CHBLAN_30.": <input class='tbox' type='text' name='cb_layer_height' size='8' value='".$pref['cb_layer_height']."' maxlength='3' /><br />".
	($pref['cb_layer'] == 2 ? "<input type='radio' name='cb_layer' value='2' checked='checked' />" : "<input type='radio' name='cb_layer' value='2' />")."&nbsp;&nbsp;". CHBLAN_38."
	</td>
	</tr>
	";

	if($pref['smiley_activate'])
	{
		$text .= "<tr><td>".CHBLAN_31."?: </td>
		<td>". ($pref['cb_emote'] ? "<input type='checkbox' name='cb_emote' value='1' checked='checked' />" : "<input type='checkbox' name='cb_emote' value='1' />")."
		</td>
		</tr>
		";
	}

	$text .= "<tr>
	<td>".CHBLAN_21.": <div class='smalltext'>".CHBLAN_22."</div></td>
	<td>
	".CHBLAN_23." <select name='chatbox_prune' class='tbox'>
	<option></option>
	<option value='86400'>".CHBLAN_24."</option>
	<option value='604800'>".CHBLAN_25."</option>
	<option value='2592000'>".CHBLAN_26."</option>
	<option value='1'>".CHBLAN_27."</option>
	</select>
	<input class='button' type='submit' name='prune' value='".CHBLAN_21."' />
	</td>
	</tr>";


	$text .= "<tr>
	<td>".CHBLAN_34.":</td>
	<td>
	<input class='button' type='submit' name='recalculate' value='".CHBLAN_35."' />
	</td>
	</tr>
	</table>";

	$text .= "<div class='buttons-bar center'>
	<input class='button' type='submit' name='updatesettings' value='".CHBLAN_19."' />
	</div>
	</form>
	</div>";

$ns->tablerender(CHBLAN_20, $text);

require_once(e_ADMIN."footer.php");
?>