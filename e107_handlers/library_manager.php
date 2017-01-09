<?php

/**
 * @file
 * External library handling for e107 plugins/themes.
 *
 * TODO:
 * - Provide the ability to use third-party callbacks (are defined in e_library.php files) for groups:
 *   'info', 'pre_detect', 'post_detect', 'pre_dependencies_load', 'pre_load', 'post_load'
 */

// [e_LANGUAGEDIR]/[e_LANGUAGE]/lan_library_manager.php
e107::lan('core', 'library_manager');


/**
 * Class e_library_manager.
 */
class e_library_manager
{

	/**
	 * Constructor
	 * Use {@link getInstance()}, direct instantiating is not possible for signleton objects.
	 */
	public function __construct()
	{
	}

	/**
	 * @return void
	 */
	protected function _init()
	{
	}

	/**
	 * Cloning is not allowed.
	 */
	private function __clone()
	{
	}

	/**
	 * Tries to detect a library and its installed version.
	 *
	 * @param $name
	 *   The machine name of a library to return registered information for.
	 *
	 * @return array|false
	 *   An associative array containing registered information for the library specified by $name, or FALSE if the
	 *   library $name is not registered. In addition to the keys returned by info(), the following keys are
	 *   contained:
	 *   - installed: A boolean indicating whether the library is installed. Note that not only the top-level library,
	 *     but also each variant contains this key.
	 *   - version: If the version could be detected, the full version string.
	 *   - error: If an error occurred during library detection, one of the following error statuses:
	 *     "not found", "not detected", "not supported".
	 *   - error_message: If an error occurred during library detection, a detailed error_message.
	 */
	public function detect($name)
	{
		// Re-use the statically cached value of info() to save memory.
		$library = &$this->info($name);

		// Exit early if the library was not found.
		if($library === false)
		{
			return $library;
		}

		// If 'installed' is set, library detection ran already.
		if(isset($library['installed']))
		{
			return $library;
		}

		$library['installed'] = false;

		// Check whether the library exists.
		if(!isset($library['library_path']))
		{
			$library['library_path'] = $this->getPath($library['machine_name']);
		}

		$libraryPath = e107::getParser()->replaceConstants($library['library_path']);
		if($library['library_path'] === false || (!file_exists($libraryPath) && substr($libraryPath, 0, 4) != 'http'))
		{
			$library['error'] = LAN_NOT_FOUND;

			$replace_with = array($library['name']);
			$library['error_message'] = e107::getParser()->lanVars(LAN_LIBRARY_MANAGER_03, $replace_with, true);

			return $library;
		}

		// TODO:
		// Invoke callbacks in the 'pre_detect' group.
		$this->invoke('pre_detect', $library);

		// Detect library version, if not hardcoded.
		if(!isset($library['version']))
		{
			// If version_callback is a method in $this class.
			if(method_exists($this, $library['version_callback']))
			{
				// We support both a single parameter, which is an associative array, and an indexed array of multiple
				// parameters.
				if(isset($library['version_arguments'][0]))
				{
					// Add the library as the first argument.
					$classMethod = array($this, $library['version_callback']);
					$params = array_merge(array($library), $library['version_arguments']);
					$library['version'] = call_user_func_array($classMethod, $params);
				}
				else
				{
					$method = $library['version_callback'];
					$library['version'] = $this->$method($library, $library['version_arguments']);
				}
			}
			// If version_callback is a method in e_library.php file.
			else
			{
				$library['version'] = '';
				$class = false;

				if(varset($library['plugin'], false))
				{
					$class = e107::getAddon($library['plugin'], 'e_library');
				}
				elseif(varset($library['theme'], false))
				{
					// e107::getAddon() does not support theme folders.
					e107_require_once(e_THEME . $library['theme'] . '/theme_library.php');
					$addonClass = $library['theme'] . '_library';

					if(isset($addonClass) && class_exists($addonClass))
					{
						$class = new $addonClass();
					}
				}

				// We support both a single parameter, which is an associative array, and an
				// indexed array of multiple parameters.
				if(isset($library['version_arguments'][0]))
				{
					if($class)
					{
						$params = array_merge(array($library), $library['version_arguments']);
						$library['version'] = e107::callMethod($class, $library['version_callback'], $params);
					}
				}
				else
				{
					if($class)
					{
						$library['version'] = e107::callMethod($class, $library['version_callback'], $library, $library['version_arguments']);
					}
				}
			}

			if(empty($library['version']))
			{
				$library['error'] = LAN_LIBRARY_MANAGER_10;

				$replace_with = array($library['name']);
				$library['error_message'] = e107::getParser()->lanVars(LAN_LIBRARY_MANAGER_04, $replace_with, true);

				return $library;
			}
		}

		// Determine to which supported version the installed version maps.
		if(!empty($library['versions']))
		{
			ksort($library['versions']);
			$version = 0;
			foreach($library['versions'] as $supported_version => $version_properties)
			{
				if(version_compare($library['version'], $supported_version, '>='))
				{
					$version = $supported_version;
				}
			}
			if(!$version)
			{
				$library['error'] = LAN_LIBRARY_MANAGER_11;

				$replace_with = array($library['version'], $library['name']);
				$library['error_message'] = e107::getParser()->lanVars(LAN_LIBRARY_MANAGER_05, $replace_with, true);

				return $library;
			}

			// Apply version specific definitions and overrides.
			$library = array_merge($library, $library['versions'][$version]);
			unset($library['versions']);
		}

		// Check each variant if it is installed.
		if(!empty($library['variants']))
		{
			foreach($library['variants'] as $variant_name => &$variant)
			{
				// If no variant callback has been set, assume the variant to be installed.
				if(!isset($variant['variant_callback']))
				{
					$variant['installed'] = true;
				}
				else
				{
					$variant['installed'] = false;
					$class = false;

					if(varset($library['plugin'], false))
					{
						$class = e107::getAddon($library['plugin'], 'e_library');
					}
					elseif(varset($library['theme'], false))
					{
						// e107::getAddon() does not support theme folders.
						e107_require_once(e_THEME . $library['theme'] . '/theme_library.php');
						$addonClass = $library['theme'] . '_library';

						if(isset($addonClass) && class_exists($addonClass))
						{
							$class = new $addonClass();
						}
					}

					// We support both a single parameter, which is an associative array, and an indexed array of
					// multiple parameters.
					if(isset($variant['variant_arguments'][0]))
					{
						if($class)
						{
							$params = array_merge(array($library, $variant_name), $variant['variant_arguments']);
							$variant['installed'] = e107::callMethod($class, $library['variant_callback'], $params);
						}
					}
					else
					{
						if($class)
						{
							// Can't use e107::callMethod(), because it only supports 2 params.
							if(method_exists($class, $variant['variant_callback']))
							{
								// Call PLUGIN/THEME_library::VARIANT_CALLBACK().
								$method = $variant['variant_callback'];
								$variant['installed'] = $class->$method($library, $variant_name, $variant['variant_arguments']);
							}
						}
					}

					if(!$variant['installed'])
					{
						$variant['error'] = LAN_NOT_FOUND;

						$replace_with = array($variant_name, $library['name']);
						$variant['error_message'] = e107::getParser()->lanVars(LAN_LIBRARY_MANAGER_06, $replace_with, true);
					}
				}
			}
		}

		// If we end up here, the library should be usable.
		$library['installed'] = true;

		// Invoke callbacks in the 'post_detect' group.
		$this->invoke('post_detect', $library);

		return $library;
	}

