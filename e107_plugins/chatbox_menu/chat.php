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

use e107\Database\QueryBuilder;
use e107\Database\SqlFragment;

require_once(__DIR__.'/../../class2.php');
if ( ! e107::isInstalled('chatbox_menu')) {
	e107::redirect();
	exit;
}

e107::lan('chatbox_menu', e_LANGUAGE);

require_once(HEADERF);
$mes = e107::getMessage();
$tp = e107::getParser();
$sql = e107::getDb();
$ns = e107::getRender();

$row = $sql->createQueryBuilder()
	->select('*')->from('menus')
	->where('menu_name', 'chatbox_menu')
	->fetchRow();

if($row)
{
	if(!check_class((int) $row['menu_class']))
	{
		$mes->addError(CHATBOX_L24);
		$ns->tablerender(LAN_ERROR, $mes->render());
		require_once(FOOTERF);
		exit;
	}
}

if ( ! isset($pref['cb_mod'])) {
	$pref['cb_mod'] = e_UC_ADMIN;
}
if(!defined('CB_MOD'))
{
	define('CB_MOD', check_class($pref['cb_mod']));
}

if ( ! empty($_POST['moderate']) && CB_MOD) {

	if (isset($_POST['block']))
	{
		$kk = array();

		foreach (array_keys($_POST['block']) as $k) {
			$kk[] = intval($k);
		}

		$sql->createQueryBuilder()->update('chatbox')
			->set('cb_blocked', 1)->whereIn('cb_id', $kk)->execute();
	}


	if (isset($_POST['unblock']))
	{
		$k = array();
		$kk = array();
		foreach (array_keys($_POST['unblock']) as $k) {
			$kk[] = intval($k);
		}

		$sql->createQueryBuilder()->update('chatbox')
			->set('cb_blocked', 0)->whereIn('cb_id', $kk)->execute();
	}


	if (isset($_POST['delete'])) {

		$kk = array();
		foreach (array_keys($_POST['delete']) as $k) {
			$kk[] = intval($k);
		}

		$rowlist = $sql->createQueryBuilder()
			->select('c.cb_id', 'u.user_id')->from('chatbox', 'c')
			->leftJoin('user', 'u', SqlFragment::raw("SUBSTRING_INDEX(c.cb_nick,'.',1) = u.user_id"))
			->whereIn('c.cb_id', $kk)
			->fetchAll();

		foreach ($rowlist as $row) {
		    $userId = (int)$row['user_id'];
			$sql->createQueryBuilder()->update('user')
				->decrement('user_chats', 1)->where('user_id', $userId)->execute();
		}

		$sql->createQueryBuilder()->delete('chatbox')->whereIn('cb_id', $kk)->execute();
	}

	e107::getCache()->clear("nq_chatbox");
	$mes->addSuccess(CHATBOX_L18);
}

// when coming from search.php

$fs = false;

if (strpos(e_QUERY, "fs") !== false) {
	$cgtm = intval(str_replace(".fs", "", e_QUERY));
	$fs = true;
}
// end search

//if (e_QUERY ? $from = intval(e_QUERY) : $from = 0) {

//}

$chat_total = $sql->createQueryBuilder()->from('chatbox')->count();

/**
 * Build the base chat-post query: all columns from chatbox, newest first,
 * restricted to visible (unblocked) posts unless the moderator view is active.
 *
 * @return QueryBuilder
 */
$chatboxQuery = static function () use ($sql) {
	$qb = $sql->createQueryBuilder()
		->select('*')->from('chatbox')
		->orderBy('cb_datestamp', 'DESC');

	if (!CB_MOD) {
		$qb->where('cb_blocked', 0);
	}

	return $qb;
};


$from = 0;
// when coming from search.php calculate page number
if ($fs) {

	$page_count = 0;
	$row_count = 0;

	$rows = $chatboxQuery()->fetchEach();

	foreach ($rows as $row) {

		if ($row['cb_id'] == $cgtm)
		{
			$from = $page_count;
			break;
		}

		$row_count++;
		if ($row_count == 30)
		{
			$row_count = 0;
			$page_count += 30;
		}

	}
}
// end search


/** Render chat posts **/
$chatList = $chatboxQuery()
	->setFirstResult(intval($from))->setMaxResults(30)
	->fetchAll();


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
