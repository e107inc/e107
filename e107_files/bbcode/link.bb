global $pref;

/*
	[link=$parm $extras]$code_text[/link]
	Correct Usage:
	[link=http://mysite.com external]My text[/link]
	[link=http://mysite.com rel=external]My text[/link]
	[link=external]http://mysite.com[/link]
	[link]http://mysite.com[/link]
	[link=mailto:myemail@email.com]My name[/link]
	Historic usage:
	[link=external=http://mysite.com]My text[/link]
*/


	$parm = trim($parm);

	/* Fix for people using link=external= */
	if(strpos($parm,"external=") !== FALSE)
	{
		list($extras,$parm) = explode("=",$parm,2);
		$parm = $parm." ".$extras;
	}

	if(substr($parm,0,6) == "mailto")
	{
		list($pre,$email) = explode(":",$parm);
		list($p1,$p2) = explode("@",$email);
		$p2=rawurlencode($p2);			// Primarily to pick up spaces, which are not allowed
		return "<a class='bbcode' rel='external' href='javascript:window.location=\"mai\"+\"lto:\"+\"$p1\"+\"@\"+\"$p2\";self.close();' onmouseover='window.status=\"mai\"+\"lto:\"+\"$p1\"+\"@\"+\"$p2\"; return true;' onmouseout='window.status=\"\";return true;'>".$code_text."</a>";
	}

	list($link,$extras) = explode(" ",$parm);

	if(!$parm) $link = $code_text;

	if($link == "external" && $extras == "")
	{
		$link = $code_text;
    	$extras = "rel=external";
	}

	if($extras == "external" || strpos($extras,"rel=external")!==FALSE)
	{
    	$insert = "rel='external' ";
	}
	else
	{
    	$insert = ($pref['links_new_window'] && strpos($link,"{e_")===FALSE && substr($link,0,1) != "#" && strpos($extras,"rel=internal")===FALSE) ? "rel='external' " : "";
    }
	if (strtolower(substr($link,0,11)) == 'javascript:') return '';
	return "<a class='bbcode' href='".$tp -> toAttribute($link)."' ".$insert.">".$code_text."</a>";

