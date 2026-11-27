<?php

/**
 * Controllers of the frontfixture module, which exists only for eFrontTest; each call eController makes on one is recorded as controller:hook, the controller being the middle of its class name.
 */
abstract class FrontControllerFixture extends eController
{
	/** @var string[] */
	public static $calls = array();

	/** @var string where the forward controller's to action forwards */
	public static $forwardTo = '';

	/** @var string the legacy script the lifecycle controller's legacy action names */
	public static $legacyScript = '';

	public function init()
	{
		$this->record('init');
	}

	public function preAction()
	{
		$this->record('preAction');
	}

	public function postAction()
	{
		$this->record('postAction');
	}

	public function shutdown()
	{
		$this->record('shutdown');
	}

	/**
	 * @param string $hook
	 * @return void
	 */
	protected function record($hook)
	{
		self::$calls[] = substr(get_class($this), strlen('core_frontfixture_'), -strlen('_controller')).':'.$hook;
	}
}

class core_frontfixture_lifecycle_controller extends FrontControllerFixture
{
	public function actionIndex()
	{
		$this->record('actionIndex');
		echo 'printed';

		return 'returned';
	}

	public function actionQuery()
	{
		return json_encode(array('parameters' => $this->getRequest()->getRequestParams(), 'query' => $_GET));
	}

	public function actionHelpers()
	{
		$this->addTitle('<b>Bold</b> title')->addTitle('Plain', false)->addMetaTitle('Meta')->addMetaDescription('About')->addBody('added');
	}

	public function actionGiveup()
	{
		echo 'half a page';

		throw new eException('The fixture gave up');
	}

	public function actionBuffered()
	{
		echo 'half a page';
		ob_start();
		echo 'and a buffer left open';

		throw new eException('The fixture gave up');
	}

	public function actionStubborn()
	{
		ob_start(null, 0, PHP_OUTPUT_HANDLER_STDFLAGS & ~PHP_OUTPUT_HANDLER_REMOVABLE);

		throw new eException('The fixture gave up');
	}

	public function actionMarkup()
	{
		throw new eException('<b>gave</b> "up"');
	}

	public function actionGone()
	{
		throw new eException('The fixture has no such item', 404);
	}

	public function actionLegacy()
	{
		eFront::isLegacy(self::$legacyScript);
	}
}

class core_frontfixture_forward_controller extends FrontControllerFixture
{
	public function actionAction()
	{
		$this->record('actionAction');
		$this->_forward('target');

		return 'before the forward, ';
	}

	public function actionController()
	{
		$this->_forward('lifecycle/index');
	}

	public function actionModule()
	{
		$this->_forward('frontfixture/lifecycle/query', array('id' => 7));
	}

	public function actionWildcard()
	{
		$this->_forward('*/*/target');
	}

	public function actionString()
	{
		$this->_forward('lifecycle/query', 'id=7&page=2');
	}

	public function actionKeep()
	{
		$this->_forward('lifecycle/query', false);
	}

	public function actionLoop()
	{
		$this->record('actionLoop');
		$this->_forward('loop');
	}

	public function actionTo()
	{
		$this->_forward(self::$forwardTo);
	}

	public function actionTarget()
	{
		$this->record('actionTarget');

		return 'target reached';
	}
}

class core_frontfixture_early_controller extends FrontControllerFixture
{
	public function init()
	{
		parent::init();
		$this->_forward('lifecycle/index');
	}

	public function actionIndex()
	{
		$this->record('actionIndex');
	}
}

class core_frontfixture_gate_controller extends FrontControllerFixture
{
	public function preAction()
	{
		parent::preAction();
		$this->_forward('lifecycle/index');
	}

	public function actionIndex()
	{
		$this->record('actionIndex');
	}
}

/**
 * A class the dispatcher finds under a controller's name that is not a controller.
 */
class core_frontfixture_stranger_controller
{
}

/**
 * A redirection handler that prints where it was sent as REDIRECT= and ends the request.
 */
class FrontRedirectionFixture extends redirection
{
	public function redirect($url, $replace = true, $http_response_code = null, $preventCache = true, $allowOffsite = false)
	{
		echo "\nREDIRECT=", json_encode($url), "\n";
		exit;
	}
}

/**
 * A router whose url config names a legacy script and then throws while it routes, leaving the request unrouted.
 */
class FrontThrowingRouterFixture extends eRouter
{
	public function route(eRequest $request, $checkOnly = false)
	{
		$request->routed = false;
		eFront::isLegacy('{e_BASE}news.php');

		throw new eException('The url config gave up');
	}
}
