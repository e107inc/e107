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
 * The core route table that index.php consults, as e107_core/route_table.php composes it from e107_core/routes.php.
 */
class routesTest extends \Test\Unit
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
		foreach(array('', 'news', 'logoutx', 'x&logout', 'route=system/error/notfound') as $query)
		{
			self::assertNull($this->table()->match(new Request('GET', $query)), $query);
		}
	}

	/**
	 * @dataProvider errorRoutes
	 * @param string $name
	 * @param int $status
	 */
	public function testAnErrorRouteIsTakenByNameAndAnswersWithItsStatus($name, $status)
	{
		$match = $this->table()->named($name);

		self::assertNotNull($match);
		self::assertInstanceOf(\e107\System\ErrorPage::class, $match->createHandler());

		$response = $match->createHandler()->handle(new Request('GET', ''));

		self::assertSame($status, $response->getStatusCode());
		self::assertSame(LAN_ERROR, $response->getPage()->getCaption());
		self::assertSame('error_page_'.$status, $response->getPage()->getMode());
	}

	/**
	 * @return array
	 */
	public function errorRoutes()
	{
		return array(
			'not found' => array('system/error/notfound', 404),
			'forbidden' => array('system/error/forbidden', 403),
			'server error' => array('system/error/servererror', 500),
		);
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
		return require(e_CORE.'route_table.php');
	}
}
