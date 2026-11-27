<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Newsletter plugin - newsletter selection menu
 *
*/

if (!defined('e107_INIT')) { exit; }
if (!e107::isInstalled('newsletter'))
{
	return;
}

require_once(e_PLUGIN.'newsletter/handlers/newsletter_subscribers_class.php');

/**
 * Fetch all top-level newsletters, keyed by newsletter_id.
 *
 * @return array
 */
$nlGetTopLevel = static function () use ($sql) {
	$rows = $sql->createQueryBuilder()
		->select('*')->from('newsletter')
		->where('newsletter_parent', '0')
		->fetchAll();

	$list = array();
	foreach ($rows as $row) {
		$list[(int) $row['newsletter_id']] = $row;
	}

	return $list;
};

// Do not display menu when there are no newsletters defined yet
if(!USER)
{
	return FALSE;
}

$newsletterArray = $nlGetTopLevel();
if(empty($newsletterArray))
{
	return FALSE;
}
$requery = false;
//include_lan(e_PLUGIN.'newsletter/languages/'.e_LANGUAGE.'.php');

foreach($_POST as $key => $value)
{
	$joining = strpos($key, 'nlSubscribe_') === 0;
	if(!$joining && strpos($key, 'nlUnsubscribe_') !== 0)
	{
		continue;
	}

	$subid = (int) substr($key, strpos($key, '_') + 1);
	if(!isset($newsletterArray[$subid]))
	{
		continue;
	}

	$subscribers = new newsletter_subscribers($newsletterArray[$subid]['newsletter_subscribers']);
	if($joining)
	{
		$subscribers->add(USERID);
	}
	else
	{
		$subscribers->remove(USERID);
	}

	$sql->createQueryBuilder()->update('newsletter')
		->set('newsletter_subscribers', $subscribers->toStoredString())
		->where('newsletter_id', $subid)->execute();
	$requery = true;
}

//global $tp;

if($requery)
{
	$refreshed = $nlGetTopLevel();
	if(!empty($refreshed))
	{
		$newsletterArray = $refreshed;
	}
}

$text = '';
foreach($newsletterArray as $nl)
{
	$text .= "<div style='text-align: center; margin-left: auto; margin-right: auto;'>
	<form method='post' action='".e_SELF."'>
	<b>".
	$tp->toHTML($nl['newsletter_title'], TRUE)."</b><br />
	<span class='smalltext'>".
	$tp->toHTML($nl['newsletter_text'], TRUE)."</span><br /><br />
	";

	$subscribers = new newsletter_subscribers($nl['newsletter_subscribers']);
	if($subscribers->has(USERID))
	{
		$text .= NLLAN_48."<br /><br />
		<input class='btn btn-sm btn-primary button' type='submit' name='nlUnsubscribe_".$nl['newsletter_id']."' value='".NLLAN_51."' onclick=\"return jsconfirm(".$tp->toAttribute($tp->toJSON(NLLAN_49)).") \" />
		";
	}
	else
	{
		$text .= NLLAN_50." <b>".USEREMAIL."</b>)<br /><br />
		<input class='btn btn-sm btn-primary button' type='submit' name='nlSubscribe_".$nl['newsletter_id']."' value='".NLLAN_52."' onclick=\"return jsconfirm(".$tp->toAttribute($tp->toJSON(NLLAN_53)).") \" />
		";
	}
	$nl_count = $sql->createQueryBuilder()->from('newsletter')
		->where('newsletter_parent', $nl['newsletter_id'])
		->where('newsletter_flag', '1')->count();
	// display issued newsletters
	if($nl_count > 0 && USER)
	{	
		$text .= "<br /><a href='".e_PLUGIN_ABS."newsletter/nl_archive.php?show.".$nl['newsletter_id']."' alt='".NLLAN_72."' title='".NLLAN_72."'>".NLLAN_72."</a><br/><br/>";
	}
	$text .= "</form>
	</div>
	<br />
	";
}

$ns->tablerender(NLLAN_MENU_CAPTION, $text);
