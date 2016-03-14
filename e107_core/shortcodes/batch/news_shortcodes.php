<?php
/*
 * Copyright (C) 2008-2013 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 *
 * News shortcode batch
*/

/**
 *	@package    e107
 *	@subpackage	shortcodes
 *
 *	Shortcodes for news item display
 */

if (!defined('e107_INIT')) { exit; }

/* DEPRECATED
register_shortcode('news_shortcodes', TRUE);
initShortcodeClass('news_shortcodes');
*/

// Done via e107::getScBatch('news'); call - see news_class.php
/*e107::getScParser()->registerShortcode('news_shortcodes', true)
	->initShortcodeClass('news_shortcodes');*/

class news_shortcodes extends e_shortcode
{
	//protected $news_item; - shouldn't be set - see __set/__get methods of e_shortcode & news::render_newsitem()
	protected $e107;
	//protected $param;  - shouldn't be set - see __set/__get methods of e_shortcode & news::render_newsitem()

	protected $commentsDisabled;

	protected $commentsEngine = 'e107';
	
	private $imageItem;
	
	
	function __construct($eVars = null)
	{
		parent::__construct($eVars);
		$this->e107 = e107::getInstance();
		
		$pref = e107::getPref();
		
		$this->commentsDisabled = vartrue($pref['comments_disabled']);

		if(!empty($pref['comments_engine']))
		{
			$this->commentsEngine = $pref['comments_engine'];
		}
	}

	function sc_newstitle($parm)
	{
		$text = e107::getParser()->toHTML($this->news_item['news_title'], TRUE, 'TITLE');

		if(!empty($parm['attribute']))
		{
			$text = e107::getParser()->toAttribute($text);
		}

		return $text;
	}

	function sc_newsurltitle()
	{
		$title = $this->sc_newstitle();
		// FIXME generic parser toAttribute method (currently toAttribute() isn't appropriate)
		return '<a href="'.$this->sc_newsurl().'" title="'.preg_replace('/\'|"|<|>/s', '', $this->news_item['news_title']).'">'.$title.'</a>';
	}
	
	function sc_newsbody($parm=null)
	{
		$tp = e107::getParser();
		e107::getBB()->setClass("news"); // For automatic bbcode image resizing. 
		
		if($parm != 'extended')
		{
			$news_body = $tp->toHTML($this->news_item['news_body'], true, 'BODY, fromadmin', $this->news_item['news_author']);
		}
		
		if($this->news_item['news_extended'] && (isset($_POST['preview']) || $this->param['current_action'] == 'extend') && ($parm != 'noextend' && $parm != 'body'))
		{
			$news_body .= $tp->toHTML($this->news_item['news_extended'], true, 'BODY, fromadmin', $this->news_item['news_author']);
		}
		e107::getBB()->clearClass();
		return $news_body;
	}

	function sc_newsicon($parm=null)
	{

		return $this->sc_newscaticon('url');
	}

	function sc_newsauthor($parm)
	{
		if($this->news_item['user_id'])
		{
			if($parm == 'nolink')
			{
				return $this->news_item['user_name'];
			}
			else
			{
				return "<a href='".e107::getUrl()->create('user/profile/view', $this->news_item)."'>".$this->news_item['user_name']."{$parm}</a>";
			}
		}
		return "<a href='http://e107.org'>e107</a>";
	}

	function sc_newscomments($parm=null)
	{
		
		$pref = e107::getPref();
		$sql = e107::getDb();
		
		if($this->commentsDisabled || ($this->commentsEngine != 'e107'))
		{
			return '';
		}
				
		$news_item = $this->news_item;
		$param = $this->param;

		if($param['current_action'] == 'extend')
		{
			return LAN_COMMENTS.' ('.$news_item['news_comment_total'].')';
		}

		if (vartrue($pref['multilanguage']))
		{	// Can have multilanguage news table, monlingual comment table. If the comment table is multilingual, it'll only count entries in the current language
			$news_item['news_comment_total'] = $sql->count("comments", "(*)", "WHERE comment_item_id='".$news_item['news_id']."' AND comment_type='0' ");
		}

		//XXX - ??? - another query? We should cache it in news table.
		if ($pref['comments_icon'] && $news_item['news_comment_total'])
		{
			$sql->select('comments', 'comment_datestamp', "comment_item_id='".intval($news_item['news_id'])."' AND comment_type='0' ORDER BY comment_datestamp DESC LIMIT 0,1");
			list($comments['comment_datestamp']) = $sql->fetch();
			$latest_comment = $comments['comment_datestamp'];
			if ($latest_comment > USERLV )
			{
				$NEWIMAGE = $param['image_new_small'];
			}
			else
			{
				$NEWIMAGE = $param['image_nonew_small'];
			}
		}
		else
		{
			$NEWIMAGE = $param['image_nonew_small'];
		}
		
		if(deftrue('BOOTSTRAP')) // Should be done with CSS, not like above.
		{
			$NEWIMAGE = "";		
		}
		
		return (!$news_item['news_allow_comments'] ? ''.($pref['comments_icon'] ? $NEWIMAGE.' ' : '')."<a title='Comments' href='".e107::getUrl()->create('news/view/item', $news_item)."'>".$param['commentlink'].intval($news_item['news_comment_total']).'</a>' : vartrue($param['commentoffstring'],'Disabled') );
	}

