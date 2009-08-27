<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Simple XML Parser
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/xml_class.php,v $
 * $Revision: 1.14 $
 * $Date: 2009-08-27 13:58:28 $
 * $Author: secretr $
*/

if (!defined('e107_INIT')) { exit; }

/**
 * Simple XML Parser
 *
 * @package e107
 * @category e107_handlers
 * @version 1.1
 * @author McFly
 * @copyright Copyright (c) 2009, e107 Inc.
 */
class xmlClass
{
	/**
	 * Loaded XML string
	 * 
	 * @var string
	 */
	public $xmlFileContents = '';


	/**
	 * Set to FALSE if not enabled (default on initialisation)
	 * Otherwise mirrors the required subset of the loaded XML - set a field FALSE to accept all
	 * ...elements lower down the tree. e.g.:
	 * <code>
	 * $filter = array(
	 * 	'name' => FALSE,
	 * 	'administration' => FALSE,
	 * 	'management' => array('install' => FALSE)
	 * 	);
	 * </code>
	 * 
	 * @see setOptFilter()
	 * @see parseXml()
	 * @see xml2array()
	 * @var mixed
	 */
	public $filter = false; // Optional filter for loaded XML

	/**
	 * Set true to strip all mention of comments from the returned array (default); 
	 * FALSE to return comment markers (object SimpleXMLElement)
	 * 
	 * @see setOptStripComments()
	 * @see parseXml()
	 * @see xml2array()
	 * @var boolean
	 */
	public $stripComments = true; 
	
	/**
	 * Add root element to the result array
	 * Exmple:
	 * <code>
	 * <root>
	 * <tag>some value</tag> 
	 * </root>
	 * </code>
	 * 
	 * if
	 * <code>$_optAddRoot = true;</code>
	 * xml2array() result is array('root' => array('tag' => 'some value'));
	 * 
	 * if
	 * <code>$_optAddRoot = false;</code>
	 * xml2array() result is array('tag' => 'some value');
	 * 
	 * @see xml2array()
	 * @see setOptAddRoot()
	 * @var boolean
	 */
	protected $_optAddRoot = false;
	
	/**
	 * Always return array, even for single first level tag => value pair
	 * Exmple:
	 * <code>
	 * <root>
	 * <tag>some value</tag> 
	 * </root>
	 * </code>
	 * 
	 * if
	 * <code>$_optForceArray = true;</code>
	 * xml2array() result is array('tag' => array('value' => 'some value'));
	 * where 'value' is the value of $_optValueKey
	 * 
	 * If
	 * <code>$_optForceArray = false;</code>
	 * xml2array() result is array('tag' => 'some value');
	 * 
	 * @see xml2array()
	 * @see setOptForceArray()
	 * @var boolean
	 */
	protected $_optForceArray = false;
	
	/**
	 * Key name for simple tag => value pairs
	 * 
	 * @see xml2array()
	 * @see setOptValueKey()
	 * @var string
	 */
	protected $_optValueKey = 'value';

	/**
	 * Constructor - set defaults
	 * 
	 */
	function __constructor()
	{
		$this->reset();
	}
	
	/**
	 * Reset object
	 * 
	 * @param boolean $xml_contents [optional]
	 * @return xmlClass
	 */
	function reset($xml_contents = true)
	{
		if($xml_contents) 
		{ 
			$this->xmlFileContents = ''; 
		}
		$this->filter = false;
		$this->stripComments = true; 
		$this->_optAddRoot = false;
		$this->_optValueKey = 'value';
		$this->_optForceArray = false;
		return $this;
	}

	/**
	 * Set addRoot option
	 * 
	 * @param boolean $flag
	 * @return xmlClass
	 */
	public function setOptAddRoot($flag)
	{
		$this->_optAddRoot = (boolean) $flag;
		return $this;
	}
	
	/**
	 * Set forceArray option
	 * 
	 * @param string $flag
	 * @return xmlClass
	 */
	public function setOptForceArray($flag)
	{
		$this->_optForceArray = (boolean) $flag;
		return $this;
	}
	
	/**
	 * Set valueKey option
	 * 
	 * @param string $str
	 * @return xmlClass
	 */
	public function setOptValueKey($str)
	{
		$this->_optValueKey = trim((string) $str);
		return $this;
	}
	
	/**
	 * Set strpComments option
	 * 
	 * @param boolean $flag
	 * @return xmlClass
	 */
	public function setOptStripComments($flag)
	{
		$this->stripComments = (boolean) $flag;
		return $this;
	}
	
