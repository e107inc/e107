<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2019 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_tohtml_linkwordsTest extends \Codeception\Test\Unit
	{

		/** @var e_tohtml_linkwords */
		protected $lw;

		protected function _before()
		{
			require_once(e_PLUGIN."linkwords/e_parse.php");
			try
			{
				$this->lw = $this->make('linkwords_parse');
			}
			catch(Exception $e)
			{
				$this->fail("Couldn't load e_tohtml_linkwords object");
			}

			$words = array(
				0   => array('word'=> 'contact us', 'link'=>'/contact.php', 'ext'=>'', 'tip'=>'Contact Us Now', 'limit'=>'3'),
				1   => array('word'=> 'contact form', 'link'=>'/contact.php', 'ext'=>'', 'tip'=>'Click here', 'limit'=> '5'),
				2   => array('word'=> 'fill out this form', 'link'=>'', 'ext'=>'', 'tip'=>'My Tip', 'limit'=>'5'),
				3   => array('word'=> '', 'link'=>'', 'ext'=>'', 'tip'=>'', 'limit'=>'3'),
				4   => array('word'=> "John's place", 'link'=>'/my-link', 'ext'=>'', 'tip'=>'', 'limit'=>'3'), // test single quote as encoded in
				5   => array('word'=> "link", 'link'=>'/page-link', 'ext'=>'', 'tip'=>'', 'limit'=>'3'), // test single quote as encoded in
				6   => array('word'=> "body only", 'link'=>'/body-link', 'ext'=>'', 'tip'=>'', 'limit'=>'3'), // test single quote as encoded in

			);

			$opts = array ('BODY' => '1', 'DESCRIPTION' => '1');
			$this->lw->cache(false);
			$this->lw->enable();
			$this->lw->setWordData($words);
			$this->lw->setAreaOpts($opts);


		}

		public function testTo_html()
		{
			$tests = array(
				0   => array(
					'text'      => "Please contact us here",
					'expected'  => "Please <a class=\"lw-tip  lw-link  lw-1\"  href=\"/contact.php\"  title=\"Contact Us Now\" >contact us</a> here"
				),

				1   => array(
					'text'      => "<p>Please fill in the <a href='#'>contact form</a> right here.",
					'expected'  => "<p>Please fill in the <a href='#'>contact form</a> right here."
				),

				2   => array(
					'text'      => "<p>To know more fill out this form right away.</p>",
					'expected'  => '<p>To know more <span class="lw-tip  lw-1"  title="My Tip" >fill out this form</span> right away.</p>',
				),

				3   => array(
					'text'      => "<p>Visit John's place.</p>",
					'expected'  => "<p>Visit <a class=\"lw-link  lw-1\"  href=\"/my-link\" >John's place</a>.</p>",
				),

				// avoid placing links within existing links.
				4   => array(
					'text'      => "<a href=''>link</a>link",
					'expected'  => "<a href=''>link</a><a class=\"lw-link  lw-1\"  href=\"/page-link\" >link</a>",
				),

				// Titles should be ignored within a body context.
				5   => array(
					'text'      => "<h3>Body only title</h3><p>body only text</p>",
					'expected'  => "<h3>Body only title</h3><p><a class=\"lw-link  lw-1\"  href=\"/body-link\" >body only</a> text</p>",
				),

			);

			foreach($tests as $val)
			{
				$result = $this->lw->toHTML($val['text'], 'BODY');
				$this->assertEquals($val['expected'],$result);

			}

		}
/*
		public function testLinksproc()
		{

		}*/




	}
