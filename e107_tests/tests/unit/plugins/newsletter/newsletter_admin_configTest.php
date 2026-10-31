<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Opens the newsletter plugin's admin area as the CLI main admin.
 *
 * @group plugins
 * @see https://github.com/e107inc/e107/issues/6873
 */
class newsletter_admin_configTest extends \Test\Unit
{
	use \Test\ScriptClassPages;
	use \Test\NewsletterRows;

	const MEMBER = 'newsletter_admin_configTest';

	const SCRIPT = 'e107_plugins/newsletter/admin_config.php';

	protected function _before()
	{
		$this->haveNewsletterTable();
	}

	protected function _after()
	{
		$this->dropNewsletterRows();
		e107::getDb()->createQueryBuilder()->delete('user')->whereLike('user_loginname', self::MEMBER.'%')->execute();
	}

	public function testViewingAListWithARepeatedIdListsEverySubscriberOnceAndKeepsTheList()
	{
		list($first, $second, $third) = $this->members(3);
		$stored = chr(1).$first.chr(1).$second.chr(1).$second.chr(1).$third;
		$id = $this->haveNewsletter(0, array($first, $second, $second, $third));

		$page = $this->open('vs.'.$id);

		self::assertSame($stored, $this->storedSubscribers($id), "viewing the list changed it:\n".$page);

		foreach(array($first, $second, $third) as $member)
		{
			self::assertSame(1, substr_count($page, '?remove.'.$id.'.'.$member.'&amp;'), 'member '.$member." is not listed once:\n".$page);
		}
	}

	/**
	 * @param int $count
	 * @return int[] the ids of $count new members, in the order they joined
	 */
	private function members($count)
	{
		$ids = array();

		for($i = 0; $i < $count; $i++)
		{
			$name = self::MEMBER.$i;
			$ids[] = (int) e107::getDb()->createQueryBuilder()->insert('user')->insertGetId(array(
				'user_name'      => $name,
				'user_loginname' => $name,
				'user_email'     => $name.'@example.test',
				'user_password'  => '',
				'user_join'      => time(),
				'user_ban'       => 0,
				'user_class'     => '',
			));
		}

		self::assertNotContains(0, $ids, 'a member was not seeded');

		return $ids;
	}

	/**
	 * @param string $query
	 * @param array $post
	 * @return string the page
	 */
	private function open($query, array $post = array())
	{
		return $this->openAsScriptClass(e_UC_MAINADMIN, self::SCRIPT, $query, $post, self::newsletterInstalled());
	}
}
