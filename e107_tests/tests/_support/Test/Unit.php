<?php

namespace Test;

/**
 * The base class every unit test in this suite extends.
 *
 * It exists so that cross-cell compatibility is inherited rather than opted
 * into. The matrix runs the same suite against two PHPUnit generations --
 * Codeception 5.x with PHPUnit 10+ on PHP 8.1 and later, Codeception 4.x with
 * PHPUnit 5.7 / 6.x on the PHP 5.6 and 7.0 cells -- and the assertion names
 * those generations agree on do not cover the whole suite. \Helper\PhpUnitCompat
 * bridges the gap, but a trait has to be named in every class that wants it,
 * and a test author has no way to notice the omission until a legacy cell
 * fails on a machine they are not looking at.
 *
 * Extending this class instead means a new test written the same way as its
 * neighbours is correct by default. unitTestConventionsTest fails the suite
 * if a test extends \Codeception\Test\Unit directly, so the omission is
 * reported on the first run rather than on the first legacy cell.
 *
 * Keep this class in PHP 5.6 syntax: it is part of the shipping-adjacent test
 * tree that the downgrade pipeline walks, and anything modern here would just
 * be rewritten by the next rector-downgrade run.
 */
class Unit extends \Codeception\Test\Unit
{
	use \Helper\PhpUnitCompat;
}
