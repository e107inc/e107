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
			throw new Exception(__CLASS__ . " couldn't delete ".$system);
		}

	}

	private function deleteDir($dirPath)
	{
		codecept_debug(__CLASS__ . ' is deleting '.escapeshellarg((string) $dirPath).'…');

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
			try
			{
				rmdir($dirPath);
			}
			catch (Exception $e)
			{
				echo $e->getMessage()."\n";
			/*	echo "Contents: \n";
				$list = scandir($dirPath);
				var_export($list);*/
			   // do something
			}
		}
	}
}
