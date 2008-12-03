<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id: main.php,v 1.3 2008-12-03 12:38:07 secretr Exp $
 *
 * eURL configuration script
*/
function url_news_main($parms)
{
	$base = e_HTTP.'news.php?'.$parms['action'];
	switch ($parms['action'])
	{
		case 'all':
			return $base;
		case 'cat':
		case 'extend':
		case 'list'://TODO - find out what are list params
		case 'month': //TODO - find out what are month params
		case 'day': //TODO - find out what are day params
			return $base.'.'.varsettrue($parms['value'],'0');
		case 'item':
		case 'default':
			return $base.".{$parms['value1']}.".varset($parms['value2'], '0');
		case 'nextprev':
			return  e_HTTP."news.php?{$parms['to_action']}.{$parms['subaction']}.[FROM]";

		default:
			return false;
	}

}

?>