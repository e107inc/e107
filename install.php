<?php
//if(filesize("config.php") != 0 && $_POST['stage'] == ""){
//	 header("location: index.php");
//}
if(IsSet($_POST['frontpage'])){ header("location: index.php"); }
if(IsSet($_POST['adminpage'])){ header("location: admin/admin.php"); }
if(!$_POST['mysql_server']){ $_POST['mysql_server'] = "localhost"; }
if(!$_POST['mysql_prefix']){ $_POST['mysql_prefix'] = "e107_"; }
if(!$_POST['admin_email']){ $_POST['admin_email'] = "you@yoursite.com"; }

echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><e107 installing ...></title>
<link rel="stylesheet" href="themes/e107/style.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-style-type" content="text/css" />
</head>
<body>

<div style="text-align:center">
<table style="width:100%" cellspacing="0" cellpadding="0">
<tr>
<td style="width:66%; background-color:#E2E2E2; text-align:left\">
<img src="themes/shared/logo.png" alt="Logo" />
</td>
<td style="background-color:#E2E2E2; text-align:right; vertical-align:bottom" class="smalltext">
©Steve Dunstan 2002. See gpl.txt for licence details.
</td>
<tr> 
<td colspan="2" style="background-color:#000; vertical-align: top;"></td>
</tr>
<tr>
<tr> 
<td colspan="2" style="background-color:#ccc; vertical-align: top;">

<?php
if(!$_POST['stage']){ $_POST['stage'] = 1; }
echo "<div class=\"mediumtext\">&nbsp;&nbsp;Installation Stage: ".$_POST['stage']." of 2</div>";
?>

</td>
</tr>
<tr> 
<td colspan="2" style="background-color:#000; vertical-align: top;"></td>
</tr>
<tr>
<td colspan="2" style="vertical-align: top; text-align:center"><br />
<table style="width:66%" class="fborder">
<tr>
<td class="installb">

