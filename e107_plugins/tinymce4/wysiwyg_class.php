<?php


class wysiwyg
{
	private $js;
	private $config;
	private $configName;
	private $bootstrapVersion;

	function renderConfig($config='')
	{
		$this->getConfig($config);
		$text = "\n /* TinyMce Config: ".$this->configName." */\n\n";
		$text .= "tinymce.init({\n";
/*
		$text .= "save_onsavecallback: function() {console.log('Save'); },\n";

		$text .= "setup: function (editor) {
			editor.on('blur', function () {
				editor.save();
			});
		},\n";
*/

		$text .= $this->config; // Moc: temporary fix for BC with PHP 5.3: https://github.com/e107inc/e107/issues/614

		$text .= "\n});";



		return stripslashes($text);
	}



	function __construct($config=FALSE)
	{
		$this->bootstrapVersion = e107::getTheme()->getLibVersion('bootstrap');
	}

	function tinymce_lang()
	{
		$lang = e_LANGUAGE;

		// Languages supported by TinyMce.
		// Commented out languages are not found in e107's language_class.php.
		$tinylang = array(
			'Arabic'        => 'ar',
			// 'Arabic (Saudi Arabia)' => 'ar_SA',
			'Armenian'      => 'hy',
			'Azerbaijani'   => 'az',
			'Basque'        => 'eu',
			'Belarusian'    => 'be',
			'Bengali'       => 'bn_BD',
			'Bosnian'       => 'bs',
			'Bulgarian'     => 'bg_BG',
			'Catalan'       => 'ca',
			'ChineseSimp'   => 'zh_CN',
			'ChineseTrad'   => 'zh_TW',
			'Croatian'      => 'hr',
			'Czech'         => 'cs',
			// 'Czech (Czech Republic)' => 'cs_CZ',
			'Danish'        => 'da',
			// 'Divehi' => 'dv',
			'Dutch'         => 'nl',
			'English'       => 'en_GB', // Default language file.
			// 'English (Canada)' => 'en_CA',
			// 'English (United Kingdom)' => 'en_GB',
			'Esperanto'     => 'eo',
			'Estonian'      => 'et',
			'Faroese'       => 'fo',
			'Finnish'       => 'fi',
			'French'        => 'fr_FR',
			// 'French (Switzerland)' => 'fr_CH',
			'Gaelic'        => 'gd',
			'Gallegan'      => 'gl',
			'Georgian'      => 'ka_GE',
			'German'        => 'de',
			// 'German (Austria)' => 'de_AT',
			'Greek'         => 'el',
			'Hebrew'        => 'he_IL',
			'Hindi'         => 'hi_IN',
			'Hungarian'     => 'hu_HU',
			'Icelandic'     => 'is_IS',
			'Indonesian'    => 'id',
			'Irish'         => 'ga',
			'Italian'       => 'it',
			'Japanese'      => 'ja',
			// 'Kabyle' => 'kab',
			'Kazakh'        => 'kk',
			'Khmer'         => 'km_KH',
			'Korean'        => 'ko',
			// 'Korean (Korea)' => 'ko_KR',
			'Kurdish'       => 'ku',
			// 'Kurdish (Iraq)' => 'ku_IQ',
			'Latvian'       => 'lv',
			'Lithuanian'    => 'lt',
			'Letzeburgesch' => 'lb',
			'Macedonian'    => 'mk_MK',
			'Malayalam'     => 'ml',
			// 'Malayalam (India)' => 'ml_IN',
			'Mongolian'     => 'mn_MN',
			'Norwegian'     => 'nb_NO',
			'Persian'       => 'fa',
			// 'Persian (Iran)' => 'fa_IR',
			'Polish'        => 'pl',
			// 'Portuguese (Brazil)' => 'pt_BR',
			'Portuguese'    => 'pt_PT',
			'Romanian'      => 'ro',
			'Russian'       => 'ru',
			// 'Russian (Russia)' => 'ru_RU',
			'Serbian'       => 'sr',
			'Sinhala'       => 'si_LK',
			'Slovak'        => 'sk',
			'Slovenian'     => 'sl_SI',
			'Spanish'       => 'es',
			// 'Spanish (Mexico)' => 'es_MX',
			'Swedish'       => 'sv_SE',
			'Tajik'         => 'tg',
			'Tamil'         => 'ta',
			// 'Tamil (India)' => 'ta_IN',
			'Tatar'         => 'tt',
			'Thai'          => 'th_TH',
			'Turkish'       => 'tr',
			// 'Turkish (Turkey)' => 'tr_TR',
			'Uighur'        => 'ug',
			'Ukrainian'     => 'uk',
			// 'Ukrainian (Ukraine)' => 'uk_UA',
			'Vietnamese'    => 'vi',
			// 'Vietnamese (Viet Nam)' => 'vi_VN',
			'Welsh'         => 'cy',
		);

		if(!isset($tinylang[$lang]))
		{
			$tinylang[$lang] = "en_GB";
		}

		// If language file is not present, use default.
		$jsFile = e_PLUGIN . 'tinymce4/langs/' . $tinylang[$lang] . '.js';
		if($tinylang[$lang] != 'en_GB' && !file_exists($jsFile))
		{
			$tinylang[$lang] = "en_GB";
		}

		return $tinylang[$lang];
	}



