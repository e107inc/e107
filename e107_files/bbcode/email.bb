$search = array(
"#\[email\](.*?)\[/email\]#si",
"#\[email=(.*?){1}(.*?)\](.*?)\[/email\]#si",
);

$replace = array(
'<a href="mailto:\1">\1</a>',
'<a href="mailto:\1\2">\3</a>',
);

return preg_replace($search,$replace,$full_text);