	/**
	 * Loads a library.
	 *
	 * @param $name
	 *   The name of the library to load.
	 * @param $variant
	 *   The name of the variant to load. Note that only one variant of a library can be loaded within a single
	 *   request. The variant that has been passed first is used; different variant names in subsequent calls are
	 *   ignored.
	 *
	 * @return mixed
	 *   An associative array of the library information as returned from config(). The top-level properties
	 *   contain the effective definition of the library (variant) that has been loaded. Additionally:
	 *   - installed: Whether the library is installed, as determined by detect().
	 *   - loaded: Either the amount of library files that have been loaded, or FALSE if the library could not be
	 *   loaded. See MYPLUGIN_library::config() for more information.
	 */
	public function load($name, $variant = null)
	{
		// Re-use the statically cached value to save memory.
		static $loaded;

		if(!isset($loaded[$name]))
		{
			$cache = e107::getCache();
			$cacheID = 'library_manager_' . md5($name);
			$cached = $cache->retrieve($cacheID, false, true, true);

			if($cached)
			{
				$library = unserialize($cached);
			}

			if(!varset($library, false))
			{
				$library = $this->detect($name);
				$cacheData = serialize($library);
				$cache->set($cacheID, $cacheData, true, false, true);
			}

			// Exit early if the library was not found.
			if($library === false)
			{
				$loaded[$name] = $library;
				return $loaded[$name];
			}

			// If a variant was specified, override the top-level properties with the variant properties.
			if(isset($variant))
			{
				// Ensure that the $variant key exists, and if it does not, set its 'installed' property to FALSE by
				// default. This will prevent the loading of the library files below.
				$library['variants'] += array($variant => array('installed' => false));
				$library = array_merge($library, $library['variants'][$variant]);
			}
			// Regardless of whether a specific variant was requested or not, there can only be one variant of a
			// library within a single request.
			unset($library['variants']);

			// TODO:
			// Invoke callbacks in the 'pre_dependencies_load' group.
			$this->invoke('pre_dependencies_load', $library);

			// If the library (variant) is installed, load it.
			$library['loaded'] = false;
			if($library['installed'])
			{
				// Load library dependencies.
				if(isset($library['dependencies']))
				{
					foreach($library['dependencies'] as $dependency)
					{
						$this->load($dependency);
					}
				}

				// TODO:
				// Invoke callbacks in the 'pre_load' group.
				$this->invoke('pre_load', $library);

				// Load all the files associated with the library.
				$library['loaded'] = $this->loadFiles($library);

				// TODO:
				// Invoke callbacks in the 'post_load' group.
				$this->invoke('post_load', $library);
			}
			$loaded[$name] = $library;
		}

		return $loaded[$name];
	}

