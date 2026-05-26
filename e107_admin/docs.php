<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Docs
 *
 *
*/
require_once(__DIR__ . '/../class2.php');
if(!deftrue('ADMIN'))
{
	e107::redirect();
	exit;
}

e107::coreLan('docs', true);

// e_DOCS already resolves to e107_docs/help/ (see HELP_DIRECTORY in e107_class.php).
define('DOC_PATH', e_DOCS . e_LANGUAGE . '/');
define('DOC_PATH_ALT', e_DOCS . 'English/');
define('DOC_MEDIA_URL', e_DOCS . '_media/');

// Structural-only CSS for .docs-item; colour palette lives per-skin in e107_themes/bootstrap3/css/*.css.
e107::css('inline', '
.docs-item { max-width: 900px; font-size: 14px; line-height: 1.6; }
.docs-item h2.docs-title { margin: 0 0 6px; padding-bottom: 8px; border-bottom-width: 2px; border-bottom-style: solid; font-weight: 600; }
.docs-item h3.docs-section { margin: 28px 0 10px; padding-bottom: 6px; border-bottom-width: 1px; border-bottom-style: solid; font-weight: 600; }
.docs-item h4.docs-subsection { margin: 20px 0 8px; font-weight: 600; }
.docs-item p.lead { font-size: 16px; margin-bottom: 18px; }
.docs-item p { margin: 0 0 12px; }
.docs-item .docs-figure { margin: 18px 0; text-align: center; }
.docs-item .docs-figure img { max-width: 100%; height: auto; }
.docs-item .docs-figure figcaption { margin-top: 8px; font-size: 13px; font-style: italic; }
.docs-item ol.docs-steps { counter-reset: docsstep; list-style: none; padding-left: 0; margin: 14px 0 18px; }
.docs-item ol.docs-steps > li { counter-increment: docsstep; position: relative; padding: 8px 12px 8px 48px; margin-bottom: 8px; border-left-width: 3px; border-left-style: solid; border-radius: 3px; }
.docs-item ol.docs-steps > li:before { content: counter(docsstep); position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 28px; height: 28px; line-height: 28px; text-align: center; border-radius: 50%; font-weight: 700; font-size: 13px; }
.docs-item .alert { margin: 14px 0; }
.docs-item .alert > .fa { margin-right: 8px; }
.docs-item pre.docs-code { padding: 12px 14px; border-radius: 4px; overflow-x: auto; font-size: 13px; }
.docs-item .panel-group.docs-faq { margin-top: 14px; }
.docs-item .panel-group.docs-faq .panel-heading { cursor: pointer; }
.docs-item .panel-group.docs-faq .panel-title { font-size: 14px; font-weight: 600; }
.docs-item .panel-group.docs-faq .panel-title > .fa { margin-right: 8px; }
.docs-item .panel-group.docs-faq .panel-title .docs-faq-toggle { float: right; font-size: 12px; transition: transform .2s; }
.docs-item .panel-group.docs-faq .panel-heading.collapsed .docs-faq-toggle { transform: rotate(-90deg); }
.docs-item .docs-toc { float: right; width: 220px; margin: 0 0 14px 20px; padding: 12px 14px; border-width: 1px; border-style: solid; border-radius: 4px; font-size: 13px; }
.docs-item .docs-toc-title { font-weight: 700; margin-bottom: 6px; text-transform: uppercase; font-size: 11px; letter-spacing: .5px; }
.docs-item .docs-toc ul { list-style: none; padding-left: 0; margin: 0; }
.docs-item .docs-toc ul ul { padding-left: 14px; margin-top: 4px; }
.docs-item .docs-toc li { margin: 3px 0; }
@media (max-width: 767px) { .docs-item .docs-toc { float: none; width: auto; margin: 0 0 14px; } }
');

e107::js('footer-inline', "
$(function() {
	var \$items = $('.docs-item');
	var \$navLinks = $('#admin-ui-nav-menu a[href^=\"#doc-\"]');

	$(document).on('click', '.docs-toc a', function(e) {
		var href = $(this).attr('href') || '';
		if (href.charAt(0) === '#') {
			var \$t = $(href);
			if (\$t.length) {
				e.preventDefault();
				$('html, body').animate({ scrollTop: \$t.offset().top - 20 }, 300);
			}
		}
	});

	function showDoc(id) {
		var \$target = $(id);
		if (!\$target.length) { return; }
		\$items.stop(true, true).hide();
		\$target.show({ effect: 'slide', duration: 250 });
		\$navLinks.closest('li').removeClass('active');
		\$navLinks.filter('[href=\"' + id + '\"]').closest('li').addClass('active');
	}

	\$navLinks.on('click', function(e) {
		var href = $(this).attr('href') || '';
		if (href.indexOf('#doc-') === 0) {
			e.preventDefault();
			showDoc(href);
		}
	});

	if (window.location.hash && window.location.hash.indexOf('#doc-') === 0) {
		showDoc(window.location.hash);
	}
});
");


class docs_admin extends e_admin_dispatcher
{

	protected $modes = array(

		'main' => array(
			'controller' => 'docs_ui',
			'path'       => null,
			'ui'         => 'docs_form_ui',
			'uipath'     => null
		),


	);

	protected $adminMenu = array();

	protected $adminMenuAliases = array();

	protected $menuTitle = LAN_DOCS;

	protected $adminMenuIcon = 'e-docs-24';

	protected static $helpList = array();

	public static function getDocs()
	{

		return self::$helpList;
	}


	function init()
	{

		$fl = e107::getFile();

		$helplist_all = $fl->get_files(DOC_PATH_ALT);
		if(!is_dir(DOC_PATH) || DOC_PATH == DOC_PATH_ALT)
		{
			$helplist = $helplist_all;
		}
		else
		{
			$helplist = $fl->get_files(DOC_PATH);
		}

		sort($helplist);

		self::$helpList = $helplist;

		foreach($helplist as $key => $helpdata)
		{

			$id = 'doc-' . $key;
			$k = 'main/' . $id;

			$this->adminMenu[$k] = array(
				'caption' => str_replace("_", " ", $helpdata['fname']),
				'perm'    => false,
				'uri'     => "#" . $id,
				'icon'    => 'fa-question-circle',
			);
		}


	}
}


class docs_ui extends e_admin_ui
{

	/**
	 * Build a safe HTML id from a heading's text.
	 */
	private function slug($text, $prefix = 'sec')
	{
		$s = strtolower(strip_tags((string) $text));
		$s = preg_replace('/[^a-z0-9]+/', '-', $s);
		$s = trim($s, '-');
		return $prefix . '-' . ($s !== '' ? $s : substr(md5((string) $text), 0, 6));
	}

	/**
	 * Resolve the path for the SHOT> marker (shared screenshots living under
	 * e107_docs/help/_media/). IMG> accepts arbitrary URLs or paths.
	 */
	private function resolveMedia($src, $shared = false)
	{
		$src = trim($src);
		if($src === '') { return ''; }
		if(preg_match('#^(https?:)?//#i', $src) || $src[0] === '/')
		{
			return $src;
		}
		return $shared ? DOC_MEDIA_URL . ltrim($src, '/') : $src;
	}

	/**
	 * Rich help parser. Keeps the legacy Q>/A> format but also recognises
	 * H1> H2> H3> P> NOTE> TIP> WARN> IMG> SHOT> STEP> CODE> markers, and
	 * allows BBCode in the remaining text via e107::getParser()->toHTML().
	 *
	 * Returns an array with:
	 *   - html : rendered HTML content
	 *   - toc  : array of headings [ [level,id,text], ... ] for the table of contents
	 */
	private function renderDoc($raw, $docIndex)
	{
		$tp = e107::getParser();
		$lines = preg_split("/\r\n|\n|\r/", (string) $raw);

		$out = '';
		$toc = array();
		$seenSlugs = array();
		$paraBuf = array();
		$stepBuf = array();
		$faqOpen = false;
		$faqIndex = 0;
		$qaCount = 0;
		$pendingQ = null;       // ['qid','aid','text'] — Q> awaiting its A>
		$pendingBlock = null;   // ['type','lines'] — multi-line P/NOTE/TIP/WARN/A buffer

		$uniqueSlug = function($text, $prefix) use (&$seenSlugs) {
			$base = $this->slug($text, $prefix);
			$candidate = $base;
			$i = 2;
			while(isset($seenSlugs[$candidate]))
			{
				$candidate = $base . '-' . $i;
				$i++;
			}
			$seenSlugs[$candidate] = true;
			return $candidate;
		};

		$flushPara = function() use (&$paraBuf, &$out, $tp) {
			if(empty($paraBuf)) { return; }
			$text = trim(implode("\n", $paraBuf));
			$paraBuf = array();
			if($text === '') { return; }
			$out .= '<p>' . $tp->toHTML($text, true, 'BODY') . '</p>';
		};

		$flushSteps = function() use (&$stepBuf, &$out, $tp) {
			if(empty($stepBuf)) { return; }
			$items = '';
			foreach($stepBuf as $s)
			{
				$items .= '<li>' . $tp->toHTML($s, true, 'BODY') . '</li>';
			}
			$stepBuf = array();
			$out .= '<ol class="docs-steps">' . $items . '</ol>';
		};

		$openFaq = function() use (&$faqOpen, &$faqIndex, &$out, $docIndex) {
			if(!$faqOpen)
			{
				$faqIndex++;
				$out .= '<div class="panel-group docs-faq" id="docs-faq-' . $docIndex . '-' . $faqIndex . '" role="tablist">';
				$faqOpen = true;
			}
		};

		$closeFaq = function() use (&$faqOpen, &$out) {
			if($faqOpen)
			{
				$out .= '</div>';
				$faqOpen = false;
			}
		};

		$emitPanel = function($qid, $aid, $qText, $aHtml) use (&$out, $tp) {
			$out .= '<div class="panel panel-default">';
			$out .= '<div class="panel-heading collapsed" role="tab" id="' . $qid . '" data-toggle="collapse" data-target="#' . $aid . '" aria-expanded="false" aria-controls="' . $aid . '">';
			$out .= '<h4 class="panel-title"><i class="fa fa-question-circle"></i>' . $tp->toHTML($qText, true, 'BODY') . '<span class="docs-faq-toggle fa fa-chevron-down"></span></h4>';
			$out .= '</div>';
			$out .= '<div id="' . $aid . '" class="panel-collapse collapse" role="tabpanel" aria-labelledby="' . $qid . '"><div class="panel-body">';
			$out .= $aHtml;
			$out .= '</div></div></div>';
		};

		$flushPendingQuestion = function() use (&$pendingQ, $emitPanel) {
			if($pendingQ !== null)
			{
				// Q> with no A> — emit panel with empty body.
				$emitPanel($pendingQ['qid'], $pendingQ['aid'], $pendingQ['text'], '');
				$pendingQ = null;
			}
		};

		$flushPendingBlock = function() use (&$pendingBlock, &$pendingQ, &$out, $tp, $emitPanel) {
			if($pendingBlock === null) { return; }
			$type = $pendingBlock['type'];
			$text = trim(implode("\n", $pendingBlock['lines']));
			$pendingBlock = null;
			if($text === '' && $type !== 'A') { return; }
			$html = $tp->toHTML($text, true, 'BODY');
			switch($type)
			{
				case 'P':
					$out .= '<p class="lead">' . $html . '</p>';
					break;
				case 'NOTE':
					$out .= '<div class="alert alert-info" role="alert"><i class="fa fa-info-circle"></i>' . $html . '</div>';
					break;
				case 'TIP':
					$out .= '<div class="alert alert-success" role="alert"><i class="fa fa-lightbulb-o"></i>' . $html . '</div>';
					break;
				case 'WARN':
					$out .= '<div class="alert alert-warning" role="alert"><i class="fa fa-exclamation-triangle"></i>' . $html . '</div>';
					break;
				case 'A':
					if($pendingQ !== null)
					{
						$emitPanel($pendingQ['qid'], $pendingQ['aid'], $pendingQ['text'], $html);
						$pendingQ = null;
					}
					else
					{
						$out .= '<div class="alert alert-info" role="alert">' . $html . '</div>';
					}
					break;
			}
		};

		foreach($lines as $rawLine)
		{
			$line = rtrim($rawLine);
			$trim = ltrim($line);

			// Blank line = block separator.
			if($trim === '')
			{
				$flushPara();
				$flushPendingBlock();
				$flushSteps();
				continue;
			}

			// Detect marker: TOKEN> rest
			if(preg_match('/^([A-Z][A-Z0-9]{0,6})>\s?(.*)$/', $trim, $m))
			{
				$tag = $m[1];
				$body = $m[2];

				// STEP keeps accumulating; any other marker flushes the step buffer.
				if($tag !== 'STEP') { $flushSteps(); }
				// Plain paragraphs always flush on a marker.
				$flushPara();
				// A new marker always closes the previous multi-line block.
				$flushPendingBlock();
				// Anything other than Q/A also closes any pending question and the FAQ group.
				if($tag !== 'Q' && $tag !== 'A')
				{
					$flushPendingQuestion();
					$closeFaq();
				}

				switch($tag)
				{
					case 'H1':
						$id = $uniqueSlug($body, 'doc' . $docIndex . '-h1');
						$toc[] = array(2, $id, $body);
						$out .= '<h3 class="docs-section" id="' . $id . '">' . $tp->toHTML($body, true, 'TITLE') . '</h3>';
						break;

					case 'H2':
						$id = $uniqueSlug($body, 'doc' . $docIndex . '-h2');
						$toc[] = array(3, $id, $body);
						$out .= '<h4 class="docs-subsection" id="' . $id . '">' . $tp->toHTML($body, true, 'TITLE') . '</h4>';
						break;

					case 'P':
					case 'NOTE':
					case 'TIP':
					case 'WARN':
					case 'A':
						$pendingBlock = array('type' => $tag, 'lines' => array($body));
						break;

					case 'IMG':
					case 'SHOT':
						$parts = explode('|', $body, 2);
						$src = $this->resolveMedia($parts[0], ($tag === 'SHOT'));
						if($src === '') { break; }
						$caption = isset($parts[1]) ? trim($parts[1]) : '';
						$alt = $caption !== '' ? htmlspecialchars($caption, ENT_QUOTES) : '';
						$out .= '<figure class="docs-figure"><img src="' . htmlspecialchars($src, ENT_QUOTES) . '" alt="' . $alt . '" class="img-responsive">';
						if($caption !== '')
						{
							$out .= '<figcaption>' . $tp->toHTML($caption, true, 'BODY') . '</figcaption>';
						}
						$out .= '</figure>';
						break;

					case 'STEP':
						$stepBuf[] = $body;
						break;

					case 'CODE':
						$out .= '<pre class="docs-code"><code>' . htmlspecialchars($body, ENT_QUOTES) . '</code></pre>';
						break;

					case 'Q':
						// Two Q> in a row: emit the previous one with empty body.
						$flushPendingQuestion();
						$openFaq();
						$qaCount++;
						$pendingQ = array(
							'qid'  => 'docs-q-' . $docIndex . '-' . $qaCount,
							'aid'  => 'docs-a-' . $docIndex . '-' . $qaCount,
							'text' => $body,
						);
						break;

					default:
						// Unknown token: treat the whole line as plain text.
						$paraBuf[] = $trim;
				}
				continue;
			}

			// Plain line: feed the most specific open buffer.
			if($pendingBlock !== null)
			{
				$pendingBlock['lines'][] = $trim;
			}
			elseif($pendingQ !== null)
			{
				// Continuation of a Q> question (rare but kept for legacy compatibility).
				$pendingQ['text'] .= "\n" . $trim;
			}
			else
			{
				$paraBuf[] = $trim;
			}
		}

		$flushPara();
		$flushPendingBlock();
		$flushSteps();
		$flushPendingQuestion();
		$closeFaq();

		return array('html' => $out, 'toc' => $toc);
	}

	/**
	 * Build the internal table of contents (TOC) from the collected H1>/H2> headings.
	 * Nested lists are placed inside the preceding <li> so the markup is valid HTML.
	 */
	private function buildToc($toc)
	{
		if(empty($toc)) { return ''; }
		$out = '<aside class="docs-toc hidden-print"><div class="docs-toc-title"><i class="fa fa-list-ul"></i> ' . LAN_DOCS_SECTIONS . '</div><ul>';
		$depth = 1;       // number of currently open <ul>
		$itemOpen = false; // is the most recent <li> still open?
		$prevLevel = null;
		foreach($toc as $h)
		{
			list($level, $id, $text) = $h;
			if($itemOpen)
			{
				if($level > $prevLevel)
				{
					// Deeper: open a nested <ul> inside the still-open <li>.
					$out .= '<ul>';
					$depth++;
				}
				else
				{
					// Same or shallower: close the current <li>…
					$out .= '</li>';
					// …then close one ul/li per level we are stepping back out of.
					while($level < $prevLevel && $depth > 1)
					{
						$out .= '</ul></li>';
						$depth--;
						$prevLevel--;
					}
				}
			}
			$out .= '<li><a href="#' . $id . '">' . htmlspecialchars(strip_tags($text), ENT_QUOTES) . '</a>';
			$itemOpen = true;
			$prevLevel = $level;
		}
		if($itemOpen) { $out .= '</li>'; }
		while($depth > 1) { $out .= '</ul></li>'; $depth--; }
		return $out . '</ul></aside>';
	}

	public function Doc0Page()
	{
		$helplist = docs_admin::getDocs();
		$text = '';

		foreach($helplist as $key => $helpdata)
		{
			$filename = DOC_PATH . $helpdata['fname'];
			$filename_alt = DOC_PATH_ALT . vartrue($helpdata['fname']);

			$raw = is_readable($filename) ? file_get_contents($filename) : file_get_contents($filename_alt);

			$rendered = $this->renderDoc($raw, $key);
			$toc = $this->buildToc($rendered['toc']);

			$id = 'doc-' . $key;
			$display = ($key === 0) ? '' : "style='display:none'";
			$title = htmlspecialchars(str_replace('_', ' ', $helpdata['fname']), ENT_QUOTES);

			$text .= "
				<div class='docs-item' id='{$id}' {$display}>
					{$toc}
					<h2 class='docs-title'>" . LAN_DOCS . SEP . $title . "</h2>
					{$rendered['html']}
					<div class='clearfix'></div>
				</div>";
		}

		return $text;
	}


}


class docs_form_ui extends e_admin_form_ui
{

}


new docs_admin();

require_once(e_ADMIN . "auth.php");

$data = e107::getAdminUI()->runPage('raw');

echo $data[1]; // just to remove the title.

require_once(e_ADMIN . "footer.php");
