<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
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

e107::coreLan('plugin', true);

$e_sub_cat = 'plug_manage';

define('PLUGIN_SHOW_REFRESH', FALSE);
define('PLUGIN_SCAN_INTERVAL', 360);

global $user_pref;

require_once(e_HANDLER.'plugin_class.php');
require_once(e_HANDLER.'file_class.php');
$plugin = new e107plugin;
$pman = new pluginManager;
define("e_PAGETITLE",ADLAN_98." - ".$pman->pagetitle);

if(e_AJAX_REQUEST && isset($_GET['action'])) // Ajax 
{
	if($_GET['action'] == 'download')
	{
		$string =  base64_decode($_GET['src']);	
		parse_str($string, $p);
		
		$mp = $pman->getMarketplace();
		$mp->generateAuthKey($e107SiteUsername, $e107SiteUserpass);
		// Server flush useless. It's ajax ready state 4, we can't flush (sadly) before that (at least not for all browsers) 
		echo "<pre>Connecting...\n"; flush(); // FIXME change the modal default label, default is Loading...
		// download and flush
		$mp->download($p['plugin_id'], $p['plugin_mode'], 'plugin');
		
		echo "</pre>"; flush();
	}
	/*$string =  base64_decode($_GET['src']);	
	parse_str($string,$p);
	$remotefile = $p['plugin_url'];
	
	$localfile = md5($remotefile.time()).".zip";
	$status = "Downloading...";
	
	$fl = e107::getFile();
	$fl->setAuthKey($e107SiteUsername,$e107SiteUserpass);
	$fl->download($remotefile,'plugin');*/
	exit;
}

if(isset($_POST['uninstall_cancel']))
{
	header("location:".e_SELF);
	exit;		
}


class pluginmanager_form extends e_form
{
	
	var $plug;
	var $plug_vars;
		
	//FIXME _ there's a problem with calling this. 
	function plugin_website($parms, $value, $id, $attributes)
	{
		return ($plugURL) ? "<a href='{$plugURL}' title='{$plugURL}' >".ADMIN_URL_ICON."</a>" : "";	
		
	}
	
	
	function options($val, $curVal)
	{
		
		$tp = e107::getParser();
		
		$_path = e_PLUGIN.$this->plug['plugin_path'].'/';
		
		$icon_src = (isset($this->plug_vars['plugin_php']) ? e_PLUGIN : $_path).$this->plug_vars['administration']['icon'];
		$plugin_icon = $this->plug_vars['administration']['icon'] ? "<img src='{$icon_src}' alt='' class='icon S32' />" : $tp->toGlyph('e-cat_plugins-32');
   		$conf_file = "#";
		$conf_title = "";
		
		if ($this->plug_vars['administration']['configFile'] && $this->plug['plugin_installflag'] == true)
		{
			$conf_file = e_PLUGIN. $this->plug['plugin_path'].'/'.$this->plug_vars['administration']['configFile'];
			$conf_title = LAN_CONFIGURE.' '.$tp->toHtml($this->plug_vars['@attributes']['name'], "", "defs,emotes_off, no_make_clickable");
			$plugin_icon = "<a title='{$conf_title}' href='{$conf_file}' >".$plugin_icon."</a>";
			$plugin_config_icon = "<a class='btn' title='{$conf_title}' href='{$conf_file}' >".ADMIN_CONFIGURE_ICON."</a>";
		}
				
		$text = "<div class='btn-group'>";
		
		$text .= vartrue($plugin_config_icon);
		
		if ($this->plug_vars['@attributes']['installRequired'])
		{
			
			if ($this->plug['plugin_installflag'])
			{
		  		$text .= ($this->plug['plugin_installflag'] ? "<a class='btn' href=\"".e_SELF."?uninstall.{$this->plug['plugin_id']}\" title='".EPL_ADLAN_1."'  >".ADMIN_UNINSTALLPLUGIN_ICON."</a>" : "<a class='btn' href=\"".e_SELF."?install.{$this->plug['plugin_id']}\" title='".EPL_ADLAN_0."' >".ADMIN_INSTALLPLUGIN_ICON."</a>");
                           //   $text .= ($this->plug['plugin_installflag'] ? "<button type='button' class='delete' value='no-value' onclick=\"location.href='".e_SELF."?uninstall.{$this->plug['plugin_id']}'\"><span>".EPL_ADLAN_1."</span></button>" : "<button type='button' class='update' value='no-value' onclick=\"location.href='".e_SELF."?install.{$this->plug['plugin_id']}'\"><span>".EPL_ADLAN_0."</span></button>");
				if (PLUGIN_SHOW_REFRESH && !varsettrue($this->plug_vars['plugin_php']))
				{
					$text .= "<br /><br /><input type='button' class='btn button' onclick=\"location.href='".e_SELF."?refresh.{$this->plug['plugin_id']}'\" title='".'Refresh plugin settings'."' value='".'Refresh plugin settings'."' /> ";
				}
			}
			else
			{
			  //	$text .=  "<input type='button' class='btn' onclick=\"location.href='".e_SELF."?install.{$this->plug['plugin_id']}'\" title='".EPL_ADLAN_0."' value='".EPL_ADLAN_0."' />";
			  //	$text .= "<button type='button' class='update' value='no-value' onclick=\"location.href='".e_SELF."?install.{$this->plug['plugin_id']}'\"><span>".EPL_ADLAN_0."</span></button>";
	           	$text .= "<a class='btn' href=\"".e_SELF."?install.{$this->plug['plugin_id']}\" title='".EPL_ADLAN_0."' >".ADMIN_INSTALLPLUGIN_ICON."</a>";
			}
			
		}
		else
		{
			if ($this->plug_vars['menuName'])
			{
				$text .= EPL_NOINSTALL.str_replace("..", "", e_PLUGIN.$this->plug['plugin_path'])."/ ".EPL_DIRECTORY;
			}
			else
			{
				$text .= EPL_NOINSTALL_1.str_replace("..", "", e_PLUGIN.$this->plug['plugin_path'])."/ ".EPL_DIRECTORY;
				if($this->plug['plugin_installflag'] == false)
				{					
					e107::getDb()->db_Delete('plugin', "plugin_installflag=0 AND (plugin_path='{$this->plug['plugin_path']}' OR plugin_path='{$this->plug['plugin_path']}/' )  ");
				}
			}
		}

		if ($this->plug['plugin_version'] != $this->plug_vars['@attributes']['version'] && $this->plug['plugin_installflag'])
		{
		  //	$text .= "<br /><input type='button' class='btn' onclick=\"location.href='".e_SELF."?upgrade.{$this->plug['plugin_id']}'\" title='".EPL_UPGRADE." to v".$this->plug_vars['@attributes']['version']."' value='".EPL_UPGRADE."' />";
			$text .= "<a class='btn' href='".e_SELF."?upgrade.{$this->plug['plugin_id']}' title=\"".EPL_UPGRADE." to v".$this->plug_vars['@attributes']['version']."\" >".ADMIN_UPGRADEPLUGIN_ICON."</a>";
		}

		$text .="</div>	";
				
		return $text;
	}	


	
}


