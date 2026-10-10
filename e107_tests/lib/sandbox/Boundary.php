<?php

namespace Sandbox;

/**
 * The network boundary round a run: while it lasts, every connection that would leave the stack is refused and recorded, so each of e107's attempts to reach the Internet is accounted for.
 *
 * A recorder process of its own raises the fence once it listens, and lowers it when the run ends or its runner is gone.
 * Keep this class in PHP 5.6 syntax: the unit suite runs on 5.6.
 */
class Boundary
{
	/** Where the recorder listens: on loopback for what the fence redirects, and on the container's address for the browser, whose resolver sends every name but its site's here. */
	const PORT = 7107;

	/** What the recorder writes, one attempt a line. */
	const JOURNAL = 'attempts.jsonl';

	/** What each suite process writes, one test a line: when it started and the hosts it declared (Extension\OutboundLedger). */
	const TESTS = 'outbound-tests.jsonl';

	/** @var string */
	private $dir;

	/** @var Shell */
	private $shell;

	/** @var ProcNet */
	private $net;

	/** @var resource|null */
	private $recorder;

	/** @var bool whether the recorder has had the fence up */
	private $raised = false;

	/**
	 * @param string $dir where the recorder keeps its journal, log and rules: a directory of the run's own
	 * @param Shell $shell
	 * @param ProcNet $net
	 */
	public function __construct($dir, Shell $shell, ProcNet $net)
	{
		$this->dir = $dir;
		$this->shell = $shell;
		$this->net = $net;
	}

	/**
	 * Start the recorder, and wait until it has the fence up.
	 *
	 * @param string $command how to start this script's loops
	 * @return bool whether it did; if not, why not is on standard error
	 */
	public function raise($command)
	{
		@mkdir($this->dir, 0777, true);
		$log = $this->log();
		$this->recorder = Loop::start("$command --record ".escapeshellarg($this->dir), $log);
		for ($waited = 0; !file_exists("{$this->dir}/ready"); $waited++)
		{
			$status = proc_get_status($this->recorder);
			if (!$status['running'] || $waited > 3000)
			{
				$this->lower();
				fwrite(STDERR, "error: the network boundary could not be put up. A run sends every connection that would leave the stack to a recorder, which takes nftables and CAP_NET_ADMIN in the web container; an env from before them needs `e107-tests up` again.\n"
					.file_get_contents($log));

				return false;
			}
			usleep(10000);
		}
		$this->raised = true;

		return true;
	}

	/** Stop the recorder, and take the fence down if it was up, whether or not the recorder managed to. */
	public function lower()
	{
		if ($this->recorder !== null)
		{
			touch("{$this->dir}/stop");
			proc_close($this->recorder);
			$this->recorder = null;
		}
		if ($this->raised)
		{
			$this->fence()->lower();
			$this->raised = false;
		}
	}

	/**
	 * @param string $host the one name the browser resolves as usual, besides the browser's own localhost
	 * @return string Chrome's --host-resolver-rules: every other name goes to the recorder
	 */
	public static function browserRules($host)
	{
		return "MAP * $host:".self::PORT.", EXCLUDE $host, EXCLUDE localhost";
	}

	/** @return string where the recorder's own output goes */
	public function log()
	{
		return "{$this->dir}/recorder.log";
	}

	/** @return bool whether the recorder is still at work: one that stopped during the run left what came after it unrecorded */
	public function isRecording()
	{
		if ($this->recorder === null)
		{
			return false;
		}
		$status = proc_get_status($this->recorder);

		return $status['running'];
	}

