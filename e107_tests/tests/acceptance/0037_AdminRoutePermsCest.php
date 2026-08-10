<?php

/**
 * Delegated-administrator escalation in e107_admin/users.php, and the route
 * permission map the file has carried a TODO for since 2008.
 *
 * The package before this one answered "is this request authenticated as an
 * administrator at all". This one answers the next question down: "does this
 * administrator hold the permission this route requires".
 *
 * Two separate defects live in the same file.
 *
 * The first is a grant of administrator permissions by a caller who may not
 * make one. users_admin_ui::AddSubmitTrigger() writes
 * $allData['data']['user_perms'] = implode('.', $_POST['perms']) with no check
 * on the caller, and users_admin_ui::beforeUpdate() does the same thing on the
 * edit route. The widgets that render the permission table both defer to
 * getperms('3') ("Modify Admin perms"), so the field is not on the form the
 * delegated administrator is served; the triggers accept it anyway. A
 * delegated administrator holding U1 ("Quick Add User") and nothing else
 * creates an account with user_perms = '0', which is main administrator, and
 * one holding U0 ("moderate users/bans") edits their own row to the same
 * effect.
 *
 * The second is that the file has no route permission map at all. Its only
 * gate is getperms('4|U0|U1|U2|U3') at the top of the file, so every one of
 * those five permissions opens every route the file serves. The map that was
 * supposed to be there is written out as a comment at users.php:37-40 and was
 * never implemented, and e_admin_dispatcher::hasRouteAccess() returns TRUE for
 * a dispatcher that declares neither $perm nor $access, so nothing else takes
 * up the slack.
 *
 * Every refusal here is read back as a side effect: the user row, the
 * e107_generic rank rows, or the core preferences through a probe.
 * e_admin_dispatcher::checkAccess() rewrites the action to e403 and then
 * constructs the controller and runs its init() anyway, so a page that renders
 * "Access Denied" is not evidence that the write did not land.
 *
 * @see e107_admin/users.php  users_admin::$modes, AddSubmitTrigger(), beforeUpdate()
 * @see e107_admin/newspost.php  news_admin::$perm, the shape this map should take
 * @see e107_handlers/admin_ui.php  e_admin_dispatcher::checkAccess(), hasRouteAccess()
 * @see e107_handlers/user_handler.php  e_userperms, the permission code list
 */
class AdminRoutePermsCest
{
	const PROBE_FILE = 'e107_tests_routeperms_probe.php';

	/** Quick Add User. users.php:37-40 says this route needs '4|U1|U0'. */
	const ROUTE_ADD = '/e107_admin/users.php?mode=main&action=add';

	/** User options. users.php:37-40 says this route needs '4|U2'. */
	const ROUTE_PREFS = '/e107_admin/users.php?mode=main&action=prefs';

	/** User ranks. users.php:37-40 says this route needs '4|U3'. */
	const ROUTE_RANKS = '/e107_admin/users.php?mode=ranks&action=list';

	const ROUTE_EDIT = '/e107_admin/users.php?mode=main&action=edit&id=';

	/** The user list, and the batch that hangs off it. */
	const ROUTE_LIST = '/e107_admin/users.php?mode=main&action=list';

	/** The same listing under its other action name, with its own batch trigger. */
	const ROUTE_GRID = '/e107_admin/users.php?mode=main&action=grid';

	/** An account awaiting activation, which is what the mass reset acts on. */
	const PENDING_USER = 'p7rppending';

	/**
	 * Seeded onto every account this Cest creates, so md5(ADMINPWCHANGE) - the
	 * value the 'ac' field of the quick-add form carries - is computable here
	 * without loading a page that the route permission map is about to close.
	 *
	 * @see e107_handlers/user_model.php  e_system_user::getAdminPwchange()
	 * @see class2.php  define('ADMINPWCHANGE', ...)
	 */
	const PWCHANGE = 1262304000;

	/**
	 * Administrator permissions the account created by the attack asks for.
	 * '0' is main administrator: every permission there is, forever.
	 */
	const ESCALATED_PERMS = '0';

	/** What a legitimate grant looks like, for the positive controls. */
	const GRANTED_PERMS = 'H';

	/**
	 * Delegated administrators seeded per test: login name => user_perms.
	 *
	 * Every login name starts 'p7rp' so the probe can sweep the accounts this
	 * Cest creates through the application, which Codeception's Db module does
	 * not know about and therefore does not roll back.
	 *
	 * @see e107_handlers/user_handler.php e_userperms::getPermList()
	 */
	const DELEGATED_ADMINS = array(
		'p7rpU0admin'    => 'U0',   // moderate users/bans
		'p7rpU1admin'    => 'U1',   // Quick Add User
		'p7rpU2admin'    => 'U2',   // user options only
		'p7rpU3admin'    => 'U3',   // user ranks only
		'p7rp4admin'     => '4',    // manage all user access and settings, but not admin perms
		'p7rpPermsadmin' => '4.3',  // manage all users, and modify admin perms
	);

	/** A second administrator, for the routes that act on somebody else's account. */
	const OTHER_ADMIN = 'p7rpotheradmin';

	/** What that second administrator holds. */
	const OTHER_ADMIN_PERMS = 'H';

	/** Login name of the account the quick-add attack creates. */
	const CREATED_USER = 'p7rpcreated';

	/** An ordinary member the edit-route attack is aimed at. */
	const VICTIM_USER = 'p7rpvictim';

	/** Distinctive value written into the user options preference set. */
	const PREF_ATTACK = 77;

