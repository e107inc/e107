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
'<a href="\1\2" rel="nofollow">\1\2</a>',
'<a href="http://\1" rel="nofollow">\1</a>',
'<a href="\1\2" rel="nofollow">\3</a>',
'<a href="\1" rel="nofollow">\2</a>', 
'<a href="\1\2" rel="nofollow">\1\2</a>',
'<a href="http://\1" rel="nofollow">\1</a>',
'<a href="\1\2" rel="nofollow">\3</a>',
'<a href="\1" rel="nofollow">\2</a>'
);

return preg_replace($search,$replace,$full_text);
