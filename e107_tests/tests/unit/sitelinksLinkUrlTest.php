<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * e107::url() answers false for an e_url key the registry does not hold, and a
 * sitelink that names one still carries the URL it was saved with.
 */
class sitelinksLinkUrlTest extends \Test\Unit
{

	/**
	 * makeLink() reads the $tp global that class2.php defines on a real
	 * request and the suite's bootstrap does not.
	 *
	 * @return void
	 */
	protected function _before()
	{
		$GLOBALS['tp'] = e107::getParser();
	}

	/**
	 * @return void
	 */
	protected function _after()
	{
		unset($GLOBALS['tp']);
	}

	/**
	 * A row of the shape plugin installation writes: a stored URL, plus the
	 * plugin and e_url key to prefer over it. Nothing registers 'myplugin'.
	 *
	 * @return array
	 */
	private static function pluginRow()
	{
		return array(
			'link_id'          => 1,
			'link_name'        => 'My plugin file',
			'link_url'         => '{e_PLUGIN}myplugin/myfile.php',
			'link_owner'       => 'myplugin',
			'link_sefurl'      => 'index',
			'link_description' => '',
			'link_button'      => '',
			'link_parent'      => 0,
			'link_open'        => 0,
			'link_class'       => 0,
			'link_order'       => 1,
		);
	}

	/**
	 * A heading: the sitelink with no URL at all that admins use as a divider.
	 *
	 * @return array
	 */
	private static function headingRow()
	{
		$row = self::pluginRow();

		$row['link_name']   = 'A heading';
		$row['link_url']    = '';
		$row['link_owner']  = '';
		$row['link_sefurl'] = '';

		return $row;
	}

	/**
	 * @return array the three style keys makeLink() reads unguarded
	 */
	private static function style()
	{
		return array('linkstart' => '', 'linkclass' => '', 'linkend' => '');
	}

	/**
	 * @param string $markup
	 * @return string the href the markup carries
	 */
	private static function href($markup)
	{
		self::assertSame(1, preg_match("/href='([^']*)'/", $markup, $matches),
			"the link has to be rendered with an href:\n".$markup);

		return $matches[1];
	}

	/**
	 * @param string $sef the route to register for myplugin/index
	 * @return array what {@see sitelinksLinkUrlTest::restoreRoutes()} puts back
	 */
	private static function registerRoute($sef)
	{
		$restore = array(e107::getRegistry('core/e107/addons/e_url'), e107::getPref('e_url_list'));

		e107::setRegistry('core/e107/addons/e_url', array('myplugin' => array('index' => array('sef' => $sef))));
		e107::getConfig()->set('e_url_list', array('myplugin' => 1));

		return $restore;
	}

	/**
	 * @param array $restore as returned by {@see sitelinksLinkUrlTest::registerRoute()}
	 * @return void
	 */
	private static function restoreRoutes($restore)
	{
		e107::setRegistry('core/e107/addons/e_url', $restore[0]);
		e107::getConfig()->set('e_url_list', $restore[1]);
	}

	/**
	 * @param array $row
	 * @return string the markup makeLink() rendered for it
	 */
	private function renderLink($row)
	{
		return e107::getSitelinks()->makeLink($row, false, self::style(), false);
	}

	public function testAnUnresolvedSefRouteKeepsTheStoredUrlInTheSitelinkHref()
	{
		$markup = $this->renderLink(self::pluginRow());

		self::assertStringEndsWith('e107_plugins/myplugin/myfile.php', self::href($markup),
			"an e_url key the registry does not hold must not cost the link its stored URL:\n".$markup);
	}

	/**
	 * The half of the contract a fallback is most likely to break.
	 */
	public function testAResolvedSefRouteStillOutranksTheStoredUrl()
	{
		$restore = self::registerRoute('my/route');

		try
		{
			$markup = $this->renderLink(self::pluginRow());
		}
		finally
		{
			self::restoreRoutes($restore);
		}

		self::assertStringEndsWith('my/route', self::href($markup),
			"a registered route still decides the href:\n".$markup);
		self::assertStringNotContainsString('myfile.php', $markup,
			"and the stored URL is only a fallback:\n".$markup);
	}

	public function testAnUnresolvedSefRouteStillMatchesTheActiveLink()
	{
		self::assertNotSame('', e_REQUEST_HTTP,
			'precondition: an empty request path matches an unresolved route by accident');

		$row = self::pluginRow();
		$old = e107::getRegistry('core/e107/navigation/active');

		e107::setRegistry('core/e107/navigation/active', 'myplugin/myfile.php');

		try
		{
			$active = e107::getNav()->isActive($row);
		}
		finally
		{
			e107::setRegistry('core/e107/navigation/active', $old);
		}

		self::assertTrue($active, 'the active check has to follow the URL the link will render');
	}

	public function testAnUnresolvedSefRouteKeepsTheStoredUrlInTheNavigationShortcode()
	{
		$sc = e107::getScBatch('navigation');
		$sc->setVars(self::pluginRow());

		self::assertStringEndsWith('e107_plugins/myplugin/myfile.php', (string) $sc->sc_nav_link_url(),
			'the navigation shortcode has the same stored URL to fall back on');
	}
}
