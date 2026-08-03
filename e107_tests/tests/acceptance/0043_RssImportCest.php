<?php

/**
 * The RSS importer writes remote bytes under a remote-controlled extension
 * into the web root.
 *
 * rss_import declares $this->saveImages as a property at init() and as a method
 * further down the same class. PHP keeps the two in different namespaces, so the
 * property is never read by anything and the method is what every call reaches.
 * The "Save Images Locally" checkbox the administrator is offered therefore
 * turns nothing off: every import downloads every <img> the feed carries.
 *
 * What is downloaded is decided by the feed. The local name is basename() of the
 * remote URL, extension and all, and the bytes are whatever the remote host
 * chose to send. The file lands under e_MEDIA, which is inside the document root
 * and which e107 ships no rule for, so a feed that names an image
 * http://example.com/x.php and answers with PHP source gets that source executed
 * by the site's own web server on the next request.
 *
 * Four things have to be true for the fix to hold, and each has a test here:
 * the preference is honoured, the extension comes from a verified getimagesize()
 * rather than from the URL, a redirect cannot take the download to an address
 * the policy was never asked about, and e_MEDIA does not execute what it holds.
 *
 * The rule for e_MEDIA is not the rule package P15 wrote for the PM attachment
 * directories. That one denies everything; this one must not, because avatars,
 * site images and every {e_MEDIA_IMAGE} URL a theme emits are fetched straight
 * off the web server. theMediaTreeStillServesAnImage is the control that says so.
 *
 * Nothing here reaches the network. The fixture "feed images" are served by the
 * container's own Apache out of a directory this test writes, and the probe
 * defines e_REMOTE_FILE_ALLOW_PRIVATE so e_file::isUrlSafe() (package P3) does
 * not refuse the container's own address before the importer is reached.
 * aGenuineImageStillImports is what says the fetch really happens, and without
 * it every refusal below would be satisfied by an importer that fetches nothing.
 *
 * Every path is asked of the application through the probe. The acceptance suite
 * installs twice and the second install puts the site under the literal path
 * 000000test, so a site path computed from the database name and prefix names a
 * directory the application never reads.
 */
class RssImportCest
{
	const PROBE_FILE = 'e107_tests_rss_import_probe.php';

	/** Where the fixture "remote host" serves its images from. */
	const FIXTURE_DIR = 'e107_tests_rss_fixtures';

	/** Printed by the payload when a web server runs it rather than serves it. */
	const PAYLOAD = 'E107-RSS-IMPORT-EXECUTED';

	/**
	 * Printed by the polyglot when a web server runs it. Written in two halves
	 * wherever it appears, so the whole string exists only if something executed
	 * it and never merely because the stored bytes were handed back.
	 */
	const POLYGLOT_PAYLOAD = 'E107-RSS-POLYGLOT-EXECUTED';

	/**
	 * First line of the rule e_file writes into the media tree. Spelled out here
	 * rather than read off the class, so the probe still answers on a tree whose
	 * rule is somebody else's or an older e107's.
	 */
	const RULE_MARKER = '# e107 script execution rule';

	/** Feed names, one per test, so each gets a directory of its own. */
	const FEEDS = 'off,script,shell,png,photo,news,newson,poly';

	/** 2x2 PNG, GIF and JPEG, so getimagesize() has something real to report. */
	const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAIAAAD91JpzAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAC0lEQVQImWNgQAYAAA4AAbGa6gYAAAAASUVORK5CYII=';

	const GIF = 'R0lGODdhAgACAIAAAAQCBAAAACwAAAAAAgACAAACAoRRADs=';

