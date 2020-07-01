<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Admin Theme Handler
 *
 */

if(!defined('e107_INIT'))
{
	exit;
}


// new in v.2.1.4
/**
 * Retrieve info about themes on the system. - optimized for speed.
 * Class e_theme
 */
class e_theme
{

	private static $allowedCategories = array(
		'generic',
		 'adult',
		 'blog',
		 'clan',
		 'children',
		 'corporate',
		 'forum',
		 'gaming',
		 'gallery',
		 'news',
		 'social',
		 'video',
		 'multimedia'
	);

	private $_data = array();

	private $_current = null;

	private $_frontcss = null;

	private $_admincss = null;

	private $_legacy_themes = array();


	const CACHETIME = 120; // 2 hours
	const CACHETAG  = "Meta_theme";


	function __construct($options=array())
	{
		$options['force'] = isset($options['force']) ? $options['force'] : false;

		if(!empty($options['themedir']))
		{
			$this->_current = $options['themedir'];
		}

		if(!defined('E107_INSTALL'))
		{
			$this->_frontcss = e107::getPref('themecss');
			$this->_admincss = e107::getPref('admincss');
		}

		if(empty($this->_data) || $options['force'] === true)
		{
			$this->load($options['force']);
		}



	}

	/**
	 * Load theme layout from html files
	 * Requires theme.html file in the theme root directory.
	 * @param string $key layout name
	 * @return array|bool
	 */
	public static function loadLayout($key=null, $theme = null)
	{
		if($theme === null)
		{
			$theme = e107::pref('core','sitetheme');
		}

		if(!is_readable(e_THEME.$theme."/layouts/".$key."_layout.html") || !is_readable(e_THEME.$theme."/theme.html"))
		{
			return false;
		}

		e107::getDebug()->log("Using HTML layout: ".$key.".html");

		$tmp = file_get_contents(e_THEME.$theme."/theme.html");
		$LAYOUT = array();

		list($LAYOUT['_header_'], $LAYOUT['_footer_']) = explode("{---LAYOUT---}", $tmp, 2);

		$tp = e107::getParser();
		e107::getScParser()->loadThemeShortcodes($theme);

		if(strpos($LAYOUT['_header_'], '{---HEADER---}')!==false)
		{
			$LAYOUT['_header_'] = str_replace('{---HEADER---}', $tp->parseTemplate('{HEADER}', true), $LAYOUT['_header_']);
		}

		if(strpos($LAYOUT['_footer_'], '{---FOOTER---}')!==false)
		{
			$LAYOUT['_footer_'] = str_replace('{---FOOTER---}', $tp->parseTemplate('{FOOTER}', true), $LAYOUT['_footer_']);
		}

		$LAYOUT[$key] = file_get_contents(e_THEME.$theme."/layouts/".$key."_layout.html");

		return $LAYOUT;
	}


	/**
	 * Load library dependencies.
	 *
	 * @param string $scope
	 *  front | admin | all | auto
	 */
	public function loadLibrary($scope = 'auto')
	{
		if($scope === 'auto')
		{
			$scope = 'front';

			if(deftrue('e_ADMIN_AREA', false))
			{
				$scope = 'admin';
			}
		}

		$libraries = $this->get('library');

		if(empty($libraries))
		{
			return;
		}


		foreach($libraries as $library)
		{
			if(empty($library['name']))
			{
				continue;
			}

			// If no scope set, we load library on both areas.
			if(empty($library['scope']) || $library['scope'] === 'all')
			{
				if($library['name'] === 'bootstrap' && !empty($library['version']))
				{
					if(intval($library['version']) > 3) // quick fix.
					{
						$library['name'] .= (string) $library['version'];
					}

					 e107::getParser()->setBootstrap($library['version']);

					if(!defined('BOOTSTRAP'))
					{
						define('BOOTSTRAP', (int) $library['version']);
					}
				}
				elseif($library['name'] === 'fontawesome' && !empty($library['version']))
				{
					if(intval($library['version']) > 4) // quick fix.
					{
						$library['name'] .= (string) $library['version'];
					}

					e107::getParser()->setFontAwesome($library['version']);

					if(!defined('FONTAWESOME'))
					{
						define('FONTAWESOME', (int) $library['version']);
					}
				}

				e107::library('load', $library['name']);
				continue;
			}

			if($library['scope'] === $scope)
			{
				e107::library('load', $library['name']);
				continue;
			}
		}
	}

	/**
	 * Get info on the current front or admin theme and selected style.
	 * (ie. as found in theme.xml <stylesheets>)
	 *
	 * @param string $mode
	 *  front | admin | auto
	 * @param string $var
	 *  file | name | scope | exclude
	 *
	 * @return mixed
	 */
	public function cssAttribute($mode = 'front', $var = null)
	{
		$css = $this->get('css');

		if(empty($css))
		{
			return false;
		}

		if($mode === 'auto')
		{
			$mode = 'front';

			if(deftrue('e_ADMIN_AREA', false))
			{
				$mode = 'admin';
			}
		}

		foreach($css as $k => $v)
		{
			if($mode === 'front' && $v['name'] === $this->_frontcss)
			{
				return !empty($var) ? varset($v[$var], null) : $v;
			}

			if($mode === 'admin' && $v['name'] === $this->_admincss)
			{
				return !empty($var) ? varset($v[$var], null) : $v;
			}
		}

		return false;
	}


	public function clearCache()
	{
		e107::getCache()->clear(self::CACHETAG, true);
		return $this;
	}


	public function upgradeThemeCode($text)
	{
		$search = array();
		$replace = array();

		$search[0] 	= '$HEADER ';
		$replace[0]	= '$HEADER["default"] ';

		$search[1] 	= '$FOOTER ';
		$replace[1]	= '$FOOTER["default"] ';

			// Early 0.6 and 0.7 Themes

		$search[2] 	= '$CUSTOMHEADER ';
		$replace[2]	= '$HEADER["custom"] ';

		$search[3] 	= '$CUSTOMFOOTER ';
		$replace[3]	= '$FOOTER["custom"] ';

		//TODO Handle v1.x style themes. eg. $CUSTOMHEADER['something'];

		$text = str_replace($_SESSION['themebulder-remove'],"",$text);
		$text = str_replace($search, $replace, $text);

		return $text;


	}


	/**
	 * Load data for all themes in theme directory.
	 * @param bool|false $force
	 * @return $this
	 */
	private function load($force=false)
	{
		$themeArray = array();

		$tloop = 1;

		$cacheTag = self::CACHETAG;

		if($force === false && $tmp = e107::getCache()->retrieve($cacheTag, self::CACHETIME, true, true))
		{
			$this->_data = e107::unserialize($tmp);
			return $this;
		}

		$array = scandir(e_THEME);

		foreach($array as $file)
		{
			if($file != "." && $file != ".." && $file != "CVS" && $file != "templates" && is_dir(e_THEME.$file) && is_readable(e_THEME.$file."/theme.php"))
			{

				$themeArray[$file] = self::getThemeInfo($file);
				$themeArray[$file]['id'] = $tloop;

				$tloop++;
			}
		}


		$cacheSet = e107::serialize($themeArray,'json');

		e107::getCache()->set($cacheTag,$cacheSet,true,true,true);

		$this->_data = $themeArray;


	}


	/**
	 * Return a var from the current theme.
	 * @param $var
	 * @param null $key
	 * @return array|bool
	 */
	public function get($var=null, $key=null)
	{
		if(empty($var) && isset($this->_data[$this->_current]))
		{
			return $this->_data[$this->_current];
		}

		return isset($this->_data[$this->_current][$var]) ? $this->_data[$this->_current][$var] : false;
	}

	/**
	 * Rebuild URL without trackers, for matching against theme_layout prefs.
	 * @param string $url
	 * @return string
	 */
	private function filterTrackers($url)
	{
		if(strpos($url,'?') === false || empty($url))
		{
			return $url;
		}

		list($site,$query) = explode('?',$url);

		parse_str($query,$get);

		$get = eHelper::removeTrackers($get);

		$ret = empty($get) ? $site : $site.'?'.http_build_query($get);

		return $ret;
	}


	/**
	 * Calculate THEME_LAYOUT constant based on theme preferences and current URL.
	 *
	 * @param array  $cusPagePref
	 * @param string $defaultLayout
	 * @param string $request_url (optional) defaults to e_REQUEST_URL
	 * @param string $request_script $_SERVER['SCRIPT_FILENAME'];
	 * @return int|string
	 */
	public function getThemeLayout($cusPagePref, $defaultLayout, $request_url = null, $request_script = null)
	{

		if($request_url === null)
		{
			$request_url = e_REQUEST_URL;
		}


		$def = "";   // no custom pages found yet.
		$matches = array();


		if(is_array($cusPagePref) && count($cusPagePref)>0)  // check if we match a page in layout custompages.
		{
		    //e_SELF.(e_QUERY ? '?'.e_QUERY : '');
			$c_url = str_replace(array('&amp;'), array('&'), $request_url);//.(e_QUERY ? '?'.e_QUERY : '');// mod_rewrite support
			// FIX - check against urldecoded strings
			$c_url = rtrim(rawurldecode($c_url), '?');

			$c_url = $this->filterTrackers($c_url);

			// First check all layouts for exact matches - possible fix for future issues?.
			/*
			foreach($cusPagePref as $lyout=>$cusPageArray)
			{
				if(!is_array($cusPageArray)) { continue; }

				$base = basename($request_url);

				if(in_array("/".$base, $cusPageArray) || in_array($base, $cusPageArray))
				{
					return $lyout;
				}
			}*/


	        foreach($cusPagePref as $lyout=>$cusPageArray)
			{

				if(!is_array($cusPageArray)) { continue; }

				// NEW - Front page template check - early
				if(in_array('FRONTPAGE', $cusPageArray) && ($c_url == SITEURL || rtrim($c_url, '/') == SITEURL.'index.php'))
				{
					return $lyout;
				}

	            foreach($cusPageArray as $kpage)
				{
					$kpage = str_replace('&#036;', '$', $kpage); // convert database encoding.

					$lastChar = substr($kpage, -1);

					if($lastChar === '$') // script name match.
					{
						$kpage = rtrim($kpage, '$');
						if(!empty($request_script) && strpos($request_script, '/'.$kpage) !== false)
						{
							return $lyout;
						}
					}



					if($lastChar === '!')
					{

						$kpage = rtrim($kpage, '!');

						if(basename($request_url) === $kpage) // exact match specified by '!', skip other processing.
						{
							return $lyout;
						}
						elseif(substr($c_url, - strlen($kpage)) === $kpage)
						{
							$def = $lyout;
						}

						continue;
					}


					if (!empty($kpage) && (strpos($c_url, $kpage) !== false)) // partial URL match
					{
						similar_text($c_url,$kpage,$perc);
						$matches[$lyout] = round($perc,2); // rank the match
					//	echo $c_url." : ".$kpage."  --- ".$perc."\n";
					}

				}
			}
		}

		if(!empty($matches)) // return the highest ranking match.
		{
			$top = array_keys($matches, max($matches));
			$def = $top[0];
			//print_r($matches);
		}

	    if($def) // custom-page layout.
		{
			$layout = $def;
		}
		else // default layout.
		{
	      $layout = $defaultLayout;
		}

		return $layout;

	}






	/**
	 * Return a list of all local themes in various formats.
	 * Replaces getThemeList
	 * @param null|string $mode  null, 'version' | 'id'
	 * @return array|bool a list or false if no results
	 */
	public function getList($mode=null)
	{
		$arr = array();

		if($mode === 'version')
		{
			foreach($this->_data as $dir=>$v)
			{
				$arr[$dir] = array('version'=>$v['version'], 'author'=>$v['author']);
			}

		}
		elseif($mode === 'id')
		{
			foreach($this->_data as $dir=>$v)
			{
				$arr[] = $dir;
			}
		}
		else
		{
			$arr = $this->_data;
		}


		return !empty($arr) ? $arr : false;


	}

	/**
	 * Get a list of all themes in theme folder and its data.
	 * @deprecated
	 * @see load();
	 * @param bool|false xml|false
	 * @param bool|false $force force a refresh ie. ignore cached list.
	 * @return array
	 */
	public static function getThemeList($mode = false, $force = false)
	{
		$themeArray = array();

		$tloop = 1;

		$cacheTag = self::CACHETAG;

		if(!empty($mode))
		{
			$cacheTag = self::CACHETAG.'_'.$mode;
		}

		if($force === false && $tmp = e107::getCache()->retrieve($cacheTag, self::CACHETIME, true, true))
		{
			return e107::unserialize($tmp);
		}

		$array = scandir(e_THEME);

		foreach($array as $file)
		{

			if(($mode == 'xml') && !is_readable(e_THEME.$file."/theme.xml"))
			{
				continue;
			}

			if($file != "." && $file != ".." && $file != "CVS" && $file != "templates" && is_dir(e_THEME.$file) && is_readable(e_THEME.$file."/theme.php"))
			{
				if($mode === "id")
				{
					$themeArray[$tloop] = $file;
				}
				elseif($mode === 'version')
				{
					$data = self::getThemeInfo($file);
					$themeArray[$file] = $data['version'];
				}
				else
				{
					$themeArray[$file] = self::getThemeInfo($file);
					$themeArray[$file]['id'] = $tloop;
				}
				$tloop++;
			}
		}


		$cacheSet = e107::serialize($themeArray,'json');

		e107::getCache()->set($cacheTag,$cacheSet,true,true,true);

		return $themeArray;
	}


