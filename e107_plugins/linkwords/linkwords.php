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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/linkwords/linkwords.php,v $
|     $Revision: 1.11 $
|     $Date: 2009-08-15 11:55:30 $
|     $Author: marj_nl_fr $
|
|	This is just a stub so that systems migrated from 0.7 don't crash
|	It auto-updates the prefs so that the newer routine is called in future.
|
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }
if (!plugInstalled('linkwords')) exit;


class e_linkwords
{
	function e_linkwords()
	{
		global $pref, $admin_log;
		/* constructor */
		// Do an auto-update on the variable used to hook parsers - so we should only be called once
		include_lan(e_PLUGIN."linkwords/languages/".e_LANGUAGE.".php");
		$hooks = explode(",",$pref['tohtml_hook']);
		if (($key=array_search('linkwords',$hooks)) !== FALSE)
		{
			unset($hooks[$key]);
		}
		if (count($hooks) == 0)
		{
			unset($pref['tohtml_hook']);
		}
		else
		{
			$pref['tohtml_hook'] = implode(',',$hooks);
		}
		if (!isset($pref['e_tohtml_list']))
		{
			$pref['e_tohtml_list'] = array();
		}
		if (!in_array('linkwords',$pref['e_tohtml_list']))
		{
			$pref['e_tohtml_list'][] = 'linkwords';
		}
		save_prefs();
		$admin_log->log_event('LINKWD_05',LWLAN_58.'[!br!]'.$pref['tohtml_hook'],'');			// Log that the update was done
		return;
	}


	// This avoids confusing the parser!
	function to_html($text,$area = 'olddefault')
	{
		return $text;
	}


	function linkwords($text,$area = 'olddefault')
	{
		return $text;
	}
	
	function linksproc($text,$first,$limit)
	{  
		return $text;		// Shouldn't get called - but just in case
	} 
}

?>