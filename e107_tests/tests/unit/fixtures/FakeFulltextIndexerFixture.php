<?php

/**
 * Stand-in for db_verify's e_search-derived FULLTEXT indexer so tests do
 * not depend on which plugins ship a search config. It only knows the base
 * table "news"; a language-prefixed name resolves to nothing, mirroring
 * production where no e_search config exists under lan_dutch_news.
 *
 * A named fixture in its own include (rather than an anonymous class) keeps
 * the test file parseable on PHP 5.6; see AdminUiSearchfieldProbeFixture
 * for the full rationale.
 */
class FakeFulltextIndexerFixture
{
	public function getIndexesForTable($tableName)
	{
		if($tableName === 'news')
		{
			return array(
				'ft_news_news_title' => array(
					'type'    => 'FULLTEXT',
					'keyname' => 'news_title',
					'field'   => 'ft_news_news_title',
				),
			);
		}

		return array();
	}
}