<!-- BEGIN STAGE 0 -->
<?php
echo "<br /><img src=\"themes/e107/images/installlogo.png\" alt=\"\" /><br /><span class=\"smalltext\">php/mySQL website system</span><br />";
if($_POST['stage'] == 1){
	echo "<br />This script will install e107 on your server.<br />";

?>
<br />
</td>
</tr>
</table>
<br />
<table style="width:50%" class="fborder">
<tr>
<td class="forumheader" colspan="3" style="text-align:center">
<span class="installh">Server Tests</span><br />
</td>
</tr>
<tr>
<td class="installb" style="width:33%">
PHP Version:
</td>
<td class="installb" style="width:33%">
<?php
echo phpversion();
?>
</td>
<td class="installb" style="width:33%">
<?php
$verreq = str_replace(".","", "4.1.0");
$server = str_replace(".","", phpversion());
if($server <= $verreq){
	echo "<span class=\"installe\">* Fail *</span>";
	$error[0] = TRUE;
}else{
	echo "<span class=\"installh\">* Pass *</span>";
}

echo "</td>
</tr>
<tr>
<td class=\"installb\" style=\"width:33%\">
mySQL Version:
</td>
<td class=\"installb\" style=\"width:33%\">".
@mysql_get_server_info()."
</td>
<td class=\"installb\" style=\"width:33%\">";

if(!@mysql_get_server_info()){
	echo "<span class=\"installe\">* Warning *</span>";
	$error[1] = TRUE;
}else{
	echo "<span class=\"installh\">* Pass *</span>";
}
echo "</td>
</tr>
<tr>
<td class=\"installb\" style=\"width:66%\" colspan=\"2\">
config.php file permissions:
</td>
<td class=\"installb\" style=\"width:33%\">";
$fp = @fopen("config.php","w");
if(!@fwrite($fp, "Test")){
	echo "<span class=\"installe\">* Fail *</span>";
	$error[2] = TRUE;
}else{
	echo "<span class=\"installh\">* Pass *</span>";
}
echo "</td>
</tr>
<tr>
<td class=\"installb\" style=\"width:66%\" colspan=\"2\">
backend file permissions:
</td>
<td class=\"installb\" style=\"width:33%\">";
$fp = @fopen("backend/news.txt","w");
if(!@fwrite($fp, "New installation")){
	echo "<span class=\"installe\">* Fail *</span>";
	$error[3] = TRUE;
}else{
	echo "<span class=\"installh\">* Pass *</span>";
}

echo "</td>
</tr>
</table>
<br />";


if($error[0] || $error[1]){
	echo "<table style=\"width:50%\" class=\"fborder\">
	<tr>
	<td class=\"forumheader\" style=\"text-align:center\"><span class=\"installh\">Error!</span></td>
	</tr>
	<tr>
	<td class=\"installb\" style=\"width:33%; text-align:center\">";

	if($error[0]){
		echo "<b>You are running a version of PHP that is not compatible with e107 (e107 requires at least version 4.1.0).</b><br />If you are using a local server on your computer you will need to upgrade your version of PHP to continue, please see <a href=\"http://php.net\">php.net</a> for instructions. If you are attempting to install e107 on a hosted server you will need to contact the server administrators and ask them  to upgrade PHP for you.<br />Please rerun this script after upgrading your PHP version.<br />";
		echo "</td></tr></table>
		</body>
		</html>";
		exit;
	}
	
	if($error[1]){
		echo "e107 was unable to determine the mySQL version number, this could mean that mySQL is not installed or not currently running. If the next step of the installation fails you will need to check your mySQL status.";
		echo "</td></tr></table><br />";
	}
}

if($error[2] || $error[3]){
	echo "<table style=\"width:50%\" class=\"fborder\">
	<tr>
	<td class=\"forumheader\" style=\"text-align:center\"><span class=\"installh\">Non Fatal Error</span></td>
	</tr>
	<tr>
	<td class=\"installb\" style=\"width:33%; text-align:center\"><br />";

	if($error[2]){
		echo "The file permissions of config.php in the root e107 directory is not set correctly - please CHMOD the file to 666 or 777 and click on the retest button.<br /><br />";
	}

	if($error[3]){
		echo "The file permissions of the two files in the /backend directory (news.txt and news.xml) are not set correctly - please CHMOD the files to 666 or 777 and click on the retest button.<br /><br />";
	}

	echo "</td></tr><tr><td class=\"forumheader\" style=\"text-align:center\">
	<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?\">
	<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Retest\" />
	</form>
	</td></tr></table>
	</body>
	</html>";
	exit;
}

// server tests passed - continue

echo "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<table style=\"width:50%\" class=\"fborder\">
<tr>
<td class=\"forumheader\" colspan=\"3\" style=\"text-align:center\">
<span class=\"installh\">mySQL details</span><br />
</td>
</tr>
<tr>
<td style=\"width:33%\" class=\"installb\">mySQL Server:</td>
<td style=\"width:66%\" class=\"installb\">
<input class=\"tbox\" type=\"text\" name=\"mysql_server\" size=\"60\" value=\"".$_POST['mysql_server']."\" maxlength=\"100\" />
</td>
</tr>
<tr>
<td style=\"width:33%\" class=\"installb\">mySQL Username:</td>
<td style=\"width:66%\" class=\"installb\">
<input class=\"tbox\" type=\"text\" name=\"mysql_name\" size=\"60\" value=\"".$_POST['mysql_name']."\" maxlength=\"100\" />
</td>
</tr>
<tr> 
<td style=\"width:33%\" class=\"installb\">mySQL Password: </td>
<td style=\"width:66%\" class=\"installb\">
<input class=\"tbox\" type=\"text\" name=\"mysql_password\" size=\"60\" value=\"".$_POST['mysql_password']."\" maxlength=\"100\" />
</td>
</tr>\n
<tr> 
<td style=\"width:33%\" class=\"installb\">mySQL Database: </td>
<td style=\"width:66%\" class=\"installb\">
<input class=\"tbox\" type=\"text\" name=\"mysql_db\" size=\"60\" value=\"".$_POST['mysql_db']."\" maxlength=\"100\" />
</td>

</tr>\n
<tr> 
<td style=\"width:33%\" class=\"installb\">Table prefix: </td>
<td style=\"width:66%\" class=\"installb\">
<input class=\"tbox\" type=\"text\" name=\"mysql_prefix\" size=\"60\" value=\"".$_POST['mysql_prefix']."\"  maxlength=\"100\" />
</td>
</tr>
</table>

<br />
<table style=\"width:50%\" class=\"fborder\">
<tr>
<td class=\"forumheader\" colspan=\"3\" style=\"text-align:center\">
<span class=\"installh\">Main Site Administrator</span><br />
</td>
</tr>

<tr>
<td style=\"width:33%\" class=\"installb\">Admin Name:</td>
<td style=\"width:66%\" class=\"installb\">
<input class=\"tbox\" type=\"text\" name=\"admin_name\" size=\"60\" value=\"".$_POST['admin_name']."\" maxlength=\"100\" />
</td>
</tr>
<tr>
<td style=\"width:33%\" class=\"installb\">Admin Password:</td>
<td style=\"width:66%\" class=\"installb\">
<input class=\"tbox\" type=\"password\" name=\"admin_password1\" size=\"60\" value=\"\" maxlength=\"100\" />
</td>
</tr>
<tr> 
<td style=\"width:33%\" class=\"installb\">Re-type password: </td>
<td style=\"width:66%\" class=\"installb\">
<input class=\"tbox\" type=\"password\" name=\"admin_password2\" size=\"60\" value=\"\" maxlength=\"100\" />
</td>
</tr>\n
<tr> 
<td style=\"width:33%\" class=\"installb\">Admin Email Address: </td>
<td style=\"width:66%\" class=\"installb\">
<input class=\"tbox\" type=\"text\" name=\"admin_email\" size=\"60\" value=\"".$_POST['admin_email']."\" maxlength=\"100\" />
</td>
</tr>
</table>

<br />
<table style=\"width:50%\" class=\"fborder\">
<tr>
<td style=\"text-align:center\" class=\"forumheader\">
<input class=\"button\" type=\"submit\" name=\"stage_2\" value=\"Continue\" />
<input type=\"hidden\" name=\"stage\" value=\"2\">
</form>
</td>
</tr>

</table>
</td>
</tr>
</table>
</body>
</html>";
}

