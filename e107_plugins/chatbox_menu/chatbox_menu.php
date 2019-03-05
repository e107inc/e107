<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 chatbox_menu Plugin
 *
*/

if (isset($_POST['chatbox_ajax'])) {
	define('e_MINIMAL', true);
	if ( ! defined('e107_INIT')) {
		require_once('../../class2.php');
	}
}

global $e107cache, $e_event, $e107;

$tp = e107::getParser();
$pref = e107::getPref();

if ( ! e107::isInstalled('chatbox_menu')) {
	return '';
}

e107::lan('chatbox_menu', e_LANGUAGE);



$emessage = '';



if ((isset($_POST['chat_submit']) || e_AJAX_REQUEST) && $_POST['cmessage'] !== '') {

	if ( ! USER && ! $pref['anon_post']) {
		// disallow post
	} else {
		$nick = trim(preg_replace("#\[.*\]#si", "", $tp->toDB($_POST['nick'])));

		$cmessage = $_POST['cmessage'];
		$cmessage = preg_replace("#\[.*?\](.*?)\[/.*?\]#s", "\\1", $cmessage);


		$fp = new floodprotect;

		if ($fp->flood('chatbox', 'cb_datestamp')) {
			if ((strlen(trim($cmessage)) < 1000) && trim($cmessage) !== '') {

				$cmessage = $tp->toDB($cmessage);

				if ($sql->select('chatbox', '*',
					"cb_message='{$cmessage}' AND cb_datestamp+84600>" . time())) {

					$emessage = CHATBOX_L17;

				} else {

					$datestamp = time();
					$ip = e107::getIPHandler()->getIP(false);

					if (USER) {

						$nick = USERID . "." . USERNAME;

						$postTime = time();
						$sql->update('user', "user_chats = user_chats + 1, user_lastpost = {$postTime} WHERE user_id = " . USERID);

					} else if ( ! $nick) {

						$nick = '0.Anonymous';

					} else {

						if ($sql->select('user', '*', "user_name='$nick' ")) {

							$emessage = CHATBOX_L1;

						} else {

							$nick = "0." . $nick;

						}

					}
					if ( ! $emessage) {
						$insertId = $sql->insert('chatbox',
							"0, '{$nick}', '{$cmessage}', '{$datestamp}', 0, '{$ip}' ");

						if ($insertId) {

							$edata_cb = [
								'id'        => $insertId,
								'nick'      => $nick,
								'cmessage'  => $cmessage,
								'datestamp' => $datestamp,
								'ip'        => $ip,
							];

							$e_event->trigger("cboxpost", $edata_cb); // deprecated

							e107::getEvent()->trigger('user_chatbox_post_created', $edata_cb);
							$e107cache->clear('nq_chatbox');
						}

					}
				}
			} else {
				$emessage = CHATBOX_L15;
			}
		} else {
			$emessage = $tp->lanVars(CHATBOX_L19, FLOODPROTECT ?: 'n/a');
		}
	}
}


if ( ! USER && ! $pref['anon_post']) {

	if ($pref['user_reg']) {

		$text1 = str_replace(['[', ']'], ["<a href='" . e_LOGIN . "'>", "</a>"],
			CHATBOX_L3);

		if ($pref['user_reg'] === 1) {
			$text1 .= str_replace(['[', ']'],
				["<a href='" . e_SIGNUP . "'>", "</a>"], CHATBOX_L3b);
		}

		$texta =
			"<div style='text-align:center'>" . $text1 . "</div><br /><br />";
	}

} else {
	$cb_width = (defined('CBWIDTH') ? CBWIDTH : '');

	if ($pref['cb_layer'] === 2) {

		$texta = "\n<form id='chatbox' action='" . e_SELF . "?" . e_QUERY . "'  method='post' onsubmit='return(false);'>
		<div>
			<input type='hidden' name='chatbox_ajax' id='chatbox_ajax' value='1' />
		</div>";

	} else {

		$texta = (e_QUERY
			? "\n<form id='chatbox' method='post' action='" . e_SELF . "?" . e_QUERY . "'>"
			: "\n<form id='chatbox' method='post' action='" . e_SELF . "'>");
	}

	$texta .= "<div class='control-group form-group' id='chatbox-input-block'>";

	if (($pref['anon_post'] == "1" && USER === false)) {
		$texta .= "\n<input class='tbox chatbox' type='text' id='nick' name='nick' value='' maxlength='50' " . ($cb_width
				? "style='width: " . $cb_width . ";'" : '') . " /><br />";
	}

	if ($pref['cb_layer'] === 2) {

		$oc =
			"onclick=\"javascript:sendInfo('" . SITEURLBASE . e_PLUGIN_ABS . "chatbox_menu/chatbox_menu.php', 'chatbox_posts', this.form);\"";

	} else {

		$oc = '';

	}

	$texta .= "
	<textarea placeholder=\"" . LAN_CHATBOX_100 . "\" required class='tbox chatbox form-control input-xlarge' id='cmessage' name='cmessage' cols='20' rows='5' style='max-width:97%; " . ($cb_width
			? "width:" . $cb_width . ";" : '') . " overflow: auto' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'></textarea>
	<br />
	<input class='btn btn-default btn-secondary button' type='submit' id='chat_submit' name='chat_submit' value='" . CHATBOX_L4 . "' {$oc}/>";


	// $texta .= "<input type='reset' name='reset' value='".CHATBOX_L5."' />"; // How often do we see these lately? ;-)


	if ($pref['cb_emote'] && $pref['smiley_activate']) {
		$texta .= "
		<input class='btn btn-default btn-secondary button' type='button' style='cursor:pointer' size='30' value='" . CHATBOX_L14 . "' onclick=\"expandit('emote')\" />
		<div class='well' style='display:none' id='emote'>" . r_emote() . "</div>\n";
	}

	$texta .= "</div>\n</form>\n";
}


