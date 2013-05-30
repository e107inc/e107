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
		protected $request;
		
		function __construct()
		{
			
			$this->request = e107::getRegistry('core/pages/request');
			
			if((varset($this->request['action']) == 'listPages' || varset($this->request['action']) == 'listChapters') && vartrue($this->request['id']))
			{
				$this->var = e107::getDb()->retrieve('page_chapters','chapter_name, chapter_meta_description','chapter_id = '.intval($this->request['id']).' LIMIT 1');	
			}		
			
		}
			
		/**
		 * Page Navigation
		 * @example {PAGE_NAVIGATION: template=navdoc&auto=1} in your Theme template. 
		 */	
		function sc_page_navigation($parm='') // TODO when No $parm provided, auto-detect based on URL which book/chapters to display. 
		{
		//	$parm = eHelper::scParams($parm);
			
			$tmpl = e107::getCoreTemplate('chapter', vartrue($parm['template'],'nav'), true, true); // always merge
			
			$template = $tmpl['showPage'];
			
			$request = $this->request;
			
			if($request && is_array($request))
			{
				switch ($request['action']) 
				{
					case 'listChapters':
						$parm['cbook'] = $request['id'];
						$template = $tmpl['listChapters'];
					break;
					
					case 'listPages':
						$parm['cchapter'] = $request['id'];
						$template = $tmpl['listPages'];
					break;
					
					case 'showPage':
						$parm['cpage'] = $request['id'];
					break;
				}
			}
			
			if($parm)
			{
				 $parm = http_build_query($parm, null, '&');
			}
			else
			{
				$parm = '';
			}
			

			$links = e107::getAddon('page', 'e_sitelink');
			
			$data = $links->pageNav($parm);
		
			if(isset($data['title']) && !vartrue($template['noAutoTitle']))
			{
				// use chapter title
				$template['caption'] = $data['title'];
				$data = $data['body'];
			}
			
			if(empty($data)){ return; }
			
			return e107::getNav()->render($data, $template) ;
			
		}
		
		
		function sc_page_chapter_name($parm='')
		{
			return e107::getParser()->toHtml($this->var['chapter_name']);	
		}		
		
		
		function sc_page_chapter_description($parm='')
		{
			return e107::getParser()->toHtml($this->var['chapter_meta_description'],true);		
		}
		
		/**
		 *  New in v2.x. eg. {CMENU=feature-1} Renders a menu called 'feature-1' as found in the e107_page table  See admin Pages/Menus . 
		 */
		function sc_cmenu($parm='')
		{
			return e107::getMenu()->renderMenu($parm, false);									
		}
}
