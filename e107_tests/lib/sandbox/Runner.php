<?php

namespace Sandbox;

/**
 * Runs one suite across workers, each test file (or, for a suite that serves no HTTP, each batch of files) in a sandbox of its own: a fresh overlay of the worktree and a fresh database cloned from the suite's template.
 */
class Runner
{
	/** A tmpfs holding every sandbox's upper layer and the run's scratch files; mounted once per container. */
	const STATE = '/srv/sbstate';

	/** Compiled scripts the suite processes share, which outlive a run. */
	const OPCACHE = '/srv/sbstate/opcache';

	/** The worktree, read-only: the bottom layer of every sandbox. */
	const BASE = '/srv/base';

	/** Where Apache serves sandbox sbN.web from (see docker/apache-vhost.conf). */
	const SANDBOXES = '/srv/sb';

	const APP = '/var/www/html';

	/** The list Codeception's RunFailed extension keeps of the tests that failed, which `-g failed` runs again. */
	const FAILED = 'failed';

	/** What a batch's process writes for the runner; anything else in its output (coverage, --html, RunFailed's list) is the user's and is kept. */
	const OWN_FILES = array('console.log', 'report.xml', 'files.txt', UpperLayer::VIOLATIONS, self::FAILED);

	/** @var Shell */
	private $shell;

	/** @var Databases */
	private $databases;

	/** @var Suite */
	private $suite;

	/** @var Timings */
	private $timings;

	/** @var array{codecept: string, pool: string, dump: string, base_path: string, output: string, value_options: string[]} */
	private $settings;

	/** @var array<string,int> */
	private $totals = array('tests' => 0, 'assertions' => 0, 'failures' => 0, 'errors' => 0, 'skipped' => 0);

	/** @var array[] [label, failed tests, leaks, crashed, where its output is kept] of every batch that did not pass */
	private $problems = array();

	/** @var string[] RunFailed's list from the run before, less the tests whose files are gone; each batch of a `-g failed` run starts with its share */
	private $failedBefore = array();

	/** @var bool whether `-g failed` is the whole selection, so each batch's share of that list says which of its tests to run */
	private $failedOnly = false;

	/** @var string[] what `-g failed` runs next time: RunFailed's lists of this run's batches, the tests that left their sandbox changed, and the files of a process that died */
	private $failed = array();

	/** @var \DOMElement[] */
	private $reports = array();

	/**
	 * @param Shell $shell
	 * @param Databases $databases
	 * @param Suite $suite
	 * @param Timings $timings
	 * @param array $settings codecept and pool: command prefixes; dump: the suite's dump file; base_path: the site's subdirectory or ''; output: where results land; value_options: codecept run's options that take the argument after them as their value
	 */
	public function __construct(Shell $shell, Databases $databases, Suite $suite, Timings $timings, array $settings)
	{
		$this->shell = $shell;
		$this->databases = $databases;
		$this->suite = $suite;
		$this->timings = $timings;
		$this->settings = $settings;
	}

	/**
	 * @param int $jobs workers
	 * @param string[] $args codecept arguments; any naming a test file or directory narrows the run to it
	 * @return int exit status
	 */
	public function run($jobs, array $args)
	{
		$started = microtime(true);
		$run = substr(md5(uniqid('', true)), 0, 6);
		$scratch = self::STATE.'/run';
		$listed = $this->settings['output'].'/'.self::FAILED;
		$before = is_file($listed) ? file_get_contents($listed) : null;
		$this->failedBefore = preg_split('/\r?\n/', (string) $before, -1, PREG_SPLIT_NO_EMPTY);
		$this->prepare($scratch);
		if ($before !== null)
		{
			file_put_contents($listed, $before);
		}

		$this->databases->grantPrefix('e107_s');
		$pool = new Pool("$scratch/pool-$run");
		try
		{
			return $this->runWith($pool, $run, $scratch, $jobs, $args, $started);
		}
		finally
		{
			$pool->stop();
			$this->sandbox(0)->unmount();
		}
	}

