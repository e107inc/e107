<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * Class installations to handle configuration forms on Admin UI.
 */

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


/**
 * Class plugin_gallery_admin.
 */
class plugin_gallery_admin extends e_admin_dispatcher
{

	/**
	 * Required (set by child class).
	 *
	 * Controller map array in format.
	 * @code
	 *  'MODE' => array(
	 *      'controller' =>'CONTROLLER_CLASS_NAME',
	 *      'path' => 'CONTROLLER SCRIPT PATH',
	 *      'ui' => 'UI_CLASS', // extend of 'comments_admin_form_ui'
	 *      'uipath' => 'path/to/ui/',
	 *  );
	 * @endcode
	 *
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
	 * Optional (set by child class).
	 *
	 * Required for admin menu render. Format:
	 * @code
	 *  'mode/action' => array(
	 *      'caption' => 'Link title',
	 *      'perm' => '0',
	 *      'url' => '{e_PLUGIN}plugname/admin_config.php',
	 *      ...
	 *  );
	 * @endcode
	 *
	 * Note that 'perm' and 'userclass' restrictions are inherited from the $modes, $access and $perm, so you don't
	 * have to set that vars if you don't need any additional 'visual' control.
	 *
	 * All valid key-value pair (see e107::getNav()->admin function) are accepted.
	 *
	 * @var array
	 */
	protected $adminMenu = array(
		'main/prefs' => array('caption' => LAN_PREFS, 'perm' => 'P'),
		'main/list'  => array('caption' => LAN_CATEGORIES, 'perm'    => 'P'),
		'main/create'  => array('caption' => LAN_CREATE, 'perm'    => 'P'),
	);

	/**
	 * Optional (set by child class).
	 *
	 * @var string
	 */
	protected $menuTitle = LAN_PLUGIN_GALLERY_TITLE;

	/**
	 * Initial function.
	 */
	function init()
	{

		if(E107_DEBUG_LEVEL > 0)
		{
			$this->adminMenu['main/list'] = array(
				'caption' => LAN_CATEGORY,
				'perm'    => 'P',
			);
		}
	}

}


/**
 * Class gallery_cat_admin_ui.
 */
class gallery_cat_admin_ui extends e_admin_ui
{

	/**
	 * Could be LAN constant (multi-language support).
	 *
	 * @var string plugin name
	 */
	protected $pluginTitle = LAN_PLUGIN_GALLERY_TITLE;

	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	protected $pluginName = 'gallery';

	/**
	 * Plugin table.
	 *
	 * @var string
	 */
	protected $table = "core_media_cat";

	/**
	 * Primary key in plugin table.
	 *
	 * @var string
	 */
	protected $pid = "media_cat_id";

	/**
	 * Default (db) limit value.
	 *
	 * @var integer
	 */
	protected $perPage = 10;

	/**
	 * SQL order, false to disable order, null is default order.
	 *
	 * @var string
	 */
	protected $listOrder = 'media_cat_order';

	/**
	 * SQL query for listing. Without any Order or Limit.
	 *
	 * @var string
	 */
	protected $listQry = "SELECT * FROM `#core_media_cat` WHERE media_cat_owner = 'gallery' ";

