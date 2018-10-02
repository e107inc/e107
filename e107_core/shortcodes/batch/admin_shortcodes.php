<?php
/*
* Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
*
* Admin shortcode batch - class
*/
/**
 *	@package    e107
 *	@subpackage	shortcodes
 *
 *	Shortcodes for admin items
 */

if (!defined('e107_INIT')) { exit; }

class admin_shortcodes
{

	const ADMIN_NAV_HOME = 'enav_home'; // Must match with admin_template. ie. {ADMIN_NAVIGATION=enav_home} and $E_ADMIN_NAVIGATION['button_enav_home']
	const ADMIN_NAV_LANGUAGE = 'enav_language';
	const ADMIN_NAV_LOGOUT = 'enav_logout';
	
	function cronUpdateRender($parm,$cacheData)
	{
		$mes = e107::getMessage();
		
            if($cacheData == 'up-to-date')
            {
                return '';
            }
    	
			$installUrl = "#"; // TODO 
		
		
            if($parm=='alert')
            {	//TODO LANVARS
				$text = ADLAN_122.'  v'.$cacheData.'</a>.
					<a class="btn btn-success" href="'.$installUrl.'">'.ADLAN_121.'</a>'; //Install
				
				$mes->addInfo($text);
				return null; //  $mes->render();
			}
            
            if($parm=='icon')
            {
				
				return '<ul class="nav navbar pill navbar-nav">
						<li class="dropdown">
						<a class="dropdown-toggle" title="'.LAN_MESSAGES.'" role="button" data-toggle="dropdown" href="#">
						'.E_16_E107.' <b class="caret"></b>
						</a> 
						<ul class="dropdown-menu" role="menu">
						<li class="nav-header dropdown-header navbar-header">'.LAN_UPDATE_AVAILABLE.'</li>
						<li><a href="'.$installUrl.'">e107 v'.$cacheData.'</a></li>
						</ul>
						</li>
						</ul>
						';
				
				
			} 
			  
    }
   
    // {ADMIN_COREUPDATE}
    function sc_admin_coreupdate($parm='')
	{
        $che = e107::getCache();
        $mes = e107::getMessage();
        
        $che->setMD5(e_LANGUAGE);
    
        $cacheData = $che->retrieve("releasecheck",3600, TRUE); // 2.0.1 | 'up-to-date' | false ; 
    	
  		$cacheData = 2.1; // XXX Remove to test for real. 
    	
    	return false;  // XXX Remove to test for real. 
    	
        if($cacheData)
        {
            return $this->cronUpdateRender($parm, $cacheData); 
        }
       

        require_once(e_HANDLER."cron_class.php");
        $cron = new _system_cron();
        
        if($result = $cron->checkCoreUpdate())
        {
           return $this->cronUpdateRender($parm, $cacheData); 
        }
    
        
	}
	
	

	
	function sc_admin_credits()
	{
		if (!ADMIN) { return ''; }
		return "
		<div style='text-align: center'>
		<input class='btn btn-default btn-secondary button' type='button' onclick=\"javascript: window.open('".e_ADMIN_ABS."credits.php', 'myWindow', 'status = 1, height = 400, width = 300, resizable = 0')\" value='".LAN_CREDITS."' />
		</div>";
	}

	function sc_admin_docs()
	{
		if (!ADMIN) { return ''; }
		global $ns;
		$i=1;
		if (!$handle=opendir(e_DOCS.e_LANGUAGE.'/'))
		{
			$handle=opendir(e_DOCS.'English/');
		}
		while ($file = readdir($handle))
		{
			if($file != '.' && $file != '..' && $file != 'CVS')
			{
				$helplist[$i] = $file;
				$i++;
			}
		}
		closedir($handle);

		unset($e107_var);
		foreach ($helplist as $key => $value)
		{
			$e107_var['x'.$key]['text'] = str_replace('_', ' ', $value);
			$e107_var['x'.$key]['link'] = e_ADMIN.'docs.php?'.$key;
		}

		$text = show_admin_menu(FOOTLAN_14, $act, $e107_var, FALSE, TRUE, TRUE);
		return $ns -> tablerender(FOOTLAN_14,$text, array('id' => 'admin_docs', 'style' => 'button_menu'), TRUE);
	}

	function sc_adminui_help()
	{
		if (!ADMIN) { return ''; }

		if($tmp = e107::getRegistry('core/e107/adminui/help'))
		{
			return  e107::getRender()->tablerender($tmp['caption'],$tmp['text'],'e_help',true);
		}

		return null;
	}

	function sc_admin_help()
	{
		if (!ADMIN) { return ''; }
	
		$ns = e107::getRender();
		$pref = e107::getPref();
		$help_text = '';

	
		if(function_exists('e_help') && ($tmp =  e_help())) // new in v2.x for non-admin-ui admin pages. 
		{
			$ns->setUniqueId('sc-admin-help');
			$help_text = $ns->tablerender($tmp['caption'],$tmp['text'],'e_help',true);
		}

		if(e_PAGE === "menus.php") // quite fix to disable e107_admin/menus.php help file in all languages.
		{
			return $help_text;
		}


		$helpfile = '';
		
		if(strpos(e_SELF, e_ADMIN_ABS) !== false)
		{
			if (is_readable(e_LANGUAGEDIR.e_LANGUAGE.'/admin/help/'.e_PAGE))
			{
				$helpfile = e_LANGUAGEDIR.e_LANGUAGE.'/admin/help/'.e_PAGE;
			}
			elseif (is_readable(e_LANGUAGEDIR.'English/admin/help/'.e_PAGE))
			{
				$helpfile = e_LANGUAGEDIR.'English/admin/help/'.e_PAGE;
			}
		}
		else
		{
			$plugpath = getcwd().'/help.php'; // deprecated file. For backwards compat. only.
			$eplugpath = getcwd().'/e_help.php';
			if(is_readable($eplugpath))
			{
				$helpfile = $eplugpath;
			}
			elseif(is_readable($plugpath))
			{
				$helpfile = $plugpath;
			}
		}

		if(!empty($helpfile))
		{
			ob_start();
			include_once($helpfile);
			$help_text .= ob_get_contents();
			ob_end_clean();
		}

		return $help_text;
	}

	function sc_admin_icon()
	{
		$tp = e107::getParser();
		
		if (ADMIN)
		{
			global $e_sub_cat, $e_icon_array, $PLUGINS_DIRECTORY;
			
			$e_icon_array = e107::getNav()->getIconArray();
			
			if (deftrue('e_CURRENT_PLUGIN'))
			{
				$eplug_icon = '';
				$eplug_folder = e_CURRENT_PLUGIN.'/';
				if (is_readable(e_PLUGIN_DIR.'plugin.xml'))
				{
					$xml = e107::getXml();
					/**
					 *	@todo: folder and administration are deprecated. What replaces them?
					 * XXX removed folder (as not needed), admininstration[icon] should be replaced with 'icon' only (root xml var), looking in adminlinks for icons isn't that easy
					 */
					$xml->filter = array('folder' => FALSE, 'administration' => FALSE);		// Just need one variable
					$readFile = $xml->loadXMLfile(e_PLUGIN_DIR.'plugin.xml', 'advanced', true); 
					
					// TODO - the better way to go - simple!
					//$eplug_icon = $readFile['icon'];
					
					if(isset($readFile['adminLinks']['link']) && is_array($readFile['adminLinks']['link']))
					{
						foreach ($readFile['adminLinks']['link'] as $data) 
						{
							if(isset($data['@attributes']['primary']) && $data['@attributes']['primary'] && vartrue($data['@attributes']['icon']))
							{
								$eplug_icon = $data['@attributes']['icon'];
								break;
							}
						}
					}
				}
				elseif (is_readable('plugin.php'))
				{
					include('plugin.php');
				}
				else
				{
					$icon = $tp->toGlyph('e-cat_plugins-32');
					return $icon;
				}
				
				$icon = ($eplug_icon && file_exists(e_PLUGIN.$eplug_folder.$eplug_icon)) ? "<img src='".e_PLUGIN_ABS.$eplug_folder.$eplug_icon."' alt='' class='icon S32' />" : E_32_CAT_PLUG;
			}
			else
			{
				$icon = varset($e_icon_array[$e_sub_cat]);
			}
			return $icon;
		}
		else
		{
			return E_32_LOGOUT;
		}
	}

