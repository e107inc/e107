<?php
/*
 * e107 website system
 *
 * Copyright (C) e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * URL and front controller Management
 *
 * $URL$
 * $Id$
*/

require_once("class2.php");
e107::coreLan('page');

$e107CorePage = new pageClass(false);

// Important - save request BEFORE any output (header footer) - used in navigation menu
if(!e_QUERY)
{
	$e107CorePage->setRequest('listBooks');
	
	require_once(HEADERF);
//	$tmp = $e107CorePage->listPages();
	$tmp = $e107CorePage->listBooks();
	
//	$text = $tp->parseTemplate("{PAGE_NAVIGATION=book=2}",true);
	if(is_array($tmp))
	{
		$ns->tablerender($tmp['title'], $text, 'cpage');
	}
	require_once(FOOTERF);
	exit;
}
elseif(vartrue($_GET['bk'])) //  List Chapters within a specific Book
{
	$e107CorePage->setRequest('listChapters');
	
	require_once(HEADERF);
	$text = $e107CorePage->listChapters($_GET['bk']);
	$ns->tablerender('', $text, 'cpage'); // TODO FIXME Caption eg. "book title"
	require_once(FOOTERF);
	exit;	
}
elseif(vartrue($_GET['ch'])) // List Pages within a specific Chapter
{
	$e107CorePage->setRequest('listPages');

	require_once(HEADERF);	

	$text = $e107CorePage->listPages($_GET['ch'],$template);
	$ns->tablerender('', $text, 'cpage'); // TODO FIXME Caption eg. "book title"
	require_once(FOOTERF);
	exit;		
}
else
{
	$e107CorePage->setRequest('showPage');
	$e107CorePage->processViewPage();
	
	require_once(HEADERF);
	
	echo $e107CorePage->showPage();
	
	require_once(FOOTERF);
	exit;
}

/* EOF */

class pageClass
{

	public $bullet;						/* bullet image */
	public $pageText;					/* main text of selected page, not parsed */
	public $multipageFlag;				/* flag - true if multiple page page, false if not */
	public $pageTitles;					/* array containing page titles */
	public $pageID;						/* id number of page to be displayed */
	public $pageSelected;				/* selected page of multiple page page */
	public $pageToRender;				/* parsed page to be sent to screen */
	public $debug;						/* temp debug flag */
	public $title;						/* title of page, it if has one (as defined in [newpage=title] tag */
	public $page;						/* page DB data */
	public $batch;						/* shortcode batch object */
	public $template;					/* current template array */
	protected $authorized;				/* authorized status */
	public $cacheString;				/* current page cache string */
	public $cacheTitleString;			/* current page title and comment flag cache string */
	public $cacheData = null;			/* cache data */
	
	function __construct($debug=FALSE)
	{
		/* constructor */
		if(!vartrue($_GET['id'])) // legacy URLs  /page.php?x
		{
			$tmp 				= explode(".", e_QUERY);
			$this->pageID 		= intval($tmp[0]);
			$this->pageSelected = (isset($tmp[1]) ? intval($tmp[1]) : 0);
			$this->pageTitles 	= array();
			$this->bullet 		= '';
		}
		else // NEW URLS  /page.php?id=x // TODO Complete and test. 
		{
			$tmp 				= explode(".", e_QUERY);
			$this->pageID 		= intval($_GET['id']);
			$this->pageSelected = (isset($tmp[1]) ? intval($tmp[1]) : 0); // Not sure what this is?
			$this->pageTitles 	= array();
			$this->bullet 		= '';	// deprecated - use CSS instead.  	
		}
		
		// TODO nq_ (no query) cache string
		$this->cacheString = 'page_'.$this->pageID.'_'.$this->pageSelected;
		$this->cacheTitleString = 'page-t_'.$this->pageID.'_'.$this->pageSelected;
	
		if(defined('BULLET'))
		{
			$this->bullet = "<img src='".THEME_ABS."images/".BULLET."' alt='' class='icon' />";
		}
		elseif(file_exists(THEME.'images/bullet2.gif'))
		{
			$this->bullet = "<img src='".THEME_ABS."images/bullet2.gif' alt='' class='icon' />";
		}
		elseif(file_exists(THEME.'images/bullet2.png'))
		{
			$this->bullet = "<img src='".THEME_ABS."images/bullet2.png' alt='' class='icon' />";
		}

		$this->debug = $debug;

		if($this->debug)
		{
			$this->debug = "<b>PageID</b> ".$this->pageID." <br />";
			$this->debug .= "<b>pageSelected</b> ".$this->pageSelected." <br />";
		}
		
		
	}

