// $Id: admin_update.sc,v 1.7 2009-09-03 13:30:01 e107coders Exp $


	if (!ADMIN) return "";

	global $e107cache,$ns, $pref;
  	if (!varset($pref['check_updates'], FALSE)) return "";
	
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

	// Keep commented out to be sure it continues to work under all circumstances.

	//if ((strpos(e_SELF,'localhost') !== FALSE) || (strpos(e_SELF,'127.0.0.1') !== FALSE)) return '';

	require_once(e_HANDLER."xml_class.php");
	$xml = new parseXml;
	require_once(e_HANDLER."magpie_rss.php");

    $ftext = "";
	if($rawData = $xml -> getRemoteXmlFile($feed,5))
	{
	  $rss = new MagpieRSS( $rawData );
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
	}
	else
	{  // Error getting data
	  $ftext = ADLAN_154;
	}

	$e107cache->set("updatecheck", $ftext, TRUE);
	if($ftext)
	{
		return $ns -> tablerender(LAN_NEWVERSION, $ftext);
	}


