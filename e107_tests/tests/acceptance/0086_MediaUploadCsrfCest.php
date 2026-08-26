<?php

/**
 * The Plupload endpoint reads its parameters from $_REQUEST, so a GET is enough.
 *
 * e107_web/js/plupload/upload.php had one gate, if(!ADMIN) exit, and ADMIN is
 * not a defence against request forgery: in a forgery the victim is the one
 * holding the permission and the forged request rides their session.
 * media_class::processAjaxUpload() then takes the file name, chunk and chunk
 * count out of $_REQUEST and hands the whole of $_REQUEST to
 * processAjaxImport(), so nothing about the endpoint needs a request body. A
 * GET from a hostile page unlinked stale .part files in e_IMPORT, wrote a file
 * the caller named, renamed it into place and could import it as media.
 *
 * The stale .part sweep is what these cases weigh, because it happens before
 * any file type or content check and so runs whatever the request carries.
 *
 * Both uploaders already send a token: the Plupload queue as a multipart
 * parameter and Dropzone through its params option. Nothing read it, and the
 * Media Manager's own uploader URL carried none at all, so the last case
 * follows the URL the Media Manager publishes rather than one of the test's
 * own.
 *
 * The fixture writes a probe into the docroot, so it answers nobody who cannot
 * show the secret this run minted for it, and it loads class2.php before it
 * looks at what it was asked to do. A probe left behind by a run that died
 * would otherwise let anyone at all create e_IMPORT and add and remove the
 * fixed-name files this case counts there. That is untidy where the signup
 * fixture is dangerous, and it is still state a stranger must not be able to
 * reach.
 *
 * @see e107_handlers/session_handler.php  e_session::attest()
 * @see e107_handlers/media_class.php      e_media::processAjaxUpload()
 */
class MediaUploadCsrfCest
{
	const PROBE_FILE = 'e107_tests_upload_csrf.php';

	const ENDPOINT = '/e107_web/js/plupload/upload.php';

	const DIALOG = '/e107_admin/image.php?mode=main&action=dialog&for=news&tagid=news_thumbnail&iframe=1&image=1';

	/** Name the forged request asks the endpoint to write into e_IMPORT. */
	const UPLOAD = 'e107_tests_upload_csrf.txt';

	/** A distinctive fragment of LANUPLOAD_REFUSED_TOKEN_MISSING. */
	const REFUSED = 'no security token';

	/**
	 * A request the framework does police: attest() refuses any e-token it
	 * cannot validate, whatever the request method, and answers with this.
	 */
	const UNAUTHORIZED = 'Unauthorized access!';

	/** @var string what a caller shows to prove it is this run of this case */
	private $secret;

	public function _before(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$this->secret = substr(hash('sha256', uniqid('', true).mt_rand()), 0, 32);
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->assertSame('STALE=1 UPLOADED=0', $this->probe($I, 'act=seed'),
			'the fixture must be able to leave a stale .part file in e_IMPORT');
	}

	public function _after(AcceptanceTester $I)
	{
		$this->probe($I, 'act=clean');
		$I->deleteAppFile(self::PROBE_FILE);
	}

	/**
	 * One <img> tag on any page an administrator visits, and the endpoint runs.
	 */
	public function aTokenlessGetDoesNotReachTheUploader(AcceptanceTester $I)
	{
		$I->amOnPage(self::ENDPOINT.'?name='.self::UPLOAD);
		$answer = $I->grabPageSource();

		$I->assertSame('STALE=1 UPLOADED=0', $this->probe($I, 'act=state'),
			'a query string alone must not reach the uploader');
		$I->assertStringContainsString(self::REFUSED, $answer,
			'the uploader must answer with the reason it refused');
	}

	/**
	 * Presence is all the endpoint tests; whether the value is the right one is
	 * attest()'s half. Both halves are needed, so assert the second one too.
	 */
	public function aGetCarryingTheWrongTokenIsRefused(AcceptanceTester $I)
	{
		$I->amOnPage(self::ENDPOINT.'?name='.self::UPLOAD.'&e-token=not-even-close');
		$answer = $I->grabPageSource();

		$I->assertSame('STALE=1 UPLOADED=0', $this->probe($I, 'act=state'),
			'a token that does not validate must not reach the uploader');
		$I->assertStringContainsString(self::UNAUTHORIZED, $answer,
			'a token that does not validate must be refused by the framework');
	}

	/**
	 * The control that matters most. A guard on an endpoint the Media Manager
	 * calls is worse than the hole it closes if uploading stops working, so
	 * this follows the URL the Media Manager publishes, token and all.
	 */
	public function theMediaManagersOwnUploaderUrlStillReachesTheUploader(AcceptanceTester $I)
	{
		$I->amOnPage($this->publishedUploaderUrl($I).'&name='.self::UPLOAD);

		$I->dontSeeInSource(self::REFUSED);
		$I->dontSeeInSource(self::UNAUTHORIZED);
		$I->assertStringContainsString('STALE=0', $this->probe($I, 'act=state'),
			'the Media Manager must still be able to reach the uploader');
	}

	/**
	 * The probe writes and deletes files in e_IMPORT, so a caller that cannot
	 * show this run's secret has to get nothing at all. A probe left in the
	 * docroot by a run that died is otherwise an anonymous way to disturb what
	 * the uploader keeps there.
	 */
	public function theProbeRefusesACallerThatCannotShowTheSecret(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=clean');

		$I->seeResponseCodeIs(403);
		$I->assertSame('STALE=1 UPLOADED=0', $this->probe($I, 'act=state'),
			'a refused caller must not have swept e_IMPORT');
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string path to follow, tokenised exactly as the Media Manager
	 *   published it
	 */
	private function publishedUploaderUrl(AcceptanceTester $I)
	{
		$I->amOnPage(self::DIALOG);

		if(!preg_match('#<div id="uploader"[^>]*rel="([^"]+)"#', $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('the Media Manager published no uploader URL');
		}

		return html_entity_decode($matches[1]);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $query
	 * @return string
	 */
	private function probe(AcceptanceTester $I, $query)
	{
		$I->amOnPage($this->probeUrl($query));

		return trim($I->grabPageSource());
	}

	/**
	 * @param string $query
	 * @return string
	 */
	private function probeUrl($query)
	{
		$url = '/'.self::PROBE_FILE.'?probe='.$this->secret;

		return ($query === '') ? $url : $url.'&'.$query;
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		$secret = $this->secret;
		$upload = self::UPLOAD;

		return <<<PHP
<?php
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}

if(!isset(\$_GET['probe']) || !hash_equals('$secret', \$_GET['probe']))
{
	header('HTTP/1.1 403 Forbidden', true, 403);
	echo 'Unauthorized access!';
	exit;
}

header('Content-Type: text/plain');

\$stale = e_IMPORT.'e107_tests_upload_csrf_stale.part';
\$upload = e_IMPORT.'$upload';
\$act = isset(\$_GET['act']) ? \$_GET['act'] : '';

if(!is_dir(e_IMPORT))
{
	@mkdir(e_IMPORT, 0755, true);
}

if(\$act === 'seed' || \$act === 'clean')
{
	@unlink(\$stale);
	@unlink(\$upload);
	@unlink(\$upload.'.part');
}

if(\$act === 'seed')
{
	file_put_contents(\$stale, 'stale');
	touch(\$stale, time() - 86400);
}

clearstatcache();

echo 'STALE='.(file_exists(\$stale) ? 1 : 0).' UPLOADED='.(file_exists(\$upload) ? 1 : 0);
PHP;
	}
}
