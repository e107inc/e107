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


	}
