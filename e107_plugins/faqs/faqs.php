<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * FAQ Core Plugin
 *
 */

if (!defined('e107_INIT'))
{
	require_once("../../class2.php");
}

if(file_exists(e_PLUGIN."faqs/controllers/list.php")) // bc for old controller.
{
	$url = e107::getUrl()->create('faqs/list/all', false, 'full=1&noencode=1');
	header('Location: '.$url);
	exit;
}
else 
{
 	e107::includeLan(e_PLUGIN."faqs/languages/".e_LANGUAGE."/".e_LANGUAGE."_front.php");
}



require_once (e_HANDLER."form_handler.php"); // TODO - Remove outdated code 
require_once (e_HANDLER."userclass_class.php");
require_once (e_HANDLER."ren_help.php"); // TODO - Remove outdated code 
require_once (e_HANDLER."comment_class.php");

/*
if (!vartrue($FAQ_VIEW_TEMPLATE))
{
	if (file_exists(THEME."faqs_template.php"))
	{
	//	require_once (THEME."faqs_template.php");
	}
	else
	{
	//	require_once (e_PLUGIN."faqs/templates/faqs_template.php");
	}
}
*/

e107::css('faqs','faqs.css');
// require_once(HEADERF);

// $pref['add_faq']=1;

$rs 	= new form; // TODO - Remove outdated code 
$cobj 	= new comment;

$tp 	= e107::getParser(); 
$frm 	= e107::getForm();

if (!vartrue($_GET['elan']) && empty($_GET))
{
	$qs 	= explode(".", e_QUERY);
	$action = $qs[0];
	$id 	= $qs[1];
	$idx 	= $qs[2];
}
else
{
	
}



$from = (vartrue($from) ? $from : 0);
$amount = 50;

