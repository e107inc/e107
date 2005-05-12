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
|     $Source: /cvs_backup/e107_0.7/page.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-05-12 12:35:48 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
$page = new pageClass();
if(IsSet($_POST['enterpw']))
{
	$page -> setPageCookie();
}
require_once(HEADERF);
if(!e_QUERY)
{
	$page -> listPages();
}
else
{
	$page -> showPage();
}
require_once(FOOTERF);


/* EOF */

class pageClass
{

	var $bullet;																/* bullet image */
	var $pageText;														/* main text of selected page, not parsed */
	var $multipageFlag;												/* flag - true if multiple page page, false if not */
	var $pageTitles;														/* array containing page titles */
	var $pageID;															/* id number of page to be displayed */
	var $pageSelected;												/* selected page of multiple page page */
	var $pageToRender;												/* parsed page to be sent to screen */
	var $debug;															/* temp debug flag */
	var $title;																/* title of page, it if has one (as defined in [newpage=title] tag */


	function pageClass($debug=FALSE)
	{
		/* constructor */
		$this -> bullet = (defined("BULLET") ? "<img src='".THEME."images/".BULLET."' alt='' style='vertical-align: middle;' />" : "<img src='".THEME."images/bullet2.gif' alt='bullet' style='vertical-align: middle;' />");
		$tmp = explode(".", e_QUERY);
		$this -> pageID = intval($tmp[0]);
		$this -> pageSelected = (isset($tmp[1]) ? intval($tmp[1]) : 0);
		$this -> debug = $debug;

		if($this -> debug)
		{
			$this -> debug = "<b>PageID</b> ".$this -> pageID." <br />";
			$this -> debug .= "<b>pageSelected</b> ".$this -> pageSelected." <br />";
		}
	}

	function listPages()
	{
		global $pref, $sql, $ns;

		if(!$pref['listPages'])
		{
			$this -> pageError(1);
		}
		else
		{
			if(!$sql -> db_Select("page", "*", "page_class IN (".USERCLASS_LIST.") "))
			{
				$text = "No custom pages yet.";
			}
			else
			{
				$pageArray = $sql -> db_getList();
				foreach($pageArray as $page)
				{
					extract($page);
					$text .= $this -> bullet." <a href='".e_BASE."page.php?".$page_id."'>".$page_title."</a><br />";
				}
				$ns -> tablerender("Pages", $text);
			}
		}

	}

	function pageError($val)
	{
		global $ns;
		$text = "<div style='text-align:center; margin-left:auto; margin-right: auto;'>
		";
		switch ($val)
		{
			case 1:
				$text .= "No page selected.";
			break;
			case 2:
				$text .= "Invalid page.";
			break;
			case 3:
				$text .= "You do not have the correct permissions to view this page.";
			break;
		}
		$text .= "</div>
		";
		$ns -> tablerender("Error", $text);
	}


	function showPage()
	{
		global $sql, $ns;
		$query = "SELECT p.*, u.user_id, u.user_name FROM #page AS p 
		LEFT JOIN #user AS u ON p.page_author = u.user_id 
		WHERE p.page_id='".$this -> pageID."' AND p.page_class IN (".USERCLASS_LIST.") ";

		if(!$sql -> db_Select_gen($query))
		{
			$this -> pageError(2);
			return FALSE;
		}

		extract($sql -> db_Fetch());

		if($page_password)
		{
			if(!$this -> pageCheckPerms($page_password))
			{
				return FALSE;
			}
		}


		$this -> pageText = $page_text;
		if($this -> debug)
		{
			echo "<b>pageText</b> ".$this -> pageText." <br />";
		}
		
		$this -> parsePage();

		$gen = new convert;
		$text = "<span class='smalltext'>by ".$user_name.", ".$gen->convert_date($page_datestamp, "long")."</span><br /><br />";
		$text .= "<b>".$this -> title."</b><br /><br />";
		$text .= $this -> pageToRender;
		$text .= $this -> pageRating($page_rating_flag);
		$text .= $this -> pageIndex();

		$ns -> tablerender($page_title, $text);

		$this -> pageComment($page_comment_flag);

	}