	function sc_admin_lang($parm)
	{
		if (!ADMIN || !e107::getPref('multilanguage')) { return ''; }
		
		$e107 = e107::getInstance();
		$sql = e107::getDb();
		$pref = e107::getPref();
		$ns = e107::getRender();
		
		e107::plugLan('user_menu', '', true);
		
		$params = array();
		parse_str($parm, $params);

		$lanlist = explode(',',e_LANLIST); 
		sort($lanlist);
		$text = '';

		$lanperms = array();

		foreach($lanlist as $langval)
		{
			if (getperms($langval))
			{
				$lanperms[] = $langval;
			}
		}

		$slng = e107::getLanguage();

		if(!getperms($sql->mySQLlanguage) && $lanperms)
		{
			$slng->set($lanperms[0]);
			if ($pref['user_tracking'] == "session" && $pref['multilanguage_subdomain'])
			{
				e107::getRedirect()->redirect($slng->subdomainUrl($lanperms[0]));
			}
			/*$sql->mySQLlanguage = ($lanperms[0] != $pref['sitelanguage']) ? $lanperms[0] : "";
			if ($pref['user_tracking'] == "session")
			{
				$_SESSION['e107language_'.$pref['cookie_name']] = $lanperms[0];
				if($pref['multilanguage_subdomain']){
					header("Location:".$slng->subdomainUrl($lanperms[0]));
				}
			}
			else
			{
				setcookie('e107language_'.$pref['cookie_name'], $lanperms[0], time() + 86400, '/');
				$_COOKIE['e107language_'.$pref['cookie_name']]= $lanperms[0];
			}*/
		}

		if(varset($GLOBALS['mySQLtablelist']))
		{
			foreach($GLOBALS['mySQLtablelist'] as $tabs)
			{
				$clang = strtolower($sql->mySQLlanguage);
				if(strpos($tabs,"lan_".$clang) && $clang !="")
				{
					$aff[] = str_replace(MPREFIX."lan_".$clang."_","",$tabs);
				}
			}
        }

		$text .= "
		<div>
		";
		if(isset($aff))
		{
			$text .= $sql->mySQLlanguage;
			$text .= " (".$slng->convert($sql->mySQLlanguage).")
			: <span class='btn btn-default btn-secondary button' style='cursor: pointer;' onclick='expandit(\"lan_tables\");'><a style='text-decoration:none' title='' href=\"javascript:void(0);\" >&nbsp;&nbsp;".count($aff)." ".UTHEME_MENU_L3."&nbsp;&nbsp;</a></span><br />
			<span style='display:none' id='lan_tables'>
			";
			$text .= implode('<br />', $aff);
			$text .= '</span>';
		}
		elseif($sql->mySQLlanguage && ($sql->mySQLlanguage != $pref['sitelanguage']))
		{
			$text .= $sql->mySQLlanguage;
			$text .= ' ('.$slng->convert($sql->mySQLlanguage).'): '.LAN_INACTIVE;
		}
		else
		{
			$text .= $pref['sitelanguage'];
		}
		$text .= "<br /><br /></div>";


		$select = '';
		if(isset($pref['multilanguage_subdomain']) && $pref['multilanguage_subdomain'])
		{
			// TODO - JS independent
			$select .= "
			<select class='tbox' name='lang_select' id='sitelanguage' onchange=\"location.href=this.options[selectedIndex].value\">";
			foreach($lanperms as $lng)
			{
				$selected = ($lng == $sql->mySQLlanguage || ($lng == $pref['sitelanguage'] && !$sql->mySQLlanguage)) ? " selected='selected'" : "";
				$urlval = $slng->subdomainUrl($lng);
				$select .= "<option value='".$urlval."' {$selected}>$lng</option>\n";
			}
			$select .= "</select>";

		}
		/*elseif(isset($params['nobutton']))
		{
			$select .= "
			<form method='post' action='".e_SELF.(e_QUERY ? '?'.e_QUERY : '')."'>
			<div>
			<select name='sitelanguage' id='sitelanguage' class='tbox' onchange=\"location.href=this.options[selectedIndex].value\">";
			foreach($lanperms as $lng)
			{
				$langval = e_SELF.'?['.$slng->convert($lng).']'.e_QUERY;
				$selected = ($lng == $sql->mySQLlanguage || ($lng == $pref['sitelanguage'] && !$sql->mySQLlanguage)) ? " selected='selected'" : "";
				$select .= "<option value='".$langval."'{$selected}>$lng</option>\n";
			}
			$select .= "</select>
			</div>
			</form>
			";
		}*/
		else
		{
			$select .= "
			<form method='post' action='".e_SELF.(e_QUERY ? '?'.e_QUERY : '')."'>
			<div>
			<select name='sitelanguage' id='sitelanguage' class='tbox' onchange='this.form.submit()'>";
			foreach($lanperms as $lng)
			{
				// FIXME - language detection is a mess - db handler, mysql handler, session handler and language handler + constants invlolved
				// Too complex, doesn't work!!! SIMPLIFY!!!
				//$langval = ($lng == $pref['sitelanguage'] && $lng == 'English') ? "" : $lng;
				//$selected = ($lng == $sql->mySQLlanguage || ($lng == $pref['sitelanguage'] && !$sql->mySQLlanguage)) ? " selected='selected'" : "";
				//$select .= "<option value='".$langval."'{$selected}>$lng</option>\n";
				$selected = ($lng == e_LANGUAGE) ? " selected='selected'" : "";
				$select .= "<option value='".$lng."'{$selected}>$lng</option>\n";
				
			}
			$select .= "</select> ".(!isset($params['nobutton']) ? "<button class='update e-hide-if-js' type='submit' name='setlanguage' value='no-value'><span>".UTHEME_MENU_L1."</span></button>" : '')."
			".e107::getForm()->hidden('setlanguage', '1')."
			</div>
			</form>
			";
		}

		if(isset($params['nomenu'])) { return $select; }
		if($select) { $text .= "<div class='center'>{$select}</div>"; }

		return $ns->tablerender(UTHEME_MENU_L2, $text, 'core-menu-lang', true);

	}

	function sc_admin_latest($parm)
	{
		if(($parm == 'infopanel' || $parm == 'flexpanel') && !deftrue('e_ADMIN_HOME'))
		{
			return null;
		}
		
		if (ADMIN) {
			if (!function_exists('admin_latest')) //XXX Is this still necessary?
			{
				function admin_latest($parm='')
				{
					
					$sql 	= e107::getDb();
					$ns 	= e107::getRender();
					$pref 	= e107::getPref();
					$mes 	= e107::getMessage();

					$active_uploads 	= $sql->count('upload', '(*)', 'WHERE upload_active = 0');
					$submitted_news 	= $sql->count('submitnews', '(*)', 'WHERE submitnews_auth = 0');
					$comments_pending 	= $sql->count("comments", "(*)", "WHERE comment_blocked = 2 ");

				//	$text = "<div class='left'><div style='padding-bottom: 2px;'>".E_16_NEWS.($submitted_news ? " <a href='".e_ADMIN."newspost.php?mode=sub&amp;action=list'>".ADLAN_LAT_2.": $submitted_news</a>" : ' '.ADLAN_LAT_2.': 0').'</div>';
				//	$text .= "<div style='padding-bottom: 2px;'>".E_16_COMMENT. " <a href='".e_ADMIN_ABS."comment.php?searchquery=&filter_options=comment_blocked__2'>".ADLAN_LAT_9.": $comments_pending</a></div>";		
		
			//		$text .= "<div style='padding-bottom: 2px;'>".E_16_UPLOADS." <a href='".e_ADMIN."upload.php'>".ADLAN_LAT_7.": $active_uploads</a></div>";

					$oldconfigs = array();
					$oldconfigs['e-news'][0] = array('icon'=>E_16_NEWS, 'title'=>ADLAN_LAT_2, 'url'=> e_ADMIN."newspost.php?mode=sub&amp;action=list", 'total'=>$submitted_news);

					if(empty($pref['comments_disabled']) && varset($pref['comments_engine'],'e107') == 'e107')
					{
						$oldconfigs['e-comment'][0] = array('icon'=>E_16_COMMENT, 'title'=>ADLAN_LAT_9, 'url'=> e_ADMIN_ABS."comment.php?searchquery=&filter_options=comment_blocked__2", 'total'=>$comments_pending);
					}

					$oldconfigs['e-upload'][0] = array('icon'=>E_16_UPLOADS, 'title'=>ADLAN_LAT_7, 'url'=> e_ADMIN."upload.php", 'total'=>$active_uploads);
				
					$messageTypes = array('Broken Download', 'Dev Team Message');
					$queryString = '';
					foreach($messageTypes as $types)
					{
						$queryString .= " gen_type='$types' OR";
					}
					$queryString = substr($queryString, 0, -3);

					if($amount = $sql->select('generic', '*', $queryString))
					{
					//	$text .= "<br /><b><a href='".e_ADMIN_ABS."message.php'>".ADLAN_LAT_8." [".$amount."]</a></b>";
						
						$oldconfigs['e-generic'][0] = array('icon'=>E_16_NOTIFY, 'title'=>ADLAN_LAT_8, 'url'=> e_ADMIN_ABS."message.php", 'total'=>$amount);
					}
				

					if(vartrue($pref['e_latest_list']))
					{
						foreach($pref['e_latest_list'] as $val)
						{
							$text = "";
							if (is_readable(e_PLUGIN.$val.'/e_latest.php'))
							{
								include_once(e_PLUGIN.$val.'/e_latest.php');
								if(!class_exists($val."_latest"))
								{
									$mes->addDebug("<strong>".$val ."</strong> using deprecated e_latest method");	
									$oldconfigs[$val] = admin_shortcodes::legacyToConfig($text);
								}
							}
						}
                    }

			
					$configs = e107::getAddonConfig('e_dashboard',null, 'latest');

					if(!is_array($configs))
					{
						$configs = array();
					}

					$allconfigs = array_merge($oldconfigs,$configs);	
					
					$allconfigs = multiarray_sort($allconfigs,'title'); //XXX FIXME - not sorting correctly. 
		
					$text = "<ul id='e-latest' class='list-group'>";
					foreach($allconfigs as $k=>$v)
					{
						foreach($v as $val)
						{
							$class = admin_shortcodes::getBadge($val['total']); 
							$link =  "<a  href='".$val['url']."'>".$val['icon']." ".str_replace(":"," ",$val['title'])." <span class='".$class."'>".$val['total']."</span></a>";
							$text .= "<li class='list-group-item clearfix'>".$link."</li>\n";
						}	
					}
					$text .= "</ul>";


				
				//	$text .= "</div>";
					$ns->setUniqueId('e-latest-list');
					return ($parm != 'norender') ? $ns -> tablerender(ADLAN_LAT_1, $text, '', TRUE) : $text;
				}
			}

			if ($parm == 'request')
			{
				if (function_exists('latest_request'))
				{
					if (latest_request())
					{
						return admin_latest($parm);
					}
				}
			}
			else
			{
				return admin_latest($parm);
			}
		}
	}

	function sc_admin_log($parm)
	{
		if (getperms('0'))
		{
			if (!function_exists('admin_log'))
			{
				function admin_log()
				{
					global $sql, $ns;
					$text = E_16_ADMINLOG." <a style='cursor: pointer' onclick=\"expandit('adminlog')\">".ADLAN_116."</a>\n";
					if (e_QUERY == 'logall')
					{
						$text .= "<div id='adminlog'>";
						$cnt = $sql ->select('admin_log', '*', "ORDER BY `dblog_datestamp` DESC", 'no_where');
					}
					else
					{
						$text .= "<div style='display: none;' id='adminlog'>";
						$cnt = $sql ->select('admin_log', '*', 'ORDER BY `dblog_datestamp` DESC LIMIT 0,10', 'no_where');
					}
					$text .= ($cnt) ? '<ul>' : '';
					$gen = e107::getDateConvert();
					while ($row = $sql ->fetch())
					{
						$datestamp = $gen->convert_date($row['dblog_datestamp'], 'short');
						$text .= "<li>{$datestamp} - {$row['dblog_title']}</li>";
					}
					$text .= ($cnt ? '</ul>' : '');
					$text .= "[ <a href='".e_ADMIN_ABS."admin_log.php?adminlog'>".ADLAN_117."</a> ]";
					$text .= "<br />[ <a href='".e_ADMIN_ABS."admin_log.php?config'>".ADLAN_118."</a> ]";

					//			$text .= "<br />[ <a href='".e_ADMIN."admin_log.php?purge' onclick=\"return jsconfirm('".LAN_CONFIRMDEL."')\">".ADLAN_118."</a> ]\n";

					$text .= "</div>";

					return $ns -> tablerender(ADLAN_135, $text, '', TRUE);
				}
			}

			if ($parm == 'request')
			{
				if (function_exists('log_request'))
				{
					if (log_request())
					{
						return admin_log();
					}
				}
			}
			else
			{
				return admin_log();
			}
		}
	}

