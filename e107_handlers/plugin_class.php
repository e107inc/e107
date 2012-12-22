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
 */

if (!defined('e107_INIT')) { exit; }


/**
 *
 * @package     e107
 * @category	e107_handlers
 * @version     $Id$
 * @author      e107inc
 *
 *	Plugin administration handler
 */

e107::coreLan('plugin', true);

class e107plugin
{
	// Reserved Addon names.
	var $plugin_addons = array(
		'e_rss',
		'e_notify',
		'e_linkgen',
		'e_list',
		'e_bb',
		'e_meta',
		'e_emailprint',
		'e_frontpage',
		'e_latest',
		'e_status',
		'e_search',
		'e_shortcode',
		'e_module',
		'e_event',
		'e_comment',
		'e_sql',
		'e_userprofile',
		'e_header',
		'e_userinfo',
		'e_tagwords',
		'e_url',
		'e_cron',
		'e_mailout',
		'e_sitelink',
		'e_tohtml',
		'e_featurebox'
	);


	var $disAllowed = array(
		'theme',
		'core'
	);

	// List of all plugin variables which need to be checked - install required if one or more set and non-empty
	// Deprecated in 0.8 (used in plugin.php only). Probably delete in 0.9
	var $all_eplug_install_variables = array(
		'eplug_link_url',
		'eplug_link',
		'eplug_prefs',
		'eplug_array_pref',
		'eplug_table_names',
		//	'eplug_sc',				// Not used in 0.8 (or later 0.7)
		'eplug_userclass',
		'eplug_module',
		//	'eplug_bb',				// Not used in 0.8 (or later 0.7)
		'eplug_latest',
		'eplug_status',
		'eplug_comment_ids',
		'eplug_conffile',
		'eplug_menu_name'
	);

	// List of all plugin variables involved in an update (not used ATM, but worth 'documenting')
	// Deprecated in 0.8 (used in plugin.php only). Probably delete in 0.9
	var $all_eplug_update_variables = array(
		'upgrade_alter_tables',
		//	'upgrade_add_eplug_sc',				// Not used in 0.8 (or later 0.7)
		//	'upgrade_remove_eplug_sc',			// Not used in 0.8 (or later 0.7)
		//	'upgrade_add_eplug_bb',				// Not used in 0.8 (or later 0.7)
		//	'upgrade_remove_eplug_bb',			// Not used in 0.8 (or later 0.7)
		'upgrade_add_prefs',
		'upgrade_remove_prefs',
		'upgrade_add_array_pref',
		'upgrade_remove_array_pref'
	);

	// List of all 'editable' DB fields ('plugin_id' is an arbitrary reference which is never edited)
	var $all_editable_db_fields = array(
		'plugin_name', // Name of the plugin - language dependent
		'plugin_version', // Version - arbitrary text field
		'plugin_path', // Name of the directory off e_PLUGIN - unique
		'plugin_installflag', // '0' = not installed, '1' = installed
		'plugin_addons', // List of any extras associated with plugin - bbcodes, e_XXX files...
		'plugin_category' // Plugin Category: settings, users, content, management, tools, misc, about
		);

	var $accepted_categories = array('settings', 'users', 'content', 'tools', 'manage', 'misc', 'menu', 'about');

	var $plug_vars;
	var $current_plug;
	var $parsed_plugin;
	var $plugFolder;
	var $plugConfigFile;
	var $unInstallOpts;
	var $module = array();

	function e107plugin()
	{
		$parsed_plugin = array();
	}

	/**
	 * Returns an array containing details of all plugins in the plugin table - should normally use e107plugin::update_plugins_table() first to
	 * make sure the table is up to date. (Primarily called from plugin manager to get lists of installed and uninstalled plugins.
	 * @return array plugin details
	 */
	function getall($flag)
	{
		$sql = e107::getDb();

		if ($sql->db_Select("plugin", "*", "plugin_installflag = ".(int) $flag." ORDER BY plugin_path ASC"))
		{
			$ret = $sql->db_getList();
			return $ret;
		}
		return false;
	}
	
		/**
	 * Returns an array containing details of all plugins in the plugin table - should normally use e107plugin::update_plugins_table() first to
	 * make sure the table is up to date. (Primarily called from plugin manager to get lists of installed and uninstalled plugins.
	 * @return array plugin details
	 */
	function getId($path)
	{
		$sql = e107::getDb();

		if ($sql->db_Select("plugin", "plugin_id", "plugin_path = '".(string) $path."' LIMIT 1"))
		{
			$row = $sql->db_Fetch(MYSQL_ASSOC);
			return intval($row['plugin_id']);
		}
		
		return false;
	}
	
	/**
	 * Checks all installed plugins and returns an array of those needing an update. 
	 * @param string $mode  'boolean' for a quick true/false or null for full array returned. 
	 * @return mixed 
	 */
	function updateRequired($mode=null)
	{
		$xml 			= e107::getXml();
		$mes 			= e107::getMessage();	
		$needed 		= array();
		
		if(!$plugVersions = e107::getConfig('core')->get('plug_installed'))
		{
			return FALSE;
		}
				
		foreach($plugVersions as $path=>$version)
		{
			$fullPath = e_PLUGIN.$path."/plugin.xml";
			if(is_file(e_PLUGIN.$path."/plugin.xml"))
			{
				$data = $xml->loadXMLfile($fullPath, true);
				$curVal = floatval($version);
				$fileVal = floatval($data['@attributes']['version']);
				
				if($ret = $this->execute_function($path, 'upgrade', 'required')) // Check {plugin}_setup.php and run a 'required' method, if true, then update is required. 
				{
					if($mode == 'boolean')
					{
						$mes->addDebug("Plugin Update(s) Required");
						return TRUE;	
					}
					$needed[$path] = $data;		
				} 
				
				if($curVal < $fileVal)
				{
					
					if($mode == 'boolean')
					{
						$mes->addDebug("Plugin Update(s) Required");
						return TRUE;	
					}
					
					$mes->addDebug("Plugin: <strong>{$path}</strong> requires an update.");
					$needed[$path] = $data;
				}	
			}

		}	
	
		return count($needed) ? $needed : FALSE;		
	}

	/**
	 * Check for new plugins, create entry in plugin table and remove deleted plugins
	 */
	function update_plugins_table()
	{
		
		$sql = e107::getDb();
		$sql2 = e107::getDb('sql2');
		$tp = e107::getParser();
		$fl = e107::getFile();
		$mes = e107::getMessage();
		$mes->addDebug("Updating plugins Table");

		global $mySQLprefix, $menu_pref;
		$pref = e107::getPref();
		

		$sp = FALSE;

		$pluginDBList = array();
		if ($sql->db_Select('plugin', "*")) // Read all the plugin DB info into an array to save lots of accesses

		{
			while ($row = $sql->db_Fetch(MYSQL_ASSOC))
			{
				$pluginDBList[$row['plugin_path']] = $row;
				$pluginDBList[$row['plugin_path']]['status'] = 'read';
				//	echo "Found plugin: ".$row['plugin_path']." in DB<br />";
				}
		}

		$plugList = $fl->get_files(e_PLUGIN, "^plugin\.(php|xml)$", "standard", 1);
		foreach ($plugList as $num => $val) // Remove Duplicates caused by having both plugin.php AND plugin.xml.
		{
			$key = basename($val['path']);
			$pluginList[$key] = $val;
		}

		$p_installed = e107::getPref('plug_installed', array()); // load preference;
		require_once(e_HANDLER."message_handler.php");
		$mes = eMessage::getInstance();

		foreach ($pluginList as $p)
		{
			$p['path'] = substr(str_replace(e_PLUGIN, "", $p['path']), 0, -1);
			$plugin_path = $p['path'];

			if (strpos($plugin_path, 'e107_') !== FALSE)
			{
				$mes->add("Folder error: <i>{$p['path']}</i>.  'e107_' is not permitted within plugin folder names.", E_MESSAGE_WARNING);
				continue;
			}
			
			if(in_array($plugin_path, $this->disAllowed))
			{
				$mes->addWarning("Folder error: <i>{$p['path']}</i> is not permitted as an acceptable folder name.");
				continue;	
			}
			
			
			$plug['plug_action'] = 'scan'; // Make sure plugin.php knows what we're up to

			if (!$this->parse_plugin($p['path']))
			{
				//parsing of plugin.php/plugin.xml failed.
				$mes->add("Parsing failed - file format error: {$p['path']}", E_MESSAGE_ERROR);
				continue; // Carry on and do any others that are OK
			}

			$plug_info = $this->plug_vars;
			$eplug_addons = $this->getAddons($plugin_path);

			//Ensure the plugin path lives in the same folder as is configured in the plugin.php/plugin.xml - no longer relevant. 
			if ($plugin_path == $plug_info['folder'])
			{
				if (array_key_exists($plugin_path, $pluginDBList))
				{ // Update the addons needed by the plugin
					$pluginDBList[$plugin_path]['status'] = 'exists';
					
						// Check for name (lan) changes
					if (vartrue($plug_info['@attributes']['lan']) && $pluginDBList[$plugin_path]['plugin_name'] != $plug_info['@attributes']['lan'])
					{
						// print_a($plug_info);
						$pluginDBList[$plugin_path]['status'] = 'update';
						$pluginDBList[$plugin_path]['plugin_name'] = $plug_info['@attributes']['lan'];
						$this->plugFolder = $plugin_path;
						$this->XmlLanguageFiles('upgrade');
					}
					
					

					// Check for missing plugin_category in plugin table.
					if ($pluginDBList[$plugin_path]['plugin_category'] == '' || $pluginDBList[$plugin_path]['plugin_category'] != $plug_info['category'])
					{
						// print_a($plug_info);
						$pluginDBList[$plugin_path]['status'] = 'update';
						$pluginDBList[$plugin_path]['plugin_category'] = (varsettrue($plug_info['category'])) ? $plug_info['category'] : "misc";
					}

					// If plugin not installed, and version number of files changed, update version as well
					if (($pluginDBList[$plugin_path]['plugin_installflag'] == 0) && ($pluginDBList[$plugin_path]['plugin_version'] != $plug_info['@attributes']['version']))
					{ // Update stored version
						$pluginDBList[$plugin_path]['plugin_version'] = $plug_info['@attributes']['version'];
						$pluginDBList[$plugin_path]['status'] = 'update';
					}
					if ($pluginDBList[$plugin_path]['plugin_addons'] != $eplug_addons)
					{ // Update stored addons list
						$pluginDBList[$plugin_path]['plugin_addons'] = $eplug_addons;
						$pluginDBList[$plugin_path]['status'] = 'update';
					}

					if ($pluginDBList[$plugin_path]['plugin_installflag'] == 0) // Plugin not installed - make sure $pref not set

					{
						if (isset($p_installed[$plugin_path]))
						{
							unset($p_installed[$plugin_path]);
							$sp = TRUE;
						}
					}
					else
					{ // Plugin installed - make sure $pref is set
						if (!isset($p_installed[$plugin_path]) || ($p_installed[$plugin_path] != $pluginDBList[$plugin_path]['plugin_version']))
						{ // Update prefs array of installed plugins
							$p_installed[$plugin_path] = $pluginDBList[$plugin_path]['plugin_version'];
							//				  echo "Add: ".$plugin_path."->".$ep_row['plugin_version']."<br />";
							$sp = TRUE;
						}
					}
				}
				else // New plugin - not in table yet, so add it. If no install needed, mark it as 'installed'
				{
					if ($plug_info['@attributes']['name'])
					{					
						$pName = vartrue($plug_info['@attributes']['lan']) ? $plug_info['@attributes']['lan'] : $plug_info['@attributes']['name'] ;
						
						$_installed = ($plug_info['@attributes']['installRequired'] == 'true' || $plug_info['@attributes']['installRequired'] == 1 ? 0 : 1);
						
						if (e107::getDb()->db_Insert("plugin", "0, '".$tp->toDB($pName, true)."', '".$tp->toDB($plug_info['@attributes']['version'], true)."', '".$tp->toDB($plugin_path, true)."',{$_installed}, '{$eplug_addons}', '".$this->manage_category($plug_info['category'])."', '".varset($plug_info['@attributes']['releaseUrl'])."' "))
						{
								$mes->add("Added <b>".$tp->toHTML($pName,false,"defs")."</b> to the plugin table.", E_MESSAGE_DEBUG);
							}
							else
							{
								$mes->add("Failed to add ".$tp->toHTML($pName,false,"defs")." to the plugin table.", E_MESSAGE_DEBUG);
							}
						}
					}
			}
			else
			{ // May be useful that we ignore what will usually be copies/backups of plugins - but don't normally say anything
				//						    echo "Plugin copied to wrong directory. Is in: {$plugin_path} Should be: {$plug_info['folder']}<br /><br />";
			}

			// print_a($plug_info);
		}

		// Now scan the table, updating the DB where needed
		foreach ($pluginDBList as $plug_path => $plug_info)
		{
			if ($plug_info['status'] == 'read')
			{ // In table, not on server - delete it
				$sql->db_Delete('plugin', "`plugin_id`={$plug_info['plugin_id']}");
				//			echo "Deleted: ".$plug_path."<br />";
				}
			if ($plug_info['status'] == 'update')
			{
				$temp = array();
				foreach ($this->all_editable_db_fields as $p_f)
				{
					$temp[] = "`{$p_f}` = '{$plug_info[$p_f]}'";
				}
				$sql->db_Update('plugin', implode(", ", $temp)."  WHERE `plugin_id`={$plug_info['plugin_id']}");
				//			echo "Updated: ".$plug_path."<br />";
				}
		}
		if ($sp/* && vartrue($p_installed)*/)
		{
			e107::getConfig('core')->setPref('plug_installed', $p_installed);
			$this->rebuildUrlConfig();
			e107::getConfig('core')->save();
		}
	}

