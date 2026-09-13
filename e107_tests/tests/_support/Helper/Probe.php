<?php

namespace Helper;

/**
 * The fixture a Cest drops into the docroot and drives by query string: written through the deployer, guarded by {@see ProbeGuard}, taken back out by {@see AppFileRegistry}.
 */
class Probe extends AppFixture
{
	const OK = 'PROBE_OK';

	/** @var string|null relative path of the probe the fetchers address */
	private $file;

	/**
	 * Write $source at $relative_path and make it the probe the other methods fetch.
	 *
	 * @param string $relative_path relative to the app root
	 * @param string $source PHP carrying {@see ProbeGuard::MARKER} wherever it boots e107
	 * @return void
	 */
	public function haveProbe($relative_path, $source)
	{
		$this->app()->writeAppFile($relative_path, $source);
		$this->file = $relative_path;
	}

	/**
	 * Load the probe in the browser; the URL carries the secret because a WebDriver browser cannot send the guard's header.
	 *
	 * @param string $query
	 * @return void
	 */
	public function amOnProbe($query = '')
	{
		$this->browser()->amOnPage($this->url($query));
	}

	/**
	 * @param string $query
	 * @return string the probe's answer, trimmed
	 */
	public function grabProbe($query = '')
	{
		$this->amOnProbe($query);

		return trim($this->browser()->grabPageSource());
	}

	/**
	 * Run an action the probe has to acknowledge with PROBE_OK.
	 *
	 * @param string $query
	 * @return string the answer, trimmed
	 * @throws \RuntimeException when the answer lacks PROBE_OK
	 */
	public function probe($query = '')
	{
		$body = $this->grabProbe($query);

		if (strpos($body, self::OK) === false)
		{
			throw new \RuntimeException($this->file.' failed for "'.$query.'": '.trim(strip_tags($body)));
		}

		return $body;
	}

	/**
	 * @param string $query
	 * @return array|null what the probe printed as JSON on the lines after PROBE_OK
	 */
	public function grabProbeJson($query = '')
	{
		$body = $this->probe($query);

		return json_decode(trim((string) substr($body, strpos($body, "\n"))), true);
	}

	private function url($query)
	{
		if ($this->file === null)
		{
			throw new \RuntimeException('No probe has been written yet; call haveProbe() first');
		}

		$url = '/'.$this->file.'?'.ProbeGuard::query();

		return ($query === '') ? $url : $url.'&'.$query;
	}
}