	function sc_trackback($parm=null)
	{
		global $pref;
		if(!vartrue($pref['trackbackEnabled'])) { return ''; }
		$news_item = $this->news_item;
		$news_item['#'] = 'track';
		
		return ($this->param['trackbackbeforestring'] ? $this->param['trackbackbeforestring'] : '')."<a href='".e107::getUrl()->create('news/view/item', $this->news_item)."'>".$this->param['trackbackstring'].$this->news_item['tb_count'].'</a>'.($this->param['trackbackafterstring'] ? $this->param['trackbackafterstring'] : '');
	}



	/**
	 * Render a news navigation link
	 * @param $parm array
	 * @example {NEWSNAVLINK: list=all} // A list of all items - usually headings and thumbnails
	 * @example {NEWSNAVLINK: list=category} // A list of all items - usually headings and thumbnails from the current category. 
	 * @example {NEWSNAVLINK: items=category}  // News items for current category. 
	 * @example {NEWSNAVLINK: text=myCaption}  // Default News item view. ie. news.php
	 */
	function sc_newsnavlink($parm=null) //TODO add more options.
	{
		
		if(varset($parm['list']) == 'all') // A list of all items - usually headings and thumbnails
		{
			$url = e107::getUrl()->create('news/list/all');
		}
		elseif(varset($parm['list']) == 'category') 
		{
			$url = e107::getUrl()->create('news/list/short', $this->news_item);  //default for now.
		}
		elseif(varset($parm['items']) == 'category') 
		{
			$url = e107::getUrl()->create('news/list/category', $this->news_item); 		
		}
		else
		{
			$url = e107::getUrl()->create('news/list/items'); // default for now. 	
		}
			
			
		$caption = vartrue($parm['text'],LAN_BACK);
		
		$text = '<ul class="pager hidden-print">
  			<li><a href="'.$url.'">'.e107::getParser()->toHtml($caption,false,'defs').'</a></li>
		</ul>';
		
		return $text;
	}




	function sc_newsheader($parm=null)
	{
		return $this->sc_newscaticon('src');
	}


	function sc_news_category_icon($parm=null)
	{
		return $this->sc_newscaticon($parm);
	}


	function sc_news_category_name($parm=null)
	{
		return $this->sc_newscategory($parm);
	}


	function sc_news_category_sef($parm=null)
	{
		if(!empty($this->news_item['category_sef']))
		{
			return strtolower(str_replace(" ","", $this->news_item['category_sef']));
		}
	}


	function sc_news_category_description($parm=null)
	{
		if(!empty($this->news_item['category_meta_description']))
		{
			return e107::getParser()->toHTML($this->news_item['category_meta_description'], false ,'BODY');
		}
	}


	//New v2.x Aliases

	public function sc_news_id($parm=null)
	{
		return $this->sc_newsid();
	}

	public function sc_news_title($parm=null)
	{
		return (!empty($parm['link'])) ? $this->sc_newstitlelink($parm) : $this->sc_newstitle($parm);
	}

	public function sc_news_body($parm=null)
	{
		return $this->sc_newsbody($parm);
	}

	public function sc_news_author($parm=null)
	{
		return $this->sc_newsauthor($parm);
	}

	public function sc_news_summary($parm=null)
	{
		return $this->sc_newssummary($parm);
	}

	public function sc_news_description($parm=null)
	{
		return $this->sc_newsmetadiz($parm);
	}