	/**
	 * UI field data.
	 *
	 * @var array
	 */
	protected $fields = array(
		'checkboxes'         => array(
			'title'   => '',
			'type'    => null,
			'width'   => '5%',
			'forced'  => true,
			'thclass' => 'center',
			'class'   => 'center',
		),
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
			'inline'   => true,
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

	/**
	 * Referenced from $prefs property per field - 'tab => xxx' where xxx is the tab key (identifier).
	 *
	 * Example:
	 * @code
	 *  array(
	 *      '0' => 'Tab label',
	 *      '1' => 'Another label',
	 *  );
	 * @endcode
	 *
	 * @var array
	 *  Edit/create form tabs.
	 */
	protected $preftabs = array(
		LAN_GENERAL,
		LAN_GALLERY_ADMIN_03,
		LAN_GALLERY_ADMIN_32,
	);

	/**
	 * Plugin Preference description array.
	 *
	 * @var array
	 */
	protected $prefs = array(
		'popup_w'                    => array(
			'title' => LAN_GALLERY_ADMIN_04,
			'tab'   => 0,
			'type'  => 'text',
			'data'  => 'int',
			'help'  => LAN_GALLERY_ADMIN_05,
		),
		'popup_h'                    => array(
			'title' => LAN_GALLERY_ADMIN_06,
			'tab'   => 0,
			'type'  => 'text',
			'data'  => 'int',
			'help'  => LAN_GALLERY_ADMIN_07,
		),
		'downloadable'               => array(
			'title' => LAN_GALLERY_ADMIN_08,
			'tab'   => 0,
			'type'  => 'boolean',
			'data'  => 'int',
			'help'  => LAN_GALLERY_ADMIN_09,
		),
		'slideshow_category'         => array(
			'title' => LAN_GALLERY_ADMIN_10,
			'tab'   => 1,
			'type'  => 'dropdown',
			'data'  => 'str',
			'help'  => LAN_GALLERY_ADMIN_11,
		),
		'slideshow_duration'         => array(
			'title' => LAN_GALLERY_ADMIN_12,
			'type'  => 'number',
			'tab'   => 1,
			'data'  => 'integer',
			'help'  => LAN_GALLERY_ADMIN_13,
		),
		'slideshow_auto'             => array(
			'title' => LAN_GALLERY_ADMIN_14,
			'type'  => 'boolean',
			'tab'   => 1,
			'data'  => 'integer',
			'help'  => LAN_GALLERY_ADMIN_15,
		),
		'slideshow_freq'             => array(
			'title' => LAN_GALLERY_ADMIN_16,
			'type'  => 'number',
			'tab'   => 1,
			'data'  => 'integer',
			'help'  => LAN_GALLERY_ADMIN_17,
		),
		'slideshow_effect'           => array(
			'title' => LAN_GALLERY_ADMIN_18,
			'type'  => 'dropdown',
			'tab'   => 1,
			'data'  => 'str',
			'help'  => LAN_GALLERY_ADMIN_19
		),
		'perpage'                    => array(
			'title' => LAN_GALLERY_ADMIN_20,
			'tab'   => 0,
			'type'  => 'number',
			'data'  => 'int',
			'help'  => LAN_GALLERY_ADMIN_21,
		),
		'orderby'                    => array(
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
		'pp_global'                  => array(
			'title'      => LAN_GALLERY_ADMIN_70,
			'type'       => 'boolean',
			'data'       => 'int',
			'tab'        => 2,
		),
		'pp_hook'                    => array(
			'title'      => LAN_GALLERY_ADMIN_71,
			'type'       => 'text',
			'data'       => 'str',
			'writeParms' => array(
				'default' => 'data-gal',
			),
			'tab'        => 2,
		),
		'pp_animation_speed'         => array(
			'title'      => LAN_GALLERY_ADMIN_33,
			'type'       => 'dropdown',
			'data'       => 'str',
			'writeParms' => array(
				'optArray' => array(
					'fast'   => LAN_GALLERY_ADMIN_62,
					'slow'   => LAN_GALLERY_ADMIN_63,
					'normal' => LAN_GALLERY_ADMIN_64,
				),
			),
			'tab'        => 2,
		),
		'pp_slideshow'               => array(
			'title'      => LAN_GALLERY_ADMIN_34,
			'type'       => 'text',
			'data'       => 'int',
			'writeParms' => array(
				'default' => 5000,
			),
			'tab'        => 2,
		),
		'pp_autoplay_slideshow'      => array(
			'title'      => LAN_GALLERY_ADMIN_35,
			'type'       => 'boolean',
			'data'       => 'int',
			'tab'        => 2,
		),
		'pp_opacity'                 => array(
			'title'      => LAN_GALLERY_ADMIN_36,
			'help'       => LAN_GALLERY_ADMIN_37,
			'type'       => 'text',
			'data'       => 'float',
			'writeParms' => array(
				'default' => 0.80,
			),
			'tab'        => 2,
		),
		'pp_show_title'              => array(
			'title'      => LAN_GALLERY_ADMIN_38,
			'type'       => 'boolean',
			'data'       => 'int',
			'tab'        => 2,
		),
		'pp_allow_resize'            => array(
			'title'      => LAN_GALLERY_ADMIN_39,
			'help'       => LAN_GALLERY_ADMIN_40,
			'type'       => 'boolean',
			'data'       => 'int',
			'tab'        => 2,
		),
		'pp_default_width'           => array(
			'title'      => LAN_GALLERY_ADMIN_41,
			'type'       => 'text',
			'data'       => 'int',
			'writeParms' => array(
				'default' => 500,
			),
			'tab'        => 2,
		),
		'pp_default_height'          => array(
			'title'      => LAN_GALLERY_ADMIN_42,
			'type'       => 'text',
			'data'       => 'int',
			'writeParms' => array(
				'default' => 344,
			),
			'tab'        => 2,
		),
		'pp_counter_separator_label' => array(
			'title'      => LAN_GALLERY_ADMIN_43,
			'help'       => LAN_GALLERY_ADMIN_44,
			'type'       => 'text',
			'data'       => 'str',
			'writeParms' => array(
				'default' => '/',
			),
			'tab'        => 2,
		),
		'pp_theme'                   => array(
			'title'      => LAN_THEME,
			'type'       => 'dropdown',
			'data'       => 'str',
			'writeParms' => array(
				'optArray' => array(
					'pp_default'    => LAN_DEFAULT,
					'light_rounded' => LAN_GALLERY_ADMIN_65,
					'dark_rounded'  => LAN_GALLERY_ADMIN_66,
					'light_square'  => LAN_GALLERY_ADMIN_67,
					'dark_square'   => LAN_GALLERY_ADMIN_68,
					'facebook'      => LAN_GALLERY_ADMIN_69,
				),
			),
			'tab'        => 2,
		),
		'pp_horizontal_padding'      => array(
			'title'      => LAN_GALLERY_ADMIN_46,
			'help'       => LAN_GALLERY_ADMIN_47,
			'type'       => 'text',
			'data'       => 'int',
			'writeParms' => array(
				'default' => 20,
			),
			'tab'        => 2,
		),
		'pp_hideflash'               => array(
			'title'      => LAN_GALLERY_ADMIN_48,
			'help'       => LAN_GALLERY_ADMIN_49,
			'type'       => 'boolean',
			'data'       => 'int',
			'tab'        => 2,
		),
		'pp_wmode'                   => array(
			'title'      => LAN_GALLERY_ADMIN_50,
			'help'       => LAN_GALLERY_ADMIN_51,
			'type'       => 'text',
			'data'       => 'str',
			'writeParms' => array(
				'default' => 'opaque',
			),
			'tab'        => 2,
		),
		'pp_autoplay'                => array(
			'title'      => LAN_GALLERY_ADMIN_52,
			'type'       => 'boolean',
			'data'       => 'int',
			'tab'        => 2,
		),
		'pp_modal'                   => array(
			'title'      => LAN_GALLERY_ADMIN_53,
			'help'       => LAN_GALLERY_ADMIN_54,
			'type'       => 'boolean',
			'data'       => 'int',
			'tab'        => 2,
		),
		'pp_deeplinking'             => array(
			'title'      => LAN_GALLERY_ADMIN_55,
			'help'       => LAN_GALLERY_ADMIN_56,
			'type'       => 'boolean',
			'data'       => 'int',
			'tab'        => 2,
		),
		'pp_overlay_gallery'         => array(
			'title'      => LAN_GALLERY_ADMIN_57,
			'help'       => LAN_GALLERY_ADMIN_58,
			'type'       => 'boolean',
			'data'       => 'int',
			'tab'        => 2,
		),
		'pp_keyboard_shortcuts'      => array(
			'title'      => LAN_GALLERY_ADMIN_59,
			'help'       => LAN_GALLERY_ADMIN_60,
			'type'       => 'boolean',
			'data'       => 'int',
			'tab'        => 2,
		),
		'pp_ie6_fallback'            => array(
			'title'      => LAN_GALLERY_ADMIN_61,
			'type'       => 'boolean',
			'data'       => 'int',
			'tab'        => 2,
		),
	);

	private $ownerCount;

	/**
	 * Initial function.
	 */
	function init()
	{
		$effects = array(
			'scrollHorz' => LAN_GALLERY_ADMIN_29,
			'scrollVert' => LAN_GALLERY_ADMIN_30,
			'fade'       => LAN_GALLERY_ADMIN_31,
		);

		$this->prefs['slideshow_effect']['writeParms'] = $effects;
		$this->prefs['slideshow_effect']['readParms'] = $effects;

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

		$this->setGalleryCount();
	}



	function setGalleryCount()
	{

		$sql = e107::getDb();

		if($sql->gen("SELECT media_cat_owner,  MAX(CAST(SUBSTRING_INDEX(media_cat_category, '_', -1 ) AS UNSIGNED)) as maxnum, count(media_cat_id) as number FROM `#core_media_cat`  GROUP BY media_cat_owner"))
		{
			while($row = $sql->fetch())
			{
				$this->ownerCount[$row['media_cat_owner']] = $row['number'];
				$own = $row['media_cat_owner'];
			//	if(!in_array($own,$this->restricted))
				{
//					$this->fields['media_cat_owner']['writeParms'][$own] = $own;

					if($row['maxnum'] > 0)
					{
						$this->ownerCount[$row['media_cat_owner']] = $row['maxnum']; // $maxnum;
					}
				}
			}
		}

		e107::getMessage()->addDebug("Max value for category names: ".print_a($this->ownerCount,true));




	}


	public function beforeCreate($new_data, $old_data)
	{
		$new_data = $this->setCategory($new_data);

		return $new_data;
	}


	public function beforeUpdate($new_data, $old_data, $id)
	{
	//	$new_data = $this->setCategory($new_data);

		return $new_data;
	}

	private function setCategory($new_data)
	{
		$type = 'image_';

		$increment = ($this->ownerCount['gallery'] +1);

		$new_data['media_cat_owner'] = 'gallery';
		$new_data['media_cat_category'] = 'gallery_'.$type.$increment;

		if(empty($new_data['media_cat_sef']))
		{
			 $new_data['media_cat_sef'] = eHelper::title2sef($new_data['media_cat_title']);
		}

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

}


class gallery_cat_admin_form_ui extends e_admin_form_ui
{

	// Override the default Options field.
	public function gallery_category_parent($curVal, $mode)
	{
		// TODO - catlist combo without current cat ID in write mode, parents only for batch/filter.
		// Get UI instance.
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
