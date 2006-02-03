global $pref;

$external = $pref['links_new_window'] || strpos($full_text,"[link=external") !== FALSE ? ' rel="external"' : '';

$search = array(
'#"#s',                                       // PREVENT QUOTES BEING USED TO BREAK OUT OF HTML
'#\[link=external#s',                         // REMOVE =external ATTRIBUTE FROM BBCODE
'#\[link=([\w]+?://.*?)\](.*?)\[/link\]#si',  // [link=PROTOCOL://SERVER.COM]DESCRIPTION[/link]
'#\[link=(.*?\..*?)\](.*?)\[/link\]#si',      // [link=*.*]DESCRIPTION[/link]
'#\[link=(.*?)\]([\w]+?://.*?)\[/link\]#si',  // [link=NODOTNOTUSED]PROTOCOL://SERVER.COM[/link]
'#\[link=(.*?)\](.*?)\[/link\]#s',            // [link=NODOTNOTUSED]SERVER.COM[/link]
'#\[link\]([\w]+?://.*?)\[/link\]#si',        // [link]PROTOCOL://SERVER.COM[/link]
'#\[link\](.*?)\[/link\]#s',                  // [link]SERVER.COM[/link]
);

$replace = array(
'',
'[link',
'<a href="\1"'.$external.'>\2</a>',
'<a href="http://\1"'.$external.'>\2</a>',
'<a href="\2"'.$external.'>\2</a>',
'<a href="http://\2"'.$external.'>http://\2</a>',
'<a href="\1"'.$external.'>\1</a>',
'<a href="http://\1"'.$external.'>http://\1</a>',
);

return preg_replace($search,$replace,$full_text);