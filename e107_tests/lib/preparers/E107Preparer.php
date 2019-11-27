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
			//	echo ($dirPath . "must be a directory");
			return null;
		}

		if(substr($dirPath, strlen($dirPath) - 1, 1) != '/')
		{
			$dirPath .= '/';
		}

		$files = glob($dirPath . '*', GLOB_MARK);

		foreach($files as $file)
		{
			if(is_dir($file))
			{
				$this->deleteDir($file);
			}
			else
			{
				unlink($file);
			}
		}

		if(is_dir($dirPath))
		{
			rmdir($dirPath);
		}
	}
}
