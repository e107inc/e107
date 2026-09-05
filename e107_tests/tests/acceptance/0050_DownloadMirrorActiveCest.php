<?php

/**
 * P6 item 7. request.php has two branches that serve a download, and only one
 * of them asks whether the download is still active.
 *
 * The by-id branch tests it at e107_plugins/download/request.php:222-231 and
 * answers with LAN_dl_78 when download_active is 0. The mirror branch at
 * :110-155 tests the same row's userclass and then goes straight on to
 * increment the counters and redirect to the mirror address. Its only mention
 * of download_active is
 *
 *   if(!empty($pref['download_limits']) && $row['download_active'] == 1)
 *
 * which decides whether to apply download limits, not whether to serve.
 *
 * A withdrawn download therefore stays available to anyone who knows a mirror
 * id, and the site goes on counting the requests.
 */
class DownloadMirrorActiveCest
{
	const RESET_FILE = 'e107_tests_p6_download_reset.php';

	/**
	 * Same-origin on purpose: the positive control follows the redirect, and
	 * nothing in this container can reach an outside host.
	 */
	private $mirrorBase = '/e107_images/button.png?p6mirror=';

	/** @var int */
	private $mirrorId;

	/** @var int */
	private $categoryId;

	/** @var string */
	private $needle;

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::RESET_FILE, $this->resetSource());
		$I->amOnPage('/'.self::RESET_FILE);
		$I->seeInSource('RESET_DONE');

		// request.php never asks whether the plugin is installed, so the tables
		// are all it needs; installing and uninstalling the plugin around every
		// test would drop the tables under the Db module's own row cleanup.
		$I->havePluginTables('download');

		$this->needle = uniqid('p6mirror', false);

		$this->categoryId = $I->haveInDatabase('e107_download_category', array(
			'download_category_name'        => 'P6 Mirror '.$this->needle,
			'download_category_description' => '',
			'download_category_icon'        => '',
			'download_category_parent'      => 0,
			'download_category_class'       => 0,
			'download_category_order'       => 0,
			'download_category_sef'         => 'p6-mirror-'.$this->needle,
		));

		$this->mirrorId = $I->haveInDatabase('e107_download_mirror', array(
			'mirror_name'        => 'P6 mirror '.$this->needle,
			'mirror_url'         => $this->mirrorAddress(),
			'mirror_image'       => '',
			'mirror_location'    => '',
			'mirror_description' => '',
			'mirror_count'       => 0,
		));
	}

	public function _after(AcceptanceTester $I)
	{
		$I->deleteAppFile(self::RESET_FILE);
	}

	/**
	 * @return string
	 */
	private function mirrorAddress()
	{
		return $this->mirrorBase.$this->needle;
	}

	/**
	 * @return string the screenshot file name, which is the marker the image
	 *   branch tests look for in the rendered page
	 */
	private function screenshot()
	{
		return 'p6shot-'.$this->needle.'.png';
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $active
	 * @param int $class download_class
	 * @return int download id
	 */
	private function haveDownload(AcceptanceTester $I, $active, $class = 0)
	{
		return $I->haveInDatabase('e107_download', array(
			'download_name'        => 'P6 mirror download '.$this->needle.' '.$active.' '.$class,
			'download_url'         => '',
			'download_sef'         => '',
			'download_author'      => '',
			'download_description' => '',
			'download_keywords'    => '',
			'download_filesize'    => 0,
			'download_requested'   => 0,
			'download_category'    => $this->categoryId,
			'download_active'      => $active,
			'download_datestamp'   => time(),
			'download_thumb'       => '',
			'download_image'       => $this->screenshot(),
			'download_comment'     => 0,
			'download_class'       => $class,
			// mid,address,requests - the shape request.php parses at :137-148
			'download_mirror'      => $this->mirrorId.','.$this->mirrorAddress().',0'.chr(1),
			'download_mirror_type' => 0,
			'download_visible'     => 0,
		));
	}

	public function anInactiveDownloadIsNotServedFromItsMirror(AcceptanceTester $I)
	{
		$I->wantTo('refuse a withdrawn download when it is asked for through a mirror');

		$downloadId = $this->haveDownload($I, 0);

		// Measured on the response rather than by following it: PhpBrowser
		// chases redirects without a cap, and a failed fetch would then be
		// indistinguishable from a refusal.
		$I->stopFollowingRedirects();
		$I->amOnPage('/e107_plugins/download/request.php?mirror.'.$downloadId.'.'.$this->mirrorId);
		$I->startFollowingRedirects();

		$I->seeNoRedirectTo($this->needle);
		$I->dontSeeInSource('Uncaught Error');
		$I->dontSeeInSource('Fatal error');

		$I->seeInDatabase('e107_download', array(
			'download_id'        => $downloadId,
			'download_requested' => 0,
		));
		$I->seeInDatabase('e107_download_mirror', array(
			'mirror_id'    => $this->mirrorId,
			'mirror_count' => 0,
		));
	}

	/**
	 * The sibling branch, which is the one that does test download_active, and
	 * which the fix for the mirror branch will most likely reuse.
	 *
	 * It does not currently reach the visitor. request.php opens with
	 * e107::lan('download', 'download'), which asks for a language file named
	 * English_download.php; the plugin ships English_front.php, so nothing is
	 * loaded and every message constant in the file is undefined. On PHP 8 the
	 * refusal at :222-231 is therefore a fatal rather than a message, and any
	 * refusal added to the mirror branch out of the same constants would be one
	 * too.
	 */
	public function theByIdBranchRefusesAWithdrawnDownloadWithAMessage(AcceptanceTester $I)
	{
		$I->wantTo('keep the by-id branch refusing a withdrawn download, and saying so');

		$downloadId = $this->haveDownload($I, 0);

		$I->amOnPage('/e107_plugins/download/request.php?'.$downloadId);

		$I->dontSeeInSource('Uncaught Error');
		$I->dontSeeInSource('Fatal error');
		$I->see('That download has been disabled or discontinued');
	}

	/**
	 * The third request shape in the same script. e_url.php publishes
	 * download/image/{download_id}/{download_sef} for it, and it renders the
	 * screenshot straight out of the download row.
	 */
	public function theImageBranchRefusesAWithdrawnDownload(AcceptanceTester $I)
	{
		$I->wantTo('refuse a withdrawn download when its screenshot is asked for');

		$downloadId = $this->haveDownload($I, 0);

		$I->amOnPage('/e107_plugins/download/request.php?download.'.$downloadId);

		$I->dontSeeInSource('Uncaught Error');
		$I->dontSeeInSource('Fatal error');
		$I->dontSeeInSource($this->screenshot());
		$I->see('That download has been disabled or discontinued');
	}

	/**
	 * The same branch, on the userclass axis rather than the active one.
	 */
	public function theImageBranchRefusesARestrictedDownload(AcceptanceTester $I)
	{
		$I->wantTo('refuse a class-restricted download when its screenshot is asked for');

		$downloadId = $this->haveDownload($I, 1, 254); // members only

		$I->amOnPage('/e107_plugins/download/request.php?download.'.$downloadId);

		$I->dontSeeInSource('Uncaught Error');
		$I->dontSeeInSource('Fatal error');
		$I->dontSeeInSource($this->screenshot());
	}

	/**
	 * Positive control for both. A branch that stopped serving screenshots
	 * altogether would satisfy the two refusals above.
	 */
	public function theImageBranchStillServesAPublicDownloadsScreenshot(AcceptanceTester $I)
	{
		$I->wantTo('keep serving the screenshot of a download anyone may have');

		$downloadId = $this->haveDownload($I, 1);

		$I->amOnPage('/e107_plugins/download/request.php?download.'.$downloadId);

		$I->dontSeeInSource('Uncaught Error');
		$I->seeInSource($this->screenshot());
	}

	/**
	 * Positive control. A mirror that stopped serving anything would satisfy the
	 * refusal above.
	 */
	public function anActiveDownloadIsStillServedFromItsMirror(AcceptanceTester $I)
	{
		$I->wantTo('keep serving an active download through its mirror');

		$downloadId = $this->haveDownload($I, 1);

		$I->stopFollowingRedirects();
		$I->amOnPage('/e107_plugins/download/request.php?mirror.'.$downloadId.'.'.$this->mirrorId);
		$I->startFollowingRedirects();
		$I->seeResponseCodeIs(302);

		$I->seeInDatabase('e107_download', array(
			'download_id'        => $downloadId,
			'download_requested' => 1,
		));
		$I->seeInDatabase('e107_download_mirror', array(
			'mirror_id'    => $this->mirrorId,
			'mirror_count' => 1,
		));
	}

	/**
	 * @return string
	 */
	private function resetSource()
	{
		return <<<'PHP'
<?php
// Fixture for 0039_DownloadMirrorActiveCest. Removed again in the Cest's _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');
echo 'RESET_DONE';
PHP;
	}
}
