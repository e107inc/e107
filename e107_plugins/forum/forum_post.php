<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum Posting
 *
*/


if(!defined('e107_INIT'))
{
	require_once('../../class2.php');
}

if(USER)
{
	define('e_TINYMCE_TEMPLATE', 'member'); // allow images / videos.
}
else
{
	define('e_TINYMCE_TEMPLATE', 'public');
}

define('NAVIGATION_ACTIVE','forum'); // ??

$tp = e107::getParser();
$ns = e107::getRender();
$mes = e107::getMessage();

if (!e107::isInstalled('forum'))
{
	e107::redirect();
	exit;
}

//e107::lan('forum','English_front');
e107::lan('forum', "front", true);
e107::css('forum','forum.css');




class forum_post_handler
{
	private $forumObj;
	private $action;
	private $id;
	private $data;

	function __construct()
	{



		$this->checkForumJump();

		require_once(e_PLUGIN.'forum/forum_class.php'); // includes LAN file.
		$forum = new e107forum();
		$this->forumObj = $forum;

		$this->action   = trim($_GET['f']); // action: rp|quote|nt|edit etc.
		$this->id       = (int) $_GET['id']; // forum thread/topic id.
		$this->post     = (int) $_GET['post']; // post ID if needed.


		$moderatorUserIds = $forum->getModeratorUserIdsByPostId($this->post);
		define('MODERATOR', (USER && in_array(USERID, $moderatorUserIds)));


		$this->data = $this->processGet();

		$this->checkPerms($this->data['forum_id']);

		if($this->processPosted() === false)
		{
			return false;
		}

		if($this->action == 'report')
		{
			$this->renderFormReport();
		}
		elseif($this->action == 'move')
		{
			$this->renderFormMove();
		}
		elseif($this->action == 'split')
		{
			$this->renderFormSplit();
		}
		else
		{
			$this->renderForm();
		}

		if(E107_DEBUG_LEVEL > 0)
		{
			e107::getMessage()->addInfo(print_a($this->data,true));
			echo e107::getMessage()->render();
		}

	}


	function checkForumJump()
	{
		/*if(isset($_POST['fjsubmit']))
		{
			$this->redirect(e107::getUrl()->create('forum/forum/view', array('id'=>(int) $_POST['forumjump']), '', 'full=1&encode=0'));
			exit;
		}*/

		if (!e_QUERY || empty($_GET['id']))
		{
			$url = e107::url('forum','index',null,'full');
			$this->redirect($url);
		//	header('Location:'.e107::getUrl()->create('forum/forum/main', array(), 'full=1&encode=0'));
			exit;
		}

	}


	/**
	 * Handle all _GET request actions.
	 */
	function processGet()
	{
		switch($this->action)
		{
			case 'rp':
				$thread                 = $this->forumObj->threadGet($this->id, false);
				$extra                  = $this->forumObj->forumGet($thread['thread_forum_id']);
				$data                   = array_merge($thread,$extra);
				$data['action']         = $this->action;
				$this->setPageTitle($data);
				return $data;
				break;

			case 'nt':
				$forumInfo              = $this->forumObj->forumGet($this->id);
				$forumInfo['action']    = $this->action;
				$this->setPageTitle($forumInfo);
				return $forumInfo;
				break;

			case 'edit':
			case "quote":
			case "report":
			case 'split':
				$postInfo               = $this->forumObj->postGet($this->post, 'post');
				$forumInfo              = $this->forumObj->forumGet($postInfo['post_forum']);
				$data                   = array_merge($postInfo ,$forumInfo);
				$data['action']         = $this->action;
				$data['initial_post']   = $this->forumObj->threadDetermineInitialPost($this->post);
				$this->setPageTitle($data);
				return $data;
				break;

			case 'move':
				$thread                 = $this->forumObj->threadGet($this->id, true);
				$extra                  = $this->forumObj->postGet($this->id,0,1);  // get first post.
				$data                   = array_merge($thread,$extra[0]);
				$data['action']         = $this->action;
				$this->setPageTitle($data);
				return $data;
				break;

			default:
				$url = e107::url('forum','index',null,'full');
				$this->redirect($url);
			//	header("Location:".e107::getUrl()->create('forum/forum/main', array(), 'full=1&encode=0'));
				exit;
		}
	}


	/**
	 * Handle all _POST actions.
	 */
	function processPosted()
	{

		if(!empty($_POST['action'])) // override from 'quote' mode to 'rp' mode.
		{
			$this->action = $_POST['action'];
		}

		if(isset($_POST['newthread']) || isset($_POST['reply']))
		{
			$this->insertPost();
		}

		if(isset($_POST['update_thread']))
		{
			$this->updateThread();
		}

		if(!empty($_POST['move_thread']))
		{
			$this->moveThread($_POST);
		}

		if(!empty($_POST['split_thread']))
		{
			$this->splitThread($_POST);
			return false;
		}

		if(isset($_POST['update_reply']))
		{
			$this->updateReply();
		}

		if(!empty($_POST['fpreview']))
		{
			$this->renderPreview();
		}

		if(isset($_POST['submitpoll']))
		{
			$this->submitPoll();
		}

		if(!empty($_POST['report_thread']))
		{
			$this->submitReport();
		}

		return true;
	}


	/**
	 * @param $url
	 */
	private function redirect($url)
	{

		if(E107_DEBUG_LEVEL > 0)
		{
			require_once(HEADERF);

			e107::getRender()->tablerender('Debug', "Redirecting to: <a href='".$url."'>".$url."</a>");
			echo e107::getMessage()->render();
			require_once(FOOTERF);
			exit;

		}

		e107::getRedirect()->go($url);

	}


	/**
	 *
	 */
	function submitPoll()
	{

		require_once(e_PLUGIN.'poll/poll_class.php');
		$poll = new poll;

		require_once(HEADERF);
		$template = $this->getTemplate('posted');
		echo $template['poll'];
		require_once(FOOTERF);
		exit;

	}