	// XXX temporary solution - upcoming page rewrite
	public function setRequest($request)
	{
		switch ($request) 
		{
			case 'listChapters':
				$id = intval($_GET['bk']);
			break;
			
			case 'listPages':
				$id = intval($_GET['ch']);
			break;
			
			case 'showPage':
				$id = $this->pageID;
			break;
			
			case 'listBooks':
			default:
				$id = 0;
			break;
		}
		e107::setRegistry('core/pages/request', array('action' => $request, 'id' => $id));
	}

	

	/**
	 * @todo Check userclasses 
	 * @todo sef urls
	 */
	function listBooks()
	{
		$sql = e107::getDb('sql2');
		$tp = e107::getParser();
		$frm = e107::getForm();
		
		$text = "";
		
		
		if(e107::getPref('listBooks',false) && $sql->select("page_chapters", "*", "chapter_parent ='0' ORDER BY chapter_order ASC "))
		{
			$layout 	= e107::getPref('listBooksTemplate','default'); 		
			$tml 		= e107::getCoreTemplate('chapter','', true, true); // always merge	
			$tmpl 		= varset($tml[$layout]);
			$template 	= $tmpl['listBooks'];
			
			$text = $template['start'];
			
			while($row = $sql->fetch())
			{
				$var = array(
					'BOOK_NAME' 		=> $tp->toHtml($row['chapter_name']),
					'BOOK_ANCHOR'		=> $frm->name2id($row['chapter_name']),
					'BOOK_ICON'			=> $this->chapterIcon($row['chapter_icon']),
					'BOOK_DESCRIPTION'	=> $tp->toHtml($row['chapter_meta_description'],true,'BODY'),
					'CHAPTERS'			=> $this->listChapters(intval($row['chapter_id'])),
					'BOOK_URL'			=> e_BASE."page.php?bk=".intval($row['chapter_id']) // FIXME SEF-URL
				);
			
				$text .= $tp->simpleParse($template['item'],$var);
			}			
		}	
		
		if(e107::getPref('listPages',false))
		{
			$text .= "<h3>Other Articles</h3>"; // Book Title. 		
			$text .= $this->listPages(0);	// Pages unassigned to Book/Chapters. 
		} //
		
		if($text)
		{
			$caption = varset($template['caption'],"Articles"); 
			e107::getRender()->tablerender($caption, $text, "cpage_list");
		}
		else
		{
			message_handler("MESSAGE", LAN_PAGE_1);
			require_once(FOOTERF); // prevent message from showing twice and still listing chapters
			exit;
		}
		
		
		
		
	}


