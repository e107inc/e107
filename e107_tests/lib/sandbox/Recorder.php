<?php

namespace Sandbox;

/**
 * Takes the connections the fence sends it, writes down where each was going, and fails it the way its protocol fails fastest:
 * HTTP gets a 503, TLS a handshake_failure alert, SMTP a 554 greeting, a name lookup REFUSED, and anything else a closed connection.
 *
 * Keep this class in PHP 5.6 syntax: the unit suite runs on 5.6.
 */
class Recorder
{
	/** Seconds a client that has said nothing keeps its connection: one waiting for the server to speak first hears nothing. */
	const QUIET = 1.0;

	/** Seconds a refused client has to stop sending before its connection is closed under it. */
	const LINGER = 1.0;

	/** Bytes a connection may send before it is taken for whatever it has shown itself to be. */
	const ENOUGH = 65536;

	/** Ports whose server speaks first, with a reply code the client acts on: SMTP and submission. */
	const SMTP_PORTS = array(25, 587, 2525);

	const REFUSAL = 'Refused by the e107 test harness, whose suites do not reach the Internet: see "The network boundary" in e107_tests/docker/README.md';

	/** @var ProcNet */
	private $net;

	/** @var string */
	private $journal;

	/** @var array[] the open connections by socket id: socket, buffer, deadline, attempt, and whether it has been refused */
	private $open = array();

	/**
	 * @param ProcNet $net
	 * @param string $journal where each attempt is appended, one JSON object a line
	 */
	public function __construct(ProcNet $net, $journal)
	{
		$this->net = $net;
		$this->journal = $journal;
	}

	/**
	 * @param resource[] $tcp the listening sockets
	 * @param resource[] $udp the bound datagram sockets
	 * @param callable $ended tells the loop when to stop
	 * @return void
	 */
	public function serve(array $tcp, array $udp, $ended)
	{
		while (!call_user_func($ended))
		{
			$read = array_merge($tcp, $udp);
			foreach ($this->open as $connection)
			{
				$read[] = $connection['socket'];
			}
			$write = null;
			$except = null;
			if (@stream_select($read, $write, $except, 0, 200000) > 0)
			{
				foreach ($read as $socket)
				{
					if (in_array($socket, $tcp, true))
					{
						$this->accept($socket);
					}
					elseif (in_array($socket, $udp, true))
					{
						$this->datagram($socket);
					}
					else
					{
						$this->receive((int) $socket);
					}
				}
			}
			$this->expire();
		}
	}

	private function accept($tcp)
	{
		$socket = @stream_socket_accept($tcp, 0, $peer);
		if ($socket === false)
		{
			return;
		}
		stream_set_blocking($socket, false);
		$id = (int) $socket;
		$this->open[$id] = array('socket' => $socket, 'buffer' => '', 'deadline' => microtime(true) + self::QUIET, 'refused' => false,
			'attempt' => $this->attempt('tcp', $peer));
		if (in_array($this->open[$id]['attempt']['port'], self::SMTP_PORTS, true))
		{
			$this->refuse($id, 'smtp', null, null, '554 5.7.1 '.self::REFUSAL."\r\n");
		}
	}

	private function receive($id)
	{
		$data = fread($this->open[$id]['socket'], 8192);
		if ($data === false || ($data === '' && feof($this->open[$id]['socket'])))
		{
			$this->settle($id);

			return;
		}
		if ($data === '' || $this->open[$id]['refused'])
		{
			return;
		}
		$this->open[$id]['buffer'] .= $data;
		$this->classify($id);
	}

	/** Refuse a connection as soon as it shows what it is: a TLS ClientHello, an HTTP request line and headers, or anything else. */
	private function classify($id)
	{
		$buffer = $this->open[$id]['buffer'];
		if ($buffer[0] === "\x16")
		{
			$length = strlen($buffer) >= 5 ? 5 + self::u16($buffer, 3) : self::ENOUGH;
			if (strlen($buffer) >= min($length, self::ENOUGH))
			{
				$this->refuse($id, 'tls', self::serverName((string) substr($buffer, 5)), null, "\x15\x03\x01\x00\x02\x02\x28");
			}

			return;
		}
		$full = strlen($buffer) >= self::ENOUGH;
		if (preg_match('/^([A-Z]+) (\S+) HTTP\/\d/', $buffer, $line))
		{
			$headers = strpos($buffer, "\r\n\r\n");
			if ($headers !== false || $full)
			{
				$host = preg_match('/\r\nHost:[ \t]*(\[[^\]]*\]|[^:\r\n]*)/i', (string) substr($buffer, 0, $headers ?: self::ENOUGH), $match) ? trim($match[1], '[]') : null;
				$this->refuse($id, 'http', $host, "$line[1] $line[2]", "HTTP/1.1 503 Service Unavailable\r\nContent-Type: text/plain\r\nContent-Length: "
					.strlen(self::REFUSAL)."\r\nConnection: close\r\n\r\n".self::REFUSAL);
			}
		}
		elseif ($full || !preg_match('/^[A-Z]+(?: \S*(?: (?:H(?:T(?:T(?:P\/?)?)?)?)?)?)?$/', $buffer))
		{
			$this->settle($id);
		}
	}