	/**
	 * Report a topic post.
	 */
	private function submitReport()
	{
		$tp = e107::getParser();
		$sql = e107::getDb();

		$report_add = $tp->toDB($_POST['report_add']);

		$insert = array(
			'gen_id'        =>	0,
			'gen_type'      =>	'reported_post',
			'gen_datestamp' =>	time(),
			'gen_user_id'   =>	USERID,
			'gen_ip'        =>	$tp->toDB($this->data['thread_name']),
			'gen_intdata'   =>	intval($this->data['thread_id']),
			'gen_chardata'  =>	$report_add,
		);

		//	$url = e107::getUrl()->create('forum/thread/post', array('id' => $postId, 'name' => $postInfo['thread_name'], 'thread' => $threadId)); // both post info and thread info contain thread name

		$url = e107::url('forum','topic', $this->data);
		$result = $sql->insert('generic', $insert);

		if($result)
		{
			$text = "<div class='alert alert-block alert-success'><h4>".LAN_FORUM_2021 . "</h4><a href='{$url}'>".LAN_FORUM_2022.'</a></div>';
		}
		else
		{
			$text = "<div class='alert alert-block alert-error alert-danger'><h4>".LAN_FORUM_2021 . "</h4><a href='{$url}'>".LAN_FORUM_2022.'</a></div>';
		}

		$link = "{e_PLUGIN}forum/forum_admin.php?mode=post&action=list&id=".intval($result);


		$report = LAN_FORUM_2018." ".SITENAME." : ".$link . "\n
					".LAN_FORUM_2019.": ".USERNAME. "\n" . $report_add;
		//$subject = LAN_FORUM_2020." ". SITENAME;

		//e107::getNotify()->send('forum_post_rep', $subject, $report);
		e107::getEvent()->trigger('user_forum_post_report', $report);
		e107::getRender()->tablerender(LAN_FORUM_2023, $text, 'forum-post-report');
	}




	function setPageTitle($data)
	{
		$tp = e107::getParser();

		$data['forum_name'] = $tp->toHTML($data['forum_name'], true);

		define('e_PAGETITLE', ($this->action == 'rp' ? LAN_FORUM_3003.": ".$data['thread_name'] : LAN_FORUM_1018).' / '.$data['forum_name'].' / '.LAN_FORUM_1001);


	}




	function checkPerms($forumId)
	{
		$mes = e107::getMessage();
		$ns = e107::getRender();

		if (!$this->forumObj->checkPerm($forumId, 'post')) // check user has permission to post to this thread.
		{
			require_once(HEADERF);
			$mes->addError(LAN_FORUM_3001);
			$mes->addDebug(print_a($this->data, true));
			$ns->tablerender(LAN_FORUM_1001, $mes->render(), 'forum-post-unauthorized');
			require_once(FOOTERF);
			exit;
		}

		$data  = $this->forumObj->threadGet($this->id, false);

		if ($this->action != 'nt' && !$data['thread_active'] && !MODERATOR) // check the thread is active.
		{
			require_once(HEADERF);
			$mes->addError(LAN_FORUM_3002);
			$ns->tablerender(LAN_FORUM_1001, $mes->render(), 'forum-post-locked');
			require_once(FOOTERF);
			exit;
		}



	}