	function parsePage()
	{
		global $tp;
		if(preg_match_all("/\[newpage.*?\]/si", $this -> pageText, $pt))
		{
			$pages = preg_split("/\[newpage.*?\]/si", $this -> pageText, -1, PREG_SPLIT_NO_EMPTY);
			$this -> multipageFlag = TRUE;
		}
		else
		{
			$this -> pageToRender = $tp -> toHTML($this -> pageText, TRUE, 'parse_sc');
			return;
		}

		foreach($pt[0] as $title)
		{
			$this -> pageTitles[] = $title;
		}
		

		if(!trim(chop($pages[0])))
		{
			$count = 0;
			foreach($pages as $page)
			{
				$pages[$count] = $pages[($count+1)];
				$count++;
			}
			unset($pages[(count($pages)-1)]);
		}

		$pageCount = count($pages);
		$titleCount = count($this -> pageTitles);
		/* if the vars above don't match, page 1 has no [newpage] tag, so we need to create one ... */

		if($pageCount != $titleCount)
		{
			array_unshift($this -> pageTitles, "[newpage]");
		}

		/* ok, titles now match pages, rename the titles if needed ... */

		$count =0;
		foreach($this -> pageTitles as $title)
		{
			$titlep = preg_replace("/\[newpage=(.*?)\]/", "\\1", $title);
			$this -> pageTitles[$count] = ($titlep == "[newpage]" ? "Page ".($count+1)."&nbsp;" : $tp -> toHTML($titlep, TRUE, 'parse_sc'));
			$count++;
		}

		$this -> pageToRender = $tp -> toHTML($pages[$this -> pageSelected], TRUE, 'parse_sc');
		$this -> title = (substr($this -> pageTitles[$this -> pageSelected], -1) == ";" ? "" : $this -> pageTitles[$this -> pageSelected]);



		if($this -> debug)
		{
			echo "<b>multipageFlag</b> ".$this -> multipageFlag." <br />";
			if($this -> multipageFlag)
			{
				echo "<pre>"; print_r($pages); echo "</pre>";
				echo "<b>pageCount</b> ".$pageCount." <br />";
				echo "<b>titleCount</b> ".$titleCount." <br />";
				echo "<pre>"; print_r($this -> pageTitles); echo "</pre>";
			}
		}

	}

	function pageIndex()
	{
		$itext = "<br /><br />";
		$count = 0;
		foreach($this -> pageTitles as $title)
		{
			$itext .= $this -> bullet." ".($count == $this -> pageSelected ? $title : "<a href='".e_SELF."?".$this -> pageID.".".$count."'>".$title."</a>")."<br />\n";
			$count++;
		}
		return $itext;
	}


	function pageRating($page_rating_flag)
	{
		if($page_rating_flag)
		{
			require_once(e_HANDLER."rate_class.php");
			$rater = new rater;
			$rate_text = "<br /><br />
				<table style='width:100%'>
				<tr>
				<td style='width:50%'>";
			if ($ratearray = $rater->getrating("page", $this -> pageID))
			{
				if ($ratearray[2] == "")
				{
					$ratearray[2] = 0;
				}
				$rate_text .= "<img src='".e_IMAGE."rate/box/box".$ratearray[1].".png' alt='' style='vertical-align:middle;' />\n";
				$rate_text .= "&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
				$rate_text .= ($ratearray[0] == 1 ? "vote" : "votes");
			}
			else
			{
				$rating .= LAN_dl_13;
			}
			$rate_text .= "</td><td style='width:50%; text-align:right'>";

			if (!$rater->checkrated("page", $this -> pageID) && USER) {
				$rate_text .= $rater->rateselect("&nbsp;&nbsp;&nbsp;&nbsp; <b>Rate this page</b>", "page", $this -> pageID)."</b>";
			}
			else if(!USER) {
				$rate_text .= "&nbsp;";
			} else {
				$rate_text .= "thankyou for rating this page";
			}
			$rate_text .= "</td></tr></table>";
		}
		return $rate_text;
	}