require_once("auth.php");
$pman->pluginObserver();
$mes = e107::getMessage();
$frm = e107::getForm();

function e_help()
{
	return array(
		'caption'	=> "Scan for Changes",
		'text'		=> "Plugin folders are scanned every ".(PLUGIN_SCAN_INTERVAL / 60) ." minutes for changes. Click the button below to scan now.
			<p><a class='btn btn-mini btn-primary' href='".e_SELF."?refresh'>Refresh</a></p>"
	);
}

require_once("footer.php");
exit;


// FIXME switch to admin UI
class pluginManager{

	var $plugArray;
	var $action;
	var $id;
	var $frm;
	var $fieldpref;
	var $titlearray 		= array();
	var $pagetitle;
	
	/**
	 * Marketplace handler instance
	 * @var e_marketplace
	 */
	var $mp;
		
	protected $pid = 'plugin_id';
	
	protected $fields = array(

		   		"checkboxes"			=> array("title" => "", 'type'=>null, "forced"=>TRUE, "width"=>"3%", 'thclass'=>'center','class'=>'center'),
				"plugin_icon"			=> array("title" => EPL_ADLAN_82, "type"=>"icon", "width" => "5%", "thclass" => "middle center",'class'=>'center', "url" => ""),
				"plugin_name"			=> array("title" => EPL_ADLAN_10, 'forced'=>true, "type"=>"text", "width" => "auto", 'class'=>'left', "thclass" => "middle", "url" => ""),
 				"plugin_version"		=> array("title" => EPL_ADLAN_11, "type"=>"numeric", "width" => "5%", "thclass" => "middle", "url" => ""),
    			"plugin_date"			=> array("title" => "Released ", 	"type"=>"text", "width" => "8%", "thclass" => "middle"),
    			
    			"plugin_folder"			=> array("title" => EPL_ADLAN_64, "type"=>"text", "width" => "10%", "thclass" => "middle"),
				"plugin_category"		=> array("title" => LAN_CATEGORY, "type"=>"text", "width" => "auto", "thclass" => "middle"),
                "plugin_author"			=> array("title" => EPL_ADLAN_12, "type"=>"text", "width" => "10%", "thclass" => "middle"),
  				"plugin_price"			=> array("title" => "Price", 	 'nolist'=>true,	"forced"=>true, "type"=>"text", "width" => "5%", "thclass" => "left"),	
  				"plugin_compatible"		=> array("title" => EPL_ADLAN_13, "type"=>"text", "width" => "5%", "thclass" => "middle"),
				"plugin_description"	=> array("title" => EPL_ADLAN_14, "type"=>"bbarea", "width" => "30%", "thclass" => "middle center",  'readParms' => 'expand=1&truncate=180&bb=1'),
				"plugin_compliant"		=> array("title" => EPL_ADLAN_81, "type"=>"text", "width" => "5%", "thclass" => "middle center", "url" => ""),
		//		"plugin_release"		=> array("title" => EPL_ADLAN_81, "type"=>"text", "width" => "5%", "thclass" => "middle center", "url" => ""),
		//		"plugin_notes"			=> array("title" => EPL_ADLAN_83, "type"=>"url", "width" => "5%", "thclass" => "middle center", "url" => ""),
			
				"options"				=> array("title" => LAN_OPTIONS, 'forced'=>TRUE, 'type'=> 'method', "width" => "15%", "thclass" => "right last", 'class'=>'right'),

	);
	
	

	function __construct()
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

		if($this->action == 'online')
		{
			$this->fields["plugin_price"]['nolist'] = false; //  = array("title" => "Price", "forced"=>true, "type"=>"text", "width" => "5%", "thclass" => "middle center");		
		}

        $keys = array_keys($this -> titlearray);
		$this->pagetitle = (in_array($this->action,$keys)) ? $this -> titlearray[$this->action] : $this -> titlearray['installed'];

/*		if(isset($_POST['uninstall-selected']))
		{
        	foreach($_POST['checkboxes'] as $val)
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

		$user_pref['admin_pluginmanager_columns'] = false;
		
		$this -> fieldpref = (vartrue($user_pref['admin_pluginmanager_columns'])) ? $user_pref['admin_pluginmanager_columns'] : array("plugin_icon","plugin_name","plugin_version","plugin_date","plugin_description","plugin_category","plugin_compatible","plugin_author","plugin_website","plugin_notes");


		foreach($this->fields as $key=>$val)
		{
			if($val['forced'] == true && substr($key,0,6)=='plugin')
			{
				$this->fieldpref[] = $key;	
			}		
		}
	

        if($this->action == 'avail' || $this->action == 'installed')   // Plugin Check is done during upgrade_routine.
		{
			$this -> pluginCheck();
		}

		if($this->action == "uninstall")
		{
        	$this -> pluginUninstall();
			$this -> pluginCheck(true); // forced
		}
		
		if($this->action == "refresh")
		{
        	$this -> pluginCheck(true); // forced
		}

        if($this->action == "install" || $this->action == "refresh")
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
		
	//	print_a($_POST);

		if(isset($_POST['install-selected']))
		{
        	foreach($_POST['multiselect'] as $val)
			{
            	$this -> id = intval($val);
                $this -> pluginInstall();
			}
      		$this -> action = "installed";
		}

        if($this->action != 'avail' && varset($this->fields['checkboxes']))
		{
		 	unset($this->fields['checkboxes']); //  = FALSE;
		}

		if($this->action !='upload' && $this->action !='uninstall')
		{
			$this -> pluginRenderList();
		}



	}
	
	
	private function compatibilityLabel($val='')
	{
		$badge = (vartrue($val) > 1.9) ? "<span class='label label-warning'>Made for v2</span>" : '1.x';
		return $badge;	
	}
	
	
	
	function pluginOnline()
	{
		global $plugin, $e107SiteUsername, $e107SiteUserpass;
		$tp = e107::getParser();
		$frm = e107::getForm();
		
		$caption	= "Search Online";
		
		$e107 = e107::getInstance();
		$xml = e107::getXml();
		$mes = e107::getMessage();
		
	//	$mes->addWarning("Some older plugins may produce unpredictable results.");
		// check for cURL
		if(!function_exists(curl_init))
		{
			$mes->addWarning("cURL is currently required to use this feature. Contact your webhosting provider to enable cURL"); // TODO LAN?
		}
		
		//TODO use admin_ui including filter capabilities by sending search queries back to the xml script. 
		$from = isset($_GET['frm']) ? intval($_GET['frm']) : 0;
		$srch = preg_replace('/[^\w]/','', vartrue($_GET['srch'])); 
		
		$mp = $this->getMarketplace();
		// auth
		$mp->generateAuthKey($e107SiteUsername, $e107SiteUserpass);
		
		// do the request, retrieve and parse data
		$xdata = $mp->call('getList', array(
			'type' => 'plugin', 
			'params' => array('limit' => 10, 'search' => $srch, 'from' => $from)
		));
		$total = $xdata['params']['count'];
		
		// OLD BIT OF CODE ------------------------------->
		/*	
	//	$file = SITEURLBASE.e_PLUGIN_ABS."release/release.php";  // temporary testing
		$file = "http://e107.org/feed?type=plugin&frm=".$from."&srch=".$srch."&limit=10";
		
		$xml->setOptArrayTags('plugin'); // make sure 'plugin' tag always returns an array
		$xdata = $xml->loadXMLfile($file,'advanced');

		$total = $xdata['@attributes']['total'];*/
		// OLD BIT OF CODE END ------------------------------->

		 
		$c = 1;
		foreach($xdata['data'] as $row)
		{
			//$row = $r['@attributes'];
			
				$badge 		= $this->compatibilityLabel($row['compatibility']);;
				$featured 	= ($row['featured']== 1) ? " <span class='label label-info'>Featured</span>" : '';
				$price 		= ($row['price'] > 0) ? "<span class='label label-success'>".$row['price']." credits</span>" : "<span class='label label-success'>Free</span>";
			
				$data[] = array(
					'plugin_id'				=> $row['params']['id'],
					'plugin_mode'			=> $row['params']['mode'],
					'plugin_icon'			=> vartrue($row['icon'],'e-plugins-32'),
					'plugin_name'			=> stripslashes($row['name']),
					'plugin_featured'		=> $featured,
					'plugin_folder'			=> $row['folder'],
					'plugin_date'			=> vartrue($row['date']),
					'plugin_category'		=> vartrue($row['category'], 'n/a'),
					'plugin_author'			=> vartrue($row['author']),
					'plugin_version'		=> $row['version'],
					'plugin_description'	=> nl2br(vartrue($row['description'])),
					'plugin_compatible'		=> $badge,
				
					'plugin_website'		=> vartrue($row['authorUrl']),
					//'plugin_url'			=> $row['url'],
					'plugin_notes'			=> '',
					'plugin_price'			=> $price 
				);	
				
			$c++;
		}

