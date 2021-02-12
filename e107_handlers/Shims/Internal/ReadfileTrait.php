<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Shims for PHP internal functions
 * readfile()
 */

namespace e107\Shims\Internal;

trait ReadfileTrait
{
	/**
	 * Outputs a file
	 *
	 * Resilient replacement for PHP internal readfile()
	 *
	 * @see https://github.com/e107inc/e107/issues/3528 Why this method was implemented
	 * @param string $filename The filename being read.
	 * @param bool $use_include_path You can use the optional second parameter and set it to TRUE,
	 *                               if you want to search for the file in the include_path, too.
	 * @param resource $context A context stream resource.
	 * @return int|bool Returns the number of bytes read from the file.
	 *                   If an error occurs, FALSE is returned.
	 */
	public static function readfile($filename, $use_include_path = FALSE, $context = NULL)
	{
		$output = @readfile($filename, $use_include_path, $context);
		if ($output === NULL)
		{
			return self::readfile_alt($filename, $use_include_path, $context);
		}
		return $output;
	}

	/**
	 * Outputs a file
	 *
	 * Alternative implementation using file streams
	 *
	 * @param $filename
	 * @param bool $use_include_path
	 * @param resource $context
	 * @return bool|int
	 */
	public static function readfile_alt($filename, $use_include_path = FALSE, $context = NULL)
	{
		// fopen() silently returns false if there is no context
		if (!is_resource($context)) $context = stream_context_create();

		$handle = @fopen($filename, 'rb', $use_include_path, $context);
		if ($handle === FALSE) return FALSE;
		while (!feof($handle))
		{
			echo(fread($handle, 8192));
		}
		fclose($handle);
		return filesize($filename);
	}
}
