<?php

/**
 * Probe controller whose submit path runs over one declared field, probe_url, with a model that only records whether it saved.
 *
 * e_HANDLER.'admin_ui.php' must already be loaded when this file is included;
 * see AdminUiSearchfieldProbeFixture for why the fixture lives in its own file.
 */
class AdminUiLinkAddressProbeFixture extends e_admin_controller_ui
{
	/** @var AdminUiLinkAddressModelStub */
	public $model;

	/**
	 * @param array $field the declaration of probe_url
	 * @param array $types the model's data types, by field
	 */
	public function __construct(array $field, array $types)
	{
		$this->fields = array('probe_url' => $field);
		$this->model = new AdminUiLinkAddressModelStub($types);
		$this->setRequest(new e_admin_request('mode=main&action=create'));
	}

	public function getModel()
	{
		return $this->model;
	}

	public function getPosted($key = null, $default = null)
	{
		$posted = array('probe_url' => 'news.php', 'etrigger_submit' => 'create');

		if($key === null)
		{
			return $posted;
		}

		return isset($posted[$key]) ? $posted[$key] : $default;
	}

	public function probeSubmit()
	{
		return $this->_manageSubmit();
	}
}

/**
 * Probe preferences page with one preference, probe_url, declared as given and posted with a script address, whose config only records whether it saved.
 */
class AdminUiLinkAddressPrefsProbeFixture extends e_admin_ui
{
	/** @var AdminUiLinkAddressModelStub */
	public $config;

	/** @var array the page's preference declarations */
	protected $declared;

	/**
	 * @param array $pref the declaration of probe_url
	 */
	public function __construct(array $pref)
	{
		$this->declared = array('probe_url' => $pref);
		$this->prefs = $this->declared;
		$this->config = new AdminUiLinkAddressModelStub(array());

		$request = new e_admin_request('mode=main&action=prefs');
		$request->setPosted(array('probe_url' => 'javascript:alert(1)', 'etrigger_save' => 'save'));
		$this->setRequest($request);
	}

	public function getConfig()
	{
		return $this->config;
	}
}

/**
 * The same page declaring its preference through getPrefs(), which its form and its save read, with $prefs left empty.
 */
class AdminUiLinkAddressPrefsGetterProbeFixture extends AdminUiLinkAddressPrefsProbeFixture
{
	/**
	 * @param array $pref the declaration of probe_url
	 */
	public function __construct(array $pref)
	{
		parent::__construct($pref);
		$this->prefs = array();
	}

	public function getPrefs()
	{
		return $this->declared;
	}
}

/**
 * A model with nothing loaded and the data types it is given, whose save() only records that it ran.
 */
class AdminUiLinkAddressModelStub
{
	/** @var bool */
	public $saved = false;

	/** @var array */
	private $types;

	public function __construct(array $types)
	{
		$this->types = $types;
	}

	public function getDataFields()
	{
		return $this->types;
	}

	public function getData()
	{
		return array();
	}

	public function getPref()
	{
		return array();
	}

	public function getId()
	{
		return 0;
	}

	public function getFieldIdName()
	{
		return 'probe_id';
	}

	public function setPostedData($data)
	{
		return $this;
	}

	public function save($force = false, $forceSave = false)
	{
		$this->saved = true;

		return true;
	}

	public function hasError()
	{
		return true;
	}

	public function setMessages($session = false)
	{
		return $this;
	}
}
