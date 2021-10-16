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

require_once(__DIR__.'/news_shortcodes_legacy.php');
e107::coreLan('news');

class news_shortcodes extends e_shortcode
{
	use news_shortcodes_legacy;

	//protected $news_item; - shouldn't be set - see __set/__get methods of e_shortcode & news::render_newsitem()
	protected $e107;
	//protected $param;  - shouldn't be set - see __set/__get methods of e_shortcode & news::render_newsitem()

	protected $commentsDisabled;

	protected $commentsEngine = 'e107';
	
	private $imageItem;

	protected $pagination;


	// protected $param; // do not enable - erases param. .
	
	
	function __construct($eVars = null)
	{
		parent::__construct($eVars);
		$this->e107 = e107::getInstance();

		$pref = e107::getPref();
		
		$this->commentsDisabled = vartrue($pref['comments_disabled']);

		$this->pagination = varset($pref['news_pagination'], 'record');

		if(!empty($pref['comments_engine']))
		{
			$this->commentsEngine = $pref['comments_engine'];
		}
	}

	function newsTitle($parm=null)
	{
		$text = e107::getParser()->toHTML($this->news_item['news_title'], TRUE, 'TITLE');

		if(!empty($parm['attribute']))
		{
			$text = e107::getParser()->toAttribute($text);
		}

		if(!empty($this->param['titleLimit']))
		{
			$parm['limit'] = $this->param['titleLimit'];
		}

		if(!empty($parm['limit']))
		{
			$text = e107::getParser()->text_truncate($text, $parm['limit']);
		}


		return $text;
	}


	function sc_news_body($parm=null)
	{
		$tp = e107::getParser();
		e107::getBB()->setClass("news"); // For automatic bbcode image resizing. 

		$action = isset($this->param['current_action']) ? $this->param['current_action'] : '';

		$news_body = '';

		if($parm != 'extended')
		{
			$news_body = $tp->toHTML($this->news_item['news_body'], true, 'BODY, fromadmin', $this->news_item['news_author']);
		}
		
		if($this->news_item['news_extended'] && (isset($_POST['preview']) || $action === 'extend') && ($parm !== 'noextend' && $parm !== 'body'))
		{
			$news_body .= $tp->toHTML($this->news_item['news_extended'], true, 'BODY, fromadmin', $this->news_item['news_author']);
		}

		e107::getBB()->clearClass();

		return $news_body;
	}


	function sc_news_author_realname($parm=null)
	{
		return !empty($this->news_item['user_login']) ? $this->news_item['user_login'] : null;
	}



	function sc_news_author($parm=null)
	{
		if(!empty($this->news_item['user_id']))
		{
			if($parm == 'nolink')
			{
				return $this->news_item['user_name'];
			}
			else
			{
				return "<a href='".e107::getUrl()->create('user/profile/view', $this->news_item)."'>".$this->news_item['user_name']."</a>";
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
				$NEWIMAGE = varset($param['image_new_small']);
			}
			else
			{
				$NEWIMAGE = varset($param['image_nonew_small']);
			}
		}
		else
		{
			$NEWIMAGE = varset($param['image_nonew_small']);
		}
		
		if(deftrue('BOOTSTRAP')) // Should be done with CSS, not like above.
		{
			$NEWIMAGE = "";		
		}
		
		return (!$news_item['news_allow_comments'] ? ''.($pref['comments_icon'] ? $NEWIMAGE.' ' : '')."<a title=\"".LAN_COMMENTS."\" href='".e107::getUrl()->create('news/view/item', $news_item)."'>".varset($param['commentlink']).intval($news_item['news_comment_total']).'</a>' : vartrue($param['commentoffstring'],'Disabled') );
	}

