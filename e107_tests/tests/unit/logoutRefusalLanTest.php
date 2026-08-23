<?php

/**
 * A refusal message that is itself a fatal is worse than no refusal.
 *
 * Every string this branch adds is defined in one English language file and
 * nowhere else, and a site running another language may never reach it:
 * release/v2.3.x merges no English behind the language file class2.php
 * includes, and master's per-constant substitution is switched off by the
 * noLanguageSubs preference. Read as a bare constant, the missing string is
 * handed to the administrator as its own name up to PHP 7 and throws Error on
 * PHP 8, so the guard that was meant to refuse a forged logout answers with a
 * 500 instead.
 *
 * defset() with a plain English fallback is what the rest of core does about
 * that, and the reason this needs a test is that a bare dereference looks
 * entirely correct on the English install every developer runs. Nothing may
 * spell one of these names outside a string literal, which is where defset(),
 * defined() and the language file itself all put it.
 *
 * @see class2.php  logout_refused(), the sibling guard, hedged the same way
 * @see e107_core/controllers/system/xup.php  the other one
 */
class logoutRefusalLanTest extends \Codeception\Test\Unit
{
	/**
	 * The refusal strings this branch introduces, each defined in exactly one
	 * English language file.
	 *
	 * @var array
	 */
	private static $refusalStrings = array(
		'LAN_LOGOUT_REFUSED_TOKEN_MISSING',
		'LAN_USET_DELETE_LINK_INVALID',
	);

	/**
	 * Top-level directories of the checkout that ship no core code.
	 *
	 * @var array
	 */
	private static $skippedDirectories = array('.claude', '.git', 'e107_tests', 'node_modules', 'vendor');

	public function testNoRefusalStringIsDereferencedWithoutAFallback()
	{
		$offenders = array();

		foreach($this->shippedPhpFiles() as $path)
		{
			$offenders = array_merge($offenders, $this->bareDereferences($path));
		}

		sort($offenders);

		$this->assertSame(
			array(),
			$offenders,
			"These read a refusal string as a bare constant.\n"
			."Write defset('CONSTANT', 'plain English fallback.') instead, the way\n"
			."class2.php and e107_core/controllers/system/xup.php do. Each of these\n"
			."strings is defined in one English language file and nowhere else, so on\n"
			."a site running another language the refusal is rendered as its own name\n"
			."up to PHP 7 and throws Error on PHP 8."
		);
	}

	/**
	 * @param string $path
	 * @return array of "relative/path.php:12 reads CONSTANT"
	 */
	private function bareDereferences($path)
	{
		$found = array();
		$source = file_get_contents($path);

		if(!$this->mentionsARefusalString($source))
		{
			return $found;
		}

		foreach(token_get_all($source) as $token)
		{
			if(!is_array($token) || $token[0] !== T_STRING)
			{
				continue;
			}

			if(in_array($token[1], self::$refusalStrings, true))
			{
				$found[] = $this->relativePath($path).':'.$token[2].' reads '.$token[1];
			}
		}

		return $found;
	}

	/**
	 * @param string $source
	 * @return bool whether the file is worth handing to the tokenizer
	 */
	private function mentionsARefusalString($source)
	{
		foreach(self::$refusalStrings as $name)
		{
			if(strpos($source, $name) !== false)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return array of absolute paths
	 */
	private function shippedPhpFiles()
	{
		$files = array();

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($this->docRoot(), FilesystemIterator::SKIP_DOTS)
		);

		foreach($iterator as $file)
		{
			$path = $file->getPathname();

			if(substr($path, -4) !== '.php')
			{
				continue;
			}

			$relative = explode('/', $this->relativePath($path));

			if(in_array($relative[0], self::$skippedDirectories, true))
			{
				continue;
			}

			$files[] = $path;
		}

		return $files;
	}

	/**
	 * @return string checkout root, with a trailing separator
	 */
	private function docRoot()
	{
		return dirname(rtrim(codecept_root_dir(), '/')).'/';
	}

	/**
	 * @param string $path
	 * @return string
	 */
	private function relativePath($path)
	{
		$root = $this->docRoot();

		return strpos($path, $root) === 0 ? (string) substr($path, strlen($root)) : $path;
	}
}
