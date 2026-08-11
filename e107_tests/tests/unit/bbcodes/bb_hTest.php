<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

class bb_hTest extends \Test\Unit
{
	/** @var bb_h */
	protected $bb;

	protected function _before()
	{
		require_once(e_CORE.'bbcodes/bb_h.php');

		try
		{
			$this->bb = $this->make('bb_h');
		}
		catch(Exception $e)
		{
			self::fail("Couldn't load bb_h object");
		}
	}

	/**
	 * The three attributes a heading can carry are all optional, and the
	 * plain [h=2]Title[/h] the toolbar inserts carries none of them. The
	 * reassembled bbcode has to come back without an attribute list, and
	 * without reading a variable that only the optional branches assign.
	 */
	public function testToDbReassemblesAHeadingCarryingNoAttributes()
	{
		self::assertSame('[h=2]Title[/h]', $this->bb->toDB('Title', '2'));
		self::assertSame('[h=3]Title[/h]', $this->bb->toDB(' Title ', '3'));
		self::assertSame('[h=2]Title[/h]', $this->bb->toDB('Title', ''));
	}

	/**
	 * And with attributes it still keeps them, sanitised.
	 */
	public function testToDbKeepsTheAttributesAHeadingDoesCarry()
	{
		self::assertSame(
			'[h=2|class=lead]Title[/h]',
			$this->bb->toDB('Title', '2|class=lead')
		);

		self::assertSame(
			'[h=4|id=intro]Title[/h]',
			$this->bb->toDB('Title', '4|id=intro')
		);
	}

	public function testToDbDropsAnEmptyHeading()
	{
		self::assertSame('', $this->bb->toDB('   ', '2'));
	}
}
