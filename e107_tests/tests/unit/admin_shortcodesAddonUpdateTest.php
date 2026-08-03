<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * admin_shortcodes::renderAddonUpdate() is the third copy of the addons-panel
 * shape, after e107_admin/boot.php's ?mode=core and ?mode=addons blocks. Every
 * value in the row it renders came back from the marketplace over the network,
 * and five of them went into markup with no encoding:
 *
 *   $row['modalDownload']            an href
 *   $row['icon'] / ['thumbnail']     an img src
 *   $row['name'], ['version'], ['date']   element text
 *
 * Its output is stored in the session as 'addons-update-status'
 * (e107_admin/boot.php:92), so it is rendered again on later requests without
 * the composition being re-run.
 *
 * All three sinks in that file take double-quoted attributes or element text,
 * so the payloads here carry a double quote and an angle bracket respectively.
 */
class admin_shortcodesAddonUpdateTest extends \Codeception\Test\Unit
{
	/** @var admin_shortcodes */
	private $sc;

	const ATTR_PAYLOAD = 'https://example.com/?a=P8ADDON" onmouseover="alert(1)';
	const ATTR_PAYLOAD_ENCODED = 'https://example.com/?a=P8ADDON&quot; onmouseover=&quot;alert(1)';

	const TEXT_PAYLOAD = 'P8ADDON<img src=x onerror="alert(1)">';
	const TEXT_PAYLOAD_ENCODED = 'P8ADDON&lt;img src=x onerror=&quot;alert(1)&quot;&gt;';

	protected function _before()
	{
		// LAN_RELEASED lives in the plugin manager's admin LAN file, which only
		// plugin.php loads. renderAddonUpdate() uses it unguarded, so on PHP 8 it
		// is an undefined-constant Error anywhere else. That is its own defect and
		// not this one; load the file so the encoding is what is measured.
		//
		// Loaded rather than defined by hand. A bare define() here wins the race
		// whenever the shuffle draws this test first, and then the real file's
		// own define() on the same constant raises a warning that the suite
		// converts to an exception. That exception is thrown *inside* the
		// include, which unwinds at that line and leaves every constant below it
		// undefined for the rest of the process, permanently: include_once and
		// the registry guard both already record the file as loaded, so nothing
		// can ever define them. LAN_ADDONS is one of the twelve below it, which
		// is how a helpful two-line shim took out scriptsTest at random.
		e107::coreLan('plugin', true);

		require_once(e_CORE.'shortcodes/batch/admin_shortcodes.php');

		try
		{
			$this->sc = $this->make('admin_shortcodes');
		}
		catch (Exception $e)
		{
			self::fail($e->getMessage());
		}
	}

	/**
	 * @return array a marketplace row with every renderable value hostile
	 */
	private function hostileRow()
	{
		return array(
			'modalDownload' => self::ATTR_PAYLOAD,
			'icon'          => self::ATTR_PAYLOAD,
			'thumbnail'     => self::ATTR_PAYLOAD,
			'name'          => self::TEXT_PAYLOAD,
			'version'       => self::TEXT_PAYLOAD,
			'date'          => self::TEXT_PAYLOAD,
		);
	}

	public function testTheAddonUpdateAttributesAreEncoded()
	{
		$actual = $this->sc->renderAddonUpdate(array($this->hostileRow()));

		$this->assertStringNotContainsString('P8ADDON" onmouseover=', $actual,
			'A marketplace URL closed the attribute it was written into.');
		$this->assertStringContainsString(self::ATTR_PAYLOAD_ENCODED, $actual,
			'A marketplace URL was not encoded for an attribute context.');
	}

	public function testTheAddonUpdateTextIsEncoded()
	{
		$actual = $this->sc->renderAddonUpdate(array($this->hostileRow()));

		$this->assertStringNotContainsString('P8ADDON<img', $actual,
			'A marketplace name, version or date was written into element text as markup.');
		$this->assertStringContainsString(self::TEXT_PAYLOAD_ENCODED, $actual,
			'A marketplace name, version or date was not encoded for a text context.');
	}

	/**
	 * Positive control. The panel still has to render a real update entry, so a
	 * fix that returned nothing at all cannot pass.
	 */
	public function testAnOrdinaryAddonUpdateStillRenders()
	{
		$actual = $this->sc->renderAddonUpdate(array(array(
			'modalDownload' => 'https://e107.org/download?id=7&t=plugin',
			'icon'          => 'https://e107.org/icon.png',
			'thumbnail'     => '',
			'name'          => 'forum',
			'version'       => '2.0',
			'date'          => '2026-08-02',
		)));

		$this->assertStringContainsString('href="https://e107.org/download?id=7&amp;t=plugin"', $actual);
		$this->assertStringContainsString('src="https://e107.org/icon.png"', $actual);
		$this->assertStringContainsString('>forum<', $actual);
		$this->assertStringContainsString('2026-08-02', $actual);
	}

	/**
	 * And the scheme half, which no encoder answers.
	 */
	public function testAJavascriptSchemeNeverReachesTheDownloadHref()
	{
		$row = $this->hostileRow();
		$row['modalDownload'] = 'javascript:alert(1)';
		$row['icon'] = 'javascript:alert(2)';
		$row['thumbnail'] = '';

		$actual = $this->sc->renderAddonUpdate(array($row));

		$this->assertStringNotContainsString('javascript:', $actual,
			'A marketplace URL whose scheme executes was emitted as an href or a src.');
	}
}
