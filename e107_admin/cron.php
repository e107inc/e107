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
|     $Source: /cvs_backup/e107_0.8/e107_admin/cron.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-06-17 05:39:22 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once('../class2.php');
if (!getperms('U'))
{
  header('location:'.e_BASE.'index.php');
  exit;
}
$e_sub_cat = 'cron';

require_once('auth.php');

if(isset($_POST['submit']))
{
    foreach($_POST['cron'] as $key=>$val)
	{
    	if(!$val['active'])
		{
         	$val['active'] = 0;
		}

		$t['minute'] 	= implode(",",$_POST['tab'][$key]['minute']);
		$t['hour'] 		= implode(",",$_POST['tab'][$key]['hour']);
		$t['day']		= implode(",",$_POST['tab'][$key]['day']);
        $t['month']		= implode(",",$_POST['tab'][$key]['month']);
		$t['weekday']	= implode(",",$_POST['tab'][$key]['weekday']);

		$val['tab'] = implode(" ",$t);
		$tabs .= $val['tab']."<br />";
    	$cron[$key] = $val;
	}

	$pref['e_cron_pref'] = $cron;
	save_prefs();
	$ns -> tablerender(LAN_SAVED,"<div style='text-align:center'>".LAN_SETSAVED."</div>");
}


$cronpref = $pref['e_cron_pref'];

// ----------- Grab All e_cron parameters -----------------------------------

$count = 0;
foreach($pref['e_cron_list'] as $key=>$val)
{
	$eplug_cron = array();
	if(is_readable(e_PLUGIN.$key."/e_cron.php"))
	{
		require_once(e_PLUGIN.$key."/e_cron.php");
		foreach($eplug_cron as $v)
		{
			$e_cron[$count]['name'] 		= $v['name'];
			$e_cron[$count]['function'] 	= $v['function'];
			$e_cron[$count]['description'] 	= $v['description'];
			$e_cron[$count]['path'] 		= $key;
			$count++;
		}
	}

}

// ----------------------  List All Functions -----------------------------

$text = "<div style='text-align:center'>
   <form method='post' action='".e_SELF."' id='linkform'>
   <table style='".ADMIN_WIDTH."' class='fborder'>
   <tr>
   <td class='fcaption'>".LAN_CRON_1."</td>
   <td class='fcaption'>".LAN_CRON_2."</td>
   <td class='fcaption'>".LAN_CRON_3."</td>
   <td class='fcaption'>".LAN_CRON_4."</td>
   <td class='fcaption'>".LAN_CRON_5."</td>
   <td class='fcaption'>".LAN_CRON_6."</td>
   <td class='fcaption'>".LAN_CRON_7."</td>
   <td class='fcaption'>".LAN_CRON_8."</td>
   </tr>";


