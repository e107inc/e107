<?php 
if (!defined('e107_INIT'))
{
	exit;
}

// e107 requires PHP > 4.3.0, all functions that are used in e107, introduced in newer
// versions than that should be recreated in here for compatabilty reasons..

// file_put_contents - introduced in PHP5
if (!function_exists('file_put_contents'))
{
	/**
	 * @return int
	 * @param string $filename
	 * @param mixed $data
	 * @desc Write a string to a file
	 */
	define('FILE_APPEND', 1);
	function file_put_contents($filename, $data, $flag = false)
	{
		$mode = ($flag == FILE_APPEND || strtoupper($flag) == 'FILE_APPEND') ? 'a' : 'w';
		if (($h = @fopen($filename, $mode)) === false)
		{
			return false;
		}
		if (is_array($data))
			$data = implode($data);
		if (($bytes = @fwrite($h, $data)) === false)
		{
			return false;
		}
		fclose($h);
		return $bytes;
	}
}

if (!function_exists('stripos'))
{
	function stripos($str, $needle, $offset = 0)
	{
		return strpos(strtolower($str), strtolower($needle), $offset);
	}
}

if (!function_exists('simplexml_load_string'))
{

	//CXml class code found on php.net
	class CXml
	{
		var $xml_data;
		var $obj_data;
		var $pointer;
		
		function CXml()
		{
		}
		
		function Set_xml_data(&$xml_data)
		{
			$this->index = 0;
			$this->pointer[] = &$this->obj_data;
			
			//strip white space between tags
			$this->xml_data = preg_replace("/>[[:space:]]+</i", "><", $xml_data);
			$this->xml_parser = xml_parser_create("UTF-8");
			
			xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, false);
			xml_set_object($this->xml_parser, $this);
			xml_set_element_handler($this->xml_parser, "_startElement", "_endElement");
			xml_set_character_data_handler($this->xml_parser, "_cData");
			
			xml_parse($this->xml_parser, $this->xml_data, true);
			xml_parser_free($this->xml_parser);
		}
		
		function _startElement($parser, $tag, $attributeList)
		{
			$attributes = '@attributes';
			foreach ($attributeList as $name=>$value)
			{
				$value = $this->_cleanString($value);
				$object-> {$attributes} [$name] = $value;
				//           $object->$name = $value;
			}
			//replaces the special characters with the underscore (_) in tag name
			$tag = preg_replace("/[:\-\. ]/", "_", $tag);
			eval("\$this->pointer[\$this->index]->".$tag."[] = \$object;");
			eval("\$size = sizeof( \$this->pointer[\$this->index]->".$tag." );");
			eval("\$this->pointer[] = &\$this->pointer[\$this->index]->".$tag."[\$size-1];");
			
			$this->index++;
		}
		
		function _endElement($parser, $tag)
		{
			array_pop($this->pointer);
			$this->index--;
		}
		
		function _cData($parser, $data)
		{
			if ( empty($this->pointer[$this->index]))
			{
				if (rtrim($data, "\n"))
				{
					$this->pointer[$this->index] = $data;
				}
			}
			else
			{
				$this->pointer[$this->index] .= $data;
			}
		}
		
		function _cleanString($string)
		{
			return utf8_decode(trim($string));
		}
	}
	
	function simplexml_load_string($xml)
	{
		$xmlClass = new CXml;
		$xmlClass->Set_xml_data($xml);
		$data = (array) $xmlClass->obj_data;
		$tmp = array_keys($data);
		$data = $data[$tmp[0]][0];
		return $data;
	}
	
}

/*
 * This work of Lionel SAURON (http://sauron.lionel.free.fr:80) is licensed under the
 * Creative Commons Attribution-Noncommercial-Share Alike 2.0 France License.
 *
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/2.0/fr/
 * or send a letter to Creative Commons, 171 Second Street, Suite 300, San Francisco, California, 94105, USA.
 */

