<?php

/**
 * A forum post attachment is stored under the document root, named from data
 * the post itself publishes, and handed out by two routes that never ask which
 * forum it hangs off.
 *
 *  - The bytes sit at e_MEDIA/plugins/forum/attachments/user_NNNNNN/<name>.
 *    e107 ships a deny rule for e107_system and none for e107_media, so the web
 *    server answers a request for that path itself, with no PHP and therefore
 *    no session, no userclass and no forum permission.
 *  - view_shortcodes.php renders an image attachment through thumbUrl(), so the
 *    same bytes come back a second way, re-encoded, through thumb.php. That
 *    endpoint reads e_MEDIA by design (avatars and site images live there) and
 *    knows nothing about forums.
 *
 * Only the third route asks. forum_class::sendFile(), reached as
 * forum_viewtopic.php?id=<post>&dl=<key>, calls checkPerm($forum, 'view')
 * before it sends, and it is reached only for the "file" attachment type. The
 * "img" type has never had a gate of any kind.
 *
 * The stored name carries no random component either: upload_handler.php:256
 * builds it as time()."_".USERID."_" plus the uploaded name, all three of which
 * a reader of the thread already knows to within a few thousand guesses.
 *
 * Every refusal here is paired with a positive control, because "restricted
 * attachments are unreachable" is trivially satisfied by a forum that serves no
 * attachments at all. The controls are load bearing: a public forum's
 * attachment must still render to a guest and still download for one, and a
 * member who holds the restricted forum's class must still get both.
 *
 * The sentinel is inside the file rather than around it. The image fixture is a
 * 13 pixel wide greyscale PNG whose pixel row spells its own name, with the
 * same name repeated after IEND; the pixel copy survives thumb.php's lossless
 * PNG re-encode and the trailing copy survives any route that hands the file
 * over unaltered. A refusal is never inferred from a status code alone.
 *
 * @see e107_plugins/forum/shortcodes/batch/view_shortcodes.php  sc_attachments()
 * @see e107_plugins/forum/forum_class.php  sendFile(), getAttachmentPath()
 * @see e107_handlers/upload_handler.php:256
 */
class ForumAttachmentServingCest
{
	const PROBE = 'e107_tests_p18_probe.php';

	/** A class nobody but the fixture member holds. */
	const CLASS_SECRET = 202;

	/** All the same length, so one decoder reads them all. */
	const SECRET_IMAGE = 'P18LEAK-RESTR';
	const PUBLIC_IMAGE = 'P18OKAY-PUBLC';
	const ORPHAN_IMAGE = 'P18LEAK-ORPHN';
	const ANON_IMAGE   = 'P18LEAK-ANONS';

	/** The non-image attachments, which come back byte for byte. */
	const SECRET_FILE = 'P18LEAK-RESTRICTED-FILE-BODY';
	const PUBLIC_FILE = 'P18OKAY-PUBLIC-FILE-BODY';

	const SECRET_IMAGE_FILE = 'p18_restricted.png';
	const PUBLIC_IMAGE_FILE = 'p18_public.png';
	const SECRET_TEXT_FILE  = 'p18_restricted.txt';
	const PUBLIC_TEXT_FILE  = 'p18_public.txt';

	/** In a poster's directory, named by no post at all. */
	const ORPHAN_IMAGE_FILE = 'p18_orphan.png';

	/** In the directory every guest shares, named by a guest's restricted post. */
	const ANON_IMAGE_FILE = 'p18_anon.png';

	/**
	 * The second spelling of the attachment tree. forum_update::moveAttachment()
	 * writes the reduced-resolution copy of a 0.7/0.8 attachment here, and
	 * nothing else in the plugin ever looks at this directory again.
	 */
	const LEGACY_THUMB_DIR = 'plugins/forum/attachments/thumb/';

	/** @var array */
	private $ids;

	/** @var int */
	private $restrictedForum;

	/** @var int */
	private $restrictedThread;

	/** @var int */
	private $restrictedPost;

	/** @var int */
	private $publicPost;

	/** @var int the member who holds CLASS_SECRET and posted both attachments */
	private $poster;

	/** @var int a member who holds nothing the restricted forum wants */
	private $outsider;

	/**
	 * @var string the poster's attachment directory, relative to the app root,
	 *             as the running application spells it
	 */
	private $dir;

	/** @var string the directory every guest shares, likewise */
	private $anonDir;