	/**
	 * Parse the Book/Chapter "listChapters' template 
	 */
	function listChapters($book=1)
	{
		$sql = e107::getDb('chap');
		$tp = e107::getParser();
		$frm = e107::getForm();
		
		// retrieve the template to use for this book 
		if(!$layout = $sql->retrieve('page_chapters','chapter_template','chapter_id = '.intval($book).' LIMIT 1'))
		{
			$layout = 'default';
		}
		
		
		$tml = e107::getCoreTemplate('chapter','', true, true); // always merge	
		$tmpl = varset($tml[$layout]);
		
		$template = $tmpl['listChapters'];
		
		if($sql->select("page_chapters", "*", "chapter_parent = ".intval($book)."  ORDER BY chapter_order ASC "))
		{
			$text .= $template['start']; 
			
			while($row = $sql->fetch())
			{
				$var = array(
					'CHAPTER_NAME' 			=> $tp->toHtml($row['chapter_name']),
					'CHAPTER_ANCHOR'		=> $frm->name2id($row['chapter_name']),
					'CHAPTER_ICON'			=> $this->chapterIcon($row['chapter_icon']),
					'CHAPTER_DESCRIPTION'	=> $tp->toHtml($row['chapter_meta_description'],true,'BODY'),
					'PAGES'					=> $this->listPages(intval($row['chapter_id'])),
					'CHAPTER_URL'			=> e_BASE."page.php?ch=".intval($row['chapter_id']) // FIXME SEF-URL
				);
				
				$text .= $tp->simpleParse($template['item'],$var);

			}
			
			$text .= $template['end'];		
			
		}
		else
		{
			$text = e107::getMessage()->addInfo("There are no chapters in this book")->render();	
		}	
		
		return $text;		
	}


