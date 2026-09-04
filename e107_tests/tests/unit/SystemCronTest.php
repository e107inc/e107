<?php

/**
 * What the Test Email is allowed to say about the site it is describing.
 *
 * @see _system_cron::withoutSecrets()
 */
class SystemCronTest extends \Codeception\Test\Unit
{
	const TOKEN = 'd8f1a0b3c5e7290146a8b2d4f60c9e13a7b5d029';

	/** @var _system_cron */
	private $cron;

	/** @var \e107\Reflection\ReflectionMethod */
	private $withoutSecrets;

	/** @var mixed */
	private $savedPwd;

	protected function _before()
	{
		require_once(e_HANDLER . 'cron_class.php');

		$this->savedPwd = e107::getConfig()->get('e_cron_pwd');
		e107::getConfig()->set('e_cron_pwd', self::TOKEN);

		$this->cron = new _system_cron();
		$this->withoutSecrets = new \e107\Reflection\ReflectionMethod('_system_cron', 'withoutSecrets');
	}

	protected function _after()
	{
		e107::getConfig()->set('e_cron_pwd', $this->savedPwd);
	}

	/**
	 * @param array $vars
	 * @return array
	 */
	private function filter(array $vars)
	{
		return $this->withoutSecrets->invoke($this->cron, $vars);
	}

	public function testTokenIsRedactedWhereverItAppears()
	{
		$result = $this->filter(array(
			'e_QUERY'       => 'token=' . self::TOKEN,
			'e_TBQS'        => 'token=' . self::TOKEN,
			'e_REQUEST_URL' => 'https://example.com/cron.php?token=' . self::TOKEN,
			'MYSQL_DATABASE' => 'e107',
		));

		self::assertFalse(strpos(json_encode($result), self::TOKEN),
			'The cron token survived somewhere in the dump.');

		self::assertSame('token=' . _system_cron::REDACTED, $result['e_QUERY']);
		self::assertSame('token=' . _system_cron::REDACTED, $result['e_TBQS']);
		self::assertSame('https://example.com/cron.php?token=' . _system_cron::REDACTED,
			$result['e_REQUEST_URL']);
	}

	public function testTokenIsRedactedInsideNestedArrays()
	{
		$result = $this->filter(array(
			'e_SOMETHING' => array('deep' => array('deeper' => 'token=' . self::TOKEN)),
		));

		self::assertSame('token=' . _system_cron::REDACTED,
			$result['e_SOMETHING']['deep']['deeper']);
	}

	/**
	 * @return array
	 */
	public function secretKeyProvider()
	{
		return array(
			'e107 request line'  => array('e_REQUEST_URI'),
			'server request line' => array('REQUEST_URI'),
			'server query string' => array('QUERY_STRING'),
			'command line'       => array('argv'),
			'HTTP credentials'   => array('HTTP_AUTHORIZATION'),
			'CSRF token'         => array('HTTP_X_CSRF_TOKEN'),
			'database password'  => array('MYSQL_PASSWORD'),
			'root password'      => array('MYSQL_ROOT_PASSWORD'),
			'API secret'         => array('AWS_SECRET_ACCESS_KEY'),
		);
	}

	/**
	 * @dataProvider secretKeyProvider
	 * @param string $key
	 */
	public function testKeysThatNameASecretAreDropped($key)
	{
		$result = $this->filter(array($key => 'whatever', 'e_HTTP' => '/'));

		self::assertArrayNotHasKey($key, $result);
		self::assertArrayHasKey('e_HTTP', $result);
	}

	public function testValuesThatAreNotSecretAreUntouched()
	{
		$vars = array(
			'e_HTTP'    => '/',
			'e_BASE'    => '/var/www/html/',
			'SERVER_PORT' => 443,
			'e_MENU'    => null,
			'PATH'      => '/usr/local/bin:/usr/bin:/bin',
		);

		self::assertSame($vars, $this->filter($vars));
	}

	public function testNoTokenConfiguredLeavesEveryValueIntact()
	{
		e107::getConfig()->set('e_cron_pwd', '');

		$vars = array('e_QUERY' => 'token=', 'e_HTTP' => '/', 'e_BASE' => '/var/www/html/');

		self::assertSame($vars, $this->filter($vars));
	}
}
