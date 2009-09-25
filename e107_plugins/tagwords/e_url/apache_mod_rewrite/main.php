<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id: main.php,v 1.1 2009-09-25 20:13:12 secretr Exp $
 *
 * eURL configuration script
*/

if (!defined('e107_INIT')) { exit; }

require_once(e_HANDLER.'news_class.php');
define('TAGWORDS_REWRITE', true);

function url_tagwords_main($parms = array())
{
	//$base = e_HTTP.$sefbase.$parms['action'];
	$base = e_HTTP.(e107::getPref('tagwords_sefbase') ? e107::getPref('tagwords_sefbase').'/' : 'tagwords/');
	return $base.urlencode(varset($parms['q']));
}

function parse_url_tagwords_main($request)
{
	$request = urldecode($request);
	parse_str($request, $request);

	if(isset($request['q']))
	{
		return array('q' => $request['q']);
	}
	
	return $request;
}

