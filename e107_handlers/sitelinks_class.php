<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }
e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_sitelinks.php');


/**
 * Legacy Navigation class.
 * Class sitelinks
 */
class sitelinks
{
	var $eLinkList = array();
	var $eSubLinkLevel = 0;
	var $sefList = array();

	const LINK_DISPLAY_FLAT     = 1;
	const LINK_DISPLAY_MENU     = 2;
	const LINK_DISPLAY_OTHER    = 3;
	const LINK_DISPLAY_SLIDER   = 4;


	/**
	 * @param $cat
	 * @return void
	 */
	function getlinks($cat=1)
	{

		$this->eLinkList = array(); // clear the array in case getlinks is called 2x on the same page.
		$sql = e107::getDb('sqlSiteLinks');
		$ins = ($cat > 0) ? "link_category = ". (int) $cat ." AND " : "";
		$query = "SELECT * FROM #links WHERE ".$ins."  ((link_class >= 0 AND link_class IN (".USERCLASS_LIST.")) OR (link_class < 0 AND ABS(link_class) NOT IN (".USERCLASS_LIST.")) ) ORDER BY link_order ASC";
		if($sql->gen($query))
		{
			while ($row = $sql->fetch())
			{

				
				//	if (substr($row['link_name'], 0, 8) == 'submenu.'){
				//		$tmp=explode('.', $row['link_name'], 3);
				//		$this->eLinkList[$tmp[1]][]=$row;
				if (isset($row['link_parent']) && $row['link_parent'] != 0)
				{
					$this->eLinkList['sub_'.$row['link_parent']][]=$row;

				}
				else
				{
					$this->eLinkList['head_menu'][] = $row;
					if(!empty($row['link_function']))
					{
						$parm = false;
						list($path,$method) = explode("::",$row['link_function']);
						
						if(strpos($method,"("))
						{
							list($method,$prm) = explode("(",$method);
							$parm = rtrim($prm,")");	
						}
						
						if(file_exists(e_PLUGIN.$path."/e_sitelink.php") && include_once(e_PLUGIN.$path."/e_sitelink.php"))
						{
							$class = $path."_sitelink";
							$sublinkArray = e107::callMethod($class,$method,$parm); //TODO Cache it.
							if(!empty($sublinkArray))
							{
								$this->eLinkList['sub_'.$row['link_id']] = $sublinkArray;
							}
						}

					}
				}
			}
		}

	}

	/**
	 * @return array
	 */
	function getLinkArray()
	{
		return $this->eLinkList;
	}

	/**
	 * @param $cat
	 * @param $style
	 * @param $css_class
	 * @return string|null
	 */
	function get($cat = 1, $style = null, $css_class = false)
	{
		global $pref, $ns, $e107cache, $linkstyle;
		$ns = e107::getRender();
		$pref = e107::getPref();
		$e107cache = e107::getCache();

		$usecache = (!(trim(defset('LINKSTART_HILITE')) != "" || trim(defset('LINKCLASS_HILITE')) != ""));

		if($usecache && !strpos(e_SELF, e_ADMIN) && ($data = $e107cache->retrieve('sitelinks_' . $cat . md5($linkstyle . e_PAGE . e_QUERY))))
		{
			return $data;
		}

		if(LINKDISPLAY == self::LINK_DISPLAY_SLIDER)
		{
			require_once(e_PLUGIN . 'ypslide_menu/ypslide_menu.php');
			return null;
		}

		$this->getlinks($cat);

        if (empty($this->eLinkList))  { return ''; }

		// are these defines used at all ?
		if(!defined('PRELINKTITLE'))
		{
			define('PRELINKTITLE', '');
		}
		if(!defined('PRELINKTITLE'))
		{
			define('POSTLINKTITLE', '');
		}
		// -----------------------------

		// where did link alignment go?
		if(!defined('LINKALIGN'))
		{
			define('LINKALIGN', '');
		}

		if(empty($style))
		{
			$style = array();
			$style['prelink'] = defined('PRELINK') ? PRELINK : '';
			$style['postlink'] = defined('POSTLINK') ? POSTLINK : '';
			$style['linkclass'] = defined('LINKCLASS') ? LINKCLASS : "";
			$style['linkclass_hilite'] = defined('LINKCLASS_HILITE') ? LINKCLASS_HILITE : "";
			$style['linkstart_hilite'] = defined('LINKSTART_HILITE') ? LINKSTART_HILITE : "";
			$style['linkstart'] = defined('LINKSTART') ? LINKSTART : '';
			$style['linkdisplay'] = defined('LINKDISPLAY') ? LINKDISPLAY : '';
			$style['linkend'] = defined('LINKEND') ? LINKEND : '';
			$style['linkseparator'] = defined('LINKSEPARATOR') ? LINKSEPARATOR : '';
			$style['sublinkstart'] = defined('SUBLINKSTART') ? SUBLINKSTART : '';
			$style['sublinkend'] = defined('SUBLINKEND') ? SUBLINKEND : '';
			$style['sublinkclass'] = defined('SUBLINKCLASS') ? SUBLINKCLASS : '';
		}

		if(!varset($style['linkseparator']))
		{
			$style['linkseparator'] = '';
		}

		// Sublink styles.- replacing the tree-menu.
		if(isset($style['sublinkdisplay']) || isset($style['subindent']) || isset($style['sublinkclass']) || isset($style['sublinkstart']) || isset($style['sublinkend']) || isset($style['subpostlink']))
		{
			foreach($style as $key => $val)
			{
				$aSubStyle[$key] = vartrue($style["sub" . $key]) ? $style["sub" . $key] : $style[$key];
			}
		}
		else
		{
			$style['subindent'] = "&nbsp;&nbsp;";
			$aSubStyle = $style;
		}

		$text = "\n\n\n<!-- Sitelinks ($cat) -->\n\n\n" . $style['prelink'];

		// php warnings
		if(!vartrue($this->eLinkList['head_menu']))
		{
			$this->eLinkList['head_menu'] = array();
		}

		$render_link = array();

		if($style['linkdisplay'] != self::LINK_DISPLAY_OTHER)
		{

			foreach($this->eLinkList['head_menu'] as $key => $link)
			{
				$main_linkid = "sub_" . $link['link_id'];
				$link['link_expand'] = ((isset($pref['sitelinks_expandsub']) && $pref['sitelinks_expandsub']) && empty($style['linkmainonly']) && !defined("LINKSRENDERONLYMAIN") && isset($this->eLinkList[$main_linkid]) && is_array($this->eLinkList[$main_linkid]));
				$render_link[$key] = $this->makeLink($link, '', $style, $css_class);

				if(!defined("LINKSRENDERONLYMAIN") && !isset($style['linkmainonly']))  /* if this is defined in theme.php only main links will be rendered */
				{
					$render_link[$key] .= $this->subLink($main_linkid, $aSubStyle, $css_class);
				}
			}

			if(!isset($style['linkseparator']))
			{
				$style['linkseparator'] = '';
			}

			$text .= implode($style['linkseparator'], $render_link);


			$text .= $style['postlink'];

			if($style['linkdisplay'] == self::LINK_DISPLAY_MENU)
			{
				$text = $ns->tablerender(LAN_SITELINKS_183, $text, 'sitelinks', true);
			}
		}
		else // link_DISPLAY_3
		{
			foreach($this->eLinkList['head_menu'] as $link)
			{
				if(!count($this->eLinkList['sub_' . $link['link_id']]))
				{
					$text .= $this->makeLink($link, '', $style, $css_class);
				}
				$text .= $style['postlink'];
			}

			$text = $ns->tablerender(LAN_SITELINKS_183, $text, 'sitelinks_main', true);

			foreach(array_keys($this->eLinkList) as $k)
			{
				$mnu = $style['prelink'];
				foreach($this->eLinkList[$k] as $link)
				{
					if($k !== 'head_menu')
					{
						$mnu .= $this->makeLink($link, true, $style, $css_class);
					}
				}
				$mnu .= $style['postlink'];
				$text .= $ns->tablerender($k, $mnu, 'sitelinks_sub', true);
			}
		}

		$text .= "\n\n\n<!-- end Site Links -->\n\n\n";



		if($usecache)
		{
			$e107cache->set('sitelinks_' . $cat . md5($linkstyle . e_PAGE . e_QUERY), $text);
		}

		return $text;
	}

