<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2026 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	/**
	 * Tests for the admin permission-emulation overlay (issue #5745):
	 * e_user_model::$_effective_model delegation plus the e_user lifecycle
	 * methods emulateAs(), loadEmulation() and stopEmulation().
	 *
	 * The constants themselves (ADMINPERMS, USERCLASS_LIST, USERNAME, ...)
	 * are frozen by the suite bootstrap before any test runs, so the
	 * assertions here target the model methods init_session() derives each
	 * constant from instead:
	 *
	 *   getId()            -> USERID        (real during emulation)
	 *   get('user_name')   -> USERNAME      (real during emulation)
	 *   isAdmin()          -> ADMIN         (real during emulation)
	 *   getAdminPerms()    -> ADMINPERMS    (emulated)
	 *   getClassList(true) -> USERCLASS_LIST (emulated)
	 */
	class e_userEmulationTest extends \Codeception\Test\Unit
	{

		/** @var array fixture user ids keyed by role */
		protected $fixtures = array();

		/** @var array user 1 (main admin) database row */
		protected $mainAdminRow;

		protected function _before()
		{
			$db = e107::getDb();

			$rows = array(
				'member' => array(
					'user_name' => 'emu_member',
					'user_loginname' => 'emu_member',
					'user_email' => 'emu_member@example.com',
					'user_password' => md5('emu_member'),
					'user_join' => 1262304000,
					'user_class' => '3',
					'user_admin' => 0,
					'user_perms' => '',
				),
				'subadmin' => array(
					'user_name' => 'emu_subadmin',
					'user_loginname' => 'emu_subadmin',
					'user_email' => 'emu_subadmin@example.com',
					'user_password' => md5('emu_subadmin'),
					'user_join' => 1262304000,
					'user_class' => '3',
					'user_admin' => 1,
					'user_perms' => 'C.F.4',
				),
				'subadmin2' => array(
					'user_name' => 'emu_subadmin2',
					'user_loginname' => 'emu_subadmin2',
					'user_email' => 'emu_subadmin2@example.com',
					'user_password' => md5('emu_subadmin2'),
					'user_join' => 1262304000,
					'user_class' => '',
					'user_admin' => 1,
					'user_perms' => '1',
				),
				'mainadmin2' => array(
					'user_name' => 'emu_mainadmin2',
					'user_loginname' => 'emu_mainadmin2',
					'user_email' => 'emu_mainadmin2@example.com',
					'user_password' => md5('emu_mainadmin2'),
					'user_join' => 1262304000,
					'user_class' => '',
					'user_admin' => 1,
					'user_perms' => '0',
				),
				'legacymain' => array(
					'user_name' => 'emu_legacymain',
					'user_loginname' => 'emu_legacymain',
					'user_email' => 'emu_legacymain@example.com',
					'user_password' => md5('emu_legacymain'),
					'user_join' => 1262304000,
					'user_class' => '',
					'user_admin' => 1,
					'user_perms' => '0.',
				),
			);

			foreach($rows as $key => $row)
			{
				$id = $db->insert('user', $row);
				$this->assertNotEmpty($id, "Could not insert '{$key}' fixture user");
				$this->fixtures[$key] = (int) $id;
			}

			$this->mainAdminRow = $db->retrieve('user', '*', 'user_id=1');
			$this->assertNotEmpty($this->mainAdminRow);

			e107::getSession()->clear(e_user::EMULATE_SESSION_KEY);
		}

		protected function _after()
		{
			e107::getSession()->clear(e_user::EMULATE_SESSION_KEY);

			$asKey = e107::getPref('cookie_name', 'e107cookie') . '_as';
			unset($_SESSION[$asKey]);

			if(!empty($this->fixtures))
			{
				e107::getDb()->delete('user', 'user_id IN (' . implode(',', array_map('intval', $this->fixtures)) . ')');
			}
			$this->fixtures = array();
		}

		/**
		 * Detached current-user object: constructor skipped, so neither the
		 * suite's real session state nor the core/e107/current_user registry
		 * entry is touched.
		 *
		 * @param array $data user row
		 * @return e_user
		 */
		private function makeUser($data)
		{
			$user = $this->make('e_user');
			$user->setData($data);
			return $user;
		}

		/**
		 * @return e_user main-admin grantor (user 1's data)
		 */
		private function makeMainAdmin()
		{
			return $this->makeUser($this->mainAdminRow);
		}

		/**
		 * @param string $key fixture role key
		 * @return array user row
		 */
		private function row($key)
		{
			return e107::getDb()->retrieve('user', '*', 'user_id=' . $this->fixtures[$key]);
		}

		public function testOverlayDelegatesPermsAndClassList()
		{
			$user = $this->makeMainAdmin();
			$this->assertSame('0', $user->getAdminPerms());

			$this->assertTrue($user->emulateAs($this->fixtures['subadmin']));

			$this->assertSame('C.F.4', $user->getAdminPerms());

			// The old constants-layer implementation copied the raw user_class
			// field, losing every implicit class. The overlay computes the
			// target's full list through a real e_user_model.
			$list = $user->getClassList();
			$this->assertContains(3, $list);
			$this->assertContains(e_UC_MEMBER, $list);
			$this->assertContains(e_UC_ADMIN, $list);
			$this->assertContains(e_UC_READONLY, $list);
			$this->assertContains(e_UC_PUBLIC, $list);
			$this->assertNotContains(e_UC_MAINADMIN, $list);

			$user->stopEmulation();
			$this->assertSame('0', $user->getAdminPerms());
			$this->assertContains(e_UC_MAINADMIN, $user->getClassList());
		}

		public function testOverlayNeverDelegatesIdentity()
		{
			$user = $this->makeMainAdmin();
			$realName = $user->get('user_name');
			$realData = $user->getUserData();

			$this->assertTrue($user->emulateAs($this->fixtures['subadmin']));

			$this->assertSame(1, $user->getId());
			$this->assertSame($realName, $user->get('user_name'));
			$this->assertSame($realName, $user->getName());
			$this->assertTrue($user->isAdmin());
			$this->assertTrue($user->isRealMainAdmin());

			// getUserData() feeds e107::user() and must stay identity-pure
			$emulatedData = $user->getUserData();
			$this->assertSame($realData['user_name'], $emulatedData['user_name']);
			$this->assertSame($realData['user_perms'], $emulatedData['user_perms']);
			$this->assertSame($realData['user_class'], $emulatedData['user_class']);

			// ...while the authorization getters are emulated
			$this->assertSame('C.F.4', $user->getAdminPerms());
			$this->assertNotSame($realData['user_class'], $user->getClassList(true));
			$this->assertSame($this->fixtures['subadmin'], $user->getEmulatedUser()->getId());
		}

		public function testV1V2ParityDuringEmulation()
		{
			$user = $this->makeMainAdmin();
			$this->assertTrue($user->emulateAs($this->fixtures['subadmin']));
			$this->assertApiParity($user);
		}

		public function testV1V2ParityWithoutEmulation()
		{
			$user = $this->makeMainAdmin();
			$this->assertNull($user->getEmulatedUser());
			$this->assertApiParity($user);
		}

		/**
		 * Regression net for the 7d94bca7d6 class of bug: the v1 functions
		 * (getperms()/check_class()) and the v2 model methods
		 * (checkAdminPerms()/checkClass()) must agree whether or not the
		 * emulation overlay is active. Explicit arguments are passed because
		 * the ADMINPERMS/USERCLASS_LIST constants are frozen to the CLI
		 * bootstrap's values for the whole suite.
		 *
		 * @param e_user $user
		 * @return void
		 */
		private function assertApiParity($user)
		{
			$permCodes = array('C', 'F', '4', 'C|X', 'X', '0', '1|X');
			foreach($permCodes as $perm)
			{
				$this->assertSame(
					getperms($perm, $user->getAdminPerms()),
					$user->checkAdminPerms($perm),
					"getperms() and checkAdminPerms() disagree on '{$perm}'"
				);
			}

			$classCodes = array('3', '2', '0', (string) e_UC_MEMBER, (string) e_UC_MAINADMIN, '-3', '-2');
			foreach($classCodes as $class)
			{
				$this->assertSame(
					check_class($class, $user->getClassList()),
					$user->checkClass($class, false),
					"check_class() and checkClass() disagree on '{$class}'"
				);
			}
		}

		public function testLiteralPluginPermCodeDiverges()
		{
			// Documents why e_admin_dispatcher route checks must special-case
			// 'perm' => 'P': getperms('P') resolves the current plugin's
			// P<id> code from the request path, while a literal 'P' lookup
			// against the perm string finds nothing.
			$this->assertFalse(e_userperms::simulateHasAdminPerms('P', 'P5'));
			$this->assertTrue(e_userperms::simulateHasAdminPerms('P5', 'P5'));
		}

		public function testEmulateAsGuards()
		{
			$user = $this->makeMainAdmin();

			$this->assertFalse($user->emulateAs(0));
			$this->assertFalse($user->emulateAs(1)); // self
			$this->assertFalse($user->emulateAs($this->fixtures['mainadmin2'])); // main-admin target
			$this->assertFalse($user->emulateAs($this->fixtures['legacymain'])); // legacy '0.' main-admin target
			$this->assertFalse($user->emulateAs($this->fixtures['member'])); // non-admin target (admins only for now)
			$this->assertFalse($user->emulateAs(99999999)); // nonexistent target

			$this->assertNull($user->getEmulatedUser());
			$this->assertEmpty(e107::getSession()->get(e_user::EMULATE_SESSION_KEY));

			// grantor who is not a main admin
			$grantor = $this->makeUser($this->row('subadmin'));
			$this->assertFalse($grantor->emulateAs($this->fixtures['subadmin2']));
			$this->assertEmpty(e107::getSession()->get(e_user::EMULATE_SESSION_KEY));

			// happy path; no second emulation while one is active
			$this->assertTrue($user->emulateAs($this->fixtures['subadmin']));
			$this->assertSame($this->fixtures['subadmin'], (int) e107::getSession()->get(e_user::EMULATE_SESSION_KEY));
			$this->assertFalse($user->emulateAs($this->fixtures['subadmin2']));
			$this->assertSame($this->fixtures['subadmin'], $user->getEmulatedUser()->getId());
		}

		public function testLoadEmulationAppliesValidSessionState()
		{
			e107::getSession()->set(e_user::EMULATE_SESSION_KEY, $this->fixtures['subadmin']);

			$user = $this->makeMainAdmin()->loadEmulation();

			$this->assertNotNull($user->getEmulatedUser());
			$this->assertSame('C.F.4', $user->getAdminPerms());
		}

		public function testLoadEmulationRevokesInvalidSessionState()
		{
			$cases = array(
				'missing target' => array($this->makeMainAdmin(), 99999999),
				'self target' => array($this->makeMainAdmin(), 1),
				'main-admin target' => array($this->makeMainAdmin(), $this->fixtures['mainadmin2']),
				'legacy main-admin target' => array($this->makeMainAdmin(), $this->fixtures['legacymain']),
				'non-admin target' => array($this->makeMainAdmin(), $this->fixtures['member']),
				'non-main-admin grantor' => array($this->makeUser($this->row('subadmin')), $this->fixtures['subadmin2']),
			);

			foreach($cases as $label => $case)
			{
				$user = $case[0];
				e107::getSession()->set(e_user::EMULATE_SESSION_KEY, $case[1]);

				$user->loadEmulation();

				$this->assertNull($user->getEmulatedUser(), "Overlay still active after: {$label}");
				$this->assertEmpty(e107::getSession()->get(e_user::EMULATE_SESSION_KEY), "Session key not cleared after: {$label}");
			}
		}

		public function testLoadEmulationIdempotentAcrossRequests()
		{
			e107::getSession()->set(e_user::EMULATE_SESSION_KEY, $this->fixtures['subadmin']);
			$user = $this->makeMainAdmin();

			$user->loadEmulation();
			$this->assertNotNull($user->getEmulatedUser());

			// The next request re-verifies on a live overlaid object. The
			// grantor's isMainAdmin() now reports the target's (false)
			// status, so re-verification must read the real identity
			// (isRealMainAdmin()) or emulation would self-revoke here.
			$user->loadEmulation();
			$this->assertNotNull($user->getEmulatedUser());
			$this->assertSame($this->fixtures['subadmin'], (int) e107::getSession()->get(e_user::EMULATE_SESSION_KEY));
		}

		public function testStopEmulation()
		{
			$user = $this->makeMainAdmin();
			$this->assertTrue($user->emulateAs($this->fixtures['subadmin']));

			$this->assertTrue($user->stopEmulation());

			$this->assertNull($user->getEmulatedUser());
			$this->assertEmpty(e107::getSession()->get(e_user::EMULATE_SESSION_KEY));
			$this->assertSame('0', $user->getAdminPerms());
			$this->assertContains(e_UC_MAINADMIN, $user->getClassList());

			$this->assertFalse($user->stopEmulation()); // nothing left to clear
		}

		public function testLoginAsCoexistence()
		{
			// Both session keys set at once: the admin-area path
			// (loadEmulation) applies the overlay without touching the
			// Login-As parent state, which only loadAs() (front-end) reads.
			$asKey = e107::getPref('cookie_name', 'e107cookie') . '_as';
			$_SESSION[$asKey] = $this->fixtures['member'];
			e107::getSession()->set(e_user::EMULATE_SESSION_KEY, $this->fixtures['subadmin']);

			$user = $this->makeMainAdmin()->loadEmulation();

			$this->assertNotNull($user->getEmulatedUser());
			$this->assertFalse($user->getParentId());
			$this->assertSame(1, $user->getId());

			unset($_SESSION[$asKey]);
		}

		public function testSystemUserNeverCarriesOverlay()
		{
			$sys = e107::getSystemUser($this->fixtures['subadmin'], false);

			$this->assertNull($sys->getEmulatedUser());
			$this->assertSame('C.F.4', $sys->getAdminPerms());
		}
	}