if ($emessage !== '') {
	$texta .= "<div style='text-align:center'><b>" . $emessage . "</b></div>";
}


if ( ! $text = $e107cache->retrieve("nq_chatbox")) {

	global $pref, $tp;

	$pref['chatbox_posts'] =
		($pref['chatbox_posts'] ? $pref['chatbox_posts'] : 10);

	$chatbox_posts = $pref['chatbox_posts'];

	if ( ! isset($pref['cb_mod'])) {
		$pref['cb_mod'] = e_UC_ADMIN;
	}

	if ( ! defined('CB_MOD')) {
		define("CB_MOD", check_class($pref['cb_mod']));
	}

	$qry = "SELECT c.*, u.user_name, u.user_image FROM #chatbox AS c
	LEFT JOIN #user AS u ON SUBSTRING_INDEX(c.cb_nick, '.', 1) = u.user_id
	ORDER BY c.cb_datestamp DESC LIMIT 0, " . (int)$chatbox_posts;

	global $CHATBOXSTYLE;

	if($CHATBOXSTYLE)  // legacy chatbox style
	{
		$legacyIconSrc = e_IMAGE_ABS . 'admin_images/chatbox_16.png';
		$currentIconSrc = e_PLUGIN . 'chatbox_menu/images/chatbox_16.png';

		$legacySrch = array($legacyIconSrc, '{USERNAME}', '{MESSAGE}', '{TIMEDATE}');
		$legacyRepl = array($currentIconSrc, '{CB_USERNAME}','{CB_MESSAGE}','{CB_TIMEDATE}');


		$CHATBOX_TEMPLATE['start'] = '';
		$CHATBOX_TEMPLATE['item'] = str_replace($legacySrch, $legacyRepl, $CHATBOXSTYLE);
		$CHATBOX_TEMPLATE['end'] = '';
	}
	else 	// default chatbox style
	{
		$CHATBOX_TEMPLATE = e107::getTemplate('chatbox_menu', null, 'menu');
	}

	// FIX - don't call getScBatch() if don't need to globally register the methods
	// $sc = e107::getScBatch('chatbox');

	// the good way in this case - it works with any object having sc_*, models too
	//$sc = new chatbox_shortcodes();

	$sc = e107::getScBatch('chatbox_menu', true);

	if ($sql->gen($qry)) {

		$cbpost = $sql->rows();

		$text .= "<div id='chatbox-posts-block'>\n";

		$text .= $tp->parseTemplate($CHATBOX_TEMPLATE['start'], false, $sc);

		foreach ($cbpost as $cb) {
			$sc->setVars($cb);
			$text .= $tp->parseTemplate($CHATBOX_TEMPLATE['item'], false, $sc);
		}

		$text .= $tp->parseTemplate($CHATBOX_TEMPLATE['end'], false, $sc);

		$text .= "</div>";

	} else {
		$text .= "<span class='mediumtext'>" . CHATBOX_L11 . "</span>";
	}

	$total_chats = $sql->count("chatbox");

	if ($total_chats > $chatbox_posts || CB_MOD) {
		$text .= "<br /><div style='text-align:center'><a href='" . e_PLUGIN_ABS . "chatbox_menu/chat.php'>" . (CB_MOD
				? CHATBOX_L13
				: CHATBOX_L12) . "</a> (" . $total_chats . ")</div>";
	}

	$e107cache->set("nq_chatbox", $text);
}


$caption = (file_exists(THEME . "images/chatbox_menu.png")
	? "<img src='" . THEME_ABS . "images/chatbox_menu.png' alt='' /> " . LAN_PLUGIN_CHATBOX_MENU_NAME
	: LAN_PLUGIN_CHATBOX_MENU_NAME);


if ($pref['cb_layer'] === 1) {

	$text =
		$texta . "<div style='border : 0; padding : 4px; width : auto; height : " . $pref['cb_layer_height'] . "px; overflow : auto; '>" . $text . "</div>";

	$ns->tablerender($caption, $text, 'chatbox');

} elseif ($pref['cb_layer'] === 2 && e_AJAX_REQUEST) {

	$text = $texta . $text;
	$text = str_replace(e_IMAGE, e_IMAGE_ABS, $text);
	echo $text;

} else {

	$text = $texta . $text;

	if ($pref['cb_layer'] === 2) {
		$text = "<div id='chatbox_posts'>" . $text . "</div>";
	}

	$ns->tablerender($caption, $text, 'chatbox');
}