	public function sc_news_tags($parm=null)
	{
		return $this->sc_newstags($parm);
	}

	public  function sc_news_comment_count($parm=null)
	{
		return $this->sc_newscommentcount($parm);
	}

	public function sc_news_date($parm=null)
	{
		return $this->sc_newsdate($parm);
	}

	public function sc_news_user_avatar($parm=null)
	{
		return $this->sc_newsavatar($parm);
	}

// ----------------------------------- BC compatible Shortcodes ------------------------------------------- //

	function sc_newscategory($parm=null)
	{
		$category_name = e107::getParser()->toHTML($this->news_item['category_name'], FALSE ,'defs');
		$category = array('id' => $this->news_item['category_id'], 'name' => $this->news_item['category_sef'] );
		$categoryClass = varset($GLOBALS['NEWS_CSSMODE'],'');
		return "<a class='".$categoryClass."_category' style='".(isset($this->param['catlink']) ? $this->param['catlink'] : "#")."' href='".e107::getUrl()->create('news/list/category', $category)."'>".$category_name."</a>";
	}

	function sc_newsdate($parm)
	{
	   $date = ($this->news_item['news_start'] > 0) ? $this->news_item['news_start'] : $this->news_item['news_datestamp'];
		$con = e107::getDate();
		if($parm == '')
		{
			return  $con->convert_date($date, 'long');
		}
		switch($parm)
		{
			case 'long':
			return  $con->convert_date($date, 'long');
			break;
			case 'short':
			return  $con->convert_date($date, 'short');
			break;
			case 'forum':
			return  $con->convert_date($date, 'forum');
			break;
			default :
			return date($parm, $date);
			break;
		}
	}


	function sc_newsavatar($parm=null)
	{
		return vartrue($this->news_item['user_id']) ? e107::getParser()->parseTemplate("{USER_AVATAR=".$this->news_item['user_id']."}",true) : '';
	} 

	/**
	 * {NEWSCOMMENTLINK: glyph=comments&class=btn btn-default btn-sm}
	 *
	 */
	function sc_newscommentlink($parm=null)
	{

		if($this->commentsDisabled || ($this->commentsEngine != 'e107'))
		{
			return;	
		}

		$class = varset($parm['class']) ? " ".$parm['class'] : "";

		// When news_allow_comments = 1 then it is disabled. Backward, but that's how it is in v1.x
		$text = ($this->news_item['news_allow_comments'] ? $this->param['commentoffstring'] : "<a title='".$this->sc_newscommentcount()." comments' class='e-tip".$class."' href='".e107::getUrl()->create('news/view/item', $this->news_item)."'>".$this->param['commentlink'].'</a>');
		return $text;
	}

	/**
	 * {NEWSCOMMENTCOUNT: glyph=x}
	 */
	function sc_newscommentcount($parm=null)
	{
		if($this->commentsDisabled || ($this->commentsEngine != 'e107'))
		{
			return;
		}
		
		$text = varset($parm['glyph']) ? e107::getParser()->toGlyph($parm['glyph']) : "";
		$text .=  $this->news_item['news_comment_total'];
		return $text;
	}

	/**
	 * {EMAILICON: class=x}
	 */
	function sc_emailicon($parm=array())
	{
		$pref = e107::getPref();
		if (!check_class(varset($pref['email_item_class'],e_UC_MEMBER)))
		{
			return '';
		}
		require_once(e_HANDLER.'emailprint_class.php');
		return emailprint::render_emailprint('news', $this->news_item['news_id'], 1, $parm);
	}

	/**
	 * {PRINTICON: class=x}
	 */
	function sc_printicon($parm=array())
	{
		require_once(e_HANDLER.'emailprint_class.php');
		return emailprint::render_emailprint('news', $this->news_item['news_id'], 2, $parm);
	}

	function sc_pdficon()
	{
		$pref = e107::getPref();
		if (!varset($pref['plug_installed']['pdf'])) { return ''; }
		return e107::getParser()->parseTemplate('{PDF='.LAN_NEWS_24.'^news.'.$this->news_item['news_id'].'}');
	}

	function sc_newsid()
	{
		return $this->news_item['news_id'];
	}

