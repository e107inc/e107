<?php
/*
* Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id$
*
* Custom Pages shortcode batch
*/

if (!defined('e107_INIT')) { exit; }

/**
 *	@package    e107
 *	@subpackage	shortcodes
 *	@version 	$Id$
 *
 *	Shortcodes for custom page display
 */



 
class cpage_shortcodes extends e_shortcode
{
	// var $var; // parsed DB values
	private $chapterData = array();
	private $cpageFieldName = null;
	private $breadcrumbComplete = false;

	// Grab all book/chapter data. 
	function __construct()
	{
		parent::__construct();
		
		$books = e107::getDb()->retrieve("SELECT * FROM #page_chapters ORDER BY chapter_id ASC" , true);
				
		foreach($books as $row)
		{
			$id 							= $row['chapter_id'];


			$this->chapterData[$id]			= $row;


		}	



	}

	
	// Set Chapter. // @see chapter_menu.php 
	public function setChapter($id)
	{
		$this->var['page_chapter'] = intval($id);	
	}
	
	// Return data for a specific chapter-id - XXX @SecretR - without $page defined above, this will return nothing. All $this->var need to be replaced with $this->var
	function getChapter()
	{
		
		$id = varset($this->var['page_chapter']);
		
		if(!empty($this->chapterData[$id]['chapter_id']) && $this->chapterData[$id]['chapter_parent'] > 0)
		{
			return $this->chapterData[$id];	
		}
		return false;
	}
	
	
	// Return data for a specific book-id
	function getBook($cid='')
	{
		if(empty($cid))
		{
			$pid = $this->var['page_chapter'];
			$cid = isset($this->chapterData[$pid]['chapter_parent']) ?  $this->chapterData[$pid]['chapter_parent'] : 0;
		}
		
		$row = isset($this->chapterData[$cid]) ? $this->chapterData[$cid] : array();
		
		if(!empty($row['chapter_id']) && $row['chapter_parent'] < 1)
		{
			return $row;	
		}
		
		return false; // not a book.
		
	}
	
	
	
	// ----------------- Shortcodes ---------------------------------------

	function sc_cpagetitle($parm='')
	{
		return e107::getParser()->toHTML($this->var['page_title'], true, 'TITLE');
	}
	
	function sc_cpagesubtitle()
	{
		$subtitle = varset($this->var['page_subtitle']);
		return $subtitle ? e107::getParser()->toHTML($subtitle, true, 'TITLE') : '';
	}


	function sc_cpagebody($parm=null)
	{
		$text = $this->var['page_text'];


		if(varset($parm['strip']) === 'blocks')
		{
			$text = e107::getParser()->stripBlockTags($text);
		}

		if(!empty($parm['raw']))
		{
			return $text;
		}

		return $text ? e107::getParser()->toHTML($text, true, 'BODY') : '';
	}

	function sc_cpageauthor($parm=null)
	{
		$parms = eHelper::scParams($parm);
		$author = '';

		if(!empty($this->var['user_name']))
		{
			$url = e107::getUrl()->create('user/profile/view', array('name' => $this->var['user_name'], 'id' => $this->var['user_id']));
		}
		else
		{
			$url = '';
		}

		if(isset($parms['url']))
		{
			return $url;
		}
		
		if($this->var['page_author'])
		{
			// currently this field used as Real Name, no matter what the db name says
			if(!empty($this->var['user_login']) && !isset($parms['user']))
			{
				$author = $this->var['user_login'];
			}
			elseif(!empty($this->var['user_name']))
			{
				$author = preg_replace('/[^\w\pL\s]+/u', ' ', $this->var['user_name']);
			}
		}
		
		if(empty($author)) return '';
		
		
		
		if(isset($parms['nolink']))
		{
			return $author;
		}
		//TODO title lan
		return '<a class="cpage-author" href="'.$url.'" title="">'.$author.'</a>';
	}

	function sc_cpagedate($parm=null)
	{
		if(empty($parm))
		{
			return e107::getDateConvert()->convert_date($this->var['page_datestamp'], 'long');
		}
		return e107::getDateConvert()->convert_date($this->var['page_datestamp'], $parm);
	}

