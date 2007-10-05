global $pref;

/*
	[link=$parm $extras]$code_text[/link]
	Correct Usage:
	[link=http://mysite.com external]My text[/link]
	[link=http://mysite.com rel=external]My text[/link]
	[link=external]http://mysite.com[/link]
	[link]http://mysite.com[/link]
	[link=mailto:myemail@email.com]My name[/link]
*/

	$parm = trim($parm);

	/* Fix for people using link=external= */
	if(strpos($parm,"external=") !== FALSE)
	{
		list($extras,$parm) = explode("=",$parm);
		$parm = $parm." ".$extras;
	}

	if(substr($parm,0,6) == "mailto")
	{
		list($pre,$email) = explode(":",$parm);
		list($p1,$p2) = explode("@",$email);
		return "<a class='bbcode' rel='external' href='javascript:window.location=\"mai\"+\"lto:\"+\"$p1\"+\"@\"+\"$p2\";self.close();' onmouseover='window.status=\"mai\"+\"lto:\"+\"$p1\"+\"@\"+\"$p2\"; return true;' onmouseout='window.status=\"\";return true;'>".$code_text."</a>";
	}

	list($link,$extras) = explode(" ",$parm);

	if($link == "external" && $extras == "")
	{
		$link = $code_text;
    	$extras = "rel=external";
	}

    $insert = (($pref['links_new_window'] || $extras == "external" || strpos($extras,"rel=external")!==FALSE) && strpos($link,"{e_")===FALSE && substr($link,0,1) != "#") ? "rel='external' " : "";

	if (strtolower(substr($link,0,11)) == 'javascript:') return '';
	return "<a class='bbcode' href='".$tp -> toAttribute($link)."' ".$insert.">".$code_text."</a>";

