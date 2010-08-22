<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
global $tp;
$comment_menu_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);

/*
SC_BEGIN CM_ICON
$bullet = '';
if(defined('BULLET'))
{
	$bullet = '<img src="'.THEME.'images/'.BULLET.'" alt="" style="vertical-align: middle;" />';
}
elseif(file_exists(THEME.'images/bullet2.gif'))
{
	$bullet = '<img src="'.THEME.'images/bullet2.gif" alt="" style="vertical-align: middle;" />';
}
return $bullet;
SC_END

SC_BEGIN CM_DATESTAMP
global $row;
$gen = new convert;
return $gen->convert_date($row['comment_datestamp'], "short");
SC_END

SC_BEGIN CM_HEADING
global $row;
return $row['comment_title'];
SC_END

SC_BEGIN CM_URL_PRE
global $row;
return ($row['comment_url'] ? "<a href='".$row['comment_url']."'>" : "");
SC_END

SC_BEGIN CM_URL_POST
global $row;
return ($row['comment_url'] ? "</a>" : "");
SC_END

SC_BEGIN CM_TYPE
global $row;
return $row['comment_type'];
SC_END

SC_BEGIN CM_AUTHOR
global $row;
return $row['comment_author'];
SC_END

SC_BEGIN CM_COMMENT
global $row, $menu_pref, $pref, $tp;
$COMMENT = '';
if($menu_pref['comment_characters']>0)
{
  $COMMENT = strip_tags($tp->toHTML($row['comment_comment'], TRUE, "emotes_off, no_make_clickable", "", $pref['menu_wordwrap']));
  if (strlen($COMMENT) > $menu_pref['comment_characters'])
  {
	$COMMENT = $tp->text_truncate($COMMENT, $menu_pref['comment_characters'],'').($row['comment_url'] ? " <a href='".$row['comment_url']."'>" : "").$menu_pref['comment_postfix'].($row['comment_url'] ? "</a>" : "");
  }
}
return $COMMENT;
SC_END

*/
?>