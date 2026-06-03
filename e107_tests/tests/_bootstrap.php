<?php
use Codeception\Util\Autoload;
use Codeception\Configuration;

// PHP 5.6 warns on every date() call when no default timezone is configured,
// and the runner's display_errors=1 means that warning lands on stdout. Once
// stdout has content, e107's session_start() can no longer send the session
// cookie and the suite fails before reaching any test. Set a deterministic
// timezone up front so the date() calls below stay silent. PHP 7+ defaults
// to UTC and was unaffected.
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

// Several PHP versions in the matrix emit warnings or deprecation notices
// the moment Codeception's autoloader pulls in classes that predate their
// stricter contract checks: PHP 7.0 flags the LSP gap between Helper\Base
// and Codeception 4.x's \Codeception\Module, and PHP 8.4 warns whenever a
// typed parameter defaults to null without an explicit "?" prefix (which
// the downgrade pipeline strips for the legacy cells, leaving the modern
// cells running the implicit form). Both messages land on stdout via the
// CI image's display_errors=1, and stdout-before-session_start triggers
// "headers already sent" inside e107's bootstrap. Silence display here;
// Codeception's ErrorHandler subscriber still routes real failures through
// its own reporting channel.
ini_set('display_errors', '0');


class E107TestSuiteBootstrap
{
    const ENABLE_LOGGING = false; // Toggle logging (set to true to enable)

    private $logFile;
    private $pluginsDir;

    public function __construct()
    {
        $this->logFile = codecept_output_dir() . '/bootstrap.log';

        if (self::ENABLE_LOGGING)
        {
            file_put_contents($this->logFile, ""); // Clear log on start
        }

        $this->initialize();
    }

    private function log($message)
    {
        if (self::ENABLE_LOGGING)
        {
            file_put_contents($this->logFile, $message . "\n", FILE_APPEND);
        }
    }

    private function initialize()
    {
        // Log initial environment
        $this->log("Time: " . date(DATE_ATOM));
        $this->log("PHP Version: " . PHP_VERSION);
        $this->log("Root Dir: " . codecept_root_dir());

        // Load core unit tests namespace (e107_tests/tests/unit/)
		Autoload::addNamespace('', codecept_root_dir() . '/tests/unit');
        $this->log("Added core unit namespace: '' => " . codecept_root_dir() . '/tests/unit');

        define('PARAMS_GENERATOR', realpath(codecept_root_dir() . "/lib/config.php"));
        $params = include(PARAMS_GENERATOR);

        $app_path = $params['app_path'] ?: codecept_root_dir() . "/e107";
        if (substr($app_path, 0, 1) !== '/')
        {
            $app_path = codecept_root_dir() . "/$app_path";
        }
        $original_app_path = realpath($app_path);

        // Load PriorityCallbacks early so a GitPreparer worktree can register
        // its deferred cleanup during snapshot().
        include(codecept_root_dir() . "/lib/PriorityCallbacks.php");

        // Ask the preparer where the app runs: in place, or in an isolated
        // git worktree for deploy-based suites. The factory decides which.
        require_once(codecept_root_dir() . "/lib/preparers/PreparerFactory.php");
        $deployer = $params['deployer'] ?? 'local';
        $preparer = PreparerFactory::createForPath($original_app_path, $deployer);
        $effective_app_path = $preparer->getAppPath();

        // APP_PATH points to the prepared tree; all later code uses it.
        define('APP_PATH', $effective_app_path);
        define('PARAMS_SERIALIZED', serialize($params));

        $this->log("App Path: " . APP_PATH);

       
        if (defined('e_PLUGIN'))
        {
            $this->log("e_PLUGIN already defined as: " . e_PLUGIN);
        }
        else
        {
            $this->log("e_PLUGIN not defined yet");
        }

        $this->pluginsDir = realpath(codecept_root_dir() . '/../e107_plugins/');
        $this->log("Plugins Dir: $this->pluginsDir");

        // Load test types
        $this->loadUnitTests();
        $this->loadAcceptanceTests();

        $this->log("e_PLUGIN after initialization: " . (defined('e_PLUGIN') ? e_PLUGIN : 'not defined'));
    }

