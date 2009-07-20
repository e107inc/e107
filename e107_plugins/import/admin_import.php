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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/import/admin_import.php,v $
|     $Revision: 1.1 $
|     $Date: 2009-07-20 15:24:34 $
|     $Author: e107coders $
|
+----------------------------------------------------------------------------+
*/

/*
Routine manages import from other databases
Options supported:
	CSV (with format file)
	Mambo/Joomla
	PHPBB2
	PHPBB3
	SMF
	PHPNuke
	proboards
	PHPFusion
*/


 define('IMPORT_DEBUG',FALSE);
// define('IMPORT_DEBUG',TRUE);

require_once("../../class2.php");
// define("USE_PERSISTANT_DB",TRUE);


require_once (e_HANDLER."form_handler.php");
$frm = new e_form();
require_once(e_HANDLER."message_handler.php");

$emessage = &eMessage::getInstance(); //nothing wrong with doing it twice

// Language defs - maybe move out later
include_lan(e_PLUGIN."import/languages/".e_LANGUAGE."_admin_import.php");


// Source DB types (i.e. CMS types) supported. Key of each element is the 'short code' for the type
$import_class_names = array();			// Title
$import_class_comment = array();		// Descriptive comment
$import_class_support = array();		// Array of data types supported

// Definitions of available areas to import
$db_import_blocks = array('users' => array('message' => LAN_CONVERT_25, 'classfile' => 'import_user_class.php', 'classname' => 'user_import'),
							'news' => array('message' => LAN_CONVERT_28),
						  'forumdefs' => array('message' => LAN_CONVERT_26),
						  'forumposts' => array('message' => LAN_CONVERT_48), 
						  'polls' => array('message' => LAN_CONVERT_27)

						  );


// See what DB-based imports are available (don't really want it here, but gets it into the header script)
require_once(e_HANDLER.'file_class.php');

$fl = new e_file;
$importClassList = $fl->get_files(e_PLUGIN.'import', "^.+?_import_class\.php$", "standard", 1);
foreach($importClassList as $file)
{
  $tag = str_replace('_class.php','',$file['fname']);
  include_once($file['fpath'].$file['fname']);		// This will set up the variables
}
unset($importClassList);
unset($fl);



if(varset($_POST['import_source']))
{
	$import_source = varset($_POST['import_source'],'csv');
	if(varset($_POST['classes_select']))
	{
		$checked_class_list = implode(',',$_POST['classes_select']);
	}
 	$import_delete_existing_data = varset($_POST['import_delete_existing_data'],0);

	$current_csv = varset($_POST['csv_format'],'default');
	$csv_pw_not_encrypted = varset($_POST['csv_pw_not_encrypted'],0);
	$csv_data_file = varset($_POST['csv_data_file'],'import.csv');

	$current_db_type = varset($_POST['db_import_type'],key($import_class_names));
}
$db_blocks_to_import = array();


foreach ($db_import_blocks as $k => $v)
{
  if (isset($_POST['import_block_'.$k]))
  {
	$db_blocks_to_import[$k] = 1;
  }
}

require_once(e_ADMIN."auth.php");

if (!is_object($e_userclass))
{
  require_once(e_HANDLER."userclass_class.php");		// Modified class handler
  $e_userclass = new user_class;
}




define('CSV_DEF_FILE','csv_import.txt');		// Supplementary CSV format definitions

// Definitions of available CSV-based imports
$csv_formats = array('default' => 'user_name,user_password');
$csv_names = array('default' => LAN_CONVERT_12);
$csv_options = array('default' => 'simple');
$csv_option_settings = array(
		'simple' 	=> array('separator' => ',', 'envelope' => ''),
		'simple_sq'	=> array('separator' => ',', 'envelope' => "'"),
		'simple_dq' => array('separator' => ',', 'envelope' => '"'),
		'simple_semi' => array('separator' => ',', 'envelope' => ';'),
		'simple_bar' => array('separator' => ',', 'envelope' => '|')
	);

// See what CSV format definitions are available
if (is_readable(CSV_DEF_FILE))
{
  $csv_temp = file(CSV_DEF_FILE);
  foreach ($csv_temp as $line)
  {
	$line = trim(str_replace("\n","",$line));
	if ($line)
	{
	  list($temp,$name,$options,$line) = explode(',',$line,4);
	  $temp = trim($temp);
	  $name = trim($name);
	  $options = trim($options);
	  $line = trim($line);
	  if ($temp && $name && $options && $line)  
	  {
		$csv_formats[$temp] = $line;		// Add any new definitions
		$csv_names[$temp] = $name;
		$csv_options[$temp] = $options;
	  }
	}
  }
  unset($csv_temp);
}



