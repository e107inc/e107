<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Preference Handler
 *
 * $URL$
 * $Id$
*/

if (!defined('e107_INIT')) { exit; }
require_once(e_HANDLER.'model_class.php');

/**
 * Base preference object - shouldn't be used direct,
 * used internal by {@link e_plugin_pref} and {@link e_core_pref classes}
 *
 * @package e107
 * @category e107_handlers
 * @version $Id$
 * @author SecretR
 * @copyright Copyright (c) 2009, e107 Inc.
 */
class e_pref extends e_front_model
{
	/**
	 * Preference ID - DB row value
	 *
	 * @var string
	 */
	protected $prefid;

	/**
	 * Preference ID alias e.g. 'core' is an alias of prefid 'SitePrefs'
	 * Used in e.g. server cache file name
	 *
	 * @var string
	 */
	protected $alias;

	/**
	 * Runtime cache, set on first data load
	 *
	 * @var string
	 */
	protected $pref_cache = '';

	/**
	 * Backward compatibility - serialized preferences
	 * Note: serialized preference storage is deprecated
	 *
	 * @var boolean
	 */
	protected $serial_bc = false;

	/**
	 * If true, $prefid.'_Backup' row will be created/updated
	 * on every {@link save()} call
	 *
	 * @var boolean
	 */
	protected $set_backup = false;

	/**
	 * Constructor
	 *
	 * @param string $prefid
	 * @param string $alias Used by cache file.
	 * @param array $data
	 * @param boolean $sanitize_data
	 */
	function __construct($prefid, $alias = '', $data = array(), $sanitize_data = true)
	{
		require_once(e_HANDLER.'cache_handler.php');

		$this->prefid = preg_replace('/[^\w\-]/', '', $prefid);
		
		if(empty($alias))
		{
			$alias = $prefid;
		}

		$this->alias = preg_replace('/[^\w\-]/', '', $alias);

		$this->loadData($data, $sanitize_data);
	}

	/**
	 * Advanced getter - $pref_name could be path in format 'pref1/pref2/pref3' (multidimensional arrays support),
	 * alias of {@link e_model::getData()}
	 * If $pref_name is empty, all data array will be returned
	 *
	 * @param string $pref_name
	 * @param mixed $default
	 * @param integer $index
	 * @return mixed
	 */
	public function getPref($pref_name = '', $default = null, $index = null)
	{
		return $this->getData($pref_name, $default, $index);
	}

	/**
	 * Simple getter - $pref_name is not parsed (no multidimensional arrays support), alias of {@link e_model::get()}
	 * This is the prefered (performance wise) method when simple preference is retrieved
	 *
	 * @param string $pref_name
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($pref_name, $default = null)
	{
		return parent::get((string) $pref_name, $default);
	}

	/**
	 * Advanced setter - $pref_name could be path in format 'pref1/pref2/pref3' (multidimensional arrays support)
	 * If $pref_name is array, it'll be merged with existing preference data, non existing preferences will be added as well
	 *
	 * @param string|array $pref_name
	 * @param mixed $value
	 * @return e_pref
	 */
	public function setPref($pref_name, $value = null)
	{
		global $pref;
		//object reset not allowed, adding new pref is allowed
		if(empty($pref_name))
		{
			return $this;
		}

		//Merge only allowed
		if(is_array($pref_name))
		{
			$this->mergeData($pref_name, false, false, false);
			return $this;
		}

		parent::setData($pref_name, $value, false);

		//BC
		if($this->alias === 'core')
		{
			$pref = $this->getData();
		}
		return $this;
	}

    /**
     * Reset preference object to given/empty state
     * @param array $prefs
     * @return $this
     */
    public function reset($prefs = array())
    {
        parent::setData(array());

        return $this;
    }

