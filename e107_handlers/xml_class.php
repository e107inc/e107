<?
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
|     $Source: /cvs_backup/e107_0.8/e107_handlers/xml_class.php,v $
|     $Revision: 1.3 $
|     $Date: 2008-01-20 04:46:35 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

class xmlClass
{
	var $xmlFileContents;

	function getRemoteFile($address)
	{
		if(function_exists('file_get_contents'))
		{
			if($data = file_get_contents($address))
			{
				return $data;
			}
		}

		if(function_exists("curl_init"))
		{
			$cu = curl_init ();
			curl_setopt($cu, CURLOPT_URL, $address);
			curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($cu, CURLOPT_HEADER, 0);
			curl_setopt ($cu, CURLOPT_TIMEOUT, 10);
			$this->xmlFileContents = curl_exec($cu);
			if (curl_error($cu))
			{
				$this -> error =  "Error: ".curl_errno($cu).", ".curl_error($cu);
				return FALSE;
			}
			curl_close ($cu);
			return $this->xmlFileContents;
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
			$this->xmlFileContents .= fgets ($remote, 4096);
		}
		fclose ($remote);
		return $this->xmlFileContents;
	}

	function parseXml($xml='')
	{
		if($xml == '' && $this->xmlFileContents)
		{
			$xml = $this->xmlFileContents;
		}
		if(!$xml)
		{
			return false;
		}

		$xml = simplexml_load_string($xml);
		if(is_object($xml))
		{
			$xml = (array)$xml;
		}
		$xml = $this->xml_convert_to_array($xml);
		return $xml;
	}

	function xml_convert_to_array($xml)
	{
		if(is_array($xml))
		{
			foreach($xml as $k => $v)
			{
				if(is_object($v))
				{
					$v = (array)$v;
				}
				$xml[$k] = $this->xml_convert_to_array($v);
			}
			if(count($xml) == 1 && isset($xml[0]))
			{
				$xml = $xml[0];
			}
		}
		return $xml;
	}

	function loadXMLfile($fname='', $parse = false)
	{

		if($fname == '')
		{
			return false;
		}
		$xml = false;

		if(strpos($filename, '://') !== false)
		{
			$this->getRemoteFile($fname);
		}
		else
		{
			if($xml = file_get_contents($fname))
			{
				$this->xmlFileContents = $xml;
			}
		}
		if($this->xmlFileContents)
		{
			if($parse == true)
			{
				return $this->parseXML();
			}
			else
			{
				return $this->xmlFileContents;
			}
		}
		return false;
	}


}