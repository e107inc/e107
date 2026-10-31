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

// Do not display menu when there are no newsletters defined yet
if(!USER || !$sql->select('newsletter', '*', "newsletter_parent='0'"))
{	
	return FALSE;
}

$newsletterArray = $sql->db_getList('ALL', false, false, 'newsletter_id');
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

	$sql->update('newsletter', array('data' => array('newsletter_subscribers' => $subscribers->toStoredString()), 'WHERE' => 'newsletter_id = '.$subid));
	$requery = true;
}

//global $tp;

if($requery)
{
	if($sql->select('newsletter', '*', "newsletter_parent='0' "))
	{
		$newsletterArray = $sql->db_getList('ALL', false, false, 'newsletter_id');
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
	$nl_count = $sql->count('newsletter', "(*)", "WHERE newsletter_parent='".$nl['newsletter_id']."' AND newsletter_flag='1'");
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
