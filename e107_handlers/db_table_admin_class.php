<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Database utilities
 *
*/

/*
 Database utilities for admin tasks:
 Get structure of a table from a database
 Get structure of a table from a file
 First level parse of table structure
 Parse of field definitions part of table structure
 Comparison of two structures, including generation of MySQL to make them the same
 Some crude printing utilities
 Note: there are some uncommented 'echo' statements which are intentional to highlight that something's gone wrong! (not that it should, of course)
 */

 
// DEPRECATED - USE db_verify_class where possible. 

class db_table_admin
{
	var $file_buffer = ''; // Contents of a file
	var $last_file = '';
	
	// Get list of fields and keys for a table - return FALSE if unsuccessful
	// Return as for get_table_def
	function get_current_table($table_name, $prefix = "")
	{
		$sql = e107::getDb();
		if(!isset($sql))
		{
			$sql = new db;
		}
		
		if (!$prefix)
		{
			$prefix = MPREFIX;
		}
		//	echo "Get table structure for: {$table_name}, prefix: {$prefix}<br />";
		$sql->gen('SET SQL_QUOTE_SHOW_CREATE = 1');
		$qry = 'SHOW CREATE TABLE `'.$prefix.$table_name."`";
		if (!($z = $sql->gen($qry)))
		{
			return FALSE;
		}
		$row = $sql->db_Fetch('num');
		$tmp = str_replace("`", "", stripslashes($row[1])).';'; // Add semicolon to work with our parser
		$count = preg_match_all("#CREATE\s+?TABLE\s+?`{0,1}({$prefix}{$table_name})`{0,1}\s+?\((.*?)\)\s+?(?:TYPE|ENGINE)\s*\=\s*(.*?);#is", $tmp, $matches, PREG_SET_ORDER);
		if ($count === FALSE)
		{
			return "Error occurred";
		}
		if (!$count)
		{
			return "No matches";
		}
		return $matches;
	}
	
	/**
	 * Routine to do first-level parse of table structure
	 *---------------------------------------------------
	 * Given the name of a file, returns an array, with each element being a table creation definition.
	 * Tracks the last file read - only reads it once
	 * If the file name is an empty string, uses a previously read/set buffer
	 *
	 * @param string $table_name  - If specified, returns only that table's info; otherwise returns a list of all tables
	 * 		The table name must include a prefix where appropriate (although not required with standard E107 table definition files)
	 * @return  string|array
	 *			- if error, returns a brief text message
	 *			- if successful, returns an array of table definitions, each of which is itself an array:
	 *				[0] - The complete string which creates a table (unless a prefix needs adding to the table name), including terminating ';'
	 *				[1] - The table name. Any backticks are stripped
	 *				[2] - Field definitions, with the surrounding (...) stripped
	 *				[3] - The 'TYPE' field ('TYPE=' is stripped) and any AUTO-INCREMENT definition or other text.
	 */
	function get_table_def($table_name = '', $file_name = "")
	{
		if ($file_name != '')
		{ // Read in and buffer a new file (if we've not already got one)
			if ($this->last_file != $file_name)
			{
				if (!is_readable($file_name))
				{
					return "No file";
				}
				$temp = file_get_contents($file_name);
				// Strip any php header
				$temp = preg_replace("#\<\?php.*?\?\>#mis", '', $temp);
				// Strip any comments  (only /*...*/ supported
				$this->file_buffer = preg_replace("#\/\*.*?\*\/#mis", '', $temp);
				$this->last_file = $file_name;
			}
		}
		if (!$table_name)
		{
			$table_name = '\w+?';
		}
		// Regex should be identical to that in get_current_table (apart from the source text variable name)
		$count = preg_match_all("#CREATE\s+?TABLE\s+?`{0,1}({$table_name})`{0,1}\s+?\((.*?)\)\s+?(?:TYPE|ENGINE)\s*\=\s*(.*?);#is", $this->file_buffer, $matches, PREG_SET_ORDER);
		if ($count === false)
		{
			return "Error occurred";
		}
		if (!$count)
		{
			return "No matches";
		}
		return $matches;
	}
	