$msg = '';

//======================================================
// 		Executive routine - actually do conversion
//======================================================
if(isset($_POST['do_conversion']))
{
  $abandon = TRUE;
  switch ($import_source)
  {
	case 'csv' : 
	  if (!isset($csv_formats[$current_csv])) $msg = "CSV File format error<br /><br />";
	  if (!is_readable($csv_data_file)) $msg = LAN_CONVERT_31;
	  if (!isset($csv_options[$current_csv])) $msg = LAN_CONVERT_37.' '.$current_csv;
	  if (!isset($csv_option_settings[$csv_options[$current_csv]])) $msg = LAN_CONVERT_37.' '.$csv_options[$current_csv];
	  
	  if (!$msg)
	  {
		$field_list = explode(',',$csv_formats[$current_csv]);
		$separator = $csv_option_settings[$csv_options[$current_csv]]['separator'];
		$enveloper = $csv_option_settings[$csv_options[$current_csv]]['envelope'];
		if (IMPORT_DEBUG) echo "CSV import: {$current_csv}  Fields: {$csv_formats[$current_csv]}<br />";
		require_once('import_user_class.php');
		$usr = new user_import;
		$usr->overrideDefault('user_class',$checked_class_list);
		if (($source_data = file($csv_data_file)) === FALSE) $msg = LAN_CONVERT_32;
		if ($import_delete_existing_data) $usr->emptyTargetDB();				// Delete existing users - reasonably safe now
		$line_counter = 0;
		$error_counter = 0;
		$write_counter = 0;
		foreach ($source_data as $line)
		{
		  $line_counter++;
		  $line_error = FALSE;
		  if ($line = trim($line))
		  {
			$usr_data = $usr->getDefaults();		// Reset user data
			$line_data = csv_split($line, $separator, $enveloper);
			$field_data = current($line_data);
			foreach ($field_list as $f)
			{
			  if ($field_data === FALSE) $line_error = TRUE;
			  if ($f != 'dummy') $usr_data[$f] = $field_data;
			  $field_data = next($line_data);
			}
			if ($line_error)
			{
			  if ($msg) $msg .= "<br />";
			  $msg .= LAN_CONVERT_33.$line_counter;
			  $error_counter++;
			}
			else
			{
			  if ($csv_pw_not_encrypted)
			  {
				$usr_data['user_password'] = md5($usr_data['user_password']);
			  }
			  $line_error = $usr->saveData($usr_data);
			  if ($line_error === TRUE)
			  {
				$write_counter++;
			  }
			  else
			  {
			    $line_error = $usr->getErrorText($line_error);
				if ($msg) $msg .= "<br />";
				$msg .= str_replace('--ERRNUM--',$line_error,LAN_CONVERT_34).$line_counter;
				$error_counter++;
			  }
			}
		  }
		}
		if ($msg) $msg .= "<br />";
		if ($import_delete_existing_data) $msg .= LAN_CONVERT_40.'<br />';
		$msg .= str_replace(array('--LINES--','--USERS--', '--ERRORS--'),array($line_counter,$write_counter,$error_counter),LAN_CONVERT_35);
	  }
	  break;

	case 'db' :

	  if (IMPORT_DEBUG) echo "Importing: {$current_db_type}<br />";
	  if (!isset($_POST['dbParamHost']) || !isset($_POST['dbParamUsername']) || !isset($_POST['dbParamPassword']) || !isset($_POST['dbParamDatabase']))
	  {
	    $msg = LAN_CONVERT_41;
	  }
	  if (!$msg)
	  {
	    if (class_exists($current_db_type))
		{
		  $converter = new $current_db_type;
		}
		else
		{
		  $msg = LAN_CONVERT_42;
		}
	  }
	  if (!$msg)
	  {

		$result = $converter->db_Connect($_POST['dbParamHost'],	$_POST['dbParamUsername'], $_POST['dbParamPassword'], $_POST['dbParamDatabase'],  $_POST['dbParamPrefix']);



		if ($result !== TRUE)
		{
		  $msg = LAN_CONVERT_43.": ".$result;   // db connect failed
		}
	  }
	  if (!$msg)
	  {
		foreach ($db_import_blocks as $k => $v)
		{
		  if (isset($db_blocks_to_import[$k]))
		  {
			$loopCounter = 0;
			$errorCounter = 0;
			if (is_readable($v['classfile']))
			{
				require_once($v['classfile']);
			}
			else
			{
				$msg = LAN_CONVERT_45.': '.$v['classfile'];  // can't read class file.
			}
			if (!$msg && (varset($_POST["import_block_{$k}"],0) == 1))
			{
			  if (IMPORT_DEBUG) echo "Importing: {$k}<br />";
			  $result = $converter->setupQuery($k,!$import_delete_existing_data);
			  if ($result !== TRUE)
			  {
				$msg = LAN_CONVERT_44.' '.$k;
			//	$msg .= "Prefix = ".$converter->DBPrefix;
				break;
			  }
			  $exporter = new $v['classname'];		// Writes the output data
			  // Do any type-specific default setting
			  switch ($k)
			  {
				case 'users' :
				  $exporter->overrideDefault('user_class',$checked_class_list);
				  break;
			  }
			  if ($import_delete_existing_data) $exporter->emptyTargetDB();		// Clean output DB - reasonably safe now
			  while ($row = $converter->getNext($exporter->getDefaults()))
			  {
				$loopCounter++;
		   		$result = $exporter->saveData($row);
				if ($result !== TRUE)
				{
				  $errorCounter++;
				  $line_error = $exporter->getErrorText($result);
				  if ($msg) $msg .= "<br />";
				  $msg .= str_replace(array('--ERRNUM--','--DB--'),array($line_error,$k),LAN_CONVERT_46).$loopCounter;
				}
			  }
			  $converter->endQuery;
			  unset($exporter);
			  if ($msg) $msg .= "<br />";
			  $msg .= str_replace(array('--LINES--','--USERS--', '--ERRORS--','--BLOCK--'),
								array($loopCounter,$loopCounter-$errorCounter,$errorCounter, $k),LAN_CONVERT_47);
			}
		  }
		}
	  }
//	  $msg = LAN_CONVERT_29;
	  $abandon = FALSE;
	  break;
  }

  if ($msg)
  {
	$emessage->add($msg, E_MESSAGE_INFO); //  $ns -> tablerender(LAN_CONVERT_30, $msg);
	$msg = '';
  }

  if ($abandon)
  {
//	unset($_POST['do_conversion']);
  $text = "
	<form method='post' action='".e_SELF."'>
	<table style='width: 98%;' class='fborder'>
	<tr><td class='forumheader3' style='text-align:center'>
	<input class='button' type='submit' name='dummy_continue' value='".LAN_CONTINUE."' />
	</td>
	</tr>
	</table></form>";
	$ns -> tablerender(LAN_CONVERT_30, $text);
	require_once(e_ADMIN."footer.php");
	exit;
  }
}



