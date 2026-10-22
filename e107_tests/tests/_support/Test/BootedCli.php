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
	 * @return string a child's php arguments that hold its output back, so the notices its boot prints send no headers and its session settings stay changeable
	 */
	protected static function buffered()
	{
		return '-d output_buffering=On';
	}

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
	 * Runs $php in a booted CLI child that defined $constants before class2.php could, so the boot keeps the values given here.
	 *
	 * @param array $constants name => value
	 * @param string $php
	 * @param string $ini extra php command-line arguments, e.g. {@see BootedCli::buffered()}
	 * @return array the output lines, stdout and stderr interleaved, then the exit status
	 */
	protected function runInBootedCliDefining(array $constants, $php, $ini = '')
	{
		$defines = '';
		foreach($constants as $name => $value)
		{
			$defines .= 'define('.var_export($name, true).', '.var_export($value, true).'); ';
		}

		return $this->runInCli($defines.$this->cliBoot(array('cli' => true)).$php, $ini);
	}

	/**
	 * Loads an entry script in a booted CLI child once $seed has run, and returns everything it wrote; the page arrives on shutdown, because footer.php leaves its output buffer open.
	 *
	 * @param string $script path under the app root, e.g. 'e107_admin/db.php'
	 * @param string $seed PHP setting up preferences and request data before the script is loaded
	 * @param string $ini extra php command-line arguments, e.g. {@see BootedCli::buffered()}
	 * @param array $constants name => value, defined before class2.php could, as {@see BootedCli::runInBootedCliDefining()} does
	 * @return string
	 */
	protected function renderInBootedCli($script, $seed, $ini = '', array $constants = array())
	{
		$booted = 'E107-BOOTED';

		$php = "fwrite(STDERR, '".$booted."'); ";
		$php .= "register_shutdown_function(function() { while(ob_get_level() > 0) { @ob_end_flush(); } }); ";
		$php .= $seed;
		$php .= "require_once('".addslashes(APP_PATH.'/'.$script)."'); ";

		list($output) = $this->runInBootedCliDefining($constants, $php, $ini);
		$page = implode("\n", $output);

		$this->assertStringContainsString($booted, $page,
			"The subprocess did not get as far as booting e107, so nothing below can be trusted.\n".$page);

		return $page;
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
		$seed = "error_reporting(E_ALL); \$_GET = ".var_export($get, true)."; ";
		$seed .= "e107::getConfig()->setPref('plug_installed/".$plugin."', '".$version."'); ";

		return $this->runInBootedCliDefining(array('e_QUERY' => http_build_query($get)), $seed.$php);
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

	/**
	 * Asserts that a child's output reports no read of a missing array key, in the wording of any PHP the suite runs on.
	 *
	 * @param string $keys the keys, as a regular expression alternation
	 * @param string $printed what the child wrote
	 * @param string $message
	 * @return void
	 */
	protected static function assertReadNoMissingKey($keys, $printed, $message)
	{
		self::assertSame(0, preg_match(self::missingKeyNotice($keys), $printed), $message);
	}

	/**
	 * The notice for a read of a missing key: PHP 8 prints `Undefined array key "name"` or `Undefined array key 0`, PHP 7 and 5.6 `Undefined index: name` or `Undefined offset: 0`.
	 *
	 * @param string $keys the keys, as a regular expression alternation
	 * @return string
	 */
	protected static function missingKeyNotice($keys)
	{
		return '/Undefined (?:array key (?:"(?:'.$keys.')"|(?:'.$keys.')(?!\w))|(?:index|offset): (?:'.$keys.')(?!\w))/i';
	}

	/**
	 * Asserts that a child's output reports no read of an undefined variable, in the wording of any PHP the suite runs on.
	 *
	 * @param string $names the variable names, as a regular expression alternation
	 * @param string $printed what the child wrote
	 * @param string $message
	 * @return void
	 */
	protected static function assertReadNoUndefinedVariable($names, $printed, $message)
	{
		self::assertSame(0, preg_match(self::undefinedVariableNotice($names), $printed), $message);
	}

	/**
	 * The notice for a read of an undefined variable: PHP 8 prints `Undefined variable $name`, PHP 7 and 5.6 `Undefined variable: name`.
	 *
	 * @param string $names the variable names, as a regular expression alternation
	 * @return string
	 */
	protected static function undefinedVariableNotice($names)
	{
		return '/Undefined variable:? \$?(?:'.$names.')(?!\w)/i';
	}

	/**
	 * Asserts that a child's output reports no read of an undefined constant, in the wording of any PHP the suite runs on.
	 *
	 * @param string $names the constant names, as a regular expression alternation
	 * @param string $printed what the child wrote
	 * @param string $message
	 * @return void
	 */
	protected static function assertReadNoUndefinedConstant($names, $printed, $message)
	{
		self::assertSame(0, preg_match(self::undefinedConstantNotice($names), $printed), $message);
	}

	/**
	 * The notice for a read of an undefined constant: PHP 8 prints `Undefined constant "NAME"`, PHP 7 and 5.6 `Use of undefined constant NAME - assumed 'NAME'`, and PHP 7 `Undefined constant 'NAME'` where a constant expression reads it.
	 *
	 * @param string $names the constant names, as a regular expression alternation
	 * @return string
	 */
	protected static function undefinedConstantNotice($names)
	{
		return '/undefined constant ["\']?(?:'.$names.')(?!\w)/i';
	}
}