	/**
	 * Gets the path of a library.
	 *
	 * @param $name
	 *   The machine name of a library to return the path for.
	 *
	 * @return string
	 *   The path to the specified library or FALSE if the library wasn't found.
	 */
	private function getPath($name)
	{
		static $libraries;

		if(!isset($libraries))
		{
			$libraries = $this->getLibraries();
		}

		$path = '';
		if(!isset($libraries[$name]))
		{
			return false;
		}
		else
		{
			$path .= $libraries[$name];
		}

		return $path;
	}

	/**
	 * Returns an array of library directories.
	 *
	 * @return array
	 *   A list of library directories.
	 */
	private function getLibraries()
	{
		$dir = e_WEB . 'lib';
		$directories = array();

		// Retrieve list of directories.
		$file = e107::getFile();
		$dirs = $file->get_dirs($dir);

		foreach($dirs as $dirName)
		{
			$directories[$dirName] = "{e_WEB}lib/$dirName";
		}

		return $directories;
	}

	/**
	 * Returns information about registered libraries.
	 *
	 * The returned information is unprocessed; i.e., as registered by plugins.
	 *
	 * @param $library
	 *   (optional) The machine name of a library to return registered information for. If omitted, information
	 *   about all registered libraries is returned.
	 *
	 * @return array|false
	 *   An associative array containing registered information for all libraries, the registered information for the
	 *   library specified by $name, or FALSE if the library $name is not registered.
	 */
	public function &info($library = null)
	{
		// This static cache is re-used by detect() to save memory.
		static $libraries;

		if(!isset($libraries))
		{
			$libraries = array();

			$plugins = array();
			$themes = array();

			// Gather information from PLUGIN_library::config().
			$pluginInfo = e107::getAddonConfig('e_library', 'library'); // 'config' is the default.
			foreach($pluginInfo as $plugin => $info)
			{
				foreach($info as $machine_name => $properties)
				{
					$properties['info_type'] = 'plugin';
					$properties['plugin'] = $plugin;
					$libraries[$machine_name] = $properties;
					$plugins[] = $plugin; // This plugin has a valid e_library implementation.
				}
			}

			// Gather information from THEME_library::config().
			$siteTheme = e107::getPref('sitetheme');
			$adminTheme = e107::getPref('admintheme');

			foreach(array($siteTheme, $adminTheme) as $theme)
			{
				if(is_readable(e_THEME . $theme . '/theme_library.php')) // we don't use e_XXXX for themes.
				{
					e107_require_once(e_THEME . $theme . '/theme_library.php');

					$className = $theme . '_library';
					if(class_exists($className))
					{
						$addonClass = new $className();

						if(method_exists($addonClass, 'config'))
						{
							$info = $addonClass->config();
							if(is_array($info))
							{
								foreach($info as $machine_name => $properties)
								{
									$properties['info_type'] = 'theme';
									$properties['theme'] = $theme;
									$libraries[$machine_name] = $properties;
									$themes[] = $theme; // This theme has a valid e_library implementation.
								}
							}
						}
					}
				}
			}

			// Provide defaults.
			foreach($libraries as $machine_name => &$properties)
			{
				$this->infoDefaults($properties, $machine_name);
			}

			// Allow enabled plugins (with e_library.php file) to alter the registered libraries.
			foreach($plugins as $plugin)
			{
				$class = e107::getAddon($plugin, 'e_library');
				if($class && method_exists($class, 'config_alter'))
				{
					// The library definitions are passed by reference.
					$class->config_alter($libraries);
				}
			}

			// Allow enabled themes (with theme_library.php file) to alter the registered libraries.
			foreach($themes as $theme)
			{
				e107_require_once(e_THEME . $theme . '/theme_library.php');
				$addonClass = $theme . '_library';

				if(class_exists($addonClass))
				{
					$class = new $addonClass();
					if(method_exists($class, 'config_alter'))
					{
						$class->config_alter($libraries);
					}
				}
			}

			// TODO:
			// Invoke callbacks in the 'info' group.
			foreach($libraries as &$properties)
			{
				$this->invoke('info', $properties);
			}
		}

		if(isset($library))
		{
			if(!empty($libraries[$library]))
			{
				return $libraries[$library];
			}
			else
			{
				$false = false;
				return $false;
			}
		}

		return $libraries;
	}

