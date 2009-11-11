<?php

// $Id: wmessage.php,v 1.3 2009-11-11 16:02:40 e107coders Exp $

function wmessage_shortcode($parm)
{

	global $e107, $e107cache, $pref;
	$prefwmsc = varset($pref['wmessage_sc'], FALSE);
	if (($prefwmsc && $parm == 'header') || (!$prefwmsc && ($parm !='header')) )
	{	// Two places it might be invoked - allow one or the other
		return;
	}

	if ($parm != 'force')
	{
		$full_url = 'news.php';					// Set a default in case
		$front_qry = '';
		$uc_array = explode(',', USERCLASS_LIST);
		if(varset($pref['frontpage']))
		{
			foreach ($pref['frontpage'] as $fk => $fp)
			{
				if (in_array($fk,$uc_array))
				{
					$full_url = $fp;
					break;
				}
			}
			list($front_url, $front_qry) = explode('?', $full_url.'?'); // extra '?' ensure the array is filled
		}
	}

	if (strpos($front_url, 'http') === FALSE) $front_url = SITEURL.$front_url;


	if (($parm == 'force') || ((e_SELF == $front_url) && (($parm == 'ignore_query') || (e_QUERY == $front_qry))))
	{
		// Actually want to display a welcome message here
		global $ns;

		if($cacheData = $e107cache->retrieve('wmessage'))
		{
			echo $cacheData;
			return;
		}

		if (!defined('WMFLAG'))
		{

			$qry = "
			SELECT * FROM #generic
			WHERE gen_type ='wmessage' AND gen_intdata IN (".USERCLASS_LIST.')';
			$wmessage = array();
			$wmcaption = '';
			if($e107->sql->db_Select_gen($qry))
			{
				while ($row = $e107->sql->db_Fetch())
				{
					$wmessage[] = $e107->tp->toHTML($row['gen_chardata'], TRUE, 'BODY, defs', 'admin');
					if(!$wmcaption)
					{
						$wmcaption = $e107->tp->toHTML($row['gen_ip'], TRUE, 'TITLE');
					}
				}
			}

			if (isset($wmessage) && $wmessage)
			{
				ob_start();
				if ($pref['wm_enclose'])
				{
				 //	$ns->tablerender($wmcaption, $wmessage, 'wm');
				}
				else
				{
				  	echo ($wmcaption) ? $wmcaption.'<br />' : '';
					echo implode('<br />',$wmessage);
				}

				$cache_data = ob_get_flush();
				$e107cache->set('wmessage', $cache_data);
			}
		}
	}
}
?>