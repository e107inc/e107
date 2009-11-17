<?php
/*
* Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: admin_shortcodes_class.php,v 1.32 2009-11-17 13:12:43 e107coders Exp $
*
* Admin shortcode batch - class
*/
if (!defined('e107_INIT')) { exit; }

class admin_shortcodes
{
	function sc_admin_credits()
	{
		if (!ADMIN) { return ''; }
		return "
		<div style='text-align: center'>
		<input class='button' type='button' onclick=\"javascript: window.open('".e_ADMIN."credits.php', 'myWindow', 'status = 1, height = 400, width = 300, resizable = 0')\" value='".LAN_CREDITS."' />
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

		$helpfile = '';
		global $ns, $pref;			// Used by the help renderer

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
			if (strstr(e_SELF, $PLUGINS_DIRECTORY))
			{
				if (is_readable('plugin.xml'))
				{
					$xml = e107::getXml();
					$xml->filter = array('folder' => FALSE, 'administration' => FALSE);		// Just need one variable
					$readFile = $xml->loadXMLfile('plugin.xml', true, true);
					$eplug_icon = $readFile['folder'].'/'.$readFile['administration']['icon'];
					$eplug_folder = $readFile['folder'];
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
				$icon = ($eplug_icon && file_exists(e_PLUGIN.$eplug_icon)) ? "<img src='".e_PLUGIN.$eplug_icon."' alt='' class='icon S32' />" : E_32_CAT_PLUG;
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
		global $e107, $sql, $pref;

		if (!ADMIN || !$pref['multilanguage']) { return ''; }

		include_lan(e_PLUGIN.'user_menu/languages/'.e_LANGUAGE.'.php');
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

		require_once(e_HANDLER.'language_class.php');
		$slng = new language;


		if(!getperms($sql->mySQLlanguage) && $lanperms)
		{
			$sql->mySQLlanguage = ($lanperms[0] != $pref['sitelanguage']) ? $lanperms[0] : "";
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
			}
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
			: <span class='button' style='cursor: pointer;' onclick='expandit(\"lan_tables\");'><a style='text-decoration:none' title='' href=\"javascript:void(0);\" >&nbsp;&nbsp;".count($aff)." ".UTHEME_MENU_L3."&nbsp;&nbsp;</a></span><br />
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
		elseif(isset($params['nobutton']))
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
		}
		else
		{
			$select .= "
			<form method='post' action='".e_SELF.(e_QUERY ? '?'.e_QUERY : '')."'>
			<div>
			<select name='sitelanguage' id='sitelanguage' class='tbox'>";
			foreach($lanperms as $lng)
			{
				$langval = ($lng == $pref['sitelanguage'] && $lng == 'English') ? "" : $lng;
				$selected = ($lng == $sql->mySQLlanguage || ($lng == $pref['sitelanguage'] && !$sql->mySQLlanguage)) ? " selected='selected'" : "";
				$select .= "<option value='".$langval."'{$selected}>$lng</option>\n";
			}
			$select .= "</select> ".(!isset($params['nobutton']) ? "<button class='update' type='submit' name='setlanguage' value='no-value'><span>".UTHEME_MENU_L1."</span></button>" : '')."
			</div>
			</form>
			";
		}

		if(isset($params['nomenu'])) { return $select; }
		if($select) { $text .= "<div class='center'>{$select}</div>"; }

		return $e107->ns->tablerender(UTHEME_MENU_L2, $text, '', true);

	}

	function sc_admin_latest($parm)
	{
		if (ADMIN) {
			if (!function_exists('admin_latest'))
			{
				function admin_latest()
				{
					global $sql, $ns, $pref;

					$active_uploads = $sql -> db_Count('upload', '(*)', 'WHERE upload_active = 0');
					$submitted_news = $sql -> db_Count('submitnews', '(*)', 'WHERE submitnews_auth = 0');

					$text = "<div class='left'><div style='padding-bottom: 2px;'>".E_16_NEWS.($submitted_news ? " <a href='".e_ADMIN."newspost.php?sn'>".ADLAN_LAT_2.": $submitted_news</a>" : ' '.ADLAN_LAT_2.': 0').'</div>';
					$text .= "<div style='padding-bottom: 2px;'>".E_16_UPLOADS.($active_uploads ? " <a href='".e_ADMIN."upload.php'>".ADLAN_LAT_7.": $active_uploads</a>" : ' '.ADLAN_LAT_7.': '.$active_uploads).'</div>';

					if(vartrue($pref['e_latest_list']))
					{
						foreach($pref['e_latest_list'] as $val)
						{
							if (is_readable(e_PLUGIN.$val.'/e_latest.php'))
							{
								include_once(e_PLUGIN.$val.'/e_latest.php');
							}
						}
                    }

					$messageTypes = array('Broken Download', 'Dev Team Message');
					$queryString = '';
					foreach($messageTypes as $types)
					{
						$queryString .= " gen_type='$types' OR";
					}
					$queryString = substr($queryString, 0, -3);

					if($amount = $sql -> db_Select('generic', '*', $queryString))
					{
						$text .= "<br /><b><a href='".e_ADMIN."message.php'>".ADLAN_LAT_8." [".$amount."]</a></b>";
					}
					$text .= "</div>";
					return $ns -> tablerender(ADLAN_LAT_1, $text, '', TRUE);
				}
			}

			if ($parm == 'request')
			{
				if (function_exists('latest_request'))
				{
					if (latest_request())
					{
						return admin_latest();
					}
				}
			}
			else
			{
				return admin_latest();
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
					$text .= "[ <a href='".e_ADMIN."admin_log.php?adminlog'>".ADLAN_117."</a> ]";
					$text .= "<br />[ <a href='".e_ADMIN."admin_log.php?config'>".ADLAN_118."</a> ]";

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

	function sc_admin_logged()
	{
		if (ADMIN)
		{
			$str = str_replace('.', '', ADMINPERMS);
			if (ADMINPERMS == '0')
			{
				return '<b>'.ADLAN_48.':</b> '.ADMINNAME.' ('.ADLAN_49.') '.( defined('e_DBLANGUAGE') ? '<b>'.LAN_head_5.'</b> '.e_DBLANGUAGE : '' );
			}
			else
			{
				return '<b>'.ADLAN_48.':</b> '.ADMINNAME.' '.( defined('e_DBLANGUAGE') ? '<b>'.LAN_head_5.'</b> '.e_DBLANGUAGE : '' );
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
			$logo = e_IMAGE.'adminlogo.png';
			$path = $logo;
		}

		$dimensions = getimagesize($path);

		$image = "<img class='logo admin_logo' src='".$logo."' style='width: ".$dimensions[0]."px; height: ".$dimensions[1]."px' alt='".ADLAN_153."' />\n";

		if (isset($link) && $link)
		{
			if ($link == 'index')
			{
				$image = "<a href='".e_ADMIN."index.php'>".$image.'</a>';
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
			// FIXME - renderMenu(), respectively e_admin_menu() should return, not output content!
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
		$plugpath = e_PLUGIN.str_replace(basename(e_SELF), '', str_replace($plugindir, '', strstr(e_SELF,$plugindir))).'admin_menu.php';
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
			$e107_var['x']['link'] = e_ADMIN.'admin.php';
			$e107_var['y']['text'] = ADLAN_53;
			$e107_var['y']['link'] = e_BASE."index.php";

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
			$e107_var['lout']['link']=e_ADMIN.'admin.php?logout';

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
		if(ADMIN && getperms('0'))
		{
			global $sql,$pst,$ns,$tp,$pref;

			if(isset($pst) && $pst->form && $pst->page)
			{
				$thispage = urlencode(e_SELF.'?'.e_QUERY);
				if(is_array($pst->page))
				{
					for ($i=0; $i<count($pst->page); $i++)
					{
						if (strpos($thispage, urlencode($pst->page[$i])) !== FALSE)
						{
							$query = urlencode($pst->page[$i]);
							$theform = $pst->form[$i];
							$pid = $i;
						}
					}
				}
				else
				{
					$query = urlencode($pst->page);
					$theform = $pst->form;
					$pid = 0;
				}

				$existing = is_array($pst->id) ? $pst->id[$pid] : $pst->id;
			//	$trigger = ($e_wysiwyg && $pref['wysiwyg']) ? 'tinyMCE.triggerSave();' : '';

				if (strpos($thispage, $query) !== false)
				{
					$pst_text = "
					<form method='post' action='".e_SELF."?clr_preset' id='e_preset' >
					<div style='text-align:center'>";
					if(!$sql->db_Count('preset', '(*)', " WHERE preset_name='".$tp->toDB($existing, true)."'  "))
					{
						$pst_text .= "<input type='button' class='button' name='save_preset' value='".LAN_SAVE."' onclick=\"$trigger savepreset('".$theform."',$pid)\" />";
					}
					else
					{
						$pst_text .= "<input type='button' class='button' name='save_preset' value='".LAN_UPDATE."' onclick=\"$trigger savepreset('".$theform."',$pid)\" />";
						$pst_text .= "<input type='hidden' name='del_id' value='$pid' />
						<input type='submit' class='button' name='delete_preset' value='".LAN_DELETE."' onclick=\"return jsconfirm('".$tp->toJS(LAN_PRESET_CONFIRMDEL." [".$existing."]")."')\" />";
					}
					$pst_text .= "</div></form>";
					return $ns->tablerender(LAN_PRESET, $pst_text, '', true);
				}
			}
		}
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

	function sc_admin_siteinfo()
	{
		if (ADMIN)
		{
			global $ns, $pref, $themename, $themeversion, $themeauthor, $themedate, $themeinfo, $mySQLdefaultdb;

			if (file_exists(e_ADMIN.'ver.php'))
			{
				include(e_ADMIN.'ver.php');
			}

			$obj = e107::getDateConvert();
			$install_date = $obj->convert_date($pref['install_date'], 'long');

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
			".mysql_get_server_info().
			"<br />
			".FOOTLAN_16.": ".$mySQLdefaultdb."
			<br /><br />
			<b>".FOOTLAN_17."</b>
			<br />utf-8"; //@TODO is this still needed?
			return $ns->tablerender(FOOTLAN_13, $text, '', TRUE);
		}
	}

	function sc_admin_status($parm)
	{
		if (getperms('0') || getperms('4'))
		{
			if (!function_exists('admin_status'))
			{
				function admin_status()
				{
					global $sql, $ns, $pref;
					$members = $sql -> db_Count('user');
					$unverified = $sql -> db_Count('user', '(*)', 'WHERE user_ban=2');
					$banned = $sql -> db_Count('user', '(*)', 'WHERE user_ban=1');
					$comments = $sql -> db_Count('comments'); 
					
				
					$unver = ($unverified ? " <a href='".e_ADMIN."users.php?filter=unverified'>".ADLAN_111."</a>" : ADLAN_111);

					$text = "
					<div class='left'>
						<div style='padding-bottom: 2px;'>".E_16_USER." ".ADLAN_110.": <a href='".e_ADMIN."users.php?filter=0'>".$members."</a></div>
						<div style='padding-bottom: 2px;'>".E_16_USER." {$unver}: <a href='".e_ADMIN."users.php?filter=unverified'>".$unverified."</a></div>
						<div style='padding-bottom: 2px;'>".E_16_BANLIST." ".ADLAN_112.": <a href='".e_ADMIN."users.php?filter=banned'>".$banned."</a></div>
						<div style='padding-bottom: 2px;'>".E_16_COMMENT." ".ADLAN_114.": <a href='".e_ADMIN."comment.php'>".$comments."</a></div>";

					if(vartrue($pref['e_status_list']))
					{
						foreach($pref['e_status_list'] as $val)
						{
							if (is_readable(e_PLUGIN.$val.'/e_status.php'))
							{
								include_once(e_PLUGIN.$val.'/e_status.php');
							}
						}
					}

					if($flo = $sql->db_Count('generic', '(*)', "WHERE gen_type='failed_login'"))
					{
						$text .= "<img src='".e_IMAGE."admin_images/failedlogin_16.png' alt='' class='icon S16' /> <a href='".e_ADMIN."fla.php'>".ADLAN_146.": $flo</a>";
					}
					$text .= "</div>";
					return $ns -> tablerender(LAN_STATUS, $text, '', TRUE);
				}
			}

			if ($parm == 'request')
			{
				if (function_exists('status_request'))
				{
					if (status_request())
					{
						return admin_status();
					}
				}
			}
			else
			{
				return admin_status();
			}
		}
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

	function sc_admin_alt_nav($parm)
	{
		if (ADMIN)
		{
			global $sql, $pref, $tp;
			parse_str($parm);
			require(e_ADMIN.'ad_links.php');
			require_once(e_HANDLER.'admin_handler.php');
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
				$text = "<script type='text/javascript' src='".e_FILE_ABS."nav_menu.js'></script>";
			}

			$text .= "<div style='width: 100%'><table border='0' cellspacing='0' cellpadding='0' style='width: 100%'>
			<tr><td>
			<div class='menuBar' style='width: 100%'>";

			$text .= adnav_cat(ADLAN_151, e_ADMIN.'admin.php', E_16_NAV_MAIN);

	   		for ($i = 1; $i < 6; $i++)
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
            /*
			if (getperms('Z'))
			{
				$pclass_extended = $active_plugs ? 'header' : '';
				$plugin_text = adnav_main(ADLAN_98, e_ADMIN.'plugin.php', E_16_PLUGMANAGER, FALSE, $pclass_extended);
				$render_plugins = TRUE;
			}*/

			if ($render_plugins)
			{
				$text .= adnav_cat(ADLAN_CL_7, '', E_16_CAT_PLUG, 'plugMenu');
				$text .= "<div id='plugMenu' class='menu' onmouseover=\"menuMouseover(event)\">";
				$text .= varset($plugin_text).varset($plugs_text);
				$text .= "</div>";
			}

			$text .= adnav_cat(ADLAN_CL_8, '', E_16_CAT_ABOUT, 'docsMenu'); //E_16_NAV_DOCS
			$text .= "<div id='docsMenu' class='menu' onmouseover=\"menuMouseover(event)\">";
			if (!is_readable(e_DOCS.e_LANGUAGE."/")) // warning killed
			{
				$handle=opendir(e_DOCS.'English/');
			}
			$i=1;
			if(varset($handle))
			{
				while ($file = readdir($handle))
				{
					if ($file != '.' && $file != '..' && $file != 'CVS')
					{
						$text .= adnav_main(str_replace('_', ' ', $file), e_ADMIN.'docs.php?'.$i, E_16_DOCS);
						$i++;
					}
				}
			}
			closedir($handle);
			$text .= '</div>';


			$text .= '</div>
			</td>';

			if (varset($exit) != 'off')
			{
				$text .= "<td style='width: 160px; white-space: nowrap'>
				<div class='menuBar' style='width: 100%'>";

				$text .= adnav_cat(ADLAN_53, e_BASE.'index.php', E_16_NAV_LEAV);
				$text .= adnav_cat(ADLAN_46, e_ADMIN.'admin.php?logout', E_16_NAV_LGOT);

				$text .= '</div>
				</td>';
			}

			$text .= '</tr>
			</table>
			</div>';

			return $text;
		}
	}

	function sc_admin_navigation($parm)
	{
		
		if (!ADMIN) return '';
		global $admin_cat, $array_functions, $array_sub_functions, $pref;

		$e107 = &e107::getInstance();
		$sql = &$e107->sql;

		parse_str($parm, $parms);
		$tmpl = strtoupper(varset($parms['tmpl'], 'E_ADMIN_NAVIGATION'));
		global $$tmpl;

		require(e_ADMIN.'ad_links.php');
		require_once(e_HANDLER.'admin_handler.php');



		// MAIN LINK
		$menu_vars = array();
		$menu_vars['main']['text'] = ADLAN_151;
		$menu_vars['main']['link'] = e_ADMIN_ABS.'admin.php';
		$menu_vars['main']['image'] = "<img src='".E_16_NAV_MAIN."' alt='".ADLAN_151."' class='icon S16' />";
		$menu_vars['main']['image_src'] = ADLAN_151;
		$menu_vars['main']['perm'] = '';

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
			$menu_vars[$id]['perm'] = '';
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

				if($pref['admin_slidedown_subs'] && vartrue($array_sub_functions[$key]))
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

   		if($sql->db_Select("plugin", "*", "plugin_installflag=1 ORDER BY plugin_path"))
		{
			while($row = $sql->db_Fetch())
			{
				if($plug->parse_plugin($row['plugin_path']))
				{
					$plug_vars = $plug->plug_vars;
					e107::loadLanFiles($row['plugin_path'], 'admin');
					if(varset($plug_vars['adminLinks']['link']))
					{		
						
						$plugpath = varset($plug_vars['plugin_php']) ? e_PLUGIN_ABS : e_PLUGIN_ABS.$row['plugin_path'].'/';
						$icon_src = varset($plug_vars['administration']['iconSmall']) ? $plugpath.$plug_vars['administration']['iconSmall'] : '';
						$icon_src_lrg = varset($plug_vars['administration']['icon']) ? $plugpath.$plug_vars['administration']['iconSmall'] : '';
						$id = 'plugnav-'.$row['plugin_path'];
					
           	  			$tmp[$id]['text'] = e107::getParser()->toHTML($plug_vars['@attributes']['name'], FALSE, "LINKTEXT");
						$tmp[$id]['description'] = $plug_vars['description'];
						$tmp[$id]['link'] = e_PLUGIN_ABS.$row['plugin_path'].'/'.$plug_vars['administration']['configFile'];
					 	$tmp[$id]['image'] = $icon_src ? "<img src='{$icon_src}' alt=\"".varset($tmp[$id]['text'])."\" class='icon S16' />" : E_16_PLUGIN;
						$tmp[$id]['image_large'] = $icon_src_lrg ? "<img src='{$icon_src_lrg}' alt=\"".varset($tmp[$id]['text'])."\" class='icon S32' />" : $icon_src_lrg;
						$tmp[$id]['image_src'] = $icon_src;
						$tmp[$id]['image_large_src'] = $icon_src_lrg;
						$tmp[$id]['perm'] = 'P'.$row['plugin_id'];
						$tmp[$id]['sub_class'] = '';
						$tmp[$id]['sort'] = 2;
						$tmp[$id]['category'] = $row['plugin_category'];

						if($pref['admin_slidedown_subs'] && vartrue($plug_vars['adminLinks']['link']))
						{
							$tmp[$id]['sub_class'] = 'sub';
							$tmp[$id]['sort'] = false;
							foreach ($plug_vars['adminLinks']['link'] as $subkey => $plugsub)
							{
								$subid = $id.'-'.$subkey;
								$predef_icons = array('add', 'manage', 'settings');
								$title = $plugsub['@value'];
								$plugsub = $plugsub['@attributes'];
								
								if(varset($plugsub['primary'])=='true') // remove primary links. 
								{
									continue;
								}
								
								$icon_src = in_array($plugsub['icon'], $predef_icons) ? e_IMAGE_ABS."admin_images/{$plugsub['icon']}_16.png" : ( $plugsub['icon'] ? $plugpath.$plugsub['icon'] : '');


								$tmp[$id]['sub'][$subid]['text'] = e107::getParser()->toHTML($title, FALSE, 'LINKTEXT');
								$tmp[$id]['sub'][$subid]['description'] = e107::getParser()->toHTML($plug_vars['description']);
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
			}

       //     print_a($menu_vars);
		// ------------------------------------------------------------------

		//added option to disable leave/logout (ll) - more flexibility for theme developers 
		if(!varsettrue($parms['disable_ll']))
		{
			$menu_vars['home']['text'] = ADLAN_53;
			$menu_vars['home']['link'] = e_BASE.'index.php';
			$menu_vars['home']['image'] = "<img src='".E_16_NAV_LEAV."' alt='".ADLAN_151."' class='icon S16' />";
			$menu_vars['home']['image_src'] = ADLAN_151;
			$menu_vars['home']['perm'] = '';
	
			$menu_vars['logout']['text'] = ADLAN_46;
			$menu_vars['logout']['link'] = e_ADMIN_ABS.'admin.php?logout';
			$menu_vars['logout']['image'] = "<img src='".E_16_NAV_LGOT."' alt='".ADLAN_151."' class='icon S16' />";
			$menu_vars['logout']['image_src'] = ADLAN_46;
			$menu_vars['logout']['perm'] = '';
		}

		return e_admin_menu('', '', $menu_vars, $$tmpl, false, false);
	}

	function sc_admin_menumanager()  // List all menu-configs for easy-navigation
	{
    	global $pref;
        $action = "";

        $var['menumanager']['text'] = LAN_MENULAYOUT;
		$var['menumanager']['link'] = e_ADMIN_ABS."menus.php";

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
        	if(strpos(e_SELF,$link['link']))
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

	  e_admin_menu(ADLAN_6,$action, $var);

	}

}


?>