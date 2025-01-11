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

		/**
		 * Test parsing of input from user via TinyMce.
		 */
		public function testToDB()
		{
			$this->tm->setHtmlClass(e_UC_ADMIN);


			$test_1 = '<ul>
<li>one<a class="bbcode bbcode-link" href="http://www.three.co.uk/"></a></li>
<li>two</li>
<li>three</li>
<li>four</li>
</ul>
<sup>2</sup>
';

			$actual_1 = $this->tm->toDB($test_1);
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




			$actual_2 = $this->tm->toDB($test_2);

			$expected_2 = '[html]<p>[img class=bbcode-img-right&width=300]{e_MEDIA_IMAGE}2017-11/e107_about.png[/img]Some text</p>
<p>[img class=bbcode-img-left&width=600]{e_MEDIA_IMAGE}2017-11/e107_about.png[/img]Some other text</p>[/html]';

			$this->assertEquals($expected_2, $actual_2);


			$test_3 = "<p>Nikdy nehovor, že niečo nejde, pretože sa vždy nájde blbec, čo to urobí.</p>";
			$actual_3 = $this->tm->toDB($test_3);
			$expected_3 = "[html]<p>Nikdy nehovor, že niečo nejde, pretože sa vždy nájde blbec, čo to urobí.</p>[/html]";
			$this->assertEquals($expected_3, $actual_3);


			$result3 = $this->tm->toHTML($actual_3);
			$this->assertEquals($test_3, $result3);

		}

		/**
		 * Simulate TinyMce usage by a user without access to post HTML.
		 */
		function testToDBUser()
		{
			$text = "An example,<br />
	<br />
	Thank you for your purchase.<br />
	Your order reference number is: #{ORDER_DATA: order_ref}<br />
	<br />
	<table class='table'>
	<colgroup>	
		<col style='width:50%' />
		<col style='width:50%' />
	</colgroup>
	<tr>
		<th>Merchant</th>
		<th>Customer</th>
	</tr>
	<tr>
		<td>{ORDER_MERCHANT_INFO}</td>
		<td>
			<h4>Billing address</h4>
			{ORDER_DATA: cust_firstname} {ORDER_DATA: cust_lastname}<br />
		</td>
	</tr>
	</table>
	<hr />";

		global $_E107;
		$_E107['phpunit']  = true;  // enable the user of check_class();

		$this->tm->setHtmlClass(e_UC_NOBODY);
		$result = $this->tm->toDB($text);

		$_E107['phpunit']  = false;

		}




		function testtoDBOnScriptTags()
		{
			$this->tm->setHtmlClass(e_UC_ADMIN);
			// test parsing of scripts.

			$string = '<p><script type="text/javascript" src="https://cdn.myscript.net/js/1.js" async></script></p>';
			$result = $this->tm->toDB($string);
			$this->assertSame('[html]'.$string.'[/html]', $result);


			$result = $this->tm->toHTML($string);
			$this->assertSame($string, $result);







		}


		public function testParsingofTable()
		{
				// -----------

			$string = "Hello {ORDER_DATA: cust_firstname} {ORDER_DATA: cust_lastname},<br />
	<br />
	Thank you for your purchase.<br />
	Your order reference number is: #{ORDER_DATA: order_ref}<br />
	<br />
	<table class='table'>
	<colgroup>	
		<col style='width:50%' />
		<col style='width:50%' />
	</colgroup>
	<tr>
		<th>Merchant</th>
		<th>Customer</th>
	</tr>
	<tr>
		<td>{ORDER_MERCHANT_INFO}</td>
		<td>
			<h4>Billing address</h4>
			{ORDER_DATA: cust_firstname} {ORDER_DATA: cust_lastname}<br />
			{ORDER_DATA: cust_company}<br />
			{ORDER_DATA: cust_address}<br />
			{ORDER_DATA: cust_city} {ORDER_DATA: cust_state} {ORDER_DATA: cust_zip}<br />
			{ORDER_DATA: cust_country}
			<br />
			<h4>Shipping address</h4>
			{ORDER_DATA: ship_firstname} {ORDER_DATA: ship_lastname}<br />
			{ORDER_DATA: ship_company}<br />
			{ORDER_DATA: ship_address}<br />
			{ORDER_DATA: ship_city} {ORDER_DATA: ship_state} {ORDER_DATA: ship_zip}<br />
			{ORDER_DATA: ship_country}
		</td>
	</tr>
	</table>";

	$this->tm->setHtmlClass(254);
	$result = $this->tm->toDB($string);
	$this->assertSame('[html]'.$string.'[/html]', $result);

	$result = $this->tm->toHTML($string);
	$this->assertSame($string, $result);






		}







	}
