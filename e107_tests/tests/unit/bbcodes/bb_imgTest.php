<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2020 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class bb_imgTest extends \Codeception\Test\Unit
	{

		/** @var bb_img */
		protected $bb;

		protected function _before()
		{

			require_once(e_CORE."bbcodes/bb_img.php");

			try
			{
				$this->bb = $this->make('bb_img');
			}
			catch(Exception $e)
			{
				$this->assertTrue(false, "Couldn't load bb_img object");
			}

		}

		public function testToDB()
		{

		}

		public function testToHTML()
		{
			$tests = array(
				array(
					'codetext'  => '{e_MEDIA_IMAGE}2020-12/5.sm.webp',
					'parm'      => '',
					'expected'  => '<img class="img-rounded rounded bbcode bbcode-img" src="thumb.php?src=e_MEDIA_IMAGE%2F2020-12%2F5.sm.webp&amp;w=0&amp;h=0" alt="5.sm.webp" loading="lazy"  />'
				),
				array(
					'codetext'  => '{e_MEDIA}images/2020-12/horse.jpg',
					'parm'      => 'width=300',
					'expected'  => '<img class="img-rounded rounded bbcode bbcode-img" src="thumb.php?src=e_MEDIA_IMAGE%2F2020-12%2Fhorse.jpg&amp;w=300&amp;h=0" alt="Horse" srcset="thumb.php?src=e_MEDIA_IMAGE%2F2020-12%2Fhorse.jpg&amp;w=600&amp;h=0 2x" width="300" loading="lazy" title="Horse"  />'
				),
				array(
					'codetext'  => '{e_MEDIA_IMAGE}2020-12/horse.jpg',
					'parm'      => 'width=300',
					'expected'  => '<img class="img-rounded rounded bbcode bbcode-img" src="thumb.php?src=e_MEDIA_IMAGE%2F2020-12%2Fhorse.jpg&amp;w=300&amp;h=0" alt="Horse" srcset="thumb.php?src=e_MEDIA_IMAGE%2F2020-12%2Fhorse.jpg&amp;w=600&amp;h=0 2x" width="300" loading="lazy" title="Horse"  />'
				),
				array(
					'codetext'  => '{e_THEME}voux/install/gasmask.jpg',
					'parm'      => 'width=300&alt=Custom&loading=auto',
					'expected'  => "<figure>
<img class=\"img-rounded rounded bbcode bbcode-img\" src=\"thumb.php?src=e_THEME%2Fvoux%2Finstall%2Fgasmask.jpg&amp;w=300&amp;h=0\" alt=\"Custom\" srcset=\"thumb.php?src=e_THEME%2Fvoux%2Finstall%2Fgasmask.jpg&amp;w=600&amp;h=0 2x\" width=\"300\" loading=\"auto\" title=\"Custom\"  /><figcaption>Custom</figcaption>
</figure>"
				),
			);

			foreach($tests as $var)
			{
				$result = $this->bb->toHTML($var['codetext'], $var['parm']);
				$result = preg_replace('/"([^"]*)thumb.php/','"thumb.php', $result); // remove the path before thumb.php

				$this->assertSame($var['expected'], $result);

			}
		}




	}