	/**
	 * {ADMINOPTIONS: class=x}
	 */
	function sc_adminoptions($parm=array())
	{
		$tp = e107::getParser();
		if (ADMIN && getperms('H'))
		{
			//TODO - discuss - a pref for 'new browser window' loading, or a parm or leave 'new browser window' as default?
			$default = (deftrue('BOOTSTRAP')) ? $tp->toGlyph('icon-edit',false) :  "<img src='".e_IMAGE_ABS."admin_images/edit_16.png' alt=\"".LAN_EDIT."\" class='icon' />";

			
			$adop_icon = (file_exists(THEME."images/newsedit.png") ? "<img src='".THEME_ABS."images/newsedit.png' alt=\"".LAN_EDIT."\" class='icon' />" : $default);
			
			$class = varset($parm['class']);
			
			return "<a class='e-tip ".$class." hidden-print' rel='external' href='".e_ADMIN_ABS."newspost.php?action=edit&amp;id=".$this->news_item['news_id']."' title=\"".LAN_EDIT."\">".$adop_icon."</a>\n";
		}
		else
		{
			return '';
		}
	}

	function sc_extended($parm='')
	{

		$class = vartrue($parm['class']) ? "class='".$parm['class']."'" : '';

		if ($this->news_item['news_extended'] && ($this->param['current_action'] != 'extend' || $parm == 'force'))
		{
			$es = (defined('EXTENDEDSTRING')) ? EXTENDEDSTRING : LAN_MORE;
			$es1 = (defined('PRE_EXTENDEDSTRING')) ? PRE_EXTENDEDSTRING : '';
			$es2 = (defined('POST_EXTENDEDSTRING')) ? POST_EXTENDEDSTRING : '';
			
			if (deftrue('ADMIN_AREA') && isset($_POST['preview']))
			{
				return $es1.$es.$es2."<br />".$this->news_item['news_extended'];
			}
			else
			{
				return $es1."<a {$class} href='".e107::getUrl()->create('news/view/item', $this->news_item)."'>".$es."</a>".$es2;
			}
		}
		return '';
	}

	function sc_captionclass()
	{
		$news_title = e107::getParser()->toHTML($this->news_item['news_title'], TRUE,'TITLE');
		return "<div class='category".$this->news_item['news_category']."'>".($this->news_item['news_render_type'] == 1 ? "<a href='".e107::getUrl()->create('news/view/item', $this->news_item)."'>".$news_title."</a>" : $news_title)."</div>";
	}

	function sc_admincaption()
	{
		$news_title = e107::getParser()->toHTML($this->news_item['news_title'], TRUE,'TITLE');
		return "<div class='".(defined('ADMINNAME') ? ADMINNAME : "null")."'>".($this->news_item['news_render_type'] == 1 ? "<a href='".e107::getUrl()->create('news/view/item', $this->news_item)."'>".$news_title."</a>" : $news_title)."</div>";
	}

	function sc_adminbody($parm)
	{
		$news_body = $this->sc_newsbody($parm);
		return "<div class='".(defined('ADMINNAME') ? ADMINNAME : 'null')."'>".$news_body.'</div>';
	}

	function sc_newssummary($parm=null)
	{
		if($this->news_item['news_summary'])
		{
			return $this->news_item['news_summary'].'<br />';
		}
		
		if($this->news_item['news_body']) // Auto-generate from first 2 sentences of news-body. //TODO Add Pref?
		{
			$tp = e107::getParser();
			$text = $tp->toHtml($this->news_item['news_body'],true);
			$text = str_replace("<br />","\n",$text);
			$text = strip_tags($text);	
			$tmp = preg_split('/(\.\s|!|\r|\n|\?)/i', trim($text));	
			$tmp = array_filter($tmp);
			
			if($tmp[0])
			{
				return trim($tmp[0]);	
			}
		}
	}

