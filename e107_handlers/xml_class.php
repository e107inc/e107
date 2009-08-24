<?php
/*
 + ----------------------------------------------------------------------------+
 |     e107 website system
 |
 |     �Steve Dunstan 2001-2002
 |     http://e107.org
 |     jalist@e107.org
 |
 |     Released under the terms and conditions of the
 |     GNU General Public License (http://gnu.org).
 |
 |     $Source: /cvs_backup/e107_0.8/e107_handlers/xml_class.php,v $
 |     $Revision: 1.13 $
 |     $Date: 2009-08-24 00:58:01 $
 |     $Author: e107coders $
 +----------------------------------------------------------------------------+
 */


class xmlClass
{

	var $xmlFileContents;

	var $filter; // Optional filter for loaded XML
	// Set to FALSE if not enabled (default on initialisation)
	// Otherwise mirrors the required subset of the loaded XML - set a field FALSE to accept all
	// ...elements lower down the tree. e.g.:
	// $filter = array(
	//		'name' => FALSE,
	//		'administration' => FALSE,
	//		'management' => array('install' => FALSE)
	//		);

	var $stripComments; // Set true to strip all mention of comments from the returned array (default); FALSE to return comment markers
	// Constructor - set defaults


	function xmlClass()
	{
		$this->xmlFileContents = '';
		$this->filter = FALSE;
		$this->stripComments = TRUE; // Don't usually want comments back
	}


	function getRemoteFile($address, $timeout = 10)
	{
		// Could do something like: if ($timeout <= 0) $timeout = $pref['get_remote_timeout'];  here
		$timeout = min($timeout, 120);
		$timeout = max($timeout, 3);
		$address = str_replace(array("\r", "\n", "\t"), '', $address); // May be paranoia, but streaky thought it might be a good idea
		// ... and there shouldn't be unprintable characters in the URL anyway
		if (function_exists('file_get_contents'))
		{
			$old_timeout = e107_ini_set('default_socket_timeout', $timeout);
			$data = file_get_contents(urlencode($address));
			echo "data=".$data;
			//		  $data = file_get_contents(htmlspecialchars($address));	// buggy - sometimes fails.
			if ($old_timeout !== FALSE)
			{
				e107_ini_set('default_socket_timeout', $old_timeout);
			}
			if ($data)
			{
				return $data;
			}
		}
		if (function_exists("curl_init"))
		{
			$cu = curl_init();
			curl_setopt($cu, CURLOPT_URL, $address);
			curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($cu, CURLOPT_HEADER, 0);
			curl_setopt($cu, CURLOPT_TIMEOUT, $timeout);
			$this->xmlFileContents = curl_exec($cu);
			if (curl_error($cu))
			{
				$this->error = "Curl error: ".curl_errno($cu).", ".curl_error($cu);
				return FALSE;
			}
			curl_close($cu);
			return $this->xmlFileContents;
		}
		if (ini_get("allow_url_fopen"))
		{
			$old_timeout = e107_ini_set('default_socket_timeout', $timeout);
			$remote = @fopen($address, "r");
			if (!$remote)
			{
				$this->error = "fopen: Unable to open remote XML file: ".$address;
				return FALSE;
			}
		}
		else
		{
			$old_timeout = $timeout;
			$tmp = parse_url($address);
			if (!$remote = fsockopen($tmp['host'], 80, $errno, $errstr, $timeout))
			{
				$this->error = "Sockets: Unable to open remote XML file: ".$address;
				return FALSE;
			}
			else
			{
				socket_set_timeout($remote, $timeout);
				fputs($remote, "GET ".urlencode($address)." HTTP/1.0\r\n\r\n");
			}
		}
		$this->xmlFileContents = "";
		while (!feof($remote))
		{
			$this->xmlFileContents .= fgets($remote, 4096);
		}
		fclose($remote);
		if ($old_timeout != $timeout)
		{
			if ($old_timeout !== FALSE)
			{
				e107_ini_set('default_socket_timeout', $old_timeout);
			}
		}
		return $this->xmlFileContents;
	}


	function parseXml($xml = '')
	{
		if ($xml == '' && $this->xmlFileContents)
		{
			$xml = $this->xmlFileContents;
		}
		if (!$xml)
		{
			return false;
		}
		$xml = simplexml_load_string($xml);
		if (is_object($xml))
		{
			$xml = (array) $xml;
		}
		$xml = $this->xml_convert_to_array($xml, $this->filter, $this->stripComments);
		return $xml;
	}