	function getExternalPlugins($data)
	{
		if(empty($data))
		{
			return null;
		}

		$tmp = explode(" ",$data);

		if(e107::pref('core','smiley_activate',false))
		{
			$tmp[] = "smileys";
		}

		$ext = array();

		foreach($tmp as $val)
		{
			$ext[$val] = e_PLUGIN_ABS."tinymce4/plugins/".$val."/plugin.js";
		}




		return json_encode($ext);
	}



	function convertBoolean($string)
	{

		if(substr($string,0,1) == '{' || substr($string,0,1) == '[' || substr($string,0,9) == 'function(')
		{
			return $string;
		}

		if(is_numeric($string))
		{
			return $string;
		}

		if(is_string($string))
		{
			$string = trim($string);
			$string = str_replace("\n","",$string);
		}

		if($string === true)
		{
			return 'true';
		}

		if($string === false)
		{
			return 'false';
		}

		if($string === 'true' || $string === 'false' || $string[0] == '[')
		{
			return $string;
		}

		return '"'.$string.'"';
	}



	function getConfig($config=false)
	{
		$tp = e107::getParser();
	//	$fl = e107::getFile();

		if($config !== false)
		{
			$template = $tp->filter($config).".xml";
		}
		else
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


		}

		if(($template == 'mainadmin.xml' && !getperms('0')) || ($template == 'admin.xml' && !ADMIN))
		{
			$template = 'public.xml';
		}


		$configPath = (is_readable(THEME."templates/tinymce/".$template)) ? THEME."templates/tinymce/".$template : e_PLUGIN."tinymce4/templates/".$template;
		$config 	= e107::getXml()->loadXMLfile($configPath, true);

		//TODO Cache!

		$this->configName = $config['@attributes']['name'];

		$tinyMceLanguage    = $this->tinymce_lang();

		unset($config['@attributes']);

		$ret = array(
			'selector' 			=> '.e-wysiwyg',
		//	'editor_selector'   => 'advancedEditor',
			'language'			=> $tinyMceLanguage,
			'language_url'      => SITEURLBASE.e_PLUGIN_ABS."tinymce4/langs/" . $tinyMceLanguage . ".js"
		);

	//	if(e_ADMIN_AREA)
		{
	//		$ret['skin_url']     = e_PLUGIN_ABS.'tinymce4/skins/eskin';
		}

		// Loop thru XML parms.
		foreach($config as $k=>$xml)
		{
			if($k == 'plugins')
			{
				$ret[$k] = $this->filter_plugins($xml);
			}
			else
			{
				$ret[$k] = $xml;
			}

		}

		$tPref = e107::pref('tinymce4');

		if(!empty($tPref['paste_as_text']))
		{
			$ret['paste_as_text']	= true;
		}


		if(!empty($tPref['browser_spellcheck']))
		{
			$ret['browser_spellcheck']	= true;
		}

		if(!empty($tPref['visualblocks']))
		{
			$ret['visualblocks_default_state']	= true;
		}

		$ret['autosave_ask_before_unload'] = true;
		$ret['autosave_retention']         = "30m";
		$ret['autosave_interval']          = "3s";
		$ret['autosave_prefix']            = "tinymce-autosave-{path}{query}-{id}-";
		$ret['verify_html']                 = false;


