<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * $URL$
 * $Id$
 * 
 * Plugin administration area
 *
 */

require_once("../class2.php");
if (!getperms("Z"))
{
	header("location:".e_BASE."index.php");
	exit;
}

// Only tested Locally so far. 
if(e_AJAX_REQUEST && isset($_GET['src'])) // Ajax 
{
	$string =  base64_decode($_GET['src']);	
	parse_str($string,$p);
	$remotefile = $p['plugin_url'];
	
	$localfile = md5($remotefile.time()).".zip";
	$status = "Downloading...";
	
	e107::getFile()->getRemoteFile($remotefile,$localfile);
	
	if(!file_exists(e_TEMP.$localfile))
	{
		echo 'There was a problem retrieving the file';
		exit;	
	}
//	chmod(e_PLUGIN,0777);
	chmod(e_TEMP.$localfile,0755);
	
	require_once(e_HANDLER."pclzip.lib.php");
	$archive = new PclZip(e_TEMP.$localfile);
	$unarc = ($fileList = $archive -> extract(PCLZIP_OPT_PATH, e_PLUGIN, PCLZIP_OPT_SET_CHMOD, 0755));
//	chmod(e_PLUGIN,0755);
	$dir 		= basename($unarc[0]['filename']);
//		chmod(e_UPLOAD.$localfile,0666);



	/* Cannot use this yet until 'folder' is included in feed. 
	if($dir != $p['plugin_folder'])
	{
		
		echo "<br />There is a problem with the data submitted by the author of the plugin.";
		echo "dir=".$dir;
		echo "<br />pfolder=".$p['plugin_folder'];
		exit;
	}	
	*/
		
	if($unarc[0]['folder'] ==1 && is_dir($unarc[0]['filename']))
	{
		$status = "Unzipping...";
		$dir 		= basename($unarc[0]['filename']);
		$plugPath	= preg_replace("/[^a-z0-9-\._]/", "-", strtolower($dir));	
		
		e107::getSingleton('e107plugin')->update_plugins_table();
		e107::getDb()->db_Select_gen("SELECT plugin_id FROM #plugin WHERE plugin_path = '".$plugPath."' LIMIT 1");
		$row = e107::getDb()->db_Fetch(MYSQL_ASSOC);
		$status = e107::getSingleton('e107plugin')->install_plugin($row['plugin_id']);
		//unlink(e_UPLOAD.$localfile);
		
	}
	else 
	{
		// print_a($fileList);
		$status = "Error: <br /><a href='".$remotefile."'>Download Manually</a>";
		//echo $archive->errorInfo(true);
		// $status = "There was a problem";	
		//unlink(e_UPLOAD.$localfile);
	}
	
	echo $status;
//	@unlink(e_TEMP.$localfile);

//	echo "file=".$file;
	exit;	
}

e107::coreLan('plugin', true);

$e_sub_cat = 'plug_manage';

define('PLUGIN_SHOW_REFRESH', FALSE);

global $user_pref;


require_once(e_HANDLER.'plugin_class.php');
require_once(e_HANDLER.'file_class.php');
require_once(e_HANDLER."form_handler.php");
require_once (e_HANDLER.'message_handler.php');

if(isset($_POST['uninstall_cancel']))
{
	header("location:".e_SELF);
	exit;		
}


$plugin = new e107plugin;
$pman = new pluginManager;
define("e_PAGETITLE",ADLAN_98." - ".$pman->pagetitle);
require_once("auth.php");
$pman->pluginObserver();




require_once("footer.php");
exit;

