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

	public function testAQueryWordRouteClaimsItsWordWithNoParameters()
	{
		$match = $this->logoutTable($this->factory())->match(new Request('GET', 'logout&e-token=abc'));

		self::assertInstanceOf(RouteMatch::class, $match);
		self::assertSame('user/logout', $match->getName());
		self::assertSame(array(), $match->getParameters());
	}

	public function testAnUnclaimedRequestIsLeftToTheLegacyPath()
	{
		self::assertNull($this->logoutTable($this->factory())->match(new Request('GET', 'news.1')));
	}

	public function testTheHandlerIsNotBuiltUntilTheRouteIsTaken()
	{
		$built = 0;
		$table = $this->logoutTable($this->factory($built));

		$table->match(new Request('GET', 'news.1'));
		$match = $table->match(new Request('GET', 'logout'));
		self::assertSame(0, $built);

		self::assertInstanceOf(RequestHandlerInterface::class, $match->createHandler());
		self::assertSame(1, $built);
	}

	public function testEveryCallBuildsAFreshHandler()
	{
		$built = 0;
		$match = $this->logoutTable($this->factory($built))->match(new Request('GET', 'logout'));

		self::assertNotSame($match->createHandler(), $match->createHandler());
		self::assertSame(2, $built);
	}

	public function testTheFirstListedRouteThatAnswersWins()
	{
		$table = new RouteTable(array(
			$this->logoutRoute($this->factory()),
			Route::queryWord('user/signout', 'logout', $this->factory()),
		));

		self::assertSame('user/logout', $table->match(new Request('GET', 'logout'))->getName());
	}

	public function testAFactoryThatBuildsNoHandlerIsRefused()
	{
		$match = $this->logoutTable(function () { return new \stdClass(); })->match(new Request('GET', 'logout'));

		$this->expectException(\UnexpectedValueException::class);

		$match->createHandler();
	}

	public function testTwoRoutesMayNotShareAName()
	{
		$this->expectException(\InvalidArgumentException::class);

		new RouteTable(array(
			$this->logoutRoute($this->factory()),
			Route::queryWord('user/logout', 'signout', $this->factory()),
		));
	}

	public function testATableHoldsOnlyRoutes()
	{
		$this->expectException(\InvalidArgumentException::class);

		new RouteTable(array('logout'));
	}

	/**
	 * @param callable $factory
	 * @return RouteTable
	 */
	private function logoutTable(callable $factory)
	{
		return new RouteTable(array($this->logoutRoute($factory)));
	}

	/**
	 * @param callable $factory
	 * @return Route
	 */
	private function logoutRoute(callable $factory)
	{
		return Route::queryWord('user/logout', 'logout', $factory);
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

