<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2011 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Simple XML Parser
 *
 * $URL $
 * $Id$
*/

if (!defined('e107_INIT')) { exit; }


class parseXml extends xmlClass // BC with v1.x
{
	private $xmlData = array();
	private $counterArray = array();
	
	function __construct()
	{
		$data = debug_backtrace(true);
		$log = e107::getAdminLog();
		$log->addDebug('Deprecated XML Parser Used');
		
		$log->addArray($data);
		$log->save('DEPRECATED',E_LOG_NOTICE,'',false, LOG_TO_ROLLING);
		
	}
	
	function setUrl($feed)
	{
		$this->setFeedUrl($feed);
	}
	
	function getRemoteXmlFile($address, $timeout = 10)
	{	
	//	$data = $this->getRemoteFile($address, $timeout);	
		$fl = e107::getFile();
		$data = $fl->getRemoteContent($address);

		$this->xmlLegacyContents = $data;

		return $data;	
	}
	
	function parseXmlContents ()
	{
		$log = e107::getAdminLog();
		
		foreach($this -> xmlData as $key => $value)
		{
			unset($this -> xmlData[$key]);
		}
		foreach($this -> counterArray as $key => $value)
		{
			unset($this -> counterArray[$key]);
		}

		if(!function_exists('xml_parser_create'))
		{
			$log->addDebug("No XML source specified")->save('XML',E_LOG_WARNING);
			return FALSE;
		}

		if(!$this -> xmlLegacyContents)
        {
            
			$log->addDebug("No XML source specified")->save('XML');
            return FALSE;
        }

		$this->parser = xml_parser_create('');

		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'startElement', 'endElement');
		xml_set_character_data_handler( $this->parser, 'characterData' );

		$array = explode("\n", $this -> xmlLegacyContents);


