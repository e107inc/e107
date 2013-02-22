<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	Stats logging plugin - admin functions
 *
 * $URL$
 * $Id$
 */

require_once('../../class2.php');
if (!getperms('P') || !e107::isInstalled('log')) 
{
	header('Location: '.e_BASE.'index.php');
	exit;
}


require_once(e_ADMIN.'auth.php');
require_once(e_HANDLER.'userclass_class.php');
$frm = e107::getForm();
$mes = e107::getMessage();

define('LogFlagFile', 'LogFlag.php');

include_lan(e_PLUGIN.'log/languages/'.e_LANGUAGE.'_admin_log.php');

if (e_QUERY) 
{
	$sl_qs = explode('.', e_QUERY);
}
$action = varset($sl_qs[0],'config');
$params = varset($sl_qs[1],'');


// List of the non-page-based info which is gathered - historically only 'all-time' stats, now we support monthly as well
$stats_list = array('statBrowser'=>ADSTAT_L6,'statOs'=>ADSTAT_L7,'statScreen'=>ADSTAT_L8,'statDomain'=>ADSTAT_L9,'statReferer'=>ADSTAT_L10,'statQuery'=>ADSTAT_L11);

$separator_list = array(1 => ADSTAT_L57, 2 => ADSTAT_L58);
$separator_char = array(1 => ',', 2 => '|');
$quote_list = array(1 => ADSTAT_L50, 2 => ADSTAT_L55, 3 => ADSTAT_L56);
$quote_char = array(1 => '', 2 => "'", 3 => '"');


//---------------------------------------------
//		Export data file
//---------------------------------------------
$export_filter = '';		// can be 'LIKE', 'REGEX', or simple equality
$export_type = $tp->toDB(varset($_POST['export_type'],'page'));				// Page data or one of the other bits of info
$export_date = intval(varset($_POST['export_date'],1));
$export2_date = intval(varset($_POST['export2_date'],3));
$export_year = intval(varset($_POST['export_year'],date('Y')));
$export_month = intval(varset($_POST['export_month'],date('m')));
$export_day = intval(varset($_POST['export_day'],date('j')));
$export_char = varset($_POST['export_char'], 1);
$export_quote = varset($_POST['export_quote'], 1);
$export_stripurl = varset($_POST['export_stripurl'], 0);

if (isset($_POST['create_export']) && (($action == 'export') || ($action == 'datasets')))
{
	$first_date = 0;
	$last_date = 0;
	$date_error = FALSE;
	if ($export_type == 'page')
	{
		switch ($export_date)
		{
			case '1' :		//	Single day
				$first_date = gmmktime(0,0,0,$export_month,$export_day,$export_year);
				$last_date = $first_date+86399;
				$export_filter = " `log_id`='".date("Y-m-j",$first_date)."'";
				break;
			case '2' :		// Daily for a month
				$first_date = gmmktime(0,0,0,$export_month,1,$export_year);
				$last_date = gmmktime(0,0,0,$export_month+1,1,$export_year) - 1;
				$export_filter = " LEFT(`log_id`,8)='".gmstrftime("%Y-%m-",$first_date)."'";
				break;
			case '3' :		// Monthly for a Year
				$first_date = gmmktime(0,0,0,1,1,$export_year);
				$last_date = gmmktime(0,0,0,1,1,$export_year+1) - 1;
				$export_filter = " LENGTH(`log_id`)=7 AND LEFT(`log_id`,5)='".gmstrftime("%Y-",$first_date)."'";
				break;
			case '4' :		// Accumulated
			case '5' :
				$export_filter = "`log_id`='pageTotal'";
				$date_error = 'ignore';
				break;
		}
	}
	else
	{  // Calculate strings for non-page sources
		$prefix_len = 0;
		$export_date = $export2_date;
		if (isset($stats_list[$export_type]))
		{
			$prefix_len = strlen($export_type) + 1;
			switch ($export2_date)
			{
				case '3' :		// Monthly for a Year
					if ($prefix_len > 0)
					{
						$first_date = gmmktime(0,0,0,1,1,$export_year);
						$last_date = gmmktime(0,0,0,1,1,$export_year+1) - 1;
						$export_filter = " LENGTH(`log_id`)='".($prefix_len + 7)."' AND LEFT(`log_id`,".($prefix_len + 5).")='".$export_type.":".gmstrftime("%Y-",$first_date)."'";
					}
					break;
				case '4' :		// Accumulated
					$export_filter = " `log_id`='".$export_type."'";
					$date_error = 'ignore';
					break;
			}
		}
		else
		{
			$message = ADSTAT_L54;
		}
	}
	if (($date_error != 'ignore') && (($first_date == 0) || ($last_date == 0) || $date_error))
	{
		$message = ADSTAT_L47;
	}
}


