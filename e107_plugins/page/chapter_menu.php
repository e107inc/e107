<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Purpose: Display a Menu item (panel) for each 'chapter' of a specific book. ie. the chapter-equivalent of {CMENU} . 
 * @example in theme.php:  {MENU: path=page/chapter&book=1} to render all chapters in book 1. 
 */


if (!defined('e107_INIT')) { exit; }


$sql = e107::getDb();
$ns = e107::getRender();
$tp = e107::getParser();

$parm = eHelper::scParams($parm);

$template = e107::getCoreTemplate('chapter','panel');

//TODO Limits and cache etc.
$qb = $sql->createQueryBuilder();
$qb->select('*')->from('page_chapters')
	->whereIn('chapter_visibility', array_map('intval', explode(',', USERCLASS_LIST)))
	->where('chapter_template', 'panel');

if(vartrue($parm['book']))
{
	$qb->where('chapter_parent', (int) $parm['book']);
}

$data = $qb->setMaxResults(24)->fetchAll();
$sc = null;

if(!empty($data))
{
	$sc = e107::getScBatch('page', null, 'cpage');

	$body = $template['listChapters']['start'];

	foreach($data as $row)
	{
		$sc->setVars($row);

		$sc->setChapter($row['chapter_id']);
		$title = $tp->toHTML($row['chapter_name'],false,'TITLE'); // Used when tablerender style includes the caption.
		$body .= $tp->parseTemplate($template['listChapters']['item'], true, $sc);

		// check for $mode == 'page-menu' in tablestyle() if you need a simple 'echo' without rendering styles.
	}

	$body .= $template['listChapters']['end'];
}
elseif(ADMIN)
{
	$body = "<div class='alert alert-danger'>No Chapters available</div>";
}


$caption = $tp->parseTemplate($template['listChapters']['caption'], true, $sc);

$ns->tablerender($caption, $body, 'chapter-menu'); 

