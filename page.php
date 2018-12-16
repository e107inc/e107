<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once("class2.php");
e107::coreLan('page');

$e107CorePage = new pageClass(false);

// Important - save request BEFORE any output (header footer) - used in navigation menu
if(!e_QUERY)
{
	$e107CorePage->setRequest('listBooks');
    $e107CorePage->listBooks();

	require_once(HEADERF);

    e107::getRender()->tablerender($e107CorePage->pageOutput['caption'], $e107CorePage->pageOutput['text'], "cpage-full-list");
//	$tmp = $e107CorePage->listPages();
	//$tmp = $e107CorePage->listBooks();
	
//	$text = $tp->parseTemplate("{PAGE_NAVIGATION=book=2}",true);
	/*if(is_array($tmp))
	{
		$ns->tablerender($tmp['title'], $text, 'cpage-full-list');
	}*/
	
	require_once(FOOTERF);
	exit;
}
elseif(vartrue($_GET['bk'])) //  List Chapters within a specific Book
{
	$e107CorePage->setRequest('listChapters');
    $e107CorePage->listChapters($_GET['bk']);
	
	require_once(HEADERF);
    e107::getRender()->tablerender($e107CorePage->pageOutput['caption'], $e107CorePage->pageOutput['text'], 'cpage-chapter-list');
	require_once(FOOTERF);
	exit;	
}
elseif(vartrue($_GET['ch'])) // List Pages within a specific Chapter
{
	$e107CorePage->setRequest('listPages');
    $e107CorePage->listPages($_GET['ch']);

	require_once(HEADERF);
    e107::getRender()->tablerender($e107CorePage->pageOutput['caption'], $e107CorePage->pageOutput['text'], 'cpage-page-list');
	require_once(FOOTERF);
	exit;		
}
else
{
	$e107CorePage->setRequest('showPage');
	$e107CorePage->processViewPage();
    $e107CorePage->setPage();

	require_once(HEADERF);

	$ns = e107::getRender();

	if(!empty($e107CorePage->pageOutput['title']))
	{
		$ns->setContent('title', $e107CorePage->pageOutput['title']);
	}

	$ns->tablerender($e107CorePage->pageOutput['caption'], $e107CorePage->pageOutput['text'], $e107CorePage->pageOutput['mode']);
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
    /**
     * @var cpage_shortcodes
     */
    public $batch;						/* shortcode batch object */
	public $template;					/* current template array */
	protected $authorized;				/* authorized status */
	public $cacheString;				/* current page cache string */
	public $cacheTitleString;			/* current page title and comment flag cache string */
	public $cacheData = null;			/* cache data */
	protected $chapterSef;				/* authorized status */
	protected $chapterParent;
	
	protected $chapterData = array();
	
	protected $displayAllMode = false;	// set to True when no book/chapter/page has been defined by the url/query.

    public $pageOutput = array();   // Output storage - text and caption
    protected $renderMode;          // Page render mode to be used on view page
    protected $templateID = null;
	
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
		else // NEW URLS  /page.php?id=x 
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
		
		$books = e107::getDb()->retrieve("SELECT chapter_id,chapter_sef,chapter_name,chapter_parent FROM #page_chapters ORDER BY chapter_id ASC" , true);
				
		foreach($books as $row)
		{
			$id 							= $row['chapter_id'];
			$this->chapterData[$id]			= $row;
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
		e107::setRegistry('core/page/request', array('action' => $request, 'id' => $id));
	}

	
	
	private function getSef($chapter)
	{
		return vartrue($this->chapterData[$chapter]['chapter_sef'],'--sef-not-assigned--');		
	}
	
	private function getParent($chapter)
	{
		return varset($this->chapterData[$chapter]['chapter_parent'], false);			
	}
	
	private function getName($chapter)
	{
		return varset($this->chapterData[$chapter]['chapter_name'], false);			
	}

	private function getDescription($chapter)
	{
		return varset($this->chapterData[$chapter]['chapter_meta_description'], false);			
	}
	
	private function getIcon($chapter)
	{
		return varset($this->chapterData[$chapter]['chapter_icon'], false);			
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
		
		$this->displayAllMode = true;
		
		$text = "";
		
		
		if(e107::getPref('listBooks',false) && $sql->select("page_chapters", "*", "chapter_parent ='0' AND chapter_visibility IN (".USERCLASS_LIST.") ORDER BY chapter_order ASC "))
		{
			$layout 	= e107::getPref('listBooksTemplate','default'); 		
			$tml 		= e107::getCoreTemplate('chapter','', true, true); // always merge	
			$tmpl 		= varset($tml[$layout]);
			$template 	= $tmpl['listBooks'];
			
			$text = $template['start'];
			
			
			
			while($row = $sql->fetch())
			{
				
				$sef = $row;
				$sef['book_sef'] = $this->getSef($row['chapter_id']);
				$sef['page_sef'] = $this->getSef($row['chapter_id']);
				
				$listChapters = $this->listChapters(intval($row['chapter_id']), $row['chapter_sef']);
				
				$var = array(
					'BOOK_NAME' 		=> $tp->toHtml($row['chapter_name']),
					'BOOK_ANCHOR'		=> $frm->name2id($row['chapter_name']),
					'BOOK_ICON'			=> $this->chapterIcon($row['chapter_icon']),
					'BOOK_DESCRIPTION'	=> $tp->toHtml($row['chapter_meta_description'],true,'BODY'),
					'CHAPTERS'			=> $listChapters['text'],
					'BOOK_URL'			=> e107::getUrl()->create('page/book/index', $sef,'allow=chapter_id,chapter_sef,book_sef,page_sef') 
				);
			
				$text .= $tp->simpleParse($template['item'],$var);
			}			
		}	
		
		if(e107::getPref('listPages',false))
		{
			$text .= "<h3>".LAN_PAGE_14."</h3>"; // Book Title.
			$tmp = $this->listPages(0);
			$text .= $tmp['text'];	// Pages unassigned to Book/Chapters. 
		} //
		
		if($text)
		{
			$caption = isset($template['caption']) ? $template['caption'] : LAN_PAGE_15;
            $this->pageOutput = array('caption'=>$caption, 'text'=>$text);
			//e107::getRender()->tablerender($caption, $text, "cpage_list");
		}
		else
		{
            $this->pageOutput = array('caption'=>LAN_ERROR, 'text'=>LAN_PAGE_1);
			//message_handler("MESSAGE", LAN_PAGE_1);
			//require_once(FOOTERF); // prevent message from showing twice and still listing chapters
			//exit;
		}
		
		
		
		
	}


	/**
	 * Parse the Book/Chapter "listChapters' template
	 * @param int $book
	 * @return array
	 */
	function listChapters($book=1)
	{
		$sql = e107::getDb('chap');
		$tp = e107::getParser();
		$frm = e107::getForm();
		
		// retrieve book information. 
		if(!$brow = $sql->retrieve('page_chapters','chapter_name,chapter_template,chapter_meta_description','chapter_id = '.intval($book).' AND chapter_visibility IN ('.USERCLASS_LIST.') LIMIT 1'))
		{
			$layout = 'default';
		}
		else
		{
			$layout = $brow['chapter_template'];
		}
		
		if($this->displayAllMode === true)
		{
			$layout = e107::getPref('listBooksTemplate');	
		}
		
	
		
		$tml = e107::getCoreTemplate('chapter','', true, true); // always merge	
		
		$error = array('listChapters' => array('start'=>"Chapter template not found: ".$layout));
		$tmpl = varset($tml[$layout],$error );
		
		$template = $tmpl['listChapters'];
		
		$bvar = array(
				'BOOK_NAME' 		=> $tp->toHtml($brow['chapter_name']),
				'BOOK_ANCHOR'		=> $frm->name2id($brow['chapter_name']),
				'BOOK_ICON'			=> $this->chapterIcon($brow['chapter_icon']),
				'BOOK_DESCRIPTION'	=> $tp->toHtml($brow['chapter_meta_description'],true,'BODY'),
			);
		
		$caption = $tp->simpleParse($template['caption'],$bvar);

        if($brow)
        {
            define('e_PAGETITLE', eHelper::formatMetaTitle($brow['chapter_name']));
            if($brow['chapter_meta_description']) define('META_DESCRIPTION', eHelper::formatMetaDescription($brow['chapter_meta_description']));
            if($brow['chapter_meta_keywords']) define('META_KEYWORDS', eHelper::formatMetaKeys($brow['chapter_meta_keywords']));
        }
		
		
		if($sql->select("page_chapters", "*", "chapter_parent = ".intval($book)."  AND chapter_visibility IN (".USERCLASS_LIST.") ORDER BY chapter_order ASC "))
		{
			$text = $tp->simpleParse($template['start'],$bvar);
			
			while($row = $sql->fetch())
			{
				$tmp = $this->listPages(intval($row['chapter_id']));
				
				$row['book_sef'] 			= $this->getSef($row['chapter_parent']); 
				$row['book_name'] 			= $this->getName($row['chapter_parent']);
				$row['book_icon']			= $this->getIcon($row['chapter_parent']);
				$row['book_description']	= $this->getDescription($row['chapter_parent']);
							
				$var = array(
					'BOOK_NAME' 		=> $tp->toHtml($row['book_name']),
					'BOOK_ANCHOR'		=> $frm->name2id($row['book_name']),
					'BOOK_ICON'			=> $this->chapterIcon($row['book_icon']),
					'BOOK_DESCRIPTION'	=> $tp->toHtml($row['book_description'],true,'BODY'),
					
					'CHAPTER_NAME' 			=> $tp->toHtml($row['chapter_name']),
					'CHAPTER_ANCHOR'		=> $frm->name2id($row['chapter_name']),
					'CHAPTER_ICON'			=> $this->chapterIcon($row['chapter_icon']),
					'CHAPTER_DESCRIPTION'	=> $tp->toHtml($row['chapter_meta_description'],true,'BODY'),
					'PAGES'					=> $tmp['text'],
					'CHAPTER_URL'			=> e107::getUrl()->create('page/chapter/index', $row,'allow=chapter_id,chapter_sef,book_sef') 
				);
				
				$text .= $tp->simpleParse($template['item'],$var);

			}
			
			$text .= $tp->simpleParse($template['end'], $bvar);		
			
		}
		else
		{
			$text = e107::getMessage()->addInfo(LAN_PAGE_16)->render();
		}	
			
		#return array('caption'=>$caption, 'text'=>$text);
		$this->pageOutput = array('caption'=>$caption, 'text'=>$text);
        return $this->pageOutput;
	}


	/**
	 * Handle Chapter Icon Glyphs.
	 * @param $icon
	 * @return null|string
	 */
	private function chapterIcon($icon)
	{
		$tp = e107::getParser();
			
		if(!vartrue($icon))
		{
			return null;
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
		$frm 			= e107::getForm();

		// retrieve the template to use for this chapter. 
		$row = $sql->retrieve('page_chapters','chapter_id,chapter_icon,chapter_name,chapter_parent, chapter_meta_description,chapter_template','chapter_id = '.intval($chapt).' LIMIT 1');
		
		if($this->displayAllMode === true)
		{
			$layout = e107::getPref('listBooksTemplate');	
		}
		else 
		{
			$layout = vartrue($row['chapter_template'],'default');
		}

        if($row)
        {
            define('e_PAGETITLE', eHelper::formatMetaTitle($row['chapter_name']));
            if($row['chapter_meta_description']) define('META_DESCRIPTION', eHelper::formatMetaDescription($row['chapter_meta_description']));
            if($row['chapter_meta_keywords']) define('META_KEYWORDS', eHelper::formatMetaKeys($row['chapter_meta_keywords']));
        }
		
		//$bookId = $row['chapter_parent'];
		$bookSef = $this->getSef($row['chapter_parent']);
		$bookTitle = $this->getName($row['chapter_parent']);

		$urlData = array(
			'chapter_id' 	=> $row['chapter_id'],
			'chapter_name'	=> $tp->toHtml($row['chapter_name']),
			'chapter_sef'	=> $bookSef,
			'book_sef'		=> $bookSef,
			'page_sef'		=> '',
			'book_id'       => $row['chapter_parent']
		);
		
		
		//print_a($this->chapterData);
		
		$tml = e107::getCoreTemplate('chapter','', true, true); // always merge	
		$tmpl = varset($tml[$layout]);
		
		$bread = array(
			0 => array('text' => $tp->toHtml($bookTitle), 'url'=> e107::getUrl()->create('page/book/index', $urlData,'allow=chapter_id,chapter_sef,book_id,book_sef,page_sef'))
		);
	
		$var = array(
					'CHAPTER_NAME' 			=> $tp->toHtml($row['chapter_name']),
					'CHAPTER_ANCHOR'		=> $frm->name2id($row['chapter_name']),
					'CHAPTER_ICON'			=> $this->chapterIcon($row['chapter_icon']),
					'CHAPTER_DESCRIPTION'	=> $tp->toHtml($row['chapter_meta_description'], true,'BODY'),
					'CHAPTER_BREADCRUMB'	=> !empty($_GET['ch']) ? $frm->breadcrumb($bread) : ''
		);		
	
		
	//	$tmpl = e107::getCoreTemplate('chapter','docs', true, true); // always merge	
			$template = $tmpl['listPages'];
		
			$pageOnly = ($layout == 'panel') ? " menu_class IN (".USERCLASS_LIST.") " : "page_title !='' AND page_class IN (".USERCLASS_LIST.")  "; // When in 'panel' mode, allow Menus to be rendered while checking menu_class. 
		
			if(!$count = $sql->select("page", "*", $pageOnly."  AND page_chapter=".intval($chapt)." ORDER BY page_order ASC "))
			{
				return array('text' => "<em>".(LAN_PAGE_2)."</em>");
			//	$text = "<ul class='page-pages-list page-pages-none'><li>".LAN_PAGE_2."</li></ul>";
			}
			else
			{
				
				$pageArray = $sql->db_getList();

				$header = $tp->simpleParse($template['start'],$var);
				$text = $tp->parseTemplate($header,true); // for parsing {SETIMAGE} etc.
				
				foreach($pageArray as $page)
				{
					/*$data = array(
						'title' => $page['page_title'],
						'text'	=> $tp->toHtml($page['page_text'],true)
					);*/
					$page['chapter_id']     = $page['page_chapter'];
					$page['chapter_name']   =  $this->getName($page['page_chapter']);
					$page['chapter_parent'] = $this->getParent($page['page_chapter']);
					$page['chapter_sef'] = $this->getSef($page['page_chapter']); // $chapter_sef;

					$page['book_id']    = $page['chapter_parent'];
					$page['book_name']  =  $this->getName($page['chapter_parent']);
					$page['book_sef'] = $bookSef;
					
				//	$this->page = $page;
					$this->batch->setVars($page);
				//	$this->batch->setVars(new e_vars($data))->setScVar('page', $this->page);


					

				//	$url = e107::getUrl()->create('page/view', $page, 'allow=page_id,page_sef,chapter_sef,book_sef');
					// $text .= "<li><a href='".$url."'>".$tp->toHtml($page['page_title'])."</a></li>"; 
					$text .= e107::getParser()->parseTemplate($template['item'], true, $this->batch);
				}
				
				$text .= $tp->simpleParse($template['end'], $var);
				
		
			//	$caption = ($title !='')? $title: LAN_PAGE_11;
			//	e107::getRender()->tablerender($caption, $text,"cpage_list");
			}



			$caption = $tp->simpleParse($template['caption'], $var);
		#return array('caption'=>$caption, 'text'=> $text);
		$this->pageOutput = array('caption'=>$caption, 'text'=> $text);
        return $this->pageOutput;
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
		 	header("HTTP/1.0 404 Not Found");
		 //	exit; 
			/*
			
			$ret['title'] = LAN_PAGE_12;			// ***** CHANGED
			$ret['sub_title'] = '';
			$ret['text'] = LAN_PAGE_3;
			$ret['comments'] = '';
			$ret['rating'] = '';
			$ret['np'] = '';
			$ret['err'] = TRUE;
			$ret['cachecontrol'] = false;
			*/
			
			// ---------- New (to replace values above) ----
			
			$this->page['page_title'] = LAN_PAGE_12;			// ***** CHANGED
			$this->page['sub_title'] = '';
			$this->page['page_text'] = LAN_PAGE_3;
			$this->page['comments'] = '';
			$this->page['rating'] = '';
			$this->page['np'] = '';
			$this->page['err'] = TRUE;
			$this->page['cachecontrol'] = false;

			
			// -------------------------------------
			
			$this->authorized = 'nf';
			$this->template = e107::getCoreTemplate('page', 'default');
		//	$this->batch = e107::getScBatch('page',null,'cpage')->setVars(new e_vars($ret))->setScVar('page', array()); ///Upgraded to setVars() array. (not using '$this->page')





			$this->batch = e107::getScBatch('page',null,'cpage')->setVars($this->page)->wrapper('page/'.$this->templateID);

			
			
			define("e_PAGETITLE", $this->page['page_title']);
			
			return;
		}

		$this->page = $sql->fetch();



		// setting override to true breaks default.

		$this->templateID = vartrue($this->page['page_template'], 'default');

		$this->template = e107::getCoreTemplate('page', $this->templateID, true, true);
		
		if(!$this->template)
		{
			// switch to default
			$this->template = e107::getCoreTemplate('page', 'default', false, false);
			$this->templateID = 'default';
		}

		if(empty($this->template))
		{
			 $this->template = e107::getCoreTemplate('page', 'default');
			 $this->templateID = 'default';
		}

		$editable = array(
				'table' => 'page',
				'pid'   => 'page_id',
				'perms' => '5',
				'shortcodes' => array(
					'cpagetitle' => array('field'=>'page_subtitle','type'=>'text', 'container'=>'span'),
					'cpagebody' => array('field'=>'page_text','type'=>'html', 'container'=>'div'),
				)
		);


		$this->batch = e107::getScBatch('page',null,'cpage');
		$this->batch->wrapper('page/'.$this->templateID );
		$this->batch->editable($editable);

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
		/*
		$ret['title'] = $this->page['page_title'];
		$ret['sub_title'] = $this->title;
        $ret['text'] = $this->pageToRender;
		$ret['np'] = $pagenav;
		$ret['rating'] = $rating;
		$ret['comments'] = $comments;
		$ret['err'] = FALSE;
		$ret['cachecontrol'] = (isset($this->page['page_password']) && !$this->page['page_password'] && $this->authorized === true);		// Don't cache password protected pages
		*/
		
	//	$this->batch->setVars(new e_vars($ret))->setScVar('page', $this->page); // Removed in favour of $this->var (cross-compatible with menus and other parts of e107 that use the same shortcodes) 
	
		// ---- New --- -
		$this->page['page_text'] 	    = $this->pageToRender;
		$this->page['np'] 			    = $pagenav;
		$this->page['rating'] 		    = $rating;
		$this->page['comments'] 	    = $comments;
		$this->page['err'] 			    = false;
		$this->page['cachecontrol']     = (isset($this->page['page_password']) && !$this->page['page_password'] && $this->authorized === true);
		$this->page['chapter_id']       = $this->page['page_chapter'];
		$this->page['chapter_name']     = $this->getName($this->page['page_chapter']);
		$this->page['chapter_sef']      = $this->getSef($this->page['page_chapter']);
		$this->page['chapter_parent']   = $this->getParent($this->page['page_chapter']);
		$this->page['book_id']          = $this->page['chapter_parent'];
		$this->page['book_parent']      = $this->getParent($this->page['chapter_parent']);
		$this->page['book_sef']         = $this->getSef($this->page['chapter_parent']);
		$this->page['book_name']        = $this->getName($this->page['chapter_parent']);
		// -----------------

		e107::getEvent()->trigger('user_page_item_viewed',$this->page);

		$this->batch->setVars($this->page);

		
		define('e_PAGETITLE', eHelper::formatMetaTitle($this->page['page_title']));
		if($this->page['page_metadscr']) define('META_DESCRIPTION', eHelper::formatMetaDescription($this->page['page_metadscr']));
		if($this->page['page_metakeys']) define('META_KEYWORDS', eHelper::formatMetaKeys($this->page['page_metakeys']));

		$tp = e107::getParser();

		if($tp->isImage($this->page['menu_image']))
		{
			$mimg = $tp->thumbUrl($this->page['menu_image'],'w=800', false, true);
			e107::meta('og:image',$mimg);
		}

		$images = e107::getBB()->getContent('img',$this->pageText);
		foreach($images as $im)
		{
			$im = $tp->ampEncode($im);
			e107::meta('og:image',($im));
		}

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
            return true;
		}
        return false;
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

	public function setPage()
	{
		if(null !== $this->cacheData)
		{
			return $this->renderCache();
		}
		if(true === $this->authorized)
		{
			
			$vars = $this->batch->getParserVars();
			
			$template = str_replace('{PAGECOMMENTS}', '[[PAGECOMMENTS]]', $this->template['start'].$this->template['body'].$this->template['end']);
			$arr = $this->renderPage($template);

			if(!empty($this->template['page']))
			{
				$ret = str_replace(array('{PAGE}', '{PAGECOMMENTS}'), array($arr['text'], '[[PAGECOMMENTS]]'), $this->template['page']);
			}
			else
			{
				$ret = $arr['text'];
			}

			$ret = e107::getParser()->parseTemplate($ret, true, $this->batch);

			if(is_object($vars) && $vars->cachecontrol) $this->setCache($ret, $this->batch->sc_cpagetitle(), $this->page['page_comment_flag']);
			
			//return str_replace('[[PAGECOMMENTS]]', $this->batch->cpagecomments(), $ret);
            $this->pageOutput = array('text' => str_replace('[[PAGECOMMENTS]]', $this->batch->cpagecomments(), $ret), 'caption'=>$arr['caption'],'mode'=>$arr['mode'], 'title'=>$this->page['page_metadscr']);

            return null;
		}
		
		$extend = new e_vars;
		$vars = new e_vars($this->batch->getParserVars());
		
		// reset batch data
//		$this->batch->setVars(null)->setScVar('page', array());
		
		// copy some data
		$extend->title = $vars->page_title;
		$extend->message = e107::getMessage()->render();
        $tp = e107::getParser();


		switch ($this->authorized) 
		{
			case 'class':
				$extend->text = LAN_PAGE_6;
				$template = $tp->parseTemplate($this->template['start'], true).$this->template['restricted'].$tp->parseTemplate($this->template['end'] ,true);
                $this->renderMode = 'cpage-restricted';
			break;
			
			case 'pw':
				$frm = e107::getForm();
				$extend->caption = LAN_PAGE_8;
				$extend->label = LAN_PASSWORD;
				$extend->password = $frm->password('page_pw','',50,'size=xlarge&required=1');
				$extend->icon = e_IMAGE_ABS.'generic/password.png';
				$extend->submit = $frm->submit('submit_page_pw', LAN_SUBMIT);
				// FIXME - add form open/close e_form methods
				$extend->form_open = '<form method="post" class="form-inline" action="'.e_REQUEST_URI.'" id="pwform">';
				$extend->form_close = '</form>';
				$template = $tp->parseTemplate($this->template['start'], true).$this->template['authorize'].$tp->parseTemplate($this->template['end'] ,true);
                $this->renderMode = 'cpage-authorize';
			break;
				
			case 'nf':
			default:
				$extend->text = $vars->page_text;
                $template = $tp->parseTemplate($this->template['start'], true).$this->template['notfound'].$tp->parseTemplate($this->template['end'] ,true);
                $this->renderMode = 'cpage-notfound';
			break;
		}

		// return $this->renderPage($template, $extend);
		$tmp = $this->renderPage($template, $extend);
        $this->pageOutput = array('text' => $tmp['text'], 'caption'=>$tmp['caption'], 'mode'=>$tmp['mode'], 'title'=>$tmp['title']);
	}
	
	public function renderPage($template, $vars = null)
	{

		if(null === $vars) 
		{
			$ret = e107::getParser()->parseTemplate($template, true, $this->batch);
		}
		else 
		{
			$ret = e107::getParser()->simpleParse($template, $vars);
		}

        if($this->renderMode)
        {
            $mode = $this->renderMode;
        }
		else
        {
            $mode = vartrue($this->template['tableRender'], 'cpage-page-view');
        }

	//	var_dump($this->batch->page_metadescr);


		return array('caption'=>$this->page['page_title'], 'text'=>$ret, 'mode'=>$mode, 'title'=>$this->page['page_metadscr']);

	//	return e107::getRender()->tablerender($this->page['page_title'], $ret, $mode, true); //table style not parsed in hearder yet.
		
	}



	public function parsePage() 
	{
		$tp = e107::getParser();
		e107::getBB()->setClass("page");
		
		$this->pageTitles = array();		// Notice removal

		if(preg_match_all('/\[newpage.*?\]/si', $this->pageText, $pt))
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
			// $this->pageToRender = $tp->toHTML($this->pageText, TRUE, 'BODY');
            // Remove double parsing - it breaks HTML (inserts <br> as [html] is already removed)
			$this->pageToRender = $this->pageText;
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
			
		}
		
		
		// return $rate_text;
	}




	function pageComment($page_comment_flag)
	{
		if($page_comment_flag)
		{
			$cobj = e107::getComment();

			if (isset($_POST['commentsubmit']))
			{
				$cobj->enter_comment($_POST['author_name'], $_POST['comment'], "page", $this->pageID, $pid, $_POST['subject']);
				$e107cache = e107::getCache();
				$e107cache->clear("comment.page.".$this->pageID);
				$e107cache->clear($this->cacheString);
			}

            return $cobj->compose_comment("page", "comment", $this->pageID, 0, $this->page['page_title'], false, true);
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
				e107::getMessage()->addError(LAN_INCORRECT_PASSWORD);
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
