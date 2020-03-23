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

class SqlpharCoreImage
{
	/** @var PDO */
	protected $db;

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

		$this->create_image($exportFolder, $tempFolder, $currentVersion);

		$phar->startBuffering();
		$phar->setStub($this->generateStub());
		$phar->addFile($imageSqliteFile, "core_image.sqlite");
		$phar->compressFiles(Phar::BZ2);
		$phar->stopBuffering();
		rename($imagePharFile, $imageFile);
	}

	function create_image($exportFolder, $tempFolder, $currentVersion)
	{
        echo("[Core-Image] Scanning Dir: " . $exportFolder . "\n");
		$this->generateCurrentChecksums($exportFolder, $currentVersion);

		echo("[Core-Image] Scanning Removed Files from Git" . "\n");
		$this->generateRemovedChecksums($tempFolder);
	}

	protected function generateCurrentChecksums($exportFolder, $currentVersion)
	{
		$absoluteBase = realpath($exportFolder);
		if (!is_dir($absoluteBase)) return false;

		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($exportFolder));

		$insert_statement = $this->insert_statement($relativePath, $currentVersion, $checksum);
		$this->db->beginTransaction();

		$this->insert_version($currentVersion);

		/**
		 * @var $file DirectoryIterator
		 */
		foreach ($iterator as $file) {
			if ($file->isDir()) continue;

			$absolutePath = $file->getRealPath();
			$relativePath = preg_replace("/^" . preg_quote($absoluteBase . "/", "/") . "/", "", $absolutePath);

			if (empty($relativePath) || $relativePath == $absolutePath) continue;

			$checksum = $this->checksumPath($absolutePath);
			$insert_statement->execute();
		}

		$this->db->commit();
	}

	protected function checksumPath($filename)
	{
		return $this->checksum(file_get_contents($filename));
	}

	protected function checksum($body)
	{
		return md5(str_replace(array(chr(13), chr(10)), '', $body));
	}

	protected function generateRemovedChecksums($tempFolder)
	{
		$stdout = '';
		OsHelper::runValidated('git tag --list ' . escapeshellarg("v*"), $stdout);
		$tags = explode("\n", trim($stdout));
		$versions = [];
		foreach ($tags as $tag) {
			$versions[] = preg_replace("/^v/", "", $tag);
		}
		$tags = array_combine($tags, $versions);
		unset($versions);
		uasort($tags, function ($a, $b) {
			return -version_compare($a, $b);
		});
		$tags = array_filter($tags, function ($version) {
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

		$insert_statement = $this->insert_statement($removedFilePath, $version, $checksum);
		$check_statement = $this->db->prepare('SELECT COUNT(*) FROM file_hashes WHERE path = :path AND hash = :hash');
		$this->db->beginTransaction();

		foreach ($tags as $tag => $version) {
			$this->insert_version($version);
			OsHelper::runValidated(
				'git --no-pager diff --no-renames --name-only --diff-filter D ' . escapeshellarg($tag),
				$stdout
			);
			$removedFiles = explode("\n", trim($stdout));
			OsHelper::runValidated(
				'git -C ' . escapeshellarg($timeMachineFolder) . ' ' .
				'checkout ' . escapeshellarg($tag)
			);
			foreach ($removedFiles as $removedFilePath) {
				$checksum = $this->checksumPath($timeMachineFolder . '/' . $removedFilePath);
				$check_statement->execute([':path' => $removedFilePath, ':hash' => $checksum]);
				if ($check_statement->fetchColumn() == 0) $insert_statement->execute();
			}
		}

		OsHelper::runValidated('rm -rf ' . escapeshellarg($timeMachineFolder));

		$this->db->commit();
	}

	/**
	 * @param $relativePath
	 * @param $releaseVersion
	 * @param $checksum
	 * @return PDOStatement
	 */
	private function insert_statement(&$relativePath, &$releaseVersion, &$checksum)
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
		return $insert_statement;
	}

	private function insert_version($releaseVersion)
	{
		$statement = $this->db->prepare(
			"INSERT INTO versions (version_id, version_string) VALUES (NULL, ?)"
		);
		$statement->execute([$releaseVersion]);
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
		$data .= "__HALT_COMPILER();";

		return $data;
	}
}