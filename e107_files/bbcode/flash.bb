// [flash=height,width,bgcolor,quality]path_to_file[/flash]
unset($flParms);
$flParms['bgcolor']='#000000';
$flParms['allowScriptAccess']='sameDomain';
$flParms['quality']='high';
$flParms['wmode']='transparent';
if($parm)
{
	parse_str($parm,$tmp);
	foreach($tmp as $p => $v)
	{
		$flParms[$p]=$v;
	}
}
$parmStr="";
foreach($flParms as $k => $v)
{
	if($k != 'height' && $k != 'width')
	{
		$parmStr .= "<param name='{$k}' value='{$v}'\n";
	}
}

$ret = "<object type='application/x-shockwave-flash' data='{$code_text}' ";
if($flParms['height'])
{
	$ret .= "height='{$flParms['height']}' ";
}
if($flParms['width'])
{
	$ret .= "width='{$flParms['width']}' ";
}
$ret .= ">\n";
$ret .= $parmStr;
$ret .= "</object>\n";
return $ret;