<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2018 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e107TinyMceParserTest extends \Codeception\Test\Unit
	{

		/** @var e107TinyMceParser $tm */
		private $tm;

		protected function _before()
		{
			@define('TINYMCE_UNIT_TEST', true);
			require_once(e_PLUGIN."tinymce4/plugins/e107/parser.php");
			try
			{
				$this->tm = $this->make('e107TinyMceParser');
			}
			catch (Exception $e)
			{
				$this->fail("Couldn't load e107TinyMceParser object");
			}
		}




		public function testToHtmlOnPlain()
		{
			$test = 'Plain text paragraph 1

Plain text &quot;paragraph&quot; 2

Plain text paragraph 3
';

			$actual = $this->tm->toHTML($test);
			$expected = 'Plain text paragraph 1<br />
<br />
Plain text "paragraph" 2<br />
<br />
Plain text paragraph 3<br />';

			$this->assertEquals($expected, $actual, "Plain text line-breaks to HTML failed in the TinyMce editor." );
		}



		public function testToHtmlOnBbcode()
		{
			$test = '[b]Bold text[/b]
			
			paragraph 2
			
			paragraph 3';

			$actual = $this->tm->toHTML($test);

			$expected = "<strong class='bbcode bold bbcode-b'>Bold text</strong><br />
			<br />
			paragraph 2<br />
			<br />
			paragraph 3";

			$this->assertEquals($expected, $actual, "Bbcode to HTML failed in the TinyMce editor." );

		}

		public function testToBBcode()
		{

			$test_1 = '<ul>
<li>one<a class="bbcode bbcode-link" href="http://www.three.co.uk/"></a></li>
<li>two</li>
<li>three</li>
<li>four</li>
</ul>
<sup>2</sup>
';

			$actual_1 = $this->tm->toBBcode($test_1);
			$expected_1 = '[html]<ul>
<li>one<a class="bbcode bbcode-link" href="http://www.three.co.uk/"></a></li>
<li>two</li>
<li>three</li>
<li>four</li>
</ul>
<sup>2</sup>[/html]';

		//	echo $actual;

			$this->assertEquals($expected_1, $actual_1);



			$test_2 =
			'<p><img class="img-rounded rounded bbcode bbcode-img bbcode-img-right" src="'.e_HTTP.'media/img/300x0/2017-11/e107_about.png" alt="E107 About" srcset="'.e_HTTP.'media/img/600x0/2017-11/e107_about.png 600w" width="300">Some text</p>
<p><img class="img-rounded rounded bbcode bbcode-img bbcode-img-left" src="'.e_HTTP.'media/img/600x0/2017-11/e107_about.png" alt="E107 About" srcset="'.e_HTTP.'media/img/1200x0/2017-11/e107_about.png 1200w" width="600">Some other text</p>';




			$actual_2 = $this->tm->toBBcode($test_2);

			$expected_2 = '[html]<p>[img class=bbcode-img-right&width=300]{e_MEDIA_IMAGE}2017-11/e107_about.png[/img]Some text</p>
<p>[img class=bbcode-img-left&width=600]{e_MEDIA_IMAGE}2017-11/e107_about.png[/img]Some other text</p>[/html]';

			$this->assertEquals($expected_2, $actual_2);






		}
	}
