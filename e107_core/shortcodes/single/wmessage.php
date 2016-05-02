<?php

function wmessage_shortcode($parm='')
{
	if($parm == 'hide')
	{
		return;
	}
	
	$e107 		= e107::getInstance();
	$e107cache 	= e107::getCache();
	$pref 		= e107::getPref();
	
	$front_url = '';
	
	/* DEPRECATED - see auto-detect in header_default.php 
	$prefwmsc = varset($pref['wmessage_sc'], FALSE);
	if (($prefwmsc && $parm == 'header') || (!$prefwmsc && ($parm !='header')) )
	{	// Two places it might be invoked - allow one or the other
	//	return;
	}
	*/
	
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

	if (deftrue('e_FRONTPAGE') || ($parm == 'force') || ((e_SELF == $front_url) && (($parm == 'ignore_query') || (e_QUERY == $front_qry))))
	{
		// Actually want to display a welcome message here
		$ns = e107::getRender();
		$tp = e107::getParser();
		$sql = e107::getDb();

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
			$wmessageCaption = array();
			$wmcaption = '';
			if($sql->gen($qry))
			{
				while ($row = $sql->fetch())
				{
					$wmessage[] = $tp->toHTML($row['gen_chardata'], TRUE, 'BODY, defs', 'admin');
					$wmessageCaption[] =  $tp->toHTML($row['gen_ip'], TRUE, 'TITLE');
					if(!$wmcaption)
					{
						$wmcaption = $tp->toHTML($row['gen_ip'], TRUE, 'TITLE');
					}
				}
			}


			if (isset($wmessage) && $wmessage)
			{
				ob_start();
				if(intval($pref['wm_enclose']) === 2) // carousel
				{
					$carousel= array();
					foreach($wmessage as $k=>$v)
					{
						$carousel['slide-'.$k] = array('caption'=>$wmessageCaption[$k], 'text'=>$ns->tablerender($wmessageCaption[$k],$v, 'wm',true));
					}

					echo e107::getForm()->carousel('wmessage-carousel',$carousel);
				}
				elseif(intval($pref['wm_enclose']) === 1)
				{
				 	$ns->tablerender($wmcaption, implode("<br />",$wmessage), 'wm');
				}
				else
				{
					echo "<div class='wmessage'>";
				  	echo ($wmcaption) ? $wmcaption.'<br />' : '';
					echo implode('<br />',$wmessage);
					echo "</div>";
				}

				$cache_data = ob_get_flush();
				$e107cache->set('wmessage', $cache_data);
			}
		}
	}
}
?>