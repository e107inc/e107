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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/trackback/trackbackClass.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-02-16 18:42:20 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

class trackbackClass
{

	function sendTrackback ($permLink, $pingUrl, $title, $excerpt)
	{
		global $e107;
		

		$title = urlencode(stripslashes($title));
		$excerpt = urlencode(stripslashes($excerpt));
		$blog_name = urlencode(stripslashes(SITENAME));
		$permLink = urlencode(stripslashes($e107->HTTPPath.$permLink));
		$query_string = "title=$title&url=$permLink&blog_name=$blog_name&excerpt=$excerpt";

		if (strstr($trackback_url, '?'))
		{
			$pingUrl .= "&".$query_string;
			$fp = fopen($trackback_url, 'r');
			$response = fread($fp, 4096);
			fclose($fp);
		}
		else
		{

			$trackback_url = parse_url($pingUrl);

			if ((isset($trackback_url["query"])) && ($trackback_url["query"] != ""))
			{
				$trackback_url["query"] = "?" . $trackback_url["query"];
			}
			else
			{
				$trackback_url["query"] = "";
			}

			if ((isset($trackback_url["port"]) && !is_numeric($trackback_url["port"])) || (!isset($trackback_url["port"])))
			{
				$trackback_url["port"] = 80;
			}

			$header  = 'POST ' . $trackback_url['path'] . $trackback_url['query'] . " HTTP/1.0\r\n";
			$header .= 'Host: '.$trackback_url['host']."\r\n";
			$header .= 'Content-Type: application/x-www-form-urlencoded'."\r\n";
			$header .= 'Content-Length: '.strlen($query_string)."\r\n";
			$header .= "\r\n";
			$header .= $query_string;

			$socket = fsockopen($trackback_url["host"], $trackback_url["port"]); 

			if (!is_resource($socket)) {
				return "$trackbackClass -> sendTrackback: Unable to connect to $pingUrl.";
			}

			fputs($socket, $header); 
       
			$response = "";
			while (!feof($socket)) {
				$response .= fgets($socket, 4096);
			}
			fclose($socket);

		}

		if(strstr($response, "<error>0</error>"))
		{
			return FALSE;
		}
		else
		{
			if(preg_match("#\<message\>(.*?)\<\/message\>#", $response, $match))
			{
				return $match[0];
			}
			else
			{
				return "No error returned.";
			}
		}

    }

	function respondTrackback ()
	{
		global $sql, $pref;
		$errorMessage = "";
		if(!$pref['trackbackEnabled'])
		{
			$errorMessage = "This site does not allow trackbacks.";
		}

		if(isset($_GET['pid']))
		{
			$pid = $_GET['pid'];
			$permLink = $_GET['url'];
			$blog_name = $_GET['blog_name'];
			$title = $_GET['title'];
			$excerpt = $_GET['excerpt'];
		}
		else
		{
			$pid = $_POST['pid'];
			$permLink = $_POST['url'];
			$blog_name = $_POST['blog_name'];
			$title = $_POST['title'];
			$excerpt = $_POST['excerpt'];
		}

		if(!$permLink)
		{
			$errorMessage = "No permanent ID sent.";
		}

		if(!isset($pid) || !is_numeric($pid))
		{
			$errorMessage = "No known item with that pid.";
		}

		$excerpt = ($excerpt ? strip_tags($excerpt) : "I found your news item interesting, I've added a trackback to it on my website :)");
		$title = ($title ? $title : "Trackbacking your news item ...");
		$blog_name = ($blog_name ? $blog_name : "Anonymous site");

		if(!$errorMessage)
		{
			if(!$sql -> db_Insert("trackback", "0, $pid, '$title', '$excerpt', '$permLink', '$blog_name' "))
			{
				$errorMessage = "Unable to enter your trackback information into the database.";
			}
		}

		if($errorMessage)
		{
			echo '<?xml version="1.0" encoding="iso-8859-1"?'.">\n";
			echo "<response>\n";
			echo "<error>1</error>\n";
			echo "<message>".$errorMessage."</message>\n";
			echo "</response>";
		}
		else
		{
			echo '<?xml version="1.0" encoding="iso-8859-1"?'.">\n";
			echo "<response>\n";
			echo "<error>0</error>\n";
			echo "</response>";
		}
	}
}