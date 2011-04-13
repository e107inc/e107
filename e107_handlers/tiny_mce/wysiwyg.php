<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Tiny MCE controller file.
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
class wysiwyg
{
	var $js;
	var $config = array();
	
	
	function wysiwyg($formids)
	{
		global $pref,$PLUGINS_DIRECTORY,$IMAGES_DIRECTORY,$lng;
		
		$tinylang = $lng->convert(e_LANGUAGE);

		if(!$tinylang)
		{
			$tinylang = "en";
		}

		$mce_plugins = array();
		$mce_plugins[0]	= "table";
		$mce_plugins[1]	= "contextmenu";
		$mce_plugins[2]	= ($pref['smiley_activate']) ? "emoticons" : "";		// 'emotions' for the tinyMCE plugin, 'emoticons' for ours
		$mce_plugins[3]	= "iespell";
		$mce_plugins[4]	= "media";
		$mce_plugins[5]	= (ADMIN) ? "ibrowser" : "";				// Third party plugins - 'image' may not be a valid plugin name
		//$mce_plugins[6]	= "compat2x";					// May well not be needed - mostly for if we have our own code
		$mce_plugins[7]	= "paste";

		if(strstr(varset($_SERVER["HTTP_ACCEPT_ENCODING"],""), "gzip") && (ini_get("zlib.output_compression") == false) && file_exists(e_HANDLER."tiny_mce/tiny_mce_gzip.php"))
		{
			$text = "<script type='text/javascript' src='".e_HANDLER_ABS."tiny_mce/tiny_mce_gzip.js'></script>
		
			<script type='text/javascript'>
			tinyMCE_GZ.init({
				plugins : '".implode(",",$mce_plugins)."',
				themes : 'advanced',
				languages : '".$tinylang."',
				disk_cache : false,
				debug : false
			});
			</script>
			";
		}
		else
		{
			$text = "<script type='text/javascript' src='".e_HANDLER."tiny_mce/tiny_mce.js'></script>\n";	
		}


		$text .= "
			<script type='text/javascript'>
			tinyMCE.init({
				language : '".$tinylang."',
				mode : 'exact',
				elements : '".$formids."',
				theme : 'advanced',
				plugins : '".implode(",",$mce_plugins)."'\n";
		


		$text .= ",theme_advanced_buttons1 : 'fontsizeselect,separator,bold,italic,underline,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,outdent, indent,separator, forecolor,cut,copy,paste,pastetext,pasteword'";
		
		$text .= ",theme_advanced_buttons2   : 'tablecontrols,separator,undo,redo,separator,link,unlink";
		$text .= ($pref['smiley_activate']) ? ",emoticons" : "";
		$text .= ",charmap,iespell,media";
		$text .= (ADMIN) ? ",ibrowser" : ",image";
		$text .= (ADMIN) ? ",code" : "";
		$text .= "',"; // end of buttons 2
		
		$text .= $this->tinyMce_config();
		
	
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
	
		$this->js = $text;	
	}

	function tinyMce_config()
	{
		$text = "";
		
		$this->getConfig();
		
		foreach($this->config as $key=>$val)
		{
			if($val != 'true' && $val !='false')
			{
				$val = "'".$val."'";
			}
			$text .= "\t\t  ".$key." : ".$val.",\n";
		}
		
		return $text;
		
	}
	
	
	function getConfig()
	{
		$this->config = array(
			'theme_advanced_buttons3' 			=> '',
			'theme_advanced_toolbar_location' 	=> 'top',
			'extended_valid_elements' 			=> '',
			'invalid_elements' 					=> 'p,font,align,script,applet,iframe',
		//	'auto_cleanup_word'					=> 'true',
			'convert_fonts_to_spans'			=> 'true',
			'trim_span_elements'				=> 'true',
			'inline_styles'						=> 'true',
			'debug'								=> 'false',
			'force_br_newlines'					=> 'false',
			'forced_root_block'					=> 'div', // div required for styling. 
			'force_p_newlines'					=> 'false',
			'entity_encoding'					=> 'raw',
			'convert_fonts_to_styles'			=> 'true',
			'remove_script_host'				=> 'true',
			'relative_urls'						=> 'true',
			'document_base_url'					=> SITEURL,
			'theme_advanced_styles'				=> 'border=border;fborder=fborder;tbox=tbox;caption=caption;fcaption=fcaption;forumheader=forumheader;forumheader3=forumheader3',
			//'popup_css'						=> '".THEME_ABS."style.css',
			'verify_css_classes'				=> 'false',
			'cleanup_callback'					 => 'tinymce_html_bbcode_control',
			'verify_css_classes'				=> 'false'
		);
		
		// Paste Plugin
		
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
		
		if(ADMIN)
		{
			$this->config['external_link_list_url'] = e_HANDLER_ABS."tiny_mce/filelist.php";	
		}
	}
	
	function render()
	{
		echo $this->js;
	}

}
?>