<?php

if (!defined('e107_INIT')) { exit; }

//require_once(e_HANDLER.'news_class.php');

parse_str($parm, $parm);
$nitem = e107::getObject('e_news_item');
$nitem->load(1);

$template = '
{NEWS_FIELD=title|format=html&arg=0,TITLE}<br />
<em>{NEWS_FIELD=datestamp|format=date}</em><br /><br />
{NEWS_FIELD=body|format=html_truncate&arg=100,...}
';

//New way of parsing batches - pass object, all sc_* methods will be auto-registered
$ret = e107::getParser()->parseTemplate($template, true, $nitem);
e107::getRender()->tablerender('Latest News', $ret, 'latest_news');


//print_a $nitem->getData();
/*
$ret .= '<br /><br />Render Tree<br />';
$ntree = new e_news_tree();
foreach ($ntree->load(1)->getTree() as $nitem) 
{
	$ret .= ' - '.$nitem->get('title').'<br/>';
	//print_a $nitem->getData();
}
*/
