<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Tiny MCE controller file.
|
|     $URL$
|     $Id$
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
ob_start();
ob_implicit_flush(0);
header("last-modified: " . gmdate("D, d M Y H:i:s",mktime(0,0,0,15,2,2004)) . " GMT");
header('Content-type: text/javascript', TRUE);


$wy = new wysiwyg();

echo_gzipped_page(); 

class wysiwyg
{
	var $js;
	var $config = array();
	var $configName;


	function __construct($config=FALSE)
	{

		$this->getConfig($config);


		$pref = e107::getConfig();







	/*
	if(strstr(varset($_SERVER["HTTP_ACCEPT_ENCODING"],""), "gzip") && (ini_get("zlib.output_compression") == false) && file_exists(e_PLUGIN."tinymce/tiny_mce_gzip.php"))
	{
		//unset($tinymce_plugins[7]); // 'zoom' causes an error with the gzip version.
		$text = "<script type='text/javascript' src='".e_PLUGIN_ABS."tinymce/tiny_mce_gzip.js'></script>

		<script type='text/javascript'>
		tinyMCE_GZ.init({
			plugins : '".implode(",",$tinymce_plugins)."',
			themes : 'advanced',
			languages : '".$tinylang[$lang]."',
			disk_cache : false,
			debug : false
		});
		</script>
		";
	}
	else
	{*/
	//	$text = "<script type='text/javascript' src='".e_PLUGIN_ABS."tinymce/tiny_mce.js'></script>\n";
	//}



//	$text .= "<script type='text/javascript'>\n";
	$text .= "\n /* TinyMce Config: ".$this->configName." */";
	$text .= $this->tinyMce_config();

	$text .= "\t\t start_tinyMce(); \n";
	
	$text .= "

	function tinymce_e107Paths(type, source) {
	";

	$tp = e107::getParser();

	$paths = array(
		e107::getFolder('images'),
		e107::getFolder('plugins'),
		e107::getFolder('media_images'),
		e107::getFolder('media_files'),
		e107::getFolder('media_videos')
	);


	 $text .= "
	    switch (type) {

	        case 'get_from_editor':
	            // Convert HTML to e107-BBcode
	            source = source.replace(/target=\"_blank\"/, 'rel=\"external\"');
	        //    source = source.replace(/^\s*|\s*$/g,'');

			";

			// Convert TinyMce Paths to  e107 paths.
			foreach($paths as $k=>$path)
			{
				//echo "<br />$path = ".$tp->createConstants($path);
				$text .=  "\t\tsource = source.replace(/(\"|])".str_replace("/","\/",$path)."/g,'$1".$tp->createConstants($path)."');\n";
			}

			$text .= "
            break;

	        case 'insert_to_editor': // Convert e107Paths for TinyMce

	            source = source.replace(/rel=\"external\"/, 'target=\"_blank\"');
	            
	      

			";

			// Convert e107 paths to TinyMce Paths.
			foreach($paths as $k=>$path)
			{
				$const = str_replace("}","\}",$tp->createConstants($path));
				$text .= "\t\tsource = source.replace(/".$const."/gi,'".$path."');\n";
			}

			$text .= "
	        break;
	    }

	    return source;
	}

	 // ]]>
	function triggerSave()
	{
	  tinyMCE.triggerSave();
	}


	";
	
	//$text .= "</script>\n";

		$this->js = $text;
		$this->render();

	}

	function tinymce_lang()
	{
		$lang = e_LANGUAGE;
		$tinylang = array(
			"Arabic" 	=> "ar",
			"Danish" 	=> "da",
			"Dutch" 	=> "nl",
			"English" 	=> "en",
			"Farsi" 	=> "fa",
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


	function tinyMce_config()
	{
		$text = "

	function start_tinyMce()
	{
	    //<![CDATA[

		tinyMCE.init({ \n\n";

		$newConfig = array();

		foreach($this->config as $key=>$val)
		{
			if($val != 'true' && $val !='false')
			{
				$val = "'".$val."'";
			}
			$newConfig[] = "\t\t  ".$key." : ".$val;
		}

		// foreach($this->config as $key=>$val)
		// {
			// if($val != 'true' && $val !='false')
			// {
				// $val = "'".$val."'";
			// }
			// $text .= "\t\t  ".$key." : '".$val."',\n";
		// }

		$text .= implode(",\n",$newConfig);

	/*
		if($tinyMcePrefs['customjs'])
		{
			$text .= "\n,

			// Start Custom TinyMce JS  -----

			".$pref['tinymce']['customjs']."

			// End Custom TinyMce JS ---

			";

		}
	*/
		$text .= "
		});

	}

";

		 return $text;
	}



	function getConfig($config=FALSE)
	{		
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
		
		$config = e107::getXml()->loadXMLfile(e_PLUGIN."tinymce/templates/".$template,true); //TODO System and theme folder checks for overrides. 


		//TODO Cache!

		$plug_array = explode(",",$config['tinymce_plugins']);
		$this->configName = $config['tinymce_name'];

		$this->config = array(
			'language'			=> $this->tinymce_lang(),
			'mode'				=> 'textareas',
			'editor_selector' 	=> 'e-wysiwyg',
			'editor_deselector'	=> 'e-wysiwyg-off',
			'theme'				=> 'advanced',
			'plugins'			=> $this->filter_plugins($config['tinymce_plugins'])
		);

		$this->config += array(

			'theme_advanced_buttons1'			=> $config['tinymce_buttons1'],
			'theme_advanced_buttons2'			=> vartrue($config['tinymce_buttons2']),
			'theme_advanced_buttons3'			=> vartrue($config['tinymce_buttons3']),
			'theme_advanced_buttons4'			=> vartrue($config['tinymce_buttons4']),
			'theme_advanced_toolbar_location'	=> vartrue($config['theme_advanced_toolbar_location'],'top'),
			'theme_advanced_toolbar_align'		=> 'left',
			'theme_advanced_blockformats' 		=> 'p,h2,h3,blockquote,code',
			// 'theme_advanced_resize_vertical' 		=> 'true',
			'dialog_type' 						=> "modal",		
		//	'theme_advanced_source_editor_height' => '400',
			
	//		'theme_advanced_statusbar_location'	=> 'bottom',
			'theme_advanced_resizing'			=> 'false',
			'remove_linebreaks'					=> 'true',
			'extended_valid_elements'			=> vartrue($config['extended_valid_elements']), 
			'apply_source_formatting'			=> 'false',
			'invalid_elements'					=> 'p,font,align,script,applet',
			'auto_cleanup_word'					=> 'true',
		//	'cleanup'							=> 'false',
			'convert_fonts_to_spans'			=> 'true',
			'trim_span_elements'				=> 'true',
			'inline_styles'						=> 'true',
			'auto_resize'						=> 'false',
			'debug'								=> 'false',
			'force_br_newlines'					=> 'true',
			'media_strict'						=> 'false',
			'width'								=> '85%',
		//	'height'							=> '90%', // higher causes padding at the top?
		//	'forced_root_block'					=> '',
			'convert_newlines_to_brs'			=> 'true', // will break [list] if set to true
		//	'force_p_newlines'					=> 'false',
			'entity_encoding'					=> 'raw',
			'convert_fonts_to_styles'			=> 'true',
			'remove_script_host'				=> 'true',
			'relative_urls'						=> 'true',
			'preformatted'						=> 'true',
			'document_base_url'					=> SITEURL,
			'theme_advanced_styles'				=> 'border=border;fborder=fborder;tbox=tbox;caption=caption;fcaption=fcaption;forumheader=forumheader;forumheader3=forumheader3',
			'verify_css_classes'				=> 'false'

		);

	//	if(!in_array('e107bbcode',$plug_array))
		{
			$this->config['cleanup_callback'] = 'tinymce_e107Paths';										
		}

		$paste_plugin = (strpos($config['tinymce_plugins'],'paste')!==FALSE) ? TRUE : FALSE;

		if($paste_plugin)
		{
			$this->config += array(

				'remove_linebreaks'						=> 'false', // remove line break stripping by tinyMCE so that we can read the HTML
 				'paste_create_paragraphs'				=> 'false',	// for paste plugin - double linefeeds are converted to paragraph elements
 				'paste_create_linebreaks'				=> 'false',	// for paste plugin - single linefeeds are converted to hard line break elements
 				'paste_use_dialog'						=> 'true',	// for paste plugin - Mozilla and MSIE will present a paste dialog if true
 				'paste_auto_cleanup_on_paste'			=> 'true',	// for paste plugin - word paste will be executed when the user copy/paste content
 				'paste_convert_middot_lists'			=> 'false',	// for paste plugin - middot lists are converted into UL lists
 				'paste_unindented_list_class'			=> 'unindentedList', // for paste plugin - specify what class to assign to the UL list of middot cl's
 				'paste_convert_headers_to_strong'		=> 'true',	// for paste plugin - converts H1-6 elements to strong elements on paste
 				'paste_insert_word_content_callback'	=> 'convertWord', // for paste plugin - This callback is executed when the user pastes word content
				'auto_cleanup_word'						=> 'false'	// auto clean pastes from Word
			);
		}


		if(ADMIN)
		{
			$this->config['external_link_list_url'] = e_PLUGIN_ABS."tiny_mce/filelist.php";
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

		return implode(",",$tinymce_plugins);
	}


	function render()
	{
		echo $this->js;
	}
}

?>