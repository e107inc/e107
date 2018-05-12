<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * External library handling for e107 core/plugins/themes.
 *
 * TODO:
 * - Provide the ability to use third-party callbacks (are defined in e_library.php files) for groups:
 *   'info', 'pre_detect', 'post_detect', 'pre_dependencies_load', 'pre_load', 'post_load'
 */

// [e_LANGUAGEDIR]/[e_LANGUAGE]/lan_library_manager.php
e107::lan('core', 'library_manager');



/**
 * Class core_library.
 */
class core_library
{

	/**
	 * Provides information about external libraries.
	 *
	 * Provides information about:
	 * - jQuery (CDN).
	 * - jQuery (local).
	 * - jQuery Once (CDN)
	 * - jQuery Once (local)
	 * - jQuery UI (CDN)
	 * - jQuery UI (local)
	 * - Bootstrap (CDN)
	 * - Bootstrap (local)
	 * - Bootstrap Editable (CDN)
	 * - Bootstrap Editable (local)
	 * - Font-Awesome (CDN)
	 * - Font-Awesome (local)
	 */
	public function config()
	{
		$libraries = array();

		// jQuery (CDN).
		$libraries['cdn.jquery'] = array(
			'name'              => 'jQuery (CDN)',
			'vendor_url'        => 'https://jquery.com/',
			'version_arguments' => array(
				'file'    => 'jquery.min.js',
				'pattern' => '/jQuery\s+v(\d\.\d\.\d+)/',
				'lines'   => 5,
			),
			'files'             => array(
				'js' => array(
					'jquery.min.js' => array(
						'zone' => 1,
						'type' => 'url',
					),
				),
			),
			'variants'          => array(
				// 'unminified' version for debugging.
				'dev' => array(
					'files' => array(
						'js' => array(
							'jquery.js' => array(
								'zone' => 1,
								'type' => 'url',
							),
						),
					),
				),
			),
			// Override library path to CDN.
			'library_path'      => 'https://cdn.jsdelivr.net/jquery',
			'path'              => '2.2.4',
		);

		// jQuery (local).
		$libraries['jquery'] = array(
			'name'              => 'jQuery (local)',
			'vendor_url'        => 'https://jquery.com/',
			'version_arguments' => array(
				'file'    => 'dist/jquery.min.js',
				'pattern' => '/v(\d\.\d\.\d+)/',
				'lines'   => 5,
			),
			'files'             => array(
				'js' => array(
					'dist/jquery.min.js' => array(
						'zone' => 1,
						'type' => 'url',
					),
				),
			),
			'variants'          => array(
				// 'unminified' version for debugging.
				'dev' => array(
					'files' => array(
						'js' => array(
							'dist/jquery.js' => array(
								'zone' => 1,
								'type' => 'url',
							),
						),
					),
				),
			),
			'library_path'      => '{e_WEB}lib/jquery',
			'path'              => '2.2.4',
		);

		// jQuery Once (CDN).
		$libraries['cdn.jquery.once'] = array(
			'name'              => 'jQuery Once (CDN)',
			'vendor_url'        => 'https://plugins.jquery.com/once/',
			'version_arguments' => array(
				'file'    => 'jquery.once.min.js',
				'pattern' => '/jQuery\sOnce\s+v(\d\.\d\.\d+)/',
				'lines'   => 5,
			),
			'files'             => array(
				'js' => array(
					'jquery.once.min.js' => array(
						'zone' => 2,
						'type' => 'footer',
					),
				),
			),
			'variants'          => array(
				// 'unminified' version for debugging.
				'dev' => array(
					'files' => array(
						'js' => array(
							// There is no non-minified version.
							'jquery.once.min.js' => array(
								'zone' => 2,
								'type' => 'footer',
							),
						),
					),
				),
			),
			// Override library path to CDN.
			'library_path'      => 'https://cdn.jsdelivr.net/jquery.once',
			'path'              => '2.1.2',
		);

		// jQuery Once (local).
		$libraries['jquery.once'] = array(
			'name'              => 'jQuery Once (local)',
			'vendor_url'        => 'https://plugins.jquery.com/once/',
			'version_arguments' => array(
				'file'    => 'jquery.once.min.js',
				'pattern' => '/jQuery\sOnce\s+v(\d\.\d\.\d+)/',
				'lines'   => 5,
			),
			'files'             => array(
				'js' => array(
					'jquery.once.min.js' => array(
						'zone' => 2,
						'type' => 'footer',
					),
				),
			),
			'variants'          => array(
				// 'unminified' version for debugging.
				'dev' => array(
					'files' => array(
						'js' => array(
							// There is no non-minified version.
							'jquery.once.min.js' => array(
								'zone' => 2,
								'type' => 'footer',
							),
						),
					),
				),
			),
			// Override library path.
			'library_path'      => '{e_WEB}lib/jquery-once',
		);

		// jQuery UI (CDN).
		$libraries['cdn.jquery.ui'] = array(
			'name'              => 'jQuery UI (CDN)',
			'vendor_url'        => 'https://jqueryui.com/',
			'version_arguments' => array(
				'file'    => 'jquery-ui.min.js',
				'pattern' => '/v(\d\.\d+\.\d+)/',
				'lines'   => 5,
			),
			'files'             => array(
				'js'  => array(
					'jquery-ui.min.js' => array(
						'zone' => 2,
						'type' => 'footer',
					),
				),
				'css' => array(
					'jquery-ui.min.css' => array(
						'zone' => 2,
					),
				),
			),
			'variants'          => array(
				// 'unminified' version for debugging.
				'dev' => array(
					'files' => array(
						'js'  => array(
							// There is no non-minified version.
							'jquery-ui.min.js' => array(
								'zone' => 2,
								'type' => 'footer',
							),
						),
						'css' => array(
							// There is no non-minified version.
							'jquery-ui.min.css' => array(
								'zone' => 2,
							),
						),
					),
				),
			),
			// Override library path to CDN.
			'library_path'      => 'https://cdn.jsdelivr.net/jquery.ui',
			'path'              => '1.11.4',
		);

		// jQuery UI (local).
		$libraries['jquery.ui'] = array(
			'name'              => 'jQuery UI (local)',
			'vendor_url'        => 'https://jqueryui.com/',
			'version_arguments' => array(
				'file'    => 'jquery-ui.js',
				'pattern' => '/v(\d\.\d+\.\d+)/',
				'lines'   => 5,
			),
			'files'             => array(
				'js'  => array(
					'jquery-ui.min.js' => array(
						'zone' => 2,
						'type' => 'footer',
					),
				),
				'css' => array(
					'jquery-ui.min.css' => array(
						'zone' => 2,
					),
				),
			),
			'variants'          => array(
				// 'unminified' version for debugging.
				'dev' => array(
					'files' => array(
						'js'  => array(
							'jquery-ui.js' => array(
								'zone' => 2,
								'type' => 'footer',
							),
						),
						'css' => array(
							'jquery-ui.css' => array(
								'zone' => 2,
							),
						),
					),
				),
			),
			// Override library path.
			'library_path'      => '{e_WEB}lib/jquery-ui',
		);



		// ----------------- Bootstrap 4 ---------------------------//

			// Bootstrap (CDN).
		$libraries['cdn.bootstrap4'] = array(
			'name'              => 'Bootstrap 4 (CDN)',
			'vendor_url'        => 'http://getbootstrap.com/',
			'version_arguments' => array(
				'file'    => 'dist/js/bootstrap.min.js',
				'pattern' => '/Bootstrap\s+v(\d\.\d\.\d+)/',
				'lines'   => 5,
			),
			'files'             => array(
				'js'  => array(
					'dist/js/bootstrap.bundle.min.js' => array(
						'zone' => 2,
						'type' => 'footer',
					),
				),
				'css' => array(
					'dist/css/bootstrap.min.css' => array(
						'zone' => 1,
					),
				),
			),
			'variants'          => array(
				// 'unminified' version for debugging.
				/*'dev' => array(
					'files' => array(
						'js'  => array(
							'js/bootstrap.js' => array(
								'zone' => 2,
								'type' => 'footer',
							),
						),
						'css' => array(
							'css/bootstrap.css' => array(
								'zone' => 2,
							),
						),
					),
				),*/


			),
			// Override library path to CDN.
		//	https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.bundle.min.js
			'library_path'      => 'https://cdn.jsdelivr.net/npm/bootstrap@4.0.0',
			'path'              => '',
		);

		// Bootstrap (local).
		$libraries['bootstrap4'] = array(
			'name'              => 'Bootstrap 4 (local)',
			'vendor_url'        => 'http://getbootstrap.com/',
			'version_arguments' => array(
				'file'    => 'js/bootstrap.bundle.min.js',
				'pattern' => '/Bootstrap\s+v(\d\.\d\.\d+)/',
				'lines'   => 5,
			),
			'files'             => array(
				'js'  => array(
					'js/bootstrap.bundle.min.js' => array(
						'zone' => 2,
						'type' => 'footer',
					),
				),
				'css' => array(
					'css/bootstrap.min.css' => array(
						'zone' => 2,
					),
				),
			),
			'variants'          => array(
				// 'unminified' version for debugging.
				'dev' => array(
					'files' => array(
						'js'  => array(
							'js/bootstrap.bundle.js' => array(
								'zone' => 2,
								'type' => 'footer',
							),
						),
						'css' => array(
							'css/bootstrap.css' => array(
								'zone' => 2,
							),
						),
					),
				),
			),
			'library_path'      => '{e_WEB}lib/bootstrap',
			'path'              => '4',
		);


		// ----------------------------------------------------- //



		// Bootstrap (CDN).
		$libraries['cdn.bootstrap'] = array(
			'name'              => 'Bootstrap (CDN)',
			'vendor_url'        => 'http://getbootstrap.com/',
			'version_arguments' => array(
				'file'    => 'js/bootstrap.min.js',
				'pattern' => '/Bootstrap\s+v(\d\.\d\.\d+)/',
				'lines'   => 5,
			),
			'files'             => array(
				'js'  => array(
					'js/bootstrap.min.js' => array(
						'zone' => 2,
						'type' => 'footer',
					),
				),
				'css' => array(
					'css/bootstrap.min.css' => array(
						'zone' => 2,
					),
				),
			),
			'variants'          => array(
				// 'unminified' version for debugging.
				'dev' => array(
					'files' => array(
						'js'  => array(
							'js/bootstrap.js' => array(
								'zone' => 2,
								'type' => 'footer',
							),
						),
						'css' => array(
							'css/bootstrap.css' => array(
								'zone' => 2,
							),
						),
					),
				),


			),
			// Override library path to CDN.
			'library_path'      => 'https://cdn.jsdelivr.net/bootstrap',
			'path'              => '3.3.7',
		);

		// Bootstrap (local).
		$libraries['bootstrap'] = array(
			'name'              => 'Bootstrap (local)',
			'vendor_url'        => 'http://getbootstrap.com/',
			'version_arguments' => array(
				'file'    => 'dist/js/bootstrap.min.js',
				'pattern' => '/Bootstrap\s+v(\d\.\d\.\d+)/',
				'lines'   => 5,
			),
			'files'             => array(
				'js'  => array(
					'dist/js/bootstrap.min.js' => array(
						'zone' => 2,
						'type' => 'footer',
					),
				),
				'css' => array(
					'dist/css/bootstrap.min.css' => array(
						'zone' => 2,
					),
				),
			),
			'variants'          => array(
				// 'unminified' version for debugging.
				'dev' => array(
					'files' => array(
						'js'  => array(
							'dist/js/bootstrap.js' => array(
								'zone' => 2,
								'type' => 'footer',
							),
						),
						'css' => array(
							'dist/css/bootstrap.css' => array(
								'zone' => 2,
							),
						),
					),
				),
			),
			'library_path'      => '{e_WEB}lib/bootstrap',
			'path'              => '3.3.7',
		);

		// Bootstrap Editable (CDN).
		$libraries['cdn.bootstrap.editable'] = array(
			'name'              => 'Bootstrap Editable (CDN)',
			'vendor_url'        => 'https://vitalets.github.io/bootstrap-editable/',
			'version_arguments' => array(
				'file'    => 'js/bootstrap-editable.min.js',
				'pattern' => '/v(\d\.\d\.\d+)/',
				'lines'   => 5,
			),
			'files'             => array(
				'js'  => array(
					'js/bootstrap-editable.min.js' => array(
						'zone' => 2,
						'type' => 'footer',
					),
				),
				'css' => array(
					'css/bootstrap-editable.min.css' => array(
						'zone' => 2,
					),
				),
			),
			'variants'          => array(
				// 'unminified' version for debugging.
				'dev' => array(
					'files' => array(
						'js'  => array(
							'js/bootstrap-editable.js' => array(
								'zone' => 2,
								'type' => 'footer',
							),
						),
						'css' => array(
							'css/bootstrap-editable.css' => array(
								'zone' => 2,
							),
						),
					),
				),
			),
			// Override library path to CDN.
			'library_path'      => 'https://cdn.jsdelivr.net/bootstrap.editable',
			'path'              => '1.5.1',
		);

		// Bootstrap Editable (local).
		$libraries['bootstrap.editable'] = array(
			'name'              => 'Bootstrap Editable (local)',
			'vendor_url'        => 'https://vitalets.github.io/bootstrap-editable/',
			'version_arguments' => array(
				'file'    => 'js/bootstrap-editable.min.js',
				'pattern' => '/v(\d\.\d\.\d+)/',
				'lines'   => 5,
			),
			'files'             => array(
				'js'  => array(
					'js/bootstrap-editable.min.js' => array(
						'zone' => 2,
						'type' => 'footer',
					),
				),
				'css' => array(
					'css/bootstrap-editable.min.css' => array(
						'zone' => 2,
					),
				),
			),
			'variants'          => array(
				// 'unminified' version for debugging.
				'dev' => array(
					'files' => array(
						'js'  => array(
							'js/bootstrap-editable.js' => array(
								'zone' => 2,
								'type' => 'footer',
							),
						),
						'css' => array(
							'css/bootstrap-editable.css' => array(
								'zone' => 2,
							),
						),
					),
				),
			),
			// Override library path.
			'library_path'      => '{e_WEB}js/bootstrap3-editable',
		);

		// Bootstrap Switch (CDN).
		$libraries['cdn.bootstrap.switch'] = array(
			'name'              => 'Bootstrap Switch (CDN)',
			'vendor_url'        => 'http://www.bootstrap-switch.org',
			'version_arguments' => array(
				'file'    => 'js/bootstrap-switch.min.js',
				'pattern' => '/v(\d\.\d\.\d)/',
				'lines'   => 5,
			),
			'files'             => array(
				'js'  => array(
					'js/bootstrap-switch.min.js' => array(
						'zone' => 2,
						'type' => 'footer',
					),
				),
				'css' => array(
					'css/bootstrap3/bootstrap-switch.min.css' => array(
						'zone' => 2,
					),
				),
			),
			'variants'          => array(
				// 'unminified' version for debugging.
				'dev' => array(
					'files' => array(
						'js'  => array(
							'js/bootstrap-switch.js' => array(
								'zone' => 2,
								'type' => 'footer',
							),
						),
						'css' => array(
							'css/bootstrap3/bootstrap-switch.css' => array(
								'zone' => 2,
							),
						),
					),
				),
			),
			// Override library path to CDN.
			'library_path'      => 'https://cdn.jsdelivr.net/bootstrap.switch',
			'path'              => '3.3.2',
		);

		// Bootstrap Switch (local).
		$libraries['bootstrap.switch'] = array(
			'name'              => 'Bootstrap Switch (local)',
			'vendor_url'        => 'http://www.bootstrap-switch.org',
			'version_arguments' => array(
				'file'    => 'dist/js/bootstrap-switch.min.js',
				'pattern' => '/v(\d\.\d\.\d)/',
				'lines'   => 5,
			),
			'files'             => array(
				'js'  => array(
					'dist/js/bootstrap-switch.min.js' => array(
						'zone' => 2,
						'type' => 'footer',
					),
				),
				'css' => array(
					'dist/css/bootstrap3/bootstrap-switch.min.css' => array(
						'zone' => 2,
					),
				),
			),
			'variants'          => array(
				// 'unminified' version for debugging.
				'dev' => array(
					'files' => array(
						'js'  => array(
							'dist/js/bootstrap-switch.js' => array(
								'zone' => 2,
								'type' => 'footer',
							),
						),
						'css' => array(
							'dist/css/bootstrap3/bootstrap-switch.css' => array(
								'zone' => 2,
							),
						),
					),
				),
			),
			// Override library path.
			'library_path'      => '{e_WEB}lib/bootstrap-switch',
		);

		// Font-Awesome (CDN).
		$libraries['cdn.fontawesome'] = array(
			'name'              => 'Font-Awesome (CDN)',
			'vendor_url'        => 'http://fontawesome.io/',
			'version_arguments' => array(
				'file'    => 'css/font-awesome.min.css',
				'pattern' => '/(\d\.\d\.\d+)/',
				'lines'   => 10,
			),
			'files'             => array(
				'css' => array(
					'css/font-awesome.min.css' => array(
						'zone' => 2,
					),
				),
			),
			'variants'          => array(
				// 'unminified' version for debugging.
				'dev' => array(
					'files' => array(
						'css' => array(
							'css/font-awesome.css' => array(
								'zone' => 2,
							),
						),
					),
				),
			),
			// Override library path to CDN.
			'library_path'      => 'https://cdn.jsdelivr.net/fontawesome',
			'path'              => '4.7.0',
		);

		// Font-Awesome (local).
		$libraries['fontawesome'] = array(
			'name'              => 'Font-Awesome (local)',
			'vendor_url'        => 'http://fontawesome.io/',
			'version_arguments' => array(
				'file'    => 'css/font-awesome.min.css',
				'pattern' => '/(\d\.\d\.\d+)/',
				'lines'   => 10,
			),
			'files'             => array(
				'css' => array(
					'css/font-awesome.min.css' => array(
						'zone' => 2,
					),
				),
			),
			'variants'          => array(
				// 'unminified' version for debugging.
				'dev' => array(
					'files' => array(
						'css' => array(
							'css/font-awesome.css' => array(
								'zone' => 2,
							),
						),
					),
				),
			),
			// Override library path.
			'library_path'      => '{e_WEB}lib/font-awesome',
			'path'              => '4.7.0',
		);


			// Font-Awesome (local).
		$libraries['animate.css'] = array(
			'name'              => 'Animate.css (local)',
			'vendor_url'        => 'https://daneden.github.io/animate.css/',
			'version_arguments' => array(
				'file'    => 'animate.min.css',
				'pattern' => '/(\d\.\d\.\d+)/',
				'lines'   => 5,
			),
			'files'             => array(
				'css' => array(
					'animate.min.css' => array(
						'zone' => 2,
					),
				),
			),
		/*	'variants'          => array(
				// 'unminified' version for debugging.
				'dev' => array(
					'files' => array(
						'css' => array(
							'css/font-awesome.css' => array(
								'zone' => 2,
							),
						),
					),
				),
			),*/
			// Override library path.
			'library_path'      => '{e_WEB}lib/animate.css',
		//	'path'              => '3.5.2',
		);





		return $libraries;
	}

