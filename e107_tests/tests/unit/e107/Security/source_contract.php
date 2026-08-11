<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Security;

/**
 * Reads the source of the security handlers so a test can assert about it.
 *
 * A test that reads source is normally a bad test, and every one built on
 * this class carries the reason it had to be. They all share a shape: the
 * property being defended is real, an attacker can exploit its absence, and
 * two implementations that differ on it are indistinguishable from outside
 * the class. Comparing a tag with != instead of hash_equals() leaks timing
 * and nothing else; authenticating a hard-coded header instead of the
 * received one is invisible while the allow-list holds one entry. Mutation
 * runs over this tree found those edits, made the code wrong, and left the
 * suite entirely green.
 *
 * Nothing here asserts about behaviour. Where a behavioural test exists it
 * is the one to write.
 */
class SourceContract
{
	/**
	 * The source text of one method, braces and all.
	 *
	 * @param string $class fully qualified, already loaded
	 * @param string $method
	 * @return string
	 */
	public static function methodBody($class, $method)
	{
		$reflection = new \ReflectionMethod($class, $method);
        if (PHP_VERSION_ID < 80100) {
            $reflection->setAccessible(true);
        }
		$lines = file($reflection->getFileName());
		$start = $reflection->getStartLine() - 1;

		return implode('', array_slice($lines, $start, $reflection->getEndLine() - $start));
	}

	/**
	 * Every string literal in a file, comments and identifiers excluded.
	 *
	 * Used to ask what a file names rather than what it says about itself.
	 * The docblocks in this tree discuss AES-GCM at length and must go on
	 * doing so; what may never appear is a GCM cipher name in a position
	 * where PHP would pass it to OpenSSL.
	 *
	 * @param string $path
	 * @return array of strings
	 */
	public static function stringLiterals($path)
	{
		$literals = array();
		$wanted = array(T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE);

		foreach(token_get_all(file_get_contents($path)) as $token)
		{
			if(is_array($token) && in_array($token[0], $wanted, true))
			{
				$literals[] = $token[1];
			}
		}

		return $literals;
	}

	/**
	 * Every .php file under a directory, sorted.
	 *
	 * @param string $directory
	 * @return array of absolute paths
	 */
	public static function phpFiles($directory)
	{
		$found = array();
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
		);

		foreach($iterator as $file)
		{
			if($file->isFile() && strtolower($file->getExtension()) === 'php')
			{
				$found[] = $file->getPathname();
			}
		}

		sort($found);

		return $found;
	}
}
