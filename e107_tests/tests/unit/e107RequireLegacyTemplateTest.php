<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Covers e107::predefineLegacyLans(), e107::requireLegacyTemplate(),
 * and the underlying e107::_extractLanConstantsFromSource() tokeniser
 * — added for issue #5653 (Undefined constant LAN_112 in fpw_template.php).
 */
class e107RequireLegacyTemplateTest extends \Codeception\Test\Unit
{
	/** @var string[] */
	protected $tempFiles = array();

	/** @var array<string,string> */
	protected $capturedWarnings = array();

	protected function _before()
	{
		// Codeception phpunit.xml has convertWarningsToExceptions=true.
		// Temporarily suppress E_USER_WARNING so trigger_error() in the
		// helper does not abort the test — we still verify warnings fired
		// via a custom error handler installed per-test.
		set_error_handler(function ($severity, $message) {
			if ($severity === E_USER_WARNING) {
				$this->capturedWarnings[] = $message;
				return true;
			}
			return false;
		}, E_USER_WARNING);
	}

	protected function _after()
	{
		restore_error_handler();
		foreach ($this->tempFiles as $f)
		{
			if (is_file($f)) @unlink($f);
		}
		$this->tempFiles = array();
		$this->capturedWarnings = array();
	}

	private function writeTempTemplate($basename, $body)
	{
		$dir = sys_get_temp_dir() . '/e107-lantokens-' . getmypid();
		if (!is_dir($dir)) mkdir($dir, 0777, true);
		$path = $dir . '/' . $basename;
		file_put_contents($path, $body);
		$this->tempFiles[] = $path;
		return $path;
	}

	// --- _extractLanConstantsFromSource: pure tokenizer behaviour -----------

	public function testExtractFindsBareConstants()
	{
		$src = '<?php echo LAN_05 . " " . LAN_FPW1 . LAN_112; if (defined("LAN_OTHER")) {}';
		$names = e107::_extractLanConstantsFromSource($src);
		sort($names);
		$this->assertSame(array('LAN_05', 'LAN_112', 'LAN_FPW1'), array_values($names));
		// defined("LAN_OTHER") is a T_CONSTANT_ENCAPSED_STRING, not T_STRING; the
		// extractor must not pick it up.
	}

	public function testExtractIgnoresFunctionDeclaration()
	{
		// function LAN_foo() should NOT be treated as a constant reference.
		$src = '<?php function LAN_NOT_A_CONST() { return 1; } echo LAN_REAL;';
		$names = e107::_extractLanConstantsFromSource($src);
		$this->assertContains('LAN_REAL', $names);
		$this->assertNotContains('LAN_NOT_A_CONST', $names);
	}

	public function testExtractIgnoresMethodCallAndStatic()
	{
		// $obj->LAN_X and Class::LAN_Y are method/static refs, not bare constants.
		$src = '<?php $o->LAN_METHOD(); Foo::LAN_STATIC; echo LAN_BARE;';
		$names = e107::_extractLanConstantsFromSource($src);
		$this->assertContains('LAN_BARE', $names);
		$this->assertNotContains('LAN_METHOD', $names);
		$this->assertNotContains('LAN_STATIC', $names);
	}

	public function testExtractIgnoresFunctionCallByName()
	{
		// LAN_func() looks like a function call — should be skipped.
		$src = '<?php LAN_func(); echo LAN_NORMAL;';
		$names = e107::_extractLanConstantsFromSource($src);
		$this->assertContains('LAN_NORMAL', $names);
		$this->assertNotContains('LAN_func', $names);
	}

	public function testExtractIgnoresClassDeclaration()
	{
		$src = '<?php class LAN_KLASS {} echo LAN_FINE;';
		$names = e107::_extractLanConstantsFromSource($src);
		$this->assertContains('LAN_FINE', $names);
		$this->assertNotContains('LAN_KLASS', $names);
	}

	public function testExtractDeduplicates()
	{
		$src = '<?php echo LAN_X . LAN_X . LAN_X;';
		$names = e107::_extractLanConstantsFromSource($src);
		$this->assertSame(array('LAN_X'), $names);
	}

	public function testExtractHandlesEmptyAndJunk()
	{
		$this->assertSame(array(), e107::_extractLanConstantsFromSource(''));
		$this->assertSame(array(), e107::_extractLanConstantsFromSource('plain text, no php tags'));
	}

	// --- predefineLegacyLans: scan-only API used by the callers ------------

	public function testPredefineDefinesMissingAndEmitsWarning()
	{
		$marker = 'LAN_TEST_5653_PRE_' . mt_rand(10000, 99999);
		$body = "<?php\n\$x = " . $marker . ";\n";
		$path = $this->writeTempTemplate('pre.php', $body);
		$this->assertFalse(defined($marker));
		$ok = e107::predefineLegacyLans($path);
		$this->assertTrue($ok);
		$this->assertTrue(defined($marker));
		$this->assertCount(1, $this->capturedWarnings);
	}

