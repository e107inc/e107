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
	 * The file is present, and its hash matches the specified version.
	 */
	const VALIDATION_PASS = 1;
	/**
	 * The file is present, but the hash does not match the specified version. VALIDATION_OLD takes precedence.
	 */
	const VALIDATION_FAIL = 2;
	/**
	 * The file is absent, but a hash exists for the specified version.
	 */
	const VALIDATION_MISSING = 3;
	/**
	 * The file is present, and its hash matches a version older than the specified version.
	 */
	const VALIDATION_OLD = 4;
	/**
	 * A hash cannot be determined for the provided file.
	 */
	const VALIDATION_INCALCULABLE = 5;
	/**
	 * The file is present, but it should be deleted due to security concerns
	 */
	const VALIDATION_INSECURE = 6;
	/**
	 * The file, present or absent, is not in this database.
	 */
	const VALIDATION_IGNORE = 7;

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
	 * Check if the file is insecure
	 *
	 * @param $path string Relative path of the file to look up
	 * @return bool TRUE if the file should be deleted due to known security flaws; FALSE otherwise
	 */
	public function isInsecure($path);
}