if (isset($_POST['faq_submit']))
{
	$message = "-";
	if ($_POST['faq_question'] != "" || $_POST['data'] != "")
	{
		$faq_question 	= $tp->toDB($_POST['faq_question']);
		$data 			= $tp->toDB($_POST['data']);
		$count 			= ($sql->db_Count("faqs", "(*)", "WHERE faq_parent='".intval($_POST['faq_parent'])."' ") + 1);
		
		$sql->insert("faqs", " 0, '".$_POST['faq_parent']."', '$faq_question', '$data', '".filter_var($_POST['faq_comment'], FILTER_SANITIZE_STRING)."', '".time()."', '".USERID."', '".$count."' ");
		
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
		$faq_question 	= $tp->toDB($_POST['faq_question']);
		$data 			= $tp->toDB($_POST['data']);

		$sql->update("faqs", "faq_parent='".intval($_POST['faq_parent'])."', faq_question ='$faq_question', faq_answer='$data', faq_comment='".$_POST['faq_comment']."'  WHERE faq_id='".$idx."' ");
		
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

	if (empty($action) || $action == "main")
	{
		if(vartrue($faqpref['classic_look']))
		{
			$ftmp = $faq->show_existing_parents($action, $sub_action, $id, $from, $amount);
			$caption = FAQLAN_41;
		}
		else
		{
			$srch = vartrue($_GET['srch']);
			$ftmp = $faq->view_all($srch);
			$caption = LAN_FAQS_011;

		}

		$pageTitle = '';


	//	define("e_PAGETITLE", $ftmp['caption']);

		if (vartrue($faqpref['page_title']))
		{
			$pageTitle = $faqpref['page_title'][e_LANGUAGE];
		}
		else
		{
			$pageTitle = $ftmp['caption'];
		}

		if(!empty($ftmp['pagetitle']))
		{
			$pageTitle .= ": ".$ftmp['pagetitle'];
		}
		e107::getMessage()->addDebug("TITLE: " . $pageTitle);

		e107::meta('og:title', $pageTitle);

		if(!empty($ftmp['pagedescription']))
		{

			e107::meta('og:description', $ftmp['pagedescription']);
		}

		define('e_PAGETITLE', $pageTitle);



		require_once (HEADERF);
				
		$ns->tablerender($ftmp['caption'], $ftmp['text']);
		
	}

	if($action == "cat" && $idx)
	{
		 $ftmp = $faq->view_faq($idx) ;
		 define("e_PAGETITLE",LAN_FAQS_011." - ". $ftmp['title']);
		 require_once(HEADERF);
		 $ns->tablerender($ftmp['caption'], $ftmp['text']);
	}

	if ($action == "cat")
	{
		$ftmp = $faq->view_cat_list($action, $id);

		define("e_PAGETITLE", strip_tags($ftmp['title'].$ftmp['caption']));
		require_once (HEADERF);
		$ns->tablerender($ftmp['caption'], $ftmp['text']);
	}


	if((check_class($faqpref['add_faq']) || ADMIN) && ($action == "new" || $action == "edit"))
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
	protected $sc = null;
	protected $template = null;
	protected $pageTitle = null;
	protected $pageDescription = null;

	function __construct()
	{
		$sc = e107::getScBatch('faqs', true);
		$this->pref = e107::pref('faqs'); // Short version of e107::getPlugConfig('faqs')->getPref(); ;
		$sc->pref = $this->pref;


		if(!empty($_POST['submit_a_question']))
		{
			$sql = e107::getDb();

			$existing = $sql->select('faqs','faq_id',"faq_answer='' AND faq_author_ip = '".USERIP."' ");

			if(!empty($this->pref['submit_question_limit']) && $existing >= $this->pref['submit_question_limit'])
			{
				e107::getMessage()->setTitle(LAN_WARNING,E_MESSAGE_INFO)->addInfo(LAN_FAQS_005);
				return;
			}

			$question = filter_input(INPUT_POST, 'ask_a_question', FILTER_SANITIZE_STRING);

			$insert = array(
				'faq_id'        => 0,
				'faq_parent'    => 0, // meaning 'unassigned/unanswered'.
				'faq_question'  => $question,
				'faq_answer'    => '',
				'faq_comment'   => 0,
				'faq_datestamp' => time(),
				'faq_author'    => USERID,
				'faq_author_ip' => USERIP,
				'faq_tags'      => '',
				'faq_order'     => 99999
			);

			if($sql->insert('faqs', $insert))
			{
				$message = !empty($this->pref['submit_question_acknowledgement']) ? e107::getParser()->toHTML($this->pref['submit_question_acknowledgement'],true, 'BODY') : LAN_FAQS_004;
				e107::getMessage()->addSuccess($message);
			}

		}



	}



	function view_all($srch) // new funtion to render all FAQs
	{
		$tp = e107::getParser();
		$ret = array();

		$template = e107::getTemplate('faqs');
		$this->template = $template;
	
		$this->sc = e107::getScBatch('faqs',TRUE);
		
		$text = $tp->parseTemplate($template['start'], true, $this->sc); // header
		
		// var_dump($sc);
		
		$text .= "<div id='faqs-container'>";
		
		$text .= $this->view_all_query($srch);
		
		$text .= "</div>";
	
		$text .= $tp->parseTemplate($template['end'], true, $this->sc); // footer

		$ret['title'] = LAN_FAQS_011;
		$ret['text'] = $text;

		if (!empty($this->pref['page_title'][e_LANGUAGE]))
		{
			$ret['caption'] = e107::getParser()->toHTML($this->pref['page_title'][e_LANGUAGE], true, 'TITLE');
		}
		else
		{
			$ret['caption'] = varset($template['caption']) ? $tp->parseTemplate($template['caption'], true, $this->sc) : LAN_PLUGIN_FAQS_FRONT_NAME;
		}

		if(!empty($this->pageTitle))
		{
			$ret['pagetitle'] = e107::getParser()->toText($this->pageTitle);
		}

		if(!empty($this->pageDescription))
		{
			$ret['pagedescription'] = e107::getParser()->toText($this->pageDescription,true,'RAWTEXT');
		}
		
		return $ret;
	}



	function view_all_query($srch='')
	{
		$sql = e107::getDb();
		$tp = e107::getParser();

		$text = "";
		
		$insert = "";
		$item = false;

		$removeUrl = e107::url('faqs','index');
		
		if(!empty($srch))
		{
			$srch = $tp->toDB($srch);
			$insert = " AND (f.faq_question LIKE '%".$srch."%' OR f.faq_answer LIKE '%".$srch."%' OR FIND_IN_SET ('".$srch."', f.faq_tags) ) ";


		//	$message = "<span class='label label-lg label-info'>".$srch." <a class='e-tip' title='".LAN_FAQS_006."' href='".$removeUrl."'>×</a></span>";

		//	e107::getMessage()->setClose(false,E_MESSAGE_INFO)->setTitle(LAN_FAQS_002,E_MESSAGE_INFO)->addInfo($message);
		//	$text = e107::getMessage()->render();
		}

		if(!empty($_GET['id'])) // pull out just one specific FAQ.
		{
			$srch = intval($_GET['id']);
		//	$insert = " AND (f.faq_id = ".$srch.") ";
			$item = $srch;
		}
		
		if(!empty($_GET['cat']))
		{
			$srch = $tp->toDB($_GET['cat']);
			$insert = " AND (cat.faq_info_sef = '".$srch."') ";
		}

		if(!empty($_GET['tag']))
		{
			$srch = $tp->toDB($_GET['tag']);


			$insert = " AND FIND_IN_SET ('".$srch."', f.faq_tags)  ";

			$message = "<span class='label label-lg label-info'>".$srch." <a class='e-tip' title='".LAN_FAQS_006."' href='".$removeUrl."'>×</a></span>";

			e107::getMessage()->setClose(false,E_MESSAGE_INFO)->setTitle(LAN_FAQS_002,E_MESSAGE_INFO)->addInfo($message);
			$text = e107::getMessage()->render();
		}


		list($orderBy, $ascdesc) = explode('-', vartrue($this->pref['orderby'],'faq_order-ASC'));

		$query = "SELECT f.*,cat.* FROM #faqs AS f LEFT JOIN #faqs_info AS cat ON f.faq_parent = cat.faq_info_id WHERE cat.faq_info_class IN (".USERCLASS_LIST.") ".$insert." ORDER BY cat.faq_info_order, f.".$orderBy." ".$ascdesc." ";
		
		if(!$data = $sql->retrieve($query, true))
		{
			$message = 	(!empty($srch)) ? e107::getParser()->lanVars(LAN_FAQS_008, $srch)."<a class='e-tip' title='".LAN_FAQS_007."' href='".$removeUrl."'>".LAN_FAQS_007."</a>" : LAN_FAQS_003;
			return "<div class='alert alert-warning alert-block'>".$message."</div>" ; 
		}
		
		// -----------------
		
		$FAQ_LISTALL = e107::getTemplate('faqs', true, 'all');

		$prevcat = "";
		$sc = e107::getScBatch('faqs', true);
		$sc->counter = 1;
		$sc->tag = htmlspecialchars(varset($tag), ENT_QUOTES, 'utf-8');
		$sc->category = varset($category);

		 if(!empty($_GET['id'])) // expand one specific FAQ.
		{
			$sc->item =intval($_GET['id']);

			$js = "
				$( document ).ready(function() {
                    $('html, body').animate({ scrollTop:  $('div#faq_".$sc->item."').offset().top - 300 }, 4000);
				});

				";

			e107::js('footer-inline', $js);
		}

	//	$text = $tp->parseTemplate($FAQ_START, true, $sc);

	//	$text = "";



		if($this->pref['list_type'] == 'ol')
		{
			$reversed = ($ascdesc == 'DESC') ? 'reversed ' : '';
			$tsrch = array('<ul ','/ul>');
			$trepl = array('<ol '.$reversed,'/ol>');
			$FAQ_LISTALL['start'] = str_replace($tsrch,$trepl, $FAQ_LISTALL['start']);
			$FAQ_LISTALL['end'] = str_replace($tsrch,$trepl, $FAQ_LISTALL['end']);
		}


		foreach ($data as $rw)
		{
			$rw['faq_sef'] = eHelper::title2sef($tp->toText($rw['faq_question']),'dashl');

			$sc->setVars($rw);

			if($sc->item == $rw['faq_id'])
			{
				$this->pageTitle = $rw['faq_question'];
				$this->pageDescription = $rw['faq_answer'];
			}
			
			if($rw['faq_info_order'] != $prevcat)
			{
				if($prevcat !='')
				{
					$text .= $tp->parseTemplate($FAQ_LISTALL['end'], true, $sc);
				}
				$text .= "\n\n<!-- FAQ Start ".$rw['faq_info_order']."-->\n\n";
				$text .= $tp->parseTemplate($FAQ_LISTALL['start'], true, $sc);
				$start = TRUE;
			}

			$text .= $tp->parseTemplate($FAQ_LISTALL['item'], true, $sc);
			$prevcat = $rw['faq_info_order'];
			$sc->counter++;
		}
		$text .= ($start) ? $tp->parseTemplate($FAQ_LISTALL['end'], true, $sc) : "";
//		$text .= $tp->parseTemplate($FAQ_END, true, $sc);

		return $text;
		
	}




// -------------  Everything below here is kept for backwards-compatability 'Classic Look' ------------


	function view_cat_list($action, $id)
	{
		global $ns,$row,$FAQ_LIST_START,$FAQ_LIST_LOOP,$FAQ_LIST_END;

		$tp 	= e107::getParser();
		$sql 	= e107::getDb();
		$sc 	= e107::getScBatch('faqs',TRUE);

		$query = "SELECT f.*,cat.* FROM #faqs AS f LEFT JOIN #faqs_info AS cat ON f.faq_parent = cat.faq_info_id WHERE f.faq_parent = '$id' ";
		$sql->gen($query);
		$sc->setVars($row);

		$text = $tp->parseTemplate($FAQ_LIST_START, true);

		while ($rw = $sql->fetch())
		{
			$sc->setVars($rw);
			$text .= $tp->parseTemplate($FAQ_LIST_LOOP, true);
			$caption = "&nbsp;".LAN_CATEGORY.": <b>".$rw['faq_info_title']."</b>";
		}

		$text .= $tp->parseTemplate($FAQ_LIST_END, true);

		$ret['title'] = LAN_FAQS_011." - ".$category_title;
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

		// require_once (e_PLUGIN."faqs/faqs_shortcodes.php");
		$sc = e107::getScBatch('faqs',TRUE);

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

		$sql->gen($qry);
		while ($row = $sql->fetch())
		{
			$sc->setVars($row);

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

		$text .= "</div>
			</div>";

		$ret['text'] = $text.$this->faq_footer();
		return $ret;

	}

	// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

	function view_faq($idx)
	{
		global $row,$pref,$cobj,$id,$FAQ_VIEW_TEMPLATE;
		$ns 	= e107::getRender();
		$sql 	= e107::getDb();
		$tp 	= e107::getParser();
		//require_once (e_PLUGIN."faqs/faqs_shortcodes.php");
		
		$sc = e107::getScBatch('faqs',TRUE);

		$sql->select("faqs", "*", "faq_id='$idx' LIMIT 1");
		$row = $sql->fetch();

		$sc->setVars($row);

		$caption = "&nbsp;FAQ #".$row['faq_id'];
		$text = $tp->parseTemplate($FAQ_VIEW_TEMPLATE, true);

	//	$text = $tp->toHTML($text, TRUE);

		$ret['text'] 		= $text;
		$ret['caption'] 	= $caption;
		$ret['title'] 		= $row['faq_question'];
		$ret['comments'] 	= $text;

		return $ret;

		$subject = (!$subject ? $tp->toDB($faq_question) : $subject);

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
			if ($comment_total = $sql2->select("comments", "*", $query))
			{
				$width = 0;
				while ($row = $sql2->fetch())
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
					$ns->tablerender(LAN_COMMENTS, $text);
				}
				if (ADMIN && getperms("B"))
				{
					// bkwon 05-Jun-2004 fix URL to moderate comment
					echo "<div style='text-align:right'><a href='".e_ADMIN."modcomment.php?faq.$faq_id'>".LAN_FAQS_009."</a></div><br />";
				}
			}
			$cobj->form_comment($action, $table, $idx.".".$id, $subject, $content_type);
		} // end of check_class
	}



	function faq_footer($id='')
	{
        global $faqpref,$timing_start,$cust_footer, $CUSTOMPAGES, $CUSTOMHEADER, $CUSTOMHEADER;
        
        $tp = e107::getParser();

        $text_menu .= "<div style='text-align:center;' ><br />
        &nbsp;&nbsp;[&nbsp;<a href='faqs.php?main'>".LAN_FAQS_010."</a>&nbsp;]&nbsp;&nbsp;";

        if(check_class($faqpref['add_faq'])){
                $text_menu .="[&nbsp;<a href='faqs.php?new.$id'>".LAN_FAQS_ASK_A_QUESTION."</a>&nbsp;]";
        }
        
        $text_menu .="</div>";

		$text_menu .= "<div style='text-align:center'><br />".$tp->parseTemplate("{SEARCH=faqs}")."</div>";
       	
       	return $text_menu;

		// require_once (FOOTERF);
	}

	function add_faq($action, $id, $idx)
	{
		global $rs; // TODO - remove old code

		$tp 	= e107::getParser();
		$sql 	= e107::getDb();
		$ns 	= e107::getRender();

		$userid = USERID;

		$text .= "<table class='fborder' style=\"".USER_WIDTH."\" >
        <tr>
        <td colspan='2' class='forumheader3' style=\"width:80%; padding:0px\">";
		$sql->select("faqs", "*", "faq_parent='$id' AND faq_author = '$userid' ORDER BY faq_id ASC");
		$text .= "<div style='width : auto; height : 110px; overflow : auto; '>
        <table class='fborder' style=\"width:100%\">
        <tr>
        <td class='fcaption' style=\"width:70%\">".FAQ_ADLAN_49."</td>
		<td class='fcaption' style='text-align:center'>".LAN_SETTINGS."</td></tr>
        ";
		while ($rw = $sql->fetch())
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


		// TODO - optimize
		if ($action == "edit")
		{
			$sql->select("faqs", "*", " faq_id = '$idx' ");
			$row = $sql->fetch();
			extract($row); // get rid of this
			$data = $faq_answer;
		}

		$text .= "</td>
        </tr></table><form method=\"post\" action=\"".e_SELF."?cat.$id.$idx\" id=\"dataform\">
        <table class='fborder' style=\"".USER_WIDTH."\" >
        <tr>
        <td class='fcaption' colspan='2' style='text-align:center'>";

		$text .= (is_numeric($id)) ? LAN_EDIT : LAN_ADD; //LAN_ADD may not exist on the front end, but I dont think this code is used - Mikey.
		$text .= " FAQ</td></tr>"; 

		$text .= "
        <tr>
        <td class='forumheader3' style=\"width:20%\">".FAQ_ADLAN_78."</td>
        <td class='forumheader3' style=\"width:80%\">";

		$text .= "<select style='width:150px' class='tbox' id='faq_parent' name='faq_parent' >";
		$sql->select("faqs_info", "*", "faq_info_parent !='0' ");
		while ($prow = $sql->fetch())
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
			$sql->select("faqs_info", "*", "faq_info_id='$faq'");
			$row = $sql->fetch();
			extract($row); // get rid of this
		}
		$ns->tablerender(LAN_PLUGIN_FAQS_FRONT_NAME.$faq_info_title, "<div style='text-align:center'>".$text."</div>".$this->faq_footer());

	}

}

?>