	/**
	 * Applies default properties to a library definition.
	 *
	 * @param array $library
	 *   An array of library information, passed by reference.
	 * @param string $name
	 *   The machine name of the passed-in library.
	 *
	 * @return array
	 */
	private function infoDefaults(&$library, $name)
	{
		$library += array(
			'machine_name'      => $name,
			'name'              => $name,
			'vendor_url'        => '',
			'download_url'      => '',
			'path'              => '',
			'library_path'      => null,
			'version_callback'  => 'getVersion',
			'version_arguments' => array(),
			'files'             => array(),
			'dependencies'      => array(),
			'variants'          => array(),
			'versions'          => array(),
			'integration_files' => array(),
			'callbacks'         => array(),
		);

		$library['callbacks'] += array(
			'info'                  => array(),
			'pre_detect'            => array(),
			'post_detect'           => array(),
			'pre_dependencies_load' => array(),
			'pre_load'              => array(),
			'post_load'             => array(),
		);

		// Add our own callbacks before any others.
		array_unshift($library['callbacks']['info'], 'prepareFiles');
		array_unshift($library['callbacks']['post_detect'], 'detectDependencies');

		return $library;
	}

	/**
	 * Library info callback to make all 'files' properties consistent.
	 *
	 * This turns libraries' file information declared as e.g.
	 * @code
	 * $library['files']['js'] = array('example_1.js', 'example_2.js');
	 * @endcode
	 * into
	 * @code
	 * $library['files']['js'] = array(
	 *   'example_1.js' => array(),
	 *   'example_2.js' => array(),
	 * );
	 * @endcode
	 * It does the same for the 'integration_files' property.
	 *
	 * @param $library
	 *   An associative array of library information or a part of it, passed by reference.
	 * @param $version
	 *   If the library information belongs to a specific version, the version string. NULL otherwise.
	 * @param $variant
	 *   If the library information belongs to a specific variant, the variant name. NULL otherwise.
	 */
	private function prepareFiles(&$library, $version = null, $variant = null)
	{
		// Both the 'files' property and the 'integration_files' property contain file declarations, and we want to make
		// both consistent.
		$file_types = array();
		if(isset($library['files']))
		{
			$file_types[] = &$library['files'];
		}
		if(isset($library['integration_files']))
		{
			// Integration files are additionally keyed by plugin.
			foreach($library['integration_files'] as &$integration_files)
			{
				$file_types[] = &$integration_files;
			}
		}
		foreach($file_types as &$files)
		{
			// Go through all supported types of files.
			foreach(array('js', 'css', 'php') as $type)
			{
				if(isset($files[$type]))
				{
					foreach($files[$type] as $key => $value)
					{
						// Unset numeric keys and turn the respective values into keys.
						if(is_numeric($key))
						{
							$files[$type][$value] = array();
							unset($files[$type][$key]);
						}
					}
				}
			}
		}
	}

