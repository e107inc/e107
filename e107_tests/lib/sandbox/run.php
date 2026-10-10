<?php
/**
 * Runs a Codeception suite across overlay sandboxes inside the web container; `e107-tests run` is the way in.
 *
 *   php lib/sandbox/run.php [-d key=value]... <suite> [--jobs N] [codecept arguments]
 *
 * -d settings are passed on to every suite process. --clone and --drop are the database pool's own loops, and --record the network boundary's recorder, which the runner starts.
 * Keep this file in PHP 5.6 syntax: the unit suite runs on 5.6.
 */

namespace Sandbox;

spl_autoload_register(function ($class)
{
	$file = __DIR__.'/'.substr($class, strlen(__NAMESPACE__) + 1).'.php';
	if (strpos($class, __NAMESPACE__.'\\') === 0 && is_file($file))
	{
		require $file;
	}
});

$root = dirname(dirname(__DIR__));
require "$root/vendor/autoload.php";
\Codeception\Configuration::config("$root/codeception.yml");
$params = include "$root/lib/config.php";
$databases = new Databases("mysql:host={$params['db']['host']};port={$params['db']['port']}", getenv('E107_DB_ROOT_PASSWORD'), $params['db']['user']);

$argv = array_slice($_SERVER['argv'], 1);
if ($argv && $argv[0] === '--clone')
{
	Pool::cloneLoop($databases, $argv[1], $argv[2], $argv[3], (int) $argv[4]);
	exit(0);
}
if ($argv && $argv[0] === '--drop')
{
	Pool::dropLoop($databases, $argv[1]);
	exit(0);
}
if ($argv && $argv[0] === '--record')
{
	$boundary = new Boundary($argv[1], new Shell(), new ProcNet());
	try
	{
		$boundary->record();
	}
	catch (\RuntimeException $e)
	{
		fwrite(STDERR, $e->getMessage()."\n");
		exit(1);
	}
	exit(0);
}

// One run per env at a time: the sandboxes and their databases are the env's.
// flock(1) holds the lock outside this process, so no suite process inherits it
// and a server a test leaves running cannot keep it.
if (getenv('E107_SANDBOX_LOCKED') !== '1')
{
	$lock = escapeshellarg(Runner::STATE.'.lock');
	$self = implode(' ', array_map('escapeshellarg', array_merge(array(PHP_BINARY, __FILE__), $argv)));
	$run = proc_open("flock -n $lock true || echo '>> another suite run holds this env; waiting for it to finish' >&2; E107_SANDBOX_LOCKED=1 exec flock -o $lock $self",
		array(STDIN, STDOUT, STDERR), $pipes);
	exit(proc_close($run));
}

$shell = new Shell();
$probe = Runner::STATE.'/probe';
$overlay = new Overlay($shell, "$probe/merged", "$probe/state", "$probe/sessions");
$host = 'this host ('.php_uname('r').')';
$xattrs = 'user extended attributes on tmpfs, which Linux keeps from 6.6 on';
try
{
	$removes = $overlay->removesLowerDirectories("$probe/lower");
}
catch (\RuntimeException $e)
{
	fwrite(STDERR, "error: the check that the sandboxes' overlay works failed on $host. The overlay takes CAP_SYS_ADMIN in the web container, which `up` gives it, and $xattrs.\n".$e->getMessage()."\n");
	exit(2);
}
rmdir($probe);
if (!$removes)
{
	fwrite(STDERR, "error: an overlay on $host cannot remove a directory of its lower layer, so a test that removes one of the worktree's directories, or moves a directory into one, would fail. That takes $xattrs.\n");
	exit(2);
}

$php = array('-d', 'register_argc_argv=1');
while ($argv && $argv[0] === '-d')
{
	$php = array_merge($php, array_splice($argv, 0, 2));
}
$name = array_shift($argv);
$jobs = null;
$args = array();
for ($i = 0; $i < count($argv); $i++)
{
	if ($argv[$i] === '--jobs' || strpos($argv[$i], '--jobs=') === 0)
	{
		$jobs = (int) ($argv[$i] === '--jobs' ? $argv[++$i] : (string) substr($argv[$i], 7));
		continue;
	}
	$args[] = $argv[$i];
}

// The stack names its sandboxes as aliases of this container, as many as the
// image serves; a name past the last is asked of the Internet's DNS, so none is.
if (getenv('E107_SANDBOXES') === false)
{
	fwrite(STDERR, "error: this env's web image is older than the harness in this tree; bring the env up again with `e107-tests up`\n");
	exit(2);
}
$sandboxes = 0;
while ($sandboxes < (int) getenv('E107_SANDBOXES') && gethostbyname('sb'.($sandboxes + 1).'.web') === gethostbyname('web'))
{
	$sandboxes++;
}
$suite = new Suite($name, $root, \Codeception\Configuration::suiteSettings($name, \Codeception\Configuration::config()));
$together = null;
if (!$suite->isolatesFiles())
{
	$together = "this tree's $name suite predates the sandboxes (it does not enable Extension\\SandboxGuard)";
}
elseif (!$suite->servesHttp() && preg_grep('/^(-g|--group|--coverage)/', $args))
{
	$together = "--group and --coverage select tests across the whole $name suite";
}
if ($jobs === null)
{
	$jobs = $together !== null ? 1 : $suite->defaultJobs(min((int) shell_exec('nproc'), $sandboxes));
}
if ($together !== null && $jobs !== 1)
{
	fwrite(STDERR, "error: $together, so it runs as one process (--jobs 1)\n");
	exit(2);
}
if ($jobs < 1 || $jobs > $sandboxes)
{
	fwrite(STDERR, "error: --jobs takes 1 to $sandboxes, the number of sandboxes this stack names (sbN.web)\n");
	exit(2);
}

// Each process compiles Codeception afresh; a file cache the processes share saves a third of that.
if (PHP_VERSION_ID >= 70000 && function_exists('opcache_get_status'))
{
	$php = array_merge($php, array('-d', 'opcache.enable_cli=1', '-d', 'opcache.file_cache='.Runner::OPCACHE, '-d', 'opcache.file_cache_only=1'));
}

// The arguments codecept's own parser would take as an option's value, which therefore never name a test.
$valueOptions = array();
$command = new \Codeception\Command\Run('run');
foreach ($command->getDefinition()->getOptions() as $option)
{
	if ($option->acceptValue())
	{
		$valueOptions[] = '--'.$option->getName();
		if ($option->getShortcut() !== null)
		{
			$valueOptions[] = '-'.$option->getShortcut();
		}
	}
}

$output = \Codeception\Configuration::outputDir();
$runner = new Runner($shell, $databases, $suite, new Timings($output."timings/$name.json"), array(
	'codecept'      => 'php '.implode(' ', array_map('escapeshellarg', $php)).' vendor/bin/codecept',
	'loops'         => 'php '.escapeshellarg(__FILE__),
	'dump'          => "$root/".$params['db']['dump_path'],
	'base_path'     => (string) getenv('E107_BASE_PATH'),
	'output'        => $output.$name,
	'value_options' => $valueOptions,
));
exit($runner->run($jobs, $args));