// Needed on Windoze platforms - not an ideal solution!
if (!function_exists('nl_langinfo'))
{
  define('MON_1',86400);
  define('MON_2',2764800);
  define('MON_3',5443200);
  define('MON_4',8035200);
  define('MON_5',10800000);
  define('MON_6',13392000);
  define('MON_7',15811200);
  define('MON_8',19008000);
  define('MON_9',21168000);
  define('MON_10',23760000);
  define('MON_11',26352000);
  define('MON_12',28944000);
  function nl_langinfo($mon)
  {
    return date('F',$mon);
  }
}

//---------------------------------------------
//		Remove page entries
//---------------------------------------------
if(isset($_POST['openRemPageD']))
{
  $action = 'rempage';
}

if(isset($_POST['remSelP']))
{
  $action = 'rempage';
  rempagego();				// Do the deletions - then redisplay the list of pages
}


//---------------------------------------------
//		Wipe accumulated stats
//---------------------------------------------
if(IsSet($_POST['wipeSubmit']))
{
	$logStr = '';
	foreach($_POST['wipe'] as $key => $wipe)
	{
		switch($key)
		{
			case "statWipePage":
				$sql -> db_Update("logstats", "log_data='' WHERE log_id='pageTotal' ");
				$sql -> db_Update("logstats", "log_data='' WHERE log_id='statTotal' ");
				$sql -> db_Update("logstats", "log_data='' WHERE log_id='statUnique' ");
			break;
			case "statWipeBrowser":
				$sql -> db_Update("logstats", "log_data='' WHERE log_id='statBrowser' ");
			break;
			case "statWipeOs":
				$sql -> db_Update("logstats", "log_data='' WHERE log_id='statOs' ");
			break;
			case "statWipeScreen":
				$sql -> db_Update("logstats", "log_data='' WHERE log_id='statScreen' ");
			break;
			case "statWipeDomain":
				$sql -> db_Update("logstats", "log_data='' WHERE log_id='statDomain' ");
			break;
			case "statWipeRefer":
				$sql -> db_Update("logstats", "log_data='' WHERE log_id='statReferer' ");
			break;
			case "statWipeQuery":
				$sql -> db_Update("logstats", "log_data='' WHERE log_id='statQuery' ");
			break;
		}
		$logStr .= '[!br!]'.$key;
	}
	$admin_log->log_event('STAT_01',ADSTAT_L81.$logStr,'');

	//$message = ADSTAT_L25; // TODO:$emessage
	$mes->addSuccess(LAN_UPDATED);
}


if(!is_writable(e_LOG)) 
{
	//$message = "<b>".ADSTAT_L38."</b>"; 
	$mes->addError(ADSTAT_L28);
}