	/**
	 * Library post detect callback to process and detect dependencies.
	 *
	 * It checks whether each of the dependencies of a library are installed and available in a compatible version.
	 *
	 * @param $library
	 *   An associative array of library information or a part of it, passed by reference.
	 * @param $version
	 *   If the library information belongs to a specific version, the version string. NULL otherwise.
	 * @param $variant
	 *   If the library information belongs to a specific variant, the variant name. NULL otherwise.
	 */
	private function detectDependencies(&$library, $version = null, $variant = null)
	{
		if(isset($library['dependencies']))
		{
			foreach($library['dependencies'] as &$dependency_string)
			{
				$dependency_info = $this->parseDependency($dependency_string);
				$dependency = $this->detect($dependency_info['name']);
				if(!$dependency['installed'])
				{
					$library['installed'] = false;
					$library['error'] = LAN_LIBRARY_MANAGER_07;

					$replace_with = array($dependency['name'], $library['name']);
					$library['error_message'] = e107::getParser()->lanVars(LAN_LIBRARY_MANAGER_01, $replace_with, true);
				}
				elseif($this->checkIncompatibility($dependency_info, $dependency['version']))
				{
					$library['installed'] = false;
					$library['error'] = LAN_LIBRARY_MANAGER_08;

					$replace_with = array($dependency['version'], $library['name'], $library['name']);
					$library['error_message'] = e107::getParser()->lanVars(LAN_LIBRARY_MANAGER_02, $replace_with, true);
				}

				// Remove the version string from the dependency, so load() can load the libraries directly.
				$dependency_string = $dependency_info['name'];
			}
		}
	}

	/**
	 * Invokes library callbacks.
	 *
	 * @param $group
	 *   A string containing the group of callbacks that is to be applied. Should be either 'info', 'post_detect'.
	 * @param $library
	 *   An array of library information, passed by reference.
	 */
	private function invoke($group, &$library)
	{
		// When introducing new callback groups in newer versions, stale cached library information somehow reaches
		// this point during the database update before clearing the library cache.
		if(empty($library['callbacks'][$group]))
		{
			return;
		}

		foreach($library['callbacks'][$group] as $callback)
		{
			$this->traverseLibrary($library, $callback);
		}
	}

