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

class JsonCoreImage
{
    public function __construct($exportFolder, $tempFolder, $currentVersion, $imageFile)
    {
        $this->create_image($exportFolder, $tempFolder, $currentVersion, $imageFile);
    }

    function create_image($exportFolder, $tempFolder, $currentVersion, $imageFile)
    {
        echo("[Core-Image] Scanning Dir: " . $exportFolder . "\n");
        $carry = $this->generateCurrentChecksums($exportFolder, $currentVersion);

        echo("[Core-Image] Scanning Removed Files from Git" . "\n");
        $result = $this->generateRemovedChecksums($tempFolder, $carry);

        $json_result = json_encode($result, JSON_PRETTY_PRINT);
        $json_string_result = var_export($json_result, true);
        $data = $this->generateStub();
        $data .= '$core_image = ' . $json_string_result . ';';

        $fp = fopen($imageFile, 'w');
        fwrite($fp, $data);
    }

    protected function generateCurrentChecksums($exportFolder, $currentVersion)
    {
        $absoluteBase = realpath($exportFolder);
        if (!is_dir($absoluteBase)) return false;

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($exportFolder));
        $checksums = [];

        /**
         * @var $file DirectoryIterator
         */
        foreach ($iterator as $file)
        {
            if ($file->isDir()) continue;

            $absolutePath = $file->getRealPath();
            $relativePath = preg_replace("/^" . preg_quote($absoluteBase . "/", "/") . "/", "", $absolutePath);

            if (empty($relativePath) || $relativePath == $absolutePath) continue;

            $checksum = $this->checksumPath($absolutePath);
            $item = self::array_get($checksums, $relativePath, []);
            if (!in_array($checksum, $item)) $item["v{$currentVersion}"] = $checksum;
            self::array_set($checksums, $relativePath, $item);
        }

        ksort($checksums);
        return $checksums;
    }

    protected function checksumPath($filename)
    {
        return $this->checksum(file_get_contents($filename));
    }

    protected function checksum($body)
    {
        return md5(str_replace(array(chr(13), chr(10)), '', $body));
    }

    protected function generateRemovedChecksums($tempFolder, $carry)
    {
        $checksums = $carry;

        $stdout = '';
        OsHelper::runValidated('git tag --list ' . escapeshellarg("v*"), $stdout);
        $tags = explode("\n", trim($stdout));
        $versions = [];
        foreach ($tags as $tag)
        {
            $versions[] = preg_replace("/^v/", "", $tag);
        }
        $tags = array_combine($tags, $versions);
        unset($versions);
        uasort($tags, function ($a, $b)
        {
            return -version_compare($a, $b);
        });
        $tags = array_filter($tags, function ($version)
        {
            return !preg_match("/[a-z]/i", $version);
        });

        $timeMachineFolder = $tempFolder . "/git_time_machine/";
        OsHelper::runValidated('mkdir -p ' . escapeshellarg($timeMachineFolder));
        OsHelper::runValidated('git rev-parse --show-toplevel', $repo_folder);
        $repo_folder = realpath(trim($repo_folder) . "/.git");
        OsHelper::runValidated(
            'cp -a ' .
            escapeshellarg($repo_folder) .
            ' ' .
            escapeshellarg($timeMachineFolder)
        );

        foreach ($tags as $tag => $version)
        {
            OsHelper::runValidated(
                'git --no-pager diff --no-renames --name-only --diff-filter D ' . escapeshellarg($tag),
                $stdout
            );
            $removedFiles = explode("\n", trim($stdout));
            OsHelper::runValidated(
                'git -C ' . escapeshellarg($timeMachineFolder) . ' ' .
                'checkout ' . escapeshellarg($tag)
            );
            foreach ($removedFiles as $removedFilePath)
            {
                $checksum = $this->checksumPath($timeMachineFolder . '/' . $removedFilePath);
                $item = self::array_get($checksums, $removedFilePath, []);
                if (!in_array($checksum, $item)) $item["v{$version}"] = $checksum;
                self::array_set($checksums, $removedFilePath, $item);
            }
        }

        OsHelper::runValidated('rm -rf ' . escapeshellarg($timeMachineFolder));

        ksort($checksums);
        return $checksums;
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

    private function generateStub()
    {
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

        return $data;
    }
}