if (isset($_POST['updatesettings'])) 
{
	$statList = array(		// Type = 0 for direct text, 1 for integer
		'statActivate' 		=> 0,
		'statCountAdmin' 	=> 0,
		'statUserclass' 	=> 0,
		'statBrowser'		=> 1,
		'statOs'			=> 1,
		'statScreen' 		=> 1,
		'statDomain' 		=> 1,
		'statRefer' 		=> 1,
		'statQuery' 		=> 1,
		'statRecent' 		=> 1,
		'statDisplayNumber' => 0,
		'statPrevMonth'		=> 1
	);
	$logStr = '';
	foreach ($statList as $k => $type)
	{
		switch ($type)
		{
		  case 0 : $pref[$k] = $_POST[$k]; break;
		  case 1 : $pref[$k] = intval($_POST[$k]); break;
		}
		$logStr .= "[!br!]{$k} => ".$pref[$k];
	}
	save_prefs();
	file_put_contents(e_LOG.LogFlagFile, "<?php\n\$logEnable={$pref['statActivate']};\n?>\n");		// Logging task uses to see if logging enabled
	$admin_log->log_event('STAT_02',ADSTAT_L82.$logStr,'');
}

$ns->tablerender($caption, $mes->render() . $text);

function gen_select($prompt,$name,$value)
{
  $ret = "<div style='padding-bottom: 4px'>".$prompt."&nbsp;&nbsp;"."<select name='{$name}' class='tbox e-select'>\n
		<option value='0' ".($value == 0 ? " selected='selected'" : "").">".ADSTAT_L50."</option>\n
		<option value='1' ".($value == 1 ? " selected='selected'" : "").">".ADSTAT_L49."</option>\n
		<option value='2' ".($value == 2 ? " selected='selected'" : "").">".ADSTAT_L48."</option>\n
		</select>\n</div>";
  return $ret;
}


function data_type_select($name,$value)
{
  global $stats_list;
  $ret = "<select name='{$name}' class='tbox e-select'  onchange=\"settypebox(this.value);\">\n
		<option value='page' ".($value == 'page' ? " selected='selected'" : "").">".ADSTAT_L52."</option>\n";
  foreach ($stats_list as $k=>$v)
  {
	$ret .= "<option value='{$k}' ".($value == $k ? " selected='selected'" : "").">{$v}</option>\n";
  }
  $ret .= "</select>\n";
  return $ret;
}