	/**
	 * @return string
	 */
	function getTemplate($type = 'post')
	{
		$pref = e107::pref('core');

		global $FORUMPOST, $subjectbox, $userbox, $poll_form, $fileattach, $fileattach_alert; // needed for BC.

//--		$FORUM_POST_TEMPLATE        = array();
//--		$FORUM_POSTED_TEMPLATE      = array();
		$FORUMREPLYPOSTED           = '';
		$FORUMTHREADPOSTED          = '';
		$FORUMPOLLPOSTED            = '';

//		$file = "forum_".$type."_template.php";

//    var_dump ($type);
//    var_dump (e107::getTemplate('forum', 'forum_'.$type));
		$template = e107::getTemplate('forum', 'forum_'.$type);
//--		if($template = e107::getTemplate('forum', 'forum_'.$type))
//--		{
//--		  	$FORUM_POST_TEMPLATE = $template;
//--		}
//--		elseif (empty($FORUMPOST) && empty($FORUMREPLYPOSTED) && empty($FORUMTHREADPOSTED))
		if (empty($template) && empty($FORUMPOST) && empty($FORUMREPLYPOSTED) && empty($FORUMTHREADPOSTED))
		{
  		$file = "forum_".$type."_template.php";
			if (is_readable(THEME.$file))
			{
				include_once(THEME.$file);
			}
			elseif(is_readable(THEME.'templates/forum/'.$file))
			{
				include_once(THEME.'templates/forum/'.$file);
			}
			else
			{
				include_once(e_PLUGIN.'forum/templates/'.$file);
			}
		}



		// ----------------- Legacy -------------------------

		if(!defined("USER_WIDTH")){ define("USER_WIDTH","width:95%"); }

		if(empty($userbox))
		{
			$userbox = "<tr>
			<td class='forumheader2' style='width:20%'>".LAN_FORUM_3010."</td>
			<td class='forumheader2' style='width:80%'>
			<input class='tbox form-control' type='text' name='anonname' size='71' value='".vartrue($anonname)."' maxlength='20' style='width:95%' />
			</td>
			</tr>";
		}

		if(empty($subjectbox))
		{
			$subjectbox = "<tr>
			<td class='forumheader2' style='width:20%'>".LAN_FORUM_3011."</td>
			<td class='forumheader2' style='width:80%'>
			<input class='tbox form-control' type='text' name='subject' size='71' value='".vartrue($subject)."' maxlength='100' style='width:95%' />
			</td>
			</tr>";
		}

		if(empty($fileattach))
		{
			$fileattach = "
			<tr>
				<td colspan='2' class='nforumcaption2 fcaption'>".($pref['image_post'] ? LAN_FORUM_3012 : LAN_FORUM_3013)."</td>
			</tr>
			<tr>
				<td style='width:20%' class='forumheader3'>".LAN_FORUM_3014."</td>
				<td style='width:80%' class='forumheader3'>".str_replace(array('[', ']'), array('<b>', '</b>'), LAN_FORUM_3015)."<br>".LAN_FORUM_3016.": ".vartrue($allowed_filetypes)." <br />".LAN_FORUM_3017."<br />".LAN_FORUM_3018.": ".(vartrue($max_upload_size) ? $max_upload_size." ".LAN_FORUM_3019 : ini_get('upload_max_filesize'))."
					<br />
					<div id='fiupsection'>
					<span id='fiupopt'>
						<input class='tbox' name='file_userfile[]' type='file' size='47' />
					</span>
					</div>
					<input class='btn btn-default btn-secondary button' type='button' name='addoption' value='".LAN_FORUM_3020."' onclick=\"duplicateHTML('fiupopt','fiupsection')\" />
				</td>
			</tr>
			";

		}
		// If the upload directory is not writable, we need to alert the user about this.
		if(empty($fileattach_alert))
		{
			$fileattach_alert = "
			<tr>
				<td colspan='2' class='nforumcaption2'>".($pref['image_post'] ? LAN_FORUM_3012 : LAN_FORUM_3013)."</td>
			</tr>
			<tr>
				<td colspan='2' class='forumheader3'>".str_replace('[x]', e_FILE."public", LAN_FORUM_3021)."</td>
			</tr>\n";
		}
		// ------------

		if(empty($FORUMPOST))
		{
			$FORUMPOST = "
			<div style='text-align:center'>
			<div class='spacer'>
			{FORMSTART}
			<table style='".USER_WIDTH."' class='fborder table'>
			<tr>
			<td colspan='2' class='fcaption'>{BACKLINK}
			</td>
			</tr>
			{USERBOX}
			{SUBJECTBOX}
			<tr>
			<td class='forumheader2' style='width:20%'>{POSTTYPE}</td>
			<td class='forumheader2' style='width:80%'>
			{POSTBOX}<br />
			{EMAILNOTIFY}<br />
			{NOEMOTES}<br />
			{POSTTHREADAS}
			</td>
			</tr>
			{POLL}
			{FILEATTACH}

			<tr style='vertical-align:top'>
			<td colspan='2' class='forumheader' style='text-align:center'>
			{BUTTONS}
			</td>
			</tr>
			</table>
			{FORMEND}

			<table style='".USER_WIDTH."'>
			<tr>
			<td>
			{FORUMJUMP}
			</td>
			</tr>
			</table>
			</div></div>
			";
		}

		if(empty($FORUMPOST_REPLY))
		{
			$FORUMPOST_REPLY = "
			<div style='text-align:center'>
			<div class='spacer'>
			{FORMSTART}
			<table style='".USER_WIDTH."' class='fborder table'>
			<tr>
			<td colspan='2' class='fcaption'>{BACKLINK}
			</td>
			</tr>
			{USERBOX}
			{SUBJECTBOX}
			<tr>
			<td class='forumheader2' style='width:20%'>{POSTTYPE}</td>
			<td class='forumheader2' style='width:80%'>
			{POSTBOX}<br />
			{EMAILNOTIFY}<br />
			{NOEMOTES}<br />
			{POSTTHREADAS}
			</td>
			</tr>

			{POLL}

			{FILEATTACH}

			<tr style='vertical-align:top'>
			<td colspan='2' class='forumheader' style='text-align:center'>
			{BUTTONS}
			</td>
			</tr>
			</table>
			{FORMEND}

			<table style='".USER_WIDTH."'>
			<tr>
			<td>
			{FORUMJUMP}
			</td>
			</tr>
			</table>
			</div></div>
			<div style='text-align:center'>
			{THREADTOPIC}
			{LATESTPOSTS}
			</div>
			";
		}

		if(empty($LATESTPOSTS_START))
		{
			$LATESTPOSTS_START = "
			<table style='".USER_WIDTH."' class='fborder table'>
			<tr>
			<td colspan='2' class='fcaption' style='vertical-align:top'>".str_replace('[y]', "{LATESTPOSTSCOUNT}", LAN_FORUM_3022)."</td>
			</tr>";
		}

		if(empty($LATESTPOSTS_POST))
		{
			$LATESTPOSTS_POST = "
			<tr>
			<td class='forumheader3' style='width:20%;vertical-align:top'><b>{POSTER}</b></td>
			<td class='forumheader3' style='width:80%'>
				<div class='smallblacktext' style='text-align:right'>".IMAGE_post2." {THREADDATESTAMP}</div>
				{POST}
			</td>
			</tr>
			";
		}

		if(empty($LATESTPOSTS_END))
		{
			$LATESTPOSTS_END = "
			</table>
			";
		}

		if(empty($THREADTOPIC_REPLY))
		{
			$THREADTOPIC_REPLY = "
			<table style='".USER_WIDTH."' class='fborder table'>
			<tr>
				<td colspan='2' class='fcaption' style='vertical-align:top'>".LAN_FORUM_1003."</td>
			</tr>
			<tr>
				<td class='forumheader3' style='width:20%;vertical-align:top'><b>{POSTER}</b></td>
				<td class='forumheader3' style='width:80%'>
					<div class='smallblacktext' style='text-align:right'>".IMAGE_post2." {THREADDATESTAMP}</div>
					{POST}
				</td>
			</tr>
			</table>
			";
		}


		// -------------------------------- End Legacy Code ----------------------------------//





//--		if($type == 'post' || $type == 'posted')
		if($template)
		{
//--			$template= (deftrue('BOOTSTRAP')) ? $FORUM_POST_TEMPLATE : array('form'=>$FORUMPOST);
			$template= (deftrue('BOOTSTRAP')) ? $template : array('form'=>$FORUMPOST);
		//	print_a($template);
			return $this->upgradeTemplate($template);
		}
//--		else
//--		{
//--			if (deftrue('BOOTSTRAP')) //v2.x
//--			{
//--				return $FORUM_POSTED_TEMPLATE;
//--			}
//--			else //v1.x
//--			{
				return array(
					 "reply"    => $FORUMREPLYPOSTED,
					 "thread"   => $FORUMTHREADPOSTED,
					 "poll"     => $FORUMPOLLPOSTED
				);
//--			}
//--		}
	}


	private function upgradeTemplate($template)
	{
		$arr = array(
			'POSTOPTIONS'       => "FORUM_POST_OPTIONS",
			'POSTOPTIONS_LABEL' => "FORUM_POST_OPTIONS_LABEL",
			'POLL'              => 'FORUM_POST_POLL',
			'FORUM_AUTHOR'      => 'FORUM_POST_AUTHOR',
			'FORUM_SUBJECT'     => 'FORUM_POST_SUBJECT',
			'BUTTONS'           => 'FORUM_POST_BUTTONS',
			'FORMSTART'         => 'FORUM_POST_FORM_START',
			'FORMEND'           => 'FORUM_POST_FORM_END',
			'POSTBOX'           => 'FORUM_POST_TEXTAREA',
			'EMAILNOTIFY'       => 'FORUM_POST_EMAIL_NOTIFY',
			'BACKLINK'          => 'FORUM_POST_BREADCRUMB',
			'POSTTYPE'          => 'FORUM_POST_TEXTAREA_LABEL'
		);

		foreach($arr as $old => $new)
		{
			//$template = str_replace("{".$old."}", "{".$new."}", $template);
			$reg = '/\{'.$old.'((?:=|:)?[^\}]*)\}/';  // handle variations.
			$repl = '{'.$new.'$1}';
			$template = preg_replace($reg,$repl, $template);

		}

	//	print_a($template);

		return $template;

	}


