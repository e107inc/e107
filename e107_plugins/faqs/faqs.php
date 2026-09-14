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
	require_once(__DIR__.'/../../class2.php');
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



require_once (e_HANDLER."userclass_class.php");
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

$cobj 	= new comment;


$action = $id = $idx = '';




$from = (vartrue($from) ? $from : 0);
$amount = 50;

$faqpref        = e107::getPlugConfig('faqs')->getPref();
$canAskQuestion = check_class(varset($faqpref['submit_question'], e_UC_NOBODY));
$tokenOk        = !e_session::modeUsesToken() || !empty($_POST['e-token']);

if (!empty($_POST['submit_a_question']) && $canAskQuestion && $tokenOk)
{
	$existing = $sql->createQueryBuilder()->from('faqs')
		->where('faq_answer', '')->where('faq_author_ip', USERIP)->count();

	if(!empty($faqpref['submit_question_limit']) && $existing >= $faqpref['submit_question_limit'])
	{
		e107::getMessage()->setTitle(LAN_WARNING,E_MESSAGE_INFO)->addInfo(LAN_FAQS_005);
	}
	else
	{
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

		if($sql->createQueryBuilder()->insert('faqs')->insertGetId($insert))
		{
			$message = !empty($faqpref['submit_question_acknowledgement']) ? e107::getParser()->toHTML($faqpref['submit_question_acknowledgement'],true, 'BODY') : LAN_FAQS_004;
			e107::getMessage()->addSuccess($message);
		}
	}
}

if (isset($_POST['commentsubmit']))
{
	$pid = (IsSet($_POST['pid']) ? $_POST['pid'] : 0);
	$cobj->enter_comment($_POST['author_name'], $_POST['comment'], "faq", $idx, $pid, $_POST['subject']);
}

// Actions +++++++++++++++++++++++++++++

	$faq = new faq;

	if (empty($action) || $action == "main")
	{
		if(vartrue($faqpref['classic_look']))
		{
			$ftmp = $faq->show_existing_parents($action, '', $id, $from, $amount);
			$caption = defset('LAN_PLUGIN_FAQS_FUNCTIONNAME',"FAQ Categories");
		}
		else
		{
			$srch = vartrue($_GET['srch']);
			$ftmp = $faq->view_all($srch);
			$caption = defset('LAN_FAQS_011', 'FAQ');

		}

		$pageTitle = '';

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

	//	e107::getMessage()->addDebug("TITLE: " . $pageTitle);

		e107::title($pageTitle);

		if(!empty($ftmp['pagedescription']))
		{
			e107::meta('og:description', $ftmp['pagedescription']);
		}


		require_once (HEADERF);
				
		e107::getRender()->tablerender($ftmp['caption'], $ftmp['text']);
		
	}

	if($action == "cat" && $idx)
	{
		 $ftmp = $faq->view_faq($idx) ;
		 if(!defined("e_PAGETITLE"))
		 {
		    e107::title( LAN_FAQS_011." - ". $ftmp['title']);
		 }
		 require_once(HEADERF);
		 e107::getRender()->tablerender($ftmp['caption'], $ftmp['text']);
	}

	if ($action == "cat")
	{
		$ftmp = $faq->view_cat_list($action, $id);

		e107::title( strip_tags($ftmp['title'].$ftmp['caption']));
		require_once (HEADERF);
		e107::getRender()->tablerender($ftmp['caption'], $ftmp['text']);
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
	}



	function view_all($srch) // new funtion to render all FAQs
	{
		e107::canonical('faqs', 'index');
		$tp = e107::getParser();
		$ret = array();

		$template = e107::getTemplate('faqs');
		$this->template = $template;
	
		$this->sc = e107::getScBatch('faqs',TRUE);
		
		$text = $tp->parseTemplate($template['start'], true, $this->sc); // header

		
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

		$item = false;

		$removeUrl = e107::url('faqs','index');

		$qb = $sql->createQueryBuilder();
		$qb->select('f.*', 'cat.*')
			->from('faqs', 'f')
			->leftJoin('faqs_info', 'cat', $qb->expr()->compareColumns('f.faq_parent', 'cat.faq_info_id'))
			->whereIn('cat.faq_info_class', explode(',', USERCLASS_LIST));

		// A single, mutually-exclusive search predicate (later branches override earlier ones,
		// preserving the legacy "$insert is overwritten, not appended" behaviour).
		$searchPredicate = null;

		if(!empty($srch))
		{
			$srch = $tp->toDB($srch);
			$searchPredicate = $qb->expr()->anyOf(
				$qb->expr()->like('f.faq_question', '%'.$srch.'%'),
				$qb->expr()->like('f.faq_answer', '%'.$srch.'%'),
				'FIND_IN_SET ('.$qb->createNamedParameter($srch).', f.faq_tags)'
			);


		//	$message = "<span class='label label-lg label-info'>".$srch." <a class='e-tip' title='".LAN_FAQS_006."' href='".$removeUrl."'>×</a></span>";

		//	e107::getMessage()->setClose(false,E_MESSAGE_INFO)->setTitle(LAN_FAQS_002,E_MESSAGE_INFO)->addInfo($message);
		//	$text = e107::getMessage()->render();
		}

		if(!empty($_GET['id'])) // pull out just one specific FAQ.
		{
			$srch = intval($_GET['id']);
		//	$searchPredicate = $qb->expr()->eq('f.faq_id', $srch);
			$item = $srch;
		}

		if(!empty($_GET['cat']))
		{
			$srch = $tp->toDB($_GET['cat']);
			$searchPredicate = $qb->expr()->eq('cat.faq_info_sef', $srch);
		}

		if(!empty($_GET['tag']))
		{
			$srch = $tp->toDB($_GET['tag']);


			$searchPredicate = $qb->expr()->findInSet('f.faq_tags', $srch);

			$message = "<span class='label label-lg label-info'>".$srch." <a class='e-tip' title='".LAN_FAQS_006."' href='".$removeUrl."'>×</a></span>";

			e107::getMessage()->setClose(false,E_MESSAGE_INFO)->setTitle(LAN_FAQS_002,E_MESSAGE_INFO)->addInfo($message);
			$text = e107::getMessage()->render();
		}

		if($searchPredicate !== null)
		{
			$qb->where($searchPredicate);
		}


		list($orderBy, $ascdesc) = explode('-', vartrue($this->pref['orderby'],'faq_order-ASC'));

		$qb->orderBy('cat.faq_info_order')->addOrderBy('f.'.$orderBy, $ascdesc);

		if(!$data = $qb->fetchAll())
		{
			$message = 	(!empty($srch)) ? e107::getParser()->lanVars(LAN_FAQS_008, $srch)."<a class='e-tip' title='".LAN_FAQS_007."' href='".$removeUrl."'>".LAN_FAQS_007."</a>" : LAN_FAQS_003;
			return "<div class='alert alert-warning alert-block'>".$message."</div>" ; 
		}
		
		// -----------------
		
		$FAQ_LISTALL = e107::getTemplate('faqs', true, 'all');

		$schemaTemplate = e107::getTemplate('faqs', true, 'schema');


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
		$start = false;


		if(isset($this->pref['list_type']) && $this->pref['list_type'] == 'ol')
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
				$start = true;
			}

			$text .= $tp->parseTemplate($FAQ_LISTALL['item'], true, $sc);
			$prevcat = $rw['faq_info_order'];
			$sc->counter++;
		}



		$text .= ($start) ? $tp->parseTemplate($FAQ_LISTALL['end'], true, $sc) : "";

		if(!empty($schemaTemplate))
		{
			if(isset($schemaTemplate['end']) && isset($schemaTemplate['item']) && isset($schemaTemplate['start']))
			{
				$schemaTpl =  $schemaTemplate['start']."\n".$schemaTemplate['item']."\n".$schemaTemplate['end'];
				$schema = $tp->parseSchemaTemplate($schemaTpl, true, $sc, $data);
			}
			elseif(is_string($schemaTemplate))
			{
				$schema = $tp->parseSchemaTemplate($schemaTemplate, true, $sc, $data);
			}

			if(!empty($schema))
			{
				e107::schema($schema);
			}
		}

		return $text;
		
	}




