unset($stream_parms);
$stream_parms['autostart']=1;
$stream_parms['showcontrols']=1;
$stream_parms['showstatusbar']=1;
$stream_parms['autorewind']=1;
$stream_parms['showdisplay']=1;

if($parm)
{
	parse_str($parm,$tmp);
	foreach($tmp as $p => $v)
	{
		$stream_parms[$p]=$v;
	}
}
$parmStr="";
foreach($stream_parms as $k => $v)
{
	$MozparmStr .= "<param name='{$k}' value='".($v ? "true" : "false")."'\n";
	$IEparmStr .= $k."='".$v."' ";
}

$ret = "
<object id='MediaPlayer' classid='CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95' standby='Loading Microsoft® Windows® Media Player components...' type='application/x-oleobject' codebase='http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,4,7,1112'>\n";
$ret .= "<param name='filename' VALUE='{$code_text}'>\n";
$ret .= $MozparmStr;
$ret .= "<embed src='{$code_text}' WIDTH='320' HEIGHT='360' id='mediaPlayer' name='mediaPlayer' {$IEparmStr}>
</object>
";

return $ret;




