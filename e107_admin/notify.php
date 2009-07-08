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
|     $Source: /cvs_backup/e107_0.8/e107_admin/notify.php,v $
|     $Revision: 1.6 $
|     $Date: 2009-07-08 06:58:00 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once('../class2.php');
if (!getperms('O')) 
{
	header('location:'.e_BASE.'index.php');
	exit;
}

$e_sub_cat = 'notify';

require_once('auth.php');

require_once(e_HANDLER.'userclass_class.php');
require_once(e_HANDLER.'form_handler.php');
$rs = new form;
$nc = new notify_config;
$uc = new user_class;

$uc->fixed_classes['email'] = 'Email Address =>';
$uc->text_class_link['email'] = 'email';

if (isset($_POST['update']))
{
	$message = ($nc -> update()) ? LAN_UPDATED : LAN_UPDATED_FAILED;
	$ns -> tablerender($message,"<div style='text-align:center'>".$message."</div>");
}
$nc -> config();


class notify_config
{
	var $notify_prefs;
	var $changeList = array();

	function notify_config() 
	{
		global $sysprefs, $eArrayStorage, $tp, $sql,$pref;
		$this -> notify_prefs = $sysprefs -> get('notify_prefs');
		$this -> notify_prefs = $eArrayStorage -> ReadArray($this -> notify_prefs);

		// load every e_notify.php file.
        foreach($pref['e_notify_list'] as $val)
		{
				if (!isset($this -> notify_prefs['plugins'][$val]))
				{
					$this -> notify_prefs['plugins'][$val] = TRUE;
					if (is_readable(e_PLUGIN.$val."/e_notify.php"))
					{
						require_once(e_PLUGIN.$val.'/e_notify.php');
						foreach ($config_events as $event_id => $event_text)
				   		{
							$this -> notify_prefs['event'][$event_id] = array('class' => '255', 'email' => '');
						}
						$recalibrate = true;
					}
				}
		}


		if ($recalibrate) 
		{
			$s_prefs = $tp -> toDB($this -> notify_prefs);
			$s_prefs = $eArrayStorage -> WriteArray($s_prefs);
			$sql -> db_Update("core", "e107_value='".$s_prefs."' WHERE e107_name='notify_prefs'");
		}
	}

	function config() 
	{
		global $ns, $rs;

		$text = "<div style='text-align: center'>
		<form action='".e_SELF."?results' method='post' id='scanform'>
		<table class='adminlist'>
		<thead>
		<tr>
		<th colspan='2'>".NT_LAN_2.":</th>
		</tr>";

		$text .= "</thead><tbody><tr>
		<td colspan='2'>".NU_LAN_1."</td>
		</tr>";

		$text .= $this -> render_event('usersup', NU_LAN_2);
		$text .= $this -> render_event('userveri', NU_LAN_3);
		$text .= $this -> render_event('login', NU_LAN_4);
		$text .= $this -> render_event('logout', NU_LAN_5);

		$text .= "<tr>
		<td colspan='2'>".NS_LAN_1."</td>
		</tr>";

		$text .= $this -> render_event('flood', NS_LAN_2);


		$text .= "<tr>
		<td colspan='2'>".NN_LAN_1."</td>
		</tr>";

		$text .= $this -> render_event('subnews', NN_LAN_2);
		$text .= $this -> render_event('newspost', NN_LAN_3);
		$text .= $this -> render_event('newsupd', NN_LAN_4);
		$text .= $this -> render_event('newsdel', NN_LAN_5);

		$text .= "<tr>
		<td colspan='2'>".NF_LAN_1."</td>
		</tr>";

		$text .= $this -> render_event('fileupload', NF_LAN_2);

		foreach ($this -> notify_prefs['plugins'] as $plugin_id => $plugin_settings)
		{
            if(is_readable(e_PLUGIN.$plugin_id.'/e_notify.php'))
			{
				require(e_PLUGIN.$plugin_id.'/e_notify.php');
				$text .= "<tr>
				<td colspan='2'>".$config_category."</td>
				</tr>";
				foreach ($config_events as $event_id => $event_text)
				{
					$text .= $this -> render_event($event_id, $event_text);
				}
			}
		}

		$text .= "<tr>
		<td colspan='2' class='center button-bar'>".$rs -> form_button('submit', 'update', LAN_UPDATE)."</td>
		</tr>
		</tbody>
		</table>
		</form>
		</div>";

		$ns -> tablerender(NT_LAN_1, $text);
	}


	function render_event($id, $description) 
	{
		global $rs, $tp, $uc;
		$text .= "
			<tr>
				<td style='width: 40%'>".$description.":	</td>
				<td style='width: 60%; white-space: nowrap'>
				".$uc->uc_dropdown('event['.$id.'][class]', $this -> notify_prefs['event'][$id]['class'],"nobody,main,admin,member,classes,email","onchange=\"mail_field(this.value,'event_".$id."');\" ");

			if($this -> notify_prefs['event'][$id]['class'] == 'email')
			{
            	$disp='display:visible';
				$value = $tp -> toForm($this -> notify_prefs['event'][$id]['email']);
			}
			else
			{
            	$disp = "display:none";
				$value= "";
			}

			$text .= "<input type='text' style='width:180px;$disp' class='tbox' id='event_".$id."' name='event[".$id."][email]' value=\"".$value."\" />\n";

		$text .= "</td>
		</tr>";
		return $text;
	}


	function update() 
	{
		global $sql, $pref, $tp, $eArrayStorage, $admin_log;
		$this->changeList = array();
		foreach ($_POST['event'] as $key => $value)
		{
			if ($this -> update_event($key))
			{
				$active = TRUE;
			}
		}
        if ($active)
		{
		   	$pref['notify'] = TRUE;
		}
		else
		{
		 	$pref['notify'] = FALSE;
		}
	  	save_prefs();
		$s_prefs = $tp -> toDB($this -> notify_prefs);
		$s_prefs = $eArrayStorage -> WriteArray($s_prefs);
		if($sql -> db_Update("core", "e107_value='".$s_prefs."' WHERE e107_name='notify_prefs'")!==FALSE)
		{
			$admin_log->logArrayAll('NOTIFY_01',$this->changeList);
			return TRUE;
		}
		else
		{
        	return FALSE;
		}

	}

	function update_event($id) 
	{
		$changed = FALSE;
		
		if ($this -> notify_prefs['event'][$id]['class'] != $_POST['event'][$id]['class'])
		{
			$this -> notify_prefs['event'][$id]['class'] = $_POST['event'][$id]['class'];
			$changed = TRUE;
		}
		if ($this -> notify_prefs['event'][$id]['email'] != $_POST['event'][$id]['email'])
		{
			$this -> notify_prefs['event'][$id]['email'] = $_POST['event'][$id]['email'];
			$changed = TRUE;
		}
		if ($changed)
		{
			$this->changeList[$id] = $this->notify_prefs['event'][$id]['class'].', '.$this->notify_prefs['event'][$id]['email'];
		}
		if ($this -> notify_prefs['event'][$id]['class'] != 255) 
		{
			return TRUE;
		} 
		else 
		{
			return FALSE;
		}
	}
}

require_once('footer.php');
function headerjs()
{

	$js = "
	<script type='text/javascript'>

    function mail_field(val,id)
	{
    	if(val == 'email')
		{
        	document.getElementById(id).style.display ='';
		}
        else
		{
        	document.getElementById(id).style.display ='none';
		}
	}

	</script>";

	return $js;
}
?>
