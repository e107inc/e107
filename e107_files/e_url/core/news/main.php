<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 *
 * eURL configuration script
*/

if (!defined('e107_INIT')) { exit; }

define('NEWS_REWRITE', false);

function url_news_main($parms)
{
	$base = e_HTTP.'news.php?'.$parms['action'];
	switch ($parms['action'])
	{
		//Main news page
		case 'all':
			return $base;
		break;
		
		//Category List
		case 'cat':
		case 'list'://TODO - find out what are list params
			return $base.'.'.varsettrue($parms['id'],'0').(varsettrue($parms['page']) ? '.'.$parms['page'] : '');
		break;
		
		//Item page
		case 'item':
		case 'extend':
			return $base.'.'.varsettrue($parms['id'],'0');
		break;
		
		//Category List default (no category ID)
		case 'default':
			return $base.".{$parms['value1']}.".varset($parms['value2'], '0');
		break;

		//Category List by date/month
		case 'month': //TODO - find out what are month params
		case 'day': //TODO - find out what are day params
			return $base.'-'.varsettrue($parms['id'],'0');
		break;
		
		case 'nextprev':
			return  e_HTTP."news.php?{$parms['to_action']}.{$parms['subaction']}.[FROM]";

		default:
			return  e_HTTP.'news.php';
		break;
	}

}

function parse_url_news_main($request)
{
	return explode('.', $request);
}
