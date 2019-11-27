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
}