	function sc_cpageid()
	{
		return $this->var['page_id'];
	}

	function sc_cpageanchor()
	{
		return e107::getForm()->name2id($this->var['page_title']);
	}

	// Not a shortcode really, as it shouldn't be cached at all :/
	function cpagecomments()
	{
		$com 		= $this->var['comments'];
		$comflag 	= $this->var['page_comment_flag'];

		if(e107::getPref('comments_disabled') == 0 && !$comflag)
		{
			if(ADMIN && deftrue('e_DEBUG'))
			{
				if(defined('BOOTSTRAP') && BOOTSTRAP)
				{
					return e107::getMessage()->addDebug(LAN_PAGE_17)->render(); 
				}
				else
				{
					return "<br /><div style='text-align:center'><b>".LAN_PAGE_17."</b></div>";
				}
			}
		}
		
		//if($parm && isset($com[$parm])) return $com[$parm];
		if($comflag)
		{
			return e107::getComment()->parseLayout($com['comment'],$com['comment_form'],$com['moderate']);	
		}
		
	//	return $com['comment'].$com['moderate'].$com['comment_form'];
	}

	/**
	 * @param array $parm
	 * @param string $parm['tmpl'] page|dropdown
	 * @usage {CPAGENAV}
	 * @usage {CPAGENAV: tmpl=dropdown}
	 *
	 * @return string|null
	 */
	function sc_cpagenav($parm=null)
	{
        $titles = implode("|", $this->scVars->pageTitles);
        $total_items = count($this->scVars->pageTitles);

		$row = $this->var;
		$row['page'] = '--FROM--';
		$url = rawurlencode(e107::getUrl()->create('page/view', $row, 'allow=page_id,page_title,page_sef,page'));

		$template = isset($parm['tmpl']) ? (string) $parm['tmpl'] : 'page';

		$parms = 'nonavcount&bullet='.rawurlencode($this->scVars->bullet.' ').'&caption=<!-- Empty -->&'.'pagetitle='.rawurlencode($titles).'&tmpl_prefix='.deftrue('PAGE_NEXTPREV_TMPL', $template).'&total='.$total_items.'&amount=1&current='.$this->scVars->pageSelected.'&url='.$url;
        $itext = ($total_items) ? e107::getParser()->parseTemplate("{NEXTPREV={$parms}}") : null;

		return $itext;
	}
	
	function sc_cpagerating()
	{
		return isset($this->var['rating']) ? $this->var['rating'] : null;
	}
	
	function sc_cpagemessage()
	{
		return e107::getMessage()->render();
	}
	
	function sc_cpagemenu()
	{
		$parm = $this->var['page_id'];
		return e107::getMenu()->renderMenu($parm, true, false, true);			
		
	}

	/**
	 * Auto-thumbnailing now allowed.
	 * New sc parameter standards
	 * Exampes: 
	 * - {CPAGETHUMBNAIL=e_MEDIA/images/someimage.jpg|type=tag&w=200} render link with thumbnail max width 200px
	 * - {CPAGETHUMBNAIL=images/someimage.jpg|w=200} same as above
	 * - {CPAGETHUMBNAIL=images/someimage.jpg|type=src&aw=200&ah=200} return thumb link only, size forced to 200px X 200px (smart thumbnailing close to how Facebook is doing it)
	 * 
	 * @see eHelper::scDualParams()
	 * @see eHelper::scParams()
	 */
	function sc_cpagethumbnail($parm = '')
	{
		$parms = eHelper::scDualParams($parm);
		if(empty($parms[1])) return '';
		
		$tp = e107::getParser();
		$path = rawurldecode($parms[1]);
		
		if(substr($path, 0, 2) === 'e_') $path = str_replace($tp->getUrlConstants('raw'), $tp->getUrlConstants('sc'), $path);
		elseif($path[0] !== '{') $path = '{e_MEDIA}'.$path;
		
		$thumb = $tp->thumbUrl($path);
		$dimensions = $tp->thumbDimensions('double');
		$type = varset($parms[2]['type'], 'tag');

		switch($type)
		{
			case 'src':
				return $thumb;
			break;

			case 'link':
				return '<a href="'.$tp->replaceConstants($path, 'abs').'" class="cpage-image" rel="external image"><img class="cpage-image" src="'.$thumb.'" alt="'.varset($parms[1]['alt']).'" '.$dimensions.' /></a>';
			break;

			case 'tag':
			default:
				return '<img class="cpage-image" src="'.$thumb.'" alt="'.varset($parms[1]['alt']).'" '.$dimensions.' />';
			break;
		}
	}
	
