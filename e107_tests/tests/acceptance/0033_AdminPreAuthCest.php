<?php

/**
 * The pre-authentication window in the admin dispatcher, and the unauthenticated
 * remote code execution it composes into.
 *
 * Every admin entry point in the product, core and plugin alike, is written as
 * "new X_admin(); require_once(e_ADMIN.'auth.php');". The dispatcher constructor
 * reaches runObservers(), which reaches _initController(), which runs the
 * controller's init(). A controller init() therefore runs on an unauthenticated
 * request, on every admin page there is, and only afterwards does the page ask
 * who is calling.
 *
 * media_admin_ui::init() read $_POST with no action check, no token check and
 * no ADMIN check, and one of the three handlers it reached wrote the core
 * preferences im_path and resize_method; e_admin_ui::PrefsSaveTrigger(), which
 * runs from the same constructor, writes every posted key into the core
 * preference set whether it is declared in $prefs or not.
 * resize_handler.php then concatenates
 * im_path raw into the ImageMagick command line, next to arguments that are
 * carefully intval()'d and escapeshellarg()'d, and e107_images/thumb.php lets
 * anyone at all fire that command. That is the whole chain: an unauthenticated
 * POST, followed by an unauthenticated GET, and the web account is executing
 * whatever the attacker wrote.
 *
 * There is no CSRF obstacle. e_core_session::hasAmbientAuthority() asks whether
 * the request carried any cookie, and a cookieless request is exempt from the
 * token check by design. That policy is right; it simply means the token was
 * never an authentication barrier.
 *
 * Every refusal asserted here is read back as a side effect, never off the
 * rendered page. e_admin_dispatcher::checkAccess() rewrites the action to e403
 * and then constructs the controller and runs its init() anyway, so a page can
 * say "Access Denied" in perfectly good faith while the unauthorised write it
 * was asked for has already landed.
 *
 * @see e107_handlers/admin_ui.php  e_admin_dispatcher::__construct(), _initController()
 * @see e107_admin/image.php        media_admin_ui::init(), updateSettings()
 * @see e107_handlers/resize_handler.php  resize_image()
 * @see e107_images/thumb.php
 */
class AdminPreAuthCest
{
	const PROBE_FILE = 'e107_tests_preauth_probe.php';

	/**
	 * Written by the payload, from inside the web container, as the web
	 * account. Registered in Extension\WorkspaceCleanup so a crashed run does
	 * not leave it in the docroot.
	 */
	const CANARY_FILE = 'e107_tests_preauth_canary.txt';

	/** Ships with the gallery plugin and is a real JPEG, so thumb.php resizes it. */
	const SOURCE_IMAGE = 'e107_plugins/gallery/images/butterfly.jpg';

	/** The route e_form::mediaUrl() builds for every image and file picker. */
	const DIALOG_ROUTE = '/e107_admin/image.php?mode=main&action=dialog';

	/** The same page, on a route image.php:26 does not exempt. */
	const GATED_ROUTE = '/e107_admin/image.php?mode=main&action=prefs';

	/** Bundled plugin the fixtures below are installed into and attack. */
	const PLUGIN = 'faqs';

	const PLUGIN_ROUTE = '/e107_plugins/faqs/admin_config.php?mode=main&action=prefs';

	/**
	 * Stands in for the third party plugin admin page that every bundled entry
	 * point used to be: it constructs a dispatcher and only afterwards requires
	 * auth.php, and it carries no permission gate of its own.
	 *
	 * The bundled pages all gate themselves now, so without this fixture no
	 * test on the branch would fail if e_admin_dispatcher::__construct() lost
	 * its gate, which is the one place the whole class of defect is closed.
	 */
	const DISPATCHER_FIXTURE = 'e107_plugins/faqs/admin_e107_tests_preauth_dispatcher.php';

	/**
	 * The same fixture under a basename e107::inAdminDir() does not recognise,
	 * so e_ADMIN_AREA is false for it. A gate that keys on e_ADMIN_AREA is
	 * keying authentication on a filename, and this is the file that says so.
	 */
	const DISPATCHER_FIXTURE_UNRECOGNISED = 'e107_plugins/faqs/e107_tests_preauth_dispatcher.php';

	/**
	 * A faqs preference that is itself an access control: the dispatcher's
	 * init() copies it into $access['main/create'].
	 */
	const PLUGIN_PREF = 'admin_faq_create';

	const PLUGIN_PREF_SAFE = 254;   // e_UC_ADMIN
	const PLUGIN_PREF_ATTACK = 0;   // e_UC_PUBLIC

	/**
	 * Signed post-login destination, written by redirect_class::go('admin').
	 *
	 * @see e107_handlers/redirection_class.php  redirection::LOGIN_DEST_COOKIE
	 */
	const LOGIN_DEST_COOKIE = 'e107_logindest';

	/** Emitted by e_form::tabs() for the media manager's tab container. */
	const MEDIA_MANAGER_MARKER = 'admin-ui-media-manager';

	/** Emitted by media_admin_ui::uploadTab(), which needs 'A' or 'A1'. */
	const UPLOAD_TAB_MARKER = '<div id="uploader"';

	/** Delegated administrators seeded per test: login name => user_perms. */
	const DELEGATED_ADMINS = array(
		'preauth_a1_admin' => 'A1',
		'preauth_h_admin'  => 'H',
		'preauth_l_admin'  => 'L',
	);

