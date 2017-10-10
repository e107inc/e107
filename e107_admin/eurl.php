<?php
/*
 * e107 website system
 *
 * Copyright (C) e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * URL and front controller Management
 *
*/

require_once('../class2.php');
if (!getperms('K'))
{
	e107::redirect('admin');
	exit;
}

e107::coreLan('eurl', true);
// TODO - admin interface support, remove it from globals
$e_sub_cat = 'eurl';

e107::css('inline', " span.e-help { cursor: help } ");

class eurl_admin extends e_admin_dispatcher
{
	protected $modes = array(
		'main' => array(
			'controller' 	=> 'eurl_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'eurl_admin_form_ui',
			'uipath' 		=> null
		)
	);

	protected $adminMenu = array(
		'main/config'		=> array('caption'=> LAN_EURL_MENU_PROFILES, 'perm' => 'L'),
		'main/alias' 		=> array('caption'=> LAN_EURL_MENU_ALIASES, 'perm' => 'L'),
		'main/simple' 		=> array('caption'=> LAN_EURL_MENU_CONFIG, 'perm' => 'L'),
		'main/settings' 	=> array('caption'=> LAN_EURL_MENU_SETTINGS, 'perm' => 'L'),

	//	'main/help' 		=> array('caption'=> LAN_EURL_MENU_HELP, 'perm' => 'L'),
	);

	protected $adminMenuAliases = array();
	
	protected $defaultAction = 'config';

	protected $menuTitle = LAN_EURL_MENU;

	protected $adminMenuIcon = 'e-eurl-24';
}

class eurl_admin_ui extends e_admin_controller_ui
{
	public $api;
	
	protected $prefs = array(
		'url_disable_pathinfo'	=> array('title'=>LAN_EURL_SETTINGS_PATHINFO,	'type'=>'boolean', 'help'=>LAN_EURL_MODREWR_DESCR),
		'url_main_module'	=> array('title'=>LAN_EURL_SETTINGS_MAINMODULE,	'type'=>'dropdown', 'data' => 'string', 'help'=>LAN_EURL_SETTINGS_MAINMODULE_HELP),
		'url_error_redirect'	=> array('title'=>LAN_EURL_SETTINGS_REDIRECT,	'type'=>'boolean', 'help'=>LAN_EURL_SETTINGS_REDIRECT_HELP),
		'url_sef_translate'	=> array('title'=>LAN_EURL_SETTINGS_SEFTRANSLATE,	'type'=>'dropdown', 'data' => 'string', 'help'=>LAN_EURL_SETTINGS_SEFTRANSLATE_HELP),
	);
	
	public function init()
	{
		if(e_AJAX_REQUEST)
		{
			$tp = e107::getParser();

			if(!empty($_POST['pk']) && !empty($_POST['value']))
			{
				$cfg = e107::getConfig();

				list($plug,$key) = explode("|", $_POST['pk']);

				if(is_string($cfg->get('e_url_alias')))
				{
					$cfg->setPostedData('e_url_alias', array(e_LAN => array($plug => array($key => $tp->filter($_POST['value']))) ), false);
				}
				else
				{
					$cfg->setPref('e_url_alias/'.e_LAN.'/'.$plug."/".$key, $tp->filter($_POST['value']));
				}

				$cfg->save(true, true, true);
			}


		//	file_put_contents(e_LOG."e_url.log", print_r($cfg->get('e_url_alias'),true));

			exit;

		}

		$htaccess = file_exists(e_BASE.".htaccess");

		if(function_exists('apache_get_modules'))
		{
			$modules = apache_get_modules();
			$modRewrite = in_array('mod_rewrite', $modules );
		}
		else
		{
			$modRewrite = true; //we don't really know.

		}

		if($modRewrite === false)
		{
			e107::getMessage()->addInfo("Apache mod_rewrite was not found on this server and is required to use this feature. ");
			e107::getMessage()->addDebug(print_a($modules,true));

		}

		if($htaccess && $modRewrite && !deftrue('e_MOD_REWRITE'))
		{
			e107::getMessage()->addInfo("Mod-rewrite is disabled. To enable, please add the following line to your <b>e107_config.php</b> file:<br /><pre>define('e_MOD_REWRITE',true);</pre>");
		}
	
		if(is_array($_POST['rebuild']))
		{
			$table = key($_POST['rebuild']);
			list($primary, $input, $output) = explode("::",$_POST['rebuild'][$table]);
			$this->rebuild($table, $primary, $input, $output);	
		}




		
		
		$this->api = e107::getInstance();
		$this->addTitle(LAN_EURL_NAME);
		
		if($this->getAction() != 'settings') return;
		
	
		

	}
	