	/**
	 * Handle Chapter Icon Glyphs. 
	 */
	private function chapterIcon($icon)
	{
		$tp = e107::getParser();
			
		if(!vartrue($icon))
		{
			return;	
		}
					
		if($glyph = $tp->toGlyph($icon))
		{
			return $glyph;
		}
		else
		{
			return $tp->toIcon($icon);
		}	
	}


	
	function listPages($chapt=0)
	{
		$sql 			= e107::getDb('pg');
		$tp 			= e107::getParser();
		$this->batch 	= e107::getScBatch('page',null,'cpage');


		// retrieve the template to use for this chapter. 
		if(!$layout = $sql->retrieve('page_chapters','chapter_template','chapter_id = '.intval($chapt).' LIMIT 1'))
		{
			$layout = 'default';
		}
		
		$tml = e107::getCoreTemplate('chapter','', true, true); // always merge	
		$tmpl = varset($tml[$layout]);
	
		
	//	$tmpl = e107::getCoreTemplate('chapter','docs', true, true); // always merge	
		$template = $tmpl['listPages'];
		
			if(!$count = $sql->select("page", "*", "page_title !='' AND page_chapter=".intval($chapt)." AND page_class IN (".USERCLASS_LIST.") ORDER BY page_order ASC "))
			{
				return e107::getMessage()->addInfo(LAN_PAGE_2)->render();
			//	$text = "<ul class='page-pages-list page-pages-none'><li>".LAN_PAGE_2."</li></ul>";
			}
			else
			{
				
				$pageArray = $sql->db_getList();

				$text .= $template['start'];
				
				foreach($pageArray as $page)
				{
					$data = array(
						'title' => $page['page_title'],
						'text'	=> $tp->toHtml($page['page_text'],true)
					);
					
					$this->page = $page;
					$this->batch->setVars(new e_vars($data))->setScVar('page', $this->page);

					$url = e107::getUrl()->create('page/view', $page, 'allow=page_id,page_sef');
					// $text .= "<li><a href='".$url."'>".$tp->toHtml($page['page_title'])."</a></li>"; 
					$text .= e107::getParser()->parseTemplate($template['item'], true, $this->batch);
				}
				
				$text .= $template['end'];
				
		
			//	$caption = ($title !='')? $title: LAN_PAGE_11;
			//	e107::getRender()->tablerender($caption, $text,"cpage_list");
			}

		
		return $text;
	}

	
	function processViewPage()
	{
		if($this->checkCache())
		{
			return;
		}
		
		$sql = e107::getDb();
		
		$query = "SELECT p.*, u.user_id, u.user_name, user_login FROM #page AS p
		LEFT JOIN #user AS u ON p.page_author = u.user_id
		WHERE p.page_id=".intval($this->pageID); // REMOVED AND p.page_class IN (".USERCLASS_LIST.") - permission check is done later 

		if(!$sql->gen($query))
		{
			$ret['title'] = LAN_PAGE_12;			// ***** CHANGED
			$ret['sub_title'] = '';
			$ret['text'] = LAN_PAGE_3;
			$ret['comments'] = '';
			$ret['rating'] = '';
			$ret['np'] = '';
			$ret['err'] = TRUE;
			$ret['cachecontrol'] = false;
			$this->authorized = 'nf';
			$this->template = e107::getCoreTemplate('page', 'default');
			$this->batch = e107::getScBatch('page',null,'cpage')->setVars(new e_vars($ret))->setScVar('page', array());
			
			define("e_PAGETITLE", $ret['title']);
			
			return;
		}

		$this->page = $sql->fetch();

		$this->template = e107::getCoreTemplate('page', vartrue($this->page['page_template'], 'default'), false, true); // setting override to true breaks default. 
	//	$this->template = e107::getCoreTemplate('page', 'default',true,true);
	//	print_a($this->template);
		if(empty($this->template))
		{
			 $this->template = e107::getCoreTemplate('page', 'default');
		}
		
		$this->batch = e107::getScBatch('page',null,'cpage');

		$this->pageText = $this->page['page_text'];

		$this->pageCheckPerms($this->page['page_class'], $this->page['page_password'], $this->page['page_title']);

		if($this->debug)
		{
			echo "<b>pageText</b> ".$this->pageText." <br />";
		}

		$this->parsePage();

		$pagenav = $rating = $comments = '';
		if($this->authorized === true)
		{
			$pagenav = $this->pageIndex();
			$rating = $this->pageRating($this->page['page_rating_flag']);
			$comments = $this->pageComment($this->page['page_comment_flag']);
		}

		$ret['title'] = $this->page['page_title'];
		$ret['sub_title'] = $this->title;
        $ret['text'] = $this->pageToRender;
		$ret['np'] = $pagenav;
		$ret['rating'] = $rating;
		$ret['comments'] = $comments;
		$ret['err'] = FALSE;
		$ret['cachecontrol'] = (isset($this->page['page_password']) && !$this->page['page_password'] && $this->authorized === true);		// Don't cache password protected pages
		
		$this->batch->setVars(new e_vars($ret))->setScVar('page', $this->page);
		
		define('e_PAGETITLE', eHelper::formatMetaTitle($ret['title']));
		if($this->page['page_metadscr']) define('META_DESCRIPTION', eHelper::formatMetaDescription($this->page['page_metadscr']));
		if($this->page['page_metakeys']) define('META_KEYWORDS', eHelper::formatMetaKeys($this->page['page_metakeys']));
		//return $ret;
	}

	public function checkCache()
	{
		$e107cache = e107::getCache();
		$cacheData = $e107cache->retrieve($this->cacheString);
		if(false !== $cacheData)
		{
			$this->cacheData = array();
			$this->cacheData['PAGE'] = $cacheData;
			list($pagetitle, $comment_flag, $meta_keys, $meta_dscr) = explode("^^^",$e107cache->retrieve($this->cacheTitleString), 4);
			$this->cacheData['TITLE'] = $pagetitle;
			$this->cacheData['COMMENT_FLAG'] = $comment_flag;
			$this->cacheData['META_KEYS'] = $meta_keys;
			$this->cacheData['META_DSCR'] = $meta_dscr;
		}
	}
	
