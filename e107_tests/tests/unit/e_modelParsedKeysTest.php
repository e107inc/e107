<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * e_model remembers the result of every 'a/b' style lookup so it does not have
 * to walk the array again. These cover the memo agreeing with the data after a
 * write, which is what e107::isInstalled() depends on: it asks
 * isData('plug_installed/<plugin>'), while installing and uninstalling replace
 * the whole plug_installed array.
 */
class e_modelParsedKeysTest extends \Codeception\Test\Unit
{
	/**
	 * @param array $data
	 * @return e_model
	 */
	private function modelOf(array $data)
	{
		$model = new e_model();
		$model->setData($data);

		return $model;
	}

	public function testReplacingAParentForgetsAPathBeneathIt()
	{
		$model = $this->modelOf(array('plug_installed' => array('pm' => '2.0')));

		self::assertTrue($model->isData('plug_installed/pm'),
			'precondition: the path has to be read once, or nothing is remembered');

		$model->set('plug_installed', array());

		self::assertFalse($model->isData('plug_installed/pm'),
			'a path under a replaced parent must not survive the replacement');
		self::assertNull($model->get('plug_installed/pm'));
	}

	public function testRemovingAParentForgetsAPathBeneathIt()
	{
		$model = $this->modelOf(array('plug_installed' => array('pm' => '2.0')));

		self::assertTrue($model->isData('plug_installed/pm'), 'precondition');

		$model->remove('plug_installed');

		self::assertFalse($model->isData('plug_installed/pm'));
	}

	/**
	 * The other direction: a path already read is an ancestor of the one being
	 * written, so what it answers with has changed underneath it.
	 */
	public function testWritingAChildRefreshesTheParentAlreadyRead()
	{
		$model = $this->modelOf(array('core' => array('plug_installed' => array('pm' => '2.0'))));

		self::assertSame(array('pm' => '2.0'), $model->getData('core/plug_installed'),
			'precondition: read the parent through a path, so it is remembered');

		$model->setData('core/plug_installed/forum', '2.0');

		self::assertSame(array('pm' => '2.0', 'forum' => '2.0'), $model->getData('core/plug_installed'));
	}

	/**
	 * setData() with an array replaces the object's contents outright, which is
	 * how a model is reloaded from the database.
	 */
	public function testReloadingTheWholeObjectForgetsEveryPath()
	{
		$model = $this->modelOf(array('plug_installed' => array('pm' => '2.0')));

		self::assertTrue($model->isData('plug_installed/pm'), 'precondition');

		$model->setData(array('plug_installed' => array('forum' => '2.0')));

		self::assertFalse($model->isData('plug_installed/pm'));
		self::assertTrue($model->isData('plug_installed/forum'));
	}
}
