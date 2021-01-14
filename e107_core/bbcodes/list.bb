//<?php
/* Tag: unordered list [list]*line 1*line2*line 3*line 4*line5 etc[/list] */
/* Tag: ordered list [list=<list type>]*line 1*line2*line 3*line 4*line5 etc[/list]  */
/* Tag: unordered list [list][*]line 1[*]line2[*]line 3[*]line 4[*]line5 etc[/list]  - not compatible with TinyMce */
/* Tag: ordered list [list=<list type>][*]line 1[*]line2[*]line 3[*]line 4[*]line5 etc[/list]  - not compatible with TinyMce */
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

$class = e107::getBB()->getClass('list');

if (strpos($code_text,"[*]") !== FALSE)
{
  $listitems = explode("[*]", $code_text);
}
else
{
  $listitems = explode("*", $code_text);
}

if (empty($parm))
{	/* unordered list */
  $listtext = "<ul class='bbcode'>";
  $trailer = "</ul>";
  $type = '';
}
else
{
  $type = e107::getParser()->toAttribute($parm);
  $listtext = "\n<ol class='bbcode ".$type."' style='list-style-type: $type'>";
  $trailer = "</ol>";
}
foreach($listitems as $item)
{
  if($item && $item != E_NL)
  {
	$listtext .= "<li class='bbcode ".$type." {$class}'>$item</li>";
  }
}
return $listtext.$trailer;