	/**
	 * The attachment directory is read out of the running site, never derived
	 * here. It hangs off a site path that is a hash of the database name on an
	 * interactively installed site and the literal "000000test" on one installed
	 * from a written e107_config.php, and the acceptance suite does both in one
	 * run. A fixture written to a computed path lands where the application
	 * never looks, and every refusal below then passes because there was
	 * nothing there to disclose.
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->stopFollowingRedirects();
		$I->resetAllCookies();

		$I->haveForumPluginInstalled();
		$I->resetForumFloodProtection();

		$I->writeAppFile(self::PROBE, $this->probeSource());

		// e107 bans an address after fifty requests in a window and every
		// request in the container arrives from the same bridge address, so the
		// ban goes before each test rather than once per suite. The image cache
		// goes with it: thumb.php keys a cache entry on the request parameters
		// and serves it back without looking at the source again, so an entry a
		// vulnerable run wrote would answer a fixed run's refusal with a hit.
		$this->probe($I, 'reset');

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');
		$I->haveUserClass(self::CLASS_SECRET, 'fixture_secret');

		$this->ids = $I->haveForumStructure();

		// Under the fixture's public category, so the parent passes the class
		// test _getForumPermList() applies to it and the restriction under test
		// is the child's own.
		$this->restrictedForum = $I->haveForum('Fixture Forum S', 'fixture-forum-s',
			$this->ids['category'], \Helper\ForumFixture::CLASS_MOD_A, 3, self::CLASS_SECRET);
		$this->restrictedThread = $I->haveForumThread('Fixture Thread S', $this->restrictedForum, 1);

		$this->poster = $I->haveForumMember('attachcarol', '253,'.self::CLASS_SECRET);
		$this->outsider = $I->haveForumMember('attachdave');

		$this->dir = $I->haveForumAttachmentDir($this->poster);
		$this->anonDir = $I->haveForumAttachmentDir(0);

		$I->writeAppFile($this->dir.self::SECRET_IMAGE_FILE, self::sentinelPng(self::SECRET_IMAGE));
		$I->writeAppFile($this->dir.self::PUBLIC_IMAGE_FILE, self::sentinelPng(self::PUBLIC_IMAGE));
		$I->writeAppFile($this->dir.self::SECRET_TEXT_FILE, self::SECRET_FILE);
		$I->writeAppFile($this->dir.self::PUBLIC_TEXT_FILE, self::PUBLIC_FILE);

		// A file in the poster's directory that no post names, which is what a
		// preview, a failed delete and a half-finished v1 migration each leave
		// behind, and a file in the directory every guest shares which only a
		// guest's post in the restricted forum names.
		$I->writeAppFile($this->dir.self::ORPHAN_IMAGE_FILE, self::sentinelPng(self::ORPHAN_IMAGE));
		$I->writeAppFile($this->anonDir.self::ANON_IMAGE_FILE, self::sentinelPng(self::ANON_IMAGE));

		// The migrated copy, in the tree the migration spells differently from
		// every other part of the plugin.
		$I->writeAppFile($this->legacyThumb(), self::sentinelPng(self::ORPHAN_IMAGE));

		// Both posts are by the same member, so both sets of bytes sit in one
		// directory. What separates them is the forum the post is in, which is
		// the only thing any of this may turn on.
		$this->restrictedPost = $I->haveForumPostWithAttachments('restricted carrier',
			$this->restrictedThread, $this->restrictedForum, $this->poster, array(
				'img'  => array(array('file' => self::SECRET_IMAGE_FILE, 'name' => 'secret.png', 'size' => 1)),
				'file' => array(array('file' => self::SECRET_TEXT_FILE, 'name' => 'secret.txt', 'size' => 1)),
			));

		$this->publicPost = $I->haveForumPostWithAttachments('public carrier',
			$this->ids['threadA'], $this->ids['forumA'], $this->poster, array(
				'img'  => array(array('file' => self::PUBLIC_IMAGE_FILE, 'name' => 'public.png', 'size' => 1)),
				'file' => array(array('file' => self::PUBLIC_TEXT_FILE, 'name' => 'public.txt', 'size' => 1)),
			));

		// A guest's attachment in the restricted forum. post_user 0 puts it in
		// the one directory every guest writes to, so the name is the only
		// thing separating it from the next guest's post.
		$I->haveForumPostWithAttachments('restricted guest carrier',
			$this->restrictedThread, $this->restrictedForum, 0, array(
				'img' => array(array('file' => self::ANON_IMAGE_FILE, 'name' => 'anon.png', 'size' => 1)),
			));

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$this->probe($I, 'cleanup');
		$I->deleteAppFile(self::PROBE);
		$I->dropForumProbe();
	}

	// ------------------------------------------------------------------
	// Positive controls. These come first: if a public forum cannot serve
	// its own attachments, every refusal below this line proves nothing.
	// ------------------------------------------------------------------

	/**
	 * A public forum renders its image attachments to anonymous visitors and
	 * always has. thumbUrl() is what view_shortcodes.php emits for them, so
	 * thumb.php is the route that has to keep working.
	 */
	public function aPublicForumAttachmentStillRendersToAGuest(AcceptanceTester $I)
	{
		$I->wantTo('keep rendering a public forum attachment to a guest');

		$I->amOnPage('/thumb.php?src='.rawurlencode($this->thumbSrc(self::PUBLIC_IMAGE_FILE)).'&w=13');

		$I->assertSame(200, $I->grabResponseCode(),
			'A public forum attachment must still be thumbnailed. Body: '.self::excerpt($I->grabResponseBody()));
		$I->assertSame(self::PUBLIC_IMAGE, self::readSentinel($I->grabResponseBody()),
			'The bytes served were not the public forum attachment that was asked for.');
	}

	/**
	 * The other half: a non-image attachment on a public post, through the
	 * download route the shortcode links.
	 */
	public function aPublicForumAttachmentStillDownloadsForAGuest(AcceptanceTester $I)
	{
		$I->wantTo('keep downloading a public forum attachment as a guest');

		$I->amOnPage($this->downloadUrl($this->publicPost));

		$I->assertSame(200, $I->grabResponseCode(),
			'A public forum attachment must still download. Body: '.self::excerpt($I->grabResponseBody()));
		$I->assertStringContainsString(self::PUBLIC_FILE, $I->grabResponseBody(),
			'The download route did not hand back the public attachment.');
	}

	/**
	 * A member who holds the restricted forum's class may read the thread, so
	 * the attachments on it are theirs to read too. This is the control that
	 * stops the fix being "refuse everything under the attachments directory".
	 */
	public function aMemberOfTheRestrictedForumStillGetsItsAttachments(AcceptanceTester $I)
	{
		$I->wantTo('serve a restricted forum attachment to a member of that forum');

		$I->loginToForum('attachcarol');

		$I->amOnPage($this->downloadUrl($this->restrictedPost));

		$I->assertSame(200, $I->grabResponseCode(),
			'A member of the forum must still be able to download its attachments. Body: '
			.self::excerpt($I->grabResponseBody()));
		$I->assertStringContainsString(self::SECRET_FILE, $I->grabResponseBody(),
			'The download route refused a member who holds the forum class.');

		$I->amOnPage('/thumb.php?src='.rawurlencode($this->thumbSrc(self::SECRET_IMAGE_FILE)).'&w=13');

		$I->assertSame(200, $I->grabResponseCode(),
			'A member of the forum must still see its image attachments. Body: '
			.self::excerpt($I->grabResponseBody()));
		$I->assertSame(self::SECRET_IMAGE, self::readSentinel($I->grabResponseBody()),
			'The thumbnailer refused a member who holds the forum class.');
	}

	/**
	 * What the page emits has to keep pointing at code that can say no. Neither
	 * the raw media path nor anything else that bypasses a permission check may
	 * appear in the rendered thread.
	 */
	public function theRenderedThreadLinksAttachmentsThroughARoute(AcceptanceTester $I)
	{
		$I->wantTo('render attachment links that go through a permission check');

		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);

		$body = $I->grabResponseBody();

