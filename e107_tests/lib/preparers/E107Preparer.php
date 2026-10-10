<?php

class E107Preparer implements Preparer
{
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
}