	/**
	 * Advanced setter - $pref_name could be path in format 'pref1/pref2/pref3' (multidimensional arrays support)
	 * Object data reseting is not allowed, adding new preferences is controlled by $strict parameter
	 *
	 * @param string|array $pref_name
	 * @param mixed $value
	 * @param boolean $strict true - update only, false - same as setPref()
	 * @return e_pref
	 */
	public function updatePref($pref_name, $value = null, $strict = false)
	{
		global $pref;
		//object reset not allowed, adding new pref is not allowed
		if(empty($pref_name))
		{
			return $this;
		}

		//Merge only allowed
		if(is_array($pref_name))
		{
			$this->mergeData($pref_name, $strict, false, false);
			return $this;
		}

		parent::setData($pref_name, $value, $strict);

		//BC
		if($this->alias === 'core')
		{
			$pref = $this->getData();
		}
		return $this;
	}

	/**
	 * Simple setter - $pref_name is not parsed (no multidimensional arrays support)
	 * Adding new pref is allowed
	 *
	 * @param string $pref_name
	 * @param mixed $value
	 * @return e_pref
	 */
	public function set($pref_name, $value=null, $strict = false)
	{
		global $pref;
		if(empty($pref_name) || !is_string($pref_name))
		{
			return $this;
		}
		
		if(!isset($this->_data[$pref_name]) || $this->_data[$pref_name] != $value) $this->data_has_changed = true;
		$this->_data[$pref_name] = $value;

		//BC
		if($this->alias === 'core')
		{
			$pref = $this->getData();
		}
		return $this;
	}

	/**
	 * Simple setter - $pref_name is  not parsed (no multidimensional arrays support)
	 * Non existing setting will be not created
	 *
	 * @param string $pref_name
	 * @param mixed $value
	 * @return e_pref
	 */
	public function update($pref_name, $value)
	{
		global $pref;
		if(empty($pref_name) || !is_string($pref_name))
		{
			return $this;
		}
		if(array_key_exists($pref_name, $this->_data))
		{
			if($this->_data[$pref_name] != $value) $this->data_has_changed = true;
			$this->_data[$pref_name] = $value;
		}

		//BC
		if($this->alias === 'core')
		{
			$pref = $this->getData();
		}
		return $this;
	}

	/**
	 * Add new (single) preference (ONLY if doesn't exist)
	 * No multidimensional arrays support
	 *
	 * @see addData()
	 * @param string $pref_name
	 * @param mixed $value
	 * @return e_pref
	 */
	public function add($pref_name, $value)
	{
		if(empty($pref_name) || !is_string($pref_name))
		{
			return $this;
		}
		if(!isset($this->_data[$pref_name])) 
		{
			$this->_data[$pref_name] = $value;
			$this->data_has_changed = true;
		}
		
		//BC
		if($this->alias === 'core')
		{
			$pref = $this->getData();
		}
		return $this;
	}

	/**
	 * Add new preference or preference array (ONLY if it/they doesn't exist)
	 * $pref_name could be path in format 'pref1/pref2/pref3'
	 *
	 * @see addData()
	 * @param string|array $pref_name
	 * @param mixed $value
	 * @return e_pref
	 */
	public function addPref($pref_name, $value = null)
	{
		$this->addData($pref_name, $value);
		return $this;
	}

	/**
	 * Remove single preference
	 * $pref_name is not parsed as a path
	 *
	 * @see e_model::remove()
	 * @param string $pref_name
	 * @return e_pref
	 */
	public function remove($pref_name)
	{
		global $pref;
		parent::remove((string) $pref_name);

		//BC
		if($this->alias === 'core')
		{
			$pref = $this->getData();
		}
		return $this;
	}

	/**
	 * Remove single preference (parse $pref_name)
	 * $pref_name could be path in format 'pref1/pref2/pref3'
	 *
	 * @see removeData()
	 * @param string $pref_name
	 * @return e_pref
	 */
	public function removePref($pref_name)
	{
		$this->removeData($pref_name);
		return $this;
	}

