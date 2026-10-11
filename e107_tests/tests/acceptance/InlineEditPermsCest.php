<?php

/**
 * Who may write a news or FAQ row, and which row, through each route that writes one.
 *
 * @see e107_handlers/news_class.php  the front-end editable map
 * @see e107_web/js/inline.php  the front-end save endpoint
 * @see e107_handlers/admin_ui.php  e_admin_ui::InlineAjaxPage(), SortAjaxPage()
 * @see e107_handlers/admin_ui.php  e_admin_controller_ui::_manageSubmit(), which the create and edit forms save through
 * @see e107_admin/newspost.php  news_admin::$perm
 * @see e107_plugins/faqs/admin_config.php  faq_admin::init(), which keys its routes to user classes
 */
class InlineEditPermsCest
{
	const PROBE_FILE = 'e107_tests_inlineperms_probe.php';

	/** The front-end editor's save endpoint, which takes the row id from the request. */
	const ROUTE_FRONT_SAVE = '/e107_web/js/inline.php';

	/** A news item on the public site. */
	const ROUTE_NEWS_ITEM = '/news.php?extend.';

	/** The news item create form. */
	const ROUTE_ITEM_CREATE = '/e107_admin/newspost.php?mode=main&action=create';

	/** The news item edit form. The row id goes on the end. */
	const ROUTE_ITEM_EDIT = '/e107_admin/newspost.php?mode=main&action=edit&id=';

	/** The news item list. */
	const ROUTE_ITEM_LIST = '/e107_admin/newspost.php?mode=main&action=list';

	/** The same rows in the grid view, a route the news map does not declare. */
	const ROUTE_ITEM_GRID = '/e107_admin/newspost.php?mode=main&action=grid';

	/** The FAQ question list. */
	const ROUTE_FAQ_LIST = '/e107_plugins/faqs/admin_config.php?mode=main&action=list';

	/** The administrator-side inline editor for news items. */
	const ROUTE_ITEM_INLINE = '/e107_admin/newspost.php?mode=main&action=inline&ajax_used=1&id=';

	/** The administrator-side inline editor for news categories. */
	const ROUTE_CATEGORY_INLINE = '/e107_admin/newspost.php?mode=cat&action=inline&ajax_used=1&id=';

	/** The drag-and-drop order of the news categories. */
	const ROUTE_CATEGORY_SORT = '/e107_admin/newspost.php?mode=cat&action=sort&ajax_used=1';

	/** The administrator-side inline editor for FAQ questions. */
	const ROUTE_FAQ_INLINE = '/e107_plugins/faqs/admin_config.php?mode=main&action=inline&ajax_used=1&id=';

	/**
	 * Administrators seeded per test, login name => user_perms with the trailing dot e_userperms::updatePerms() writes.
	 *
	 * @see e107_handlers/user_handler.php  e_userperms::getPermList()
	 */
	const ADMINS = array(
		'p7iecategory'  => 'H4.',
		'p7iecreate'    => 'H0.',
		'p7ieitem'      => 'H1.',
		'p7iedelete'    => 'H2.',
		'p7iesubmitted' => 'N.',
	);

	/** Seeded on every account so no test has to load the password-change screen. */
	const PWCHANGE = 1262304000;

	/** What the attack posts. */
	const ATTACK_VALUE = 'p7ie attacked';

	/** What a legitimate inline save posts. */
	const SAVED_VALUE = 'p7ie saved';

	/** The seeded news item's title. */
	const ITEM_TITLE = 'p7ie item';

	/** The seeded category's name. */
	const CATEGORY_NAME = 'p7ie category';

	/** The seeded category's order, which the sort route rewrites. */
	const CATEGORY_ORDER = 7;

	/** A news body the news list's image batch rewrites. */
	const IMAGE_BODY = '<p><img src="http://example.com/p7ie.png" alt="p7ie" /></p>';

	/** A news id no row has. */
	const MISSING_ID = 987654;

	public function _before(AcceptanceTester $I)
	{
		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
		$I->startFollowingRedirects();
		$I->probe('act=reset');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->probe('act=reset');
		$I->dropPluginInstall('faqs');
		$I->dropPluginProbe();
	}

