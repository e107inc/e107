<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_admin/plugin.php,v $
|     $Revision: 1.37 $
|     $Date: 2009-08-15 15:44:37 $
|     $Author: marj_nl_fr $
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");
if (!getperms("Z")) 
{
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'plug_manage';

define('PLUGIN_SHOW_REFRESH', FALSE);

global $user_pref;


require_once(e_HANDLER.'plugin_class.php');
require_once(e_HANDLER.'file_class.php');
require_once(e_HANDLER."form_handler.php");
require_once (e_HANDLER.'message_handler.php');


$plugin = new e107plugin;
$frm = new e_form();
$pman = new pluginManager;
define("e_PAGETITLE",ADLAN_98." - ".$pman->pagetitle);
require_once("auth.php");
$pman->pluginObserver();




require_once("footer.php");
exit;

class pluginManager{

	var $plugArray;
	var $action;
	var $id;
	var $frm;
	var $fields;
	var $fieldpref;
	var $titlearray = array();
	var $pagetitle;

	function pluginManager()
	{
        global $user_pref,$admin_log;

        $tmp = explode('.', e_QUERY);
	  	$this -> action = ($tmp[0]) ? $tmp[0] : "installed";
		$this -> id = varset($tmp[1]) ? intval($tmp[1]) : "";
		$this -> titlearray = array('installed'=>EPL_ADLAN_22,'avail'=>EPL_ADLAN_23, 'upload'=>EPL_ADLAN_38);

        $keys = array_keys($this -> titlearray);
		$this->pagetitle = (in_array($this->action,$keys)) ? $this -> titlearray[$this->action] : $this -> titlearray['installed'];


		$this-> fields = array(

		   		"plugin_checkboxes"		=> array("title" => "", "forced"=>TRUE, "width"=>"3%"),
				"plugin_icon"			=> array("title" => EPL_ADLAN_82, "type"=>"image", "width" => "5%", "thclass" => "middle center", "url" => ""),
				"plugin_name"			=> array("title" => EPL_ADLAN_10, "type"=>"text", "width" => "30", "thclass" => "middle", "url" => ""),
 				"plugin_version"		=> array("title" => EPL_ADLAN_11, "type"=>"numeric", "width" => "5%", "thclass" => "middle", "url" => ""),
    			"plugin_folder"			=> array("title" => EPL_ADLAN_64, "type"=>"text", "width" => "10%", "thclass" => "middle", "url" => ""),
				"plugin_category"		=> array("title" => LAN_CATEGORY, "type"=>"text", "width" => "15%", "thclass" => "middle", "url" => ""),
                "plugin_author"			=> array("title" => EPL_ADLAN_12, "type"=>"text", "width" => "auto", "thclass" => "middle", "url" => ""),
  				"plugin_website"		=> array("title" => EPL_WEBSITE, "type"=>"url", "width" => "5%", "thclass" => "middle center", "url" => ""),
				"plugin_notes"			=> array("title" => EPL_ADLAN_83, "type"=>"url", "width" => "5%", "thclass" => "middle center", "url" => ""),
				"plugin_description"	=> array("title" => EPL_ADLAN_14, "type"=>"text", "width" => "auto", "thclass" => "middle center", "url" => ""),
			   	"plugin_compatible"		=> array("title" => EPL_ADLAN_13, "type"=>"text", "width" => "auto", "thclass" => "middle", "url" => ""),
				"plugin_compliant"		=> array("title" => EPL_ADLAN_81, "type"=>"text", "width" => "5%", "thclass" => "middle center", "url" => ""),
				"options"				=> array("title" => LAN_OPTIONS, "width" => "15%", "thclass" => "middle center last", "url" => "")
		);



/*		if(isset($_POST['uninstall-selected']))
		{
        	foreach($_POST['plugin_checkbox'] as $val)
			{
            	$this -> id = intval($val);
                $this -> pluginUninstall();
			}
      		$this -> action = "installed";
			$this -> pluginRenderList();
			return;

			// Complicated, as each uninstall system is different.
		}*/






    }

    function pluginObserver()
	{
        global $user_pref,$admin_log;
    	if (isset($_POST['upload']))
		{
        	$this -> pluginProcessUpload();
		}

        if(isset($_POST['submit-e-columns']))
		{
			$user_pref['admin_pluginmanager_columns'] = $_POST['e-columns'];
			save_prefs('user');
		}

        $this -> fieldpref = (is_array($user_pref['admin_pluginmanager_columns'])) ? $user_pref['admin_pluginmanager_columns'] : array("plugin_icon","plugin_name","plugin_version","plugin_description","plugin_category","plugin_author","plugin_website","plugin_notes");



        if($this->action == 'avail' || $this->action == 'installed')   // Plugin Check is done during upgrade_routine.
		{
			$this -> pluginCheck();
		}

		if($this->action == "uninstall")
		{
        	$this -> pluginUninstall();
		}

        if($this->action == "install")
		{
        	$this -> pluginInstall();
    		$this -> action = "installed";
		}



		if($this->action == "upgrade")
		{
        	$this -> pluginUpgrade();
      		$this -> action = "installed";
		}

		if($this->action == "refresh")
		{
        	$this -> pluginRefresh();
		}
		if($this->action == "upload")
		{
        	$this -> pluginUpload();
		}

		if(isset($_POST['install-selected']))
		{
        	foreach($_POST['plugin_checkbox'] as $val)
			{
            	$this -> id = intval($val);
                $this -> pluginInstall();
			}
      		$this -> action = "installed";
		}

        if($this->action != 'avail')
		{
			unset($this-> fields['plugin_checkboxes']);
		}

		if($this->action !='upload' && $this->action !='uninstall')
		{
			$this -> pluginRenderList();
		}

	}

	function pluginUninstall()
	{
         global $plugin,$admin_log,$pref,$tp,$sql;

			if(!isset($_POST['uninstall_confirm']))
			{	// $id is already an integer
				$this->pluginConfirmUninstall($this->id);
   				return;
			}

			$plug = $plugin->getinfo($this->id);
			$text = '';
			//Uninstall Plugin
			if ($plug['plugin_installflag'] == TRUE )
			{
				$_path = e_PLUGIN.$plug['plugin_path'].'/';
				if(file_exists($_path.'plugin.xml'))
				{
					$options = array(
						'del_tables' => varset($_POST['delete_tables'],FALSE),
						'del_userclasses' => varset($_POST['delete_userclasses'],FALSE),
						'del_extended' => varset($_POST['delete_xfields'],FALSE)
						);
					$text .= $plugin->manage_plugin_xml($this->id, 'uninstall', $options);
				}
				else
				{
					include(e_PLUGIN.$plug['plugin_path'].'/plugin.php');

					$func = $eplug_folder.'_uninstall';
					if (function_exists($func))
					{
						$text .= call_user_func($func);
					}

					if($_POST['delete_tables'])
					{
						if (is_array($eplug_table_names))
						{
							$result = $plugin->manage_tables('remove', $eplug_table_names);
							if ($result !== TRUE)
							{
								$text .= EPL_ADLAN_27.' <b>'.$mySQLprefix.$result.'</b> - '.EPL_ADLAN_30.'<br />';
							}
							else
							{
								$text .= EPL_ADLAN_28."<br />";
							}
						}
					}
					else
					{
						$text .= EPL_ADLAN_49."<br />";
					}

					if (is_array($eplug_prefs))
					{
						$plugin->manage_prefs('remove', $eplug_prefs);
						$text .= EPL_ADLAN_29."<br />";
					}

					if (is_array($eplug_comment_ids))
					{
						$text .= ($plugin->manage_comments('remove', $eplug_comment_ids)) ? EPL_ADLAN_50."<br />" : "";
					}

		/* Not used in 0.8
					if ($eplug_module)
					{
						$plugin->manage_plugin_prefs('remove', 'modules', $eplug_folder);
					}
					if ($eplug_status)
					{
						$plugin->manage_plugin_prefs('remove', 'plug_status', $eplug_folder);
					}

					if ($eplug_latest)
					{
						$plugin->manage_plugin_prefs('remove', 'plug_latest', $eplug_folder);
					}
		*/
					if (is_array($eplug_array_pref))
					{
						foreach($eplug_array_pref as $key => $val)
						{
							$plugin->manage_plugin_prefs('remove', $key, $eplug_folder, $val);
						}
					}

		/* Not used in 0.8
					if (is_array($eplug_sc))
					{
						$plugin->manage_plugin_prefs('remove', 'plug_sc', $eplug_folder, $eplug_sc);
					}

					if (is_array($eplug_bb))
					{
						$plugin->manage_plugin_prefs('remove', 'plug_bb', $eplug_folder, $eplug_bb);
					}
		*/
					if ($eplug_menu_name)
					{
						$sql->db_Delete('menus', "menu_name='$eplug_menu_name' ");
					}

					if ($eplug_link)
					{
						$plugin->manage_link('remove', $eplug_link_url, $eplug_link_name);
					}

					if ($eplug_userclass)
					{
						$plugin->manage_userclass('remove', $eplug_userclass);
					}

					$sql->db_Update('plugin', "plugin_installflag=0, plugin_version='{$eplug_version}' WHERE plugin_id='{$this->id}' ");
					$plugin->manage_search('remove', $eplug_folder);

					$plugin->manage_notify('remove', $eplug_folder);
				}

				$admin_log->log_event('PLUGMAN_03', $plug['plugin_path'], E_LOG_INFORMATIVE, '');
                print_a($pref['plug_installed']);
				if (isset($pref['plug_installed'][$plug['plugin_path']]))
				{
					print_a($plug);
					unset($pref['plug_installed'][$plug['plugin_path']]);
					if(save_prefs())
					{
                    	echo "WORKED";
					}
					else
					{
                    	echo "FAILED";
					}
				}
			}

			if($_POST['delete_files'])
			{
				include_once(e_HANDLER."file_class.php");
				$fi = new e_file;
				$result = $fi->rmtree(e_PLUGIN.$eplug_folder);
				$text .= ($result ? "<br />All files removed from ".e_PLUGIN.$eplug_folder : '<br />File deletion failed<br />'.EPL_ADLAN_31.' <b>'.e_PLUGIN.$eplug_folder.'</b> '.EPL_ADLAN_32);
			}
			else
			{
				$text .= '<br />'.EPL_ADLAN_31.' <b>'.e_PLUGIN.$eplug_folder.'</b> '.EPL_ADLAN_32;
			}

			$plugin->save_addon_prefs();

			$this->show_message($text, E_MESSAGE_SUCCESS);
		 //	$ns->tablerender(EPL_ADLAN_1.' '.$tp->toHtml($plug['plugin_name'], "", "defs,emotes_off,no_make_clickable"), $text);
			$text = '';
			$this->action = "installed";
			return;

   }

   function pluginProcessUpload()
   {
			if (!$_POST['ac'] == md5(ADMINPWCHANGE))
			{
				exit;
			}

			extract($_FILES);
			/* check if e_PLUGIN dir is writable ... */
			if(!is_writable(e_PLUGIN))
			{
				/* still not writable - spawn error message */
				e107::getRender()->tablerender(EPL_ADLAN_40, EPL_ADLAN_39);
			}
			else
			{
				/* e_PLUGIN is writable - continue */
				$pref['upload_storagetype'] = "1";
				require_once(e_HANDLER."upload_handler.php");
				$fileName = $file_userfile['name'][0];
				$fileSize = $file_userfile['size'][0];
				$fileType = $file_userfile['type'][0];

				if(strstr($file_userfile['type'][0], "gzip"))
				{
					$fileType = "tar";
				}
				else if (strstr($file_userfile['type'][0], "zip"))
				{
					$fileType = "zip";
				}
				else
				{
					/* not zip or tar - spawn error message */
					e107::getRender()->tablerender(EPL_ADLAN_40, EPL_ADLAN_41);
					require_once("footer.php");
					exit;
				}

				if ($fileSize)
				{

					$opref = $pref['upload_storagetype'];
					$pref['upload_storagetype'] = 1;		/* temporarily set upload type pref to flatfile */
					$uploaded = file_upload(e_PLUGIN);
					$pref['upload_storagetype'] = $opref;

					$archiveName = $uploaded[0]['name'];

					/* attempt to unarchive ... */

					if($fileType == "zip")
					{
						require_once(e_HANDLER."pclzip.lib.php");
						$archive = new PclZip(e_PLUGIN.$archiveName);
						$unarc = ($fileList = $archive -> extract(PCLZIP_OPT_PATH, e_PLUGIN, PCLZIP_OPT_SET_CHMOD, 0666));
					}
					else
					{
						require_once(e_HANDLER."pcltar.lib.php");
						$unarc = ($fileList = PclTarExtract($archiveName, e_PLUGIN));
					}

					if(!$unarc)
					{
						/* unarc failed ... */
						if($fileType == "zip")
						{
							$error = EPL_ADLAN_46." '".$archive -> errorName(TRUE)."'";
						}
						else
						{
							$error = EPL_ADLAN_47.PclErrorString().", ".EPL_ADLAN_48.intval(PclErrorCode());
						}
						e107::getRender()->tablerender(EPL_ADLAN_40, EPL_ADLAN_42." ".$archiveName." ".$error);
						require_once("footer.php");
						exit;
					}

					/* ok it looks like the unarc succeeded - continue */

					/* get folder name ... */
					$folderName = substr($fileList[0]['stored_filename'], 0, (strpos($fileList[0]['stored_filename'], "/")));

					if(file_exists(e_PLUGIN.$folderName."/plugin.php") || file_exists(e_PLUGIN.$folderName."/plugin.xml"))
					{
						/* upload is a plugin */
						e107::getRender()->tablerender(EPL_ADLAN_40, EPL_ADLAN_43);
					}
					else
					{
						/* upload is a menu */
						e107::getRender()->tablerender(EPL_ADLAN_40, EPL_ADLAN_45);
					}

					/* attempt to delete uploaded archive */
					@unlink(e_PLUGIN.$archiveName);
				}
			}
   }


// -----------------------------------------------------------------------------

   function pluginInstall()
   {
        global $plugin,$admin_log;

			$text = $plugin->install_plugin($this->id);
			if ($text === FALSE)
			{ // Tidy this up
				$this->show_message("Error messages above this line", E_MESSAGE_ERROR);
			}
			else
			{
				$plugin ->save_addon_prefs();
		//	if($eplug_conffile){ $text .= "&nbsp;<a href='".e_PLUGIN."$eplug_folder/$eplug_conffile'>[".LAN_CONFIGURE."]</a>"; }
				$admin_log->log_event('PLUGMAN_01', $this->id.':'.$eplug_folder, E_LOG_INFORMATIVE, '');
				$this->show_message($text, E_MESSAGE_SUCCESS);
			}

   }


// -----------------------------------------------------------------------------

   function pluginUpgrade()
   {
       global $plugin,$pref;

			$plug = $plugin->getinfo($this->id);

			$_path = e_PLUGIN.$plug['plugin_path'].'/';
			if(file_exists($_path.'plugin.xml'))
			{
				$text .= $plugin->manage_plugin_xml($this->id, 'upgrade');
			}
			else
			{
				include(e_PLUGIN.$plug['plugin_path'].'/plugin.php');

				$func = $eplug_folder.'_upgrade';
				if (function_exists($func))
				{
					$text .= call_user_func($func);
				}

				if (is_array($upgrade_alter_tables))
				{
					$result = $plugin->manage_tables('upgrade', $upgrade_alter_tables);
					if (!$result)
					{
						$text .= EPL_ADLAN_9.'<br />';
					}
					else
					{
						$text .= EPL_ADLAN_7."<br />";
					}
				}

		/* Not used in 0.8
				if ($eplug_module)
				{
					$plugin->manage_plugin_prefs('add', 'modules', $eplug_folder);
				}
				else
				{
					$plugin->manage_plugin_prefs('remove', 'modules', $eplug_folder);
				}

				if ($eplug_status)
				{
					$plugin->manage_plugin_prefs('add', 'plug_status', $eplug_folder);
				}
				else
				{
					$plugin->manage_plugin_prefs('remove', 'plug_status', $eplug_folder);
				}

				if ($eplug_latest)
				{
					$plugin->manage_plugin_prefs('add', 'plug_latest', $eplug_folder);
				}
				else
				{
					$plugin->manage_plugin_prefs('remove', 'plug_latest', $eplug_folder);
				}

				if (is_array($upgrade_add_eplug_sc))
				{
					$plugin->manage_plugin_prefs('add', 'plug_sc', $eplug_folder, $eplug_sc);
				}

				if (is_array($upgrade_remove_eplug_sc))
				{
					$plugin->manage_plugin_prefs('remove', 'plug_sc', $eplug_folder, $eplug_sc);
				}

				if (is_array($upgrade_add_eplug_bb))
				{
					$plugin->manage_plugin_prefs('add', 'plug_bb', $eplug_folder, $eplug_bb);
				}

				if (is_array($upgrade_remove_eplug_bb))
				{
					$plugin->manage_plugin_prefs('remove', 'plug_bb', $eplug_folder, $eplug_bb);
				}
		*/
				if (is_array($upgrade_add_prefs))
				{
					$plugin->manage_prefs('add', $upgrade_add_prefs);
					$text .= EPL_ADLAN_8.'<br />';
				}

				if (is_array($upgrade_remove_prefs))
				{
					$plugin->manage_prefs('remove', $upgrade_remove_prefs);
				}

				if (is_array($upgrade_add_array_pref))
				{
					foreach($upgrade_add_array_pref as $key => $val)
					{
						$plugin->manage_plugin_prefs('add', $key, $eplug_folder, $val);
					}
				}

				if (is_array($upgrade_remove_array_pref))
				{
					foreach($upgrade_remove_array_pref as $key => $val)
					{
						$plugin->manage_plugin_prefs('remove', $key, $eplug_folder, $val);
					}
				}

				$plugin->manage_search('upgrade', $eplug_folder);
				$plugin->manage_notify('upgrade', $eplug_folder);

				$eplug_addons = $plugin -> getAddons($eplug_folder);

				$admin_log->log_event('PLUGMAN_02', $eplug_folder, E_LOG_INFORMATIVE, '');
				$text .= (isset($eplug_upgrade_done)) ? '<br />'.$eplug_upgrade_done : "<br />".LAN_UPGRADE_SUCCESSFUL;
				$sql->db_Update('plugin', "plugin_version ='{$eplug_version}', plugin_addons='{$eplug_addons}' WHERE plugin_id='$this->id' ");
				$pref['plug_installed'][$plug['plugin_path']] = $eplug_version; 			// Update the version
				save_prefs();
			}
			e107::getRender()->tablerender(EPL_ADLAN_34, $text);

			$plugin->save_addon_prefs();

   }


// -----------------------------------------------------------------------------

   function pluginRefresh()
   {
       global $plug;

			$plug = $plugin->getinfo($this->id);

			$_path = e_PLUGIN.$plug['plugin_path'].'/';
			if(file_exists($_path.'plugin.xml'))
			{
				$text .= $plugin->manage_plugin_xml($this->id, 'refresh');
				$admin_log->log_event('PLUGMAN_04', $this->id.':'.$plug['plugin_path'], E_LOG_INFORMATIVE, '');
			}

    }

// -----------------------------------------------------------------------------

		// Check for new plugins, create entry in plugin table ...
    function pluginCheck()
	{
		global $plugin;
		$plugin->update_plugins_table();
    }
		// ----------------------------------------------------------
		//        render plugin information ...


// -----------------------------------------------------------------------------


    function pluginUpload()
	{
         global $plugin,$frm;

		/* plugin upload form */

			if(!is_writable(e_PLUGIN))
			{
			   	e107::getRender()->tablerender(EPL_ADLAN_40, EPL_ADLAN_44);
			}
			else
			{
			  // Get largest allowable file upload
			  require_once(e_HANDLER.'upload_handler.php');
			  $max_file_size = get_user_max_upload();

			  $text = "
				<form enctype='multipart/form-data' method='post' action='".e_SELF."'>
                <table cellpadding='0' cellspacing='0' class='adminform'>
                	<colgroup span='2'>
                		<col class='col-label' />
                		<col class='col-control' />
                	</colgroup>
				<tr>
				<td>".EPL_ADLAN_37."</td>
				<td>
				<input type='hidden' name='MAX_FILE_SIZE' value='{$max_file_size}' />
				<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
				<input class='tbox' type='file' name='file_userfile[]' size='50' />
				</td>
				</tr>
                </tr>
				</table>

				<div class='center buttons-bar'>";
                $text .= $frm->admin_button('upload', EPL_ADLAN_38, 'submit', EPL_ADLAN_38);

				$text .= "
				</div>

				</form>\n";
			}

         e107::getRender()->tablerender(ADLAN_98." :: ".EPL_ADLAN_38, $text);
	}

// -----------------------------------------------------------------------------

	function pluginRenderList() // Uninstall and Install sorting should be fixed once and for all now !
	{

		global $plugin, $frm;
		$e107 = &e107::getInstance();
		
		if($this->action == "" || $this->action == "installed")
		{
			$installed = $plugin->getall(1);
			$caption = EPL_ADLAN_22;
			$pluginRenderPlugin = $this->pluginRenderPlugin($installed);
			$button_mode = "uninstall-selected";
			$button_caption = EPL_ADLAN_85;
			$button_action = "delete";
		}
		if($this->action == "avail")
		{
			$uninstalled = $plugin->getall(0);
			$caption = EPL_ADLAN_23;
			$pluginRenderPlugin = $this->pluginRenderPlugin($uninstalled);
			$button_mode = "install-selected";
			$button_caption = EPL_ADLAN_84;
			$button_action = "update";
		}

		$text = "
			<form action='".e_SELF."?".e_QUERY."' id='core-plugin-list-form' method='post'>
				<fieldset id='core-plugin-list'>
					<legend class='e-hideme'>".$caption."</legend>
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						".$frm->colGroup($this->fields,$this->fieldpref).
						$frm->thead($this->fields,$this->fieldpref)."
						<tbody>
		";

		$text .= $pluginRenderPlugin;

		$text .= "
						</tbody>
					</table>";

		if($this->action == "avail")
		{
			$text .= "
					<div class='buttons-bar left'>".$frm->admin_button($button_mode, $button_caption, $button_action)."</div>";
		}
		$text .= "
				</fieldset>
			</form>
		";

		$emessage = &eMessage::getInstance();
		e107::getRender()->tablerender(ADLAN_98." :: ".$caption, $emessage->render(). $text);
	}


// -----------------------------------------------------------------------------

	function pluginRenderPlugin($pluginList)
	{
			global $tp, $plugin, $frm;

			if (empty($pluginList)) return '';

			$text = "";

			foreach($pluginList as $plug)
			{
				$_path = e_PLUGIN.$plug['plugin_path'].'/';
				$plug_vars = false;
				$plugin_config_icon = "";

				if($plugin->parse_plugin($plug['plugin_path']))
				{
					$plug_vars = $plugin->plug_vars;
				}
				if($plug_vars)
				{

					$icon_src = (isset($plug_vars['plugin_php']) ? e_PLUGIN : $_path).$plug_vars['administration']['icon'];
					$plugin_icon = $plug_vars['administration']['icon'] ? "<img src='{$icon_src}' alt='' class='icon S32' />" : E_32_CAT_PLUG;
                    $conf_file = "#";
					$conf_title = "";

					if ($plug_vars['administration']['configFile'] && $plug['plugin_installflag'] == true)
					{
						$conf_file = e_PLUGIN.$plug['plugin_path'].'/'.$plug_vars['administration']['configFile'];
						$conf_title = LAN_CONFIGURE.' '.$tp->toHtml($plug_vars['@attributes']['name'], "", "defs,emotes_off, no_make_clickable");
						$plugin_icon = "<a title='{$conf_title}' href='{$conf_file}' >".$plugin_icon."</a>";
						$plugin_config_icon = "<a title='{$conf_title}' href='{$conf_file}' >".ADMIN_CONFIGURE_ICON."</a>";
					}

					$plugEmail = varset($plug_vars['author']['@attributes']['email'],'');
					$plugAuthor = varset($plug_vars['author']['@attributes']['name'],'');
					$plugURL = varset($plug_vars['author']['@attributes']['url'],'');
                    $plugReadme = "";
					if(varset($plug['plugin_installflag']))
					{
						$plugName = "<a title='{$conf_title}' href='{$conf_file}' >".$tp->toHTML($plug['plugin_name'], false, "defs,emotes_off, no_make_clickable")."</a>";
                    }
                    else
					{
                    	$plugName = $tp->toHTML($plug['plugin_name'], false, "defs,emotes_off, no_make_clickable");
					}
					if(varset($plug_vars['readme']))   // 0.7 plugin.php
					{
                    	$plugReadme = $plug_vars['readme'];
					}
					if(varset($plug_vars['readMe'])) // 0.8 plugin.xml
					{
                    	$plugReadme = $plug_vars['readMe'];
					}

					$text .= "<tr>";

					if(varset($this-> fields['plugin_checkboxes']))
					{
                 		$rowid = "plugin_checkbox[".$plug['plugin_id']."]";
                		$text .= "<td class='center middle'>".$frm->checkbox($rowid, $plug['plugin_id'])."</td>\n";
					}

				//	$text .= (in_array("plugin_status",$this->fieldpref)) ? "<td class='center'>".$img."</td>" : "";
                    $text .= (in_array("plugin_icon",$this->fieldpref)) ? "<td class='center middle'>".$plugin_icon."</td>" : "";
                    $text .= (in_array("plugin_name",$this->fieldpref)) ? "<td class='middle'>".$plugName."</td>" : "";
                    $text .= (in_array("plugin_version",$this->fieldpref)) ? "<td class='middle'>".$plug['plugin_version']."</td>" : "";
					$text .= (in_array("plugin_folder",$this->fieldpref)) ? "<td class='middle'>".$plug['plugin_path']."</td>" : "";
					$text .= (in_array("plugin_category",$this->fieldpref)) ? "<td class='middle'>".$plug['plugin_category']."</td>" : "";
                    $text .= (in_array("plugin_author",$this->fieldpref)) ? "<td class='middle'><a href='mailto:".$plugEmail."' title='".$plugEmail."'>".$plugAuthor."</a>&nbsp;</td>" : "";
                    $text .= (in_array("plugin_website",$this->fieldpref)) ? "<td class='center middle'>".($plugURL ? "<a href='{$plugURL}' title='{$plugURL}' >".ADMIN_URL_ICON."</a>" : "")."</td>" : "";
                    $text .= (in_array("plugin_notes",$this->fieldpref)) ? "<td class='center middle'>".($plugReadme ? "<a href='".e_PLUGIN.$plug['plugin_path']."/".$plugReadme."' title='".$plugReadme."'>".ADMIN_INFO_ICON."</a>" : "&nbsp;")."</td>" : "";
					$text .= (in_array("plugin_description",$this->fieldpref)) ? "<td class='middle'>".$tp->toHTML($plug_vars['description'], false, "defs,emotes_off, no_make_clickable")."</td>" : "";
                    $text .= (in_array("plugin_compatible",$this->fieldpref)) ? "<td class='center middle'>".varset($plug_vars['@attributes']['compatibility'],'')."</td>" : "";
					$text .= (in_array("plugin_compliant",$this->fieldpref)) ? "<td class='center middle'>".((varset($plug_vars['compliant']) || varsettrue($plug_vars['@attributes']['xhtmlcompliant'])) ? ADMIN_TRUE_ICON : "&nbsp;")."</td>" : "";


                	// Plugin options Column --------------

   					$text .= "<td class='center middle'>".$plugin_config_icon;


						if ($plug_vars['@attributes']['installRequired'])
						{
							if ($plug['plugin_installflag'])
							{
						  		$text .= ($plug['plugin_installflag'] ? "<a href=\"".e_SELF."?uninstall.{$plug['plugin_id']}'\" title='".EPL_ADLAN_1."'  >".ADMIN_UNINSTALLPLUGIN_ICON."</a>" : "<a href=\"".e_SELF."?install.{$plug['plugin_id']}\" title='".EPL_ADLAN_0."' >".ADMIN_INSTALLPLUGIN_ICON."</a>");

                             //   $text .= ($plug['plugin_installflag'] ? "<button type='button' class='delete' value='no-value' onclick=\"location.href='".e_SELF."?uninstall.{$plug['plugin_id']}'\"><span>".EPL_ADLAN_1."</span></button>" : "<button type='button' class='update' value='no-value' onclick=\"location.href='".e_SELF."?install.{$plug['plugin_id']}'\"><span>".EPL_ADLAN_0."</span></button>");
								if (PLUGIN_SHOW_REFRESH && !varsettrue($plug_vars['plugin_php']))
								{
									$text .= "<br /><br /><input type='button' class='button' onclick=\"location.href='".e_SELF."?refresh.{$plug['plugin_id']}'\" title='".'Refresh plugin settings'."' value='".'Refresh plugin settings'."' /> ";
								}
							}
							else
							{
							  //	$text .=  "<input type='button' class='button' onclick=\"location.href='".e_SELF."?install.{$plug['plugin_id']}'\" title='".EPL_ADLAN_0."' value='".EPL_ADLAN_0."' />";
							  //	$text .= "<button type='button' class='update' value='no-value' onclick=\"location.href='".e_SELF."?install.{$plug['plugin_id']}'\"><span>".EPL_ADLAN_0."</span></button>";
                            	$text .= "<a href=\"".e_SELF."?install.{$plug['plugin_id']}\" title='".EPL_ADLAN_0."' >".ADMIN_INSTALLPLUGIN_ICON."</a>";
							}
						}
						else
						{
							if ($plug_vars['menuName'])
							{
								$text .= EPL_NOINSTALL.str_replace("..", "", e_PLUGIN.$plug['plugin_path'])."/ ".EPL_DIRECTORY;
							}
							else
							{
								$text .= EPL_NOINSTALL_1.str_replace("..", "", e_PLUGIN.$plug['plugin_path'])."/ ".EPL_DIRECTORY;
								if($plug['plugin_installflag'] == false)
								{
									global $sql;
									$sql->db_Delete('plugin', "plugin_installflag=0 AND (plugin_path='{$plug['plugin_path']}' OR plugin_path='{$plug['plugin_path']}/' )  ");
								}
							}
						}

						if ($plug['plugin_version'] != $plug_vars['@attributes']['version'] && $plug['plugin_installflag'])
						{
						  //	$text .= "<br /><input type='button' class='button' onclick=\"location.href='".e_SELF."?upgrade.{$plug['plugin_id']}'\" title='".EPL_UPGRADE." to v".$plug_vars['@attributes']['version']."' value='".EPL_UPGRADE."' />";
							$text .= "<a href='".e_SELF."?upgrade.{$plug['plugin_id']}' title=\"".EPL_UPGRADE." to v".$plug_vars['@attributes']['version']."\" >".ADMIN_UPGRADEPLUGIN_ICON."</a>";
						}

					$text .="</td>";
                    $text .= "</tr>";

				}
			}
			return $text;
	}


// -----------------------------------------------------------------------------



		function pluginConfirmUninstall()
		{
			global $plugin, $tp;
			$plug = $plugin->getinfo($this->id);

			if ($plug['plugin_installflag'] == true )
			{
				if($plugin->parse_plugin($plug['plugin_path']))
				{
					$plug_vars = $plugin->plug_vars;
				}
				else
				{
					return FALSE;
				}
			}
			else
			{
				return FALSE;
			}
			$userclasses = '';
			$eufields = '';
			if (isset($plug_vars['userclass']))
			{
				if (isset($plug_vars['userclass']['@attributes']))
				{
					$plug_vars['userclass'][0]['@attributes'] = $plug_vars['userclass']['@attributes'];
					unset($plug_vars['userclass']['@attributes']);
				}
				$spacer = '';
				foreach ($plug_vars['userclass'] as $uc)
				{
					$userclasses .= $spacer.$uc['@attributes']['name'].' - '.$uc['@attributes']['description'];
					$spacer = '<br />';
				}
			}
			if (isset($plug_vars['extendedField']))
			{
				if (isset($plug_vars['extendedField']['@attributes']))
				{
					$plug_vars['extendedField'][0]['@attributes'] = $plug_vars['extendedField']['@attributes'];
					unset($plug_vars['extendedField']['@attributes']);
				}
				$spacer = '';
				foreach ($plug_vars['extendedField'] as $eu)
				{
					$eufields .= $spacer.'plugin_'.$plug_vars['folder'].'_'.$eu['@attributes']['name'];
					$spacer = '<br />';
				}
			}

			if(is_writable(e_PLUGIN.$plug['plugin_path']))
			{
				$del_text = "
				<select class='tbox' name='delete_files'>
				<option value='0'>".LAN_NO."</option>
				<option value='1'>".LAN_YES."</option>
				</select>
				";
			}
			else
			{
				$del_text = "
				".EPL_ADLAN_53."
				<input type='hidden' name='delete_files' value='0' />
				";
			}

			$text = "
			<form action='".e_SELF."?".e_QUERY."' method='post'>
			<fieldset id='core-plugin-confirmUninstall'>
			<legend>".EPL_ADLAN_54." ".$tp->toHtml($plug_vars['@attributes']['name'], "", "defs,emotes_off, no_make_clickable")."</legend>
            <table cellpadding='0' cellspacing='0' class='adminform'>
            	<colgroup span='2'>
            		<col class='col-label' />
            		<col class='col-control' />
            	</colgroup>
 			<tr>
			<td>".EPL_ADLAN_55."</td>
			<td>".LAN_YES."</td>
			</tr>
			<tr>
			<td>
			".EPL_ADLAN_57."<div class='smalltext'>".EPL_ADLAN_58."</div>
			</td>
			<td>
			<select class='tbox' name='delete_tables'>
			<option value='1'>".LAN_YES."</option>
			<option value='0'>".LAN_NO."</option>
			</select>
			</td>
			</tr>";

			if ($userclasses)
			{
				$text .= "	<tr>
				<td>
				".EPL_ADLAN_78."<div class='indent'>".$userclasses."</div><div class='smalltext'>".EPL_ADLAN_79."</div>
				</td>
				<td>
					<select class='tbox' name='delete_userclasses'>
					<option value='1'>".LAN_YES."</option>
					<option value='0'>".LAN_NO."</option>
					</select>
				</td>
				</tr>";
			}

			if ($eufields)
			{
				$text .= "	<tr>
				<td>
				".EPL_ADLAN_80."<div class='indent'>".$eufields."</div><div class='smalltext'>".EPL_ADLAN_79."</div>
				</td>
				<td>
					<select class='tbox' name='delete_xfields'>
					<option value='1'>".LAN_YES."</option>
					<option value='0'>".LAN_NO."</option>
					</select>
				</td>
				</tr>";
			}

			$text .="<tr>
			<td>".EPL_ADLAN_59."<div class='smalltext'>".EPL_ADLAN_60."</div></td>
			<td>{$del_text}</td>
			</tr>
			</table>
			<div class='buttons-bar center'>";

			$text .= "<input class='button' type='submit' name='uninstall_confirm' value=\"".EPL_ADLAN_3."\" />&nbsp;&nbsp;
			<input class='button' type='submit' name='uninstall_cancel' value='".EPL_ADLAN_62."' onclick=\"location.href='".e_SELF."'; return false;\"/>";

             //   $frm->admin_button($name, $value, $action = 'submit', $label = '', $options = array());

			$text .= "</div>
			</fieldset>
			</form>
			";
			e107::getRender()->tablerender(EPL_ADLAN_63." ".$tp->toHtml($plug_vars['@attributes']['name'], "", "defs,emotes_off, no_make_clickable"), $text);

		}

        function show_message($message, $type = E_MESSAGE_INFO, $session = false)
		{
		// ##### Display comfort ---------
			$emessage = &eMessage::getInstance();
			$emessage->add($message, $type, $session);
		}

        function pluginOptions()
		{
		   //	$e107 = &e107::getInstance();

				$var['installed']['text'] = EPL_ADLAN_22;
				$var['installed']['link'] = e_SELF;

				$var['avail']['text'] = EPL_ADLAN_23;
				$var['avail']['link'] = e_SELF."?avail";

				$var['upload']['text'] = EPL_ADLAN_38;
				$var['upload']['link'] = e_SELF."?upload";

				$keys = array_keys($var);

				$action = (in_array($this->action,$keys)) ? $this->action : "installed";

				e_admin_menu(ADLAN_98, $action, $var);
		}



} // end of Class.



function plugin_adminmenu()
{
	global $pman;
	$pman -> pluginOptions();
}

?>