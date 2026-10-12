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

			$tests = array(
				array(
					'parm'      => 'width=300&height=200',
					'expected'  => '[img width=300&height=200]{e_MEDIA_IMAGE}2020-12/horse.jpg[/img]'
				),
				array(
					'parm'      => 'height=200',
					'expected'  => '[img height=200]{e_MEDIA_IMAGE}2020-12/horse.jpg[/img]'
				),
				array(
					'parm'      => 'width=300',
					'expected'  => '[img width=300]{e_MEDIA_IMAGE}2020-12/horse.jpg[/img]'
				),
				array(
					'parm'      => 'height=1" onload="alert(1)',
					'expected'  => '[img]{e_MEDIA_IMAGE}2020-12/horse.jpg[/img]'
				),
				array(
					'parm'      => 'width=abc',
					'expected'  => '[img]{e_MEDIA_IMAGE}2020-12/horse.jpg[/img]'
				),
				array(
					'parm'      => 'figcaption=Hello&loading=eager',
					'expected'  => '[img figcaption=Hello&loading=eager]{e_MEDIA_IMAGE}2020-12/horse.jpg[/img]'
				),
				array(
					'parm'      => 'loading=nonsense',
					'expected'  => '[img]{e_MEDIA_IMAGE}2020-12/horse.jpg[/img]'
				),
			);

			foreach($tests as $var)
			{
				$result = $this->bb->toDB('{e_MEDIA_IMAGE}2020-12/horse.jpg', $var['parm']);

				$this->assertSame($var['expected'], $result, $var['parm']);
			}
		}

		/**
		 * filter() is not idempotent, so the caption is stored as it was typed and encoded once, at the render.
		 */
		public function testAPostedCaptionReachesTheReaderAsItWasTyped()
		{

			$stored = $this->bb->toDB('{e_MEDIA_IMAGE}2020-12/horse.jpg', "alt=A horse&figcaption=Ben's horse");

			$this->assertSame('[img alt=A+horse&figcaption=Ben%27s+horse]{e_MEDIA_IMAGE}2020-12/horse.jpg[/img]', $stored);

			preg_match('#^\[img ([^\]]*)\](.*)\[/img\]$#', $stored, $m);

			$result = $this->bb->toHTML($m[2], $m[1]);

			$this->assertStringContainsString('<figcaption>Ben&#039;s horse</figcaption>', $result);
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

		/**
		 * @param string $path
		 * @return string what rendering the [img] printed, its echoed output included
		 */
		private function renderWildcard($path)
		{
			ob_start();

			try
			{
				$result = $this->bb->toHTML($path, '');
			}
			finally
			{
				$echoed = ob_get_clean();
			}

			return $echoed.$result;
		}

		/**
		 * @return array
		 */
		public function wildcardImagesThatListNothing()
		{
			return array(
				'outside the site'         => array('/etc/pass*'),
				'a system folder'          => array('{e_SYSTEM}logs/*'),
				'out of an image folder'   => array('{e_IMAGE}../e107_conf*'),
				'an ftp address'           => array('ftp://127.0.0.1:1/*'),
				'a phar archive'           => array('phar://e107_system/core_image.phar/*'),
				'a missing image folder'   => array('{e_IMAGE}nowhere/*'),
				'a bracket in the pattern' => array('{e_IMAGE}generic/(*'),
				'a regex in the pattern'   => array('{e_IMAGE}generic/blank.?gif*'),
				'a folder name'            => array('{e_IMAGE}generic/bbcod*'),
				'an index page'            => array('{e_IMAGE}avatars/index*'),
			);
		}

		/**
		 * An [img] whose file name holds * shows a random matching file from the site's image folder, and prints nothing else.
		 *
		 * @dataProvider wildcardImagesThatListNothing
		 * @param string $path
		 */
		public function testAWildcardImageListsOnlyTheSiteImageFolder($path)
		{
			$this->assertSame('', $this->renderWildcard($path));
		}

		/**
		 * A path that starts with two separators names a network host on Windows, so it is refused before it is resolved.
		 */
		public function testAWildcardImageOnANetworkPathListsNothing()
		{
			$this->assertSame('', $this->renderWildcard('/'.realpath(e_IMAGE).'/generic/blank*'));
		}

		public function testAWildcardImageStillShowsAFileFromTheImageFolder()
		{
			$this->assertStringContainsString("src='".e_IMAGE_ABS."generic/blank.gif'", $this->renderWildcard('{e_IMAGE}generic/blank.gi*'));
		}

		public function testAWildcardImageEncodesTheNameItPicks()
		{
			$folder = e_IMAGE.'bb_img_test_'.md5(uniqid('', true));

			try
			{
				mkdir($folder);
				touch($folder."/it's.png");
				$html = $this->renderWildcard('{e_IMAGE}'.basename($folder).'/it*');
			}
			finally
			{
				if(is_file($folder."/it's.png"))
				{
					unlink($folder."/it's.png");
				}
				if(is_dir($folder))
				{
					rmdir($folder);
				}
			}

			$document = new DOMDocument();
			$document->loadHTML('<div>'.$html.'</div>');

			$this->assertSame(e_IMAGE_ABS.basename($folder)."/it's.png", $document->getElementsByTagName('img')->item(0)->getAttribute('src'));
		}

	}
