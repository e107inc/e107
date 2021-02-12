<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Sitelinks configuration module - News
 *
 * $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.8/e107_plugins/news/e_sitelink.php $
 * $Id: e_sitelink.php 12401 2011-11-25 17:53:20Z secretr $
 *
*/

if (!defined('e107_INIT')) { exit; }

//TODO Lans

class news_featurebox // include plugin-folder in the name.
{
	function config()
	{	
		$links = array();
			
		$links[] = array( // render_type 
			'name'			=> "Featurebox",
			'function'		=> "process",
			'description' 	=> ""
		);	
		
		return $links;
	}
	
	

	function process() 
	{
		$sql = e107::getDb();
		$fbox = array();
		
		$nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";
		$query = "SELECT * FROM #news WHERE news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (news_class REGEXP ".$nobody_regexp.") AND FIND_IN_SET(5,news_render_type) ORDER BY news_datestamp DESC LIMIT 10";
		
		if($sql->gen($query))
		{		
			while($row = $sql->fetch())
			{
				$fbox[] = array(
					'title'			=> $row['news_title'],
					'url'			=> '{e_BASE}news.php?extend.'.$row['news_id'],
					'body'			=> $row['news_summary'],
					'image'			=> $row['news_image'],
					'class'			=> $row['news_class']
				);
			}
			
			return $fbox;
	    };
	}
	
}



