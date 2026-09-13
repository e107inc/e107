<?php

class E107Preparer implements Preparer
{
	const TEST_HASH = '000000test'; // see e107_config.php

	/** @var string */
	private $appPath;

	public function __construct($appPath)
	{
		$this->appPath = $appPath;
	}

	public function getAppPath()
	{
		// In-place: the app runs straight from the served tree.
		return $this->appPath;
	}

	/** Written by install.php and e_file::blockScriptExecution(), so they belong to the installer rather than to a test. */
	const INSTALL_ARTEFACTS = array(
		'e107_system/e107Install.log',
		'e107_media/.htaccess',
	);

	public function snapshot()
	{
		return $this->deleteHashDirs();
	}

	public function rollback()
	{
		return $this->deleteHashDirs();
	}

	/**
	 * The directory name a real install keeps its state under. {@see \e107::makeSiteHash()}
	 *
	 * @return string
	 */
	private static function siteHash()
	{
		$params = unserialize(PARAMS_SERIALIZED);
		$dbname = isset($params['db']['dbname']) ? $params['db']['dbname'] : '';

		return substr(md5($dbname.'.'.\Helper\E107Base::E107_MYSQL_PREFIX), 0, 10);
	}

	protected function deleteHashDirs()
	{
		$system = APP_PATH."/e107_system/".self::TEST_HASH;
		$this->deleteDir($system);

		$media = APP_PATH."/e107_media/".self::TEST_HASH;
		$this->deleteDir($media);

		$hash = self::siteHash();
		$this->deleteDir(APP_PATH."/e107_system/".$hash);
		$this->deleteDir(APP_PATH."/e107_media/".$hash);

		foreach(self::INSTALL_ARTEFACTS as $artefact)
		{
			@unlink(APP_PATH."/".$artefact);
		}

		if(is_dir($system))
		{
			throw new Exception(__CLASS__ . " couldn't delete ".$system);
		}

	}

	private function deleteDir($dirPath)
	{
		codecept_debug(__CLASS__ . ' is deleting '.escapeshellarg($dirPath).'…');

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
			}
		}
	}
}
