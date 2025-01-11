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

require_once(__DIR__.'/../class2.php');

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
	e107::redirect(e_SELF);
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
		'online/grid'			=> array('caption'=> EPL_ADLAN_220, 'perm' => 'Z', 'icon'=>'fas-search'),
		'avail/upload'			=> array('caption'=>EPL_ADLAN_38, 'perm' => '0'),
		'create/build'          =>  array('caption'=>EPL_ADLAN_114, 'perm' => '0', 'icon'=>'fas-toolbox'),

	//	'main/create'		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),

		// 'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
	);


	protected $defaultMode = 'installed';


	protected $adminMenuAliases = array(
		'installed/uninstall'	=> 'installed/list',
		'lans/list'             => 'create/build',
		'online/list'           => 'online/grid',
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
				'plugin_description' => array('title' => LAN_DESCRIPTION, 'type' => 'textarea', 'data' => false, 'width' => 'auto', 'help' => '', 'readParms' => 'expand=1&truncate=180&bb=1', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',),

				'plugin_date'       => array('title' => LAN_RELEASED, 'type' => 'text', 'data' => false,  "width" => "8%", 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',),
			    'plugin_category'    => array('title' => LAN_CATEGORY, 'type' => 'dropdown', 'data' => 'str', 'width' => 'auto', 'batch' => true, 'filter' => true, 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => array(), 'class' => 'left', 'thclass' => 'left',),

				'plugin_author'      => array('title' => LAN_AUTHOR, 'type' => 'text', 'data' => false, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',),
				"plugin_license"	 => array("title" => "License", 	 'nolist'=>false,'data'=>false,	 "type"=>"text", "width" => "5%", "thclass" => "left"),
  				'plugin_compatible'  => array('title' => EPL_ADLAN_13, 'type' => 'method', 'data' => false, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',),

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

		protected $batchOptions     = array();
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

			if($this->getMode() === 'installed') // TODO more batch options install, uninstall etc.
			{
				$this->batchOptions = array('repairall'=> 'Repair selected'); // TODO LAN "[x] selected"
			}

		}

		public function handleListRepairallBatch($arr)
		{
			if(empty($arr))
			{
				return null;
			}

			$arr = e107::getParser()->filter($arr, 'int');

			$data = e107::getDb()->retrieve('plugin', 'plugin_path', 'plugin_id IN ('.implode(',', $arr).')', true);

			if(empty($data))
			{
				return null;
			}

			foreach($data as $row)
			{
				$this->repair($row['plugin_path']);
			}


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

			/**
			 * @var  $id
			 * @var e_model $model
			 */
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

			$this->getTreeModel()->setParam('db_query', $this->_modifyListQry(false, false, 0, false, $this->listQry))->loadBatch();

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
			$plg->clearCache();
			if(!$list = $plg->getUpgradableList())
			{
				return null;
			}


			$text = "<ul class='media-list'>";
			foreach($list as $path=>$ver)
			{
				$plg->load($path);
				$name = $plg->getName();
				$url = e_ADMIN."plugin.php?mode=installed&action=upgrade&path=".$path."&e-token=".defset('e_TOKEN');
				$text .= "<li class='media'>
				<div class='media-left'>
					<a href='".$url."'>".$plg->getIcon(32)."</a>
					</div><div class='media-body'><a class='e-spinner' href='".$url."' title=\"".EPL_UPGRADE." ".$name." v".$ver."\">".$name."</a></div></li>";

			}
			$text .= "</ul>";


			return array('caption'=>EPL_ADLAN_247, 'text'=>$text);

		}

		// Action Pages.





		function installPage()
		{
			if(empty($this->getQuery('e-token')))
			{
				e107::getMessage()->addError("Invalid Token"); // Debug - no need for translation.
				$this->redirectAction('list');
			}

			$id = $this->getQuery('path');

			$text = e107::getPlugin()->install($id);

		//	$log = e107::getAdminLog();

			if ($text === FALSE)
			{
				e107::getMessage()->add(EPL_ADLAN_99, E_MESSAGE_ERROR);
			}
			else
			{
				//$plugin->save_addon_prefs('update');
			//	$info = $plugin->getinfo($this->id);  //FIXME use e107::getPlug();

			//	$name = deftrue($info['plugin_name'],$info['plugin_name']). " v".$info['plugin_version']. "({e_PLUGIN}".$info['plugin_path'].")";

			//	$log->add('PLUGMAN_01', $name, E_LOG_INFORMATIVE, '');

				// make sure ALL plugin/addon pref lists get update and are current
				e107::getPlug()->clearCache()->buildAddonPrefLists();

				e107::getMessage()->add($text, E_MESSAGE_SUCCESS);
			}


			$this->redirectAction('list');
		}


		function buildPage()
		{
			require_once(e_HANDLER."e_pluginbuilder_class.php");
			$pc = new e_pluginbuilder;
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
			if(empty($this->getQuery('e-token')))
			{
				e107::getMessage()->addError("Invalid Token"); // Debug - no need for translation.
				$this->redirectAction('list');
			}


			$id = $this->getQuery('path');


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

			if(empty($this->getQuery('e-token')))
			{
				e107::getMessage()->addError("Invalid Token"); // Debug - no need for translation.
				$this->redirectAction('list');
				return null;
			}

			$id = $this->getQuery('path');

			$this->repair($id);

			$this->redirectAction('list');
		}

		private function repair($id)
		{
			if(!is_dir(e_PLUGIN.$id))
			{
				e107::getMessage()->addError("Plugin {$id} doesn't exist");
				return false;
			}

			e107::getSingleton('e107plugin')->refresh($id);
			e107::getLog()->add('PLUGMAN_04', $id);

			e107::getMessage()->addSuccess("Repair Complete (".$id.")"); // Repair Complete ([x])

		}


		function pullPage()
		{
			$id = $this->getQuery('path');

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
			if(empty($this->getQuery('e-token')))
			{
				e107::getMessage()->addError("Invalid Token"); // Debug - no need for translation.
				$this->redirectAction('list');
			}

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
				<form enctype='multipart/form-data' method='post' action='".e_SELF."?mode=avail&action=upload'>
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
         //   e107::getRender()->tablerender(ADLAN_98.SEP.EPL_ADLAN_38, $text);


		}

		private function pluginUpgrade()
		{
			$pref 		= e107::getPref();
			$admin_log 	= e107::getLog();
			$plugin 	= e107::getPlugin();

		    $sql 		= e107::getDb();
	        $mes 		= e107::getMessage();

	        $id         = $this->getQuery('path');

			$plug 		= e107::getPlug()->load($id)->getMeta();

			$text = '';

			$_path = e_PLUGIN.$id.'/';
			if(file_exists($_path.'plugin.xml'))
			{

				$plugin->install_plugin_xml($id, 'upgrade');
				$text = LAN_UPGRADE_SUCCESSFUL;
			}
			else
			{
				e107::getMessage()->addDebug("Running Legacy plugin upgrade. <b>".$_path."</b> not found."); // NO LAN

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

				e107::getLog()->add('PLUGMAN_02', $name);
				$text .= (isset($eplug_upgrade_done)) ? '<br />'.$eplug_upgrade_done : "<br />".LAN_UPGRADE_SUCCESSFUL;
				$sql->update('plugin', "plugin_version ='{$eplug_version}', plugin_addons='{$eplug_addons}' WHERE plugin_id='$this->id' ");
				$pref['plug_installed'][$plug['plugin_path']] = $eplug_version; 			// Update the version

				e107::getConfig()->setPref($pref);
				$plugin->rebuildUrlConfig();
				e107::getConfig()->save();
			}

			$mes->addSuccess($text);
			//$plugin->save_addon_prefs('update');

			// make sure ALL plugin/addon pref lists get update and are current
			e107::getPlug()->clearCache()->buildAddonPrefLists();

			// clear infopanel in admin dashboard.
			e107::getCache()->clear('Infopanel_plugin', true);
			e107::getSession()->clear('addons-update-status');
			e107::getSession()->set('addons-update-checked',false); // set to recheck it.

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
			<legend>".EPL_ADLAN_54." ".$tp->toHTML($plug_vars['@attributes']['name'], "", "defs,emotes_off, no_make_clickable")."</legend>
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
			$icons = (array) $med->listIcons(e_PLUGIN.$path);

			$iconText = '';

			if(count($icons) > 0)
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
				$text .= varset($val['helpText']) ? "".$frm->help($val['helpText'])."" : "";
				$text .= "</td>\n<td>".$frm->select($key,$val['itemList'],$val['itemDefault']);

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



			$text .= "<input type='hidden' name='e-token' value='".defset('e_TOKEN')."' /></div>
			</fieldset>
			</form>
			";

			return $text;
		//	e107::getRender()->tablerender(EPL_ADLAN_63.SEP.$tp->toHTML($plug_vars['@attributes']['name'], "", "defs,emotes_off, no_make_clickable"),$mes->render(). $text);

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

			if(!empty($var['plugin_installflag']))
			{
				$text .= "<a class='btn btn-default' href=\"" . e_SELF . "?mode=".$mode."&action=uninstall&path=".$var['plugin_path']."&e-token=".defset('e_TOKEN')."\" title='" . EPL_ADLAN_1 . "'  >" . ADMIN_UNINSTALLPLUGIN_ICON . "</a>";
			}
			else
			{
				$text .= "<a class='btn btn-default' href=\"" . e_SELF . "?mode=installed&action=install&path=".$var['plugin_path']."&e-token=".defset('e_TOKEN')."\" title='" . EPL_ADLAN_0 . "' >" . ADMIN_INSTALLPLUGIN_ICON . "</a>";
			}

		}
	//	else
	//	{
/*			if($var['menuName'])
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
			}*/
	//	}

		if($var['plugin_version'] != $var['plugin_version_file'] && $var['plugin_installflag'])
		{
			$text .= "<a class='btn btn-default' href='" . e_SELF . "?mode=".$mode."&action=upgrade&path=".$var['plugin_path']."&e-token=".defset('e_TOKEN')."' title=\"" . EPL_UPGRADE . " v" . $var['plugin_version_file'] . "\" >" . ADMIN_UPGRADEPLUGIN_ICON . "</a>";
		}

		if($var['plugin_installflag'])
		{
			$text .= "<a class='btn btn-default' href='" . e_SELF . "?mode=".$mode."&action=repair&path=".$var['plugin_path']."&e-token=".defset('e_TOKEN')."' title='" . LAN_REPAIR_PLUGIN_SETTINGS . "'> " . ADMIN_REPAIRPLUGIN_ICON . "</a>";
		}

		if($var['plugin_installflag'] && is_dir($_path . ".git"))
		{
			$text .= "<a class='plugin-manager btn btn-default' href='" . e_SELF . "?mode=".$mode."&action=pull&path=".$var['plugin_path']."&e-token=".defset('e_TOKEN')."' title='" . LAN_SYNC_WITH_GIT_REPO . "'> " . ADMIN_GITSYNC_ICON . "</a>";
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
		protected $pid				= 'plugin_id';
		protected $perPage			= 10;
		protected $batchDelete		= true;
		protected $batchExport     = true;
		protected $batchCopy		= true;

		protected $grid             = array();

	//	protected $sortField		= 'somefield_order';
	//	protected $orderStep		= 10;
	//	protected $tabs				= array('Tabl 1','Tab 2'); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable.

	//	protected $listQry      	= "SELECT * FROM `#tableName` WHERE field != '' "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.

		protected $listOrder		= '';

		protected $fields 		= array ();

		protected $fieldpref = array('plugin_icon', 'plugin_name', 'plugin_version',  'plugin_description', 'plugin_license', 'plugin_compatible', 'plugin_date','plugin_author', 'plugin_category','plugin_installflag');


	//	protected $preftabs        = array('General', 'Other' );
		protected $prefs = array(
		);

		/** @var e_marketplace  */
		protected $mp = null;


		public function __construct($request, $response, $params = array())
		{

			$this->fields = plugman_adminArea::getPluginManagerFields();
			unset($this->fields['checkboxes']);
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
			if(empty($_GET['e-token']))
			{
				echo e107::getMessage()->addError("Invalid Token")->render('default', 'error');
				return null;
			}

			$frm = e107::getForm();
			$mes = e107::getMessage();
			$tp = e107::getParser();


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
					$mes->addSuccess("<a target='_top' href='".e_ADMIN."plugin.php?mode=installed&action=upgrade&id=".$pluginFolder."&e-token=".defset('e_TOKEN')."' class='btn btn-primary'>".LAN_UPDATE."</a>");
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
            $this->setupGridData();
             $this->setPlugData();
            parent::ListObserver();

        }

		public function ListAjaxObserver()
		{
			parent::ListAjaxObserver();
			$this->setPlugData();
		}

		public function GridAjaxObserver()
		{
			$this->setupGridData();
			$this->setPlugData();

			parent::GridAjaxObserver();
		}


		public function GridObserver()
		{
			$this->setupGridData();
			$this->setPlugData();


			parent::GridObserver();


		}

		private function setupGridData()
		{

			$this->fields['plugin_description']['readParms'] = 'expand=0&truncate=1800&bb=1';
			$this->fields['plugin_license']['class'] = 'right';

			$this->grid = array(
				'price'    => 'plugin_license',
				'title'    => 'plugin_name',
				'image'    => 'plugin_icon',
				'date'      => 'plugin_date',
				'body'     => 'plugin_description',
				'version'   => 'plugin_version',
				'class'    => 'col-md-6 col-lg-4',
				'author'    => 'plugin_author',
				'perPage'  => 6,
				'carousel' => true
			);


			$this->grid['template'] = '

				 <div class="panel panel-primary" style="height:190px" >
				 	<table class="table" style="height:180px;display:block" >
				 	<tr>
				 	<td style="width:25%">
					<div class="text-center" style="height:90px;">{IMAGE}
					</div>
					</td>
					<td><h4>{TITLE} <small> v{VERSION} {PRICE}</small></h4>
					<div style="height:100px; overflow:hidden">{BODY}</div>
					<div><small class="text-muted"><i class="fa fa-user"></i> {AUTHOR} <i>{DATE}</i></small> <span class="pull-right">&nbsp; {OPTIONS}</span></div>
					</td></tr>
					</table>
					
				</div>';

			$this->perPage = 180;

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

	//	$dicon = "<a data-toggle='modal' data-bs-toggle='modal' data-modal-caption=\"Downloading ".$data['plugin_name']." ".$data['plugin_version']."\" href='{$url}' data-cache='false' data-target='#uiModal' title='".$LAN_DOWNLOAD."' ><img class='top' src='".e_IMAGE_ABS."icons/download_32.png' alt=''  /></a> ";

		return "<div id='{$id}' class='center' >
		{$dicon}
		</div>";
	}

	private function truncateSentence($string, 	$limit = 120 )
	{
		if(strlen($string) <= $limit)
		{
			$text = nl2br($string);
			return $string;
		}

		$tmp = explode(".", $string);

		$chars = 0;

		$arr = array();
		foreach($tmp as $line)
		{
			$line = str_replace("\n", '', trim($line));
			$len = strlen($line);

			if($chars >= $limit)
			{
				break;
			}

			$arr[] = $line;
			$chars += $len;

		}

		$text = implode('. ', $arr).'.';

		$text = nl2br($text);

		return $text;

	}



	private function setPlugData()
	{

		$from = $this->getQuery('from', 0);
		$srch = $this->getQuery('searchquery');
		//	$srch = preg_replace('/[^\w]/','', vartrue($_GET['srch']));

		$mp = $this->getMarketplace();
		$cat = '';

		if($filter = $this->getQuery('filter_options'))
		{
			list($bla, $cat) = explode("__",$filter);
		}


		// do the request, retrieve and parse data
		$xdata = $mp->call('getList', array(
			'type'   => 'plugin',
			'params' => array('limit' => $this->perPage, 'search' => $srch, 'from' => $from, 'cat'=>$cat)
		));

		$total = (int) $xdata['params']['count'];

	//	e107::getDebug()->log($xdata);

		$tree = $this->getTreeModel();
		$tree->setTotal($total);
		$tp = e107::getParser();


		foreach($xdata['data'] as $id => $row)
		{

			$v['id'] = $id;

			$model = new e_model($v);
			$tree->setNode($id, $model);

			$badge = $this->compatibilityLabel($row['compatibility']);
			$featured = ($row['featured'] == 1) ? " <span class='label label-info'>" . EPL_ADLAN_91 . "</span>" : '';
			$price = (!empty($row['price'])) ? "<span class='label label-primary'>" . $row['price'] . " " . $row['currency'] . "</span>" : "<span class='label label-success'>" . EPL_ADLAN_93 . "</span>";

			$node = array(
				'plugin_id'          => $row['params']['id'],
				'plugin_mode'        => $row['params']['mode'],
				'plugin_icon'        => vartrue($row['icon'], e_IMAGE."logo_template.png"),
				'plugin_name'        => stripslashes($row['name']),
				'plugin_description' => $this->truncateSentence(vartrue($row['description'])),
				'plugin_featured'    => $featured,
				'plugin_sef'         => '',
				'plugin_folder'      => $row['folder'],
				'plugin_path'        => $row['folder'],
				'plugin_date'        => $tp->toDate(strtotime($row['date']), 'relative'),
				'plugin_category'    => vartrue($row['category'], 'n/a'),
				'plugin_author'      => vartrue($row['author']),
				'plugin_version'     => $row['version'],

				'plugin_compatible' => $row['compatibility'], // $badge,

				'plugin_website'     => vartrue($row['authorUrl']),
				'plugin_url'         => $row['urlView'],
				'plugin_notes'       => '',
				'plugin_price'       => $row['price'],
				'plugin_license'     => $price,
				'plugin_installflag' => e107::isInstalled($row['folder']),
				'options'       => $row,
			);

			$model->setData($node);

		}


	}


	public function listoldPage()
	{

		//	e107SiteUsername
		global $plugin;
		$tp = e107::getParser();
		$frm = $this->getUI();

		$caption = EPL_ADLAN_89;

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
		$srch = preg_replace('/[^\w]/', '', vartrue($_GET['srch']));


		$mp = $this->getMarketplace();

		// auth
		//	$mp->generateAuthKey($e107SiteUsername, $e107SiteUserpass);

		// do the request, retrieve and parse data
		$xdata = $mp->call('getList', array(
			'type'   => 'plugin',
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

			$badge = $this->compatibilityLabel($row['compatibility']);
			$featured = ($row['featured'] == 1) ? " <span class='label label-info'>" . EPL_ADLAN_91 . "</span>" : '';
			$price = (!empty($row['price'])) ? "<span class='label label-primary'>" . $row['price'] . " " . $row['currency'] . "</span>" : "<span class='label label-success'>" . EPL_ADLAN_93 . "</span>";

			$data[] = array(
				'plugin_id'          => $row['params']['id'],
				'plugin_mode'        => $row['params']['mode'],
				'plugin_icon'        => vartrue($row['icon'], 'e-plugins-32'),
				'plugin_name'        => stripslashes($row['name']),
				'plugin_featured'    => $featured,
				'plugin_sef'         => '',
				'plugin_folder'      => $row['folder'],
				'plugin_path'        => $row['folder'],
				'plugin_date'        => vartrue($row['date']),
				'plugin_category'    => vartrue($row['category'], 'n/a'),
				'plugin_author'      => vartrue($row['author']),
				'plugin_version'     => $row['version'],
				'plugin_description' => nl2br(vartrue($row['description'])),
				'plugin_compatible'  => $badge,

				'plugin_website'     => vartrue($row['authorUrl']),
				'plugin_url'         => $row['urlView'],
				'plugin_notes'       => '',
				'plugin_price'       => $row['price'],
				'plugin_license'     => $price,
				'plugin_installflag' => e107::isInstalled($row['folder'])
			);

			$c++;
		}

		$fieldList = $this->fields;
		unset($fieldList['checkboxes']);

		$text = "
				<form class='form-search form-inline' action='" . e_SELF . "?" . e_QUERY . "' id='core-plugin-list-form' method='get'>
				<div id='admin-ui-list-filter' class='e-search '>" . $frm->search('srch', $srch, 'go') . $frm->hidden('mode', 'online') . "
				</div>
				</form>

				<form action='" . e_SELF . "?" . e_QUERY . "' id='core-plugin-list-form' method='post'>
					<fieldset class='e-filter' id='core-plugin-list'>
						<legend class='e-hideme'>" . $caption . "</legend>





						<table id=core-plugin-list' class='table adminlist table-striped'>
							" . $frm->colGroup($fieldList, $this->fieldpref) .
			$frm->thead($fieldList, $this->fieldpref) . "
							<tbody>
			";


		foreach($data as $key => $val)
		{
			//	print_a($val);
			$text .= "<tr>";

			foreach($this->fields as $v => $foo)
			{
				if(!in_array($v, $this->fieldpref) || $v == 'checkboxes' || $v === 'options')
				{
					continue;
				}

				$_value = $val[$v];
				if($v == 'plugin_name')
				{
					$_value .= $val['plugin_featured'];
				}
				// echo '<br />v='.$v;
				$text .= "<td style='height: 40px' class='" . vartrue($this->fields[$v]['class'], 'left') . "'>" . $frm->renderValue($v, $_value, $this->fields[$v], $key) . "</td>\n";
			}
			$text .= "<td class='center'>" . $this->options($val) . "</td>";
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
			$parms = $total . "," . $this->perPage . "," . $from . "," . e_SELF . '?mode=online&amp;action=list&amp;frm=[FROM]';

			if(!empty($srch))
			{
				$parms .= '&amp;srch=' . $srch;
			}

			$text .= "<div class='control-group form-inline input-inline' style='text-align:center;margin-top:10px'>" . $tp->parseTemplate("{NEXTPREV=$parms}") . "</div>";
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


	function plugin_icon($curVal, $mode)
	{
		return e107::getParser()->toIcon($curVal);
	}


	function options($bla, $data)
	{
		$action = $this->getController()->getAction();

		if(e107::isInstalled($data['folder']))
		{
			if($action === 'grid')
			{
				return "<button class='btn btn-sm btn-default btn-secondary' disabled>".LAN_INSTALLED."</button>";
			}

			return '&nbsp; <span class="label label-default">'.LAN_INSTALLED."</span>";
			return null;
		}


		$id = 'plug_'.$data['params']['id'];
		$modalCaption = (!empty($data['price'])) ? EPL_ADLAN_92." ".$data['name']." ".$data['version'] : EPL_ADLAN_230." ".$data['name']." ".$data['version'];

		$srcData = array(
			'plugin_id'     => $data['params']['id'],
			'plugin_folder' => $data['folder'],
			'plugin_price'  => $data['price'],
			'plugin_mode'   =>  'addon',
			'plugin_url'    => $data['url'],
		);

		$url = $this->getController()->getMarketplace()->getDownloadModal('plugin', $data);

		$button = ADMIN_INSTALLPLUGIN_ICON;
		$class = 'btn btn-sm btn-default btn-secondary';
		$disable = '';
		$title = EPL_ADLAN_237;
		$tp = e107::getParser();

		if($action === 'grid')
		{
			$button = e107::getParser()->toGlyph('fa-bolt').ADLAN_121; // Install
			$class = 'btn btn-sm btn-primary';

			$version = $tp->filter(e_VERSION,'version');
			$compat = (float)  $tp->filter($data['compatibility'], 'version');

			if($compat == 2)
			{
				$compat = $version;
			}


			if(!e107::isCompatible($compat, 'plugin'))
			{
				$button = e107::getParser()->toGlyph('fa-bolt').ADLAN_121;
				$class = 'btn btn-sm btn-warning';
			//	$disable = 'data-confirm="This plugin may not be compatible with your version of e107. Are you sure?"';
				$title = "Install: May not be compatible";
			}
		}


		return '<a title="'.$title.'" '.$disable.' class="e-modal '.$class.'" href="'.$url.'" rel="external" data-loading="'.e_IMAGE.'/generic/loading_32.gif"  data-cache="false" data-modal-caption="'.$modalCaption.'"  target="_blank" >'.$button.'</a>';
	//	$dicon = "<a data-toggle='modal' data-bs-toggle='modal' data-modal-caption=\"Downloading ".$data['plugin_name']." ".$data['plugin_version']."\" href='{$url}' data-cache='false' data-target='#uiModal' title='".LAN_DOWNLOAD."' ><img class='top' src='".e_IMAGE_ABS."icons/download_32.png' alt=''  /></a> ";


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

			$sim =  (array) $this->findSimilar($data);

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

			//	if(substr($row,0,2) == '/*')
			//	{
				//	$skip =true; ;

			//	}
			//	if(substr($row,0,2) == '*/')
			//	{
				//	$skip =false;
				//	continue;
			//	}

				if(empty($row) /*|| $skip == true*/ || substr($row,0,5) == '<?php' || substr($row,0,2) == '?>' || substr($row,0,2)=='//')
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

new plugman_adminArea();
require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();
require_once(e_ADMIN."footer.php");



























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
		    e107::getMessage()->addInfo("<b>".$tp->toHTML($this->plug['plugin_name'],false,'TITLE')."</b> is ready to be upgraded. (see below)"); // TODO LAN
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






