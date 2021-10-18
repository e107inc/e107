<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2021 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

/**
 * Two Modes supported below going to and from the Tinymce wysiwyg editor.
 * 1) When the post_html pref is active - raw html is used in the editor and wrapped in [html] [/html] bbcodes in the background.
 * 2) When the post_html pref is disabled - bbcodes are used in the background and converted to html for the editor.
 * Tested extensively over 24 hours with Images - check with Cameron first if issues arise.
 * TODO Check with html5 tags active.
 */
class tinymce4_parse
{
	protected $postHtmlClass;

	function __construct()
	{
		$this->postHtmlClass = (int) e107::getPref('post_html', e_UC_NOBODY);
	}

	public function setHtmlClass($value)
	{
		$this->postHtmlClass = (int) $value;
	}

	/**
	 * Process a string before it is sent to the browser as WYSIWYG.
	 * @param string $content html/text to be processed.
	 * @return string
	 */
	public function toWYSIWYG($content)
	{
		$tp = e107::getParser();
		$fa = e107::getTheme()->getFontAwesome(); // get the frontend theme's fontawesome version.
		$tp->setFontAwesome($fa);

		$content = stripslashes($content);

		//	$content = e107::getBB()->htmltoBBcode($content);	//XXX This breaks inserted images from media-manager. :/
		e107::getBB()->setClass($this->getMediaCategory());

		if(check_class($this->postHtmlClass)) // raw HTML within [html] tags.
		{
			if(strpos($content,"[html]") === false) // BC - convert old BB code text to html.
			{
				e107::getBB()->clearClass();

				if($tp->isHtml($content) === false) // plain text or BBcode to HTML
				{
					$content =  nl2br($content, true);
				}

				$content = $tp->toHTML($content, true, 'WYSIWYG');
			}

			$content 		= str_replace("{e_BASE}",e_HTTP,$content); // We want {e_BASE} in the final data going to the DB, but not the editor.
			$srch 			= array("<!-- bbcode-html-start -->","<!-- bbcode-html-end -->","[html]","[/html]");
			$content 		= str_replace($srch,"",$content);
			$content 		= $tp->parseBBTags($content,true); // parse the <bbcode> tag so we see the HTML equivalent while editing!
			$content 		= e107::getBB()->parseBBCodes($content);

			$text = $content;

		}
		else  // bbcode Mode.
		{

			// XXX @Cam this breaks new lines, currently we use \n instead [br]
			//echo $tp->toHTML(str_replace("\n","",$content), true);

			$content = str_replace("{e_BASE}",e_HTTP, $content); // We want {e_BASE} in the final data going to the DB, but not the editor.
			$content = $tp->toHTML($content, true, 'WYSIWYG');
			$content = str_replace(e_MEDIA_IMAGE,"{e_MEDIA_IMAGE}",$content);

			$text = "";

			$text .= $content;
		}

		e107::getBB()->clearClass();
		return $text;

	}

	/**
	 * Process a string before it is saved to the database.
	 * @param string $content html/text to be processed.
	 * @param array $kwargs nostrip, noencode etc.
	 * @return string
	 */
	public function toDB($content, $kwargs = [])
	{
		if (!isset($kwargs['field'])) return $content;
		if (!isset($_POST["__meta_type_{$kwargs['field']}"])) return $content;
		if ($_POST["__meta_type_{$kwargs['field']}"] !== 'bbarea') return $content;

		$content = html_entity_decode($content);
		e107::getBB()->setClass($this->getMediaCategory());

		$content = stripslashes($content);

		if(check_class($this->postHtmlClass)) // Plain HTML mode.
		{
			$content = trim($content);
			$content = e107::getBB()->imgToBBcode($content);

			if(strip_tags($content, '<i>') == '&nbsp;') // Avoid this: [html]<p>&nbsp;</p>[/html]
			{
				exit;
			}

			$text = !empty($content) ? "[html]".$content."[/html]" : ''; // Add the tags before saving to DB.
		}
		else  // User doesn't have HTML access -  bbcode Mode.
		{
			$text = e107::getBB()->htmltoBBcode($content);   // not reliable enough yet.
		}

		$text = str_replace('[html]<p></p>[/html]','',$text); // cleanup.

		e107::getBB()->clearClass();
		return $text;

	}

	/**
	 * @return mixed|null
	 */
	private function getMediaCategory()
	{
		return isset($_SESSION['media_category']) ? $_SESSION['media_category'] : null;
	}
}