	/**
	 * @param Pool $pool
	 * @param string $run
	 * @param string $scratch
	 * @param int $jobs
	 * @param string[] $args
	 * @param float $started
	 * @return int exit status
	 */
	private function runWith(Pool $pool, $run, $scratch, $jobs, array $args, $started)
	{
		$pool->startDropper($this->settings['pool']);
		foreach (array_merge($this->databases->named('e107_s'), $this->databases->named('e107_t')) as $stale)
		{
			$pool->release($stale);
		}

		$this->shell->run('cd '.escapeshellarg($this->suite->root()).' && '.$this->settings['codecept'].' build');
		list($files, $only) = $this->selection($args);
		if (($this->failedOnly || count($only) > 1) && array_filter($only, function ($arg)
		{
			list(, $filter) = Suite::split($arg);

			return $filter !== '';
		}))
		{
			fwrite(STDERR, $this->failedOnly
				? "error: -g failed runs the tests on its list, so it takes no :test filter; name the file whole, or run the filter without -g failed\n"
				: "error: a :test filter picks tests from one named file at a time, as codecept takes it; name the others whole, or run them apart\n");

			return 2;
		}
		$args = array_values(array_diff($args, $only));
		if (!$files)
		{
			$this->summarise(0, microtime(true) - $started);

			return 0;
		}
		$jobs = max(1, min($jobs, count($files)));
		$failFast = (bool) preg_grep('/^(-f|--fail-fast)/', $args);

		$template = "e107_t$run";
		$layers = $this->buildTemplate($template, $scratch);
		if ($layers === null)
		{
			$pool->release($template);

			return 1;
		}
		$pool->startCloners($this->settings['pool'], $template, "e107_s{$run}_", $jobs + 1, 2);

		$batched = !$this->suite->servesHttp() || !$this->suite->isolatesFiles();
		$queue = new Queue($this->timings->estimate($this->suite->root(), $files), $jobs, $batched);
		$total = $queue->count();
		$passthrough = $jobs === 1 && $batched;
		$running = array();
		$idle = range(1, $jobs);
		$done = 0;
		$number = 0;
		$stopping = false;

		while (!$queue->isEmpty() && !$stopping || $running)
		{
			foreach ($running as $worker => $batch)
			{
				if ($batch->isRunning())
				{
					continue;
				}
				unset($running[$worker]);
				$idle[] = $worker;
				$pool->release($batch->database);
				if (!$this->sandbox($worker)->unmount())
				{
					fwrite(STDERR, "!! sb$worker was still serving a request ten seconds after ".$batch->label()." ended, and was detached from under it\n");
				}
				$done += count($batch->files);
				$passed = $this->finish($batch, $done, $total, $passthrough);
				$stopping = $stopping || ($failFast && !$passed);
			}
			if ($idle && !$queue->isEmpty() && !$stopping && !$running && !$pool->isCloning())
			{
				throw new \RuntimeException("the database copies stopped coming; see $scratch/pool-$run/pool.log");
			}
			while ($idle && !$queue->isEmpty() && !$stopping && ($database = $pool->claim()) !== null)
			{
				$worker = array_shift($idle);
				$batch = new Batch(++$number, $queue->next(), $worker, $database, "$scratch/units/$number");
				if ($this->failedBefore)
				{
					file_put_contents($batch->dir.'/'.self::FAILED, implode("\n", array_filter($this->failedBefore, function ($line) use ($batch)
					{
						return in_array(self::fileOf($line), $batch->files, true);
					}))."\n");
				}
				$overlay = $this->sandbox($worker);
				$overlay->mount($layers['lowers']);
				$batch->start($this->command($batch, $overlay, $layers, $this->targets($batch, $only, $total), $args), $passthrough);
				$running[$worker] = $batch;
			}
			usleep(5000);
		}

		$pool->release($template);
		$this->timings->save();
		if ($this->failed)
		{
			file_put_contents($this->settings['output'].'/'.self::FAILED, implode("\n", array_unique($this->failed))."\n");
		}
		else
		{
			@unlink($this->settings['output'].'/'.self::FAILED);
		}
		$this->writeReport();
		$this->summarise($jobs, microtime(true) - $started);

		return empty($this->problems) ? 0 : 1;
	}

