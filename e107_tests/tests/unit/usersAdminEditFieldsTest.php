<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

use e107\Database\SqlFragment;

/**
 * The admin user editor offers what the member's own settings form does: the user class tree, the photo, the hide boxes and the extended field categories (#6004).
 */
class usersAdminEditFieldsTest extends \Test\Unit
{
	/** @var int[] */
	private $members = array();

	/** @var string[] */
	private $structNames = array();

	protected function _before()
	{
		\Test\CoreTable::create('admin_history');
	}

	protected function _after()
	{
		putenv('QUERY_STRING');

		$db = e107::getDb();

		foreach($this->members as $id)
		{
			$db->createQueryBuilder()->delete('user')->where('user_id', $id)->execute();
			$db->createQueryBuilder()->delete('user_extended')->where('user_extended_id', $id)->execute();
		}

		foreach($this->structNames as $name)
		{
			$db->createQueryBuilder()->delete('user_extended_struct')->where('user_extended_struct_name', $name)->execute();

			if($db->field('user_extended', 'user_'.$name) !== false)
			{
				$db->schema()->dropColumn('user_extended', 'user_'.$name);
			}
		}

		$this->members = array();
		$this->structNames = array();

		\Test\CoreTable::drop('admin_history');
	}

	public function testTheEditFormOffersTheUserClassTree()
	{
		$form = $this->editForm($this->member(array('user_class' => (string) e_UC_MEMBER)));

		self::assertNotNull($form, 'the edit form is on the page');
		self::assertArrayHasKey('user_class', $form, 'the edit form posts the user class tree');
		self::assertContains(e_UC_MEMBER, array_map('intval', $form['user_class']), 'the tree shows the class the member holds as ticked');
	}

	public function testTheEditFormSavesATickedClass()
	{
		$id = $this->member(array('user_class' => (string) e_UC_NEWUSER));
		$form = $this->editForm($id);
		$form['user_class'] = array('0', (string) e_UC_MEMBER);

		$this->save($id, $form);

		self::assertSame(e_UC_NEWUSER.','.e_UC_MEMBER, $this->stored($id, 'user_class'));
	}

	public function testTheEditFormRemovesAnUntickedClassAndKeepsTheOnesItDoesNotOffer()
	{
		$id = $this->member(array('user_class' => e_UC_NEWUSER.','.e_UC_MEMBER));
		$form = $this->editForm($id);
		$form['user_class'] = array('0');

		$this->save($id, $form);

		self::assertSame((string) e_UC_NEWUSER, $this->stored($id, 'user_class'), 'unticking Members removes it, and the tree never offers the new-user class');
	}

	public function testARefusedClassChangeBringsTheEditFormBackAsPosted()
	{
		e107::coreLan('users', true);
		$this->hideableField('usaerefused');
		$this->hideableField('usaerefusedkept');
		$id = $this->member(array('user_class' => (string) e_UC_MEMBER));
		$this->storeHiddenFields($id, '^user_usaerefusedkept^');
		$form = $this->editForm($id);
		$form['user_class'] = array('0', '9999');
		$form['hide'] = array('user_usaerefused' => 1);

		$page = $this->save($id, $form);
		$shown = $this->untouchedSubmission($page, 'user_loginname');

		self::assertStringContainsString(USRLAN_226, $page, 'the refusal is said');
		self::assertNotNull($shown, 'the refused save shows the form again');
		self::assertSame('1', varset($shown['hide']['user_usaerefused']), 'the hide box comes back as posted');
		self::assertArrayNotHasKey('user_usaerefusedkept', (array) varset($shown['hide']), 'a stored flag the admin unticked comes back unticked');
		self::assertSame((string) e_UC_MEMBER, $this->stored($id, 'user_class'));
	}

	public function testTheEditFormSavesAdminPermissionsAsAdministratorsDoes()
	{
		$id = $this->member(array('user_admin' => 1, 'user_perms' => 'I.'));
		$form = $this->editForm($id);
		$form['perms'] = array('I', '5');

		$this->save($id, $form);

		self::assertSame('I.5.', $this->stored($id, 'user_perms'));
	}

	public function testTheEditFormDropsAMainAdminCodeItsSaverCannotGrant()
	{
		$id = $this->member(array('user_admin' => 1, 'user_perms' => 'J.'));
		$form = $this->editForm($id);
		$form['perms'] = array('0', 'I');

		$this->save($id, $form, $this->bootedAdminHolding('4.3.'));

		self::assertSame('I.', $this->stored($id, 'user_perms'), 'an administrator who may grant permissions but is not a main admin cannot grant main admin');
	}

	public function testTheEditFormRemovesThePhotoOnlyWhenAsked()
	{
		$id = $this->member(array('user_sess' => '-upload-ap_usaephoto.jpg'));
		$form = $this->editForm($id);

		$this->save($id, $form);
		self::assertSame('-upload-ap_usaephoto.jpg', $this->stored($id, 'user_sess'), 'a save that does not tick the box keeps the photo');

		$form['user_delete_photo'] = 1;
		$this->save($id, $form);
		self::assertSame('', $this->stored($id, 'user_sess'), 'ticking the box removes the photo, as the member\'s own box does');
	}

