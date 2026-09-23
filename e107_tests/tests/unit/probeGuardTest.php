<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * {@see \Helper\ProbeGuard::contain()} guards every fixture that boots e107, whatever the include is spelled, and leaves the rest alone.
 */
class probeGuardTest extends \Codeception\Test\Unit
{
	/**
	 * @dataProvider bootstraps
	 * @param string $source
	 */
	public function testAFixtureThatBootsE107WithoutTheMarkerIsRefused($source)
	{
		$this->expectException('RuntimeException');

		\Helper\ProbeGuard::contain('probe.php', $source);
	}

	/**
	 * @dataProvider bootstraps
	 * @param string $source
	 */
	public function testTheGuardIsWrittenWhereTheMarkerStands($source)
	{
		$contained = \Helper\ProbeGuard::contain('probe.php', $source."\n".\Helper\ProbeGuard::MARKER."\n");

		self::assertStringNotContainsString(\Helper\ProbeGuard::MARKER, $contained);
		self::assertStringContainsString(\Helper\ProbeGuard::secret(), $contained);
		self::assertStringContainsString(\Helper\ProbeGuard::REFUSAL, $contained);
	}

	public function bootstraps()
	{
		return array(
			'single quotes' => array("<?php\nrequire_once(__DIR__.'/class2.php');"),
			'double quotes' => array("<?php\nrequire_once(__DIR__.\"/class2.php\");"),
			'a variable interpolated' => array("<?php\n\$root = __DIR__;\nrequire_once(\"\$root/class2.php\");"),
			'braces interpolated' => array("<?php\n\$root = __DIR__;\nrequire_once \"{\$root}/class2.php\";"),
			'a heredoc' => array("<?php\n\$root = __DIR__;\nrequire_once <<<PATH\n\$root/class2.php\nPATH;\n"),
			'a plugin subdirectory' => array("<?php\nrequire_once(__DIR__.'/../../class2.php');"),
			'no parentheses' => array("<?php\nrequire __DIR__ . '/class2.php';"),
			'include' => array("<?php\ninclude_once(__DIR__.'/class2.php');"),
			'after other statements' => array("<?php\n\$_E107['cli'] = true;\nrequire_once(__DIR__.'/class2.php');"),
		);
	}

	/**
	 * @dataProvider payloads
	 * @param string $source
	 */
	public function testAFixtureThatDoesNotBootE107IsWrittenAsItIs($source)
	{
		self::assertSame($source, \Helper\ProbeGuard::contain('fixture.php', $source));
	}

	public function payloads()
	{
		return array(
			'plain PHP' => array("<?php\necho 'PROBE_OK';"),
			'the bootstrap in a comment' => array("<?php\n// require_once(__DIR__.'/class2.php');\necho 1;"),
			'the bootstrap in a string' => array("<?php\n\$php = \"require_once(__DIR__.'/class2.php');\";"),
			'the bootstrap interpolated into a string' => array("<?php\n\$php = \"require_once('\$root/class2.php');\";"),
			'another file included' => array("<?php\nrequire_once(__DIR__.'/e107_config.php');"),
			'not PHP at all' => array('P2NOTANIMAGE'),
		);
	}

	/**
	 * The tokeniser's warning is a compile warning, which no error handler is shown, so the witness is error_get_last().
	 */
	public function testABinaryFixtureIsWrittenAsItIsWithoutAWarning()
	{
		$image = file_get_contents(e_PLUGIN.'gallery/images/butterfly.jpg');
		@trigger_error(__METHOD__, E_USER_NOTICE);

		self::assertSame($image, \Helper\ProbeGuard::contain('e107_files/downloadimages/butterfly.jpg', $image));

		$last = error_get_last();
		self::assertSame(__METHOD__, $last['message']);
	}
}
