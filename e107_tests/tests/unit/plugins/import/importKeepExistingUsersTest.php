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
 * What a provider's {@see base_import_class::copyUserData()} hands the import loop for one source user, with the existing data kept or deleted.
 */
class importKeepExistingUsersTest extends \Test\Unit
{
	protected function _before()
	{
		e107::lan('import', true);

		require_once(e_PLUGIN.'import/providers/drupal_import_class.php');
		require_once(e_PLUGIN.'import/providers/e107_import_class.php');
		require_once(e_PLUGIN.'import/providers/joomla_import_class.php');
	}

	protected function _after()
	{
		$_POST = array();
	}

	/**
	 * @return array<string, array{0: string, 1: array<string, string>}>
	 */
	public function sourceUsers()
	{
		return array(
			'Drupal' => array('drupal_import', array(
				'uid'       => '7',
				'name'      => 'imported',
				'pass'      => 'hash',
				'mail'      => 'imported@example.test',
				'signature' => '',
				'created'   => '1490000000',
				'login'     => '1490000100',
				'timezone'  => '0',
				'language'  => '',
				'picture'   => '',
			)),
			'e107' => array('e107_import', array(
				'user_id'           => '7',
				'user_name'         => 'imported',
				'user_loginname'    => 'imported',
				'user_password'     => 'hash',
				'user_email'        => 'imported@example.test',
				'user_hideemail'    => '1',
				'user_join'         => '1490000000',
				'user_admin'        => '0',
				'user_lastvisit'    => '1490000100',
				'user_login'        => '',
				'user_ban'          => '0',
				'user_customtitle'  => '',
				'user_sess'         => '',
				'user_signature'    => '',
				'user_image'        => '',
				'user_currentvisit' => '1490000100',
				'user_lastpost'     => '0',
				'user_chats'        => '0',
				'user_comments'     => '0',
				'user_ip'           => '',
				'user_prefs'        => '',
				'user_visits'       => '1',
				'user_class'        => '',
				'user_perms'        => '',
				'user_xup'          => '',
				'user_language'     => '',
				'user_country'      => '',
				'user_location'     => '',
				'user_aim'          => '',
				'user_icq'          => '',
				'user_yahoo'        => '',
				'user_msn'          => '',
				'user_homepage'     => '',
				'user_birthday'     => '',
				'user_timezone'     => '0',
			)),
			'Joomla' => array('joomla_import', array(
				'id'            => '7',
				'name'          => 'imported',
				'username'      => 'imported',
				'password'      => 'hash',
				'email'         => 'imported@example.test',
				'registerDate'  => '2017-03-20 08:53:20',
				'usertype'      => 'Registered',
				'lastvisitDate' => '2017-03-20 08:55:00',
				'block'         => '0',
			)),
		);
	}

	/**
	 * @dataProvider sourceUsers
	 * @param string $class
	 * @param array<string, string> $source
	 */
	public function testKeepingExistingDataCopiesTheUserUnderANewId($class, $source)
	{
		$row = $this->copyUser($class, $source, false);

		self::assertIsArray($row, $class.' returned no row, so the import stops');
		self::assertSame(0, $row['user_id']);
		self::assertSame('imported', $row['user_name']);
		self::assertSame('imported', $row['user_loginname']);
		self::assertSame('imported@example.test', $row['user_email']);
	}

	/**
	 * @dataProvider sourceUsers
	 * @param string $class
	 * @param array<string, string> $source
	 */
	public function testDeletingExistingDataKeepsTheSourceId($class, $source)
	{
		$row = $this->copyUser($class, $source, true);

		self::assertIsArray($row);
		self::assertSame('7', $row['user_id']);
		self::assertSame('imported', $row['user_name']);
	}

	/**
	 * @param string $class
	 * @param array<string, string> $source
	 * @param bool $copyUserInfo
	 * @return array<string, mixed>|null
	 */
	private function copyUser($class, $source, $copyUserInfo)
	{
		$_POST = array('version' => 6, 'pcontent' => 0);

		$provider = new $class();
		$provider->init();
		$provider->copyUserInfo = $copyUserInfo;

		$target = array('user_id' => 0, 'user_name' => '', 'user_loginname' => '', 'user_email' => '');

		return $provider->copyUserData($target, $source);
	}
}