switch ($action)
{
  case 'config' :
	$text = "
	<form method='post' action='".e_SELF."'>
	<table class='table adminform'>
	<colgroup>
		<col style='width:50%' />
		<col style='width:50%' />
	</colgroup>

	<tr>
		<td>".ADSTAT_L4."</td>
		<td>".$frm->radio_switch('statActivate', $pref['statActivate'])."</td>
	</tr>
	<tr>
		<td>".ADSTAT_L18."</td>
		<td>".r_userclass("statUserclass", $pref['statUserclass'],'off','public, member, admin, classes')."</td>
	</tr>
	<tr>
		<td>".ADSTAT_L20."</td>
		<td>".$frm->radio_switch('statCountAdmin', $pref['statCountAdmin'])."</td>
	</tr>
	<tr>
		<td>".ADSTAT_L21."</td>
		<td><input class='tbox' type='text' name='statDisplayNumber' size='8' value='".$pref['statDisplayNumber']."' maxlength='3' /></td>
	</tr>
	<tr>
		<td>".ADSTAT_L5."</td>
		<td>
		".gen_select(ADSTAT_L6, 'statBrowser',$pref['statBrowser'])
		 .gen_select(ADSTAT_L7, 'statOs',$pref['statOs'])
		 .gen_select(ADSTAT_L8, 'statScreen',$pref['statScreen'])
		 .gen_select(ADSTAT_L9, 'statDomain',$pref['statDomain'])
		 .gen_select(ADSTAT_L10, 'statRefer',$pref['statRefer'])
		 .gen_select(ADSTAT_L11, 'statQuery',$pref['statQuery'])
		 .ADSTAT_L19."&nbsp;&nbsp;
		 ".$frm->radio_switch('statRecent', $pref['statRecent'])."
		</td>
	</tr>

	<tr>
	<td>".ADSTAT_L78."</td>
		<td>".$frm->checkbox('statPrevMonth', 1, varset($pref['statPrevMonth'],0))."<span class='field-help'>".ADSTAT_L79."</span></td>
	</tr>
	<tr>
		<td>".ADSTAT_L12."</td>
		<td>
			".$frm->checkbox('wipe[statWipePage]', 1)." ".ADSTAT_L14."<br />
			".$frm->checkbox('wipe[statWipeBrowser]', 1)." ".ADSTAT_L6."<br />
			".$frm->checkbox('wipe[statWipeOs]', 1)." ".ADSTAT_L7."<br />
			".$frm->checkbox('wipe[statWipeScreen]', 1)." ".ADSTAT_L8."<br />
			".$frm->checkbox('wipe[statWipeDomain]', 1)." ".ADSTAT_L9."<br />
			".$frm->checkbox('wipe[statWipeRefer]', 1)." ".ADSTAT_L10."<br />
			".$frm->checkbox('wipe[statWipeQuery]', 1)." ".ADSTAT_L11."<br />
			<br />
			".$frm->admin_button('wipeSubmit', LAN_RESET, 'delete')."<span class='field-help'>".ADSTAT_L13."</span>
		</td>
	</tr>
	<tr>
		<td>".ADSTAT_L26."</td>
		<td>".$frm->admin_button('openRemPageD', ADSTAT_L28, 'other')."<span class='field-help'>".ADSTAT_L27."</span>	</td>
	</tr>
	</table>
	<div class='buttons-bar center'>
		".$frm->admin_button('updatesettings', LAN_UPDATE, 'update')."
	</div>
	</form>";

	$ns->tablerender(ADSTAT_L16, $text);
	break;  // case config
	
  case 'rempage' :			// Remove pages
	rempage();
    break;
	
	
  case 'export' :			// Export file
  case 'datasets' :
	//===========================================================
	//				EXPORT DATA
	//===========================================================
	$text = "<div style='text-align:center'>";
	if ($action == 'export')
	{
	  $text .= "<form method='post' action='".e_PLUGIN."log/stats_csv.php?export'>";
	}
	else
	{
	  $text .= "<form method='post' action='".e_SELF."?datasets'>";
	}
	$text .= "<table class='table adminform'>
	<colgroup>
	  <col style='width:50%' />
	  <col style='width:50%' />
	</colgroup>
	";

	if ($action == 'export')
	{
	  $text .= "<tr><td colspan = '2'>".ADSTAT_L67."</td></tr>";
	}
	else
	{
	  $text .= "<tr><td colspan = '2'>".ADSTAT_L68."</td></tr>";
	}

	// Type of output data - page data, browser stats....
	$text .= "<tr><td>".ADSTAT_L51."</td><td>\n".data_type_select('export_type',$export_type).'</td></tr>';

	// Period selection type for page data
	$text .= "<tr><td>".ADSTAT_L41."</td><td>\n
	<select class='tbox e-select' name='export_date' id='export_date' onchange=\"setdatebox(this.value);\" ".($export_type=='page' ? "" : "style='display:none'" ).">\n
	<option value='1'".($export_date==1 ? " selected='selected'" : "").">".ADSTAT_L42."</option>\n
	<option value='2'".($export_date==2 ? " selected='selected'" : "").">".ADSTAT_L43."</option>\n
	<option value='3'".($export_date==3 ? " selected='selected'" : "").">".ADSTAT_L44."</option>\n
	<option value='4'".($export_date==4 ? " selected='selected'" : "").">".ADSTAT_L45."</option>\n
	<option value='5'".($export_date==5 ? " selected='selected'" : "").">".ADSTAT_L62."</option>\n
	</select>";
	
	// Period selection type for non-page data
	$text .= "
	<select class='tbox e-select' name='export2_date' id='export2_date' onchange=\"setdatebox(this.value);\"  ".($export_type=='page' ? "style='display:none'" : "").">\n
	<option value='3'".($export2_date==3 ? " selected='selected'" : "").">".ADSTAT_L44."</option>\n
	<option value='4'".($export2_date==4 ? " selected='selected'" : "").">".ADSTAT_L45."</option>\n
	</select>";

	$text .= "</td></tr>";



	$text .= "<tr><td>".ADSTAT_L46."</td><td>\n";
	
	
	// Now put the various dropdowns - their visibility is controlled by the export_type dropdown

	$text .= "<select class='tbox e-select' name='export_day' id='export_day'>\n";
	for ($i = 1; $i < 32; $i++) 
	{ 
	  $selected = $export_day == $i ? " selected='selected'" : "";
	  $text .= "<option value='{$i}'{$selected}>{$i}</option>\n"; 
	};
	$text .= "</select>\n&nbsp;&nbsp;&nbsp;";


	$text .= "<select class='tbox e-select' name='export_month' id='export_month'>\n";
	for ($i = 1; $i < 13; $i++) 
	{ 
	  $selected = $export_month == $i ? " selected='selected'" : "";
	  $text .= "<option value='{$i}'{$selected}>".nl_langinfo(constant('MON_'.$i))."</option>\n"; 
	};
	$text .= "</select>\n&nbsp;&nbsp;&nbsp;";
		
	$this_year = date("Y");
	$text .= "<select class='tbox e-select' name='export_year' id='export_year'>\n";
	for ($i = $this_year; $i > $this_year - 6; $i--) 
	{ 
	  $selected = $export_year == $i ? " selected='selected'" : "";
	  $text .= "<option value='{$i}'{$selected}>{$i}</option>\n"; 
	};
	$text .= "</select>\n&nbsp;&nbsp;&nbsp;";

	$text .= "<span id='export_cumulative' style='display: none'>".ADSTAT_L53."</span>\n";

	$text .= "</td></tr>";


	if ($action == 'export')
	{
	// Separators, quotes
	$text .= "<tr><td>".ADSTAT_L59."</td><td>\n
			<select class='tbox e-select' name='export_char'>";
	foreach ($separator_list as $k=>$v)
	{
	  $selected = $export_char == $k ? " selected='selected'" : "";
	  $text .= "<option value='{$k}'{$selected}>{$v}</option>\n";
	}
	$text .= "</select>\n&nbsp;&nbsp;&nbsp;&nbsp;<select class='tbox e-select' name='export_quote'>\n";
	foreach ($quote_list as $k=>$v)
	{
	  $selected = $export_quote == $k ? " selected='selected'" : "";
	  $text .= "<option value='{$k}'{$selected}>{$v}</option>\n";
	}
	$text .= "</select>\n</td></tr>";

	$text .= "<tr>
	<td>".ADSTAT_L60."</td>
	<td>".$frm->checkbox('export_stripurl', 1)."<span class='field-help'>".ADSTAT_L61."</span></td>
	</tr>";
	}


	if ($export_filter)
	{
	  if (getperms('0')) $text .= "<tr><td>".ADSTAT_L65."</td><td>".$export_filter."</td></tr>";
	  $sql -> db_Select("logstats", "log_id", "{$export_filter} ");
	  $text .= "<tr><td>".ADSTAT_L64."</td><td>";
	  while($row = $sql -> db_Fetch())
	  {
		$text .= $row['log_id']."<br />";
	  }
	  $text .= "</td></tr>";
	}

	$text .= "
	</table>
		<div class='buttons-bar center'>
	".$frm->admin_button('create_export', ($action == 'export' ? LAN_CREATE : ADSTAT_L66), 'update')."
	</div>
	</form>
	</div>";

	// Set up the date display boxes
	$text .= "<script type=\"text/javascript\"> settypebox('{$export_type}');</script>";

	$ns->tablerender(ADSTAT_L40, $text);
    break;	// case 'export'
	
  case 'history' :
	//===========================================================
	//				DELETE HISTORY
	//===========================================================
	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."?history'>
	<table class='table adminlist'>
	<colgroup>
	  <col style='width:50%' />
	  <col style='width:50%' />
	</colgroup>";
	$keep_month = varset($_POST['delete_month'],0);
	$keep_year = varset($_POST['delete_year'],0);
    if (isset($_POST['delete_history']))
	{
	  $text .= "<tr><td>".ADSTAT_L72."</td><td>".nl_langinfo(constant('MON_'.$keep_month))." ".$keep_year."</td></tr>
		<tr><td colspan='2'  style='text-align:center' class='forumheader'>
		<input type='hidden' name='delete_month' value='{$keep_month}' />
		<input type='hidden' name='delete_year' value='{$keep_year}' />
		<input class='button' type='submit' name='actually_delete' value='".ADSTAT_L73."' /><br />".ADSTAT_L74."
	</td></tr>";
	  $text .= "<tr><td>".ADSTAT_L75."</td><td>".implode("<br />",get_for_delete($keep_year,$keep_month))."</td></tr>";
	}
	else
	{
	  if (isset($_POST['actually_delete']))
	  {
		    $delete_list = get_for_delete($keep_year,$keep_month);
			$logStr = '';
	//	    $text .= "<tr><td colspan='2'>Data notionally deleted {$keep_month}-{$keep_year}</td></tr>";
			$text .= "<tr><td>".ADSTAT_L77."</td><td>";
			foreach ($delete_list as $k => $v)
			{
			  $sql->db_Delete('logstats',"log_id='{$k}'");
			  $text .= $v."<br />";
			  $logStr .= "[!br!]{$k} => ".$v;
			}
			$text .= "</td></tr>";
			$admin_log->log_event('STAT_04',ADSTAT_L83.$logStr,'');
		  }
		$text .= "<tr><td>".ADSTAT_L70."</td>";
		$text .= "<td><select class='tbox e-select' name='delete_month'>\n";
		$match_month = date("n");
		for ($i = 1; $i < 13; $i++) 
		{ 
		  $selected = $match_month == $i ? " selected='selected'" : "";
		  $text .= "<option value='{$i}'{$selected}>".nl_langinfo(constant('MON_'.$i))."</option>\n"; 
		};
		$text .= "</select>\n&nbsp;&nbsp;&nbsp;";
			
		$this_year = date("Y");
		$text .= "<select class='tbox e-select' name='delete_year' id='export_year'>\n";
		for ($i = $this_year; $i > $this_year - 6; $i--) 
		{ 
		  $selected = ($this_year - 2) == $i ? " selected='selected'" : "";
		  $text .= "<option value='{$i}'{$selected}>{$i}</option>\n"; 
		};
		$text .= "</select>\n</td></tr>";
	}

	$text .= "</table>
	
	<div class='buttons-bar center'>
	".$frm->admin_button('delete_history',LAN_DELETE,'delete')."
	<span class='field-help'>".ADSTAT_L76."</span>
	</div>
	
	</form></div>";
	$ns->tablerender(ADSTAT_L69, $text);
    break;	// case 'history'

}