	function sc_admin_logged($parm='')
	{
		if (ADMIN)
		{
			$str = str_replace('.', '', ADMINPERMS);
			if (ADMINPERMS == '0')
			{
				return '<b>'.ADLAN_48.':</b> '.ADMINNAME.' ('.ADLAN_49.') '.( defined('e_DBLANGUAGE') ? '<b>'.LAN_HEADER_05.'</b>: '.e_DBLANGUAGE : '' );
			}
			else
			{
				return '<b>'.ADLAN_48.':</b> '.ADMINNAME.' '.( defined('e_DBLANGUAGE') ? '<b>'.LAN_HEADER_05.'</b>: '.e_DBLANGUAGE : '' );
			}
		}
		else
		{
			return ADLAN_51.' ...';
		}
	}

	function sc_admin_logo($parm)
	{
		parse_str($parm);

		if (isset($file) && $file && is_readable($file))
		{
			$logo = $file;
			$path = $file;
		}
		else if (is_readable(THEME.'images/e_adminlogo.png'))
		{
			$logo = THEME_ABS.'images/e_adminlogo.png';
			$path = THEME.'images/e_adminlogo.png';
		}
		else
		{
			$logo = e_IMAGE_ABS.'adminlogo.png';
			$path = e_IMAGE.'adminlogo.png';
		}

		$dimensions = getimagesize($path);

		$image = "<img class='logo admin_logo' src='".$logo."' style='width: ".$dimensions[0]."px; height: ".$dimensions[1]."px' alt='".ADLAN_153."' />\n";

		if (isset($link) && $link)
		{
			if ($link == 'index')
			{
				$image = "<a href='".e_ADMIN_ABS."index.php'>".$image.'</a>';
			}
			else
			{
				$image = "<a href='".$link."'>".$image.'</a>';
			}
		}
		return $image;
	}

	function sc_admin_menu($parm)
	{
		if (!ADMIN)
		{
			return '';
		}
		global $ns, $pref;

		// SecretR: NEW v0.8
		$tmp = e107::getAdminUI();
		if($tmp)
		{
			ob_start();
			// FIXME - renderMenu(), respectively e_adm/in_menu() should return, not output content!
			$tmp->renderMenu();
			$ret = ob_get_contents();
			ob_end_clean();
			return $ret;
		}
		unset($tmp);


		$curScript = basename($_SERVER['SCRIPT_FILENAME']);

		// Obsolete
		ob_start();
		//Show upper_right menu if the function exists
		$tmp = explode('.',$curScript);
        $adminmenu_parms = "";

		$adminmenu_func = $tmp[0].'_adminmenu';
		if(function_exists($adminmenu_func))
		{
			if (!$parm)
			{
				call_user_func($adminmenu_func,$adminmenu_parms);   // ? not sure why there's an adminmenu_parms;
			}
			else
			{
				ob_end_clean();
				return 'pre';
			}
		}
		$plugindir = (str_replace('/','',str_replace('..', '', e_PLUGIN)).'/');

		// FIXME @TODO $plugPath is using the URL to detect the path. It should use $_SERVER['SCRIPT_FILENAME']
		$plugpath = e_PLUGIN.str_replace(basename(e_SELF),'',str_replace('/'.$plugindir,'','/'.strstr(e_SELF,$plugindir))).'admin_menu.php';

		$action = e_QUERY; // required.

		if(file_exists($plugpath))
		{
			if (!$parm)
			{
				@require_once($plugpath);
			}
			else
			{
				ob_end_clean();
				return 'pre';
			}
		}
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}


	function sc_admin_debug()
	{
		if(e_DEBUG !== false)
		{
			return "<div class='navbar-right nav-admin navbar-text admin-icon-debug' title='DEBUG MODE ACTIVE'>".e107::getParser()->toGlyph('fa-bug', array('class'=>'text-warning'))."&nbsp;&nbsp;</div>";
		}

	}




	// FIXME - make it work
	function sc_admin_pm($parm)
	{
		if(!e107::isInstalled('pm')) return;
        
        $sql = e107::getDb();
		$tp = e107::getParser();
		
        $count =  $sql->count('private_msg','(*)','WHERE pm_read = 0 AND pm_to='.USERID);
       
       if ($count >0)
       {
            $countDisp = ' <span class="badge badge-primary">'.$count.'</span> ' ;
       }
       else
      {
            $countDisp = '';    
      }
         
		$inboxUrl = e_PLUGIN.'pm/admin_config.php?mode=inbox&amp;action=list&amp;iframe=1';
		$outboxUrl = e_PLUGIN.'pm/admin_config.php?mode=outbox&amp;action=list&amp;iframe=1';
		$composeUrl = e_PLUGIN.'pm/admin_config.php?mode=outbox&amp;action=create&amp;iframe=1';

       $text = '<ul class="nav nav-admin navbar-nav navbar-right">
        <li class="dropdown">
            <a class="dropdown-toggle" title="'.LAN_PM.'" role="button" data-toggle="dropdown" href="#" >
                '.$tp->toGlyph('fa-envelope').$countDisp.'
            </a> 
            <ul class="dropdown-menu" role="menu" >
                <li class="nav-header navbar-header dropdown-header">'.LAN_PM.'</li>
                    <li><a class="e-modal" data-cache="false" data-modal-caption="'.LAN_PLUGIN_PM_INBOX.'" data-target="#uiModal" href="'.$inboxUrl.'" >'.LAN_PLUGIN_PM_INBOX.'</a></li>
                    <li><a class="e-modal" data-cache="false" data-modal-caption="'.LAN_PLUGIN_PM_OUTBOX.'" data-target="#uiModal" href="'.$outboxUrl.'">'.LAN_PLUGIN_PM_OUTBOX.'</a></li>
                    <li><a class="e-modal" data-cache="false" data-modal-caption="'.LAN_PM_35.'" data-target="#uiModal" href="'.$composeUrl.'">'.LAN_PM_35.'</a></li>
                </ul>
        </li>
        </ul>
        '; 
        
        return $text;
        
      //  e107_plugins/pm/pm.php
        
        
        
       /*
        
		$text = '
		<li class="dropdown">
			<a class="dropdown-toggle" title="Messages" role="button" data-toggle="dropdown" href="#" >
				<i class="icon-envelope icon-white active"></i> 3 <b class="caret"></b>
			</a> 
			<div id="dropdown" class="dropdown-menu pull-right e-noclick" style="padding:10px;width:300px">
				<ul class="nav-list">
		    		<li class="nav-header navbar-header">Unread Messages</li>
		    		<li><a href="#">Incoming Message Number 1</a></li>
		      		<li><a href="#">Incoming Message Number 2</a></li>
		        	<li><a href="#">Incoming Message Number 3</a></li>
		         	<li role="separator" class="divider"></li>
		   		</ul>
				<textarea class="e-tip input-block-level" title="Example Only"></textarea>
				<button class="dropdown-toggle btn btn-primary">Send</button>	
			</div>
		</li>
		';

		return $text;
       */
	}


	function sc_admin_multisite($parm=null)
	{
		$file = e_SYSTEM_BASE."multisite.json";

		if(!getperms('0') || !file_exists($file))
		{
			return null;
		}

		$tp = e107::getParser();
		$parsed = file_get_contents($file);
		$tmp = e107::unserialize($parsed);

		if(!defined('e_MULTISITE_MATCH'))
		{
			define('e_MULTISITE_MATCH', null);
		}
	//	e107::getDebug()->log($tmp);

		  $text = '<ul class="nav nav-admin navbar-nav navbar-right">
        <li class="dropdown">
            <a class="dropdown-toggle" title="Multisite" role="button" data-toggle="dropdown" href="#" >
                '.$tp->toGlyph('fa-clone').'
            </a> 
            <ul class="dropdown-menu" role="menu" >';

			$srch = array();
			foreach($tmp as $k=>$val)
            {
                $srch[] = '/'.$val['match'].'/';
            }

            foreach($tmp as $k=>$val)
            {
				$active = (e_MULTISITE_MATCH === $val['match']) ? ' class="active"' : '';
				$url = str_replace($srch,'/'.$val['match'].'/',e_REQUEST_SELF);
                $text .= '<li '.$active.'><a href="'.$url.'">'.$val['name'].'</a></li>';
            }

                $text .= '
             </ul>
        </li>
        </ul>
        ';

		// e107::getDebug()->log(e_MULTISITE_IN_USE);

        return $text;

	}


	function sc_admin_msg($parm)
	{
		if (ADMIN)
		{
			if(!FILE_UPLOADS)
			{
				return e107::getRender()->tablerender(LAN_WARNING,LAN_HEADER_02,'admin_msg',true);
			}
		}
	}

	function sc_admin_nav($parm)
	{
		if (ADMIN)
		{
			global $ns, $pref, $array_functions, $tp;
			$e107_var = array();

			if (strstr(e_SELF, '/admin.php'))
			{
				$active_page = 'x';
			}
			else
			{
				$active_page = time();
			}
			$e107_var['x']['text'] = ADLAN_52;
			$e107_var['x']['link'] = e_ADMIN_ABS.'admin.php';
			$e107_var['y']['text'] = ADLAN_53;
			$e107_var['y']['link'] = e_HTTP."index.php";

			//$text .= show_admin_menu("",$active_page,$e107_var);
			$e107_var['afuncs']['text'] = ADLAN_93;
			$e107_var['afuncs']['link'] = '';

			/* SUBLINKS */
			$tmp = array();
			foreach ($array_functions as $links_key => $links_value)
			{
				$tmp[$links_key]['text'] = $links_value[1];
				$tmp[$links_key]['link'] = $links_value[0];
			}
			$e107_var['afuncs']['sub'] = $tmp;
			/* SUBLINKS END */

			// Plugin links menu
			$xml = e107::getXml();
			$xml->filter = array('@attributes' => FALSE, 'administration' => FALSE);	// .. and they're all going to need the same filter

			$nav_sql = new db;
			if ($nav_sql ->select('plugin', '*', 'plugin_installflag=1'))
			{
				$tmp = array();
				$e107_var['plugm']['text'] = ADLAN_95;
				$e107_var['plugm']['link'] = '';

				/* SUBLINKS */
				//Link Plugin Manager
				$tmp['plugm']['text'] = '<strong>'.ADLAN_98.'</strong>';
				$tmp['plugm']['link'] = e_ADMIN.'plugin.php';
				$tmp['plugm']['perm'] = 'P';

				while($rowplug = $nav_sql ->fetch())
				{
					$plugin_id = $rowplug['plugin_id'];
					$plugin_path = $rowplug['plugin_path'];
					if (is_readable(e_PLUGIN.$plugin_path.'/plugin.xml'))
					{
						$readFile = $xml->loadXMLfile(e_PLUGIN.$plugin_path.'/plugin.xml', true, true);
					//	e107::loadLanFiles($plugin_path, 'admin');
						$eplug_caption 	= $tp->toHTML($readFile['@attributes']['name'], FALSE, 'defs, emotes_off');
						$eplug_conffile = $readFile['administration']['configFile'];
					}
					elseif (is_readable(e_PLUGIN.$plugin_path.'/plugin.php'))
					{
						include(e_PLUGIN.$plugin_path.'/plugin.php');
					}

					// Links Plugins
					if ($eplug_conffile)
					{
						$tmp['plug_'.$plugin_id]['text'] = $eplug_caption;
						$tmp['plug_'.$plugin_id]['link'] = e_PLUGIN.$plugin_path.'/'.$eplug_conffile;
						$tmp['plug_'.$plugin_id]['perm'] = 'P'.$plugin_id;
					}
					unset($eplug_conffile, $eplug_name, $eplug_caption);
				}
				$e107_var['plugm']['sub'] = $tmp;
				$e107_var['plugm']['sort'] = true;
				/* SUBLINKS END */
				//$text .= show_admin_menu(ADLAN_95, time(), $e107_var, FALSE, TRUE, TRUE);
				unset($tmp);
			}

			$e107_var['lout']['text']=LAN_LOGOUT;
			$e107_var['lout']['link']=e_ADMIN_ABS.'admin.php?logout';

			$text = e_admin_menu('', '', $e107_var);
			return $ns->tablerender(LAN_HEADER_01, $text, array('id' => 'admin_nav', 'style' => 'button_menu'), TRUE);
		}
	}

