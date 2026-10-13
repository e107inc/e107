<?php

/**
 * Uninstalling a plugin with its tables removes the comments of the types its plugin.xml declares and no others; keeping the tables keeps them.
 */
class PluginUninstallCommentIdsCest
{
	const PROBE_FILE = 'e107_tests_plugin_uninstall_comment_ids.php';

	/** Carried by every fixture comment, so the assertions find this test's rows alone. */
	const MARKER = 'e107_tests_plugin_uninstall_comment_ids';

	/** Comment types no fixture plugin declares: news and custom pages. */
	private static $keptTypes = array('0', 'page');

	public function _after(AcceptanceTester $I)
	{
		$I->dropPluginProbe();
	}

	public function aPluginDeclaringSeveralCommentTypesTakesThoseCommentsAlong(AcceptanceTester $I)
	{
		$types = array('e107_tests_cmtids_a', 'e107_tests_cmtids_b');
		$this->havePluginWithComments($I, 'e107_tests_comment_ids_two', $types);

		$I->dropPluginInstall('e107_tests_comment_ids_two');

		$this->seeOnlyTheKeptTypes($I, $types);
	}

	public function aPluginDeclaringOneCommentTypeTakesThoseCommentsAlong(AcceptanceTester $I)
	{
		$types = array('e107_tests_cmtids_c');
		$this->havePluginWithComments($I, 'e107_tests_comment_ids_one', $types);

		$I->dropPluginInstall('e107_tests_comment_ids_one');

		$this->seeOnlyTheKeptTypes($I, $types);
	}

	/**
	 * The uninstall form promises that a plugin uninstalled with its tables kept can be reinstalled with no data lost.
	 */
	public function aPluginUninstalledWithItsTablesKeptKeepsItsComments(AcceptanceTester $I)
	{
		$types = array('e107_tests_cmtids_k');
		$this->havePluginWithComments($I, 'e107_tests_comment_ids_kept', $types);

		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
		$I->probe('plugin=e107_tests_comment_ids_kept');

		foreach(array_merge($types, self::$keptTypes) as $type)
		{
			$I->seeInDatabase('e107_comments', array('comment_type' => $type, 'comment_comment' => self::MARKER));
		}
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $folder the fixture plugin's folder
	 * @param string[] $types the comment types its plugin.xml declares
	 * @return void
	 */
	private function havePluginWithComments(AcceptanceTester $I, $folder, array $types)
	{
		$tags = '';

		foreach($types as $type)
		{
			$tags .= '<commentID>'.$type.'</commentID>';
		}

		$I->writeAppFile('e107_plugins/'.$folder.'/plugin.xml', '<?xml version="1.0" encoding="utf-8"?>'."\n"
			.'<e107Plugin name="'.$folder.'" version="1.0" date="2026-10-12" compatibility="2.0" installRequired="true">'
			.'<author name="e107 Inc." url="https://e107.org" /><description>Fixture</description><category>misc</category>'
			.$tags.'</e107Plugin>'."\n");

		foreach(array_merge($types, self::$keptTypes) as $type)
		{
			$I->haveInDatabase('e107_comments', array(
				'comment_item_id'     => 1,
				'comment_subject'     => 'Fixture',
				'comment_author_id'   => 1,
				'comment_author_name' => 'fixture',
				'comment_datestamp'   => time(),
				'comment_comment'     => self::MARKER,
				'comment_blocked'     => 0,
				'comment_ip'          => '127.0.0.1',
				'comment_type'        => $type,
				'comment_lock'        => 0,
				'comment_share'       => 0,
			));
		}

		$I->havePluginInstalled($folder);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string[] $removedTypes the comment types the uninstalled plugin declared
	 * @return void
	 */
	private function seeOnlyTheKeptTypes(AcceptanceTester $I, array $removedTypes)
	{
		foreach($removedTypes as $type)
		{
			$I->dontSeeInDatabase('e107_comments', array('comment_type' => $type, 'comment_comment' => self::MARKER));
		}

		foreach(self::$keptTypes as $type)
		{
			$I->seeInDatabase('e107_comments', array('comment_type' => $type, 'comment_comment' => self::MARKER));
		}
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for PluginUninstallCommentIdsCest: uninstall a fixture plugin, keeping its tables.
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

$folder = isset($_GET['plugin']) ? preg_replace('/[^\w]/', '', $_GET['plugin']) : '';

if(strpos($folder, 'e107_tests_') !== 0 || !is_readable(e_PLUGIN.$folder.'/plugin.xml'))
{
	echo "not a fixture plugin\n";
	exit;
}

e107::getPlugin()->install_plugin_xml($folder, 'uninstall', array('delete_tables' => false));

echo "PROBE_OK\n";
PHP;
	}
}
