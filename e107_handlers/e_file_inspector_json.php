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

    /**
     * @param $jsonFilePath string Absolute path to the file inspector database
     */
    public function __construct($jsonFilePath = null)
    {
        global $core_image;
        if ($jsonFilePath === null) $jsonFilePath = e_ADMIN . "core_image.php";
        require($jsonFilePath);
        $this->coreImage = self::array_slash(json_decode($core_image, true));
        unset($core_image);
    }

    /**
     * @inheritDoc
     */
    public function getPathIterator($version = null)
    {
        $result = $this->coreImage;
        if (!empty($version))
        {
            $result = array_filter($result, function ($value) use ($version)
            {
                return array_key_exists($version, $value);
            });
        }
        return new ArrayIterator(array_keys($result));
    }

    /**
     * @inheritDoc
     */
    public function getChecksums($path)
    {
        $path = $this->pathToDefaultPath($path);
        return isset($this->coreImage[$path]) ? $this->coreImage[$path] : [];
    }

    /**
     * Flatten a multi-dimensional associative array with slashes.
     * Excludes the second-to-last level of depth from flattening.
     *
     * Based on Illuminate\Support\Arr::dot()
     *
     * @param array $array
     * @param string $prepend
     * @return array
     * @copyright Copyright (c) Taylor Otwell
     * @license https://github.com/illuminate/support/blob/master/LICENSE.md MIT License
     */
    private static function array_slash($array, $prepend = '')
    {
        $results = array();

        foreach ($array as $key => $value)
        {
            if (is_array($value) && is_array(reset($value)))
            {
                $results = array_merge($results, self::array_slash($value, $prepend . $key . '/'));
            }
            else
            {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }
}