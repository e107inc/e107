CREATE TABLE tinymce (
  `tinymce_id` int(5) NOT NULL AUTO_INCREMENT,
  `tinymce_userclass` varchar(255) NOT NULL,
  `tinymce_plugins` text NOT NULL,
  `tinymce_buttons1` varchar(255) NOT NULL,
  `tinymce_buttons2` varchar(255) NOT NULL,
  `tinymce_buttons3` varchar(255) NOT NULL,
  `tinymce_buttons4` varchar(255) NOT NULL,
  `tinymce_custom` text NOT NULL,
  `tinymce_prefs` text NOT NULL,
  PRIMARY KEY (`tinymce_id`)
) TYPE=MyISAM;

INSERT INTO tinymce (
`tinymce_id`, `tinymce_name`, `tinymce_userclass`, `tinymce_plugins`, `tinymce_buttons1`, `tinymce_buttons2`, `tinymce_buttons3`, `tinymce_buttons4`, `tinymce_custom`, `tinymce_prefs`) VALUES 
(1, 'Simple Users', '252', 'e107bbcode,emoticons', 'bold, italic, underline, undo, redo, link, unlink, image, forecolor, bullist, numlist, outdent, indent, emoticons', '', '', '', '', ''),
(2, 'Members', '253', 'e107bbcode,emoticons,table', 'bold, italic, underline, undo, redo, link, unlink, image, forecolor, removeformat, table, bullist, numlist, outdent, indent, emoticons', '', '', '', '', ''),
(3, 'Administrators', '254', 'contextmenu,e107bbcode,emoticons,ibrowser,iespell,paste,table,xhtmlxtras', 'bold, italic, underline, undo, redo, link, unlink, image, forecolor, removeformat, table, bullist, numlist, outdent, indent, cleanup, code, emoticons', '', '', '', '', ''),
(4, 'Main Admin', '250', 'advhr,advlink,autoresize,compat2x,contextmenu,directionality,emoticons,ibrowser,paste,table,visualchars,wordcount,xhtmlxtras,zoom', 'bold, italic, underline, undo, redo, link, unlink, ibrowser, forecolor, removeformat, table, bullist, numlist, outdent, indent, cleanup, code, emoticons', '', '', '', '', ''
);

