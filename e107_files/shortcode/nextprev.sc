$np_parms['prev'] = "<<";
$np_parms['next'] = ">>";
$np_parms['template'] = "[PREV] [DROPDOWN] [NEXT]";
$np_parms['action'] = e_SELF.'?'.e_QUERY;
$np_parms['np_class'] = 'tbox';
$np_parms['dropdown_class'] = 'tbox';
if($cached_parms = getcachedvars('nextprev'))
{
	$tmp = $cached_parms;
}	
if($parm || is_array($tmp))
{
	if(!is_array($tmp))
	{
		parse_str($parm,$tmp);
	}
	foreach($tmp as $key => $val)
	{
		$np_parms[$key]=$val;
	}
}
$form_start = "<span><form method='post' action='{$np_parms['action']}'>";
$form_end = "</form></span>";

$dropdown = "<select class='{$np_parms['dropdown_class']}' name='pageSelect' onchange='this.form.submit()'>";
for($i = 1; $i <= $np_parms['totalpages']; $i++)
{
	if($np_parms['currentpage'] == $i)
	{
		$sel = " selected='selected' ";
	}
	else
	{
		$sel = "";
	}
	$dropdown .= "<option value='{$i}' {$sel}>{$i}</option>\n";
}
$dropdown .= "</select>";
if($np_parms['currentpage'] >1)
{
	$prev = "
	<input type='hidden' name='pagePrev' value='".($np_parms['currentpage']-1)."' />
	<input type='submit' class='{$np_parms['np_class']}' name='submitPrev' value='".$np_parms['prev']."' />
	";
}
else
{
	$prev = "";
}

if($np_parms['currentpage'] < $np_parms['totalpages'])
{
	$next = "
	<input type='hidden' name='pageNext' value='".($np_parms['currentpage']+1)."' />
	<input type='submit' class='{$np_parms['np_class']}' name='submitNext' value='".$np_parms['next']."' />
	";
}
else
{
	$prev = "";
}

$ret = $np_parms['template'];
$ret = str_replace('[DROPDOWN]', $dropdown, $ret);
$ret = str_replace('[PREV]', $prev, $ret);
$ret = str_replace('[NEXT]', $next, $ret);
return $form_start.$ret.$form_end;