	// For Future Use..
	/*
	function sc_cpageimage($parm = '')
	{
		list($num,$size) = explode("|",$parm);
		if($this->var['page_images'])
		{
			$img = explode(",",$this->var['page_images']);
			
		}	
	}*/

	function sc_cpagelink($parm=null)
	{
		$url = $this->sc_cpageurl();
		
		if($parm == 'href' || !$url)
		{
			return $url;
		}
		return '<a class="cpage" href="'.$url.'">'.$this->sc_cpagetitle().'</a>';
	}
	
  	/**
	 * @param null $parm
	 * @example {CPAGEBUTTON}
	 * @example {CPAGEBUTTON: class=btn large default mb&target=blank}
	 * @return string
	 */
	function sc_cpagebutton($parm=null)
	{
		$tp = e107::getParser();

		$pgClass = intval($this->var['page_class']);

		if(empty($this->var['menu_button_url']) && !check_class($pgClass)) // ignore when custom url used.
		{
			return "<!-- Button Removed: Page check_class() returned false. -->";	
		}

		$url = $this->sc_cpageurl();
		
		if($parm == 'href' || !$url)
		{
			return $url;
		}
		
		if(trim($this->var['page_text']) == '' && empty($this->var['menu_button_url'])) // Hide the button when there is no page content. (avoids templates with and without buttons)
		{
			return "<!-- Button Removed: No page text exists! -->";	
		}

		if(is_string($parm))
		{
			parse_str($parm,$options);
		}
		else
		{
			$options= $parm;
		}
		
		$buttonText = (empty($this->var['menu_button_text'])) ? LAN_READ_MORE : $this->var['menu_button_text'];
		$buttonUrl	= (empty($this->var['menu_button_url'])) ? $url : $tp->replaceConstants($this->var['menu_button_url'], 'abs');
		$buttonTarget = (empty($this->var['menu_button_target'])) ? '' : ' target="'.$this->var['menu_button_target'].'" '; //TODO add pref to admin area.

		$text = vartrue($options['text'], $buttonText);
		$size = vartrue($options['size'], "");

		$inc = ($size) ? " btn-".$size : "";
		
		$class = (!empty($options['class'])) ? $options['class'] : 'cpage btn btn-primary btn-cpage';
    	$buttonTarget = (!empty($options['target'])) ? ' target="'.$options['target'].'" ' : $buttonTarget;

		return '<a class="'.$class.' '.$inc.'" href="'.$buttonUrl.'" '.$buttonTarget.' title="'.$text.'">'.$text.'</a>';
	}	
	
	
	function sc_cmenutitle($parm='')
	{
		$tp 	= e107::getParser(); 
	//
		return $tp->toHTML($this->var['menu_title'], true, 'TITLE');
	}

	function sc_cmenuname($parm='')
	{
		return $this->var['menu_name'];
	}


	function sc_cmenubody($parm='')
	{
		// print_a($this);
		return e107::getParser()->toHTML($this->var['menu_text'], true, 'BODY');
	}

	/**
	 * @param null $parm
	 * @example {CMENUURL}
	 * @return string
	 */
	function sc_cmenuurl($parm=null)
	{
		if(empty($this->var['menu_button_url']))
		{
			return $this->sc_cpageurl();
		}

		return e107::getParser()->replaceConstants($this->var['menu_button_url'], 'abs');
	}
	
	
	function sc_cmenuimage($parm='')
	{
		$tp = e107::getParser();

		if($parm == 'url')
		{
			$img = ($tp->isVideo($this->var['menu_image'])) ? $tp->toVideo($this->var['menu_image'], array('thumb'=>'src')) : $tp->thumbUrl($this->var['menu_image']);
			return $img;
		}

		if($video = $tp->toVideo($this->var['menu_image']))
		{
			return $video;
		}

		return $tp->toImage($this->var['menu_image'], $parm);

	}


