<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

require_once("e_file_inspector_interface.php");

/**
 * File Inspector
 *
 * Tool to validate application files for consistency by comparing hashes of files with those in a database
 */
abstract class e_file_inspector implements e_file_inspector_interface
{
    protected $database;
    protected $currentVersion;
    private $validatedBitmask;

    protected $defaultDirsCache;
    protected $customDirsCache;
    private $undeterminable = array();

    // FIXME: Better place for the insecure file list
    public $insecureFiles = [
        e_ADMIN . "ad_links.php",
        e_PLUGIN . "tinymce4/e_meta.php",
        e_THEME . "bootstrap3/css/bootstrap_dark.css",
        e_PLUGIN . "search_menu/languages/English.php",
        e_LANGUAGEDIR . e_LANGUAGE . "/lan_parser_functions.php",
        e_LANGUAGEDIR . e_LANGUAGE . "/admin/help/theme.php",
        e_HANDLER . "np_class.php",
        e_CORE . "shortcodes/single/user_extended.sc",
        e_ADMIN . "download.php",
        e_PLUGIN . "banner/config.php",
        e_PLUGIN . "forum/newforumposts_menu_config.php",
        e_PLUGIN . "forum/e_latest.php",
        e_PLUGIN . "forum/e_status.php",
        e_PLUGIN . "forum/forum_post_shortcodes.php",
        e_PLUGIN . "forum/forum_shortcodes.php",
        e_PLUGIN . "forum/forum_update_check.php",
        e_PLUGIN . "online_extended_menu/online_extended_menu.php",
        e_PLUGIN . "online_extended_menu/images/user.png",
        e_PLUGIN . "online_extended_menu/languages/English.php",
        e_PLUGIN . "pm/sendpm.sc",
        e_PLUGIN . "pm/shortcodes/",
        e_PLUGIN . "social/e_header.php",
    ];

    private $existingInsecureFiles = array();
    private $existingInsecureDirectories = array();

    /**
     * e_file_inspector constructor
     * @param string $database The database from which integrity data may be read or to which integrity data may be
     *                         written.  This should be an URL or absolute file path for most implementations.
     */
    public function __construct($database)
    {
        $this->database = $database;

        $this->checkDeprecatedFilesLog();

        $appRoot = e107::getInstance()->file_path;
        $this->undeterminable = array_map(function ($path)
        {
            return realpath($path) ? realpath($path) : $path;
        }, [
                $appRoot . "e107_config.php",
                $appRoot . e107::getFolder('system_base') . "core_image.phar",
            ]
        );
        $this->existingInsecureFiles = array_filter($this->insecureFiles, function ($path)
        {
            return is_file($path);
        });
        $this->existingInsecureFiles = array_map('realpath', $this->existingInsecureFiles);
        $this->existingInsecureDirectories = array_filter($this->insecureFiles, function ($path)
        {
            return is_dir($path);
        });
        $this->existingInsecureDirectories = array_map('realpath', $this->existingInsecureDirectories);
    }

    /**
     * Populate insecureFiles list if deprecatedFiles.log found.
     * @return |null
     */
    private function checkDeprecatedFilesLog()
    {
        $log = e_LOG.'fileinspector/deprecatedFiles.log';

        if(!file_exists($log))
        {
            return null;
        }

        $content = file_get_contents($log);

        if(empty($content))
        {
            return null;
        }

       $tmp = explode("\n", $content);
       $this->insecureFiles = [];
       foreach($tmp as $line)
       {
            if(empty($line))
            {
                continue;
            }

            $this->insecureFiles[] = e_BASE.$line;
       }


    }