	function manage_category($cat)
	{
		if (vartrue($cat) && in_array($cat, $this->accepted_categories))
		{
			return $cat;
		}
		else
		{
			return 'misc';
		}
	}

	function manage_icons($plugin = '', $function = '')
	{

		if ($plugin == '')
		{
			return;
		}

		$mes = eMessage::getInstance();
		$sql = e107::getDb();
		$tp = e107::getParser();
		$med = e107::getMedia();

		if ($function == 'install' || $function == 'upgrade')
		{
			$med->importIcons(e_PLUGIN.$plugin);
			return;
		}

		if ($function == 'uninstall')
		{
			if (vartrue($this->unInstallOpts['delete_ipool'], FALSE))
			{
				$status = ($med->removePath(e_PLUGIN.$plugin, 'icon')) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
				$mes->add('Removing Icons from Media-Manager', $status);
			}
			return;
		}

	}

	/**
	 * Returns details of a plugin from the plugin table from it's ID
	 *
	 * @param int $id
	 * @return array plugin info
	 */
	function getinfo($id, $force = false)
	{
		$sql = e107::getDb();
		static $getinfo_results;
		if (!is_array($getinfo_results))
		{
			$getinfo_results = array();
		}

		$id = (int) $id;
		if (!isset($getinfo_results[$id]) || $force == true)
		{
			if ($sql->db_Select('plugin', '*', "plugin_id = ".$id))
			{
				$getinfo_results[$id] = $sql->db_Fetch();
			}
			else
			{
				return false;
			}
		}
		return $getinfo_results[$id];
	}
	
	public function setUe()
	{
		if (!isset($this->module['ue']))
		{
			include_once(e_HANDLER.'user_extended_class.php');
			$this->module['ue'] = new e107_user_extended;
		}
	}
	
	/**
	 * User field name, based on its type
	 * @param string $folder plugin folder
	 * @param int $type normalized field type
	 * @param string $name field name
	 * @return string  field name
	 */
	public function ue_field_name($folder, $type, $name)
	{
		if($type == EUF_PREFIELD || $type == EUF_CATEGORY)
		{
			return $name; // no plugin_plugname_ prefix
		}
		return 'plugin_'.$folder.'_'.$name;
	}
	
	/**
	 * Normalize type
	 * @param array $attrib parsed from XML user field definitions
	 * @return integer type ID
	 */
	public function ue_field_type($attrib)
	{
		$field_type = $attrib['type'];
		$type = defined($field_type) ? constant($field_type) : $field_type;
		if(!is_numeric($type))
		{
			// normalize further
			$this->setUe();
			$type = $this->module['ue']->typeArray[$type];
		}
		return $type;
	}
	
	/**
	 * Type number to type name
	 * @param integer $typeId
	 * @return string type name
	 */
	public function ue_field_type_name($typeId)
	{
		if(is_numeric($typeId))
		{
			$this->setUe();
			return array_search($typeId, $this->module['ue']->typeArray);
		}
		return $typeId;
	}
	
	/**
	 * Field atributes ($field_attrib array) as they have to be defined in plugin.xml:
	 * name - REQUIRED string
	 * text -  (string|constant name) field label 
	 * type - REQUIRED (constant name) see EUF_* constants in e107_user_extended class
	 * regex - regex validation string
	 * required - 0-not requried, don't show on signup; 1 - required, show on signup; 2-not required, show on signup
	 * allow_hide (0|1) - allow user to hide this field on profile page
	 * read, write, applicable - classes, see e_UC_* defines
	 * values - comma separated values (if required)
	 * default - default value
	 * order - (number)
	 * parent - (string) category name for this field
	 * system - (0|1) - field wont be shown if it's system, NOTE - default value if system is not set is 1!
	 * 
	 * @param string $action - add|remove
	 * @param string $field_name normalized field name (see self::ue_field_name())
	 * @param array $field_attrib
	 * @param string $field_source used for system user fields 
	 * @return boolean success
	 */
	function manage_extended_field($action, $field_name, $field_attrib, $field_source = '')
	{
		$mes = e107::getMessage();
		$this->setUe();

		$type = $this->ue_field_type($field_attrib);
		$type_name = $this->ue_field_type_name($type);
		
		$mes->add("Extended Field: ".$action.": ".$field_name." : ".$type_name, E_MESSAGE_DEBUG);
		
		// predefined
		if($type == EUF_PREFIELD)
		{
			
			$preList = $this->module['ue']->parse_extended_xml(''); // passed value currently not used at all, could be file path in the near future
			if($preList && isset($preList[$field_name]))
			{
				$preField = $preList[$field_name];
				if($preField)
				{
					$field_attrib = array_merge($preField, $field_attrib); // merge
					// predefined type - numeric value, constant or as defined in user_extended_class::typeArray
					$field_attrib['type'] = $type = $this->ue_field_type($preField); // override type
				}
				else 
				{
					return false;
				}
			}
			
		}
		// not allowed for categories
		elseif($type == EUF_CATEGORY) 
		{
			$field_attrib['parent'] = 0;
		}

		if ($action == 'add')
		{
			// system field
			if($field_attrib['system'])
			{
				return $this->module['ue']->user_extended_add_system($field_name, $type, varset($field_attrib['default'], ''), $field_source);
			}
			
			// new - add non-system extended field

			// classes
			$field_attrib['read'] = varset($field_attrib['read'], 'e_UC_MEMBER');
			$field_attrib['write'] = varset($field_attrib['read'], 'e_UC_MEMBER');
			$field_attrib['applicable'] = varset($field_attrib['applicable'], 'e_UC_MEMBER');
			
			// manage parent
			if(vartrue($field_attrib['parent']))
			{
				foreach ($this->module['ue']->catDefinitions as $key => $value) 
				{
					if($value['user_extended_struct_name'] == $field_attrib['parent'])
					{
						$field_attrib['parent'] = $key;
						break;
					}
				}
				if(!is_numeric($field_attrib['parent'])) $field_attrib['parent'] = 0;
			} 
			else $field_attrib['parent'] = 0;

			
			// manage required (0, 1, 2)
			if(!isset($field_attrib['required']))
			{
				$field_attrib['required'] = 0;
			}
			
			// manage params
			$field_attrib['parms'] = '';
			
			// validation and parms
			$include = varset($field_attrib['include_text']);
			$regex = varset($field_attrib['regex']);
			$hide = vartrue($field_attrib['allow_hide']) ? 1 : 0;
			$failmsg = '';
			if($regex || $hide)
			{
				// failmsg only when required
				if($field_attrib['required'] == 1 || $regex)
					$failmsg = vartrue($field_attrib['error']) ? $field_attrib['error'] : 'LAN_UE_FAIL_'.strtoupper($field_name);
				
				$field_attrib['parms'] = $include."^,^".$regex."^,^".$failmsg.'^,^'.$hide;
			}
			
			//var_dump($field_attrib, $field_name, $type);
			
			$status = $this->module['ue']->user_extended_add(
				$field_name, 
				varset($field_attrib['text'], "LAN_UE_".strtoupper($field_name)), 
				$type, 
				$field_attrib['parms'], 
				varset($field_attrib['values'], ''), 
				varset($field_attrib['default'], ''),
				$field_attrib['required'],
				defset($field_attrib['read'], e_UC_MEMBER),
				defset($field_attrib['write'], e_UC_MEMBER),
				defset($field_attrib['applicable'], e_UC_MEMBER),
				varset($field_attrib['order'], ''),
				$field_attrib['parent']
			);
			
			// db fields handling
			if($status && $type == EUF_DB_FIELD)
			{
				// handle DB, use original non-modified name value
				$status = !$this->manage_extended_field_sql('add', $field_attrib['name']); // reverse logic - sql method do a error check
			}
			
			// refresh categories - sadly the best way so far... need improvement (inside ue class)
			if($status && $type == EUF_CATEGORY)
			{
				$cats = $this->module['ue']->user_extended_get_categories(false);
				foreach ($cats as $cat) 
				{
					$this->module['ue']->catDefinitions[$cat['user_extended_struct_id']] = $cat;
				}
			}
			
			return $status;
		}

		if ($action == 'remove')
		{
			//var_dump($field_attrib, $field_name, $type);
			$status = $this->module['ue']->user_extended_remove($field_name, $field_name);
			if($status && $type == EUF_DB_FIELD)
			{
				$status = $this->manage_extended_field_sql('remove', $field_attrib['name']);
			}
			
			return $status;
		}

		return false;
	}

