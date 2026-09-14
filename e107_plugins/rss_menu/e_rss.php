<?php
/*
* e107 website system
*
* Copyright (C) 2008-2016 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* RSS comments feed addon
*
*/

if (!defined('e107_INIT')) { exit; }

/**
 * The feed of comments left across the site.
 *
 * Commenting is core rather than a plugin, so the feed is declared by the
 * plugin that serves every other feed. {@see rss_addons::feeds()} scans this
 * folder alongside the plugins named in the e_rss_list pref.
 */
class rss_menu_rss
{
	/**
	 * Numeric feed key this feed answered to before v0.7.6.
	 *
	 * {@see rss_addons::legacyKeys()}
	 *
	 * @return array old numeric key => canonical text key
	 */
	public function legacy()
	{
		return array(
			5 => 'comments',
		);
	}

	/**
	 * ADMIN AREA ONLY: the labels are admin language constants.
	 *
	 * @return array
	 */
	public function config()
	{
		return array(
			array(
				'name'        => LAN_COMMENTS,
				'url'         => 'comments',
				'topic_id'    => '',
				'description' => RSS_PLUGIN_LAN_9,
				'class'       => '0',
				'limit'       => '9',
			),
		);
	}

	/**
	 * @param array $parms feed key, topic id and item limit
	 * @return array rss items
	 */
	public function data($parms = null)
	{
		$limit = (int) vartrue($parms['limit'], 9);
		$base  = SITEURL.'comment.php?comment.';
		$items = array();

		foreach($this->commentParents() as $name => $parent)
		{
			if(!empty($parent['plugin']) && !e107::isInstalled($parent['plugin']))
			{
				continue;
			}

			foreach($this->visibleComments($name, $parent, $limit) as $row)
			{
				$items[] = array(
					'title'       => $row['comment_subject'],
					'datestamp'   => $row['comment_datestamp'],
					'link'        => $base.$name.'.'.$row['comment_item_id'],
					'description' => $row['comment_comment'],
					'author'      => $row['comment_author_name'],
				);
			}
		}

		usort($items, array($this, 'byNewestFirst'));

		return array_slice($items, 0, $limit);
	}

	/**
	 * The things a comment in this feed can be attached to.
	 *
	 * A type this list does not describe is one whose visibility the feed has no
	 * way to establish, so comments of that type are not served. Comments of type
	 * 'profile' are among them: they belong to a member's profile page, which is
	 * not public, so the feed has no version of them it could publish.
	 *
	 * @return array
	 */
	private function commentParents()
	{
		return array(
			'news' => array(
				'types'  => array('0', 'news'),
				'table'  => 'news',
				'key'    => 'news_id',
				'plugin' => '',
			),
			'download' => array(
				'types'  => array('2', 'download'),
				'table'  => 'download',
				'key'    => 'download_id',
				'plugin' => 'download',
			),
			'poll' => array(
				'types'  => array('4', 'poll'),
				'table'  => 'polls',
				'key'    => 'poll_id',
				'plugin' => 'poll',
			),
			'page' => array(
				'types'  => array('page'),
				'table'  => 'page',
				'key'    => 'page_id',
				'plugin' => '',
			),
		);
	}

	/**
	 * @param array $left
	 * @param array $right
	 * @return int
	 */
	private function byNewestFirst($left, $right)
	{
		if($left['datestamp'] == $right['datestamp'])
		{
			return 0;
		}

		return ($left['datestamp'] > $right['datestamp']) ? -1 : 1;
	}

	/**
	 * The userclass predicate core states for a comma separated class column.
	 *
	 * The column holds a list, so it is matched as one: an IN () would make
	 * MySQL read '254,0' as the number 254, and would admit a list that names
	 * both a class the visitor holds and e_UC_NOBODY.
	 *
	 * @param \e107\Database\QueryBuilder $qb
	 * @param string $column
	 * @return void
	 */
	private function whereClassPermits($qb, $column)
	{
		$qb->where($qb->expr()->regexp($column, e_CLASS_REGEXP))
			->where($qb->expr()->not($qb->expr()->regexp($column, e_NOBODY_REGEXP)));
	}

	/**
	 * Comments the visitor could have reached through the page they were left on.
	 *
	 * comment_blocked is a property of the comment. Whether the item it belongs
	 * to has been published, and who may read it, are properties of that item, so
	 * the feed joins to it and asks there.
	 *
	 * @param string $name key from {@see rss_menu_rss::commentParents()}
	 * @param array $parent its description
	 * @param int $limit
	 * @return array comment rows
	 */
	private function visibleComments($name, $parent, $limit)
	{
		$now = time();
		$userclass = array_map('intval', explode(',', USERCLASS_LIST));

		$qb = e107::getDb()->createQueryBuilder();
		$qb->select('c.*')
			->from('comments', 'c')
			->innerJoin($parent['table'], 'p', $qb->expr()->compareColumns('p.'.$parent['key'], 'c.comment_item_id'))
			->where('c.comment_blocked', 0)
			->whereIn('c.comment_type', $parent['types']);

		switch($name)
		{
			case 'news':
				$this->whereClassPermits($qb, 'p.news_class');
				$qb->where('p.news_start', '<', $now)
					->where($qb->expr()->anyOf(
						$qb->expr()->eq('p.news_end', 0),
						$qb->expr()->gt('p.news_end', $now)
					));
				break;

			case 'download':
				// download_visible is who may see the item listed, which is what
				// a feed is; download_class is who may then fetch the file.
				$qb->innerJoin('download_category', 'dc',
						$qb->expr()->compareColumns('dc.download_category_id', 'p.download_category'))
					->whereIn('p.download_visible', $userclass)
					->whereIn('p.download_class', $userclass)
					->whereIn('dc.download_category_class', $userclass)
					->where('p.download_active', '!=', 0);
				break;

			case 'poll':
				$qb->where('p.poll_start_datestamp', '<=', $now)
					->where($qb->expr()->anyOf(
						$qb->expr()->eq('p.poll_end_datestamp', 0),
						$qb->expr()->gt('p.poll_end_datestamp', $now)
					));
				break;

			case 'page':
				$this->whereClassPermits($qb, 'p.page_class');
				$qb->where('p.page_password', '');
				break;
		}

		return $qb->orderBy('c.comment_datestamp', 'DESC')
			->setFirstResult(0)->setMaxResults($limit)
			->fetchAll();
	}
}