	/**
	 * Internal Use. Heavy CPU usage.
	 * Use e107::getTheme($themeDir,$force)->get() instead.
	 * @param $file
	 * @return mixed
	 */
	public static function getThemeInfo($file)
	{
		$reject = array('e_.*');

		$handle2 = e107::getFile()->get_files(e_THEME.$file."/", "\.php|\.css|\.xml|preview\.jpg|preview\.png", $reject, 1);

		$themeArray = array();

		foreach ($handle2 as $fln)
		{
			$file2 = str_replace(e_THEME.$file."/", "", $fln['path']).$fln['fname'];

			$themeArray[$file]['files'][] = $file2;

			if(strstr($file2, "preview."))
			{
				$themeArray[$file]['preview'] = e_THEME.$file."/".$file2;
			}

			// ----------------  get information string for css file - Legacy mode (no theme.xml)

			if(strstr($file2, ".css") && !strstr($file2, "menu.css") && strpos($file2, "e_") !== 0)
			{
				if($cssContents = file_get_contents(e_THEME.$file."/".$file2))
				{
					$nonadmin = preg_match('/\* Non-Admin(.*?)\*\//', $cssContents) ? true : false;
					preg_match('/\* info:(.*?)\*\//', $cssContents, $match);
					$match[1] = varset($match[1], '');
					$scope = ($nonadmin == true) ? 'front' : '';


					$themeArray[$file]['css'][] = array("name"=>$file2,	 "info"=>$match[1], "scope"=>$scope, "nonadmin"=>$nonadmin);

				}
				else
				{
 				//	$mes->addDebug("Couldn't read file: ".e_THEME.$file."/".$file2);
				}
			}


		} // end foreach



		// Load Theme information and merge with existing array. theme.xml (v2.x theme) is given priority over theme.php (v1.x).

		if(in_array("theme.xml", $themeArray[$file]['files']))
		{
			$themeArray[$file] = array_merge($themeArray[$file], self::parse_theme_xml($file));
		}
		elseif(in_array("theme.php", $themeArray[$file]['files']))
		{
			$themeArray[$file] = array_merge($themeArray[$file], self::parse_theme_php($file));
		}

		if(!empty($themeArray[$file]['css']) && count($themeArray[$file]['css']) > 1)
		{
			$themeArray[$file]['multipleStylesheets'] = true;
		}



		return $themeArray[$file];


	}




	public static function parse_theme_php($path)
	{
		$CUSTOMPAGES = null;

		$tp = e107::getParser(); // could be used by a theme file.
		$sql = e107::getDb(); // could be used by a theme file.

		$fp = fopen(e_THEME.$path."/theme.php", "r");
		$themeContents = fread($fp, filesize(e_THEME.$path."/theme.php"));
		fclose($fp);


		preg_match('/themename(\s*?=\s*?)("|\')(.*?)("|\');/si', $themeContents, $match);
		$themeArray['name'] = varset($match[3], '');
		preg_match('/themeversion(\s*?=\s*?)("|\')(.*?)("|\');/si', $themeContents, $match);
		$themeArray['version'] = varset($match[3], '');
		preg_match('/themeauthor(\s*?=\s*?)("|\')(.*?)("|\');/si', $themeContents, $match);
		$themeArray['author'] = varset($match[3], '');
		preg_match('/themeemail(\s*?=\s*?)("|\')(.*?)("|\');/si', $themeContents, $match);
		$themeArray['email'] = varset($match[3], '');
		preg_match('/themewebsite(\s*?=\s*?)("|\')(.*?)("|\');/si', $themeContents, $match);
		$themeArray['website'] = varset($match[3], '');
		preg_match('/themedate(\s*?=\s*?)("|\')(.*?)("|\');/si', $themeContents, $match);
		$themeArray['date'] = varset($match[3], '');
		preg_match('/themeinfo(\s*?=\s*?)("|\')(.*?)("|\');/si', $themeContents, $match);
		$themeArray['info'] = varset($match[3], '');
		preg_match('/xhtmlcompliant(\s*?=\s*?)(\S*?);/si', $themeContents, $match);
		$xhtml = strtolower($match[2]);
		$themeArray['xhtmlcompliant'] = ($xhtml == "true" ? "1.1" : false);

		preg_match('/csscompliant(\s*?=\s*?)(\S*?);/si', $themeContents, $match);
		$css = strtolower($match[2]);
		$themeArray['csscompliant'] = ($css == "true" ? "2.1" : false);

		$themeArray['version'] = str_replace(array('<br />','<br>','<br/>'),' ',$themeArray['version']);

		/*        preg_match('/CUSTOMPAGES(\s*?=\s*?)("|\')(.*?)("|\');/si', $themeContents, $match);
		 $themeArray['custompages'] = array_filter(explode(" ",$match[3]));*/

		$themeContentsArray = explode("\n", $themeContents);

		preg_match_all("#\\$"."CUSTOMHEADER\[(\"|')(.*?)('|\")\].*?#",$themeContents,$match);
		$customHeaderArray = $match[2];

		preg_match_all("#\\$"."CUSTOMFOOTER\[(\"|')(.*?)('|\")\].*?#",$themeContents,$match);
		$customFooterArray = $match[2];

		if(!$themeArray['name'])
		{
			unset($themeArray);
		}


		$lays['legacyDefault']['@attributes'] = array('title'=>'Default',
			 'plugins'=>'',
			 'default'=>'true');

		// load custompages from theme.php only when theme.xml doesn't exist.
		if(!file_exists(e_THEME.$path."theme.xml"))
		{
			foreach ($themeContentsArray as $line)
			{
				if(strstr($line, "CUSTOMPAGES"))
				{
					try
					{
					    @eval(str_replace("$", "\$", $line)); // detect arrays also.
					}
					catch (ParseError $e)
					{


					}

				}
			}

			if(is_array($CUSTOMPAGES))
			{
				foreach ($CUSTOMPAGES as $key=>$val)
				{
					$themeArray['custompages'][$key] = explode(" ", trim($val));
					$lays[$key]['custompages'] = trim($val);
				}
			}
			elseif($CUSTOMPAGES)
			{
				$themeArray['custompages']['legacyCustom'] = explode(" ", $CUSTOMPAGES);
				$lays['legacyCustom']['@attributes'] = array('title'=>'Custom',
					 'plugins'=>'');
			}


			foreach($customHeaderArray as $tm)
			{
				$lays[$tm]['@attributes'] = array('title'=>str_replace("_"," ",$tm),
						 'plugins'=>'');
			}

			foreach($customFooterArray as $tm)
			{
				$lays[$tm]['@attributes'] = array('title'=>str_replace("_"," ",$tm),
						 'plugins'=>'');
			}
		}

		$themeArray['path'] = $path;
		$themeArray['layouts'] = $lays;
		$themeArray['description'] = $themeArray['info'];

		if(file_exists(e_THEME.$path."/preview.jpg"))
		{
			$themeArray['preview'] = array("preview.jpg");
			$themeArray['thumbnail'] = "preview.jpg";
		}

		if(file_exists(e_THEME.$path."/preview.png"))
		{
			$themeArray['preview'] = array("preview.png");
			$themeArray['thumbnail'] = "preview.png";
		}
	//	 echo "<h2>".$themeArray['name']."</h2>";
	//	 print_a($lays);
		$themeArray['legacy'] = true;
		$themeArray['html'] = false;

		return $themeArray;
	}

	public static function parse_theme_xml($path)
	{
		$tp = e107::getParser();
		$xml = e107::getXml();

				//	loadLanFiles($path, 'admin');     // Look for LAN files on default paths
		// layout should always be an array.
		$xml->setOptArrayTags('layout,screenshots/image,plugins/plugin');
		$xml->setOptStringTags('menuPresets,customPages,custompages');

//
	//	$vars = $xml->loadXMLfile(e_THEME.$path.'/theme.xml', true, true);
	//	$oldvars =
		$vars = $xml->loadXMLfile(e_THEME.$path.'/theme.xml', 'advanced', true); // must be 'advanced'

		if($path == "bootstrap3" )
		{
	//		echo "<table class='table table-bordered'>
	//		<tr><th>old</th><th>new parser</th></tr>
	//	<tr><td>".print_a($oldvars,true)."</td><td>".print_a($vars,true)."</td></tr></table>";
		}


		$vars['name'] 			= varset($vars['@attributes']['name']);
		$vars['version'] 		= varset($vars['@attributes']['version']);
		$vars['date'] 			= varset($vars['@attributes']['date']);
		$vars['compatibility'] 	= !empty($vars['@attributes']['compatibility']) ? $tp->filter($vars['@attributes']['compatibility'], 'version') : '';
		$vars['releaseUrl'] 	= varset($vars['@attributes']['releaseUrl']);
		$vars['email'] 			= varset($vars['author']['@attributes']['email']);
		$vars['website'] 		= varset($vars['author']['@attributes']['url']);
		$vars['author'] 		= varset($vars['author']['@attributes']['name']);
		$vars['info'] 			= !empty($vars['description']['@value']) ? $vars['description']['@value'] : varset($vars['description']);
		$vars['category'] 		= self::getThemeCategory(varset($vars['category']));
		$vars['xhtmlcompliant'] = varset($vars['compliance']['@attributes']['xhtml']);
		$vars['csscompliant'] 	= varset($vars['compliance']['@attributes']['css']);
		$vars['path'] 			= $path;
		$vars['@attributes']['default'] = (varset($vars['@attributes']['default']) && strtolower($vars['@attributes']['default']) == 'true') ? 1 : 0;
		$vars['preview'] 		= varset($vars['screenshots']['image']);
		$vars['thumbnail'] 		= isset($vars['preview'][0]) && file_exists(e_THEME.$path.'/'.$vars['preview'][0]) ?  $vars['preview'][0] : '';
		$vars['html']           = file_exists(e_THEME.$path.'/theme.html') && is_dir(e_THEME.$path.'/layouts') ? true : false;


		if(!empty($vars['themePrefs']))
		{

			foreach($vars['themePrefs']['pref'] as $k=>$val)
			{
				$name = $val['@attributes']['name'];
				$vars['preferences'][$name] = $val['@value'];
			}
		}


		unset($vars['authorEmail'], $vars['authorUrl'], $vars['xhtmlCompliant'], $vars['cssCompliant'], $vars['screenshots']);

		// Compile layout information into a more usable format.


		$custom = array();
		/*
		foreach ($vars['layouts'] as $layout)
		{
			foreach ($layout as $key=>$val)
			{
				$name = $val['@attributes']['name'];
				unset($val['@attributes']['name']);
				$lays[$name] = $val;


				if(isset($val['customPages']))
				{
					$cusArray = explode(" ", $val['customPages']);
					$custom[$name] = array_filter($cusArray);
				}
				if(isset($val['custompages']))
				{
					$cusArray = explode(" ", $val['custompages']);
					$custom[$name] = array_filter(explode(" ", $val['custompages']));
				}
			}
		}
		*/

		$lays = array();

		foreach($vars['layouts']['layout'] as $k=>$val)
		{
			$name = $val['@attributes']['name'];
			unset($val['@attributes']['name']);
			$lays[$name] = $val;


			if(isset($val['custompages']))
			{
				if(is_string($val['custompages']))
				{
					$custom[$name] = array_filter(explode(" ", $val['custompages']));
				}
				elseif(is_array($val['custompages']))
				{
					$custom[$name] = $val['custompages'];
				}
			}
		}


		$vars['layouts'] = $lays;
		$vars['path'] = $path;
		$vars['custompages'] = $custom;
		$vars['legacy'] = false;
		$vars['library'] = array();

		if(!empty($vars['libraries']['library']))
		{
			$vars['css'] = array();

			foreach($vars['libraries']['library'] as $val)
			{
				$vars['library'][] = array(
					'name'  => $val['@attributes']['name'],
					'version' => varset($val['@attributes']['version']),
					'scope' => varset($val['@attributes']['scope']),
				);
			}

			unset($vars['libraries']);
		}

		if(!empty($vars['stylesheets']['css']))
		{
			$vars['css'] = array();

			foreach($vars['stylesheets']['css'] as $val)
			{
			//	$notadmin = vartrue($val['@attributes']['admin']) ? false : true;
				$notadmin = (varset($val['@attributes']['scope']) !== 'admin') ? true : false;

				$vars['css'][] = array(
					"name"      => $val['@attributes']['file'],
					"info"      => $val['@attributes']['name'],
					"nonadmin"  => $notadmin,
					'scope'     => vartrue($val['@attributes']['scope']),
					'exclude'   => vartrue($val['@attributes']['exclude']),
					'description'   => vartrue($val['@attributes']['description']),
					'thumbnail'   => vartrue($val['@attributes']['thumbnail'])
				);
			}

			unset($vars['stylesheets']);
		}

		$vars['glyphs'] = array();
		if(!empty($vars['glyphicons']['glyph']))
		{
			foreach($vars['glyphicons']['glyph'] as $val)
			{
				$vars['glyphs'][] = array(
					'name'    => isset($val['@attributes']['name']) ? $val['@attributes']['name'] : '',
					'pattern' => isset($val['@attributes']['pattern']) ? $val['@attributes']['pattern'] : '',
					'path'    => isset($val['@attributes']['path']) ? $val['@attributes']['path'] : '',
					'class'   => isset($val['@attributes']['class']) ? $val['@attributes']['class'] : '',
					'prefix'  => isset($val['@attributes']['prefix']) ? $val['@attributes']['prefix'] : '',
					'tag'     => isset($val['@attributes']['tag']) ? $val['@attributes']['tag'] : '',
				);
			}

			unset($vars['glyphicons']);
		}


		if($path == "bootstrap3" )
		{
            //	e107::getMessage()->addDebug("<h2>".$path."</h2>");
            //	e107::getMessage()->addDebug(print_a($vars,true));
            //	print_a($vars);
            //	echo "<table class='table'><tr><td>".print_a($vars,true)."</td><td>".print_a($adv,true)."</td></tr></table>";
		}


		return $vars;
	}


