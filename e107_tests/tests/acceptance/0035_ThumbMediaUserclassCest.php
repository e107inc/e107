<?php

/**
 * e_MEDIA has to stay a permitted thumbnail root: avatars and site images live
 * there and every {e_MEDIA_IMAGE} URL in every theme depends on it. But e_MEDIA
 * also holds the media library, and every core_media row carries a
 * media_userclass that e107_handlers/media_class.php gates its own reads by
 * (getImages(), getIcons()) and that request.php gates its own file sends by.
 *
 * The thumbnailer asks nobody. Name a class-restricted media item as the source
 * and it re-encodes the file and hands the bytes to an anonymous caller, so the
 * two endpoints disagree about who may read the same file. Containment cannot
 * settle that disagreement, because the directory the restricted item sits in
 * is the same directory the site logo sits in. Only a permission test on the
 * row can.
 *
 * A row is not confined to e_MEDIA either: every plugin install writes
 * {e_PLUGIN} rows and every theme install writes {e_THEME} rows, and the media
 * manager edits their media_userclass like any other. So the theme case is
 * tested beside the library case.
 *
 * The sentinel is inside the file: each fixture is a 13 pixel wide greyscale
 * PNG whose pixel row spells its own name, and the row survives e_thumbnail's
 * lossless PNG re-encode, so a disclosure is read back out of the image the
 * endpoint returns rather than inferred from a status code.
 *
 * Every refusal is paired with a positive control, because a thumbnailer that
 * refuses everything satisfies a refusal assertion and breaks every e107 site
 * on earth. The controls that matter most are the ones a blanket refusal would
 * fail: a file with no core_media row at all (a theme image, an avatar), a row
 * whose media_userclass is public, and the same restricted row requested by a
 * caller who does hold the class.
 *
 * @see e107_handlers/e_thumbnail_class.php  checkSrc()
 * @see e107_handlers/media_class.php        getImages(), getIcons()
 * @see request.php                          the sibling endpoint that does ask
 */
class ThumbMediaUserclassCest
{
	const PROBE = 'e107_tests_p16_probe.php';

	/** Class the restricted rows are pinned to. e_UC_MEMBER: "members only". */
	const RESTRICTED_CLASS = 253;

	/** A theme every install ships, and whose images directory is writable. */
	const THEME = 'bootstrap5';

	/**
	 * Bytes no anonymous response may contain, and bytes the positive controls
	 * must contain. All the same length, so one decoder reads them all.
	 */
	const SECRET_MEMBER   = 'P16LEAK-MEMBR';
	const SECRET_ICONROW  = 'P16LEAK-ICONF';
	const SECRET_THEME    = 'P16LEAK-THEME';
	const PUBLIC_ROW      = 'P16OKAY-PUBLI';
	const PUBLIC_AVATAR   = 'P16OKAY-AVATA';
	const PUBLIC_NAMESAKE = 'P16OKAY-NAMES';
	const PUBLIC_ICON     = 'P16OKAY-ICONP';
	const PUBLIC_THEME    = 'P16OKAY-THEMP';

	/** A theme image every install ships, and which has no core_media row. */
	const THEME_IMAGE = 'e_THEME/bootstrap5/images/lumen.png';

	/** @var string e_MEDIA as a docroot-relative directory, trailing slash */
	private $media;

	/** @var array probe output, keyed by the names the probe prints */
	private $env = array();

