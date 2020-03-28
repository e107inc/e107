<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

require_once("JsonCoreImage.php");

class JsonPharCoreImage extends JsonCoreImage
{
    protected function saveImage($imageFile)
    {
        $imagePharFile = "$imageFile.phar";
        $phar = new Phar($imagePharFile);

        $json_result = json_encode($this->checksums);

        $imageJsonFile = "$imageFile.json";
        $fp = fopen($imageJsonFile, 'w');
        fwrite($fp, $json_result);
        fclose($fp);

        $phar->startBuffering();
        $phar->setStub($this->generateStub());
        $phar->addFile($imageJsonFile, "core_image.json");
        $phar->compressFiles(Phar::GZ);
        $phar->stopBuffering();
        rename($imagePharFile, $imageFile);
    }

    protected function generateStub()
    {
        $data = parent::generateStub();
        $data .= "__HALT_COMPILER();";

        return $data;
    }
}