		$fieldList = $this->fields;
		unset($fieldList['checkboxes']);

		$text = "
			<form class='form-search' action='".e_SELF."?".e_QUERY."' id='core-plugin-list-form' method='get'>
			<div class='e-search'>".$frm->search('srch', $srch, 'go', $filterName, $filterArray, $filterVal).$frm->hidden('mode','online')."
			</div>
			</form>
			
			<form action='".e_SELF."?".e_QUERY."' id='core-plugin-list-form' method='post'>
				<fieldset class='e-filter' id='core-plugin-list'>
					<legend class='e-hideme'>".$caption."</legend>
					
					
					
					
					
					<table id=core-plugin-list' class='table adminlist'>
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
				if(!in_array($v,$this->fieldpref) || $v == 'checkboxes')
				{
					continue;	
				}
				
				$_value = $val[$v];
				if($v == 'plugin_name') $_value .= $val['plugin_featured'];
				// echo '<br />v='.$v;
				$text .= "<td style='height: 40px' class='".vartrue($this->fields[$v]['class'],'left')."'>".$frm->renderValue($v, $_value, $this->fields[$v], $key)."</td>\n";
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
		
		e107::getRender()->tablerender(ADLAN_98.SEP.$caption, $mes->render(). $text);
	}
	
	
	
	function options($data)
	{
			
	//	print_a($data);
		
		/*
		if(!e107::getFile()->hasAuthKey())
		{
		//	return "<a href='".e_SELF."' class='btn btn-primary e-modal' >Download and Install</a>"; 	
			
		}
		*/
				
		$d = http_build_query($data,false,'&');
		//$url = e_SELF."?src=".base64_encode($d);
		$url = e_SELF.'?action=download&amp;src='.base64_encode($d);//$url.'&amp;action=download';
		$id = 'plug_'.$data['plugin_id'];
		//<button type='button' data-target='{$id}' data-loading='".e_IMAGE."/generic/loading_32.gif' class='btn btn-primary e-ajax middle' value='Download and Install' data-src='".$url."' ><span>Download and Install</span></button>
		$dicon = "<a data-toggle='modal' data-modal-caption=\"Downloading ".$data['plugin_name']." ".$data['plugin_version']."\" href='{$url}' data-cache='false' data-target='#uiModal' title='".$LAN_DOWNLOAD."' ><img class='top' src='".e_IMAGE_ABS."icons/download_32.png' alt=''  /></a> ";
		return "<div id='{$id}' style='vertical-align:middle'>
		{$dicon}
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

					$sql->update('plugin', "plugin_installflag=0, plugin_version='{$eplug_version}' WHERE plugin_id='{$this->id}' ");
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

				$logInfo = deftrue($plug['plugin_name'],$plug['plugin_name']). " v".$plug['plugin_version']." ({e_PLUGIN}".$plug['plugin_path'].")";
				$admin_log->log_event('PLUGMAN_03', $logInfo, E_LOG_INFORMATIVE, '');
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

			$plugin->save_addon_prefs('update');

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
		
		$log = e107::getAdminLog();
			
			
			
			if ($text === FALSE)
			{ // Tidy this up
				$this->show_message("Error messages above this line", E_MESSAGE_ERROR);
			}
			else
			{
				 $plugin->save_addon_prefs('update');
				 $info = $plugin->getinfo($this->id);
				 
				 $name = deftrue($info['plugin_name'],$info['plugin_name']). " v".$info['plugin_version']. "({e_PLUGIN}".$info['plugin_path'].")";
				 
				$log->log_event('PLUGMAN_01', $name, E_LOG_INFORMATIVE, '');
			
				$this->show_message($text, E_MESSAGE_SUCCESS);
			}

   }


// -----------------------------------------------------------------------------

	function pluginUpgrade()
	{
		$pref 		= e107::getPref();
		$admin_log 	= e107::getAdminLog();
		$plugin 	= e107::getPlugin();

	  	$sql 		= e107::getDb();
   		$mes 		= e107::getMessage(); 
		$plug 		= $plugin->getinfo($this->id);

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
					$mes->addWarning(EPL_ADLAN_9)
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

			$info = $plugin->getinfo($this->id);
				 
			$name = deftrue($info['plugin_name'],$info['plugin_name']). " v".$eplug_version. "({e_PLUGIN}".$info['plugin_path'].")";

			$admin_log->log_event('PLUGMAN_02', $name, E_LOG_INFORMATIVE, '');
			$text .= (isset($eplug_upgrade_done)) ? '<br />'.$eplug_upgrade_done : "<br />".LAN_UPGRADE_SUCCESSFUL;
			$sql->update('plugin', "plugin_version ='{$eplug_version}', plugin_addons='{$eplug_addons}' WHERE plugin_id='$this->id' ");
			$pref['plug_installed'][$plug['plugin_path']] = $eplug_version; 			// Update the version
			
			e107::getConfig('core')->setPref($pref);
			$plugin->rebuildUrlConfig();
			e107::getConfig('core')->save();
		}


		$mes->addSuccess($text);
		$plugin->save_addon_prefs('update');

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
    function pluginCheck($force=false)
	{
		global $plugin;
		
		if((time() > vartrue($_SESSION['nextPluginFolderScan'],0)) || $force == true)
		{
			$plugin->update_plugins_table('update');
		}
		
		$_SESSION['nextPluginFolderScan'] = time() + PLUGIN_SCAN_INTERVAL;
		//echo "TIME = ".$_SESSION['nextPluginFolderScan'];
		
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

         e107::getRender()->tablerender(ADLAN_98.SEP.EPL_ADLAN_38, $text);
	}

