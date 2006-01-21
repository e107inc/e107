<?php
/*
+---------------------------------------------------------------+
| e107 website system
| /classes/news_class.php
|
| �Steve Dunstan 2001-2002
| http://jalist.com
| stevedunstan@jalist.com
|
| Released under the terms and conditions of the
| GNU General Public License (http://gnu.org).
|
| $Source: /cvs_backup/e107_0.7/e107_handlers/news_class.php,v $
| $Revision: 1.69.2.1 $
| $Date: 2006-01-21 22:33:56 $
| $Author: streaky $
+---------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

class news {

	function render_newsitem($news, $mode = 'default', $n_restrict = '', $NEWS_TEMPLATE = '', $param='') {
		
		//print_a($news);
		
		global $tp, $sql, $override, $pref, $ns, $NEWSSTYLE, $NEWSLISTSTYLE, $news_shortcodes;
		if ($override_newsitem = $override -> override_check('render_newsitem')) {
			$result = call_user_func($override_newsitem, $news, $mode, $n_restrict, $NEWS_TEMPLATE, $param);
			if ($result == 'return') {
				return;
			}
		}
		if (!is_object($tp)) {
			$tp = new e_parse;
		}

		if (!$param) {
			if (!defined("IMAGE_nonew_small")){
				define("IMAGE_nonew_small", (file_exists(THEME."generic/nonew_comments.png") ? "<img src='".THEME_ABS."generic/nonew_comments.png' alt=''  /> " : "<img src='".e_IMAGE_ABS."generic/".IMODE."/nonew_comments.png' alt=''  />"));
			}
			if (!defined("IMAGE_new_small"))	{
				define("IMAGE_new_small", (file_exists(THEME."generic/new_comments.png") ? "<img src='".THEME_ABS."generic/new_comments.png' alt=''  /> " : "<img src='".e_IMAGE_ABS."generic/".IMODE."/new_comments.png' alt=''  /> "));
			}
			if (!defined("IMAGE_sticky")){
				define("IMAGE_sticky", (file_exists(THEME."images/sticky.png") ? "<img src='".THEME_ABS."images/sticky.png' alt=''  /> " : "<img src='".e_IMAGE_ABS."generic/".IMODE."/sticky.png' alt='' style='width: 14px; height: 14px; vertical-align: bottom' /> "));
			}

			$param['image_nonew_small'] = IMAGE_nonew_small;
			$param['image_new_small'] = IMAGE_new_small;
			$param['image_sticky'] = IMAGE_sticky;
			$param['caticon'] = ICONSTYLE;
			$param['commentoffstring'] = COMMENTOFFSTRING;
			$param['commentlink'] = COMMENTLINK;
			$param['trackbackstring'] = (defined("TRACKBACKSTRING") ? TRACKBACKSTRING : "");
			$param['trackbackbeforestring'] = (defined("TRACKBACKBEFORESTRING") ? TRACKBACKBEFORESTRING : "");
			$param['trackbackafterstring'] = (defined("TRACKBACKAFTERSTRING") ? TRACKBACKAFTERSTRING : "");
		}

		cachevars('current_news_item', $news);
		cachevars('current_news_param', $param);

		if ($NEWS_TEMPLATE) {
			$NEWS_PARSE = $NEWS_TEMPLATE;
		} else {
			if (function_exists("news_style")) {
				$NEWS_PARSE = news_style($news);
			} else {
				$NEWS_PARSE = $NEWSSTYLE;
			}
		}

		require_once(e_FILE.'shortcode/batch/news_shortcodes.php');
		$text = $tp -> parseTemplate($NEWS_PARSE, FALSE, $news_shortcodes);

		if ($mode == 'return') {
			return "<div id='news_item_{$news['news_id']}'>{$text}</div>";
		} else {
			echo "<div id='news_item_{$news['news_id']}'>{$text}</div>";
			return TRUE;
		}
	}
}

?>