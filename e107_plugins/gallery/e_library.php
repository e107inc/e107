<?php

/**
 * @file
 * Provides information about external libraries.
 */


/**
 * Class PLUGIN_library.
 */
class gallery_library
{

	/**
	 * Return information about external libraries.
	 *
	 * @return
	 *   An associative array whose keys are internal names of libraries and whose values are describing each library.
	 *   Each key is the directory name below the '{e_WEB}/lib' directory, in which the library may be found. Each
	 *   value is an associative array containing:
	 *   - name: The official, human-readable name of the library.
	 *   - vendor_url: The URL of the homepage of the library.
	 *   - download_url: The URL of a web page on which the library can be obtained.
	 *   - path: (optional) A relative path from the directory of the library to the actual library. Only required if
	 *     the extracted download package contains the actual library files in a sub-directory.
	 *   - library_path: (optional) The absolute path to the library directory. This should not be declared normally, as
	 *     it is automatically detected, to allow for multiple possible library locations. A valid use-case is an
	 *     external library, in which case the full URL to the library should be specified here.
	 *   - version: (optional) The version of the library. This should not be declared normally, as it is automatically
	 *     detected (see 'version_callback' below) to allow for version changes of libraries without code changes of
	 *     implementing plugins and to support different versions of a library simultaneously. A valid use-case is an
	 *     external library whose version cannot be determined programmatically. Either 'version' or 'version_callback'
	 *     (or 'version_arguments' in case libraryGetVersion() is being used as a version callback) must be declared.
	 *   - version_callback: (optional) The name of a function that detects and returns the full version string of the
	 *     library. The first argument is always $library, an array containing all library information as described here.
	 *     There are two ways to declare the version callback's additional arguments, either as a single $options
	 *     parameter or as multiple parameters, which correspond to the two ways to specify the argument values (see
	 *     'version_arguments'). Defaults to libraryGetVersion(). Unless 'version' is declared or libraryGetVersion()
	 *     is being used as a version callback, 'version_callback' must be declared. In the latter case, however,
	 *     'version_arguments' must be declared in the specified way.
	 *   - version_arguments: (optional) A list of arguments to pass to the version callback. Version arguments can be
	 *     declared either as an associative array whose keys are the argument names or as an indexed array without
	 *     specifying keys. If declared as an associative array, the arguments get passed to the version callback as a
	 *     single $options parameter whose keys are the argument names (i.e. $options is identical to the specified
	 *     array). If declared as an indexed array, the array values get passed to the version callback as separate
	 *     arguments in the order they were declared. The default version callback libraryGetVersion() expects a
	 *     single, associative array with named keys:
	 *     - file: The filename to parse for the version, relative to the path specified as the 'library_path' property
	 *       (see above). For example: 'docs/changelog.txt'.
	 *     - pattern: A string containing a regular expression (PCRE) to match the library version. For example:
	 *       '@version\s+([0-9a-zA-Z\.-]+)@'. Note that the returned version is not the match of the entire pattern
	 *       (i.e. '@version 1.2.3' in the above example) but the match of the first sub-pattern (i.e. '1.2.3' in the
	 *       above example).
	 *     - lines: (optional) The maximum number of lines to search the pattern in. Defaults to 20.
	 *     - cols: (optional) The maximum number of characters per line to take into account. Defaults to 200. In case
	 *       of minified or compressed files, this prevents reading the entire file into memory.
	 *     Defaults to an empty array. 'version_arguments' must be specified unless 'version' is declared or the
	 *     specified 'version_callback' does not require any arguments. The latter might be the case with a
	 *     library-specific version callback, for example.
	 *   - files: An associative array of library files to load. Supported keys are:
	 *     - js: A list of JavaScript files to load.
	 *     - css: A list of CSS files to load.
	 *     - php: A list of PHP files to load.
	 *   - dependencies: An array of libraries this library depends on. Similar to declaring plugin dependencies, the
	 *     dependency declaration may contain information on the supported version. Examples of supported declarations:
	 * @code
	 *     $library['dependencies'] = array(
	 *       // Load the 'example' library, regardless of the version available:
	 *       'example',
	 *       // Only load the 'example' library, if version 1.2 is available:
	 *       'example (1.2)',
	 *       // Only load a version later than 1.3-beta2 of the 'example' library:
	 *       'example (>1.3-beta2)'
	 *       // Only load a version equal to or later than 1.3-beta3:
	 *       'example (>=1.3-beta3)',
	 *       // Only load a version earlier than 1.5:
	 *       'example (<1.5)',
	 *       // Only load a version equal to or earlier than 1.4:
	 *       'example (<=1.4)',
	 *       // Combinations of the above are allowed as well:
	 *       'example (>=1.3-beta2, <1.5)',
	 *     );
	 * @endcode
	 *   - variants: (optional) An associative array of available library variants. For example, the top-level 'files'
	 *     property may refer to a default variant that is compressed. If the library also ships with a minified and
	 *     uncompressed/source variant, those can be defined here. Each key should describe the variant type, e.g.
	 *     'minified' or 'source'. Each value is an associative array of top-level properties that are entirely
	 *     overridden by the variant, most often just 'files'. Additionally, each variant can contain following
	 *     properties:
	 *     - variant_callback: (optional) The name of a function that detects the variant and returns TRUE or FALSE,
	 *       depending on whether the variant is available or not. The first argument is always $library, an array
	 *       containing all library information as described here. The second argument is always a string containing the
	 *       variant name. There are two ways to declare the variant callback's additional arguments, either as a single
	 *       $options parameter or as multiple parameters, which correspond to the two ways to specify the argument
	 *       values (see 'variant_arguments'). If omitted, the variant is expected to always be available.
	 *     - variant_arguments: A list of arguments to pass to the variant callback. Variant arguments can be declared
	 *       either as an associative array whose keys are the argument names or as an indexed array without specifying
	 *       keys. If declared as an associative array, the arguments get passed to the variant callback as a single
	 *       $options parameter whose keys are the argument names (i.e. $options is identical to the specified array).
	 *       If declared as an indexed array, the array values get passed to the variant callback as separate arguments
	 *       in the order they were declared.
	 *     Variants can be version-specific (see 'versions').
	 *   - versions: (optional) An associative array of supported library versions. Naturally, libraries evolve over
	 *     time and so do their APIs. In case a library changes between versions, different 'files' may need to be
	 *     loaded, different 'variants' may become available, or e107 plugins need to load different integration files
	 *     adapted to the new version. Each key is a version *string* (PHP does not support floats as keys). Each value
	 *     is an associative array of top-level properties that are entirely overridden by the version.
	 *   - integration_files: (optional) Sets of files to load for the plugin, using the same notion as the top-level
	 *     'files' property. Each specified file should contain the path to the file relative to the plugin it belongs
	 *     to.
	 *   Additional top-level properties can be registered as needed.
	 */
	function config()
	{
		$libraries['jquery.prettyPhoto'] = array(
			// Only used in administrative UI of Libraries API.
			'name'              => 'prettyPhoto',
			'vendor_url'        => 'http://www.no-margin-for-errors.com',
			'download_url'      => 'https://github.com/scaron/prettyphoto',
			'version_arguments' => array(
				'file'    => 'js/jquery.prettyPhoto.js',
				// Version: 3.1.6
				'pattern' => '/Version: (\d+\.+\d+\.+\d+)/',
				'lines'   => 5,
			),
			'files'             => array(
				'js'  => array(
					'js/jquery.prettyPhoto.js' => array(
						'type' => 'footer',
					),
				),
				'css' => array(
					'css/prettyPhoto.css',
				),
			),
		);

		$libraries['jquery.cycle'] = array(
			// Only used in administrative UI of Libraries API.
			'name'              => 'jQuery Cycle Plugin',
			'vendor_url'        => 'http://jquery.malsup.com/cycle/',
			'download_url'      => 'http://jquery.malsup.com/cycle/',
			'version_arguments' => array(
				'file'    => 'jquery.cycle.all.js',
				// Version: 2.9999.5
				'pattern' => '/Version: (\d+\.+\d+\.+\d+)/',
				'lines'   => 5,
			),
			'files'             => array(
				'js' => array(
					'jquery.cycle.all.js' => array(
						'type' => 'footer',
					),
				),
			),
		);

		return $libraries;
	}

}
