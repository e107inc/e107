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
		self::println("Wrote file \"$relative_path\" to deployed test location");
	}
}