	public function testTheEditFormLeavesAnActivationKeyOrDeletionHashAlone()
	{
		foreach(array('activation key' => array('user_ban' => 2), 'deletion hash' => array()) as $held => $fields)
		{
			$id = $this->member($fields + array('user_sess' => 'usae0123456789abcdef'));
			$page = $this->edit($id);

			self::assertStringNotContainsString("name='user_delete_photo'", $page, 'user_sess holds an '.$held.', not a photograph');

			$form = $this->untouchedSubmission($page, 'user_loginname');
			$form['user_delete_photo'] = 1;
			$this->save($id, $form);

			self::assertSame('usae0123456789abcdef', $this->stored($id, 'user_sess'), 'the '.$held.' survives a save');
		}
	}

	public function testTheEditFormSavesTheMembersHideFlag()
	{
		$this->hideableField('usaehide');
		$id = $this->member();
		$form = $this->editForm($id);
		$form['hide'] = array('user_usaehide' => 1);

		$this->save($id, $form);

		self::assertSame('^user_usaehide^', $this->storedHiddenFields($id));
	}

	public function testTheEditFormKeepsAStoredHideFlag()
	{
		$this->hideableField('usaekeep');
		$id = $this->member();
		$this->storeHiddenFields($id, '^user_usaekeep^');

		$page = $this->edit($id);
		self::assertStringContainsString("name='hide[user_usaekeep]'", $page, 'the field offers its hide box');

		$this->save($id, $this->untouchedSubmission($page, 'user_loginname'));

		self::assertSame('^user_usaekeep^', $this->storedHiddenFields($id), 'an untouched save keeps the flag the box showed');
	}

	public function testTheEditFormPutsAnExtendedFieldOnItsCategorysTab()
	{
		$category = $this->extendedField('usaecat', 0, 0, '', 'Usaecat caption', array('user_extended_struct_order' => 5));
		$this->extendedField('usaefield', 1, $category, '', '', array('user_extended_struct_order' => 1));
		$addonOnly = $this->extendedField('usaeaddoncat', 0, 0, '', 'Usaeaddoncat caption');
		$this->extendedField('usaeaddon', 12, $addonOnly, '', '', array('user_extended_struct_values' => e107::serialize(array('nolist' => true))));

		$this->extendedField('usaeloose', 1, 0);

		$page = $this->edit($this->member());

		self::assertStringContainsString('Usaecat caption', $page, 'the category is a tab of the edit form, as it is of the member\'s form');
		self::assertTrue($this->paneHolds($page, 'category'.$category, 'usaefield'), 'the field sits on its category\'s tab, whichever orders first');
		self::assertFalse($this->paneHolds($page, 'category'.$category, 'usaeloose'));
		self::assertStringNotContainsString('Usaeaddoncat caption', $page, 'a category holding only unlisted addon fields gets no tab');
	}

	public function testTheEditFormOrdersCategoriesAndTheirFieldsAsTheMembersFormDoes()
	{
		$late = $this->extendedField('usaelate', 0, 0, '', '', array('user_extended_struct_order' => 20));
		$early = $this->extendedField('usaeearly', 0, 0, '', '', array('user_extended_struct_order' => 10));
		$this->extendedField('usaesecond', 1, $early, '', '', array('user_extended_struct_order' => 20));
		$this->extendedField('usaefirst', 1, $early, '', '', array('user_extended_struct_order' => 10));
		$this->extendedField('usaelatefield', 1, $late);

		$page = $this->edit($this->member());

		$tabs = array_intersect($this->attributesOf($page, "//a[starts-with(@href, '#category')]", 'href'), array('#category'.$early, '#category'.$late));

		self::assertSame(array('#category'.$early, '#category'.$late), array_values($tabs), 'category tabs come in category order');
		self::assertSame(array('ue[user_usaefirst]', 'ue[user_usaesecond]'), array_values(preg_grep('/usae/', $this->attributesOf($page, "//*[@id='category".$early."']//*[@name]", 'name'))), 'a category\'s fields come in field order');
	}

	public function testTheReadOnlyViewShowsNeitherTheClassTreeNorThePhoto()
	{
		$id = $this->member(array('user_class' => (string) e_UC_MEMBER, 'user_sess' => '-upload-ap_usaephoto.jpg'));

		$page = $this->usersPage($id, '', array('readonly' => 1, 'iframe' => 1));

		self::assertStringNotContainsString("name='user_class'", $page, 'the popup that admin lists open shows what it showed before');
		self::assertStringNotContainsString("name='user_sess'", $page);
	}

	/**
	 * @param array $fields user columns to seed beside the defaults
	 * @return int
	 */
	private function member(array $fields = array())
	{
		$name = 'usaedit'.count($this->members).uniqid();

		$id = (int) e107::getDb()->createQueryBuilder()->insert('user')->insertGetId($fields + array(
			'user_name'      => $name,
			'user_loginname' => $name,
			'user_email'     => $name.'@example.test',
			'user_join'      => time(),
			'user_class'     => '',
		));

		self::assertGreaterThan(0, $id);
		$this->members[] = $id;

		return $id;
	}