	private function renderBreadcrumb()
	{
		$sc  = e107::getScBatch('post', 'forum')->setScVar('forum', $this->forumObj)->setScVar('threadInfo', vartrue($this->data))->setVars($this->data);
		return  e107::getParser()->parseTemplate("<div class='row-fluid'><div>{FORUM_POST_BREADCRUMB}</div></div>",true,$sc);

	}


	private function renderFormSplit()
	{
		if(!deftrue('MODERATOR'))
		{
			return;
		}


		$frm = e107::getForm();

		$tp = e107::getParser();
		$ns = e107::getRender();


		$text = $this->renderBreadcrumb();


		$text .= e107::getMessage()->setTitle(LAN_FORUM_8015,E_MESSAGE_ERROR)->addError( LAN_FORUM_8014 )->render();

			$text .= "
		<form class='forum-horizontal' method='post' action='".e_REQUEST_URI."'>
		<div>
		<table class='table table-striped'>
		<tr><td>".LAN_FORUM_3050."</td>
		<td><div class='alert alert-warning' style='margin:0'>".$tp->toHTML($this->data['post_entry'], true)."</div></td>
		</tr>

		<tr>
		<td>".LAN_FORUM_3051.": </td>
		<td>".$this->forumSelect('forum_split',$this->data['forum_id'], 'required=1')."

		</td>
		</tr>
		<tr>
		<td >".LAN_FORUM_3042."</td>
		<td>

		".$frm->text('new_thread_title', $tp->toForm($this->data['thread_name'], 250))."

		</div></td>
		</tr>
		</table>
		<div class='center'>
		<input class='btn btn-primary button' type='submit' name='split_thread' value=\"".LAN_FORUM_3052."\" />
		<a class='btn btn-default btn-secondary button'  href='".$_SERVER['HTTP_REFERER']."' >".LAN_CANCEL."</a>
		</div>

		</div>
		</form>";


		$ns->tablerender(LAN_FORUM_3052, $text, 'forum-post-split');


	}





	/**
	 * Render a drop-down list of forums.
	 * @param $name
	 * @param mixed $curVal
	 * @param string|array $opts
	 * @return string
	 */
	private function forumSelect($name, $curVal=null, $opts=null)
	{
		$sql = e107::getDb();

		$qry = "
		SELECT f.forum_id, f.forum_name, fp.forum_name AS forum_parent, sp.forum_name AS sub_parent
		FROM `#forum` AS f
		LEFT JOIN `#forum` AS fp ON f.forum_parent = fp.forum_id
		LEFT JOIN `#forum` AS sp ON f.forum_sub = sp.forum_id
		WHERE f.forum_parent != 0
		ORDER BY f.forum_parent ASC, f.forum_sub, f.forum_order ASC
		";

		$fList = $sql->retrieve($qry,true);

		$opts = array();
		$currentName = "";

		foreach($fList as $f)
		{
			if(substr($f['forum_name'], 0, 1) != '*')
			{
				$f['sub_parent'] = ltrim($f['sub_parent'], '*');
				$for_name = $f['forum_parent'].' &gg; ';
				$for_name .= ($f['sub_parent'] ? $f['sub_parent'].' &gg; ' : '');
				$for_name .= $f['forum_name'];

				if($this->data['forum_id'] == $f['forum_id'])
				{
					$for_name .= LAN_FORUM_8016;
					$currentName = $for_name;
					continue;
				}

				$id = $f['forum_id'];
				$opts[$id] = $for_name;
			}
		}


		return e107::getForm()->select($name, $opts, $curVal, $opts, $currentName);
	}


	/**
	 * Render Move Form.
	 */
	private function renderFormMove()
	{
		if(!deftrue('MODERATOR'))
		{
			return;
		}

		$frm = e107::getForm();
		$tp = e107::getParser();
		$ns = e107::getRender();

		$text = $this->renderBreadcrumb();

		$text .= "
		<form class='forum-horizontal' method='post' action='".e_REQUEST_URI."'>
		<div>
		<table class='table table-striped'>
		<tr>
		<td>".LAN_FORUM_3011.": </td>
		<td>
		".$tp->toHTML($this->data['thread_name'],true)."
		</td>
		</tr>
		<tr><td></td>
		<td><div class='alert alert-warning'>".$tp->toHTML($this->data['post_entry'], true)."</div></td></tr>

		<tr>
		<td>".LAN_FORUM_5019.": </td>
		<td>".$this->forumSelect('forum_move', $this->data['forum_id'], 'required=1')."

		</td>
		</tr>
		<tr>
		<td >".LAN_FORUM_5026."</td>
		<td><div class='radio'>
		".$frm->radio('rename_thread','none',true, 'label='.LAN_FORUM_5022)."
		</div>
		<div class='radio'>
		".$frm->radio('rename_thread', 'add', false, array('label'=> $tp->lanVars(LAN_FORUM_5024,'<b> ['.LAN_FORUM_5021.']</b> '))). "
		</div>
		<div class='radio'>".$frm->radio('rename_thread','rename', false, array('label'=>LAN_FORUM_5025))."
		".$frm->text('newtitle', $tp->toForm($this->data['thread_name'], 250))."
		</div>
		</div></td>
		</tr>
		</table>
		<div class='center'>
		<input class='btn btn-primary button' type='submit' name='move_thread' value='".LAN_FORUM_5019."' />
		<a class='btn btn-default btn-secondary button'  href='".$_SERVER['HTTP_REFERER']."' >".LAN_CANCEL."</a>
		</div>

		</div>
		</form>";


		$ns->tablerender(LAN_FORUM_5019, $text, 'forum-post-move');



	}




	function renderForm()
	{
		$data       = $this->data;
		$template   = $this->getTemplate();
		$sc         = e107::getScBatch('post', 'forum')->setScVar('forum', $this->forumObj)->setScVar('threadInfo', vartrue($data))->setVars($data);

		$sc->wrapper('forum_post');

		$text       = e107::getParser()->parseTemplate($template['form'], true, $sc);

		$caption = null;

		if(!empty($template['caption']))
		{
			$caption =  e107::getParser()->parseTemplate($template['caption'], true, $sc);
		}

		$this->render($text, $caption);

		if(empty($data))
		{
			e107::getMessage()->addError("No Data supplied");
		}


	}


