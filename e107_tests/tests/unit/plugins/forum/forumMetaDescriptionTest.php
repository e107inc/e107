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
 */
class forumMetaDescriptionTest extends \Test\Unit
{
	use \Test\ForumRows;

	/** The attributes a meta description is printed with; anything else came out of the text. */
	const OWN_ATTRIBUTES = array('property', 'name', 'content');

	protected function _before()
	{
		$this->haveForumTables();
	}

	protected function _after()
	{
		$this->dropForumRows();
	}

	public function testAMembersPostAddsNoAttributeToTheTopicDescription()
	{
		$threadId = $this->haveTopicOpenedWith('0;url=//example.com/ &quot; http-equiv=&quot;refresh&quot; x=&quot; [/&#039;b]' . str_repeat(' padding', 40));

		$this->assertOnlyOwnAttributes($this->metaDescriptionOf('forum_viewtopic.php', $threadId));
	}

	public function testAPostHtmlPostAddsNoAttributeToTheTopicDescription()
	{
		$threadId = $this->haveTopicOpenedWith($this->postHtmlBreakout());

		$this->assertOnlyOwnAttributes($this->metaDescriptionOf('forum_viewtopic.php', $threadId));
	}

	public function testAForumDescriptionAddsNoAttributeToItsOwnMetaDescription()
	{
		$forumId = $this->haveForum('e107help probe forum', $this->haveForum('e107help probe category'));
		e107::getDb()->createQueryBuilder()->update('forum')
			->set('forum_description', $this->postHtmlBreakout())
			->where('forum_id', $forumId)
			->execute();

		$this->assertOnlyOwnAttributes($this->metaDescriptionOf('forum_viewforum.php', $forumId));
	}

	/**
	 * @return string stored as a post_html poster's text is saved
	 */
	private function postHtmlBreakout()
	{
		return '0;url=//example.com/&amp;[b][/b]quot; http-equiv=refresh x=' . str_repeat(' padding', 40) . ' [/right]';
	}

	/**
	 * @param string $entry the first post, as stored
	 * @return int the thread id
	 */
	private function haveTopicOpenedWith($entry)
	{
		$forumId = $this->haveForum('e107help probe forum', $this->haveForum('e107help probe category'));
		$threadId = $this->haveForumThread('e107help probe thread', $forumId);
		$this->haveForumPost($entry, $threadId, $forumId);

		return $threadId;
	}

	/**
	 * @param string[] $attributes the meta description of a page whose text ends in padding
	 */
	private function assertOnlyOwnAttributes(array $attributes)
	{
		self::assertStringContainsString('padding', varset($attributes['content'], ''),
			'the meta description does not carry the text it was made from: '.var_export($attributes, true));
		self::assertSame(array(), array_values(array_diff(array_keys($attributes), self::OWN_ATTRIBUTES)),
			'the description text added attributes of its own to the meta tag: '.var_export($attributes, true));
	}

	/**
	 * @param string $page the page file under e107_plugins/forum/
	 * @param int $id the thread or forum it is opened on
	 * @return string[] the attributes of the meta description the page printed, by name
	 */
	private function metaDescriptionOf($page, $id)
	{
		$php = "\$_REQUEST = \$_GET; ";
		$php .= "require_once('".addslashes(APP_PATH.'/e107_plugins/forum/'.$page)."'); ";

		list($output, ) = $this->bootPluginInCli('forum', '2.0', array('id' => $id), $php);
		$printed = implode("\n", $output);

		$previous = libxml_use_internal_errors(true);
		$dom = new DOMDocument();
		$dom->loadHTML($printed);
		libxml_clear_errors();
		libxml_use_internal_errors($previous);

		$metas = (new DOMXPath($dom))->query("//meta[@name='description']");

		self::assertSame(1, $metas->length, "the page did not print exactly one meta description:\n".$printed);

		$attributes = array();

		foreach($metas->item(0)->attributes as $attribute)
		{
			$attributes[$attribute->name] = $attribute->value;
		}

		return $attributes;
	}
}