//======================================================
// 					Display front page
//======================================================



if(varset($_POST['import_type']) || varset($_POST['do_conversion']))
{
	showImportOptions($_POST['import_type']);
}
else
{
	showStartPage();
}

  require_once(e_ADMIN."footer.php");
  exit;





function showStartPage()
{
    global $ns, $emessage, $frm, $import_class_names, $import_class_support, $db_import_blocks, $import_class_comment;

	$text = "
	<form method='post' action='".e_SELF."' id='core-import-form'>
		<fieldset id='core-import-select-type'>
		<legend class='e-hideme'>".DBLAN_10."</legend>
            <table cellpadding='0' cellspacing='0' class='adminlist'>
			<colgroup span='2'>
			<col />
			<col />
			<col />
			<col />
			<col />
			</colgroup>
			<thead>
			<tr>
            	<th>".LAN_CONVERT_06."</th>";
                foreach($db_import_blocks as $name)   // 1 column for each of users, news, forum etc.
				{
                	$text .= "<th class='center'>".$name['message']."</th>";
 				}

				$text.="
				<th class='center'>".LAN_OPTIONS."</th>

			</tr>
			</thead>
			<tbody>

			<tr>
			<td>CSV</td>
			<td class='center'>".ADMIN_TRUE_ICON."</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
	        <td class='center middle'>".$frm->radio('import_type', 'csv')."</td></tr>";


        foreach ($import_class_names as $k => $title)
		{
          	$text .= "<!-- $title -->
			<tr><td>".$title."<div class='smalltext'>".$import_class_comment[$k]."</div></td>\n";

			 foreach($db_import_blocks as $key=>$val)
			 {
 			 	$text .= "<td class='center'>".(in_array($key,$import_class_support[$k]) ? ADMIN_TRUE_ICON : "&nbsp;")."</td>\n";
			 }

             $text .= "
			 	<td class='center middle'>
			 		".$frm->radio('import_type', $k)."
			 		<!-- <input class='button' style='width: 100%' type='submit' name='db_update' value='".DBLAN_16."' /> -->
			 	</td>
			 </tr>";
		}


		$text .= "
				</tbody>
			</table>
			<div class='buttons-bar center'>
				".$frm->admin_button('trigger_import',LAN_CONTINUE, 'execute')."
			</div>
		</fieldset>
	</form>";

	$ns->tablerender(LAN_CONVERT_01, $emessage->render().$text);

}




