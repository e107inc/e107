<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/users_extended.php,v $
 * $Revision: 1.21 $
 * $Date: 2009-11-17 10:46:34 $
 * $Author: marj_nl_fr $
 */

require_once('../class2.php');
if (!getperms('4'))
{
	header('location:'.e_BASE.'index.php');
	exit;
}
if (isset($_POST['cancel']))
{
	header('location:'.e_SELF);
	exit;
}
if (isset($_POST['cancel_cat']))
{
	header("location:".e_SELF."?cat");
	exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'user_extended';

$curtype = '1';
require_once(e_HANDLER.'calendar/calendar_class.php');
require_once(e_HANDLER.'message_handler.php');

$cal = new DHTML_Calendar(true);
require_once("auth.php");
require_once(e_HANDLER.'user_extended_class.php');
require_once(e_HANDLER.'userclass_class.php');

require_once(e_HANDLER.'form_handler.php');
$frm = new e_form;

$ue = new e107_user_extended;


$user = new users_ext;


$message = '';

if (e_QUERY)
{
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$sub_action = varset($tmp[1],'');
	$id = varset($tmp[2],0);
	unset($tmp);
}


if (isset($_POST['up_x']))
{
	$qs = explode(".", $_POST['id']);
	$_id = intval($qs[0]);
	$_order = intval($qs[1]);
	$_parent = intval($qs[2]);
	if (($_id > 0) && ($_order > 0) && ($_parent > 0))
	{
		$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order+1 WHERE user_extended_struct_type > 0 AND user_extended_struct_parent = {$_parent} AND user_extended_struct_order ='".($_order-1)."'");
		$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order-1 WHERE user_extended_struct_type > 0 AND user_extended_struct_parent = {$_parent} AND user_extended_struct_id='".$_id."'");
		$admin_log->log_event('EUF_01',$_id.', '.$_order.', '.$_parent,E_LOG_INFORMATIVE,'');
	}
}


if (isset($_POST['down_x']))
{
	$qs = explode(".", $_POST['id']);
	$_id = intval($qs[0]);
	$_order = intval($qs[1]);
	$_parent = intval($qs[2]);
	if (($_id > 0) && ($_order > 0) && ($_parent > 0))
	{
		$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order-1 WHERE user_extended_struct_type > 0 AND user_extended_struct_parent = {$_parent} AND user_extended_struct_order='".($_order+1)."'");
		$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order+1 WHERE user_extended_struct_type > 0 AND user_extended_struct_parent = {$_parent} AND user_extended_struct_id='".$_id."'");
		$admin_log->log_event('EUF_02',$_id.', '.$_order.', '.$_parent,E_LOG_INFORMATIVE,'');
	}
}


if (isset($_POST['catup_x']))
{
	$qs = explode(".", $_POST['id']);
	$_id = intval($qs[0]);
	$_order = intval($qs[1]);
	if (($_id > 0) && ($_order > 0))
	{
		$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order+1 WHERE user_extended_struct_type = 0 AND user_extended_struct_order='".($_order-1)."'");
		$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order-1 WHERE user_extended_struct_type = 0 AND user_extended_struct_id='".$_id."'");
		$admin_log->log_event('EUF_03',$_id.', '.$_order,E_LOG_INFORMATIVE,'');
	}
}


if (isset($_POST['catdown_x']))
{
	$qs = explode(".", $_POST['id']);
	$_id = intval($qs[0]);
	$_order = intval($qs[1]);
	if (($_id > 0) && ($_order > 0))
	{
		$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order-1 WHERE user_extended_struct_type = 0 AND user_extended_struct_order='".($_order+1)."'");
		$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order+1 WHERE user_extended_struct_type = 0 AND user_extended_struct_id='".$_id."'");
		$admin_log->log_event('EUF_04',$_id.', '.$_order,E_LOG_INFORMATIVE,'');
	}
}


if (isset($_POST['add_field']))
{
	$ue_field_name = str_replace(' ','_',trim($_POST['user_field']));		// Replace space with underscore - better security
	if (preg_match('#^\w+$#',$ue_field_name) === 1)						// Check for allowed characters, finite field length
	{
		if($_POST['user_type']==EUF_DB_FIELD)
		{
			$_POST['user_values'] = array($_POST['table_db'],$_POST['field_id'],$_POST['field_value'],$_POST['field_order']);
		}
		$new_values = $user->make_delimited($_POST['user_values']);
		$new_parms = $tp->toDB($_POST['user_include']."^,^".$_POST['user_regex']."^,^".$_POST['user_regexfail']."^,^".$_POST['user_hide']);
	
// Check to see if its a reserved field name before adding to database
		if ($ue->user_extended_reserved($ue_field_name))
		{  // Reserved field name
			$message = "[user_".$tp->toHTML($ue_field_name)."] ".EXTLAN_74;
		}
		else
		{
			$result = admin_update($ue->user_extended_add($ue_field_name, $tp->toDB($_POST['user_text']), intval($_POST['user_type']), $new_parms, $new_values, $tp->toDB($_POST['user_default']), intval($_POST['user_required']), intval($_POST['user_read']), intval($_POST['user_write']), intval($_POST['user_applicable']), 0, intval($_POST['user_parent'])), 'insert', EXTLAN_29);
			if(!$result)
			{
				$message = EXTLAN_75;
			}
			else
			{
				$admin_log->log_event('EUF_05',$ue_field_name.'[!br!]'.$tp->toDB($_POST['user_text']).'[!br!]'.intval($_POST['user_type']),E_LOG_INFORMATIVE,'');
			}
		}
	}
	else
	{
		$message = EXTLAN_76." : ".$tp->toHTML($ue_field_name);
	}
}


if (isset($_POST['update_field'])) 
{
	if($_POST['user_type']==EUF_DB_FIELD)
	{
    	$_POST['user_values'] = array($_POST['table_db'],$_POST['field_id'],$_POST['field_value'],$_POST['field_order']);
	}
	$upd_values = $user->make_delimited($_POST['user_values']);
	$upd_parms = $tp->toDB($_POST['user_include']."^,^".$_POST['user_regex']."^,^".$_POST['user_regexfail']."^,^".$_POST['user_hide']);
	admin_update($ue->user_extended_modify($sub_action, $tp->toDB($_POST['user_field']), $tp->toDB($_POST['user_text']), intval($_POST['user_type']), $upd_parms, $upd_values, $tp->toDB($_POST['user_default']), intval($_POST['user_required']), intval($_POST['user_read']), intval($_POST['user_write']), intval($_POST['user_applicable']), intval($_POST['user_parent'])), 'update', EXTLAN_29);
	$admin_log->log_event('EUF_06',$tp->toDB($_POST['user_field']).'[!br!]'.$tp->toDB($_POST['user_text']).'[!br!]'.intval($_POST['user_type']),E_LOG_INFORMATIVE,'');
}


if (isset($_POST['update_category']))
{
	$name = trim($tp->toHTML($_POST['user_field']));
	admin_update($sql->db_Update("user_extended_struct","user_extended_struct_name = '{$name}', user_extended_struct_read = '{$_POST['user_read']}', user_extended_struct_write = '{$_POST['user_write']}', user_extended_struct_applicable = '{$_POST['user_applicable']}' WHERE user_extended_struct_id = '{$sub_action}'"), 'update', EXTLAN_43);
	$admin_log->log_event('EUF_09',$name,E_LOG_INFORMATIVE,'');
}


if (isset($_POST['add_category']))
{
	$name = $tp->toHTML($_POST['user_field']);
	admin_update($sql->db_Insert("user_extended_struct","'0', '{$name}', '', 0, '', '', '', '{$_POST['user_read']}', '{$_POST['user_write']}', '0', '0', '{$_POST['user_applicable']}', '0', '0'"), 'insert', EXTLAN_40);
	$admin_log->log_event('EUF_08',$name,E_LOG_INFORMATIVE,'');
}


// Delete category
if (varset($_POST['eu_action'],'') == "delcat")
{
	list($_id, $_name) = explode(",",$_POST['key']);
	if (count($ue->user_extended_get_fields($_id)) > 0)
	{
	  $message = EXTLAN_77;
	}
	elseif($ue->user_extended_remove($_id, $_name))
	{
		$admin_log->log_event('EUF_10',$_id.', '.$_name,E_LOG_INFORMATIVE,'');
		$message = EXTLAN_41;
	}
}

if(isset($_POST['activate']))
{
	$message .= $user->field_activate();
}

if(isset($_POST['deactivate']))
{
	$message .= $user->field_deactivate();
}



if($sql->db_Select("user_extended_struct","DISTINCT(user_extended_struct_parent)"))
{
	$plist = $sql->db_getList();
	foreach($plist as $_p)
	{
		$o = 0;
		if($sql->db_Select("user_extended_struct", "user_extended_struct_id", "user_extended_struct_parent = {$_p['user_extended_struct_parent']} && user_extended_struct_type != 0 ORDER BY user_extended_struct_order ASC"))
		{
			$_list = $sql->db_getList();
			foreach($_list as $r)
			{
				$sql->db_Update("user_extended_struct", "user_extended_struct_order = '{$o}' WHERE user_extended_struct_id = {$r['user_extended_struct_id']}");
				$o++;
			}
		}
	}
}


if($message)
{
    $emessage = eMessage::getInstance();
	$emessage->add($message, E_MESSAGE_SUCCESS);
  //	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}



if(isset($_POST['table_db']) && !$_POST['add_field'] && !$_POST['update_field'])
{
	$action = "continue";
	$current['user_extended_struct_name'] = $_POST['user_field'];
    $current['user_extended_struct_parms'] = $_POST['user_include']."^,^".$_POST['user_regex']."^,^".$_POST['user_regexfail']."^,^".$_POST['user_hide'];
    $current['user_extended_struct_text'] = $_POST['user_text'];
	$current['user_extended_struct_type'] = $_POST['user_type'];
	$user->show_extended($current);
}





if ($action == "editext")
{
	if($sql->db_Select('user_extended_struct','*',"user_extended_struct_id = '{$sub_action}'"))
	{
		$tmp = $sql->db_Fetch();
		$user->show_extended($tmp);
	}
	else
	{
		$user->show_extended('new');
	}
}

if($action == 'pre')
{
	$user->show_predefined();
}

if($action == 'cat')
{
	if(is_numeric($sub_action))
	{
		if($sql->db_Select('user_extended_struct','*',"user_extended_struct_id = '{$sub_action}'"))
		{
			$tmp = $sql->db_Fetch();
		}
	}
	$user->show_categories($tmp);
}

require_once("footer.php");

class users_ext
{
    var $catList;
	var $catNums;

	function users_ext()
	{
        global $action,$ue;

        if (varset($_POST['eudel'],''))
		{
			foreach($_POST['eudel'] as $id=>$name)
			{
            	$this->delete_extended($id,$name);
			}
		}

        $this->catList = $ue->user_extended_get_categories();
		$this->catList[0][0] = array('user_extended_struct_name' => EXTLAN_36);
		$this->catNums = array_keys($this->catList);

        if (!e_QUERY || $action == 'main')
		{
  			$this->showExtendedList();
		}

	}





	function delete_extended($_id,$_name)
	{
		global $ue,$admin_log;
        $emessage = eMessage::getInstance();

		if ($ue->user_extended_remove($_id, $_name))
		{
			$admin_log->log_event('EUF_07',$_id.', '.$_name, E_LOG_INFORMATIVE,'');
			$emessage->add(EXTLAN_30." [".$_name."]", E_MESSAGE_SUCCESS);
		}
		else
		{
        	$emessage->add(LAN_ERROR." [".$_name."]", E_MESSAGE_ERROR);
		}
	}

	function showExtendedList()
	{
        global $sql, $ns, $ue, $curtype, $tp, $mySQLdefaultdb, $action, $sub_action,$frm;

  //		$catList = $ue->user_extended_get_categories();
  //		$catList[0][0] = array('user_extended_struct_name' => EXTLAN_36);
  //		$catNums = array_keys($catList);
		$extendedList = $ue->user_extended_get_fields();

        $emessage = &eMessage::getInstance();
	  	$text = $emessage->render()  ;

		$mode = 'show';
			$text .= "

			   <form method='post' action='".e_SELF."' >
			   <table class='adminlist' cellpadding='0' cellspacing='0'>
			<thead>
				<tr>
				<th>".EXTLAN_1."</th>
                <th>".EXTLAN_79."</th>

				<th>".EXTLAN_2."</th>
				<th>".EXTLAN_44."</th>
				<th>".EXTLAN_4."</th>

				<th>".EXTLAN_5."</th>
				<th>".EXTLAN_6."</th>
				<th>".EXTLAN_7."</th>
				<th class='center last' colspan='2'>".EXTLAN_8."</th>
				</tr>
			</thead>
			<tbody>
			";

			foreach($this->catNums as $cn)
			{

				$i=0;
				$category_name = $this->catList[$cn][0]['user_extended_struct_name'];

				if(count($extendedList[$cn]))  //	Show current extended fields
				{
					foreach($extendedList[$cn] as $ext)
					{

						$name = $ext['user_extended_struct_name'];
						$fname = "user_".$name;

						$id = $ext['user_extended_struct_id'];

						$uVal = str_replace(chr(1), "", $ext['user_extended_struct_default']);		// Is this right?
							$text .= "
							<tr>
							<td>{$ext['user_extended_struct_name']}</td>
							<td>".$tp->toHTML($ext['user_extended_struct_text'], FALSE, "defs")."</td>
							<td class='left'>".$ue->user_extended_edit($ext,$uVal)."</td>
							<td class='left'>".$category_name."</td>
							<td>".($ext['user_extended_struct_required'] == 1 ? LAN_YES : LAN_NO)."</td>
							<td>".r_userclass_name($ext['user_extended_struct_applicable'])."</td>
							<td>".r_userclass_name($ext['user_extended_struct_read'])."</td>
							<td>".r_userclass_name($ext['user_extended_struct_write'])."</td>
							<td>";

						  	if($i > 0)
						  	{
						 		$text .= "<input type='image' alt='' title='".EXTLAN_26."' src='".ADMIN_UP_ICON_PATH."' name='up[$id]' value='{$ext['user_extended_struct_id']}.{$ext['user_extended_struct_order']}.{$ext['user_extended_struct_parent']}' />";
							}
							if($i <= count($extendedList[$cn])-2)
							{
								$text .= "<input type='image' alt='' title='".EXTLAN_25."' src='".ADMIN_DOWN_ICON_PATH."' name='down[$id]' value='{$ext['user_extended_struct_id']}.{$ext['user_extended_struct_order']}.{$ext['user_extended_struct_parent']}' />";
							}
							$text .= "
						  	</td>
							<td class='center' style='width:10%'>

							<a style='text-decoration:none' href='".e_SELF."?editext.".$id."'>".ADMIN_EDIT_ICON."</a>
		 					<input type='image' title='".LAN_DELETE."' name='eudel[".$id."]' src='".ADMIN_DELETE_ICON_PATH."' value='".$name."' onclick='return confirm(\"".EXTLAN_27."\")' />
							</td>
							</tr>
							";
							$i++;
					  }
				}
				else
				{
						$text .= "
						<tr>
						<td colspan='8' class='center'>".EXTLAN_28."</td>
						</tr>
						";
				}

			}

			//Show add/edit form
			$text .= "</tbody>
			</table></form>";


			$emessage = eMessage::getInstance();
	  		$ns->tablerender(EXTLAN_9,$text);


	}

	function show_extended($current = '')  // Show existing fields List.
	{
        global $sql, $ns, $ue, $curtype, $tp, $mySQLdefaultdb, $action, $sub_action,$frm;
 			if($current == 'new')
			{
					$mode = 'new';
				  $current = array();
				  $current_include = '';
				  $current_regex = '';
				  $current_regexfail = '';
				  $current_hide = '';
			}
			else
			{	// Editing existing definition
				$mode = 'edit';
				list($current_include, $current_regex, $current_regexfail, $current_hide) = explode("^,^",$current['user_extended_struct_parms']);
			}

			$text .= "
			<form method='post' action='".e_SELF."?".e_QUERY."'>
			<fieldset id='core-user-extended-create'>";

			$text .= "
            <table cellpadding='0' cellspacing='0' class='adminform'>
            	<colgroup span='2'>
            		<col class='col-label' />
            		<col class='col-control' />
            	</colgroup>
			<tr>
			<td>".EXTLAN_10.":</td>
			<td>user_";
			if(is_array($current) && $current['user_extended_struct_name'])
			{
				$text .= $current['user_extended_struct_name']."
				<input type='hidden' name='user_field' value='".$current['user_extended_struct_name']."' />
				";
			}
			else
			{
				$text .= "
				<input class='tbox' type='text' name='user_field' size='40' value='".$current['user_extended_struct_name']."' maxlength='50' />
				";
			}
			$text .= "
			<br /><span class='field-help'>".EXTLAN_11."</span>
			</td>
			</tr>

			<tr>
			<td>".EXTLAN_12.":</td>
			<td colspan='3'>
			<input class='tbox' type='text' name='user_text' size='40' value='".$current['user_extended_struct_text']."' maxlength='50' /><br />
			<span class='field-help'>".EXTLAN_13."</span>
			</td>
			</tr>
			";

			$text .= "<tr>
			<td >".EXTLAN_14."</td>
			<td colspan='3'>
			<select onchange='changeHelp(this.value)' class='tbox' name='user_type' id='user_type'>";
			foreach($ue->user_extended_types as $key => $val)
			{
				$selected = ($current['user_extended_struct_type'] == $key) ? " selected='selected'": "";
				$text .= "<option value='".$key."' $selected>".$val."</option>";
			}
			$curtype = $current['user_extended_struct_type'];
			if(!$curtype)
			{
				$curtype = '1';
			}
			$text .= "
			</select>
			</td></tr>";



			$text .= "
			<tr>
			<td>".EXTLAN_3."</td>
			<td colspan='3'>";
  // Start of Values ---------------------------------

      		$val_hide = ($current['user_extended_struct_type'] != 4) ? "visible" : "none";

			$text .= "<div id='values' style='display:$val_hide'>\n";
			$text .= "<div id='value_container' >\n";
			$curVals = explode(",",$current['user_extended_struct_values']);
			if(count($curVals) == 0){
				$curVals[]='';
			}
			$i=0;
			foreach($curVals as $v){
				$id = $i ? "" : " id='value_line'";
				$i++;
				$text .= "
				<span {$id}>
				<input class='tbox' type='text' name='user_values[]' size='40' value='{$v}' /></span><br />";
			}
			$text .= "
			</div>
			<input type='button' class='button' value='".EXTLAN_48."' onclick=\"duplicateHTML('value_line','value_container');\"  />
			<br /><span class='field-help'>".EXTLAN_17."</span></div>";
// End of Values. --------------------------------------
       		$db_hide = ($current['user_extended_struct_type'] == 4) ? "visible" : "none";

			$text .= "<div id='db_mode' style='display:$db_hide'>\n";
			$text .= "<table style='width:70%;margin-left:0px'><tr><td>";
            $text .= EXTLAN_62."</td><td style='70%'><select style='width:99%' class='tbox' name='table_db' onchange=\"this.form.submit()\" >
            <option value='' class='caption'>".EXTLAN_61."</option>\n";
			$result = mysql_list_tables($mySQLdefaultdb);
			while ($row2 = mysql_fetch_row($result))
			{
			  $fld = str_replace(MPREFIX,"",$row2[0]);
			  $selected =  (varset($_POST['table_db'],'') == $fld || $curVals[0] == $fld) ? " selected='selected'" : "";
			  if (MPREFIX!='' && strpos($row2[0], MPREFIX)!==FALSE)
			  {
				$text .= "<option value=\"".$fld."\" $selected>".$fld."</option>\n";
			  }
			}
			$text .= " </select></td></tr>";
     	if($_POST['table_db'] || $curVals[0])
		{
			// Field ID
			$text .= "<tr><td>".EXTLAN_63."</td><td><select style='width:99%' class='tbox' name='field_id' >\n
			<option value='' class='caption'>".EXTLAN_61."</option>\n";
			$table_list = ($_POST['table_db']) ? $_POST['table_db'] : $curVals[0] ;
			if($sql -> db_Select_gen("DESCRIBE ".MPREFIX."{$table_list}")){
		   		while($row3 = $sql -> db_Fetch()){
    				$field_name=$row3[0];
					$selected =  ($curVals[1] == $field_name) ? " selected='selected' " : "";
					$text .="<option value=\"$field_name\" $selected>".$field_name."</option>\n";
				}
			}
    		$text .= " </select></td></tr><tr><td>";
             // Field Value
			$text .= EXTLAN_64."</td><td><select style='width:99%' class='tbox' name='field_value' >
			<option value='' class='caption'>".EXTLAN_61."</option>\n";
			$table_list = ($_POST['table_db']) ? $_POST['table_db'] : $curVals[0] ;
			if($sql -> db_Select_gen("DESCRIBE ".MPREFIX."{$table_list}")){
		   		while($row3 = $sql -> db_Fetch()){
    				$field_name=$row3[0];
					$selected =  ($curVals[2] == $field_name) ? " selected='selected' " : "";
					$text .="<option value=\"$field_name\" $selected>".$field_name."</option>\n";
				}
			}
    		$text .= " </select></td></tr><tr><td>";

			$text .= LAN_ORDER."</td><td><select style='width:99%' class='tbox' name='field_order' >
			<option value='' class='caption'>".EXTLAN_61."</option>\n";
			$table_list = ($_POST['table_db']) ? $_POST['table_db'] : $curVals[0] ;
			if($sql -> db_Select_gen("DESCRIBE ".MPREFIX."{$table_list}")){
		   		while($row3 = $sql -> db_Fetch()){
    				$field_name=$row3[0];
					$selected =  ($curVals[3] == $field_name) ? " selected='selected' " : "";
					$text .="<option value=\"$field_name\" $selected>".$field_name."</option>\n";
				}
			}
    		$text .= " </select></td></tr>";

     	}
        $text .= "</table></div>";
// ---------------------------------------------------------
			$text .= "
			</td>
			</tr>

			<tr>
			<td>".EXTLAN_16."</td>
			<td colspan='3'>
			<input class='tbox' type='text' name='user_default' size='40' value='{$current['user_extended_struct_default']}' />
			</td>
			</tr>


			<tr>
			<td>".EXTLAN_15."</td>
			<td colspan='3'>
			<textarea class='tbox' name='user_include' cols='60' rows='2'>{$current_include}</textarea><br />
			<span class='field-help'>".EXTLAN_51."</span><br />
			</td>
			</tr>

			<tr>
			<td>".EXTLAN_52."</td>
			<td colspan='3'>
			<input class='tbox' type='text' name='user_regex' size='30' value='{$current_regex}' /><br />
			<span class='field-help'>".EXTLAN_53."</span><br />
			</td>
			</tr>

			<tr>
			<td >".EXTLAN_54."</td>
			<td colspan='3'>
			<input class='tbox' type='text' name='user_regexfail' size='40' value='{$current_regexfail}' /><br />
			<span class='field-help'>".EXTLAN_55."</span><br />
			</td>
			</tr>

			<tr>
			<td>".EXTLAN_44."</td>
			<td colspan='3'>
			<select class='tbox' name='user_parent'>";
			foreach($this->catNums as $k)
			{
				$sel = ($k == $current['user_extended_struct_parent']) ? " selected='selected' " : "";
				$text .= "<option value='{$k}' {$sel}>".$this->catList[$k][0]['user_extended_struct_name']."</option>\n";
			}
			$text .= "</select>

			</td>
			</tr>

			<tr>
			<td>".EXTLAN_18."</td>
			<td colspan='3'>
			<select class='tbox' name='user_required'>
			";
			$_r = array('0' => EXTLAN_65, '1' => EXTLAN_66, '2' => EXTLAN_67);
			foreach($_r as $k => $v)
			{
				$sel = ($current['user_extended_struct_required'] == $k ? " selected='selected' " : "");
				$text .= "<option value='{$k}' {$sel}>{$v}</option>\n";
			}

			$text .= "
			</select>
			<br />
			<span class='field-help'>".EXTLAN_19."</span>
			</td>
			</tr>

			<tr>
			<td >".EXTLAN_5."</td>
			<td colspan='3'>
			".r_userclass("user_applicable", $current['user_extended_struct_applicable'], 'off', 'member, admin, classes, nobody')."<br /><span class='field-help'>".EXTLAN_20."</span>
			</td>
			</tr>

			<tr>
			<td>".EXTLAN_6."</td>
			<td colspan='3'>
			".r_userclass("user_read", $current['user_extended_struct_read'], 'off', 'public, member, admin, readonly, classes')."<br /><span class='field-help'>".EXTLAN_22."</span>
			</td>
			</tr>

			<tr>
			<td>".EXTLAN_7."</td>
			<td colspan='3'>
			".r_userclass("user_write", $current['user_extended_struct_write'], 'off', 'member, admin, classes')."<br /><span class='field-help'>".EXTLAN_21."</span>
			</td>
			</tr>

			<tr>
			<td>".EXTLAN_49."
			</td>
			<td colspan='3'>
			<select class='tbox' name='user_hide'>
			";
			if($current_hide)
			{
				$text .= "
				<option value='1' selected='selected'>".LAN_YES."</option>
				<option value='0'>".LAN_NO."</option>";
			}
			else
			{
				$text .= "
				<option value='1'>".LAN_YES."</option>
				<option value='0' selected='selected'>".LAN_NO."</option>";
			}
			$text .= "
			</select>
			<br /><span class='field-help'>".EXTLAN_50."</span>
			</td>
			</tr>
			";

			$text .= "
			</table>
			<div class='buttons-bar center'>
			";

//			if ((!is_array($current) || $action == "continue") && $sub_action == "")
			if ((($mode == 'new') || $action == "continue") && $sub_action == "")
			{
				$text .= $frm->admin_button('add_field', EXTLAN_23);
			}
			else
			{
				$text .= $frm->admin_button('update_field', EXTLAN_24,'update').
				$frm->admin_button('cancel', EXTLAN_33);
			}


			$text .= "</div>
			</fieldset></form>
			";

		//		$text .= "</div>";
		$emessage = eMessage::getInstance();
		$ns->tablerender(EXTLAN_9,$emessage->render().$text);
	}


	function show_categories($current)
	{
		global $sql, $ns, $ue, $frm;

		$text = "<div style='text-align:center'>";
		$text .= "
        <table cellpadding='0' cellspacing='0' class='adminlist'>

		<thead>
		<tr>
		<th>".EXTLAN_1."</th>
		<th>".EXTLAN_5."</th>
		<th>".EXTLAN_6."</th>
		<th>".EXTLAN_7."</th>
		<th>&nbsp;</td>
		<th>".EXTLAN_8."</th>
		</tr>
		</thead>
		<tbody>
		";
		$catList = $ue->user_extended_get_categories(FALSE);
		if(count($catList))
		{
			//			Show current categories
			$i=0;
			foreach($catList as $ext)
			{
				if ($ext['user_extended_struct_order'] != $i)
				{
					$ext['user_extended_struct_order'] = $i;
					$xID=$ext['user_extended_struct_id'];
					$sql->db_Update("user_extended_struct", "user_extended_struct_order=$i WHERE user_extended_struct_type = 0 AND user_extended_struct_id=$xID");
				}

				$text .= "
				<td>{$ext['user_extended_struct_name']}</td>
				</td>
				<td>".r_userclass_name($ext['user_extended_struct_applicable'])."</td>
				<td>".r_userclass_name($ext['user_extended_struct_read'])."</td>
				<td>".r_userclass_name($ext['user_extended_struct_write'])."</td>
				<td>
				<form method='post' action='".e_SELF."?cat'>
				<input type='hidden' name='id' value='{$ext['user_extended_struct_id']}.{$ext['user_extended_struct_order']}' />
				";
				if($i > 0)
				{
					$text .= "
					<input type='image' alt='' title='".EXTLAN_26."' src='".e_IMAGE."/admin_images/up.png' name='catup' value='{$ext['user_extended_struct_id']}.{$i}' />
					";
				}
				if($i <= count($catList)-2)
				{
					$text .= "<input type='image' alt='' title='".EXTLAN_25."' src='".e_IMAGE."/admin_images/down.png' name='catdown' value='{$ext['user_extended_struct_id']}.{$i}' />";
				}
				$text .= "
				</form>
				</td>
				<td class='center' style='white-space: nowrap'>
				<form method='post' action='".e_SELF."?cat' onsubmit='return confirm(\"".EXTLAN_27."\")'>
				<input type='hidden' name='eu_action' value='delcat' />
				<input type='hidden' name='key' value='{$ext['user_extended_struct_id']},{$ext['user_extended_struct_name']}' />
				<a style='text-decoration:none' href='".e_SELF."?cat.{$ext['user_extended_struct_id']}'>".ADMIN_EDIT_ICON."</a>
				<input type='image' title='".LAN_DELETE."' name='eudel' src='".ADMIN_DELETE_ICON_PATH."' />
				</form>
				</td>
				</tr>
				";
				$i++;
			}
		}
		else
		{
			$text .= "
			<tr>
			<td colspan='8' class='center'>".EXTLAN_37."</td>
			</tr>
			";
		}

		//Show add/edit form
		$text .= "</tbody>
		</table>
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		";
		$text .= "<div><br /></div>
		<table cellpadding='0' cellspacing='0' class='adminform'>
		<colgroup span='2'>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>";

		$text .= "

		<tr>
		<td>".EXTLAN_38.":</td>
		<td colspan='3'>
		<input class='tbox' type='text' name='user_field' size='40' value='".$current['user_extended_struct_name']."' maxlength='50' />
		<br /><span class='field-help'>".EXTLAN_11."</span>
		</td>
		</tr>

		<tr>
		<td>".EXTLAN_5."</td>
		<td colspan='3'>
		".r_userclass("user_applicable", $current['user_extended_struct_applicable'], 'off', 'member, admin, classes')."<br /><span class='field-help'>".EXTLAN_20."</span>
		</td>
		</tr>

		<tr>
		<td>".EXTLAN_6."</td>
		<td colspan='3'>
		".r_userclass("user_read", $current['user_extended_struct_read'], 'off', 'public, member, admin, classes, readonly')."<br /><span class='field-help'>".EXTLAN_22."</span>
		</td>
		</tr>

		<tr>
		<td >".EXTLAN_7."</td>
		<td colspan='3'>
		".r_userclass("user_write", $current['user_extended_struct_write'], 'off', 'member, admin, classes')."<br /><span class='field-help'>".EXTLAN_21."</span>
		</td>
		</tr>
		</table>";


		$text .= "<div class='buttons-bar center'>";

		if (!is_array($current))
		{
			$text .= $frm->admin_button('add_category', EXTLAN_39);
		}
		else
		{
        	$text .= $frm->admin_button('update_category', EXTLAN_42,'update').
				$frm->admin_button('cancel', EXTLAN_33);
		}
		// ======= end added by Cam.
		$text .= "</div></form></div>";
		$ns->tablerender(EXTLAN_9, $text);
	}


	function show_options($action)
	{
		if ($action == "")
		{
			$action = "main";
		}
		$var['main']['text'] = EXTLAN_34;
		$var['main']['link'] = e_SELF;

		$var['editext']['text'] = EXTLAN_45;
		$var['editext']['link'] = e_SELF."?editext";

		$var['cat']['text'] = EXTLAN_35;
		$var['cat']['link'] = e_SELF."?cat";

		$var['pre']['text'] = EXTLAN_56;
		$var['pre']['link'] = e_SELF."?pre";

		show_admin_menu(EXTLAN_9, $action, $var);
	}






	function make_delimited($var)
	{
		global $tp;
		foreach($var as $k => $v)
		{
			$var[$k] = $tp->toDB(trim($v));
			$var[$k] = str_replace(",", "[E_COMMA]", $var[$k]);
			if($var[$k] == "")
			{
				unset($var[$k]);
			}
		}
		$ret = implode(",", $var);
		return $ret;
	}


	function show_predefined()
	{
		global $tp, $ns, $ue, $sql, $frm;

		// Get list of current extended fields
		$curList = $ue->user_extended_get_fieldlist();
		foreach($curList as $c)
		{
			$curNames[] = $c['user_extended_struct_name'];
		}

		//Get list of predefined fields.
		$preList = $ue->parse_extended_xml('getfile');
		ksort($preList);

		$txt = "
		<form method='post' action='".e_SELF."?pre'>
	    <table cellpadding='0' cellspacing='0' class='adminlist'>
		<colgroup span='6'>
			<col  />
			<col  />
			<col  />
	        <col  />
			<col  />
			<col  />
		</colgroup>
	    <thead>
			<tr>
			<th>".UE_LAN_21."</th>
			<th>".EXTLAN_79."</th>
			<th>".EXTLAN_2."</th>
			<th>".UE_LAN_22."</th>
			<th class='center' >".EXTLAN_57."</th>
			<th class='center last' >".LAN_OPTIONS."</th>
			</tr>
			</thead>
			<tbody>";

	   	foreach($preList as $k=>$a)
		{
			if($k !='version') // don't know why this is appearing in the array.
			{
		   		$active = (in_array($a['name'], $curNames)) ? TRUE : FALSE;
				$txt .= $this->show_predefined_field($a,$active);
			}
		}

		$txt .= "</tbody></table></form>";

		$emessage = eMessage::getInstance();
		$ns->tablerender(EXTLAN_56,$emessage->render(). $txt);
		require_once('footer.php');
		exit;
	}


	function show_predefined_field($var, $active)
	{
		global $tp,$ue, $frm;
		static $head_shown;
		$txt = "";


		foreach($var as $key=>$val) // convert predefined xml to default array format
		{
	    	$var['user_extended_struct_'.$key] = $val;
		}

		$var['user_extended_struct_type'] = $ue->typeArray[$var['user_extended_struct_type']];
		$var['user_extended_struct_parms'] = $var['include_text'];

		$txt .= "
		<tr>
		<td>{$var['user_extended_struct_name']}</td>
		<td>".constant(strtoupper($var['user_extended_struct_text'])."_DESC")."</td>
		<td>".$ue->user_extended_edit($var,$uVal)."</td>
       	<td>".$tp->toHTML($var['type'], false, 'defs')."</td>
		<td class='center'>".($active ? ADMIN_TRUE_ICON : "&nbsp;")."</td>
		";
	//	$txt .= constant("UE_LAN_".strtoupper($var['text'])."DESC")."<br />";
	//	foreach($showlist as $f)
	//	{
	//		if($var[$f] != "" && $f != 'type' && $f !='text')
	//		{
	//			$txt .= "<strong>{$f}: </strong>".$tp->toHTML($var[$f], false, 'defs')."<br />";
	//		}
	//	}
		$val = (!$active) ? EXTLAN_59 : EXTLAN_60;
		$type = (!$active) ? 'activate' : 'deactivate';
        ;
		$txt .= "
		<td class='center last'>";
        $txt .= $frm->admin_button($type."[".$var['user_extended_struct_name']."]", $val);
		$txt .= "</td>
		</tr>";
		return $txt;
	}


	function field_activate()
	{
		global $ue, $ns, $tp, $admin_log;
		$ret = "";
		$preList = $ue->parse_extended_xml('getfile');
		$tmp = $preList;

		foreach(array_keys($_POST['activate']) as $f)
		{

			$tmp[$f]['parms'] = $tp->toDB($tmp[$f]['parms']);
			if($ue->user_extended_add($tmp[$f]))
			{
				$ret .= EXTLAN_68." $f ".EXTLAN_69."<br />";

				if ($tmp[$f]['type']=="db field")
				{
					if (is_readable(e_ADMIN.'sql/extended_'.$f.'.php'))
					{
	             	$ret .= ($this->process_sql($f)) ? LAN_CREATED." user_extended_{$f}<br />" : LAN_CREATED_FAILED." user_extended_{$f}<br />";
				}
					else
					{
						$ret .= str_replace('--FILE--',e_ADMIN.'sql/extended_'.$f.'.php',EXTLAN_78);
					}
				}
			}
			else
			{
				$ret .= EXTLAN_70." $f ".EXTLAN_71."<br />";
			}
		}
		$admin_log->log_event('EUF_11',implode(', ',$_POST['activate']),E_LOG_INFORMATIVE,'');
		return $ret;
	}


	function field_deactivate()
	{
		global $ue, $ns, $tp, $sql, $admin_log;
		$ret = "";
		foreach(array_keys($_POST['deactivate']) as $f)
		{
			if($ue->user_extended_remove($f, $f))
			{
				$ret .= EXTLAN_68." $f ".EXTLAN_72."<br />";
				if(is_readable(e_ADMIN."sql/extended_".$f.".php")){
	             	$ret .= (mysql_query("DROP TABLE ".MPREFIX."user_extended_".$f)) ? LAN_DELETED." user_extended_".$f."<br />" : LAN_DELETED_FAILED." user_extended_".$f."<br />";
				}
			}
			else
			{
				$ret .= EXTLAN_70." $f ".EXTLAN_73."<br />";
			}
		}
		$admin_log->log_event('EUF_12',implode(', ',$_POST['deactivate']),E_LOG_INFORMATIVE,'');
		return $ret;
	}


	function process_sql($f)
	{
	    global $sql;
		$filename = e_ADMIN."sql/extended_".$f.".php";
		$fd = fopen ($filename, "r");
		$sql_data = fread($fd, filesize($filename));
		fclose ($fd);

		$search[0] = "CREATE TABLE ";	$replace[0] = "CREATE TABLE ".MPREFIX;
		$search[1] = "INSERT INTO ";	$replace[1] = "INSERT INTO ".MPREFIX;

	    preg_match_all("/create(.*?)myisam;/si", $sql_data, $creation);
	    foreach($creation[0] as $tab){
			$query = str_replace($search,$replace,$tab);
	      	if(!mysql_query($query)){
	        	$error = TRUE;
			}
		}

	    preg_match_all("/insert(.*?);/si", $sql_data, $inserts);
		foreach($inserts[0] as $ins){
			$qry = str_replace($search,$replace,$ins);
			if(!mysql_query($qry)){
			  	$error = TRUE;
			}
	    }

		return ($error) ? FALSE : TRUE;

	}
}// end class



	function users_extended_adminmenu() {
		global $user, $action, $ns, $curtype, $action;
		$user->show_options($action);
		if($action == 'editext' || $action == 'continue')
		{
			$ns->tablerender(EXTLAN_46." - <span id='ue_type'>&nbsp;</span>", "<div id='ue_help'>&nbsp;</div>");
			echo "<script type='text/javascript'>changeHelp('{$curtype}');</script>";
		}
	}

function headerjs()
{
	
	//FIXME
	include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_user_extended.php");
	$text = "
	<script type='text/javascript'>

	function changeHelp(type) {
	 //<![CDATA[
		var ftype;
		var helptext;
		";
		for($i=1; $i<=9; $i++)
		{
			$type_const = "UE_LAN_{$i}";
			$help_const = "\"".str_replace("/","\/","EXTLAN_HELP_{$i}")."\"";
			$text .= "
			if(type == \"{$i}\")
			{
				xtype=\"".constant($type_const)."\";
				what=\"".constant($help_const)."\";
			}";
		}
		$text .= "
		document.getElementById('ue_type').innerHTML=''+xtype+'';
		document.getElementById('ue_help').innerHTML=''+what+'';

		if(type == 4){
			document.getElementById('db_mode').style.display = '';
			document.getElementById('values').style.display = 'none';
		}else{
            document.getElementById('values').style.display = '';
			document.getElementById('db_mode').style.display = 'none';
		}
		   // ]]>
	}


	</script>";

	global $cal;
	$text .= $cal->load_files();

	echo $text;
}
?>