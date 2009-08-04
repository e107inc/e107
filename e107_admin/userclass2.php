<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area - User classes
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/userclass2.php,v $
 * $Revision: 1.26 $
 * $Date: 2009-08-04 15:04:18 $
 * $Author: e107coders $
 *
*/

require_once("../class2.php");
if (!getperms("4"))
{
  header("location:".e_BASE."index.php");
  exit;
}
$e_sub_cat = 'userclass';
//define('UC_DEBUG_OPTS',FALSE);

require_once(e_HANDLER."userclass_class.php");		// Modified class handler
$uclass = new e_userclass;							// Class management functions - legacy stuff from 0.7
$e_userclass = new user_class_admin;				// Admin functions - should just obliterate any previous object created in class2.php
require_once (e_HANDLER.'form_handler.php');
$frm = new e_form();
$uc = new uclass_manager;

$message = '';

function check_allowed($class_id, $redirect = true)
{
	global $e107;
	if (!isset($e107->user_class->class_tree[$class_id]))
	{
		if(!$redirect) return false;
		header("location:".SITEURL);
		exit;
	}
	if (!getperms('0') && !check_class($e107->user_class->class_tree[$class_id]['userclass_editclass']))
	{
		if(!$redirect) return false;
		header("location:".SITEURL);
		exit;
	}
	return true;
}

if (e_QUERY)
{
  $uc_qs = explode(".", e_QUERY);
}
$action = varset($uc_qs[0]);
$params = varset($uc_qs[1],'');

//AJAX request check is already  made by the API
if(e_AJAX_REQUEST)
{
    $class_num = intval(varset($uc_qs[2],0));
	if(!$class_num && isset($_POST['edit']))
	{
	  $params = 'edit';
	  $class_num = intval(varset($_POST['existing'],0));
	}

	if ($params == 'edit')
	{
	    require_once(e_HANDLER.'js_helper.php');
	    $jshelper = new e_jshelper();
	    if(!check_allowed($class_num, false))
		{
			//This will raise an error
			//'Access denied' is the message which will be thrown
			//by the JS AJAX handler
			e_jshelper::sendAjaxError('403', 'Access denied. Form update failed!');
		}
		$sql->db_Select('userclass_classes', '*', "userclass_id='".$class_num."' ");
		$row = $sql->db_Fetch(MYSQL_ASSOC);

		//Response action - reset all group checkboxes
		$jshelper->addResponseAction('reset-checked', array('group_classes_select' => '0'));

		//it's grouped userclass
		if ($row['userclass_type'] == UC_TYPE_GROUP)
		{
			//Response action - show group, hide standard
			$jshelper->addResponseAction('element-invoke-by-id', array('show' => 'userclass_type_groups', 'hide' => 'userclass_type_standard'));

			//fill in the classes array
			$tmp = explode(',',$row['userclass_accum']);
			foreach ($tmp as $uid) {
				$row['group_classes_select_'.$uid] = $uid;
			}
		} else {
			//hide group, show standard rows
			$jshelper->addResponseAction('element-invoke-by-id', array('hide' => 'userclass_type_groups', 'show' => 'userclass_type_standard'));
		}
		unset($row['userclass_accum']);
		$row['createclass'] = UCSLAN_14; //update the submit button value
		$row['existing'] = $class_num; //required when user tree is clicked
		//icon
		$row['iconview'] = $row['userclass_icon'] ? e_IMAGE_ABS.'userclasses/'.$row['userclass_icon'] : e_IMAGE_ABS."generic/blank.gif";
		$row['uc_icon_select'] = $row['userclass_icon']; //icons select box

		//Send the prefered response type
		//$jshelper->sendJSONResponse('fill-form', $row);
		$jshelper->sendResponse('fill-form', $row);
		exit;
	}
}

/*
 * Authorization should be done a bit later!
 * FIXME - should we call auth.php and header.php separate?
 * Definitely yes if AJAX is in the game.
 */
require_once("auth.php");

//---------------------------------------------------
//		Set Initial Classes
//---------------------------------------------------
if (isset($_POST['set_initial_classes']))
{
	$changed = $pref['init_class_stage'] != intval($_POST['init_class_stage']);
	$pref['init_class_stage'] = intval($_POST['init_class_stage']);
	$temp = array();
	foreach ($_POST['init_classes'] as $ic)
	{
		$temp[] = intval($ic);
	}
	$newval = implode(',', $temp);
	$temp = varset($pref['initial_user_classes'],'');
	if ($temp != $newval) $changed = TRUE;
	if ($changed)
	{
		$pref['initial_user_classes'] = $newval;
		save_prefs();
		userclass2_adminlog("05","New: {$newval}, Old: {$temp}, Stage: ".$pref['init_class_stage']);
		$message = UCSLAN_41;
	}
	else
	{
		$message = UCSLAN_42;
	}
}


//---------------------------------------------------
//		Delete existing class
//---------------------------------------------------
if (isset($_POST['delete']))
{
	$class_id = intval($_POST['existing']);
	check_allowed($class_id);
	if (($class_id >= e_UC_SPECIAL_BASE) && ($class_id <= e_UC_SPECIAL_END))
	{
	  $message = UCSLAN_29;
	}
	elseif ($_POST['confirm'])
	{
		if ($e_userclass->delete_class($class_id) !== FALSE)
		{
			userclass2_adminlog("02","ID:{$class_id} (".$e_userclass->uc_get_classname($class_id).")");
			if ($sql->db_Select('user', 'user_id, user_class', "user_class = '{$class_id}' OR user_class REGEXP('^{$class_id},') OR user_class REGEXP(',{$class_id},') OR user_class REGEXP(',{$class_id}$')"))
			{	// Delete existing users from class
				while ($row = $sql->db_Fetch(MYSQL_ASSOC))
				{
					$uidList[$row['user_id']] = $row['user_class'];
				}
				$uclass->class_remove($class_id, $uidList);
			}
			if (isset($pref['frontpage'][$class_id]))
			{
				unset($pref['frontpage'][$class_id]);		// (Should work with both 0.7 and 0.8 front page methods)
				save_prefs();
			}
			$message = UCSLAN_3;
		}
		else
		{
			$message = UCSLAN_10;
		}
	}
	else
	{
		$message = UCSLAN_4;
	}
}



