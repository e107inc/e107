<?php

/**
 * End-to-end tests for the admin permission-emulation overlay (#5745):
 * starting emulation via the dedicated users.php route, the faithful
 * denial of perm-gated admin pages, the always-reachable stop route
 * (escape hatch), and CSRF enforcement on the start route.
 */
class PermEmulationCest
{
	const START_ROUTE = '/e107_admin/users.php?mode=main&action=emulate';
	const STOP_ROUTE = '/e107_admin/users.php?mode=main&action=emulatestop';
	const BANNER_MARKER = 'admin-icon-emulation';

	/**
     * @param \AcceptanceTester $I
     */
    public function _before($I)
	{
		$I->loginAsAdmin();
	}

	/**
     * @param \AcceptanceTester $I
     */
    public function emulationAppliesAndStopRouteEscapesLowPerms($I)
	{
		$I->wantTo('Emulate a low-perm admin, see the banner, get denied on prefs.php, then stop from the banner route');

		$targetId = $this->seedSubAdmin($I, 'emucest_subadmin');

		// Sanity: prefs.php (perm '1') is reachable as the real main admin
		$I->amOnPage('/e107_admin/prefs.php');
		$I->seeInCurrentUrl('prefs.php');

		// Start emulation via the dedicated POST route (the user list
		// renders token-bearing forms; the bare dashboard does not)
		$token = $I->grabFreshAdminToken('/e107_admin/users.php');
		$I->sendPostRequest(self::START_ROUTE, array(
			'userid'  => $targetId,
			'e-token' => $token,
		));

		// The audit-log row proves the route ran to completion (a mid-route
		// fatal would mutate the session but skip the log write)
		$I->seeInDatabase('e107_admin_log', array('dblog_eventcode' => 'USET_102'));

		// Banner renders on every admin page while emulating
		$I->amOnPage('/e107_admin/admin.php');
		$I->seeInSource(self::BANNER_MARKER);
		$I->seeInSource('emucest_subadmin');

		// A perm-gated page now denies faithfully (perm '1' not in 'C')
		$I->amOnPage('/e107_admin/prefs.php');
		$I->dontSeeInCurrentUrl('prefs.php');

		// The stop route stays reachable although the emulated permissions
		// would deny users.php itself (the escape hatch). The banner's stop
		// form is the only token source the emulated perms can reach, which
		// also proves the banner form carries the CSRF token.
		$token = $I->grabFreshAdminToken('/e107_admin/admin.php');
		$I->sendPostRequest(self::STOP_ROUTE, array(
			'e-token' => $token,
		));

		$I->seeInDatabase('e107_admin_log', array('dblog_eventcode' => 'USET_103'));

		$I->amOnPage('/e107_admin/admin.php');
		$I->dontSeeInSource(self::BANNER_MARKER);

		// Real permissions are back
		$I->amOnPage('/e107_admin/prefs.php');
		$I->seeInCurrentUrl('prefs.php');
	}

	/**
     * @param \AcceptanceTester $I
     */
    public function startRejectsForgedRequestWithoutToken($I)
	{
		$I->wantTo('Reject an emulation-start POST that omits the e-token');

		$targetId = $this->seedSubAdmin($I, 'emucest_csrf');

		$I->sendPostRequest(self::START_ROUTE, array(
			'userid' => $targetId,
		));

		$I->seeInSource('Unauthorized access!');

		$I->amOnPage('/e107_admin/admin.php');
		$I->dontSeeInSource(self::BANNER_MARKER);
	}

	/**
     * @param \AcceptanceTester $I
     */
    public function startRefusesMainAdminTarget($I)
	{
		$I->wantTo('Refuse to emulate a main administrator');

		$targetId = $I->haveInDatabase('e107_user', $this->userRow('emucest_mainadmin', '0'));

		$token = $I->grabFreshAdminToken('/e107_admin/users.php');
		$I->sendPostRequest(self::START_ROUTE, array(
			'userid'  => $targetId,
			'e-token' => $token,
		));

		$I->amOnPage('/e107_admin/admin.php');
		$I->dontSeeInSource(self::BANNER_MARKER);
	}

	private function seedSubAdmin(AcceptanceTester $I, $name)
	{
		return $I->haveInDatabase('e107_user', $this->userRow($name, 'C'));
	}

	private function userRow($name, $perms)
	{
		return array(
			'user_name'      => $name,
			'user_loginname' => $name,
			'user_email'     => $name . '@example.com',
			'user_password'  => md5($name),
			'user_join'      => 1262304000,
			'user_class'     => '',
			'user_admin'     => 1,
			'user_perms'     => $perms,
			'user_xup'       => '',
			'user_prefs'     => '',
			'user_signature' => '',
			'user_realm'     => '',
		);
	}
}
