<?php

/**
 * GHSA-87hm-vh32-7c3r was fixed at the download handler and nowhere else. e107
 * ships two more file-to-browser routes, and both are thumbnailers reachable
 * without a cookie:
 *
 *  - thumb.php at the docroot root, which is what thumbUrl() emits and what
 *    the shipped e107.htaccess rewrites /media/img/... onto. e_thumbnail::
 *    checkSrc() strips the literal ".." out of the requested source, asks
 *    is_file() about the result and reads whatever answers yes. There is no
 *    realpath() and no root list, so a docroot-relative path, an absolute
 *    path and a base64 id= payload carrying {e_SYSTEM} are all honoured.
 *
 *  - e107_images/thumb.php, whose positional grammar (?<path>+<size>+<model>)
 *    hands the source straight to resize_image(). An absolute path and a
 *    scheme:// source are both taken verbatim, and "noscale" on a source
 *    smaller than the requested size is a literal readfile() of it. That last
 *    shape is the arbitrary file read primitive itself: the bytes come back
 *    unaltered, so it reads outside the docroot as happily as inside it.
 *
 * The sentinels are inside the files, not around them. Each fixture is a
 * 13 pixel wide greyscale PNG whose pixel row spells its own name, with the
 * same name repeated as trailing bytes after IEND. The trailing copy survives
 * a readfile() passthrough, so the legacy endpoint's disclosure can be
 * asserted as a literal string; the pixel copy survives e_thumbnail's lossless
 * PNG re-encode, so the modern endpoint's disclosure can be asserted through
 * the image it hands back. A refusal is never inferred from a status code
 * alone.
 *
 * Every refusal here is paired with a positive control, because containment is
 * trivially satisfied by a thumbnailer that serves nothing. The controls cover
 * the media library, the avatar directory, a plugin image, a forum attachment,
 * an image in the v1.x e107_files/public directory, the placeholder a
 * missing-but-legitimate source is supposed to produce, the dimensions a
 * base64 id= request asks for, and the legacy grammar the shim has to keep
 * serving.
 *
 * @see e107_handlers/e_thumbnail_class.php  checkSrc(), parseRequest()
 * @see thumb.php
 * @see e107_images/thumb.php
 * @see e107_handlers/resize_handler.php  resize_image()
 */
class ThumbnailContainmentCest
{
	const PROBE = 'e107_tests_thumb_probe.php';

	/**
	 * Bytes no legitimate response may contain. All the same length, so one
	 * decoder reads them all.
	 */
	const SECRET_SYSTEM  = 'P2LEAK-SYSTEM';
	const SECRET_PM      = 'P2LEAK-PMFILE';
	const SECRET_OUTSIDE = 'P2LEAK-OUTSID';

	/** Bytes the positive controls must contain. */
	const PUBLIC_MEDIA  = 'P2OKAY-MEDIAF';
	const PUBLIC_AVATAR = 'P2OKAY-AVATAR';
	const PUBLIC_LEGACY = 'P2OKAY-LEGACY';
	const PUBLIC_FORUM  = 'P2OKAY-FORUMF';

	/** A plugin image every install ships, used as a realistic resize control. */
	const PLUGIN_IMAGE = 'e_PLUGIN/gallery/images/butterfly.jpg';

	/** A file inside a permitted root that is not an image. */
	const SECRET_INROOT = 'P2LEAK-INROOT';

	/**
	 * @var string e_MEDIA as a docroot-relative directory, trailing slash
	 */
	private $media;

	/**
	 * @var string e_SYSTEM as a docroot-relative directory, trailing slash
	 */
	private $system;

	/**
	 * @var string e_PLUGIN as a docroot-relative directory, trailing slash
	 */
	private $plugins;

	/**
	 * @var string e_FILE as a docroot-relative directory, trailing slash
	 */
	private $files;

	/** @var array probe output, keyed by the names the probe prints */
	private $env = array();