	/**
	 * The directories come out of the running site rather than being derived
	 * here. e_MEDIA hangs off a site path that is a hash of the database name
	 * on an interactively installed site and the literal 000000test on one
	 * installed from a written e107_config.php, and the acceptance suite does
	 * both, the second one last. A fixture written to a computed hash lands in
	 * a directory the application never reads, and then every refusal passes
	 * because the file was not there to disclose.
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->stopFollowingRedirects();
		$I->resetAllCookies();

		$I->writeAppFile(self::PROBE, $this->probeSource());

		// One request, three jobs. e107 bans an address after fifty requests in
		// a window and every request here arrives from the same bridge address.
		// The generated-thumbnail cache has to go as well, or an entry an
		// earlier test minted answers a later one without the source being
		// looked at again. And the site's own directory layout comes back in
		// the same response.
		$this->env = $this->probe($I, 'reset');

		$I->assertArrayHasKey('MEDIA', $this->env, 'The probe did not report e_MEDIA.');
		$this->media = rtrim(preg_replace('#^\./#', '', $this->env['MEDIA']), '/').'/';
		$I->assertNotSame('/', $this->media, 'The probe reported an empty e_MEDIA.');

		$I->writeAppFile($this->restrictedImage(), self::sentinelPng(self::SECRET_MEMBER));
		$I->writeAppFile($this->restrictedIcon(), self::sentinelPng(self::SECRET_ICONROW));
		$I->writeAppFile($this->restrictedThemeImage(), self::sentinelPng(self::SECRET_THEME));
		$I->writeAppFile($this->publicImage(), self::sentinelPng(self::PUBLIC_ROW));
		$I->writeAppFile($this->publicIcon(), self::sentinelPng(self::PUBLIC_ICON));
		$I->writeAppFile($this->publicThemeImage(), self::sentinelPng(self::PUBLIC_THEME));
		$I->writeAppFile($this->avatarImage(), self::sentinelPng(self::PUBLIC_AVATAR));
		$I->writeAppFile($this->namesakeImage(), self::sentinelPng(self::PUBLIC_NAMESAKE));

		$this->env = array_merge($this->env, $this->probe($I, 'seed'));

		$I->assertSame('1', self::env($this->env, 'SEEDED'),
			'The core_media rows were not seeded, so every assertion below would '
			.'be about a file with no row and would pass for the wrong reason.');

		// The probe bootstraps class2.php, which starts a session and never
		// reaches the footer that would close it again, so its response leaves
		// a session cookie in the jar. A test that means to arrive as a first
		// time visitor has to be given an empty one.
		$I->resetAllCookies();
	}

	public function _after(AcceptanceTester $I)
	{
		$this->probe($I, 'cleanup');
		$I->deleteAppFile(self::PROBE);
	}

	// ------------------------------------------------------------------
	// Positive controls. These come first: a thumbnailer that cannot serve a
	// legitimate image satisfies every refusal below this line without
	// proving anything.
	// ------------------------------------------------------------------

	public function aThemeImageWithNoMediaRowStillRenders(AcceptanceTester $I)
	{
		$I->wantTo('keep serving a theme image, which has no core_media row at all');

		$I->amOnPage('/thumb.php?src='.self::THEME_IMAGE.'&w=32');

		$I->assertSame(200, $I->grabResponseCode(),
			'A shipped theme image must still be thumbnailed.');

		$body = $I->grabResponseBody();
		$I->assertNotFalse(self::rasterType($body),
			'A theme image must come back as an image. Got: '.self::excerpt($body));

		$info = getimagesizefromstring($body);
		$I->assertSame(32, $info[0], 'The theme image came back at the wrong width.');
	}

	public function anAvatarWithNoMediaRowStillRenders(AcceptanceTester $I)
	{
		$I->wantTo('keep serving an avatar, which has no core_media row either');

		$I->amOnPage('/thumb.php?src=e_AVATAR/'.basename($this->avatarImage()));

		$I->assertSame(200, $I->grabResponseCode(), 'An avatar must still be thumbnailed.');
		$I->assertSame(self::PUBLIC_AVATAR, self::readSentinel($I->grabResponseBody()),
			'The bytes served were not the avatar that was asked for.');
	}

	public function aPublicMediaRowStillRenders(AcceptanceTester $I)
	{
		$I->wantTo('keep serving a media library item whose media_userclass is public');

		$I->amOnPage('/thumb.php?src=e_MEDIA_IMAGE/'.basename($this->publicImage()));

		$I->assertSame(200, $I->grabResponseCode(), 'A public media row must still be served.');
		$I->assertSame(self::PUBLIC_ROW, self::readSentinel($I->grabResponseBody()),
			'The bytes served were not the public media item that was asked for.');
	}

	public function aPublicMediaRowStillRendersUnderTheOtherSpelling(AcceptanceTester $I)
	{
		$I->wantTo('keep serving a public media item spelled through e_MEDIA rather than e_MEDIA_IMAGE');

		$I->amOnPage('/thumb.php?src=e_MEDIA/images/'.basename($this->publicImage()));

		$I->assertSame(200, $I->grabResponseCode(),
			'The e_MEDIA spelling of a public media row must still be served.');
		$I->assertSame(self::PUBLIC_ROW, self::readSentinel($I->grabResponseBody()),
			'The bytes served were not the public media item that was asked for.');
	}

	/**
	 * The icon route needs a control of its own. Without one, the icon refusal
	 * below cannot tell "the row was enforced" from "the fixture never landed,
	 * or MEDIA_ICONS_DIRECTORY is not where this test thinks it is".
	 */
	public function aPublicIconRowStillRenders(AcceptanceTester $I)
	{
		$I->wantTo('keep serving an icon whose media_userclass is public');

		$I->amOnPage('/thumb.php?src=e_MEDIA_ICON/'.basename($this->publicIcon()));

		$I->assertSame(200, $I->grabResponseCode(), 'A public icon row must still be served.');
		$I->assertSame(self::PUBLIC_ICON, self::readSentinel($I->grabResponseBody()),
			'The bytes served were not the public icon that was asked for.');
	}

