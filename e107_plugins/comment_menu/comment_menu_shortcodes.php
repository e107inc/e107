<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Comment menu shortcodes
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/comment_menu/comment_menu_shortcodes.php,v $
 * $Revision: 1.6 $
 * $Date: 2009-10-08 14:53:37 $
 * $Author: secretr $
*/

if (!defined('e107_INIT')) { exit; }
global $tp;
$comment_menu_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);
e107::getRegistry('plugin/comment_menu/current');
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
return $gen->convert_date($row['comment_datestamp'], "short");
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
  if ($tp->uStrLen($COMMENT) > $menu_pref['comment_characters'])
  {
	$COMMENT = $tp->text_truncate($COMMENT, $menu_pref['comment_characters'],'').($row['comment_url'] ? " <a href='".$row['comment_url']."'>" : "").defset($menu_pref['comment_postfix'], $menu_pref['comment_postfix']).($row['comment_url'] ? "</a>" : "");
  }
}
return $COMMENT;
SC_END

*/
?>