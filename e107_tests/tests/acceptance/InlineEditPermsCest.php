<?php

/**
 * Who may write a news or FAQ row, and which row, through each route that writes one, and who may read the lists they are written from.
 *
 * @see e107_handlers/news_class.php  the front-end editable map
 * @see e107_web/js/inline.php  the front-end save endpoint
 * @see e107_handlers/admin_ui.php  e_admin_ui::InlineAjaxPage(), SortAjaxPage()
 * @see e107_handlers/admin_ui.php  e_admin_controller_ui::_manageSubmit(), which the create and edit forms save through
 * @see e107_handlers/admin_ui.php  e_admin_controller::_preDispatch(), which runs the default action for an action that names no page of its own
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

	/** A custom page on the public site. */
	const ROUTE_PAGE = '/page.php?id=';

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

	/** An action no news item page answers to, so the default action, the list, runs in its place. */
	const ROUTE_ITEM_NO_PAGE = '/e107_admin/newspost.php?mode=main&action=p7ienopage';

	/** An action no submitted news page answers to. */
	const ROUTE_SUBMITTED_NO_PAGE = '/e107_admin/newspost.php?mode=sub&action=p7ienopage';

	/** An action no FAQ category page answers to. */
	const ROUTE_FAQ_CATEGORY_NO_PAGE = '/e107_plugins/faqs/admin_config.php?mode=cat&action=p7ienopage';

	/** The news item list over ajax. */
	const ROUTE_ITEM_LIST_AJAX = '/e107_admin/newspost.php?mode=main&action=list&ajax_used=1';

	/** The news item list's ajax page, named by an action on a request that is not ajax. */
	const ROUTE_ITEM_LIST_AJAX_PAGE = '/e107_admin/newspost.php?mode=main&action=listajax';

	/** The submitted news list's ajax page, named by an action on a request that is not ajax. */
	const ROUTE_SUBMITTED_LIST_AJAX_PAGE = '/e107_admin/newspost.php?mode=sub&action=listajax';

	/** The news category list's ajax page, named by an action on a request that is not ajax. */
	const ROUTE_CATEGORY_LIST_AJAX_PAGE = '/e107_admin/newspost.php?mode=cat&action=listajax';

	/** The FAQ category list's ajax page, named by an action on a request that is not ajax. */
	const ROUTE_FAQ_CATEGORY_LIST_AJAX_PAGE = '/e107_plugins/faqs/admin_config.php?mode=cat&action=listajax';

	/** An action that names the news item controller's own dispatch methods. */
	const ROUTE_ITEM_DISPATCH = '/e107_admin/newspost.php?mode=main&action=dispatch';

	/** An action that names the news item controller's own per-page getter as its page. */
	const ROUTE_ITEM_PER_PAGE = '/e107_admin/newspost.php?mode=main&action=getper';

	/**
	 * Administrators seeded per test, login name => user_perms with the trailing dot e_userperms::updatePerms() writes.
	 *
	 * @see e107_handlers/user_handler.php  e_userperms::getPermList()
	 */
	const ADMINS = array(
		'p7iecategory'  => 'H4.',
		'p7iecreate'    => 'H0.',
		'p7iecreatenav' => 'H0.I.',
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

	/** The seeded submitted news item's title. */
	const SUBMISSION_TITLE = 'p7ie submission';

	/** The seeded FAQ category's title. */
	const FAQ_CATEGORY_TITLE = 'p7ie faq category';

	/** What the 403 page says. */
	const REFUSED = 'Access to the requested page is denied.';

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

	public function aPagesAdministratorCannotSaveAChapterMenuOnTheFrontEnd(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a front-end save of a chapter menu to an administrator who may only edit custom pages');

		list($pageId, $menuId) = $this->seedChapterMenusPage($I);

		$I->probe('act=enable');
		$this->loginAsAdministrator($I, 'p7iepages', '5.');

		$I->amOnPage(self::ROUTE_PAGE.$pageId);
		$this->postFrontEndSave($I, $menuId, 'cmenubody', self::ATTACK_VALUE);

		$I->seeInDatabase('e107_page', array('page_id' => $menuId, 'menu_text' => self::ITEM_TITLE));
	}

	public function aMenusAdministratorSavesAChapterMenuOnTheFrontEnd(AcceptanceTester $I)
	{
		$I->wantTo('Keep the front-end editor of a chapter menu working for an administrator who may edit custom menus');

		list($pageId, $menuId) = $this->seedChapterMenusPage($I);

		$I->probe('act=enable');
		$this->loginAsAdministrator($I, 'p7iemenus', 'J.');

		$I->amOnPage(self::ROUTE_PAGE.$pageId);
		$this->postFrontEndSave($I, $menuId, 'cmenubody', self::SAVED_VALUE);

		$I->seeInDatabase('e107_page', array('page_id' => $menuId, 'menu_text' => '[html]'.self::SAVED_VALUE.'[/html]'));
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

	public function aMapFiledBeforeTheUpgradeIsNotHonoured(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a front-end inline save through a map an earlier release filed in the session');

		$categoryId = $this->seedCategory($I);
		$itemId = $this->seedNewsItem($I, $categoryId);

		$this->loginAsAdminHolding($I, 'p7iecategory');
		$I->probe('act=seedoldmap');

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

	public function aNewsDeleterCannotBatchCreateANavigationLink(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a news list batch navigation link to an administrator who may not create items');

		$itemId = $this->seedNewsItem($I, $this->seedCategory($I));

		$this->loginAsAdminHolding($I, 'p7iedelete');
		$this->sendBatch($I, self::ROUTE_ITEM_LIST, 'url', $itemId);

		$I->dontSeeInDatabase('e107_links', array('link_name' => self::ITEM_TITLE));
	}

	public function aNewsCreatorWithoutTheLinksPermissionCannotBatchCreateANavigationLink(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a news list batch navigation link to an administrator who may create items but not manage navigation links');

		$itemId = $this->seedNewsItem($I, $this->seedCategory($I));

		$this->loginAsAdminHolding($I, 'p7iecreate');
		$this->sendBatch($I, self::ROUTE_ITEM_LIST, 'url', $itemId);

		$I->dontSeeInDatabase('e107_links', array('link_name' => self::ITEM_TITLE));
	}

	public function aNewsCreatorStillBatchCreatesANavigationLink(AcceptanceTester $I)
	{
		$I->wantTo('Keep the news list batch navigation link working for an administrator who may create items and manage navigation links');

		$itemId = $this->seedNewsItem($I, $this->seedCategory($I));

		$this->loginAsAdminHolding($I, 'p7iecreatenav');
		$this->sendBatch($I, self::ROUTE_ITEM_LIST, 'url', $itemId);

		$I->seeInDatabase('e107_links', array('link_name' => self::ITEM_TITLE));
	}

	public function aNewsCreatorCannotBatchCreateAFeatureBoxItemTheListDoesNotOffer(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a news list batch feature box item, which the news list does not offer');

		$I->havePluginInstalled('featurebox');
		$itemId = $this->seedNewsItem($I, $this->seedCategory($I));
		$items = $I->grabNumRecords('e107_featurebox');

		$this->loginAsAdminHolding($I, 'p7iecreate');
		$this->sendBatch($I, self::ROUTE_ITEM_LIST, 'featurebox', $itemId);

		$I->seeNumRecords($items, 'e107_featurebox');
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

	public function aCategoryAdministratorCannotOpenTheNewsListThroughAnActionWithNoPage(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the news list to an administrator who may only edit categories when it stands in for an action with no page');

		$this->requestWithANewsItemAs($I, 'p7iecategory', self::ROUTE_ITEM_NO_PAGE);
		$this->seeRefusedWithout($I, self::ITEM_TITLE);
	}

	public function aCategoryAdministratorCannotLoadTheNewsListOverAjaxThroughAnActionWithNoPage(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the ajax news list to an administrator who may only edit categories when it stands in for an action with no page');

		$this->requestWithANewsItemAs($I, 'p7iecategory', self::ROUTE_ITEM_NO_PAGE.'&ajax_used=1');
		$I->dontSee(self::ITEM_TITLE);
	}

	public function aNewsItemAdministratorStillOpensTheNewsListThroughAnActionWithNoPage(AcceptanceTester $I)
	{
		$I->wantTo('Keep the news list standing in for an action with no page for an administrator who may list items');

		$this->requestWithANewsItemAs($I, 'p7ieitem', self::ROUTE_ITEM_NO_PAGE);
		$I->see(self::ITEM_TITLE);
	}

	public function aCategoryAdministratorCannotOpenTheSubmittedNewsThroughAnActionWithNoPage(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the submitted news list to an administrator who may only edit categories when it stands in for an action with no page');

		$this->requestWithASubmissionAs($I, 'p7iecategory', self::ROUTE_SUBMITTED_NO_PAGE);
		$this->seeRefusedWithout($I, self::SUBMISSION_TITLE);
	}

	public function aSubmittedNewsAdministratorStillOpensTheSubmittedNewsThroughAnActionWithNoPage(AcceptanceTester $I)
	{
		$I->wantTo('Keep the submitted news list standing in for an action with no page for an administrator who may moderate submissions');

		$this->requestWithASubmissionAs($I, 'p7iesubmitted', self::ROUTE_SUBMITTED_NO_PAGE);
		$I->see(self::SUBMISSION_TITLE);
	}

	public function aFaqsAdministratorOutsideTheCategoryClassesCannotOpenTheCategoryListThroughAnActionWithNoPage(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the FAQ category list to an administrator its user classes leave out when it stands in for an action with no page');

		$this->requestWithAFaqCategoryAsAFaqsAdministrator($I, self::ROUTE_FAQ_CATEGORY_NO_PAGE);
		$this->seeRefusedWithout($I, self::FAQ_CATEGORY_TITLE);
	}

	public function aCategoryAdministratorCannotOpenTheNewsListThroughItsAjaxPage(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the news list to an administrator who may only edit categories when a plain request names its ajax page');

		$this->requestWithANewsItemAs($I, 'p7iecategory', self::ROUTE_ITEM_LIST_AJAX_PAGE);
		$this->seeRefusedWithout($I, self::ITEM_TITLE);
	}

	public function aCategoryAdministratorCannotOpenTheSubmittedNewsThroughItsAjaxPage(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the submitted news list to an administrator who may only edit categories when a plain request names its ajax page');

		$this->requestWithASubmissionAs($I, 'p7iecategory', self::ROUTE_SUBMITTED_LIST_AJAX_PAGE);
		$this->seeRefusedWithout($I, self::SUBMISSION_TITLE);
	}

	public function aNewsCreatorCannotOpenTheCategoryListThroughItsAjaxPage(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the news category list to an administrator who may only create news items when a plain request names its ajax page');

		$this->requestWithANewsItemAs($I, 'p7iecreate', self::ROUTE_CATEGORY_LIST_AJAX_PAGE);
		$this->seeRefusedWithout($I, self::CATEGORY_NAME);
	}

	public function aFaqsAdministratorOutsideTheCategoryClassesCannotOpenTheCategoryListThroughItsAjaxPage(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the FAQ category list to an administrator its user classes leave out when a plain request names its ajax page');

		$this->requestWithAFaqCategoryAsAFaqsAdministrator($I, self::ROUTE_FAQ_CATEGORY_LIST_AJAX_PAGE);
		$this->seeRefusedWithout($I, self::FAQ_CATEGORY_TITLE);
	}

	public function aNewsItemAdministratorStillLoadsTheNewsListOverAjax(AcceptanceTester $I)
	{
		$I->wantTo('Keep the ajax news list working for an administrator who may list items');

		$this->requestWithANewsItemAs($I, 'p7ieitem', self::ROUTE_ITEM_LIST_AJAX);
		$I->seeInSource(self::ITEM_TITLE);
	}

	public function aNewsItemAdministratorStillOpensTheNewsListWhenAPlainRequestNamesItsAjaxPage(AcceptanceTester $I)
	{
		$I->wantTo('Give an administrator who may list items the news list when a plain request names its ajax page');

		$this->requestWithANewsItemAs($I, 'p7ieitem', self::ROUTE_ITEM_LIST_AJAX_PAGE);
		$I->see(self::ITEM_TITLE);
	}

	public function aNewsItemAdministratorGetsTheNewsListWhenARequestNamesTheControllersOwnDispatch(AcceptanceTester $I)
	{
		$I->wantTo('Give an administrator who may list items the news list when a request names a dispatch method of the controller itself');

		$this->requestWithANewsItemAs($I, 'p7ieitem', self::ROUTE_ITEM_DISPATCH);
		$I->see(self::ITEM_TITLE);
	}

	public function aCategoryAdministratorIsRefusedWhenARequestNamesTheControllersOwnDispatch(AcceptanceTester $I)
	{
		$I->wantTo('Refuse the news list to an administrator who may only edit categories when a request names a dispatch method of the controller itself');

		$this->requestWithANewsItemAs($I, 'p7iecategory', self::ROUTE_ITEM_DISPATCH);
		$this->seeRefusedWithout($I, self::ITEM_TITLE);
	}

	public function aNewsItemAdministratorGetsTheNewsListWhenARequestNamesTheControllersOwnPerPageGetter(AcceptanceTester $I)
	{
		$I->wantTo('Give an administrator who may list items the news list when a request names the per-page getter of the controller itself');

		$this->requestWithANewsItemAs($I, 'p7ieitem', self::ROUTE_ITEM_PER_PAGE);
		$I->see(self::ITEM_TITLE);
	}

	/**
	 * Seed a news item, log in as one of the seeded administrators and request a route.
	 *
	 * @param string $loginName a key of {@see InlineEditPermsCest::ADMINS}
	 * @param string $route
	 * @return void
	 */
	private function requestWithANewsItemAs(AcceptanceTester $I, $loginName, $route)
	{
		$this->seedNewsItem($I, $this->seedCategory($I));
		$this->loginAsAdminHolding($I, $loginName);

		$I->amOnPage($route);
	}

	/**
	 * Seed a submitted news item, log in as one of the seeded administrators and request a route.
	 *
	 * @param string $loginName a key of {@see InlineEditPermsCest::ADMINS}
	 * @param string $route
	 * @return void
	 */
	private function requestWithASubmissionAs(AcceptanceTester $I, $loginName, $route)
	{
		$this->seedSubmission($I, $this->seedCategory($I));
		$this->loginAsAdminHolding($I, $loginName);

		$I->amOnPage($route);
	}

	/**
	 * Install the FAQs plugin, seed a FAQ category, log in as an administrator holding the plugin's permission alone and request a route.
	 *
	 * @param string $route
	 * @return void
	 */
	private function requestWithAFaqCategoryAsAFaqsAdministrator(AcceptanceTester $I, $route)
	{
		$I->havePluginInstalled('faqs');
		$this->seedFaqCategory($I);
		$this->loginAsFaqsAdministrator($I);

		$I->amOnPage($route);
	}

	/**
	 * See the 403 page, and not the text the refused route would have shown.
	 *
	 * @param string $hidden
	 * @return void
	 */
	private function seeRefusedWithout(AcceptanceTester $I, $hidden)
	{
		$I->dontSee($hidden);
		$I->see(self::REFUSED);
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
	 * Seed a news item a visitor submitted and nobody has approved.
	 *
	 * @param int $categoryId
	 * @return int submitnews id
	 */
	private function seedSubmission(AcceptanceTester $I, $categoryId)
	{
		return (int) $I->haveInDatabase('e107_submitnews', array(
			'submitnews_name'        => 'p7ie submitter',
			'submitnews_email'       => 'p7iesubmitter@example.com',
			'submitnews_title'       => self::SUBMISSION_TITLE,
			'submitnews_category'    => $categoryId,
			'submitnews_item'        => 'Submitted to InlineEditPermsCest.',
			'submitnews_datestamp'   => self::PWCHANGE,
			'submitnews_ip'          => '',
			'submitnews_file'        => '',
			'submitnews_keywords'    => '',
			'submitnews_description' => '',
			'submitnews_summary'     => '',
			'submitnews_media'       => '',
		));
	}

	/**
	 * @return int faq_info id
	 */
	private function seedFaqCategory(AcceptanceTester $I)
	{
		return (int) $I->haveInDatabase('e107_faqs_info', array(
			'faq_info_title' => self::FAQ_CATEGORY_TITLE,
			'faq_info_about' => '',
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
	 * Seed a chapter holding one custom menu, and a custom page whose body lists the chapter's menus through {CHAPTER_MENUS}.
	 *
	 * @return array the page's id, then the menu's
	 */
	private function seedChapterMenusPage(AcceptanceTester $I)
	{
		$chapterId = (int) $I->haveInDatabase('e107_page_chapters', array(
			'chapter_parent'           => 0,
			'chapter_name'             => 'p7ie chapter',
			'chapter_sef'              => 'p7ie-chapter',
			'chapter_meta_description' => '',
			'chapter_meta_keywords'    => '',
			'chapter_manager'          => 254,
			'chapter_icon'             => '',
			'chapter_image'            => '',
			'chapter_order'            => 0,
			'chapter_template'         => 'default',
			'chapter_visibility'       => 0,
		));

		$row = array(
			'page_sef'       => '',
			'page_author'    => 1,
			'page_datestamp' => self::PWCHANGE,
			'page_class'     => '0',
			'page_template'  => 'default',
			'menu_image'     => '',
			'menu_icon'      => '',
			'menu_template'  => 'default',
			'menu_class'     => '0',
		);

		$pageId = (int) $I->haveInDatabase('e107_page', array_merge($row, array(
			'page_title'   => self::ITEM_TITLE,
			'page_chapter' => 0,
			'page_text'    => '{CHAPTER_MENUS: name=p7ie-chapter}',
			'menu_name'    => '',
			'menu_title'   => '',
			'menu_text'    => '',
		)));

		$menuId = (int) $I->haveInDatabase('e107_page', array_merge($row, array(
			'page_title'   => '',
			'page_chapter' => $chapterId,
			'page_text'    => '',
			'menu_name'    => 'p7iemenu',
			'menu_title'   => 'p7ie menu',
			'menu_text'    => self::ITEM_TITLE,
		)));

		return array($pageId, $menuId);
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
		e107::getDb()->delete('links', "link_name LIKE 'p7ie%'");
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

		\$_SESSION['inline_editable'][e107::getSession()->getFormToken()] = \$map;

		echo "PROBE_OK\n";
		break;

	case 'seedoldmap':
		// The news map as releases before the key's rename filed it, under the key they used.
		\$_SESSION['editable'][e107::getSession()->getFormToken()] = array(
			'table'      => 'news',
			'pid'        => 'news_id',
			'vars'       => 'news_item',
			'perms'      => 'H|H4',
			'shortcodes' => array(
				'news_title' => array('field' => 'news_title', 'type' => 'text', 'container' => 'span'),
			),
		);

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