	/**
	 * Disallow public use of e_model::addData()
	 * Disallow preference override
	 *
	 * @param string|array $pref_name
	 * @param mixed value
	 * @param boolean $strict
	 * @return $this|\e_model
	 */
	final public function addData($pref_name, $value = null, $override = true)
	{
		global $pref;
		parent::addData($pref_name, $value, false);
		//BC
		if($this->alias === 'core')
		{
			$pref = $this->getData();
		}
		return $this;
	}

	/**
	 * Disallow public use of e_model::setData()
	 * Only data merge possible
	 *
	 * @param string|array $pref_name
	 * @param mixed $value
	 * @return e_pref
	 */
	final public function setData($pref_name, $value = null, $strict = false)
	{
		global $pref;
		if(empty($pref_name))
		{
			return $this;
		}

		//Merge only allowed
		if(is_array($pref_name))
		{
			$this->mergeData($pref_name, false, false, false);
			return $this;
		}

		parent::setData($pref_name, $value, false);

		//BC
		if($this->alias === 'core')
		{
			$pref = $this->getData();
		}
		return $this;
	}

	/**
	 * Disallow public use of e_model::removeData()
	 * Object data reseting is not allowed
	 *
	 * @param string $pref_name
	 * @return e_pref
	 */
	final public function removeData($pref_name=null)
	{
		global $pref;
		parent::removeData((string) $pref_name);

		//BC
		if($this->alias === 'core')
		{
			$pref = $this->getData();
		}
		return $this;
	}

	/**
	 * Reset object data
	 *
	 * @param array $data
	 * @param boolean $sanitize
	 * @return e_pref
	 */
	public function loadData(array $data, $sanitize = true)
	{
		global $pref;
		if(!empty($data))
		{
			if($sanitize)
			{
				$data = e107::getParser()->toDB($data);
			}
			parent::setData($data, null, false);
			$this->pref_cache = e107::getArrayStorage()->WriteArray($data, false); //runtime cache
			//BC
			if($this->alias === 'core')
			{
				$pref = $this->getData();
			}
		}
		return $this;
	}

	/**
	 * Load object data - public
	 *
	 * @see _load()
	 * @param boolean $force
	 * @return e_pref
	 */
	public function load($id=null, $force = false)
	{
		global $pref;
		if($force || !$this->hasData())
		{
			$this->data_has_changed = false;
			$this->_load($force);
			//BC
			if($this->alias === 'core')
			{
				$pref = $this->getData();
			}
		}

		return $this;
	}

	/**
	 * Load object data
	 *
	 * @param boolean $force
	 * @return e_pref
	 */
	protected function _load($force = false)
	{
		$id = $this->prefid;
		$data = $force ? false : $this->getPrefCache(true);

		if(!empty($data))
		{
			$this->pref_cache = e107::getArrayStorage()->WriteArray($data, false); //runtime cache
			$this->loadData((array) $data, false);
			return $this;
		}

		if (e107::getDb()->select('core', 'e107_value', "e107_name='{$id}'"))
		{
			$row = e107::getDb()->fetch();

			if($this->serial_bc)
			{
				$data = unserialize($row['e107_value']);
				$row['e107_value'] = e107::getArrayStorage()->WriteArray($data, false);
			}
			else
			{
				$data = e107::unserialize($row['e107_value']);
			}

			$this->pref_cache = $row['e107_value']; //runtime cache
			$this->setPrefCache($row['e107_value'], true);
		}

		if(empty($data))
			$data = array();

		$this->loadData($data, false);
		return $this;
	}

