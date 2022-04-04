<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Language handler
 *
 */

/**
 * @package e107
 * @subpackage	e107_handlers
 * @version $Id$
 */

class language{

// http://www.loc.gov/standards/iso639-2/php/code_list.php

// Valid Language Pack Names are shown directly below on the right. 
	public $detect = false;
	public $e_language = 'English'; // replaced later with $pref
	public $_cookie_domain = '';
	
	/**
	 * Cached list of Installed Language Packs
	 * @var array
	 */
	protected $lanlist = null; // null is important!!!

	// code / folder.
	protected $list = array(
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

			"br" => "Brazilian",

            "bg" => "Bulgarian",
            "my" => "Burmese",
            "ca" => "Catalan",
            "cs" => "Czech",
            "ch" => "Chamorro",
            "ce" => "Chechen",
            "cn" => "ChineseSimp",
            "tw" => "ChineseTrad",
            "cv" => "Chuvash",
            "kw" => "Cornish",
            "co" => "Corsican",

            "da" => "Danish",
            "nl" => "Dutch",
            "dz" => "Dzongkha",
            "de" => "German",


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
            "el" => "Greek",
            "ga" => "Irish",
            "gl" => "Gallegan",

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
            "gv" => "Manx",

            "na" => "Nauru",
            "nv" => "Navajo",

            "ng" => "Ndonga",
            "ne" => "Nepali",

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
            "si" => "Sinhala",
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

            "ug" => "Uighur",
            "uk" => "Ukrainian",
            "ur" => "Urdu",
            "uz" => "Uzbek",
            "vi" => "Vietnamese",

            "cy" => "Welsh",
            "wo" => "Wolof",
            "xh" => "Xhosa",
            "yi" => "Yiddish",
            "yo" => "Yoruba",
            "za" => "Zhuang",
           // "zh" => "Chinese",
            "zu" => "Zulu"
        );

		protected $names = array(
			"Arabic" 		=> "العربية",
			"Bengali"		=> "বাংলা",
			"Bosnian"		=> "Bosanski",
			"Bulgarian"		=> "Български",
			"Croatian"		=> "Hrvatski",
			"ChineseTrad"  	=> "繁体中文",
			"ChineseSimp"  	=> "简体中文",
			"Czech"			=> "Čeština",
			"Dutch"			=> "Nederlands",
			"English"		=> "English",
			"Estonian"		=> "Eesti",
			"French"		=> "Français",
			"Finnish"		=> "Suomi",
			"German"		=> "Deutsch",
			"Greek"			=> "Ελληνικά",
			"Hebrew"		=> "עִבְרִית",
			"Hindi"			=> "हिन्दी",
			"Hungarian"		=> "Magyar",
			"Icelandic"		=> "íslenska",
			"Indonesian"	=> "Bahasa Indonesia",
			"Italian"		=> "Italiano",
			"Japanese"		=> "日本語",
			"Khmer"			=> "ខ្មែរ",
			"Korean"		=> "한국어",
			"Lithuanian"	=> "Lietuvių",
			"Mongolian"		=> "Монгол",
			"Nepali"		=> "नेपाली",
			"Norwegian"		=> "Norsk",
			"Persian"	   	=> "فارسي",
		    "Portuguese"	=> "Português",
		    "Brazilian"     => "Português do Brasil",
			"Polish"		=> "Polski",
			"Romanian"		=> "Română",
			"Russian"		=> "Pусский",
			"Serbian"		=> "Српски",
			"Sinhala"		=> "සිංහල",
			"Spanish"		=> "Español",
			"Slovenian"		=> "Slovensko",
			"Slovakian"		=> "Slovensky",
			"Slovak"		=> "Slovensky",
			"Swedish"		=> "Svenska",
			"Thai"			=> "ภาษาไทย",
			"Turkish"		=> "Türkçe",
			"Vietnamese"	=> "Tiếng Việt",
			"Welsh"         => "Cymraeg"
		);

