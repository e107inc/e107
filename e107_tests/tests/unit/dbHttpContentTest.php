<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The Database tools check for stored content that still embeds the site's own address over http://.
 *
 * @see https://github.com/e107inc/e107/issues/6860
 */
class dbHttpContentTest extends \Test\Unit
{
	const MARKER = 'e107help-6860';

	/** Opens the check under a Site URL and a trusted host that no stored row holds, with the SEP the admin theme would define. */
	const SEED = "define('SEP', ' &raquo; '); \$_GET['mode'] = 'http_content'; e107::getConfig()->set('siteurl', 'https://e107help.test/')->set('trusted_hosts', 'mirror.e107help.test'); ";

	/** A core row named as a backup of the preferences. */
	const BACKUP = 'e107help6860_Backup';

	/** A core row of any other name. */
	const CORE_ROW = 'e107help6860';

	/** @var callable puts back the stored core preferences */
	private $restorePrefs;

	protected function _before()
	{
		$this->restorePrefs = $this->keepStoredCorePrefs();
	}

	protected function _after()
	{
		$sql = e107::getDb();
		$sql->createQueryBuilder()->delete('generic')->where('gen_type', self::MARKER)->execute();
		$sql->createQueryBuilder()->delete('core')->whereIn('e107_name', array(self::BACKUP, self::CORE_ROW))->execute();
		call_user_func($this->restorePrefs);
	}

	public function testAWelcomeMessageThatEmbedsTheSitesOwnAddressOverHttpIsCounted()
	{
		$before = $this->rowsListed('generic');

		$this->storeGenericText("<img src='http://e107help.test/e107_media/welcome.png' alt='' />");

		self::assertSame($before + 1, $this->rowsListed('generic'));
	}

	public function testTheSitesAddressOverHttpsAndAnotherSitesOverHttpAreNotCounted()
	{
		$this->storeGenericText("<img src='http://e107help.test/e107_media/welcome.png' alt='' />");
		$before = $this->rowsListed('generic');

		$this->storeGenericText("<img src='https://e107help.test/e107_media/welcome.png' alt='' />");
		$this->storeGenericText("<img src='http://elsewhere.example.test/welcome.png' alt='' />");

		self::assertGreaterThan(0, $before, 'The check did not count the row that embeds the site over http://, so nothing below is measured.');
		self::assertSame($before, $this->rowsListed('generic'));
	}

	public function testTheSitesWwwAliasAndItsTrustedHostsOverHttpAreCounted()
	{
		$before = $this->rowsListed('generic');

		$this->storeGenericText("<img src='http://www.e107help.test/e107_media/welcome.png' alt='' />");
		$this->storeGenericText("<img src='http://mirror.e107help.test/e107_media/welcome.png' alt='' />");

		self::assertSame($before + 2, $this->rowsListed('generic'));
	}

	public function testABackupOfThePreferencesIsNotCounted()
	{
		$before = $this->rowsListed('core');

		foreach(array(self::BACKUP, self::CORE_ROW) as $name)
		{
			e107::getDb()->createQueryBuilder()->insert('core')->values(array(
				'e107_name'  => $name,
				'e107_value' => "a:1:{s:7:\"siteurl\";s:21:\"http://e107help.test/\";}",
			))->execute();
		}

		self::assertSame($before + 1, $this->rowsListed('core'));
	}

	/**
	 * @param string $table
	 * @return int the rows of $table the check names, 0 when it names none
	 */
	private function rowsListed($table)
	{
		$page = $this->renderInBootedCli('e107_admin/db.php', self::SEED);

		return preg_match('/\b'.preg_quote($table, '/').' \((\d+)\)/', $page, $match) ? (int) $match[1] : 0;
	}

	/**
	 * @param string $text
	 * @return void
	 */
	private function storeGenericText($text)
	{
		e107::getDb()->createQueryBuilder()->insert('generic')->values(array(
			'gen_type'      => self::MARKER,
			'gen_datestamp' => time(),
			'gen_user_id'   => 0,
			'gen_ip'        => '',
			'gen_intdata'   => 0,
			'gen_chardata'  => $text,
		))->execute();
	}
}
