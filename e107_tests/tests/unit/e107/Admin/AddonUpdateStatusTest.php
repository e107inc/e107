<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Admin;

/**
 * The dashboard's add-on update notice is drawn from this one entry, whichever administrator's check wrote it.
 */
class AddonUpdateStatusTest extends \Test\Unit
{
	/** @var AddonUpdateStatus */
	private $status;

	/** @var mixed */
	private $checked;

	protected function _before()
	{
		$this->checked = \e107::getSession()->get(AddonUpdateStatus::CHECKED);
		\e107::getSession()->set(AddonUpdateStatus::CHECKED, true);
		$this->status = new AddonUpdateStatus(new \ecache(), \e107::getSession());
		$this->status->clear();
	}

	protected function _after()
	{
		$this->status->clear();
		\e107::getSession()->set(AddonUpdateStatus::CHECKED, $this->checked);
	}

	/**
	 * @param string $folder
	 * @return array a marketplace row as admin_shortcodes::getUpdateable() returns it
	 */
	private static function row($folder)
	{
		return array(
			'folder'        => $folder,
			'name'          => ucfirst($folder),
			'version'       => '9.0',
			'author'        => 'e107 Inc',
			'params'        => array('id' => 7, 'mode' => 'addon'),
			'modalDownload' => 'e107_admin/plugin.php?mode=online&action=download&e-token=abc&src=x',
		);
	}

	/**
	 * @param string $folder
	 * @return array the same row as it is kept
	 */
	private static function kept($folder)
	{
		$row = self::row($folder);
		unset($row['modalDownload']);

		return $row;
	}

	public function testTheCheckRunsWhenNoAnswerIsKept()
	{
		$this->assertNull($this->status->get());
	}

	public function testAnotherReaderGetsTheRowsWithoutTheirDownloadLinks()
	{
		$this->status->set(array('plugin' => array(self::row('forum')), 'theme' => array(self::row('voux'))));

		$this->assertSame(
			array('plugin' => array(self::kept('forum')), 'theme' => array(self::kept('voux'))),
			(new AddonUpdateStatus(new \ecache(), \e107::getSession()))->get()
		);
	}

	public function testACheckThatFoundNothingIsAnAnswer()
	{
		$this->status->set(array('plugin' => array(self::row('forum')), 'theme' => array()));
		$this->status->set(array('plugin' => array(), 'theme' => array()));

		$this->assertSame(array('plugin' => array(), 'theme' => array()), $this->status->get());
	}

	public function testTheCheckRunsAgainOnceTheAnswerIsCleared()
	{
		$this->status->set(array('plugin' => array(self::row('forum')), 'theme' => array()));
		$this->status->clear();

		$this->assertNull($this->status->get());
	}

	public function testAnAnswerOlderThanTheVersionListsIsCheckedAgain()
	{
		$this->status->set(array('plugin' => array(self::row('forum')), 'theme' => array()));
		touch((new \ecache())->cache_fname(AddonUpdateStatus::CACHE_TAG, true), time() - 60 * AddonUpdateStatus::LIFETIME - 60);
		clearstatcache();

		$this->assertNull($this->status->get());
	}

	public function testASessionThatHasNotCheckedRunsTheCheckWhateverIsKept()
	{
		$this->status->set(array('plugin' => array(self::row('forum')), 'theme' => array()));
		\e107::getSession()->set(AddonUpdateStatus::CHECKED, false);

		$this->assertNull($this->status->get());
	}

	/**
	 * The download links carry the session's token, and the cache refuses to store anything that does; no CLI request mints a token, so this runs in a process that holds one.
	 */
	public function testAnAnswerWhoseLinksCarryTheSessionTokenIsStillKept()
	{
		$probe = <<<'PHP'
define('e_TOKEN', 'e107t0ken0123456789abcdef0123456');

e107::getSession()->set(e107\Admin\AddonUpdateStatus::CHECKED, true);
$status = new e107\Admin\AddonUpdateStatus(e107::getCache(), e107::getSession());
$status->set(array('plugin' => array(array(
	'folder'        => 'forum',
	'version'       => '9.0',
	'modalDownload' => 'e107_admin/plugin.php?mode=online&action=download&e-token='.e_TOKEN,
)), 'theme' => array()));

echo '@@'.json_encode($status->get()).'@@';
$status->clear();
PHP;

		list($output, $status) = $this->runInBootedCli($probe);

		$printed = implode("\n", $output);
		$matches = array();

		$this->assertSame(0, $status, "the probe exited ".$status.":\n".$printed);
		$this->assertSame(1, preg_match('/@@(.*)@@/s', $printed, $matches), "the probe printed no answer:\n".$printed);
		$this->assertSame(array('plugin' => array(array('folder' => 'forum', 'version' => '9.0')), 'theme' => array()), json_decode($matches[1], true));
	}
}
