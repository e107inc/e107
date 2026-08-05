<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * RSS feed addon: template and developer reference.
 *
 * Copy this file into your plugin folder as e_rss.php and adapt it. It
 * documents the two-method contract the rss_menu plugin expects from every
 * feed provider. The example bodies are illustrative: replace the placeholder
 * table and columns with your own.
 */

if (!defined('e107_INIT')) { exit; }


/**
 * The class name must be the plugin FOLDER plus '_rss'.
 *
 * rss_menu builds the include path from the feed row's rss_path (the plugin
 * folder) and derives the class from that path, not from the feed key stored
 * in rss_url. The two are independent: chatbox_menu serves a feed whose key is
 * 'chatbox' from a class named chatbox_menu_rss. Name the class after the
 * folder and the key can be whatever reads best in a URL.
 */
class _blank_rss
{
	/**
	 * Declares the feed(s) this plugin offers.
	 *
	 * Called by the RSS admin Import page so an administrator can add the feed
	 * to the rss table. Return one array per feed: a base feed, and optionally
	 * one row per category.
	 *
	 * Recognised keys:
	 *   'name'        Admin-facing feed name. Prefer a LAN constant.
	 *   'url'         The feed key. Stored in rss_url and handed back to data()
	 *                 as $parms['url']. Use a text key; the numeric keys some
	 *                 older feeds carry are legacy and are not worth copying.
	 *   'topic_id'    '' for the base feed, or a category id for a
	 *                 category-scoped feed. A literal '*' marks the row as a
	 *                 template needing a topic id supplied at request time;
	 *                 such rows are excluded from the feed list and from the
	 *                 <link rel="alternate"> discovery tags.
	 *   'description' Admin-facing description. 'text' is accepted as an older
	 *                 spelling of the same field and 'description' wins when
	 *                 both are present. Either way the value lands in rss_text.
	 *   'class'       Userclass permitted to see the feed. e_UC_PUBLIC is everyone.
	 *   'limit'       Default item count.
	 *
	 * Do not set 'path'. The Import page overwrites it with your plugin folder.
	 *
	 * If the feature behind the feed can be switched off site-wide, check that
	 * preference here and return an empty array when it is off, so a disabled
	 * feature is never offered as a feed.
	 *
	 * @return array
	 */
	function config()
	{
		$config = array();

		$config[] = array(
			'name'        => 'Feed Name',                      // e.g. LAN_PLUGIN_BLANK_RSS
			'url'         => 'blank',                          // text key -> rss_url -> $parms['url']
			'topic_id'    => '',                               // '' = base feed
			'description' => 'RSS feed for the _blank plugin',
			'class'       => e_UC_PUBLIC,
			'limit'       => '9'
		);

		// Optional: one feed per category. Same 'url', different 'topic_id'.
		//
		// $rows = e107::getDb()->createQueryBuilder()
		//     ->select('*')->from('blank_category')
		//     ->orderBy('category_name')->fetchAll();
		//
		// foreach($rows as $row)
		// {
		//     $config[] = array(
		//         'name'        => 'Feed Name > '.$row['category_name'],
		//         'url'         => 'blank',
		//         'topic_id'    => $row['category_id'],
		//         'description' => 'Category feed: '.$row['category_name'],
		//         'class'       => e_UC_PUBLIC,
		//         'limit'       => '9'
		//     );
		// }

		return $config;
	}


	/**
	 * Produces the feed items.
	 *
	 * Called by rss_menu when a feed is served. $parms['url'] says which feed
	 * to build when config() declared several, $parms['id'] is the topic id
	 * from the request, and $parms['limit'] is the row's configured count.
	 *
	 * Return a 0-indexed array of items. Keys rss_menu reads, anything else is
	 * ignored:
	 *
	 *   'title'         Item title.
	 *   'link'          Item URL. A value containing 'http' is used verbatim;
	 *                   anything else is treated as relative to the plugins
	 *                   directory. Return an absolute URL from e107::url() with
	 *                   'mode' => 'full' and the ambiguity never arises.
	 *   'description'   Item body or summary. HTML is allowed.
	 *   'datestamp'     UNIX timestamp, becomes pubDate. Defaults to the current
	 *                   time when absent, so set it or every item looks new.
	 *   'author'        Author name.
	 *   'author_email'  Author email address.
	 *   'category_name' Category label.
	 *   'category_link' Category URL, resolved like 'link' above.
	 *
	 * These are emitted only in the RSS output, not in Atom:
	 *
	 *   'custom'        Array of extra tags keyed by tag name, for podcast
	 *                   elements and anything else the core feed does not model.
	 *   'media'         Array of media:content / media:player structures.
	 *   'enc_url'       Enclosure file, relative to the plugins directory. It is
	 *                   always prefixed, so an absolute URL will not survive.
	 *   'enc_leng'      Enclosure size in bytes.
	 *   'enc_type'      Enclosure MIME type. All three enclosure keys must be
	 *                   set and non-empty or no <enclosure> element is written.
	 *
	 * @param array $parms url, id, limit
	 * @return array
	 */
	function data($parms = null)
	{
		$limit = (int) vartrue($parms['limit'], 10);

		// Replace this with a query against your own table. $parms['id'] is the
		// category filter when the feed row carries a topic id.
		//
		// $rows = e107::getDb()->createQueryBuilder()
		//     ->select('*')->from('blank')
		//     ->orderBy('blank_datestamp', 'DESC')
		//     ->setFirstResult(0)->setMaxResults($limit)
		//     ->fetchAll();
		$rows = array();

		$rss = array();
		$i   = 0;

		foreach($rows as $row)
		{
			$rss[$i]['title']       = $row['blank_title'];

			// 'item' is illustrative. Add a matching rule to this plugin's
			// e_url.php, which currently ships 'index', 'other' and 'parked'.
			$rss[$i]['link']        = e107::url('_blank', 'item', $row, array('mode' => 'full'));

			$rss[$i]['description'] = $row['blank_message'];
			$rss[$i]['datestamp']   = (int) $row['blank_datestamp'];
			$rss[$i]['author']      = $row['blank_author'];

			// $rss[$i]['author_email']  = $row['blank_author_email'];
			// $rss[$i]['category_name'] = $row['blank_category_name'];
			// $rss[$i]['category_link'] = e107::url('_blank', 'index', array(), array('mode' => 'full'));

			// Enclosure: set all three or none.
			// $rss[$i]['enc_url']  = '_blank/files/'.$row['blank_file'];
			// $rss[$i]['enc_leng'] = $row['blank_file_size'];
			// $rss[$i]['enc_type'] = 'audio/mpeg';

			$i++;
		}

		return $rss;
	}
}
