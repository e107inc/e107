<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Tiny MCE controller file.
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/tiny_mce/wysiwyg.php,v $
|     $Revision: 1.14 $
|     $Date: 2005-08-08 12:54:04 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/

function wysiwyg($formids){
define("ADMIN","");
global $pref,$HANDLERS_DIRECTORY;
$lang = e_LANGUAGE;
$tinylang = array(
"English" => "en",
"Swedish" => "sv",
"French" => "fr",
"Spanish" => "es",
"Greek" => "el",
"Italian" => "it",
"Dutch" => "nl",
"Polish" => "pl",
"Japanese" => "ja",
"Korean" => "ko",
"Danish" => "da",
"Russian" => "ru",
"Hungarian" => "hu");


$text = "
	<script type='text/javascript' src='".e_HANDLER."tiny_mce/tiny_mce.js'></script>
	<script type='text/javascript'>\n
	tinyMCE.init({\n";
$text .= "language : '".$tinylang[$lang]."',\n";
$text .= "mode : 'exact',\n";
$text .= "elements : '".$formids."',\n";
$text .= "theme : 'advanced'\n";
$text .= ",plugins : 'table,contextmenu";

$text .= ($pref['smiley_activate']) ? ",emoticons" : "";
$text .= (ADMIN) ? ",ibrowser" : ",image";
$text .= ",iespell,zoom,flash,forecolor";
$text .= "'\n"; // end of plugins list.

$text .= ",theme_advanced_buttons1 : 'bold,italic,underline,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,outdent, indent,separator, forecolor,cut,copy,paste,separator,link,unlink'";

$text .= ",theme_advanced_buttons2   : 'tablecontrols,separator,undo,redo,separator";
$text .= ($pref['smiley_activate']) ? ",emoticons" : "";
$text .= ",charmap,iespell,flash";
$text .= (ADMIN) ? ",ibrowser," : ",image";
$text .= (ADMIN) ? ",code" : "";
$text .= "'"; // end of buttons 2

$text .= ",theme_advanced_buttons3 : ''";
$text .= ",theme_advanced_toolbar_location : 'top'";
$text .= ",extended_valid_elements : 'p[style],a[name|href|title|style],img[class|src|style|alt|title|name],hr[class],span[class|style],div[class|style],table[class|style|cellpadding|cellspacing]'";
$text .= ",invalid_elements: 'p,font,align,script,applet,iframe'\n";
$text .= ",auto_cleanup_word: true\n";
$text .= ",trim_span_elements: true\n";
$text .= ",inline_styles: true\n";
$text .= ",debug: false\n";
$text .= ",force_br_newlines: true\n";
$text .= ",force_p_newlines: false\n";
$text .= ",relative_urls: true\n";
$text .= ",document_base_url: '".SITEURL."'\n";
$text .= ",theme_advanced_styles: 'border=border;fborder=fborder;tbox=tbox;caption=caption;fcaption=fcaption;forumheader=forumheader;forumheader3=forumheader3'\n";
$text .= ",popup_css: '".THEME."style.css'\n";
$text .= ",verify_css_classes : false\n";

$text .= (ADMIN) ? "\n, external_link_list_url: '../".$HANDLERS_DIRECTORY."tiny_mce/filelist.php'\n" : "";

$text .= "

	});

</script>\n
";

return $text;

}


?>