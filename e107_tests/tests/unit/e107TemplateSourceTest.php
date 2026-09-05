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
 * Covers the template source capture in e107::_getTemplate().
 *
 * One physical template file can back several registry paths: the override
 * ('/ext') and non-override flavours resolve to the same core file whenever
 * the theme provides no override. Because the file is loaded with
 * include_once(), only the first load can capture the template array from
 * the file's scope; without a per-file capture, whichever flavour loads
 * second caches array() and every later lookup on it returns false.
 */
class e107TemplateSourceTest extends \Codeception\Test\Unit
{
	/** @var string[] */
	protected $tempFiles = array();

	/** @var string[] */
	protected $capturedWarnings = array();

	/** @var array|null */
	protected $scStyleBefore;

	/** Captures the E_USER_WARNING {@see e107::predefineLegacyLans()} reports for each constant it invents, which Codeception turns into an exception. */
	protected function _before()
	{
		set_error_handler(function ($severity, $message)
		{
			if($severity === E_USER_WARNING)
			{
				$this->capturedWarnings[] = $message;

				return true;
			}

			return false;
		}, E_USER_WARNING);

		$this->scStyleBefore = e107::getRegistry('shortcodes/sc_style');
	}

	protected function _after()
	{
		restore_error_handler();

		e107::setRegistry('shortcodes/sc_style', $this->scStyleBefore);

		foreach($this->tempFiles as $file)
		{
			if(is_file($file))
			{
				unlink($file);
			}
		}

		$this->tempFiles = array();
		$this->capturedWarnings = array();
	}

	public function testGetCoreTemplateOverrideThenPlain()
	{
		$this->clearTemplateRegistry('admin');

		$override = e107::getCoreTemplate('admin', 'menu', true);
		$plain = e107::getCoreTemplate('admin', 'menu', false);

		$this->assertNotEmpty($override);
		$this->assertNotEmpty($plain);
		$this->assertBothFlavoursMatchWithoutThemeOverride('admin', $override, $plain);
	}

	public function testGetCoreTemplatePlainThenOverride()
	{
		$this->clearTemplateRegistry('admin');

		$plain = e107::getCoreTemplate('admin', 'menu', false);
		$override = e107::getCoreTemplate('admin', 'menu', true);

		$this->assertNotEmpty($plain);
		$this->assertNotEmpty($override);
		$this->assertBothFlavoursMatchWithoutThemeOverride('admin', $override, $plain);
	}

	public function testGetCoreTemplateMergeDoesNotPoisonPlainFlavour()
	{
		// 'admin' has no theme override in any bundled theme, so both flavours
		// resolve to the same core file and the merge call exercises the
		// one-file-behind-two-registry-paths case this test exists for.
		$this->clearTemplateRegistry('admin');

		$merged = e107::getCoreTemplate('admin', null, true, true);
		$plain = e107::getCoreTemplate('admin', 'menu', false);

		$this->assertNotEmpty($merged);
		$this->assertNotEmpty($plain);
	}

	/** A v1-shaped file assigns scalars rather than ${ID}_TEMPLATE, which {@see e107::getCoreTemplate()} used to answer array() for. */
	public function testV1FileExposesItsUppercaseVariables()
	{
		$id = $this->uniqueTemplateId();
		$path = $this->writeTemplate("<?php\n\$FPWX_TABLE = 'v1 body';\n\$FPWX_TABLE_HEADER = 'v1 head';\n");

		$loaded = e107::_getTemplate($id, null, 'core/e107/templates/'.$id, $path);

		$this->assertSame(array('FPWX_TABLE' => 'v1 body', 'FPWX_TABLE_HEADER' => 'v1 head'), $loaded);
		$this->assertSame($loaded, e107::getCoreTemplate($id, null, false));
		$this->assertFalse(e107::getCoreTemplate($id, 'form', false), 'a v1 file has no v2 keys to hand back');
	}