		/**
	 * Validate and return the name of the categories.
	 *
	 * @param string [optional] $categoryfromXML
	 * @return string
	 */
	private static function getThemeCategory($categoryfromXML = '')
	{
		if(!$categoryfromXML)
		{
			return 'generic';
		}

		$tmp = explode(",", $categoryfromXML);
		$category = array();
		foreach ($tmp as $cat)
		{
			$cat = trim($cat);
			if(in_array($cat, self::$allowedCategories))
			{
				$category[] = $cat;
			}
			else
			{
				$category[] = 'generic';
			}
		}

		return implode(', ', $category);

	}


}





class themeHandler
{
	
	var $themeArray;
	var $action;
	var $id;
	var $frm;
	var $fl;
	var $themeConfigObj = null;
	var $themeConfigFormObj= null;
	var $noLog = FALSE;
	private $curTheme = null;
	
	private $approvedAdminThemes = array('bootstrap','bootstrap3');
	
	public $allowedCategories = array('generic',
		 'adult',
		 'blog',
		 'clan',
		 'children',
		 'corporate',
		 'forum',
		 'gaming',
		 'gallery',
		 'news',
		 'social',
		 'video',
		 'multimedia');
		 
	/**
	 * Marketplace handler instance
	 * @var e_marketplace
	 */
	protected $mp;

	const RENDER_THUMBNAIL = 0;
	const RENDER_SITEPREFS = 1;
	const RENDER_ADMINPREFS = 2;

	
	/* constructor */
	
	function __construct()
	{
		
		global $e107cache,$pref;
		$mes = e107::getMessage();

/*
		if(deftrue('e_BOOTSTRAP3_ADMIN'))
		{
			$this->approvedAdminThemes[] = 'bootstrap3';
		}
		*/
		require_once (e_HANDLER."form_handler.php");

		
		//enable inner tabindex counter
		if(!deftrue("E107_INSTALL"))
		{
			 $this->frm = new e_form();
		}

		
		$this->fl = e107::getFile();
		
		$this->postObserver();

	
	}

	public function postObserver()
	{

		$mes = e107::getMessage();
		$pref = e107::getPref();

		if(!empty($_POST['upload']))
		{
			$unzippedTheme = $this->themeUpload();
		}

		if(!empty($_POST['curTheme']))
		{
			$this->curTheme = e107::getParser()->filter($_POST['curTheme'],'file');
		}

		if(!empty($_POST['setUploadTheme']) && !empty($unzippedTheme))
		{
			$themeArray = $this->getThemes();
			$this->id = $themeArray[$unzippedTheme]['id'];

			if($this->setTheme())
			{

				$mes->addSuccess(TPVLAN_3);
			}
			else
			{
				$mes->addError(TPVLAN_86);
			}

		}

		if(!empty($_POST['installContent']))
		{
			$this->installContent($_POST['installContent']);
		}


		$this->themeArray = (defined('E107_INSTALL')) ? $this->getThemes('xml') : $this->getThemes();

		//     print_a($this -> themeArray);


		foreach ($_POST as $key=>$post)
		{
			if(strstr($key, "preview"))
			{
				//	$this -> id = str_replace("preview_", "", $key);
				$this->id = key($post);
				$this->themePreview();
			}

		/*	if(strstr($key, "selectmain"))
			{
				//	$this -> id = str_replace("selectmain_", "", $key);
				$this->id = key($post);
				if($this->setTheme())
				{
					$mes->addSuccess(TPVLAN_3);
				}
				else
				{
					$mes->addError(TPVLAN_3);
				}
			}*/

			if(strstr($key, "selectadmin"))
			{
				$this->id = key($post);
				$this->setAdminTheme();
				$this->refreshPage('admin');
			}
		}


		if(isset($_POST['submit_adminstyle']))
		{
			$this->id = $this->curTheme;
			if($this->setAdminStyle())
			{
				eMessage::getInstance()->add(TPVLAN_43, E_MESSAGE_SUCCESS);
			}
			e107::getConfig()->save(true);
		}

		if(isset($_POST['submit_style']))
		{
			$this->id = $this->curTheme;

			$this->setLayouts(); // Update the layouts in case they have been manually changed.
			$this->SetCustomPages($_POST['custompages']);
			$this->setStyle();

			e107::getConfig()->save(true);

		}

		if(!empty($_POST['git_pull']))
		{
			$gitTheme = e107::getParser()->filter($_POST['git_pull'],'w');
			$return = e107::getFile()->gitPull($gitTheme, 'theme');
			$mes->addSuccess($return);
		}

		if(isset($_POST['installplugin']))
		{
			$key = key($_POST['installplugin']);

			e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_plugin.php");
			require_once (e_HANDLER."plugin_class.php");

			$eplug = new e107plugin;
			$message = $eplug->install_plugin($key);
			$mes->add($message, E_MESSAGE_SUCCESS);
		}

		if(isset($_POST['setMenuPreset']))
		{
			$key = key($_POST['setMenuPreset']);
			e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_menus.php");
			require_once (e_HANDLER."menumanager_class.php");
			$men = new e_menuManager();
			$men->curLayout = $key;
			//menu_layout is left blank when it's default.
			$men->dbLayout = ($men->curLayout != $pref['sitetheme_deflayout']) ? $men->curLayout : "";


			if($areas = $men->menuSetPreset())
			{
				$message = '';
				foreach ($areas as $val)
				{
					$ar[$val['menu_location']][] = $val['menu_name'];
				}
				foreach ($ar as $k=>$v)
				{
					$message .= MENLAN_14." ".$k." : ".implode(", ", $v)."<br />";
				}

				$mes->add(MENLAN_43." : ".$key."<br />".$message, E_MESSAGE_SUCCESS);
			}

		}


	}
	
	function getThemes($mode = FALSE)
	{
		$themeArray = array();
		
		$tloop = 1;
		$fl = e107::getFile();
		$array = $fl->get_dirs(e_THEME);
		
		foreach($array as $file)
		{
			
			if(($mode == 'xml') && !is_readable(e_THEME.$file."/theme.xml"))
			{
				continue;
			}
			
			if($file != "." && $file != ".." && $file != "CVS" && $file != "templates" && is_dir(e_THEME.$file) && is_readable(e_THEME.$file."/theme.php"))
			{
				if($mode == "id")
				{
					$themeArray[$tloop] = $file;
				}
				else
				{
					$themeArray[$file] = $this->getThemeInfo($file);
					$themeArray[$file]['id'] = $tloop;
				}
				$tloop++;
			}
		}
	
	//	 echo "<pre>";
	//	 print_r($themeArray);
	//	 echo "</pre>";

		
		return $themeArray;
	}

	
	function getThemeInfo($file)
	{
	//	return e_theme::getThemeInfo($file);

		$mes = e107::getMessage();
		$reject = array('e_.*');
		$handle2 = e107::getFile()->get_files(e_THEME.$file."/", "\.php|\.css|\.xml|preview\.jpg|preview\.png", $reject, 1);

		foreach ($handle2 as $fln)
		{
			$file2 = str_replace(e_THEME.$file."/", "", $fln['path']).$fln['fname'];
			
			$themeArray[$file]['files'][] = $file2;
			
			if(strstr($file2, "preview."))
			{
				$themeArray[$file]['preview'] = e_THEME.$file."/".$file2;
			}

			// ----------------  get information string for css file - Legacy mode (no theme.xml) 

			if(strstr($file2, ".css") && !strstr($file2, "menu.css") && strpos($file2, "e_") !== 0)
			{
				if($cssContents = file_get_contents(e_THEME.$file."/".$file2))
				{
					$nonadmin = preg_match('/\* Non-Admin(.*?)\*\//', $cssContents) ? true : false;
					preg_match('/\* info:(.*?)\*\//', $cssContents, $match);
					$match[1] = varset($match[1], '');
					$scope = ($nonadmin == true) ? 'front' : '';


					$themeArray[$file]['css'][] = array("name"=>$file2,	 "info"=>$match[1], "scope"=>$scope, "nonadmin"=>$nonadmin);
					
				}
				else
				{
 				//	$mes->addDebug("Couldn't read file: ".e_THEME.$file."/".$file2);	
				}
			}

		
		} // end while..
		
		// Load Theme information and merge with existing array. theme.xml (v2.x theme) is given priority over theme.php (v1.x).
		
		if(in_array("theme.xml", $themeArray[$file]['files']))
		{
			$themeArray[$file] = array_merge($themeArray[$file], $this->parse_theme_xml($file));
		}
		elseif(in_array("theme.php", $themeArray[$file]['files']))
		{
			$themeArray[$file] = array_merge($themeArray[$file], $this->parse_theme_php($file));
		}

		if(!empty($themeArray[$file]['css']) && count($themeArray[$file]['css']) > 1)
		{
			$themeArray[$file]['multipleStylesheets'] = true;
		}



		return $themeArray[$file];

	
	}
	
