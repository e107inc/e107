<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Loads the vendored Hybridauth Apple adapter in a fresh interpreter, after php-jwt, as a request that decoded a token first would.
 */
class HybridauthAppleTest extends \Test\Unit
{
	public function testAppleAdapterCompilesOnceJwtIsLoaded()
	{
		if(!function_exists('shell_exec') || !defined('PHP_BINARY') || PHP_BINARY === '')
		{
			$this->markTestSkipped('No usable PHP binary to run a child process with.');
		}

		$autoload = realpath(__DIR__ . '/../../../e107_handlers/vendor/autoload.php');
		$this->assertNotFalse($autoload, 'e107_handlers/vendor/autoload.php is missing');

		$probe = 'require ' . var_export($autoload, true) . ';'
			. ' class_exists("Firebase\\\\JWT\\\\JWT");'
			. ' echo class_exists("Hybridauth\\\\Provider\\\\Apple") ? "loaded" : "missing";';

		$command = escapeshellarg(PHP_BINARY)
			. ' -d display_errors=1 -d error_reporting=' . (E_ALL & ~E_DEPRECATED)
			. ' -r ' . escapeshellarg($probe)
			. ' 2>&1';

		$this->assertSame('loaded', trim((string) shell_exec($command)));
	}
}
