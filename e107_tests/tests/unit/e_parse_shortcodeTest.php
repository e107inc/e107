<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2018 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

class e_parse_shortcodeTest extends \Codeception\Test\Unit
{
	/** @var e_parse_shortcode */
	private $scParser;
	/**
	 * @var e_render
	 */
	private $original_e_render;
	/**
	 * @var e_date
	 */
	private $original_e_date;

	public function _before()
	{
		e107::loadAdminIcons();
		e107::getParser()->setFontAwesome(5);

		try
		{
			$this->scParser = $this->make('e_parse_shortcode');
		}
		catch (Exception $e)
		{
			$this->fail("Couldn't create e_parse_shortcode object");
		}

		$this->scParser->__construct();

		$this->original_e_render = e107::getRender();
		$mock_e_render = $this->make('e_render');
		e107::setRegistry('core/e107/singleton/e_render', $mock_e_render);

		$this->original_e_date = e107::getDate();
		$mock_e_date = $this->make('e_date', [
			'computeLapse' => 'E107_TEST_STUBBED_OUT',
			'convert_date' => function($datestamp, $mask = '')
			{
				return $this->original_e_date->convert_date(0, $mask);
			},
		]);
		e107::setRegistry('core/e107/singleton/e_date', $mock_e_date);
	}

	public function _after()
	{
		e107::setRegistry('core/e107/singleton/e_render', $this->original_e_render);
		e107::setRegistry('core/e107/singleton/e_date', $this->original_e_date);
	}

//	public function testShortcode_SITELINKS_ALT()
//	{
//		$output = $this->scParser->parseCodes('{SITELINKS_ALT=/e107_themes/jayya/images/arrow.png+noclick}');
//		var_export($output);
//	}

	/*
	public function testIsBatchOverride()
	{

	}

	public function testIsRegistered()
	{

	}

	public function testIsOverride()
	{

	}

	public function testResetScClass()
	{

	}

	public function testDoCode()
	{

	}

	public function testGetScObject()
	{

	}
*/
	public function testParseCodesWithArray()
	{
		$text = '<ul class="dropdown-menu {LINK_SUB_OVERSIZED}" role="menu" >';

		$array = array(
			'LINK_TEXT' => 'Content',
		    'LINK_URL' => '#',
		    'ONCLICK' => '',
		    'SUB_HEAD' => '',
		    'SUB_MENU' => '',

		    'ID' => '',
		    'SUB_ID' => '',
		    'LINK_CLASS' =>  'e-expandit',
		    'SUB_CLASS' =>  'e-hideme e-expandme',
		    'LINK_IMAGE' =>  '',
		    'LINK_SUB_OVERSIZED' => 'oversized',
		    'LINK_BADGE' => '',
		);

		  // -- Legacy Wrapper --
        global $sc_style;
        $sc_style = array();
        $sc_style['LINK_SUB_OVERSIZED']['pre'] = "** ";
        $sc_style['LINK_SUB_OVERSIZED']['post'] = " **";

		$actual = $this->scParser->parseCodes($text, false, $array);
        $expected = '<ul class="dropdown-menu ** oversized **" role="menu" >';
		$this->assertEquals($expected, $actual);

		// v2.x Array Wrapper - should override any $sc_style legacy wrapper
		$array['_WRAPPER_'] = "non-existent/template";
		$actual = $this->scParser->parseCodes($text, false, $array);
        $expected = '<ul class="dropdown-menu oversized" role="menu" >';
		$this->assertEquals($expected, $actual);

	}

	public function testParseCodesWithMagicShortcodes()
	{
		$template = '{SITENAME} {---BREADCRUMB---}';
		$expected = 'e107 {---BREADCRUMB---}';

		$result = $this->scParser->parseCodes($template, true);
		$this->assertSame($expected, $result);

		$array = array(
			'LINK_TEXT' => 'Content',
		    'LINK_URL' => '#',
		    'ONCLICK' => '',
		    'SUB_HEAD' => '',
		    'SUB_MENU' => '',
		);

		$result = $this->scParser->parseCodes($template, true, $array);
		$this->assertSame($expected, $result);

		$sc = e107::getScBatch('_blank', true, '_blank');
	    $this->assertIsObject($sc);
		$result = $this->scParser->parseCodes($template, true, $sc);
		$this->assertSame($expected, $result);

	}


	public function testParseCodesWithClass()
	{
	    $sc = e107::getScBatch('_blank', true, '_blank');
	    $this->assertIsObject($sc);

        // - v1.x Wrapper Test.
        global $sc_style;
        $sc_style = array();
        $sc_style['BLANK_TEST']['pre'] = "** ";
        $sc_style['BLANK_TEST']['post'] = " **";

        $actualTemplate = e107::getTemplate('_blank', '_blank', 'default');
        $otherTemplate = e107::getTemplate('_blank', '_blank', 'other');


        $expectedTemplate = "<div>{BLANK_TEST}</div>";
        $this->assertEquals($expectedTemplate, $actualTemplate);
        $actualLegacy = $this->scParser->parseCodes($actualTemplate, false, $sc);
        $expectedLegacy = "<div>** test **</div>";
        $this->assertEquals($expectedLegacy, $actualLegacy);

        // - v2.x Wrapper Test.
        $sc->wrapper('_blank/default'); // overrides legacy $sc_style;
        $actual = $this->scParser->parseCodes($actualTemplate, false, $sc);
        $expected = "<div>[ test ]</div>";
        $this->assertEquals($expected, $actual);

		// different template, same wrapper ID.
        $actual = $this->scParser->parseCodes($otherTemplate, false, $sc);
        $expected = "<div>[ test ]</div>";
        $this->assertEquals($expected, $actual);

		// different template and non-existent wrappers - should fallback to legacy wrappers and not use '_blank/default' wrappers by the same name.
        $sc->wrapper('_blank/other');
        $actual = $this->scParser->parseCodes($otherTemplate, false, $sc);
		$expected = "<div>** test **</div>";
        $this->assertEquals($expected, $actual);


        // And back to a wrapper that exists.
        $sc->wrapper('_blank/default'); // overrides legacy $sc_style;
        $actual = $this->scParser->parseCodes($otherTemplate, false, $sc);
        $expected = "<div>[ test ]</div>";
        $this->assertEquals($expected, $actual);


    }