		// plugins: "visualblocks",


/*
		$formats = array(
			'hilitecolor' => array('inline'=> 'span', 'classes'=> 'hilitecolor', 'styles'=> array('backgroundColor'=> '%value'))
			//	block : 'h1', attributes : {title : "Header"}, styles : {color : red}
		);*/

		//@see http://www.tinymce.com/wiki.php/Configuration:formats

		$codeHighlightClass = varset($tPref['code_highlight_class'], 'prettyprint linenums');

		$formats = "[
                {title: 'Headers', items: [
                    {title: 'Heading 1', block: 'h1'},
                    {title: 'Heading 2', block: 'h2'},
                    {title: 'Heading 3', block: 'h3'},
                    {title: 'Heading 4', block: 'h4'},
                    {title: 'Heading 5', block: 'h5'},
                    {title: 'Heading 6', block: 'h6'}
                ]},

                {title: 'Inline', items: [
                    {title: 'Bold', inline: 'b', icon: 'bold'},
                    {title: 'Italic', inline: 'em', icon: 'italic'},
                    {title: 'Underline', inline: 'span', styles : {textDecoration : 'underline'}, icon: 'underline'},
                    {title: 'Strikethrough', inline: 'span', styles : {textDecoration : 'line-through'}, icon: 'strikethrough'},
                    {title: 'Superscript', inline: 'sup', icon: 'superscript'},
                    {title: 'Subscript', inline: 'sub', icon: 'subscript'},
                    {title: 'Code', inline: 'code', icon: 'code'},
                    {title: 'Small', inline: 'small', icon: ''},
                ]},

                {title: 'Blocks', items: [
                    {title: 'Paragraph', block: 'p'},
                    {title: 'Blockquote', block: 'blockquote'},
                    {title: 'Blockquote alt.', block: 'blockquote', classes: 'blockquote-alt blockquote__alternative'},
                    {title: 'Div', block: 'div'},
                    {title: 'Pre', block: 'pre'},
                    {title: 'Code Highlighted', block: 'pre', classes: '".$codeHighlightClass."' }
                ]},

                  {title: 'Lists', items: [
                    {title: 'FontAwesome', selector: 'ul', classes: 'fa-ul' },
                    {title: 'FontAwesome List Icon', selector: 'i', classes: 'fa-li' },
                    {title: 'Bootstrap Listgroup', selector: 'ul', classes: 'list-group' },
                    {title: 'Bootstrap Listgroup Item', selector: 'li', classes: 'list-group-item' },
                ]},

                {title: 'Alignment', items: [
                    {title: 'Left', block: 'div', classes: 'text-left',  icon: 'alignleft'},
                    {title: 'Center', block: 'div',classes: 'text-center', icon: 'aligncenter'},
                    {title: 'Right', block: 'div', classes: 'text-right',  icon: 'alignright'},
                    {title: 'Justify', block: 'div', classes: 'text-justify', icon: 'alignjustify'},
                    {title: 'No Text-Wrap', block: 'div', classes: 'text-nowrap', icon: ''},
                    {title: 'Clear Float', block: 'div', classes: 'clearfix'},
                    {title: 'Image Left', selector: 'img', classes: 'bbcode-img-left',  icon: 'alignleft'},
                    {title: 'Image Right', selector: 'img', classes: 'bbcode-img-right', icon: 'alignright'}

                ]},
                
                {title: 'Glyphs', items: [
                    {title: 'Size 2x', selector: 'i', classes: 'fa-2x'},
                    {title: 'Size 3x', selector: 'i', classes: 'fa-3x'},
                    {title: 'Size 4x', selector: 'i', classes: 'fa-4x'},
                    {title: 'Size 5x', selector: 'i', classes: 'fa-5x'},
                    {title: 'Fixed Width', selector: 'i', classes: 'fa-fw'},
                     {title: 'Spin', selector: 'i', classes: 'fa-spin'}
                ]},

                {title: 'Bootstrap Inline', items: [
				 {title: 'Label (Default)', inline: 'span', classes: 'label label-default'},
				 {title: 'Label (Primary)', inline: 'span', classes: 'label label-primary'},
                 {title: 'Label (Success)', inline: 'span', classes: 'label label-success'},
                 {title: 'Label (Info)', inline: 'span', classes: 'label label-info'},
                 {title: 'Label (Warning)', inline: 'span', classes: 'label label-warning'},
                 {title: 'Label (Danger)', inline: 'span', classes: 'label label-danger'},
                 {title: 'Muted', inline: 'span', classes: 'text-muted'},
                ]},