//---------------------------------------------------
//		Add/Edit class information
//---------------------------------------------------
if (isset($_POST['createclass']))		// Add or edit
{
	$class_record = array(
		'userclass_name' 		=> varset($tp->toDB($_POST['userclass_name']),''),
		'userclass_description' => varset($tp->toDB($_POST['userclass_description']),''),
		'userclass_editclass' 	=> intval(varset($_POST['userclass_editclass'],0)),
		'userclass_parent'		=> intval(varset($_POST['userclass_parent'],0)),
		'userclass_visibility'	=> intval(varset($_POST['userclass_visibility'],0)),
		'userclass_icon' 		=> $tp->toDB(varset($_POST['userclass_icon'],'')),
		'userclass_type'		=> intval(varset($_POST['userclass_type'],UC_TYPE_STD))
		);
	if ($class_record['userclass_type'] == UC_TYPE_GROUP)
	{
		$temp = array();
		foreach ($_POST['group_classes_select'] as $gc)
		{
			$temp[] = intval($gc);
		}
		$class_record['userclass_accum'] = implode(',',$temp);
	}

	$do_tree = FALSE;		// Set flag to rebuild tree if no errors
	$forwardVals = FALSE;	// Set to ripple through existing values to a subsequent pass

	$tempID = intval(varset($_POST['userclass_id'], -1));
	if (($tempID < 0) && $e_userclass->ucGetClassIDFromName($class_record['userclass_name']))
	{	// Duplicate name
		$message = UCSLAN_63;
		$forwardVals = TRUE;
	}
	elseif ($e_userclass->checkAdminInfo($class_record, $tempID) === FALSE)
	{
		$message = UCSLAN_86;
	}

	if (!$forwardVals)
	{
		if ($tempID > 0)
		{		// Editing existing class here
			check_allowed($tempID);
			$class_record['userclass_id'] = $tempID;
			$e_userclass->save_edited_class($class_record);
			userclass2_adminlog("03","ID:{$class_record['userclass_id']} (".$class_record['userclass_name'].")");
			$do_tree = TRUE;
			$message .= UCSLAN_5;
		}
		else
		{	// Creating new class
			if($class_record['userclass_name'])
			{
				if (getperms("0") || ($class_record['userclass_editclass'] && check_class($class_record['userclass_editclass'])))
				{
					$i = $e_userclass->findNewClassID();
					if ($i === FALSE)
					{
						$message = UCSLAN_85;

					}
					else
					{
						$class_record['userclass_id'] = $i;
						$e_userclass->add_new_class($class_record);
						userclass2_adminlog("01","ID:{$class_record['userclass_id']} (".$class_record['userclass_name'].")");
						$do_tree = TRUE;
						$message .= UCSLAN_6;
					}
				}
				else
				{
					header("location:".SITEURL);
					exit;
				}
			}
			else
			{
				$message = UCSLAN_37;		// Class name required
				$forwardVals = TRUE;
			}
		}
	}

  if ($do_tree)
  {
	$e_userclass->calc_tree();
	$e_userclass->save_tree();
  }
}



if ($message)
{
  // $ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
	$emessage = &eMessage::getInstance();
	$emessage->add($message, E_MESSAGE_SUCCESS);
}

if(!e_QUERY || $action == "list")
{
	$ns->tablerender(UCSLAN_21, $emessage->render(). $uc->show_existing());
}
if($_GET['uc'])
{
	$action = "config";
    $_POST['existing'] = $_GET['uc'];
}

