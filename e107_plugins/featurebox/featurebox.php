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


if($sql->select("featurebox", "*", "fb_mode=1 AND fb_class IN (".USERCLASS_LIST.") ORDER BY fb_class ASC"))
{
	while($row = $sql->fetch())
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
else if($sql->select("featurebox", "*", "fb_mode!=1 AND fb_class IN (".USERCLASS_LIST.")"))
{
	$nfArray = $sql->db_getList();
	$entry = $nfArray[array_rand($nfArray)];
	extract($entry);
}
else
{
	return FALSE;
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
