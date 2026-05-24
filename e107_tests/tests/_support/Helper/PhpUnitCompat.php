<?php
namespace Helper;

/**
 * Backport of PHPUnit 8.x / 9.x assertion names for the legacy PHP cells.
 *
 * The matrix straddles two PHPUnit generations:
 *
 *   - Codeception 5.x on PHP 8.1+ ships PHPUnit 10 or 11, which dropped
 *     assertContains(string,string), assertRegExp(), assertFileNotExists,
 *     and friends in favour of the explicit assertStringContainsString,
 *     assertMatchesRegularExpression, assertFileDoesNotExist names that
 *     landed in PHPUnit 8.x and 9.x.
 *
 *   - Codeception 4.x on PHP 5.6 / 7.0 ships PHPUnit 5.x or 6.x, which
 *     never gained the new names: only the old ones exist.
 *
 * Rather than rewrite ~180 assertion call sites across the unit suite
 * (or pin a specific PHPUnit minor), bridge the gap with a __call shim:
 * if the test class extends a PHPUnit that lacks the requested new name,
 * forward to the historical equivalent. On modern PHPUnit the method
 * exists on the parent so __call is never invoked.
 *
 * Tests opt in by adding `use \Helper\PhpUnitCompat;` inside the class
 * body. The trait is intentionally additive: classes that don't use any
 * of the bridged methods don't need to change.
 */
trait PhpUnitCompat
{
    /**
     * Map of PHPUnit-9-era assertion names to their historical equivalents.
     * Names not listed here are still routed through the parent class.
     */
    private static $phpUnitCompatForwardMap = [
        'assertStringContainsString'    => 'assertContains',
        'assertStringNotContainsString' => 'assertNotContains',
        'assertMatchesRegularExpression' => 'assertRegExp',
        'assertDoesNotMatchRegularExpression' => 'assertNotRegExp',
        'assertFileDoesNotExist'        => 'assertFileNotExists',
        'assertDirectoryDoesNotExist'   => 'assertDirectoryNotExists',
        'expectExceptionMessageMatches' => 'expectExceptionMessageRegExp',
    ];

    public function __call($name, $arguments)
    {
        if (isset(self::$phpUnitCompatForwardMap[$name])) {
            $forward = self::$phpUnitCompatForwardMap[$name];
            if (method_exists($this, $forward)) {
                return call_user_func_array([$this, $forward], $arguments);
            }
        }
        throw new \BadMethodCallException(sprintf(
            'Method %s::%s does not exist.',
            get_class($this),
            $name
        ));
    }
}
