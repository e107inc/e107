<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id: main.php,v 1.1 2009-09-25 20:13:12 secretr Exp $
 *
 * eURL configuration script
*/

if (!defined('e107_INIT')) { exit; }
define('TAGWORDS_REWRITE', false);

function url_tagwords_main($parms = array())
{
	$base = e_PLUGIN_ABS.'tagwords/tagwords.php';
	if(isset($parms['q'])) return $base.'?q='.urlencode(varset($parms['q']));
	return $base;

}

function parse_url_tagwords_main($request)
{
	parse_str($request, $parsed);
	return $parsed;
}
