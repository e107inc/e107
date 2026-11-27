<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * @group plugins
 *
 * Issue #6093: an item template prints the custom fields its category defines.
 */
class featureboxItemFieldTest extends \Test\Unit
{
	const PHOTO = '{e_PLUGIN}featurebox/images/featurebox_32.png';

	public function testTheFieldPrintsAnImageFieldAsAnImage()
	{
		$printed = $this->printed('{FEATUREBOX_FIELD: name=photo}');

		self::assertStringContainsString('<img', $printed);
		self::assertStringContainsString('featurebox_32.png', $printed);
	}

	public function testTheRawModePrintsTheImageAddressAlone()
	{
		$printed = $this->printed('{FEATUREBOX_FIELD: name=photo&mode=raw}');

		self::assertStringContainsString('featurebox_32.png', $printed);
		self::assertStringNotContainsString('<', $printed, 'an address for an attribute, not a tag');
	}

	public function testTheRawModeEscapesTheValueForAnAttribute()
	{
		self::assertSame('Say &quot;hi&quot; &amp; go', $this->printed('{FEATUREBOX_FIELD: name=badge&mode=raw}'));
	}

	public function testAFieldTheCategoryNoLongerDefinesPrintsNothing()
	{
		self::assertSame('Say &quot;hi&quot; &amp; go|', $this->printed('{FEATUREBOX_FIELD: name=badge&mode=raw}|{FEATUREBOX_FIELD: name=retired}'));
	}

	public function testAnItemInACategoryWithoutFieldsPrintsNothing()
	{
		$template = '{FEATUREBOX_FIELD: name=badge&mode=raw}';

		self::assertSame(array('Say &quot;hi&quot; &amp; go', ''), array($this->printed($template), $this->printed($template, null)));
	}

	public function testTheFieldNeedsAName()
	{
		self::assertSame('Say &quot;hi&quot; &amp; go|', $this->printed('{FEATUREBOX_FIELD: name=badge&mode=raw}|{FEATUREBOX_FIELD}'));
	}

	public function testAFieldHoldingMoreThanOneValuePrintsNothing()
	{
		self::assertSame('Say &quot;hi&quot; &amp; go|', $this->printed('{FEATUREBOX_FIELD: name=badge&mode=raw}|{FEATUREBOX_FIELD: name=sizes&mode=raw}'));
	}

	/**
	 * @return array a url field's value as stored, and the trust level recorded with it, null for none
	 */
	public function addressesThatRunScriptOnceStoredOrPrinted()
	{
		return array(
			'saved outside the post_script class'                => array('javascript:void(0)', 1),
			'saved by a member of the post_script class'         => array('javascript:void(0)', 2),
			'with no level, which prints after another toDB()'   => array('java[p][/p]script:void(0)', null),
			'with no level, through a tag pair toDB() takes out' => array('java<x></x>script:void(0)', null),
			'a control character a browser decodes'              => array('&#1;javascript:void(0)', 1),
			'a carriage return a browser decodes'                => array('java&#13;script:void(0)', 1),
			'a reference without its semicolon'                  => array('&#106avascript:void(0)', 0),
		);
	}

	/**
	 * Each is printed beside an address that runs no script, which has to print.
	 *
	 * @dataProvider addressesThatRunScriptOnceStoredOrPrinted
	 * @param string $address
	 * @param int|null $trust
	 */
	public function testAUrlFieldPrintsNoAddressThatRunsScript($address, $trust)
	{
		$link = array(
			'link' => array('title' => 'Link', 'type' => 'url', 'writeParms' => '', 'help' => ''),
			'home' => array('title' => 'Home', 'type' => 'url', 'writeParms' => '', 'help' => ''),
		);
		$values = array('link' => $address, 'home' => 'https://e107.org/');

		if($trust !== null)
		{
			$values['__trust__'] = array('link' => $trust, 'home' => $trust);
		}

		foreach(array('', '&mode=raw') as $mode)
		{
			$printed = $this->printed('[{FEATUREBOX_FIELD: name=link'.$mode.'}]{FEATUREBOX_FIELD: name=home'.$mode.'}', $link, $values);

			self::assertSame(1, preg_match('/^\[([^\]]*)\]/', $printed, $bracketed), 'printed as'.($mode ? ' raw' : ' formatted'));
			self::assertStringNotContainsString('&#', $bracketed[1], 'printed as'.($mode ? ' raw' : ' formatted'));
			self::assertStringNotContainsString('javascript:', $bracketed[1], 'printed as'.($mode ? ' raw' : ' formatted'));
			self::assertStringContainsString('https://e107.org/', $printed, 'printed as'.($mode ? ' raw' : ' formatted'));
		}
	}

	/**
	 * @param string $template
	 * @param array|null $definitions the category's fields, null for none
	 * @param array|null $values the item's fb_fields, null for the default set
	 * @return string
	 */
	private function printed($template, $definitions = array(), $values = null)
	{
		if($definitions === array())
		{
			$definitions = array(
				'badge' => array('title' => 'Badge', 'type' => 'text', 'writeParms' => '', 'help' => ''),
				'photo' => array('title' => 'Photo', 'type' => 'image', 'writeParms' => '', 'help' => ''),
				'sizes' => array('title' => 'Sizes', 'type' => 'text', 'writeParms' => '', 'help' => ''),
			);
		}

		$category = new plugin_featurebox_category();
		$category->setData(array(
			'fb_category_id'     => 7,
			'fb_category_fields' => ($definitions === null) ? null : json_encode($definitions),
		));

		$item = new plugin_featurebox_item();
		$item->setData(array(
			'fb_id'       => 42,
			'fb_category' => 7,
			'fb_fields'   => json_encode(($values === null) ? array('badge' => 'Say "hi" & go', 'photo' => self::PHOTO, 'retired' => 'old', 'sizes' => array('S', 'M')) : $values),
		));
		$item->setCategory($category);

		return e107::getParser()->parseTemplate($template, true, $item);
	}
}
