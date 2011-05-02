<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
$e_sub_cat = 'database';
require_once("auth.php");


$filename = "sql/core_sql.php";
$fd = fopen ($filename, "r");
$sql_data = @fread($fd, filesize($filename));
fclose ($fd);

if (!$sql_data) {
	echo DBLAN_1."<br /><br />";
	exit;
}

 $tables["core"] = $sql_data;

if (!getperms("0")) {
	header("location:".e_BASE."index.php");
	exit;
}

//Get any plugin _sql.php files

    foreach($pref['e_sql_list'] as $path => $file)
    {
        $filename = e_PLUGIN.$path."/".$file.".php";
		if(is_readable($filename)){
        	$fd = fopen($filename, "r");
        	$sql_data = fread($fd, filesize($filename));
        	fclose ($fd);
			$id = str_replace("_sql","",$file);
        	$tables[$id] = $sql_data;
		}else{
        	echo $filename.DBLAN_22."<br />";
		}
    }



function read_tables($tab) 
{
	global $tablines;
	global $table_list;
	global $tables,$sql,$pref;

	$file = explode("\n", $tables[$tab]);
	foreach($file as $line) 
	{
		$line = ltrim(stripslashes($line));
		if ($line)
		{
			if (preg_match("/CREATE TABLE (.*) /", $line, $match)) 
			{
				if($match[1] != "user_extended"){
					$current_table = str_replace('`','',$match[1]);
					$table_list[$current_table]  = 1;
					$x = 0;
					$cnt = 0;
				}
			}

			if ((strpos($line, "TYPE=") !== FALSE) || (strpos($line, "ENGINE=") !== FALSE))
			{
				$current_table = "";
			}

			if ($current_table && $x) {
				$tablines[$current_table][$cnt++] = $line;

			}

			$x = 1;
		}
	}

// Get multi-language tables as well
	if($pref['multilanguage']){
		$langs = table_list();
		foreach($table_list as $name=>$stuff){
			if($langs[$name]){
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
  if (!$prefix) 
  {
	$prefix = MPREFIX;
  }
  $result = mysql_query('SET SQL_QUOTE_SHOW_CREATE = 1');
  $qry = 'SHOW CREATE TABLE `'.$prefix.$tab."`";
  $z = mysql_query($qry);
  if ($z) 
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
	global $tablines;
	global $table_list;
	global $ns;
	$cur=0;
	$table_list = "";
	read_tables($what);

	$fix_active = FALSE;			// Flag set as soon as there's a fix - enables 'Fix it' button
	
	$text = "<form method='post' action='".e_SELF."' id='checktab'>
		<div style='text-align:center'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td class='fcaption' style='text-align:center'>".DBLAN_4."</td>
		<td class='fcaption' style='text-align:center'>".DBLAN_5."</td>
		<td class='fcaption' style='text-align:center'>".DBLAN_6."</td>
		<td class='fcaption' style='text-align:center'>".DBLAN_7."</td>
		</tr>";
	foreach(array_keys($table_list) as $k) 
	{	// $k is the DB table name (less prefix)
		$prefix = MPREFIX;
		$current_tab = get_current($k, $prefix);		// Get list of fields and keys from actual table
		unset($fields);
		unset($xfields);
		if ($current_tab) 
		{
			$lines = explode("\n", $current_tab);			// Create one element of $lines per field or other line of info
			$fieldnum = 0;
			foreach($tablines[$k] as $x) 
			{	// $x is a line of the DB definition from the *_sql.php file
				$x = str_replace('  ',' ',$x);				// Remove double spaces
				$x = str_replace('`','',$x);				// Remove backticks
				$fieldnum++;
				$ffound = 0;
				list($fname, $fparams) = explode(" ", $x, 2);
				switch ($fname)
				{
					case 'KEY' :
						list($key, $keyname, $keyparms) = explode(" ", $x, 3);
						$fname = $key." ".$keyname;
						$fparams = $keyparms;
						break;
					case 'PRIMARY' :
						// Nothing to do ATM
						break;
					case 'UNIQUE' :
						list($keyword, $key, $keyname, $keyparms) = explode(" ", $x, 4);
						$fname = 'UNIQUE '.$key.' '.$keyname;
						$fparams = $keyparms;
						break;
						echo 'Unique field entry: '.$x.'<br />';
						break;
					default :
						// Must be a field name
						$fname = str_replace('`','',$fname);		// Just remove back ticks if present
				}
				$fields[$fname] = 1;
				$fparams = ltrim(rtrim($fparams));
				$fparams = preg_replace("/\r?\n$|\r[^\n]$|,$/", "", $fparams);

				if(stristr($k, "lan_") !== FALSE && $cur != 1)
				{
					$text .= "<tr><td colspan='6' class='fcaption'>".ADLAN_132."</td></tr>";
					$cur = 1;
				};



				$text .= "<tr><td class='forumheader3'>$k</td><td class='forumheader3'>$fname";
				if (strpos($fparams, "KEY") !== FALSE) 
				{
					$text .= " $fparams";
				}
				$text .= "</td>";
				$s = 0;
				$xfieldnum = -1;
				foreach($lines as $l) 
				{
					$xfieldnum++;
					list($xl, $tmp) = explode("\n", $l, 2);			// $tmp should be null
					$xl = ltrim(rtrim(stripslashes($xl)));
					$xl = preg_replace("/\r?\n$|\r[^\n]$/", "", $xl);
					$xl = str_replace('  ',' ',$xl);				// Remove double spaces
					list($xfname, $xfparams) = explode(" ", $xl, 2);	// Field name and the rest

					if ($xfname == 'UNIQUE') 
					{
						list($keyword, $key, $keyname, $keyparms) = explode(" ", $xl, 4);
						if ($key != 'KEY')
						{
							echo 'Invalid parameters to line beginning \'UNIQUE\': '.$xl.'<br />';
						}
						$xfname = 'UNIQUE '.$key.' '.$keyname;
						$xfparams = $keyparms;
					}
					if ($xfname == "KEY") 
					{
						list($key, $keyname, $keyparms) = explode(" ", $xl, 3);
						$xfname = $key." ".$keyname;
						$xfparams = $keyparms;
					}

					if ($xfname != "CREATE" && $xfname != ")") 
					{
						$xfields[$xfname] = 1;
					}
					$xfparams = preg_replace("/,$/", "", $xfparams);
					$fparams = preg_replace("/,$/", "", $fparams);
					if ($xfname == $fname) 
					{  // Field names match - or it could be the word 'KEY' or 'UNIQUE KEY' and its name which matches
						$ffound = 1;
						//	echo "Field: ".$xfname."   Actuals: ".$xfparams."   Expected: ".$fparams."<br />";
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
								//	echo "  Unskip: ".$xf."<br />";
							}
							elseif (strcasecmp(trim($xf),'collate') == 0)
							{	// Strip out the collation definition
								$skip = TRUE;
								//	echo "Skip = ".$xf;
							}
							else
							{
								//	echo "Compare: ".$xf." - ".$fsplit[$i]."<br />";
								// Since VARCHAR and CHAR are interchangeable, convert to CHAR (strictly, VARCHAR(3) and smalller becomes CHAR() )
								if (stripos($xf,'VARCHAR') === 0) $xf = substr($xf,3);
								if (stripos($fsplit[$i],'VARCHAR') === 0) $fsplit[$i] = substr($fsplit[$i],3);
								if (strcasecmp(trim($xf),trim($fsplit[$i])) != 0) 
								{
									$fld_err = TRUE;
			//						echo "Mismatch: ".$xf." - ".$fsplit[$i]."<br />";
								}
								$i++;
							}
						}

						if ($fld_err)
						{
							$text .= "<td class='forumheader' style='text-align:center'>".DBLAN_8."</td>";
							$text .= "<td class='forumheader3' style='text-align:center'>".DBLAN_9."<div class='indent'>".$xfparams."</div><b>".DBLAN_10."</b><div class='indent'>".$fparams." <br />".fix_form($k,$fname,$fparams,"alter")."</div></td>";
							$fix_active = TRUE;
						} 
						elseif ($fieldnum != $xfieldnum) 
						{  // Field numbers different - missing field?
							$text .= "<td class='fcaption' style='text-align:center'>".DBLAN_5." ".DBLAN_8."</td>
								<td class='forumheader3' style='text-align:center'>".DBLAN_9." #{$xfieldnum}<br />".DBLAN_10." #{$fieldnum}</td>";
						} 
						else 
						{
							$text .= "<td class='forumheader3' style='text-align:center;'>OK</td>
								<td class='forumheader3' style='text-align:center'>&nbsp;</td>";
						}
					}
				}		// Finished checking one field

				if ($ffound == 0) 
				{
					$text .= "<td class='forumheader' style='text-align:center'><strong><em>".DBLAN_11."</em></strong></td>
						<td class='forumheader3' style='text-align:center'><b>".DBLAN_10." [$fparams]</b><br />".fix_form($k,$fname,$fparams,"insert",$prev_fname)."<br /></td>";
					$fix_active = TRUE;
				}
				$prev_fname = $fname;
				$text .= "</tr>\n";
			}
			foreach(array_keys($xfields) as $tf) 	// Pick up extra fields?
			{
				if (!$fields[$tf] && $k != "user_extended") 
				{
					$fix_active = TRUE;
					$text .= "<tr><td class='forumheader3' style='text-align:center'>{$k}</td><td class='forumheader3' style='text-align:center'>$tf</td><td class='forumheader3' style='text-align:center'><strong><em>".DBLAN_12."</em></strong></td><td class='forumheader3' style='text-align:center'>&nbsp;".fix_form($k,$tf,$fparams,"drop")."</td></tr>";
				}
			}
		} 
		else 
		{	// Table Missing.  $k is table name.  $tf is field
			$text .= "<tr><td class='forumheader3' style='text-align:center'>{$k}</td><td class='forumheader3' style='text-align:center'>&nbsp;</td><td class='forumheader' style='text-align:center'>".DBLAN_13."</td><td class='forumheader3' style='text-align:center'>&nbsp;".fix_form($k,$tf,$tablines[$k],'create')."</td></tr>";
			$fix_active = TRUE;
		}
	}
	$text .= "</table></div>";

	if($fix_active)
	{
		$text .= "<div style='".ADMIN_WIDTH.";text-align:right'>
		<input class='button' type='submit' name='do_fix' value='".DBLAN_21."' /></div>\n";
	}

	foreach(array_keys($_POST) as $j) 
	{
		if (preg_match("/table_(.*)/", $j, $mitch)) 
		{
			$lx = $mitch[1];
			$text .= "<input type='hidden' name='table_{$lx}' value='1' />\n";
		}
	}
	$text .= "</form>";

	return $text;
}

global $table_list;




/**
 *		Fix tables as selected
 */
if(isset($_POST['do_fix']))
{
	$text = "<div><table class='fborder' style='".ADMIN_WIDTH."'>";
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

		if($mode == "alter")
		{
			$query = "ALTER TABLE `".MPREFIX.$table."` CHANGE `$field` `$field` $newval";
		}

		if($mode == "insert")
		{
			$query = "ALTER TABLE `".MPREFIX.$table."` ADD `$field` $newval AFTER $after";
		}

		if($mode == "drop")
		{
			$query = "ALTER TABLE `".MPREFIX.$table."` DROP `$field` ";
		}

		if($mode == "index")
		{
			$query = "ALTER TABLE `".MPREFIX.$table."` ADD INDEX `$field` (`$newval`)";
		}

		if($mode == "indexunique")
		{
			$query = "ALTER TABLE `".MPREFIX.$table."` ADD UNIQUE INDEX `$field` (`$newval`)";
		}

		if($mode == "indexdrop")
		{
			$query = "ALTER TABLE `".MPREFIX.$table."` DROP INDEX `$field`";
		}

		if($mode == "create")
		{
			//$query = "CREATE TABLE ".MPREFIX.$table." ($newval) ENGINE=MyISAM;";
			$query = "CREATE TABLE `".MPREFIX.$table."` ({$newval}";
			if (!preg_match('#.*?\s+?(?:TYPE|ENGINE)\s*\=\s*(.*?);#is', $newval))
			{
				$query .= ') ENGINE=MyISAM;';
			}
		}


		$text .= "<tr><td class='forumheader3' style='vertical-align:top;width:70%'>".$query."</td><td class='forumheader3' style='vertical-align:top;width:30%'>";
		$text .= (mysql_query($query)) ? " - <b>".LAN_UPDATED."</b>" : " - <b>".LAN_UPDATED_FAILED."</b>";
		$text .= "</td></tr>";


	}
		$text .= "</table></div>";
		$text .="<div style='text-align:center'><br />
				<form method='post' action='db.php'>
				<input class='button' type='submit' name='back' value='".DBLAN_17."' />
				</form>
				</div>";

	$ns -> tablerender(DBLAN_20, $text);
}