	// Parses the block of lines which make up the field and index definitions
	// Returns an array where each entry is the definitions of a field or index
	function parse_field_defs($text)
	{
		$ans = array(
		);
		$text = str_replace("\r", "\n", $text);
		$field_lines = explode("\n", $text);
		foreach ($field_lines as $fv)
		{
			unset($defs);
			$fv = trim(str_replace('  ', ' ', $fv));
			$fv = str_replace('`', '', $fv);
			if (substr($fv, -1) == ',')
			{
				$fv = trim(substr($fv, 0, -1));
			}
			//	  echo "Line: ".$fv."<br />";
			if ($fv)
			{
				$fd = explode(' ', $fv);
				switch (strtoupper($fd[0]))
				{
					case 'PRIMARY':
						if (strtoupper($fd[1]) == 'KEY')
							$defs['type'] = 'pkey';
						$defs['name'] = $fd[2];
					break;
					
					case 'UNIQUE':
						if (count($fd) < 3)
						{
							echo "Truncated definition after UNIQUE {$i}: ".$fd[1]."<br />";
						}
						elseif (strtoupper($fd[1]) == 'KEY')
						{
							$defs['type'] = 'ukey';
							$defs['name'] = $fd[2];
							if (isset($fd[3])) $defs['keyfield'] = $fd[3];
							else $defs['keyfield'] = '['.$fd[2].']';
						}
						else
						{
							echo "Unrecognised word after UNIQUE in definition {$i}: ".$fd[1]."<br />";
						}
					break;
					
					case 'FULLTEXT':
						if (count($fd) < 3)
						{
							echo "Truncated definition after FULLTEXT {$i}: ".$fd[1]."<br />";
						}
						elseif (strtoupper($fd[1]) == 'KEY')
						{
							$defs['type'] = 'ftkey';
							$defs['name'] = $fd[2];
							if (isset($fd[3])) $defs['keyfield'] = $fd[3];
							else $defs['keyfield'] = '['.$fd[2].']';
						}
						else
						{
							echo "Unrecognised word after FULLTEXT in definition {$i}: ".$fd[1]."<br />";
						}
					break;
					
					case 'KEY':
						$defs['type'] = 'key';
						$defs['name'] = $fd[1];
						if (isset($fd[2]))
						{
							$defs['keyfield'] = $fd[2];
						}
						else
						{
							$defs['keyfield'] = '['.$fd[1].']';
						}
					break;
					default: // Must be a DB field name
						$defs['type'] = 'field';
						$defs['name'] = $fd[0];
						$defs['fieldtype'] = $fd[1];
						$i = 2; // First unused field
						if ((strpos($fd[1], 'int') === 0) || (strpos($fd[1], 'tinyint') === 0) || (strpos($fd[1], 'smallint') === 0) || (strpos($fd[1], 'bigint') === 0))
						{
							if (isset($fd[2]) && (strtoupper($fd[2]) == 'UNSIGNED'))
							{
								$defs['vartype'] = $fd[2];
								$i++;
							}
						}
						while ($i < count($fd))
						{
							switch (strtoupper($fd[$i]))
							{
								case 'NOT':
									if (isset($fd[$i + 1]) && strtoupper($fd[$i + 1]) == 'NULL')
									{
										$i++;
										$defs['nulltype'] = 'NOT NULL';
									}
									else
									{ // Syntax error
										echo "Unrecognised word in definition {$i} after 'NOT': ".$fd[$i + 1]."<br />";
									}
								break;
								case 'DEFAULT':
									if (isset($fd[$i + 1]))
									{
										$i++;
										$defs['default'] = $fd[$i];
									}
								break;
								case 'COLLATE':
									$i++; // Just skip over - we ignore collation
								break;
								case 'AUTO_INCREMENT':
									$defs['autoinc'] = TRUE;
								break;
								default:
									if(E107_DBG_SQLDETAILS)
									{
										$mes = e107::getMessage();
										$mes->add("db_table_admin_class.php :: parse_field_defs() Line: 230 - Unknown definition {$i}: ".$fd[$i], E_MESSAGE_DEBUG);
									}
								}
								$i++;
							}
					}
					if (count($defs) > 1)
					{
						$ans[] = $defs;
					}
					else
					{
						echo "Partial definition<br />";
					}
				}
			}
			if (!count($ans))
			{
				return FALSE;
			}
			return $ans;
		}
		