	/** What a fresh install leaves in user_new_period. */
	const PREF_SAFE = 3;

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->startFollowingRedirects();
		$this->reset($I);
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$this->reset($I);
		$I->deleteAppFile(self::PROBE_FILE);
	}

	// -----------------------------------------------------------------
	// item 1: the escalation, on the create route and on the edit route
	// -----------------------------------------------------------------

	/**
	 * A delegated administrator who may add users must not be able to make the
	 * account they add an administrator.
	 *
	 * U1 is "Quick Add User" and nothing else. The permission table is not on
	 * the form this account is served, because users_admin_form_ui renders it
	 * only for getperms('4|U0'); AddSubmitTrigger() reads $_POST['perms']
	 * regardless of who is calling.
	 *
	 * The assertion is read out of the user table, not off the page. The
	 * request is a legitimate one for this account - it holds the create route
	 * - so the page it is answered with says "user created" either way, and the
	 * only thing that distinguishes a fixed tree from a broken one is what
	 * landed in user_perms.
	 *
	 * Paired with a positive control in its own right: the account must still
	 * be created. Dropping the field is the fix; refusing the submission is
	 * not, and a fix that refused it would leave this test green while breaking
	 * the feature.
	 */
	public function aDelegatedAdministratorCannotGrantAdminPermissionsOnCreate(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an administrator grant from a delegated administrator on the quick-add route');

		$this->loginAsDelegatedAdmin($I, 'p7rpU1admin');

		$I->amOnPage(self::ROUTE_ADD);
		$I->seeResponseCodeIs(200);

		$I->assertSame(md5((string) self::PWCHANGE), $this->grabConfirmToken($I),
			'The quick-add form did not render the ac value this Cest computes, so every request below '
			.'would be refused by the identity-confirmation guard rather than by anything under test.');

		$I->dontSeeElement('input[name="perms[]"]');

		$I->sendPostRequest(self::ROUTE_ADD, $this->quickAddPayload($I, self::ESCALATED_PERMS));

		$I->seeInDatabase('e107_user', array('user_loginname' => self::CREATED_USER));

		$perms = $I->grabFromDatabase('e107_user', 'user_perms',
			array('user_loginname' => self::CREATED_USER));

		$I->assertSame('', (string) $perms,
			'A delegated administrator holding U1 ("Quick Add User") and nothing else created '
			.self::CREATED_USER.' with user_perms = '.var_export($perms, true).'. '
			.'"'.self::ESCALATED_PERMS.'" is main administrator. The permission table is not even on the '
			.'form this account is served; users_admin_ui::AddSubmitTrigger() reads $_POST[\'perms\'] with '
			.'no check on the caller.');

		$admin = $I->grabFromDatabase('e107_user', 'user_admin',
			array('user_loginname' => self::CREATED_USER));

		$I->assertSame('0', (string) $admin,
			self::CREATED_USER.' was created with user_admin = '.var_export($admin, true)
			.'. AddSubmitTrigger() sets it alongside user_perms, from the same unchecked $_POST[\'perms\'].');
	}

	/**
	 * The same defect on the edit route, which is a different call site: the
	 * grant is written by users_admin_ui::beforeUpdate(), not by
	 * AddSubmitTrigger().
	 *
	 * This is the sibling the fix has to cover as well. Four published e107
	 * advisories were closed at one call site while a sibling route stayed
	 * open, which is why it is asserted separately rather than assumed.
	 *
	 * The account used holds U0 ("moderate users/bans"), so editing a member is
	 * a thing it is entitled to do, and the rename asserted below is the
	 * positive control that says so.
	 */
	public function aDelegatedAdministratorCannotGrantAdminPermissionsOnEdit(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an administrator grant from a delegated administrator on the user edit route');

		$victimId = $this->seedVictim($I);
		$this->loginAsDelegatedAdmin($I, 'p7rpU0admin');

		$I->amOnPage(self::ROUTE_EDIT.$victimId);
		$I->seeResponseCodeIs(200);
		$I->dontSeeElement('input[name="perms[]"]');

		$payload = $this->editPayload($I, $victimId, self::VICTIM_USER);
		$payload['user_name'] = self::VICTIM_USER.'renamed';
		$payload['perms'] = array(self::ESCALATED_PERMS);

		$I->sendPostRequest(self::ROUTE_EDIT.$victimId, $payload);

		$I->assertSame(self::VICTIM_USER.'renamed',
			(string) $I->grabFromDatabase('e107_user', 'user_name', array('user_id' => $victimId)),
			'The edit did not take effect at all, so nothing below distinguishes a fix that drops the '
			.'permission field from one that broke the user editor.');

		$perms = $I->grabFromDatabase('e107_user', 'user_perms', array('user_id' => $victimId));

		$I->assertSame('', (string) $perms,
			'A delegated administrator holding U0 and nothing else wrote user_perms = '
			.var_export($perms, true).' onto '.self::VICTIM_USER.' through the user edit route. '
			.'users_admin_ui::beforeUpdate() maps $_POST[\'perms\'] onto user_perms with no check on '
			.'the caller, the same defect as AddSubmitTrigger() at a second call site.');
	}

	/**
	 * The other half of the edit route's grant, asserted separately because it
	 * is a separate mechanism and a fix aimed only at $_POST['perms'] leaves it
	 * standing.
	 *
	 * user_admin is a declared field of users_admin_ui, so e_admin_ui writes
	 * whatever was posted for it. The widget is rendered read-only unless the
	 * caller holds getperms('3'), exactly like the permission table, and
	 * exactly like the permission table that is a decision taken in the form
	 * and nowhere else.
	 *
	 * On its own this grants no permission letter, but it is what ADMIN is
	 * defined from, so the account it is set on satisfies the authentication
	 * gate on every admin entry point in the product and reaches any dispatcher
	 * route that declares no permission of its own. Paired with the perms grant
	 * above it promotes an arbitrary member to main administrator in one
	 * request.
	 */
	public function aDelegatedAdministratorCannotMakeAnAccountAnAdministratorOnEdit(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a delegated administrator setting the admin flag on the user edit route');

		$victimId = $this->seedVictim($I);
		$this->loginAsDelegatedAdmin($I, 'p7rpU0admin');

		$I->amOnPage(self::ROUTE_EDIT.$victimId);
		$I->seeResponseCodeIs(200);

		$payload = $this->editPayload($I, $victimId, self::VICTIM_USER);
		$payload['user_customtitle'] = 'p7rp admin flag';
		$payload['user_admin'] = 1;

		$I->sendPostRequest(self::ROUTE_EDIT.$victimId, $payload);

		$I->assertSame('p7rp admin flag',
			(string) $I->grabFromDatabase('e107_user', 'user_customtitle', array('user_id' => $victimId)),
			'The edit did not take effect at all, so the assertion below proves nothing.');

		$admin = $I->grabFromDatabase('e107_user', 'user_admin', array('user_id' => $victimId));

		$I->assertSame('0', (string) $admin,
			'A delegated administrator holding U0 and nothing else set user_admin = '
			.var_export($admin, true).' on '.self::VICTIM_USER.'. The widget for that field is rendered '
			.'read-only for this caller by users_admin_form_ui::user_admin(), which tests getperms(\'3\'); '
			.'the model writes the posted value regardless.');
	}

	/**
	 * The edit route pointed at the caller's own row, which is the shape that
	 * turns the defect into a self-service promotion.
	 *
	 * The account already has user_admin = 1, so overwriting user_perms is the
	 * whole of the escalation: no second step, no other account, no waiting for
	 * anyone to approve anything.
	 */
	public function aDelegatedAdministratorCannotPromoteThemselvesThroughTheEditRoute(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a delegated administrator rewriting their own admin permissions');

		$this->loginAsDelegatedAdmin($I, 'p7rpU0admin');

		$selfId = (int) $I->grabFromDatabase('e107_user', 'user_id',
			array('user_loginname' => 'p7rpU0admin'));

		$I->amOnPage(self::ROUTE_EDIT.$selfId);
		$I->seeResponseCodeIs(200);

		$payload = $this->editPayload($I, $selfId, 'p7rpU0admin');
		$payload['user_customtitle'] = 'p7rp self edit';
		$payload['perms'] = array(self::ESCALATED_PERMS);

		$I->sendPostRequest(self::ROUTE_EDIT.$selfId, $payload);

		$I->assertSame('p7rp self edit',
			(string) $I->grabFromDatabase('e107_user', 'user_customtitle', array('user_id' => $selfId)),
			'The self-edit did not take effect at all, so the permission assertion below proves nothing.');

		$perms = $I->grabFromDatabase('e107_user', 'user_perms', array('user_id' => $selfId));

		$I->assertSame('U0', (string) $perms,
			'A delegated administrator holding U0 promoted themselves to user_perms = '
			.var_export($perms, true).' by posting perms[] to their own row on the user edit route.');

		// The refusal writes user_admin and user_perms back out of $old_data on
		// every save by a caller without '3'. If $old_data ever arrives without
		// those keys the varset() defaults fire and this administrator demotes
		// themselves by saving their own profile, and every other assertion in
		// this Cest stays green because every other row starts at user_admin 0.
		$I->assertSame('1',
			(string) $I->grabFromDatabase('e107_user', 'user_admin', array('user_id' => $selfId)),
			'The delegated administrator lost their own admin flag by saving their own profile.');
	}

	/**
	 * A delegated administrator must not be able to rewrite another
	 * administrator's account.
	 *
	 * Pinning user_admin and user_perms answers "may this caller grant
	 * administrator status". It leaves "may this caller act on this account at
	 * all" unanswered, and the user editor writes user_password and user_email
	 * as readily as it writes a display name. A U0 administrator who posts a
	 * new password for the main administrator's row has taken the site whether
	 * or not a permission letter moved, and a new e-mail address plus password
	 * recovery is the same outcome one step slower.
	 *
	 * The rule already exists in this file, but only in the presentation layer:
	 * users_admin_form_ui::user_admin() renders read-only for a main
	 * administrator's row and options() withholds the row actions. This asserts
	 * it at the model, which is where the request actually lands.
	 */
	public function aDelegatedAdministratorCannotRewriteAnotherAdministrator(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a delegated administrator rewriting another administrator through the edit route');

		$targetId = $this->seedOtherAdmin($I);
		$before = (string) $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $targetId));

		$this->loginAsDelegatedAdmin($I, 'p7rpU0admin');

		$I->amOnPage(self::ROUTE_EDIT.$targetId);
		$I->seeResponseCodeIs(200);

		$payload = $this->editPayload($I, $targetId, self::OTHER_ADMIN);
		$payload['user_email'] = 'p7rp-taken-over@example.com';
		$payload['user_password_'.$targetId] = 'p7rp-Tak30ver-Pass';

		$I->sendPostRequest(self::ROUTE_EDIT.$targetId, $payload);

		$I->assertSame($before,
			(string) $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $targetId)),
			'A delegated administrator holding U0 and nothing else set a new password on '
			.self::OTHER_ADMIN.', an administrator holding "'.self::OTHER_ADMIN_PERMS.'". '
			.'users_admin_ui::beforeUpdate() hashes and stores whatever user_password_<id> was posted, '
			.'so pinning user_admin and user_perms leaves the account takeover standing.');

		$I->assertSame(self::OTHER_ADMIN.'@example.com',
			(string) $I->grabFromDatabase('e107_user', 'user_email', array('user_id' => $targetId)),
			'A delegated administrator holding U0 rewrote another administrator\'s e-mail address, '
			.'which is password recovery for that account.');
	}

	/**
	 * Positive control for the refusal above: an administrator who may modify
	 * admin perms must still be able to administer another administrator, or
	 * the guard has removed the only way the product has of doing so.
	 */
	public function anAdministratorWhoMayModifyAdminPermsStillEditsAnotherAdministrator(AcceptanceTester $I)
	{
		$I->wantTo('Keep another administrator editable by an administrator holding 3');

		$targetId = $this->seedOtherAdmin($I);
		$this->loginAsDelegatedAdmin($I, 'p7rpPermsadmin');

		$I->amOnPage(self::ROUTE_EDIT.$targetId);
		$I->seeResponseCodeIs(200);

		$payload = $this->editPayload($I, $targetId, self::OTHER_ADMIN);
		$payload['user_customtitle'] = 'p7rp legitimate admin edit';

		$I->sendPostRequest(self::ROUTE_EDIT.$targetId, $payload);

		$I->assertSame('p7rp legitimate admin edit',
			(string) $I->grabFromDatabase('e107_user', 'user_customtitle', array('user_id' => $targetId)),
			'An administrator holding 3 ("Modify Admin perms") can no longer edit another '
			.'administrator at all, so the refusal asserted above is a broken editor rather than an '
			.'authorisation boundary.');
	}

	/**
	 * Positive control for both halves of the grant.
	 *
	 * getperms('3') is "Modify Admin perms", and it is the permission both
	 * widgets already test before they render the permission table. An
	 * administrator who holds it must still be able to grant permissions, on
	 * the create route and on the edit route alike, or the fix has removed a
	 * feature rather than closed a hole.
	 *
	 * The account holds '4.3' rather than '0', so this is a delegated
	 * administrator and not the main administrator: a fix keyed on
	 * isMainAdmin() would leave the first assertion red.
	 */
	public function anAdministratorWhoMayModifyAdminPermsStillGrantsThem(AcceptanceTester $I)
	{
		$I->wantTo('Keep the administrator grant working for a delegated administrator holding 3');

		$victimId = $this->seedVictim($I);
		$this->loginAsDelegatedAdmin($I, 'p7rpPermsadmin');

		$I->amOnPage(self::ROUTE_ADD);
		$I->seeResponseCodeIs(200);

		// Anchors every dontSeeElement('input[name="perms[]"]') in this Cest to a
		// selector that is known to match something, so a rename of the widget
		// cannot make those negatives pass for the rest of time.
		$I->seeElement('input[name="perms[]"]');

		$I->sendPostRequest(self::ROUTE_ADD, $this->quickAddPayload($I, self::GRANTED_PERMS));

		$I->seeInDatabase('e107_user', array(
			'user_loginname' => self::CREATED_USER,
			'user_perms'     => self::GRANTED_PERMS,
			'user_admin'     => 1,
		));

		$I->amOnPage(self::ROUTE_EDIT.$victimId);
		$I->seeResponseCodeIs(200);

		$payload = $this->editPayload($I, $victimId, self::VICTIM_USER);
		$payload['perms'] = array(self::GRANTED_PERMS);
		$payload['user_admin'] = 1;

		$I->sendPostRequest(self::ROUTE_EDIT.$victimId, $payload);

		$I->assertSame(self::GRANTED_PERMS,
			(string) $I->grabFromDatabase('e107_user', 'user_perms', array('user_id' => $victimId)),
			'An administrator holding 3 ("Modify Admin perms") can no longer set user_perms through the '
			.'user edit route, so the refusals asserted elsewhere in this Cest are a broken feature '
			.'rather than an authorisation boundary.');

		$I->assertSame('1',
			(string) $I->grabFromDatabase('e107_user', 'user_admin', array('user_id' => $victimId)),
			'An administrator holding 3 can no longer set user_admin through the user edit route, which '
			.'is the only way the product has of making someone an administrator by hand.');
	}

	// -----------------------------------------------------------------
	// item 3: the route permission map
	// -----------------------------------------------------------------

	/**
	 * A delegated administrator who holds only the user ranks permission must
	 * not be able to create a user.
	 *
	 * users.php:37-40: "create" - getperms('4|U1|U0'). U3 is none of those.
	 *
	 * The request carries a valid CSRF token, taken off the ranks page this
	 * account does hold, and a valid identity-confirmation value, computed from
	 * the account's own user_pwchange. Both are session-scoped and neither is
	 * an authorisation barrier, so neither may be what answers this request or
	 * the test would go green for the wrong reason the moment either of them is
	 * tightened.
	 */
	public function aDelegatedAdministratorCannotReachTheQuickAddRoute(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the quick-add route to an administrator holding only the user-ranks permission');

		$this->loginAsDelegatedAdmin($I, 'p7rpU3admin');

		$payload = $this->quickAddPayload($I, null, $this->grabTokenFrom($I, self::ROUTE_RANKS));

		$I->sendPostRequest(self::ROUTE_ADD, $payload);

		$I->dontSeeInDatabase('e107_user', array('user_loginname' => self::CREATED_USER));
	}

	/**
	 * A delegated administrator who may add users must not be able to rewrite
	 * the site's user preferences.
	 *
	 * users.php:37-40: "options" - getperms('4|U2'). U1 is neither.
	 *
	 * e_admin_ui::PrefsSaveTrigger() pushes the posted set into the core
	 * preference object, and e_pref::save() then filters it against the
	 * preferences this page declares, so what a caller who reaches this route
	 * gets is those twelve preferences and not the whole site configuration.
	 * They live serialised inside one e107_core row, so they are read back
	 * through a probe rather than with seeInDatabase().
	 */
	public function aDelegatedAdministratorCannotReachTheUserOptionsRoute(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the user options route to an administrator holding only Quick Add User');

		$this->loginAsDelegatedAdmin($I, 'p7rpU1admin');

		$I->sendPostRequest(self::ROUTE_PREFS, array(
			'etrigger_save'   => 'Save Settings',
			'user_new_period' => self::PREF_ATTACK,
			'e-token'         => $this->grabTokenFrom($I, self::ROUTE_ADD),
		));

		$after = $this->dump($I);

		$I->assertSame((string) self::PREF_SAFE, (string) $after['user_new_period'],
			'A delegated administrator holding U1 ("Quick Add User") rewrote the core preference '
			.'user_new_period to '.var_export($after['user_new_period'], true).' through '
			.self::ROUTE_PREFS.'. users.php:37-40 has said that route needs 4|U2 since 2008.');
	}

	/**
	 * Positive control for the route above: the permission the map names must
	 * still open it.
	 */
	public function theUserOptionsRouteIsStillOpenToItsOwnPermission(AcceptanceTester $I)
	{
		$I->wantTo('Keep the user options route open to an administrator holding U2');

		$this->loginAsDelegatedAdmin($I, 'p7rpU2admin');

		$I->amOnPage(self::ROUTE_PREFS);
		$I->seeResponseCodeIs(200);

		$I->sendPostRequest(self::ROUTE_PREFS, array(
			'etrigger_save'   => 'Save Settings',
			'user_new_period' => self::PREF_ATTACK,
			'e-token'         => $this->grabToken($I),
		));

		$after = $this->dump($I);

		$I->assertSame((string) self::PREF_ATTACK, (string) $after['user_new_period'],
			'An administrator holding U2 ("Manage only user-options") can no longer save the user '
			.'options, so the refusal asserted for U1 is a broken page rather than a permission check.');
	}

	/**
	 * A delegated administrator who may add users must not be able to reach the
	 * user ranks route.
	 *
	 * users.php:37-40: "ranks" - getperms('4|U3'). U1 is neither.
	 *
	 * The side effect is a plain GET's: users_ranks_ui::init() seeds twelve
	 * e107_generic rows on the first view of mode=ranks&action=list. That makes
	 * this the one route here whose unauthorised write is directly readable
	 * with seeInDatabase(), and it is also a reminder that an admin route can
	 * write on a GET, so a fix that only guards POSTs would not close it.
	 */
	public function aDelegatedAdministratorCannotReachTheUserRanksRoute(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the user ranks route to an administrator holding only Quick Add User');

		$this->loginAsDelegatedAdmin($I, 'p7rpU1admin');

		$I->dontSeeInDatabase('e107_generic', array('gen_type' => 'user_rank_data'));

		$I->amOnPage(self::ROUTE_RANKS);

		$I->dontSeeInDatabase('e107_generic', array('gen_type' => 'user_rank_data'));
	}

	/**
	 * Positive control for the route above.
	 */
	public function theUserRanksRouteIsStillOpenToItsOwnPermission(AcceptanceTester $I)
	{
		$I->wantTo('Keep the user ranks route open to an administrator holding U3');

		$this->loginAsDelegatedAdmin($I, 'p7rpU3admin');

		$I->amOnPage(self::ROUTE_RANKS);
		$I->seeResponseCodeIs(200);

		$I->seeInDatabase('e107_generic', array('gen_type' => 'user_rank_data'));
	}

	/**
	 * A route permission is only a permission if it cannot be spelled around.
	 *
	 * e_admin_request::parseRequest() strips \W from the action and nothing
	 * else, so the case and the underscores of the query value survive.
	 * e_admin_dispatcher::checkAccess() then looks the route up as a string,
	 * while dispatch resolves the action through camelize() and PHP resolves a
	 * method name without regard to case. 'ADD' and 'a_d_d' both camelize onto
	 * AddSubmitTrigger(), so a map keyed on 'main/add' answers a question the
	 * caller chose the wording of.
	 *
	 * The same shape defeats e_admin_controller::checkAccess(), which compares
	 * the raw action against users_admin_ui::$disallow = array('create').
	 */
	public function theRoutePermissionMapIsNotBypassedByTheSpellingOfTheAction(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the quick-add route however the action is spelled');

		$this->loginAsDelegatedAdmin($I, 'p7rpU3admin');

		foreach(array('ADD', 'a_d_d') as $spelling)
		{
			$route = '/e107_admin/users.php?mode=main&action='.$spelling;
			$payload = $this->quickAddPayload($I, null, $this->grabTokenFrom($I, self::ROUTE_RANKS));

			$I->sendPostRequest($route, $payload);

			$I->dontSeeInDatabase('e107_user', array('user_loginname' => self::CREATED_USER));
		}
	}

	/**
	 * Positive control for the spellings above: they must really reach the
	 * trigger, or the refusals prove only that a nonsense action does nothing.
	 */
	public function anUpperCaseActionStillReachesTheRouteItNames(AcceptanceTester $I)
	{
		$I->wantTo('Keep an upper-case spelling of the quick-add action working for its own permission');

		$this->loginAsDelegatedAdmin($I, 'p7rpU1admin');

		$route = '/e107_admin/users.php?mode=main&action=ADD';

		$I->sendPostRequest($route, $this->quickAddPayload($I, null,
			$this->grabTokenFrom($I, self::ROUTE_ADD)));

		$I->seeInDatabase('e107_user', array('user_loginname' => self::CREATED_USER));
	}

	// -----------------------------------------------------------------
	// item 1, third call site: the batch
	// -----------------------------------------------------------------

	/**
	 * The batch is the third way this page writes to the user table, and it
	 * does not pass through beforeUpdate() or AddSubmitTrigger().
	 *
	 * e_admin_ui::_handleListBatch() takes the column name out of the posted
	 * etrigger_batch string and hands it to e_admin_tree_model::batchUpdate(),
	 * which binds the value and writes the column. Nothing between the two
	 * consults the field's own 'batch' declaration, so the batch menu the page
	 * renders is not the limit of what can be posted.
	 *
	 * Runs a batch this account is entitled to first, so a fix that simply
	 * refused every batch would leave the page's own feature broken and this
	 * test red.
	 */
	public function aDelegatedAdministratorCannotSetTheAdminFlagThroughTheListBatch(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an administrator grant from a delegated administrator through the list batch');

		$victimId = $this->seedVictim($I);
		$this->loginAsDelegatedAdmin($I, 'p7rpU0admin');

		$this->sendBatch($I, self::ROUTE_LIST, 'bool__user_hideemail__1', $victimId);

		$I->assertSame('1',
			(string) $I->grabFromDatabase('e107_user', 'user_hideemail', array('user_id' => $victimId)),
			'A batch on a column this page declares batchable no longer runs for a delegated '
			.'administrator, so the refusal below is a broken batch menu rather than a guard.');

		$this->sendBatch($I, self::ROUTE_LIST, 'bool__user_admin__1', $victimId);

		$I->assertSame('0',
			(string) $I->grabFromDatabase('e107_user', 'user_admin', array('user_id' => $victimId)),
			'A delegated administrator holding U0 set user_admin on '.self::VICTIM_USER
			.' by posting etrigger_batch=bool__user_admin__1 to '.self::ROUTE_LIST.'.');
	}

	/**
	 * The same batch on the grid route, which is a different trigger method.
	 *
	 * e_admin_ui declares ListBatchTrigger() and GridBatchTrigger() with
	 * identical bodies, and the trigger that runs is chosen from the action
	 * name. A guard installed on one of them protects one of two doors, which
	 * is the failure mode four published e107 advisories were closed with.
	 */
	public function aDelegatedAdministratorCannotSetTheAdminFlagThroughTheGridBatch(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an administrator grant from a delegated administrator through the grid batch');

		$victimId = $this->seedVictim($I);
		$this->loginAsDelegatedAdmin($I, 'p7rpU0admin');

		$this->sendBatch($I, self::ROUTE_GRID, 'bool__user_admin__1', $victimId);

		$I->assertSame('0',
			(string) $I->grabFromDatabase('e107_user', 'user_admin', array('user_id' => $victimId)),
			'A delegated administrator holding U0 set user_admin on '.self::VICTIM_USER
			.' through '.self::ROUTE_GRID.'. GridBatchTrigger() and ListBatchTrigger() are the same '
			.'handler reached under two action names.');

		$this->sendBatch($I, self::ROUTE_GRID, 'user_perms__'.self::ESCALATED_PERMS, $victimId);

		$I->assertSame('',
			(string) $I->grabFromDatabase('e107_user', 'user_perms', array('user_id' => $victimId)),
			'A delegated administrator holding U0 wrote user_perms through the grid batch.');
	}

	/**
	 * The batch writes whichever column the trigger string names, so the two
	 * privilege columns are not the boundary. A denylist of those two names
	 * leaves user_password and user_email reachable by the same request, and
	 * either of those on the main administrator's row is the takeover the two
	 * names were being protected against.
	 */
	public function aDelegatedAdministratorCannotRewriteAnUndeclaredColumnThroughTheBatch(AcceptanceTester $I)
	{
		$I->wantTo('Confine a batch to the columns the user list declares batchable');

		$victimId = $this->seedVictim($I);
		$before = (string) $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $victimId));

		$this->loginAsDelegatedAdmin($I, 'p7rpU0admin');

		$this->sendBatch($I, self::ROUTE_LIST, 'user_password__'.md5('p7rp-chosen'), $victimId);

		$I->assertSame($before,
			(string) $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $victimId)),
			'A delegated administrator set user_password through the batch by naming the column in '
			.'etrigger_batch. user_password carries no batch declaration and is on no batch menu, '
			.'but e_admin_ui::handleListBatch() never asks.');

		$this->sendBatch($I, self::ROUTE_LIST, 'user_email__p7rp-taken@example.com', $victimId);

		$I->assertSame(self::VICTIM_USER.'@example.com',
			(string) $I->grabFromDatabase('e107_user', 'user_email', array('user_id' => $victimId)),
			'A delegated administrator set user_email through the batch, which is password recovery '
			.'for whichever account was selected.');
	}

	/**
	 * Positive control for the batch guard: an administrator who may modify
	 * admin perms must still be able to run the batch that does so.
	 */
	public function anAdministratorWhoMayModifyAdminPermsStillRunsTheAdminBatch(AcceptanceTester $I)
	{
		$I->wantTo('Keep the admin-flag batch working for an administrator holding 3');

		$victimId = $this->seedVictim($I);
		$this->loginAsDelegatedAdmin($I, 'p7rpPermsadmin');

		$this->sendBatch($I, self::ROUTE_LIST, 'bool__user_admin__1', $victimId);

		$I->assertSame('1',
			(string) $I->grabFromDatabase('e107_user', 'user_admin', array('user_id' => $victimId)),
			'An administrator holding 3 can no longer set the admin flag through the batch, so the '
			.'refusals asserted above are a removed feature rather than an authorisation boundary.');
	}

	// -----------------------------------------------------------------
	// the maintenance write that runs whatever the route
	// -----------------------------------------------------------------

	/**
	 * users_admin_ui::init() runs on every route this file serves, including
	 * one the dispatcher has already rewritten to e403, so the
	 * 'main/maintenance' entry in the route map does not reach this write. The
	 * guard has to be at the branch, and this is the only place in the package
	 * where that is true.
	 *
	 * resend_to_all($resetPasswords) regenerates the password and the session
	 * key of every account awaiting activation and queues a mail to each of
	 * them, so before the guard any holder of U0, U1, U2 or U3 could trigger a
	 * site-wide password reset and mail-out from any users.php URL.
	 *
	 * Deliberately posted to the list route rather than to maintenance, so what
	 * is under test is the branch and not the map.
	 */
	public function aDelegatedAdministratorCannotResetEveryPendingPassword(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the mass password reset to an administrator who does not hold 4');

		$pendingId = $this->seedPendingMember($I);
		$before = (string) $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $pendingId));

		$this->loginAsDelegatedAdmin($I, 'p7rpU1admin');

		$I->sendPostRequest(self::ROUTE_LIST, array(
			'resendToAll'    => 1,
			'resetPasswords' => 1,
			'resendAge'      => 24,
			'e-token'        => $this->grabTokenFrom($I, self::ROUTE_ADD),
		));

		$I->assertSame($before,
			(string) $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $pendingId)),
			'A delegated administrator holding U1 ("Quick Add User") reset the password of every '
			.'account awaiting activation by posting resendToAll to '.self::ROUTE_LIST.'. '
			.'users_admin_ui::init() runs on every route, so the main/maintenance entry in the route '
			.'map never sees this request.');
	}

	/**
	 * Positive control for the branch above.
	 */
	public function theMassPasswordResetIsStillOpenToItsOwnPermission(AcceptanceTester $I)
	{
		$I->wantTo('Keep the mass password reset working for an administrator holding 4');

		$pendingId = $this->seedPendingMember($I);
		$before = (string) $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $pendingId));

		$this->loginAsDelegatedAdmin($I, 'p7rp4admin');

		$I->sendPostRequest(self::ROUTE_LIST, array(
			'resendToAll'    => 1,
			'resetPasswords' => 1,
			'resendAge'      => 24,
			'e-token'        => $this->grabTokenFrom($I, self::ROUTE_LIST),
		));

		$I->assertNotSame($before,
			(string) $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $pendingId)),
			'An administrator holding 4 ("Manage all user access and settings") can no longer run the '
			.'mass password reset, so the refusal asserted above is a broken feature.');
	}

	/**
	 * The quick-add permission table moved from getperms('4|U0') to
	 * getperms('3'), which is what the trigger honours and what the read and
	 * edit widgets already tested.
	 *
	 * That is an observable change to a shipped admin page in both directions,
	 * and neither of the accounts used elsewhere in this Cest can see it: the
	 * positive control holds '4.3' and satisfies the old condition and the new
	 * one alike. This account holds '4' alone, so it used to be served the
	 * table and now is not.
	 */
	public function theQuickAddPermissionTableFollowsTheTriggerAndNotTheOldCondition(AcceptanceTester $I)
	{
		$I->wantTo('Serve the quick-add permission table to the permission that may actually use it');

		$this->loginAsDelegatedAdmin($I, 'p7rp4admin');

		$I->amOnPage(self::ROUTE_ADD);
		$I->seeResponseCodeIs(200);

		$I->dontSeeElement('input[name="perms[]"]');
	}

	// -----------------------------------------------------------------
	// fixture
	// -----------------------------------------------------------------

	/**
	 * Post a batch the way the user list posts one: the trigger string names
	 * the operation and the column, and the selection is the checkbox column
	 * e_admin_ui reads through getFieldAttr('checkboxes', 'toggle').
	 *
	 * @param string $route
	 * @param string $trigger etrigger_batch value
	 * @param int $id row to apply it to
	 * @return void
	 */
	private function sendBatch(AcceptanceTester $I, $route, $trigger, $id)
	{
		$token = $this->grabTokenFrom($I, $route);

		$I->sendPostRequest($route, array(
			'etrigger_batch'    => $trigger,
			'e__execute_batch'  => 'Go',
			'multiselect'       => array($id => $id),
			'e-token'           => $token,
		));
	}

	/**
	 * The quick-add form's parameter set.
	 *
	 * 'ac' is computed rather than scraped, because the routes this Cest
	 * refuses are routes it must be able to post to without first loading the
	 * page that carries the field. It is md5 of the caller's user_pwchange, and
	 * every account here is seeded with self::PWCHANGE.
	 *
	 * @param string|null $perms permission code to ask for, or null for none
	 * @param string|null $token CSRF token, scraped from the current page if omitted
	 * @return array
	 */
	private function quickAddPayload(AcceptanceTester $I, $perms = null, $token = null)
	{
		$payload = array(
			'etrigger_submit' => 'Add user',
			'username'        => self::CREATED_USER,
			'loginname'       => self::CREATED_USER,
			'email'           => self::CREATED_USER.'@example.com',
			'realname'        => '',
			'password'        => 'p7rp-Str0ng-Pass',
			'sendconfemail'   => 0,
			'ac'              => md5((string) self::PWCHANGE),
			'e-token'         => $token === null ? $this->grabToken($I) : $token,
		);

		if($perms !== null)
		{
			$payload['perms'] = array($perms);
		}

		return $payload;
	}

	/**
	 * The user edit form's parameter set, less the permission field.
	 *
	 * Every text field the form renders is supplied, because e_admin_ui writes
	 * the posted set onto the model and an omitted field is an emptied one.
	 *
	 * @param int $id
	 * @param string $loginName
	 * @return array
	 */
	private function editPayload(AcceptanceTester $I, $id, $loginName)
	{
		return array(
			'etrigger_submit'       => 'update',
			'submit_value'          => $id,
			'__after_submit_action' => 'list',
			'user_name'             => $loginName,
			'user_loginname'        => $loginName,
			'user_email'            => $loginName.'@example.com',
			'user_login'            => '',
			'user_customtitle'      => '',
			'user_signature'        => '',
			'user_image'            => '',
			'user_hideemail'        => 1,
			'user_ban'              => 0,
			'e-token'               => $this->grabToken($I),
		);
	}

	/**
	 * The session's CSRF token, taken off a route the caller does hold.
	 *
	 * A token says who rendered the page, never who may write, and it is
	 * session-scoped rather than route-scoped: an administrator refused one
	 * route still has one from any other. Taking it from a page this account is
	 * entitled to is what stops the forgery check answering an authorisation
	 * test.
	 *
	 * @param string $route
	 * @return string
	 */
	private function grabTokenFrom(AcceptanceTester $I, $route)
	{
		$I->amOnPage($route);

		return $this->grabToken($I);
	}

	/**
	 * @return string the CSRF token on the page currently loaded
	 */
	private function grabToken(AcceptanceTester $I)
	{
		$source = $I->grabPageSource();
		$matches = array();

		if(!preg_match('/name=[\'"]e-token[\'"][^>]*value=[\'"]([^\'"]+)[\'"]/', $source, $matches))
		{
			throw new \RuntimeException('The current page rendered no e-token to post back.');
		}

		return $matches[1];
	}

	/**
	 * @return string the 'ac' identity-confirmation value on the page currently loaded
	 */
	private function grabConfirmToken(AcceptanceTester $I)
	{
		$source = $I->grabPageSource();
		$matches = array();

		if(!preg_match('/name=[\'"]ac[\'"][^>]*value=[\'"]([^\'"]*)[\'"]/', $source, $matches))
		{
			throw new \RuntimeException('The current page rendered no ac field.');
		}

		return $matches[1];
	}

	/**
	 * Seed an ordinary member for the edit route to be aimed at.
	 *
	 * @return int user id
	 */
	private function seedVictim(AcceptanceTester $I)
	{
		return (int) $I->haveInDatabase('e107_user', array(
			'user_name'      => self::VICTIM_USER,
			'user_loginname' => self::VICTIM_USER,
			'user_email'     => self::VICTIM_USER.'@example.com',
			'user_password'  => md5(self::VICTIM_USER),
			'user_join'      => 1262304000,
			'user_class'     => '',
			'user_admin'     => 0,
			'user_perms'     => '',
			'user_pwchange'  => self::PWCHANGE,
			'user_xup'       => '',
			'user_prefs'     => '',
			'user_signature' => '',
			'user_realm'     => '',
		));
	}

	/**
	 * Seed a second administrator for the routes that act on somebody else's
	 * account.
	 *
	 * @return int user id
	 */
	private function seedOtherAdmin(AcceptanceTester $I)
	{
		return (int) $I->haveInDatabase('e107_user', array(
			'user_name'      => self::OTHER_ADMIN,
			'user_loginname' => self::OTHER_ADMIN,
			'user_email'     => self::OTHER_ADMIN.'@example.com',
			'user_password'  => md5(self::OTHER_ADMIN),
			'user_join'      => 1262304000,
			'user_class'     => '',
			'user_admin'     => 1,
			'user_perms'     => self::OTHER_ADMIN_PERMS,
			'user_pwchange'  => self::PWCHANGE,
			'user_xup'       => '',
			'user_prefs'     => '',
			'user_signature' => '',
			'user_realm'     => '',
		));
	}

	/**
	 * Seed an account awaiting activation: user_ban = 2 is what resend_to_all()
	 * selects on, and user_join has to be older than the age it is given.
	 *
	 * @return int user id
	 */
	private function seedPendingMember(AcceptanceTester $I)
	{
		return (int) $I->haveInDatabase('e107_user', array(
			'user_name'      => self::PENDING_USER,
			'user_loginname' => self::PENDING_USER,
			'user_email'     => self::PENDING_USER.'@example.com',
			'user_password'  => md5(self::PENDING_USER),
			'user_sess'      => 'p7rppendingsess',
			'user_join'      => 1262304000,
			'user_class'     => '',
			'user_ban'       => 2,
			'user_admin'     => 0,
			'user_perms'     => '',
			'user_pwchange'  => self::PWCHANGE,
			'user_xup'       => '',
			'user_prefs'     => '',
			'user_signature' => '',
			'user_realm'     => '',
		));
	}

	/**
	 * Seed and sign in as an administrator holding one delegated permission and
	 * nothing else.
	 *
	 * Seeded on every call, never memoised: Codeception shares one Cest
	 * instance across its test methods and removes every haveInDatabase() row
	 * after each of them, so a cached user id outlives the user it names.
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
			'user_pwchange'  => self::PWCHANGE,
			'user_xup'       => '',
			'user_prefs'     => '',
			'user_signature' => '',
			'user_realm'     => '',
		));

		$I->loginAsAdmin($loginName, $loginName);
		$I->dontSeeElement('input[name=authpass]');
	}

	/**
	 * Put the preferences this Cest writes back where it found them, drop the
	 * accounts and rank rows it created through the application, and clear the
	 * request ban: e107 bans an address after 50 requests in a window and every
	 * container request arrives from the same bridge address.
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
	 * @return array the core preferences this Cest watches
	 */
	private function dump(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->amOnPage('/'.self::PROBE_FILE.'?act=dump');

		$body = $I->grabPageSource();
		$matches = array();

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

	/**
	 * @return string
	 */
	private function probeSource()
	{
		$safe = self::PREF_SAFE;

		return <<<PHP
<?php
// Fixture for 0040_AdminRoutePermsCest. Removed again in the Cest's _after().
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');

\$core = e107::getConfig('core');
\$db = e107::getDb();

switch(isset(\$_GET['act']) ? \$_GET['act'] : '')
{
	case 'reset':
		\$db->delete('online');
		\$db->delete('banlist', 'banlist_bantype IN (2, -2)');

		// Accounts this Cest created through the application. Codeception's Db
		// module rolls back only the rows it inserted itself.
		\$db->delete('user', "user_loginname LIKE 'p7rp%'");

		// Seeded by users_ranks_ui::init() on the first view of the ranks route.
		\$db->delete('generic', "gen_type = 'user_rank_data'");

		// Queued by resend_to_all(), which runs with mail_force_queue so nothing
		// is actually sent, but the rows outlive the request that made them.
		\$db->delete('mail_recipients', "mail_recipient_name LIKE 'p7rp%'");
		\$db->delete('mail_content', "mail_title = 'RESEND ACTIVATION'");

		\$core->set('user_new_period', $safe)->save(false, true, false);

		echo "PROBE_OK\n";
		break;

	case 'dump':
		// Core preferences live serialised inside a single e107_core row, so
		// there is nothing for seeInDatabase() to read. Boot the application
		// and ask it instead.
		echo 'PROBE_DUMP'.json_encode(array(
			'user_new_period' => \$core->get('user_new_period'),
		))."PROBE_END\n";
		break;

	default:
		echo "PROBE_UNKNOWN_ACTION\n";
}
PHP;
	}
}
