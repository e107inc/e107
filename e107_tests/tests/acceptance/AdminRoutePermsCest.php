<?php

/**
 * Delegated-administrator escalation in e107_admin/users.php, and the route
 * permission map the file has carried a TODO for since 2008.
 *
 * The package before this one answered "is this request authenticated as an
 * administrator at all". This one answers the next question down: "does this
 * administrator hold the permission this route requires".
 *
 * Three separate defects live in the same file.
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
 * The third is that the user class is written by three routes that never vet
 * it. users_admin_ui::checkAllowed() refuses a class whose userclass_editclass
 * the caller does not hold, and the dedicated Set user class page runs it over
 * every posted id. e_admin_ui::InlineAjaxPage() writes the posted value
 * straight onto the model; the batch reaches the column through
 * handleCommaBatch(), which saves the tree model without ever calling
 * beforeUpdate(); and AddSubmitTrigger() writes it out of $_POST['class'] on
 * its way to the insert. A delegated administrator holding '4', U0 or U1 could
 * put any account, their own included, into Main Admin's class.
 *
 * The handler underneath this file has a defect of its own: the guard
 * e_admin_controller_ui carried for a userclass batch handed the whole class
 * row to checkClass() rather than its userclass_editclass, so it answered on
 * whichever column came first rather than on the one that says who may manage
 * a class, on every list in core that has such a batch.
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
 * @see e107_admin/users.php  users_admin_ui::checkAllowed(), the user class rule
 * @see e107_handlers/admin_ui.php  e_admin_controller_ui::_handleListBatch(), the userclass batch
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

	/** The superseded ranks editor on the main mode, carrying the same writes. */
	const ROUTE_RANKS_LEGACY = '/e107_admin/users.php?mode=main&action=ranks';

	const ROUTE_EDIT = '/e107_admin/users.php?mode=main&action=edit&id=';

	/** The dedicated Set user class page. The row id goes on the end. */
	const ROUTE_USERCLASS = '/e107_admin/users.php?mode=main&action=userclass&id=';

	/** The user list, and the batch that hangs off it. */
	const ROUTE_LIST = '/e107_admin/users.php?mode=main&action=list';

	/** The same listing under its other action name, with its own batch trigger. */
	const ROUTE_GRID = '/e107_admin/users.php?mode=main&action=grid';

	/** The inline editor's endpoint. The row id goes on the end. */
	const ROUTE_INLINE = '/e107_admin/users.php?mode=main&action=inline&ajax_used=1&id=';

	/** Welcome messages: a userclass batch on another table, behind another permission. */
	const ROUTE_WMESSAGE = '/e107_admin/wmessage.php?mode=main&action=list';

	/** The welcome-message edit form. The row id goes on the end. */
	const ROUTE_WMESSAGE_EDIT = '/e107_admin/wmessage.php?mode=main&action=edit&id=';

	/** The welcome-message list's inline editor, less the row id. */
	const ROUTE_WMESSAGE_INLINE = '/e107_admin/wmessage.php?mode=main&action=inline&ajax_used=1&id=';

	/** Custom page and menu edit routes, less the row id. */
	const ROUTE_PAGE_EDIT = '/e107_admin/cpage.php?mode=page&action=edit&id=';
	const ROUTE_MENU_EDIT = '/e107_admin/cpage.php?mode=menu&action=edit&id=';
	const ROUTE_OVERVIEW_EDIT = '/e107_admin/cpage.php?mode=overview&action=edit&id=';

	/** The custom page and custom menu lists, each its own permission's way in. */
	const ROUTE_PAGE_LIST = '/e107_admin/cpage.php?mode=page&action=list';
	const ROUTE_MENU_LIST = '/e107_admin/cpage.php?mode=menu&action=list';

	/** The overview of every custom page and menu row, which either permission opens. */
	const ROUTE_OVERVIEW_LIST = '/e107_admin/cpage.php?mode=overview&action=list';

	/** The custom page and custom menu lists' inline editors, less the row id. */
	const ROUTE_PAGE_INLINE = '/e107_admin/cpage.php?mode=page&action=inline&ajax_used=1&id=';
	const ROUTE_MENU_INLINE = '/e107_admin/cpage.php?mode=menu&action=inline&ajax_used=1&id=';
	const ROUTE_OVERVIEW_INLINE = '/e107_admin/cpage.php?mode=overview&action=inline&ajax_used=1&id=';

	/** The custom menu create route, which the admin menu does not draw, and the custom page grid. */
	const ROUTE_MENU_CREATE = '/e107_admin/cpage.php?mode=menu&action=create';
	const ROUTE_PAGE_GRID = '/e107_admin/cpage.php?mode=page&action=grid';

	/** The columns of the menu half a create form posts beside the titles, none of which takes NULL. */
	const MENU_CREATE_COLUMNS = array(
		'menu_template'     => 'default',
		'menu_class'        => 0,
		'menu_button_text'  => '',
		'menu_button_url'   => '',
		'menu_icon'         => '',
		'menu_image'        => '',
	);

	/** The columns of both halves the custom page create form posts beside the titles, none of which takes NULL. */
	const PAGE_CREATE_COLUMNS = array(
		'page_subtitle'     => '',
		'page_chapter'      => 0,
		'page_template'     => 'default',
		'page_author'       => 1,
		'page_datestamp'    => 1262304000,
		'page_class'        => 0,
		'page_rating_flag'  => 0,
		'page_comment_flag' => 0,
		'page_password'     => '',
		'page_metatitle'    => '',
		'page_metakeys'     => '',
		'page_metaimage'    => '',
	) + self::MENU_CREATE_COLUMNS;

	/** The custom page create route, the chapter create route and the custom page options. */
	const ROUTE_PAGE_CREATE = '/e107_admin/cpage.php?mode=page&action=create';
	const ROUTE_CHAPTER_CREATE = '/e107_admin/cpage.php?mode=cat&action=create';
	const ROUTE_CHAPTER_LIST = '/e107_admin/cpage.php?mode=cat&action=list';
	const ROUTE_PAGE_PREFS = '/e107_admin/cpage.php?mode=page&action=prefs';

	/** Ban list routes, less the row id where they take one. */
	const ROUTE_BANLIST_EDIT = '/e107_admin/banlist.php?mode=main&action=edit&id=';
	const ROUTE_WHITELIST_EDIT = '/e107_admin/banlist.php?mode=white&action=edit&id=';

	/**
	 * Every list that shares its table with another, by route, with the row type it shows and a
	 * row type of the same table it must not.
	 */
	const SHARED_TABLE_LISTS = array(
		'/e107_admin/wmessage.php?mode=main&action=list'                   => array('wmessage', 'failed_login'),
		'/e107_admin/users.php?mode=ranks&action=list'                     => array('user_rank_data', 'wmessage'),
		'/e107_admin/banlist.php?mode=failed&action=list'                  => array('failed_login', 'user_rank_data'),
		'/e107_plugins/forum/forum_admin.php?mode=report&action=list'      => array('reported_post', 'wmessage'),
		'/e107_plugins/download/admin_download.php?mode=broken&action=list' => array('Broken Download', 'reported_post'),
	);

	/** The welcome-message create form. */
	const ROUTE_WMESSAGE_CREATE = '/e107_admin/wmessage.php?mode=main&action=create';

	/** The user ranks list's inline editor, less the row id. */
	const ROUTE_RANKS_INLINE = '/e107_admin/users.php?mode=ranks&action=inline&ajax_used=1&id=';

	/** The gallery's category edit form, less the row id. */
	const ROUTE_GALLERY_CATEGORY_EDIT = '/e107_plugins/gallery/admin_gallery.php?mode=main&action=edit&id=';

	/** The custom menu list's drag-and-drop sort. */
	const ROUTE_MENU_SORT = '/e107_admin/cpage.php?mode=menu&action=sort&ajax_used=1';

	/** The custom page list's drag-and-drop sort and filter routes. */
	const ROUTE_PAGE_SORT = '/e107_admin/cpage.php?mode=page&action=sort&ajax_used=1';
	const ROUTE_PAGE_FILTER = '/e107_admin/cpage.php?mode=page&action=filter&ajax_used=1';

	/** The fields a custom page create form loads when its chapter changes. */
	const ROUTE_PAGE_CHAPTER_CHANGE = '/e107_admin/cpage.php?mode=page&action=chapter-change&ajax_used=1';

	/** The chapter list's drag-and-drop sort and its inline editor, less the row id. */
	const ROUTE_CHAPTER_SORT = '/e107_admin/cpage.php?mode=cat&action=sort&ajax_used=1';
	const ROUTE_CHAPTER_INLINE = '/e107_admin/cpage.php?mode=cat&action=inline&ajax_used=1&id=';

	/** wmessage.php pages its list at ten rows. */
	const WMESSAGE_PER_PAGE = 10;

	/**
	 * Main Admin, whose userclass_editclass is itself, so only a main administrator manages it.
	 *
	 * @see e107_core/xml/default_install.xml the userclass_classes a stock site is installed with
	 */
	const BARRED_CLASS = 250;

	/** PRIVATEMENU, whose userclass_editclass is 254, a class every administrator holds. */
	const MANAGED_CLASS = 1;

	/**
	 * Members. The stock install seeds the row with userclass_editclass 250, and
	 * a site missing the row falls back to the same, so a main administrator is
	 * the only one who manages it until somebody edits that field. Every
	 * logged-in account holds the class at runtime whether or not the column
	 * says so, which is why losing it from the column costs no access.
	 */
	const MEMBER_CLASS = 253;

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
		'p7rpMadmin'     => 'M',    // welcome messages, and nothing on the user list at all
		'p7rp5admin'     => '5',    // custom pages
		'p7rpJadmin'     => 'J',    // custom menus
		'p7rp5J1admin'   => '5.J1', // custom pages, and deleting custom pages and menus
	);

	/** A second administrator, for the routes that act on somebody else's account. */
	const OTHER_ADMIN = 'p7rpotheradmin';

	/** What that second administrator holds. */
	const OTHER_ADMIN_PERMS = 'H';

	/**
	 * Seeded session key for that second administrator. Non-empty on purpose: Verify blanks
	 * user_sess, and a row seeded with '' would satisfy the refusal assertion either way.
	 */
	const OTHER_ADMIN_SESS = 'p7rpotheradminsess';

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
		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
		$I->startFollowingRedirects();
		$this->reset($I);
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$this->reset($I);
		$I->dropPluginProbe();
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

	/** The edit route writes the row it loaded, so a member's edit form cannot be turned onto an administrator. */
	public function aDelegatedAdministratorCannotTurnAMembersEditOntoAnAdministrator(AcceptanceTester $I)
	{
		$I->wantTo('Hold a delegated administrator\'s edit of a member to that member');

		$targetId = $this->seedOtherAdmin($I);
		$victimId = $this->seedVictim($I);
		$before = (string) $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $targetId));

		$this->loginAsDelegatedAdmin($I, 'p7rpU0admin');

		$I->amOnPage(self::ROUTE_EDIT.$victimId);

		$payload = $this->editPayload($I, $victimId, self::OTHER_ADMIN);
		$payload['user_id'] = $targetId;
		$payload['user_email'] = 'p7rp-taken-over@example.com';
		$payload['user_password_'.$victimId] = 'p7rp-Tak30ver-Pass';

		$I->sendPostRequest(self::ROUTE_EDIT.$victimId, $payload);

		$I->assertSame($before,
			(string) $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $targetId)));
		$I->seeInDatabase('e107_user', array(
			'user_id'    => $targetId,
			'user_email' => self::OTHER_ADMIN.'@example.com',
			'user_admin' => 1,
		));
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
			'e-token'         => $I->grabToken(),
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

	/** The superseded ranks editor's update replaces every rank, so it needs the ranks permission. */
	public function aDelegatedAdministratorCannotUpdateRanksInTheSupersededEditor(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the superseded ranks update to an administrator holding only Quick Add User');

		$rank = $this->seedGenericRow($I, 'user_rank_data', 'p7rp rank', 0);

		$this->loginAsDelegatedAdmin($I, 'p7rpU1admin');

		$I->sendPostRequest(self::ROUTE_RANKS_LEGACY, array(
			'e-token'         => $this->grabTokenFrom($I, self::ROUTE_ADD),
			'etrigger_update' => 'update',
			'op'              => array('comments' => '*'),
			'val'             => array('comments' => '5'),
			'calc_name'       => array('main_admin' => 'p7rp', 'admin' => 'p7rp'),
			'calc_pfx'        => array('main_admin' => 1, 'admin' => 1),
			'calc_img'        => array('main_admin' => '', 'admin' => ''),
		));

		$I->seeInDatabase('e107_generic', array('gen_id' => $rank));
	}

	/** The superseded ranks editor's delete needs the ranks permission too. */
	public function aDelegatedAdministratorCannotDeleteRanksInTheSupersededEditor(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the superseded ranks delete to an administrator holding only Quick Add User');

		$rank = $this->seedGenericRow($I, 'user_rank_data', 'p7rp rank', 0);

		$this->loginAsDelegatedAdmin($I, 'p7rpU1admin');

		$I->sendPostRequest(self::ROUTE_RANKS_LEGACY, array(
			'e-token'         => $this->grabTokenFrom($I, self::ROUTE_ADD),
			'etrigger_delete' => array($rank => $rank),
		));

		$I->seeInDatabase('e107_generic', array('gen_id' => $rank));
	}

	/** Positive control: the ranks permission still reaches the superseded editor. */
	public function theSupersededRanksEditorIsStillOpenToItsOwnPermission(AcceptanceTester $I)
	{
		$I->wantTo('Keep the superseded ranks editor open to an administrator holding U3');

		$rank = $this->seedGenericRow($I, 'user_rank_data', 'p7rp rank', 0);

		$this->loginAsDelegatedAdmin($I, 'p7rpU3admin');

		$this->postWithToken($I, self::ROUTE_RANKS_LEGACY, array(
			'etrigger_delete' => array($rank => $rank),
		));

		$I->dontSeeInDatabase('e107_generic', array('gen_id' => $rank));
	}

	/** The superseded ranks editor deletes rank rows and no other kind of e107_generic row. */
	public function theSupersededRanksEditorDeletesOnlyRankRows(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the superseded ranks editor an e107_generic row that is not a rank');

		$counter = $this->seedGenericRow($I, 'failed_login', '203.0.113.7');
		$message = $this->seedGenericRow($I, 'wmessage', 'p7rp welcome');

		$this->loginAsDelegatedAdmin($I, 'p7rpU3admin');

		foreach(array($counter, $message) as $id)
		{
			$this->postWithToken($I, self::ROUTE_RANKS_LEGACY, array(
				'etrigger_delete' => array($id => $id),
			));

			$I->seeInDatabase('e107_generic', array('gen_id' => $id));
		}
	}

	/** A list's row delete reaches only the rows its own query selects. */
	public function aWelcomeMessageAdministratorCannotDeleteRowsAnotherListOwns(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a welcome-message administrator the shared-table rows another permission owns');

		$counter = $this->seedGenericRow($I, 'failed_login', '203.0.113.7');
		$rank = $this->seedGenericRow($I, 'user_rank_data', 'p7rp rank', 0);

		$this->loginAsDelegatedAdmin($I, 'p7rpMadmin');

		foreach(array($counter, $rank) as $id)
		{
			$this->sendConfirmedRowDelete($I, self::ROUTE_WMESSAGE, $id);

			$I->seeInDatabase('e107_generic', array('gen_id' => $id));
		}
	}

	/** The User Ranks list's row delete reaches only rank rows. */
	public function aRanksAdministratorCannotDeleteRowsAnotherListOwns(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a user-ranks administrator the shared-table rows another permission owns');

		$counter = $this->seedGenericRow($I, 'failed_login', '203.0.113.7');
		$message = $this->seedGenericRow($I, 'wmessage', 'p7rp welcome');

		$this->loginAsDelegatedAdmin($I, 'p7rpU3admin');

		foreach(array($counter, $message) as $id)
		{
			$this->sendConfirmedRowDelete($I, self::ROUTE_RANKS, $id);

			$I->seeInDatabase('e107_generic', array('gen_id' => $id));
		}
	}

	/** Positive control: the welcome-message delete still removes a welcome message. */
	public function aWelcomeMessageAdministratorStillDeletesAWelcomeMessage(AcceptanceTester $I)
	{
		$I->wantTo('Keep the welcome-message delete working on a welcome message');

		$message = $this->seedGenericRow($I, 'wmessage', 'p7rp welcome');

		$this->loginAsDelegatedAdmin($I, 'p7rpMadmin');

		$this->sendConfirmedRowDelete($I, self::ROUTE_WMESSAGE, $message);

		$I->dontSeeInDatabase('e107_generic', array('gen_id' => $message));
	}

	/** Positive control: a row on a list page after the first still deletes, through the list form's own action. */
	public function aRowOnASecondListPageIsStillDeleted(AcceptanceTester $I)
	{
		$I->wantTo('Keep the row delete working on a list page after the first');

		for($i = 0; $i <= self::WMESSAGE_PER_PAGE * 2; $i++)
		{
			$this->seedGenericRow($I, 'wmessage', 'p7rp paged '.$i);
		}

		$this->loginAsDelegatedAdmin($I, 'p7rpMadmin');

		$I->amOnPage(self::ROUTE_WMESSAGE.'&from='.self::WMESSAGE_PER_PAGE);

		$action = $I->grabAttributeFrom(array('css' => "form[id$='-list-form']"), 'action');
		$I->assertStringContainsString('from='.self::WMESSAGE_PER_PAGE, $action,
			'The list form on page two posts without that page\'s offset, so the delete below is not the paged one.');

		$target = (int) $I->grabAttributeFrom(array('css' => 'button.delete'), 'value');
		$I->assertGreaterThan(0, $target, 'The second page offered no row delete button to exercise.');

		$this->sendConfirmedRowDelete($I, $action, $target);

		$I->dontSeeInDatabase('e107_generic', array('gen_id' => $target));
	}

	/** A list's batch writes reach only the rows its own query selects. */
	public function aWelcomeMessageAdministratorCannotBatchWriteRowsAnotherListOwns(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a welcome-message batch write on the shared-table rows another permission owns');

		$counter = $this->seedGenericRow($I, 'failed_login', '203.0.113.7');
		$rank = $this->seedGenericRow($I, 'user_rank_data', 'p7rp rank', 0);

		$this->loginAsDelegatedAdmin($I, 'p7rpMadmin');

		foreach(array($counter, $rank) as $id)
		{
			$this->sendBatch($I, self::ROUTE_WMESSAGE, 'gen_intdata__254', $id, 'e-multiselect');

			$I->seeInDatabase('e107_generic', array('gen_id' => $id, 'gen_intdata' => 0));
		}
	}

	/** A list's batch copy reaches only the rows its own query selects. */
	public function aWelcomeMessageAdministratorCannotBatchCopyRowsAnotherListOwns(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a welcome-message batch copy of the shared-table rows another permission owns');

		$counter = $this->seedGenericRow($I, 'failed_login', '203.0.113.7');

		$this->loginAsDelegatedAdmin($I, 'p7rpMadmin');
		$this->sendBatch($I, self::ROUTE_WMESSAGE, 'copy', $counter, 'e-multiselect');

		$I->seeNumRecords(1, 'e107_generic', array('gen_type' => 'failed_login', 'gen_ip' => '203.0.113.7'));
	}

	/** A list's batch export reaches only the rows its own query selects. */
	public function aWelcomeMessageAdministratorCannotBatchExportRowsAnotherListOwns(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a welcome-message batch export of the shared-table rows another permission owns');

		$counter = $this->seedGenericRow($I, 'failed_login', '203.0.113.7');

		$this->loginAsDelegatedAdmin($I, 'p7rpMadmin');
		$this->sendBatch($I, self::ROUTE_WMESSAGE, 'export', $counter, 'e-multiselect');

		$I->dontSeeInSource('203.0.113.7');
	}

	/** Positive control: the welcome-message batch still writes a welcome message. */
	public function aWelcomeMessageAdministratorStillBatchWritesAWelcomeMessage(AcceptanceTester $I)
	{
		$I->wantTo('Keep the welcome-message batch write working on a welcome message');

		$message = $this->seedGenericRow($I, 'wmessage', 'p7rp welcome');

		$this->loginAsDelegatedAdmin($I, 'p7rpMadmin');
		$this->sendBatch($I, self::ROUTE_WMESSAGE, 'gen_intdata__254', $message, 'e-multiselect');

		$I->seeInDatabase('e107_generic', array('gen_id' => $message, 'gen_intdata' => 254));
	}

	/** A list's edit form writes the row it loaded, whatever primary key the request carries. */
	public function aWelcomeMessageEditCannotBeTurnedOnAnotherListsRow(AcceptanceTester $I)
	{
		$I->wantTo('Hold a welcome-message edit to the welcome message it loaded');

		$message = $this->seedGenericRow($I, 'wmessage', 'p7rp welcome');
		$counter = $this->seedGenericRow($I, 'failed_login', '203.0.113.7');

		$this->loginAsDelegatedAdmin($I, 'p7rpMadmin');

		$this->postWithToken($I, self::ROUTE_WMESSAGE_EDIT.$message, array(
			'etrigger_submit' => 'update',
			'gen_id'          => $counter,
			'gen_type'        => 'wmessage',
			'gen_ip'          => 'p7rp edited',
			'gen_intdata'     => '0',
			'gen_chardata'    => 'p7rp welcome',
		));

		$I->seeInDatabase('e107_generic', array('gen_id' => $counter, 'gen_type' => 'failed_login', 'gen_ip' => '203.0.113.7'));
		$I->seeInDatabase('e107_generic', array('gen_id' => $message, 'gen_ip' => 'p7rp edited'));
	}

	/** A list's edit form loads only the rows its own query selects. */
	public function aWelcomeMessageAdministratorCannotEditRowsAnotherListOwns(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a welcome-message edit of the shared-table rows another permission owns');

		$counter = $this->seedGenericRow($I, 'failed_login', '203.0.113.7');
		$rank = $this->seedGenericRow($I, 'user_rank_data', 'p7rp rank', 0);

		$this->loginAsDelegatedAdmin($I, 'p7rpMadmin');

		foreach(array($counter => 'failed_login', $rank => 'user_rank_data') as $id => $type)
		{
			$this->postWithToken($I, self::ROUTE_WMESSAGE_EDIT.$id, array(
				'etrigger_submit' => 'update',
				'gen_type'        => $type,
				'gen_ip'          => 'p7rp edited',
				'gen_intdata'     => '0',
				'gen_chardata'    => 'p7rp edited',
			));

			$I->dontSeeInDatabase('e107_generic', array('gen_id' => $id, 'gen_ip' => 'p7rp edited'));
		}
	}

	/** A list's inline editor loads only the rows its own query selects. */
	public function aWelcomeMessageAdministratorCannotInlineEditRowsAnotherListOwns(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a welcome-message inline edit of the shared-table rows another permission owns');

		$this->seedGenericRow($I, 'wmessage', 'p7rp welcome');
		$counter = $this->seedGenericRow($I, 'failed_login', '203.0.113.7');
		$rank = $this->seedGenericRow($I, 'user_rank_data', 'p7rp rank', 0);

		$this->loginAsDelegatedAdmin($I, 'p7rpMadmin');

		foreach(array($counter, $rank) as $id)
		{
			$this->sendInline($I, $id, 'gen_ip', 'p7rp edited', self::ROUTE_WMESSAGE, self::ROUTE_WMESSAGE_INLINE);

			$I->dontSeeInDatabase('e107_generic', array('gen_id' => $id, 'gen_ip' => 'p7rp edited'));
		}
	}

	/** Positive control: the welcome-message edit form and inline editor still write a welcome message. */
	public function aWelcomeMessageAdministratorStillEditsAWelcomeMessage(AcceptanceTester $I)
	{
		$I->wantTo('Keep the welcome-message edit form and inline editor working on a welcome message');

		$message = $this->seedGenericRow($I, 'wmessage', 'p7rp welcome');

		$this->loginAsDelegatedAdmin($I, 'p7rpMadmin');

		$this->postWithToken($I, self::ROUTE_WMESSAGE_EDIT.$message, array(
			'etrigger_submit' => 'update',
			'gen_type'        => 'wmessage',
			'gen_ip'          => 'p7rp edited',
			'gen_intdata'     => '0',
			'gen_chardata'    => 'p7rp edited',
		));
		$I->seeInDatabase('e107_generic', array('gen_id' => $message, 'gen_ip' => 'p7rp edited'));

		$this->sendInline($I, $message, 'gen_ip', 'p7rp inline', self::ROUTE_WMESSAGE, self::ROUTE_WMESSAGE_INLINE);
		$I->seeInDatabase('e107_generic', array('gen_id' => $message, 'gen_ip' => 'p7rp inline'));
	}

	/** Positive control: the white list's edit form loads a white-list entry, and the ban list's does not. */
	public function theWhiteListEditFormStillLoadsAWhiteListEntry(AcceptanceTester $I)
	{
		$I->wantTo('Keep the white-list edit form loading white-list entries');

		$entry = (int) $I->haveInDatabase('e107_banlist', array(
			'banlist_ip'         => '198.51.100.77',
			'banlist_bantype'    => 100,
			'banlist_datestamp'  => 1262304000,
			'banlist_banexpires' => 0,
			'banlist_admin'      => 1,
			'banlist_reason'     => 'p7rp white',
			'banlist_notes'      => '',
		));

		$this->loginAsDelegatedAdmin($I, 'p7rp4admin');

		$I->amOnPage(self::ROUTE_WHITELIST_EDIT.$entry);
		$I->seeInSource('198.51.100.77');

		$I->amOnPage(self::ROUTE_BANLIST_EDIT.$entry);
		$I->dontSeeInSource('198.51.100.77');
	}

	/** Positive control: each list over a shared table still shows its own rows, and only those. */
	public function eachSharedTableListStillShowsItsOwnRows(AcceptanceTester $I)
	{
		$I->wantTo('Keep every shared-table list showing its own rows and no others');

		$I->havePluginInstalled('forum');
		$I->havePluginInstalled('download');
		$I->loginAsAdmin();

		foreach(self::SHARED_TABLE_LISTS as $route => $types)
		{
			list($own, $other) = $types;

			$this->seedGenericRow($I, $own, 'p7rp own '.$own);
			$this->seedGenericRow($I, $other, 'p7rp other '.$other);

			$I->amOnPage($route);
			$I->see('p7rp own '.$own);
			$I->dontSee('p7rp other '.$other);
		}
	}

	/** A list's edit form keeps the row type its scope pins, and still saves the row's other fields. */
	public function aWelcomeMessageCannotBeMovedToAnotherListThroughItsEditForm(AcceptanceTester $I)
	{
		$I->wantTo('Keep a welcome message a welcome message when its edit form posts the row type of another list');

		$message = $this->seedGenericRow($I, 'wmessage', 'p7rp welcome');

		$this->loginAsDelegatedAdmin($I, 'p7rpMadmin');

		$this->postWithToken($I, self::ROUTE_WMESSAGE_EDIT.$message, array(
			'etrigger_submit' => 'update',
			'gen_type'        => 'user_rank_data',
			'gen_ip'          => 'p7rp edited',
			'gen_intdata'     => '0',
			'gen_chardata'    => 'p7rp edited',
		));

		$I->seeInDatabase('e107_generic', array('gen_id' => $message, 'gen_ip' => 'p7rp edited'));
		$I->seeInDatabase('e107_generic', array('gen_id' => $message, 'gen_type' => 'wmessage'));
	}

	/** A list's create form stores the row type its scope pins, whatever it is posted. */
	public function aWelcomeMessageCreatedAsAnotherListsRowIsStillAWelcomeMessage(AcceptanceTester $I)
	{
		$I->wantTo('Store a welcome message as a welcome message when its create form posts the row type of another list');

		$this->loginAsDelegatedAdmin($I, 'p7rpMadmin');

		$this->postWithToken($I, self::ROUTE_WMESSAGE_CREATE, array(
			'etrigger_submit' => 'create',
			'gen_type'        => 'user_rank_data',
			'gen_ip'          => 'p7rp created',
			'gen_intdata'     => '0',
			'gen_chardata'    => 'p7rp created',
		));

		$I->seeInDatabase('e107_generic', array('gen_ip' => 'p7rp created'));
		$I->seeInDatabase('e107_generic', array('gen_ip' => 'p7rp created', 'gen_type' => 'wmessage'));
	}

	/** A list's inline editor refuses the row type its scope pins, and still saves the row's other fields. */
	public function aUserRankCannotBeMovedToAnotherListInline(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a user-ranks inline edit of the row type, and keep the inline editor saving a rank');

		$rank = $this->seedGenericRow($I, 'user_rank_data', 'p7rp rank', 0);

		$this->loginAsDelegatedAdmin($I, 'p7rpU3admin');

		$this->sendInline($I, $rank, 'gen_type', 'wmessage', self::ROUTE_RANKS, self::ROUTE_RANKS_INLINE);
		$I->seeInDatabase('e107_generic', array('gen_id' => $rank, 'gen_type' => 'user_rank_data'));

		$this->sendInline($I, $rank, 'gen_ip', 'p7rp inline', self::ROUTE_RANKS, self::ROUTE_RANKS_INLINE);
		$I->seeInDatabase('e107_generic', array('gen_id' => $rank, 'gen_ip' => 'p7rp inline'));
	}

	/** A list's batch refuses the row type its scope pins, and still writes the row's other fields. */
	public function aUserRankCannotBeMovedToAnotherListByBatch(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a user-ranks batch write of the row type, and keep the batch writing a rank');

		$rank = $this->seedGenericRow($I, 'user_rank_data', 'p7rp rank', 0);

		$this->loginAsDelegatedAdmin($I, 'p7rpU3admin');

		$this->sendBatch($I, self::ROUTE_RANKS, 'gen_type__wmessage', $rank, 'e-multiselect');
		$I->seeInDatabase('e107_generic', array('gen_id' => $rank, 'gen_type' => 'user_rank_data'));

		$this->sendBatch($I, self::ROUTE_RANKS, 'bool__gen_user_id__0', $rank, 'e-multiselect');
		$I->seeInDatabase('e107_generic', array('gen_id' => $rank, 'gen_user_id' => 0));
	}

	/** The gallery's category edit form keeps the owner its scope pins, and still saves the category's other fields. */
	public function aGalleryCategoryCannotBeMovedOutOfTheGallery(AcceptanceTester $I)
	{
		$I->wantTo('Keep a gallery category in the gallery when its edit form posts another owner');

		$I->havePluginInstalled('gallery');

		$category = (int) $I->haveInDatabase('e107_core_media_cat', array(
			'media_cat_owner'    => 'gallery',
			'media_cat_category' => 'gallery_image_p7rp',
			'media_cat_title'    => 'p7rp gallery',
			'media_cat_sef'      => 'p7rp-gallery',
			'media_cat_diz'      => '',
			'media_cat_class'    => 0,
			'media_cat_image'    => '',
			'media_cat_order'    => 0,
		));

		$I->loginAsAdmin();

		$this->postWithToken($I, self::ROUTE_GALLERY_CATEGORY_EDIT.$category, array(
			'etrigger_submit'    => 'update',
			'media_cat_owner'    => 'p7rp',
			'media_cat_category' => 'gallery_image_p7rp',
			'media_cat_title'    => 'p7rp edited',
			'media_cat_sef'      => 'p7rp-gallery',
			'media_cat_diz'      => '',
			'media_cat_class'    => 0,
			'media_cat_image'    => '',
			'media_cat_order'    => 0,
		));

		$I->seeInDatabase('e107_core_media_cat', array('media_cat_id' => $category, 'media_cat_title' => 'p7rp edited'));
		$I->seeInDatabase('e107_core_media_cat', array('media_cat_id' => $category, 'media_cat_owner' => 'gallery'));
	}

	/** A list's drag-and-drop sort resets, writes and renumbers only the rows its scope selects. */
	public function aMenuListSortLeavesTheCustomPagesInTheirOrder(AcceptanceTester $I)
	{
		$I->wantTo('Hold a custom menu sort to the menus, leaving every custom page in its order');

		$menu = (int) $I->haveInDatabase('e107_page', array('menu_title' => 'p7rp menu', 'page_order' => 3));
		$posted = (int) $I->haveInDatabase('e107_page', array('page_title' => 'p7rp posted page', 'page_order' => 5));
		$page = (int) $I->haveInDatabase('e107_page', array('page_title' => 'p7rp page', 'page_order' => 6));
		$unposted = (int) $I->haveInDatabase('e107_page', array('menu_title' => 'p7rp unposted menu', 'page_order' => 45));
		$late = (int) $I->haveInDatabase('e107_page', array('page_title' => 'p7rp late page', 'page_order' => 50));

		$I->loginAsAdmin();

		$I->sendPostRequest(self::ROUTE_MENU_SORT, array(
			'all'     => array('row-'.$menu, 'row-'.$posted),
			'e-token' => $this->grabTokenFrom($I, self::ROUTE_WMESSAGE),
		));

		$I->seeInDatabase('e107_page', array('page_id' => $menu, 'page_order' => 10));
		$I->seeInDatabase('e107_page', array('page_id' => $posted, 'page_order' => 5));
		$I->seeInDatabase('e107_page', array('page_id' => $page, 'page_order' => 6));
		$I->assertNotContains((int) $I->grabFromDatabase('e107_page', 'page_order', array('page_id' => $unposted)), array(45, 999), 'The sort did not renumber an unposted custom menu.');
		$I->seeInDatabase('e107_page', array('page_id' => $late, 'page_order' => 50));
	}

	/** No edit route a custom-menus administrator reaches writes the page half of a row. */
	public function aMenusAdministratorCannotEditACustomPage(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a custom-menus administrator every edit of a custom page');

		$page = $this->seedPageRow($I, 'p7rp page', '');
		$both = $this->seedPageRow($I, 'p7rp both', 'p7rp both menu');

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');

		foreach(array(self::ROUTE_PAGE_EDIT.$page, self::ROUTE_OVERVIEW_EDIT.$page, self::ROUTE_MENU_EDIT.$page, self::ROUTE_PAGE_EDIT.$both, self::ROUTE_MENU_EDIT.$both) as $route)
		{
			$this->sendPageEdit($I, $route, 'p7rp edited', 'p7rp edited menu', self::ROUTE_MENU_LIST);
		}

		$I->seeInDatabase('e107_page', array('page_id' => $page, 'page_title' => 'p7rp page', 'page_text' => 'p7rp page'));
		$I->seeInDatabase('e107_page', array('page_id' => $both, 'page_title' => 'p7rp both', 'page_text' => 'p7rp both'));
	}

	/** No edit route a custom-pages administrator reaches writes the menu half of a row. */
	public function aPagesAdministratorCannotEditACustomMenu(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a custom-pages administrator every edit of a custom menu');

		$menu = $this->seedPageRow($I, '', 'p7rp menu');

		$this->loginAsDelegatedAdmin($I, 'p7rp5admin');

		foreach(array(self::ROUTE_MENU_EDIT.$menu, self::ROUTE_OVERVIEW_EDIT.$menu) as $route)
		{
			$this->sendPageEdit($I, $route, 'p7rp edited', 'p7rp edited menu', self::ROUTE_PAGE_LIST);
		}

		$I->seeInDatabase('e107_page', array('page_id' => $menu, 'menu_title' => 'p7rp menu'));
	}

	/** A custom-menus administrator still opens the overview, with an inline editor on a menu column and none on a page column. */
	public function aMenusAdministratorStillOpensTheOverviewWithInlineEditorsOnMenuColumnsOnly(AcceptanceTester $I)
	{
		$I->wantTo('Keep the overview open to the custom-menus administrator, inline-editable on its menu columns alone');

		$this->seedPageRow($I, 'p7rp both', 'p7rp both menu');

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');
		$I->amOnPage(self::ROUTE_OVERVIEW_LIST);

		$I->see('p7rp both');
		$I->seeElement('a.e-editable[data-name="menu_title"]');
		$I->dontSeeElement('a.e-editable[data-name="page_title"]');
	}

	/** Positive control: the custom pages administrator still edits a custom page. */
	public function aPagesAdministratorStillEditsACustomPage(AcceptanceTester $I)
	{
		$I->wantTo('Keep the custom page edit route open to the pages permission');

		$page = $this->seedPageRow($I, 'p7rp page', '');

		$this->loginAsDelegatedAdmin($I, 'p7rp5admin');
		$this->sendPageEdit($I, self::ROUTE_PAGE_EDIT.$page, 'p7rp edited page', '', self::ROUTE_PAGE_LIST);

		$I->seeInDatabase('e107_page', array('page_id' => $page, 'page_title' => 'p7rp edited page'));
	}

	/** Positive control: the custom menus administrator still edits a custom menu. */
	public function aMenusAdministratorStillEditsACustomMenu(AcceptanceTester $I)
	{
		$I->wantTo('Keep the custom menu edit route open to the menus permission');

		$menu = $this->seedPageRow($I, '', 'p7rp menu');

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');
		$this->sendPageEdit($I, self::ROUTE_MENU_EDIT.$menu, '', 'p7rp edited menu', self::ROUTE_MENU_LIST);

		$I->seeInDatabase('e107_page', array('page_id' => $menu, 'menu_title' => 'p7rp edited menu'));
	}

	/** The page list's inline editor refuses a custom-pages administrator a menu column. */
	public function aPagesAdministratorCannotInlineEditACustomMenuColumn(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a custom-pages administrator an inline write to a menu column of the page list');

		$both = $this->seedPageRow($I, 'p7rp both', 'p7rp both menu');

		$this->loginAsDelegatedAdmin($I, 'p7rp5admin');
		$this->sendInline($I, $both, 'menu_class', '254', self::ROUTE_PAGE_LIST, self::ROUTE_PAGE_INLINE);

		$I->seeInDatabase('e107_page', array('page_id' => $both, 'menu_class' => '0'));
	}

	/** The page list's batch refuses a custom-pages administrator a menu column. */
	public function aPagesAdministratorCannotBatchWriteACustomMenuColumn(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a custom-pages administrator a batch write to a menu column of the page list');

		$both = $this->seedPageRow($I, 'p7rp both', 'p7rp both menu');

		$this->loginAsDelegatedAdmin($I, 'p7rp5admin');
		$this->sendBatch($I, self::ROUTE_PAGE_LIST, 'menu_template__button', $both);

		$I->seeInDatabase('e107_page', array('page_id' => $both, 'menu_template' => ''));
	}

	/** The menu list's inline editor refuses a custom-menus administrator the chapter of a row that is a custom page. */
	public function aMenusAdministratorCannotInlineEditTheChapterOfACustomPage(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a custom-menus administrator an inline write to the chapter of a custom page on the menu list');

		$both = $this->seedPageRow($I, 'p7rp both', 'p7rp both menu');

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');
		$this->sendInline($I, $both, 'page_chapter', '7', self::ROUTE_MENU_LIST, self::ROUTE_MENU_INLINE);

		$I->seeInDatabase('e107_page', array('page_id' => $both, 'page_chapter' => 0));
	}

	/** On a menu-only row the chapter is a menu column: the menu list's inline editor and the menu edit form both write it for a custom-menus administrator. */
	public function aMenusAdministratorStillPlacesACustomMenuInAChapter(AcceptanceTester $I)
	{
		$I->wantTo('Keep the chapter of a menu-only row writable to a custom-menus administrator');

		$inline = $this->seedPageRow($I, '', 'p7rp inline menu');
		$form = $this->seedPageRow($I, '', 'p7rp form menu');

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');
		$this->sendInline($I, $inline, 'page_chapter', '7', self::ROUTE_MENU_LIST, self::ROUTE_MENU_INLINE);

		$this->postWithToken($I, self::ROUTE_MENU_EDIT.$form, array(
			'etrigger_submit' => 'update',
			'menu_name'       => 'p7rpmenu',
			'menu_title'      => 'p7rp form menu',
			'page_chapter'    => '7',
		));

		$I->seeInDatabase('e107_page', array('page_id' => $inline, 'page_chapter' => 7));
		$I->seeInDatabase('e107_page', array('page_id' => $form, 'page_chapter' => 7));
	}

	/** A batch write of the chapter on the menu list reaches the menu-only rows of the selection and not the custom pages. */
	public function aMenusAdministratorBatchPlacesOnlyTheCustomMenusInAChapter(AcceptanceTester $I)
	{
		$I->wantTo('Hold a custom-menus administrator\'s batch chapter write to the menu-only rows');

		$menu = $this->seedPageRow($I, '', 'p7rp menu');
		$both = $this->seedPageRow($I, 'p7rp both', 'p7rp both menu');

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');
		$this->postWithToken($I, self::ROUTE_MENU_LIST, array(
			'etrigger_batch'   => 'page_chapter__7',
			'e__execute_batch' => 'Go',
			'multiselect'      => array($menu => $menu, $both => $both),
		));

		$I->seeInDatabase('e107_page', array('page_id' => $menu, 'page_chapter' => 7));
		$I->seeInDatabase('e107_page', array('page_id' => $both, 'page_chapter' => 0));
	}

	/** On the overview, the chapter of a menu-only row is a menu column, which a custom-pages administrator does not hold, and the chapter of a custom page is a page column, which it does. */
	public function aPagesAdministratorPlacesOnlyCustomPagesInAChapterThroughTheOverview(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a custom-pages administrator the chapter of a menu-only row on the overview, and keep the chapter of a custom page');

		$menu = $this->seedPageRow($I, '', 'p7rp menu');
		$page = $this->seedPageRow($I, 'p7rp page', '');

		$this->loginAsDelegatedAdmin($I, 'p7rp5admin');
		$this->sendInline($I, $menu, 'page_chapter', '7', self::ROUTE_OVERVIEW_LIST, self::ROUTE_OVERVIEW_INLINE);
		$this->sendInline($I, $page, 'page_chapter', '7', self::ROUTE_OVERVIEW_LIST, self::ROUTE_OVERVIEW_INLINE);

		$I->seeInDatabase('e107_page', array('page_id' => $menu, 'page_chapter' => 0));
		$I->seeInDatabase('e107_page', array('page_id' => $page, 'page_chapter' => 7));
	}

	/** The batch commands that name the chapter after them reach only the rows whose chapter the route saves, however the field is spelled, so a custom-menus administrator moves no custom page through them. */
	public function aMenusAdministratorCannotMoveACustomPageWithAnotherBatchCommand(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a custom-menus administrator the chapter of a custom page through the batch commands that name a field');

		$both = $this->seedPageRow($I, 'p7rp both', 'p7rp both menu');
		$attach = $this->seedPageRow($I, 'p7rp attach page', '');
		$deattach = $this->seedPageRow($I, 'p7rp deattach page', '');
		$padded = $this->seedPageRow($I, 'p7rp padded page', '');
		$split = $this->seedPageRow($I, 'p7rp split page', '');
		$I->updateInDatabase('e107_page', array('page_chapter' => 3), array('page_id' => $deattach));
		$I->updateInDatabase('e107_page', array('page_chapter' => 3), array('page_id' => $padded));

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');
		$this->sendBatch($I, self::ROUTE_MENU_LIST, 'bool__page_chapter__1', $both);
		$this->sendBatch($I, self::ROUTE_OVERVIEW_LIST, 'attach__page_chapter__7', $attach);
		$this->sendBatch($I, self::ROUTE_OVERVIEW_LIST, 'deattach__page_chapter__3', $deattach);
		$this->sendBatch($I, self::ROUTE_OVERVIEW_LIST, 'bool__page_chapter __0', $padded);
		$this->sendBatch($I, self::ROUTE_OVERVIEW_LIST, "attach__page_\rchapter__7", $split);

		$I->seeInDatabase('e107_page', array('page_id' => $both, 'page_chapter' => 0));
		$I->seeInDatabase('e107_page', array('page_id' => $attach, 'page_chapter' => 0));
		$I->seeInDatabase('e107_page', array('page_id' => $deattach, 'page_chapter' => 3));
		$I->seeInDatabase('e107_page', array('page_id' => $padded, 'page_chapter' => 3));
		$I->seeInDatabase('e107_page', array('page_id' => $split, 'page_chapter' => 0));
	}

	/** The batch commands that name the chapter after them reach only the rows whose chapter the route saves, however the field is spelled, so a custom-pages administrator moves no custom menu through them. */
	public function aPagesAdministratorCannotMoveACustomMenuWithAnotherBatchCommand(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a custom-pages administrator the chapter of a custom menu through the batch commands that name a field');

		$menu = $this->seedPageRow($I, '', 'p7rp menu');
		$padded = $this->seedPageRow($I, '', 'p7rp padded menu');

		$this->loginAsDelegatedAdmin($I, 'p7rp5admin');
		$this->sendBatch($I, self::ROUTE_OVERVIEW_LIST, 'bool__page_chapter__1', $menu);
		$this->sendBatch($I, self::ROUTE_OVERVIEW_LIST, 'bool__ page_chapter__1', $padded);

		$I->seeInDatabase('e107_page', array('page_id' => $menu, 'page_chapter' => 0));
		$I->seeInDatabase('e107_page', array('page_id' => $padded, 'page_chapter' => 0));
	}

	/** The menu list draws the chapter of a page-and-menu row, which only the page routes save, as plain text, and says when its batch leaves such a row in its chapter. */
	public function theMenuListLeavesTheChapterOfACustomPageReadOnly(AcceptanceTester $I)
	{
		$I->wantTo('Draw no chapter editor on the menu list where the menu list does not save the chapter, and count the rows its batch leaves out');

		$menu = $this->seedPageRow($I, '', 'p7rp menu');
		$both = $this->seedPageRow($I, 'p7rp both', 'p7rp both menu');

		$I->loginAsAdmin();
		$I->amOnPage(self::ROUTE_MENU_LIST);
		$I->seeElement('a.e-editable[data-name="page_chapter"][data-pk="'.$menu.'"]');
		$I->dontSeeElement('a.e-editable[data-name="page_chapter"][data-pk="'.$both.'"]');

		$this->postWithToken($I, self::ROUTE_MENU_LIST, array(
			'etrigger_batch'   => 'page_chapter__7',
			'e__execute_batch' => 'Go',
			'multiselect'      => array($menu => $menu, $both => $both),
		));

		$I->see('1 selected record(s) were left in their chapter');
		$I->seeInDatabase('e107_page', array('page_id' => $menu, 'page_chapter' => 7));
		$I->seeInDatabase('e107_page', array('page_id' => $both, 'page_chapter' => 0));
	}

	/** A custom-menus administrator's menu create takes its author, date and SEF URL from the request rather than the post, and keeps the chapter posted with it. */
	public function aMenusAdministratorCreatesACustomMenuWithItsAuthorDateAndSefUrl(AcceptanceTester $I)
	{
		$I->wantTo('Fill in the author, date and SEF URL of a custom menu a custom-menus administrator creates');

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');
		$admin = (int) $I->grabFromDatabase('e107_user', 'user_id', array('user_loginname' => 'p7rpJadmin'));
		$before = time();

		$this->postWithToken($I, self::ROUTE_MENU_CREATE, array(
			'etrigger_submit' => 'create',
			'menu_name'       => 'p7rpcreated',
			'menu_title'      => 'p7rp created menu',
			'page_chapter'    => '7',
			'page_author'     => '1',
			'page_datestamp'  => '1',
			'page_sef'        => 'p7rp-posted-sef',
		) + self::MENU_CREATE_COLUMNS);

		$I->seeInDatabase('e107_page', array('menu_title' => 'p7rp created menu', 'page_author' => $admin, 'page_sef' => 'p7rpcreated', 'page_chapter' => 7));
		$I->assertGreaterThanOrEqual($before, (int) $I->grabFromDatabase('e107_page', 'page_datestamp', array('menu_title' => 'p7rp created menu')), 'The created menu carries no date of its own.');
	}

	/** A custom-pages administrator's create saves the page half of the row and not its menu half. */
	public function aPagesAdministratorCreatesACustomPageWithoutItsMenuHalf(AcceptanceTester $I)
	{
		$I->wantTo('Keep the menu half out of a custom page a custom-pages administrator creates');

		$this->loginAsDelegatedAdmin($I, 'p7rp5admin');
		$this->sendPageEdit($I, self::ROUTE_PAGE_CREATE, 'p7rp new page', 'p7rp new menu', self::ROUTE_PAGE_LIST, self::PAGE_CREATE_COLUMNS);

		$I->seeInDatabase('e107_page', array('page_title' => 'p7rp new page', 'menu_name' => '', 'menu_title' => ''));
	}

	/** The menu edit form shows a custom-menus administrator none of a custom page's custom-field values. */
	public function aMenusAdministratorCannotReadACustomPagesFieldsThroughTheMenuEditForm(AcceptanceTester $I)
	{
		$I->wantTo("Keep a custom page's custom-field values off the menu edit form");

		$page = $this->seedPageRow($I, 'p7rp page', '');
		$this->seedCustomFieldValue($I, $page, 'p7rphiddenvalue');

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');
		$I->amOnPage(self::ROUTE_MENU_EDIT.$page);

		$I->seeElement('input[name="menu_title"]');
		$I->dontSeeInSource('p7rphiddenvalue');
	}

	/** The page edit form shows no custom-field values for a row the page list leaves out. */
	public function thePageEditFormShowsNoCustomFieldsForARowThePageListLeavesOut(AcceptanceTester $I)
	{
		$I->wantTo('Keep the custom-field values of a row the page list leaves out off the page edit form');

		$menu = $this->seedPageRow($I, '', 'p7rp menu');
		$this->seedCustomFieldValue($I, $menu, 'p7rphiddenvalue');

		$this->loginAsDelegatedAdmin($I, 'p7rp5admin');
		$I->amOnPage(self::ROUTE_PAGE_EDIT.$menu);

		$I->seeElement('input[name="page_title"]');
		$I->dontSeeInSource('p7rphiddenvalue');
	}

	/** The custom page list answers to the pages permission. */
	public function aMenusAdministratorCannotOpenTheCustomPageList(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the custom page list to a custom-menus administrator');

		$this->seedPageRow($I, 'p7rp page', '');

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');
		$I->amOnPage(self::ROUTE_PAGE_LIST);

		$I->see('You do not have permission to view this page.');
		$I->dontSee('p7rp page');
	}

	/** The chapter create route answers to the pages permission. */
	public function aMenusAdministratorCannotCreateAChapter(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the chapter create route to a custom-menus administrator');

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');

		$I->sendPostRequest(self::ROUTE_CHAPTER_CREATE, array(
			'etrigger_submit'          => 'create',
			'chapter_name'             => 'p7rp chapter',
			'chapter_parent'           => 0,
			'chapter_sef'              => 'p7rp-chapter',
			'chapter_meta_description' => '',
			'chapter_meta_keywords'    => '',
			'chapter_manager'          => 254,
			'chapter_icon'             => '',
			'chapter_image'            => '',
			'chapter_order'            => 0,
			'chapter_template'         => 'default',
			'chapter_visibility'       => 0,
			'e-token'                  => $this->grabTokenFrom($I, self::ROUTE_MENU_LIST),
		));

		$I->dontSeeInDatabase('e107_page_chapters', array('chapter_name' => 'p7rp chapter'));
	}

	/** The custom page options answer to the main administrator's permission, which the admin menu already marks them with, in every mode that serves them. */
	public function aPagesAdministratorCannotOpenTheCustomPageOptionsInAnyMode(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the custom page options, in every mode, to a custom-pages administrator');

		$this->loginAsDelegatedAdmin($I, 'p7rp5admin');
		$this->seeTheCustomPageOptionsRefusedInEveryMode($I);
	}

	/** The custom page options answer to the main administrator's permission in every mode, the overview's included. */
	public function aMenusAdministratorCannotOpenTheCustomPageOptionsInAnyMode(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the custom page options, in every mode, to a custom-menus administrator');

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');
		$this->seeTheCustomPageOptionsRefusedInEveryMode($I);
	}

	/** cpage.php opens no route its permission map does not name, to the main administrator either. */
	public function theCustomPageAdminRefusesARouteItsMapDoesNotName(AcceptanceTester $I)
	{
		$I->wantTo('Refuse every custom page route the permission map does not declare');

		$I->loginAsAdmin();

		foreach(array('mode=cat&action=prefs', 'mode=overview&action=grid', 'mode=dialog&action=list', 'mode=page&action=p7rpundeclared') as $query)
		{
			$I->amOnPage('/e107_admin/cpage.php?'.$query);
			$I->see('You do not have permission to view this page.');
		}
	}

	/** Positive control: an admin menu divider names no action, so the custom page admin menu keeps both of its dividers. */
	public function theCustomPageAdminMenuKeepsItsDividers(AcceptanceTester $I)
	{
		$I->wantTo('Draw both dividers of the custom page admin menu');

		$I->loginAsAdmin();
		$I->amOnPage(self::ROUTE_OVERVIEW_LIST);
		$I->seeNumberOfElements('li[role="separator"]', 2);
	}

	/** Positive control: the menu list's grid view, which its toggle draws, still answers a custom-menus administrator. */
	public function aMenusAdministratorStillReachesTheMenuGrid(AcceptanceTester $I)
	{
		$I->wantTo('Keep the menu grid open to the menus permission');

		$this->seedPageRow($I, '', 'p7rp grid menu');

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');
		$I->amOnPage(self::ROUTE_MENU_LIST);
		$I->seeElement('a[href*="mode=menu"][href*="action=grid"]');
		$I->amOnPage('/e107_admin/cpage.php?mode=menu&action=grid');
		$I->dontSee('You do not have permission to view this page.');
		$I->see('p7rp grid menu');
	}

	/** Positive control: the custom page list's inline editor, sort, filter route and create form's chapter fields still answer a custom-pages administrator. */
	public function aPagesAdministratorStillReachesThePageListsAjaxRoutes(AcceptanceTester $I)
	{
		$I->wantTo('Keep the custom page list\'s ajax routes open to the pages permission');

		$first = (int) $I->haveInDatabase('e107_page', array('page_title' => 'p7rp first page', 'page_text' => 'p7rppagebody', 'page_order' => 50));
		$second = (int) $I->haveInDatabase('e107_page', array('page_title' => 'p7rp second page', 'page_order' => 60));

		$this->loginAsDelegatedAdmin($I, 'p7rp5admin');

		$this->sendInline($I, $first, 'page_title', 'p7rp inline title', self::ROUTE_PAGE_LIST, self::ROUTE_PAGE_INLINE);
		$I->seeInDatabase('e107_page', array('page_id' => $first, 'page_title' => 'p7rp inline title'));

		$I->sendPostRequest(self::ROUTE_PAGE_SORT, array('all' => array('row-'.$second, 'row-'.$first), 'e-token' => $this->grabTokenFrom($I, self::ROUTE_PAGE_LIST)));
		$I->assertLessThan((int) $I->grabFromDatabase('e107_page', 'page_order', array('page_id' => $first)), (int) $I->grabFromDatabase('e107_page', 'page_order', array('page_id' => $second)), 'The custom page sort did not reorder the pages.');

		$I->sendPostRequest(self::ROUTE_PAGE_FILTER, array('searchquery' => 'p7rppage', 'e-token' => $this->grabTokenFrom($I, self::ROUTE_PAGE_LIST)));
		$I->seeInSource('p7rppagebody');

		$I->sendPostRequest(self::ROUTE_PAGE_CHAPTER_CHANGE, array('page_chapter' => '0', 'e-token' => $this->grabTokenFrom($I, self::ROUTE_PAGE_CREATE)));
		$I->seeInSource('#tab');
	}

	/** Positive control: the chapter list's inline editor and sort still answer a custom-pages administrator. */
	public function aPagesAdministratorStillReachesTheChapterListsAjaxRoutes(AcceptanceTester $I)
	{
		$I->wantTo('Keep the chapter list\'s ajax routes open to the pages permission');

		$chapter = array('chapter_parent' => 0, 'chapter_meta_description' => '', 'chapter_meta_keywords' => '', 'chapter_manager' => 254, 'chapter_icon' => '', 'chapter_image' => '', 'chapter_template' => 'default', 'chapter_visibility' => 0);
		$first = (int) $I->haveInDatabase('e107_page_chapters', $chapter + array('chapter_name' => 'p7rp first book', 'chapter_sef' => 'p7rp-first-book', 'chapter_order' => 50));
		$second = (int) $I->haveInDatabase('e107_page_chapters', $chapter + array('chapter_name' => 'p7rp second book', 'chapter_sef' => 'p7rp-second-book', 'chapter_order' => 60));

		$this->loginAsDelegatedAdmin($I, 'p7rp5admin');

		$this->sendInline($I, $first, 'chapter_sef', 'p7rp-inline-sef', self::ROUTE_CHAPTER_LIST, self::ROUTE_CHAPTER_INLINE);
		$I->seeInDatabase('e107_page_chapters', array('chapter_id' => $first, 'chapter_sef' => 'p7rp-inline-sef'));

		$I->sendPostRequest(self::ROUTE_CHAPTER_SORT, array('all' => array('row-'.$second, 'row-'.$first), 'e-token' => $this->grabTokenFrom($I, self::ROUTE_CHAPTER_LIST)));
		$I->assertLessThan((int) $I->grabFromDatabase('e107_page_chapters', 'chapter_order', array('chapter_id' => $first)), (int) $I->grabFromDatabase('e107_page_chapters', 'chapter_order', array('chapter_id' => $second)), 'The chapter sort did not reorder the chapters.');
	}

	/** Positive control: the overview's batch copy, which asks the overview's create route, still answers the main administrator. */
	public function theMainAdministratorStillCopiesThroughTheOverview(AcceptanceTester $I)
	{
		$I->wantTo('Keep the overview\'s batch copy open to the main administrator');

		$both = $this->seedPageRow($I, 'p7rp both', 'p7rp both menu');
		$I->updateInDatabase('e107_page', array('page_sef' => 'p7rp-both'), array('page_id' => $both));

		$I->loginAsAdmin();
		$this->sendBatch($I, self::ROUTE_OVERVIEW_LIST, 'copy', $both);

		$I->seeNumRecords(2, 'e107_page', array('menu_title' => 'p7rp both menu'));
	}

	/** The custom page grid answers to the pages permission, as the custom page list does. */
	public function aMenusAdministratorCannotReadCustomPagesThroughThePageGrid(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the custom page grid, and its export, to a custom-menus administrator');

		$page = $this->seedPageRow($I, 'p7rp page', '');
		$I->updateInDatabase('e107_page', array('page_password' => 'p7rpsecretpw'), array('page_id' => $page));

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');
		$I->amOnPage(self::ROUTE_PAGE_GRID);
		$I->see('You do not have permission to view this page.');

		$I->sendPostRequest(self::ROUTE_PAGE_GRID, \Test\AdminListPost::batch('export', $page, 'multiselect') + array('e-token' => $this->grabTokenFrom($I, self::ROUTE_MENU_LIST)));
		$I->dontSeeInSource('p7rpsecretpw');
	}

	/** The batch export leaves out the columns of a half the administrator does not hold, on the overview and on the menu list. */
	public function aMenusAdministratorExportsNoPageColumns(AcceptanceTester $I)
	{
		$I->wantTo("Keep a custom page's password and body out of a custom-menus administrator's export");

		$page = $this->seedPageRow($I, 'p7rp page', '');
		$both = $this->seedPageRow($I, 'p7rp both', 'p7rp both menu');
		$I->updateInDatabase('e107_page', array('page_password' => 'p7rpsecretpw', 'page_text' => 'p7rpsecrettext'), array('page_id' => $page));
		$I->updateInDatabase('e107_page', array('page_password' => 'p7rpbothpw', 'page_text' => 'p7rpbothtext'), array('page_id' => $both));

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');

		$this->sendBatch($I, self::ROUTE_OVERVIEW_LIST, 'export', $page);
		$I->dontSeeInSource('p7rpsecretpw');
		$I->dontSeeInSource('p7rpsecrettext');

		$this->sendBatch($I, self::ROUTE_MENU_LIST, 'export', $both);
		$I->seeInSource('p7rp both menu');
		$I->dontSeeInSource('p7rpbothpw');
		$I->dontSeeInSource('p7rpbothtext');
	}

	/** The overview lists no column of a half the administrator does not hold, whatever its column chooser is sent. */
	public function aMenusAdministratorCannotListThePageColumnsOfTheOverview(AcceptanceTester $I)
	{
		$I->wantTo("Keep a custom page's password and body off a custom-menus administrator's overview");

		$page = $this->seedPageRow($I, 'p7rp page', '');
		$I->updateInDatabase('e107_page', array('page_password' => 'p7rpsecretpw', 'page_text' => 'p7rpsecrettext'), array('page_id' => $page));

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');
		$this->postWithToken($I, self::ROUTE_OVERVIEW_LIST, array(
			'etrigger_ecolumns' => 'Save',
			'e-columns'         => array('page_id', 'page_title', 'page_password', 'page_text'),
		));

		$I->amOnPage(self::ROUTE_OVERVIEW_LIST);
		$I->seeElement('input[name="multiselect['.$page.']"]');
		$I->dontSeeInSource('p7rpsecretpw');
		$I->dontSeeInSource('p7rpsecrettext');
	}

	/** The overview's search-in-field and filter reach no column of a half the administrator does not hold, so a guess at a page's password through either lists what any other guess lists. */
	public function aMenusAdministratorCannotSearchThePasswordOfACustomPage(AcceptanceTester $I)
	{
		$I->wantTo("Keep a custom page's password out of a custom-menus administrator's list search and filter");

		$page = $this->seedPageRow($I, 'p7rp page', '');
		$I->updateInDatabase('e107_page', array('page_password' => 'p7rpsecretpw'), array('page_id' => $page));

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');

		foreach(array('searchquery=p7rpwrongguess&filter_options=searchfield__page_password', 'searchquery=&filter_options=page_password__p7rpwrongguess') as $query)
		{
			$I->amOnPage(self::ROUTE_OVERVIEW_LIST.'&'.$query);
			$I->seeElement('input[name="multiselect['.$page.']"]');
		}
	}

	/** The overview sorts by no column of a half the administrator does not hold, so it lists the same rows in the same order whatever the pages' passwords. */
	public function aMenusAdministratorCannotSortTheOverviewByThePasswordOfACustomPage(AcceptanceTester $I)
	{
		$I->wantTo("Keep a custom page's password out of a custom-menus administrator's overview sort");

		$first = $this->seedPageRow($I, 'p7rp first page', '');
		$second = $this->seedPageRow($I, 'p7rp second page', '');

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');

		$orders = array();

		foreach(array(array('p7rpaaaa', 'p7rpzzzz'), array('p7rpzzzz', 'p7rpaaaa')) as $passwords)
		{
			$I->updateInDatabase('e107_page', array('page_password' => $passwords[0]), array('page_id' => $first));
			$I->updateInDatabase('e107_page', array('page_password' => $passwords[1]), array('page_id' => $second));

			$I->amOnPage(self::ROUTE_OVERVIEW_LIST.'&field=page_password&asc=desc');
			preg_match_all('/multiselect\[(\d+)\]/', $I->grabPageSource(), $listed);
			$orders[] = $listed[1];
		}

		$I->assertNotEmpty($orders[0], 'The overview listed no rows.');
		$I->assertSame($orders[0], $orders[1], "The overview sorted by the custom pages' passwords.");
	}

	/** A batch copy creates every half the selected rows have, so the custom-menus administrator copies no row that is a custom page. */
	public function aMenusAdministratorCannotCopyACustomPage(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a custom-menus administrator a batch copy of a page-and-menu row');

		$both = $this->seedPageRow($I, 'p7rp both', 'p7rp both menu');
		$I->updateInDatabase('e107_page', array('page_sef' => 'p7rp-both'), array('page_id' => $both));

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');
		$this->sendBatch($I, self::ROUTE_MENU_LIST, 'copy', $both);

		$I->seeNumRecords(1, 'e107_page', array('page_title' => 'p7rp both'));
	}

	/** A batch copy creates every half the selected rows have, so the custom-pages administrator copies no row that is a custom menu. */
	public function aPagesAdministratorCannotCopyACustomMenu(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a custom-pages administrator a batch copy of a page-and-menu row');

		$both = $this->seedPageRow($I, 'p7rp both', 'p7rp both menu');
		$I->updateInDatabase('e107_page', array('page_sef' => 'p7rp-both'), array('page_id' => $both));

		$this->loginAsDelegatedAdmin($I, 'p7rp5admin');
		$this->sendBatch($I, self::ROUTE_PAGE_LIST, 'copy', $both);

		$I->seeNumRecords(1, 'e107_page', array('menu_title' => 'p7rp both menu'));
	}

	/** Positive control: the custom-menus administrator still copies a menu-only row. */
	public function aMenusAdministratorStillCopiesACustomMenu(AcceptanceTester $I)
	{
		$I->wantTo('Keep the batch copy of a custom menu working for a custom-menus administrator');

		$menu = $this->seedPageRow($I, '', 'p7rp menu');
		$I->updateInDatabase('e107_page', array('page_sef' => 'p7rp-menu'), array('page_id' => $menu));

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');
		$this->sendBatch($I, self::ROUTE_MENU_LIST, 'copy', $menu);

		$I->seeNumRecords(2, 'e107_page', array('menu_title' => 'p7rp menu'));
	}

	/** A feature box item carries a row's page title and body, so a custom-menus administrator who administers the feature box makes none from a custom page. */
	public function aMenusAdministratorCannotCarryACustomPageIntoTheFeatureBox(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a feature box item made from a custom page to a custom-menus administrator');

		$I->havePluginInstalled('featurebox');
		$both = $this->seedPageRow($I, 'p7rp both', 'p7rp both menu');
		$plugin = $I->grabFromDatabase('e107_plugin', 'plugin_id', array('plugin_path' => 'featurebox'));

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin', '.P'.$plugin);
		$this->sendBatch($I, self::ROUTE_MENU_LIST, 'featurebox', $both);

		$I->dontSeeInDatabase('e107_featurebox', array('fb_title' => 'p7rp both'));
	}

	/** The overview's delete key deletes nothing on any route but the overview list, even for an administrator who may delete. */
	public function theOverviewDeleteKeyDeletesNothingOnAnyOtherRoute(AcceptanceTester $I)
	{
		$I->wantTo('Delete nothing for the overview delete key posted to any route but the overview list');

		$page = $this->seedPageRow($I, 'p7rp page', 'p7rp menu');

		$this->loginAsDelegatedAdmin($I, 'p7rp5J1admin');

		foreach(array(self::ROUTE_PAGE_LIST, self::ROUTE_PAGE_EDIT.$page, self::ROUTE_OVERVIEW_EDIT.$page, self::ROUTE_MENU_LIST) as $route)
		{
			$I->sendPostRequest($route, array(
				'menu_delete' => array($page => $page),
				'e-token'     => $this->grabTokenFrom($I, self::ROUTE_PAGE_LIST),
			));
		}

		$I->seeInDatabase('e107_page', array('page_id' => $page));
	}

	/** The overview's delete button, and the key it posts, answer to the permission to delete custom pages and menus. */
	public function aPagesAdministratorWhoMayNotDeleteCannotDeleteThroughTheOverview(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the overview delete to a custom-pages administrator who may not delete');

		$page = $this->seedPageRow($I, 'p7rp page', '');

		$this->loginAsDelegatedAdmin($I, 'p7rp5admin');
		$I->amOnPage(self::ROUTE_OVERVIEW_LIST);
		$I->dontSeeElement("button[name='menu_delete[{$page}]']");

		$I->sendPostRequest(self::ROUTE_OVERVIEW_LIST, array(
			'menu_delete' => array($page => $page),
			'e-token'     => $I->grabToken(),
		));

		$I->seeInDatabase('e107_page', array('page_id' => $page));
	}

	/** Positive control: an administrator who may delete still deletes a row with the overview's delete button. */
	public function aPagesAdministratorWhoMayDeleteStillDeletesThroughTheOverview(AcceptanceTester $I)
	{
		$I->wantTo('Keep the overview delete button deleting for a custom-pages administrator who may delete');

		$page = $this->seedPageRow($I, 'p7rp page', 'p7rp menu');

		$this->loginAsDelegatedAdmin($I, 'p7rp5J1admin');
		$I->amOnPage(self::ROUTE_OVERVIEW_LIST);

		$I->sendPostRequest(self::ROUTE_OVERVIEW_LIST, array(
			$I->grabAttributeFrom("button.delete[value='{$page}']", 'name') => $page,
			'e-token' => $I->grabToken(),
		));

		$I->dontSeeInDatabase('e107_page', array('page_id' => $page));
	}

	/** The page list's batch delete answers to the permission to delete custom pages and menus. */
	public function aPagesAdministratorWhoMayNotDeleteCannotBatchDeleteACustomPage(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the page list batch delete to a custom-pages administrator who may not delete');

		$page = $this->seedPageRow($I, 'p7rp page', '');

		$this->loginAsDelegatedAdmin($I, 'p7rp5admin');
		$this->sendConfirmedBatchDelete($I, self::ROUTE_PAGE_LIST, $page);

		$I->seeInDatabase('e107_page', array('page_id' => $page));
	}

	/** Positive control: a custom-pages administrator who may delete still batch-deletes a custom page. */
	public function aPagesAdministratorWhoMayDeleteStillBatchDeletesACustomPage(AcceptanceTester $I)
	{
		$I->wantTo('Keep the page list batch delete working for a custom-pages administrator who may delete');

		$page = $this->seedPageRow($I, 'p7rp page', '');

		$this->loginAsDelegatedAdmin($I, 'p7rp5J1admin');
		$this->sendConfirmedBatchDelete($I, self::ROUTE_PAGE_LIST, $page);

		$I->dontSeeInDatabase('e107_page', array('page_id' => $page));
	}

	/** The menu list's row delete answers to the permission to delete custom pages and menus. */
	public function aMenusAdministratorWhoMayNotDeleteCannotDeleteACustomMenu(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a menu list row delete to a custom-menus administrator who may not delete');

		$menu = $this->seedPageRow($I, '', 'p7rp menu');

		$this->loginAsDelegatedAdmin($I, 'p7rpJadmin');
		$this->sendConfirmedRowDelete($I, self::ROUTE_MENU_LIST, $menu);

		$I->seeInDatabase('e107_page', array('page_id' => $menu));
	}

	/** The chapter list's row delete answers to the permission to delete custom pages and menus. */
	public function aPagesAdministratorWhoMayNotDeleteCannotDeleteAChapter(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a chapter list row delete to a custom-pages administrator who may not delete');

		$chapter = (int) $I->haveInDatabase('e107_page_chapters', array('chapter_name' => 'p7rp chapter', 'chapter_meta_description' => ''));

		$this->loginAsDelegatedAdmin($I, 'p7rp5admin');
		$this->sendConfirmedRowDelete($I, self::ROUTE_CHAPTER_LIST, $chapter);

		$I->seeInDatabase('e107_page_chapters', array('chapter_id' => $chapter));
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

	/**
	 * The scenario the advisory leads with, which nothing under e107_tests pinned. user_ban is
	 * on the batch menu the page draws for a holder of 4 or U0, and a batch writes through
	 * e_admin_tree_model::batchUpdate(), which never reaches beforeUpdate(): ticking another
	 * administrator's row and choosing Banned locked them out of the site.
	 *
	 * The ordinary member is banned in the same shape first, so a batch menu that had stopped
	 * working could not pass for the guard.
	 */
	public function aDelegatedAdministratorCannotBanAnAdministratorThroughTheBatch(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a user_ban batch on an administrator to a delegated administrator');

		$victimId = $this->seedVictim($I);
		$adminId = $this->seedOtherAdmin($I);
		$this->loginAsDelegatedAdmin($I, 'p7rpU0admin');

		$this->sendBatch($I, self::ROUTE_LIST, 'user_ban__1', $victimId);

		$I->assertSame('1',
			(string) $I->grabFromDatabase('e107_user', 'user_ban', array('user_id' => $victimId)),
			'The user_ban batch no longer bans an ordinary member for a delegated administrator, '
			.'so the refusal below is a broken batch menu rather than an authorisation boundary.');

		$this->sendBatch($I, self::ROUTE_LIST, 'user_ban__1', $adminId);

		$I->assertSame('0',
			(string) $I->grabFromDatabase('e107_user', 'user_ban', array('user_id' => $adminId)),
			'A delegated administrator holding U0 banned '.self::OTHER_ADMIN.' by posting '
			.'etrigger_batch=user_ban__1 to '.self::ROUTE_LIST.'.');

		$I->seeInDatabase('e107_admin_log', array('dblog_remarks like' => '%Refused the trigger user_ban%'));
	}

	/**
	 * The batch delete carries its selection in a second field, and that is the
	 * one the request that deletes is read from.
	 *
	 * e_admin_ui::handleListDeleteBatch() puts the ticked ids into
	 * delete_confirm_value, renders the confirm screen, and on the answer takes
	 * the selection back out of that field. multiselect is not part of that
	 * request, and nothing requires the confirm screen to have been rendered
	 * first: the field and the confirm trigger posted together delete in one
	 * round trip. A guard that reads only the checkbox column is looking at an
	 * empty selection at the moment the rows are removed.
	 *
	 * Deleting the account is the destructive end of the rule beforeUpdate()
	 * applies to rewriting it, so it answers to the same permission.
	 *
	 * The ordinary member goes first, and their row has to be gone: a route
	 * that deleted nothing would make the refusal below meaningless.
	 */
	public function aDelegatedAdministratorCannotBatchDeleteAnAdministrator(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a confirmed batch delete of an administrator to a delegated administrator');

		$victimId = $this->seedVictim($I);
		$adminId = $this->seedOtherAdmin($I);
		$this->loginAsDelegatedAdmin($I, 'p7rp4admin');

		$this->sendConfirmedBatchDelete($I, self::ROUTE_LIST, $victimId);

		$I->dontSeeInDatabase('e107_user', array('user_id' => $victimId));

		$this->sendConfirmedBatchDelete($I, self::ROUTE_LIST, $adminId);

		$I->seeInDatabase('e107_user', array('user_id' => $adminId));
	}

	/**
	 * Positive control for the delete half of the guard, in the same shape as
	 * the one above it: an administrator who may modify admin perms must still
	 * be able to remove another administrator's account.
	 */
	public function anAdministratorWhoMayModifyAdminPermsStillBatchDeletesAnAdministrator(AcceptanceTester $I)
	{
		$I->wantTo('Keep the batch delete of an administrator working for a caller holding 3');

		$adminId = $this->seedOtherAdmin($I);
		$this->loginAsDelegatedAdmin($I, 'p7rpPermsadmin');

		$this->sendConfirmedBatchDelete($I, self::ROUTE_LIST, $adminId);

		$I->dontSeeInDatabase('e107_user', array('user_id' => $adminId));
	}

	/**
	 * {@see e_admin_ui::ListBatchTrigger()} drops the whole submission on a posted cancel, so the
	 * rule stands aside for one and writes no refusal. The caller is the one refused above,
	 * which is what gives the audit-log assertion its teeth; the surviving row asserts the
	 * dispatcher's own early return, base code this branch does not touch.
	 */
	public function aCancelledBatchDeleteNeitherDeletesNorRefuses(AcceptanceTester $I)
	{
		$I->wantTo('Leave a cancelled batch delete unrefused as well as undone');

		$adminId = $this->seedOtherAdmin($I);
		$this->loginAsDelegatedAdmin($I, 'p7rp4admin');

		$this->sendCancelledBatchDelete($I, self::ROUTE_LIST, $adminId);

		$I->seeInDatabase('e107_user', array('user_id' => $adminId));
		$I->dontSeeInDatabase('e107_admin_log', array('dblog_remarks like' => 'Refused %'));
	}

	// -----------------------------------------------------------------
	// the single-row controls beside the batch
	// -----------------------------------------------------------------

	/**
	 * The row controls write the columns the batch writes, one row at a time, and not one of
	 * them asked whether the row belonged to an administrator. Unban and Reqverify are the
	 * sharpest: both set user_ban = 2 and rotate user_sess, which is the state the login handler
	 * refuses, so a delegated administrator could lock another administrator out with one posted
	 * trigger and no batch anywhere in the request. Verify blanks the session key instead, and
	 * Resend replaces the password outright on a site that generates passwords at signup.
	 *
	 * The ordinary member goes through the same controls first. A set of controls that had
	 * simply stopped working would make every refusal below meaningless.
	 */
	public function aDelegatedAdministratorCannotReachAnAdministratorThroughTheRowControls(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the single-row user controls on an administrator to a delegated administrator');

		$victimId = $this->seedVictim($I);
		$adminId = $this->seedOtherAdmin($I);
		$seededPassword = (string) $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $adminId));
		$this->loginAsDelegatedAdmin($I, 'p7rp4admin');

		$this->sendRowTrigger($I, self::ROUTE_LIST, 'ban', $victimId);

		$I->assertSame('1',
			(string) $I->grabFromDatabase('e107_user', 'user_ban', array('user_id' => $victimId)),
			'The row Ban control no longer bans an ordinary member for a delegated administrator, '
			.'so the refusals below are a removed feature rather than an authorisation boundary.');

		$this->sendRowTrigger($I, self::ROUTE_LIST, 'unban', $victimId);

		$I->assertSame('2',
			(string) $I->grabFromDatabase('e107_user', 'user_ban', array('user_id' => $victimId)),
			'The row Unban control no longer acts on an ordinary member for a delegated administrator.');

		$this->sendRowTrigger($I, self::ROUTE_LIST, 'verify', $victimId);

		$I->assertSame('0',
			(string) $I->grabFromDatabase('e107_user', 'user_ban', array('user_id' => $victimId)),
			'The row Verify control no longer acts on an ordinary member for a delegated administrator.');

		foreach(array('ban', 'unban', 'verify', 'reqverify', 'resend') as $trigger)
		{
			$this->sendRowTrigger($I, self::ROUTE_LIST, $trigger, $adminId);
		}

		$I->assertSame('0',
			(string) $I->grabFromDatabase('e107_user', 'user_ban', array('user_id' => $adminId)),
			'A delegated administrator holding 4 rewrote user_ban on '.self::OTHER_ADMIN
			.' from a row control. user_ban = 1 is Ban and 2 is the pending state the login handler '
			.'refuses, and either of them locks that administrator out.');

		$I->assertSame(self::OTHER_ADMIN_SESS,
			(string) $I->grabFromDatabase('e107_user', 'user_sess', array('user_id' => $adminId)),
			'A row control rotated or blanked another administrator\'s session key, which signs them '
			.'out of every device they are signed in on.');

		$I->assertSame($seededPassword,
			(string) $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $adminId)),
			'The row Resend control replaced another administrator\'s password: resendActivation() '
			.'calls resetPassword() when signup_option_password is empty.');
	}

	/**
	 * The single-row Delete control: the destructive end of the rule the batch delete already
	 * answers to, reached under a different trigger and with no rule of its own.
	 *
	 * The ordinary member is deleted first, so a route that removed nothing cannot pass for a
	 * guard.
	 */
	public function aDelegatedAdministratorCannotDeleteAnAdministratorFromTheRow(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a confirmed single-row delete of an administrator to a delegated administrator');

		$victimId = $this->seedVictim($I);
		$adminId = $this->seedOtherAdmin($I);
		$this->loginAsDelegatedAdmin($I, 'p7rp4admin');

		$this->sendConfirmedRowDelete($I, self::ROUTE_LIST, $victimId);

		$I->dontSeeInDatabase('e107_user', array('user_id' => $victimId));

		$this->sendConfirmedRowDelete($I, self::ROUTE_LIST, $adminId);

		$I->seeInDatabase('e107_user', array('user_id' => $adminId));
	}

	/**
	 * Positive control for the row delete, in the shape the batch delete's control already has.
	 */
	public function anAdministratorWhoMayModifyAdminPermsStillDeletesAnAdministratorFromTheRow(AcceptanceTester $I)
	{
		$I->wantTo('Keep the single-row delete of an administrator working for a caller holding 3');

		$adminId = $this->seedOtherAdmin($I);
		$this->loginAsDelegatedAdmin($I, 'p7rpPermsadmin');

		$this->sendConfirmedRowDelete($I, self::ROUTE_LIST, $adminId);

		$I->dontSeeInDatabase('e107_user', array('user_id' => $adminId));
	}

	/**
	 * The export batch is exempt from the administrator rule because it only reads. What it
	 * read was every column of every selected row: e_front_tree_model::export() named no
	 * columns and e107Xml::e107Export() falls back to SELECT *, so the password hash and the
	 * session key of any row a delegated administrator could tick came back as a download.
	 *
	 * The row itself has to be in the file, or an export that had simply stopped working would
	 * satisfy every other assertion here.
	 */
	public function theUserExportStreamsNoCredentials(AcceptanceTester $I)
	{
		$I->wantTo('Keep the password hash and the session key out of the user export');

		$victimId = $this->seedVictim($I);
		$victimPassword = (string) $I->grabFromDatabase('e107_user', 'user_password', array('user_id' => $victimId));
		$this->loginAsDelegatedAdmin($I, 'p7rp4admin');

		$this->sendBatch($I, self::ROUTE_LIST, 'export', $victimId);

		$export = $I->grabPageSource();

		$I->assertNotSame(false, strpos($export, self::VICTIM_USER),
			'The export batch no longer returns the row it was given, so nothing below is evidence.');

		$I->assertSame(false, strpos($export, $victimPassword),
			'The user export streamed the password hash of the selected row.');

		$I->assertSame(false, strpos($export, 'user_password'),
			'The user export named the password hash column, so it is selecting it.');

		$I->assertSame(false, strpos($export, 'user_sess'),
			'The user export streamed user_sess, which is the key signup.php activates an account with.');
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
	// item 3: the user class, which the Set user class page vets and the
	// inline editor and the batch do not
	// -----------------------------------------------------------------

	/**
	 * A delegated administrator must not put an account in a class whose
	 * userclass_editclass they do not hold.
	 *
	 * users_admin_ui::checkAllowed() is the rule, and the dedicated Set user
	 * class page runs it over every posted id. The inline editor posts one
	 * field straight at the model through e_admin_ui::InlineAjaxPage(), which
	 * checks the field is inline and the token verifies and nothing else. On a
	 * stock install the widget already draws checkboxes for three classes the
	 * dedicated page refuses, so the crafted value below is the general case
	 * rather than the only one.
	 *
	 * The class this account may manage goes first: an editor that refused
	 * everything would leave the refusal beneath it meaningless.
	 */
	public function aDelegatedAdministratorCannotSetABarredUserClassInline(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an inline user class this administrator may not manage');

		$victimId = $this->seedVictim($I);
		$this->loginAsDelegatedAdmin($I, 'p7rp4admin');

		$this->sendInline($I, $victimId, 'user_class', (string) self::MANAGED_CLASS);

		$I->assertSame((string) self::MANAGED_CLASS,
			(string) $I->grabFromDatabase('e107_user', 'user_class', array('user_id' => $victimId)),
			'The inline editor no longer writes a class this administrator may manage, so the '
			.'refusal asserted below is a broken editor rather than an authorisation boundary.');

		$this->sendInline($I, $victimId, 'user_class', self::MANAGED_CLASS.','.self::BARRED_CLASS);

		$I->assertSame((string) self::MANAGED_CLASS,
			(string) $I->grabFromDatabase('e107_user', 'user_class', array('user_id' => $victimId)),
			'A delegated administrator holding 4 put '.self::VICTIM_USER.' in class '
			.self::BARRED_CLASS.' by posting name=user_class to the inline route. That class carries '
			.'userclass_editclass '.self::BARRED_CLASS.', which this account does not hold, and the '
			.'Set user class page refuses the same id with USRLAN_231.');
	}

	/**
	 * The mirror of the case above, and the half no rendered widget makes
	 * obvious: taking a class away is as much a change to it as adding one.
	 *
	 * The inline editor replaces the whole field with what was posted, and the
	 * option list it renders leaves out the classes it did not draw a control
	 * for, so a save that never mentions class 250 removes it. Only a caller
	 * who may manage that class may do that.
	 */
	public function aDelegatedAdministratorCannotStripABarredUserClassInline(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an inline save that drops a user class this administrator may not manage');

		$victimId = $this->seedVictim($I, self::BARRED_CLASS);
		$this->loginAsDelegatedAdmin($I, 'p7rp4admin');

		$this->sendInline($I, $victimId, 'user_class', (string) self::MANAGED_CLASS);

		$I->assertSame((string) self::BARRED_CLASS,
			(string) $I->grabFromDatabase('e107_user', 'user_class', array('user_id' => $victimId)),
			'A delegated administrator holding 4 took '.self::VICTIM_USER.' out of class '
			.self::BARRED_CLASS.' through the inline editor, by posting a value that leaves it out. '
			.'The widget never renders that class, so this is what an ordinary click does.');
	}

	/**
	 * Positive control for the whole of the user class guard: the main
	 * administrator manages every class, and the inline editor must keep
	 * working for them.
	 */
	public function theMainAdministratorStillSetsAnyUserClassInline(AcceptanceTester $I)
	{
		$I->wantTo('Keep the inline user class editor working for the main administrator');

		$victimId = $this->seedVictim($I);
		$I->loginAsAdmin();

		$this->sendInline($I, $victimId, 'user_class', (string) self::BARRED_CLASS);

		$I->assertSame((string) self::BARRED_CLASS,
			(string) $I->grabFromDatabase('e107_user', 'user_class', array('user_id' => $victimId)),
			'The main administrator can no longer set a user class inline, which is the feature '
			.'rather than the defect.');
	}

	/**
	 * The same rule on the batch, which reaches the column by a different road:
	 * e_admin_ui::handleCommaBatch() saves the tree model directly and never
	 * calls beforeUpdate(), as this file's own docblock at refusesBatch() says.
	 *
	 * Two spellings of one operation are posted. ucadd is what the batch menu
	 * emits for a userclasses field; attach is the generic comma spelling that
	 * reaches the same handler and that the menu never renders.
	 */
	public function aDelegatedAdministratorCannotAttachABarredUserClassThroughTheBatch(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a user class batch this administrator may not manage');

		$victimId = $this->seedVictim($I);
		$this->loginAsDelegatedAdmin($I, 'p7rp4admin');

		$this->sendBatch($I, self::ROUTE_LIST, 'ucadd__user_class__'.self::MANAGED_CLASS, $victimId);

		$I->assertSame((string) self::MANAGED_CLASS,
			(string) $I->grabFromDatabase('e107_user', 'user_class', array('user_id' => $victimId)),
			'The user class batch no longer runs for a class this administrator may manage, so the '
			.'refusals below are a broken batch menu rather than an authorisation boundary.');

		$this->sendBatch($I, self::ROUTE_LIST, 'ucadd__user_class__'.self::BARRED_CLASS, $victimId);

		$I->assertSame((string) self::MANAGED_CLASS,
			(string) $I->grabFromDatabase('e107_user', 'user_class', array('user_id' => $victimId)),
			'A delegated administrator holding 4 attached class '.self::BARRED_CLASS.' to '
			.self::VICTIM_USER.' by posting etrigger_batch=ucadd__user_class__'.self::BARRED_CLASS.'.');

		$this->sendBatch($I, self::ROUTE_LIST, 'attach__user_class__'.self::BARRED_CLASS, $victimId);

		$I->assertSame((string) self::MANAGED_CLASS,
			(string) $I->grabFromDatabase('e107_user', 'user_class', array('user_id' => $victimId)),
			'A delegated administrator holding 4 attached class '.self::BARRED_CLASS.' through the '
			.'generic comma spelling, which the batch menu does not render and which reaches '
			.'e_admin_ui::handleCommaBatch() all the same.');
	}

	/**
	 * The batch writes whichever column the trigger names, so the untyped
	 * spelling replaces user_class outright with the posted value. It is the
	 * shortest form of this attack and it is on no menu.
	 */
	public function aDelegatedAdministratorCannotWriteUserClassWholesaleThroughTheBatch(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a wholesale user class write through the batch');

		$victimId = $this->seedVictim($I);
		$this->loginAsDelegatedAdmin($I, 'p7rp4admin');

		$this->sendBatch($I, self::ROUTE_LIST, 'user_class__'.self::BARRED_CLASS, $victimId);

		$I->assertSame('',
			(string) $I->grabFromDatabase('e107_user', 'user_class', array('user_id' => $victimId)),
			'A delegated administrator holding 4 set user_class to '.self::BARRED_CLASS.' by naming '
			.'the column in etrigger_batch, the same shape that reached user_password and '
			.'user_email before this page confined a batch to its declared columns.');
	}

	/**
	 * The removal half on the batch. Every spelling but attach and deattach
	 * rewrites the column outright, so it discards whatever the selected
	 * accounts held, including the classes this caller may not manage.
	 */
	public function aDelegatedAdministratorCannotStripABarredUserClassThroughTheBatch(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a batch that drops a user class this administrator may not manage');

		$victimId = $this->seedVictim($I, self::BARRED_CLASS);
		$this->loginAsDelegatedAdmin($I, 'p7rp4admin');

		$this->sendBatch($I, self::ROUTE_LIST, 'deattach_all__user_class', $victimId);

		$I->assertSame((string) self::BARRED_CLASS,
			(string) $I->grabFromDatabase('e107_user', 'user_class', array('user_id' => $victimId)),
			'A delegated administrator holding 4 emptied user_class on '.self::VICTIM_USER
			.', which held class '.self::BARRED_CLASS.'. The batch menu\'s own "(Clear All)" spelling '
			.'withdraws whichever options the field declares.');

		$this->sendBatch($I, self::ROUTE_LIST, 'clearAll__user_class__', $victimId);

		$I->assertSame((string) self::BARRED_CLASS,
			(string) $I->grabFromDatabase('e107_user', 'user_class', array('user_id' => $victimId)),
			'A delegated administrator holding 4 emptied user_class on '.self::VICTIM_USER
			.' with the spelling the dispatcher carried before #6089, which names no column of the table.');

		$this->sendBatch($I, self::ROUTE_LIST, 'ucaddall__user_class', $victimId);

		$I->assertSame((string) self::BARRED_CLASS,
			(string) $I->grabFromDatabase('e107_user', 'user_class', array('user_id' => $victimId)),
			'A delegated administrator holding 4 took '.self::VICTIM_USER.' out of class '
			.self::BARRED_CLASS.' with the batch menu\'s own "(Add All)", which replaces the column '
			.'with the classes it offers.');

		$this->sendBatch($I, self::ROUTE_LIST, 'ucdelall__user_class', $victimId);

		$I->assertSame((string) self::BARRED_CLASS,
			(string) $I->grabFromDatabase('e107_user', 'user_class', array('user_id' => $victimId)),
			'"(Clear All)" took '.self::VICTIM_USER.' out of class '.self::BARRED_CLASS
			.'. That batch removes only the classes e_admin_controller_ui hands it, and it is not '
			.'supposed to hand it one this administrator may not manage.');
	}

	/**
	 * Positive control for the two batches that name no class of their own.
	 *
	 * They are on the rendered menu, so a rule that refused them outright would
	 * take a working feature away from every administrator who is not the main
	 * one. e_admin_controller_ui drops the classes this caller may not manage
	 * before either runs, which is what leaves them safe to allow.
	 */
	public function aDelegatedAdministratorStillRunsTheAddAllUserClassBatch(AcceptanceTester $I)
	{
		$I->wantTo('Keep "(Add All)" and "(Clear All)" working for a delegated administrator');

		$victimId = $this->seedVictim($I);
		$this->loginAsDelegatedAdmin($I, 'p7rp4admin');

		$this->sendBatch($I, self::ROUTE_LIST, 'ucaddall__user_class', $victimId);

		$stored = (string) $I->grabFromDatabase('e107_user', 'user_class', array('user_id' => $victimId));

		$I->assertContains((string) self::MANAGED_CLASS, explode(',', $stored),
			'"(Add All)" no longer adds the classes this administrator may manage. It wrote '
			.var_export($stored, true).'.');

		$I->assertNotContains('247', explode(',', $stored),
			'"(Add All)" added class 247, whose userclass_editclass is 250. e_admin_controller_ui '
			.'is supposed to drop it before the batch runs.');

		$this->sendBatch($I, self::ROUTE_LIST, 'ucdelall__user_class', $victimId);

		$I->assertSame('',
			(string) $I->grabFromDatabase('e107_user', 'user_class', array('user_id' => $victimId)),
			'"(Clear All)" no longer removes the classes this administrator may manage.');
	}

	/**
	 * Positive control for the batch half: the main administrator manages every
	 * class and must keep the batch menu.
	 */
	public function theMainAdministratorStillRunsTheUserClassBatch(AcceptanceTester $I)
	{
		$I->wantTo('Keep the user class batch working for the main administrator');

		$victimId = $this->seedVictim($I);
		$I->loginAsAdmin();

		$this->sendBatch($I, self::ROUTE_LIST, 'ucadd__user_class__'.self::BARRED_CLASS, $victimId);

		$I->assertSame((string) self::BARRED_CLASS,
			(string) $I->grabFromDatabase('e107_user', 'user_class', array('user_id' => $victimId)),
			'The main administrator can no longer run the user class batch, which is the feature '
			.'rather than the defect.');
	}

	/**
	 * The guard e_admin_controller_ui already carried for a userclass batch
	 * hands the whole class row to e_user_model::checkClass(), which iterates
	 * an array argument's values and returns false on the first non-numeric
	 * empty one. userclass_icon is empty on every stock class, so the guard
	 * answered no for every class and every caller who is not the main
	 * administrator, and never reached userclass_editclass at all.
	 *
	 * The managed class therefore goes first and is the case that reds without
	 * the fix: the guard used to refuse it. The barred class holds either way,
	 * and is here so that a fix which merely inverted the guard would be caught.
	 *
	 * Welcome messages, not the user list: the guard is in the shared handler,
	 * that page answers to 'M' rather than to a Users permission, and nothing
	 * in users_admin_ui is in the way, so a refusal here is the handler's.
	 *
	 * What is asserted is the value the guard tests, not that gen_intdata is
	 * closed. The guard covers the ucadd and ucaddall spellings; the untyped
	 * gen_intdata__250 and the inline editor reach that column with no
	 * per-value check at all, and a page that wants one has to say so, which is
	 * the per-field authorisation hook this release does not carry.
	 */
	public function theUserclassBatchGuardTestsTheClassManagerAndNotTheWholeRow(AcceptanceTester $I)
	{
		$I->wantTo('Test userclass_editclass in the shared userclass batch guard');

		$managed = $this->seedGenericRow($I, 'wmessage', 'p7rp-managed');
		$barred = $this->seedGenericRow($I, 'wmessage', 'p7rp-barred');
		$this->loginAsDelegatedAdmin($I, 'p7rpMadmin');

		$this->sendBatch($I, self::ROUTE_WMESSAGE, 'ucadd__gen_intdata__'.self::MANAGED_CLASS,
			$managed, 'e-multiselect');

		$I->assertSame((string) self::MANAGED_CLASS,
			(string) $I->grabFromDatabase('e107_generic', 'gen_intdata', array('gen_id' => $managed)),
			'A userclass batch for a class this administrator manages did not run. The guard reads '
			.'the whole class row instead of its userclass_editclass, and check_class() gives up on '
			.'the row\'s empty userclass_icon, so it refuses every class to everybody but the main '
			.'administrator.');

		$this->sendBatch($I, self::ROUTE_WMESSAGE, 'ucadd__gen_intdata__'.self::BARRED_CLASS,
			$barred, 'e-multiselect');

		$I->assertSame('0',
			(string) $I->grabFromDatabase('e107_generic', 'gen_intdata', array('gen_id' => $barred)),
			'A batch put a welcome message behind class '.self::BARRED_CLASS.' for an administrator '
			.'holding only M. userclass_editclass on that class is '.self::BARRED_CLASS.', which this '
			.'account does not hold.');
	}

	/**
	 * The Set user class page writes what its boxes posted, so a class it drew
	 * no box for is a class the save silently drops, and a box the caller never
	 * moved used to cost them the rest of the submission.
	 *
	 * The victim holds 250, which the page's option list leaves out, and 253,
	 * which it now offers and ticks. A delegated administrator adds 1. All
	 * three ids have to be in the column afterwards: 1 because the submission
	 * ran, 253 because a pre-ticked box is not a grant, and 250 because a form
	 * governs the controls it drew and no others.
	 */
	public function theSetUserClassPageKeepsTheClassesItsBoxesDidNotMove(AcceptanceTester $I)
	{
		$I->wantTo('Save a user class change without discarding what the page never offered');

		$victimId = $this->seedVictim($I, self::BARRED_CLASS.','.self::MEMBER_CLASS);
		$this->loginAsDelegatedAdmin($I, 'p7rp4admin');

		$this->sendUserclassPage($I, $victimId, array(self::MEMBER_CLASS, self::MANAGED_CLASS));

		$stored = explode(',', (string) $I->grabFromDatabase('e107_user', 'user_class',
			array('user_id' => $victimId)));

		$I->assertContains((string) self::MANAGED_CLASS, $stored,
			'The Set user class page refused a delegated administrator the class change it asked '
			.'for. Class '.self::MEMBER_CLASS.' is ticked on arrival and carries userclass_editclass '
			.self::BARRED_CLASS.', so a rule applied per posted id abandons the whole submission '
			.'over a box nobody moved.');

		$I->assertContains((string) self::MEMBER_CLASS, $stored,
			'The save dropped class '.self::MEMBER_CLASS.', which the page had drawn ticked. A '
			.'delegated administrator cannot re-tick it, so nothing on any admin screen puts it back.');

		$I->assertContains((string) self::BARRED_CLASS, $stored,
			'The save dropped class '.self::BARRED_CLASS.', which the page drew no box for at all. '
			.'A form governs the controls it drew, and the option list it rendered from is the only '
			.'thing that says which those were.');
	}

	/**
	 * The mirror of the case above: measuring the change rather than each
	 * posted id must still refuse a change the caller may not make.
	 */
	public function aDelegatedAdministratorStillCannotSetABarredUserClassOnTheSetUserClassPage(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a Set user class submission that grants a class this administrator may not manage');

		$victimId = $this->seedVictim($I);
		$this->loginAsDelegatedAdmin($I, 'p7rp4admin');

		$this->sendUserclassPage($I, $victimId, array(self::MANAGED_CLASS));

		$I->assertSame((string) self::MANAGED_CLASS,
			(string) $I->grabFromDatabase('e107_user', 'user_class', array('user_id' => $victimId)),
			'The Set user class page no longer writes a class this administrator may manage, so the '
			.'refusal asserted below is a broken page rather than an authorisation boundary.');

		$this->sendUserclassPage($I, $victimId, array(self::MANAGED_CLASS, self::BARRED_CLASS));

		$I->assertSame((string) self::MANAGED_CLASS,
			(string) $I->grabFromDatabase('e107_user', 'user_class', array('user_id' => $victimId)),
			'A delegated administrator holding 4 put '.self::VICTIM_USER.' in class '
			.self::BARRED_CLASS.' by posting userclass[] to the Set user class page. That class '
			.'carries userclass_editclass '.self::BARRED_CLASS.', which this account does not hold.');
	}

	/**
	 * The third road into the column, and the one that never goes near the
	 * model layer: users_admin_ui::AddSubmitTrigger() hands $_POST to
	 * validatorClass::validateFields(), where user_class is declared with
	 * srcName 'class' and dataType 1, so the posted list becomes the new
	 * account's classes. An administrator holding U1 ("Quick Add User") and
	 * nothing else creates a member of any class it names, with a password it
	 * chooses.
	 *
	 * The barred class goes first here, where the cases above put the control
	 * first, because this route creates the account only once: a refusal posted
	 * after a successful add would be answered by the duplicate login name
	 * rather than by the guard, and the test would prove nothing.
	 *
	 * The refusal stops the submission where the permission grant above it
	 * drops the field and carries on. The permission table is not on the form
	 * this account is served, so there is nothing for it to correct; the class
	 * checkboxes are on the form for every caller, so the error names something
	 * it can untick. That shape is asserted rather than assumed: a guard that
	 * stripped the barred ids and created the account anyway would satisfy the
	 * first assertion below and leave the control reading the row it had just
	 * written, because this route creates the account only once.
	 */
	public function aDelegatedAdministratorCannotQuickAddIntoABarredUserClass(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a quick-add into a user class this administrator may not manage');

		$this->loginAsDelegatedAdmin($I, 'p7rpU1admin');

		$I->amOnPage(self::ROUTE_ADD);
		$I->sendPostRequest(self::ROUTE_ADD,
			$this->quickAddPayload($I, null, null, array(self::MANAGED_CLASS, self::BARRED_CLASS)));

		$I->assertNotContains((string) self::BARRED_CLASS,
			explode(',', (string) $I->grabFromDatabase('e107_user', 'user_class',
				array('user_loginname' => self::CREATED_USER))),
			'A delegated administrator holding U1 ("Quick Add User") and nothing else created '
			.self::CREATED_USER.' in class '.self::BARRED_CLASS.' by posting class[]. That class '
			.'carries userclass_editclass '.self::BARRED_CLASS.', which this account does not hold, '
			.'and the Set user class page refuses the same id with USRLAN_231.');

		$I->dontSeeInDatabase('e107_user', array('user_loginname' => self::CREATED_USER));

		$I->amOnPage(self::ROUTE_ADD);
		$I->sendPostRequest(self::ROUTE_ADD,
			$this->quickAddPayload($I, null, null, array(self::MEMBER_CLASS, self::MANAGED_CLASS)));

		$stored = explode(',', (string) $I->grabFromDatabase('e107_user', 'user_class',
			array('user_loginname' => self::CREATED_USER)));

		$I->assertContains((string) self::MANAGED_CLASS, $stored,
			'The quick-add route no longer creates an account in a class this administrator may '
			.'manage, so the refusal above is a broken route rather than an authorisation boundary.');

		$I->assertContains((string) self::MEMBER_CLASS, $stored,
			'The quick-add route dropped class '.self::MEMBER_CLASS.', which the form ticks for '
			.'every new account. The route reads the posted list, so the box has to reach the '
			.'column and a delegated administrator has to be allowed to leave it as it arrived.');
	}

	// -----------------------------------------------------------------
	// fixture
	// -----------------------------------------------------------------

	/**
	 * Post a batch the way the user list posts one.
	 *
	 * @param string $route
	 * @param string $trigger etrigger_batch value
	 * @param int $id row to apply it to
	 * @param string $selection name of the checkbox column, which each list declares for itself
	 * @return void
	 */
	private function sendBatch(AcceptanceTester $I, $route, $trigger, $id, $selection = 'multiselect')
	{
		$this->postWithToken($I, $route, \Test\AdminListPost::batch($trigger, $id, $selection));
	}

	/**
	 * Post what the inline editor posts: the field, its new value, the session's CSRF token, and
	 * the inline token {@see e_form::renderInline()} puts on every inline link of the page.
	 *
	 * @param int $id row to edit
	 * @param string $field column to write
	 * @param string $value posted value
	 * @param string $listRoute list page whose inline links carry the token
	 * @param string $inlineRoute inline route, less the row id
	 * @return void
	 */
	private function sendInline(AcceptanceTester $I, $id, $field, $value, $listRoute = self::ROUTE_LIST, $inlineRoute = self::ROUTE_INLINE)
	{
		$I->amOnPage($listRoute);

		$I->sendPostRequest($inlineRoute.$id, array(
			'name'    => $field,
			'value'   => $value,
			'pk'      => $id,
			'token'   => $this->grabInlineToken($I),
			'e-token' => $I->grabToken(),
		));
	}

	/**
	 * @return string the inline-edit token on the page currently loaded
	 */
	private function grabInlineToken(AcceptanceTester $I)
	{
		$source = $I->grabPageSource();
		$matches = array();

		if(!preg_match('/data-token=[\'"]([^\'"]+)[\'"]/', $source, $matches))
		{
			throw new \RuntimeException('The user list rendered no inline-edit token to post back.');
		}

		return $matches[1];
	}

	/**
	 * Post the request a batch delete is finished by.
	 *
	 * @param string $route
	 * @param int $id row to delete
	 * @return void
	 */
	private function sendConfirmedBatchDelete(AcceptanceTester $I, $route, $id)
	{
		$this->postWithToken($I, $route, \Test\AdminListPost::confirmedBatchDelete($id));
	}

	/**
	 * Post the shape a confirm screen's Cancel button posts for a selection of two or more: the
	 * ids and the cancel key, and not the confirm key, because a browser sends the button that
	 * was clicked and not its sibling. A one-row selection posts etrigger_delete keyed by the id
	 * instead, which reaches {@see e_admin_ui::ListDeleteTrigger()} and never
	 * {@see users_admin_ui::refusesBatch()}.
	 *
	 * @param string $route
	 * @param int $id row the cancelled delete would have removed
	 * @return void
	 */
	private function sendCancelledBatchDelete(AcceptanceTester $I, $route, $id)
	{
		$this->postWithToken($I, $route, array(
			'etrigger_batch'       => 'delete',
			'delete_confirm_value' => $id,
			'etrigger_cancel'      => 'Cancel',
		));
	}

	/**
	 * Post a single-row control the way the user list posts one: the trigger names the
	 * operation and carries the id, with no checkbox selection in the request at all.
	 *
	 * @param string $route
	 * @param string $trigger operation name, as users_admin::init() maps useraction to it
	 * @param int $id row to apply it to
	 * @return void
	 */
	private function sendRowTrigger(AcceptanceTester $I, $route, $trigger, $id)
	{
		$this->postWithToken($I, $route, array('etrigger_'.$trigger => $id));
	}

	/**
	 * Post the request a single-row delete is finished by.
	 *
	 * @param string $route
	 * @param int $id row to delete
	 * @return void
	 */
	private function sendConfirmedRowDelete(AcceptanceTester $I, $route, $id)
	{
		$this->postWithToken($I, $route, \Test\AdminListPost::confirmedRowDelete($id));
	}

	/**
	 * Post the Set user class page the way its own form posts: the checkboxes
	 * are userclass[], the row id is both on the query string and in the hidden
	 * field, and the submission is the update button.
	 *
	 * @param int $id row to apply it to
	 * @param array $classes class ids the boxes are ticked for
	 * @return void
	 */
	private function sendUserclassPage(AcceptanceTester $I, $id, array $classes)
	{
		$this->postWithToken($I, self::ROUTE_USERCLASS.$id, array(
			'etrigger_updateclass' => 'update',
			'userid'               => $id,
			'userclass'            => $classes,
		));
	}

	/**
	 * @param string $route
	 * @param array $payload posted keys, which the route's own e-token is added to
	 * @return void
	 */
	private function postWithToken(AcceptanceTester $I, $route, array $payload)
	{
		$payload['e-token'] = $this->grabTokenFrom($I, $route);

		$I->sendPostRequest($route, $payload);
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
	 * @param array|null $classes user class ids to ask for, or null for none
	 * @return array
	 */
	private function quickAddPayload(AcceptanceTester $I, $perms = null, $token = null, $classes = null)
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
			'e-token'         => $token === null ? $I->grabToken() : $token,
		);

		if($perms !== null)
		{
			$payload['perms'] = array($perms);
		}

		if($classes !== null)
		{
			$payload['class'] = $classes;
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
			'e-token'               => $I->grabToken(),
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

		return $I->grabToken();
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
	 * @param string $class user_class the account starts with
	 * @return int user id
	 */
	private function seedVictim(AcceptanceTester $I, $class = '')
	{
		return (int) $I->haveInDatabase('e107_user', array(
			'user_name'      => self::VICTIM_USER,
			'user_loginname' => self::VICTIM_USER,
			'user_email'     => self::VICTIM_USER.'@example.com',
			'user_password'  => \Test\Password::hash(self::VICTIM_USER),
			'user_join'      => 1262304000,
			'user_class'     => (string) $class,
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
			'user_password'  => \Test\Password::hash(self::OTHER_ADMIN),
			'user_sess'      => self::OTHER_ADMIN_SESS,
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
	 * Seed an e107_generic row of one type.
	 *
	 * @param string $type gen_type
	 * @param string $title
	 * @param int $datestamp 0 marks a custom rank
	 * @return int gen_id
	 */
	private function seedGenericRow(AcceptanceTester $I, $type, $title, $datestamp = 1262304000)
	{
		return (int) $I->haveInDatabase('e107_generic', array(
			'gen_type'      => $type,
			'gen_datestamp' => $datestamp,
			'gen_user_id'   => 1,
			'gen_ip'        => $title,
			'gen_intdata'   => 0,
			'gen_chardata'  => $title,
		));
	}

	/**
	 * Open the custom page options in every mode, the chapters' included, see each refused, and see no save through any of them reach a core preference.
	 *
	 * @return void
	 */
	private function seeTheCustomPageOptionsRefusedInEveryMode(AcceptanceTester $I)
	{
		foreach(array('page', 'menu', 'overview', 'cat') as $mode)
		{
			$route = '/e107_admin/cpage.php?mode='.$mode.'&action=prefs';
			$I->amOnPage($route);

			$I->see('You do not have permission to view this page.');
			$I->dontSeeElement('[name="listPages"]');

			$I->sendPostRequest($route, array(
				'etrigger_save'   => 'save',
				'user_new_period' => self::PREF_ATTACK,
				'e-token'         => $this->grabTokenFrom($I, self::ROUTE_OVERVIEW_LIST),
			));
		}

		$I->assertSame(0, preg_match('/user_new_period\W+'.self::PREF_ATTACK.'\W/', (string) $I->grabFromDatabase('e107_core', 'e107_value', array('e107_name' => 'SitePrefs'))), 'A custom page options save rewrote a core preference.');
	}

	/**
	 * Seed a custom page row, which is a page, a menu or both by which of its titles it has.
	 *
	 * @param string $pageTitle
	 * @param string $menuTitle
	 * @return int page_id
	 */
	private function seedPageRow(AcceptanceTester $I, $pageTitle, $menuTitle)
	{
		return (int) $I->haveInDatabase('e107_page', array(
			'page_title'     => $pageTitle,
			'page_sef'       => '',
			'page_chapter'   => 0,
			'page_text'      => $pageTitle,
			'page_author'    => 1,
			'page_datestamp' => 1262304000,
			'page_class'     => 0,
			'page_password'  => '',
			'page_template'  => 'default',
			'page_order'     => 0,
			'menu_name'      => $menuTitle === '' ? '' : 'p7rpmenu',
			'menu_title'     => $menuTitle,
			'menu_text'      => $menuTitle,
			'menu_image'     => '',
			'menu_icon'      => '',
		));
	}

	/**
	 * Post what the custom page edit form posts for the two titles.
	 *
	 * @param string $route edit route with the row id
	 * @param string $pageTitle
	 * @param string $menuTitle
	 * @param string $tokenRoute a list the administrator may open, to take the e-token from
	 * @param array $more further columns to post
	 * @return void
	 */
	private function sendPageEdit(AcceptanceTester $I, $route, $pageTitle, $menuTitle, $tokenRoute, array $more = array())
	{
		$I->sendPostRequest($route, array_merge(array(
			'etrigger_submit' => 'update',
			'page_title'      => $pageTitle,
			'page_text'       => $pageTitle,
			'menu_name'       => $menuTitle === '' ? '' : 'p7rpmenu',
			'menu_title'      => $menuTitle,
			'e-token'         => $this->grabTokenFrom($I, $tokenRoute),
		), $more));
	}

	/**
	 * Put a custom page row in a chapter that declares one custom field, with a value for it stored on the row.
	 *
	 * @param int $id page_id
	 * @param string $value
	 * @return void
	 */
	private function seedCustomFieldValue(AcceptanceTester $I, $id, $value)
	{
		$chapter = (int) $I->haveInDatabase('e107_page_chapters', array(
			'chapter_parent'           => 0,
			'chapter_name'             => 'p7rp fields chapter',
			'chapter_sef'              => 'p7rp-fields-chapter',
			'chapter_meta_description' => '',
			'chapter_meta_keywords'    => '',
			'chapter_manager'          => 254,
			'chapter_icon'             => '',
			'chapter_image'            => '',
			'chapter_order'            => 0,
			'chapter_template'         => 'default',
			'chapter_visibility'       => 0,
			'chapter_fields'           => json_encode(array(
				'__tabs__' => array('additional' => 'Extra'),
				'secret'   => array('title' => 'Secret', 'type' => 'text', 'writeParms' => '', 'help' => ''),
			)),
		));

		$I->updateInDatabase('e107_page', array('page_chapter' => $chapter, 'page_fields' => json_encode(array('secret' => $value))), array('page_id' => $id));
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
			'user_password'  => \Test\Password::hash(self::PENDING_USER),
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
			'user_password'  => \Test\Password::hash($loginName),
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
		$I->amOnProbe('act=reset');

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
		$I->amOnProbe('act=dump');

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
// Fixture for AdminRoutePermsCest.
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
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

		// Stored by a create route this Cest posted to.
		\$db->delete('generic', "gen_ip LIKE 'p7rp%'");

		// Queued by resend_to_all(), which runs with mail_force_queue so nothing
		// is actually sent, but the rows outlive the request that made them.
		\$db->delete('mail_recipients', "mail_recipient_name LIKE 'p7rp%'");
		\$db->delete('mail_content', "mail_title = 'RESEND ACTIVATION'");

		// Refusals from earlier tests in this Cest, which a later one asserts the absence of.
		\$db->delete('admin_log', "dblog_remarks LIKE 'Refused %'");

		// Custom pages created through the application, and the menus row each create adds.
		\$db->delete('menus', "menu_path IN (SELECT page_id FROM ".MPREFIX."page WHERE page_sef LIKE 'p7rp%')");
		\$db->delete('page', "page_sef LIKE 'p7rp%'");

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
