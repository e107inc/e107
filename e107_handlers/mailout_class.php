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
|     $Source: /cvs_backup/e107_0.8/e107_handlers/mailout_class.php,v $
|     $Revision: 1.2 $
|     $Date: 2009-10-26 01:23:19 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/


if (!defined('e107_INIT')) { exit; }

/* 
Class for 'core' mailout function. Additional mailout sources may replicate the functions of this class under a different name, or may use inheritance.

In general each class object must be self-contained, and use internal variables for storage

The class may use the global $sql object for database access - it will effectively have exclusive use of this during the email address search phase

It is the responsibility of each class to manager permission restrictions where required.

*/
// These variables determine the circumstances under which this class is loaded (only used during loading, and may be overwritten later)
	$mailer_include_with_default = TRUE;			// Mandatory - if false, show only when mailout for this specific plugin is enabled 
	$mailer_exclude_default = TRUE;					// Mandatory - if TRUE, when this plugin's mailout is active, the default isn't loaded

class core_mailout
{
  var   $mail_count = 0;
  var   $mail_read = 0;
  var	$mailer_name = LAN_MAILOUT_68;					// Text to identify the source of selector (displayed on left of admin page)
  var	$mailer_enabled = TRUE;							// Mandatory - set to FALSE to disable this plugin (e.g. due to permissions restrictions)

  // Constructor
  function core_mailout()
  {
  }
  
  // Data selection routines
  
  // Initialise data selection - save any queries or other information into internal variables, do initial DB queries as appropriate.
  // Return number of records available (or 1 if unknown) on success, FALSE on failure
  // Could in principle read all addresses and buffer them for later routines, if this is more convenient
  function select_init()
  {
    global	$sql;		// We can use this OK

	switch ($_POST['email_to'])
	{
	  // Build the query for the user database
      case "all" :
	  case  "admin" :
		switch ($_POST['email_to']) 
		{
		  case "admin":
			$insert = "u.user_admin='1' ";
			break;
		  case "all":
			$insert = "u.user_ban='0' ";
		    break;
		}
		$qry = ", ue.* FROM #user AS u LEFT JOIN #user_extended AS ue ON ue.user_extended_id = u.user_id WHERE {$insert} ";
		break;
  
	  case "unverified" :
		$qry = " FROM #user AS u WHERE u.user_ban='2'";
		break;

	  case "self" :
		$qry = " FROM #user AS u WHERE u.user_id='".USERID."'";
		break;

	  default :
		$insert = "u.user_class REGEXP concat('(^|,)',{$_POST['email_to']},'(,|$)') AND u.user_ban='0' ";
	    $qry = ", ue.* FROM #user AS u LEFT JOIN #user_extended AS ue ON ue.user_extended_id = u.user_id WHERE {$insert} ";
	}


	// Determine which fields we actually need (u.user_sess is the signup link)
	$qry = "SELECT u.user_id, u.user_name, u.user_email, u.user_sess".$qry;

	if($_POST['extended_1_name'] && $_POST['extended_1_value'])
	{
	  $qry .= " AND ".$_POST['extended_1_name']." = '".$_POST['extended_1_value']."' ";
	}

	if($_POST['extended_2_name'] && $_POST['extended_2_value'])
    {
	  $qry .= " AND ".$_POST['extended_2_name']." = '".$_POST['extended_2_value']."' ";
	}

	if($_POST['user_search_name'] && $_POST['user_search_value'])
	{
	  $qry .= " AND u.".$_POST['user_search_name']." LIKE '%".$_POST['user_search_value']."%' ";
	}

	$qry .= " ORDER BY u.user_name";
	if (!( $this->mail_count = $sql->db_Select_gen($qry))) return FALSE;
	$this->mail_read = 0;
	return $this->mail_count;
  }


  // Return an email address to add. Return FALSE if no more addresses to add
  // Returns an array with appropriate elements defined:
  //	'user_id' - non-zero if a registered user, zero if a non-registered user. (Always non-zero from this class)
  //	'user_name' - user name
  //	'user_email' - email address to use
  //	'user_signup' - signup link (zero if not applicable)
  function select_add()
  {
    global $sql;
    if (!($row = $sql->db_Fetch())) return FALSE;
	$ret = array('user_id' => $row['user_id'],
				 'user_name' => $row['user_name'],
				 'user_email' => $row['user_email'],
				 'user_signup' => $row['user_sess']
				 );
	$this->mail_read++;
//	echo "Return value: ".$row['user_name']."<br />";
	return $ret;
  }


