//<?
 
/* Tag: [textarea name=name&style=style&row=rows&whatever=whatever]value[/textarea] */

$class = e107::getBB()->getClass('textarea');
$tastr = "";
parse_str($parm, $tmp);

foreach($tmp as $key => $p)
{
  $tastr .= $tp -> toAttribute($key)." = '".$tp -> toAttribute($p)."' ";
}
return "<textarea class='{$class}' $tastr>$code_text</textarea>";
