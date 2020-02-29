<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once("coreImage.php");

$builder = new e107Build();
$builder->makeBuild();

class e107Build
{
	var $releaseDir = "";
	var $tempDir = null;
	var $exportDir = null;
	var $gitDir = null;
	var $stagingDir = null;
	protected $config;
	protected $version;

	public function __construct()
	{
		$this->config['baseDir'] = dirname(__FILE__);
		$iniFile = $this->config['baseDir'] . '/make.ini';

		if (is_readable($iniFile))
		{
			$this->status('Reading config file: ' . $iniFile);
			$this->config = parse_ini_file($iniFile, true);
		}
		else
		{
			throw new RuntimeException("Configuration file " . escapeshellarg($iniFile) . " not found.");
		}
		foreach ($this->config as $k => $v)
		{
			if (preg_match('#release_(\d*)#', $k, $matches))
			{
				$this->config['releases'][] = $v;
				unset($this->config[$k]);
			}
		}

		$this->config['baseDir'] = dirname(__FILE__);


		$this->exportDir = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/export/";
		$this->tempDir = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/temp/";
		$this->stagingDir = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/staging/";

		$rc = 255;
		$output = [];
		exec("git rev-parse --show-toplevel", $output, $rc);
		$gitRoot = array_pop($output);
		if (!is_dir($gitRoot))
		{
			throw new RuntimeException("Error getting Git repo root (rc=$rc). Output was garbage: $gitRoot");
		}
		$this->gitDir = realpath($gitRoot);

		exec("git describe --tags", $output, $rc);
		$gitVersion = array_pop($output);
		$verFileVersion = self::getVerFileVersion($this->gitDir . "/e107_admin/ver.php");
		$this->version = self::gitVersionToPhpVersion($gitVersion, $verFileVersion);

		$this->validateReadme();
	}

	private function status($msg, $heading = false)
	{
		if ($heading == false)
		{
			echo date('m/d/Y h:i:s') . '  ';
		}
		else
		{
			echo "\n\n>>>> ";
		}

		echo $msg . "\n";

		if ($heading != false)
		{
			echo "\n";
		}
	}

	private static function getVerFileVersion($verFilePath)
	{
		$verFileTokens = token_get_all(file_get_contents($verFilePath));
		$nextConstantEncapsedStringIsVersion = false;
		foreach ($verFileTokens as $verFileToken)
		{
			if (!isset($verFileToken[1])) continue;
			$token = $verFileToken[0];
			$value = trim($verFileToken[1], "'\"");

			if ($token === T_CONSTANT_ENCAPSED_STRING)
			{
				if ($nextConstantEncapsedStringIsVersion)
				{
					return $value;
				}
				if ($value === 'e107_version') $nextConstantEncapsedStringIsVersion = true;
			}
		}
		return '0';
	}

	private static function gitVersionToPhpVersion($gitVersion, $verFileVersion)
	{
		$verFileVersion = array_shift(explode(" ", $verFileVersion));
		$version = preg_replace("/^v/", "", $gitVersion);
		$versionSplit = explode("-", $version);
		if (count($versionSplit) > 1)
		{
			if (version_compare($verFileVersion, $versionSplit[0], '>')) $versionSplit[0] = $verFileVersion;
			$versionSplit[0] .= "dev";
		}
		return implode("-", $versionSplit);
	}

	private function validateReadme()
	{
		//check for readme files associated with configured releases
		foreach ($this->config['releases'] as $rel)
		{
			if (isset($rel['readme']))
			{
				$fname = "{$this->config['baseDir']}/readme/{$this->config['main']['name']}/{$rel['readme']}";
				if (!is_readable($fname))
				{
					throw new RuntimeException("ERROR: readme file $fname does not exist.");
				}
			}
		}
	}

	public function makeBuild()
	{
		$this->status("Building release " . $this->version);

		$this->cleanupFiles();

		$this->preprocess();

		$this->createReleases();

		echo "\n\nDONE!!!\n\n\n";
	}

