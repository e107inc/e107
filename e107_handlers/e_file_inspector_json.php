<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

require_once("e_file_inspector.php");

class e_file_inspector_json extends e_file_inspector
{
	private $coreImage;

	public function __construct()
	{
		global $core_image;
		require(e_ADMIN . "core_image.php");
		$this->coreImage = json_decode($core_image, true);
		unset($core_image);
	}

	/**
	 * @inheritDoc
	 */
	public function getChecksums($path)
	{
		$path = $this->pathToDefaultPath($path);
		return self::array_get($this->coreImage, $path, []);
	}

	/**
	 * @inheritDoc
	 */
	public function getVersions($path)
	{
		$path = $this->pathToDefaultPath($path);
		return array_keys(self::array_get($this->coreImage, $path, []));
	}

	/**
	 * @inheritDoc
	 */
	public function isInsecure($path)
	{
		# TODO
		return false;
	}

	/**
	 * Get an item from an array using "slash" notation.
	 *
	 * Based on Illuminate\Support\Arr::get()
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 * @copyright Copyright (c) Taylor Otwell
	 * @license https://github.com/illuminate/support/blob/master/LICENSE.md MIT License
	 */
	private static function array_get($array, $key, $default = null)
	{
		if (is_null($key)) return $array;

		if (isset($array[$key])) return $array[$key];

		foreach (explode('/', $key) as $segment) {
			if (!is_array($array) || !array_key_exists($segment, $array)) {
				return $default;
			}

			$array = $array[$segment];
		}

		return $array;
	}

	private function pathToDefaultPath($path)
	{
		$defaultDirs = e107::getInstance()->defaultDirs();
		foreach ($defaultDirs as $dirType => $defaultDir) {
			$customDir = e107::getFolder(preg_replace("/_DIRECTORY$/i", "", $dirType));
			$path = preg_replace("/^" . preg_quote($customDir, "/") . "/", $defaultDir, $path);
		}
		return $path;
	}
}