	public function testPredefineReturnsFalseForMissingPath()
	{
		$this->assertFalse(e107::predefineLegacyLans('/no/such/file-' . uniqid() . '.php'));
	}

	public function testPredefineReturnsFalseForEmptyOrNull()
	{
		$this->assertFalse(e107::predefineLegacyLans(''));
		$this->assertFalse(e107::predefineLegacyLans(null));
	}

	public function testPredefineThenCallerRequireKeepsCallerScope()
	{
		// The whole reason we split off predefineLegacyLans() from
		// requireLegacyTemplate(): templates set $FPW_TABLE / $SIGNUP_BODY
		// etc. into the *caller's* scope. If we required from inside the
		// helper those would land in the helper's scope. So we test that
		// the caller-side `require` preserves scope.
		$body = "<?php\n\$LOCAL_VAR_FROM_TEMPLATE = 'present';\n";
		$path = $this->writeTempTemplate('scope.php', $body);
		e107::predefineLegacyLans($path);
		require $path;
		$this->assertSame('present', $LOCAL_VAR_FROM_TEMPLATE);
	}

	// --- requireLegacyTemplate (convenience wrapper): integration ----------

	public function testRequireDefinesMissingConstantAndEmitsWarning()
	{
		$marker = 'LAN_TEST_5653_' . mt_rand(1000, 9999);
		$body = "<?php\n\$GLOBALS['__test_lan_value'] = " . $marker . ";\n";
		$path = $this->writeTempTemplate('autodef.php', $body);

		$this->assertFalse(defined($marker), 'Sanity: constant should not pre-exist');

		$ret = e107::requireLegacyTemplate($path);

		$this->assertTrue($ret);
		$this->assertTrue(defined($marker), 'Helper should have defined the constant');
		$this->assertSame($marker, constant($marker), 'Value should equal its own name');
		$this->assertSame($marker, $GLOBALS['__test_lan_value']);
		$this->assertCount(1, $this->capturedWarnings, 'Expected one E_USER_WARNING');
		$this->assertStringContainsString($marker, $this->capturedWarnings[0]);
		$this->assertStringContainsString('Auto-defined', $this->capturedWarnings[0]);

		unset($GLOBALS['__test_lan_value']);
	}

	public function testRequireLeavesExistingConstantsAlone()
	{
		$existing = 'LAN_TEST_5653_EXIST_' . mt_rand(1000, 9999);
		define($existing, 'pre-set-value');

		$body = "<?php\n\$GLOBALS['__test_lan_value2'] = " . $existing . ";\n";
		$path = $this->writeTempTemplate('existing.php', $body);

		e107::requireLegacyTemplate($path);

		$this->assertSame('pre-set-value', constant($existing), 'Existing constant must not be overwritten');
		$this->assertSame('pre-set-value', $GLOBALS['__test_lan_value2']);
		$this->assertCount(0, $this->capturedWarnings, 'No warning when constant already defined');

		unset($GLOBALS['__test_lan_value2']);
	}

	public function testRequireOnMissingFileReturnsFalse()
	{
		$missing = sys_get_temp_dir() . '/this-file-does-not-exist-' . uniqid('', true) . '.php';
		$ret = e107::requireLegacyTemplate($missing);
		$this->assertFalse($ret);
	}

	public function testRequireWithNoLanReferencesIsNoop()
	{
		$body = "<?php\n\$GLOBALS['__test_no_lan'] = 'hello';\n";
		$path = $this->writeTempTemplate('nolans.php', $body);

		e107::requireLegacyTemplate($path);

		$this->assertSame('hello', $GLOBALS['__test_no_lan']);
		$this->assertCount(0, $this->capturedWarnings);
		unset($GLOBALS['__test_no_lan']);
	}

	public function testRequireReusesCacheOnSecondCall()
	{
		// Indirect check: second call with same path should still work (and
		// for templates with no LAN_*, must not emit warnings).
		$body = "<?php\n\$GLOBALS['__test_cached'] = (\$GLOBALS['__test_cached'] ?? 0) + 1;\n";
		$path = $this->writeTempTemplate('cached.php', $body);

		e107::requireLegacyTemplate($path);
		e107::requireLegacyTemplate($path);
		e107::requireLegacyTemplate($path);

		$this->assertSame(3, $GLOBALS['__test_cached']);
		unset($GLOBALS['__test_cached']);
	}

	// --- fpw_template.php regression for #5653 -----------------------------

	public function testFpwLegacyTemplateScanFindsLan112()
	{
		$path = realpath(__DIR__ . '/../../../e107_core/templates/legacy/fpw_template.php');
		if (!$path || !is_readable($path))
		{
			$this->markTestSkipped('fpw_template.php not found at expected legacy path');
		}
		$names = e107::_extractLanConstantsFromSource(file_get_contents($path));
		$this->assertContains('LAN_05', $names);
		$this->assertContains('LAN_FPW1', $names);
		$this->assertContains('LAN_112', $names, 'The very constant that fataled in issue #5653 must be detected');
	}
}