	/**
	 * Auto-thumbnailing now allowed.
	 * New sc parameter standards
	 * Exampes: 
	 * - {NEWSTHUMBNAIL=link|w=200} render link with thumbnail max width 200px
	 * - {NEWSTHUMBNAIL=|w=200} same as above
	 * - {NEWSTHUMBNAIL=src|aw=200&ah=200} return thumb link only, size forced to 200px X 200px (smart thumbnailing close to how Facebook is doing it)
	 * 
	 * First parameter values: link|src|tag
	 * Second parameter format: aw|w=xxx&ah|ah=xxx
	 * 
	 * @see eHelper::scDualParams()
	 * @see eHelper::scParams()
	 * XXX Also returns Video thumbnails. 
	 */
	function sc_newsthumbnail($parm = '') //TODO Add support {NEWSTHUMBNAIL: x=y} format 
	{
		$tmp = $this->handleMultiple($parm,'all');
		$newsThumb = $tmp['file'];
		
		$class = 'news-thumbnail-'.$tmp['count'];
		$dimensions = null;
		$srcset = null;
		$tp = e107::getParser();
		
		if(!$newsThumb && $parm != 'placeholder')
		{
			return '';
		}
		
		if($vThumb = e107::getParser()->toVideo($newsThumb, array('thumb'=>'src')))
		{
			$src = $vThumb;
			$_src = '#';
		}
		else
		{
			$parms = eHelper::scDualParams($parm);
			
			if(empty($parms[2])) // get {SETIMAGE} values when no parm provided. 
			{
				$parms[2] = array('aw' => $tp->thumbWidth(), 'ah'=> $tp->thumbHeight());
			}
			
			
			if(isset($parms[2]['legacy']) && $parms[2]['legacy']==true) // Legacy mode - swap out thumbnails for actual images and update paths.  
			{
				if($newsThumb[0] != '{') // Fix old paths. 
				{
					$newsThumb = '{e_IMAGE}newspost_images/'.$newsThumb;	
				}
				
				$tmp = str_replace('newspost_images/thumb_', 'newspost_images/', $newsThumb); // swap out thumb for image. 
				
				if(is_readable(e_IMAGE.$tmp))
				{
					$newsThumb = $tmp;	
				}
				
				unset($parms[2]);
			}
			
			// We store SC path in DB now + BC
			$_src = $src = ($newsThumb[0] == '{' || $parms[1] == 'placeholder') ? e107::getParser()->replaceConstants($newsThumb, 'abs') : e_IMAGE_ABS."newspost_images/".$newsThumb;
		
		
			if($parms[2] || $parms[1] == 'placeholder')
			{
				//  $srcset = "srcset='".$tp->thumbSrcSet($src,'all')."' size='100vw' ";
				  $src = e107::getParser()->thumbUrl($src, $parms[2]);
				  $dimensions = e107::getParser()->thumbDimensions();

			}
		}
		
		
		
				
		switch($parms[1])
		{
			case 'src':
				return $src;
			break;

			case 'tag':
				return "<img class='news_image ".$class."' src='".$src."' alt='' style='".$this->param['thumbnail']."' {$dimensions} {$srcset} />";
			break;

			case 'img':
				return "<a href='".$_src."' rel='external image'><img class='news_image ".$class."' src='".$src."' alt='' style='".$this->param['thumbnail']."' {$dimensions} {$srcset} /></a>";
			break;

			default:
				return "<a href='".e107::getUrl()->create('news/view/item', $this->news_item)."'><img class='news_image img-responsive img-rounded ".$class."' src='".$src."' alt='' style='".$this->param['thumbnail']."' {$dimensions} {$srcset} /></a>";
			break;
		}
	}



	/**
	 * XXX  Experimental Images/Video - supports multiple items
	 * {NEWSMEDIA: item=1}
	 */
	function sc_newsmedia($parm=array())
	{
		
		$media = explode(",", $this->news_item['news_thumbnail']);

		if(empty($parm['item']))
		{
			$parm['item'] = 0;	
		}
		else 
		{
			$parm['item'] = ($parm['item'] -1);
		}
			
		$this->imageItem = varset($media[$parm['item']]); // Set the current Image for other image shortcodes. 
		
		if(vartrue($parm['placeholder']))
		{
			return $this->sc_newsimage('placeholder');	
		}
		else
		{
			return $this->sc_newsimage();	
		}
			
		
	}


	function sc_newsvideo($parm='')
	{
		$tmp = $this->handleMultiple($parm,'video');	
		$file = $tmp['file'];	
			
		if($video = e107::getParser()->toVideo($file, array('class'=> 'news-video-'.$tmp['count'])))
		{
			return $video;
		}
				
	}