	function sc_admin_plugins($parm)
	{
		if (ADMIN)
		{
			global $e107_plug, $ns, $pref;
			if ($pref['admin_alerts_ok'] == 1)
			{
				ob_start();
				$text = "";
				$i = 0;
				if (strstr(e_SELF, '/admin.php'))
				{
					global $sql;
					if ($sql ->select('plugin', '*', 'plugin_installflag=1'))
					{
						while($rowplug = $sql->fetch())
						{
							extract($rowplug);
							if(varset($rowplug[1]))
							{
								$e107_plug[$rowplug[1]] = varset($rowplug[3]);
							}

						}
					}
				}
				if (is_array($e107_plug))
				{
					foreach(array_keys($e107_plug) as $xplug)
					{
						if (file_exists(e_PLUGIN.$e107_plug[$xplug].'/admin_info.php'))
						{
							if ($pref['admin_alerts_uniquemenu'] == 1)
							{
								$text .= '<b>'.$xplug.'</b><br />';
							}
							else
							{
								$text = '';
							}
							require_once(e_PLUGIN.$e107_plug[$xplug].'/admin_info.php');
							$text .= '<br />';
							if ($pref['admin_alerts_uniquemenu'] != 1)
							{
								$caption = $xplug;
								$ns->tablerender($caption, $text);
							}
							else
							{
								$text .= "<br />";
							}
							$i++;
						}
					}
				}

				$caption = LAN_HEADER_06;
				if ($i>0 && $pref['admin_alerts_uniquemenu'] == 1)
				{
					$ns -> tablerender($caption, $text);
				}
				$plug_text = ob_get_contents();
				ob_end_clean();
				return $plug_text;
			}
		}
	}

	function sc_admin_preset($parm)
	{
		//DEPRECATED
	}

	function sc_admin_pword()
	{

		if(strpos(e_REQUEST_URI,e_ADMIN_ABS."menus.php") !==false)
		{
			return false;
		}


		global $pref;
		if (ADMIN && ADMINPERMS == '0')
		{
			global $ns;
			if ($pref['adminpwordchange'] && ((ADMINPWCHANGE+2592000) < time()))
			{
				$text = "<div style='mediumtext; text-align:center'>".ADLAN_102." <a href='".e_ADMIN."updateadmin.php'>".ADLAN_103.'</a></div>';
				$ns->setUniqueId('e-password-change');
				return $ns -> tablerender(ADLAN_104, $text, '', true);
			}
		}
	}

	function sc_admin_sel_lan()
	{
		global $pref;
		if (ADMIN && $pref['multilanguage'])
		{
			$language = ($pref['sitelanguage'] == e_LANGUAGE) ? ADLAN_133 : e_LANGUAGE;
			return ' <strong>'.ADLAN_132.'</strong> '.$language;
		}
	}

	function sc_admin_siteinfo($parm='')
	{
		if($parm == 'creditsonly' && e_PAGE != "credits.php"  && e_PAGE != "phpinfo.php" && e_PAGE != 'e107_update.php')
		{
			return null;
		}	
		
		
		if (ADMIN)
		{
			global $ns, $pref, $themename, $themeversion, $themeauthor, $themedate, $themeinfo, $mySQLdefaultdb;

		//	if (file_exists(e_ADMIN.'ver.php'))
			{
			//	include(e_ADMIN.'ver.php');
			}
			
			if($parm == "version")
			{
				return e_VERSION;
			}

			$obj = e107::getDateConvert();
			$install_date = $obj->convert_date($pref['install_date'], 'long');
			
			if(is_readable(THEME."theme.xml"))
			{
				$xml = e107::getXml();
				$data = $xml->loadXMLfile(THEME."theme.xml",true);
			
				$themename = $data['@attributes']['name'];
				$themeversion = $data['@attributes']['version'];
				$themedate = $data['@attributes']['date'];
				$themeauthor = $data['author']['@attributes']['name'];			
			}
			
			$text = "<b>".FOOTLAN_1."</b>
			<br />".
			SITENAME."
			<br /><br />
			<b>".FOOTLAN_2."</b>
			<br />
			<a href=\"mailto:".SITEADMINEMAIL."\">".SITEADMIN."</a>
			<br />
			<br />
			<b>e107</b>
			<br />
			".FOOTLAN_3." ".e_VERSION."
			<br /><br />
			<b>".FOOTLAN_20."</b>
			<br />
			[".e_SECURITY_LEVEL."] ".defset('LAN_SECURITYL_'.e_SECURITY_LEVEL, 'n/a')." 
			<br /><br />
			<b>".FOOTLAN_18."</b>
			<br />".$pref['sitetheme']."<br /><br />
			<b>".FOOTLAN_5."</b>
			<br />
			".$themename." v".$themeversion." ".($themeauthor ? FOOTLAN_6.' '.$themeauthor : '')." ".($themedate ? "(".$themedate.")" : "");

			$text .= $themeinfo ? "<br />".FOOTLAN_7.": ".$themeinfo : '';

			$sqlMode = str_replace(",", ", ",e107::getDB()->getMode());

			$text .= "<br /><br />
			<b>".FOOTLAN_8."</b>
			<br />
			".$install_date."
			<br />";

			$text .= $this->getLastGitUpdate();

			$text .= "<br />
			<b>".FOOTLAN_9."</b>
			<br />".
			preg_replace("/PHP.*/i", "", $_SERVER['SERVER_SOFTWARE'])."<br />(".FOOTLAN_10.": ".$_SERVER['SERVER_NAME'].")
			<br /><br />
			<b>".FOOTLAN_11."</b>
			<br />
			".phpversion()."
			<br /><br />
			<b>".FOOTLAN_12."</b>
			<br />
			".e107::getDB()->getServerInfo(). // mySqlServerInfo.

			"<br />".FOOTLAN_16.": ".$mySQLdefaultdb."
			<br />PDO: ".((e107::getDB()->getPDO() === true) ? LAN_ENABLED : LAN_DISABLED)."
			<br />Mode: <small>".$sqlMode."</small>

			<br /><br />
			<b>".FOOTLAN_17."</b>
			<br />utf-8
			<br /><br />
			<b>".FOOTLAN_19."</b>
			<br />
			".date('r').
			"<br />";

			return $ns->tablerender(FOOTLAN_13, $text, '', TRUE);
		}
	}

	private function getLastGitUpdate()
	{
		$gitFetch = e_BASE.'.git/FETCH_HEAD';

		if(file_exists($gitFetch))
		{
			$unix = filemtime($gitFetch);

			$text = "<br /><b>Last Git Update</b><br />"; // NO LAN required. Developer-Only
			$text.= ($unix) ? date('r',$unix)  : "Never";
			$text .= "<br />";
			return $text;
		}

	}

