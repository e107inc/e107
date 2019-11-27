<?php

/**
 * @file
 * Documents API functions for Library Manager.
 * Usage examples.
 */


/**
 * Class PLUGIN_library.
 */
class _blank_library
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
		// The following is a full explanation of all properties. See below for more concrete example implementations.
		// This array key lets Library Manager search for 'e107_web/lib/example' directory, which should contain the
		// entire, original extracted library.
		$libraries['example'] = array(
			// Only used in administrative UI of Libraries API.
			'name'              => 'Example library',
			'vendor_url'        => 'http://example.com',
			'download_url'      => 'http://example.com/download',
			// Override default library location ({e_WEB}/lib).
			'library_path'      => e_PLUGIN . 'example',
			// Optional: If, after extraction, the actual library files are contained in 'e107_web/lib/example/lib',
			// specify the relative path here.
			'path'              => 'lib',
			// Optional: Define a custom version detection callback, if required. Need to be in your 'PLUGIN_library'
			// class.
			'version_callback'  => 'example_custom_version_callback',
			// Specify arguments for the version callback.
			// By default, libraryGetVersion() takes a named argument array:
			'version_arguments' => array(
				'file'    => 'docs/CHANGELOG.txt',
				'pattern' => '@version\s+([0-9a-zA-Z\.-]+)@',
				'lines'   => 5,
				'cols'    => 20,
			),
			// Default list of files of the library to load. Important: Only specify third-party files belonging to the
			// library here, not integration files of your plugin.
			'files'             => array(
				// 'js' and 'css' file paths are relative to the library_path.
				'js'  => array(
					'exlib.js'       => array(
						'zone' => 3, // If not set, the default: 2. See: e107::js()
					),
					'gadgets/foo.js' => array(
						'type' => 'footer', // If not set, the default: 'url'. See: e107::js()
						'zone' => 5, // If not set, the default: 2. See: e107::js()
					),
				),
				'css' => array(
					'lib_style.css',
					'skin/example.css',
				),
				// For PHP libraries, specify include files here, still relative to the library_path.
				'php' => array(
					'exlib.php',
					'exlib.inc',
				),
			),
			// Optional: Specify alternative variants of the library, if available.
			'variants'          => array(
				// All properties defined for 'minified' override top-level properties.
				'minified' => array(
					'files'             => array(
						'js'  => array(
							'exlib.min.js',
							'gadgets/foo.min.js',
						),
						'css' => array(
							'lib_style.css',
							'skin/example.css',
						),
					),
					// Your variant callback needs to be in your 'PLUGIN_library' class.
					'variant_callback'  => 'example_custom_variant_callback',
					'variant_arguments' => array(
						'variant' => 'minified',
					),
				),
			),
			// Optional, but usually required: Override top-level properties for later versions of the library. The
			// properties of the minimum version that is matched override the top-level properties.
			//
			// Note:
			// - When registering 'versions', it usually does not make sense to register 'files', 'variants', and
			// 'integration_files' on the top-level, as most of those likely need to be different per version and there
			// are no defaults.
			// - The array keys have to be strings, as PHP does not support floats for array keys.
			'versions'          => array(
				'2'   => array(
					'files' => array(
						'js'  => array('exlib.js'),
						'css' => array('exlib_style.css'),
					),
				),
				'3.0' => array(
					'files' => array(
						'js'  => array('exlib.js'),
						'css' => array('lib_style.css'),
					),
				),
				'3.2' => array(
					'files' => array(
						'js'  => array(
							'exlib.js',
							'gadgets/foo.js',
						),
						'css' => array(
							'lib_style.css',
							'skin/example.css',
						),
					),
				),
			),
			// Optional: Register files to auto-load for your plugin. All files must be keyed by plugin, and follow the
			// syntax of the 'files' property.
			'integration_files' => array(
				'MYPLUGIN' => array(
					'js' => array('ex_lib.inc'),
				),
			),
		);

		// A very simple library. No changing APIs (hence, no versions), no variants. Expected to be extracted into
		// 'e107_web/lib/simple'.
		$libraries['simple'] = array(
			'name'              => 'Simple library',
			'vendor_url'        => 'http://example.com/simple',
			'download_url'      => 'http://example.com/simple',
			'version_arguments' => array(
				'file'    => 'readme.txt',
				// Best practice: Document the actual version strings for later reference.
				// 1.x: Version 1.0
				'pattern' => '/Version (\d+)/',
				'lines'   => 5,
			),
			'files'             => array(
				'js'  => array(
					'simple.js',
				),
				'css' => array(
					'simple.css',
				),
			),
		);

		// A library that (naturally) evolves over time with API changes.
		$libraries['tinymce'] = array(
			'name'              => 'TinyMCE',
			'vendor_url'        => 'http://tinymce.moxiecode.com',
			'download_url'      => 'http://tinymce.moxiecode.com/download.php',
			'path'              => 'jscripts/tiny_mce',
			// The regular expression catches two parts (the major and the minor version), which libraryGetVersion()
			// doesn't allow.
			'version_callback'  => 'tinymce_get_version',
			'version_arguments' => array(
				// It can be easier to parse the first characters of a minified file instead of doing a multi-line
				// pattern matching in a source file. See 'lines' and 'cols' below.
				'file'    => 'jscripts/tiny_mce/tiny_mce.js',
				// Best practice: Document the actual version strings for later reference.
				// 2.x: this.majorVersion="2";this.minorVersion="1.3"
				// 3.x: majorVersion:'3',minorVersion:'2.0.1'
				'pattern' => '@majorVersion[=:]["\'](\d).+?minorVersion[=:]["\']([\d\.]+)@',
				'lines'   => 1,
				'cols'    => 100,
			),
			'versions'          => array(
				'2.1' => array(
					'files'             => array(
						'js' => array('tiny_mce.js'),
					),
					'variants'          => array(
						'source' => array(
							'files' => array(
								'js' => array('tiny_mce_src.js'),
							),
						),
					),
					'integration_files' => array(
						'wysiwyg' => array(
							'js'  => array('editors/js/tinymce-2.js'),
							'css' => array('editors/js/tinymce-2.css'),
						),
					),
				),
				// Definition used if 3.1 or above is detected.
				'3.1' => array(
					'files'             => array(
						'js' => array(
							'tiny_mce.js',
						),
					),
					'variants'          => array(
						// New variant leveraging jQuery. Not stable yet; therefore not the default variant.
						'jquery' => array(
							'files' => array(
								'js' => array(
									'tiny_mce_jquery.js',
								),
							),
						),
						'source' => array(
							'files' => array(
								'js' => array(
									'tiny_mce_src.js',
								),
							),
						),
					),
					'integration_files' => array(
						'wysiwyg' => array(
							'js'  => array('editors/js/tinymce-3.js'),
							'css' => array('editors/js/tinymce-3.css'),
						),
					),
				),
			),
		);

		// Example for Facebook PHP SDK v4.
		$libraries['facebook-php-sdk-v4'] = array(
			'name'              => 'Facebook PHP SDK v4',
			'vendor_url'        => 'https://github.com/facebook/facebook-php-sdk-v4',
			'download_url'      => 'https://github.com/facebook/facebook-php-sdk-v4/archive/4.0.23.tar.gz',
			'version_arguments' => array(
				'file'    => 'src/Facebook/FacebookRequest.php',
				// const VERSION = '4.0.23';
				'pattern' => '/const\s+VERSION\s+=\s+\'(4\.\d\.\d+)\'/',
				'lines'   => 75,
			),
			'files'             => array(
				'php' => array(
					'autoload.php',
				),
			),
		);

		// Example for Facebook PHP SDK v5.
		$libraries['facebook-php-sdk-v5'] = array(
			'name'              => 'Facebook PHP SDK v5',
			'vendor_url'        => 'https://github.com/facebook/facebook-php-sdk-v4',
			'download_url'      => 'https://github.com/facebook/facebook-php-sdk-v4/archive/5.1.2.tar.gz',
			'version_arguments' => array(
				'file'    => 'src/Facebook/Facebook.php',
				// const VERSION = '5.1.2';
				'pattern' => '/const\s+VERSION\s+=\s+\'(5\.\d\.\d+)\'/',
				'lines'   => 100,
			),
			'files'             => array(
				'php' => array(
					'src/Facebook/autoload.php',
				),
			),
		);

		return $libraries;
	}

	/**
	 * Alter the library information before detection and caching takes place.
	 *
	 * The library definitions are passed by reference. A common use-case is adding a plugin's integration files to the
	 * library array, so that the files are loaded whenever the library is. As noted above, it is important to declare
	 * integration files inside of an array, whose key is the plugin name.
	 */
	function config_alter(&$libraries)
	{
		$files = array(
			'php' => array('example_plugin.php_spellchecker.inc'),
		);

		$libraries['php_spellchecker']['integration_files']['example_plugin'] = $files;
	}

}
