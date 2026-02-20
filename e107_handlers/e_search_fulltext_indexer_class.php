<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

if(!defined('e107_INIT'))
{
	exit;
}


/**
 * Derives FULLTEXT index requirements from e_search addon configurations
 * for use by db_verify to detect and fix missing FULLTEXT indexes.
 */
class e_search_fulltext_indexer
{

	/** @var array Cached search configs keyed by plugin */
	private $searchConfigs = array();

	/** @var array Derived indexes keyed by table name */
	private $derivedIndexes = array();

	/**
	 * Load all e_search addon configurations
	 *
	 * This method loads e_search addons safely, handling cases where
	 * language constants may not be loaded (e.g., in admin db_verify context).
	 *
	 * @return array
	 */
	public function loadSearchConfigs()
	{
		if(!empty($this->searchConfigs))
		{
			return $this->searchConfigs;
		}

		// Load search language file to ensure constants like LAN_SEARCH_* are available
		// This is needed because e_search addons reference these constants in config()
		$this->loadSearchLanguage();

		// Load e_search addons safely with error handling for each addon
		$this->searchConfigs = $this->loadAddonsWithErrorHandling();

		return $this->searchConfigs;
	}

	/**
	 * Load the search language file
	 */
	private function loadSearchLanguage()
	{
		// Try to load the main search language file
		$langFile = e_LANGUAGEDIR . e_LANGUAGE . '/lan_search.php';
		if(is_readable($langFile))
		{
			e107::includeLan($langFile);
		}
		else
		{
			// Fallback to English
			$langFile = e_LANGUAGEDIR . 'English/lan_search.php';
			if(is_readable($langFile))
			{
				e107::includeLan($langFile);
			}
		}
	}

	/**
	 * Load a plugin's language file
	 *
	 * @param string $pluginName
	 */
	private function loadPluginLanguage($pluginName)
	{
		// Try to load the plugin's global language file
		$langFile = e_PLUGIN . $pluginName . '/languages/' . e_LANGUAGE . '/' . e_LANGUAGE . '_global.php';
		if(is_readable($langFile))
		{
			e107::includeLan($langFile);
			return;
		}

		// Fallback to English
		$langFile = e_PLUGIN . $pluginName . '/languages/English/English_global.php';
		if(is_readable($langFile))
		{
			e107::includeLan($langFile);
		}
	}

	/**
	 * Load e_search addons with individual error handling
	 *
	 * This reimplements the core of e107::getAddonConfig('e_search') but with
	 * try-catch around each addon to gracefully skip addons that fail to load.
	 *
	 * @return array
	 */
	private function loadAddonsWithErrorHandling()
	{
		$configs = array();
		$elist = e107::getPref('e_search_list');

		if(empty($elist))
		{
			return $configs;
		}

		foreach(array_keys($elist) as $pluginName)
		{
			$addonFile = e_PLUGIN . $pluginName . '/e_search.php';

			if(!is_readable($addonFile))
			{
				continue;
			}

			try
			{
				// Load the plugin's language file before instantiating
				// This ensures constants like LAN_PLUGIN_*_NAME are defined
				$this->loadPluginLanguage($pluginName);

				// Include the addon file
				include_once($addonFile);

				$className = $pluginName . '_search';

				if(!class_exists($className))
				{
					continue;
				}

				$obj = new $className();

				if(!method_exists($obj, 'config'))
				{
					continue;
				}

				$config = $obj->config();

				if(!empty($config))
				{
					$configs[$pluginName] = $config;
				}
			}
			catch(\Error $e)
			{
				// PHP 7+ Error (including undefined constants in PHP 8+)
				// Log and skip this addon
				e107::getLog()->add('SEARCH_FULLTEXT_INDEXER', 'Failed to load e_search addon: ' . $pluginName . ' - ' . $e->getMessage(), E_LOG_INFORMATIVE);
			}
			catch(\Exception $e)
			{
				// Legacy Exception handling
				e107::getLog()->add('SEARCH_FULLTEXT_INDEXER', 'Failed to load e_search addon: ' . $pluginName . ' - ' . $e->getMessage(), E_LOG_INFORMATIVE);
			}
		}

		return $configs;
	}

