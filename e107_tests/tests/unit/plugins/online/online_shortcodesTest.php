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
 * e107_plugins/online/online_shortcodes.php:346 builds
 *
 *   <a href='$pinfo'>$currentMember[page]</a>
 *
 * with neither value encoded. Both come from the online table's
 * online_location column, which is a visitor-controlled request URI passed
 * through FILTER_SANITIZE_URL and nothing else
 * (e107_handlers/online_class.php:115-119). FILTER_SANITIZE_URL keeps the
 * apostrophe, the double quote and the angle brackets, so the value can close
 * the href attribute or the anchor outright.
 *
 * {ONLINE_MEMBER_PAGE} is rendered by the online menu's extended member list
 * for every member shown, so the audience is every visitor who can see the
 * menu.
 *
 * The two values land in two different contexts, so they need two different
 * encodings: $pinfo is an attribute value, $currentMember['page'] is element
 * text. Asserting the exact bytes for each is the point of these tests; an
 * assertion that some tag is absent would pass on a payload that never carried
 * one.
 */
class online_shortcodesTest extends \Test\Unit
{

	/** @var online_shortcodes */
	protected $sc;

	/** Closes the href attribute, then the anchor. */
	const ATTR_PAYLOAD = "/index.php?a=ENCXSSA'><img src=x onerror=alert(1)>";

	/** htmlspecialchars(ENT_QUOTES) of {@see ATTR_PAYLOAD}. */
	const ATTR_PAYLOAD_ENCODED =
		'/index.php?a=ENCXSSA&#039;&gt;&lt;img src=x onerror=alert(1)&gt;';

	/** Element-text payload: no quote needed, the tag is enough. */
	const TEXT_PAYLOAD = 'ENCXSSC<img src=x onerror=alert(1)>';

	/** htmlspecialchars(ENT_QUOTES) of {@see TEXT_PAYLOAD}. */
	const TEXT_PAYLOAD_ENCODED = 'ENCXSSC&lt;img src=x onerror=alert(1)&gt;';

	protected function _before()
	{
		require_once(e_PLUGIN.'online/online_shortcodes.php');

		try
		{
			$this->sc = $this->make('online_shortcodes');
		}
		catch (Exception $e)
		{
			self::fail($e->getMessage());
		}

		$this->sc->__construct();
	}

	public function testMemberPageEncodesTheHrefAttribute()
	{
		$this->sc->currentMember = array(
			'oid'   => 1,
			'oname' => 'someone',
			'page'  => 'index',
			'pinfo' => self::ATTR_PAYLOAD,
		);

		$actual = $this->sc->sc_online_member_page();

		$this->assertStringNotContainsString("ENCXSSA'><img", $actual,
			'The visitor location closed the href attribute it was written into.');
		$this->assertStringContainsString(self::ATTR_PAYLOAD_ENCODED, $actual,
			'The visitor location was not encoded for an attribute context.');
	}

	public function testMemberPageEncodesTheAnchorText()
	{
		$this->sc->currentMember = array(
			'oid'   => 1,
			'oname' => 'someone',
			'page'  => self::TEXT_PAYLOAD,
			'pinfo' => '/index.php',
		);

		$actual = $this->sc->sc_online_member_page();

		$this->assertStringNotContainsString('ENCXSSC<img', $actual,
			'The visitor page name was written into element text as markup.');
		$this->assertStringContainsString(self::TEXT_PAYLOAD_ENCODED, $actual,
			'The visitor page name was not encoded for a text context.');
	}

	/**
	 * Positive control. The shortcode still has to produce a working link for
	 * an ordinary location, and still has to fall back to the bare page name
	 * for a member who is inside the admin directory (the existing behaviour
	 * of the ternary at :345).
	 */
	public function testMemberPageStillLinksAnOrdinaryLocation()
	{
		$this->sc->currentMember = array(
			'oid'   => 1,
			'oname' => 'someone',
			'page'  => 'news',
			'pinfo' => '/news.php?extend.7',
		);

		$this->assertSame("<a href='/news.php?extend.7'>news</a>",
			$this->sc->sc_online_member_page());

		$this->sc->currentMember = array(
			'oid'   => 1,
			'oname' => 'someone',
			'page'  => 'admin',
			'pinfo' => '/'.e107::getFolder('admin').'admin.php',
		);

		$this->assertSame('admin', $this->sc->sc_online_member_page());
	}
}
