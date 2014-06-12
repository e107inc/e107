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
require_once("../../class2.php");

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

$wy = new wysiwyg();

$gen = $wy->renderConfig();


if(ADMIN && e_QUERY == 'debug')
{
	require_once(HEADERF);

	echo "<table class='table'><tr><td>";

	print_a($output);

	echo "</td>
	<td>
	".print_a($gen,true)."
	</td>
	</tr></table>";
	
	require_once(FOOTERF);

}
else
{
	ob_start();
	ob_implicit_flush(0);
	//header("last-modified: " . gmdate("D, d M Y H:i:s",mktime(0,0,0,15,2,2004)) . " GMT");
	header('Content-type: text/javascript', TRUE);
	echo $gen;
}
		
	
exit;





echo_gzipped_page(); 

class wysiwyg
{
	var $js;
	var $config = array();
	var $configName;

	function renderConfig()
	{
		$this->getConfig($config);	
		$text .= "\n /* TinyMce Config: ".$this->configName." */\n\n";
		$text .= "tinymce.init(";
		$text .= json_encode($this->config, JSON_PRETTY_PRINT);
		$text .= ");";
		
		return stripslashes($text);
	}
	


	function __construct($config=FALSE)
	{

	}

	function tinymce_lang()
	{
		$lang = e_LANGUAGE;
		$tinylang = array(
			"Arabic" 	=> "ar",
			"Bulgarian"	=> "bg",
			"Danish" 	=> "da",
			"Dutch" 	=> "nl",
			"English" 	=> "en",
			"Persian" 	=> "fa",
			"French" 	=> "fr",
			"German"	=> "de",
			"Greek" 	=> "el",
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

		if(!$tinylang[$lang])
		{
		 	$tinylang[$lang] = "en";
		}

		return $tinylang[$lang];
	}



	function getExternalPlugins($data)
	{
		if(empty($data))
		{
			return;
		}
				
		$tmp = explode(" ",$data);

		$ext = array();

		foreach($tmp as $val)
		{
			$ext[$val] = e_PLUGIN_ABS."tinymce4/plugins/".$val."/plugin.js";
		}
			
			
		return $ext;
	}
		
		
				
	function convertBoolean($string)
	{
		$string = str_replace("\n","",$string);
		
		if($string === 'true')
		{
			return true;
		}
		
		if($string === 'false')
		{
			return false;	
		}
				
		return $string;
	}			
		


	function getConfig($config=FALSE)
	{
		$tp = e107::getParser();	
		$fl = e107::getFile();
				
		if(getperms('0'))
		{
			$template = "mainadmin.xml";		
		}
		elseif(ADMIN)
		{
			$template = "admin.xml";			
		}
		elseif(USER)
		{
			$template = "member.xml";			
		}
		else
		{
			$template = "public.xml";			
		}
		
		$configPath = (is_readable(THEME."templates/tinymce/".$template)) ? THEME."templates/tinymce/".$template : e_PLUGIN."tinymce4/templates/".$template;
		$config 	= e107::getXml()->loadXMLfile($configPath, true); 

		//TODO Cache!

		$this->configName = $config['@attributes']['name'];

		unset($config['@attributes']);

		$this->config = array(
			'selector' 	=> '.e-wysiwyg',
			'theme'				=> 'modern',
			'plugins'			=> $this->filter_plugins($config['tinymce_plugins']),
			'language'			=> $this->tinymce_lang()
			
		);

		
		// Loop thru XML parms. 
		foreach($config as $k=>$xml)
		{
			if($k == 'external_plugins')
			{
				$this->config['external_plugins'] = $this->getExternalPlugins($xml); 
				continue;		
			}
			
			$this->config[$k] = $this->convertBoolean($xml);
		}

		$this->config['convert_fonts_to_spans']	= false;
		$this->config['content_css']			= 'http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css';
		
		$this->config['relative_urls']			= false;  //Media Manager prefers it like this. 
		$this->config['preformatted']			= true;
		$this->config['document_base_url']		= SITEURL;
		
		//	$this->config['verify_css_classes']	= 'false';
		
		
		return; 

		// -------------------------------------------------------------------------------------
		
		
	
		$cssFiles = $fl->get_files(THEME,"\.css",'',2);
		
		
		foreach($cssFiles as $val)
		{
			$css[] = str_replace(THEME,THEME_ABS,$val['path'].$val['fname']);	
		}
		$css[] = "{e_WEB_ABS}js/bootstrap/css/bootstrap.min.css";
		$content_css = vartrue($config['content_css'], implode(",",$css)); 
		
		$content_styles = array('Bootstrap Button' => 'btn btn-primary', 'Bootstrap Table' => 'table');



		$this->config += array(

	//		'theme_advanced_buttons1'			=> $config['tinymce_buttons1'],
	//		'theme_advanced_buttons2'			=> vartrue($config['tinymce_buttons2']),
	//		'theme_advanced_buttons3'			=> vartrue($config['tinymce_buttons3']),
	//		'theme_advanced_buttons4'			=> vartrue($config['tinymce_buttons4']),
	//		'theme_advanced_toolbar_location'	=> vartrue($config['theme_advanced_toolbar_location'],'top'),
	//		'theme_advanced_toolbar_align'		=> 'left',
	//		'theme_advanced_blockformats' 		=> 'p,h2,h3,h4,h5,h6,blockquote,pre,code',
	//		'theme_advanced_styles'				=> str_replace(array("+")," ",http_build_query($content_styles)),  //'Bootstrap Button=btn btn-primary;Bootstrap Table=table;border=border;fborder=fborder;tbox=tbox;caption=caption;fcaption=fcaption;forumheader=forumheader;forumheader3=forumheader3',
		
			// 'theme_advanced_resize_vertical' 		=> 'true',
			'dialog_type' 						=> "modal",		
		//	'theme_advanced_source_editor_height' => '400',
            
            // ------------- html5 Stuff. 
		
		    //  'visualblocks_default_state'   => 'true',

                // Schema is HTML5 instead of default HTML4
           //     'schema'     => "html5",
        
                // End container block element when pressing enter inside an empty block
           //     'end_container_on_empty_block' => true,
        
                // HTML5 formats
                /*
                'style_formats' => "[
                        {title : 'h1', block : 'h1'},
                        {title : 'h2', block : 'h2'},
                        {title : 'h3', block : 'h3'},
                        {title : 'h4', block : 'h4'},
                        {title : 'h5', block : 'h5'},
                        {title : 'h6', block : 'h6'},
                        {title : 'p', block : 'p'},
                        {title : 'div', block : 'div'},
                        {title : 'pre', block : 'pre'},
                        {title : 'section', block : 'section', wrapper: true, merge_siblings: false},
                        {title : 'article', block : 'article', wrapper: true, merge_siblings: false},
                        {title : 'blockquote', block : 'blockquote', wrapper: true},
                        {title : 'hgroup', block : 'hgroup', wrapper: true},
                        {title : 'aside', block : 'aside', wrapper: true},
                        {title : 'figure', block : 'figure', wrapper: true}
                ]",
        		*/
	       // --------------------------------
		
			
	//		'theme_advanced_statusbar_location'	=> 'bottom',
			'theme_advanced_resizing'			=> 'true',
			'remove_linebreaks'					=> 'false',
			'extended_valid_elements'			=> vartrue($config['extended_valid_elements']), 
	//		'pagebreak_separator'				=> "[newpage]", 
			'apply_source_formatting'			=> 'true',
			'invalid_elements'					=> 'font,align,script,applet',
			'auto_cleanup_word'					=> 'true',
			'cleanup'							=> 'true',
			'convert_fonts_to_spans'			=> 'true',
	//		'content_css'						=> $tp->replaceConstants($content_css),
			'popup_css'							=> 'false', 
			
			'trim_span_elements'				=> 'true',
			'inline_styles'						=> 'true',
			'auto_resize'						=> 'false',
			'debug'								=> 'true',
			'force_br_newlines'					=> 'true',
			'media_strict'						=> 'false',
			'width'								=> vartrue($config['width'],'100%'),
		//	'height'							=> '90%', // higher causes padding at the top?
			'forced_root_block'					=> 'false', //remain as false or it will mess up some theme layouts. 
		
			'convert_newlines_to_brs'			=> 'true', // will break [list] if set to true
		//	'force_p_newlines'					=> 'false',
			'entity_encoding'					=> 'raw',
			'convert_fonts_to_styles'			=> 'true',
			'remove_script_host'				=> 'true',
			'relative_urls'						=> 'false', //Media Manager prefers it like this. 
			'preformatted'						=> 'true',
			'document_base_url'					=> SITEURL,
			'verify_css_classes'				=> 'false'

		);

	//	if(!in_array('e107bbcode',$plug_array))
		{
	//		$this->config['cleanup_callback'] = 'tinymce_e107Paths';										
		}

		$paste_plugin = false; // (strpos($config['tinymce_plugins'],'paste')!==FALSE) ? TRUE : FALSE;

		if($paste_plugin)
		{
			$this->config += array(

				'paste_text_sticky'						=> 'true',
				'paste_text_sticky_default'				=> 'true',
				'paste_text_linebreaktype'				=> 'br',
		
				'remove_linebreaks'						=> 'false', // remove line break stripping by tinyMCE so that we can read the HTML
 				'paste_create_paragraphs'				=> 'false',	// for paste plugin - double linefeeds are converted to paragraph elements
 				'paste_create_linebreaks'				=> 'true',	// for paste plugin - single linefeeds are converted to hard line break elements
 				'paste_use_dialog'						=> 'true',	// for paste plugin - Mozilla and MSIE will present a paste dialog if true
 				'paste_auto_cleanup_on_paste'			=> 'true',	// for paste plugin - word paste will be executed when the user copy/paste content
 				'paste_convert_middot_lists'			=> 'false',	// for paste plugin - middot lists are converted into UL lists
 				'paste_unindented_list_class'			=> 'unindentedList', // for paste plugin - specify what class to assign to the UL list of middot cl's
 				'paste_convert_headers_to_strong'		=> 'true',	// for paste plugin - converts H1-6 elements to strong elements on paste
 				'paste_insert_word_content_callback'	=> 'convertWord', // for paste plugin - This callback is executed when the user pastes word content
				'auto_cleanup_word'						=> 'true'	// auto clean pastes from Word
			);
		}

		if(ADMIN)
		{
	//		$this->config['external_link_list_url'] = e_PLUGIN_ABS."tiny_mce/filelist.php";
		}
	}


	function filter_plugins($plugs)
	{

		$smile_pref = e107::getConfig()->getPref('smiley_activate');

		$admin_only = array("ibrowser");

		$plug_array = explode(",",$plugs);

		foreach($plug_array as $val)
		{
			if(in_array($val,$admin_only) && !ADMIN)
			{
		    	continue;
			}

			if(!$smile_pref && ($val=="emoticons"))
			{
		    	continue;
			}

			$tinymce_plugins[] = $val;
		}

		return $tinymce_plugins;
	}



}

?>