	public function setCache($data, $title, $comment_flag)
	{
		$e107cache = e107::getCache();
		$e107cache->set($this->cacheString, $data);
		$e107cache->set($this->cacheTitleString, $title."^^^".$this->page['page_comment_flag']."^^^".$this->page['page_metakeys']."^^^".$this->page['page_metadscr']);
	}

	
	public function renderCache()
	{
		$comments = '';
		if($this->cacheData['COMMENT_FLAG'])
		{
			$vars = new e_vars(array('comments' => $this->pageComment(true)));
			$comments = e107::getScBatch('page',null,'cpage')->setVars($vars)->cpagecomments();
		} 
		define('e_PAGETITLE', eHelper::formatMetaTitle($this->cacheData['TITLE']));
		define('META_DESCRIPTION', $this->cacheData['META_DSCR']);
		define('META_KEYWORDS', $this->cacheData['META_KEYS']);
		if($this->debug)
		{
			echo "<b>Reading page from cache</b><br />";
		}
		return str_replace('[[PAGECOMMENTS]]', $comments, $this->cacheData['PAGE']);
	}

	public function showPage()
	{
		
		
		if(null !== $this->cacheData)
		{
			
			return $this->renderCache();
		}
		if(true === $this->authorized)
		{
			
			$vars = $this->batch->getParserVars();
			
			$template = str_replace('{PAGECOMMENTS}', '[[PAGECOMMENTS]]', $this->template['start'].$this->template['body'].$this->template['end']);
			$ret = $this->renderPage($template);

			if(!empty($this->template['page']))
			{
				$ret = str_replace(array('{PAGE}', '{PAGECOMMENTS}'), array($ret, '[[PAGECOMMENTS]]'), $this->template['page']);
			}
			$ret = e107::getParser()->parseTemplate($ret, true, $this->batch);

			if($vars->cachecontrol) $this->setCache($ret, $this->batch->sc_cpagetitle(), $this->page['page_comment_flag']);
			
			return str_replace('[[PAGECOMMENTS]]', $this->batch->cpagecomments(), $ret);
		}
		
		$extend = new e_vars;
		$vars = $this->batch->getParserVars();
		
		// reset batch data
		$this->batch->setVars(null)->setScVar('page', array());
		
		// copy some data
		$extend->title = $vars->title;
		$extend->message = e107::getMessage()->render();
		
		switch ($this->authorized) 
		{
			case 'class':
				$extend->text = LAN_PAGE_6;
				$template = $this->template['start'].$this->template['restricted'].$this->template['end'];
			break;
			
			case 'pw':
				$frm = e107::getForm();
				$extend->caption = LAN_PAGE_8;
				$extend->label = LAN_PAGE_9;
				$extend->password = $frm->password('page_pw');
				$extend->icon = e_IMAGE_ABS.'generic/password.png';
				$extend->submit = $frm->submit('submit_page_pw', LAN_PAGE_10);
				// FIXME - add form open/close e_form methods
				$extend->form_open = '<form method="post" action="'.e_REQUEST_URI.'" id="pwform">';
				$extend->form_close = '</form>';
				$template = $this->template['start'].$this->template['authorize'].$this->template['end'];
			break;
				
			case 'nf':
			default:
				$extend->text = $vars->text;
				$template = $this->template['start'].$this->template['notfound'].$this->template['end'];
			break;
		}
		
		return $this->renderPage($template, $extend);
	}
	
	public function renderPage($template, $vars = null)
	{
		if(null === $vars) 
		{
			$ret = e107::getParser()->parseTemplate($template, true, $this->batch);
			$vars = $this->batch->getParserVars();
		}
		else 
		{
			$ret = e107::getParser()->simpleParse($template, $vars);
		}
		
	//	if(vartrue($this->template['noTableRender'])) //XXX Deprecated - use tablerender $mode instead. eg. cpage-templatename : echo $text;
	//	{
	//		return $ret;
	//	}
		
		$mode = vartrue($this->template['tableRender'], 'cpage-'.$template);
		$title = $vars->title;
		
		return e107::getRender()->tablerender($title, $ret, $mode, true);
	}

