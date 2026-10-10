<?php

namespace Sandbox;

/**
 * The nftables rules that send every connection leaving the stack to the recorder, while a run lasts.
 *
 * What stays inside: loopback, the networks the container reaches without a gateway (the stack's own, which holds the database and every sandbox name), and name lookups at the stack's name servers.
 * Keep this class in PHP 5.6 syntax: the unit suite runs on 5.6.
 */
class Fence
{
	const TABLE = 'e107_boundary';

	/** @var Shell */
	private $shell;

	/** @var ProcNet */
	private $net;

	/** @var string */
	private $resolvConf;

	/** @var string */
	private $file;

	/**
	 * @param Shell $shell
	 * @param ProcNet $net
	 * @param string $resolvConf where the container's name servers are listed
	 * @param string $file where the rules are written for nft to read
	 */
	public function __construct(Shell $shell, ProcNet $net, $resolvConf, $file)
	{
		$this->shell = $shell;
		$this->net = $net;
		$this->resolvConf = $resolvConf;
		$this->file = $file;
	}

	/**
	 * Put the rules up, in place of any a run before left, in one transaction.
	 *
	 * @param int $port where the recorder listens on loopback
	 * @return void
	 */
	public function raise($port)
	{
		$rules = $this->clear();
		foreach ($this->families() as $family => $exempt)
		{
			$address = $family === 'ip' ? 'ip daddr' : 'ip6 daddr';
			$resolvers = $this->resolvers($family);
			$rules .= "table $family ".self::TABLE." {\n\tchain output {\n\t\ttype nat hook output priority -100; policy accept;\n"
				."\t\t$address { ".implode(', ', $exempt)." } return\n"
				.($resolvers ? "\t\t$address { $resolvers } udp dport 53 return\n\t\t$address { $resolvers } tcp dport 53 return\n" : '')
				."\t\tmeta l4proto tcp redirect to :$port\n\t\tmeta l4proto udp redirect to :$port\n\t}\n}\n";
		}
		$this->apply($rules);
	}

	/** Take the rules down, whether or not they are up. */
	public function lower()
	{
		$this->apply($this->clear());
	}

	/** @return array<string,string[]> the address families the container has, each with the destinations that stay inside */
	private function families()
	{
		$families = array('ip' => $this->net->inside('ip'));
		if ($this->net->hasIpv6())
		{
			$families['ip6'] = $this->net->inside('ip6');
		}

		return $families;
	}

	/** Creating a table and deleting it in the same transaction deletes it whether or not it was there, which nft 0.7 (PHP 5.6 and 7.0 images) has no other way to say. */
	private function clear()
	{
		$rules = '';
		foreach (array_keys($this->families()) as $family)
		{
			$rules .= "add table $family ".self::TABLE."\ndelete table $family ".self::TABLE."\n";
		}

		return $rules;
	}

	/** @return string the name servers of $family, comma separated */
	private function resolvers($family)
	{
		preg_match_all('/^\s*nameserver\s+([^\s%]+)/m', (string) @file_get_contents($this->resolvConf), $matches);
		$servers = array();
		foreach ($matches[1] as $server)
		{
			if ((strpos($server, ':') !== false) === ($family === 'ip6'))
			{
				$servers[] = $server;
			}
		}

		return implode(', ', array_unique($servers));
	}

	private function apply($rules)
	{
		file_put_contents($this->file, $rules);
		$this->shell->run('nft -f '.escapeshellarg($this->file));
	}
}
