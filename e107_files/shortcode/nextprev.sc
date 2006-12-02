
global $pref;

@include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_np.php");
@include_once(e_LANGUAGEDIR."English/lan_np.php");

$parm_count = substr_count($parm, ",");

while($parm_count < 4)
{
	$parm .= ",";
	$parm_count++;
}

$p = explode(",", $parm, 5);

$total_items = intval($p[0]);
$perpage = intval($p[1]);
$current_start = intval($p[2]);
$url = trim($p[3]);
$caption = trim($p[4]);

if($total_items < $perpage) {	return ""; }

$caption = (!$caption || $caption == "off") ? NP_3."&nbsp;" : $caption;

while(substr($url, -1) == ".")
{
	$url=substr($url, 0, -1);
}

$current_page = ($current_start/$perpage) + 1;
$total_pages = ceil($total_items/$perpage);

if($total_pages > 1)
{
	if(isset($pref['old_np']) && $pref['old_np'])
	{

		$NP_PRE_ACTIVE = "";
		$NP_POST_ACTIVE = "";
		$NP_STYLE = "";

        if(!defined("NEXTPREV_NOSTYLE") || NEXTPREV_NOSTYLE==FALSE){
        	$NP_PRE_ACTIVE = "[";
            $NP_POST_ACTIVE = "] ";
			$NP_STYLE = "style='text-decoration:underline'";
		}


		//	Use OLD nextprev method
		$nppage = '';
		$nppage .= "\n\n<!-- Start of Next/Prev -->\n\n";
		if ($total_pages > 10)
		{
			$current_page = ($current_start/$perpage)+1;

			for($c = 0; $c <= 2; $c++)
			{
				if($perpage * $c == $current_start)
				{
					$nppage .= $NP_PRE_ACTIVE."<span class='nextprev_current' {$NP_STYLE} >".($c+1)."</span>".$NP_POST_ACTIVE."\n";
				}
				else
				{
					$link = str_replace("[FROM]", ($perpage * $c), $url);
					$nppage .= "<a class='nextprev_link' href='{$link}'>".($c+1)."</a> \n";

				}
			}

			if ($current_page >= 3 && $current_page <= 5)
			{
				for($c = 3; $c <= $current_page; $c++)
				{
					if($perpage * $c == $current_start)
					{
						$nppage .= $NP_PRE_ACTIVE."<span class='nextprev_current' {$NP_STYLE} >".($c+1)."</span>".$NP_POST_ACTIVE."\n";
					}
					else
					{
						$link = str_replace("[FROM]", ($perpage * $c), $url);
						$nppage .= "<a class='nextprev_link' href='{$link}'>".($c+1)."</a> \n";
					}
				}
			}
			else if($current_page >= 6 && $current_page <= ($total_pages-5))
			{
				$nppage .= " ... ";
				for($c = ($current_page-2); $c <= $current_page; $c++)
				{
					if($perpage * $c == $current_start)
					{
						$nppage .= $NP_PRE_ACTIVE."<span class='nextprev_current' {$NP_STYLE} >".($c+1)."</span>".$NP_POST_ACTIVE."\n";
					}
					else
					{
						$link = str_replace("[FROM]", ($perpage * $c), $url);
						$nppage .= "<a class='nextprev_link' href='{$link}'>".($c+1)."</a> \n";
					}
				}
			}
			$nppage .= " ... ";

			if (($current_page+5) > $total_pages && $current_page != $total_pages)
			{
				$tmp = ($current_page-2);
			}
			else
			{
				$tmp = $total_pages-3;
			}

			for($c = $tmp; $c <= ($total_pages-1); $c++)
			{
				if($perpage * $c == $current_start)
				{
					$nppage .= $NP_PRE_ACTIVE."<span class='nextprev_current' {$NP_STYLE} >".($c+1)."</span>".$NP_POST_ACTIVE."\n";
				}
				else
				{
					$link = str_replace("[FROM]", ($perpage * $c), $url);
					$nppage .= "<a class='nextprev_link' href='{$link}'>".($c+1)."</a> \n";
				}
			}

		}
		else
		{
			for($c = 0; $c < $total_pages; $c++)
			{
				if($perpage * $c == $current_start)
				{
					$nppage .= $NP_PRE_ACTIVE."<span class='nextprev_current' {$NP_STYLE} >".($c+1)."</span>".$NP_POST_ACTIVE."\n";
				}
				else
				{
					$link = str_replace("[FROM]", ($perpage * $c), $url);
					$nppage .= "<a class='nextprev_link' href='{$link}'>".($c+1)."</a> \n";
				}
			}
		}
        $nppage .= "\n\n<!-- End of Next/Prev -->\n\n";
		return $caption.$nppage;
	}

	// Use NEW nextprev method
	$np_parm['template'] = "[PREV]&nbsp;&nbsp;[DROPDOWN]&nbsp;&nbsp;[NEXT]";
	$np_parms['prev'] = "&nbsp;&nbsp;&lt;&lt;&nbsp;&nbsp;";
	$np_parms['next'] = "&nbsp;&nbsp;&gt;&gt;&nbsp;&nbsp;";
	$np_parms['np_class'] = 'tbox npbutton';
	$np_parms['dropdown_class'] = 'tbox npdropdown';

	if($cached_parms = getcachedvars('nextprev'))
	{
		$tmp = $cached_parms;
		foreach($tmp as $key => $val)
		{
			$np_parms[$key]=$val;
		}
	}

	$prev="";
	$next="";
	if($current_page > 1)
	{
		$prevstart = ($current_start - $perpage);
		$link = str_replace("[FROM]", $prevstart, $url);
		$prev = "<a class='{$np_parms['np_class']}' style='text-decoration:none' href='{$link}'>{$np_parms['prev']}</a>";
	}
	if($current_page < $total_pages)
	{
		$nextstart = ($current_start + $perpage);
		$link = str_replace("[FROM]", $nextstart, $url);
		$next = "<a class='{$np_parms['np_class']}' style='text-decoration:none' href='{$link}'>{$np_parms['next']}</a>";
	}
	$dropdown = "<select class='{$np_parms['dropdown_class']}' name='pageSelect' onchange='location.href=this.options[selectedIndex].value'>";
	for($i = 1; $i <= $total_pages; $i++)
	{
		$sel = "";
		if($current_page == $i)
		{
			$sel = " selected='selected' ";
		}
		$newstart = ($i-1)*$perpage;
		$link = str_replace("[FROM]", $newstart, $url);
		$dropdown .= "<option value='{$link}' {$sel}>{$i}</option>\n";
	}
	$dropdown .= "</select>";
	$ret = $np_parm['template'];
	$ret = str_replace('[DROPDOWN]', $dropdown, $ret);
	$ret = str_replace('[PREV]', $prev, $ret);
	$ret = str_replace('[NEXT]', $next, $ret);
	return $caption.$ret;
}
return "";