	/**
	 * Manage Sublink Rendering
	 * @param string $main_linkid
	 * @param array $aSubStyle
	 * @param string $css_class
	 * @param int $level [optional]
	 * @return string|null
	 */
	function subLink($main_linkid,$aSubStyle=array(),$css_class='',$level=0)
	{
		global $pref;

		if(!isset($this->eLinkList[$main_linkid]) || !is_array($this->eLinkList[$main_linkid]))
		{
			return null;
		}

		$sub['link_expand'] = ((isset($pref['sitelinks_expandsub']) && $pref['sitelinks_expandsub']) && empty($style['linkmainonly']) && !defined("LINKSRENDERONLYMAIN") && isset($this->eLinkList[$main_linkid]) && is_array($this->eLinkList[$main_linkid]));
						
		foreach($this->eLinkList[$main_linkid] as $val) // check that something in the submenu is actually selected.
 		{
			if($this->hilite($val['link_url'],TRUE)== TRUE || $sub['link_expand'] == FALSE)
         	{
         		$substyle = "block"; // previously (non-W3C compliant): compact
          		break;
        	}
			else
			{
				$substyle = "none";
			}
		}

		$text = "";
		$text .= "\n\n<div id='{$main_linkid}' style='display:$substyle' class='d_sublink'>\n";

		foreach ($this->eLinkList[$main_linkid] as $sub)
		{
			$id = (!empty($sub['link_id'])) ? "sub_".$sub['link_id'] : 'sub_0';
			$sub['link_expand'] = ((isset($pref['sitelinks_expandsub']) && $pref['sitelinks_expandsub']) && empty($style['linkmainonly']) && !defined("LINKSRENDERONLYMAIN") && isset($this->eLinkList[$id]) && is_array($this->eLinkList[$id]));
			$class = "sublink-level-".($level+1);
			$class .= ($css_class) ? " ".$css_class : "";
			$class .= ($aSubStyle['sublinkclass']) ? " ".$aSubStyle['sublinkclass'] : ""; // backwards compatible
			$text .= $this->makeLink($sub, TRUE, $aSubStyle,$class );
			$text .= $this->subLink($id,$aSubStyle,$css_class,($level+1));				
		}

		$text .= "\n</div>\n\n";
		return $text;	
	}


	/**
	 * @param $linkInfo
	 * @param $submenu
	 * @param $style
	 * @param $css_class
	 * @return string
	 */
	function makeLink($linkInfo=array(), $submenu = FALSE, $style=array(), $css_class = false)
	{
		global $pref,$tp;

		// Start with an empty link
		$linkstart = $indent = $linkadd = $screentip = $href = $link_append = '';
		$highlighted = FALSE;
		
		if(!isset($style['linkstart_hilite'])) // Notice removal
		{
			$style['linkstart_hilite'] = "";	
		}
		
		if(!isset($style['linkclass_hilite']))
		{
			$style['linkclass_hilite'] = "";	
		}

		if(!empty($linkInfo['link_sefurl']) && !empty($linkInfo['link_owner']))
		{
			$linkInfo['link_url'] = e107::url($linkInfo['link_owner'],$linkInfo['link_sefurl']) ; //  $linkInfo['link_sefurl'];
		}



		// If submenu: Fix Name, Add Indentation.
		if ($submenu == true)
		{
			if(strpos($linkInfo['link_name'], 'submenu.') === 0)
			{
				$tmp = explode('.', $linkInfo['link_name'], 3);
				$linkInfo['link_name'] = $tmp[2];
			}
			$indent = ($style['linkdisplay'] != self::LINK_DISPLAY_OTHER && !empty($style['subindent'])) ? ($style['subindent']) : "";
		}

		// Convert any {e_XXX} to absolute URLs (relative ones sometimes get broken by adding e_HTTP at the front)
		$linkInfo['link_url'] = $tp -> replaceConstants($linkInfo['link_url'], TRUE, TRUE); // replace {e_xxxx}

		if(strpos($linkInfo['link_url'],"{") !== false)
		{
			$linkInfo['link_url'] = $tp->parseTemplate($linkInfo['link_url'], TRUE); // shortcode in URL support - dynamic urls for multilanguage.
		}
		elseif($linkInfo['link_url'][0] !== '/' && strpos($linkInfo['link_url'],'http') !== 0)
		{
			$linkInfo['link_url'] = e_HTTP.ltrim($linkInfo['link_url'],'/');
		}
		// By default links are not highlighted.
		
		if (isset($linkInfo['link_expand']) && $linkInfo['link_expand'])
		{
			// $href = " href=\"javascript:expandit('sub_".$linkInfo['link_id']."')\"";
			$css_class .= " e-expandit";
		}
		
		
		$linkstart = $style['linkstart'];
		$linkadd = ($style['linkclass']) ? " class='".$style['linkclass']."'" : "";
		$linkadd = ($css_class) ? " class='".$style['linkclass'].$css_class."'" : $linkadd;

		// Check for screentip regardless of URL.
		if (isset($pref['linkpage_screentip']) && $pref['linkpage_screentip'] && $linkInfo['link_description'])
		{
			$screentip = " title = \"".$tp->toHTML($linkInfo['link_description'],"","value, emotes_off, defs, no_hook")."\"";
		}

		// Check if its expandable first. It should override its URL.
		if (isset($linkInfo['link_expand']) && $linkInfo['link_expand'])
		{
			// $href = " href=\"javascript:expandit('sub_".$linkInfo['link_id']."')\"";
			$href = "href='#sub_".$linkInfo['link_id']."'";
		}
		elseif ($linkInfo['link_url'])
		{
			// Only add the e_BASE if it actually has an URL.
		//	$linkInfo['link_url'] = (strpos($linkInfo['link_url'], '://') === FALSE && strpos($linkInfo['link_url'], 'mailto:') !== 0 ? $linkInfo['link_url'] : $linkInfo['link_url']);

			// Only check if its highlighted if it has an URL
			if ($this->hilite($linkInfo['link_url'], $style['linkstart_hilite'])== TRUE) 
			{
				$linkstart = (isset($style['linkstart_hilite'])) ? $style['linkstart_hilite'] : "";
				$highlighted = TRUE;
			}


			if ($this->hilite(varset($linkInfo['link_url']), !empty($style['linkclass_hilite'])))
			{
				$linkadd = (isset($style['linkclass_hilite'])) ? " class='".$style['linkclass_hilite']."'" : "";
				$highlighted = TRUE;
			}

			if ($linkInfo['link_open'] == 4 || $linkInfo['link_open'] == 5)
			{
				$dimen = ($linkInfo['link_open'] == 4) ? "600,400" : "800,600";
				$href = " href=\"javascript:open_window('".$linkInfo['link_url']."',{$dimen})\"";
			}
			else
			{
				$href = " href='".$linkInfo['link_url']."'";
			}

			// Open link in a new window.  (equivalent of target='_blank' )
			$link_append = ($linkInfo['link_open'] == 1) ? " rel='external'" : "";
		}

		// Remove default images if its a button and add new image at the start.
		if ($linkInfo['link_button'])
		{
			$linkstart = preg_replace('/\<img.*\>/si', '', $linkstart);
			$linkstart .= $tp->toIcon($linkInfo['link_button'],array('legacy'=> "{e_IMAGE}icons/"));
			
		/*	if($linkInfo['link_button'][0]=='{')
			{
				$linkstart .= "<img src='".$tp->replaceConstants($linkInfo['link_button'],'abs')."' alt='' style='vertical-align:middle' />";	
			}
			else 
			{

				$linkstart .= "<img src='".e_IMAGE_ABS."icons/".$linkInfo['link_button']."' alt='' style='vertical-align:middle' />";
			}*/
		}

		// mobile phone support.
		$accesskey = (isset($style['accesskey']) && $style['accesskey']==TRUE) ? " accesskey='".$linkInfo['link_order']."' " : "";
        $accessdigit = (isset($style['accessdigit'],$style['accesskey']) && $style['accessdigit']==TRUE && $style['accesskey']==TRUE) ? $linkInfo['link_order'].". " : "";

		// If its a link.. make a link
		$_link = "";
		$_link .= $accessdigit;
		if (!empty($href) && ((isset($style['hilite_nolink']) && $highlighted)!=TRUE))
		{
			$_link .= "<a".$linkadd.$screentip.$href.$link_append.$accesskey.">".$tp->toHTML($linkInfo['link_name'],"","emotes_off, defs, no_hook")."</a>";
		}
		elseif (!empty($linkadd) || !empty($screentip))
		{	// If its not a link, but has a class or screentip do span:
			$_link .= "<span".$linkadd.$screentip.">".$tp->toHTML($linkInfo['link_name'],"","emotes_off, defs, no_hook")."</span>";
		}
		else 
		{	// Else just the name:
			$_link .= $tp->toHTML($linkInfo['link_name'],"","emotes_off, defs, no_hook");
		}

		$_link = $linkstart.$indent.$_link;
        return $_link.$style['linkend']."\n";


		/*

		$search[0] = "/\{LINK\}(.*?)/si";
		$replace[0] = $_link.$style['linkend']."\n";
		$search[1] = "/\{LINK_DESCRIPTION\}(.*?)/si";
		$replace[1] = $tp -> toHTML($linkInfo['link_description'], true);

		$text = preg_replace($search, $replace, $SITELINKSTYLE);

		return $text;*/
	}

