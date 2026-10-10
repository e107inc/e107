<?php

namespace Sandbox;

/**
 * What the kernel's /proc/net tables say about this container's network: the networks it reaches without a gateway, and where its own sockets are connected.
 *
 * Keep this class in PHP 5.6 syntax: the unit suite runs on 5.6.
 */
class ProcNet
{
	/** The state of a connected socket, as the tables print it. */
	const ESTABLISHED = '01';

	/** States whose socket is no client's: one closed and waiting out its last packets, which may hold the same address, and a listener. */
	const PAST = array('06', '0A');

	/** @var string */
	private $root;

	/** @param string $root where the tables are, /proc/net unless a test says otherwise */
	public function __construct($root = '/proc/net')
	{
		$this->root = $root;
	}

	/**
	 * @param string $family ip or ip6
	 * @return string[] the destinations a connection reaches without leaving the stack, as address/prefix: loopback, and the networks an interface other than loopback reaches directly
	 */
	public function inside($family)
	{
		return $family === 'ip' ? array_merge(array('127.0.0.0/8'), $this->localNetworks4()) : array_merge(array('::1/128'), $this->localNetworks6());
	}

	private function localNetworks4()
	{
		$networks = array();
		foreach ($this->rows('route', 1) as $row)
		{
			if ($row[0] !== 'lo' && $row[2] === '00000000' && $row[1] !== '00000000')
			{
				$networks[] = self::address($row[1]).'/'.substr_count(decbin(hexdec($row[7])), '1');
			}
		}

		return $networks;
	}

	/** Multicast aside. */
	private function localNetworks6()
	{
		$networks = array();
		foreach ($this->rows('ipv6_route', 0) as $row)
		{
			$prefix = hexdec($row[1]);
			if ($row[9] !== 'lo' && $prefix > 0 && trim($row[4], '0') === '' && strpos($row[0], 'ff') !== 0)
			{
				$networks[] = inet_ntop(hex2bin($row[0])).'/'.$prefix;
			}
		}

		return $networks;
	}

	/** @return bool whether the container has IPv6 at all */
	public function hasIpv6()
	{
		return count($this->rows('if_inet6', 0)) > 0;
	}

	/**
	 * Where a socket of this container is connected to: for one the boundary redirected, the address it was really after.
	 *
	 * The same local address may also carry a connection inside the stack, to another destination, which is not the one redirected; a client that has already closed is still found, until it is waiting out its last packets. An IPv6 socket that reaches an IPv4 address sends IPv4, so the fence sees that address, while the socket stays in the IPv6 table under the mapped form of its own.
	 *
	 * @param string $protocol tcp or udp
	 * @param string $address the socket's own address, as a peer sees it ("1.2.3.4:5", "[::1]:5", or "::1:5" on older PHP versions)
	 * @return array{0: string|null, 1: int|null, 2: int}|null the remote address and port, null for a datagram socket that names none, and the socket owner's uid; null when no socket here has that address
	 */
	public function remoteOf($protocol, $address)
	{
		$colon = strrpos($address, ':');
		$ip = trim((string) substr($address, 0, $colon), '[]');
		$port = sprintf('%04X', (int) substr($address, $colon + 1));
		$inside = array('ip' => $this->inside('ip'), 'ip6' => $this->inside('ip6'));
		$found = null;
		$unconnected = null;
		foreach (strpos($ip, ':') === false ? array($protocol => $ip, $protocol.'6' => "::ffff:$ip") : array($protocol.'6' => $ip) as $table => $own)
		{
			$packed = inet_pton($own);
			$local = self::hex($packed).':'.$port;
			$anywhere = self::hex(str_repeat("\0", strlen($packed))).':'.$port;
			foreach ($this->rows($table, 1) as $row)
			{
				list($remote, $remotePort) = explode(':', $row[2]);
				$remote = self::unmapped(self::address($remote));
				if ($row[1] === $local && hexdec($remotePort) !== 0 && !in_array($row[3], self::PAST, true) && !self::within($remote, $inside[strpos($remote, ':') === false ? 'ip' : 'ip6']))
				{
					$candidate = array($remote, hexdec($remotePort), (int) $row[7]);
					if ($row[3] === self::ESTABLISHED)
					{
						return $candidate;
					}
					$found = $found ?: $candidate;
				}
				elseif ($protocol === 'udp' && ($row[1] === $local || $row[1] === $anywhere) && hexdec($remotePort) === 0)
				{
					$unconnected = $unconnected ?: array(null, null, (int) $row[7]);
				}
			}
		}

		return $found ?: $unconnected;
	}

	/** An IPv4 address an IPv6 socket names in its mapped form (::ffff:a.b.c.d), as the address it is. */
	private static function unmapped($ip)
	{
		return stripos($ip, '::ffff:') === 0 && strpos($ip, '.') !== false ? (string) substr($ip, 7) : $ip;
	}

	/**
	 * @param string $table
	 * @param int $header how many lines head the table
	 * @return string[][] its rows, split on whitespace
	 */
	private function rows($table, $header)
	{
		$lines = @file("{$this->root}/$table", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: array();
		$rows = array();
		foreach (array_slice($lines, $header) as $line)
		{
			$rows[] = preg_split('/\s+/', trim($line));
		}

		return $rows;
	}

	/**
	 * @param string $ip
	 * @param string[] $networks address/prefix
	 * @return bool
	 */
	private static function within($ip, array $networks)
	{
		$packed = inet_pton($ip);
		foreach ($networks as $network)
		{
			list($base, $bits) = explode('/', $network);
			$base = inet_pton($base);
			$whole = (int) ($bits / 8);
			$mask = chr((0xFF << (8 - $bits % 8)) & 0xFF);
			if (strlen($base) === strlen($packed) && (string) substr($packed, 0, $whole) === (string) substr($base, 0, $whole)
				&& ($bits % 8 === 0 || (substr($packed, $whole, 1) & $mask) === (substr($base, $whole, 1) & $mask)))
			{
				return true;
			}
		}

		return false;
	}

	/** The kernel prints an address as 32-bit words in host order: little-endian on every machine this harness runs on. */
	private static function hex($packed)
	{
		$hex = '';
		foreach (str_split($packed, 4) as $word)
		{
			$hex .= strrev($word);
		}

		return strtoupper(bin2hex($hex));
	}

	private static function address($hex)
	{
		return inet_ntop(hex2bin(self::hex(hex2bin($hex))));
	}
}
