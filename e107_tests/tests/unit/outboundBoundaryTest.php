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
 * How {@see \Sandbox\Boundary::account()} holds what the network boundary's recorder wrote against what each test declared.
 */
class outboundBoundaryTest extends \Test\Unit
{
	/** @var string */
	private $dir;

	protected function _before()
	{
		foreach (array('Shell', 'ProcNet', 'Boundary') as $class)
		{
			include_once codecept_root_dir().'lib/sandbox/'.$class.'.php';
		}

		$this->dir = sys_get_temp_dir().'/outboundBoundaryTest-'.getmypid();
		@mkdir($this->dir.'/worker', 0777, true);
	}

	protected function _after()
	{
		@unlink($this->dir.'/'.\Sandbox\Boundary::JOURNAL);
		@unlink($this->dir.'/worker/'.\Sandbox\Boundary::TESTS);
		@rmdir($this->dir.'/worker');
		@rmdir($this->dir);
	}

	/**
	 * A client that is slow to say where it is going, or gives up first, leaves the recorder only the address it went to.
	 */
	public function testADeclaredNameCoversAConnectionThatNamedNothingAndWentToItsAddress()
	{
		self::assertSame(array(), $this->undeclared(array('protocol' => 'tcp', 'host' => '203.0.113.7')));
	}

	public function testANameStillHasToBeTheOneDeclared()
	{
		self::assertCount(1, $this->undeclared(array('protocol' => 'tls', 'host' => 'example.org')));
	}

	public function testAnAddressTheDeclaredNameDoesNotResolveToIsStillUndeclared()
	{
		self::assertCount(1, $this->undeclared(array('protocol' => 'tcp', 'host' => '198.51.100.9')));
	}

	public function testAnAttemptWhoseProtocolCarriesANameIsNotCoveredByItsAddress()
	{
		self::assertCount(1, $this->undeclared(array('protocol' => 'tls', 'host' => '203.0.113.7')));
	}

	/**
	 * The recorder writes a datagram down as it arrives, so one that is not a name lookup never had a name to lose.
	 */
	public function testADatagramToTheDeclaredNamesAddressIsStillUndeclared()
	{
		self::assertCount(1, $this->undeclared(array('protocol' => 'udp', 'host' => '203.0.113.7')));
	}

	/**
	 * @param array $attempt what the recorder wrote down, during a test that declared e107.org, which resolves to 203.0.113.7 here
	 * @return array[] the attempts account() reports undeclared
	 */
	private function undeclared(array $attempt)
	{
		file_put_contents($this->dir.'/'.\Sandbox\Boundary::JOURNAL,
			json_encode($attempt + array('t' => 100.5, 'from' => 'www-data', 'port' => 443, 'request' => null))."\n");
		file_put_contents($this->dir.'/worker/'.\Sandbox\Boundary::TESTS,
			json_encode(array('name' => '/srv/tests/unit/aTest.php:testIt', 'start' => 100.0, 'expect' => array('e107.org')))."\n");

		$boundary = new \Sandbox\Boundary($this->dir, new \Sandbox\Shell(), new \Sandbox\ProcNet(), function ($name)
		{
			return $name === 'e107.org' ? array('203.0.113.7') : array();
		});

		return $boundary->account(array($this->dir.'/worker' => 1), '/srv', true);
	}
}