	/**
	 *	Determine whether link highlighting needs to be active
	 *
	 *	@param string $link - the full link as stored in the DB
	 *	@param boolean $enabled - TRUE if the link is enabled
	 *
	 *	@return boolean TRUE if link to be highlighted, FALSE if not
	 */
	function hilite($link,$enabled = FALSE)
	{
		global $PLUGINS_DIRECTORY;
		if(!$enabled){ return FALSE; }

		$tp = e107::getParser();

		$link = $tp->replaceConstants($link, '', TRUE);			// The link saved in the DB
		$tmp = explode('?',$link);
		$link_qry = (isset($tmp[1])) ? $tmp[1] : '';
		$link_slf = (isset($tmp[0])) ? $tmp[0] : '';
		$link_pge = basename($link_slf);
		$link_match = (empty($tmp[0])) ? "": strpos(e_SELF,$tmp[0]);	// e_SELF is the actual displayed page

		if(e_MENU === "debug" && getperms('0'))
		{
			echo "<br />link= ".$link;
			echo "<br />link_q= ".$link_qry;
			echo "<br />url= ".e_PAGE;
			echo "<br />self= ".e_SELF;
			echo "<br />url_query= ".e_QUERY."<br />";
		}

		// ----------- highlight overriding - set the link matching in the page itself.
		if(defined('HILITE'))
		{
			if(strpos($link,HILITE))
			{
				return TRUE;
			}
		}

		// --------------- highlighting for 'HOME'. ----------------
		// See if we're on whatever is set as 'home' page for this user

		// Although should be just 'index.php', allow for the possibility that there might be a query part
	//	global $pref;
		$pref = e107::pref();

		if (($link_slf == e_HTTP."index.php") && count($pref['frontpage']))
		{	// Only interested if the displayed page is index.php - see whether its the user's home (front) page
			$full_url = 'news.php';					// Set a default in case
			$uc_array = explode(',', USERCLASS_LIST);
			foreach ($pref['frontpage'] as $fk=>$fp)
			{
				if (in_array($fk,$uc_array))
				{
					$full_url = ((strpos($fp, 'http') === FALSE) ? SITEURL : '').$fp;
					break;
				}
			}
			list($fp,$fp_q) = explode("?",$full_url."?"); // extra '?' ensure the array is filled
			if (e_MENU === "debug" && getperms('0'))
			{
				echo "\$fp = ".$fp."<br />";
				echo "\$fp_q = ".$fp_q."<br />";
			}
			$tmp = str_replace("../", "", e_SELF);
			if ((strpos($fp, $tmp) !== FALSE) && ($fp_q == $link_qry))
			{
				return TRUE;
			}
		}

		// --------------- highlighting for plugins. ----------------
		if(stripos($link, $PLUGINS_DIRECTORY) !== false && stripos($link, "custompages") === false)
		{
			if($link_qry)
			{	// plugin links with queries
				return (strpos(e_SELF, $link_slf) && e_QUERY == $link_qry);
			}
			else
			{	// plugin links without queries
				$link = str_replace("../", "", $link);
		   		if(stripos(dirname(e_SELF), dirname($link)) !== false)
				{
 			 		return TRUE;
				}
			}
            return FALSE;
		}

		// --------------- highlight for news items.----------------
		// eg. news.php, news.php?list.1 or news.php?cat.2 etc
		if(strpos(basename($link), "news.php") === 0)
		{
			if (strpos($link, "news.php?") !== FALSE && strpos(e_SELF,"/news.php")!==FALSE) 
			{
				$lnk = explode(".",$link_qry); // link queries.
				$qry = explode(".",e_QUERY); // current page queries.

				if($qry[0] === "item")
				{
					return $qry[2] == $lnk[1];
				}

				if($qry[0] === "all" && $lnk[0] === "all")
				{
					return TRUE;
				}

				if($lnk[0] == $qry[0] && $lnk[1] == $qry[1])
				{
					return TRUE;
				}

				if($qry[1] === "list" && $lnk[0] === "list" && $lnk[1] == $qry[2])
				{
					return TRUE;
				}
			}
			elseif (!e_QUERY && defset('e_PAGE') === "news.php")
			{
				return true;
			}
			return FALSE;
		}

		// --------------- highlight for Custom Pages.----------------
		// eg. page.php?1, or page.php?5.7	[2nd parameter is page # within item]

		//echo "Link: {$link}, link query: {$link_qry}, e_SELF: ".e_SELF.", link_slf: {$link_slf}, link_pge: {$link_pge}, e_PAGE: ".e_PAGE."<br />";
		if (($link_slf == e_HTTP.'page.php') && (e_PAGE === 'page.php'))
		{
            list($custom,$page) = explode('.',$link_qry.'.');
			list($q_custom,$q_page) = explode('.',e_QUERY.'.');
			if($custom == $q_custom)
			{
            	return TRUE;
			}
			else
			{
              	return FALSE;
			}
		}

		// --------------- highlight default ----------------
		if(strpos($link, '?') !== FALSE)
		{
			$thelink = str_replace("../", "", $link);
			if(!preg_match("/all|item|cat|list/", e_QUERY) && (empty($link) == false) && (strpos(e_SELF, str_replace("../", "",$link)) !== false))
			{
		   		return true;
			}
		}
		if(!preg_match("/all|item|cat|list/", e_QUERY) && (strpos(e_SELF, str_replace("../", "",$link)) !== false))
		{
		  	return true;
		}

		if((!$link_qry && !e_QUERY) && (empty($link) == FALSE) && (strpos(e_SELF,$link) !== FALSE))
		{
			return TRUE;
		}

		if(($link_slf == e_SELF && !$link_qry) || (e_QUERY && empty($link) == FALSE && strpos(e_SELF."?".e_QUERY,$link)!== FALSE) )
		{
          	return TRUE;
		}
		
		if($link_pge == basename($_SERVER['REQUEST_URI'])) // mod_rewrite support
		{
			return TRUE;
		}
		
	   	return FALSE;
	}
}


/**
 * Class for handling all navigation links site-wide. ie. admin links, admin-menu links, admin-plugin links, front-end sitelinks etc. 
 */
class e_navigation
{
	/**
	 * @var array Admin link structure
	 */
	var $admin_cat = array();
	
	/**
	 * @var boolean active check main
	 */
	public $activeSubFound = false;
	
	/**
	 * @var boolean active check sub
	 */
	public $activeMainFound = false;
	
	
	var $iconArray = array();

	function __construct()
	{
		if(defined('E_32_MAIN')) // basic check so that it's not loaded on the front-end. 
		{
			$this->setIconArray();	
		}
		
		
	}

	/**
	 * Guess the admin menu item icon based on the path
	 * @param $key
	 * @return string
	 */
	public static function guessMenuIcon($key)
	{
		$tmp = explode('/', $key);
		$mode = varset($tmp[0]);
		$action = varset($tmp[1]);

		switch($action)
		{
			case 'main':
			case 'list':
				$ret = $ret = ($mode === 'cat') ? 'fa-folder.glyph' : 'fa-list.glyph';
				break;

			case 'options':
			case 'prefs':
			case 'settings':
			case 'config':
			case 'configure':
				$ret = 'fa-cog.glyph';
				break;

			case 'create':
				$ret = ($mode === 'cat') ? 'fa-folder-plus.glyph' : 'fa-plus.glyph';
				break;

			case 'tools':
			case 'maint':
				$ret = 'fas-toolbox.glyph';
				break;

			case 'import':
			case 'upload':
				$ret = 'fa-upload.glyph';
				break;

			default:
				$ret = 'fa-question.glyph';
			// code to be executed if n is different from all labels;
		}


		return $ret;

	}


	/**
	 * @return array
	 */
	function getIconArray()
	{
		return $this->iconArray;	
	}