	/**
	 * Rebuild SEF Urls for a particular table
	 * @param $table
	 * @param primary field id. 
	 * @param input field (title)
	 * @param output field (sef)
	 */
	private function rebuild($table, $primary='', $input='',$output='')
	{
		if(empty($table) || empty($input) || empty($output) || empty($primary))
		{
			e107::getMessage()->addError("Missing Generator data");	
			return;
		}
		
		$sql = e107::getDb();
		
		$data = $sql->retrieve($table, $primary.",".$input, $input ." != '' ", true);
		
		$success = 0;
		$failed = 0;
		
		foreach($data as $row)
		{
			$sef = eHelper::title2sef($row[$input]);
			
			if($sql->update($table, $output ." = '".$sef."' WHERE ".$primary. " = ".intval($row[$primary]). " LIMIT 1")!==false)
			{
				$success++;
			}
			else
			{
				$failed++;
			}
			
			// echo $row[$input]." => ".$output ." = '".$sef."'  WHERE ".$primary. " = ".intval($row[$primary]). " LIMIT 1 <br />";

		}
			
		if($success)
		{
			e107::getMessage()->addSuccess(LAN_EURL_TABLE.": <b>".$table."</b><br />".$success. LAN_EURL_SURL_UPD);
		}
		
		if($failed)
		{
			e107::getMessage()->addError(LAN_EURL_TABLE.": <b>".$table."</b><br />".$failed. LAN_EURL_SURL_NUPD);
		}
		
		
	}
	
	
	
	
	public function HelpObserver()
	{
		
	}
	
	public function HelpPage()
	{
		$this->addTitle(LAN_EURL_NAME_HELP);
		return LAN_EURL_UC;
	}