	/**
	 * Validate and return the name of the categories.
	 *
	 * @param string [optional] $categoryfromXML
	 * @return string
	 */
	function getThemeCategory($categoryfromXML = '')
	{
		if(!$categoryfromXML)
		{
			return 'generic';
		}
		
		$tmp = explode(",", $categoryfromXML);
		$category = array();
		foreach ($tmp as $cat)
		{
			$cat = trim($cat);
			if(in_array($cat, $this->allowedCategories))
			{
				$category[] = $cat;
			}
			else
			{
				$category[] = 'generic';
			}
		}
		
		return implode(', ', $category);
	
	}

	
	function themeUpload()
	{
		if(!$_POST['ac'] == md5(ADMINPWCHANGE))
		{
			exit;
		}

		$mes = e107::getMessage();
		$ns = e107::getRender();
		
	//	extract($_FILES);
		//print_a($_FILES);

		if(!is_writable(e_TEMP))
		{
			$mes->addInfo(TPVLAN_20);
			return FALSE;
		}
		
		
		$fl = e107::getFile();
		$mp = $this->getMarketplace(); 
		$status = $fl->getUploaded(e_TEMP); 
		
		if(!empty($status[0]['error']))
		{
			$mes->addError($status[0]['message']);
			return; 	
		}
		
		$mes->addSuccess($status[0]['message']); 
		
		return $fl->unzipArchive($status[0]['name'],'theme');

		
	//	else
	/*
		{
			// FIXME - temporary fixes to upload process, check required. 
			// Probably in need of a rewrite to use process_uploaded_files();
			require_once (e_HANDLER."upload_handler.php");
			$fileName = $_FILES['file_userfile']['name'][0]; 
			$fileSize = $_FILES['file_userfile']['size'][0];
			$fileType = $_FILES['file_userfile']['type'][0]; // type is returned as mime type (application/octet-stream) not as zip/rar

			// There may be a better way to do this.. MIME may not be secure enough
			// process_uploaded_files() ?
			$mime_zip 	= array("application/octet-stream", "application/zip", "multipart/x-zip");
			$mime_gzip 	= array("application/x-gzip", "multipart/x-gzip");
			// rar?
			
			if(in_array($fileType, $mime_zip))
			{
				$fileType = "zip";
			}
			elseif(in_array($fileType, $mime_gzip))
			{
				$fileType = "gzip";
			}
			else
			{
				$mes->addError(TPVLAN_17);
				return FALSE;
			}
			
			if($fileSize)
			{
				
				$uploaded = file_upload(e_THEME);				
				$archiveName = $uploaded[0]['name'];
				
				if($fileType == "zip")
				{
					require_once (e_HANDLER."pclzip.lib.php");
					$archive = new PclZip(e_THEME.$archiveName);
					$unarc = ($fileList = $archive->extract(PCLZIP_OPT_PATH, e_THEME, PCLZIP_OPT_SET_CHMOD, 0666)); // FIXME - detect folder structure similar to 'Find themes'
				}
				else
				{
					require_once (e_HANDLER."pcltar.lib.php");
					$unarc = ($fileList = PclTarExtract($archiveName, e_THEME)); // FIXME - detect folder structure similar to 'Find themes'
				}
				
				if(!$unarc)
				{
					if($fileType == "zip")
					{
						$error = TPVLAN_46." '".$archive->errorName(TRUE)."'";
					}
					else
					{
						$error = TPVLAN_47.PclErrorString().", ".TPVLAN_48.intval(PclErrorCode());
					}
					
					$mes->addError(TPVLAN_18." ".$archiveName." ".$error);
					return FALSE;
				}
				
				$folderName = substr($fileList[0]['stored_filename'], 0, (strpos($fileList[0]['stored_filename'], "/")));
				$mes->addSuccess(TPVLAN_19);
				
				if(varset($_POST['setUploadTheme']))
				{
					$themeArray = $this->getThemes();
					$this->id = $themeArray[$folderName]['id'];
					if($this->setTheme())
					{
						$mes->addSuccess(TPVLAN_3);
					}
					else
					{
						$mes->addError("Could not change site theme."); // TODO LAN
					}
				
				}
				
				@unlink(e_THEME.$archiveName);
			}
		}
	 * 
	 */
	}
	

	private function search($name, $searchVal, $submitName, $filterName='', $filterArray=false, $filterVal=false)
	{
		$frm = e107::getForm();
		
		return $frm->search($name, $searchVal, $submitName, $filterName, $filterArray, $filterVal);
		/*
		$text = '<span class="input-append e-search"><i class="icon-search"></i>
    		'.$frm->text($name, $searchVal,20,'class=search-query').'
   			 <button class="btn btn-primary" name="'.$submitName.'" type="submit">'.LAN_GO.'</button>
    	</span>';
		
	//	$text .= $this->admin_button($submitName,LAN_SEARCH,'search');
		
		return $text;
		*/
	}

	/**
	 * Temporary, e107::getMarketplace() coming soon
	 * @return e_marketplace
	 */
	public function getMarketplace()
	{
		if(null === $this->mp)
		{
			require_once(e_HANDLER.'e_marketplace.php');
			$this->mp = new e_marketplace(); // autodetect the best method
		}
		return $this->mp;
	}
	
	
	function renderOnline($ajax=false)
	{
		global $e107SiteUsername, $e107SiteUserpass;
			$xml 	= e107::getXml();
			$mes 	= e107::getMessage();
			$frm 	= e107::getForm();
			$ns 	= e107::getRender();
			$mp 	= $this->getMarketplace();
			$from 	= intval(varset($_GET['frm']));
			$limit 	= 96; // FIXME - ajax pages load
			$srch 	= preg_replace('/[^\w]/','', vartrue($_GET['srch'])); 
			
			// check for cURL
			if(!function_exists('curl_init'))
			{
				$mes->addWarning(TPVLAN_79); 
			}
			
			// auth
			$mp->generateAuthKey($e107SiteUsername, $e107SiteUserpass);
			
			// do the request, retrieve and parse data
			$xdata = $mp->call('getList', array(
				'type' => 'theme', 
				'params' => array('limit' => $limit, 'search' => $srch, 'from' => $from)
			));
			$total = $xdata['params']['count'];
			
			// OLD BIT OF CODE ------------------------------->
			/*$file = "http://e107.org/feed?type=theme&frm=".$from."&srch=".$srch."&limit=".$limit;
			
			$mes->addDebug("File = ".$file);
			
			$xml->setOptArrayTags('theme,screenshots/image'); // make sure 'theme' tag always returns an array
		//	$xdata = $xml->loadXMLfile($file,'advanced',true);
			$xdata = $xml->loadXMLfile($file,true,false);
			$total = $xdata['@attributes']['total'];*/
			// OLD BIT OF CODE ------------------------------->
			
			$amount =$limit;
			
			/*
			if($total > $amount)
			{
				//$parms = $total.",".$amount.",".$from.",".e_SELF.'?mode='.$_GET['mode'].'&amp;frm=[FROM]';
				
				$url = rawurlencode(e_SELF.'?mode='.$_GET['mode'].'&frm=[FROM]');
				$parms = "total=".$total."&amount=".$amount."&current=".$from."&url=".$url."&caption=off&tmpl=basic&navcount=4&glyphs=1";
				
				$text .= "<div class='span5' style='margin-left: 100px;margin-top:10px'>".$tp->parseTemplate("{NEXTPREV=$parms}",TRUE)."</div>";
			}
			 */
			
		//	print_a($xdata);
	
			$c = 1;

			$filterName = '';
			$filterArray = array();
			$filterVal = '';
		
			$text = "<form class='form-search' action='".e_SELF."?".e_QUERY."' id='core-plugin-list-form' method='get'>";
			$text .= '<div id="myCarousel"  class="carousel slide" data-interval="false">';
			$text .= "<div class='form-inline clearfix row-fluid'>";
			$text .= $this->search('srch', $srch, 'go', $filterName, $filterArray, $filterVal).$frm->hidden('mode','online');
			$text .= '<div class="btn-group" style="margin-left:10px"><a class="btn btn-primary" href="#myCarousel" data-slide="prev">&lsaquo;</a><a class="btn btn-primary" href="#myCarousel" data-slide="next">&rsaquo;</a></div>';
			$text .= "{CAROUSEL_INDICATORS}";		
			$text .= "</div>";
			$text .= '<div id="shop" style="margin-top:10px;min-height:585px" class=" carousel-inner">';

			if(is_array($xdata['data'] ))
			{
				
				$text .= '<div  class="active item">';
				
				$slides = array();
				
				foreach($xdata['data'] as $r)
				{
					if(E107_DBG_PATH)
					{
						$mes->addDebug(print_a($r,true));	
					}
					
					$theme = array(
						'id'			=> $r['params']['id'],
						'type'			=> 'theme',
						'mode'			=> $r['params']['mode'],
						'name'			=> stripslashes($r['name']),
						'category'		=> $r['category'],
						'preview' 		=> varset($r['screenshots']['image']),
						'date'			=> $r['date'],
						'version'		=> $r['version'],
						'thumbnail'		=> $r['thumbnail'],
						'url'			=> $r['urlView'],
						'author'		=> $r['author'],
						'website'		=> $r['authorUrl'],
						'compatibility'	=> $r['compatibility'],
						'description'	=> $r['description'],
						'price'			=> $r['price'],
						'livedemo'		=> $r['livedemo'],
					);


					$text .= $this->renderTheme(FALSE, $theme);
					
					$c++;
					
					if($c == 19)
					{
						$text .= '</div><div class="item">';
						$slides[] = 1;
						$c = 1;
					}
					
					
					
					
					/*
	    		
				    [author] => e107 Inc
				    [summary] => Bootstrap e107 admin theme
				    [category] => generic
				    [keywords] => Array
				        (
				            [word] => Array
				                (
				                    [0] => bootstrap
				                    [1] => clean
				                )
				
				        )
						[name] => bootstrap
				    [version] => 1.0
				    [date] => 2012-12-01
				    [compatibility] => 2.0
				    [releaseUrl] => 
				    [email] => e107inc@something.com
				    [website] => http://e107.org
				    [info] => Bootstrap e107 admin theme
				    [compliance] => Array
				        (
				            [@attributes] => Array
				                (
				                    [xhtml] => 
				                    [css] => 
				                )
				
				        )
				
				    [xhtmlcompliant] => 
				    [csscompliant] => 
				    [path] => bootstrap		
								
				*/	
					
				}	
				
				
				$text .= "<div class='clear'>&nbsp;</div>";
				$text .= "</div>";
				$text .= "</div>";
			}
			else 
			{
				$mes->addInfo(TPVLAN_80);		
			}	
				
			 $indicators = '<ol class="carousel-indicators col-md-6 span6">
				<li data-target="#myCarousel" data-slide-to="0" class="active"></li>';
				
			foreach($slides as $key=>$v)
			{
				$id = $key + 1;	
				$indicators .= '<li data-target="#myCarousel" data-slide-to="'.$id.'"></li>';
			}
			
			$indicators .=	'</ol>';		
						
			$text = str_replace("{CAROUSEL_INDICATORS}",$indicators,$text);

			$text .= "</form>";

			$ns->tablerender(TPVLAN_26.SEP.TPVLAN_69, $mes->render().$text);

	}
	
	
	