	/** @var bool whether this run installed the plugin and owes an uninstall */
	private $pluginInstalled = false;

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->startFollowingRedirects();
		$this->reset($I);
	}

	public function _after(AcceptanceTester $I)
	{
		$I->deleteAppFile(self::DISPATCHER_FIXTURE);
		$I->deleteAppFile(self::DISPATCHER_FIXTURE_UNRECOGNISED);

		if($this->pluginInstalled)
		{
			$I->dropPluginInstall(self::PLUGIN);
			$I->dropPluginProbe();
			$this->pluginInstalled = false;
		}

		$I->startFollowingRedirects();
		$this->reset($I);

		$I->deleteAppFile(self::PROBE_FILE);
		$I->deleteAppFile(self::CANARY_FILE);
	}

	// -----------------------------------------------------------------
	// the write, on its own
	// -----------------------------------------------------------------

	/**
	 * An unauthenticated POST to a core admin dispatcher page must not write a
	 * core preference.
	 *
	 * This is the tightest statement of the defect: no shell, no thumbnailer,
	 * nothing but "did the write land". The route has to be action=dialog,
	 * because image.php turns a guest away on every other action and used to
	 * exempt this one.
	 *
	 * Two payloads, and they are not the same kind of statement.
	 *
	 * The first is the live writer: e_admin_ui::PrefsSaveTrigger() runs from
	 * dispatchObserver() inside the dispatcher constructor and writes every
	 * posted key into the core preference set, not merely the keys declared in
	 * media_admin_ui::$prefs. anAdministratorCanStillSaveTheMediaPreferences()
	 * is its positive control: same route, same trigger, same preference, an
	 * authenticated administrator, and there the write lands. That pairing is
	 * what makes this an authorisation assertion rather than a statement that
	 * nothing was listening.
	 *
	 * The second is a regression guard and nothing more. media_admin_ui::
	 * updateSettings() fired on the bare presence of update_options in $_POST
	 * and has been deleted; no control is possible for a handler that no longer
	 * exists, and this half would pass against a tree where the whole page had
	 * been removed. It is here so that re-adding the handler turns something
	 * red.
	 *
	 * The response code is asserted as well, so a failure says whether the
	 * guest was actively turned away or merely served something inert.
	 */
	public function guestCannotWriteCorePreferencesThroughTheDialogRoute(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an unauthenticated core preference write on e107_admin/image.php');

		$before = $this->dump($I);

		$I->resetAllCookies();
		$token = $this->grabGuestToken($I);

		$I->stopFollowingRedirects();
		$I->sendPostRequest(self::DIALOG_ROUTE, array(
			'etrigger_save' => 'Save Settings',
			'resize_method' => 'ImageMagick',
			'im_path'       => '/tmp/attacker/',
			'e-token'       => $token,
		));

		$I->assertSame(301, $I->grabResponseCode(),
			'The guest POST to '.self::DIALOG_ROUTE.' was not refused by the page permission gate. '
			.'A 200 here means the request reached the page and was turned away by something else, '
			.'most likely the token check, which is not an authorisation boundary.');

		$afterTrigger = $this->dump($I);

		$I->assertSame($before['resize_method'], $afterTrigger['resize_method'],
			'An unauthenticated POST to '.self::DIALOG_ROUTE.' rewrote the core preference resize_method. '
			.'Was '.var_export($before['resize_method'], true).', is now '
			.var_export($afterTrigger['resize_method'], true).'.');

		$I->assertSame($before['im_path'], $afterTrigger['im_path'],
			'An unauthenticated POST to '.self::DIALOG_ROUTE.' rewrote the core preference im_path. '
			.'Was '.var_export($before['im_path'], true).', is now '
			.var_export($afterTrigger['im_path'], true).'.');

		$I->resetAllCookies();
		$I->sendPostRequest(self::DIALOG_ROUTE, $this->updateOptionsPayload('/tmp/attacker/', 'ImageMagick'));

		$after = $this->dump($I);

		$I->assertSame($before['im_path'], $after['im_path'],
			'media_admin_ui::updateSettings() is back: an unauthenticated POST carrying update_options to '
			.self::DIALOG_ROUTE.' rewrote the core preference im_path. Was '
			.var_export($before['im_path'], true).', is now '.var_export($after['im_path'], true).'.');

		$I->assertSame($before['resize_method'], $after['resize_method'],
			'media_admin_ui::updateSettings() is back: an unauthenticated POST carrying update_options to '
			.self::DIALOG_ROUTE.' rewrote the core preference resize_method. Was '
			.var_export($before['resize_method'], true).', is now '.var_export($after['resize_method'], true).'.');
	}

	/**
	 * The property the dispatcher gate exists for, on the population it exists
	 * for: an admin entry point that constructs a dispatcher and has no
	 * permission gate of its own.
	 *
	 * Every bundled entry point carries a getperms() line now, so every other
	 * negative test on this branch is satisfied by a page-local gate and would
	 * stay green if e_admin_dispatcher::__construct() lost its own. Two decades
	 * of third party plugin admin pages are the population that has no such
	 * line, and this fixture is one of them: it boots class2.php, declares a
	 * controller whose init() writes a preference, constructs the dispatcher,
	 * and only then requires auth.php. That is the shape every page in the
	 * product had before this branch.
	 *
	 * The fixture is written twice under two names. e107::inAdminDir()
	 * recognises the "admin_" prefix and not the other, so the second copy runs
	 * with e_ADMIN_AREA false and pins that the gate does not decide whether to
	 * authenticate by looking at a filename.
	 *
	 * Each half carries its own positive control in the same method: the same
	 * POST from a main administrator has to move the preference, or "the guest
	 * was refused" would be indistinguishable from "the fixture never wrote
	 * anything".
	 */
	public function guestCannotReachAControllerThroughAnUngatedAdminDispatcher(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an unauthenticated request to an admin entry point that has no gate of its own');

		$this->installPlugin($I);
		$this->reset($I);

		$I->writeAppFile(self::DISPATCHER_FIXTURE, $this->dispatcherFixtureSource());
		$I->writeAppFile(self::DISPATCHER_FIXTURE_UNRECOGNISED, $this->dispatcherFixtureSource());

		$fixtures = array(
			'e_ADMIN_AREA true'  => self::DISPATCHER_FIXTURE,
			'e_ADMIN_AREA false' => self::DISPATCHER_FIXTURE_UNRECOGNISED,
		);

		foreach($fixtures as $shape => $fixture)
		{
			$this->reset($I);

			$I->resetAllCookies();
			$I->stopFollowingRedirects();
			$I->sendPostRequest('/'.$fixture, array());

			$guestCode = $I->grabResponseCode();
			$body = $I->grabPageSource();
			$after = $this->dump($I);

			$I->assertSame((string) self::PLUGIN_PREF_SAFE, (string) $after['plugin_pref'],
				'An unauthenticated POST to /'.$fixture.' ('.$shape.') ran the controller\'s init() and wrote '
				.self::PLUGIN_PREF.' = '.var_export($after['plugin_pref'], true).'. The page has no permission '
				.'gate of its own, so e_admin_dispatcher::__construct() is the only thing standing between a '
				.'guest and every third party plugin admin page in the product.');

			$I->assertSame(301, $guestCode,
				'/'.$fixture.' ('.$shape.') answered a guest with HTTP '.$guestCode
				.' instead of turning them away. First 300 bytes: '.var_export(substr($body, 0, 300), true));
		}

		// Positive control: the fixture is a working writer, so a refusal above
		// is a refusal and not an inert page.
		$I->startFollowingRedirects();
		$I->loginAsAdmin();
		$I->dontSeeElement('input[name=authpass]');

		foreach($fixtures as $shape => $fixture)
		{
			$this->reset($I);

			// The administrator's POST carries a cookie, so it is attested and
			// needs a token. The guest's did not: a cookieless request has no
			// ambient authority to forge with and is exempt by design, which is
			// exactly why the token was never the barrier here.
			$token = $I->grabFreshAdminToken(self::GATED_ROUTE);

			$I->sendPostRequest('/'.$fixture, array('e-token' => $token));

			$after = $this->dump($I);

			$I->assertSame((string) self::PLUGIN_PREF_ATTACK, (string) $after['plugin_pref'],
				'/'.$fixture.' ('.$shape.') did not write '.self::PLUGIN_PREF.' for a main administrator, '
				.'so the refusals asserted above prove nothing: the fixture writes nothing for anybody. '
				.'It is '.var_export($after['plugin_pref'], true).'.');
		}
	}

	/**
	 * The same defect on a bundled plugin, because the window is in the
	 * dispatcher and not in image.php.
	 *
	 * faqs/admin_config.php carries no permission gate of its own, so a guest
	 * reaches the dispatcher. e_admin_ui::PrefsSaveTrigger() then runs from
	 * dispatchObserver(), inside the constructor, and writes the plugin's
	 * preferences. The preference chosen here is one the plugin's own dispatcher
	 * reads back as $access['main/create'], so an unauthenticated write to it is
	 * an unauthenticated grant of a permission.
	 *
	 * This one presents a CSRF token, and a token is not hard to come by: the
	 * request is the attacker's own, so they browse to the front page, take the
	 * token this site handed them, and post it back. That is what separates
	 * authorisation from forgery protection. Doing it the other way, cookieless,
	 * would prove nothing here for a reason that has nothing to do with
	 * permissions: csrf_handler.php back-fills $_COOKIE with the CSRF cookie it
	 * has just minted, so by the time the trigger asks
	 * e_core_session::hasAmbientAuthority() the request is no longer cookieless
	 * and is refused as unattested. See the report accompanying this branch.
	 *
	 * The plugin is installed through the plugin manager first. A request to an
	 * absent plugin is turned away because it is absent, which is
	 * indistinguishable from the refusal this test believes it is proving; the
	 * positive control below is what makes the difference visible.
	 */
	public function guestCannotWritePluginPreferencesThroughAnAdminDispatcher(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an unauthenticated plugin preference write on a bundled plugin admin page');

		$this->installPlugin($I);
		$this->reset($I);

		$before = $this->dump($I);
		$I->assertSame((string) self::PLUGIN_PREF_SAFE, (string) $before['plugin_pref'],
			'Fixture failed: the '.self::PLUGIN.' preference '.self::PLUGIN_PREF.' should start at '
			.self::PLUGIN_PREF_SAFE.', not '.var_export($before['plugin_pref'], true).'.');

		$I->resetAllCookies();
		$token = $this->grabGuestToken($I);

		$I->stopFollowingRedirects();
		$I->sendPostRequest(self::PLUGIN_ROUTE, array(
			'etrigger_save'    => 'Save Settings',
			self::PLUGIN_PREF  => self::PLUGIN_PREF_ATTACK,
			'e-token'          => $token,
		));

		$code = $I->grabResponseCode();
		$after = $this->dump($I);

		$I->assertSame((string) self::PLUGIN_PREF_SAFE, (string) $after['plugin_pref'],
			'An unauthenticated POST to '.self::PLUGIN_ROUTE.' rewrote the '.self::PLUGIN.' preference '
			.self::PLUGIN_PREF.' to '.var_export($after['plugin_pref'], true).'. That preference is the '
			.'plugin dispatcher\'s own $access["main/create"], so the write hands a guest the permission.');

		$I->assertSame(301, $code,
			'The guest POST to '.self::PLUGIN_ROUTE.' was answered with HTTP '.$code.' rather than a redirect '
			.'to the admin login. e107::redirect(\'admin\') answers 301 and a token refusal renders a page with '
			.'200, so a 200 here means the request was turned away by the forgery check and this test says '
			.'nothing about permissions.');
	}

	// -----------------------------------------------------------------
	// the whole chain
	// -----------------------------------------------------------------

	/**
	 * The full chain, cookieless and unauthenticated throughout: drive im_path
	 * to a shell payload and resize_method to ImageMagick through the admin
	 * dispatcher's pre-auth window, then ask thumb.php for a thumbnail so the
	 * command actually runs.
	 *
	 * Package P2 took the trigger half away. e107_images/thumb.php no longer
	 * requires resize_handler.php: it translates the 0.8 grammar into an
	 * e_thumbnail request, so no unauthenticated route reaches the exec() that
	 * im_path is interpolated into, and the canary assertion below can no
	 * longer fire whatever the preferences say. What still carries this test is
	 * the pair of preference assertions after it, which are about the write
	 * gate rather than about the shell. The escapeshellarg() sink itself is
	 * covered directly by resize_handlerTest.
	 *
	 * The canary is the headline. The preferences are read back as well so that
	 * a failure says which half fired: a refused write and an absent canary is a
	 * pass, a landed write and an absent canary means the command did not fire
	 * for some reason of its own and the test would otherwise have passed for
	 * the wrong reason.
	 *
	 * _before() has already proved that the web account can create this exact
	 * path through a shell, so "the canary is absent" cannot mean "the container
	 * could never have written it".
	 *
	 * The canary is read inside the web process, through the probe, because
	 * that is the only place it would land. APP_PATH is the runner's own tree
	 * and Extension\WorkspaceCleanup::appRunsInPlace() documents that under a
	 * deploying deployer the app is served from somewhere else entirely, where
	 * a filesystem assertion on APP_PATH would be true against a fully
	 * exploitable site.
	 *
	 * Two payloads, for the same reason as the core preference test above: the
	 * live trigger first, then the deleted update_options handler as a
	 * regression guard.
	 */
	public function guestCannotDriveTheThumbnailerIntoAShell(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the unauthenticated pre-auth chain that ends in a shell command');

		$canary = APP_PATH.'/'.self::CANARY_FILE;
		$payload = 'touch '.$canary.'; ';

		$before = $this->dump($I);

		$I->resetAllCookies();
		$token = $this->grabGuestToken($I);

		$I->stopFollowingRedirects();
		$I->sendPostRequest(self::DIALOG_ROUTE, array(
			'etrigger_save' => 'Save Settings',
			'resize_method' => 'ImageMagick',
			'im_path'       => $payload,
			'e-token'       => $token,
		));

		$I->resetAllCookies();
		$I->sendPostRequest(self::DIALOG_ROUTE, $this->updateOptionsPayload($payload, 'ImageMagick'));

		$I->resetAllCookies();
		$I->amOnPage('/e107_images/thumb.php?'.self::SOURCE_IMAGE.'+100');

		$after = $this->dump($I);

		$I->assertSame(0, (int) $after['canary'],
			'Unauthenticated remote code execution: a cookieless POST to '.self::DIALOG_ROUTE
			.' followed by a cookieless GET of e107_images/thumb.php ran "'.$payload.'" as the web account. '
			.'im_path is now '.var_export($after['im_path'], true)
			.' and resize_method is now '.var_export($after['resize_method'], true).'.');

		$I->assertSame($before['im_path'], $after['im_path'],
			'The shell command did not fire, but the unauthenticated write still landed: im_path was '
			.var_export($before['im_path'], true).' and is now '.var_export($after['im_path'], true)
			.'. The chain is one working thumbnailer away from remote code execution.');

		$I->assertSame($before['resize_method'], $after['resize_method'],
			'The shell command did not fire, but the unauthenticated write still landed: resize_method was '
			.var_export($before['resize_method'], true).' and is now '.var_export($after['resize_method'], true).'.');

		// Secondary, and only meaningful when the app under test is this tree.
		$I->assertFalse(is_file($canary),
			'The canary landed in the runner\'s own tree at '.$canary.'.');
	}

	// -----------------------------------------------------------------
	// the dialog exemption
	// -----------------------------------------------------------------

	/**
	 * image.php:26 reads
	 *
	 *   if (!getperms('A') && ($_GET['action'] !== 'dialog') && ($_GET['action'] !== 'youtube'))
	 *
	 * so a guest asking for any other action on this page is redirected before a
	 * single line of the dispatcher runs, and a guest asking for the dialog is
	 * carried straight into it.
	 *
	 * The assertion is that difference, not a status code of its own choosing. A
	 * fix may put the gate in the dispatcher constructor and answer a guest with
	 * the admin login page, or it may drop the exemption and answer with a
	 * redirect; either is a defensible product decision. What is not defensible
	 * is one route on one page behaving differently from every other route on
	 * the same page, which is precisely what opens the window that the two tests
	 * above walk through.
	 *
	 * Paired with theMediaDialogIsStillServedToAnA1Administrator(), so a fix
	 * that simply refuses the dialog to everybody does not pass.
	 */
	public function theDialogRouteIsNoMoreOpenToAGuestThanAnyOtherRoute(AcceptanceTester $I)
	{
		$I->wantTo('Treat an unauthenticated media dialog request like any other unauthenticated admin request');

		$I->stopFollowingRedirects();

		$I->resetAllCookies();
		$I->amOnPage(self::GATED_ROUTE);
		$gated = $I->grabResponseCode();
		$I->dontSeeInSource(self::MEDIA_MANAGER_MARKER);

		$I->resetAllCookies();
		$I->amOnPage(self::DIALOG_ROUTE);
		$dialog = $I->grabResponseCode();
		$I->dontSeeInSource(self::MEDIA_MANAGER_MARKER);

		$I->assertSame($gated, $dialog,
			'image.php answers an unauthenticated request for '.self::GATED_ROUTE.' with HTTP '.$gated
			.' and one for '.self::DIALOG_ROUTE.' with HTTP '.$dialog.'. The dialog is exempted from the '
			.'page\'s own permission gate, which is what lets a guest reach media_admin_ui::init().');

		// The equality alone is satisfied by 200/200, i.e. by a fix that removed
		// the gate rather than extended it. Pin the direction.
		$I->assertNotSame(200, $dialog,
			'Both routes now answer a guest with 200, so the gate has gone from image.php rather than '
			.'been extended to the dialog.');
	}

	// -----------------------------------------------------------------
	// positive controls
	// -----------------------------------------------------------------

	/**
	 * The media dialog is what every image and file picker in the admin area
	 * opens, and it is offered to delegated administrators holding A1 as well as
	 * to main administrators. Part 1 of this fix changes the boot order of every
	 * admin page in the product, so this is the case that says whether the admin
	 * area still works.
	 *
	 * Asserts the tab container and the upload tab by their exact bytes. The
	 * upload tab is gated on 'A|A1' inside media_admin_ui::mediaManagerTabs(),
	 * so its presence proves the delegated permission was honoured and not
	 * merely tolerated.
	 */
	public function theMediaDialogIsStillServedToAnA1Administrator(AcceptanceTester $I)
	{
		$I->wantTo('Serve the media dialog to a delegated administrator holding A1');

		$this->loginAsDelegatedAdmin($I, 'preauth_a1_admin');

		$I->amOnPage(self::DIALOG_ROUTE.'&for=news&tagid=news_thumbnail&iframe=1&image=1');

		$I->seeResponseCodeIs(200);
		$I->seeInSource(self::MEDIA_MANAGER_MARKER);
		$I->seeInSource(self::UPLOAD_TAB_MARKER);
	}

	/**
	 * The population the A1 case cannot see.
	 *
	 * e_form::mediaUrl() sends every imagepicker, filepicker and mediapicker in
	 * the admin area to action=dialog, and image fields live on pages whose own
	 * permission is not A: news is 'H', custom pages are '5|J', and the
	 * featurebox, forum, download, hero, gsitemap and social plugin admin pages
	 * are 'P'. A news administrator holding 'H' and nothing else is therefore
	 * the ordinary caller of this route, and the dialog is written to serve
	 * them: mediaManagerTabs() renders the icon, image, video, file, audio and
	 * glyph tabs unconditionally and adds the upload tab only for 'A|A1', and
	 * uploadTab() returns an empty string rather than exiting for anyone else.
	 *
	 * A gate on this route that asks for 'A|A1' passes every test in this Cest
	 * and takes the picker away from every delegated administrator in the
	 * product, so this case asserts both halves: the manager renders, and the
	 * upload tab does not.
	 */
	public function theMediaDialogIsStillServedToAnAdministratorWithoutMediaPermissions(AcceptanceTester $I)
	{
		$I->wantTo('Serve the media dialog to a news administrator who holds neither A nor A1');

		$this->loginAsDelegatedAdmin($I, 'preauth_h_admin');

		$I->amOnPage(self::DIALOG_ROUTE.'&for=news&tagid=news_thumbnail&iframe=1&image=1');

		$I->seeResponseCodeIs(200);
		$I->seeInSource(self::MEDIA_MANAGER_MARKER);
		$I->dontSeeInSource(self::UPLOAD_TAB_MARKER);
	}

	/**
	 * media_admin_ui::beforePrefsSave() constrains resize_method to the
	 * backends resize_image() implements, and the constraint has to be visible:
	 * anAdministratorCanStillSaveTheMediaPreferences() posts a value the
	 * whitelist accepts and would pass identically against a tree with no
	 * whitelist at all.
	 *
	 * The stored value is asserted to be the substitute rather than merely "not
	 * the payload", so the coercion itself is pinned and not just the refusal.
	 *
	 * The same request also pins the pref set cleanup that returning an array
	 * from beforePrefsSave() performs: PrefsSaveTrigger() writes the override's
	 * return value instead of the raw $_POST, so 'etrigger_save' and 'e-token'
	 * stop being stored as core preferences by this page.
	 */
	public function theMediaPreferencesRefuseAResizeMethodWithNoBackend(AcceptanceTester $I)
	{
		$I->wantTo('Constrain resize_method to the backends resize_image() implements');

		$I->loginAsAdmin();
		$I->dontSeeElement('input[name=authpass]');

		$token = $I->grabFreshAdminToken(self::GATED_ROUTE);

		$I->sendPostRequest(self::GATED_ROUTE, array(
			'etrigger_save' => 'Save Settings',
			'resize_method' => 'ImageMagick; touch '.APP_PATH.'/'.self::CANARY_FILE,
			'e-token'       => $token,
		));

		$after = $this->dump($I);

		$I->assertSame('gd2', $after['resize_method'],
			'An administrator saved a resize_method no backend implements and it was stored as '
			.var_export($after['resize_method'], true).' rather than substituted with gd2.');

		$I->assertSame(0, (int) $after['pref_spill'],
			'A media preferences save wrote the form\'s own etrigger_save or e-token fields into the core '
			.'preference set. media_admin_ui::beforePrefsSave() returns the copy PrefsSaveTrigger() has '
			.'already stripped them from, so neither should be stored.');
	}

	/**
	 * resize_method is a live preference: it is in media_admin_ui::$prefs and an
	 * administrator saves it from the media preferences page. Any validation
	 * this fix introduces has to sit on that write path too, and has to keep
	 * accepting the value the page offers.
	 *
	 * im_path has no such control because it has no reachable writer:
	 * media_admin_ui::settingsPage() is marked deprecated and returns false on
	 * its first statement, so the form that once posted im_path is not rendered
	 * anywhere. updateSettings() reaching the pre-auth window is the only way
	 * im_path is written in the tree at all.
	 */
	public function anAdministratorCanStillSaveTheMediaPreferences(AcceptanceTester $I)
	{
		$I->wantTo('Let an authenticated administrator save resize_method from the media preferences page');

		$I->loginAsAdmin();
		$I->dontSeeElement('input[name=authpass]');

		$token = $I->grabFreshAdminToken(self::GATED_ROUTE);

		$I->sendPostRequest(self::GATED_ROUTE, array(
			'etrigger_save'  => 'Save Settings',
			'resize_method'  => 'ImageMagick',
			'e-token'        => $token,
		));

		$after = $this->dump($I);

		$I->assertSame('ImageMagick', $after['resize_method'],
			'An authenticated administrator could not save resize_method from '.self::GATED_ROUTE
			.'; it is '.var_export($after['resize_method'], true).'. A refusal that also refuses the '
			.'legitimate write is not a fix.');
	}

	/**
	 * The read side of the same preference pair, end to end.
	 *
	 * This asked resize_image()'s ImageMagick branch for a thumbnail until
	 * package P2 reduced e107_images/thumb.php to a shim over e_thumbnail. The
	 * endpoint no longer reads resize_method or im_path at all, so what is left
	 * here is the compatibility statement: a site that has ImageMagick selected
	 * must still get an image out of the legacy endpoint. The ImageMagick
	 * branch itself is covered by resize_handlerTest::
	 * testImageMagickBranchStillWorksWithADirectoryPrefixImPath() and its empty
	 * im_path twin, which run the branch and read the file it writes.
	 *
	 * The preferences are set through the application rather than through the
	 * route the tests above attack, so this stays a statement about the
	 * thumbnailer and not about who is allowed to write a preference.
	 */
	public function theThumbnailerStillRendersThroughImageMagick(AcceptanceTester $I)
	{
		$I->wantTo('Keep serving thumbnails through the ImageMagick branch');

		$I->amOnPage('/'.self::PROBE_FILE.'?act=imagemagick');
		$I->seeInSource('PROBE_OK');

		$I->amOnPage('/e107_images/thumb.php?'.self::SOURCE_IMAGE.'+100');

		$body = $I->grabPageSource();

		$I->assertSame("\xFF\xD8\xFF", substr($body, 0, 3),
			'e107_images/thumb.php served no JPEG to a site with resize_method set to ImageMagick. '
			.'First 200 bytes: '.var_export(substr($body, 0, 200), true));
	}

	/**
	 * The paired control for the plugin half. Same page, same trigger, same
	 * preference, an authenticated administrator instead of a guest.
	 */
	public function anAdministratorCanStillSavePluginPreferences(AcceptanceTester $I)
	{
		$I->wantTo('Let an authenticated administrator save a bundled plugin preference');

		$this->installPlugin($I);
		$this->reset($I);

		$I->loginAsAdmin();
		$I->dontSeeElement('input[name=authpass]');

		$token = $I->grabFreshAdminToken(self::PLUGIN_ROUTE);

		$I->sendPostRequest(self::PLUGIN_ROUTE, array(
			'etrigger_save'   => 'Save Settings',
			self::PLUGIN_PREF => 250,
			'e-token'         => $token,
		));

		$after = $this->dump($I);

		$I->assertSame('250', (string) $after['plugin_pref'],
			'An authenticated administrator could not save the '.self::PLUGIN.' preference '.self::PLUGIN_PREF
			.' from '.self::PLUGIN_ROUTE.'; it is '.var_export($after['plugin_pref'], true).'.');
	}

	/**
	 * The blunt regression net for part 1. Moving the gate into the dispatcher
	 * constructor touches roughly thirty-nine core pages and every bundled
	 * plugin admin page, and a mistake there takes the whole admin area with it.
	 *
	 * Each page is asserted by a marker of its own, so a page that answers 200
	 * with a login form or an "Access Denied" panel does not pass.
	 */
	public function theAdminPagesAreStillReachableForAMainAdministrator(AcceptanceTester $I)
	{
		$I->wantTo('Keep the admin dispatcher pages reachable for a main administrator');

		$this->installPlugin($I);
		$I->havePluginInstalled('tinymce4');
		$I->havePluginInstalled('import');

		$I->loginAsAdmin();
		$I->dontSeeElement('input[name=authpass]');

		// The marker is the admin-ui list filter or the settings save button:
		// both belong to the controller's own page, so neither survives the
		// login form and neither survives an e403 panel.
		$pages = array(
			'/e107_admin/image.php?mode=main&action=grid'          => '#admin-ui-list-filter',
			'/e107_admin/image.php?mode=cat&action=list'           => '#admin-ui-list-filter',
			'/e107_admin/cpage.php?mode=page&action=list'          => '#admin-ui-list-filter',
			'/e107_admin/users.php?mode=main&action=list'          => '#admin-ui-list-filter',
			'/e107_admin/newspost.php?mode=main&action=list'       => '#admin-ui-list-filter',
			'/e107_plugins/tinymce4/admin_config.php?mode=main&action=prefs' => '[name=etrigger_save]',
			self::PLUGIN_ROUTE                                     => '[name=etrigger_save]',
		);

		foreach($pages as $page => $marker)
		{
			$I->amOnPage($page);
			$I->seeResponseCodeIs(200);
			$I->dontSeeElement('input[name=authpass]');
			$I->seeElement($marker);
		}

		$I->amOnPage('/e107_plugins/import/admin_import.php');
		$I->seeResponseCodeIs(200);
		$I->dontSeeElement('input[name=authpass]');
		$I->seeElement('#core-import-form');

		$I->dropPluginInstall('tinymce4');
		$I->dropPluginInstall('import');
	}

	/**
	 * The permission letters that commit "gate the admin entry points that had
	 * no gate of their own" chose, tested by the accounts a wrong letter
	 * breaks.
	 *
	 * A main administrator satisfies every getperms() letter there is, so the
	 * sweep above would stay green even if a gate asked for the wrong one. The
	 * account that breaks is the delegated administrator, and lancheck.php is
	 * the sharpest case: it is not requested directly but pulled in by
	 * e107::getSingleton('lancheck', ...) from four places in language.php, so
	 * a wrong letter there redirects in the middle of a page that the visitor
	 * is entitled to.
	 */
	public function theLanguageToolsPageIsStillReachableForALanguageAdministrator(AcceptanceTester $I)
	{
		$I->wantTo('Keep e107_admin/lancheck.php reachable for an administrator holding L and nothing else');

		$this->loginAsDelegatedAdmin($I, 'preauth_l_admin');

		$I->amOnPage('/e107_admin/language.php?mode=main&action=tools');

		$I->seeResponseCodeIs(200);
		$I->dontSeeElement('input[name=authpass]');
		$I->seeElement('#lancheck');
	}

	// -----------------------------------------------------------------
	// where the guest lands afterwards
	// -----------------------------------------------------------------

	/**
	 * A guest turned away by the dispatcher's gate must come back to the page
	 * they asked for once they have signed in, not to the dashboard.
	 *
	 * The mechanism is redirect_class::go('admin'), which calls
	 * setLoginDestination() whenever the caller is not already an
	 * administrator. That is not new and it is not part of the gate; what was
	 * missing is anything that would notice it breaking. The gate is a new
	 * caller of go('admin') on a page nothing else redirects from, so this is
	 * the seam where a regression would show up first and be blamed on the
	 * gate.
	 *
	 * The page used is the dispatcher fixture: an admin entry point with no
	 * permission gate of its own, so the redirect under test is unambiguously
	 * the one e_admin_dispatcher::__construct() issues.
	 *
	 * This test passes on the unfixed tree, because the mechanism already
	 * works. Its value is as a regression guard, and its non-vacuity is shown
	 * by reverting e107_handlers/redirection_class.php to the commit before
	 * "feat(login): return users to their intended page after login", which
	 * turns it red. Reverting that file to the commit before the dispatcher
	 * gate would change nothing, because that package did not touch it.
	 *
	 * @see e107_handlers/redirection_class.php  redirection::go(), setLoginDestination()
	 * @see e107_admin/auth.php  the login check that consumes the destination
	 */
	public function aGuestBouncedFromAnAdminPageReturnsToItAfterSigningIn(AcceptanceTester $I)
	{
		$I->wantTo('Return the administrator to the admin page they asked for as a guest');

		$this->installPlugin($I);
		$I->writeAppFile(self::DISPATCHER_FIXTURE, $this->dispatcherFixtureSource());
		$this->reset($I);

		$I->resetAllCookies();
		$I->startFollowingRedirects();
		$I->amOnPage('/'.self::DISPATCHER_FIXTURE);

		$I->seeElement('input[name=authname]');
		$I->seeCookie(self::LOGIN_DEST_COOKIE);

		$I->fillField('authname', \Helper\AdminLogin::ADMIN_USER);
		$I->fillField('authpass', \Helper\AdminLogin::ADMIN_PASS);
		$I->click('authsubmit');

		$I->dontSeeElement('input[name=authname]');
		$I->seeInCurrentUrl('/'.self::DISPATCHER_FIXTURE);
	}

	/**
	 * A POST is not a page, and must not become the place the administrator is
	 * returned to.
	 *
	 * redirection::isCapturable() refuses anything that is not a top-level GET
	 * document navigation, so this lands on the dashboard by design. Asserted
	 * here so that a change which starts capturing it is caught rather than
	 * welcomed: replaying a POST target as a GET after login is at best a
	 * confusing page and at worst a form resubmission the administrator never
	 * asked for.
	 */
	public function aGuestPostToAnAdminPageIsNotRememberedAsTheLoginDestination(AcceptanceTester $I)
	{
		$I->wantTo('Not remember a POST target as the post-login destination');

		$this->installPlugin($I);
		$I->writeAppFile(self::DISPATCHER_FIXTURE, $this->dispatcherFixtureSource());
		$this->reset($I);

		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->sendPostRequest('/'.self::DISPATCHER_FIXTURE, array());

		$I->assertSame(301, $I->grabResponseCode(),
			'The guest POST was not turned away, so there was no bounce for the destination logic to '
			.'have captured and nothing below is being measured.');

		$I->dontSeeCookie(self::LOGIN_DEST_COOKIE);

		$I->startFollowingRedirects();
		$I->loginAsAdmin();

		$I->dontSeeInCurrentUrl('/'.self::DISPATCHER_FIXTURE);
	}

	/**
	 * An iframe sub-request is not a page either.
	 *
	 * The media dialog is the case that matters in practice: e_form::mediaUrl()
	 * builds it for every image and file picker in the product, and returning
	 * an administrator to the bare dialog after login drops them into an
	 * embedded view with no navigation. The browser tags the sub-request
	 * Sec-Fetch-Dest: iframe, which isCapturable() honours, and the header
	 * cannot be set by page script.
	 */
	public function anIframeSubRequestIsNotRememberedAsTheLoginDestination(AcceptanceTester $I)
	{
		$I->wantTo('Not remember an iframe sub-request as the post-login destination');

		$this->reset($I);

		$I->resetAllCookies();
		$I->startFollowingRedirects();
		$I->haveHttpHeader('Sec-Fetch-Dest', 'iframe');
		$I->amOnPage(self::DIALOG_ROUTE);

		$I->seeElement('input[name=authname]');
		$I->dontSeeCookie(self::LOGIN_DEST_COOKIE);

		// The header belongs to the sub-request, not to the login the visitor
		// then performs in the top-level document.
		$I->deleteHeader('Sec-Fetch-Dest');

		$I->loginAsAdmin();

		$I->dontSeeInCurrentUrl('/e107_admin/image.php');
	}

	/**
	 * The same refusal, on a URL that carries no dialog marker at all.
	 *
	 * The media dialog is guarded twice over: by Sec-Fetch-Dest, and by the URL
	 * marker belt that recognises ?mode=dialog off the address itself for
	 * clients that send no Fetch Metadata. Either guard alone keeps the test
	 * above green, so neither is witnessed by it.
	 *
	 * The dispatcher fixture has no marker in its URL, so the header is the
	 * only thing that can refuse it, and a change that stops isCapturable()
	 * reading Sec-Fetch-Dest is caught here on its own.
	 *
	 * @see e107_handlers/redirection_class.php  redirection::isCapturable()
	 */
	public function anIframeSubRequestToAnUnmarkedUrlIsNotRememberedEither(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an iframe sub-request as the login destination without a dialog marker to go on');

		$this->installPlugin($I);
		$I->writeAppFile(self::DISPATCHER_FIXTURE, $this->dispatcherFixtureSource());
		$this->reset($I);

		$I->resetAllCookies();
		$I->startFollowingRedirects();
		$I->haveHttpHeader('Sec-Fetch-Dest', 'iframe');
		$I->amOnPage('/'.self::DISPATCHER_FIXTURE);

		$I->seeElement('input[name=authname]');
		$I->dontSeeCookie(self::LOGIN_DEST_COOKIE);

		$I->deleteHeader('Sec-Fetch-Dest');

		$I->loginAsAdmin();

		$I->dontSeeInCurrentUrl('/'.self::DISPATCHER_FIXTURE);
	}

	// -----------------------------------------------------------------
	// fixture
	// -----------------------------------------------------------------

	/**
	 * The parameter set media_admin_ui::updateSettings() reads. Every key is
	 * supplied because updateSettings() reads them unconditionally; an attacker
	 * copying the form would supply them too.
	 *
	 * @param string $imPath
	 * @param string $resizeMethod
	 * @return array
	 */
	private function updateOptionsPayload($imPath, $resizeMethod)
	{
		return array(
			'update_options'             => 1,
			'im_path'                    => $imPath,
			'resize_method'              => $resizeMethod,
			'image_post'                 => 1,
			'image_post_class'           => 253,
			'image_post_disabled_method' => 0,
			'enable_png_image_fix'       => 0,
			'img_import_resize_w'        => 0,
			'img_import_resize_h'        => 0,
		);
	}

	/**
	 * Put the preferences this Cest writes back where it found them, clear the
	 * request ban, and prove the canary path is one the web account can create
	 * through a shell and this fixture can remove again.
	 *
	 * That last part is not housekeeping. "The canary is absent" is the whole
	 * of the headline assertion, and it would be just as absent on a container
	 * with exec() disabled or a docroot the web account cannot write. The probe
	 * creates the canary by the same mechanism the payload would and refuses to
	 * report success if it could not.
	 */
	private function reset(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=reset');

		$body = $I->grabPageSource();

		if(strpos($body, 'PROBE_OK') === false)
		{
			throw new \RuntimeException('Fixture reset failed: '.trim(strip_tags($body)));
		}
	}

	/**
	 * @return array im_path, resize_method and the plugin preference
	 */
	private function dump(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->amOnPage('/'.self::PROBE_FILE.'?act=dump');

		$body = $I->grabPageSource();

		if(!preg_match('/PROBE_DUMP(.*)PROBE_END/s', $body, $matches))
		{
			throw new \RuntimeException('Fixture dump failed: '.trim(strip_tags($body)));
		}

		$decoded = json_decode(trim($matches[1]), true);

		if(!is_array($decoded))
		{
			throw new \RuntimeException('Fixture dump was not JSON: '.trim($matches[1]));
		}

		return $decoded;
	}

	private function installPlugin(AcceptanceTester $I)
	{
		$I->havePluginInstalled(self::PLUGIN);
		$this->pluginInstalled = true;
	}

	/**
	 * The CSRF token this site hands a visitor who has not signed in.
	 *
	 * An attacker driving their own browser has one for the asking, so a token
	 * says nothing about who is calling. Grabbing it here is what stops the
	 * authorisation test being answered by the forgery check.
	 *
	 * @return string
	 */
	private function grabGuestToken(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->amOnPage('/index.php');

		$source = $I->grabPageSource();

		if(!preg_match('/name=[\'"]e-token[\'"][^>]*(?:value|content)=[\'"]([^\'"]+)[\'"]/', $source, $matches))
		{
			throw new \RuntimeException('The front page handed a guest no CSRF token to replay.');
		}

		return $matches[1];
	}

	/**
	 * Seed and sign in as an administrator holding one delegated permission and
	 * nothing else.
	 *
	 * Seeded on every call, never memoised: Codeception shares one Cest
	 * instance across its test methods, and Codeception\Module\Db::_after()
	 * removes every haveInDatabase() row after each of them, so a cached user
	 * id outlives the user it names.
	 *
	 * @param string $loginName a key of self::DELEGATED_ADMINS
	 */
	private function loginAsDelegatedAdmin(AcceptanceTester $I, $loginName)
	{
		$perms = self::DELEGATED_ADMINS[$loginName];

		$I->haveInDatabase('e107_user', array(
			'user_name'      => $loginName,
			'user_loginname' => $loginName,
			'user_email'     => $loginName.'@example.com',
			'user_password'  => md5($loginName),
			'user_join'      => 1262304000,
			'user_class'     => '',
			'user_admin'     => 1,
			'user_perms'     => $perms,
			'user_xup'       => '',
			'user_prefs'     => '',
			'user_signature' => '',
			'user_realm'     => '',
		));

		$I->loginAsAdmin($loginName, $loginName);
		$I->dontSeeElement('input[name=authpass]');
	}

	/**
	 * An admin entry point with no gate of its own, standing in for the third
	 * party plugin admin pages the dispatcher gate was written for.
	 *
	 * The controller is a plain e_admin_controller rather than an e_admin_ui,
	 * so the fixture needs no table and no fields: the only thing under test is
	 * whether _initController() reaches init() at all.
	 *
	 * @return string
	 */
	private function dispatcherFixtureSource()
	{
		$plugin = self::PLUGIN;
		$pref = self::PLUGIN_PREF;
		$attack = self::PLUGIN_PREF_ATTACK;

		return <<<PHP
<?php
// Fixture for 0033_AdminPreAuthCest. Removed again in the Cest's _after().
\$_E107['no_online'] = true;
require_once(__DIR__.'/../../class2.php');

// class2.php loads these only when it recognises the file as an admin page,
// and e_admin_dispatcher::\$pageTitles has LAN_MANAGE in a property default,
// so the class cannot even be declared without them.
e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_admin.php');

class e107_tests_preauth_controller extends e_admin_controller
{
	public function init()
	{
		e107::getPlugConfig('$plugin')->set('$pref', $attack)->save(false, true, false);
	}
}

class e107_tests_preauth_admin extends e_admin_dispatcher
{
	protected \$modes = array(
		'main' => array('controller' => 'e107_tests_preauth_controller', 'index' => 'list', 'path' => null)
	);
}

new e107_tests_preauth_admin();

require_once(e_ADMIN.'auth.php');
PHP;
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		$canary = self::CANARY_FILE;
		$plugin = self::PLUGIN;
		$pluginPref = self::PLUGIN_PREF;
		$pluginSafe = self::PLUGIN_PREF_SAFE;

		return <<<PHP
<?php
// Fixture for 0033_AdminPreAuthCest. Removed again in the Cest's _after().
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');

\$canary = __DIR__.'/$canary';
\$core = e107::getConfig('core');
\$plug = e107::getPlugConfig('$plugin');

switch(isset(\$_GET['act']) ? \$_GET['act'] : '')
{
	case 'reset':
		e107::getDb()->delete('online');
		e107::getDb()->delete('banlist', 'banlist_bantype IN (2, -2)');

		@unlink(\$canary);

		// Prove the payload's mechanism works here before any test concludes
		// anything from the canary being absent.
		\$disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

		if(in_array('exec', \$disabled, true) || in_array('passthru', \$disabled, true))
		{
			echo "PROBE_SHELL_DISABLED: exec/passthru are disabled here, so an absent canary would prove nothing\n";
			exit;
		}

		\$out = array();
		\$rc = 0;
		exec('touch '.escapeshellarg(\$canary), \$out, \$rc);

		if(\$rc !== 0 || !file_exists(\$canary))
		{
			echo "PROBE_CANARY_UNWRITABLE: the web account cannot create ".\$canary." through a shell\n";
			exit;
		}

		@unlink(\$canary);

		if(file_exists(\$canary))
		{
			echo "PROBE_CANARY_UNDELETABLE: ".\$canary."\n";
			exit;
		}

		// Back to what a fresh install leaves, plus the two keys a save through
		// e_admin_ui::PrefsSaveTrigger() spills into the pref set.
		\$core->set('im_path', '/usr/X11R6/bin/');
		\$core->set('resize_method', 'gd2');
		\$core->remove('etrigger_save');
		\$core->remove('e-token');
		\$core->save(false, true, false);

		// Only when the plugin is installed. Writing it otherwise creates the
		// plugin_faqs row this fixture then reads back as the plugin's own
		// shipped default.
		if(e107::isInstalled('$plugin'))
		{
			\$plug->set('$pluginPref', '$pluginSafe');
			\$plug->remove('etrigger_save');
			\$plug->remove('e-token');
			\$plug->save(false, true, false);
		}

		echo "PROBE_OK\n";
		break;

	case 'imagemagick':
		// A working ImageMagick configuration, written the legitimate way.
		\$core->set('resize_method', 'ImageMagick');
		\$core->set('im_path', '');
		\$core->save(false, true, false);
		echo "PROBE_OK\n";
		break;

	case 'dump':
		// Core preferences live serialised inside a single e107_core row, so
		// there is nothing for seeInDatabase() to read. Boot the application
		// and ask it instead.
		echo 'PROBE_DUMP'.json_encode(array(
			'im_path'       => \$core->get('im_path'),
			'resize_method' => \$core->get('resize_method'),
			'plugin_pref'   => \$plug->get('$pluginPref'),
			'canary'        => file_exists(\$canary) ? 1 : 0,
			'pref_spill'    => (\$core->get('etrigger_save') !== null || \$core->get('e-token') !== null) ? 1 : 0,
		))."PROBE_END\n";
		break;

	default:
		echo "PROBE_UNKNOWN_ACTION\n";
}
PHP;
	}
}
