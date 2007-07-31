// $Id: wmessage.sc,v 1.5 2007-07-31 19:25:26 e107steved Exp $

$prefwmsc = varset($pref['wmessage_sc'], FALSE);
if (($prefwmsc && $parm == "header") || (!$prefwmsc && ($parm !='header')) )
{	// Two places it might be invoked - allow one or the other
	return;
}

  global $e107,$e107cache;

  if ($parm != "force")
  {
    $full_url = 'news.php';					// Set a default in case
	$uc_array = explode(',', USERCLASS_LIST);
	foreach ($pref['frontpage'] as $fk=>$fp)
	{
	  if (in_array($fk,$uc_array))
	  {
	    $full_url = ((strpos($fp, 'http') === FALSE) ? SITEURL : '').$fp;
	    break;
	  }
	}
	list($front_url,$front_qry) = explode("?",$full_url."?"); // extra '?' ensure the array is filled
  }


	if (($parm == "force") || ((e_SELF == $front_url) && (($parm == "ignore_query") || (e_QUERY == $front_qry)))) 
	{
		// Actually want to display a welcome message here
		global $sql, $pref, $tp, $ns;


		if($cacheData = $e107cache->retrieve("wmessage"))
		{
			echo $cacheData;
   			return;
		}


		if (!defined("WMFLAG")) 
		{
			$qry = "
			SELECT * FROM #generic
			WHERE gen_type ='wmessage' AND gen_intdata IN (".USERCLASS_LIST.")";
            $wmessage = "";
			$wmcaption = "";
			if($sql->db_Select_gen($qry))
			{
				while ($row = $sql->db_Fetch())
				{
					$wmessage .= $tp->toHTML($row['gen_chardata'], TRUE, 'BODY, defs', 'admin')."<br />";
					if(!$wmcaption){
						$wmcaption = $tp->toHTML($row['gen_ip'], TRUE, 'TITLE');
					}
				}
			}

			if (isset($wmessage) && $wmessage)
			{
				ob_start();

				if ($pref['wm_enclose'])
				{
					$ns->tablerender($wmcaption, $wmessage, "wm");
				}
				else
				{
					echo ($wmcaption) ? $wmcaption."<br />" : "";
					echo $wmessage;
				}

				$cache_data = ob_get_flush();
				$e107cache->set("wmessage", $cache_data);
			}
		}
	}


