<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

$builder = new e107Build();
$builder->init();
$builder->makeBuild();

class e107Build
{

	public $config, $version, $lastversion, $lastversiondate, $beta, $error, $rc;

	var $createdFiles = array();
	var $releaseDir = "";

	var $tempDir = null;
	var $exportDir = null;
	var $gitDir = null;
	var $stagingDir = null;

	public function __construct()
	{
		$this->beta = false;
		$this->error = false;
		$this->rc = false;

		$this->config['baseDir'] = dirname(__FILE__);
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

	public function init($module = null)
	{
		$iniFile = $this->config['baseDir'] . '/make.ini';

		if (is_readable($iniFile))
		{
			$this->status('Reading config file: ' . $iniFile);
			$this->config = parse_ini_file($iniFile, true);
		}
		else
		{
			echo(" configuration file '{$iniFile}' not found.\n\n");
			$this->error = TRUE;
			return;
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
			$this->status("Error getting Git repo root (rc=$rc). Output was garbage: $gitRoot");
			return false;
		}
		$this->gitDir = realpath($gitRoot);

		exec("git describe --tags", $output, $rc);
		$gitVersion = array_pop($output);
		$verFileVersion = self::getVerFileVersion($this->gitDir . "/e107_admin/ver.php");
		$this->version = self::gitVersionToPhpVersion($gitVersion, $verFileVersion);

		$this->config['preprocess']['version'] = $this->version;

		if ($this->ReadMeProblems())
		{
			return;
		}


		if ($this->beta && ($module == '07'))
		{
			$this->config['releases'] = array();

			// One Full Release Beta
			$this->config['releases'][] = array(
				'type' => 'full',
				'files_create' => 'e107_config.php',
				'files_rename' => 'install_.php->install.php'
			);

			// One Full Upgrade Beta
			$this->config['releases'][] = array(
				'type' => 'upgrade',
				'from_version' => 'v1.x',
				'files_delete' => 'e107_config.php,install.php,favicon.ico,.gitignore',
				'since' => '01152006', // $this->lastversiondate, // mmddyyyy
				//		'readme'		=> '07x_upgrade.txt'
			);

			$this->buildLastConfig();
		}


	}

	private function status($msg, $heading = false)
	{
		if ($heading == false)
		{
			echo date('m/d/Y h:i:s') . '  ';
		}

		if ($heading != false)
		{
			echo "\n\n>>>> ";
		}

		echo $msg . "\n";

		if ($heading != false)
		{
			echo "\n";
		}


	}

	private function ReadMeProblems()
	{
		//check for readme files associated with configured releases
		$error = false;
		foreach ($this->config['releases'] as $rel)
		{
			if (isset($rel['readme']))
			{
				$fname = "{$this->config['baseDir']}/readme/{$this->config['main']['name']}/{$rel['readme']}";
				if (!is_readable($fname))
				{
					echo "ERROR: readme file $fname does not exist.\n";
					$error = true;
				}
			}
		}

		return $error;
	}

	private function buildLastConfig()
	{
		if (!$this->lastversion || !$this->lastversiondate)
		{
			echo "No LastVersion of LastVersiondate Found. Continuing...\n";
			return;
		}

		// Automatically Include the last release in the Config
		if ($this->lastversion && $this->lastversiondate)
		{
			$this->config['releases'][] = array(
				'type' => 'upgrade',
				'since' => $this->lastversiondate, // mmddyyyy
				'files_delete' => 'favicon.ico',
				'readme' => str_replace(".", "", $this->lastversion) . '_upgrade.txt',
				'from_version' => 'v' . $this->lastversion
			);

			// Generate the Readme for the "last release -> this release update";

			$lastReadme = $this->config['baseDir'] . "/readme/{$this->config['main']['name']}/" . str_replace(".", "", $this->lastversion) . '_upgrade.txt';
			if (!is_readable($lastReadme))
			{
				if (file_put_contents($lastReadme, $this->generateReadme()))
				{
					echo("Writing ReadMe Data to " . $lastReadme . "\n");
				}
				else
				{
					echo("Couldn't write ReadMe Data to " . $lastReadme . "\n");
				}
			}

		}
	}

	private function generateReadme($additional = '', $dbchange = FALSE)
	{
		$TEMPLATE = "[oldversion] -> [newversion] Upgrade Guide\n";

		$TEMPLATE .= "This is an update from [oldversion] to [newversion] only. If you are upgrading from any other version besides [oldversion] ,\n";
		$TEMPLATE .= "then you have downloaded the wrong package.  For those users that have been using the current SVN version of e107, from any other version besides [oldversion] ";
		$TEMPLATE .= "this is the correct version to use.\n";

		$TEMPLATE .= "\nIncluded in these releases are security related file changes and so you must upgrade your site with all these files.\n";

		$TEMPLATE .= "\nTo install, simply upload the files to your server overwriting the existing [oldversion] files.\n";

		$TEMPLATE .= ($dbchange == FALSE) ? "There are no database changes in this release." : "This version contains database changes.\n After uploading the files, go to the admin area and click 'Update'.";

		if ($additional)
		{
			$TEMPLATE .= "\n" . $additional . "\n";
		}

		$srch[0] = "[oldversion]";
		$repl[0] = $this->lastversion;

		$srch[1] = "[newversion]";
		$repl[1] = $this->version;

		$text = str_replace($srch, $repl, $TEMPLATE);
		echo("Generating ReadMe Data:  " . $this->lastversion . " -> " . $this->version . "\n");
		return $text;
	}

	public function makeBuild()
	{
		echo date('r') . "<br />Begin Creating Release -> ";
		echo ($this->rc) ? $this->version . " " . $this->rc : $this->version;

		echo "\n\n";

		if ($this->cleanupFiles() === false)
		{
			return;
		}


		if ($this->preprocess())
		{
			$this->createReleases();
			echo "\n\nDONE!!!\n\n\n";
		}
		else
		{
			echo "\n\nERRORS FOUND!";
		}

		return;
	}

	private function cleanupFiles()
	{
		$dir = "{$this->config['baseDir']}/target/{$this->config['main']['name']}";

		if (file_exists($dir))
		{
			$this->status("Cleaning up old target directory ($dir)");
			chdir($dir);
			$cmd = "rm -rf *";
			`$cmd`;


			chdir($this->config['baseDir']);
		}
		else
		{
			$this->status("Creating new target directory ($dir)");
			$cmd = "mkdir -p {$dir}";
			`$cmd`;
		}


		if (file_exists($dir . '/temp'))
		{
			$this->status("Target Directory Not Clean! Aborting...");
			return false;
		}


		$cmd = "mkdir -p {$dir}/temp";
		`$cmd`;

		$cmd = "mkdir -p {$dir}/release";
		`$cmd`;

		$releaseDir = "e107_" . $this->version;

		if ($this->rc)
		{
			$releaseDir .= "_" . $this->rc;
		}

		$this->releaseDir = $releaseDir;

		$this->status("Creating new release directory ($releaseDir)", true);
		$cmd = "mkdir -p {$dir}/release/" . $releaseDir;
		`$cmd`;

		return true;
	}

	private function preprocess()
	{
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

	private function run($cmd)
	{
		$return = `$cmd 2>&1`;

		$this->status($cmd . ":");

		if ($return)
		{
			$this->status(print_r($return, true));
		}
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
				$newfile = "e107_" . $this->config['preprocess']['version'] . "_full";
				$this->status("Creating Release " . $c . " Packages : full", true);
			}
			elseif ($rel['type'] == "upgrade")
			{
				$newfile = "e107_" . $rel['from_version'] . "_to_" . $this->config['preprocess']['version'] . "_upgrade";
				$this->status("Creating Release " . $c . " Packages :  upgrade from {$rel['from_version']}", true);
			}

			if ($this->beta && !$this->rc)
			{
				$newfile .= "_beta_" . date('Ymd');
			}
			elseif ($this->rc)
			{
				$newfile .= "_" . $this->rc;
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
			`$zipcmd`;

			$this->status('Creating TAR archive');
			$this->status($tarcmd);
			`$tarcmd`;

			$this->status('Creating TAR.GZ archive');
			`(cat $tarfile | gzip -9 > $tarfile.gz)`;
//			$this->status('Creating TAR.XZ archive');
//			`(cat $tarfile | xz -9e > $tarfile.xz)`;

			$this->createdFiles[] = array('path' => $releaseDir . "/", 'file' => $newfile . '.zip');
			$this->createdFiles[] = array('path' => $releaseDir . "/", 'file' => $newfile . '.tar');
			$this->createdFiles[] = array('path' => $releaseDir . "/", 'file' => $newfile . '.tar.gz');
//			$this->createdFiles[] = array('path' => $releaseDir . "/", 'file' => $newfile . '.tar.xz');
		} // end loop


	}

	private function emptyExportDir()
	{
		if (is_dir($this->exportDir))
		{
			$this->rmdir($this->exportDir);
			mkdir($this->exportDir, 0755);
		}
		else
		{
			$this->status("Making export directory. ");
			mkdir($this->exportDir, 0755);
		}

	}

	private function rmdir($dir)
	{
		if (!is_dir($dir))
		{
			return false;
		}

		$this->status("Removing directory: " . $dir);

		$dir = rtrim($dir, "/");

		$cmd = "rm -rf {$dir}";
		$this->status($cmd);
		`$cmd`;

		return true;
	}

	private function gitArchive($zipFile, $since = null)
	{
		$file = $this->tempDir . $zipFile;

		$this->status("Zipping up temp Release archive..");

		if (!empty($since))
		{
			$cmd = "git archive -o " . $file . " HEAD $(git diff --name-only --diff-filter=ACMRTUXB " . $since . ")";
		}
		else
		{
			$cmd = "git archive -o " . $file . " HEAD";
		}

		$this->changeDir($this->gitDir);

		$this->run($cmd);
	}

	private function changeDir($dir)
	{
		$this->status("Changing to dir: " . $dir);
		chdir($dir);
	}

	private function gitArchiveUnzip($file)
	{
		$this->status("Unzipping temp archive to export folder", true);
		$filepath = $this->tempDir . $file;
		$cmd = 'unzip -q -o ' . $filepath . ' -d ' . $this->exportDir;

		$this->run($cmd);
		$this->run('chmod -R a=,u+rwX,go+rX ' . escapeshellarg($this->exportDir));
	}

	private function editVersion($dir = 'export')
	{
		$version = $this->config['preprocess']['version'];

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
		chdir($this->config['baseDir']);

		$_current = $this->exportDir;
		$_deprecated = "{$this->config['baseDir']}/deprecated/{$this->config['main']['name']}";

		$_image = $this->tempDir . "core_image.php";

		$this->status("Creating new core_image.php file ({$_image})", true);
		new coreImage($_current, $_deprecated, $_image);

		$dir = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/export";
		chdir($dir);
	}

	private function copyCoreImage()
	{
		$orig = $this->tempDir . "core_image.php";
		$dest = $this->exportDir . "e107_admin/core_image.php";

		if (!file_exists($orig))
		{
			$this->status("ERROR: Image file not found");
		}

		$this->status("Copying Core Image into export directory", true);
		$this->run("/bin/cp -rf " . $orig . " " . $dest);

		if (!file_exists($dest))
		{
			$this->status("ERROR: Image file didnt copy.");
		}

	}

	private function moveReadme($readme)
	{
		$from = "{$this->config['baseDir']}/readme/{$this->config['main']['name']}/{$readme}";
		$to = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/export/README.txt";
		$result = copy($from, $to);
		$this->status("Copying readme file $readme to $to - " . ($result ? "SUCCESS" : "FAIL"));
	}

	function deleteAll($directory, $empty = false)
	{
		if (substr($directory, -1) == "/")
		{
			$directory = substr($directory, 0, -1);
		}

		if (!file_exists($directory) || !is_dir($directory))
		{
			return false;
		}
		elseif (!is_readable($directory))
		{
			return false;
		}
		else
		{
			$directoryHandle = opendir($directory);

			while ($contents = readdir($directoryHandle))
			{
				if ($contents != '.' && $contents != '..')
				{
					$path = $directory . "/" . $contents;

					if (is_dir($path))
					{
						$this->deleteAll($path);
					}
					else
					{
						unlink($path);
					}
				}
			}

			closedir($directoryHandle);

			if ($empty == false)
			{
				if (!rmdir($directory))
				{
					return false;
				}
			}

			return true;
		}
	}
}

/*****************************************************************************************
 ******************************************************************************************
 ******************************************************************************************/
class coreImage
{
	public function __construct($_current, $_deprecated, $_image)
	{
		global $coredir;
		set_time_limit(240);

		define("IMAGE_CURRENT", $_current);
		define("IMAGE_DEPRECATED", $_deprecated);
		define("IMAGE_IMAGE", $_image);

		$maindirs = array(
			'admin' => 'e107_admin/',
			'files' => 'e107_files/',
			'images' => 'e107_images/',
			'themes' => 'e107_themes/',
			'plugins' => 'e107_plugins/',
			'handlers' => 'e107_handlers/',
			'languages' => 'e107_languages/',
			'downloads' => 'e107_files/downloads/',
			'docs' => 'e107_docs/'
		);

		foreach ($maindirs as $maindirs_key => $maindirs_value)
		{
			$coredir[$maindirs_key] = substr($maindirs_value, 0, -1);
		}

		$this->create_image(IMAGE_CURRENT, IMAGE_DEPRECATED);
	}

