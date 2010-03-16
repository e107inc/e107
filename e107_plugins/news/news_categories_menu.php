<?php

if (!defined('e107_INIT')) { exit; }

$cacheString = 'nq_news_categories_menu_'.preg_replace('#[^\w]#', '', $parm);
$cached = e107::getCache()->retrieve($cacheString);
if(false === $cached)
{
	e107::includeLan(e_PLUGIN.'news/languages/'.e_LANGUAGE.'.php');

	parse_str($parm, $parms);
	$ctree = e107::getObject('e_news_category_tree', null, e_HANDLER.'news_class.php');

	//TODO real template, menu parameters
	$sc_style['NEWS_CATEGORY_NEWS_COUNT']['pre'] = '(';
	$sc_style['NEWS_CATEGORY_NEWS_COUNT']['post'] = ')';

	$template = array();
	$template['item'] = '
		<img src="'.THEME_ABS.'images/bullet2.gif" alt="bullet" class="icon" /> <a class="e-menu-link newscats'.$active.'" href="{NEWS_CATEGORY_URL}">{NEWS_CATEGORY_TITLE} {NEWS_CATEGORY_NEWS_COUNT}</a>
	';
	$template['separator'] = '<br />';

	//always return
	$parms['return'] = true;

	$cached = $ctree->loadActive()->render($parms, true, $template);
	e107::getCache()->set($cacheString, $cached);
}

echo $cached;