	public function aCategoryAdministratorCannotSaveANewsItemOnTheFrontEnd(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a front-end inline news save to an administrator who may only edit categories');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$I->probe('act=enable');
		$this->loginAsAdminHolding($I, 'p7iecategory');

		$this->saveOnTheFrontEnd($I, $itemId, 'news_title', self::ATTACK_VALUE);

		$I->seeInDatabase('e107_news', array('news_id' => $itemId, 'news_title' => self::ITEM_TITLE));
	}

	public function aNewsItemAdministratorStillSavesANewsItemOnTheFrontEnd(AcceptanceTester $I)
	{
		$I->wantTo('Keep the front-end inline news editor working for an administrator who may edit items');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$I->probe('act=enable');
		$this->loginAsAdminHolding($I, 'p7ieitem');

		$this->saveOnTheFrontEnd($I, $itemId, 'news_title', self::SAVED_VALUE);

		$I->seeInDatabase('e107_news', array('news_id' => $itemId, 'news_title' => self::SAVED_VALUE));
	}

	public function aFrontEndSaveIsRefusedWhenTheMapDeclaresNoPermission(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a front-end inline save whose editable map declares no permission');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7ieitem');
		$I->probe('act=seedmap');

		$this->postFrontEndSave($I, $itemId, 'news_title', self::ATTACK_VALUE);

