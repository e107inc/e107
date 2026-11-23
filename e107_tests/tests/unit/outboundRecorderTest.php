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
 * How {@see \Sandbox\Recorder} answers the name lookups the network boundary's fence sends it.
 */
class outboundRecorderTest extends \Test\Unit
{
	/** @var string */
	private $journal;

	protected function _before()
	{
		foreach (array('ProcNet', 'Recorder') as $class)
		{
			include_once codecept_root_dir().'lib/sandbox/'.$class.'.php';
		}

		$this->journal = tempnam(sys_get_temp_dir(), 'outboundRecorderTest');
	}

	protected function _after()
	{
		@unlink($this->journal);
	}

	public function testAStackNameIsAnsweredWithItsAddressesOfTheTypeAsked()
	{
		self::assertSame(array(0, array('10.89.0.2')), $this->lookUp('DB', 1));
		self::assertSame(array(0, array('fd00::2')), $this->lookUp('db', 28));
		self::assertSame('', file_get_contents($this->journal));
	}

	public function testAStackNameWithNoAddressOfTheTypeAskedIsAnsweredWithNone()
	{
		self::assertSame(array(0, array()), $this->lookUp('sb1.web', 28));
	}

	public function testTheRootIsAnsweredWithNoAddress()
	{
		self::assertSame(array(0, array()), $this->lookUp('', 1));
		self::assertSame('', file_get_contents($this->journal));
	}

	public function testAnyOtherNameIsRefusedAndWrittenDown()
	{
		self::assertSame(array(5, array()), $this->lookUp('e107.org', 1));

		$attempt = json_decode(file_get_contents($this->journal), true);
		self::assertSame(array('dns', 'e107.org'), array($attempt['protocol'], $attempt['host']));
	}

	/**
	 * @param string $name
	 * @param int $type 1 for A, 28 for AAAA
	 * @return array{0: int, 1: string[]} the answer's response code and the addresses it carries
	 */
	private function lookUp($name, $type)
	{
		$udp = stream_socket_server('udp://127.0.0.1:0', $errno, $error, STREAM_SERVER_BIND);
		$tcp = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
		$client = stream_socket_client('udp://'.stream_socket_get_name($udp, false));
		$question = '';
		foreach (array_filter(explode('.', $name), 'strlen') as $label)
		{
			$question .= chr(strlen($label)).$label;
		}
		$question .= "\0".pack('nn', $type, 1);
		fwrite($client, pack('nnnnnn', 0x1234, 0x0100, 1, 0, 0, 0).$question);

		$recorder = new \Sandbox\Recorder(new \Sandbox\ProcNet(), $this->journal, array('db' => array('10.89.0.2', 'fd00::2'), 'sb1.web' => array('10.89.0.3')));
		$served = false;
		$recorder->serve($tcp, array($udp), function () use (&$served)
		{
			$ended = $served;
			$served = true;

			return $ended;
		});

		stream_set_timeout($client, 2);
		$answer = (string) fread($client, 512);
		self::assertSame(pack('n', 0x1234).$question, substr($answer, 0, 2).substr($answer, 12, strlen($question)), 'The answer carries the query back.');
		$addresses = array();
		for ($at = 12 + strlen($question); $at + 12 <= strlen($answer); $at += 12 + $length)
		{
			$length = unpack('n', (string) substr($answer, $at + 10, 2))[1];
			$addresses[] = inet_ntop((string) substr($answer, $at + 12, $length));
		}

		return array(ord($answer[3]) & 15, $addresses);
	}
}
