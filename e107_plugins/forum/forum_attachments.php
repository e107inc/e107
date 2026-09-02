<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum post attachments: where they live and who may read them
 */

if(!defined('e107_INIT'))
{
	exit;
}

/**
 * The forum's answer to "may this caller read this attachment?".
 *
 * An attachment is not a file with permissions of its own. It belongs to a
 * post, the post belongs to a forum, and the forum is what carries the
 * userclass. Anything that hands the bytes to a browser has to make that
 * journey, and until this class existed only forum_class::sendFile() did.
 *
 * A file under the attachment tree that no post names is refused. Nothing
 * bundled reads one: sc_attachments() emits only names it read out of
 * post_attachments, and renderPreview() renders no attachments at all. The
 * tree collects such files from an abandoned preview, from a delete whose
 * unlink failed and from a half-completed v1 migration, and every one of them
 * was written by a member posting into a forum somebody may have restricted.
 *
 * Written to be usable from thumb.php, which builds a bootstrap of its own
 * with no session, no userclass constants and no class2. Nothing here touches
 * a constant class2 defines, and the caller's classes arrive as an argument
 * rather than being read out of the environment.
 *
 * @see e107_plugins/forum/forum_class.php  sendFile()
 * @see e107_handlers/e_thumbnail_class.php
 */
class forum_attachments
{
	/** @var string canonical path of the file, or '' when it is not one of ours */
	private $path = '';

	/** @var array|null forum ids of the posts carrying this file, once looked up */
	private $forums = null;

	/**
	 * @param string $path path of an attachment, constants already expanded
	 */
	public function __construct($path = '')
	{
		$roots = self::roots();

		if($path !== '' && $roots !== array())
		{
			$resolved = e107::getFile()->resolveSendPath($path, $roots);

			$this->path = ($resolved === false) ? '' : $resolved;
		}
	}

	/**
	 * Every directory the forum has ever stored a post attachment in.
	 *
	 * The second spelling is where forum_update::moveAttachment() puts the
	 * reduced-resolution copy of a 0.7/0.8 attachment. It holds the same
	 * picture as the original, so it answers to the same rule.
	 *
	 * @return array paths with a trailing slash, whether or not they exist
	 */
	public static function paths()
	{
		return array(
			e_MEDIA . 'plugins/forum/attachments/',
			e_MEDIA . 'files/plugins/forum/attachments/',
		);
	}

	/**
	 * The subset of paths() that is on the disk.
	 *
	 * @return array
	 */
	public static function roots()
	{
		$roots = array();

		foreach(self::paths() as $path)
		{
			if(is_dir($path))
			{
				$roots[] = $path;
			}
		}

		return $roots;
	}

	/**
	 * Create the attachment directory if it is absent, then write the guard
	 * files that stop the web server handing out an attachment by its raw path.
	 *
	 * A rule at the top of the tree covers everything below it on Apache, but
	 * the blank index.html is the part a server with its own configuration
	 * language honours, and a directory listing of a poster's directory
	 * publishes every name in it. Each poster's directory is therefore covered
	 * as well as the root above it.
	 *
	 * The walk over every poster's directory is why this belongs to the setup
	 * routes and to no request path: install and upgrade through
	 * {@see forum_setup::install_post()}, and the v1 migration's first step
	 * through {@see forum_update::checkAttachmentDirs()}, which is reached when
	 * the upgrade hook does not run. A post covers the root and the directory it
	 * is about to write into, in
	 * {@see forum_post_handler::processAttachments()}.
	 *
	 * A public forum's attachments stay readable, because nothing in e107 ever
	 * links the raw path. view_shortcodes.php renders an image through
	 * thumbUrl() and links a file through forum_viewtopic.php?id=&dl=, and both
	 * of those read the file off the disk rather than off the web server.
	 *
	 * @return bool true when every directory found is covered
	 */
	public static function protect()
	{
		$paths = self::paths();

		if(!is_dir($paths[0]))
		{
			@mkdir($paths[0], 0755, true);
		}

		$file = e107::getFile();
		$done = true;

		foreach(self::roots() as $root)
		{
			if(!$file->protectDirectory($root))
			{
				$done = false;
			}

			$posters = glob($root . '*', GLOB_ONLYDIR);

			foreach(($posters ? $posters : array()) as $dir)
			{
				if(!$file->protectDirectory($dir . '/'))
				{
					$done = false;
				}
			}
		}

		return $done;
	}

	/**
	 * Whether every caller may read the attachment, decided without working out
	 * who is asking.
	 *
	 * The ordinary attachment hangs off a post in a public forum, and answering
	 * it without resolving the caller is what keeps that request free of a
	 * session and its response free of the headers that stop a shared cache
	 * storing it.
	 *
	 * @return bool
	 */
	public function admitsEveryone()
	{
		return $this->mayRead(array(e_UC_PUBLIC));
	}

	/**
	 * Whether a caller holding $classes may read the attachment.
	 *
	 * One readable forum is enough. The same bytes can hang off more than one
	 * post, and a post in a forum the caller may read is a post whose
	 * attachments the caller is being shown.
	 *
	 * No post, no permission: see the class docblock.
	 *
	 * @param array $classes userclass ids the caller holds
	 * @return bool
	 */
	public function mayRead(array $classes)
	{
		$forums = $this->postForums();

		if($forums === array())
		{
			return false;
		}

		return $this->readableForums($forums, $classes) !== array();
	}

