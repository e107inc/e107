<?php
/*
* Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: news_shortcodes.php,v 1.29 2009-09-27 20:07:05 e107coders Exp $
*
* News shortcode batch
*/
if (!defined('e107_INIT')) { exit; }
//include_once(e_HANDLER.'shortcode_handler.php');

/*
$codes = array(
'newstitle', 'newsbody', 'newsicon','newsauthor', 'newscomments',
'trackback', 'newsheader', 'newscategory', 'newsdate', 'newscommentlink',
'newscommentcount', 'emailicon', 'printicon', 'pdficon', 'newsid', 'adminoptions',
'extended', 'captionclass', 'admincaption', 'adminbody', 'newssummary',
'newsthumbnail', 'newsimage', 'sticky_icon', 'newstitlelink', 'newscaticon', 'newsinfo'
);
*/

$codes = array();
/*
$tmp = get_class_methods('news_shortcodes');
foreach($tmp as $c)
{
	if(strpos($c, 'sc_') === 0)
	{
		$codes[] = substr($c, 3);
	}
}
unset($tmp);
*/
register_shortcode('news_shortcodes', TRUE);
initShortcodeClass('news_shortcodes');

class news_shortcodes
{
	var $news_item, $param, $e107;

	function news_shortcodes()
	{
		$this->e107 = e107::getInstance();
	}

	function loadNewsItem()
	{
		$e107 = e107::getInstance();
		$e107->tp->e_sc->scClasses['news_shortcodes']->news_item = getcachedvars('current_news_item');
		$e107->tp->e_sc->scClasses['news_shortcodes']->param = getcachedvars('current_news_param');
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
		$category_icon = str_replace('../', '', trim($this->news_item['category_icon']));
		if ($category_icon && strstr('images', $category_icon))
		{
			$category_icon = THEME_ABS.$category_icon;
		}
		else
		{
			$category_icon = e_IMAGE_ABS.'icons/'.$category_icon;
		}
		if (!$category_icon) { return ''; }

		return "<a href='".$this->e107->url->getUrl('core:news', 'main', 'action=list&id='.$this->news_item['news_category'].'&sef='.$this->news_item['news_category_rewrite_string'])."'><img style='".$this->param['caticon']."'  src='".$category_icon."' alt='' /></a>";
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
		$category_icon = str_replace("../", "", trim($this->news_item['category_icon']));
		if (!$category_icon) return '';
		if ($category_icon && strstr("images", $category_icon)) {
			return THEME_ABS.$category_icon;
		} else {
			return e_IMAGE_ABS."icons/".$category_icon;
		}
	}


	function sc_newscategory($parm)
	{
		$category_name = $this->e107->tp->toHTML($this->news_item['category_name'], FALSE ,'defs');
		return "<a class='".$GLOBALS['NEWS_CSSMODE']."_category' style='".(isset($this->param['catlink']) ? $this->param['catlink'] : "#")."' href='".$this->e107->url->getUrl('core:news', 'main', 'action=list&id='.$this->news_item['news_category'].'&sef='.$this->news_item['news_category_rewrite_string'])."'>".$category_name."</a>";
	}