    public function testAdminShortcodes()
    {

      //  require_once(e_CORE."shortcodes/batch/admin_shortcodes.php");
        e107::getScBatch('admin');

        e107::includeLan(e_LANGUAGEDIR.'English/admin/lan_header.php');
        e107::includeLan(e_LANGUAGEDIR.'English/admin/lan_footer.php');

		e107::loadAdminIcons();

        try
		{
			$sc = $this->make('admin_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();


        $this->processShortcodeMethods($sc);

    }

    public function testBBcodeShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/bbcode_shortcodes.php");

        try
		{
			$sc = $this->make('bbcode_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();

        $this->processShortcodeMethods($sc);

    }

    public function testCommentShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/comment_shortcodes.php");

        try
		{
			/** @var comment_shortcodes $sc */
			$sc = $this->make('comment_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

	   $values = array(
	        'comment_id'           => '84',
	        'comment_pid'          => '82',
	        'comment_item_id'      => '53',
	        'comment_subject'      => 'Re: New Item',
	        'comment_author_id'    => '1',
	        'comment_author_name'  => 'admin',
	        'comment_author_email' => 'someone@gmail.com',
	        'comment_datestamp'    => '1609767045',
	        'comment_comment'      => 'Nested Comment here',
	        'comment_blocked'      => '0',
	        'comment_ip'           => '0000:0000:0000:0000:0000:ffff:7f00:0001',
	        'comment_type'         => '0',
	        'comment_lock'         => '0',
	        'comment_share'        => '0',
	        'table'                 => 'news',
			'action'	            => '',
			'subject' 	            => 'subject name',
			'comval'	            => 'a comment',
			'itemid'	            => 5,
			'pid'		            => 3,
	        'eaction'	            => '',
	        'rate'		            => 2,
	        'user_id'               => 1,
	        'user_join'             => 1518441749
	   );

		$sc->__construct();
		$sc->setVars($values);

        $this->processShortcodeMethods($sc);

    }



    public function testContactShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/contact_shortcodes.php");

        try
		{
			$sc = $this->make('contact_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();
        $this->processShortcodeMethods($sc);

    }


    public function testErrorShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/error_shortcodes.php");

        try
		{
			$sc = $this->make('error_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();
        $this->processShortcodeMethods($sc);

    }


    public function testLoginShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/login_shortcodes.php");

        try
		{
			$sc = $this->make('login_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();

        $this->processShortcodeMethods($sc);

    }

    public function testNavigationShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/navigation_shortcodes.php");

        try
		{
			/** @var navigation_shortcodes $sc */
			$sc = $this->make('navigation_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
			'link_id'          => '6',
			'link_name'        => 'News',
			'link_url'         => 'news.php',
			'link_description' => '',
			'link_button'      => '',
			'link_category'    => '1',
			'link_order'       => '5',
			'link_parent'      => '0',
			'link_open'        => '0',
			'link_class'       => '0',
			'link_function'    => 'page::bookNavChaptersPages',
			'link_sefurl'      => 'index',
			'link_owner'       => 'news'
		);

		$sc->__construct();
		$sc->setVars($vars);

        $this->processShortcodeMethods($sc);


        // Test sub links with deep level array.
        $template =  e107::getCoreTemplate('navigation', 'main');
		$sc->template =  $template;

        $outArray 	= array();
		$data 		= array($vars);

		$ret = e107::getNav()->compile($data, $outArray);

		$sc->setVars($ret[0]);
		$actual = e107::getParser()->parseTemplate('{LINK_SUB}', true, $sc);
		$this->assertStringContainsString('General</a>', $actual);
		$this->assertStringContainsString('<li role="menuitem" class="dropdown-submenu lower">', $actual);
		$this->assertStringContainsString('<li role="menuitem" class="link-depth-3">', $actual);

		// test sublink with HTML.

		$vars['link_function'] = 'theme::sc_bootstrap_megamenu_example';

	    $outArray 	= array();
		$data 		= array($vars);

		$ret = e107::getNav()->compile($data, $outArray);

		// HTML in {LINK_SUB}
		$sc->setVars($ret[0]);
		$actual = e107::getParser()->parseTemplate('{LINK_SUB}', false, $sc);
		$this->assertStringContainsString('<div class="dropdown-menu">', $actual);

		// HTML in {NAV_LINK_SUB}
		$actual = e107::getParser()->parseTemplate('{NAV_LINK_SUB}', false, $sc);
		$this->assertStringContainsString('<div class="dropdown-menu">', $actual);

		// test HTML with core template using e107::getNav()->render();
		$result = e107::getNav()->render($ret, $template);
		$this->assertStringContainsString('<li class="nav-item dropdown theme-sc-bootstrap-megamenu-example">', $result);
		$this->assertStringContainsString('<div class="dropdown-menu"><div class="container mega-menu-example">', $result);

    }

    // "Next/Prev"
    public function testPaginationShortCode()
    {
    	require_once(e_CORE."shortcodes/single/nextprev.php");

        $tests = array(
            0 => array(
                'parm' => array (
                    'nonavcount' => '',
                    'bullet' => '',
                    'caption' => '',
                    'pagetitle' => 'Page 1|Page1|Page2|Page3',
                    'tmpl_prefix' => 'page',
                    'total' => '4',
                    'amount' => '1',
                    'current' => '0',
                    'url' => '/new-page-test?page=--FROM--',
                ),
                'expected'  => '
<!-- Start of Next/Prev -->
<div class="cpage-nav">
&nbsp;<a class=\'cpage-np current\' href=\'#\' onclick=\'return false;\' title="Page 1">Page 1</a><br />&nbsp;<a class=\'cpage-np\' href=\'/new-page-test?page=1\' title="Page1">Page1</a><br />&nbsp;<a class=\'cpage-np\' href=\'/new-page-test?page=2\' title="Page2">Page2</a><br />&nbsp;<a class=\'cpage-np\' href=\'/new-page-test?page=3\' title="Page3">Page3</a>
</div>
<!-- End of Next/Prev -->
'),
            1 => array(
                'parm' => array (
                    'nonavcount' => '',
                    'bullet' => '',
                    'caption' => '',
                    'pagetitle' => 'Page 1|Page1|Page2|Page3',
                    'tmpl_prefix' => 'dropdown',
                    'total' => '4',
                    'amount' => '1',
                    'current' => '0',
                    'url' => '/new-page-test?page=--FROM--',
                ),
                'expected'  => '
<!-- Start of Next/Prev -->
<div class="nextprev form-group form-inline input-group input-group-btn"><select class="tbox npdropdown nextprev-select form-control form-select" name="pageSelect" onchange="window.location.href=this.options[selectedIndex].value"><option value="/new-page-test?page=0" selected="selected">Page 1</option><option value="/new-page-test?page=1">Page1</option><option value="/new-page-test?page=2">Page2</option><option value="/new-page-test?page=3">Page3</option></select><a class="btn btn-default btn-outline-secondary nextprev-item next tbox npbutton" href="/new-page-test?page=1" title="Go to the next page">next</a></div>
<!-- End of Next/Prev -->
',
            ),


        );

        foreach($tests as $item)
        {
            $result = nextprev_shortcode($item['parm']);
            $this->assertSame($item['expected'], $result);
        }


    }


    public function testNewsArchiveShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/news_archive_shortcodes.php");

        try
		{
			$sc = $this->make('news_archive_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
			'news_id'       => 1,
			'news_title'    => "my title",
			'news_datestamp'    => time(),
			'category_name'     => "my category",
			'user_id'           => 1,
			'user_name'         => 'admin'
		);

		$sc->__construct();
		$sc->setVars($vars);

        $this->processShortcodeMethods($sc);

    }


    public function testNewsShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/news_shortcodes.php");

        try
		{
			/** @var news_shortcodes $sc */
			$sc = $this->make('news_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
			'news_id'               => '1',
			'news_title'            => 'Welcome',
			'news_sef'              => 'welcome-to-e107-me-again-x',
			'news_body'             => '[html]<p>Main Body</p>[/html]',
			'news_extended'         => '[html]<p><strong>Extended Body</strong></p>[/html]',
			'news_meta_keywords'    => 'welcome,new website',
			'news_meta_description' => 'Description for Facebook and search engines.',
			'news_meta_robots'      => '',
			'news_datestamp'        => '1454367600',
			'news_modified'         => '1654101979',
			'news_author'           => '1',
			'news_category'         => '1',
			'news_allow_comments'   => '0',
			'news_start'            => '0',
			'news_end'              => '0',
			'news_class'            => '0',
			'news_render_type'      => '0',
			'news_comment_total'    => '0',
			'news_summary'          => 'Example news item summary there',
			'news_thumbnail'        => '{e_THEME}agency2/install/news/deer.jpg,,,,',
			'news_sticky'           => '0',
			'news_template'         => 'default'
		);

        $parms = array(
            'news_body'         => array(
                '=body'             => '<!-- bbcode-html-start --><p>Main Body</p><!-- bbcode-html-end -->',
                '=extended'         => '<!-- bbcode-html-start --><p><strong>Extended Body</strong></p><!-- bbcode-html-end -->',
            ),
            'newscommentlink'   => array(
                ': class=me'    => "<a title='0 Comments' class='e-tip me' href='".e107::url('news/view/item', ['news_id'=>1, 'news_sef'=>'welcome-to-e107-me-again-x'])."'><i class='fas fa-comment' ></i></a>"

            ),

		);

	//	$sc->setVars($vars);
		$sc->__construct();
		$sc->setScVar('news_item', $vars);
		$sc->setScVar('param', array('current_action'=>'extend'));


        $this->processShortcodeMethods($sc, $parms);

    }

	 public function testPageShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/page_shortcodes.php");

        try
		{
			/** @var cpage_shortcodes $sc */
			$sc = $this->make('cpage_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();

		$vars =  array(
			'page_id'           => '1',
			'page_title'        => 'Article 1',
			'page_subtitle'     => 'My subtitle goes here.',
			'page_sef'          => 'article-1',
			'page_chapter'      => '2',
			'page_metakeys'     => 'keywords',
			'page_metadscr'     => 'Meta Description',
			'page_metarobots'   => 'noindex',
			'page_text'         => '[html]<p>Lorem ipsum dolor sit amet</p><p>Suspendisse <b>placerat</b> nunc orci</p>[/html]',
			'page_author'       => '1',
			'page_datestamp'    => '1371420000',
			'page_rating_flag'  => '1',
			'page_comment_flag' => '1',
			'page_password'     => '',
			'page_class'        => '0',
			'page_ip_restrict'  => '',
			'page_template'     => 'default',
			'page_order'        => '20',
			'page_fields'       => NULL,'menu_name'                                                                                                                         => '',
			'menu_title'        => 'Heading 1',
			'menu_text'         => '[html]<p>Lorem ipsum dolor sit amet. Suspendisse placerat nunc orci, lectus tellus.</p>[/html]',
			'menu_image'        => '{e_THEME}myimage.jpg',
			'menu_icon'         => '',
			'menu_template'     => 'button',
			'menu_class'        => '0',
			'menu_button_url'   => '',
			'menu_button_text'  => ''
		);

		$sc->__construct();
		$sc->setVars($vars);
		$sc->setScVar('pageTitles', []);
		$sc->setScVar('pageSelected', 0);
		$sc->setScVar('bullet', '');

		$exclude = array('sc_cpagemessage'); // system messages

		 $parms = array(
            'cpagebody'         => array(
                ': strip=blocks'    => '<!-- bbcode-html-start --><p>Main Body</p><!-- bbcode-html-end -->',
                '=extended'         => '<!-- bbcode-html-start --><p><strong>Extended Body</strong></p><!-- bbcode-html-end -->',
            ),
		 );





        $this->processShortcodeMethods($sc, null, $exclude);

    }

     public function testPageEShortcodes()
    {
        require_once(e_PLUGIN."page/e_shortcode.php");

        try
		{
			/** @var page_shortcodes $sc */
			$sc = $this->make('page_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars =  array('page_id' => '1',
/*			'page_title' => 'Article 1',
			'page_subtitle' => 'My subtitle goes here.',
			'page_sef' => 'article-1',
			'page_chapter' => '2',
			'page_metakeys' => 'keywords',
			'page_metadscr' => 'Meta Description',
			'page_metarobots' => 'noindex',
			'page_text' => '[html]<p>Lorem ipsum dolor sit amet, <sup>1</sup> consectetur adipiscing elit. Donec libero ipsum; imperdiet at risus non, dictum sagittis odio! Nulla facilisi. Pellentesque adipiscing facilisis pharetra. Morbi imperdiet augue in ligula luctus, et iaculis est porttitor. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. In ultricies vitae nisi ut porttitor. Curabitur lectus tellus, feugiat a elit vel, gravida iaculis dui. Nulla vulputate turpis dui, ac faucibus enim dignissim non. Ut non tellus suscipit, scelerisque orci sed, scelerisque sapien. Aenean convallis sodales nulla in porttitor. In pretium ante sapien, a tempor eros blandit nec <sup>2</sup>.<br><br>Nulla non est nibh? Fusce lacinia quam adipiscing magna posuere dapibus. Sed mollis condimentum rhoncus. Morbi sollicitudin tellus a ligula luctus, ac varius arcu ullamcorper. Mauris in aliquet tellus, nec porttitor dui. Quisque interdum euismod mi sed bibendum. Vivamus non odio quis quam lacinia rhoncus in nec nibh. Integer vitae turpis condimentum, laoreet diam nec viverra fusce.</p>[/html]',
			'page_author' => '1',
			'page_datestamp' => '1371420000',
			'page_rating_flag' => '1',
			'page_comment_flag' => '1',
			'page_password' => '',
			'page_class' => '0',
			'page_ip_restrict' => '',
			'page_template' => 'default',
			'page_order' => '20',
			'page_fields' => NULL,'menu_name' => '',
			'menu_title' => 'Heading 1',
			'menu_text' => '[html]<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus auctor egestas condimentum. Suspendisse placerat nunc orci, a ultrices tortor eleifend in. Vestibulum tincidunt fringilla malesuada? Phasellus dolor risus, aliquam eu odio quis, mattis cursus magna. Integer ut blandit purus; vitae posuere ante. Vivamus sapien nisl, pulvinar vel turpis a, malesuada vehicula lorem! Curabitur magna justo; laoreet at congue sit amet, tincidunt sit amet erat. Integer vehicula eros quis odio tincidunt, nec dapibus sem molestie. Cras sed viverra eros. Nulla ut lectus tellus.</p>[/html]',
			'menu_image' => '{e_THEME}steminst_eu/_content/2019-07/chromosome_dna_pattern_genetic_3_d_psychedelic_1920x1200.jpg',
			'menu_icon' => '',
			'menu_template' => 'button',
			'menu_class' => '0',
			'menu_button_url' => '',
			'menu_button_text' => '',*/
			'chapter_id' => '1',
			'chapter_parent' => '0',
			'chapter_name' => 'General',
			'chapter_sef' => 'general',
			'chapter_meta_description' => 'Lorem ipsum dolor sit amet.',
			'chapter_meta_keywords' => '',
			'chapter_manager' => '0',
			'chapter_icon' => '',
			'chapter_image' => '',
			'chapter_order' => '0',
			'chapter_template' => '',
			'chapter_visibility' => '0',
			'chapter_fields' => NULL

			)
			;

		$sc->__construct();
		$sc->setVars($vars);

	//	$exclude = array('sc_cpagemessage'); // system messages

        $this->processShortcodeMethods($sc);

    }


    public function testSignupShortcodes()
    {
      //  require_once(e_CORE."shortcodes/batch/signup_shortcodes.php");

        try
		{
			$sc = e107::getScBatch('signup'); // $this->make('signup_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();

		$exclude = array('sc_signup_coppa_text'); // uses random email obfiscation.
        $this->processShortcodeMethods($sc, null, $exclude);

    }


	public function testSitedownShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/sitedown_shortcodes.php");

        try
		{
			$sc = $this->make('sitedown_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();
        $this->processShortcodeMethods($sc);

    }

    public function testSocialShortcodes()
    {
    	require_once(e_PLUGIN."social/e_shortcode.php");

        try
		{
			/** @var social_shortcodes $sc */
			$sc = $this->make('social_shortcodes');
		}
		catch (Exception $e)
		{
			self::fail($e->getMessage());
		}

		$sc->__construct();

		parse_str('type=facebook,twitter,youtube,flickr,vimeo,google-plus,github,instagram,linkedin&size=3x', $parm);

		$result = $sc->sc_xurl_icons($parm);

		self::assertStringContainsString('<span class="e-social-twitter fa-3x"></span>', $result);
		self::assertStringContainsString('<span class="e-social-youtube fa-3x"></span>', $result);

    }



    public function testUserShortcodes()
    {
        require_once(e_CORE."shortcodes/batch/user_shortcodes.php");

        try
		{
			/** @var user_shortcodes $sc */
			$sc = $this->make('user_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
			'user_id'           => '1',
			'user_name'         => 'admin',
			'user_loginname'    => 'admin',
			'user_customtitle'  => 'Administrator',
			'user_password'     => '$2y$10$EfCajR8.i3G1Qu82VKwSzu4dOroWabexa9B10LFYuEqepSD4gzzWa',
			'user_sess'         => '',
			'user_email'        => 'myemail@gmail.com',
			'user_signature'    => '',
			'user_image'        => 'myimage.jpeg',
			'user_hideemail'    => '0',
			'user_join'         => '1518441749',
			'user_lastvisit'    => '1609890429',
			'user_currentvisit' => '1609953446',
			'user_lastpost'     => '1609793616',
			'user_chats'        => '1',
			'user_comments'     => '52',
			'user_ip'           => '123.45.678.91',
			'user_ban'          => '0',
			'user_prefs'        => '',
			'user_visits'       => '766',
			'user_admin'        => '1',
			'user_login'        => 'Real Name',
			'user_class'        => '12,14,15,16,6',
			'user_perms'        => '0',
			'user_realm'        => '',
			'user_pwchange'     => '1518441749',
			'user_xup'          => ''

			);

		$sc->__construct();
		$sc->setVars($vars);


		$exclude = array('sc_user_email'); // uses random obfiscation.
        $this->processShortcodeMethods($sc, null, $exclude);

    }


	public function testUserSettingsShortcodes()
    {
   //     require_once(e_CORE."shortcodes/batch/usersettings_shortcodes.php");

        try
		{
			/** @var user_shortcodes $sc */
	//		$sc = $this->make('usersettings_shortcodes');
			$sc = e107::getScBatch('usersettings');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
			'user_id'           => '1',
			'user_name'         => 'admin',
			'user_loginname'    => 'admin',
			'user_customtitle'  => 'Administrator',
			'user_password'     => '$2y$10$EfCajR8.i3G1Qu82VKwSzu4dOroWabexa9B10LFYuEqepSD4gzzWa',
			'user_sess'         => '',
			'user_email'        => 'myemail@gmail.com',
			'user_signature'    => '',
			'user_image'        => 'myimage.jpeg',
			'user_hideemail'    => '0',
			'user_join'         => '1518441749',
			'user_lastvisit'    => '1609890429',
			'user_currentvisit' => '1609953446',
			'user_lastpost'     => '1609793616',
			'user_chats'        => '1',
			'user_comments'     => '52',
			'user_ip'           => '123.45.678.91',
			'user_ban'          => '0',
			'user_prefs'        => '',
			'user_visits'       => '766',
			'user_admin'        => '1',
			'user_login'        => 'Real Name',
			'user_class'        => '12,14,15,16,6',
			'user_perms'        => '0',
			'user_realm'        => '',
			'user_pwchange'     => '1518441749',
			'user_xup'          => '',
			'userclass_list'    => USERCLASS_LIST

			);

	//	$sc->__construct();
		$sc->setVars($vars);

		// these are tested in the user-extended test.
		$exclude = array('sc_userextended_all', 'sc_userextended_cat', 'sc_userextended_field'); // uses e107::setRegistry() to avoid duplicate rendering.
        $this->processShortcodeMethods($sc, null, $exclude);

    }

// -------------- Plugins ------------------------


    public function testChatboxMenuShortcodes()
    {
        require_once(e_PLUGIN."chatbox_menu/chatbox_menu_shortcodes.php");

        try
		{
			/** @var chatbox_menu_shortcodes $sc */
			$sc = $this->make('chatbox_menu_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}


		$vars = array(
			'cb_id'        => '11',
			'cb_nick'      => '1.admin',
			'cb_message'   => 'A new chatbox comment',
			'cb_datestamp' => '1609613065',
			'cb_blocked'   => '0',
			'cb_ip'        => '0000:0000:0000:0000:0000:ffff:7f00:0001'
		);

		$sc->__construct();
		$sc->setVars($vars);

        $this->processShortcodeMethods($sc);

    }

      public function testCommentMenuShortcodes()
    {
        require_once(e_PLUGIN."comment_menu/comment_menu_shortcodes.php");

        try
		{
			/** @var comment_menu_shortcodes $sc */
			$sc = $this->make('comment_menu_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

	   $values = array(
	        'comment_id'           => '84',
	        'comment_pid'          => '82',
	        'comment_item_id'      => '53',
	        'comment_subject'      => 'Re: New Item',
	        'comment_author_id'    => '1',
	        'comment_author_name'  => 'admin',
	        'comment_author_email' => 'someone@gmail.com',
	        'comment_datestamp'    => '1609767045',
	        'comment_comment'      => 'Nested Comment here',
	        'comment_blocked'      => '0',
	        'comment_ip'           => '0000:0000:0000:0000:0000:ffff:7f00:0001',
	   //     'comment_type'         => '0',
	        'comment_lock'         => '0',
	        'comment_share'        => '0',
	        'table'                 => 'news',
			'action'	            => '',
			'subject' 	            => 'subject name',
			'comval'	            => 'a comment',
			'itemid'	            => 5,
			'pid'		            => 3,
	        'eaction'	            => '',
	        'rate'		            => 2,
	        'user_id'               => 1,
	        'user_join'             => 1518441749,
	        'comment_type'          => 'Type',
	        'comment_title'         => "Title",
	        'comment_url'           => e_HTTP."page.php?3",
	        'comment_author'        => 'admin',
			'comment_author_image'  => '',

	   );

		$sc->__construct();
		$sc->setVars($values);
        $this->processShortcodeMethods($sc);

    }


    public function testDownloadShortcodes()
    {
        require_once(e_PLUGIN."download/download_shortcodes.php");

        try
		{
			/** @var download_shortcodes $sc */
			$sc = $this->make('download_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars =  array(
			'download_id'             => '1',
			'download_name'           => 'MyFile v1',
			'download_url'            => '{e_MEDIA_FILE}2016-03/myfile.zip',
			'download_sef'            => 'italk-v1',
			'download_author'         => 'admin',
			'download_author_email'   => 'email@gmail.com',
			'download_author_website' => 'https://somewhere.com',
			'download_description'    => 'description of my file',
			'download_keywords'       => 'keyword1,keyword2',
			'download_filesize'       => '654432',
			'download_requested'      => '4',
			'download_category'       => '2',
			'download_active'         => '1',
			'download_datestamp'      => '1560544675',
			'download_thumb'          => '',
			'download_image'          => '',
			'download_comment'        => '1',
			'download_class'          => '0',
			'download_mirror'         => '',
			'download_mirror_type'    => '0',
			'download_visible'        => '0',
			'download_category_id'    => '2',
			'download_category_name'  => 'My Category',
			'download_category_description' => 'My Category Description',
			'download_category_icon'    => '',
			'download_category_parent'  => '0',
			'download_category_class'   => '0',
			'download_category_order'   => '1',
			'download_category_sef'     => 'my-category'

		);

		$sc->__construct();

		$sc->setVars($vars);

        $this->processShortcodeMethods($sc);

    }

    public function testFaqsShortcodes()
    {
        require_once(e_PLUGIN."faqs/faqs_shortcodes.php");

        try
		{
			/** @var faqs_shortcodes $sc */
			$sc = $this->make('faqs_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
		'faq_id'        => '4',
		'faq_parent'    => '1',
		'faq_question'  => 'My Second Question which is quite long and might wrap to another line after that',
		'faq_answer'    => '[html]<p>My Second Answer</p>[/html]',
		'faq_comment'   => '0',
		'faq_datestamp' => '1461263100',
		'faq_author'    => '1',
		'faq_author_ip' => '',
		'faq_tags'      => '',
		'faq_order'     => '2',
		'faq_info_id'   => '2',
		'faq_info_title'  => 'Misc',
		'faq_info_about'  => 'Other FAQs',
		'faq_info_parent' => '0',
		'faq_info_class'  => '0',
		'faq_info_order'  => '1',
		'faq_info_icon'   => '',
		'faq_info_metad'  => 'description',
		'faq_info_metak'  => 'keyword1,keyword2',
		'faq_info_sef'    => 'misc'

		);

		$sc->__construct();
		$sc->setVars($vars);

        $this->processShortcodeMethods($sc);

    }

	/**
	 * @see https://github.com/e107inc/e107/issues/4512
	 * @throws Exception
	 */
	public function testFaqShortcodesDisplayFaqTotal()
	{
		require_once(e_PLUGIN."faqs/faqs_shortcodes.php");

		/** @var faqs_shortcodes $sc */
		$sc = $this->make('faqs_shortcodes');

		$faqsConfig = e107::getPlugConfig("faqs");
		$beforePref = $faqsConfig->getPref("display_total");
		try
		{
			$faqsConfig->setPref("display_total", true);
			$sc->counter = $counter = 593407;

			$output = e107::getParser()->parseTemplate("<small>{FAQ_COUNT}</small>", true, $sc);
			$this->assertEquals("<small><span class='faq-total'>(".($counter-1).")</span></small>", $output);
		}
		finally
		{
			$faqsConfig->setPref("display_total", $beforePref);
		}
	}

	/**
	 * @see https://github.com/e107inc/e107/issues/4512
	 * @throws Exception
	 */
	public function testFaqShortcodesDoNotDisplayFaqTotal()
	{
		require_once(e_PLUGIN . "faqs/faqs_shortcodes.php");

		/** @var faqs_shortcodes $sc */
		$sc = $this->make('faqs_shortcodes');

		$faqsConfig = e107::getPlugConfig("faqs");
		$beforePref = $faqsConfig->getPref("display_total");
		try
		{
			$faqsConfig->setPref("display_total", false);
			$sc->counter = 1017703;

			$output = e107::getParser()->parseTemplate("<small>{FAQ_COUNT}</small>", true, $sc);
			$this->assertEquals("<small></small>", $output);
		}
		finally
		{
			$faqsConfig->setPref("display_total", $beforePref);
		}
	}

	public function testFpwShortcodes()
	{
		require_once(e_CORE."shortcodes/batch/fpw_shortcodes.php");

        try
		{
			$sc = $this->make('fpw_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();

        $this->processShortcodeMethods($sc);


	}



    public function testForumShortcodes()
    {
        require_once(e_PLUGIN."forum/shortcodes/batch/forum_shortcodes.php");

        try
		{
			/** @var forum_shortcodes $sc */
			$sc = $this->make('forum_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
			'forum_id'                 => '2',
			'forum_name'               => 'Parent Number Two',
			'forum_description'        => 'Forum Description',
			'forum_parent'             => '0',
			'forum_sub'                => '0',
			'forum_datestamp'          => '1367304545',
			'forum_moderators'         => '248',
			'forum_threads'            => '0',
			'forum_replies'            => '0',
			'forum_lastpost_user'      => '0',
			'forum_lastpost_user_anon' => NULL,
			'forum_lastpost_info'      => '',
			'forum_class'              => '253',
			'forum_order'              => '300',
			'forum_postclass'          => '253',
			'forum_threadclass'        => '0',
			'forum_options'            => '',
			'forum_sef'                => 'parent-number-two',
			'forum_image'              => NULL,
			'forum_icon'               => NULL

		);

		$sc->__construct();

		$sc->setVars($vars);

		$exclude = array('sc_info'); // uses time with seconds.

        $this->processShortcodeMethods($sc);

    }

      public function testForumPostShortcodes()
    {
        require_once(e_PLUGIN."forum/shortcodes/batch/post_shortcodes.php");

        try
		{
			/** @var plugin_forum_post_shortcodes $sc */
			$sc = $this->make('plugin_forum_post_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
			'forum_id'                 => '2',
			'forum_name'               => 'Parent Number Two',
			'forum_description'        => 'Forum Description',
			'forum_parent'             => '0',
			'forum_sub'                => '0',
			'forum_datestamp'          => '1367304545',
			'forum_moderators'         => '248',
			'forum_threads'            => '0',
			'forum_replies'            => '0',
			'forum_lastpost_user'      => '0',
			'forum_lastpost_user_anon' => NULL,
			'forum_lastpost_info'      => '',
			'forum_class'              => '253',
			'forum_order'              => '300',
			'forum_postclass'          => '253',
			'forum_threadclass'        => '0',
			'forum_options'            => '',
			'forum_sef'                => 'parent-number-two',
			'forum_image'              => NULL,
			'forum_icon'               => NULL,
			'thread_id' => '1',
			'thread_name' => '3 Duis tempus enim vitae magna placerat vel dapibus tellus feugiat.',
			'thread_forum_id' => '4',
			'thread_views' => '53',
			'thread_active' => '1',
			'thread_lastpost' => '1434584999',
			'thread_sticky' => '0',
			'thread_datestamp' => '1367307189',
			'thread_user' => '2',
			'thread_user_anon' => NULL,
			'thread_lastuser' => '1',
			'thread_lastuser_anon' => NULL,
			'thread_total_replies' => '7',
			'thread_options' => NULL,
			'post_id' => '1',
			'post_entry' => '4 Morbi eleifend auctor quam, ac consequat ipsum dictum vitae. Curabitur egestas lacinia mi, in venenatis mi euismod eu.',
			'post_thread' => '1',
			'post_forum' => '4',
			'post_status' => '0',
			'post_datestamp' => '1367307189',
			'post_user' => '2',
			'post_edit_datestamp' => NULL,
			'post_edit_user' => NULL,
			'post_ip' => NULL,
			'post_user_anon' => NULL,
			'post_attachments' => NULL,
			'post_options' => NULL


		);

		$sc->__construct();

		$sc->setVars($vars);

        $this->processShortcodeMethods($sc);

    }


      public function testForumViewShortcodes()
    {
        require_once(e_PLUGIN."forum/shortcodes/batch/view_shortcodes.php");

        try
		{
			/** @var plugin_forum_view_shortcodes $sc */
			$sc = $this->make('plugin_forum_view_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
			'forum_id'                 => '2',
			'forum_name'               => 'Parent Number Two',
			'forum_description'        => 'Forum Description',
			'forum_parent'             => '0',
			'forum_sub'                => '0',
			'forum_datestamp'          => '1367304545',
			'forum_moderators'         => '248',
			'forum_threads'            => '0',
			'forum_replies'            => '0',
			'forum_lastpost_user'      => '0',
			'forum_lastpost_user_anon' => NULL,
			'forum_lastpost_info'      => '',
			'forum_class'              => '253',
			'forum_order'              => '300',
			'forum_postclass'          => '253',
			'forum_threadclass'        => '0',
			'forum_options'            => '',
			'forum_sef'                => 'parent-number-two',
			'forum_image'              => NULL,
			'forum_icon'               => NULL,
			'thread_id' => '1',
			'thread_name' => '3 Duis tempus enim vitae magna placerat vel dapibus tellus feugiat.',
			'thread_forum_id' => '4',
			'thread_views' => '53',
			'thread_active' => '1',
			'thread_lastpost' => '1434584999',
			'thread_sticky' => '0',
			'thread_datestamp' => '1367307189',
			'thread_user' => '2',
			'thread_user_anon' => NULL,
			'thread_lastuser' => '1',
			'thread_lastuser_anon' => NULL,
			'thread_total_replies' => '7',
			'thread_options' => NULL,
			'post_id' => '1',
			'post_entry' => '4 Morbi eleifend auctor quam, ac consequat ipsum dictum vitae. Curabitur egestas lacinia mi, in venenatis mi euismod eu.',
			'post_thread' => '1',
			'post_forum' => '4',
			'post_status' => '0',
			'post_datestamp' => '1367307189',
			'post_user' => 1,
			'post_edit_datestamp' => NULL,
			'post_edit_user' => NULL,
			'post_ip' => NULL,
			'post_user_anon' => NULL,
			'post_attachments' => NULL,
			'post_options' => NULL,
		//	'user_join'     => time(),
			'user_id'       => 1,
			'user_name'     => USERNAME,
			'user_hideemail'    => 1,
			'user_plugin_forum_posts' => 3,
			'user_visits' => 6,
			'user_admin' => 1,
			'user_join' => time() - 8000,
		);

		$sc->__construct();

		$sc->setVars($vars);
		$sc->setScVar('postInfo', $vars);

        $this->processShortcodeMethods($sc);

    }


    public function testGalleryShortcodes()
    {
        require_once(e_PLUGIN."gallery/e_shortcode.php");

        try
		{
			/** @var gallery_shortcodes $sc */
			$sc = $this->make('gallery_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars = array(
			'media_id'           => '227',
			'media_type'         => 'image/jpeg',
			'media_name'         => 'gasmask.jpg',
			'media_caption'      => 'gasmask.jpg',
			'media_description'  => '',
			'media_category'     => '_common_image',
			'media_datestamp'    => '1464646050',
			'media_author'       => '1',
			'media_url'          => '{e_THEME}voux/install/gasmask.jpg',
			'media_size'         => '91054',
			'media_dimensions'   => '1200 x 830',
			'media_userclass'    => '0',
			'media_usedby'       => '',
			'media_tags'         => '',
			'media_cat_id'       => '1',
			'media_cat_owner'    => '_common',
			'media_cat_category' => '_common_image',
			'media_cat_title'    => '(Common Images)',
			'media_cat_sef'      => '',
			'media_cat_diz'      => 'Media in this category will be available in all areas of admin.',
			'media_cat_class'    => '253',
			'media_cat_image'    => '',
			'media_cat_order'    => '0'
		);


		$sc->__construct();
		$sc->setVars($vars);
		$exclude = array('sc_gallery_slides'); // uses a counter.
        $this->processShortcodeMethods($sc, null, $exclude);

    }


    public function testHeroShortcodes()
    {
        require_once(e_PLUGIN."hero/hero_shortcodes.php");

        try
		{
			/** @var plugin_hero_hero_shortcodes $sc */
			$sc = $this->make('plugin_hero_hero_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$vars =  array(
				'hero_id' => '1',
				'hero_title' => 'A [powerful] &amp; [free] hero area',
				'hero_description' => '[Easy] to Use',
				'hero_bg' => '{e_MEDIA_IMAGE}2020-12/4.sm.webp',
				'hero_media' => '{e_MEDIA_IMAGE}2020-12/horse.jpg',
				'hero_bullets' => e107::unserialize('[
		    {
		        "icon": "fa-sun-o.glyph",
		        "icon_style": "warning",
		        "text": "Add some bullet text",
		        "animation": "fadeInRight",
		        "animation_delay": "15"
		    },
		    {
		        "icon": "fa-font-awesome.glyph",
		        "icon_style": "success",
		        "text": "Select an Icon from FontAwesome or others",
		        "animation": "fadeInRight",
		        "animation_delay": "25"
		    },
		    {
		        "icon": "fa-adjust.glyph",
		        "icon_style": "danger",
		        "text": "Choose a Style from Bootstrap",
		        "animation": "fadeInRight",
		        "animation_delay": "35"
		    },
		    {
		        "icon": "",
		        "icon_style": "",
		        "text": "",
		        "animation": "",
		        "animation_delay": "0"
		    },
		    {
		        "icon": "",
		        "icon_style": "",
		        "text": "",
		        "animation": "",
		        "animation_delay": "0"
		    }
		]'),
		'hero_button1' => e107::unserialize('{
		    "icon": "fa-",
		    "label": "",
		    "url": "",
		    "class": ""
		}'),
		'hero_button2' => e107::unserialize('{
		    "icon": "fa-",
		    "label": "",
		    "url": "",
		    "class": ""
		}'),
		'hero_order' => '1',
		'hero_class' => '0'
		);


		$sc->__construct();
		$sc->setVars($vars);
	//	$exclude = array('sc_gallery_slides'); // uses a counter.
        $this->processShortcodeMethods($sc);

    }


    public function testLoginMenuShortcodes()
    {
        require_once(e_PLUGIN."login_menu/login_menu_shortcodes.php");

        try
		{
			/** @var login_menu_shortcodes $sc */
			$sc = $this->make('login_menu_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();

        $this->processShortcodeMethods($sc);

    }

     public function testOnlineShortcodes()
    {
        require_once(e_PLUGIN."online/online_shortcodes.php");

        try
		{
			/** @var online_shortcodes $sc */
			$sc = $this->make('online_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();

        $this->processShortcodeMethods($sc);

    }

    public function testPMShortcodes()
    {
        require_once(e_PLUGIN."pm/pm_shortcodes.php");

        try
		{
			/** @var plugin_pm_pm_shortcodes $sc */
			$sc = $this->make('plugin_pm_pm_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();

		$vars = array(
			'pm_id' => 5,
			'pm_sent' => time(),
			'pm_read' => 0,
			'pm_from' => 1,
			'from_name' => 'admin',
			'pm_to' => 1,
			'pm_block_datestamp' => time(),
			'pm_block_from'=> 2,

		);

		$sc->setVars($vars);

        $this->processShortcodeMethods($sc);

    }


	public function testRSSShortcodes()
    {
        require_once(e_PLUGIN."rss_menu/rss_shortcodes.php");

        try
		{
			/** @var rss_menu_shortcodes $sc */
			$sc = $this->make('rss_menu_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();

		$vars =  array(
			'rss_id'        => '1',
			'rss_name'      => 'News',
			'rss_url'       => 'news',
			'rss_topicid'   => '0',
			'rss_path'      => '0',
			'rss_text'      => 'The rss feed of the news',
			'rss_datestamp' => '1456448477',
			'rss_class'     => '0',
			'rss_limit'     => '10',
			// import shortcodes. 
			'name'		    => "Comments",
			'url'           => 'comments',
			'topic_id'      => '',
			'path'		    => 'comments',
			'text'		    => 'the rss feed of comments',
			'class'		    => '0',
			'limit'		    => '9',
		);

		$sc->setVars($vars);

        $this->processShortcodeMethods($sc);

    }



	public function testSigninShortcodes()
    {
        require_once(e_PLUGIN."signin/signin_shortcodes.php");

        try
		{
			/** @var plugin_signin_signin_shortcodes $sc */
			$sc = $this->make('plugin_signin_signin_shortcodes');
		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();

        $this->processShortcodeMethods($sc);

    }

    public function testListShortcodes()
    {
        require_once(e_PLUGIN."list_new/list_shortcodes.php");
        require_once(e_PLUGIN."list_new/list_class.php");

        try
		{
			/** @var list_shortcodes $sc */
			$sc = $this->make('list_shortcodes');

		}
		catch (Exception $e)
		{
			$this->fail($e->getMessage());
		}

		$sc->__construct();

		$sc->rc = new listclass;

		$vars = array (
			'caption' => 'My Caption',
			 'display' => '1',
			 'open' => '1',
			 'author' => '',
			 'category' => '1',
			 'date' => '',
			 'icon' => '',
			 'amount' => '1',
			 'order' => '1',
			 'section' => 'news',
        );


		$sc->row = $vars;

		$exclude = array('sc_list_category');  // unknown issue.

        $this->processShortcodeMethods($sc, null, $exclude);

    }




	/*


            e107_plugins\links_page  (1 usage found)
                links_page_shortcodes.php  (1 usage found)
                    1 <?php


	 */


// ------------------------------------------------


    private function processShortcodeMethods($sc, $parms=array(), $exclude=array())
    {
        $sc->wrapper('non-existent/wrapper');

    	$list = get_class_methods($sc);

        foreach($list as $meth)
        {
            if(strpos($meth, 'sc_') !== 0 || in_array($meth, $exclude))
            {
                continue;
            }

			$name = str_replace('sc_', '', $meth);
            $scName = '{'.strtoupper($name).'}';

            $result = e107::getParser()->parseTemplate($scName, true, $sc);
            $expected = $sc->$meth();

            $this->assertEquals($expected,$result, $scName.' != '.$meth.'()');

            if(!empty($parms[$name]))
            {
            	foreach($parms[$name] as $parm=>$expect)
                {
                    $scWithParm = str_replace('}', $parm.'}', $scName);
                    $actual = e107::getParser()->parseTemplate($scWithParm, true, $sc);
                    $this->assertEquals($expect, $actual);
                }
            }


        }



    }

	/**
	 * Execute all single shortcodes to check for PHP errors.
	 */
    public function testSingleShortcodes()
    {
        $list = scandir(e_CORE."shortcodes/single");
        $tp = e107::getParser();

        $parms = array(
            'email'         => 'myemail@somewhere.com-link',
            'emailto'       => '2',
            'email_item'    => 'Some Message^plugin:forum.45',
            'glyph'         => 'fa-anchor',
        	'url'           => 'news/view/item|news_id=1&news_sef=sef-string&category_id=1&category_sef=category-sef&options[full]=1',
            'user_extended' => 'name.text.1',
            'lan'           => 'LAN_EDIT',
            'search'        => 'all',
            'sitelinks_alt' => '/e107_themes/bootstrap3/images/logo.webp+noclick',
        );

		foreach($list as $sc)
		{
			$ext = pathinfo($sc);
			$name = $ext['filename'];

			if($ext['extension'] !== 'sc' && $ext['extension'] !== 'php')
			{
				continue;
			}

			$shortcode = '{';
			$shortcode .= strtoupper($name);
			$shortcode .= isset($parms[$name]) ? '='.$parms[$name] : '';
			$shortcode .= '}';
		//	echo "\n".$shortcode."\n";
			$result = $tp->parseTemplate($shortcode,true);

			if(isset($parms[$name]) && $name !== 'user_extended')
			{
				$this->assertNotEmpty($result, $shortcode." returned nothing!");
			}

		}

    }

    public function testGlyphShortcode()
    {
         $tp = e107::getParser();
         $result = $tp->parseTemplate('{GLYPH=fa-user}');
         $this->assertSame("<i class='fas fa-user' ></i>", $result);

    }


    public function testAddonShortcodes()
    {
        $vars = array(
            'gallery'   => array(
                'media_caption'     => 'caption',
				'media_url'         => '{e_IMAGE}logo.png',
				'media_description' => 'diz',
				'media_cat_title'   => 'category',
				'media_cat_diz'     => 'category description',
				'media_cat_image'   => '',
				'media_cat_sef'     => 'gallery-cat-sef',
            ),
			'pm'=> array(
				'pm_id' => 5,
				'pm_sent' => time(),
				'pm_read' => 0,
				'pm_from' => 1,
				'from_name' => 'admin',
				'pm_to' => 1,
				'pm_block_datestamp' => time(),
				'pm_block_from'=> 2,
				'pm_class'  => '0',
			),





        );





        $list = e107::getPlug()->getCorePluginList();

        foreach($list as $plug)
        {
            $path = e_PLUGIN.$plug."/e_shortcode.php";

            if(!file_exists($path) || $plug ==='page' || $plug === 'news') // news/page have their own test for this.
            {
                continue;
            }

			require_once($path);

			try
			{
				/** @var e_shortcode $sc */
				$sc = $this->make($plug.'_shortcodes');
			}
			catch (Exception $e)
			{
				$this->fail($e->getMessage());
			}

			$methods = get_class_methods($sc);

			if(empty($methods))
			{
				continue;
			}

			if(isset($vars[$plug]))
			{
				$sc->setVars($vars[$plug]);
			}

			foreach($methods as $meth)
			{
				if(strpos($meth, 'sc_') !== 0)
				{
					continue;
				}

				if(in_array('__construct', $methods))
				{
					$sc->__construct();
				}

				$result = $sc->$meth();
			}







        }







    }


/*
	public function testInitShortcodeClass()
	{

	}

	public function testRegisterShortcode()
	{

	}

	public function testSetScVar()
	{

	}

	public function testCallScFunc()
	{

	}

	public function testIsScClass()
	{

	}

	public function testParse_scbatch()
	{

	}

	public function testLoadThemeShortcodes()
	{

	}
	*/
}