	//TODO Checkbox for each plugin to enable/disable
	protected function simplePage()
	{
		// $this->addTitle("Simple Redirects");
		$eUrl =e107::getUrlConfig();
		$frm = e107::getForm();
		$tp = e107::getParser();
		$cfg = e107::getConfig();



		if(!empty($_POST['saveSimpleSef']))
		{
			/*if(is_string($this->getConfig()->get('e_url_alias')))
			{
				$cfg->setPostedData('e_url_alias', array(e_LAN => $_POST['e_url_alias']), false);
			}
			else
			{
				$cfg->setPref('e_url_alias/'.e_LAN, $_POST['e_url_alias']);
			}*/

			foreach($_POST['urlstatus'] as $k=>$v)
			{
				$val = (!empty($v)) ? $tp->filter($k,'w') : 0;
				$cfg->setPref('e_url_list/'.$k, $val);
			}

			$cfg->save(true, true, true);

		}

		$pref = e107::getPref('e_url_alias');
		$sefActive = e107::getPref('e_url_list');

		if(empty($eUrl))
		{
			return false;
		}

		$text = "<div class='e-container'>";
		$text .= $frm->open('simpleSef');

		$multilan = "<small class='e-tip admin-multilanguage-field' style='cursor:help; padding-left:10px' title='Multi-language field'>".$tp->toGlyph('fa-language')."</small>";

		$home = "<small>".SITEURL.'</small>';


		//  e107::getDebug()->log($sefActive);

		$plg = e107::getPlug();

		foreach($eUrl as $plug=>$val)
		{

			$plg->load($plug);

			$active = !empty($sefActive[$plug]) ? true : false;
			$text .= "<table class='table table-striped table-bordered' style='margin-bottom:40px'>
			<colgroup>
				<col style='min-width:220px' />
				<col style='width:45%' />
				<col style='width:45%' />
			</colgroup>";

			$name = 'urlstatus['.$plug.']';

			$switch = $frm->radio_switch($name, $active, LAN_ON, LAN_OFF, array(
				'switch' => 'mini',
			));

			$text .= "<tr class='active'><td><h4>" . $plg->getName() . "</h4></td><td colspan='2'>" . $switch . "</td></tr>";
			$text .= "<tr><th>".LAN_EURL_KEY."</th><th>".LAN_EURL_REGULAR_EXPRESSION."</th>


			<th>".LAN_URL."</th>
			</tr>";
			
			foreach($val as $k=>$v)
			{

					$alias          = vartrue($pref[e_LAN][$plug][$k], $v['alias']);
				//	$sefurl         = (!empty($alias)) ? str_replace('{alias}', $alias, $v['sef']) : $v['sef'];
					$pid            = $plug."|".$k;

					$v['regex'] =   preg_replace("/^\^/",$home,$v['regex']);
					$aliasForm      = $frm->renderInline('e_url_alias['.$plug.']['.$k.']', $pid, 'e_url_alias['.$plug.']['.$k.']', $alias, $alias,'text',null,array('title'=>LAN_EDIT." (Language-specific)", 'url'=>e_REQUEST_SELF));
					$aliasRender    = str_replace('{alias}', $aliasForm, $v['regex']);

					$text .= "<tr>
					<td >".$k."</td>
					<td >".$aliasRender."</td>

					<td >". $v['redirect']."</td>
					</tr>";
			}
		
					
			$text .= "</table>";
		}	

		$text .= "<div class='buttons-bar center'>".$frm->button('saveSimpleSef',LAN_SAVE, 'submit')."</div>";
		$text .= $frm->close();
		$text .= "</div>";
		return $text;		
	}
		
	
	public function SettingsObserver()
	{
		// main module pref dropdown
		$this->prefs['url_main_module']['writeParms'][''] = 'None';

		// e_url.php aliases
		$tmp = e107::getUrlConfig('alias');
		foreach($tmp as $plugin=>$alias)
		{
			$this->prefs['url_main_module']['writeParms'][$alias] = eHelper::labelize($plugin);
		}

		// legacy URL (news, pages )
		$modules = e107::getPref('url_config', array());

		foreach ($modules as $module => $location) 
		{
			$labels = array();
			$obj = eDispatcher::getConfigObject($module, $location); 
			if(!$obj) continue;
			$config = $obj->config();
			if(!$config || !vartrue($config['config']['allowMain'])) continue;
			$admin = $obj->admin();
			$labels = vartrue($admin['labels'], array());

			$this->prefs['url_main_module']['writeParms'][$module] = vartrue($section['name'], eHelper::labelize($module));
		}

		ksort($this->prefs['url_main_module']['writeParms']);
		
		// title2sef transform type pref  
		$types = explode('|', 'none|dashl|dashc|dash|underscorel|underscorec|underscore|plusl|plusc|plus');
		$this->prefs['url_sef_translate']['writeParms'] = array();
		foreach ($types as $type) 
		{
			$this->prefs['url_sef_translate']['writeParms'][$type] = deftrue('LAN_EURL_SETTINGS_SEFTRTYPE_'.strtoupper($type), ucfirst($type));
		}
		
		if(isset($_POST['etrigger_save']))
		{
			$this->getConfig()
						->setPostedData($this->getPosted(), null, false)
						//->setPosted('not_existing_pref_test', 1)
						->save(true);
		
			$this->getConfig()->setMessages();
		}
	}
	
	protected function SettingsPage()
	{
		//$this->addTitle(LAN_EURL_NAME_SETTINGS);
		return $this->getUI()->urlSettings();
	}
	
	public function AliasObserver()
	{
		if(isset($_POST['update']))
		{
			$posted = is_array($_POST['eurl_aliases']) ? e107::getParser()->post_toForm($_POST['eurl_aliases']) : '';
			$locations = array_keys(e107::getPref('url_locations', array()));
			$aliases = array();
			$message = e107::getMessage();
			
			foreach ($posted as $lan => $als) 
			{
				foreach ($als as $module => $alias) 
				{
					$alias = trim($alias);
					$module = trim($module);
					if($module !== $alias) 
					{
						$cindex = array_search($module, $locations);
						$sarray = $locations;
						unset($sarray[$cindex]);
						
						if(!in_array(strtolower($alias), $sarray)) $aliases[$lan][$alias] = $module;
						else $message->addError(sprintf(LAN_EURL_ERR_ALIAS_MODULE, $alias, $module));
					}
				}
			}
			e107::getConfig()->set('url_aliases', e107::getParser()->post_toForm($aliases))->save(false);
		}
	}
	
