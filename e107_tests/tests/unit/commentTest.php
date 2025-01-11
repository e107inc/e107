<?php


	class commentTest extends \Codeception\Test\Unit
	{

		/** @var comment */
		protected $cm;

		protected function _before()
		{
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
/*
		public function testModerateComment()
		{

		}

		public function testForm_comment()
		{

		}

		public function testDelete_comments()
		{

		}

		public function testRecalc_user_comments()
		{

		}

		public function testGetCommentData()
		{

		}

		public function test__construct()
		{

		}

		public function testGetCommentPermissions()
		{

		}

		public function testNextprev()
		{

		}

		public function testEnter_comment()
		{

		}

		public function testReplyComment()
		{

		}

		public function testGetCommentType()
		{

		}

		public function testGet_e_comment()
		{

		}

		public function testUpdateComment()
		{

		}
*/
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
/*
		public function testGetComments()
		{

		}

		public function testRender_comment()
		{

		}

		public function testDeleteComment()
		{

		}

		public function testCount_comments()
		{

		}

		public function testParseLayout()
		{

		}

		public function testApproveComment()
		{

		}

		public function testGetTable()
		{

		}

		public function testGet_author_list()
		{

		}

*/


	}