	/**
	 * @return void
	 */
	function setIconArray()
	{
		if(!defined('E_32_MAIN'))
		{
		//	e107::getCoreTemplate('admin_icons');
			e107::loadAdminIcons();
		}
		
		
		$this->iconArray = array(
				'main' 			=> E_32_MAIN,
				'admin' 		=> E_32_ADMIN,
				'admin_pass' 	=> E_32_ADPASS,
				'banlist' 		=> E_32_BANLIST,
				'cache' 		=> E_32_CACHE,
				'comment' 		=> E_32_COMMENT,
				'credits' 		=> E_32_CREDITS,
				'cron'			=> E_32_CRON,
				'custom' 		=> E_32_CUST,
				// 'custom_field' => E_32_CUSTOMFIELD,
				'database' 		=> E_32_DATAB,
				'docs' 			=> E_32_DOCS,
				//'download' => E_32_DOWNL,
				'emoticon' 		=> E_32_EMOTE,
				'filemanage' 	=> E_32_FILE,
				'fileinspector' => E_32_INSPECT,
				'frontpage' 	=> E_32_FRONT,
				'image' 		=> E_32_IMAGES,
				'language' 		=> E_32_LANGUAGE,
				'links' 		=> E_32_LINKS,
				'mail' 			=> E_32_MAIL,
				'maintain' 		=> E_32_MAINTAIN,
				'menus' 		=> E_32_MENUS,
				'meta' 			=> E_32_META,
				'newsfeed' 		=> E_32_NEWSFEED,
				'news' 			=> E_32_NEWS,
				'notify' 		=> E_32_NOTIFY,
				'phpinfo' 		=> E_32_PHP,
				'plug_manage' 	=> E_32_PLUGMANAGER,
				'poll' 			=> E_32_POLLS,
				'prefs' 		=> E_32_PREFS,
				'search' 		=> E_32_SEARCH,
				'syslogs' 		=> E_32_ADMINLOG,
				'theme_manage' 	=> E_32_THEMEMANAGER,
				'upload' 		=> E_32_UPLOADS,
				'eurl' 			=> E_32_EURL,
				'userclass' 	=> E_32_USERCLASS,
				'user_extended' => E_32_USER_EXTENDED,
				'users' 		=> E_32_USER,
				'wmessage' 		=> E_32_WELCOME 
			);		
		
	}

	
	/**
	 * Structure $this->_md5cache[$category] = md5HASH
	 * @var md5 hash used for build unique cache string per category and user classes
	 */
	protected $_md5cache = array();
	
	//FIXME array structure - see $this->admin();

	/**
	 * @return array
	 */
	function adminCats()
	{
		$tp = e107::getParser();
		
		if(count($this->admin_cat))
		{
			 return $this->admin_cat;
		}
		
		$pref = e107::getPref();
		
		$this->admin_cat['title'][1] = LAN_SETTINGS;
		$this->admin_cat['id'][1] = 'setMenu';
		$this->admin_cat['img'][1] = 'fa-cogs.glyph';
		$this->admin_cat['lrg_img'][1] = $tp->toGlyph('e-settings-32');
		$this->admin_cat['sort'][1] = true;

/*
 * i.e-cat_content-32{ background-position: -370px 0; width: 32px; height: 32px; } 
i.e-cat_files-32{ background-position: -407px 0; width: 32px; height: 32px; } 
i.e-cat_plugins-32{ background-position: -444px 0; width: 32px; height: 32px; } 
i.e-cat_settings-32{ background-position: -481px 0; width: 32px; height: 32px; } 
i.e-cat_tools-32{ background-position: -518px 0; width: 32px; height: 32px; } 
i.e-cat_users-32{ background-position: -555px 0; width: 32px; height: 32px; } 
 * e-manage-32
 */


		
		$this->admin_cat['title'][2] = ADLAN_CL_2;
		$this->admin_cat['id'][2] = 'userMenu';
		$this->admin_cat['img'][2] = 'fa-users.glyph'; // $tp->toGlyph('e-cat_users-16');
		$this->admin_cat['lrg_img'][2] = $tp->toGlyph('e-cat_users-32'); 
		$this->admin_cat['sort'][2] = true;
		
		$this->admin_cat['title'][3] = ADLAN_CL_3;
		$this->admin_cat['id'][3] = 'contMenu';
		$this->admin_cat['img'][3] = 'fa-file-text-o.glyph'; // $tp->toGlyph('e-cat_content-16');
		$this->admin_cat['lrg_img'][3] = $tp->toGlyph('e-cat_content-32'); 
		$this->admin_cat['sort'][3] = true;
		
		$this->admin_cat['title'][4] = ADLAN_CL_6;
		$this->admin_cat['id'][4] = 'toolMenu';
		$this->admin_cat['img'][4] = 'fa-wrench.glyph'; // $tp->toGlyph('e-cat_tools-16');
		$this->admin_cat['lrg_img'][4] = $tp->toGlyph('e-cat_tools-32'); 
		$this->admin_cat['sort'][4] = true;
		
		// Manage
		$this->admin_cat['title'][5] = LAN_MANAGE;
		$this->admin_cat['id'][5] = 'managMenu';
		$this->admin_cat['img'][5] = 'fa-desktop.glyph' ; // $tp->toGlyph('e-manage-16');
		$this->admin_cat['lrg_img'][5] = $tp->toGlyph('e-manage-32'); 
		$this->admin_cat['sort'][5] = TRUE;
		
		if(!empty($pref['admin_separate_plugins']))
		{
			$this->admin_cat['title'][6] = ADLAN_CL_7;
			$this->admin_cat['id'][6] = 'plugMenu'; 
			$this->admin_cat['img'][6] = 'fa-puzzle-piece.glyph'; // $tp->toGlyph('e-cat_plugins-16');
			$this->admin_cat['lrg_img'][6] = $tp->toGlyph('e-cat_plugins-32'); 
			$this->admin_cat['sort'][6] = false;	
		}
		else
		{
			// Misc.
			$this->admin_cat['title'][6] = ADLAN_CL_8;
			$this->admin_cat['id'][6] = 'miscMenu';
			$this->admin_cat['img'][6] = 'fa-puzzle-piece.glyph';  // E_16_CAT_MISC;
			$this->admin_cat['lrg_img'][6] = ''; // E_32_CAT_MISC;
			$this->admin_cat['sort'][6] = TRUE;
		}
		
		//About menu    - No 20 -  leave space for user-categories.
		$this->admin_cat['title'][20] = LAN_ABOUT;
		$this->admin_cat['id'][20] = 'aboutMenu';
		$this->admin_cat['img'][20] = 'fa-info-circle.glyph'; // E_16_CAT_ABOUT;//E_16_NAV_DOCS
		$this->admin_cat['lrg_img'][20] = ''; // $tp->toGlyph('e-cat_about-32'); ; // E_32_CAT_ABOUT;
		$this->admin_cat['sort'][20] = false;	
				
		
		return $this->admin_cat;
		
	}
	
	// Previously $array_functions variable. 

