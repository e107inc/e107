<?php

/**
 * Probe subclass exposing e_admin_ui's protected search-field filtering.
 *
 * e_HANDLER.'admin_ui.php' must already be loaded when this file is
 * included, so include it inside the test method, after that require has
 * run. A named fixture in its own include (rather than an anonymous class
 * or a class declared inside the test file) keeps the test file parseable
 * on PHP 5.6 and keeps the class out of Codeception's discovery, which
 * reflects every class declared in a *Test.php while loading the suite,
 * before any test method has required the parent handler.
 */
class AdminUiSearchfieldProbeFixture extends e_admin_ui
{
	public $probeFields = array();

	public function __construct()
	{
	}

	public function getFields()
	{
		return $this->probeFields;
	}

	public function getQuery($key = null, $default = null)
	{
		return 'needle';
	}

	public function probe($selected)
	{
		return $this->handleListSearchfieldFilter($selected);
	}
}