	function renderFormReport()
	{
		if(!empty($_POST['report_thread']))
		{
			return false;
		}

		$tp = e107::getParser();
		$frm = e107::getForm();

		$thread_name = e107::getParser()->toHTML($this->data['thread_name'], true, 'no_hook, emotes_off');
	//	define('e_PAGETITLE', LAN_FORUM_1001.' / '.LAN_FORUM_2024.': '.$thread_name);
	//	$url = e107::getUrl()->create('forum/thread/post', array('id' => $postId, 'name' => $postInfo['thread_name'], 'thread' => $threadId));
	//	$actionUrl = e107::getUrl()->create('forum/thread/report', "id={$threadId}&post={$postId}");

		$actionUrl = e107::url('forum','post')."?f=report&amp;id=".$this->data['thread_id']."&amp;post=".$this->data['post_id'];


		if(deftrue('BOOTSTRAP')) //v2.x
		{
			$text = $this->renderBreadcrumb();

			$text .= $frm->open('forum-report-thread','post');
			$text .= "
							<div>
								<div class='alert alert-block alert-warning'>
								<h4>".LAN_FORUM_2025.': '.$thread_name."</h4>
									".LAN_FORUM_2027."<br />".str_replace(array('[', ']'), array('<b>', '</b>'), LAN_FORUM_2028)."
								<a class='pull-right btn btn-xs btn-primary e-expandit' href='#post-info'>".LAN_FORUM_2026."</a>
								</div>
								<div id='post-info' class='e-hideme alert alert-block alert-danger'>
									".$tp->toHtml($this->data['post_entry'],true)."
								</div>
								<div class='form-group' >
									<div class='col-md-12'>
								".$frm->textarea('report_add','',10,35,array('size'=>'xxlarge', 'placeholder'=>LAN_FORUM_2038))."
									</div>
								</div>
								<div class='form-group'>
									<div class='col-md-12'>
									".$frm->button('report_thread',1,'submit',LAN_FORUM_2029)."
									</div>
								</div>

							</div>";

			$text .= $frm->close();
		}
		else //v1.x legacy layout.
		{
			$text = "<form action='".$actionUrl."' method='post'>
						<table class='table' style='width:100%'>
						<tr>
							<td  style='width:50%'>
							".LAN_FORUM_2025.': '.$thread_name." <a  class='e-expandit' href='#post-info'><span class='smalltext'>".LAN_FORUM_2026."</span></a>
							<div id='post-info' class='e-hideme alert alert-block alert-danger'>
									".$tp->toHtml($this->data['post_entry'],true)."
							</div>
							</td>
							<td style='text-align:center;width:50%'></td>
						</tr>
						<tr>
							<td>".LAN_FORUM_2027."<br />".str_replace(array('[', ']'), array('<b>', '</b>'), LAN_FORUM_2028)."</td>
						</tr>
						<tr>
							<td style='text-align:center;'><textarea cols='40' rows='10' class='tbox' name='report_add'></textarea></td>
						</tr>
						<tr>
							<td colspan='2' style='text-align:center;'><br /><input class='btn btn-default btn-secondary button' type='submit' name='report_thread' value='".LAN_FORUM_2029."' /></td>
						</tr>
						</table>
						</form>";



		}


		e107::getRender()->tablerender(LAN_FORUM_2023, $text, 'forum-post-report');




	}




	/**
	 * @param $text
	 */
	function render($text, $caption = false)
	{
		$ns = e107::getRender();

		if ($this->forumObj->prefs->get('enclose'))
		{

			$caption = (!empty($caption)) ? $caption : $this->forumObj->prefs->get('title');
			$ns->tablerender($caption, $text, 'forum-post');
		}
		else
		{
			echo $text;
		}


	}


	/**
	 *
	 */
	function renderPreview()
	{
		global $FORUM_PREVIEW; // BC v1.x

		$tp = e107::getParser();
		$ns = e107::getRender();

		$this->processAttachments();

		require_once(HEADERF);
		if (USER)
		{
			$poster = USERNAME;
		}
		else
		{
			$poster = ($_POST['anonname']) ? $_POST['anonname'] : LAN_ANONYMOUS;
		}

		$postdate = e107::getDate()->convert_date(time(), "forum");
		$tsubject = $tp->post_toHTML($_POST['subject'], true);
		$tpost = $tp->post_toHTML($_POST['post'], true);

		if (empty($tsubject))
		{
			$tsubject = $this->data['thread_name'];
		}

		if ($_POST['poll_title'] != '' && check_class($this->forumObj->prefs->get('poll')))
		{
			require_once(e_PLUGIN."poll/poll_class.php");
			$poll = new poll;
			$poll_text = $poll->render_poll($_POST, 'forum', 'notvoted', true);
		}
		else
		{
			$poll_text = false;
		}

		if (empty($FORUM_PREVIEW))
		{

			if(deftrue('BOOTSTRAP')) //v2.x
			{
				$FORUM_PREVIEW = e107::getTemplate('forum','forum_preview', 'item');
			}
			else //1.x
			{
				if (file_exists(THEME."forum_preview_template.php"))
				{
					require_once(THEME."forum_preview_template.php");
				}
				elseif(file_exists(THEME.'templates/forum/forum_preview_template.php'))
				{
					require_once(THEME.'templates/forum/forum_preview_template.php');
				}
				else
				{
					require_once(e_PLUGIN."forum/templates/forum_preview_template.php");
				}
			}

		}

		$shortcodes = array('PREVIEW_DATE'=>$postdate, 'PREVIEW_SUBJECT'=>$tsubject, 'PREVIEW_POST'=>$tpost);


		$text = $tp->simpleParse($FORUM_PREVIEW,$shortcodes);

		if ($poll_text)
		{
			$ns->tablerender($_POST['poll_title'], $poll_text, 'forum-post-preview-poll');
		}

		$ns->tablerender(LAN_FORUM_3005, $text, 'forum-post-preview');

/*
		if ($this->action == 'edit')
		{
			if ($_POST['subject'])
			{
				$action = 'edit';
			}
			else
			{
				$action = 'rp';
			}
			$eaction = true;
		}
		else if($this->action == 'quote')
		{
			$action = 'rp';
			$eaction = false;
		}*/


	}


