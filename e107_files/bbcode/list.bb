/* Tag: unordered list [list]*line 1*line2*line 3*line 4*line5 etc[/list] */
/* Tag: ordered list [list=<list type>]*line 1*line2*line 3*line 4*line5 etc[/list] */
/* valid list types: 
				disc
				circle
				square
				decimal		1, 2, 3	(default)
				lower-roman	i, ii, iii
				upper-roman	I, II, III
				lower-alpha	a, b, c
				upper-alpha	A, B, C
*/


$listitems = explode("*", $code_text);

if ($parm == '')
{	/* unordered list */
  $listtext = "<ul>";
  $trailer = "</ul>";
}
else
{
  $type = $tp -> toAttribute($parm);
  $listtext = "\n<ol style='list-style-type: $type'>";
  $trailer = "</ol>";
}
foreach($listitems as $item)
{
  if($item && $item != E_NL)
  {
	$listtext .= "<li>$item</li>";
  }
}
return $listtext.$trailer;