	/**
	 * Helper function to apply a callback to all parts of a library.
	 *
	 * Because library declarations can include variants and versions, and those version declarations can in turn
	 * include variants, modifying e.g. the 'files' property everywhere it is declared can be quite cumbersome, in
	 * which case this helper function is useful.
	 *
	 * @param $library
	 *   An array of library information, passed by reference.
	 * @param $callback
	 *   A string containing the callback to apply to all parts of a library.
	 */
	private function traverseLibrary(&$library, $callback)
	{
		// If callback belongs to $this class.
		if(method_exists($this, $callback))
		{
			// Always apply the callback to the top-level library.
			// Params: $library, $version, $variant
			$this->$callback($library, null, null);

			// Apply the callback to versions.
			if(isset($library['versions']))
			{
				foreach($library['versions'] as $version_string => &$version)
				{
					$this->$callback($version, $version_string, null);

					// Versions can include variants as well.
					if(isset($version['variants']))
					{
						foreach($version['variants'] as $version_variant_name => &$version_variant)
						{
							$this->$callback($version_variant, $version_string, $version_variant_name);
						}
					}
				}
			}

			// Apply the callback to variants.
			if(isset($library['variants']))
			{
				foreach($library['variants'] as $variant_name => &$variant)
				{
					$this->$callback($variant, null, $variant_name);
				}
			}
		}
		else
		{
			// TODO: Provide the ability to use third-party callbacks (are defined in e_library.php files) for groups:
			// 'info', 'pre_detect', 'post_detect', 'pre_dependencies_load', 'pre_load', 'post_load'
		}
	}

	/**
	 * Loads a library's files.
	 *
	 * @param $library
	 *   An array of library information as returned by info().
	 *
	 * @return int
	 *   The number of loaded files.
	 */
	private function loadFiles($library)
	{
		$siteTheme = e107::getPref('sitetheme');
		$adminTheme = e107::getPref('admintheme');

		// Load integration_files.
		if(!$library['post_load_integration_files'] && !empty($library['integration_files']))
		{
			foreach($library['integration_files'] as $provider => $files)
			{
				// If provider is an installed plugin.
				if(e107::isInstalled($provider))
				{
					$this->loadFiles(array(
						'files'                       => $files,
						'path'                        => '',
						'library_path'                => e_PLUGIN . $provider,
						'post_load_integration_files' => false,
					));
				}
				// If provider is the admin theme, we only allow it for admin pages.
				elseif(e_ADMIN_AREA && $provider == $adminTheme)
				{
					$this->loadFiles(array(
						'files'                       => $files,
						'path'                        => '',
						'library_path'                => e_THEME . $provider,
						'post_load_integration_files' => false,
					));
				}
				// If provider is the site theme, we only allow it on user areas.
				elseif(!deftrue(e_ADMIN_AREA, false) && $provider == $siteTheme)
				{
					$this->loadFiles(array(
						'files'                       => $files,
						'path'                        => '',
						'library_path'                => e_THEME . $provider,
						'post_load_integration_files' => false,
					));
				}
			}
		}

		// Construct the full path to the library for later use.
		$path = e107::getParser()->replaceConstants($library['library_path']);
		$path = ($library['path'] !== '' ? $path . '/' . $library['path'] : $path);
		$path = rtrim($path, '/');

		// Count the number of loaded files for the return value.
		$count = 0;

		// Load both the JavaScript and the CSS files.
		foreach(array('js', 'css') as $type)
		{
			if(!empty($library['files'][$type]))
			{
				foreach($library['files'][$type] as $data => $options)
				{
					// If the value is not an array, it's a filename and passed as first (and only) argument.
					if(!is_array($options))
					{
						$data = $options;
						$options = array();
					}
					// In some cases, the first parameter ($data) is an array. Arrays can't be passed as keys in PHP,
					// so we have to get $data from the value array.
					if(is_numeric($data))
					{
						$data = $options['data'];
						unset($options['data']);
					}
					// Prepend the library_path to the file name.
					$data = "$path/$data";
					// Apply the default zone if the zone isn't explicitly given.
					if(!isset($options['zone']))
					{
						$options['zone'] = ($type == 'js') ? 2 : 2; // TODO: default zones.
					}
					// Apply the default type if the type isn't explicitly given.
					if(!isset($options['type']))
					{
						$options['type'] = 'url';
					}
					if($type == 'js')
					{
						e107::js($options['type'], $data, null, $options['zone']);
					}
					elseif($type == 'css')
					{
						e107::css($options['type'], $data, null);
					}
					$count++;
				}
			}
		}

		// Load PHP files.
		if(!empty($library['files']['php']))
		{
			foreach($library['files']['php'] as $file => $array)
			{
				$file_path1 = $path . '/' . $file;
				$file_path2 = e_ROOT . $path . '/' . $file;

				if(file_exists($file_path1))
				{
					$this->_requireOnce($file_path1);
					$count++;
				}
				elseif(file_exists($file_path2))
				{
					$this->_requireOnce($file_path2);
					$count++;
				}
			}
		}

		// Load integration_files.
		if($library['post_load_integration_files'] && !empty($library['integration_files']))
		{
			foreach($library['integration_files'] as $provider => $files)
			{
				// If provider is an installed plugin.
				if(e107::isInstalled($provider))
				{
					$this->loadFiles(array(
						'files'                       => $files,
						'path'                        => '',
						'library_path'                => e_PLUGIN . $provider,
						'post_load_integration_files' => false,
					));
				}
				// If provider is the admin theme, we only allow it for admin pages.
				elseif(e_ADMIN_AREA && $provider == $adminTheme)
				{
					$this->loadFiles(array(
						'files'                       => $files,
						'path'                        => '',
						'library_path'                => e_THEME . $provider,
						'post_load_integration_files' => false,
					));
				}
				// If provider is the site theme, we only allow it on user areas.
				elseif(!deftrue(e_ADMIN_AREA, false) && $provider == $siteTheme)
				{
					$this->loadFiles(array(
						'files'                       => $files,
						'path'                        => '',
						'library_path'                => e_THEME . $provider,
						'post_load_integration_files' => false,
					));
				}
			}
		}

		return $count;
	}

