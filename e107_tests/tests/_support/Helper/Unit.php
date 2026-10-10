<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Unit extends E107Base
{
	protected $deployer_components = [];

	public function _beforeSuite($settings = array())
	{
		parent::_beforeSuite($settings);

		global $_E107;
		$_E107 = array();
		$_E107['cli'] = true;
		$_E107['debug'] = true;

		codecept_debug("Loading ".APP_PATH."/class2.php…");
		define('E107_DEBUG_LEVEL', 1 << 0);
		require_once(APP_PATH."/class2.php");
		\e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_admin.php');

		$create_dir = array(e_MEDIA,e_MEDIA_IMAGE,e_MEDIA_ICON,e_SYSTEM,e_CACHE,e_CACHE_CONTENT,e_CACHE_IMAGE, e_CACHE_DB, e_LOG, e_BACKUP, e_CACHE_URL, e_TEMP, e_IMPORT);

		foreach($create_dir as $dr)
		{
			if(!is_dir($dr))
			{
				if(mkdir($dr, 0755))
				{
				//	echo "\n(Creating ".$dr.")";
				}

			}

		}
	}

	/**
	 * Start each test with shortcode batches that hold nobody else's data.
	 *
	 * The suite runs its whole file list in one PHP process and shuffles the
	 * order on purpose. The shortcode parser lives for that whole run and keeps
	 * every batch object it has built, each still holding the vars it was last
	 * rendered with. A shortcode that reads a var it never set therefore passes
	 * whenever an earlier test happened to leave one behind and warns whenever
	 * the shuffle puts that test later. One unlucky order raised seventeen
	 * errors and eight failures on a tree whose other runs were clean.
	 *
	 * The batches are emptied rather than discarded. Building one again reruns
	 * the plugin's include_lan(), and a language file that has already defined
	 * its constants cannot define them twice.
	 *
	 * Every test also declares e107.org: whichever is first in its process to
	 * render the admin navigation makes e107 ask it for a new release (pref
	 * check_updates), and the shuffle decides which test that is.
	 *
	 * @param \Codeception\TestInterface $test
	 * @return void
	 */
	public function _before(\Codeception\TestInterface $test)
	{
		parent::_before($test);
		$this->getModule('\Helper\Outbound')->expectOutboundRequest('e107.org');

		$scClasses = new \e107\Reflection\ReflectionProperty('e_parse_shortcode', 'scClasses');

		foreach($scClasses->getValue(\e107::getScParser()) as $batch)
		{
			if($batch instanceof \e_shortcode)
			{
				$batch->setParserVars(array());
			}
		}
	}
}