	/**
	 * Save object data to DB
	 *
	 * @param boolean $from_post merge post data
	 * @param boolean $force
	 * @param mixed $session_messages      null: normal messages displayed, true: session messages used, false: no messages displayed. 
	 * @return boolean|integer 0 - no change, true - saved, false - error
	 */
	public function save($from_post = true, $force = false, $session_messages = null)
	{
		global $pref;
		if(!$this->prefid)
		{
			return false;
		}
		
		e107::getMessage()->setUnique($this->prefid); // attempt to fix 
		
		if($from_post)
		{
			$this->mergePostedData(); //all posted data is sanitized and filtered vs preferences/_data_fields array
		}

		if($this->hasValidationError())
		{
			return false;
		}

		if(!$this->data_has_changed && !$force)
		{
			if($session_messages !== false)
			{
				e107::getMessage()->addInfo(LAN_SETTINGS_NOT_SAVED_NO_CHANGES_MADE, $this->prefid, $session_messages)->moveStack($this->prefid);
			}

			return 0;
		}

		$log = e107::getAdminLog();
		$disallow_logs = $this->getParam('nologs', false);

		//Save to DB
		if(!$this->hasError())
		{
			if($this->serial_bc)
			{
				$dbdata = serialize($this->getPref());
			}
			else
			{
				$dbdata = $this->toString(false);
			}

			if(e107::getDb()->gen("REPLACE INTO `#core` (e107_name,e107_value) values ('{$this->prefid}', '".addslashes($dbdata)."') "))
			{
				$this->data_has_changed = false; //reset status

				if(!empty($this->pref_cache))
				{
					$old = e107::unserialize($this->pref_cache);
					if($this->serial_bc)
					{
						$dbdata = serialize($old);
					}
					else
					{
						$dbdata = $this->pref_cache;
					}

					// auto admin log
					if(is_array($old) && !$disallow_logs) // fix install problems - no old prefs available
					{
						$new = $this->getPref();
					//	$log->logArrayDiffs($new, $old, 'PREFS_02', false);
						$log->addArray($new,$old);
						unset($new, $old);
						
					}
					
					// Backup 
					if($this->set_backup === true && e107::getDb()->gen("REPLACE INTO `#core` (e107_name,e107_value) values ('".$this->prefid."_Backup', '".addslashes($dbdata)."') "))
					{
						if(!$disallow_logs) $log->logMessage('Backup of <strong>'.$this->alias.' ('.$this->prefid.')</strong> successfully created.', E_MESSAGE_DEBUG, E_MESSAGE_SUCCESS, $session_messages);
						e107::getCache()->clear_sys('Config_'.$this->alias.'_backup');
					}
					
				}
				
				$this->setPrefCache($this->toString(false), true); //reset pref cache - runtime & file
				
				if($this->alias == 'search') // Quick Fix TODO Improve. 
				{
					$logId = 'SEARCH_04';	
				}
				elseif($this->alias == 'notify')
				{
					$logId = 'NOTIFY_01';	
				}
				else
				{
					$logId = 'PREFS_01';	
				}
				
				$log->addSuccess(LAN_SETSAVED, ($session_messages === null || $session_messages === true));

				$uid = USERID;

				if(empty($uid)) // Log extra details of any pref changes made by a non-user.
				{
					$log->addWarning(print_r(debug_backtrace(null,2), true), false);
				}

				$log->save($logId);

			//	if(!$disallow_logs) $log->logSuccess('Settings successfully saved.', true, $session_messages)->flushMessages($logId, E_LOG_INFORMATIVE, '', $this->prefid);
				
				
				//BC
				if($this->alias === 'core')
				{
					$pref = $this->getPref();
				}
				e107::getMessage()->moveStack($this->prefid);
				return true;
			}
			elseif(e107::getDb()->getLastErrorNumber())
			{
				if(!$disallow_logs)
					$log->logError('mySQL error #'.e107::getDb()->getLastErrorNumber().': '.e107::getDb()->getLastErrorText(), true, $session_messages)
					->logError('Settings not saved.', true, $session_messages)
					->flushMessages('PREFS_03', E_LOG_INFORMATIVE, '', $this->prefid);
					
				e107::getMessage()->moveStack($this->prefid);
				return false;
			}
		}

		if($this->hasError())
		{
			//add errors to the eMessage stack
			//$this->setErrors(true, $session_messages); old - doesn't needed anymore
			if(!$disallow_logs)
				$log->logError('Settings not saved.', true, $session_messages)
				->flushMessages('LAN_FIXME', E_LOG_INFORMATIVE, '', $this->prefid);
				
			e107::getMessage()->moveStack($this->prefid);
			return false;
		}
		else
		{
			e107::getMessage()->addInfo(LAN_SETTINGS_NOT_SAVED_NO_CHANGES_MADE, $this->prefid, $session_messages);
			if(!$disallow_logs) $log->flushMessages('LAN_FIXME', E_LOG_INFORMATIVE, '', $this->prefid);
			e107::getMessage()->moveStack($this->prefid);
			return 0;
		}
	}

