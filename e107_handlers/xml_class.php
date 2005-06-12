<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/xml_class.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-06-12 03:34:59 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

class parseXml {

	var $parser;
	var $error;
	var $current_tag;
	var $start_tag;
	var $xmlData = array();
	var $counterArray = array();
	var $data;
	var $xmlFileContents;


	function getRemoteXmlFile($address)
	{
		if(function_exists("curl_init"))
		{
			$cu = curl_init (); 
			curl_setopt($cu, CURLOPT_URL, $address);
			curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($cu, CURLOPT_HEADER, 0);
			curl_setopt ($cu, CURLOPT_TIMEOUT, 10);
			$this -> xmlFileContents = curl_exec($cu);
			if (curl_error($cu))
			{
				$this -> error =  "Error: ".curl_errno($cu).", ".curl_error($cu);
				return FALSE;
			}
			curl_close ($cu);
			return $this -> xmlFileContents;
		}

		if(ini_get("allow_url_fopen"))
		{
			if(!$remote = @fopen ($address, "r"))
			{
				$this -> error = "Unable to open remote XML file.";
				return FALSE;
			}
		}
		else
		{
			$tmp = parse_url($address);
			if(!$remote = fsockopen ($tmp['host'], 80 ,$errno, $errstr, 10))
			{
				$this -> error = "Unable to open remote XML file.";
				return FALSE;
			}
			else
			{
				socket_set_timeout($remote, 10);
				fputs($remote, "GET ".$headline_url." HTTP/1.0\r\n\r\n");
			}
		}

		$this -> xmlFileContents = "";
		while (!feof($remote))
		{
			$this -> xmlFileContents .= fgets ($remote, 4096);
		}
		fclose ($remote);
		return $this -> xmlFileContents;
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
       $this->xml_data = eregi_replace(">"."[[:space:]]+"."<","><",$xml_data);
       $this->xml_parser = xml_parser_create( "UTF-8" );
  
       xml_parser_set_option( $this->xml_parser, XML_OPTION_CASE_FOLDING, false );
       xml_set_object( $this->xml_parser, &$this );
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

?>