	/**
	 * Parse table aliases from a FROM/JOIN clause
	 *
	 * @param string $tableClause e.g., 'news AS n LEFT JOIN #news_category AS c ON ...'
	 * @return array ['alias' => 'table_name', ...]
	 */
	public function parseTableAliases($tableClause)
	{
		$aliases = array();

		// Normalize whitespace (handle multi-line table definitions)
		$tableClause = preg_replace('/\s+/', ' ', trim($tableClause));

		// Match patterns like:
		// - table_name AS alias
		// - #table_name AS alias
		// - table_name alias (without AS keyword)
		// - table_name (no alias - use table as its own alias)
		$pattern = '/(?:^|(?:LEFT|RIGHT|INNER|OUTER|CROSS)?\s*JOIN\s+)#?(\w+)(?:\s+(?:AS\s+)?(\w+))?/i';

		preg_match_all($pattern, $tableClause, $matches, PREG_SET_ORDER);

		foreach($matches as $match)
		{
			$table = $match[1];
			$alias = isset($match[2]) && !empty($match[2]) ? $match[2] : $table;

			// Skip if alias looks like a SQL keyword (ON, WHERE, etc.)
			if(in_array(strtoupper($alias), array('ON', 'WHERE', 'AND', 'OR', 'LEFT', 'RIGHT', 'INNER', 'OUTER', 'JOIN')))
			{
				$alias = $table;
			}

			$aliases[$alias] = $table;
		}

		return $aliases;
	}

	/**
	 * Map search fields to actual table.column pairs
	 *
	 * @param array $searchFields e.g., ['n.news_title' => '1.2', 'n.news_body' => '0.6']
	 * @param array $tableAliases e.g., ['n' => 'news', 'c' => 'news_category']
	 * @return array ['table_name' => ['column1', 'column2'], ...]
	 */
	public function mapSearchFields($searchFields, $tableAliases)
	{
		$mapped = array();

		foreach(array_keys($searchFields) as $field)
		{
			// Split alias.column
			if(strpos($field, '.') !== false)
			{
				list($alias, $column) = explode('.', $field, 2);

				if(isset($tableAliases[$alias]))
				{
					$table = $tableAliases[$alias];
					if(!isset($mapped[$table]))
					{
						$mapped[$table] = array();
					}
					if(!in_array($column, $mapped[$table]))
					{
						$mapped[$table][] = $column;
					}
				}
			}
			else
			{
				// No alias - assume first/primary table
				$primaryTable = reset($tableAliases);
				if($primaryTable)
				{
					if(!isset($mapped[$primaryTable]))
					{
						$mapped[$primaryTable] = array();
					}
					if(!in_array($field, $mapped[$primaryTable]))
					{
						$mapped[$primaryTable][] = $field;
					}
				}
			}
		}

		return $mapped;
	}

	/**
	 * Generate FULLTEXT index definition for a column
	 *
	 * The format matches db_verify::getIndex() output where:
	 * - 'keyname' = column name(s) (content in parentheses)
     * 
	 * - 'field' = index name (used as array key and in backticks)
	 *
	 * toMysql() produces: FULLTEXT `field` (keyname)
	 *
	 * @param string $tableName
	 * @param string $columnName
	 * @return array Index definition compatible with db_verify::getIndex() output
	 */
	public function generateIndexDefinition($tableName, $columnName)
	{
		$indexName = 'ft_' . $tableName . '_' . $columnName;

		return array(
			'type'    => 'FULLTEXT',
			'keyname' => $columnName,  // column name (goes in parentheses)
			'field'   => $indexName,   // index name (goes in backticks, used as array key)
		);
	}

	/**
	 * Get all derived FULLTEXT indexes for a specific table
	 *
	 * @param string $tableName Table name without prefix
	 * @return array Array of index definitions keyed by index name
	 */
	public function getIndexesForTable($tableName)
	{
		// Build indexes if not cached
		if(empty($this->derivedIndexes))
		{
			$this->buildAllDerivedIndexes();
		}

		return isset($this->derivedIndexes[$tableName])
			? $this->derivedIndexes[$tableName]
			: array();
	}

	/**
	 * Get all derived FULLTEXT indexes for all tables
	 *
	 * @return array Array keyed by table name, each containing index definitions
	 */
	public function getAllDerivedIndexes()
	{
		if(empty($this->derivedIndexes))
		{
			$this->buildAllDerivedIndexes();
		}

		return $this->derivedIndexes;
	}

	/**
	 * Build all derived indexes from all e_search configs
	 */
	private function buildAllDerivedIndexes()
	{
		$this->derivedIndexes = array();
		$configs = $this->loadSearchConfigs();

		foreach($configs as $config)
		{
			if(empty($config['table']) || empty($config['search_fields']))
			{
				continue;
			}

			$tableAliases = $this->parseTableAliases($config['table']);
			$fieldMapping = $this->mapSearchFields($config['search_fields'], $tableAliases);

			foreach($fieldMapping as $table => $columns)
			{
				if(!isset($this->derivedIndexes[$table]))
				{
					$this->derivedIndexes[$table] = array();
				}

				foreach($columns as $column)
				{
					$indexDef = $this->generateIndexDefinition($table, $column);
					$indexKey = $indexDef['field'];
					$this->derivedIndexes[$table][$indexKey] = $indexDef;
				}
			}
		}
	}

	/**
	 * Clear cached data (useful for testing or after plugin changes)
	 */
	public function clearCache()
	{
		$this->searchConfigs = array();
		$this->derivedIndexes = array();
	}
}