	public function aPublicThemeRowStillRenders(AcceptanceTester $I)
	{
		$I->wantTo('keep serving a theme image whose core_media row is public');

		$I->amOnPage('/thumb.php?src=e_THEME/'.self::THEME.'/images/'.basename($this->publicThemeImage()));

		$I->assertSame(200, $I->grabResponseCode(), 'A public {e_THEME} row must still be served.');
		$I->assertSame(self::PUBLIC_THEME, self::readSentinel($I->grabResponseBody()),
			'The bytes served were not the public theme image that was asked for.');
	}

	public function aFileSharingItsNameWithARestrictedRowStillRenders(AcceptanceTester $I)
	{
		$I->wantTo('keep serving a file whose basename a restricted row elsewhere also uses');

		$I->amOnPage('/thumb.php?src=e_MEDIA_IMAGE/'.basename($this->namesakeImage()));

		$I->assertSame(200, $I->grabResponseCode(),
			'A file with no row of its own must be served even when a restricted row '
			.'in another folder carries the same basename.');
		$I->assertSame(self::PUBLIC_NAMESAKE, self::readSentinel($I->grabResponseBody()),
			'The bytes served were not the unregistered namesake that was asked for.');
	}

	/**
	 * The branch of the identity lookup that answers without opening a session
	 * at all. Every other request in this file arrives with a cookie of some
	 * kind, so without this one nothing proves that a first time visitor, who
	 * has none, is answered rather than fataled at.
	 */
	public function aPublicMediaRowIsServedToACallerCarryingNoCookies(AcceptanceTester $I)
	{
		$I->wantTo('keep serving a public media item to a caller with an empty cookie jar');

		$session = self::env($this->env, 'SESSION');
		$I->assertNotSame('', $session, 'The probe did not report the session name.');
		$I->dontSeeCookie($session);

		$I->amOnPage('/thumb.php?src=e_MEDIA_IMAGE/'.basename($this->publicImage()));

		$I->assertSame(200, $I->grabResponseCode(),
			'A public media row must be served to a caller that presented no cookie.');
		$I->assertSame(self::PUBLIC_ROW, self::readSentinel($I->grabResponseBody()),
			'The bytes served were not the public media item that was asked for.');
	}

	public function theHolderOfTheClassIsStillServed(AcceptanceTester $I)
	{
		$I->wantTo('keep serving a restricted media item to a caller who holds the class');

		$I->startFollowingRedirects();
		$I->loginAsAdmin();
		$I->stopFollowingRedirects();

		$I->amOnPage('/thumb.php?src=e_MEDIA_IMAGE/'.basename($this->restrictedImage()));

		$I->assertSame(200, $I->grabResponseCode(),
			'A caller who holds media_userclass '.self::RESTRICTED_CLASS.' must still be served.');
		$I->assertSame(self::SECRET_MEMBER, self::readSentinel($I->grabResponseBody()),
			'The authorised caller was not given the media item.');
	}

	/**
	 * The headers of an ordinary thumbnail, which is the highest volume request
	 * on an e107 site. Answering a public row must not cost it a session: PHP's
	 * session cache limiter puts Pragma: no-cache on the response, sendHeaders()
	 * replaces Cache-Control and Expires but never Pragma, and Pragma no-cache
	 * is what stops a shared cache storing an image it was told to keep for a
	 * year.
	 */
	public function aPublicItemStaysCacheableByEveryone(AcceptanceTester $I)
	{
		$I->wantTo('answer a public media row without opening a session for it');

		$session = self::env($this->env, 'SESSION');
		$this->probe($I, 'session');
		$I->seeCookie($session);

		$I->amOnPage('/thumb.php?src=e_MEDIA_IMAGE/'.basename($this->publicImage()));

		$I->assertSame(200, $I->grabResponseCode(), 'A public media row must still be served.');
		$I->assertSame(self::PUBLIC_ROW, self::readSentinel($I->grabResponseBody()),
			'The bytes served were not the public media item that was asked for.');

		$I->assertSame('must-revalidate', trim($I->grabHttpHeader('Cache-Control')),
			'An ordinary thumbnail must keep the cache headers it has always had.');
		$I->assertNotSame('', $I->grabHttpHeader('Expires'),
			'An ordinary thumbnail must keep its Expires header.');
		$I->assertSame('', $I->grabHttpHeader('Pragma'),
			'The thumbnailer opened a PHP session to answer a public media row, and the '
			.'session cache limiter added Pragma: no-cache to the image response.');
		$I->assertSame('', $I->grabHttpHeader('Set-Cookie'),
			'The thumbnailer put a Set-Cookie on an image response.');
	}