		// Utility routine - given our array-based definition, create a string MySQL field definition
		function make_def($list)
		{
			switch ($list['type'])
			{
				case 'key':
					return 'KEY '.$list['name'].' ('.str_replace(array( '(', ')' ), '', $list['keyfield']).')';
				case 'ukey':
					return 'UNIQUE KEY '.$list['name'].' ('.str_replace(array( '(', ')' ), '', $list['keyfield']).')';
				case 'ftkey':
					return 'FULLTEXT KEY '.$list['name'].' ('.str_replace(array( '(', ')' ), '', $list['keyfield']).')';
				case 'pkey':
					return 'PRIMARY KEY ('.$list['name'].')';
				case 'field': // Require a field - got a key. so add a field at the end
					$def = $list['name'];
					if (isset($list['fieldtype']))
					{
						$def .= ' '.$list['fieldtype'];
					}
					if (isset($list['vartype']))
					{
						$def .= ' '.$list['vartype'];
					}
					if (isset($list['nulltype']))
					{
						$def .= ' '.$list['nulltype'];
					}
					if (isset($list['default']))
					{
						$def .= ' default '.$list['default'];
					}
					if (vartrue($list['autoinc']))
					{
						$def .= ' auto_increment';
					}
					return $def;
			}
			return "Cannot generate definition for: ".$list['type'].' '.$list['name'];
		}
		
