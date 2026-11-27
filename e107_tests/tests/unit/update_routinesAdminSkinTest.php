<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * The 2.x update, for the admin skin a site has chosen.
 *
 * @see https://github.com/e107inc/e107/issues/6810
 */
class update_routinesAdminSkinTest extends \Test\Unit
{
	const CHOSEN = 'css/bootstrap-dark.min.css';

	/** @var callable puts the stored core preferences back as this test found them */
	private $restoreStoredPrefs;

	protected function _before()
	{
		require_once(e_ADMIN . 'update_routines.php');
		$this->restoreStoredPrefs = $this->keepStoredCorePrefs();
	}

	protected function _after()
	{
		call_user_func($this->restoreStoredPrefs);
	}

	public function testTheUpdateLeavesTheChosenAdminSkinAlone()
	{
		e107::getConfig()->set('admincss', self::CHOSEN)->save(false, true, false);

		update_20x_to_latest('do');

		$this->assertSame(self::CHOSEN, e107::getConfig()->get('admincss'));
	}
}