	function sc_cmenu_tab_active($parm=null)
	{
		if(!empty($this->var['cmenu_tab_active']))
		{
			return 'active';
		}

		return null;
	}

	function sc_cmenu_button($parm=null)
	{
		return $this->sc_cpagebutton($parm);
	}


	function sc_cmenu_button_text($parm=null)
	{
		if(empty($this->var['menu_button_url']) && empty($this->var['page_text']))
		{
			return null;
		}


		return (empty($this->var['menu_button_text'])) ? LAN_READ_MORE : $this->var['menu_button_text'];
	}

	function sc_cmenu_button_url($parm=null)
	{
		return $this->sc_cmenuurl($parm);
	}

	
	function sc_cmenuicon($parm='')
	{
		if($parm === 'css')
		{
			return str_replace(".glyph", "", $this->var['menu_icon']);
		}

		return e107::getParser()->toIcon($this->var['menu_icon'], array('space'=>' '));
	}		


	function sc_cpageurl()
	{
		$route = ($this->var['page_chapter'] == 0) ? 'page/view/other' : 'page/view';
		$urldata = $this->var;

		if(!empty($this->var['page_chapter']) && isset($this->chapterData[$this->var['page_chapter']]))
		{
			$chapter = $this->chapterData[$this->var['page_chapter']]; 
			$urldata = array_merge($this->var, $chapter);
			$urldata['book_sef'] = $this->chapterData[$chapter['chapter_parent']]['chapter_sef'];
		}

		return e107::getUrl()->create($route, $urldata, array('allow' => 'page_sef,page_id,chapter_sef,book_sef'));
	}
	
	function sc_cpagemetadiz()
	{
  		return $this->var['page_metadscr'];
	}
	
	function sc_cpagesef()
	{
  		return vartrue($this->var['page_sef'],'page-no-sef');
	}	


	
	// -------------------- Book - specific to the current page. -------------------------
	
	function sc_book_id()
	{
		$frm = e107::getForm();

		if($row = $this->getBook())
		{
			return $row['chapter_id'];
		}

	}
		
	function sc_book_name()
	{
		$tp = e107::getParser();

		if(	$row = $this->getBook())
		{
			return $tp->toHTML($row['chapter_name'], false, 'TITLE');
		}

	}
	
	function sc_book_anchor()
	{
		$frm = e107::getForm();

		if($row = $this->getBook())
		{
			return $frm->name2id($row['chapter_name']);
		}

	}
	
	function sc_book_icon()
	{
		$tp = e107::getParser();

		if($row = $this->getBook())
		{
			return $tp->toIcon($row['chapter_icon'], array('space'=>' '));
		}

	}
	
	function sc_book_description()
	{
		$tp = e107::getParser();

		if($row = $this->getBook())
		{
			return $tp->toHTML($row['chapter_meta_description'], true, 'BODY');
		}

	}
	
	function sc_book_url()
	{
		if($row = $this->getBook())
		{
			return e107::getUrl()->create('page/book/index', $row,'allow=chapter_id,chapter_sef,book_sef') ;
		}
	}	
	
	// -------------------- Chapter - specific to the current page. -------------------------
		
	/**
	 * @example {CHAPTER_ID}
	 */	
	function sc_chapter_id()
	{
		if($row = $this->getChapter())
		{
			return $row['chapter_id'];
		}
	}
	
	
		
	/**
	 * @example {CHAPTER_NAME}
	 */		
	function sc_chapter_name()
	{
		if($row = $this->getChapter())
		{
			return e107::getParser()->toHTML($row['chapter_name'], false, 'TITLE');
		}
	}

	/**
	 * Alias for {CHAPTER_NAME}
	 * @example {CHAPTER_TITLE}
	 */
	function sc_chapter_title()
	{
		return $this->sc_chapter_name();
	}


	/**
	 * @example {CHAPTER_ANCHOR}
	 */		
	function sc_chapter_anchor()
	{
		if($row = $this->getChapter())
		{
			return e107::getForm()->name2id($row['chapter_name']);
		}
	}

