<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Tiny MCE controller file.
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/tiny_mce/wysiwyg.php,v $
|     $Revision: 1.38 $
|     $Date: 2009-07-07 23:17:41 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

function wysiwyg($formids)
{
global $pref,$HANDLERS_DIRECTORY,$PLUGINS_DIRECTORY,$IMAGES_DIRECTORY;
$lang = e_LANGUAGE;
$tinylang = array(
	"Arabic" 	=> "ar",
	"Danish" 	=> "da",
	"Dutch" 		=> "nl",
	"English" 	=> "en",
	"Farsi" 		=> "fa",
	"French" 	=> "fr",
	"German"		=> "de",
	"Greek" 		=> "el",
	"Hebrew" 	=> " ",
	"Hungarian" => "hu",
	"Italian" 	=> "it",
	"Japanese" 	=> "ja",
	"Korean" 	=> "ko",
	"Norwegian" => "nb",
	"Polish" 	=> "pl",
	"Russian" 	=> "ru",
	"Slovak" 	=> "sk",
	"Spanish" 	=> "es",
	"Swedish" 	=> "sv"
);

if(!$tinylang[$lang]){
 $tinylang[$lang] = "en";
}

$thescript = (strpos($_SERVER['SERVER_SOFTWARE'],"mod_gzip")) ? "tiny_mce_gzip.php" : "tiny_mce.js";

$text = "<script type='text/javascript' src='".e_HANDLER."tiny_mce/".$thescript."'></script>\n";

$text .= "<script type='text/javascript'>\n	tinyMCE.init({\n";

$text .= "language : '".$tinylang[$lang]."',\n";
$text .= "mode : 'exact',\n";
$text .= "elements : '".$formids."',\n";
$text .= "theme : 'advanced'\n";
$text .= ",plugins : 'table,contextmenu";

$text .= ($pref['smiley_activate']) ? ",emoticons" : "";
$text .= (ADMIN) ? ",ibrowser" : ",image";
$text .= ",iespell,zoom,media,compat2x";
$text .= "'\n"; // end of plugins list.

$text .= ",theme_advanced_buttons1 : 'fontsizeselect,separator,bold,italic,underline,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,outdent, indent,separator, forecolor,cut,copy,paste'";

$text .= ",theme_advanced_buttons2   : 'tablecontrols,separator,undo,redo,separator,link,unlink";
$text .= ($pref['smiley_activate']) ? ",emoticons" : "";
$text .= ",charmap,iespell,media";
$text .= (ADMIN) ? ",ibrowser," : ",image";
$text .= (ADMIN) ? ",code" : "";
$text .= "'"; // end of buttons 2

$text .= ",theme_advanced_buttons3 : ''";
$text .= ",theme_advanced_toolbar_location : 'top'";
$text .= ",extended_valid_elements : 'p[style],a[name|href|target|rel|title|style|class],img[class|src|style|alt|title|name],hr[class],span[align|class|style],div[align|class|style|height|width] ,table[class|style|cellpadding|cellspacing|background|height|width],td[background|style|class|valign|align|height|width]'";
$text .= ",invalid_elements: 'p,font,align,script,applet,iframe'\n";
$text .= ",auto_cleanup_word: true\n";
$text .= ",convert_fonts_to_spans : true\n";
$text .= ",trim_span_elements: true\n";
$text .= ",inline_styles: true\n";
$text .= ",debug: false\n";
$text .= ",force_br_newlines: true\n";
$text .= ",force_p_newlines: false\n";
$text .= ",entity_encoding: \"raw\" \n";
$text .= ",convert_fonts_to_styles: true\n";
$text .= ",remove_script_host : true\n";
$text .= ",relative_urls: true\n";
$text .= ",document_base_url: '".SITEURL."'\n";
$text .= ",theme_advanced_styles: 'border=border;fborder=fborder;tbox=tbox;caption=caption;fcaption=fcaption;forumheader=forumheader;forumheader3=forumheader3'\n";
$text .= ",popup_css: '".THEME."style.css'\n";
$text .= ",verify_css_classes : false\n";
$text .= ",cleanup_callback : \"tinymce_html_bbcode_control\" \n";
$text .= (ADMIN) ? "\n, external_link_list_url: '../".$HANDLERS_DIRECTORY."tiny_mce/filelist.php'\n" : "";

$text .= "

	});

function tinymce_html_bbcode_control(type, source) {

    switch (type) {

        case 'get_from_editor':
            // Convert HTML to e107-BBcode
            source = source.replace(/target=\"_blank\"/, 'rel=\"external\"');
            source = source.replace(/^\s*|\s*$/g,'');
            if(source != '')
            {
                source = '[html]\\n' + source + '\\n[/html]';";
/*
				source = source.replace(/<\/strong>/gi,'[/b]');
                source = source.replace(/<strong>/gi,'[b]');
                source = source.replace(/<\/em>/gi,'[/i]');
                source = source.replace(/<em>/gi,'[i]');
                source = source.replace(/<\/u>/gi,'[/u]');
                source = source.replace(/<u>/gi,'[u]');
                source = source.replace(/<\/strong>/gi,'[/b]');
                source = source.replace(/<img/gi,'[img');
                source = source.replace(/<\/strong>/gi,'[/b]');
				source = source.replace(/<a href=\"(.*?)\"(.*?)>(.*?)<\/a>/gi,'[link=$1 $2]$3[/link]');
*/
$text .= "
            }

		// Convert e107 paths.
                source = source.replace(/\"".str_replace("/","\/",$IMAGES_DIRECTORY)."/g,'\"{e_IMAGE}');
				source = source.replace(/\"".str_replace("/","\/",$PLUGINS_DIRECTORY)."/g,'\"{e_PLUGIN}');
				source = source.replace(/\'".str_replace("/","\/",$IMAGES_DIRECTORY)."/g,'\'{e_IMAGE}');
				source = source.replace(/\'".str_replace("/","\/",$PLUGINS_DIRECTORY)."/g,'\'{e_PLUGIN}');

            break;

        case 'insert_to_editor':
		// Convert e107-BBcode to HTML
            source = source.replace(/rel=\"external\"/, 'target=\"_blank\"');

            html_bbcode_check = source.slice(0,6);

            if (html_bbcode_check == '[html]') {
                source = source.slice(6);
            }

            html_bbcode_check = source.slice(-7);

            if (html_bbcode_check == '[/html]') {
                source = source.slice(0, -7);
            }";
/*
			source = source.replace(/\[b\]/gi,'<strong>');
			source = source.replace(/\[\/b\]/gi,'<\/strong>');
*/
$text .= "
			source = source.replace(/\{e_IMAGE\}/gi,'".$IMAGES_DIRECTORY."');
			source = source.replace(/\{e_PLUGIN\}/gi,'".$PLUGINS_DIRECTORY."');

            break;
    }

    return source;
}


</script>\n
";

return $text;

}


?>