	protected function AliasPage()
	{
	//	$this->addTitle(LAN_EURL_NAME_ALIASES);
		
		$aliases = e107::getPref('url_aliases', array());
		
		$form = $this->getUI();
		$text = "
			<form action='".e_SELF."?mode=main&action=alias' method='post' id='urlconfig-form'>
				<fieldset id='core-eurl-core'>
					<legend>".LAN_EURL_LEGEND_ALIASES."</legend>
					<table class='table adminlist'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
		";
		
		$text .= $this->renderAliases($aliases);
		
		$text .= "
						</tbody>
					</table>
					<div class='buttons-bar center'>
						".$form->admin_button('update', LAN_UPDATE, 'update')."
					</div>
				</fieldset>
			</form>
		";
		
		return $text;
	}
	
	public function ConfigObserver()
	{

		if(!empty($_POST['generate']))
		{
			$gen = e107::getUrlConfig('generate');
			$id = key($_POST['generate']);

			if(empty($gen[$id]))
			{
				e107::getMessage()->addDebug("Empty");
				return null;
			}

			foreach($gen[$id] as $conf)
			{
				$this->rebuild($conf['table'], $conf['primary'], $conf['input'], $conf['output']);
			}

		}


		if(isset($_POST['update']))
		{
			$config = is_array($_POST['eurl_config']) ? e107::getParser()->post_toForm($_POST['eurl_config']) : '';
			$modules = eRouter::adminReadModules();
			$locations = eRouter::adminBuildLocations($modules);
			
			$aliases = eRouter::adminSyncAliases(e107::getPref('url_aliases'), $config);

			if(!empty($_POST['eurl_profile']))
			{
				e107::getConfig()->set('url_profiles', $_POST['eurl_profile']);
			//	unset($locations['download']);
			//	unset($config['download']);
			}

			e107::getConfig()
				->set('url_aliases', $aliases)
				->set('url_config', $config)
				->set('url_modules', $modules)
				->set('url_locations', $locations)
				->save();

			if(!empty($_POST['eurl_config']['gallery'])) // disabled, so disable e_url on index also.
			{
				$val = ($_POST['eurl_config']['gallery'] === 'plugin') ? 0 : 'gallery';
				e107::getConfig()->setPref('e_url_list/gallery', $val)->save(false,true,false);
			}

			if(!empty($_POST['eurl_config']['news'])) // disabled, so disable e_url on index also.
			{
				$val = ($_POST['eurl_config']['news'] === 'core') ? 0 : 'news';
				e107::getConfig()->setPref('e_url_list/news', $val)->save(false,true,false);
			}

		//	var_dump($_POST['eurl_config']);

				
			eRouter::clearCache();
			e107::getCache()->clearAll('content'); // clear content - it may be using old url scheme.

		}
	}
	
	protected function ConfigPage()
	{
		// $this->addTitle(LAN_EURL_NAME_CONFIG);
		$active = e107::getPref('url_config');

		$set = array();
		// all available URL modules
		$set['url_modules'] = eRouter::adminReadModules();
		// set by user URL config locations
		$set['url_config'] = eRouter::adminBuildConfig($active, $set['url_modules']);
		// all available URL config locations
		$set['url_locations'] = eRouter::adminBuildLocations($set['url_modules']);
		
		$form = $this->getUI();
		$text = "
			<form action='".e_SELF."?mode=main&action=config' method='post' id='urlconfig-form'>
				<fieldset id='core-eurl-core'>
					<legend>".LAN_EURL_LEGEND_CONFIG."</legend>
					<table class='table adminlist'>
						<colgroup>
							<col class='col-label' style='width:20%' />
							<col class='col-control' style='width:60%' />
							<col style='width:20%' />
						</colgroup>
						<thead>
						  <tr>
						      <th>".LAN_TYPE."</th>
						      <th>".LAN_URL."</th>
						      <th>".LAN_OPTIONS."</th>
						  </tr>
						</thead>
						
						
						<tbody>
		";


		$text .=  $this->renderProfiles();


		$text .= $this->renderConfig($set['url_config'], $set['url_locations']);
		
		$text .= "
						</tbody>
					</table>
					<div class='buttons-bar center'>
						".$form->admin_button('update', LAN_UPDATE, 'update')."
					</div>
				</fieldset>
			</form>
		";
		
		return $text;
	}