	function manage_extended_field_sql($action, $field_name)
	{
		$f = e_ADMIN.'sql/extended_'.preg_replace('/[^\w]/', '', $field_name).'.php'; // quick security, always good idea
		
		if(!is_readable($f)) return false;
		
		// TODO - taken from user_extended Administration, need to be refined :/
		// FIXME - use sql parse handler
		$error = FALSE;
		$count = 0;
		if($action == 'add')
		{
			$sql_data = file_get_contents($f);
	
			$search[0] = "CREATE TABLE ";	$replace[0] = "CREATE TABLE ".MPREFIX;
			$search[1] = "INSERT INTO ";	$replace[1] = "INSERT INTO ".MPREFIX;
	
		    preg_match_all("/create(.*?)myisam;/si", $sql_data, $creation);
		    foreach($creation[0] as $tab)
		    {
				$query = str_replace($search,$replace,$tab);
		      	if(!mysql_query($query))
		      	{
		        	$error = TRUE;
				}
				$count++;
			}
	
		    preg_match_all("/insert(.*?);/si", $sql_data, $inserts);
			foreach($inserts[0] as $ins)
			{
				$qry = str_replace($search,$replace,$ins);
				if(!mysql_query($qry))
				{
				  	$error = TRUE;
				}
				$count++;
		    }
			
			if(!$count) $error = TRUE;
			
			return $error;
		}
		
		//remove
		if($action == 'remove')
		{
			// executed only if the sql file exists!
			return mysql_query("DROP TABLE ".MPREFIX."user_extended_".$field_name) ? true : false;
		}
	}