	/**
	 * An uncategorised text field whose definition offers the member a hide box.
	 *
	 * @param string $name
	 * @return int the struct id
	 */
	private function hideableField($name)
	{
		return $this->extendedField($name, 1, 0, '^,^^,^^,^1');
	}

	/**
	 * @param string $name
	 * @param int $type EUF_* type; 0 for a category.
	 * @param int $parent category id.
	 * @param string $parms user_extended_struct_parms; a fourth ^,^ part of 1 offers the hide box.
	 * @param string $text
	 * @param array $struct user_extended_struct columns that override the defaults
	 * @return int the struct id
	 */
	private function extendedField($name, $type, $parent, $parms = '', $text = '', array $struct = array())
	{
		$db = e107::getDb();

		$id = (int) $db->createQueryBuilder()->insert('user_extended_struct')->insertGetId($struct + array(
			'user_extended_struct_name'   => $name,
			'user_extended_struct_text'   => ($text === '') ? $name : $text,
			'user_extended_struct_type'   => $type,
			'user_extended_struct_parms'  => $parms,
			'user_extended_struct_values' => '',
			'user_extended_struct_parent' => $parent,
			'user_extended_struct_read'   => e_UC_PUBLIC,
			'user_extended_struct_write'  => e_UC_MEMBER,
			'user_extended_struct_applicable' => e_UC_MEMBER,
		));

		self::assertGreaterThan(0, $id);
		$this->structNames[] = $name;

		if($type !== 0)
		{
			$db->schema()->addColumn('user_extended', 'user_'.$name, SqlFragment::raw('VARCHAR(255)'));
		}

		return $id;
	}

	/**
	 * @param int $id
	 * @return string the edit page
	 */
	private function edit($id)
	{
		return $this->usersPage($id, '');
	}

	/**
	 * @param int $id
	 * @return array|null what the edit form of member $id posts untouched
	 */
	private function editForm($id)
	{
		return $this->untouchedSubmission($this->edit($id), 'user_loginname');
	}

	/**
	 * @param string $page
	 * @param string $paneId
	 * @param string $name part of a control's name
	 * @return bool
	 */
	private function paneHolds($page, $paneId, $name)
	{
		return $this->attributesOf($page, "//*[@id='".$paneId."']//*[contains(@name, '".$name."')]", 'name') !== array();
	}

	/**
	 * @param string $page
	 * @param string $query XPath
	 * @param string $attribute
	 * @return string[] $attribute of every element $query finds, in page order
	 */
	private function attributesOf($page, $query, $attribute)
	{
		$dom = new DOMDocument();
		@$dom->loadHTML($page);

		$values = array();

		foreach((new DOMXPath($dom))->query($query) as $element)
		{
			$values[] = $element->getAttribute($attribute);
		}

		return $values;
	}

	/**
	 * Posts $post as the edit form of member $id, with the triggers its submit button sends.
	 *
	 * @param int $id
	 * @param array $post
	 * @param array $saver who saves, as {@see \Test\Unit::bootedAdminHolding()} gives it; the main admin when empty
	 * @return string the page
	 */
	private function save($id, array $post, array $saver = array(array(), ''))
	{
		$post['etrigger_submit'] = 'update';
		$post['__after_submit_action'] = 'edit';

		return $this->usersPage($id, $saver[1].'$_POST = '.var_export($post, true)." + array('e-token' => defset('e_TOKEN')); ", array(), $saver[0]);
	}

	/**
	 * @param int $id
	 * @param string $seed
	 * @param array $query further query parameters
	 * @param array $constants name => value, defined in the child before class2.php could
	 * @return string
	 */
	private function usersPage($id, $seed, array $query = array(), array $constants = array())
	{
		$get = array('mode' => 'main', 'action' => 'edit', 'id' => $id) + $query;
		putenv('QUERY_STRING='.http_build_query($get));

		$seed = "define('SEP', ' &raquo; '); \$_GET = ".var_export($get, true).'; e107::getUser()->setData(e107::user(USERID)); '.$seed;
		$page = $this->renderInBootedCli('e107_admin/users.php', $seed, '', $constants);

		self::assertStringNotContainsString('Fatal error', $page, "the page died:\n".$page);

		return $page;
	}

	/**
	 * @param int $id
	 * @param string $column
	 * @return string
	 */
	private function stored($id, $column)
	{
		return (string) e107::getDb()->createQueryBuilder()->select($column)->from('user')->where('user_id', $id)->fetchOne();
	}

	/**
	 * @param int $id
	 * @return string
	 */
	private function storedHiddenFields($id)
	{
		return (string) e107::getDb()->createQueryBuilder()->select('user_hidden_fields')->from('user_extended')->where('user_extended_id', $id)->fetchOne();
	}

	/**
	 * @param int $id
	 * @param string $flags user_hidden_fields, as ^user_name^ per hidden field
	 */
	private function storeHiddenFields($id, $flags)
	{
		e107::getDb()->createQueryBuilder()->insert('user_extended')->values(array('user_extended_id' => $id, 'user_hidden_fields' => $flags))->execute();
	}
}
