<?php

namespace Test;

/**
 * A subprocess with class2.php booted in CLI mode, for tests that need a process of their own.
 *
 * Several tests in this suite build this by hand. They can drop their copy and
 * name this trait instead; the signature carries what each of them needs.
 *
 * Keep this in PHP 5.6 syntax: it is part of the shipping-adjacent test tree
 * that the downgrade pipeline walks.
 */
trait BootedCli
{
	/**
	 * Runs $php in a subprocess that has booted class2.php in CLI mode; the child serves {@see Addresses::SERVER} and visits as {@see Addresses::nextVisitor()}, keeping an address its caller put in the environment.
	 *
	 * @param string $php
	 * @param string $ini extra php command-line arguments, e.g. '-d memory_limit=64M'
	 * @param array $e107 what $_E107 holds when class2.php boots
	 * @param int $timeout seconds
	 * @return array the output lines, stdout and stderr interleaved, then the exit status
	 */
	protected function runInBootedCli($php, $ini = '', $e107 = array('cli' => true), $timeout = 60)
	{
		return $this->runInCli($this->cliBoot($e107).$php, $ini, $timeout);
	}

	/**
	 * Runs $php in a booted CLI child holding one query string and one installed plugin, which is what a plugin's own front-end files need before they will run.
	 *
	 * e_QUERY is defined ahead of the boot, and this is why the child cannot be
	 * had from {@see BootedCli::runInBootedCli()}: e107::set_request() defines it
	 * from the query string of the process, which a CLI process does not have,
	 * and the first definition is the one the page then reads. The installed
	 * preference is set in the child alone, so a plugin whose tables the test does
	 * not need stays absent from the site.
	 *
	 * @param string $plugin plugin folder name, e.g. 'download'
	 * @param string $version the version plug_installed carries for it
	 * @param array $get the query string the child is opened with, as e_QUERY and as $_GET
	 * @param string $php statements to run once the plugin is installed
	 * @return array the output lines, stdout and stderr interleaved, then the exit status
	 */
	protected function bootPluginInCli($plugin, $version, array $get, $php)
	{
		$boot = "define('e_QUERY', '".http_build_query($get)."'); ";
		$boot .= $this->cliBoot(array('cli' => true));
		$boot .= "error_reporting(E_ALL); \$_GET = ".var_export($get, true)."; ";
		$boot .= "e107::getConfig()->setPref('plug_installed/".$plugin."', '".$version."'); ";

		return $this->runInCli($boot.$php);
	}

	/**
	 * The statements that boot e107 in a child of this run, so every child boots the one way whatever it was started for.
	 *
	 * @param array $e107 what $_E107 holds when class2.php boots
	 * @return string
	 */
	private function cliBoot($e107)
	{
		$boot = "error_reporting(E_ALL); ini_set('display_errors', 1); ";
		$boot .= "\$_SERVER['SERVER_ADDR'] = '".Addresses::SERVER."'; ";
		$boot .= "if(empty(\$_SERVER['REMOTE_ADDR'])) { \$_SERVER['REMOTE_ADDR'] = '".Addresses::nextVisitor()."'; } ";
		$boot .= "\$_E107 = ".var_export($e107, true)."; ";
		$boot .= "require_once('".addslashes(APP_PATH.'/class2.php')."'); ";

		return $boot;
	}

	/**
	 * Runs $php in a subprocess booting nothing, for a test that has to define something before class2.php reads it.
	 *
	 * @param string $php
	 * @param string $ini extra php command-line arguments, e.g. '-d memory_limit=64M'
	 * @param int $timeout seconds
	 * @return array the output lines, stdout and stderr interleaved, then the exit status
	 */
	protected function runInCli($php, $ini = '', $timeout = 60)
	{
		$output = array();
		$status = 0;
		exec(sprintf('timeout %d php %s -r %s 2>&1', $timeout, $ini, escapeshellarg($php)), $output, $status);

		if($status === 124)
		{
			$head = array_slice($output, 0, 20);

			self::fail(sprintf("the subprocess wedged, so nothing was measured. The first %d of its %d line(s):\n%s",
				count($head), count($output), implode("\n", $head)));
		}

		return array($output, $status);
	}
}