		$I->seeInDatabase('e107_news', array('news_id' => $itemId, 'news_title' => self::ITEM_TITLE));
	}

	public function aFrontEndSaveIsRefusedToAnAdministratorWithoutThePermissionTheMapDeclares(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a front-end inline save to an administrator without the permission the editable map declares');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7iecategory');
		$I->probe('act=seedmap&perms='.rawurlencode('H|H1'));

		$this->postFrontEndSave($I, $itemId, 'news_title', self::ATTACK_VALUE);

		$I->seeInDatabase('e107_news', array('news_id' => $itemId, 'news_title' => self::ITEM_TITLE));
	}

	public function aCategoryAdministratorCannotSaveANewsItemInline(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an inline news item save to an administrator who may only edit categories');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7iecategory');
		$this->saveInline($I, self::ROUTE_ITEM_INLINE.$itemId, 'news_title', self::ATTACK_VALUE);

		$I->seeInDatabase('e107_news', array('news_id' => $itemId, 'news_title' => self::ITEM_TITLE));
	}

	public function aNewsItemAdministratorStillSavesANewsItemInline(AcceptanceTester $I)
	{
		$I->wantTo('Keep the inline news item editor working for an administrator who may edit items');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7ieitem');
		$this->saveInline($I, self::ROUTE_ITEM_INLINE.$itemId, 'news_title', self::SAVED_VALUE);

		$I->seeInDatabase('e107_news', array('news_id' => $itemId, 'news_title' => self::SAVED_VALUE));
	}

	public function aCategoryAdministratorStillSavesACategoryInline(AcceptanceTester $I)
	{
		$I->wantTo('Keep the inline category editor working for an administrator who may edit categories');

		$categoryId = $this->seedCategory($I);

		$this->loginAsAdminHolding($I, 'p7iecategory');
		$this->saveInline($I, self::ROUTE_CATEGORY_INLINE.$categoryId, 'category_name', self::SAVED_VALUE);

		$I->seeInDatabase('e107_news_category',
			array('category_id' => $categoryId, 'category_name' => self::SAVED_VALUE));
	}

	public function aSubmittedNewsAdministratorCannotReorderTheCategories(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a category reorder to an administrator who may only moderate submitted news');

		$categoryId = $this->seedCategory($I);

		$this->loginAsAdminHolding($I, 'p7iesubmitted');
		$this->sortCategories($I, $categoryId);

		$I->seeInDatabase('e107_news_category',
			array('category_id' => $categoryId, 'category_order' => self::CATEGORY_ORDER));
	}

	public function aCategoryAdministratorStillReordersTheCategories(AcceptanceTester $I)
	{
		$I->wantTo('Keep the category reorder working for an administrator who may edit categories');

		$categoryId = $this->seedCategory($I);

		$this->loginAsAdminHolding($I, 'p7iecategory');
		$this->sortCategories($I, $categoryId);

		$I->dontSeeInDatabase('e107_news_category',
			array('category_id' => $categoryId, 'category_order' => self::CATEGORY_ORDER));
	}

	public function aFaqsAdministratorOutsideTheEditClassCannotSaveAQuestionInline(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an inline FAQ save to an administrator outside the class the site lets edit FAQs');

		$I->havePluginInstalled('faqs');
		$faqId = $this->seedFaq($I);
		$I->probe('act=faqsedit&class=mainadmin');

		$this->loginAsFaqsAdministrator($I);
		$this->saveInline($I, self::ROUTE_FAQ_INLINE.$faqId, 'faq_question', self::ATTACK_VALUE);

		$I->seeInDatabase('e107_faqs', array('faq_id' => $faqId, 'faq_question' => self::ITEM_TITLE));
	}

	public function aFaqsAdministratorInsideTheEditClassStillSavesAQuestionInline(AcceptanceTester $I)
	{
		$I->wantTo('Keep the inline FAQ editor working for an administrator in the class the site lets edit FAQs');

		$I->havePluginInstalled('faqs');
		$faqId = $this->seedFaq($I);
		$I->probe('act=faqsedit&class=admin');

		$this->loginAsFaqsAdministrator($I);
		$this->saveInline($I, self::ROUTE_FAQ_INLINE.$faqId, 'faq_question', self::SAVED_VALUE);

		$I->seeInDatabase('e107_faqs', array('faq_id' => $faqId, 'faq_question' => self::SAVED_VALUE));
	}

	public function aNewsCreatorCannotRewriteANewsItemThroughTheCreateRoute(AcceptanceTester $I)
	{
		$I->wantTo('Insert, and never update, through the news item create form');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7iecreate');
		$this->submitNewsForm($I, self::ROUTE_ITEM_CREATE, 'update', $categoryId, self::ATTACK_VALUE, array('news_id' => $itemId));

		$I->seeInDatabase('e107_news', array('news_id' => $itemId, 'news_title' => self::ITEM_TITLE));
	}

	public function aNewsCreatorStillCreatesANewsItem(AcceptanceTester $I)
	{
		$I->wantTo('Keep the news item create form working for an administrator who may create items');

		$categoryId = $this->seedCategory($I);

		$this->loginAsAdminHolding($I, 'p7iecreate');
		$this->submitNewsForm($I, self::ROUTE_ITEM_CREATE, 'create', $categoryId, self::SAVED_VALUE);

		$I->seeInDatabase('e107_news', array('news_title' => self::SAVED_VALUE));
	}

	public function aNewsItemAdministratorCannotCreateANewsItemThroughTheEditRoute(AcceptanceTester $I)
	{
		$I->wantTo('Refuse an insert through the news item edit form when it has no row to update');

		$categoryId = $this->seedCategory($I);

		$this->loginAsAdminHolding($I, 'p7ieitem');
		$this->submitNewsForm($I, self::ROUTE_ITEM_EDIT.self::MISSING_ID, 'update', $categoryId, self::ATTACK_VALUE);

		$I->dontSeeInDatabase('e107_news', array('news_title' => self::ATTACK_VALUE));
	}

	public function aNewsItemAdministratorStillSavesTheEditForm(AcceptanceTester $I)
	{
		$I->wantTo('Keep the news item edit form working for an administrator who may edit items');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7ieitem');
		$this->submitNewsForm($I, self::ROUTE_ITEM_EDIT.$itemId, 'update', $categoryId, self::SAVED_VALUE);

		$I->seeInDatabase('e107_news', array('news_id' => $itemId, 'news_title' => self::SAVED_VALUE));
	}

	public function aNewsCreatorCannotBatchWriteANewsItem(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a news list batch write to an administrator who may only create items');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7iecreate');
		$this->sendBatch($I, self::ROUTE_ITEM_LIST, 'news_class__255', $itemId);

		$I->seeInDatabase('e107_news', array('news_id' => $itemId, 'news_class' => '0'));
	}

	public function aCategoryAdministratorCannotBatchWriteANewsItemOnTheGrid(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a news grid batch write to an administrator who may only edit categories');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7iecategory');
		$this->sendBatch($I, self::ROUTE_ITEM_GRID, 'bool__news_sticky__1', $itemId);

		$I->seeInDatabase('e107_news', array('news_id' => $itemId, 'news_sticky' => 0));
	}

	public function aNewsDeleterCannotBatchCopyANewsItem(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a news list batch copy to an administrator who may not create items');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7iedelete');
		$this->sendBatch($I, self::ROUTE_ITEM_LIST, 'copy', $itemId);

		$I->seeNumRecords(1, 'e107_news', array('news_title' => self::ITEM_TITLE));
	}

	public function aNewsCreatorCannotBatchWriteANewsItemUnderAnotherSpelling(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a news list batch write to an administrator who may only create items, however the command is spelt');

		$categoryId = $this->seedCategory($I);

		$this->loginAsAdminHolding($I, 'p7iecreate');

		foreach(array('Boolreverse__news_sticky', 'bool_reverse__news_sticky') as $trigger)
		{
			$itemId = $this->seedNewsItem($I, $categoryId);

			$this->sendBatch($I, self::ROUTE_ITEM_LIST, $trigger, $itemId);

			$I->seeInDatabase('e107_news', array('news_id' => $itemId, 'news_sticky' => 0));
		}
	}

	public function aNewsDeleterCannotBatchCopyANewsItemUnderAnotherSpelling(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a news list batch copy to an administrator who may not create items, however the command is spelt');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7iedelete');

		foreach(array('Copy', 'co_py', 'CO-PY') as $trigger)
		{
			$this->sendBatch($I, self::ROUTE_ITEM_LIST, $trigger, $itemId);
		}

		$I->seeNumRecords(1, 'e107_news', array('news_title' => self::ITEM_TITLE));
	}

	public function aNewsItemAdministratorCannotBatchWriteAColumnTheListDoesNotOffer(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a news list batch write to a column the list offers no batch for, however the command is spelt');

		$categoryId = $this->seedCategory($I);

		$this->loginAsAdminHolding($I, 'p7ieitem');

		foreach(array('boolreverse__news_comment_total', 'Boolreverse__news_comment_total') as $trigger)
		{
			$itemId = $this->seedNewsItem($I, $categoryId);

			$this->sendBatch($I, self::ROUTE_ITEM_LIST, $trigger, $itemId);

			$I->seeInDatabase('e107_news', array('news_id' => $itemId, 'news_comment_total' => 0));
		}
	}

	public function aNewsCreatorCannotRewriteANewsBodyThroughTheListsOwnBatch(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the news list\'s own body rewrite batch to an administrator who may only create items');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);
		$I->updateInDatabase('e107_news', array('news_body' => self::IMAGE_BODY), array('news_id' => $itemId));

		$this->loginAsAdminHolding($I, 'p7iecreate');
		$this->sendBatch($I, self::ROUTE_ITEM_LIST, 'image_bbcode', $itemId);

		$I->seeInDatabase('e107_news', array('news_id' => $itemId, 'news_body' => self::IMAGE_BODY));
	}

	public function aNewsCreatorCannotBatchWriteANewsItemThroughACommandThatNamesNothing(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a news list batch whose command names no handler to an administrator who may only create items');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7iecreate');
		$this->sendBatch($I, self::ROUTE_ITEM_LIST, '__news_title', $itemId);

		$I->seeInDatabase('e107_news', array('news_id' => $itemId, 'news_title' => self::ITEM_TITLE));
	}

	public function aNewsItemAdministratorStillBatchWritesANewsItem(AcceptanceTester $I)
	{
		$I->wantTo('Keep the news list batch write working for an administrator who may edit items');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7ieitem');
		$this->sendBatch($I, self::ROUTE_ITEM_LIST, 'bool__news_sticky__1', $itemId);

		$I->seeInDatabase('e107_news', array('news_id' => $itemId, 'news_sticky' => 1));
	}

	public function aFaqsAdministratorOutsideTheEditClassCannotBatchMoveAQuestion(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a FAQ list batch write to an administrator outside the class the site lets edit FAQs');

		$I->havePluginInstalled('faqs');
		$faqId = $this->seedFaq($I, 1);
		$I->probe('act=faqsedit&class=mainadmin');

		$this->loginAsFaqsAdministrator($I);
		$this->sendBatch($I, self::ROUTE_FAQ_LIST, 'faq_parent__99', $faqId, 'multiselect');

		$I->seeInDatabase('e107_faqs', array('faq_id' => $faqId, 'faq_parent' => 1));
	}

	public function aNewsItemAdministratorCannotDeleteANewsItemFromTheRow(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a news row delete to an administrator who may only edit items');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7ieitem');
		$this->sendConfirmedRowDelete($I, self::ROUTE_ITEM_LIST, $itemId);

		$I->seeInDatabase('e107_news', array('news_id' => $itemId));
	}

	public function aNewsItemAdministratorCannotBatchDeleteANewsItem(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a news batch delete to an administrator who may only edit items');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7ieitem');
		$this->sendConfirmedBatchDelete($I, self::ROUTE_ITEM_LIST, $itemId);

		$I->seeInDatabase('e107_news', array('news_id' => $itemId));
	}

	public function aNewsItemAdministratorCannotBatchDeleteANewsItemUnderAnotherSpelling(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a news batch delete to an administrator who may only edit items, however the command is spelt');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7ieitem');

		foreach(array('Delete', 'de_lete', 'DE-LETE') as $command)
		{
			$this->sendConfirmedBatchDelete($I, self::ROUTE_ITEM_LIST, $itemId, $command);
		}

		$I->seeInDatabase('e107_news', array('news_id' => $itemId));
	}

	public function aCategoryAdministratorCannotDeleteANewsItemOnTheGrid(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a news grid row delete to an administrator who may only edit categories');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7iecategory');
		$this->sendConfirmedRowDelete($I, self::ROUTE_ITEM_GRID, $itemId);

		$I->seeInDatabase('e107_news', array('news_id' => $itemId));
	}

	public function aNewsDeleterStillDeletesANewsItemFromTheRow(AcceptanceTester $I)
	{
		$I->wantTo('Keep the news row delete working for an administrator who may delete items');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7iedelete');
		$this->sendConfirmedRowDelete($I, self::ROUTE_ITEM_LIST, $itemId);

		$I->dontSeeInDatabase('e107_news', array('news_id' => $itemId));
	}

	public function aNewsDeleterStillBatchDeletesANewsItem(AcceptanceTester $I)
	{
		$I->wantTo('Keep the news batch delete working for an administrator who may delete items');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7iedelete');
		$this->sendConfirmedBatchDelete($I, self::ROUTE_ITEM_LIST, $itemId);

		$I->dontSeeInDatabase('e107_news', array('news_id' => $itemId));
	}

	/**
	 * Render the item, which files the editable map in the session, then save through the front-end editor.
	 *
	 * @param int $id row to write
	 * @param string $shortcode shortcode name the map keys the field under
	 * @param string $value posted content
	 * @return void
	 */
	private function saveOnTheFrontEnd(AcceptanceTester $I, $id, $shortcode, $value)
	{
		$I->amOnPage(self::ROUTE_NEWS_ITEM.$id);

		$this->postFrontEndSave($I, $id, $shortcode, $value);
	}

	/**
	 * @param int $id row to write
	 * @param string $shortcode shortcode name the map keys the field under
	 * @param string $value posted content
	 * @return void
	 */
	private function postFrontEndSave(AcceptanceTester $I, $id, $shortcode, $value)
	{
		$sessionToken = $this->sessionToken($I);

		$I->sendPostRequest(self::ROUTE_FRONT_SAVE, array(
			'content'   => $value,
			'sc'        => $shortcode,
			'id'        => $id,
			'token'     => $sessionToken,
			'ajax_used' => 1,
			'e-token'   => $sessionToken,
		));
	}

	/**
	 * Post what an inline editor posts, with both tokens minted from the session.
	 *
	 * @param string $route
	 * @param string $field column to write
	 * @param string $value posted value
	 * @return void
	 */
	private function saveInline(AcceptanceTester $I, $route, $field, $value)
	{
		$I->sendPostRequest($route, array(
			'name'    => $field,
			'value'   => $value,
			'token'   => $this->inlineToken($I),
			'e-token' => $this->sessionToken($I),
		));
	}

	/**
	 * Post what the news item form posts.
	 *
	 * @param string $route
	 * @param string $submit the value of the form's submit trigger
	 * @param int $categoryId
	 * @param string $title
	 * @param array $extra further fields
	 * @return void
	 */
	private function submitNewsForm(AcceptanceTester $I, $route, $submit, $categoryId, $title, $extra = array())
	{
		$I->sendPostRequest($route, array_merge(array(
			'news_title'            => $title,
			'news_category'         => $categoryId,
			'news_body'             => array('Posted by InlineEditPermsCest.'),
			'news_summary'          => '',
			'news_thumbnail'        => '',
			'news_meta_title'       => '',
			'news_meta_keywords'    => '',
			'news_meta_description' => '',
			'news_sef'              => '',
			'news_class'            => '0',
			'news_render_type'      => '0',
			'news_template'         => 'default',
			'news_author'           => '1',
			'news_datestamp'        => '',
			'news_start'            => '',
			'news_end'              => '',
			'news_sticky'           => '0',
			'news_allow_comments'   => '0',
			'news_meta_robots'      => '',
			'etrigger_submit'       => $submit,
			'e-token'               => $this->sessionToken($I),
		), $extra));
	}

	/**
	 * Post what a list's batch menu posts.
	 *
	 * @param string $route
	 * @param string $trigger etrigger_batch value
	 * @param int $id the one row the selection carries
	 * @param string $selection name of the list's checkbox column
	 * @return void
	 */
	private function sendBatch(AcceptanceTester $I, $route, $trigger, $id, $selection = 'news_selected')
	{
		$this->postWithSessionToken($I, $route, \Test\AdminListPost::batch($trigger, $id, $selection));
	}

	/**
	 * @param string $route
	 * @param array $payload posted keys, which the session's form token is added to
	 * @return void
	 */
	private function postWithSessionToken(AcceptanceTester $I, $route, array $payload)
	{
		$payload['e-token'] = $this->sessionToken($I);

		$I->sendPostRequest($route, $payload);
	}

	/**
	 * Post what a row delete's confirm screen posts.
	 *
	 * @param string $route
	 * @param int $id row to delete
	 * @return void
	 */
	private function sendConfirmedRowDelete(AcceptanceTester $I, $route, $id)
	{
		$this->postWithSessionToken($I, $route, \Test\AdminListPost::confirmedRowDelete($id));
	}

	/**
	 * Post what a batch delete's confirm screen posts.
	 *
	 * @param string $route
	 * @param int $id row to delete
	 * @param string $command the batch command, as the confirm screen or a caller spells it
	 * @return void
	 */
	private function sendConfirmedBatchDelete(AcceptanceTester $I, $route, $id, $command = 'delete')
	{
		$this->postWithSessionToken($I, $route, \Test\AdminListPost::confirmedBatchDelete($id, $command));
	}

	/**
	 * Post what the drag-and-drop sort posts.
	 *
	 * @param int $id the one row the selection carries
	 * @return void
	 */
	private function sortCategories(AcceptanceTester $I, $id)
	{
		$I->sendPostRequest(self::ROUTE_CATEGORY_SORT, array(
			'all'     => array('row-'.$id),
			'e-token' => $this->sessionToken($I),
		));
	}

	/**
	 * Minted by the probe, because the refused administrators cannot load the lists that print it.
	 *
	 * @return string the session's form token, which is also the key its editable map is filed under
	 */
	private function sessionToken(AcceptanceTester $I)
	{
		return $this->probeValue($I, 'act=formtoken');
	}

	/**
	 * @return string the inline token this session's pages carry
	 */
	private function inlineToken(AcceptanceTester $I)
	{
		return $this->probeValue($I, 'act=inlinetoken');
	}

	/**
	 * @param string $query
	 * @return string what the probe answered, less its acknowledgement
	 */
	private function probeValue(AcceptanceTester $I, $query)
	{
		return trim(str_replace('PROBE_OK', '', $I->probe($query)));
	}

	/**
	 * Seed the category the news item belongs to.
	 *
	 * @return int category id
	 */
	private function seedCategory(AcceptanceTester $I)
	{
		return (int) $I->haveInDatabase('e107_news_category', array(
			'category_name'             => self::CATEGORY_NAME,
			'category_sef'              => 'p7ie-category',
			'category_meta_description' => '',
			'category_meta_keywords'    => '',
			'category_icon'             => '',
			'category_order'            => self::CATEGORY_ORDER,
		));
	}

	/**
	 * Seed a news item the public site renders.
	 *
	 * @param int $categoryId
	 * @return int news id
	 */
	private function seedNewsItem(AcceptanceTester $I, $categoryId)
	{
		return (int) $I->haveInDatabase('e107_news', array(
			'news_title'            => self::ITEM_TITLE,
			'news_sef'              => 'p7ie-item',
			'news_body'             => 'Seeded by InlineEditPermsCest.',
			'news_extended'         => '',
			'news_meta_title'       => '',
			'news_meta_keywords'    => '',
			'news_meta_description' => '',
			'news_datestamp'        => self::PWCHANGE,
			'news_author'           => 1,
			'news_category'         => $categoryId,
			'news_class'            => '0',
			'news_render_type'      => '0',
			'news_summary'          => '',
			'news_thumbnail'        => '',
		));
	}

	/**
	 * @param int $parent FAQ category
	 * @return int faq id
	 */
	private function seedFaq(AcceptanceTester $I, $parent = 0)
	{
		return (int) $I->haveInDatabase('e107_faqs', array(
			'faq_parent'    => $parent,
			'faq_question'  => self::ITEM_TITLE,
			'faq_answer'    => 'Seeded by InlineEditPermsCest.',
			'faq_author_ip' => '',
			'faq_tags'      => '',
		));
	}

	/**
	 * Seed an administrator holding one permission code and log in as them.
	 *
	 * @param string $loginName a key of {@see InlineEditPermsCest::ADMINS}
	 * @return void
	 */
	private function loginAsAdminHolding(AcceptanceTester $I, $loginName)
	{
		$this->loginAsAdministrator($I, $loginName, self::ADMINS[$loginName]);
	}

	/**
	 * Seed an administrator holding the FAQs plugin's permission and nothing else, and log in as them.
	 *
	 * @return void
	 */
	private function loginAsFaqsAdministrator(AcceptanceTester $I)
	{
		$pluginId = $I->grabFromDatabase('e107_plugin', 'plugin_id', array('plugin_path' => 'faqs'));

		$this->loginAsAdministrator($I, 'p7iefaqs', 'P'.$pluginId.'.');
	}

	/**
	 * @param string $loginName also the password
	 * @param string $perms user_perms, with the trailing dot {@see e_userperms::updatePerms()} writes
	 * @return void
	 */
	private function loginAsAdministrator(AcceptanceTester $I, $loginName, $perms)
	{
		$I->haveInDatabase('e107_user', array(
			'user_name'      => $loginName,
			'user_loginname' => $loginName,
			'user_email'     => $loginName.'@example.com',
			'user_password'  => \Test\Password::hash($loginName),
			'user_join'      => self::PWCHANGE,
			'user_class'     => '',
			'user_admin'     => 1,
			'user_perms'     => $perms,
			'user_pwchange'  => self::PWCHANGE,
			'user_xup'       => '',
			'user_prefs'     => '',
			'user_signature' => '',
			'user_realm'     => '',
		));

		$I->loginAsAdmin($loginName, $loginName);
		$I->dontSeeElement('input[name=authpass]');
	}

	/**
	 * The front-end editor is off on a stock install, so the test that needs it switches it on through the application and _before puts it back.
	 *
	 * @return string
	 */
	private function probeSource()
	{
		return <<<PHP
<?php
// Fixture for InlineEditPermsCest.
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

\$core = e107::getConfig('core');

switch(isset(\$_GET['act']) ? \$_GET['act'] : '')
{
	case 'reset':
		e107::getDb()->delete('user', "user_loginname LIKE 'p7ie%'");
		\$core->set('inline_editing', e_UC_NOBODY)->save(false, true, false);

		echo "PROBE_OK\n";
		break;

	case 'enable':
		\$core->set('inline_editing', e_UC_ADMIN)->save(false, true, false);

		echo "PROBE_OK\n";
		break;

	case 'faqsedit':
		\$classes = array('mainadmin' => e_UC_MAINADMIN, 'admin' => e_UC_ADMIN);
		e107::getPlugConfig('faqs')->set('admin_faq_edit', \$classes[\$_GET['class']])->save(false, true, false);

		echo "PROBE_OK\n";
		break;

	case 'seedmap':
		\$map = array(
			'table'      => 'news',
			'pid'        => 'news_id',
			'vars'       => 'news_item',
			'shortcodes' => array(
				'news_title' => array('field' => 'news_title', 'type' => 'text', 'container' => 'span'),
			),
		);

		if(isset(\$_GET['perms']))
		{
			\$map['perms'] = \$_GET['perms'];
		}

		\$_SESSION['editable'][e107::getSession()->getFormToken()] = \$map;

		echo "PROBE_OK\n";
		break;

	case 'formtoken':
		echo "PROBE_OK\n".e107::getSession()->getFormToken()."\n";
		break;

	case 'inlinetoken':
		echo "PROBE_OK\n".password_hash(session_id(), PASSWORD_DEFAULT, array('cost' => 4))."\n";
		break;

	default:
		echo "PROBE_UNKNOWN_ACTION\n";
}
PHP;
	}
}