	function manage_userclass($action, $class_name, $class_description)
	{
		global $e107;
		$tp = e107::getParser();
		$sql = e107::getDb();
		$mes = e107::getMessage();

		$mes->add("Userclass: ".$action.": ".$class_name." : ".$class_description, E_MESSAGE_DEBUG);

		if (!$e107->user_class->isAdmin())
		{
			$e107->user_class = new user_class_admin; // We need the extra methods of the admin extension
			}
		$class_name = strip_tags(strtoupper($class_name));
		if ($action == 'add')
		{
			if ($e107->user_class->ucGetClassIDFromName($class_name) !== FALSE)
			{ // Class already exists.
				return TRUE; // That's probably OK
				}
			$i = $e107->user_class->findNewClassID();
			if ($i !== FALSE)
			{
				$tmp = array();
				$tmp['userclass_id'] = $i;
				$tmp['userclass_name'] = $class_name;
				$tmp['userclass_description'] = $class_description;
				$tmp['userclass_editclass'] = e_UC_ADMIN;
				$tmp['userclass_visibility'] = e_UC_ADMIN;
				$tmp['userclass_type'] = UC_TYPE_STD;
				$tmp['userclass_parent'] = e_UC_NOBODY;
				$tmp['_FIELD_TYPES']['userclass_editclass'] = 'int';
				$tmp['_FIELD_TYPES']['userclass_visibility'] = 'int';
				$tmp['_FIELD_TYPES']['userclass_id'] = 'int';
				$tmp['_FIELD_TYPES']['_DEFAULT'] = 'todb';
				return $e107->user_class->add_new_class($tmp);
			}
			else
			{
				return NULL;
			}
		}
		if ($action == 'remove')
		{
			$classID = $e107->user_class->ucGetClassIDFromName($class_name);
			if (($classID !== FALSE) && ($e107->user_class->deleteClassAndUsers($classID) === TRUE))
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
	}

	function manage_link($action, $link_url, $link_name, $link_class = 0, $options=array())
	{

		$sql = e107::getDb();
		$tp = e107::getParser();
		
		if (!is_numeric($link_class))
		{
			$link_class = strtolower($link_class);
			$plug_perm['everyone'] = e_UC_PUBLIC;
			$plug_perm['guest'] = e_UC_GUEST;
			$plug_perm['member'] = e_UC_MEMBER;
			$plug_perm['mainadmin'] = e_UC_MAINADMIN;
			$plug_perm['admin'] = e_UC_ADMIN;
			$plug_perm['nobody'] = e_UC_NOBODY;
			$link_class = ($plug_perm[$link_class]) ? intval($plug_perm[$link_class]) : e_UC_PUBLIC;
		}


		$link_url = $tp->toDB($link_url, true);
		$link_name = $tp->toDB($link_name, true);
		$path = str_replace("../", "", $link_url); // This should clean up 'non-standard' links
		$path = $tp->createConstants($path); // Add in standard {e_XXXX} directory constants if we can	
		
		if ($action == 'add')
		{
			$link_t = $sql->db_Count('links');
			if (!$sql->db_Count('links', '(*)', "WHERE link_url = '{$path}' OR link_name = '{$link_name}'"))
			{
					$linkData = array(
						'link_name'			 => $link_name,
						'link_url'			 => $path,
						'link_description'	 => '',
						'link_button'		 => '',
						'link_category'		 => '1',
						'link_order'		 => $link_t + 1,
						'link_parent'		 => '0',
						'link_open'			 => '0',
						'link_class'		 => vartrue($linkclass,'0'),
						'link_function'		 => (vartrue($options['link_function']) ? $this->plugFolder ."::".$options['link_function'] : "")
					);
					return $sql->db_Insert('links', $linkData); // TODO: Add the _FIELD_DEFS array
			}
			else
			{
				return ;
			}
		}
		if ($action == 'remove')
		{ // Look up by URL if we can - should be more reliable. Otherwise try looking up by name (as previously)
			if (($path && $sql->db_Select('links', 'link_id,link_order', "link_url = '{$path}'")) || $sql->db_Select('links', 'link_id,link_order', "link_name = '{$link_name}'"))
			{
					$row = $sql->db_Fetch();
					$sql->db_Update('links', "link_order = link_order - 1 WHERE link_order > {$row['link_order']}");
					return $sql->db_Delete('links', "link_id = {$row['link_id']}");
			}
		}
	}

	// DEPRECATED in 0.8 - See XmlPrefs(); Left for BC.
	// Update prefs array according to $action
	// $prefType specifies the storage type - may be 'pref', 'listPref' or 'arrayPref'
	function manage_prefs($action, $var, $prefType = 'pref', $path = '', $unEscape = FALSE)
	{
		global $pref;
		if (!is_array($var))
			return;
		if (($prefType == 'arrayPref') && ($path == ''))
			return;
		foreach ($var as $k => $v)
		{
			if ($unEscape)
			{
				$v = str_replace(array('\{', '\}'), array('{', '}'), $v);
			}
			switch ($prefType)
			{
				case 'pref':
					switch ($action)
					{
						case 'add':
							$pref[$k] = $v;
							break;

						case 'update':
						case 'refresh':
							// Only update if $pref doesn't exist
							if (!isset($pref[$k]))
								$pref[$k] = $v;
							break;

						case 'remove':
							if (is_numeric($k))
							{ // Sometimes arrays specified with value being the name of the key to delete
								unset($pref[$var[$k]]);
							}
							else
							{ // This is how the array should be specified - key is the name of the pref
								unset($pref[$k]);
							}
							break;
					}
					break;
				case 'listPref':
					$tmp = array();
					if (isset($pref[$k]))
						$tmp = explode(',', $pref[$k]);
					switch ($action)
					{
						case 'add':
						case 'update':
						case 'refresh':
							if (!in_array($v, $tmp))
								$tmp[] = $v;
							break;
						case 'remove':
							if (($tkey = array_search($v, $tmp)) !== FALSE)
								unset($tmp[$tkey]);
							break;
					}
					$pref[$k] = implode(',', $tmp); // Leaves an empty element if no values - probably acceptable or even preferable
					break;
				case 'arrayPref':
					switch ($action)
					{
						case 'add':
							$pref[$k][$path] = $v;
							break;
						case 'update':
						case 'refresh':
							if (!isset($pref[$k][$path]))
								$pref[$k][$path] = $v;
							break;
						case 'remove':
							if (isset($pref[$k][$path]))
								unset($pref[$k][$path]); // Leaves an empty element if no values - probably acceptable or even preferable
							break;
					}
					break;
			}
		}

		e107::getConfig('core')->setPref($pref)->save();

		//	 e107::getConfig()->loadData($pref, false)->save(false, true);
	}

	function manage_comments($action, $comment_id)
	{
		$sql = e107::getDb();
		$tp = e107::getParser();

		$tmp = array();
		if ($action == 'remove')
		{
			foreach ($comment_id as $com)
			{
				$tmp[] = "comment_type='".$tp->toDB($com, true)."'";
			}
			$qry = implode(" OR ", $tmp);
			//			echo $qry."<br />";
			return $sql->db_Delete('comments', $qry);
		}
	}

	// Handle table updates - passed an array of actions.
	// $var array:
	//   For 'add' - its a query to create the table
	//	 For 'upgrade' - its a query to modify the table (not called from the plugin.xml handler)
	//	 For 'remove' - its a table name
	//  'upgrade' and 'remove' operate on all language variants of the same table
	function manage_tables($action, $var)
	{
		$sql = e107::getDB();
		$mes = e107::getMessage();
		
		if (!is_array($var))
			return FALSE; // Return if nothing to do
		$error = false;
		$error_data = array();
		switch ($action)
		{
			case 'add':
				foreach ($var as $tab)
				{
										
					if(!preg_match("/MyISAM.*CHARSET ?= ?utf8/i",$tab))
					{
						$tab = str_replace("MyISAM", "MyISAM DEFAULT CHARSET=utf8", $tab);		
					}
					
					$mes->addDebug($tab);
					if (false === $sql->db_Query($tab))
					{
						$error = true;
						$error_data[] = $tab;
					}
				}
				break;
			case 'upgrade':
				foreach ($var as $tab)
				{
					if (false === $sql->db_Query_all($tab))
					{
						$error = true;
						$error_data[] = $tab;
					}
				}
				break;
			case 'remove':
				foreach ($var as $tab)
				{
					$qry = 'DROP TABLE '.MPREFIX.$tab;
					if (!$sql->db_Query_all($qry))
					{
						$error = true;
						$error_data[] = $tab;
					}
				}
				break;
		}
		// doesn't exit the loop now, returns true on success
		// or error queries (string)
		return (!$error ? true : (!empty($$error_data) ? implode('<br />', $error_data) : false));
	}

	// DEPRECATED for 0.8 xml files - See XmlPrefs();
	// Handle prefs from arrays (mostly 0.7 stuff, possibly apart from the special cases)
	function manage_plugin_prefs($action, $prefname, $plugin_folder, $varArray = '')
	{ // These prefs are 'cumulative' - several plugins may contribute an array element
		//	global $pref;
		/*
		 if ($prefname == 'plug_sc' || $prefname == 'plug_bb')
		 {  // Special cases - shortcodes and bbcodes - each plugin may contribute several elements
		 foreach($varArray as $code)
		 {
		 $prefvals[] = "$code:$plugin_folder";
		 }
		 }
		 else
		 {
		 */
		$prefvals[] = $varArray;
		//			$prefvals[] = $plugin_folder;
		//		}
		$curvals = explode(',', $pref[$prefname]);

		if ($action == 'add')
		{
			$newvals = array_merge($curvals, $prefvals);
		}
		if ($action == 'remove')
		{
			foreach ($prefvals as $v)
			{
				if (($i = array_search($v, $curvals)) !== FALSE)
				{
					unset($curvals[$i]);
				}
			}
			$newvals = $curvals;
		}
		$newvals = array_unique($newvals);
		$pref[$prefname] = implode(',', $newvals);

		if (substr($pref[$prefname], 0, 1) == ",")
		{
			$pref[$prefname] = substr($pref[$prefname], 1);
		}

		e107::getConfig('core')->setPref($pref);
		e107::getConfig('core')->save();

	}

	function manage_search($action, $eplug_folder)
	{
		global $sysprefs;
		$sql = e107::getDb();

		$search_prefs = e107::getConfig('search')->getPref();

		//	$search_prefs = $sysprefs -> getArray('search_prefs');
		$default = file_exists(e_PLUGIN.$eplug_folder.'/e_search.php') ? TRUE : FALSE;
		$comments = file_exists(e_PLUGIN.$eplug_folder.'/search/search_comments.php') ? TRUE : FALSE;
		if ($action == 'add')
		{
			$install_default = $default ? TRUE : FALSE;
			$install_comments = $comments ? TRUE : FALSE;
		}
		else
			if ($action == 'remove')
			{
				$uninstall_default = isset($search_prefs['plug_handlers'][$eplug_folder]) ? TRUE : FALSE;
				$uninstall_comments = isset($search_prefs['comments_handlers'][$eplug_folder]) ? TRUE : FALSE;
			}
			else
				if ($action == 'upgrade')
				{
					if (isset($search_prefs['plug_handlers'][$eplug_folder]))
					{
						$uninstall_default = $default ? FALSE : TRUE;
					}
					else
					{
						$install_default = $default ? TRUE : FALSE;
					}
					if (isset($search_prefs['comments_handlers'][$eplug_folder]))
					{
						$uninstall_comments = $comments ? FALSE : TRUE;
					}
					else
					{
						$install_comments = $comments ? TRUE : FALSE;
					}
				}
		if (vartrue($install_default))
		{
			$search_prefs['plug_handlers'][$eplug_folder] = array('class' => 0, 'pre_title' => 1, 'pre_title_alt' => '', 'chars' => 150, 'results' => 10);
		}
		else
			if (vartrue($uninstall_default))
			{
				unset($search_prefs['plug_handlers'][$eplug_folder]);
			}
		if (vartrue($install_comments))
		{
			require_once(e_PLUGIN.$eplug_folder.'/search/search_comments.php');
			$search_prefs['comments_handlers'][$eplug_folder] = array('id' => $comments_type_id, 'class' => 0, 'dir' => $eplug_folder);
		}
		else
			if (vartrue($uninstall_comments))
			{
				unset($search_prefs['comments_handlers'][$eplug_folder]);
			}

		e107::getConfig('search')->setPref($search_prefs)->save();

	}

	function manage_notify($action, $eplug_folder)
	{
		$tp = e107::getParser();
		//	$notify_prefs = $sysprefs -> get('notify_prefs');
		//	$notify_prefs = $eArrayStorage -> ReadArray($notify_prefs);

		$notify_prefs = e107::getConfig('notify');
		$e_notify = file_exists(e_PLUGIN.$eplug_folder.'/e_notify.php') ? TRUE : FALSE;
		if ($action == 'add')
		{
			$install_notify = $e_notify ? TRUE : FALSE;
		}
		else
			if ($action == 'remove')
			{
				$uninstall_notify = $notify_prefs->isData('plugins/'.$eplug_folder); //isset($notify_prefs['plugins'][$eplug_folder]) ? TRUE : FALSE;
				}
			else
				if ($action == 'upgrade')
				{
					if ($notify_prefs->isData('plugins/'.$eplug_folder))
					{
						$uninstall_notify = $e_notify ? FALSE : TRUE;
					}
					else
					{
						$install_notify = $e_notify ? TRUE : FALSE;
					}
				}
		if (vartrue($install_notify))
		{
			$notify_prefs->setPref('plugins/'.$eplug_folder, 1); //$notify_prefs['plugins'][$eplug_folder] = TRUE;
			require_once(e_PLUGIN.$eplug_folder.'/e_notify.php');
			foreach ($config_events as $event_id => $event_text)
			{
				$notify_prefs->setPref('event/'.$event_id.'/class', e_UC_NOBODY)
					->setPref('event/'.$event_id.'/email', '');
				//$notify_prefs['event'][$event_id] = array('class' => e_UC_NOBODY, 'email' => '');
				}
		}
		else
			if (vartrue($uninstall_notify))
			{
				$notify_prefs->removePref('plugins/'.$eplug_folder);
				//unset($notify_prefs['plugins'][$eplug_folder]);
				require_once(e_PLUGIN.$eplug_folder.'/e_notify.php');
				foreach ($config_events as $event_id => $event_text)
				{
					$notify_prefs->removePref('event/'.$event_id);
					//unset($notify_prefs['event'][$event_id]);
					}
			}
		//$s_prefs = $tp -> toDB($notify_prefs);
		//$s_prefs = e107::getArrayStorage()->WriteArray($s_prefs);
		//e107::getDb() -> db_Update("core", "e107_value='".$s_prefs."' WHERE e107_name='notify_prefs'");
		$notify_prefs->save(false);
	}

	/**
	 * Rebuild URL configuration values
	 * Note - new core system pref values will be set, but not saved
	 * e107::getConfig()->save() is required outside after execution of this method 
	 * @return void
	 */
	public function rebuildUrlConfig()
	{
		
		$modules = eRouter::adminReadModules(); // get all available locations, non installed plugins will be ignored
		$config = eRouter::adminBuildConfig(e107::getPref('url_config'), $modules); // merge with current config
		$locations = eRouter::adminBuildLocations($modules); // rebuild locations pref
		$aliases = eRouter::adminSyncAliases(e107::getPref('url_aliases'), $config); // rebuild aliases
			
		// set new values, changes should be saved outside this methods
		e107::getConfig()
			->set('url_aliases', $aliases)
			->set('url_config', $config)
			->set('url_modules', $modules)
			->set('url_locations', $locations);
				
		eRouter::clearCache();
	}

	function displayArray(&$array, $msg = '')
	{
		$txt = ($msg ? $msg.'<br />' : '');
		foreach ($array as $_k => $_v)
		{
			$txt .= "{$_k} -> {$_v}<br />";
		}
		$txt .= '<br />';
		return $txt;
	}

	/**
	 * Install routine for XML file
	 * @param object $id (the number of the plugin in the DB) or the path to the plugin folder. eg. 'forum' 
	 * @param object $function install|upgrade|uninstall|refresh (adds things that are missing, but doesn't change any existing settings)
	 * @param object $options [optional] an array of possible options - ATM used only for uninstall:
	 * 			'delete_userclasses' - to delete userclasses created
	 * 			'delete_tables' - to delete DB tables
	 * 			'delete_xfields' - to delete extended fields
	 * 			'delete_ipool' - to delete icon pool entry
	 * 			+ any defined in <pluginname>_setup.php in the uninstall_options() method.
	 * @return TBD
	 */
	function install_plugin_xml($id, $function = '', $options = FALSE)
	{	
		if(!is_numeric($id))
		{
			$id = $this->getId($id);	// use path instead. 
		}
				
		$pref = e107::getPref();
		$sql = e107::getDb();
		$mes = e107::getMessage();

		$error = array(); // Array of error messages
		$canContinue = TRUE; // Clear flag if must abort part way through

		$id = (int) $id;
		$plug = $this->getinfo($id); // Get plugin info from DB
		$this->current_plug = $plug;
		$txt = '';
		$path = e_PLUGIN.$plug['plugin_path'].'/';

		$this->plugFolder = $plug['plugin_path'];
		
	
		
		$this->unInstallOpts = $options;

		$addons = explode(',', $plug['plugin_addons']);
		$sql_list = array();
		foreach ($addons as $addon)
		{
			if (substr($addon, -4) == '_sql')
			{
				$sql_list[] = $addon.'.php';
			}
		}

		if (!file_exists($path.'plugin.xml') || $function == '')
		{
			$error[] = EPL_ADLAN_77;
			$canContinue = FALSE;
		}

		if ($canContinue && $this->parse_plugin_xml($plug['plugin_path']))
		{
			$plug_vars = $this->plug_vars;
		}
		else
		{
			$error[] = EPL_ADLAN_76;
			$canContinue = FALSE;
		}
			
		// Load install longuage file and set lan_global pref. 
		$this->XmlLanguageFiles($function, $plug_vars['languageFiles'], 'pre'); // First of all, see if there's a language file specific to install

		// Next most important, if installing or upgrading, check that any dependencies are met
		if ($canContinue && ($function != 'uninstall') && isset($plug_vars['dependencies']))
		{
			$canContinue = $this->XmlDependencies($plug_vars['dependencies']);
		}

		if (!$canContinue)
		{
			return FALSE;
		}

		// All the dependencies are OK - can start the install now
		
		if ($canContinue) // Run custom {plugin}_setup install/upgrade etc. for INSERT, ALTER etc. etc. etc. 
		{
			$ret = $this->execute_function($plug['plugin_path'], $function, 'pre'); 
			if (!is_bool($ret))
				$txt .= $ret;
		}
	
			
		// Handle tables
		if ($canContinue && count($sql_list)) // TODO - move to it's own function. XmlTables(). 
		{ 

			require_once(e_HANDLER.'db_table_admin_class.php');
			$dbHandler = new db_table_admin;
			foreach ($sql_list as $sqlFile)
			{
				$tableList = $dbHandler->get_table_def('', $path.$sqlFile);
				if (!is_array($tableList))
				{
					$mes->add("Can't read SQL definition: ".$path.$sqlFile, E_MESSAGE_ERROR);
					break;
				}
				// Got the required definition here

				foreach ($tableList as $ct)
				{ // Process one table at a time (but they could be multi-language)
					switch ($function)
					{
						case 'install':
							$sqlTable = str_replace("CREATE TABLE ".MPREFIX.'`', "CREATE TABLE `".MPREFIX, preg_replace("/create table\s+/si", "CREATE TABLE ".MPREFIX, $ct[0]));
							$txt = "Adding Table: {$ct[1]} ... ";
							$status = $this->manage_tables('add', array($sqlTable)) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR; // Pass the statement to create the table
							$mes->add($txt, $status);
							break;
						case 'upgrade':
							$tmp = $dbHandler->update_table_structure($ct, FALSE, TRUE, $pref['multilanguage']);
							if ($tmp === FALSE)
							{
								$error[] = 'Error Updating Table: '.$ct[1];
							}
							elseif ($tmp !== TRUE)
							{
								$error[] = $tmp;
							}
							break;
						case 'refresh': // Leave things alone
							break;
						case 'uninstall':
							if (vartrue($options['delete_tables'], FALSE))
							{
								$txt = "Removing Table: {$ct[1]} <br />";
								$status = $this->manage_tables('remove', array($ct[1])) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR; // Delete the table
								$mes->add($txt, $status);
							}
							else
							{
								$mes->add("Table {$ct[1]} left in place.", E_MESSAGE_SUCCESS);
							}
							break;
					}
				}
			}
		}


		if (varset($plug_vars['adminLinks']))
		{
			$this->XmlAdminLinks($function, $plug_vars['adminLinks']);
		}

		if (varset($plug_vars['siteLinks']))
		{
			$this->XmlSiteLinks($function, $plug_vars['siteLinks']);
		}

		if (varset($plug_vars['mainPrefs'])) //Core pref items <mainPrefs>
		{
			$this->XmlPrefs('core', $function, $plug_vars['mainPrefs']);
		}

		if (varset($plug_vars['pluginPrefs'])) //Plugin pref items <pluginPrefs>
		{
			$this->XmlPrefs($plug['plugin_path'], $function, $plug_vars['pluginPrefs']);
		}

		if (varset($plug_vars['userClasses']))
		{
			$this->XmlUserClasses($function, $plug_vars['userClasses']);
		}

		if (varset($plug_vars['extendedFields']))
		{
			$this->XmlExtendedFields($function, $plug_vars['extendedFields']);
		}

		if (varset($plug_vars['languageFiles']))
		{
			$this->XmlLanguageFiles($function, $plug_vars['languageFiles']);
		}
		
		if (varset($plug_vars['bbcodes']))
		{
			$this->XmlBBcodes($function, $plug_vars);
		}
		
		if (varset($plug_vars['mediaCategories']))
		{
			$this->XmlMediaCategories($function, $plug_vars);
		}

		$this->manage_icons($this->plugFolder, $function);

		//FIXME
		//If any commentIDs are configured, we need to remove all comments on uninstall
		if ($function == 'uninstall' && isset($plug_vars['commentID']))
		{
			$txt .= 'Removing all plugin comments: ('.implode(', ', $plug_vars['commentID']).')<br />';
			$this->manage_comments('remove', $commentArray);
		}

		$this->manage_search($function, $plug_vars['folder']);
		$this->manage_notify($function, $plug_vars['folder']);

		$eplug_addons = $this->getAddons($plug['plugin_path']);

		$p_installed = e107::getPref('plug_installed', array()); // load preference;

		if ($function == 'install' || $function == 'upgrade')
		{
			$sql->db_Update('plugin', "plugin_installflag = 1, plugin_addons = '{$eplug_addons}', plugin_version = '{$plug_vars['@attributes']['version']}', plugin_category ='".$this->manage_category($plug_vars['category'])."', plugin_releaseUrl= '".varset($plug_vars['@attributes']['releaseUrl'])."' WHERE plugin_id = ".$id);
			$p_installed[$plug['plugin_path']] = $plug_vars['@attributes']['version'];

			e107::getConfig('core')->setPref('plug_installed', $p_installed);
			//e107::getConfig('core')->save(); - save triggered below
		}

		if ($function == 'uninstall')
		{
			$sql->db_Update('plugin', "plugin_installflag = 0, plugin_addons = '{$eplug_addons}', plugin_version = '{$plug_vars['@attributes']['version']}', plugin_category ='".$this->manage_category($plug_vars['category'])."', plugin_releaseUrl= '".varset($plug_vars['@attributes']['releaseUrl'])."' WHERE plugin_id = ".$id);
			unset($p_installed[$plug['plugin_path']]);
			e107::getConfig('core')->setPref('plug_installed', $p_installed);

		}
		
		$this->rebuildUrlConfig();

		e107::getConfig('core')->save();

		/*	if($function == 'install')
		 {
		 if(isset($plug_vars['management']['installDone'][0]))
		 {
		 $mes->add($plug_vars['management']['installDone'][0], E_MESSAGE_SUCCESS);
		 }
		 }*/

		// Run custom {plugin}_setup install/upgrade etc. for INSERT, ALTER etc. etc. etc. 
		// Call any custom post functions defined in <plugin>_setup.php or the deprecated <management> section
		if (!$this->execute_function($plug['plugin_path'], $function, 'post')) 
		{
			if ($function == 'install')
			{
				$text = "Installation Complete.";

				if ($this->plugConfigFile)
				{
					$text .= "&nbsp;<a href='".$this->plugConfigFile."'>[".LAN_CONFIGURE."]</a>";
				}

				$mes->add($text, E_MESSAGE_SUCCESS);
			}

		}

	}

	// Placeholder. 
	function XmlTables($data)
	{
					
	}
				
			
		
	
	
	
	/**
	 * Process XML Tag <dependencies> (deprecated 'depend' which is a brand of adult diapers)
	 * @param array $tag
	 * @return boolean
	 */
	function XmlDependencies($tag)
	{
		$canContinue = TRUE;
		$mes = eMessage::getInstance();
		$error = array();

		foreach ($tag as $dt => $dv)
		{
			if (isset($dv['@attributes']) && isset($dv['@attributes']['name']))
			{
				//			  echo "Check {$dt} dependency: {$dv['@attributes']['name']} version {$dv['@attributes']['min_version']}<br />";
				switch ($dt)
				{
					case 'plugin':
						if (!isset($pref['plug_installed'][$dv['@attributes']['name']]))
						{ // Plugin not installed
							$canContinue = FALSE;
							$error[] = EPL_ADLAN_70.$dv['@attributes']['name'];
						}
						elseif (isset($dv['@attributes']['min_version']) && (version_compare($dv['@attributes']['min_version'], $pref['plug_installed'][$dv['@attributes']['name']], '<=') === FALSE))
						{
							$error[] = EPL_ADLAN_71.$dv['@attributes']['name'].EPL_ADLAN_72.$dv['@attributes']['min_version'];
							$canContinue = FALSE;
						}
						break;
					case 'extension':
						if (!extension_loaded($dv['@attributes']['name']))
						{
							$canContinue = FALSE;
							$error[] = EPL_ADLAN_73.$dv['@attributes']['name'];
						}
						elseif (isset($dv['@attributes']['min_version']) && (version_compare($dv['@attributes']['min_version'], phpversion($dv['@attributes']['name']), '<=') === FALSE))
						{
							$error[] = EPL_ADLAN_71.$dv['@attributes']['name'].EPL_ADLAN_72.$dv['@attributes']['min_version'];
							$canContinue = FALSE;
						}
						break;
					case 'php': // all should be lowercase
						if (isset($dv['@attributes']['min_version']) && (version_compare($dv['@attributes']['min_version'], phpversion(), '<=') === FALSE))
						{
							$error[] = EPL_ADLAN_74.$dv['@attributes']['min_version'];
							$canContinue = FALSE;
						}
						break;
					case 'mysql': // all should be lowercase
						if (isset($dv['@attributes']['min_version']) && (version_compare($dv['@attributes']['min_version'], mysql_get_server_info(), '<=') === FALSE))
						{
							$error[] = EPL_ADLAN_75.$dv['@attributes']['min_version'];
							$canContinue = FALSE;
						}
						break;
					default:
						echo "Unknown dependency: {$dt}<br />";
				}
			}
		}

		if (count($error))
		{
			$text = '<b>'.LAN_INSTALL_FAIL.'</b><br />'.implode('<br />', $error);
			$mes->add($text, E_MESSAGE_ERROR);
		}

		return $canContinue;
	}

	/**
	 * Process XML Tag <LanguageFiles> // DEPRECATED - using _install _log and _global
	 * @param object $function
	 * @param object $tag
	 * @return none
	 */
	function XmlLanguageFiles($function, $tag='', $when = '')
	{
		$core = e107::getConfig('core');
	
		$updated = false;
		
		$path_a = e_PLUGIN.$this->plugFolder."/languages/English_install.php"; // always check for English so we have a fall-back
		$path_b = e_PLUGIN.$this->plugFolder."/languages/English/English_install.php";		
		
		if(file_exists($path_a) || file_exists($path_b))
		{
			e107::lan($this->plugFolder,'install',true);	
		}
			
		$path_a = e_PLUGIN.$this->plugFolder."/languages/English_global.php"; // always check for English so we have a fall-back
		$path_b = e_PLUGIN.$this->plugFolder."/languages/English/English_global.php";		
		
		if(file_exists($path_a) || file_exists($path_b))
		{
			switch ($function)
			{
				case 'install':
				case 'upgrade':
				case 'refresh':
					e107::getMessage()->addDebug("Adding ".$this->plugFolder." to lan_global_list");
					e107::lan($this->plugFolder,'global',true);
					$core->setPref('lan_global_list/'.$this->plugFolder, $this->plugFolder);
					$updated = true;
					break;
				case 'uninstall':
					$core->removePref('lan_global_list/'.$this->plugFolder);
					$update = true;
				break;
			}	
		}
			
	
		$path_a = e_PLUGIN.$this->plugFolder."/languages/English_log.php";  // always check for English so we have a fall-back
		$path_b = e_PLUGIN.$this->plugFolder."/languages/English/English_log.php";
		
		if(file_exists($path_a) || file_exists($path_b))
		{
			switch ($function)
			{
				case 'install':
				case 'upgrade':
				case 'refresh':
					$core->setPref('lan_log_list/'.$this->plugFolder, $this->plugFolder);
					$updated = true;
					break;
				case 'uninstall':
					$core->removePref('lan_log_list/'.$this->plugFolder);
					$updated = true;
				break;
			}	
		}
				
	
		if($updated === true)
		{
			$core->save();	//FIXME do this quietly without an s-message
		}
	
	}

	/**
	 * Process XML Tag <siteLinks>
	 * @param string $function install|upgrade|refresh|uninstall
	 * @param array $array
	 * @return none
	 */
	function XmlSiteLinks($function, $array)
	{
		$mes = e107::getMessage();

		foreach ($array['link'] as $link)
		{
			$attrib 	= $link['@attributes'];
			$linkName 	= (defset($link['@value'])) ? constant($link['@value']) : $link['@value'];
			$remove 	= (varset($attrib['deprecate']) == 'true') ? TRUE : FALSE;
			$url 		= vartrue($attrib['url']);
			$perm 		= vartrue($attrib['perm'],'everyone'); 
			
			$options 	= array(
				'link_function'=>vartrue($attrib['function'])
			);

			switch ($function)
			{
				case 'upgrade':
				case 'install':

					if (!$remove) // Add any non-deprecated link
					{
						$result = $this->manage_link('add', $url, $linkName, $perm, $options);
						if($result !== NULL)
						{
							$status = ($result) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
							$mes->add("Adding Link: {$linkName} with url [{$url}] and perm {$perm} ", $status); //TODO LAN
						}					
					}

					if ($function == 'upgrade' && $remove) //remove inactive links on upgrade
					{
						$status = ($this->manage_link('remove', $url, $linkName,false, $options)) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
						$mes->add("Removing Link: {$linkName} with url [{$url}]", $status);
					}
					break;

				case 'refresh': // Probably best to leave well alone
					break;

				case 'uninstall': //remove all links

					$status = ($this->manage_link('remove', $url, $linkName)) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
					$mes->add("Removing Link: {$linkName} with url [{$url}]", $status);
					break;
			}
		}
	}

	/**
	 * Process XML Tag <adminLinks>
	 * @return none
	 */
	function XmlAdminLinks($function, $tag)
	{
		foreach ($tag['link'] as $link)
		{
			$attrib = $link['@attributes'];
			$linkName = (defset($link['@value'])) ? constant($link['@value']) : $link['@value'];
			$url = e_PLUGIN_ABS.$this->plugFolder."/".$attrib['url'];
			if (isset($attrib['primary']) && $attrib['primary'] == 'true')
			{
				$this->plugConfigFile = $url;
			}
		}
	}

	// Only 1 category per file-type allowed. ie. 1 for images, 1 for files. 
	function XmlMediaCategories($function, $tag)
	{
		$mes = e107::getMessage();
	//	print_a($tag);
		
		$folder = $tag['folder'];
		$prevType = "";
	
		
		//print_a($tag);
		switch ($function)
		{
			case 'install': 
				$c = 1;
				foreach($tag['mediaCategories']['category'] as $v)
				{
					$type = $v['@attributes']['type'];
					
					if(strpos($type, 'image') !== 0 && strpos($type, 'file') !== 0 && strpos($type, 'video') !== 0)
					{
						continue; 	
					}
					
					if($c == 4 || ($prevType == $type))
					{
						$mes->addDebug("Only 3 Media Categories are permitted during install. One for images and one for files.");
						break;
					}
					
					$prevType = $type;
									
					$data['owner'] = $folder;
					$data['category'] = $folder."_".$type;	
					$data['title'] = $v['@value'];
				//	$data['type'] = $v['@attributes']['type']; //TODO
					$data['class'] = 253;
					$status = e107::getMedia()->createCategory($data) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
					$mes->add("Adding Media Category: {$data['category']}", $status);				
					
					$c++;					
				}	
			
			break;
			
			case 'uninstall': // Probably best to leave well alone
				$status = e107::getMedia()->deleteAllCategories($folder)? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
				$mes->add("Deleting All Media Categories owned by : {$folder}", $status);	
			break;
		
		
		}	
		
		
	}





	
	
	/**
	 * Process XML Tag <bbcodes>
	 * @return none
	 */
	function XmlBBcodes($function, $tag)
	{
		$mes = e107::getMessage();
		//print_a($tag);
		switch ($function)
		{
			case 'install': // Probably best to leave well alone
				if(vartrue($tag['bbcodes']['@attributes']['imgResize']))
				{
					e107::getConfig('core')->setPref('resize_dimensions/'.$this->plugFolder."-bbcode", array('w'=>300,'h'=>300));
					$mes->debug('Adding imageResize for: '.$this->plugFolder);
				}		
			break;
			
			case 'uninstall': // Probably best to leave well alone
				if(vartrue($tag['bbcodes']['@attributes']['imgResize']))
				{
					//e107::getConfig('core')->removePref('resize_dimensions/'.$this->plugFolder);
					//e107::getConfig('core')->removePref('e_imageresize/'.$this->plugFolder);
					e107::getConfig('core')->removePref('resize_dimensions/'.$this->plugFolder."-bbcode");
					$mes->debug('Removing imageResize for: '.$this->plugFolder."-bbcode");
				}
			
			break;	
		}
		
             
		return;

	}

	/**
	 * Process XML Tag <userClasses>
	 * @param string $function install|upgrade|refresh|uninstall
	 * @param array $array
	 * @return none
	 */
	function XmlUserClasses($function, $array)
	{
		$mes = e107::getMessage();

		foreach ($array['class'] as $uclass)
		{
			$attrib = $uclass['@attributes'];
			$name = $attrib['name'];
			$description = $attrib['description'];
			$remove = (varset($attrib['deprecate']) == 'true') ? TRUE : FALSE;

			switch ($function)
			{
				case 'install':
				case 'upgrade':
				case 'refresh':

					if (!$remove) // Add all active userclasses (code checks for already installed)
					{
						$result = $this->manage_userclass('add', $name, $description);
						if($result !== NULL)
						{
							$status = ($result) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
							$mes->add('Adding Userclass: '.$name, $status);	
						}						
					}

					if ($function == 'upgrade' && $remove) //If upgrading, removing any inactive userclass

					{
						$status = $this->manage_userclass('remove', $name, $description) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
						$mes->add('Removing Userclass: '.$name, $status);
					}

					break;

				case 'uninstall': //If uninstalling, remove all userclasses (active or inactive)

					if (varsettrue($this->unInstallOpts['delete_userclasses'], FALSE))
					{
						$status = $this->manage_userclass('remove', $name, $description) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
						$mes->add('Removing Userclass: '.$name, $status);
					}
					else
					{
						$mes->add('Userclass: '.$name.' left in place'.$name, $status);
					}

					break;
			}
		}
	}

	/**
	 * Process XML Tag <extendedFields>
	 * @param string $function install|upgrade|refresh|uninstall
	 * @param array $array
	 * @return none
	 */
	function XmlExtendedFields($function, $array)
	{
		$mes = e107::getMessage();
		$this->setUe();

		foreach ($array['field'] as $efield)
		{
			$attrib = $efield['@attributes'];
			$attrib['default'] = varset($attrib['default']);
			
			$type = $this->ue_field_type($attrib);
			$name = $this->ue_field_name($this->plugFolder, $type, $attrib['name']);
			
			//$name = 'plugin_'.$this->plugFolder.'_'.$attrib['name'];
			$source = 'plugin_'.$this->plugFolder;
			$remove = (varset($attrib['deprecate']) == 'true') ? TRUE : FALSE;

			if(!isset($attrib['system'])) $attrib['system'] = true; // default true
			else $attrib['system'] = $attrib['system'] === 'true' ? true : false;

			switch ($function)
			{
				case 'install': // Add all active extended fields
					case 'upgrade':

					if (!$remove)
					{
						//$status = $this->manage_extended_field('add', $name, $type, $attrib['default'], $source) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;

						$status = $this->manage_extended_field('add', $name, $attrib, $source) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
						$mes->add('Adding Extended Field: '.$name.' ... ', $status);
					}

					if ($function == 'upgrade' && $remove) //If upgrading, removing any inactive extended fields

					{
						$status = $this->manage_extended_field('remove', $name, $attrib, $source) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
						$mes->add('Removing Extended Field: '.$name.' ... ', $status);
					}
					break;

				case 'uninstall': //If uninstalling, remove all extended fields (active or inactive)

					if (varsettrue($this->unInstallOpts['delete_xfields'], FALSE))
					{
						$status = ($this->manage_extended_field('remove', $name, $attrib, $source)) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
						$mes->add('Removing Extended Field: '.$name.' ... ', $status);
					}
					else
					{
						$mes->add('Extended Field: '.$name.' left in place'.$name, E_MESSAGE_SUCCESS);
					}
					break;
			}
		}
	}

	/**
	 * Process XML tags <mainPrefs> and <pluginPrefs>
	 * @param object $mode 'core' or the folder name of the plugin.
	 * @param object $function install|uninstall|upgrade|refresh
	 * @param object $prefArray XML array of prefs. eg. mainPref() or pluginPref();
	 * @return none
	 */
	function XmlPrefs($mode = 'core', $function, $prefArray)
	{

		//XXX Could also be used for theme prefs.. perhaps this function should be moved elsewhere?
		//TODO array support for prefs. <key>? or array() as used in xml site export?

		$mes = e107::getMessage();

		if (!varset($prefArray) || !varset($prefArray))
		{
			return;
		}

		$config = ($mode == 'core') ? e107::getConfig('core') : e107::getPlugConfig($mode);

		foreach ($prefArray['pref'] as $tag)
		{
			$key = varset($tag['@attributes']['name']);
			$value = vartrue($tag['@value']);
			$remove = (varset($tag['@attributes']['deprecate']) == 'true') ? TRUE : FALSE;

			if (varset($tag['@attributes']['value']))
			{
				$mes->add("Deprecated plugin.xml spec. found. Use the following format: ".htmlentities("<pref name='name'>value</pref>"), E_MESSAGE_ERROR);
			}

			switch ($function)
			{
				case 'install':
				case 'upgrade':
					$ret = $config->add($key, $value);
					if($ret->data_has_changed == TRUE)
					{
						$mes->add("Adding Pref: ".$key, E_MESSAGE_SUCCESS);	
					}								
					break;

				
				case 'refresh':
					if ($remove) // remove active='false' prefs.

					{
						$config->remove($key, $value);
						$mes->add("Removing Pref: ".$key, E_MESSAGE_SUCCESS);
					}
					else
					{
						$config->update($key, $value);
						$mes->add("Updating Pref: ".$key, E_MESSAGE_SUCCESS);
					}

					break;

				case 'uninstall':
					$config->remove($key, $value);
					$mes->add("Removing Pref: ".$key, E_MESSAGE_SUCCESS);
					break;
			}
		}

		if ($mode != "core") // Do only one core pref save during install/uninstall etc.
		{
			$config->save();
		}
		return;
	}

	/**
	 *
	 * @param object $path [unused]
	 * @param object $what install|uninstall|upgrade
	 * @param object $when pre|post
	 * @return boolean FALSE
	 */
	function execute_function($path = null, $what = '', $when = '')
	{
		$mes = eMessage::getInstance();
		
		if($path == null)
		{
			$path = $this->plugFolder;	
		}
		
		$class_name = $path."_setup"; // was using $this->pluginFolder; 
		$method_name = $what."_".$when;
		
		
			// {PLUGIN}_setup.php should ALWAYS be the name of the file.. 
			
			
	//	if (varset($this->plug_vars['@attributes']['setupFile']))
	//	{
	//		$setup_file = e_PLUGIN.$this->plugFolder.'/'.$this->plug_vars['@attributes']['setupFile'];
	//	}
	//	else
	//	{
			$setup_file = e_PLUGIN.$path.'/'.$path.'_setup.php';
	//	}

		if (is_readable($setup_file))
		{
			if(e_PAGE == 'e107_update.php')
			{
				$mes->add("Found setup file <b>".$path."_setup.php</b> ", E_MESSAGE_DEBUG);
			}
			
			include_once($setup_file);
			

			if (class_exists($class_name))
			{
				$obj = new $class_name;
				$obj->version_from = $this;
				
				if (method_exists($obj, $method_name))
				{
					if(e_PAGE == 'e107_update.php')
					{
						$mes->add("Executing setup function <b>".$class_name." :: ".$method_name."()</b>", E_MESSAGE_DEBUG);
					}
					return call_user_func(array($obj, $method_name), $this);
				}
				else
				{
					if(e_PAGE == 'e107_update.php')
					{
						$mes->add("Setup function ".$class_name." :: ".$method_name."() NOT found.", E_MESSAGE_DEBUG);
					}
					return FALSE;
				}
			}
			else
			{
			//	$mes->add("Setup Class ".$class_name." NOT found.", E_MESSAGE_DEBUG);
				return FALSE;
			}
		}
		else
		{
			//$mes->add("Optional Setup File NOT Found ".$path."_setup.php ", E_MESSAGE_DEBUG);
		}

		return FALSE; // IMPORTANT.
	}

	// DEPRECATED - See XMLPrefs();
	function parse_prefs($pref_array, $mode = 'simple')
	{
		$ret = array();
		if (!isset($pref_array[0]))
		{
			$pref_array = array($pref_array);
		}
		if (is_array($pref_array))
		{
			foreach ($pref_array as $k => $p)
			{
				$attrib = $p['@attributes'];
				if (isset($attrib['type']) && $attrib['type'] == 'array')
				{
					$name = $attrib['name'];
					$tmp = $this->parse_prefs($pref_array[$k]['key']);
					$ret['all'][$name] = $tmp['all'];
					$ret['active'][$name] = $tmp['active'];
					$ret['inactive'][$name] = $tmp['inactive'];
				}
				else
				{
					$ret['all'][$attrib['name']] = $attrib['value'];
					if (!isset($attrib['active']) || $attrib['active'] == 'true')
					{
						$ret['active'][$attrib['name']] = $attrib['value'];
					}
					else
					{
						$ret['inactive'][$attrib['name']] = $attrib['value'];
					}
				}
			}
		}
		return $ret;
	}

	function install_plugin_php($id)
	{
		$function = 'install';
		$sql = e107::getDb();
		$mes = e107::getMessage();
		$mySQLprefix = MPREFIX; // Fix for some plugin.php files.

		$plug = $this->getinfo($id);
		$_path = e_PLUGIN.$plug['plugin_path'].'/';

		$plug['plug_action'] = 'install';

		$this->parse_plugin_php($plug['plugin_path']);
		$plug_vars = $this->plug_vars;

		include($_path.'plugin.php');

		$func = $eplug_folder.'_install';
		if (function_exists($func))
		{
			$text .= call_user_func($func);
		}

		if (is_array($eplug_tables))
		{
			$result = $this->manage_tables('add', $eplug_tables);
			if ($result === TRUE)
			{
				$text .= EPL_ADLAN_19.'<br />';

				$mes->add(EPL_ADLAN_19, E_MESSAGE_SUCCESS);
				//success
				}
			else
			{
				$mes->add(EPL_ADLAN_18, E_MESSAGE_ERROR);
				//fail
				}
		}

		/*		if (is_array($eplug_prefs))
		 {
		 $this->manage_prefs('add', $eplug_prefs);
		 $text .= EPL_ADLAN_8.'<br />';
		 }*/

		if (varset($plug_vars['mainPrefs'])) //Core pref items <mainPrefs>

		{
			$this->XmlPrefs('core', $function, $plug_vars['mainPrefs']);
			$text .= EPL_ADLAN_8.'<br />';
		}

		if (is_array($eplug_array_pref))
		{
			foreach ($eplug_array_pref as $key => $val)
			{
				$this->manage_plugin_prefs('add', $key, $eplug_folder, $val);
			}
		}

		if (varset($plug_vars['siteLinks']))
		{
			$this->XmlSiteLinks($function, $plug_vars['siteLinks']);
		}

		if (varset($plug_vars['userClasses']))
		{
			$this->XmlUserClasses($function, $plug_vars['userClasses']);
		}

		$this->manage_search('add', $eplug_folder);

		$this->manage_notify('add', $eplug_folder);

		$eplug_addons = $this->getAddons($eplug_folder);

		$sql->db_Update('plugin', "plugin_installflag = 1, plugin_addons = '{$eplug_addons}' WHERE plugin_id = ".(int) $id);

		$p_installed = e107::getPref('plug_installed', array()); // load preference;
		$p_installed[$plug['plugin_path']] = $plug['plugin_version'];

		e107::getConfig('core')->setPref('plug_installed', $p_installed);
		
		$this->rebuildUrlConfig();
		
		e107::getConfig('core')->save();

		$text .= (isset($eplug_done) ? "<br />{$eplug_done}" : "<br />".LAN_INSTALL_SUCCESSFUL);
		if ($eplug_conffile)
		{
			$text .= "&nbsp;<a href='".e_PLUGIN.$eplug_folder."/".$eplug_conffile."'>[".LAN_CONFIGURE."]</a>";
		}

		return $text;
	}

	/**
	 * Installs a plugin by ID
	 *
	 * @param int $id
	 */
	function install_plugin($id)
	{
		global $ns, $sysprefs, $mySQLprefix;

		$sql = e107::getDb();
		$tp = e107::getParser();

		$text = '';

		// install plugin ...
		$id = (int) $id;
		$plug = $this->getinfo($id);

		$plug['plug_action'] = 'install';

		if (!vartrue($plug['plugin_installflag']))
		{
			$_path = e_PLUGIN.$plug['plugin_path'].'/';
			if (file_exists($_path.'plugin.xml'))
			{
				$text = $this->install_plugin_xml($id, 'install');
			}
			elseif (file_exists($_path.'plugin.php'))
			{
				$text = $this->install_plugin_php($id);
			}
		}
		else
		{
			$text = EPL_ADLAN_21;
		}
		return $text;
	}

	function save_addon_prefs() // scan the plugin table and create path-array-prefs for each addon.
	{
		$sql = e107::getDb();
		$core = e107::getConfig('core');

		foreach ($this->plugin_addons as $var) // clear all existing prefs.

		{
			$core->update($var.'_list', "");
		}

		$query = "SELECT * FROM #plugin WHERE plugin_addons !='' ORDER BY plugin_path ASC";

		if ($sql->db_Select_gen($query))
		{
			while ($row = $sql->db_Fetch())
			{
				$is_installed = ($row['plugin_installflag'] == 1);
				$tmp = explode(",", $row['plugin_addons']);
				$path = $row['plugin_path'];

				if ($is_installed)
				{
					foreach ($tmp as $val)
					{
						if (strpos($val, 'e_') === 0)
						{
							// $addpref[$val."_list"][$path] = $path;
							$core->setPref($val.'_list/'.$path, $path);
						}
					}
				}

				// search for .bb and .sc files.
				$sc_array = array();
				$bb_array = array();
				$sql_array = array();

				foreach ($tmp as $adds)
				{
					if (substr($adds, -3) == ".sc")
					{
						$sc_name = substr($adds, 0, -3); // remove the .sc
						if ($is_installed)
						{
							$sc_array[$sc_name] = "0"; // default userclass = e_UC_PUBLIC
							}
						else
						{
							$sc_array[$sc_name] = e_UC_NOBODY; // register shortcode, but disable it
						}
					}
					
					if($is_installed)
					{
						// simple bbcode
						if(substr($adds,-3) == ".bb")
						{
							$bb_name = substr($adds, 0,-3); // remove the .bb
	                    	$bb_array[$bb_name] = "0"; // default userclass.
						}
						// bbcode class
						elseif(substr($adds, 0, 3) == "bb_" && substr($adds, -4) == ".php") 
						{
							$bb_name = substr($adds, 0,-4); // remove the .php
							$bb_name = substr($bb_name, 3);
	                    	$bb_array[$bb_name] = "0"; // TODO - instance and getPermissions() method
						}
					}

					if ($is_installed && (substr($adds, -4) == "_sql"))
					{
						$core->setPref('e_sql_list/'.$path, $adds);
					}
				}

				// Build Bbcode list (will be empty if plugin not installed)
				if (count($bb_array) > 0)
				{
					ksort($bb_array);
					$core->setPref('bbcode_list/'.$path, $bb_array);
				}

				// Build shortcode list - do if uninstalled as well
				if (count($sc_array) > 0)
				{
					ksort($sc_array);
					$core->setPref('shortcode_list/'.$path, $sc_array);
				}
			}
		}

		$core->save(FALSE);

		if ($this->manage_icons())
		{
			// 	echo 'IT WORKED';
			}
		else
		{
			// echo "didn't work!";
			}
		return;
	}

	// return a list of available plugin addons for the specified plugin. e_xxx etc.
	// $debug = TRUE - prints diagnostics
	// $debug = 'check' - checks each file found for php tags - prints 'pass' or 'fail'
	function getAddons($plugin_path, $debug = FALSE)
	{
		$fl = e107::getFile();

		$p_addons = array();

		foreach ($this->plugin_addons as $addon) //Find exact matches only.
		{
			//	if(preg_match("#^(e_.*)\.php$#", $f['fname'], $matches))

			$addonPHP = $addon.".php";

			if (is_readable(e_PLUGIN.$plugin_path."/".$addonPHP))
			{
				if ($debug === 'check')
				{
					$passfail = '';
					$file_text = file_get_contents(e_PLUGIN.$plugin_path."/".$addonPHP);
					if ((substr($file_text, 0, 5) != '<'.'?php') || ((substr($file_text, -2, 2) != '?'.'>') && (strrpos($file_text, '?'.'>') !== FALSE)))
					{
						$passfail = '<b>fail</b>';
					}
					else
					{
						$passfail = 'pass';
					}
					echo $plugin_path."/".$addon.".php - ".$passfail."<br />";
				}
				$mes = e107::getMessage();
				// $mes->add('Detected addon: <b>'.$addon.'</b>', E_MESSAGE_DEBUG);

				$p_addons[] = $addon;
			}
		}

		// Grab List of Shortcodes & BBcodes
		$shortcodeList = $fl->get_files(e_PLUGIN.$plugin_path, '\.sc$', "standard", 1);
		
		$bbcodeList		= $fl->get_files(e_PLUGIN.$plugin_path, '\.bb$', "standard", 1);
		$bbcodeClassList= $fl->get_files(e_PLUGIN.$plugin_path, '^bb_(.*)\.php$', "standard", 1);
		$bbcodeList = array_merge($bbcodeList, $bbcodeClassList);
		
		$sqlList = $fl->get_files(e_PLUGIN.$plugin_path, '_sql\.php$', "standard", 1);
		

		

		// Search Shortcodes
		foreach ($shortcodeList as $sc)
		{
			if (is_readable(e_PLUGIN.$plugin_path."/".$sc['fname']))
			{
				$p_addons[] = $sc['fname'];
			}
		}

		// Search Bbcodes.
		foreach ($bbcodeList as $bb)
		{
			if (is_readable(e_PLUGIN.$plugin_path."/".$bb['fname']))
			{
				$p_addons[] = $bb['fname'];
			}
		}

		// Search _sql files.
		foreach ($sqlList as $esql)
		{
			if (is_readable(e_PLUGIN.$plugin_path."/".$esql['fname']))
			{
				$fname = str_replace(".php", "", $esql['fname']);
				if (!in_array($fname, $p_addons))
					$p_addons[] = $fname; // Probably already found - avoid duplication
				}
		}

		if ($debug == true)
		{
			echo $plugin_path." = ".implode(",", $p_addons)."<br />";
		}

		return implode(",", $p_addons);
	}

	function checkAddon($plugin_path, $e_xxx)
	{ // Return 0 = OK, 1 = Fail, 2 = inaccessible
		if (is_readable(e_PLUGIN.$plugin_path."/".$e_xxx.".php"))
		{
			$file_text = file_get_contents(e_PLUGIN.$plugin_path."/".$e_xxx.".php");
			if ((substr($file_text, 0, 5) != '<'.'?php') || ((substr($file_text, -2, 2) != '?'.'>') && (strrpos($file_text, '?'.'>') !== FALSE)))
			{
				return 1;
			}
			return 0;
		}
		return 2;
	}

	// Entry point to read plugin configuration data
	function parse_plugin($plugName, $force = false)
	{
		$ret = "";

		if (isset($this->parsed_plugin[$plugName]) && $force != true)
		{
			$this->plug_vars = $this->parsed_plugin[$plugName];
			return true;
		}
		unset($this->parsed_plugin[$plugName]); // In case forced parsing which fails
		if (file_exists(e_PLUGIN.$plugName.'/plugin.xml'))
		{
			$ret = $this->parse_plugin_xml($plugName);
		}
		elseif (file_exists(e_PLUGIN.$plugName.'/plugin.php'))
		{
			$ret = $this->parse_plugin_php($plugName);
		}
		if ($ret == true)
		{
			$this->parsed_plugin[$plugName] = $this->plug_vars;
		}

		return $ret;
	}
	
	// return the Icon of the 
	function getIcon($plugName='',$size=32)
	{
		if(!$plugName) return;
		
		if(!isset($this->parsed_plugin[$plugName]))
		{
			$plug_vars = $this->parse_plugin($plugName);	
		}
		else
		{
			$plug_vars = $this->parsed_plugin[$plugName];	
		}
			
		//return print_r($plug_vars,TRUE);	
			
		$sizeArray = array(32=>'icon', 16=>'iconSmall');
		$default = ($size == 32) ? E_32_CAT_PLUG : "<img class='icon S16' src='".E_16_CAT_PLUG."' alt='' />"; 
		$sz = $sizeArray[$size];
		
		$icon_src = e_PLUGIN.$plugName."/".$plug_vars['administration'][$sz];
		$plugin_icon = $plug_vars['administration'][$sz] ? "<img src='{$icon_src}' alt='' class='icon S".intval($size)."' />" : $default;
     	
		if(!$plugin_icon)
		{
		//	
		}
		
     	return $plugin_icon;
	}
	
	

	// Called to parse the (deprecated) plugin.php file
	function parse_plugin_php($plugName)
	{
		$mes = e107::getMessage();
		$tp = e107::getParser();

		if (include(e_PLUGIN.$plugName.'/plugin.php'))
		{
			//$mes->add("Loading ".e_PLUGIN.$plugName.'/plugin.php', E_MESSAGE_DEBUG);
			}

		$ret = array();

		//		$ret['installRequired'] = ($eplug_conffile || is_array($eplug_table_names) || is_array($eplug_prefs) || is_array($eplug_sc) || is_array($eplug_bb) || $eplug_module || $eplug_userclass || $eplug_status || $eplug_latest);

		$ret['@attributes']['name'] = varset($eplug_name);
		$ret['@attributes']['lang'] = varset($eplug_name);
		$ret['@attributes']['version'] = varset($eplug_version);
		$ret['@attributes']['date'] = varset($eplug_date);
		$ret['@attributes']['compatibility'] = varset($eplug_compatible);
		$ret['@attributes']['installRequired'] = ($eplug_conffile || is_array($eplug_table_names) || is_array($eplug_prefs) || $eplug_module || $eplug_userclass || $eplug_status || $eplug_latest) ? 'true' : '';
		$ret['@attributes']['xhtmlcompliant'] = vartrue($eplug_compliant) ? 'true' : '';
		$ret['folder'] = (varset($eplug_folder)) ? $eplug_folder : $plugName;

		$ret['author']['@attributes']['name'] = varset($eplug_author);
		$ret['author']['@attributes']['url'] = varset($eplug_url);
		$ret['author']['@attributes']['email'] = varset($eplug_email);
		$ret['description']['@value'] = varset($eplug_description);
		$ret['description']['@attributes']['lang'] = varset($eplug_description);

		$ret['category'] = varset($eplug_category) ? $this->manage_category($eplug_category) : "misc";
		$ret['readme'] = varset($eplug_readme);

		$ret['menuName'] = varset($eplug_menu_name);

		if (varset($eplug_prefs))
		{
			$c = 0;
			foreach ($eplug_prefs as $name => $value)
			{
				$ret['mainPrefs']['pref'][$c]['@attributes']['name'] = $name;
				$ret['mainPrefs']['pref'][$c]['@value'] = $value;
				$c++;
			}
		}

		// For BC.
		$ret['administration']['icon'] = str_replace($plugName."/","",$eplug_icon);
		$ret['administration']['caption'] = varset($eplug_caption);
		$ret['administration']['iconSmall'] = str_replace($plugName."/","",$eplug_icon_small);
		$ret['administration']['configFile'] = varset($eplug_conffile);

		if (varset($eplug_conffile))
		{
			$ret['adminLinks']['link'][0]['@attributes']['url'] = varset($eplug_conffile);
			$ret['adminLinks']['link'][0]['@attributes']['description'] = LAN_CONFIGURE;
			$ret['adminLinks']['link'][0]['@attributes']['icon'] = str_replace($plugName."/","",$eplug_icon);
			$ret['adminLinks']['link'][0]['@attributes']['iconSmall'] = str_replace($plugName."/","",$eplug_icon_small);
			$ret['adminLinks']['link'][0]['@attributes']['primary'] = 'true';
		}
		if (vartrue($eplug_link) && varset($eplug_link_name) && varset($eplug_link_url))
		{
			$ret['siteLinks']['link'][0]['@attributes']['url'] = $tp->createConstants($eplug_link_url, 1);
			$ret['siteLinks']['link'][0]['@attributes']['perm'] = varset($eplug_link_perms);
			$ret['siteLinks']['link'][0]['@value'] = varset($eplug_link_name);
		}

		if (vartrue($eplug_userclass) && vartrue($eplug_userclass_description))
		{
			$ret['userClasses']['class'][0]['@attributes']['name'] = $eplug_userclass;
			$ret['userClasses']['class'][0]['@attributes']['description'] = $eplug_userclass_description;
		}

		// Set this key so we know the vars came from a plugin.php file
		// $ret['plugin_php'] = true; // Should no longer be needed. 
		$this->plug_vars = $ret;

		return true;
	}

	// Called to parse the plugin.xml file if it exists
	function parse_plugin_xml($plugName, $where = null)
	{

		$tp = e107::getParser();
		//	loadLanFiles($plugName, 'admin');					// Look for LAN files on default paths
		$xml = e107::getXml();
		$mes = e107::getMessage();
		
		//	$xml->setOptArrayTags('extendedField,userclass,menuLink,commentID'); // always arrays for these tags.
		//	$xml->setOptStringTags('install,uninstall,upgrade');
		if(null === $where) $where = 'plugin.xml';

		$this->plug_vars = $xml->loadXMLfile(e_PLUGIN.$plugName.'/'.$where, 'advanced');
		
		if ($this->plug_vars === FALSE)
		{
			$mes->add("Error reading {$plugName}/plugin.xml", E_MESSAGE_ERROR);
			return FALSE;
		}

		$this->plug_vars['category'] = (isset($this->plug_vars['category'])) ? $this->manage_category($this->plug_vars['category']) : "misc";
		$this->plug_vars['folder'] = $plugName; // remove the need for <folder> tag in plugin.xml.

		if(varset($this->plug_vars['description']) && !is_array($this->plug_vars['description']))
		{
			$diz = $this->plug_vars['description'];
			unset($this->plug_vars['description']);
			
			$this->plug_vars['description']['@value'] = $diz;		
		}
		
		 // Very useful debug code.to compare plugin.php vs plugin.xml
		/*
		 $testplug = 'forum';
		 if($plugName == $testplug)
		 {
		 $plug_vars1 = $this->plug_vars;
		 $this->parse_plugin_php($testplug);
		 $plug_vars2 = $this->plug_vars;
		 ksort($plug_vars2);
		 ksort($plug_vars1);
		 echo "<table>
		 <tr><td><h1>PHP</h1></td><td><h1>XML</h1></td></tr>
		 <tr><td style='border-right:1px solid black'>";
		 print_a($plug_vars2);
		 echo "</td><td>";
		 print_a($plug_vars1);
		 echo "</table>";
		 }
		*/
		

		// TODO search for $this->plug_vars['adminLinks']['link'][0]['@attributes']['primary']==true.
		$this->plug_vars['administration']['icon'] = varset($this->plug_vars['adminLinks']['link'][0]['@attributes']['icon']);
		$this->plug_vars['administration']['caption'] = varset($this->plug_vars['adminLinks']['link'][0]['@attributes']['description']);
		$this->plug_vars['administration']['iconSmall'] = varset($this->plug_vars['adminLinks']['link'][0]['@attributes']['iconSmall']);
		$this->plug_vars['administration']['configFile'] = varset($this->plug_vars['adminLinks']['link'][0]['@attributes']['url']);

		return TRUE;
	}

}