	/**
	 * Alters library information before detection and caching takes place.
	 */
	function config_alter(&$libraries)
	{
		$pref = e107::pref('core');
		$cdnProvider = varset($pref['e_jslib_cdn_provider'], 'jsdelivr');

		// If CDNJS is the selected provider, we alter core CDN libraries to use it
		// instead of jsDelivr.
		if($cdnProvider == 'cdnjs')
		{
			$libraries['cdn.jquery']['library_path'] = str_replace('https://cdn.jsdelivr.net/jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery', $libraries['cdn.jquery']['library_path']);
			$libraries['cdn.jquery.once']['library_path'] = str_replace('https://cdn.jsdelivr.net/jquery.once', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-once', $libraries['cdn.jquery.once']['library_path']);
			$libraries['cdn.jquery.ui']['library_path'] = str_replace('https://cdn.jsdelivr.net/jquery.ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui', $libraries['cdn.jquery.ui']['library_path']);
			$libraries['cdn.bootstrap']['library_path'] = str_replace('https://cdn.jsdelivr.net/bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap', $libraries['cdn.bootstrap']['library_path']);

			$libraries['cdn.bootstrap.editable']['library_path'] = str_replace('https://cdn.jsdelivr.net/bootstrap.editable', 'https://cdnjs.cloudflare.com/ajax/libs/x-editable', $libraries['cdn.bootstrap.editable']['library_path']);
			$libraries['cdn.bootstrap.editable']['path'] .= '/bootstrap-editable';

			$libraries['cdn.bootstrap.switch']['library_path'] = str_replace('https://cdn.jsdelivr.net/bootstrap.switch', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch', $libraries['cdn.bootstrap.switch']['library_path']);
			$libraries['cdn.fontawesome']['library_path'] = str_replace('https://cdn.jsdelivr.net/fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome', $libraries['cdn.fontawesome']['library_path']);
		}
	}

}


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
			$library['library_path'] = $this->detectPath($library['machine_name']);
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
					if(is_readable(e_THEME . $library['theme'] . '/theme_library.php'))
					{
						e107_require_once(e_THEME . $library['theme'] . '/theme_library.php');
						$addonClass = 'theme_library';

						if(class_exists($addonClass))
						{
							$class = new $addonClass();
						}
					}

					// e107::getAddon() does not support theme folders.
					if(is_readable(e_THEME . $library['theme'] . '/admin_theme_library.php'))
					{
						e107_require_once(e_THEME . $library['theme'] . '/admin_theme_library.php');
						$addonClass = 'admin_theme_library';

						if(class_exists($addonClass))
						{
							$class = new $addonClass();
						}
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
						if(is_readable(e_THEME . $library['theme'] . '/theme_library.php'))
						{
							e107_require_once(e_THEME . $library['theme'] . '/theme_library.php');
							$addonClass = 'theme_library';

							if(class_exists($addonClass))
							{
								$class = new $addonClass();
							}
						}

						// e107::getAddon() does not support theme folders.
						if(is_readable(e_THEME . $library['theme'] . '/admin_theme_library.php'))
						{
							e107_require_once(e_THEME . $library['theme'] . '/admin_theme_library.php');
							$addonClass = 'admin_theme_library';

							if(class_exists($addonClass))
							{
								$class = new $addonClass();
							}
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
			$cache_context = (defset('e_ADMIN_AREA', false) == true) ? 'AdminArea' : 'UserArea';
			$cacheID = 'Library_' . $cache_context . '_' . e107::getParser()->filter($name, 'file');
			$cached = $cache->retrieve($cacheID, false, true, true);

			if($cached)
			{
				$library = e107::unserialize($cached);
			}

			if(!varset($library, false))
			{
				$library = $this->detect($name);
				$cacheData = e107::serialize($library, 'json');
				$cache->set($cacheID, $cacheData, true, true, true);
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
	private function detectPath($name)
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
	 * Returns with the selected property of a library.
	 *
	 * @param string $library
	 *  Library machine name. For example: bootstrap
	 *
	 * @param string $property
	 *  The property name. For example: library_path
	 *
	 * @return mixed
	 */
	public function getProperty($library, $property)
	{
		$lib = self::info($library);
		return varset($lib[$property], false);
	}


	/**
	 * Return full path to a library in different formats.
	 * @param string $library
	 * The library name eg. bootstrap
	 *
	 * @param null $mode
	 * The mode: null | 'full' | 'abs'
	 *
	 * @return string
	 */
	public function getPath($library, $mode=null)
	{
		$path = self::getProperty($library, 'library_path').'/'. self::getProperty($library, 'path');
		return e107::getParser()->replaceConstants($path,$mode).'/';
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

			$coreLibrary = new core_library();
			$info = $coreLibrary->config();
			if(is_array($info))
			{
				foreach($info as $machine_name => $properties)
				{
					$properties['info_type'] = 'core';
					$libraries[$machine_name] = $properties;
				}
			}

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

			$themes[] = array(
				'name'  => e107::getPref('sitetheme'),
				'file'  => 'theme_library',
				'class' => 'theme_library',
			);

			$themes[] = array(
				'name'  => e107::getPref('admintheme'),
				'file'  => 'admin_theme_library',
				'class' => 'admin_theme_library',
			);

			foreach($themes as $theme)
			{
				if(is_readable(e_THEME . $theme['name'] . '/' . $theme['file'] . '.php'))
				{
					e107_require_once(e_THEME . $theme['name'] . '/' . $theme['file'] . '.php');

					$info = e107::callMethod($theme['class'], 'config');
					if(is_array($info))
					{
						foreach($info as $machine_name => $properties)
						{
							$properties['info_type'] = 'theme';
							$properties['theme'] = $theme['name'];
							$libraries[$machine_name] = $properties;
						}
					}
				}
			}

			// Provide defaults.
			foreach($libraries as $machine_name => &$properties)
			{
				$this->infoDefaults($properties, $machine_name);
			}

			// Alter config array. For example, change CDN provider.
			$coreLibrary->config_alter($libraries);

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

			// Allow enabled themes to alter the registered libraries.
			foreach($themes as $theme)
			{
				if(is_readable(e_THEME . $theme['name'] . '/' . $theme['file'] . '.php'))
				{
					e107_require_once(e_THEME . $theme['name'] . '/' . $theme['file'] . '.php');

					if(class_exists($theme['class']))
					{
						$class = new $theme['class']();
						if(method_exists($class, 'config_alter'))
						{
							// We cannot use e107::callMethod() because need to pass variable by reference.
							$class->config_alter($libraries);
						}
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
			'pre_detect'            => array('preDetect'),
			'post_detect'           => array(),
			'pre_dependencies_load' => array(),
			'pre_load'              => array('preLoad'),
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
			$this->{$callback}($library, null, null);

			// Apply the callback to versions.
			if(isset($library['versions']))
			{
				foreach($library['versions'] as $version_string => &$version)
				{
					$this->{$callback}($version, $version_string, null);

					// Versions can include variants as well.
					if(isset($version['variants']))
					{
						foreach($version['variants'] as $version_variant_name => &$version_variant)
						{
							$this->{$callback}($version_variant, $version_string, $version_variant_name);
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
		if(!empty($library['post_load_integration_files']) && !$library['post_load_integration_files'] && !empty($library['integration_files']))
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
		$path = ($library['path'] !== '' ? rtrim($path, '/') . '/' . $library['path'] : $path);
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
						e107::getJs()->libraryCSS($data); // load before others.
					//	e107::css($options['type'], $data, null);
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
		if(!empty($library['post_load_integration_files']) && $library['post_load_integration_files'] && !empty($library['integration_files']))
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
		$libraryPath = ($library['path'] !== '' ? rtrim($libraryPath, '/') . '/' . $library['path'] : $libraryPath);
		$libraryPath = rtrim($libraryPath, '/');

		$file = $libraryPath . '/' . $options['file'];

		if(empty($options['file']))
		{
			return;
		}

		// If remote file (e.g. CDN URL)... we download file to temp, and get version number.
		// The library will be cached with version number, so this only run once per library.
		if(substr($file, 0, 4) == 'http')
		{
			$content = e107::getFile()->getRemoteContent($file);
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

	/**
	 * Alters library information before detecting.
	 */
	private function preDetect(&$library)
	{
		if(empty($library['machine_name']))
		{
			return;
		}

		// Prevent plugins/themes from altering libraries on Admin UI.
		if(defset('e_ADMIN_AREA', false) == true)
		{
			$coreLibrary = new core_library();
			$coreLibs = $coreLibrary->config();

			if (isset($coreLibs[$library['machine_name']])) {
				$coreLib = $coreLibs[$library['machine_name']];
				$library = array_replace_recursive($coreLib, array_replace_recursive($library, $coreLib));
			}
		}
	}

	/**
	 * Alters library information before loading.
	 */
	private function preLoad(&$library)
	{
		if(empty($library['machine_name']))
		{
			return;
		}

		$excluded = $this->getExcludedLibraries();

		if(empty($excluded))
		{
			return;
		}

		// Make sure we have the name without cdn prefix.
		$basename = str_replace('cdn.', '', $library['machine_name']);

		// If this library (or the CDN version of this library) is excluded
		// by the theme is currently used.
		if (in_array($basename, $excluded) || in_array('cdn.' . $basename, $excluded))
		{
			unset($library['files']['css']);

			if (!empty($library['variants']))
			{
				foreach($library['variants'] as &$variant)
				{
					if(!empty($variant['files']['css']))
					{
						unset($variant['files']['css']);
					}
				}
			}
		}
	}

	/**
	 * Get excluded libraries.
	 *
	 * @return array
	 */
	public function getExcludedLibraries()
	{
		// This static cache is re-used by preLoad() to save memory.
		static $excludedLibraries;

		if(!isset($excludedLibraries))
		{
			$excludedLibraries = array();

			$exclude = e107::getTheme('current', true)->cssAttribute('auto', 'exclude');

			if($exclude)
			{
				// Split string into array and remove whitespaces.
				$excludedLibraries = array_map('trim', explode(',', $exclude));
			}
		}

		return $excludedLibraries;
	}

}
