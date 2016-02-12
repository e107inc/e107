<?php

/**
 * @file
 * External library handling for e107 plugins/themes.
 *
 * TODO:
 * - Provide the ability to use third-party callbacks (are defined in e_library.php files) for groups:
 *   'info', 'pre-detect', 'post-detect', 'pre-dependencies-load', 'pre-load', 'post-load'
 */

// [e_LANGUAGEDIR]/[e_LANGUAGE]/lan_library_manager.php
e107::lan('core', 'library_manager');


/**
 * Class e_library_manager.
 */
class e_library_manager
{

	/**
	 * Singleton instance.
	 * Allow class extends - override {@link getInstance()}
	 *
	 * @var e_library_manager
	 */
	protected static $_instance = null;

	/**
	 * Constructor
	 * Use {@link getInstance()}, direct instantiating is not possible for signleton objects.
	 */
	protected function __construct()
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
	 * Get singleton instance.
	 *
	 * @return e_library_manager
	 */
	public static function getInstance()
	{
		if(null === self::$_instance)
		{
			self::$_instance = new self();
			self::$_instance->_init();
		}
		return self::$_instance;
	}

	/**
	 * Tries to detect a library and its installed version.
	 *
	 * @param $name
	 *   The machine name of a library to return registered information for.
	 *
	 * @return array|false
	 *   An associative array containing registered information for the library specified by $name, or FALSE if the
	 *   library $name is not registered. In addition to the keys returned by libraryInfo(), the following keys are
	 *   contained:
	 *   - installed: A boolean indicating whether the library is installed. Note that not only the top-level library,
	 *     but also each variant contains this key.
	 *   - version: If the version could be detected, the full version string.
	 *   - error: If an error occurred during library detection, one of the following error statuses:
	 *     "not found", "not detected", "not supported".
	 *   - error message: If an error occurred during library detection, a detailed error message.
	 */
	public function libraryDetect($name)
	{
		// Re-use the statically cached value of libraryInfo() to save memory.
		$library = &$this->libraryInfo($name);

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
		if(!isset($library['library path']))
		{
			$library['library path'] = $this->libraryGetPath($library['machine name']);
		}

		if($library['library path'] === false || !file_exists($library['library path']))
		{
			$library['error'] = LAN_LIBRARY_MANAGER_09;

			$replace = array('[x]');
			$replace_with = array($library['name']);

			$library['error message'] = str_replace($replace, $replace_with, LAN_LIBRARY_MANAGER_03);

			return $library;
		}

		// TODO:
		// Invoke callbacks in the 'pre-detect' group.
		$this->libraryInvoke('pre-detect', $library);

		// Detect library version, if not hardcoded.
		if(!isset($library['version']))
		{
			// If version callback is a method in $this class.
			if(method_exists($this, $library['version callback']))
			{
				// We support both a single parameter, which is an associative array, and an indexed array of multiple
				// parameters.
				if(isset($library['version arguments'][0]))
				{
					// Add the library as the first argument.
					$classMethod = array($this, $library['version callback']);
					$params = array_merge(array($library), $library['version arguments']);
					$variant['installed'] = call_user_func_array($classMethod, $params);
				}
				else
				{
					$method = $library['version callback'];
					$library['version'] = $this->$method($library, $library['version arguments']);
				}
			}
			// If version callback is a method in e_library.php file.
			else
			{
				if(varset($library['plugin'], false))
				{
					e107_require_once(e_PLUGIN . $library['plugin'] . '/e_library.php');
					$addonClass = $library['plugin'] . '_library';
				}
				elseif(varset($library['theme'], false))
				{
					e107_require_once(e_THEME . $library['theme'] . '/e_library.php');
					$addonClass = $library['theme'] . '_library';
				}

				// We support both a single parameter, which is an associative array, and an
				// indexed array of multiple parameters.
				if(isset($library['version arguments'][0]))
				{
					if(isset($addonClass) && class_exists($addonClass))
					{
						$class = new $addonClass();
						if(method_exists($class, $library['version callback']))
						{
							// Add the library as the first argument.
							// Call PLUGIN/THEME_library::VERSION_CALLBACK().
							$classMethod = array($class, $library['version callback']);
							$params = array_merge(array($library), $library['version arguments']);
							$variant['installed'] = call_user_func_array($classMethod, $params);
						}
					}
				}
				else
				{
					if(isset($addonClass) && class_exists($addonClass))
					{
						$class = new $addonClass();
						if(method_exists($class, $library['version callback']))
						{
							// Call PLUGIN/THEME_library::VERSION_CALLBACK().
							$method = $library['version callback'];
							$library['version'] = $class->$method($library, $library['version arguments']);
						}
					}
				}
			}

			if(empty($library['version']))
			{
				$library['error'] = LAN_LIBRARY_MANAGER_10;

				$replace = array('[x]');
				$replace_with = array($library['name']);

				$library['error message'] = str_replace($replace, $replace_with, LAN_LIBRARY_MANAGER_04);

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

				$replace = array('[x]', '[y]');
				$replace_with = array($library['version'], $library['name']);

				$library['error message'] = str_replace($replace, $replace_with, LAN_LIBRARY_MANAGER_05);

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
				if(!isset($variant['variant callback']))
				{
					$variant['installed'] = true;
				}
				else
				{
					if(varset($library['plugin'], false))
					{
						e107_require_once(e_PLUGIN . $library['plugin'] . '/e_library.php');
						$addonClass = $library['plugin'] . '_library';
					}
					elseif(varset($library['theme'], false))
					{
						e107_require_once(e_THEME . $library['theme'] . '/e_library.php');
						$addonClass = $library['theme'] . '_library';
					}

					// We support both a single parameter, which is an associative array, and an indexed array of
					// multiple parameters.
					if(isset($variant['variant arguments'][0]))
					{
						if(isset($addonClass) && class_exists($addonClass))
						{
							$class = new $addonClass();
							if(method_exists($class, $variant['variant callback']))
							{
								// Add the library as the first argument, and the variant name as the second.
								// Call PLUGIN/THEME_library::VARIANT_CALLBACK().
								$classMethod = array($class, $library['variant callback']);
								$params = array_merge(array($library, $variant_name), $variant['variant arguments']);
								$variant['installed'] = call_user_func_array($classMethod, $params);
							}
							else
							{
								$variant['installed'] = true;
							}
						}
						else
						{
							$variant['installed'] = true;
						}
					}
					else
					{
						if(isset($addonClass) && class_exists($addonClass))
						{
							$class = new $addonClass();
							if(method_exists($class, $variant['variant callback']))
							{
								// Call PLUGIN/THEME_library::VARIANT_CALLBACK().
								$method = $variant['variant callback'];
								$variant['installed'] = $class->$method($library, $variant_name, $variant['variant arguments']);
							}
							else
							{
								$variant['installed'] = true;
							}
						}
						else
						{
							$variant['installed'] = true;
						}
					}
					if(!$variant['installed'])
					{
						$variant['error'] = LAN_LIBRARY_MANAGER_09;

						$replace = array('[x]', '[y]');
						$replace_with = array($variant_name, $library['name']);

						$variant['error message'] = str_replace($replace, $replace_with, LAN_LIBRARY_MANAGER_06);
					}
				}
			}
		}

		// If we end up here, the library should be usable.
		$library['installed'] = true;

		// Invoke callbacks in the 'post-detect' group.
		$this->libraryInvoke('post-detect', $library);

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
	 * @return
	 *   An associative array of the library information as returned from libraryInfo(). The top-level properties
	 *   contain the effective definition of the library (variant) that has been loaded. Additionally:
	 *   - installed: Whether the library is installed, as determined by libraryDetectLibrary().
	 *   - loaded: Either the amount of library files that have been loaded, or FALSE if the library could not be
	 *   loaded. See MYPLUGIN_library::libraryInfo() for more information.
	 */
	public function libraryLoad($name, $variant = null)
	{
		static $loaded;

		if(!isset($loaded[$name]))
		{
			// TODO: cache result from libraryDetect() !!!!!!
			$library = $this->libraryDetect($name);

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
			// Invoke callbacks in the 'pre-dependencies-load' group.
			$this->libraryInvoke('pre-dependencies-load', $library);

			// If the library (variant) is installed, load it.
			$library['loaded'] = false;
			if($library['installed'])
			{
				// Load library dependencies.
				if(isset($library['dependencies']))
				{
					foreach($library['dependencies'] as $dependency)
					{
						$this->libraryLoad($dependency);
					}
				}

				// TODO:
				// Invoke callbacks in the 'pre-load' group.
				$this->libraryInvoke('pre-load', $library);

				// Load all the files associated with the library.
				$library['loaded'] = $this->libraryLoadFiles($library);

				// TODO:
				// Invoke callbacks in the 'post-load' group.
				$this->libraryInvoke('post-load', $library);
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
	 * @param $base_path
	 *   Whether to prefix the resulting path with base_path().
	 *
	 * @return string
	 *   The path to the specified library or FALSE if the library wasn't found.
	 */
	private function libraryGetPath($name, $base_path = false)
	{
		static $libraries;

		if(!isset($libraries))
		{
			$libraries = $this->libraryGetLibraries();
		}

		// e_HTTP will at least default to '/'.
		$path = ($base_path ? e_HTTP : '');
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
	private function libraryGetLibraries()
	{
		$dir = e_WEB . 'lib';

		// Retrieve list of directories.
		$directories = array();
		$nomask = array('CVS');
		if(is_dir($dir) && $handle = opendir($dir))
		{
			while(false !== ($file = readdir($handle)))
			{
				if(!in_array($file, $nomask) && $file[0] != '.')
				{
					if(is_dir("$dir/$file"))
					{
						$directories[$file] = "$dir/$file";
					}
				}
			}
			closedir($handle);
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
	private function &libraryInfo($library = null)
	{
		// This static cache is re-used by libraryDetect() to save memory.
		static $libraries;

		if(!isset($libraries))
		{
			$libraries = array();

			$plugins = array();
			$themes = array();

			// Gather information from PLUGIN_library::config().
			$pluginInfo = e107::getAddonConfig('e_library', 'library', 'config');
			foreach($pluginInfo as $plugin => $info)
			{
				foreach($info as $machine_name => $properties)
				{
					$properties['info type'] = 'plugin';
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
				if(is_readable(e_THEME . $theme . '/e_library.php'))
				{
					e107_require_once(e_THEME . $theme . '/e_library.php');

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
									$properties['info type'] = 'theme';
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
				$this->libraryInfoDefaults($properties, $machine_name);
			}

			// Allow enabled plugins (with e_library.php file) to alter the registered libraries.
			foreach($plugins as $plugin)
			{
				e107_require_once(e_PLUGIN . $plugin . '/e_library.php');
				$addonClass = $plugin . '_library';

				if(class_exists($addonClass))
				{
					$class = new $addonClass();
					if(method_exists($class, 'config_alter'))
					{
						$class->config_alter($libraries);
					}
				}
			}

			// Allow enabled themes (with e_library.php file) to alter the registered libraries.
			foreach($themes as $theme)
			{
				e107_require_once(e_THEME . $theme . '/e_library.php');
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
				$this->libraryInvoke('info', $properties);
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
	private function libraryInfoDefaults(&$library, $name)
	{
		$library += array(
			'machine name'      => $name,
			'name'              => $name,
			'vendor url'        => '',
			'download url'      => '',
			'path'              => '',
			'library path'      => null,
			'version callback'  => 'libraryGetVersion',
			'version arguments' => array(),
			'files'             => array(),
			'dependencies'      => array(),
			'variants'          => array(),
			'versions'          => array(),
			'integration files' => array(),
			'callbacks'         => array(),
		);

		$library['callbacks'] += array(
			'info'                  => array(),
			'pre-detect'            => array(),
			'post-detect'           => array(),
			'pre-dependencies-load' => array(),
			'pre-load'              => array(),
			'post-load'             => array(),
		);

		// Add our own callbacks before any others.
		array_unshift($library['callbacks']['info'], 'libraryPrepareFiles');
		array_unshift($library['callbacks']['post-detect'], 'libraryDetectDependencies');

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
	 * It does the same for the 'integration files' property.
	 *
	 * @param $library
	 *   An associative array of library information or a part of it, passed by reference.
	 * @param $version
	 *   If the library information belongs to a specific version, the version string. NULL otherwise.
	 * @param $variant
	 *   If the library information belongs to a specific variant, the variant name. NULL otherwise.
	 */
	private function libraryPrepareFiles(&$library, $version = null, $variant = null)
	{
		// Both the 'files' property and the 'integration files' property contain file declarations, and we want to make
		// both consistent.
		$file_types = array();
		if(isset($library['files']))
		{
			$file_types[] = &$library['files'];
		}
		if(isset($library['integration files']))
		{
			// Integration files are additionally keyed by plugin.
			foreach($library['integration files'] as &$integration_files)
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
	 * Library post-detect callback to process and detect dependencies.
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
	private function libraryDetectDependencies(&$library, $version = null, $variant = null)
	{
		if(isset($library['dependencies']))
		{
			foreach($library['dependencies'] as &$dependency_string)
			{
				$dependency_info = $this->libraryParseDependency($dependency_string);
				$dependency = $this->libraryDetect($dependency_info['name']);
				if(!$dependency['installed'])
				{
					$library['installed'] = false;
					$library['error'] = LAN_LIBRARY_MANAGER_07;

					$replace = array('[x]', '[y]');
					$replace_with = array($dependency['name'], $library['name']);

					$library['error message'] = str_replace($replace, $replace_with, LAN_LIBRARY_MANAGER_01);
				}
				elseif($this->libraryCheckIncompatibility($dependency_info, $dependency['version']))
				{
					$library['installed'] = false;
					$library['error'] = LAN_LIBRARY_MANAGER_08;

					$replace = array('[x]', '[y]', '[z]');
					$replace_with = array($dependency['version'], $library['name'], $library['name']);

					$library['error message'] = str_replace($replace, $replace_with, LAN_LIBRARY_MANAGER_02);
				}

				// Remove the version string from the dependency, so libraryLoad() can load the libraries directly.
				$dependency_string = $dependency_info['name'];
			}
		}
	}

	/**
	 * Invokes library callbacks.
	 *
	 * @param $group
	 *   A string containing the group of callbacks that is to be applied. Should be either 'info', 'post-detect'.
	 * @param $library
	 *   An array of library information, passed by reference.
	 */
	private function libraryInvoke($group, &$library)
	{
		// When introducing new callback groups in newer versions, stale cached library information somehow reaches
		// this point during the database update before clearing the library cache.
		if(empty($library['callbacks'][$group]))
		{
			return;
		}

		foreach($library['callbacks'][$group] as $callback)
		{
			$this->libraryTraverseLibrary($library, $callback);
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
	private function libraryTraverseLibrary(&$library, $callback)
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
			// 'info', 'pre-detect', 'post-detect', 'pre-dependencies-load', 'pre-load', 'post-load'
		}
	}

	/**
	 * Loads a library's files.
	 *
	 * @param $library
	 *   An array of library information as returned by libraryInfo().
	 *
	 * @return int
	 *   The number of loaded files.
	 */
	private function libraryLoadFiles($library)
	{
		$siteTheme = e107::getPref('sitetheme');
		$adminTheme = e107::getPref('admintheme');

		// Load integration files.
		if(!$library['post-load integration files'] && !empty($library['integration files']))
		{
			foreach($library['integration files'] as $provider => $files)
			{
				// If provider is an installed plugin.
				if(e107::isInstalled($provider))
				{
					$this->libraryLoadFiles(array(
						'files'                       => $files,
						'path'                        => '',
						'library path'                => e_PLUGIN . $provider,
						'post-load integration files' => false,
					));
				}
				// If provider is the admin theme, we only allow it for admin pages.
				elseif(e_ADMIN_AREA && $provider == $adminTheme)
				{
					$this->libraryLoadFiles(array(
						'files'                       => $files,
						'path'                        => '',
						'library path'                => e_THEME . $provider,
						'post-load integration files' => false,
					));
				}
				// If provider is the site theme, we only allow it for on the user area.
				elseif(!deftrue(e_ADMIN_AREA, false) && $provider == $siteTheme)
				{
					$this->libraryLoadFiles(array(
						'files'                       => $files,
						'path'                        => '',
						'library path'                => e_THEME . $provider,
						'post-load integration files' => false,
					));
				}
			}
		}

		// Construct the full path to the library for later use.
		$path = $library['library path'];
		$path = ($library['path'] !== '' ? $path . '/' . $library['path'] : $path);

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
					// Prepend the library path to the file name.
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
				// TODO: review these includes.
				$file_path1 = $path . '/' . $file;
				$file_path2 = e_ROOT . $path . '/' . $file;

				if(file_exists($file_path1))
				{
					$this->_libraryRequireOnce($file_path1);
					$count++;
				}
				elseif(file_exists($file_path2))
				{
					$this->_libraryRequireOnce($file_path2);
					$count++;
				}
			}
		}

		// Load integration files.
		if($library['post-load integration files'] && !empty($library['integration files']))
		{
			foreach($library['integration files'] as $provider => $files)
			{
				// If provider is an installed plugin.
				if(e107::isInstalled($provider))
				{
					$this->libraryLoadFiles(array(
						'files'                       => $files,
						'path'                        => '',
						'library path'                => e_PLUGIN . $provider,
						'post-load integration files' => false,
					));
				}
				// If provider is the admin theme, we only allow it for admin pages.
				elseif(e_ADMIN_AREA && $provider == $adminTheme)
				{
					$this->libraryLoadFiles(array(
						'files'                       => $files,
						'path'                        => '',
						'library path'                => e_THEME . $provider,
						'post-load integration files' => false,
					));
				}
				// If provider is the site theme, we only allow it for on the user area.
				elseif(!deftrue(e_ADMIN_AREA, false) && $provider == $siteTheme)
				{
					$this->libraryLoadFiles(array(
						'files'                       => $files,
						'path'                        => '',
						'library path'                => e_THEME . $provider,
						'post-load integration files' => false,
					));
				}
			}
		}

		return $count;
	}

	/**
	 * Wrapper function for require_once.
	 *
	 * A library file could set a $path variable in file scope. Requiring such a file directly in libraryLoadFiles()
	 * would lead to the local $path variable being overridden after the require_once statement. This would break
	 * loading further files. Therefore we use this trivial wrapper which has no local state that can be tampered with.
	 *
	 * @param $file_path
	 *   The file path of the file to require.
	 */
	private function _libraryRequireOnce($file_path)
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
	 *   A string containing the version of the library.
	 */
	private function libraryGetVersion($library, $options)
	{
		// Provide defaults.
		$options += array(
			'file'    => '',
			'pattern' => '',
			'lines'   => 20,
			'cols'    => 200,
		);

		$file = $library['library path'] . '/' . $options['file'];
		if(empty($options['file']) || !file_exists($file))
		{
			return;
		}

		$file = fopen($file, 'r');
		while($options['lines'] && $line = fgets($file, $options['cols']))
		{
			if(preg_match($options['pattern'], $line, $version))
			{
				fclose($file);
				return $version[1];
			}
			$options['lines']--;
		}
		fclose($file);
	}

	/**
	 * Parses a dependency for comparison by libraryCheckIncompatibility().
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
	 *   Callers should pass this structure to libraryCheckIncompatibility().
	 */
	private function libraryParseDependency($dependency)
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
	 *   The parsed dependency structure from libraryParseDependency().
	 * @param $current_version
	 *   The version to check against (like 4.2).
	 *
	 * @return
	 *   NULL if compatible, otherwise the original dependency version string that caused the incompatibility.
	 *
	 * @see libraryParseDependency()
	 */
	private function libraryCheckIncompatibility($v, $current_version)
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
