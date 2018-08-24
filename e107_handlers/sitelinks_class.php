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


	function getlinks($cat=1)
	{

		$this->eLinkList = array(); // clear the array in case getlinks is called 2x on the same page.
		$sql = e107::getDb('sqlSiteLinks');
		$ins = ($cat > 0) ? "link_category = ".intval($cat)." AND " : "";
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
					if(vartrue($row['link_function']))
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
							if(vartrue($sublinkArray))
							{
								$this->eLinkList['sub_'.$row['link_id']] = $sublinkArray;
							}
						}

					}
				}
			}
		}

	}

	function getLinkArray()
	{
		return $this->eLinkList;
	}

	function get($cat = 1, $style = null, $css_class = false)
	{
		global $pref, $ns, $e107cache, $linkstyle;
		$ns = e107::getRender();
		$pref = e107::getPref();
		$e107cache = e107::getCache();

		$usecache = ((trim(defset('LINKSTART_HILITE')) != "" || trim(defset('LINKCLASS_HILITE')) != "") ? false : true);

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
				$link['link_expand'] = ((isset($pref['sitelinks_expandsub']) && $pref['sitelinks_expandsub']) && empty($style['linkmainonly']) && !defined("LINKSRENDERONLYMAIN") && isset($this->eLinkList[$main_linkid]) && is_array($this->eLinkList[$main_linkid])) ? true : false;
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
					if($k != 'head_menu')
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
	 * @param string $aSubStyle
	 * @param string $css_class
	 * @param object $level [optional]
	 * @return 
	 */
	function subLink($main_linkid,$aSubStyle,$css_class='',$level=0)
	{
		global $pref;

		if(!isset($this->eLinkList[$main_linkid]) || !is_array($this->eLinkList[$main_linkid]))
		{
			return null;
		}

		$sub['link_expand'] = ((isset($pref['sitelinks_expandsub']) && $pref['sitelinks_expandsub']) && empty($style['linkmainonly']) && !defined("LINKSRENDERONLYMAIN") && isset($this->eLinkList[$main_linkid]) && is_array($this->eLinkList[$main_linkid])) ?  TRUE : FALSE;
						
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
			$sub['link_expand'] = ((isset($pref['sitelinks_expandsub']) && $pref['sitelinks_expandsub']) && empty($style['linkmainonly']) && !defined("LINKSRENDERONLYMAIN") && isset($this->eLinkList[$id]) && is_array($this->eLinkList[$id])) ?  TRUE : FALSE;
			$class = "sublink-level-".($level+1);
			$class .= ($css_class) ? " ".$css_class : "";
			$class .= ($aSubStyle['sublinkclass']) ? " ".$aSubStyle['sublinkclass'] : ""; // backwards compatible
			$text .= $this->makeLink($sub, TRUE, $aSubStyle,$class );
			$text .= $this->subLink($id,$aSubStyle,$css_class,($level+1));				
		}

		$text .= "\n</div>\n\n";
		return $text;	
	}
	
	

	function makeLink($linkInfo, $submenu = FALSE, $style='', $css_class = false)
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

		if(vartrue($linkInfo['link_sefurl']) && !empty($linkInfo['link_owner']))
		{
			$linkInfo['link_url'] = e107::url($linkInfo['link_owner'],$linkInfo['link_sefurl']) ; //  $linkInfo['link_sefurl'];
		}



		// If submenu: Fix Name, Add Indentation.
		if ($submenu == true)
		{
			if(substr($linkInfo['link_name'],0,8) == "submenu.")
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
		elseif($linkInfo['link_url'][0] != '/' && strpos($linkInfo['link_url'],'http') !== 0)
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
			$linkInfo['link_url'] = (strpos($linkInfo['link_url'], '://') === FALSE && strpos($linkInfo['link_url'], 'mailto:') !== 0 ? $linkInfo['link_url'] : $linkInfo['link_url']);

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
			
			if($linkInfo['link_button'][0]=='{')
			{
				$linkstart .= "<img src='".$tp->replaceConstants($linkInfo['link_button'],'abs')."' alt='' style='vertical-align:middle' />";	
			}
			else 
			{
				$linkstart .= "<img src='".e_IMAGE_ABS."icons/".$linkInfo['link_button']."' alt='' style='vertical-align:middle' />";	
			}
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
		global $PLUGINS_DIRECTORY,$tp,$pref;
		if(!$enabled){ return FALSE; }

		$link = $tp->replaceConstants($link, '', TRUE);			// The link saved in the DB
		$tmp = explode('?',$link);
		$link_qry = (isset($tmp[1])) ? $tmp[1] : '';
		$link_slf = (isset($tmp[0])) ? $tmp[0] : '';
		$link_pge = basename($link_slf);
		$link_match = (empty($tmp[0])) ? "": strpos(e_SELF,$tmp[0]);	// e_SELF is the actual displayed page

		if(e_MENU == "debug" && getperms('0'))
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
		global $pref;
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
			if (e_MENU == "debug" && getperms('0'))
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
		if(stristr($link, $PLUGINS_DIRECTORY) !== FALSE && stristr($link, "custompages") === FALSE)
		{
			if($link_qry)
			{	// plugin links with queries
				return (strpos(e_SELF,$link_slf) && e_QUERY == $link_qry) ? TRUE : FALSE;
			}
			else
			{	// plugin links without queries
				$link = str_replace("../", "", $link);
		   		if(stristr(dirname(e_SELF), dirname($link)) !== FALSE)
				{
 			 		return TRUE;
				}
			}
            return FALSE;
		}

		// --------------- highlight for news items.----------------
		// eg. news.php, news.php?list.1 or news.php?cat.2 etc
		if(substr(basename($link),0,8) == "news.php")
		{
			if (strpos($link, "news.php?") !== FALSE && strpos(e_SELF,"/news.php")!==FALSE) 
			{
				$lnk = explode(".",$link_qry); // link queries.
				$qry = explode(".",e_QUERY); // current page queries.

				if($qry[0] == "item")
				{
					return ($qry[2] == $lnk[1]) ? TRUE : FALSE;
				}

				if($qry[0] == "all" && $lnk[0] == "all")
				{
					return TRUE;
				}

				if($lnk[0] == $qry[0] && $lnk[1] == $qry[1])
				{
					return TRUE;
				}

				if($qry[1] == "list" && $lnk[0] == "list" && $lnk[1] == $qry[2])
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
		if (($link_slf == e_HTTP.'page.php') && (e_PAGE == 'page.php'))
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

		if(($link_slf == e_SELF && !link_qry) || (e_QUERY && empty($link) == FALSE && strpos(e_SELF."?".e_QUERY,$link)!== FALSE) )
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

	function getIconArray()
	{
		return $this->iconArray;	
	}
	
	
	
	
	function setIconArray()
	{
		if(!defined('E_32_MAIN'))
		{
			e107::getCoreTemplate('admin_icons');
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
		
		if(vartrue($pref['admin_separate_plugins']))
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
			$this->admin_cat['img'][6] = 'fa-puzzle-piece.glyph'; ; // E_16_CAT_MISC;
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
	function adminLinks($mode=false)
	{
	
        if($mode == 'plugin')
        {
             return $this->pluginLinks(E_16_PLUGMANAGER, "array") ;   
        }

		if($mode == 'plugin2')
        {
             return $this->pluginLinks(E_16_PLUGMANAGER, "standard") ;
        }


		
		$this->setIconArray();	
		
			
		if($mode=='sub')
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
			12 => array(e_ADMIN_ABS.'links.php', 		ADLAN_138,	ADLAN_139,	'I', 1, E_16_LINKS, E_32_LINKS),
			13 => array(e_ADMIN_ABS.'wmessage.php', 	ADLAN_28,	ADLAN_29,	'M', 3, E_16_WELCOME, E_32_WELCOME),
			14 => array(e_ADMIN_ABS.'ugflag.php', 		ADLAN_40,	ADLAN_41,	'9', 4, E_16_MAINTAIN, E_32_MAINTAIN),
			15 => array(e_ADMIN_ABS.'menus.php', 		ADLAN_6,	ADLAN_7,	'2', 5, E_16_MENUS, E_32_MENUS),
			16 => array(e_ADMIN_ABS.'meta.php', 		ADLAN_66,	ADLAN_67,	'T', 1, E_16_META, E_32_META),
			17 => array(e_ADMIN_ABS.'newspost.php', 	ADLAN_0,	ADLAN_1,	'H|N|7|H0|H1|H2|H3|H4|H5', 3, E_16_NEWS, E_32_NEWS),
			18 => array(e_ADMIN_ABS.'phpinfo.php', 		ADLAN_68, 	ADLAN_69,	'0', 20, E_16_PHP, E_32_PHP),
			19 => array(e_ADMIN_ABS.'prefs.php', 		LAN_PREFS, 	ADLAN_5,	'1', 1, E_16_PREFS, E_32_PREFS),
			20 => array(e_ADMIN_ABS.'search.php', 		LAN_SEARCH,	ADLAN_143,	'X', 1, E_16_SEARCH, E_32_SEARCH),
			21 => array(e_ADMIN_ABS.'admin_log.php', 	ADLAN_155,	ADLAN_156,	'S', 4, E_16_ADMINLOG, E_32_ADMINLOG),
			22 => array(e_ADMIN_ABS.'theme.php', 		ADLAN_140,	ADLAN_141,	'1', 5, E_16_THEMEMANAGER, E_32_THEMEMANAGER),
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


		if($mode == 'legacy')
        {
            return $array_functions; // Old BC format.      
        }

		$newarray = asortbyindex($array_functions, 1);
    	$array_functions_assoc = $this->convert_core_icons($newarray);


        
       if($mode == 'core') // Core links only. 
        {          
            return $array_functions_assoc;          
        }
            
        $merged = array_merge($array_functions_assoc, $this->pluginLinks(E_16_PLUGMANAGER, "array")); 
        $sorted = multiarray_sort($merged,'title'); // this deleted the e-xxxx and p-xxxxx keys. 
        return $this->restoreKeys($sorted); // we restore the keys with this. 
        
	}



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


	// Function renders all the plugin links according to the required icon size and layout style
	// - common to the various admin layouts such as infopanel, classis etc. 
	/**
	 * @deprecated
	 * @param string $iconSize
	 * @param string $linkStyle
	 * @return array|string
	 */
	function pluginLinksOld($iconSize = E_16_PLUGMANAGER, $linkStyle = 'adminb')
	{
	
		$sql = e107::getDb();
		$tp = e107::getParser();
		
		
		$plug_id = array();
		$plugin_array = array();
		e107::getDb()->db_Select("plugin", "*", "plugin_installflag = 1"); // Grab plugin IDs. 
		while ($row = e107::getDb()->db_Fetch())
		{
			$pth = $row['plugin_path'];
			$plug_id[$pth] = $row['plugin_id'];
		}
		
		$pref = e107::getConfig('core')->getPref();
		
		$text = $this->renderAdminButton(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "Z", $iconSize, $linkStyle);
	
		$plugs = e107::getObject('e107plugin');
		

		
		if(!empty($pref['plug_installed']))
		{
			foreach($pref['plug_installed'] as $plug=>$vers)
			{

				$plugs->parse_plugin($plug);
				
		
				$plugin_path = $plug;
				$name = $plugs->plug_vars['@attributes']['name'];
			/*	
				echo "<h1>".$name." ($plug)</h1>";
				print_a($plugs->plug_vars);
                */

				if(!varset($plugs->plug_vars['adminLinks']['link']))
				{
					continue;	
				}
		
				foreach($plugs->plug_vars['adminLinks']['link'] as $tag)
				{
					if(varset($tag['@attributes']['primary']) !='true')
					{
						continue;
					}

					if(!empty($pref['lan_global_list']) && !in_array($plugin_path, $pref['lan_global_list']))
					{
						e107::loadLanFiles($plugin_path, 'admin');	
					}
								
					$att = $tag['@attributes'];
		
			
					$eplug_name 		= $tp->toHTML($name,FALSE,"defs, emotes_off");
					$eplug_conffile 	= $att['url'];
					$eplug_icon_small 	= (!empty($att['iconSmall'])) ? $plugin_path.'/'.$att['iconSmall'] : '';
					$eplug_icon 		= (!empty($att['icon'])) ? $plugin_path.'/'.$att['icon'] : '';
					$eplug_caption 		= str_replace("'", '', $tp->toHTML($att['description'], FALSE, 'defs, emotes_off'));
					
					if (varset($eplug_conffile))
					{
						$eplug_name = $tp->toHTML($eplug_name,FALSE,"defs, emotes_off");
						$plugin_icon = $eplug_icon_small ? "<img class='icon S16' src='".e_PLUGIN_ABS.$eplug_icon_small."' alt=''  />" : E_16_PLUGIN;
						$plugin_icon_32 = $eplug_icon ? "<img class='icon S32' src='".e_PLUGIN_ABS.$eplug_icon."' alt=''  />" :  E_32_PLUGIN;
						$plugin_array['p-'.$plugin_path] = array(
						  'key'      => 'p-'.$plugin_path,
						  'link'      => e_PLUGIN.$plugin_path."/".$eplug_conffile, 
						  'title'     => $eplug_name,
						  'caption' => $eplug_caption,
						  'perms'     => "P".varset($plug_id[$plugin_path]), 
						  'icon'      => $plugin_icon, 
						  'icon_32'   => $plugin_icon_32,
						  'cat'       => $this->plugCatToCoreCat($plugs->plug_vars['category'])
                        );
					}
				}
			}	
		}
	
		
	//	print_a($plugs->plug_vars['adminLinks']['link']);
		
		
	
	
		/*	echo "hello there";
		
		 	$xml = e107::getXml();
			$xml->filter = array('@attributes' => FALSE,'description'=>FALSE,'administration' => FALSE);	// .. and they're all going to need the same filter
		
			if ($sql->db_Select("plugin", "*", "plugin_installflag=1"))
			{
				while ($row = $sql->db_Fetch())
				{
					extract($row);		//  plugin_id int(10) unsigned NOT NULL auto_increment,
										//	plugin_name varchar(100) NOT NULL default '',
										//	plugin_version varchar(10) NOT NULL default '',
										//	plugin_path varchar(100) NOT NULL default '',
										//	plugin_installflag tinyint(1) unsigned NOT NULL default '0',
										//	plugin_addons text NOT NULL,
		
					if (is_readable(e_PLUGIN.$plugin_path."/plugin.xml"))
					{
						$readFile = $xml->loadXMLfile(e_PLUGIN.$plugin_path.'/plugin.xml', true, true);
						if ($readFile === FALSE)
						{
							echo 'Error in file: '.e_PLUGIN.$plugin_path.'/plugin.xml'.'<br />';
						}
						else
						{
							loadLanFiles($plugin_path, 'admin');
							$eplug_name 		= $tp->toHTML($readFile['@attributes']['name'],FALSE,"defs, emotes_off");
							$eplug_conffile 	= $readFile['administration']['configFile'];
							$eplug_icon_small 	= $plugin_path.'/'.$readFile['administration']['iconSmall'];
							$eplug_icon 		= $plugin_path.'/'.$readFile['administration']['icon'];
							$eplug_caption 		= str_replace("'", '', $tp->toHTML($readFile['description'], FALSE, 'defs, emotes_off'));
						}
					}
					elseif (is_readable(e_PLUGIN.$plugin_path."/plugin.php"))
					{
						include(e_PLUGIN.$plugin_path."/plugin.php");
					}
					if (varset($eplug_conffile))
					{
						$eplug_name = $tp->toHTML($eplug_name,FALSE,"defs, emotes_off");
						$plugin_icon = $eplug_icon_small ? "<img class='icon S16' src='".e_PLUGIN.$eplug_icon_small."' alt=''  />" : E_16_PLUGIN;
						$plugin_icon_32 = $eplug_icon ? "<img class='icon S32' src='".e_PLUGIN.$eplug_icon."' alt=''  />" : E_32_PLUGIN;
		
						$plugin_array['p-'.$plugin_path] = array('link' => e_PLUGIN.$plugin_path."/".$eplug_conffile, 'title' => $eplug_name, 'caption' => $eplug_caption, 'perms' => "P".$plugin_id, 'icon' => $plugin_icon, 'icon_32' => $plugin_icon_32);
					}
					unset($eplug_conffile, $eplug_name, $eplug_caption, $eplug_icon_small);
				}
					}
					else
					{
						$plugin_array = array();	
					}
				*/
			ksort($plugin_array, SORT_STRING);  // To FIX, without changing the current key format, sort by 'title'
		
			if($linkStyle == "array" || $iconSize == 'assoc')
			{
		       	return $plugin_array;
			}
		
			foreach ($plugin_array as $plug_key => $plug_value)
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
			
		global $E_ADMIN_MENU; //TODO remove me?
		$tp = e107::getParser();
		
		if (!$tmpl)
			$tmpl = $E_ADMIN_MENU;
	
		/*
		 * Search for id
		 */
		$temp = explode('--id--', $title, 2);
		$title = $temp[0];
		$id = str_replace(array(' ', '_'), '-', varset($temp[1]));
	
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
		else
		{
			 $text = $tmpl['start'];
		}
	
		//FIXME - e_parse::array2sc()
		$search = array();
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
		$search[11] = '/\{LINK_DATA\}/si';


		foreach (array_keys($e107_vars) as $act)
		{
			if (isset($e107_vars[$act]['perm']) && $e107_vars[$act]['perm'] !== false && !getperms($e107_vars[$act]['perm'])) // check perms first.
			{
				continue;
			}


			
			if (isset($e107_vars[$act]['header'])) 
			{
			 	$text .= "<li class='nav-header'>".$e107_vars[$act]['header']."</li>";	//TODO add to Template. 
				continue;
			}
			
			if (isset($e107_vars[$act]['divider'])) 
			{
			 //	$text .= "<li class='divider'></li>";	
			 	$text .= $tmpl['divider'];
				continue;	
			}
			
			
			
			// check class so that e.g. e_UC_NOBODY will result no permissions granted (even for main admin)
			if (isset($e107_vars[$act]['userclass']) && !e107::getUser()->checkClass($e107_vars[$act]['userclass'], false)) // check userclass perms 
			{
				continue;
			}
	
			//  print_a($e107_vars[$act]);
	
			$replace = array();



			
			$rid = str_replace(array(' ', '_'), '-', $act).($id ? "-{$id}" : '');
			
			//XXX  && !is_numeric($act) ???
			if (($active_page == (string) $act)|| (str_replace("?", "", e_PAGE.e_QUERY) == str_replace("?", "", $act)))
			{
				$temp = $tmpl['button_active'.$kpost];
			}
			else
			{
				$temp = $tmpl['button'.$kpost];
			}
	
		//	$temp = $tmpl['button'.$kpost];
		//	echo "ap = ".$active_page;
		//	echo " act = ".$act."<br /><br />";



		
			if($rid == 'adminhome')
			{
				$temp = $tmpl['button_other'.$kpost];	
			}

			if(!empty($e107_vars[$act]['template']))
			{
				$tmplateKey = 'button_'.$e107_vars[$act]['template'].$kpost;
				$temp = $tmpl[$tmplateKey];

				// e107::getDebug()->log($tmplateKey);
			}
	

			$replace[0] = str_replace(" ", "&nbsp;", $e107_vars[$act]['text']);
			// valid URLs
			$replace[1] = str_replace(array('&amp;', '&'), array('&', '&amp;'), vartrue($e107_vars[$act]['link'], "#{$act}"));
			$replace[2] = '';
			if (vartrue($e107_vars[$act]['include']))
			{
				$replace[2] = $e107_vars[$act]['include'];
				//$replace[2] = $js ? " onclick=\"showhideit('".$act."');\"" : " onclick=\"document.location='".$e107_vars[$act]['link']."'; disabled=true;\"";
			}
			$replace[3] = $title;
			$replace[4] = '';
			
			$replace[5] = $id ? " id='eplug-nav-{$rid}'" : '';
			$replace[6] = $rid;
		
			$replace[7] = varset($e107_vars[$act]['link_class']);
			$replace[8] = '';
			
			if(vartrue($e107_vars[$act]['image_src']) && strstr($e107_vars[$act]['image_src'],'.glyph'))
			{
				$replace[9] = $tp->toGlyph($e107_vars[$act]['image_src'], array('space'=>'&nbsp;'));
			}
			else
			{
				$replace[9] = varset($e107_vars[$act]['image']);	
			}

			$replace[10] = (isset($e107_vars[$act]['sub']) && count($e107_vars[$act]['sub']) > 20) ? 'oversized' : '';

			if(!empty($e107_vars[$act]['link_data']))
			{

				$dataTmp = array();
				foreach($e107_vars[$act]['link_data'] as $k=>$v)
				{
					$dataTmp[] = $k.'="'.$v.'"';
				}

				$replace[11] = implode(" ", $dataTmp); // $e107_vars[$act]['link_data']

			}



			
			if($rid == 'logout' || $rid == 'home' || $rid == 'language')
			{
				$START_SUB = $tmpl['start_other_sub'];
			}
			else 
			{
				$START_SUB = $tmpl['start_sub'];	
			}		
	
			if(!empty($e107_vars[$act]['sub']))
			{
				$replace[6] = $id ? " id='eplug-nav-{$rid}-sub'" : '';
				$replace[7] = ' '.varset($e107_vars[$act]['link_class'], 'e-expandit');
				$replace[8] = ' '.varset($e107_vars[$act]['sub_class'], 'e-hideme e-expandme');
				$replace[4] = preg_replace($search, $replace, $START_SUB);
				$replace[4] .= $this->admin(false, $active_page, $e107_vars[$act]['sub'], $tmpl, true, (isset($e107_vars[$act]['sort']) ? $e107_vars[$act]['sort'] : $sortlist));
				$replace[4] .= $tmpl['end_sub'];
			}
	
			$text .= preg_replace($search, $replace, $temp);
		//	echo "<br />".$title." act=".$act;
			//print_a($e107_vars[$act]);
		}
	
		$text .= (!$sub_link) ? $tmpl['end'] : '';
		
		if ($sub_link || empty($title))
		{
			return $text;
		}
	
		$ns = e107::getRender();
		$ns->setUniqueId($id);
		$ns->tablerender($title, $text);
		return '';
	}
			



	// Previously admin.php -> render_links();
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
			if ($mode == 'adminb')
			{
				$text = "<tr><td class='forumheader3'>
					<div class='td' style='text-align:left; vertical-align:top; width:100%'
					onmouseover=\"eover(this, 'forumheader5')\" onmouseout=\"eover(this, 'td')\" onclick=\"document.location.href='".$link."'\">
					".$icon." <b>".$title."</b> ".($description ? "[ <span class='field-help'>".$description."</span> ]" : "")."</div></td></tr>";
			}
			else
			{
	
				if($mode != "div" && $mode != 'div-icon-only')
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
						$text .= "<div class='core-mainpanel-block '><a data-toggle='tooltip' class='core-mainpanel-link-icon btn btn-default btn-secondary muted' href='".$link."' title='{$description}'>".$icon."
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
		else
		{
			// echo "no Perms";
		}
	
		return $text;
	}


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

	public function cacheBase()
	{
		return 'nomd5_sitelinks_';
	}


	/**
	 * TODO Cache
	 */
	public function render($data, $template, $useCache = true) 
	{
		if(empty($data) || empty($template) || !is_array($template)) return '';
		
		$sc 			= e107::getScBatch('navigation');	
		$sc->template 	= $template; 
		$head			= e107::getParser()->parseTemplate($template['start'],true);
		$foot 			= e107::getParser()->parseTemplate($template['end'],true);
		$ret 			= "";
		
		$sc->counter	= 1;
		$this->activeMainFound = false;

		foreach ($data as $_data) 
		{		
			$active			= ($this->isActive($_data, $this->activeMainFound)) ? "_active" : ""; 
			$sc->setDepth(0);
			$sc->setVars($_data); // isActive is allowed to alter data
			$itemTmpl 		= count($_data['link_sub']) > 0 ? $template['item_submenu'.$active] : $template['item'.$active];
			$ret 			.= e107::getParser()->parseTemplate($itemTmpl, true, $sc);
			$sc->active		= ($active) ? true : false;
			if($sc->active)
			{
				$this->activeMainFound = true;
			}
			$sc->counter++;		
		}
		
		return ($ret != '') ? $head.$ret.$foot : '';
	}

	
	/**
	 * --------------- CODE-EFFICIENT APPROACH -------------------------
	 * FIXME syscache
	 */
	public function initData($cat=1, $opt=array())
	{	
		$sql 		= e107::getDb('sqlSiteLinks');

		$ins = ($cat > 0) ? " link_category = ".intval($cat)." AND " : "";

		$query 		= "SELECT * FROM #links WHERE ".$ins." ((link_class >= 0 AND link_class IN (".USERCLASS_LIST.")) OR (link_class < 0 AND ABS(link_class) NOT IN (".USERCLASS_LIST.")) ) ORDER BY link_order,link_parent ASC";

		$outArray 	= array();
		$data 		= $sql->retrieve($query,true);


		$ret = $this->compile($data, $outArray);

		if(!empty($opt['flat']))
		{
			$newArr = array();
			foreach($ret as $row)
			{
				$ignore = (!empty($opt['noempty']) && (empty($row['link_url']) || $row['link_url'] === '#')) ? true : false;

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
						$tmp2 = (array) $val['link_sub'];
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
	            if( $val['link_id'] != $pid) $this->compile($inArray, $val['link_sub'], $val['link_id']);
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
			
			if(strpos($method,"("))
			{
				list($method,$prm) = explode("(",$method);
				$parm = rtrim($prm,")");	
			}

			if(!file_exists(e_PLUGIN.$path."/e_sitelink.php"))
			{

			}


			if(include_once(e_PLUGIN.$path."/e_sitelink.php"))
			{
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
	public function isActive(&$data='', $removeOnly = false, $exactMatch = false)
	{
		if(empty($data)) return;

		
		### experimental active match added to the URL (and removed after parsing)
		### Example of main link: {e_BASE}some/url/#?match/string1^match/string2
		### It would match http://your.domain/match/string/ or http://your.domain/match/string2?some=vars
		### '#?' is the alternate active check trigger
		if(strpos($data['link_url'], '#?') !== false)
		{
			if($removeOnly)
			{
				$data['link_url'] = array_shift(explode('#?', $data['link_url'], 2));
				return;
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
		if($removeOnly) return; 
		
		// already checked by compile() or external source
		if(isset($data['link_active'])) return $data['link_active'];
		
		$dbLink = e_HTTP. e107::getParser()->replaceConstants($data['link_url'], TRUE, TRUE);
	//	$dbLink =  e107::getParser()->replaceConstants($data['link_url'], TRUE, TRUE);

		$dbLink = str_replace("//","/",$dbLink); // precaution for e_HTTP inclusion above.

		if(!empty($data['link_owner']) && !empty($data['link_sefurl']))
		{
			$dbLink = e107::url($data['link_owner'],$data['link_sefurl']);
		}

		if(E107_DBG_PATH)
		{
		//	e107::getDebug()->log("db=".$dbLink."<br />url=".e_REQUEST_URI."<br /><br />");
		}
	
		if($exactMatch)
		{
			if(e_REQUEST_URI == $dbLink) return true;	
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
		
		
		// XXX Temporary Fix - TBD. 
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











/**
 * Navigation Shortcodes
 * @todo SEF 
 */
class navigation_shortcodes extends e_shortcode
{
	
	public $template;
	public $counter;
	public $active;
	public $depth = 0;

	
	/**
	 * 
	 * @return string 'active' on the active link.
	 * @example {LINK_ACTIVE}
	 */
	function sc_link_active($parm='')
	{
		if($this->active == true)
		{
			return 'active';	
		}
		
		// check if it's the first link.. (eg. anchor mode) and nothing activated. 
		return ($this->counter ==1) ? 'active' : '';	
		
	}
	
	/**
	 * Return the primary_id number for the current link
	 * @return integer
	 */
	function sc_link_id($parm='')
	{
		return intval($this->var['link_id']);		
	}

	function sc_link_depth($parm='')
	{
		return isset($this->var['link_depth']) ? intval($this->var['link_depth']) : $this->depth;
	}


	function setDepth($val)
	{
		$this->depth = intval($val);
	}


	/**
	 * Return the name of the current link
	 * @return string 
	 * @example {LINK_NAME}
	 */
	function sc_link_name($parm='')
	{
		if(empty($this->var['link_name']))
		{
			return null;
		}
		
		if(substr($this->var['link_name'],0,8) == 'submenu.') // BC Fix. 
		{
			list($tmp,$tmp2,$link) = explode('.',$this->var['link_name'],3);	
		}
		else
		{
			$link = $this->var['link_name'];	
		}
		
		return e107::getParser()->toHtml($link, false,'defs');		
	}

	
	/**
	 * Return the parent of the current link
	 * @return integer
	 */
	function sc_link_parent($parm='')
	{
		return intval($this->var['link_parent']);
	}


	function sc_link_identifier($parm='')
	{
		return isset($this->var['link_identifier']) ? $this->var['link_identifier'] : '';
	}

	/**
	 * Return the URL of the current link
	 * @return string
	 */
	function sc_link_url($parm='')
	{
		$tp = e107::getParser();

		if(!empty($this->var['link_owner']) && !empty($this->var['link_sefurl']))
		{
			return e107::url($this->var['link_owner'],$this->var['link_sefurl']);
		}
		
		if(strpos($this->var['link_url'], e_HTTP) === 0)
		{
			$url = "{e_BASE}".substr($this->var['link_url'], strlen(e_HTTP));
		}
		elseif($this->var['link_url'][0] != "{" && strpos($this->var['link_url'],"://")===false)
		{
			$url = "{e_BASE}".$this->var['link_url']; // Add e_BASE to links like: 'news.php' or 'contact.php' 	
		}
		else
		{
			$url = $this->var['link_url'];	
		}	
		
		$url = $tp->replaceConstants($url, 'full', TRUE);
		
		if(strpos($url,"{") !== false)
		{
           $url = $tp->parseTemplate($url, TRUE); // BC Fix shortcode in URL support - dynamic urls for multilanguage.
        }
		
		return $url;
	}

/*
	function sc_link_sub_oversized($parm='')
	{
		$count = count($this->var['link_sub']);

		if(!empty($parm) && $count > $parm)
		{
			return 'oversized';
		}

		return $count;

	}
*/

	/**
	 * Returns only the anchor target in the URL if one is found.
	 * @param null $parm
	 * @return null|string
	 */
	function sc_link_target($parm=null)
	{
		if(strpos($this->var['link_url'],'#')!==false)
		{
			list($tmp,$segment) = explode('#',$this->var['link_url'],2);
			return '#'.$segment;

		}

		return '#';
	}


	
	function sc_link_open($parm = '')
	{
		$type = $this->var['link_open'] ? (int) $this->var['link_open'] : 0;
		
		### 0 - same window, 1 - target blank, 4 - 600x400 popup, 5 - 800x600 popup
		### TODO - JS popups (i.e. bootstrap)
		switch($type)
		{
			case 1:
				return ' target="_blank"';
			break;
			
			case 4:
				return " onclick=\"open_window('".$this->var['link_url']."',600,400); return false;\"";
			break;
			
			case 5:
				return " onclick=\"open_window('".$this->var['link_url']."',800,600); return false;\"";
			break;
		}
		return '';
	}

	/**
	 * @Deprecated - Use {LINK_ICON} instead. 
	 */
	function sc_link_image($parm='')
	{
		e107::getMessage()->addDebug("Using deprecated shortcode: {LINK_IMAGE} - use {LINK_ICON} instead.");
		return $this->sc_link_icon($parm);	
	}
	
	
	/**
	 * Return the link icon of the current link
	 * @return string
	 */
	function sc_link_icon($parm='')
	{
		$tp = e107::getParser();
				
		if (empty($this->var['link_button'])) return '';
		
	//	if($icon = $tp->toGlyph($this->var['link_button']))
	//	{
	//		return $icon;	
	//	}
	//	else 
		{
			//$path = e107::getParser()->replaceConstants($this->var['link_button'], 'full', TRUE);	
			return $tp->toIcon($this->var['link_button'],array('fw'=>true, 'space'=>' ', 'legacy'=>"{e_IMAGE}icons/"));
			// return "<img class='icon' src='".$path."' alt=''  />";	
		}

	}

		
	/**
	 * Return the link description of the current link
	 * @return string
	 */
	function sc_link_description($parm='')
	{
		$toolTipEnabled = e107::pref('core', 'linkpage_screentip', false);

		if($toolTipEnabled == false || empty($this->var['link_description']))
		{
			return null;
		}


		return e107::getParser()->toAttribute($this->var['link_description']);	
	}

	
	/**
	 * Return the parsed sublinks of the current link
	 * @return string
	 */	
	function sc_link_sub($parm='')
	{
		if(empty($this->var['link_sub']))
		{
			return false;
		}

		if(is_string($this->var['link_sub'])) // html override option.
		{

		//	e107::getDebug()->log($this->var);

			return $this->var['link_sub'];
		}

		$this->depth++;
		// Assume it's an array.

		$startTemplate = !empty($this->var['link_sub'][0]['link_sub']) && isset($this->template['submenu_lowerstart']) ? $this->template['submenu_lowerstart'] : $this->template['submenu_start'];
		$endTemplate = !empty($this->var['link_sub'][0]['link_sub']) && isset($this->template['submenu_lowerstart']) ? $this->template['submenu_lowerend'] :  $this->template['submenu_end'];

		$text = e107::getParser()->parseTemplate(str_replace('{LINK_SUB}', '', $startTemplate), true, $this);

		foreach($this->var['link_sub'] as $val)
		{
			$active	= (e107::getNav()->isActive($val, $this->activeSubFound, true)) ? "_active" : "";
			$this->setVars($val);	// isActive is allowed to alter data
			$tmpl = !empty($val['link_sub']) ? varset($this->template['submenu_loweritem'.$active]) : varset($this->template['submenu_item'.$active]);
			$text .= e107::getParser()->parseTemplate($tmpl, TRUE, $this);
			if($active) $this->activeSubFound = true;		
		}

		$text .= e107::getParser()->parseTemplate(str_replace('{LINK_SUB}', '', $endTemplate), true, $this);
		
		return $text;
	}
	
	/**
	 * Return a generated anchor for the current link. 
	 * @param unused
	 * @return	string - a generated anchor for the current link. 
	 * @example {LINK_ANCHOR} 
	 */
	function sc_link_anchor($parm='')
	{
		return $this->var['link_name'] ? '#'.e107::getForm()->name2id($this->var['link_name']) : '';	
	}
}