	private function cleanupFiles()
	{
		$dir = "{$this->config['baseDir']}/target/{$this->config['main']['name']}";

		if (file_exists($dir))
		{
			$this->status("Cleaning up old target directory ($dir)");
			$cmd = "rm -rf " . escapeshellarg($dir);
			$this->runValidated($cmd);
			$this->changeDir($this->config['baseDir']);
		}
		else
		{
			$this->status("Creating new target directory ($dir)");
			$cmd = "mkdir -pv " . escapeshellarg($dir);
			$this->runValidated($cmd);
		}

		if (file_exists($dir . '/temp'))
		{
			throw new RuntimeException("Target Directory Not Clean! Aborting...");
		}

		$cmd = "mkdir -pv " . escapeshellarg($dir . "/temp");
		$this->runValidated($cmd);

		$cmd = "mkdir -pv " . escapeshellarg($dir . "/release");
		$this->runValidated($cmd);

		$releaseDir = "e107_" . $this->version;

		$this->releaseDir = $releaseDir;

		$this->status("Creating new release directory ($releaseDir)", true);
		$cmd = "mkdir -pv " . escapeshellarg($dir . "/release/" . $releaseDir);
		$this->runValidated($cmd);

		return true;
	}

	protected function runValidated($command, &$stdout = "", &$stderr = "")
	{
		$rc = $this->run($command, $stdout, $stderr);
		if ($rc != 0)
		{
			throw new RuntimeException(
				"Error while running command (rc=$rc): " . $command . PHP_EOL .
				"========== STDOUT ==========" . PHP_EOL .
				$stdout . PHP_EOL .
				"========== STDERR ==========" . PHP_EOL .
				$stderr . PHP_EOL
			);
		}
		return $rc;
	}

	/*
	private function targetClone()
	{
		$rc = 255;
		$output = [];
		exec("git rev-parse --show-toplevel", $output, $rc);
		$gitRoot = array_pop($output);
		if (!is_dir($gitRoot))
		{
			$this->status("Error getting Git repo root (rc=$rc). Output was garbage: $gitRoot");
			return false;
		}
		$gitRoot = realpath($gitRoot);
		mkdir($this->stagingDir, 0755, true);
		$stagingDir = realpath($this->stagingDir);
		$stagingDirLocalSegment = preg_replace("/^" . preg_quote($gitRoot, "/") . "/", "", $stagingDir);
		if ($stagingDirLocalSegment == $stagingDir)
		{
			$this->status("Staging dir \"$stagingDir\" is currently not supported outside repo root \"$gitRoot\"");
		}
		$cloneCommand =
			"rsync -avHXShPs" .
			" --exclude=" . escapeshellarg($stagingDirLocalSegment . "/") .
			" --delete-after --delete-excluded" .
			" --link-dest=" . escapeshellarg($gitRoot) .
			" " . escapeshellarg($gitRoot . "/") .
			" " . escapeshellarg($stagingDir . "/");
		exec($cloneCommand, $output, $rc);

		return $rc;
	}
	*/

	/**
	 * @param string $command The command to run
	 * @param string $stdout Reference to the STDOUT output as a string
	 * @param string $stderr Reference to the STDERR output as a string
	 * @return int Return code of the command that was run
	 */
	protected function run($command, &$stdout = "", &$stderr = "")
	{
		$descriptorspec = [
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];
		$pipes = [];
		$resource = proc_open($command, $descriptorspec, $pipes);
		$stdout .= stream_get_contents($pipes[1]);
		$stderr .= stream_get_contents($pipes[2]);
		foreach ($pipes as $pipe)
		{
			fclose($pipe);
		}
		return proc_close($resource);
	}

	private function changeDir($dir)
	{
		$this->status("Changing to dir: " . $dir);
		chdir($dir);
	}

	private function preprocess()
	{
		return true;
	}

