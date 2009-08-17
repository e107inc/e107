<?php

if (!defined('e107_INIT')) { exit; }

require_once(e_HANDLER.'news_class.php');

parse_str($parm, $parm);

$ret = 'Menu parameters<br />';
$ret .= '<pre>'.var_export($parm, true).'</pre><br /><br />';

$ret .= 'Render Item<br />';
//$tmpl = e107::getCoreTemplate($parm['tmpl']);
$nitem = new e_news_item();
$ret .= $nitem->load(1)->get('title');
//print_a $nitem->getData();

$ret .= '<br /><br />Render Tree<br />';
$ntree = new e_news_tree();
foreach ($ntree->load(1)->getTree() as $nitem) 
{
	$ret .= ' - '.$nitem->get('title').'<br/>';
	//print_a $nitem->getData();
}
e107::getRender()->tablerender('Latest News', $ret, 'latest_news');

