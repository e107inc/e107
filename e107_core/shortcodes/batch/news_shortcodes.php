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

	function __construct($eVars = null)
	{
		parent::__construct($eVars);
		$this->e107 = e107::getInstance();
	}

	function sc_newstitle()
	{
		return e107::getParser()->toHTML($this->news_item['news_title'], TRUE, 'TITLE');
	}

	function sc_newsurltitle()
	{
		$title = $this->sc_newstitle();
		// FIXME generic parser toAttribute method (currently toAttribute() isn't appropriate)
		return '<a href="'.$this->sc_newsurl().'" title="'.preg_replace('/\'|"|<|>/s', '', $this->news_item['news_title']).'">'.$title.'</a>';
	}
	
	function sc_newsbody($parm)
	{
		$tp = e107::getParser();
		e107::getBB()->setClass("news");
		$news_body = $tp->toHTML($this->news_item['news_body'], true, 'BODY, fromadmin', $this->news_item['news_author']);
		if($this->news_item['news_extended'] && (isset($_POST['preview']) || $this->param['current_action'] == 'extend') && $parm != 'noextend')
		{
			$news_body .= $tp->toHTML($this->news_item['news_extended'], true, 'BODY, fromadmin', $this->news_item['news_author']);
		}
		e107::getBB()->clearClass();
		return $news_body;
	}

	function sc_newsicon($parm)
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

	function sc_newscomments($parm)
	{
		
		$pref = e107::getPref();
		$sql = e107::getDb();
		
		if($pref['comments_disabled'] == 1)
		{
			return "Disabled";
		}
		
		$news_item = $this->news_item;
		$param = $this->param;

		if($param['current_action'] == 'extend')
		{
			return LAN_NEWS_99.' ('.$news_item['news_comment_total'].')';
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

	function sc_trackback($parm)
	{
		global $pref;
		if(!varsettrue($pref['trackbackEnabled'])) { return ''; }
		$news_item = $this->news_item;
		$news_item['#'] = 'track';
		
		return ($this->param['trackbackbeforestring'] ? $this->param['trackbackbeforestring'] : '')."<a href='".e107::getUrl()->create('news/view/item', $this->news_item)."'>".$this->param['trackbackstring'].$this->news_item['tb_count'].'</a>'.($this->param['trackbackafterstring'] ? $this->param['trackbackafterstring'] : '');
	}

	function sc_newsheader($parm)
	{
		return $this->sc_newscaticon('src');
	}


	function sc_newscategory($parm)
	{
		$category_name = e107::getParser()->toHTML($this->news_item['category_name'], FALSE ,'defs');
		return "<a class='".$GLOBALS['NEWS_CSSMODE']."_category' style='".(isset($this->param['catlink']) ? $this->param['catlink'] : "#")."' href='".e107::getUrl()->create('news/list/category', $this->news_item)."'>".$category_name."</a>";
	}

	function sc_newsdate($parm)
	{
	   $date = ($this->news_item['news_start'] > 0) ? $this->news_item['news_start'] : $this->news_item['news_datestamp'];
		$con = new convert;
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


	function sc_newsavatar()
	{
		return vartrue($this->news_item['user_id']) ? e107::getParser()->parseTemplate("{USER_AVATAR=".$this->news_item['user_id']."}",true) : '';
	} 

	function sc_newscommentlink($parm)
	{
		return ($this->news_item['news_allow_comments'] ? $this->param['commentoffstring'] : " <a href='".e107::getUrl()->create('news/view/item', $this->news_item)."'>".$this->param['commentlink'].'</a>');
	}

	function sc_newscommentcount($parm)
	{
		return $this->news_item['news_comment_total'];
	}

	function sc_emailicon($parm)
	{
		$pref = e107::getPref();
		if (!check_class(varset($pref['email_item_class'],e_UC_MEMBER)))
		{
			return '';
		}
		require_once(e_HANDLER.'emailprint_class.php');
		return emailprint::render_emailprint('news', $this->news_item['news_id'], 1);
	}

	function sc_printicon()
	{
		require_once(e_HANDLER.'emailprint_class.php');
		return emailprint::render_emailprint('news', $this->news_item['news_id'], 2);
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

	function sc_adminoptions()
	{
		if (ADMIN && getperms('H'))
		{
			
			$default = (deftrue('BOOTSTRAP')) ? "<i class='icon-edit'></i>" :  "<img src='".e_IMAGE_ABS."admin_images/edit_16.png' alt='".LAN_NEWS_25."' class='icon' />";
			
			$adop_icon = (file_exists(THEME."images/newsedit.png") ? "<img src='".THEME_ABS."images/newsedit.png' alt='".LAN_NEWS_25."' class='icon' />" : $default);
			return " <a href='".e_ADMIN_ABS."newspost.php?action=create&amp;sub=edit&amp;id=".$this->news_item['news_id']."' title=\"".LAN_NEWS_25."\">".$adop_icon."</a>\n";
		}
		else
		{
			return '';
		}
	}

	function sc_extended($parm)
	{

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
				return $es1."<a href='".e107::getUrl()->create('news/view/item', $this->news_item)."'>".$es."</a>".$es2;
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

	function sc_newssummary()
	{
		return ($this->news_item['news_summary']) ? $this->news_item['news_summary'].'<br />' : '';
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
	 */
	function sc_newsthumbnail($parm = '')
	{
		if(!$this->news_item['news_thumbnail'])
		{
			return '';
		}
		
		$parms = eHelper::scDualParams($parm);
		// We store SC path in DB now + BC
		$_src = $src = $this->news_item['news_thumbnail'][0] == '{' ? e107::getParser()->replaceConstants($this->news_item['news_thumbnail'], 'abs') : e_IMAGE_ABS."newspost_images/".$this->news_item['news_thumbnail'];
		if($parms[2])  $src = e107::getParser()->thumbUrl($src, $parms[2]);
		switch($parms[1])
		{
			case 'src':
				return $src;
			break;

			case 'tag':
				return "<img class='news_image' src='".$src."' alt='' style='".$this->param['thumbnail']."' />";
			break;

			case 'img':
				return "<a href='".$_src."' rel='external image'><img class='news_image' src='".$src."' alt='' style='".$this->param['thumbnail']."' /></a>";
			break;

			default:
				return "<a href='".e107::getUrl()->create('news/view/item', $this->news_item)."'><img class='news_image img-rounded' src='".$src."' alt='' style='".$this->param['thumbnail']."' /></a>";
			break;
		}
	}

	function sc_newsimage($parm = '')
	{
		$tp = e107::getParser();
		if(!$this->news_item['news_thumbnail'])
		{
			return '';
		}
		
			
		if($this->news_item['news_thumbnail'][0] == '{' ) // Always resize. Use {SETIMAGE: w=x&y=x&crop=0} PRIOR to calling shortcode to change. 
		{
			$src = $tp->thumbUrl($this->news_item['news_thumbnail']);	
		}
		else
		{
			// We store SC path in DB now + BC
			$src = $this->news_item['news_thumbnail'][0] == '{' ? $tp->replaceConstants($this->news_item['news_thumbnail'], 'abs') : e_IMAGE_ABS."newspost_images/".$this->news_item['news_thumbnail'];			
		}
		
		
		switch($parm)
		{
			case 'src':
				return $src;
			break;

			case 'tag':
				return "<img class='news_image' src='".$src."' alt='' style='".$this->param['thumbnail']."' />";
			break;

			case 'url':
			default:
				return "<a href='".e107::getUrl()->create('news/view/item', $this->news_item)."'><img class='news_image img-rounded' src='".$src."' alt='' style='".$this->param['thumbnail']."' /></a>";
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
				return $category_icon;
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
	function sc_newsitem_schook($parm)
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
		foreach($tmp as $val)
		{
			if(trim($val))
			{
				$words[] = "<a href='".e_BASE."news.php?tag=".$val."'><span class='label'>".$val."</span></a>";	
			}
		}
		
		if(count($words))
		{
			return implode(" ",$words);
		}
		else 
		{
			return "None";
		}			
	}

}
?>