	function sc_admin_status($parm)
	{
		if(($parm == 'infopanel' || $parm == 'flexpanel') && !deftrue('e_ADMIN_HOME'))
		{
			return;
		}
				
		if (getperms('0') || getperms('4'))
		{
			if (!function_exists('admin_status')) //XXX Discuss.. do we still need to embed this function within the shortcode?
			{
				function admin_status($parm='')
				{
					$mes = e107::getMessage();
					$sql = e107::getDb();
					$ns = e107::getRender();
					$pref = e107::getPref();
					
					
					$members 		= $sql->count('user', '(*)', 'WHERE user_ban=0');
					$unverified 	= $sql->count('user', '(*)', 'WHERE user_ban=2');
					$banned 		= $sql->count('user', '(*)', 'WHERE user_ban=1');
					$comments 		= $sql->count('comments');

					/*
					$unver = ($unverified ? " <a href='".e_ADMIN."users.php?searchquery=&amp;filter_options=user_ban__2&amp;filter=unverified'> ".ADLAN_111.": {$unverified}</a>" : ADLAN_111);
					$lban = ($banned) ? "<a href='".e_ADMIN_ABS."users.php??searchquery=&filter_options=user_ban__1&filter=banned'>".ADLAN_112. ": ".$banned."</a>" : ADLAN_112.":";
					$lcomment = ($comments) ? "<a href='".e_ADMIN_ABS."comment.php'>".ADLAN_114.": ".$comments."</a>" : ADLAN_114;
					
					$text = "
					<div class='left'>
						<div style='padding-bottom: 2px;'>". E_16_USER." <a href='".e_ADMIN_ABS."users.php?filter=0'>".ADLAN_110.": ".$members."</a></div>
						<div style='padding-bottom: 2px;'>".E_16_USER." {$unver}</div>
						<div style='padding-bottom: 2px;'>".E_16_BANLIST." ".$lban."</div>
						<div style='padding-bottom: 2px;'>".E_16_COMMENT." ".$lcomment."</div>\n\n";
						
					*/
					// for BC only. 	
	
					
					$oldconfigs['e-user'][0] 		= array('icon'=>E_16_USER, 'title'=>ADLAN_110, 'url'=> e_ADMIN_ABS."users.php?searchquery=&amp;filter_options=user_ban__0", 'total'=>$members, 'invert'=>1);
					$oldconfigs['e-user'][1] 		= array('icon'=>E_16_USER, 'title'=>ADLAN_111, 'url'=> e_ADMIN."users.php?searchquery=&amp;filter_options=user_ban__2", 'total'=>$unverified);
					$oldconfigs['e-user'][2] 		= array('icon'=>E_16_BANLIST, 'title'=>ADLAN_112, 'url'=> e_ADMIN."users.php?searchquery=&filter_options=user_ban__1", 'total'=>$banned);


					if(empty($pref['comments_disabled']) && varset($pref['comments_engine'],'e107') == 'e107')
					{
						$oldconfigs['e-comments'][0] 	= array('icon'=>E_16_COMMENT, 'title'=>LAN_COMMENTS, 'url'=> e_ADMIN_ABS."comment.php", 'total'=>$comments);
					}
					if($flo = $sql->count('generic', '(*)', "WHERE gen_type='failed_login'"))
					{
						//$text .= "\n\t\t\t\t\t<div style='padding-bottom: 2px;'>".E_16_FAILEDLOGIN." <a href='".e_ADMIN_ABS."fla.php'>".ADLAN_146.": $flo</a></div>";	
						$oldconfigs['e-failed'][0]	= array('icon'=>E_16_FAILEDLOGIN, 'title'=>ADLAN_146, 'url'=>e_ADMIN_ABS."banlist.php?mode=failed&action=list", 'total'=>$flo);
					}

					if($emls = $sql->count('mail_recipients', '(*)', "WHERE mail_status = 13"))
					{
						//$text .= "\n\t\t\t\t\t<div style='padding-bottom: 2px;'>".E_16_FAILEDLOGIN." <a href='".e_ADMIN_ABS."fla.php'>".ADLAN_146.": $flo</a></div>";
						$oldconfigs['e-mailout'][0]	= array('icon'=>E_16_MAIL, 'title'=>ADLAN_167, 'url'=>e_ADMIN_ABS."mailout.php?mode=pending&action=list", 'total'=>$emls);
					}
					
					
					
					if(vartrue($pref['e_status_list']))
					{
						foreach($pref['e_status_list'] as $val)
						{
							$text = "";
							if (is_readable(e_PLUGIN.$val.'/e_status.php'))
							{
								
								include_once(e_PLUGIN.$val.'/e_status.php');
								if(!class_exists($val."_status"))
								{
									$mes->addDebug("<strong>".$val ."</strong> using deprecated e_status method. See the chatbox plugin folder for a working example of the new one. ");	
								}
								
								$oldconfigs[$val] = admin_shortcodes::legacyToConfig($text);
							}
						}
					}
								
					// New in v2.x
				//	$configs = e107::getAddonConfig('e_status');
					$configs = e107::getAddonConfig('e_dashboard',null, 'status');
		
					if(!is_array($configs))
					{
						$configs = array();	
					}

					$allconfigs = array_merge($oldconfigs,$configs);	
					
					$allconfigs = multiarray_sort($allconfigs,'title'); //XXX FIXME - not sorting correctly. 
		
					$text = "<ul id='e-status' class='list-group'>";
					foreach($allconfigs as $k=>$v)
					{
						foreach($v as $val)
						{
							$type = empty($val['invert']) ? 'latest' : 'invert';
							$class = admin_shortcodes::getBadge($val['total'], $type);
							$link =  "<a href='".$val['url']."'>".$val['icon']." ".str_replace(":"," ",$val['title'])." <span class='".$class."'>".$val['total']."</span></a>";
							$text .= "<li class='list-group-item clearfix'>".$link."</li>\n";
						}	
					}
					$text .= "</ul>";

					if($parm == 'list')
					{
					//	$text = str_replace("<div style='padding-bottom: 2px;'>","<li>",$text);;	
					}
					
				//	$text .= "\n\t\t\t\t\t</div>";
					
					$ns->setUniqueId('e-status-list');
					return ($parm != 'norender') ? $ns -> tablerender(LAN_STATUS, $text, '', TRUE) : $text;
				}
			}

			if ($parm == 'request')
			{
				if (function_exists('status_request'))
				{
					if (status_request())
					{
						return admin_status($parm);
					}
				}
			}
			else
			{
				return admin_status($parm);
			}
		}
	}

	static function getBadge($total, $type = 'latest')
	{
		
		/*
		 *  	<span class="badge">1</span>
Success 	2 	<span class="badge badge-success">2</span>
Warning 	4 	<span class="badge badge-warning">4</span>
Important 	6 	<span class="badge badge-important">6</span>
Info 	8 	<span class="badge badge-info">8</span>
Inverse 	10 	<span class="badge badge-inverse">10</span>
		 */
		 if($type != 'invert')
		 {
			 $important = 'label-important label-danger';
			 $warning   = 'label-warning';
			 $info      = 'label-primary';
			 $invert = false;
		 }
		 else // invert
		 {
			 $info      = 'label-important label-danger';
			 $warning   = 'label-warning';
			 $important = 'label-primary';
			 $type = 'latest';
			 $invert = true;
		 }

		
		$class = 'label ';

		if($total > 500 && $invert == true)
		{
			$class .= 'label-success';
		}
		elseif($total > 100 && $type == 'latest')
		{
			$class .= $important;
		}
		elseif($total > 50 && $type == 'latest')
		{
			$class .= $warning;
		}
		elseif($total > 0)
		{
			$class .= $info;
		}

		if(deftrue('BOOTSTRAP') !== 3)
		{
			$class = str_replace('label', 'badge', $class);
		}
		
		return $class;		
	}


	/**
	 * Attempt to Convert Old $text string into new array format (e_status and e_latest)
	 */
	static function legacyToConfig($text)
	{
		$var = array();
		preg_match_all('/(<img[^>]*>)[\s]*<a[^>]href=(\'|")([^\'"]*)(\'|")>([^<]*)<\/a>[: ]*([\d]*)/is',$text, $match);
		foreach($match[5] as $k=>$m)
		{
			$var[$k]['icon'] 	= $match[1][$k];
			$var[$k]['title'] 	= $match[5][$k];
			$var[$k]['url']		= $match[3][$k];
			$var[$k]['total'] 	= $match[6][$k];
		}
		return $var;
			
	}
			
	function sc_admin_addon_updates()
	{
		if(!getperms('0') || !deftrue('e_ADMIN_HOME'))
		{
			return null;
		}

		$res = e107::getSession()->get('addons-update-status');

		if($res !== null)
		{
			return $res;
		}

		return "<div id='e-admin-addons-update'><!-- --></div>";

/*

		e107::getDb()->db_mark_time("sc_admin_addon_updates() // start");

		$themes = $this->getUpdateable('theme');
		$plugins = $this->getUpdateable('plugin');

		$text = $this->renderAddonUpdate($plugins);
		$text .= $this->renderAddonUpdate($themes);

		if(empty($text))
		{
			return null;
		}
		$ns = e107::getRender();

		$tp = e107::getParser();
		$ns->setUniqueId('e-addon-updates');

		e107::getDb()->db_mark_time("sc_admin_addon_updates() // end");





		return $ns->tablerender($tp->toGlyph('fa-arrow-circle-o-down').LAN_UPDATE_AVAILABLE,$text,'default',true);*/


	}


	public function getUpdateable($type)
	{

		if(empty($type))
		{
			return false;
		}

		require_once(e_HANDLER.'e_marketplace.php');
		$mp = new e_marketplace(); // autodetect the best method

		switch($type)
		{
			case "theme":
				$versions = $mp->getVersionList('theme');
				$list = e107::getTheme()->getList('version');
				break;

			case "plugin":
				$versions = $mp->getVersionList('plugin');
				$list = e107::getPref('plug_installed');
				break;
		}

		$ret = array();

		foreach($list as $folder=>$version)
		{

			if(!empty($versions[$folder]['version']) && version_compare( $version, $versions[$folder]['version'], '<'))
			{
				$versions[$folder]['modalDownload'] = $mp->getDownloadModal($type, $versions[$folder]);
				$ret[] = $versions[$folder];
				e107::getMessage()->addDebug("Local version: ".$version." Remote version: ".$versions[$folder]['version']);
			}

		}

		return $ret;

	}



	public function renderAddonUpdate($list)
	{

		if(empty($list))
		{
			return null;
		}


		$tp = e107::getParser();
		$text = '<ul class="media-list">';
		foreach($list as $row)
		{

			$caption = LAN_DOWNLOAD.": ".$row['name']." ".$row['version'];

			$ls = '<a href="'.$row['modalDownload'].'" class="e-modal alert-link" data-modal-caption="'.$caption .'" title="'.LAN_DOWNLOAD.'">';
			$le = '</a>';

			$thumb = ($row['icon']) ? $row['icon'] : $row['thumbnail'];

			$text .= '
			  <li class="media">
			    <div class="media-left">
			      '.$ls.'
			        <img class="media-object" src="'.$thumb.'" width="96">
			      '.$le.'
			    </div>
			    <div class="media-body">
			      <h4 class="media-heading">'.$ls.$row['name'].$le.'</h4>
			      <p>'.$row['version'].'<br />
			       <small class="text-muted">'.LAN_RELEASED.': '.($row['date']).'</small>
			       </p>

			    </div>
			  </li>
			';

		}


		$text .= "</ul>";


		return $text;
	}


	function sc_admin_update()
	{
		if (!ADMIN) { return null; }

		$pref = e107::getPref();

		if(empty($pref['check_updates']))
		{
			return null;
		}

		$cacheTag = 'Update_core';

		if(!$cached = e107::getCache()->retrieve($cacheTag, 1440, true, true))
		{
			e107::getDebug()->log("Checking for Core Update");
			$status = e107::coreUpdateAvailable();

			if($status === false)
			{
				$status = array('status'=>'not needed');
			}

			$cached =  e107::serialize($status,'json');
			e107::getCache()->set_sys($cacheTag, $cached,true,true);
		}
		else
		{
			e107::getDebug()->log("Using Cached Core Update Data");

		}

		$data = e107::unserialize($cached);

		if($data === false || isset($data['status']))
		{
			return null;
		}


		// Don't check for updates if running locally (comment out the next line to allow check - but
		// remember it can cause delays/errors if its not possible to access the Internet
		if(e_DEBUG !== true)
		{
			if ((strpos(e_SELF,'localhost') !== false) || (strpos(e_SELF,'127.0.0.1') !== false)) { return null; }
		}


		return '<ul class="core-update-available nav navbar-nav navbar-left">
        <li class="dropdown open">
            <a class="dropdown-toggle " title="Core Update Available" role="button" data-toggle="dropdown" href="#" aria-expanded="true">
                <i class="fa fa-cloud-download  text-success"></i>
            </a>
            <ul class="dropdown-menu" role="menu">
                <li class="nav-header navbar-header dropdown-header">'.e107::getParser()->lanVars(LAN_NEWVERSION,$data['version']).'</li>
                    <li><a href="'.$data['url'].'" rel="external"><i class="fa fa-download" ></i> '.LAN_DOWNLOAD.'</a></li>
                    <li><a href="'.$data['infourl'].'" rel="external"><i class="fa fa-file-text-o "></i> Release Notes</a></li>
                </ul>
        </li>
        </ul>';


	}