// FIXME switch to admin UI
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
		
		if(isset($_GET['mode']))
		{
			$this->action = $_GET['mode'];
		}

        $keys = array_keys($this -> titlearray);
		$this->pagetitle = (in_array($this->action,$keys)) ? $this -> titlearray[$this->action] : $this -> titlearray['installed'];


		$this-> fields = array(

		   		"plugin_checkboxes"		=> array("title" => "", "forced"=>TRUE, "width"=>"3%"),
				"plugin_icon"			=> array("title" => EPL_ADLAN_82, "type"=>"icon", "width" => "5%", "thclass" => "middle center",'class'=>'center', "url" => ""),
				"plugin_name"			=> array("title" => EPL_ADLAN_10, "type"=>"text", "width" => "30", "thclass" => "middle", "url" => ""),
 				"plugin_version"		=> array("title" => EPL_ADLAN_11, "type"=>"numeric", "width" => "5%", "thclass" => "middle", "url" => ""),
    			"plugin_date"			=> array("title" => "Release ".LAN_DATE, "type"=>"text", "width" => "auto", "thclass" => "middle"),
    			
    			"plugin_folder"			=> array("title" => EPL_ADLAN_64, "type"=>"text", "width" => "10%", "thclass" => "middle", "url" => ""),
				"plugin_category"		=> array("title" => LAN_CATEGORY, "type"=>"text", "width" => "15%", "thclass" => "middle", "url" => ""),
                "plugin_author"			=> array("title" => EPL_ADLAN_12, "type"=>"text", "width" => "auto", "thclass" => "middle", "url" => ""),
  				"plugin_website"		=> array("title" => EPL_WEBSITE, "type"=>"url", "width" => "5%", "thclass" => "middle center", "url" => ""),
				"plugin_notes"			=> array("title" => EPL_ADLAN_83, "type"=>"url", "width" => "5%", "thclass" => "middle center", "url" => ""),
				"plugin_description"	=> array("title" => EPL_ADLAN_14, "type"=>"text", "width" => "auto", "thclass" => "middle center", "url" => ""),
			   	"plugin_compatible"		=> array("title" => EPL_ADLAN_13, "type"=>"text", "width" => "auto", "thclass" => "middle", "url" => ""),
				"plugin_compliant"		=> array("title" => EPL_ADLAN_81, "type"=>"text", "width" => "5%", "thclass" => "middle center", "url" => ""),
		//		"plugin_release"		=> array("title" => EPL_ADLAN_81, "type"=>"text", "width" => "5%", "thclass" => "middle center", "url" => ""),
				"options"				=> array("title" => LAN_OPTIONS, 'forced'=>TRUE, "width" => "15%", "thclass" => "middle center last", "url" => ""),

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

        if(isset($_POST['etrigger_ecolumns']))
		{
			$user_pref['admin_pluginmanager_columns'] = $_POST['e-columns'];
			save_prefs('user');
		}

        $this -> fieldpref = (vartrue($user_pref['admin_pluginmanager_columns'])) ? $user_pref['admin_pluginmanager_columns'] : array("plugin_icon","plugin_name","plugin_version","plugin_description","plugin_category","plugin_author","plugin_website","plugin_notes");



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

		if($this->action == 'create')
		{
			$pc = new pluginBuilder;
			return;
				
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
		
		if($this->action == "online")
		{
        	$this -> pluginOnline();
			return;
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

        if($this->action != 'avail' && varset($this->fields['plugin_checkboxes']))
		{
		 	$this->fields['plugin_checkboxes'] = FALSE;
		}

		if($this->action !='upload' && $this->action !='uninstall')
		{
			$this -> pluginRenderList();
		}



	}
	
	
	function pluginOnline()
	{
		global $plugin;
		$tp = e107::getParser();
		$frm = e107::getForm();
		
		$caption	= "Search Online";
		
		$e107 = e107::getInstance();
		$xml = e107::getXml();
		$mes = e107::getMessage();
		
		$mes->addWarning("This area is experimental and may produce unpredictable results.");

		$from = intval($_GET['frm']);
	
	//	$file = SITEURLBASE.e_PLUGIN_ABS."release/release.php";  // temporary testing
		$file = "http://e107.org/feed?frm=".$from;
		
		$xml->setOptArrayTags('plugin'); // make sure 'plugin' tag always returns an array
		$xdata = $xml->loadXMLfile($file,'advanced');

		$total = $xdata['@attributes']['total'];

		//TODO use admin_ui including filter capabilities by sending search queries back to the xml script. 

		// XML data array. 
		$c = 1;
		foreach($xdata['plugin'] as $r)
		{
			$row = $r['@attributes'];
			
				$data[] = array(
					'plugin_id'				=> $c,
					'plugin_icon'			=> vartrue($row['icon'],e_IMAGE."admin_images/plugins_32.png"),
					'plugin_name'			=> $row['name'],
					'plugin_folder'			=> $row['folder'],
					'plugin_date'			=> vartrue($row['date']),
					'plugin_category'		=> vartrue($r['category'][0]),
					'plugin_author'			=> vartrue($row['author']),
					'plugin_version'		=> $row['version'],
					'plugin_description'	=> $tp->text_truncate(vartrue($r['description'][0]),200),
				
				
					'plugin_website'		=> vartrue($row['authorUrl']),
					'plugin_url'			=> $row['url'],
					'plugin_notes'			=> ''
					);	
			$c++;
		}
	
//	print_a($data);
		$fieldList = $this->fields;
		unset($fieldList['plugin_checkboxes']);
		
		$text = "
			<form action='".e_SELF."?".e_QUERY."' id='core-plugin-list-form' method='post'>
				<fieldset id='core-plugin-list'>
					<legend class='e-hideme'>".$caption."</legend>
					<table class='table adminlist'>
						".$frm->colGroup($fieldList,$this->fieldpref).
						$frm->thead($fieldList,$this->fieldpref)."
						<tbody>
		";	
		
		
	
		
		foreach($data as $key=>$val	)
		{
		//	print_a($val);
			$text .= "<tr>";
						
			foreach($this->fields as $v=>$foo)
			{
				if(!in_array($v,$this->fieldpref) || $v == 'plugin_checkboxes')
				{
					continue;	
				}
				// echo '<br />v='.$v;
				$text .= "<td class='".vartrue($this->fields[$v]['class'],'left')."'>".$frm->renderValue($v, $val[$v], $this->fields[$v])."</td>\n";
			}
			$text .= "<td class='center'>".$this->options($val)."</td>";
			$text .= "</tr>";		
			
		}
		
		
		$text .= "
						</tbody>
					</table>";
		$text .= "
				</fieldset>
			</form>
		";
		
		$amount = 30;
		
		
		if($total > $amount)
		{
			$parms = $total.",".$amount.",".$from.",".e_SELF.'?mode='.$_GET['mode'].'&amp;frm=[FROM]';
			$text .= "<div style='text-align:center;margin-top:10px'>".$tp->parseTemplate("{NEXTPREV=$parms}",TRUE)."</div>";
		}
		
		e107::getRender()->tablerender(ADLAN_98." :: ".$caption, $mes->render(). $text);
	}
	
	
	
	function options($data)
	{		
		$d = http_build_query($data,false,'&');
		$url = e_SELF."?src=".base64_encode($d);
		$id = 'plug_'.$data['plugin_folder'];
		return "<div id='{$id}' style='vertical-align:middle'>
		<button type='button' data-target='{$id}' data-loading='".e_IMAGE."/generic/loading_32.gif' class='btn btn-primary e-ajax middle' value='Download and Install' data-src='".$url."' ><span>Download and Install</span></button>
		</div>";				
	}
	
	
	// FIXME - move it to plugin handler, similar to install_plugin() routine
	function pluginUninstall()
	{
			$pref = e107::getPref();
			$admin_log = e107::getAdminLog();
			$plugin = e107::getPlugin();
			$tp = e107::getParser();
			$sql = e107::getDb();
			$eplug_folder = '';
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
				$eplug_folder = $plug['plugin_path'];
				$_path = e_PLUGIN.$plug['plugin_path'].'/';

				if(file_exists($_path.'plugin.xml'))
				{
					unset($_POST['uninstall_confirm']);
					$text .= $plugin->install_plugin_xml($this->id, 'uninstall', $_POST); //$_POST must be used.
				}
				else
				{	// Deprecated - plugin uses plugin.php
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

					if (is_array($eplug_array_pref))
					{
						foreach($eplug_array_pref as $key => $val)
						{
							$plugin->manage_plugin_prefs('remove', $key, $eplug_folder, $val);
						}
					}

					if ($eplug_menu_name)
					{
						$sql->db_Delete('menus', "menu_name='{$eplug_menu_name}' ");
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
					
					// it's done inside install_plugin_xml(), required only here
					if (isset($pref['plug_installed'][$plug['plugin_path']]))
					{
						unset($pref['plug_installed'][$plug['plugin_path']]);
					}
					e107::getConfig('core')->setPref($pref);
					$plugin->rebuildUrlConfig();
					e107::getConfig('core')->save();
				}

				$admin_log->log_event('PLUGMAN_03', $plug['plugin_path'], E_LOG_INFORMATIVE, '');
			}

			if($_POST['delete_files'])
			{
				include_once(e_HANDLER.'file_class.php');
				$fi = new e_file;
				$result = $fi->rmtree(e_PLUGIN.$eplug_folder);
				$text .= ($result ? '<br />'.EPL_ADLAN_86.e_PLUGIN.$eplug_folder : '<br />'.EPL_ADLAN_87.'<br />'.EPL_ADLAN_31.' <b>'.e_PLUGIN.$eplug_folder.'</b> '.EPL_ADLAN_32);
			}
			else
			{
				$text .= '<br />'.EPL_ADLAN_31.' <b>'.e_PLUGIN.$eplug_folder.'</b> '.EPL_ADLAN_32;
			}

			$plugin->save_addon_prefs();

			$this->show_message($text, E_MESSAGE_SUCCESS);
		 //	$ns->tablerender(EPL_ADLAN_1.' '.$tp->toHtml($plug['plugin_name'], "", "defs,emotes_off,no_make_clickable"), $text);
			$text = '';
			$this->action = 'installed';
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
					$uploaded = file_upload(e_PLUGIN);
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

					/* get folder name ...  */
					$folderName = substr($fileList[0]['stored_filename'], 0, (strpos($fileList[0]['stored_filename'], "/")));

					if(file_exists(e_PLUGIN.$folderName."/plugin.php") || file_exists(e_PLUGIN.$folderName."/plugin.xml"))
					{
						/* upload is a plugin */
						e107::getRender()->tablerender(EPL_ADLAN_40, EPL_ADLAN_43);
					}
					elseif(file_exists(e_PLUGIN.$folderName."/theme.php") || file_exists(e_PLUGIN.$folderName."/theme.xml"))
					{
						/* upload is a menu */
						e107::getRender()->tablerender(EPL_ADLAN_40, EPL_ADLAN_45);
					}
					else
					{
						/* upload is unlocatable */
						e107::getRender()->tablerender(EPL_ADLAN_40, 'Unknown file: '.$fileList[0]['stored_filename']);
					}

					/* attempt to delete uploaded archive */
					@unlink(e_PLUGIN.$archiveName);
				}
			}
   }