	function handleMultiple($parm,$type='image')
	{
		if(empty($this->news_item['news_thumbnail']))
		{
			return;	
		}			
		
		$tp = e107::getParser();
	
		$media = explode(",", $this->news_item['news_thumbnail']);
		$list = array();
		
		foreach($media as $file)
		{
			if($tp->isVideo($file))
			{
				$list['video'][] = $file;	
			}
			else
			{
				$list['image'][] = $file;		
			}	
			
			$list['all'][] = $file;	
		}
		

		if(is_string($parm) || empty($parm['item']))
		{
			$item = 0;	
			$parm = array('item' => 1);
		}
		else 
		{
			$item = ($parm['item'] -1);
		}			
				
			
		$file = varset($list[$type][$item]);
		$count = varset($parm['item'],1);
		
		return array('file'=>$file, 'count'=> $count);		
		
	}
	
	
				
			
		
		

	/**
	 * Display News Images (but not video thumbnails )
	 * @param $parm array
	 * @example {NEWSIMAGE: type=src&placeholder=true}
	 */
	function sc_newsimage($parm = null)
	{
		$tp = e107::getParser();
		
		if(is_string($parm))
		{
			$parm = array('type'=> $parm);
		}
			
		$tmp = $this->handleMultiple($parm);
		$srcPath = $tmp['file'];
		
		$class = "news_image news-image img-responsive img-rounded";
		$class .= ' news-image-'.$tmp['count'];
		$dimensions = null;
		$srcset = null;
			
		if($tp->isVideo($srcPath))
		{
			return; 
		}
		else 
		{
		
			if(!$srcPath)
			{
				if(varset($parm['type']) == 'placeholder' || vartrue($parm['placeholder']))
				{
					$src = 	$tp->thumbUrl(); // placeholder;
					$dimensions = $tp->thumbDimensions();
				}
				else
				{
					return;
				}
			}
			elseif($srcPath[0] == '{' ) // Always resize. Use {SETIMAGE: w=x&y=x&crop=0} PRIOR to calling shortcode to change. 
			{
				$src = $tp->thumbUrl($srcPath);
				$dimensions = $tp->thumbDimensions();

			}
			else
			{
				// We store SC path in DB now + BC
				$src = $srcPath[0] == '{' ? $tp->replaceConstants($srcPath, 'abs') : e_IMAGE_ABS."newspost_images/".$srcPath;			
			}
		}
		
	
		
		if(vartrue($parm['nolegacy'])) // Remove legacy thumbnails. 
		{
			$legSrc = urldecode($src);

		 	if(strpos($legSrc,'newspost_images/thumb_')!==false)
			{
				return;	
			}
		}
		
		if($tmp['count'] > 1 && empty($parm['type'])) // link first image by default, but not others.
		{
			$parm['type'] = 'tag';
		}

		switch(vartrue($parm['type']))
		{
			case 'src':
				return $src;
			break;

			case 'tag':
				return "<img class='{$class}' src='".$src."' alt='' style='".$this->param['thumbnail']."' {$dimensions} {$srcset} />";
			break;

			case 'url':
			default:
				return "<a href='".e107::getUrl()->create('news/view/item', $this->news_item)."'><img class='{$class}' src='".$src."' alt='' style='".$this->param['thumbnail']."' {$dimensions} {$srcset} /></a>";
			break;
		}
	}





	function sc_sticky_icon()
	{
		return $this->news_item['news_sticky'] ? $this->param['image_sticky'] : '';
	}



	function sc_newstitlelink($parm = '')
	{
		parse_str($parm, $parms);
		$url = e107::getUrl()->create('news/view/item', $this->news_item);
		if(isset($parms['href']))
		{
			return $url;
		}
		return "<a style='".(isset($this->param['itemlink']) ? $this->param['itemlink'] : 'null')."' href='{$url}'>".$this->news_item['news_title'].'</a>';
	}

	function sc_newsurl()
	{
		return e107::getUrl()->create('news/view/item', $this->news_item);
	}

	function sc_newscaticon($parm = '')
	{
		// BC
		$category_icon = str_replace('../', '', trim($this->news_item['category_icon']));
		if (!$category_icon) { return ''; }

		// We store SC path in DB now + BC
		if($category_icon[0] == '{')
		{
			$src =  e107::getParser()->replaceConstants($category_icon, 'abs');	
		}
		else
		{
			//Backwards Compatible Link.
			$src =  (is_readable(e_IMAGE_ABS."newspost_images/".$category_icon)) ? e_IMAGE_ABS."newspost_images/".$category_icon : e_IMAGE_ABS."icons/".$category_icon;
		}
		
		$alt_text = e107::getParser()->toHTML($this->news_item['category_name'], FALSE ,'defs');
		//TODO - remove inline styles
		if($this->param['caticon'] == ''){$this->param['caticon'] = 'border:0px';}

		switch($parm)
		{
			case 'src':
				return $src;
			break;

			case 'tag':
				return "<img class='news_image' src='{$src}' alt='$alt_text' style='".$this->param['caticon']."' />";
			break;

			case 'url':
			default:
				return "<a href='".e107::getUrl()->create('news/list/category', $this->news_item)."'><img class='img-rounded' style='".$this->param['caticon']."' src='".$src."' alt='$alt_text' /></a>";
			break;
		}
	}

