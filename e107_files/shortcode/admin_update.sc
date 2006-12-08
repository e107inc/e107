// $Id: admin_update.sc,v 1.1 2006-12-08 08:10:25 e107coders Exp $

	global $e107cache,$ns;
	if (is_readable(e_ADMIN."ver.php"))
	{
		include(e_ADMIN."ver.php");
	}

	$feed = "http://sourceforge.net/export/rss2_projfiles.php?group_id=63748";
	$e107cache->CachePageMD5 = md5($e107info['e107_version']);

    if($cacheData = $e107cache->retrieve("xfeed",3600, TRUE))
    {
		return $cacheData;
    }

	require_once(e_HANDLER."xml_class.php");
	$xml = new parseXml;
	require_once(e_HANDLER."magpie_rss.php");

    $ftext = "";
	if($rawData = $xml -> getRemoteXmlFile($feed))
	{
		$rss = new MagpieRSS( $rawData );
	}

    $current_vrs = floatval(str_replace(".","",$e107info['e107_version']));
	foreach($rss->items as $val)
	{

		$search = array((strstr($val['title'],"(")),"e107","released"," v");
		$version = trim(str_replace($search,"",$val['title']));
		$numb = str_replace(".","",$version);
		$vrs = floatval($numb);

	  	if(($vrs > $current_vrs) && $vrs < 400)
	 	{
        	$ftext .= "<a rel='external' href='".$val['link']."' >e107 v".$version."</a><br />\n";
			break;
	  	}
	}

	if($ftext){
	   	$text = $ftext;
    }

	$e107cache->set("xfeed", $text, TRUE);
	if($text)
	{
		return $ns -> tablerender(LAN_NEWVERSION, $text);
	}


