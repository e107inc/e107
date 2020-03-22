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
		$absolutePath = realpath($path);
		$actualChecksum = $this->checksumPath($absolutePath);
		$dbChecksum = $this->getChecksum($path, $version);

		if ($dbChecksum === false) return self::VALIDATION_IGNORE;
		if (!file_exists($absolutePath)) return self::VALIDATION_MISSING;
		if ($this->isInsecure($path)) return self::VALIDATION_INSECURE;
		if ($actualChecksum === false) return self::VALIDATION_INCALCULABLE;
		if ($actualChecksum === $dbChecksum) return self::VALIDATION_PASS;

		foreach ($this->getChecksums($path) as $dbVersion => $dbChecksum)
		{
			if (version_compare($dbVersion, $version, ">=")) continue;

			if ($dbChecksum === $actualChecksum) return self::VALIDATION_OLD;
		}

		return self::VALIDATION_FAIL;
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
		if (!is_file($absolutePath) || !is_readable($absolutePath)) return false;

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
		return md5(str_replace(array(chr(13),chr(10)), "",  $content));
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
        $checksums = $this->getChecksums("index.php");
        $versions = array_keys($checksums);
        usort($versions, 'version_compare');
        return array_pop($versions);
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
}