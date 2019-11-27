//<?
$class = e107::getBB()->getClass('stream');
unset($stream_parms);

if($parm)
{
	parse_str($parm,$tmp);
	foreach($tmp as $p => $v)
	{
		$stream_parms[$p]=$v;
	}
}

$stream_parms['autostart'] = isset($stream_parms['autostart']) ? $stream_parms['autostart'] : 'true';
$stream_parms['showcontrols'] = isset($stream_parms['showcontrols']) ? $stream_parms['showcontrols'] : 'true';
$stream_parms['showstatusbar'] = isset($stream_parms['showstatusbar']) ? $stream_parms['showstatusbar'] : 'true';
$stream_parms['autorewind'] = isset($stream_parms['autorewind']) ? $stream_parms['autorewind'] : 'true';
$stream_parms['showdisplay'] = isset($stream_parms['showdisplay']) ? $stream_parms['showdisplay'] : 'true';

if (isset($stream_parms['width'])) {
	$width = $stream_parms['width'];
	unset($stream_parms['width']);
} else {
	$width = '320';
}

if (isset($stream_parms['height'])) {
	$height = $stream_parms['height'];
	unset($stream_parms['height']);
} else {
	$height = '360';
}

$parmStr="";
foreach($stream_parms as $k => $v)
{
	$MozparmStr .= "<param name='".$tp -> toAttribute($k)."' value='".$tp -> toAttribute($v)."'>\n";
	$IEparmStr .= $tp -> toAttribute($k)."='".$tp -> toAttribute($v)."' ";
}

$ret = "
<object class='{$class}' id='MediaPlayer' classid='CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95' standby='Loading Microsoft� Windows� Media Player components...' type='application/x-oleobject' codebase='http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,4,7,1112' width='".$tp -> toAttribute($width)."' height='".$tp -> toAttribute($height)."'>\n";
$ret .= "<param name='filename' value='".$tp -> toAttribute($code_text)."'>\n";
$ret .= $MozparmStr;
$ret .= "<embed src='".$tp -> toAttribute($code_text)."' width='".$tp -> toAttribute($width)."' height='".$tp -> toAttribute($height)."' id='mediaPlayer' name='mediaPlayer' {$IEparmStr}>
</object>
";

return $ret;