	/** A keyed call over a theme's v1 file hands the merge false against core's array, and core's is what the page can use. */
	public function testKeyedCallOverAThemeSuppliedV1FileFallsBackToCore()
	{
		$id = $this->uniqueTemplateId();
		$this->writeTemplate("<?php\n\$".strtoupper($id)."_TEMPLATE = array('extended' => array('start' => '<div>'));\n",
			e_CORE.'templates/'.$id.'_template.php');
		$this->writeTemplate("<?php\n\$EXTENDED_CATEGORY_START = 'v1 start';\n",
			e107::getThemeInfo(true, 'rel').$id.'_template.php');

		$this->assertSame(array('start' => '<div>'), e107::getCoreTemplate($id, 'extended', false),
			'the core side of the merge has to be an array, or the assertion below is vacuous');
		$this->assertSame('v1 start', e107::getCoreTemplate($id, 'EXTENDED_CATEGORY_START'),
			'the theme side has to be the v1 capture, or the assertion below is vacuous');
		$this->assertSame(array('start' => '<div>'), e107::getCoreTemplate($id, 'extended'));
	}

	public function testV1ScStyleReachesScStyle()
	{
		$id = $this->uniqueTemplateId();
		$path = $this->writeTemplate("<?php\n\$SCX_TABLE = 'x';\n"
			."\$sc_style['E107_TESTS_TP_CODE'] = array('pre' => '<b>', 'post' => '</b>');\n");

		e107::_getTemplate($id, null, 'core/e107/templates/'.$id, $path);

		$styles = e107::scStyle();
		$this->assertArrayHasKey('E107_TESTS_TP_CODE', $styles);
		$this->assertSame(array('pre' => '<b>', 'post' => '</b>'), $styles['E107_TESTS_TP_CODE']);
	}

	/** A file assigning ${ID}_TEMPLATE is v2-shaped, so the legacy $sc_style the shipped plugin templates carry beside it stays unregistered. */
	public function testV2FileKeepsItsScStyleToItself()
	{
		$id = $this->uniqueTemplateId();
		$path = $this->writeTemplate("<?php\n\$".strtoupper($id)."_TEMPLATE = array('form' => 'v2 form');\n"
			."\$sc_style['E107_TESTS_TP_V2CODE'] = array('pre' => '<i>', 'post' => '</i>');\n");

		$loaded = e107::_getTemplate($id, null, 'core/e107/templates/'.$id, $path);

		$this->assertSame(array('form' => 'v2 form'), $loaded, 'the file has to load at all, or the assertion below is vacuous');
		$this->assertArrayNotHasKey('E107_TESTS_TP_V2CODE', e107::scStyle());
	}

	public function testThemeSuppliedFileGetsItsLansPredefined()
	{
		$marker = 'LAN_TP_LOADER_'.mt_rand(10000, 99999);
		$id = $this->uniqueTemplateId();
		$path = $this->writeTemplate("<?php\n\$TPX_TABLE = ".$marker.";\n");

		$this->assertFalse(defined($marker), 'sanity: the constant must not pre-exist');

		$loaded = e107::_getTemplate($id, null, 'core/e107/templates/'.$id, $path);

		$this->assertTrue(defined($marker));
		$this->assertSame(array('TPX_TABLE' => $marker), $loaded);
		$this->assertCount(1, $this->capturedWarnings);
	}

	/** Core's own templates are written to current LAN names, so a path under e_CORE is not scanned for them. */
	public function testCorePathIsNotScannedForLans()
	{
		$marker = 'LAN_TP_CORE_'.mt_rand(10000, 99999);
		$id = 'e107_tests_corescan';
		$path = $this->writeTemplate("<?php\nif(false) { \$unreachable = ".$marker."; }\n\$CORE_PROBE_TABLE = 'core';\n",
			e_CORE.'templates/'.$id.'_template.php');

		$this->assertContains($marker, e107::_extractLanConstantsFromSource(file_get_contents($path)),
			'the fixture has to name a constant the scan would find, or the assertion below is vacuous');

		$loaded = e107::_getTemplate($id, null, 'core/e107/templates/'.$id, $path);

		$this->assertSame(array('CORE_PROBE_TABLE' => 'core'), $loaded);
		$this->assertFalse(defined($marker), 'a path under e_CORE must not be scanned');
		$this->assertCount(0, $this->capturedWarnings);
	}