		// Compare two field/index lists as generated by parse_field_defs
		// If $stop_on_error is TRUE, returns TRUE if the same, false if different
		// Return a text list of differences, plus an array of MySQL queries to fix
		// List1 is the reference, List 2 is the actual
		// This version looks ahead on a failed match, and moves a field up in the table if already defined - should retain as much as possible
		function compare_field_lists($list1, $list2, $stop_on_error = FALSE)
		{
			$i = 0; // Counts records in list1 (required format)
			$j = 0; // Counts records in $created_list (our 'table so far' list)
			$error_list = array(
			); // Free text list of differences
			$change_list = array(
			); // MySQL statements to implement changes
			$created_list = array(
			); // List of field defs that we build up (just names)
			while ($i < count($list1))
			{
				if (count($list2) == 0)
				{ // Missing field at end
					if ($stop_on_error)
					{
						return FALSE;
					}
					$error_list[] = 'Missing field at end: '.$list1[$i]['name'];
					$change_list[] = 'ADD '.$this->make_def($list1[$i]);
					$created_list[$j] = $list1[$i]['name'];
					$j++;
				}
				elseif ($list1[$i]['type'] == $list2[0]['type'])
				{ // Worth doing a compare - fields are same type
					//		echo $i.': compare - '.$list1[$i]['name'].', '.$list2[0]['name'].'<br />';
					if (strcasecmp($list1[$i]['name'], $list2[0]['name']) != 0)
					{ // Names differ, so need to add or subtract a field.
						//		  echo $i.': names differ - '.$list1[$i]['name'].', '.$list2[0]['name'].'<br />';
						if ($stop_on_error)
						{
							return FALSE;
						}
						$found = FALSE;
						for ($k = $i + 1; $k < count($list1); $k++)
						{
							//		    echo "Compare ".$list1[$k]['name'].' with '.$list2[0]['name'];
							if (strcasecmp($list1[$k]['name'], $list2[0]['name']) == 0)
							{ // Field in list2 found later in list1; do nothing
								//			  echo " - match<br />";
								$found = TRUE;
							break;
							}
							//			echo " - no match<br />";
						}
						
						if (!$found)
						{ // Field in existing DB no longer required
							$error_list[] = 'Obsolete field: '.$list2[0]['name'];
							$change_list[] = 'DROP '.($list2[0]['type'] == 'field' ? '' : 'INDEX ').$list2[0]['name'];
							array_shift($list2);
							continue;
						}
						
						$found = FALSE;
						for ($k = 0; $k < count($list2); $k++)
						{
							//		    echo "Compare ".$list1[$i]['name'].' with '.$list2[$k]['name'];
							if (strcasecmp($list1[$i]['name'], $list2[$k]['name']) == 0)
							{ // Field found; we need to move it up
								//			  echo " - match<br />";
								$found = TRUE;
							break;
							}
							//			echo " - no match<br />";
						}
						if ($found)
						{
							$error_list[] = 'Field out of position: '.$list2[$k]['name'];
							$change_list[] = 'MODIFY '.$this->make_def($list1[$i]).(count($created_list) ? ' AFTER '.$created_list[count($created_list) - 1] : ' FIRST');
							array_splice($list2, $k, 1); // Finished with this element - delete it, and renumber the keys
							$created_list[$j] = $list1[$i]['name'];
							$j++;
							// The above also amends any parameters as necessary
						}
						else
						{ // Need to insert a field
							$error_list[] = 'Missing field: '.$list1[$i]['name'].' (found: '.$list2[0]['type'].' '.$list2[0]['name'].')';
							switch ($list1[$i]['type'])
							{
								case 'key':
								case 'ukey':
								case 'ftkey':
								case 'pkey': // Require a key
									$change_list[] = 'ADD '.$this->make_def($list1[$i]);
									$error_list[] = 'Missing index: '.$list1[$i]['name'];
									$created_list[$j] = $list1[$i]['name'];
									$j++;
								break;
								
								case 'field':
									$change_list[] = 'ADD '.$this->make_def($list1[$i]).(count($created_list) ? ' AFTER '.$created_list[count($created_list) - 1] : ' FIRST');
									$error_list[] = 'Missing field: '.$list1[$i]['name'].' (found: '.$list2[0]['type'].' '.$list2[0]['name'].')';
									$created_list[$j] = $list1[$i]['name'];
									$j++;
								break;
							}
						}
					}
					else
					{ // Field/index is present as required; may be changes though
						// Any difference and we need to update the table
						//		  echo $i.': name match - '.$list1[$i]['name'].'<br />';
						foreach ($list1[$i] as $fi=>$v)
						{
							$t = $list2[0][$fi];
							if (stripos($v, 'varchar') !== FALSE)
							{
								$v = substr($v, 3);
							} // Treat char, varchar the same
							if (stripos($t, 'varchar') !== FALSE)
							{
								$t = substr($t, 3);
							} // Treat char, varchar the same
							if (strcasecmp($t, $v) !== 0)
							{
								if ($stop_on_error)
								{
									return FALSE;
								}
								$error_list[] = 'Incorrect definition: '.$fi.' = '.$v;
								$change_list[] = 'MODIFY '.$this->make_def($list1[$i]);
							break;
							}
						}
						array_shift($list2);
						$created_list[$j] = $list1[$i]['name'];
						$j++;
					}
				}
				else
				{ // Field type has changed. We know fields come before indexes. So something's missing
					//		echo $i.': types differ - '.$list1[$i]['type'].' '.$list1[$i]['name'].', '.$list2[$k]['type'].' '.$list2[$k]['name'].'<br />';
					if ($stop_on_error)
					{
						return FALSE;
					}
					switch ($list1[$i]['type'])
					{
						case 'key':
						case 'ukey':
						case 'ftkey':
						case 'pkey': // Require a key - got a field, or a key of a different type
							while ((count($list2) > 0) && ($list2[0]['type'] == 'field'))
							{
								$error_list[] = 'Extra field: '.$list2[0]['name'];
								$change_list[] = 'DROP '.$list2[0]['name'];
								array_shift($list2);
							}
							if ((count($list2) == 0) || ($list1[$i]['type'] != $list2[0]['type']))
							{ // need to add a key
								$change_list[] = 'ADD '.$this->make_def($list1[$i]);
								$error_list[] = 'Missing index: '.$list1[$i]['name'];
								$created_list[$j] = $list1[$i]['name'];
								$j++;
							}
						break;
						
						case 'field': // Require a field - got a key. so add a field at the end
							$error_list[] = 'Missing field: '.$list1[$i]['name'].' (found: '.$list2[0]['type'].' '.$list2[0]['name'].')';
							$change_list[] = 'ADD '.$this->make_def($list1[$i]);
						break;
						
						default:
							$error_list[] = 'Unknown field type: '.$list1[$i]['type'];
							$change_list[] = ''; // Null entry to keep them in step
					}
				} // End - missing or extra field
				
				$i++; // On to next field
			}
			if (count($list2))
			{ // Surplus fields in actual table
				//	  Echo count($list2)." fields at end to delete<br />";
				foreach ($list2 as $f)
				{
					switch ($f['type'])
					{
						case 'key':
						case 'ukey':
						case 'ftkey':
						case 'pkey': // Require a key - got a field
							$error_list[] = 'Extra index: '.$list2[0]['name'];
							$change_list[] = 'DROP INDEX '.$list2[0]['name'];
						break;
						case 'field':
							$error_list[] = 'Extra field: '.$list2[0]['name'];
							$change_list[] = 'DROP '.$list2[0]['name'];
						break;
					}
				}
			}
			if ($stop_on_error)
				return TRUE; // If doing a simple comparison and we get to here, all matches
			return array(
				$error_list, $change_list
			);
		}
		