// -----------------------------------------------------------------------------

   function pluginInstall()
   {
        global $plugin,$admin_log,$eplug_folder;
			$text = $plugin->install_plugin($this->id);
			if ($text === FALSE)
			{ // Tidy this up
				$this->show_message("Error messages above this line", E_MESSAGE_ERROR);
			}
			else
			{
				 $plugin ->save_addon_prefs();
				$admin_log->log_event('PLUGMAN_01', $this->id.':'.$eplug_folder, E_LOG_INFORMATIVE, '');
				$this->show_message($text, E_MESSAGE_SUCCESS);
			}

   }


// -----------------------------------------------------------------------------

	function pluginUpgrade()
	{
		$pref = e107::getPref();
		$admin_log = e107::getAdminLog();
		$plugin = e107::getPlugin();

	  	$sql = e107::getDb();

   		$emessage = eMessage::getInstance();

		$plug = $plugin->getinfo($this->id);

		$_path = e_PLUGIN.$plug['plugin_path'].'/';
		if(file_exists($_path.'plugin.xml'))
		{
			$plugin->install_plugin_xml($this->id, 'upgrade');
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
				if (true !== $result)
				{
					//$text .= EPL_ADLAN_9.'<br />';
					$emessage->addWarning(EPL_ADLAN_9)
						->addDebug($result);
				}
				else
				{
					$text .= EPL_ADLAN_7."<br />";
				}
			}

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
			
			e107::getConfig('core')->setPref($pref);
			$plugin->rebuildUrlConfig();
			e107::getConfig('core')->save();
		}


		$emessage->add($text, E_MESSAGE_SUCCESS);
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
				$text .= $plugin->install_plugin_xml($this->id, 'refresh');
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
         global $plugin;
		 $frm = e107::getForm();

		//TODO 'install' checkbox in plugin upload form. (as it is for theme upload)

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
                <table class='table adminform'>
                	<colgroup>
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

		global $plugin;
		$frm = e107::getForm();
		$e107 = e107::getInstance();

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
					<table class='table adminlist'>
						".$frm->colGroup($this->fields,$this->fieldpref).
						$frm->thead($this->fields,$this->fieldpref)."
						<tbody>
		";

		if($pluginRenderPlugin)
		{
			$text .= $pluginRenderPlugin;
		}
		else
		{
			//TODO LANs
			$text .= "<tr><td class='center' colspan='".count($this->fields)."'>No plugins installed - <a href='".e_ADMIN."plugin.php?avail'>click here to install some</a>.</td></tr>";
		}

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
			global $plugin; 
			
			if (empty($pluginList)) return '';

			$tp = e107::getParser();
			$frm = e107::getForm();

			$text = "";

			foreach($pluginList as $plug)
			{
				e107::loadLanFiles($plug['plugin_path'],'admin');

				$_path = e_PLUGIN.$plug['plugin_path'].'/';

				$plug_vars = false;
				$plugin_config_icon = "";

				if($plugin->parse_plugin($plug['plugin_path']))
				{
					$plug_vars = $plugin->plug_vars;
				}

				if(varset($plug['plugin_category']) == "menu") // Hide "Menu Only" plugins.
				{
					continue;
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
					$plugDate	= varset($plug_vars['@attributes']['date'],'');
					
					$description = varset($plug_vars['description']['@attributes']['lang']) ? $tp->toHTML($plug_vars['description']['@attributes']['lang'], false, "defs,emotes_off, no_make_clickable") : $tp->toHTML($plug_vars['description']['@value'], false, "emotes_off, no_make_clickable") ;
					
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
					$text .= (in_array("plugin_date",$this->fieldpref)) ? "<td class='middle'>".$plugDate."</td>" : "";
					
					$text .= (in_array("plugin_folder",$this->fieldpref)) ? "<td class='middle'>".$plug['plugin_path']."</td>" : "";
					$text .= (in_array("plugin_category",$this->fieldpref)) ? "<td class='middle'>".$plug['plugin_category']."</td>" : "";
                    $text .= (in_array("plugin_author",$this->fieldpref)) ? "<td class='middle'><a href='mailto:".$plugEmail."' title='".$plugEmail."'>".$plugAuthor."</a>&nbsp;</td>" : "";
                    $text .= (in_array("plugin_website",$this->fieldpref)) ? "<td class='center middle'>".($plugURL ? "<a href='{$plugURL}' title='{$plugURL}' >".ADMIN_URL_ICON."</a>" : "")."</td>" : "";
                    $text .= (in_array("plugin_notes",$this->fieldpref)) ? "<td class='center middle'>".($plugReadme ? "<a href='".e_PLUGIN.$plug['plugin_path']."/".$plugReadme."' title='".$plugReadme."'>".ADMIN_INFO_ICON."</a>" : "&nbsp;")."</td>" : "";
					$text .= (in_array("plugin_description",$this->fieldpref)) ? "<td class='middle'>".$description."</td>" : "";
                    $text .= (in_array("plugin_compatible",$this->fieldpref)) ? "<td class='center middle'>".varset($plug_vars['@attributes']['compatibility'],'')."</td>" : "";
					$text .= (in_array("plugin_compliant",$this->fieldpref)) ? "<td class='center middle'>".((varset($plug_vars['compliant']) || varsettrue($plug_vars['@attributes']['xhtmlcompliant'])) ? ADMIN_TRUE_ICON : "&nbsp;")."</td>" : "";


                	// Plugin options Column --------------

   					$text .= "<td class='center middle'>".$plugin_config_icon;


						if ($plug_vars['@attributes']['installRequired'])
						{
							if ($plug['plugin_installflag'])
							{
						  		$text .= ($plug['plugin_installflag'] ? "<a href=\"".e_SELF."?uninstall.{$plug['plugin_id']}\" title='".EPL_ADLAN_1."'  >".ADMIN_UNINSTALLPLUGIN_ICON."</a>" : "<a href=\"".e_SELF."?install.{$plug['plugin_id']}\" title='".EPL_ADLAN_0."' >".ADMIN_INSTALLPLUGIN_ICON."</a>");

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
									e107::getDb()->db_Delete('plugin', "plugin_installflag=0 AND (plugin_path='{$plug['plugin_path']}' OR plugin_path='{$plug['plugin_path']}/' )  ");
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
			global $plugin;

			$frm 	= e107::getForm();
			$tp 	= e107::getParser();
			$mes 	= e107::getMessage();

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
			if (isset($plug_vars['userClasses']))
			{
				if (isset($plug_vars['userclass']['@attributes']))
				{
					$plug_vars['userclass'][0]['@attributes'] = $plug_vars['userclass']['@attributes'];
					unset($plug_vars['userclass']['@attributes']);
				}
				$spacer = '';
				foreach ($plug_vars['userClasses']['class'] as $uc)
				{
					$userclasses .= $spacer.$uc['@attributes']['name'].' - '.$uc['@attributes']['description'];
					$spacer = '<br />';
				}
			}
			if (isset($plug_vars['extendedFields']))
			{
				if (isset($plug_vars['extendedFields']['@attributes']))
				{
					$plug_vars['extendedField'][0]['@attributes'] = $plug_vars['extendedField']['@attributes'];
					unset($plug_vars['extendedField']['@attributes']);
				}
				$spacer = '';
				foreach ($plug_vars['extendedFields']['field'] as $eu)
				{
					$eufields .= $spacer.'plugin_'.$plug_vars['folder'].'_'.$eu['@attributes']['name'];
					$spacer = '<br />';
				}
			}

			if(is_writable(e_PLUGIN.$plug['plugin_path']))
			{
				$del_text = $frm->selectbox('delete_files','yesno',0);
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
            <table class='table adminform'>
            	<colgroup>
            		<col class='col-label' />
            		<col class='col-control' />
            	</colgroup>
 			<tr>
				<td>".EPL_ADLAN_55."</td>
				<td>".LAN_YES."</td>
			</tr>";

			$opts = array();

			$opts['delete_tables'] = array(
					'label'			=> EPL_ADLAN_57,
					'helpText'		=> EPL_ADLAN_58,
					'itemList'		=> array(1=>LAN_YES,0=>LAN_NO),
					'itemDefault' 	=> 1
			);

			if ($userclasses)
			{
				$opts['delete_userclasses'] = array(
					'label'			=> EPL_ADLAN_78,
					'preview'		=> $userclasses,
					'helpText'		=> EPL_ADLAN_79,
					'itemList'		=> array(1=>LAN_YES,0=>LAN_NO),
					'itemDefault' 	=> 1
				);
			}

			if ($eufields)
			{
				$opts['delete_xfields'] = array(
					'label'			=> EPL_ADLAN_80,
					'preview'		=> $eufields,
					'helpText'		=> EPL_ADLAN_79,
					'itemList'		=> array(1=>LAN_YES,0=>LAN_NO),
					'itemDefault' 	=> 0
				);
			}

			$med = e107::getMedia();
			$icons = $med->listIcons(e_PLUGIN.$plug['plugin_path']);

			if(count($icons)>0)
			{
				foreach($icons as $key=>$val)
				{
					$iconText .= "<img src='".$tp->replaceConstants($val)."' alt='' />";
				}

				$opts['delete_ipool'] = array(
					'label'			=>'Remove icons from Media-Manager',
					'preview'		=> $iconText,
					'helpText'		=> EPL_ADLAN_79,
					'itemList'		=> array(1=>LAN_YES,0=>LAN_NO),
					'itemDefault' 	=> 1
				);
			}

			if(is_readable(e_PLUGIN.$plug['plugin_path']."/".$plug['plugin_path']."_setup.php"))
			{
				include_once(e_PLUGIN.$plug['plugin_path']."/".$plug['plugin_path']."_setup.php");


				$mes->add("Loading ".e_PLUGIN.$plug['plugin_path']."/".$plug['plugin_path']."_setup.php", E_MESSAGE_DEBUG);

				$class_name = $plug['plugin_path']."_setup";

				if(class_exists($class_name))
				{
					$obj = new $class_name;
					if(method_exists($obj,'uninstall_options'))
					{
						$arr = call_user_func(array($obj,'uninstall_options'), $this);
						foreach($arr as $key=>$val)
						{
							$newkey = $plug['plugin_path']."_".$key;
							$opts[$newkey] = $val;
						}
					}
				}
			}

			foreach($opts as $key=>$val)
			{
				$text .= "<tr>\n<td class='top'>".$tp->toHTML($val['label'],FALSE,'TITLE');
				$text .= varset($val['preview']) ? "<div class='indent'>".$val['preview']."</div>" : "";
				$text .= "</td>\n<td>".$frm->selectbox($key,$val['itemList'],$val['itemDefault']);
				$text .= varset($val['helpText']) ? "<div class='field-help'>".$val['helpText']."</div>" : "";
				$text .= "</td>\n</tr>\n";
			}


			$text .="<tr>
			<td>".EPL_ADLAN_59."</td>
			<td>{$del_text}
			<div class='field-help'>".EPL_ADLAN_60."</div>
			</td>
			</tr>
			</table>
			<div class='buttons-bar center'>";
			
			$text .= $frm->admin_button('uninstall_confirm',EPL_ADLAN_3,'submit');
			$text .= $frm->admin_button('uninstall_cancel',EPL_ADLAN_62,'cancel');

			/*
			$text .= "<input class='button' type='submit' name='uninstall_confirm' value=\"".EPL_ADLAN_3."\" />&nbsp;&nbsp;
			<input class='button' type='submit' name='uninstall_cancel' value='".EPL_ADLAN_62."' onclick=\"location.href='".e_SELF."'; return false;\"/>";
			*/
             //   $frm->admin_button($name, $value, $action = 'submit', $label = '', $options = array());

			$text .= "</div>
			</fieldset>
			</form>
			";
			e107::getRender()->tablerender(EPL_ADLAN_63." ".$tp->toHtml($plug_vars['@attributes']['name'], "", "defs,emotes_off, no_make_clickable"),$mes->render(). $text);

		}

        function show_message($message, $type = E_MESSAGE_INFO, $session = false)
		{
		// ##### Display comfort ---------
			$emessage = &eMessage::getInstance();
			$emessage->add($message, $type, $session);
		}

        function pluginMenuOptions()
		{
		   //	$e107 = &e107::getInstance();

				$var['installed']['text'] = EPL_ADLAN_22;
				$var['installed']['link'] = e_SELF;

				$var['avail']['text'] = EPL_ADLAN_23;
				$var['avail']['link'] = e_SELF."?avail";

			//	$var['upload']['text'] = EPL_ADLAN_38;
			//	$var['upload']['link'] = e_SELF."?upload";
				
				$var['online']['text'] = "Search";
				$var['online']['link'] = e_SELF."?mode=online";
				
				$var['create']['text'] = "Plugin Builder";
				$var['create']['link'] = e_SELF."?mode=create";

				$keys = array_keys($var);

				$action = (in_array($this->action,$keys)) ? $this->action : "installed";

				e107::getNav()->admin(ADLAN_98, $action, $var);
		}



		

} // end of Class.



function plugin_adminmenu()
{
	global $pman;
	$pman -> pluginMenuOptions();
}


/**
 * Plugin Admin Generator by CaMer0n. //TODO Incorporate plugin.xml generation
 */
class pluginBuilder
{
	
		var $fields = array();
		var $table = '';
		var $pluginName = '';
		var $special = array();
	
		function __construct()
		{
			$this->special['checkboxes'] =  array('title'=> '','type' => null, 'data' => null,	 'width'=>'5%', 'thclass' =>'center', 'forced'=> TRUE,  'class'=>'center', 'toggle' => 'e-multiselect', 'fieldpref'=>true);
			$this->special['options'] = array( 'title'=> LAN_OPTIONS, 'type' => null, 'data' => null, 'width' => '10%',	'thclass' => 'center last', 'class' => 'center last', 'forced'=>TRUE, 'fieldpref'=>true);		
			
			if($_GET['newplugin'])
			{
				$this->pluginName = $_GET['newplugin'];
			}
				
			
			if(vartrue($_POST['step']) == 3)
			{
		
				$this->step3();	
				
				
				return;
			}
			
			if(vartrue($_GET['newplugin']) && $_GET['step']==2)
			{
				return $this->step2();	
			}
			
		
		
			return $this->step1();
		}



		function step1()
		{
			
			$fl = e107::getFile();
			$frm = e107::getForm();
			$ns = e107::getRender();
			$mes = e107::getMessage();
			
			$plugFolders = $fl->get_dirs(e_PLUGIN);	
			foreach($plugFolders as $dir)
			{
				if(file_exists(e_PLUGIN.$dir."/admin_config.php") || !file_exists(e_PLUGIN.$dir."/".$dir."_sql.php"))
				{
					continue;	
				}	
				$newDir[$dir] = $dir;
			}
			
			$mes->addInfo("This Wizard will build an admin area for your plugin and generate a plugin.xml meta file.
				Before you start: <ul>
						<li>Create a new writable folder in the ".e_PLUGIN." directory eg. <b>myplugin</b></li>
						<li>Create a new file in this folder and name it the same as the directory but with <b>_sql.php</b> as a sufix eg. <b>myplugin_sql.php</b></li>
						<li>Create your table in Phpmyadmin and paste an sql dump of it into your file and save. (see <il>e107_plugins/_blank/_blank_sql.php</i> for an example)</li>
						<li>Select your plugin's folder to begin.</li>
				</ul>
			");
			
			$text = $frm->open('createPlugin','get');
			$text .= "<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
				<tr>
					<td>Select your plugin's folder</td>
					<td>".$frm->selectbox("newplugin",$newDir)."</td>
				</tr>
				";
			
			/* NOT a good idea - requires the use of $_POST which would prevent browser 'go Back' navigation. 
			if(e_DOMAIN == FALSE) // localhost. 
			{
				$text .= "<tr>
					<td>Pasted MySql Dump Here</td>
					<td>".$frm->textarea('mysql','', 10,80)."
					<span class='field-help'>eg. </span></td>
					</tr>";			
			}
			*/
					
				
			$text .= "				
				</table>
				<div class='buttons-bar center'>
				".$frm->admin_button('step', 2,'other','Go')."
				</div>";
		
			
			
			
			
			
			
			
			
			$text .= $frm->close();
			
			$ns->tablerender("Plugin Builder", $mes->render() . $text);			
			
		}


		function enterMysql()
		{
			
			$frm = e107::getForm();
			return "<div>".$frm->textarea('mysql','', 10,80)."</div>";	
			
		}




		function step2()
		{
			
			require_once(e_HANDLER."db_verify_class.php");
			$dv = new db_verify;
			
			$frm = e107::getForm();
			$ns = e107::getRender();
			$mes = e107::getMessage();
			
			$newplug = $_GET['newplugin'];
			$this->pluginName = $newplug;
			
		
			
		//	$data = e107::getXml()->loadXMLfile(e_PLUGIN.'links_page/plugin.xml', 'advanced');
		//	print_a($data);
		//	echo "<pre>".var_export($data,true)."</pre>";
			
			
			
			$data = file_get_contents(e_PLUGIN.$newplug."/".$newplug."_sql.php");
			$ret =  $dv->getTables($data);
		
		
			$text = $frm->open('newplugin-step3','post', e_SELF.'?mode=create&newplugin='.$newplug.'&step=3');
			$text .= "<div class='admintabs' id='tab-container'>\n";
			$text .= "<ul class='e-tabs' id='core-emote-tabs'>\n";
			
			
			foreach($ret['tables'] as $key=>$table)
			{
				$text .= "<li id='tab-".$table."'><a href='#".$table."'>Table: ".$table."</a></li>";
			}
			$text .= "<li id='tab-preferences'><a href='#preferences'>Preferences</a></li>";
			$text .= "<li id='tab-xml'><a href='#xml'>Meta Info.</a></li>";
			$text .= "</ul>";
				
			foreach($ret['tables'] as $key=>$table)
			{
				$text .= "<fieldset id='".$table."'>\n";
				$fields = $dv->getFields($ret['data'][$key]);
				
			
				$text .= $this->form($table,$fields);
				$text .= "</fieldset>";	
			}
			
			$text .= "<fieldset id='preferences'>\n";
			$text .= $this->prefs(); 
			$text .= "</fieldset>";
			
			
			$text .= "<fieldset id='xml'>\n";
			$text .= $this->pluginXml(); 
			$text .= "</fieldset>";
			
			$text .= "</div>";
			
			$text .= "
			<div class='buttons-bar center'>
			".$frm->hidden('newplugin', $this->pluginName)."
			".$frm->admin_button('step', 3,'other','Generate')."
			</div>";
			
			$text .= $frm->close();
			
			$mes->addInfo("Review all fields and modify if necessary.");
			
			if(count($ret['tables']) > 1)
			{
				$mes->addInfo("Review ALL tables before clicking 'Generate'.");	
			}
			
			$ns->tablerender("Plugin Builder", $mes->render() . $text);		
		}


		function prefs()
		{
			//TODO Preferences 
			return "Coming Soon";				
		}


		function pluginXml()
		{
			
			
			//TODO Plugin.xml Form Fields. . 
			
			$data = array(
				'main' 			=> array('name','lang','version','date', 'compatibility','installRequired'),
				'author' 		=> array('name','url'),
				'description' 	=> array('description'),
				'category'		=> array('category'),
				'copyright'		=> array('copyright'),
		//		'languageFile'	=> array('type','path'),
		//		'adminLinks'	=> array('url','description','icon','iconSmall','primary'),
		//		'sitelinks'		=> array('url','description','icon','iconSmall')
			);
			
			$text = "<table class='table adminlist'>";
					
			foreach($data as $key=>$val)
			{
				$text.= "<tr><td>$key</td><td>
				<div class='controls'>";
				foreach($val as $type)
				{
					$nm = $key.'-'.$type;
					$name = "xml[$nm]";	
					$size = (count($val)==1) ? 'span7' : 'span2';
					$text .= "<div class='{$size}'>".$this->xmlInput($name, $key."-". $type)."</div>";	
				}	
			
				$text .= "</div></td></tr>";
				
				
			}
			$text .= "</table>";
			
			return $text;				
		}
		
		
		function xmlInput($name, $info)
		{
			$frm = e107::getForm();	
			list($cat,$type) = explode("-",$info);
			
			$size 		= 30;
			$default 	= '';
			$help		= '';
			
			switch ($info)
			{
				
				case 'main-name':
					$help 		= "The name of your plugin. (Must be written in English)";
					$required 	= true;
				break;
		
				case 'main-lang':
					$help 		= "If you have a language file, enter the LAN_XXX value for the plugin's name";
					$required 	= false;
				break;
				
				case 'main-date':
					$help 		= "Creation date of your plugin";
					$required 	= true;
				break;
				
				case 'main-version':
					$default 	= '1.0';
					$required 	= true;
					$help 		= "The version of your plugin";
				break;

				case 'main-compatibility':
					$default 	= '2.0';
					$required 	= true;
					$help 		= "Compatible with this version of e107";
				break;
				
				case 'author-name':
					$default 	= USERNAME;
					$required 	= true;
					$help 		= "Author Name";
				break;
				
				case 'author-url':
					$default 	= '';
					$required 	= true;
					$help 		= "Author Website Url";
				break;
				
				case 'main-installRequired':
					return "Installation required: ".$frm->radio_switch($name,'',LAN_YES, LAN_NO);
				break;		
				
				case 'description-description':
					$help 		= "A short description of the plugin<br />(Must be written in English)";
					$required 	= true;
					$size 		= 100;
				break;
				
					
				case 'category-category':
					$help 		= "What category of plugin is this?";
					$required 	= true;
					$size 		= 20;
				break;
						
				default:
					
				break;
			}

			$req = ($required == true) ? "&required=1" : "";	
			
			if($type == 'date')
			{
				$text = $frm->datepicker($name,time(),'dateformat=yy-mm-dd'.$req);	
			}
			elseif($type == 'category')
			{
				$options = array(
					'settings'	=> 'settings',
					'users'		=> 'users', 
					'content'	=> 'content',
					'tools'		=> 'tools',
					'manage'	=> 'manage',
					'misc'		=> 'misc',
					'menu'		=> 'menu',
					'about'		=> 'about'
				);
				
				$text = $frm->selectbox($name, $options,'','required=1', true);	
			}
			else 
			{
				$text = $frm->text($name, $default, $size, 'placeholder='.$type.$req);	
			}
			
			$text .= ($help) ? "<span class='field-help'>".$help."</span>" : "";
			return $text;
			
		}

		function processXml($data)
		{
			
			$ns = e107::getRender();
			$mes = e107::getMessage();
			
			foreach($data as $key=>$val)
			{
				$key = strtoupper(str_replace("-","_",$key));
				$newArray[$key] = $val;	
				
			}
			
			//	print_a($newArray);
			// print_a($this);
			
$template = <<<TEMPLATE
<?xml version="1.0" encoding="utf-8"?>
<e107Plugin name="{MAIN_NAME}" version="{MAIN_VERSION}" date="{MAIN_DATE}" compatibility="{MAIN_COMPATIBILITY}" installRequired="{MAIN_INSTALLREQUIRED}" >
	<author name="{AUTHOR_NAME}" url="{AUTHOR_URL}" />
	<description lang="">{DESCRIPTION_DESCRIPTION}</description>
	<category>{CATEGORY_CATEGORY}</category>
	<copyright>{COPYRIGHT_COPYRIGHT}</copyright>
	<adminLinks>
		<link url="admin_config.php" description="{ADMINLINKS_DESCRIPTION}" icon="images/icon_32.png" iconSmall="images/icon_16.png" primary="true" >LAN_CONFIGURE</link>
	</adminLinks>
</e107Plugin>
TEMPLATE;

/*
	<siteLinks>
		<link url="{e_PLUGIN}_blank/_blank.php" perm="everyone">Blank</link>		
	</siteLinks>
	<pluginPrefs>
		<pref name="blank_pref_1">1</pref>
		<pref name="blank_pref_2">[more...]</pref>
	</pluginPrefs>
	<userClasses>
		<class name="blank_userclass" description="Blank Userclass Description" />		
	</userClasses>
	<extendedFields>
		<field name="custom" type="EUF_TEXTAREA" default="0" active="true" />
	</extendedFields>	
*/


			$result = e107::getParser()->simpleParse($template, $newArray);
			$path = e_PLUGIN.$this->pluginName."/plugin.xml";
			
			if(file_put_contents($path,$result))
			{
				$mes->addSuccess("Saved: ".$path);
			}
			else {
				$mes->addError("Couldn't Save: ".$path);
			}
			
			return  htmlentities($result);
			
		//	$ns->tablerender(LAN_CREATED.": plugin.xml", "<pre  style='font-size:80%'>".htmlentities($result)."</pre>");	
		}
						
					
				
			


		function form($table,$fieldArray)
		{
			$frm = e107::getForm();
					
			$modes = array("main"=>"Main Area","cat"=>"Categories");
			
			$this->table = $table."_ui";
			
			$text .= "<table class='table adminform'>\n";
				
			$text .= "
					<tr>
						<td>Plugin Title</td>
						<td>".$frm->text($this->table.'[pluginTitle]', $newplug, '', 'required=1').
						
						$frm->hidden($this->table.'[pluginName]', $this->pluginName, 15).
						$frm->hidden($this->table.'[table]', $table, 15).
						"</td>
						
					</tr>
					<tr>
						<td>Mode</td>
						<td>".$frm->selectbox($this->table."[mode]",$modes, '', 'required=1', true)."</td>
					</tr>
					
				";
				
			$text .= "</table>".$this->special('checkboxes');
			
			$text .= "<table class='table adminlist'>
						<thead>
						<tr>
							<th>Field</th>
							<th>Caption</th>
							<th>Type</th>
							<th>Data</th>
							<th>Width</th>
							<th class='center'>Batch</th>
							<th class='center'>Filter</th>
							<th class='center e-tip' title='Field is required to be filled'>Validate</th>
							<th class='center e-tip' title='Displayed by Default'>Display</th>
							<th>HelpTip</th>
							<th>ReadParms</th>
							<th>WriteParms</th>
						</tr>
						</thead>
						<tbody>
						";
						
			foreach($fieldArray as $name=>$val)
			{
				list($tmp,$nameDef) = explode("_",$name,2);
				// 'faq_question', 'faq_answer', 'faq_parent', 'faq_datestamp'
				$text .= "<tr>
					<td>".$name."</td>
					<td>".$frm->text($this->table."[fields][".$name."][title]", $this->guess($name, $val,'title'),35, 'required=1')."</td>
					<td>".$this->fieldType($name, $val)."</td>
					<td>".$this->fieldData($name, $val)."</td>
					<td>".$frm->text($this->table."[fields][".$name."][width]",'auto',4)."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][batch]", true, $this->guess($name, $val,'batch'))."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][filter]", true, $this->guess($name, $val,'filter'))."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][validate]", true)."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][fieldpref]", true, $this->guess($name, $val,'fieldpref'))."</td>
					<td>".$frm->text($this->table."[fields][".$name."][help]",'', 50)."</td>
					<td>".$frm->text($this->table."[fields][".$name."][readParms]",'', 35)."</td>
					<td>".$frm->text($this->table."[fields][".$name."][writeParms]",'', 35)."</td>
					</tr>";
			
			}
			//'width' => '20%',	'thclass' => 'center',	'batch' => TRUE, 'filter'=>TRUE, 'parms' => 'truncate=30', 'validate' => false, 'help' => 'Enter blank URL here', 'error' => 'please, ener valid URL'),		
			$text .= "</tbody></table>".$this->special('options');	
			
			
			return $text;
			
		}
		
		// Checkboxes and Options. 
		function special($name)
		{
			$frm = e107::getForm();
			$text = "";
			
			foreach($this->special[$name] as $key=>$val)
			{
				$text .= $frm->hidden($this->table."[fields][".$name."][".$key."]", $val);					
			}

			return $text;
			
		}
					
				
			
		
		function fieldType($name, $val)
		{
			$type = strtolower($val['type']);
			$frm = e107::getForm();
			
			if(strtolower($val['default']) == "auto_increment")
			{
				$key = $this->table."[pid]";
				return "Primary Id".$frm->hidden($key, $name );	// 
			}
			
			switch ($type) 
			{
			
				case 'int':
				case 'tinyint':
				case 'smallint':
					$array = array(
					"boolean"	=> "True/Flase",
					"number"	=> "Text Box",
					"dropdown"	=> "DropDown",
					"userclass"	=> "DropDown (userclasses)",
					"datestamp"	=> "Date",
					"method"	=> "Custom Function",
					"hidden"	=> "Hidden"
					);	
				break;
				
				case 'varchar':
				$array = array(
					'text'		=> "Text Box",
					"dropdown"	=> "DropDown",
					"userclass"	=> "DropDown (userclasses)",
					"url"		=> "Text Box (url)",
					"icon"		=> "Icon",
					"image"		=> "Image",
					"method"	=> "Custom Function",
					"hidden"	=> "Hidden"
					);
				break;
				
				case 'text':
				$array = array(
					'textarea'	=> "Text Area",
					'bbarea'	=> "Rich-Text Area",
					'text'		=> "Text Box",
					"method"	=> "Custom Function",
					"image"		=> "Image",
					"hidden"	=> "Hidden"
					);
				break;
			}
			
		//	asort($array);
			
			$fname = $this->table."[fields][".$name."][type]";
			return $frm->selectbox($fname, $array, $this->guess($name, $val),'required=1', true);
			
		}

		// Guess Default Field Type based on name of field. 
		function guess($data, $val='',$mode = 'type')
		{
			$tmp = explode("_",$data);	
			
			if(count($tmp) == 3) // eg Link_page_title
			{
				$name = $tmp[2];	
			}
			else // Link_description
			{
				$name = $tmp[1];		
			}
	
			$ret['title'] = ucfirst($name);
			//echo "<br />name=".$name; 
			switch ($name) 
			{
				
				case 'id':
					$ret['title'] = 'LAN_ID';
					// $ret['type'] = 'datestamp';
					$ret['batch'] = false;
					$ret['filter'] = false;
				break;
				
				case 'start':
				case 'end':
				case 'datestamp':
					$ret['title'] = 'LAN_DATESTAMP';
					$ret['type'] = 'datestamp';
					$ret['batch'] = true;
					$ret['filter'] = true;
					$ret['fieldpref'] = true;
				break;
				
				case 'name':
				case 'title':
				case 'subject':
				case 'summary':
					$ret['title'] = 'LAN_TITLE';
					$ret['type'] = 'text';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['fieldpref'] = true;
				break;
				
				case 'author':
					$ret['title'] = 'LAN_AUTHOR';
					$ret['type'] = 'user';
					$ret['batch'] = false;
					$ret['filter'] = false;
				break;
				
				case 'thumb':
				case 'thumbnail':
				case 'image':
					$ret['title'] = 'LAN_IMAGE';
					$ret['type'] = 'image';
					$ret['batch'] = false;
					$ret['filter'] = false;
				break;

				case 'total':
				case 'order':
					$ret['title'] = 'LAN_ORDER';
					$ret['type'] = 'number';
					$ret['batch'] = false;
					$ret['filter'] = false;
				break;

				case 'category':
					$ret['title'] = 'LAN_CATEGORY';
					$ret['type'] = 'dropdown';
					$ret['batch'] = true;
					$ret['filter'] = true;
					$ret['fieldpref'] = true;
				break;
				
				case 'type':
					$ret['title'] = 'LAN_TYPE';
					$ret['type'] = 'dropdown';
					$ret['batch'] = true;
					$ret['filter'] = true;
					$ret['fieldpref'] = true;
				break;
								
				case 'icon':
				case 'button':
					$ret['title'] = 'LAN_ICON';
					$ret['type'] = 'icon';
					$ret['batch'] = false;
					$ret['filter'] = false;
				break;
				
				case 'website':
				case 'url':
				case 'homepage':
					$ret['title'] = 'LAN_URL';
					$ret['type'] = 'url';
					$ret['batch'] = false;
					$ret['filter'] = false;
				break;
				
				case 'visibility':
				case 'class':
					$ret['title'] = 'LAN_USERCLASS';
					 $ret['type'] = 'userclass';
					 $ret['batch'] = true;
					 $ret['filter'] = true;
					 $ret['fieldpref'] = true;
				break;
				
				case 'description':
					$ret['title'] = 'LAN_DESCRIPTION';
					 $ret['type'] = ($val['type'] == 'TEXT') ? 'textarea' : 'text';
				break;
				
				default:
					$ret['type'] = 'boolean';
					$ret['batch'] = false;
					$ret['filter'] = false;
					break;
			}
			
			return vartrue($ret[$mode]);
			
		}




		function fieldData($name, $val)
		{
			$frm = e107::getForm();
			$type = $val['type'];
			
			if($type == 'VARCHAR' || $type == 'TEXT')
			{
				$value = 'str';	
			}	
			else 
			{
				$value = 'int';
			}
			
			
			$fname = $this->table."[fields][".$name."][data]";
			
			return $frm->hidden($fname, $value). "<a href='#' class='e-tip' title='{$type}' >".$value."</a>" ;
			
		}




// ******************************** CODE GENERATION AREA *************************************************

		function step3()
		{
			
			if($_POST['xml'])
			{
				$xmlText =	$this->processXml($_POST['xml']);
			}
					
			
			
			
			
			unset($_POST['step'],$_POST['xml']);
	

$text .= "\n
// Generated e107 Plugin Admin Area 

require_once('../../class2.php');
if (!getperms('P')) 
{
	header('location:'.e_BASE.'index.php');
	exit;
}



class ".$_POST['newplugin']."_admin extends e_admin_dispatcher
{

	protected \$modes = array(	
	";
	
	$thePlugin = $_POST['newplugin'];
	unset($_POST['newplugin']);
	
			foreach($_POST as $table => $vars) // LOOP Through Tables. 
			{
	$text .= "
		'".$vars['mode']."'	=> array(
			'controller' 	=> '".$vars['table']."_ui',
			'path' 			=> null,
			'ui' 			=> '".$vars['table']."_form_ui',
			'uipath' 		=> null
		),
";
			} // END LOOP
/*
		'cat'		=> array(
			'controller' 	=> 'faq_cat_ui',
			'path' 			=> null,
			'ui' 			=> 'faq_cat_form_ui',
			'uipath' 		=> null
		)					
	);	
*/

$text .= "
	);	
	
	
	protected \$adminMenu = array(
";
			foreach($_POST as $table => $vars) // LOOP Through Tables. 
			{
$text .= "
		'".$vars['mode']."/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
		'".$vars['mode']."/create'		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),
";
			}
$text .= "			
	/*
		'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => 'P'),
		'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
	*/	

	);

	protected \$adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected \$menuTitle = '".$vars['pluginName']."';
}



";			
			// print_a($_POST);

			
			$srch = array(
				
				"\n",
				"),",
				"    ",
				"'batch' => '1'",
				"'filter' => '1'",
				"'validate' => '1'",
				", 'fieldpref' => '1'",
			 );
			 
			$repl = array(
				
				 "",
				 "),\n\t\t",
				 " ",
				"'batch' => true",
				"'filter' => true",
				"'validate' => true",
				""
				  );
			
	
			
			 
			
			foreach($_POST as $table => $vars) // LOOP Through Tables. 
			{
				
				$FIELDS = str_replace($srch,$repl,var_export($vars['fields'],true));
				$FIELDPREF = array();
				
				foreach($vars['fields'] as $k=>$v)
				{
					if($v['fieldpref'])
					{
						$FIELDPREF[] = "'".$k."'";
					}							
				}
				
$text .= 
"
				
class ".$table." extends e_admin_ui
{
			
		protected \$pluginTitle		= '".$vars['pluginTitle']."';
		protected \$pluginName		= '".$vars['pluginName']."';
		protected \$table			= '".$vars['table']."';
		protected \$pid				= '".$vars['pid']."';
		protected \$perPage 			= 10; 
			
		protected \$fields 		= ".$FIELDS.";		
		
		protected \$fieldpref = array(".implode(", ",$FIELDPREF).");
		
		
		
		/*
		protected $prefs = array(
			'pref_type'	   				=> array('title'=> 'type', 'type'=>'text', 'data' => 'string', 'validate' => true),
			'pref_folder' 				=> array('title'=> 'folder', 'type' => 'boolean', 'data' => 'integer'),
			'pref_name' 				=> array('title'=> 'name', 'type' => 'text', 'data' => 'string', 'validate' => 'regex', 'rule' => '#^[\w]+$#i', 'help' => 'allowed characters are a-zA-Z and underscore')
		);

		
		// optional
		public function init()
		{
			
		}
	
		
		public function customPage()
		{
			\$ns = e107::getRender();
			\$text = 'Hello World!';
			\$ns->tablerender('Hello',\$text);	
			
		}
		*/
			
}
				


class ".$vars['table']."_form_ui extends e_admin_form_ui
{
";

foreach($vars['fields'] as $fld=>$val)
{
	if($val['type'] != 'method')
	{
		continue;	
	}	
	
$text .= "
	
	// Custom Method/Function 
	function ".$fld."(\$curVal,\$mode)
	{
		\$frm = e107::getForm();		
		 		
		switch(\$mode)
		{
			case 'read': // List Page
				return \$curVal;
			break;
			
			case 'write': // Edit Page
				return \$frm->text('".$fld."',\$curVal);		
			break;
			
			case 'filter':
			case 'batch':
				return  \$array; 
			break;
		}
	}
";
}

$text .= "
}		
		
";			
						
	 			
					
			} // End LOOP. 
	
$text .= '		
new '.$vars['pluginName'].'_admin();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;

';

// ******************************** END GENERATION AREA *************************************************	
					
			$ns = e107::getRender();
			$mes = e107::getMessage();
			
			$generatedFile = e_PLUGIN.$thePlugin."/admin_config.php";
			
			$startPHP = chr(60)."?php";		
			$endPHP =  "?>";
			
			if(file_put_contents($generatedFile, $startPHP .$text . $endPHP))
			{
				$mes->addSuccess("<a href='".$generatedFile."'>Click Here</a> to vist your generated admin area");
			}	
			else 
			{
				$mes->addError("Could not write to ".$generatedFile);
			}
			
			echo $mes->render();
			
			$ns->tablerender(LAN_CREATED.": plugin.xml", "<pre style='font-size:80%'>".$xmlText."</pre>");	
	
			
			$ns->tablerender(LAN_CREATED.": admin_config.php", "<pre style='font-size:80%'>".$text."</pre>");
			
		//	
			return;
	
		}
}

?>