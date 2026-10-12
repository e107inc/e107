<?php


	class commentTest extends \Test\Unit
	{

		/** @var comment */
		protected $cm;

		/** @var array */
		private $savedPrefs = array();

		protected function _before()
		{
			foreach(array('comments_icon', 'nested_comments') as $key)
			{
				$this->savedPrefs[$key] = e107::getConfig()->get($key);
			}

			e107::getDb()->truncate('comments');
			$path = codecept_data_dir().'comments/commentsSetup.xml';
			$result = e107::getXml()->e107Import($path);
			if(!empty($result['failed']))
			{
				$this->fail("Comment setup failed. ".print_r($result['failed'], true));
			}

			try
			{
				$this->cm = e107::getComment();
			}

			catch(Exception $e)
			{
				$this->assertTrue(false, $e->getMessage());
			}

		}

		protected function _after()
		{
			foreach($this->savedPrefs as $key => $value)
			{
				e107::getConfig()->set($key, $value);
			}
		}

		public function testRender()
		{
			$plugin = '_blank';
			$id     = 3;
			$subject = 'My blank item subject';
			$rate   = true;

			$result = $this->cm->render($plugin, $id, $subject, $rate);

			$this->assertIsString($result);

			$this->assertStringContainsString('e-comment-form',$result);

		}

		public function testLoadNested()
		{
			$result = $this->cm->loadNested(55,'profile', 'desc');

			$this->assertNotempty($result['profile']);
			$this->assertCount(2, $result['profile'][2]);
			$this->assertCount(2, $result['profile'][4]);
		}

		public function testGetNested()
		{
			$this->cm->loadNested(55, 'profile', 'desc');

			$result = $this->cm->getNested(4, 'profile');

			$this->assertEquals('sub-red 1 child-1', $result[0]['comment_comment']);
			$this->assertEquals('sub-red 1 child-2', $result[1]['comment_comment']);

			$result = $this->cm->getNested(2, 'profile');

			$this->assertEquals('sub-red 2', $result[0]['comment_comment']);
			$this->assertEquals('sub-red 1', $result[1]['comment_comment']);

		}

		public function testGetCommentsIndentsEveryReplyOneLevelBelowItsParent()
		{
			e107::getConfig()->set('nested_comments', 1);

			$result = $this->cm->getComments('profile', 55, 0, array('action' => 'comment'));

			$this->assertSame(2, substr_count($result['comments'], 'offset-md-2'));
		}

		public function testGetCommentsShowsNoNewIconWithoutLastVisit()
		{
			global $NEWIMAGE;

			e107::getConfig()->set('comments_icon', 1)->set('nested_comments', 1);

			$result = $this->cm->getComments('profile', 55, 0, array('action' => 'comment', 'subject' => 'New Title'));

			$this->assertStringContainsString('sub-red 1 child-1', $result['comments']);
			$this->assertSame(IMAGE_nonew_comments, $NEWIMAGE);
		}

		/**
		 * The fixture's profile comments are left on item 55, the id a news item can also have.
		 */
		public function testGetCommentsListsOnlyTheCommentsOfTheTypeAsked()
		{
			$this->haveNewsComment(55, 0, 'news on 55');

			$result = $this->cm->getComments('news', 55, 0, array('action' => 'comment'));

			$this->assertStringContainsString('news on 55', $result['comments']);
			$this->assertStringNotContainsString('blue', $result['comments']);
		}

		public function testCountCommentsCountsOnlyTheTypeAsked()
		{
			$this->haveNewsComment(55, 0, 'news on 55');

			$this->assertSame(1, $this->cm->count_comments('news', 55));
		}

		public function testLoadNestedLoadsOnlyTheRepliesOfTheTypeAsked()
		{
			$parent = $this->haveNewsComment(55, 0, 'news on 55');
			$this->haveNewsComment(55, $parent, 'news reply on 55');

			$result = $this->cm->loadNested(55, 0, 'desc');

			$this->assertSame(array($parent), array_keys($result[0]));
			$this->assertSame('news reply on 55', $result[0][$parent][0]['comment_comment']);
		}

		public function testDeleteCommentsDeletesOnlyTheCommentsOfTheTypeAsked()
		{
			$this->haveNewsComment(55, 0, 'news on 55');

			$this->assertSame(1, $this->cm->delete_comments('news', 55));
			$this->assertSame(7, e107::getDb()->createQueryBuilder()->from('comments')
				->where('comment_item_id', 55)->where('comment_type', 'profile')->count());
		}

		/**
		 * @param int    $itemId
		 * @param int    $pid
		 * @param string $text
		 * @return int comment_id
		 */
		private function haveNewsComment($itemId, $pid, $text)
		{
			return (int) e107::getDb()->createQueryBuilder()->insert('comments')->insertGetId(array(
				'comment_pid'         => $pid,
				'comment_item_id'     => $itemId,
				'comment_type'        => '0',
				'comment_author_id'   => 1,
				'comment_author_name' => 'admin',
				'comment_datestamp'   => 1700000000,
				'comment_comment'     => $text,
				'comment_blocked'     => 0,
			));
		}


	}