switch ($action)
{
//-----------------------------------
//		Class management
//-----------------------------------
  case 'config' :
	if(isset($_POST['existing']))
	{
		$params = 'edit';
		$class_num = intval(varset($_POST['existing'],0));
	}
	else
	{
		$class_num = intval(varset($uc_qs[2],0));
	}

	$userclass_id = 0;		// Set defaults for new class to start with
	$userclass_name = '';
	$userclass_description = '';
	$userclass_editclass = e_UC_ADMIN;
	$userclass_visibility = e_UC_ADMIN;
	$userclass_parent = e_UC_NOBODY;
	$userclass_icon = '';
	$userclass_type = UC_TYPE_STD;
	$userclass_groupclass = '';
	if ($params == 'edit' || $forwardVals)
	{
		if (!$forwardVals)
		{	// Get the values from DB (else just recycle data uer was trying to store)
			check_allowed($class_num);
			$sql->db_Select('userclass_classes', '*', "userclass_id='".intval($class_num)."' ");
			$class_record = $sql->db_Fetch();
			$userclass_id = $class_record['userclass_id'];			// Update fields from DB if editing
		}
		$userclass_name = $class_record['userclass_name'];
		$userclass_description = $class_record['userclass_description'];
		$userclass_editclass = $class_record['userclass_editclass'];
		$userclass_visibility = $class_record['userclass_visibility'];
		$userclass_parent = $class_record['userclass_parent'];
		$userclass_icon = $class_record['userclass_icon'];
		$userclass_type = $class_record['userclass_type'];
		if ($userclass_type == UC_TYPE_GROUP)
		{
			$userclass_groupclass = $class_record['userclass_accum'];
		}
	}

	$class_total = $sql->db_Count('userclass_classes', '(*)');

	$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."' id='classForm'>
 	<table cellpadding='0' cellspacing='0' class='adminform'>
 	<colgroup span='2'>
 		<col class='col-label' />
 		<col class='col-control' />

 	</colgroup>";

   /*	$text .= "
		<tr>
		<td class='fcaption' style='text-align:center' colspan='3'>";

	if ($class_total == "0")
	{
	  $text .= UCSLAN_7;
	}
	else
	{
	  $text .= "<span class='defaulttext'>".UCSLAN_8.":</span>";
	  $text .= "<select name='existing' class='tbox'>".$e_userclass->vetted_tree('existing',array($e_userclass,'select'), $userclass_id,"main,admin,new,classes,matchclass").'</select>';
	  $text .= "
		<input class='button' type='submit' id='edit' name='edit' value='".LAN_EDIT."' />
		<input class='button' type='submit' name='delete' value='".LAN_DELETE."' />
		<input type='checkbox' name='confirm' id='confirm' value='1' /><label for='confirm' class='smalltext'> ".UCSLAN_11."</label>
		</td>
		</tr>";
	}*/

	$text .= "
		<tr>
		<td>".UCSLAN_12."</td>
		<td>
		<input class='tbox' type='text' size='30' maxlength='25' name='userclass_name' value='{$userclass_name}' />
		<div class='field-help'>".UCSLAN_30."</div></td>

		</tr>
		<tr>
		<td>".UCSLAN_13."</td>
		<td><input class='tbox' type='text' size='60' maxlength='85' name='userclass_description' value='{$userclass_description}' />
		<div class='field-help'>".UCSLAN_31."</div></td>
		</tr>";

// Userclass icon
		$text .= "
		<tr>
		<td>".UCSLAN_68."</td>
		<td>".$frm->iconpicker('userclass_icon', $userclass_icon, LAN_SELECT)."
		<div class='field-help'>".UCSLAN_69."</div></td>
  		</tr>
		";

	$text .= "
		<tr>
		<td>".UCSLAN_79."</td>
		<td>\n
		<select name='userclass_type' class='tbox' onchange='setGroupStatus(this)'>
		<option value='".UC_TYPE_STD."'".(UC_TYPE_STD == $userclass_type ? " selected='selected'" : "").">".UCSLAN_80."</option>\n
		<option value='".UC_TYPE_GROUP."'".(UC_TYPE_GROUP == $userclass_type ? " selected='selected'" : "").">".UCSLAN_81."</option>\n
		</select>\n
		<div class='field-help'>".UCSLAN_82."</div></td>
	  	</tr>
	";

	// Who can manage class
	$text .= "
		<tr id='userclass_type_standard' ".(UC_TYPE_GROUP == $userclass_type ? " style='display:none'" : "").">
		<td>".UCSLAN_24."</td>
		<td>";
	  $text .= "<select name='userclass_editclass' class='tbox'>".$e_userclass->vetted_tree('userclass_editclass',array($e_userclass,'select'), $userclass_editclass,'nobody,public,main,admin,classes,matchclass,member').'</select>';
	$text .= "<div class='field-help'>".UCSLAN_32."</div></td>
	   	</tr>
		";

	// List of class checkboxes for grouping
	$text .= "
		<tr id='userclass_type_groups'".(UC_TYPE_STD == $userclass_type ? " style='display:none'" : "").">
		<td>".UCSLAN_83."</td>
		<td>";
	  $text .= $e_userclass->vetted_tree('group_classes_select',array($e_userclass,'checkbox'),  $userclass_groupclass,"classes,matchclass");
	$text .= "<div class='field-help'>".UCSLAN_32."</div></td>
	  	</tr>
		";


	$text .= "
		<tr>
		<td>".UCSLAN_34."</td>
		<td>";
	  $text .= "<select name='userclass_visibility' class='tbox'>".$e_userclass->vetted_tree('userclass_visibility',array($e_userclass,'select'), $userclass_visibility,'main,admin,classes,matchclass,public,member,nobody').'</select>';
	$text .= "<div class='field-help'>".UCSLAN_33."</div></td>
	 	</tr>
		";

	$text .= "
		<tr>
		<td>".UCSLAN_35."</td>
		<td>";
	  $text .= "<select name='userclass_parent' class='tbox'>".$e_userclass->vetted_tree('userclass_parent',array($e_userclass,'select'), $userclass_parent,'main,admin,nobody,classes,matchclass,member').'</select>';
//		.r_userclass("userclass_parent", $userclass_parent, "off", "admin,classes,matchclass,public,member").
	$text .= "<div class='field-help'>".UCSLAN_36."</div></td>
		</tr></table>
		";


	$text .= "
		<div class='buttons-bar center'>";

if($params == 'edit')
{
   	$text .= $frm->admin_button('createclass', UCSLAN_14, 'create');
	$text .= $frm->admin_button('updatecancel', LAN_CANCEL, 'create');
 //	$text .= "<input class='button' type='submit' id='createclass' name='createclass' value='".UCSLAN_14."' />";
 //	$text .= "&nbsp;&nbsp;<input class='button' type='submit' id='updatecancel' name='updatecancel' value='".LAN_CANCEL."' />";
	$text .= "
		<input type='hidden' name='userclass_id' value='{$userclass_id}' />
	    <script type='text/javascript'>
	        //just in case...
	        \$('updatecancel').show();
	    </script>
		";
}
else
{
	$text .= $frm->admin_button('createclass', UCSLAN_15, 'create');
	$text .= $frm->admin_button('updatecancel', LAN_CANCEL, 'create');
 //	$text .= "<input class='button' type='submit' id='createclass' name='createclass' value='".UCSLAN_15."' />
  //	&nbsp;&nbsp;<input class='button' type='submit' id='updatecancel' name='updatecancel' value='".LAN_CANCEL."' />";
	$text .= "
	    <input type='hidden' name='userclass_id' value='0' />";
}

$text .= "</div>";
$text .= "</form></div><br /><br />";
	$text .= $e_userclass->show_graphical_tree();


$ns->tablerender(UCSLAN_21, $text);

    break;				// End of 'config' option




//-----------------------------------
//		Initial User class(es)
//-----------------------------------
  case 'initial' :
    $initial_classes = varset($pref['initial_user_classes'],'');
	$irc = explode(',',$initial_classes);
	$icn = array();
	foreach ($irc as $i)
	{
	  if (trim($i)) $icn[] = $e_userclass->uc_get_classname($i);
	}

//	$class_text = $e_userclass->uc_checkboxes('init_classes', $initial_classes, 'classes, force', TRUE);
	$class_text = $e_userclass->vetted_tree('init_classes',array($e_userclass,'checkbox_desc'), $initial_classes, 'classes, force');

	$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?initial' id='initialForm'>
		<table class='fborder' style='".ADMIN_WIDTH."'><tr><td>";
	$text .= UCSLAN_43;
	if (count($icn) > 0)
	{
	  $text .= implode(', ',$icn);
	}
	else
	{
	  $text .= UCSLAN_44;
	}
	$text .= "</td></tr>
	<tr><td>".UCSLAN_49."</td></tr><tr><td>";

	if ($class_text)
	{
	  $text .= $class_text."</td></tr><tr><td>";
	  $sel_stage = varset($pref['init_class_stage'],2);
	  $text .= "<table><tr><td>".UCSLAN_45."<br /><span class='smalltext'>".UCSLAN_46."</span></td><td>
	  <select class='tbox' name='init_class_stage'>\n
	  <option value='1'".($sel_stage==1 ? " selected='selected'" : "").">".UCSLAN_47."</option>
	  <option value='2'".($sel_stage==2 ? " selected='selected'" : "").">".UCSLAN_48."</option>
	  </select>\n";
	  $text .= "</td></tr></table></td></tr>
	  <tr><td style='text-align:center'><input class='button' type='submit' name='set_initial_classes' value='".UCSLAN_UPDATE."' />";
	}
	else
	{
	  $text .= UCSLAN_39;
	}
	$text .= "</td></tr></table></form></div>";
	$ns->tablerender(UCSLAN_40, $text);

    break;				// End of 'initial'


//-----------------------------------
//		Debug aids
//-----------------------------------
  case 'debug' :
//    if (!check_class(e_UC_MAINADMIN)) break;				// Let ordinary admins see this if they know enough to specify the URL
	$text .= $e_userclass->show_graphical_tree(TRUE);			// Print with debug options
	$ns->tablerender(UCSLAN_21, $text);

	$text = "<table cellpadding='5' cellspacing='0' border='1'><tr><td colspan='5'>Class rights for first 20 users in database</td></tr>
	<tr><td>User ID</td><td>Disp Name</td><td>Raw classes</td><td>Inherited classes</td><td>Editable classes</td></tr>";
	$sql->db_Select('user','user_id,user_name,user_class',"ORDER BY user_id LIMIT 0,20",'no_where');
	while ($row = $sql->db_Fetch())
	{
	  $inherit = $e_userclass->get_all_user_classes($row['user_class']);
	  $text .= "<tr><td>".$row['user_id']."</td>
	  <td>".$row['user_name']."</td><td>".$row['user_class']."</td>
	  <td>".$inherit."</td>
	  <td>".$e_userclass->get_editable_classes($inherit)."</td>
	  </tr>";
	}
	$text .= "</table>";
	$ns->tablerender(UCSLAN_21, $text);
    break;				// End of 'debug'


//-----------------------------------
//		Configuration options
//-----------------------------------
  case 'options' :
    if (!check_class(e_UC_MAINADMIN)) break;

	// Set general options
	if (isset($_POST['set_admin_options']))
	{
	  $pref['admin_log_log']['admin_userclass'] = intval($_POST['admin_log_userclass']);
	  save_prefs();
	}

	if (isset($_POST['add_class_tree']))
	{	// Create a default tree
		$message = UCSLAN_62;
	    $e_userclass->set_default_structure();
		$e_userclass->calc_tree();
		$e_userclass->save_tree();
		$e_userclass->read_tree(TRUE);		// Need to re-read the tree to show correct info
		$message .= UCSLAN_64;
	}

	if (isset($_POST['flatten_class_tree']))
	{	// Remove the default tree
	  $message = UCSLAN_65;
	  $sql->db_Update("userclass_classes", "userclass_parent='0'");
	  $e_userclass->calc_tree();
	  $e_userclass->save_tree();
	  $e_userclass->read_tree(TRUE);		// Need to re-read the tree to show correct info
	  $message .= UCSLAN_64;
	}

	if (isset($_POST['rebuild_tree']))
	{
	  $message = UCSLAN_70;
	  $e_userclass->calc_tree();
	  $e_userclass->save_tree();
	  $message .= UCSLAN_64;
	}

	if ($message)
	{
	  $ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
	}

	$text = "<form method='post' action='".e_SELF."?options' id='optionsForm'>
		<table class='fborder' style='".ADMIN_WIDTH."'>
		<tr><td>".UCSLAN_59."</td><td>
		<input type='checkbox' name='admin_log_userclass' value='1'".(varset($pref['admin_log_log']['admin_userclass'],0) ? " checked='checked'" : '')."/></td></tr>
		<tr><td style='text-align:center' colspan='2'>
		<input class='button' type='submit' name='set_admin_options' value='".UCSLAN_UPDATE."' />
		</td>
		</tr></table></form>";
	$ns->tablerender(UCSLAN_60, $text);


	$text = "<form method='post' action='".e_SELF."?options' id='treesetForm'>
		<table class='fborder' style='text-align:center; ".ADMIN_WIDTH."'>
		<colgroup>
		<col style='width:50%' />
		<col style='width:50%' />
		</colgroup>
		<tr><td style='text-align:center' colspan='2'>".UCSLAN_52."<br />".UCSLAN_53."</td></tr>
		<tr><td style='text-align:center'>".UCSLAN_54."<br /><span class='smalltext'>".UCSLAN_57."</span><br />
		<input class='button' type='submit' name='add_class_tree' value='".UCSLAN_58."' onclick=\"return jsconfirm('".UCSLAN_67."')\" />
		</td><td style='text-align:center'>".UCSLAN_55."<br /><span class='smalltext'>".UCSLAN_56."</span><br />
		<input class='button' type='submit' name='flatten_class_tree' value='".UCSLAN_58."' onclick=\"return jsconfirm('".UCSLAN_66."')\" />
		</td>
		</tr></table></form>";
	$ns->tablerender(UCSLAN_61, $text);


	$text = "<form method='post' action='".e_SELF."?options' id='maintainForm'>
		<table class='fborder' style='text-align:center; ".ADMIN_WIDTH."'>
		<colgroup>
		<col style='width:50%' />
		<col style='width:50%' />
		</colgroup>
		<tr><td style='text-align:center'>".UCSLAN_72."<br /><span class='smalltext'>".UCSLAN_73."</span></td>
		<td style='text-align:center'>
		<input class='button' type='submit' name='rebuild_tree' value='".UCSLAN_58."' />
		</td>
		</tr></table></form>";
	$ns->tablerender(UCSLAN_71, $text);

    break;				// End of 'options'


//-----------------------------------
//		Test options
//-----------------------------------
  case 'test' :
    if (!check_class(e_UC_MAINADMIN)) break;
	if (isset($_POST['add_db_fields']))
	{	// Add the extra DB fields
	  $message = "Add DB fields: ";
	  $e_userclass->update_db(FALSE);
	  $message .= "Completed";
	}

	if (isset($_POST['remove_db_fields']))
	{	// Remove the DB fields
	  $message = "Remove DB fields: ";
	  $sql->db_Select_gen("ALTER TABLE #userclass_classes DROP `userclass_parent`, DROP `userclass_accum`, DROP `userclass_visibility`");
	  $message .= "Completed";
	}

	if (isset($_POST['add_class_tree']))
	{	// Create a default tree
	  $message = "Create default class tree: ";
	  if (!$e_userclass->update_db(TRUE))
	  {
	    $message .= "Must add new DB fields first";
	  }
	  else
	  {
	    $e_userclass->set_default_structure();
		$e_userclass->read_tree(TRUE);		// Need to re-read the tree to show correct info
		$message .= "Completed";
	  }
	}

	if (isset($_POST['remove_class_tree']))
	{	// Remove the default tree
	  $message = "Remove default class tree: ";
	  $sql->db_Delete("userclass_classes","`userclass_id` IN (".implode(',',array(e_UC_MAINADMIN,e_UC_MEMBER, e_UC_ADMIN, e_UC_ADMINMOD, e_UC_MODS, e_UC_USERS, e_UC_READONLY)).") ");
	  $e_userclass->read_tree(TRUE);		// Need to re-read the tree to show correct info
	  $message .= "completed";
	}

	if (isset($_POST['rebuild_tree']))
	{
	  $message = 'Rebuilding tree: ';
	  $e_userclass->calc_tree();
	  $e_userclass->save_tree();
	  $message .= " completed";
	}

	if ($message)
	{
	  $ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
	}

	$db_status = "Unknown";
	$db_status = $e_userclass->update_db(TRUE) ? "Updated" : "Original";
	$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?test' id='testForm'>
		<table class='fborder' style='".ADMIN_WIDTH."'>
		<tr><td class='fcaption' style='text-align:center' colspan='2'>Test Functions and Information</td></tr>";
	$text .= "<tr><td style='text-align:center' colspan='2'>DB Status: ".$db_status."</td></tr>";
	$text .= "<tr><td><input class='button' type='submit' name='add_db_fields' value='Add new DB fields' />First required step</td>";
	$text .= "<td><input class='button' type='submit' name='remove_db_fields' value='Remove new DB fields' />Reverse the process</td></tr>";
	$text .= "<tr><td><input class='button' type='submit' name='add_class_tree' value='Add class tree' />Optional default tree</td>";
	$text .= "<td><input class='button' type='submit' name='remove_class_tree' value='Remove class tree' />Deletes the 'core' class entries</td></tr>";
	$text .= "<tr><td><input class='button' type='submit' name='rebuild_tree' value='Rebuild class tree' />Sets up all the structures</td>";
	$text .= "<td><input class='button' type='submit' name='' value='Spare' />Spare</td></tr>";
	$text .= "<tr><td colspan='2'>&nbsp;</td></tr>";
	$text .= "<tr><td colspan='2'>".$e_userclass->show_tree(TRUE)."</td></tr>";

	$text .= "</table>";

	$text .= "</form>
			</div>";
	$ns->tablerender('User classes - test features', $text);
	break;				// End of temporary test options

//-----------------------------------
//		Edit class membership
//-----------------------------------
  case 'membs' :
	if ($params == 'clear')
	{
	  $class_id = intval(varset($uc_qs[2]));
	  check_allowed($class_id);
	  if ($sql->db_Select('user', 'user_id, user_class', "user_class = '{$class_id}' OR user_class REGEXP('^{$class_id},') OR user_class REGEXP(',{$class_id},') OR user_class REGEXP(',{$class_id}$')"))
	  {
		while ($row = $sql->db_Fetch())
		{
		  $uidList[$row['user_id']] = $row['user_class'];
		}
		$uclass->class_remove($class_id, $uidList);
		$message = UCSLAN_1;
		userclass2_adminlog("06","ID:{$class_id} (".$e_userclass->uc_get_classname($class_id).")");
	  }
	}
	elseif($params)
	{	// Process the updated membership list
	  $tmp2 = explode('-', $params,2);
	  $class_id = intval($tmp2[0]);
	  check_allowed($class_id);
	  $message = UCSLAN_2;

	  if ($sql->db_Select('user', 'user_id, user_class', "user_class = '{$class_id}' OR user_class REGEXP('^{$class_id},') OR user_class REGEXP(',{$class_id},') OR user_class REGEXP(',{$class_id}$')"))
	  {
		while ($row = $sql->db_Fetch())
		{
			$uidList[$row['user_id']] = $row['user_class'];
		}
		$uclass->class_remove($class_id, $uidList);
	  }
	  unset($uidList);
	  if ($sql->db_Select('user', 'user_id, user_class', "user_id IN({$tmp2[1]})"))
	  {
		while ($row = $sql->db_Fetch())
		{
			$uidList[$row['user_id']] = $row['user_class'];
		}
		$uclass->class_add($class_id, $uidList);
	  }
	  userclass2_adminlog("04","ID:{$class_id} (".$e_userclass->uc_get_classname($class_id).")");
	}


	if ($message)
	{
	  $ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
	}


	// If we're editing a class, get the info on the class
	if(isset($_POST['class_members_edit']))
	{
		$uc_edit_class = varset($_POST['class_to_edit'],0);
		check_allowed($uc_edit_class);
		$sql->db_Select('userclass_classes', '*', "userclass_id=".$uc_edit_class);
		$row = $sql->db_Fetch();
		extract($row);
	}

	$class_total = $sql->db_Select("userclass_classes", "*", "ORDER BY userclass_name", "nowhere");

	$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?membs' id='classForm'>
		<table class='fborder' style='".ADMIN_WIDTH."'>
		<tr>
		<td class='fcaption' style='text-align:center' colspan='2'>";

	if ($class_total == "0")
	{
	  $text .= UCSLAN_7;
	}
	else
	{
	  $text .= "<span class='defaulttext'>".UCSLAN_8.":</span>
			<select name='class_to_edit' class='tbox'>";
	  while ($row = $sql->db_Fetch())
	  {
		if (check_class($row['userclass_editclass']) || getperms("0"))
		{
		  $s =  $uc_edit_class == $row['userclass_id'] ? " selected='selected'" : '';
		  $text .= "<option value='{$row['userclass_id']}'{$s}>{$row['userclass_name']}</option>";
		}
	  }
	  $text .= "</select>
			<input class='button' type='submit' name='class_members_edit' value='".LAN_EDIT."' />";
	}

$text .= "</td></tr><tr><td>&nbsp;</td></tr></table>";


if(isset($_POST['class_members_edit']))
{
	$sql->db_Select("user", "user_id, user_name, user_class, user_login", "user_ban != 1 ORDER BY user_name ");
	$c = 0;
	$d = 0;
	while ($row = $sql->db_Fetch())
	{
		extract($row);
		if (check_class($userclass_id, $user_class))
		{
			$in_userid[$c] = $user_id;
			$in_username[$c] = $user_name;
			$in_userlogin[$c] = $user_login ? "(".$user_login.")" : "";
			$c++;
		}
		else
		{
			$out_userid[$d] = $user_id;
			$out_username[$d] = $user_name;
			$out_userlogin[$d] = $user_login ? "(".$user_login.")" : "";
			$d++;
		}
	}

	$text .= "<table class='fborder' style='".ADMIN_WIDTH."'>
		<tr>
		<td class='fcaption' style='text-align:center;width:30%'>".UCSLAN_16." ".$userclass_name."</td></tr>
		<tr>
		<td style='width:70%; text-align:center'>

		<table style='width:90%'>
		<tr>
		<td style='width:45%; vertical-align:top'>
		".UCSLAN_22."<br />
		<select class='tbox' id='assignclass1' name='assignclass1' size='10' style='width:220px' onchange='addIt()'>";
//		<select class='tbox' id='assignclass1' name='assignclass1' size='10' style='width:220px' multiple='multiple'>";

	for ($a = 0; $a <= ($d-1); $a++)
	{
	  $text .= "<option value='".$out_userid[$a]."'>".htmlentities($out_username[$a])." ".htmlentities($out_userlogin[$a])."</option>\n";
	}

	$text .= "</select>
		</td>\n
		<td style='width:45%; vertical-align:top'>
		".UCSLAN_23."<br />
		<select class='tbox' id='assignclass2' name='assignclass2' size='10' style='width:220px' onchange='delIt()'>";
//		<select class='tbox' id='assignclass2' name='assignclass2' size='10' style='width:220px' multiple='multiple'>";
	for($a = 0; $a <= ($c-1); $a++)
	{
	  $text .= "<option value='".$in_userid[$a]."'>".htmlentities($in_username[$a])." ".htmlentities($in_userlogin[$a])."</option>\n";
	}
	$text .= "</select><br />\n<br />";
	if (count($in_userid))
	{	// No option to clear class if it starts empty
	  $text .= "<input class='button' type='button' value='".UCSLAN_18."' onclick='clearMe({$userclass_id});' />";
	}

	$text .= "<input type='hidden' name='class_id' value='{$userclass_id}' />

		</td></tr></table>
		</td></tr>
		<tr><td colspan='2' style='text-align:center' class='forumheader'>
		<input class='button' type='button' value='".UCSLAN_19." ".$userclass_name." ".UCSLAN_20."' onclick='saveMe({$userclass_id});' />
		</td>
		</tr>
		</table>";

}

	$text .= "</form>
			</div>";
	$ns->tablerender(UCSLAN_28, $text);

    break;				// End of 'membs' (class membership) option


//-----------------------------------
//		Special fooling around
//-----------------------------------
  case 'special' :
    if (!check_class(e_UC_MAINADMIN)) break;				// Let main admins see this if they know enough to specify the URL

  $text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?special' id='specialclassForm'>";


  $text .= "<select name='class_select'>\n";
  $text .= $e_userclass->vetted_tree('class_select',array($e_userclass,'select'), $_POST['class_select']);
  $text .= "</select>\n";
  $ns->tablerender('Select box with nested items', $text);

  $text = "<select multiple size='10' name='multi_class_select[]'>\n";
  $text .= $e_userclass->vetted_tree('multi_class_select[]',array($e_userclass,'select'), implode(',',$_POST['multi_class_select']));
  $text .= "</select>\n";
  $ns->tablerender('Multiple Select box with nested items', $text);

  $checked_class_list = implode(',',$_POST['classes_select']);
  $text = "<table style='".ADMIN_WIDTH."'><tr><td style='text-align:left'>";
  $text .= $e_userclass->vetted_tree('classes_select',array($e_userclass,'checkbox'), $checked_class_list);
  $text .= "Classes: ".$checked_class_list;
  $text .= "</td><td style='text-align:left'>";
  $text .= $e_userclass->vetted_tree('normalised_classes_select',array($e_userclass,'checkbox'), $e_userclass->normalise_classes($checked_class_list));
  $text .= "Normalised Classes: ".$e_userclass->normalise_classes($checked_class_list);
  $text .= "</td></tr></table>";
  $ns->tablerender('Nested checkboxes, showing the effect of the normalise() routine', $text);

  $text = "Single class: ".$_POST['class_select']."<br />
       Multi-select: ".implode(',',$_POST['multi_class_select'])."<br />
       Check boxes: ".implode(',',$_POST['classes_select'])."<br />";
  $text .= "<input class='button' type='submit' value='Click to save' />
	</form>	</div>";
  $ns->tablerender('Click on the button - the settings above should be remembered, and the $_POST values displayed', $text);
    break;				// End of 'debug'

}	// End - switch ($action)



