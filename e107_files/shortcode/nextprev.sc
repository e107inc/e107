$np_parms['prev'] = "&nbsp;&nbsp;<<&nbsp;&nbsp;";
$np_parms['next'] = "&nbsp;&nbsp;>>&nbsp;&nbsp;";
$np_parms['template'] = "[PREV]&nbsp;&nbsp;[DROPDOWN]&nbsp;&nbsp;[NEXT]";
$np_parms['np_class'] = 'tbox';
$np_parms['dropdown_class'] = 'tbox';
$np_parms['perpage'] = 1;
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
if($np_parms['currentpage'] > 1)
{
	$pp = ($np_parms['currentpage']-2)*$np_parms['perpage'];
	$link = str_replace("[FROM]", $pp, $np_parms['action']);
	$prev = "<a class='{$np_parms['np_class']}' style='text-decoration:none' href='{$link}'>{$np_parms['prev']}</a>";
}
if($np_parms['currentpage'] < $np_parms['totalpages'])
{
	$np = ($np_parms['currentpage'])*$np_parms['perpage'];
	$link = str_replace("[FROM]", $np, $np_parms['action']);
	$next = "<a class='{$np_parms['np_class']}' style='text-decoration:none' href='{$link}'>{$np_parms['next']}</a>";
}
$form_start = "<span><form method='post' id='frmNextPrev' action='{$np_parms['action']}'>";
$form_end = "</form></span>";

$dropdown = "<select class='{$np_parms['dropdown_class']}' name='pageSelect' OnChange='location.href=this.options[selectedIndex].value'>";
for($i = 1; $i <= $np_parms['totalpages']; $i++)
{
	$sel = "";
	if($np_parms['currentpage'] == $i)
	{
		$sel = " selected='selected' ";
	}
	$np = ($i-1)*$np_parms['perpage'];
	$link = str_replace("[FROM]", $np, $np_parms['action']);
	$dropdown .= "<option value='{$link}' {$sel}>{$i}</option>\n";
}
$dropdown .= "</select>";
$ret = $np_parms['template'];
$ret = str_replace('[DROPDOWN]', $dropdown, $ret);
$ret = str_replace('[PREV]', $prev, $ret);
$ret = str_replace('[NEXT]', $next, $ret);
return $form_start.$ret.$form_end;
