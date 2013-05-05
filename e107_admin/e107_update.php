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
define("e_MINIMAL",true);
require_once ("../class2.php");

// include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'database';

require_once ("auth.php");
require_once ("update_routines.php");



// 

// Carry out CORE updates
/*
function run_updates($dbupdate)
{
	global $mes;

	foreach($dbupdate as $func => $rmks)
	{
		if(function_exists('update_'.$func)) // Legacy Method. 
		{
			$installed = call_user_func("update_".$func);
			//?! (LAN_UPDATE == $_POST[$func])
			if(varsettrue($_POST['update_core'][$func]) && !$installed)
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
}

function run_updates_plugin($func,$check=TRUE) // New for {plugin}_setup.php 
{
	if(class_exists($func.'_setup'))
	{
			$class = $func.'_setup';
			$setObj = new $class;
		
			if(method_exists($setObj,'upgrade_post'))
			{
				return $setObj->upgrade_post($check);	
			}			
			// print_a($setObj);
			// echo "<br />Found: ".$func;			
	}	
}




function show_updates($dbupdate, $what)
{
	global $frm;
	$mes = e107::getMessage();
	
	$caption = constant('LAN_UPDATE_CAPTION_'.strtoupper($what));
	$text = "
	<form method='post' action='".e_SELF."'>
		<fieldset id='core-e107-update-{$what}'>
		<legend>{$caption}</legend>
			<table class='table adminlist'>
				<colgroup>
					<col style='width: 60%' />
					<col style='width: 40%' />
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
	
	// asort($dbupdate);

	foreach($dbupdate as $func => $rmks)
	{
		if(function_exists("update_".$func))
		{
			$text .= "<tr><td>{$rmks}</td>";

			if(call_user_func("update_".$func))
			{
				$text .= "<td>".LAN_UPDATE_3."</td>";
			}
			else
			{
				$updates ++;
				$text .= "<td>".$frm->admin_button('update_core['.$func.']', LAN_UPDATE, 'warning', '', "id=e-{$func}")."</td>";
			}
			$text .= "</tr>\n";
		}
		
		if(class_exists($func.'_setup')) // plugin_setup.php
		{
			$text .= "<tr><td>{$rmks}</td>";
			
			$reason = run_updates_plugin($func,TRUE); // TRUE = Just check if needed. 		
			if(!$reason)
			{
				$text .= "<td>".LAN_UPDATE_3."</td>";
			}
			else
			{
				$updates ++;
				$mes->addDebug($reason);
				$text .= "<td>".$frm->admin_button('update['.$func.']', LAN_UPDATE, 'warning')."</td>";
			}
			$text .= "</tr>\n";	
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
*/



new e107Update($dbupdate);


require_once ("footer.php");



/*

if(varset($_POST['update_core']) && is_array($_POST['update_core']))
{
	$message = run_updates($dbupdate);
}

if(varset($_POST['update']) && is_array($_POST['update'])) // Do plugin updates
{ 
	$func = key($_POST['update']);
	run_updates_plugin($func,FALSE);
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
 * 
 * 
 */



?>