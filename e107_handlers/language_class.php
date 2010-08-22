<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language Class.
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

class language{

// http://www.loc.gov/standards/iso639-2/php/code_list.php

	var $list = array(
            "aa" => "Afar",
			"ab" => "Abkhazian",
			"af" => "Afrikaans",
			"am" => "Amharic",
			"ar" => "Arabic",
			"as" => "Assamese",
			"ae" => "Avestan",
			"ay" => "Aymara",
			"az" => "Azerbaijani",
			"ba" => "Bashkir",
			"be" => "Belarusian",
			"bn" => "Bengali",
			"bh" => "Bihari",
			"bi" => "Bislama",
			"bo" => "Tibetan",
			"bs" => "Bosnian",
			"br" => "Breton",
			"bg" => "Bulgarian",
			"ca" => "Catalan",
			"cs" => "Czech",
			"ch" => "Chamorro",
			"ce" => "Chechen",
			"cn" => "ChineseSimp",
			"cv" => "Chuvash",
			"kw" => "Cornish",
			"co" => "Corsican",
			"cy" => "Welsh",
			"da" => "Danish",
			"de" => "German",
			"dz" => "Dzongkha",
			"el" => "Greek",
			"en" => "English",
			"eo" => "Esperanto",
			"et" => "Estonian",
			"eu" => "Basque",
			"fo" => "Faroese",
			"fa" => "Persian",
			"fj" => "Fijian",
			"fi" => "Finnish",
			"fr" => "French",
			"fy" => "Frisian",
			"gd" => "Gaelic",
			"ga" => "Irish",
			"gl" => "Gallegan",
			"gv" => "Manx",
			"gn" => "Guarani",
			"gu" => "Gujarati",
			"ha" => "Hausa",
			"he" => "Hebrew",
			"hz" => "Herero",
			"hi" => "Hindi",
			"ho" => "Hiri Motu",
			"hr" => "Croatian",
			"hu" => "Hungarian",
			"hy" => "Armenian",
			"iu" => "Inuktitut",
			"ie" => "Interlingue",
			"id" => "Indonesian",
			"ik" => "Inupiaq",
			"is" => "Icelandic",
			"it" => "Italian",
			"jw" => "Javanese",
			"ja" => "Japanese",
			"kl" => "Kalaallisut",
			"kn" => "Kannada",
			"ks" => "Kashmiri",
			"ka" => "Georgian",
			"kk" => "Kazakh",
			"km" => "Khmer",
			"ki" => "Kikuyu",
			"rw" => "Kinyarwanda",
			"ky" => "Kirghiz",
			"kv" => "Komi",
			"ko" => "Korean",
			"ku" => "Kurdish",
			"lo" => "Lao",
			"la" => "Latin",
			"lv" => "Latvian",
			"ln" => "Lingala",
			"lt" => "Lithuanian",
			"lb" => "Letzeburgesch",
			"mh" => "Marshall",
			"ml" => "Malayalam",
	        "mr" => "Marathi",
	        "mk" => "Macedonian",
	        "mg" => "Malagasy",
	        "mt" => "Maltese",
	        "mo" => "Moldavian",
	        "mn" => "Mongolian",
	        "mi" => "Maori",
	        "ms" => "Malay",
	        "my" => "Burmese",
	        "na" => "Nauru",
	        "nv" => "Navajo",

	        "ng" => "Ndonga",
	        "ne" => "Nepali",
	        "nl" => "Dutch",
	        "no" => "Norwegian",

	        "ny" => "Chichewa",
	        "or" => "Oriya",
	        "om" => "Oromo",
	        "pa" => "Panjabi",
	        "pi" => "Pali",
	        "pl" => "Polish",
	        "pt" => "Portuguese",
	        "ps" => "Pushto",
	        "qu" => "Quechua",
	        "ro" => "Romanian",
	        "rn" => "Rundi",
	        "ru" => "Russian",
	        "sg" => "Sango",
	        "sa" => "Sanskrit",
	        "si" => "Sinhalese",
	        "sk" => "Slovak",
	        "sl" => "Slovenian",

	        "sm" => "Samoan",
	        "sn" => "Shona",
	        "sd" => "Sindhi",
	        "so" => "Somali",

	        "es" => "Spanish",
	        "sq" => "Albanian",
	        "sc" => "Sardinian",
	        "sr" => "Serbian",
	        "ss" => "Swati",
	        "su" => "Sundanese",
	        "sw" => "Swahili",
	        "sv" => "Swedish",
	        "ty" => "Tahitian",
	        "ta" => "Tamil",
	        "tt" => "Tatar",
	        "te" => "Telugu",
	        "tg" => "Tajik",
	        "tl" => "Tagalog",
	        "th" => "Thai",
	        "ti" => "Tigrinya",

	        "tn" => "Tswana",
	        "ts" => "Tsonga",
	        "tk" => "Turkmen",
	        "tr" => "Turkish",
			"tw" => "ChineseTrad",
	        "ug" => "Uighur",
	        "uk" => "Ukrainian",
	        "ur" => "Urdu",
	        "uz" => "Uzbek",
	        "vi" => "Vietnamese",

	        "wo" => "Wolof",
	        "xh" => "Xhosa",
	        "yi" => "Yiddish",
	        "yo" => "Yoruba",
	        "za" => "Zhuang",
           // "zh" => "Chinese",
	        "zu" => "Zulu"
		);

		var $names = array(
			"Arabic" 		=> "العربية",
			"Bosnian"		=> "Bosanski",
			"Bulgarian"		=> "Български",
			"Croatian"		=> "Hrvatski",
			"ChineseTrad"  	=> "繁体中文",
			"ChineseSimp"  	=> "简体中文",
			"Dutch"			=> "Nederlands",
			"English"		=> "English",
			"French"		=> "Français",
			"German"		=> "Deutsch",
			"Greek"			=> "Ελληνικά",
			"Hebrew"		=> "עִבְרִית",
			"Hungarian"		=> "Magyar",
			"Italian"		=> "Italiano",
			"Japanese"		=> "日本語",
			"Korean"		=> "한국어",
			"Lithuanian"	=> "Lietuvių",
			"Mongolian"		=> "монгол",
			"Nepali"		=> "नेपाली",
			"Persian"	   	=> "فارسي",
		    "Portuguese"	=> "Português",
			"Polish"		=> "Polski",
			"Romanian"		=> "Romanesc",
			"Russian"		=> "Pусский",
			"Serbian"		=> "Srpski",
			"Spanish"		=> "Español",
			"Slovenian"		=> "Slovensko",
			"Slovakian"		=> "Slovensky",
			"Slovak"		=> "Slovensky",
			"Swedish"		=> "Svenska",
			"Thai"			=> "ภาษาไทย",
			"Turkish"		=> "Türkçe"
		);

// ---  Converts iso to language-name and visa-versa. ----------------

	function convert($data){

		if(strlen($data) > 2)
		{
        	$tmp = array_flip($this->list);
			return $tmp[$data];
		}
		else
		{
			return $this->list[$data];
		}
	}

// -------------------------------------------------------------------

	function toNative($lang)
	{
		return ($this->names[$lang]) ? $this->names[$lang] : $lang;
	}


	function subdomainUrl($language)
	{
		global $pref;
		$codelnk = ($language == $pref['sitelanguage']) ? "www" : $this->convert($language);
        $urlval = str_replace($_SERVER['HTTP_HOST'],$codelnk.".".e_DOMAIN,e_SELF);
        return $urlval;
	}

}




?>
