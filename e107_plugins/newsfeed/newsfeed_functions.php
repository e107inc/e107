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
|     $Revision: 1.2 $
|     $Date: 2005-03-02 09:06:47 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

function checkUpdate($query = "newsfeed_active=2 OR newsfeed_active=3")
{
	global $sql, $tp;
	require_once(e_HANDLER."xml_class.php");
	$xml = new parseXml;
	require_once(e_HANDLER."magpie_rss.php");
	
	if ($sql -> db_Select("newsfeed", "*", $query))
	{
		$feedArray = $sql -> db_getList();
		foreach($feedArray as $feed)
		{
			extract ($feed);
			if($newsfeed_timestamp + $newsfeed_updateint < time())
			{
				if($rawData = $xml -> getRemoteXmlFile($newsfeed_url))
				{
					$rss = new MagpieRSS( $rawData );

					$serializedArray = addslashes(serialize($rss));

					$newsfeed_des = FALSE;
					if($newsfeed_description == "default")
					{
						if($rss -> channel['description'])
						{
							$newsfeed_des = $tp -> toDB($rss -> channel['description']);
						}
						else if($rss -> channel['tagline'])
						{
							$newsfeed_des = $tp -> toDB($rss -> channel['tagline']);
						}
					}
	
					if(!$sql->db_Update('newsfeed', "newsfeed_data='$serializedArray', newsfeed_timestamp=".time().($newsfeed_des ? ", newsfeed_description='$newsfeed_des'": "")." WHERE newsfeed_id=$newsfeed_id"))
					{
						echo "Unable to save raw data in database.<br /><br />".$serializedArray;
					}
				}
				else
				{
					echo $xml -> error;
				}
			}
		}
	}
}



?>