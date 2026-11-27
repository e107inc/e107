<?php

namespace Sandbox;

/**
 * The nftables rules that send every name lookup, and every connection leaving the stack, to the recorder, while a run lasts.
 *
 * What stays inside: loopback, and the networks the container reaches without a gateway (the stack's own, which holds the database and every sandbox name).
 * Keep this class in PHP 5.6 syntax: the unit suite runs on 5.6.
 */
class Fence
{
	const TABLE = 'e107_boundary';

	/** Ahead of the rule at -100 that Docker gives the container for its name server, whichever of them went in first. */
	const PRIORITY = -101;

	/** @var Shell */
	private $shell;

	/** @var ProcNet */
	private $net;

	/** @var string */
	private $file;

	/** @var int */
	private $port;

	/**
	 * @param Shell $shell
	 * @param ProcNet $net
	 * @param string $file where the rules are written for nft to read
	 * @param int $port where the recorder listens on loopback
	 */
	public function __construct(Shell $shell, ProcNet $net, $file, $port)
	{
		$this->shell = $shell;
		$this->net = $net;
		$this->file = $file;
		$this->port = $port;
	}

	/**
	 * Put the rules up, in place of any a run before left, in one transaction, and forget the lookups made before them.
	 *
	 * @return void
	 */
	public function raise()
	{
		$rules = $this->clear();
		foreach ($this->families() as $family => $exempt)
		{
			$rules .= "table $family ".self::TABLE." {\n\tchain output {\n\t\ttype nat hook output priority ".self::PRIORITY."; policy accept;\n"
				."\t\tudp dport 53 redirect to :$this->port\n\t\ttcp dport 53 redirect to :$this->port\n"
				."\t\t$family daddr { ".implode(', ', $exempt)." } return\n"
				."\t\tmeta l4proto tcp redirect to :$this->port\n\t\tmeta l4proto udp redirect to :$this->port\n\t}\n}\n";
		}
		$this->apply($rules);
		$this->forget('--orig-port-dst 53');
	}

	/** Take the rules down, whether or not they are up, and forget what they sent to the recorder. */
	public function lower()
	{
		$this->apply($this->clear());
		$this->forget("--reply-port-src $this->port");
	}

	/** Delete the datagram flows the kernel tracks that match $filter; conntrack exits 1 when none do, as it does when it fails. */
	private function forget($filter)
	{
		foreach (array_keys($this->families()) as $family)
		{
			$this->shell->run('said=$(conntrack -D -f '.($family === 'ip' ? 'ipv4' : 'ipv6')." -p udp $filter 2>&1 >/dev/null)"
				.' || case $said in *": 0 flow entries have been deleted.") ;; *) echo "$said"; exit 1 ;; esac');
		}
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

	private function apply($rules)
	{
		file_put_contents($this->file, $rules);
		$this->shell->run('nft -f '.escapeshellarg($this->file));
	}
}