	/**
	 * Example usage: {NEWSITEM_SCHOOK=mysc_name|my_var1=val1&myvar2=myval2}
	 * will fire {MYSC_NAME=news_id=1&my_var1=val1&myvar2=myval2}
	 * Inside your 'MYSC_NAME' shortcode you are also able to access current item data this way
	 * <code>
	 * $newsdata = e107::getRegistry('core/news/schook_data');
	 * //returns array('data' => (array) $current_news_data, 'params' => array() $current_params)
	 * </code>
	 *
	 * @param string $parm
	 * @return string
	 */
	function sc_newsitem_schook($parm='')
	{
		$parm = explode('|', $parm, 2);
		$parm[1] = 'news_id='.$this->news_item['news_id'].(varset($parm[1]) ? '&'.$parm[1] : '');
		e107::setRegistry('core/news/schook_data', array('data' => $this->news_item, 'params' => $this->param));
		return e107::getParser()->parseTemplate('{'.strtoupper($parm[0]).'='.$parm[1].'}');
	}

	function sc_newsinfo()
	{
		$news_item = $this->news_item;
		$param = $this->param;
		$con = new convert;
		$news_item['news_start'] = (isset($news_item['news_start']) && $news_item['news_start'] ? str_replace(' - 00:00:00', '', $con->convert_date($news_item['news_start'], 'long')) : LAN_NEWS_19);
		$news_item['news_end'] = (isset($news_item['news_end']) && $news_item['news_end'] ? ' to '.str_replace(' - 00:00:00', '', $con->convert_date($news_item['news_end'], 'long')) : '');
		$info = $news_item['news_render_type'] == 1 ? LAN_NEWS_9 : '';
		$info .= $news_item['news_class'] == 255 ? LAN_NEWS_10 : LAN_NEWS_11;
		$info .= $news_item['news_sticky'] ? '<br />'.LAN_NEWS_31 : '';
		$info .= '<br />'.($news_item['news_allow_comments'] ? LAN_NEWS_13 : LAN_NEWS_12);
		$info .= LAN_NEWS_14.$news_item['news_start'].$news_item['news_end'].'<br />';
		$info .= LAN_NEWS_15.strlen($news_item['news_body']).LAN_NEWS_16.strlen($news_item['news_extended']).LAN_NEWS_17."<br /><br />";
		//return $ns->tablerender(LAN_NEWS_18, $info);
		return $info;
	}

	function sc_newstags($parm='')
	{
		$tmp = explode(",",$this->news_item['news_meta_keywords']);
		$words = array();
		
		if($parm == 'label')
		{
			$start = "<span class='label label-default'>";
			$end	= "</span>";	
			$sep = " ";
		}
		else
		{
			$start = "";
			$end = "";
			$sep = ", ";
		}
		
		foreach($tmp as $val)
		{
			if(trim($val))
			{
				$url = e107::getUrl()->create('news/list/tag',array('tag'=>$val)); // e_BASE."news.php?tag=".$val
				$words[] = "<a class='news-tag' href='".$url."'>".$start.$val.$end."</a>";	
			}
		}
		
		if(count($words))
		{
			return implode($sep,$words);
		}
		else 
		{
			return LAN_NONE;
		}			
	}
	
	
	
	function sc_newsrelated($array=array())
	{
		if(!varset($array['types']))
		{
			$array['types'] = 'news,page';
		}

		$template = e107::getTemplate('news', 'news', 'related');

		return e107::getForm()->renderRelated($array, $this->news_item['news_meta_keywords'], array('news'=>$this->news_item['news_id']),$template);
	}


	function sc_newsmetadiz($parm=null)
	{
  		return e107::getParser()->toHtml($this->news_item['news_meta_description'],true);
	}

}
?>