		function make_changes_list($result)
		{
			if (!is_array($result))
			{
				return "Not an array<br />";
			}
			$text = "<table>";
			for ($i = 0; $i < count($result[0]); $i++)
			{
				$text .= "<tr><td>{$result[0][$i]}</td>";
				$text .= "<td>{$result[1][$i]}</td>";
				$text .= "</tr>\n";
			}
			$text .= "</table><br /><br />";
			return $text;
		}
		
		// Return a table of info from the output of get_table_def
		function make_table_list($result)
		{
			if (!is_array($result))
			{
				return "Not an array<br />";
			}
			$text = "<table>";
			for ($i = 0; $i < count($result); $i++)
			{
				$text .= "<tr><td>{$result[$i][0]}</td>";
				$text .= "<td>{$result[$i][1]}</td>";
				$text .= "<td>{$result[$i][2]}</td>";
				$text .= "<td>{$result[$i][3]}</td></tr>\n";
			}
			$text .= "</table><br /><br />";
			return $text;
		}
		
		// Return a table of info from the output of parse_field_defs()
		function make_field_list($fields)
		{
			$text = "<table>";
			foreach ($fields as $f)
			{
				switch ($f['type'])
				{
					case 'pkey':
						$text .= "<tr><td>PRIMARY KEY</td><td>{$f['name']}</td><td>&nbsp;</td></tr>";
					break;
					case 'ukey':
						$text .= "<tr><td>UNIQUE KEY</td><td>{$f['name']}</td><td>{$f['keyfield']}</td></tr>";
					break;
					case 'ftkey':
						$text .= "<tr><td>FULLTEXT KEY</td><td>{$f['name']}</td><td>{$f['keyfield']}</td></tr>";
					break;
					case 'key':
						$text .= "<tr><td>KEY</td><td>{$f['name']}</td><td>{$f['keyfield']}</td></tr>";
					break;
					case 'field':
						$text .= "<tr><td>FIELD</td><td>{$f['name']}</td><td>{$f['fieldtype']}";
						if (isset($f['vartype']))
						{
							$text .= " ".$f['vartype'];
						}
						$text .= "</td>";
						if (isset($f['nulltype']))
						{
							$text .= "<td>{$f['nulltype']}</td>";
						}
						else
						{
							$text .= "<td>&nbsp;</td>";
						}
						if (isset($f['default']))
						{
							$text .= "<td>default {$f['default']}</td>";
						}
						elseif (isset($f['autoinc']))
						{
							$text .= "<td>AUTO_INCREMENT</td>";
						}
						else
						{
							$text .= "<td>&nbsp;</td>";
						}
						$text .= "</tr>";
					break;
					default:
						$text .= "<tr><td>!!Unknown!!</td><td>{$f['type']}</td><td>&nbsp;</td></tr>";
				}
			}
			$text .= "</table><br /><br />--Ends--<br />";
			return $text;
		}
		
