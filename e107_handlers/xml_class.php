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
|     $Revision: 1.2 $
|     $Date: 2005-02-28 18:23:54 $
|     $Author: stevedunstan $
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
			if (curl_error($remote))
			{
				$this -> error =  "Error: ".curl_errno($ch).", ".curl_error($ch);
				return FALSE;
			}
			curl_close ($cu);
			return TRUE;
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
		return TRUE;
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

?>