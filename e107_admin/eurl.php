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
 * $URL$
 * $Id$
*/

require_once('../class2.php');
if (!ADMIN || !getperms('L'))
{
	header('location:'.e_BASE.'index.php');
	exit;
}

e107::coreLan('eurl', true);
// TODO - admin interface support, remove it from globals
$e_sub_cat = 'eurl';


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
		'main/config'		=> array('caption'=> LAN_EURL_MENU_CONFIG, 'perm' => 'L'),
		'main/alias' 		=> array('caption'=> LAN_EURL_MENU_ALIASES, 'perm' => 'L'),
		'main/settings' 	=> array('caption'=> LAN_EURL_MENU_SETTINGS, 'perm' => 'L'),
		'main/help' 		=> array('caption'=> LAN_EURL_MENU_HELP, 'perm' => 'L'),
	);

	protected $adminMenuAliases = array();
	
	protected $defaultAction = 'config';

	protected $menuTitle = LAN_EURL_MENU;
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
		$this->api = e107::getInstance();
		$this->addTitle(LAN_EURL_NAME);
		
		if($this->getAction() != 'settings') return;
		

	}
	
	public function HelpObserver()
	{
		
	}
	
	public function HelpPage()
	{
		$this->addTitle(LAN_EURL_NAME_HELP);
		return LAN_EURL_UC;
	}
	
	public function SettingsObserver()
	{
		// main module pref dropdown
		$this->prefs['url_main_module']['writeParms'][''] = 'None';
		$modules = e107::getPref('url_config', array());
		ksort($modules);
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
						->setPostedData($this->getPosted(), null, false, false)
						//->setPosted('not_existing_pref_test', 1)
						->save(true);
		
			$this->getConfig()->setMessages();
		}
	}
	
	public function SettingsPage()
	{
		$this->addTitle(LAN_EURL_NAME_SETTINGS);
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
	
	public function AliasPage()
	{
		$this->addTitle(LAN_EURL_NAME_ALIASES);
		
		$aliases = e107::getPref('url_aliases', array());
		
		$form = $this->getUI();
		$text = "
			<form action='".e_SELF."?mode=main&action=alias' method='post' id='urlconfig-form'>
				<fieldset id='core-eurl-core'>
					<legend>".LAN_EURL_LEGEND_ALIASES."</legend>
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						<colgroup span='2'>
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
		if(isset($_POST['update']))
		{
			$config = is_array($_POST['eurl_config']) ? e107::getParser()->post_toForm($_POST['eurl_config']) : '';
			$modules = eRouter::adminReadModules();
			$locations = eRouter::adminBuildLocations($modules);
			
			$aliases = eRouter::adminSyncAliases(e107::getPref('url_aliases'), $config);
			
			e107::getConfig()
				->set('url_aliases', $aliases)
				->set('url_config', $config)
				->set('url_modules', $modules)
				->set('url_locations', $locations)
				->save();
				
			eRouter::clearCache();
		}
	}
	
	public function ConfigPage()
	{
		$this->addTitle(LAN_EURL_NAME_CONFIG);
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
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						<colgroup span='3'>
							<col class='col-label' />
							<col class='col-control' />
							<col class='col-control' />
						</colgroup>
						<tbody>
		";
		
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

	public function renderConfig($current, $locations)
	{

		$ret = array();
		$url = e107::getUrl();
		
		
		ksort($locations);
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
	
	public function moduleRows($data)
	{
		$text = '';
		$tp = e107::getParser();
		if(empty($data))
		{
			return "
				<tr>
					<td colspan='2'>".LAN_EURL_EMPTY."</td>
				</tr>
			";
		}
		
		foreach ($data as $obj) 
		{
			$admin = $obj->config->admin();
			$section = vartrue($admin['labels'], array());
			$text .= "
				<tr>
					<td class='label'>".vartrue($section['name'], eHelper::labelize($obj->module))."</td>
					<td class='control'>
			";
			
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
				$module = $obj->module;
				$checked = varset($obj->current[$module]) == $location ? ' checked="checked"' : '';
				
				$path = eDispatcher::getConfigPath($module, $location, false);
				if(!is_readable($path)) $path = str_replace('/url.php', '/', $tp->replaceConstants(eDispatcher::getConfigPath($module, $location, true), true)).' <em>('.LAN_EURL_LOCATION_NONE.')</em>';
				else $path = $tp->replaceConstants(eDispatcher::getConfigPath($module, $location, true), true);
				
				$label = vartrue($section['label'], $index == 0 ? LAN_EURL_DEFAULT : eHelper::labelize(ltrim(strstr($location, '/'), '/')));
				$cssClass = $checked ? 'e-showme' : 'e-hideme';
				$cssClass = 'e-hideme'; // always hidden for now, some interface changes could come after pre-alpha
				// XXX use e_form
				$text .= "
				
					<a href='#{$id}-info' class='e-expandit' title='".LAN_EURL_INFOALT."'><img src='".e_IMAGE_ABS."admin_images/info_16.png' class='icon' alt='' /></a>
					<input type='radio' class='radio' id='{$id}' name='eurl_config[$module]' value='{$location}'{$checked} /><label for='{$id}'>".$label."</label>
					<div class='{$cssClass}' id='{$id}-info'>
						<div class='indent'>
							<strong>".LAN_EURL_LOCATION."</strong> ".$path."
							<p>".vartrue($section['description'], LAN_EURL_PROFILE_INFO)."</p>
						</div>
					</div>
					<div class='spacer'><!-- --></div>
				";
			}
			$text .= "
					</td>
				</tr>
			";
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
		// FIXME TODO XXX
		
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
		
		foreach ($modules as $module => $obj) 
		{
			$cfg = $obj->config->config();
			if(isset($cfg['config']['noSingleEntry']) && $cfg['config']['noSingleEntry']) continue;
			
			if($module == 'index')
			{
			$text .= "
				<tr>
					<td class='label'>
						".LAN_EURL_CORE_INDEX."
					</td>
					<td class='control'>
						".LAN_EURL_CORE_INDEX_INFO."
					</td>
					<td class='control'>
						".LAN_EURL_FORM_HELP_EXAMPLE." <br /><strong>".e107::getUrl()->create('/', '', array('full' => 1))."</strong>
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
					<td class='label'>
						".vartrue($section['name'], ucfirst(str_replace('_', ' ', $obj->module)))."
						<div class='label-note'>
						".LAN_EURL_FORM_HELP_ALIAS_0." <strong>{$module}</strong><br />
						</div>
					</td>
					<td class='control'>
			";
			
			
			
			// default language		
			$text .= $this->text('eurl_aliases['.$lanDef[0].']['.$module.']', $defVal).' ['.$lanDef[1].']'.$this->help(LAN_EURL_FORM_HELP_DEFAULT);
			$help[] = '['.$lanDef[1].'] '.LAN_EURL_FORM_HELP_EXAMPLE.'<br /><strong>'.$url.'</strong>';
			
			if($lans)
			{
				foreach ($lans as $code => $lan) 
				{

					$url = e107::getUrl()->create($module, '', array('lan' => $code, 'full' => 1, 'encode' => 0)); 
					$defVal = isset($currentAliases[$code]) && in_array($module, $currentAliases[$code]) ? array_search($module, $currentAliases[$code]) : $module; 
					$text .= "<div class='spacer'><!-- --></div>";
					$text .= $this->text('eurl_aliases['.$code.']['.$module.']', $defVal).' ['.$lan.']'.$this->help(LAN_EURL_FORM_HELP_ALIAS_1.' <strong>'.$lan.'</strong>');
					$help[] = '['.$lan.'] '.LAN_EURL_FORM_HELP_EXAMPLE.'<br /><strong>'.$url.'</strong>';
				}
			}
			
			if(e107::getUrl()->router()->isMainModule($module))
			{
				$help = array(LAN_EURL_CORE_MAIN);
			}
			
			$text .= "
					</td>
					<td class='control'>
						".implode("<div class='spacer'><!-- --></div>", $help)."
					</td>
				</tr>
			";
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