/**
 * http://snipplr.com/view/4964/emulate-php-5-for-backwards-compatibility/
 *
 * Parse a date generated with strftime().
 * PHP Compatibility *nix v5.1.0+, all $M
 * 
 * This function is the same as the original one defined by PHP (Linux/Unix only),
 *  but now you can use it on Windows too.
 *  Limitation : Only this format can be parsed %S, %M, %H, %d, %m, %Y
 *
 * @author Lionel SAURON
 * @version 1.0
 * @public
 *
 * @param string $str date string to parse (e.g. returned from strftime()).
 * @param string $sFormat strftime format used to create the date
 * @return array Returns an array with the <code>$str</code> parsed, or <code>false</code> on error.
 */
if (!function_exists('strptime'))
{
	define('STRPTIME_COMPAT', true);
	function strptime($str, $format)
	{
		static $expand = array('%D'=>'%m/%d/%y', '%T'=>'%H:%M:%S', );
		
		static $map_r = array('%S'=>'tm_sec', '%M'=>'tm_min', '%H'=>'tm_hour', '%d'=>'tm_mday', '%m'=>'tm_mon', '%Y'=>'tm_year', '%y'=>'tm_year', /*'%W'=>'tm_wday', '%D'=>'tm_yday',*/ '%u'=>'unparsed', );
		
		#-- TODO  - not so useful, locale is breaking it, so use date() and mktime() to generate the array below
		static $names = array('Jan'=>1, 'Feb'=>2, 'Mar'=>3, 'Apr'=>4, 'May'=>5, 'Jun'=>6, 'Jul'=>7, 'Aug'=>8, 'Sep'=>9, 'Oct'=>10, 'Nov'=>11, 'Dec'=>12, 'Sun'=>0, 'Mon'=>1, 'Tue'=>2, 'Wed'=>3, 'Thu'=>4, 'Fri'=>5, 'Sat'=>6 );
		
		#-- transform $format into extraction regex
		$format = str_replace(array_keys($expand), array_values($expand), $format);
		$preg = preg_replace('/(%\w)/', '(\w+)', preg_quote($format));
		
		#-- record the positions of all STRFCMD-placeholders
		preg_match_all('/(%\w)/', $format, $positions);
		$positions = $positions[1];
		
		#-- get individual values
		if (preg_match("#$preg#", $str, $extracted))
		{
			#-- get values
			foreach ($positions as $pos => $strfc)
			{
				$v = $extracted[$pos + 1];
				#-- add
				if (isset($map_r[$strfc]))
				{
					$n = $map_r[$strfc];
					$vals[$n] = ($v > 0) ? (int) $v : $v;
				}
				else
				{
					$vals['unparsed'] .= $v.' ';
				}
			}
			
			#-- fixup some entries
			//$vals["tm_wday"] = $names[ substr($vals["tm_wday"], 0, 3) ];
			if ($vals['tm_year'] >= 1900)
			{
				$vals['tm_year'] -= 1900;
			}
			elseif ($vals['tm_year'] > 0)
			{
				$vals['tm_year'] += 100;
			}
			
			if ($vals['tm_mon'])
			{
				$vals['tm_mon'] -= 1;
			}
			else
			{
				$vals['tm_mon'] = $names[substr($vals['tm_mon'], 0, 3)] - 1;
			}
			//$vals['tm_sec'] -= 1; always increasing tm_sec + 1 ??????
			
			#-- calculate wday/yday
			$unxTimestamp = mktime($vals['tm_hour'], $vals['tm_min'], $vals['tm_sec'], ($vals['tm_mon'] + 1), $vals['tm_mday'], ($vals['tm_year'] + 1900));
			$vals['tm_wday'] = (int) strftime('%w', $unxTimestamp); // Days since Sunday (0-6)
			$vals['tm_yday'] = (strftime('%j', $unxTimestamp) - 1); // Days since January 1 (0-365)
			//var_dump($vals, $str, strftime($format, $unxTimestamp), $unxTimestamp);
		}
		
		return isset($vals) ? $vals : false;
		
	} 
	
}