	private function createReleases()
	{
		foreach ($this->config['releases'] as $c => $rel)
		{
			$this->status(" ------------------ Release " . $c . "--------------------------- ", true);

			$this->emptyExportDir();

			$zipExportFile = 'release_' . $c . ".zip";

			$this->gitArchive($zipExportFile, $rel['since']);

			$this->gitArchiveUnzip($zipExportFile);

			$this->editVersion('export');

			$this->changeDir($this->exportDir);


			foreach ($rel as $name => $val)
			{
				switch ($name)
				{
					case "files_create" :
						$this->filesCreate($val);
						break;

					case "files_rename" :
						$this->filesRename($val);
						break;

					case "files_delete" :
						$this->filesDelete($val);
						break;

					case "plugin_delete" :
						$this->pluginRemove($val);
						break;
				}
			}


			if ($rel['type'] == 'full')
			{
				$this->CreateCoreImage(); // Create Image
			}

			$this->copyCoreImage();

			if (isset($rel['readme']))
			{
				$this->moveReadme($rel['readme']);
			}

			$zipsince = '';
			$ts = '';

			$newfile = "";
			if ($rel['type'] == 'full')
			{
				$newfile = "e107_" . $this->version . "_full";
				$this->status("Creating Release " . $c . " Packages : full", true);
			}
			elseif ($rel['type'] == "upgrade")
			{
				$newfile = "e107_" . $rel['from_version'] . "_to_" . $this->version . "_upgrade";
				$this->status("Creating Release " . $c . " Packages :  upgrade from {$rel['from_version']}", true);
			}

			$releaseDir = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/release/" . $this->releaseDir;

			/**
			 * git archive -o update.zip HEAD $(git diff --name-only [id])
			 *
			 * Of course you need to replace the ‘[id]’-part with the ID of your commit. So if the ID of your commit is ‘599313e986c56e5451caa14d32c6b18273f4331b’ then your command would look like this:
			 * git archive -o update.zip HEAD $(git diff --name-only  599313e986c56e5451caa14d32c6b18273f4331b)
			 * 1
			 *
			 * git archive -o update.zip HEAD $(git diff --name-only  599313e986c56e5451caa14d32c6b18273f4331b)
			 */


			$zipfile = $releaseDir . '/' . $newfile . '.zip';
			$tarfile = $releaseDir . '/' . $newfile . '.tar';

			$zipcmd = "zip -9 -r{$zipsince} $zipfile . 2>&1";
			$tarcmd = "tar --owner=0 --group=0 -cf $tarfile {$ts} . 2>&1";

			$this->status('Creating ZIP archive');
			$this->status($zipcmd);
			$this->runValidated($zipcmd);

			$this->status('Creating TAR archive');
			$this->status($tarcmd);
			$this->runValidated($tarcmd);

			$this->status('Creating TAR.GZ archive');
			$this->runValidated("(cat $tarfile | gzip -9 > $tarfile.gz)");
//			$this->status('Creating TAR.XZ archive');
//			$this->runValidated(cat $tarfile | xz -9e > $tarfile.xz)");

			$this->status('Removing TAR archive');
			unlink($tarfile);
		} // end loop


	}

	private function emptyExportDir()
	{
		if (is_dir($this->exportDir))
		{
			$this->rmdir($this->exportDir);
		}

		$this->status("Making export directory. ");
		mkdir($this->exportDir, 0755);
	}

	private function rmdir($dir)
	{
		if (!is_dir($dir))
		{
			return false;
		}

		$this->status("Removing directory: " . $dir);

		$dir = rtrim($dir, "/");

		$cmd = "rm -rf " . escapeshellarg($dir);
		$this->status($cmd);
		$this->runValidated($cmd);

		return true;
	}

	private function gitArchive($zipFile, $since = null)
	{
		$file = $this->tempDir . $zipFile;

		$this->status("Zipping up temp Release archive..");

		if (!empty($since))
		{
			$cmd = "git archive -o " . escapeshellarg($file) . " HEAD $(git diff --name-only --diff-filter=ACMRTUXB " . escapeshellarg($since) . ")";
		}
		else
		{
			$cmd = "git archive -o " . escapeshellarg($file) . " HEAD";
		}

		$this->changeDir($this->gitDir);

		$this->runValidated($cmd);
	}

	private function gitArchiveUnzip($file)
	{
		$this->status("Unzipping temp archive to export folder", true);
		$filepath = $this->tempDir . $file;
		$cmd = 'unzip -q -o ' . escapeshellarg($filepath) . ' -d ' . escapeshellarg($this->exportDir);

		$this->runValidated($cmd);
		$this->runValidated('chmod -R a=,u+rwX,go+rX ' . escapeshellarg($this->exportDir));
	}