	/**
	 * @param $mode
	 * @return array|array[]|string
	 */
	function adminLinks($mode=false)
	{
	
        if($mode === 'plugin')
        {
             return $this->pluginLinks(E_16_PLUGMANAGER, "array") ;   
        }

		if($mode === 'plugin2')
        {
             return $this->pluginLinks(E_16_PLUGMANAGER, "standard") ;
        }


		
		$this->setIconArray();	
		
			
		if($mode === 'sub')
		{
				
				//FIXME  array structure suitable for e_admin_menu - see shortcodes/admin_navigation.php
				/*
				 * Info about sublinks array structure
				 *
				 * key = $array_functions key
				 * attribute 1 = link
				 * attribute 2 = title
				 * attribute 3 = description
				 * attribute 4 = perms
				 * attribute 5 = category
				 * attribute 6 = 16 x 16 image
				 * attribute 7 = 32 x 32 image
				 *
				 */
				$array_sub_functions = array();
				$array_sub_functions[17][] = array(e_ADMIN.'newspost.php', LAN_MANAGE, ADLAN_3, 'H', 3, E_16_MANAGE, E_32_MANAGE);
				$array_sub_functions[17][] = array(e_ADMIN.'newspost.php?create', LAN_CREATE, ADLAN_2, 'H', 3, E_16_CREATE, E_32_CREATE);
				$array_sub_functions[17][] = array(e_ADMIN.'newspost.php?pref', LAN_PREFS, LAN_PREFS, 'H', 3, E_16_SETTINGS, E_32_SETTINGS);	
				
				return $array_sub_functions;
		}
		
		
			//FIXME array structure suitable for e_admin_menu (NOW admin() below) - see shortcodes/admin_navigation.php
			//TODO find out where is used $array_functions elsewhere, refactor it
		
			//XXX DO NOT EDIT without first checking perms in user_handler.php !!!!
			
			$array_functions = array(
			0 => array(e_ADMIN_ABS.'administrator.php', ADLAN_8,	ADLAN_9,	'3', 2, E_16_ADMIN, E_32_ADMIN),
			1 => array(e_ADMIN_ABS.'updateadmin.php', 	ADLAN_10,	ADLAN_11,	false, 2, E_16_ADPASS, E_32_ADPASS),
			2 => array(e_ADMIN_ABS.'banlist.php', 		ADLAN_34,	ADLAN_35,	'4', 2, E_16_BANLIST, E_32_BANLIST),
			4 => array(e_ADMIN_ABS.'cache.php', 		ADLAN_74,	ADLAN_75,	'C', 1, E_16_CACHE, E_32_CACHE),
			5 => array(e_ADMIN_ABS.'cpage.php', 		ADLAN_42,	ADLAN_43,	'5|J', 3, E_16_CUST, E_32_CUST),
			6 => array(e_ADMIN_ABS.'db.php', 			ADLAN_44,	ADLAN_45,	'0', 4, E_16_DATAB, E_32_DATAB),
		//	7 => array(e_ADMIN.'download.php', ADLAN_24, ADLAN_25, 'R', 3, E_16_DOWNL, E_32_DOWNL),
			8 => array(e_ADMIN_ABS.'emoticon.php', 		ADLAN_58,	ADLAN_59,	'F', 1, E_16_EMOTE, E_32_EMOTE),
		//	9 => array(e_ADMIN.'filemanager.php', 	ADLAN_30,	ADLAN_31,	'6', 5, E_16_FILE, E_32_FILE), // replaced by media-manager
			10 => array(e_ADMIN_ABS.'frontpage.php', 	ADLAN_60,	ADLAN_61,	'G', 1, E_16_FRONT, E_32_FRONT),
			11 => array(e_ADMIN_ABS.'image.php', 		LAN_MEDIAMANAGER, LAN_MEDIAMANAGER, 'A', 5, E_16_IMAGES, E_32_IMAGES),
			12 => array(e_ADMIN_ABS.'links.php', 		LAN_NAVIGATION,	ADLAN_139,	'I', 1, E_16_LINKS, E_32_LINKS),
			13 => array(e_ADMIN_ABS.'wmessage.php', 	ADLAN_28,	ADLAN_29,	'M', 3, E_16_WELCOME, E_32_WELCOME),
			14 => array(e_ADMIN_ABS.'ugflag.php', 		ADLAN_40,	ADLAN_41,	'9', 4, E_16_MAINTAIN, E_32_MAINTAIN),
			15 => array(e_ADMIN_ABS.'menus.php', 		ADLAN_6,	ADLAN_7,	'2', 5, E_16_MENUS, E_32_MENUS),
			16 => array(e_ADMIN_ABS.'meta.php', 		ADLAN_66,	ADLAN_67,	'T', 1, E_16_META, E_32_META),
			17 => array(e_ADMIN_ABS.'newspost.php', 	ADLAN_0,	ADLAN_1,	'H|N|7|H0|H1|H2|H3|H4|H5', 3, E_16_NEWS, E_32_NEWS),
			18 => array(e_ADMIN_ABS.'phpinfo.php', 		ADLAN_68, 	ADLAN_69,	'0', 20, E_16_PHP, E_32_PHP),
			19 => array(e_ADMIN_ABS.'prefs.php', 		LAN_PREFS, 	ADLAN_5,	'1', 1, E_16_PREFS, E_32_PREFS),
			20 => array(e_ADMIN_ABS.'search.php', 		LAN_SEARCH,	ADLAN_143,	'X', 1, E_16_SEARCH, E_32_SEARCH),
			21 => array(e_ADMIN_ABS.'admin_log.php', 	ADLAN_155,	ADLAN_156,	'S', 4, E_16_ADMINLOG, E_32_ADMINLOG),
			22 => array(e_ADMIN_ABS.'theme.php', 		ADLAN_140,	ADLAN_141,	'1|TMP', 5, E_16_THEMEMANAGER, E_32_THEMEMANAGER),
			23 => array(e_ADMIN_ABS.'upload.php', 		ADLAN_72,	ADLAN_73,	'V', 3, E_16_UPLOADS, E_32_UPLOADS),
			24 => array(e_ADMIN_ABS.'users.php', 		ADLAN_36,	ADLAN_37,	'4|U0|U1|U2|U3', 2, E_16_USER, E_32_USER),
			25 => array(e_ADMIN_ABS.'userclass2.php', 	ADLAN_38,	ADLAN_39,	'4', 2, E_16_USERCLASS, E_32_USERCLASS),
			26 => array(e_ADMIN_ABS.'language.php', 	ADLAN_132,	ADLAN_133,	'L', 1, E_16_LANGUAGE, E_32_LANGUAGE),
			27 => array(e_ADMIN_ABS.'mailout.php', 		ADLAN_136,	ADLAN_137,	'W', 2, E_16_MAIL, E_32_MAIL),
			28 => array(e_ADMIN_ABS.'users_extended.php', ADLAN_78, ADLAN_79,	'4', 2, E_16_USER_EXTENDED, E_32_USER_EXTENDED),
			29 => array(e_ADMIN_ABS.'fileinspector.php', ADLAN_147, ADLAN_148,	'Y', 4, E_16_INSPECT, E_32_INSPECT),
			30 => array(e_ADMIN_ABS.'notify.php', 		ADLAN_149,	ADLAN_150,	'O', 4, E_16_NOTIFY, E_32_NOTIFY),
			31 => array(e_ADMIN_ABS.'cron.php', 		ADLAN_157,	ADLAN_158,	'U', 4, E_16_CRON, E_32_CRON),
		
			32 => array(e_ADMIN_ABS.'eurl.php', 		ADLAN_159,	ADLAN_160,	'K', 1, E_16_EURL, E_32_EURL),
			33 => array(e_ADMIN_ABS.'plugin.php', 		ADLAN_98,	ADLAN_99,	'Z', 5 , E_16_PLUGMANAGER, E_32_PLUGMANAGER),
			34 => array(e_ADMIN_ABS.'docs.php', 		ADLAN_12,	ADLAN_13,	false,	20, E_16_DOCS, E_32_DOCS),
		// TODO System Info.
		//	35 => array('#TODO', 'System Info', 'System Information', '', 20, '', ''),
			36 => array(e_ADMIN_ABS.'credits.php', LAN_CREDITS, LAN_CREDITS, false, 20, E_16_E107, E_32_E107),
		//	37 => array(e_ADMIN.'custom_field.php', ADLAN_161, ADLAN_162, 'U', 4, E_16_CUSTOMFIELD, E_32_CUSTOMFIELD),
			38 => array(e_ADMIN_ABS.'comment.php', LAN_COMMENTMAN, LAN_COMMENTMAN, 'B', 5, E_16_COMMENT, E_32_COMMENT),
		);


		if($mode === 'legacy')
        {
            return $array_functions; // Old BC format.      
        }

		$newarray = asortbyindex($array_functions, 1);
    	$array_functions_assoc = $this->convert_core_icons($newarray);


        
       if($mode === 'core') // Core links only.
        {          
            return $array_functions_assoc;          
        }
            
        $merged = array_merge($array_functions_assoc, $this->pluginLinks(E_16_PLUGMANAGER, "array")); 
        $sorted = multiarray_sort($merged,'title'); // this deleted the e-xxxx and p-xxxxx keys. 
        return $this->restoreKeys($sorted); // we restore the keys with this. 
        
	}


	/**
	 * @param $newarray
	 * @return array
	 */
	private function restoreKeys($newarray)  // Put core button array in the same format as plugin button array.
    {       
        $array_functions_assoc = array();
        
        foreach($newarray as $key=>$val)
        {
           if(varset($val['key'])) // Plugin Array.  
            {
                $key = $val['key']; 
             
                $array_functions_assoc[$key] = $val;   
            }
        }

        return $array_functions_assoc;
    }


	/**
	 * Return the default array of icon identifiers for the admin "Control Panel". (infopanel and flexpanel)
	 * @return array
	 */
	public function getDefaultAdminPanelArray()
	{
		$iconlist = $this->adminLinks();

		$defArray = array();

		$exclude = array (
				'e-administrator',
				'e-updateadmin',
				'e-banlist',
				'e-cache',
				'e-comment',
				'e-credits',
				'e-db',
				'e-docs',
				'e-emoticon',
				'e-users_extended',
				'e-fileinspector',
				'e-language',
				'e-ugflag',
				'e-notify',
				'e-phpinfo',
				'e-upload',
				'e-cron',
				'e-search',
				'e-admin_log',
				'e-eurl'
			);

		foreach($iconlist as $k=>$v)
		{
			if(!in_array($k,$exclude))
			{
				$defArray[] = $k;
			}
		}

		return $defArray;
	}


	/**
	 * @param $newarray
	 * @return array
	 */
	private function convert_core_icons($newarray)  // Put core button array in the same format as plugin button array.
	{
	 
	    $array_functions_assoc = array();
        
	    foreach($newarray as $key=>$val)
		{
			if(varset($val[0]))
			{
				$key = "e-".basename($val[0],".php");
                $val['key'] = $key;
				$val['icon'] = $val[5];
				$val['icon_32'] = $val[6];
				$val['title'] = $val[1];
				$val['link'] = $val[0];
				$val['caption'] = $val['2'];
                $val['cat'] = $val['4'];
				$val['perms'] = $val['3'];
				$array_functions_assoc[$key] = $val;
			}

		}
	
	    return $array_functions_assoc;
	}
    