function showImportOptions($mode='csv')
{
	global $text, $frm, $ns, $emessage, $csv_names, $import_class_names, $e_userclass, $db_import_blocks, $import_class_support, $import_default_prefix;

	$message = LAN_CONVERT_02."<br /><strong>".LAN_CONVERT_05."</strong>";
	$emessage->add($message, E_MESSAGE_WARNING);

	$text = "
	<form method='post' action='".e_SELF."'>
    <table cellpadding='0' cellspacing='0' class='adminform'>
    	<colgroup span='2'>
    		<col class='col-label' />
    		<col class='col-control' />
    	</colgroup>";

	if($mode == "csv")
	{
		$text .= "
		<tr>
		  <td>".LAN_CONVERT_07."</td>
		  <td><select name='csv_format' class='tbox'>\n";
		  foreach ($csv_names as $k => $v)
		  {
			$s = ($current_csv == $k) ? " selected='selected'" : '';
			$text .= "<option value='{$k}'{$s}>{$v}</option>\n";
		  }
	  	$text .= "</select>\n
		  </td>
		</tr>

		<tr>
		<td>".LAN_CONVERT_36."</td>
		<td><input class='tbox' type='text' name='csv_data_file' size='30' value='{$csv_data_file}' maxlength='100' /></td>
		</tr>

		<tr><td>".LAN_CONVERT_17."
		</td>
		<td>

		<input type='hidden' name='import_source' value='csv' />
		<input type='checkbox' name='csv_pw_not_encrypted' value='1'".($csv_pw_not_encrypted ? " checked='checked'" : '')."/>
		<span class='smallblacktext'>".LAN_CONVERT_18."</span></td>
		</tr>
		";

	}
	else
	{
    	$importType = $import_class_names[$mode];

    	$text .= "
		<tr>
		<td>$importType ".LAN_CONVERT_19."</td>
		<td><input class='tbox' type='text' name='dbParamHost' size='30' value='".(varset($_POST['dbParamHost']) ? $_POST['dbParamHost'] : 'localhost')."' maxlength='100' /></td>
		</tr>
		<tr>
		<td >$importType ".LAN_CONVERT_20."</td>
		<td ><input class='tbox' type='text' name='dbParamUsername' size='30' value='".$_POST['dbParamUsername']."'' maxlength='100' /></td>
		</tr>
		<tr>
		<td >$importType ".LAN_CONVERT_21."</td>
		<td ><input class='tbox' type='text' name='dbParamPassword' size='30' value='".$_POST['dbParamPassword']."' maxlength='100' /></td>
		</tr>
		<tr>
		<td >$importType ".LAN_CONVERT_22."</td>
		<td ><input class='tbox' type='text' name='dbParamDatabase' size='30' value='".$_POST['dbParamDatabase']."' maxlength='100' /></td>
		</tr>
		<tr>
		<td >$importType ".LAN_CONVERT_23."</td>
		<td ><input class='tbox' type='text' name='dbParamPrefix' size='30' value='".(varset($_POST['dbParamPrefix']) ? $_POST['dbParamPrefix'] : $import_default_prefix[$mode])."' maxlength='100' />
		<input type='hidden' name='import_source' value='db' />
  		</td>
		</tr>
		<tr>
		<td >$importType ".LAN_CONVERT_24."</td>
		<td >";

		$defCheck = (count($import_class_support[$mode])==1) ? "checked='checked'" : "";
   	  	foreach ($db_import_blocks as $k => $v)
	  	{
			if(in_array($k, $import_class_support[$mode])) // display only the options supported.
			{
				$text .= "<input type='checkbox' name='import_block_{$k}' id='import_block_{$k}' value='1' {$defCheck} />&nbsp;".$v['message'];
				$text .= "<br />";
			}
	  	}
	  	$text .= "</td></tr>";
	}

	$text .= "<tr><td>".LAN_CONVERT_38."</td>
	<td><input type='checkbox' name='import_delete_existing_data' value='1'".($import_delete_existing_data ? " checked='checked'" : '')."/>
	<span class='smallblacktext'>".LAN_CONVERT_39."</span></td>
	</tr>

	<tr><td>".LAN_CONVERT_16."</td>
	<td>";
  	$text .= $e_userclass->vetted_tree('classes_select',array($e_userclass,'checkbox'), $checked_class_list,'main,admin,classes,matchclass');

  	$text .= "</td></tr></table>
	<div class='buttons-bar center'>".$frm->admin_button('do_conversion',LAN_CONTINUE, 'execute')."
	<input type='hidden' name='db_import_type' value='$mode' />
	<input type='hidden' name='import_type' value='".$mode."' />
	</div>
	</form>";

	// Now a little bit of JS to initialise some of the display divs etc
  	$temp = '';
  	if ($import_source) $temp .=  "disp('{$import_source}');";
  	if ($current_db_type) $temp .= " flagbits('{$current_db_type}');";
  	if ($temp) $text .= "<script type=\"text/javascript\"> {$temp}</script>";

  	$ns -> tablerender(LAN_CONVERT_01." :: ".$importType, $emessage->render().$text);

}