foreach($e_cron as $cron)
{
    $c = $cron['function'];
    $sep = array();

	list($sep['minute'],$sep['hour'],$sep['day'],$sep['month'],$sep['weekday']) = explode(" ",$cronpref[$c]['tab']);

    foreach($sep as $key=>$value)
	{
    	if($value=="")
		{
        	$sep[$key] = "*";
		}
	}

    $minute 	= explode(",",$sep['minute']);
	$hour 		= explode(",",$sep['hour']);
    $day 		= explode(",",$sep['day']);
    $month 		= explode(",",$sep['month']);
	$weekday	= explode(",",$sep['weekday']);

	$min_options = array(
		"*" => LAN_CRON_11,
		"0,2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,32,34,36,38,40,42,44,46,48,50,52,54,56,58" => LAN_CRON_12,
		"0,5,10,15,20,25,30,35,40,45,50,55" => LAN_CRON_13,
		"0,10,20,30,40,50" => LAN_CRON_14,
		"0,15,30,45" => LAN_CRON_15
	);

	$hour_options = array(
		"*" => LAN_CRON_16,
		"0,2,4,6,8,10,12,14,16,18,20,22" => LAN_CRON_17,
		"0,3,6,9,12,15,18,21" => LAN_CRON_18,
		"0,6,12,18" => LAN_CRON_19
	);

	$text .= "<tr>
     <td class='forumheader3'>".$cron['name']."</td>
   	<td class='forumheader3'>".$cron['description']."</td>
   	<td class='forumheader3'>
	<input type='hidden'  name='cron[$c][path]' value='".$cron['path']."' />
   		<select class='tbox' style='height:70px' multiple='multiple' name='tab[$c][minute][]'>\n";

        foreach($min_options as $key=>$val)
		{
			if($sep['minute'] == $key)
			{
				$sel = "selected='selected'";
				$minute = "";
			}
			else
			{
				$sel =  "";
			}
        	$text .= "<option value='$key' $sel>".$val."</option>\n";
    	}


		for ($i=0; $i<=59; $i++)
		{
			$sel = (in_array(strval($i),$minute)) ? "selected='selected'" : "";
	    	$text .= "<option value='$i' $sel>".$i."</option>\n";
	    }
		$text .= "</select>
	</td>
   	<td class='forumheader3'>
		<select class='tbox' style='height:70px' multiple='multiple' name='tab[$c][hour][]'>
		\n";

        foreach($hour_options as $key=>$val)
		{
			if($sep['hour'] == $key)
			{
				$sel = "selected='selected'";
				$hour = "";
			}
			else
			{
				$sel =  "";
			}
        	$text .= "<option value='$key' $sel>".$val."</option>\n";
    	}

		for ($i=0; $i<=23; $i++)
		{
			$sel = (in_array(strval($i),$hour)) ? "selected='selected'" : "";
			$diz = mktime($i,00,00,1,1,2000);
	    	$text .= "<option value='$i' $sel>".$i." - ".date("g A",$diz)."</option>\n";
	    }
		$text .= "</select>
 	</td>
   	<td class='forumheader3'>
		<select class='tbox' style='height:70px' multiple='multiple' name='tab[$c][day][]'>\n";

		$sel_day = ($day[0] == "*") ? "selected='selected'" : "";

		$text .= "<option value='*' {$sel_day}>".LAN_CRON_20."</option>\n"; // Every Day
		for ($i=1; $i<=31; $i++)
		{
			$sel = (in_array($i,$day)) ? "selected='selected'" : "";
	    	$text .= "<option value='$i' $sel>".$i."</option>\n";
	    }
		$text .= "</select>
	</td>
   	<td class='forumheader3'>
		<select class='tbox' style='height:70px' multiple='multiple' name='tab[$c][month][]'>\n";

		$sel_month = ($month[0] == "*") ? "selected='selected'" : "";
		$text .= "<option value='*' $sel_month>".LAN_CRON_21."</option>\n"; // Every Month

		for ($i=1; $i<=12; $i++)
		{
			$sel = (in_array($i,$month)) ? "selected='selected'" : "";
			$diz = mktime(00,00,00,$i,1,2000);
	    	$text .= "<option value='$i' $sel>".strftime("%B",$diz)."</option>\n";
	    }
		$text .= "</select>
	</td>
   	<td class='forumheader3'>
    	<select class='tbox' style='height:70px' multiple='multiple' name='tab[$c][weekday][]'>\n";

		$sel_weekday = ($weekday[0] == "*") ? "selected='selected'" : "";
		$text .= "<option value='*' $sel_weekday>".LAN_CRON_22."</option>\n"; // Every Week Day.
		$days = array(LAN_SUN,LAN_MON,LAN_TUE,LAN_WED,LAN_THU,LAN_FRI,LAN_SAT);

		for ($i=0; $i<=6; $i++)
		{
			$sel = (in_array(strval($i),$weekday)) ? "selected='selected'" : "";
	    	$text .= "<option value='$i' $sel>".$days[$i]."</option>\n";
	    }
		$text .= "</select>
	</td>
       	<td class='forumheader3' style='text-align:center'>";
        $checked = ($cronpref[$c]['active'] == 1) ? "checked='checked'" : "";
		$text .= "<input type='checkbox' name='cron[$c][active]' value='1' $checked />
      	</td>
   	</tr>";
}

$text .= "

   <tr style='vertical-align:top'>
   <td colspan='8' style='text-align:center' class='forumheader'>";
   $text .= "<input class='button' type='submit' name='submit' value='".LAN_SAVE."' />";
   $text .= "</td>
   </tr>
   </table>
   </form>
   </div>";

   $ns -> tablerender(PAGE_NAME, $text);


require_once(e_ADMIN."footer.php");
exit;

?>