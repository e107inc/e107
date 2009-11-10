<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Tiny MCE controller file.
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/tinymce/wysiwyg.php,v $
|     $Revision: 1.19 $
|     $Date: 2009-11-10 01:21:05 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/


class wysiwyg
{
	var $js;
	var $config = array();
	var $configName;
	
	
	function wysiwyg($config=FALSE)
	{
	
		$this->getConfig($config);
		
		
		global $pref,$HANDLERS_DIRECTORY,$PLUGINS_DIRECTORY,$IMAGES_DIRECTORY;
	

	

	
	
	
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
		$text = "<script type='text/javascript' src='".e_PLUGIN_ABS."tinymce/tiny_mce.js'></script>\n";	
	//}
	
	
	
	$text .= "<script type='text/javascript'>\n";
	$text .= "\n /* TinyMce Config: ".$this->configName." */";
	$text .= $this->tinyMce_config();
	
	$text .= "\t\t start_tinyMce(); \n
	
	function tinymce_html_bbcode_control(type, source) {
	

	    switch (type) {
	
	        case 'get_from_editor':
	            // Convert HTML to e107-BBcode
	            source = source.replace(/target=\"_blank\"/, 'rel=\"external\"');
	            source = source.replace(/^\s*|\s*$/g,'');
	            if(source != '')
	            {
	                source = '[html]\\n' + source + '\\n[/html]';
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
	            }
	/*
				source = source.replace(/\[b\]/gi,'<strong>');
				source = source.replace(/\[\/b\]/gi,'<\/strong>');
	*/
				source = source.replace(/\{e_IMAGE\}/gi,'".$IMAGES_DIRECTORY."');
				source = source.replace(/\{e_PLUGIN\}/gi,'".$PLUGINS_DIRECTORY."');
	
	            break;
	    }
	
	    return source;
	}
	
	 // ]]>
	function triggerSave()
	{
	  tinyMCE.triggerSave();
	}
	
	
	
	
	</script>\n
	";
	
		$this->js = $text;
	
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
		
		foreach($this->config as $key=>$val)
		{
			$text .= "\t\t  ".$key." : '".$val."',\n";
		}
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
		$sql = e107::getDb();
				
		if($config)
		{
			$query = "SELECT * FROM #tinymce WHERE tinymce_id = ".$config." LIMIT 1";				
		}
		else
		{
			$query = "SELECT * FROM #tinymce WHERE tinymce_userclass REGEXP '".e_CLASS_REGEXP."' AND NOT (tinymce_userclass REGEXP '(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)') ORDER BY Field(tinymce_userclass,250,254,253) LIMIT 1";			
		}
		
		$sql -> db_Select_gen($query);
		$config = $sql->db_Fetch();
		
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
			'theme_advanced_buttons2'			=> $config['tinymce_buttons2'],
			'theme_advanced_buttons3'			=> $config['tinymce_buttons3'],
			'theme_advanced_buttons4'			=> $config['tinymce_buttons4'],
			'theme_advanced_toolbar_location'	=> 'bottom',
			'theme_advanced_toolbar_align'		=> 'center',
	//		'theme_advanced_statusbar_location'	=> 'bottom',
			'theme_advanced_resizing'			=> 'true',
			'extended_valid_elements'			=> '',
			'invalid_elements'					=> 'p,font,align,script,applet,iframe',
			'auto_cleanup_word'					=> 'true',
			'convert_fonts_to_spans'			=> 'true',
			'trim_span_elements'				=> 'true',
			'inline_styles'						=> 'true',
			'auto_resize'						=> 'true',
			'debug'								=> 'false',
			'force_br_newlines'					=> 'true',
			'forced_root_block'					=> '',
			'force_p_newlines'					=> 'false',
			'entity_encoding'					=> 'raw',
			'convert_fonts_to_styles'			=> 'true',
			'remove_script_host'				=> 'true',
			'relative_urls'						=> 'true',
			'document_base_url'					=> SITEURL,
			'theme_advanced_styles'				=> 'border=border;fborder=fborder;tbox=tbox;caption=caption;fcaption=fcaption;forumheader=forumheader;forumheader3=forumheader3',
			'verify_css_classes'				=> 'false'

		);
		
		if(!in_array('e107bbcode',$plug_array))
		{
			$this->config['cleanup_callback'] = 'tinymce_html_bbcode_control';		
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
		
		$admin_only = array("ibrowser","code");
	
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