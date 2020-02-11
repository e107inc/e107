<?php
/*
if($argc !== 2) {
	showUsage();
	exit;
}

echo "Begin -> ".date('r')."\n";
// Read in make.ini file, create $config

$build = new e107Build($argv[1]);
//var_dump($build->config);

exit;*/

function showUsage()
{
	echo "\nUsage: {$_SERVER['argv'][0]} <config_file_name>\n\n";
}

class e107Build
{

	public $config, $path, $version, $tag, $lastversion, $lastversiondate, $dbchange, $beta, $error, $rc;

	var $createdFiles = array();
	var $releaseDir = "";

	var $pause = false;

	var $tempDir = null;
	var $exportDir = null;
	var $gitDir = null;
	var $gitRepo = null;


	public function __construct()
	{
		$this->beta = false;
		$this->error = false;
		$this->live = false;
		$this->testCoreImage = false;
		$this->rc = false;


		//	$this->config['baseDir'] = realpath(getcwd());
		$this->config['baseDir'] = dirname(__FILE__);

	}

	public function init($module)
	{
		$iniFile = $this->config['baseDir'] . '/config/config_' . $module . '.ini';

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
		$this->gitDir = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/checkout/";
		$this->gitRepo = $this->config['main']['git_repo'];
		/*
				if(is_dir($this->tempDir))
				{
					$this->rmdir($this->tempDir);
					mkrdir($this->tempDir,0755);
				}
		*/


		if (!$this->version)
		{
			echo "Error: No Version Set\n"; // eg. 0.7.22
			$this->error = TRUE;
			return;
		}

		if (!$this->tag)
		{
			echo "Error: No Tag Set\n"; // eg. e107_v07_22_release	
			$this->error = TRUE;
		}

		$this->config['preprocess']['version'] = $this->version;
		$this->config['preprocess']['tag'] = $this->tag;

		//	$this->buildLastConfig();

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

		if ($this->testCoreImage)
		{
			return false;
		}

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

		$cmd = "mkdir -p {$dir}/checkout";
		`$cmd`;

		$cmd = "mkdir -p {$dir}/release";
		`$cmd`;

		//	$cmd = "mkdir -p {$dir}/export";
		//	`$cmd`;


		$releaseDir = "e107_" . $this->version;

		if ($this->rc)
		{
			$releaseDir .= "_" . $this->rc;
		}

		$this->releaseDir = $releaseDir;

		$this->status("Creating new release directory ($releaseDir)", true);
		$cmd = "mkdir -p {$dir}/release/" . $releaseDir;
		`$cmd`;

	}

	private function preprocess()
	{
		//Update current cvs
		/*
		$this->checkoutSvn();

		$checkedOutFiles = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/checkout/e107_admin/ver.php";
		if(!file_exists($checkedOutFiles) && !$this->beta)
		{
			$this->status("Checkout Failed!");
			return FALSE;
		}


		//Edit ver.php file with new version
		if(isset($this->config['preprocess']['version']) && !$this->beta)
		{
			if($this->editVersion()) // /checkout not export.
			{
				//Commit new ver.php file to cvs
				$this->commitFile("e107_admin/ver.php", "new version: {$this->config['preprocess']['version']} - auto");
			}
			else
			{
				$this->status("Error updating version");
				return false;
			}
		}
		*/

		$this->gitClone();

		//$this->editVersion('checkout');

		//	$this->exportSvn(); //Export files to export dir

		//	$this->editVersion('export'); // needed because we don't commit the version change with betas.

		if (trim($this->config['preprocess']['plugin_delete']) != '')
		{
			//		$this->pluginRemove($this->config['preprocess']['plugin_delete']);
		}

		//	$this->rmdir($this->exportDir."e107_themes/bootstrap");

		return true;
		/*

			if(!$this->beta) // Official Release.
			{
				$this->CreateCoreImage(); //Create the new image file

				//Commit new image file to cvs
				$this->commitFile("e107_admin/core_image.php", "Update image file: {$this->config['preprocess']['version']} - auto");

				$this->tagFiles($this->config['preprocess']['tag']); //Tag all files with new tag

				//Create clean export of new tag
				//$this->exportSvn($this->config['preprocess']['tag']);
				// ** No longer export via tag, since the $URL:$ substitution will mess up file inspector
				$this->exportSvn();
			}
			else // Beta - we don't do any commits.
			{
				$this->editVersion('export'); // needed because we don't commit the version change with betas.

				if(trim($this->config['preprocess']['plugin_delete']) != '')
				{
					$this->pluginRemove($this->config['preprocess']['plugin_delete']);
				}

				// $this->CreateCoreImage(); // Create Image
			}

			return true;
		*/
	}

