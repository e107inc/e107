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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/newsfeed/newsfeed_functions.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-02-28 18:23:56 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

function checkUpdate($query = "newsfeed_active=2 OR newsfeed_active=3")
{
	global $sql, $tp;
	require_once(e_HANDLER."xml_class.php");
	$xml = new parseXml;
	if ($sql -> db_Select("newsfeed", "*", $query))
	{
		$feedArray = $sql -> db_getList();
		foreach($feedArray as $feed)
		{
			extract ($feed);
			if($newsfeed_timestamp + $newsfeed_updateint < time())
			{
				if($xml -> getRemoteXmlFile($newsfeed_url))
				{
					$array = $xml -> parseXmlContents();
				//	echo "<pre>"; print_r($xml -> parseXmlContents()); echo "</pre>"; exit;
					$newsfeed_des = FALSE;
					if($newsfeed_description == "default")
					{
						$newsfeed_des = $array['description'][0];
					}
	
					$dataArray = serialize($array);
					$sql->db_Update('newsfeed', "newsfeed_data='".addslashes($dataArray)."', newsfeed_timestamp=".time().($newsfeed_des ? ", newsfeed_description='$newsfeed_des'": "")." WHERE newsfeed_id=$newsfeed_id");
				}
				else
				{
					echo $xml -> error;
				}
			}
		}
	}
}


function parseData($array)
{

	$dataArray = array();
	$feedArray = unserialize($array);

	$dataArray['image'] = (array_key_exists("url", $feedArray) ? $feedArray['url'][0] : "");
	$dataArray['language'] = (array_key_exists("language", $feedArray) ? $feedArray['language'][0] : $feedArray['dc:language'][0]);
	$dataArray['lastbuilddate'] = (array_key_exists("lastbuilddate", $feedArray) ? $feedArray['lastbuilddate'][0] : $feedArray['dc:date'][0]);
	$dataArray['copyright'] = $feedArray['copyright'][0];
	$dataArray['docs'] = $feedArray['docs'][0];
	$dataArray['title'] = $feedArray['title'][0];
	$dataArray['sectitle'] = $feedArray['title'][1];
	$dataArray['link'] = $feedArray['link'][0];
	$dataArray['seclink'] = $feedArray['title'][1];

	$loop = 2;
	while($feedArray['title'][$loop])
	{
		$dataArray['itemlink'][($loop-2)] = "<a href='".$feedArray['link'][$loop]."' rel='external'>".$feedArray['title'][$loop]."</a>\n";
		$dataArray['itemtext'][($loop-2)] = $feedArray['description'][($loop-1)];
		if(array_key_exists("dc:creator", $feedArray))
		{
			$dataArray['itemcreator'][($loop-2)] = NFLAN_35.$feedArray['dc:creator'][($loop-1)];
		}
		else
		{
			$dataArray['itemcreator'][($loop-2)] = "";
		}
		$loop++;
	}
	return $dataArray;
}

?>