if($_POST['stage'] == 2){

	echo "<br /></td></tr></table><br />";

	if($_POST['mysql_server'] == "" || $_POST['mysql_name'] == "" || $_POST['mysql_db'] == "" || $_POST['admin_name'] == "" || $_POST['admin_password1'] == "" || $_POST['admin_password2'] == "" || $_POST['admin_email'] == ""){
		$error = "<br />You left required fields blank";
	}
	if($_POST['admin_password1'] != $_POST['admin_password2']){
		$error = "<br />The two passwords you entered do not match";
	}
	if(!preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $_POST['admin_email'])){
		$error = "<br />That doesn't appear to be a valid email address";
		$_POST['admin_email'] = "";
	}

	if($error != ""){
		echo "<table style=\"width:50%\" class=\"fborder\">
		<tr>
		<td style=\"text-align:center\" class=\"installb\">
		",$error." - please re-enter your information.<br /><br />
		</td></tr>
		<tr>
		<td style=\"text-align:center\" class=\"forumheader\">
		<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
		<input class=\"button\" type=\"submit\" name=\"submit\" value=\"<- Back to last page\" />
		<input type=\"hidden\" name=\"stage\" value=\"1\">
		<input type=\"hidden\" name=\"mysql_server\" value=\"".$_POST['mysql_server'] ."\">
			<input type=\"hidden\" name=\"mysql_name\" value=\"".$_POST['mysql_name'] ."\">
			<input type=\"hidden\" name=\"mysql_db\" value=\"".$_POST['mysql_db'] ."\">
			<input type=\"hidden\" name=\"mysql_password\" value=\"".$_POST['mysql_password'] ."\">
			<input type=\"hidden\" name=\"mysql_prefix\" value=\"".$_POST['mysql_prefix'] ."\">
			<input type=\"hidden\" name=\"admin_name\" value=\"".$_POST['admin_name'] ."\">
			<input type=\"hidden\" name=\"admin_email\" value=\"".$_POST['admin_email'] ."\">
		</form>
		</td></tr></table></body></html>";
		exit;
	}else{

		// no errors - continue


		echo "<table style=\"width:50%\" class=\"fborder\">
		<tr>
		<td class=\"forumheader\" colspan=\"3\" style=\"text-align:center\">
		<span class=\"installh\">Testing mySQL connection</span><br />
		</td>
		</tr>

		<tr>
		<td style=\"width:50%; text-align:center\" class=\"installb\">Connection to mySQL established?:.</td>
		<td style=\"width:50%; text-align:center\" class=\"installb\">";
		
		if(!@mysql_connect($_POST['mysql_server'], $_POST['mysql_name'], $_POST['mysql_password'])){
			echo "<span class=\"installe\">* Fail *</span>
			</td>
			</tr>
			<tr>
			<td style=\"text-align:center\" class=\"installb\" colspan=\"2\"><br />Unable to connect to mySQL server <i>'".$_POST['mysql_server']."</i>' using  username <i>'".$_POST['mysql_name']."'</i> and password <i>'".$_POST['mysql_password']."'</i> - please return to previous page and verify you have entered the correct details.<br /><br />
			</td>
			</tr>
			<tr>
			<td style=\"text-align:center\" class=\"forumheader\" colspan=\"2\">
			<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
			<input class=\"button\" type=\"submit\" name=\"submit\" value=\"<- Back to last page\" />
			<input type=\"hidden\" name=\"stage\" value=\"1\">
			<input type=\"hidden\" name=\"mysql_server\" value=\"".$_POST['mysql_server'] ."\">
			<input type=\"hidden\" name=\"mysql_name\" value=\"".$_POST['mysql_name'] ."\">
			<input type=\"hidden\" name=\"mysql_db\" value=\"".$_POST['mysql_db'] ."\">
			<input type=\"hidden\" name=\"mysql_password\" value=\"".$_POST['mysql_password'] ."\">
			<input type=\"hidden\" name=\"mysql_prefix\" value=\"".$_POST['mysql_prefix'] ."\">
			<input type=\"hidden\" name=\"admin_name\" value=\"".$_POST['admin_name'] ."\">
			<input type=\"hidden\" name=\"admin_email\" value=\"".$_POST['admin_email'] ."\">
			</form>
			</td></tr></table></body></html>";
			exit;
		}else{
			echo "<span class=\"installh\">* Pass *</span>
			</td>
			</tr>
			<tr>
			<td style=\"width:50%; text-align:center\" class=\"installb\">Database <i>'".$_POST['mysql_db']."'</i> verified?:</i></td>
			<td style=\"width:50%; text-align:center\" class=\"installb\">";

			if(!@mysql_select_db($_POST['mysql_db'])){
				echo "<span class=\"installe\">* Fail *</span>
				</td>
				</tr>
				<tr>
				<td style=\"text-align:center\" class=\"installb\" colspan=\"2\"><br />Unable to verify database <i>'".$_POST['mysql_db']."'</i> - please return to previous page and verify you have entered the correct details.<br />
				</td></tr>
				<tr>
				<td style=\"text-align:center\" class=\"forumheader\" colspan=\"2\">
				<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
				<input class=\"button\" type=\"submit\" name=\"submit\" value=\"<- Back to last page\" />
				<input type=\"hidden\" name=\"stage\" value=\"1\">
				<input type=\"hidden\" name=\"mysql_server\" value=\"".$_POST['mysql_server'] ."\">
				<input type=\"hidden\" name=\"mysql_name\" value=\"".$_POST['mysql_name'] ."\">
				<input type=\"hidden\" name=\"mysql_db\" value=\"".$_POST['mysql_db'] ."\">
				<input type=\"hidden\" name=\"mysql_password\" value=\"".$_POST['mysql_password'] ."\">
				<input type=\"hidden\" name=\"mysql_prefix\" value=\"".$_POST['mysql_prefix'] ."\">
				<input type=\"hidden\" name=\"admin_name\" value=\"".$_POST['admin_name'] ."\">
				<input type=\"hidden\" name=\"admin_email\" value=\"".$_POST['admin_email'] ."\">
				</form>
				</td></tr></table></body></html>";
				exit;
			}else{
				echo "<span class=\"installh\">* Pass *</span>
				</td>
				</tr></table>";

				// mySQL connection and db verified - continue


				echo "<br /><table style=\"width:50%\" class=\"fborder\">
				<tr>
				<td class=\"forumheader\" colspan=\"3\" style=\"text-align:center\">
				<span class=\"installh\">Save Settings</span><br />
				</td>
				</tr>

				<tr>
				<td style=\"width:50%; text-align:center\" class=\"installb\">Settings saved to config.php?:</td>
				<td style=\"width:50%; text-align:center\" class=\"installb\">";

				$fpath = str_replace(strrchr($_SERVER['PHP_SELF'], "/"), "", $_SERVER['PHP_SELF'])."/";
				$data = chr(60)."?php\n".
chr(47)."*\n+---------------------------------------------------------------+\n|	e107 website system\n|	/config.php\n|\n|	©Steve Dunstan 2001-2002\n|	http://e107.org\n|	jalist@e107.org\n|\n|	Released under the terms and conditions of the\n|	GNU General Public License (http://gnu.org).\n+---------------------------------------------------------------+\n\nThis file has been generated by the installation script.\n\n*".
chr(47)."\n\n".
chr(36)."mySQLserver = ".chr(34).$_POST['mysql_server'].chr(34).";\n".
chr(36)."mySQLuser = ".chr(34).$_POST['mysql_name'].chr(34).";\n".
chr(36)."mySQLpassword = ".chr(34).$_POST['mysql_password'].chr(34).";\n".
chr(36)."mySQLdefaultdb = ".chr(34).$_POST['mysql_db'].chr(34).";\n".
chr(36)."mySQLprefix = ".chr(34).$_POST['mysql_prefix'].chr(34).";\n\n".
chr(47).chr(47)."define(".chr(34)."MQ".chr(34).", TRUE);\ndefine(".chr(34)."e_HTTP".chr(34).", ".chr(34).$fpath.chr(34).");\n\n?".chr(62);

				$fp = @fopen("config.php","w");
				if(!@fwrite($fp, $data)){
					echo "<span class=\"installe\">* Fail *</span>
					</td>
					</tr>
					<tr>
					<td style=\"text-align:center\" class=\"installb\" colspan=\"2\"><br />Unable to successfully write settings to config.php - please check that you have the correct permissions set (CHMOD 666 or 777)<br />
					</td></tr>
					<tr>
					<td style=\"text-align:center\" class=\"forumheader\" colspan=\"2\">
					<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
					<input class=\"button\" type=\"submit\" name=\"submit\" value=\"<- Back to last page\" />
					<input type=\"hidden\" name=\"stage\" value=\"1\">
					<input type=\"hidden\" name=\"mysql_server\" value=\"".$_POST['mysql_server'] ."\">
					<input type=\"hidden\" name=\"mysql_name\" value=\"".$_POST['mysql_name'] ."\">
					<input type=\"hidden\" name=\"mysql_db\" value=\"".$_POST['mysql_db'] ."\">
					<input type=\"hidden\" name=\"mysql_password\" value=\"".$_POST['mysql_password'] ."\">
					<input type=\"hidden\" name=\"mysql_prefix\" value=\"".$_POST['mysql_prefix'] ."\">
					<input type=\"hidden\" name=\"admin_name\" value=\"".$_POST['admin_name'] ."\">
					<input type=\"hidden\" name=\"admin_email\" value=\"".$_POST['admin_email'] ."\">
					</form>
					</td></tr></table></body></html>";
					exit;
				}else{
					fclose($fp);
					echo "<span class=\"installh\">* Pass *</span>
					</td>
					</tr></table>";


					// config.php written - continue

					echo "<br /><table style=\"width:50%\" class=\"fborder\">
					<tr>
					<td class=\"forumheader\" colspan=\"3\" style=\"text-align:center\">
					<span class=\"installh\">Creating mySQL tables</span><br />
					</td>
					</tr>

					<tr>
					<td style=\"text-align:center\" class=\"installb\">";
					$error = setuptables($_POST['mysql_server'], $_POST['mysql_name'], $_POST['mysql_password'], $_POST['mysql_db'], $_POST['mysql_prefix'], $_POST['admin_name'], $_POST['admin_email']);

					if($error != ""){
						echo "<span class=\"installe\">* Fail *</span>
						</td>
						</tr>
						<tr>
						<td style=\"text-align:center\" class=\"installb\">
						<br />".$error."<br />You may need to delete the tables involved and recreate them by re-running this script.<br />If you have previously run this install script and created the database tables you may be OK to continue ...<br /><br />";
					}else{
						echo "<span class=\"installh\">* Pass *</span>
						</td>
						</tr>
						<tr>
						<td style=\"text-align:center\" class=\"installb\">
						<br />All tables successfully created and propagated.<br /><br />";
					}
					// tables created - continue

					echo "</td></tr></table><br /><table style=\"width:50%\" class=\"fborder\">
					<tr>
					<td class=\"forumheader\" style=\"text-align:center\">
					<span class=\"installh\">Setting up Main Site Administrator</span><br />
					</td>
					</tr>

					<tr>
					<td style=\"text-align:center\" class=\"installb\">";

					@mysql_connect($_POST['mysql_server'], $_POST['mysql_name'], $_POST['mysql_password']);
					@mysql_select_db($_POST['mysql_db']);

					$time = time();
					$userp = "1, '".$_POST['admin_name']."', '".md5($_POST['admin_password1'])."', '', '".$_POST['admin_email']."', '', '', '', '', '', '', '', '', '', 0, ".$time.", 0, 0, 0, 0, 0, 0, '$ip', 0, '', '', '', 0, 1, '', '', '0', '', ".$time;

					if(!mysql_query("INSERT INTO ".$_POST['mysql_prefix']."user VALUES ($userp)" )){
						echo "<span class=\"installe\">* Fail *</span>
						</td>
						</tr>
						<tr>
						<td style=\"text-align:center\" class=\"installb\"><br />Unable to enter admin details into database, it's likely that some or all of the database tables were not created in the previous stage of installation. You may have to delete the tables or database involved and try to rerun the install process.<br />Script halted.<br /><br /></td></tr></table><br /><br /></body></html>";
						exit;
					}

					echo "<span class=\"installh\">* Pass *</span>
					</td>
					</tr>
					<tr>
					<td style=\"text-align:center\" class=\"installb\"><br />
					The main site administrator has been set up and entered into the database - please write these down in a safe place<br /><br />
					<b>Administrator name: ".$_POST['admin_name'].", Administrator password: ".$_POST['admin_password1']."</b><br /><br />
					</td></tr>
					</table><br />

					<table style=\"width:50%\" class=\"fborder\">
					<tr>
					<td class=\"forumheader\" colspan=\"3\" style=\"text-align:center\">
					<span class=\"installh\">Installation Complete!</span><br />
					</td>
					</tr>
					<tr>
					<td style=\"text-align:center\" class=\"installb\"><br />
					
					<b>For security reasons you should now set the file permissions on the config.php file in your root e107 directory to back to 644.<br />
					Also please delete /install.php from your server after you have clicked the button below.</b><br /><br />

					<form method=\"post\" action=\"index.php\">
					<input class=\"button\" type=\"submit\" name=\"frontpage\" value=\"Click here to go to your new website!\" />
					</form>
					<br />
					</td></tr></table><br /><br /></body></html>";
				}
			}
		}
	}
}

