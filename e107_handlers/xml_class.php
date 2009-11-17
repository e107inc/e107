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
 * $Revision: 1.36 $
 * $Date: 2009-11-17 14:50:30 $
 * $Author: marj_nl_fr $
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
	 *FIXME is this an array or a string???
	 * @param object $array
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
			$data = file_get_contents(urldecode($address));
	
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
	 * 
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
			if(varset($vars[$vl][0]))
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


		foreach($this->arrayTags as $vl)
		{
			
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
		
		require_once(e_ADMIN."ver.php");
		
		$text = "<?xml version='1.0' encoding='utf-8' ?".">\n";
		$text .= "<e107Export version='".$e107info['e107_version']."' timestamp='".time()."' >\n";
	
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
						$text .= "\t\t<".$type." name='$key'>".$this->e107ExportValue($val)."</".$type.">\n";
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
				$text .= "\t<dbTable name='$eTable'>\n";
				while($row = e107::getDB()-> db_Fetch())
				{
					$text .= "\t\t<item>\n";
					foreach($row as $key=>$val)
					{
						$text .= "\t\t\t<field name='".$key."'>".$this->e107ExportValue($val,$key)."</field>\n";
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
			return;
		} 
		
		$mes = eMessage::getInstance();
		
		$pref = array();
		foreach($XMLData['prefs'][$prefType] as $val)
		{	
			$name = $val['@attributes']['name'];
			$value = (substr($val['@value'],0,7) == "array (") ? e107::getArrayStorage()->ReadArray($val['@value']) : $val['@value'];
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
	 * @param boolean $debug [optional]
	 * @return array with keys 'success' and 'failed' - DB table entry status. 
	 */
	public function e107Import($file,$mode='replace',$debug=FALSE)
	{

		$xmlArray = $this->loadXMLfile($file,'advanced');
		
		if($debug)
		{
			// $message = print_r($xmlArray);
		//	echo "<pre>".print_r($xmlArray,TRUE)."</pre>";
			return;
		}

		$ret = array();
		
		//FIXME - doesn't work from install_.php. 		
		if(vartrue($xmlArray['prefs'])) // Save Core Prefs
		{
			foreach($xmlArray['prefs'] as $type=>$array)
			{
				$pArray = $this->e107ImportPrefs($xmlArray,$type);
				if($mode == 'replace')
				{
					e107::getConfig($type)->setPref($pArray);
				}
				else
				{
					e107::getConfig($type)->addPref($pArray); // FIXME addPref() doesn't behave the same way as setPref() with arrays. 
				}

				if($debug == FALSE)
				{
					 e107::getConfig($type)->save(FALSE,TRUE);	
				}			  	
			}
		}
		
		if(vartrue($xmlArray['database']))
		{
			foreach($xmlArray['database']['dbTable'] as $val)
			{
				$table = $val['@attributes']['name'];
				
				foreach($val['item'] as $item)
				{
					$insert_array = array();
					foreach($item['field'] as $f)
					{
						$fieldkey = $f['@attributes']['name'];
						$fieldval = (isset($f['@value'])) ? $f['@value'] : "";
					
						$insert_array[$fieldkey] = $fieldval;											
					}
					if(($mode == "replace") && e107::getDB()->db_Replace($table, $insert_array)!==FALSE)
					{			
						$ret['success'][] = $table;				
					}
					elseif(($mode == "add") && e107::getDB()->db_Insert($table, $insert_array)!==FALSE)
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
