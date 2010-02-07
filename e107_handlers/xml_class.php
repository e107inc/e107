<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/xml_class.php,v $
|     $Revision: 1.12 $
|     $Date: 2010-02-07 00:43:01 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

class parseXml {

	var $parser;
	var $error;
	var $current_tag;
	var $start_tag;
	var $xmlData = array();
	var $counterArray = array();
	var $data;
	var $xmlFileContents;


    function getRemoteXmlFile($address, $timeout = 10)
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

			// $data = file_get_contents(htmlspecialchars($address));	// buggy - sometimes fails.
			if ($old_timeout !== FALSE)
			{
				e107_ini_set('default_socket_timeout', $old_timeout);
			}
			if ($data)
			{
				$this->xmlFileContents = $data;
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


	function parseXmlContents ()
	{
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
			$this->error = "XML library not available.";
			return FALSE;
		}

		if(!$this -> xmlFileContents)
        {
            $this->error = "No XML source specified";
            return FALSE;
        }

		$this->parser = xml_parser_create('');
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, 'startElement', 'endElement');
		xml_set_character_data_handler( $this->parser, 'characterData' );

		$array = explode("\n", $this -> xmlFileContents);

		foreach($array as $data)
		{

			if(strlen($data == 4096))
			{
				$this -> error = "The XML cannot be parsed as it is badly formed.";
				return FALSE;
			}

            if (!xml_parse($this->parser, $data))
            {
				$this->error = sprintf('XML error: %s at line %d, column %d', xml_error_string(xml_get_error_code($this->parser)), xml_get_current_line_number($this->parser),xml_get_current_column_number($this->parser));
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

//CXml class code found on php.net
class CXml
{
   var $xml_data;
   var $obj_data;
   var $pointer;

   function CXml() { }

   function Set_xml_data( &$xml_data )
   {
       $this->index = 0;
       $this->pointer[] = &$this->obj_data;

       //strip white space between tags
       $this->xml_data = preg_replace("/>[[:space:]]+</i", "><", $xml_data);
       $this->xml_parser = xml_parser_create( "UTF-8" );

       xml_parser_set_option( $this->xml_parser, XML_OPTION_CASE_FOLDING, false );
       xml_set_object( $this->xml_parser, $this );
       xml_set_element_handler( $this->xml_parser, "_startElement", "_endElement");
       xml_set_character_data_handler( $this->xml_parser, "_cData" );

       xml_parse( $this->xml_parser, $this->xml_data, true );
       xml_parser_free( $this->xml_parser );
   }

   function _startElement( $parser, $tag, $attributeList )
   {
       foreach( $attributeList as $name => $value )
       {
           $value = $this->_cleanString( $value );
           $object->$name = $value;
       }
       //replaces the special characters with the underscore (_) in tag name
       $tag = preg_replace("/[:\-\. ]/", "_", $tag);
       eval( "\$this->pointer[\$this->index]->" . $tag . "[] = \$object;" );
       eval( "\$size = sizeof( \$this->pointer[\$this->index]->" . $tag . " );" );
       eval( "\$this->pointer[] = &\$this->pointer[\$this->index]->" . $tag . "[\$size-1];" );

       $this->index++;
   }

   function _endElement( $parser, $tag )
   {
       array_pop( $this->pointer );
       $this->index--;
   }

   function _cData( $parser, $data )
   {
       if (empty($this->pointer[$this->index])) {
           if (rtrim($data, "\n"))
               $this->pointer[$this->index] = $data;
       } else {
           $this->pointer[$this->index] .= $data;
       }
   }

   function _cleanString( $string )
   {
       return utf8_decode( trim( $string ) );
   }
}

/**
 * 0.7 Only Xml Parsing Function.
 * Do not use in plugins if you want them to work in 0.8.
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

?>