<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/faqs/faqs.php,v $
 * $Revision: 1.4 $
 * $Date: 2009-11-20 05:01:51 $
 * $Author: e107coders $
 */

require_once ("../../class2.php");

include_lan(e_PLUGIN."faqs/languages/faq_lan_".e_LANGUAGE.".php");
include_lan(e_PLUGIN."faqs/languages/admin/faq_lan_".e_LANGUAGE.".php");


require_once (e_HANDLER."form_handler.php");
require_once (e_HANDLER."userclass_class.php");
require_once (e_HANDLER."ren_help.php");
require_once (e_HANDLER."comment_class.php");

if (!$FAQ_VIEW_TEMPLATE)
{
	if (file_exists(THEME."faqs_template.php"))
	{
		require_once (THEME."faqs_template.php");
	}
	else
	{
		require_once (e_PLUGIN."faqs/faqs_template.php");
	}
}

// require_once(HEADERF);

// $pref['add_faq']=1;

$rs = new form;
$cobj = new comment;

if (!$_GET['elan'])
{
	$qs = explode(".", e_QUERY);
	$action = $qs[0];
	$id = $qs[1];
	$idx = $qs[2];
}
$from = ($from ? $from : 0);
$amount = 50;

if (isset($_POST['faq_submit']))
{
	$message = "-";
	if ($_POST['faq_question'] != "" || $_POST['data'] != "")
	{
		$faq_question = $aj->formtpa($_POST['faq_question'], "on");
		$data = $aj->formtpa($_POST['data'], "on");
		$count = ($sql->db_Count("faqs", "(*)", "WHERE faq_parent='".$_POST['faq_parent']."' ") + 1);
		$sql->db_Insert("faqs", " 0, '".$_POST['faq_parent']."', '$faq_question', '$data', '".$_POST['faq_comment']."', '".time()."', '".USERID."', '".$count."' ");
		$message = FAQ_ADLAN_32;
		unset($faq_question, $data);
	}
	else
	{
		$message = FAQ_ADLAN_30;
	}
	$id = $_POST['faq_parent'];
}

if (isset($_POST['faq_edit_submit']))
{
	if ($_POST['faq_question'] != "" || $_POST['data'] != "")
	{
		$faq_question = $aj->formtpa($_POST['faq_question'], "on");
		$data = $aj->formtpa($_POST['data'], "on");
		
		$sql->db_Update("faqs", "faq_parent='".$_POST['faq_parent']."', faq_question ='$faq_question', faq_answer='$data', faq_comment='".$_POST['faq_comment']."'  WHERE faq_id='".$idx."' ");
		$message = FAQ_ADLAN_29;
		unset($faq_question, $data);
	}
	else
	{
		$message = FAQ_ADLAN_30;
	}
}

if (isset($_POST['commentsubmit']))
{
	$pid = (IsSet($_POST['pid']) ? $_POST['pid'] : 0);
	$cobj->enter_comment($_POST['author_name'], $_POST['comment'], "faq", $idx, $pid, $_POST['subject']);
}

// Actions +++++++++++++++++++++++++++++

	$faq = new faq;

	$faqpref = e107::getPlugConfig('faqs')->getPref();

	if ($action == "" || $action == "main")
	{
		if(vartrue($faqpref['classic_look']))
		{
			$tmp = $faq->show_existing_parents($action, $sub_action, $id, $from, $amount);	
		}
		else
		{
			$tmp = $faq->view_all();	
		}
	
		if (vartrue($faqpref['faq_title']))
		{
			define("e_PAGETITLE", $faqpref['faq_title']);
		}
		else
		{
			define("e_PAGETITLE", FAQLAN_23);
		}
		require_once (HEADERF);
		$ns->tablerender(FAQLAN_41, $tmp['text']);	
	}

	if($action == "cat" && $idx)
	{
		 $tmp = $faq->view_faq($idx) ;
		 define("e_PAGETITLE",FAQLAN_FAQ." - ". $tmp['title']);
		 require_once(HEADERF);
		 $ns -> tablerender($tmp['caption'], $tmp['text']);
	}
	
	if ($action == "cat")
	{
		$tmp = $faq->view_cat_list($action, $id);
		
		define("e_PAGETITLE", strip_tags($tmp['title'].$tmp['caption']));
		require_once (HEADERF);
		$ns->tablerender($tmp['caption'], $tmp['text']);
	}


	if ((check_class($faqpref['add_faq']) || ADMIN) && ($action == "new" || $action == "edit"))
	{
		require_once (HEADERF);
		$faq->add_faq($action, $id, $idx);
	}

