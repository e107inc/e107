$np_parm['template'] = "[PREV]&nbsp;&nbsp;[DROPDOWN]&nbsp;&nbsp;[NEXT]";
$np_parms['prev'] = "&nbsp;&nbsp;&lt;&lt;&nbsp;&nbsp;";
$np_parms['next'] = "&nbsp;&nbsp;&gt;&gt;&nbsp;&nbsp;";
$np_parms['np_class'] = 'tbox';
$np_parms['dropdown_class'] = 'tbox';

if($cached_parms = getcachedvars('nextprev'))
{
	$tmp = $cached_parms;
	foreach($tmp as $key => $val)
	{
		$np_parms[$key]=$val;
	}
}
list($total_items, $perpage, $current_start, $url) = explode(".", $parm, 4);
while(substr($url,-1) == ".")
{
	$url=substr($url,0,-1);
}

$current_page = ($current_start/$perpage) + 1;
$total_pages = ceil($total_items/$perpage);

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
return $ret;