	function showThemes($mode = 'main')
	{
		global $pref;
		$mes = e107::getMessage();
		$ns = e107::getRender();
		$tp = e107::getParser();
		$frm = e107::getForm();
		
		
		echo "<div>";
		
		if($mode == "main" || !$mode) // Show Main Configuration
		{
			foreach ($this->themeArray as $key=>$theme)
			{
				if($key == $pref['sitetheme'])
				{
					$text = $this->renderTheme(1, $theme);
				}
			}
			echo "<form enctype='multipart/form-data' method='post' action='".e_SELF."?mode=".$mode."'>\n";
			$ns->tablerender(TPVLAN_26.SEP.TPVLAN_33, $mes->render().$text);
			echo "</form>";
		}
		
		// Show Admin Configuration
		if($mode == "admin")
		{
			
			foreach ($this->themeArray as $key=>$theme)
			{
				if($key == $pref['admintheme'])
				{
					$text = $this->renderTheme(2, $theme);
				}
			}
			echo "<form enctype='multipart/form-data' method='post' action='".e_SELF."?mode=".$mode."'>\n";
			$ns->tablerender(TPVLAN_26.SEP.TPVLAN_34, $mes->render().$text);
			echo "</form>";
		}
		
		// Show Upload Form
		if($mode == "upload")
		{
			$this->renderUploadForm();
		}
		
		// Show All Themes
		if($mode == "choose")
		{
			
			$text = "";
			foreach ($this->themeArray as $key=>$theme)
			{
				$text .= $this->renderTheme(FALSE, $theme);
				// print_a($theme);
			}
			$text .= "<div class='clear'>&nbsp;</div>";
			echo "<form enctype='multipart/form-data' method='post' action='".e_SELF."?mode=".$mode."'>\n";	
			$ns->tablerender(TPVLAN_26.SEP.TPVLAN_39, $mes->render().$text);
			$text .= "</form>";
			
		}
		
		
		if($mode == "online")
		{
			$this->renderOnline();
		}
		
		echo "</div>\n";
	}




	
	function renderUploadForm() 
	{
		$mes = e107::getMessage();
		$ns = e107::getRender();
		$sql = e107::getDb();
		$frm = e107::getForm();
		
		if(!is_writable(e_THEME))
		{
			$ns->tablerender(TPVLAN_16, TPVLAN_15);
			$text = "";
		}
		else
		{
			require_once(e_HANDLER.'upload_handler.php');
			$max_file_size = get_user_max_upload();
			
			$text = "
			<form enctype='multipart/form-data' action='".e_SELF."' method='post'>
				<table class='table adminform'>
					<colgroup>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
				<tr>
					<td>".TPVLAN_13."</td>
					<td>
						<input type='hidden' name='MAX_FILE_SIZE' value='{$max_file_size}' />
						<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
						<input class='tbox' type='file' name='file_userfile[]' size='50' />
					</td>
				</tr>
                <tr>
					<td>".TPVLAN_10."</td>
					<td><input type='checkbox' name='setUploadTheme' value='1' /></td>
				</tr>
				</table>
			
			<div class='buttons-bar center'>".$frm->admin_button('upload', 1, 'submit', LAN_UPLOAD)."</div>
			</form>
			";
		}

		$ns->tablerender(TPVLAN_26.SEP.TPVLAN_38, $mes->render().$text);
	}

	
	function renderThemeInfo($theme)
	{

		if(empty($theme))
		{
			return null;
		}

		if(!empty($theme['compatibility']) && $theme['compatibility'] == 2)
		{
			$theme['compatibility'] = '2.0';
		}

		$version = e107::getParser()->filter(e_VERSION,'version');

		$compatLabel = TPVLAN_77;
		$compatLabelType = 'warning';

		if(version_compare($theme['compatibility'],$version, '<=') === false)
		{
			$compatLabelType = 'danger';
			$compatLabel = defset('TPVLAN_97', "This theme requires a newer version of e107.");
		}

		global $pref;
		$author 		= !empty($theme['email']) ? "<a href='mailto:".$theme['email']."' title='".$theme['email']."'>".$theme['author']."</a>" : $theme['author'];
		$website 		= !empty($theme['website']) ? "<a href='".$theme['website']."' rel='external'>".$theme['website']."</a>" : "";
//		$preview 		= "<a href='".SITEURL."news.php?themepreview.".$theme['id']."' title='".TPVLAN_9."' >".($theme['preview'] ? "<img src='".$theme['preview']."' style='border: 1px solid #000;width:200px' alt='' />" : "<img src='".e_IMAGE_ABS."admin_images/nopreview.png' title='".TPVLAN_12."' alt='' />")."</a>";
		$description 	= vartrue($theme['description'],'');
		$compat			= (version_compare(1.9,$theme['compatibility'],'<')) ? "<span class='label label-".$compatLabelType."'>".$theme['compatibility']."</span><span class='text-".$compatLabelType."'> ".$compatLabel."</span>": vartrue($theme['compatibility'],'1.0');
		$price 			= (!empty($theme['price'])) ? "<span class='label label-primary'><i class='icon-shopping-cart icon-white'></i> ".$theme['price']."</span>" : "<span class='label label-success'>".TPVLAN_76."</span>";

		$text = e107::getForm()->open('theme-info','post');
		$text .= "<table class='table table-striped'>";



	//	$text .= "<tr><th colspan='2'><h3>".$theme['name']." ".$theme['version']."</h3></th></tr>";
		$text .=  "<tr><td style='vertical-align:top; width:24%'><b>".TPVLAN_75."</b>:</td><td style='vertical-align:top'>".$price."</td></tr>";

		$text .= ($author) ? "<tr><td style='vertical-align:top; width:24%'><b>".LAN_AUTHOR."</b>:</td><td style='vertical-align:top'>".$author."</td></tr>" : "";
		$text .= ($website) ? "<tr><td style='vertical-align:top; width:24%'><b>".TPVLAN_5."</b>:</td><td style='vertical-align:top'>".$website."</td></tr>" : "";
		$text .= !empty($theme['date']) ? "<tr><td style='vertical-align:top; width:24%'><b>".TPVLAN_6."</b>:</td><td style='vertical-align:top'>".$theme['date']."</td></tr>" : "";
		$text .= $compat ? "<tr><td style='vertical-align:top; width:24%'><b>".TPVLAN_57."</b>:</td><td style='vertical-align:top'>".$compat."</td></tr>" : "";

		$text .= !empty($description) ? "<tr><td style='vertical-align:top; width:24%'><b>".LAN_DESCRIPTION."</b>:</td><td style='vertical-align:top'>".$description."</td></tr>" : "";


	//	$text .= "<tr><td style='vertical-align:top; width:24%'><b>".TPVLAN_49."</b>:</td>
	//		<td style='vertical-align:top'>XHTML ";
	//	$text .= ($theme['xhtmlcompliant']) ? ADMIN_TRUE_ICON : ADMIN_FALSE_ICON;
	//	$text .= "  &nbsp;&nbsp;  CSS ";
	//	$text .= ($theme['csscompliant']) ? ADMIN_TRUE_ICON : ADMIN_FALSE_ICON;
	//	$text .= "</td></tr>";
		
		if(!empty($theme['category']))
		{
			$text .= "<tr><td><b>".LAN_CATEGORY."</b></td><td>".$theme['category']."</td></tr>";			
		}
		
		if(is_dir(e_THEME.$theme['path']."/.git"))
		{
			$text .= "<tr><td><b>Developer</b></td>
				<td >".$this->frm->admin_button('git_pull', $theme['path'], 'primary', e107::getParser()->toGlyph('fa-refresh'). "Git Sync")."</td></tr>";
		}
	
		$itext = '';

		if(!empty($theme['layouts']))
		{
			$itext .= "<tr>
					<td style='vertical-align:top; width:24%'><b>".TPVLAN_50."</b>:</td>
					<td class='well' style='vertical-align:top'>
					<table class='table table-striped table-bordered' style='margin-left:0px;margin-right:auto' >
						<tr>";
		//	$itext .= ($mode == 1) ? "<td class='fcaption' style='text-align:center;vertical-align:top;'>".TPVLAN_55."</td>" : "";
			$itext .= "
							<th class='fcaption'>".LAN_TITLE."</th>
							<th class='fcaption'>".TPVLAN_78."</th>
							<th class='fcaption' style='text-align:center;width:100px'>".TPVLAN_54."</th>
						</tr>\n";
			
			foreach ($theme['layouts'] as $key=>$val)
			{
				$itext .= "
				<tr>";
			/*	if($mode == 1)
				{
					if(!$pref['sitetheme_deflayout'])
					{
						$pref['sitetheme_deflayout'] = ($val['@attributes']['default'] == 'true') ? $key : "";
						//	echo "------------- NODEFAULT";
					}
					$itext .= "
	                <td style='vertical-align:top width:auto;text-align:center'>
						<input type='radio' name='layout_default' value='{$key}' ".($pref['sitetheme_deflayout'] == $key ? " checked='checked'" : "")." />
					</td>";
				}*/
				
				$itext .= "<td style='vertical-align:top'>";
				$itext .= !empty($val['@attributes']['previewFull']) ? "<a href='".e_THEME_ABS.$theme['path']."/".$val['@attributes']['previewFull']."' >" : "";
				$itext .= $val['@attributes']['title'];
				$itext .= !empty($val['@attributes']['previewFull']) ? "</a>" : "";
				$itext .= ($pref['sitetheme_deflayout'] == $key) ? " (default)" : "";
				$itext .= "</td>
					<td style='vertical-align:top'>".varset($val['@attributes']['plugins'])."&nbsp;</td>
                    <td style='vertical-align:top;text-align:center'>";
				$itext .= !empty($val['menuPresets']) ? ADMIN_TRUE_ICON : "&nbsp;";
				$itext .= "</td>
				</tr>";
			}


			


			
			$itext .= "</table></td></tr>";
		}
		
		
	
	//	$text .= "<tr><td><b>".TPVLAN_22.": </b></td><td colspan='2'>";
	//	foreach ($theme['css'] as $val)
	//	{
	//		$text .= $val['name']."<br />";
	//	}
	//	$text .= "</td></tr>";
		
		$text .= $itext."</table>";

		$text .= e107::getForm()->close();
		
		if(count($theme['preview']))
			{
				$text .= "<div class='clearfix'>";
				foreach($theme['preview'] as $pic)
				{
					
					$picFull = (substr($pic,0,4) == 'http') ? $pic : e_THEME.$theme['path']."/".$pic;
					
					
					$text .= "<div class='col-md-6'>
						<img class='img-responsive img-fluid' src='".$picFull."' alt=\"".$theme['name']."\" />
						</div>";	
					
				}

				$text .= "</div>";
			//	$text .= "</td>
				// 		</tr>";	
				
				
		}
		
		
	//	$text .= "<div class='right'><a href='#themeInfo_".$theme['id']."' class='e-expandit'>Close</a></div>";

		if(E107_DEBUG_LEVEL > 0)
		{
		//	$text .= print_a($theme, true);
		}
	
	
		return $text;
	}
	
	function loadThemeConfig()
	{
		$mes = e107::getMessage();
		
		$newConfile = e_THEME.$this->id."/theme_config.php";
		
		$legacyConfile = e_THEME.$this->id."/".$this->id."_config.php"; // @Deprecated

		if(is_readable($newConfile))
		{
			$confile = $newConfile;
		}
		elseif(is_readable($legacyConfile))// TODO Eventually remove it. 
		{
			// NOTE:  this is debug info.. do not translate. 
			e107::getMessage()->addDebug("Deprecated Theme Config File found! Rename <b>".$this->id."_config.php.</b> to <b>theme_config.php</b> to correct this issue. .");
			$confile = $legacyConfile;		
		}
		else
		{
			return;
		}
				
		if(($this->themeConfigObj === null) )
		{
			e107::getDebug()->log("Loading : ".$confile);
			include ($confile);
			$className = 'theme_'.$this->id;

			if(class_exists('theme_config')) // new v2.1.4 theme_config is the class name.
			{
				$this->themeConfigObj = new theme_config();

				if(!$this->themeConfigObj instanceof e_theme_config)
				{
				    // debug - no need to translate.
                    e107::getMessage()->addWarning("class <b>theme_config</b> is missing 'implements e_theme_config'");
                }

				if(class_exists('theme_config_form')) // new v2.1.7
				{
					$this->themeConfigFormObj = new theme_config_form();
				}
			}
			elseif(class_exists($className)) // old way.
			{
				$this->themeConfigObj = new $className();
			}
			else
			{
				$this->themeConfigObj = false;
			}
		}
	
	}
	
	// TODO process custom theme configuration - .
	function renderThemeConfig()
	{
		
		$mes = e107::getMessage();

		$frm = ($this->themeConfigFormObj !== null) ?  $this->themeConfigFormObj : e107::getForm();

		$pref = e107::getConfig()->getPref();
		e107::getDebug()->log("Rendering Theme Config");
		
		$this->loadThemeConfig();

		$value = e107::getThemeConfig($this->id)->getPref();

		if(empty($value) && !empty($pref['sitetheme_pref']))
		{
			$value = $pref['sitetheme_pref'];
		}

		if($this->themeConfigObj)
		{
			$var = call_user_func(array(&$this->themeConfigObj, 'config'));
			$text = ''; // avoid notice

			foreach ($var as $field=>$val)
			{
				if(is_numeric($field))
				{
					$text .= "<tr><td><b>".$val['caption']."</b>:</td><td colspan='2'>".$val['html']."<div class='field-help'>".$val['help']."</div></td></tr>";
				}
				else
				{
					if(!empty($val['multilan']) && isset($value[$field][e_LANGUAGE]))
					{
						$value[$field] = varset($value[$field][e_LANGUAGE],'');
					}

					$tdClass = !empty($val['writeParms']['post']) ? 'form-inline' : '';
					$text .= "<tr><td><b>".$val['title']."</b>:</td><td class='".$tdClass."' colspan='2'>".$frm->renderElement($field, $value[$field], $val)."<div class='field-help'>".varset($val['help'])."</div></td></tr>";
				}
			}

			return $text;
		}
	
	}



	
	function renderThemeHelp()
	{
		if($this->themeConfigObj)
		{
			return call_user_func(array(&$this->themeConfigObj, 'help'));
		}
	}

	
	function setThemeConfig()
	{
		$this->loadThemeConfig();

		if($this->themeConfigObj)
		{
			$name = get_class($this->themeConfigObj);

			if($name === 'theme_config') // v2.1.4 - don't use process() method.
			{
				$pref = e107::getThemeConfig();

				$theme_pref = array();

				$fields = call_user_func(array(&$this->themeConfigObj, 'config'));

				foreach($fields as $field=>$data)
				{
					$theme_pref[$field] = $_POST[$field];
				}

				if($pref->setPref($theme_pref)->save(true,true,false))
				{
					$siteThemePref = e107::getConfig()->get('sitetheme_pref');
					if(!empty($siteThemePref))
					{
						e107::getConfig()->set('sitetheme_pref',null)->save(false,true,false); // remove old theme pref
					}
				}

			//	if($pref->dataHasChanged())
				{

					e107::getCache()->clearAll('library'); // Need to clear cache in order to refresh library information.
				}

				return true;
			}

			e107::getCache()->clearAll('library');
			return call_user_func(array(&$this->themeConfigObj, 'process')); //pre v2.1.4
		}
	}
	