	public function testV2FileIsUnchanged()
	{
		$id = $this->uniqueTemplateId();
		$upper = strtoupper($id);
		$path = $this->writeTemplate("<?php\n"
			."\$".$upper."_TEMPLATE = array('form' => 'v2 form');\n"
			."\$".$upper."_INFO = array('form' => array('title' => 'Form'));\n"
			."\$".$upper."_WRAPPER = array('form' => array('X' => 'pre{---}post'));\n"
			."\$LOOSE_SCALAR = 'must not be captured';\n");

		$loaded = e107::_getTemplate($id, null, 'core/e107/templates/'.$id, $path);

		$this->assertSame(array('form' => 'v2 form'), $loaded);
		$this->assertSame(array('form' => array('title' => 'Form')),
			e107::_getTemplate($id, null, 'core/e107/templates/'.$id, $path, true));
		$this->assertSame(array('form' => array('X' => 'pre{---}post')),
			e107::getRegistry('templates/wrapper/'.$id));
	}

	public function testLoaderLocalsNeverLeakIntoTheTemplate()
	{
		$id = $this->uniqueTemplateId();
		$upper = strtoupper($id);
		$path = $this->writeTemplate("<?php\n"
			."\$AAA = 'a';\n"
			."\$BBB_CCC = 'b';\n"
			."\$D1 = 'd';\n"
			."\$lower = 'no';\n"
			."\$Mixed = 'no';\n"
			."\$_UNDER = 'no';\n"
			."\$".$upper."_INFO = array('form' => array());\n"
			."\$".$upper."_WRAPPER = array('form' => array());\n"
			."\$SC_WRAPPER = array('E107_TESTS_TP_LEAK' => array('pre' => '', 'post' => ''));\n");

		$loaded = e107::_getTemplate($id, null, 'core/e107/templates/'.$id, $path);

		$this->assertSame(array('AAA', 'BBB_CCC', 'D1'), array_keys($loaded));
	}

	/**
	 * @return string a template id no other test in the process has used, so include_once() runs
	 */
	protected function uniqueTemplateId()
	{
		return 'tpsrc'.mt_rand(100000, 999999);
	}

	/**
	 * @param string $body
	 * @param string|null $path where to write it, a temporary file when null
	 * @return string
	 */
	protected function writeTemplate($body, $path = null)
	{
		if($path === null)
		{
			$path = sys_get_temp_dir().'/e107-tpsource-'.getmypid().'-'.mt_rand(100000, 999999).'.php';
		}

		file_put_contents($path, $body);
		$this->tempFiles[] = $path;

		return $path;
	}

	/**
	 * Reset both registry flavours of a core template so each test exercises
	 * a fresh load. The 'templates/source/' capture is intentionally left
	 * alone: include_once() cannot re-execute a file within one PHP process,
	 * so that capture is the only remaining source of the file's contents.
	 *
	 * @param string $id
	 * @return void
	 */
	protected function clearTemplateRegistry($id)
	{
		foreach(array('', '/ext') as $flavour)
		{
			e107::setRegistry('core/e107/templates/'.$id.$flavour.'/info', null);
			e107::setRegistry('core/e107/templates/'.$id.$flavour, null);
		}
	}

	/**
	 * When the theme provides no override, both flavours resolve to the same
	 * physical file and must return identical content. With an override
	 * present the contents legitimately differ, so only non-emptiness (already
	 * asserted by the caller) applies.
	 *
	 * @param string $id
	 * @param mixed  $override
	 * @param mixed  $plain
	 * @return void
	 */
	protected function assertBothFlavoursMatchWithoutThemeOverride($id, $override, $plain)
	{
		if(e107::coreTemplatePath($id, true) === e107::coreTemplatePath($id, false))
		{
			$this->assertSame($override, $plain);
		}
	}
}
