<?php

class E107Preparer implements Preparer
{
	const TEST_HASH = '000000test'; // see e107_config.php

	public function snapshot()
	{
		return $this->deleteHashDirs();
	}

	public function rollback()
	{
		return $this->deleteHashDirs();
	}

	protected function deleteHashDirs()
	{
		$system = APP_PATH."/e107_system/".self::TEST_HASH;
		$this->deleteDir($system);

		$media = APP_PATH."/e107_media/".self::TEST_HASH;
		$this->deleteDir($media);

		if(is_dir($system))
		{
			throw new Exception(get_class() . " couldn't delete ".$system);
		}

	}

	private function deleteDir($dirPath)
	{
		codecept_debug(get_class() . ' is deleting '.escapeshellarg($dirPath).'…');

		if(!is_dir($dirPath))
		{
			return null;
		}

		$files = scandir($dirPath);

		foreach($files as $file)
		{
			if ($file == "." || $file == "..") continue;

			if(is_dir("$dirPath/$file"))
			{
				$this->deleteDir("$dirPath/$file");
			}
			else
			{
				unlink("$dirPath/$file");
			}
		}

		if(is_dir($dirPath))
		{
			rmdir($dirPath);
		}
	}
}
