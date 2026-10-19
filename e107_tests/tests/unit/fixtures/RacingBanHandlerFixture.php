<?php

/**
 * An eIPHandler whose host-name look-up, which add_ban() makes between its look-up and its insert, writes a rival's row.
 *
 * See AdminUiSearchfieldProbeFixture for why the fixture lives in its own file.
 */
class RacingBanHandlerFixture extends eIPHandler
{
	/** @var array the row the other request writes */
	public $rivalRow = array();

	/** @var int how many times add_ban() asked for the ban files to be written again */
	public $regenerated = 0;

	public function get_host_name($ip_address)
	{
		e107::getDb()->insert('banlist', $this->rivalRow);

		return '';
	}

	public function regenerateFiles()
	{
		$this->regenerated++;
	}
}
