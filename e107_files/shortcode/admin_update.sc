// $Id: admin_update.sc,v 1.2 2006-12-08 21:37:09 e107coders Exp $

	global $e107cache,$ns;
	if (is_readable(e_ADMIN."ver.php"))
	{
		include(e_ADMIN."ver.php");
	}

	$feed = "http://sourceforge.net/export/rss2_projfiles.php?group_id=63748";
	$e107cache->CachePageMD5 = md5($e107info['e107_version']);

    if($cacheData = $e107cache->retrieve("updatecheck",3600, TRUE))
    {
   		return $ns -> tablerender(LAN_NEWVERSION, $cacheData);
    }

	require_once(e_HANDLER."xml_class.php");
	$xml = new parseXml;
	require_once(e_HANDLER."magpie_rss.php");

    $ftext = "";
	if($rawData = $xml -> getRemoteXmlFile($feed))
	{
		$rss = new MagpieRSS( $rawData );
	}

    list($cur_version,$tag) = explode(" ",$e107info['e107_version']);

	foreach($rss->items as $val)
	{
		$search = array((strstr($val['title'],"(")),"e107","released"," v");
		$version = trim(str_replace($search,"",$val['title']));

	  	if(version_compare($version,$cur_version,">"))
	 	{
        	$ftext .= "<a rel='external' href='".$val['link']."' >e107 v".$version."</a><br />\n";
	  	}
		break;
	}

	$e107cache->set("updatecheck", $ftext, TRUE);
	if($ftext)
	{
		return $ns -> tablerender(LAN_NEWVERSION, $ftext);
	}