	function sc_newsdate($parm)
	{
		$con = new convert;
		if($parm == '')
		{
			return  $con->convert_date($this->news_item['news_datestamp'], 'long');
		}
		switch($parm)
		{
			case 'long':
			return  $con->convert_date($this->news_item['news_datestamp'], 'long');
			break;
			case 'short':
			return  $con->convert_date($this->news_item['news_datestamp'], 'short');
			break;
			case 'forum':
			return  $con->convert_date($this->news_item['news_datestamp'], 'forum');
			break;
			default :
			return date($parm, $this->news_item['news_datestamp']);
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
			if (isset($_POST['preview']))
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
		$news_title = $this->e107->tp->toHTML($this->news_item['news_title'], TRUE,'no_hook,emotes_off, no_make_clickable');
		return "<div class='category".$this->news_item['news_category']."'>".($this->news_item['news_render_type'] == 1 ? "<a href='".e107::getUrl()->create('core:news', 'main', 'action=extend&id='.$this->news_item['news_id'].'&sef='.$this->news_item['news_rewrite_string'])."'>".$news_title."</a>" : $news_title)."</div>";
	}

	function sc_admincaption()
	{
		$news_title = $this->e107->tp->toHTML($this->news_item['news_title'], TRUE,'no_hook,emotes_off, no_make_clickable');
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

	function sc_newsthumbnail($parm = '')
	{
		switch($parm)
		{
			case 'src':
				return (isset($this->news_item['news_thumbnail']) && $this->news_item['news_thumbnail'] ? e_IMAGE_ABS.$this->news_item['news_thumbnail'] : '');
			break;
			
			case 'tag':
				return (isset($this->news_item['news_thumbnail']) && $this->news_item['news_thumbnail'] ? "<img class='news_image' src='".e_IMAGE_ABS."newspost_images/".$this->news_item['news_thumbnail']."' alt='' style='".$this->param['thumbnail']."' />" : '');
			break;
		
			default:
				return (isset($this->news_item['news_thumbnail']) && $this->news_item['news_thumbnail']) ? "<a href='".$this->e107->url->getUrl('core:news', 'main', "action=extend&id={$this->news_item['news_id']}&catid={$this->news_item['news_category']}&sef={$this->news_item['news_rewrite_string']}")."'><img class='news_image' src='".e_IMAGE_ABS."newspost_images/".$this->news_item['news_thumbnail']."' alt='' style='".$this->param['thumbnail']."' /></a>" : '';
			break;
		}
	}

	function sc_newsimage($parm = '')
	{
		switch($parm)
		{
			case 'src':
				return (isset($this->news_item['news_thumbnail']) && $this->news_item['news_thumbnail'] ? e_IMAGE_ABS.$this->news_item['news_thumbnail'] : '');
			break;
			
			case 'tag':
				return (isset($this->news_item['news_thumbnail']) && $this->news_item['news_thumbnail'] ? "<img class='news_image' src='".e_IMAGE_ABS."newspost_images/".$this->news_item['news_thumbnail']."' alt='' style='".$this->param['thumbnail']."' />" : '');
			break;
		
			default:
				return (isset($this->news_item['news_thumbnail']) && $this->news_item['news_thumbnail']) ? "<a href='".$this->e107->url->getUrl('core:news', 'main', "action=extend&id={$this->news_item['news_id']}&catid={$this->news_item['news_category']}&sef={$this->news_item['news_rewrite_string']}")."'><img class='news_image' src='".e_IMAGE_ABS."newspost_images/".$this->news_item['news_thumbnail']."' alt='' style='".$this->param['thumbnail']."' /></a>" : '';
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
		$category_icon = str_replace('../', '', trim($this->news_item['category_icon']));
		if (!$category_icon) { return ''; }
		if ($category_icon && strstr('images', $category_icon))
		{
			$category_icon = THEME_ABS.$category_icon;
		}
		else
		{
			$category_icon = e_IMAGE_ABS.'icons/'.$category_icon;
		}
		
		//TODO - remove inline styles
		if($this->param['caticon'] == ''){$this->param['caticon'] = 'border:0px';}
		
		switch($parm)
		{
			case 'src':
				return $category_icon;
			break;
			
			case 'tag':
				return "<img class='news_image' src='{$category_icon}' alt='' style='".$this->param['caticon']."' />";
			break;
		
			default:
				return "<a href='".$this->e107->url->getUrl('core:news', 'main', "action=list&id={$this->news_item['news_category']}&sef={$this->news_item['news_category_rewrite_string']}")."'><img style='".$this->param['caticon']."' src='".$category_icon."' alt='' /></a>";
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
	
	function sc_alt_news($news_category)
	{
		global $sql, $aj, $ns;
		$ix = new news;
		if (strstr(e_QUERY, "cat"))
		{
			$category = $news_category;
			if ($category != 0)
			{
				$gen = new convert;
				$sql2 = new db;
				$sql->db_Select("news_category", "*", "category_id='".intval($category)."'");
				list($category_id, $category_name, $category_icon) = $sql->db_Fetch();
				$category_name = $aj->tpa($category_name);
				if (strstr($category_icon, "../"))
				{
					$category_icon = str_replace("../", "", e_BASE.$category_icon);
				} else {
				$category_icon = THEME.$category_icon;
			}

			if ($count = $sql->db_Select("news", "*", "news_category='".intval($category)."' ORDER BY news_datestamp DESC"))
			{
				while ($row = $sql->db_Fetch())
				{
					extract($row);
					if ($news_title == "")
					{
						$news_title = "Untitled";
					}
					$datestamp = $gen->convert_date($news_datestamp, "short");
					$news_body = strip_tags(substr($news_body, 0, 100))." ...";
					$comment_total = $sql2->db_Count("comments", "(*)", "WHERE comment_item_id='".intval($news_id)."' AND comment_type='0' ");
					$bullet = '';
					if(defined('BULLET'))
					{
						$bullet = '<img src="'.THEME.'images/'.BULLET.'" alt="" class="icon" />';
					}
					elseif(file_exists(THEME.'images/bullet2.gif'))
					{
						$bullet = '<img src="'.THEME.'images/bullet2.gif" alt="" class="icon" />';
					}
					$text .= "
						<div class='mediumtext'>
						".$bullet;

					if ($news_allow_comments) {
						$text .= "<a href='news.php?extend.".$news_id."'>".$news_title."</a>";
					} else {
						$text .= "<a href='comment.php?comment.news.".$news_id."'>".$news_title."</a>";
					}
					$text .= "<br />
						".LAN_NEWS_100." ".$datestamp." (".LAN_NEWS_99.": ";
					if ($news_allow_comments) {
						$text .= COMMENTOFFSTRING.")";
					} else {
						$text .= $comment_total.")";
					}
					$text .= "</div>
						".$news_body."
						<br /><br />\n";
				}
				$text = "<img src='$category_icon' alt='' /><br />". LAN_NEWS_307.$count."
					<br /><br />".$text;
				$ns->tablerender(LAN_NEWS_82." '".$category_name."'", $text, 'alt_news');
			}
		}
			return TRUE;
		}

		if ($sql->db_Select("news", "*", "news_class<255 AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().") AND news_category='".intval($news_category)."' ORDER BY news_datestamp DESC LIMIT 0,".ITEMVIEW)) {
		$sql2 = new db;
		while (list($news['news_id'], $news['news_title'], $news['data'], $news['news_extended'], $news['news_datestamp'], $news['admin_id'], $news_category, $news['news_allow_comments'], $news['news_start'], $news['news_end'], $news['news_class']) = $sql->db_Fetch()) {

			if (check_class($news['news_class']) || !$news['news_class']) {

				if ($news['admin_id'] == 1 && $pref['siteadmin']) {
					$news['admin_name'] = $pref['siteadmin'];
				}
				else if(!$news['admin_name'] = getcachedvars($news['admin_id'])) {
					$sql2->db_Select("user", "user_name", "user_id='".intval($news['admin_id'])."' ");
					list($news['admin_name']) = $sql2->db_Fetch();
					cachevars($news['admin_id'], $news['admin_name']);
				}

					$sql2->db_Select("news_category", "*", "category_id='".intval($news_category)."' ");

				list($news['category_id'], $news['category_name'], $news['category_icon']) = $sql2->db_Fetch();
				$news['comment_total'] = $sql2->db_Count("comments", "(*)", "WHERE comment_item_id='".intval($news['news_id'])."' AND comment_type='0' ");
				$ix->render_newsitem($news);
			} 
			/*
			else 
			{
				if ($pref['subnews_hide_news'] == 1) 		This $pref no longer available
				{
					if ($news['admin_id'] == 1 && $pref['siteadmin']) {
						$news['admin_name'] = $pref['siteadmin'];
					}
					else if(!$news['admin_name'] = getcachedvars($news['admin_id'])) {
						$sql2->db_Select("user", "user_name", "user_id='".intval($news['admin_id'])."' ");
						list($news['admin_name']) = $sql2->db_Fetch();
						cachevars($news['admin_id'], $news['admin_name']);
					}

					$sql2->db_Select("news_category", "*", "category_id='".intval($news_category)."' ");

					list($news['category_id'], $news['category_name'], $news['category_icon']) = $sql2->db_Fetch();
					$ix->render_newsitem($news, "", "userclass");
				}
			} */
			}
		}
	}

}
?>