	function pageComment($page_comment_flag)
	{
		global $sql, $ns, $e107cache;
		if($page_comment_flag)
		{
			require_once(e_HANDLER."comment_class.php");
			$cobj = new comment;

			if (isset($_POST['commentsubmit']))
			{
				if ($sql->db_Select("page", "page_comment_flag", "page_id='".$this -> pageID."' "))
				{
					$row = $sql->db_Fetch();
					if ($row[0] && (ANON === TRUE || USER === TRUE)) {
						$cobj->enter_comment($_POST['author_name'], $_POST['comment'], "page", $this -> pageID, $pid, $_POST['subject']);
						$e107cache->clear("comment.page.".$this -> pageID);
					}
				}
			}

			$query = ($pref['nested_comments'] ? 
			"SELECT #comments.*, user_id, user_name, user_image, user_signature, user_join, user_comments, user_location FROM #comments
			LEFT JOIN #user ON #comments.comment_author = #user.user_id WHERE comment_item_id='".$this -> pageID."' AND comment_type='page' AND comment_pid='0' ORDER BY comment_datestamp"
			:
			"SELECT #comments.*, user_id, user_name, user_image, user_signature, user_join, user_comments, user_location FROM #comments
			LEFT JOIN #user ON #comments.comment_author = #user.user_id WHERE comment_item_id='".$this -> pageID."' AND comment_type='page'  ORDER BY comment_datestamp");

			$comment_total = $sql->db_Select_gen($query);

			if ($comment_total) {
				$width = 0;
				while ($row = $sql->db_Fetch())
				{
					if ($pref['nested_comments'])
					{
						$text = $cobj->render_comment($row, "page", "comment", $this -> pageID, $width, $subject);
						$ns->tablerender(LAN_5, $text);
					}
					else
					{
						$text .= $cobj->render_comment($row, "page", "comment", $this -> pageID, $width, $subject);
					}
				}
				if (!$pref['nested_comments'])
				{
					$ns->tablerender(LAN_5, $text);
				}
					if(ADMIN == TRUE && $comment_total)
					{
						echo "<a href='".e_BASE.e_ADMIN."modcomment.php?page.".$this -> pageID."'>".LAN_314."</a>";
					}
			}
			$cobj->form_comment("comment", "page", $id, $subject, $content_type);
		}
	}

	function pageCheckPerms($page_password)
	{
		global $ns;
		$cookiename = "e107page_".$this -> pageID;
		if(IsSet($_COOKIE[$cookiename]))
		{

			if($_COOKIE[$cookiename] != md5($page_password.USERID))
			{
				$this -> pageError(3);
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
		else
		{
			$text = "
			<div style='text-align:center; margin-left:auto; margin-right: auto;'>
			<form method='post' action='".e_SELF."?".e_QUERY."' id='pwform'>
			<table style='width:90%;' class='fborder'>
			<tr>
			<td class='forumheader' style='text-align:center;' colspan='3'>This page is password protected - please enter password to continue</td>
			</tr>
			<tr>
			<td class='forumheader3' style='width: 20%;'>Password:</td>
			<td class='forumheader3' style='width: 60%;'><input type='password' id='page_pw' name='page_pw' style='width: 90%;'/></td>
			<td class='forumheader3' width='20%' style='vertical-align: middle; margin-left: auto; margin-right: auto; text-align: center;'><img src='".e_IMAGE."generic/".IMODE."/password.png' alt='' /></td>
			</tr>
			<tr>
			<td class='forumheader' style='text-align:center;' colspan='3'><input class='button' type='submit' name='enterpw' value='Submit' /></td>
			</tr>
			</table>
			</form>
			</div>
			";
			$ns->tablerender("&nbsp;", $text);

			require_once(FOOTERF);
			exit;
		}
	}

	function setPageCookie()
	{
		global $pref;
		$hash = md5($_POST['page_pw'].USERID);
		cookie("e107page_".e_QUERY, $hash, (time() + $pref['pageCookieExpire']));
		header("location:".e_SELF."?".e_QUERY);
		exit;
	}

}


?>