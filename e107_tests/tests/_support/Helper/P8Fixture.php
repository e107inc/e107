<?php
namespace Helper;

/**
 * Shared fixture for the P8 (output encoding + tinymce4 authorisation) Cests.
 *
 * Not a Codeception module: it holds no state, so a module would only add a
 * suite-configuration entry to maintain.
 *
 * The probe exists because several of the sinks under test are reached only
 * through code that boots the framework first, and because two of them fetch a
 * feed over HTTP. Asking the application through a probe is also the only
 * honest way to learn a site-relative path: the acceptance suite installs twice
 * and the second install's site_path is the literal string 000000test, so
 * deriving it from a hash of the database name names a directory nothing ever
 * reads.
 *
 * Every filename this class writes is registered in
 * {@see \Extension\WorkspaceCleanup} so a crashed run does not leave it behind.
 */
class P8Fixture
{
	/** Probe dropped into the docroot for as long as a Cest needs it. */
	const PROBE_FILE = 'e107_tests_p8_probe.php';

	/** RSS served back to e107_admin/boot.php in place of e107.org. */
	const ADMIN_FEED_FILE = 'e107_tests_p8_feed.xml';

	/** The addons listing served back to e107_admin/boot.php's ?mode=addons block. */
	const ADDON_FEED_FILE = 'e107_tests_p8_addons.xml';

	/** RSS served back to the newsfeed plugin. */
	const NEWSFEED_FILE = 'e107_tests_p8_newsfeed.xml';

	/** A TinyMce config outside both directories wysiwyg_class may read. */
	const TINYMCE_CANARY_FILE = 'e107_tests_p8_tinymce_canary.xml';

	const MEMBER_NAME = 'p8member';
	const MEMBER_PASS = 'P8memberPass1';

	/**
	 * A single quote followed by an event handler: the shape that matters for
	 * a single-quoted attribute, because the tag never has to be closed.
	 */
	const ATTR_PAYLOAD = "P8XSSA' onmouseover='alert(1)";

	/** Encoded form the same value has to take once the sink encodes. */
	const ATTR_PAYLOAD_ENCODED = 'P8XSSA&#039; onmouseover=&#039;alert(1)';

	/** Closes the attribute and the tag, so a real browser runs the handler. */
	const BREAKOUT_PAYLOAD = 'P8XSSB\'><img src=x onerror="window.__p8xss=1">';

	/** Encoded form of {@see BREAKOUT_PAYLOAD}. */
	const BREAKOUT_PAYLOAD_ENCODED =
		'P8XSSB&#039;&gt;&lt;img src=x onerror=&quot;window.__p8xss=1&quot;&gt;';

	/**
	 * Text-context payload. Element text needs the angle brackets encoded; the
	 * quote is irrelevant there, which is why it is a different string from
	 * {@see ATTR_PAYLOAD}.
	 */
	const TEXT_PAYLOAD = 'P8XSSC<img src=x onerror="window.__p8xss=1">';

	/** Encoded form of {@see TEXT_PAYLOAD}. */
	const TEXT_PAYLOAD_ENCODED =
		'P8XSSC&lt;img src=x onerror=&quot;window.__p8xss=1&quot;&gt;';

	/**
	 * For a double-quoted attribute. A single quote is inert there, so the
	 * single-quote payloads above would prove nothing about e107_admin/boot.php,
	 * whose anchor is written with double quotes.
	 */
	const DQ_ATTR_PAYLOAD = 'https://example.com/?a=P8XSSD" onmouseover="alert(1)';

	/** Encoded form of {@see DQ_ATTR_PAYLOAD}. */
	const DQ_ATTR_PAYLOAD_ENCODED =
		'https://example.com/?a=P8XSSD&quot; onmouseover=&quot;alert(1)';

	/** A second element-text payload, so two text sinks fail distinguishably. */
	const TEXT_PAYLOAD_2 = 'P8XSSE<b onmouseover="alert(1)">2026</b>';

