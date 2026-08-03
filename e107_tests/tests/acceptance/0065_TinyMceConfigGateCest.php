<?php

/**
 * e107_plugins/tinymce4/wysiwyg_class.php::getConfig() picks the editor
 * configuration from ?config=, and both of its guards are the wrong shape.
 *
 * :251 is the main-admin gate:
 *
 *   if(($template == 'mainadmin.xml' && !getperms('0')) || ($template == 'admin.xml' && !ADMIN))
 *
 * A literal string comparison against a value the caller supplies. ?config=
 * goes through $tp->filter() at :227, which for the default 'str' type is
 * htmlspecialchars(strip_tags($input)) and leaves dots and slashes untouched,
 * so ?config=./mainadmin becomes './mainadmin.xml', which is not equal to
 * 'mainadmin.xml' and so is never downgraded, while :257 resolves it to exactly
 * the same file. The gate is defeated by two characters.
 *
 * :257 is the containment:
 *
 *   $configPath = is_readable(THEME."templates/tinymce/".$template)
 *       ? THEME."templates/tinymce/".$template
 *       : e_PLUGIN."tinymce4/templates/".$template;
 *
 * Nothing keeps $template inside either directory, so ../ walks out of both.
 *
 * wysiwyg.php requires nothing but class2.php, so every one of these requests
 * is made as an unauthenticated visitor.
 */
class TinyMceConfigGateCest
{
	const WYSIWYG = '/e107_plugins/tinymce4/wysiwyg.php';

	/** e107_plugins/tinymce4/templates/mainadmin.xml names itself. */
	const MAINADMIN_MARKER = 'TinyMce Config: Main Admin';
	const PUBLIC_MARKER = 'TinyMce Config: Public';
	const CANARY_MARKER = 'TinyMce Config: P8 CANARY';

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(\Helper\P8Fixture::PROBE_FILE, \Helper\P8Fixture::probeSource());
		$I->writeAppFile(\Helper\P8Fixture::TINYMCE_CANARY_FILE, \Helper\P8Fixture::tinymceCanaryXml());
		$I->amOnPage('/'.\Helper\P8Fixture::PROBE_FILE.'?p8=reset');
		$I->see('P8_OK reset');
		$I->resetAllCookies();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->deleteAppFile(\Helper\P8Fixture::PROBE_FILE);
		$I->deleteAppFile(\Helper\P8Fixture::TINYMCE_CANARY_FILE);
	}

	/**
	 * The gate, defeated by a leading "./".
	 */
	public function aVisitorCannotReachTheMainAdminConfigByDottingThePath(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the main-admin editor config to a visitor who writes ./mainadmin');

		$I->amOnPage(self::WYSIWYG.'?config=./mainadmin');

		$I->dontSeeInSource(self::MAINADMIN_MARKER);
		// Refused means downgraded to the public editor, not blank: a 500 or an
		// empty body satisfies the assertion above just as well.
		$I->seeInSource(self::PUBLIC_MARKER);
		$I->seeInSource('tinymce.init({');
	}

	/**
	 * The same file named through a directory that exists, so the fix cannot be
	 * a special case for the two characters above.
	 */
	public function aVisitorCannotReachTheMainAdminConfigThroughATraversal(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the main-admin editor config named through a traversal');

		$I->amOnPage(self::WYSIWYG.'?config=templates/../mainadmin');

		$I->dontSeeInSource(self::MAINADMIN_MARKER);
		$I->seeInSource(self::PUBLIC_MARKER);
	}

	/**
	 * Containment: ../../../ from e107_plugins/tinymce4/templates reaches the
	 * app root, and the canary sitting there must not be loadable as an editor
	 * configuration.
	 */
	public function aVisitorCannotReadAConfigOutsideTheTemplateDirectories(AcceptanceTester $I)
	{
		$I->wantTo('Refuse to load an editor config from outside both template directories');

		$canary = basename(\Helper\P8Fixture::TINYMCE_CANARY_FILE, '.xml');
		// Percent-encoded: an unencoded "../" in the query string is normalised
		// away before the request leaves, so the literal spelling would test the
		// test client rather than the application. An attacker has no such
		// constraint, and PHP decodes both to the same value.
		$I->amOnPage(self::WYSIWYG.'?config='.rawurlencode('../../../'.$canary));

		$I->dontSeeInSource(self::CANARY_MARKER);
		$I->seeInSource(self::PUBLIC_MARKER);
	}

	/**
	 * The same traversal spelled with backslashes. basename() does not treat a
	 * backslash as a separator on Linux, so a name scrubbed with basename() alone
	 * passes through intact and resolves on a Windows host, where wysiwyg.php
	 * still asks nobody who is calling.
	 */
	public function aVisitorCannotReadAConfigNamedWithBackslashes(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an editor config named through a backslash traversal');

		$canary = basename(\Helper\P8Fixture::TINYMCE_CANARY_FILE, '.xml');
		$I->amOnPage(self::WYSIWYG.'?config='.rawurlencode('..\\..\\..\\'.$canary));

		$I->dontSeeInSource(self::CANARY_MARKER);
		$I->seeInSource(self::PUBLIC_MARKER);
	}

	/**
	 * Positive control for the gate as it already stands: the plainly spelled
	 * name is downgraded today and has to go on being downgraded.
	 */
	public function aVisitorAskingPlainlyStillGetsThePublicConfig(AcceptanceTester $I)
	{
		$I->wantTo('Downgrade a plainly spelled main-admin request to the public config');

		$I->amOnPage(self::WYSIWYG.'?config=mainadmin');

		$I->seeInSource(self::PUBLIC_MARKER);
	}

	/**
	 * Positive control. A visitor with no config parameter gets the public
	 * editor, and it has to keep working.
	 */
	public function aVisitorStillGetsAWorkingPublicConfig(AcceptanceTester $I)
	{
		$I->wantTo('Still serve a working public editor config to a visitor');

		$I->amOnPage(self::WYSIWYG);

		$I->seeInSource(self::PUBLIC_MARKER);
		$I->seeInSource('tinymce.init({');
	}

	/**
	 * Positive control. A main administrator is who mainadmin.xml is for, and
	 * they have to keep getting it, both by default and by name. e_footer.php
	 * asks for a named config through e_TINYMCE_TEMPLATE, so the named form is
	 * a real caller and not a hypothetical one.
	 */
	public function aMainAdministratorStillGetsTheMainAdminConfig(AcceptanceTester $I)
	{
		$I->wantTo('Still serve the main-admin editor config to a main administrator');

		$I->loginAsAdmin();

		$I->amOnPage(self::WYSIWYG);
		$I->seeInSource(self::MAINADMIN_MARKER);

		$I->amOnPage(self::WYSIWYG.'?config=mainadmin');
		$I->seeInSource(self::MAINADMIN_MARKER);
	}
}
