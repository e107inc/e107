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
		 * @example {PAGE_NAVIGATION: chapter=4}
		 * @example {PAGE_NAVIGATION: book=3&pages=true}
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
			
			/** @var page_sitelink $links */
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
			return e107::getParser()->toHTML($this->var['chapter_name']);	
		}		
		
		
		function sc_page_chapter_description($parm='')
		{
			return e107::getParser()->toHTML($this->var['chapter_meta_description'],true);		
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
			$classCount = 0;

			$sef = $tp->filter($parm['name'],'str');

			$registry = 'e_shortcode/sc_chapter_menus/'.$sef;

			if(!$pageArray = e107::getRegistry($registry))
			{
				$query = "SELECT * FROM #page AS p LEFT JOIN #page_chapters as ch ON p.page_chapter=ch.chapter_id WHERE ch.chapter_visibility IN (" . USERCLASS_LIST . ") AND p.menu_class IN (" . USERCLASS_LIST . ") AND ch.chapter_sef = '" . $sef . "' ORDER BY p.page_order ASC ";

				e107::getDebug()->log("Loading Page Chapters (".$sef.")");

				if(!$pageArray = e107::getDb()->retrieve($query, true))
				{
					e107::getDebug()->log('{CHAPTER_MENUS: name='.$parm['name'].'} failed.<br />Query: '.$query);
					return null;
				}

				e107::setRegistry($registry, $pageArray);
			}

			$template = e107::getCoreTemplate('menu',null,true,true);

			$sc = e107::getScBatch('page', null, 'cpage');
			$editable = array(
				'table' => 'page',
				'pid'   => 'page_id',
				'perms' => '5',
				'shortcodes' => array(
					'cpagetitle' => array('field'=>'page_subtitle','type'=>'text', 'container'=>'span'),
					'cpagebody' => array('field'=>'page_text','type'=>'html', 'container'=>'div'),
					'cmenubody' => array('field'=>'menu_text','type'=>'html', 'container'=>'div'),

				)
			);

			$sc->setVars($pageArray[0]);
			$sc->editable($editable);

			$tpl = varset($pageArray[0]['menu_template'],'default'); // use start template from first row.

			if(!empty($parm['template']))
			{
				e107::getDebug()->log('{CHAPTER_MENUS CUSTOM TEMPLATE}');
				$tpl = $parm['template'];
				$start .= "<!-- CHAPTER_MENUS Start Template: ". $tpl." -->";

				if(empty($template[$tpl]))
				{
					e107::getDebug()->log('{CHAPTER_MENUS: '.http_build_query($parm).'} has an empty template.');
				}

			}

			if(!empty($parm['class']) && is_array($parm['class']))
			{
				$classArray = $parm['class'];
				$classCount = count($parm['class']);
			}


			$active = varset($parm['active'],1);

			$start .= $tp->parseTemplate($template[$tpl]['start'],true,$sc);

			$c=1;
			$i = 0;
			foreach($pageArray as $row)
			{
				if(!empty($parm['limit']) && $c > $parm['limit'])
				{
					break;
				}


				$row['cmenu_tab_active'] = ($c === (int) $active) ? true : false;

				if(empty($parm['template']))
				{
					$tpl = varset($row['menu_template'],'default');
				}

				$itemTemplate = $template[$tpl]['body'];

				$sc->setVars($row);

				if(!empty($classArray))
				{
					$itemTemplate = str_replace('{CLASS}',$classArray[$i],$itemTemplate);

					$i++;
					if($classCount === $i)
					{
						$i = 0;
					}
				}

				$text .= $tp->parseTemplate($itemTemplate,true,$sc);
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


		/**
		 * Render All visible Chapters from a specific Book.
		 * Uses "listChapter" template key. ie. $CHAPTER_TEMPLATE[---TEMPLATE --]['listChapters']
		 * @param null $parm
		 * @example {BOOK_CHAPTERS: name=book-sef-url}
		 * @example {BOOK_CHAPTERS: name=book-sef-url&template=xxxxx&limit=3}
		 * @return string
		 */
		function sc_book_chapters($parm)
		{
			$tp = e107::getParser();

			$sef = $tp->filter($parm['name'],'str');



			$tmplKey = varset($parm['template'],'panel');
			$limit = (int) varset($parm['limit'], 3);

			$registry = 'e_shortcode/sc_book_chapters/'.$sef. '/'.$limit;

			if(!$chapArray = e107::getRegistry($registry))
			{
				$bookID = e107::getDb()->retrieve('page_chapters', 'chapter_id', "chapter_sef = '" . $sef . "' LIMIT 1");

				if(empty($bookID))
				{
					return null;
				}

				$query = "SELECT * FROM #page_chapters WHERE chapter_visibility IN (" . USERCLASS_LIST . ") AND chapter_parent = ".$bookID."  ORDER BY chapter_order ASC LIMIT ".$limit;

				e107::getDebug()->log("Loading sc_book_chapters(".$sef.")");

				if(!$chapArray = e107::getDb()->retrieve($query, true))
				{
					e107::getDebug()->log('{BOOK_CHAPTERS: name='.$parm['name'].'} failed.<br />Query: '.$query);
					return null;
				}

				e107::setRegistry($registry, $chapArray);
			}



			$temp = e107::getCoreTemplate('chapter',$tmplKey,true,true);
			$template = $temp['listChapters'];

			$sc = e107::getScBatch('page', null, 'cpage');

			$start = "<!-- sc_book_chapters Start Template: ". $tmplKey." -->";
			$start .= $tp->parseTemplate($template['start'],true,$sc);

			$text = '';

			foreach($chapArray as $row)
			{
				$sc->setVars($row);
				$sc->setChapter($row['chapter_id']);
				$text .= $tp->parseTemplate($template['item'],true,$sc);
			}

			$end = $tp->parseTemplate($template['start'],true,$sc);
			$end .= "<!-- sc_book_chapters end template: ". $tmplKey." -->";

			if(!empty($text))
			{
				return $start . $text . $end;
			}

			return null;

		}




}
