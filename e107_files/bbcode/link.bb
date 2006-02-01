global $pref;

$full_text = str_replace('"','&039;',$full_text);
$_ext      = $pref['links_new_window'] ? " rel=\"external\"" : "";

$search = array(
"#\[link\]([a-z]+?://){1}(.*?)\[/link\]#si",
"#\[link\](.*?)\[/link\]#si",
"#\[link=([a-z]+?://){1}(.*?)\](.*?)\[/link\]#si",
"#\[link=(.*?)\](.*?)\[/link\]#si", 
"#\[url\]([a-z]+?://){1}(.*?)\[/url\]#si",
"#\[url\](.*?)\[/url\]#si",
"#\[url=([a-z]+?://){1}(.*?)\](.*?)\[/url\]#si",
"#\[url=(.*?)\](.*?)\[/url\]#si"

);

$replace = array(
'<a href="\1\2"'.$_ext.'>\1\2</a>',
'<a href="http://\1"'.$_ext.'>\1</a>',
'<a href="\1\2"'.$_ext.'>\3</a>',
'<a href="\1"'.$_ext.'>\2</a>', 
'<a href="\1\2"'.$_ext.'>\1\2</a>',
'<a href="http://\1"'.$_ext.'>\1</a>',
'<a href="\1\2"'.$_ext.'>\3</a>',
'<a href="\1"'.$_ext.'>\2</a>'
);

return preg_replace($search,$replace,$full_text);
