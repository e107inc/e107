<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once("OsHelper.php");
require_once("CoreImage.php");

class JsonCoreImage extends CoreImage
{
    protected $checksums = [];

    public function __construct($exportFolder, $tempFolder, $currentVersion, $imageFile)
    {
        $this->create_image($exportFolder, $tempFolder, $currentVersion);

        $this->saveImage($imageFile);
    }

    /**
     * @param $imageFile
     */
    protected function saveImage($imageFile)
    {
        $json_result = json_encode($this->checksums, JSON_PRETTY_PRINT);
        $json_string_result = var_export($json_result, true);
        $data = $this->generateStub();
        $data .= '$core_image = ' . $json_string_result . ';';

        $fp = fopen($imageFile, 'w');
        fwrite($fp, $data);
    }

    protected function generateCurrentChecksums($exportFolder, $currentVersion)
    {
        parent::generateCurrentChecksums($exportFolder, $currentVersion);
        ksort($this->checksums);
    }

    protected function generateRemovedChecksums($tempFolder)
    {
        parent::generateRemovedChecksums($tempFolder);
        ksort($this->checksums);
    }

    /**
     * @inheritDoc
     */
    protected function insertChecksumIntoDatabase(&$relativePath, &$checksum, &$version)
    {
        $item = self::array_get($this->checksums, $relativePath, []);
        if (!in_array($checksum, $item)) $item["v{$version}"] = $checksum;
        self::array_set($this->checksums, $relativePath, $item);
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

        foreach (explode('/', $key) as $segment)
        {
            if (!is_array($array) || !array_key_exists($segment, $array))
            {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
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
}