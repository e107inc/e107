<?php

/**
 * Stands in front of the eIPHandler singleton so a test can look at the ban-list
 * table and the replace-import lock at the one moment the importer hands control
 * back out, which is after it has deleted the batch it replaced.
 *
 * See AdminUiBatchProbeFixture for why the fixture lives in its own file.
 */
class BanlistImportRegenerateProbe
{
	/** @var eIPHandler the handler this stands in front of, which still does the work */
	private $inner;

	/** @var callable run before the wrapped handler regenerates anything */
	private $onRegenerate;

	public function __construct($inner, $onRegenerate)
	{
		$this->inner = $inner;
		$this->onRegenerate = $onRegenerate;
	}

	public function regenerateFiles()
	{
		call_user_func($this->onRegenerate);

		return $this->inner->regenerateFiles();
	}

	/**
	 * @return mixed whatever the wrapped handler answers, so standing in front of it changes nothing else
	 */
	public function __call($name, $args)
	{
		return call_user_func_array(array($this->inner, $name), $args);
	}
}
