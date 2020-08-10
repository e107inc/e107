<?php 
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Comment menu
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/comment_menu/comment_menu.php,v $
 * $Revision$
 * $Date$
 * $Author$
*/

if (!defined('e107_INIT'))
{
	exit;
}

	/**
	 * @todo upgrade prefs usage and use e_menu.php instead of config.php
	 */

/*
	if(is_string($parm))
	{
		parse_str($parm, $parms);
	}
	else
	{
		$parms = $parm;
	}
*/

require_once (e_PLUGIN."comment_menu/comment_menu_shortcodes.php");

$cobj = e107::getObject('comment');

if (file_exists(THEME."templates/comment_menu/comment_menu_template.php"))
{
	require_once (THEME."templates/comment_menu/comment_menu_template.php");
}
elseif (file_exists(THEME."comment_menu_template.php"))
{
	require_once (THEME."comment_menu_template.php");
}
else
{
	require_once(e_PLUGIN."comment_menu/comment_menu_template.php");
}
global $menu_pref;



$data = $cobj->getCommentData(intval($menu_pref['comment_display']));

$text = '';
// no posts yet ..
if (empty($data) || !is_array($data))
{
	$text = CM_L1;
}

if(!is_array($COMMENT_MENU_TEMPLATE)) // Convert to v2.x standard. 
{
	$TEMPLATE = array();
	$TEMPLATE['start'] = "";
	$TEMPLATE['item'] = $COMMENT_MENU_TEMPLATE;
	$TEMPLATE['end'] = "";		
}
else 
{
	$TEMPLATE = $COMMENT_MENU_TEMPLATE;	
}

$comment_menu_shortcodes = new comment_menu_shortcodes;

$text .= $tp->parseTemplate($TEMPLATE['start'], true, $comment_menu_shortcodes);

foreach ($data as $row)
{
	//e107::setRegistry('plugin/comment_menu/current', $row);
	$comment_menu_shortcodes->setVars($row);
	$text .= $tp->parseTemplate($TEMPLATE['item'], true, $comment_menu_shortcodes);
}

$text .= $tp->parseTemplate($TEMPLATE['end'], true, $comment_menu_shortcodes);

//e107::setRegistry('plugin/comment_menu/current', null);

$title = e107::getConfig('menu')->get('comment_caption');

if(!empty($title[e_LANGUAGE][e_LANGUAGE])) // fix for saving bug.
{
	$title = $title[e_LANGUAGE][e_LANGUAGE];
}
elseif(!empty($title[e_LANGUAGE]))
{
	$title = $title[e_LANGUAGE];
}


if(empty($title))
{
	$title = LAN_COMMENTS;
}

e107::getRender()->tablerender(defset($title, $title), $text, 'comment_menu');


