<?php
/*
* Copyright (c) e107 Inc 2015 e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
*
* Log Stats shortcode batch class - shortcodes available site-wide. ie. equivalent to multiple .sc files.
*/

if (!defined('e107_INIT')) { exit; }

class log_shortcodes extends e_shortcode
{

	private $dbPageInfo;
	private $lgc;

	function __construct()
	{

		require_once(e_PLUGIN."log/consolidate.php");
		$this->lgc = new logConsolidate;

		$sql = e107::getDB();
		$logfile = e_LOG.'logp_'.date('z.Y', time()).'.php'; /* get today's logfile ... */

		$pageInfo = array();

		if(is_readable($logfile)) // populate $pageInfo
		{
			require($logfile);
		}

		$logfile = e_LOG.'logi_'.date('z.Y', time()).'.php'; //	$logfile = e_PLUGIN.'log/logs/logi_'.date('z.Y', time()).'.php';

		if(is_readable($logfile))
		{
			require($logfile);
		//	e107::getMessage()->addDebug("Loading Log File: ".$logfile);
		}


		if($sql->select('logstats', 'log_data', "log_id='pageTotal'")) /* get main stat info from database */
		{
			$row = $sql->fetch();
			$this->dbPageInfo = unserialize($row['log_data']);
	//		e107::getMessage()->addDebug("Loading Logstats from DB: ".print_a($this->dbPageInfo,true));
		}
		else
		{
			$this->dbPageInfo = array();
		}

		/* temp consolidate today's info (if it exists)... */
		if(is_array($pageInfo))
		{
			foreach($pageInfo as $key => $info)
			{
				$key = preg_replace("/\?.*/", "", $key);
				if(array_key_exists($key, $this -> dbPageInfo))
				{
					$this -> dbPageInfo[$key]['ttlv'] += $info['ttl'];
					$this -> dbPageInfo[$key]['unqv'] += $info['unq'];
				}
				else
				{
					$this -> dbPageInfo[$key]['url'] = $info['url'];
					$this -> dbPageInfo[$key]['ttlv'] = $info['ttl'];
					$this -> dbPageInfo[$key]['unqv'] = $info['unq'];
				}
			}
		}


	}


	private function getKey($self)
	{
		return $this->lgc->getPageKey($self, false, '', e_LAN);
		//$base = basename($self);
	//	list($url,$qry) = explode(".",$base, 2);
	//	return $url;
	}


	function sc_log_pagecounter($parm)
	{

		//print_a($this->dbPageInfo);

		$url = str_replace("www.", "", e_REQUEST_URL);
		$id = $this->getKey(e_REQUEST_URL);
	//	print_a("Checking for: ".$id);

	//	print_a($this->dbPageInfo);

		if(isset($this->dbPageInfo[$id]['url']) && ($this->dbPageInfo[$id]['url'] == e_REQUEST_URL || $this->dbPageInfo[$id]['url'] == $url))
		{
			return ($parm == 'unique') ? number_format($this->dbPageInfo[$id]['unqv']) : number_format($this->dbPageInfo[$id]['ttlv']);
		}

	}

}