	/**
	 * Get cached data from server cache file
	 *
	 * @param boolean $toArray convert to array
	 * @return string|array|false
	 */
	protected function getPrefCache($toArray = true)
	{
		if(!$this->pref_cache)
		{
			$this->pref_cache = e107::getCache()->retrieve_sys('Config_'.$this->alias, 24 * 60, true);
		}

		return ($toArray && $this->pref_cache ? e107::unserialize($this->pref_cache) : $this->pref_cache);
	}

	/**
	 * Convert data to a string and store it to a server cache file
	 * If $cache_string is an array, it'll be converted to a string
	 * If $save is string, it'll be used for building the cache filename
	 *
	 * @param string|array $cache_string
	 * @param string|boolean $save write to a file
	 * @return e_pref
	 */
	protected function setPrefCache($cache_string, $save = false)
	{
		if(is_array($cache_string))
		{
			$cache_string = e107::getArrayStorage()->WriteArray($cache_string, false);
		}
		if(is_bool($save))
		{
			$this->pref_cache = $cache_string;
		}
		if($save)
		{
			e107::getCache()->set_sys('Config_'.($save !== true ? $save : $this->alias), $cache_string, true);
		}
		return $this;
	}

	/**
	 * Clear pref cache
	 *
	 * @param string $cache_name default to current alias
	 * @param boolean $runtime clear runtime cache as well ($this->pref_cache)
	 * @return e_pref
	 */
	public function clearPrefCache($cache_name = '', $runtime = true)
	{
		if($runtime)
		{
			$this->pref_cache = '';
		}
		e107::getCache()->clear_sys('Config_'.(!empty($cache_name) ? $cache_name : $this->alias));
		return $this;
	}

	/**
	 * Validation
	 *
	 * @param array $data [optional] null to use Posted data
	 * @return boolean
	 */
	public function validate($data = null)
	{
		return parent::validate($data);
	}

	/**
	 * Set $set_backup option
	 *
	 * @param boolean $optval
	 * @return e_pref
	 *
	 */
	public function setOptionBackup($optval)
	{
		$this->set_backup = $optval;
		return $this;
	}

	/**
	 * Set $serial_bc option
	 *
	 * @param boolean $optval
	 * @return e_pref
	 *
	 */
	public function setOptionSerialize($optval)
	{
		$this->serial_bc = $optval;
		return $this;
	}

    /**
     * Override
     */
    public function delete($ids, $destroy = true, $session_messages = false)
    {
    }

    /**
     * Override
     */
    protected function dbUpdate($force = false, $session_messages = false)
    {
    }
}

/**
 * Handle core preferences
 *
 * @package e107
 * @category e107_handlers
 * @version 1.0
 * @author SecretR
 * @copyright Copyright (c) 2009, e107 Inc.
 */
final class e_core_pref extends e_pref
{
	/**
	 * Allowed core id array
	 *
	 * @var array
	 */
	public $aliases = array(
		'core' 			=> 'SitePrefs',
		'core_backup' 	=> 'SitePrefs_Backup',
		'core_old' 		=> 'pref',
		'emote' 		=> 'emote_default', //TODO include other emote packs of the user.
		'menu' 			=> 'menu_pref',
		'search' 		=> 'search_prefs',
		'notify' 		=> 'notify_prefs',
		'history'		=> 'history_prefs'
	);

