<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

interface e_file_inspector_interface
{
    /**
     * TRUE: All validations pass for the provided file.
     * FALSE: One or more validations failed for the provided file.
     */
    const VALIDATED = 1 << 0;
    /**
     * TRUE: The file path is known in this database, regardless of version.
     * FALSE: The file path is not in this database.
     */
    const VALIDATED_PATH_KNOWN = 1 << 1;
    /**
     * TRUE: The file path and specified version have a hash in this database.
     * FALSE: There is no hash for the file path and specified version.
     */
    const VALIDATED_PATH_VERSION = 1 << 2;
    /**
     * TRUE: The file exists.
     * FALSE: The file doesn't exist.
     */
    const VALIDATED_FILE_EXISTS = 1 << 3;
    /**
     * TRUE: The file's hash matches any known version.
     * FALSE: The file's hash does not match any known versions.
     */
    const VALIDATED_HASH_EXISTS = 1 << 4;
    /**
     * TRUE: The file's hash matches the specified version.
     * FALSE: The file's hash matches a newer or older version than the one specified.
     */
    const VALIDATED_HASH_CURRENT = 1 << 5;
    /**
     * TRUE: The file hash is calculable.
     * FALSE: The file hash is not calculable (e.g. the core image itself, a user config file, a nonexistent file).
     */
    const VALIDATED_HASH_CALCULABLE = 1 << 6;
    /**
     * TRUE: The file is not known to be insecure.
     * FALSE: The file should be deleted due to security concerns.
     */
    const VALIDATED_FILE_SECURITY = 1 << 7;

    /**
     * Return an Iterator that can enumerate every path in the image database
     *
     * @param $version string|null Provide a PHP-standardized version to limit the paths to that version
     * @return Iterator
     */
    public function getPathIterator($version = null);

    /**
     * Get all the known file integrity hashes for the provided path
     *
     * @param $path string Relative path of the file to look up
     * @return array Associative array where the keys are the PHP-standardized versions and the values are the checksums
     *               for those versions.
     */
    public function getChecksums($path);

    /**
     * List of versions of the provided path for which the database has hashes
     *
     * @param $path string Relative path of the file to look up
     * @return array PHP-standardized versions. Empty if there are none.
     */
    public function getVersions($path);

    /**
     * Get the version of the software that goes with this image database.
     *
     * This database SHOULD contain file integrity hashes for this software version.
     * This database MAY contain file integrity hashes for older versions of this software.
     *
     * @return string PHP-standardized version
     */
    public function getCurrentVersion();

    /**
     * Check if the file is insecure
     *
     * @param $path string Relative path of the file to look up
     * @return bool TRUE if the file should be deleted due to known security flaws; FALSE otherwise
     */
    public function isInsecure($path);
}