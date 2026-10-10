<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Routing;

use e107\Http\Request;
use e107\Http\RequestHandlerInterface;

/**
 * The table claims what a route answers to, and leaves the rest to the legacy path.
 */
class RouteTableTest extends \Codeception\Test\Unit
{
	protected function _before()
	{
		require_once(__DIR__.'/redirect_home_handler.php');
	}

	public function testAFlagRouteClaimsItsFlagWithNoParameters()
	{
		$table = new RouteTable(array(Route::flag('user/logout', 'logout', $this->factory())));

		$match = $table->match(new Request('GET', 'logout&e-token=abc'));

		self::assertInstanceOf(RouteMatch::class, $match);
		self::assertSame('user/logout', $match->getName());
		self::assertSame(array(), $match->getParameters());
	}

	public function testAnUnclaimedRequestIsLeftToTheLegacyPath()
	{
		$table = new RouteTable(array(Route::flag('user/logout', 'logout', $this->factory())));

		self::assertNull($table->match(new Request('GET', 'news.1')));
	}

	public function testTheHandlerIsNotBuiltUntilTheRouteIsTaken()
	{
		$built = 0;
		$table = new RouteTable(array(Route::flag('user/logout', 'logout', $this->factory($built))));

		$table->match(new Request('GET', 'news.1'));
		$match = $table->match(new Request('GET', 'logout'));
		self::assertSame(0, $built);

		self::assertInstanceOf(RequestHandlerInterface::class, $match->handler());
		self::assertSame(1, $built);
	}

	public function testAFactoryThatBuildsNoHandlerIsRefused()
	{
		$table = new RouteTable(array(Route::flag('user/logout', 'logout', function () { return new \stdClass(); })));

		$this->expectException(\UnexpectedValueException::class);

		$table->match(new Request('GET', 'logout'))->handler();
	}

	public function testTwoRoutesMayNotShareAName()
	{
		$this->expectException(\InvalidArgumentException::class);

		new RouteTable(array(
			Route::flag('user/logout', 'logout', $this->factory()),
			Route::flag('user/logout', 'signout', $this->factory()),
		));
	}

	public function testATableHoldsOnlyRoutes()
	{
		$this->expectException(\InvalidArgumentException::class);

		new RouteTable(array('logout'));
	}

	/**
	 * @param int $built counts the handlers built
	 * @return callable
	 */
	private function factory(&$built = 0)
	{
		return function () use (&$built)
		{
			$built++;

			return new RedirectHomeHandler();
		};
	}
}