	/**
	 * @param int $id
	 * @param string $protocol
	 * @param string|null $host the name the client asked for, when its protocol says
	 * @param string|null $request what it asked for
	 * @param string $reply
	 * @return void
	 */
	private function refuse($id, $protocol, $host, $request, $reply)
	{
		$connection = &$this->open[$id];
		$connection['attempt']['protocol'] = $protocol;
		if ($host !== null && $host !== '')
		{
			$connection['attempt']['host'] = strtolower($host);
		}
		$connection['attempt']['request'] = $request;
		$this->record($connection['attempt']);
		@fwrite($connection['socket'], $reply);
		@stream_socket_shutdown($connection['socket'], STREAM_SHUT_WR);
		$connection['refused'] = true;
		$connection['deadline'] = microtime(true) + self::LINGER;
	}

	/** Close what has run out of time: a quiet client, and a refused one that has had its chance to finish sending. */
	private function expire()
	{
		$now = microtime(true);
		foreach ($this->open as $id => $connection)
		{
			if ($connection['deadline'] <= $now)
			{
				$this->settle($id);
			}
		}
	}

	/** Close a connection, recording it first if it ends unrefused. */
	private function settle($id)
	{
		$connection = $this->open[$id];
		if (!$connection['refused'])
		{
			$this->record($connection['attempt']);
		}
		$this->close($id);
	}

	private function close($id)
	{
		fclose($this->open[$id]['socket']);
		unset($this->open[$id]);
	}

	private function datagram($udp)
	{
		$data = stream_socket_recvfrom($udp, 65535, 0, $peer);
		if ($data === false || $data === '')
		{
			return;
		}
		$attempt = $this->attempt('udp', $peer);
		$question = self::question($data);
		if ($question !== null)
		{
			$attempt = array('protocol' => 'dns', 'host' => strtolower($question[0]), 'request' => $attempt['host'] === '' ? null : 'to '.$attempt['host']) + $attempt;
			stream_socket_sendto($udp, self::refused($data, $question[1]), 0, preg_match('/^[^\[].*:.*:/', $peer) ? '['.substr($peer, 0, strrpos($peer, ':')).']'.strrchr($peer, ':') : $peer);
		}
		$this->record($attempt);
	}

	/**
	 * @param string $protocol tcp or udp
	 * @param string $peer the client's address
	 * @return array where the client was going, as far as its socket still says
	 */
	private function attempt($protocol, $peer)
	{
		$remote = $this->net->remoteOf($protocol, $peer);
		$owner = $remote === null ? false : posix_getpwuid($remote[2]);

		return array(
			't'        => microtime(true),
			'from'     => $owner ? $owner['name'] : ($remote === null ? 'this container' : 'uid '.$remote[2]),
			'protocol' => $protocol,
			'host'     => $remote === null ? '' : (string) $remote[0],
			'port'     => $remote === null ? null : $remote[1],
			'request'  => null,
		);
	}

	/** Bytes a client sent that are not printable ASCII are written down as \xNN, so whatever it sent can be journaled. */
	private function record(array $attempt)
	{
		foreach ($attempt as $field => $value)
		{
			if (is_string($value))
			{
				$attempt[$field] = preg_replace_callback('/[^\x20-\x7e]/', function ($byte)
				{
					return sprintf('\\x%02x', ord($byte[0]));
				}, $value);
			}
		}
		file_put_contents($this->journal, json_encode($attempt, JSON_UNESCAPED_SLASHES)."\n", FILE_APPEND | LOCK_EX);
	}

	/**
	 * @param string $handshake a TLS handshake message
	 * @return string|null the server name a ClientHello asks for
	 */
	private static function serverName($handshake)
	{
		if (strlen($handshake) < 39 || $handshake[0] !== "\x01")
		{
			return null;
		}
		$at = 38;
		$at += 1 + ord($handshake[$at]);
		$at += 2 + self::u16($handshake, $at);
		$at += 1 + (isset($handshake[$at]) ? ord($handshake[$at]) : 0);
		$end = min(strlen($handshake), $at + 2 + self::u16($handshake, $at));
		for ($at += 2; $at + 4 <= $end; $at += 4 + self::u16($handshake, $at + 2))
		{
			if (self::u16($handshake, $at) === 0)
			{
				return (string) substr($handshake, $at + 9, self::u16($handshake, $at + 7));
			}
		}

		return null;
	}

	/**
	 * @param string $packet
	 * @return array{0: string, 1: int}|null the name a DNS query asks about, and where its question ends
	 */
	private static function question($packet)
	{
		if (strlen($packet) < 17 || (ord($packet[2]) & 0x80) || self::u16($packet, 4) < 1)
		{
			return null;
		}
		$labels = array();
		for ($at = 12; $at < strlen($packet) && ($length = ord($packet[$at])) > 0; $at += 1 + $length)
		{
			$labels[] = (string) substr($packet, $at + 1, $length);
		}

		return $at + 5 <= strlen($packet) ? array(implode('.', $labels), $at + 5) : null;
	}

	/** The query back with its question alone, marked as an answer that refuses it. */
	private static function refused($query, $end)
	{
		return substr($query, 0, 2).chr(ord($query[2]) | 0x80).chr(0x80 | 5)."\x00\x01\x00\x00\x00\x00\x00\x00".substr($query, 12, $end - 12);
	}

	private static function u16($bytes, $at)
	{
		return $at + 2 <= strlen($bytes) ? (ord($bytes[$at]) << 8) | ord($bytes[$at + 1]) : 0;
	}
}