	/**
		 mode = 0 :: normal
		 mode = 1 :: selected site theme
		 mode = 2 :: selected admin theme
	*/
	function renderTheme($mode = 0, $theme)
	{
		$ns = e107::getRender();
		$pref = e107::getPref();
		$frm = e107::getForm();
		$tp = e107::getParser();


		
		$author 		= ($theme['email'] ? "<a href='mailto:".$theme['email']."' title='".$theme['email']."'>".$theme['author']."</a>" : $theme['author']);
		$website 		= ($theme['website'] ? "<a href='".$theme['website']."' rel='external'>".$theme['website']."</a>" : "");
	//	$preview 		= "<a href='".e_BASE."news.php?themepreview.".$theme['id']."' title='".TPVLAN_9."' >".($theme['preview'] ? "<img src='".$theme['preview']."' style='border: 1px solid #000;width:200px' alt='' />" : "<img src='".e_IMAGE_ABS."admin_images/nopreview.png' title='".TPVLAN_12."' alt='' />")."</a>";
		$main_icon 		= ($pref['sitetheme'] != $theme['path']) ? "<button class='btn btn-default btn-secondary btn-small btn-sm btn-inverse' type='submit'   name='selectmain[".$theme['id']."]' alt=\"".TPVLAN_10."\" title=\"".TPVLAN_10."\" >".$tp->toGlyph('fa-home',array('size'=>'2x'))."</button>" : "<button class='btn btn-small btn-default btn-secondary btn-sm btn-inverse' type='button'>".$tp->toGlyph('fa-check',array('size'=>'2x'))."</button>";
	//	$info_icon 		= "<a data-toggle='modal' data-target='".e_SELF."' href='#themeInfo_".$theme['id']."' class='e-tip' title='".TPVLAN_7."'><img src='".e_IMAGE_ABS."admin_images/info_32.png' alt='' class='icon S32' /></a>";
		$info_icon 		= "<a class='btn btn-default btn-secondary btn-small btn-sm btn-inverse e-modal'  data-modal-caption=\"".$theme['name']." ".$theme['version']."\" href='".e_SELF."?mode=".varset($_GET['mode'])."&id=".$theme['path']."&action=info'  title='".TPVLAN_7."'>".$tp->toGlyph('fa-info-circle',array('size'=>'2x'))."</a>";
//		$preview_icon 	= "<a title='Preview : ".$theme['name']."' rel='external' class='e-dialog' href='".e_BASE."index.php?themepreview.".$theme['id']."'>".E_32_SEARCH."</a>";
		$admin_icon 	= ($pref['admintheme'] != $theme['path'] ) ? "<button class='btn btn-default btn-secondary btn-small btn-sm btn-inverse' type='submit'   name='selectadmin[".$theme['id']."]' alt=\"".TPVLAN_32."\" title=\"".TPVLAN_32."\" >".$tp->toGlyph('fa-gears',array('size'=>'2x'))."</button>" : "<button class='btn btn-small btn-default btn-secondary btn-sm btn-inverse' type='button'>".$tp->toGlyph('fa-check',array('size'=>'2x'))."</button>";


		$price 			= '';


		if(substr($theme['thumbnail'],0,4) == 'http')
		{
			$thumbPath = $theme['thumbnail'];	
			$previewPath = $theme['preview'][0];	
		}
		elseif(!empty($theme['thumbnail']))
		{
			$thumbPath = e_THEME.$theme['path'] ."/".$theme['thumbnail'];
			$previewPath = e_THEME.$theme['path'] ."/".$theme['thumbnail'];
			$class = 'admin-theme-preview';
		}
		else 
		{
			$thumbPath = e_IMAGE_ABS."admin_images/nopreview.png";
			$previewPath = e_BASE."index.php?themepreview.".$theme['id'];
			$class = 'admin-theme-nopreview';
		}
		
		$thumbnail = "<img class='".$class."' src='".$thumbPath."' style='max-width:100%'  alt='' />";
		

		if($_GET['mode'] == 'online')
		{
			$srcData = array(
				'id'    => $theme['id'],
				'url'   => $theme['url'],
				'mode'  => $theme['mode'],
				'price' => $theme['price']
			);


			e107::getSession()->set('thememanager/online/'.$theme['id'], $theme);

			$d = http_build_query($srcData,false,'&');
			$base64 = base64_encode($d);
		//	e107::getDebug()->log($theme['name'].': '.strlen($base64));
			$url = e_SELF."?src=".$base64;
			$id = $frm->name2id($theme['name']);
			$LAN_DOWNLOAD = ($theme['price'] > 0) ? LAN_PURCHASE."/".LAN_DOWNLOAD : LAN_DOWNLOAD;
			
			/*
			if($this->mp->hasAuthKey())
			{
				$action = 'download';	
				$caption = "Downloading ".$theme['name']." ".$theme['version'];
			}
			else
			{
				$action = 'login';
				$caption = "Please login to your e107.org account to proceed..";
			}
			*/
			
			$downloadUrl = e_SELF.'?mode=download&src='.base64_encode($d);//$url.'&amp;action=download';
			$infoUrl = $url.'&amp;action=info';
			
			$viewUrl = $theme['url'];
			
			//$main_icon = "<a data-src='".$downloadUrl."' href='{$downloadUrl}' data-target='{$id}' data-loading='".e_IMAGE."/generic/loading_32.gif' class='-e-ajax' title='".$LAN_DOWNLOAD."' ><img class='top' src='".e_IMAGE_ABS."icons/download_32.png' alt=''  /></a> ";		
		//	$main_icon = "<a data-toggle='modal' data-modal-caption=\"".$caption."\" href='{$downloadUrl}' data-cache='false' data-target='#uiModal' title='".$LAN_DOWNLOAD."' >".$tp->toGlyph('download',array('size'=>'2x'))."</a> ";
			
			$modalCaption = (empty($theme['price'])) ? ' '.LAN_DOWNLOADING.' '.$theme['name']." ".$theme['version'] :' '.LAN_PURCHASE.' '.$theme['name']." ".$theme['version'];
			$main_icon = "<a class='e-modal btn-default btn-secondary btn btn-sm btn-small btn-inverse' data-modal-caption=\"".$modalCaption."\" rel='external'  href='{$downloadUrl}' data-cache='false' title='".$LAN_DOWNLOAD."' >".$tp->toGlyph('fa-download',array('size'=>'2x'))."</a>";
		
			
		
			// Temporary Pop-up version. 
		//	$main_icon = "<a class='e-modal btn btn-small btn-inverse' data-modal-caption=\"".$theme['name']." ".$theme['version']."\" rel='external'  href='{$viewUrl}' data-cache='false' title='".$LAN_DOWNLOAD."' >".$tp->toGlyph('download',array('size'=>'2x'))."</a> ";
		
			
			$info_icon 	= "<a class='btn btn-default btn-secondary btn-sm btn-small btn-inverse e-modal' data-toggle='modal' data-modal-caption=\"".$theme['name']." ".$theme['version']."\" href='".$infoUrl."' data-cache='false'  title='".TPVLAN_7."'>".$tp->toGlyph('fa-info-circle',array('size'=>'2x'))."</a>";
			
			if($theme['livedemo'])
			{
				$previewPath = $theme['livedemo'];	
			}

			$price = (!empty($theme['price'])) ? "<span class='label label-primary pull-right'>".$theme['price']."</span>" : "<span class='label label-success pull-right'>".TPVLAN_76."</span>";
	
		}
		
		$preview_icon 	= "<a class='e-modal btn btn-default btn-secondary btn-sm btn-small btn-inverse' title=' ".TPVLAN_70." ".$theme['name']."' data-modal-caption=\"".$theme['name']." ".$theme['version']."\" rel='external'  href='".$previewPath."'>".$tp->toGlyph('fa-search',array('size'=>'2x'))."</a>";
		
		
		if(!in_array($theme['path'], $this->approvedAdminThemes))
		{
			$admin_icon = "";	
		}
		
		if($theme['name'] == 'bootstrap')
		{
		//	print_a($theme);	
		}
	//	
	//	$thumbPath = (substr($theme['thumbnail'],0,4) == 'http') ? $theme['thumbnail'] : e_THEME.$theme['path'] ."/".$theme['preview'][0];
	//	$thumbnail = "<a href='".e_BASE."news.php?themepreview.".$theme['id']."' title='".TPVLAN_9."' >";
		
	
	
	//	$thumbnail .= "</a>";
		
		// Choose a Theme to Install.
		
		
		
		
		if($mode == self::RENDER_THUMBNAIL)
		{
			// styles NEED to be put into style.css
			/*
			if($pref['sitetheme'] == $theme['path'])
			{
				$borderStyle = "admin-theme-cell-site";		
			}
			elseif($pref['admintheme'] == $theme['path'])
			{
				$borderStyle = "admin-theme-cell-admin";	
			}
			else 
			{
				$borderStyle = "admin-theme-cell-default";
			}
		*/
			$borderStyle = 'well';
			
			
			$text = "
				<div class='f-left block-text admin-theme-cell ".$borderStyle."'>
					<div class='well admin-theme-thumb'>".$thumbnail."</div>
					<div id='".$frm->name2id($theme['name'])."' class='admin-theme-options'>".$main_icon.$admin_icon.$info_icon.$preview_icon."</div>
					<div class='admin-theme-title'><small>".strip_tags($theme['name'])." ".$theme['version']."</small>
					".$price."
					</div>	
				</div>";

			return $text;
		}
		
		$this->id = $theme['path'];
		
		// load customn theme configuration fields.
		$this->loadThemeConfig();

		$text = '';
	//	$text .= "<h2 class='caption'>".$theme['name']."</h2>";

		$text .= "
        
        <ul class='nav nav-tabs'>
        <li class='active'><a data-toggle='tab' href='#core-thememanager-configure'>".LAN_CONFIGURE."</a></li>";
		

		if($this->themeConfigObj && call_user_func(array(&$this->themeConfigObj, 'config')) && $mode == self::RENDER_SITEPREFS)
		{
			$text .= "<li><a data-toggle='tab' href='#core-thememanager-customconfig'>".LAN_PREFS."</a></li>\n";
		}
		
		if($this->themeConfigObj && call_user_func(array(&$this->themeConfigObj, 'help')))
		{
			$text .= "<li><a data-toggle='tab' href='#core-thememanager-help'>".LAN_HELP."</a></li>\n";
		}
		
		$text .= "</ul>
		<div class='tab-content'>
			<div class='tab-pane active'  id='core-thememanager-configure'>
		        <table class='table adminform'>
		        	<colgroup>
		        		<col class='col-label' />
		        		<col class='col-control' />
						<col class='col-control' />
		        	</colgroup>
					<tr>
						<td><b>".TPVLAN_11."</b></td>
						<td>".$theme['version']."</td>
						<td class='well center middle' rowspan='9' style='text-align:center; vertical-align:middle;width:25%'>".$thumbnail."</td>
					</tr>";
		
					$text .= "<tr><td style='vertical-align:top; width:25%'><b>".LAN_AUTHOR."</b>:</td><td style='vertical-align:top'>".$author."</td></tr>";
					$text .= "<tr><td style='vertical-align:top; width:25%'><b>".TPVLAN_5."</b>:</td><td style='vertical-align:top'>".$website."</td></tr>";
					$text .= "<tr><td style='vertical-align:top; width:25%'><b>".TPVLAN_6."</b>:</td><td style='vertical-align:top'>".$theme['date']."</td></tr>";
					
					$text .= "<tr><td style='vertical-align:top; width:25%'><b>".TPVLAN_7."</b>:</td><td style='vertical-align:top'>".strip_tags($theme['info'],'b')."</td></tr>";
					$text .= "<tr><td style='vertical-align:top; width:25%'><b>".LAN_CATEGORY."</b>:</td><td style='vertical-align:top'>".$theme['category']."</td></tr>";
					$text .= "<tr><td style='vertical-align:top; width:25%'><b>".LAN_FOLDER."</b>:</td><td style='vertical-align:top'>".$theme['path']."</td></tr>";

				//		$text .= "<tr><td style='vertical-align:top; width:25%'><b>Price</b>:</td><td style='vertical-align:top'>".$price."</td></tr>";
					$text .= "<tr><td style='vertical-align:top; width:25%'><b>".TPVLAN_49."</b>:</td><td style='vertical-align:top'>";
					$text .= ($theme['xhtmlcompliant']) ? "W3C XHTML ".$theme['xhtmlcompliant'] : TPVLAN_71;
					$text .= ($theme['csscompliant']) ? " &amp; CSS ".$theme['csscompliant'] : "";
					$text .= "</td></tr>";


					if(is_dir(e_THEME.$this->id."/.git"))
					{
						$text .= "<tr><td><b>Developer</b></td>
							<td >".$this->frm->admin_button('git_pull', $this->id, 'primary', $tp->toGlyph('fa-refresh'). "Git Sync")."</td></tr>";
					}

		
					// site theme..
					if($mode == self::RENDER_SITEPREFS)
					{
						
						$text .= "
							<tr>
			                    <td style='vertical-align:top; width:24%;'><b>".TPVLAN_53."</b></td>
								<td style='vertical-align:top width:auto;'>";

							if(!empty($theme['plugins']['plugin']))
							{
								$text .= $this->renderPlugins($theme['plugins']['plugin']);
							}
						
						$text .= "&nbsp;</td>
							</tr>";
						
						/*$text .= "
							<tr>
			                    <td style='vertical-align:top; width:24%;'><b>".TPVLAN_30."</b></td>
								<td colspan='2' style='vertical-align:top width:auto;'>
								<input type='radio' name='image_preload' value='1'".($pref['image_preload'] ? " checked='checked'" : "")." /> ".TPVLAN_28."&nbsp;&nbsp;
								<input type='radio' name='image_preload' value='0'".(!$pref['image_preload'] ? " checked='checked'" : "")." /> ".TPVLAN_29."
								</td>
							</tr>";*/

						
						$itext = "<tr>
								<td style='vertical-align:top; width:24%'><b>".TPVLAN_50."</b>:</td>
								<td colspan='2' style='vertical-align:top'>
			                    <table class='table table-bordered table-striped'>
			                      	<colgroup>
			                      		<col class='col-tm-layout-default' style='width:10%' />
			                      		<col class='col-tm-layout-name' style='width:40%' />
										<col class='col-tm-layout-visibility' style='width:30%' />
										<col class='col-tm-layout-preset' style='width:20%' />
			                      	</colgroup>
									<tr>";
						$itext .= ($mode == self::RENDER_SITEPREFS) ? "<th class='center top'>".TPVLAN_55."</th>" : "";
						$itext .= "
										<th>".TPVLAN_52."</th>
										<th>".TPVLAN_56."&nbsp;<a href='#' class='e-tip' title=\"".TPVLAN_96."\">".ADMIN_INFO_ICON."</a></th>
										<th class='text-right' style='text-align:right'>".TPVLAN_54."</th>
			
									</tr>\n";
			
						
						foreach ($theme['layouts'] as $key=>$val)
						{
							$itext .= "
										<tr>";
							if($mode == self::RENDER_SITEPREFS)
							{
								if(!$pref['sitetheme_deflayout'])
								{
									$pref['sitetheme_deflayout'] = ($val['@attributes']['default'] == 'true') ? $key : "";
								}
								$itext .= "<td class='center'>\n";
								
								$itext .= "<input id='".$frm->name2id($key)."' type='radio' name='layout_default' value='{$key}' ".($pref['sitetheme_deflayout'] == $key ? " checked='checked'" : "")." />
											</td>";
							}
							
							$itext .= "<td style='vertical-align:top'><label for='".$frm->name2id($key)."'>";
						//	$itext .= ($val['@attributes']['previewFull']) ? "<a href='".e_THEME_ABS.$theme['path']."/".$val['@attributes']['previewFull']."' >" : "";
							$itext .= $val['@attributes']['title']."</label><div class='field-help'>".$key."</div>"; 
						//	$itext .= ($val['@attributes']['previewFull']) ? "</a>" : "";
							
							$custompage_count = (isset($pref['sitetheme_custompages'][$key])) ? " [".count($pref['sitetheme_custompages'][$key])."]" : "";
							$custompage_diz = "";
							$count = 1;
							if(isset($pref['sitetheme_custompages'][$key]) && count($pref['sitetheme_custompages'][$key]) > 0)
							{
								foreach ($pref['sitetheme_custompages'][$key] as $cp)
								{
									$custompage_diz .= "<a href='#element-to-be-shown-{$key}' class='btn btn-default btn-secondary btn-xs btn-mini e-expandit'>".trim($cp)."</a>&nbsp;";
									if($count > 4)
									{
										$custompage_diz .= "...";
									break;
									}
									$count++;
								}
							}
							else
							{
								$custompage_diz = "<a href='#element-to-be-shown-{$key}' class='e-tip btn btn-xs btn-default btn-secondary btn-mini e-expandit'>".LAN_NONE."</a> ";
							}
			
							
							$itext .= "</td>
											<td style='vertical-align:top'>";
							// Default

							// issue #3663: 1. custompages are "deleted" for the current selected layout
							// issue #3663: 2. custompages of the selected layout are not editable

							//if($pref['sitetheme_deflayout'] != $key)
							//if(isset($pref['sitetheme_custompages'][$key]))
							//{
								$itext .= $custompage_diz."<div class='e-hideme' id='element-to-be-shown-{$key}'>
										<textarea style='width:97%' rows='6' placeholder='usersettings.php' cols='20' name='custompages[".$key."]' >".(isset($pref['sitetheme_custompages'][$key]) ? implode("\n", $pref['sitetheme_custompages'][$key]) : "")."</textarea>";



								//TODO Later.
								if(e_DEBUG === true)
								{
									$itext .= "<small>(Not functional yet)</small>";
									$itext .= e107::getForm()->userclass('layoutUserclass['.$key.']',null, null, array('options'=>'public,member,admin,classes,no-excludes','size'=>'xxlarge'));
								}

								$itext .= "
								</div>\n";
							//}
							//else
							//{
							//	$itext .= TPVLAN_55;
							//}


							
							$itext .= "</td>";


							
							$itext .= "<td>";
							
							if(varset($val['menuPresets'])) 
							{
								$itext .= $this->renderPresets($key);
							}
							
							
							$itext .= "</td>
			
										</tr>";
						}



						
						$itext .= "</table></td></tr>";
					}

		
		//		$itext .= !$mode ? "<tr><td style='vertical-align:top;width:24%'><b>".TPVLAN_8."</b>:</td><td style='vertical-align:top'>".$previewbutton.$selectmainbutton.$selectadminbutton."</td></tr>" : "";
		
					if($mode == self::RENDER_ADMINPREFS)
					{
						
						$astext = "";
						$file = e107::getFile();
						
						$adminstyles = $file->get_files(e_ADMIN."includes");
						
						$astext = "\n<select id='mode2' name='adminstyle' class='form-control input-medium'>\n";
						
						foreach ($adminstyles as $as)
						{
							$style = str_replace(".php", "", $as['fname']);
							$astext .= "<option value='{$style}'".($pref['adminstyle'] == $style ? " selected='selected'" : "").">".$style."</option>\n";
						}
						$astext .= "</select>";
						
						$text .= "
						<tr>
							<td><b>".TPVLAN_41.":</b></td>
							<td>".$astext."</td>
						</tr>
						\n";

						$text .= "
						<tr>
							<td><b>" . TPVLAN_89 . "</b></td>
							<td colspan='2'>
								<div class='checkbox'>
								<label class='checkbox'>
									" . $frm->checkbox('adminpref', 1, (varset($pref['adminpref'], 0) == 1)) . "
								</label>
								</div>
							</td>
						</tr>
						\n";
					}

		
					$text .= varset($itext, '');


					$theme['css'] = $this->filterStylesheets($mode, $theme);

					
					if(array_key_exists("multipleStylesheets", $theme) && $mode && !empty($theme['css']))
					{
						$pLabel = (self::RENDER_ADMINPREFS === $mode) ? TPVLAN_95 : TPVLAN_22;

						$text .= "
							<tr><td style='vertical-align:top;'><b>".$pLabel.":</b></td>
							<td colspan='2' style='vertical-align:top'>
							<table class='table table-bordered table-striped' >
							<tr>
			                	<td class='center' style='width:10%'>".TPVLAN_93."</td>
						  		<td style='width:20%'>".TPVLAN_52."</td>
								<td class='left'>".TPVLAN_7."</td>
							</tr>";
			
						foreach ($theme['css'] as $css)
						{
								
							$text2 = "";

							switch($mode)
							{
								case self::RENDER_ADMINPREFS: // admin mode.
									$for = $frm->name2id("admincss-".$css['name']);
									$text2 = "<td class='center'>";
									$text2 .= $frm->radio('admincss', $css['name'], vartrue($pref['admincss'])== $css['name'], array('id'=>$for));
									$text2 .= "</td>";
									$text2 .= "<td><label for='".$for."' title=\"".$css['name']."\">".$css['info']."</label></td>";
									$text2 .= "<td>".($css['description'] ? $css['description'] : '')."</td>\n";
									break;

								case self::RENDER_SITEPREFS: // front 'sitetheme' mode.

									$text2 = "
									<td class='center'>
									<input id='".$frm->name2id($css['name'])."' type='radio' name='themecss' value='".$css['name']."' ".($pref['themecss'] == $css['name'] || (!$pref['themecss'] && $css['name'] == "style.css") ? " checked='checked'" : "")." />
									</td>
									<td><label for='".$frm->name2id($css['name'])."' >".$css['name']."</lable></td>
									<td>".($css['info'] ? $css['info'] : ($css['name'] == "style.css" ? TPVLAN_23 : TPVLAN_24))."</td>\n";
								break;

							}

							$text .= ($text2) ? "<tr>".$text2."</tr>" : "";
						
						}
						
						$text .= "</table></td></tr>";
					}


					$text .= "</table>


			   		<div class='center buttons-bar'>";
			
					if($mode == self::RENDER_ADMINPREFS) // admin
					{
						$mainid = "selectmain[".$theme['id']."]";
						$text .= $this->frm->admin_button('submit_adminstyle', TPVLAN_35, 'update');
						//$text .= $this->frm->admin_button($mainid, TPVLAN_10, 'other');
					
					}
					else // main
					{
						$adminid = "selectadmin[".$theme['id']."]";
						$text .= $this->frm->admin_button('submit_style', TPVLAN_35, 'update');
						//$text .= $this->frm->admin_button($adminid, TPVLAN_32, 'other');
					}
					
					$text .= "<input type='hidden' name='curTheme' value='".$theme['path']."' />";
			
					$text .= "</div>
			</div>
			
			 <div class='tab-pane' id='core-thememanager-help'>".$this->renderThemeHelp()."</div>
			 
			 <div class='tab-pane' id='core-thememanager-customconfig'>
			 	<table class='table adminform'>
		        	<colgroup>
		        		<col class='col-label' />
		        		<col class='col-control' />
						<col class='col-control' />
		        	</colgroup>
	
					".$this->renderThemeConfig()."

				</table>

				<div class='center buttons-bar'>";
		
				if($mode == self::RENDER_ADMINPREFS) // admin
				{
					$mainid = "selectmain[".$theme['id']."]";
					$text .= $this->frm->admin_button('submit_adminstyle', TPVLAN_35, 'update');
					//$text .= $this->frm->admin_button($mainid, TPVLAN_10, 'other');
				
				}
				else // main
				{
					$adminid = "selectadmin[".$theme['id']."]";
					$text .= $this->frm->admin_button('submit_style', TPVLAN_35, 'update');
					//$text .= $this->frm->admin_button($adminid, TPVLAN_32, 'other');
				}
				
				$text .= "<input type='hidden' name='curTheme' value='".$theme['path']."' />";
		
				$text .= "</div>
			</div>
        </div>
		\n";
		
		return $text;
	}



