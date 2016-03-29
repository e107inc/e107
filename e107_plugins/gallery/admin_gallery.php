<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/download/admin_download.php,v $
 * $Revision: 12639 $
 * $Date: 2012-04-20 00:28:53 -0700 (Fri, 20 Apr 2012) $
 * $Author: e107coders $
 */

// TODO: prefs for prettyPhoto library, e.g. theme settings

$eplug_admin = true;

require_once("../../class2.php");

if(!getperms("P") || !e107::isInstalled('gallery'))
{
	e107::redirect('admin');
	exit();
}

// [PLUGINS]/gallery/languages/[LANGUAGE]/[LANGUAGE]_admin.php
e107::lan('gallery', true, true);

$e_sub_cat = 'gallery';


class plugin_gallery_admin extends e_admin_dispatcher
{

	/**
	 * Format: 'MODE' => array('controller' =>'CONTROLLER_CLASS'[, 'index' => 'list', 'path' => 'CONTROLLER SCRIPT PATH', 'ui' => 'UI CLASS NAME child of e_admin_ui', 'uipath' => 'UI SCRIPT PATH']);
	 * Note - default mode/action is autodetected in this order:
	 * - $defaultMode/$defaultAction (owned by dispatcher - see below)
	 * - $adminMenu (first key if admin menu array is not empty)
	 * - $modes (first key == mode, corresponding 'index' key == action)
	 * @var array
	 */
	protected $modes = array(
		'main' => array(
			'controller' => 'gallery_cat_admin_ui',
			'path'       => null,
			'ui'         => 'gallery_cat_admin_form_ui',
			'uipath'     => null
		),
		'cat'  => array(
			'controller' => 'gallery_cat_ui',
			'path'       => null,
			'ui'         => 'gallery_cat_form_ui',
			'uipath'     => null
		)
	);

	/**
	 * Format: 'MODE/ACTION' => array('caption' => 'Menu link title'[, 'url' => '{e_PLUGIN}release/admin_config.php', 'perm' => '0']);
	 * Additionally, any valid e107::getNav()->admin() key-value pair could be added to the above array
	 * @var array
	 */
	protected $adminMenu = array(
		'main/prefs' => array('caption' => LAN_PREFS, 'perm' => 'P')
	);

	/**
	 * Navigation menu title
	 * @var string
	 */
	protected $menuTitle = LAN_PLUGIN_GALLERY_TITLE;

	function init()
	{
		if(E107_DEBUG_LEVEL > 0)
		{
			$this->adminMenu['main/list'] = array('caption' => LAN_CATEGORY, 'perm' => 'P');
		}
	}
}


class gallery_cat_admin_ui extends e_admin_ui
{

	protected $pluginTitle = LAN_PLUGIN_GALLERY_TITLE;
	protected $pluginName  = 'gallery';
	protected $table       = "core_media_cat";
	protected $pid         = "media_cat_id";
	protected $perPage     = 10; // No limit.
	protected $listOrder   = 'media_cat_order';

	protected $listQry = "SELECT * FROM `#core_media_cat` WHERE media_cat_owner = 'gallery' "; // Without any Order or Limit.

	protected $fields = array(
		'checkboxes'         => array(
			'title'   => '',
			'type'    => null,
			'width'   => '5%',
			'forced'  => true,
			'thclass' => 'center',
			'class'   => 'center',
		),
		/*
		'media_cat_id'       => array(
			'title'    => LAN_ID,
			'type'     => 'number',
			'width'    => '5%',
			'forced'   => true,
			'readonly' => true,
		),
		*/
		'media_cat_image'    => array(
			'title'     => LAN_IMAGE,
			'type'      => 'image',
			'data'      => 'str',
			'width'     => '100px',
			'thclass'   => 'center',
			'class'     => 'center',
			'readParms' => 'thumb=60&thumb_urlraw=0&thumb_aw=60',
			'readonly'  => false,
			'batch'     => false,
			'filter'    => false,
		),
		'media_cat_owner'    => array(
			'title'      => LAN_OWNER,
			'type'       => 'hidden',
			'nolist'     => true,
			'width'      => 'auto',
			'thclass'    => 'left',
			'readonly'   => false,
			'writeParms' => 'value=gallery',
		),
		'media_cat_category' => array(
			'title'    => LAN_CATEGORY,
			'type'     => 'hidden',
			'nolist'   => true,
			'width'    => 'auto',
			'thclass'  => 'left',
			'readonly' => true,
		),
		'media_cat_title'    => array(
			'title'    => LAN_TITLE,
			'type'     => 'text',
			'width'    => 'auto',
			'thclass'  => 'left',
			'readonly' => false,
		),
		'media_cat_sef'      => array(
			'title'   => LAN_SEFURL,
			'type'    => 'text',
			'inline'  => true,
			'width'   => 'auto',
			'thclass' => 'left',
		),
		'media_cat_diz'      => array(
			'title'     => LAN_DESCRIPTION,
			'type'      => 'bbarea',
			'width'     => '30%',
			'readParms' => 'expand=...&truncate=150&bb=1',
			'readonly'  => false,
		),
		'media_cat_class'    => array(
			'title'  => LAN_VISIBILITY,
			'type'   => 'userclass',
			'width'  => 'auto',
			'data'   => 'int',
			'filter' => true,
			'batch'  => true,
		),
		'media_cat_order'    => array(
			'title'   => LAN_ORDER,
			'type'    => 'text',
			'width'   => 'auto',
			'thclass' => 'center',
			'class'   => 'center',
		),
		'options'            => array(
			'title'   => LAN_OPTIONS,
			'type'    => null,
			'width'   => '5%',
			'forced'  => true,
			'thclass' => 'center last',
			'class'   => 'right',
		),
	);


