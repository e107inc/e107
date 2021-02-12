<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

require_once("e_file_inspector_json.php");

class e_file_inspector_json_phar extends e_file_inspector_json
{
    /**
     * @param $jsonPharFilePath string Absolute path to the file inspector database
     */
    public function __construct($jsonPharFilePath = null)
    {
        parent::__construct($jsonPharFilePath);
    }

    /**
     * @inheritDoc
     */
    public function loadDatabase()
    {
        if(!file_exists($this->database))
        {
            $this->coreImage = [];
            return false;
        }

        Phar::loadPhar($this->database, "core_image.phar");
        $tmpFile = tmpfile();
        $tmpFilePath = stream_get_meta_data($tmpFile)['uri'];
        $this->copyUrlToResource("phar://core_image.phar/core_image.json", $tmpFile);
        $this->coreImage = json_decode(file_get_contents($tmpFilePath), true);
        if (!is_array($this->coreImage)) $this->coreImage = [];
        $this->coreImage = self::array_slash($this->coreImage);
        if (!$this->coreImage) $this->coreImage = [];
    }

    /**
     * Copy file to destination with low memory footprint
     * @param $source string URL of the source
     * @param $destination resource File pointer of the destination
     */
    private function copyUrlToResource($source, $destination)
    {
        $dbFile = fopen($source, "r");
        while (!feof($dbFile))
        {
            $buffer = fread($dbFile, 4096);
            fwrite($destination, $buffer);
        }
        unset($buffer);
        fclose($dbFile);
    }
}