<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * {ADMIN_NAVIGATION} folds plugin admin links into the core categories when
 * the site is not showing plugins in a section of their own. The map it folds
 * them with has to cover every category a plugin is allowed to declare.
 */
class admin_shortcodesNavigationTest extends \Codeception\Test\Unit
{
	/** @var admin_shortcodes */
	private $sc;

	/** @var array registry entries this test overwrites, restored in _after() */
	private $savedRegistry = array();

	/** @var mixed the admin_separate_plugins pref as found, restored in _after() */
	private $savedSeparate = null;

	/** @var array the $menu_vars handed to e_navigation::admin() */
	private $rendered = array();

	protected function _before()
	{
		require_once(e_CORE.'shortcodes/batch/admin_shortcodes.php');

		try
		{
			$this->sc = $this->make('admin_shortcodes');
		}
		catch (Exception $e)
		{
			self::fail($e->getMessage());
		}

		$this->savedRegistry['core/e107/singleton/e_navigation'] = e107::getRegistry('core/e107/singleton/e_navigation');
		$this->savedSeparate = e107::getConfig()->get('admin_separate_plugins');
	}

	protected function _after()
	{
		foreach($this->savedRegistry as $key => $value)
		{
			e107::setRegistry($key, $value);
		}

		e107::getConfig()->set('admin_separate_plugins', $this->savedSeparate);
	}

	/**
	 * The categories adminCats() builds, cut down to the ids the map names
	 * plus the About section, which is where a plugin declaring 'about'
	 * belongs.
	 *
	 * @return array
	 */
	private function adminCats()
	{
		$ids = array(1 => 'setMenu', 2 => 'userMenu', 3 => 'contMenu', 4 => 'toolMenu', 5 => 'managMenu', 6 => 'miscMenu', 20 => 'aboutMenu');
		$cats = array();

		foreach($ids as $i => $id)
		{
			$cats['id'][$i] = $id;
			$cats['title'][$i] = $id;
			$cats['img'][$i] = '';
			$cats['lrg_img'][$i] = '';
			$cats['sort'][$i] = true;
		}

		return $cats;
	}

	/**
	 * Put one installed plugin in the given category in front of the
	 * shortcode, and hand back the sections it sorted the link into.
	 *
	 * @param string $category
	 * @return array section id => list of links
	 */
	private function sectionsFor($category)
	{
		e107::getConfig()->set('admin_separate_plugins', 0);

		$cats = $this->adminCats();
		$link = array(
			'text'        => 'Probe plugin',
			'description' => 'Probe plugin',
			'link'        => 'e107_plugins/probe/admin_config.php',
			'image'       => '',
			'image_large' => '',
			'category'    => $category,
			'perm'        => '0',
			'sort'        => 2,
			'sub_class'   => null,
		);

		$test = $this;
		$nav = $this->make('e_navigation', array(
			'adminCats'  => function() use ($cats) { return $cats; },
			'adminLinks' => function($mode = false) use ($link)
			{
				return ($mode === 'plugin2') ? array('plugnav-probe' => $link) : array();
			},
			'admin'      => function($title, $active_page, $e107_vars, $tmpl = array(), $sub_link = false, $sortlist = false) use ($test)
			{
				$test->recordRendered($e107_vars);

				return '';
			},
		));

		e107::setRegistry('core/e107/singleton/e_navigation', $nav);

		$this->sc->sc_admin_navigation();

		$sections = array();
		foreach($this->rendered as $id => $data)
		{
			if(isset($data['sub']))
			{
				$sections[$id] = $data['sub'];
			}
		}

		return $sections;
	}

	/**
	 * @param array $vars
	 * @return void
	 */
	public function recordRendered($vars)
	{
		$this->rendered = (array) $vars;
	}

	/**
	 * plugin_class accepts eight categories. The map here has to name each one
	 * that can reach it, or the link is filed under a section id of '', which
	 * has no title, no icon and no link of its own.
	 */
	public function testEveryAcceptedPluginCategoryHasASection()
	{
		$sections = $this->sectionsFor('about');

		self::assertArrayNotHasKey('', $sections, "a plugin's admin link must not land in a nameless section");
		self::assertArrayHasKey('aboutMenu', $sections, "a plugin declaring 'about' belongs in the About section");
		self::assertSame('Probe plugin', $sections['aboutMenu'][0]['text']);
	}

	/**
	 * And the categories that were already mapped still go where they went, so
	 * the added entries are additions and not a reshuffle.
	 */
	public function testTheAlreadyMappedCategoriesAreUnchanged()
	{
		$expected = array(
			'settings' => 'setMenu',
			'users'    => 'userMenu',
			'content'  => 'contMenu',
			'tools'    => 'toolMenu',
			'manage'   => 'managMenu',
			'misc'     => 'miscMenu',
		);

		foreach($expected as $category => $section)
		{
			$sections = $this->sectionsFor($category);
			self::assertArrayHasKey($section, $sections, "'".$category."' belongs in ".$section);
		}
	}
}
