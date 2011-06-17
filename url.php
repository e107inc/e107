<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2011 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 * $URL: /cvs_backup/e107_0.8/e107_admin/db_verify.php,v $
 * $Revision: 12255 $
 * $Id: 2011-06-07 17:16:42 -0700 (Tue, 07 Jun 2011) $
 * $Author: e107coders $
 *
*/
define("e_SELF_DISABLE",TRUE);
define("e_QUERY_DISABLE",TRUE);
 exit;
require_once("class2.php");
require_once(HEADERF);


// new eUrl Draft. 
// TODO Move to e107_handlers/eUrl.php once complete. 
class eUrl
{
	protected 	$urlPath;
	protected 	$urlSrch;
	protected 	$include;
	
	public		$incFile;
	
				
	function __construct()
	{
	
		$tp = e107::getParser();
		
		$tmp = str_replace(e_HTTP,'',e_REQUEST_HTTP);
		list($urlPath,$urlSrch) = explode("/",$tmp,2);

		if($urlSrch)
		{
			$this->urlPath 	= $urlPath;
			$this->urlSrch	= $urlSrch;
		}
		else // Root position SEF Url. 
		{
			$this->urlPath	= "";
			$this->urlSrch	= $urlPath;		
		}	
				
		$this->include = $this->getInclude(); //TODO Clean and Check returned URL. 
		
		list($self,$query)	= explode("?",$this->include);
		$this->incFile		= $tp->replaceConstants($self);

		if(!$query && $_SERVER['QUERY_STRING'])
		{
			$e_QUERY = str_replace(array('{', '}', '%7B', '%7b', '%7D', '%7d'), '', rawurldecode($_SERVER['QUERY_STRING']));
			$e_QUERY = str_replace('&', '&amp;', $tp->post_toForm($e_QUERY));
			$query = $e_QUERY;	
		}

		define("e_SELF", e_REQUEST_SELF);
		define("e_QUERY", $query);
	}
	
	
	function getInclude()
	{
		// Check SiteLinks First
		$lnk = e107::getSitelinks();
		$links = $lnk->getlinks(0);
		if(isset($lnk->sefList[$this->urlSrch]))
		{
			return $lnk->sefList[$this->urlSrch];
		}
		
		// Check Plugins (including News and Pages)
		$urlConfig = e107::getAddonConfig('e_url');
				
		foreach($urlConfig as $class_name=>$val)
		{
			foreach($val as $p=>$t)
			{
				if((vartrue($t['path']) == $this->urlPath) && vartrue($t['function']))
				{
					if($ret = e107::callMethod($class_name."_url", $t['function'], $this->urlSrch))
					{
						return $ret;
					}	
				}
			}
		}
	}
	
	
	function debug()
	{
		echo "<br />REQUEST=".$_SERVER['REQUEST_URI'];
		echo "<br />URI Path= ".$this->urlPath;	
		echo "<br />URI Found= ".$this->urlSrch;
		echo "<br />Calculated e_SELF= ".$this->include;
		echo "<br />Renewed e_SELF= ".e_SELF;

		echo "<br />e_QUERY= ".e_QUERY;
		echo "<br />Including: ".$this->incFile;
		
		// echo "<br />e_REQUEST_URL= ".e_REQUEST_URL;
		// echo "<br />e_REQUEST_SELF= " . e_REQUEST_SELF; 	// full URL without the QUERY string
		// echo "<br />e_REQUEST_URI= "  .e_REQUEST_URI;		// absolute http path + query string
		// echo "<br />e_REQUEST_HTTP= ". e_REQUEST_HTTP; 	//  SELF URL without the QUERY string and leading domain part
		// echo "<br />e_HTTP= ".e_HTTP;
		// echo "<br />e_SELF= ".e_SELF;
	}
	
	function create()
	{
		
	}
}


$url = new eUrl;
$url->debug();


if($url->incFile)
{
	require_once($url->incFile);			
}


require_once(FOOTERF); // in case of URL failure. 
?>