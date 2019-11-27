<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Database utilities - create arrays from SQL definition
 *
 * $Source: /cvs_backup/e107_0.8/e107_files/utilities/dbgen.php,v $
 * $Revision$
 * $Date$
 * $Author$
*/


require('../../class2.php');
require_once(e_HANDLER.'db_table_admin_class.php');
if (!check_class(e_UC_MAINADMIN))
{
  exit;
}

$dbAdm = new db_table_admin();


// Create an array of all tables
$tableStruct = $dbAdm->get_table_def('',e_CORE.'sql/core_sql.php');
$tableArray = array();

foreach ($tableStruct as $t)
{
	$tableArray[$t[1]] = array('src' => e_CORE.'sql/core_sql.php', 'desc' => 'core:'.$t[1]);
}


// Now do the plugins
$sqlFiles = e107::getPref('e_sql_list');
foreach ($sqlFiles as $p => $f)
{
	$targ = e_PLUGIN.$p.'/'.$f.'.php';
	//echo $p.':'.$targ.'<br />';
	$tableStruct = $dbAdm->get_table_def('',$targ);
	foreach ($tableStruct as $t)
	{
		$tableArray[$t[1]] = array('src' => $targ, 'desc' => 'plug:'.$p.':'.$t[1]);
	}
}
unset($tableStruct);
unset($sqlFiles);


function genFileSelect($tableDefs, $name,$fl)
{
	$ret = "<select name='{$name}' class='tbox'>\n<option value=''>----</option>\n";
	foreach ($tableDefs as $k => $f)
	{
		$ret .= "<option value='{$k}'>{$f['desc']}</option>\n";
	}
	$ret .= "</select>\n";
	return $ret;
}





//$HEADER = '';
//$FOOTER = '';
require(HEADERF);


$message = '';
//========================================================
//						ACTION
//========================================================
if (isset($_POST['do_conversion']))
{
	if (varset($_POST['selected_plugin'],FALSE))
	{
		$table = $_POST['selected_plugin'];
		if (!isset($tableArray[$table]))
		{
			$message = 'Bad table name specified';
			$table = '';
		}
	}
	else
	{
		$message = 'No table name specified';
	}
}

if ($table)
{
	$baseStruct = $dbAdm->get_table_def($table,$tableArray[$table]['src']);
	$fieldDefs = $dbAdm->parse_field_defs($baseStruct[0][2]);					// Required definitions
	$outDefs = array();
	foreach ($fieldDefs as $k => $v)
	{
		switch ($v['type'])
		{
			case 'field' :
				if (vartrue($v['autoinc']))
				{
					if (isset($outDefs['WHERE']))
					{
					}
					else
					{
						$outDefs['WHERE'] = "`{$v['name']}` = \$id";
					}
					break;
				}
				$baseType = preg_replace('#\(\d+?\)#', '', $v['fieldtype']);		// Should strip any length
				switch ($baseType)
				{
					case 'int' :
					case 'shortint' :
					case 'tinyint' :
						$outDefs['_FIELD_TYPES'][$v['name']] = 'int';
						break;
					case 'char' :
					case 'text' :
					case 'varchar' :
						$outDefs['_FIELD_TYPES'][$v['name']] = 'todb';
						break;
				}
				if (isset($v['nulltype']) && !isset($v['default']))
				{
					$outDefs['_NOTNULL'][$v['name']] = '';
				}
				break;
			case 'pkey' :
			case 'ukey' :
			case 'key' :
				break;			// Do nothing with keys for now
			default :
				echo "Unexpected field type: {$k} => {$v['type']}<br />";
		}
	}
	$toSave = $eArrayStorage->WriteArray($outDefs, FALSE);	// 2nd parameter to TRUE if needs to be written to DB
}

//========================================================
//						FORM
//========================================================
$text = 
	"<div style='text-align:center; width:700px'>
	<form method='post' action='".e_SELF."'>
	<table style='width:95%' class='fborder'>
	<colgroup>
	<col style='width:30%' />
	<col style='width:70%' />
	</colgroup>";

$text .= "<tr>
	  <td colspan='2' class='forumheader3' style='text-align:center'>".'Table printout'."<br /><span class='smallblacktext'>".'(ready to copy and paste)'."</span></td>
	</tr>";

if ($message)
{
  $text .= "<tr>
	  <td class='forumheader3'>".'Error:'."</td>
	  <td class='forumheader3'>".$message."
	  </td>
	</tr>";
}


if ($table)
{
$text .= "<tr>
	  <td class='forumheader3'>Table <b>{$table}</b></td>
	  <td class='forumheader3'><pre>".str_replace("\n", '<br />', $toSave)."</pre></td>
	</tr>";
}

$text .= "
	<tr>
	  <td class='forumheader3'>".'Choose table:'."</td>
	  <td class='forumheader3'>".genFileSelect($tableArray, 'selected_plugin',$pluginList)."
	  </td>
	</tr>";



  $text .= "
	<tr>
	  <td class='forumheader3' colspan='2' style='text-align:center'>
		<input class='btn btn-default btn-secondary button' type='submit' name='do_conversion' value='".'Parse Definition'."' />
	  </td>
	</tr>";


$text .= "
	</table>\n
	</form>
	</div><br />";
	$ns->tablerender('SQL Definition Parser', $text);

require(FOOTERF);


?>