	function xml_convert_to_array($xml, $localFilter = FALSE, $stripComments = TRUE)
	{
		if (is_array($xml))
		{
			foreach ($xml as $k=>$v)
			{
				if ($stripComments && ($k === 'comment'))
				{
					unset($xml[$k]);
					continue;
				}
				$enabled = FALSE;
				if ($localFilter === FALSE)
				{
					$enabled = TRUE;
					$onFilter = FALSE;
				}
				elseif (isset($localFilter[$k]))
				{
					$enabled = TRUE;
					$onFilter = $localFilter[$k];
				}
				if ($enabled)
				{
					if (is_object($v))
					{
						$v = (array) $v;
					}
					$xml[$k] = $this->xml_convert_to_array($v, $onFilter, $stripComments);
				}
				else
				{
					unset($xml[$k]);
				}
			}
			if (count($xml) == 1 && isset($xml[0]))
			{
				$xml = $xml[0];
			}
		}
		return $xml;
	}


	function loadXMLfile($fname = '', $parse = false, $replace_constants = false)
	{
		if ($fname == '')
		{
			return false;
		}
		$xml = false;
		if (strpos($fname, '://') !== false)
		{
			$this->getRemoteFile($fname);
		}
		else
		{
			if ($xml = file_get_contents($fname))
			{
				$this->xmlFileContents = $xml;
			}
		}
		if ($this->xmlFileContents)
		{
			if ($replace_constants == true)
			{
				global $tp;
				if (!is_object($tp))
				{
					require_once ('e_parse_class.php');
					$tp = new e_parse;
				}
				$this->xmlFileContents = $tp->replaceConstants($this->xmlFileContents, '', true);
			}
			if ($parse == true)
			{
				return $this->parseXML('');
			}
			else
			{
				return $this->xmlFileContents;
			}
		}
		return false;
	}


	function xml2ary(&$string)
	{
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parse_into_struct($parser, $string, $vals, $index);
		xml_parser_free($parser);
		$mnary = array();
		$ary = &$mnary;
		foreach ($vals as $r)
		{
			$t = $r['tag'];
			if ($r['type'] == 'open')
			{
				if (isset($ary[$t]))
				{
					if (isset($ary[$t][0]))
						$ary[$t][] = array();
					else
						$ary[$t] = array($ary[$t], array());
					$cv = &$ary[$t][count($ary[$t]) - 1];
				}
				else
					$cv = &$ary[$t];
				if (isset($r['attributes']))
				{
					foreach ($r['attributes'] as $k=>$v)
						$cv['_a'][$k] = $v;
				}
				$cv['_c'] = array();
				$cv['_c']['_p'] = &$ary;
				$ary = &$cv['_c'];
			}
			elseif ($r['type'] == 'complete')
			{
				if (isset($ary[$t]))
				{ // same as open
					if (isset($ary[$t][0]))
						$ary[$t][] = array();
					else
						$ary[$t] = array($ary[$t], array());
					$cv = &$ary[$t][count($ary[$t]) - 1];
				}
				else
					$cv = &$ary[$t];
				if (isset($r['attributes']))
				{
					foreach ($r['attributes'] as $k=>$v)
						$cv['_a'][$k] = $v;
				}
				$cv['_v'] = (isset($r['value']) ? $r['value'] : '');
			}
			elseif ($r['type'] == 'close')
			{
				$ary = &$ary['_p'];
			}
		}
		$this->_del_p($mnary);
		return $mnary;
	}


	function _del_p(&$ary)
	{
		foreach ($ary as $k=>$v)
		{
			if ($k === '_p')
				unset($ary[$k]);
			elseif (is_array($ary[$k]))
				$this->_del_p($ary[$k]);
		}
	}
	// Array to XML


	function ary2xml($cary, $d = 0, $forcetag = '')
	{
		$res = array();
		foreach ($cary as $tag=>$r)
		{
			if (isset($r[0]))
			{
				$res[] = $this->ary2xml($r, $d, $tag);
			}
			else
			{
				if ($forcetag)
					$tag = $forcetag;
				$sp = str_repeat("\t", $d);
				$res[] = "$sp<$tag";
				if (isset($r['_a']))
				{
					foreach ($r['_a'] as $at=>$av)
						$res[] = " $at=\"$av\"";
				}
				$res[] = ">".((isset($r['_c'])) ? "\n" : '');
				if (isset($r['_c']))
					$res[] = $this->ary2xml($r['_c'], $d + 1);
				elseif (isset($r['_v']))
					$res[] = $r['_v'];
				$res[] = (isset($r['_c']) ? $sp : '')."</$tag>\n";
			}
		}
		return implode('', $res);
	}
	// Insert element into array


	function ins2ary(&$ary, $element, $pos)
	{
		$ar1 = array_slice($ary, 0, $pos);
		$ar1[] = $element;
		$ary = array_merge($ar1, array_slice($ary, $pos));
	}
}



?>