	private function editVersion($dir = 'export')
	{
		$version = $this->version;

		if (strpos($version, "-") !== false)
		{
			$version .= " nightly build " . date('Ymd');
		}

		$fname = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/" . $dir . "/e107_admin/ver.php";

		$this->status("Writing new version {$version} to ver.php in " . $dir . " directory.", true);

		$contents = "<?php\n";
		$contents .= "/*\n";
		$contents .= "* Copyright (c) " . date("Y") . " e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)\n";
		$contents .= "*\n";
		$contents .= "* Version file\n";
		$contents .= "*/\n\n";
		$contents .= "if (!defined('e107_INIT')) { exit; }\n\n";
		$contents .= "\$e107info['e107_version'] = \"{$version}\";\n";

		$contents .= "?>\n";

		return file_put_contents($fname, $contents);
	}

	private function filesCreate($parm)
	{
		$fnames = explode(",", $parm);
		foreach ($fnames as $fn)
		{
			$fn = trim($fn);
			$result = touch($fn);
			$this->status("Creating $fn - " . ($result ? "SUCCESS" : "FAIL"));
			if (!$result) throw new RuntimeException("Failed to touch: $fn");
		}
	}

	private function filesRename($parm)
	{
		$pair = explode(',', $parm);
		foreach ($pair as $fn)
		{
			list($old, $new) = explode('->', $fn);
			$result = rename($old, $new);
			$this->status("Renaming {$old} to {$new} " . ($result ? "SUCCESS" : "FAIL"));
			if (!$result)
				throw new RuntimeException(
					"Failed to rename " . escapeshellarg($old) . " to " . escapeshellarg($new)
				);
		}
	}

	private function filesDelete($parm)
	{
		$fnames = explode(',', $parm);

		foreach ($fnames as $fn)
		{
			$fn = trim($fn);
			if (file_exists($fn))
			{
				if (is_file($fn)) $result = unlink($fn);
				elseif (is_dir($fn)) $result = $this->rmdir($fn);
				else $result = false;
				$this->status("Deleting $fn - " . ($result ? "SUCCESS" : "FAIL"));
				if (!$result) throw new RuntimeException("Failed to delete: $fn");
			}
			else
			{
				$this->status("File already deleted or absent - " . $fn);
			}
		}
	}

	private function pluginRemove($parm, $restore = false)
	{
		if ($restore)
		{
			$this->status("Running Plugin-Restore", true);
		}
		else
		{
			$this->status("Running Plugin-Remove", true);
		}

		$fnames = explode(',', $parm);

		if ($restore == true)
		{
			return;
		}

		foreach ($fnames as $fn)
		{
			$fn = trim($fn);
			$dir = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/export/e107_plugins/" . $fn;

			$this->rmdir($dir);
		}
	}

	private function createCoreImage()
	{
		//create new image file - writes directly to cvsroot
		$this->changeDir($this->config['baseDir']);

		$_current = $this->exportDir;
		$_deprecated = "{$this->config['baseDir']}/deprecated/{$this->config['main']['name']}";

		$_image = $this->tempDir . "core_image.php";

		$this->status("Creating new core_image.php file ({$_image})", true);
		new coreImage($_current, $_deprecated, $_image);

		$dir = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/export";
		$this->changeDir($dir);
	}

	private function copyCoreImage()
	{
		$orig = $this->tempDir . "core_image.php";
		$dest = $this->exportDir . "e107_admin/core_image.php";

		if (!file_exists($orig))
		{
			throw new RuntimeException("Core image file not found: {$orig}");
		}

		$this->status("Copying Core Image into export directory", true);
		$this->runValidated("cp -rf " . escapeshellarg($orig) . " " . escapeshellarg($dest));

		if (!file_exists($dest))
		{
			throw new RuntimeException("Core image file didnt copy to: {$dest}");
		}

	}

	private function moveReadme($readme)
	{
		$from = "{$this->config['baseDir']}/readme/{$this->config['main']['name']}/{$readme}";
		$to = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/export/README.txt";
		$result = copy($from, $to);
		$this->status("Copying readme file $readme to $to - " . ($result ? "SUCCESS" : "FAIL"));
		if (!$result) throw new RuntimeException("Failed to copy $readme to $to");
	}
}