	/**
	 * Which forums a caller holding $classes reaches through $column.
	 *
	 * The forum's view/post/thread permission rule, in one place, because a
	 * permission rule written twice is a permission rule that can drift apart.
	 * A forum grants nothing unless its parent grants it too, and a forum at
	 * the top level grants nobody anything, which is why the join is not
	 * decoration.
	 *
	 * It lives in this file rather than in forum_class.php because this file
	 * loads into thumb.php's bootstrap and forum_class.php cannot: forum_class
	 * pulls in a template at file scope and expects the language constants
	 * class2 defines. The dependency only works in this direction.
	 *
	 * @see e107forum::_getForumPermList()
	 * @param array $classes userclass ids the caller holds
	 * @param string $column one of the three forum class columns
	 * @param array $forumIds forum ids to consider, empty for all of them
	 * @return array rows of forum_id and forum_parent
	 */
	public static function readableForumRows(array $classes, $column, array $forumIds = array())
	{
		$columns = array('forum_class', 'forum_postclass', 'forum_threadclass');

		if(!in_array($column, $columns, true))
		{
			return array();
		}

		$classes = array_map('intval', $classes);

		if($classes === array())
		{
			return array();
		}

		$qb = e107::getDb()->createQueryBuilder();

		$placeholders = array();

		foreach($classes as $class)
		{
			$placeholders[] = $qb->createNamedParameter($class);
		}

		$placeholders = implode(', ', $placeholders);

		$qb->select('f.forum_id', 'f.forum_parent')->from('forum', 'f')
			->leftJoin('forum', 'fp', $qb->raw('f.forum_parent = fp.forum_id AND fp.' . $column . ' IN (' . $placeholders . ')'))
			->where($qb->raw('f.' . $column . ' IN (' . $placeholders . ')'))
			->where('f.forum_parent', '!=', 0)
			->where($qb->expr()->isNotNull('fp.forum_id'));

		if($forumIds !== array())
		{
			$qb->whereIn('f.forum_id', $forumIds);
		}

		return $qb->fetchAll();
	}

	/**
	 * The forums of every post carrying this file.
	 *
	 * The name alone does not identify a file: the same name can sit in two
	 * posters' directories. The poster is read back out of the directory the
	 * file is in, which is where getAttachmentPath() puts it, so the pair is
	 * the file's identity.
	 *
	 * The LIKE is a prefilter over a serialised column and can only match too
	 * much; every candidate is then unserialised and compared exactly, so a
	 * name that happens to be a substring of another post's does not widen the
	 * answer.
	 *
	 * @return array forum ids, possibly empty
	 */
	private function postForums()
	{
		if($this->forums !== null)
		{
			return $this->forums;
		}

		$this->forums = array();

		if($this->path === '')
		{
			return $this->forums;
		}

		$user = $this->poster();
		$name = basename($this->path);

		if($user === false)
		{
			return $this->forums;
		}

		$sql = e107::getDb();

		if(!$sql->isTable('forum_post'))
		{
			return $this->forums;
		}

		$qb = $sql->createQueryBuilder();

		$rows = $qb->select('post_forum', 'post_attachments')->from('forum_post')
			->where('post_user', $user)
			->where($qb->expr()->contains('post_attachments', $name))
			->fetchAll();

		foreach($rows as $row)
		{
			if(self::names($row['post_attachments'], $name) && !in_array((int) $row['post_forum'], $this->forums, true))
			{
				$this->forums[] = (int) $row['post_forum'];
			}
		}

		return $this->forums;
	}

	/**
	 * The poster whose directory holds the file.
	 *
	 * @return int|false user id, 0 for a guest, false when the file is not in a
	 *                   poster's directory at all
	 */
	private function poster()
	{
		$path = str_replace(DIRECTORY_SEPARATOR, '/', $this->path);

		foreach(self::roots() as $root)
		{
			$real = @realpath($root);

			if(empty($real))
			{
				continue;
			}

			$real = rtrim(str_replace(DIRECTORY_SEPARATOR, '/', $real), '/') . '/';

			if(strpos($path, $real) !== 0)
			{
				continue;
			}

			$parts = explode('/', (string) substr($path, strlen($real)));

			if(count($parts) !== 2)
			{
				return false;
			}

			if($parts[0] === 'anon')
			{
				return 0;
			}

			return preg_match('/^user_(\d+)$/', $parts[0], $m) ? (int) $m[1] : false;
		}

		return false;
	}

	/**
	 * Whether a stored post_attachments value names $name.
	 *
	 * @param string $stored serialised post_attachments
	 * @param string $name
	 * @return bool
	 */
	private static function names($stored, $name)
	{
		$attachments = e107::unserialize($stored);

		if(!is_array($attachments))
		{
			return false;
		}

		foreach($attachments as $entries)
		{
			foreach((array) $entries as $entry)
			{
				$file = is_array($entry) ? varset($entry['file'], '') : $entry;

				if((string) $file === $name)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Which of $forums a caller holding $classes may view.
	 *
	 * Only the forums themselves. e107forum::_getForumPermList() also marks the
	 * parent category readable so that a category page can list the children a
	 * caller may reach; a post does not live in a category, and a gate is the
	 * wrong place to widen an answer for a listing's benefit.
	 *
	 * @param array $forums forum ids to consider
	 * @param array $classes userclass ids the caller holds
	 * @return array the subset of $forums the caller may view
	 */
	private function readableForums(array $forums, array $classes)
	{
		if($forums === array())
		{
			return array();
		}

		$readable = array();

		foreach(self::readableForumRows($classes, 'forum_class', $forums) as $row)
		{
			$readable[] = (int) $row['forum_id'];
		}

		return $readable;
	}
}