	/** Mount what every sandbox is made of, and clear what a run that never finished left mounted. */
	private function prepare($scratch)
	{
		$state = escapeshellarg(self::STATE);
		$base = escapeshellarg(self::BASE);
		$app = escapeshellarg(self::APP);
		$this->shell->run("set -e; mkdir -p $state $base ".escapeshellarg(self::SANDBOXES)
			."; awk '\$2 ~ \"^(".self::SANDBOXES."|".self::STATE.")/\" {print \$2}' /proc/mounts | sort -r | while read -r m; do umount -l \"\$m\"; done"
			."; mountpoint -q $state || mount -t tmpfs -o mode=0755 sandbox-state $state"
			."; mountpoint -q $base || { mount --bind $app $base && mount -o remount,bind,ro $base; }"
			."; rm -rf ".escapeshellarg($scratch)."; mkdir -p ".escapeshellarg($scratch).' '.escapeshellarg(self::OPCACHE));
		$this->shell->run('rm -rf '.escapeshellarg($this->settings['output']).'; mkdir -p '.escapeshellarg($this->settings['output']));
	}

	/**
	 * @param string[] $args
	 * @return array{0: string[], 1: string[]} the files to run, and the arguments that named them
	 */
	private function selection(array $args)
	{
		$all = $this->suite->files();
		$only = array();
		$files = array();
		foreach ($args as $i => $arg)
		{
			$path = $arg === '' || $arg[0] === '-' || ($i > 0 && in_array($args[$i - 1], $this->settings['value_options'], true)) ? null : $this->suite->locate($arg);
			if ($path === null)
			{
				continue;
			}
			$only[] = $arg;
			foreach ($all as $file)
			{
				if ($file === $path || strpos($file, "$path/") === 0)
				{
					$files[] = $file;
				}
			}
		}
		$files = $only ? array_values(array_unique($files)) : $all;
		$groups = self::groups($args);
		$this->failedOnly = $groups === array(self::FAILED);
		$this->failedBefore = in_array(self::FAILED, $groups, true) ? array_values(array_filter($this->failedBefore, function ($line) use ($all)
		{
			return in_array(self::fileOf($line), $all, true);
		})) : array();

		return array($this->failedOnly ? array_values(array_intersect($files, array_map(array(__CLASS__, 'fileOf'), $this->failedBefore))) : $files, $only);
	}

	/** @return string[] the groups `-g` selects */
	private static function groups(array $args)
	{
		$groups = array();
		foreach ($args as $i => $arg)
		{
			if (in_array($arg, array('-g', '--group'), true) && isset($args[$i + 1]))
			{
				$groups[] = $args[$i + 1];
			}
			elseif (preg_match('/^(?:-g|--group=)(.+)$/', $arg, $m))
			{
				$groups[] = $m[1];
			}
		}

		return array_values(array_unique($groups));
	}

	/**
	 * @param string $line an entry of RunFailed's list: a test file, with the test after a colon
	 * @return string the file
	 */
	private static function fileOf($line)
	{
		return preg_replace('/\.php:.*$/s', '.php', $line);
	}

	/**
	 * Build the database every sandbox starts from: the site its site-template test installs, kept with the files the install wrote; otherwise the suite's dump.
	 *
	 * @param string $template
	 * @param string $scratch
	 * @return array{lowers: string[], start: string}|null the lower layers every sandbox mounts and the tree they make, or null when the template could not be built
	 */
	private function buildTemplate($template, $scratch)
	{
		$layers = array('lowers' => array(self::BASE), 'start' => self::BASE);
		if (!$this->suite->hasSiteTemplate())
		{
			$this->databases->create($template, false);
			$this->databases->load($template, $this->settings['dump']);

			return $layers;
		}
		$this->databases->create($template, true);
		try
		{
			$overlay = $this->sandbox(0);
			$overlay->mount($layers['lowers']);
			$batch = new Batch(0, array(), 1, $template, "$scratch/units/template");
			$batch->start($this->command($batch, $overlay, $layers, array('-g', Suite::SITE_TEMPLATE_GROUP), array()), false);
			while ($batch->isRunning())
			{
				usleep(5000);
			}
			$outcome = new Outcome($batch, $this->suite->root());
			if (!$outcome->passed() || $outcome->counts['tests'] === 0)
			{
				fwrite(STDERR, "!! the site every test starts from could not be built:\n".file_get_contents($batch->log()));

				return null;
			}
			$overlay->freeze($layers['lowers']);

			return array('lowers' => array($overlay->upper(), self::BASE), 'start' => $overlay->view());
		}
		finally
		{
			$this->databases->revoke($template);
		}
	}