	public function beforeCreate($new_data)
	{

		$replace = array("_", " ", "'", '"', "."); //FIXME Improve
		$new_data['media_cat_category'] = strtolower(str_replace($replace, "", $new_data['media_cat_title']));
		return $new_data;
	}

	function galleryPage()
	{
		$mes = e107::getMessage();
		$tp = e107::getParser();

		$x = LAN_PLUGIN_GALLERY_TITLE;
		$y = "<a href='" . e_ADMIN . "image.php'>" . LAN_MEDIAMANAGER . "</a>";

		$message = $tp->lanVars(LAN_GALLERY_ADMIN_01, array($x, $y), true);
		$mes->addInfo($message);
	}

	protected $preftabs = array(LAN_GALLERY_ADMIN_02, LAN_GALLERY_ADMIN_03);

	protected $prefs = array(
		'popup_w'            => array(
			'title' => LAN_GALLERY_ADMIN_04,
			'tab'   => 0,
			'type'  => 'text',
			'data'  => 'int',
			'help'  => LAN_GALLERY_ADMIN_05,
			// 'validate' => 'regex',
			// 'rule' => '#^[\d]+$#i',
			// 'help' => 'allowed characters are a-zA-Z and underscore',
		),
		'popup_h'            => array(
			'title' => LAN_GALLERY_ADMIN_06,
			'tab'   => 0,
			'type'  => 'text',
			'data'  => 'int',
			'help'  => LAN_GALLERY_ADMIN_07,
			// 'validate' => 'regex',
			// 'rule' => '#^[\d]+$#i',
			// 'help' => 'allowed characters are a-zA-Z and underscore',
		),
		'downloadable'       => array(
			'title' => LAN_GALLERY_ADMIN_08,
			'tab'   => 0,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => LAN_GALLERY_ADMIN_09,
			// 'validate' => 'regex',
			// 'rule' => '#^[\d]+$#i',
			// 'help' => 'allowed characters are a-zA-Z and underscore',
		),
		'slideshow_category' => array(
			'title' => LAN_GALLERY_ADMIN_10,
			'tab'   => 1,
			'type'  => 'dropdown',
			'data'  => 'str',
			'help'  => LAN_GALLERY_ADMIN_11,
			// 'validate' => 'regex',
			// 'rule' => '#^[\d]+$#i',
			// 'help' => 'allowed characters are a-zA-Z and underscore',
		),
		/*
		'slideshow_thumb_w'  => array(
			'title' => 'Thumbnail Width',
			'type'  => 'number',
			'data'  => 'integer',
			'help'  => 'Width in px',
			// 'validate' => 'regex',
			// 'rule' => '#^[\d]+$#i',
			// 'help' => 'allowed characters are a-zA-Z and underscore',
		),
		*/
		/*
		'slideshow_thumb_h'  => array(
			'title' => 'Thumbnail Height',
			'type'  => 'number',
			'data'  => 'integer',
			'help'  => 'Height in px',
			// 'validate' => 'regex',
			// 'rule' => '#^[\d]+$#i',
			// 'help' => 'allowed characters are a-zA-Z and underscore',
		),
		*/
		/*
		'slideshow_perslide' => array(
			'title' => 'Images per slide',
			'type'  => 'number',
			'data'  => 'integer',
			'help'  => 'Number of images to show per slide.',
			// 'validate' => 'regex',
			// 'rule' => '#^[\d]+$#i',
			// 'help' => 'allowed characters are a-zA-Z and underscore',
		),
		*/
		'slideshow_duration' => array(
			'title' => LAN_GALLERY_ADMIN_12,
			'type'  => 'number',
			'tab'   => 1,
			'data'  => 'integer',
			'help'  => LAN_GALLERY_ADMIN_13,
			// 'validate' => 'regex',
			// 'rule' => '#^[\d]+$#i',
			// 'help' => 'allowed characters are a-zA-Z and underscore',
		),
		'slideshow_auto'     => array(
			'title' => LAN_GALLERY_ADMIN_14,
			'type'  => 'boolean',
			'tab'   => 1,
			'data'  => 'integer',
			'help'  => LAN_GALLERY_ADMIN_15,
		),
		'slideshow_freq'     => array(
			'title' => LAN_GALLERY_ADMIN_16,
			'type'  => 'number',
			'tab'   => 1,
			'data'  => 'integer',
			'help'  => LAN_GALLERY_ADMIN_17,
			// 'validate' => 'regex',
			// 'rule' => '#^[\d]+$#i',
			// 'help' => 'allowed characters are a-zA-Z and underscore',
		),
		/*
		'slideshow_circular' => array(
			'title' => 'Slide circular-mode',
			'type'  => 'boolean',
			'data'  => 'integer',
			'help'  => 'By default when the first/last slide is reached, calling prev/next does nothing. If you want the effect to continue enable this option.',
		),
		*/
		'slideshow_effect'   => array(
			'title' => LAN_GALLERY_ADMIN_18,
			'type'  => 'dropdown',
			'tab'   => 1,
			'data'  => 'str',
			'help'  => LAN_GALLERY_ADMIN_19
		),
		/*
		'slideshow_transition' => array(
			'title' => 'Slide transition',
			'type'  => 'dropdown',
			'data'  => 'str',
			'help'  => 'Type of transition.',
		),
		*/
		'perpage'            => array(
			'title' => LAN_GALLERY_ADMIN_20,
			'tab'   => 0,
			'type'  => 'number',
			'data'  => 'int',
			'help'  => LAN_GALLERY_ADMIN_21,
			// 'rule' => '#^[\d]+$#i',
			// 'help' => 'allowed characters are a-zA-Z and underscore',
		),
		'orderby'            => array(
			'title'      => LAN_GALLERY_ADMIN_22,
			'tab'        => 0,
			'type'       => 'dropdown',
			'data'       => 'str',
			'writeParms' => array(
				'optArray' => array(
					'media_id ASC'       => LAN_GALLERY_ADMIN_23,
					'media_id DESC'      => LAN_GALLERY_ADMIN_24,
					'media_name ASC'     => LAN_GALLERY_ADMIN_25,
					'media_name DESC'    => LAN_GALLERY_ADMIN_26,
					'media_caption ASC'  => LAN_GALLERY_ADMIN_27,
					'media_caption DESC' => LAN_GALLERY_ADMIN_28,
				),
			),
		),
	);


