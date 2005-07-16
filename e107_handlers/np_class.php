<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/np_class.php,v $
|     $Revision: 1.6 $
|     $Date: 2005-07-16 10:47:37 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/

@include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_np.php");
@include_once(e_LANGUAGEDIR."English/lan_np.php");

/**
 * Next / Previous handling class
 *
 */
class nextprev {
	
	/**
	 * Generate next / previous pages and appropriate links (
	 *
	 * @param string $url, refer url
	 * @param int $from, start figure
	 * @param int $view, items per page
	 * @param int $total, total items
	 * @param string $td, comfort text
	 * @param string $qs, QUERY_STRING, default null
	 * @param bool $return, Output onto page or return the links
	 * @return nextprev string
	 */
	function nextprev($url, $from, $view, $total, $td, $qs = "", $return = false) {
		if ($total > $view) {
			$a = $total / $view;
			$r = explode(".", $a);
			if (isset($r[1]) && $r[1] != 0 ? $pages = ($r[0] + 1) : $pages = $r[0]);
		} else {
			$pages = FALSE;
		}

		if ($pages) {
			$nppage = NP_3." ";
			if ($pages > 10) {
				$current = ($from/$view)+1;

				for($c = 0; $c <= 2; $c++) {
					$nppage .= ($view * $c == $from ? "[<span style='text-decoration:underline'>".($c + 1)."</span>] " : "<a href='{$url}?".($view * $c).($qs ? ".{$qs}" : "")."'>".($c + 1)."</a> ");
				}

				if ($current >= 3 && $current <= 5) {
					for($c = 3; $c <= $current; $c++) {
						$nppage .= ($view * $c == $from ? "[<span style='text-decoration:underline'>".($c+1)."</span>] " : "<a href='{$url}?".($view * $c).($qs ? ".{$qs}" : "")."'>".($c + 1)."</a> ");
					}
				}
				else if($current >= 6 && $current <= ($pages-5)) {
					$nppage .= " ... ";
					for($c = ($current-2); $c <= $current; $c++) {
						$nppage .= ($view * $c == $from ? "[<span style='text-decoration:underline'>".($c+1)."</span>] " : "<a href='{$url}?".($view * $c).($qs ? ".{$qs}" : "")."'>".($c + 1)."</a> ");
					}
				}
				$nppage .= " ... ";


				if (($current + 5) > $pages && $current != $pages) {
					$tmp = ($current-2);
				} else {
					$tmp = $pages-3;
				}

				for($c = $tmp; $c <= ($pages-1); $c++) {
					$nppage .= ($view * $c == $from ? "[<span style='text-decoration:underline'>".($c + 1)."</span>] " : "<a href='{$url}?".($view * $c).($qs ? ".{$qs}" : "")."'>".($c + 1)."</a> ");
				}

			} else {
				for($c = 0; $c < $pages; $c++) {
					if ($view * $c == $from ? $nppage .= "[<span style='text-decoration:underline'>".($c + 1)."</span>] " : $nppage .= "<a href='{$url}?".($view * $c).($qs ? ".{$qs}" : "")."'>".($c + 1)."</a> ");
				}
			}
			$text = "<div style='text-align:right'><div class='nextprev'><span class='smalltext'>{$nppage}</span></div></div>\n<br /><br />\n";
			if($return == true){
				return $text;
			} else {
				echo $text;
				return null;
			}
		}
	}
}

?>