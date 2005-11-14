

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



if(preg_match("#\[list\](.*?)\[/list\]#si", $full_text, $match))
{
	/* unordered list */
	$listitems = explode("*", $match[1]);
	$listtext = "<ul>";
	foreach($listitems as $item)
	{
		if($item && $item != E_NL)
		{
			$listtext .= "<li>$item</li>";
		}
	}
	$listtext .= "</ul>";
	return $listtext;
}
else if(preg_match("#\[list=(.*?)\](.*?)\[/list\]#si", $full_text, $match))
{
	$type = $match[1];
	$listitems = $match[2];
	$listitems = explode("*", $match[2]);
	$listtext = "\n<ol style='list-style-type: $type'>";
	foreach($listitems as $item)
	{
		if($item && $item != E_NL)
		{
			$listtext .= "<li>$item</li>";
		}
	}
	$listtext .= "</ol>";
	return $listtext;
}