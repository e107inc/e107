<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Admin;

/**
 * The GET that dismisses a notice. Nothing here touches a global: the query,
 * the token mode and the registry all arrive as arguments, which is what lets
 * the refusal be tested at all.
 */
class DismissRequestTest extends \Test\Unit
{
	/** @var array */
	private $called;

	/** @var array */
	private $dismissible;

	protected function _before()
	{
		require_once(e_HANDLER.'Admin/DismissRequest.php');

		$this->called = array();
		$called = &$this->called;
		$this->dismissible = array('unit-test' => function($id) use (&$called) { $called[] = $id; });
	}

	public function testNothingAskedForIsNothingDone()
	{
		$request = new DismissRequest(array(), true);

		self::assertSame(DismissRequest::NONE, $request->act($this->dismissible));
		self::assertSame(array(), $this->called);
	}

	public function testARequestWithoutItsTokenIsRefused()
	{
		$request = new DismissRequest(array('dismiss' => 'unit-test'), true);

		self::assertSame(DismissRequest::REFUSED, $request->act($this->dismissible));
		self::assertSame(array(), $this->called);
	}

	public function testATokenIsNotAskedForWhenTheSessionIssuesNone()
	{
		$request = new DismissRequest(array('dismiss' => 'unit-test'), false);

		self::assertSame(DismissRequest::DONE, $request->act($this->dismissible));
		self::assertSame(array('unit-test'), $this->called);
	}

	public function testAnIdNobodyOfferedIsUnknown()
	{
		$request = new DismissRequest(array('dismiss' => 'other', 'e-token' => 'present'), true);

		self::assertSame(DismissRequest::UNKNOWN, $request->act($this->dismissible));
		self::assertSame(array(), $this->called);

		$request = new DismissRequest(array('dismiss' => array('unit-test'), 'e-token' => 'present'), true);
		self::assertSame(DismissRequest::NONE, $request->act($this->dismissible), 'an array is no id');
	}

	public function testTheCallableIsHandedTheId()
	{
		$request = new DismissRequest(array('dismiss' => 'unit-test', 'e-token' => 'present'), true);

		self::assertSame(DismissRequest::DONE, $request->act($this->dismissible));
		self::assertSame(array('unit-test'), $this->called);
	}

	public function testALinkCarriesTheIdAndTheToken()
	{
		$bare = DismissRequest::link('unit-test', '/e107_admin/admin.php', 'tok', 'Never again');
		self::assertSame("<a class='btn btn-xs btn-primary' href='/e107_admin/admin.php?dismiss=unit-test&amp;e-token=tok'>Never again</a>", $bare);

		$query = DismissRequest::link('unit test', '/e107_admin/cron.php?mode=main&amp;action=list', 'tok', 'x');
		self::assertStringContainsString("cron.php?mode=main&amp;action=list&amp;dismiss=unit%20test&amp;e-token=tok'", $query);
	}
}
