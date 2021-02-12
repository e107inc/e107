<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

class OsHelper
{
	const REGEX_MATCH_GIT_DESCRIBE_TAGS = "-[0-9]+-g[0-9a-f]+";

	/**
	 * @param string $command The command to run
	 * @param string $stdout Reference to the STDOUT output as a string
	 * @param string $stderr Reference to the STDERR output as a string
	 * @return int Return code of the command that was run
	 */
	public static function run($command, &$stdout = "", &$stderr = "")
	{
		$stdout = $stderr = "";
		$descriptorspec = [
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];
		$pipes = [];
		$resource = proc_open($command, $descriptorspec, $pipes);
		$stdout .= stream_get_contents($pipes[1]);
		$stderr .= stream_get_contents($pipes[2]);
		foreach ($pipes as $pipe)
		{
			fclose($pipe);
		}
		return proc_close($resource);
	}

	public static function runValidated($command, &$stdout = "", &$stderr = "")
	{
		$rc = OsHelper::run($command, $stdout, $stderr);
		if ($rc != 0)
		{
			throw new RuntimeException(
				"Error while running command (rc=$rc): " . $command . PHP_EOL .
				"========== STDOUT ==========" . PHP_EOL .
				$stdout . PHP_EOL .
				"========== STDERR ==========" . PHP_EOL .
				$stderr . PHP_EOL
			);
		}
		return $rc;
	}

	public static function gitVersionToPhpVersion($gitVersion, $verFileVersion = "0")
	{
		$verFileVersion = explode(" ", $verFileVersion);
		$verFileVersion = array_shift($verFileVersion);
		$version = preg_replace("/^v/", "", $gitVersion);
		$versionSplit = explode("-", $version);
		$matchGitDescribeTags = self::REGEX_MATCH_GIT_DESCRIBE_TAGS;
		if (preg_match("/{$matchGitDescribeTags}$/", $version))
		{
			$increment = 1;
			if (version_compare($verFileVersion, $version, '>'))
			{
				$increment = 0;
				$versionSplit[0] = $verFileVersion;
			}
			$version = implode("-", $versionSplit);
			return preg_replace_callback("/(.*\.)([0-9]+)([^.]*)({$matchGitDescribeTags})$/",
				function ($matches) use ($increment)
				{
					return $matches[1] . ($matches[2] + $increment) . "dev" . $matches[4];
				}, $version);
		}
		return implode("-", $versionSplit);
	}

	public static function getVerFileVersion($verFilePath)
	{
		$verFileTokens = token_get_all(file_get_contents($verFilePath));
		$nextConstantEncapsedStringIsVersion = false;
		foreach ($verFileTokens as $verFileToken)
		{
			if (!isset($verFileToken[1])) continue;
			$token = $verFileToken[0];
			$value = trim($verFileToken[1], "'\"");

			if ($token === T_CONSTANT_ENCAPSED_STRING)
			{
				if ($nextConstantEncapsedStringIsVersion)
				{
					return $value;
				}
				if ($value === 'e107_version') $nextConstantEncapsedStringIsVersion = true;
			}
		}
		return '0';
	}
}