

/* Tag: [textarea name=name&style=style&row=rows&whatever=whatever]value[/textarea] */


$tastr = "";

parse_str($parm, $tmp);

foreach($tmp as $key => $p)
{
	$tastr .= $key." = '$p' ";
}


preg_match ("#\](.*?)\[/textarea#", $full_text, $match);
$value = $match[1];

return "<textarea $tastr>$value</textarea>";

