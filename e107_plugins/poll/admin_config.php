<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     �Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/poll/admin_config.php,v $
|     $Revision: 1.10 $
|     $Date: 2009-11-09 16:54:29 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
if (!is_object($tp)) $tp = new e_parse;
if (!getperms("P") || !plugInstalled('poll')) 
{
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'poll';

include_lan(e_PLUGIN.'poll/languages/English_admin_poll.php');
require_once(e_ADMIN."auth.php");
require_once(e_PLUGIN."poll/poll_class.php");
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."userclass_class.php");

if(isset($_POST)) 
{
	$_POST = strip_if_magic($_POST);
}

$rs = new form;
$poll = new poll;

if (isset($_POST['reset']))
{
	unset($poll_id, $_POST['poll_title'], $_POST['poll_option'], $_POST['activate'], $_POST['multipleChoice'], $_POST['showResults'], $_POST['startday'], $_POST['startmonth'], $_POST['startyear'], $_POST['endday'], $_POST['endmonth'], $_POST['endyear']);
	define("RESET", TRUE);
} 


	$emessage = eMessage::getInstance();
	
if (varset($_POST['delete'])) 
{
	$message = $poll->delete_poll(key($_POST['delete']));
	unset($poll_id, $_POST['poll_title'], $_POST['poll_option'], $_POST['activate']);
	$_GET['mode']='list';
}


if (isset($_POST['submit']))
{
	if($_POST['poll_title'])
	{
		define("POLLID",$_POST['poll_id']);
		$emessage->add($poll -> submit_poll(), E_MESSAGE_SUCCESS);
		unset($_POST['poll_title'], $_POST['poll_option'], $_POST['activate'], $_POST['poll_comment']);
	}
	else
	{
		$emessage->add(POLLAN_46, E_MESSAGE_SUCCESS);
	}
	$_GET['mode']='list';

}

if (isset($_POST['preview']))
{
	// Can't have everyone voting if tracking method is user ID
	if (($_POST['pollUserclass'] == e_UC_PUBLIC) && ($_POST['storageMethod'] == 2)) $_POST['pollUserclass'] = e_UC_MEMBER;
	$poll->render_poll($_POST, "preview");
}

if (varset($_POST['edit']) || varset($_GET['mode'])=='create' && !varset($_POST['submit']))
{
		$_GET['mode']='create';
		if($_POST['edit'])
		{
			edit_poll();
			define("POLLACTION",'edit');
		}
			
		$poll_total = $sql->db_Select("polls");
		$text = $poll -> renderPollForm();
		$ns->tablerender(POLLAN_MENU_CAPTION." :: ".POLLAN_2, $text);
}



if (isset($message))
{
	$emessage->add($message, E_MESSAGE_SUCCESS);

}

if(!varset($_POST['edit']) && ($_GET['mode']=="list" || !$_GET['mode']))
{
	poll_list();
}

require_once(e_ADMIN."footer.php");

function edit_poll()
{
	
	$sql = e107::getDb();
	$id = key($_POST['edit']);
	
	if ($sql->db_Select("polls", "*", "poll_id=".$id)) 
	{
		$_GET['mode'] = 'create';
		$row = $sql->db_Fetch();
		extract($row);

		$tmpArray = explode(chr(1), $poll_options);

		foreach($tmpArray as $option)
		{
			$_POST['poll_option'][] = $option;
		}

		$_POST['poll_id'] = $id;
		$_POST['activate'] = $poll_active;
		$_POST['option_count'] = count($_POST['poll_option']);
		$_POST['poll_title'] = $poll_title;
		$_POST['poll_comment'] = $poll_comment;

		if ($poll_start_datestamp)
		{
			$tmp = getdate($poll_start_datestamp);
			$_POST['startmonth'] = $tmp['mon'];
			$_POST['startday'] = $tmp['mday'];
			$_POST['startyear'] = $tmp['year'];
		}
		if ($poll_end_datestamp)
		{
			$tmp = getdate($poll_end_datestamp);
			$_POST['endmonth'] = $tmp['mon'];
			$_POST['endday'] = $tmp['mday'];
			$_POST['endyear'] = $tmp['year'];
		}

		$_POST['multipleChoice'] = $poll_allow_multiple;
		$_POST['showResults'] = $poll_result_type;
		// Can't have everyone voting if tracking method is user ID
		$_POST['pollUserclass'] = (($poll_vote_userclass == e_UC_PUBLIC) && $poll_storage_method == 2) ? e_UC_MEMBER : $poll_vote_userclass;
		$_POST['storageMethod'] = $poll_storage_method;
	}
}

