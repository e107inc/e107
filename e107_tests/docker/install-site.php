<?php
/**
 * Build-time steps of the self-contained image that e107's installer leaves to an administrator.
 *
 * Usage, as the web server's user from the site root:
 *   php install-site.php plugin <folder>   install one bundled plugin, unless it is installed already
 *   php install-site.php finish            add the accounts README.md lists and the hosts the site answers to
 */

$_E107 = array('cli' => true);
require_once getcwd().'/class2.php';

$action = isset($argv[1]) ? $argv[1] : '';

if($action === 'plugin' && isset($argv[2]))
{
	$folder = $argv[2];
	$installed = e107::getConfig('core')->get('plug_installed');

	if(is_array($installed) && isset($installed[$folder]))
	{
		exit(0);
	}

	e107::getPlug()->clearCache();
	$plugin = e107::getPlugin();
	$plugin->update_plugins_table('update');
	$plugin->install_plugin_xml($folder, 'install');

	$installed = e107::getConfig('core', false)->get('plug_installed');
	echo $folder, ': ', (is_array($installed) && isset($installed[$folder])) ? 'installed' : 'NOT installed', "\n";
	exit(is_array($installed) && isset($installed[$folder]) ? 0 : 1);
}

if($action === 'finish')
{
	$accounts = array(
		array('name' => 'editor', 'admin' => 1, 'perms' => '5'),
		array('name' => 'member', 'admin' => 0, 'perms' => ''),
	);

	$db = e107::getDb();
	$userHandler = e107::getUserSession();

	foreach($accounts as $account)
	{
		$name = $account['name'];

		if($db->count('user', '(*)', "user_loginname = '".$name."'"))
		{
			continue;
		}

		$db->insert('user', array(
			'user_name'         => $name,
			'user_loginname'    => $name,
			'user_password'     => $userHandler->HashPassword($name, $name),
			'user_email'        => $name.'@example.com',
			'user_join'         => time(),
			'user_lastvisit'    => 0,
			'user_currentvisit' => 0,
			'user_ban'          => 0,
			'user_class'        => '',
			'user_admin'        => $account['admin'],
			'user_perms'        => $account['perms'],
			'user_prefs'        => '',
			'user_signature'    => '',
			'user_realm'        => '',
			'user_xup'          => '',
		));
	}

	e107::getConfig('core')
		->set('trusted_hosts', array('localhost', '127.0.0.1'))
		->save(false, true, false);

	exit(0);
}

fwrite(STDERR, "usage: php install-site.php plugin <folder> | finish\n");
exit(2);
