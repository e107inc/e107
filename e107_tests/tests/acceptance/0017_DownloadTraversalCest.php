<?php

/**
 * GHSA-87hm-vh32-7c3r: the download request handler must not read arbitrary
 * files. request.php used to concatenate the raw query string onto the
 * downloads directory and stream the result, while e_file::send()'s sandbox
 * silently permitted everything (realpath() of the absent e107_files/public/
 * returned false, and on PHP 8 strpos($path, "") is 0, which collapsed the
 * whole reject chain). Containment now happens in e_file::resolveSendPath(),
 * and by-name requests resolve through the download table so the userclass,
 * active-state and limit checks still apply.
 *
 * The dot-padded payload matters: `.././../` reaches the same file as
 * `../../` without ever containing the literal `../../` that the QUERY_STRING
 * filter looks for, so the filter was never the boundary.
 */
class DownloadTraversalCest
{
	/** @var string file served by the backwards-compatibility checks */
	private $canaryPath = 'e107_files/public/ghsa87hm_canary.txt';

	/**
	 * Stored the way the media picker writes one. A bare relative path would be
	 * resolved against the plugin directory, since e107 only chdir()s to e_ROOT
	 * in CLI mode.
	 */
	private $canaryUrl = '{e_FILE}public/ghsa87hm_canary.txt';
	private $canaryBody = 'GHSA87HMCANARYBODY';

	public function _before(AcceptanceTester $I)
	{
	}

	public function _after(AcceptanceTester $I)
	{
		// Leaving the canary behind would be worse than untidy: it makes
		// e107_files/public/ exist, and that directory's absence is what
		// collapsed the old sandbox. A leftover copy quietly hides the
		// vulnerability from anyone testing an unpatched tree.
		$I->deleteAppFile($this->canaryPath);
	}

	public function refusesToTraverseOutOfTheDownloadsDirectory(AcceptanceTester $I)
	{
		$I->wantTo('refuse arbitrary file reads through the download handler (GHSA-87hm-vh32-7c3r)');

		$payloads = array(
			'..%2f..%2f..%2fe107_config.php',       // the reported payload
			'.././.././../e107_config.php',        // no encoding, defeats the ../../ filter
			'%2e%2e%2f%2e%2e%2fe107_config.php',   // fully encoded
			'..%5c..%5c..%5ce107_config.php',      // backslash separators
			'pub_..%2f..%2f..%2fe107_config.php',  // via the pub_ branch
			'..%2f..%2f..%2fclass2.php',           // application source
		);

		foreach($payloads as $payload)
		{
			$I->amOnPage('/e107_plugins/download/request.php?'.$payload);

			// Markers unique to e107_config.php and to core PHP source.
			$I->dontSeeInSource('mySQLdefaultdb');
			$I->dontSeeInSource('mySQLpassword');
			$I->dontSeeInSource('e107_INIT');
		}
	}

	public function refusesTraversalThroughTheMediaHandler(AcceptanceTester $I)
	{
		$I->wantTo('refuse arbitrary file reads through the core media handler');

		$I->amOnPage('/request.php?file=e_PLUGIN/download/request.php');
		$I->dontSeeInSource('e107_INIT');
		$I->dontSeeInSource('mySQLdefaultdb');
	}

	public function malformedPayloadsDoNotFatal(AcceptanceTester $I)
	{
		$I->wantTo('handle NUL bytes and brace constants without a fatal error');

		// realpath() raises a ValueError on a NUL byte in PHP 8, which would turn
		// an unauthenticated request into a 500.
		foreach(array('x%00.zip', '%7Be_BASE%7De107_config.php', 'pub_%00') as $payload)
		{
			$I->amOnPage('/e107_plugins/download/request.php?'.$payload);
			$I->dontSeeResponseCodeIs(500);
			$I->dontSeeInSource('mySQLdefaultdb');
		}
	}

	public function stillServesALegitimateDownloadById(AcceptanceTester $I)
	{
		$I->wantTo('keep serving a normal download by numeric id');

		$id = $this->seedDownload($I);

		$I->amOnPage('/e107_plugins/download/request.php?'.$id);
		$I->seeResponseCodeIs(200);
		$I->seeInSource($this->canaryBody);
	}

	public function stillServesALegitimateDownloadByName(AcceptanceTester $I)
	{
		$I->wantTo('keep serving a normal download by file name (legacy pretty link)');

		$this->seedDownload($I);

		$I->amOnPage('/e107_plugins/download/request.php?'.basename($this->canaryPath));
		$I->seeResponseCodeIs(200);
		$I->seeInSource($this->canaryBody);
	}

	public function stillServesPluginMediaThroughRequestPhp(AcceptanceTester $I)
	{
		$I->wantTo('keep serving a registered {e_PLUGIN} media row (BC for importIcons rows)');

		// Every plugin install writes rows of this shape via importIcons(), and
		// they are public. They only stay reachable because request.php widens
		// the permitted roots to e_ROOT for rows that are in the media table.
		$mediaId = $I->haveInDatabase('e107_core_media', array(
			'media_type'        => 'image/png',
			'media_name'        => 'ghsa87hm downloads_32',
			'media_caption'     => '',
			'media_description' => '',
			'media_category'    => '_common_image',
			'media_datestamp'   => time(),
			'media_author'      => 1,
			'media_url'         => '{e_PLUGIN}download/images/downloads_32.png',
			'media_size'        => 0,
			'media_dimensions'  => '',
			'media_userclass'   => '0',
			'media_usedby'      => '',
			'media_tags'        => '',
		));

		$I->amOnPage('/request.php?file='.$mediaId);
		$I->seeResponseCodeIs(200);
	}

	/**
	 * Create a download row backed by a real file inside a permitted root.
	 *
	 * e107_files/public/ is deliberate: its absence is what made the original
	 * sandbox collapse, so serving from it proves the root is honoured rather
	 * than merely tolerated.
	 *
	 * @param AcceptanceTester $I
	 * @return int download id
	 */
	private function seedDownload(AcceptanceTester $I)
	{
		// A fresh install has core tables only; the download plugin is bundled
		// but not installed, so bring its schema in before seeding a row.
		$I->havePluginTables('download');

		$I->writeAppFile($this->canaryPath, $this->canaryBody);

		$categoryId = $I->haveInDatabase('e107_download_category', array(
			'download_category_name'        => 'GHSA87HM',
			'download_category_description' => '',
			'download_category_icon'        => '',
			'download_category_parent'      => 0,
			'download_category_class'       => 0,
			'download_category_order'       => 0,
			'download_category_sef'         => 'ghsa87hm',
		));

		return $I->haveInDatabase('e107_download', array(
			'download_name'        => 'GHSA87HM canary '.uniqid('', false),
			'download_url'         => $this->canaryUrl,
			'download_sef'         => '',
			'download_author'      => '',
			'download_description' => '',
			'download_keywords'    => '',
			'download_filesize'    => strlen($this->canaryBody),
			'download_requested'   => 0,
			'download_category'    => $categoryId,
			'download_active'      => 1,
			'download_datestamp'   => time(),
			'download_thumb'       => '',
			'download_image'       => '',
			'download_comment'     => 0,
			'download_class'       => 0,
			'download_mirror'      => '',
			'download_mirror_type' => 0,
			'download_visible'     => 0,
		));
	}
}
