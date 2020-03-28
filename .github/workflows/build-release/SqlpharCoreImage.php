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

class SqlpharCoreImage extends CoreImage
{
    /** @var PDO */
    protected $db;
    /**
     * @var PDOStatement
     */
    private $insert_statement;
    /**
     * @var PDOStatement
     */
    private $check_statement;

    // Insert bindings
    private $relativePath;
    private $releaseVersion;
    private $checksum;

    public function __construct($exportFolder, $tempFolder, $currentVersion, $imageFile)
    {
        $imagePharFile = "$imageFile.phar";
        $phar = new Phar($imagePharFile);

        $imageSqliteFile = "$imageFile.sqlite";
        file_put_contents($imageSqliteFile, '');
        $this->db = new PDO("sqlite:{$imageSqliteFile}");
        $this->db->exec('
			CREATE TABLE IF NOT EXISTS file_hashes (
			    path TEXT,
			    release_version INTEGER,
			    hash TEXT'/*.',
			    UNIQUE(path, hash) ON CONFLICT IGNORE'*/ . '
			);
        ');
        $this->db->exec('
			CREATE TABLE IF NOT EXISTS versions (
				version_id INTEGER PRIMARY KEY,
				version_string TEXT,
				UNIQUE(version_string) ON CONFLICT IGNORE
			);
		');
        # Retrieval:
        #   SELECT file_hashes.path, versions.version_string, file_hashes.hash
        #   FROM file_hashes
        #   LEFT JOIN versions ON versions.version_id = file_hashes.release_version
        #   ORDER BY path ASC;

        $this->check_statement = $this->db->prepare('SELECT COUNT(*) FROM file_hashes WHERE path = :path AND hash = :hash');
        $this->insert_statement = $this->bind_insert(
            $this->relativePath,
            $this->releaseVersion,
            $this->checksum);

        $this->create_image($exportFolder, $tempFolder, $currentVersion);

        $phar->startBuffering();
        $phar->setStub($this->generateStub());
        $phar->addFile($imageSqliteFile, "core_image.sqlite");
        $phar->compressFiles(Phar::BZ2);
        $phar->stopBuffering();
        rename($imagePharFile, $imageFile);
    }

    protected function generateCurrentChecksums($exportFolder, $currentVersion)
    {
        $this->db->beginTransaction();
        $this->insert_version($currentVersion);
        parent::generateCurrentChecksums($exportFolder, $currentVersion);
        $this->db->commit();
    }

    /**
     * @param $relativePath
     * @param $releaseVersion
     * @param $checksum
     * @return PDOStatement
     */
    private function bind_insert(&$relativePath, &$releaseVersion, &$checksum)
    {
        $relativePath = $relativePath ?: null;
        $releaseVersion = $releaseVersion ?: null;
        $checksum = $checksum ?: null;
        $insert_statement = $this->db->prepare(
            "INSERT INTO file_hashes (
                      	path, release_version, hash
                      ) VALUES (
                        :path, (SELECT version_id FROM versions WHERE version_string = :release_version), :hash
                      )"
        );
        $insert_statement->bindParam(":path", $relativePath);
        $insert_statement->bindParam(":release_version", $releaseVersion);
        $insert_statement->bindParam(":hash", $checksum);
        return $this->insert_statement = $insert_statement;
    }

    private function insert_version($releaseVersion)
    {
        $statement = $this->db->prepare(
            "INSERT INTO versions (version_id, version_string) VALUES (NULL, ?)"
        );
        $statement->execute([$releaseVersion]);
    }

    protected function generateRemovedChecksums($tempFolder)
    {
        $this->db->beginTransaction();
        $tags = $this->getGitTags();
        foreach ($tags as $tag => $version)
        {
            $this->insert_version($version);
        }
        parent::generateRemovedChecksums($tempFolder);
        $this->db->commit();
    }

    protected function generateStub()
    {
        $data = parent::generateStub();
        $data .= "__HALT_COMPILER();";

        return $data;
    }

    /**
     * @inheritDoc
     */
    protected function insertChecksumIntoDatabase(&$relativePath, &$checksum, &$releaseVersion)
    {
        $this->relativePath = $relativePath;
        $this->checksum = $checksum;
        $this->releaseVersion = $releaseVersion;
        $this->check_statement->execute([':path' => $relativePath, ':hash' => $checksum]);
        if ($this->check_statement->fetchColumn() == 0) $this->insert_statement->execute();
    }
}