	function create_image($_curdir, $_depdir)
	{
		global $coredir;

		$search = $replace = [];
		foreach ($coredir as $trim_key => $trim_dirs)
		{
			$search[$trim_key] = "'" . $trim_dirs . "'";
			$replace[$trim_key] = "\$coredir['" . $trim_key . "']";
		}

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

		$scan_current = $this->scan($_curdir);


		echo("[Core-Image] Scanning Dir: " . $_curdir . "\n");


		$image_array = var_export($scan_current, true);
		$image_array = str_replace($search, $replace, $image_array);
		$data .= "\$core_image = " . $image_array . ";\n\n";

		$scan_deprecated = $this->scan($_depdir, $scan_current);
		$image_array = var_export($scan_deprecated, true);
		$image_array = str_replace($search, $replace, $image_array);
		$data .= "\$deprecated_image = " . $image_array . ";\n\n";
		$data .= "?>";

		$fp = fopen(IMAGE_IMAGE, 'w');
		fwrite($fp, $data);
	}

	function scan($dir, $image = array())
	{
		$absoluteBase = realpath($dir);
		if (!is_dir($absoluteBase)) return false;

		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
		$files = [];

		/**
		 * @var $file DirectoryIterator
		 */
		foreach ($iterator as $file)
		{
			if ($file->isDir()) continue;

			$absolutePath = $file->getRealPath();
			$relativePath = preg_replace("/^" . preg_quote($absoluteBase . "/", "/") . "/", "", $absolutePath);

			if (empty($relativePath) || $relativePath == $absolutePath) continue;

			self::array_set($files, $relativePath, $this->checksum($absolutePath));
		}

		ksort($files);

		return $files;
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

	function checksum($filename)
	{
		return md5(str_replace(array(chr(13), chr(10)), '', file_get_contents($filename)));
	}
}