		$I->assertStringContainsString('public carrier', $body,
			'The public thread did not render, so its attachment markup proves nothing.');
		$I->assertStringContainsString('id='.$this->publicPost.'&amp;dl=0', $body,
			'The rendered post does not link its file attachment through the download route.');
		$I->assertStringNotContainsString($this->dir, $body,
			'The rendered post published the raw filesystem path of an attachment.');
	}

	/**
	 * A public forum attachment is the same bytes for everybody, so the answer
	 * must stay a cacheable one. sendHeaders() drops the year-long Expires and
	 * declares the response no-store the moment the thumbnailer decides the
	 * answer depends on who asked, and deciding that for every attachment would
	 * take the busiest image path a forum has out of every shared cache.
	 */
	public function aPublicForumAttachmentStaysCacheable(AcceptanceTester $I)
	{
		$I->wantTo('keep a public forum attachment cacheable');

		$I->amOnPage('/thumb.php?src='.rawurlencode($this->thumbSrc(self::PUBLIC_IMAGE_FILE)).'&w=13');

		$I->assertSame(self::PUBLIC_IMAGE, self::readSentinel($I->grabResponseBody()),
			'The public attachment was not served, so its headers prove nothing.');

		$control = (string) $I->grabHttpHeader('Cache-Control');

		$I->assertStringNotContainsString('no-store', $control,
			'A public forum attachment answered no-store, so nothing may cache it: '.$control);
		$I->assertNotSame('', (string) $I->grabHttpHeader('Expires'),
			'A public forum attachment lost its Expires header.');

		// The other half, so the assertion above is not satisfied by the
		// thumbnailer having stopped marking anything caller-dependent.
		$I->loginToForum('attachcarol');

		$I->amOnPage('/thumb.php?src='.rawurlencode($this->thumbSrc(self::SECRET_IMAGE_FILE)).'&w=13');

		$I->assertSame(self::SECRET_IMAGE, self::readSentinel($I->grabResponseBody()),
			'The member of the restricted forum was refused, so its headers prove nothing.');
		$I->assertStringContainsString('no-store', (string) $I->grabHttpHeader('Cache-Control'),
			'A restricted forum attachment was handed out as a cacheable response.');
	}

	// ------------------------------------------------------------------
	// The defect.
	// ------------------------------------------------------------------

	/**
	 * The bytes, straight off the web server. No session, no userclass, no
	 * forum: e107 ships no deny rule for e107_media, so this request never
	 * reaches PHP at all.
	 */
	public function theRestrictedAttachmentIsNotFetchableByItsRawPath(AcceptanceTester $I)
	{
		$I->wantTo('refuse a restricted forum attachment fetched by its raw path');

		$failures = array();

		foreach(array(self::SECRET_IMAGE_FILE => self::SECRET_IMAGE,
			self::SECRET_TEXT_FILE => self::SECRET_FILE) as $file => $secret)
		{
			$I->amOnPage('/'.$this->dir.$file);

			$failures = self::collect($failures,
				$this->disclosureFailure($I, 'GET /'.$this->dir.$file, $secret));
		}

		$this->seeNoFailures($I, $failures, 'raw attachment paths');
	}

	/**
	 * The same bytes through thumbUrl()'s endpoint, which is what the shortcode
	 * actually emits for an image attachment. thumb.php serves e_MEDIA and has
	 * never known that part of it belongs to a forum.
	 */
	public function theRestrictedAttachmentIsNotServedByTheThumbnailer(AcceptanceTester $I)
	{
		$I->wantTo('refuse a restricted forum attachment through thumb.php');

		$src = $this->thumbSrc(self::SECRET_IMAGE_FILE);

		$failures = array();

		$I->amOnPage('/thumb.php?src='.rawurlencode($src).'&w=13');

		$failures = self::collect($failures,
			$this->thumbnailFailure($I, 'thumb.php?w=13', self::SECRET_IMAGE));

		// The full-size link behind the thumbnail, in the shape the page
		// publishes it. thumbUrl($file, 'w=0&x=1', true) does not emit an "x"
		// parameter at all: e_parse::thumbUrl() reads a non-empty x as "cloak
		// this", and rewrites the whole query to a base64 id=.
		$I->amOnPage('/thumb.php?id='.rawurlencode(base64_encode('src='.$src.'&w=0&h=0')));

		$failures = self::collect($failures,
			$this->thumbnailFailure($I, 'thumb.php?id=', self::SECRET_IMAGE));

		// The 0.8 endpoint is a shim over the same class, and this programme
		// exists because a fix landed at one call site while a sibling stayed
		// open. Its grammar is positional and its source is docroot relative.
		$legacy = $this->dir.self::SECRET_IMAGE_FILE;

		foreach(array('+100', '+100+noscale') as $parms)
		{
			$I->amOnPage('/e107_images/thumb.php?'.$legacy.$parms);

			$failures = self::collect($failures,
				$this->thumbnailFailure($I, 'e107_images/thumb.php?'.$parms, self::SECRET_IMAGE));
		}

		$this->seeNoFailures($I, $failures, 'thumbnail requests');
	}

	/**
	 * The one route that already asks. Pinned so a later change cannot quietly
	 * take the check out again.
	 */
	public function theRestrictedAttachmentIsNotServedByTheDownloadRoute(AcceptanceTester $I)
	{
		$I->wantTo('refuse a restricted forum attachment through the download route');

		$I->amOnPage($this->downloadUrl($this->restrictedPost));

		$I->assertStringNotContainsString(self::SECRET_FILE, $I->grabResponseBody(),
			'The download route handed a restricted attachment to a guest.');
	}

	/**
	 * A signed-in member who does not hold the forum's class is in exactly the
	 * position the guest is in. Asserted separately because a fix that keys off
	 * "is there a session" rather than "may this caller read that forum" passes
	 * the guest cases and fails this one.
	 */
	public function aMemberWithoutTheForumClassIsRefusedEveryRoute(AcceptanceTester $I)
	{
		$I->wantTo('refuse a restricted forum attachment to a member outside the class');

		$I->loginToForum('attachdave');

		// Without this the sign-in is unobserved: this Cest runs under
		// stopFollowingRedirects(), so the login answers 302 and nothing after
		// it would notice a refusal. Every assertion below would then be the
		// guest test again, which already passes.
		$I->assertSame((string) $this->outsider, self::env($this->probe($I, 'whoami'), 'USERID'),
			'attachdave is not signed in, so this test is the guest test over again.');

		$failures = array();

		$I->amOnPage('/'.$this->dir.self::SECRET_IMAGE_FILE);
		$failures = self::collect($failures,
			$this->disclosureFailure($I, 'raw path', self::SECRET_IMAGE));

		$I->amOnPage('/'.$this->dir.self::SECRET_TEXT_FILE);
		$failures = self::collect($failures,
			$this->disclosureFailure($I, 'raw path, text', self::SECRET_FILE));

		$I->amOnPage('/thumb.php?src='.rawurlencode($this->thumbSrc(self::SECRET_IMAGE_FILE)).'&w=13');
		$failures = self::collect($failures,
			$this->thumbnailFailure($I, 'thumb.php', self::SECRET_IMAGE));

		$I->amOnPage($this->downloadUrl($this->restrictedPost));
		$failures = self::collect($failures,
			$this->disclosureFailure($I, 'download route', self::SECRET_FILE));

		$this->seeNoFailures($I, $failures, 'routes to the restricted attachment');
	}

	/**
	 * dl= names a key in the post's own attachment list, and a key the post
	 * does not have used to leave the filename empty. The path then resolved to
	 * the poster's directory rather than to a file in it, file_exists() said
	 * yes to the directory, and e107::getFile()->send() was handed it.
	 *
	 * What is asserted is where the answer comes from: the forum's own "no such
	 * attachment" path, and not the file handler's internal bail, which is what
	 * a request that reached send() is answered by.
	 */
	public function aDownloadKeyThePostDoesNotHaveNeverReachesTheFileHandler(AcceptanceTester $I)
	{
		$I->wantTo('refuse a download key the post does not have');

		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->publicPost.'&dl=99');

		$location = (string) $I->grabHttpHeader('Location');

		$I->assertStringNotContainsString(self::PUBLIC_FILE, $I->grabResponseBody(),
			'An out-of-range download key handed back an attachment.');
		$I->assertStringNotContainsString('index.php', $location,
			'The download route gave the poster\'s directory to the file handler, which bailed to '
			.'the site root: '.$location);
		$I->assertStringContainsString('forum', $location,
			'An out-of-range download key was not answered by the forum: '.$location);
	}

	/**
	 * A file in a poster's directory that no post names.
	 *
	 * The forum loses track of files all the time and every one of them was
	 * written by somebody posting into a forum that may be restricted:
	 * renderPreview() uploads and throws the result away, postDeleteAttachments()
	 * nulls the column and only warns when the unlink fails, and a v1 migration
	 * that gets the original moved and the thumbnail not abandons the original
	 * where it stands. The gate cannot say which forum such a file belongs to,
	 * and "cannot say" has to mean no.
	 */
	public function anUnattributedFileUnderTheAttachmentsRootIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('refuse a file under the attachments root that no post names');

		$failures = array();

		$I->amOnPage('/thumb.php?src='.rawurlencode($this->thumbSrc(self::ORPHAN_IMAGE_FILE)).'&w=13');
		$failures = self::collect($failures,
			$this->thumbnailFailure($I, 'thumb.php', self::ORPHAN_IMAGE));

		$I->amOnPage('/e107_images/thumb.php?'.$this->dir.self::ORPHAN_IMAGE_FILE.'+100');
		$failures = self::collect($failures,
			$this->thumbnailFailure($I, 'e107_images/thumb.php', self::ORPHAN_IMAGE));

		$I->amOnPage('/'.$this->dir.self::ORPHAN_IMAGE_FILE);
		$failures = self::collect($failures,
			$this->disclosureFailure($I, 'raw path', self::ORPHAN_IMAGE));

		// The member who owns the directory is refused too. The file is not
		// theirs to read because it is nobody's: no post carries it, so no
		// forum answers for it.
		$I->loginToForum('attachcarol');

		$I->amOnPage('/thumb.php?src='.rawurlencode($this->thumbSrc(self::ORPHAN_IMAGE_FILE)).'&w=14');
		$failures = self::collect($failures,
			$this->thumbnailFailure($I, 'thumb.php, as the owner', self::ORPHAN_IMAGE));

		$this->seeNoFailures($I, $failures, 'routes to an unattributed attachment');
	}

	/**
	 * The forum keeps a second copy of every 0.7/0.8 image attachment, and it
	 * keeps it somewhere else: forum_update::moveAttachment() writes to
	 * e_MEDIA/files/plugins/forum/attachments/thumb/ while everything else in
	 * the plugin uses e_MEDIA/plugins/forum/attachments/. e_MEDIA is a root the
	 * thumbnailer serves, so a gate that knows only the one spelling gates the
	 * original and hands over the copy.
	 */
	public function theMigratedThumbnailTreeIsGatedToo(AcceptanceTester $I)
	{
		$I->wantTo('refuse the copy a v1 migration leaves in the other attachments tree');

		$src = 'e_MEDIA/files/'.self::LEGACY_THUMB_DIR.self::ORPHAN_IMAGE_FILE;

		$failures = array();

		$I->amOnPage('/thumb.php?src='.rawurlencode($src).'&w=13');
		$failures = self::collect($failures,
			$this->thumbnailFailure($I, 'thumb.php', self::ORPHAN_IMAGE));

		$I->amOnPage('/e107_images/thumb.php?'.$this->legacyThumb().'+100');
		$failures = self::collect($failures,
			$this->thumbnailFailure($I, 'e107_images/thumb.php', self::ORPHAN_IMAGE));

		$this->seeNoFailures($I, $failures, 'routes to a migrated thumbnail');
	}

	/**
	 * The upload field is rendered only where the forum's attach preference and
	 * the site's upload_class both allow it, and until now that was the whole
	 * of the test: what consumed $_FILES asked nobody anything. A site whose
	 * administrator had never turned attachments on still took them, from
	 * anybody who could post, into a directory the deny rule is the only cover
	 * for on servers it cannot reach.
	 */
	public function anUploadIsRefusedWhereTheForumDoesNotTakeAttachments(AcceptanceTester $I)
	{
		$I->wantTo('refuse an upload to a forum that does not take attachments');

		$this->probe($I, 'noattach');

		$I->loginToForum('admin', \Helper\AdminLogin::ADMIN_PASS);

		$page = '/e107_plugins/forum/forum_post.php?f=rp&id='.$this->ids['threadA'];
		$token = $I->grabForumToken($page);

		$upload = tempnam(sys_get_temp_dir(), 'p18');
		file_put_contents($upload, self::sentinelPng(self::ORPHAN_IMAGE));

		$I->sendPostRequestWithFiles($page, array(
			'post'    => 'a reply the site never asked for',
			'reply'   => 'Submit',
			'e-token' => $token,
		), array(
			'file_userfile' => array(array(
				'name'     => 'refused.png',
				'type'     => 'image/png',
				'error'    => 0,
				'size'     => filesize($upload),
				'tmp_name' => $upload,
			)),
		));

		@unlink($upload);

		$stored = $this->probe($I, 'attachments', array('entry' => 'a reply the site never asked for'));

		// Anti-vacuity: the reply itself is stored, so the refusal below is
		// about the file and not about the post.
		$I->assertNotSame('', self::env($stored, 'POST'),
			'The reply was not stored at all, so nothing about the attachment was tested.');
		$I->assertSame('', self::env($stored, 'STORED'),
			'A forum that takes no attachments stored one: '.self::env($stored, 'WHY'));
		$I->assertStringNotContainsString('_refused.png', self::env($stored, 'ONDISK'),
			'A forum that takes no attachments wrote one to disk: '.self::env($stored, 'ONDISK'));
	}

	/**
	 * Previewing a reply that carries a file uploads it and discards the
	 * result, so the ordinary use of the ordinary form is one of the things
	 * that populates the set above. Driven for real rather than simulated,
	 * because the claim is about what the shipped form does.
	 */
	public function aPreviewedAttachmentIsNotLeftFetchable(AcceptanceTester $I)
	{
		$I->wantTo('refuse the copy a preview leaves behind');

		$I->loginToForum('admin', \Helper\AdminLogin::ADMIN_PASS);

		$page = '/e107_plugins/forum/forum_post.php?f=rp&id='.$this->ids['threadA'];
		$token = $I->grabForumToken($page);

		$upload = tempnam(sys_get_temp_dir(), 'p18');
		file_put_contents($upload, self::sentinelPng(self::ORPHAN_IMAGE));

		$I->sendPostRequestWithFiles($page, array(
			'post'      => 'a preview carrying an attachment',
			'fpreview'  => 'Preview',
			'e-token'   => $token,
		), array(
			'file_userfile' => array(array(
				'name'     => 'preview.png',
				'type'     => 'image/png',
				'error'    => 0,
				'size'     => filesize($upload),
				'tmp_name' => $upload,
			)),
		));

		@unlink($upload);

		$left = $this->probe($I, 'attachments', array('entry' => 'a preview carrying an attachment'));
		$onDisk = self::env($left, 'ONDISK');

		$I->assertSame('', self::env($left, 'POST'),
			'The preview stored a post, so the copy it left behind is not an orphan after all.');
		$I->assertSame(1, preg_match('~(\d+_1_[0-9a-f]{16}_preview\.png)~', $onDisk),
			'The preview uploaded nothing, so there is no leftover copy to refuse. On disk: '.$onDisk);

		preg_match('~(\d+_1_[0-9a-f]{16}_preview\.png)~', $onDisk, $m);

		$dir = $I->haveForumAttachmentDir(1);

		$I->resetAllCookies();

		$I->amOnPage('/thumb.php?src='
			.rawurlencode('e_MEDIA/'.substr($dir, strpos($dir, 'plugins/')).$m[1]).'&w=13');

		$this->seeNoFailures($I,
			self::collect(array(), $this->thumbnailFailure($I, 'thumb.php', self::ORPHAN_IMAGE)),
			'routes to a previewed attachment');
	}

	/**
	 * post_attachments is the authorisation record both routes read, and it is
	 * written from the request. A registered poster can only name a file in
	 * their own directory, so the forgery confines itself; every guest writes
	 * into one directory, so a guest naming a file names whatever the last
	 * guest put there.
	 */
	public function aGuestCannotAdoptAnotherGuestsAttachment(AcceptanceTester $I)
	{
		$I->wantTo('refuse a guest post that names an attachment it did not upload');

		$page = '/e107_plugins/forum/forum_post.php?f=rp&id='.$this->ids['threadA'];
		$token = $I->grabForumToken($page);

		$I->sendPostRequest($page, array(
			'post'                  => 'a guest reply naming somebody elses file',
			'anonname'              => 'p18guest',
			'reply'                 => 'Submit',
			'e-token'               => $token,
			'post_attachments_json' => json_encode(array(
				'img' => array(array('file' => self::ANON_IMAGE_FILE, 'name' => 'mine.png', 'size' => 1)),
			)),
		));

		$answer = self::messages($I->grabResponseBody());

		$stored = $this->probe($I, 'attachments',
			array('entry' => 'a guest reply naming somebody elses file'));

		// Anti-vacuity: guests really can post here, so a refusal below is
		// about the attachment and not about the post.
		$I->assertNotSame('', self::env($stored, 'POST'),
			'The guest reply was not stored, so nothing was tested. The post answered: '.$answer);
		$I->assertSame('', self::env($stored, 'STORED'),
			'The guest post adopted a file it never uploaded: '.self::env($stored, 'WHY'));

		$I->amOnPage('/thumb.php?src='.rawurlencode('e_MEDIA/'.substr($this->anonDir, strpos($this->anonDir, 'plugins/'))
			.self::ANON_IMAGE_FILE).'&w=13');

		$this->seeNoFailures($I,
			self::collect(array(), $this->thumbnailFailure($I, 'thumb.php', self::ANON_IMAGE)),
			'routes to an adopted guest attachment');
	}

	/**
	 * The gate is in checkSrc() and not in sendImage() so that a cache entry
	 * minted for a member who may read the forum cannot be collected by the
	 * next caller. Nothing else in this Cest can see that, because _before()
	 * empties the cache and every request therefore misses.
	 */
	public function theGeneratedThumbnailCacheIsBehindTheGate(AcceptanceTester $I)
	{
		$I->wantTo('keep the thumbnail cache behind the permission check');

		$url = '/thumb.php?src='.rawurlencode($this->thumbSrc(self::SECRET_IMAGE_FILE)).'&w=17';

		$I->loginToForum('attachcarol');
		$I->amOnPage($url);

		$I->assertSame(self::SECRET_IMAGE, self::readSentinel($I->grabResponseBody()),
			'The member of the forum was refused, so no cache entry exists and the request below '
			.'proves nothing.');

		// No probe call in between: it would empty the cache and take the
		// entry this test exists to ask for away with it.
		$I->resetAllCookies();
		$I->amOnPage($url);

		$this->seeNoFailures($I,
			self::collect(array(), $this->thumbnailFailure($I, 'thumb.php, cached', self::SECRET_IMAGE)),
			'cached thumbnail requests');
	}

	/**
	 * The name is the only thing protecting an attachment on a server whose
	 * configuration the deny rule cannot reach, and it is not a secret:
	 * upload_handler.php builds it from the upload time, the poster's user id
	 * and the name the file was uploaded under. The thread publishes all three.
	 *
	 * Driven through the real form as the main administrator, because
	 * process_uploaded_files() is reached from forum_post.php and nowhere else,
	 * and because getperms('0') is what lets an upload past a stock site's
	 * upload_class of 255.
	 */
	public function theStoredNameOfAnUploadedAttachmentIsNotGuessable(AcceptanceTester $I)
	{
		$I->wantTo('store a forum attachment under a name that cannot be guessed');

		$I->loginToForum('admin', \Helper\AdminLogin::ADMIN_PASS);

		$page = '/e107_plugins/forum/forum_post.php?f=rp&id='.$this->ids['threadA'];
		$token = $I->grabForumToken($page);

		$upload = tempnam(sys_get_temp_dir(), 'p18');
		file_put_contents($upload, self::sentinelPng(self::PUBLIC_IMAGE));

		$I->sendPostRequestWithFiles($page, array(
			'post'    => 'a reply carrying an attachment',
			'reply'   => 'Submit',
			'e-token' => $token,
		), array(
			'file_userfile' => array(array(
				'name'     => 'holiday.png',
				'type'     => 'image/png',
				'error'    => 0,
				'size'     => filesize($upload),
				'tmp_name' => $upload,
			)),
		));

		@unlink($upload);

		$answer = self::messages($I->grabResponseBody());

		$stored = $this->probe($I, 'attachments', array('entry' => 'a reply carrying an attachment'));

		// Without this the assertion below is satisfied by there being no
		// upload at all, which is the shape a vacuous pass takes here.
		$I->assertNotSame('', self::env($stored, 'STORED'),
			'No attachment was stored for the reply, so the stored name proves nothing. '
			.'Probe said: '.self::env($stored, 'WHY').' On disk: '.self::env($stored, 'ONDISK')
			.' The post answered: '.$answer);

		$name = self::env($stored, 'STORED');

		$I->assertSame(1, preg_match('/^\d+_1_[0-9a-f]{16}_holiday\.png$/', $name),
			'The stored attachment name carries no random component, so anybody who knows '
			.'when the post was made, who made it and what the file was called can ask for '
			.'it by name: '.$name);

		// The name in the row has to be the name on the disk, and the file has
		// to come back through the route the thread links. upload_handler.php
		// splits the "+" out of the type it was given to build the stored name,
		// so a divergence between the two would leave every new attachment a
		// dead link and this test still green.
		$I->assertStringContainsString($name, self::env($stored, 'ONDISK'),
			'The stored attachment name names no file on disk. On disk: '.self::env($stored, 'ONDISK'));

		$dir = $I->haveForumAttachmentDir(1);

		$I->amOnPage('/thumb.php?src='.rawurlencode('e_MEDIA/'.substr($dir, strpos($dir, 'plugins/')).$name).'&w=13');

		$I->assertSame(self::PUBLIC_IMAGE, self::readSentinel($I->grabResponseBody()),
			'The attachment that was just uploaded to a public forum does not come back through '
			.'the route the thread links it by.');
	}

	/**
	 * The attachments a site already holds keep the name they were stored
	 * under, so the random component does nothing for them and the serving
	 * routes are not the only way to their bytes. What covers them is a deny
	 * rule over the directory, written by the plugin's own setup hook rather
	 * than by anything a member does.
	 *
	 * The rule is allowed here, and was not allowed for the media library,
	 * because nothing in e107 ever links an attachment by its raw path:
	 * view_shortcodes.php renders an image through thumbUrl() and links a file
	 * through forum_viewtopic.php?id=&dl=, and both of those read the file off
	 * the disk. The last two assertions are the ones that say so, and they are
	 * why this test comes last: it takes the rule away first, and a refusal
	 * asserted without knowing the bytes were reachable a moment earlier would
	 * prove nothing at all.
	 */
	public function theSetupHookCoversTheAttachmentDirectory(AcceptanceTester $I)
	{
		$I->wantTo('cover an existing attachment directory with a deny rule');

		$bare = $this->probe($I, 'unprotect');

		$I->assertSame('0', self::env($bare, 'GUARDED'),
			'The guard files were still in place, so what follows measures nothing.');
		$I->assertSame('0', self::env($bare, 'GUARDED_DIRS'),
			'The posters\' guard files were still in place, so what follows measures nothing.');

		$I->amOnPage('/'.$this->dir.self::PUBLIC_TEXT_FILE);

		$I->assertSame(200, $I->grabResponseCode(),
			'Without the deny rule the web server must hand the file over, or the refusal '
			.'below is about something else entirely.');

		$covered = $this->probe($I, 'setup');

		$I->assertSame('1', self::env($covered, 'GUARDED'),
			'The forum setup hook did not write the guard files.');

		// The deny rule at the root covers everything below it on Apache and
		// nothing at all on a server with its own configuration language, where
		// the blank index.html is what stops a directory listing publishing
		// every attachment name a poster has.
		$I->assertSame('1', self::env($covered, 'GUARDED_DIRS'),
			'The setup hook left the posters\' own directories without guard files, so a server '
			.'with autoindex on lists every attachment name in them.');

		$I->amOnPage('/'.$this->dir.self::PUBLIC_TEXT_FILE);

		$I->assertSame(403, $I->grabResponseCode(),
			'The deny rule did not stop the web server handing out an attachment.');

		$I->amOnPage('/thumb.php?src='.rawurlencode($this->thumbSrc(self::PUBLIC_IMAGE_FILE)).'&w=13');

		$I->assertSame(self::PUBLIC_IMAGE, self::readSentinel($I->grabResponseBody()),
			'The deny rule took a public forum attachment off the site.');

		$I->amOnPage($this->downloadUrl($this->publicPost));

		$I->assertStringContainsString(self::PUBLIC_FILE, $I->grabResponseBody(),
			'The deny rule stopped the download route reading its own attachment.');
	}

	// ------------------------------------------------------------------
	// Assertions
	// ------------------------------------------------------------------

	/**
	 * Why the last response counts as a disclosure of $secret, or '' when it
	 * does not.
	 *
	 * Returned rather than asserted so one test can put several requests and
	 * report on all of them. An assertion inside the loop stops at the first
	 * leak and silently skips the rest.
	 *
	 * @param AcceptanceTester $I
	 * @param string $label
	 * @param string $secret
	 * @return string
	 */
	private function disclosureFailure(AcceptanceTester $I, $label, $secret)
	{
		$body = $I->grabResponseBody();

		if($secret === '')
		{
			return $label.': a disclosure check with nothing to look for proves nothing.';
		}

		if(strpos($body, $secret) !== false)
		{
			return $label.': returned the contents of the file, byte for byte. Status '
				.$I->grabResponseCode().'.';
		}

		if(self::readSentinel($body) === $secret)
		{
			return $label.': returned the file re-encoded as an image. Status '
				.$I->grabResponseCode().'.';
		}

		return '';
	}

	/**
	 * Containment for a thumbnail request. A refusal cannot have produced a
	 * raster out of the source, and the placeholder a missing image gets is an
	 * SVG, so a raster coming back at all is a disclosure however it is spelled.
	 *
	 * @param AcceptanceTester $I
	 * @param string $label
	 * @param string $secret
	 * @return string
	 */
	private function thumbnailFailure(AcceptanceTester $I, $label, $secret)
	{
		$failure = $this->disclosureFailure($I, $label, $secret);

		if($failure !== '')
		{
			return $failure;
		}

		if($I->grabResponseCode() >= 500)
		{
			return $label.': answered '.$I->grabResponseCode().'; a refused source must not fatal. '
				.'Body: '.self::excerpt($I->grabResponseBody());
		}

		if(self::rasterType($I->grabResponseBody()) !== false)
		{
			return $label.': answered with a raster image, so it read and decoded the source.';
		}

		return '';
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

	// ------------------------------------------------------------------
	// Requests
	// ------------------------------------------------------------------

	/**
	 * The migrated-thumbnail copy, in the tree forum_update.php writes it to,
	 * relative to the app root.
	 *
	 * Derived from the poster's directory the application reported, so the
	 * site path is the running site's own.
	 *
	 * @return string
	 */
	private function legacyThumb()
	{
		$media = substr($this->dir, 0, strpos($this->dir, 'plugins/'));

		return $media.'files/'.self::LEGACY_THUMB_DIR.self::ORPHAN_IMAGE_FILE;
	}

	/**
	 * The attachment as thumbUrl() spells it. Built from the directory the
	 * application reported rather than from a guess at the site path.
	 *
	 * @param string $file
	 * @return string
	 */
	private function thumbSrc($file)
	{
		$tail = strpos($this->dir, 'plugins/');

		if($tail === false)
		{
			throw new \RuntimeException('Unexpected attachment directory: '.$this->dir);
		}

		return 'e_MEDIA/'.substr($this->dir, $tail).$file;
	}

	/**
	 * @param int $postId
	 * @return string
	 */
	private function downloadUrl($postId)
	{
		return '/e107_plugins/forum/forum_viewtopic.php?id='.$postId.'&dl=0';
	}

	// ------------------------------------------------------------------
	// The probe
	// ------------------------------------------------------------------

	/**
	 * @param AcceptanceTester $I
	 * @param string $act
	 * @param array $params
	 * @return array probe output, keyed by the names the probe prints
	 */
	private function probe(AcceptanceTester $I, $act, $params = array())
	{
		$query = http_build_query(array_merge(array('act' => $act), $params));

		$I->amOnPage('/'.self::PROBE.'?'.$query);

		$body = $I->grabResponseBody();

		if(strpos($body, 'P18PROBE_OK') === false)
		{
			throw new \RuntimeException('P18 probe failed for "'.$act.'": '.trim(strip_tags($body)));
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
	 * @param array $env
	 * @param string $key
	 * @return string
	 */
	private static function env(array $env, $key)
	{
		return isset($env[$key]) ? $env[$key] : '';
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for 0047_ForumAttachmentServingCest. Removed again in the Cest's _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');

$act = isset($_GET['act']) ? $_GET['act'] : '';

if($act === 'reset' || $act === 'cleanup')
{
	e107::getDb()->delete('online');
	e107::getDb()->delete('banlist', 'banlist_bantype IN (2, -2)');

	foreach(glob(e_CACHE_IMAGE.'*') ?: array() as $file)
	{
		if(is_file($file))
		{
			@unlink($file);
		}
	}
}

if($act === 'reset')
{
	// What install.php::saveFileTypes() writes, verbatim. Without it
	// get_filetypes() answers with an empty array, set_max_size() then reports
	// a maximum upload size of zero, and every upload is refused as too large:
	// a site that cannot accept an attachment at all would let the naming test
	// pass for having nothing to name. The suite installs twice and the second
	// install writes its own e107_config.php, so e_SYSTEM moves to a directory
	// the first install never wrote this into.
	//
	// The marker records that this file is the probe's, so that act=cleanup
	// takes it away again: it sets the allowed extensions and the maximum
	// upload size for every request that follows, and a security test has no
	// business configuring the site the next Cest runs against.
	if(!is_readable(e_SYSTEM.'filetypes.xml'))
	{
		file_put_contents(e_SYSTEM.'filetypes.xml', '<?xml version="1.0" encoding="utf-8"?>
<e107Filetypes>
	<class name="member" type="zip,gz,jpg,jpeg,png,gif,webp,xml,pdf" maxupload="2M" />
	<class name="admin" type="zip,gz,jpg,jpeg,png,gif,webp,xml,pdf" maxupload="10M" />
	<class name="main" type="zip,gz,rar,jpg,jpeg,png,gif,webp,xml,pdf,ppt,pptx,mov,mp4,mp3,doc,docx,xls,xlsm,mp3,mp4,wav,ogg,webm,mid,midi,torrent,txt,dmg,msi" maxupload="50M" />
</e107Filetypes>');
		file_put_contents(e_SYSTEM.'p18_filetypes.marker', '1');
	}

	// The forum takes an upload only where its own preference says it may, so
	// a test that drives the real form has to turn the feature on the way an
	// administrator would.
	e107::getPlugConfig('forum')->set('attach', 1)->save(false, true, false);
}

if($act === 'cleanup')
{
	foreach(glob(e_MEDIA.'plugins/forum/attachments/*/*') ?: array() as $file)
	{
		if(preg_match('/(^p18_|_(preview|holiday|refused)\.png$)/', basename($file)))
		{
			@unlink($file);
		}
	}

	@unlink(e_MEDIA.'files/plugins/forum/attachments/thumb/p18_orphan.png');

	// The forum takes attachments only where an administrator said so, and
	// this probe is the one that said so.
	e107::getPlugConfig('forum')->remove('attach')->save(false, true, false);

	foreach(array('a reply carrying an attachment', 'a preview carrying an attachment',
		'a guest reply naming somebody elses file') as $entry)
	{
		e107::getDb()->delete('forum_post', "post_entry = '".addslashes($entry)."'");
	}

	if(is_readable(e_SYSTEM.'p18_filetypes.marker'))
	{
		@unlink(e_SYSTEM.'filetypes.xml');
		@unlink(e_SYSTEM.'p18_filetypes.marker');
	}
}

if($act === 'whoami')
{
	echo "USERID=".USERID."\n";
}

if($act === 'noattach')
{
	e107::getPlugConfig('forum')->set('attach', 0)->save(false, true, false);
}

if($act === 'unprotect' || $act === 'setup')
{
	$root = e_MEDIA.'plugins/forum/attachments/';
	$posters = glob($root.'*', GLOB_ONLYDIR) ?: array();

	if($act === 'unprotect')
	{
		foreach(array_merge(array(rtrim($root, '/')), $posters) as $dir)
		{
			@unlink($dir.'/.htaccess');
			@unlink($dir.'/index.html');
		}
	}
	else
	{
		require_once(e_PLUGIN.'forum/forum_setup.php');
		$setup = new forum_setup();
		$setup->install_post(array());
	}

	$dirsGuarded = ($posters !== array());

	foreach($posters as $dir)
	{
		if(!is_file($dir.'/.htaccess') || !is_file($dir.'/index.html'))
		{
			$dirsGuarded = false;
		}
	}

	echo "GUARDED=".((is_file($root.'.htaccess') && is_file($root.'index.html')) ? '1' : '0')."\n";
	echo "GUARDED_DIRS=".($dirsGuarded ? '1' : '0')."\n";
}

if($act === 'attachments')
{
	// The name of the first stored attachment on the post whose body is
	// $_GET['entry'], read out of the row the application wrote.
	$sql = e107::getDb();
	$row = array();

	if($sql->select('forum_post', 'post_id, post_attachments',
		"post_entry='".$sql->escape((string) $_GET['entry'])."' LIMIT 1"))
	{
		$row = $sql->fetch();
	}

	$stored = '';
	$why = 'no post with that body';
	$postId = '';

	if(!empty($row))
	{
		$postId = $row['post_id'];
		$why = 'post '.$row['post_id'].' stored post_attachments='.var_export($row['post_attachments'], true);
		$attachments = e107::unserialize($row['post_attachments']);

		if(is_array($attachments))
		{
			foreach($attachments as $entries)
			{
				foreach((array) $entries as $entry)
				{
					$name = is_array($entry) ? varset($entry['file'], '') : $entry;

					if($name !== '' && $stored === '')
					{
						$stored = $name;
					}
				}
			}
		}
	}

	$onDisk = array();

	foreach(glob(e_MEDIA.'plugins/forum/attachments/*/*') ?: array() as $file)
	{
		$onDisk[] = basename(dirname($file)).'/'.basename($file);
	}

	echo "POST=".$postId."\n";
	echo "STORED=".$stored."\n";
	echo "WHY=".str_replace(array("\r", "\n"), ' ', $why)."\n";
	echo "ONDISK=".implode(' ', $onDisk)."\n";
}

echo "P18PROBE_OK\n";
PHP;
	}

	// ------------------------------------------------------------------
	// Fixtures
	// ------------------------------------------------------------------

	/**
	 * A PNG whose pixel row spells $text, with $text repeated after IEND.
	 *
	 * Two channels on purpose. GD drops everything after IEND, so the trailing
	 * copy comes back only from a route that hands the file over unaltered; the
	 * pixel copy survives a lossless PNG round trip through GD, so it comes
	 * back from the route that re-encodes. Greyscale so one channel carries the
	 * byte.
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
	 * Not getimagesizefromstring() on its own: its WBMP branch is a heuristic
	 * over arbitrary bytes rather than a magic number test, and it reports the
	 * thumbnailer's own SVG placeholder as a 1 pixel WBMP.
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

		if($width !== strlen(self::SECRET_IMAGE)) // both image sentinels are this long
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
	/**
	 * Whatever the page said in an alert, so a refused upload reports the
	 * reason it was refused rather than the first 200 bytes of the doctype.
	 *
	 * @param string $body
	 * @return string
	 */
	private static function messages($body)
	{
		if(!preg_match_all('#<div[^>]*class="[^"]*alert[^"]*"[^>]*>(.*?)</div>#s', $body, $m))
		{
			return self::excerpt($body);
		}

		$said = array();

		foreach($m[1] as $block)
		{
			$text = trim(preg_replace('/\s+/', ' ', strip_tags($block)));

			if($text !== '')
			{
				$said[] = $text;
			}
		}

		return $said ? implode(' | ', $said) : self::excerpt($body);
	}

	private static function excerpt($body)
	{
		$text = preg_replace('/[^\x20-\x7e]+/', '.', substr($body, 0, 200));

		return strlen($body) > 200 ? $text.' [...]' : $text;
	}
}