function csv_split(&$data,$delim=',',$enveloper='')
{
  $ret_array = array();
  $fldval='';
  $enclosed = false;
// $fldcount=0;
// $linecount=0;
  for($i=0;$i<strlen($data);$i++)
  {
	$c=$data[$i];
	switch($c)
	{
	  case $enveloper :
		if($enclosed && ($i<strlen($data)) && ($data[$i+1]==$enveloper))
		{
		  $fldval .= $c;
		  $i++; //skip next char
		}
		else
		{
		  $enclosed  = !$enclosed;
		}
		break;

	  case $delim :
		if(!$enclosed)
		{
		  $ret_array[]= $fldval;
		  $fldval='';
		}
		else
		{
		  $fldval.=$c;
		}
		break;
	  case "\r":
	  case "\n":
		$fldval .= $c;	// We may want to strip these
		break;
	  default:
		$fldval .= $c;
	}
  }
  if($fldval)
	$ret_array[] = $fldval;
  return $ret_array;
}





function headerjs()
{
//  global $import_class_names;		// Keys are the various db options
  global $import_class_support;
  global $db_import_blocks;
  global $import_class_comment;

  $vals = "var db_names = new Array();\n";
  $texts = "var db_options = new Array();\n";
  $blocks = "var block_names = new Array();\n";
  $comments = "var comment_text = new Array();\n";
  
  $i = 0;
  foreach ($db_import_blocks as $it => $val)
  {
	$blocks .= "block_names[{$i}]='{$it}';\n";
	$i++;
  }

  $i = 0;
  foreach ($import_class_support as $k => $v)
  {
	$vals .= "db_names[$i] = '{$k}';\n";
	$comments .= "comment_text[$i] = '{$import_class_comment[$k]}';\n";
//	$temp = $import_class_support[$k];		// Array of import types supported
	$j = 0;
	$m = 1;		// Mask bit
	foreach ($db_import_blocks as $it => $val)
	{
	  if (in_array($it,$v)) $j = $j + $m;
	  $m = $m + $m;
	}
	$texts .= "db_options[{$i}] = {$j};\n";
	$i++;
  }

  $text = "
	<script type='text/javascript'>{$vals}{$texts}{$blocks}{$comments}
	function disp(type) 
	{
	  if(type == 'csv')
	  {
		document.getElementById('import_csv').style.display = '';
		document.getElementById('import_db').style.display = 'none';
		return;
	  }

	  if(type =='db')
	  {
        document.getElementById('import_csv').style.display = 'none';
		document.getElementById('import_db').style.display = '';
		return;
	  }
	}
	
	function flagbits(type)
	{
	  var i,j;
	  for (i = 0; i < ".count($import_class_support)."; i++)
	  {
	    if (type == db_names[i])
		{
		  var mask = 1;
		  for (j = 0; j < ".count($db_import_blocks)."; j++)
		  {
			var checkbox = document.getElementById('import_block_'+block_names[j]);
			if (checkbox != null)
			{
			  if (db_options[i] & mask)
			  {
			    checkbox.checked = 'checked';
				checkbox.disabled = '';
			  }
			  else
			  {
				checkbox.checked = '';
				checkbox.disabled = 'disabled';
			  }
			}
			else
			{
			  alert('Could not find: '+'import_block_'+block_names[j]);
			}
			mask = mask + mask;
		  }
		  var checkbox = document.getElementById('db_comment_block');
		  if (checkbox != null) checkbox.innerHTML = comment_text[i];
		  return;
		}
	  }
	  alert('Type not found: '+type);
	}
	</script>";

	return $text;
}



?>