	public function parsePage()
	{
		$tp = e107::getParser();
		e107::getBB()->setClass("page");
		
		$this->pageTitles = array();		// Notice removal

		if(preg_match_all("/\[newpage.*?\]/si", $this->pageText, $pt))
		{
			if (substr($this->pageText, 0, 6) == '[html]')
			{	// Need to strip html bbcode from wysiwyg on multi-page docs (handled automatically on single pages)
				if (substr($this->pageText, -7, 7) == '[/html]')
				{
					$this->pageText = substr($this->pageText, 6, -7);
				}
				else
				{
					$this->pageText = substr($this->pageText, 6);
				}
			}
			$pages = preg_split("/\[newpage.*?\]/si", $this->pageText, -1, PREG_SPLIT_NO_EMPTY);
			$this->multipageFlag = TRUE;
		}
		else
		{
			$this->pageToRender = $tp->toHTML($this->pageText, TRUE, 'BODY');
			return;
		}

		foreach($pt[0] as $title)
		{
			$this->pageTitles[] = $title;
		}


		if(!trim($pages[0]))
		{
			$count = 0;
			foreach($pages as $page)
			{
				$pages[$count] = $pages[($count+1)];
				$count++;
			}
			unset($pages[(count($pages)-1)]);
		}

		$pageCount = count($pages);
		$titleCount = count($this->pageTitles);
		/* if the vars above don't match, page 1 has no [newpage] tag, so we need to create one ... */

		if($pageCount != $titleCount)
		{
			array_unshift($this->pageTitles, "[newpage]");
		}

		/* ok, titles now match pages, rename the titles if needed ... */

		$count =0;
		foreach($this->pageTitles as $title)
		{
			$titlep = preg_replace("/\[newpage=(.*?)\]/", "\\1", $title);
			$this->pageTitles[$count] = ($titlep == "[newpage]" ? LAN_PAGE_13." ".($count+1) : $tp->toHTML($titlep, TRUE, 'TITLE'));
			$count++;
		}

		$this->pageToRender = $tp->toHTML($pages[$this->pageSelected], TRUE, 'BODY');
		$this->title = (substr($this->pageTitles[$this->pageSelected], -1) == ";" ? "" : $this->pageTitles[$this->pageSelected]);

		if($this->debug)
		{
			echo "<b>multipageFlag</b> ".$this->multipageFlag." <br />";
			if($this->multipageFlag)
			{
				echo "<pre>"; print_r($pages); echo "</pre>";
				echo "<b>pageCount</b> ".$pageCount." <br />";
				echo "<b>titleCount</b> ".$titleCount." <br />";
				echo "<pre>"; print_r($this->pageTitles); echo "</pre>";
			}
		}
		e107::getBB()->clearClass();
	}

	function pageIndex()
	{
    	// Use always nextprev shortcode (with a special default 'page' tempalte)
        $titles = implode("|",$this->pageTitles);
        $total_items = count($this->pageTitles);
        //$parms = $total_items.",1,".$this->pageSelected.",".e_SELF."?".$this->pageID.".[FROM],,$titles";
        
		$row = $this->page;
		$row['page'] = '--FROM--';
		$url = rawurlencode(e107::getUrl()->create('page/view', $row, 'allow=page_id,page_title,page_sef,page'));
		
		$parms = 'nonavcount&bullet='.rawurlencode($this->bullet.' ').'&caption=<!-- Empty -->&'.'pagetitle='.rawurlencode($titles).'&tmpl_prefix='.deftrue('PAGE_NEXTPREV_TMPL', 'page').'&total='.$total_items.'&amount=1&current='.$this->pageSelected.'&url='.$url;
        $itext = ($total_items) ? e107::getParser()->parseTemplate("{NEXTPREV={$parms}}") : "";
        
		return $itext;
	}