	/**
	 * Backward compatibility - list of prefid's which operate wit serialized data
	 *
	 * @var array
	 */
	// protected $serial_bc_array = array('core_old', 'emote', 'menu', 'search');
	protected $serial_bc_array = array('core_old');

	/**
	 * Constructor
	 *
	 * @param string $alias
	 * @param boolean $load load DB data on startup
	 */
	function __construct($alias, $load = true)
	{


		$pref_alias = $alias;

		if($alias == 'emote')
		{
			$pack = e107::pref('core','emotepack');
			$this->aliases['emote'] = 'emote_'.$pack;
		}

		$pref_id = $this->getConfigId($alias);


		if(!$pref_id)
		{
			$pref_id = $pref_alias = '';
			trigger_error('Core config ID '.$alias.' not found!', E_USER_WARNING);
			return;
		}

		if(in_array($pref_alias, $this->serial_bc_array))
		{
			$this->setOptionSerialize(true);
		}

		if('core' === $pref_alias)
		{
			$this->setOptionBackup(true);
		}

		parent::__construct($pref_id, $pref_alias);
		if($load && $pref_id)
		{
			$this->load();
		}


	}

	/**
	 * Get config ID
	 * Allowed values: key or value from $alias array
	 * If id not found this method returns false
	 *
	 * @param string $alias
	 * @return string
	 */
	public function getConfigId($alias)
	{
		$alias = trim($alias);
		if(isset($this->aliases[$alias]))
		{
			return $this->aliases[$alias];
		}
		return false;
	}

	/**
	 * Get config ID
	 * Allowed values: key or value from $alias array
	 * If id not found this method returns false
	 *
	 * @param string $prefid
	 * @return string
	 */
	public function getAlias($prefid)
	{
		$prefid = trim($prefid);
		return array_search($prefid, $this->aliases);
	}


	/**
	 * Export data from core pref and remove if needed. Useful for core pref -> menu table parm migration.
	 * @param array $prefList  key/value pairs.  key = oldpref value = new pref key
	 * @param bool|false $remove
	 * @return array|false if no match found.
	 */
	public function migrateData($prefList=array(), $remove=false)
	{
		$data = self::getData();
		$array = array();
		$save = false;

		if(empty($prefList))
		{
			return false;
		}

		foreach($data as $k=>$v)
		{
			if(isset($prefList[$k]))
			{
				$key = $prefList[$k];
				$array[$key] = $v;

				if($remove == true)
				{
					self::remove($k);
					$save = true;
				}
			}

		}

		if(empty($array))
		{
			return false;
		}

		if(!empty($save))
		{
			self::save(false,true,false);
		}

		return $array;

	}
}

/**
 * Handle plugin preferences
 *
 * @package e107
 * @category e107_handlers
 * @version 1.0
 * @author SecretR
 * @copyright Copyright (c) 2009, e107 Inc.
 */
class e_plugin_pref extends e_pref
{
	/**
	 * Unique plugin name
	 *
	 * @var string
	 */
	protected $plugin_id;

	/**
	 * Constructor
	 * Note: object data will be loaded only if the plugin is installed (no matter of the passed
	 * $load value)
	 *
	 * @param string $plugin_id unique plugin name
	 * @param string $multi_row additional field identifier appended to the $prefid
	 * @param boolean $load load on startup
	 */
	function __construct($plugin_id, $multi_row = '', $load = true)
	{
		$this->plugin_id = $plugin_id;
		if($multi_row)
		{
			$plugin_id = $plugin_id.'_'.$multi_row;
		}
		parent::__construct('plugin_'.$plugin_id, "plugin_".$this->plugin_id);
		if($load && e107::findPref('plug_installed/'.$this->plugin_id))
		{
			$this->load();
		}
	}

	/**
	 * Retrive unique plugin name
	 *
	 * @return string
	 */
	public function getPluginId()
	{
		return $this->plugin_id;
	}