require_once(e_ADMIN."footer.php");


function headerjs()
{
  $script_js = "<script type=\"text/javascript\">
	//<![CDATA[
	  var names = new Array();
	    names[0] = 'export_day';
	    names[1] = 'export_month';
	    names[2] = 'export_year';
	    names[3] = 'export_cumulative';

	  var dispinfo = new Array();
		dispinfo[1] = new Array();		// Single day
		  dispinfo[1][0] = '';
		  dispinfo[1][1] = '';
		  dispinfo[1][2] = '';
		  dispinfo[1][3] = 'none';
		  
		dispinfo[2] = new Array();		// Month
		  dispinfo[2][0] = 'none';
		  dispinfo[2][1] = '';
		  dispinfo[2][2] = '';
		  dispinfo[2][3] = 'none';
		  
		dispinfo[3] = new Array();		// Year
		  dispinfo[3][0] = 'none';
		  dispinfo[3][1] = 'none';
		  dispinfo[3][2] = '';
		  dispinfo[3][3] = 'none';
		  
		dispinfo[4] = new Array();		// Specials
		  dispinfo[4][0] = 'none';
		  dispinfo[4][1] = 'none';
		  dispinfo[4][2] = 'none';
		  dispinfo[4][3] = '';


	function setdatebox(disptype)
	{
	  var target;
	  var j;

	  if (disptype > 4) disptype = 4;

	  for (j = 0; j < names.length; j++)
	  {
	    target = document.getElementById(names[j]).style;
		target.display = dispinfo[disptype][j];
	  }
	}
	
	function settypebox(pagetype)
	{
	  var newdateformat = 1;
	  var target1 = document.getElementById('export_date');
	  var target2 = document.getElementById('export2_date');
	  if (pagetype == 'page')
	  {
	    target1.style.display = '';
	    target2.style.display = 'none';
		newdateformat = target1.value;
	  }
	  else
	  {
	    target1.style.display = 'none';
	    target2.style.display = '';
		newdateformat = target2.value;
	  }
	  setdatebox(newdateformat);
	}

	//]]>
	</script>\n";
  return $script_js;
}