function poll_list()
{
	$sql = e107::getDb();
	$ns = e107::getRender();
	$tp = e107::getParser();
	$frm = new e_form(true);
	
	
	global $user_pref;
	if(isset($_POST['etrigger_ecolumns'])) //TODO User 
	{
		$user_pref['admin_poll_columns'] = $_POST['e-columns'];
		save_prefs('user');
	}
	
	$fieldpref = (varset($user_pref['admin_poll_columns'])) ? $user_pref['admin_poll_columns'] : array("poll_id","poll_title","poll_options","poll_vote_userclass"); ;

	//TODO Add more column options. 
	$fields = array(
			'poll_id'				=> array('title'=> ID, 'width'=>'5%', 'forced'=> TRUE),
            'poll_title'	   		=> array('title'=> POLLAN_3, 'width'=>'auto'),
			'poll_options' 			=> array('title'=> POLLAN_4, 'type' => 'text', 'width' => 'auto', 'thclass' => 'center' ),	 // No real vetting
		//	'poll_start_datestamp' 	=> array('title'=> LAN_AUTHOR, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left first'), // Display name
		//	'poll_end_datestamp' 	=> array('title'=> LAN_DATE, 'type' => 'text', 'width' => 'auto'),	// User name
            'poll_vote_userclass' 	=> array('title'=> LAN_USERCLASS, 'type' => 'text', 'width' => 'auto'),	 	// Photo
			
			'options' 				=> array('title'=> LAN_OPTIONS, 'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last')
	);
	
	
	
	
	
	
	
	$text = "<div style='text-align:center'><div>
		<form action='".e_SELF."' method='post' id='del_poll'>";
	
	if ($poll_total = $sql->db_Select("polls", "*", "poll_type=1")) 
	{
		$text .= "<table class='adminlist' cellpadding='0' cellspacing='0'>";
		$text .= $frm->colGroup($fields,$fieldpref).
				$frm->thead($fields,$fieldpref);
	    $text .= "<tbody>";		
			
		while ($row = $sql->db_Fetch())
		{
			extract($row);
			
			$text .= "<tr>
				<td>$poll_id</td>";
				$text .= (in_array("poll_title",$fieldpref)) ? "<td class='left'>".$tp -> toHTML($poll_title, TRUE,"no_hook, emotes_off, defs")."</td>" : "";              
                $text .= (in_array("poll_options",$fieldpref)) ? "<td class='left'>".(str_replace(chr(1),"<br />",$poll_options))."</td>" : "";
		 		$text .= (in_array("poll_comment",$fieldpref)) ? "<td>".($poll_comment ? LAN_YES : LAN_NO)."</td>" : "";
				$text .= (in_array("poll_vote_userclass",$fieldpref)) ? "<td>".(r_userclass_name($poll_vote_userclass))."</td>" : "";
				
				$text .= "
				<td class='center'>
					<input type='image' name='edit[{$poll_id}]' value='edit' src='".ADMIN_EDIT_ICON_PATH."' alt='".LAN_EDIT."' title='".LAN_EDIT."' style='border:0px' />
					<input type='image' name='delete[$poll_id]' value='del' onclick=\"return jsconfirm('".$tp->toJS(LAN_CONFIRMDEL." [".$poll_id."]")."') \" src='".ADMIN_DELETE_ICON_PATH."' alt='".LAN_DELETE."' title='".LAN_DELETE."' style='border:0px' />
				</td>
				</tr>";
		}
		$text .= "</tbody></table>";
	}
	else 
	{
	  $text .= "<div style='text-align:center'>".POLLAN_7."</div>";
	}
	$text .= "</form></div></div>";
	
	$emessage = eMessage::getInstance();
	
	$ns->tablerender(POLLAN_MENU_CAPTION." :: ".POLLAN_1,$emessage->render(). $text);
}





function admin_config_adminmenu() 
{
	$action = varset($_GET['mode']) ? $_GET['mode'] : "list";
    $var['list']['text'] = POLLAN_1;
	$var['list']['link'] = e_SELF;
	$var['list']['perm'] = "P";
	$var['create']['text'] = POLLAN_2 ;
	$var['create']['link'] = e_SELF."?mode=create";
	$var['create']['perm'] = "P";
/*	$var['import']['text'] = GSLAN_23;
	$var['import']['link'] = e_SELF."?import";
	$var['import']['perm'] = "0";*/
	show_admin_menu(POLLAN_MENU_CAPTION, $action, $var);
}
?>