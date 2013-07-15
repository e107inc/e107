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
	
	function cronUpdateRender($parm,$cacheData)
	{
		$mes = e107::getMessage();
		
            if($cacheData == 'up-to-date')
            {
                return '';
            }
    	
			$installUrl = "#"; // TODO 
		
		
            if($parm=='alert')
            {
            	$text = 'A new update is ready to install! Click to unzip and install  v'.$cacheData.'</a>.
            	<a class="btn btn-success" href="'.$installUrl.'">Install</a>'; 
				
                 $mes->addInfo($text);
				return; //  $mes->render(); 
			}
            
            if($parm=='icon')
            {
				
				return '<ul class="nav pill">
        			<li class="dropdown">
            		<a class="dropdown-toggle" title="Messages" role="button" data-toggle="dropdown" href="#">
                	'.E_16_E107.' <b class="caret"></b>
            	</a> 
            	<ul class="dropdown-menu" role="menu">
                	<li class="nav-header">Update Available</li>
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
		<input class='btn button' type='button' onclick=\"javascript: window.open('".e_ADMIN_ABS."credits.php', 'myWindow', 'status = 1, height = 400, width = 300, resizable = 0')\" value='".LAN_CREDITS."' />
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

	function sc_admin_help()
	{
		if (!ADMIN) { return ''; }
	
		$ns = e107::getRender();
		$pref = e107::getPref();
	
		if(function_exists('e_help') && ($tmp =  e_help())) // new in v2.x for non-admin-ui admin pages. 
		{
			return $ns->tablerender($tmp['caption'],$tmp['text'],'e_help',true);
		}
		
		$helpfile = '';
		
		if(strpos(e_SELF, e_ADMIN_ABS) !== FALSE)
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
		if (!$helpfile) { return ''; }

		ob_start();
		include_once($helpfile);
		$help_text = ob_get_contents();
		ob_end_clean();
		return $help_text;
	}

	function sc_admin_icon()
	{
		if (ADMIN)
		{
			global $e_sub_cat, $e_icon_array, $PLUGINS_DIRECTORY;
			
			$e_icon_array = e107::getNav()->getIconArray();
			
			if (e_CURRENT_PLUGIN)
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
					$icon = E_32_CAT_PLUG;
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
		<img src='".e_IMAGE_ABS."admin_images/language_16.png' alt='' />&nbsp;";
		if(isset($aff))
		{
			$text .= $sql->mySQLlanguage;
			$text .= " (".$slng->convert($sql->mySQLlanguage).")
			: <span class='btn button' style='cursor: pointer;' onclick='expandit(\"lan_tables\");'><a style='text-decoration:none' title='' href=\"javascript:void(0);\" >&nbsp;&nbsp;".count($aff)." ".UTHEME_MENU_L3."&nbsp;&nbsp;</a></span><br />
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
				$select .= "<option value='".$urlval."'{$selected}>$lng</option>\n";
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

		return $ns->tablerender(UTHEME_MENU_L2, $text, '', true);

	}

	function sc_admin_latest($parm)
	{
		if($parm == 'infopanel' && e_PAGE != 'admin.php')
		{
			return;
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
					$oldconfigs['e-comment'][0] = array('icon'=>E_16_COMMENT, 'title'=>ADLAN_LAT_9, 'url'=> e_ADMIN_ABS."comment.php?searchquery=&filter_options=comment_blocked__2", 'total'=>$comments_pending);
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

					$configs = e107::getAddonConfig('e_latest');
					$allconfigs = array_merge($oldconfigs,$configs);	
					
					$allconfigs = multiarray_sort($allconfigs,'title'); //XXX FIXME - not sorting correctly. 
		
					$text = "<ul id='e-latest' class='unstyled'>";
					foreach($allconfigs as $k=>$v)
					{
						foreach($v as $val)
						{
							$class = admin_shortcodes::getBadge($val['total']); 
							$link =  "<a  href='".$val['url']."'>".str_replace(":"," ",$val['title'])." <span class='".$class."'>".$val['total']."</span></a>";	
							$text .= "<li class='clearfix'>".$val['icon']." ".$link."</li>\n";	
						}	
					}
					$text .= "</ul>";


				
				//	$text .= "</div>";
					
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
						$cnt = $sql -> db_Select('admin_log', '*', "ORDER BY `dblog_datestamp` DESC", 'no_where');
					}
					else
					{
						$text .= "<div style='display: none;' id='adminlog'>";
						$cnt = $sql -> db_Select('admin_log', '*', 'ORDER BY `dblog_datestamp` DESC LIMIT 0,10', 'no_where');
					}
					$text .= ($cnt) ? '<ul>' : '';
					$gen = e107::getDateConvert();
					while ($row = $sql -> db_Fetch())
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
				return '<b>'.ADLAN_48.':</b> '.ADMINNAME.' ('.ADLAN_49.') '.( defined('e_DBLANGUAGE') ? '<b>'.LAN_head_5.'</b>: '.e_DBLANGUAGE : '' );
			}
			else
			{
				return '<b>'.ADLAN_48.':</b> '.ADMINNAME.' '.( defined('e_DBLANGUAGE') ? '<b>'.LAN_head_5.'</b>: '.e_DBLANGUAGE : '' );
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

		// Obsolete
		ob_start();
		//Show upper_right menu if the function exists
		$tmp = explode('.',e_PAGE);
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
		$plugpath = e_PLUGIN.str_replace(basename(e_SELF),'',str_replace('/'.$plugindir,'','/'.strstr(e_SELF,$plugindir))).'admin_menu.php';
		
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

	// FIXME - make it work
	function sc_admin_pm($parm)
	{
		if(!e107::isInstalled('pm')) return;
        
        $sql = e107::getDb();
        $count =  $sql->count('private_msg','(*)','WHERE pm_read = 0 AND pm_to='.USERID);
       
       if ($count >0)
       {
            $countDisp = ' <span class="label label-info">'.$count.'</span> ' ;
       }
       else
      {
            $countDisp = '';    
      }
         
		$inboxUrl = e_PLUGIN.'pm/admin_config.php?'.'searchquery=&amp;iframe=1&amp;filter_options=bool__pm_to__'.USERID; 
		$outboxUrl = e_PLUGIN.'pm/admin_config.php?'.'searchquery=&amp;iframe=1&amp;filter_options=bool__pm_from__'.USERID;
		$composeUrl = e_PLUGIN.'pm/admin_config.php?'.'mode=main&amp;iframe=1&amp;action=create';

       $text = '<ul class="nav nav-pills">
        <li class="dropdown">
            <a class="dropdown-toggle" title="Messages" role="button" data-toggle="dropdown" href="#" >
                <i class="icon-envelope  active"></i>'.$countDisp.'<b class="caret"></b>
            </a> 
            <ul class="dropdown-menu" role="menu" >
                <li class="nav-header">Private Messages</li>
                    <li><a class="e-modal" data-cache="false" data-modal-caption="Inbox" data-target="#uiModal" href="'.$inboxUrl.'" >Inbox</a></li>
                    <li><a class="e-modal" data-cache="false" data-modal-caption="Outbox" data-target="#uiModal" href="'.$outboxUrl.'">Outbox</a></li>
                    <li><a class="e-modal" data-cache="false" data-modal-caption="Compose" data-target="#uiModal" href="'.$composeUrl.'">Compose</a></li>
                </ul>
        </li>
        </ul>
        '; 
        
        return $text;
        
      //  e107_plugins/pm/pm.php
        
        
        
        
        
		$text = '
		<li class="dropdown">
			<a class="dropdown-toggle" title="Messages" role="button" data-toggle="dropdown" href="#" >
				<i class="icon-envelope icon-white active"></i> 3 <b class="caret"></b>
			</a> 
			<div id="dropdown" class="dropdown-menu pull-right e-noclick" style="padding:10px;width:300px">
				<ul class="nav-list">
		    		<li class="nav-header">Unread Messages</li>
		    		<li><a href="#">Incoming Message Number 1</a></li>
		      		<li><a href="#">Incoming Message Number 2</a></li>
		        	<li><a href="#">Incoming Message Number 3</a></li>
		         	<li class="divider"></li>
		   		</ul>
				<textarea class="e-tip input-block-level" title="Example Only"></textarea>
				<button class="dropdown-toggle btn btn-primary">Send</button>	
			</div>
		</li>
		';
		
		return $text;	
	}




	function sc_admin_msg($parm)
	{
		if (ADMIN)
		{
			global $ns;
			ob_start();
			if(!FILE_UPLOADS)
			{
				echo message_handler('ADMIN_MESSAGE', LAN_head_2, __LINE__, __FILE__);
			}
			/*
			if(OPEN_BASEDIR){
			echo message_handler('ADMIN_MESSAGE', LAN_head_3, __LINE__, __FILE__);
			}
			*/
			$message_text = ob_get_contents();
			ob_end_clean();
			return $message_text;
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
			if ($nav_sql -> db_Select('plugin', '*', 'plugin_installflag=1'))
			{
				$tmp = array();
				$e107_var['plugm']['text'] = ADLAN_95;
				$e107_var['plugm']['link'] = '';

				/* SUBLINKS */
				//Link Plugin Manager
				$tmp['plugm']['text'] = '<strong>'.ADLAN_98.'</strong>';
				$tmp['plugm']['link'] = e_ADMIN.'plugin.php';
				$tmp['plugm']['perm'] = 'P';

				while($rowplug = $nav_sql -> db_Fetch())
				{
					$plugin_id = $rowplug['plugin_id'];
					$plugin_path = $rowplug['plugin_path'];
					if (is_readable(e_PLUGIN.$plugin_path.'/plugin.xml'))
					{
						$readFile = $xml->loadXMLfile(e_PLUGIN.$plugin_path.'/plugin.xml', true, true);
						e107::loadLanFiles($plugin_path, 'admin');
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

			$e107_var['lout']['text']=ADLAN_46;
			$e107_var['lout']['link']=e_ADMIN_ABS.'admin.php?logout';

			$text = e_admin_menu('', '', $e107_var);
			return $ns->tablerender(LAN_head_1, $text, array('id' => 'admin_nav', 'style' => 'button_menu'), TRUE);
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
					if ($sql -> db_Select('plugin', '*', 'plugin_installflag=1'))
					{
						while($rowplug = $sql -> db_Fetch())
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

				$caption = LAN_head_6;
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
		global $pref;
		if (ADMIN && ADMINPERMS == '0')
		{
			global $ns;
			if ($pref['adminpwordchange'] && ((ADMINPWCHANGE+2592000) < time()))
			{
				$text = "<div style='mediumtext; text-align:center'>".ADLAN_102." <a href='".e_ADMIN."updateadmin.php'>".ADLAN_103.'</a></div>';
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
		if($parm == 'creditsonly' && e_PAGE != "credits.php"  && e_PAGE != "phpinfo.php")
		{
			return;
		}	
		
		
		if (ADMIN)
		{
			global $ns, $pref, $themename, $themeversion, $themeauthor, $themedate, $themeinfo, $mySQLdefaultdb;

			if (file_exists(e_ADMIN.'ver.php'))
			{
				include(e_ADMIN.'ver.php');
			}
			
			if($parm == "version")
			{
				return $e107info['e107_version'];
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
			".FOOTLAN_3." ".$e107info['e107_version']."
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

			$text .= "<br /><br />
			<b>".FOOTLAN_8."</b>
			<br />
			".$install_date."
			<br /><br />
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
			".e107::getDB()->mySqlServerInfo.
			"<br />
			".FOOTLAN_16.": ".$mySQLdefaultdb."
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

	function sc_admin_status($parm)
	{
		if($parm == 'infopanel' && e_PAGE != 'admin.php')
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
					
					
					$members 		= $sql->count('user');
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
	
					
					$oldconfigs['e-user'][0] 		= array('icon'=>E_16_USER, 'title'=>ADLAN_110, 'url'=> e_ADMIN_ABS."users.php?filter=0", 'total'=>$members);
					$oldconfigs['e-user'][1] 		= array('icon'=>E_16_USER, 'title'=>ADLAN_111, 'url'=> e_ADMIN."users.php?searchquery=&amp;filter_options=user_ban__2&amp;filter=unverified", 'total'=>$unverified);
					$oldconfigs['e-user'][2] 		= array('icon'=>E_16_BANLIST, 'title'=>ADLAN_112, 'url'=> e_ADMIN."users.php?searchquery=&filter_options=user_ban__1&filter=banned", 'total'=>$banned);
					$oldconfigs['e-comments'][0] 	= array('icon'=>E_16_COMMENT, 'title'=>ADLAN_114, 'url'=> e_ADMIN_ABS."comment.php", 'total'=>$comments);
				
					if($flo = $sql->count('generic', '(*)', "WHERE gen_type='failed_login'"))
					{
						//$text .= "\n\t\t\t\t\t<div style='padding-bottom: 2px;'>".E_16_FAILEDLOGIN." <a href='".e_ADMIN_ABS."fla.php'>".ADLAN_146.": $flo</a></div>";	
						$oldconfigs['e-failed'][0]	= array('icon'=>E_16_FAILEDLOGIN, 'title'=>ADLAN_146, 'url'=>e_ADMIN_ABS."fla.php", 'total'=>$flo);
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
					$configs = e107::getAddonConfig('e_status');
					
					if(!is_array($configs))
					{
						$configs = array();	
					}

					$allconfigs = array_merge($oldconfigs,$configs);	
					
					$allconfigs = multiarray_sort($allconfigs,'title'); //XXX FIXME - not sorting correctly. 
		
					$text = "<ul id='e-status' class='unstyled'>";
					foreach($allconfigs as $k=>$v)
					{
						foreach($v as $val)
						{
							$class = admin_shortcodes::getBadge($val['total']); 
							$link =  "<a href='".$val['url']."'>".str_replace(":"," ",$val['title'])." <span class='".$class."'>".$val['total']."</span></a>";	
							$text .= "<li>".$val['icon']." ".$link."</li>\n";	
						}	
					}
					$text .= "</ul>";

					if($parm == 'list')
					{
					//	$text = str_replace("<div style='padding-bottom: 2px;'>","<li>",$text);;	
					}
					
				//	$text .= "\n\t\t\t\t\t</div>";
					
					
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

	function getBadge($total, $type = 'latest')
	{
		
		/*
		 *  	<span class="badge">1</span>
Success 	2 	<span class="badge badge-success">2</span>
Warning 	4 	<span class="badge badge-warning">4</span>
Important 	6 	<span class="badge badge-important">6</span>
Info 	8 	<span class="badge badge-info">8</span>
Inverse 	10 	<span class="badge badge-inverse">10</span>
		 */
		
		$class = 'badge ';
		if($total > 100 && $type == 'latest')
		{
			$class .= 'badge-important';
		}
		elseif($total > 50 && $type == 'latest')
		{
			$class .= 'badge-warning';
		}
		elseif($total > 0)
		{
			$class .= 'badge-info';
		}
	
		
		return $class;		
	}


	/**
	 * Attempt to Convert Old $text string into new array format (e_status and e_latest)
	 */
	function legacyToConfig($text)
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
			
		

	function sc_admin_update()
	{
		if (!ADMIN) { return ''; }

		global $e107cache,$ns, $pref;
		if (!varset($pref['check_updates'], FALSE)) { return ''; }

		if (is_readable(e_ADMIN.'ver.php'))
		{
			include(e_ADMIN.'ver.php');
		}

		$feed = "http://sourceforge.net/export/rss2_projfiles.php?group_id=63748&rss_limit=5";
		$e107cache->CachePageMD5 = md5($e107info['e107_version']);

		if($cacheData = $e107cache->retrieve('updatecheck', 3600, TRUE))
		{
			return $ns -> tablerender(LAN_NEWVERSION, $cacheData);
		}

		// Don't check for updates if running locally (comment out the next line to allow check - but
		// remember it can cause delays/errors if its not possible to access the Internet
		if ((strpos(e_SELF,'localhost') !== FALSE) || (strpos(e_SELF,'127.0.0.1') !== FALSE)) { return ''; }

		$xml = e107::getXml();

		require_once(e_HANDLER."magpie_rss.php");

		$ftext = '';
		if($rawData = $xml -> getRemoteFile($feed))
		{
			$rss = new MagpieRSS( $rawData );
			list($cur_version,$tag) = explode(" ",$e107info['e107_version']);
			$c = 0;
			foreach($rss->items as $val)
			{
				$search = array((strstr($val['title'], '(')), 'e107', 'released', ' v');
				$version = trim(str_replace($search, '', $val['title']));

				if(version_compare($version,$cur_version)==1)
				{
					$ftext = "<a rel='external' href='".$val['link']."' >e107 v".$version."</a><br />\n";
					break;
				}
				$c++;
			}
		}
		else
		{  // Error getting data
			$ftext = ADLAN_154;
		}

		$e107cache->set('updatecheck', $ftext, TRUE);
		if($ftext)
		{
			return $ns -> tablerender(LAN_NEWVERSION, $ftext);
		}
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
	 * Legacy Admin Menu Routine. 
	 * Currently Used by Jayya admin. 
	 */
	 /*
	function sc_admin_alt_nav($parm)
	{
		
		if (ADMIN)
		{
			global $sql, $pref, $tp;
			parse_str($parm);
			require(e_ADMIN.'ad_links.php');
		
			
			function adnav_cat($cat_title, $cat_link, $cat_img, $cat_id=FALSE)
			{
				$cat_link = ($cat_link ? $cat_link : "javascript:void(0);");
				$text = "<a class='menuButton' href='".$cat_link."' style='background-image: url(".$cat_img."); background-repeat: no-repeat;  background-position: 3px 1px' ";
				if ($cat_id)
				{
					$text .= "onclick=\"return buttonClick(event, '".$cat_id."');\" onmouseover=\"buttonMouseover(event, '".$cat_id."');\"";
				}
				$text .= ">".$cat_title."</a>";
				return $text;
			}
			
			

			function adnav_main($cat_title, $cat_link, $cat_img, $cat_id=FALSE, $cat_highlight='')
			{

				$exit = "";
				$text = "<a class='menuItem ".$cat_highlight."' href='".$cat_link."' ";
				if ($cat_id)
				{
					$text .= "onclick=\"return false;\" onmouseover=\"menuItemMouseover(event, '".$cat_id."');\"";
				}
				$text .= "><span class='menuItemBuffer'>".$cat_img."</span><span class='menuItemText'>".$cat_title."</span>";
				if ($cat_id)
				{
					$text .= "<span class=\"menuItemArrow\">&#9654;</span>";
				}
				$text .= "</a>";
				return $text;
			}
			
	
			if (file_exists(THEME.'nav_menu.js'))
			{
				$text = "<script type='text/javascript' src='".THEME_ABS."nav_menu.js'></script>";
			}
			else
			{
				$text = "<script type='text/javascript' src='".e_JS."nav_menu.js'></script>";
			}

			$text .= "<div style='width: 100%'><table style='border-collapse: collapse; width: 100%'>
			<tr><td>
			<div class='menuBar' style='width: 100%'>";

			$text .= adnav_cat(ADLAN_151, e_ADMIN.'admin.php', E_16_NAV_MAIN); // Main Link. 

			// Render Settings, Users, Content, Tools, Manage. 
	   		for ($i = 1; $i < 7; $i++)
	   		{

				$ad_tmpi = 0;
				$ad_links_array = asortbyindex($array_functions, 1);
				$nav_main = adnav_cat($admin_cat['title'][$i], '', $admin_cat['img'][$i], $admin_cat['id'][$i]);
				$ad_texti = "<div id='".$admin_cat['id'][$i]."' class='menu' onmouseover=\"menuMouseover(event)\">";
				while(list($key, $nav_extract) = each($ad_links_array))
				{
					if($nav_extract[4]==$i)
					{
						if(getperms($nav_extract[3]))
						{
							$ad_texti .= adnav_main($nav_extract[1], $nav_extract[0], $nav_extract[5]);
							$ad_tmpi = 1;
						}
					}
				}
				$ad_texti .= '</div>';
				if ($ad_tmpi == 1)
				{
					$text .= $nav_main;
					$text .= $ad_texti;
				}
			}

			$render_plugins = FALSE;
			include_once(e_HANDLER.'plugin_class.php');
			$plug = new e107plugin;
			$plugin_array = array(); // kill php notices
			if($sql -> db_Select('plugin', '*', 'plugin_installflag=1 ORDER BY plugin_path'))
			{
				while($row = $sql -> db_Fetch())
				{
					if(getperms('P'.$row['plugin_id']))
					{
						if($plug->parse_plugin($row['plugin_path']))
						{
							$plug_vars = $plug->plug_vars;
							loadLanFiles($row['plugin_path'], 'admin');
							if($plug_vars['administration']['configFile'])
							{
								$plug_vars['@attributes']['name'] = $tp->toHTML($plug_vars['@attributes']['name'], FALSE, "defs");
								$icon_src = (isset($plug_vars['plugin_php']) ? e_PLUGIN_ABS : e_PLUGIN_ABS.$row['plugin_path'].'/') .$plug_vars['administration']['iconSmall'];
								$plugin_icon = $plug_vars['administration']['iconSmall'] ? "<img src='{$icon_src}' alt='".$plug_vars['administration']['caption']."' class='icon S16' />" : E_16_PLUGIN;
								$plugin_array[ucfirst($plug_vars['@attributes']['name'])] = adnav_main($plug_vars['@attributes']['name'], e_PLUGIN.$row['plugin_path']."/".$plug_vars['administration']['configFile'], $plugin_icon);
							}
							$render_plugins = TRUE;
							$active_plugs = TRUE;
						}
					}
				}
				ksort($plugin_array, SORT_STRING);
				$plugs_text = '';
				foreach ($plugin_array as $plugin_compile)
				{
					$plugs_text .= $plugin_compile;
				}
			}
         
	//		if (getperms('Z'))
	//		{
	//			$pclass_extended = $active_plugs ? 'header' : '';
	//			$plugin_text = adnav_main(ADLAN_98, e_ADMIN.'plugin.php', E_16_PLUGMANAGER, FALSE, $pclass_extended);
	//			$render_plugins = TRUE;
	//		}
	
	 			if ($render_plugins)
			{
				$text .= adnav_cat(ADLAN_CL_7, '', E_16_CAT_PLUG, 'plugMenu');
				$text .= "<div id='plugMenu' class='menu' onmouseover=\"menuMouseover(event)\">";
				$text .= varset($plugin_text).varset($plugs_text);
				$text .= "</div>";
			}
			

			// Render the "About" Menu - Phpinfo, Credits and Docs. 
			$text .= adnav_cat(ADLAN_CL_20, '', E_16_CAT_ABOUT, $admin_cat['id'][20]); //E_16_NAV_DOCS
			$text .= "<div id='".$admin_cat['id'][20]."' class='menu' onmouseover=\"menuMouseover(event)\">";
			foreach($ad_links_array as $key=>$nav_extract)
			{					
				$text .= ($nav_extract[4]==20) ? adnav_main($nav_extract[1], $nav_extract[0], $nav_extract[5]) : "";
			}
				
		
			// if (!is_readable(e_DOCS.e_LANGUAGE."/")) // warning killed
			// {
				// $handle=opendir(e_DOCS.'English/');
			// }
			// $i=1;
			// if(varset($handle))
			// {
				// while ($file = readdir($handle))
				// {
					// if ($file != '.' && $file != '..' && $file != 'CVS')
					// {
						// $text .= adnav_main(str_replace('_', ' ', $file), e_ADMIN_ABS.'docs.php?'.$i, E_16_DOCS);
						// $i++;
					// }
				// }
				// closedir($handle);
			// }
			 $text .= '</div>';


			$text .= '</div>
			</td>';

			if (varset($exit) != 'off')
			{
				$text .= "<td style='width: 160px; white-space: nowrap'>
				<div class='menuBar' style='width: 100%'>";

				$text .= adnav_cat(ADLAN_53, e_HTTP.'index.php', E_16_NAV_LEAV);
				$text .= adnav_cat(ADLAN_46, e_ADMIN_ABS.'admin.php?logout', E_16_NAV_LGOT);

				$text .= '</div>
				</td>';
			}

			$text .= '</tr>
			</table>
			</div>';

			return $text;
		}
	}
	*/
	
	/**
	 * New Admin Navigation Routine. 
	 */
	function sc_admin_navigation($parm)
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

		require_once(e_ADMIN.'ad_links.php'); //FIXME loaded in boot.php but $admin_cat is not available here. 
		
		if($parm == 'home' || $parm == 'logout' || $parm == 'language' || $parm == 'pm')
		{
			$menu_vars = $this->getOtherNav($parm);	
			return e107::getNav()->admin('', '', $menu_vars, $$tmpl, FALSE, FALSE);
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
		foreach ($array_functions as $key => $subitem)
		{
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

   		if($sql->db_Select("plugin", "*", "plugin_installflag =1 ORDER BY plugin_path"))
		{
			while($row = $sql->db_Fetch())
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
						
						if($row['plugin_category'] == 'menu' || !vartrue($plug_vars['adminLinks']['link'][0]['@attributes']['url']))
						{
							continue;
						}
						
						
						$plugpath = varset($plug_vars['plugin_php']) ? e_PLUGIN_ABS : e_PLUGIN_ABS.$row['plugin_path'].'/';
						$icon_src = varset($plug_vars['administration']['iconSmall']) ? $plugpath.$plug_vars['administration']['iconSmall'] : '';
						$icon_src_lrg = varset($plug_vars['administration']['icon']) ? $plugpath.$plug_vars['administration']['iconSmall'] : '';
						$id = 'plugnav-'.$row['plugin_path'];

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
						$tmp[$id]['category'] = $row['plugin_category'];

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

		if(!varsettrue($pref['admin_separate_plugins']))
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
			 	$id = $convert[$pg['category']][1];
             	$menu_vars[$id]['sub'][] = $pg;
			 }
		   	 unset($menu_vars['plugMenu']);
			 
		
			// Clean up - remove empty main sections
			foreach ($menu_vars as $_m => $_d) 
			{
				if(!isset($_d['sub']) || empty($_d['sub']))
				{
					unset($menu_vars[$_m]);
				}
			}
		}

		// ------------------------------------------------------------------

		//added option to disable leave/logout (ll) - more flexibility for theme developers
		if(!varsettrue($parms['disable_ll']))
		{
		//	$menu_vars += $this->getOtherNav('home');	
		}

		// print_a($menu_vars);
		return e107::getNav()->admin('', e_PAGE, $menu_vars, $$tmpl, FALSE, FALSE);
		//return e_admin_men/u('', e_PAGE, $menu_vars, $$tmpl, FALSE, FALSE);
	}


	function getOtherNav($type)
	{
		$tp = e107::getParser();
		$frm = e107::getForm();
		
		if($type == 'home')
		{
		
			$menu_vars['home']['text'] =  ""; // ADLAN_53;
			$menu_vars['home']['link'] = e_HTTP.'index.php';
			$menu_vars['home']['image'] = "<i class='icon-home'></i>" ; // "<img src='".E_16_NAV_LEAV."' alt='".ADLAN_151."' class='icon S16' />";
			$menu_vars['home']['image_src'] = ADLAN_151;
			$menu_vars['home']['perm'] = '';
			$menu_vars['home']['sort'] = 1;
			$menu_vars['home']['sub_class'] = 'sub';
			
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
				$tmp[$c]['perm'] = '';
				$c++;
			}

			$menu_vars['home']['sub'] = $tmp;
			// --------------------
		}
		elseif($type == 'logout')
		{
			$tmp = array();
			
			$tmp[1]['text'] = ADLAN_CL_1;
			$tmp[1]['description'] = ADLAN_151;
			$tmp[1]['link'] = e_BASE.'usersettings.php';
			$tmp[1]['image'] = "<img src='".E_16_CAT_SETT."' alt='".ADLAN_151."' class='icon S16' />";
			$tmp[1]['image_large'] = '';
			$tmp[1]['image_src'] = '';
			$tmp[1]['image_large_src'] = '';
			$tmp[1]['perm'] = '';	
			
						
			$tmp[2]['text'] = "Personalize"; // TODO - generic LAN in lan_admin.php 
			$tmp[2]['description'] = "Customize administration panels";
			$tmp[2]['link'] = e_ADMIN.'admin.php?mode=customize';
			$tmp[2]['image'] = E_16_ADMIN; // "<img src='".E_16_NAV_ADMIN."' alt='".ADLAN_151."' class='icon S16' />";
			$tmp[2]['image_large'] = '';
			$tmp[2]['image_src'] = '';
			$tmp[2]['image_large_src'] = '';
			$tmp[2]['perm'] = '';	
			
			
			$tmp[3]['text'] = ADLAN_46;
			$tmp[3]['description'] = ADLAN_151;
			$tmp[3]['link'] = e_ADMIN_ABS.'admin.php?logout';
			$tmp[3]['image'] = "<img src='".E_16_NAV_LGOT."' alt='".ADLAN_151."' class='icon S16' />";
			$tmp[3]['image_large'] = '';
			$tmp[3]['image_src'] = '';
			$tmp[3]['image_large_src'] = '';
			$tmp[3]['perm'] = '';
				
				
					
			$tmp[4]['text'] = ADLAN_46;
			$tmp[4]['description'] = ADLAN_151;
			$tmp[4]['link'] = e_ADMIN_ABS.'admin.php?logout';
			$tmp[4]['image'] = "<img src='".E_16_NAV_LGOT."' alt='".ADLAN_151."' class='icon S16' />";
			$tmp[4]['image_large'] = '';
			$tmp[4]['image_src'] = '';
			$tmp[4]['image_large_src'] = '';
			$tmp[4]['perm'] = '';
			$tmp[4]['link_class']	= 'divider';
			
							
			$tmp[5]['text'] 			= "e107 Website";
			$tmp[5]['description'] 		= '';
			$tmp[5]['link'] 			= 'http://e107.org';
			$tmp[5]['image'] 			= E_16_E107;
			$tmp[5]['image_large'] 		= '';
			$tmp[5]['image_src'] 		= '';
			$tmp[5]['image_large_src'] 	= '';
			$tmp[5]['perm'] 			= '';
			$tmp[5]['link_class']		= '';

										
			$tmp[6]['text'] 			= "e107 on Twitter";
			$tmp[6]['description'] 		= '';
			$tmp[6]['link'] 			= 'http://twitter.com/e107';
			$tmp[6]['image'] 			= E_16_TWITTER; // "<img src='".E_16_NAV_LGOT."' alt='".ADLAN_151."' class='icon S16' />";
			$tmp[6]['image_large'] 		= '';
			$tmp[6]['image_src'] 		= '';
			$tmp[6]['image_large_src'] 	= '';
			$tmp[6]['perm'] 			= '';
			$tmp[6]['link_class']		= '';
								
							
			$tmp[7]['text'] 			= "e107 on Facebook";
			$tmp[7]['description'] 		= '';
			$tmp[7]['link'] 			= 'https://www.facebook.com/e107CMS';
			$tmp[7]['image'] 			= E_16_FACEBOOK; // "<img src='".E_16_NAV_LGOT."' alt='".ADLAN_151."' class='icon S16' />";
			$tmp[7]['image_large'] 		= '';
			$tmp[7]['image_src'] 		= '';
			$tmp[7]['image_large_src'] 	= '';
			$tmp[7]['perm'] 			= '';
			$tmp[7]['link_class']		= '';	
	
			
			$tmp[8]['text'] 			= "e107 on Github";
			$tmp[8]['description'] 		= '';
			$tmp[8]['link'] 			= 'https://github.com/e107inc';
			$tmp[8]['image'] 			= E_16_GITHUB; // "<img src='".E_16_NAV_LGOT."' alt='".ADLAN_151."' class='icon S16' />";
			$tmp[8]['image_large'] 		= '';
			$tmp[8]['image_src'] 		= '';
			$tmp[8]['image_large_src'] 	= '';
			$tmp[8]['perm'] 			= '';
			$tmp[8]['link_class']		= '';					
				
			$menu_vars['logout']['text'] = ""; // ADMINNAME;
			$menu_vars['logout']['link'] = '#';
			$menu_vars['logout']['image'] = "<i class='icon-user'></i>"; // "<img src='".E_16_NAV_LGOT."' alt='".ADLAN_151."' class='icon S16' />";
			$menu_vars['logout']['image_src'] = ADLAN_46;
			$menu_vars['logout']['perm'] = '';	
			$menu_vars['logout']['sub'] = $tmp;	
		}

		if($type == 'language')
		{
			
			$languages = e107::getLanguage()->installed();//array('English','French');
			if(count($languages) > 1)
			{
				$c = 0;
				foreach($languages as $lng)
				{			
					$checked = ($lng == e_LANGUAGE) ? "<i class='icon-ok'></i> " : "<i >&nbsp;</i>&nbsp;";
					
					$tmp[$c]['text'] = $lng;
					$tmp[$c]['description'] = '';
					$tmp[$c]['link'] = $lng == e_LANGUAGE ? '#' : e_SELF.'?elan='.$lng;
					$tmp[$c]['image'] = $checked; 
					$tmp[$c]['image_large'] = '';
					$tmp[$c]['image_src'] = '';
					$tmp[$c]['image_large_src'] = '';
					$tmp[$c]['perm'] = '';
					$c++;		
				}
				
				$menu_vars['language']['text'] = ""; // e_LANGUAGE;
				$menu_vars['language']['link'] = '#';
				$menu_vars['language']['image'] = "<i class='icon-globe'></i>" ;
				$menu_vars['language']['image_src'] = ADLAN_46;
				$menu_vars['language']['perm'] = '';	
				$menu_vars['language']['sub'] = $tmp;	
			}	
			
		}	
		
		return $menu_vars;
	}



	function sc_admin_menumanager()  // List all menu-configs for easy-navigation
	{
    	global $pref;
        $action = "";

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
	 //  e_admin/_menu(ADLAN_6,$action, $var);

	}

}


?>