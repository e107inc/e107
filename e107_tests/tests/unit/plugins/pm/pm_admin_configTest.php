<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

require_once(__DIR__.'/pm_limit_fixture.php');

/**
 * @group plugins
 *
 * What the Update button on the Limits page of the private messaging plugin's admin area stores, pressed with the limit mode at its plugin.xml default.
 */
class pm_admin_configTest extends pm_limit_fixture
{
	protected function _before()
	{
		parent::_before();

		$this->storePrefs(array('pm_limits' => 0));
	}

	public function testTheUpdateStoresTheLimitModeWhenNoClassHasALimit()
	{
		$page = $this->update(array('pm_limits' => '1'));

		self::assertSame(1, $this->storedMode(), "The update did not store the limit mode:\n".$page);
		self::assertStringContainsString("<option value='1' selected='selected'>", $page, "The page does not show the mode it stored:\n".$page);
	}

	public function testTheUpdateStoresTheLimitModeAndTheClassLimitsTogether()
	{
		$id = $this->addLimit(e_UC_MEMBER, 2, 4);

		$page = $this->update(array(
			'pm_limits'    => '2',
			'inbox_count'  => array($id => '10'),
			'outbox_count' => array($id => '20'),
			'inbox_size'   => array($id => '30'),
			'outbox_size'  => array($id => '40'),
		));

		self::assertSame(2, $this->storedMode(), "The update did not store the limit mode:\n".$page);

		$row = e107::getDb()->createQueryBuilder()->select('gen_user_id', 'gen_ip', 'gen_intdata', 'gen_chardata')
			->from('generic')->where('gen_id', $id)->fetchRow();
		self::assertEquals(array('gen_user_id' => 10, 'gen_ip' => 20, 'gen_intdata' => 30, 'gen_chardata' => 40), $row, "The update did not store the class limits:\n".$page);
	}

	/**
	 * Presses Update on the Limits page.
	 *
	 * @param array $post what the form posts besides its button
	 * @return string everything the page wrote
	 */
	private function update(array $post)
	{
		return $this->renderPage('e107_plugins/pm/admin_config.php', 'mode=main&action=limits',
			self::posting(array('updatelimits' => 'no-value') + $post), array('e_ADMIN_AREA' => true));
	}

	/**
	 * @return mixed the limit mode in the plugin's preferences as the database holds them
	 */
	private function storedMode()
	{
		$prefs = e107::unserialize((string) self::storedPrefsValue('plugin_pm'));

		return isset($prefs['pm_limits']) ? $prefs['pm_limits'] : null;
	}
}