	/**
	 * The directories are read out of the running site rather than derived
	 * here. e_MEDIA and e_SYSTEM hang off a site path that is a hash of the
	 * database name on an interactively installed site and a fixed string on
	 * one installed from a written e107_config.php, and the acceptance suite
	 * does both. Computing it in the test was wrong for the suite as it stands,
	 * and every fixture landed in a directory the application never looks at:
	 * the refusals then passed because the file was not there to disclose.
	 * Hence the probe runs first and the fixtures are written where it says.
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->stopFollowingRedirects();
		$I->resetAllCookies();

		$I->writeAppFile(self::PROBE, $this->probeSource());

		// Three jobs, one request. e107 bans an address after fifty requests in
		// a window and every request here arrives from the same bridge address,
		// so the ban has to go before each test rather than once per suite. The
		// image cache has to go too: the thumbnailer keys a cache file on the
		// request parameters and serves it back without looking at the source
		// again, so an entry a vulnerable run wrote would mask the refusal a
		// fixed run makes. And the site's own directory layout comes back in
		// the same response.
		$this->env = $this->probe($I, 'reset');

		$this->media   = self::appDir($I, $this->env, 'MEDIA');
		$this->system  = self::appDir($I, $this->env, 'SYSTEM');
		$this->plugins = self::appDir($I, $this->env, 'PLUGIN');
		$this->files   = self::appDir($I, $this->env, 'FILE');

		$I->writeAppFile($this->systemSecret(), self::sentinelPng(self::SECRET_SYSTEM));
		$I->writeAppFile($this->outsideSource(), self::sentinelPng(self::SECRET_OUTSIDE));
		$I->writeAppFile($this->pmAttachment(), self::sentinelPng(self::SECRET_PM));
		$I->writeAppFile($this->legacyPmAttachment(), self::sentinelPng(self::SECRET_PM));
		$I->writeAppFile($this->publicImage(), self::sentinelPng(self::PUBLIC_MEDIA));
		$I->writeAppFile($this->avatarImage(), self::sentinelPng(self::PUBLIC_AVATAR));
		$I->writeAppFile($this->legacyImage(), self::sentinelPng(self::PUBLIC_LEGACY));
		$I->writeAppFile($this->forumAttachment(), self::sentinelPng(self::PUBLIC_FORUM));
		$I->writeAppFile($this->brokenImage(), 'P2NOTANIMAGE');
		$I->writeAppFile($this->inRootSecret(), self::SECRET_INROOT);

		// The copy outside the docroot has to be placed by something running
		// inside the container, because the deployer only writes into the app.
		// Moved rather than copied: a file that exists in both places would let
		// a disclosure of the copy inside the docroot pass for a disclosure of
		// the one outside it.
		$this->env = array_merge($this->env, $this->probe($I, 'move'));

		$I->assertSame('1', self::env($this->env, 'OUTSIDE_OK'),
			'The fixture outside the docroot was not placed at '.self::env($this->env, 'OUTSIDE')
			.', so every out-of-root assertion below would pass for the wrong reason.');
		$I->assertSame('1', self::env($this->env, 'INSIDE_GONE'),
			'The copy of the outside fixture inside the docroot was not removed, so a disclosure '
			.'of it would be indistinguishable from a disclosure of the one outside.');
	}

	public function _after(AcceptanceTester $I)
	{
		$this->probe($I, 'cleanup');
		$I->deleteAppFile(self::PROBE);
	}

	/**
	 * A directory the probe reported, as a path relative to the app root.
	 *
	 * @param AcceptanceTester $I
	 * @param array  $env
	 * @param string $key
	 * @return string
	 */
	private static function appDir(AcceptanceTester $I, array $env, $key)
	{
		$I->assertArrayHasKey($key, $env, 'The probe did not report '.$key.'.');

		$dir = preg_replace('#^\./#', '', $env[$key]);

		$I->assertNotSame('', $dir, 'The probe reported an empty '.$key.'.');

		return rtrim($dir, '/').'/';
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

	// ------------------------------------------------------------------
	// Positive controls. These come first: if the thumbnailer cannot serve
	// a legitimate image, every refusal below this line proves nothing.
	// ------------------------------------------------------------------

	public function aMediaLibraryImageStillRenders(AcceptanceTester $I)
	{
		$I->wantTo('keep serving a media library image through thumb.php');

		$I->amOnPage('/thumb.php?src=e_MEDIA_IMAGE/e107_tests_p2_public.png');

		$I->assertSame(200, $I->grabResponseCode(), 'A media library image must still be served.');
		$I->assertSame('image/png', $I->grabHttpHeader('Content-Type'),
			'A PNG thumbnail must still be served as image/png.');
		$I->assertSame(self::PUBLIC_MEDIA, self::readSentinel($I->grabResponseBody()),
			'The bytes served were not the media library image that was asked for.');
	}

	public function aMediaLibraryImageStillRendersResized(AcceptanceTester $I)
	{
		$I->wantTo('keep resizing a media library image through thumb.php');

		$I->amOnPage('/thumb.php?src=e_MEDIA_IMAGE/e107_tests_p2_public.png&w=6');

		$I->assertSame(200, $I->grabResponseCode(), 'A resize request must still be served.');

		$body = $I->grabResponseBody();
		$I->assertSame(IMAGETYPE_PNG, self::rasterType($body),
			'A resize request must come back as a PNG. Got: '.self::excerpt($body));

		$info = getimagesizefromstring($body);
		$I->assertSame(6, $info[0], 'The image came back at the wrong width.');
	}

	public function anAvatarStillRenders(AcceptanceTester $I)
	{
		$I->wantTo('keep serving an avatar through thumb.php');

		$I->amOnPage('/thumb.php?src=e_AVATAR/e107_tests_p2_avatar.png');

		$I->assertSame(200, $I->grabResponseCode(), 'An avatar must still be served.');
		$I->assertSame(self::PUBLIC_AVATAR, self::readSentinel($I->grabResponseBody()),
			'The bytes served were not the avatar that was asked for.');
	}

	public function aPluginImageStillRenders(AcceptanceTester $I)
	{
		$I->wantTo('keep serving a plugin image through thumb.php');

		$I->amOnPage('/thumb.php?src='.self::PLUGIN_IMAGE.'&w=220&h=190');

		$I->assertSame(200, $I->grabResponseCode(), 'A plugin image must still be served.');
		$I->assertSame('image/jpeg', $I->grabHttpHeader('Content-Type'),
			'A JPEG thumbnail must still be served as image/jpeg.');

		$body = $I->grabResponseBody();
		$I->assertSame(IMAGETYPE_JPEG, self::rasterType($body),
			'A plugin thumbnail must come back as a JPEG. Got: '.self::excerpt($body));

		$info = getimagesizefromstring($body);
		$I->assertSame(220, $info[0], 'The plugin thumbnail came back at the wrong width.');
	}

	/**
	 * e107_files/ is where a v1.x site kept the images its stored [img] bbcode
	 * still names, and bb_img.php routes every local [img] through thumbUrl()
	 * whenever a resize width is set, which the shipped news and page bbcode
	 * preferences both do. download_setup.php also rewrites every download
	 * image to {e_FILE}downloadimages/, and download_class.php:385 thumbnails
	 * it for the og:image tag. A root list that omits the directory answers all
	 * of that with 403 on every site that has ever been upgraded.
	 */
	public function aLegacyPublicFileStillRenders(AcceptanceTester $I)
	{
		$I->wantTo('keep serving an image out of e107_files/public through thumb.php');

		// The bare docroot-relative spelling, which is what a v1.x [img] body
		// holds and what bb_img.php hands to thumbUrl(). The "{e_FILE}" form
		// cannot be asked for at all: e107::set_request() strips braces out of
		// every query string and "e_FILE/" is not one of the raw prefixes
		// e_parse::getUrlConstants() maps back, so it arrives as nonsense.
		$I->amOnPage('/thumb.php?src='.rawurlencode($this->legacyImage()).'&w=13');

		$I->assertSame(200, $I->grabResponseCode(),
			'A legacy e107_files image must still be served. Body: '.self::excerpt($I->grabResponseBody()));
		$I->assertSame(self::PUBLIC_LEGACY, self::readSentinel($I->grabResponseBody()),
			'The bytes served were not the legacy e107_files image that was asked for.');
	}

	/**
	 * The other half of the private message exclusion. Forum attachments live
	 * in the directory next to the private message ones and
	 * view_shortcodes.php thumbnails them on purpose ("Always use thumb to hide
	 * the hash"), so shutting the door on e_MEDIA/plugins/ wholesale would take
	 * a shipped feature with it. This is what says which of the two subtrees
	 * the exclusion is allowed to name.
	 */
	public function aForumAttachmentStillRenders(AcceptanceTester $I)
	{
		$I->wantTo('keep serving a forum attachment through thumb.php');

		// The forum releases an attachment to whoever may read the post that
		// names it, so the fixture is the post as much as it is the bytes. A
		// file in a poster's directory that no post names is not an attachment
		// and is refused; asking for one here would measure that refusal
		// instead of the containment rule this test exists for.
		$I->haveForumPluginInstalled();
		$ids = $I->haveForumStructure();
		$I->haveForumPostWithAttachments('p2 attachment carrier', $ids['threadA'], $ids['forumA'], 42,
			array('img' => array(array(
				'file' => basename($this->forumAttachment()), 'name' => 'p2.png', 'size' => 1))));

		$src = 'e_MEDIA/plugins/forum/attachments/user_000042/'.basename($this->forumAttachment());

		$I->amOnPage('/thumb.php?src='.rawurlencode($src).'&w=13');

		$I->assertSame(200, $I->grabResponseCode(),
			'A forum attachment must still be served. Body: '.self::excerpt($I->grabResponseBody()));
		$I->assertSame(self::PUBLIC_FORUM, self::readSentinel($I->grabResponseBody()),
			'The bytes served were not the forum attachment that was asked for.');
	}

	/**
	 * A source that is inside the permitted roots and simply is not there is
	 * not an attack, and answering it with the placeholder is what keeps a page
	 * whose image was deleted from breaking. Containment must not collapse into
	 * refusing everything it cannot find.
	 *
	 * Both depths, because they are answered by different code. A missing file
	 * in a subdirectory of a root is found by walking up one level; a missing
	 * file directly inside a root is found by asking whether a root contains
	 * itself, which the prefix test in e_file::resolveSendPath() answers "no"
	 * to. e107_images/ holds logo.png and adminlogo.png at its top level, so
	 * that second shape is the shape a renamed site logo takes.
	 */
	public function aMissingButLegitimateSourceStillYieldsThePlaceholder(AcceptanceTester $I)
	{
		$I->wantTo('keep answering a missing media image with the placeholder');

		$sources = array(
			'e_MEDIA_IMAGE/e107_tests_p2_absent.png',
			'e_IMAGE/e107_tests_p2_absent.png',
			'e_MEDIA/e107_tests_p2_absent.png',
		);

		foreach($sources as $src)
		{
			$I->amOnPage('/thumb.php?src='.rawurlencode($src).'&w=60&h=40');

			$I->assertSame(200, $I->grabResponseCode(),
				$src.': a missing image must still answer with a placeholder, not a refusal. Body: '
				.self::excerpt($I->grabResponseBody()));
			$I->assertStringContainsString('image/svg+xml', $I->grabHttpHeader('Content-Type'),
				$src.': the placeholder must still be the generated SVG.');
		}
	}

	/**
	 * The legacy grammar, in the three shapes twenty years of themes emit: a
	 * width on a source larger than it, the same with the "noscale" keyword
	 * the endpoint's own usage block advertised, and a width on a source
	 * smaller than it.
	 *
	 * The keyword is accepted and ignored rather than refused. It selected
	 * between ways of handling a source already smaller than the requested
	 * width, and one of those ways was the readfile() that this package exists
	 * to remove; but the source is contained now, so answering the other two
	 * with a 403 would break working themes for nothing. What it costs is the
	 * upsize decision: e_thumbnail derives that from the width alone, so a
	 * source narrower than the request comes back unenlarged below 111 pixels
	 * and enlarged above it whatever the caller named. Pinned here so the
	 * divergence is a recorded decision rather than a surprise.
	 *
	 * The first case is also the one control in the file that did not pass on
	 * the unfixed tree, and the reason is worth writing down. resize_image()'s
	 * gd1/gd2 branch signals "send it to the browser" by passing '' as the
	 * output filename, and every PHP 8 image writer raises "Path must not be
	 * empty" on that. The branch has therefore emitted nothing but a fatal
	 * error for the whole of PHP 8, which is to say that the only shapes the
	 * legacy endpoint still served were the ImageMagick one and the noscale
	 * passthrough, and the passthrough is the vulnerability. Delegating to
	 * e_thumbnail is what makes this pass.
	 */
	public function theLegacyEndpointStillServesAWidthConstrainedResize(AcceptanceTester $I)
	{
		$I->wantTo('keep serving the legacy width-constrained resize');

		$small = $this->publicImage(); // 13 pixels wide

		$cases = array(
			// request                                                    width
			'e107_plugins/gallery/images/butterfly.jpg+100'            => 100,
			'e107_plugins/gallery/images/butterfly.jpg+100+noscale'    => 100,
			$small.'+100'                                              => 13,
			$small.'+100+noscale'                                      => 13,
		);

		foreach($cases as $query => $width)
		{
			$I->amOnPage('/e107_images/thumb.php?'.$query);

			$body = $I->grabResponseBody();

			$I->assertLessThan(400, $I->grabResponseCode(),
				$query.': the legacy resize shape must be served, not refused. Got: '.self::excerpt($body));
			$I->assertNotFalse(self::rasterType($body),
				$query.': the legacy resize shape must come back as an image. Got: '.self::excerpt($body));

			$info = getimagesizefromstring($body);
			$I->assertSame($width, $info[0], $query.': the legacy resize came back at the wrong width.');
		}
	}

	// ------------------------------------------------------------------
	// (a) Absolute path sources.
	// ------------------------------------------------------------------

	/**
	 * The doubled leading slash is not decoration. createConstants($src, 'mix')
	 * runs before anything looks at the path, and its absolute-path table maps
	 * e_HTTP onto {e_BASE}; on a root install e_HTTP is "/", so a single-slash
	 * absolute path is rewritten into a docroot-relative one and misses. The
	 * one thing that table refuses to rewrite is a URL beginning "//", because
	 * that is how a protocol-relative CDN URL is spelled, and "//var/www/html"
	 * is a path the kernel resolves exactly like "/var/www/html". So the
	 * exemption written to protect CDN URLs is what carries the absolute path
	 * through untouched.
	 */
	public function theModernEndpointRefusesAnAbsolutePathSource(AcceptanceTester $I)
	{
		$I->wantTo('refuse an absolute filesystem path through thumb.php');

		$root = $this->env['ROOT'];
		$system = $root.'/'.$this->systemSecret();
		$outside = $this->env['OUTSIDE'];

		$payloads = array(
			$system        => self::SECRET_SYSTEM,
			$outside       => self::SECRET_OUTSIDE,
			'/'.$system    => self::SECRET_SYSTEM,
			'/'.$outside   => self::SECRET_OUTSIDE,
		);

		$failures = array();

		foreach($payloads as $path => $sentinel)
		{
			$I->amOnPage('/thumb.php?src='.rawurlencode($path));
			$failures = self::collect($failures,
				$this->containmentFailure($I, 'thumb.php?src='.$path, $sentinel));
		}

		$this->seeNoFailures($I, $failures, 'absolute paths');
	}

	public function theLegacyEndpointRefusesAnAbsolutePathSource(AcceptanceTester $I)
	{
		$I->wantTo('refuse an absolute filesystem path through e107_images/thumb.php');

		$outside = $this->env['OUTSIDE'];

		// noscale on a source smaller than the requested size is the readfile()
		// passthrough, so this asks for the bytes of a file that lives outside
		// the docroot entirely and cannot be reached by any other request.
		$failures = array();

		$I->amOnPage('/e107_images/thumb.php?'.$outside.'+100+noscale');
		$failures = self::collect($failures,
			$this->containmentFailure($I, 'legacy noscale of '.$outside, self::SECRET_OUTSIDE));

		$I->amOnPage('/e107_images/thumb.php?'.$outside.'+100');
		$failures = self::collect($failures,
			$this->containmentFailure($I, 'legacy resize of '.$outside, self::SECRET_OUTSIDE));

		$this->seeNoFailures($I, $failures, 'absolute paths');
	}

	// ------------------------------------------------------------------
	// (b) scheme:// sources.
	// ------------------------------------------------------------------

	/**
	 * The deny-list at e_thumbnail_class.php:201 names five schemes. Anything
	 * else falls through to is_file(), and what is_file() answers for a stream
	 * wrapper depends on which wrappers the build registered rather than on any
	 * decision e107 made. On this build none of the extra wrappers answers yes,
	 * so nothing leaks; what does happen is that a source e107 has no business
	 * resolving is quietly filed as a missing image and answered with the
	 * placeholder. An allow-list rejects it instead, and the difference is what
	 * this test pins.
	 */
	public function theModernEndpointRefusesEverySchemeShapedSource(AcceptanceTester $I)
	{
		$I->wantTo('refuse any scheme-shaped source through thumb.php');

		$outside = $this->env['OUTSIDE'];

		$this->seeTheSsrfTargetIsReachable($I);

		$payloads = array(
			'file://'.$outside,
			'http://127.0.0.1/'.$this->publicImage(),
			'php://filter/resource='.$outside,
			'phar://'.$outside,
			'zip://'.$outside,
			'compress.zlib://'.$outside,
			'glob://'.$outside,
			'FILE://'.$outside,
		);

		$failures = array();

		foreach($payloads as $payload)
		{
			$I->amOnPage('/thumb.php?src='.rawurlencode($payload));
			$failures = self::collect($failures,
				$this->schemeFailure($I, 'thumb.php?src='.$payload, self::SECRET_OUTSIDE));
		}

		$this->seeNoFailures($I, $failures, 'scheme-shaped sources');
	}

	public function theLegacyEndpointRefusesEverySchemeShapedSource(AcceptanceTester $I)
	{
		$I->wantTo('refuse any scheme-shaped source through e107_images/thumb.php');

		$outside = $this->env['OUTSIDE'];

		// getimagesize() and readfile() both speak http when allow_url_fopen is
		// on, so the http shape is a request the server makes on the caller's
		// behalf as well as a read.
		$http = 'http://127.0.0.1/'.$this->publicImage();

		$this->seeTheSsrfTargetIsReachable($I);

		$payloads = array(
			array($http.'+100+noscale', self::PUBLIC_MEDIA),
			array($http.'+100', self::PUBLIC_MEDIA),
			array('file://'.$outside.'+100+noscale', self::SECRET_OUTSIDE),
			array('file://'.$outside.'+100', self::SECRET_OUTSIDE),
		);

		$failures = array();

		foreach($payloads as $payload)
		{
			$I->amOnPage('/e107_images/thumb.php?'.$payload[0]);
			$failures = self::collect($failures,
				$this->containmentFailure($I, 'legacy ?'.$payload[0], $payload[1]));
		}

		$this->seeNoFailures($I, $failures, 'scheme-shaped sources');
	}

	// ------------------------------------------------------------------
	// (c) Traversal, including the dot-padded form.
	// ------------------------------------------------------------------

	/**
	 * checkSrc() strips every literal ".." and then trusts what is left, so the
	 * shortest payload needs no dots at all: the thumbnailer runs with the
	 * docroot as its working directory, and e107_system is a directory in it.
	 *
	 * The dot-padded forms are in the list for completeness rather than because
	 * they work here. str_replace('..', '') is a global strip of the pair, not
	 * a single pass over "../", so "....//" collapses to "//" and lands nowhere.
	 * They are the payloads the legacy endpoint falls to, and the containment
	 * has to hold for both endpoints, so both are asked.
	 */
	public function theModernEndpointRefusesASourceOutsideItsRoots(AcceptanceTester $I)
	{
		$I->wantTo('refuse a source outside the permitted roots through thumb.php');

		$secret = $this->systemSecret();

		$payloads = array(
			'e_MEDIA_IMAGE/....//....//'.$secret,
			'e_MEDIA_IMAGE/.%2e/.%2e/'.$secret,
			'./'.$secret,
			$secret,
			'e_SYSTEM/e107_tests_p2_secret.png',
		);

		$failures = array();

		foreach($payloads as $payload)
		{
			$I->amOnPage('/thumb.php?src='.$payload);
			$failures = self::collect($failures,
				$this->containmentFailure($I, 'thumb.php?src='.$payload, self::SECRET_SYSTEM));
		}

		$this->seeNoFailures($I, $failures, 'sources outside the permitted roots');
	}

	/**
	 * The legacy endpoint strips "../" once and prefixes "../", which puts the
	 * docroot in reach with no payload at all and everything above it in reach
	 * with a dot-padded one: "....//" is not "../", so it survives the strip
	 * and becomes "../" afterwards.
	 */
	public function theLegacyEndpointRefusesATraversalSource(AcceptanceTester $I)
	{
		$I->wantTo('refuse a traversal source through e107_images/thumb.php');

		$secret = $this->systemSecret();

		$payloads = array(
			'e107_media/....//'.$secret,      // dot padding survives the single-pass strip
			'e107_media/.%2e/'.$secret,       // the same thing, spelled with an encoded dot
			'e107_media/../'.$secret,         // the form the strip was written for
			$secret,                          // "../" prefix alone reaches the docroot
		);

		$failures = array();

		foreach($payloads as $payload)
		{
			$I->amOnPage('/e107_images/thumb.php?'.$payload.'+100+noscale');
			$failures = self::collect($failures,
				$this->containmentFailure($I, 'legacy noscale of '.$payload, self::SECRET_SYSTEM));
		}

		$this->seeNoFailures($I, $failures, 'traversal sources');
	}

	/**
	 * The residual read primitive, and the only assertion in the file that
	 * covers it.
	 *
	 * Containment says which directories may be read from; it says nothing
	 * about what may be read out of them. After the fix e_PLUGIN, e_THEME,
	 * e_WEB, e_IMAGE, e_AVATAR, e_FILE/public and all of e_MEDIA are readable
	 * by name, and the only thing standing between thumb.php and every PHP
	 * source file under them is that Intervention throws on bytes GD cannot
	 * decode. That is an accident of the library, not a decision this code
	 * makes, and it is one refactor away from reversing: a "no resize was
	 * asked for, so just stream the file" shortcut would satisfy every other
	 * test in this file and hand the tree back.
	 *
	 * Asked in every shape that could take a shortcut: no dimensions at all,
	 * with dimensions, through id=, and through the legacy grammar including
	 * the keyword that used to be exactly such a shortcut.
	 */
	public function aNonImageInsideAPermittedRootIsNeverServed(AcceptanceTester $I)
	{
		$I->wantTo('refuse a non-image that lives inside a permitted root');

		$src = 'e_MEDIA/images/'.basename($this->inRootSecret());

		$requests = array(
			'/thumb.php?src='.rawurlencode($src),
			'/thumb.php?src='.rawurlencode($src).'&w=60',
			'/thumb.php?src='.rawurlencode($src).'&aw=60&ah=60&c=C',
			'/thumb.php?id='.rawurlencode(base64_encode('src={e_MEDIA}images/'.basename($this->inRootSecret()))),
			'/e107_images/thumb.php?'.$this->inRootSecret().'+100',
			'/e107_images/thumb.php?'.$this->inRootSecret().'+100+noscale',
		);

		$failures = array();

		foreach($requests as $request)
		{
			$I->amOnPage($request);
			$failures = self::collect($failures,
				$this->disclosureFailure($I, $request, self::SECRET_INROOT));
		}

		$this->seeNoFailures($I, $failures, 'requests for a non-image inside a permitted root');
	}

	// ------------------------------------------------------------------
	// (d) {e_SYSTEM} smuggled through the base64 id= parameter.
	// ------------------------------------------------------------------

	/**
	 * set_request() strips braces out of the query string, which is the only
	 * thing keeping a path shortcode out of a thumbnail request. id= is
	 * base64_decode()d after that, in parseRequest(), so the payload is never
	 * shown to the sanitiser that was supposed to see it.
	 */
	public function theModernEndpointRefusesAPathShortcodeSmuggledThroughId(AcceptanceTester $I)
	{
		$I->wantTo('refuse {e_SYSTEM} smuggled through the base64 id= parameter');

		$payloads = array(
			'src={e_SYSTEM}e107_tests_p2_secret.png',
			'src={e_BASE}'.$this->systemSecret(),
		);

		$failures = array();

		foreach($payloads as $payload)
		{
			$id = rawurlencode(base64_encode($payload));

			$I->amOnPage('/thumb.php?id='.$id);
			$failures = self::collect($failures, $this->containmentFailure(
				$I, 'thumb.php?id= carrying "'.$payload.'"', self::SECRET_SYSTEM));
		}

		$this->seeNoFailures($I, $failures, 'base64 id= payloads');
	}

	/**
	 * The brace-stripping in set_request() is what makes id= worth attacking,
	 * so pin it: the same payload spelled out in the query string must not
	 * resolve either.
	 */
	public function theModernEndpointRefusesAPathShortcodeInThePlainQuery(AcceptanceTester $I)
	{
		$I->wantTo('refuse {e_SYSTEM} spelled out in the query string');

		$payload = 'src={e_SYSTEM}e107_tests_p2_secret.png';

		$I->amOnPage('/thumb.php?'.$payload);
		$this->seeContained($I, 'thumb.php?'.$payload, self::SECRET_SYSTEM);
	}

	/**
	 * The positive control for the same route, which the refusals above cannot
	 * give: they pass just as well when the payload is mangled into something
	 * that resolves to nothing.
	 *
	 * The payload is the one thumbUrl() actually writes into id=, separators
	 * HTML-encoded and all, because that is what .htaccess line 68 rewrites
	 * /media/img/<base64>.jpg onto. Getting that decoding wrong drops w= and h=
	 * and every thumbnail on a rewriting site silently comes back at the
	 * source's own size, which is invisible to any assertion about refusals.
	 * The plain query twin is asked in the same test so a divergence between
	 * the two decoders shows up as a difference between two lines.
	 */
	public function theModernEndpointStillServesADimensionedIdRequest(AcceptanceTester $I)
	{
		$src = 'e_MEDIA_IMAGE/'.basename($this->publicImage());

		$I->wantTo('keep serving a base64 id= request at the dimensions it asks for');

		$requests = array(
			'/thumb.php?id='.rawurlencode(base64_encode('src='.$src.'&amp;w=6&amp;h=6')),
			'/thumb.php?id='.rawurlencode(base64_encode('src='.$src.'&w=6&h=6')),
			'/thumb.php?src='.$src.'&w=6&h=6',
		);

		foreach($requests as $request)
		{
			$I->amOnPage($request);

			$body = $I->grabResponseBody();

			$I->assertSame(200, $I->grabResponseCode(),
				$request.': a legitimate thumbnail request must be served. Body: '.self::excerpt($body));
			$I->assertSame(IMAGETYPE_PNG, self::rasterType($body),
				$request.': must come back as a PNG. Got: '.self::excerpt($body));

			$info = getimagesizefromstring($body);
			$I->assertSame(6, $info[0],
				$request.': the dimensions in the request were dropped; the image came back at '
				.$info[0].' pixels wide.');
		}
	}

	// ------------------------------------------------------------------
	// (e) Unbounded dimensions.
	// ------------------------------------------------------------------

	/**
	 * w, h, aw and ah reach GD unclamped from an unauthenticated request. The
	 * only outcomes worth accepting are a refusal or a clamp; a 5xx means the
	 * process tried and failed to allocate, which is the denial of service.
	 */
	public function theModernEndpointRefusesOversizedDimensions(AcceptanceTester $I)
	{
		$I->wantTo('refuse an oversized thumbnail request through thumb.php');

		$payloads = array(
			'w=99999&h=99999',
			'w=65535',
			'h=65535',
			'aw=99999&ah=99999',
			'aw=99999&ah=99999&c=C',
		);

		$failures = array();

		foreach($payloads as $payload)
		{
			$I->amOnPage('/thumb.php?src=e_MEDIA_IMAGE/e107_tests_p2_public.png&'.$payload);
			$failures = self::collect($failures,
				$this->boundFailure($I, 'thumb.php with '.$payload, true));
		}

		$this->seeNoFailures($I, $failures, 'oversized requests');
	}

	/**
	 * The legacy endpoint has bounded its size at 4000 since 0.8, which is the
	 * precedent the modern one is missing. A guard rather than a reproduction:
	 * the shim must not lose the one check the code it replaces got right.
	 */
	public function theLegacyEndpointKeepsBoundingItsSize(AcceptanceTester $I)
	{
		$I->wantTo('keep the legacy 4000 pixel bound through e107_images/thumb.php');

		$failures = array();

		foreach(array('4001', '9999', '99999999', '2147483647') as $size)
		{
			$I->amOnPage('/e107_images/thumb.php?e107_plugins/gallery/images/butterfly.jpg+'.$size);
			$failures = self::collect($failures, $this->boundFailure($I, 'legacy resize at '.$size));
		}

		$this->seeNoFailures($I, $failures, 'oversized requests');
	}

	// ------------------------------------------------------------------
	// (f) The type parameter.
	// ------------------------------------------------------------------

	/**
	 * type is never filtered and becomes the extension of the cache file that
	 * thumbCacheFile() names, so an unauthenticated caller chooses part of a
	 * filename the application then creates on disk. Asserted on the directory
	 * listing rather than on the response, because the response looks the same
	 * either way.
	 *
	 * An empty listing satisfies "no cache file was created carrying the type"
	 * as happily as a correct one does, and the listing is empty whenever the
	 * thumbnailer refused every request or the cache directory is not writable.
	 * So the legitimate entry is asserted first and again at the end: the
	 * absence of the three bad names is only evidence when it is measured
	 * against the presence of a good one.
	 */
	public function theModernEndpointRefusesANonImageType(AcceptanceTester $I)
	{
		$I->wantTo('refuse a non-image type= through thumb.php');

		$types = array('php', 'phtml', 'htaccess');

		$I->amOnPage('/thumb.php?src=e_MEDIA_IMAGE/e107_tests_p2_public.png&w=6');
		$I->assertSame(200, $I->grabResponseCode(), 'The control request must be served.');

		$cache = $this->probe($I, 'cache');
		$I->assertStringContainsString('thumb_e107_tests_p2_public', self::env($cache, 'CACHE'),
			'A legitimate request wrote no cache file, so the assertions below would hold against an '
			.'empty directory. The image cache holds: '.self::env($cache, 'CACHE'));

		foreach($types as $type)
		{
			$I->amOnPage('/thumb.php?src=e_MEDIA_IMAGE/e107_tests_p2_public.png&w=6&type='.$type);
			$I->assertLessThan(500, $I->grabResponseCode(),
				'type='.$type.' must not put the thumbnailer into a fatal error.');
		}

		$cache = $this->probe($I, 'cache');
		$listing = isset($cache['CACHE']) ? $cache['CACHE'] : '';

		$I->assertStringContainsString('thumb_e107_tests_p2_public', $listing,
			'The legitimate cache entry disappeared while the type= requests were made. '
			.'The image cache now holds: '.$listing);

		foreach($types as $type)
		{
			$I->assertStringNotContainsString('.'.$type.'.', $listing,
				'A cache file was created carrying the caller-supplied type "'.$type.'". '
				.'The image cache now holds: '.$listing);
		}
	}

	// ------------------------------------------------------------------
	// (g) What an error tells the caller.
	// ------------------------------------------------------------------

	/**
	 * thumb.php:27-41 answers an exception with the message, the file, the line
	 * and the whole stack trace, var_dump()ed to whoever asked. Reaching it
	 * needs nothing more than a file inside the media library that is not an
	 * image, which any member with upload rights can arrange.
	 */
	public function theModernEndpointLeaksNoStackTraceOnError(AcceptanceTester $I)
	{
		$I->wantTo('answer a thumbnailer error without a stack trace');

		$I->amOnPage('/thumb.php?src=e_MEDIA_IMAGE/e107_tests_p2_broken.png&w=60');

		$this->seeOpaqueError($I, 'thumb.php on an undecodable source', 'P2NOTANIMAGE');
	}

	public function theLegacyEndpointLeaksNoFilesystemPathOnError(AcceptanceTester $I)
	{
		$I->wantTo('answer a legacy thumbnailer error without a filesystem path');

		$I->amOnPage('/e107_images/thumb.php?'.$this->brokenImage().'+100');
		$this->seeOpaqueError($I, 'e107_images/thumb.php on an undecodable source', 'P2NOTANIMAGE');

		// The database password is in this file. seeOpaqueError() is about what
		// an error message says, and the two requests it is asked about here
		// both name a file, so it has to be told what the file holds as well:
		// a byte-for-byte disclosure of e107_config.php carries none of the
		// needles an error message is checked for.
		$params = unserialize(PARAMS_SERIALIZED);
		$dbname = isset($params['db']['dbname']) ? $params['db']['dbname'] : '';

		$I->amOnPage('/e107_images/thumb.php?'.$this->env['ROOT'].'/e107_config.php+100');
		$this->seeOpaqueError($I, 'e107_images/thumb.php on an absolute path', $dbname);
	}

	// ------------------------------------------------------------------
	// (h) The handover from package P5.
	// ------------------------------------------------------------------

	/**
	 * Private message attachments are stored under
	 * e107_media/<sitehash>/plugins/pm/attachments/user_NNNNNN/, and pm_class::
	 * send_file() guards them: it will not release one unless the caller is a
	 * party to the message. thumb.php asks no such question. It asks is_file(),
	 * and an image attachment therefore comes back to anybody who can name it.
	 *
	 * Containment against e_MEDIA does not close this on its own, because
	 * e_MEDIA is exactly where the file is. Nor is excluding e_MEDIA/plugins/
	 * free: forum/shortcodes/batch/view_shortcodes.php:474 thumbnails a forum
	 * attachment out of the sibling directory, with the comment "Always use
	 * thumb to hide the hash", so the same shape is a shipped feature one
	 * plugin over. Whatever closes this has to tell the two apart.
	 *
	 * The assertion is deliberately about the bytes rather than about a root
	 * list, so it stays honest whichever way the door is shut.
	 */
	public function aPrivateMessageAttachmentIsNotFetchableThroughTheThumbnailer(AcceptanceTester $I)
	{
		$I->wantTo('refuse a private message attachment through thumb.php');

		$src = 'e_MEDIA/plugins/pm/attachments/user_000042/'.$this->pmAttachmentName();
		$id = rawurlencode(base64_encode('src={e_MEDIA}plugins/pm/attachments/user_000042/'
			.$this->pmAttachmentName()));

		$requests = array(
			'/thumb.php?src='.$src,
			'/thumb.php?src='.$src.'&w=60',
			'/thumb.php?src='.$src.'&aw=60&ah=60&c=C',
			'/thumb.php?id='.$id,
		);

		$failures = array();

		foreach($requests as $request)
		{
			$I->amOnPage($request);
			$failures = self::collect($failures,
				$this->containmentFailure($I, $request, self::SECRET_PM));
		}

		$this->seeNoFailures($I, $failures, 'requests for a private attachment');
	}

	public function aPrivateMessageAttachmentIsNotFetchableThroughTheLegacyThumbnailer(AcceptanceTester $I)
	{
		$I->wantTo('refuse a private message attachment through e107_images/thumb.php');

		$path = $this->pmAttachment();

		$I->amOnPage('/e107_images/thumb.php?'.$path.'+100+noscale');
		$this->seeContained($I, 'legacy noscale of '.$path, self::SECRET_PM);
	}

	/**
	 * The same attachment in the other place pm_class::send_file() reads it
	 * from. pm_class.php:857 lists e_PLUGIN."pm/attachments/" first and calls
	 * it the legacy path; pm_class.php:359 still unlinks from it, and the two
	 * admin orphan scanners still enumerate it. On any site upgraded from a
	 * release that used it, that is where the attachments are, and it sits
	 * inside e_PLUGIN, which the thumbnailer serves.
	 *
	 * The exclusion has to name both directories or it closes the door on one
	 * install and leaves it open on the other, which is the shape of the bug
	 * that produced GHSA-5w63-63rh-99q6.
	 */
	public function aLegacyPrivateMessageAttachmentIsNotFetchableEither(AcceptanceTester $I)
	{
		$I->wantTo('refuse a private message attachment stored beside the plugin');

		$name = $this->pmAttachmentName();

		$requests = array(
			'/thumb.php?src='.rawurlencode('{e_PLUGIN}pm/attachments/'.$name),
			'/thumb.php?src='.rawurlencode('{e_PLUGIN}pm/attachments/'.$name).'&w=60',
			'/thumb.php?src='.rawurlencode('e_PLUGIN/pm/attachments/'.$name).'&aw=60&ah=60&c=C',
			'/thumb.php?id='.rawurlencode(base64_encode('src={e_PLUGIN}pm/attachments/'.$name)),
			'/e107_images/thumb.php?'.$this->legacyPmAttachment().'+100',
			'/e107_images/thumb.php?'.$this->legacyPmAttachment().'+100+noscale',
		);

		$failures = array();

		foreach($requests as $request)
		{
			$I->amOnPage($request);
			$failures = self::collect($failures,
				$this->containmentFailure($I, $request, self::SECRET_PM));
		}

		$this->seeNoFailures($I, $failures, 'requests for a legacy private attachment');
	}

	// ------------------------------------------------------------------
	// Response headers.
	// ------------------------------------------------------------------

	/**
	 * Both the freshly generated answer and the cached one, because they are
	 * written by two different methods.
	 *
	 * Two identical requests only exercise two branches if the second one is
	 * served from the cache, and nothing about the response says so on its own.
	 * sendCachedImage() is the only caller that fills in md5s and lmodified, so
	 * an Etag is the evidence that the second answer came from disk: without
	 * that assertion this passes just as well on a site whose cache directory
	 * is not writable, with the cached branch never executed.
	 */
	public function theThumbnailerDeclaresNosniff(AcceptanceTester $I)
	{
		$I->wantTo('serve thumbnails with X-Content-Type-Options: nosniff');

		$I->amOnPage('/thumb.php?src=e_MEDIA_IMAGE/e107_tests_p2_public.png&w=6');
		$I->assertSame(200, $I->grabResponseCode(), 'The control request must succeed.');
		$I->seeHttpHeader('X-Content-Type-Options', 'nosniff');
		$I->assertEmpty($I->grabHttpHeader('Etag'),
			'The first answer carried an Etag, so it was already the cached branch and the two '
			.'requests below prove one thing rather than two.');

		$I->amOnPage('/thumb.php?src=e_MEDIA_IMAGE/e107_tests_p2_public.png&w=6');
		$I->assertSame(200, $I->grabResponseCode(), 'The cached request must succeed.');
		$I->seeHttpHeader('X-Content-Type-Options', 'nosniff');
		$I->assertNotEmpty($I->grabHttpHeader('Etag'),
			'The second answer carried no Etag, so it did not come from the cache and the cached '
			.'response path is untested.');
	}

	/**
	 * The cache filename is keyed on the path the URL names, not on the
	 * realpath() of it.
	 *
	 * e_parse::thumbCacheFile() hashes the source path after expanding the
	 * shortcodes in it, and e_parse::thumbUrl() calls it with the URL form,
	 * which expands to a docroot-relative path. thumb.php has to arrive at the
	 * same string or the names part company for good: on a site with
	 * e_MEDIA_STATIC defined, thumbUrl() serves the file it named directly when
	 * it is readable and it never will be again, and on any site every
	 * thumbnail already on disk is orphaned rather than reused.
	 *
	 * The expected name comes from the application rather than from a copy of
	 * the algorithm here, because a copy would agree with a wrong answer. The
	 * options are spelled the way e_thumbnail::getRequestOptions() spells them,
	 * with the unrequested keys present and empty, so that the path is the only
	 * thing under test. thumbUrl() itself passes the query string, in which
	 * those keys are absent rather than empty, and that difference alone gives
	 * a different hash; it predates this work and is not what is measured here.
	 */
	public function theCacheFilenameIsKeyedOnTheRelativePath(AcceptanceTester $I)
	{
		$I->wantTo('key the thumbnail cache on the path the URL names');

		$src = 'e_MEDIA_IMAGE/'.basename($this->publicImage());
		$opts = 'w=7&h=7&aw=&ah=&c=';

		$I->amOnPage('/thumb.php?src='.$src.'&w=7&h=7');
		$I->assertSame(200, $I->grabResponseCode(),
			'The control request must be served. Body: '.self::excerpt($I->grabResponseBody()));

		$expected = $this->probe($I, 'cachename', array('src' => $src, 'opts' => $opts));
		$name = self::env($expected, 'CACHENAME');

		$I->assertNotSame('', $name, 'The probe could not compute a cache filename.');

		$cache = $this->probe($I, 'cache');

		$I->assertStringContainsString($name, self::env($cache, 'CACHE'),
			'thumb.php wrote its cache entry under a different name from the one thumbCacheFile() '
			.'gives for the same source spelled as a URL. Expected '.$name.'; the image cache '
			.'holds: '.self::env($cache, 'CACHE'));
	}

	// ------------------------------------------------------------------
	// Assertions
	// ------------------------------------------------------------------

	/**
	 * A response that read the file it was pointed at, in any of the three ways
	 * it could show: the trailing bytes, the pixels, or the mere fact that a
	 * raster image came back at all. The last one is what makes this hard to
	 * satisfy dishonestly: a thumbnailer that refused a source cannot have
	 * produced a raster from it, and the placeholder is an SVG.
	 *
	 * @param AcceptanceTester $I
	 * @param string $label   the request, for the failure message
	 * @param string $secret  the sentinel that must not come back
	 * @return void
	 */
	private function seeContained(AcceptanceTester $I, $label, $secret)
	{
		$failure = $this->containmentFailure($I, $label, $secret);

		$I->assertEmpty($failure, $failure);
	}

	/**
	 * Why the last response counts as a disclosure, or '' when it does not.
	 *
	 * Returned rather than asserted so a test can put several payloads to the
	 * same endpoint and report on all of them. An assertion inside the loop
	 * stops at the first one that leaks, which is how a suite ends up proving
	 * one payload and silently skipping the rest.
	 *
	 * @param AcceptanceTester $I
	 * @param string $label   the request, for the failure message
	 * @param string $secret  the sentinel that must not come back
	 * @return string
	 */
	private function containmentFailure(AcceptanceTester $I, $label, $secret)
	{
		$code = $I->grabResponseCode();

		if($code >= 500)
		{
			return $label.': answered '.$code.'; a refused source must not fatal. '
				.'Body: '.self::excerpt($I->grabResponseBody());
		}

		return $this->disclosureFailure($I, $label, $secret);
	}

	/**
	 * The same three tests on the bytes, without the opinion about the status.
	 *
	 * A source the thumbnailer is allowed to read but the image library cannot
	 * decode is answered with an opaque 500, and that is a legitimate answer:
	 * what matters is that nothing of the file comes back with it, which is
	 * what this measures. theModernEndpointLeaksNoStackTraceOnError() is what
	 * holds the body of that 500 to saying nothing.
	 *
	 * @param AcceptanceTester $I
	 * @param string $label
	 * @param string $secret
	 * @return string
	 */
	private function disclosureFailure(AcceptanceTester $I, $label, $secret)
	{
		$body = $I->grabResponseBody();

		if(strpos($body, $secret) !== false)
		{
			return $label.': returned the contents of the file, byte for byte.';
		}

		if(self::readSentinel($body) === $secret)
		{
			return $label.': returned the file re-encoded as a thumbnail.';
		}

		if(self::rasterType($body) !== false)
		{
			return $label.': answered with a raster image, so it read and decoded the source.';
		}

		return '';
	}

	/**
	 * Containment, plus the part that is specific to a scheme: the placeholder
	 * is the answer to a missing file, and a source e107 will not resolve is
	 * not a missing file. Answering the two the same way leaves the question of
	 * what is readable to whichever stream wrappers the build happens to carry.
	 *
	 * @param AcceptanceTester $I
	 * @param string $label
	 * @param string $secret
	 * @return string
	 */
	private function schemeFailure(AcceptanceTester $I, $label, $secret)
	{
		$failure = $this->containmentFailure($I, $label, $secret);

		if($failure !== '')
		{
			return $failure;
		}

		if($I->grabResponseCode() < 400 && strpos($I->grabResponseBody(), '<svg') !== false)
		{
			return $label.': was filed as a missing image and answered with the placeholder, '
				.'rather than rejected as a source carrying a scheme.';
		}

		return '';
	}

	/**
	 * The http:// payloads say nothing unless the server could have fetched
	 * them. If the application is served on another port or under a path
	 * prefix, the fetch fails for a reason that has nothing to do with the
	 * fix and the case passes against a fully vulnerable tree.
	 *
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function seeTheSsrfTargetIsReachable(AcceptanceTester $I)
	{
		$I->amOnPage('/'.$this->publicImage());

		$I->assertSame(self::PUBLIC_MEDIA, self::readSentinel($I->grabResponseBody()),
			'http://127.0.0.1/'.$this->publicImage().' is not the media fixture, so the scheme cases '
			.'that ask the server to fetch it prove nothing.');
	}

	/**
	 * @param array $failures
	 * @param string $failure
	 * @return array
	 */
	private static function collect(array $failures, $failure)
	{
		if($failure !== '')
		{
			$failures[] = $failure;
		}

		return $failures;
	}

	/**
	 * @param AcceptanceTester $I
	 * @param array $failures
	 * @param string $what
	 * @return void
	 */
	private function seeNoFailures(AcceptanceTester $I, array $failures, $what)
	{
		$I->assertEmpty($failures,
			count($failures).' of the '.$what." were honoured:\n  ".implode("\n  ", $failures));
	}

	/**
	 * The two acceptable answers to an absurd size: a refusal, or an image
	 * within the limit the legacy endpoint has always enforced.
	 *
	 * @param AcceptanceTester $I
	 * @param string $label
	 * @param bool   $strict require the refusal to be a status rather than prose
	 * @return string
	 */
	private function boundFailure(AcceptanceTester $I, $label, $strict = false)
	{
		$code = $I->grabResponseCode();
		$body = $I->grabResponseBody();

		if($code >= 500)
		{
			return $label.': answered '.$code.'; an oversized request must be refused, not '
				.'attempted. Body: '.self::excerpt($body);
		}

		$type = self::rasterType($body);

		if($type === false)
		{
			if($strict && $code < 400)
			{
				return $label.': answered '.$code.' with something that is not an image. A clamp '
					.'has to produce a thumbnail and a refusal has to say so. '
					.'Body: '.self::excerpt($body);
			}

			return ''; // refused in words or in status; either way nothing was allocated
		}

		$info = getimagesizefromstring($body);

		if($info[0] > 4000 || $info[1] > 4000)
		{
			return $label.': produced an image '.$info[0].'x'.$info[1].' pixels.';
		}

		return '';
	}

	/**
	 * An error the caller learns nothing from, about a file the caller learns
	 * nothing from either.
	 *
	 * @param AcceptanceTester $I
	 * @param string $label
	 * @param string $forbiddenContent bytes out of the file the request named
	 * @return void
	 */
	private function seeOpaqueError(AcceptanceTester $I, $label, $forbiddenContent)
	{
		$body = $I->grabResponseBody();

		$I->assertNotSame('', $forbiddenContent,
			$label.': a disclosure check with nothing to look for proves nothing.');
		$I->assertStringNotContainsString($forbiddenContent, $body,
			$label.' returned the contents of the file it was pointed at.');

		$I->assertStringNotContainsString($this->env['ROOT'], $body,
			$label.' printed the absolute path of the install.');

		$forbidden = array('Trace:', '#0 ', 'Stack trace', 'Exception:', '.php on line',
			'e_thumbnail_class', 'Intervention', 'e107_handlers');

		foreach($forbidden as $needle)
		{
			$I->assertStringNotContainsString($needle, $body,
				$label.' printed "'.$needle.'" to an unauthenticated caller.');
		}
	}

	// ------------------------------------------------------------------
	// Fixtures
	// ------------------------------------------------------------------

	/**
	 * A PNG whose pixel row spells $text, with $text repeated after IEND.
	 *
	 * Two channels on purpose. GD drops everything after IEND, so the trailing
	 * copy comes back only from a route that hands over the file unaltered, and
	 * that is precisely what resize_image()'s noscale branch does. The pixel
	 * copy survives e_thumbnail's re-encode instead, because a PNG round trip
	 * through GD is lossless, so it comes back from the route that rewrites the
	 * image. Greyscale so one channel carries the byte.
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

		return $png."\n".$text."\n";
	}

	/**
	 * The IMAGETYPE_* of $body, or false when it is not a raster image.
	 *
	 * Not getimagesizefromstring() on its own. Its WBMP branch is a heuristic
	 * over arbitrary bytes rather than a magic number test, and it happily
	 * reports the thumbnailer's own SVG placeholder as a 1 pixel WBMP. A
	 * containment assertion built on that would have read "no image came back"
	 * for a response that was an image, and the reverse: it reported a raster
	 * for the placeholder, which is exactly the answer a contained thumbnailer
	 * is allowed to give.
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

		$raster = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP);

		// IMAGETYPE_WEBP arrived in PHP 7.1, and this branch still supports
		// 5.6, where naming it is an undefined-constant notice that PHPUnit
		// turns into a test error. getimagesizefromstring() on that
		// interpreter cannot return the type either, so the entry is only
		// ever useful where the constant exists.
		if(defined('IMAGETYPE_WEBP'))
		{
			$raster[] = IMAGETYPE_WEBP;
		}

		return in_array($info[2], $raster, true) ? $info[2] : false;
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

		if($width !== strlen(self::SECRET_SYSTEM)) // every sentinel is this long
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
	 * @return string
	 */
	private function pmAttachmentName()
	{
		// The shape pm_class::send_file() parses: timestamp, sender, nonce, name.
		return '1750000000_42_9c1f_p2-private.png';
	}

	private function systemSecret()
	{
		return $this->system.'e107_tests_p2_secret.png';
	}

	private function outsideSource()
	{
		return $this->system.'e107_tests_p2_outside_src.png';
	}

	private function pmAttachment()
	{
		return $this->media.'plugins/pm/attachments/user_000042/'.$this->pmAttachmentName();
	}

	/**
	 * The other directory pm_class::send_file() reads an attachment from, and
	 * the one pm_class still deletes from. It sits inside e_PLUGIN, which is a
	 * root the thumbnailer serves.
	 *
	 * @return string
	 */
	private function legacyPmAttachment()
	{
		return $this->plugins.'pm/attachments/'.$this->pmAttachmentName();
	}

	/**
	 * A forum attachment, in the directory next door to the private message
	 * one. view_shortcodes.php thumbnails these deliberately, so it is the
	 * control that stops the private-message exclusion being widened to the
	 * whole of e_MEDIA/plugins/.
	 *
	 * @return string
	 */
	private function forumAttachment()
	{
		return $this->media.'plugins/forum/attachments/user_000042/e107_tests_p2_forum.png';
	}

	private function publicImage()
	{
		return $this->media.'images/e107_tests_p2_public.png';
	}

	private function avatarImage()
	{
		return $this->media.'avatars/e107_tests_p2_avatar.png';
	}

	/**
	 * Where a v1.x site kept the images its stored [img] bbcode still points
	 * at, and where download_setup.php rewrites download images to.
	 *
	 * @return string
	 */
	private function legacyImage()
	{
		return $this->files.'public/e107_tests_p2_legacy.png';
	}

	private function brokenImage()
	{
		return $this->media.'images/e107_tests_p2_broken.png';
	}

	/**
	 * A file inside a permitted root that is not an image at all. Nothing but
	 * Intervention refusing to decode it stands between this and an in-root
	 * arbitrary read.
	 *
	 * @return string
	 */
	private function inRootSecret()
	{
		return $this->media.'images/e107_tests_p2_secret.txt';
	}

	/**
	 * Run the probe and read back the KEY=value lines it prints.
	 *
	 * @param AcceptanceTester $I
	 * @param string $act
	 * @param array  $params extra query parameters the action reads
	 * @return array
	 */
	private function probe(AcceptanceTester $I, $act, $params = array())
	{
		$query = http_build_query(array_merge(array('act' => $act), $params));

		$I->amOnPage('/'.self::PROBE.'?'.$query);

		$body = $I->grabResponseBody();

		if(strpos($body, 'THUMBPROBE_OK') === false)
		{
			throw new \RuntimeException('Thumbnail probe failed for "'.$act.'": '.trim(strip_tags($body)));
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
		$outside = 'e107_tests_p2_outside_src.png';
		$legacy = basename($this->legacyImage());
		$pm = $this->pmAttachmentName();

		return <<<PHP
<?php
// Fixture for 0034_ThumbnailContainmentCest. Removed again in the Cest's _after().
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');

\$act = isset(\$_GET['act']) ? \$_GET['act'] : '';
\$outside = rtrim(sys_get_temp_dir(), '/').'/$outside';
\$inside = e_SYSTEM.'$outside';

if(\$act === 'reset' || \$act === 'cleanup')
{
	e107::getDb()->delete('online');
	e107::getDb()->delete('banlist', 'banlist_bantype IN (2, -2)');

	foreach(glob(e_CACHE_IMAGE.'*') ?: array() as \$file)
	{
		if(is_file(\$file))
		{
			@unlink(\$file);
		}
	}
}

if(\$act === 'move')
{
	if(is_file(\$inside))
	{
		@copy(\$inside, \$outside);
		@unlink(\$inside);
	}
}

if(\$act === 'cleanup')
{
	@unlink(\$outside);
	@unlink(\$inside);
	@unlink(e_FILE.'public/$legacy');
	@rmdir(e_FILE.'public');
	@unlink(e_PLUGIN.'pm/attachments/$pm');
	@rmdir(e_PLUGIN.'pm/attachments');
}

echo "THUMBPROBE_OK\n";
echo 'ROOT='.rtrim(e_ROOT, '/')."\n";
echo 'MEDIA='.e_MEDIA."\n";
echo 'SYSTEM='.e_SYSTEM."\n";
echo 'PLUGIN='.e_PLUGIN."\n";
echo 'FILE='.e_FILE."\n";
echo 'CACHEIMG='.e_CACHE_IMAGE."\n";
echo 'OUTSIDE='.\$outside."\n";

if(\$act === 'move')
{
	echo 'OUTSIDE_OK='.((is_file(\$outside) && filesize(\$outside) > 0) ? '1' : '0')."\n";
	echo 'INSIDE_GONE='.(is_file(\$inside) ? '0' : '1')."\n";
}

if(\$act === 'cachename')
{
	\$opts = isset(\$_GET['opts']) ? \$_GET['opts'] : '';
	echo 'CACHENAME='.e107::getParser()->thumbCacheFile(\$_GET['src'], \$opts)."\n";
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
PHP;
	}
}
