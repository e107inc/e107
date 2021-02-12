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



// $cobj = e107::getObject('comment');

e107::lan('comment_menu', null);
$cobj = e107::getComment();

$menu_pref = e107::getConfig('menu')->getPref();

$data = $cobj->getCommentData(intval($menu_pref['comment_display']));

$text = '';
// no posts yet ..
if (empty($data) || !is_array($data))
{
	$text = CM_L1;
}

if(!$TEMPLATE = e107::getTemplate('comment_menu'))
{
	$COMMENT_MENU_TEMPLATE = null;

	if (file_exists(THEME."templates/comment_menu/comment_menu_template.php"))
	{
		require_once (THEME."templates/comment_menu/comment_menu_template.php");
	}
	elseif (file_exists(THEME."comment_menu_template.php"))
	{
		require_once (THEME."comment_menu_template.php");
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

}

require_once (e_PLUGIN."comment_menu/comment_menu_shortcodes.php");
$sc = e107::getScBatch('comment_menu', true);
$sc->wrapper('comment_menu');

$tp = e107::getParser();

$text .= $tp->parseTemplate($TEMPLATE['start'], true, $sc);

foreach ($data as $row)
{
	//e107::setRegistry('plugin/comment_menu/current', $row);
	$sc->setVars($row);
	$text .= $tp->parseTemplate($TEMPLATE['item'], true, $sc);
}

$text .= $tp->parseTemplate($TEMPLATE['end'], true, $sc);

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