	/**
	 * Converts iso to language-name and visa-versa.
	 * @param string $data
	 * @return string
	 */
	function convert($data){

		if(strlen($data) > 2)
		{
        	$tmp = array_flip($this->list);
			return isset($tmp[$data]) ? $tmp[$data] : false;
		}
		else
		{
			return (isset($this->list[$data])) ? $this->list[$data] : false;
		}
	}

// -------------------------------------------------------------------
	/**
	 * Check if a Language is installed and valid
	 * @param object $lang - Language to check. eg. 'es' or 'Spanish'
	 * @return false or the name of the valid Language
	 */
	function isValid($lang='')
	{
		if(empty($lang))
		{
			return false;
		}

		global $pref;

		if(!$lang)
		{
			return (ADMIN_AREA &&  vartrue($pref['adminlanguage'])) ? $pref['adminlanguage'] : $pref['sitelanguage'];
		}

		if(strpos($lang,"debug")!==false)
		{
			 return false;
		}

		if($lang == 'E_SITELANGUAGE') // allows for overriding language using a scripted 'define' before class2.php is loaded.
		{
			$lang = $pref['sitelanguage'];
		}

		if($lang == 'E_ADMINLANGUAGE')
		{
			$lang = $pref['adminlanguage'];
		}

		if(strlen($lang)== 2)
		{
			$iso = $lang;
			$lang = $this->convert($lang);	
		}
		else
		{
			$iso = $this->convert($lang);
		}
			
		if($iso==false || $lang==false)
		{
			$diz = ($lang) ? $lang : $iso;
			trigger_error("The selected language (".$diz.") is invalid. See e107_handlers/language_class.php for a list of valid languages. ", E_USER_ERROR);
			return false;
		}
		
		if(is_readable(e_LANGUAGEDIR.$lang.'/'.$lang.'.php'))
		{
			return $lang;	
		}
		else
		{
			trigger_error("The selected language (".$lang.") was not found.", E_USER_ERROR);
			return false;
		}

	}

	/**
	 * Check if the specified domain has multi-language subdomains enabled.
	 * @param string $domain
	 * @return bool|int|string
	 */
	function isLangDomain($domain='')
	{
		if(!$domain)
		{
			return false;
		}
		
		global $pref;
		$mtmp = explode("\n", $pref['multilanguage_subdomain']);
        foreach($mtmp as $val)
		{
        	if($domain == trim($val))
			{
            	return true;
			}
		}

		if(!empty($pref['multilanguage_domain']) && is_array($pref['multilanguage_domain']))
		{
			foreach($pref['multilanguage_domain'] as $lng=>$val)
			{
				if($domain == trim($val))
				{
					return $lng;
				}
			}

		}
		
		return false;
		
	}


	/**
	 * Generic variable translator for LAN definitions. 
	 * @example $lng->translate("My name is [x] and I own a [y]", array('x'=>"John", 'y'=>"Cat")); 
	 * @deprecated Use $tp->lanVars() instead. 
	 */
	function translate($lan, $array= array())
	{
		trigger_error('<b>'.__METHOD__.' is deprecated.</b> Use $tp->lanVars() instead.', E_USER_DEPRECATED); // NO LAN

		$search = array();
		$replace = array();

		foreach($array as $k=>$v)
		{
			$search[] = "[".$k."]";
			$replace[] = "<b>".$v."</b>";
		}
		
		return str_replace($search, $replace, $lan);
	}




	

	/**
	 * Return a list of Installed Language Packs
	 * @param str $type - English or Native.
	 * @example type = english: array(0=>'English', 1=>'French' ...)
	 * @example type = native: array('English'=>'English', 'French'=>'Francais'...)
	 * @example type = abbr: array('en'=>'English, 'fr'=>'French' ... )
	 * @return array
	 */
	function installed($type='english')
	{
		if(null == $this->lanlist)
		{
			$fl = e107::getFile();
			$dirArray = $fl->get_dirs(e_LANGUAGEDIR);
		//	$handle = opendir(e_LANGUAGEDIR);
			$lanlist = array();
		//	while ($file = readdir($handle))
			foreach($dirArray as $file)
			{
				if ($file != '.' && $file != '..' && is_readable(e_LANGUAGEDIR.$file.'/'.$file.'.php'))
				{
					$lanlist[] = $file;
				}
			}
			// closedir($handle);
			
			$this->lanlist = array_intersect($lanlist,$this->list);
		}

		switch($type)
		{
			case "native":
				$natList = array();
				foreach($this->lanlist as $lang)
				{
					$natList[$lang] = $this->toNative($lang);
				}

				natsort($natList);

				return $natList;
				break;

			case "abbr":
				$natList = array();
				foreach($this->lanlist as $lang)
				{
					$iso = $this->convert($lang);
					$natList[$iso] = $lang;
				}

				natsort($natList);

				return $natList;
				break;

			case 'count':
				return count($this->lanlist);
			break;

			case "english":
			default:
				return $this->lanlist;
		}


	}
	
	
	/**
	 * Convert a Language to its Native title. eg. 'Spanish' becomes 'Español'
	 * @param string $lang
	 * @return string
	 */
	function toNative($lang)
	{
		return (!empty($this->names[$lang])) ? $this->names[$lang] : $lang;
	}