    /**
     * Convert from plugin category found in plugin.xml to Navigation Category ID number. 
     */
    function plugCatToCoreCat($cat)
    {
            $convert = array(
                'settings'  => array(1,'setMenu'),
                'users'     => array(2,'userMenu'),
                'content'   => array(3,'contMenu'),
                'tools'     => array(4,'toolMenu'),
                'manage'    => array(6,'managMenu'),
                'misc'      => array(7,'miscMenu'),
                'help'      => array(20,'helpMenu')
            );

            return (int) vartrue($convert[$cat][0]);
    }


	/**
	 * Get Plugin Links - rewritten for v2.1.5
	 * @param string $iconSize
	 * @param string $linkStyle standard = new in v2.1.5 | array | adminb
	 * @return array|string
	 */
	function pluginLinks($iconSize = E_16_PLUGMANAGER, $linkStyle = 'adminb')
	{
		$plug = e107::getPlug();
		$data = $plug->getInstalled();

		$arr = array();

		$pref = e107::getPref();

		foreach($data as $path=>$ver)
		{

			if(!e107::isInstalled($path))
			{
				continue;
			}

			if(!empty($pref['lan_global_list']) && !in_array($path, $pref['lan_global_list']))
			{
				e107::loadLanFiles($path, 'admin');
			}

			$plug->load($path);


			$key = ($linkStyle === 'standard') ? "plugnav-".$path : 'p-'.$path;

			$url = $plug->getAdminUrl();
			$cat = $plug->getCategory();

			if(empty($url) || $cat === 'menu')
			{
				continue;
			}

			// Keys compatible with legacy and new admin layouts.
			$arr[$key] = array(

				'text'          => $plug->getName(),
				'description'   => $plug->getDescription(),
				'link'          => $url,
				'image'         => $plug->getIcon(16),
				'image_large'   => $plug->getIcon(32),
				'category'      => $cat,
				'perm'           => "P".$plug->getId(),
				'sort'          => 2,
				'sub_class'     => null,


				// Legacy Keys.
				'key'       => $key,
				'title'     => $plug->getName(),
				'caption'   => $plug->getAdminCaption(),
				'perms'     => "P".$plug->getId(),
				'icon'      => $plug->getIcon(16),
				'icon_32'   => $plug->getIcon(32),
				'cat'       => $this->plugCatToCoreCat($plug->getCategory())

			);


		}

		//ksort($arr, SORT_STRING);

		if($linkStyle === "array" || $iconSize === 'assoc' || $linkStyle === 'standard')
		{
		   	return $arr;
		}


		$text = '';

		foreach ($arr as $plug_key => $plug_value)
		{
			$the_icon =  ($iconSize == E_16_PLUGMANAGER) ?  $plug_value['icon'] : $plug_value['icon_32'];
			$text .= $this->renderAdminButton($plug_value['link'], $plug_value['title'], $plug_value['caption'], $plug_value['perms'], $the_icon, $linkStyle);
		}

		return $text;

	}


	
	/** 
	 * XXX the NEW version of e_admin_menu(); 
	 * Build admin menus - addmin menus are now supporting unlimitted number of submenus
	 * TODO - add this to a handler for use on front-end as well (tree, sitelinks.sc replacement)
	 *
	 * $e107_vars structure:
	 * $e107_vars['action']['text'] -> link title
	 * $e107_vars['action']['link'] -> if empty '#action' will be added as href attribute
	 * $e107_vars['action']['image'] -> (new) image tag
	 * $e107_vars['action']['perm'] -> permissions via getperms()
	 * $e107_vars['action']['userclass'] -> user class permissions via check_class()
	 * $e107_vars['action']['include'] -> additional <a> tag attributes
	 * $e107_vars['action']['sub'] -> (new) array, exactly the same as $e107_vars' first level e.g. $e107_vars['action']['sub']['action2']['link']...
	 * $e107_vars['action']['sort'] -> (new) used only if found in 'sub' array - passed as last parameter (recursive call)
	 * $e107_vars['action']['link_class'] -> (new) additional link class
	 * $e107_vars['action']['sub_class'] -> (new) additional class used only when sublinks are being parsed
	 *
	 * @param string $title
	 * @param string $active_page
	 * @param array $e107_vars
	 * @param array $tmpl
	 * @param array $sub_link
	 * @param bool $sortlist
	 * @return string parsed admin menu (or empty string if title is empty)
	 */
	function admin($title, $active_page, $e107_vars, $tmpl = array(), $sub_link = false, $sortlist = false)
	{
			
		$tp = e107::getParser();
		
		if (!$tmpl)
		{
			$tmpl = e107::getCoreTemplate('admin', 'menu', false);
		}
	
		/*
		 * Search for id
		 */
		$extraParms = array();
		$temp = explode('--id--', $title, 2);
		$title = $temp[0];
		$id = str_replace(array(' ', '_'), '-', varset($temp[1]));

		if(isset($e107_vars['_extras_'])) // hold icon info, but could be more.
		{
			$extraParms = $e107_vars['_extras_'];
			unset($e107_vars['_extras_']);
		}

		unset($temp);
	
		/*
		 * SORT
		 */
		if ($sortlist == TRUE)
		{
			$temp = $e107_vars;
			unset($e107_vars);
			$func_list = array();
			foreach (array_keys($temp) as $key)
			{
				$func_list[] = $temp[$key]['text'];
			}
	
			usort($func_list, 'strcoll');
	
			foreach ($func_list as $func_text)
			{
				foreach (array_keys($temp) as $key)
				{
					if ($temp[$key]['text'] == $func_text)
					{
						$e107_vars[] = $temp[$key];
					}
				}
			}
			unset($temp);
		}
	
		if(empty($e107_vars))
		{
			return null;
		}


	
		$kpost = '';
		$text = '';
		
		if ($sub_link)
		{
			$kpost = '_sub';
		}
		elseif(isset($tmpl['start']))
		{
			 $text = $tmpl['start'];
		}

		//FIXME - e_parse::array2sc()
/*		$search = array();
		$search[0] = '/\{LINK_TEXT\}(.*?)/si';
		$search[1] = '/\{LINK_URL\}(.*?)/si';
		$search[2] = '/\{ONCLICK\}(.*?)/si';
		$search[3] = '/\{SUB_HEAD\}(.*?)/si';
		$search[4] = '/\{SUB_MENU\}(.*?)/si';
		$search[5] = '/\{ID\}(.*?)/si';
		$search[6] = '/\{SUB_ID\}(.*?)/si';
		$search[7] = '/\{LINK_CLASS\}(.*?)/si';
		$search[8] = '/\{SUB_CLASS\}(.*?)/si';
		$search[9] = '/\{LINK_IMAGE\}(.*?)/si';
		$search[10] = '/\{LINK_SUB_OVERSIZED\}/si';
		$search[11] = '/\{LINK_DATA\}/si';*/



		foreach (array_keys($e107_vars) as $act)
		{
			if (isset($e107_vars[$act]['perm']) && $e107_vars[$act]['perm'] !== false && !getperms($e107_vars[$act]['perm'])) // check perms first.
			{
				continue;
			}


			
			if (isset($e107_vars[$act]['header'])) 
			{
				$text .= str_replace('{HEADING}', $e107_vars[$act]['header'], $tmpl['heading']);
				continue;
			}
			
			if (isset($e107_vars[$act]['divider']) && !empty($tmpl['divider']))
			{
			 	$text .= $tmpl['divider'];
				continue;	
			}

			// check class so that e.g. e_UC_NOBODY will result no permissions granted (even for main admin)
			if (isset($e107_vars[$act]['userclass']) && !e107::getUser()->checkClass($e107_vars[$act]['userclass'], false)) // check userclass perms 
			{
				continue;
			}

			$replace = array();

			$rid = str_replace(array(' ', '_'), '-', $act).($id ? "-{$id}" : '');
			
			//XXX  && !is_numeric($act) ???
			if (($active_page == (string) $act)
			|| (str_replace("?", "", e_PAGE.e_QUERY) == str_replace("?", "", $act))
            || e_REQUEST_HTTP === varset($e107_vars[$act]['link'])
			)
			{
				$temp = isset($tmpl['button_active' . $kpost]) ? $tmpl['button_active' . $kpost] : '';
			}
			else
			{
				$temp = isset($tmpl['button'.$kpost]) ? $tmpl['button'.$kpost] : '';
			}

   //     e107::getDebug()->log($e107_vars[$act]['link']);

		//	$temp = $tmpl['button'.$kpost];
		//	echo "ap = ".$active_page;
		//	echo " act = ".$act."<br /><br />";


		
			if($rid === 'adminhome')
			{
				$temp = $tmpl['button_other'.$kpost];	
			}

			if(!empty($e107_vars[$act]['template']))
			{
				$tmplateKey = 'button_'.$e107_vars[$act]['template'].$kpost;
				$temp = varset($tmpl[$tmplateKey]);
			}
	

			$replace['LINK_TEXT'] = str_replace(" ", "&nbsp;", varset($e107_vars[$act]['text']));
			$replace['LINK_DESCRIPTION'] = varset($e107_vars[$act]['description']);

			// valid URLs
			$replace['LINK_URL'] = str_replace(array('&amp;', '&'), array('&', '&amp;'), vartrue($e107_vars[$act]['link'], "#{$act}"));
			$replace['ONCLICK'] = '';

			if (vartrue($e107_vars[$act]['include']))
			{
				$replace['ONCLICK'] = $e107_vars[$act]['include'];
				//$replace[2] = $js ? " onclick=\"showhideit('".$act."');\"" : " onclick=\"document.location='".$e107_vars[$act]['link']."'; disabled=true;\"";
			}
			$replace['SUB_HEAD'] = $title;
			$replace['SUB_MENU'] = '';
			
			$replace['ID'] = $id ? " id='eplug-nav-{$rid}'" : '';
			$replace['SUB_ID'] = $rid;
		
			$replace['LINK_CLASS'] = varset($e107_vars[$act]['link_class']);
			$replace['SUB_CLASS'] = '';

			if(!isset($e107_vars[$act]['image_src']) && !isset($e107_vars[$act]['icon']))
			{
				$e107_vars[$act]['image_src'] = self::guessMenuIcon($act.'/'.$act);
			}
			
			if(!empty($e107_vars[$act]['image_src']) && strpos($e107_vars[$act]['image_src'], '.glyph') !== false)
			{
				$replace['LINK_IMAGE'] = $tp->toGlyph($e107_vars[$act]['image_src'], array('space'=>'&nbsp;'));
			}
			else
			{
				$replace['LINK_IMAGE'] = varset($e107_vars[$act]['image']);
			}

			$replace['LINK_SUB_OVERSIZED'] = (isset($e107_vars[$act]['sub']) && count($e107_vars[$act]['sub']) > 15) ? 'oversized' : '';

			if(!empty($e107_vars[$act]['link_data']))
			{

				$dataTmp = array();
				foreach($e107_vars[$act]['link_data'] as $k=>$v)
				{
					$dataTmp[] = $k.'="'.$v.'"';
				}

				$replace['LINK_DATA'] = implode(" ", $dataTmp); // $e107_vars[$act]['link_data']

			}


			$replace['LINK_BADGE'] = isset($e107_vars[$act]['badge']['value']) ? $tp->toLabel($e107_vars[$act]['badge']['value'], varset($e107_vars[$act]['badge']['type'])) : '';


			if($rid === 'logout' || $rid === 'home' || $rid === 'language')
			{
				$START_SUB = $tmpl['start_other_sub'];
			}
			else 
			{
				$START_SUB = isset($tmpl['start_sub']) ? $tmpl['start_sub'] : '';
			}		
	
			if(!empty($e107_vars[$act]['sub']))
			{
				$replace['SUB_ID'] = $id ? " id='eplug-nav-{$rid}-sub'" : '';
				$replace['LINK_CLASS'] = ' '.varset($e107_vars[$act]['link_class'], 'e-expandit');
				$replace['SUB_CLASS'] = ' '.varset($e107_vars[$act]['sub_class'], 'e-hideme e-expandme');

				$replace['SUB_MENU']  = $tp->parseTemplate($START_SUB, false, $replace);
				$replace['SUB_MENU'] .= $this->admin(false, $active_page, $e107_vars[$act]['sub'], $tmpl, true, (isset($e107_vars[$act]['sort']) ? $e107_vars[$act]['sort'] : $sortlist));
				$replace['SUB_MENU'] .= isset($tmpl['end_sub']) ? $tmpl['end_sub'] : '';
			}


			$text .= $tp->simpleParse($temp, $replace); 

		}
	
		$text .= (!$sub_link && isset($tmpl['end'])) ? $tmpl['end'] : '';
		
		if ($sub_link || empty($title))
		{
			return $text;
		}
	
		$ns = e107::getRender();
		$ns->setUniqueId($id);

		$srch = array('{ICON}', '{CAPTION}');
		$repl = array(varset($extraParms['icon']), $title);

		$caption = isset($tmpl['caption']) ? (string) $tmpl['caption'] : '';
		$title = str_replace($srch,$repl, $caption);

		$ret = $ns->tablerender($title, $text, 'default', true);
		$ns->setUniqueId(null);

		if(!empty($extraParms['return']))
		{
			return $ret;
		}

		echo $ret;
	}
			



