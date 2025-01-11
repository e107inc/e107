<?php


/**
 * @group plugins
 */
class siteinfo_shortcodesTest extends \Codeception\Test\Unit
{

	/** @var siteinfo_shortcodes */
	protected $sc;

	protected function _before()
	{

		try
		{
			require_once(e_PLUGIN."siteinfo/e_shortcode.php");
			$this->sc = $this->make('siteinfo_shortcodes');
		}
		catch(Exception $e)
		{
			self::fail($e->getMessage());
		}

	}

	public function testSc_sitename()
	{
		$result = $this->sc->sc_sitename();
		self::assertSame('e107', $result);

	}

/*	public function testSc_siteurl()
	{

	}

	public function testSc_sitedisclaimer()
	{

	}

	public function testSc_sitedescription()
	{

	}

	public function testSc_sitetag()
	{

	}

	public function testSc_sitebutton()
	{

	}

	public function testSc_sitelogo()
	{

	}*/

	public function testSc_logo()
	{
		$tp = e107::getParser();

		$result = $this->sc->sc_logo(['w'=>200, 'h'=>100]);
		$expected = '<img class="logo img-responsive img-fluid" src="/thumb.php?src=e_IMAGE%2FlogoHD.png&amp;w=200&amp;h=100" alt="e107" srcset="/thumb.php?src=e_IMAGE%2FlogoHD.png&amp;w=400&amp;h=200 2x" width="200" height="100"  />';
		self::assertSame($expected, $result);

		$tp->setStaticUrl('https://my.cdn.com/');
		$result = $this->sc->sc_logo(['w'=>240, 'h'=>120]);
		$expected = '<img class="logo img-responsive img-fluid" src="https://my.cdn.com/thumb.php?src=e_IMAGE%2FlogoHD.png&amp;w=240&amp;h=120" alt="e107" srcset="https://my.cdn.com/thumb.php?src=e_IMAGE%2FlogoHD.png&amp;w=480&amp;h=240 2x" width="240" height="120"  />';
		self::assertSame($expected, $result);

		file_put_contents(e_MEDIA_IMAGE.'logo.png','dummy image content');
		e107::getConfig()->set('sitelogo', '{e_MEDIA_IMAGE}logo.png');
		$tp->setStaticUrl('https://my.cdn.com/');
		$tp->thumbWidth(100);
		$tp->thumbHeight(0);
		$tp->thumbCrop(0);
		$result = $this->sc->sc_logo();
		$expected = '<img class="logo img-responsive img-fluid" src="https://my.cdn.com/thumb.php?src=e_MEDIA_IMAGE%2Flogo.png&amp;w=100&amp;h=0" alt="e107" srcset="https://my.cdn.com/thumb.php?src=e_MEDIA_IMAGE%2Flogo.png&amp;w=200&amp;h=0 2x" width="100"  />';
		self::assertSame($expected, $result);


		$tp->setmodRewriteMedia(true);
		$result = $this->sc->sc_logo(['w'=>240, 'h'=>120]);
		$expected = '<img class="logo img-responsive img-fluid" src="https://my.cdn.com/media/img/240x120/logo.png" alt="e107" srcset="https://my.cdn.com/media/img/480x240/logo.png 2x" width="240" height="120"  />';

		self::assertSame($expected, $result);

		// Reset for other tests.
		$tp->setmodRewriteMedia(false);
		$tp->setStaticUrl(null);

	}

/*	public function testSc_theme_disclaimer()
	{
		$result = $this->sc->sc_theme_disclaimer();

	}*/



}