	/**
	 * The mirror image. A restricted item that is served is served to one
	 * caller, so the response must not be a shared cache's to keep: the same
	 * URL answers 403 to the next visitor, and .htaccess rewrites it to
	 * something ending in .png, which is what an edge cache stores by default.
	 */
	public function aRestrictedItemIsNotDeclaredSharedCacheable(AcceptanceTester $I)
	{
		$I->wantTo('keep a restricted thumbnail out of every cache it does not belong in');

		$I->startFollowingRedirects();
		$I->loginAsAdmin();
		$I->stopFollowingRedirects();

		$I->amOnPage('/thumb.php?src=e_MEDIA_IMAGE/'.basename($this->restrictedImage()));

		$I->assertSame(200, $I->grabResponseCode(), 'The class holder must be served.');
		$I->assertSame(self::SECRET_MEMBER, self::readSentinel($I->grabResponseBody()),
			'The authorised caller was not given the media item.');

		$cacheControl = $I->grabHttpHeader('Cache-Control');

		$I->assertStringContainsString('private', $cacheControl,
			'A class-restricted thumbnail was declared cacheable by a shared cache. '
			.'Cache-Control was "'.$cacheControl.'".');
		$I->assertStringContainsString('no-store', $cacheControl,
			'A class-restricted thumbnail was declared storable. Cache-Control was "'.$cacheControl.'".');
		$I->assertStringContainsString('Cookie', $I->grabHttpHeader('Vary'),
			'A class-restricted thumbnail did not vary on the credential that decides it.');
	}

	/**
	 * thumb.php reads cookie_name itself, because its own bootstrap is not
	 * class2.php's, and class2.php coerces an empty value to 'e107cookie'
	 * before defining e_COOKIE from it. e107_admin/prefs.php stores an empty
	 * one for anyone who clears the field, so the two now coerce alike.
	 *
	 * Nothing identifies a caller by that constant today, and this case passes
	 * with or without the coercion: e_session::setSessionName() records the
	 * name it computes and never hands it to session_name(), so every e107
	 * session is the engine's default one, and e_user::setSessionData() keys
	 * the login off the pref rather than off e_COOKIE. The case is here to keep
	 * it that way. It is the configuration that breaks first if the naming code
	 * is ever finished, and it is the only one in the file that exercises a
	 * site whose cookie_name is not the shipped value.
	 */
	public function aMemberIsStillIdentifiedWhenTheSiteHasNoCookieName(AcceptanceTester $I)
	{
		$I->wantTo('identify the class holder on a site whose cookie_name pref is empty');

		$this->probe($I, 'blankcookie');

		// Read back on a later request. The value the writing request reports is
		// its own in-memory copy, which says nothing about what the next one sees.
		$blanked = $this->probe($I, 'session');

		$I->assertSame('[]', self::env($blanked, 'COOKIENAME'),
			'The probe could not store an empty cookie_name, so this test is not measuring '
			.'what it claims to measure. A later request read '.self::env($blanked, 'COOKIENAME').'.');

		$I->startFollowingRedirects();
		$I->loginAsAdmin();
		$I->stopFollowingRedirects();

		$I->amOnPage('/thumb.php?src=e_MEDIA_IMAGE/'.basename($this->restrictedImage()));

		$I->assertSame(200, $I->grabResponseCode(),
			'The class holder was refused on a site whose cookie_name pref is empty.');
		$I->assertSame(self::SECRET_MEMBER, self::readSentinel($I->grabResponseBody()),
			'The authorised caller was not given the media item.');
	}

	// ------------------------------------------------------------------
	// The defect.
	// ------------------------------------------------------------------

	public function aRestrictedMediaRowIsNotServedToAGuest(AcceptanceTester $I)
	{
		$I->wantTo('refuse a class-restricted media item to a caller with no session');

		$I->amOnPage('/thumb.php?src=e_MEDIA_IMAGE/'.basename($this->restrictedImage()));

		$this->seeNoDisclosure($I, 'thumb.php src=e_MEDIA_IMAGE', self::SECRET_MEMBER);
	}