function get_for_delete($keep_year,$keep_month = 1, $filter='*')
{
  global $sql, $stats_list;
  $ret = array();
  // Its tedious, but the filter criteria are sufficiently tricky that its probably best to read all records and decide what can go
  if ($sql->db_Select('logstats','log_id'))
  {
    while ($row = $sql->db_Fetch())
	{
	  $can_go = FALSE;
	  $check = FALSE;
	  $data_type = 'unknown';
	  $date_info = $row['log_id'];
	  if (($temp = strpos($date_info,':')) !== FALSE) 
	  {  // its monthly browser stats and similar
//	    echo "Checking {$date_info}, posn = {$temp} ";
		$data_type = substr($date_info,0,$temp);
	    $date_info = substr($date_info,$temp+1);
		$check = TRUE;
//		echo "Date string: {$date_info}, data type: {$data_type}<br />";
		if (isset($stats_list[$data_type])) $data_type = $stats_list[$data_type];
	  }
	  list($poss_year,$poss_month,$poss_day) = explode('-',$date_info.'--',3);
	  if (!$check)
	  {
	    if (is_numeric($poss_year))
		{
		  $check = TRUE;
		  if ($poss_day > 0) $data_type = 'daily'; else $data_type = 'monthly';
		}
	  }
	  if ($check)
	  {
	    if ($keep_year == $poss_year)
		{
		  if (($poss_month > 0) && ($poss_month < $keep_month)) $can_go = TRUE;
		}
		elseif ($keep_year > $poss_year) $can_go = TRUE;
	  }
	  if ($can_go)
	  {
	    $ret[$row['log_id']] = $row['log_id']." - ".$data_type;
	  }
	}
  }
  return $ret;
}

