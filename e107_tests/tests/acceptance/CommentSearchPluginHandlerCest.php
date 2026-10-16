<?php

/**
 * A visitor's comments search answers without building the plugin handler, so nothing that handler needs can take the page down.
 */
class CommentSearchPluginHandlerCest
{
	const PROBE_FILE = 'e107_tests_comment_search_plugin_handler.php';

	/** The language files the probe takes away, relative to the running language's folder. */
	private static $lanFiles = array('admin/lan_plugin.php', 'admin/lan_admin.php');

	/** A legacy plugin.php that cannot be parsed outside its own admin pages. */
	const LEGACY_PLUGIN = 'e107_plugins/e107_tests_legacy_plugin/plugin.php';

	/** The one keyword the fixture comment carries. */
	const KEYWORD = 'tarnwhistle';

	const MARKER = 'CMTPLUGINHANDLER';

	public function _before(AcceptanceTester $I)
	{
		foreach(self::$lanFiles as $file)
		{
			\Helper\AppFileRegistry::park('e107_languages/English/'.$file);
		}

		$I->haveProbe(self::PROBE_FILE, $this->probeSource());

		$news = $I->haveInDatabase('e107_news', array(
			'news_title'          => 'Fixture Plugin Handler News',
			'news_sef'            => 'fixture-plugin-handler-news',
			'news_body'           => 'fixture',
			'news_extended'       => '',
			'news_meta_keywords'  => '',
			'news_meta_description' => '',
			'news_datestamp'      => time() - 3600,
			'news_author'         => 1,
			'news_category'       => 1,
			'news_allow_comments' => 0,
			'news_start'          => 0,
			'news_end'            => 0,
			'news_class'          => 0,
			'news_render_type'    => 0,
			'news_comment_total'  => 0,
			'news_summary'        => '',
			'news_thumbnail'      => '',
			'news_sticky'         => 0,
		));

		$I->haveInDatabase('e107_comments', \Test\Rows::comment(array(
			'comment_item_id'     => $news,
			'comment_subject'     => self::MARKER,
			'comment_author_id'   => 1,
			'comment_author_name' => 'fixture',
			'comment_datestamp'   => time() - 3600,
			'comment_comment'     => self::KEYWORD.' '.self::MARKER,
			'comment_ip'          => '127.0.0.1',
		)));

		$I->probe('act=seed');
		$I->resetAllCookies();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->probe('act=restore');
	}

	public function theSearchRendersWithoutTheAdminLanguageFiles(AcceptanceTester $I)
	{
		$I->probe('act=strip');
		$I->probe('act=cold');

		$this->searchAndSeeTheComment($I);
	}

	public function theSearchRendersBesideALegacyPluginThatOnlyItsAdminPagesCanParse(AcceptanceTester $I)
	{
		$I->writeAppFile(self::LEGACY_PLUGIN, "<?php\n\$eplug_name = 'e107_tests_legacy_plugin';\n\$eplug_version = '1.0';\n"
			."\$eplug_caption = E107_TESTS_LEGACY_PLUGIN_UNLOADED_LAN;\n");
		$I->probe('act=cold');

		$this->searchAndSeeTheComment($I);
	}

	/**
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function searchAndSeeTheComment(AcceptanceTester $I)
	{
		$I->amOnPage('/search.php?q='.self::KEYWORD.'&t=comments&r=0');

		$I->seeInSource(self::MARKER);
		$I->seeInSource('</html>');
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return str_replace('{{LAN_FILES}}', var_export(self::$lanFiles, true), <<<'PHP'
<?php
// Fixture for CommentSearchPluginHandlerCest.
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

$act = isset($_GET['act']) ? $_GET['act'] : '';
$search = e107::getConfig('search');

switch($act)
{
	case 'seed':
		if(!is_array($search->get('e107_tests_plugin_handler_stash')))
		{
			$search->set('e107_tests_plugin_handler_stash', array(
				'comments_handlers' => $search->get('comments_handlers'),
				'core_handlers' => $search->get('core_handlers'),
			));
		}
		$search->set('comments_handlers', array(
			'news' => array('id' => 0, 'dir' => 'core', 'class' => '0'),
			'download' => array('id' => 2, 'dir' => 'core', 'class' => '0'),
		));
		$search->set('core_handlers', array('comments' => array('class' => '0', 'pre_title' => '1', 'pre_title_alt' => '', 'chars' => '150', 'results' => '10', 'order' => '1')));
		$search->save(false, true, false);
		echo "PROBE_OK seed\n";
		break;

	case 'strip':
		$left = array();
		foreach({{LAN_FILES}} as $file)
		{
			$lan = e_LANGUAGEDIR.e_LANGUAGE.'/'.$file;
			if(!file_exists($lan.'.bak'))
			{
				copy($lan, $lan.'.bak');
				clearstatcache();
			}
			if(file_exists($lan.'.bak'))
			{
				unlink($lan);
				clearstatcache();
			}
			if(file_exists($lan))
			{
				$left[] = $file;
			}
		}
		echo empty($left) ? "PROBE_OK strip\n" : 'still there: '.implode(', ', $left)."\n";
		break;

	case 'cold':
		$cache = e107::getCache();
		$cache->clear(e_plugin::CACHETAG, true);
		echo $cache->retrieve(e_plugin::CACHETAG, e_plugin::CACHETIME, true, true) ? "the plugin cache is still there\n" : "PROBE_OK cold\n";
		break;

	case 'restore':
		$missing = array();
		foreach({{LAN_FILES}} as $file)
		{
			$lan = e_LANGUAGEDIR.e_LANGUAGE.'/'.$file;
			if(file_exists($lan.'.bak'))
			{
				if(file_exists($lan))
				{
					unlink($lan);
				}
				rename($lan.'.bak', $lan);
			}
			clearstatcache();
			if(!file_exists($lan))
			{
				$missing[] = $file;
			}
		}
		$stash = $search->get('e107_tests_plugin_handler_stash');
		if(is_array($stash))
		{
			$search->set('comments_handlers', $stash['comments_handlers']);
			$search->set('core_handlers', $stash['core_handlers']);
		}
		$search->removePref('e107_tests_plugin_handler_stash')->save(false, true, false);
		echo empty($missing) ? "PROBE_OK restore\n" : 'not restored: '.implode(', ', $missing)."\n";
		break;

	default:
		echo "unknown action\n";
}
PHP
		);
	}
}