	public function aRestrictedMediaRowIsNotServedUnderTheOtherSpelling(AcceptanceTester $I)
	{
		$I->wantTo('refuse a restricted media item spelled through e_MEDIA rather than e_MEDIA_IMAGE');

		$I->amOnPage('/thumb.php?src=e_MEDIA/images/'.basename($this->restrictedImage()));

		$this->seeNoDisclosure($I, 'thumb.php src=e_MEDIA', self::SECRET_MEMBER);
	}

	/**
	 * A caller who has a session but not the class. thumb.php's own bootstrap
	 * opens no session, so this is the only shape that makes it open one, and
	 * the public control at the end of the same test is what says opening it
	 * did not cost the ordinary request its answer.
	 */
	public function aRestrictedMediaRowIsNotServedToASignedOutCallerCarryingCookies(AcceptanceTester $I)
	{
		$I->wantTo('refuse a restricted media item to a caller carrying a session cookie but no login');

		$session = self::env($this->env, 'SESSION');
		$this->probe($I, 'session');
		$I->seeCookie($session);

		$I->amOnPage('/thumb.php?src=e_MEDIA_IMAGE/'.basename($this->restrictedImage()));

		$this->seeNoDisclosure($I, 'thumb.php with a guest session', self::SECRET_MEMBER);

		$I->amOnPage('/thumb.php?src=e_MEDIA_IMAGE/'.basename($this->publicImage()));

		$I->assertSame(200, $I->grabResponseCode(),
			'A public media row must still be served to a caller carrying cookies.');
		$I->assertSame(self::PUBLIC_ROW, self::readSentinel($I->grabResponseBody()),
			'The bytes served were not the public media item that was asked for.');
	}

	public function aRestrictedIconRowIsNotServedToAGuest(AcceptanceTester $I)
	{
		$I->wantTo('refuse a class-restricted icon, which getIcons() also hides');

		$I->amOnPage('/thumb.php?src=e_MEDIA_ICON/'.basename($this->restrictedIcon()));

		$this->seeNoDisclosure($I, 'thumb.php src=e_MEDIA_ICON', self::SECRET_ICONROW);
	}

	/**
	 * media_url is not confined to {e_MEDIA*}. e107_admin/theme.php and
	 * theme_handler.php both run e107::getMedia()->import('_common_image',
	 * e_THEME.$name) on a theme install, plugin_class.php runs importIcons()
	 * over e_PLUGIN on a plugin install, and update_routines.php imports
	 * {e_IMAGE} and {e_FILE} subtrees on an upgrade from 1.x. Every one of
	 * those rows is inline and batch editable in the media manager, and
	 * e_THEME, e_PLUGIN, e_IMAGE and the e_FILE subtrees are all thumbnail
	 * roots, so a gate that only reads rows under e_MEDIA enforces half the
	 * column and gives an operator no way to tell which half.
	 */
	public function aRestrictedThemeRowIsNotServedToAGuest(AcceptanceTester $I)
	{
		$I->wantTo('refuse a class-restricted media row that lives under e_THEME rather than e_MEDIA');

		$I->amOnPage('/thumb.php?src=e_THEME/'.self::THEME.'/images/'.basename($this->restrictedThemeImage()));

		$this->seeNoDisclosure($I, 'thumb.php src=e_THEME', self::SECRET_THEME);
	}

	public function aRestrictedMediaRowIsNotServedThroughTheCloakedRoute(AcceptanceTester $I)
	{
		$I->wantTo('refuse a restricted media item asked for through the base64 id= route');

		$query = 'src=e_MEDIA_IMAGE/'.basename($this->restrictedImage()).'&w=40';

		$I->amOnPage('/thumb.php?id='.rawurlencode(base64_encode($query)));

		$this->seeNoDisclosure($I, 'thumb.php id=', self::SECRET_MEMBER);
	}

	public function aRestrictedMediaRowIsNotServedThroughTheLegacyThumbnailer(AcceptanceTester $I)
	{
		$I->wantTo('refuse a restricted media item asked for through e107_images/thumb.php');

		$path = $this->media.'images/'.basename($this->restrictedImage());

		$I->amOnPage('/e107_images/thumb.php?'.$path.'+40');

		$this->seeNoDisclosure($I, 'e107_images/thumb.php', self::SECRET_MEMBER);
	}