	/**
	 * Convert the current URL to a multi-lang for the specified language. 
	 * eg. 'http://www.mydomain.com' becomes 'http://es.mydomain.com'
	 * @param string $language eg. 'Spanish'
	 * @return string url
	 */
	function subdomainUrl($language, $url=e_REQUEST_URL)
	{

		$sitelanguage =  e107::getPref('sitelanguage',null);

		$iso = (strlen($language) == 2) ? $language : $this->convert($language);

		$codelnk = ($language == $sitelanguage) ? "www" : $iso;
		
		if($codelnk == '')
		{
			$codelnk = 'www';	
		}
		
      //  $urlval = str_replace($_SERVER['HTTP_HOST'],$codelnk.".".e_DOMAIN,e_SELF);
		
		/*	$urlval = (e_QUERY)
			        ? str_replace($_SERVER['HTTP_HOST'], $codelnk.'.'.e_DOMAIN, e_SELF).'?'.e_QUERY
			        : str_replace($_SERVER['HTTP_HOST'], $codelnk.'.'.e_DOMAIN, e_SELF);
		*/


		$domain = deftrue('e_DOMAIN','example.com');

		$urlval = str_replace($_SERVER['HTTP_HOST'], $codelnk.'.'.$domain, $url) ;
		
        return (string) $urlval;
	}
	
	/**
 	* Detect a Language Change
	* 1. Scripted Definition    eg. define('e_PAGE_LANGUAGE', 'English');
	* 2. Parked Domain          eg. http://mylanguagedomain.com
 	* 3. Parked subDomain		eg. http://es.mydomain.com (Preferred for SEO)
 	* 4. e_MENU Query			eg. /index.php?[es]
 	* 5. $_GET['elan']			eg. /index.php?elan=es
 	* 6. $_POST['sitelanguage']	eg. <input type='hidden' name='sitelanguage' value='Spanish' />
 	* 7. $GLOBALS['elan']		eg. <?php $GLOBALS['elan']='es' (deprecated)
 	* 
 	* @param boolean $force force detection, don't use cached value
 	*/
	function detect($force = false)
	{
		global $pref;
		
		
		if(false !== $this->detect && !$force) return $this->detect;
		$this->_cookie_domain = '';

		if(defined('e_PAGE_LANGUAGE') && ($detect_language = $this->isValid(e_PAGE_LANGUAGE))) // page specific override.
		{
			$doNothing = '';
			// Do nothing as $detect_language is set.
		}
		elseif(!empty($pref['multilanguage_subdomain']) && $this->isLangDomain(e_DOMAIN) && (defset('MULTILANG_SUBDOMAIN') !== false))
		{
			$detect_language = (e_SUBDOMAIN) ? $this->isValid(e_SUBDOMAIN) : $pref['sitelanguage'];
			// Done in session handler now, based on MULTILANG_SUBDOMAIN value
			//ini_set("session.cookie_domain", ".".e_DOMAIN); // Must be before session_start()
			$this->_cookie_domain = ".".e_DOMAIN;
			define('MULTILANG_SUBDOMAIN',true);
		}
		elseif(!empty($pref['multilanguage_domain']) &&  ($newLang = $this->isLangDomain(e_DOMAIN)))
		{
			$detect_language = $this->isValid($newLang);
			$this->_cookie_domain = ".".e_DOMAIN;
		}
		elseif(e_MENU && ($detect_language = $this->isValid(e_MENU))) // 
		{
			define("e_LANCODE",true);

		}
		elseif(isset($_GET['elan']) && ($detect_language = $this->isValid($_GET['elan']))) // eg: /index.php?elan=Spanish
		{
			$doNothing = '';// Do nothing
		}
		elseif(isset($_POST['setlanguage']) && ($detect_language = $this->isValid($_POST['sitelanguage'])))
		{
			$doNothing = '';// Do nothing
		}
		
		elseif(isset($GLOBALS['elan']) && ($detect_language = $this->isValid($GLOBALS['elan'])))
		{
			$doNothing = '';// Do nothing
		}
		else
		{
			$detect_language = false; // ie. No Change.
		}
		
		// Done in session handler now
		// ini_set("session.cookie_path", e_HTTP);
		
		$this->detect = $detect_language;	
		return $detect_language;
	}

	/**
	 * Get domain to be used in cookeis (e.g. .domain.com), or empty
	 * if multi-language subdomain settings not enabled
	 * Available after self::detect() 
	 * @return string
	 */
	public function getCookieDomain()
	{
		return $this->_cookie_domain;
	}

