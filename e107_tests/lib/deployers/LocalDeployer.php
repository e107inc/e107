<?php

class LocalDeployer extends NoopDeployer
{
	public function unlinkAppFile($relative_path)
	{
		self::println("Deleting file \"$relative_path\" from deployed test location…");
		if (file_exists(APP_PATH."/$relative_path"))
		{
			unlink(APP_PATH."/$relative_path");
			self::println("Deleted file \"$relative_path\" from deployed test location");
		}
		else
		{
			self::println("No such file to delete: \"$relative_path\"");
		}
	}

	public function writeAppFile($relative_path, $contents)
	{
		self::println("Writing file \"$relative_path\" to deployed test location…");
		$target = APP_PATH."/$relative_path";
		$dir = dirname($target);
		if (!is_dir($dir))
		{
			mkdir($dir, 0755, true);
		}
		if (file_put_contents($target, $contents) === false)
		{
			throw new RuntimeException("Failed to write \"$relative_path\" to deployed test location");
		}
		// The web container runs as a different user than this host-side runner.
		// Make the file world-writable so the app can manage files it owns in
		// production (e.g. e107_config.php written by a same-user installer).
		@chmod($target, 0666);
		self::println("Wrote file \"$relative_path\" to deployed test location");
	}

	public function removeAppPaths(array $relative_paths)
	{
		foreach ($relative_paths as $relative_path)
		{
			self::assertPathInsideApp($relative_path);
			$target = APP_PATH."/$relative_path";
			if (!file_exists($target) && !is_link($target))
			{
				continue;
			}
			self::println("Removing \"$relative_path\" from deployed test location…");
			self::removeRecursively($target);
		}
	}

	/**
	 * Failures are swallowed: the caller is housekeeping, and a path this
	 * process cannot remove is a warning, not a reason to stop a test run.
	 *
	 * @param string $path absolute path
	 * @return void
	 */
	private static function removeRecursively($path)
	{
		if (is_dir($path) && !is_link($path))
		{
			foreach (scandir($path) as $entry)
			{
				if ($entry === '.' || $entry === '..')
				{
					continue;
				}
				self::removeRecursively("$path/$entry");
			}
			@rmdir($path);
			return;
		}
		@unlink($path);
	}
}