	/**
	 * Wrapper function for require_once.
	 *
	 * A library file could set a $path variable in file scope. Requiring such a file directly in loadFiles()
	 * would lead to the local $path variable being overridden after the require_once statement. This would break
	 * loading further files. Therefore we use this trivial wrapper which has no local state that can be tampered with.
	 *
	 * @param $file_path
	 *   The file path of the file to require.
	 */
	private function _requireOnce($file_path)
	{
		// TODO: use e107_require_once() instead?
		require_once $file_path;
	}

	/**
	 * Gets the version information from an arbitrary library.
	 *
	 * @param $library
	 *   An associative array containing all information about the library.
	 * @param $options
	 *   An associative array containing with the following keys:
	 *   - file: The filename to parse for the version, relative to the library path. For example: 'docs/changelog.txt'.
	 *   - pattern: A string containing a regular expression (PCRE) to match the library version. For example:
	 *     '@version\s+([0-9a-zA-Z\.-]+)@'. Note that the returned version is not the match of the entire pattern (i.e.
	 *     '@version 1.2.3' in the above example) but the match of the first sub-pattern (i.e. '1.2.3' in the above example).
	 *   - lines: (optional) The maximum number of lines to search the pattern in. Defaults to 20.
	 *   - cols: (optional) The maximum number of characters per line to take into account. Defaults to 200. In case of
	 *     minified or compressed files, this prevents reading the entire file into memory.
	 *
	 * @return mixed
	 *   A string containing the version of the library. Or null.
	 */
	private function getVersion($library, $options)
	{
		// Provide defaults.
		$options += array(
			'file'    => '',
			'pattern' => '',
			'lines'   => 20,
			'cols'    => 200,
		);

		$libraryPath = e107::getParser()->replaceConstants($library['library_path']);
		$file = $libraryPath . '/' . $options['file'];

		if(empty($options['file']))
		{
			return;
		}

		// If remote file (e.g. CDN URL)... we download file to temp, and get version number.
		// The library will be cached with version number, so this only run once per library.
		if(substr($file, 0, 4) == 'http')
		{
			$content = file_get_contents($file);
			$tmpFile = tempnam(sys_get_temp_dir(), 'lib_');

			if($tmpFile)
			{
				file_put_contents($tmpFile, $content);
				$file = $tmpFile;
			}
		}

		if(!file_exists($file))
		{
			return;
		}

		$file = fopen($file, 'r');
		while($options['lines'] && $line = fgets($file, $options['cols']))
		{
			if(preg_match($options['pattern'], $line, $version))
			{
				fclose($file);

				// If downloaded file, we need to unlink it from temp.
				if(isset($tmpFile) && file_exists($tmpFile))
				{
					unlink($tmpFile);
				}

				return $version[1];
			}
			$options['lines']--;
		}
		fclose($file);

		// If downloaded file, we need to unlink it from temp.
		if(isset($tmpFile) && file_exists($tmpFile))
		{
			unlink($tmpFile);
		}

		return;
	}

