<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - DB Verify
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/db_verify.php,v $
 * $Revision: 1.8 $
 * $Date: 2009-09-10 19:15:43 $
 * $Author: secretr $
 *
*/
require_once("../class2.php");

if(varset($_POST['db_tools_back']))
{
	header("Location:".e_ADMIN_ABS."db.php");
	exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'database';

require_once("auth.php");

require_once(e_HANDLER."form_handler.php");
$frm = new e_form();

require_once (e_HANDLER."message_handler.php");
$emessage = &eMessage::getInstance();

$sql_data = file_get_contents(e_ADMIN.'sql/core_sql.php');
if (!$sql_data)
{
	exit(DBLAN_1);
}

$tables["core"] = $sql_data;

if (!getperms("0"))
{
	header("Location:".SITEURL."index.php");
	exit;
}

//Get any plugin _sql.php files
foreach($pref['e_sql_list'] as $path => $file)
{
	$filename = e_PLUGIN.$path."/".$file.".php";
	if(is_readable($filename))
	{
		$id = str_replace("_sql","",$file);
      	$tables[$id] = file_get_contents($filename);
	}
	else
	{
      	$emessage->add($filename.DBLAN_22, E_MESSAGE_WARNING);
	}
}



function read_tables($tab)
{
	global $tablines, $table_list, $tables, $pref;

	$file = split("\n", $tables[$tab]);
	foreach($file as $line)
	{
		$line = ltrim(stripslashes($line));
		$match = array();
		if (preg_match('/CREATE TABLE (.*) /', $line, $match))
		{
			if($match[1] != "user_extended")
			{
				$table_list[$match[1]]  = 1;
				$current_table = $match[1];
				$x = 0;
				$cnt = 0;
			}
		}

		if (strpos($line, "TYPE=") !== FALSE)
		{
			$current_table = "";
		}

		if ($current_table && $x)
		{
			$tablines[$current_table][$cnt++] = $line;
		}

		$x = 1;
	}

// Get multi-language tables as well
	if($pref['multilanguage'])
	{
		$langs = table_list();
		foreach(array_keys($table_list) as $name)
		{
			if($langs[$name])
			{
				$ltab = $langs[$name];
				$table_list[$ltab] = 1;
				$tablines[$ltab] = $tablines[$name];
			}
		}
	}
}


// Get list of fields and keys for a table
function get_current($tab, $prefix = "")
{
	if(! $prefix)
	{
		$prefix = MPREFIX;
	}
	mysql_query('SET SQL_QUOTE_SHOW_CREATE = 1');
	$qry = 'SHOW CREATE TABLE `' . $prefix . $tab . "`";
	$z = mysql_query($qry);
	if($z)
	{
		$row = mysql_fetch_row($z);
		return str_replace("`", "", stripslashes($row[1]));
	}
	else
	{
		return FALSE;
	}
}

function check_tables($what)
{
	global $tablines, $table_list, $frm, $emessage;

	$cur = 0;
	$table_list = "";
	read_tables($what);

	$fix_active = FALSE;			// Flag set as soon as there's a fix - enables 'Fix it' button

	$text = "
		<form method='post' action='".e_SELF."'>
			<fieldset id='core-db-verify-{$what}'>
				<legend id='core-db-verify-{$what}-legend'>".DBLAN_16." - $what ".DBLAN_18."</legend>
	";
	foreach(array_keys($table_list) as $k)
	{	// $k is the DB table name (less prefix)
		$ttcount = 0;
		$ttext = "
				<table cellpadding='0' cellspacing='0' class='adminlist'>
					<colgroup span='4'>
						<col style='width: 25%'></col>
						<col style='width: 25%'></col>
						<col style='width: 10%'></col>
						<col style='width: 30%'></col>
						<col style='width: 10%'></col>
					</colgroup>
					<thead>
						<tr>
							<th>".DBLAN_4.": {$k}</th>
							<th>".DBLAN_5."</th>
							<th class='center'>".DBLAN_6."</th>
							<th>".DBLAN_7."</th>
							<th class='center last'>".DBLAN_19."</th>
						</tr>
					</thead>
					<tbody>
		";

		$prefix = MPREFIX;
		$current_tab = get_current($k, $prefix);		// Get list of fields and keys from actual table
		unset($fields);
		unset($xfields);
		$xfield_errors = 0;

		if ($current_tab)
		{
			$lines = split("\n", $current_tab);			// Create one element of $lines per field or other line of info
			$fieldnum = 0;
			foreach($tablines[$k] as $x)
			{	// $x is a line of the DB definition from the *_sql.php file
		  		$x = str_replace('  ',' ',$x);				// Remove double spaces
		  		$fieldnum++;
		 		 $ffound = 0;
		  		list($fname, $fparams) = explode(" ", $x, 2);
				if ($fname == "UNIQUE" || $fname == 'FULLTEXT')
				{
					list($key, $key1, $keyname, $keyparms) = split(" ", $x, 4);
					$fname = $key." ".$key1." ".$keyname;
					$fparams = $keyparms;
				}
				elseif ($fname == "KEY")
		  		{
					list($key, $keyname, $keyparms) = split(" ", $x, 3);
					$fname = $key." ".$keyname;
					$fparams = $keyparms;
		  		}
		  		$fields[$fname] = 1;
		 		$fparams = ltrim(rtrim($fparams));
		  		$fparams = preg_replace("/\r?\n$|\r[^\n]$|,$/", "", $fparams);


		 		if(stristr($k, "lan_") !== FALSE && $cur != 1)
				{
					$cur = 1;
				}

				$head_txt = "
							<tr>
								<td>{$k}</td>
								<td>{$fname} 
				";

				if (strpos($fparams, "KEY") !== FALSE)
				{
					$head_txt .= " $fparams aa";
				}

				$head_txt .= "
								</td>
				";

				$xfieldnum = -1;
				$body_txt = '';

				foreach($lines as $l)
				{
					$xfieldnum++;
					list($xl, $tmp) = explode("\n", $l, 2);			// $tmp should be null

					$xl = ltrim(rtrim(stripslashes($xl)));
					$xl = preg_replace('/\r?\n$|\r[^\n]$/', '', $xl);
					$xl = str_replace('  ',' ',$xl);				// Remove double spaces
					list($xfname, $xfparams) = explode(" ", $xl, 2);	// Field name and the rest
					
					if ($xfname == 'UNIQUE' || $xfname == 'FULLTEXT')
					{
						list($key, $key1, $keyname, $keyparms) = explode(" ", $xl, 4);
						$xfname = $key." ".$key1." ".$keyname;
						$xfparams = $keyparms;
					}
					elseif ($xfname == "KEY")
					{
						list($key, $keyname, $keyparms) = explode(" ", $xl, 3);
						$xfname = $key." ".$keyname;
						$xfparams = $keyparms;
					}
					
					if ($xfname != "CREATE" && $xfname != ")")
					{
						$xfields[$xfname] = 1;
					}
					$xfparams = preg_replace('/,$/', '', $xfparams);
					$fparams = preg_replace('/,$/', '', $fparams);
					if ($xfname == $fname)
					{  // Field names match - or it could be the word 'KEY' and its name which matches
						$ffound = 1;
						//echo "Field: ".$xfname."   Actuals: ".$xfparams."   Expected: ".$fparams."<br />";
						$xfsplit = explode(' ',$xfparams);
						$fsplit  = explode(' ',$fparams);
						$skip = FALSE;
						$i = 0;
						$fld_err = FALSE;
						foreach ($xfsplit as $xf)
						{
							if ($skip)
							{
								$skip = FALSE;
								// echo "  Unskip: ".$xf."<br />";
							}
							elseif (strcasecmp(trim($xf),'collate') == 0)
							{	// Strip out the collation definition
								$skip = TRUE;
							// cho "Skip = ".$xf;
							}
							else
							{
							// echo "Compare: ".$xf." - ".$fsplit[$i]."<br />";
							// Since VARCHAR and CHAR are interchangeable, convert to CHAR (strictly, VARCHAR(3) and smalller becomes CHAR() )
								if (stripos($xf,'VARCHAR') === 0) $xf = substr($xf,3);
								if (stripos($fsplit[$i],'VARCHAR') === 0) $fsplit[$i] = substr($fsplit[$i],3);
								if (strcasecmp(trim($xf),trim($fsplit[$i])) != 0)
								{
									$fld_err = TRUE;
								//echo "Mismatch: ".$xf." - ".$fsplit[$i]."<br />";
								}
							$i++;
							}
						}

						if ($fld_err)
						{
							$body_txt .= "
								<td class='center middle error'>".DBLAN_8."</td>
								<td>
									<strong>".DBLAN_9."</strong>
									<div class='indent'>{$xfparams}</div>
									<strong>".DBLAN_10."</strong>
									<div class='indent'>{$fparams}</div>
								</td>
								<td class='center middle autocheck e-pointer'>".fix_form($k, $fname, $fparams, "alter")."</td>
							";
							$fix_active = TRUE;
							$xfield_errors++;
						}
						/* FIXME - can't stay if there is no way of fixing the field numbers (e.g. AFTER query)
						elseif ($fieldnum != $xfieldnum)
						{  // Field numbers different - missing field?
							$body_txt .= "
								<td class='center middle error'>".DBLAN_5." ".DBLAN_8."</td>
								<td>
									<strong>".DBLAN_9.": </strong>#{$xfieldnum}
									<br />
									<strong>".DBLAN_10.": </strong>#{$fieldnum}
								</td>
								<td class='center middle'>&nbsp;</td>

							";
						}
						*/
						
						/* DISABLED for now (show only errors), could be page setting
						else
						{
							$body_txt .= "
								<td class='center'>OK</td>
								<td>&nbsp;</td>
								<td class='center middle'>&nbsp;</td>
							";
						}
						*/
					}
				}	// Finished checking one field

				if ($ffound == 0)
				{
					$prev_fname = $fname; //FIXME - wrong $prev_fname!
					$body_txt .= "
								<td class='center middle error'>".DBLAN_11."</td>
								<td>
									<strong>".DBLAN_10."</strong>
									<div class='indent'>{$fparams}</div>
								</td>
								<td class='center middle autocheck e-pointer'>".fix_form($k, $fname, $fparams, "insert"/*, $prev_fname*/)."</td>
					";
					$fix_active = TRUE;
					$xfield_errors++;
				}

				if($xfield_errors && $body_txt)
				{
					$ttext .= $head_txt.$body_txt."
							</tr>
					";
				}


			}

			foreach(array_keys($xfields) as $tf)
			{
				if (!$fields[$tf] && $k != "user_extended")
				{
					$fix_active = TRUE;
					$xfield_errors++;
					$ttext .= "
					<tr>
						<td>$k</td>
						<td>$tf</td>
						<td class='center middle'>".DBLAN_12."</td>
						<td>&nbsp;</td>
						<td class='center middle autocheck e-pointer'>".fix_form($k, $tf, $fparams, "drop")."</td>
					</tr>
					";
				}
			}
			
			
		}
		else
		{	// Table Missing.
			$ttext .= "
					<tr>
						<td>{$k}</td>
						<td>&nbsp;</td>
						<td class='center middle error'>".DBLAN_13."</td>
						<td>&nbsp;</td>
						<td class='center middle autocheck e-pointer'>".fix_form($k, $tf, $tablines[$k], "create") . "</td>
					</tr>
			";

			$fix_active = TRUE;
			$xfield_errors++;
		}
		
		if(!$xfield_errors)
		{
			//no errors, so no table rows yet
			$ttext .= "
					<tr>
						<td colspan='5' class='center'>Table status OK</td>
					</tr>
			";
		}
	
		$ttext .= "
					</tbody>
				</table>
				<br/>
		";
		
		//FIXME - add 'show_if_ok' switch
		if($xfield_errors || (!$xfield_errors && varsettrue($_GET['show_if_ok'])))
		{
			$text .= $ttext;
			$ttcount++;
		}
	}
	
	if(!$fix_active)
	{
		//Everything should be OK
		$emessage->add('DB successfully verified - no problems were found.', E_MESSAGE_SUCCESS);
		
		if(!$ttcount)
		{
			//very tired and sick of this page, so quick and dirty
			$text .= "
					<script type='text/javascript'>
						\$('core-db-verify-{$what}-legend').hide();
					</script>
			";
		}
	}
	
	if($fix_active)
	{
		$text .= "
			<div class='buttons-bar right'>
				".$frm->admin_button('do_fix', DBLAN_21, 'execute', '', array('id'=>false))."
				".$frm->admin_button('check_all', 'jstarget:fix_active', 'action', LAN_CHECKALL, array('id'=>false))."
				".$frm->admin_button('uncheck_all', 'jstarget:fix_active', 'action', LAN_UNCHECKALL, array('id'=>false))."
			</div>
		";
	}
	
	foreach(array_keys($_POST) as $j) 
	{
		$match = array();
		if (preg_match('/table_(.*)/', $j, $match))
		{
			$lx = $match[1];
			$text .= "<div><input type='hidden' name='table_{$lx}' value='1' /></div>\n";
		}
	}

	$text .= "
		</fieldset>
		<div class='buttons-bar center'>
			".$frm->admin_button('back', DBLAN_17, 'back')."
		</div>
	</form>

	";

	return $text;
}

global $table_list;

// -------------------- Table Fixing ------------------------------

if(isset($_POST['do_fix']))
{
	//$emessage->add(DBLAN_20);
	foreach( $_POST['fix_active'] as $key=>$val)
	{

		if (MAGIC_QUOTES_GPC == TRUE)
		{
			$table = stripslashes($_POST['fix_table'][$key][0]);
			$newval = stripslashes($_POST['fix_newval'][$key][0]);
			$mode = stripslashes($_POST['fix_mode'][$key][0]);
			$after = stripslashes($_POST['fix_after'][$key][0]);
		}
		else
		{
			$table = $_POST['fix_table'][$key][0];
			$newval = $_POST['fix_newval'][$key][0];
			$mode = $_POST['fix_mode'][$key][0];
			$after = $_POST['fix_after'][$key][0];
		}


		$field= $key;
		
		switch($mode)
		{
			case 'alter':
				$query = "ALTER TABLE `".MPREFIX.$table."` CHANGE `$field` `$field` $newval";
			break;

			case 'insert':
				if($after) $after = " AFTER {$after}";
				$query = "ALTER TABLE `".MPREFIX.$table."` ADD `$field` $newval{$after}";
			break;
			
			case 'drop':
				$query = "ALTER TABLE `".MPREFIX.$table."` DROP `$field` ";
			break;
			
			case 'index':
				$query = "ALTER TABLE `".MPREFIX.$table."` ADD INDEX `$field` ($newval)";
			break;
			
			case 'indexalt':
				$query = "ALTER TABLE `".MPREFIX.$table."` ADD $field ($newval)";
			break;
			
			case 'indexdrop':
				$query = "ALTER TABLE `".MPREFIX.$table."` DROP INDEX `$field`";
			break;
			
			case 'create':
				$query = "CREATE TABLE ".MPREFIX.$table." ($newval) TYPE=MyISAM;";
			break;
		}

		//FIXME - db handler!!!
		if(mysql_query($query)) $emessage->add(LAN_UPDATED.' [&nbsp;'.$query.'&nbsp;]', E_MESSAGE_SUCCESS);
		else 
		{
			$emessage->add(LAN_UPDATED_FAILED.' [&nbsp;'.$query.'&nbsp;]', E_MESSAGE_WARNING);
			if(mysql_errno())
			{
				$emessage->add('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SQL #'.mysql_errno().': '.mysql_error(), E_MESSAGE_WARNING);
			}
		}
	}
}


// ---------------------- Main Form and Submit. ------------------------
if (varset($_POST['db_verify']) || varset($_POST['do_fix']))
{
	$text = '';
	foreach(array_keys($_POST) as $k)
	{
		$match = array();
		if (preg_match("/table_(.*)/", $k, $match))
		{
			$xx = $match[1];
			$text .= check_tables($xx);

		}
	}

	if(!$text) $emessage->add(DBLAN_24, E_MESSAGE_WARNING);
	else
	{
		$e107->ns->tablerender(DBLAN_23.' - '.DBLAN_16, $emessage->render().$text);
		require_once(e_ADMIN."footer.php");
		exit;
	}
}

$text = "
	<form method='post' action='".e_SELF.(e_QUERY ? '?'.e_QUERY : '')."' id='core-db-verify-sql-tables-form'>
		<fieldset id='core-db-verify-sql-tables'>
			<legend>".DBLAN_14."</legend>
			<table cellpadding='0' cellspacing='0' class='adminlist'>
				<colgroup span='1'>
					<col style='width: 100%'></col>
				</colgroup>
				<thead>
					<tr>
						<th class='last'>".$frm->checkbox_toggle('check-all-verify', 'table_').LAN_CHECKALL.' | '.LAN_UNCHECKALL."</th>
					</tr>
				</thead>
				<tbody>
";

foreach(array_keys($tables) as $x) {
	$text .= "
						<tr>
							<td>
								".$frm->checkbox('table_'.$x, $x).$frm->label($x, 'table_'.$x, $x)."
							</td>
						</tr>
	";
}

$text .= "
					</tbody>
				</table>
				<div class='buttons-bar center'>
					".$frm->admin_button('db_verify', DBLAN_15)."
					".$frm->admin_button('db_tools_back', DBLAN_17, 'back')."
				</div>
			</fieldset>
		</form>
";

$e107->ns->tablerender(DBLAN_23.' - '.DBLAN_16, $emessage->render().$text);
require_once(e_ADMIN."footer.php");
exit;

// --------------------------------------------------------------
function fix_form($table,$field, $newvalue,$mode,$after =''){
	global $frm;
	
	if($mode == 'create')
	{
		$newvalue = implode("\n",$newvalue);
	}
	else
	{
		if(stristr($field, 'KEY ') !== FALSE)
		{
			$field = chop(str_replace('KEY ','',$field));
			$mode = ($mode == 'drop') ? 'indexdrop' : 'index';
			$search = array('(', ')');
			$newvalue = str_replace($search,'',$newvalue);
			$after = '';
		}
		
		if($mode == 'index' && (stristr($field, 'FULLTEXT ') !== FALSE || stristr($field, 'UNIQUE ') !== FALSE))
		{
			$mode = 'indexalt';
		}
		elseif($mode == 'indexdrop' && (stristr($field, 'FULLTEXT ') !== FALSE || stristr($field, 'UNIQUE ') !== FALSE))
		{
			$field = trim(str_replace(array('FULLTEXT ', 'UNIQUE '), '', $field));
		}
		$field = trim($field, '`');
	}

	$text .= $frm->checkbox("fix_active[$field][]", 1, false, array('id'=>false));
	$text .= "<input type='hidden' name=\"fix_newval[$field][]\" value=\"$newvalue\" />\n";
	$text .= "<input type='hidden'  name=\"fix_table[$field][]\" value=\"$table\" />\n";
	$text .= "<input type='hidden'  name=\"fix_mode[$field][]\" value=\"$mode\" />\n";
	$text .= ($after) ? "<input type='hidden'  name=\"fix_after[$field][]\" value=\"$after\" />\n" : "";

	return $text;
}

function table_list()
{
	// grab default language lists.
	global $mySQLdefaultdb;

	$exclude[] = "banlist";		$exclude[] = "banner";
	$exclude[] = "cache";		$exclude[] = "core";
	$exclude[] = "online";		$exclude[] = "parser";
	$exclude[] = "plugin";		$exclude[] = "user";
	$exclude[] = "upload";		$exclude[] = "userclass_classes";
	$exclude[] = "rbinary";		$exclude[] = "session";
	$exclude[] = "tmp";	 		$exclude[] = "flood";
	$exclude[] = "stat_info";	$exclude[] = "stat_last";
	$exclude[] = "submit_news";	$exclude[] = "rate";
	$exclude[] = "stat_counter";$exclude[] = "user_extended";
	$exclude[] = "user_extended_struc";
	$exclude[] = "pm_messages";
	$exclude[] = "pm_blocks";

	$tables = mysql_list_tables($mySQLdefaultdb);
	while (list($temp) = mysql_fetch_array($tables))
	{
		$prefix = MPREFIX."lan_";
		$match = array();
		if(preg_match('/^'.$prefix.'(.*)/', $temp, $match))
		{
			$e107tab = str_replace(MPREFIX, "", $temp);
			$pos = strrpos($match[1],"_")+1;
			$core = substr(str_replace("lan_","",$e107tab),$pos);
			if (str_replace($exclude, "", $e107tab))
			{
				$tabs[$core] = $e107tab;
			}
		}
	}

	return $tabs;
}

/**
 * Handle page DOM within the page header
 *
 * @return string JS source
 */
function headerjs()
{
	require_once (e_HANDLER.'js_helper.php');
	$ret = "
		<script type='text/javascript'>
			if(typeof e107Admin == 'undefined') var e107Admin = {}

			/**
			 * OnLoad Init Control
			 */
			e107Admin.initRules = {
				'Helper': true,
				'AdminMenu': false
			}
		</script>
		<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>
	";

	return $ret;
}
?>