function setuptables($server, $user, $pass, $db, $mySQLprefix, $mainsiteadmin, $mainsiteadminemail){

$banlist_table = "CREATE TABLE ".$mySQLprefix."banlist (
  banlist_ip varchar(15) NOT NULL default '',
  banlist_admin smallint(5) unsigned NOT NULL default '0',
  banlist_reason tinytext NOT NULL,
  PRIMARY KEY  (banlist_ip)
) TYPE=MyISAM;";
if(!mysql_query($banlist_table)){	$error .= "There was a problem creating the <b>banlist</b> mySQL table ...<br />"; }

$chatbox_table = "CREATE TABLE ".$mySQLprefix."chatbox (
  cb_id int(10) unsigned NOT NULL auto_increment,
  cb_nick varchar(20) NOT NULL default '',
  cb_message text NOT NULL,
  cb_datestamp int(10) unsigned NOT NULL default '0',
  cb_blocked tinyint(3) unsigned NOT NULL default '0',
  cb_ip varchar(15) NOT NULL default '',
  PRIMARY KEY  (cb_id)
) TYPE=MyISAM;";
if(!mysql_query($chatbox_table)){	$error .= "There was a problem creating the <b>chatbox</b> mySQL table ...<br />"; }


$comments_table = "CREATE TABLE ".$mySQLprefix."comments (
  comment_id int(10) unsigned NOT NULL auto_increment,
  comment_item_id int(10) unsigned NOT NULL default '0',
  comment_author varchar(100) NOT NULL default '',
  comment_author_email varchar(200) NOT NULL default '',
  comment_datestamp int(10) unsigned NOT NULL default '0',
  comment_comment text NOT NULL,
  comment_blocked tinyint(3) unsigned NOT NULL default '0',
  comment_ip varchar(20) NOT NULL default '',
  comment_type tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (comment_id)
) TYPE=MyISAM;";
if(!mysql_query($comments_table)){	$error .= "There was a problem creating the <b>comments</b> mySQL table ...<br />"; }