	/**
	 * Set the Language (Constants, $_SESSION and $_COOKIE) for the current page. 
	 * @param string $language force set
	 * @return void
	 */
	function set($language = null)
	{
		$pref = e107::getPref();
		$session = e107::getSession(); // default core session namespace
		if($language && ($language = $this->isValid($language))) // force set
		{
			$this->detect = $language;
		}
		if($this->detect) // Language-Change Trigger Detected. 
		{
			// new - e_language moved to e107 namespace - $_SESSION['e107']['e_language']
			$oldlan = $session->get('e_language');
			
			if(!$session->has('e_language') || (($session->get('e_language') != $this->detect) && $this->isValid($this->detect)))
			{
				$session->set('e_language', $this->detect);	
			}
			
			if(varset($_COOKIE['e107_language'])!=$this->detect && (defset('MULTILANG_SUBDOMAIN') != TRUE))
			{
				setcookie('e107_language', $this->detect, time() + 86400, e_HTTP);
				$_COOKIE['e107_language'] = $this->detect; // Used only when a user returns to the site. Not used during this session. 
			}
			else // Multi-lang SubDomains should ignore cookies and remove old ones if they exist. 
			{
				if(isset($_COOKIE['e107_language']))
				{
					unset($_COOKIE['e107_language']);
				}
			}
			$user_language = $this->detect;		

			// new system trigger 'lanset' 
			if($oldlan && $oldlan !== $this->detect)
			{
				e107::getEvent()->trigger('lanset', array('new' => $this->detect, 'old' => $oldlan));
			}
		}
		else // No Language-change Trigger Detected. 
		{
							
			if($session->has('e_language'))
			{
				$user_language = $session->get('e_language');
			}
			elseif(isset($_COOKIE['e107_language']) && ($user_language = $this->isValid($_COOKIE['e107_language']))) 
			{
				$session->set('e_language', $user_language);	 		
			}
			else
			{
								
				$user_language = $pref['sitelanguage'];	
				
				if($session->is('e_language'))
				{
					$session->clear('e_language');
				}
			
				if(isset($_COOKIE['e107_language']))
				{
					unset($_COOKIE['e107_language']);
				}	
			}	
		}
		
		$this->e_language = $user_language;
		$this->setDefs();

		if(e_LAN !== 'en')
		{
			e107::getParser()->setMultibyte(true);
		}

		return;
	}


	/**
	 * Set Language-specific Constants
	 * FIXME - language detection is a mess - db handler, mysql handler, session handler and language handler + constants invlolved,
	 * SIMPLIFY, test, get feedback
	 * @return void
	 */
	function setDefs()
	{
		global $pref;
		
		$language = $this->e_language;
		//$session = e107::getSession();

		// SecretR - don't register lanlist in session, confusions, save it as class property (lan class is singleton) 
		e107::getSession()->set('language-list', null); // cleanup test installs, will be removed soon
		
		/*if(!$session->is('language-list'))
		{
			$session->set('language-list', implode(',',$this->installed()));
		}*/
		
		//define('e_LANLIST', $session->get('language-list'));
		define('e_LANLIST',  implode(',', $this->installed()));
		define('e_LANGUAGE', $language);
		define('USERLAN', $language); // Keep USERLAN for backward compatibility
		$iso = $this->convert($language);	
		define("e_LAN", $iso);
		
		// Below is for BC
		if(defined('e_LANCODE') && varset($pref['multilanguage']) && ($language != $pref['sitelanguage']))
		{			
			define("e_LANQRY", "[".$iso."]");
		}
		else
		{
			define("e_LANCODE", '');		
			define("e_LANQRY", false);
		} 	
	}

	/**
	 * @param $force
	 * @return array
	 */
	public function getLanSelectArray($force = false)
	{
		if($force ||null === $this->_select_array)
		{
			$lanlist = explode(',', e_LANLIST);
			$this->_select_array = array();
			foreach ($lanlist as $lan) 
			{
				$this->_select_array[$this->convert($lan)] = $this->toNative($lan);
			}
		}
		return $this->_select_array;
	}

	/**
	 * Return an array of all language types. 
	 */
	public function getList()
	{
		return $this->list;
	}


	/**
	 * Define Legacy LAN constants based on a supplied array.
	 * @param array $bcList legacyLAN => Replacement-LAN
	 */
	public function bcDefs($bcList = null)
	{

		if(empty($bcList))
		{
			$bcList = array(
				'LAN_180'   => 'LAN_SEARCH'
			);
		}

		foreach($bcList as $old => $new)
		{
			if(!defined($old) && defined($new))
			{
				define($old, constant($new));
			}
			elseif(empty($new) && !defined($old))
			{
				define($old,'');
			}

		}

	}

}
