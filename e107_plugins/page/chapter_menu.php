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

$insert = (vartrue($parm['book'])) ? "AND chapter_parent = ".intval($parm['book']) : '';

//TODO Limits and cache etc. 
$data = $sql->retrieve("SELECT * FROM #page_chapters WHERE chapter_visibility IN (".USERCLASS_LIST.") AND chapter_template = 'panel'  ".$insert. " LIMIT 24", true);

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

$caption = $tp->parseTemplate($template['listChapters']['caption'], true, $sc);

$ns->tablerender($caption, $body, 'chapter-menu'); 