// -------------  Everything below here is kept for backwards-compatability 'Classic Look' ------------


	function view_cat_list($action, $id)
	{
		global $ns,$row,$FAQ_LIST_START,$FAQ_LIST_LOOP,$FAQ_LIST_END;

		$tp 	= e107::getParser();
		$sql 	= e107::getDb();
		$sc 	= e107::getScBatch('faqs',TRUE);

		$qb = $sql->createQueryBuilder();
		$qb->select('f.*', 'cat.*')->from('faqs', 'f')->leftJoin('faqs_info', 'cat', $qb->expr()->compareColumns('f.faq_parent', 'cat.faq_info_id'))->where('f.faq_parent', (int) $id)->execute();
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

		$qb = $sql->createQueryBuilder();
		$rows = $qb
			->select('dc.*')->selectAggregate('COUNT', 'd.faq_id', 'f_count')->selectAggregate('COUNT', 'd2.faq_id', 'f_subcount')
			->from('faqs_info', 'dc')
			->leftJoin('faqs', 'd', $qb->expr()->compareColumns('dc.faq_info_id', 'd.faq_parent'))
			->leftJoin('faqs_info', 'dc2', $qb->expr()->compareColumns('dc2.faq_info_parent', 'dc.faq_info_id'))
			->leftJoin('faqs', 'd2', $qb->expr()->compareColumns('dc2.faq_info_id', 'd2.faq_parent'))
			->whereIn('dc.faq_info_class', explode(',', USERCLASS_LIST))
			->groupBy('dc.faq_info_id')
			->orderBy('dc.faq_info_order')->addOrderBy('dc.faq_info_parent')
			->fetchAll();

		$text .= $FAQ_CAT_START;

		foreach ($rows as $row)
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

		$sql->createQueryBuilder()->select('*')->from('faqs')->where('faq_id', (int) $idx)->limit(1)->execute();
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
			unset($text);

			if (!is_object($sql2))
			{
				$sql2 = new db;
			}
			$commentQuery = $sql2->createQueryBuilder();
			$commentQuery->select('*')->from('comments')
				->where('comment_item_id', $idx)
				->where($commentQuery->expr()->anyOf(
					$commentQuery->expr()->eq('comment_type', $table),
					$commentQuery->expr()->eq('comment_type', '3')
				));
			if($pref['nested_comments'])
			{
				$commentQuery->where('comment_pid', '0');
			}
			$commentQuery->orderBy('comment_datestamp');
			$comments = $commentQuery->fetchAll();
			if ($comment_total = count($comments))
			{
				$width = 0;
				foreach ($comments as $row)
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

        $text_menu .="</div>";

		$text_menu .= "<div style='text-align:center'><br />".$tp->parseTemplate("{SEARCH=faqs}")."</div>";

       	return $text_menu;

		// require_once (FOOTERF);
	}

}