	/**
	 * The cache is part of the sink. thumb.php writes every thumbnail it
	 * generates to e_CACHE_IMAGE and sendCachedImage() serves a hit without
	 * looking at the source again, so a permission test that only guards the
	 * generate path means one authorised request mints an entry every
	 * anonymous caller can then collect.
	 */
	public function theCacheAnAuthorisedRequestMintedIsNotServedToAGuest(AcceptanceTester $I)
	{
		$I->wantTo('refuse the cached copy of a restricted item an authorised request generated');

		$url = '/thumb.php?src=e_MEDIA_IMAGE/'.basename($this->restrictedImage()).'&w=44';

		$I->startFollowingRedirects();
		$I->loginAsAdmin();
		$I->stopFollowingRedirects();

		$I->amOnPage($url);
		$I->assertSame(200, $I->grabResponseCode(),
			'The authorised request has to succeed, or no cache entry is minted and '
			.'the anonymous request below proves nothing.');

		$cache = $this->probe($I, 'cache');

		// Named, not merely non-empty: ordinary page rendering writes its own
		// alt-text cache files into the same directory, and one of those would
		// satisfy a test for "something is in there".
		$I->assertStringContainsString('e107_tests_p16_restricted', self::env($cache, 'CACHE'),
			'The authorised request minted no thumbnail cache entry for the restricted '
			.'source, so the anonymous request below is not testing the cache hit path. '
			.'e_CACHE_IMAGE holds: '.self::env($cache, 'CACHE'));

		$I->resetAllCookies();
		$I->amOnPage($url);

		$this->seeNoDisclosure($I, 'thumb.php cache hit', self::SECRET_MEMBER);
	}

	/**
	 * The declared residual exposure, written down where the next reader will
	 * look. e107_system/ ships an .htaccess that denies everything; e107_media/
	 * ships only an index.html, and neither the root .htaccess nor e107.htaccess
	 * mentions it. So the file this suite has just watched thumb.php refuse is
	 * still fetchable at its own URL by anyone who knows the site path and the
	 * filename, both of which appear in the URL of every public media image on
	 * the site.
	 *
	 * Closing that means moving class-restricted media behind a gated endpoint,
	 * which is larger than this package and touches every stored URL. Until
	 * then this test states the position; it is expected to flip to a refusal,
	 * loudly, the day someone believes the wider fix has landed.
	 */
	public function theRestrictedFileIsStillReachableStatically(AcceptanceTester $I)
	{
		$I->wantTo('record that the web server still serves the restricted file directly');

		$I->amOnPage('/'.$this->restrictedImage());

		$I->assertSame(200, $I->grabResponseCode(),
			'The web server no longer serves e107_media/ directly. If that is deliberate, '
			.'this test and the advisory both need rewriting; if it is not, media is broken.');
		$I->assertSame(self::SECRET_MEMBER, self::readSentinel($I->grabResponseBody()),
			'The static URL did not return the fixture, so this test is not measuring '
			.'what it claims to measure.');
	}

	// ------------------------------------------------------------------
	// Assertions
	// ------------------------------------------------------------------

	/**
	 * The request was refused, the response did not carry the file the request
	 * named, and it did not describe the server either.
	 *
	 * The status assertion is load bearing. checkSrc() answers 200 with an SVG
	 * placeholder for a source that is absent from a permitted directory, and
	 * readSentinel() rejects SVG, so without it "the fixture never landed" and
	 * "the file was refused" are the same result.
	 *
	 * @param AcceptanceTester $I
	 * @param string $label
	 * @param string $secret sentinel encoded in the fixture
	 * @return void
	 */
	private function seeNoDisclosure(AcceptanceTester $I, $label, $secret)
	{
		$body = $I->grabResponseBody();
		$code = $I->grabResponseCode();

		$I->assertSame(403, $code,
			$label.' did not refuse the request; it answered '.$code.'.');

		$I->assertNotSame($secret, self::readSentinel($body),
			$label.' handed the class-restricted media item to a caller who does not hold the class.');

		$I->assertStringNotContainsString($secret, $body,
			$label.' returned the raw bytes of the class-restricted media item.');

		$I->assertStringNotContainsString(self::env($this->env, 'ROOT'), $body,
			$label.' printed the absolute path of the install.');

		foreach(array('Trace:', 'Stack trace', 'Exception:', '.php on line', 'e_thumbnail_class') as $needle)
		{
			$I->assertStringNotContainsString($needle, $body,
				$label.' printed "'.$needle.'" to an unauthenticated caller.');
		}
	}

	// ------------------------------------------------------------------
	// Fixtures
	// ------------------------------------------------------------------

	private function restrictedImage()
	{
		return $this->media.'images/e107_tests_p16_restricted.png';
	}

	private function restrictedIcon()
	{
		return $this->media.'icons/e107_tests_p16_icon.png';
	}