	// Does actually the same than ADMIN_SEL_LAN
	function sc_admin_userlan()
	{
		/*
		if (isset($_COOKIE['userlan']) && $_COOKIE['userlan'])
		{
			return ' <b>Language:</b> '.$_COOKIE['userlan'];
		}
		*/
	}


	
	/**
	 * Old Admin Navigation Routine.
	 */
	function sc_admin_navigationOld($parm=null)
	{
	
		if (!ADMIN) return '';
	//	global $admin_cat, $array_functions, $array_sub_functions, $pref;
		
		$pref = e107::getPref();

		$admin_cat 				= e107::getNav()->adminCats();		
		$array_functions 		= e107::getNav()->adminLinks('legacy');
		$array_sub_functions	= e107::getNav()->adminLinks('sub');	
		

		$tp 	= e107::getParser();
		$e107	= e107::getInstance();
		$sql	= e107::getDb('sqlp');

		parse_str($parm, $parms);
		$tmpl = strtoupper(varset($parms['tmpl'], 'E_ADMIN_NAVIGATION'));
		global $$tmpl;
		
		if($parm == self::ADMIN_NAV_HOME || $parm == self::ADMIN_NAV_LOGOUT || $parm == self::ADMIN_NAV_LANGUAGE || $parm == 'pm')
		{
			$template = $$tmpl;

			$template['start'] = $template['start_other'];

			$menu_vars = $this->getOtherNav($parm);	
			return e107::getNav()->admin('', '', $menu_vars, $template, FALSE, FALSE);
		}
        

        
		// MAIN LINK
		if($parm != 'no-main')
		{
			$menu_vars = array();
			$menu_vars['adminhome']['text'] = ADLAN_151;
			$menu_vars['adminhome']['link'] = e_ADMIN_ABS.'admin.php';
			$menu_vars['adminhome']['image'] = "<img src='".E_16_NAV_MAIN."' alt='".ADLAN_151."' class='icon S16' />";
			$menu_vars['adminhome']['image_src'] = ADLAN_151;
			$menu_vars['adminhome']['perm'] = '';
		}

		//ALL OTHER ROOT LINKS - temporary data transformation - data structure will be changed in the future and this block will be removed

  		foreach($admin_cat['id'] as $i => $cat)
		{
			
			$id = $admin_cat['id'][$i];
			$menu_vars[$id]['text'] = $admin_cat['title'][$i];
			$menu_vars[$id]['description'] = $admin_cat['title'][$i];
			$menu_vars[$id]['link'] = '#';
			$menu_vars[$id]['image'] = "<img src='".$admin_cat['img'][$i]."' alt='".$admin_cat['title'][$i]."' class='icon S16' />";
			$menu_vars[$id]['image_large'] = "<img src='".$admin_cat['lrg_img'][$i]."' alt='".$admin_cat['title'][$i]."' class='icon S32' />";
			$menu_vars[$id]['image_src'] = $admin_cat['img'][$i];
			$menu_vars[$id]['image_large_src'] = $admin_cat['lrg_img'][$i];
			// FIX - 'perm' should not be set or navigation->admin() will be broken (bad permissions) for non main administrators
			//$menu_vars[$id]['perm'] = '';
			$menu_vars[$id]['sort'] = $admin_cat['sort'][$i];


		}

		//CORE SUBLINKS
		$active = '';
		foreach ($array_functions as $key => $subitem)
		{
			if(!empty($subitem[3]) && !getperms($subitem[3]))
			{
				continue;
			}

				$catid = $admin_cat['id'][$subitem[4]];
				$tmp = array();
				$tmp['text'] = $subitem[1];
				$tmp['description'] = $subitem[2];
				$tmp['link'] = $subitem[0];
				$tmp['image'] = $subitem[5];
				$tmp['image_large'] = $subitem[6];
				$tmp['image_src'] = '';
				$tmp['image_large_src'] = '';
				$tmp['perm'] = $subitem[3];
				$tmp['sub_class'] = '';
				$tmp['sort'] = false;

				if(strpos(e_REQUEST_SELF,$tmp['link'])!==false)
				{
					$active = $catid;
				}



			//	e107::getDebug()->log($catid);

				if(vartrue($pref['admin_slidedown_subs']) && vartrue($array_sub_functions[$key]))
				{
					$tmp['sub_class'] = 'sub';
					foreach ($array_sub_functions[$key] as $subkey => $subsubitem)
					{
						$subid = $key.'_'.$subkey;
						$tmp['sub'][$subid]['text'] = $subsubitem[1];
						$tmp['sub'][$subid]['description'] = $subsubitem[2];
						$tmp['sub'][$subid]['link'] = $subsubitem[0];
						$tmp['sub'][$subid]['image'] = $subsubitem[5];
						$tmp['sub'][$subid]['image_large'] = $subsubitem[6];
						$tmp['sub'][$subid]['image_src'] = '';
						$tmp['sub'][$subid]['image_large_src'] = '';
						$tmp['sub'][$subid]['perm'] = $subsubitem[3];
					}
				}

				if($tmp) $menu_vars[$catid]['sub'][$key] = $tmp;
		}


		//PLUGINS
		require_once(e_HANDLER.'plugin_class.php');
		$plug = new e107plugin;
		$tmp = array();

   		if($sql->select("plugin", "*", "plugin_installflag =1 ORDER BY plugin_path"))
		{
			while($row = $sql->fetch())
			{
				
				if($plug->parse_plugin($row['plugin_path']))
				{
					$plug_vars = $plug->plug_vars;

					if($row['plugin_path']=='calendar_menu')
					{
				//		print_a($plug_vars);
					}
					
					// moved to boot.php
					// e107::loadLanFiles($row['plugin_path'], 'admin');
					if(varset($plug_vars['adminLinks']['link']))
					{
						
						if(!empty($row['plugin_category']) && $row['plugin_category'] == 'menu' || !vartrue($plug_vars['adminLinks']['link'][0]['@attributes']['url']))
						{
							continue;
						}
						
						
						$plugpath = varset($plug_vars['plugin_php']) ? e_PLUGIN_ABS : e_PLUGIN_ABS.$row['plugin_path'].'/';
						$icon_src = varset($plug_vars['administration']['iconSmall']) ? $plugpath.$plug_vars['administration']['iconSmall'] : '';
						$icon_src_lrg = varset($plug_vars['administration']['icon']) ? $plugpath.$plug_vars['administration']['iconSmall'] : '';
						$id = 'plugnav-'.$row['plugin_path'];

						if(!getperms('P'.$row['plugin_id']))
						{
							continue;
						}

           	  			$tmp[$id]['text'] = e107::getParser()->toHTML($plug_vars['@attributes']['name'], FALSE, "LINKTEXT");
						$tmp[$id]['description'] = vartrue($plug_vars['description']['@value']);
						$tmp[$id]['link'] = e_PLUGIN_ABS.$row['plugin_path'].'/'.$plug_vars['administration']['configFile'];
					 	$tmp[$id]['image'] = $icon_src ? "<img src='{$icon_src}' alt=\"".varset($tmp[$id]['text'])."\" class='icon S16' />" : E_16_PLUGIN;
						$tmp[$id]['image_large'] = $icon_src_lrg ? "<img src='{$icon_src_lrg}' alt=\"".varset($tmp[$id]['text'])."\" class='icon S32' />" : $icon_src_lrg;
						$tmp[$id]['image_src'] = $icon_src;
						$tmp[$id]['image_large_src'] = $icon_src_lrg;
						$tmp[$id]['perm'] = 'P'.$row['plugin_id'];
						$tmp[$id]['sub_class'] = '';
						$tmp[$id]['sort'] = 2;
						$tmp[$id]['category'] = varset($row['plugin_category']);

						if($pref['admin_slidedown_subs'] && vartrue($plug_vars['adminLinks']['link']) )
						{
							$tmp[$id]['sub_class'] = 'sub';
							$tmp[$id]['sort'] = false;
							foreach ($plug_vars['adminLinks']['link'] as $subkey => $plugsub)
							{
								$subid = $id.'-'.$subkey;
								$predef_icons = array('add', 'manage', 'settings');
								$title = vartrue($plugsub['@value']);
								$plugsub = $plugsub['@attributes'];

								if(varset($plugsub['primary'])=='true') // remove primary links.
								{
									continue;
								}

								$icon_src = in_array($plugsub['icon'], $predef_icons) ? e_IMAGE_ABS."admin_images/{$plugsub['icon']}_16.png" : ( $plugsub['icon'] ? $plugpath.$plugsub['icon'] : '');


								$tmp[$id]['sub'][$subid]['text'] = e107::getParser()->toHTML($title, FALSE, 'LINKTEXT');
								$tmp[$id]['sub'][$subid]['description'] = (vartrue($plug_vars['description']['@value'])) ? e107::getParser()->toHTML($plug_vars['description']['@value']) : "";
								$tmp[$id]['sub'][$subid]['link'] = e_PLUGIN_ABS.$row['plugin_path'].'/'.$plugsub['url'];
								$tmp[$id]['sub'][$subid]['image'] = $icon_src ? "<img src='{$icon_src}' alt=\"".varset($tmp[$id]['sub'][$subid]['text'])."\" class='icon S16' />" : "";
								$tmp[$id]['sub'][$subid]['image_large'] = '';
								$tmp[$id]['sub'][$subid]['image_src'] = $icon_src;
								$tmp[$id]['sub'][$subid]['image_large_src'] = '';
								$tmp[$id]['sub'][$subid]['perm'] = varset($plugsub['perm']) ? $plugsub['perm'] : 'P'.$row['plugin_id'];
								$tmp[$id]['sub'][$subid]['sub_class'] = '';

							}
						}
					}
				}
			}



		 	$menu_vars['plugMenu']['sub'] = multiarray_sort($tmp, 'text');

		}

		// ---------------- Cameron's Bit ---------------------------------

		if(empty($pref['admin_separate_plugins']))
		{
        	// Convert Plugin Categories to Core Categories.
			$convert = array(
				'settings' 	=> array(1,'setMenu'),
				'users'		=> array(2,'userMenu'),
				'content'	=> array(3,'contMenu'),
				'tools'		=> array(4,'toolMenu'),
				'manage'	=> array(6,'managMenu'),
				'misc'		=> array(7,'miscMenu'),
				'help'		=> array(20,'helpMenu')
			);


             foreach($tmp as $pg)
			 {
			    if(!empty($pg['category']))
			    {
			 	    $id = $convert[$pg['category']][1];
             	    $menu_vars[$id]['sub'][] = $pg;

				    if(strpos(e_REQUEST_SELF,$pg['link'])!==false)
					{
						$active = $id;
					}


			    }
			 }

			// Clean up - remove empty main sections
			foreach ($menu_vars as $_m => $_d)
			{

				if(!isset($_d['sub']) || empty($_d['sub']))
				{
					unset($menu_vars[$_m]);
				}
			}

			unset($menu_vars['plugMenu']);
		}

		// ------------------------------------------------------------------

		//added option to disable leave/logout (ll) - more flexibility for theme developers
		if(!vartrue($parms['disable_ll']))
		{
		//	$menu_vars += $this->getOtherNav('home');	
		}

	//	 print_a($menu_vars);


		return e107::getNav()->admin('', $active, $menu_vars, $$tmpl, FALSE, FALSE);
		//return e_admin_men/u('', e_PAGE, $menu_vars, $$tmpl, FALSE, FALSE);
	}


