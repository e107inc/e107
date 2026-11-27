<?php


	class eResponseTest extends \Test\Unit
	{

		/** @var eResponse */
		protected $er;

		/** @var array[] what each system_meta_pre event carried */
		private static $heardMetaTags = array();

		protected function _before()
		{

			try
			{
				$this->er = $this->make('eResponse');
			}

			catch(Exception $e)
			{
				$this->assertTrue(false, $e->getMessage());
			}

		}
		public function testAddMeta()
		{
			$title = "Admin's Blog Title";
			$this->er->addMeta('og:title', $title);
			$result = $this->er->getMeta('og:title');

			$expected = array (
			  'og:title' =>
			  array (
			    'property' => 'og:title',
			    'content' => "Admin's Blog Title",
			  ),
			);

			$this->assertSame($expected, $result);

		}
		public function testAddMetaData()
		{

			$title = "Admin's Blog Title";

			$this->er->addMetaData('e_PAGETITLE', $title);
			$result = $this->er->getMetaData('e_PAGETITLE');

			$this->assertSame("Admin's Blog Title", $result);

			$title = ' - "Quote"';

			$this->er->addMetaData('e_PAGETITLE', $title);
			$result = $this->er->getMetaData('e_PAGETITLE');

			$this->assertSame("Admin's Blog Title - \"Quote\"", $result);

		}
		public function testAddMetaTitle()
		{
			$title = 'Admin&#39;s Blog Title';
			$this->er->addMetaTitle($title);
			$result = $this->er->getMetaTitle();
			$this->assertSame("Admin's Blog Title", $result);


			$title = ' "quote"';
			$this->er->addMetaTitle($title);
			$result = $this->er->getMetaTitle();
			$this->assertSame("Admin's Blog Title -  \"quote\"", $result);


			$title = 'Cam&#039;s Fixed &quot;Meta&quot;';
			$this->er->addMetaTitle($title, true);
			$result = $this->er->getMetaTitle();
			$this->assertSame("Cam's Fixed &quot;Meta&quot;", $result);

		}

		public function testEveryParameterHasADefault()
		{
			self::assertTrue($this->er->getParam('render'));
			self::assertFalse($this->er->getParam('meta'));
			self::assertFalse($this->er->getParam('jsonNoTitle'));
			self::assertFalse($this->er->getParam('jsonRender'));
			self::assertNull($this->er->getParam('unknown'));
			self::assertSame('fallback', $this->er->getParam('unknown', 'fallback'));
		}

		public function testAParameterExistsUnlessItIsUnsetOrNull()
		{
			self::assertSame($this->er, $this->er->setParam('render', false));
			self::assertTrue($this->er->isParam('render'));
			self::assertFalse($this->er->getParam('render', true));

			$this->er->setParam('render', null);
			self::assertFalse($this->er->isParam('render'));
			self::assertFalse($this->er->isParam('unknown'));
		}

		public function testSettingAllParametersDropsTheDefaults()
		{
			self::assertSame($this->er, $this->er->setParams(array('jsonRender' => true)));

			self::assertTrue($this->er->getParam('jsonRender'));
			self::assertNull($this->er->getParam('render'));
		}

		public function testTheContentTypeIsANameWithAMediaType()
		{
			self::assertSame('html', $this->er->getContentType());
			self::assertSame('text/html', $this->er->getContentMediaType('html'));
			self::assertSame('application/json', $this->er->getContentMediaType('json'));
			self::assertNull($this->er->getContentMediaType('csv'));

			self::assertSame($this->er, $this->er->addContentType('csv', 'text/csv'));
			$this->er->setContentType('csv');

			self::assertSame('csv', $this->er->getContentType());
			self::assertSame('text/csv', $this->er->getContentMediaType($this->er->getContentType()));
		}

		public function testTheBodyIsKeptPerNamespace()
		{
			self::assertSame('', $this->er->getBody());
			self::assertSame($this->er, $this->er->appendBody('a'));
			$this->er->appendBody('b')->appendBody('side', 'menu');

			self::assertSame('ab', $this->er->getBody());
			self::assertSame('ab', $this->er->getBody('default'));
			self::assertSame('side', $this->er->getBody('menu'));
			self::assertSame('', $this->er->getBody('unknown'));

			self::assertSame($this->er, $this->er->setBody('c'));
			self::assertSame('c', $this->er->getBody());
			self::assertSame('side', $this->er->getBody('menu'));
		}

		public function testReadingABodyWithResetEmptiesIt()
		{
			$this->er->setBody('a');

			self::assertSame('a', $this->er->getBody('default', true));
			self::assertSame('', $this->er->getBody());
		}

		/**
		 * Recorded, not endorsed: the assignment inside prependBody() is commented out.
		 */
		public function testPrependingToTheBodyAddsNothing()
		{
			$this->er->setBody('a');

			self::assertSame($this->er, $this->er->prependBody('b'));
			self::assertSame('a', $this->er->getBody());

			$this->er->prependBody('b', 'menu');
			self::assertSame('', $this->er->getBody('menu'));
		}

		public function testTheTitleIsItsPartsJoinedPerNamespace()
		{
			self::assertSame('', $this->er->getTitle());
			self::assertSame($this->er, $this->er->setTitle('One'));
			self::assertSame($this->er, $this->er->appendTitle('Two'));
			self::assertSame($this->er, $this->er->prependTitle('Zero'));
			$this->er->appendTitle('Menu', 'menu')->prependTitle('Side', 'menu');

			self::assertSame('Zero &raquo; One &raquo; Two', $this->er->getTitle());
			self::assertSame('Side &raquo; Menu', $this->er->getTitle('menu'));
			self::assertSame('', $this->er->getTitle('unknown'));

			$this->er->setTitle('Only');
			self::assertSame('Only', $this->er->getTitle());
		}

		public function testAnEmptyTitlePartIsNotAdded()
		{
			$this->er->setTitle('One')->appendTitle('')->prependTitle('');

			self::assertSame('One', $this->er->getTitle());
		}

		public function testATitleWithAnEmptyNamespaceGoesToTheDefaultOne()
		{
			$this->er->setTitle('One', '')->appendTitle('Two', '')->prependTitle('Zero', '');

			self::assertSame('Zero &raquo; One &raquo; Two', $this->er->getTitle());
			self::assertSame('Zero &raquo; One &raquo; Two', $this->er->getTitle(''));
		}

		public function testReadingATitleWithResetEmptiesIt()
		{
			$this->er->setTitle('One', 'menu');

			self::assertSame('One', $this->er->getTitle('menu', true));
			self::assertSame('', $this->er->getTitle('menu'));
		}

		public function testTheRenderModeIsKeptPerNamespace()
		{
			self::assertSame('default', $this->er->getRenderMod());
			self::assertNull($this->er->getRenderMod('menu'));

			self::assertSame($this->er, $this->er->setRenderMod('error_page_404'));
			$this->er->setRenderMod('side', 'menu');

			self::assertSame('error_page_404', $this->er->getRenderMod());
			self::assertSame('error_page_404', $this->er->getRenderMod(''));
			self::assertSame('side', $this->er->getRenderMod('menu'));
		}

		public function testARenderModeSwitchedOffReadsAsNone()
		{
			$this->er->setRenderMod(false);

			self::assertNull($this->er->getRenderMod());
		}

		public function testAMetaTagCarriesNameAndPropertyUnlessItsNameIsListedForOne()
		{
			self::assertSame($this->er, $this->er->addMeta('description', 'About'));
			$this->er->addMeta('keywords', 'a,b')->addMeta('og:title', 'Open');

			self::assertSame(array(
				'description' => array('property' => 'description', 'name' => 'description', 'content' => 'About'),
				'keywords' => array('name' => 'keywords', 'content' => 'a,b'),
				'og:title' => array('property' => 'og:title', 'content' => 'Open'),
			), $this->er->getMeta());
		}

		public function testAMetaTagWithoutContentIsNotAdded()
		{
			$this->er->addMeta('description', '')->addMeta('robots', null)->addMeta('author', 0);

			self::assertSame(array(), $this->er->getMeta());
		}

		public function testAMetaTagAddedAgainReplacesTheFirst()
		{
			$this->er->addMeta('description', 'First')->addMeta('description', 'Second', array('lang' => 'en'));

			self::assertSame(array(
				'description' => array('property' => 'description', 'name' => 'description', 'content' => 'Second', 'lang' => 'en'),
			), $this->er->getMeta());
		}

		public function testSetMetaRewritesOnlyATagThatHasAName()
		{
			$this->er->addMeta('description', 'About')->addMeta('og:title', 'Open');

			self::assertSame($this->er, $this->er->setMeta('description', 'Changed'));
			$this->er->setMeta('og:title', 'Changed')->setMeta('author', 'Nobody');

			self::assertSame(array(
				'description' => array('property' => 'description', 'name' => 'description', 'content' => 'Changed'),
				'og:title' => array('property' => 'og:title', 'content' => 'Open'),
			), $this->er->getMeta());
		}

		public function testRemoveMetaDropsATagByNameOrByProperty()
		{
			$this->er->addMeta('description', 'About')->addMeta('keywords', 'a,b')->addMeta('og:title', 'Open');

			self::assertSame($this->er, $this->er->removeMeta('keywords'));
			$this->er->removeMeta('og:title')->removeMeta('author');

			self::assertSame(array('description'), array_keys($this->er->getMeta()));
		}

		public function testMetaTagsRenderOnePerLineWithDoubleQuotesAndOpeningAngleBracketsCutOut()
		{
			$restore = $this->withCorePrefs(array('meta_keywords' => null));

			try
			{
				$this->er->addMeta('description', 'Say "hi" <now>')->addMeta('og:title', 'Open', array('data set' => 'x"y'));

				self::assertSame(
					'<meta property="description" name="description" content="Say hi now>" />'."\n"
					.'<meta property="og:title" content="Open" dataset="xy" />'."\n",
					$this->er->renderMeta());
			}
			finally
			{
				$restore();
			}
		}

		public function testRenderingRewritesAKeywordsTagFromTheMetaKeywordsAndTheSiteKeywords()
		{
			$restore = $this->withCorePrefs(array('meta_keywords' => array(e_LANGUAGE => 'b,c')));

			try
			{
				$this->er->addMetaKeywords('a')->addMetaKeywords('b')->addMeta('keywords', 'only in the tag');

				self::assertSame('<meta name="keywords" content="a,b,c" />'."\n", $this->er->renderMeta());
			}
			finally
			{
				$restore();
			}
		}

		public function testTheSiteKeywordsAreNotRenderedWithoutAKeywordsTag()
		{
			$restore = $this->withCorePrefs(array('meta_keywords' => array(e_LANGUAGE => 'b,c')));

			try
			{
				$this->er->addMetaKeywords('a');

				self::assertSame('', $this->er->renderMeta());
			}
			finally
			{
				$restore();
			}
		}

		public function testRenderingAnnouncesTheMetaTagsBeforeTheSiteKeywordsAreMergedIn()
		{
			$restore = $this->withCorePrefs(array('meta_keywords' => array(e_LANGUAGE => 'b')));
			$event = e107::getEvent();
			$listeners = $event->functions;
			$event->register('system_meta_pre', array(__CLASS__, 'hearMetaTags'));

			try
			{
				$rendered = $this->er->addMetaKeywords('a')->addMeta('keywords', 'a')->renderMeta();

				self::assertSame(array(array('keywords' => array('name' => 'keywords', 'content' => 'a'))), self::$heardMetaTags);
				self::assertSame('<meta name="keywords" content="a,b" />'."\n", $rendered);
			}
			finally
			{
				$event->functions = $listeners;
				self::$heardMetaTags = array();
				$restore();
			}
		}

		/**
		 * @param array $meta what the system_meta_pre event carries
		 * @return void
		 */
		public static function hearMetaTags($meta)
		{
			self::$heardMetaTags[] = $meta;
		}

		public function testTheMetaDescriptionAndKeywordsAreEscapedAsTheyAreAdded()
		{
			self::assertSame($this->er, $this->er->addMetaDescription('Tom &amp; "Jerry" <b>'));
			$this->er->addMetaDescription(' again');
			self::assertSame($this->er, $this->er->addMetaKeywords('cat'));
			$this->er->addMetaKeywords("mouse's");

			self::assertSame('Tom &amp; &quot;Jerry&quot; &lt;b&gt; again', $this->er->getMetaDescription());
			self::assertSame('cat,mouse&#039;s', $this->er->getMetaKeywords());
		}

		public function testAnOverridingMetaTitleIsKeptApartFromTheOrdinaryOne()
		{
			$this->er->addMetaTitle('Page');
			self::assertSame($this->er, $this->er->addMetaTitle('Whole', false, true));
			$this->er->addMetaTitle('Title', false, true);

			self::assertSame('Page', $this->er->getMetaTitle());
			self::assertSame('Whole - Title', $this->er->getMetaTitle(true));

			$this->er->addMetaTitle('Again', true, true);
			self::assertSame('Again', $this->er->getMetaTitle(true));
			self::assertSame('Page', $this->er->getMetaTitle());
		}

		public function testMetaDataOfAnUnknownKindIsNeitherKeptNorRead()
		{
			self::assertSame($this->er, $this->er->addMetaData('unknown', 'value'));

			self::assertSame('', $this->er->getMetaData('unknown'));
			self::assertSame('', $this->er->getMetaData('META_DESCRIPTION'));
		}

		public function testSendReturnsTheBodyInOneThemeBoxAndEmptiesIt()
		{
			$this->er->setTitle('Caption')->setBody('Body')->setRenderMod('error_page_404');
			$drawn = array();
			$restore = $this->withRecordingRender($drawn);

			try
			{
				self::assertSame('the box', $this->er->send('default', true, false));
			}
			finally
			{
				$restore();
			}

			self::assertSame(array(array('Caption', 'Body', 'error_page_404', true)), $drawn);
			self::assertSame('', $this->er->getBody());
			self::assertSame('', $this->er->getTitle());
			self::assertSame('error_page_404', $this->er->getRenderMod());
		}

		public function testSendHasTheBoxPrintedWhenItIsNotAskedToReturn()
		{
			$this->er->setTitle('Caption')->setBody('Body');
			$drawn = array();
			$restore = $this->withRecordingRender($drawn);

			try
			{
				self::assertSame('', $this->er->send('default', false, false));
			}
			finally
			{
				$restore();
			}

			self::assertSame(array(array('Caption', 'Body', 'default', false)), $drawn);
		}

		public function testSendPutsTheSystemMessagesAboveTheBody()
		{
			$message = eMessage::getInstance();
			$message->addInfo('recorded by eResponseTest');
			$this->er->setBody('Body')->setParam('render', false);

			$sent = $this->er->send('default', true, true);

			self::assertStringContainsString('recorded by eResponseTest', $sent);
			self::assertStringEndsWith('Body', $sent);
			self::assertSame('', $message->render());
		}

		public function testSendWithoutTheMessagesLeavesThemWaiting()
		{
			$message = eMessage::getInstance();
			$message->addInfo('recorded by eResponseTest');
			$this->er->setBody('Body')->setParam('render', false);

			self::assertSame('Body', $this->er->send('default', true, false));
			self::assertStringContainsString('recorded by eResponseTest', $message->render());
		}

		public function testSendLeavesTheBoxOutWhenRenderingIsOff()
		{
			$this->er->setTitle('Caption')->setBody('Body')->setParam('render', false);

			self::assertSame('Body', $this->er->send('default', true, false));
		}

		public function testSendLeavesTheBoxOutWhenTheNamespaceHasNoRenderMode()
		{
			$this->er->setTitle('Caption')->setBody('Body')->setRenderMod(false);
			self::assertSame('Body', $this->er->send('default', true, false));

			$this->er->setTitle('Caption', 'menu')->setBody('Side', 'menu');
			self::assertSame('Side', $this->er->send('menu', true, false));
		}

		public function testSendPrintsWhenItIsNotAskedToReturn()
		{
			$this->er->setBody('Body')->setParam('render', false);

			ob_start();
			$returned = $this->er->send('default', false, false);
			$printed = ob_get_clean();

			self::assertSame('', $returned);
			self::assertSame('Body', $printed);
		}

		public function testSendMetaDefinesThePageConstantsFromWhatWasAdded()
		{
			$report = $this->metaReport('', "\$response->sendMeta();");

			self::assertSame(array('e_PAGETITLE' => 'Page - Title', 'META_DESCRIPTION' => 'About', 'META_KEYWORDS' => 'a,b'), $report);
		}

		public function testSendSendsTheMetaOnlyWhenTheParameterAsksForIt()
		{
			self::assertSame(array('e_PAGETITLE' => null, 'META_DESCRIPTION' => null, 'META_KEYWORDS' => null),
				$this->metaReport('', "\$response->send('default', true, false);"));

			$report = $this->metaReport('', "\$response->setParam('meta', true)->send('default', true, false);");
			self::assertSame('Page - Title', $report['e_PAGETITLE']);
		}

		public function testSendMetaDefinesNothingOnceTheThemeHeaderHasStarted()
		{
			$report = $this->metaReport("define('USER_AREA', true); ", "\$response->sendMeta();");

			self::assertSame(array('e_PAGETITLE' => null, 'META_DESCRIPTION' => null, 'META_KEYWORDS' => null), $report);
		}

		public function testSendJsonPrintsTheLastTitlePartAndTheBodyThenEndsTheRequest()
		{
			list($output) = $this->runInBootedCli(
				"\$response = new eResponse(); \$response->setTitle('First')->appendTitle('Last')->setBody('Body'); "
				."echo \"\\n\"; \$response->sendJson(array('footer' => 'Foot'), null, false); echo 'NOT REACHED';",
				self::BUFFERED);
			$printed = implode("\n", $output);

			self::assertStringContainsString("/*-secure-\n".'{"header":"Last","body":"Body","footer":"Foot"}'."\n*/", $printed);
			self::assertStringNotContainsString('NOT REACHED', $printed);
		}

		/**
		 * Puts a render handler in place that records each box it is asked for and draws 'the box'; call what it returns in a finally block.
		 *
		 * @param array $drawn receives the caption, text, mode and return flag of each box
		 * @return callable puts the site's render handler back
		 */
		private function withRecordingRender(array &$drawn)
		{
			$render = e107::getRender();

			e107::setRegistry('core/e107/singleton/e_render', $this->make('e_render', array(
				'tablerender' => function ($caption, $text, $mode = 'default', $return = false) use (&$drawn)
				{
					$drawn[] = array($caption, $text, $mode, $return);

					return 'the box';
				},
			)));

			return static function () use ($render)
			{
				e107::setRegistry('core/e107/singleton/e_render', $render);
			};
		}

		/**
		 * Runs $act in a booted child on a response holding a meta title, description and keywords, and reports the constants it defined.
		 *
		 * @param string $arrange statements run before the response is built
		 * @param string $act statements run on $response
		 * @return array e_PAGETITLE, META_DESCRIPTION and META_KEYWORDS, each null where undefined
		 */
		private function metaReport($arrange, $act)
		{
			$marker = 'E_RESPONSE=';

			list($output) = $this->runInBootedCli(
				$arrange."\$response = new eResponse(); "
				."\$response->addMetaTitle('Page')->addMetaTitle('Title')->addMetaDescription('About')->addMetaKeywords('a')->addMetaKeywords('b'); "
				.$act
				." echo \"\\n".$marker."\", json_encode(array('e_PAGETITLE' => defined('e_PAGETITLE') ? e_PAGETITLE : null, "
				."'META_DESCRIPTION' => defined('META_DESCRIPTION') ? META_DESCRIPTION : null, "
				."'META_KEYWORDS' => defined('META_KEYWORDS') ? META_KEYWORDS : null)), \"\\n\";",
				self::BUFFERED);

			return self::reportedAfter($output, $marker, 'the child that sent the meta');
		}

	}
