<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
*/

if (!defined('e107_INIT')) { exit; }

class page_shortcodes extends e_shortcode
{
		function sc_page_navigation($parm) // TODO when No $parm provided, auto-detect based on URL which book/chapters to display. 
		{
			// FIXME sitelink class should be page_sitelink
			$links = e107::getAddon('page', 'e_sitelink', 'page_sitelink');
			
			$data = $links->pageNav($parm);

			$template = e107::getCoreTemplate('page','nav');
			if(isset($data['title']) && !vartrue($template['noAutoTitle']))
			{
				$data = $data['body'];
			}	
					
			return e107::getNav()->render($data, $template) ;
					
		}
}
