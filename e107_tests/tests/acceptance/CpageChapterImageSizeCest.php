<?php

/**
 * Issue #6692: {CHAPTER_IMAGE} in a chapter template takes the size the template gives it, from a {SETIMAGE} before it or from its own parameters.
 */
class CpageChapterImageSizeCest
{
	const LAYOUT = 'chapterimage6692';

	/** A thumbnail address's forced size, in its query form (aw=W&ah=H) or its rewritten one (/aWxaH/). */
	const FORCED_SIZE = '#aw=(\d+)&ah=(\d+)|/a(\d+)xa(\d+)/#';

	/** @var int */
	private $bookId;

	/** @var int[] */
	private $chapterIds = array();

	/** @var string */
	private $templateFile;

	public function _before(AcceptanceTester $I)
	{
		$this->templateFile = $I->grabActiveThemeDir().'templates/chapter_template.php';
		$I->writeAppFile($this->templateFile, $this->templateSource());

		$this->chapterIds = array();
		$this->bookId = $this->seedChapter($I, array(
			'chapter_parent' => 0,
			'chapter_name'   => 'Sweep book 6692',
			'chapter_sef'    => 'sweep-book-6692',
			'chapter_image'  => '',
		));

		foreach(array('logo.png', 'button.png') as $order => $image)
		{
			$chapterId = $this->seedChapter($I, array(
				'chapter_parent' => $this->bookId,
				'chapter_name'   => 'Sweep chapter 6692 '.$order,
				'chapter_sef'    => 'sweep-chapter-6692-'.$order,
				'chapter_image'  => '{e_IMAGE}'.$image,
				'chapter_order'  => $order,
			));

			$I->haveInDatabase('e107_page', array(
				'page_title'     => 'Sweep page 6692 '.$order,
				'page_sef'       => 'sweep-page-6692-'.$order,
				'page_chapter'   => $chapterId,
				'page_text'      => '[html]<p>PAGEBODY6692</p>[/html]',
				'page_author'    => 1,
				'page_datestamp' => time(),
				'page_class'     => 0,
				'page_password'  => '',
				'page_template'  => 'default',
				'page_order'     => 0,
			));

			$this->chapterIds[] = $chapterId;
		}
	}

	public function _after(AcceptanceTester $I)
	{
		$I->deleteAppFile($this->templateFile);
	}

	public function aSetimageInTheItemSizesItsOwnChapter(AcceptanceTester $I)
	{
		$I->amOnPage('/page.php?bk='.$this->bookId);

		$I->assertSame(array('320x200', '320x200'), $this->imageSizes($I, 'item'),
			'every chapter, the first included, takes the size set just before its image');
	}

	public function theImageTakesTheSizeWrittenOnIt(AcceptanceTester $I)
	{
		$I->amOnPage('/page.php?bk='.$this->bookId);

		$I->assertSame(array('240x160', '240x160'), $this->imageSizes($I, 'own'),
			'{CHAPTER_IMAGE: w=240&h=160&crop=1} renders at that size');
	}

	public function aSetimageInThePageListStartSizesTheChapterImage(AcceptanceTester $I)
	{
		$I->amOnPage('/page.php?ch='.$this->chapterIds[0]);

		$I->assertSame(array('300x180'), $this->imageSizes($I, 'pages'),
			'the chapter page takes the size set before the image in its own start template');
	}

	public function theOtherValuesStillPrintWithParametersOnTheirTags(AcceptanceTester $I)
	{
		$I->amOnPage('/page.php?bk='.$this->bookId);

		$I->seeInSource("<span class='name6692'>Sweep chapter 6692 0</span>");
		$I->seeInSource("<span class='name6692'>Sweep chapter 6692 1</span>");
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $marker the class of the span the fixture template wraps the images in
	 * @return string[] the forced width x height of each wrapped image, in page order
	 */
	private function imageSizes(AcceptanceTester $I, $marker)
	{
		preg_match_all("#<span class='img6692-".$marker."'><img [^>]*src=\"([^\"]+)\"#", $I->grabPageSource(), $images);

		$I->assertNotEmpty($images[1], 'the page carries the '.$marker.' images');

		$sizes = array();

		foreach($images[1] as $src)
		{
			$src = html_entity_decode($src);
			$I->assertMatchesRegularExpression(self::FORCED_SIZE, $src, 'a forced size is in '.$src);
			preg_match(self::FORCED_SIZE, $src, $size);
			$sizes[] = isset($size[3]) ? $size[3].'x'.$size[4] : $size[1].'x'.$size[2];
		}

		return $sizes;
	}

	/**
	 * @param AcceptanceTester $I
	 * @param array $row
	 * @return int chapter id
	 */
	private function seedChapter(AcceptanceTester $I, $row)
	{
		return $I->haveInDatabase('e107_page_chapters', array_merge(array(
			'chapter_meta_description' => '',
			'chapter_meta_keywords'    => '',
			'chapter_manager'          => 254,
			'chapter_icon'             => '',
			'chapter_order'            => 0,
			'chapter_template'         => self::LAYOUT,
			'chapter_visibility'       => 0,
		), $row));
	}

	/**
	 * @return string a chapter template that sizes {CHAPTER_IMAGE} in each of the ways a template can
	 */
	private function templateSource()
	{
		$layout = self::LAYOUT;

		return <<<PHP
<?php

\$CHAPTER_TEMPLATE['{$layout}']['listChapters']['caption'] = "{BOOK_NAME}";
\$CHAPTER_TEMPLATE['{$layout}']['listChapters']['start']   = "<ul>";
\$CHAPTER_TEMPLATE['{$layout}']['listChapters']['item']    = "<li>{SETIMAGE: w=320&h=200&crop=1}<span class='img6692-item'>{CHAPTER_IMAGE}</span><span class='img6692-own'>{CHAPTER_IMAGE: w=240&h=160&crop=1}</span><span class='name6692'>{CHAPTER_NAME: x=1}</span>{SETIMAGE: w=100&h=0&crop=0}</li>";
\$CHAPTER_TEMPLATE['{$layout}']['listChapters']['end']     = "</ul>";

\$CHAPTER_TEMPLATE['{$layout}']['listPages']['caption'] = "{CHAPTER_NAME}";
\$CHAPTER_TEMPLATE['{$layout}']['listPages']['start']   = "{SETIMAGE: w=300&h=180&crop=1}<span class='img6692-pages'>{CHAPTER_IMAGE}</span><ul>";
\$CHAPTER_TEMPLATE['{$layout}']['listPages']['item']    = "<li>{CPAGETITLE}</li>";
\$CHAPTER_TEMPLATE['{$layout}']['listPages']['end']     = "</ul>{SETIMAGE: w=100&h=0&crop=0}";
PHP;
	}
}
