<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 *
 * eURL configuration script
*/

if (!defined('e107_INIT')) { exit; }

require_once(e_HANDLER.'news_class.php');
define('NEWS_REWRITE', true);

function url_news_main($parms = array())
{
	//$base = e_HTTP.$sefbase.$parms['action'];
	$base = e_HTTP.(e107::getPref('news_sefbase') ? e107::getPref('news_sefbase').'/' : '');
	
	if(isset($parms['sef']))
	{
		$parms['sef'] = urlencode($parms['sef']);
	}
	elseif(vartrue($parms['id']))
	{
		//'sef' is not set, which means we don't know it!
		$parms['sef'] = urlencode(news::retrieveRewriteString($parms['id'], $parms['action']));
	}
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

function parse_url_news_main($request)
{
	static $_parsed_request = array();
	$known_actions = array('cat', 'list', 'item', 'extend', 'default', 'month', 'day');
	
	//runtime cache
	if(isset($_parsed_request[$request]))
	{
		return $_parsed_request[$request];
	}
	
	$request_str = $request;
	parse_str($request, $request);
	if(!isset($request['rewrite']))
	{
		$_parsed_request[''] = array();
		return array(); 
	}
	
	$chunks = explode('/', $request['rewrite']);
	
	//action found in the request
	if(in_array($chunks[0], $known_actions))
	{
		$_parsed_request[$request_str] = $chunks;
		return $_parsed_request[$request_str];
	}
	
	//sef string
	if(!($sefdata = news::getRewriteCache($chunks[0])))
	{
		$sefdata = news::retrieveRewriteData($chunks[0], true);
	}
	
	//not found - redirect
	if(empty($sefdata))
	{
		if(!session_id()) session_start();
		$_SESSION['e107_http_referer'] = ltrim(SITEURL, '/').url_news_main().$request['rewrite'];
		$_SESSION['e107_error_return'] = array(url_news_main() => 'Go to News front page'); //TODO - LANs
		session_write_close();
		header('HTTP/1.1 404 Not Found', true);
		header('Location: '.SITEURL.'error.php?404');
		exit;	
	}
	
	$parsed = array();
	switch($sefdata['news_rewrite_type'])
	{
		case '2': //Category list
			$parsed = array('list', $sefdata['news_rewrite_source']);
		break;
	
		case '1': //Item view
			$parsed = array('extend', $sefdata['news_rewrite_source']);
		break;
	}
	
	if(count($chinks) > 1)
	{
		
		$parsed = array_merge($parsed, array_slice($chunks, 1));
	}
	
	$_parsed_request[$request_str] = $parsed;
	return $_parsed_request[$request_str];
}

