<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }
if (!e107::isInstalled('featurebox'))
{
	e107::redirect();
	exit;
}


$fbRows = e107::getDb()->createQueryBuilder()
	->select('*')->from('featurebox')
	->where('fb_mode', 1)
	->whereIn('fb_class', explode(',', USERCLASS_LIST))
	->orderBy('fb_class', 'ASC')
	->fetchAll();

if($fbRows)
{
	foreach($fbRows as $row)
	{
		if($row['fb_class'] > 0 && $row['fb_class'] < 251)
		{
			extract($row);
			continue;
		}
		else
		{
			$xentry = $row;
		}
	}
	if(!isset($fb_title))
	{
		extract($xentry);
	}
}
else
{
	$nfArray = e107::getDb()->createQueryBuilder()
		->select('*')->from('featurebox')
		->where('fb_mode', '!=', 1)
		->whereIn('fb_class', explode(',', USERCLASS_LIST))
		->fetchAll();

	if($nfArray)
	{
		$entry = $nfArray[array_rand($nfArray)];
		extract($entry);
	}
	else
	{
		return FALSE;
	}
}

$fbcc = $fb_title;
$fb_title = $tp->toHTML($fb_title, TRUE,'title');
$fb_text = $tp->toHTML($fb_text, TRUE,'body');
if(!$fb_rendertype)
{
	$ns->tablerender($fb_title, $fb_text, 'featurebox');
}
else 
{
	require_once(e_PLUGIN."featurebox/templates/".$fb_template.".php");
	echo $FB_TEMPLATE;
}
