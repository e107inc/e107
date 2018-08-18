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

if(!getperms("Z"))
{
	e107::redirect('admin');
	exit;
}

e107::coreLan('plugin', true);

$e_sub_cat = 'plug_manage';

define('PLUGIN_SHOW_REFRESH', FALSE);
define('PLUGIN_SCAN_INTERVAL', !empty($_SERVER['E_DEV']) ? 0 : 360);
define("ADMIN_GITSYNC_ICON", e107::getParser()->toGlyph('fa-refresh', array('size'=>'2x', 'fw'=>1)));


global $user_pref;
/*

if(!deftrue('e_DEBUG_PLUGMANAGER'))
{
	require_once(e_HANDLER.'plugin_class.php');
	require_once(e_HANDLER.'file_class.php');
	$plugin = new e107plugin;
	$pman = new pluginManager;

	define("e_PAGETITLE",ADLAN_98." - ".$pman->pagetitle);
}
*/

if(isset($_POST['uninstall_cancel']))
{
	header("location:".e_SELF);
	exit;		
}


// Experimental rewrite for v2.1.5 ----------------------




class plugman_adminArea extends e_admin_dispatcher
{

	protected $modes = array(

		'installed'	=> array(
			'controller' 	=> 'plugin_ui',
			'path' 			=> null,
			'ui' 			=> 'plugin_form_ui',
			'uipath' 		=> null
		),
		'avail'	=> array(
			'controller' 	=> 'plugin_ui',
			'path' 			=> null,
			'ui' 			=> 'plugin_form_ui',
			'uipath' 		=> null
		),
		'online'	=> array(
			'controller' 	=> 'plugin_online_ui',
			'path' 			=> null,
			'ui' 			=> 'plugin_form_online_ui',
			'uipath' 		=> null
		),
		'create'	=> array(
			'controller' 	=> 'plugin_ui',
			'path' 			=> null,
			'ui' 			=> 'plugin_form_ui',
			'uipath' 		=> null
		),
		'lans'      => array(
			'controller' 	=> 'pluginLanguage',
			'path' 			=> null,
			'ui' 			=> 'plugin_form_ui',
			'uipath' 		=> null
		),

	);


	protected $adminMenu = array(

		'installed/list'		=> array('caption'=> EPL_ADLAN_22, 'perm' => 'Z'),
		'avail/list'			=> array('caption'=> EPL_ADLAN_23, 'perm' => 'Z'),
		'online/list'			=> array('caption'=> EPL_ADLAN_220, 'perm' => 'Z'),
		'avail/upload'			=> array('caption'=>EPL_ADLAN_38, 'perm' => '0'),
		'create/build'          =>  array('caption'=>EPL_ADLAN_114, 'perm' => '0'),

	//	'main/create'		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),

		// 'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
	);


	protected $defaultMode = 'installed';


	protected $adminMenuAliases = array(
		'installed/uninstall'	=> 'installed/list',
		'lans/list'             => 'create/build'
	);

	protected $adminMenuIcon = 'e-plugmanager-24';

	function init()
	{

		$mode = $this->getRequest()->getMode();
		$action = $this->getRequest()->getAction();

		if($mode === 'online' && $action === 'download')
		{
			define('e_IFRAME', true);
		}

		if(deftrue('e_DEVELOPER'))
		{
			e107::getPlug()->clearCache();
		}
	}


