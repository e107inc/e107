<?php

if (!defined('e107_INIT')) { exit; }


$sql = e107::getDb();
$ns = e107::getRender();
$tp = e107::getParser();

$template = e107::getCoreTemplate('page','panel');

//TODO Limits and cache etc.
$qb = $sql->createQueryBuilder();
$data = $qb->select('*')->from('page')
	->whereIn('page_class', explode(',', USERCLASS_LIST))
	->where($qb->expr()->findInSet('page_template', 'panel'))
	->setMaxResults(3)
	->fetchAll();
if(!$data)
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


