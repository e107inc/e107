<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }
// if (!e107::isInstalled('linkwords')) exit; // This will break a site completely under some circumstance. 

class e_linkwords
{
	function e_linkwords()
	{
		global $pref, $admin_log;
		/* constructor */
		// Do an auto-update on the variable used to hook parsers - so we should only be called once
		
		e107::lan('linkwords', e_LANGUAGE); // e_PLUGIN."linkwords/languages/".e_LANGUAGE.".php"
		
		$hooks = explode(",", $pref['tohtml_hook']);
		
		if(($key=array_search('linkwords',$hooks)) !== FALSE)
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
		e107::getLog()->add('LINKWD_05',LWLAN_58.'[!br!]'.$pref['tohtml_hook'],'');			// Log that the update was done
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