  // Called once all email addresses read, to do any housekeeping needed
  function select_close()
  {	
	// Nothing to do here
  }
  
  
  // Called to show current selection criteria, and optionally allow edit
  // Returns HTML which is displayed in a table cell. Typically we return a complete table
  // $allow_edit is TRUE to allow user to change the selection; FALSE to just display current settings
 
  //TODO - remove HTML markup from this class! (see below)
  
  function show_select($allow_edit = FALSE)
  {
    global $sql;
    $ret = "<table style='width:95%'>";

	if ($allow_edit)
	{  
	  // User class select
	  $ret .= "	<tr>
		<td class='forumheader3'>".LAN_MAILOUT_03.": </td>
		<td class='forumheader3'>
		".userclasses("email_to", $_POST['email_to'])."</td>
		</tr>";
	
	  // User Search Field.
	  $u_array = array("user_name"=>LAN_MAILOUT_43,"user_login"=>LAN_MAILOUT_44,"user_email"=>LAN_MAILOUT_45);
	  $ret .= "
		<tr>
			<td style='width:35%' class='forumheader3'>".LAN_MAILOUT_46."
			<select name='user_search_name' class='tbox'>
			<option value=''>&nbsp;</option>";

	  foreach ($u_array as $key=>$val)
	  {
		$ret .= "<option value='{$key}' >".$val."</option>\n";
	  }
	  $ret .= "
		</select> ".LAN_MAILOUT_47." </td>
		<td style='width:65%' class='forumheader3'>
		<input type='text' name='user_search_value' class='tbox' style='width:80%' value='' />
		</td></tr>
		";



	  // Extended user fields
	  $ret .= "
		<tr><td class='forumheader3'>".LAN_MAILOUT_46.ret_extended_field_list('extended_1_name', TRUE).LAN_MAILOUT_48." </td>
		<td class='forumheader3'>
		<input type='text' name='extended_1_value' class='tbox' style='width:80%' value='' />
		</td></tr>
		<tr><td class='forumheader3'>".LAN_MAILOUT_46.ret_extended_field_list('extended_2_name', TRUE).LAN_MAILOUT_48." </td>
		<td class='forumheader3'>
		<input type='text' name='extended_2_value' class='tbox' style='width:80%' value='' />
		</td></tr>
		";
	}
	else
	{ 
	if(is_numeric($_POST['email_to']))
	{
		$sql->db_Select("userclass_classes", "userclass_name", "userclass_id = '{$_POST['email_to']}'");
		$row = $sql->db_Fetch();
		$_to = LAN_MAILOUT_23.$row['userclass_name'];
	}
	else
	{
		$_to = $_POST['email_to'];
	}
	  $ret .= "<tr>
			<td class='forumheader3' style='width:30%'>".LAN_MAILOUT_03."</td>
			<td class='forumheader3'>".$_to."&nbsp;";
			if($_POST['email_to'] == "self"){
				$text .= "&lt;".USEREMAIL."&gt;";
			}
	  $ret .= "</td></tr>";


	  if ($_POST['user_search_name'] && $_POST['user_search_value'])
	  {
		$ret .= "
		<tr>
			<td class='forumheader3' style='width:30%'>".$_POST['user_search_name']."</td>
			<td class='forumheader3'>".$_POST['user_search_value']."&nbsp;</td>
		</tr>";
	  }

	  if ($_POST['extended_1_name'] && $_POST['extended_1_value'])
	  {
		$ret .= "
		  <tr>
			<td class='forumheader3' style='width:30%'>".$_POST['extended_1_name']."</td>
			<td class='forumheader3'>".$_POST['extended_1_value']."&nbsp;</td>
		  </tr>";
	  }
	  if ($_POST['extended_2_name'] && $_POST['extended_2_value'])
	  {
		$ret .= "
		  <tr>
			<td class='forumheader3' style='width:30%'>".$_POST['extended_2_name']."</td>
			<td class='forumheader3'>".$_POST['extended_2_value']."&nbsp;</td>
		  </tr>";
	  }
	}


    return $ret.'</table>';
  }
}



?>