// $Id$
$prefwmsc = isset($pref['wmessage_sc']) && $pref['wmessage_sc'];
if (($prefwmsc && $parm == "header") || (!$prefwmsc && ($parm !='header')) ){
	return;
}

	global $e107,$e107cache;


	if (isset($pref['frontpage']['all']) && $pref['frontpage']['all']) {
		$full_url = ((strpos($pref['frontpage']['all'], 'http') === FALSE) ? SITEURL : '').$pref['frontpage']['all'];
	} else if (ADMIN) {
		$full_url = ((strpos($pref['frontpage']['254'], 'http') === FALSE) ? SITEURL : '').$pref['frontpage']['254'];
	} else if (USER) {
		require_once(e_HANDLER.'userclass_class.php');
		$class_list = get_userclass_list();
		foreach ($class_list as $fp_class) {
			if (check_class($fp_class['userclass_id'])) {
				$full_url = ((strpos($pref['frontpage'][$fp_class['userclass_id']], 'http') === FALSE) ? SITEURL : '').$pref['frontpage'][$fp_class['userclass_id']];
        $class_match = true;
				break;
			}
		}
		if (!$class_match) {
			$full_url = ((strpos($pref['frontpage']['253'], 'http') === FALSE) ? SITEURL : '').$pref['frontpage']['253'];
		}
	} else {
		$full_url = ((strpos($pref['frontpage']['252'], 'http') === FALSE) ? SITEURL : '').$pref['frontpage']['252'];
	}
	list($front_url,$front_qry) = explode("?",$full_url."?"); // extra '?' ensure the array is filled

	if($parm == "ignore_query"){
    	$front_qry = e_QUERY;
	}

	if($parm == "force"){
    	$front_url = e_SELF;
		$front_qry = e_QUERY;
	}

	if (e_SELF == $front_url && e_QUERY == $front_qry) {
		global $sql, $pref, $tp, $ns;


		if($cacheData = $e107cache->retrieve("wmessage"))
		{
			echo $cacheData;
   			return;
		}


		if (!defined("WMFLAG")) {
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