	function init()
	{
		$effects = array(
			'scrollHorz' => LAN_GALLERY_ADMIN_29,
			'scrollVert' => LAN_GALLERY_ADMIN_30,
			'fade'       => LAN_GALLERY_ADMIN_31,
		);

		$this->prefs['slideshow_effect']['writeParms'] = $effects;
		$this->prefs['slideshow_effect']['readParms'] = $effects;

		//	$transitions = array('sinoidal'=>'sinoidal','spring'=>'spring');

		//	$this->prefs['slideshow_transition']['writeParms'] 	= $transitions;
		//	$this->prefs['slideshow_transition']['readParms'] 	= $transitions;

		$categories = e107::getMedia()->getCategories('gallery');
		$cats = array();
		foreach($categories as $k => $var)
		{
			$id = preg_replace("/[^0-9]/", '', $k);
			$cats[$id] = $var['media_cat_title'];
		}

		$this->prefs['slideshow_category']['writeParms'] = $cats;
		$this->prefs['slideshow_category']['readParms'] = $cats;

		$mes = e107::getMessage();
		$tp = e107::getParser();

		$x = LAN_PLUGIN_GALLERY_TITLE;
		$y = "<a href='" . e_ADMIN . "image.php'>" . LAN_MEDIAMANAGER . "</a>";

		$message = $tp->lanVars(LAN_GALLERY_ADMIN_01, array($x, $y), true);
		$mes->addInfo($message);
	}

}


class gallery_cat_admin_form_ui extends e_admin_form_ui
{

	// Override the default Options field.
	public function gallery_category_parent($curVal, $mode)
	{
		// TODO - catlist combo without current cat ID in write mode, parents only for batch/filter 
		// Get UI instance
		$controller = $this->getController();
		switch($mode)
		{
			case 'read':
				return e107::getParser()->toHTML($controller->getDownloadCategoryTree($curVal), false, 'TITLE');
				break;

			case 'write':
				return $this->selectbox('gallery_category_parent', $controller->getDownloadCategoryTree(), $curVal);
				break;

			case 'filter':
			case 'batch':
				return $controller->getDownloadCategoryTree();
				break;
		}
	}
}


class gallery_main_admin_ui extends e_admin_ui
{


}


class gallery_main_admin_form_ui extends e_admin_form_ui
{


}


new plugin_gallery_admin();
require_once(e_ADMIN . "auth.php");
e107::getAdminUI()->runPage(); //gallery/includes/admin.php is auto-loaded.
require_once(e_ADMIN . "footer.php");
exit;
