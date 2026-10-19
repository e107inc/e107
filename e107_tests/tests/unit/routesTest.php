<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

use e107\Http\Request;
use e107\Routing\RouteTable;

/**
 * The core route list that index.php consults, as e107_core/routes.php returns it.
 */
class routesTest extends \Codeception\Test\Unit
{
	public function testATokenlessLogoutIsTheLogoutRoute()
	{
		$match = $this->logoutMatch();

		self::assertNotNull($match);
		self::assertSame('user/logout', $match->getName());
		self::assertSame(array(), $match->getParameters());
	}

	public function testEverythingElseIsLeftToTheLegacyPath()
	{
		foreach(array('', 'news', 'logoutx', 'x&logout') as $query)
		{
			self::assertNull($this->table()->match(new Request('GET', $query)), $query);
		}
	}

	public function testTheLogoutRouteBuildsTheLogoutConfirmation()
	{
		self::assertInstanceOf(\e107\User\LogoutConfirmation::class, $this->logoutMatch()->createHandler());
	}

	/**
	 * @return \e107\Routing\RouteMatch|null
	 */
	private function logoutMatch()
	{
		return $this->table()->match(new Request('GET', 'logout'));
	}

	/**
	 * @return RouteTable
	 */
	private function table()
	{
		return new RouteTable(require(e_CORE.'routes.php'));
	}
}
