<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * e_upgrade::releaseCheck() asks a plugin's or theme's release feed whether a
 * newer version exists and announces it to the admin. The answer is cached
 * for an hour, and the message handler was only fetched on the branch that
 * reads the cache. Every other path, which is the one that actually finds a
 * new release, reached the announcement with nothing to announce it through.
 */
class e_upgradeTest extends \Codeception\Test\Unit
{
	/** @var mixed */
	private $savedXml;

	/** @var mixed */
	private $savedCache;

	public function _before()
	{
		require_once(e_HANDLER.'e_upgrade_class.php');

		$this->savedXml = e107::getRegistry('core/e107/singleton/xmlClass');
		$this->savedCache = isset($GLOBALS['e107cache']) ? $GLOBALS['e107cache'] : null;
	}

	public function _after()
	{
		e107::setRegistry('core/e107/singleton/xmlClass', $this->savedXml);
		$GLOBALS['e107cache'] = $this->savedCache;
		e107::getMessage()->reset(false, false, true);
	}

	/**
	 * A feed naming a version newer than the installed one, with nothing in
	 * the cache: the announcement path. It used to call addInfo() on null.
	 */
	public function testANewReleaseIsAnnouncedWhenTheCacheIsCold()
	{
		$feed = array('plugin' => array(
			0 => array('@attributes' => array(
				'name' => 'Fixture Plugin', 'folder' => 'fixture_plugin',
				'version' => '9.9', 'url' => 'https://example.invalid/fixture',
			)),
			1 => array('@attributes' => array(
				'name' => 'Other Plugin', 'folder' => 'other_plugin',
				'version' => '2.0', 'url' => 'https://example.invalid/other',
			)),
		));

		e107::setRegistry('core/e107/singleton/xmlClass',
			$this->make('xmlClass', array('loadXMLfile' => $feed)));

		$GLOBALS['e107cache'] = $this->make('ecache', array(
			'retrieve' => false, // nothing cached
			'set'      => true,
		));

		$upgrade = new e_upgrade();
		$upgrade->setOptions(array(
			'curFolder'  => 'fixture_plugin',
			'curVersion' => '1.0',
			'releaseUrl' => 'https://example.invalid/releases.xml',
		));

		$upgrade->releaseCheck('plugin', false);

		$announced = e107::getMessage()->get('info', 'default', true, true);

		$this->assertStringContainsString('Fixture Plugin',
			implode("\n", (array) $announced),
			'The newer release has to reach the admin, not a fatal error.');
	}
}
