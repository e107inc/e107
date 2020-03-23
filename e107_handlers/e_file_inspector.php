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
    protected $currentVersion;
    private $validatedBitmask;

    protected $defaultDirsCache;
    protected $customDirsCache;

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
        $absolutePath = realpath(e_BASE . $path);
        $dbChecksum = $this->getChecksum($path, $version);
        $actualChecksum = $dbChecksum ? $this->checksumPath($absolutePath) : null;

        if ($dbChecksum !== false) $bits |= self::VALIDATED_RELEVANCE;
        if (file_exists($absolutePath)) $bits |= self::VALIDATED_PRESENCE;
        if (!$this->isInsecure($path)) $bits |= self::VALIDATED_SECURITY;
        if ($this->isDeterminable($absolutePath)) $bits |= self::VALIDATED_DETERMINABLE;
        if ($actualChecksum === $dbChecksum) $bits |= self::VALIDATED_UPTODATE;

        foreach ($this->getChecksums($path) as $dbVersion => $dbChecksum)
        {
            if ($dbChecksum === $actualChecksum) $bits |= self::VALIDATED_HASH;
        }

        if ($bits + self::VALIDATED === $this->getValidatedBitmask()) $bits |= self::VALIDATED;

        return $bits;
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
        # TODO
        return false;
    }

    protected function pathToDefaultPath($path)
    {
        if (!$this->customDirsCache)
        {
            $this->defaultDirsCache = e107::getInstance()->defaultDirs();
            $customDirs = e107::getInstance()->e107_dirs ? e107::getInstance()->e107_dirs : [];
            $this->customDirsCache = array_diff_assoc($customDirs, $this->defaultDirsCache);
        }
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
        return is_file($absolutePath) && is_readable($absolutePath);
    }
}