	private function publicImage()
	{
		return $this->media.'images/e107_tests_p16_public.png';
	}

	private function publicIcon()
	{
		return $this->media.'icons/e107_tests_p16_publicicon.png';
	}

	private function avatarImage()
	{
		return $this->media.'avatars/e107_tests_p16_avatar.png';
	}

	/**
	 * A media row outside e_MEDIA, spelled the way a theme install spells one.
	 *
	 * @return string
	 */
	private function restrictedThemeImage()
	{
		return 'e107_themes/'.self::THEME.'/images/e107_tests_p16_theme.png';
	}

	private function publicThemeImage()
	{
		return 'e107_themes/'.self::THEME.'/images/e107_tests_p16_themepublic.png';
	}

	/**
	 * A file with no core_media row of its own, sharing its basename with a
	 * restricted row that lives in another folder. Matching a request to a row
	 * by basename would refuse this, which is the false positive that mirrors
	 * the false negative of matching by the caller's own spelling.
	 *
	 * @return string
	 */
	private function namesakeImage()
	{
		return $this->media.'images/e107_tests_p16_namesake.png';
	}

	/**
	 * A PNG whose pixel row spells $text, greyscale so one channel carries the
	 * byte. A PNG round trip through GD is lossless, so the row survives
	 * e_thumbnail's re-encode and comes back out of whatever the endpoint
	 * hands over.
	 *
	 * @param string $text
	 * @return string PNG bytes
	 */
	private static function sentinelPng($text)
	{
		$width = strlen($text);
		$image = imagecreatetruecolor($width, 4);

		for($x = 0; $x < $width; $x++)
		{
			$value = ord($text[$x]);
			$colour = imagecolorallocate($image, $value, $value, $value);

			for($y = 0; $y < 4; $y++)
			{
				imagesetpixel($image, $x, $y, $colour);
			}
		}

		ob_start();
		imagepng($image);
		$png = ob_get_clean();
		imagedestroy($image);

		return $png;
	}

	/**
	 * The IMAGETYPE_* of $body, or false when it is not a raster image.
	 *
	 * getimagesizefromstring() on its own is not enough: its WBMP branch is a
	 * heuristic over arbitrary bytes rather than a magic number test, and it
	 * reports the thumbnailer's own SVG placeholder as a 1 pixel WBMP.
	 *
	 * @param string $body
	 * @return int|false
	 */
	private static function rasterType($body)
	{
		if($body === '')
		{
			return false;
		}

		$info = @getimagesizefromstring($body);

		if(!is_array($info) || !isset($info[2]))
		{
			return false;
		}

		$raster = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP, IMAGETYPE_WEBP);

