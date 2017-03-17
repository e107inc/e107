<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Comment menu shortcodes
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/comment_menu/comment_menu_shortcodes.php,v $
 * $Revision$
 * $Date$
 * $Author$
*/

if (!defined('e107_INIT')) { exit; }

//$comment_menu_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);
//e107::getRegistry('plugin/comment_menu/current');

class comment_menu_shortcodes extends e_shortcode
{
	/**
	 * @DEPRECATED - use css styling instead. 
	 */
	function sc_cm_icon()
	{
		//TODO review bullet
		$bullet = '';
		if(defined('BULLET'))
		{
			$bullet = '<img src="'.THEME.'images/'.BULLET.'" alt="" class="icon" />';
		}
		elseif(file_exists(THEME.'images/bullet2.gif'))
		{
			$bullet = '<img src="'.THEME.'images/bullet2.gif" alt="" class="icon" />';
		}
		return $bullet;
	}

	function sc_cm_datestamp()
	{
		return e107::getParser()->toDate($this->var['comment_datestamp'], "relative");
	}
		
	function sc_cm_heading($parm=null)
	{
		if(!empty($parm['limit'])) // new v2.1.5
		{
			$text = e107::getParser()->text_truncate($this->var['comment_title'], $parm['limit']);
		}
		else
		{
			$text = $this->var['comment_title'];
		}

		return e107::getParser()->toHtml($text,false,'TITLE');
	}
		
	function sc_cm_url_pre()
	{
		return ($this->var['comment_url'] ? "<a href='".$this->var['comment_url']."'>" : "");
	}

	function sc_cm_url() // new v2.1.5
	{
		return (!empty($this->var['comment_url'])) ? $this->var['comment_url'] : '#';
	}
		
	function sc_cm_url_post()
	{
		return ($this->var['comment_url'] ? "</a>" : "");
	}
		
	function sc_cm_type()
	{
		return $this->var['comment_type'];
	}
		
	function sc_cm_author()
	{
		return $this->var['comment_author'];
	}

	function sc_cm_author_avatar($parm=null) // new v2.1.5
	{
		$data = array('user_id'=>$this->var['comment_author_id'], 'user_image'=>$this->var['comment_author_image']);
		return e107::getParser()->toAvatar($data, $parm);
	}
	
	
	function sc_cm_comment($parm=null)
	{
		$menu_pref 	= e107::getConfig('menu')->getPref();
		$tp 		= e107::getParser();
		$COMMENT 	= '';


		if(!empty($parm['limit'])) // override using shortcode parm.  // new v2.1.5
		{
			$menu_pref['comment_characters'] = intval($parm['limit']);
		}

		
		if($menu_pref['comment_characters'] > 0)
		{
			$COMMENT = strip_tags($tp->toHTML($this->var['comment_comment'], TRUE, "emotes_off, no_make_clickable", "", e107::getPref('menu_wordwrap')));
			if ($tp->ustrlen($COMMENT) > $menu_pref['comment_characters'])
			{
				$COMMENT = $tp->text_truncate($COMMENT, $menu_pref['comment_characters'],'').($this->var['comment_url'] ? " <a href='".$this->var['comment_url']."'>" : "").defset($menu_pref['comment_postfix'], $menu_pref['comment_postfix']).($this->var['comment_url'] ? "</a>" : "");
			}
		}
		
		return $COMMENT;	
	}
	
}







/*
SC_BEGIN CM_ICON
//TODO review bullet
$bullet = '';
if(defined('BULLET'))
{
	$bullet = '<img src="'.THEME.'images/'.BULLET.'" alt="" class="icon" />';
}
elseif(file_exists(THEME.'images/bullet2.gif'))
{
	$bullet = '<img src="'.THEME.'images/bullet2.gif" alt="" class="icon" />';
}
return $bullet;
SC_END

SC_BEGIN CM_DATESTAMP
$row = e107::getRegistry('plugin/comment_menu/current');
$gen = new convert;
return $gen->convert_date($row['comment_datestamp'], "relative");
SC_END

SC_BEGIN CM_HEADING
$row = e107::getRegistry('plugin/comment_menu/current');
return $row['comment_title'];
SC_END

SC_BEGIN CM_URL_PRE
$row = e107::getRegistry('plugin/comment_menu/current');
return ($row['comment_url'] ? "<a href='".$row['comment_url']."'>" : "");
SC_END

SC_BEGIN CM_URL_POST
$row = e107::getRegistry('plugin/comment_menu/current');
return ($row['comment_url'] ? "</a>" : "");
SC_END

SC_BEGIN CM_TYPE
$row = e107::getRegistry('plugin/comment_menu/current');
return $row['comment_type'];
SC_END

SC_BEGIN CM_AUTHOR
$row = e107::getRegistry('plugin/comment_menu/current');
return $row['comment_author'];
SC_END

SC_BEGIN CM_COMMENT
$row = e107::getRegistry('plugin/comment_menu/current');
$menu_pref = e107::getConfig('menu')->getPref();
$tp = e107::getParser();
$COMMENT = '';

if($menu_pref['comment_characters'] > 0)
{
  $COMMENT = strip_tags($tp->toHTML($row['comment_comment'], TRUE, "emotes_off, no_make_clickable", "", e107::getPref('menu_wordwrap')));
  if ($tp->ustrlen($COMMENT) > $menu_pref['comment_characters'])
  {
	$COMMENT = $tp->text_truncate($COMMENT, $menu_pref['comment_characters'],'').($row['comment_url'] ? " <a href='".$row['comment_url']."'>" : "").defset($menu_pref['comment_postfix'], $menu_pref['comment_postfix']).($row['comment_url'] ? "</a>" : "");
  }
}
return $COMMENT;
SC_END

*/
?>