	// FIXME most probably will fail when cache enabled
	function pageRating($page_rating_flag)
	{
		
		if($page_rating_flag)
		{
			return "<br /><div style='text-align:right'>".e107::getRate()->render("page", $this->pageID,array('label'=>LAN_PAGE_4))."</div>";
			/*
			
						$rate_text = '';      // Notice removal
						
						require_once(e_HANDLER."rate_class.php");
						$rater = new rater;
						$rate_text = "<br /><table style='width:100%'><tr><td style='width:50%'>";
			
						if ($ratearray = $rater->getrating("page", $this->pageID))
						{
							if ($ratearray[2] == "")
							{
								$ratearray[2] = 0;
							}
							$rate_text .= "<img src='".e_IMAGE_ABS."rate/box/box".$ratearray[1].".png' alt='' style='vertical-align:middle;' />\n";
							$rate_text .= "&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
							$rate_text .= ($ratearray[0] == 1 ? "vote" : "votes");
						}
						else
						{
							$rating .= LAN_PAGE_dl_13;
						}
						$rate_text .= "</td><td style='width:50%; text-align:right'>";
			
						if (!$rater->checkrated("page", $this->pageID) && USER)
						{
							$rate_text .= $rater->rateselect("&nbsp;&nbsp;&nbsp;&nbsp; <b>".LAN_PAGE_4."</b>", "page", $this->pageID);
						}
						else if(!USER)
						{
							$rate_text .= "&nbsp;";
						}
						else
						{
							$rate_text .= LAN_PAGE_5;
						}
						$rate_text .= "</td></tr></table>";
						*/
			
		}
		
		
		// return $rate_text;
	}

	function pageComment($page_comment_flag)
	{
		if($page_comment_flag)
		{
			require_once(e_HANDLER."comment_class.php");
			$cobj = new comment;

			if (isset($_POST['commentsubmit']))
			{
				$cobj->enter_comment($_POST['author_name'], $_POST['comment'], "page", $this->pageID, $pid, $_POST['subject']);
				$e107cache = e107::getCache();
				$e107cache->clear("comment.page.".$this->pageID);
				$e107cache->clear($this->cacheString);
			}
            return $cobj->compose_comment("page", "comment", $this->pageID, 0, $this->title, false, true);
		}
	}

	function pageCheckPerms($page_class, $page_password, $page_title="&nbsp;")
	{
		global $ns, $tp, $pref, $HEADER, $FOOTER, $sql;     // $tp added - also $pref - used by footer


		if (!check_class($page_class))
		{
			$this->authorized = 'class';
			return false;
		}

		if (!$page_password)
		{
			$this->authorized = true;
			$cookiename = $this->getCookieName();
			if(isset($_COOKIE[$cookiename])) cookie($cookiename, '', (time() - 2592000));
			return true;
		}

		if(isset($_POST['submit_page_pw']))
		{
			if($_POST['page_pw'] == $page_password)
			{
				$this->setPageCookie();
				$this->authorized = true;
				return true;
			}
			else
			{
				e107::getMessage()->addError(LAN_PAGE_7);
			}
		}
		else
		{
			// TODO - e_COOKIE
			$cookiename = $this->getCookieName();

			if(isset($_COOKIE[$cookiename]) && ($_COOKIE[$cookiename] == md5($page_password.USERID)))
			{
				$this->authorized = true;
				return TRUE;
			}
			// Invalid/empty password here
		}
		
		$this->authorized = 'pw';
		return false;
	}

	function getCookieName()
	{
		return e_COOKIE.'_page_'.$this->pageID;
	}

	function setPageCookie()
	{
		if(!$this->pageID || !vartrue($_POST['page_pw'])) return;
		$pref = e107::getPref();
		
		$pref['pageCookieExpire'] = max($pref['pageCookieExpire'], 120);
		$hash = md5($_POST['page_pw'].USERID);
		
		cookie($this->getCookieName(), $hash, (time() + $pref['pageCookieExpire']));
		//header("location:".e_SELF."?".e_QUERY);
		//exit;
	}
}

?>