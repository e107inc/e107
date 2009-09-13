<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id: main.php,v 1.3 2009-09-13 16:37:18 secretr Exp $
 *
 * eURL configuration script
*/
function url_news_main($parms)
{
	//$base = e_HTTP.$sefbase.$parms['action'];
	$base = e_HTTP.(e107::getPref('news_sefbase') ? e107::getPref('news_sefbase').'/' : '');
	switch ($parms['action'])
	{
		//Main news page
		case 'all':
			return $base;
		break;
		
		//Category List
		case 'cat':
		case 'list'://TODO - find out what are list params
			return $base.varsettrue($parms['sef'], $parms['action'].'/'.$parms['id']).(varsettrue($parms['page']) ? '/'.$parms['page'] : '');
		break;
		
		//Item page
		case 'item':
		case 'extend':
			return $base.varsettrue($parms['sef'], $parms['action'].'/'.$parms['id']);
		break;
		
		//Category List default (no category ID)
		case 'default':
			return $base."default/{$parms['value1']}-".varset($parms['value2'], '0');
		break;

		//Category List by date/month
		case 'month': 
		case 'day': 
			return $base.$parms['action'].'/'.varsettrue($parms['value'],'0');
		break;
		
		case 'nextprev':
			return  e_HTTP."news/{$parms['to_action']}/{$parms['subaction']}/[FROM].html";

		default:
			return $base;
		break;
	}

}