	const JPG = '/9j/4AAQSkZJRgABAQEAYABgAAD//gA7Q1JFQVRPUjogZ2QtanBlZyB2MS4wICh1c2luZyBJSkcgSlBFRyB2NjIpLCBxdWFsaXR5ID0gNjAK/9sAQwANCQoLCggNCwoLDg4NDxMgFRMSEhMnHB4XIC4pMTAuKS0sMzpKPjM2RjcsLUBXQUZMTlJTUjI+WmFaUGBKUVJP/9sAQwEODg4TERMmFRUmTzUtNU9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09P/8AAEQgAAgACAwEiAAIRAQMRAf/EAB8AAAEFAQEBAQEBAAAAAAAAAAABAgMEBQYHCAkKC//EALUQAAIBAwMCBAMFBQQEAAABfQECAwAEEQUSITFBBhNRYQcicRQygZGhCCNCscEVUtHwJDNicoIJChYXGBkaJSYnKCkqNDU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6g4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2drh4uPk5ebn6Onq8fLz9PX29/j5+v/EAB8BAAMBAQEBAQEBAQEAAAAAAAABAgMEBQYHCAkKC//EALURAAIBAgQEAwQHBQQEAAECdwABAgMRBAUhMQYSQVEHYXETIjKBCBRCkaGxwQkjM1LwFWJy0QoWJDThJfEXGBkaJicoKSo1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoKDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uLj5OXm5+jp6vLz9PX29/j5+v/aAAwDAQACEQMRAD8A8wooooA//9k=';

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());

		$I->writeAppFile(self::FIXTURE_DIR.'/shell.php', $this->shellFixture());
		$I->writeAppFile(self::FIXTURE_DIR.'/polyglot.php', $this->polyglotFixture());
		$I->writeAppFile(self::FIXTURE_DIR.'/redirect.php', $this->redirectFixture());
		$I->writeAppFile(self::FIXTURE_DIR.'/script.jpg', $this->payloadSource());
		$I->writeAppFile(self::FIXTURE_DIR.'/png.jpg', base64_decode(self::PNG));
		$I->writeAppFile(self::FIXTURE_DIR.'/photo.jpg', base64_decode(self::JPG));
		$I->writeAppFile(self::FIXTURE_DIR.'/blocked.jpg', base64_decode(self::JPG));

		$this->probe($I, 'act=reset');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();

		$this->probe($I, 'act=reset');

		$I->deleteAppFile(self::FIXTURE_DIR.'/shell.php');
		$I->deleteAppFile(self::FIXTURE_DIR.'/polyglot.php');
		$I->deleteAppFile(self::FIXTURE_DIR.'/redirect.php');
		$I->deleteAppFile(self::FIXTURE_DIR.'/script.jpg');
		$I->deleteAppFile(self::FIXTURE_DIR.'/png.jpg');
		$I->deleteAppFile(self::FIXTURE_DIR.'/photo.jpg');
		$I->deleteAppFile(self::FIXTURE_DIR.'/blocked.jpg');
		$I->deleteAppFile(self::PROBE_FILE);
	}

	/**
	 * The positive control the whole file rests on.
	 *
	 * A blanket refusal would satisfy every other assertion here. This says the
	 * feature still does what an administrator ticked the box for: a real image
	 * is fetched, stored under the extension it really is, offered to the media
	 * manager, and the body comes back pointing at the local copy.
	 */
	public function aGenuineImageStillImports(AcceptanceTester $I)
	{
		$run = $this->probe($I, 'act=import&feed=photo&save=1&fixture=photo.jpg');
		$stored = $this->grab('/FILES=(\S*)/', $run);

		$I->assertMatchesRegularExpression('/^photo_[0-9a-f]{10}\.jpg$/', $stored,
			'A real image must still be imported, and be the only file stored');

		$body = $this->grab('/BODY=(.*)/', $run);

		$I->assertStringNotContainsString($this->grab('/SRC=(\S+)/', $run), $body,
			'The remote address must be gone from the body');
		$I->assertStringContainsString($stored, $body,
			'The body must be rewritten to point at the local copy');

		$I->assertSame('1', $this->grab('/MEDIA_ROWS=(\d+)/', $run),
			'The imported image must reach the media manager');
	}

	/**
	 * The other half of the control: what was stored is still served.
	 *
	 * e_MEDIA is public by design. A rule that stopped Apache handing out the
	 * imported file would take every avatar, every site image and every
	 * {e_MEDIA_IMAGE} URL in every theme with it.
	 */
	public function theImportedImageIsStillServedByTheWebServer(AcceptanceTester $I)
	{
		$run = $this->probe($I, 'act=import&feed=photo&save=1&fixture=photo.jpg');

		$url = $this->grab('/MEDIA_URL=(\S+)/', $run).$this->grab('/RELPATH=(\S+)/', $run)
			.'/'.$this->grab('/FILES=(\S*)/', $run);

		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->amOnPage($url);

		$I->seeResponseCodeIs(200);
		$I->assertSame("\xFF\xD8\xFF", substr($I->grabPageSource(), 0, 3),
			'The stored image must come back off the web server unchanged');
	}

	/**
	 * The advertised opt-out. It is a property whose name is also the name of a
	 * method, so nothing ever reads it and the importer downloads regardless.
	 *
	 * Read off the disk, not off the returned body: the fetch of a URL e107
	 * refuses leaves the body alone too, so a body assertion here would pass on
	 * vulnerable code.
	 */
	public function theOptOutStopsTheImporterWritingAnything(AcceptanceTester $I)
	{
		$run = $this->probe($I, 'act=import&feed=off&save=0&fixture=photo.jpg');

		$I->assertSame('?', $this->grab('/FILES=(\S*)/', $run),
			'With image saving off, no file may be written');
		$I->assertSame('0', $this->grab('/DIR_EXISTS=(\d)/', $run),
			'With image saving off, not even the directory may be created');
	}

	/**
	 * The same opt-out through the entry point the importer really uses.
	 * copyNewsData() is what getNext() calls for every item in the feed, and it
	 * is where news_thumbnail is filled in from whatever was downloaded.
	 */
	public function theNewsImportRouteHonoursTheOptOut(AcceptanceTester $I)
	{
		$run = $this->probe($I, 'act=news&feed=news&save=0&fixture=photo.jpg');

		$I->assertSame('?', $this->grab('/FILES=(\S*)/', $run),
			'copyNewsData() must not download when image saving is off');
		$I->assertSame('', $this->grab('/THUMB=(\S*)/', $run),
			'Nothing was downloaded, so nothing may be named as the thumbnail');
	}

	/**
	 * The positive control for that route.
	 *
	 * copyNewsData() is the one line joining getNext() to saveImages(), and
	 * news_thumbnail is filled from what saveImages() found. Without this, the
	 * refusal above cannot tell "the route honours the preference" apart from
	 * "the route stopped importing anything at all".
	 */
	public function theNewsImportRouteStillImportsWhenAskedTo(AcceptanceTester $I)
	{
		$run = $this->probe($I, 'act=news&feed=newson&save=1&fixture=photo.jpg');
		$stored = $this->grab('/FILES=(\S*)/', $run);

		$I->assertMatchesRegularExpression('/^photo_[0-9a-f]{10}\.jpg$/', $stored,
			'With image saving on, copyNewsData() must still download what the feed carries');
		$I->assertStringContainsString($stored, $this->grab('/THUMB=(\S*)/', $run),
			'news_thumbnail must name the image that was stored');
	}

	/**
	 * A feed decides the extension today. The remote host answers a URL ending
	 * .jpg with something that is not an image at all, and the bytes are stored
	 * because the only test applied is that the file is not empty.
	 */
	public function bytesThatAreNotAnImageAreNotWritten(AcceptanceTester $I)
	{
		$run = $this->probe($I, 'act=import&feed=script&save=1&fixture=script.jpg&names=script.jpg');

		$I->assertSame('0', $this->grab('/FILE:script\.jpg=(\d)/', $run),
			'A payload that is not an image must not be written whatever the URL claims');
		$I->assertSame('', $this->grab('/FILES=(\S*)/', $run),
			'Nothing at all may be left in the directory');
	}

	/**
	 * The defect end to end. The feed names an image http://.../shell.php, the
	 * remote host answers with PHP source, and the importer writes it under the
	 * document root with the name the feed chose.
	 */
	public function aPayloadNamedPhpIsNeitherStoredNorExecuted(AcceptanceTester $I)
	{
		$run = $this->probe($I, 'act=import&feed=shell&save=1&fixture=shell.php&names=shell.php');

		$url = $this->grab('/MEDIA_URL=(\S+)/', $run).$this->grab('/RELPATH=(\S+)/', $run).'/shell.php';

		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->amOnPage($url);

		$I->dontSeeInSource(self::PAYLOAD);
		$I->seeResponseCodeIs(403);

		$I->assertSame('0', $this->grab('/FILE:shell\.php=(\d)/', $run),
			'A PHP payload from a feed must not be written into the media tree');
	}

	/**
	 * The classic way past a getimagesize() check, and the reason the rename is
	 * the load-bearing half of this package rather than the refusal.
	 *
	 * A file whose first bytes are GIF89a and whose remainder is PHP source is
	 * an image as far as getimagesize() is concerned, so it is stored. What
	 * keeps it away from an interpreter is that it is stored under the extension
	 * its bytes call for and not the .php its URL asked for.
	 */
	public function aPolyglotIsStoredUnderTheExtensionItsBytesCallFor(AcceptanceTester $I)
	{
		$run = $this->probe($I, 'act=import&feed=poly&save=1&fixture=polyglot.php&names=polyglot.php');
		$stored = $this->grab('/FILES=(\S*)/', $run);

		$I->assertSame('0', $this->grab('/FILE:polyglot\.php=(\d)/', $run),
			'The extension the URL asked for must not be used, however real the bytes are');
		$I->assertMatchesRegularExpression('/^polyglot_[0-9a-f]{10}\.gif$/', $stored,
			'The extension getimagesize() reports must be used');

		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->amOnPage($this->grab('/MEDIA_URL=(\S+)/', $run)
			.$this->grab('/RELPATH=(\S+)/', $run).'/'.$stored);

		$I->seeResponseCodeIs(200);
		$I->assertSame('GIF8', substr($I->grabPageSource(), 0, 4));
		$I->dontSeeInSource(self::POLYGLOT_PAYLOAD);
	}

	/**
	 * The extension is the remote host's choice today and must become the
	 * verifier's. The bytes are a PNG and the URL says .jpg; what is stored has
	 * to be named for what it is.
	 */
	public function theExtensionComesFromTheBytesAndNotFromTheUrl(AcceptanceTester $I)
	{
		$run = $this->probe($I, 'act=import&feed=png&save=1&fixture=png.jpg&names=png.jpg');

		$I->assertSame('0', $this->grab('/FILE:png\.jpg=(\d)/', $run),
			'The extension the URL asked for must not be used');
		$I->assertMatchesRegularExpression('/^png_[0-9a-f]{10}\.png$/',
			$this->grab('/FILES=(\S*)/', $run),
			'The extension getimagesize() reports must be used');
	}

	/**
	 * The control for the guard below. A redirect to an address the site is
	 * willing to reach is still followed, so the refusal cannot be satisfied by
	 * an e107 that stopped following redirects at all.
	 */
	public function aRedirectToAPermittedAddressIsStillFollowed(AcceptanceTester $I)
	{
		$run = $this->probe($I, 'act=redirect&fixture=photo.jpg');

		$I->assertSame('1', $this->grab('/FETCHED=(\d)/', $run),
			'A redirect to an address the policy permits must still be followed');
		$I->assertSame('1', $this->grab('/ISJPEG=(\d)/', $run),
			'The bytes the redirect led to must be what was written');
	}

	/**
	 * e_file::isUrlSafe() is asked about the address a feed named and nothing
	 * else, so a permitted host answering 302 with a refused one takes the
	 * download somewhere the policy never saw.
	 */
	public function aRedirectToARefusedAddressIsNotFollowed(AcceptanceTester $I)
	{
		$run = $this->probe($I, 'act=redirect&fixture=blocked.jpg');

		$I->assertSame('0', $this->grab('/FETCHED=(\d)/', $run),
			'A redirect to an address the policy refuses must not be followed');
		$I->assertSame('0', $this->grab('/BYTES=(\d+)/', $run),
			'Nothing fetched over a refused hop may be left on disk');
	}

	/**
	 * The last line, for a file that gets under the verifier by some route this
	 * package did not think of. e107 ships no rule for e107_media at all today.
	 */
	public function theMediaTreeDoesNotExecuteWhatItHolds(AcceptanceTester $I)
	{
		$run = $this->probe($I, 'act=canary');

		$I->assertSame('1', $this->grab('/CANARY_PHP=(\d)/', $run),
			'The canary is not on disk, so refusing it proves nothing');
		$I->assertSame('1', $this->grab('/MEDIA_HT=(\d)/', $run),
			'The media tree carries no rule at all');

		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->amOnPage($this->grab('/MEDIA_URL=(\S+)/', $run).'e107_tests_rss_canary.php');

		$I->dontSeeInSource(self::PAYLOAD);
		$I->seeResponseCodeIs(403);
	}

	/**
	 * The same, on a site whose media tree already holds somebody else's
	 * .htaccess. A hosting panel, a caching plugin or an old hardening guide
	 * leaving one there is ordinary, and skipping those sites would mean the
	 * tree every other writer now leans on is never covered at all.
	 */
	public function aForeignRuleInTheMediaTreeIsAddedToRatherThanSkipped(AcceptanceTester $I)
	{
		$this->probe($I, 'act=foreign');
		$run = $this->probe($I, 'act=canary');

		$I->assertSame('1', $this->grab('/CANARY_PHP=(\d)/', $run),
			'The canary is not on disk, so refusing it proves nothing');
		$I->assertSame('1', $this->grab('/MEDIA_HT_FOREIGN=(\d)/', $run),
			'What the directory already held must survive');

		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->amOnPage($this->grab('/MEDIA_URL=(\S+)/', $run).'e107_tests_rss_canary.php');

		$I->dontSeeInSource(self::PAYLOAD);
		$I->seeResponseCodeIs(403);

		$I->assertSame('1', $this->grab('/MEDIA_HT_RULE=(\d)/', $run),
			'The rule must be added to one that is already there');
	}

	/**
	 * The regression that rule must not cause. e107_media is served to the
	 * public: a deny-everything rule here breaks every e107 site.
	 */
	public function theMediaTreeStillServesAnImage(AcceptanceTester $I)
	{
		$run = $this->probe($I, 'act=canary');

		$I->assertSame('1', $this->grab('/CANARY_GIF=(\d)/', $run),
			'The canary is not on disk, so serving it proves nothing');

		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->amOnPage($this->grab('/MEDIA_URL=(\S+)/', $run).'e107_tests_rss_canary.gif');

		$I->seeResponseCodeIs(200);
		$I->assertSame('GIF8', substr($I->grabPageSource(), 0, 4),
			'An image under e107_media must still be served byte for byte');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $query
	 * @return string probe output
	 */
	private function probe(AcceptanceTester $I, $query)
	{
		$I->startFollowingRedirects();
		$I->amOnPage('/'.self::PROBE_FILE.'?'.$query);
		$body = $I->grabPageSource();

		if (strpos($body, 'PROBE_OK') === false)
		{
			throw new \RuntimeException('RSS import probe failed for "'.$query.'": '.trim(strip_tags($body)));
		}

		return $body;
	}

	/**
	 * @param string $pattern
	 * @param string $body
	 * @return string
	 */
	private function grab($pattern, $body)
	{
		$matches = array();

		if (!preg_match($pattern, $body, $matches))
		{
			throw new \RuntimeException('RSS import probe reported no '.$pattern.': '.trim(strip_tags($body)));
		}

		return rtrim($matches[1], "\r");
	}

	/**
	 * The payload, as bytes. Stored as a fixture image so a remote host can
	 * answer a URL ending .jpg with it, and echoed by the .php fixture so a
	 * remote host can answer a URL ending .php with it.
	 *
	 * @return string
	 */
	private function payloadSource()
	{
		return '<'.'?php echo "'.self::PAYLOAD.'"; ?'.'>';
	}

	/**
	 * The same idea for the polyglot, written so the marker exists as two halves
	 * until something runs it.
	 *
	 * @return string
	 */
	private function polyglotPayloadSource()
	{
		$half = (int) (strlen(self::POLYGLOT_PAYLOAD) / 2);

		return '<'.'?php echo "'.substr(self::POLYGLOT_PAYLOAD, 0, $half).'"'
			.'."'.substr(self::POLYGLOT_PAYLOAD, $half).'"; ?'.'>';
	}

	/**
	 * A remote host serving PHP source at a URL ending .php. Apache runs this
	 * one, which is the point: what reaches the importer is the output.
	 *
	 * @return string
	 */
	private function shellFixture()
	{
		return "<?php\n"
			."// Fixture for RssImportCest: a remote host answering with a payload.\n"
			."header('Content-Type: text/plain');\n"
			."echo '".$this->payloadSource()."';\n";
	}

	/**
	 * A remote host serving, at a URL ending .php, bytes that getimagesize()
	 * reads as a GIF and an interpreter reads as PHP.
	 *
	 * @return string
	 */
	private function polyglotFixture()
	{
		return "<?php\n"
			."// Fixture for RssImportCest: an image that is also PHP source.\n"
			."header('Content-Type: text/plain');\n"
			."echo base64_decode('".self::GIF."');\n"
			."echo '".$this->polyglotPayloadSource()."';\n";
	}

	/**
	 * A remote host answering 302 with the address of a sibling fixture. The
	 * target is a bare file name resolved against this fixture's own directory,
	 * so the fixture cannot be pointed anywhere else.
	 *
	 * @return string
	 */
	private function redirectFixture()
	{
		return "<?php\n"
			."// Fixture for RssImportCest: a remote host handing the download on.\n"
			."\$name = isset(\$_GET['to']) ? preg_replace('/[^\\w.]/', '', \$_GET['to']) : '';\n"
			."\$base = 'http://'.\$_SERVER['HTTP_HOST'].rtrim(dirname(\$_SERVER['SCRIPT_NAME']), '/').'/';\n"
			."header('Location: '.\$base.\$name, true, 302);\n";
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		$php = <<<'PHP'
<?php
// Fixture for RssImportCest. Written per test, removed in _after().
//
// The fixture host is the container serving this request, so its address is
// private and e_file::isUrlSafe() would refuse it before the importer was
// reached. The constant is e107's own documented opt-out for intranet use.
define('e_REMOTE_FILE_ALLOW_PRIVATE', true);
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');
require_once(e_PLUGIN.'import/providers/rss_import_class.php');

/**
 * A download policy that refuses one named address and permits everything else.
 *
 * e_REMOTE_FILE_ALLOW_PRIVATE above makes the real isUrlSafe() say yes to
 * everything, which is what lets the importer reach this container at all. The
 * redirect guard has to be watched refusing something, so this stands in for a
 * policy that declines the address a redirect leads to.
 */
class e107_test_file extends e_file
{
	public function isUrlSafe($url)
	{
		return substr((string) parse_url($url, PHP_URL_PATH), -12) !== '/blocked.jpg';
	}
}

/**
 * The path a browser would ask for, given a path the application works with.
 */
function e107_test_url($path)
{
	$path = str_replace('\\', '/', $path);
	$root = str_replace('\\', '/', e_ROOT);

	if($root !== '' && strpos($path, $root) === 0)
	{
		$path = substr($path, strlen($root));
	}

	return ltrim($path, './');
}

function e107_test_rmtree($dir)
{
	if(!is_dir($dir))
	{
		return;
	}

	foreach(scandir($dir) as $entry)
	{
		if($entry === '.' || $entry === '..')
		{
			continue;
		}

		$path = $dir.'/'.$entry;

		if(is_dir($path))
		{
			e107_test_rmtree($path);
		}
		else
		{
			@unlink($path);
		}
	}

	@rmdir($dir);
}

/** The feed URL a test's name stands for, and the directory it imports into. */
function e107_test_feed($name)
{
	return 'http://feed.example.net/'.$name.'.xml';
}

function e107_test_hash($name)
{
	return substr(md5(e107_test_feed($name)), 0, 10);
}

function e107_test_relpath($name)
{
	return 'images/'.e107_test_hash($name);
}

function e107_test_dir($name)
{
	return e_MEDIA.e107_test_relpath($name);
}

function e107_test_is_jpeg($path)
{
	return is_file($path) && substr(file_get_contents($path), 0, 3) === "\xFF\xD8\xFF";
}

$act = isset($_GET['act']) ? $_GET['act'] : '';
$feed = isset($_GET['feed']) ? preg_replace('/[^a-z]/', '', $_GET['feed']) : '';
$fixture = isset($_GET['fixture']) ? preg_replace('/[^\w.]/', '', $_GET['fixture']) : '';

$host = getenv('E107_INTERNAL_URL');
$host = rtrim(($host === false || $host === '') ? 'http://web/' : $host, '/');

echo 'MEDIA_DIR='.e_MEDIA."\n";
echo 'MEDIA_URL=/'.e107_test_url(e_MEDIA)."\n";

switch($act)
{
	case 'reset':
		// e107 bans an address after fifty requests in a window and every
		// request from the container arrives from the same bridge address.
		e107::getDb()->delete('online');
		e107::getDb()->delete('banlist', 'banlist_bantype IN (2, -2)');

		// install.php creates every directory in e107_dirs, e_MEDIA_IMAGE
		// among them. The suite's second install writes a config naming a site
		// path it never installed under, so put the tree in the shape a real
		// install leaves it in before anything is asked of the importer.
		if(!is_dir(e_MEDIA.'images'))
		{
			@mkdir(e_MEDIA.'images', 0755, true);
		}

		foreach(explode(',', '{{FEEDS}}') as $name)
		{
			e107_test_rmtree(e107_test_dir($name));
			e107::getDb()->delete('core_media', "media_url LIKE '%".e107_test_hash($name)."%'");
		}

		@unlink(e_MEDIA.'e107_tests_rss_canary.php');
		@unlink(e_MEDIA.'e107_tests_rss_canary.gif');

		// Written by this request's own bootstrap. Dropped again so the next
		// request is the one that writes it, over whatever a test put there.
		@unlink(e_MEDIA_BASE.'.htaccess');

		echo "PROBE_OK reset\n";
		break;

	case 'foreign':
		// The shape a hosting panel or a caching plugin leaves behind.
		file_put_contents(e_MEDIA_BASE.'.htaccess', "# not written by e107\nOptions -Indexes\n");

		echo "PROBE_OK foreign\n";
		break;

	case 'canary':
		// Planted here rather than by the runner: the media directories are
		// created by this container and the runner cannot write into them.
		file_put_contents(e_MEDIA.'e107_tests_rss_canary.php', '{{PAYLOAD_SOURCE}}');
		file_put_contents(e_MEDIA.'e107_tests_rss_canary.gif', base64_decode('{{GIF}}'));

		$rule = is_file(e_MEDIA_BASE.'.htaccess') ? file_get_contents(e_MEDIA_BASE.'.htaccess') : '';

		echo 'CANARY_PHP='.(is_file(e_MEDIA.'e107_tests_rss_canary.php') ? 1 : 0)."\n";
		echo 'CANARY_GIF='.(is_file(e_MEDIA.'e107_tests_rss_canary.gif') ? 1 : 0)."\n";
		echo 'MEDIA_HT='.($rule === '' ? 0 : 1)."\n";
		echo 'MEDIA_HT_FOREIGN='.(strpos($rule, 'Options -Indexes') === false ? 0 : 1)."\n";
		echo 'MEDIA_HT_RULE='.(strpos($rule, '{{RULE_MARKER}}') === false ? 0 : 1)."\n";
		echo "PROBE_OK canary\n";
		break;

	case 'redirect':
		$local = 'e107_tests_rss_redirect.tmp';
		@unlink(e_TEMP.$local);

		$url = $host.'/{{FIXTURE_DIR}}/redirect.php?to='.rawurlencode($fixture);
		$fl = new e107_test_file();
		$fetched = (bool) $fl->getRemoteFile($url, $local, 'temp');

		echo 'SRC='.$url."\n";
		echo 'FETCHED='.($fetched ? 1 : 0)."\n";
		echo 'BYTES='.(is_file(e_TEMP.$local) ? filesize(e_TEMP.$local) : 0)."\n";
		echo 'ISJPEG='.(e107_test_is_jpeg(e_TEMP.$local) ? 1 : 0)."\n";

		@unlink(e_TEMP.$local);
		echo "PROBE_OK redirect\n";
		break;

	case 'import':
	case 'news':
		if($feed === '' || $fixture === '')
		{
			echo "no feed or fixture named\n";
			exit;
		}

		$dir = e107_test_dir($feed);
		e107_test_rmtree($dir);

		$src = $host.'/{{FIXTURE_DIR}}/'.$fixture;
		$body = '<p>before <img src="'.$src.'" alt="fixture" /> after</p>';

		$_POST = array('rss_feed' => e107_test_feed($feed));

		if(!empty($_GET['save']))
		{
			$_POST['rss_saveimages'] = 1;
		}

		$rss = new rss_import();
		$rss->init();

		if($act === 'news')
		{
			$target = array();
			$source = array(
				'title'       => array('a headline'),
				'description' => array($body),
				'pubDate'     => array('Thu, 01 Jan 2026 00:00:00 +0000'),
			);
			$rss->copyNewsData($target, $source);
			$out = $target['news_body'];
			echo 'THUMB='.$target['news_thumbnail']."\n";
		}
		else
		{
			$out = $rss->saveImages($body, 'news');
		}

		echo 'SRC='.$src."\n";
		echo 'RELPATH='.e107_test_relpath($feed)."\n";
		echo 'DIR_EXISTS='.(is_dir($dir) ? 1 : 0)."\n";
		echo 'BODY='.str_replace(array("\r", "\n"), ' ', $out)."\n";

		foreach(explode(',', isset($_GET['names']) ? $_GET['names'] : '') as $name)
		{
			$name = preg_replace('/[^\w.]/', '', $name);

			if($name === '')
			{
				continue;
			}

			echo 'FILE:'.$name.'='.(is_file($dir.'/'.$name) ? 1 : 0)."\n";
		}

		$found = @scandir($dir);
		echo 'FILES='.($found === false ? '?' : implode(',', array_diff($found, array('.', '..'))))."\n";
		echo 'MEDIA_ROWS='.(int) e107::getDb()->count('core_media', '(*)',
			"WHERE media_url LIKE '%".e107_test_hash($feed)."%'")."\n";
		echo "PROBE_OK import\n";
		break;

	default:
		echo "unknown action\n";
}
PHP;

		return strtr($php, array(
			'{{FEEDS}}'          => self::FEEDS,
			'{{RULE_MARKER}}'    => self::RULE_MARKER,
			'{{FIXTURE_DIR}}'    => self::FIXTURE_DIR,
			'{{PAYLOAD_SOURCE}}' => $this->payloadSource(),
			'{{GIF}}'            => self::GIF,
		));
	}
}
