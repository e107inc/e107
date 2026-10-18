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
 * The Facebook app id, stored as typed on the social plugin's Login tab, as the pages that load Facebook's SDK print it.
 */
class socialFacebookAppIdTest extends \Test\Unit
{
	const MARK = '@@socialFacebookAppIdTest@@';

	/** @var array what each child printed, by part and stored value */
	private static $printed = array();

	/**
	 * Values as the Login tab stores them, exactly as typed, and the app id each should give Facebook's SDK.
	 *
	 * @return array
	 */
	public function storedValues()
	{
		return array(
			'an app id'                          => array('1234567890123456', array('1234567890123456')),
			'an app id typed with spaces around' => array(' 1234567890123456 ', array('1234567890123456')),
			'a quote'                            => array("1234567890123456'});document.title='breakout';({a:'", array()),
			'a closing script tag'               => array("1234567890123456</script><script>document.title='breakout'</script>", array()),
		);
	}

	/**
	 * @dataProvider storedValues
	 * @param string $stored
	 * @param array  $initialised
	 * @return void
	 */
	public function testTheFacebookLikeMenuInitialisesTheSdkWithTheAppIdAlone($stored, $initialised)
	{
		$footer = $this->printed($stored, 'fb_like_menu', 'footer');

		self::assertSame($initialised, $this->appIdsInitialised($footer), $footer);
	}

	/**
	 * @dataProvider storedValues
	 * @param string $stored
	 * @param array  $initialised
	 * @return void
	 */
	public function testFacebookCommentsInitialiseTheSdkWithTheAppIdAlone($stored, $initialised)
	{
		$footer = $this->printed($stored, 'comments', 'footer');

		self::assertSame($initialised, $this->appIdsInitialised($footer), $footer);
	}

	/**
	 * eResponse::renderMeta() drops " and < from every value it prints, so the tag holds nothing but its own attributes.
	 *
	 * @dataProvider storedValues
	 * @param string $stored
	 * @param array  $initialised
	 * @return void
	 */
	public function testTheAppIdMetaTagCarriesTheAppIdAlone($stored, $initialised)
	{
		$meta = $this->printed($stored, 'fb_like_menu', 'meta');

		$doc = new DOMDocument();
		@$doc->loadHTML('<html><head>'.$meta.'</head></html>');

		$contents = array();

		foreach((new DOMXPath($doc))->query('//meta[@property="fb:app_id" or @name="fb:app_id"]') as $tag)
		{
			$names = array();

			foreach($tag->attributes as $attribute)
			{
				$names[] = $attribute->nodeName;
			}

			self::assertSame(array(), array_diff($names, array('property', 'name', 'content')), $meta);

			$contents[] = $tag->getAttribute('content');
		}

		self::assertSame($initialised, $contents, $meta);
	}

	/**
	 * @param string $html what the footer printed
	 * @return array the app id each FB.init() call passes, decoded
	 */
	private function appIdsInitialised($html)
	{
		$ids = array();

		foreach($this->scriptsIn($html) as $script)
		{
			$calls = preg_split('/\bFB\.init\s*\(/', $script);

			foreach(array_slice($calls, 1) as $call)
			{
				self::assertSame(1, preg_match('/^\s*\{\s*appId\s*:\s*(\'[^\'\\\\\n]*\'|"(?:[^"\\\\\n]|\\\\.)*")\s*,\s*xfbml\s*:/', $call, $match),
					"The app id is not passed to FB.init() as one JavaScript string:\n".$html);

				$ids[] = $match[1][0] === "'" ? (string) substr($match[1], 1, -1) : json_decode($match[1]);
			}
		}

		return $ids;
	}

	/**
	 * An HTML parser ends a script element at the first </script it meets, whatever the script was in the middle of.
	 *
	 * @param string $html what the footer printed
	 * @return array the text of each script element in $html
	 */
	private function scriptsIn($html)
	{
		preg_match_all('#<script\b[^>]*>(.*?)</script[\s/>]#is', $html, $scripts);

		self::assertSame('', trim(preg_replace('#<script\b[^>]*>.*?</script\s*>#is', '', $html)),
			"Something the footer printed lies outside its script elements:\n".$html);
		self::assertLessThanOrEqual(1, count($scripts[1]), "The footer printed more script elements than it was given:\n".$html);

		return $scripts[1];
	}

	/**
	 * @return array PHP running each part that loads Facebook's SDK, by name
	 */
	private static function parts()
	{
		return array(
			'fb_like_menu' => "require(e_PLUGIN.'social/fb_like_menu.php'); ",
			'comments'     => "require_once(e_PLUGIN.'social/e_comment.php'); \$comments = new social_comment(); echo \$comments->facebook(array()); ",
		);
	}

	/**
	 * The init script is built while class2.php loads the plugins' e_module.php files, so the child boots without them, stores the value, then loads the social plugin's as class2.php does.
	 *
	 * @param string $stored what the Login tab stored as the Facebook app id
	 * @param string $part a key of {@see socialFacebookAppIdTest::parts()}
	 * @param string $printer 'meta' for the head's meta tags, 'part' for the part itself, 'footer' for the footer's inline script
	 * @return string
	 */
	private function printed($stored, $part, $printer)
	{
		$key = $part.':'.$stored;

		if(!isset(self::$printed[$key]))
		{
			$parts = self::parts();
			$login = array('Facebook' => array('enabled' => 1, 'keys' => array('id' => $stored)));

			$php = "e107::getConfig()->set('social_login', ".var_export($login, true)."); ";
			$php .= "require(e_PLUGIN.'social/e_module.php'); ";
			$php .= "echo '".self::MARK."'; echo e107::getSingleton('eResponse')->renderMeta(); ";
			$php .= "echo '".self::MARK."'; ".$parts[$part];
			$php .= "echo '".self::MARK."'; e107::getJs()->renderJs('footer_inline', true); ";
			$php .= "echo '".self::MARK."'; ";

			list($output) = $this->runInBootedCli($php, '', array('cli' => true, 'no_module' => true));

			$printed = explode(self::MARK, implode("\n", $output));

			self::assertCount(5, $printed, "The child did not print every part:\n".implode("\n", $output));

			self::$printed[$key] = array_combine(array('meta', 'part', 'footer'), array_slice($printed, 1, 3));
		}

		return self::$printed[$key][$printer];
	}
}
