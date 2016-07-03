<?php

/**
 * @file
 * Provides information about external libraries.
 */


/**
 * Class bootstrap3_library.
 */
class bootstrap3_library
{

	/* @see https://www.cdnperf.com */
	// Warning: Some bootstrap CDNs are not compiled with popup.js.
	// Use https if e107 is using https.

	/**
	 * Returns information about external libraries.
	 */
	function config()
	{
		// Bootstrap.
		$libraries['bootstrap'] = array(
			'name'              => 'Bootstrap',
			'vendor_url'        => 'http://getbootstrap.com/',
			'variants' => array(
				'cdn' => array(
					'version_arguments' => array(
						'file'    => 'js/bootstrap.min.js',
						// Bootstrap v3.3.6
						'pattern' => '/Bootstrap\s+v(3\.\d\.\d+)/',
						'lines'   => 5,
					),
					// Override library path to CDN.
					'library_path'      => 'https://cdn.jsdelivr.net/bootstrap/3.3.6/',
					'files'             => array(
						'js'  => array(
							'js/bootstrap.min.js' => array(
								'zone' => 2,
								'type' => 'url',
							),
						),
						'css' => array(
							'css/bootstrap.min.css' => array(
								'zone' => 2,
							),
						),
					),
				),
			),
		);

		// Bootswatch.
		$libraries['bootswatch'] = array(
			'name'              => 'Bootswatch',
			'vendor_url'        => 'http://bootswatch.com/',
			'variants' => array(
				'cdn' => array(
					'version_arguments' => array(
						'file'    => 'cerulean/bootstrap.min.css',
						// bootswatch v3.3.6
						'pattern' => '/bootswatch\s+v(3\.\d\.\d+)/',
						'lines'   => 5,
					),
					// Override library path to CDN.
					'library_path'      => 'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.6/',
					'files'             => array(
						'css' => array(
							// Selected CSS file is added in config_alter() method.
							// @see theme_config.php
							// @see theme.php
						),
					),
				),
			),
		);

		// Font-Awesome.
		$libraries['fontawesome'] = array(
			'name'              => 'Font-Awesome',
			'vendor_url'        => 'http://fontawesome.io/',
			'variants' => array(
				'cdn' => array(
					'version_arguments' => array(
						'file'    => 'css/font-awesome.min.css',
						// Font Awesome 4.6.3 by
						'pattern' => '/Font\s+Awesome\s+(4\.\d\.\d+)/',
						'lines'   => 5,
					),
					// Override library path to CDN.
					'library_path'      => 'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/',
					'files'             => array(
						'css' => array(
							'css/font-awesome.min.css' => array(
								'zone' => 2,
							),
						),
					),
				),
			),
		);

		return $libraries;
	}

	/**
	 * Alter the library information before detection and caching takes place.
	 */
	function config_alter(&$libraries)
	{
		$sitetheme = e107::getPref('sitetheme');
		$bootswatch = e107::pref('theme', 'bootswatch', false);

		if($bootswatch && $sitetheme == 'bootstrap3')
		{
			// Selected Bootswatch theme.
			$cssFile = $bootswatch . '/bootstrap.min.css';
			$libraries['bootswatch']['variants']['cdn']['files']['css'][] = $cssFile;

			// Remove default bootstrap css.
			unset($libraries['bootstrap']['variants']['cdn']['files']['css']);
		}
	}

}
