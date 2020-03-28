<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

require_once("e_file_inspector.php");

class e_file_inspector_sqlphar extends e_file_inspector
{
    private $coreImage;

    /**
     * @param $pharFilePath string Absolute path to the file inspector database
     */
    public function __construct($pharFilePath = null)
    {
        parent::__construct($pharFilePath);
    }

    /**
     * @inheritDoc
     */
    public function loadDatabase()
    {
        Phar::loadPhar($this->database, "core_image.phar");
        $tmpFile = tmpfile();
        $tmpFilePath = stream_get_meta_data($tmpFile)['uri'];
        $this->copyUrlToResource("phar://core_image.phar/core_image.sqlite", $tmpFile);
        $this->coreImage = new PDO("sqlite:{$tmpFilePath}");
    }

    /**
     * @inheritDoc
     */
    public function getPathIterator($version = null)
    {
        $addVersionWhere = "";
        $inputParameters = [];
        if (!empty($version))
        {
            $addVersionWhere = "WHERE versions.version_string = ?";
            $inputParameters[] = $version;
        }
        $statement = $this->coreImage->prepare("
            SELECT path
            FROM file_hashes
            LEFT JOIN versions ON versions.version_id = file_hashes.release_version
            $addVersionWhere
            ORDER BY path ASC;
        ");
        $statement->setFetchMode(PDO::FETCH_COLUMN, 0);
        $statement->execute($inputParameters);
        return new IteratorIterator($statement);
    }

    /**
     * @inheritDoc
     */
    public function getChecksums($path)
    {
        $path = $this->customPathToDefaultPath($path);
        $statement = $this->coreImage->prepare("
            SELECT versions.version_string, file_hashes.hash
            FROM file_hashes
            LEFT JOIN versions ON versions.version_id = file_hashes.release_version
            WHERE file_hashes.path = :path
            ORDER BY path ASC;
        ");
        $statement->execute([
            ':path' => $path
        ]);
        return $statement->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * @inheritDoc
     */
    public function getCurrentVersion()
    {
        if ($this->currentVersion) return $this->currentVersion;

        $statement = $this->coreImage->query("
            SELECT version_string FROM versions ORDER BY version_string DESC LIMIT 1
        ");
        return $this->currentVersion = $statement->fetchColumn();
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