	/** @param int $worker 0 for the template's sandbox, which shares the first worker's host */
	private function sandbox($worker)
	{
		$name = 'sb'.max(1, $worker);
		$mountPoint = self::SANDBOXES."/$name";
		if ($this->suite->servesHttp() && $this->settings['base_path'] !== '')
		{
			$mountPoint .= '/'.$this->settings['base_path'];
		}

		return new Overlay($this->shell, $mountPoint, self::STATE.'/'.($worker === 0 ? 'template' : $name), self::STATE."/sessions/$name");
	}

	/**
	 * The codecept arguments that select a batch's tests: none for a `-g failed` run, whose batches each have their share of the list; the user's own when the batch is the whole selection, so a single test named with its method runs as named.
	 *
	 * @param Batch $batch
	 * @param string[] $only the arguments that named the selection
	 * @param int $total files selected
	 * @return string[]
	 */
	private function targets(Batch $batch, array $only, $total)
	{
		if ($this->failedOnly)
		{
			return array();
		}
		if (count($batch->files) === $total && count($only) <= 1)
		{
			return $only;
		}
		if (count($batch->files) === 1)
		{
			return $batch->files;
		}
		file_put_contents($batch->dir.'/files.txt', implode("\n", $batch->files)."\n");

		return array('-o', 'groups: sandbox: '.$batch->dir.'/files.txt', '-g', 'sandbox');
	}

	/**
	 * @param Batch $batch
	 * @param Overlay $overlay
	 * @param array{lowers: string[], start: string} $layers
	 * @param string[] $targets
	 * @param string[] $args
	 * @return string
	 */
	private function command(Batch $batch, Overlay $overlay, array $layers, array $targets, array $args)
	{
		$base = $this->suite->servesHttp() && $this->settings['base_path'] !== '' ? $this->settings['base_path'].'/' : '';
		$params = array(
			'url' => 'http://sb'.$batch->worker.".web/$base",
			'db' => array('dbname' => $batch->database, 'populate' => false),
			'sandbox' => array('upper' => $overlay->upper(), 'start' => $layers['start']),
		);
		$wrapper = '';
		if ($this->suite->servesHttp())
		{
			$params['app_path'] = $overlay->mountPoint().'/';
		}
		else
		{
			$wrapper = escapeshellarg(__DIR__.'/in-sandbox').' '.escapeshellarg(dirname($overlay->upper())).' '.escapeshellarg($overlay->mountPoint()).' '.escapeshellarg(self::APP).' ';
		}

		$end = array_search('--', $args, true);
		$end = $end === false ? count($args) : $end;

		return 'cd '.escapeshellarg($this->suite->root()).' && E107_TEST_PARAMS='.escapeshellarg(json_encode($params, JSON_UNESCAPED_SLASHES))
			.' exec '.$wrapper.$this->settings['codecept'].' run '.escapeshellarg($this->suite->name()).' '
			.implode(' ', array_map('escapeshellarg', array_merge($targets, array_slice($args, 0, $end),
				array('--no-rebuild', '--xml=report.xml', '-o', 'settings: log_incomplete_skipped: true', '-o', 'paths: output: '.$batch->dir), array_slice($args, $end))));
	}

