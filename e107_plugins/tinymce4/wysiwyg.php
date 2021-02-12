<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Tiny MCE controller file.
|
|     $URL$
|     $Id$
+----------------------------------------------------------------------------+
*/
$_E107['no_online'] = true;
$_E107['no_forceuserupdate'] = true;
$_E107['no_menus'] = true;
$_E107['no_maintenance'] = true;
require_once(__DIR__.'/../../class2.php'); //TODO Prevent Theme loading.

/*
echo '


tinymce.init({
	"selector": ".e-wysiwyg",
	"theme": "modern",
	"plugins": "advlist autolink lists link image charmap print preview hr anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen        insertdatetime media nonbreaking save table contextmenu directionality emoticons template paste textcolor",
	"language": "en",
	"menubar": "edit view format insert table tools",
	"toolbar1": "undo redo | styleselect | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | e107-image e107-video e107-glyph | preview",
	"external_plugins": {"e107":"/e107_plugins/tinymce4/plugins/e107/plugin.js","example":"/e107_plugins/tinymce4/plugins/example/plugin.js"},
	"image_advtab": true,
	"extended_valid_elements": "i[*], object[*],embed[*],bbcode[*]",
	"convert_fonts_to_spans": false,
	"content_css": "http://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css",
	"relative_urls": false,
	"preformatted": true,
//	"document_base_url": "http://eternal.technology/"
});
';
exit;
*/



/*
echo 'tinymce.init({
	 selector: ".e-wysiwyg",
    theme: "modern",
    plugins: "template",
    toolbar: "template",
 //   template_cdate_classes: "cdate creationdate",
 //   template_mdate_classes: "mdate modifieddate",
 //   template_selected_content_classes: "selcontent",
 //   template_cdate_format: "%m/%d/%Y : %H:%M:%S",
 //   template_mdate_format: "%m/%d/%Y : %H:%M:%S",
 //   template_replace_values: {
//        username : "Jack Black",
//        staffid : "991234"
 //   },
    templates : [
        {
            title: "Editor Details",
            url: "editor_details.htm",
            description: "Adds Editor Name and Staff ID"
        },
        {
            title: "Timestamp",
            content: "Some Content goes here. ",
            description: "Adds an editing timestamp."
        }
    ]
});';
*/

// exit;



/*
$text = <<<TMPL


tinymce.init({
    selector: ".e-wysiwyg",
    theme: "modern",
    plugins: [
        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor "
    ],
    external_plugins: {
        "example": "{e_PLUGIN_ABS}tinymce4/plugins/example/plugin.min.js",
        "e107" : "{e_PLUGIN_ABS}tinymce4/plugins/e107/plugin.js"
    },
    menubar: "edit view format insert table tools",
    
    toolbar1: "undo redo | styleselect | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | e107-image e107-video e107-glyph | preview",

    image_advtab: true,
    extended_valid_elements: 'span[*],i[*],iframe[*]',
    trim_span_elements: false,
    content_css: 'http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css',
    templates: [
        {title: 'Test template 1', content: 'Test 1'},
        {title: 'Test template 2', content: 'Test 2'}
    ]
});


TMPL;

$output = str_replace("{e_PLUGIN_ABS}", e_PLUGIN_ABS, $text);
*/

require_once(__DIR__."/wysiwyg_class.php");
$wy = new wysiwyg();


$config = varset($_GET['config'],false); // e_QUERY;

$gen = $wy->renderConfig($config);

define('USE_GZIP', true);
$compression_browser_support = false;
$compression_server_support = false;

if(strpos(varset($_SERVER['HTTP_ACCEPT_ENCODING']), 'gzip') !== false)
{
	$compression_browser_support = true;
}

if(ini_get("zlib.output_compression")=='' && function_exists("gzencode"))
{
	$compression_server_support = true;
}


if(ADMIN && e_QUERY == 'debug' || !empty($_GET['debug']))
{
	define('e_IFRAME', true); 
	require_once(HEADERF);

	echo "<table class='table'><tr>";
	echo "
	<td>
	".print_a($gen,true)."
	</td>
	</tr></table>";

//	echo "<br />Browser gZip support: ".$compression_browser_support;
//	echo "<br />Server gZip support: ". $compression_server_support;
	
	require_once(FOOTERF);

}
elseif((USE_GZIP === true) && $compression_browser_support && $compression_server_support)
{
	while (ob_get_length() !== false)  // clear out anything that may have been echoed from class2.php or theme
	{
        ob_end_clean();
	}
	header('Content-type: text/javascript;charset=UTF-8');
	header('Content-Encoding: gzip');

	$minified = e107::minify($gen);
	$gzipoutput = gzencode($minified,6);

	header('Content-Length: '.strlen($gzipoutput));
	echo $gzipoutput;
}
else
{
	while (ob_get_length() !== false)  // clear out anything that may have been echoed from class2.php or theme
	{
        ob_end_clean();
	}
	ob_start();
	ob_implicit_flush(0);
	header('Content-type: text/javascript', TRUE);
	echo $gen;
}
	
exit;






