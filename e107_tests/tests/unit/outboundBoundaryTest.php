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
class outboundBoundaryTest extends \Codeception\Test\Unit
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

	public function testADeclaredNameCoversAnAttemptThatCarriedIt()
	{
		self::assertSame(array(), $this->undeclared(array(array('protocol' => 'dns', 'host' => 'e107.org'))));
	}

	public function testANameStillHasToBeTheOneDeclared()
	{
		self::assertCount(1, $this->undeclared(array(array('protocol' => 'tls', 'host' => 'example.org'))));
	}

	public function testAConnectionThatNamedNothingIsNotCoveredByADeclaredName()
	{
		self::assertCount(1, $this->undeclared(array(array('protocol' => 'tcp', 'host' => '203.0.113.7'))));
	}

	/**
	 * @param array[] $attempts what the recorder wrote down, during a test that declared e107.org
	 * @return array[] the attempts account() reports undeclared
	 */
	private function undeclared(array $attempts)
	{
		$journal = '';
		foreach ($attempts as $attempt)
		{
			$journal .= json_encode($attempt + array('t' => 100.5, 'from' => 'www-data', 'port' => 443, 'request' => null))."\n";
		}
		file_put_contents($this->dir.'/'.\Sandbox\Boundary::JOURNAL, $journal);
		file_put_contents($this->dir.'/worker/'.\Sandbox\Boundary::TESTS,
			json_encode(array('name' => '/srv/tests/unit/aTest.php:testIt', 'start' => 100.0, 'expect' => array('e107.org')))."\n");

		$boundary = new \Sandbox\Boundary($this->dir, new \Sandbox\Shell(), new \Sandbox\ProcNet());

		return $boundary->account(array($this->dir.'/worker' => 1), '/srv', true);
	}
}