	/**
	 * New in v2.1.6
	 */
	private function renderProfiles()
	{

		$PLUGINS_DIRECTORY = e107::getFolder("PLUGINS");
        $srch = array("{SITEURL}","{e_PLUGIN_ABS}");
        $repl = array(SITEURL,SITEURL.$PLUGINS_DIRECTORY);

		$profiles = e107::getUrlConfig('profiles');
		$generate = e107::getUrlConfig('generate');

		$form = $this->getUI();

		$text = '';

		$active = e107::getPref('url_profiles');

		foreach($profiles as $plug=>$prof)
		{
			$arr = array();
			foreach($prof as $id=>$val)
			{
				$arr[$id] = $val['label'].": ". str_replace($srch,$repl,$val['examples'][0]);
			}

			$sel = $active[$plug];

			$selector = $form->select('eurl_profile['.$plug.']',$arr,$sel, array('size'=>'block-level'));

			$label = e107::getPlugLan($plug,'name');

			$text .= "<tr><td>".$label."</td><td>".$selector."</td><td>";




			$text .= (!empty($generate[$plug])) ? $form->admin_button('generate['.$plug.']', $plug,'delete', LAN_EURL_REBUILD) : "";

			$text .= "</td></tr>";

		}

		return $text;

	}

	public function renderConfig($current, $locations)
	{

		$ret = array();
		$url = e107::getUrl();

		ksort($locations);

		unset($locations['forum'],$locations['faqs'], $locations['pm']); // using new system so hide from here.

		foreach ($locations as $module => $l) 
		{
			$data = new e_vars(array(
				'current' => $current,
			));
			$obj = eDispatcher::getConfigObject($module, $l[0]);
			if(null === $obj) $obj = new eurlAdminEmptyConfig;

			$data->module = $module;
			$data->locations = $l;
			$data->defaultLocation = $l[0];
			$data->config = $obj;

			$ret[] = $data;
		}



		return $this->getUI()->moduleRows($ret);
	}
	

	public function renderAliases($aliases)
	{

		$ret = array();
		$lans = array();
		
		$lng = e107::getLanguage();
		$lanList = $lng->installed();
		sort($lanList);
		
		$lanDef = e107::getPref('sitelanguage') ? e107::getPref('sitelanguage') : e_LANGUAGE;
		$lanDef = array($lng->convert($lanDef), $lanDef);
		
		foreach ($lanList as $index => $lan) 
		{
			$lanCode = $lng->convert($lan);
			if($lanDef[0] == $lanCode) continue;
			$lans[$lanCode] = $lan;
		}
		
		$modules = e107::getPref('url_config');
		if(!$modules)
		{
			$modules = array();
			e107::getConfig()->set('url_aliases', array())->save(false);
			// do not output message
			e107::getMessage()->reset(false, 'default');
		}
		
		foreach ($modules as $module => $location) 
		{
			$data = new e_vars();
			$obj = eDispatcher::getConfigObject($module, $location);
			if(null === $obj) $obj = new eurlAdminEmptyConfig;

			$data->module = $module;
			$data->location = $location;
			$data->config = $obj;
			$modules[$module] = $data;
		}
		
		return $this->getUI()->aliasesRows($aliases, $modules, $lanDef, $lans);
	}
	
	
	/**
	 * Set extended (UI) Form instance
	 * @return e_admin_ui
	 */
	public function _setUI()
	{
		$this->_ui = $this->getParam('ui');
		$this->setParam('ui', null);
		
		return $this;
	}
	
	/**
	 * Set Config object
	 * @return e_admin_ui
	 */
	protected function _setConfig()
	{
		$this->_pref = e107::getConfig();

		$dataFields = $validateRules = array();
		foreach ($this->prefs as $key => $att)
		{
			// create dataFields array
			$dataFields[$key] = vartrue($att['data'], 'string');

			// create validation array
			if(vartrue($att['validate']))
			{
				$validateRules[$key] = array((true === $att['validate'] ? 'required' : $att['validate']), varset($att['rule']), $att['title'], varset($att['error'], $att['help']));
			}
			/* Not implemented in e_model yet
			elseif(vartrue($att['check']))
			{
				$validateRules[$key] = array($att['check'], varset($att['rule']), $att['title'], varset($att['error'], $att['help']));
			}*/
		}
		$this->_pref->setDataFields($dataFields)->setValidationRules($validateRules);

		return $this;
	}
}

class eurl_admin_form_ui extends e_admin_form_ui
{
	public function urlSettings()
	{
		return $this->getSettings();
	}
    
    
    