                 {title: 'Bootstrap Blocks', items: [
                 {title: 'Alert (Success)', block: 'div', classes: 'alert alert-success'},
                 {title: 'Alert (Info)', block: 'div', classes: 'alert alert-info'},
                 {title: 'Alert (Warning)', block: 'div', classes: 'alert alert-warning'},
                 {title: 'Alert (Danger)', block: 'div', classes: 'alert alert-block alert-danger'},
                 {title: 'Lead', block: 'p', classes: 'lead'},
                 {title: 'Well', block: 'div', classes: 'well'},
				 {title: 'Row', block: 'div', classes: 'row'},
                 {title: '1/4 Width Block', block: 'div', classes: 'col-md-3 col-sm-12'},
                 {title: '3/4 Width Block', block: 'div', classes: 'col-md-9 col-sm-12'},
                 {title: '1/3 Width Block', block: 'div', classes: 'col-md-4 col-sm-12'},
                 {title: '2/3 Width Block', block: 'div', classes: 'col-md-8 col-sm-12'},
                 {title: '1/2 Width Block', block: 'div', classes: 'col-md-6 col-sm-12'},
                ]},

                 {title: 'Bootstrap Buttons', items: ".$this->bootstrapButtonStyles()."},

				 {title: 'Bootstrap Images', items: [
				 {title: 'Responsive (recommended)',  selector: 'img', classes: 'img-responsive img-fluid'},
				 {title: 'Rounded',  selector: 'img', classes: 'img-rounded rounded'},
				 {title: 'Circle', selector: 'img', classes: 'img-circle rounded-circle'},
                 {title: 'Thumbnail', selector: 'img', classes: 'img-thumbnail'},
                ]},