$content_table = "CREATE TABLE ".$mySQLprefix."content (
 content_id int(10) unsigned NOT NULL auto_increment,
  content_heading tinytext NOT NULL,
  content_subheading tinytext NOT NULL,
  content_content text NOT NULL,
  content_page tinyint(3) unsigned NOT NULL default '0',
  content_datestamp int(10) unsigned NOT NULL default '0',
  content_author smallint(5) unsigned NOT NULL default '0',
  content_comment tinyint(3) unsigned NOT NULL default '0',
  content_summary text NOT NULL,
  content_type tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (content_id)
) TYPE=MyISAM;";
if(!mysql_query($content_table)){	$error .= "There was a problem creating the <b>content</b> mySQL table ...<br />"; }


$core_table = "CREATE TABLE ".$mySQLprefix."core (
  e107_name varchar(20) NOT NULL default '',
  e107_value text NOT NULL,
  PRIMARY KEY  (e107_name)
) TYPE=MyISAM;";
if(!mysql_query($core_table)){	$error .= "There was a problem creating the <b>core</b> mySQL table ...<br />"; }


$forum_table = "CREATE TABLE ".$mySQLprefix."forum (
  forum_id int(10) unsigned NOT NULL auto_increment,
  forum_name varchar(250) NOT NULL default '',
  forum_description text NOT NULL,
  forum_parent int(10) unsigned NOT NULL default '0',
  forum_datestamp int(10) unsigned NOT NULL default '0',
  forum_active tinyint(3) unsigned NOT NULL default '0',
  forum_moderators text NOT NULL,
  forum_threads int(10) unsigned NOT NULL default '0',
  forum_replies int(10) unsigned NOT NULL default '0',
  forum_lastpost varchar(200) NOT NULL default '',
  forum_class varchar(100) NOT NULL default '',
  PRIMARY KEY  (forum_id)
) TYPE=MyISAM;";
if(!mysql_query($forum_table)){	$error .= "There was a problem creating the <b>forum</b> mySQL table ...<br />"; }


$forum_t_table = "CREATE TABLE ".$mySQLprefix."forum_t (
  thread_id int(10) unsigned NOT NULL auto_increment,
  thread_name varchar(250) NOT NULL default '',
  thread_thread text NOT NULL,
  thread_forum_id int(10) unsigned NOT NULL default '0',
  thread_datestamp int(10) unsigned NOT NULL default '0',
  thread_parent int(10) unsigned NOT NULL default '0',
  thread_user varchar(100) NOT NULL default '',
  thread_views int(10) unsigned NOT NULL default '0',
  thread_active tinyint(3) unsigned NOT NULL default '0',
  thread_lastpost int(10) unsigned NOT NULL default '0',
  thread_s tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (thread_id)
) TYPE=MyISAM;";
if(!mysql_query($forum_t_table)){	$error .= "There was a problem creating the <b>forum_t</b> mySQL table ...<br />"; }


$flood_table = "CREATE TABLE ".$mySQLprefix."flood (
  flood_url text NOT NULL,
  flood_time int(10) unsigned NOT NULL default '0'
) TYPE=MyISAM;";
if(!mysql_query($flood_table)){	$error .= "There was a problem creating the <b>flood</b> mySQL table ...<br />"; }


$headlines_table = "CREATE TABLE ".$mySQLprefix."headlines (
  headline_id int(10) unsigned NOT NULL auto_increment,
  headline_url varchar(150) NOT NULL default '',
  headline_data text NOT NULL,
  headline_timestamp int(10) unsigned NOT NULL default '0',
  headline_description tinyint(3) unsigned NOT NULL default '0',
  headline_webmaster tinyint(3) unsigned NOT NULL default '0',
  headline_copyright tinyint(3) unsigned NOT NULL default '0',
  headline_tagline tinyint(3) unsigned NOT NULL default '0',
  headline_image varchar(100) NOT NULL default '',
  headline_active tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (headline_id)
) TYPE=MyISAM;";
if(!mysql_query($headlines_table)){	$error .= "There was a problem creating the <b>headlines_table</b> mySQL table ...<br />"; }


$link_category_table = "CREATE TABLE ".$mySQLprefix."link_category (
  link_category_id int(10) unsigned NOT NULL auto_increment,
  link_category_name varchar(100) NOT NULL default '',
  link_category_description varchar(250) NOT NULL default '',
  PRIMARY KEY  (link_category_id)
) TYPE=MyISAM;";
if(!mysql_query($link_category_table)){	$error .= "There was a problem creating the <b>link_category</b> mySQL table ...<br />"; }