	/**
	 * New Admin Navigation Routine. v2.1.5
	 */
	function sc_admin_navigation($parm=null)
	{

		if (!ADMIN) return '';

		$tp 	= e107::getParser();

		parse_str($parm, $parms);
		$tmpl = strtoupper(varset($parms['tmpl'], 'E_ADMIN_NAVIGATION'));
		global $$tmpl;


		if($parm == 'enav_popover') // @todo move to template and make generic.
		{
			if('0' != ADMINPERMS)
			{
				return null;
			}

			$template = $$tmpl;


			$upStatus =  (e107::getSession()->get('core-update-status') === true) ? "<span title=\"".ADLAN_120."\" class=\"text-info\"><i class=\"fa fa-database\"></i></span>" : '<!-- -->';

			return $template['start']. '<li><a id="e-admin-core-update" tabindex="0" href="'.e_ADMIN_ABS.'e107_update.php" class="e-popover text-primary" role="button" data-container="body" data-toggle="popover" data-placement="right" data-trigger="bottom" data-content="'.$tp->toAttribute(ADLAN_120).'">'.$upStatus.'</a></li>' .$template['end'];

		}

		if($parm == self::ADMIN_NAV_HOME || $parm == self::ADMIN_NAV_LOGOUT || $parm == self::ADMIN_NAV_LANGUAGE || $parm == 'pm')
		{
			$template = $$tmpl;

			$template['start'] = $template['start_other'];

			$menu_vars = $this->getOtherNav($parm);
			return e107::getNav()->admin('', '', $menu_vars, $template, FALSE, FALSE);
		}


		$pref = e107::getPref();

		$admin_cat 				= e107::getNav()->adminCats();
		$array_functions 		= e107::getNav()->adminLinks('legacy');
		$array_sub_functions	= e107::getNav()->adminLinks('sub');
		$array_plugins          = e107::getNav()->adminLinks('plugin2');


		// MAIN LINK
		if($parm != 'no-main')
		{
			$menu_vars = array();
			$menu_vars['adminhome']['text'] = ADLAN_151;
			$menu_vars['adminhome']['link'] = e_ADMIN_ABS.'admin.php';
			$menu_vars['adminhome']['image'] = "<img src='".E_16_NAV_MAIN."' alt='".ADLAN_151."' class='icon S16' />";
			$menu_vars['adminhome']['image_src'] = ADLAN_151;
			$menu_vars['adminhome']['perm'] = '';
		}

		//ALL OTHER ROOT LINKS - temporary data transformation - data structure will be changed in the future and this block will be removed

  		foreach($admin_cat['id'] as $i => $cat)
		{

			$id = $admin_cat['id'][$i];
			$menu_vars[$id]['text'] = $admin_cat['title'][$i];
			$menu_vars[$id]['description'] = $admin_cat['title'][$i];
			$menu_vars[$id]['link'] = '#';
			$menu_vars[$id]['image'] = "<img src='".$admin_cat['img'][$i]."' alt='".$admin_cat['title'][$i]."' class='icon S16' />";
			$menu_vars[$id]['image_large'] = "<img src='".$admin_cat['lrg_img'][$i]."' alt='".$admin_cat['title'][$i]."' class='icon S32' />";
			$menu_vars[$id]['image_src'] = $admin_cat['img'][$i];
			$menu_vars[$id]['image_large_src'] = $admin_cat['lrg_img'][$i];
			// FIX - 'perm' should not be set or navigation->admin() will be broken (bad permissions) for non main administrators
			//$menu_vars[$id]['perm'] = '';
			$menu_vars[$id]['sort'] = $admin_cat['sort'][$i];

		}


		//CORE SUBLINKS
		$active = '';
		foreach ($array_functions as $key => $subitem)
		{

			if(isset($subitem[3]) && $subitem[3] !== false && !getperms($subitem[3]))
			{
				continue;
			}

				$catid = $admin_cat['id'][$subitem[4]];
				$tmp = array();
				$tmp['text'] = $subitem[1];
				$tmp['description'] = $subitem[2];
				$tmp['link'] = $subitem[0];
				$tmp['image'] = $subitem[5];
				$tmp['image_large'] = $subitem[6];
				$tmp['image_src'] = '';
				$tmp['image_large_src'] = '';
				$tmp['perm'] = $subitem[3];
				$tmp['sub_class'] = '';
				$tmp['sort'] = false;

				if(strpos(e_REQUEST_SELF,$tmp['link'])!==false)
				{
					$active = $catid;
				}
				
			//	e107::getDebug()->log($catid);

				if(vartrue($pref['admin_slidedown_subs']) && vartrue($array_sub_functions[$key]))
				{
					$tmp['sub_class'] = 'sub';
					foreach ($array_sub_functions[$key] as $subkey => $subsubitem)
					{
						$subid = $key.'_'.$subkey;
						$tmp['sub'][$subid]['text'] = $subsubitem[1];
						$tmp['sub'][$subid]['description'] = $subsubitem[2];
						$tmp['sub'][$subid]['link'] = $subsubitem[0];
						$tmp['sub'][$subid]['image'] = $subsubitem[5];
						$tmp['sub'][$subid]['image_large'] = $subsubitem[6];
						$tmp['sub'][$subid]['image_src'] = '';
						$tmp['sub'][$subid]['image_large_src'] = '';
						$tmp['sub'][$subid]['perm'] = $subsubitem[3];
					}
				}

				if($tmp) $menu_vars[$catid]['sub'][$key] = $tmp;
		}


	//PLUGINS ----------------------------------------------------------

		$tmp = array();
		foreach($array_plugins as $key=>$p)
		{
			if(!getperms($p['perm']))
			{
				continue;
			}

			$tmp[$key]= $p;

		}


		$menu_vars['plugMenu']['sub'] = multiarray_sort($tmp, 'text');

	// --------------------------------------------------------------------


		if(empty($pref['admin_separate_plugins']))
		{
        	// Convert Plugin Categories to Core Categories.
			$convert = array(
				'settings' 	=> array(1,'setMenu'),
				'users'		=> array(2,'userMenu'),
				'content'	=> array(3,'contMenu'),
				'tools'		=> array(4,'toolMenu'),
				'manage'	=> array(6,'managMenu'),
				'misc'		=> array(7,'miscMenu'),
				'help'		=> array(20,'helpMenu')
			);


             foreach($tmp as $pg)
			 {
			    if(!empty($pg['category']))
			    {
			 	    $id = $convert[$pg['category']][1];
             	    $menu_vars[$id]['sub'][] = $pg;

				    if(strpos(e_REQUEST_SELF,$pg['link'])!==false)
					{
						$active = $id;
					}


			    }
			 }

			// Clean up - remove empty main sections
			foreach ($menu_vars as $_m => $_d)
			{

				if(!isset($_d['sub']) || empty($_d['sub']))
				{
					unset($menu_vars[$_m]);
				}
			}

			unset($menu_vars['plugMenu']);
		}

		// ------------------------------------------------------------------

		//	e107::getDebug()->log($menu_vars);


		return e107::getNav()->admin('', $active, $menu_vars, $$tmpl, false, false);

	}