		foreach($array as $data)
		{

			if(strlen($data == 4096))
			{
				$log->addDebug("The XML cannot be parsed as it is badly formed.")->save('XML');
				return FALSE;
			}

            if (!xml_parse($this->parser, $data))
            {
				$error = sprintf('XML error: %s at line %d, column %d', xml_error_string(xml_get_error_code($this->parser)), xml_get_current_line_number($this->parser),xml_get_current_column_number($this->parser));
				$log->addDebug($error)->save('XML');
				return FALSE;
            }
        }
		xml_parser_free( $this->parser );
		return $this -> xmlData;
	}
	
	
	
	function startElement ($p, $element, &$attrs)
	{
		$this -> start_tag = $element;
		$this -> current_tag = strtolower($element);
		if(!array_key_exists($this -> current_tag, $this -> counterArray))
		{
			$this -> counterArray[$this -> current_tag] = 0;
			$this -> xmlData[$this -> current_tag][$this -> counterArray[$this -> current_tag]] = "";
		}
	}

	function endElement ($p, $element)
	{
		if($this -> start_tag == $element)
		{
			$this -> counterArray[$this -> current_tag] ++;
		}
	}

	function characterData ($p, $data)
	{
		$data = trim ( chop ( $data ));
		$data = preg_replace('/&(?!amp;)/', '&amp;', $data);
		if(!array_key_exists($this -> current_tag, $this -> xmlData))
		{
			$this -> xmlData [$this -> current_tag] = array();
		}
		if(array_key_exists($this -> counterArray[$this -> current_tag], $this -> xmlData [$this -> current_tag]))
		{
			$this -> xmlData [$this -> current_tag] [$this -> counterArray[$this -> current_tag]] .= $data;
		}
		else
		{
			$this -> xmlData [$this -> current_tag] [$this -> counterArray[$this -> current_tag]] = $data;
		}
	}
	
	
}







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
	 * Log of all paths replaced.
	 *
	 * @var array
	 */
	public $fileConvertLog = array();

	public $convertFilePaths = FALSE;

	public $filePathDestination = FALSE;

	public $convertFileTypes = array("jpg", "gif", "png", "jpeg");

	public $filePathPrepend = array();

	public $filePathConvKeys = array();

	public $errors;

	private $arrayTags = false;

	private $stringTags = false;

	private $urlPrefix = false;

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
	protected $_optValueKey = '@value';


	protected $_feedUrl = FALSE;



	/**
	 * Constructor - set defaults
	 *
	 */
	function __constructor()
	{
		$this->reset();

		if(count($this->filePathConversions))
		{
			$this->filePathConvKeys = array_keys($this->filePathConversions);
		}
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
		$this->_optValueKey = '@value';
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
	 * Set Xml tags that should always return arrays.
	 *
	 * @param string $string (comma separated)
	 * @return xmlClass
	 */
	public function setOptArrayTags($string)
	{
		$this->arrayTags = (array) explode(",", $string);
		return $this;
	}

	public function setOptStringTags($string)
	{
		$this->stringTags = (array) explode(",", $string); 
		return $this;
	}

	/**
	 * Set forceArray option
	 *
	 * @param boolean $flag
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
	 * Set urlPrefix
	 *
	 * @param array $filter
	 * @return xmlClass
	 */
	public function setUrlPrefix($url)
	{
		$this->urlPrefix = $url;
		return $this;
	}

	
	
	public function setFeedUrl($feed)
	{
		if($feed)
		{
			$this->_feedUrl = $feed;
		}
		return $this;	
	}

	/**
	 * Get Remote XML file contents
	 * use setOptArrayTags above if you require a consistent array result by in 1 item or many. 
	 * @param string $address
	 * @param integer $timeout [optional] seconds
	 * @return string
	 */
	function getRemoteFile($address, $timeout = 10, $postData=null)
	{		
		$_file = e107::getFile();
		$this->xmlFileContents = $_file->getRemoteContent($address, array('timeout' => $timeout, 'post' => $postData));
		$this->error = $_file->error;
		
		return $this->xmlFileContents;
		
		// ------ MOVED TO FILE HANDLER ------ //
		// Could do something like: if ($timeout <= 0) $timeout = $pref['get_remote_timeout'];  here
		$timeout = min($timeout, 120);
		$timeout = max($timeout, 3);
		$this->xmlFileContents = '';
		
		$mes = e107::getMessage();
			
		if($this->_feedUrl) // override option for use when part of the address needs to be encoded. 
		{
			$mes->addDebug("getting Remote File: ".$this->_feedUrl);
		}
		else
		{
			$address = str_replace(array("\r", "\n", "\t"), '', $address); // May be paranoia, but streaky thought it might be a good idea	
			// ... and there shouldn't be unprintable characters in the URL anyway
		}		
		
		if($this->urlPrefix !== false)
		{
			$address = 	$this->urlPrefix.$address;
		}
		
		
		// ... and there shouldn't be unprintable characters in the URL anyway
		
		
		// Keep this in first position. 
		if (function_exists("curl_init")) // Preferred. 
		{
			$cu = curl_init();
			curl_setopt($cu, CURLOPT_URL, $address);
			curl_setopt($cu, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($cu, CURLOPT_HEADER, 0);
			curl_setopt($cu, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($cu, CURLOPT_SSL_VERIFYPEER, FALSE); 
			curl_setopt($cu, CURLOPT_REFERER, e_REQUEST_HTTP);
			curl_setopt($cu, CURLOPT_FOLLOWLOCATION, 0); 
			curl_setopt($cu, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
			curl_setopt($cu, CURLOPT_COOKIEFILE, e_SYSTEM.'cookies.txt');
			curl_setopt($cu, CURLOPT_COOKIEJAR, e_SYSTEM.'cookies.txt');
	
			if(!file_exists(e_SYSTEM.'cookies.txt'))
			{
				file_put_contents(e_SYSTEM.'cookies.txt','');	
			}
			
			$this->xmlFileContents = curl_exec($cu);
			if (curl_error($cu))
			{
				$this->error = "Curl error: ".curl_errno($cu).", ".curl_error($cu);
				return FALSE;
			}
			curl_close($cu);
			return $this->xmlFileContents;
		}
		

		if (function_exists('file_get_contents') && ini_get('allow_url_fopen'))
		{
			$old_timeout = e107_ini_set('default_socket_timeout', $timeout);
			$address = ($this->_feedUrl) ? $this->_feedUrl : urldecode($address);

			$data = file_get_contents($address);

			//		  $data = file_get_contents(htmlspecialchars($address));	// buggy - sometimes fails.
			if ($old_timeout !== FALSE)
			{
				e107_ini_set('default_socket_timeout', $old_timeout);
			}
			if ($data !== FALSE)
			{
				$this->xmlFileContents = $data;
				return $data;
			}
			$this->error = "File_get_contents(XML) error";		// Fill in more info later
			return FALSE;
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
	function parseXml($xmlData = '', $simple = true)
	{
		if ($xmlData)
		{
			$this->xmlFileContents = $xmlData;
		}
		elseif ($this->xmlFileContents)
		{
			$xmlData = $this->xmlFileContents;
		}
		if (!$xmlData)
		{
			return FALSE;
		}

		$extendedTypes = array(
			'content:encoded'   => 'content_encoded',
			'<media:'     => '<media_',
			'</media:'    => '</media_',
			'<opensearch:'  => '<opensearch_',
			'</opensearch:' => '</opensearch_'
		);

		$xmlData = str_replace(array_keys($extendedTypes), array_values($extendedTypes), $xmlData);
		
		if(!$xml = simplexml_load_string($xmlData))
		{
			$this->errors = $this->getErrors($xmlData);
			return FALSE;
		};

		$xml = $simple ? $this->xml_convert_to_array($xml, $this->filter, $this->stripComments) : $this->xml2array($xml);
		return $xml;
	}

	/**
	 * Advanced XML parser - handles tags with attributes and values
	 * properly.
	 * TODO - filter (see xml_convert_to_array)
	 * FIXME can't handle multi-dimensional associative arrays (e.g. <screnshots><image>...</image><image>...</image></screenshots> to screenshots[image] = array(...))
	 * XXX New parser in testing phase - see e_marketplace::parse()
	 * @param SimpleXMLElement $xml
	 * @param string $rec_parent used for recursive calls only
	 * @return array
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

			if(!is_object($xml))
			{
				return array();
			}

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

			$ret = $this->parseArrayTags($ret);
			$ret = $this->parseStringTags($ret);

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
						
//FIXME - commented code breaks parsing of plugin.xml extended and userclass tags and possibly other xml files. 
						
	/*
						// fix - empty SimpleXMLElement
						if(empty($xml->{$tag}))
						{
							if($this->arrayTags && in_array($tag, $this->arrayTags))
							{
								$ret[$tag] = array();
							}
							else $ret[$tag] = '';
							break;
						}
	*/					
						$count = count($xml->{$tag}); 
						if($count >= 1) //array of elements - loop
						{
							for ($i = 0; $i < $count; $i++)
							{
								$ret[$tag][$i] = $this->xml2array($xml->{$tag}[$i], $tag);
								$ret[$tag][$i] = $this->parseStringTags($ret[$tag][$i]);

							}
						}
						else //single element
						{
							$ret[$tag] = $this->xml2array($xml->{$tag}, $tag);
							$ret[$tag] = $this->parseStringTags($ret[$tag]);
						}
					break;
				}
			}
			$ret = $this->parseStringTags($ret);
			return $ret;
		}

		//parse value only
		$ret = trim((string) $xml);

		return ($this->_optForceArray ? array($this->_optValueKey => $ret) : $ret);
	}

	// OLD
	function xml_convert_to_array($xml, $localFilter = FALSE, $stripComments = TRUE)
	{
		if (is_object($xml))
		{
			$xml = (array) $xml;
		}
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

		$xml = $this->parseArrayTags($xml);
	//	$xml = $this->parseStringTags($xml);

		return $xml;
	}


	/**
	 * Convert Array(0) to String based on specified Tags.
	 *
	 * @param array|string $vars
	 * @return string
	 */
	function parseStringTags($vars)
	{
		if(!$this->stringTags || !is_array($vars))
		{
			return $vars;
		}

		foreach($this->stringTags as $vl)
		{
			if(isset($vars[$vl]) && is_array($vars[$vl]) && isset($vars[$vl][0]))
			{
				$vars[$vl] = $vars[$vl][0];
			}
		}

		return $vars;
	}

	/**
	 * Return as an array, even when a single xml tag value is found
	 * Use setArrayTags() to set which tags are affected.
	 *
	 * @param array $vars
	 * @return array
	 */
	private function parseArrayTags($vars)
	{

		if(!$this->arrayTags)
		{
			return $vars;
		}
			
		foreach($this->arrayTags as $p)
		{
			if(strpos($p,"/")!==false)
			{
				list($vl,$sub) = explode("/",$p);	
			}
			else
			{
				$vl = $p;
				$sub = false;	
			}
			
			if($sub)
			{
				if(isset($vars[$vl][$sub]) && is_string($vars[$vl][$sub]))
				{
					$vars[$vl][$sub] = array($vars[$vl][$sub]);	
				}
				
				continue;	
			}

			if(isset($vars[$vl]) && is_array($vars[$vl]) && !varset($vars[$vl][0]))
			{
				
				$vars[$vl] = array($vars[$vl]);		
			}
		}
		
		return $vars;
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
		$tp = e107::getParser();
	
		if($this->_feedUrl !== false)
		{
			$fname = $this->_feedUrl;	
		}
	
		if (empty($fname))
		{
			return false;
		}
		
		$xml = false;
		
		if (strpos($fname, '://') !== false)
		{
			$this->getRemoteFile($fname);
			$this->_feedUrl = false; // clear it to avoid conflicts. 
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
				$this->xmlFileContents = $tp->replaceConstants($this->xmlFileContents, '', true);
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

	/**
	 * Convert file path for inclusion in XML file.

	 * @see e107ExportValue()
	 * @param string $text - callback function
	 * @return string converted file path
	 */
	private function replaceFilePaths($text)
	{
		$fullpath = e107::getParser()->replaceConstants($text[1]);
		$this->fileConvertLog[] = $fullpath;
		$file = basename($fullpath);

		return $this->filePathDestination.$file;

	}


	/**
	 * Process data values for XML file. If $this->convertFilePaths is TRUE, convert paths
	 *
	 * @see replaceFilePaths()
	 * @param mixed $val
	 * @param string $key key for the current value. Used for exception processing.
	 * @return mixed
	 */
	private function e107ExportValue($val, $key = '')
	{
		if($key && isset($this->filePathPrepend[$key]))
		{
			$val = $this->filePathPrepend[$key].$val;
		}

		if($this->convertFilePaths)
		{
			$types = implode("|",$this->convertFileTypes);
			$val = preg_replace_callback("#({e_.*?\.(".$types."))#i", array($this,'replaceFilePaths'), $val);
		}

		if(is_array($val))
		{
			return "<![CDATA[".e107::getArrayStorage()->WriteArray($val,FALSE)."]]>";
		}

		if((strpos($val,"<")!==FALSE) || (strpos($val,">")!==FALSE) || (strpos($val,"&")!==FALSE))
		{
			return "<![CDATA[". $val."]]>";
		}

		return $val;
	}

	/**
	 * Create an e107 Export File in XML format
	 * Note: If $this->filePathDestination has a value, then the file will be saved there.
	 *
	 * @param array $prefs  - see e_core_pref $aliases (eg. core, ipool etc)
	 * @param array $tables - table names without the prefix
	 * @param boolean $debug [optional]
	 * @return string text / file for download
	 */
	public function e107Export($xmlprefs, $tables, $debug = FALSE)
	{
		error_reporting(0);
		require_once(e_ADMIN."ver.php");

		$text = "<?xml version='1.0' encoding='utf-8' ?".">\n";
		$text .= "<e107Export version=\"".$e107info['e107_version']."\" timestamp=\"".time()."\" >\n";

		if(varset($xmlprefs)) // Export Core Preferences.
		{
			$text .= "\t<prefs>\n";
			foreach($xmlprefs as $type)
			{
				$theprefs = e107::getConfig($type)->getPref();
				$prefsorted = ksort($theprefs);
				foreach($theprefs as $key=>$val)
				{
					if(isset($val))
					{
						$text .= "\t\t<".$type." name=\"".$key."\">".$this->e107ExportValue($val)."</".$type.">\n";
					}
				}
			}
			$text .= "\t</prefs>\n";
		}

		if(varset($tables))
		{
			$text .= "\t<database>\n";
			foreach($tables as $tbl)
			{
				$eTable= str_replace(MPREFIX,"",$tbl);
				e107::getDB()->db_Select($eTable, "*");
				$text .= "\t<dbTable name=\"".$eTable."\">\n";
				while($row = e107::getDB()-> db_Fetch())
				{
					$text .= "\t\t<item>\n";
					foreach($row as $key=>$val)
					{
						$text .= "\t\t\t<field name=\"".$key."\">".$this->e107ExportValue($val,$key)."</field>\n";
					}

					$text .= "\t\t</item>\n";
				}
				$text .= "\t</dbTable>\n";

			}
			$text .= "\t</database>\n";
		}



		$text .= "</e107Export>";

		if($debug==TRUE)
		{
			echo "<pre>".htmlentities($text)."</pre>";
			return TRUE;
		}
		else
		{
			if(!$text)
			{
				return FALSE;
			}

			$path = e107::getParser()->replaceConstants($this->filePathDestination);
			if($path)
			{
				file_put_contents($path."install.xml",$text,FILE_TEXT);
				return true;
			}

			header('Content-type: application/xml', TRUE);
			header("Content-disposition: attachment; filename= e107Export_" . date("Y-m-d").".xml");
			header("Cache-Control: max-age=30");
			header("Pragma: public");
			echo $text;
			exit;

		}
	}

	/**
	 * Return an Array of core preferences from e107 XML Dump data
	 *
	 * @param array $XMLData Raw XML e107 Export Data
	 * @param string $prefType [optional] the type of core pref: core|emote|ipool|menu etc.
	 * @return array preference array equivalent to the old $pref global;
	 */
	public function e107ImportPrefs($XMLData, $prefType='core')
	{
		if(!vartrue($XMLData['prefs'][$prefType]))
		{
			return array();
		}

		//$mes = eMessage::getInstance();

		$pref = array();
		foreach($XMLData['prefs'][$prefType] as $val)
		{
			$name = $val['@attributes']['name'];
			// if(strpos($val['@value'], 'array (') === 0)
			// {
				// echo '<pre>'.$val['@value'];
				// echo "\n";
				// var_dump(e107::getArrayStorage()->ReadArray($val['@value']));
				// echo $val['@value'].'</pre>';
			// }
			$value = strpos($val['@value'], 'array (') === 0 ? e107::unserialize($val['@value']) : $val['@value'];
			$pref[$name] = $value;

			// $mes->add("Setting up ".$prefType." Pref [".$name."] => ".$value, E_MESSAGE_DEBUG);
		}


		return $pref;
	}

	/**
	 * Import an e107 XML file into site preferences and DB tables
	 *
	 * @param path $file - e107 XML file path
	 * @param string $mode[optional] - add|replace
	 * @param boolean $noLogs [optional] tells pref handler to disable admin logs when true (install issues)
	 * @param boolean $debug [optional]
	 * @return array with keys 'success' and 'failed' - DB table entry status.
	 */
	public function e107Import($file, $mode='replace', $noLogs = false, $debug=FALSE, $sql = null)
	{

		if($sql == null)
		{
			$sql = e107::getDB();	
		}

		$xmlArray = $this->loadXMLfile($file, 'advanced');

		if($debug)
		{
			//$message = print_r($xmlArray);
			echo "<pre>".var_export($xmlArray,TRUE)."</pre>";
			return;
		}

		$ret = array();
		
		if(vartrue($xmlArray['prefs'])) // Save Core Prefs
		{
			foreach($xmlArray['prefs'] as $type=>$array)
			{
				
				$pArray = $this->e107ImportPrefs($xmlArray,$type);
				
				if($mode == 'replace') // merge with existing, add new
				{
					e107::getConfig($type)->setPref($pArray);
				}
				else // 'add' only new prefs
				{
					foreach ($pArray as $pname => $pval)
					{
						e107::getConfig($type)->add($pname, $pval); // don't parse x/y/z
					}
				}

				if($debug == FALSE)
				{
					 e107::getConfig($type)
					 	->setParam('nologs', $noLogs)
					 	->save(FALSE,TRUE);
				}
			}
		}

		if(vartrue($xmlArray['database']))
		{
			foreach($xmlArray['database']['dbTable'] as $val)
			{
				$table = $val['@attributes']['name'];
				
				if(!isset($val['item']))
				{
					continue;
				}

				foreach($val['item'] as $item)
				{
					$insert_array = array();
					foreach($item['field'] as $f)
					{
						$fieldkey = $f['@attributes']['name'];
						$fieldval = (isset($f['@value'])) ? $f['@value'] : "";

						$insert_array[$fieldkey] = $fieldval;
					}
					if(($mode == "replace") && $sql->replace($table, $insert_array)!==FALSE)
					{
						$ret['success'][] = $table;
					}
					elseif(($mode == "add") && $sql->insert($table, $insert_array)!==FALSE)
					{
						$ret['success'][] = $table;
					}
					else
					{
						$ret['failed'][] = $table;
					}
				}
			}
		}

		return $ret;
	}


	function getErrors($xml)
	{
		libxml_use_internal_errors(true);
		$sxe = simplexml_load_string($xml);
		$errors = array();
		if (!$sxe)
		{
		    foreach(libxml_get_errors() as $error)
			{
		        $errors[] = $error->message. "Line:".$error->line." Column:".$error->column;
			}
			return $errors;
		}
		return FALSE;
	}



	




}



/**
 * DEPRECATED XML Class from v1.x
 */
class XMLParse
{
    var $rawXML;
    var $valueArray = array();
    var $keyArray = array();
    var $parsed = array();
    var $index = 0;
    var $attribKey = 'attributes';
    var $valueKey = 'value';
    var $cdataKey = 'cdata';
    var $isError = false;
    var $error = '';

    function XMLParse($xml = NULL)
    {
        $this->rawXML = $xml;
		$mes = e107::getMessage();
		$mes->addDebug("Deprecated class XMLParse used. Please use 'xmlClass' instead");
    }

    function parse($xml = NULL)
    {
        if (!is_null($xml))
        {
            $this->rawXML = $xml;
        }

        $this->isError = false;

        if (!$this->parse_init())
        {
            return false;
        }

        $this->index = 0;
        $this->parsed = $this->parse_recurse();
        $this->status = 'parsing complete';

        return $this->parsed;
    }

    function parse_recurse()
    {
        $found = array();
        $tagCount = array();

        while (isset($this->valueArray[$this->index]))
        {
            $tag = $this->valueArray[$this->index];
            $this->index++;

            if ($tag['type'] == 'close')
            {
                return $found;
            }

            if ($tag['type'] == 'cdata')
            {
                $tag['tag'] = $this->cdataKey;
                $tag['type'] = 'complete';
            }

            $tagName = $tag['tag'];

            if (isset($tagCount[$tagName]))
            {
                if ($tagCount[$tagName] == 1)
                {
                    $found[$tagName] = array($found[$tagName]);
                }

                $tagRef =& $found[$tagName][$tagCount[$tagName]];
                $tagCount[$tagName]++;
            }
            else
            {
                $tagCount[$tagName] = 1;
                $tagRef =& $found[$tagName];
            }

            switch ($tag['type'])
            {
                case 'open':
                    $tagRef = $this->parse_recurse();

                    if (isset($tag['attributes']))
                    {
                        $tagRef[$this->attribKey] = $tag['attributes'];
                    }

                    if (isset($tag['value']))
                    {
                        if (isset($tagRef[$this->cdataKey]))
                        {
                            $tagRef[$this->cdataKey] = (array)$tagRef[$this->cdataKey];
                            array_unshift($tagRef[$this->cdataKey], $tag['value']);
                        }
                        else
                        {
                            $tagRef[$this->cdataKey] = $tag['value'];
                        }
                    }
                    break;

                case 'complete':
                    if (isset($tag['attributes']))
                    {
                        $tagRef[$this->attribKey] = $tag['attributes'];
                        $tagRef =& $tagRef[$this->valueKey];
                    }

                    if (isset($tag['value']))
                    {
                        $tagRef = $tag['value'];
                    }
                    break;
            }
        }

        return $found;
    }

    function parse_init()
    {
        $this->parser = xml_parser_create();

        $parser = $this->parser;
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        if (!$res = (bool)xml_parse_into_struct($parser, $this->rawXML, $this->valueArray, $this->keyArray))
        {
            $this->isError = true;
            $this->error = 'error: '.xml_error_string(xml_get_error_code($parser)).' at line '.xml_get_current_line_number($parser);
        }
        xml_parser_free($parser);

        return $res;
    }
}