$link_table = "CREATE TABLE ".$mySQLprefix."links (
 link_id int(10) unsigned NOT NULL auto_increment,
  link_name varchar(100) NOT NULL default '',
  link_url varchar(200) NOT NULL default '',
  link_description text NOT NULL,
  link_button varchar(100) NOT NULL default '',
  link_category tinyint(3) unsigned NOT NULL default '0',
  link_order int(10) unsigned NOT NULL default '0',
  link_refer int(10) unsigned NOT NULL default '0',
  link_open tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (link_id)
) TYPE=MyISAM;";
 if(!mysql_query($link_table)){	$error .= "There was a problem creating the <b>link</b> mySQL table ...<br />"; }


$menus_table = "CREATE TABLE ".$mySQLprefix."menus (
 menu_id int(10) unsigned NOT NULL auto_increment,
  menu_name varchar(100) NOT NULL default '',
  menu_location tinyint(3) unsigned NOT NULL default '0',
  menu_order tinyint(3) unsigned NOT NULL default '0',
  menu_class tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (menu_id)
) TYPE=MyISAM;";
if(!mysql_query($menus_table)){	$error .= "There was a problem creating the <b>menus</b> mySQL table ...<br />"; }


$news_table = "CREATE TABLE ".$mySQLprefix."news (
  news_id int(10) unsigned NOT NULL auto_increment,
  news_title varchar(200) NOT NULL default '',
  news_body text NOT NULL,
  news_extended text NOT NULL,
  news_datestamp int(10) unsigned NOT NULL default '0',
  news_author int(10) unsigned NOT NULL default '0',
  news_source varchar(200) NOT NULL default '',
  news_url varchar(200) NOT NULL default '',
  news_category tinyint(3) unsigned NOT NULL default '0',
  news_allow_comments tinyint(3) unsigned NOT NULL default '0',
  news_start int(10) unsigned NOT NULL default '0',
  news_end int(10) unsigned NOT NULL default '0',
  news_active tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (news_id)
) TYPE=MyISAM;";
if(!mysql_query($news_table)){	$error .= "There was a problem creating the <b>news</b> mySQL table ...<br />"; }


$news_catagory_table = "CREATE TABLE ".$mySQLprefix."news_category (
  category_id int(10) unsigned NOT NULL auto_increment,
  category_name varchar(200) NOT NULL default '',
  category_icon varchar(250) NOT NULL default '',
  PRIMARY KEY  (category_id)
) TYPE=MyISAM;";
if(!mysql_query($news_catagory_table)){	$error .= "There was a problem creating the <b>news_category</b> mySQL table ...<br />"; }


$online_table = "CREATE TABLE ".$mySQLprefix."online (
  online_timestamp int(10) unsigned NOT NULL default '0',
  online_flag tinyint(3) unsigned NOT NULL default '0',
  online_user_id  varchar(100) NOT NULL default '',
  online_ip varchar(15) NOT NULL default '',
  online_location varchar(100) NOT NULL default ''
) TYPE=MyISAM;";
if(!mysql_query($online_table)){	$error .= "There was a problem creating the <b>online</b> mySQL table ...<br />"; }


$poll_table = "CREATE TABLE ".$mySQLprefix."poll (
  poll_id int(10) unsigned NOT NULL auto_increment,
  poll_datestamp int(10) unsigned NOT NULL default '0',
  poll_end_datestamp int(10) unsigned NOT NULL default '0',
  poll_admin_id tinyint(3) unsigned NOT NULL default '0',
  poll_title varchar(250) NOT NULL default '',
  poll_option_1 varchar(250) NOT NULL default '',
  poll_option_2 varchar(250) NOT NULL default '',
  poll_option_3 varchar(250) NOT NULL default '',
  poll_option_4 varchar(250) NOT NULL default '',
  poll_option_5 varchar(250) NOT NULL default '',
  poll_option_6 varchar(250) NOT NULL default '',
  poll_option_7 varchar(250) NOT NULL default '',
  poll_option_8 varchar(250) NOT NULL default '',
  poll_option_9 varchar(250) NOT NULL default '',
  poll_option_10 varchar(250) NOT NULL default '',
  poll_votes_1 int(10) unsigned NOT NULL default '0',
  poll_votes_2 int(10) unsigned NOT NULL default '0',
  poll_votes_3 int(10) unsigned NOT NULL default '0',
  poll_votes_4 int(10) unsigned NOT NULL default '0',
  poll_votes_5 int(10) unsigned NOT NULL default '0',
  poll_votes_6 int(10) unsigned NOT NULL default '0',
  poll_votes_7 int(10) unsigned NOT NULL default '0',
  poll_votes_8 int(10) unsigned NOT NULL default '0',
  poll_votes_9 int(10) unsigned NOT NULL default '0',
  poll_votes_10 int(10) unsigned NOT NULL default '0',
  poll_ip text NOT NULL,
  poll_active tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (poll_id)
) TYPE=MyISAM;";
if(!mysql_query($poll_table)){	$error .= "There was a problem creating the <b>poll</b> mySQL table ...<br />"; }


$stat_counter_table = "CREATE TABLE ".$mySQLprefix."stat_counter (
 counter_date date NOT NULL default '0000-00-00',
  counter_url varchar(100) NOT NULL default '',
  counter_unique int(10) unsigned NOT NULL default '0',
  counter_total int(10) unsigned NOT NULL default '0',
  counter_ip text NOT NULL,
  counter_today_total int(10) unsigned NOT NULL default '0',
  counter_today_unique int(10) unsigned NOT NULL default '0'
) TYPE=MyISAM;";
if(!mysql_query($stat_counter_table)){	$error .= "There was a problem creating the <b>stat_counter</b> mySQL table ...<br />"; }