	/**
	 * Hold what the recorder wrote against what the tests declared, in what each suite process wrote down (Extension\OutboundLedger).
	 *
	 * A test answers for what happens from its start until the next test on its worker starts, or the run ends: a page it left open in the browser can still make e107 reach out, and Apache finishes a request after the browser that sent it is gone.
	 *
	 * @param array<string,int> $workers the worker of each suite process, by its output directory
	 * @param string $root the project directory, which test names are given relative to
	 * @param bool $byTest whether to tell the attempts apart by the tests running at the time, which with one worker is the test that made them
	 * @return array[] the attempts no test running at the time declared, each once with its count and the tests running then (during)
	 */
	public function account(array $workers, $root, $byTest)
	{
		$tests = $this->tests($workers, rtrim($root, '/').'/');
		$undeclared = array();
		foreach ($this->attempts() as $attempt)
		{
			$during = array();
			$declared = false;
			foreach ($tests as $test)
			{
				if ($test['start'] <= $attempt['t'] && $attempt['t'] <= $test['end'])
				{
					$during[] = $test['name'];
					$declared = $declared || in_array($attempt['host'], $test['expect'], true);
				}
			}
			if ($declared)
			{
				continue;
			}
			$key = json_encode(array($attempt['from'], $attempt['protocol'], $attempt['host'], $attempt['port'], $attempt['request'], $byTest ? $during : null));
			if (!isset($undeclared[$key]))
			{
				$undeclared[$key] = $attempt + array('count' => 0, 'during' => array());
			}
			$undeclared[$key]['count']++;
			$undeclared[$key]['during'] = array_values(array_unique(array_merge($undeclared[$key]['during'], $during)));
		}

		return array_values($undeclared);
	}

	/**
	 * @param array<string,int> $workers
	 * @param string $root
	 * @return array[] every test that ran: name, start, end, and expect, the hosts it declared
	 */
	private function tests(array $workers, $root)
	{
		$byWorker = array();
		foreach ($workers as $dir => $worker)
		{
			foreach (@file("$dir/".self::TESTS, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: array() as $line)
			{
				$test = json_decode($line, true);
				$test['name'] = strpos($test['name'], $root) === 0 ? (string) substr($test['name'], strlen($root)) : $test['name'];
				$byWorker[$worker][] = $test;
			}
		}
		$tests = array();
		$now = microtime(true);
		foreach ($byWorker as $run)
		{
			usort($run, function ($a, $b)
			{
				return $a['start'] < $b['start'] ? -1 : 1;
			});
			foreach ($run as $i => $test)
			{
				$test['end'] = isset($run[$i + 1]) ? $run[$i + 1]['start'] : $now;
				$tests[] = $test;
			}
		}

		return $tests;
	}

	/** @return array[] every attempt the recorder wrote down this run */
	public function attempts()
	{
		$attempts = array();
		foreach (@file("{$this->dir}/".self::JOURNAL, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: array() as $line)
		{
			$attempts[] = json_decode($line, true);
		}

		return $attempts;
	}

	/**
	 * @param array $attempt one of {@see account()}'s undeclared
	 * @return string the attempt in a line
	 */
	public static function describe(array $attempt)
	{
		$where = $attempt['host'] === '' ? '(no name given)' : $attempt['host'];
		if ($attempt['port'] !== null && strpos($where, ':') !== false)
		{
			$where = "[$where]";
		}

		return $attempt['protocol'].' '.$where.($attempt['port'] !== null ? ':'.$attempt['port'] : '')
			.($attempt['request'] !== null ? ' '.$attempt['request'] : '')
			.' from '.($attempt['from'] === 'browser' ? 'the browser' : $attempt['from'])
			.($attempt['count'] > 1 ? ', '.$attempt['count'].' times' : '');
	}

	/**
	 * The recorder process: listen, raise the fence, record until the run ends or its runner is gone, and lower the fence.
	 *
	 * @return void
	 */
	public function record()
	{
		$runner = posix_getppid();
		$port = self::PORT;
		$tcp = @stream_socket_server("tcp://[::]:$port", $errno, $error) ?: stream_socket_server("tcp://0.0.0.0:$port", $errno, $error);
		$udp = array(stream_socket_server("udp://127.0.0.1:$port", $errno, $error, STREAM_SERVER_BIND));
		if ($this->net->hasIpv6())
		{
			$udp[] = stream_socket_server("udp://[::1]:$port", $errno, $error, STREAM_SERVER_BIND);
		}
		if ($tcp === false || in_array(false, $udp, true))
		{
			throw new \RuntimeException("the recorder cannot listen on port $port: $error");
		}
		$fence = $this->fence();
		$fence->raise($port);
		try
		{
			touch("{$this->dir}/ready");
			$dir = $this->dir;
			$recorder = new Recorder($this->net, "$dir/".self::JOURNAL);
			$recorder->serve($tcp, $udp, function () use ($dir, $runner)
			{
				return Loop::ended($dir, $runner);
			});
		}
		finally
		{
			$fence->lower();
		}
	}

	/** @return Fence */
	private function fence()
	{
		return new Fence($this->shell, $this->net, '/etc/resolv.conf', "{$this->dir}/rules.nft");
	}
}