    /**
     * Convert validation code to string.
     * @param $validationCode
     * @return string
     */
    public static function getStatusForValidationCode($validationCode)
    {
        $status = 'unknown';
        if ($validationCode & self::VALIDATED)
            $status = 'check';
        elseif (!($validationCode & self::VALIDATED_FILE_EXISTS))
            $status = 'missing';
        elseif (!($validationCode & self::VALIDATED_FILE_SECURITY))
            $status = 'warning';
        elseif (!($validationCode & self::VALIDATED_PATH_KNOWN))
            $status = 'unknown';
        elseif (!($validationCode & self::VALIDATED_PATH_VERSION))
            $status = 'old';
        elseif (!($validationCode & self::VALIDATED_HASH_CALCULABLE))
            $status = 'uncalc';
        elseif (!($validationCode & self::VALIDATED_HASH_CURRENT))
            if ($validationCode & self::VALIDATED_HASH_EXISTS)
                $status = 'old';
            else
                $status = 'fail';
        return $status;
    }

    /**
     * Prepare the provided database for reading or writing
     *
     * Should tolerate a non-existent database and try to create it if a write operation is executed.
     *
     * @return void
     */
    abstract public function loadDatabase();

    /**
     * Check the integrity of the provided path
     *
     * @param $path string Relative path of the file to look up
     * @param $version string The desired software release to match.
     *                        Leave blank for the current version.
     *                        Do not prepend the version number with "v".
     * @return int Validation code (see the constants of this class)
     */
    public function validate($path, $version = null)
    {
        if ($version === null) $version = $this->getCurrentVersion();

        $bits = 0x0;
        $absolutePath = $this->relativePathToAbsolutePath($path);
        $dbChecksums = $this->getChecksums($path);
        $dbChecksum = $this->getChecksum($path, $version);
        $actualChecksum = !empty($dbChecksums) ? $this->checksumPath($absolutePath) : null;

        if (!empty($dbChecksums)) $bits |= self::VALIDATED_PATH_KNOWN;
        if ($dbChecksum !== false) $bits |= self::VALIDATED_PATH_VERSION;
        if (file_exists($absolutePath)) $bits |= self::VALIDATED_FILE_EXISTS;
        if (!$this->isInsecure($path)) $bits |= self::VALIDATED_FILE_SECURITY;
        if ($this->isDeterminable($absolutePath)) $bits |= self::VALIDATED_HASH_CALCULABLE;
        if ($actualChecksum === $dbChecksum) $bits |= self::VALIDATED_HASH_CURRENT;

        foreach ($dbChecksums as $dbChecksum)
        {
            if ($dbChecksum === $actualChecksum) $bits |= self::VALIDATED_HASH_EXISTS;
        }

        if ($bits + self::VALIDATED === $this->getValidatedBitmask()) $bits |= self::VALIDATED;

        $this->log($path, $bits);

        return $bits;
    }

     /**
     * Log old file paths. (may be expanded to other types in future)
     *
     * @param string $relativePath
     * @param int $status
     * @return null
     */
    private function log($relativePath, $status)
    {
        if(empty($relativePath) || self::getStatusForValidationCode($status) !== 'old') // deprecated-file status
        {
            return null;
        }

        $message = $relativePath."\n";

        $logPath = e_LOG."fileinspector/";

        if(!is_dir($logPath))
        {
            mkdir($logPath, 0775);
        }

        file_put_contents($logPath."deprecatedFiles.log", $message, FILE_APPEND);

        return null;
    }

    /**
     * Get the file integrity hash for the provided path and version
     *
     * @param $path string Relative path of the file to look up
     * @param $version string The software release version corresponding to the file hash.
     *                        Leave blank for the current version.
     *                        Do not prepend the version number with "v".
     * @return string|bool The database hash for the path and version specified. FALSE if the record does not exist.
     */
    public function getChecksum($path, $version = null)
    {
        if ($version === null) $version = $this->getCurrentVersion();
        $checksums = $this->getChecksums($path);
        return isset($checksums[$version]) ? $checksums[$version] : false;
    }

    /**
     * Calculate the hash of a path to compare with the hash database
     *
     * @param $absolutePath string Absolute path of the file to hash
     * @return string|bool The actual hash for the path. FALSE if the hash was incalculable.
     */
    public function checksumPath($absolutePath)
    {
        if (!$this->isDeterminable($absolutePath)) return false;

        return $this->checksum(file_get_contents($absolutePath));
    }