		//--------------------------------------------------
		//		Update a table to required structure
		//--------------------------------------------------
		
		// $newStructure is an array element as returned from get_table_def()
		// If $mlUpdate is TRUE, applies same query to all tables of same language
		// Return TRUE on success.
		// Return text string if $justCheck is TRUE and changes needed
		// Return text string on most failures
		// Return FALSE on certain failures (generally indicative of code/system problems)
		function update_table_structure($newStructure, $justCheck = FALSE, $makeNewifNotExist = TRUE, $mlUpdate = FALSE)
		{
			global $sql;
			// Pull out table name
			$debugLevel = E107_DBG_SQLDETAILS;
	
			$tableName = $newStructure[1];
			if (!$sql->isTable($tableName))
			{
				if ($makeNewifNotExist === FALSE)
				{
					return 'Table doesn\'t exist';
				}
				if ($sql->gen($newStructure[0]))
				{
					return TRUE;
				}
				return 'Error creating new table: '.$tableName;
			}
			$reqFields = $this->parse_field_defs($newStructure[2]); // Required field definitions
			if ($debugLevel)
			{
				echo "Required table structure: <br />".$this->make_field_list($reqFields);
			}
			
			if ((($actualDefs = $this->get_current_table($tableName)) === FALSE) || !is_array($actualDefs)) // Get actual table definition (Adds current default prefix)
			{
				return "Couldn't get table structure: {$tableName}<br />";
			}
			else
			{
				//		echo $db_parser->make_table_list($actual_defs);
				$actualFields = $this->parse_field_defs($actualDefs[0][2]); // Split into field definitions
				if ($debugLevel)
				{
					echo 'Actual table structure: <br />'.$this->make_field_list($actualFields);
				}
				
				$diffs = $this->compare_field_lists($reqFields, $actualFields); // Work out any differences
				if (count($diffs[0]))
				{ // Changes needed
					if ($justCheck)
					{
						return 'Field changes rqd; table: '.$tableName.'<br />';
					}
					// Do the changes here
					if ($debugLevel)
					{
						echo "List of changes found:<br />".$this->make_changes_list($diffs);
					}
					$qry = 'ALTER TABLE '.MPREFIX.$tableName.' '.implode(', ', $diffs[1]);
					if ($debugLevel)
					{
						echo 'Update Query used: '.$qry.'<br />';
					}
					if ($mlUpdate)
					{
						$ret = $sql->db_Query_all($qry); // Returns TRUE = success, FALSE = fail
					}
					else
					{
						$ret = $sql->gen($qry);
					}
					if ($ret === FALSE)
					{
						return $sql->dbError();
					}
				}
				return TRUE; // Success even if no changes required
			}
			return FALSE;
		}
		
		function createTable($pathToSqlFile = '', $tableName = '', $addPrefix = true, $renameTable = '')
		{
			$e107 = e107::getInstance();
			$tmp = $this->get_table_def($tableName, $pathToSqlFile);
			$createText = $tmp[0][0];
			$newTableName = ($renameTable ? $renameTable : $tableName);
			if ($addPrefix)
			{
				$newTableName = MPREFIX.$newTableName;
			}
			if ($newTableName != $tableName)
			{
				$createText = preg_replace('#create +table +(\w*?) +#i', 'CREATE TABLE '.$newTableName.' ', $createText);
			}
			return e107::getDb()->gen($createText);
		}
		
	}
	



