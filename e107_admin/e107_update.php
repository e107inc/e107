<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - e107 System Update
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/e107_update.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/
require_once ("../class2.php");

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'database';

require_once ("auth.php");
require_once ("update_routines.php");

$mes = e107::getMessage();
$frm = e107::getForm();


// FIX ME - Should be a class so it can be called any where.  

// Carry out core updates
function run_updates($dbupdate)
{
	global $mes;
	foreach($dbupdate as $func => $rmks)
	{
		$installed = call_user_func("update_".$func);
		//?! (LAN_UPDATE == $_POST[$func])
		if(varsettrue($_POST[$func]) && !$installed)
		{
			if(function_exists("update_".$func))
			{
				$message = LAN_UPDATE_7." {$rmks}";
				$error = call_user_func("update_".$func, "do");
				if($error != '')
				{
					$mes->add($message, E_MESSAGE_ERROR);
					$mes->add($error, E_MESSAGE_ERROR);
				}
				else $mes->add($message, E_MESSAGE_SUCCESS);
			}
		}
	}
}

function show_updates($dbupdate, $what)
{
	global $frm;
	$caption = constant('LAN_UPDATE_CAPTION_'.strtoupper($what));
	$text = "
	<form method='post' action='".e_SELF."'>
		<fieldset id='core-e107-update-{$what}'>
		<legend>{$caption}</legend>
			<table cellpadding='0' cellspacing='0' class='adminlist'>
				<colgroup span='2'>
					<col style='width: 60%'></col>
					<col style='width: 40%'></col>
				</colgroup>
				<thead>
					<tr>
						<th>".LAN_UPDATE_55."</th>
						<th class='last'>".LAN_UPDATE_2."</th>
					</tr>
				</thead>
				<tbody>
	";

	$updates = 0;

	foreach($dbupdate as $func => $rmks)
	{
		if(function_exists("update_".$func))
		{
			$text .= "
					<tr>
						<td>{$rmks}</td>
			";
			//	  echo "Core2 Check {$func}=>{$rmks}<br />";
			if(call_user_func("update_".$func))
			{
				$text .= "
						<td>".LAN_UPDATE_3."</td>
				";
			}
			else
			{
				$updates ++;
				$text .= "
						<td>".$frm->admin_button($func, LAN_UPDATE, 'update', '', "id=e-{$func}")."</td>
				";
			}
			$text .= "
					</tr>
			";
		}
	}

	$text .= "
				</tbody>
			</table>
		</fieldset>
	</form>
		";

	echo $text;
	return $updates; // Number of updates to do
}

if($_POST)
{
	$message = run_updates($dbupdate);
}

if($_POST)
{ // Do plugin updates
	$message = run_updates($dbupdatep);
}

$total_updates = 0;

ob_start();
	if(isset($dbupdatep))
	{ // Show plugin updates done
		$total_updates += show_updates($dbupdatep, 'plugin');
	}
	// Show core updates done
	$total_updates += show_updates($dbupdate, 'core');
	$text = ob_get_contents();
ob_end_clean();

$e107->ns->tablerender(LAN_UPDATE_56, $mes->render().$text);

if($total_updates == 0)
{ // No updates needed - clear the cache to be sure
	$e107cache->set_sys("nq_admin_updatecheck", time().', 1, '.$e107info['e107_version'], TRUE);
}

require_once ("footer.php");
?>