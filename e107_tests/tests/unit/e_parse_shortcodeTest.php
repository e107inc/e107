<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2018 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

class e_parse_shortcodeTest extends \Codeception\Test\Unit
{
	/** @var e_parse_shortcode */
	private $scParser;

	public function _before()
	{
		try
		{
			$this->scParser = $this->make('e_parse_shortcode');
		}
		catch (Exception $e) {
			$this->fail("Couldn't create e_parse_shortcode object");
		}

		$this->scParser->__construct();
	}

//	public function testShortcode_SITELINKS_ALT()
//	{
//		$output = $this->scParser->parseCodes('{SITELINKS_ALT=/e107_themes/jayya/images/arrow.png+noclick}');
//		var_export($output);
//	}

	/*
	public function testIsBatchOverride()
	{

	}

	public function testIsRegistered()
	{

	}

	public function testIsOverride()
	{

	}

	public function testResetScClass()
	{

	}

	public function testDoCode()
	{

	}

	public function testGetScObject()
	{

	}
*/
	public function testParseCodesWithArray()
	{
		$text = '<ul class="dropdown-menu {LINK_SUB_OVERSIZED}" role="menu" >';

		$array = array(
			'LINK_TEXT' => 'Content',
		    'LINK_URL' => '#',
		    'ONCLICK' => '',
		    'SUB_HEAD' => '',
		    'SUB_MENU' => '',

		    'ID' => '',
		    'SUB_ID' => '',
		    'LINK_CLASS' =>  'e-expandit',
		    'SUB_CLASS' =>  'e-hideme e-expandme',
		    'LINK_IMAGE' =>  '',
		    'LINK_SUB_OVERSIZED' => 'oversized',
		    'LINK_BADGE' => '',
		);

		  // -- Legacy Wrapper --
        global $sc_style;
        $sc_style = array();
        $sc_style['LINK_SUB_OVERSIZED']['pre'] = "** ";
        $sc_style['LINK_SUB_OVERSIZED']['post'] = " **";

		$actual = $this->scParser->parseCodes($text, false, $array);
        $expected = '<ul class="dropdown-menu ** oversized **" role="menu" >';
		$this->assertEquals($expected, $actual);

		// v2.x Array Wrapper - should override any $sc_style legacy wrapper
		$array['_WRAPPER_'] = "non-existent/template";
		$actual = $this->scParser->parseCodes($text, false, $array);
        $expected = '<ul class="dropdown-menu oversized" role="menu" >';
		$this->assertEquals($expected, $actual);

	}


	public function testParseCodesWithClass()
	{
	    $sc = e107::getScBatch('_blank', true, '_blank');
	    $this->assertIsObject($sc);

        // - v1.x Wrapper Test.
        global $sc_style;
        $sc_style = array();
        $sc_style['BLANK_TEST']['pre'] = "** ";
        $sc_style['BLANK_TEST']['post'] = " **";

        $actualTemplate = e107::getTemplate('_blank', '_blank', 'default');
        $expectedTemplate = "<div>{BLANK_TEST}</div>";
        $this->assertEquals($expectedTemplate, $actualTemplate);
        $actualLegacy = $this->scParser->parseCodes($actualTemplate, false, $sc);
        $expectedLegacy = "<div>** test **</div>";
        $this->assertEquals($expectedLegacy, $actualLegacy);

        // - v2.x Wrapper Test.
        $sc->wrapper('_blank/default'); // overrides legacy $sc_style;
        $actual = $this->scParser->parseCodes($actualTemplate, false, $sc);
        $expected = "<div>[ test ]</div>";
        $this->assertEquals($expected, $actual);


    }
/*
	public function testInitShortcodeClass()
	{

	}

	public function testRegisterShortcode()
	{

	}

	public function testSetScVar()
	{

	}

	public function testCallScFunc()
	{

	}

	public function testIsScClass()
	{

	}

	public function testParse_scbatch()
	{

	}

	public function testLoadThemeShortcodes()
	{

	}
	*/
}
