if ($pref['wmessage_sc'])
{
	global $e107;
	if (strpos($pref['frontpage'], "http")!==FALSE)
	{
		$front_url = $pref['frontpage'];
	}
	else
	{
		$front_url = $e107->HTTPPath.$pref['frontpage'];
	}
	if (e_SELF == $front_url) {
		global $sql, $pref, $tp, $ns;
		if (!defined("WMFLAG")) {
			$qry = "
			SELECT * FROM #generic
			WHERE gen_type ='wmessage' AND gen_intdata IN (".USERCLASS_LIST.")";

			if($sql->db_Select_gen($qry))
			{
				while ($row = $sql->db_Fetch())
				{
					$wmessage .= $tp->toHTML($row['gen_chardata'], TRUE, 'parse_sc', 'admin')."<br />";
				}
			}

			if (isset($wmessage) && $wmessage)
			{
				if ($pref['wm_enclose'])
				{
					$ns->tablerender("", $wmessage, "wm");
				}
				else
				{
					echo $wmessage;
				}
			}
		}
	}
}
