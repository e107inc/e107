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
 * The private messaging plugin's admin area: what the Limits page's Update button stores, pressed with the limit mode at its plugin.xml default, and which pages it offers.
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

	public function testTheAdminMenuNoLongerOffersMaintenance()
	{
		$page = $this->renderAdmin('mode=main&action=prefs');

		self::assertStringContainsString('action=limits"', $page, "The admin menu did not render, so its entries cannot be read:\n".$page);
		self::assertStringNotContainsString('action=maint"', $page, "The admin menu still links to the Maintenance page:\n".$page);
	}

	public function testTheMaintenanceAddressDrawsNoMaintenanceForm()
	{
		$page = $this->renderAdmin('mode=main&action=maint');

		self::assertStringNotContainsString("id='plugin-pm-maint'", $page, "The Maintenance page still renders:\n".$page);
		self::assertStringNotContainsString('pm_maint_execute', $page, "The Maintenance button still renders:\n".$page);
	}

	/**
	 * Presses Update on the Limits page.
	 *
	 * @param array $post what the form posts besides its button
	 * @return string everything the page wrote
	 */
	private function update(array $post)
	{
		return $this->renderAdmin('mode=main&action=limits', self::posting(array('updatelimits' => 'no-value') + $post));
	}

	/**
	 * @param string $query the admin page's query string
	 * @param string $seed PHP to run before the page, e.g. setting $_POST
	 * @return string everything the page wrote
	 */
	private function renderAdmin($query, $seed = '')
	{
		return $this->renderPage('e107_plugins/pm/admin_config.php', $query, $seed, array('e_ADMIN_AREA' => true));
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