				 {title: 'Bootstrap Tables', items: [
				 {title: 'Bordered',  selector: 'table', classes: 'table-bordered'},
				 {title: 'Condensed', selector: 'table', classes: 'table-condensed'},
				 {title: 'Hover', selector: 'table', classes: 'table-hover'},
                 {title: 'Striped', selector: 'table', classes: 'table-striped'},
                ]},
                

                
                {title: 'Animate.css Style', items: [
					{title: 'bounce',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated bounce'},
					{title: 'flash',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated flash'},
					{title: 'pulse',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated pulse'},
					{title: 'rubberBand',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated rubberBand'},
					{title: 'shake',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated shake'},
					{title: 'headShake',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated headShake'},
					{title: 'swing',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated swing'},
					{title: 'tada',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated tada'},
					{title: 'wobble',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated wobble'},
					{title: 'jello',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated jello'},
					{title: 'bounceIn',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated bounceIn'},
					{title: 'bounceInDown',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated bounceInDown'},
					{title: 'bounceInLeft',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated bounceInLeft'},
					{title: 'bounceInRight',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated bounceInRight'},
					{title: 'bounceInUp',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated bounceInUp'},
				/*	{title: 'bounceOut',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated bounceOut'},
					{title: 'bounceOutDown',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated bounceOutDown'},
					{title: 'bounceOutLeft',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated bounceOutLeft'},
					{title: 'bounceOutRight',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated bounceOutRight'},
					{title: 'bounceOutUp',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated bounceOutUp'},*/
					{title: 'fadeIn',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated fadeIn'},
					{title: 'fadeInDown',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated fadeInDown'},
					{title: 'fadeInDownBig',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated fadeInDownBig'},
					{title: 'fadeInLeft',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated fadeInLeft'},
					{title: 'fadeInLeftBig',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated fadeInLeftBig'},
					{title: 'fadeInRight',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated fadeInRight'},
					{title: 'fadeInRightBig',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated fadeInRightBig'},
					{title: 'fadeInUp',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated fadeInUp'},
					{title: 'fadeInUpBig',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated fadeInUpBig'},
					{title: 'fadeOut',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated fadeOut'},
				/*	{title: 'fadeOutDown',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated fadeOutDown'},
					{title: 'fadeOutDownBig',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated fadeOutDownBig'},
					{title: 'fadeOutLeft',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated fadeOutLeft'},
					{title: 'fadeOutLeftBig',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated fadeOutLeftBig'},
					{title: 'fadeOutRight',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated fadeOutRight'},
					{title: 'fadeOutRightBig',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated fadeOutRightBig'},
					{title: 'fadeOutUp',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated fadeOutUp'},
					{title: 'fadeOutUpBig',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated fadeOutUpBig'}, */
					{title: 'flip',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated flip'},
					{title: 'flipInX',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated flipInX'},
					{title: 'flipInY',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated flipInY'},
				/*	{title: 'flipOutX',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated flipOutX'},
					{title: 'flipOutY',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated flipOutY'}, */
					{title: 'lightSpeedIn',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated lightSpeedIn'},
				/*	{title: 'lightSpeedOut',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated lightSpeedOut'}, */
					{title: 'rotateIn',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated rotateIn'},
					{title: 'rotateInDownLeft',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated rotateInDownLeft'},
					{title: 'rotateInDownRight',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated rotateInDownRight'},
					{title: 'rotateInUpLeft',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated rotateInUpLeft'},
					{title: 'rotateInUpRight',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated rotateInUpRight'},
				/*	{title: 'rotateOut',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated rotateOut'},
					{title: 'rotateOutDownLeft',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated rotateOutDownLeft'},
					{title: 'rotateOutDownRight',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated rotateOutDownRight'},
					{title: 'rotateOutUpLeft',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated rotateOutUpLeft'},
					{title: 'rotateOutUpRight',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated rotateOutUpRight'}, */
					{title: 'hinge',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated hinge'},
					{title: 'jackInTheBox',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated jackInTheBox'},
					{title: 'rollIn',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated rollIn'},
					{title: 'rollOut',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated rollOut'},
					{title: 'zoomIn',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated zoomIn'},
					{title: 'zoomInDown',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated zoomInDown'},
					{title: 'zoomInLeft',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated zoomInLeft'},
					{title: 'zoomInRight',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated zoomInRight'},
					{title: 'zoomInUp',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated zoomInUp'},
					{title: 'zoomOut',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated zoomOut'},
					{title: 'zoomOutDown',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated zoomOutDown'},
					{title: 'zoomOutLeft',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated zoomOutLeft'},
					{title: 'zoomOutRight',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated zoomOutRight'},
					{title: 'zoomOutUp',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated zoomOutUp'},
					{title: 'slideInDown',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated slideInDown'},
					{title: 'slideInLeft',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated slideInLeft'},
					{title: 'slideInRight',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated slideInRight'},
					{title: 'slideInUp',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated slideInUp'},
			/*		{title: 'slideOutDown',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated slideOutDown'},
					{title: 'slideOutLeft',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated slideOutLeft'},
					{title: 'slideOutRight',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated slideOutRight'},
					{title: 'slideOutUp',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animated slideOutUp'}, */
		
                ]},


				 {title: 'Animate.css Delay', items: [
					{title: '2 sec.',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animation-delay-2'},
					{title: '4 sec.',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animation-delay-4'},
					{title: '6 sec.',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animation-delay-6'},
					{title: '8 sec.',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animation-delay-8'},
					{title: '10 sec.',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animation-delay-10'},
					{title: '12 sec.',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animation-delay-12'},
					{title: '14 sec.',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animation-delay-14'},
					{title: '16 sec.',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animation-delay-16'},
					{title: '18 sec.',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animation-delay-18'},
					{title: '20 sec.',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animation-delay-20'},
					{title: '22 sec.',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animation-delay-22'},
					{title: '24 sec.',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animation-delay-24'},
					{title: '26 sec.',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animation-delay-26'},
					{title: '28 sec.',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animation-delay-28'},
					{title: '30 sec.',  selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', classes: 'animation-delay-30'},
                ]},




            ]";




	//	$ret['style_formats_merge'] = true;

	//	$ret['visualblocks_default_state'] = true; //pref
		$ret['style_formats']  = $formats; // json_encode($formats);
		$ret['link_class_list'] = $this->getLinkClassList(); "[
        {title: 'None', value: ''},
        {title: 'Link', value: 'btn btn-link'},
        {title: 'Alert Link', value: 'alert-link'},
        {title: 'Button (Default)', value: 'btn btn-default'},
        {title: 'Button (Primary)', value: 'btn btn-primary'},
        {title: 'Button (Success)', value: 'btn btn-success'},
        {title: 'Button (Info)', value: 'btn btn-info'},
        {title: 'Button (Warning)', value: 'btn btn-warning'},
        {title: 'Button (Danger)', value: 'btn btn-danger'}
    ]";



	/*	$ret['setup'] = "function(ed) {
      ed.addMenuItem('test', {
         text: 'Clear Floats',
         context: 'insert',
         icon: false,
         onclick: function() {
            ed.insertContent('<div class=\"clearfix\" ></div>');
         }
      });
      }";*/
// https://github.com/valtlfelipe/TinyMCE-LocalAutoSave


	/*
		$ret['setup'] = "function(ed) {
      ed.addMenuItem('test', {
         text: 'Clear Floats',
         context: 'insert',
         icon: false,
         onclick: function() {
            ed.insertContent('<br class=\"clearfix\" />');
         }
      });
      }";




	*/

	// e107 Bbcodes.
	/*

		$ret['setup'] = "function(ed) {
			ed.addButton('e107-bbcode', {
				text: 'bbcode',
				icon: 'emoticons',
				onclick: function() {
		// Open window

			ed.windowManager.open({
						title: 'Example plugin',
						body: [
							{type: 'listbox', name: 'code', label: 'BbCode', values: [
								{text: 'Left', value: 'left'},
						        {text: 'Right', value: 'right'},
						        {text: 'Center', value: 'center'}
						    ]},
                            {type: 'textbox', name: 'parm', label: 'Parameters'}
						],
						onsubmit: function(e) {

							var selected = ed.selection.getContent({format : 'text'});

						//	alert(selected);
							// Insert content when the window form is submitted
							ed.insertContent('[' + e.data.code + ']' + selected + '[/' + e.data.code + ']');
						}
					});
				}
			});
	}";
*/


		// Emoticon Support @see //https://github.com/nhammadi/Smileys
		if(e107::pref('core','smiley_activate',false))
		{

			$emo = e107::getConfig("emote")->getPref();
			$pack = e107::pref('core','emotepack');

			$emotes = array();
			$i = 0;
			$c = 0;
			foreach($emo as $path=>$co)
			{
				$codes = explode(" ",$co);
				$url = e_IMAGE_ABS."emotes/" . $pack . "/" . str_replace("!",".",$path);
				$emotes[$i][] = array('shortcut'=>$codes, 'url'=>$url, 'title'=>ucfirst($path));

				if($c == 6)
				{
					$i++;
					$c = 0;
				}
				else
				{
					$c++;
				}
			}

		//	print_r($emotes);

			$ret['extended_smileys'] = json_encode($emotes);
		}

	//	$ret['skin']                    = 'e107admin';
	//	$ret['skin_url']                = SITEURLBASE.e_PLUGIN_ABS.'tinymce4/skins/e107admin';

		$ret['convert_fonts_to_spans']	= false;

/*
		$editorCSS = array(

			'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css',
			'http://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css',
			e_PLUGIN_ABS.'tinymce4/editor.css',
		);
*/
		$editorCSS = $this->getEditorCSS();

		//	file_put_contents(e_LOG."wysiwyg.log", print_r($editorCSS,true));

	//	$editorCSS = $tp->replaceConstants($editorCSS, 'abs');

		$ret['content_css']				= json_encode($editorCSS);
		$ret['content_style']           = "div.clearfix { border-top:1px solid red } ";
		$ret['relative_urls']			= false;  //Media Manager prefers it like this.
		$ret['preformatted']			= true;
		$ret['document_base_url']		= SITEURL;
		$ret['schema']                  = "html5";
		$ret['element_format']          = "html";

	//	$ret['table_default_attributes'] = json_encode(array('class'=>'table table-striped' ));


		if(!empty($ret['templates']))
		{
			$ret['templates']				 = $tp->replaceConstants($ret['templates'],'abs'); // $this->getTemplates();
		}

		if(ADMIN)
		{
			$ret['templates'] = $this->getSnippets();
		}





		//	$this->config['verify_css_classes']	= 'false';

		$text = array();
		foreach($ret as $k=>$v)
		{
			if($k == 'external_plugins')
			{
				$text[] = 'external_plugins: '. $this->getExternalPlugins($v);
				continue;
			}
			$text[] = $k.': '.$this->convertBoolean($v);
		}



		$this->config = implode(",\n",$text);

		

		// -------------------------------------------------------------------------------------


/*



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

	       // --------------------------------


	//		'theme_advanced_statusbar_location'	=> 'bottom',
			'theme_advanced_resizing'			=> 'true',
			'remove_linebreaks'					=> 'false',
			'extended_valid_elements'			=> vartrue($config['extended_valid_elements']),
	//		'pagebreak_separator'				=> "[newpage]",
			'apply_source_formatting'			=> 'true',
			'invalid_elements'					=> 'font,align,script,applet',
			'auto_cleanup_word'					=> 'true',
		//	'cleanup'							=> 'true',
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
		}*/
	}


	function getTemplates()
	{
	//	$templatePath = (is_readable(THEME."templates/tinymce/".$template)) ? THEME."templates/tinymce/".$template : e_PLUGIN."tinymce4/templates/".$template;




	}

	private function bootstrapButtonStyles()
	{
		switch($this->bootstrapVersion)
		{
			case 4:
			case 5:
				$ret = "[
					{title: 'Button (Primary)', selector: 'a', classes: 'btn btn-primary'},
					{title: 'Button (Secondary)', selector: 'a', classes: 'btn btn-secondary'},
	                {title: 'Button (Success)', selector: 'a', classes: 'btn btn-success'},
	                {title: 'Button (Info)', selector: 'a', classes: 'btn btn-info'},
	                {title: 'Button (Warning)', selector: 'a', classes: 'btn-warning'},
	                {title: 'Button (Danger)', selector: 'a', classes: 'btn-danger'},
	                 
					{title: 'Button (Light)', selector: 'a', classes: 'btn btn-light'},
			        {title: 'Button (Dark)', selector: 'a', classes: 'btn btn-dark'},
	
			        {title: 'Button (Outline Primary)', selector: 'a', classes: 'btn btn-outline-primary'},
			        {title: 'Button (Outline Secondary)', selector: 'a', classes: 'btn btn-outline-secondary'},
			        {title: 'Button (Outline Success)', selector: 'a', classes: 'btn btn-outline-success'},
			        {title: 'Button (Outline Info)', selector: 'a', classes: 'btn btn-outline-info'},
			        {title: 'Button (Outline Warning)', selector: 'a', classes: 'btn btn-outline-warning'},
			        {title: 'Button (Outline Danger)', selector: 'a', classes: 'btn btn-outline-danger'},
			        {title: 'Button (Outline Light)', selector: 'a', classes: 'btn btn-outline-light'},
			        {title: 'Button (Outline Dark)', selector: 'a', classes: 'btn btn-outline-dark'},
	                 
	                ]";
			break;
			default:
				$ret = "[
	                 {title: 'Button (Default)', selector: 'a', classes: 'btn btn-default'},
					 {title: 'Button (Primary)', selector: 'a', classes: 'btn btn-primary'},
	                 {title: 'Button (Success)', selector: 'a', classes: 'btn btn-success'},
	                 {title: 'Button (Info)', selector: 'a', classes: 'btn btn-info'},
	                 {title: 'Button (Warning)', selector: 'a', classes: 'btn-warning'},
	                 {title: 'Button (Danger)', selector: 'a', classes: 'btn-danger'},
	                ]";
			
		}
		
		return $ret; 
	}



	private function getLinkClassList()
	{


		switch($this->bootstrapVersion)
		{
			case 4:
			case 5:
				return "[
		        {title: 'None', value: ''},
		        {title: 'Link', value: 'btn btn-link'},
		        {title: 'Alert Link', value: 'alert-link'},
		        {title: 'Button (Primary)', value: 'btn btn-primary'},
		        {title: 'Button (Secondary)', value: 'btn btn-secondary'},
		        {title: 'Button (Success)', value: 'btn btn-success'},
		        {title: 'Button (Info)', value: 'btn btn-info'},
		        {title: 'Button (Warning)', value: 'btn btn-warning'},
		        {title: 'Button (Danger)', value: 'btn btn-danger'},
		        {title: 'Button (Light)', value: 'btn btn-light'},
		        {title: 'Button (Dark)', value: 'btn btn-dark'},

		        {title: 'Button (Outline Primary)', value: 'btn btn-outline-primary'},
		        {title: 'Button (Outline Secondary)', value: 'btn btn-outline-secondary'},
		        {title: 'Button (Outline Success)', value: 'btn btn-outline-success'},
		        {title: 'Button (Outline Info)', value: 'btn btn-outline-info'},
		        {title: 'Button (Outline Warning)', value: 'btn btn-outline-warning'},
		        {title: 'Button (Outline Danger)', value: 'btn btn-outline-danger'},
		        {title: 'Button (Outline Light)', value: 'btn btn-outline-light'},
		        {title: 'Button (Outline Dark)', value: 'btn btn-outline-dark'},

		        ]";
				break;

			default:
				return "[
		        {title: 'None', value: ''},
		        {title: 'Link', value: 'btn btn-link'},
		        {title: 'Alert Link', value: 'alert-link'},
		        {title: 'Button (Default)', value: 'btn btn-default'},
		        {title: 'Button (Primary)', value: 'btn btn-primary'},
		        {title: 'Button (Success)', value: 'btn btn-success'},
		        {title: 'Button (Info)', value: 'btn btn-info'},
		        {title: 'Button (Warning)', value: 'btn btn-warning'},
		        {title: 'Button (Danger)', value: 'btn btn-danger'}
		        ]";
		}
	}


	private function getSnippets()
	{

		$customPath = THEME."templates/tinymce/snippets";

		if(is_dir($customPath))
		{
			$path = $customPath;
			$base = THEME_ABS."templates/tinymce/snippets";
		}
		else
		{
			$path = e_PLUGIN."tinymce4/snippets";
			$base = e_PLUGIN_ABS."tinymce4/snippets";
		}


		$files = e107::getFile()->get_files($path);

		$ret = array();
		foreach($files as $f)
		{
			$content = file_get_contents($f['path'].$f['fname'], null, null, null, 140);

			preg_match('/<!--[^\w]*Title:[\s]([^\r\n]*)[\s]*Info: ?([^\r\n]*)/is', $content, $m);
			if(!empty($m[1]))
			{
			//	$url = e_PLUGIN_ABS."tinymce4/snippets/".$f['fname'];

				$url = $base."/".$f['fname'];
				$ret[] = array('title'=>trim($m[1]), 'url'=>$url, 'description'=>trim($m[2]));
			}
		}

		return json_encode($ret);

	}


	function filter_plugins($plugs)
	{

		$smile_pref = e107::getConfig()->getPref('smiley_activate');

		$admin_only = array("ibrowser");

		$plug_array = explode(" ",$plugs);

		$tinymce_plugins = array();

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

			if(!empty($val))
			{
				$tinymce_plugins[$val] = trim($val);
			}
		}


		$tPref = e107::pref('tinymce4');


		if(!empty($tPref['visualblocks']))
		{
			$tinymce_plugins['visualblocks'] = 'visualblocks';
		}

	//	print_a($tinymce_plugins);

		return implode(" ",$tinymce_plugins);
	}

	/**
	 * @return array
	 */
	public function getEditorCSS($theme = 'front')
	{

		$tp = e107::getParser();

		$libraries = e107::getTheme($theme)->getThemeFiles('library', 'wysiwyg');

		$ret = [];

		foreach($libraries as $lib)
		{
			if(!empty($lib['css']))
			{
				foreach($lib['css'] as $path)
				{
					$ret[] = $tp->replaceConstants($path, 'abs');
				}
			}
		}

		$useThemeStyle = (bool) e107::pref('tinymce4', 'use_theme_style', false);

		if($useThemeStyle)
		{
			$theme = e107::getTheme($theme)->getThemeFiles('css', 'wysiwyg');
			if(!empty($theme['css']))
			{
				foreach($theme['css'] as $path)
				{
					$ret[] = $tp->replaceConstants($path, 'abs');
				}
			}
		}

		$animate = e107::library('files', 'animate.css');
		$ret[] = $tp->replaceConstants($animate['css'][0], 'abs');

		$ret[] = e_PLUGIN_ABS . 'tinymce4/editor.css';

		return $ret;

	}


}