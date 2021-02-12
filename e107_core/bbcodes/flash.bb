//<?
$class = e107::getBB()->getClass('flash');

// USAGE: [flash=width,height,param_name=value&param_name=value]http://www.example.com/file.swf[/flash]

$movie_path = e107::getParser()->toAttribute($code_text);

$parm_array = explode(',', $parm);
$width = preg_replace('#[^0-9%]#', '', varsettrue($parm_array[0], 50));
$height= preg_replace('#[^0-9%]#', '', varsettrue($parm_array[1], 50));

$text = "
<object class='{$class}' type='application/x-shockwave-flash' data='{$movie_path}' width='{$width}' height='{$height}'>
	<param name='movie' value='{$movie_path}' />
	<param name='quality' value='high' />
	<param name='allowscriptaccess' value='samedomain' />
";

if(isset($parm_array[2]))
{
	parse_str($parm_array[2], $extraParms);
	foreach($extraParms as $_parm => $_val)
	{
		if($_parm && $_val) {
			$text .= "\t<param name='{$_parm}' value='{$_val}' />\n";
		}
	}
}

$text .= "</object>";
return $text;
