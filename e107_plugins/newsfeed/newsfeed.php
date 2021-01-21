<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin - newsfeeds
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/newsfeed/newsfeed.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/
if(!defined('e107_INIT'))
{
    require_once(__DIR__.'/../../class2.php');
}

if (!e107::isInstalled('newsfeed')) 
{
	e107::redirect();
	exit;
}

e107::includeLan(e_PLUGIN.'newsfeed/languages/'.e_LANGUAGE.'_newsfeed.php');
if(!class_exists('newsfeedClass'))
{
	require(e_PLUGIN.'newsfeed/newsfeed_functions.php');
}
global $newsFeed;
if (!is_object($newsFeed)) 
{
	$newsFeed = new newsfeedClass;
}

e107::css('inline', "

.newsfeed ul { max-width:100% }
.newsfeed img { max-width:100% }


");


require_once(HEADERF);

/* get template */
if(file_exists(THEME."templates/newsfeed/newsfeed_template.php"))
{
	include(THEME."templates/newsfeed/newsfeed_template.php");
}
elseif (file_exists(THEME."newsfeed_template.php"))
{
	include(THEME."newsfeed_template.php");
}
else if(!varset($NEWSFEED_LIST_START, FALSE))
{
	include(e_PLUGIN."newsfeed/templates/newsfeed_template.php");
}

$action = FALSE;
if(e_QUERY)
{
	$qs = explode(".", e_QUERY);
	$action = $qs[0];
	$id = intval(varset($qs[1], 0));
}

if(!empty($_GET['id'])) //v2.x
{
	$id = intval($_GET['id']);
	$action = 'show';
}

if($action == "show")
{
	/* 'show' action - show feed */

	$data = $newsFeed->newsfeedInfo($id == 0 ? 'all' : $id, 'main');
	$ns->tablerender($data['title'], $data['text']);
	require_once(FOOTERF);
	exit;
}

	
/* no action - display feed list ... */
$newsFeed->readFeedList();
$vars = array();
if (count($newsFeed->feedList))
{
	$data = "";
	foreach ($newsFeed->feedList as $feed)
	{
		if (($feed['newsfeed_active'] == 2) || ($feed['newsfeed_active'] == 3))
		{

			$feed['newsfeed_sef'] = eHelper::title2sef($feed['newsfeed_name'], 'dashl');

			$url = e107::url('newsfeed', 'source', $feed); // e_SELF."?show.{$feed['newsfeed_id']}

			$vars['FEEDNAME'] = "<a href='".$url."'>{$feed['newsfeed_name']}</a>";
			$vars['FEEDDESCRIPTION'] = ((!$feed['newsfeed_description'] || $feed['newsfeed_description'] == "default") ? "&nbsp;" : $feed['newsfeed_description']);
//			$FEEDIMAGE = $feed['newsfeed_image'];	// This needs splitting up. Not used ATM anyway, so disable for now
			$data .= $tp->simpleParse($NEWSFEED_LIST, $vars);
		}
	}
}

$text = $NEWSFEED_LIST_START . vartrue($data) . $NEWSFEED_LIST_END;
$ns->tablerender(NFLAN_29, $text);
require_once(FOOTERF);

