<?php

/**
 * @file
 *
 */

if(!defined('e107_INIT'))
{
	exit;
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

		$panels = array(
			'Area01' => '', // Sidebar.
			'Area02' => '',
			'Area03' => '',
			'Area04' => '',
			'Area05' => '',
			'Area06' => '',
			'Area07' => '', // Content left.
			'Area08' => '', // Content right.
			'Area09' => '',
			'Area10' => '',
			'Area11' => '',
			'Area12' => '',
			'Area13' => '',
		);


		// "Help" box.
		$tp->parseTemplate("{SETSTYLE=flexpanel}");
		$panels['Area01'] .= $tp->parseTemplate('{ADMIN_HELP}', true, $admin_sc);
		
		// "Latest" box.
		$tp->parseTemplate("{SETSTYLE=flexpanel}");
		$panels['Area01'] .= $tp->parseTemplate('{ADMIN_LATEST=infopanel}', true, $admin_sc);
		
		// "Status" box.
		$tp->parseTemplate("{SETSTYLE=flexpanel}");
		$panels['Area01'] .= $tp->parseTemplate('{ADMIN_STATUS=infopanel}', true, $admin_sc);
		

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
		$coreInfoPanelMyE107 = $ns->tablerender($caption, $mainPanel, "core-my-e107", true);
		$panels['Area07'] .= $coreInfoPanelMyE107;


		// --------------------- e107 News --------------------------------
		$newsTabs = array();
		$newsTabs['coreFeed'] = array('caption' => LAN_GENERAL, 'text' => "<div id='e-adminfeed' style='min-height:300px'></div><div class='right'><a rel='external' href='" . ADMINFEEDMORE . "'>" . LAN_MORE . "</a></div>");
		$newsTabs['pluginFeed'] = array('caption' => LAN_PLUGIN, 'text' => "<div id='e-adminfeed-plugin'></div>");
		$newsTabs['themeFeed'] = array('caption' => LAN_THEMES, 'text' => "<div id='e-adminfeed-theme'></div>");

		$coreInfoPanelNews = $ns->tablerender(LAN_LATEST_e107_NEWS, e107::getForm()->tabs($newsTabs, array('active' => 'coreFeed')), "core-e107-news", true);
		$panels['Area08'] .= $coreInfoPanelNews;


		// --------------------- Website Status ---------------------------
		$coreInfoPanelWebsiteStatus = $ns->tablerender(LAN_WEBSITE_STATUS, $this->renderWebsiteStatus(), "core-website-status", true);
		$panels['Area08'] .= $coreInfoPanelWebsiteStatus;


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
					$inc = e107::getMenu()->renderMenu($val, null, null, true);
				}
				else
				{
					$inc = $tp->parseTemplate("{PLUGIN=$val|TRUE}");
				}

				$panels['Area01'] .= $inc;
			}
		}

		// Sidebar.
		echo '<div class="row">';
		echo '<div class="col-md-3 col-lg-2" id="left-panel">';
		echo '<div class="draggable-panels" id="menu-area-01">';
		echo $panels['Area01'];
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
			echo $panels['Area02'];
			echo '</div>';
			echo '</div>';
			echo '</div>';


			echo '<div class="row">';
			echo '<div class="col-sm-4">';
			echo '<div class="draggable-panels" id="menu-area-03">';
			echo $panels['Area03'];
			echo '</div>';
			echo '</div>';
			echo '<div class="col-sm-4">';
			echo '<div class="draggable-panels" id="menu-area-04">';
			echo $panels['Area04'];
			echo '</div>';
			echo '</div>';
			echo '<div class="col-sm-4">';
			echo '<div class="draggable-panels" id="menu-area-05">';
			echo $panels['Area05'];
			echo '</div>';
			echo '</div>';
			echo '</div>';


			echo '<div class="row">';
			echo '<div class="col-sm-12">';
			echo '<div class="draggable-panels" id="menu-area-06">';
			echo $panels['Area06'];
			echo '</div>';
			echo '</div>';
			echo '</div>';


			echo '<div class="row">';
			echo '<div class="col-sm-6">';
			echo '<div class="draggable-panels" id="menu-area-07">';
			echo $panels['Area07'];
			echo '</div>';
			echo '</div>';
			echo '<div class="col-sm-6">';
			echo '<div class="draggable-panels" id="menu-area-08">';
			echo $panels['Area08'];
			echo '</div>';
			echo '</div>';
			echo '</div>';


			echo '<div class="row">';
			echo '<div class="col-sm-12">';
			echo '<div class="draggable-panels" id="menu-area-09">';
			echo $panels['Area09'];
			echo '</div>';
			echo '</div>';
			echo '</div>';


			echo '<div class="row">';
			echo '<div class="col-sm-4">';
			echo '<div class="draggable-panels" id="menu-area-10">';
			echo $panels['Area10'];
			echo '</div>';
			echo '</div>';
			echo '<div class="col-sm-4">';
			echo '<div class="draggable-panels" id="menu-area-11">';
			echo $panels['Area11'];
			echo '</div>';
			echo '</div>';
			echo '<div class="col-sm-4">';
			echo '<div class="draggable-panels" id="menu-area-12">';
			echo $panels['Area12'];
			echo '</div>';
			echo '</div>';
			echo '</div>';


			echo '<div class="row">';
			echo '<div class="col-sm-12">';
			echo '<div class="draggable-panels" id="menu-area-13">';
			echo $panels['Area13'];
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

}
