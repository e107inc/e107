<?php

namespace Sandbox;

/**
 * What one batch's report and sandbox audit say about it.
 */
class Outcome
{
	/** How Codeception 4's reports, asked to log tests that did not fail, open the error they record for one, and what it counts as: PHPUnit 5's logger records all three this way, the wrapper of PHPUnit 9 a risky test only. */
	const NOT_FAILED = array("Skipped Test\n" => 'skipped', "Incomplete Test\n" => 'skipped', "Risky Test\n" => null);

	/** @var array<string,int> tests, assertions, failures, errors, skipped */
	public $counts = array('tests' => 0, 'assertions' => 0, 'failures' => 0, 'errors' => 0, 'skipped' => 0);

	/** @var string[] tests that failed or erred */
	public $failed = array();

	/** @var array[] one record per test that left its sandbox changed */
	public $leaks = array();

	/** @var array<string,float> seconds spent in each file */
	public $seconds = array();

	/** @var \DOMElement[] the report's testsuite elements */
	public $suites = array();

	/** @var bool whether the process ended without a report */
	public $crashed;

	/** @var int */
	private $exitCode;

	/**
	 * @param Batch $batch a finished batch
	 * @param string $root the project directory, which file paths are reported relative to
	 */
	public function __construct(Batch $batch, $root)
	{
		$this->exitCode = $batch->exitCode;
		$report = $batch->dir.'/report.xml';
		$this->crashed = !is_file($report);
		if (!$this->crashed)
		{
			$this->read($report, rtrim($root, '/').'/');
		}
		$violations = $batch->dir.'/'.UpperLayer::VIOLATIONS;
		foreach (is_file($violations) ? file($violations, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : array() as $line)
		{
			$this->leaks[] = json_decode($line, true);
		}
	}

	/** @return bool */
	public function passed()
	{
		return !$this->crashed && $this->exitCode === 0 && empty($this->failed) && empty($this->leaks);
	}

	private function read($report, $root)
	{
		$dom = new \DOMDocument();
		$dom->load($report);
		foreach ($dom->documentElement->childNodes as $node)
		{
			if ($node instanceof \DOMElement && $node->tagName === 'testsuite')
			{
				$this->suites[] = $node;
			}
		}
		foreach ($dom->getElementsByTagName('testcase') as $case)
		{
			$this->counts['tests']++;
			$this->counts['assertions'] += (int) $case->getAttribute('assertions');
			$file = $case->getAttribute('file');
			$file = strpos($file, $root) === 0 ? (string) substr($file, strlen($root)) : $file;
			$this->seconds[$file] = (isset($this->seconds[$file]) ? $this->seconds[$file] : 0.0) + (float) $case->getAttribute('time');
			list($kind, $error) = self::kind($case);
			if ($error !== null)
			{
				self::refile($case, $error, $kind);
			}
			if ($kind !== null)
			{
				$this->counts[$kind]++;
				if ($kind !== 'skipped')
				{
					$this->failed[] = $case->getAttribute('class').':'.$case->getAttribute('name');
				}
			}
		}
	}

	/** @return array{0: string|null, 1: \DOMElement|null} failures, errors or skipped, or null for a test that passed; and the error Codeception 4 recorded for it if it did not fail */
	private static function kind(\DOMElement $case)
	{
		foreach (array('failure' => 'failures', 'error' => 'errors', 'warning' => 'failures', 'skipped' => 'skipped') as $tag => $kind)
		{
			$fault = $case->getElementsByTagName($tag)->item(0);
			if ($fault === null)
			{
				continue;
			}
			foreach (self::NOT_FAILED as $opening => $notFailed)
			{
				if ($tag === 'error' && strpos($fault->textContent, $opening) === 0)
				{
					return array($notFailed, $fault);
				}
			}

			return array($kind, null);
		}

		return array(null, null);
	}

	/**
	 * Files a test Codeception 4 recorded as an error without its failing the way Codeception 5 files it, so the merged report agrees with the run.
	 *
	 * @param \DOMElement $case
	 * @param \DOMElement $error what Codeception 4 recorded
	 * @param string|null $kind skipped, or null for a pass
	 * @return void
	 */
	private static function refile(\DOMElement $case, \DOMElement $error, $kind)
	{
		$case->removeChild($error);
		if ($kind !== null)
		{
			$case->appendChild($case->ownerDocument->createElement($kind));
		}
		for ($suite = $case->parentNode; $suite instanceof \DOMElement && $suite->tagName === 'testsuite'; $suite = $suite->parentNode)
		{
			$suite->setAttribute('errors', (int) $suite->getAttribute('errors') - 1);
			if ($kind !== null)
			{
				$suite->setAttribute($kind, (int) $suite->getAttribute($kind) + 1);
			}
		}
	}
}