	private function gitClone()
	{
		if (empty($this->gitRepo))
		{
			$this->status("No Repo  selected");
			return false;
		}

		$this->status("Cloning git repo", true);

		$this->run("git clone " . $this->gitRepo . " " . $this->gitDir);
		//	$this->run("mv ".$tempDir."/.git ".$repoDir."/.git");
		//	$this->run("mv ".$tempDir."/* ".$repoDir);
		//	$this->run("/bin/cp -rfv ".$tempDir."/* ".$repoDir);
		//	$this->run("git --work-tree=".$repoDir." --git-dir=".$repoDir."/.git pull");
		//	$this->run("chown -R ".$dir.":".$dir." ".$repoDir);
		$this->run("chmod 0755 " . $this->gitDir);

		if (!is_dir($this->gitDir . "/.git"))
		{
			$this->status("There was a problem. Check your setup:\n
			cd /usr/bin/<br />
			sudo ln -s /usr/local/cpanel/3rdparty/bin/git* .<br />
			git --version
			<br /><br />

			Make sure TCP port 9418 is open!");
		}

	}

	private function run($cmd)
	{
		$return = `$cmd 2>&1`;

		$this->status($cmd . ":");

		if ($return)
		{
			//	$return = $this->parseReturn($return);
			//	$this->lastRunMessage = $return;
			$this->status(print_r($return, true));
		}


		//	print_r($return);
		// $this->alert($return);
	}

	private function createReleases()
	{

		foreach ($this->config['releases'] as $c => $rel)
		{
			$this->status(" ------------------ Release " . $c . "--------------------------- ", true);

			$this->emptyExportDir();

			//	$this->pause(25);

			$zipExportFile = 'release_' . $c . ".zip";

			$this->gitArchive($zipExportFile, $rel['since']);

			//	$this->pause(25);

			$this->gitArchiveUnzip($zipExportFile);


			//	$this->pause(25);

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

			$this->pause(20);


			$this->copyCoreImage();

			$this->pause(20);

			if (isset($rel['readme']))
			{
				$this->moveReadme($rel['readme']);
			}

			$zipsince = '';
			$tarsince = '';
			$ts = '';
			/*
						if(!empty($rel['since']))
						{
							$zipsince = "t {$rel['since']}";
							$ts = "--newer-mtime=".substr($rel['since'], 4)."-".substr($rel['since'], 0, 2)."-".substr($rel['since'], 2, 2);
							$tarsince = "--newer={$ts}";
							$reftime = substr($rel['since'], 4).substr($rel['since'], 0, 4).'0001';
							$this->gitArchive('release_'.$rel, $rel['since']);
						}*/

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


			// $newfile

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
			$gzfile = $releaseDir . '/' . $newfile . '.tar.gz';

			$zipcmd = "zip -r{$zipsince} $zipfile * >/dev/null 2>&1";
			$tarcmd = "tar cz {$ts} -f$gzfile * >/dev/null 2>&1";

//touch -t 201003140001 ../reftime && find . -newer ../reftime -type f -print0 | xargs -0 zip ../release/mytest2.zip
			//	$xzipcmd = "touch -t {$reftime} ../reftime && find . -newer ../reftime -type f -print0 | xargs -0 zip {$zipfile} >/dev/null 2>&1";
			//	$xtarcmd = "touch -t {$reftime} ../reftime && find . -newer ../reftime -type f -print0 | xargs -0 tar --no-recursion -zc -f{$gzfile} >/dev/null 2>&1";


			$this->status('Creating ZIP archive');
			$this->status($zipcmd);
			`$zipcmd`;

			$this->status('Creating TAR.GZ archive');
			$this->status($tarcmd);
			`$tarcmd`;

			/*	else // Doesn't make a zip/tar with empty directories.
				{
					$this->status('Creating Alternate ZIP release');
					$this->status($xzipcmd);
					`$xzipcmd`;

					$this->status('Creating Alternate TAR.GZ release');
					$this->status($xtarcmd);
					`$xtarcmd`;
				}*/

			$this->createdFiles[] = array('path' => $releaseDir . "/", 'file' => $newfile . '.zip');
			$this->createdFiles[] = array('path' => $releaseDir . "/", 'file' => $newfile . '.tar.gz');

			if ($rel['plugin_delete'])
			{
				//	$this->pluginRemove($rel['plugin_delete'],true);
			}


		} // end loop


	}

	private function emptyExportDir()
	{
		if (is_dir($this->exportDir))
		{
			//$this->status("Cleaning out export directory. ");
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

		if (empty($dir))
		{
			return false;
		}

		$this->status("Removing directory: " . $dir);

		$dir = rtrim($dir, "/");

		$cmd = "rm -rf {$dir}/*";
		$this->status($cmd);
		`$cmd`;
		$cmd = "rmdir {$dir}";
		$this->status($cmd);
		`$cmd`;

		//	$found = scandir($dir);

		//	$this->status("Remaining files: ".implode(", ",$found));

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

	}

	private function editVersion($dir = 'checkout')
	{

		$version = $this->config['preprocess']['version'];

		if ($this->beta && !$this->rc)
		{
			$version .= " beta build " . date('Ymd');
		}
		elseif ($this->rc)
		{
			$version .= " " . $this->rc;
		}


		$fname = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/" . $dir . "/e107_admin/ver.php";

		$this->status("Writing new version {$version} to ver.php in " . $dir . " directory.", true);

		$contents = "<?php\n";
		$contents .= "/*\n";
		$contents .= "* Copyright (c) " . date("Y") . " e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)\n";
		//	$contents .= "* \$URL$\n";
		//	$contents .= "* \$Id$\n";
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

		//	$this->changeDir($this->exportDir);

		foreach ($fnames as $fn)
		{
			$fn = trim($fn);
			if (file_exists($fn))
			{
				$result = unlink($fn);
				$this->status("Deleting $fn - " . ($result ? "SUCCESS" : "FAIL"));
			}
			else
			{
				$this->status("File already deleted or abscent - " . $fn);
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
			$temp = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/temp/" . $fn;
			//	$result = rename($dir,$temp);


			//	$cmd = ($restore == false) ? "mv {$dir} {$temp}" :  "mv {$temp} {$dir}";;
			//	$this->status($cmd);
			$this->rmdir($dir);

			/*	if($restore)
				{
					$this->status("Restoring Plugin: {$fn} ");
				}
				else
				{
					$this->status("Removing Plugin: {$fn} ");
				}*/
		}
	}

	private function createCoreImage()
	{
		//	$dir = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/export";
		//	$this->exportDir;
		//	chdir($dir);

		//Delete or create any files as per config

		if ($this->testCoreImage != TRUE)
		{
			//	$this->filesDelete($this->config['preprocess']['files_delete']);
			//	$this->filesCreate($this->config['preprocess']['files_create']);
		}
		//create new image file - writes directly to cvsroot
		chdir($this->config['baseDir']);

		$_current = $this->exportDir;
		$_deprecated = "{$this->config['baseDir']}/deprecated/{$this->config['main']['name']}";

		/*	if(!$this->beta)
			{
				$_image = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/checkout/e107_admin/core_image.php";
			}
			else
			{
				$_image = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/export/e107_admin/core_image.php";
			}
		*/

		if ($this->testCoreImage)
		{
			$_image = "{$this->config['baseDir']}/test_core_image.php";
		}
		else
		{
			$_image = $this->tempDir . "core_image.php";
		}

		$this->status("Creating new core_image.php file ({$_image})", true);
		new coreImage($_current, $_deprecated, $_image);

		$dir = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/export";
		chdir($dir);
	}

	private function pause($seconds)
	{

		if ($this->pause !== true)
		{
			return false;
		}

		$this->status("   (Pausing for " . $seconds . " seconds...)", true);
		sleep($seconds);
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

	public function coreImageTest()
	{
		//$this->checkoutSvn();
		//Export files to export dir
		//$this->exportSvn();
		$this->CreateCoreImage();
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

	private function checkoutSvn()
	{

		return false;

		if ($this->beta)
		{
			$this->status("Beta Mode - skipping svn checkout");
			return;
		}


		$dir = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/checkout";
		$this->status("Checking out current svn to {$dir}");
		$cmd = "svn checkout --username {$this->config['main']['svn_username']} --password {$this->config['main']['svn_password']} --no-auth-cache --non-interactive {$this->config['main']['svn_path']} {$dir}";
		$this->status($cmd);
		`$cmd`;
	}

	private function exportSvn($tag = '')
	{


		if (is_dir($this->exportDir))
		{
			$this->status("Cleaning out export directory. ");
			$this->rmdir($this->exportDir);
		}

		if (is_dir($this->tempDir))
		{
			$this->status("Cleaning out temp directory. ");
			$this->rmdir($this->tempDir);
			mkdir($this->tempDir, 0755);
		}


		$this->status("Exporting from Github to temp directory  ");

		/*
		if($tag == '')
		{
			$this->status("Exporting current svn to {$dir}");
			$path = $this->config['main']['svn_path'];
		}
		else
		{
			$this->status("Exporting tag {$tag} to {$dir}");
			$path = "{$this->config['main']['svn_tag_path']}/{$tag}";
		}
		*/

		$filePath = $this->tempDir . "master.zip";
		$cmd = 'wget https://codeload.github.com/e107inc/e107/zip/master -O ' . $filePath; // , 'e107-master.zip', 'temp');

		//	$cmd = "svn export --username {$this->config['main']['svn_username']} --password {$this->config['main']['svn_password']} --no-auth-cache --non-interactive --force  {$path} {$dir}";
		$this->status($cmd);


		`$cmd`;


		if (!file_exists($filePath))
		{
			$this->status("FAIL: Couldn't retrieve zip file from github.");
			return false;
		}

		$cmd = 'unzip -o ' . $filePath . ' -d ' . $this->tempDir;
		$this->status($cmd);

		`$cmd`;

		if (!is_dir($this->tempDir . "e107-master"))
		{
			$this->status("FAIL: Couldn't unzip ." . $filePath . " to " . $this->tempDir);
			return false;
		}

		$cmd = "mv " . $this->tempDir . "e107-master " . $this->exportDir;


		$this->status($cmd);

		`$cmd`;

		if (!is_dir($this->exportDir))
		{
			$this->status("GIT Export FAILED for some reason");

		}
	}

	private function commitFile($fname, $message = "no remarks")
	{
		if (($this->beta == TRUE) || ($this->live != TRUE) || $this->rc)
		{
			return;
		}

		$dir = "{$this->config['baseDir']}/target/{$this->config['main']['name']}/checkout";
		chdir($dir);
		$cmd = "svn commit --username {$this->config['main']['svn_username']} --password {$this->config['main']['svn_password']} --no-auth-cache --non-interactive -m \"{$message}\" {$fname}";
		$this->status("commiting $fname to svn");
		$this->status($cmd);
		return `$cmd`;
//		return true;
	}

	private function tagFiles($tagid)
	{
		if (($this->beta == TRUE) || $this->live != TRUE || $this->rc)
		{
			return;
		}

		$this->status("Creating Tag of $tagid");
		$cmd = "svn copy --username {$this->config['main']['svn_username']} --password {$this->config['main']['svn_password']} --no-auth-cache --non-interactive -m \"Auto creating of tag during build\" {$this->config['main']['svn_path']} {$this->config['main']['svn_tag_path']}/{$tagid}";
		$this->status($cmd);
		return `$cmd`;
	}


}

/*****************************************************************************************
 ******************************************************************************************
 ******************************************************************************************/
class coreImage
{

	var $file;
	var $image = array();

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

//		echo IMAGE_IMAGE;
//		include(IMAGE_IMAGE);
//		die("xxx\n");
		$this->create_image(IMAGE_CURRENT, IMAGE_DEPRECATED);
	}

	function create_image($_curdir, $_depdir)
	{
		global $core_image, $deprecated_image, $coredir;

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
		$data .= "|     Copyright (C) 2008-2010 e107 Inc. \n";
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

//		echo "writing to ".IMAGE_IMAGE."\n";

		//	echo "\n------- Core Image --------\n";
		//	echo $data;
		//	echo "\n------- End Image --------\n";


		$fp = fopen(IMAGE_IMAGE, 'w');
//		echo "open results = [{$fp}]\n";
		fwrite($fp, $data);
	}

	function scan($dir, $image = array())
	{
//		echo "Scanning directory $dir \n";
		$handle = opendir($dir . '/');

		$exclude = array('e107_config.php', 'install.php', 'CVS', '.svn', 'Thumbs.db', '.gitignore');

		while (false !== ($readdir = readdir($handle)))
		{
			if ($readdir != '.' && $readdir != '..' && $readdir != '/' && !in_array($readdir, $exclude) && (strpos('._', $readdir) === FALSE))
			{
				$path = $dir . '/' . $readdir;
				if (is_dir($path))
				{
					$dirs[$path] = $readdir;
				}
				else if (!isset($image[$readdir]))
				{
					$files[$readdir] = $this->checksum($path, TRUE);
				}
			}
		}
		closedir($handle);

		if (isset($dirs))
		{
			ksort($dirs);
			foreach ($dirs as $dir_path => $dir_list)
			{
				$list[$dir_list] = ($set = $this->scan($dir_path, $image[$dir_list])) ? $set : array();
			}
		}

		if (isset($files))
		{
			ksort($files);
			foreach ($files as $file_name => $file_list)
			{
				$list[$file_name] = $file_list;
			}
		}

		return $list;
	}

	function checksum($filename)
	{
		$checksum = md5(str_replace(array(chr(13), chr(10)), '', file_get_contents($filename)));
		return $checksum;
	}
}