$stat_info_table = "CREATE TABLE ".$mySQLprefix."stat_info (
  info_name text NOT NULL default '',
  info_count int(10) unsigned NOT NULL default '0',
  info_type tinyint(3) unsigned NOT NULL default '0'
) TYPE=MyISAM;";
if(!mysql_query($stat_info_table)){	$error .= "There was a problem creating the <b>stat_info</b> mySQL table ...<br />"; }


$stat_last_table = "CREATE TABLE ".$mySQLprefix."stat_last (
  stat_last_date int(11) unsigned NOT NULL default '0',
  stat_last_info text NOT NULL
) TYPE=MyISAM;";
if(!mysql_query($stat_last_table)){	$error .= "There was a problem creating the <b>stat_last</b> mySQL table ...<br />"; }


$submitnews_table = "CREATE TABLE ".$mySQLprefix."submitnews (
  submitnews_id int(10) unsigned NOT NULL auto_increment,
  submitnews_name varchar(100) NOT NULL default '',
  submitnews_email varchar(100) NOT NULL default '',
  submitnews_title varchar(200) NOT NULL default '',
  submitnews_item text NOT NULL,
  submitnews_datestamp int(10) unsigned NOT NULL default '0',
  submitnews_ip varchar(15) NOT NULL default '',
  submitnews_auth tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (submitnews_id)
) TYPE=MyISAM;";
if(!mysql_query($submitnews_table)){	$error .= "There was a problem creating the <b>submit_news</b> mySQL table ...<br />"; }

$tmp_table = "CREATE TABLE ".$mySQLprefix."tmp (
  tmp_ip varchar(20) NOT NULL default '',
  tmp_time int(10) unsigned NOT NULL default '0',
  tmp_info text NOT NULL
) TYPE=MyISAM;";
if(!mysql_query($tmp_table)){	$error .= "There was a problem creating the <b>tmp</b> mySQL table ...<br />"; }

$user_table = "CREATE TABLE ".$mySQLprefix."user (
  user_id int(10) unsigned NOT NULL auto_increment,
  user_name varchar(100) NOT NULL default '',
  user_password varchar(32) NOT NULL default '',
  user_sess varchar(32) NOT NULL default '',
  user_email varchar(100) NOT NULL default '',
  user_homepage varchar(150) NOT NULL default '',
  user_icq varchar(10) NOT NULL default '',
  user_aim varchar(100) NOT NULL default '',
  user_msn varchar(100) NOT NULL default '',
  user_location varchar(150) NOT NULL default '',
  user_birthday date NOT NULL default '0000-00-00',
  user_signature text NOT NULL,
  user_image varchar(100) NOT NULL default '',
  user_timezone char(3) NOT NULL default '',
  user_hideemail tinyint(3) unsigned NOT NULL default '0',
  user_join int(10) unsigned NOT NULL default '0',
  user_lastvisit int(10) unsigned NOT NULL default '0',
  user_currentvisit int(10) unsigned NOT NULL default '0',
  user_lastpost int(10) unsigned NOT NULL default '0',
  user_chats int(10) unsigned NOT NULL default '0',
  user_comments int(10) unsigned NOT NULL default '0',
  user_forums int(10) unsigned NOT NULL default '0',
  user_ip varchar(20) NOT NULL default '',
  user_ban tinyint(3) unsigned NOT NULL default '0',
  user_prefs text NOT NULL,
  user_new text NOT NULL,
  user_viewed text NOT NULL,
  user_visits int(10) unsigned NOT NULL default '0',
  user_admin tinyint(3) unsigned NOT NULL default '0',
  user_login varchar(100) NOT NULL default '',
  user_class text NOT NULL,
  user_perms text NOT NULL,
  user_realm text NOT NULL,
  user_pwchange int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (user_id),
  UNIQUE KEY user_name (user_name)
) TYPE=MyISAM;";
if(!mysql_query($user_table)){	$error .= "There was a problem creating the <b>user</b> mySQL table ...<br />"; }


$wmessage_table = "CREATE TABLE ".$mySQLprefix."wmessage (
  wm_id tinyint(3) unsigned NOT NULL default '0',
  wm_text text NOT NULL,
  wm_active tinyint(3) unsigned NOT NULL default '0'
) TYPE=MyISAM;";
if(!mysql_query($wmessage_table)){	$error .= "There was a problem creating the <b>wmessage</b> mySQL table ...<br />"; }


$userclass_classes_table = "CREATE TABLE ".$mySQLprefix."userclass_classes (
  userclass_id int(10) unsigned NOT NULL auto_increment,
  userclass_name varchar(100) NOT NULL default '',
  userclass_description varchar(250) NOT NULL default '',
  PRIMARY KEY  (userclass_id)
) TYPE=MyISAM;";
if(!mysql_query($userclass_classes_table)){	$error .= "There was a problem creating the <b>userclass_classes</b> mySQL table ...<br />"; }