require_once (FOOTERF);
exit;


// ====== +++++++++++++++++++++++++++++


class faq
{
	var $pref = array();
	
	function __construct()
	{
		$this->pref = e107::getPlugConfig('faqs')->getPref();
		setScVar('faqs_shortcodes', 'pref', $this->pref);
	}	
	
	function view_all() // new funtion to render all FAQs
	{
		echo "hello";
		$sql = e107::getDb();
		$tp = e107::getParser();
		
		global $FAQ_START, $FAQ_END, $FAQ_LISTALL_START,$FAQ_LISTALL_LOOP,$FAQ_LISTALL_END;
		
		require_once (e_PLUGIN."faqs/faqs_shortcodes.php");
		
		$query = "SELECT f.*,cat.* FROM #faqs AS f LEFT JOIN #faqs_info AS cat ON f.faq_parent = cat.faq_info_id ORDER BY cat.faq_info_order,f.faq_order ";
		$sql->db_Select_gen($query);
		$text = $tp->parseTemplate($FAQ_START, true);		
		$prevcat = "";
		while ($rw = $sql->db_Fetch())
		{
			setScVar('faqs_shortcodes', 'row', $rw);
					
			if($rw['faq_info_order'] != $prevcat)
			{
				if($prevcat !='')
				{
					$text .= $tp->parseTemplate($FAQ_LISTALL_END, true);
				}
				$text .= "\n\n<!-- FAQ Start ".$rw['faq_info_order']."-->\n\n";
				$text .= $tp->parseTemplate($FAQ_LISTALL_START, true);
				$start = TRUE;				
			}
						
			$text .= $tp->parseTemplate($FAQ_LISTALL_LOOP, true);
			$prevcat = $rw['faq_info_order'];
		
		}
		$text .= $tp->parseTemplate($FAQ_LISTALL_END, true);
		$text .= $tp->parseTemplate($FAQ_END, true);
					
		$ret['title'] = FAQLAN_FAQ;
		$ret['text'] = $text;
		$ret['caption'] = $caption;
		return $ret;	
	}
	

// -------------  Everything below here is kept for backwards-compatability 'Classic Look' ------------

	
	function view_cat_list($action, $id)
	{
		global $ns,$row,$FAQ_LIST_START,$FAQ_LIST_LOOP,$FAQ_LIST_END;
		
		$tp = e107::getParser();
		$sql = e107::getDb();
		require_once (e_PLUGIN."faqs/faqs_shortcodes.php");
		
		$query = "SELECT f.*,cat.* FROM #faqs AS f LEFT JOIN #faqs_info AS cat ON f.faq_parent = cat.faq_info_id WHERE f.faq_parent = '$id' ";
		$sql->db_Select_gen($query);
		
		setScVar('faqs_shortcodes', 'row', $row);
		
		$text = $tp->parseTemplate($FAQ_LIST_START, true);
		
		while ($rw = $sql->db_Fetch())
		{
			setScVar('faqs_shortcodes', 'row', $rw);
			$text .= $tp->parseTemplate($FAQ_LIST_LOOP, true);
			$caption = "&nbsp;Category: <b>".$rw['faq_info_title']."</b>";

		}
		
		$text .= $tp->parseTemplate($FAQ_LIST_END, true);
		
		$ret['title'] = FAQLAN_FAQ." - ".$category_title;
		$ret['text'] = $text.$this->faq_footer($id);
		$ret['caption'] = $caption;
		return $ret;
	}
	// =============================================================================
	
