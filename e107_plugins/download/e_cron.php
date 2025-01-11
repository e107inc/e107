<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin configuration module - gsitemap
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/gsitemap/e_cron.php,v $
 * $Revision: 12212 $
 * $Date: 2011-05-11 15:25:02 -0700 (Wed, 11 May 2011) $
 * $Author: e107coders $
 *
*/

if (!defined('e107_INIT')) { exit; }


class download_cron // include plugin-folder in the name.
{
	function config()
	{
		$cron = array();
	
		$cron[] = array(
			'name'			=> "Prune Download Log older than 12 months", // Prune downloads history
			'function'		=> "pruneLog",
			'category'		=> '',
			'description' 	=> "Non functional at the moment"
		);	
		
		return $cron;
	}
	
	

	function pruneLog() 
	{
	    // Whatever code you wish.
	    e107::getMessage()->add("Executed dummy function within download/e_cron.php");
	    return ;
	}
	
	
	

}



