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

class CoreImage
{
	/** @var PDO */
	protected $db;

	public function __construct($exportFolder, $tempFolder, $currentVersion, $imageFile)
	{
		set_time_limit(240);

		file_put_contents($imageFile, '');
		$this->db = new PDO("sqlite:{$imageFile}");
		$this->db->exec('
			CREATE TABLE IF NOT EXISTS file_hashes (
			    path TEXT,
			    release_version TEXT,
			    hash TEXT'/*.',
			    UNIQUE(path, hash) ON CONFLICT IGNORE'*/ . '
			);
        ');

		$this->create_image($exportFolder, $tempFolder, $currentVersion);
	}

	function create_image($exportFolder, $tempFolder, $currentVersion)
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
			"INSERT INTO file_hashes (path, release_version, hash) VALUES (:path, :release_version, :hash)"
		);
		$insert_statement->bindParam(":path", $relativePath);
		$insert_statement->bindParam(":release_version", $releaseVersion);
		$insert_statement->bindParam(":hash", $checksum);
		return $insert_statement;
	}
}