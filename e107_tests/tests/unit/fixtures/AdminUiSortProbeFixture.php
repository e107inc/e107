<?php

/**
 * A sortable list over the probe table, without the request plumbing the sort does not use.
 *
 * e_HANDLER.'admin_ui.php' must already be loaded when this file is included;
 * see AdminUiSearchfieldProbeFixture for why the fixture lives in its own file.
 */
class adminUiSortProbe extends e_admin_ui
{
	protected $table = adminUiSortTest::TABLE;

	protected $pid = 'probe_id';

	protected $sortField = 'probe_order';

	public function __construct()
	{
	}
}