    public function moreInfo($title,$info)
    {
        $tp = e107::getParser();
       
        $id = 'eurl_'.$this->name2id($title);
        
        $text = "<a data-toggle='modal' href='#".$id."' data-cache='false' data-target='#".$id."' class='e-tip' title='".LAN_MOREINFO."'>";
        $text .= $title;  
        $text .= '</a>';
        
        $text .= '

         <div id="'.$id.'" class="modal fade" tabindex="-1" role="dialog"  aria-hidden="true">
            <div class="modal-dialog modal-lg">
				<div class="modal-content">
	                <div class="modal-header">
	                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	               <h4>'.$tp->toHtml($title,false,'TITLE').'</h4>
	                </div>
	                <div class="modal-body">
	                <p>';
        
        $text .= $info;
       
                
        $text .= '</p>
                </div>
                <div class="modal-footer">
                <a href="#" data-dismiss="modal" class="btn btn-primary">'.LAN_CLOSE.'</a>
                </div>
                </div>
                </div></div>';
        
        return $text;
        
    }
    
    
    
    
	
	public function moduleRows($data)
	{
		$text = '';
		$tp = e107::getParser();
		$frm = e107::getForm();

		if(empty($data))
		{
			return "
				<tr>
					<td colspan='2'>".LAN_EURL_EMPTY."</td>
				</tr>
			";
		}
		
        $PLUGINS_DIRECTORY = e107::getFolder("PLUGINS");
        $srch = array("{SITEURL}","{e_PLUGIN_ABS}");
        $repl = array(SITEURL,SITEURL.$PLUGINS_DIRECTORY);


        
		foreach ($data as $obj) 
		{
			$admin 		= $obj->config->admin();
			$section 	= vartrue($admin['labels'], array());
            $rowspan 	= count($obj->locations)+1;
            $module 	= $obj->module;
			$generate 	= vartrue($admin['generate'], array());
           
          /*
			$info .= "
                <tr>
                    <td rowspan='$rowspan'><a class='e-tip' style='display:block' title='".LAN_EURL_LOCATION.$path."'>
                    ".vartrue($section['name'], eHelper::labelize($obj->module))."
                    </a></td>
               </tr>
            ";
          */
            $opt = "";   
			$info = "<table class='table table-striped'>";



            
			foreach ($obj->locations as $index => $location) 
			{
				$objSub = $obj->defaultLocation != $location ? eDispatcher::getConfigObject($obj->module, $location) : false; 
				if($objSub) 
				{
					$admin = $objSub->admin();
					$section = vartrue($admin['labels'], array());
				} 
				elseif($obj->defaultLocation != $location) $section = array();
				
				$id = 'eurl-'.str_replace('_', '-', $obj->module).'-'.$index;
				
				$checked = varset($obj->current[$module]) == $location ? ' checked="checked"' : '';
				
				$path = eDispatcher::getConfigPath($module, $location, false);
				if(!is_readable($path))
				{
				    $path = str_replace('/url.php', '/', $tp->replaceConstants(eDispatcher::getConfigPath($module, $location, true), true)).' <em>('.LAN_EURL_LOCATION_NONE.')</em>';
                    $diz = LAN_EURL_DEFAULT;
                }
				else
				{
				    $path = $tp->replaceConstants(eDispatcher::getConfigPath($module, $location, true), true);
                    $diz  = (basename($path) != 'url.php' ) ? LAN_EURL_FRIENDLY : LAN_EURL_DEFAULT;
				}
				    

				$label = vartrue($section['label'], $index == 0 ? LAN_EURL_DEFAULT : eHelper::labelize(ltrim(strstr($location, '/'), '/')));
				$cssClass = $checked ? 'e-showme' : 'e-hideme';
				$cssClass = 'e-hideme'; // always hidden for now, some interface changes could come after pre-alpha

				 $exampleUrl = array();
				 if(!empty($section['examples']))
				 {
	                foreach($section['examples'] as $ex)
	                {
	                    $exampleUrl[] = str_replace($srch,$repl,$ex);

	                }
				 }

                 if(strpos($path,'noid')!==false)
                {
               //     $exampleUrl .= "  &nbsp; &Dagger;";    //XXX Add footer - denotes more CPU required. ?
                }
                
                $selected = varset($obj->current[$module]) == $location ? "selected='selected'" : '';
				$opt .= "<option value='{$location}' {$selected} >".$diz.": ".$exampleUrl[0]."</option>";

				$info .= "<tr><td>".$label."
					
					</td>
					<td><strong>".LAN_EURL_LOCATION."</strong>: ".$path."
                    <p>".vartrue($section['description'], LAN_EURL_PROFILE_INFO)."</p><small>".implode("<br />", $exampleUrl)."</small></td>
                    
                    
                    
                    </tr>
				";

			}

			$info .= "</table>";

			$title = vartrue($section['name'], eHelper::labelize($obj->module));


			
			$text .= "
                <tr>
                    <td>".$this->moreInfo($title, $info)."</td>
                    <td><select name='eurl_config[$module]' class='form-control input-block-level'>".$opt."</select></td>
                    <td>";
		
			$bTable = ($admin['generate']['table']);
			$bInput = $admin['generate']['input'];
			$bOutput = $admin['generate']['output'];
			$bPrimary = $admin['generate']['primary'];
			
		
			$text .= (is_array($admin['generate'])) ? $frm->admin_button('rebuild['.$bTable.']', $bPrimary."::".$bInput."::".$bOutput,'delete', LAN_EURL_REBUILD) : "";	  
				  

			$text .= "</td>
               </tr>";
		}

		
		
		
		
		
		
		
		
		/*
		For Miro - intuitive interface example. All configs are contained within one e_url.php file. 
		Root namespacing automatically calculated based on selection. 
		ie. choosing option 1 below will set root namespacing for news. 
		Known bug (example): 
		  News title: Nothing's Gonna Change my World!
		  Currently becomes: /Nothing%26%23039%3Bs%20Gonna%20Change%20my%20World%21
		 Should become: /nothings-gonna-change-my-world
		 Good SEF reference: http://davidwalsh.name/generate-search-engine-friendly-urls-php-function
		 
		 [Miro] Solution comes from the module itself, not related with URL assembling in anyway (as per latest Skype discussion)
		 */

		
		// Global On/Off Switch Example
		// [Miro] there is no reason of switch, everything could go through single entry point at any time, without a need of .htaccess (path info)
		// Control is coming per configuration file.
		$example = "
		<tr><td>Enable Search-Engine-Friendly URLs</td>
		<td><input type='checkbox' name='SEF-active' value='1' />
		</td></tr>";
		
		//Entry Example (Hidden unless the above global switch is active)
		$example .= "
		
		<tr><td>News</td>
					<td style='padding:0px'>
					<table style='width:600px;margin-left:0px'>
					<tr>
						<td><input type='radio' class='radio' name='example' />Default</td><td>/news.php?item.1</td>
					</tr>
					<tr>
						<td><input type='radio' class='radio' name='example' />News Namespace and News Title</td><td>/news/news-item-title</td>
					</tr>
					<tr>
						<td><input type='radio' class='radio' name='example' />Year and News Title</td><td>/2011/news-item-title</td>
					</tr>
					<tr>
						<td><input type='radio' class='radio' name='example' />Year/Month and News Title</td><td>/2011/08/news-item-title</td>
					</tr>
					<tr>
						<td><input type='radio' class='radio' name='example' />Year/Month/Day and News Title</td><td>/2011/08/27/news-item-title</td>
					</tr>
					<tr>
						<td><input type='radio' class='radio' name='example' />News Category and News Title</td><td>/news-category/news-item-title</td>
					</tr>
					";
					
			// For 0.8 Beta 
			$example .= "
					<tr>
						<td><input type='radio' class='radio' name='example' />Custom</td><td><input class='tbox' type='text' name='custom-news' value='' /></td>
						</tr>";
		
			$example .= "</table>";
					
		$example .= "</td>
					</tr>";
					

		return $text;
		
	}

