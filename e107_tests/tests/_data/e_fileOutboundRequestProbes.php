<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Test doubles for e_fileOutboundRequestTest. They live outside tests/unit
 * because Codeception parses every test file to discover its cases, and that
 * happens before Helper\Unit has loaded class2.php, so a file that extends
 * e_file at parse time cannot be a test file.
 */

/**
 * Records what the production code puts to the outbound policy, and lets a
 * test decide how many hops the policy allows.
 *
 * It overrides the policy rather than the transport, because every hop in this
 * file is served by 127.0.0.1 and the real policy would, correctly, refuse the
 * first one. What is being measured is which URLs reach the predicate, not
 * what the predicate answers; @see the coverage gaps at the top of this file.
 */
class E107P3RecordingFile extends e_file
{
	/** @var string[] every URL handed to the policy, in order */
	public $seen = array();

	/** @var int how many distinct hops the policy will allow */
	public $allowHops = PHP_INT_MAX;

	public function isUrlSafe($url)
	{
		$this->seen[] = $url;

		return count($this->uniqueSeen()) <= $this->allowHops;
	}

	public function resolveOutboundTarget($url)
	{
		if(!$this->isUrlSafe($url))
		{
			return false;
		}

		$parts = parse_url($url);

		return array(
			'scheme'    => $parts['scheme'],
			'host'      => $parts['host'],
			'port'      => isset($parts['port']) ? (int) $parts['port'] : ($parts['scheme'] === 'https' ? 443 : 80),
			'addresses' => array(),
		);
	}

	/**
	 * @return string[] the chain as visited, without the repeats that come
	 *                  from getRemoteFile() checking before it walks
	 */
	public function uniqueSeen()
	{
		return array_values(array_unique($this->seen));
	}
}


/**
 * Drives the two resolvers behind resolveHostname() separately, so the state a
 * Windows host is intermittently in (PHP's own resolver silent, the operating
 * system's answering) can be put to the policy on any platform.
 */
class E107P3SplitResolverFile extends e_file
{
	/** @var string[]|null what PHP's own resolver answers, null to let it run */
	public $dnsRecords = null;

	/** @var string[]|null what the system resolver answers, null to let it run */
	public $systemRecords = null;

	/**
	 * @param string $host
	 * @return string[] as e_file::resolveHostname()
	 */
	public function addressesFor($host)
	{
		return $this->resolveHostname($host);
	}

	protected function dnsRecordAddresses($host)
	{
		return ($this->dnsRecords === null) ? parent::dnsRecordAddresses($host) : $this->dnsRecords;
	}

	protected function systemResolverAddresses($host)
	{
		return ($this->systemRecords === null) ? parent::systemResolverAddresses($host) : $this->systemRecords;
	}
}


/**
 * Substitutes the name lookup and the libcurl version, so the pin can be
 * asserted exactly without the suite depending on live DNS or on the version
 * of libcurl the container happens to ship.
 */
class E107P3ResolverFile extends e_file
{
	/** @var string[] addresses every name resolves to */
	public $addresses = array();

	/** @var int libcurl version to build the pin for */
	public $versionNumber = 0x080000; // 8.0.0

	protected function resolveHostname($host)
	{
		return $this->addresses;
	}

	protected function curlVersionNumber()
	{
		return $this->versionNumber;
	}
}


/**
 * Answers the policy wholesale, so a live fetch can be pointed at a name or an
 * address the real policy would never hand back. This is what lets the pin be
 * observed on the wire rather than read out of an option array: `.test` has no
 * DNS record anywhere (RFC 6761), so a request to it can only arrive if
 * CURLOPT_RESOLVE, or the stream transport's equivalent rewrite, was honoured.
 */
class E107P3PinnedFile extends e_file
{
	/** @var string[] the addresses the policy resolves every URL to */
	public $addresses = array('127.0.0.1');

	public function resolveOutboundTarget($url)
	{
		$parts = parse_url($url);

		if(empty($parts['host']) || empty($parts['scheme']))
		{
			return false;
		}

		return array(
			'scheme'    => $parts['scheme'],
			'host'      => $parts['host'],
			'port'      => isset($parts['port']) ? (int) $parts['port'] : ($parts['scheme'] === 'https' ? 443 : 80),
			'addresses' => $this->addresses,
		);
	}
}


/**
 * Appends an option value libcurl refuses. curl_setopt_array() applies options
 * in array order and stops at the first one it cannot set, ignoring the rest,
 * so this is what a build that declines an option e107 relies on looks like
 * from PHP's side of the handle.
 */
class E107P3RejectedOptionFile extends e_file
{
	public function curlOptions($address, $options = null, $target = null)
	{
		$curlOptions = parent::curlOptions($address, $options, $target);

		if($curlOptions !== false)
		{
			// libcurl answers CURLE_BAD_FUNCTION_ARGUMENT below -1.
			$curlOptions[CURLOPT_MAXREDIRS] = -2;
		}

		return $curlOptions;
	}
}
