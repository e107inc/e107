<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Manage failed login attempts
 *
*/


exit;

// -- No Longer used - see banlist.php


require_once('../class2.php');
if (!getperms('4'))
{
	e107::redirect('admin');
	exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);


/*
'generic' table:
  gen_id 		- unique identifier
  gen_type 		- 'auto_banned' is of interest here
  gen_datestamp	- date/time of ban
  gen_user_id 	- set to zero
  gen_ip 		- IP address of ban
  gen_intdata 	- user ID (where known)
  gen_chardata 	- ban detail as known

*/


class failed_login_admin extends e_admin_dispatcher
{
	protected $modes = array(	
	
		'main'	=> array(
			'controller' 	=> 'generic_ui',
			'path' 			=> null,
			'ui' 			=> 'generic_form_ui',
			'uipath' 		=> null
		),

	);

	protected $adminMenu = array(
		'main/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = ADLAN_146;
}




				
class generic_ui extends e_admin_ui
{
			
		protected $pluginTitle		= ADLAN_146;
		protected $pluginName		= 'failed_login';
		protected $table			= 'generic';
		protected $pid				= 'gen_id';
		protected $perPage 			= 10; 
		protected $listQry			= "SELECT * FROM `#generic` WHERE gen_type='failed_login' ORDER BY gen_datestamp DESC";
			