	/**
	 * Delete plugin preferences
	 * @see e107_handlers/e_pref#delete()
	 * @return boolean
	 */
	public function delete($ids, $destroy = true, $session_messages = false)
	{
		$ret = false;
		if($this->plugin_id)
		{
			$ret = e107::getDb($this->plugin_id)->delete('core', "e107_name='{$this->plugin_id}'");
			$this->destroy();
		}
		return $ret;
	}
}


/**
 * Handle plugin preferences
 *
 * @package e107
 * @category e107_handlers
 * @version 1.0
 * @author SecretR
 * @copyright Copyright (c) 2009, e107 Inc.
 */
class e_theme_pref extends e_pref
{
	/**
	 * Unique plugin name
	 *
	 * @var string
	 */
	protected $theme_id;

	/**
	 * Constructor
	 * Note: object data will be loaded only if the plugin is installed (no matter of the passed
	 * $load value)
	 *
	 * @param string $theme_id unique plugin name
	 * @param string $multi_row additional field identifier appended to the $prefid
	 * @param boolean $load load on startup
	 */
	function __construct($theme_id, $multi_row = '', $load = true)
	{
		$this->theme_id = $theme_id;
		if($multi_row)
		{
			$theme_id = $theme_id.'_'.$multi_row;
		}
		parent::__construct('theme_'.$theme_id, "theme_".$this->theme_id);
	//	if($load && e107::findPref('plug_installed/'.$this->theme_id))
		{
			$this->load();
		}
	}

	/**
	 * Retrive unique plugin name
	 *
	 * @return string
	 */
	public function getPluginId()
	{
		return $this->theme_id;
	}

	/**
	 * Delete plugin preferences
	 * @see e107_handlers/e_pref#delete()
	 * @return boolean
	 */
	public function delete($ids, $destroy = true, $session_messages = false)
	{
		$ret = false;
		if($this->theme_id)
		{
			$ret = e107::getDb($this->theme_id)->delete('core', "e107_name='{$this->theme_id}'");
			$this->destroy();
		}
		return $ret;
	}
}







/**
 * DEPRECATED - see e107::getConfig(), e_core_pref and e_plugin_pref
 *
 */
//
// Simple functionality:
// Grab all prefs once, in one DB query. Reuse them throughout the session.
//
// get/set methods serve/consume strings (with slashes taken care of)
// getArray/setArray methods serve/consume entire arrays (since most prefs are such!)
//
// NOTE: Use of this class is VALUABLE (efficient) yet not NECESSARY (i.e. the system
//       will not break if it is ignored)... AS LONG AS there is no path consisting of:
//             - modify pref value(s) IGNORING this class
//  - retrieve pref value(s) USING this class
//       (while processing a single web page)
//  Just to be safe I have changed a number of menu_pref edits to use setArray().
//

class prefs
{
	var $prefVals;
	var $prefArrays;

	// Default prefs to load
	var $DefaultRows = "e107_name='e107' OR e107_name='menu_pref' OR e107_name='notify_prefs'";

	// Read prefs from DB - get as many rows as are required with a single query.
	// $RowList is an array of pref entries to retrieve.
	// If $use_default is TRUE, $RowList entries are added to the default array. Otherwise only $RowList is used.
	// Returns TRUE on success (measured as getting at least one row of data); false on error.
	// Any data read is buffered (in serialised form) here - retrieve using get()
	function ExtractPrefs($RowList = "", $use_default = FALSE)
	{
		global $sql;
		$Args = '';
		if($use_default)
		{
			$Args = $this->DefaultRows;
		}
		if(is_array($RowList))
		{
			foreach($RowList as $v)
			{
				$Args .= ($Args ? " OR e107_name='{$v}'" : "e107_name='{$v}'");
			}
		}
		if (!$sql->select('core', '*', $Args, 'default'))
		{
			return FALSE;
		}
		while ($row = $sql->fetch())
		{
			$this->prefVals['core'][$row['e107_name']] = $row['e107_value'];
		}
		return TRUE;
	}