	private function filterStylesheets($mode, $theme)
	{

		$remove = array();
		$detected = array();

		if($mode == self::RENDER_SITEPREFS)
		{
			foreach($theme['css'] as $k=>$v) // check if wildcard is present.
			{
				if($v['name'] == '*')
				{
					foreach($theme['files'] as $val) // get wildcard list of css files.
					{
						if(substr($val,-4) == '.css' && substr($val, 0, 6) != "admin_")
						{
							$detected[$val] = array('name'=>$val, 'info'=>'User-added Stylesheet', 'nonadmin'=>1);
						}
					}
					break;
				}
			}
		}




		foreach($theme['css'] as $k=>$vl) // as defined.
		{
			if(!empty($detected[$vl['name']])) // remove any detected files which are listed
			{
				unset($detected[$vl['name']]);
			}


				 // frontend
				if($mode === self::RENDER_SITEPREFS)
				{

					if(substr($vl['name'], 0, 6) == "admin_")
					{
						$remove[$k] = $vl['name'];
					}

					if($vl['scope'] == 'admin')
					{
						$remove[$k] = $vl['name'];
					}

					if($vl['name'] == '*' )
					{
						$remove[$k] = $vl['name'];

						$wildcard = true;
						continue;
					}

				}

				if($mode === self::RENDER_ADMINPREFS)
				{

					if($vl['name'] == "style.css" || empty($vl['info'])) // Hide the admin css unless it has a header. eg. /* info: Default stylesheet */
					{
						$remove[$k] = $vl['name'];
					}

					if($vl['name'] == '*' )
					{
						$remove[$k] = $vl['name'];
					}

					if($vl['scope'] === 'front')
					{
						$remove[$k] = $vl['name'];
					}

					if(!empty($vl['nonadmin']))
					{
						$remove[$k] = $vl['name'];
					}
				}




		}

		foreach($remove as $k=>$file)
		{
			unset($theme['css'][$k]);
		//	unset($detected[$file]);
		}

		foreach($detected as $k=>$v)
		{
			$theme['css'][] = $v;
		}

	//	print_a($detected);
	//	print_a($remove);

		return $theme['css'];

	}