// Log event to admin log
function userclass2_adminlog($msg_num='00', $woffle='')
{
  global $pref, $admin_log;
  if (!varset($pref['admin_log_log']['admin_userclass'],0)) return;
//  $admin_log->log_event($title,$woffle,E_LOG_INFORMATIVE,'UCLASS_'.$msg_num);
  $admin_log->log_event('UCLASS_'.$msg_num,$woffle,E_LOG_INFORMATIVE,'');
}


function userclass2_adminmenu()
{
  if (e_QUERY)
  {
	$tmp = explode(".", e_QUERY);
//	$action = $tmp[0];
  }
  $action = varsettrue($tmp[0],'list');

	$var['list']['text'] = LAN_MANAGE;
	$var['list']['link'] = 'userclass2.php';


	$var['config']['text'] = UCSLAN_25;
	$var['config']['link'] = 'userclass2.php?config';

	$var['membs']['text'] = UCSLAN_26;
	$var['membs']['link'] ='userclass2.php?membs';

	$var['initial']['text'] = UCSLAN_38;
	$var['initial']['link'] ='userclass2.php?initial';

  if (check_class(e_UC_MAINADMIN))
  {
	$var['options']['text'] = UCSLAN_50;
	$var['options']['link'] ='userclass2.php?options';

	if (defined('UC_DEBUG_OPTS'))
	{
		$var['debug']['text'] = UCSLAN_27;
		$var['debug']['link'] ='userclass2.php?debug';

		$var['test']['text'] = 'Test functions';
		$var['test']['link'] ="userclass2.php?test";

		$var['specials']['text'] = 'Special tests';
		$var['specials']['link'] ="userclass2.php?special";
	}
  }
  show_admin_menu(UCSLAN_51, $action, $var);
}


