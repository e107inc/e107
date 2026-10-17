<?php
namespace Test;

/**
 * Whole rows for the core tables a Cest seeds through haveInDatabase(), so a test names only the columns it is about.
 */
final class Rows
{
	/**
	 * @param array $columns the columns this comment is about
	 * @return array an e107_comments row, every other column at its default
	 */
	public static function comment(array $columns)
	{
		return array_merge(array(
			'comment_pid'          => 0,
			'comment_item_id'      => 0,
			'comment_subject'      => '',
			'comment_author_id'    => 0,
			'comment_author_name'  => '',
			'comment_author_email' => '',
			'comment_datestamp'    => 0,
			'comment_comment'      => '',
			'comment_blocked'      => 0,
			'comment_ip'           => '',
			'comment_type'         => '0',
			'comment_lock'         => 0,
			'comment_share'        => 0,
		), $columns);
	}

	/**
	 * @param array $columns the columns this news item is about
	 * @return array an e107_news row, every other column empty or at its default
	 */
	public static function news(array $columns)
	{
		return array_merge(array(
			'news_title'            => '',
			'news_sef'              => '',
			'news_body'             => '',
			'news_extended'         => '',
			'news_meta_keywords'    => '',
			'news_meta_description' => '',
			'news_datestamp'        => 0,
			'news_author'           => 0,
			'news_category'         => 0,
			'news_allow_comments'   => 0,
			'news_start'            => 0,
			'news_end'              => 0,
			'news_class'            => '0',
			'news_render_type'      => '0',
			'news_summary'          => '',
			'news_thumbnail'        => '',
		), $columns);
	}

	/**
	 * @param array $columns the columns this page is about
	 * @return array an e107_page row, every other column empty or at its default
	 */
	public static function page(array $columns)
	{
		return array_merge(array(
			'page_title'        => '',
			'page_sef'          => '',
			'page_subtitle'     => '',
			'page_chapter'      => 0,
			'page_metatitle'    => '',
			'page_metakeys'     => '',
			'page_metadscr'     => '',
			'page_metaimage'    => '',
			'page_metarobots'   => '',
			'page_text'         => '',
			'page_author'       => 0,
			'page_datestamp'    => 0,
			'page_rating_flag'  => 0,
			'page_comment_flag' => 0,
			'page_password'     => '',
			'page_class'        => '0',
			'page_ip_restrict'  => '',
			'page_template'     => '',
			'page_order'        => 9999,
			'page_fields'       => '',
			'menu_name'         => '',
			'menu_title'        => '',
			'menu_text'         => '',
			'menu_image'        => '',
			'menu_icon'         => '',
			'menu_template'     => '',
			'menu_class'        => '0',
			'menu_button_url'   => '',
			'menu_button_text'  => '',
		), $columns);
	}
}
