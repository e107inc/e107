<?
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Tiny MCE controller file.
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/tiny_mce/wysiwyg.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-02-22 02:29:03 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

function wysiwyg($formids){
$text = "
    <script language='javascript' type='text/javascript' src='".e_HANDLER."tiny_mce/tiny_mce.js'></script>
	<script language='javascript' type='text/javascript'>
   	tinyMCE.init({
		mode : 'exact',
		elements : '".$formids."',
		theme : 'advanced',
		plugins : 'table,";

// $text .= "emotions,";
$text .= (ADMIN) ? "ibrowser," : "image,";
$text .= "iespell,zoom,flash',
		theme_advanced_buttons1 : 'bold,italic,underline,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,forecolor,cut,copy,paste,separator,link,unlink,";

$text .= (ADMIN) ? "ibrowser'," : "image',";
$text .= "theme_advanced_buttons2	: 'tablecontrols,separator,undo,redo,separator";
// $text .= ",emotions";
$text .= ",charmap,iespell,flash";
$text .= (ADMIN) ? ",code" : "";
$text .= "',
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

$text .= (ADMIN) ? ", external_link_list_url: '../".$HANDLERS_DIRECTORY."tiny_mce/filelist.php'" : "";

$text .= "

	});

</script>
";

return $text;

}


?>