	// Previously admin.php -> render_links();

	/**
	 * @param $link
	 * @param $title
	 * @param $description
	 * @param $perms
	 * @param $icon
	 * @param $mode
	 * @return string
	 */
	function renderAdminButton($link, $title, $description, $perms, $icon = FALSE, $mode = FALSE)
	{
		global $td;
		$tp = e107::getParser();
		$mes = e107::getMessage();
		$cols = defset('ADLINK_COLS',5);
	
		
		$text = '';
		if (getperms($perms))
		{
			$description = strip_tags($description);
			if ($mode === 'adminb')
			{
				$text = "<tr><td class='forumheader3'>
					<div class='td' style='text-align:left; vertical-align:top; width:100%'
					onmouseover=\"eover(this, 'forumheader5')\" onmouseout=\"eover(this, 'td')\" onclick=\"document.location.href='".$link."'\">
					".$icon." <b>".$title."</b> ".($description ? "[ <span class='field-help'>".$description."</span> ]" : "")."</div></td></tr>";
			}
			else
			{
	
				if($mode !== "div" && $mode !== 'div-icon-only')
				{
					if ($td == ($cols +1))
					{
						$text .= '</tr>';
						$td = 1;
					}
					if ($td == 1)
					{
						$text .= '<tr>';
					}
				}
				
				
				switch ($mode) 
				{
					case 'default':
						$text .= "<td class='td' style='text-align:left; vertical-align:top; width:20%; white-space:nowrap'>
						 <a class='core-mainpanel-link-icon e-tip' href='".$link."' title='{$description}'>".$icon." ".$tp->toHTML($title,FALSE,"defs, emotes_off")."</a></td>";
					break;
					
					case 'classis':
						$text .= "<td style='text-align:center; vertical-align:top; width:20%'><a class='core-mainpanel-link-icon' href='".$link."' title='{$description}'>".$icon."</a><br />
						<a class='core-mainpanel-link-text' href='".$link."' title='{$description}'><b>".$tp->toHTML($title,FALSE,"defs, emotes_off")."</b></a></td>";			
					break;
						
					case 'beginner':
						 $text .= "<td style='text-align:center; vertical-align:top; width:20%' ><a class='core-mainpanel-link-icon' href='".$link."' >".$icon."</a>
						<div style='padding:5px'>
						<a class='core-mainpanel-link-text' href='".$link."' title='".$description."'><b>".$tp->toHTML($title,FALSE,"defs, emotes_off")."</b></a></div><br /><br /><br /></td>";		
					break;
						
					case 'div':
						$text .= "<div class='core-mainpanel-block col-md-2'><a data-toggle='tooltip' data-bs-toggle='tooltip' class='core-mainpanel-link-icon btn btn-default btn-secondary muted' href='".$link."' title='{$description}'>".$icon."
						<small class='core-mainpanel-link-text'>".$tp->toHTML($title,FALSE,"defs, emotes_off")."</small></a>	
						</div>";					
					break;
					
					case 'div-icon-only':
						$text .= "<div class='core-mainpanel-block  e-tip' title='{$description}'><a class='core-mainpanel-link-icon btn btn-default btn-secondary e-tip' href='".$link."' >".$icon."</a></div>";
					break;
					
					default:
						
						break;
				}
				
				$td++;
			}
		}
		//else
		//{
			// echo "no Perms";
		//}
	
		return $text;
	}


	/**
	 * @param $category
	 * @param $type
	 * @return mixed|string|void
	 */
	public function cacheString($category, $type = 'sys')
	{
		if(!isset($this->_md5cache[$category]))
		{
			$uclist = e107::getUser()->getClassList();
			sort($uclist, SORT_NUMERIC);
			$this->_md5cache[$category] = md5($category.$uclist);
		}
		switch ($type) 
		{
			case 'sys':
				return $this->cacheBase().$this->_md5cache[$category];
			break;
			
			case 'md5':
				return $this->_md5cache[$category];
			break;
		}
	}

