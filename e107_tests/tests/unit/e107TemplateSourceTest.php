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
