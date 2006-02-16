// USAGE: [flash=width,height]http://www.example.com/file.swf[/flash]

$movie_path   = "http://".eregi_replace("http://", "",$movie_path);
$movie_path   = $tp -> toAttribute($code_text);

$parm_array   = explode(",",$parm);

$width_type   = strstr("%", $parm_array[0]) ? "%" : "px";
$height_type  = strstr("%", $parm_array[1]) ? "%" : "px";

$width_value  = ereg_replace("[^0-9]","",$parm_array[0]);
$height_value = ereg_replace("[^0-9]","",$parm_array[1]);

$width_value  = $width_value  ? $width_value.$width_type   : "50px";
$height_value = $height_value ? $height_value.$height_type : "50px";

return "<object type='application/x-shockwave-flash' data='$movie_path' width='$width_value' height='$height_value'>
		<param name='movie'   value='$movie_path' />
		<param name='quality' value='high' />
		<param name='allowscriptaccess' value='samedomain' />
		</object>";
