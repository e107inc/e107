<?php

/**
 * @file
 * Flexpanel dashboard style.
 */

if(!defined('e107_INIT'))
{
	exit;
}

// Get "Apply dashboard preferences to all administrators" setting.
$adminPref = e107::getConfig()->get('adminpref', 0);
$flepanelEnabled = true;

// If not Main Admin and "Apply dashboard preferences to all administrators" is checked.
if(!getperms('1') && $adminPref == 1)
{
	$flepanelEnabled = false;
}

define('FLEXPANEL_ENABLED', $flepanelEnabled);


// Save rearranged menus to user.
if(e_AJAX_REQUEST)
{
	if(FLEXPANEL_ENABLED && varset($_POST['core-flexpanel-order'], false))
	{
		// If "Apply dashboard preferences to all administrators" is checked.
		if($adminPref == 1)
		{
			e107::getConfig()
				->setPosted('core-flexpanel-order', $_POST['core-flexpanel-order'])
				->save();
		}
		else
		{
			e107::getUser()
				->getConfig()
				->set('core-flexpanel-order', $_POST['core-flexpanel-order'])
				->save();
		}
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

		if(FLEXPANEL_ENABLED)
		{
			e107::css('inline', '.draggable-panels .panel-heading { cursor: move; }');
			e107::js('core', 'core/admin.flexpanel.js', 'jquery', 4);

			if(varset($_GET['mode']) == 'customize')
			{
				e107::css('inline', '.layout-container { display: table; margin-left: auto; margin-right: auto; }');
				e107::css('inline', '.layout-container label.radio { float: left; padding: 0; width: 120px; margin: 7px; cursor: pointer; text-align: center; }');
				e107::css('inline', '.layout-container label.radio img { margin-left: auto; margin-right: auto; display: block; }');
				e107::css('inline', '.layout-container label.radio input { width: 100%; margin-left: auto; margin-right: auto; display: block; }');
				e107::css('inline', '.layout-container label.radio p { width: 100%; text-align: center; display: block; margin: 20px 0 0 0; }');
			}

			// Save posted Layout type.
			if(varset($_POST['e-flexpanel-layout']))
			{
				$user_pref = $this->getUserPref();

				// If Layout has been changed, we clear previous arrangement in order to use defaults.
				if($user_pref['core-flexpanel-layout'] != $_POST['e-flexpanel-layout'])
				{
					$this->savePref('core-flexpanel-order', array());
				}

				$this->savePref('core-flexpanel-layout', $_POST['e-flexpanel-layout']);
			}
		}
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

		$user_pref = $this->getUserPref();

		if(varset($_GET['mode']) == 'customize')
		{
			echo $frm->open('infopanel', 'post', e_SELF);
			echo $ns->tablerender(LAN_DASHBOARD_LAYOUT, $this->renderLayoutPicker(), 'personalize', true);
			echo '<div class="clear">&nbsp;</div>';
			echo $this->render_infopanel_options(true);
			echo $frm->close();
			return;
		}

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
		$ns->setStyle('flexpanel');
		$ns->setUniqueId('core-infopanel_help');
		$info = $this->getMenuPosition('core-infopanel_help');
		if (!isset($panels[$info['area']][$info['weight']]))
		{
			$panels[$info['area']][$info['weight']] = '';
		}
		$panels[$info['area']][$info['weight']] .= $tp->parseTemplate('{ADMIN_HELP}', false, $admin_sc);


		// "Latest" box.
		$ns->setStyle('flexpanel');
		$info = $this->getMenuPosition('e-latest-list');
		if (!isset($panels[$info['area']][$info['weight']]))
		{
			$panels[$info['area']][$info['weight']] = '';
		}
		$panels[$info['area']][$info['weight']] .= $tp->parseTemplate('{ADMIN_LATEST=infopanel}', false, $admin_sc);


		// "Status" box.
		$ns->setStyle('flexpanel');
		$info = $this->getMenuPosition('e-status-list');
		if (!isset($panels[$info['area']][$info['weight']]))
		{
			$panels[$info['area']][$info['weight']] = '';
		}
		$panels[$info['area']][$info['weight']] .= $tp->parseTemplate('{ADMIN_STATUS=infopanel}', false, $admin_sc);


		// --------------------- Personalized Panel -----------------------
		$myE107 = varset($user_pref['core-infopanel-mye107'], array());
		if(empty($myE107)) // Set default icons.
		{
			$user_pref['core-infopanel-mye107'] = e107::getNav()->getDefaultAdminPanelArray();
		}

		$ns->setStyle('flexpanel');
		$mainPanel = "<div id='core-infopanel_mye107'>";
		$mainPanel .= "<div class='left'>";
		$count = 0;
		foreach($this->iconlist as $key => $val)
		{
			if(in_array($key, $user_pref['core-infopanel-mye107']))
			{
				if($tmp = e107::getNav()->renderAdminButton($val['link'], $val['title'], $val['caption'], $val['perms'], $val['icon_32'], "div"))
				{
					$mainPanel .= $tmp;
					$count++;
				}
				
			}

			if($count == 20)
			{
				break;
			}
		}
		$mainPanel .= "</div></div>";

		// Rendering the saved configuration.
		$ns->setStyle('flexpanel');
		$caption = $tp->lanVars(LAN_CONTROL_PANEL, ucwords(USERNAME));
		$ns->setUniqueId('core-infopanel_mye107');
		$coreInfoPanelMyE107 = $ns->tablerender($caption, $mainPanel, "core-infopanel_mye107", true);
		$info = $this->getMenuPosition('core-infopanel_mye107');
		if (!isset($panels[$info['area']][$info['weight']]))
		{
			$panels[$info['area']][$info['weight']] = '';
		}
		$panels[$info['area']][$info['weight']] .= $coreInfoPanelMyE107;


		// --------------------- e107 News --------------------------------
		$newsTabs = array();
		$newsTabs['coreFeed'] = array('caption' => LAN_GENERAL, 'text' => "<div id='e-adminfeed' style='min-height:300px'></div><div class='right'><a rel='external' href='" . ADMINFEEDMORE . "'>" . LAN_MORE . "</a></div>");
		$newsTabs['pluginFeed'] = array('caption' => LAN_PLUGIN, 'text' => "<div id='e-adminfeed-plugin'></div>");
		$newsTabs['themeFeed'] = array('caption' => LAN_THEMES, 'text' => "<div id='e-adminfeed-theme'></div>");
		$ns->setStyle('flexpanel');
		$ns->setUniqueId('core-infopanel_news');
		$coreInfoPanelNews = $ns->tablerender(LAN_LATEST_e107_NEWS, e107::getForm()->tabs($newsTabs, array('active' => 'coreFeed')), "core-infopanel_news", true);
		$info = $this->getMenuPosition('core-infopanel_news');
		if (!isset($panels[$info['area']][$info['weight']]))
		{
			$panels[$info['area']][$info['weight']] = '';
		}
		$panels[$info['area']][$info['weight']] .= $coreInfoPanelNews;


		// --------------------- Website Status ---------------------------
	/*	$ns->setStyle('flexpanel');
		$ns->setUniqueId('core-infopanel_website_status');
		$coreInfoPanelWebsiteStatus = '';// 'hi';/// "<div id='core-infopanel_website_status'>".$this->renderAddonDashboards()."</div>";  $ns->tablerender(LAN_WEBSITE_STATUS, $this->renderAddonDashboards(), "core-infopanel_website_status", true);
		$info = $this->getMenuPosition('core-infopanel_website_status');
		$panels[$info['area']][$info['weight']] .= $coreInfoPanelWebsiteStatus;*/


		// --------------------- Latest Comments --------------------------
		// $panels['Area01'] .= $this->renderLatestComments(); // TODO


		// --------------------- Add-on updates ---------------------------
		$ns->setStyle('flexpanel');
		$ns->setUniqueId('e-addon-updates');
		$addonUpdates = $admin_sc->sc_admin_addon_updates();
		$info = $this->getMenuPosition('e-addon-updates');
		if (!isset($panels[$info['area']][$info['weight']]))
		{
			$panels[$info['area']][$info['weight']] = '';
		}
		$panels[$info['area']][$info['weight']] .= $addonUpdates;


		// --------------------- User Selected Menus ----------------------
		if(varset($user_pref['core-infopanel-menus']))
		{
			$ns->setStyle('flexpanel');
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
				if (!isset($panels[$info['area']][$info['weight']]))
				{
					$panels[$info['area']][$info['weight']] = '';
				}
				$panels[$info['area']][$info['weight']] .= $inc;
			}
		}


	// --------------------- Plugin Addon Dashboards ---------------------- eg. e107_plugin/user/e_dashboard.php
		$dashboards = $this->getAddonDashboards();
		if(!empty($dashboards))
		{
			$ns->setStyle('flexpanel');
			foreach($dashboards as $val)
			{
				$id = $val['mode'];
				$ns->setUniqueId($id);
				$inc = $ns->tablerender($val['caption'], $val['text'], $val['mode'], true);
				$info = $this->getMenuPosition($id);
				if (!isset($panels[$info['area']][$info['weight']]))
				{
					$panels[$info['area']][$info['weight']] = '';
				}
				$panels[$info['area']][$info['weight']] .= $inc;
			}
		}


		// Sorting panels.
		foreach($panels as $key => $value)
		{
			ksort($panels[$key]);
		}

		$layout = varset($user_pref['core-flexpanel-layout'], 'default');
		$layout_file = e_ADMIN . 'includes/layouts/flexpanel_' . $layout . '.php';

		if(is_readable($layout_file))
		{
			include_once($layout_file);

			$template = varset($FLEXPANEL_LAYOUT);
			$template = str_replace('{MESSAGES}', $mes->render(), $template);

			foreach($panels as $key => $value)
			{
				$token = '{' . strtoupper(str_replace('-', '_', $key)) . '}';
				$template = str_replace($token, implode("\n", $value), $template);
			}

			echo $template;
		}
	}

	/**
	 * Get selected area and position for a menu item.
	 *
	 * @param $id
	 *  Menu ID.
	 * @return array
	 *  Contains menu area and weight.
	 */
	function getMenuPosition($id)
	{
		$user_pref = $this->getUserPref();

		if(!empty($user_pref['core-flexpanel-order'][$id]))
		{
			return $user_pref['core-flexpanel-order'][$id];
		}

		$default = array(
			'area'   => 'menu-area-01',
			'weight' => 1000,
		);

		$positions = $this->getDefaultPositions();

		$layout = varset($user_pref['core-flexpanel-layout'], 'default');

		if(!empty($positions[$layout][$id]))
		{
			return $positions[$layout][$id];
		}

		if(strpos($id,'plug-infopanel-') === 0) // addon dashboards default to area 2.
		{
			$default = array(
				'area'   => 'menu-area-02',
				'weight' => 1000,
			);
		}

		return $default;
	}

	/**
	 * Get default menu positions.
	 *
	 * @return array
	 */
	function getDefaultPositions()
	{
		return array(
			'two_col_bricks'    => array(
				'e-addon-updates'               => array(
					'area'   => 'menu-area-01',
					'weight' => -1,
				),
				'core-infopanel_help'           => array(
					'area'   => 'menu-area-01',
					'weight' => 0,
				),
				'e-latest-list'                 => array(
					'area'   => 'menu-area-04',
					'weight' => 1,
				),
				'e-status-list'                 => array(
					'area'   => 'menu-area-04',
					'weight' => 2,
				),
				'core-infopanel_mye107'         => array(
					'area'   => 'menu-area-02',
					'weight' => 0,
				),
				'core-infopanel_news'           => array(
					'area'   => 'menu-area-03',
					'weight' => 0,
				),
				'core-infopanel_website_status' => array(
					'area'   => 'menu-area-03',
					'weight' => 1,
				),
			),
			'two_col_stacked'   => array(
				'e-addon-updates'               => array(
					'area'   => 'menu-area-01',
					'weight' => -1,
				),
				'core-infopanel_help'           => array(
					'area'   => 'menu-area-01',
					'weight' => 0,
				),
				'e-latest-list'                 => array(
					'area'   => 'menu-area-04',
					'weight' => 0,
				),
				'e-status-list'                 => array(
					'area'   => 'menu-area-05',
					'weight' => 0,
				),
				'core-infopanel_mye107'         => array(
					'area'   => 'menu-area-02',
					'weight' => 0,
				),
				'core-infopanel_news'           => array(
					'area'   => 'menu-area-03',
					'weight' => 0,
				),
				'core-infopanel_website_status' => array(
					'area'   => 'menu-area-12',
					'weight' => 1,
				),
			),
			'three_col_bricks'  => array(
				'e-addon-updates'               => array(
					'area'   => 'menu-area-02',
					'weight' => -1,
				),
				'core-infopanel_help'           => array(
					'area'   => 'menu-area-02',
					'weight' => 0,
				),
				'e-latest-list'                 => array(
					'area'   => 'menu-area-03',
					'weight' => 0,
				),
				'e-status-list'                 => array(
					'area'   => 'menu-area-04',
					'weight' => 0,
				),
				'core-infopanel_mye107'         => array(
					'area'   => 'menu-area-01',
					'weight' => 0,
				),
				'core-infopanel_news'           => array(
					'area'   => 'menu-area-09',
					'weight' => 0,
				),
				'core-infopanel_website_status' => array(
					'area'   => 'menu-area-13',
					'weight' => 0,
				),
			),
			'three_col_stacked' => array(
				'e-addon-updates'               => array(
					'area'   => 'menu-area-03',
					'weight' => -1,
				),
				'core-infopanel_help'           => array(
					'area'   => 'menu-area-03',
					'weight' => 0,
				),
				'e-latest-list'                 => array(
					'area'   => 'menu-area-04',
					'weight' => 0,
				),
				'e-status-list'                 => array(
					'area'   => 'menu-area-05',
					'weight' => 0,
				),
				'core-infopanel_mye107'         => array(
					'area'   => 'menu-area-02',
					'weight' => 0,
				),
				'core-infopanel_news'           => array(
					'area'   => 'menu-area-12',
					'weight' => 0,
				),
				'core-infopanel_website_status' => array(
					'area'   => 'menu-area-13',
					'weight' => 0,
				),
			),
			'one_col'           => array(
				'e-addon-updates'               => array(
					'area'   => 'menu-area-01',
					'weight' => -1,
				),
				'core-infopanel_help'           => array(
					'area'   => 'menu-area-01',
					'weight' => 0,
				),
				'e-latest-list'                 => array(
					'area'   => 'menu-area-02',
					'weight' => 0,
				),
				'e-status-list'                 => array(
					'area'   => 'menu-area-03',
					'weight' => 0,
				),
				'core-infopanel_mye107'         => array(
					'area'   => 'menu-area-04',
					'weight' => 0,
				),
				'core-infopanel_news'           => array(
					'area'   => 'menu-area-05',
					'weight' => 0,
				),
				'core-infopanel_website_status' => array(
					'area'   => 'menu-area-06',
					'weight' => 0,
				),
			),
			'wider_sidebar'     => array(
				'e-addon-updates'               => array(
					'area'   => 'menu-area-01',
					'weight' => -1,
				),
				'core-infopanel_help'           => array(
					'area'   => 'menu-area-01',
					'weight' => 0,
				),
				'e-latest-list'                 => array(
					'area'   => 'menu-area-01',
					'weight' => 1,
				),
				'e-status-list'                 => array(
					'area'   => 'menu-area-01',
					'weight' => 2,
				),
				'core-infopanel_mye107'         => array(
					'area'   => 'menu-area-07',
					'weight' => 0,
				),
				'core-infopanel_news'           => array(
					'area'   => 'menu-area-08',
					'weight' => 0,
				),
				'core-infopanel_website_status' => array(
					'area'   => 'menu-area-08',
					'weight' => 1,
				),
			),
			'default'           => array(
				'e-addon-updates'               => array(
					'area'   => 'menu-area-01',
					'weight' => -1,
				),
				'core-infopanel_help'           => array(
					'area'   => 'menu-area-01',
					'weight' => 0,
				),
				'e-latest-list'                 => array(
					'area'   => 'menu-area-01',
					'weight' => 1,
				),
				'e-status-list'                 => array(
					'area'   => 'menu-area-01',
					'weight' => 2,
				),
				'core-infopanel_mye107'         => array(
					'area'   => 'menu-area-07',
					'weight' => 0,
				),
				'core-infopanel_news'           => array(
					'area'   => 'menu-area-08',
					'weight' => 0,
				),
				'core-infopanel_website_status' => array(
					'area'   => 'menu-area-08',
					'weight' => 1,
				),
			),
		);
	}

	/**
	 * Render layout-picker widget.
	 *
	 * @return string
	 */
	function renderLayoutPicker()
	{
		$tp = e107::getParser();
		$fr = e107::getForm();
		$fl = e107::getFile();

		$user_pref = $this->getUserPref();

		$default = varset($user_pref['core-flexpanel-layout'], 'default');

		$html = '<div class="layout-container">';

		$layouts = array(
			'default',
			'wider_sidebar',
			'two_col_bricks',
			'two_col_stacked',
			'three_col_bricks',
			'three_col_stacked',
			'one_col',
		);

		$files = $fl->get_files(e_ADMIN . 'includes/layouts/', "flexpanel_(.*).php", "standard", 1);
		foreach($files as $num => $val)
		{
			$filename = basename($val['fname']);
			$layout = str_replace('flexpanel_', '', $filename);
			$layout = str_replace('.php', '', $layout);

			if(!in_array($layout, $layouts))
			{
				$layouts[] = $layout;
			}
		}

		foreach($layouts as $layout)
		{
			$html .= '<label class="radio">';
			$html .= $tp->toImage('{e_ADMIN}includes/layouts/flexpanel_' . $layout . '.png', array(
				'legacy' => '{e_ADMIN}includes/layouts/',
				'w'      => 75,
			));
			$checked = ($default == $layout);
			$html .= $fr->radio('e-flexpanel-layout', $layout, $checked);
			$name = str_replace('_', ' ', $layout);
			$html .= '<p>' . ucwords($name) . '</p>';
			$html .= '</label>';
		}

		$html .= '<div class="clear"></div>';
		$html .= '</div>';

		return $html;
	}

}