    /**
     * Calculate the hash of a string, which would be used to compare with the hash database
     *
     * @param $content string Full content to hash
     * @return string
     */
    public function checksum($content)
    {
        return md5(str_replace(array(chr(13), chr(10)), "", $content));
    }

    /**
     * @inheritDoc
     */
    public function getVersions($path)
    {
        return array_keys($this->getChecksums($path));
    }

    /**
     * @inheritDoc
     */
    public function getCurrentVersion()
    {
        if ($this->currentVersion) return $this->currentVersion;

        $checksums = $this->getChecksums("index.php");
        $versions = array_keys($checksums);
        usort($versions, 'version_compare');
        return $this->currentVersion = array_pop($versions);
    }

    /**
     * Get the matching version of the provided path
     *
     * Useful for looking up the versions of old files that no longer exist in the latest image
     *
     * @param $path string Relative path of the file to look up
     * @return string|bool PHP-standardized version of the file. FALSE if there is no match.
     */
    public function getVersion($path)
    {
        $actualChecksum = $this->checksumPath($path);
        foreach ($this->getChecksums($path) as $dbVersion => $dbChecksum)
        {
            if ($actualChecksum === $dbChecksum) return $dbVersion;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isInsecure($path)
    {
        $absolutePath = $this->relativePathToAbsolutePath($path);
        if (in_array($absolutePath, $this->existingInsecureFiles)) return true;
        foreach ($this->existingInsecureDirectories as $existingInsecureDirectory)
        {
            $existingInsecureDirectory .= '/';
            if (substr($absolutePath, 0, strlen($existingInsecureDirectory)) === $existingInsecureDirectory) return true;
        }
        return false;
    }

    /**
     * Convert a custom site path to a default path
     * @param string $path Custom path
     * @return string
     */
    public function customPathToDefaultPath($path)
    {
        if (!is_array($this->customDirsCache)) $this->populateDirsCache();
        foreach ($this->customDirsCache as $dirType => $customDir)
        {
            if (!isset($this->defaultDirsCache[$dirType])) continue;

            $defaultDir = $this->defaultDirsCache[$dirType];
            if ($customDir === $defaultDir) continue;

            if (substr($path, 0, strlen($customDir)) === $customDir)
                $path = $defaultDir . substr($path, strlen($customDir));
        }
        return $path;
    }

    public function defaultPathToCustomPath($path)
    {
        if (!is_array($this->customDirsCache)) $this->populateDirsCache();
        foreach ($this->customDirsCache as $dirType => $customDir)
        {
            if (!isset($this->defaultDirsCache[$dirType])) continue;

            $defaultDir = $this->defaultDirsCache[$dirType];
            if ($customDir === $defaultDir) continue;

            if (substr($path, 0, strlen($defaultDir)) === $defaultDir)
                $path = $customDir . substr($path, strlen($defaultDir));
        }
        return $path;
    }

    private function getValidatedBitmask()
    {
        if ($this->validatedBitmask !== null) return $this->validatedBitmask;
        $constants = (new ReflectionClass(self::class))->getConstants();
        $validated_constants = array_filter($constants, function ($key)
        {
            $str = 'VALIDATED_';
            return substr($key, 0, strlen($str)) === $str;
        }, ARRAY_FILTER_USE_KEY);

        $this->validatedBitmask = (max($validated_constants) << 0x1) - 0x1;
        return $this->validatedBitmask;
    }

    /**
     * @param $absolutePath
     * @return bool
     */
    private function isDeterminable($absolutePath)
    {
        return is_file($absolutePath) && is_readable($absolutePath) && !in_array($absolutePath, $this->undeterminable);
    }

    protected function populateDirsCache()
    {
        $this->defaultDirsCache = e107::getInstance()->defaultDirs();
        $customDirs = e107::getInstance()->e107_dirs ? e107::getInstance()->e107_dirs : [];
        $this->customDirsCache = array_diff_assoc($customDirs, $this->defaultDirsCache);
    }

    /**
     * @param $path
     * @return false|string
     */
    private function relativePathToAbsolutePath($path)
    {
        return realpath(e_BASE . $path);
    }
}