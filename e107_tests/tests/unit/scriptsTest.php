<?php


	class scriptsTest extends \Codeception\Test\Unit
	{


		protected function _before()
		{
			if(!defined('SEP'))
			{
				define("SEP", " <span class='fa fa-angle-double-right e-breadcrumb'></span> ");
			}

			e107::loadAdminIcons();
		}

		public function testAdminScripts()
		{
			$exclude = array(
				'index.php',
				'menus.php', // FIXME menus defines e_ADMIN_AREA which messes up other tests.
				'header.php',
				'footer.php'
			);
			
			$this->loadScripts(e_ADMIN, $exclude);

		}

		public function testAdminIncludes()
		{
			ob_start();
			require_once(e_ADMIN."admin.php");
			ob_end_clean();
			$this->loadScripts(e_ADMIN."includes/");

		}

		public function testAdminLayouts()
		{
			$this->loadScripts(e_ADMIN.'includes/layouts/');
		}


		public function testFrontend()
		{
			e107::getConfig()->setPref('plug_installed/gsitemap', '1.0');

			$include = 	array (
				  0 => 'banner.php',
			//	  1 => 'class2.php',
			//	  2 => 'comment.php',
				  3 => 'contact.php',

			//	  5 => 'cron.php',
			//	  6 => 'download.php',
			//	  7 => 'e107_config.php',

			//	  12 => 'email.php',
				  13 => 'error.php',

				  15 => 'fpw.php',
				  16 => 'gsitemap.php',
			//	  17 => 'index.php', // redirects
			//	  18 => 'install.php', // not compatible with core.

				  20 => 'login.php',
				  21 => 'membersonly.php',
			//	  22 => 'metaweblog.php',
				  23 => 'news.php',
				  24 => 'online.php',
				  25 => 'page.php',
			//	  26 => 'print.php',
			//	  27 => 'rate.php', // has a redirect.
			//	  28 => 'request.php', // redirects
				  29 => 'search.php',
			//	  30 => 'signup.php', too many 'exit';
				  31 => 'sitedown.php',
				  32 => 'submitnews.php',

			//	  34 => 'thumb.php', // separate test.
				  35 => 'top.php',
				  36 => 'unsubscribe.php',
		//		  37 => 'upload.php', // FIXME LAN conflict.
				  38 => 'user.php',
			//	  39 => 'userposts.php', // FIXME needs a rework
				  40 => 'usersettings.php',
				);

			$this->loadScripts(e_BASE, array(), $include);
		}



		private function loadScripts($folder, $exclude = array(), $include = array())
		{
			$list = scandir($folder);

			$config = e107::getConfig();

			// Pre-register certain plugins if needed
			$preInstall = array('banner', 'page');
			foreach ($preInstall as $plug) {
				$config->setPref('plug_installed/' . $plug, '1.0');
			}

			global $pref, $ns, $tp, $frm;
			$pref = e107::getPref();
			$ns = e107::getRender();
			$tp = e107::getParser();
			$frm = e107::getForm();

			global $_E107;
			$_E107['cli'] = true;

			$e107Root = realpath(__DIR__ . '/../../../');
			$class2Path = $e107Root . '/class2.php';
			$lanAdminPath = $e107Root . '/e107_languages/English/admin/lan_admin.php';

			if (!file_exists($class2Path)) {
				$this->fail("Could not locate class2.php at $class2Path");
				return;
			}
			if (!file_exists($lanAdminPath)) {
				$this->fail("Could not locate lan_admin.php at $lanAdminPath");
				return;
			}

			fwrite(STDOUT, "Loading scripts from: $folder\n");

			$filesToTest = [];
			$alreadyProcessed = []; // To avoid duplicate processing

			foreach ($list as $file) {
				// Skip directories "." and ".." or any unintended duplicates
				if ($file === '.' || $file === '..') {
					continue;
				}

				$ext = pathinfo($folder . $file, PATHINFO_EXTENSION);
				$filePath = realpath($folder . $file); // Get canonicalized absolute path

				// Skip directories, duplicates, excluded files, or files not in the include list
				if (
					is_dir($filePath) ||
					in_array($filePath, $alreadyProcessed) ||  // Skip files already processed
					$ext !== 'php' ||
					in_array($file, $exclude) ||
					(!empty($include) && !in_array($file, $include))
				) {
					continue;
				}

				fwrite(STDOUT, " - $file\n");
				$filesToTest[$file] = $filePath;

				// Mark this file as processed
				$alreadyProcessed[] = $filePath;
			}

			if (empty($filesToTest)) {
				fwrite(STDOUT, "No scripts to test in $folder\n");
				return;
			}

			// Prepare dynamic error-catching and script-loading logic
			$phpCode = "<?php\n";
			$phpCode .= "require_once '" . addslashes($class2Path) . "';\n";
			$phpCode .= "restore_error_handler();\n";
			$phpCode .= "error_reporting(E_ALL | E_STRICT);\n";
			$phpCode .= "ini_set('display_errors', 1);\n";
			$phpCode .= "ini_set('log_errors', 0);\n";
			foreach ($filesToTest as $file => $filePath) {
				$phpCode .= "echo 'START: " . addslashes($file) . "\\n';\n";
				$phpCode .= "try {\n";
				$phpCode .= "    require_once '" . addslashes($filePath) . "';\n";
				$phpCode .= "} catch (Throwable \$e) {\n";
				$phpCode .= "    echo 'Error in $file: ' . \$e->getMessage() . ' on Line ' . \$e->getLine() . '\\n';\n";
				$phpCode .= "}\n";
				$phpCode .= "echo 'END: " . addslashes($file) . "\\n';\n";
			}

			// Write the generated code to a temporary PHP file
			$tmpFile = tempnam(sys_get_temp_dir(), 'loadScripts_') . '.php';
			file_put_contents($tmpFile, $phpCode);

			try {
				$errors = [];
				foreach ($filesToTest as $file => $filePath) {
					// Check for syntax errors using `php -l`
					$lintCommand = sprintf('php -l "%s"', addslashes($filePath));
					exec($lintCommand, $lintOutput, $lintExitCode);

					if ($lintExitCode !== 0) {
						// Log syntax errors explicitly
						$errors[] = "Syntax error in $file: " . implode("\n", $lintOutput);
						fwrite(STDOUT, "Syntax error in $file: " . implode("\n", $lintOutput) . "\n");
						continue;
					}
				}

				// Run the temporary PHP script
				$command = sprintf('php "%s"', $tmpFile);
				$descriptors = [
					0 => ['pipe', 'r'], // stdin
					1 => ['pipe', 'w'], // stdout
					2 => ['pipe', 'w'], // stderr
				];
				$process = proc_open($command, $descriptors, $pipes);

				if (!is_resource($process)) {
					$this->fail("Failed to start process for $folder");
					return;
				}

				fclose($pipes[0]); // No input needed
				$stdout = stream_get_contents($pipes[1]);
				$stderr = stream_get_contents($pipes[2]);
				fclose($pipes[1]);
				fclose($pipes[2]);

				$exitCode = proc_close($process);

				// Parse regular runtime errors and warnings
				if ($exitCode !== 0 || !empty($stderr)) {
					if (!empty($stdout)) {
						// Parse START-END blocks or error output
						if (preg_match_all('/START: (.*?)\n(.*?)(\nEND: \1|$)/is', $stdout, $matches, PREG_SET_ORDER)) {
							foreach ($matches as $match) {
								$file = $match[1];
								$blockOutput = trim($match[2]);

								if (preg_match('/(Parse error|Fatal error|Warning|Notice|Error):.*in\s+([^\s]+)\s+on\s+line\s+(\d+)/i', $blockOutput, $errorMatch)) {
									$errorMessage = $errorMatch[0];
									$error = "Error in $file: $errorMessage";
									fwrite(STDOUT, "$error\n");
									$errors[] = $error;
								} elseif ($blockOutput) {
									$error = "Unexpected output in $file: $blockOutput";
									fwrite(STDOUT, "$error\n");
									$errors[] = $error;
								}
							}
						}
					}
					if (!empty($stderr)) {
						$error = "Error in $folder: " . trim($stderr);
						fwrite(STDOUT, "$error\n");
						$errors[] = $error;
					}
				}

				// Report the errors or confirm success
				if (!empty($errors)) {
					$this->fail("Errors found in scripts:\n" . implode("\n", $errors));
				} else {
					$this->assertTrue(true, "All scripts in $folder loaded successfully");
				}
			} finally {
				// Cleanup: remove the temporary file
				unlink($tmpFile);
			}
		}



	}