	function show_existing_parents($action, $sub_action, $id, $from, $amount)
	{
		$tp = e107::getParser();
		$sql = e107::getDb();
		
		// ##### Display scrolling list of existing FAQ items ---------------------------------------------------------------------------------------------------------
		global $FAQ_CAT_START,$FAQ_CAT_PARENT,$FAQ_CAT_CHILD,$FAQ_CAT_END;
		
		require_once (e_PLUGIN."faqs/faqs_shortcodes.php");
		
		$text = "<div style='text-align:center'>
			<div style='text-align:center'>";
		
		$qry = "SELECT dc.*,
		COUNT(d.faq_id) AS f_count,
		COUNT(d2.faq_id) AS f_subcount
		FROM #faqs_info AS dc
		LEFT JOIN #faqs AS d ON dc.faq_info_id = d.faq_parent
 		LEFT JOIN #faqs_info as dc2 ON dc2.faq_info_parent = dc.faq_info_id
		LEFT JOIN #faqs AS d2 ON dc2.faq_info_id = d2.faq_parent
		WHERE dc.faq_info_class IN (".USERCLASS_LIST.")
		GROUP by dc.faq_info_id ORDER by dc.faq_info_order,dc.faq_info_parent "; //
		
		$text .= $FAQ_CAT_START;
		
		$sql->db_Select_gen($qry);
		while ($row = $sql->db_Fetch())
		{
			
			setScVar('faqs_shortcodes', 'row', $row);
			
			if ($row['faq_info_parent'] == '0') //
			{
				$text .= $tp->parseTemplate($FAQ_CAT_PARENT, true);
			}
			else
			{
				
				if (!$row['f_count'] && !$row['f_subcount'])
				{
					
					$text .= $tp->parseTemplate($FAQ_CAT_CHILD, true);
				}
				else
				{
					$text .= $tp->parseTemplate($FAQ_CAT_CHILD, true);
				}
			}
		}
		
		$text .= $FAQ_CAT_END;
		
		$ret['text'] = $text.$this->faq_footer();
		return $ret;
	
	}
	
	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	function view_faq($idx)
	{
		global $ns,$row,$sql,$aj,$pref,$cobj,$id,$tp,$FAQ_VIEW_TEMPLATE;
		
		require_once (e_PLUGIN."faqs/faqs_shortcodes.php");
		
		$sql->db_Select("faqs", "*", "faq_id='$idx' LIMIT 1");
		$row = $sql->db_Fetch();
		setScVar('faqs_shortcodes', 'row', $row);
		
		$caption = "&nbsp;FAQ #".$row['faq_id'];
		$text = $tp->parseTemplate($FAQ_VIEW_TEMPLATE, true);
		
	//	$text = $tp->toHTML($text, TRUE);
		
		$ret['text'] = $text;
		$ret['caption'] = $caption;
		$ret['title'] = $row['faq_question'];
		$ret['comments'] = $text;
		
		return $ret;
		
		$subject = (!$subject ? $aj->formtpa($faq_question) : $subject);
		
		if (check_class($row['faq_comment']))
		{
			
			$action = "comment";
			$table = "faq";
			$query = ($pref['nested_comments'] ? "comment_item_id='$idx' AND (comment_type='$table' OR comment_type='3') AND comment_pid='0' ORDER BY comment_datestamp" : "comment_item_id='$idx' AND (comment_type='$table' OR comment_type='3') ORDER BY comment_datestamp");
			unset($text);
			if (!is_object($sql2))
			{
				$sql2 = new db;
			}
			if ($comment_total = $sql2->db_Select("comments", "*", $query))
			{
				$width = 0;
				while ($row = $sql2->db_Fetch())
				{
					if ($pref['nested_comments'])
					{
						$text = $cobj->render_comment($row, $table, $action, $idx.".".$id, $width, $subject);
						$ns->tablerender(FAQLAN_38, $text);
					}
					else
					{
						$text .= $cobj->render_comment($row, $table, $action, $idx.".".$id, $width, $subject);
					}
				}
				if (!$pref['nested_comments'])
				{
					$ns->tablerender("Comments", $text);
				}
				if (ADMIN && getperms("B"))
				{
					// bkwon 05-Jun-2004 fix URL to moderate comment
					echo "<div style='text-align:right'><a href='".e_ADMIN."modcomment.php?faq.$faq_id'>moderate comments</a></div><br />";
				}
			}
			$cobj->form_comment($action, $table, $idx.".".$id, $subject, $content_type);
		} // end of check_class
	}


	
	function faq_footer($id='')
	{
        global $faqpref,$timing_start,$tp,$cust_footer, $CUSTOMPAGES, $CUSTOMHEADER, $CUSTOMHEADER;
        $text_menu .= "<div style='text-align:center;' ><br />
        &nbsp;&nbsp;[&nbsp;<a href=\"faqs.php?main\">Back to Categories</a>&nbsp;]&nbsp;&nbsp;";

        if(check_class($faqpref['add_faq'])){
                $text_menu .="[&nbsp;<a href=\"faqs.php?new.$id\">Submit a Question</a>&nbsp;]";
        }
        $text_menu .="</div>";

		$text_menu .= "<div style='text-align:center'><br />".$tp->parseTemplate("{SEARCH=faq}")."</div>";
       return $text_menu;
		
		// require_once (FOOTERF);
	}
	
	function add_faq($action, $id, $idx)
	{
		global $rs;
		
		$tp = e107::getParser();
		$sql = e107::getDb();
		$ns = e107::getRender();
		
		$userid = USERID;
		
		$text .= "<table class='fborder' style=\"".USER_WIDTH."\" >
        <tr>
        <td colspan='2' class='forumheader3' style=\"width:80%; padding:0px\">";
		$sql->db_Select("faqs", "*", "faq_parent='$id' AND faq_author = '$userid' ORDER BY faq_id ASC");
		$text .= "<div style='width : auto; height : 110px; overflow : auto; '>
        <table class='fborder' style=\"width:100%\">
        <tr>
        <td class='fcaption' style=\"width:70%\">".FAQ_ADLAN_49."</td>
        <td class='fcaption' style='text-align:center'>Options</td></tr>
        ";
		while ($rw = $sql->db_Fetch())
		{
			// list($pfaq_id, $pfaq_parent, $pfaq_question, $pfaq_answer, $pfaq_comment);
			$rw['faq_question'] = substr($rw['faq_question'], 0, 50)." ... ";
			
			$text .= "<tr>

                  <td style='width:70%' class='forumheader3'>".($rw['faq_question'] ? $tp->toHTML($rw['faq_question']) : "[".NWSLAN_42."]")."</td>
                  <td style='width:30%; text-align:center' class='forumheader3'>
                  ".$rs->form_button("submit", "entry_edit_{$rw['faq_id']}", FAQ_ADLAN_45, "onclick=\"document.location='".e_SELF."?edit.".$id.".".$rw['faq_id'].".'\"");
			//     $text .= $rs -> form_button("submit", "entry_delete", FAQ_ADLAN_50, "onclick=\"document.location='".e_SELF."?delentry.$id.$pfaq_id'\"")."
			$text .= "</td>
                  </tr>";
		}
		$text .= "</table></div>";
		
		if ($action == "edit")
		{
			$sql->db_Select("faqs", "*", " faq_id = '$idx' ");
			$row = $sql->db_Fetch();
			extract($row);
			$data = $faq_answer;
		}
		
		$text .= "</td>
        </tr></table><form method=\"post\" action=\"".e_SELF."?cat.$id.$idx\" id=\"dataform\">
        <table class='fborder' style=\"".USER_WIDTH."\" >
        <tr>
        <td class='fcaption' colspan='2' style='text-align:center'>";
		
		$text .= (is_numeric($id)) ? "Edit" : "Add";
		$text .= " an FAQ</td></tr>";
		
		$text .= "
        <tr>
        <td class='forumheader3' style=\"width:20%\">".FAQ_ADLAN_78."</td>
        <td class='forumheader3' style=\"width:80%\">";
		
		$text .= "<select style='width:150px' class='tbox' id='faq_parent' name='faq_parent' >";
		$sql->db_Select("faqs_info", "*", "faq_info_parent !='0' ");
		while ($prow = $sql->db_Fetch())
		{
			//extract($row);
			$selected = $prow['faq_info_id'] == $id ? " selected='selected'" : "";
			$text .= "<option value=\"".$prow['faq_info_id']."\" $selected>".$prow['faq_info_title']."</option>";
		}
		$text .= " </select>
            </td>
            </tr>";
		
		$text .= "
        <tr>
        <td class='forumheader3' style=\"width:20%\">".FAQ_ADLAN_51."</td>
        <td class='forumheader3' style=\"width:80%\">

        <input class=\"tbox\" type=\"text\" name=\"faq_question\" style=\"width:100%\" value=\"$faq_question\"  />
        </td>
        </tr>

        <tr>
        <td class='forumheader3' style=\"width:20%;vertical-align:top\">".FAQ_ADLAN_60."</td>
        <td class='forumheader3' style=\"width:80%\">
        <textarea id=\"data\" cols='15' class=\"tbox\" name=\"data\" style=\"width:100%\" rows=\"8\" onselect=\"storeCaret(this);\" onclick=\"storeCaret(this);\" onkeyup=\"storeCaret(this);\">$data</textarea>
        <br />
        <input class='helpbox' type=\"text\" id='helpb' name=\"helpb\" size=\"70\" style='width:100%' /><br />
         ";
		$text .= ren_help("addtext");
		
		$text .= "<br /></td></tr>";
		
		if (ADMIN)
		{
			$text .= "<tr>
          <td class='forumheader3'  style=\"width:20%; vertical-align:top\">".FAQ_ADLAN_52."</td>";
			require_once (e_HANDLER."userclass_class.php");
			$text .= "<td class='forumheader3' >".r_userclass("faq_comment", $faq_comment, "", "public,guest,nobody,member,admin,classes")."</td>";
			$text .= "
          </tr>";
		}
		else
		{
			$text .= "<input type='hidden' name='faq_comment' value='0' />";
		}
		$text .= "

        <tr>
        <td class='forumheader' colspan=\"2\" style=\"text-align:center\">
        ";
		
		if ($action == "edit")
		{
			$text .= "<input class=\"button\" type=\"submit\" name=\"faq_edit_submit\" value=\"".FAQ_ADLAN_53."$faq_id\" />
            <input type=\"hidden\" name=\"faq_id\" value=\"$idx\" /> ";
		}
		else
		{
			$text .= "<input class=\"button\" type=\"submit\" name=\"faq_submit\" value=\"".FAQ_ADLAN_54."\" />";
		}
		
		$text .= "<input type=\"hidden\" name=\"faq\" value=\"$faq\" />
        </td>
        </tr>
        </table>

        </form>";
		
		if(varset($faq))
		{
			$sql->db_Select("faqs_info", "*", "faq_info_id='$faq'");
			$row = $sql->db_Fetch();
			extract($row);
		}
		$ns->tablerender("Frequently asked Questions".$faq_info_title, "<div style='text-align:center'>".$text."</div>".$this->faq_footer());
	
	}

}

?>