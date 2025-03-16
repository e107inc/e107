<?php
use Codeception\Util\Autoload;
use Codeception\Configuration;

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
        Autoload::addNamespace('Tests\Unit', codecept_root_dir() . '/tests/unit');
        $this->log("Added core unit namespace: Tests\\Unit => " . codecept_root_dir() . '/tests/unit');

        // Load parameters
        define('PARAMS_GENERATOR', realpath(codecept_root_dir() . "/lib/config.php"));
        $params = include(PARAMS_GENERATOR);

        // Define APP_PATH
        $app_path = $params['app_path'] ?: codecept_root_dir() . "/e107";
        if (substr($app_path, 0, 1) !== '/')
        {
            $app_path = codecept_root_dir() . "/$app_path";
        }
        define('APP_PATH', realpath($app_path));
        define('PARAMS_SERIALIZED', serialize($params));

        // Log App Path after definition
        $this->log("App Path: " . APP_PATH);

        // e_PLUGIN status
        if (defined('e_PLUGIN'))
        {
            $this->log("e_PLUGIN already defined as: " . e_PLUGIN);
        }
        else
        {
            $this->log("e_PLUGIN not defined yet");
        }

        // Set plugins directory once
        $this->pluginsDir = realpath(codecept_root_dir() . '/../e107_plugins/');
        $this->log("Plugins Dir: $this->pluginsDir");

        // Load test types
        $this->loadUnitTests();
        $this->loadAcceptanceTests();

        // Include required e107 file
        include(codecept_root_dir() . "/lib/PriorityCallbacks.php");

        $this->log("e_PLUGIN after initialization: " . (defined('e_PLUGIN') ? e_PLUGIN : 'not defined'));
    }

    private function loadUnitTests()
    {
        $pluginUnitDirs = [];

        if ($this->pluginsDir && is_dir($this->pluginsDir))
        {
            $unitDirs = glob($this->pluginsDir . '/*/tests/unit', GLOB_ONLYDIR);
            $separator = DIRECTORY_SEPARATOR; // \ on Windows, / on Linux
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
                $pluginName = basename(dirname($testDir, 2)); // Two levels up from /unit
                $relativePath = '../e107_plugins/' . $pluginName . '/tests/unit';
                $pluginUnitDirs[] = $relativePath;
                Autoload::addNamespace("Tests\\Unit\\" . ucfirst($pluginName), $testDir);
                $this->log("Added unit namespace: Tests\\Unit\\" . ucfirst($pluginName) . " => $testDir");
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
            $separator = DIRECTORY_SEPARATOR; // \ on Windows, / on Linux
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
                $pluginName = basename(dirname($testDir, 2));
                $relativePath = '../e107_plugins/' . $pluginName . '/tests/acceptance';
                $pluginAcceptanceDirs[] = $relativePath;
                Autoload::addNamespace("Tests\\Acceptance\\" . ucfirst($pluginName), $testDir);
                $this->log("Added acceptance namespace: Tests\\Acceptance\\" . ucfirst($pluginName) . " => $testDir");
            }
        }
        else
        {
            $this->log("Plugins Dir not found or not a directory");
        }
        $this->log("Included Acceptance Dirs: " . json_encode($pluginAcceptanceDirs, JSON_PRETTY_PRINT));
    }
}


new E107TestSuiteBootstrap;