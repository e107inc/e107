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
		$verFileVersion = array_shift(explode(" ", $verFileVersion));
		$version = preg_replace("/^v/", "", $gitVersion);
		$versionSplit = explode("-", $version);
		if (count($versionSplit) > 1)
		{
			if (version_compare($verFileVersion, $versionSplit[0], '>')) $versionSplit[0] = $verFileVersion;
			$versionSplit[0] .= "dev";
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