	/**
	 * @example {CHAPTER_ICON}
	 */		
	function sc_chapter_icon()
	{
		if($row = $this->getChapter())
		{
			return e107::getParser()->toIcon($row['chapter_icon']);
		}

	}

	function sc_chapter_image($parm=null)
	{
		if($row = $this->getChapter())
		{
			return e107::getParser()->toImage($row['chapter_image'],$parm);
		}
	}

	/**
	 * @example {CHAPTER_DESCRIPTION}
	 */		
	function sc_chapter_description()
	{
		if($row = $this->getChapter())
		{
			return e107::getParser()->toHTML($row['chapter_meta_description'], true, 'BODY');
		}
	}

	/**
	 * @example {CHAPTER_URL}
	 */	
	function sc_chapter_url()
	{
		if(!$row = $this->getChapter())
		{
			return null;
		}
		
		if($brow = $this->getBook($row['chapter_parent']))
		{
			$row['book_sef']  = vartrue($brow['chapter_sef'],"no-sef-found"); //$this->getBook();
		
			return e107::getUrl()->create('page/chapter/index', $row,'allow=chapter_id,chapter_sef,book_sef') ;
		}
		
	}
	
	/**
	 * @example {CHAPTER_BUTTON: text=More&size=sm}
	 */
	function sc_chapter_button($options=null)
	{		
		$text = vartrue($options['text'], LAN_READ_MORE);
		$size = vartrue($options['size'], "");
		$inc = ($size) ? " btn-".$size : "";
		$url = $this->sc_chapter_url();
		
		return '<a class="cpage btn btn-primary btn-chapter'.$inc.'" href="'.$url.'">'.$text.'</a>';	
	}


	function breadcrumb()
	{
		$breadcrumb = array();

		$request = e107::getRegistry('core/page/request');
		$action = varset($request['action']);

		$row = $this->getChapter();
		$parent = isset($row['chapter_parent']) ? (int) $row['chapter_parent'] : 0;
		$brow = $this->getBook($parent);

		if(empty($brow['chapter_sef']) || $this->breadcrumbComplete === true || ($action === 'listBooks'))
		{
			//e107::getDebug()->log($this);
			return null;
		}


		$row['book_sef']  = vartrue($brow['chapter_sef'],"no-sef-found"); //$this->getBook();

		$breadcrumb[] = array('text'=> $brow['chapter_name'], 'url'=> e107::getUrl()->create('page/book/index', $brow,'allow=chapter_id,chapter_sef,book_sef,page_sef'));

		if($action !== 'listChapters')
		{
			$breadcrumb[] = array('text'=> $row['chapter_name'], 'url'=> e107::getUrl()->create('page/chapter/index', $row,'allow=chapter_id,chapter_sef,book_sef'));
		}

		if($action === 'showPage')
		{
			$breadcrumb[] = array('text'=> $this->var['page_title']); 
		}

		e107::breadcrumb($breadcrumb);
		$this->breadcrumbComplete = true;
	}




	function sc_chapter_breadcrumb()
	{
		$brow = array();
		
		if($row = $this->getChapter())
		{
			$brow = $this->getBook($row['chapter_parent']);
		}

		if(empty($brow['chapter_sef']))
		{
			return null;
		}

		$breadcrumb = e107::breadcrumb();

		return e107::getForm()->breadcrumb($breadcrumb);

	}




		
	/**
	 * @example {CPAGERELATED: types=news}
	 */	
	function sc_cpagerelated($array=array())
	{
		if(!varset($array['types']))
		{
			$array['types'] = 'page,news';
		}

		$templateID = vartrue($this->var['page_template'],'default');

		$template = e107::getCoreTemplate('page', $templateID);


		return e107::getForm()->renderRelated($array, $this->var['page_metakeys'], array('page'=>$this->var['page_id']), $template['related']);
	}