    private function loadUnitTests()
    {
        $pluginUnitDirs = [];

        if ($this->pluginsDir && is_dir($this->pluginsDir))
        {
            $unitDirs = glob($this->pluginsDir . '/*/tests/unit', GLOB_ONLYDIR);
            $separator = DIRECTORY_SEPARATOR;
            $unitGlobPattern = str_replace('/', $separator, $this->pluginsDir . '/*/tests/unit');
            $this->log("Unit Glob Pattern: $unitGlobPattern");
            $this->log("Found Unit Dirs: " . (empty($unitDirs) ? 'None' : ''));
            if (!empty($unitDirs))
            {
                foreach ($unitDirs as $dir)
                {
                    $this->log("\t" . $dir);
                }
            }
            foreach ($unitDirs as $testDir)
            {
                $pluginName = basename(dirname(dirname($testDir)));
                $relativePath = '../e107_plugins/' . $pluginName . '/tests/unit';
                $pluginUnitDirs[] = $relativePath;
                $namespace = "E107\\Plugins\\" . ucfirst($pluginName) . "\\Tests\\Unit";
                Autoload::addNamespace($namespace, $testDir);
                $this->log("Added unit namespace: $namespace => $testDir");
            }
        }
        else
        {
            $this->log("Plugins Dir not found or not a directory");
        }
        $this->log("Included Unit Dirs: " . json_encode($pluginUnitDirs, JSON_PRETTY_PRINT));
    }

    private function loadAcceptanceTests()
    {
        $pluginAcceptanceDirs = [];

        if ($this->pluginsDir && is_dir($this->pluginsDir))
        {
            $acceptanceDirs = glob($this->pluginsDir . '/*/tests/acceptance', GLOB_ONLYDIR);
            $separator = DIRECTORY_SEPARATOR;
            $acceptanceGlobPattern = str_replace('/', $separator, $this->pluginsDir . '/*/tests/acceptance');
            $this->log("Acceptance Glob Pattern: $acceptanceGlobPattern");
            $this->log("Found Acceptance Dirs: " . (empty($acceptanceDirs) ? 'None' : ''));
            if (!empty($acceptanceDirs))
            {
                foreach ($acceptanceDirs as $dir)
                {
                    $this->log("\t" . $dir);
                }
            }
            foreach ($acceptanceDirs as $testDir)
            {
                $pluginName = basename(dirname(dirname($testDir)));
                $relativePath = '../e107_plugins/' . $pluginName . '/tests/acceptance';
                $pluginAcceptanceDirs[] = $relativePath;
                $namespace = "E107\\Plugins\\" . ucfirst($pluginName) . "\\Tests\\Acceptance";
                Autoload::addNamespace($namespace, $testDir);
                $this->log("Added acceptance namespace: $namespace => $testDir");
            }
        }
        else
        {
            $this->log("Plugins Dir not found or not a directory");
        }
        $this->log("Included Acceptance Dirs: " . json_encode($pluginAcceptanceDirs, JSON_PRETTY_PRINT));
    }
}


if(!function_exists('dbg'))
{
	/**
	 * Custom Debug To Console function  - Part of _bootstrap.php
	 *
	 * @param mixed $data
	 * @return void
	 */
	function dbg($data)
	{

		$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
		$line = isset($bt[0]['line']) ? $bt[0]['line'] : '?';

		if(is_array($data) || is_object($data))
		{
			$output = print_r($data, true) . PHP_EOL;
		}
		elseif(is_bool($data))
		{
			$output = ($data ? 'true' : 'false') . PHP_EOL;
		}
		elseif(is_null($data))
		{
			$output = 'null' . PHP_EOL;
		}
		elseif(is_string($data))
		{
			$output = "\"" . $data . "\"" . PHP_EOL; // wrap string in double quotes
		}
		else
		{
			$output = $data . PHP_EOL; // other scalars (ints, floats) as-is
		}

		fwrite(STDERR, "DEBUG (Line: $line): " . $output);
	}
}


new E107TestSuiteBootstrap;