<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * An admin page over adminUiFailedSaveTest's table, with a field of each element type an edit form fills from what was posted; the test loads it in a booted child, which has already loaded class2.php.
 */
class AdminUiFailedSaveFixtureDispatcher extends e_admin_dispatcher
{
	protected $modes = array(
		'main' => array(
			'controller' => 'AdminUiFailedSaveFixtureUi',
			'path'       => null,
			'ui'         => 'e_admin_form_ui',
			'uipath'     => null,
		),
	);

	protected $adminMenu = array(
		'main/list' => array('caption' => 'List', 'perm' => '0'),
	);
}

class AdminUiFailedSaveFixtureUi extends e_admin_ui
{
	/** The options of each choice field; a key may hold an ampersand or an entity. */
	const CHOICES = array(
		'plain'             => 'Plain',
		'x&y'               => 'Ampersand',
		'&amp;amp;'         => 'Entity',
		'&amp;lt;b&amp;gt;' => 'Tag',
	);

	protected $pluginTitle = 'adminUiFailedSaveTest';
	protected $pluginName = 'core';
	protected $table = 'admin_ui_failed_save';
	protected $pid = 'fs_id';

	protected $fields = array(
		'checkboxes'    => array('title' => '', 'type' => null, 'forced' => true),
		'fs_id'         => array('title' => 'ID', 'type' => 'number', 'data' => 'int', 'readonly' => true),
		'fs_title'      => array('title' => 'Title', 'type' => 'text', 'data' => 'str', 'validate' => true),
		'fs_text'       => array('title' => 'Text', 'type' => 'text', 'data' => 'str'),
		'fs_url'        => array('title' => 'Url', 'type' => 'url', 'data' => 'str'),
		'fs_tags'       => array('title' => 'Tags', 'type' => 'tags', 'data' => 'str'),
		'fs_hidden'     => array('title' => 'Hidden', 'type' => 'hidden', 'data' => 'str'),
		'fs_icon'       => array('title' => 'Icon', 'type' => 'icon', 'data' => 'str'),
		'fs_image'      => array('title' => 'Image', 'type' => 'image', 'data' => 'str'),
		'fs_file'       => array('title' => 'File', 'type' => 'file', 'data' => 'str'),
		'fs_textarea'   => array('title' => 'Textarea', 'type' => 'textarea', 'data' => 'str'),
		'fs_bbarea'     => array('title' => 'Bbarea', 'type' => 'bbarea', 'data' => 'str'),
		'fs_dropdown'   => array('title' => 'Dropdown', 'type' => 'dropdown', 'data' => 'str', 'writeParms' => array('optArray' => self::CHOICES)),
		'fs_checkboxes' => array('title' => 'Checkboxes', 'type' => 'checkboxes', 'data' => 'str', 'writeParms' => array('optArray' => self::CHOICES, 'useKeyValues' => 1)),
		'fs_radio'      => array('title' => 'Radio', 'type' => 'radio', 'data' => 'str', 'writeParms' => array('optArray' => self::CHOICES)),
		'fs_hooked'     => array('title' => 'Hooked', 'type' => 'text', 'data' => 'str'),
		'options'       => array('title' => 'Options', 'type' => null, 'forced' => true),
	);

	/**
	 * @param array $new_data
	 * @param array $old_data
	 * @param int $id
	 * @return array the post with fs_hooked written through {@see e_parse::toDB()}
	 */
	public function beforeUpdate($new_data, $old_data, $id)
	{
		$new_data['fs_hooked'] = e107::getParser()->toDB(varset($new_data['fs_hooked'], ''));

		return $new_data;
	}
}

new AdminUiFailedSaveFixtureDispatcher();

require_once(e_ADMIN.'auth.php');
e107::getAdminUI()->runPage();
require_once(e_ADMIN.'footer.php');