// ---------------------- Main Form and Submit. ------------------------
if (!$_POST['db_verify'] && !$_POST['do_fix']) 
{
	$text = "
		<form method='post' action='".e_SELF."'>
		<table border=0 align='center'>
		<tr><td>".DBLAN_14."<br /><br />";
	foreach(array_keys($tables) as $x) {
		$text .= "<input type='checkbox' name='table_".$x."' />".$x."<br />";
	}
	$text .= "
		<br /><input class='button' name='db_verify' type='submit' value='".DBLAN_15."' />
		</td></tr></table></form>";
	$ns->tablerender(DBLAN_16, $text);
} 
else 
{
	foreach(array_keys($_POST) as $k) 
	{
		if (preg_match("/table_(.*)/", $k, $match)) 
		{
			$xx = $match[1];
			$str = "<br />
				<div style='text-align:center'>
				<form method='post' action='db.php'>
				<input class='button' type='submit' name='back' value='".DBLAN_17."' />
				</form>
				</div>";
			$ns->tablerender(DBLAN_16." - $xx ".DBLAN_18, check_tables($xx).$str);
		}
	}
}



/**
 *	Generate the display code and hidden values needed for the fix
 */
function fix_form($table, $field, $newvalue, $mode, $after ='')
{
	//echo "fix_form: {$mode} => {$table}, {$field}, {$newvalue}, {$after}<br />";
	if($mode == "create")
	{
		$newvalue = implode("\n",$newvalue);
		$field = $table;		// Value for $field may be rubbish!
	}
	elseif (substr($field, 0, 7) == 'UNIQUE ')
	{
		$field = trim(str_replace("UNIQUE KEY ","",$field));
		$mode = ($mode == "drop") ? "indexdrop" : "indexunique";
		$search = array("(",")");
		$newvalue = str_replace($search,'',$newvalue);
	}
	elseif(stristr($field, "KEY ") !== FALSE)
	{
		$field = trim(str_replace("KEY ","",$field));
		$mode = ($mode == "drop") ? "indexdrop" : "index";
		$search = array("(",")");
		$newvalue = str_replace($search,"",$newvalue);
	}


	$text .= "<input type='checkbox'  name=\"fix_active[$field][]\" value='1' /> ".DBLAN_19."\n"; // 'attempt to fix'
	$text .= "<input type='hidden' name=\"fix_newval[$field][]\" value=\"$newvalue\" />\n";
	$text .= "<input type='hidden'  name=\"fix_table[$field][]\" value=\"$table\" />\n";
	$text .= "<input type='hidden'  name=\"fix_mode[$field][]\" value=\"$mode\" />\n";
	$text .= ($after) ? "<input type='hidden'  name=\"fix_after[$field][]\" value=\"$after\" />\n" : "";

	return $text;
}

function table_list() {
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

	//   print_r($search);

	$tables = mysql_list_tables($mySQLdefaultdb);
	while (list($temp) = mysql_fetch_array($tables)){
		$prefix = MPREFIX."lan_";

		if(preg_match("/^".$prefix."(.*)/", $temp, $match)){
			$e107tab = str_replace(MPREFIX, "", $temp);
			$pos = strrpos($match[1],"_")+1;
			$core = substr(str_replace("lan_","",$e107tab),$pos);
			if (str_replace($exclude, "", $e107tab)){
				$tabs[$core] = $e107tab;
			}
		}
	}

	return $tabs;
}




require_once(e_ADMIN."footer.php");
?>
