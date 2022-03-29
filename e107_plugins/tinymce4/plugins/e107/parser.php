<?php
/*
 * e107 website system
 *
 * Copyright (C) e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

if(empty($_POST['content']) && empty($_GET['debug']) && !defined('TINYMCE_DEBUG') && !defined('TINYMCE_UNIT_TEST'))
{
	header('Content-Length: 0');
	exit;
}

if(!defined('e_ADMIN_AREA'))
{
	define('e_ADMIN_AREA', true);
}

if(!defined('TINYMCE_DEBUG') && !defined('TINYMCE_UNIT_TEST'))
{
	$_E107['no_online'] = true;
	$_E107['no_menus'] = true;
	$_E107['no_forceuserupdate'] = true;
	$_E107['no_maintenance'] = true;
	$_E107['minimal'] = true;

	require_once(__DIR__."/../../../../class2.php");
}

/**
 * Two Modes supported below going to and from the Tinymce wysiwyg editor.
 * 1) When the post_html pref is active - raw html is used in the editor and wrapped in [html] [/html] bbcodes in the background.
 * 2) When the post_html pref is disabled - bbcodes are used in the background and converted to html for the editor.
 * Tested extensively over 24 hours with Images - check with Cameron first if issues arise.
 * TODO Check with html5 tags active.
*/
class e107TinyMceParser
{
	protected $gzipCompression = false;
	protected $postHtmlClass;

	function __construct()
	{
		$this->postHtmlClass = (int) e107::getPref('post_html', e_UC_NOBODY);

		$mode = isset($_POST['mode']) ? $_POST['mode'] : 'tohtml';
		$_POST['content'] = isset($_POST['content']) ? $_POST['content'] : '';

		$html = '';

		if(defined('TINYMCE_DEBUG') || defined('TINYMCE_UNIT_TEST'))
		{
			$this->gzipCompression = false;
		}

		if(!empty($_GET['debug']) && getperms('0'))
		{
			$debug = true;  // For future use.

			if(defined("TINYMCE_PARSER_DEBUG_TEXT"))
			{
				$text = TINYMCE_PARSER_DEBUG_TEXT;
				echo "<h1>Original</h1>";
				print_a($text);
				echo "<h1>toHtml</h1>";
			}
			else
			{

			$text = <<<TEMPL

	[html][code]Something goes here [b]bold print[/b][/code][/html]

TEMPL;
			}
			$_POST['content'] = $text;
		}
		else
		{
			$debug = false;
		}

		if($mode === 'tohtml')
		{
			$html =  $this->toHTML($_POST['content']);
		}
		elseif($mode === 'tobbcode')
		{
			$html = $this->toDB($_POST['content']);
		}

		if($this->gzipCompression == true)
		{
			header('Content-Encoding: gzip');
			$gzipoutput = gzencode($html,6);
			header('Content-Length: '.strlen($gzipoutput));
			echo $gzipoutput;
		}
		else
		{
			echo $html;
		}

	}

	public function setHtmlClass($value)
	{
		$this->postHtmlClass = (int) $value;
	}


	public function toHTML($content)
	{
		$tp = e107::getParser();
		$fa = e107::getTheme()->getFontAwesome(); // get the frontend theme's fontawesome version.
		$tp->setFontAwesome($fa);

		if(!defined('BOOTSTRAP') && ($bs = e107::getTheme()->getLibVersion('bootstrap')))
		{
			define('BOOTSTRAP', (int) $bs);
		}

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



	function toDB($content)
	{
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

			$content = html_entity_decode($content);
			$text = !empty($content) ? "[html]".$content."[/html]" : ''; // Add the tags before saving to DB.
		}
		else  // User doesn't have HTML access -  bbcode Mode.
		{
			$content = html_entity_decode($content);
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




	/**
	 * Rebuld <img> tags with modified thumbnail size.
	 * @deprecated @see e107::getBB()->imgToBBcode();
	 * @param $text
	 * @return mixed
	 */
/*	function updateImg($text)
	{
		$tp = e107::getParser();
		$arr = $tp->getTags($text,'img');

		$srch = array("?","&");
		$repl = array("\?","&amp;");

		if(defined('TINYMCE_DEBUG'))
		{
			print_a($arr);
		}

		foreach($arr['img'] as $img)
		{
			if(substr($img['src'],0,4) == 'http' || strpos($img['src'], e_IMAGE_ABS.'emotes/')!==false) // dont resize external images or emoticons.
			{
				continue;
			}


			$regexp = '#(<img[^>]*src="'.str_replace($srch, $repl, $img['src']).'"[^>]*>)#';

	//		$width 	= vartrue($img['width']) 	? ' width="'.$img['width'].'"' : '';
	//		$height = vartrue($img['height'])	? ' height="'.$img['height'].'"' : '';
	//		$style 	= vartrue($img['style'])	? ' style="'.$img['style'].'"' : '';
	//		$class 	= vartrue($img['class'])	? ' class="'.$img['class'].'"' : '';
	//		$alt 	= vartrue($img['alt'])		? ' alt="'.$img['alt'].'"' : '';
	//		$title 	= vartrue($img['title'])	? ' title="'.$img['title'].'"' : '';
	//		$srcset = vartrue($img['srcset'])   ? 'srcset="'.$img['srcset'].'"' : '';




			$qr = $tp->thumbUrlDecode($img['src']);

			if(substr($qr['src'],0,4)!=='http' && empty($qr['w']) && empty($qr['aw']))
			{
				$qr['w'] = $img['width'];
				$qr['h'] = $img['height'];
			}

			$qr['ebase'] = true;

		//	$src = e107::getParser()->thumbUrl($qr['src'],$qr);

		//	$replacement = '<img src="'.$src.'" '.$srcset.$style.$alt.$title.$class.$width.$height.' />';

			unset($img['src'],$img['srcset'],$img['@value'], $img['caption'], $img['alt']);

			if(!empty($img['class']))
			{
				$tmp = explode(" ",$img['class']);
				$cls = array();
				foreach($tmp as $v)
				{
					if($v === 'img-rounded' || $v === 'rounded' || $v === 'bbcode' || $v === 'bbcode-img-news' || $v === 'bbcode-img')
					{
						continue;
					}

					$cls[] = $v;

				}

				if(empty($cls))
				{
					unset($img['class']);
				}
				else
				{
					$img['class'] = implode(" ",$cls);
				}

			}

			$parms = !empty($img) ? ' '.str_replace('+', ' ', http_build_query($img,null, '&')) : "";

			$code_text = str_replace($tp->getUrlConstants('raw'), $tp->getUrlConstants('sc'), $qr['src']);

			$replacement = '[img'.$parms.']'.$code_text.'[/img]';

			$text = preg_replace($regexp, $replacement, $text);

		}

		return $text;
	}*/


}

$mce = new	e107TinyMceParser();


