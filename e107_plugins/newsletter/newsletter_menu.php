<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Newsletter plugin - newsletter selection menu
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/newsletter/newsletter_menu.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

/**
 *	e107 Newsletter plugin
 *
 *	@package	e107_plugins
 *	@subpackage	newsletter
 *	@version 	$Id$;
 */

if (!defined('e107_INIT')) { exit; }
if (!$e107->isInstalled('newsletter')) 
{
	return;
}

if(!USER || !$sql -> db_Select('newsletter', '*', "newsletter_parent='0'"))
{	// no newsletters defined yet
	return FALSE;
}

$newsletterArray = $sql -> db_getList();
$requery = false;
include_lan(e_PLUGIN.'newsletter/languages/'.e_LANGUAGE.'.php');

foreach($_POST as $key => $value)
{
	if(strpos($key, 'nlUnsubscribe_') === 0)
	{
		$subid = str_replace('nlUnsubscribe_', '', $key);
		$newsletterArray[$subid]['newsletter_subscribers'] = str_replace(chr(1).USERID, "", $newsletterArray[$subid]['newsletter_subscribers']);
		$sql -> db_Update('newsletter', "newsletter_subscribers='".$newsletterArray[$subid]['newsletter_subscribers']."' WHERE newsletter_id='".intval($subid)."' ");
		$requery = true;
	}
	else if(strpos($key, 'nlSubscribe_') === 0)
	{
		$subid = str_replace("nlSubscribe_", "", $key);
		$nl_subscriber_array = $newsletterArray[$subid]['newsletter_subscribers'];
		if (!array_key_exists(USERID, $nl_subscriber_array))
		{	// prevent double entry of same user id
			$newsletterArray[$subid]['newsletter_subscribers'] .= chr(1).USERID;
			$subscribers_list = array_flip(explode(chr(1), $newsletterArray[$subid]['newsletter_subscribers']));
			sort($subscribers_list);
			$new_subscriber_list = implode(chr(1), array_keys($subscribers_list));
			if (substr($new_subscriber_list, 0, 1) == '0')
			{	// remove the possible zero caused by function array_flip
				$new_subscriber_list = substr($new_subscriber_list, 1);
			}
			$sql -> db_Update('newsletter', "newsletter_subscribers='".$new_subscriber_list."' WHERE newsletter_id='".intval($subid)."' ");
			$requery = true;
		}
	}
}

global $tp;

if($requery)
{
	if($sql -> db_Select('newsletter', '*', "newsletter_parent='0' "))
	{
		$newsletterArray = $sql -> db_getList();
	}
}	

$text = '';
foreach($newsletterArray as $nl)
{
	$text .= "<div style='text-align: center; margin-left: auto; margin-right: auto;'>
	<form method='post' action='".e_SELF."'>
	<b>".
	$tp -> toHTML($nl['newsletter_title'], TRUE)."</b><br />
	<span class='smalltext'>".
	$tp -> toHTML($nl['newsletter_text'], TRUE)."</span><br /><br />
	";

	if(preg_match("#".chr(1).USERID."(".chr(1)."|$)#si", $nl['newsletter_subscribers']))
	{
		$text .= NLLAN_48."<br /><br />
		<input class='button' type='submit' name='nlUnsubscribe_".$nl['newsletter_id']."' value='".NLLAN_51."' onclick=\"return jsconfirm('".$tp->toJS(NLLAN_49)."') \" />
		";
	}
	else
	{
		$text .= NLLAN_50." <b>".USEREMAIL."</b> )<br /><br />
		<input class='button' type='submit' name='nlSubscribe_".$nl['newsletter_id']."' value='".NLLAN_52."' onclick=\"return jsconfirm('".$tp->toJS(NLLAN_53)."') \" />
		";
	}
	$nl_count = $sql -> db_Count('newsletter', "(*)", "WHERE newsletter_parent='".$nl['newsletter_id']."' AND newsletter_flag='1'");
	if($nl_count > 0 && USER)
	{	// display issued newsletters
		$text .= "<br /><a href='".e_PLUGIN_ABS."newsletter/nl_archive.php?show.".$nl['newsletter_id']."' alt='".NLLAN_72."' title='".NLLAN_72."'>".NLLAN_72."</a><br/><br/>";
	}
	$text .= "</form>
	</div>
	<br />
	";
}

$ns -> tablerender(NLLAN_MENU_CAPTION, $text);
?>