<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

class coreImage
{
	public function __construct($_current, $_deprecated, $_image)
	{
		global $coredir;
		set_time_limit(240);

		define("IMAGE_CURRENT", $_current);
		define("IMAGE_DEPRECATED", $_deprecated);
		define("IMAGE_IMAGE", $_image);

		$maindirs = array(
			'admin' => 'e107_admin/',
			'files' => 'e107_files/',
			'images' => 'e107_images/',
			'themes' => 'e107_themes/',
			'plugins' => 'e107_plugins/',
			'handlers' => 'e107_handlers/',
			'languages' => 'e107_languages/',
			'downloads' => 'e107_files/downloads/',
			'docs' => 'e107_docs/'
		);

		foreach ($maindirs as $maindirs_key => $maindirs_value)
		{
			$coredir[$maindirs_key] = substr($maindirs_value, 0, -1);
		}

		$this->create_image(IMAGE_CURRENT, IMAGE_DEPRECATED);
	}

	function create_image($_curdir, $_depdir)
	{
		global $coredir;

		$search = $replace = [];
		foreach ($coredir as $trim_key => $trim_dirs)
		{
			$search[$trim_key] = "'" . $trim_dirs . "'";
			$replace[$trim_key] = "\$coredir['" . $trim_key . "']";
		}

		$data = "<?php\n";
		$data .= "/*\n";
		$data .= "+ ----------------------------------------------------------------------------+\n";
		$data .= "|     e107 website system\n";
		$data .= "|\n";
		$data .= "|     Copyright (C) 2008-" . date("Y") . " e107 Inc. \n";
		$data .= "|     http://e107.org\n";
		//	$data .= "|     jalist@e107.org\n";
		$data .= "|\n";
		$data .= "|     Released under the terms and conditions of the\n";
		$data .= "|     GNU General Public License (http://gnu.org).\n";
		$data .= "|\n";
		$data .= "|     \$URL$\n";
		$data .= "|     \$Id$\n";
		$data .= "+----------------------------------------------------------------------------+\n";
		$data .= "*/\n\n";
		$data .= "if (!defined('e107_INIT')) { exit; }\n\n";

		$scan_current = $this->scan($_curdir);


		echo("[Core-Image] Scanning Dir: " . $_curdir . "\n");


		$image_array = var_export($scan_current, true);
		$image_array = str_replace($search, $replace, $image_array);
		$data .= "\$core_image = " . $image_array . ";\n\n";

		$scan_deprecated = $this->scan($_depdir, $scan_current);
		$image_array = var_export($scan_deprecated, true);
		$image_array = str_replace($search, $replace, $image_array);
		$data .= "\$deprecated_image = " . $image_array . ";\n\n";
		$data .= "?>";

		$fp = fopen(IMAGE_IMAGE, 'w');
		fwrite($fp, $data);
	}

	function scan($dir, $image = array())
	{
		$absoluteBase = realpath($dir);
		if (!is_dir($absoluteBase)) return false;

		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
		$files = [];

		/**
		 * @var $file DirectoryIterator
		 */
		foreach ($iterator as $file)
		{
			if ($file->isDir()) continue;

			$absolutePath = $file->getRealPath();
			$relativePath = preg_replace("/^" . preg_quote($absoluteBase . "/", "/") . "/", "", $absolutePath);

			if (empty($relativePath) || $relativePath == $absolutePath) continue;

			self::array_set($files, $relativePath, $this->checksum($absolutePath));
		}

		ksort($files);

		return $files;
	}

	/**
	 * Set an array item to a given value using "slash" notation.
	 *
	 * If no key is given to the method, the entire array will be replaced.
	 *
	 * Based on Illuminate\Support\Arr::set()
	 *
	 * @param array $array
	 * @param string|null $key
	 * @param mixed $value
	 * @return array
	 * @copyright Copyright (c) Taylor Otwell
	 * @license https://github.com/illuminate/support/blob/master/LICENSE.md MIT License
	 */
	private static function array_set(&$array, $key, $value)
	{
		if (is_null($key))
		{
			return $array = $value;
		}

		$keys = explode('/', $key);

		while (count($keys) > 1)
		{
			$key = array_shift($keys);

			// If the key doesn't exist at this depth, we will just create an empty array
			// to hold the next value, allowing us to create the arrays to hold final
			// values at the correct depth. Then we'll keep digging into the array.
			if (!isset($array[$key]) || !is_array($array[$key]))
			{
				$array[$key] = [];
			}

			$array = &$array[$key];
		}

		$array[array_shift($keys)] = $value;

		return $array;
	}

	function checksum($filename)
	{
		return md5(str_replace(array(chr(13), chr(10)), '', file_get_contents($filename)));
	}
}