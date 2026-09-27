<?php

/** The Site URL box takes a host-less path, and the admin area home warns while the pref carries no host (#6117). */
class AdminSiteUrlCest
{
	const PROBE_FILE = 'e107_test_siteurl_probe.php';
	const PREFS = '/e107_admin/prefs.php';
	const DASHBOARD = '/e107_admin/admin.php';

	/** A distinctive run of {@see ADLAN_SITEURL_NO_HOST}. */
	const WARNING = 'Site URL does not include a web address';

	/** What the boot-time host check serves instead of the page. */
	const REFUSED = 'Site Configuration Issue';

	/** @var string the pref as the site was installed with it */
	private $original = '';

	public function _before(AcceptanceTester $I)
	{
		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
		$I->loginAsAdmin();
		$this->original = $this->siteUrl($I);
	}

	public function _after(AcceptanceTester $I)
	{
		if($this->original !== '')
		{
			$this->setSiteUrl($I, $this->original);
		}
	}

	public function theSiteUrlBoxAdmitsAHostLessPath(AcceptanceTester $I)
	{
		$I->wantTo('have the Site Information tab accept a Site URL of /');

		$I->amOnPage(self::PREFS);
		$pattern = $I->grabAttributeFrom('input[name=siteurl]', 'pattern');

		$I->assertNotEmpty($pattern, 'The Site URL box must still guide what is typed into it.');

		$I->assertSame(1, $this->admits($pattern, '/'),
			'A host-less Site URL is what an install derives for itself, so the box has to take it.');
		$I->assertSame(1, $this->admits($pattern, '/subdir/'),
			'A site in a subdirectory carries its path and no host.');
		$I->assertSame(1, $this->admits($pattern, 'https://example.com/'),
			'A full address has to keep working.');
		$I->assertSame(0, $this->admits($pattern, 'example.com'),
			'Something that is neither an address nor a path is still a mistake.');
		$I->assertSame(0, $this->admits($pattern, '//example.com/'),
			'A protocol-relative address names a host but no scheme, so it would escape the warning and still break links in mail.');
	}

	public function theAdminHomeWarnsWhileTheSiteUrlHasNoHost(AcceptanceTester $I)
	{
		$I->wantTo('be told the Site URL needs a host while it has none');

		$this->setSiteUrl($I, '/');
		$I->amOnPage(self::DASHBOARD);
		$I->dontSee(self::REFUSED);
		$I->see(self::WARNING);

		$I->probe('own=1');
		$I->amOnPage(self::DASHBOARD);
		$I->dontSee(self::REFUSED);
		$I->dontSee(self::WARNING);
	}

	/**
	 * @param string $pattern the rendered pattern attribute
	 * @param string $value   what an administrator typed
	 * @return int 1 when the browser would let the save through
	 */
	private function admits($pattern, $value)
	{
		return preg_match('{^(?:'.$pattern.')$}', $value);
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string the stored siteurl pref
	 */
	private function siteUrl(AcceptanceTester $I)
	{
		$answer = $I->probe();
		$newline = strpos($answer, "\n");

		return ($newline === false) ? '' : trim((string) substr($answer, $newline));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string           $value
	 * @return void
	 */
	private function setSiteUrl(AcceptanceTester $I, $value)
	{
		$I->probe('set='.rawurlencode($value));
	}

	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for AdminSiteUrlCest.
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

$config = e107::getConfig('core');

if(isset($_GET['own']))
{
	$secure = !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off';
	$_GET['set'] = ($secure ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].'/';
}

if(isset($_GET['set']))
{
	$config->set('siteurl', $_GET['set'])->save(false, true, false);
}

echo "PROBE_OK\n".$config->get('siteurl');
PHP;
	}
}