	function sc_trackback($parm=null)
	{
		return null;
	/*	global $pref;
		if(!vartrue($pref['trackbackEnabled'])) { return ''; }
		$news_item = $this->news_item;
		$news_item['#'] = 'track';
		
		return ($this->param['trackbackbeforestring'] ? $this->param['trackbackbeforestring'] : '')."<a href='".e107::getUrl()->create('news/view/item', $this->news_item)."'>".$this->param['trackbackstring'].$this->news_item['tb_count'].'</a>'.($this->param['trackbackafterstring'] ? $this->param['trackbackafterstring'] : '');*/
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
		$url = $this->sc_news_nav_url($parm);

		$caption = vartrue($parm['text'], LAN_BACK);
		
		$text = '<ul class="pagination hidden-print">
  			<li class="page-item"><a class="page-link" href="'.$url.'">'.e107::getParser()->toHTML($caption,false,'defs').'</a></li>
		</ul>';
		
		if(defined('BOOTSTRAP') && BOOTSTRAP === 4)
		{
			$text = '<a class="pager-button btn btn-primary hidden-print" href="'.$url.'">'.e107::getParser()->toHTML($caption,false,'defs').'</a>';
		}
		
		return $text;
	}



	public function sc_news_nav_url($parm=null)
	{
		$url = e107::getUrl()->create('news/list/items'); // default for now.

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

		return $url;

	}


	function sc_newsheader($parm=null)
	{
		return $this->sc_news_category_icon('src');
	}



	function sc_news_category_id($parm=null)
	{
		return !empty($this->news_item['category_id']) ? (int) $this->news_item['category_id'] : 0;
	}


	function sc_news_category_icon($parm=null)
	{
			if(is_string($parm))
		{
			$parm = array('type'=>$parm);
		}
		// BC
		$category_icon = !empty($this->news_item['category_icon']) ? str_replace('../', '', trim($this->news_item['category_icon'])) : '';
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


	//	if($this->param['caticon'] == ''){$this->param['caticon'] = 'border:0px';}

		$parm['alt']       = e107::getParser()->toHTML($this->news_item['category_name'], FALSE ,'defs');
		$parm['legacy']    = array('{e_IMAGE}newspost_images/', '{e_IMAGE}icons/');
		$parm['class']     = 'icon news_image news-category-icon';

		$icon = e107::getParser()->toIcon($category_icon, $parm);

		switch($parm['type'])
		{
			/* @deprecated - Will cause issues with glyphs */
			case 'src':
				return $src;
			break;

			case 'tag':
				return $icon;
			break;

			case 'url':
			default:
				return "<a href='".e107::getUrl()->create('news/list/category', $this->news_item)."'>".$icon."</a>";
			break;
		}
	}


