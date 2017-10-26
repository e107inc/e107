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


			$this->request = e107::getRegistry('core/page/request');
			
			$action = varset($this->request['action']);
			
			if(($action == 'listPages' || $action == 'listChapters') && vartrue($this->request['id']))
			{
				$this->var = e107::getDb()->retrieve('page_chapters','chapter_name, chapter_meta_description, chapter_sef','chapter_id = '.intval($this->request['id']).' LIMIT 1');	
			}
			
			if($action == 'showPage' && vartrue($this->request['id'])) // get chapter and description from current. 
			{
				$query = "SELECT p.page_id,c.chapter_name,c.chapter_meta_description FROM #page AS p LEFT JOIN #page_chapters AS c ON p.page_chapter = c.chapter_id WHERE p.page_id = ".intval($this->request['id'])." LIMIT 1 "; 
				$rows = e107::getDb()->retrieve($query,true);
				$this->var = $rows[0];	
			}
					
			
		}
			
		/**
		 * Page Navigation
		 * @example {PAGE_NAVIGATION: template=navdoc&auto=1} in your Theme template. 
		 */	
		function sc_page_navigation($parm=null) // TODO when No $parm provided, auto-detect based on URL which book/chapters to display.
		{
		//	$parm = eHelper::scParams($parm);
				
			$tmpl = e107::getCoreTemplate('chapter', vartrue($parm['template'],'nav'), true, true); // always merge
			
			$template = $tmpl['showPage'];
			
			$request = $this->request;
			
			if($request && is_array($request))
			{
				switch ($request['action']) 
				{
					case 'listBooks':
						$parm['cbook'] = 'all';
						$template = $tmpl['listBooks'];
						if(e107::getPref('listBooks',false) == false) // List Books has been disabled. 
						{
							return false;
						}
					break;
					
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
			
			if(empty($data)){ return null; }
			
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
			return e107::getMenu()->renderMenu($parm,  false, false, true);									
		}


		/**
		 * Render All visible Menus from a specific chapter.
		 * @param null $parm
		 * @example {CHAPTER_MENUS: name=chapter-sef-url}
		 * @example {CHAPTER_MENUS: name=chapter-sef-url&template=xxxxx}
		 * @return string
		 */
		function sc_chapter_menus($parm=null)
		{
			$tp = e107::getParser();

			$text = '';
			$start = '';

			$sef = $tp->filter($parm['name'],'str');

			$registry = 'e_shortcode/sc_chapter_menus/'.$sef;

			if(!$pageArray = e107::getRegistry($registry))
			{
				$query = "SELECT * FROM #page AS p LEFT JOIN #page_chapters as ch ON p.page_chapter=ch.chapter_id WHERE ch.chapter_visibility IN (" . USERCLASS_LIST . ") AND p.menu_class IN (" . USERCLASS_LIST . ") AND ch.chapter_sef = '" . $sef . "' ORDER BY p.page_order ASC ";

				e107::getDebug()->log("Loading page Chapters");

				if(!$pageArray = e107::getDb()->retrieve($query, true))
				{
					e107::getDebug()->log('{CHAPTER_MENUS: name='.$parm['name'].'} failed.<br />Query: '.$query);
					return null;
				}

				e107::setRegistry($registry, $pageArray);
			}

			$template = e107::getCoreTemplate('menu',null,true,true);

			$sc = e107::getScBatch('page', null, 'cpage');
			$sc->setVars($pageArray[0]);
			$tpl = varset($pageArray[0]['menu_template'],'default'); // use start template from first row.

			if(!empty($parm['template']))
			{

				$tpl = $parm['template'];
				$start .= "<!-- CHAPTER_MENUS Start Template: ". $tpl." -->";

				if(empty($template[$tpl]))
				{
					e107::getDebug()->log('{CHAPTER_MENUS: '.http_build_query($parm).'} has an empty template.');
				}

			}

			$active = varset($parm['active'],1);

			$start .= $tp->parseTemplate($template[$tpl]['start'],true,$sc);

			$c=1;
			foreach($pageArray as $row)
			{
				$row['cmenu_tab_active'] = ($c === $active) ? true : false;

				if(empty($parm['template']))
				{
					$tpl = varset($row['menu_template'],'default');
				}

				$sc->setVars($row);

				$text .= $tp->parseTemplate($template[$tpl]['body'],true,$sc);
				$c++;
			}

			$end = $tp->parseTemplate($template[$tpl]['end'],true,$sc);

			$end .= "<!-- ".http_build_query($parm)." -->";

			if(!empty($parm['template']))
			{
				$end .= "<!-- CHAPTER_MENUS end template: ". $parm['template']." -->";
			}

			if(!empty($text))
			{
				return $start . $text . $end;
			}


		}

}