	/** Encoded form of {@see TEXT_PAYLOAD_2}. */
	const TEXT_PAYLOAD_2_ENCODED =
		'P8XSSE&lt;b onmouseover=&quot;alert(1)&quot;&gt;2026&lt;/b&gt;';

	/**
	 * A single-quoted-attribute payload carrying its own marker, so a feed with
	 * six separate sinks fails legibly rather than in one indistinguishable
	 * blur.
	 *
	 * @param string $marker
	 * @return string
	 */
	public static function attrPayload($marker)
	{
		return "https://example.com/?".$marker."=1' onmouseover='alert(1)";
	}

	/**
	 * @param string $marker
	 * @return string what the sink has to emit for {@see attrPayload()}
	 */
	public static function attrPayloadEncoded($marker)
	{
		return htmlspecialchars(self::attrPayload($marker), ENT_QUOTES, 'UTF-8');
	}

	/**
	 * The unencoded fragment whose presence in a response proves the attribute
	 * was closed.
	 *
	 * @param string $marker
	 * @return string
	 */
	public static function attrPayloadRaw($marker)
	{
		return $marker."=1' onmouseover=";
	}

	/**
	 * An element-text payload carrying its own marker, so a feed with a dozen
	 * separate text sinks fails legibly rather than in one blur.
	 *
	 * @param string $marker
	 * @return string
	 */
	public static function textPayload($marker)
	{
		return $marker.'<img src=x onerror="alert(1)">';
	}

	/**
	 * @param string $marker
	 * @return string what the sink has to emit for {@see textPayload()}
	 */
	public static function textPayloadEncoded($marker)
	{
		return htmlspecialchars(self::textPayload($marker), ENT_QUOTES, 'UTF-8');
	}

	/**
	 * The unencoded fragment whose presence proves markup reached the page.
	 *
	 * @param string $marker
	 * @return string
	 */
	public static function textPayloadRaw($marker)
	{
		return $marker.'<img';
	}

	/**
	 * A URL whose scheme executes on click. htmlspecialchars() does not touch a
	 * single character of it, so an encoder alone leaves this working.
	 *
	 * @param string $marker
	 * @return string
	 */
	public static function schemePayload($marker)
	{
		return 'javascript:alert("'.$marker.'")';
	}

