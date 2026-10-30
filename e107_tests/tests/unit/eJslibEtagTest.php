<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Covers the validator {@see e_jslib::content_out()} sends with the combined scripts it builds.
 */
class eJslibEtagTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	public function testScriptsAddedSinceAnEmptyBundleWasCachedAreSent()
	{
		$response = $this->serve('var added = true;', md5(''));

		$this->assertStringContainsString('var added = true;', $response,
			'a validator for the empty bundle must not answer for scripts that have since been added');
		$this->assertStringNotContainsString('STATUS=304', $response);
	}

	public function testUnchangedScriptsAreAnsweredNotModified()
	{
		$response = $this->serve('var same = true;', md5('var same = true;'));

		$this->assertStringContainsString('STATUS=304', $response,
			'a validator for the scripts being served must be answered 304 Not Modified');
		$this->assertStringNotContainsString('var same = true;', $response);
	}

	/**
	 * Builds the combined scripts in a booted child from $scripts, as a request presenting $ifNoneMatch, and returns what it sent with its status after it.
	 *
	 * @param string $scripts
	 * @param string $ifNoneMatch
	 * @return string
	 */
	private function serve($scripts, $ifNoneMatch)
	{
		$php = '$_SERVER["QUERY_STRING"] = "eJslibEtagTest"; ';
		$php .= '$_SERVER["HTTP_IF_NONE_MATCH"] = '.var_export('"'.$ifNoneMatch.'"', true).'; ';
		$php .= '$jslib = e107::getObject("e_jslib", null, e_HANDLER."jslib_handler.php"); ';
		$php .= 'register_shutdown_function(function() use ($jslib) { @unlink($jslib->cache_filename()); echo "\nSTATUS=".http_response_code(); }); ';
		$php .= 'ob_start(); echo '.var_export($scripts, true).'; ';
		$php .= '$jslib->content_out(0); ';

		list($output) = $this->runInBootedCli($php, self::buffered());

		return implode("\n", $output);
	}
}
