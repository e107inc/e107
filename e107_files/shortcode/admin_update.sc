// $Id: admin_update.sc,v 1.4 2007-02-12 18:16:57 e107coders Exp $

	global $e107cache,$ns;
	if (is_readable(e_ADMIN."ver.php"))
	{
		include(e_ADMIN."ver.php");
	}

	$feed = "http://sourceforge.net/export/rss2_projfiles.php?group_id=63748&rss_limit=5";
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
    $c = 0;
	foreach($rss->items as $val)
	{
		$search = array((strstr($val['title'],"(")),"e107","released"," v");
		$version = trim(str_replace($search,"",$val['title']));

	 	if(version_compare($version,$cur_version)==1)
	 	{
        	$ftext = "<a rel='external' href='".$val['link']."' >e107 v".$version."</a><br />\n";
            break;
	  	}
		$c++;
	}

	$e107cache->set("updatecheck", $ftext, TRUE);
	if($ftext)
	{
		return $ns -> tablerender(LAN_NEWVERSION, $ftext);
	}


