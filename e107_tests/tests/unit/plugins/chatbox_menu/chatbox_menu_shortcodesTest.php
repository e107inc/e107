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
 * Regression coverage for issue #5657: sc_cb_avatar() previously hard-coded
 * 'crop' => 'C' and ignored caller-supplied $parm. Templates could not
 * override width, height, or crop, which made chatbox avatars dependent on
 * a reachable thumb.php (since non-numeric crop forces nosef=true at
 * e_parse_class.php:2754-2758).
 */
class chatbox_menu_shortcodesTest extends \Codeception\Test\Unit
{
	use \Helper\PhpUnitCompat;
	/** @var chatbox_menu_shortcodes */
	protected $sc;

	protected function _before()
	{
		$icon = codecept_data_dir() . "icon_64.png";

		if (!is_dir(e_AVATAR_DEFAULT))
		{
			mkdir(e_AVATAR_DEFAULT, 0755, true);
		}
		if (!file_exists(e_AVATAR_DEFAULT . "avatartest.png"))
		{
			copy($icon, e_AVATAR_DEFAULT . "avatartest.png");
		}

		require_once(e_PLUGIN . "chatbox_menu/chatbox_menu_shortcodes.php");

		try
		{
			$this->sc = $this->make('chatbox_menu_shortcodes');
		}
		catch (Exception $e)
		{
			self::fail($e->getMessage());
		}

		$this->sc->__construct();
		$this->sc->setVars(array(
			'user_id'    => 1,
			'user_name'  => 'admin',
			'user_image' => 'avatartest.png',
		));
	}

	/**
	 * BC lock-in: {CB_AVATAR} with no parm still center-crops at 40x40
	 * via thumb.php, matching the rendering every existing site relies on.
	 */
	public function testCbAvatarDefaultPreservesCenterCrop()
	{
		$result = $this->sc->sc_cb_avatar();
		self::assertStringContainsString('aw=40&amp;ah=40', $result);
		self::assertStringContainsString('c=C', $result);
	}

	/**
	 * The fix: $parm['crop'] must override the hard-coded 'C'. Passing
	 * crop=false drops the center-crop, letting thumbUrl emit a
	 * SEF-eligible query without c=C.
	 */
	public function testCbAvatarCropParmOverridesHardCodedDefault()
	{
		$result = $this->sc->sc_cb_avatar(array('crop' => false));
		self::assertStringNotContainsString('c=C', $result);
		self::assertStringContainsString('w=40&amp;h=40', $result);
	}

	/**
	 * The fix: $parm['w'] and $parm['h'] must reach toAvatar(). Previously
	 * ignored, so {CB_AVATAR: w=80&h=60} silently rendered at 40x40.
	 */
	public function testCbAvatarWidthHeightParmFlowsThroughToAvatar()
	{
		$result = $this->sc->sc_cb_avatar(array('w' => 80, 'h' => 60, 'crop' => false));
		self::assertStringContainsString('w=80&amp;h=60', $result);
	}

	/**
	 * BC lock-in: {CB_AVATAR: size=N} continues to set both dimensions.
	 */
	public function testCbAvatarSizeShortcutSetsBothDimensions()
	{
		$result = $this->sc->sc_cb_avatar(array('size' => 64, 'crop' => false));
		self::assertStringContainsString('w=64&amp;h=64', $result);
	}
}