	public static function getPluginManagerFields()
	{

		return array(
				'checkboxes'         => array('title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',),
		        'plugin_id'          => array('title' => LAN_ID, 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',),
				'plugin_icon'        => array('title' => LAN_ICON, 'type' => 'icon', 'data' => false, "width" => "5%", 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',),
		        'plugin_name'        => array('title' => LAN_TITLE, 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',),
		        'plugin_version'     => array('title' => LAN_VERSION, 'type' => 'text', 'data' => false, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',),
				'plugin_date'       => array('title' => LAN_RELEASED, 'type' => 'text', 'data' => false,  "width" => "8%", 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',),
			    'plugin_category'    => array('title' => LAN_CATEGORY, 'type' => 'dropdown', 'data' => 'str', 'width' => 'auto', 'batch' => true, 'filter' => true, 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => array(), 'class' => 'left', 'thclass' => 'left',),

				'plugin_author'      => array('title' => LAN_AUTHOR, 'type' => 'text', 'data' => false, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',),
				"plugin_license"	 => array("title" => "License", 	 'nolist'=>false,'data'=>false,	 "type"=>"text", "width" => "5%", "thclass" => "left"),
  				'plugin_compatible'  => array('title' => EPL_ADLAN_13, 'type' => 'method', 'data' => false, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',),
				'plugin_description' => array('title' => LAN_DESCRIPTION, 'type' => 'textarea', 'data' => false, 'width' => 'auto', 'help' => '', 'readParms' => 'expand=1&truncate=180&bb=1', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',),

				'plugin_path'        => array('title' => LAN_PATH, 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',),
		        'plugin_installflag' => array('title' => EPL_ADLAN_22, 'type' => 'boolean', 'data' => 'int', 'width' => 'auto', 'filter' => false, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',),
		        'plugin_addons'      => array('title' => LAN_ADDONS, 'type' => 'method', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',),
		         'options'            => array('title' => LAN_OPTIONS, 'type' => 'method', 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',),
		);


	}




	protected $menuTitle = ADLAN_98;
}





class plugin_ui extends e_admin_ui
{

		protected $pluginTitle		= ADLAN_98;
		protected $pluginName		= 'core';
	//	protected $eventName		= 'plugman-plugin'; // remove comment to enable event triggers in admin.
		protected $table			= 'plugin';
		protected $pid				= 'plugin_id';
		protected $perPage			= 10;

		protected $batchDelete		= false;
		protected $batchExport     = false;
		protected $batchCopy		= false;
	//	protected $sortField		= 'somefield_order';
	//	protected $orderStep		= 10;
	//	protected $tabs				= array('Tabl 1','Tab 2'); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable.

		protected $listQry      	= "SELECT * FROM `#plugin` WHERE plugin_installflag = 1 AND plugin_category != 'menu' "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.

		protected $listOrder		= 'plugin_path ASC';

		protected $fields = array();

		protected $fieldpref = array('plugin_icon', 'plugin_name', 'plugin_version', 'plugin_description', 'plugin_compatible', 'plugin_released','plugin_author', 'plugin_category','plugin_installflag');


	//	protected $preftabs        = array('General', 'Other' );
		protected $prefs = array(
		);

		// Ideal way to set field data.

		public function __construct($request, $response, $params = array())
		{
			$this->fields = plugman_adminArea::getPluginManagerFields();
			$this->fields['plugin_category']['writeParms']['optArray'] = e107::getPlug()->getCategoryList(); // array('plugin_category_0','plugin_category_1', 'plugin_category_2'); // Example Drop-down array.

			unset($this->fields['plugin_category']['writeParms']['optArray']['menu']);
			unset($this->fields['plugin_category']['writeParms']['optArray']['about']);

			parent:: __construct($request, $response, $params);

		}


		public function init()
		{

			if(!e_QUERY)
			{
				e107::getPlug()->clearCache();
			}


			if($this->getMode()=== 'avail')
			{
				$this->listQry  = "SELECT * FROM `#plugin` WHERE plugin_installflag = 0 AND plugin_category != 'menu'  ";
			}

			// Set drop-down values (if any).

		}

		// Modify the list data.
        public function ListObserver()
        {
            parent::ListObserver();

	        $this->setPlugData();
        }

		private function setPlugData()
		{
			   $tree = $this->getTreeModel();

				$plg = e107::getPlug();

	            foreach ($tree->getTree() as $id => $model)
	            {
					$path = $model->get('plugin_path');

					$plg->load($path);

		            $model->set('plugin_name',$plg->getName());
		            $model->set('plugin_date',$plg->getDate());
		            $model->set('plugin_author',$plg->getAuthor());
		            $model->set('plugin_compatible',$plg->getCompat());
		            $model->set('plugin_admin_url',$plg->getAdminUrl());
		            $model->set('plugin_admin_caption', $plg->getAdminCaption());
		            $model->set('plugin_description',$plg->getDescription());
		            $model->set('plugin_version_file',$plg->getVersion());
		            $model->set('plugin_install_required',$plg->getInstallRequired());
	                $model->set('plugin_icon',$plg->getIcon(32, 'path'));

	            }

		}


		public function ListAjaxObserver()
		{

			$this->getTreeModel()->setParam('db_query', $this->_modifyListQry(false, false, 0, false, $this->listQry))->load();

			$this->setPlugData();
		}

        private function pluginProcessUpload()
        {
			if (!$_POST['ac'] == md5(ADMINPWCHANGE))
			{
				exit;
			}

			$fl = e107::getFile();
			$data = $fl->getUploaded(e_TEMP);
			$mes = e107::getMessage();

			if(empty($data[0]['error']))
			{
				if($fl->unzipArchive($data[0]['name'],'plugin'))
				{
					$mes->addSuccess(EPL_ADLAN_43);
				}
				else
				{
					$mes->addError(EPL_ADLAN_97);
				}
			}


			//echo $mes->render();

			return true;

	     }



		function renderHelp()
		{
			$plg = e107::getPlug();
			if(!$list = $plg->getUpgradableList())
			{
				return null;
			}


			$text = "<ul class='media-list'>";
			foreach($list as $path=>$ver)
			{
				$plg->load($path);
				$url = e_ADMIN."plugin.php?mode=installed&action=upgrade&id=".$path;
				$text .= "<li class='media'>
				<div class='media-left'>
					<a href='".$url."'>".$plg->getIcon(32)."</a>
					</div><div class='media-body'><a href='".$url."'>".$plg->getName()."</a></div></li>";

			}
			$text .= "</ul>";




			return array('caption'=>EPL_ADLAN_247, 'text'=>$text);

		}

		// Action Pages.





		function installPage()
		{
			$id = $this->getId();

			$text = e107::getPlugin()->install($id);

			$log = e107::getAdminLog();

			if ($text === FALSE)
			{
				e107::getMessage()->add(EPL_ADLAN_99, E_MESSAGE_ERROR);
			}
			else
			{
				//$plugin->save_addon_prefs('update');
			//	$info = $plugin->getinfo($this->id);  //FIXME use e107::getPlug();

			//	$name = deftrue($info['plugin_name'],$info['plugin_name']). " v".$info['plugin_version']. "({e_PLUGIN}".$info['plugin_path'].")";

			//	$log->log_event('PLUGMAN_01', $name, E_LOG_INFORMATIVE, '');

				// make sure ALL plugin/addon pref lists get update and are current
				e107::getPlug()->clearCache()->buildAddonPrefLists();

				e107::getMessage()->add($text, E_MESSAGE_SUCCESS);
			}


			$this->redirectAction('list');
		}


		function buildPage()
		{
			$pc = new pluginBuilder;
			$ret = $pc->run();

			if(is_array($ret))
			{
				$this->addTitle($ret['caption']);
				return $ret['text'];
			}

			return $ret;
		}


		function lanPage()
		{



		}

		function uninstallPage()
		{
			$id = $this->getId();

			if(empty($_POST['uninstall_confirm']))
			{

				$plug_vars = e107::getPlug()->load($id)->getMeta();

				$name = e107::getPlug()->getName();
				$this->addTitle(EPL_ADLAN_63);
				$this->addTitle($name);

				return $this->pluginConfirmUninstall($plug_vars);
			}

			$post = e107::getParser()->filter($_POST);

			if(empty($_POST['e-token']))
			{
				return false;
			}

		//	$id = e107::getPlugin

			$text = e107::getPlugin()->uninstall($id, $post);

			// make sure ALL plugin/addon pref lists get update and are current
			e107::getPlug()->clearCache()->buildAddonPrefLists();

			e107::getMessage()->add($text, E_MESSAGE_SUCCESS);
			$log = e107::getPlugin()->getLog();
			e107::getDebug()->log($log);

			$this->redirectAction('list');
		}



		function repairPage()
		{
			$id = $this->getId();

			if(!is_dir(e_PLUGIN.$id))
			{
				e107::getMessage()->addError("Bad Link");
				return false;
			}

			e107::getSingleton('e107plugin')->refresh($id);
			e107::getLog()->add('PLUGMAN_04', $id, E_LOG_INFORMATIVE, '');

			e107::getMessage()->addSuccess("Repair Complete (".$id.")"); // Repair Complete ([x])

			$this->redirectAction('list');
		}


		function pullPage()
		{
			$id = $this->getId();

			if(!e107::isInstalled($id))
			{
				$this->redirectAction('list');
			}


			$return = e107::getFile()->gitPull($id, 'plugin');
			e107::getMessage()->addSuccess($return);
			e107::getPlugin()->refresh($id);

			$this->redirectAction('list');
		}


		function upgradePage()
		{
			$this->pluginUpgrade();
		}



		function uploadPage()
		{

			global $plugin;
		    $frm = e107::getForm();
			if(!empty($_POST['upload']))
			{
	            $this->pluginProcessUpload();

				$this->redirectAction('list');
			}



		//TODO 'install' checkbox in plugin upload form. (as it is for theme upload)

		/* plugin upload form */

			if(!is_writable(e_PLUGIN))
			{
			   	$text = EPL_ADLAN_44;
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


			return $text;
            e107::getRender()->tablerender(ADLAN_98.SEP.EPL_ADLAN_38, $text);


		}

		private function pluginUpgrade()
		{
			$pref 		= e107::getPref();
			$admin_log 	= e107::getAdminLog();
			$plugin 	= e107::getPlugin();

		    $sql 		= e107::getDb();
	        $mes 		= e107::getMessage();

	        $id         = $this->getId();

			$plug 		= e107::getPlug()->load($id)->getMeta();

			$text = '';

			$_path = e_PLUGIN.$id.'/';
			if(file_exists($_path.'plugin.xml'))
			{
				$plugin->install_plugin_xml($id, 'upgrade');
			}
			else
			{
				$eplug_folder = null;
				$upgrade_alter_tables = null;
				$upgrade_add_prefs = null;
				$upgrade_remove_prefs = null;
				$upgrade_add_array_pref = null;
				$upgrade_remove_array_pref = null;
				$eplug_version = null;



				include(e_PLUGIN.$plug['plugin_path'].'/plugin.php');

				$text = '';

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

				$info = e107plugin::getPluginRecord($this->id);

				$name = deftrue($info['plugin_name'],$info['plugin_name']). " v".$eplug_version. "({e_PLUGIN}".$info['plugin_path'].")";

				e107::getLog()->add('PLUGMAN_02', $name, E_LOG_INFORMATIVE, '');
				$text .= (isset($eplug_upgrade_done)) ? '<br />'.$eplug_upgrade_done : "<br />".LAN_UPGRADE_SUCCESSFUL;
				$sql->update('plugin', "plugin_version ='{$eplug_version}', plugin_addons='{$eplug_addons}' WHERE plugin_id='$this->id' ");
				$pref['plug_installed'][$plug['plugin_path']] = $eplug_version; 			// Update the version

				e107::getConfig('core')->setPref($pref);
				$plugin->rebuildUrlConfig();
				e107::getConfig('core')->save();
			}


			$mes->addSuccess($text);
			//$plugin->save_addon_prefs('update');

			// make sure ALL plugin/addon pref lists get update and are current
			e107::getPlug()->clearCache()->buildAddonPrefLists();

			$this->redirectAction('list');
	   }




		private function pluginConfirmUninstall($plug_vars)
		{
			global $plugin;

			$frm 	= e107::getForm();
			$tp 	= e107::getParser();
			$mes 	= e107::getMessage();


			$path = $plug_vars['folder'];
		//	$plug = $plugin->getinfo($this->id);

			if(!e107::isInstalled($path))
			{
				return false;
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

			if(is_writable(e_PLUGIN.$path))
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
			$icons = $med->listIcons(e_PLUGIN.$path);

			$iconText = '';

			if(count($icons)>0)
			{
				foreach($icons as $key=>$val)
				{
					$iconText .= "<img src='".$tp->replaceConstants($val)."' alt='' />";
				}

				$iconText = '<div class="icon-pool-preview">'.$iconText.'</div>';

				$opts['delete_ipool'] = array(
					'label'			=> EPL_ADLAN_231,
					'preview'		=> $iconText,
					'helpText'		=> EPL_ADLAN_79,
					'itemList'		=> array(1=>LAN_YES,0=>LAN_NO),
					'itemDefault' 	=> 1
				);


			}



			if(is_readable(e_PLUGIN.$path."/".$path."_setup.php"))
			{
				include_once(e_PLUGIN.$path."/".$path."_setup.php");


				$mes->add("Loading ".e_PLUGIN.$path."/".$path."_setup.php", E_MESSAGE_DEBUG);

				$class_name = $path."_setup";

				if(class_exists($class_name))
				{
					$obj = new $class_name;
					if(method_exists($obj,'uninstall_options'))
					{
						$arr = call_user_func(array($obj,'uninstall_options'), $this);
						foreach($arr as $key=>$val)
						{
							$newkey = $path."_".$key;
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



			$text .= "<input type='hidden' name='e-token' value='".e_TOKEN."' /></div>
			</fieldset>
			</form>
			";

			return $text;
		//	e107::getRender()->tablerender(EPL_ADLAN_63.SEP.$tp->toHtml($plug_vars['@attributes']['name'], "", "defs,emotes_off, no_make_clickable"),$mes->render(). $text);

		}
	/*
		// optional - a custom page.
		public function customPage()
		{
			$text = 'Hello World!';
			$otherField  = $this->getController()->getFieldVar('other_field_name');
			return $text;

		}
	*/

}



class plugin_form_ui extends e_admin_form_ui
{


	// Custom Method/Function
	function plugin_compatible($curVal,$mode)
	{
		$frm = e107::getForm();

		switch($mode)
		{
			case 'read': // List Page

				if(intval($curVal) > 1)
				{
					return "<span class='label label-warning'>".$curVal."</span>";
				}

				return $curVal;
			break;

			case 'write': // Edit Page
				return $frm->text('plugin_name',$curVal, 255, 'size=large');
			break;

			case 'filter':
			case 'batch':
				return  array();
			break;
		}
	}


	// Custom Method/Function
	function plugin_addons($curVal,$mode)
	{
		$frm = e107::getForm();

		switch($mode)
		{
			case 'read': // List Page
				return $curVal;
			break;

			case 'write': // Edit Page
				return $frm->text('plugin_addons',$curVal, 255, 'size=large');
			break;

			case 'filter':
			case 'batch':
				return  array();
			break;
		}
	}


	function options($val, $curVal)
	{

		$tp = e107::getParser();

		$var = $this->getController()->getListModel()->getData();

		$mode = $this->getController()->getMode();

	//	e107::getDebug()->log($var);

		$_path = e_PLUGIN . $var['plugin_path'] . '/';

		if($var['plugin_admin_url'] && $var['plugin_installflag'] == true)
		{

			$conf_title = !empty($var['plugin_admin_caption']) ? $var['plugin_admin_caption'] : LAN_CONFIGURE . ' ' . $tp->toHTML($var['plugin_name'], "", "defs,emotes_off, no_make_clickable");
			$plugin_config_icon = "<a class='btn btn-default' title='{$conf_title}' href='" . $var['plugin_admin_url'] . "' >" . ADMIN_CONFIGURE_ICON . "</a>";
		}

		$text = "<div class='btn-group'>";
		$text .= vartrue($plugin_config_icon);

		if($var['plugin_install_required'] == true)
		{

			if($var['plugin_installflag'])
			{
				$text .= ($var['plugin_installflag'] ? "<a class='btn btn-default' href=\"" . e_SELF . "?mode=".$mode."&action=uninstall&id={$var['plugin_path']}\" title='" . EPL_ADLAN_1 . "'  >" . ADMIN_UNINSTALLPLUGIN_ICON . "</a>" : "<a class='btn' href=\"" . e_SELF . "?install.{$var['plugin_id']}\" title='" . EPL_ADLAN_0 . "' >" . ADMIN_INSTALLPLUGIN_ICON . "</a>");
			}
			else
			{
				$text .= "<a class='btn btn-default' href=\"" . e_SELF . "?mode=installed&action=install&id={$var['plugin_path']}\" title='" . EPL_ADLAN_0 . "' >" . ADMIN_INSTALLPLUGIN_ICON . "</a>";
			}

		}
		else
		{
			if($var['menuName'])
			{
			//	$text .= EPL_NOINSTALL . str_replace("..", "", e_PLUGIN . $var['plugin_path']) . "/ " . EPL_DIRECTORY;
			}
			else
			{
			//	$text .= EPL_NOINSTALL_1 . str_replace("..", "", e_PLUGIN . $var['plugin_path']) . "/ " . EPL_DIRECTORY;
				if($var['plugin_installflag'] == false)
				{
			//		e107::getDb()->delete('plugin', "plugin_installflag=0 AND (plugin_path='{$var['plugin_path']}' OR plugin_path='{$var['plugin_path']}/' )  ");
				}
			}
		}

		if($var['plugin_version'] != $var['plugin_version_file'] && $var['plugin_installflag'])
		{
			$text .= "<a class='btn btn-default' href='" . e_SELF . "?mode=".$mode."&action=upgrade&id={$var['plugin_path']}' title=\"" . EPL_UPGRADE . " v" . $var['plugin_version_file'] . "\" >" . ADMIN_UPGRADEPLUGIN_ICON . "</a>";
		}

		if($var['plugin_installflag'] && e_DEBUG == true)
		{
			$text .= "<a class='btn btn-default' href='" . e_SELF . "?mode=".$mode."&action=repair&id={$var['plugin_path']}' title='" . LAN_REPAIR_PLUGIN_SETTINGS . "'> " . ADMIN_REPAIRPLUGIN_ICON . "</a>";
		}

		if($var['plugin_installflag'] && is_dir($_path . ".git"))
		{
			$text .= "<a class='plugin-manager btn btn-default' href='" . e_SELF . "?mode=".$mode."&action=pull&id={$var['plugin_path']}' title='" . LAN_SYNC_WITH_GIT_REPO . "'> " . ADMIN_GITSYNC_ICON . "</a>";
		}


		$text .= "</div>	";

		return $text;
	}

}







class plugin_online_ui extends e_admin_ui
{

		protected $pluginTitle		=  ADLAN_98;
		protected $pluginName		= 'core';
	//	protected $eventName		= 'plugman-plugin'; // remove comment to enable event triggers in admin.
		protected $table			= false;
		protected $pid				= '';
		protected $perPage			= 10;
		protected $batchDelete		= true;
		protected $batchExport     = true;
		protected $batchCopy		= true;
	//	protected $sortField		= 'somefield_order';
	//	protected $orderStep		= 10;
	//	protected $tabs				= array('Tabl 1','Tab 2'); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable.

	//	protected $listQry      	= "SELECT * FROM `#tableName` WHERE field != '' "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.

		protected $listOrder		= '';

		protected $fields 		= array ();

		protected $fieldpref = array('plugin_icon', 'plugin_name', 'plugin_version', 'plugin_license', 'plugin_description', 'plugin_compatible', 'plugin_date','plugin_author', 'plugin_category','plugin_installflag');


	//	protected $preftabs        = array('General', 'Other' );
		protected $prefs = array(
		);

		protected $mp = null;


		public function __construct($request, $response, $params = array())
		{

			$this->fields = plugman_adminArea::getPluginManagerFields();
			$this->fields['plugin_category']['writeParms']['optArray'] = e107::getPlug()->getCategoryList(); // array('plugin_category_0','plugin_category_1', 'plugin_category_2'); // Example Drop-down array.
			$this->fields["plugin_license"]['nolist'] = false;
			$this->fields['plugin_category']['inline'] = false;
			parent:: __construct($request, $response, $params);

		}


		public function init()
		{
			require_once(e_HANDLER.'e_marketplace.php');
		}

		function pluginCheck($force=false)
		{
			if(!PLUGIN_SCAN_INTERVAL)
			{
				e107::getPlugin()->update_plugins_table('update');
				return;
			}

			if((time() > vartrue($_SESSION['nextPluginFolderScan'],0)) || $force == true)
			{
				e107::getPlugin()->update_plugins_table('update');
			}

			$_SESSION['nextPluginFolderScan'] = time() + PLUGIN_SCAN_INTERVAL;
			//echo "TIME = ".$_SESSION['nextPluginFolderScan'];

	    }

		// Modal Download.
		public function downloadPage()
		{

			$frm = e107::getForm();
			$mes = e107::getMessage();
			$tp = e107::getParser();

		//	print_a($_GET);

			$string =  base64_decode($_GET['src']);
			parse_str($string, $data);

			if(deftrue('e_DEBUG_MARKETPLACE'))
			{
				echo "<b>DEBUG MODE ACTIVE (no downloading)</b><br />";
				echo '$_GET[src]: ';
				print_a($_GET);

				echo 'base64 decoded and parsed as $data:';
				print_a($data);
				return false;
			}

			$pluginFolder = !empty($data['plugin_folder']) ? $tp->filter($data['plugin_folder']) : '';
			$pluginUrl = !empty($data['plugin_url']) ? $tp->filter($data['plugin_url']) : '';
			$pluginID = !empty($data['plugin_id']) ? $tp->filter($data['plugin_id']) : '';
			$pluginMode = !empty($data['plugin_mode']) ? $tp->filter($data['plugin_mode']) : '';

			if(!empty($data['plugin_price']))
			{
				e107::getRedirect()->go($pluginUrl);
				return true;
			}

			$mp = $this->getMarketplace();
		//	$mp->generateAuthKey($e107SiteUsername, $e107SiteUserpass);



			// Server flush useless. It's ajax ready state 4, we can't flush (sadly) before that (at least not for all browsers)
		    $mes->addSuccess(EPL_ADLAN_94);

			if($mp->download($pluginID, $pluginMode, 'plugin'))
			{
				$this -> pluginCheck(true); // rescan the plugin directory
				$text = e107::getPlugin()->install($pluginFolder);



				$mes->addInfo($text);

				$upgradable =  e107::getPlug()->getUpgradableList();
				if(!empty($upgradable[$pluginFolder]))
				{
					$mes->addSuccess("<a target='_top' href='".e_ADMIN."plugin.php?mode=installed&action=upgrade&id=".$pluginFolder."' class='btn btn-primary'>".LAN_UPDATE."</a>");
				}

				echo $mes->render('default', 'success');
			}
			else
			{
				// Unable to continue
				echo $mes->addError(EPL_ADLAN_95)->render('default', 'error');
			}

			echo $mes->render('default', 'debug');
			return null;



		}

        public function ListObserver()
        {
      //    parent::ListObserver();

	        $this->setPlugData();
        }



		/**
		 * @return e_marketplace|null
		 */
		public function getMarketplace()
		{
			if(null === $this->mp)
			{
				$this->mp = new e_marketplace(); // autodetect the best method
			}

			return $this->mp;
		}



		private function compatibilityLabel($val='')
		{
			$badge = (vartrue($val) > 1.9) ? "<span class='label label-warning'>".EPL_ADLAN_88."</span>" : '1.x';
			return $badge;
		}



	function options($data)
	{


		/*
		if(!e107::getFile()->hasAuthKey())
		{
		//	return "<a href='".e_SELF."' class='btn btn-primary e-modal' >Download and Install</a>";

		}
		*/
		if($data['plugin_installflag'])
		{
			return null;
		}



		//$url = e_SELF."?src=".base64_encode($d);
	//	$url = e_SELF.'?action=download&amp;src='.base64_encode($d);//$url.'&amp;action=download';
		$id = 'plug_'.$data['plugin_id'];
		//<button type='button' data-target='{$id}' data-loading='".e_IMAGE."/generic/loading_32.gif' class='btn btn-primary e-ajax middle' value='Download and Install' data-src='".$url."' ><span>Download and Install</span></button>
		$modalCaption = (!empty($data['plugin_price'])) ? EPL_ADLAN_92." ".$data['plugin_name']." ".$data['plugin_version'] : EPL_ADLAN_230." ".$data['plugin_name']." ".$data['plugin_version'];

		$srcData = array(
			'plugin_id'     => $data['plugin_id'],
			'plugin_folder' => $data['plugin_folder'],
			'plugin_price'  => $data['plugin_price'],
			'plugin_mode'   => $data['plugin_mode'],
			'plugin_url'    => $data['plugin_url'],
		);


		$url = $this->getMarketplace()->getDownloadModal('plugin',  $srcData);


	//	$d = http_build_query($srcData,false,'&');
	//	$url = e_SELF.'?mode=download&src='.base64_encode($d);
		$dicon = '<a title="'.EPL_ADLAN_237.'" class="e-modal btn btn-default btn-secondary" href="'.$url.'" rel="external" data-loading="'.e_IMAGE.'/generic/loading_32.gif"  data-cache="false" data-modal-caption="'.$modalCaption.'"  target="_blank" >'.ADMIN_INSTALLPLUGIN_ICON.'</a>';

		/*

		// DEBUGGER .
		$base64 = base64_encode($d);
		$tmp = base64_decode($base64);
		parse_str($tmp, $data);

	//  XXX Suhosin has a 512 char limit for $_GET strings.
		e107::getDebug()->log($data['plugin_name'].' : '.strlen($base64)."<br />".print_a($data,true)); //FIXME - enable when needed to debug.
		*/

		// Temporary Pop-up version.
	//	$dicon = '<a class="e-modal" href="'.$data['plugin_url'].'" rel="external" data-modal-caption="'.$data['plugin_name']." ".$data['plugin_version'].'"  target="_blank" ><img class="top" src="'.e_IMAGE_ABS.'icons/download_32.png" alt=""  /></a>';

	//	$dicon = "<a data-toggle='modal' data-modal-caption=\"Downloading ".$data['plugin_name']." ".$data['plugin_version']."\" href='{$url}' data-cache='false' data-target='#uiModal' title='".$LAN_DOWNLOAD."' ><img class='top' src='".e_IMAGE_ABS."icons/download_32.png' alt=''  /></a> ";

		return "<div id='{$id}' class='center' >
		{$dicon}
		</div>";
	}

		private function setPlugData()
		{

			//	$this->setTreeModel();

			/*   $tree = $this->getTreeModel();

				$plg = e107::getPlug();

	            foreach ($tree->getTree() as $id => $model)
	            {
					$path = $model->get('plugin_path');

					$plg->load($path);

		            $model->set('plugin_name',$plg->getName());
		            $model->set('plugin_date',$plg->getDate());
		            $model->set('plugin_author',$plg->getAuthor());
		            $model->set('plugin_compatible',$plg->getCompat());
		            $model->set('plugin_admin_url',$plg->getAdminUrl());
		            $model->set('plugin_description',$plg->getDescription());
		            $model->set('plugin_version_file',$plg->getVersion());
		            $model->set('plugin_install_required',$plg->getInstallRequired());
	                $model->set('plugin_icon',$plg->getIcon(32, 'path'));

	            }*/

		}


		public function listPage()
		{


			global $plugin, $e107SiteUsername, $e107SiteUserpass;
			$tp = e107::getParser();
			$frm = $this->getUI();

			$caption	= EPL_ADLAN_89;

			$e107 = e107::getInstance();
			$xml = e107::getXml();
			$mes = e107::getMessage();

		//	$mes->addWarning("Some older plugins may produce unpredictable results.");
			// check for cURL
			if(!function_exists('curl_init'))
			{
				$mes->addWarning(EPL_ADLAN_90);
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
				'params' => array('limit' => $this->perPage, 'search' => $srch, 'from' => $from)
			));
			$total = $xdata['params']['count'];

			// OLD BIT OF CODE ------------------------------->
		/*
		//	$file = SITEURLBASE.e_PLUGIN_ABS."release/release.php";  // temporary testing
			$file = "http://e107.org/feed?type=plugin&frm=".$from."&srch=".$srch."&limit=10";

			$xml->setOptArrayTags('plugin'); // make sure 'plugin' tag always returns an array
			$xdata = $xml->loadXMLfile($file,'advanced');

			$total = $xdata['@attributes']['total'];

			echo 'file='.$file;
		//	print_a($xdata);

			$xdata['data'] = $xdata['plugin'];
			*/
			// OLD BIT OF CODE END ------------------------------->


	// print_a($xdata);

			$c = 1;
			foreach($xdata['data'] as $row)
			{
				//$row = $r['@attributes'];

				//	print_a($row);

					$badge 		= $this->compatibilityLabel($row['compatibility']);;
					$featured 	= ($row['featured']== 1) ? " <span class='label label-info'>".EPL_ADLAN_91."</span>" : '';
					$price 		= (!empty($row['price'])) ? "<span class='label label-primary'>".$row['price']." ".$row['currency']."</span>" : "<span class='label label-success'>".EPL_ADLAN_93."</span>";

					$data[] = array(
						'plugin_id'				=> $row['params']['id'],
						'plugin_mode'			=> $row['params']['mode'],
						'plugin_icon'			=> vartrue($row['icon'],'e-plugins-32'),
						'plugin_name'			=> stripslashes($row['name']),
						'plugin_featured'		=> $featured,
						'plugin_sef'			=> '',
						'plugin_folder'			=> $row['folder'],
						'plugin_path'			=> $row['folder'],
						'plugin_date'			=> vartrue($row['date']),
						'plugin_category'		=> vartrue($row['category'], 'n/a'),
						'plugin_author'			=> vartrue($row['author']),
						'plugin_version'		=> $row['version'],
						'plugin_description'	=> nl2br(vartrue($row['description'])),
						'plugin_compatible'		=> $badge,

						'plugin_website'		=> vartrue($row['authorUrl']),
						'plugin_url'			=> $row['urlView'],
						'plugin_notes'			=> '',
						'plugin_price'			=> $row['price'],
						'plugin_license'		=> $price,
						'plugin_installflag'    => e107::isInstalled($row['folder'])
					);

				$c++;
			}

			$fieldList = $this->fields;
			unset($fieldList['checkboxes']);

			$text = "
				<form class='form-search form-inline' action='".e_SELF."?".e_QUERY."' id='core-plugin-list-form' method='get'>
				<div id='admin-ui-list-filter' class='e-search '>".$frm->search('srch', $srch, 'go', $filterName, $filterArray, $filterVal).$frm->hidden('mode','online')."
				</div>
				</form>

				<form action='".e_SELF."?".e_QUERY."' id='core-plugin-list-form' method='post'>
					<fieldset class='e-filter' id='core-plugin-list'>
						<legend class='e-hideme'>".$caption."</legend>





						<table id=core-plugin-list' class='table adminlist table-striped'>
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
					if(!in_array($v,$this->fieldpref) || $v == 'checkboxes' || $v === 'options')
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

			if($total > $this->perPage)
			{
				$parms = $total.",".$this->perPage.",".$from.",".e_SELF.'?mode=online&amp;action=list&amp;frm=[FROM]';

				if(!empty($srch))
				{
					$parms .= '&amp;srch='.$srch;
				}

				$text .= "<div class='control-group form-inline input-inline' style='text-align:center;margin-top:10px'>".$tp->parseTemplate("{NEXTPREV=$parms}",TRUE)."</div>";
			}

			return $text;

		}




		// ------- Customize Create --------

		public function beforeCreate($new_data,$old_data)
		{
			return $new_data;
		}

		public function afterCreate($new_data, $old_data, $id)
		{
			// do something
		}

		public function onCreateError($new_data, $old_data)
		{
			// do something
		}


		// ------- Customize Update --------

		public function beforeUpdate($new_data, $old_data, $id)
		{
			return $new_data;
		}

		public function afterUpdate($new_data, $old_data, $id)
		{
			// do something
		}

		public function onUpdateError($new_data, $old_data, $id)
		{
			// do something
		}


	/*
		// optional - a custom page.
		public function customPage()
		{
			$text = 'Hello World!';
			$otherField  = $this->getController()->getFieldVar('other_field_name');
			return $text;

		}
	*/

}



class plugin_form_online_ui extends e_admin_form_ui
{


	// Custom Method/Function
	function plugin_name($curVal,$mode)
	{
		$frm = e107::getForm();

		switch($mode)
		{
			case 'read': // List Page
				return $curVal;
			break;

			case 'write': // Edit Page
				return $frm->text('plugin_name',$curVal, 255, 'size=large');
			break;

			case 'filter':
			case 'batch':
				return  array();
			break;
		}
	}


	// Custom Method/Function
	function plugin_addons($curVal,$mode)
	{
		$frm = e107::getForm();

		switch($mode)
		{
			case 'read': // List Page
				return $curVal;
			break;

			case 'write': // Edit Page
				return $frm->text('plugin_addons',$curVal, 255, 'size=large');
			break;

			case 'filter':
			case 'batch':
				return  array();
			break;
		}
	}



	// Custom Method/Function
	function plugin_compatible($curVal,$mode)
	{
		$frm = e107::getForm();

		switch($mode)
		{
			case 'read': // List Page

				if(intval($curVal) > 1)
				{
					return "<span class='label label-warning'>".$curVal."</span>";
				}

				return $curVal;
			break;

			case 'write': // Edit Page
				return $frm->text('plugin_name',$curVal, 255, 'size=large');
			break;

			case 'filter':
			case 'batch':
				return  array();
			break;
		}
	}


	function options($data)
	{
return null;
	}

}





class pluginLanguage extends e_admin_ui
{

	private $scriptFiles 	= array();
	private $lanFiles 		= array();

	private $lanDefs 		= array();
	private $scriptDefs 	= array();

	private $lanDefsData 	= array();
	private $scriptDefsData = array();

	private $unused			= array();
	private $unsure			= array();

	private $excludeLans 	= array('CORE_LC', 'CORE_LC2', 'e_LAN', 'e_LANGUAGE', 'e_LANGUAGEDIR', 'LAN', 'LANGUAGE');

	private $useSimilar		= false;


	function listPage()
	{
		if(!empty($_GET['newplugin']) && $_GET['step']==2)
		{
				$plugin = e107::getParser()->filter($_GET['newplugin'],'file');
				return $this->step2($plugin);

		}

	}





	function step2($path)
	{
			$this->plugin = $path;

			$fl = e107::getFile();

			$files = $fl->get_files(e_PLUGIN.$path.'/languages',null,null,3);
			$files2 = $fl->get_files(e_PLUGIN.$path,'\.php|\.sc|\.bb|\.xml','languages',3);

			$this->scanLanFile(e_LANGUAGEDIR."English/English.php");
			$this->scanLanFile(e_LANGUAGEDIR."English/admin/lan_admin.php");

			foreach($files as $v)
			{
				if(strpos($v['path'],'English')!==false OR strpos($v['fname'],'English')!==false)
				{
					$path = $v['path'].$v['fname'];
					$this->lanFiles[] = $path;

					$this->scanLanFile($path);
				}
			}

			foreach($files2 as $v)
			{
				$path = $v['path'].$v['fname'];
				$this->scriptFiles[] = 	$path;
				$this->scanScriptFile($path);
			}


			return $this->renderResults();

	}


		function findSimilar($data)
		{
			$sim = array();

			foreach($this->lanDefsData as $k=>$v)
			{
				if(empty($v['value']))
				{
					continue;
				}

				if($this->useSimilar == true)
				{
					similar_text($v['value'], $data['value'], $percentSimilar);
				}
				else
				{
					$percentSimilar = 0;
				}

				if((($v['value'] == $data['value'] || $percentSimilar > 89) && $data['file'] != $v['file']))
				{
					if(strpos($v['lan'],'LAN')===false) // Defined constants that don't contain 'LAN'.
					{
						$v['status'] = 2;
					}
					else
					{
						$v['status'] = (in_array($v['lan'],$this->used)) ? 1 : 0;
					}

					$sim[] = $v;

				}
			}



			return $sim;

		}


		function renderSimilar($data,$mode='')
		{

			$sim = $this->findSimilar($data);


			if(empty($sim) || ($mode == 'script' && count($sim) < 2))
			{
				return; //  ADMIN_TRUE_ICON;
			}

			$text = "<table class='table table-striped table-bordered'>
			";

			foreach($sim as $k=>$val)
			{
				$text .= "<tr>
				<td style='width:30%'>".$this->shortPath($val['file'])."</td>
				<td style='width:45%'>".$val['lan']."<br /><small>".$val['value']."</small></td>
				<td style='width:25%'>".$this->renderStatus($val['status'])."</td>
				</tr>";

			}

			$text .= "</table>";
			return $text;

		}

		function renderFilesList($list)
		{
			$l= array();
			foreach($list as $v)
			{
				$l[] = $this->shortPath($v,'script');


			}

			if(!empty($l))
			{
				return implode("<br />",$l);
			}


		}

		function renderStatus($val,$mode='lan')
		{
			$diz = array(
				'lan'		=> array(0 => 'Unused by '.$this->plugin, 1=>'Used by '.$this->plugin, 2=>'Unsure'),
				'script'	=> array(0=> 'Missing from Language Files', 1=>'Found in Language Files', 3=>"Generic")
			);



			if($val ==1)
			{
				return "<span class='label label-success'>".$diz[$mode][$val]."</span>";
			}

			if($val == 2)
			{
				return "<span class='label label-warning'>".$diz[$mode][$val]."</span>";
			}

			return "<span class='label label-important label-danger'>".$diz[$mode][$val]."</span>";
		}

		function shortPath($path,$mode='lan')
		{

			if($path == e_LANGUAGEDIR.'English/English.php')
			{
				return "<i>Core Frontend Language File</i>";
			}

			if($path == e_LANGUAGEDIR.'English/admin/lan_admin.php')
			{
				return "<i>Core Admin-area Language File</i>";
			}

			if($mode == 'script')
			{
				return str_replace(e_PLUGIN.$this->plugin.'/','',$path);
			}
			else
			{

				$text = str_replace(e_PLUGIN.$this->plugin.'/languages/','',$path);

				if(strpos($path,'_front.php')===false && strpos($path,'_admin.php')===false && strpos($path,'_global.php')===false && strpos($path,'_menu.php')===false && strpos($path,'_notify.php')===false && strpos($path,'_search.php')===false)
				{
					return "<span class='text-error e-tip' title='File name should be either English_front.php, English_admin.php or English_global.php'>".$text."</span>";
				}

				return $text;

			}

		}


		function renderTable($array,$mode)
		{
			if(empty($array))
			{
				return "<div class='alert alert-info alert-block'>No Matches</div>";
			}

			$text2 = '';

			if($mode == 'unsure')
			{
				$text2 .= "<div class='alert alert-info alert-block'>LAN items in this list have been named incorrectly. They should include 'LAN' in their name. eg. LAN_".strtoupper($this->plugin)."_001</div>";

			}

			$text2 .= "<table class='table table-striped  table-bordered'>
			<tr>
			<th>LAN</th>
			<th>File</th>
			<th>Value</th>
			<th>Duplicate or Similar Value</th>
			</tr>
			";

			foreach($array as $k=>$v)
			{
				$text2 .= "<tr>
					<td style='width:5%'>".$v."</td>
					<td>".$this->shortPath($this->lanDefsData[$k]['file'])."</td>
					<td style='width:20%'>".$this->lanDefsData[$k]['value']."</td>
					<td>".$this->renderSimilar($this->lanDefsData[$k])."</td>
					</tr>";

			}


			$text2 .= "</table>";

			return $text2;
		}

		function renderScriptTable()
		{

		//	return print_a($this->scriptDefsData,true);

			$text2 = "<table class='table table-striped table-bordered'>
			<tr>
			<th>id</th>
			<th>File</th>
			<th>Detected LAN</th>
			<th>LAN Value</th>
			<th class='right'>Found on Line</th>
			<th style='width:10%'>Status</th>
			<th>Duplicates / Possible Substitions</th>
			</tr>
			";

			foreach($this->scriptDefsData as $k=>$v)
			{
				$status = in_array($v['lan'],$this->lanDefs) ? 1 : 0;
			//	$lan = $v['lan'];
			//	$v['value'] = $this->lanDefsRaw[$lan];
			//	$sim = $this->findSimilar($v);

				$text2 .= "<tr>
					<td style='width:5%'>".$k."</td>
					<td>".$this->shortPath($v['file'],'script')."</td>
					<td >".$v['lan']."</td>
					<td ><small>".$this->lanDefsRaw[$v['lan']]."</small></td>
					<td class='right'>".$v['line']."</td>
					<td>".$this->renderStatus($status,'script')."</td>
					<td>".$this->renderSimilar($v,'script')."</td> 
					</tr>";

			}


			$text2 .= "</table>";

			return $text2;

		}


		function renderResults()
		{
			$frm = e107::getForm();
			$ns = e107::getRender();

			$this->unused = array_diff($this->lanDefs,$this->scriptDefs);

			$this->used = array_intersect($this->lanDefs,$this->scriptDefs);

			foreach($this->unused as $k=>$v)
			{
				if(strpos($v,'LAN')===false)
				{
					unset($this->unused[$k]);
					$this->unsure[$k] = $v;
				}

				if(strpos($this->lanDefsData[$k]['file'],$this->plugin) === false || in_array($v,$this->excludeLans))
				{
					unset($this->unused[$k]);
					unset($this->unsure[$k]);
				}


			}

//			print_a($this->scriptData);

			$used =  $this->renderTable($this->used, 'used');
			$unused =  $this->renderTable($this->unused,'unused');
			$unsure =  $this->renderTable($this->unsure,'unsure');


			// echo $text2;
			$tabs = array (
				0	=> array('caption'=>EPL_ADLAN_222, 'text'=> $this->renderScriptTable()),
				1 => array('caption'=>EPL_ADLAN_223, 'text'=>$used),
				2 => array('caption'=>EPL_ADLAN_224, 'text'=>$unused),
				3 => array('caption'=>EPL_ADLAN_225, 'text'=>$unsure),


			);


			$this->addTitle(ADLAN_98.SEP.EPL_ADLAN_114.SEP.EPL_ADLAN_221.SEP.$this->plugin);

			$text = "<div class='center'><a class='btn btn-default' href='".e_ADMIN_ABS."plugin.php?mode=create&action=build'>".LAN_BACK."</a></div>";

			return  $frm->tabs($tabs).$text;

			//$ns->tablerender(ADLAN_98.SEP.EPL_ADLAN_114.SEP.EPL_ADLAN_221.SEP.$this->plugin, $frm->tabs($tabs));

		}






		function scanScriptFile($path)
		{
			$lines = file($path, FILE_IGNORE_NEW_LINES);

			foreach($lines as $ln=>$row)
			{
				$row = trim($row);
				if(substr($row,0,2) == '/*')
				{
				//	$skip =true; ;

				}
				if(substr($row,0,2) == '*/')
				{
				//	$skip =false;
				//	continue;
				}

				if(empty($row) || $skip == true || substr($row,0,5) == '<?php' || substr($row,0,2) == '?>' || substr($row,0,2)=='//')
				{
					continue;
				}

				if(preg_match_all("/([\w_]*LAN[\w_]*)/", $row, $match))
				{
					foreach($match[1] as $lan)
					{
						if(!in_array($lan,$this->excludeLans))
						{
							$this->scriptDefs[] = $lan;
							$this->scriptDefsData[] = array('file'=>$path, 'line'=>$ln, 'lan'=>$lan, 'value'=>$this->lanDefsRaw[$lan]);
						//	$this->scriptData[$path][$ln] = $row;
						}
					}
				}
			}


		}


		function scanLanFile($path)
		{


			$data = file_get_contents($path);

			if(preg_match_all('/(\/\*[\s\S]*?\*\/)/i',$data, $multiComment))
			{
				$data = str_replace($multiComment[1],'',$data);	// strip multi-line comments.
			}


			$type = basename($path);

			if(preg_match_all('/^\s*?define\s*?\(\s*?(\'|\")([\w]+)(\'|\")\s*?,\s*?(\'|\")([\s\S]*?)\s*?(\'|\")\s*?\)\s*?;/im',$data,$matches))
			{
				$def = $matches[2];
				$values = $matches[5];

				foreach($def as $k=>$d)
				{
					if($d == 'e_PAGETITLE' || $d == 'PAGE_NAME' || $d =='CORE_LC' && $d =='CORE_LC2')
					{
							continue;
					}

					$retloc[$type][$d]= $values[$k];
					$this->lanDefs[] = $d;
					$this->lanDefsData[] = array('file'=>$path, 'lan'=>$d, 'value'=>$values[$k]);
					$this->lanDefsRaw[$d] = $values[$k];
				}
			}

		//print_a($this->lanDefsData);
			return;
		}






}














//if(deftrue('e_DEBUG_PLUGMANAGER'))
{
	new plugman_adminArea();
	require_once(e_ADMIN."auth.php");
	e107::getAdminUI()->runPage();
	require_once(e_ADMIN."footer.php");
	exit;

}

























// --------------------------------------

/*
class pluginmanager_form extends e_form
{
	
	var $plug;
	var $plug_vars;
		
	//FIXME _ there's a problem with calling this. 
	function plugin_website($parms, $value, $id, $attributes)
	{
		return (varset($plugURL, false)) ? "<a href='{$plugURL}' title='{$plugURL}' >".ADMIN_URL_ICON."</a>" : "";	
		
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
			$conf_title = LAN_CONFIGURE.' '.$tp->toHTML($this->plug_vars['@attributes']['name'], "", "defs,emotes_off, no_make_clickable");
			$plugin_icon = "<a title='{$conf_title}' href='{$conf_file}' >".$plugin_icon."</a>";
			$plugin_config_icon = "<a class='btn btn-default' title='{$conf_title}' href='{$conf_file}' >".ADMIN_CONFIGURE_ICON."</a>";
		}
				
		$text = "<div class='btn-group'>";
		
		$text .= vartrue($plugin_config_icon);
		
		if ($this->plug_vars['@attributes']['installRequired'])
		{
			
			if ($this->plug['plugin_installflag'])
			{
		  		$text .= ($this->plug['plugin_installflag'] ? "<a class='btn btn-default' href=\"".e_SELF."?uninstall.{$this->plug['plugin_id']}\" title='".EPL_ADLAN_1."'  >".ADMIN_UNINSTALLPLUGIN_ICON."</a>" : "<a class='btn' href=\"".e_SELF."?install.{$this->plug['plugin_id']}\" title='".EPL_ADLAN_0."' >".ADMIN_INSTALLPLUGIN_ICON."</a>");
                           //   $text .= ($this->plug['plugin_installflag'] ? "<button type='button' class='delete' value='no-value' onclick=\"location.href='".e_SELF."?uninstall.{$this->plug['plugin_id']}'\"><span>".EPL_ADLAN_1."</span></button>" : "<button type='button' class='update' value='no-value' onclick=\"location.href='".e_SELF."?install.{$this->plug['plugin_id']}'\"><span>".EPL_ADLAN_0."</span></button>");
				if (e_DEBUG && !vartrue($this->plug_vars['plugin_php']))
				{
			//		$text .= "<br /><br /><input type='button' class='btn btn-default button' onclick=\"location.href='".e_SELF."?refresh.{$this->plug['plugin_id']}'\" title='".'Refresh plugin settings'."' value='".'Refresh plugin settings'."' /> ";
				}
			}
			else
			{
			  //	$text .=  "<input type='button' class='btn' onclick=\"location.href='".e_SELF."?install.{$this->plug['plugin_id']}'\" title='".EPL_ADLAN_0."' value='".EPL_ADLAN_0."' />";
			  //	$text .= "<button type='button' class='update' value='no-value' onclick=\"location.href='".e_SELF."?install.{$this->plug['plugin_id']}'\"><span>".EPL_ADLAN_0."</span></button>";
	           	$text .= "<a class='btn btn-default' href=\"".e_SELF."?install.{$this->plug['plugin_id']}\" title='".EPL_ADLAN_0."' >".ADMIN_INSTALLPLUGIN_ICON."</a>";
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
					e107::getDb()->delete('plugin', "plugin_installflag=0 AND (plugin_path='{$this->plug['plugin_path']}' OR plugin_path='{$this->plug['plugin_path']}/' )  ");
				}
			}
		}

		if ($this->plug['plugin_version'] != $this->plug_vars['@attributes']['version'] && $this->plug['plugin_installflag'])
		{
		  //	$text .= "<br /><input type='button' class='btn' onclick=\"location.href='".e_SELF."?upgrade.{$this->plug['plugin_id']}'\" title='".EPL_UPGRADE." to v".$this->plug_vars['@attributes']['version']."' value='".EPL_UPGRADE."' />";
		    e107::getMessage()->addInfo("<b>".$tp->toHtml($this->plug['plugin_name'],false,'TITLE')."</b> is ready to be upgraded. (see below)"); // TODO LAN
			$text .= "<a class='btn btn-default' href='".e_SELF."?upgrade.{$this->plug['plugin_id']}' title=\"".EPL_UPGRADE." v".$this->plug_vars['@attributes']['version']."\" >".ADMIN_UPGRADEPLUGIN_ICON."</a>";
		}

		if ($this->plug['plugin_installflag'] && e_DEBUG == true)
		{
				$text .= "<a class='btn btn-default' href='".e_SELF."?repair.".$this->plug['plugin_id']."' title='".LAN_REPAIR_PLUGIN_SETTINGS."'> ".ADMIN_REPAIRPLUGIN_ICON."</a>";
		}

		if($this->plug['plugin_installflag'] && is_dir($_path.".git"))
		{
			$text .=  "<a class='plugin-manager btn btn-default' href='".e_SELF."?pull.".$this->plug['plugin_id']."' title='".LAN_SYNC_WITH_GIT_REPO."'> ".ADMIN_GITSYNC_ICON."</a>";
		}


		$text .="</div>	";
				
		return $text;
	}	


	
}
*/
/*
require_once("auth.php");
$pman->pluginObserver();
$mes = e107::getMessage();
$frm = e107::getForm();*/

require_once("footer.php");
exit;


// FIXME switch to admin UI
/*
class pluginManager{

	var $plugArray;
	var $action;
	var $id;
	var $frm;
	var $fieldpref;
	var $titlearray 		= array();
	var $pagetitle;
	

	var $mp;
		
	protected $pid = 'plugin_id';
	
	protected $fields = array(

		   		"checkboxes"			=> array("title" => "", 'type'=>null, "forced"=>TRUE, "width"=>"3%", 'thclass'=>'center','class'=>'center'),
				"plugin_icon"			=> array("title" => EPL_ADLAN_82, "type"=>"icon", "width" => "5%", "thclass" => "middle center",'class'=>'center', "url" => ""),
				"plugin_name"			=> array("title" => EPL_ADLAN_10, 'forced'=>true, "type"=>"text", "width" => "auto", 'class'=>'left', "thclass" => "middle", "url" => ""),
 				"plugin_version"		=> array("title" => EPL_ADLAN_11, "type"=>"numeric", "width" => "5%", "thclass" => "middle", "url" => ""),
    			"plugin_date"			=> array("title" => LAN_RELEASED, 	"type"=>"text", "width" => "8%", "thclass" => "middle"),
    			
    			"plugin_folder"			=> array("title" => EPL_ADLAN_64, "type"=>"text", "width" => "10%", "thclass" => "middle"),
				"plugin_category"		=> array("title" => LAN_CATEGORY, "type"=>"text", "width" => "auto", "thclass" => "middle"),
                "plugin_author"			=> array("title" => LAN_AUTHOR, "type"=>"text", "width" => "10%", "thclass" => "middle"),
                "plugin_license"		=> array("title" => "License", 	 'nolist'=>true,	"forced"=>true, "type"=>"text", "width" => "5%", "thclass" => "left"),	
  		//		"plugin_price"			=> array("title" => "Price", 	 'nolist'=>true,	"forced"=>true, "type"=>"text", "width" => "5%", "thclass" => "left"),	
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

		$qry = str_replace('XDEBUG_PROFILE', '', e_QUERY);

        $tmp = explode('.',$qry);

	  	$this -> action     = ($tmp[0]) ? $tmp[0] : "installed";
		$this -> id         = !empty($tmp[1]) ? intval($tmp[1]) : "";
		$this -> titlearray = array('installed'=>EPL_ADLAN_22,'avail'=>EPL_ADLAN_23, 'upload'=>EPL_ADLAN_38);
		
		if(isset($_GET['mode']))
		{
			$this->action = $_GET['mode'];
		}

		if($this->action == 'online')
		{
		//	$this->fields["plugin_price"]['nolist'] = false; //  = array("title" => "Price", "forced"=>true, "type"=>"text", "width" => "5%", "thclass" => "middle center");		
			$this->fields["plugin_license"]['nolist'] = false; 
		}

        $keys = array_keys($this -> titlearray);
		$this->pagetitle = (in_array($this->action,$keys)) ? $this -> titlearray[$this->action] : $this -> titlearray['installed'];



    }


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
		$tp = e107::getParser();

        global $user_pref,$admin_log;
        
    	if (isset($_POST['upload']))
		{
        	$this -> pluginProcessUpload();
			$this->action = 'avail'; 
		}

        if(isset($_POST['etrigger_ecolumns']))
		{
			$user_pref['admin_pluginmanager_columns'] = $tp->filter($_POST['e-columns']);
			save_prefs('user');
		}

		$user_pref['admin_pluginmanager_columns'] = false;
		
		$this -> fieldpref = (vartrue($user_pref['admin_pluginmanager_columns'])) ? $user_pref['admin_pluginmanager_columns'] : array("plugin_icon","plugin_name","plugin_version","plugin_date","plugin_description","plugin_category","plugin_compatible","plugin_author","plugin_website","plugin_notes");


		foreach($this->fields as $key=>$val)
		{
			if(vartrue($val['forced']) && substr($key,0,6)=='plugin')
			{
				$this->fieldpref[] = $key;	
			}		
		}
		
		if($this->action == 'download')
		{
			$this->pluginDownload();
			return; 	
			
		}


		if($this->action == 'pull' && !empty($this->id))
		{
			$info = e107::getPlugin()->getinfo($this->id);

			if(!empty($info['plugin_path']))
			{
				$return = e107::getFile()->gitPull($info['plugin_path'], 'plugin');
				e107::getMessage()->addSuccess($return);
				$this->action = 'refresh';
			}
			else
			{
				$this->action = 'avail';
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



		if($this->action == "repair")
		{
        	$this -> pluginRepair();
        	$this->action = 'refresh';
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
		
		if($this->action == 'lans')
		{
			$pc = new pluginLanguage;
			return;
				
		}

		if($this->action == "upgrade")
		{
        	$this -> pluginUpgrade();
      		$this -> action = "installed";
		}



		if($this->action == "upload")
		{
        	$this -> pluginUpload();
		}
		
		if($this->action == "online")
		{
        	$text = $this -> pluginOnline();
        	$mes = e107::getMessage();
        	e107::getRender()->tablerender(ADLAN_98.SEP.$caption, $mes->render(). $text);
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
		$badge = (vartrue($val) > 1.9) ? "<span class='label label-warning'>".EPL_ADLAN_88."</span>" : '1.x';
		return $badge;	
	}
	
	
	
	function pluginOnline()
	{
		global $plugin, $e107SiteUsername, $e107SiteUserpass;
		$tp = e107::getParser();
		$frm = e107::getForm();
		
		$caption	= EPL_ADLAN_89;
		
		$e107 = e107::getInstance();
		$xml = e107::getXml();
		$mes = e107::getMessage();
		
	//	$mes->addWarning("Some older plugins may produce unpredictable results.");
		// check for cURL
		if(!function_exists('curl_init'))
		{
			$mes->addWarning(EPL_ADLAN_90);
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

		// OLD BIT OF CODE END ------------------------------->
		
		
// print_a($xdata);
		 
		$c = 1;
		foreach($xdata['data'] as $row)
		{
			//$row = $r['@attributes'];
			
			//	print_a($row);
			
				$badge 		= $this->compatibilityLabel($row['compatibility']);;
				$featured 	= ($row['featured']== 1) ? " <span class='label label-info'>".EPL_ADLAN_91."</span>" : '';
				$price 		= (!empty($row['price'])) ? "<span class='label label-primary'>".$row['price']." ".$row['currency']."</span>" : "<span class='label label-success'>".EPL_ADLAN_93."</span>";
			
				$data[] = array(
					'plugin_id'				=> $row['params']['id'],
					'plugin_mode'			=> $row['params']['mode'],
					'plugin_icon'			=> vartrue($row['icon'],'e-plugins-32'),
					'plugin_name'			=> stripslashes($row['name']),
					'plugin_featured'		=> $featured,
					'plugin_sef'			=> '',
					'plugin_folder'			=> $row['folder'],
					'plugin_date'			=> vartrue($row['date']),
					'plugin_category'		=> vartrue($row['category'], 'n/a'),
					'plugin_author'			=> vartrue($row['author']),
					'plugin_version'		=> $row['version'],
					'plugin_description'	=> nl2br(vartrue($row['description'])),
					'plugin_compatible'		=> $badge,
				
					'plugin_website'		=> vartrue($row['authorUrl']),
					'plugin_url'			=> $row['urlView'],
					'plugin_notes'			=> '',
					'plugin_price'			=> $row['price'],
					'plugin_license'		=> $price
				);	
				
			$c++;
		}

		$fieldList = $this->fields;
		unset($fieldList['checkboxes']);

		$text = "
			<form class='form-search form-inline' action='".e_SELF."?".e_QUERY."' id='core-plugin-list-form' method='get'>
			<div id='admin-ui-list-filter' class='e-search '>".$frm->search('srch', $srch, 'go', $filterName, $filterArray, $filterVal).$frm->hidden('mode','online')."
			</div>
			</form>
			
			<form action='".e_SELF."?".e_QUERY."' id='core-plugin-list-form' method='post'>
				<fieldset class='e-filter' id='core-plugin-list'>
					<legend class='e-hideme'>".$caption."</legend>
					
					
					
					
					
					<table id=core-plugin-list' class='table adminlist table-striped'>
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
			$text .= "<td class='right'>".$this->options($val)."</td>";
			$text .= "</tr>";		
			
		}
		
		
		$text .= "
						</tbody>
					</table>";
		$text .= "
				</fieldset>
			</form>
		";
		
		if($total > $this->perPage)
		{
			$parms = $total.",".$this->perPage.",".$from.",".e_SELF.'?mode=online&amp;frm=[FROM]';

			if(!empty($srch))
			{
				$parms .= '&amp;srch='.$srch;
			}

			$text .= "<div class='control-group form-inline input-inline' style='text-align:center;margin-top:10px'>".$tp->parseTemplate("{NEXTPREV=$parms}",TRUE)."</div>";
		}

		return $text;

	}
	
	
	
	function options($data)
	{
			
	//	print_a($data);

				

		//$url = e_SELF."?src=".base64_encode($d);
	//	$url = e_SELF.'?action=download&amp;src='.base64_encode($d);//$url.'&amp;action=download';
		$id = 'plug_'.$data['plugin_id'];
		//<button type='button' data-target='{$id}' data-loading='".e_IMAGE."/generic/loading_32.gif' class='btn btn-primary e-ajax middle' value='Download and Install' data-src='".$url."' ><span>Download and Install</span></button>
		$modalCaption = (!empty($data['plugin_price'])) ? EPL_ADLAN_92." ".$data['plugin_name']." ".$data['plugin_version'] : EPL_ADLAN_230." ".$data['plugin_name']." ".$data['plugin_version'];

		$srcData = array(
			'plugin_id'     => $data['plugin_id'],
			'plugin_folder' => $data['plugin_folder'],
			'plugin_price'  => $data['plugin_price'],
			'plugin_mode'   => $data['plugin_mode'],
			'plugin_url'    => $data['plugin_url'],
		);


		$d = http_build_query($srcData,false,'&');
		$url = e_SELF.'?mode=download&src='.base64_encode($d);
		$dicon = '<a title="'.EPL_ADLAN_237.'" class="e-modal btn btn-default" href="'.$url.'" rel="external" data-loading="'.e_IMAGE.'/generic/loading_32.gif"  data-cache="false" data-modal-caption="'.$modalCaption.'"  target="_blank" >'.ADMIN_INSTALLPLUGIN_ICON.'</a>';



		// Temporary Pop-up version.
	//	$dicon = '<a class="e-modal" href="'.$data['plugin_url'].'" rel="external" data-modal-caption="'.$data['plugin_name']." ".$data['plugin_version'].'"  target="_blank" ><img class="top" src="'.e_IMAGE_ABS.'icons/download_32.png" alt=""  /></a>';

	//	$dicon = "<a data-toggle='modal' data-modal-caption=\"Downloading ".$data['plugin_name']." ".$data['plugin_version']."\" href='{$url}' data-cache='false' data-target='#uiModal' title='".$LAN_DOWNLOAD."' ><img class='top' src='".e_IMAGE_ABS."icons/download_32.png' alt=''  /></a> ";

		return "<div id='{$id}' class='right' >
		{$dicon}
		</div>";				
	}



	private function pluginDownload()
	{
		define('e_IFRAME', true);
		$frm = e107::getForm();
		$mes = e107::getMessage();
		$tp = e107::getParser();
		
	//	print_a($_GET); 	
		
		$string =  base64_decode($_GET['src']);	
		parse_str($string, $data);

		if(deftrue('e_DEBUG_MARKETPLACE'))
		{
			echo "<b>DEBUG MODE ACTIVE (no downloading)</b><br />";
			echo '$_GET[src]: ';
			print_a($_GET);

			echo 'base64 decoded and parsed as $data:';
			print_a($data);
			return false;
		}

		$pluginFolder = !empty($data['plugin_folder']) ? $tp->filter($data['plugin_folder']) : '';
		$pluginUrl = !empty($data['plugin_url']) ? $tp->filter($data['plugin_url']) : '';
		$pluginID = !empty($data['plugin_id']) ? $tp->filter($data['plugin_id']) : '';
		$pluginMode = !empty($data['plugin_mode']) ? $tp->filter($data['plugin_mode']) : '';

		if(!empty($data['plugin_price']))
		{
			e107::getRedirect()->go($pluginUrl);
			return true;
		}

		$mp = $this->getMarketplace();
	//	$mp->generateAuthKey($e107SiteUsername, $e107SiteUserpass);
	

		
		// Server flush useless. It's ajax ready state 4, we can't flush (sadly) before that (at least not for all browsers) 
	 	$mes->addSuccess(EPL_ADLAN_94);

		if($mp->download($pluginID, $pluginMode, 'plugin'))
		{
			$this -> pluginCheck(true); // rescan the plugin directory
			$text = e107::getPlugin()->install($pluginFolder);

			$mes->addInfo($text); 
			echo $mes->render('default', 'success'); 
		}
		else
		{
			// Unable to continue
			echo $mes->addError(EPL_ADLAN_95)->render('default', 'error');
		}
		
		echo $mes->render('default', 'debug'); 
		return; 
		
		
		
		$text ="<iframe src='".$pluginUrl."' style='width:99%; height:500px; border:0px'>Loading...</iframe>";
	//	print_a($data); 
		$text .= $frm->open('upload-url-form','post');
		
		$text .= "<div class='form-inline' style='padding:20px'>";
		$text .= "<input type='text' name='upload_url' size='255' style='width:70%;height:50px;text-align:center' placeholder='".EPL_ADLAN_96."' />";
		$text .= $frm->admin_button('upload_remote_url',1,'create','Install');
	    $text .= "</div>";
		$text .= "</div>\n\n";
		
		$text .= $frm->close();
		echo $text; 
		
	}
	

	function pluginUninstall()
	{

		if(!isset($_POST['uninstall_confirm']))
		{	// $id is already an integer

			$this->pluginConfirmUninstall();
			return;
		}

		$post = e107::getParser()->filter($_POST);
		$text = e107::getPlugin()->uninstall($this->id, $post);
		$this->show_message($text, E_MESSAGE_SUCCESS);

		$this->action = 'installed';

		$log = e107::getPlugin()->getLog();
		e107::getDebug()->log($log);

		return;

   }





   function pluginProcessUpload()
   {
			if (!$_POST['ac'] == md5(ADMINPWCHANGE))
			{
				exit;
			}
			
			$fl = e107::getFile();
			$data = $fl->getUploaded(e_TEMP); 
			$mes = e107::getMessage();
			
			if(empty($data[0]['error']))
			{
				if($fl->unzipArchive($data[0]['name'],'plugin'))
				{
					$mes->addSuccess(EPL_ADLAN_43); 
				}
				else 
				{
					$mes->addError(EPL_ADLAN_97);
				}
			}
			
		//	$data = process_uploaded_files(e_TEMP);
		//	print_a($data); 
			
			echo $mes->render(); 
			
			return true;


   }


// -----------------------------------------------------------------------------
// TODO FIXME - This needs cleaning: e107::getMessage(), limit the globals, etc. 

   function pluginInstall()
   {
        global $plugin;
		$text = $plugin->install_plugin($this->id);
		
		$log = e107::getAdminLog();
			
			
			
		if ($text === FALSE)
		{ // Tidy this up
			$this->show_message(EPL_ADLAN_99, E_MESSAGE_ERROR);
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

		$text = '';

		$_path = e_PLUGIN.$plug['plugin_path'].'/';
		if(file_exists($_path.'plugin.xml'))
		{
			$plugin->install_plugin_xml($this->id, 'upgrade');
		}
		else
		{
			$eplug_folder = null;
			$upgrade_alter_tables = null;
			$upgrade_add_prefs = null;
			$upgrade_remove_prefs = null;
			$upgrade_add_array_pref = null;
			$upgrade_remove_array_pref = null;
			$eplug_version = null;



			include(e_PLUGIN.$plug['plugin_path'].'/plugin.php');

			$text = '';

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

			e107::getLog()->add('PLUGMAN_02', $name, E_LOG_INFORMATIVE, '');
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

   function pluginRepair()
   {
      // global $plug;

			$plug = e107::getSingleton('e107plugin')->getinfo($this->id);

			$_path = e_PLUGIN.$plug['plugin_path'].'/';
			if(file_exists($_path.'plugin.xml'))
			{
				// $text .= $plugin->install_plugin_xml($this->id, 'refresh');
				e107::getSingleton('e107plugin')->refresh($plug['plugin_path']);
				e107::getLog()->add('PLUGMAN_04', $this->id.':'.$plug['plugin_path'], E_LOG_INFORMATIVE, '');
			}

    }

// -----------------------------------------------------------------------------

		// Check for new plugins, create entry in plugin table ...
    function pluginCheck($force=false)
	{
		global $plugin;

		if(!PLUGIN_SCAN_INTERVAL)
		{
			$plugin->update_plugins_table('update');
			return;
		}
		
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


			if(!is_writable(e_PLUGIN))
			{
			   	$text = EPL_ADLAN_44;
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
		$mes = e107::getMessage();

		if($this->action == "" || $this->action == "installed")
		{
			$installed = $plugin->getall(1);

			$mp = $this->getMarketplace();

			$versions = $mp->getVersionList();

		//	print_a($versions);
			$caption = EPL_ADLAN_22;
			$pluginRenderPlugin = $this->pluginRenderPlugin($installed, $versions);
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
					<table class='table adminlist table-striped'>
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
			$text .= "<tr><td class='center' colspan='".count($this->fields)."'>";
 			$text .= str_replace("[x]", "<a href='".e_ADMIN."plugin.php?avail'>".EPL_ADLAN_100."</a>", EPL_ADLAN_101);
			$text .= "</td></tr>";
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

	function pluginRenderPlugin($pluginList, $versions = array())
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
						$conf_title = LAN_CONFIGURE.' '.$tp->toHTML($plug_vars['@attributes']['name'], "", "defs,emotes_off, no_make_clickable");
					//	$plugin_icon = "<a title='{$conf_title}' href='{$conf_file}' >".$plugin_icon."</a>";
						$plugin_config_icon = "<a class='btn btn-default' title='{$conf_title}' href='{$conf_file}' >".ADMIN_CONFIGURE_ICON."</a>";
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
				
					'plugin_website'		=> vartrue($plug['authorUrl']),
			//		'plugin_url'			=> vartrue($plugURL), // ; //  ? "<a href='{$plugURL}' title='{$plugURL}' >".ADMIN_URL_ICON."</a>" : "",
					'plugin_notes'			=> ''
					);	


					$pgf->plug_vars = $plug_vars;
					$pgf->plug		= $plug;
					$text 			.= $pgf->renderTableRow($this->fields, $this->fieldpref, $data, 'plugin_id');



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

			$iconText = '';

			if(count($icons)>0)
			{
				foreach($icons as $key=>$val)
				{
					$iconText .= "<img src='".$tp->replaceConstants($val)."' alt='' />";
				}

				$iconText = '<div class="icon-pool-preview">'.$iconText.'</div>';

				$opts['delete_ipool'] = array(
					'label'			=> EPL_ADLAN_231,
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


             //   $frm->admin_button($name, $value, $action = 'submit', $label = '', $options = array());

			$text .= "</div>
			</fieldset>
			</form>
			";
		//	e107::getRender()->tablerender(EPL_ADLAN_63.SEP.$tp->toHtml($plug_vars['@attributes']['name'], "", "defs,emotes_off, no_make_clickable"),$mes->render(). $text);

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

				
				$var['online']['text'] = EPL_ADLAN_220;
				$var['online']['link'] = e_SELF."?mode=online";
				
				
				if(E107_DEBUG_LEVEL > 0)
				{	
					$var['upload']['text'] = EPL_ADLAN_38;
					$var['upload']['link'] = e_SELF."?mode=upload";
				}

				$var['create']['text'] = EPL_ADLAN_114;
				$var['create']['link'] = e_SELF."?mode=create";
				
				
				
				

				$keys = array_keys($var);

				$action = (in_array($this->action,$keys)) ? $this->action : "installed";
				
				if($this->action == 'lans')
				{
					$action = 'create';
				}

				$icon  = e107::getParser()->toIcon('e-plugmanager-24');
				$caption = $icon."<span>".ADLAN_98."</span>";

				e107::getNav()->admin($caption, $action, $var);
		}



		

} // end of Class.
*/

/*
function plugin_adminmenu()
{
	global $pman;
	$pman -> pluginMenuOptions();
}*/




/**
 * Plugin Admin Generator by CaMer0n. //TODO - Added dummy template and shortcode creation, plus e_search, e_cron, e_xxxxx etc. 
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
		private $buildTable = false;
		private $debug = false;
	
		function __construct()
		{

			if(e_DEBUG == true)
			{
				$this->debug = true;
			}

			$this->special['checkboxes'] =  array('title'=> '','type' => null, 'data' => null,	 'width'=>'5%', 'thclass' =>'center', 'forced'=> TRUE,  'class'=>'center', 'toggle' => 'e-multiselect', 'fieldpref'=>true);
			$this->special['options'] = array( 'title'=> 'LAN_OPTIONS', 'type' => null, 'data' => null, 'width' => '10%',	'thclass' => 'center last', 'class' => 'center last', 'forced'=>TRUE, 'fieldpref'=>true);		
			

		}


		function run()
		{

			if(!empty($_GET['newplugin']))
			{
				$this->pluginName = e107::getParser()->filter($_GET['newplugin'],'file');
			}

			if(!empty($_GET['createFiles']))
			{
				$this->createFiles	= true;
			}

			if(vartrue($_POST['step']) == 4)
			{
				return $this->step4();
			}

			if(vartrue($_GET['step']) == 3)
			{
				return $this->step3();
			}




			if(!empty($_GET['newplugin']) && $_GET['step']==2)
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
			$tp = e107::getParser();
			
			$plugFolders = $fl->get_dirs(e_PLUGIN);	
			foreach($plugFolders as $dir)
			{
				$lanDir[$dir] = $dir;
				if(E107_DEBUG_LEVEL == 0 && file_exists(e_PLUGIN.$dir."/admin_config.php"))
				{
					continue;	
				}	
				$newDir[$dir] = $dir;
			}


			$info = EPL_ADLAN_102;
			$info .= "<ul>";
			$info .= "<li>".str_replace('[x]', e_PLUGIN, EPL_ADLAN_103)."</li>";
		//	$info .= "<li>".EPL_ADLAN_104."</li>";
			$info .= "<li>".EPL_ADLAN_105."</li>";
			$info .= "<li>".EPL_ADLAN_106."</li>";
			$info .= "</ul>";

		//	$mes->addInfo($tp->toHtml($info,true));
			
			$text = $frm->open('createPlugin','get', e_SELF);
			$text .= $frm->hidden('action', 'build');

			$text .= "<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
							<col class='col-control' />
						</colgroup>
				<tr>
					<td>".EPL_ADLAN_107."</td>
					<td><div class='input-append form-inline'>".$frm->open('createPlugin','get',e_SELF."?mode=create").$frm->select("newplugin",$newDir, false, 'size=xlarge').$frm->admin_button('step', 2,'other',LAN_GO)."</div> ".$frm->checkbox('createFiles',1,1,EPL_ADLAN_255).$frm->close()."</td>
					<td><div class='alert alert-info'>".$info."</div></td>
				</tr>
				
				<tr>
					<td>".EPL_ADLAN_108."</td>
					<td><div class='input-append form-inline'>".$frm->open('checkPluginLangs','get',e_SELF."?mode=lans").$frm->select("newplugin",$lanDir, false, 'size=xlarge').$frm->admin_button('step', 2,'other',LAN_GO)."</div> ".$frm->close()."</td>
					<td><div class='alert alert-info'>".EPL_ADLAN_254."</div></td>
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
				
				</div>";

			$text .= $frm->close();

			return $text;

		//	$ns->tablerender(ADLAN_98.SEP.EPL_ADLAN_114, $mes->render() . $text);
			
			
			
		//	$var['lans']['text'] = EPL_ADLAN_226;
		//		$var['lans']['link'] = e_SELF."?mode=lans";
			
			
		}


		function enterMysql()
		{
			
			$frm = e107::getForm();
			return "<div>".$frm->textarea('mysql','', 10,80)."</div>";	
			
		}


		/**
		 * @param string $table
		 * @param string $file
		 */
		private function buildSQLFile($table, $file)
		{

			$table = e107::getParser()->filter($table);

			e107::getDb()->gen("SHOW CREATE TABLE `#".$table."`");
			$data = e107::getDb()->fetch('num');

			if(!empty($data[1]))
			{
				$createData = str_replace("`".MPREFIX, '`', $data[1]);
				$createData .= ";";
				if(!file_exists($file)/* && empty($this->createFiles)*/)
				{
					file_put_contents($file,$createData);
				}
			}

		}




		function step3()
		{
			
		//	require_once(e_HANDLER."db_verify_class.php");
		//	$dv = new db_verify;
			$dv = e107::getSingleton('db_verify', e_HANDLER."db_verify_class.php");
			
			$frm = e107::getForm();
			$ns = e107::getRender();
			$mes = e107::getMessage();
			$tp = e107::getParser();


			$newplug = $tp->filter($_GET['newplugin'],'file');
			$this->pluginName = $newplug;

			$sqlFile = e_PLUGIN.$newplug."/".$newplug."_sql.php";

			if(!empty($_GET['build']) && !file_exists($sqlFile))
			{
				$this->buildSQLFile($_GET['build'], $sqlFile);
			}

			$ret = array();
			
			if(file_exists($sqlFile))
			{		
				$data = file_get_contents($sqlFile);
				$ret =  $dv->getSqlFileTables($data);
			}
			else
			{
				e107::getDebug()->log("SQL File Not Found");
		//		$this->buildTable = true;
			}
		
			$text = $frm->open('newplugin-step3','post', e_SELF.'?mode=create&action=build&newplugin='.$newplug.'&createFiles='.$this->createFiles.'&step=3');
			
			$text .= "<ul class='nav nav-tabs'>\n";
			$text .= "<li class='active'><a data-toggle='tab' href='#xml'>".EPL_ADLAN_109."</a></li>";
			
			$this->tableCount = count($ret['tables']);

			if(!empty($ret['tables']))
			{
				foreach($ret['tables'] as $key=>$table)
				{
					$label = "Table: ".$table;
					$text .= "<li><a data-toggle='tab'  href='#".$table."'>".$label."</a></li>";
					$this->tableList[] = $table;
				}
			}


			$text .= "<li><a data-toggle='tab'  href='#preferences'>".LAN_PREFS."</a></li>";
			$text .= "<li><a data-toggle='tab'  href='#addons'>".LAN_ADDONS."</a></li>"; //TODO LAN

			
			$text .= "</ul>";
			
			$text .= "<div class='tab-content'>\n";
			
			$text .= "<div class='tab-pane active' id='xml'>\n";
			$text .= $this->pluginXml(); 
			$text .= "</div>";

			if(!empty($ret['tables']))
			{
				foreach($ret['tables'] as $key=>$table)
				{
					$text .= "<div class='tab-pane' id='".$table."'>\n";
					$fields = $dv->getFields($ret['data'][$key]);
					$text .= $this->form($table,$fields);
					$text .= "</div>";
				}
			}




			$text .= "<div class='tab-pane' id='preferences'>\n";
			$text .= $this->prefs(); 
			$text .= "</div>";


			$text .= "<div class='tab-pane' id='addons'>\n";
			$text .= $this->addons();
			$text .= "</div>";


			if(empty($ret['tables']))
			{
				$text .= $frm->hidden($this->pluginName.'_ui[mode]','main');
				$text .= $frm->hidden($this->pluginName.'_ui[pluginName]', $this->pluginName);
			}

			$text .= "</div>";
			
			$text .= "
			<div class='buttons-bar center'>
			".$frm->hidden('newplugin', $this->pluginName)."
			".$frm->admin_button('step', 4,'other', LAN_GENERATE)."
			</div>";
			
			$text .= $frm->close();
			
			$mes->addInfo(EPL_ADLAN_112);

			$mes->addInfo(EPL_ADLAN_113);
		
			return array('caption'=>EPL_ADLAN_115, 'text'=> $text);
		//	$ns->tablerender(ADLAN_98.SEP.EPL_ADLAN_114.SEP., $mes->render() . $text);
		}



		private function step2()
		{


			$frm = e107::getForm();

			$tables = e107::getDb()->tables();


			$text = $frm->open('buildTab', 'get', e_REQUEST_SELF);

			$text .= "<table class='table adminform'>
				<tr><td colspan='2'><h4>".ucfirst(LAN_OPTIONAL)."</h4></td></tr>

				<tr>
				<td class='col-label'>To generate your <em>".$this->pluginName."_sql.php</em> table creation file, please select your sql table then click 'Refresh'</td>
				<td class='form-inline'>";

			$text .= $frm->select('build', $tables, null, array('useValues'=>1), "(".LAN_OPTIONAL.")");


		//	$text .= "<a href='#' id='build-table-submit' class='btn btn-success'>Refresh</a>";
		//	$text .= $frm->button('step', 3, 'submit', "Continue");
				unset($_GET['step']);
			foreach($_GET as $k=>$v)
			{
				$text .= $frm->hidden($k,$v);

			}
		//	$text .= $frm->hidden("build_table_url", e_REQUEST_SELF.'?'.$qry, array('id'=>'build-table-url'));


			$text .= "</td></tr>
			<tr><td>&nbsp;</td><td>
			".$frm->button('step', 3, 'submit', LAN_CONTINUE)."
			</td></tr></table>";

			$text .=  $frm->close();

/*
			e107::js('footer-inline','

				  $(document).on("click", "#build-table-submit", function(e){

					e.preventDefault();

					$(this).addClass("disabled");

                    var url = $("#build-table-url").val();
                    var sel = $("#build-table-tbl").val();

                    url = url + "&build=" + sel;

					window.location.href = url;

					return false;
				});





			');*/
			$ns = e107::getRender();
			return array('caption'=>EPL_ADLAN_115, 'text'=>$text);
		//	$ns->tablerender(ADLAN_98.SEP.EPL_ADLAN_114.SEP.EPL_ADLAN_115,  $text);


			return $text;

		}





		private function buildTemplateFile()
		{
			$dirName  = e_PLUGIN.$this->pluginName. "/templates";

			if(!is_dir($dirName))
			{
				mkdir($dirName,0755);
			}


			$file    = $dirName. "/".$this->pluginName."_template.php";
			$shortFileName = "templates/".$this->pluginName."_template.php";

			if(file_exists($file) && empty($this->createFiles))
			{
				return e107::getParser()->lanVars(EPL_ADLAN_256,$shortFileName);
			}


$content = <<<TMPL
<?php

// Template File
TMPL;

$upperName = strtoupper($this->pluginName);

$content .=  "
// ".$this->pluginName." Template file

if (!defined('e107_INIT')) { exit; }


\$".$upperName."_TEMPLATE = array();

\$".$upperName."_TEMPLATE['default']['start'] \t= '{SETIMAGE: w=400&h=300}';

\$".$upperName."_TEMPLATE['default']['item'] \t= '';

\$".$upperName."_TEMPLATE['default']['end'] \t= '';



";


			return file_put_contents($file,$content)? LAN_CREATED.': '.$shortFileName : LAN_CREATED_FAILED.': '.$shortFileName;
		}





		private function buildShortcodesFile()
		{
			$file    = e_PLUGIN.$this->pluginName. "/".$this->pluginName."_shortcodes.php";

$content = <<<TMPL
<?php
	

TMPL;

$content .=  "
// ".$this->pluginName." Shortcodes file

if (!defined('e107_INIT')) { exit; }

class plugin_".$this->pluginName."_".$this->pluginName."_shortcodes extends e_shortcode
{

";

		if(!empty($_POST['bullets_ui']['fields']))
		{
			foreach($_POST['bullets_ui']['fields'] as $key=>$row)
			{

				if($key === 'options' || $key === 'checkboxes')
				{
					continue;
				}

$content .= "
	/**
	* {".strtoupper($key)."}
	*/
	public function sc_".$key."(\$parm=null)
	{
	
		return \$this->var['".$key."'];
	}
	

";










			}


		}


$content .= '}';

			return file_put_contents($file,$content)? LAN_CREATED.': '.$this->pluginName."_shortcodes.php" : LAN_CREATED_FAILED.': '.$this->pluginName."_shortcodes.php";
		}


		private function createAddons($list)
		{

			$srch = array('_blank','blank');
			$result = array();

			foreach($list as $addon)
			{
				$addonDest = str_replace("_blank",$this->pluginName,$addon);
				$source         = e_PLUGIN."_blank/".$addon.".php";
				$destination    = e_PLUGIN.$this->pluginName. "/".$addonDest.".php";

				if(file_exists($destination) && empty($this->createFiles))
				{
					$result[] = e107::getParser()->lanVars(EPL_ADLAN_256,$addonDest.'.php');
					continue;
				}

				if($addon === '_blank_template')
				{
					$result[] = $this->buildTemplateFile();
					continue;
				}

				if($addon === '_blank_shortcodes')
				{
					$result[] = $this->buildShortcodesFile();
					continue;
				}

				if($content = file_get_contents($source))
				{
					$content = str_replace($srch, $this->pluginName, $content);

					if(file_exists($destination) && empty($this->createFiles))
					{
						$result[] = e107::getParser()->lanVars(EPL_ADLAN_256,$addonDest.'.php');
					}
					else
					{
						if(file_put_contents($destination,$content))
						{
							$result[] = LAN_CREATED." : ".$addonDest.".php";
						}
					}
				}
				else
				{
					//$mes->addError("Addon source-file was empty: ".$addon);
				}

			}

			return $result;
		}




		private function addons()
		{
			$plg = e107::getPlugin();

			$list = $plg->getAddonsList();
			$frm = e107::getForm();
			$text = "<table class='table table-striped adminlist' >";


		//Todo LANS
			$dizOther = array(
				'_blank' => "Simple frontend script",
				'_blank_setup' => "Create default table data during install, upgrade, uninstall etc",
				'_blank_menu' => "Menu item for use in the menu manager.",
				'_blank_template' => "Template to allow layout customization by themes.",
				'_blank_shortcodes' => "Shortcodes for the template."
			);

			array_unshift($list,'_blank', '_blank_setup', '_blank_menu', '_blank_template', '_blank_shortcodes');

			$templateFiles = scandir(e_PLUGIN."_blank");



	//print_a($list);
		//	$list[] = "_blank";
		//	$list[] = "_blank_setup";

			foreach($list as $v)
			{

				if(!in_array($v.".php", $templateFiles) && $v != '_blank_template' && $v!='_blank_shortcodes')
				{
					continue;
				}

				$diz = !empty($dizOther[$v]) ? $dizOther[$v] : $plg->getAddonsDiz($v);
				$label = str_replace("_blank", $this->pluginName, $v);
				$id = str_replace('_blank', 'blank', $v);

				$text .= "<tr>";
				$text .= "<td>".$frm->checkbox('addons[]',$v,false,$label)."</td>";
				$text .= "<td><label for='".$frm->name2id('addons-'.$id)."'>".$diz."</label></td>";
				$text .= "</tr>";
			}

			$text .= "</table>";

			return $text;

		}



		function prefs()
		{
			$frm = e107::getForm();

			$text = '';
			
				$options = array(
					'text'		=> EPL_ADLAN_116,
					'number'	=> EPL_ADLAN_117,
					'url'		=> EPL_ADLAN_118,
					'textarea'	=> EPL_ADLAN_119,
					'bbarea'	=> EPL_ADLAN_120,
					'boolean'	=> EPL_ADLAN_121,
					"method"	=> EPL_ADLAN_122,
					"image"		=> EPL_ADLAN_123,
					
					"dropdown"	=> EPL_ADLAN_124,
					"userclass"	=> EPL_ADLAN_125,
					"language"	=> EPL_ADLAN_126,

					"icon"		=> EPL_ADLAN_127,
		
					"file"		=> EPL_ADLAN_128,
	
				);
						

			$text = "<table class='table table-striped'>";

			for ($i=0; $i < 10; $i++) 
			{ 		
				$text .= "<tr><td>".
				$frm->text("pluginPrefs[".$i."][index]", '',40,'placeholder='.EPL_ADLAN_129)."</td><td>".
				$frm->text("pluginPrefs[".$i."][value]", '',50,'placeholder='.EPL_ADLAN_130)."</td><td>".
				$frm->select("pluginPrefs[".$i."][type]", $options, '', 'class=null', EPL_ADLAN_131)."</td><td>".
				$frm->text("pluginPrefs[".$i."][help]", '',80,'size=xxlarge&placeholder='.EPL_ADLAN_174)."</td>".
				"</tr>";
			}

			$text .= "</table>";
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
				'keywords' 		=> array('one','two','three'),
				'category'		=> array('category'),
				'copyright'		=> array('copyright'),
		//		'adminLinks'	=> array('url','description','icon','iconSmall','primary'),
		//		'sitelinks'		=> array('url','description','icon','iconSmall')
			);
			
			// Load old plugin.php file if it exists; 
			$legacyFile = e_PLUGIN.$this->pluginName."/plugin.php";		
			if(file_exists($legacyFile))
			{
				$eplug_name = $eplug_author = $eplug_url = $eplug_description = "";
				$eplug_tables = array();

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
						$info = str_replace('[x]', $this->pluginName."_sql.php", EPL_ADLAN_132);
						$mes->addInfo($info,'default',true);
						$red = e107::getRedirect();
						$red->redirect(e_REQUEST_URL,true);
					//	$red->redirect(e_SELF."?mode=create&newplugin=".$this->pluginName."&createFiles=1&step=2",true);
					}
					else 
					{
						$msg = str_replace('[x]', $this->pluginName."_sql.php", EPL_ADLAN_133)."<br />";
						$msg .= str_replace(array('[x]','[y]'), array($this->pluginName."_sql.php",$cont), EPL_ADLAN_134);
						$mes->addWarning($msg);	
					}
					
					
				}
			}

			$existingXml = e_PLUGIN.$this->pluginName."/plugin.xml";		
			if(file_exists($existingXml))
			{
				$p = e107::getXml()->loadXMLfile($existingXml,true);	
				
		//		print_a($p);
				$defaults = array(
					"main-name"					=> varset($p['@attributes']['name']),
					"main-lang"					=> varset($p['@attributes']['lan']),
					"author-name"				=> varset($p['author']['@attributes']['name']),
					"author-url"				=> varset($p['author']['@attributes']['url']),
					"description-description"	=> varset($p['description']),
					"summary-summary"			=> varset($p['summary'], $p['description']),
					"category-category"			=> varset($p['category']),
					"copyright-copyright"			=> varset($p['copyright']),
					"keywords-one"				=> varset($p['keywords']['word'][0]),
					"keywords-two"				=> varset($p['keywords']['word'][1]),
					"keywords-three"			=> varset($p['keywords']['word'][2]),
				);
				
				unset($p);
		
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
					$size = (count($val)==1) ? 'span7 col-md-7' : 'span2 col-md-2';
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
			
			$size 		= 30; // Textbox size.
			$help		= '';
			$pattern	= "";
			$required	= false;
			
			switch ($info)
			{
				
				case 'main-name':
					$help 		= EPL_ADLAN_135;
					$required 	= true;
					$pattern 	= "[A-Za-z0-9 -]*";
					$xsize		= 'medium';
				break;
		
				case 'main-lang':
					$help 		= EPL_ADLAN_136;
					$required 	= false;
					$placeholder= " ";
					$pattern 	= "[A-Z0-9_]*";
					$xsize		= 'medium';
				break;
				
				case 'main-date':
					$help 		= EPL_ADLAN_137;
					$required 	= true;
					$xsize		= 'medium';
				break;
				
				case 'main-version':
					$default 	= '1.0';
					$required 	= true;
					$help 		= EPL_ADLAN_138;
					$pattern	= "^[\d]{1,2}\.[\d]{1,2}(\.[\d]{1,2})?$";
					$xsize		= 'small';
				break;

				case 'main-compatibility':
					$default 	= '2.0';
					$required 	= true;
					$help 		= EPL_ADLAN_139;
					$pattern	= "^[\d]{1,2}\.[\d]{1,2}$";
					$xsize		= 'small';
				break;
				
				case 'author-name':
					$default 	= (vartrue($default)) ? $default : USERNAME;
					$required 	= true;
					$help 		= EPL_ADLAN_140;
					$pattern	= "[A-Za-z \.0-9]*";
					$xsize		= 'medium';
				break;
				
				case 'author-url':
					$required 	= true;
					$help 		= EPL_ADLAN_141;
				//	$pattern	= "https?://.+";
					$xsize		= 'medium';
				break;
				
				//case 'main-installRequired':
				//	return "Installation required: ".$frm->radio_switch($name,'',LAN_YES, LAN_NO);
				//break;	
				
				case 'summary-summary':
					$help 		= EPL_ADLAN_142."<br />".EPL_ADLAN_143;
					$required 	= true;
					$size 		= 130;
					$placeholder= " ";
					$pattern	= "[A-Za-z -\.0-9]*";
					$xsize		= 'block-level';
				break;	

				case 'keywords-one':
					$type = 'keywordDropDown';
					$required = true;
					$help 		= EPL_ADLAN_144;
				break;

				case 'keywords-three':
				case 'keywords-two':
					$help 		= EPL_ADLAN_144."<br />".EPL_ADLAN_143;
					$required 	= true;
					$size 		= 20;
					$placeholder= " ";
					$pattern 	= '^[a-z]*$';
					$xsize		= 'medium';
				break;	
				
				case 'description-description':
					$help 		= EPL_ADLAN_145."<br />".EPL_ADLAN_143;
					$required 	= true;
					$size 		= 100;
					$placeholder = " ";
					$pattern	= "[A-Za-z -\.0-9]*";
					$xsize		= 'block-level';
				break;
				
					
				case 'category-category':
					$help 		= EPL_ADLAN_146;
					$required 	= true;
					$size 		= 20;
				break;
						
				default:
					
				break;
			}

			$req = ($required == true) ? "&required=1" : "";	
			$placeholder = (varset($placeholder)) ? $placeholder : $type;
			$pat = ($pattern) ? "&pattern=".$pattern : "";
			$sz = ($xsize) ? "&size=".$xsize : "";
			
			switch ($type) 
			{
				case 'date':
					$text = $frm->datepicker($name, time(), 'format=yyyy-mm-dd&return=string'.$req . $sz);
				break;
				
				case 'description':
					$text = $frm->textarea($name,$default, 3, 100, $req.$sz);	// pattern not supported. 	
				break;
								
						
				case 'category':
					$options = array(
					'settings'	=> EPL_ADLAN_147,
					'users'		=> EPL_ADLAN_148,
					'content'	=> EPL_ADLAN_149,
					'tools'		=> EPL_ADLAN_150,
					'manage'	=> EPL_ADLAN_151,
					'misc'		=> EPL_ADLAN_152,
					'menu'		=> EPL_ADLAN_153,
					'about'		=> EPL_ADLAN_154
					);
				
					$text = $frm->select($name, $options, $default,'required=1&class=form-control', true);
				break;

				case 'keywordDropDown':

					$options = array(

						'generic',
						'admin',
					    'messaging',
					    'enhancement',
					    'date',
					    'commerce',
					    'form',
					    'gaming',
					    'intranet',
					    'multimedia',
					    'information',
					    'mail',
					    'search',
						'stats',
						'files',
						'security',
						'generic',
						'language'
					);

					sort($options);

					$text = $frm->select($name, $options, $default,'required=1&class=form-control&useValues=1', true);


				break;
				
				
				default:
					$text = $frm->text($name, $default, $size, 'placeholder='.$placeholder . $sz. $req. $pat);	
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
			
			$newArray['DESCRIPTION_DESCRIPTION'] = strip_tags($tp->toHTML($newArray['DESCRIPTION_DESCRIPTION'],true));

			$_POST['pluginPrefs'] = $tp->filter($_POST['pluginPrefs']);

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
		<word>{KEYWORDS_THREE}</word>
	</keywords>
	<category>{CATEGORY_CATEGORY}</category>
	<copyright>{COPYRIGHT_COPYRIGHT}</copyright>
	<adminLinks>
		<link url="admin_config.php" description="{ADMINLINKS_DESCRIPTION}" icon="" iconSmall="" icon128="" primary="true" >LAN_CONFIGURE</link>
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
			
			if(file_exists($path) && empty($this->createFiles))
			{
				return  htmlentities($result);
			}


			if($this->createFiles == true || !file_exists($path))
			{
				if(file_put_contents($path,$result) )
				{
					$mes->addSuccess(EPL_ADLAN_155." ".$path);
				}
				else {
					$mes->addError(EPL_ADLAN_156." ".$path);
				}
			}
			return  htmlentities($result);
			
		//	$ns->tablerender(LAN_CREATED.": plugin.xml", "<pre  style='font-size:80%'>".htmlentities($result)."</pre>");	
		}
						
					
				
			


		function form($table,$fieldArray)
		{
			$frm = e107::getForm();
					
			$modes = array(
				"main"=>EPL_ADLAN_157,
				"cat"=>EPL_ADLAN_158,
				"other1"=>EPL_ADLAN_159,
				"other2"=>EPL_ADLAN_160,
				"other3"=>EPL_ADLAN_161,
				"other4"=>EPL_ADLAN_162,
				'exclude'=>EPL_ADLAN_163,
			);
			
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
							<th>".EPL_ADLAN_164."</th>
							<th>".EPL_ADLAN_165."</th>
							<th>".EPL_ADLAN_166."</th>
							<th>".EPL_ADLAN_167."</th>
							<th>".EPL_ADLAN_168."</th>
							<th class='center'>".EPL_ADLAN_169."</th>
							<th class='center'>".EPL_ADLAN_170."</th>
							<th class='center'>".EPL_ADLAN_171."</th>
							<th class='center e-tip' title='".EPL_ADLAN_177."'>".EPL_ADLAN_172."</th>
							<th class='center e-tip' title='".EPL_ADLAN_178."'>".EPL_ADLAN_173."</th>
							<th>".EPL_ADLAN_174."</th>
							<th>".EPL_ADLAN_175."</th>
							<th>".EPL_ADLAN_176."</th>
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
					<td>".$frm->text($this->table."[fields][".$name."][readParms]",'', 60,'size=small')."</td>
					<td>".$frm->text($this->table."[fields][".$name."][writeParms]",'', 60,'size=small').
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


		/**
		 * @param $name
		 * @param $val
		 * @return string
		 */
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
				case 'date':
				case 'datetime':
				case 'time':
				case 'timestamp':
					$array = array(
					'text'		=> EPL_ADLAN_179,
					"hidden"	=> EPL_ADLAN_180,
					"method"	=> EPL_ADLAN_186,
					);
				break;
			
				case 'int':
				case 'tinyint':
				case 'bigint':
				case 'smallint':
				case 'mediumint':
					$array = array(
					"boolean"	=> EPL_ADLAN_181,
					"number"	=> EPL_ADLAN_182,
					"dropdown"	=> EPL_ADLAN_183,
					"userclass"	=> EPL_ADLAN_184,
					"datestamp"	=> LAN_DATE,
					"method"	=> EPL_ADLAN_186,
					"hidden"	=> EPL_ADLAN_187,
					"user"		=> EPL_ADLAN_188,
					);	
				break;
				
				case 'decimal':
				case 'double':
				case 'float':
					$array = array(
					"number"	=> EPL_ADLAN_189,
					"dropdown"	=> EPL_ADLAN_190,
					"method"	=> EPL_ADLAN_191,
					"hidden"	=> EPL_ADLAN_192,
					);	
				break;
				
				case 'varchar':
				case 'tinytext':
				case 'tinyblob':
				$array = array(
					'text'		=> EPL_ADLAN_193,
					"url"		=> EPL_ADLAN_194,
					"email"		=> EPL_ADLAN_195,
					"ip"		=> EPL_ADLAN_196,
					"number"	=> EPL_ADLAN_197,
					"password"	=> EPL_ADLAN_198,
					"tags"		=> EPL_ADLAN_199,
					
					"dropdown"	=> EPL_ADLAN_200,
					"userclass"	=> EPL_ADLAN_201,
					"language"	=> EPL_ADLAN_202,

					"icon"		=> EPL_ADLAN_203,
					"image"		=> EPL_ADLAN_204,
					"file"		=> EPL_ADLAN_205,
					"method"	=> EPL_ADLAN_206,

					"hidden"	=> EPL_ADLAN_207
					);
				break;

				case 'enum':
				$array = array(
					"dropdown"	=> EPL_ADLAN_200,
					"tags"		=> EPL_ADLAN_211,
					"method"	=> EPL_ADLAN_212,
					"hidden"	=> EPL_ADLAN_215
					);
				break;
				
				case 'text':
				case 'mediumtext':
				case 'longtext':
				case 'blob':
				case 'mediumblob':
				case 'longblob':
				$array = array(
					'textarea'	=> EPL_ADLAN_208,
					'bbarea'	=> EPL_ADLAN_209,
					'text'		=> EPL_ADLAN_210,
					"tags"		=> EPL_ADLAN_211,
					"method"	=> EPL_ADLAN_212,
					"image"		=> EPL_ADLAN_213,
					"images"	=> EPL_ADLAN_214,
					"hidden"	=> EPL_ADLAN_215
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
			elseif(count($tmp) == 2) // Link_description
			{
				$name = $tmp[1];		
			}
			elseif(count($tmp) === 1)
			{
				$name = $data;	
			}
	
			$ret['title'] = ucfirst($name);
			$ret['width'] = 'auto';
			$ret['class'] = 'left';
			$ret['thclass'] = 'left';
			
		//	echo "<br />name=".$name; 
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
				
				case 'prename':
				case 'firstname':
				case 'lastname':
				case 'company':
				case 'city':
					$ret['title'] = ucfirst($name);
					$ret['type'] = 'text';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['fieldpref'] = true;
					$ret['inline'] = true;
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
				
				case 'email':
				case 'email2':
					$ret['title'] = 'LAN_EMAIL';
					$ret['type'] = 'email';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['fieldpref'] = false;
					$ret['inline'] = true;
				break;

				
				case 'ip':
					$ret['title'] = 'LAN_IP';
					$ret['type'] = 'ip';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['fieldpref'] = false;
					$ret['inline'] = false;
				break;
				
				case 'user':	
				case 'userid':				
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
				
				case 'code':
				case 'zip':
					$ret['title'] = ucfirst($name);
					$ret['type'] = 'number';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['inline'] = true;
				break;

				case 'state':
				case 'country':
				case 'category':
					$ret['title'] = ($name == 'category') ? 'LAN_CATEGORY' : ucfirst($name);
					$ret['type'] = 'dropdown';
					$ret['batch'] = true;
					$ret['filter'] = true;
					$ret['fieldpref'] = true;
					$ret['inline'] = true;
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
				
				case 'notes':
				case 'comment':
				case 'comments':
				case 'address':
				case 'description':
					$ret['title'] = ($name == 'description') ? 'LAN_DESCRIPTION' : ucfirst($name);
					 $ret['type'] = ($val['type'] == 'TEXT') ? 'textarea' : 'text';
					 $ret['width'] = '40%';
					$ret['inline'] = false;
				break;
				
				default:
					$ret['type'] = 'boolean';
					$ret['class'] = 'left';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['thclass'] = 'left';
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

		function step4()
		{
			$tp = e107::getParser();
			$pluginTitle = $tp->filter($_POST['xml']['main-name']);
			
			if($_POST['xml'])
			{
				$_POST['xml'] = $tp->filter($_POST['xml']);
				$xmlText =	$this->createXml($_POST['xml']);
			}
					
			if(!empty($_POST['addons']))
			{
				$_POST['addons'] = $tp->filter($_POST['addons']);
				$addonResults = $this->createAddons($_POST['addons']);
			}
			
		//	e107::getDebug()->log($_POST);
			
			unset($_POST['step'],$_POST['xml'], $_POST['addons']);
		$thePlugin = $tp->filter($_POST['newplugin'],'file');

$text = "\n
// Generated e107 Plugin Admin Area 

require_once('../../class2.php');
if (!getperms('P')) 
{
	e107::redirect('admin');
	exit;
}

// e107::lan('".$thePlugin."',true);


class ".$thePlugin."_adminArea extends e_admin_dispatcher
{

	protected \$modes = array(	
	";
	

	unset($_POST['newplugin'], $_POST['mode']);

			foreach($_POST as $table => $vars) // LOOP Through Tables. 
			{
				if(!empty($vars['mode']) && $vars['mode'] != 'exclude')
				{

					$vars['mode'] = $tp->filter($vars['mode']);

	$text .= "
		'".$vars['mode']."'	=> array(
			'controller' 	=> '".$table."',
			'path' 			=> null,
			'ui' 			=> '".str_replace("_ui", "_form_ui", $table)."',
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
				if(!empty($vars['mode']) && $vars['mode'] != 'exclude' && !empty($vars['table']))
				{

						$vars['mode'] = $tp->filter($vars['mode']);
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
		// 'main/div0'      => array('divider'=> true),
		// 'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P'),
		
	);

	protected \$adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected \$menuTitle = '".vartrue($pluginTitle, $tp->filter($vars['pluginName']))."';
}



";			
			// print_a($_POST);

			

	
			
			 
			$tableCount = 1;
			foreach($_POST as $table => $vars) // LOOP Through Tables. 
			{

				$vars['mode'] = $tp->filter($vars['mode']);
				$vars['pluginName'] = $tp->filter($vars['pluginName']);
				$vars['table'] = $tp->filter($vars['table']);
				$vars['pid'] = $tp->filter($vars['pid']);

				if($table == 'pluginPrefs' || $vars['mode'] == 'exclude')
				{
					continue;
				}
				
				
				$FIELDS = $this->buildAdminUIFields($vars);
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
	//	protected \$eventName		= '".$vars['pluginName']."-".$vars['table']."'; // remove comment to enable event triggers in admin. 		
		protected \$table			= '".$vars['table']."';
		protected \$pid				= '".$vars['pid']."';
		protected \$perPage			= 10; 
		protected \$batchDelete		= true;
		protected \$batchExport     = true;
		protected \$batchCopy		= true;

	//	protected \$sortField		= 'somefield_order';
	//	protected \$sortParent      = 'somefield_parent';
	//	protected \$treePrefix      = 'somefield_title';

	//	protected \$tabs				= array('Tabl 1','Tab 2'); // Use 'tab'=>0  OR 'tab'=>1 in the \$fields below to enable. 
		
	//	protected \$listQry      	= \"SELECT * FROM `#tableName` WHERE field != '' \"; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.
	
		protected \$listOrder		= '".$vars['pid']." DESC';
	
		protected \$fields 		= ".$FIELDS.";		
		
		protected \$fieldpref = array(".implode(", ",$FIELDPREF).");
		
";


if($_POST['pluginPrefs'] && ($vars['mode']=='main'))
{
	$text .= "
	//	protected \$preftabs        = array('General', 'Other' );
		protected \$prefs = array(\n";
		
		foreach($_POST['pluginPrefs'] as $k=>$val)
		{
			if(!empty($val['index']))
			{
				$index = $tp->filter($val['index']);
				$type = vartrue($val['type'],'text');
				$help = str_replace("'",'', vartrue($val['help']));
				
				$text .= "\t\t\t'".$index."'\t\t=> array('title'=> '".ucfirst($index)."', 'tab'=>0, 'type'=>'".$tp->filter($type)."', 'data' => 'str', 'help'=>'".$tp->filter($help)."'),\n";
			}	
	
		}
		
		
		$text .= "\t\t); \n\n";
				
}
				
			

$text .= "	
		public function init()
		{
			// This code may be removed once plugin development is complete. 
			if(!e107::isInstalled('".$vars['pluginName']."'))
			{
				e107::getMessage()->addWarning(\"This plugin is not yet installed. Saving and loading of preference or table data will fail.\");
			}
			
			// Set drop-down values (if any). 
";
			
		foreach($vars['fields'] as $k=>$v)
		{
			if($v['type'] == 'dropdown')
			{
				$text .= "\t\t\t\$this->fields['".$k."']['writeParms']['optArray'] = array('".$k."_0','".$k."_1', '".$k."_2'); // Example Drop-down array. \n";
			}
		}
					
				
				
			
			
$text .= "	
		}

		
		// ------- Customize Create --------
		
		public function beforeCreate(\$new_data,\$old_data)
		{
			return \$new_data;
		}
	
		public function afterCreate(\$new_data, \$old_data, \$id)
		{
			// do something
		}

		public function onCreateError(\$new_data, \$old_data)
		{
			// do something		
		}		
		
		
		// ------- Customize Update --------
		
		public function beforeUpdate(\$new_data, \$old_data, \$id)
		{
			return \$new_data;
		}

		public function afterUpdate(\$new_data, \$old_data, \$id)
		{
			// do something	
		}
		
		public function onUpdateError(\$new_data, \$old_data, \$id)
		{
			// do something		
		}		
		
		// left-panel help menu area. (replaces e_help.php used in old plugins)
		public function renderHelp()
		{
			\$caption = LAN_HELP;
			\$text = 'Some help text';

			return array('caption'=>\$caption,'text'=> \$text);

		}
			
	/*	
		// optional - a custom page.  
		public function customPage()
		{
			\$text = 'Hello World!';
			\$otherField  = \$this->getController()->getFieldVar('other_field_name');
			return \$text;
			
		}
		
";


$text .= $this->buildAdminUIBatchFilter($vars['fields'], $table, 'batch');
$text .= $this->buildAdminUIBatchFilter($vars['fields'], $table, 'filter');

$text .= "	
		
		
	*/
			
}
				


class ".str_replace("_ui", "_form_ui", $table)." extends e_admin_form_ui
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

		 		
		switch(\$mode)
		{
			case 'read': // List Page
				return \$curVal;
			break;
			
			case 'write': // Edit Page
				return \$this->text('".$fld."',\$curVal, 255, 'size=large');
			break;
			
			case 'filter':
				return array('customfilter_1' => 'Custom Filter 1', 'customfilter_2' => 'Custom Filter 2');
			break;
			
			case 'batch':
				return array('custombatch_1' => 'Custom Batch 1', 'custombatch_2' => 'Custom Batch 2');
			break;
		}
		
		return null;
	}
";
}


foreach($_POST['pluginPrefs'] as $fld=>$val)
{
	if(varset($val['type']) !== 'method' || empty($val['index']))
	{
		continue;
	}

	$index = $tp->filter($val['index']);

$text .= "
	
	// Custom Method/Function (pref)
	function ".$index."(\$curVal,\$mode)
	{

		 		
		switch(\$mode)
		{			
			case 'write': // Edit Page
				return \$this->text('".$index."',\$curVal, 255, 'size=large');
			break;
			
		}
		
		return null;
	}
";
}










$text .= "
}		
		
";			
						
	 		$tableCount++;	
					
			} // End LOOP. 
	
$text .= '		
new '.$thePlugin.'_adminArea();

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
			$endPHP =  '';

			if(!empty($addonResults))
			{
				foreach($addonResults as $v)
				{
					$mes->addSuccess($v);
				}
			}

			if(file_exists($generatedFile) && empty($this->createFiles))
			{
				$message = e107::getParser()->lanVars(EPL_ADLAN_256,"admin_config.php");
				$mes->addSuccess($message);
			}
			else
			{
				if(file_put_contents($generatedFile, $startPHP .$text . $endPHP))
				{
					$message = str_replace("[x]", "<a class='alert-link' href='".$generatedFile."'>".EPL_ADLAN_216."</a>", EPL_ADLAN_217);
					$mes->addSuccess($message);
				}	
				else 
				{
					$mes->addError(str_replace('[x]', $generatedFile, EPL_ADLAN_218));
				}
			}

		//	echo $mes->render();

			$ret = "<h3>plugin.xml</h3>";
			$ret .= "<pre style='font-size:80%'>".$xmlText."</pre>";
			$ret .= "<h3>admin_config.php</h3>";
			$ret .= "<pre style='font-size:80%'>".$text."</pre>";

			e107::getPlug()->clearCache();

			return array('caption'=>EPL_ADLAN_253, 'text'=> $ret);

	
		}


		/**
		 * @param array  $fields
		 * @param string $table
		 * @param string $type
		 * @return string
		 */
		private function buildAdminUIBatchFilter($fields, $table, $type='batch')
	{
		$text = '';

		$typeUpper = ucfirst($type);

		$params = ($type === 'batch') ? "\$selected, \$type" : "\$type";

		foreach($fields as $fld=>$val)
		{
			if(varset($val['type']) !== 'method')
			{
				continue;
			}



			$text .= "
	
	 // Handle ".$type." options as defined in ".str_replace("_ui", "_form_ui", $table)."::".$fld.";  'handle' + action + field + '".$typeUpper."'
	 // @important \$fields['".$fld."']['".$type."'] must be true for this method to be detected. 
	 // @param \$selected
	 // @param \$type
	function handleList".eHelper::camelize($fld,true).$typeUpper."(".$params.")
	{
";

	if($type === 'filter')
	{
		$text .= "
		\$this->listOrder = '".$fld." ASC';
	";

	}
	else
	{
		$text .= "
		\$ids = implode(',', \$selected);\n";

	}

$text .= "
		switch(\$type)
		{
			case 'custom".$type."_1':
";

$text .= ($type === 'batch') ? "				// do something" : "				// return ' ".$fld." != 'something' '; ";


$text .= "
				e107::getMessage()->addSuccess('Executed custom".$type."_1');
				break;

			case 'custom".$type."_2':
";

$text .= ($type === 'batch') ? "				// do something" : "				// return ' ".$fld." != 'something' '; ";

$text .= "
				e107::getMessage()->addSuccess('Executed custom".$type."_2');
				break;

		}


	}
";
		}


		return $text;

	}


		/**
		 * @param array $vars
		 * @return null|string|string[]
		 */
		private function buildAdminUIFields($vars)
	{
			$srch = array(

				"\n",
			//	"),",
				"    ",
				"'forced' => '1'",
				"'batch' => '1'",
				"'filter' => '1'",
				"'batch' => '0'",
				"'filter' => '0'",
				"'inline' => '1'",
				"'validate' => '1'",
			//	", 'fieldpref' => '1'",
				"'type' => ''",
				"'data' => ''",
				" array (  )",
			 );

			$repl = array(

				 "",
			//	 "),\n\t\t",
				 " ",
				"'forced' => true",
				"'batch' => true",
				"'filter' => true",
				"'batch' => false",
				"'filter' => false",
				"'inline' => true",
				"'validate' => true",
			//	"",
				"'type' => null",
				"'data' => null",
				"array ()"
				  );



			foreach($vars['fields'] as $key=>$val)
			{

				if(($val['type'] === 'dropdown' || $val['type'] === 'method') && empty($val['filter']))
				{
					$vars['fields'][$key]['filter'] = '0';
				}

				if(($val['type'] === 'dropdown' || $val['type'] === 'method') && empty($val['batch']))
				{
					$vars['fields'][$key]['batch'] = '0';
				}

				if($val['type'] == 'image' && empty($val['readParms']))
				{
					$vars['fields'][$key]['readParms'] = 'thumb=80x80'; // provide a thumbnail preview by default.
				}

				if(empty($vars['fields'][$key]['readParms']))
				{
					$vars['fields'][$key]['readParms'] = array();
				}

				if(empty($vars['fields'][$key]['writeParms']))
				{
					$vars['fields'][$key]['writeParms'] = array();
				}


				unset($vars['fields'][$key]['fieldpref']);

			}

			$FIELDS = "array (\n";

			foreach($vars['fields'] as $key=>$val)
			{
				$FIELDS .= "\t\t\t'".str_pad($key."'",25," ",STR_PAD_RIGHT)."=> ".str_replace($srch,$repl,var_export($val,true)).",\n";
			}

			$FIELDS .= "\t\t)";

		//	$FIELDS = var_export($vars['fields'],true);
		//	$FIELDS = str_replace($srch,$repl,var_export($vars['fields'],true));
			$FIELDS = preg_replace("#('([A-Z0-9_]*?LAN[_A-Z0-9]*)')#","$2",$FIELDS); // remove quotations from LANs.


		return $FIELDS;




	}




}




?>