class uclass_manager
{
    function uclass_manager()
	{
		global $user_pref;
    	if(isset($_POST['submit-e-columns']))
		{
			$user_pref['admin_userclass_columns'] = $_POST['e-columns'];
			save_prefs('user');
		}

        $this->fieldpref = (varset($user_pref['admin_userclass_columns'])) ? $user_pref['admin_userclass_columns'] : array("userclass_id","userclass_name","userclass_description"); ;

    	$this->fields = array(
			'userclass_icon' 			=> array('title'=> UCSLAN_68, 'type' => 'text', 'width' => '10%', 'thclass' => 'center' ),	 // No real vetting
			'userclass_id'				=> array('title'=> ID, 'width'=>'5%', 'thclass' => 'left'),
            'userclass_name'	   		=> array('title'=> UCSLAN_12, 'width'=>'auto', 'thclass' => 'left'),
			'userclass_description'   	=> array('title'=> UCSLAN_13, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left'),
         	'userclass_editclass' 		=> array('title'=> UCSLAN_24, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left'), // Display name
			'userclass_parent' 			=> array('title'=> UCSLAN_35, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left'),	// User name
            'userclass_visibility' 		=> array('title'=> UCSLAN_34, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left'),	 	// Photo
			'userclass_type' 			=> array('title'=> UCSLAN_79, 'type' => 'text', 'width' => '10%', 'thclass' => 'center' ),	 // Real name (no real vetting)
   			'options' 					=> array('title'=> LAN_OPTIONS, 'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last')
		);

	}


	function show_existing()
	{
	    global $e_userclass, $sql, $frm, $tp;
	 //	$text = "<div>";
        $total = $sql->db_Select('userclass_classes', '*');
		if ($total == "0")
		{
		  $text .= UCSLAN_7;
		}
		else
		{
             $text .= "<form method='post' action='".e_SELF."?".e_QUERY."'>
                        <fieldset id='core-userclass-list'>
						<legend class='e-hideme'>".CUSLAN_5."</legend>
						<table cellpadding='0' cellspacing='0' class='adminlist'>".
							$frm->colGroup($this->fields,$this->fieldpref).
							$frm->thead($this->fields,$this->fieldpref).

							"<tbody>";
			$classes = $sql->db_getList('ALL', FALSE, FALSE);
            $types = array(
				UC_TYPE_STD 	=> UCSLAN_80,
			   	UC_TYPE_GROUP	=> UCSLAN_81
			);

            foreach($classes as $row)
			{
				if(varset($row['userclass_icon']))
				{
                	$iconpath = $tp->replaceConstants($row['userclass_icon']);
            		$icon = is_readable($iconpath) ? "<img src='".$iconpath."' alt='' />" : "&nbsp;";
                }

				$text .= "
						<tr>";
                $text .= (in_array("userclass_icon",$this->fieldpref)) ? "<td class='center'>".$icon."</td>" : "";
				$text .= (in_array("userclass_id",$this->fieldpref)) ? "<td>".$row['userclass_id']."</td>" : "";
                $text .= (in_array("userclass_name",$this->fieldpref)) ? "<td>".($row['userclass_name'])."</td>" : "";
                $text .= (in_array("userclass_description",$this->fieldpref)) ? "<td>".($row['userclass_description'])."</td>" : "";
				$text .= (in_array("userclass_editclass",$this->fieldpref)) ? "<td>".r_userclass_name($row['userclass_editclass'])."</td>" : "";
				$text .= (in_array("userclass_parent",$this->fieldpref)) ? "<td>".(r_userclass_name($row['userclass_parent']))."</td>" : "";
				$text .= (in_array("userclass_visibility",$this->fieldpref)) ? "<td>".(r_userclass_name($row['userclass_visibility']))."</td>" : "";
				$text .= (in_array("userclass_type",$this->fieldpref)) ? "<td>".($types[$row['userclass_type']])."</td>" : "";



				$text .= "<td class='center'>
								<a class='action edit' href='".e_SELF."?uc=".$row['userclass_id']."'>".ADMIN_EDIT_ICON."</a>
								<input type='image' class='action delete' name='delete[{$row['userclass_id']}]' src='".ADMIN_DELETE_ICON_PATH."' title='".LAN_DELETE." [ ID: {$row['userclass_id']} ]' />
							</td>
						</tr>
				";
			}
		}
	    $text .= "</tbody></table></fieldset></form>";

	    return $text;


	}

}
require_once("footer.php");


function headerjs()
{
   /*
	* e107Ajax.fillForm demonstration
	* Open Firebug console for Ajax transaction details
	*
	*/
	$script_js = "<script type=\"text/javascript\">
		//<![CDATA[

			//Click observer
            document.observe('click', (function(event){
                var target = (event.findElement('a.userclass_edit') || event.findElement('input#edit'));
                if (target) {
                    event.stop();

                    //show cancel button in edit mod only
                    \$('updatecancel').show();

                    //If link is clicked use it's href as a target
    				$('classForm').fillForm($(document.body), { handler: target.readAttribute('href') });
                }
            }));

            //run on e107 init finished (dom is loaded)
    		e107.runOnLoad( function() {
				\$('updatecancel').hide(); //hide cancel button onload
			});

    		//Observe fillForm errors
    		e107Event.register('ajax_fillForm_error', function(transport) {
    			//memo.error object contains the error message
    			//error handling will be extended in the near future
				alert(transport.memo.error.message);
    		});



function setGroupStatus(dropdown)
{
	var temp1 = document.getElementById('userclass_type_standard');
	var temp2 = document.getElementById('userclass_type_groups');
	if (!temp1 || !temp2) return;
	if (dropdown.value == 0)
	{
		temp1.style.display = '';
		temp2.style.display = 'none';
	}
	else
	{
		temp2.style.display = '';
		temp1.style.display = 'none';
	}
}

	//]]>
	</script>\n";
  if (!e_QUERY) return $script_js;
  $qs = explode('.',e_QUERY);
  if ($qs[0] != 'membs') return $script_js;

// We only want this JS on the class membership selection page

	$script_js .= "<script type=\"text/javascript\">
		//<![CDATA[
// Inspiration (and some of the code) from a script by Sean Geraty -  Web Site:  http://www.freewebs.com/sean_geraty/
// Script from: The JavaScript Source!! http://javascript.internet.com

// Control flags for list selection and sort sequence
// Sequence is on option value (first 2 chars - can be stripped off in form processing)
// It is assumed that the select list is in sort sequence initially

var singleSelect = true;  // Allows an item to be selected once only (i.e. in only one list at a time)
var sortSelect = true;  // Only effective if above flag set to true
var sortPick = true;  // Will order the picklist in sort sequence


// Initialise - invoked on load
function initIt()
{
  var selectList = document.getElementById(\"assignclass1\");
  var pickList   = document.getElementById(\"assignclass2\");
  var pickOptions = pickList.options;
  pickOptions[0] = null;  // Remove initial entry from picklist (was only used to set default width)
  selectList.focus();  // Set focus on the selectlist
}



// Adds a selected item into the picklist

function addIt()
{
  var selectList = document.getElementById(\"assignclass1\");
  var selectIndex = selectList.selectedIndex;
  var selectOptions = selectList.options;

  var pickList   = document.getElementById(\"assignclass2\");
  var pickOptions = pickList.options;
  var pickOLength = pickOptions.length;

  // An item must be selected
  if (selectIndex > -1)
  {
    pickOptions[pickOLength] = new Option(selectList[selectIndex].text);
    pickOptions[pickOLength].value = selectList[selectIndex].value;
    // If single selection, remove the item from the select list
    if (singleSelect)
	{
      selectOptions[selectIndex] = null;
    }

    if (sortPick)
	{
      var tempText;
      var tempValue;
      // Sort the pick list
//      while (pickOLength > 0 && pickOptions[pickOLength].text < pickOptions[pickOLength-1].text)
      while (pickOLength > 0 && pickOptions[pickOLength].text.toLowerCase() < pickOptions[pickOLength-1].text.toLowerCase())
	  {
        tempText = pickOptions[pickOLength-1].text;
        tempValue = pickOptions[pickOLength-1].value;
        pickOptions[pickOLength-1].text = pickOptions[pickOLength].text;
        pickOptions[pickOLength-1].value = pickOptions[pickOLength].value;
        pickOptions[pickOLength].text = tempText;
        pickOptions[pickOLength].value = tempValue;
        pickOLength = pickOLength - 1;
      }
    }
  }
}



// Deletes an item from the picklist

function delIt()
{
  var selectList = document.getElementById(\"assignclass1\");
  var selectOptions = selectList.options;
  var selectOLength = selectOptions.length;

  var pickList   = document.getElementById(\"assignclass2\");
  var pickIndex = pickList.selectedIndex;
  var pickOptions = pickList.options;

  if (pickIndex > -1)
  {
    // If single selection, replace the item in the select list
    if (singleSelect)
	{
      selectOptions[selectOLength] = new Option(pickList[pickIndex].text);
      selectOptions[selectOLength].value = pickList[pickIndex].value;
    }
    pickOptions[pickIndex] = null;
    if (singleSelect && sortSelect)
	{
      var tempText;
      var tempValue;
      // Re-sort the select list - start from the bottom, swapping pairs, until the moved element is in the right place
// Commented out line sorts upper case first, then lower case. 'Active' line does case-insensitive sort
//      while (selectOLength > 0 && selectOptions[selectOLength].text < selectOptions[selectOLength-1].text)
      while (selectOLength > 0 && selectOptions[selectOLength].text.toLowerCase() < selectOptions[selectOLength-1].text.toLowerCase())
	  {
        tempText = selectOptions[selectOLength-1].text;
        tempValue = selectOptions[selectOLength-1].value;
        selectOptions[selectOLength-1].text = selectOptions[selectOLength].text;
        selectOptions[selectOLength-1].value = selectOptions[selectOLength].value;
        selectOptions[selectOLength].text = tempText;
        selectOptions[selectOLength].value = tempValue;
        selectOLength = selectOLength - 1;
      }
    }
  }
}

function clearMe(clid)
{
  location.href = document.location + \".clear.\" + clid;
}


function saveMe(clid)
{
  var strValues = \"\";
  var boxLength = document.getElementById('assignclass2').length;
  var count = 0;
  if (boxLength != 0)
  {
	for (i = 0; i < boxLength; i++)
	{
	  if (count == 0)
	  {
		strValues = document.getElementById('assignclass2').options[i].value;
	  }
	  else
	  {
		strValues = strValues + \",\" + document.getElementById('assignclass2').options[i].value;
	  }
	  count++;
	}
  }
  if (strValues.length == 0)
  {
	//alert(\"You have not made any selections\");
  }
  else
  {
	location.href = document.location + \".\" + clid + \"-\" + strValues;
  }
}

	//]]>
	</script>\n";
	return $script_js;
}


?>