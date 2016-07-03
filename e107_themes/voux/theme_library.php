<?php

/**
 * @file
 * Provides information about external libraries.
 */


/**
 * Class voux_library.
 */
class voux_library
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

}
