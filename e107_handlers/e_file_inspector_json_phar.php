<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

require_once("e_file_inspector_json.php");


/**
 *
 */
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
        $this->coreImage = json_decode(file_get_contents("phar://core_image.phar/core_image.json"), true);
        if (!is_array($this->coreImage)) $this->coreImage = [];
        $this->coreImage = self::array_slash($this->coreImage);
        if (!$this->coreImage) $this->coreImage = [];
    }
}