	function getOtherNav($type)
	{
		$tp = e107::getParser();
		$frm = e107::getForm();
		
		if($type === self::ADMIN_NAV_HOME)
		{
		
			$menu_vars[$type]['text'] =  ""; // ADLAN_53;
			$menu_vars[$type]['link'] = e_HTTP.'index.php';
			$menu_vars[$type]['image'] = $tp->toGlyph('fa-home'); // "<i class='fa fa-home'></i>" ; // "<img src='".E_16_NAV_LEAV."' alt='".ADLAN_151."' class='icon S16' />";
			$menu_vars[$type]['image_src'] = ADLAN_151;
			$menu_vars[$type]['sort'] = 1;
			$menu_vars[$type]['sub_class'] = 'sub';
			$menu_vars[$type]['template'] = $type;
			
			// Sub Links for 'home'. 
			require_once(e_HANDLER."sitelinks_class.php");
			$slinks = new sitelinks;
			$slinks->getlinks(1);
			$tmp = array();	
			$c= 0;
			foreach($slinks->eLinkList['head_menu'] as $k=>$lk)
			{
				$subid = 'home_'.$k;
				$subid = $c;
				$link = (substr($lk['link_url'],0,1)!="/" && substr($lk['link_url'],0,3)!="{e_" && substr($lk['link_url'],0,4)!='http') ? "{e_BASE}".$lk['link_url'] : $lk['link_url'];
								
				$tmp[$c]['text'] = $tp->toHtml($lk['link_name'],'','defs');
				$tmp[$c]['description'] = $tp->toHtml($lk['link_description'],'','defs');
				$tmp[$c]['link'] = $tp->replaceConstants($link,'full');
				$tmp[$c]['image'] = vartrue($lk['link_button']) ? "<img class='icon S16' src='".$tp->replaceConstants($lk['link_button'])."' alt='".$tp->toAttribute($lk['link_description'],'','defs')."' />": "" ;
				$tmp[$c]['image_large'] = '';
				$tmp[$c]['image_src'] = vartrue($lk['link_button']);
				$tmp[$c]['image_large_src'] = '';
			//	$tmp[$c]['perm'] = '';
				$c++;
			}

			$menu_vars[$type]['sub'] = $tmp;
			// --------------------
		}
		elseif($type == self::ADMIN_NAV_LOGOUT)
		{
			$tmp = array();
			
			$tmp[1]['text'] = LAN_SETTINGS;
			$tmp[1]['description'] = ADLAN_151;
			$tmp[1]['link'] = e_BASE.'usersettings.php';
			$tmp[1]['image'] =  "<i class='S16 e-settings-16'></i>"; // "<img src='".E_16_CAT_SETT."' alt='".ADLAN_151."' class='icon S16' />";
			$tmp[1]['image_large'] = '';
			$tmp[1]['image_src'] = '';
			$tmp[1]['image_large_src'] = '';


			// If not Main Admin and "Apply dashboard preferences to all administrators"
			// is checked in admin theme settings.
			$adminPref = e107::getConfig()->get('adminpref', 0);
			if(getperms("1") || $adminPref == 0)
			{
				$tmp[2]['text'] = LAN_PERSONALIZE;
				$tmp[2]['description'] = "Customize administration panels";
				$tmp[2]['link'] = e_ADMIN . 'admin.php?mode=customize';
				$tmp[2]['image'] = "<i class='S16 e-admins-16'></i>"; //E_16_ADMIN; // "<img src='".E_16_NAV_ADMIN."' alt='".ADLAN_151."' class='icon S16' />";
				$tmp[2]['image_large'] = '';
				$tmp[2]['image_src'] = '';
				$tmp[2]['image_large_src'] = '';
				//	$tmp[2]['perm'] = '';
			}
			
			
			$tmp[3]['text'] = LAN_LOGOUT;
			$tmp[3]['description'] = ADLAN_151;
			$tmp[3]['link'] = e_ADMIN_ABS.'admin.php?logout';
			$tmp[3]['image'] = "<i class='S16 e-logout-16'></i>"; // "<img src='".E_16_NAV_LGOT."' alt='".ADLAN_151."' class='icon S16' />";
			$tmp[3]['image_large'] = '';
			$tmp[3]['image_src'] = '';
			$tmp[3]['image_large_src'] = '';

				
				
					
			$tmp[4]['text'] = LAN_LOGOUT;
			$tmp[4]['description'] = ADLAN_151;
			$tmp[4]['link'] = e_ADMIN_ABS.'admin.php?logout';
			$tmp[4]['image'] = "";
			$tmp[4]['image_large'] = '';
			$tmp[4]['image_src'] = '';
			$tmp[4]['image_large_src'] = '';
			$tmp[4]['link_class']	= 'divider';
			
							
			$tmp[5]['text'] 			= "e107 Website";
			$tmp[5]['description'] 		= '';
			$tmp[5]['link'] 			= 'http://e107.org';
			$tmp[5]['image'] 			= E_16_E107;
			$tmp[5]['image_large'] 		= '';
			$tmp[5]['image_src'] 		= '';
			$tmp[5]['image_large_src'] 	= '';
			$tmp[5]['link_class']		= '';

										
			$tmp[6]['text'] 			= "e107 on Twitter";
			$tmp[6]['description'] 		= '';
			$tmp[6]['link'] 			= 'http://twitter.com/e107';
			$tmp[6]['image'] 			= E_16_TWITTER; // "<img src='".E_16_NAV_LGOT."' alt='".ADLAN_151."' class='icon S16' />";
			$tmp[6]['image_large'] 		= '';
			$tmp[6]['image_src'] 		= '';
			$tmp[6]['image_large_src'] 	= '';
			$tmp[6]['link_class']		= '';
								
							
			$tmp[7]['text'] 			= "e107 on Facebook";
			$tmp[7]['description'] 		= '';
			$tmp[7]['link'] 			= 'https://www.facebook.com/e107CMS';
			$tmp[7]['image'] 			= E_16_FACEBOOK; // "<img src='".E_16_NAV_LGOT."' alt='".ADLAN_151."' class='icon S16' />";
			$tmp[7]['image_large'] 		= '';
			$tmp[7]['image_src'] 		= '';
			$tmp[7]['image_large_src'] 	= '';
			$tmp[7]['link_class']		= '';	
	
			
			$tmp[8]['text'] 			= "e107 on Github";
			$tmp[8]['description'] 		= '';
			$tmp[8]['link'] 			= 'https://github.com/e107inc';
			$tmp[8]['image'] 			= E_16_GITHUB; // "<img src='".E_16_NAV_LGOT."' alt='".ADLAN_151."' class='icon S16' />";
			$tmp[8]['image_large'] 		= '';
			$tmp[8]['image_src'] 		= '';
			$tmp[8]['image_large_src'] 	= '';
			$tmp[8]['link_class']		= '';					
				
			$menu_vars[$type]['text'] = ''; // ADMINNAME; // ""; // ADMINNAME;
			$menu_vars[$type]['link'] = '#';
			$menu_vars[$type]['image'] = $tp->toAvatar(null, array('w'=>30,'h'=>30,'crop'=>1, 'shape'=>'circle')); // $tp->toGlyph('fa-user'); // "<i class='icon-user'></i>"; // "<img src='".E_16_NAV_LGOT."' alt='".ADLAN_151."' class='icon S16' />";
			$menu_vars[$type]['image_src'] = LAN_LOGOUT;
			$menu_vars[$type]['sub'] = $tmp;
			$menu_vars[$type]['template'] = $type;
		}

		if($type == self::ADMIN_NAV_LANGUAGE)
		{
			$slng = e107::getLanguage();
			$languages = $slng->installed();//array('English','French');
			$multiDoms = array();
			
			if($langSubs = explode("\n", e107::getPref('multilanguage_subdomain')))
			{
				
				foreach($langSubs as $v)
				{
					if(!empty($v))
					{
						$multiDoms[] = trim($v);
					}
				}
				
			}

			sort($languages);
			
			if(count($languages) > 1)
			{
				$c = 0;
				foreach($languages as $lng)
				{			
					$checked = "<i class='fa fa-fw'>&nbsp;</i>&nbsp;";
					$code = $slng->convert($lng);
					
					if($lng == e_LANGUAGE)
					{
						$checked = $tp->toGlyph('fa-check', array('fw'=>1))." ";
						$link = '#';
					}
					elseif(in_array(e_DOMAIN,$multiDoms))
					{
						$code = ($lng == e107::getPref('sitelanguage')) ? 'www' : $code;
						$link = str_replace($_SERVER['HTTP_HOST'], $code.'.'.e_DOMAIN, e_REQUEST_URL); // includes query string
					}
					else
					{
						$get = $_GET;
						$get['elan'] = $code;
						
						$qry = http_build_query($get, null, '&amp;');
						$link = e_REQUEST_SELF.'?'.$qry;
					}
					
					$tmp[$c]['text'] = $lng;
					$tmp[$c]['description'] = '';
					$tmp[$c]['link'] = $link;
					$tmp[$c]['image'] = $checked; 
					$tmp[$c]['image_large'] = '';
					$tmp[$c]['image_src'] = '';
					$tmp[$c]['image_large_src'] = '';
					$c++;		
				}
				
				$menu_vars[$type]['text'] = strtoupper(e_LAN); // e_LANGUAGE;
				$menu_vars[$type]['link'] = '#';
				$menu_vars[$type]['image'] = $tp->toGlyph('fa-globe'); //  "<i class='icon-globe'></i>" ;
				$menu_vars[$type]['image_src'] = null;
				$menu_vars[$type]['sub'] = $tmp;
				$menu_vars[$type]['template'] = $type;
			}	
			
		}	
		
		return !empty($menu_vars) ? $menu_vars : null;
	}






	function sc_admin_menumanager($parm=null)  // List all menu-configs for easy-navigation
	{
		if(strpos(e_REQUEST_URI,e_ADMIN_ABS."menus.php")===false)
		{
			return false;
		}

		if($parm == 'selection')
		{
			return $this->menuManagerSelection();
		}

		$pref = e107::getPref();



		$search = array("_","legacyDefault","legacyCustom");
		$replace = array(" ",MENLAN_31,MENLAN_33);

		$var = array();



		foreach($pref['sitetheme_layouts'] as $key=>$val)
		{
			$layoutName = str_replace($search,$replace,$key);
			$layoutName .=($key==$pref['sitetheme_deflayout']) ? " (".LAN_DEFAULT.")" : "";
		//	$selected = ($this->curLayout == $key || ($key==$pref['sitetheme_deflayout'] && $this->curLayout=='')) ? "selected='selected'" : FALSE;


			//$url = e_SELF."?configure=".$key;
			$url = e_SELF."?configure=".$key;

		//	$text .= "<option value='".$url."' {$selected}>".$layoutName."</option>";
			$var[$key]['text'] = str_replace(":"," / ",$layoutName);
			$var[$key]['link'] = '#'.$key;
			$var[$key]['link_class'] = ' menuManagerSelect';
			$var[$key]['active'] = ($key==$pref['sitetheme_deflayout']) ? true: false;
			$var[$key]['include'] = " data-url='". e_SELF."?configure=".$key."' data-layout='".$key."' ";
		}
		$action = $pref['sitetheme_deflayout'];

		$defLayout = $pref['sitetheme_deflayout'];

		$var = array($defLayout => $var[$defLayout]) + $var;

		e107::setRegistry('core/e107/menu-manager/curLayout',$action);

		$icon  = e107::getParser()->toIcon('e-menus-24');
		$caption = $icon."<span>".ADLAN_6."</span>";

				$diz = MENLAN_58;

		$caption .= "<span class='e-help-icon pull-right'><a data-placement=\"bottom\" class='e-tip' title=\"".e107::getParser()->toAttribute($diz)."\">".ADMIN_INFO_ICON."</a></span>";

	   return e107::getNav()->admin($caption,$action, $var);



/*

        $var['menumanager']['text'] = LAN_MENULAYOUT;
		$var['menumanager']['link'] = e_ADMIN_ABS.'menus.php';
		
		$var['nothing']['divider'] = true;

		if(vartrue($pref['menuconfig_list']))
		{
	        foreach($pref['menuconfig_list'] as $name=>$val)
			{
	           	$var[$name]['text'] = str_replace(":"," / ",$val['name']);
	 			$var[$name]['link'] = e_PLUGIN_ABS.$val['link'];

			}
		}

 		foreach($var as $key=>$link)
		{
        	if(varset($link['link']) && strpos(e_SELF,$link['link']))
			{
            	$action = $key;
			}
		}

		if(!$action)
		{
        	return;
		}
   //		$keys = array_keys($var);
	//	$action = (in_array($this->action,$keys)) ? $this->action : "installed";

		e107::getNav()->admin("Menu Manager",$action, $var);

*/


	}


	private function menuManagerSelection()
	{

		 if(e_DEBUG === false)
	    {
	        return null;
	    }


		// TODO: Move to another shortcode?
		// TODO: Get drag-n-drop working.


		$sql = e107::getDb();

		$pageMenu = array();
		$pluginMenu = array();

		$sql->select("menus", "menu_name, menu_id, menu_pages, menu_path", "1 GROUP BY menu_name ORDER BY menu_name ASC");

		while ($row = $sql->fetch())
		{
			if(is_numeric($row['menu_path']))
			{
				$pageMenu[] = $row;
			}
			else
			{
				$pluginMenu[] = $row;
			}

		}

		$text = "<div id='menu-manager-item-list' class='menu-manager-items' style='height:400px;overflow-y:scroll'>";
		$text .= "<h4>Your Menus</h4>";

		foreach($pageMenu as $row)
		{
			if(!empty($row['menu_name']))
			{
				$text .= "<div class='item' >".$row['menu_name']."</div>";
			}
		}

		$text .= "<h4>Plugin Menus</h4>";
		foreach($pluginMenu as $row)
		{
			$text .= "<div class='item' data-menu-id='".$row['menu_id']."'>".substr($row['menu_name'],0,-5)."</div>";
		}

		$text .=  "</div>";







		return e107::getRender()->tablerender("Drag-N-Drop Menus", $text, null, true);




	}


}


?>
