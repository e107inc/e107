<?php
namespace Helper;

/**
 * Backport of PHPUnit 8.x / 9.x assertion names for the legacy PHP cells.
 *
 * The matrix straddles two PHPUnit generations:
 *
 *   - Codeception 5.x on PHP 8.1+ ships PHPUnit 10 or newer, which dropped
 *     assertContains(string,string), assertRegExp(), assertInternalType(),
 *     assertFileNotExists and friends in favour of the explicit
 *     assertStringContainsString, assertMatchesRegularExpression,
 *     assertIsArray, assertFileDoesNotExist names that landed in PHPUnit
 *     7.5, 8.x and 9.x.
 *
 *   - Codeception 4.x on PHP 5.6 / 7.0 ships PHPUnit 5.7 or 6.x, which
 *     never gained the new names: only the old ones exist.
 *
 * Rather than rewrite the assertion call sites across the unit suite (or
 * pin a specific PHPUnit minor), bridge the gap with a magic-method shim:
 * if the test class extends a PHPUnit that lacks the requested new name,
 * forward to the historical equivalent. On modern PHPUnit the method is
 * defined on the parent, so neither __call nor __callStatic is ever
 * reached and the trait is a pure no-op.
 *
 * Both magic entry points are implemented: __call covers $this->assertX()
 * and __callStatic covers self::assertX() / static::assertX(), which the
 * suite also uses (PHPUnit's assertions are static, so the static form is
 * legal and bypasses __call entirely).
 *
 * Tests do not name this trait themselves. \Test\Unit carries it, every
 * unit test extends \Test\Unit, and unitTestConventionsTest fails the
 * suite if one stops doing so. The trait is intentionally additive:
 * classes that don't use any of the bridged methods pay nothing for it.
 *
 * Everything here is written in PHP 5.6 syntax on purpose (array(), no
 * return types, no trait constants) so the Rector downgrade pipeline has
 * nothing left to rewrite and its idempotency gate stays green.
 */
trait PhpUnitCompat
{
	/**
	 * Map of PHPUnit-8/9-era assertion names to their historical
	 * equivalents, for the cases where the argument list is unchanged.
	 * Names not listed here are still routed through the parent class.
	 *
	 * @var array
	 */
	private static $phpUnitCompatForwardMap = array(
		'assertStringContainsString'          => 'assertContains',
		'assertStringNotContainsString'       => 'assertNotContains',
		'assertMatchesRegularExpression'      => 'assertRegExp',
		'assertDoesNotMatchRegularExpression' => 'assertNotRegExp',
		'assertFileDoesNotExist'              => 'assertFileNotExists',
		'assertDirectoryDoesNotExist'         => 'assertDirectoryNotExists',
		'expectExceptionMessageMatches'       => 'expectExceptionMessageRegExp',
	);

	/**
	 * PHPUnit 8 turned the ignore-case flag of assertContains() into
	 * separate ...IgnoringCase methods. On PHPUnit 5.7 / 6.x the flag is
	 * still the FOURTH POSITIONAL argument of assertContains():
	 *
	 *   assertContains($needle, $haystack, $message = '',
	 *                  $ignoreCase = false, $checkForObjectIdentity = true,
	 *                  $checkForNonObjectIdentity = false)
	 *
	 * so a plain name-to-name entry in the map above would silently drop
	 * the case-insensitivity and turn the assertion into its case-sensitive
	 * cousin. These two are forwarded with the flag rebuilt instead.
	 *
	 * @var array
	 */
	private static $phpUnitCompatIgnoringCaseMap = array(
		'assertStringContainsStringIgnoringCase'    => 'assertContains',
		'assertStringNotContainsStringIgnoringCase' => 'assertNotContains',
	);

	/**
	 * Suffix of the PHPUnit 7.5 assertIs*() / assertIsNot*() family mapped
	 * to the type string assertInternalType() / assertNotInternalType()
	 * understands. Every value here is in PHPUnit 5.7's IsType::KNOWN_TYPES,
	 * which is what the PHP 5.6 cell resolves to.
	 *
	 * 'Iterable' is deliberately absent: assertIsIterable() exists in
	 * PHPUnit 7.5+, but 'iterable' is not a known type on PHPUnit 5.7, so
	 * there is no correct forward for it. The suite does not use it; if it
	 * ever does, the shim reports a BadMethodCallException rather than
	 * inventing a broken translation.
	 *
	 * @var array
	 */
	private static $phpUnitCompatInternalTypeMap = array(
		'array'    => 'array',
		'bool'     => 'bool',
		'callable' => 'callable',
		'float'    => 'float',
		'int'      => 'int',
		'numeric'  => 'numeric',
		'object'   => 'object',
		'resource' => 'resource',
		'scalar'   => 'scalar',
		'string'   => 'string',
	);

	public function __call($name, $arguments)
	{
		$forward = self::phpUnitCompatResolve(get_class($this), $name, $arguments);
		if ($forward !== null)
		{
			return call_user_func_array(array($this, $forward[0]), $forward[1]);
		}

		throw new \BadMethodCallException(sprintf(
			'Method %s::%s does not exist.',
			get_class($this),
			$name
		));
	}

	public static function __callStatic($name, $arguments)
	{
		$class = get_called_class();

		$forward = self::phpUnitCompatResolve($class, $name, $arguments);
		if ($forward !== null)
		{
			return forward_static_call_array(array($class, $forward[0]), $forward[1]);
		}

		throw new \BadMethodCallException(sprintf(
			'Method %s::%s does not exist.',
			$class,
			$name
		));
	}

	/**
	 * Work out which historical method a missing modern name should reach,
	 * and with which arguments.
	 *
	 * @param string $class     the class the call was made against
	 * @param string $name      the missing method name
	 * @param array  $arguments the arguments it was called with
	 * @return array|null array($targetMethod, $targetArguments), or null
	 *                    when there is nothing sensible to forward to
	 */
	private static function phpUnitCompatResolve($class, $name, $arguments)
	{
		if (isset(self::$phpUnitCompatIgnoringCaseMap[$name]))
		{
			$target = self::$phpUnitCompatIgnoringCaseMap[$name];
			if (!method_exists($class, $target))
			{
				return null;
			}

			// ($needle, $haystack, $message = '') becomes
			// ($needle, $haystack, $message, $ignoreCase = true).
			$forwarded = array_slice($arguments, 0, 2);
			$forwarded[] = isset($arguments[2]) ? $arguments[2] : '';
			$forwarded[] = true;

			return array($target, $forwarded);
		}

		if (isset(self::$phpUnitCompatForwardMap[$name]))
		{
			$target = self::$phpUnitCompatForwardMap[$name];

			return method_exists($class, $target) ? array($target, $arguments) : null;
		}

		$matches = array();
		if (preg_match('/^assertIs(Not)?([A-Z]\w+)$/', $name, $matches))
		{
			$type = strtolower($matches[2]);
			if (!isset(self::$phpUnitCompatInternalTypeMap[$type]))
			{
				return null;
			}

			$target = $matches[1] === 'Not' ? 'assertNotInternalType' : 'assertInternalType';
			if (!method_exists($class, $target))
			{
				return null;
			}

			array_unshift($arguments, self::$phpUnitCompatInternalTypeMap[$type]);

			return array($target, $arguments);
		}

		return null;
	}
}
