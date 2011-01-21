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
require_once("../../class2.php");
if (!getperms("P")) 
{
	header("location:../../index.php");
	exit;
}

if (isset($_POST['updateStats']))
{
	header("location: ".e_PLUGIN."log/admin_updateroutine.php");
	exit;
}

require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."userclass_class.php");

define("LOGPATH", e_PLUGIN."log/");

include_lan(LOGPATH."languages/admin/".e_LANGUAGE.".php");


if(isset($_POST['openRemPageD']))
{
	rempage();
}
if(isset($_POST['remSelP']))
{
	rempagego();
}


if(IsSet($_POST['wipeSubmit']))
{
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
	}
	$message = ADSTAT_L25;
}



if(!is_writable(LOGPATH."logs")) 
{
	$message = "<b>".ADSTAT_L38."</b>";
}

if (isset($_POST['updatesettings'])) {
	$pref['statActivate'] = $_POST['statActivate'];
	$pref['statCountAdmin'] = $_POST['statCountAdmin'];
	$pref['statUserclass'] = $_POST['statUserclass'];
	$pref['statBrowser'] = $_POST['statBrowser'];
	($_POST['statBrowser'] == 1)?($pref['statBrowserDispCompr'] = $_POST['statBrowserDispCompr']):$pref['statBrowserDispCompr']=0;
	$pref['statOs'] = $_POST['statOs'];
	$pref['statScreen'] = $_POST['statScreen'];
	$pref['statDomain'] = $_POST['statDomain'];
	$pref['statRefer'] = $_POST['statRefer'];
	$pref['statQuery'] = $_POST['statQuery'];
	$pref['statRecent'] = $_POST['statRecent'];
	$pref['statDisplayNumber'] = $_POST['statDisplayNumber'];
	save_prefs();
	$message = ADSTAT_L17;
}


