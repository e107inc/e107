<?php

/**
 * Issue #6377: a book's chapter listing carries the book's own title,
 * description, keywords and icon, and a chapter rendered inside it carries
 * none of them.
 *
 * The fixture theme template is here because no bundled chapter template
 * renders {BOOK_ICON}, which is the only place a missing chapter_icon shows.
 */
class CpageChapterMetaCest
{
	/** Layout the fixture template adds, so {BOOK_ICON} has somewhere to render. */
	const LAYOUT = 'chaptermeta6377';

	const BOOK_NAME    = 'Sweep book 6377';
	const CHAPTER_NAME = 'Sweep chapter 6377';
	const BOOK_DIZ     = 'BOOKDIZ6377';
	const CHAPTER_DIZ  = 'CHAPDIZ6377';
	const BOOK_KEYS    = 'BOOKKEY6377';
	const CHAPTER_KEYS = 'CHAPKEY6377';

	/** A core glyph, which {@see e_parse::toGlyph()} renders as its own name inside a class attribute. */
	const BOOK_ICON = 'e-book-32.glyph';

	/** @var int */
	private $bookId;

	/** @var int */
	private $chapterId;

	/** @var string */
	private $templateFile;

	public function _before(AcceptanceTester $I)
	{
		$this->templateFile = $I->grabActiveThemeDir().'templates/chapter_template.php';
		$I->writeAppFile($this->templateFile, $this->templateSource());

		$this->bookId = $this->seedChapter($I, array(
			'chapter_parent'           => 0,
			'chapter_name'             => self::BOOK_NAME,
			'chapter_sef'              => 'sweep-book-6377',
			'chapter_meta_description' => self::BOOK_DIZ,
			'chapter_meta_keywords'    => self::BOOK_KEYS,
			'chapter_icon'             => self::BOOK_ICON,
			'chapter_template'         => self::LAYOUT,
		));

		$this->chapterId = $this->seedChapter($I, array(
			'chapter_parent'           => $this->bookId,
			'chapter_name'             => self::CHAPTER_NAME,
			'chapter_sef'              => 'sweep-chapter-6377',
			'chapter_meta_description' => self::CHAPTER_DIZ,
			'chapter_meta_keywords'    => self::CHAPTER_KEYS,
			'chapter_icon'             => '',
			'chapter_template'         => 'default',
		));

		$I->haveInDatabase('e107_page', array(
			'page_title'     => 'Sweep page 6377',
			'page_sef'       => 'sweep-page-6377',
			'page_chapter'   => $this->chapterId,
			'page_text'      => '[html]<p>PAGEBODY6377</p>[/html]',
			'page_author'    => 1,
			'page_datestamp' => time(),
			'page_class'     => 0,
			'page_password'  => '',
			'page_template'  => 'default',
			'page_order'     => 0,
		));
	}

	public function _after(AcceptanceTester $I)
	{
		$I->deleteAppFile($this->templateFile);
	}

	public function bookListingKeepsItsOwnMetaAndIcon(AcceptanceTester $I)
	{
		$I->wantTo('confirm a book listing is titled, described and iconed as the book');

		$I->amOnPage('/page.php?bk='.$this->bookId);

		$I->seeInTitle(self::BOOK_NAME);
		$I->dontSeeInTitle(self::CHAPTER_NAME);

		$description = $this->metaContent($I, 'description');
		$I->assertStringContainsString(self::BOOK_DIZ, $description,
			'the description meta tag carries the book description');
		$I->assertStringNotContainsString(self::CHAPTER_DIZ, $description,
			'a chapter rendered inside the listing does not describe the listing');

		$keywords = $this->metaContent($I, 'keywords');
		$I->assertStringContainsString(self::BOOK_KEYS, $keywords,
			'the keywords meta tag carries the book keywords');
		$I->assertStringNotContainsString(self::CHAPTER_KEYS, $keywords,
			'a chapter rendered inside the listing does not key the listing');

		$I->seeInSource(self::BOOK_ICON);
		$I->see(self::CHAPTER_NAME);
	}

	public function chapterListingKeepsItsOwnMeta(AcceptanceTester $I)
	{
		$I->wantTo('confirm a chapter listing is still titled and described as the chapter');

		$I->amOnPage('/page.php?ch='.$this->chapterId);

		$I->seeInTitle(self::CHAPTER_NAME);

		$description = $this->metaContent($I, 'description');
		$I->assertStringContainsString(self::CHAPTER_DIZ, $description,
			'the description meta tag carries the chapter description');
		$I->assertStringNotContainsString(self::BOOK_DIZ, $description,
			'the book the chapter belongs to does not describe it');

		$I->see('Sweep page 6377');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name meta tag name
	 * @return string the tag's content attribute
	 */
	private function metaContent(AcceptanceTester $I, $name)
	{
		$pattern = '#<meta name="'.$name.'" content="([^"]*)"#';
		$source = $I->grabPageSource();

		$I->assertMatchesRegularExpression($pattern, $source,
			'the page carries a '.$name.' meta tag');

		preg_match($pattern, $source, $match);

		return $match[1];
	}

	/**
	 * @param AcceptanceTester $I
	 * @param array $row
	 * @return int chapter id
	 */
	private function seedChapter(AcceptanceTester $I, $row)
	{
		return $I->haveInDatabase('e107_page_chapters', array_merge(array(
			'chapter_manager'    => 254,
			'chapter_image'      => '',
			'chapter_order'      => 0,
			'chapter_visibility' => 0,
		), $row));
	}

	/**
	 * @return string a chapter template whose listChapters layout renders {BOOK_ICON}
	 */
	private function templateSource()
	{
		$layout = self::LAYOUT;

		return <<<PHP
<?php

\$CHAPTER_TEMPLATE['{$layout}']['listChapters']['caption'] = "{BOOK_NAME}";
\$CHAPTER_TEMPLATE['{$layout}']['listChapters']['start']   = "<div class='chapter-book-icon'>{BOOK_ICON}</div><ul>";
\$CHAPTER_TEMPLATE['{$layout}']['listChapters']['item']    = "<li>{CHAPTER_NAME}{PAGES}</li>";
\$CHAPTER_TEMPLATE['{$layout}']['listChapters']['end']     = "</ul>";
PHP;
	}
}