	/**
	 * Insert a new thread or a reply/quoted reply.
	 */
	function insertPost()
	{
		$postInfo = array();
		$threadInfo = array();
		$threadOptions = array();

		$fp = new floodprotect;

		if ((isset($_POST['newthread']) && trim($_POST['subject']) == '') || trim($_POST['post']) == '')
		{
			message_handler('ALERT', 5);
		}
		else
		{
			if ($fp->flood('forum_thread', 'thread_datestamp') == false && !ADMIN)
			{
				echo "<script type='text/javascript'>document.location.href='".e_BASE."index.php'</script>\n";
				exit;
			}

			$hasPoll = ($this->action == 'nt' && varset($_POST['poll_title']) && $_POST['poll_option'][0] != '' && $_POST['poll_option'][1] != '');


			if (USER)
			{
				$postInfo['post_user']              = USERID;
				$threadInfo['thread_lastuser']      = USERID;
				$threadInfo['thread_user']          = USERID;
				$threadInfo['thread_lastuser_anon'] = '';
			}
			else
			{
				$postInfo['post_user_anon']         = $_POST['anonname'];
				$threadInfo['thread_lastuser_anon'] = $_POST['anonname'];
				$threadInfo['thread_user_anon']     = $_POST['anonname'];
			}

			$time = time();
			$postInfo['post_entry']                 = $_POST['post'];
			$postInfo['post_forum']                 = $this->data['forum_id'];
			$postInfo['post_datestamp']             = $time;
			$postInfo['post_ip']                    = e107::getIPHandler()->getIP(FALSE);

			$threadInfo['thread_lastpost']          = $time;

			if(isset($_POST['no_emote']))
			{
				$postInfo['post_options']           = serialize(array('no_emote' => 1));
			}

			//If we've successfully uploaded something, we'll have to edit the post_entry and post_attachments
			$newValues = array();

			if($uploadResult = $this->processAttachments())
			{
				foreach($uploadResult as $ur)
				{
					$type = $ur['type'];
					$newValues[$type][] = array('file'=>$ur['file'], 'name'=>$ur['fname'], 'size'=>$ur['size']);
				}

				$postInfo['post_attachments'] = e107::serialize($newValues);
			}
			
			//Allows directly overriding the method of adding files (or other data) as attachments
			if($attachmentsPosted = $this->processAttachmentsPosted())
			{
				$postInfo['post_attachments'] = $attachmentsPosted;
			}	
			
//		var_dump($uploadResult);

			switch($this->action)
			{
				// Reply only.  Add the post, update thread record with latest post info.
				// Update forum with latest post info
				case 'rp':
					$postInfo['post_thread']        = $this->id;
					$newPostId = $this->forumObj->postAdd($postInfo);
					break;

				// New thread started.  Add the thread info (with lastest post info), add the post.
				// Update forum with latest post info
				case 'nt':

					$threadInfo['thread_sticky']    = (MODERATOR ? (int)$_POST['threadtype'] : 0);
					$threadInfo['thread_name']      = $_POST['subject'];
					$threadInfo['thread_forum_id']  = $this->id;
					$threadInfo['thread_active']    = 1;
					$threadInfo['thread_datestamp'] = $time;

					if($hasPoll)
					{
						$threadOptions['poll'] = '1';
					}

					if(is_array($threadOptions) && count($threadOptions))
					{
						$threadInfo['thread_options'] = serialize($threadOptions);
					}
					else
					{
						$threadInfo['thread_options'] = '';
					}

					if($postResult = $this->forumObj->threadAdd($threadInfo, $postInfo))
					{
						$newPostId = $postResult['postid'];
						$newThreadId = $postResult['threadid'];

						$this->data['thread_id'] = $newThreadId;
					//	$this->data['thread_sef'] = $postResult['threadsef'];
						$this->data['thread_sef'] = eHelper::title2sef($threadInfo['thread_name'],'dashl');



						if($_POST['email_notify'])
						{
							$this->forumObj->track('add', USERID, $newThreadId);
						}
					}

					break;
			}

			e107::getMessage()->addDebug(print_a($postInfo,true));
		//	e107::getMessage()->addDebug(print_a($this,true));

			if($postResult === -1 || $newPostId === -1) //Duplicate post
			{
				require_once(HEADERF);
				$message = LAN_FORUM_3006."<br ><a class='btn btn-default' href='".$_SERVER['HTTP_REFERER']."'>".LAN_FORUM_8028."</a>";
				$text = e107::getMessage()->addError($message)->render();
				e107::getRender()->tablerender(LAN_PLUGIN_FORUM_NAME, $text, 'forum-post-duplicate'); // change to forum-title pref.
				require_once(FOOTERF);
				exit;
			}

			$threadId = ($this->action == 'nt' ? $newThreadId : $this->id);


			//If a poll was submitted, let's add it to the poll db
			if ($this->action == 'nt' && varset($_POST['poll_title']) && $_POST['poll_option'][0] != '' && $_POST['poll_option'][1] != '')
			{
				require_once(e_PLUGIN.'poll/poll_class.php');
				$_POST['iid'] = $threadId;
				$poll = new poll;
				$poll->submit_poll(2);
			}

			e107::getCache()->clear('newforumposts');


		//	$postInfo = $this->forumObj->postGet($newPostId, 'post');
		//	$forumInfo = $this->forumObj->forumGet($postInfo['post_forum']);

		//	$threadLink = e107::getUrl()->create('forum/thread/last', $postInfo);
		// 	$forumLink = e107::getUrl()->create('forum/forum/view', $forumInfo);

			$threadLink = e107::url('forum','topic',$this->data,'full')."&amp;last=1";
			$forumLink = e107::url('forum', 'forum', $this->data);

			if ($this->forumObj->prefs->get('redirect'))
			{

				$this->redirect($threadLink);
			//	header('location:'.e107::getUrl()->create('forum/thread/last', $postInfo, array('encode' => false, 'full' => true)));
				exit;
			}
			else
			{
				require_once(HEADERF);
				$template = $this->getTemplate('posted');

				$SHORTCODES = array(
					'THREADLINK'    => $threadLink,
					'FORUMLINK'     => $forumLink
				);


				$txt = (isset($_POST['newthread']) ? $template['thread'] : $template['reply']);

				$txt = e107::getParser()->parseTemplate($txt,true, $SHORTCODES);


				e107::getRender()->tablerender(e_PAGETITLE, e107::getMessage()->render().$txt, 'forum-post');
				require_once(FOOTERF);
				exit;
			}
		}



	}


