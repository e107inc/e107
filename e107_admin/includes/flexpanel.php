<?php

/**
 * @file
 *
 */

if(!defined('e107_INIT'))
{
	exit;
}

if(e_AJAX_REQUEST)
{
	if(varset($_POST['core-flexpanel-order'], false))
	{
		global $user_pref;
		$user_pref['core-flexpanel-order'] = $_POST['core-flexpanel-order'];
		save_prefs('user');
		exit;
	}
}

// Flexpanel uses infopanel's methods to avoid code duplication.
e107_require_once(e_ADMIN . 'includes/infopanel.php');


/**
 * Class adminstyle_flexpanel.
 */
class adminstyle_flexpanel extends adminstyle_infopanel
{

	private $iconlist = array();

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->iconlist = $this->getIconList();

		e107::js('core', 'core/admin.flexpanel.js', 'jquery', 4);
	}

	/**
	 * Render contents.
	 */
	public function render()
	{
		$admin_sc = e107::getScBatch('admin');
		$tp = e107::getParser();
		$ns = e107::getRender();
		$mes = e107::getMessage();
		$pref = e107::getPref();
		$frm = e107::getForm();

		global $user_pref;

		// Default menu areas.
		$panels = array(
			'menu-area-01' => array(), // Sidebar.
			'menu-area-02' => array(),
			'menu-area-03' => array(),
			'menu-area-04' => array(),
			'menu-area-05' => array(),
			'menu-area-06' => array(),
			'menu-area-07' => array(), // Content left.
			'menu-area-08' => array(), // Content right.
			'menu-area-09' => array(),
			'menu-area-10' => array(),
			'menu-area-11' => array(),
			'menu-area-12' => array(),
			'menu-area-13' => array(),
		);


		// "Help" box.
		$tp->parseTemplate("{SETSTYLE=flexpanel}");
		$ns->setUniqueId('core-infopanel_help');
		$info = $this->getMenuPosition('core-infopanel_help');
		$panels[$info['area']][$info['weight']] .= $tp->parseTemplate('{ADMIN_HELP}', true, $admin_sc);

		// "Latest" box.
		$tp->parseTemplate("{SETSTYLE=flexpanel}");
		$info = $this->getMenuPosition('e-latest-list');
		$panels[$info['area']][$info['weight']] .= $tp->parseTemplate('{ADMIN_LATEST=infopanel}', true, $admin_sc);

		// "Status" box.
		$tp->parseTemplate("{SETSTYLE=flexpanel}");
		$info = $this->getMenuPosition('e-status-list');
		$panels[$info['area']][$info['weight']] .= $tp->parseTemplate('{ADMIN_STATUS=infopanel}', true, $admin_sc);


		// --------------------- Personalized Panel -----------------------
		if(getperms('0') && !vartrue($user_pref['core-infopanel-mye107'])) // Set default icons.
		{
			$defArray = array(
				0  => 'e-administrator',
				1  => 'e-cpage',
				2  => 'e-frontpage',
				3  => 'e-mailout',
				4  => 'e-image',
				5  => 'e-menus',
				6  => 'e-meta',
				7  => 'e-newspost',
				8  => 'e-plugin',
				9  => 'e-prefs',
				10 => 'e-links',
				11 => 'e-theme',
				12 => 'e-userclass2',
				13 => 'e-users',
				14 => 'e-wmessage'
			);
			$user_pref['core-infopanel-mye107'] = vartrue($pref['core-infopanel-default'], $defArray);
		}

		$tp->parseTemplate("{SETSTYLE=flexpanel}");

		$mainPanel = "<div id='core-infopanel_mye107'>";
		$mainPanel .= "<div class='left'>";
		foreach($this->iconlist as $key => $val)
		{
			if(!vartrue($user_pref['core-infopanel-mye107']) || in_array($key, $user_pref['core-infopanel-mye107']))
			{
				$mainPanel .= e107::getNav()->renderAdminButton($val['link'], $val['title'], $val['caption'], $val['perms'], $val['icon_32'], "div");
			}
		}
		$mainPanel .= "</div></div>";
		// Rendering the saved configuration.

		$tp->parseTemplate("{SETSTYLE=flexpanel}");

		$caption = $tp->lanVars(LAN_CONTROL_PANEL, ucwords(USERNAME));
		$ns->setUniqueId('core-infopanel_mye107');
		$coreInfoPanelMyE107 = $ns->tablerender($caption, $mainPanel, "core-infopanel_mye107", true);
		$info = $this->getMenuPosition('core-infopanel_mye107');
		$panels[$info['area']][$info['weight']] .= $coreInfoPanelMyE107;


		// --------------------- e107 News --------------------------------
		$newsTabs = array();
		$newsTabs['coreFeed'] = array('caption' => LAN_GENERAL, 'text' => "<div id='e-adminfeed' style='min-height:300px'></div><div class='right'><a rel='external' href='" . ADMINFEEDMORE . "'>" . LAN_MORE . "</a></div>");
		$newsTabs['pluginFeed'] = array('caption' => LAN_PLUGIN, 'text' => "<div id='e-adminfeed-plugin'></div>");
		$newsTabs['themeFeed'] = array('caption' => LAN_THEMES, 'text' => "<div id='e-adminfeed-theme'></div>");
		$ns->setUniqueId('core-infopanel_news');
		$coreInfoPanelNews = $ns->tablerender(LAN_LATEST_e107_NEWS, e107::getForm()->tabs($newsTabs, array('active' => 'coreFeed')), "core-infopanel_news", true);
		$info = $this->getMenuPosition('core-infopanel_news');
		$panels[$info['area']][$info['weight']] .= $coreInfoPanelNews;


		// --------------------- Website Status ---------------------------
		$ns->setUniqueId('core-infopanel_website_status');
		$coreInfoPanelWebsiteStatus = $ns->tablerender(LAN_WEBSITE_STATUS, $this->renderWebsiteStatus(), "core-infopanel_website_status", true);
		$info = $this->getMenuPosition('core-infopanel_website_status');
		$panels[$info['area']][$info['weight']] .= $coreInfoPanelWebsiteStatus;


		// --------------------- Latest Comments --------------------------
		// $panels['Area01'] .= $this->renderLatestComments(); // TODO


		// --------------------- User Selected Menus ----------------------
		if(varset($user_pref['core-infopanel-menus']))
		{
			foreach($user_pref['core-infopanel-menus'] as $val)
			{
				// Custom menu.
				if(is_numeric($val))
				{
					$menu = e107::getDb()->retrieve('page', 'menu_name', 'page_id = ' . (int) $val);
					$id = 'cmenu-' . $menu;
					$inc = e107::getMenu()->renderMenu($val, null, null, true);
				}
				else
				{
					$id = $frm->name2id($val);
					$inc = $tp->parseTemplate("{PLUGIN=$val|TRUE}");
				}

				$info = $this->getMenuPosition($id);
				$panels[$info['area']][$info['weight']] .= $inc;
			}
		}

		ksort($panels['menu-area-01']);
		ksort($panels['menu-area-02']);
		ksort($panels['menu-area-03']);
		ksort($panels['menu-area-04']);
		ksort($panels['menu-area-05']);
		ksort($panels['menu-area-06']);
		ksort($panels['menu-area-07']);
		ksort($panels['menu-area-08']);
		ksort($panels['menu-area-09']);
		ksort($panels['menu-area-10']);
		ksort($panels['menu-area-11']);
		ksort($panels['menu-area-12']);
		ksort($panels['menu-area-13']);

		// Sidebar.
		echo '<div class="row">';
		echo '<div class="col-md-3 col-lg-2" id="left-panel">';
		echo '<div class="draggable-panels" id="menu-area-01">';
		echo implode("\n", $panels['menu-area-01']);
		echo '</div>';
		echo '</div>';
		echo '<div class="col-md-9 col-lg-10" id="right-panel">';


		if(vartrue($_GET['mode']) != 'customize')
		{
			echo '<div class="row">';
			echo '<div class="col-sm-12">';
			echo $mes->render();
			echo '</div>';
			echo '</div>';


			echo '<div class="row">';
			echo '<div class="col-sm-12">';
			echo '<div class="draggable-panels" id="menu-area-02">';
			echo implode("\n", $panels['menu-area-02']);
			echo '</div>';
			echo '</div>';
			echo '</div>';


			echo '<div class="row">';
			echo '<div class="col-sm-4">';
			echo '<div class="draggable-panels" id="menu-area-03">';
			echo implode("\n", $panels['menu-area-03']);
			echo '</div>';
			echo '</div>';
			echo '<div class="col-sm-4">';
			echo '<div class="draggable-panels" id="menu-area-04">';
			echo implode("\n", $panels['menu-area-04']);
			echo '</div>';
			echo '</div>';
			echo '<div class="col-sm-4">';
			echo '<div class="draggable-panels" id="menu-area-05">';
			echo implode("\n", $panels['menu-area-05']);
			echo '</div>';
			echo '</div>';
			echo '</div>';


			echo '<div class="row">';
			echo '<div class="col-sm-12">';
			echo '<div class="draggable-panels" id="menu-area-06">';
			echo implode("\n", $panels['menu-area-06']);
			echo '</div>';
			echo '</div>';
			echo '</div>';


			echo '<div class="row">';
			echo '<div class="col-sm-6">';
			echo '<div class="draggable-panels" id="menu-area-07">';
			echo implode("\n", $panels['menu-area-07']);
			echo '</div>';
			echo '</div>';
			echo '<div class="col-sm-6">';
			echo '<div class="draggable-panels" id="menu-area-08">';
			echo implode("\n", $panels['menu-area-08']);
			echo '</div>';
			echo '</div>';
			echo '</div>';


			echo '<div class="row">';
			echo '<div class="col-sm-12">';
			echo '<div class="draggable-panels" id="menu-area-09">';
			echo implode("\n", $panels['menu-area-09']);
			echo '</div>';
			echo '</div>';
			echo '</div>';


			echo '<div class="row">';
			echo '<div class="col-sm-4">';
			echo '<div class="draggable-panels" id="menu-area-10">';
			echo implode("\n", $panels['menu-area-10']);
			echo '</div>';
			echo '</div>';
			echo '<div class="col-sm-4">';
			echo '<div class="draggable-panels" id="menu-area-11">';
			echo implode("\n", $panels['menu-area-11']);
			echo '</div>';
			echo '</div>';
			echo '<div class="col-sm-4">';
			echo '<div class="draggable-panels" id="menu-area-12">';
			echo implode("\n", $panels['menu-area-12']);
			echo '</div>';
			echo '</div>';
			echo '</div>';


			echo '<div class="row">';
			echo '<div class="col-sm-12">';
			echo '<div class="draggable-panels" id="menu-area-13">';
			echo implode("\n", $panels['menu-area-13']);
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
		else
		{
			echo $frm->open('infopanel', 'post', e_SELF);
			echo $this->render_infopanel_options(true);
			echo $frm->close();
		}

		echo '</div>';
		echo '</div>';
	}


	function getMenuPosition($id)
	{
		global $user_pref;
		
		if(varset($user_pref['core-flexpanel-order'][$id]))
		{
			return $user_pref['core-flexpanel-order'][$id];
		}

		$default = array(
			'area'   => 'menu-area-01',
			'weight' => 1000,
		);

		if($id == 'core-infopanel_help')
		{
			$default['area'] = 'menu-area-01';
			$default['weight'] = 0;
		}

		if($id == 'e-latest-list')
		{
			$default['area'] = 'menu-area-01';
			$default['weight'] = 1;
		}

		if($id == 'e-status-list')
		{
			$default['area'] = 'menu-area-01';
			$default['weight'] = 2;
		}

		if($id == 'core-infopanel_mye107')
		{
			$default['area'] = 'menu-area-07';
			$default['weight'] = 0;
		}

		if($id == 'core-infopanel_news')
		{
			$default['area'] = 'menu-area-08';
			$default['weight'] = 0;
		}

		if($id == 'core-infopanel_website_status')
		{
			$default['area'] = 'menu-area-08';
			$default['weight'] = 1;
		}

		return $default;
	}

}
