<?php
/*
 * e107 website system
 *
 * Copyright (C) e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */


if(empty($_POST['content']) && empty($_GET['debug']))
{
	header('Content-Length: 0');
	exit;
}

$_E107['no_online'] = true;
$_E107['no_menus'] = true;
$_E107['no_forceuserupdate'] = true;
require_once("../../../../class2.php");

/**
 * Two Modes supported below going to and from the Tinymce wysiwyg editor.
 * 1) When the post_html pref is active - raw html is used in the editor and wrapped in [html] [/html] bbcodes in the background.
 * 2) When the post_html pref is disabled - bbcodes are used in the background and converted to html for the editor.
 * Tested extensively over 24 hours with Images - check with Cameron first if issues arise.
 * TODO Check with html5 tags active.
*/
class e107TinyMceParser
{

	protected $gzipCompression = true;

	/**
	 *
	 */
	function __construct()
	{
		$html = '';

		if(!empty($_GET['debug']) && getperms('0'))
		{
			$debug = true;  // For future use.
			$text = <<<TEMPL

	[html][code]Something goes here [b]bold print[/b][/code][/html]

TEMPL;
			$_POST['content'] = $text;
			$_POST['mode'] = 'tohtml';
		}
		else
		{
			$debug = false;
		}

		if($_POST['mode'] == 'tohtml')
		{
			$html =  $this->toHtml($_POST['content']);
		}

		if($_POST['mode'] == 'tobbcode')
		{
			$html = $this->toBBcode($_POST['content']);
		}

		if($debug == true)
		{
			print_a($html);
			echo "<hr />";
			echo $html;
		}
		elseif($this->gzipCompression == true)
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



	function toHtml($content)
	{
		global $pref, $tp; //XXX faster?
		// XXX @Cam possible fix - convert to BB first, see news admin AJAX request/response values for reference why
		$content = stripslashes($content);

		//	$content = e107::getBB()->htmltoBBcode($content);	//XXX This breaks inserted images from media-manager. :/
		e107::getBB()->setClass($_SESSION['media_category']);

		if(check_class($pref['post_html'])) // raw HTML within [html] tags.
		{

			//	$content = $tp->replaceConstants($content,'abs');

			if(strstr($content,"[html]") === false) // BC - convert old BB code text to html.
			{
				e107::getBB()->clearClass();

				$content = str_replace('\r\n',"<br />",$content);
				$content =  nl2br($content, true);
				$content = $tp->toHtml($content, true);
			}

			$content 		= str_replace("{e_BASE}",e_HTTP,$content); // We want {e_BASE} in the final data going to the DB, but not the editor.
			$srch 			= array("<!-- bbcode-html-start -->","<!-- bbcode-html-end -->","[html]","[/html]");
			$content 		= str_replace($srch,"",$content);
			$content 		= e107::getBB()->parseBBCodes($content); // parse the <bbcode> tag so we see the HTML equivalent while editing!

			if(!empty($content) && E107_DEBUG_LEVEL > 0)
			{
		//		$content =  "-- DEBUG MODE ACTIVE -- \n".$content;
				//	echo htmlentities($content)."\n";
				//	echo "<pre>".$content."</pre>";
				$text = $content;
				return $text;
				// exit;
			}
			else
			{
				$text = $content;
			}



		}
		else  // bbcode Mode.
		{

			// XXX @Cam this breaks new lines, currently we use \n instead [br]
			//echo $tp->toHtml(str_replace("\n","",$content), true);

			$content = str_replace("{e_BASE}",e_HTTP, $content); // We want {e_BASE} in the final data going to the DB, but not the editor.
			$content = $tp->toHtml($content, true);
			$content = str_replace(e_MEDIA_IMAGE,"{e_MEDIA_IMAGE}",$content);

			$text = "";
			if(!empty($content) && E107_DEBUG_LEVEL > 0)
			{
				$text .= "<!-- bbcode mode -->";
				//print_r(htmlentities($content))."\n";
				//exit;
			}

			$text .= $content;
		}

		e107::getBB()->clearClass();
		return $text;

	}



	function toBBcode($content)
	{
		// echo $_POST['content'];
		global $pref, $tp;
		$content = stripslashes($content);

		if(check_class($pref['post_html'])) // Plain HTML mode.
		{
			$srch 		= array('src="'.e_HTTP.'thumb.php?','src="/{e_MEDIA_IMAGE}');
			$repl 		= array('src="{e_BASE}thumb.php?','src="{e_BASE}thumb.php?src=e_MEDIA_IMAGE/');
			$content 	= str_replace($srch, $repl, $content);

			// resize the thumbnail to match wysiwyg width/height.

			//    $psrch 		= '/<img[^>]*src="{e_BASE}thumb.php\?src=([\S]*)w=([\d]*)&amp;h=([\d]*)"(.*)width="([\d]*)" height="([\d]*)"/i';
			//    $prepl 		= '<img src="{e_BASE}thumb.php?src=$1w=$5&amp;h=$6"$4width="$5" height="$6" ';

			//	$content 	= preg_replace($psrch, $prepl, $content);
			$content = $this->updateImg($content);
			$content = $tp->parseBBTags($content,true); // replace html with bbcode equivalent

			if(strip_tags($content, '<i>') == '&nbsp;') // Avoid this: [html]<p>&nbsp;</p>[/html]
			{
				exit;
			}

			$text = $content ? "[html]".$content."[/html]" : ""; // Add the tags before saving to DB.
		}
		else  // bbcode Mode. //XXX Disabled at the moment in tinymce/e_meta.php - post_html is required to activate.
		{
			//   [img width=400]/e107_2.0/thumb.php?src={e_MEDIA_IMAGE}2012-12/e107org_white_stripe.png&w=400&h=0[/img]
			// $content = str_replace("{e_BASE}","", $content); // We want {e_BASE} in the final data going to the DB, but not the editor.

			$text = e107::getBB()->htmltoBBcode($content);   // not reliable enough yet.
		}

		return $text;

	}


	/**
	 * Rebuld <img> tags with modified thumbnail size.
	 */
	function updateImg($text)
	{

		$arr = e107::getParser()->getTags($text,'img');

		$srch = array("?","&");
		$repl = array("\?","&amp;");

		foreach($arr['img'] as $img)
		{
			$regexp = '#(<img[^>]*src="'.str_replace($srch, $repl, $img['src']).'"[^>]*>)#';

			$width 	= vartrue($img['width']) 	? ' width="'.$img['width'].'"' : '';
			$height = vartrue($img['height'])	? ' height="'.$img['height'].'"' : '';
			$style 	= vartrue($img['style'])	? ' style="'.$img['style'].'"' : '';
			$class 	= vartrue($img['class'])	? ' class="'.$img['class'].'"' : '';
			$alt 	= vartrue($img['alt'])		? ' alt="'.$img['alt'].'"' : '';

			list($url,$qry) = explode("?",$img['src']);

			parse_str($qry,$qr);

			if(substr($url,0,4)!=='http' && empty($qr['w']) && empty($qr['aw']))
			{
				$qr['w'] = $img['width'];
				$qr['h'] = $img['height'];
			}

			$src = $url."?".urldecode(http_build_query($qr));

			$replacement = '<img'.$class.$style.' src="'.$src.'"'.$width.$height.$alt.' />';

			$text = preg_replace($regexp, $replacement, $text);


		}

		return $text;


	}


}

new	e107TinyMceParser();


?>