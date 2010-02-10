/* Tag: unordered list [list][*]line 1[*]line2[*]line 3[*]line 4[*]line5 etc[/list]  - preferred */
/* Tag: ordered list [list=<list type>][*]line 1[*]line2[*]line 3[*]line 4[*]line5 etc[/list]  - preferred */
/* Tag: unordered list [list]*line 1*line2*line 3*line 4*line5 etc[/list]  - legacy*/
/* Tag: ordered list [list=<list type>]*line 1*line2*line 3*line 4*line5 etc[/list] - legacy */
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

if (strpos($code_text,"[*]") !== FALSE)
{
  $listitems = explode("[*]", $code_text);
}
else
{
  $listitems = explode("*", $code_text);
}

if ($parm == '')
{	/* unordered list */
  $listtext = "<ul class='bbcode'>";
  $trailer = "</ul>";
}
else
{
  $type = $tp -> toAttribute($parm);
  $listtext = "\n<ol class='bbcode' style='list-style-type: $type'>";
  $trailer = "</ol>";
}
foreach($listitems as $item)
{
  if($item && $item != E_NL)
  {
	$listtext .= "<li class='bbcode'>$item</li>";
  }
}
return $listtext.$trailer;