if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>
	<table style='".ADMIN_WIDTH."' class='fborder'>

	<tr>
	<td style='width:50%' class='forumheader3'>".ADSTAT_L4."</td>
	<td style='width:50%; text-align: right;' class='forumheader3'>
	<input type='radio' name='statActivate' value='1'".($pref['statActivate'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statActivate' value='0'".(!$pref['statActivate'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".ADSTAT_L18."</td>
	<td style='width:50%; text-align: right;' class='forumheader3'>".r_userclass("statUserclass", $pref['statUserclass'],'off','public, member, admin, classes')."</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".ADSTAT_L20."</td>
	<td style='width:50%; text-align: right;' class='forumheader3'>
	<input type='radio' name='statCountAdmin' value='1'".($pref['statCountAdmin'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statCountAdmin' value='0'".(!$pref['statCountAdmin'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".ADSTAT_L21."</td>
	<td style='width:50%; text-align: right;' class='forumheader3'>
	<input class='tbox' type='text' name='statDisplayNumber' size='8' value='".$pref['statDisplayNumber']."' maxlength='3' />
	</td>
	</tr>


	<tr>
	<td style='width:50%' class='forumheader3'>".ADSTAT_L5."</td>
	<td style='width:50%; text-align: right;' class='forumheader3'>
	".ADSTAT_L6."&nbsp;&nbsp;
	<input type='radio' name='statBrowser' value='1'".($pref['statBrowser'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statBrowser' value='0'".(!$pref['statBrowser'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."<br />
	
	".ADSTAT_L7."&nbsp;&nbsp;
	<input type='radio' name='statOs' value='1'".($pref['statOs'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statOs' value='0'".(!$pref['statOs'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."<br />

	".ADSTAT_L8."&nbsp;&nbsp;
	<input type='radio' name='statScreen' value='1'".($pref['statScreen'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statScreen' value='0'".(!$pref['statScreen'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."<br />

	".ADSTAT_L9."&nbsp;&nbsp;
	<input type='radio' name='statDomain' value='1'".($pref['statDomain'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statDomain' value='0'".(!$pref['statDomain'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."<br />

	".ADSTAT_L10."&nbsp;&nbsp;
	<input type='radio' name='statRefer' value='1'".($pref['statRefer'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statRefer' value='0'".(!$pref['statRefer'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."<br />

	".ADSTAT_L11."&nbsp;&nbsp;
	<input type='radio' name='statQuery' value='1'".($pref['statQuery'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statQuery' value='0'".(!$pref['statQuery'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."<br />

	".ADSTAT_L19."&nbsp;&nbsp;
	<input type='radio' name='statRecent' value='1'".($pref['statRecent'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statRecent' value='0'".(!$pref['statRecent'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."<br />

	</td>
	</tr>
";

if ($pref['statBrowser'] == 1)
{	// Only display option to show browser stats in a compact way if stats on browser is actived
$text .= "
	<tr>
	<td style='width:50%' class='forumheader3'>".ADSTAT_L35."</span></td>
	<td style='width:50%; text-align: right;' class='forumheader3'>
	<input type='radio' name='statBrowserDispCompr' value='1'".($pref['statBrowserDispCompr'] ? " checked='checked'" : "")." /> ".ADSTAT_ON."&nbsp;&nbsp;
	<input type='radio' name='statBrowserDispCompr' value='0'".(!$pref['statBrowserDispCompr'] ? " checked='checked'" : "")." /> ".ADSTAT_OFF."<br />
	</td>
	</tr>";
}
	
$text .= "
	<tr>
	<td style='width:50%' class='forumheader3'>".ADSTAT_L12."<br /><span class='smalltext'>".ADSTAT_L13."</span></td>
	<td style='width:50%; text-align: right;' class='forumheader3'>
	".ADSTAT_L14."<input type='checkbox' name='wipe[statWipePage]' value='1' /><br />
	".ADSTAT_L6."<input type='checkbox' name='wipe[statWipeBrowser]' value='1' /><br />
	".ADSTAT_L7." <input type='checkbox' name='wipe[statWipeOs]' value='1' /><br />
	".ADSTAT_L8." <input type='checkbox' name='wipe[statWipeScreen]' value='1' /><br />
	".ADSTAT_L9."<input type='checkbox' name='wipe[statWipeDomain]' value='1' /><br />
	".ADSTAT_L10."<input type='checkbox' name='wipe[statWipeRefer]' value='1' /><br />
	".ADSTAT_L11."<input type='checkbox' name='wipe[statWipeQuery]' value='1' /><br />
	<br /><input class='button' type='submit' name='wipeSubmit' value='".ADSTAT_L12."' />
	</td>
	</tr>

	<tr>
	<td style='width:50%' class='forumheader3'>".ADSTAT_L26."<br /><span class='smalltext'>".ADSTAT_L27."</span></td>
	<td style='width:50%; text-align: right;' class='forumheader3'><input class='button' type='submit' name='openRemPageD' value='".ADSTAT_L28."' />
	</td>
	</tr>

	";

	$text .= "



	<tr>
	<td colspan='2'  style='text-align:center' class='forumheader'>
	<input class='button' type='submit' name='updatesettings' value='".ADSTAT_L15."' />
	</td>
	</tr>
	</table>
	</form>
	</div>";

$ns->tablerender(ADSTAT_L16, $text);
require_once(e_ADMIN."footer.php");




function rempage()
{
	global $sql, $ns;

	$logfile = e_PLUGIN."log/logs/logp_".date("z.Y", time()).".php";
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

	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>
	<table style='".ADMIN_WIDTH."' class='fborder'>

	<tr>
	<td style='width:30%' class='forumheader'>".ADSTAT_L29."</td>
	<td style='width:50%' class='forumheader'>URL</td>
	<td style='width:30%; text-align: center;' class='forumheader'>".ADSTAT_L30." ...</td>
	</tr>
	";

	foreach($pageTotal as $key => $page)
	{
		$text .= "
		<tr>
		<td style='width:30%' class='forumheader3'>$key</td>
		<td style='width:50%' class='forumheader3'>".$page['url']."</td>
		<td style='width:30%; text-align: center;' class='forumheader3'><input type='checkbox' name='remcb[]' value='$key' /></td>
		</tr>
		";
	}

	$text .= "

	<tr>
	<td colspan='3' class='forumheader3' style='text-align: center;'><input class='button' type='submit' name='remSelP' value='".ADSTAT_L31."' />
	</td>
	</tr>

	</table>
	</form>
	</div>
	";

	$ns -> tablerender(ADSTAT_L32, $text);
}


function rempagego()
{
	global $sql;

	$sql -> db_Select("logstats", "*", "log_id='pageTotal' ");
	$row = $sql -> db_Fetch();
	$pageTotal = unserialize($row['log_data']);

	$logfile = e_PLUGIN."log/logs/logp_".date("z.Y", time()).".php";
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
	if(!$sql -> db_Update("logstats", "log_data='$pagetotal' WHERE log_id='pageTotal' "))
	{
		$sql -> db_Insert("logstats", "0, 'pageTotal', '$pagetotal' ");
	}

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

	if ($handle = fopen($logfile, 'w')) {
		fwrite($handle, $data);
	}
	fclose($handle);


}
?>