		protected $fields 		= array (  'checkboxes' =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		  'gen_id' 				=> array ( 'title' => LAN_ID,	 'nolist'=>true,	'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
	//	  'gen_type' 			=> array ( 'title' => LAN_BAN, 	'type' => 'method', 'data' => 'str', 'width' => 'auto', 'batch' => true, 'filter' => true, 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'gen_datestamp' 		=> array ( 'title' => LAN_DATESTAMP, 'type' => 'datestamp', 'data' => 'int', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'gen_chardata' 		=> array ( 'title' => 'Chardata', 'type' => 'method', 'data' => 'str', 'width' => '40%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
	
	//	  'gen_user_id' 		=> array ( 'title' => LAN_BAN, 'type' => 'method', 'batch'=>true, 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'gen_ip' 				=> array ( 'title' => LAN_IP, 'type' => 'ip', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
	//	  'gen_intdata' 		=> array ( 'title' =>  LAN_BAN, 'type' => 'method', 'batch'=>true, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'options'				=> array ( 'title' => LAN_OPTIONS, 'type' => null, 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1', 'readParms'=>'edit=0'  ),
		);		
		
		protected $fieldpref = array('gen_datestamp', 'gen_ip', 'gen_chardata');
		
			
		// optional
		public function init()
		{
			if($_POST['etrigger_batch'] == 'gen_intdata__1' && count($_POST['e-multiselect'])) // Do we need BAN here?
			{
				$dels = implode(',',$_POST['e-multiselect']);	
				//$e107::getDb()->insert('banlist',
			}
		}
	
		public function afterDelete($data)
		{
		//	$sql2->db_Delete('banlist', "banlist_ip='{$banIP}'");
		}
			
}
				


class generic_form_ui extends e_admin_form_ui
{

	
	// Custom Method/Function 
	function gen_intdata($curVal,$mode)
	{
		$frm = e107::getForm();		
		 		
		switch($mode)
		{
			case 'read': // List Page
				return $curVal;
			break;
			
			case 'write': // Edit Page
				return $frm->text('gen_type',$curVal);		
			break;
			
			case 'filter':
			case 'batch':
				return  array(1=>LAN_BAN);
			break;
		}
	}

	
	// Custom Method/Function 
	function gen_chardata($curVal,$mode)
	{
		$frm = e107::getForm();		
		 		
		switch($mode)
		{
			case 'read': // List Page
				return str_replace(":::","<br />",$curVal);
			break;
			
			case 'write': // Edit Page
				return $frm->text('gen_chardata',$curVal);		
			break;
			
			case 'filter':
			case 'batch':
			//	return  $array; 
			break;
		}
	}

}		
		
		
new failed_login_admin();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;





// ---------- OLD STUFF BELOW - For Review --- //






$e_sub_cat = 'failed_login';
//require_once('auth.php');

$frm = e107::getForm();
$mes = e107::getMessage();

$tmp = (e_QUERY) ? explode('.', e_QUERY) : '';
$from = intval(varset($tmp[0], 0));
$amount = intval(varset($tmp[1], 50));





function deleteBan($banID, $banIP = '')
{
	$sql2 = e107::getDb('sql2');
	$banID = intval($banID);
	if ($banIP == '')
	{
		if($sql2->db_Select('generic', 'gen_ip', 'gen_id='.$banID))
		{
			$at = $sql2->db_Fetch();
			$banIP = $at['gen_ip'];
		}
	}
	$sql2->db_Delete('generic', 'gen_id='.$banID);				// Delete from generic table
	if ($banIP == '') return FALSE;
	$sql2->db_Delete('banlist', "banlist_ip='{$banIP}'");		// Delete from main banlist
	// @todo Admin log messages
	return TRUE;
}

/*
 * FIXME - refine messages (strange messages on delete all & reload)
 */
if(isset($_POST['delbanSubmit']))
{
	$message = '';
	$delcount = 0;
	$spacer = '';
	foreach($_POST['fladelete'] as $delete)
	{
		$delcount++;
		$sql->db_Delete("generic", "gen_id='{$delete}' ");
	}
	if ($delcount)
	{
		$mes->addSuccess(FLALAN_3.": ".$delcount);
	}

	$bancount = 0;
	foreach($_POST['flaban'] as $ban)
	{
		if($sql->db_Select("generic", "*", "gen_id={$ban}"))
		{
			$at = $sql->db_Fetch();
			//if (!$e107->add_ban(4, FLALAN_4, $at['gen_ip'], ADMINID))
			if (!e107::getIPHandler()->add_ban(4, FLALAN_4, $at['gen_ip'], ADMINID))
			{  // IP on whitelist (although possibly we shouldn't get to this stage, but check anyway
				$mes->addWarning(str_replace(FLALAN_18,'--IP--',$at['gen_ip']));
			}
			else $bancount++;
			$banlist_ip = $at['gen_ip'];
			//XXX - why inserting it twice?
			//$sql->db_Insert("banlist", "'$banlist_ip', '".ADMINID."', '".FLALAN_4."' ");
			$sql->db_Delete("generic", "gen_id='{$ban}' ");
		}
	}
	$mes->add(FLALAN_5.": ".$bancount, $bancount ? E_MESSAGE_SUCCESS : E_MESSAGE_INFO); // FIXME
}


if(e_QUERY == 'dabl')
{
	$sql->db_Select("generic", 'gen_ip,gen_id',"gen_type='auto_banned' ");
	while ($row = $sql->db_Fetch())
	{
		if (deleteBan($row['gen_id'],$row['gen_ip']))
		{
			$delcount++;
		}
	}
	//XXX - add delcount to the message
	$mes->addSuccess(FLALAN_17);
}


// Now display any outstanding auto-banned IP addresses
if($sql->db_Select('generic', "*", "gen_type='auto_banned' ORDER BY gen_datestamp DESC "))
{
	$abArray = $sql->db_getList();
	$message = FLALAN_15;
	foreach($abArray as $ab)
	{
		$message .= " - ".$ab['gen_ip'];
	}

	$message .= "<div class='right'>(<a href='".e_SELF."?dabl'>".FLALAN_16."</a>)</div>";
	$mes->addInfo($message);

}

$gen = new convert;
$fla_total = $sql->db_Count('generic', '(*)', "WHERE gen_type='failed_login'");
if(!$sql->db_Select('generic', '*', "gen_type='failed_login' ORDER BY gen_datestamp DESC LIMIT {$from},{$amount}"))
{
	$mes->addInfo(FLALAN_2);
}
else
{

	$faArray = $sql->db_getList('ALL', FALSE, FALSE);

	$text = "
		<form method='post' action='".e_SELF."' id='flaform' >
			<fieldset id='core-fla'>
				<legend class='e-hideme'>".ADLAN_146."</legend>
				<table class='table adminlist'>
					<colgroup>
						<col style='width: 20%' />
						<col style='width: 40%' />
						<col style='width: 20%' />
						<col style='width: 10%' />
						<col style='width: 10%' />
					</colgroup>
					<thead>
						<tr>
							<th>".LAN_DATE."</th>
							<th>".FLALAN_7."</th>
							<th>".FLALAN_8."</th>
							<th class='center last'>
								".LAN_DELETE."<br/>
								".$frm->checkbox('check_all_del', 'jstarget:fladelete', false, array('id'=>false,'class'=>'checkbox toggle-all'))."
							</th>
							<th class='center last'>
								".LAN_BAN."<br/>
								".$frm->checkbox_toggle('check-all-ban', 'flaban')."
							</th>
						</tr>
					</thead>
					<tbody>
	";

	foreach($faArray as $fa)
	{
		extract($fa);//FIXME kill extract()
		
		$gen_chardata = str_replace(":::", "<br />", $tp->toHTML($gen_chardata));
		$host = e107::getIPHandler()->get_host_name(getenv($gen_ip));
		$text .= "
						<tr>
							<td>".$gen->convert_date($gen_datestamp, "forum")."</td>
							<td>".$gen_chardata."</td>
							<td>".e107::getIPHandler()->ipDecode($fa['gen_ip'])."<br />{$host}</td>
							<td class='center middle autocheck e-pointer'>
								".$frm->checkbox('fladelete[]', $gen_id)."
							</td>
							<td class='center middle autocheck e-pointer'>
								".$frm->checkbox('flaban[]', $gen_id)."
							</td>
						</tr>
		";
	}

	$text .= "
					</tbody>
				</table>
				<div class='buttons-bar center'>
					".$frm->admin_button('delbanSubmit', FLALAN_10, 'delete', FLALAN_10, 'title=')."
				</div>
			</fieldset>
		</form>
	";

	$parms = $fla_total.",".$amount.",".$from.",".e_SELF.'?'."[FROM].".$amount;
	$nextprev = $tp->parseTemplate("{NEXTPREV={$parms}}");
	if ($nextprev)
		$text .= "<div class='nextprev-bar'>".$nextprev."</div>";


}

$ns->tablerender(ADLAN_146, $mes->render().$text);

require_once("footer.php");
/**
 * Handle page DOM within the page header
 *
 * @return string JS source
 */
function headerjs()
{
	require_once(e_HANDLER.'js_helper.php');
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
		<script type='text/javascript' src='".e_JS."core/admin.js'></script>
	";

	return $ret;
}
