<?php


	class commentTest extends \Codeception\Test\Unit
	{

		/** @var comment */
		protected $cm;

		protected function _before()
		{

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