	function sc_news_category_name($parm=null)
	{
		if(empty($parm['link']) && isset($this->news_item['category_name']))
		{
			return e107::getParser()->toHTML($this->news_item['category_name'], 'TITLE');
		}

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

	function sc_news_category_url($parm=null)
	{
		if(empty($this->news_item['category_id']) || empty($this->news_item['category_sef']) )
		{
			return null;
		}

		$category = array('id' => $this->news_item['category_id'], 'name' => $this->news_item['category_sef'] );

		return e107::getUrl()->create('news/list/category', $category);	
	}



	//New v2.x Aliases



	public function sc_news_title($parm=null)
	{
		return (!empty($parm['link'])) ? $this->newsTitleLink($parm) : $this->newsTitle($parm);
	}

	public function sc_news_author_signature($parm=null)
	{
		if(empty($this->news_item['user_id']))
		{
			return null;
		}

		$user = e107::user($this->news_item['user_id']);

		if(!empty($user['user_signature']))
		{
			return e107::getParser()->toHTML($user['user_signature'], true, 'DESCRIPTION');
		}
	}

	public function sc_news_author_items_url($parm=null)
	{
		if(empty($this->news_item['user_name']))
		{
			return null;
		}

		return e107::getUrl()->create('news/list/author',array('author'=>$this->news_item['user_name'])); // e_BASE."news.php?author=".$val
	}

	/**
	 * @example {NEWS_AUTHOR_EUF: field=biography} - returns the 'value' of the User Extended Field 'biography' ('type' defaults to 'value')
	 * @example {NEWS_AUTHOR_EUF: field=biography&type=icon} - returns the 'icon' of the User Extended Field 'biography' - [text|value|icon|text_value]
	*/
	public function sc_news_author_euf($parm=null)
	{
		if(empty($this->news_item['user_id']))
		{
			return null;
		}

		$userid = $this->news_item['user_id'];		
		$field 	= (!empty($parm['field'])) ? $parm['field'] : '';
		$type 	= (!empty($parm['type'])) ? $parm['type'] : 'value';
		
		if($field)
		{
			return e107::getParser()->parseTemplate("{USER_EXTENDED={$field}.{$type}.{$userid}}");
		}
	}



	public function sc_news_comment_label($parm=null)
	{
		return ($this->news_item['news_comment_total'] == 1) ? COMLAN_8 : LAN_COMMENTS;
	}




	/**
	 * Display News Images (but not video thumbnails )
	 *
     * @param $parm array
	 * @example {NEWS_IMAGE: type=src&placeholder=true}
	 * @example {NEWS_IMAGE: class=img-responsive img-fluid}
	 */
	public function sc_news_image($parm=null)
	{
		if(!empty($parm['carousel']))
		{
			return $this->news_carousel($parm);
		}

		$tp = e107::getParser();
		$srcPath = '';

		if(is_string($parm))
		{
			$parm = array('type'=> $parm);
		}


		if($tmp = $this->handleMultiple($parm))
		{
			$srcPath = $tmp['file'];
		}



		$class = (!empty($parm['class'])) ? $parm['class'] : "news_image news-image img-responsive img-fluid img-rounded rounded";
		$class .= ' news-image-'.varset($tmp['count'],0);
		$dimensions = null;
		$srcset = null;
		$src = '';

		if ($tp->isVideo($srcPath))
		{
			return null;
		}


		if (empty($srcPath))
		{
			if (varset($parm['type']) == 'placeholder' || !empty($parm['placeholder']))
			{
				$src = $tp->thumbUrl(); // placeholder;
				$dimensions = $tp->thumbDimensions();
			}
		}
		elseif ($srcPath[0] === '{') // Always resize. Use {SETIMAGE: w=x&y=x&crop=0} PRIOR to calling shortcode to change.
		{
			$src = $tp->thumbUrl($srcPath);
			$dimensions = $tp->thumbDimensions();
			$srcset = $tp->thumbSrcSet($srcPath, array('size' => '2x'));
		}
		else
		{
			// We store SC path in DB now + BC

			$src = $srcPath[0] == '{' ? $tp->replaceConstants($srcPath, 'abs') : e_IMAGE_ABS . "newspost_images/" . $srcPath;

		}




		if(!empty($parm['nolegacy'])) // Remove legacy thumbnails.
		{
			$legSrc = urldecode($src);

		 	if(strpos($legSrc,'newspost_images/thumb_')!==false)
			{
				return null;
			}
		}

		if(isset($tmp['count']) && ($tmp['count'] > 1) && empty($parm['type'])) // link first image by default, but not others.
		{
			$parm['type'] = 'tag';
		}

		$style = !empty($this->param['thumbnail']) ? $this->param['thumbnail'] : '';

		$imgParms = array(
			'class'         => $class,
			'alt'           => basename($srcPath),
			'style'         => $style,
			'placeholder'   => varset($parm['placeholder']),
			'legacy'        => '{e_IMAGE}newspost_images'
		);

		if(!empty($parm['loading']))
		{
		    $imgParms['loading'] = $parm['loading'];
        }
		

		$imgTag = $tp->toImage($srcPath,$imgParms);

		if(empty($imgTag))
		{
			return null;
		}

		switch(vartrue($parm['type']))
		{
			case 'src':
				return empty($src) ? e_IMAGE_ABS."generic/nomedia.png" : $src;
			break;

			case 'url':
				return "<a href='".e107::getUrl()->create('news/view/item', $this->news_item)."'>".$imgTag."</a>";
			break;

			case 'tag':
			default:
				return $imgTag; // "<img class='{$class}' src='".$src."' alt='' style='".$style."' {$dimensions} {$srcset} />";
			break;


		}
	}

	private function news_carousel($parm)
	{
		if(empty($this->news_item['news_thumbnail']))
		{
			return null;
		}

		$options = $parm;

		if(!isset($options['interval']))
		{
			$options['interval'] = 'false';
		}


		$tp = e107::getParser();

		$media = explode(",", $this->news_item['news_thumbnail']);
		$images = array();

		foreach($media as $file)
		{
			if($tp->isVideo($file) || empty($file))
			{
				continue;
			}

			$images[] = array('caption'=>'', 'text'=> $tp->toImage($file,$parm));
		}

	//	return print_a($images,true);

		return e107::getForm()->carousel('news-carousel-'.$this->news_item['news_id'],$images, $options);
	}

	public function sc_news_visibility($parm=null)
	{
		$string= e107::getUserClass()->getIdentifier($this->news_item['news_class']);
		return $string;

	}

	public function sc_news_rate($parm=array())
	{
		return e107::getRate()->render("news", $this->news_item['news_id'],$parm);
	}


// ----------------------------------- BC compatible Shortcodes ------------------------------------------- //

	function sc_newscategory($parm=null)
	{
		$category_name = !empty($this->news_item['category_name']) ? e107::getParser()->toHTML($this->news_item['category_name'], FALSE ,'defs') : '';
		$category = !empty($this->news_item['category_id']) ? array('id' => $this->news_item['category_id'], 'name' => $this->news_item['category_sef'] ) : array();
	//	$categoryClass = varset($GLOBALS['NEWS_CSSMODE'],'');
	    $style = isset($this->param['catlink']) ? "style='".$this->param['catlink']."'" : '';
		return "<a ".$style." href='".e107::getUrl()->create('news/list/category', $category)."'>".$category_name."</a>";
	}

	function sc_news_date($parm=null)
	{
	   $date = ($this->news_item['news_start'] > 0) ? $this->news_item['news_start'] : $this->news_item['news_datestamp'];
		$con = e107::getDate();
		$tp = e107::getParser();

		if(empty($parm))
		{
			return  $tp->toDate($date, 'long');
		}


		switch($parm)
		{
			case 'long':
			return  $tp->toDate($date, 'long');
			break;
			case 'short':
			return  $tp->toDate($date, 'short');
			break;
			case 'forum':
			return  $con->convert_date($date, 'forum');
			break;
			default :
			return $tp->toDate($date,$parm);
		//	return date($parm, $date);
			break;
		}
	}


	function sc_news_author_avatar($parm=null)
	{
		if(!empty($this->news_item['user_id']) && !empty($this->news_item['user_image']))
		{
			return e107::getParser()->toAvatar($this->news_item, $parm);
		}
	} 

	/**
	 * {NEWSCOMMENTLINK: glyph=comments&class=btn btn-default btn-sm}
	 *
	 */
	function sc_newscommentlink($parm=null)
	{

		if($this->commentsDisabled || ($this->commentsEngine != 'e107'))
		{
			return null;
		}

		$class = !empty($parm['class']) ? " ".$parm['class'] : " btn btn-default btn-secondary";

		$param = $this->getScVar('param');

		if(empty($param['commentlink']))
		{
			$param['commentlink'] = e107::getParser()->toGlyph('fa-comment','');
		}

		// When news_allow_comments = 1 then it is disabled. Backward, but that's how it is in v1.x
		$text = ($this->news_item['news_allow_comments'] ? $this->param['commentoffstring'] : "<a title='".$this->sc_newscommentcount()." ".LAN_COMMENTS."' class='e-tip".$class."' href='".e107::getUrl()->create('news/view/item', $this->news_item)."'>".$param['commentlink'].'</a>');
		return $text;
	}

	/**
	 * {NEWSCOMMENTCOUNT: glyph=x}
	 */
	function sc_news_comment_count($parm=null)
	{
		if($this->commentsDisabled || ($this->commentsEngine != 'e107'))
		{
			return null;
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

	function sc_news_id()
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
			$default = (deftrue('BOOTSTRAP')) ? $tp->toGlyph('fa-edit',false) :  "<img src='".e_IMAGE_ABS."admin_images/edit_16.png' alt=\"".LAN_EDIT."\" class='icon' />";

			
			$adop_icon = (file_exists(THEME."images/newsedit.png") ? "<img src='".THEME_ABS."images/newsedit.png' alt=\"".LAN_EDIT."\" class='icon' />" : $default);
			
			$class = varset($parm['class'], 'btn btn-default btn-secondary');
			
			return "<a class='e-tip ".$class." hidden-print' rel='external' href='".e_ADMIN_ABS."newspost.php?action=edit&amp;id=".$this->news_item['news_id']."' title=\"".LAN_EDIT."\">".$adop_icon."</a>\n";
		}
		else
		{
			return '';
		}
	}

	function sc_extended($parm=null)
	{

		$class = !empty($parm['class']) ? "class='".$parm['class']."'" : '';

		if (!empty($this->news_item) && $this->news_item['news_extended'] && ($this->param['current_action'] != 'extend' || $parm == 'force'))
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

	function sc_adminbody($parm=null)
	{
		$news_body = $this->sc_news_body($parm);
		return "<div class='".(defined('ADMINNAME') ? ADMINNAME : 'null')."'>".$news_body.'</div>';
	}

	function sc_news_summary($parm=null)
	{
		$text = '';

		if($this->news_item['news_summary'])
		{
			$text = $this->news_item['news_summary'];
		}
		elseif($this->news_item['news_body']) // Auto-generate from first 2 sentences of news-body. //TODO Add Pref?
		{
			$tp = e107::getParser();
			$text = $tp->toHTML($this->news_item['news_body'],true);
			$breaks = array('<br />','<br>');
			$text = str_replace($breaks,"\n",$text);
			$text = strip_tags($text);	
			$tmp = preg_split('/(\.\s|!|\r|\n|\?)/i', trim($text), 2, PREG_SPLIT_DELIM_CAPTURE);
			$tmp = array_filter($tmp);

			if(!empty($tmp[0]))
			{
				$text = trim($tmp[0]);
				$text .= (!empty($tmp[1])) ? trim($tmp[1]) : '';
			}
		}

		if(!empty($this->param['summaryLimit']))
		{
			$parm['limit'] = $this->param['summaryLimit'];
		}


		if(!empty($parm['limit']))
		{
			$text = e107::getParser()->text_truncate($text, $parm['limit']);
		}


		return $text;
	}

	/**
	 * Auto-thumbnailing now allowed.
	 * New sc parameter standards
	 * Exampes: 
	 * - {NEWS_THUMBNAIL=link|w=200} render link with thumbnail max width 200px
	 * - {NEWS_THUMBNAIL=|w=200} same as above
	 * - {NEWS_THUMBNAIL=src|aw=200&ah=200} return thumb link only, size forced to 200px X 200px (smart thumbnailing close to how Facebook is doing it)
	 * 
	 * First parameter values: link|src|tag
	 * Second parameter format: aw|w=xxx&ah|ah=xxx
	 * 
	 * @see eHelper::scDualParams()
	 * @see eHelper::scParams()
	 * XXX Also returns Video thumbnails. 
	 */
	function sc_news_thumbnail($parm = '') //TODO Add support {NEWSTHUMBNAIL: x=y} format
	{
		if($tmp = $this->handleMultiple($parm,'all'))
		{
			$newsThumb = $tmp['file'];
		}
		
		$class = 'news-thumbnail-'.varset($tmp['count']);
		$dimensions = null;
		$srcset = null;
		$tp = e107::getParser();
		
		if(!isset($newsThumb) && $parm != 'placeholder')
		{
			return '';
		}
		
		if(isset($newsThumb) && $vThumb = e107::getParser()->toVideo($newsThumb, array('thumb'=>'src')))
		{
			$src = $vThumb;
			$_src = '#';
			$dimensions = e107::getParser()->thumbDimensions();
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
				if($newsThumb[0] !== '{') // Fix old paths.
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
			if(!empty($newsThumb))
			{
				$_src = $src = (($newsThumb[0] === '{')) || varset($parms[1]) === 'placeholder' ? e107::getParser()->replaceConstants($newsThumb, 'abs') : e_IMAGE_ABS."newspost_images/".$newsThumb;
			}
			else
			{
				$src = '';
			}
		
			if(!empty($parms[2]) || varset($parms[1]) === 'placeholder')
			{
				//  $srcset = "srcset='".$tp->thumbSrcSet($src,'all')."' size='100vw' ";
				  $attr = !empty($parms[2]) ? $parms[2] : null;
				  $src = e107::getParser()->thumbUrl($src, $attr);
				  $dimensions = e107::getParser()->thumbDimensions();

			}
		}
		

		if(empty($parms[1]))
		{
			$parms = array(1 => null);
		}

		$style = !empty($this->param['thumbnail']) ? $this->param['thumbnail'] : '';


		switch($parms[1])
		{
			case 'src':
				return $src;
			break;

			case 'tag':
				return "<img class='news_image ".$class."' src='".$src."' alt='' style='".$style."' {$dimensions} {$srcset} />";
			break;

			case 'img':
				return "<a href='".$_src."' rel='external image'><img class='news_image ".$class."' src='".$src."' alt='' style='".$style."' {$dimensions} {$srcset} /></a>";
			break;

			default:
				return "<a href='".e107::getUrl()->create('news/view/item', $this->news_item)."'><img class='news_image img-responsive img-fluid img-rounded rounded ".$class."' src='".$src."' alt='' style='".$style."' {$dimensions} {$srcset} /></a>";
			break;
		}
	}



	/**
	 * XXX  Experimental Images/Video - supports multiple items
	 * @example {NEWS_MEDIA: item=1}
	 */
	function sc_news_media($parm=array())
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


		if(!empty($parm['placeholder']))
		{
			return $this->sc_news_image('placeholder');
		}
		elseif($video = e107::getParser()->toVideo($this->imageItem, array('class'=> 'news-media news-media-'.$parm['item'])))
		{
			return $video;
		}
		else
		{
			$parm['item'] = ($parm['item'] +1);
			if(empty($parm['class']))
			{
				$parm['class'] = 'img-responsive img-fluid news-media news-media-'.$parm['item'];
			}
			return $this->sc_news_image($parm);
		}
			
		
	}


	function sc_news_video($parm=null)
	{
		$tmp = $this->handleMultiple($parm,'video');	
		$file = varset($tmp['file']);
			
		if($video = e107::getParser()->toVideo($file, array('class'=> 'news-video-'.varset($tmp['count']))))
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
	
	
				
			
		
		





	function sc_sticky_icon()
	{
		return $this->news_item['news_sticky'] ? $this->param['image_sticky'] : '';
	}



	function newsTitleLink($parm = null)
	{
		if(is_string($parm))
		{
			parse_str($parm, $parms);
		}
		else
		{
			$parms = $parm;
		}

		$url = e107::getUrl()->create('news/view/item', $this->news_item);

		if(isset($parms['href']))
		{
			return $url;
		}

		if(isset($parm['link']))
		{
			unset($parm['link']);
		}
		
		$title = $this->sc_news_title($parm);

		return "<a style='".(isset($this->param['itemlink']) ? $this->param['itemlink'] : 'null')."' href='{$url}'>".$title.'</a>';
	}

	function sc_news_url($parm=null)
	{
		return e107::getUrl()->create('news/view/item', $this->news_item);
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
	function sc_newsitem_schook($parm=null)
	{
		if(!is_string($parm))
		{
			return null;
		}

		$parm = explode('|', $parm, 2);
		$parm[1] = 'news_id='.$this->news_item['news_id'].(varset($parm[1]) ? '&'.$parm[1] : '');
		e107::setRegistry('core/news/schook_data', array('data' => $this->news_item, 'params' => $this->param));
		return e107::getParser()->parseTemplate('{'.strtoupper($parm[0]).'='.$parm[1].'}');
	}

	public function sc_news_info($parm=null)
	{
		$news_item = $this->news_item;
		$param = $this->param;
		$con = e107::getDate();
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

	function sc_news_tags($parm=null)
	{
		$tmp = explode(",",$this->news_item['news_meta_keywords']);
		$words = array();
		$class = (!empty($parm['class'])) ? $parm['class'] : 'news-tag';
		$separator = (isset($parm['separator'])) ? $parm['separator'] : ', ';
		
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
			$sep = $separator;
		}
		
		foreach($tmp as $val)
		{
			if(trim($val))
			{
				//$url = e107::getUrl()->create('news/list/tag',array('tag'=>rawurlencode($val))); // e_BASE."news.php?tag=".$val
				// will be encoded during create()
				$url = e107::getUrl()->create('news/list/tag',array('tag'=> str_replace(' ','-',$val))); // e_BASE."news.php?tag=".$val
				$words[] = "<a class='".$class."' href='".$url."'>".$start.$val.$end."</a>";
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


	/**
	*
	* @param array $array
	* @param string $array['types']
	* @param int $array['limit']
	* @example {NEWS_RELATED: types=news&limit-3}
	* @return string
	*/
	function sc_news_related($array=array())
	{
		if(!varset($array['types']))
		{
			$array['types'] = 'news,page';
		}

		$template = e107::getTemplate('news', 'news', 'related');

		return e107::getForm()->renderRelated($array, $this->news_item['news_meta_keywords'], array('news'=>$this->news_item['news_id']),$template);
	}


	function sc_news_description($parm=null)
	{
  		$text = e107::getParser()->toHTML($this->news_item['news_meta_description'],true);

		if(!empty($parm['limit']))
		{
			$text = e107::getParser()->text_truncate($text, $parm['limit']);
		}

		return $text;

	}

	/** New in v2.3 {NEWS_PAGINATION}  */
	function sc_news_pagination($parm=null)
	{
		$params = (string) e107::getRegistry('core/news/pagination');

		parse_str($params,$opts);

		if($this->pagination === 'page')
		{
			$opts['type'] = 'page';
			$opts['total'] = ceil($opts['total'] / $opts['amount']);
			$opts['current'] = ($opts['current'] / $opts['amount']) + 1;
		}

		return e107::getParser()->parseTemplate("{NEXTPREV=".http_build_query($opts)."}");

	}



	/**
	 * @example {NEWS_NAV_NEXT}
	 * @return string|null
	 */
	function sc_news_nav_next()
	{
		return $this->parseNavTemplate('next');
	}

	/**
	 * {NEWS_NAV_PREVIOUS}
	 * @return string|null
	 */
	function sc_news_nav_previous()
	{
		return $this->parseNavTemplate('previous');
	}

	/**
	 * {NEWS_NAV_CURRENT}
	 * @return string|null
	 */
	function sc_news_nav_current()
	{
		$template = e107::getTemplate('news', 'news_view', 'nav');
		return e107::getParser()->parseTemplate($template['current'], true, $this);
	}

	/**
	 * Retrieves the data from the previous/next news record and renders the corresponding template.
	 * @param string $type next|previous
	 * @return string|null
	 */
	private function parseNavTemplate($type)
	{
		if(!$data = $this->getNavQuery($type))
		{
			return null;
		}

		$template = e107::getTemplate('news', 'news_view', 'nav');
		$orig = $this->getScVar('news_item');
		$this->setScVar('news_item', $data);
		$text = e107::getParser()->parseTemplate($template[$type], true, $this);
		$this->setScVar('news_item', $orig);

		return $text;
	}

	/**
	 * Retrieve table data from the previous or next news item.
	 * @param string $type next/previous
	 * @return array|string
	 */
	private function getNavQuery($type)
	{
		$nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";

		$var = $this->getScVar('news_item');

		$dir = ($type === 'next') ? '>=' : '<=';
		$sort = ($type === 'next') ? 'ASC' : 'DESC';

		$query = "
				SELECT SQL_CALC_FOUND_ROWS n.*, u.user_id, u.user_name, u.user_customtitle, u.user_image, nc.category_id, nc.category_name, nc.category_sef, nc.category_icon,
				nc.category_meta_keywords, nc.category_meta_description, nc.category_template 
				FROM #news AS n
				LEFT JOIN #user AS u ON n.news_author = u.user_id
				LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
				WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.")
				AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
				AND (FIND_IN_SET('0', n.news_render_type) OR FIND_IN_SET(1, n.news_render_type))
				AND n.news_datestamp ".$dir . (int) $var['news_datestamp']. " AND n.news_id != ".(int) $var['news_id']." ORDER by n.news_datestamp ".$sort." LIMIT 1";

		return e107::getDb()->retrieve($query);
	}

}