// -----------------------------------------------------------------------------

	function pluginRenderList() // Uninstall and Install sorting should be fixed once and for all now !
	{

		global $plugin;
		$frm = e107::getForm();
		$e107 = e107::getInstance();
		$mes = e107::getMessage();

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
					<legend class='e-hideme'>".vartrue($caption)."</legend>
					<table class='table adminlist'>
						".$frm->colGroup($this->fields,$this->fieldpref).
						$frm->thead($this->fields,$this->fieldpref)."
						<tbody>
		";

		if(vartrue($pluginRenderPlugin))
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
					<div class='buttons-bar center'>".$frm->admin_button($button_mode, $button_caption, $button_action)."</div>";
		}
		$text .= "
				</fieldset>
			</form>
		";

		e107::getRender()->tablerender(ADLAN_98.SEP.$caption, $mes->render(). $text);
	}


// -----------------------------------------------------------------------------

	function pluginRenderPlugin($pluginList)
	{
			global $plugin; 
			
			if (empty($pluginList)) return '';

			$tp = e107::getParser();
			$frm = e107::getForm();
			
			$pgf = new pluginmanager_form; 

			$text = "";

			foreach($pluginList as $plug)
			{
				e107::loadLanFiles($plug['plugin_path'],'admin');
				
				if($this->action == "avail")
				{
					e107::lan($plug['plugin_path'],'global', true); // Load language files. 
				}
					
				

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
			
                   	$plugin_icon = $plug_vars['administration']['icon'] ? $icon_src : $tp->toGlyph('e-cat_plugins-32');
              
                    
                    $conf_file = "#";
					$conf_title = "";

					if ($plug_vars['administration']['configFile'] && $plug['plugin_installflag'] == true)
					{
						$conf_file = e_PLUGIN.$plug['plugin_path'].'/'.$plug_vars['administration']['configFile'];
						$conf_title = LAN_CONFIGURE.' '.$tp->toHtml($plug_vars['@attributes']['name'], "", "defs,emotes_off, no_make_clickable");
					//	$plugin_icon = "<a title='{$conf_title}' href='{$conf_file}' >".$plugin_icon."</a>";
						$plugin_config_icon = "<a class='btn' title='{$conf_title}' href='{$conf_file}' >".ADMIN_CONFIGURE_ICON."</a>";
					}

					$plugEmail = varset($plug_vars['author']['@attributes']['email'],'');
					$plugAuthor = varset($plug_vars['author']['@attributes']['name'],'');
					$plugURL = varset($plug_vars['author']['@attributes']['url'],'');
					$plugDate	= varset($plug_vars['@attributes']['date'],'');
					$compatibility	= varset($plug_vars['@attributes']['compatibility'],'');
					
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

					if(!file_exists($plugin_icon))
					{
						$plugin_icon = 'e-cat_plugins-32'; // e_IMAGE."admin_images/cat_plugins_32.png";
					}

						
					$data = array(
					'plugin_id'				=> $plug['plugin_id'],
					'plugin_icon'			=> $plugin_icon,
					'plugin_name'			=> $plugName,
					'plugin_folder'			=> $plug['plugin_path'],
					'plugin_date'			=> $plugDate,
					'plugin_category'		=> vartrue($plug['plugin_category']),
					'plugin_author'			=> vartrue($plugAuthor), // vartrue($plugEmail) ? "<a href='mailto:".$plugEmail."' title='".$plugEmail."'>".$plugAuthor."</a>" : vartrue($plugAuthor),
					'plugin_version'		=> $plug['plugin_version'],
					'plugin_description'	=> $description,
					'plugin_compatible'		=> $this->compatibilityLabel($plug_vars['@attributes']['compatibility']),
				
					'plugin_website'		=> vartrue($row['authorUrl']),
			//		'plugin_url'			=> vartrue($plugURL), // ; //  ? "<a href='{$plugURL}' title='{$plugURL}' >".ADMIN_URL_ICON."</a>" : "",
					'plugin_notes'			=> ''
					);	


					$pgf->plug_vars = $plug_vars;
					$pgf->plug		= $plug;
					$text 			.= $pgf->renderTableRow($this->fields, $this->fieldpref, $data, 'plugin_id');


/*

					//LEGACY CODE 




					$text .= "<tr>";

					if(varset($this-> fields['checkboxes']))
					{
                 		$rowid = "checkboxes[".$plug['plugin_id']."]";
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

                   	$text .= (in_array("plugin_compatible",$this->fieldpref)) ? "<td class='center middle'>".$this->compatibilityLabel($plug_vars['@attributes']['compatibility'])."</td>" : "";
					
					$text .= (in_array("plugin_description",$this->fieldpref)) ? "<td class='middle'>".$description."</td>" : "";
                 	$text .= (in_array("plugin_compliant",$this->fieldpref)) ? "<td class='center middle'>".((varset($plug_vars['compliant']) || varsettrue($plug_vars['@attributes']['xhtmlcompliant'])) ? ADMIN_TRUE_ICON : "&nbsp;")."</td>" : "";
	     			$text .= (in_array("plugin_notes",$this->fieldpref)) ? "<td class='center middle'>".($plugReadme ? "<a href='".e_PLUGIN.$plug['plugin_path']."/".$plugReadme."' title='".$plugReadme."'>".ADMIN_INFO_ICON."</a>" : "&nbsp;")."</td>" : "";
			


				


                	// Plugin options Column --------------

   					$text .= "<td class='options center middle'>
   					<div class='btn-group'>".$plugin_config_icon;


						if ($plug_vars['@attributes']['installRequired'])
						{
							if ($plug['plugin_installflag'])
							{
						  		$text .= ($plug['plugin_installflag'] ? "<a class='btn' href=\"".e_SELF."?uninstall.{$plug['plugin_id']}\" title='".EPL_ADLAN_1."'  >".ADMIN_UNINSTALLPLUGIN_ICON."</a>" : "<a class='btn' href=\"".e_SELF."?install.{$plug['plugin_id']}\" title='".EPL_ADLAN_0."' >".ADMIN_INSTALLPLUGIN_ICON."</a>");

                             //   $text .= ($plug['plugin_installflag'] ? "<button type='button' class='delete' value='no-value' onclick=\"location.href='".e_SELF."?uninstall.{$plug['plugin_id']}'\"><span>".EPL_ADLAN_1."</span></button>" : "<button type='button' class='update' value='no-value' onclick=\"location.href='".e_SELF."?install.{$plug['plugin_id']}'\"><span>".EPL_ADLAN_0."</span></button>");
								if (PLUGIN_SHOW_REFRESH && !varsettrue($plug_vars['plugin_php']))
								{
									$text .= "<br /><br /><input type='button' class='btn button' onclick=\"location.href='".e_SELF."?refresh.{$plug['plugin_id']}'\" title='".'Refresh plugin settings'."' value='".'Refresh plugin settings'."' /> ";
								}
							}
							else
							{
							  //	$text .=  "<input type='button' class='btn' onclick=\"location.href='".e_SELF."?install.{$plug['plugin_id']}'\" title='".EPL_ADLAN_0."' value='".EPL_ADLAN_0."' />";
							  //	$text .= "<button type='button' class='update' value='no-value' onclick=\"location.href='".e_SELF."?install.{$plug['plugin_id']}'\"><span>".EPL_ADLAN_0."</span></button>";
                            	$text .= "<a class='btn' href=\"".e_SELF."?install.{$plug['plugin_id']}\" title='".EPL_ADLAN_0."' >".ADMIN_INSTALLPLUGIN_ICON."</a>";
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
						  //	$text .= "<br /><input type='button' class='btn' onclick=\"location.href='".e_SELF."?upgrade.{$plug['plugin_id']}'\" title='".EPL_UPGRADE." to v".$plug_vars['@attributes']['version']."' value='".EPL_UPGRADE."' />";
							$text .= "<a class='btn' href='".e_SELF."?upgrade.{$plug['plugin_id']}' title=\"".EPL_UPGRADE." to v".$plug_vars['@attributes']['version']."\" >".ADMIN_UPGRADEPLUGIN_ICON."</a>";
						}

					$text .="</div></td>";
              //      $text .= "</tr>";




*/








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
				$del_text = $frm->select('delete_files','yesno',0);
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
				$text .= "</td>\n<td>".$frm->select($key,$val['itemList'],$val['itemDefault']);
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
			$text .= "<input class='btn' type='submit' name='uninstall_confirm' value=\"".EPL_ADLAN_3."\" />&nbsp;&nbsp;
			<input class='btn' type='submit' name='uninstall_cancel' value='".EPL_ADLAN_62."' onclick=\"location.href='".e_SELF."'; return false;\"/>";
			*/
             //   $frm->admin_button($name, $value, $action = 'submit', $label = '', $options = array());

			$text .= "</div>
			</fieldset>
			</form>
			";
			e107::getRender()->tablerender(EPL_ADLAN_63.SEP.$tp->toHtml($plug_vars['@attributes']['name'], "", "defs,emotes_off, no_make_clickable"),$mes->render(). $text);

		}

        function show_message($message, $type = E_MESSAGE_INFO, $session = false)
		{
		// ##### Display comfort ---------
			$mes = e107::getMessage();
			$mes->add($message, $type, $session);
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
				
				$var['online']['text'] = "Find Plugins";
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
		var $tableCount = 0;
		var $tableList = array();
		var $createFiles = false; 
	
		function __construct()
		{
			$this->special['checkboxes'] =  array('title'=> '','type' => null, 'data' => null,	 'width'=>'5%', 'thclass' =>'center', 'forced'=> TRUE,  'class'=>'center', 'toggle' => 'e-multiselect', 'fieldpref'=>true);
			$this->special['options'] = array( 'title'=> LAN_OPTIONS, 'type' => null, 'data' => null, 'width' => '10%',	'thclass' => 'center last', 'class' => 'center last', 'forced'=>TRUE, 'fieldpref'=>true);		
			
			if(vartrue($_GET['newplugin']))
			{
				$this->pluginName = $_GET['newplugin'];
			}
			
			if(vartrue($_GET['createFiles']))
			{
				$this->createFiles	= true; 
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
				if(file_exists(e_PLUGIN.$dir."/admin_config.php"))
				{
					continue;	
				}	
				$newDir[$dir] = $dir;
			}
			
			$mes->addInfo("This Wizard will build an admin area for your plugin and generate a plugin.xml meta file.
				Before you start: <ul>
						<li>Create a new writable folder in the ".e_PLUGIN." directory eg. <b>myplugin</b></li>
						<li>If your plugin will use sql tables, create a new file in this folder and name it the same as the directory but with <b>_sql.php</b> as a sufix eg. <b>myplugin_sql.php</b></li>
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
					<td>".$frm->select("newplugin",$newDir)."</td>
				</tr>";
				
				
				$text .= "
				<tr>
					<td>Create Files</td>
					<td>".$frm->checkbox('createFiles',1,1)."</td>
				</tr>";
			
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
			
			$ns->tablerender(ADLAN_98.SEP."Plugin Builder", $mes->render() . $text);			
			
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
			
			$sqlFile = e_PLUGIN.$newplug."/".$newplug."_sql.php";
			
			$ret = array();
			
			if(file_exists($sqlFile))
			{		
				$data = file_get_contents($sqlFile);
				$ret =  $dv->getTables($data);
			}
		
			$text = $frm->open('newplugin-step3','post', e_SELF.'?mode=create&newplugin='.$newplug.'&createFiles='.$this->createFiles.'&step=3');
			
			$text .= "<ul class='nav nav-tabs'>\n";
			$text .= "<li class='active'><a data-toggle='tab' href='#xml'>Basic Info.</a></li>";
			
			$this->tableCount = count($ret['tables']);
			
			foreach($ret['tables'] as $key=>$table)
			{
				$text .= "<li><a data-toggle='tab'  href='#".$table."'>Table: ".$table."</a></li>";
				$this->tableList[] = $table;
			}
			$text .= "<li><a data-toggle='tab'  href='#preferences'>Preferences</a></li>";
			
			$text .= "</ul>";
			
			$text .= "<div class='tab-content'>\n";
			
			$text .= "<div class='tab-pane active' id='xml'>\n";
			$text .= $this->pluginXml(); 
			$text .= "</div>";
				
			foreach($ret['tables'] as $key=>$table)
			{
				$text .= "<div class='tab-pane' id='".$table."'>\n";
				$fields = $dv->getFields($ret['data'][$key]);
				$text .= $this->form($table,$fields);
				$text .= "</div>";	
			}
			
			$text .= "<div class='tab-pane' id='preferences'>\n";
			$text .= $this->prefs(); 
			$text .= "</div>";
			
			
			
			
			$text .= "</div>";
			
			$text .= "
			<div class='buttons-bar center'>
			".$frm->hidden('newplugin', $this->pluginName)."
			".$frm->admin_button('step', 3,'other','Generate')."
			</div>";
			
			$text .= $frm->close();
			
			$mes->addInfo("Review all fields and modify if necessary.");

			$mes->addInfo("Review ALL tabs before clicking 'Generate'.");	
		
			
			$ns->tablerender(ADLAN_98.SEP."Plugin Builder".SEP."Step 2", $mes->render() . $text);		
		}


		function prefs()
		{
			$frm = e107::getForm();

			$text = '';
			
				$options = array(
					'text'		=> "Text Box",
					'bbarea'	=> "Rich-Text Area",
					'boolean'	=> "Text Area",
					"method"	=> "Custom Function",
					"image"		=> "Image",
				);
						
			
			for ($i=0; $i < 10; $i++) 
			{ 		
				$text .= "<div>".
				$frm->text("pluginPrefs[".$i."][index]", '',40,'placeholder=Preference Name')." ".
				$frm->text("pluginPrefs[".$i."][value]", '',40,'placeholder=Default Value')." ".
				$frm->select("pluginPrefs[".$i."][type]", $options, '', 'class=null', 'Field Type...').
				"</div>";		
			}
			
			return $text;
		}


		function pluginXml()
		{
			
			
			//TODO Plugin.xml Form Fields. . 
			
			$data = array(
				'main' 			=> array('name','lang','version','date', 'compatibility'),
				'author' 		=> array('name','url'),
				'summary' 		=> array('summary'),
				'description' 	=> array('description'),
				'keywords' 		=> array('one','two'),
				'category'		=> array('category'),
				'copyright'		=> array('copyright'),
		//		'adminLinks'	=> array('url','description','icon','iconSmall','primary'),
		//		'sitelinks'		=> array('url','description','icon','iconSmall')
			);
			
			// Load old plugin.php file if it exists; 
			$legacyFile = e_PLUGIN.$this->pluginName."/plugin.php";		
			if(file_exists($legacyFile))
			{
				require_once($legacyFile);	
				$mes = e107::getMessage();
				$mes->addInfo("Loading plugin.php file");
				
				$defaults = array(
					"main-name"					=> $eplug_name,
					"author-name"				=> $eplug_author,
					"author-url"				=> $eplug_url,
					"description-description"	=> $eplug_description,
					"summary-summary"			=> $eplug_description
				);
				
				if(count($eplug_tables) && !file_exists(e_PLUGIN.$this->pluginName."/".$this->pluginName."_sql.php"))
				{
					
					$cont = '';
					foreach($eplug_tables as $tab)
					{
						if(strpos($tab,"INSERT INTO")!==FALSE)
						{
							continue;	
						}
						
						$cont .= "\n".str_replace("\t"," ",$tab);	
						
					}
					
					if(file_put_contents(e_PLUGIN.$this->pluginName."/".$this->pluginName."_sql.php",$cont))
					{
						$mes->addInfo($this->pluginName."_sql.php as been generated",'default',true);	
						$red = e107::getRedirect();
						$red->redirect(e_REQUEST_URL,true);
					//	$red->redirect(e_SELF."?mode=create&newplugin=".$this->pluginName."&createFiles=1&step=2",true);
					}
					else 
					{
						$msg = $this->pluginName."_sql.php is missing!<br />";
						$msg .= "Please create <b>".$this->pluginName."_sql.php</b> in your plugin directory with the following content:<pre>".$cont."</pre>";
						$mes->addWarning($msg);	
					}
					
					
				}
			}
			
			$text = "<table class='table adminform'>";
					
			foreach($data as $key=>$val)
			{
				$text.= "<tr><td>$key</td><td>
				<div class='controls'>";
				foreach($val as $type)
				{
					$nm = $key.'-'.$type;
					$name = "xml[$nm]";	
					$size = (count($val)==1) ? 'span7' : 'span2';
					$text .= "<div class='{$size}'>".$this->xmlInput($name, $key."-". $type, vartrue($defaults[$nm]))."</div>";	
				}	
			
				$text .= "</div></td></tr>";
				
				
			}
			$text .= "</table>";
			
			return $text;				
		}
		
		
		function xmlInput($name, $info, $default='')
		{
			$frm = e107::getForm();	
			list($cat,$type) = explode("-",$info);
			
			$size 		= 30;
			$help		= '';
			$pattern	= "";
			$required	= false;
			
			switch ($info)
			{
				
				case 'main-name':
					$help 		= "The name of your plugin. (Must be written in English)";
					$required 	= true;
					$pattern 	= "[A-Za-z0-9 ]*";
				break;
		
				case 'main-lang':
					$help 		= "If you have a language file, enter the LAN_XXX value for the plugin's name";
					$required 	= false;
					$placeholder= " ";
					$pattern 	= "[A-Z0-9_]*";
				break;
				
				case 'main-date':
					$help 		= "Creation date of your plugin";
					$required 	= true;
				break;
				
				case 'main-version':
					$default 	= '1.0';
					$required 	= true;
					$help 		= "The version of your plugin. Format: x.x";
					$pattern	= "^[\d]{1,2}\.[\d]{1,2}$";
				break;

				case 'main-compatibility':
					$default 	= '2.0';
					$required 	= true;
					$help 		= "Compatible with this version of e107";
					$pattern	= "^[\d]{1,2}\.[\d]{1,2}$";
				break;
				
				case 'author-name':
					$default 	= (vartrue($default)) ? $default : USERNAME;
					$required 	= true;
					$help 		= "Author Name";
					$pattern	= "[A-Za-z \.0-9]*";
				break;
				
				case 'author-url':
					$required 	= true;
					$help 		= "Author Website Url";
				//	$pattern	= "https?://.+";
				break;
				
				//case 'main-installRequired':
				//	return "Installation required: ".$frm->radio_switch($name,'',LAN_YES, LAN_NO);
				//break;	
				
				case 'summary-summary':
					$help 		= "A short one-line description of the plugin<br />(Must be written in English)";
					$required 	= true;
					$size 		= 100;
					$placeholder= " ";
					$pattern	= "[A-Za-z \.0-9]*";
				break;	
				
				case 'keywords-one':
				case 'keywords-two':
					$help 		= "Keyword/Tag for this plugin<br />(Must be written in English)";
					$required 	= true;
					$size 		= 20;
					$placeholder= " ";
					$pattern 	= '^[a-z]*$';
				break;	
				
				case 'description-description':
					$help 		= "A full description of the plugin<br />(Must be written in English)";
					$required 	= true;
					$size 		= 100;
					$placeholder = " ";
					$pattern	= "[A-Za-z \.0-9]*";
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
			$placeholder = (varset($placeholder)) ? $placeholder : $type;
			$pat = ($pattern) ? "&pattern=".$pattern : "";
			
			switch ($type) 
			{
				case 'date':
					$text = $frm->datepicker($name, time(), 'format=yyyy-mm-dd'.$req);		
				break;
				
				case 'description':
					$text = $frm->textarea($name,$default, 3, 100, $req);	// pattern not supported. 	
				break;
								
						
				case 'category':
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
				
					$text = $frm->select($name, $options,'','required=1&class=null', true);	
				break;
				
				
				default:
					$text = $frm->text($name, $default, $size, 'placeholder='.$placeholder . $req. $pat);	
				break;
			}
	
			
			$text .= ($help) ? "<span class='field-help'>".$help."</span>" : "";
			return $text;
			
		}

		function createXml($data)
		{
		//	print_a($_POST);
			$ns = e107::getRender();
			$mes = e107::getMessage();
			$tp = e107::getParser();
			
			foreach($data as $key=>$val)
			{
				$key = strtoupper(str_replace("-","_",$key));
				$newArray[$key] = $val;	
				
			}
			
			$newArray['DESCRIPTION_DESCRIPTION'] = strip_tags($tp->toHtml($newArray['DESCRIPTION_DESCRIPTION'],true));
			
			foreach($_POST['pluginPrefs'] as $val)
			{
				if(vartrue($val['index']))
				{
					$id = $val['index'];
					$plugPref[$id] = $val['value'];		
				}	
			}
			
		//	print_a($_POST['pluginPrefs']);
			
			if(count($plugPref))
			{
				$xmlPref = "<pluginPrefs>\n";
				foreach($plugPref as $k=>$v)
				{
					$xmlPref .= "		<pref name='".$k."'>".$v."</pref>\n";	
				}	
				
				$xmlPref .= "	</pluginPrefs>";
				$newArray['PLUGINPREFS'] = $xmlPref;
			}
			
			//	print_a($newArray);
			// print_a($this);
			
$template = <<<TEMPLATE
<?xml version="1.0" encoding="utf-8"?>
<e107Plugin name="{MAIN_NAME}" lan="{MAIN_LANG}" version="{MAIN_VERSION}" date="{MAIN_DATE}" compatibility="{MAIN_COMPATIBILITY}" installRequired="true" >
	<author name="{AUTHOR_NAME}" url="{AUTHOR_URL}" />
	<summary lan="">{SUMMARY_SUMMARY}</summary>
	<description lan="">{DESCRIPTION_DESCRIPTION}</description>
	<keywords>
		<word>{KEYWORDS_ONE}</word>
		<word>{KEYWORDS_TWO}</word>
	</keywords>
	<category>{CATEGORY_CATEGORY}</category>
	<copyright>{COPYRIGHT_COPYRIGHT}</copyright>
	<adminLinks>
		<link url="admin_config.php" description="{ADMINLINKS_DESCRIPTION}" icon="images/icon_32.png" iconSmall="images/icon_16.png" primary="true" >LAN_CONFIGURE</link>
	</adminLinks>
	{PLUGINPREFS}
</e107Plugin>
TEMPLATE;


// pluginPrefs




// TODO
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
			
			
			if($this->createFiles == true)
			{
				if(file_put_contents($path,$result) )
				{
					$mes->addSuccess("Saved: ".$path);
				}
				else {
					$mes->addError("Couldn't Save: ".$path);
				}
			}
			return  htmlentities($result);
			
		//	$ns->tablerender(LAN_CREATED.": plugin.xml", "<pre  style='font-size:80%'>".htmlentities($result)."</pre>");	
		}
						
					
				
			


		function form($table,$fieldArray)
		{
			$frm = e107::getForm();
					
			$modes = array("main"=>"Main Area","cat"=>"Categories","other1"=>"Other 1","other2"=>"Other 2");
			
		//	echo "TABLE COUNT= ".$this->tableCount ;
			
			
			$this->table = $table."_ui";
			
			$c=0;
			foreach($modes as $id=>$md)
			{
				$tbl = $this->tableList[$c];
				$defaultMode[$tbl] = $id;	
				$c++;
			}
			
		//	print_a($defaultMode);
				
			$text = 	$frm->hidden($this->table.'[pluginName]', $this->pluginName, 15).
						$frm->hidden($this->table.'[table]', $table, 15);
				
			if($this->tableCount > 1)
			{
				$text .= "<table class='table adminform'>\n";
				$text .= "
					<tr>
						<td>Mode</td>
						<td>".$frm->select($this->table."[mode]",$modes, $defaultMode[$table], 'required=1&class=null', true)."</td>
					</tr>
					
				";
			}
			else
			{
				$text .= $frm->hidden($this->table.'[mode]','main');
			}
				
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
							<th class='center'>Inline</th>
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
					<td>".$frm->text($this->table."[fields][".$name."][width]", $this->guess($name, $val,'width'), 4, 'size=mini')."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][batch]", true, $this->guess($name, $val,'batch'))."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][filter]", true, $this->guess($name, $val,'filter'))."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][inline]", true, $this->guess($name, $val,'inline'))."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][validate]", true)."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][fieldpref]", true, $this->guess($name, $val,'fieldpref'))."</td>
					<td>".$frm->text($this->table."[fields][".$name."][help]",'', 50,'size=medium')."</td>
					<td>".$frm->text($this->table."[fields][".$name."][readParms]",'', 20,'size=small')."</td>
					<td>".$frm->text($this->table."[fields][".$name."][writeParms]",'', 20,'size=small').
					$frm->hidden($this->table."[fields][".$name."][class]", $this->guess($name, $val,'class')).
					$frm->hidden($this->table."[fields][".$name."][thclass]", $this->guess($name, $val,'thclass')).
					"</td>
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
					"boolean"	=> "True/False",
					"number"	=> "Text Box",
					"dropdown"	=> "DropDown",
					"userclass"	=> "DropDown (userclasses)",
					"datestamp"	=> "Date",
					"method"	=> "Custom Function",
					"hidden"	=> "Hidden",
					"user"		=> "User",
					);	
				break;
				
				case 'decimal':
					$array = array(
					"number"	=> "Text Box",
					"dropdown"	=> "DropDown",
					"method"	=> "Custom Function",
					"hidden"	=> "Hidden",
					);	
				break;
				
				case 'varchar':
				case 'tinytext':
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
				case 'mediumtext':
				case 'longtext':
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
			return $frm->select($fname, $array, $this->guess($name, $val),'required=1&class=null', true);
			
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
			$ret['width'] = 'auto';
			$ret['class'] = 'left';
			$ret['thclass'] = 'left';
			
			//echo "<br />name=".$name; 
			switch ($name) 
			{
				
				case 'id':
					$ret['title'] = 'LAN_ID';
					$ret['type'] = 'boolean';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['inline'] = false;
					$ret['width'] = '5%';
				break;
				
				case 'start':
				case 'end':
				case 'datestamp':
				case 'date':
					$ret['title'] = 'LAN_DATESTAMP';
					$ret['type'] = 'datestamp';
					$ret['batch'] = false;
					$ret['filter'] = true;
					$ret['fieldpref'] = true;
					$ret['inline'] = false;
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
					$ret['inline'] = true;
				break;
				
				case 'author':
					$ret['title'] = 'LAN_AUTHOR';
					$ret['type'] = 'user';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['inline'] = false;
				break;
				
				case 'thumb':
				case 'thumbnail':
				case 'image':
					$ret['title'] = 'LAN_IMAGE';
					$ret['type'] = 'image';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['inline'] = false;
				break;

				case 'total':
				case 'order':
				case 'limit':
					$ret['title'] = 'LAN_ORDER';
					$ret['type'] = 'number';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['inline'] = false;
				break;

				case 'category':
					$ret['title'] = 'LAN_CATEGORY';
					$ret['type'] = 'dropdown';
					$ret['batch'] = true;
					$ret['filter'] = true;
					$ret['fieldpref'] = true;
					$ret['inline'] = false;
				break;
				
				case 'type':
					$ret['title'] = 'LAN_TYPE';
					$ret['type'] = 'dropdown';
					$ret['batch'] = true;
					$ret['filter'] = true;
					$ret['fieldpref'] = true;
					$ret['inline'] = true;
				break;
								
				case 'icon':
				case 'button':
					$ret['title'] = 'LAN_ICON';
					$ret['type'] = 'icon';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['inline'] = false;
				break;
				
				case 'website':
				case 'url':
				case 'homepage':
					$ret['title'] = 'LAN_URL';
					$ret['type'] = 'url';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['inline'] = true;
				break;
				
				case 'visibility':
				case 'class':
					$ret['title'] = 'LAN_USERCLASS';
					 $ret['type'] = 'userclass';
					 $ret['batch'] = true;
					 $ret['filter'] = true;
					 $ret['fieldpref'] = true;
					$ret['inline'] = true;
				break;
				
				case 'description':
					$ret['title'] = 'LAN_DESCRIPTION';
					 $ret['type'] = ($val['type'] == 'TEXT') ? 'textarea' : 'text';
					 $ret['width'] = '40%';
					$ret['inline'] = false;
				break;
				
				default:
					$ret['type'] = 'boolean';
					$ret['class'] = 'center';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['thclass'] = 'center';
					$ret['width'] = 'auto';
					$ret['inline'] = false;
					break;
			}
			
			return vartrue($ret[$mode]);
			
		}




		function fieldData($name, $val)
		{
			$frm = e107::getForm();
			$type = $val['type'];
			
			$strings = array('time','timestamp','datetime','year','tinyblob','blob',
							'mediumblob','longblob','tinytext','mediumtext','longtext','text','date','varchar','char');
			
			
			if(in_array(strtolower($type),$strings))
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
			
			$pluginTitle = $_POST['xml']['main-name'] ;
			
			if($_POST['xml'])
			{
				$xmlText =	$this->createXml($_POST['xml']);
			}
					
			
			
			
			
			unset($_POST['step'],$_POST['xml']);
		$thePlugin = $_POST['newplugin'];

$text = "\n
// Generated e107 Plugin Admin Area 

require_once('../../class2.php');
if (!getperms('P')) 
{
	header('location:'.e_BASE.'index.php');
	exit;
}



class ".$thePlugin."_admin extends e_admin_dispatcher
{

	protected \$modes = array(	
	";
	

	unset($_POST['newplugin']);
	
			foreach($_POST as $table => $vars) // LOOP Through Tables. 
			{
				if(vartrue($vars['mode']))
				{
	$text .= "
		'".$vars['mode']."'	=> array(
			'controller' 	=> '".$vars['table']."_ui',
			'path' 			=> null,
			'ui' 			=> '".$vars['table']."_form_ui',
			'uipath' 		=> null
		),
		
";
				}
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
				if(vartrue($vars['mode']))
				{
$text .= "
		'".$vars['mode']."/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
		'".$vars['mode']."/create'		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),
";
}
			}
			
if($_POST['pluginPrefs'][0]['index'])
{
				
$text .= "			
	
		'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => 'P'),	
";
}
$text .= "
		// 'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
	);

	protected \$adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected \$menuTitle = '".vartrue($vars['pluginName'],'Nav')."';
}



