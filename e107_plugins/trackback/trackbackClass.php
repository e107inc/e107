<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin administration - newsfeeds
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/trackback/trackbackClass.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

if (!defined('e107_INIT')) { exit; }

class trackbackClass
{

	function sendTrackback ($permLink, $pingUrl, $title, $excerpt)
	{
		global $e107;

		$title = urlencode(stripslashes($title));
		$excerpt = urlencode(stripslashes($excerpt));
		$blog_name = urlencode(stripslashes(SITENAME));
		$permLink = urlencode(stripslashes($e107->base_path.$permLink));
		$query_string = "title=".$title."&url=".$permLink."&blog_name=".$blog_name."&excerpt=".$excerpt;

		if (strstr($pingUrl, '?'))
		{
			$pingUrl .= "&".$query_string;
			$fp = fopen($pingUrl, 'r');
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
			$header .= 'Content-Type: application/x-www-form-urlencoded; charset=utf-8'."\r\n";
			$header .= 'Content-Length: '.strlen($query_string)."\r\n";
			$header .= "\r\n";
			$header .= $query_string;

			$socket = fsockopen($trackback_url["host"], $trackback_url["port"]);

			if (!is_resource($socket))
			{
				return 'trackbackClass -> sendTrackback: Unable to connect to {$trackback_url[\'host\']}.';
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
			if(preg_match('#\<message\>(.*?)\<\/message\>#', $response, $match))
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
		global $sql, $pref, $tp, $e107cache;
		$errorMessage = "";
		if(!$pref['trackbackEnabled'])
		{
			$errorMessage = "This site does not allow trackbacks.";
		}

		$pid = (isset($_GET['pid']) ? intval($_GET['pid']) : intval($_POST['pid']));
		$permLink = (isset($_GET['url']) ? $_GET['url'] : $_POST['url']);
		$permLink = $tp -> toDB($permLink);
		$blog_name = (isset($_GET['blog_name']) ? $_GET['blog_name'] : $_POST['blog_name']);
		$blog_name = $tp -> toDB($blog_name);
		$title = (isset($_GET['title']) ? $_GET['title'] : $_POST['title']);
		$title = $tp -> toDB($title);
		$excerpt = (isset($_GET['excerpt']) ? $_GET['excerpt'] : $_POST['excerpt']);
		$excerpt = $tp -> toDB($excerpt);

		/* debug	 */
		/*
		$debug_str = "Query string: ".e_TBQS."\n";
		$debug_str .= "GET INFO: \n";
		foreach($_GET as $key => $get)
		{
			$debug_str .= "$key => $get\n";
		}
		$debug_str .= "POST INFO: \n";
		foreach($_POST as $key => $get)
		{
			$debug_str .= "$key => $get\n";
		}
		$sql -> db_Insert("debug", "0, '".time()."', '$debug_str' ");
		*/
		/* end debug */


		if(!$pid)
		{
			$errorMessage = "No permanent ID sent.";
		}

		if(!isset($pid) || !is_numeric($pid))
		{
			$errorMessage = "No known item with that pid (pid sent as ".$pid.").";
		}

		$excerpt = ($excerpt ? strip_tags($excerpt) : "I found your news item interesting, I've added a trackback to it on my website :)");
		$title = ($title ? $title : "Trackbacking your news item ...");
		$blog_name = ($blog_name ? $blog_name : "Anonymous site");

		if(!$errorMessage)
		{
		  if(!$sql -> db_Insert("trackback", "0, {$pid}, '{$title}', '{$excerpt}', '{$permLink}', '{$blog_name}' "))
		  {
			$errorMessage = "Unable to enter your trackback information into the database -> 0, {$pid}, '{$title}', '{$excerpt}', '{$permLink}', '{$blog_name}'";
		  }
		  else
		  {
			$e107cache->clear("comment.php?news.{$pid}");
		  }
		}

		if($errorMessage)
		{
			echo '<?xml version="1.0" encoding="utf-8"?'.">\n";
			echo "<response>\n";
			echo "<error>1</error>\n";
			echo "<message>".$errorMessage."</message>\n";
			echo "</response>";
		}
		else
		{
			echo '<?xml version="1.0" encoding="utf-8"?'.">\n";
			echo "<response>\n";
			echo "<error>0</error>\n";
			echo "</response>";
		}
	}
}
