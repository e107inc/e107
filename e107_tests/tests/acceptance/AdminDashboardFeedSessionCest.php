<?php

/**
 * The dashboard's feed and add-on update handlers in e107_admin/boot.php ask e107.org for data; every other request the administrator's browser makes waits on the PHP session's lock until they let it go.
 *
 * The probe answers each remote fetch itself, and the answer names the session state at the moment the handler asked.
 */
class AdminDashboardFeedSessionCest
{
	const PROBE_FILE = 'e107_tests_feed_session_probe.php';

	const RELEASED = 'FEED_SESSION_RELEASED';
	const HELD = 'FEED_SESSION_HELD';

	/** The name the probe gives the theme update it invents. */
	const UPDATE_NAME = 'FEED_SESSION_UPDATE';

	public function _before(AcceptanceTester $I)
	{
		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
		$I->probe('act=reset');
		$I->loginAsAdmin();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->probe('act=reset');
	}

	public function theCoreFeedIsFetchedAfterTheSessionIsReleased(AcceptanceTester $I)
	{
		$I->expectOutboundRequest('e107.org');
		$I->amOnProbe('act=handler&mode=core&type=feed');

		$I->seeInSource(self::RELEASED);
		$I->dontSeeInSource(self::HELD);
	}

	public function theAddonFeedIsFetchedAfterTheSessionIsReleased(AcceptanceTester $I)
	{
		$I->expectOutboundRequest('e107.org');
		$I->amOnProbe('act=handler&mode=addons&type=plugin');

		$I->seeInSource(self::RELEASED);
		$I->dontSeeInSource(self::HELD);
	}

	public function theAddonVersionsAreLookedUpAfterTheSessionIsReleased(AcceptanceTester $I)
	{
		$I->expectOutboundRequest('e107.org');
		$I->amOnProbe('act=handler&mode=addons&type=update');

		$I->seeInSource(self::UPDATE_NAME.' '.self::RELEASED);
		$I->dontSeeInSource(self::HELD);
	}

	/**
	 * The check's answer outlives the request that found it: the next dashboard shows the notice, with this session's token in its links, and does not ask again.
	 */
	public function theDashboardShowsTheUpdateTheCheckFound(AcceptanceTester $I)
	{
		$I->expectOutboundRequest('e107.org');
		$style = $I->haveSitePref('adminstyle', 'infopanel');

		try
		{
			$I->amOnProbe('act=handler&mode=addons&type=update');
			$I->seeInSource(self::UPDATE_NAME);

			$token = $I->grabProbe('act=token');
			$I->amOnPage('/e107_admin/admin.php');

			$I->seeInSource(self::UPDATE_NAME);
			$I->seeInSource('action=download&amp;e-token='.$token);
			$I->dontSeeInSource('mode=addons&type=update');
		}
		finally
		{
			$I->haveSitePref('adminstyle', $style === '' ? null : $style);
		}
	}

	/**
	 * Any system cache clear removes the answer, a preference save among them, and the next dashboard asks again rather than showing nothing for the rest of the session.
	 */
	public function theDashboardRunsTheCheckAgainOnceTheAnswerIsGone(AcceptanceTester $I)
	{
		$I->expectOutboundRequest('e107.org');
		$style = $I->haveSitePref('adminstyle', 'infopanel');

		try
		{
			$I->amOnProbe('act=handler&mode=addons&type=update');
			$I->seeInSource(self::UPDATE_NAME);

			$I->probe('act=reset');
			$I->amOnPage('/e107_admin/admin.php');

			$I->dontSeeInSource(self::UPDATE_NAME);
			$I->seeInSource("id='e-admin-addons-update'");
			$I->seeInSource('mode=addons&type=update');
		}
		finally
		{
			$I->haveSitePref('adminstyle', $style === '' ? null : $style);
		}
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
$act = isset($_GET['act']) ? $_GET['act'] : '';

if($act === 'handler')
{
	$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
}

require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}

require_once(e_HANDLER.'xml_class.php');

function feed_session_state()
{
	return (session_status() === PHP_SESSION_ACTIVE) ? 'FEED_SESSION_HELD' : 'FEED_SESSION_RELEASED';
}

function feed_session_versions($type)
{
	$themes = e107::getTheme()->getList('version');
	$folder = key($themes);

	if($type !== 'theme' || $folder === null)
	{
		return array('-unable-to-connect');
	}

	return array($folder => array(
		'folder'    => $folder,
		'name'      => 'FEED_SESSION_UPDATE '.feed_session_state(),
		'version'   => '999.0',
		'author'    => $themes[$folder]['author'],
		'icon'      => '',
		'thumbnail' => '',
		'date'      => '2026-10-06',
		'url'       => '',
		'price'     => '',
		'params'    => array('id' => 0, 'mode' => ''),
	));
}

class FeedSessionXml extends xmlClass
{
	public function getRemoteFile($address, $timeout = 10, $postData = null)
	{
		$state = feed_session_state();

		if(strpos($address, ADMINFEED) === 0)
		{
			$item = '<item><title>'.$state.'</title><link>https://example.com/</link><pubDate>Tue, 06 Oct 2026 00:00:00 +0000</pubDate><description>-</description></item>';

			return '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>-</title><image><url>https://example.com/logo.png</url></image>'.$item.$item.'</channel></rss>';
		}

		$addon = '<plugin name="'.$state.'" version="1.0" author="-" icon="https://example.com/icon.png" thumbnail="https://example.com/icon.png"><description>-</description></plugin>';

		return '<?xml version="1.0" encoding="UTF-8"?><e107>'.$addon.$addon.'</e107>';
	}
}

class FeedSessionCache extends ecache
{
	public function retrieve($CacheTag, $MaximumAge = false, $ForcedCheck = false, $syscache = false)
	{
		if(strpos($CacheTag, 'Infopanel_') === 0)
		{
			return false;
		}

		if(strpos($CacheTag, 'Versions_') === 0)
		{
			return json_encode(feed_session_versions(substr($CacheTag, strlen('Versions_'))));
		}

		return parent::retrieve($CacheTag, $MaximumAge, $ForcedCheck, $syscache);
	}
}

switch($act)
{
	case 'handler':
		e107::setRegistry('core/e107/singleton/xmlClass', new FeedSessionXml());
		e107::setRegistry('core/e107/singleton/ecache', new FeedSessionCache());
		$_GET['e-token'] = defset('e_TOKEN');
		require_once(e_ADMIN.'boot.php');
		echo "PROBE_FAIL the handler did not answer\n";
		break;

	case 'token':
		echo defset('e_TOKEN');
		break;

	case 'reset':
		e107::getCache()->clearAll('system');
		echo "PROBE_OK reset\n";
		break;
}
PHP;
	}
}
