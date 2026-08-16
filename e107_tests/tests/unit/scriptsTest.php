<?php


	/**
	 * Loads every admin and front-end entry script and fails on anything PHP
	 * complains about.
	 *
	 * One script per process. That is what makes the sweep trustworthy: a
	 * script cannot define a constant, unset a superglobal or exit() its way
	 * into another script's result, so a failure names the script that caused
	 * it and an exclusion is a statement about a script rather than about the
	 * loader. The sweep that shared one child between every script is how
	 * issue #5908 happened, by way of the two entry scripts that call
	 * unset($_POST).
	 *
	 * The verdict is the child's exit status and its stderr, with
	 * display_errors pointed at stderr so PHP's diagnostics arrive separately
	 * from the page the script rendered. Judging on output alone cannot work:
	 * a warning on the CLI SAPI leaves the exit status at 0 and writes nothing
	 * to stderr by default, so the previous sweep, which only inspected output
	 * after a non-zero exit, could not fail on the one thing it most needed to
	 * catch.
	 *
	 * Deprecations are excluded. They are a backlog of their own and would
	 * bury the breakage this is looking for.
	 */
	class scriptsTest extends \Codeception\Test\Unit
	{
		/**
		 * Children in flight at once. Eight is also what surfaces a race in
		 * code every request touches, so lowering it hides those.
		 */
		const CONCURRENCY = 8;

		/** Generous for a script that loads in well under a second. */
		const TIMEOUT_SECONDS = 60;

		/**
		 * Every child gets its own address out of TEST-NET-2 (RFC 5737).
		 *
		 * e107 flood-controls by IP and bans at around a hundred hits within
		 * its window. A CLI process has no REMOTE_ADDR, so without this every
		 * child in the sweep is the same visitor, the sweep bans itself part
		 * way through, and each later test that bootstraps e107 dies on "Your
		 * IP is banned!" instead of on anything to do with itself. One script
		 * is one visitor, which is also what the sweep is modelling.
		 */
		const REMOTE_ADDR_PREFIX = '198.51.100.';

		public function testAdminScripts()
		{
			$exclude = array(
				// Defines e_ADMIN_AREA, USER_AREA and ADMIN_AREA before it
				// loads class2.php, and the sweep has already loaded
				// class2.php by the time it gets here, so the block is skipped
				// and a plugin's e_header.php then reads USER_AREA undefined.
				'menus.php',
				// Included by admin.php, never requested on their own, and
				// covered by every case that passes --admin-header.
				'header.php',
				'footer.php',
			);

			$this->sweep(e_ADMIN, $exclude, array('--admin-area'));
		}

		public function testAdminIncludes()
		{
			$this->sweep(e_ADMIN . 'includes/', array(), array('--admin-area', '--admin-header'));
		}

		public function testAdminLayouts()
		{
			$this->sweep(e_ADMIN . 'includes/layouts/', array(), array('--admin-area', '--admin-header'));
		}

		public function testFrontend()
		{
			$exclude = array(
				// The bootstrap and the configuration it reads, not entry scripts.
				'class2.php',
				'e107_config.php',
				// Redeclares check_class() on top of the one class2.php defines,
				// which is what "not compatible with core" meant.
				'install.php',
				// Bootstraps a minimal environment of its own, so it collides with
				// the one the sweep has already loaded. It has its own test.
				'thumb.php',
				// Sends the queued mail on load, so sweeping it needs an MTA and
				// posts real messages.
				'cron.php',
			);

			$this->sweep(e_BASE, $exclude);
		}

		public function testACleanScriptReportsClean()
		{
			$report = $this->onlyReport($this->runScripts(array($this->fixture('clean.php'))));

			$this->assertTrue($this->isClean($report), $report['stderr']);
			$this->assertTrue(
				strpos($report['stdout'], 'clean fixture loaded') !== false,
				'the sweep has to run the script, not merely start a process for it'
			);
		}

		public function testADiagnosticFailsAScriptEvenThoughTheExitStatusStaysZero()
		{
			$report = $this->onlyReport($this->runScripts(array($this->fixture('emits_notice.php'))));

			$this->assertSame(0, $report['exitCode'], 'a notice leaves the exit status alone, which is why exit status is not enough');
			$this->assertNotEmpty(trim($report['stderr']), 'the diagnostic has to reach stderr or the sweep cannot see it');
			$this->assertFalse($this->isClean($report));
		}

		public function testAScriptThatExitsPassesAndDoesNotHideTheNextOne()
		{
			$exits  = $this->fixture('exits_early.php');
			$notice = $this->fixture('emits_notice.php');

			$reports = $this->runScripts(array($exits, $notice));

			$this->assertCount(2, $reports, 'an exit() must not truncate the sweep');
			$this->assertTrue($this->isClean($reports[$exits]), 'exit() is how an e107 entry script finishes');
			$this->assertFalse($this->isClean($reports[$notice]), 'the script after the exit still has to be judged on its own');
		}

		public function testOneScriptCannotUnsetASuperglobalOnAnother()
		{
			$unsets = $this->fixture('unsets_post.php');
			$needs  = $this->fixture('needs_post.php');

			$reports = $this->runScripts(array($unsets, $needs));

			$this->assertTrue($this->isClean($reports[$unsets]), $reports[$unsets]['stderr']);
			$this->assertTrue(
				$this->isClean($reports[$needs]),
				"unset(\$_POST) reached the next script, so the sweep is not isolating them; see #5908\n" . $reports[$needs]['stderr']
			);
		}

		public function testAScriptAlreadyLoadedByTheBootstrapIsNotReportedAsAPass()
		{
			$report = $this->onlyReport($this->runScripts(array(APP_PATH . '/class2.php')));

			$this->assertFalse(
				$this->isClean($report),
				'class2.php is loaded before the sweep reaches it, so require_once is a no-op that must not read as a pass'
			);
			$this->assertTrue(strpos($report['stderr'], 'already loaded') !== false, $report['stderr']);
		}

		/**
		 * @param string $folder
		 * @param array  $exclude file names to skip, each with the reason why
		 * @param array  $flags   probe flags, @see _data/scriptsTest/probe.php
		 * @return void
		 */
		private function sweep($folder, array $exclude = array(), array $flags = array())
		{
			$paths = $this->scriptsIn($folder, $exclude);

			$this->assertNotEmpty($paths, 'No scripts to sweep in ' . $folder);

			$started = microtime(true);
			$reports = $this->runScripts($paths, $flags);

			fwrite(STDOUT, sprintf("\n%s: %d scripts in %.2fs\n", $folder, count($reports), microtime(true) - $started));

			$this->assertScriptsAreClean($reports);
		}

		/**
		 * @return array absolute paths, in scandir order
		 */
		private function scriptsIn($folder, array $exclude)
		{
			$folder = rtrim($folder, '/\\') . '/';
			$paths  = array();

			foreach(scandir($folder) as $file)
			{
				if(pathinfo($file, PATHINFO_EXTENSION) !== 'php') { continue; }
				if(in_array($file, $exclude, true)) { continue; }

				$path = realpath($folder . $file);

				if($path === false || is_dir($path) || in_array($path, $paths, true)) { continue; }

				codecept_debug(' - ' . $file);
				$paths[] = $path;
			}

			return $paths;
		}

		/**
		 * @return array report per script, keyed by the path it was given
		 */
		private function runScripts(array $paths, array $flags = array())
		{
			$reports = array();
			$queue   = array_values($paths);
			$address = 0;

			// e107 writes its caches with a non-atomic file_put_contents, so
			// opening the pool on a cold cache races several children through
			// the same write. One script on its own warms it.
			if(!empty($queue))
			{
				$this->runToCompletion(array_shift($queue), $flags, ++$address, $reports);
			}

			$running = array();
			$nextId  = 0;

			while(!empty($queue) || !empty($running))
			{
				while(!empty($queue) && count($running) < self::CONCURRENCY)
				{
					$running[$nextId++] = $this->start(array_shift($queue), $flags, ++$address);
				}

				$this->pump($running, $reports);
			}

			return $reports;
		}

		private function runToCompletion($path, array $flags, $address, array &$reports)
		{
			$running = array($this->start($path, $flags, $address));

			while(!empty($running))
			{
				$this->pump($running, $reports);
			}
		}

		/**
		 * Drains every open child once, then reaps the ones that finished or
		 * ran out of time.
		 */
		private function pump(array &$running, array &$reports)
		{
			$open = array();

			foreach($running as $child)
			{
				foreach($child['pipes'] as $pipe)
				{
					$open[] = $pipe;
				}
			}

			if(!empty($open))
			{
				$write  = null;
				$except = null;
				stream_select($open, $write, $except, 0, 100000);
			}
			else
			{
				usleep(1000);
			}

			foreach(array_keys($running) as $id)
			{
				foreach($running[$id]['pipes'] as $fd => $pipe)
				{
					$chunk = fread($pipe, 65536);

					if($chunk !== false && $chunk !== '')
					{
						$running[$id][$fd === 1 ? 'stdout' : 'stderr'] .= $chunk;
					}

					if(feof($pipe))
					{
						fclose($pipe);
						unset($running[$id]['pipes'][$fd]);
					}
				}

				$elapsed  = microtime(true) - $running[$id]['started'];
				$timedOut = $elapsed > self::TIMEOUT_SECONDS;

				if(!empty($running[$id]['pipes']) && !$timedOut) { continue; }

				$reports[$running[$id]['path']] = $this->reap($running[$id], $elapsed, $timedOut);
				unset($running[$id]);
			}
		}

		private function start($path, array $flags, $address)
		{
			$descriptors = array(
				0 => array('pipe', 'r'),
				1 => array('pipe', 'w'),
				2 => array('pipe', 'w'),
			);

			$pipes   = array();
			$command = $this->probeCommand($path, $flags, $address);
			$proc    = proc_open(is_array($command) ? implode(' ', array_map('escapeshellarg', $command)) : $command, $descriptors, $pipes, dirname($path));

			if(!is_resource($proc))
			{
				$this->fail('Could not start a probe process for ' . $path);
			}

			fclose($pipes[0]);
			stream_set_blocking($pipes[1], false);
			stream_set_blocking($pipes[2], false);

			return array(
				'path'    => $path,
				'proc'    => $proc,
				'pipes'   => array(1 => $pipes[1], 2 => $pipes[2]),
				'stdout'  => '',
				'stderr'  => '',
				'started' => microtime(true),
			);
		}

		private function reap(array $child, $elapsed, $timedOut)
		{
			if($timedOut)
			{
				proc_terminate($child['proc']);
			}

			foreach($child['pipes'] as $pipe)
			{
				fclose($pipe);
			}

			return array(
				'path'     => $child['path'],
				'name'     => basename($child['path']),
				'exitCode' => proc_close($child['proc']),
				'stdout'   => $child['stdout'],
				'stderr'   => $child['stderr'],
				'seconds'  => $elapsed,
				'timedOut' => $timedOut,
			);
		}

		/**
		 * PHP_BINARY, so the sweep exercises the interpreter running the suite
		 * rather than whichever php is first on PATH.
		 */
		private function probeCommand($path, array $flags, $address)
		{
			$timezone = ini_get('date.timezone');

			$settings = array(
				'error_reporting=' . (E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED),
				'display_errors=stderr',
				'log_errors=0',
				'html_errors=0',
				'date.timezone=' . ($timezone !== '' ? $timezone : 'UTC'),
				// Coverage runs load xdebug, whose chatter would land on the
				// stderr this verdict depends on.
				'xdebug.mode=off',
			);

			$command = escapeshellarg(PHP_BINARY);

			foreach($settings as $setting)
			{
				$command .= ' -d ' . escapeshellarg($setting);
			}

			$command .= ' ' . escapeshellarg(codecept_data_dir('scriptsTest/probe.php'));
			$command .= ' ' . escapeshellarg(APP_PATH);
			$command .= ' ' . escapeshellarg($path);
			$command .= ' ' . escapeshellarg('--remote-addr=' . self::REMOTE_ADDR_PREFIX . (($address % 254) + 1));

			foreach($flags as $flag)
			{
				$command .= ' ' . escapeshellarg($flag);
			}

			return $command;
		}

		private function isClean(array $report)
		{
			return $report['exitCode'] === 0 && trim($report['stderr']) === '' && !$report['timedOut'];
		}

		private function assertScriptsAreClean(array $reports)
		{
			$failures = array();

			foreach($reports as $report)
			{
				if($this->isClean($report)) { continue; }

				$failures[] = $this->describe($report);
			}

			$this->assertCount(
				0,
				$failures,
				"Scripts that did not load cleanly:\n\n" . implode("\n\n", $failures) . "\n"
			);
		}

		private function describe(array $report)
		{
			$lines = array(sprintf('%s: exit %d after %.2fs', $report['name'], $report['exitCode'], $report['seconds']));

			if($report['timedOut'])
			{
				$lines[] = '  did not finish within ' . self::TIMEOUT_SECONDS . 's and was terminated';
			}

			$stderr = trim($report['stderr']);

			if($stderr !== '')
			{
				$lines[] = '  ' . str_replace("\n", "\n  ", $this->clip($stderr, 2000));
			}

			// What it did manage to render says which page it ended up on,
			// which is the whole diagnosis when the failure is that the script
			// was never reached.
			$stdout = trim($report['stdout']);

			if($stdout !== '')
			{
				$lines[] = '  rendered: ' . str_replace("\n", "\n  ", $this->clip($stdout, 600));
			}

			return implode("\n", $lines);
		}

		private function clip($text, $limit)
		{
			if(strlen($text) <= $limit)
			{
				return $text;
			}

			return substr($text, 0, $limit) . "\n... (" . (strlen($text) - $limit) . ' more bytes)';
		}

		private function fixture($name)
		{
			$path = realpath(codecept_data_dir('scriptsTest/fixtures/' . $name));

			$this->assertNotEmpty($path, 'Missing fixture: ' . $name);

			return $path;
		}

		private function onlyReport(array $reports)
		{
			$this->assertCount(1, $reports);

			return reset($reports);
		}
	}
