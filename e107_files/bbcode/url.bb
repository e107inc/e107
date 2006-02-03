global $pref;

$external = $pref['links_new_window'] || strpos($full_text,"[url=external") !== FALSE ? ' rel="external"' : '';

$search = array(
'#"#s',                                     // PREVENT QUOTES BEING USED TO BREAK OUT OF HTML
'#\[url=external#s',                        // REMOVE =external ATTRIBUTE FROM BBCODE
'#\[url=([\w]+?://.*?)\](.*?)\[/url\]#si',  // [url=PROTOCOL://SERVER.COM]DESCRIPTION[/url]
'#\[url=(.*?\..*?)\](.*?)\[/url\]#si',      // [url=*.*]DESCRIPTION[/url]
'#\[url=(.*?)\]([\w]+?://.*?)\[/url\]#si',  // [url=NODOTNOTUSED]PROTOCOL://SERVER.COM[/url]
'#\[url=(.*?)\](.*?)\[/url\]#s',            // [url=NODOTNOTUSED]SERVER.COM[/url]
'#\[url\]([\w]+?://.*?)\[/url\]#si',        // [url]PROTOCOL://SERVER.COM[/url]
'#\[url\](.*?)\[/url\]#s',                  // [url]SERVER.COM[/url]
);

$replace = array(
'',
'[url',
'<a href="\1"'.$external.'>\2</a>',
'<a href="http://\1"'.$external.'>\2</a>',
'<a href="\2"'.$external.'>\2</a>',
'<a href="http://\2"'.$external.'>http://\2</a>',
'<a href="\1"'.$external.'>\1</a>',
'<a href="http://\1"'.$external.'>http://\1</a>',
);

return preg_replace($search,$replace,$full_text);