	/**
	 * Parses a dependency for comparison by checkIncompatibility().
	 *
	 * @param $dependency
	 *   A dependency string, which specifies a plugin dependency, and versions that are supported. Supported formats
	 *   include:
	 *   - 'plugin'
	 *   - 'plugin (>=version, version)'
	 *
	 * @return array
	 *   An associative array with three keys:
	 *   - 'name' includes the name of the thing to depend on (e.g. 'foo').
	 *   - 'original_version' contains the original version string (which can be used in the UI for reporting
	 *     incompatibilities).
	 *   - 'versions' is a list of associative arrays, each containing the keys 'op' and 'version'. 'op' can be one of:
	 *     '=', '==', '!=', '<>', '<', '<=', '>', or '>='. 'version' is one piece like '4.5-beta3'.
	 *   Callers should pass this structure to checkIncompatibility().
	 */
	private function parseDependency($dependency)
	{
		$value = array();

		// We use named subpatterns and support every op that version_compare supports. Also, op is optional and
		// defaults to equals.
		$p_op = '(?P<operation>!=|==|=|<|<=|>|>=|<>)?';
		$p_major = '(?P<major>\d+)';
		// By setting the minor version to x, branches can be matched.
		$p_minor = '(?P<minor>(?:\d+|x)(?:-[A-Za-z]+\d+)?)';
		$parts = explode('(', $dependency, 2);
		$value['name'] = trim($parts[0]);

		if(isset($parts[1]))
		{
			$value['original_version'] = ' (' . $parts[1];
			foreach(explode(',', $parts[1]) as $version)
			{
				if(preg_match("/^\s*$p_op\s*$p_major\.$p_minor/", $version, $matches))
				{
					$op = !empty($matches['operation']) ? $matches['operation'] : '=';
					if($matches['minor'] == 'x')
					{
						// "2.x" to mean any version that begins with "2" (e.g. 2.0, 2.9 are all "2.x").
						// PHP's version_compare(), on the other hand, treats "x" as a string; so to version_compare(),
						// "2.x" is considered less than 2.0. This means that >=2.x and <2.x are handled by
						// version_compare() as we need, but > and <= are not.
						if($op == '>' || $op == '<=')
						{
							$matches['major']++;
						}
						// Equivalence can be checked by adding two restrictions.
						if($op == '=' || $op == '==')
						{
							$value['versions'][] = array('op' => '<', 'version' => ($matches['major'] + 1) . '.x');
							$op = '>=';
						}
					}
					$value['versions'][] = array('op' => $op, 'version' => $matches['major'] . '.' . $matches['minor']);
				}
			}
		}

		return $value;
	}

	/**
	 * Checks whether a version is compatible with a given dependency.
	 *
	 * @param $v
	 *   The parsed dependency structure from parseDependency().
	 * @param $current_version
	 *   The version to check against (like 4.2).
	 *
	 * @return mixed
	 *   NULL if compatible, otherwise the original dependency version string that caused the incompatibility.
	 *
	 * @see parseDependency()
	 */
	private function checkIncompatibility($v, $current_version)
	{
		if(!empty($v['versions']))
		{
			foreach($v['versions'] as $required_version)
			{
				if((isset($required_version['op'])))
				{
					if(!version_compare($current_version, $required_version['version'], $required_version['op']))
					{
						return $v['original_version'];
					}
				}
			}
		}
	}

}
