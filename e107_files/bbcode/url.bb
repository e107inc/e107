$full_text = str_replace('"','&039;',$full_text);
$search = array(
"#\[url\]([a-z]+?://){1}(.*?)\[/url\]#si",
"#\[url\](.*?)\[/url\]#si",
"#\[url=([a-z]+?://){1}(.*?)\](.*?)\[/url\]#si",
);

$replace = array(
'<a href="\1\2">\1\2</a>',
'<a href="http://\1">\1</a>',
'<a href="\1\2">\3</a>',
);

return preg_replace($search,$replace,$full_text);