	/**
	* Return current pref string $name from $table (only core for now)
	*
	* @param  string $name -- name of pref row
	* @param  string $table -- "core"
	* @return  string pref value, slashes already stripped. FALSE on error
	* @access  public
	*/
	function get($Name)
	{
		if(isset($this->prefVals['core'][$Name]))
		{
			if($this->prefVals['core'][$Name] != '### ROW CACHE FALSE ###')
			{
				return $this->prefVals['core'][$Name];		// Dava from cache
			}
			else
			{
				return false;
			}
		}

		// Data not in cache - retrieve from DB
		$get_sql = new db; // required so sql loops don't break using $tp->toHTML().
		if($get_sql->db_Select('core', '*', "`e107_name` = '{$Name}'", 'default'))
		{
			$row = $get_sql->db_Fetch();
			$this->prefVals['core'][$Name] = $row['e107_value'];
			return $this->prefVals['core'][$Name];
		}
		else
		{	// Data not in DB - put a 'doesn't exist' entry in cache to save another DB access
			$this->prefVals['core'][$Name] = '### ROW CACHE FALSE ###';
			return false;
		}
	}

	/**
	* Return current array from pref string $name in $table (core only for now)
	*
	* @param  string $name -- name of pref row
	* @param  string $table -- "core" only now
	* @return  array pref values
	* @access     public
	*/
	// retrieve prefs as an array of values
	function getArray($name)
	{
		return e107::unserialize($this->get($name));
		// return unserialize($this->get($name));
	}


	/**
	* Update pref set and cache
	*
	* @param  string val -- pre-serialized string
	* @param  string $name -- name of pref row
	* @param  string $table -- "core" or "user"
	* @global  mixed $$name
	* @access  public
	*
	* set("val")    == 'core', 'pref'
	* set("val","rowname")   == 'core', rowname
	* set("val","","user")   == 'user', 'user_pref' for current user
	* set("val","","user",uid)   == 'user', 'user_pref' for user uid
	* set("val","fieldname","user")  == 'user', fieldname
	*
	*/
	function set($val, $name = "", $table = "core", $uid = USERID) {
		global $sql;
		if (!strlen($name)) {
			switch ($table) {
				case 'core':
				$name = "pref";
				break;
				case 'user':
				$name = "user_pref";
				break;
			}
		}
		$val = addslashes($val);

		switch ($table ) {
			case 'core':
			if(!$sql->db_Update($table, "e107_value='$val' WHERE e107_name='$name'"))
			{
				$sql->db_Insert($table, "'{$name}', '{$val}'");
			}
			$this->prefVals[$table][$name] = $val;
			unset($this->prefArrays[$table][$name]);
			break;
			case 'user':
			$sql->db_Update($table, "user_prefs='$val' WHERE user_id=$uid");
			break;
		}
	}


	/**
	* Update pref set and cache
	*
	* - @param  string $name -- name of pref row
	* - @param  string $table -- "core" or "user"
	* - @global  $$name
	* - @access  public
	*
	* set()    == core, pref
	* set("rowname")   == core, rowname
	* set("","user")   == user, user_pref for current user
	* set("","user",uid)   == user, user_pref for user uid
	* set("fieldname","user")  == user, fieldname
	*
	* all pref sets other than menu_pref get toDB()
	*/
	function setArray($name = '', $table = 'core', $uid = USERID)
	{
		$tp = e107::getParser();

		if (!strlen($name))
		{
			switch ($table)
			{
				case 'core':
				$name = 'pref';
				break;
				case 'user':
				$name = 'user_pref';
				break;
			}
		}

		global $$name;
		if ($name != 'menu_pref')
		{
			foreach($$name as $key => $prefvalue)
			{
				$$name[$key] = $tp->toDB($prefvalue);
			}
		}
		$tmp = e107::getArrayStorage()->WriteArray($$name, FALSE);		// $this->set() adds slashes now
	//	$tmp = serialize($$name);
		$this->set($tmp, $name, $table, $uid);
	}
}
?>
