<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Shims;

use e107\SessionHandlers\SessionId;

class SessionCreateIdTest extends \Test\Unit
{
	public function testSessionCreateIdDefault()
	{
		$this->assertMakesANewIdEachTime(array(InternalShims::class, 'session_create_id'));
	}

	public function testSessionCreateIdDefaultLegacy()
	{
		$this->assertMakesANewIdEachTime(array(\eShims::class, 'session_create_id'));
	}

	public function testSessionCreateIdAlt()
	{
		$this->assertMakesANewIdEachTime(array(InternalShims::class, 'session_create_id_alt'));
	}

	public function testSessionCreateIdAltMakesTheIdOfPhpsDefaultSettings()
	{
		$id = InternalShims::session_create_id_alt();

		$this->assertSame(1, preg_match('/^[0-9a-f]{32}$/', $id), $id);
	}

	public function testSessionCreateIdDefaultPutsAPrefixInFront()
	{
		$this->assertPutsAPrefixInFront(array(InternalShims::class, 'session_create_id'));
	}

	public function testSessionCreateIdAltPutsAPrefixInFront()
	{
		$this->assertPutsAPrefixInFront(array(InternalShims::class, 'session_create_id_alt'));
	}

	public function testSessionCreateIdDefaultRefusesAPrefixPhpWouldRefuse()
	{
		$this->assertRefusesAPrefixPhpWouldRefuse(array(InternalShims::class, 'session_create_id'));
	}

	public function testSessionCreateIdAltRefusesAPrefixPhpWouldRefuse()
	{
		$this->assertRefusesAPrefixPhpWouldRefuse(array(InternalShims::class, 'session_create_id_alt'));
	}

	public function testSessionCreateIdAltRefusesAPrefixLongerThanPhpTakes()
	{
		$this->assertFalse(@InternalShims::session_create_id_alt(str_repeat('a', 257)));
	}

	/**
	 * @param callable $implementation
	 */
	private function assertPutsAPrefixInFront($implementation)
	{
		$prefix = 'e107,-'.str_repeat('a', 250);
		$id = call_user_func($implementation, $prefix);

		$this->assertSame(0, strpos($id, $prefix), var_export($id, true));
		$this->assertTrue(SessionId::isWellFormed($id), $id);
		$this->assertGreaterThan(strlen($prefix), strlen($id));
	}

	/**
	 * @param callable $implementation
	 */
	private function assertRefusesAPrefixPhpWouldRefuse($implementation)
	{
		$this->assertFalse(@call_user_func($implementation, 'e107/'));
	}

	/**
	 * @param callable $implementation
	 */
	private function assertMakesANewIdEachTime($implementation)
	{
		$first = call_user_func($implementation);

		$this->assertTrue(SessionId::isWellFormed($first), var_export($first, true));
		$this->assertNotSame($first, call_user_func($implementation));
	}
}