		return in_array($info[2], $raster, true) ? $info[2] : false;
	}

	/**
	 * The string sentinelPng() encoded, or null when $body is not an image of
	 * the right shape.
	 *
	 * @param string $body
	 * @return string|null
	 */
	private static function readSentinel($body)
	{
		if(self::rasterType($body) === false)
		{
			return null;
		}

		$image = @imagecreatefromstring($body);

		if($image === false)
		{
			return null;
		}

		$width = imagesx($image);

		if($width !== strlen(self::SECRET_MEMBER)) // every sentinel is this long
		{
			imagedestroy($image);

			return null;
		}

		$truecolor = imageistruecolor($image);
		$text = '';

		for($x = 0; $x < $width; $x++)
		{
			$index = imagecolorat($image, $x, 0);

			if($truecolor)
			{
				$text .= chr(($index >> 16) & 0xFF);
			}
			else
			{
				$colour = imagecolorsforindex($image, $index);
				$text .= chr($colour['red']);
			}
		}

		imagedestroy($image);

		return $text;
	}

	/**
	 * A printable, bounded view of a response, for a failure message.
	 *
	 * @param string $body
	 * @return string
	 */
	private static function excerpt($body)
	{
		$text = preg_replace('/[^\x20-\x7e]+/', '.', substr($body, 0, 200));

		return strlen($body) > 200 ? $text.' [...]' : $text;
	}

	/**
	 * @param array  $env
	 * @param string $key
	 * @return string
	 */
	private static function env(array $env, $key)
	{
		return isset($env[$key]) ? $env[$key] : '';
	}

	/**
	 * Run the probe and read back the KEY=value lines it prints.
	 *
	 * @param AcceptanceTester $I
	 * @param string $act
	 * @return array
	 */
	private function probe(AcceptanceTester $I, $act)
	{
		$I->amOnPage('/'.self::PROBE.'?act='.$act);

		$body = $I->grabResponseBody();

		if(strpos($body, 'P16PROBE_OK') === false)
		{
			throw new \RuntimeException('Media userclass probe failed for "'.$act.'": '.trim(strip_tags($body)));
		}

		$env = array();

		foreach(explode("\n", $body) as $line)
		{
			if(strpos($line, '=') === false)
			{
				continue;
			}

			list($key, $value) = explode('=', trim($line), 2);
			$env[$key] = $value;
		}

		return $env;
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		$class = self::RESTRICTED_CLASS;
		$restricted = basename($this->restrictedImage());
		$icon = basename($this->restrictedIcon());
		$public = basename($this->publicImage());
		$publicIcon = basename($this->publicIcon());
		$namesake = basename($this->namesakeImage());
		$theme = self::THEME.'/images/'.basename($this->restrictedThemeImage());
		$publicTheme = self::THEME.'/images/'.basename($this->publicThemeImage());

		return <<<PHP
<?php
// Fixture for 0040_ThumbMediaUserclassCest. Removed again in the Cest's _after().
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');

\$act = isset(\$_GET['act']) ? \$_GET['act'] : '';

// The rows are keyed the way the media manager writes them: media_url holds
// the path behind whichever {e_XXX} constant createConstants() picks, never a
// filesystem path. The {e_THEME} pair is what e_media::import() writes when a
// theme is installed. @see e_media::importFile(), e_media::import()
\$rows = array(
	'{e_MEDIA_IMAGE}$restricted' => '$class',
	'{e_MEDIA_ICON}$icon'        => '$class',
	'{e_MEDIA_ICON}$namesake'    => '$class',
	'{e_THEME}$theme'            => '$class',
	'{e_MEDIA_IMAGE}$public'     => '0',
	'{e_MEDIA_ICON}$publicIcon'  => '0',
	'{e_THEME}$publicTheme'      => '0',
);

function p16_pref_cookie_name(\$value)
{
	e107::getConfig('core')->set('cookie_name', \$value)->save(false, true, false);

	foreach(glob(e_CACHE_CONTENT.'S_Config_*.cache.php') ?: array() as \$cached)
	{
		@unlink(\$cached);
	}
}

if(\$act === 'reset' || \$act === 'cleanup')
{
	e107::getDb()->delete('online');
	e107::getDb()->delete('banlist', 'banlist_bantype IN (2, -2)');

	p16_pref_cookie_name('e107cookie');

	foreach(glob(e_CACHE_IMAGE.'*') ?: array() as \$file)
	{
		if(is_file(\$file))
		{
			@unlink(\$file);
		}
	}

	foreach(array_keys(\$rows) as \$url)
	{
		e107::getDb()->createQueryBuilder()->delete('core_media')->where('media_url', \$url)->execute();
	}
}

if(\$act === 'blankcookie')
{
	p16_pref_cookie_name('');
}

if(\$act === 'seed')
{
	\$seeded = 0;

	foreach(\$rows as \$url => \$userclass)
	{
		\$insert = array(
			'media_type'        => 'image/png',
			'media_name'        => basename(\$url),
			'media_caption'     => 'p16',
			'media_description' => '',
			'media_category'    => strpos(\$url, '{e_MEDIA_ICON}') === 0 ? '_icon_32' : '_common_image',
			'media_datestamp'   => time(),
			'media_author'      => 1,
			'media_url'         => \$url,
			'media_size'        => 0,
			'media_dimensions'  => '13 x 4',
			'media_userclass'   => \$userclass,
			'media_usedby'      => '',
			'media_tags'        => '',
		);

		if(e107::getDb()->insert('core_media', \$insert))
		{
			\$seeded++;
		}
	}

	echo 'SEEDED='.((\$seeded === count(\$rows)) ? '1' : '0')."\n";
	echo 'SEEDCOUNT='.\$seeded."\n";
}

if(\$act === 'cache')
{
	\$names = array();

	foreach(glob(e_CACHE_IMAGE.'*') ?: array() as \$file)
	{
		\$names[] = basename(\$file);
	}

	echo 'CACHE='.implode(' ', \$names)."\n";
}

echo "P16PROBE_OK\n";
echo 'ROOT='.rtrim(e_ROOT, '/')."\n";
echo 'MEDIA='.e_MEDIA."\n";
echo 'CACHEIMG='.e_CACHE_IMAGE."\n";
echo 'SESSION='.session_name()."\n";
echo 'COOKIENAME=['.e107::getPref('cookie_name')."]\n";
PHP;
	}
}