	/** @return bool whether the batch passed */
	private function finish(Batch $batch, $done, $total, $passthrough)
	{
		$outcome = new Outcome($batch, $this->suite->root());
		foreach ($outcome->counts as $count => $n)
		{
			$this->totals[$count] += $n;
		}
		$this->reports = array_merge($this->reports, $outcome->suites);
		$this->timings->record(count($batch->files) === 1 ? array($batch->files[0] => $batch->seconds) : $outcome->seconds);

		if ($outcome->failed)
		{
			$this->failed = array_merge($this->failed, file($batch->dir.'/'.self::FAILED, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		}
		foreach ($outcome->leaks as $leak)
		{
			$this->failed[] = $leak['name'];
		}
		if ($outcome->crashed)
		{
			$this->failed = array_merge($this->failed, $batch->files);
		}
		$passed = $outcome->passed();
		$keep = count($batch->files) === $total ? $this->settings['output'] : $this->settings['output'].'/'.$batch->label();
		if (!$passed || array_diff(scandir($batch->dir), array('.', '..'), self::OWN_FILES))
		{
			$this->shell->run('mkdir -p '.escapeshellarg($keep).' && cp -r '.escapeshellarg($batch->dir).'/. '.escapeshellarg($keep));
		}
		if (!$passed)
		{
			$this->problems[] = array($batch->label(), $outcome->failed, $outcome->leaks, $outcome->crashed, $keep);
		}
		if (!$passthrough)
		{
			printf("%s [%d/%d] %s, worker %d, %.1fs\n%s", $passed ? 'OK  ' : 'FAIL', $done, $total, $batch->label(), $batch->worker, $batch->seconds,
				self::body(file_get_contents($batch->log())));
		}

		return $passed;
	}

	/** A batch's log without the banner and the summary every process prints. */
	private static function body($log)
	{
		$lines = array();
		foreach (preg_split('/\r?\n/', $log) as $line)
		{
			if (!preg_match('/^(Codeception PHP Testing Framework|Powered by PHPUnit|Running with seed|Time: |OK \(|OK, but |FAILURES!$|ERRORS!$|WARNINGS!$|Tests: \d+, Assertions: |run with `-v` |- (JUNIT )?XML report generated in |\S.* Tests \(\d+\) -+$|-+$|\s*$)/', preg_replace('/\e\[[0-9;]*m/', '', $line)))
			{
				$lines[] = $line;
			}
		}

		return $lines ? implode("\n", $lines)."\n" : '';
	}

	private function writeReport()
	{
		$dom = new \DOMDocument('1.0', 'UTF-8');
		$dom->formatOutput = true;
		$root = $dom->appendChild($dom->createElement('testsuites'));
		foreach ($this->reports as $suite)
		{
			$root->appendChild($dom->importNode($suite, true));
		}
		$leaks = $dom->createElement('testsuite');
		$leaks->setAttribute('name', 'sandbox audit');
		foreach ($this->problems as $problem)
		{
			foreach ($problem[2] as $leak)
			{
				$case = $leaks->appendChild($dom->createElement('testcase'));
				$case->setAttribute('name', $leak['test']);
				$case->appendChild($dom->createElement('failure', htmlspecialchars(self::describe($leak))));
			}
		}
		$root->appendChild($leaks);
		$dom->save($this->settings['output'].'/report.xml');
	}

	private static function describe(array $leak)
	{
		$lines = array();
		foreach (array('new' => 'new paths', 'modified' => 'worktree files modified', 'deleted' => 'worktree files deleted') as $kind => $title)
		{
			if (!empty($leak[$kind]))
			{
				$lines[] = "$title: ".implode(', ', $leak[$kind]);
			}
		}

		return implode('; ', $lines);
	}

	private function summarise($jobs, $seconds)
	{
		$t = $this->totals;
		printf("\n%s: %d tests, %d assertions, %d failures, %d errors, %d skipped; %d worker%s, %.1fs\n",
			$this->suite->name(), $t['tests'], $t['assertions'], $t['failures'], $t['errors'], $t['skipped'], $jobs, $jobs === 1 ? '' : 's', $seconds);
		if (empty($this->problems))
		{
			echo $t['skipped'] === 0
				? "OK ({$t['tests']} tests, {$t['assertions']} assertions)\n"
				: "OK, but incomplete, skipped, or useless tests!\nTests: {$t['tests']}, Assertions: {$t['assertions']}, Skipped: {$t['skipped']}.\n";

			return;
		}
		echo "FAILURES!\n";
		foreach ($this->problems as $problem)
		{
			list($label, $failed, $leaks, $crashed, $keep) = $problem;
			echo "  $label".($crashed ? ': the process died before writing its report' : '').", output in $keep/\n";
			foreach ($failed as $test)
			{
				echo "    failed: $test\n";
			}
			foreach ($leaks as $leak)
			{
				echo '    left its sandbox changed: '.$leak['test'].': '.self::describe($leak)."\n";
			}
		}
	}
}
