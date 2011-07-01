<?php
/*
* Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id$
*
* News shortcode batch
*/
/**
 *	@package    e107
 *	@subpackage	shortcodes
 *	@version 	$Id$;
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
		return $this->e107->tp->toHTML($this->news_item['news_title'], TRUE, 'TITLE');
	}

	function sc_newsbody($parm)
	{
		$news_body = $this->e107->tp->toHTML($this->news_item['news_body'], true, 'BODY, fromadmin', $this->news_item['news_author']);
		if($this->news_item['news_extended'] && (isset($_POST['preview']) || $this->param['current_action'] == 'extend') && $parm != 'noextend')
		{
			$news_body .= $this->e107->tp->toHTML($this->news_item['news_extended'], true, 'BODY, fromadmin', $this->news_item['news_author']);
		}

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
				return "<a href='".$this->e107->url->getUrl('core:user', 'main', 'func=profile&id='.$this->news_item['user_id'])."'>".$this->news_item['user_name']."{$parm}</a>";
			}
		}
		return "<a href='http://e107.org'>e107</a>";
	}

	function sc_newscomments($parm)
	{
		global $pref, $sql;
		if($pref['comments_disabled'] == 1)
		{
			return;
		}
		$news_item = $this->news_item;
		$param = $this->param;

		if($param['current_action'] == 'extend')
		{
			return '';
		}

		if (vartrue($pref['multilanguage']))
		{	// Can have multilanguage news table, monlingual comment table. If the comment table is multilingual, it'll only count entries in the current language
			$news_item['news_comment_total'] = $sql->db_Count("comments", "(*)", "WHERE comment_item_id='".$news_item['news_id']."' AND comment_type='0' ");
		}

		//XXX - ??? - another query? We should cache it in news table.
		if ($pref['comments_icon'] && $news_item['news_comment_total'])
		{
			$sql->db_Select('comments', 'comment_datestamp', "comment_item_id='".intval($news_item['news_id'])."' AND comment_type='0' ORDER BY comment_datestamp DESC LIMIT 0,1");
			list($comments['comment_datestamp']) = $sql->db_Fetch();
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
		return (!$news_item['news_allow_comments'] ? ''.($pref['comments_icon'] ? $NEWIMAGE : '')." <a href='".e107::getUrl()->create('core:news', 'main', 'action=extend&id='.$news_item['news_id'].'&sef='.$news_item['news_rewrite_string'])."'>".$param['commentlink'].$news_item['news_comment_total'].'</a>' : $param['commentoffstring']);
	}

	function sc_trackback($parm)
	{
		global $pref;
		if(!varsettrue($pref['trackbackEnabled'])) { return ''; }
		return ($this->param['trackbackbeforestring'] ? $this->param['trackbackbeforestring'] : '')."<a href='".e_HTTP."comment.php?comment.news.".$this->news_item['news_id']."#track'>".$this->param['trackbackstring'].$this->news_item['tb_count'].'</a>'.($this->param['trackbackafterstring'] ? $this->param['trackbackafterstring'] : '');
	}

	function sc_newsheader($parm)
	{
		return $this->sc_newscaticon('src');
	}


	function sc_newscategory($parm)
	{
		$category_name = $this->e107->tp->toHTML($this->news_item['category_name'], FALSE ,'defs');
		return "<a class='".$GLOBALS['NEWS_CSSMODE']."_category' style='".(isset($this->param['catlink']) ? $this->param['catlink'] : "#")."' href='".$this->e107->url->getUrl('core:news', 'main', 'action=list&id='.$this->news_item['news_category'].'&sef='.$this->news_item['news_category_rewrite_string'])."'>".$category_name."</a>";
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

	function sc_newscommentlink($parm)
	{
		return ($this->news_item['news_allow_comments'] ? $this->param['commentoffstring'] : " <a href='".e107::getUrl()->create('core:news', 'main', 'action=extend&id='.$this->news_item['news_id'].'&sef='.$this->news_item['news_rewrite_string'])."'>".$this->param['commentlink'].'</a>');
	}

	function sc_newscommentcount($parm)
	{
		return $this->news_item['news_comment_total'];
	}

	function sc_emailicon($parm)
	{
		global $pref;
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
		global $pref;
		if (!$pref['plug_installed']['pdf']) { return ''; }
		return $this->e107->tp->parseTemplate('{PDF='.LAN_NEWS_24.'^news.'.$this->news_item['news_id'].'}');
	}

	function sc_newsid()
	{
		return $this->news_item['news_id'];
	}

	function sc_adminoptions()
	{
		if (ADMIN && getperms('H'))
		{
			$adop_icon = (file_exists(THEME."images/newsedit.png") ? THEME_ABS."images/newsedit.png" : e_IMAGE_ABS."admin_images/edit_16.png");
			return " <a href='".e_ADMIN_ABS."newspost.php?create.edit.".$this->news_item['news_id']."'><img src='".$adop_icon."' alt='".LAN_NEWS_25."' class='icon' /></a>\n";
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
			if (defined('PRE_EXTENDEDSTRING'))
			{
				$es1 = PRE_EXTENDEDSTRING;
			}
			if (defined('POST_EXTENDEDSTRING'))
			{
				$es2 = POST_EXTENDEDSTRING;
			}
			if (deftrue('ADMIN_AREA') && isset($_POST['preview']))
			{
				return $es1.EXTENDEDSTRING.$es2."<br />".$this->news_item['news_extended'];
			}
			else
			{
				return $es1."<a href='".$this->e107->url->getUrl('core:news', 'main', 'action=extend&id='.$this->news_item['news_id'].'&sef='.$this->news_item['news_rewrite_string'])."'>".EXTENDEDSTRING."</a>".$es2;
			}
		}
		return '';
	}

	function sc_captionclass()
	{
		$news_title = $this->e107->tp->toHTML($this->news_item['news_title'], TRUE,'TITLE');
		return "<div class='category".$this->news_item['news_category']."'>".($this->news_item['news_render_type'] == 1 ? "<a href='".e107::getUrl()->create('core:news', 'main', 'action=extend&id='.$this->news_item['news_id'].'&sef='.$this->news_item['news_rewrite_string'])."'>".$news_title."</a>" : $news_title)."</div>";
	}

	function sc_admincaption()
	{
		$news_title = $this->e107->tp->toHTML($this->news_item['news_title'], TRUE,'TITLE');
		return "<div class='".(defined('ADMINNAME') ? ADMINNAME : "null")."'>".($this->news_item['news_render_type'] == 1 ? "<a href='".e107::getUrl()->create('core:news', 'main', 'action=extend&id='.$this->news_item['news_id'].'&sef='.$this->news_item['news_rewrite_string'])."'>".$news_title."</a>" : $news_title)."</div>";
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

	// FIXME - REAL thumbnail - already possible on the fly
	function sc_newsthumbnail($parm = '')
	{
		if(!$this->news_item['news_thumbnail'])
		{
			return '';
		}
		// We store SC path in DB now + BC
		$src = $this->news_item['news_thumbnail'][0] == '{' ? e107::getParser()->replaceConstants($this->news_item['news_thumbnail'], 'abs') : e_IMAGE_ABS."newspost_images/".$this->news_item['news_thumbnail'];
		switch($parm)
		{
			case 'src':
				return $src;
			break;

			case 'tag':
				return "<img class='news_image' src='".$src."' alt='' style='".$this->param['thumbnail']."' />";
			break;

			default:
				return "<a href='".$this->e107->url->getUrl('core:news', 'main', "action=extend&id={$this->news_item['news_id']}&catid={$this->news_item['news_category']}&sef={$this->news_item['news_rewrite_string']}")."'><img class='news_image' src='".$src."' alt='' style='".$this->param['thumbnail']."' /></a>";
			break;
		}
	}

	function sc_newsimage($parm = '')
	{
		if(!$this->news_item['news_thumbnail'])
		{
			return '';
		}
		// We store SC path in DB now + BC
		$src = $this->news_item['news_thumbnail'][0] == '{' ? e107::getParser()->replaceConstants($this->news_item['news_thumbnail'], 'abs') : e_IMAGE_ABS."newspost_images/".$this->news_item['news_thumbnail'];

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
				return "<a href='".$this->e107->url->getUrl('core:news', 'main', "action=extend&id={$this->news_item['news_id']}&catid={$this->news_item['news_category']}&sef={$this->news_item['news_rewrite_string']}")."'><img class='news_image' src='".$src."' alt='' style='".$this->param['thumbnail']."' /></a>";
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
		$url = $this->e107->url->getUrl('core:news', 'main', "action=".vartrue($parms['action'], 'extend')."&id={$this->news_item['news_id']}&sef={$this->news_item['news_rewrite_string']}");
		if(isset($parms['href']))
		{
			return $url;
		}
		return "<a style='".(isset($this->param['itemlink']) ? $this->param['itemlink'] : 'null')."' href='{$url}'>".$this->news_item['news_title'].'</a>';
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
		
		

		//TODO - remove inline styles
		if($this->param['caticon'] == ''){$this->param['caticon'] = 'border:0px';}

		switch($parm)
		{
			case 'src':
				return $category_icon;
			break;

			case 'tag':
				return "<img class='news_image' src='{$src}' alt='' style='".$this->param['caticon']."' />";
			break;

			case 'url':
			default:
				return "<a href='".$this->e107->url->getUrl('core:news', 'main', "action=list&id={$this->news_item['news_category']}&sef={$this->news_item['news_category_rewrite_string']}")."'><img style='".$this->param['caticon']."' src='".$src."' alt='' /></a>";
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
		return $this->e107->tp->parseTemplate('{'.strtoupper($parm[0]).'='.$parm[1].'}');
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
		//return $this->e107->ns->tablerender(LAN_NEWS_18, $info);
		return $info;
	}


}
?>