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
require_once('../../class2.php');
if ( ! e107::isInstalled('chatbox_menu')) {
	e107::redirect();
	exit;
}

e107::lan('chatbox_menu', e_LANGUAGE);

require_once(HEADERF);
$mes = e107::getMessage();
$sql->select('menus', "*", "menu_name='chatbox_menu'");
$row = $sql->fetch();

if ( ! check_class((int)$row['menu_class'])) {
	$mes->addError(CHATBOX_L24);
	$ns->tablerender(LAN_ERROR, $mes->render());
	require_once(FOOTERF);
	exit;
}

if ( ! isset($pref['cb_mod'])) {
	$pref['cb_mod'] = e_UC_ADMIN;
}

define('CB_MOD', check_class($pref['cb_mod']));

if ( ! empty($_POST['moderate']) && CB_MOD) {

	if (isset($_POST['block'])) {

		foreach (array_keys($_POST['block']) as $k) {
			$kk[] = intval($k);
		}

		$blocklist = implode(",", $kk);
		$sql->gen("UPDATE #chatbox SET cb_blocked=1 WHERE cb_id IN ({$blocklist})");
	}


	if (isset($_POST['unblock'])) {

		foreach (array_keys($_POST['unblock']) as $k) {
			$kk[] = intval($k);
		}

		$unblocklist = implode(",", $kk);
		$sql->gen("UPDATE #chatbox SET cb_blocked=0 WHERE cb_id IN ({$unblocklist})");
	}


	if (isset($_POST['delete'])) {

		$deletelist = implode(",", array_keys($_POST['delete']));

		$query = "SELECT c.cb_id, u.user_id FROM #chatbox AS c
		LEFT JOIN #user AS u ON SUBSTRING_INDEX(c.cb_nick,'.',1) = u.user_id
		WHERE c.cb_id IN (" . $deletelist . ")";

		$sql->gen($query);

		$rowlist = $sql->rows();

		foreach ($rowlist as $row) {
		    $userId = (int)$row['user_id'];
			$sql->gen("UPDATE #user SET user_chats=user_chats-1 WHERE user_id = {$userId} ");
		}

		$sql->gen("DELETE FROM #chatbox WHERE cb_id IN ({$deletelist})");
	}

	e107::getCache()->clear("nq_chatbox");
	$mes->addSuccess(CHATBOX_L18);
}

// when coming from search.php

$fs = false;

if (strstr(e_QUERY, "fs")) {
	$cgtm = intval(str_replace(".fs", "", e_QUERY));
	$fs = true;
}
// end search

if (e_QUERY ? $from = intval(e_QUERY) : $from = 0) {
	;
}

$chat_total = $sql->count('chatbox');

$qry_where = (CB_MOD ? "1" : "cb_blocked=0");



// when coming from search.php calculate page number
if ($fs) {

	$page_count = 0;
	$row_count = 0;

	$sql->select("chatbox", "*", "{$qry_where} ORDER BY cb_datestamp DESC");

	while ($row = $sql->fetch()) {

		if ($row['cb_id'] == $cgtm) {
			$from = $page_count;
			break;
		}

		$row_count++;
		if ($row_count == 30) {
			$row_count = 0;
			$page_count += 30;
		}

	}
}
// end search


/** Render chat posts **/
$sql->select("chatbox", "*",
	"{$qry_where} ORDER BY cb_datestamp DESC LIMIT " . intval($from) . ", 30");

$chatList = $sql->rows();


$CHATBOX_LIST_TEMPLATE =
	e107::getTemplate('chatbox_menu', 'chatbox_menu', 'list');

$sc = e107::getScBatch('chatbox_menu', true);


$textstring = '';

foreach ($chatList as $row) {

	$sc->setVars($row);

	$textstring .= $tp->parseTemplate($CHATBOX_LIST_TEMPLATE['item'], false, $sc);
}


$textstart = $tp->parseTemplate($CHATBOX_LIST_TEMPLATE['start'], true, $sc);
$textend = $tp->parseTemplate($CHATBOX_LIST_TEMPLATE['end'], true, $sc);

$text = $textstart . $textstring . $textend;


if (CB_MOD) {

	$text =
		"<form method='post' action='" . e_SELF . "'>"
		. $text .
		"<input type='submit' class='btn btn-danger btn-secondary button float-right pull-right' name='moderate' value='" . CHATBOX_L13 . "' />
		</form>";

}

$parms = "{$chat_total},30,{$from}," . e_SELF . '?[FROM]';

$text .= "<div class='nextprev'>" . $tp->parseTemplate("{NEXTPREV={$parms}}") . '</div>';

$ns->tablerender(CHATBOX_L20, $mes->render() . $text);

require_once(FOOTERF);