	function renderPresets($key)
	{
		require_once (e_HANDLER."menumanager_class.php");
		$frm = e107::getForm();
		
		
		$men = new e_menuManager();
		$men->curLayout = $key;
		$preset = $men->getMenuPreset();
		
// 		print_a($preset); 
		//TODO LAN
		$text = "<div class='btn-group pull-right'>".$frm->admin_button("setMenuPreset[".$key."]", TPVLAN_73,'other');
		$text .= '<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
		<span class="caret"></span>
		</button>
		<ul class="dropdown-menu col-selection">
		<!-- dropdown menu links -->
		<li class="dropdown-header">'.TPVLAN_74.'</li>
		';
										
		foreach($preset as $val)
		{
			$text .= "<li><a title='".$val['menu_name']."'>".str_replace("_menu","",$val['menu_name'])."</a></li>";	
			
		}					

		$text .= "</ul></div>";
		return $text;
		
	}








	
	function renderPlugins($pluginOpts)
	{
		global $frm,$sql;
		
		// if there is 1 entry, then it's not the same array.
	//	$tmp = (varset($pluginOpts['plugin'][1])) ? $pluginOpts['plugin'] : $pluginOpts;
		$text = "";


		
		foreach ($pluginOpts as $p)
		{
			$plug = trim($p['@attributes']['name']);
			
			if(e107::isInstalled($plug))
			{
				$text .= $plug." ".ADMIN_TRUE_ICON;
			}
			else
			{
				//	echo $plug;
				if($sql->db_Select("plugin", "plugin_id", " plugin_path = '".$plug."' LIMIT 1 "))
				{
					$row = $sql->db_Fetch();
					$name = "installplugin[".$row['plugin_id']."]";
					$text .= $this->frm->admin_button($name, ADLAN_121." ".$plug."", 'delete');
				}
				else
				{
					$text .= (varset($p['@attributes']['url']) && ($p['@attributes']['url'] != 'core')) ? "<a rel='external' href='".$p['@attributes']['url']."'>".$plug."</a> " : "<i>".$plug."</i>";
					$text .= ADMIN_FALSE_ICON;
				}
			
			}
			$text .= "&nbsp;&nbsp;&nbsp;";
		}
		
		return $text;
	}
	
	function refreshPage($page = e_QUERY )
	{
		header("Location: ".e_SELF."?".$page);
		exit;
	}
	
	function themePreview()
	{
		echo "<script type='text/javascript'>document.location.href='".e_BASE."index.php?themepreview.".$this->id."'</script>\n";
		exit;
	}
	
	static function showPreview()
	{
		e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_theme.php");
		$text = "<br /><div class='indent'>".TPVLAN_1.".</div><br />";

		$srch = array(
			'{PREVIEWTHEMENAME}' => PREVIEWTHEMENAME,
			'{e_ADMIN}' => e_ADMIN
		);

		$text = str_replace(array_keys($srch),$srch,$text);

		global $ns;
		$ns->tablerender(TPVLAN_2, $text);
	}

	
	/**
	 * Set Theme as Main Theme.
	 *
	 * @param string $name [optional] name (folder) of the theme to set.
	 * @return boolean TRUE on success, FALSE otherwise
	 */
	function setTheme($name = '', $contentCheck = true)
	{
		$core = e107::getConfig('core');
		$sql = e107::getDb();
		$mes = e107::getMessage();
		
		$themeArray = $this->getThemes("id");
		
		$name = ($name) ? $name : vartrue($themeArray[$this->id]);
		$layout = $pref['sitetheme_layouts'] = is_array($this->themeArray[$name]['layouts']) ? $this->themeArray[$name]['layouts'] : array();
		$deflayout = $this->findDefault($name);
		$customPages = $this->themeArray[$name]['custompages'];
		$version = $this->themeArray[$name]['version'];
		$glyphs = $this->themeArray[$name]['glyphs'];
		
		$core->set('sitetheme', $name);
		$core->set('themecss', 'style.css');
		$core->set('sitetheme_layouts', $layout);
		$core->set('sitetheme_deflayout', $deflayout);
		$core->set('sitetheme_custompages', $customPages);
		$core->set('sitetheme_glyphicons', $glyphs);
		
		$core->set('sitetheme_version', $version);
				
		if(!empty($this->themeArray[$name]['preferences']))
		{
			$themePrefs = $this->themeArray[$name]['preferences'];

			e107::getMessage()->addDebug("ThemePrefs found in theme.xml");

			$this->id = $name;
			$this->loadThemeConfig();
			$className = get_class($this->themeConfigObj);

			if($className === 'theme_config') // new way.  2.1.4
			{
				$themeConfig = e107::getThemeConfig($name);

				e107::getMessage()->addDebug("Saving theme prefs to their own row: ".print_r($themePrefs,true));

				foreach($themePrefs as $key=>$val)
				{
					$themeConfig->add($key,$val);
				}

				$themeConfig->save(false,true,false);


			}
			else // old way.
			{
				e107::getMessage()->addDebug("Saving theme prefs to sitetheme_ref");
				 $core->set('sitetheme_pref', $this->themeArray[$name]['preferences']);
			}



		}

		if($contentCheck === true)
		{
			$sql->delete("menus", "menu_layout !='' ");
		}
		
		e107::getCache()->clear();
		e107::getCache()->clearAll('js');
		e107::getCache()->clearAll('css');
		e107::getCache()->clearAll('library');
		
		if($core->save())
		{
			$mes->addDebug("Default Layout: ".$deflayout);
			$mes->addDebug("Custom Pages: ".print_a($customPages,true));
			
			$med = e107::getMedia();
			$med->import('_common_image', e_THEME.$name, "^.*?logo.*?(\.png|\.jpeg|\.jpg|\.JPG|\.GIF|\.PNG)$");	
			$med->import('_common_image', e_THEME.$name, '', 'min-size=20000');
			
			if($contentCheck === true)
			{
				$this->installContentCheck($name);
			}

			$this->theme_adminlog('01', $name.', style.css');

			return true;
		}
		else
		{
		//	$mes->add(TPVLAN_3." <b>'".$name."'</b>", E_MESSAGE_ERROR);
			return true;
		}
	
	}


	/**
	 * @param $name
	 */
	function installContentCheck($name)
	{
		$file = e_THEME.$name."/install/install.xml";
		$frm = e107::getForm();
		$tp = e107::getParser();

		if(!is_readable($file))
		{
			return false;
		}

		$mes = e107::getMessage();

		$xmlArray = e107::getXml()->loadXMLfile($file, 'advanced');

		$text = "
		<form action='".e_SELF."' method='post'>
		<div>
		<p>".TPVLAN_58."<br />
		".$tp->toHTML(TPVLAN_59, true).":<br />
		</p>

		<ul>";

		$lng = e107::getLanguage();

		foreach($xmlArray['database']['dbTable'] as $key=>$val)
		{
			$count = count($val['item']);
			$data = array('x'=> $count, 'y' => $val['@attributes']['name']);
			$text .= "<li>".$tp->lanVars(TPVLAN_60, $data)."</li>";
		}

		$text .= "</ul>

		<p>".$tp->toHTML(TPVLAN_61, true)."</p>

		".$frm->admin_button('installContent',$name, 'warning', LAN_YES)."
		".$frm->admin_button('dismiss',0, 'cancel', LAN_NO)."
		</div>
		</form>
		";
	//	$text .= print_a($xmlArray, true);
		$mes->addInfo($text);
	}


	function installContent($name)
	{
		$mes = e107::getMessage();
		$file = e_THEME.$name."/install/install.xml";
		e107::getXml()->e107Import($file, 'replace', true, false); // Overwrite specific core pref and tables entries. 
		$mes->addSuccess(LAN_UPDATED);
	}


	/**
	 * @param $theme
	 * @return int|string
	 */
	function findDefault($theme)
	{
		if(!empty($_POST['layout_default']))
		{
			return e107::getParser()->filter($_POST['layout_default'], 'w');
		}
		
		$l = $this->themeArray[$theme];
		
		if(!$l)
		{
			$l = $this->getThemeInfo($theme);
		}

		
		if($l['layouts'])
		{
			foreach ($l['layouts'] as $key=>$val)
			{
				if(isset($val['@attributes']['default']) && ($val['@attributes']['default'] == "true"))
				{
					return $key;
				}
			}
		}
		else
		{
			return "";
		}
	}
	
	function setAdminTheme()
	{
		global $pref,$e107cache;
		
		$ns = e107::getRender();
		$mes = e107::getMessage();
		
		$themeArray = $this->getThemes("id");
		$pref['admintheme'] = $themeArray[$this->id];
		$pref['admincss'] = file_exists(e_THEME.$pref['admintheme'].'/admin_dark.css') ? 'admin_dark.css' : 'admin_light.css';
		$e107cache->clear_sys();
		
		if(save_prefs())
		{
			// Default Message
			$mes->add(TPVLAN_40." <b>'".$themeArray[$this->id]."'</b>", E_MESSAGE_SUCCESS);
			$this->theme_adminlog('02', $pref['admintheme'].', '.$pref['admincss']);
		}
		
		//	$ns->tablerender("Admin Message", "<br /><div style='text-align:center;'>".TPVLAN_40." <b>'".$themeArray[$this -> id]."'</b>.</div><br />");
		//  $this->showThemes('admin');
	}

	/**
	 * @todo add admin log
	 */
	function setStyle()
	{
		global $pref,$e107cache;
		$sql            = e107::getDb();
		$ns             = e107::getRender();
		$mes            = e107::getMessage();

		$themeCSS       = vartrue($_POST['themecss'],'style.css');
		$themeLayout    = vartrue($_POST['layout_default'], 'default');

		e107::getConfig()->setPosted('themecss',$themeCSS)->setPosted('sitetheme_deflayout', $themeLayout);

		$msg = $this->setThemeConfig();

		if($msg)
		{
			$mes->add(TPVLAN_37, E_MESSAGE_SUCCESS);
			if(is_array($msg))
				$mes->add($msg[0], $msg[1]);
		}


	}
	
	function setAdminStyle()
	{
		//TODO adminlog

		$config =  e107::getConfig();

		if(!empty($_POST['admincss']))
		{
			$config->setPosted('admincss', $_POST['admincss']);
		}

		$config->setPosted('adminstyle', $_POST['adminstyle'])
			->setPosted('adminpref', varset($_POST['adminpref'], 0))->save(true,true,false);


		e107::redirect(e_REQUEST_URI);

		/*return (e107::getConfig()->dataHasChangedFor('admincss')
			|| e107::getConfig()->dataHasChangedFor('adminstyle')
			|| e107::getConfig()->dataHasChangedFor('adminpref'));*/
	}
	
	function SetCustomPages($array)
	{
		if(!is_array($array))
		{
			return;
		}
		$newprefs = array();
		foreach ($array as $key => $newpref)
		{
			$newpref = trim(str_replace("\r\n", "\n", $newpref));
			$newprefs[$key] = array_filter(explode("\n", $newpref));
			$newprefs[$key] = array_unique($newprefs[$key]);
			
		}
		
		if(e107::getPref('sitetheme_deflayout') == 'legacyCustom')
		{
			$newprefs['legacyCustom'] = array();
		}

		//setPosted couldn't be used here - sitetheme_custompages structure is not defined
		e107::getConfig()->set('sitetheme_custompages', e107::getParser()->toDB($newprefs));
	}

	/**
	 * Set the Theme layouts, as found in theme.xml
	 */
	function setLayouts($name='')
	{
		$name = $this->id;
		$layout = is_array($this->themeArray[$name]['layouts']) ? $this->themeArray[$name]['layouts'] : array();	
		
		e107::getConfig()->set('sitetheme_layouts', $layout);
		
	}



	
	// Log event to admin log
	function theme_adminlog($msg_num = '00', $woffle = '')
	{
		if($this->noLog)
		{
			return;
		}
		global $pref,$admin_log;
		//  if (!varset($pref['admin_log_log']['admin_banlist'],0)) return;
		e107::getLog()->add('THEME_'.$msg_num, $woffle, E_LOG_INFORMATIVE, '');
	}
	
	function parse_theme_php($path)
	{
		return e_theme::parse_theme_php($path);
	}
	
	function parse_theme_xml($path)
	{
		return e_theme::parse_theme_xml($path);

	}





}





interface e_theme_config
{
	/**
	 * Triggered on theme settings submit
	 * Catch and save theme configuration
	 */
//	public function process();
	
	/**
	 * Theme configuration user interface
	 * Print out config fields
	 */
	public function config(); // only config() is absolutely required.
	
	/**
	 * Theme help tab
	 * Print out theme help content
	 */
//	public function help();
}


/**
 * Interface e_theme_render
 * @see e107_themes/bootstrap3/theme.php
 * @see e107_themes/bootstrap3/admin_theme.php
 */
interface e_theme_render
{
	public function tablestyle($caption, $text, $mode='', $data=array());

}


/**
* Interface e_theme_library
*//*
interface e_theme_library
{
	public function config();
}*/