	private function moveThread($posted)
	{

		if(!deftrue('MODERATOR'))
		{
			e107::getDebug()->log("Move Thread attempted by non-moderator"); // No LAN necessary.
			return false;
		}

		$tp = e107::getParser();
		$mes = e107::getMessage();

		$newThreadTitle = '';
		$newThreadTitleType = 0;

		if($posted['rename_thread'] == 'add')
		{
			$newThreadTitle = '['.LAN_FORUM_5021.']';
		}
		elseif($posted['rename_thread'] == 'rename' && trim($posted['newtitle']) != '')
		{
			$newThreadTitle = $tp->toDB($posted['newtitle']);
			$newThreadTitleType = 1;
		}

		$threadId = intval($_GET['id']);
		$toForum = intval($posted['forum_move']);

		$this->forumObj->threadMove($threadId, $toForum, $newThreadTitle, $newThreadTitleType);

		$message = LAN_FORUM_5005."<br />";// XXX _URL_ thread name

		$url = e107::url('forum','topic', $this->data);
		$text = "<a class='btn btn-primary' href='".$url."'>".LAN_FORUM_5007."</a>";

		$mes->addSuccess($message.$text);
		echo $mes->render();

//	$ns->tablerender(LAN_FORUM_5008, $text);


	}



	private function splitThread($post)
	{
		if(!deftrue('MODERATOR'))
		{
			e107::getDebug()->log("Split Thread attempted by non-moderator"); // No LAN necessary.
			return false;
		}

		$threadInfo = array();
		$threadInfo['thread_sticky']    = 0;
		$threadInfo['thread_name']      = $post['new_thread_title'];
		$threadInfo['thread_forum_id']  = (!empty($post['forum_split'])) ? intval($post['forum_split']) : $this->data['post_forum'];
		$threadInfo['thread_active']    = 1;
		$threadInfo['thread_datestamp'] = $this->data['post_datestamp'];
		$threadInfo['thread_views']      = 0;
		$threadInfo['thread_user']       = $this->data['post_user'];


	//	print_a($this->data);

		if($ret = $this->forumObj->threadAdd($threadInfo, false))
		{

			$urlInfo = $threadInfo;
			$urlInfo['thread_sef'] = $ret['threadsef'];
			$urlInfo['thread_id'] = $ret['threadid'];
			$urlInfo['forum_sef'] = $this->forumObj->getForumSef($threadInfo);

			$newUrl = e107::url('forum','topic', $urlInfo);

			e107::getMessage()->addSuccess("Created new thread <a class='alert-link' href='".$newUrl."'>#".$ret['threadid']."</a>");
			$update = array(
				'post_thread' => $ret['threadid'],
				'post_forum'  => $threadInfo['thread_forum_id'],
				 'WHERE'   => "post_thread = ".$this->data['post_thread']." AND post_id >= ".$this->data['post_id']

			);

			if($result = e107::getDb()->update('forum_post', $update))
			{

				e107::getMessage()->addSuccess("Moved ".$result." posts to topic #". $ret['threadid']);


				// Update old thread.

				if(!$this->forumObj->threadUpdateCounts($this->data['post_thread']))
				{
					e107::getMessage()->addError("Couldn't update thread replies for original topic #". $this->data['post_thread']);
				}

				if(!$this->forumObj->forumUpdateLastpost('thread',$this->data['post_thread']))
				{
					e107::getMessage()->addError("Couldn't update last post user for original topic #". $this->data['post_thread']);

				}

				// Update new thread.

				if(!$this->forumObj->threadUpdateCounts($ret['threadid']))
				{
					e107::getMessage()->addError("Couldn't update thread replies for #". $ret['threadid']);
				}

				if(!$this->forumObj->forumUpdateLastpost('thread',$ret['threadid']))
				{
					e107::getMessage()->addError("Couldn't update last post user for #". $ret['threadid']);

				}

			}

		}

		$sc   = e107::getScBatch('post', 'forum')->setScVar('forum', $this->forumObj)->setScVar('threadInfo', vartrue($this->data))->setVars($this->data);
		$text = e107::getParser()->parseTemplate("<div class='row-fluid'><div>{FORUM_POST_BREADCRUMB}</div></div>",true,$sc);
		$text .= e107::getMessage()->render();


		e107::getRender()->tablerender(LAN_FORUM_3052, $text, 'forum-post-split');
	}



	function updateThread()
	{

		$mes = e107::getMessage();

		if (empty($_POST['subject']) || empty($_POST['post']))
		{
			$mes->addError(LAN_FORUM_3007);
			return;
		}
		else
		{
			if (!$this->isAuthor())
			{
				$mes->addError(LAN_FORUM_3009);
				return;
			}

			$postVals = array();
			$threadVals = array();

			if($uploadResult = $this->processAttachments())
			{
				// $attachments = explode(',', $this->data['post_attachments']);
				$newValues   = e107::unserialize($this->data['post_attachments']);
				foreach($uploadResult as $ur)
				{
				//	$_tmp = $ur['type'].'*'.$ur['file'];
				//	if($ur['thumb']) { $_tmp .= '*'.$ur['thumb']; }
				//	if($ur['fname']) { $_tmp .= '*'.$ur['fname']; }
				//	$attachments[] = $_tmp;

					$type = $ur['type'];
					$newValues[$type][] = array('file'=>$ur['file'], 'name'=>$ur['fname'], 'size'=>$ur['size']);
				}
				$postVals['post_attachments'] = e107::serialize($newValues);
				// $postVals['post_attachments'] = implode(',', $attachments);
			}
			
			//Allows directly overriding the method of adding files (or other data) as attachments
			if($attachmentsPosted = $this->processAttachmentsPosted($this->data['post_attachments']))
			{
				$postVals['post_attachments'] = $attachmentsPosted;
			}	
      
			$postVals['post_edit_datestamp']    = time();
			$postVals['post_edit_user']         = USERID;
			$postVals['post_entry']             = $_POST['post'];

			$threadVals['thread_name'] 	 = $_POST['subject'];
			$threadVals['thread_sticky'] = (MODERATOR ? (int)$_POST['threadtype'] : 0);

			$this->forumObj->threadUpdate($this->data['post_thread'], $threadVals);
			$this->forumObj->postUpdate($this->data['post_id'], $postVals);

			e107::getCache()->clear('newforumposts');

			$url = e107::url('forum','topic',$this->data);

			$this->redirect($url);
			exit;

		//	$url = e107::getUrl()->create('forum/thread/post', array('name'=>$threadVals['thread_name'], 'id' => $this->data['post_id'], 'thread' => $this->data['post_thread']), array('encode'=>false));

		//	header('location:'.$url);
		//	exit;
		}


	}