";			
			// print_a($_POST);

			
			$srch = array(
				
				"\n",
				"),",
				"    ",
				"'batch' => '1'",
				"'filter' => '1'",
				"'inline' => '1'",
				"'validate' => '1'",
				", 'fieldpref' => '1'",
				"'type' => ''",
				"'data' => ''"
			 );
			 
			$repl = array(
				
				 "",
				 "),\n\t\t",
				 " ",
				"'batch' => true",
				"'filter' => true",
				"'inline' => true",
				"'validate' => true",
				"",
				"'type' => null",
				"'data' => null"
				  );
			
	
			
			 
			$tableCount = 1;
			foreach($_POST as $table => $vars) // LOOP Through Tables. 
			{
				if($table == 'pluginPrefs')
				{
					continue;
				}
				$FIELDS = str_replace($srch,$repl,var_export($vars['fields'],true));
				$FIELDS = preg_replace("#('([A-Z0-9_]*?LAN[_A-Z0-9]*)')#","$2",$FIELDS); // remove quotations from LANs. 
				$FIELDPREF = array();
				
				foreach($vars['fields'] as $k=>$v)
				{
					if(isset($v['fieldpref']) && $k != 'checkboxes' && $k !='options')
					{
						$FIELDPREF[] = "'".$k."'";
					}							
				}
				
$text .= 
"
				
class ".$table." extends e_admin_ui
{
			
		protected \$pluginTitle		= '".$pluginTitle."';
		protected \$pluginName		= '".$vars['pluginName']."';
		protected \$table			= '".$vars['table']."';
		protected \$pid				= '".$vars['pid']."';
		protected \$perPage 			= 10; 
			
		protected \$fields 		= ".$FIELDS.";		
		
		protected \$fieldpref = array(".implode(", ",$FIELDPREF).");
		
";


if($_POST['pluginPrefs'] && ($vars['mode']=='main'))
{
	$text .= "		
		
	
		protected \$prefs = array(	\n";
		
		foreach($_POST['pluginPrefs'] as $k=>$val)
		{
			if(vartrue($val['index']))
			{
				$index = $val['index'];
				$type = vartrue($val['type'],'text');
				
				$text .= "\t\t\t'".$index."'\t\t=> array('title'=> '".ucfirst($index)."', 'type'=>'".$type."', 'data' => 'string','help'=>'Help Text goes here'),\n";	
			}	
	
		}
		
		
		$text .= "\t\t); \n\n";
				
}
				
			
		
	







$text .= "	
	/*	
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
	if(varset($val['type']) != 'method')
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
						
	 		$tableCount++;	
					
			} // End LOOP. 
	
$text .= '		
new '.$thePlugin.'_admin();

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
			
			if($this->createFiles == true)
			{
				if(file_put_contents($generatedFile, $startPHP .$text . $endPHP))
				{
					$mes->addSuccess("<a href='".$generatedFile."'>Click Here</a> to vist your generated admin area");
				}	
				else 
				{
					$mes->addError("Could not write to ".$generatedFile);
				}
			}
			else
			{
				$mes->addInfo("No Files have been created. Please Copy &amp; Paste the code below into your files. ");	
			}
			
			echo $mes->render();
			
			$ns->tablerender(ADLAN_98.SEP."Plugin Builder".SEP." plugin.xml", "<pre style='font-size:80%'>".$xmlText."</pre>");	
	
			
			$ns->tablerender("admin_config.php", "<pre style='font-size:80%'>".$text."</pre>");
			
		//	
			return;
	
		}
}

?>