	public function aliasesRows($currentAliases, $modules, $lanDef, $lans)
	{
		if(empty($modules))
		{
			return "
				<tr>
					<td colspan='3'>".LAN_EURL_EMPTY."</td>
				</tr>
			";
		}
		
		$text = '';
		$tp = e107::getParser();

		$text .= "<tr>
			<th>Module</th>
			<th></th>
			<th></th>
		</tr>";

		$lng = e107::getLanguage();

		foreach ($modules as $module => $obj) 
		{
			$cfg = $obj->config->config();
			if(isset($cfg['config']['noSingleEntry']) && $cfg['config']['noSingleEntry']) continue;
			
			if($module == 'index')
			{
				$text .= "
				<tr>
					<td>
						".LAN_EURL_CORE_INDEX."
					</td>
					<td>
						<table class='table table-striped table-bordered' style='margin-bottom:0'>
						<colgroup>
<col style='width:20%' />
<col style='width:40%' />
<col style='width:40%' />
</colgroup>
							<tr>
							<td colspan='2'>
								".LAN_EURL_CORE_INDEX_INFO."
							</td>
							<td>
								".e107::getUrl()->create('/', '', array('full' => 1))."
							</tr>
						</table>
					</td>
				</tr>
				";
				continue;
			}


			$help = array();
			$admin = $obj->config->admin();
			$lan = $lanDef[0];
			$url = e107::getUrl()->create($module, '', array('full' => 1, 'encode' => 0));
			$defVal = isset($currentAliases[$lan]) && in_array($module, $currentAliases[$lan]) ? array_search($module, $currentAliases[$lan]) : $module; 
			$section = vartrue($admin['labels'], array());
			
			$text .= "
				<tr>
					<td>
						".vartrue($section['name'], ucfirst(str_replace('_', ' ', $obj->module)))."
						<div class='label-note'>
						".LAN_EURL_FORM_HELP_ALIAS_0." <strong>{$module}</strong><br />
						</div>
					</td>
					<td>
			";
			

			
			// default language
			$text .= "<table class='table table-striped table-bordered' style='margin-bottom:0'>
<colgroup>
<col style='width:20%' />
<col style='width:40%' />
<col style='width:40%' />
</colgroup>";

			$text .= "<tr>
			<th>".ADLAN_132."</th>
			<th>".LAN_EURL_NAME_ALIASES."</th>
			<th>".LAN_EURL_FORM_HELP_EXAMPLE."</th>
		</tr>";

			$text .= "<tr>";
			$text .= "<td>".$lanDef[1]."</td>";
			$text .= "<td class='form-inline'>";
			$text .= $this->text('eurl_aliases['.$lanDef[0].']['.$module.']', $defVal, 255, 'size=xlarge');
		//	$text .= ' ['.$lanDef[1].']';
			$text .= "</td><td>";
			$text .= $this->help(LAN_EURL_FORM_HELP_DEFAULT);

			$text .= "</td>";
		//	$help[] = '['.$lanDef[1].'] '.LAN_EURL_FORM_HELP_EXAMPLE.':<br /><strong>'.$url.'</strong>';

			$text .= "</tr>";

			if(e107::getUrl()->router()->isMainModule($module))
			{
				$help = " <span class='e-tip e-help' title=\"".LAN_EURL_CORE_MAIN."\">".$tp->toGlyph('fa-home')."</span>";
				//$readonly = 1; // may be used later.
				$readonly = 0;
			}
			else
			{
				$help = '';
				$readonly=0;
			}

			if($lans)
			{

				foreach ($lans as $code => $lan) 
				{

					$url = e107::getUrl()->create($module, '', array('lan' => $code, 'full' => 1, 'encode' => 0)); 
					$defVal = isset($currentAliases[$code]) && in_array($module, $currentAliases[$code]) ? array_search($module, $currentAliases[$code]) : $module;


				//	$help .= '['.$lan.'] '.LAN_EURL_FORM_HELP_EXAMPLE.':<br /><strong>'.$url.'</strong>';

					$text .= "<tr>";
					$text .= "<td>".$lan."</td>";
					$text .= "<td class='form-inline'>". $this->text('eurl_aliases['.$code.']['.$module.']', $defVal, 255, array('size' => 'xlarge', 'readonly'=>$readonly));
					$text .=  $help;
					$text .= "</td>";
					$text .= "<td>";

					//	$text .= $this->help(LAN_EURL_FORM_HELP_ALIAS_1.' <strong>'.$lan.'</strong>');
				//	$text .= $this->help(LAN_EURL_FORM_HELP_ALIAS_1.' <strong>'.$lan.'</strong>');
					$url = $lng->subdomainUrl($lan,$url);
					$text .= $url;
					$text .= "</td>";
				//	$text .= "<td>".

				//	$text .= '['.$lan.'] '.LAN_EURL_FORM_HELP_EXAMPLE.':<br /><strong>'.$url.'</strong>';
				//	$text .= "</td>";
					$text .= "</tr>";
				}

			}

			$text .= "</table>
				</td></tr>";


			
			/*$text .= "
					</td>
					<td>
						".implode("<div class='spacer'><!-- --></div>", $help)."
					</td>
				</tr>
			";*/

		//	$text .= "</tr>";
		}

		return $text;
	}
}

class eurlAdminEmptyConfig extends eUrlConfig
{
	public function config()
	{
		return array();
	}
}

new eurl_admin();

require_once(e_ADMIN.'auth.php');

e107::getAdminUI()->runPage();

require_once(e_ADMIN.'footer.php');