	/**
	 * @return string probe source
	 */
	public static function probeSource()
	{
		return <<<'PHP'
<?php
/**
 * Fixture for the P8 acceptance and webdriver Cests. Removed again in each
 * Cest's _after().
 *
 * ?p8=<action>. Payload-bearing arguments arrive base64 encoded so nothing
 * between the test and the sink can be blamed for mangling them.
 */

$p8act = isset($_GET['p8']) ? $_GET['p8'] : '';

/**
 * @param string $name
 * @return string
 */
function p8_arg($name)
{
	return isset($_GET[$name]) ? base64_decode($_GET[$name]) : '';
}

/**
 * The base URL this very request arrived on, trailing slash included.
 *
 * The fixtures the application is pointed at are served by the same server
 * that is serving this probe, but where that server lives is not knowable from
 * here: the docker harness answers to http://web/ at the docroot, and CI
 * answers to http://localhost/e107/ in a subdirectory. Hard-coding either one
 * makes the other environment fetch nothing at all, which reads as an encoder
 * that dropped the payload rather than as a feed that never arrived.
 *
 * @return string
 */
function p8_base_url()
{
	$https  = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off');
	$scheme = $https ? 'https' : 'http';
	$host   = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
	$dir    = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

	return $scheme.'://'.$host.rtrim($dir, '/').'/';
}

if($p8act === 'adminfeed')
{
	// e107_admin/boot.php guards its own define, so the dashboard feed can be
	// pointed at a fixture served by this very container.
	define('ADMINFEED', p8_base_url().'e107_tests_p8_feed.xml');
	define('e_REMOTE_FILE_ALLOW_PRIVATE', true);
	$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
	$_GET['mode'] = 'core';
	$_GET['type'] = 'feed';
}

if($p8act === 'addonsfeed')
{
	// boot.php guards this define exactly as it guards ADMINFEED, so the addons
	// panel can be pointed at a fixture served by this very container.
	define('ADDONFEED', p8_base_url().'e107_tests_p8_addons.xml');
	define('e_REMOTE_FILE_ALLOW_PRIVATE', true);
	$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
	$_GET['mode'] = 'addons';
	$_GET['type'] = 'plugin';
}

if($p8act === 'newsfeed' || $p8act === 'newsfeedstale')
{
	define('e_REMOTE_FILE_ALLOW_PRIVATE', true);
}

$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}

$sql = e107::getDb();

switch($p8act)
{
	case 'reset':
		// Flood protection counts hits per address and bans at fifty; every
		// request in this container arrives from the same bridge address.
		$sql->delete('online');
		$sql->delete('banlist', 'banlist_bantype IN (2, -2)');
		header('Content-Type: text/plain');
		echo "P8_OK reset\n";
		break;

	case 'constants':
		header('Content-Type: text/plain');
		echo "P8_OK constants\n";
		echo 'MEDIA:'.e_MEDIA."\n";
		echo 'SYSTEM:'.e_SYSTEM."\n";
		echo 'PLUGIN:'.e_PLUGIN."\n";
		echo 'THEME:'.THEME."\n";
		echo 'HOST:'.$_SERVER['HTTP_HOST']."\n";
		echo 'SITEURL:'.SITEURL."\n";
		// A CSRF token for whoever is asking, guest or member alike, so an
		// authorisation test can send a POST the CSRF handler has no quarrel
		// with and measure only the authorisation.
		echo 'TOKEN:'.defset('e_TOKEN')."\n";
		break;

	case 'member':
		// A member with no admin bit and no user class, so check_class() on the
		// post_html preference (254, administrators) and getperms('P') are both
		// false for them. Hashed by the application, so login.php accepts it.
		$name = 'p8member';
		$pass = 'P8memberPass1';
		$sql->delete('user', "user_loginname='".$name."'");
		$hash = e107::getUserSession()->HashPassword($pass, $name);
		$id = $sql->insert('user', array(
			'user_name'      => $name,
			'user_loginname' => $name,
			'user_email'     => $name.'@example.com',
			'user_password'  => $hash,
			'user_join'      => time(),
			'user_class'     => '',
			'user_admin'     => 0,
			'user_perms'     => '',
			'user_ban'       => 0,
			'user_xup'       => '',
			'user_prefs'     => '',
			'user_signature' => '',
			'user_realm'     => '',
		));
		header('Content-Type: text/plain');
		echo $id ? "P8_OK member id=".$id."\n" : "P8_FAIL member\n";
		break;

	case 'online':
		// A guest row, at an address that is not this test client's, so the
		// admin's own row can never adopt it. online_agent is written on insert
		// and never on update, which is exactly how a visitor's User-Agent
		// survives into the row an administrator is later shown.
		$agent = p8_arg('agent');
		$loc = p8_arg('loc');
		$sql->delete('online', "online_agent LIKE '%P8XSS%' OR online_location LIKE '%P8XSS%'");
		$ok = $sql->insert('online', array(
			'online_timestamp' => time(),
			'online_flag'      => 0,
			'online_user_id'   => '0',
			'online_ip'        => e107::getIPHandler()->ipEncode('203.0.113.7'),
			'online_location'  => $loc,
			'online_pagecount' => 1,
			'online_active'    => 1,
			'online_agent'     => $agent,
			'online_language'  => e_LAN,
		));
		header('Content-Type: text/plain');
		echo $ok ? "P8_OK online\n" : "P8_FAIL online\n";
		break;

	case 'cleanup':
		// The acceptance install is not rebuilt between Cests, so the member and
		// the seeded rows would otherwise outlive the Cest that made them.
		$sql->delete('user', "user_loginname='p8member'");
		$sql->delete('online', "online_agent LIKE '%P8XSS%' OR online_location LIKE '%P8XSS%'");
		$sql->delete('newsfeed', "newsfeed_url LIKE '%e107_tests_p8_newsfeed%'");
		header('Content-Type: text/plain');
		echo "P8_OK cleanup\n";
		break;

	case 'adminfeed':
	case 'addonsfeed':
		$_GET['e-token'] = defset('e_TOKEN');
		require_once(e_ADMIN.'boot.php');
		// boot.php exits inside each feed branch, so reaching here means the
		// branch was never entered.
		header('Content-Type: text/plain');
		echo "P8_FAIL ".$p8act." branch not reached\n";
		break;

	case 'newsfeed':
	case 'newsfeedstale':
		// Seed a feed row, then render it exactly as newsfeed.php:51-62 and :82
		// do: pick up the shipped template at global scope, where newsfeedInfo()
		// reaches it through `global`, and ask for both the main and the menu
		// rendering.
		//
		// The rendering happens here rather than on newsfeed.php itself because
		// the fetch has to be allowed to reach a private address, and the
		// plugin's own cache is keyed on e_QUERY (cache_handler.php:100-105), so
		// a fetch primed from one URL is invisible to any other.
		//
		// 'newsfeedstale' is the upgraded-site case: a row written before the
		// patch, still inside its update interval, so getFeed() serves what is
		// stored rather than re-fetching. What is stored must therefore be inert.
		$stale = ($p8act === 'newsfeedstale');
		$url = p8_base_url().'e107_tests_p8_newsfeed.xml';
		$sql->delete('newsfeed', "newsfeed_url='".$url."'");
		$feedId = $sql->insert('newsfeed', array(
			'newsfeed_name'        => 'P8 fixture feed',
			'newsfeed_url'         => $url,
			'newsfeed_description' => 'default',
			'newsfeed_active'      => 3,
			'newsfeed_image'       => 'default::5::5',
			'newsfeed_updateint'   => 3600,
			'newsfeed_timestamp'   => $stale ? time() : 0,
			'newsfeed_data'        => $stale ? p8_arg('data') : '',
		));

		if(empty($feedId))
		{
			header('Content-Type: text/plain');
			echo "P8_FAIL newsfeed insert\n";
			break;
		}

		e107::includeLan(e_PLUGIN.'newsfeed/languages/'.e_LANGUAGE.'_newsfeed.php');
		require_once(e_PLUGIN.'newsfeed/newsfeed_functions.php');

		if(!class_exists('newsfeedClass'))
		{
			header('Content-Type: text/plain');
			echo "P8_FAIL newsfeed plugin not installed\n";
			break;
		}

		// Either cache would otherwise hand back a previous run's parse.
		e107::getCache()->clear(NEWSFEED_LIST_CACHE_TAG);
		e107::getCache()->clear(NEWSFEED_NEWS_CACHE_TAG);

		if($stale)
		{
			// getFeed() re-fetches unless the news cache is warm as well.
			e107::getCache()->set(NEWSFEED_NEWS_CACHE_TAG.$feedId, p8_arg('data'), true);
		}

		include(e_PLUGIN.'newsfeed/templates/newsfeed_template.php');

		// {FEEDLANGUAGE} and {FEEDLINK} are documented template variables that no
		// shipped template happens to place, so they are placed here the way a
		// theme override would place them. Without this the two sinks render
		// nowhere and an assertion about them could only ever be vacuous.
		$NEWSFEED_MAIN_END = "<div id='p8-extra'>{FEEDLANGUAGE}"
			." <a href='{FEEDLINK}'>P8 feed link</a></div>".$NEWSFEED_MAIN_END;

		$nf = new newsfeedClass;
		$main = $nf->newsfeedInfo($feedId, 'main');
		$menu = $nf->newsfeedInfo($feedId, 'menu');

		echo "P8_OK ".$p8act." id=".$feedId."\n";
		echo "<!-- P8 MAIN -->\n".$main['text'];
		echo "\n<!-- P8 MENU -->\n".$menu['text'];
		break;

	default:
		header('Content-Type: text/plain');
		echo "P8_FAIL unknown action\n";
}
PHP;
	}

	/**
	 * RSS the admin dashboard feed is pointed at. The payloads sit in the three
	 * fields e107_admin/boot.php interpolates: link (a double-quoted href),
	 * title (element text inside that anchor) and pubDate (element text).
	 *
	 * @return string
	 */
	public static function adminFeedXml()
	{
		$attr = self::xmlText(self::DQ_ATTR_PAYLOAD);
		$text = self::xmlText(self::TEXT_PAYLOAD);
		$date = self::xmlText(self::TEXT_PAYLOAD_2);

		return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
	<channel>
		<title>P8 admin feed fixture</title>
		<link>https://example.com/</link>
		<description>P8 admin feed fixture</description>
		<image>
			<url>https://example.com/logo.png</url>
			<title>P8</title>
			<link>https://example.com/</link>
		</image>
		<item>
			<title>$text</title>
			<link>$attr</link>
			<pubDate>$date</pubDate>
			<description>P8 admin feed item one</description>
		</item>
		<item>
			<title>P8 benign item</title>
			<link>https://example.com/benign</link>
			<pubDate>Sun, 02 Aug 2026 00:00:00 +0000</pubDate>
			<description>P8 benign description</description>
		</item>
	</channel>
</rss>
XML;
	}

	/**
	 * RSS the newsfeed plugin is pointed at. Every value the plugin renders is a
	 * value the feed operator chose, so every one of them carries its own marker:
	 *
	 *   P8NFIMGLINK   channel image  <a href='...'>
	 *   P8NFIMGSRC    channel image  <img src='...'>
	 *   P8NFIMGALT    channel image  <img alt='...'>
	 *   P8NFCHANLINK  {FEEDTITLE}    <a href='...'>
	 *   P8NFCHANTITLE {FEEDTITLE}    anchor text
	 *   P8NFDATE      {FEEDLASTBUILDDATE}
	 *   P8NFCOPY      {FEEDCOPYRIGHT}
	 *   P8NFLANG      {FEEDLANGUAGE}
	 *   P8NFITEMLINK  {FEEDITEMLINK} <a href='...'>
	 *   P8NFITEMTITLE {FEEDITEMLINK} anchor text
	 *   P8NFAUTHOR    {FEEDITEMCREATOR}
	 *   P8NFDESC      {FEEDITEMTEXT}
	 *   P8NFJSLINK    a second item whose link is a javascript: URL
	 *
	 * The last of those is the one an encoder alone does not answer: a
	 * javascript: URL contains no character htmlspecialchars() touches.
	 *
	 * The benign item deliberately carries ampersands in both its title and its
	 * link, because the item anchor used to be run through
	 * str_replace('&', '&amp;') whole and no longer is.
	 *
	 * @return string
	 */
	public static function newsfeedXml()
	{
		$imgLink = self::xmlText(self::attrPayload('P8NFIMGLINK'));
		$imgSrc = self::xmlText(self::attrPayload('P8NFIMGSRC'));
		$imgAlt = self::xmlText(self::attrPayload('P8NFIMGALT'));
		$chanLink = self::xmlText(self::attrPayload('P8NFCHANLINK'));
		$chanTitle = self::xmlText(self::textPayload('P8NFCHANTITLE'));
		$date = self::xmlText(self::textPayload('P8NFDATE'));
		$copyright = self::xmlText(self::textPayload('P8NFCOPY'));
		$language = self::xmlText(self::textPayload('P8NFLANG'));
		$itemLink = self::xmlText(self::attrPayload('P8NFITEMLINK'));
		$itemTitle = self::xmlText(self::textPayload('P8NFITEMTITLE'));
		$author = self::xmlText(self::textPayload('P8NFAUTHOR'));
		$description = self::xmlText('P8NFDESC<script>window.__p8xss=1;</script>'
			.'<img src=x onerror="window.__p8xss=1">');
		$jsLink = self::xmlText(self::schemePayload('P8NFJSLINK'));

		return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
	<channel>
		<title>$chanTitle</title>
		<link>$chanLink</link>
		<language>$language</language>
		<copyright>$copyright</copyright>
		<description>P8 newsfeed fixture</description>
		<lastBuildDate>$date</lastBuildDate>
		<image>
			<url>$imgSrc</url>
			<title>$imgAlt</title>
			<link>$imgLink</link>
		</image>
		<item>
			<title>$itemTitle</title>
			<link>$itemLink</link>
			<author>$author</author>
			<description>$description</description>
		</item>
		<item>
			<title>P8 newsfeed scheme item</title>
			<link>$jsLink</link>
			<description>P8 newsfeed scheme body</description>
		</item>
		<item>
			<title>P8 newsfeed benign R&amp;D item</title>
			<link>https://example.com/benign?a=1&amp;b=2</link>
			<description>P8 newsfeed benign body</description>
		</item>
	</channel>
</rss>
XML;
	}

	/**
	 * A newsfeed_data blob in the shape the plugin wrote before the channel image
	 * was composed at render time: a finished, unencoded anchor stored under
	 * newsfeed_image_link.
	 *
	 * @return string JSON, as e107::serialize() writes it
	 */
	public static function staleNewsfeedData()
	{
		return json_encode(array(
			'channel' => array(
				'title'         => 'P8 stale channel title',
				'link'          => 'https://example.com/stale',
				'language'      => 'en',
				'copyright'     => 'P8 stale copyright',
				'lastbuilddate' => 'Sun, 02 Aug 2026 00:00:00 +0000',
			),
			'items' => array(
				array(
					'title'       => 'P8 stale item',
					'link'        => 'https://example.com/stale-item',
					'description' => 'P8 stale item body',
				),
			),
			'newsfeed_image_link' => "<a href='https://example.com/?P8NFSTALE=1' onmouseover='alert(1)'"
				." rel='external'><img src='https://example.com/stale.png' alt='' /></a>",
		));
	}

	/**
	 * The listing e107_admin/boot.php's ?mode=addons block fetches. Same remote
	 * host and same admin victim as the ?mode=core block, fifty lines away.
	 *
	 * Two entries, because xmlClass::parseXml() collapses a single repeated
	 * element into one associative array and the block's foreach would then walk
	 * the attributes instead of the entries.
	 *
	 * @return string
	 */
	public static function addonFeedXml()
	{
		$icon = self::xmlText(self::DQ_ATTR_PAYLOAD);
		$name = self::xmlText(self::textPayload('P8ADDONNAME'));
		$version = self::xmlText(self::textPayload('P8ADDONVER'));
		$author = self::xmlText(self::textPayload('P8ADDONAUTH'));
		$description = self::xmlText(self::textPayload('P8ADDONDESC'));

		return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<e107>
	<plugin name="$name" version="$version" author="$author" icon="$icon" thumbnail="$icon">
		<description>$description</description>
	</plugin>
	<plugin name="P8 benign addon" version="1.0" author="P8" icon="https://example.com/benign.png" thumbnail="https://example.com/benign.png">
		<description>P8 benign addon description</description>
	</plugin>
</e107>
XML;
	}

	/**
	 * A TinyMce configuration outside e107_plugins/tinymce4/templates and
	 * outside THEME/templates/tinymce. wysiwyg_class must never read it.
	 *
	 * @return string
	 */
	public static function tinymceCanaryXml()
	{
		// A copy of the shipped public config with only its name changed, so a
		// traversal that reaches it produces a valid, obviously-named editor
		// configuration. A hand-written approximation would risk failing to
		// load for reasons of its own, and an empty response would then be
		// indistinguishable from containment.
		$source = APP_PATH.'/e107_plugins/tinymce4/templates/public.xml';
		$xml = @file_get_contents($source);

		if($xml === false)
		{
			throw new \RuntimeException("Could not read $source to build the TinyMce canary");
		}

		return str_replace('name="Public"', 'name="P8 CANARY"', $xml);
	}

	/**
	 * @param string $value
	 * @return string $value as XML character data
	 */
	private static function xmlText($value)
	{
		return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
	}
}
