<?php

/**
 * A screen whose options save hands its values to a recorder rather than to a stored preference row.
 *
 * e_HANDLER.'admin_ui.php' must already be loaded when this file is included;
 * see AdminUiSearchfieldProbeFixture for why the fixture lives in its own file.
 */
class AdminUiPrefsSaveProbeFixture extends e_admin_ui
{
	protected $pluginName = 'core';

	/** @var array what the request posts */
	private $posted;

	/** @var AdminUiPrefsSaveConfigRecorder */
	private $config;

	/** @var array|null what beforePrefsSave() answers with */
	private $before;

	/**
	 * @param array      $prefs  the options the screen declares
	 * @param array      $posted what the request posts
	 * @param array|null $before what the screen's beforePrefsSave() returns, null for the default
	 */
	public function __construct(array $prefs, array $posted, $before = null)
	{
		$this->prefs = $prefs;
		$this->posted = $posted;
		$this->before = $before;
		$this->config = new AdminUiPrefsSaveConfigRecorder();
	}

	public function getPosted($key = null, $default = null)
	{
		if($key === null)
		{
			return $this->posted;
		}

		return isset($this->posted[$key]) ? $this->posted[$key] : $default;
	}

	public function getConfig()
	{
		return $this->config;
	}

	public function beforePrefsSave($new_data, $old_data)
	{
		return $this->before;
	}
}

/**
 * Stands in for the preference object, keeping every key the options save hands it.
 */
class AdminUiPrefsSaveConfigRecorder
{
	/** @var array key => value, in the order the save handed them over */
	public $written = array();

	/** @var bool */
	public $saved = false;

	public function getPref()
	{
		return array();
	}

	public function get($key)
	{
		return null;
	}

	public function setPostedData($key, $value)
	{
		$this->written[$key] = $value;

		return $this;
	}

	public function setData($key, $value)
	{
		$this->written[$key] = $value;

		return $this;
	}

	public function save()
	{
		$this->saved = true;

		return true;
	}

	public function setMessages()
	{
		return $this;
	}
}