	/**
	 * @param $id of the post
	 */
	function updateReply()
	{
		$mes = e107::getMessage();
		$ns = e107::getRender();

		if (empty($_POST['post']))
		{
			$mes->addError(LAN_FORUM_3007);
			return;
		}

		if ($this->isAuthor()==false)
		{
			$mes->addError(LAN_FORUM_3009);
			return;
		}

		e107::getMessage()->addDebug(print_a($this->data,true));

		$postVals['post_edit_datestamp']    = time();
		$postVals['post_edit_user']         = USERID;
		$postVals['post_entry']             = $_POST['post'];

		if($uploadResult = $this->processAttachments())
		{
			$newValues   = e107::unserialize($this->data['post_attachments']);

			foreach($uploadResult as $ur)
			{
				$type = $ur['type'];
				$newValues[$type][] = array('file'=>$ur['file'], 'name'=>$ur['fname'], 'size'=>$ur['size']);
			}

			$postVals['post_attachments'] = e107::serialize($newValues);
		}
		
		//Allows directly overriding the method of adding files (or other data) as attachments
		if($attachmentsPosted = $this->processAttachmentsPosted($this->data['post_attachments']))
		{
			$postVals['post_attachments'] = $attachmentsPosted;
		}		

		$this->forumObj->postUpdate($this->data['post_id'], $postVals);

		e107::getCache()->clear('newforumposts');


	//	$url = e107::getUrl()->create('forum/thread/post', "id={$this->data['post_id']}", 'encode=0&full=1'); // XXX what data is available, find thread name

		$url = e107::url('forum','topic',$this->data); // ."&f=post";

		$this->redirect($url);

		exit;

	}




	function isAuthor()
	{
		return ((USERID === (int)$this->data['post_user']) || MODERATOR);
	}


	/**
	 * @return array
	 */
	function processAttachments()
	{

		$ret = array();

		e107::getMessage()->addDebug("Processing Attachments");


		if (isset($_FILES['file_userfile']['error']))
		{

				e107::getMessage()->addDebug("Attachment Detected");

			// retrieve and create attachment directory if needed
			//$attachmentDir = $this->forumObj->getAttachmentPath(USERID, true);

		//	e107::getMessage()->addDebug("Attachment Directory: ".$attachmentDir);

			if($uploaded = e107::getFile()->getUploaded('attachments', 'attachment', array( 'max_file_count' => 5)))
			{

				e107::getMessage()->addDebug("Uploaded Data: ".print_a($uploaded,true));


				foreach($uploaded as $upload)
				{
					//print_a($upload); exit;
					if ($upload['error'] == 0)
					{
						$_txt = '';
						$_att = '';
						$_file = '';
						$_thumb = '';
						$_fname = '';
						$fpath = '';
						if(strstr($upload['type'], 'image'))
						{
							$_type = 'img';

							//XXX v2.x Image-resizing is now dynamic.

							/*if($forum->prefs->get('maxwidth', 0) > 0)
							{
								require_once(e_HANDLER.'resize_handler.php');
								$orig_file = $upload['name'];
								$new_file = 'th_'.$orig_file;

								$resizeDir = ($forum->prefs->get('linkimg') ? 'thumb/' : '');

								if(resize_image($attachmentDir.$orig_file, $attachmentDir.$resizeDir.$new_file, $forum->prefs->get('maxwidth')))
								{
									if($forum->prefs->get('linkimg'))
									{
										$parms = image_getsize($attachmentDir.$new_file);
										$_txt = '[br][link='.$fpath.$orig_file."][img{$parms}]".$fpath.$new_file.'[/img][/link][br]';
										$_file = $orig_file;
										$_thumb = $new_file;
										//show resized, link to fullsize
									}
									else
									{
										@unlink($attachmentDir.$orig_file);
										//show resized
										$parms = image_getsize($attachmentDir.$new_file);
										$_txt = "[br][img{$parms}]".$fpath.$new_file.'[/img][br]';
										$_file = $new_file;
									}
								}
								else
								{	//resize failed, show original
									$parms = image_getsize($attachmentDir.$upload['name']);
									$_txt = "[br][img{$parms}]".$fpath.$upload['name'].'[/img]';
									$_file = $upload['name'];
								}
							}
							else

							 */
							{	//resizing disabled, show original
								//	$parms = image_getsize($attachmentDir.$upload['name']);
								//resizing disabled, show original
								$_txt = "[br][img]".$fpath.$upload['name']."[/img]\n";
								$_file = $upload['name'];
							}
						}
						else
						{
							//upload was not an image, link to file
							$_type = 'file';
							$_fname = (isset($upload['rawname']) ? $upload['rawname'] : $upload['name']);
							$_txt = '[br][file='.$fpath.$upload['name'].']'.$_fname.'[/file]';
							$_file = $upload['name'];
							$_thumb = $_fname;
						}
						if($_txt && $_file)
						{
							$ret[] = array('type' => $_type, 'txt' => $_txt, 'file' => $_file, 'thumb' => $_thumb, 'fname' => $upload['origname'], 'size'=>$upload['size']);
						}
					}
					else
					{
						// Error in uploaded file, proceed but add error message.
						//echo 'Error in uploaded file: '.(isset($upload['rawname']) ? $upload['rawname'] : $upload['name']).'<br />';
						e107::getMessage()->addError('Error in uploading attachment: '.vartrue($upload['message']));
					}
				}

				return $ret;
			}
			else
			{
				// e107::getMessage()->addError('There was a problem with the attachment.');
				// e107::getMessage()->addDebug(print_a($_FILES['file_userfile'],true));
			}
		}
		/* no file uploaded at all, proceed with creating the topic or reply
		// TODO don't call process_upload() when no attachments are uploaded.. (check  user input first, then call if needed)
		else
		{
			e107::getMessage()->addError('Something went wrong during the attachment uploading process.');
		}
		*/
	}
	
	
	//Allows directly overriding the method of adding files (or other data) as attachments
	function processAttachmentsPosted($existingValues = '')
	{		
		if(isset($_POST['post_attachments_json']) && trim($_POST['post_attachments_json']))
		{
			$postedAttachments = json_decode($_POST['post_attachments_json'], true);
			$attachmentsJsonErrors = json_last_error();
			if($attachmentsJsonErrors === JSON_ERROR_NONE)
			{
		        if($existingValues)
		        {
		          $existingValues = e107::unserialize($existingValues);
		          return e107::serialize(array_merge_recursive($existingValues,$postedAttachments));
		        }
		        else
		        {
				  return e107::serialize($postedAttachments);
				}
			}
		}

    	return false;
	}

}

require_once(HEADERF);
new forum_post_handler;
require_once(FOOTERF);
exit;


?>