//---------------------------------------------
//		Remove page entries - prompt/list
//---------------------------------------------
function rempage()
{
	$sql = e107::getDb();
	$ns = e107::getRender();
	$frm = e107::getForm();

	$logfile = e_LOG."logp_".date("z.Y", time()).".php";
//	$logfile = e_PLUGIN."log/logs/logp_".date("z.Y", time()).".php";
	if(is_readable($logfile))
	{
		require($logfile);
	}

	$sql -> db_Select("logstats", "*", "log_id='pageTotal' ");
	$row = $sql -> db_Fetch();
	$pageTotal = unserialize($row['log_data']);

	foreach($pageInfo as $url => $tmpcon) {
		$pageTotal[$url]['url'] = $tmpcon['url'];
		$pageTotal[$url]['ttlv'] += $tmpcon['ttl'];
		$pageTotal[$url]['unqv'] += $tmpcon['unq'];
	}

	$text = "
	<form method='post' action='".e_SELF."'>
	<table class='table adminlist'>

	<tr>
	<td style='width:30%' class='forumheader'>".ADSTAT_L29."</td>
	<td style='width:50%' class='forumheader'>URL</td>
	<td style='width:30%; text-align: center;'>".ADSTAT_L30." ...</td>
	</tr>
	";

	foreach($pageTotal as $key => $page)
	{
		$text .= "
		<tr>
		<td style='width:30%'>{$key}</td>
		<td style='width:50%'>".$page['url']."</td>
		<td style='width:30%; text-align: center;'><input type='checkbox' name='remcb[]' value='{$key}' /></td>
		</tr>
		";
	}

	$text .= "
	</table>
	<div class='buttons-bar center'>
	".$frm->admin_button('remSelP', ADSTAT_L31, 'delete')."
	</form>

	";

	$ns -> tablerender(ADSTAT_L32, $text);
}