	/**
	 * Set strpComments option
	 * 
	 * @param array $filter
	 * @return xmlClass
	 */
	public function setOptFilter($filter)
	{
		$this->filter = (array) $filter;
		return $this;
	}

	/**
	 * Get Remote file contents
	 * 
	 * @param string $address
	 * @param integer $timeout [optional] seconds
	 * @return string
	 */
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

	/**
	 * Parse $xmlFileContents XML string to array
	 * 
	 * @param string $xml [optional] 
	 * @param boolean $simple [optional] false - use xml2array(), true - use xml_convert_to_array()
	 * @return string
	 */
	function parseXml($xml = '', $simple = true)
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
		$xml = $simple ? $this->xml_convert_to_array($xml, $this->filter, $this->stripComments) : $this->xml2array($xml);
		return $xml;
	}
	
	/**
	 * Advanced XML parser - handles tags with attributes and values
	 * proper. 
	 * TODO - filter (see xml_convert_to_array)
	 * 
	 * @param SimpleXMLElement $xml
	 * @param string $rec_parent used for recursive calls only
	 * @return 
	 */
	function xml2array($xml, $rec_parent = '')
	{
		$ret = array();
		$tags = get_object_vars($xml);
		
		//remove comments
		if($this->stripComments && isset($tags['comment']))
		{
			unset($tags['comment']);
		}
		
		//first call
		if(!$rec_parent)
		{
			//$ret = $this->xml2array($xml, true);
			//repeating code because of the _optForceArray functionality 
			$tags = array_keys($tags); 
			foreach ($tags as $tag)
			{
				if($tag == '@attributes')
				{
					$tmp = (array) $xml->attributes();
					$ret['@attributes'] = $tmp['@attributes'];
					continue;
				}

				$count = count($xml->{$tag});
				if($count > 1)
				{
					for ($i = 0; $i < $count; $i++)
					{
						$ret[$tag][$i] = $this->xml2array($xml->{$tag}[$i], $tag);
					}
					continue;
				}
				$ret[$tag] = $this->xml2array($xml->{$tag}, $tag);
			}
			
			return ($this->_optAddRoot ? array($xml->getName() => $ret) : $ret);
		}

		//Recursive calls start here		
		if($tags)
		{
			$tags = array_keys($tags);
			$count_tags = count($tags);
			
			//loop through tags
			foreach ($tags as $tag)
			{
				switch($tag)
				{
					case '@attributes':
						$tmp = (array) $xml->attributes();
						$ret['@attributes'] = $tmp['@attributes'];
						
						if($count_tags == 1) //only attributes & possible value
						{
							$ret[$this->_optValueKey] = trim((string) $xml);
							return $ret;
						}
					break;
					
					case 'comment':
						$ret[$this->_optValueKey] = trim((string) $xml);
						$ret['comment'] = $xml->comment;
					break;
				
					//more cases?
					default:
						$count = count($xml->{$tag});
						if($count >= 1) //array of elements - loop
						{
							for ($i = 0; $i < $count; $i++)
							{
								$ret[$tag][$i] = $this->xml2array($xml->{$tag}[$i], $tag);
							}
						}
						else //single element
						{
							$ret[$tag] = $this->xml2array($xml->{$tag}, $tag);
						}
					break;
				}
			}
			return $ret;
		}
		
		//parse value only
		$ret = trim((string) $xml);
		return ($this->_optForceArray ? array($this->_optValueKey => $ret) : $ret);
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

	/**
	 * Load XML file and parse it (optional)
	 * 
	 * @param string $fname local or remote XML source file path
	 * @param boolean|string $parse false - no parse; 
	 * 								true - use  xml_convert_to_array(); 
	 * 								in any other case  - use xml2array()
	 * 
	 * @param boolean $replace_constants [optional]
	 * @return mixed
	 */
	function loadXMLfile($fname, $parse = false, $replace_constants = false)
	{
		if (empty($fname))
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
				$this->xmlFileContents = e107::getParser()->replaceConstants($this->xmlFileContents, '', true);
			}
			if ($parse)
			{
				return $this->parseXML('', ($parse === true));
			}
			else
			{
				return $this->xmlFileContents;
			}
		}
		return false;
	}
	
	//FIXME  - TEST - remove me
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

	//FIXME  - TEST - remove me
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

	//FIXME  - TEST - remove me
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

	//FIXME  - TEST - remove me
	function ins2ary(&$ary, $element, $pos)
	{
		$ar1 = array_slice($ary, 0, $pos);
		$ar1[] = $element;
		$ary = array_merge($ar1, array_slice($ary, $pos));
	}
}
