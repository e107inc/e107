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
require_once("JsonPharCoreImage.php");

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
		$verFileVersion = OsHelper::getVerFileVersion($this->gitDir . "/e107_admin/ver.php");
		$this->version = OsHelper::gitVersionToPhpVersion($gitVersion, $verFileVersion);

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
			OsHelper::runValidated($cmd);
			$this->changeDir($this->config['baseDir']);
		}
		else
		{
			$this->status("Creating new target directory ($dir)");
			$cmd = "mkdir -pv " . escapeshellarg($dir);
			OsHelper::runValidated($cmd);
		}

		if (file_exists($dir . '/temp'))
		{
			throw new RuntimeException("Target Directory Not Clean! Aborting...");
		}

		$cmd = "mkdir -pv " . escapeshellarg($dir . "/temp");
		OsHelper::runValidated($cmd);

		$cmd = "mkdir -pv " . escapeshellarg($dir . "/release");
		OsHelper::runValidated($cmd);

		$releaseDir = "e107_" . $this->version;

		$this->releaseDir = $releaseDir;

		$this->status("Creating new release directory ($releaseDir)", true);
		$cmd = "mkdir -pv " . escapeshellarg($dir . "/release/" . $releaseDir);
		OsHelper::runValidated($cmd);

		return true;
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

			$since = isset($rel['since']) ? $rel['since'] : null;
			$this->gitArchive($zipExportFile, $since);

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
				$this->createCoreImage(); // Create Image
			}

			$this->copyCoreImage($this->exportDir . "e107_system/core_image.phar");

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

			$this->copyCoreImage($releaseDir . "/core_image.phar");

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
			OsHelper::runValidated($zipcmd);

			$this->status('Creating TAR archive');
			$this->status($tarcmd);
			OsHelper::runValidated($tarcmd);

			$this->status('Creating TAR.GZ archive');
			OsHelper::runValidated("(cat $tarfile | gzip -9 > $tarfile.gz)");
			$this->status('Creating TAR.XZ archive');
			OsHelper::runValidated("(cat $tarfile | xz -9 > $tarfile.xz)");

			$this->status('Removing TAR archive');
			unlink($tarfile);
		} // end loop

		$this->status('Removing export folder', true);
		$this->rmdir($this->exportDir);
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
		OsHelper::runValidated($cmd);

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

		OsHelper::runValidated($cmd);
	}

	private function gitArchiveUnzip($file)
	{
		$this->status("Unzipping temp archive to export folder", true);
		$filepath = $this->tempDir . $file;
		$cmd = 'unzip -q -o ' . escapeshellarg($filepath) . ' -d ' . escapeshellarg($this->exportDir);

		OsHelper::runValidated($cmd);
		OsHelper::runValidated('chmod -R a=,u+rwX,go+rX ' . escapeshellarg($this->exportDir));
	}

	private function editVersion($dir = 'export')
	{
		$version = $this->version;

		if (preg_match("/" . OsHelper::REGEX_MATCH_GIT_DESCRIBE_TAGS . "$/", $version))
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

		$imageFile = $this->tempDir . "core_image.phar";

		$this->status("Creating new core_image.phar file ({$imageFile})", true);
		new JsonPharCoreImage($this->exportDir, $this->tempDir, $this->version, $imageFile);

		$dir = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/export";
		$this->changeDir($dir);
	}

	private function copyCoreImage($destination)
	{
		$source = $this->tempDir . "core_image.phar";

		if (!file_exists($source))
		{
			throw new RuntimeException("Core image file not found: {$source}");
		}

		$this->status("Copying Core Image into: $destination", true);
		OsHelper::runValidated("cp -rf " . escapeshellarg($source) . " " . escapeshellarg($destination));

		if (!file_exists($destination))
		{
			throw new RuntimeException("Core image file didnt copy to: {$destination}");
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
