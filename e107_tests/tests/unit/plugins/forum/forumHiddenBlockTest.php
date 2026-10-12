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
 * A [hide] block a quote carries sealed opens only for a reader who may see
 * hidden blocks in the thread it was quoted from, wherever the quote is read.
 */
class forumHiddenBlockTest extends \Test\Unit
{
	/** @var array the hide bbcode's opener and hidden-text globals as they were found */
	private $saved = array();

	protected function _before()
	{
		global $e_hide_unseal, $e_hide_hidden;

		require_once(e_PLUGIN.'forum/forum_class.php');

		$this->saved = array($e_hide_unseal, $e_hide_hidden);
		$e_hide_hidden = 'HIDDEN BLOCK';
	}

	protected function _after()
	{
		global $e_hide_unseal, $e_hide_hidden;

		list($e_hide_unseal, $e_hide_hidden) = $this->saved;
	}

	public function testASealedBlockOpensOnlyForAReaderWhoMaySeeItsThread()
	{
		$sealed = $this->forum(false)->sealHidden(7, 11, 'The answer is 42');

		self::assertIsString($sealed);
		self::assertStringNotContainsString('answer', $sealed);
		self::assertSame('The answer is 42', $this->forum(true)->unsealHidden($sealed));
		self::assertNull($this->forum(false)->unsealHidden($sealed));
	}

	public function testOpeningASealedBlockAsksAboutTheThreadItWasSealedIn()
	{
		$asked = array();
		$forum = $this->make('e107forum', array('mayReadHidden' => function ($threadId, $postId = 0) use (&$asked)
		{
			$asked[] = array($threadId, $postId);

			return true;
		}));

		$forum->unsealHidden($this->forum(false)->sealHidden(7, 11, 'The answer is 42'));

		self::assertSame(array(array(7, 11)), $asked);
	}

	public function testABlockTooLongToOpenIsNotSealed()
	{
		self::assertSame(str_repeat('a', 4000), $this->forum(true)->unsealHidden($this->forum(false)->sealHidden(7, 11, str_repeat('a', 4000))));
		self::assertFalse($this->forum(false)->sealHidden(7, 11, str_repeat('a', 7000)));
	}

	public function testAnOpenedBlockIsRenderedOnceWhateverItHolds()
	{
		global $e_hide_unseal;

		$forum = $this->forum(true);
		$opened = 0;
		$e_hide_unseal = function ($token) use ($forum, &$opened)
		{
			if(++$opened > 3)
			{
				throw new RuntimeException('opened again while it was being rendered');
			}

			return $forum->unsealHidden($token);
		};

		$html = e107::getParser()->toHTML('[hide]' . $this->forum(false)->sealHidden(7, 11, '[table]p[code]q[/table] [hide]x[/hide][/code]') . '[/hide]', true);

		self::assertSame(1, $opened);
		self::assertStringContainsString('q', $html);
	}

	public function testABlockSealedWhileTheSiteAnsweredAtAnotherAddressStillOpens()
	{
		require_once(__DIR__ . '/../../e107/Security/sealed_token_doubles.php');

		$token = (new \e107\Security\SealedTokenElsewhere('forum hidden block'))->seal(array('thread' => 7, 'post' => 11, 'text' => 'The answer is 42'), 600);

		self::assertSame('The answer is 42', $this->forum(true)->unsealHidden($token));
	}

	public function testAnAlteredSealedBlockOpensForNobody()
	{
		$sealed = $this->forum(false)->sealHidden(7, 11, 'The answer is 42');
		$last = strlen($sealed) - 2;
		$altered = substr($sealed, 0, $last) . (($sealed[$last] === 'A') ? 'B' : 'A') . substr($sealed, $last + 1);

		self::assertNull($this->forum(true)->unsealHidden($altered));
	}

	public function testATokenSealedForAnotherPurposeOpensForNobody()
	{
		$token = e107::getSealedToken()->seal(array('thread' => 7, 'post' => 11, 'text' => 'The answer is 42'), 600);

		self::assertNull($this->forum(true)->unsealHidden($token));
	}

	public function testTheTopicPageShowsASealedBlockOnlyToAReaderWhoMaySeeItsThread()
	{
		global $e_hide_unseal;

		$post = 'Before [hide]' . $this->forum(false)->sealHidden(7, 11, 'The [b]answer[/b] is 42') . '[/hide] after';

		$e_hide_unseal = array($this->forum(true), 'unsealHidden');
		$shown = e107::getParser()->toHTML($post, true);

		$e_hide_unseal = array($this->forum(false), 'unsealHidden');
		$hidden = e107::getParser()->toHTML($post, true);

		$e_hide_unseal = null;
		$unopened = e107::getParser()->toHTML($post, true);

		self::assertStringContainsString('The <strong', $shown);
		self::assertStringContainsString('answer</strong> is 42', $shown);

		foreach(array($hidden, $unopened) as $html)
		{
			self::assertStringContainsString('HIDDEN BLOCK', $html);
			self::assertStringNotContainsString('answer', $html);
		}
	}

	/**
	 * @param bool $mayReadHidden
	 * @return e107forum
	 */
	private function forum($mayReadHidden)
	{
		return $this->make('e107forum', array('mayReadHidden' => $mayReadHidden));
	}
}
