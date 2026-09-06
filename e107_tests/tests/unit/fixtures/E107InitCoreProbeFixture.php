<?php

/**
 * {@see e107} with the boot sequence stubbed out, so {@see e107::initCore()}
 * can be called for the folder overrides it hands {@see e107::_init()}.
 *
 * e107_class.php must already be loaded when this file is included.
 */
class E107InitCoreProbeFixture extends e107
{
	/** @var array the folder overrides initCore() passed on */
	public $paths;

	public function __construct()
	{
	}

	protected function _init($e107_paths, $e107_root_path, $e107_config_mysql_info, $e107_config_override = array())
	{
		$this->paths = $e107_paths;

		return $this;
	}
}
