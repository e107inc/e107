<?php

if (!defined('e107_INIT')) { exit; }


$sql = e107::getDb();
$ns = e107::getRender();
$tp = e107::getParser();

$template = e107::getCoreTemplate('page','panel');

//TODO Limits and cache etc. 
if(!$data = $sql->retrieve("SELECT * FROM #page WHERE page_class IN (".USERCLASS_LIST.") AND FIND_IN_SET('panel', page_template) LIMIT 3", true))
{
	if(ADMIN)
	{
		echo "<div class='alert alert-danger'>There are no page items assigned to the 'panel' template.</div>";
	}
	return null;
}

//TODO Use shortcodes and template. 
foreach($data as $row)
{
	$title = $tp->toHTML($row['page_title'],false,'TITLE');
	$body = $tp->toHTML($row['page_text'],true,'BODY');
	
	
	$ns->tablerender($title, $body,'page-menu'); // check for $mode == 'page-menu' in tablestyle() if you need a simple 'echo' without rendering styles. 
}