	function sc_cpagekeywords($parm=null)
	{
		if(empty($this->var['page_metakeys']))
		{
			return null;
		}

		$text = e107::getParser()->toHTML($this->var['page_metakeys'], false, 'SUMMARY');
		return str_replace(',', ', ', $text);
	}
	
	
	function sc_cpageedit($parm=array())
	{

		if(!ADMIN || !getperms('5'))
		{
			return null;
		}

		$tp = e107::getParser();

		if(!empty($parm['modal']))
		{
			$modal =  'e-modal';
			$iframe = '&iframe=1';
		}
		else
		{
			$modal =  '';
		    $iframe = '';
		}

		$icon = deftrue('FONTAWESOME') ? $tp->toGlyph('fa-edit') : "<img src='".e_IMAGE_ABS."/admin_images/edit_16.png' alt='edit' style='border: 0px none; height: 16px; width: 16px;' />";


	    return "<a rel='external'  title=\"".LAN_EDIT."\"  data-modal-caption=\"".LAN_EDIT."\" class='btn btn-default btn-secondary ".$modal."' href='".e_ADMIN_ABS."cpage.php?action=edit&id=".$this->var['page_id'].$iframe."' >".$icon."</a>";
	}


	function sc_cpagefieldtitle($parm=null)
	{

		$this->cpageFieldName = null;

		if(empty($parm['name']) || empty($this->var['page_fields']))
		{
			return null;
		}

		$chap       = $this->var['page_chapter'];
		$key        = $parm['name'];

		$this->cpageFieldName = $key;

		$arr = array('name'=>$parm['name']);
		$value = $this->sc_cpagefield($arr);

		if(empty($value) && !isset($parm['force']))
		{
			return null;
		}


		if(!empty($this->chapterData[$chap]['chapter_fields']) && is_string($this->chapterData[$chap]['chapter_fields']))
		{
			$this->chapterData[$chap]['chapter_fields'] = e107::unserialize($this->chapterData[$chap]['chapter_fields']);
		}


		if(!empty($this->chapterData[$chap]['chapter_fields'][$key]['title']))
		{
			return $this->chapterData[$chap]['chapter_fields'][$key]['title'];
		}

		return null;
	}


	/**
	 * Return raw HTML-usable values from page fields.
	 * @experimental subject to change without notice.
	 * @param null $parm
	 * @return mixed
	 */
	function sc_cpagefield($parm=null)
	{
		$this->cpageFieldName = null;

		if(empty($parm['name']) || empty($this->var['page_fields']))
		{
			return null;
		}

		$this->cpageFieldName = $parm['name'];

		$chap       = $this->var['page_chapter'];
		$fields     = $this->chapterData[$chap]['chapter_fields'];

		return e107::getCustomFields()->loadConfig($fields)->loadData($this->var['page_fields'])->getFieldValue($parm['name'],$parm);


	}

	/**
	 * Return the last custom-page field name used.
	 * @return string|null
	 */
	function sc_cpagefieldname()
	{
		return $this->cpageFieldName;
	}


	/**
	 * @experimental - subject to change without notice. Use at own risk.
	 * @param null $parm
	 * @return string
	 */
	function sc_cpagefields($parm=null)
	{
		$fieldData  = e107::unserialize($this->var['page_fields']);

		if(isset($parm['generate'])) // use to generate all fields for use in template file.
		{
			$text = '<pre>';

			foreach($fieldData as $ok=>$v)
			{

				$text .= "&#123;CPAGEFIELDTITLE: name=".$ok."&#125;\n";
				$text .= "&#123;CPAGEFIELD: name=".$ok."&#125;\n";
			}

			$text .= "</pre>";

			return $text;
		}



		$text = '<table class="table table-bordered table-striped">
		<tr><th>Name</th><th>Title<br /><small>&#123;CPAGEFIELDTITLE: name=x&#125;</small></th><th>Normal<br /><small>&#123;CPAGEFIELD: name=x&#125;</small></th><th>Raw<br /><small>&#123;CPAGEFIELD: name=x&mode=raw&#125;</small></th></tr>';

		foreach($fieldData as $ok=>$v)
		{

			$text .= "<tr><td>".$ok."</td><td>".$this->sc_cpagefieldtitle(array('name'=>$ok))."</td><td>".$this->sc_cpagefield(array('name'=>$ok))."</td><td>".$this->sc_cpagefield(array('name'=>$ok, 'mode'=>'raw'))."</td></tr>";
		}

		$text .= "</table>";

		return $text;

	}



}