//---------------------------------------------
//		Remove page entries - action
//---------------------------------------------
function rempagego()
{
	global $sql, $admin_log;

	$sql -> db_Select("logstats", "*", "log_id='pageTotal' ");
	$row = $sql -> db_Fetch();
	$pageTotal = unserialize($row['log_data']);
	$logfile = e_LOG."logp_".date("z.Y", time()).".php";
	
//	$logfile = e_PLUGIN."log/logs/logp_".date("z.Y", time()).".php";
	if(is_readable($logfile))
	{
		require($logfile);
	}

	foreach($_POST['remcb'] as $page)
	{
		unset($pageInfo[$page]);
		unset($pageTotal[$page]);
	}

	$pagetotal = serialize($pageTotal);
	if(!$sql -> db_Update("logstats", "log_data='{$pagetotal}' WHERE log_id='pageTotal' "))
	{
		$sql -> db_Insert("logstats", "0, 'pageTotal', '{$pagetotal}' ");
	}
	$admin_log->log_event('STAT_03',ADSTAT_L80."[!br!]".implode("[!br!]",$_POST['remcb']),'');

	$varStart = chr(36);
	$quote = chr(34);

	$data = chr(60)."?php\n". chr(47)."* e107 website system: Log file: ".date("z:Y", time())." *". chr(47)."\n\n".
	$varStart."ipAddresses = ".$quote.$ipAddresses.$quote.";\n".
	$varStart."siteTotal = ".$quote.$siteTotal.$quote.";\n".
	$varStart."siteUnique = ".$quote.$siteUnique.$quote.";\n";

	$loop = FALSE;
	$data .= $varStart."pageInfo = array(\n";
	foreach($pageInfo as $info)
	{
		$page = preg_replace("/(\?.*)|(\_.*)|(\.php)|(\s)|(\')|(\")|(eself)|(&nbsp;)/", "", basename ($info['url']));
		$page = str_replace("\\", "", $page);
		$info['url'] = preg_replace("/(\s)|(\')|(\")|(eself)|(&nbsp;)/", "", $info['url']);
		$info['url'] = str_replace("\\", "", $info['url']);
		$page = trim($page);
		if($page && !strstr($page, "cache") && !strstr($page, "file:"))
		{
			if($loop){ $data .= ",\n"; }
			$data .= $quote.$page.$quote." => array('url' => '".$info['url']."', 'ttl' => ".$info['ttl'].", 'unq' => ".$info['unq'].")";
			$loop = 1;
		}
	}

	$data .= "\n);\n\n?".  chr(62);

	if ($handle = fopen($logfile, 'w')) 
	{
	  fwrite($handle, $data);
	}
	fclose($handle);
}



function admin_config_adminmenu()
{
  if (e_QUERY) 
  {
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
  }
  if (!isset($action) || ($action == "")) $action = "config";

  $var['config']['text'] = ADSTAT_L35;
  $var['config']['link'] = 'admin_config.php';

  $var['export']['text'] = ADSTAT_L36;
  $var['export']['link'] ='admin_config.php?export';

//  $var['datasets']['text'] = ADSTAT_L63;
//  $var['datasets']['link'] ='admin_config.php?datasets';

  $var['rempage']['text'] = ADSTAT_L26;
  $var['rempage']['link'] ='admin_config.php?rempage';

  $var['history']['text'] = ADSTAT_L69;
  $var['history']['link'] ='admin_config.php?history';

  show_admin_menu(ADSTAT_L39, $action, $var);
}


?>
