<?
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Tiny MCE controller file.
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/tiny_mce/wysiwyg.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-02-19 12:12:45 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/


echo "
    <script language='javascript' type='text/javascript' src='".e_HANDLER."tiny_mce/tiny_mce.js'></script>
	<script language='javascript' type='text/javascript'>
   	tinyMCE.init({
		mode : 'textareas',
		theme : 'advanced',
		plugins : 'table,";

// echo "emotions,";
echo (ADMIN) ? "ibrowser," : "image,";
echo "iespell,zoom,flash',
		theme_advanced_buttons1 : 'bold,italic,underline,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,forecolor,cut,copy,paste,separator,link,unlink,";

echo (ADMIN) ? "ibrowser'," : "image',";
echo "theme_advanced_buttons2	: 'tablecontrols,separator,undo,redo,separator";
// echo ",emotions";
echo ",charmap,iespell,flash";
echo (ADMIN) ? ",code" : "";
echo "',
		theme_advanced_buttons3 : '',
		theme_advanced_toolbar_location : 'top',
		extended_valid_elements : 'a[name|href|title|style],img[class|src|style|alt|title|name],hr[class|size|noshade],span[class|style],div[class|style],table[width|height|border|class|style|cellpadding|cellspacing]',
		invalid_elements: 'font,align,script,object,applet,iframe',
		inline_styles: true,
		debug: false,
		relative_urls: false,
		theme_advanced_styles: 'border=border;fborder=fborder;tbox=tbox;caption=caption;fcaption=fcaption;forumheader=forumheader;forumheader3=forumheader3',
		popup_css: '".THEME."style.css',
		verify_css_classes : false";

echo (ADMIN) ? ", external_link_list_url: '../".$HANDLERS_DIRECTORY."tiny_mce/filelist.php'" : "";

echo "

	});

</script>
";



?>