	/**
	 * @return string
	 */
	public function cacheBase()
	{
		return 'nomd5_sitelinks_';
	}


	/**
	 * TODO Cache
	 */
	public function render($data, $template, $opts = array())
	{
		if(empty($data) || empty($template) || !is_array($template))
		{
			return '';
		}
		
		/** @var navigation_shortcodes $sc */
		$sc 			= e107::getScBatch('navigation');
		$sc->template 	= $template;

		if(!empty($opts['class']))
		{
			$sc->navClass = $opts['class'];
		}

		$head			= e107::getParser()->parseTemplate($template['start'], true, $sc);

		$ret 			= "";
		
		$sc->counter	= 1;
		$this->activeMainFound = false;

		foreach ($data as $_data) 
		{		
			$active			= ($this->isActive($_data, $this->activeMainFound)) ? "_active" : ""; 
			$sc->setDepth(0);
			$sc->setVars($_data); // isActive is allowed to alter data
			$itemTmpl 		= !empty($_data['link_sub']) ? $template['item_submenu'.$active] : $template['item'.$active];
			$ret 			.= e107::getParser()->parseTemplate($itemTmpl, true, $sc);
			$sc->active		= ($active) ? true : false;
			if($sc->active)
			{
				$this->activeMainFound = true;
			}
			$sc->counter++;		
		}

		$foot 			= e107::getParser()->parseTemplate($template['end'], true, $sc);

		return ($ret != '') ? $head.$ret.$foot : '';
	}

	
	/**
	 * --------------- CODE-EFFICIENT APPROACH -------------------------
	 * FIXME syscache
	 */
	public function initData($cat=1, $opt=array())
	{	
		$sql 		= e107::getDb('sqlSiteLinks');

		$ins = ($cat > 0) ? " link_category = ". (int) $cat ." AND " : "";

		$query 		= "SELECT * FROM #links WHERE ".$ins." ((link_class >= 0 AND link_class IN (".USERCLASS_LIST.")) OR (link_class < 0 AND ABS(link_class) NOT IN (".USERCLASS_LIST.")) ) ORDER BY link_order,link_parent ASC";

		$outArray 	= array();
		$data 		= $sql->retrieve($query,true);


		$ret = $this->compile($data, $outArray);

		if(!empty($opt['flat']))
		{
			$newArr = array();
			foreach($ret as $row)
			{
				$ignore = (!empty($opt['noempty']) && (empty($row['link_url']) || $row['link_url'] === '#'));

				$tmp = (array) $row['link_sub'];

				unset($row['link_sub']);

				if($ignore !== true)
				{
					$newArr[] = $row;
				}

				if(!empty($tmp))
				{
					foreach($tmp as $val)
					{
						if(!is_array($val))
						{
							continue;
						}

						$tmp2 = $val['link_sub'];
						unset($val['link_sub']);
						$newArr[] = $val;
						if(!empty($tmp2))
						{
							foreach($tmp2 as $k=>$v)
							{
								$tmp3 = (array) $v['link_sub'];
								unset($v['link_sub']);
								$newArr[] = $v;
								foreach($tmp3 as $sub)
								{
									$newArr[] = $sub;
								}
							}
						}
					}
				}

			}

	//e107::getDebug()->log($newArr);

			return $newArr;
		}

		return $ret;
	}


	/**
	 * Compile Array Structure
	 */
	public function compile(&$inArray, &$outArray, $pid = 0) 
	{
	    if(!is_array($inArray) || !is_array($outArray)){ return null; }

	    $frm = e107::getForm();

	    foreach($inArray as $key => $val)
	    {
	        if($val['link_parent'] == $pid) 
	        {
	            $val['link_sub'] = $this->isDynamic($val);

	            if(empty($val['link_identifier']) && !empty($val['link_function']))
	            {
	                $val['link_identifier'] = $frm->name2id($val['link_function']);
	            }
				// prevent loop of death
	            if( $val['link_id'] != $pid)
	            {
		            $this->compile($inArray, $val['link_sub'], $val['link_id']);
	            }
	            $outArray[] = $val;   
	        }
	    }
		return $outArray;
	}


	/**
	 * Check for Dynamic Function
	 * @example class:method($parm)
	 */
	protected function isDynamic($row)
	{
		if(varset($row['link_sub']))
		{
			return $row['link_sub'];	
		}
		
		if(!empty($row['link_function']))
		{	
			$parm = false;	
			
			list($path,$method) = explode("::",$row['link_function']);

			if($path === 'theme')
			{
				$text = e107::callMethod('theme_shortcodes',$method, $row); // no parms as theme_shortcodes may be added as needed.

				if(!empty($text))
				{
					return '<div class="dropdown-menu">'.$text.'</div>'; // @todo use template?
				}

				e107::getDebug()->log("Theme shortcode (".$method.") could not be found for use in sitelink");
				return array();
			}
			
			if(!file_exists(e_PLUGIN.$path."/e_sitelink.php") || !e107::isInstalled($path))
			{
				return array();
			}


			if(include_once(e_PLUGIN.$path."/e_sitelink.php"))
			{
				if(strpos($method,"("))
				{
					list($method,$prm) = explode("(",$method);
					$parm = rtrim($prm,")");
				}

				$class = $path."_sitelink";
				if($sublinkArray = e107::callMethod($class,$method,$parm,$row)) //TODO Cache it.
				{
					return $sublinkArray;
				} 
			}
		}		
		
		return array();	
	}
	
	/**
	* TODO Extensive Active Link Detection; 
	* 
	*/
	public function isActive(&$data=array(), $removeOnly = false, $exactMatch = false)
	{
		if(empty($data))
		{
			return null;
		}

		
		### experimental active match added to the URL (and removed after parsing)
		### Example of main link: {e_BASE}some/url/#?match/string1^match/string2
		### It would match http://your.domain/match/string/ or http://your.domain/match/string2?some=vars
		### '#?' is the alternate active check trigger
		if(strpos($data['link_url'], '#?') !== false)
		{
			if($removeOnly)
			{
			    $arr = explode('#?', $data['link_url'], 2);
				$data['link_url'] = array_shift($arr);
				return null;
			}
			
			$_temp = explode('#?', $data['link_url'], 2);
			$data['link_url'] = $_temp[0] ? $_temp[0] : '#';
			$matches = explode('^', $_temp[1]);
			foreach ($matches as $match) 
			{
				if(strpos(e_REQUEST_URL, $match) !== false)
				{
					return true;
				}
			}
		}
		
		// No need of further checks
		if($removeOnly)
		{
			return null;
		}
		
		// already checked by compile() or external source
		if(isset($data['link_active']))
		{
			return $data['link_active'];
		}
		
		$dbLink = e_HTTP. e107::getParser()->replaceConstants($data['link_url'], TRUE, TRUE);
	//	$dbLink =  e107::getParser()->replaceConstants($data['link_url'], TRUE, TRUE);

		$dbLink = str_replace("//","/",$dbLink); // precaution for e_HTTP inclusion above.

		if(!empty($data['link_owner']) && !empty($data['link_sefurl']))
		{
			$dbLink = e107::url($data['link_owner'],$data['link_sefurl']);
		}

		//if(E107_DBG_PATH)
		//{
		//	e107::getDebug()->log("db=".$dbLink."<br />url=".e_REQUEST_URI."<br /><br />");
	//	}
	
		if($exactMatch)
		{
			if(e_REQUEST_URI == $dbLink)
			{
				return true;
			}
		}
		// XXX this one should go soon - no cotroll at all
		elseif(e_REQUEST_HTTP == $dbLink)
		{
			return true;	
		}
		elseif(e_REQUEST_HTTP."index.php" == $dbLink)
		{
			return true;	
		}
		
		if(!empty($data['link_active'])) // Can be used by e_sitelink.php
		{
			return true;		
		}

		// New e107 v.2.3.1  - using e107::nav('active', link url);
		$manualOverride = e107::getRegistry('core/e107/navigation/active');
		if(!empty($manualOverride) && empty($data['link_sub']))
		{
			if(strpos($dbLink, $manualOverride) !==false)
			{
				return true;
			}
		}
		
		
		// XXX Temporary Fix - @deprecated.
		// Set the URL matching in the page itself. see: forum_viewforum.php and forum_viewtopic.php 
		if(defined("NAVIGATION_ACTIVE") && empty($data['link_sub']))
		{
			if(strpos($data['link_url'], NAVIGATION_ACTIVE)!==false)
			{
				return true;
			}
		}
		
		
		return false;
	}
}
