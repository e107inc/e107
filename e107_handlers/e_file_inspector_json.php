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
    protected $coreImage;

    /**
     * @param $jsonFilePath string Absolute path to the file inspector database
     */
    public function __construct($jsonFilePath = null)
    {
        parent::__construct($jsonFilePath);
    }

    /**
     * @inheritDoc
     */
    public function loadDatabase()
    {
        global $core_image;
        @include($this->database);
        $this->coreImage = json_decode($core_image, true);
        if (!is_array($this->coreImage)) $this->coreImage = [];
        $this->coreImage = self::array_slash($this->coreImage);
        if (!$this->coreImage) $this->coreImage = [];
        unset($core_image);
    }

    /**
     * @inheritDoc
     */
    public function getPathIterator($version = null)
    {
        $result = $this->coreImage ? $this->coreImage : [];
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
        $path = $this->customPathToDefaultPath($path);
        return isset($this->coreImage[$path]) ? $this->coreImage[$path] : [];
    }

    /**
     * Flatten a multi-dimensional associative array with slashes.
     * Excludes the second-to-last level of depth from flattening.
     * Also removes the leading "v" from all version keys.
     *
     * Based on Illuminate\Support\Arr::dot()
     *
     * @param array $array
     * @param string $prepend
     * @return array
     * @copyright Copyright (c) Taylor Otwell
     * @license https://github.com/illuminate/support/blob/master/LICENSE.md MIT License
     */
    protected static function array_slash($array, $prepend = '')
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
                foreach ($value as $versionWithV => $checksum)
                {
                    $value[ltrim($versionWithV, 'v')] = $checksum;
                    unset($value[$versionWithV]);
                }
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }
}