$welcome_message = addslashes("e107 is what is commonly known as a CMS, or content management system. It gives you a completely interactive website without the need to learn HTML, PHP etc.<br />It has been in developement since July 2002 and is constantly being updated and tweaked for better performance and stability.
Some of the features of e107 are ...<ul><li>Secure administration backend allows you to moderate all aspects of your website, post news items etc</li><li>News item commenting, chatbox, forums, poll etc make your site totally interactive to visitors</li><li>Totally themeable interface, change every aspect of how your site looks</li><li>More themes and plugins available to download, dynamic recognition of new addons means extremely easy installation</li><li>Allow users to register as members on your site, and allow comments from members only or anonymous users</li></ul>Your admin section is located at <a href=\"admin/admin.php\">/admin/admin.php</a>, click to go there now. You will have to login using the name and password you entered during the installation process.
If you would like to see something added to the core, or coded as a plugin please visit <a href=\"http://e107.org\">e107.org</a> and leave a message on the Requests forum, or alternatively email the developer jalist (Steve Dunstan) <a href=\"mailto:jalist@e107.org\">here</a>.
If you have created a theme or plugin for e107 please consider sharing it with the rest of the community - send it to <a href=\"mailto:jalist@e107.org\">jalist</a> who will upload it to the main e107 site at <a href=\"http://e107.org\">e107.org</a>.
Thankyou for trying e107, and have fun with your new website!
(You can delete this message from your admin section.)");

$datestamp = time();

mysql_query("INSERT INTO ".$mySQLprefix."content VALUES (0, '$article_heading', '$article_subheading', '$article', '$datestamp', 0, 0) ");
mysql_query("INSERT INTO ".$mySQLprefix."news VALUES (0, 'Welcome to e107', '$welcome_message', '', '$datestamp', '0', '', '', '1', 1, 0, 0, 0) ");
mysql_query("INSERT INTO ".$mySQLprefix."news_category VALUES (0, 'Misc', 'images/bullet1.gif') ");
mysql_query("INSERT INTO ".$mySQLprefix."poll VALUES (0, '$datestamp', 0, 1, 'So what do you think of e107?', 'I\'m not impressed', 'It\'s not bad but other content management systems are a lot better', 'It\'s good', 'I love it!', 'Grah I hate polls', 'What\'s e107 anyway?', '', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 1) ");
mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Home', 'index.php', '', '', 1, 0, 0, 0) ");
mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Links', 'links.php', '', '', 1, 0, 0, 0) ");
mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Submit News', 'submitnews.php', '', '', 1, 0, 0, 0) ");
mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'e107.org', 'http://e107.org', 'Home of the e107 website script', 'button.png', 2, 0, 0, 0) ");
mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Forum', 'forum.php', '', '', 1, 0, 0, 0) ");
mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Stats', 'stats.php', '', '', 1, 0, 0, 0) ");
mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Members', 'user.php', '', '', 1, 0, 0, 0) ");

$e107['e107_author'] = "Steve Dunstan (jalist)";
$e107['e107_url'] = "http://e107.org";
$e107['e107_version'] = "v5.4";
$e107['e107_build'] = "beta1";
$e107['e107_datestamp'] = time();
$tmp = serialize($e107);
mysql_query("INSERT INTO ".$mySQLprefix."core VALUES ('e107', '$tmp') ");

$udirs = "admin/|plugins/|temp";
$e_SELF = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
$e_HTTP = eregi_replace($udirs, "", substr($e_SELF, 0, strrpos($e_SELF, "/"))."/");
$pref['sitename'][1] = "e107 powered website";
$pref['siteurl'][1] = $e_HTTP;
$pref['sitebutton'][1] = $e_HTTP."button.png";
$pref['sitetag'][1] = "Website System ".$e107['e107_version']." ".$e107['e107_build'];
$pref['sitedescription'][1] = "";
$pref['siteadmin'][1] = $mainsiteadmin;
$pref['siteadminemail'][1] = $mainsiteadminemail;
$pref['sitetheme'][1] = "e107";
$pref['sitedisclaimer'][1] = "All trademarks are &copy; their respective owners, all other content is &copy; e107 powered website.<br />e107 is &copy; e107.org 2002/2003 and is released under the <a href=\"http://www.gnu.org/\">GNU GPL license</a>.";
$pref['newsposts'][1] = "10";
$pref['flood_protect'][1] = "";
$pref['flood_timeout'][1] = "5";
$pref['flood_time'][1] = "30";
$pref['flood_hits'][1] = "100";
$pref['anon_post'][1] = "1";
$pref['user_reg'][1] = "1";
$pref['use_coppa'][1] = "1";
$pref['profanity_filter'][1] = "1";
$pref['profanity_replace'][1] = "[censored]";
$pref['chatbox_posts'][1] = "10";
$pref['smiley_activate'][1] = "";
$pref['log_activate'][1] = "";
$pref['log_refertype'][1] = "1";
$pref['longdate'][1] = "%A %d %B %Y - %H:%M:%S";
$pref['shortdate'][1] = "%d %b : %H:%M";
$pref['forumdate'][1] = "%a %b %d %Y, %I:%M%p";
$pref['sitelanguage'][1] = "English";
$pref['maintainance_flag'][1] = "0";
$pref['time_offset'][1] = "0";
$pref['cb_linkc'][1] = " -link- ";
$pref['cb_wordwrap'][1] = "30";
$pref['cb_linkreplace'][1] = "1";
$pref['log_lvcount'][1] = "10";
$pref['meta_tag'][1] = "";
$pref['user_reg_veri'][1] = "1";
$tmp = serialize($pref);
mysql_query("INSERT INTO ".$mySQLprefix."core VALUES ('pref', '$tmp') ");
mysql_query("INSERT INTO ".$mySQLprefix."link_category VALUES (0, 'Main', 'Any links with this category will be displayed in main navigation bar.')");
mysql_query("INSERT INTO ".$mySQLprefix."link_category VALUES (0, 'Misc', 'Miscellaneous links.')");
mysql_query("INSERT INTO ".$mySQLprefix."wmessage VALUES ('1', 'This text (if activated) will appear at the top of your front page all the time.', '0')");
mysql_query("INSERT INTO ".$mySQLprefix."wmessage VALUES ('2', 'Member message ----- This text (if activated) will appear at the top of your front page all the time - only logged in members will see this.', '0')");
mysql_query("INSERT INTO ".$mySQLprefix."wmessage VALUES ('3', 'Administrator message ----- This text (if activated) will appear at the top of your front page all the time - only logged in administrators will see this.', '0')");
mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'login_menu', 1, 2, 0)");
mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'chatbox_menu', 1, 3, 0)");
mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'sitebutton_menu', 1, 4, 0)");
mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'online_menu', 1, 5, 0)");
mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'compliance_menu', 1, 6, 0)");
mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'articles_menu', 2, 1, 0)");
mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'poll_menu', 2, 2, 0)");
mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'headlines_menu', 2, 3, 0)");
mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'backend_menu', 2, 4, 0)");
mysql_query("INSERT INTO ".$mySQLprefix."userclass_classes VALUES (1, 'PRIVATEMENU', 'Grants access to private menu items')");
mysql_query("INSERT INTO ".$mySQLprefix."userclass_classes VALUES (2, 'PRIVATEFORUM1', 'Example private forum class')");
mysql_close();
return $error;
}
?>