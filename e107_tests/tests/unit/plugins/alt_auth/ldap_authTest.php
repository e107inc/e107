<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * @group plugins
 *
 * Every alt_auth method file declares a class named auth_login, so the LDAP one is built in a child process of its own.
 */
class ldap_authTest extends \Test\Unit
{
	protected function _before()
	{
		e107::getPlugin()->install('alt_auth');
	}

	protected function _after()
	{
		e107::getPlugin()->uninstall('alt_auth', array('delete_tables' => 1));
	}

	/**
	 * The settings arrive as one name => value map, and the directory attribute names are kept in the lower case ldap_get_entries() reports them in.
	 */
	public function testConstructorReadsEachSettingByNameAndValue()
	{
		$settings = array(
			'ldap_server'            => 'ldap://ldap.e107help.test',
			'ldap_servertype'        => 'LDAP',
			'ldap_basedn'            => 'dc=e107help,dc=test',
			'ldap_ou'                => '',
			'ldap_user'              => '',
			'ldap_passwd'            => '',
			'ldap_version'           => '3',
			'ldap_xf_user_email'     => 'mail',
			'ldap_xf_user_login'     => 'displayName',
			'ldap_xf_user_name'      => '',
			'ldap_pm_user_hideemail' => 'bool1',
			'ldap_pm_user_join'      => 'none',
		);

		$rows = array();
		foreach($settings as $name => $value)
		{
			$rows[] = array('auth_type' => 'ldap', 'auth_parmname' => $name, 'auth_parmval' => base64_encode(base64_encode($value)));
		}

		self::assertNotFalse(e107::getDb()->createQueryBuilder()->insert('alt_auth')->values($rows)->execute());

		$php = <<<'PHP'
require_once(e_PLUGIN.'alt_auth/alt_auth_login_class.php');
require_once(e_PLUGIN.'alt_auth/ldap_auth.php');
echo "\n@@BUILDING@@\n";
$login = new auth_login();
$copied = Closure::bind(function () {
	return array('attributes' => $this->copyAttribs, 'methods' => $this->copyMethods);
}, $login, 'auth_login');
echo "\n@@COPIED=", json_encode($copied()), "@@\n";
PHP;

		list($output, $status) = $this->runInBootedCli($php);

		$printed = implode("\n", $output);

		self::assertSame(0, $status, "the constructor never returned:\n".$printed);
		self::assertSame(1, preg_match('/@@BUILDING@@(.*)@@COPIED=(.*)@@/s', $printed, $match), "the child reported nothing copied:\n".$printed);
		self::assertDoesNotMatchRegularExpression('/(^|\n)(PHP )?(Warning|Fatal error|Uncaught)\b/', $match[1],
			"the constructor printed a diagnostic:\n".$printed);

		$copied = json_decode($match[2], true);
		ksort($copied['attributes']);
		ksort($copied['methods']);

		self::assertSame(array('user_email' => 'mail', 'user_login' => 'displayname'), $copied['attributes']);
		self::assertSame(array('user_hideemail' => 'bool1'), $copied['methods']);
	}
}
