// $Id$
//<?

	if (!ADMIN) return "";

	global $tp, $e107cache,$ns, $pref;
	
  	if (!varset($pref['check_updates'], FALSE)) return "";
	
	list($when,$parm) = explode("|",$parm);
	
	if($when == "adminpanel" && (strpos(e_SELF,e_ADMIN_ABS."admin.php")===FALSE))
	{ 
		return;
	}
	if(($when == "notadminpanel" || $when == '') && (strpos(e_SELF,e_ADMIN_ABS."admin.php")!==FALSE))
	{
		return;
	}
	
	
	if (is_readable(e_ADMIN."ver.php"))
	{
		include(e_ADMIN."ver.php");
	}

	$feed = "http://www.e107.org/releases.php";
	
	$e107cache->CachePageMD5 = e_LANGUAGE;

	$cacheData = $e107cache->retrieve("releasecheck",3600, TRUE);
	
	
    if($cacheData)
    {
    	list($caption,$ftext) = explode('|', $cacheData);
		
    	if($parm == 'text')
		{
			return "<div id='new-version'><div id='new-version-caption'>".$caption."</div><div id='new-version-diz'>".$ftext."</div></div>" ;	
		}
		else
		{
			if ($cacheData == 'up-to-date') return '';			
   	  		return $ns -> tablerender($caption, $ftext,'admin_update');	
		}
		
    }
	
	// Keep commented out to be sure it continues to work under all circumstances.

	//if ((strpos(e_SELF,'localhost') !== FALSE) || (strpos(e_SELF,'127.0.0.1') !== FALSE)) return '';

	require_once(e_HANDLER."xml_class.php");
	
	$xml = new parseXml;
	$xm = new XMLParse();
	
    $ftext = '';
	
	if($rawData = $xml -> getRemoteXmlFile($feed,5))
	{	
		$array = $xm->parse($rawData);
		
		list($cur_version,$tag) = explode(" ", $e107info['e107_version']);

		foreach($array['e107Release']['core'] as $val)
		{
			$val = (array) $val;
	
			$version = varsettrue($val['attributes']['version']);
			$link = varsettrue($val['attributes']['url']);
			$compat = varsettrue($val['attributes']['compatibility']);
	
		 	if(($compat == '0.7') && version_compare($version,$cur_version)==1 || (($compat == '0.7') && version_compare($version,$cur_version)==0 && ($tag =='svn')))
			{
	        	$ftext = "<a rel='external' href='".$link."' >".sprintf(LAN_NEWVERSION_DLD, "e107 v".$version)."</a>\n";
	        	if(varsettrue($val['description']))
	        	{
	        		$ftext .= '<br />'.$tp->toHTML(trim($val['description']), true, 'BODY');
	        	}
	        	if(varsettrue($val['attributes']['infourl']))
	        	{
	        		$ftext .= "<br /><a rel='external' href='".$val['attributes']['infourl']."' >".LAN_NEWVERSION_MORE."</a>\n";
	        	}
				break;
			}
		}
		$caption = LAN_NEWVERSION;
	}
	else // Error getting data
	{
		$ftext = ADLAN_154;
		$caption = LAN_NEWVERSION_CHECK_ERROR;
	}

	if($ftext)
	{
		$e107cache->set("releasecheck", $caption.'|'.$ftext, TRUE);
		return ($parm == 'text') ? "<div id='new-version'><div id='new-version-caption'>".$caption."</div><div id='new-version-diz'>".$ftext."</div></div>" : $ns -> tablerender($caption, $ftext,'admin_update');
	}
	else
	{
		$e107cache->set("releasecheck", 'up-to-date', TRUE);
	}
unset ($ftext, $caption);

