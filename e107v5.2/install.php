<?php
if(filesize("config.php") != 0 && $_POST['stage'] == ""){
	 header("location: index.php");
}
if(IsSet($_POST['frontpage'])){ header("location: index.php"); }
if(IsSet($_POST['adminpage'])){ header("location: admin/admin.php"); }
if(IsSet($_POST['upgrade'])){ header("location: upgrade.php"); exit; }
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
<td style="width:80%; background-color:#E2E2E2; text-align:left\">
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
if(!$_POST['stage']){ $_POST['stage'] = 0; }
echo "<div class=\"mediumtext\">&nbsp;&nbsp;Installation Stage: ".$_POST['stage']." of 5</div>";
?>

</td>
</tr>
<tr> 
<td colspan="2" style="background-color:#000; vertical-align: top;"></td>
</tr>
<tr>
<td style="vertical-align: top;"><br />
<table style="width:100%" cellpadding="10" cellspacing="10">
<tr>
<td style="background-color:#fff; ">

<!-- POST CHECK FAILED! -->
<?php
if($_POST['mysql_server'] == "" && $mysql_server != ""){
	echo "POST type variables are not being recognised - these were introduced in php version 4.1.0, if your php version is older than this you will need to upgrade to use e107<br />(Your php version is ".phpversion().".)<br />Script halted.";
	exit;
}
?>
<!-- BEGIN STAGE 0 -->
<?php
if($_POST['stage'] == ""){
?>
Welcome to e107 website system. This script will install or upgrade e107 on your server, please follow the instructions.
<br />
<br />
e107 has been extensively tested under Linux, Windows and FreeBSD environments. It requires PHP v4.1 or newer and mySQL. (Your PHP version is <?php echo phpversion(); ?>)
<br />
Before you start the installation you need to ensure that /config.php is chmodded to 666 as the installation script needs to write some values to it during the install process. 
<br />
If you want e107 to use it's own mySQL database you need to create one either from your shell or using phpMyAdmin. You can use an existing database if you wish.
<br />
<br />
<form method="post" action="install.php">
<input class="button" type="submit" name="submit" value="Click to begin installation" />
<input type="hidden" name="stage" value="1">
</form>
<?php
}
?>
<!-- END STAGE 0 -->

<!-- BEGIN STAGE 1 -->
<?php
if($_POST['stage'] == 1){
echo "Testing file permissions of /config.php ...<br /><br />";

$fp = @fopen("config.php","w");
if(@fwrite($fp, "Test")){
	echo "File permissions test passed - please click button to continue.<br /><br />
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Proceed to stage 2\" />
<input type=\"hidden\" name=\"stage\" value=\"2\">";
	exit;
}else{
	echo "<b>Unable to write to /config.php!</b><br />Please CHMOD /config.php to 666 then click the button to continue.<br /><br />
	<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Retest file permissions of /config.php\" />
<input type=\"hidden\" name=\"stage\" value=\"1\">";
}
@fclose($fp);
}
?>
<!-- END STAGE 1 -->

<!-- BEGIN STAGE 2 -->
<?php
if($_POST['stage'] == 2){
echo "Please enter your mysql details <br />If you dont have any of these details or are unable to create a database you will need to contact your system administrator.
<br />
If you are using an existing database that already has tables in it you can prefix your e107 tables so that naming conflicts don't occur. Enter whatever you want to use as a prefix in the Table Prefix box and your tables wil be called [yourprefix]admin, [yourprefix]news etc. If you have created a new database for e107 to use you can leave the prefix box blank if you wish.
<br />
<br />
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<table style=\"width:100%\">
<tr>
<td style=\"width:20%\" class=\"mediumtext\">mySQL Server:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"mysql_server\" size=\"60\" value=\"localhost\" maxlength=\"100\" /> Your mySQL server (normally 'localhost')
</td>
</tr>
<tr>
<td style=\"width:20%\" class=\"mediumtext\">mySQL Username:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"mysql_name\" size=\"60\" value=\"\" maxlength=\"100\" /> Your mySQL username
</td>
</tr>
<tr> 
<td style=\"width:20%\" class=\"mediumtext\">mySQL Password: </td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"mysql_password\" size=\"60\" value=\"\" maxlength=\"100\" /> Your mySQL password
</td>
</tr>\n
<tr> 
<td style=\"width:20%\" class=\"mediumtext\">mySQL Database: </td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"mysql_db\" size=\"60\" value=\"\" maxlength=\"100\" /> The name of the database you wish to use for e107
</td>

</tr>\n
<tr> 
<td style=\"width:20%\" class=\"mediumtext\">Table prefix: </td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"mysql_prefix\" size=\"60\" value=\"e107_\"  maxlength=\"100\" /> What to prefix your table names with - leave blank for no prefix
</td>

</tr>
<tr>
<td colspan=\"2\">
<br /><br />
Please enter the details for the main site administrator
<br /><br />
</td>
</tr>

<tr>
<td style=\"width:20%\" class=\"mediumtext\">Admin Name:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"admin_name\" size=\"60\" value=\"\" maxlength=\"100\" />
</td>
</tr>
<tr>
<td style=\"width:20%\" class=\"mediumtext\">Admin Password:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"password\" name=\"admin_password1\" size=\"60\" value=\"\" maxlength=\"100\" />
</td>
</tr>
<tr> 
<td style=\"width:20%\" class=\"mediumtext\">Re-type password: </td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"password\" name=\"admin_password2\" size=\"60\" value=\"\" maxlength=\"100\" />
</td>
</tr>\n
<tr> 
<td style=\"width:20%\" class=\"mediumtext\">Admin Email Address: </td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"admin_email\" size=\"60\" value=\"you@yoursite.com\" maxlength=\"100\" />
</td>

</tr>\n
<tr style=\"vertical-align:top\"> 
<td style=\"width:20%\"><br /></td>
<td style=\"width:80%\"><br />
<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Continue\" />

</td>
</tr>
</table>
<input type=\"hidden\" name=\"stage\" value=\"3\">
</form>";
exit;
}
?>

<!-- END STAGE 2 -->

<!-- BEGIN STAGE 3 -->
<?php
if($_POST['stage'] == 3){
	if($_POST['mysql_server'] == "" || $_POST['mysql_name'] =="" || $_POST['mysql_db'] == "" || $_POST['admin_name'] == "" || $_POST['admin_password1'] == "" || $_POST['admin_password2'] == "" || $_POST['admin_email'] == ""){
		$error = "You left required fields blank";
	}
	if($_POST['admin_password1'] != $_POST['admin_password2']){
		$error = "The two passwords you entered do not match";
	}
	if(!preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $_POST['admin_email'])){
		$error = "That doesn't appear to be a valid email address";
	}

	if($error != ""){
		echo $error." -  - please re-enter your information.<br /><br />";
	?>
	<form method="post" action="install.php">
	<input class="button" type="submit" name="submit" value="Continue" />
	<input type="hidden" name="stage" value="2">
	</form>
	<?php
		exit;
	}

	echo "Information verified<br /><br />";
	echo "Attempting to connect to mySQL server (".$_POST['mysql_server'].") using  username <i>".$_POST['mysql_name']."</i> and password <i>".$_POST['admin_password1']."</i> ... ";
	if(!@mysql_connect($_POST['mysql_server'], $_POST['mysql_name'], $_POST['mysql_password'])){
		echo "Unable to connect to mySQL server - please verify your details and re-enter.<br /><br />";
		?>
		<form method="post" action="install.php">
		<input class="button" type="submit" name="submit" value="Continue" />
		<input type="hidden" name="stage" value="2">
		</form>
		<?php
		exit;
	}
	echo "mySQL test passed - successfully connected to mySQL server.<br /><br />Checking validity of database ...";

	if(@mysql_select_db($_POST['mysql_db'])){
		echo " database validity test passed - database found and verified.<br /><br />";
	}else{
		echo "<br /><b>Could not verify database '".$_POST['mysql_db']."' - please make sure database was created properly and that it is called '".$_POST['mysql_db']."'</b><br /><br />";
		?>
		<form method="post" action="install.php">
		<input class="button" type="submit" name="submit" value="Continue" />
		<input type="hidden" name="stage" value="2">
		</form>
		<?php
		exit;
	}

	echo "Attempting to write settings to config file ...";
	$data = chr(60)."?php\n".
chr(47)."*\n+---------------------------------------------------------------+\n|	e107 website system\n|	/config.php\n|\n|	©Steve Dunstan 2001-2002\n|	http://e107.org\n|	jalist@e107.org\n|\n|	Released under the terms and conditions of the\n|	GNU General Public License (http://gnu.org).\n+---------------------------------------------------------------+\n\nThis file has been generated by the installation script.\n\n*".
chr(47)."\n\n".
chr(36)."mySQLserver = ".chr(34).$_POST['mysql_server'].chr(34).";\n".
chr(36)."mySQLuser = ".chr(34).$_POST['mysql_name'].chr(34).";\n".
chr(36)."mySQLpassword = ".chr(34).$_POST['mysql_password'].chr(34).";\n".
chr(36)."mySQLdefaultdb = ".chr(34).$_POST['mysql_db'].chr(34).";\n".
chr(36)."mySQLprefix = ".chr(34).$_POST['mysql_prefix'].chr(34).";\n\n".
chr(47).chr(47)."define(".chr(34)."MQ".chr(34).", TRUE);\n\n?".chr(62);

	$fp = @fopen("config.php","w");
	if(!@fwrite($fp, $data)){
		echo "<b>Error!</b><br />Was unable to write config.php to server, the file probably doesn't have the correct permissions set. Try chmodding config.php to 666 or 777 and re-running script. Script halted.";
		exit;
	}
	echo " config file successfully written to server.<br /><br />";
	fclose($fp);

	echo "<b>You are now ready to begin creating the database tables e107 will use, please press the button to continue.</b><br /><br />
	
	<form method=\"post\" action=\"install.php\">
	<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Continue\" />
	<input type=\"hidden\" name=\"stage\" value=\"4\" />

	<input type=\"hidden\" name=\"admin_name\" value=\"".$_POST['admin_name']."\" />
	<input type=\"hidden\" name=\"admin_password1\" value=\"".$_POST['admin_password1']."\" />
	<input type=\"hidden\" name=\"admin_email\" value=\"".$_POST['admin_email']."\" />

	</form>";
	
	exit;
}
	?>

<!-- END STAGE 3 -->

<!-- BEGIN STAGE 4 -->
<?php
if($_POST['stage'] == 4){

	require_once("config.php");
	mysql_connect($mySQLserver, $mySQLuser, $mySQLpassword);
	mysql_select_db($mySQLdefaultdb);

	echo "Setting up database tables ...<br /><br />";
	require_once("config.php");
	$error = setuptables($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb, $mySQLprefix);
	if($error != ""){
		echo "<br /><b>Error</b><br />".$error."<br />You may need to delete the tables involved and recreate them by re-running this script.<br /><br />Script halted.";
		exit;
	}else{
		echo " Tables successfully set up.<br /><br />Please press the button to set up the main site administrator in the database.<br /><br />
		
	<form method=\"post\" action=\"install.php\">
	<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Continue\" />
	<input type=\"hidden\" name=\"stage\" value=\"5\">
	<input type=\"hidden\" name=\"admin_name\" value=\"".$_POST['admin_name']."\" />
	<input type=\"hidden\" name=\"admin_password1\" value=\"".$_POST['admin_password1']."\" />
	<input type=\"hidden\" name=\"admin_email\" value=\"".$_POST['admin_email']."\" />

	</form>";
	
	exit;
	}
}
	?>
<!-- END STAGE 4 -->

<!-- BEGIN STAGE 5 -->
<?php

if($_POST['stage'] == 5){
	require_once("config.php");
	mysql_connect($mySQLserver, $mySQLuser, $mySQLpassword);
	mysql_select_db($mySQLdefaultdb);
	echo "Setting up main site administrator ...";
	if(!mysql_query("INSERT INTO ".$mySQLprefix."admin VALUES (0, '".$_POST['admin_name']."',  '".md5($_POST['admin_password1'])."', '".$_POST['admin_email']."', 0, '0', '".time()."') ")){
		echo "<b>Error</b> - unable to enter admin details into database - script halted.";
		exit;
	}else{
		if(!mysql_query("INSERT INTO ".$mySQLprefix."user VALUES (0, '".$_POST['admin_name']."', '".md5($_POST['admin_password1'])."', '', '$email', 	'$website', '$icq', '$aim', '$msn', '$location', '$birthday', '$signature', '$image', '$timezone', '$hideeamil', '".time()."', '0', '".time()."', '0', '0', '0', '0', '".$ip."', '0', '0', '', '', '', '1')")){
			echo "<b>Error</b> - unable to enter admin details into database - script halted.";
			exit;
		}
	}

	echo " main adminstrator set up and entered into database.<br /><br />
	Congratulations - e107 has been successfully installed on your server.<br />
	The main site administrator has been set up and entered into the database - please write these down in a safe place<br />
	<b>Administrator name: ".$_POST['admin_name'].", Administrator password: ".$_POST['admin_password1']."</b><br /><br />
	You now need to chmod the following files to these respective values ...<br />
	/config.php - 644<br />
	/backend/news.xml - 666<br />
	/backend/news.txt - 666<br /><br />
	<b>For security reasons please delete /install.php and /upgrade.php from your server after you have clicked on one of the buttons below.</b><br /><br />";

	?>
	<form method="post" action="install.php">
	<input class="button" type="submit" name="frontpage" value="Click here to go to your main front page" />
	<input class="button" type="submit" name="adminpage" value="Click here to go to your admin page" />
	</form>
	<?php
}

function setuptables($server, $user, $pass, $db, $mySQLprefix){
$admin_table = "CREATE TABLE ".$mySQLprefix."admin (
  admin_id smallint(5) unsigned NOT NULL auto_increment,
  admin_name varchar(20) NOT NULL default '',
  admin_password varchar(100) NOT NULL default '',
  admin_email varchar(200) NOT NULL default '',
  admin_sess varchar(32) NOT NULL default '',
  admin_permissions text NOT NULL,
  admin_pwchange int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (admin_id)
) TYPE=MyISAM;";

$banlist_table = "CREATE TABLE ".$mySQLprefix."banlist (
  banlist_ip varchar(15) NOT NULL default '',
  banlist_admin smallint(5) unsigned NOT NULL default '0',
  banlist_reason tinytext NOT NULL,
  PRIMARY KEY  (banlist_ip)
) TYPE=MyISAM;";

$chatbox_table = "CREATE TABLE ".$mySQLprefix."chatbox (
  cb_id int(10) unsigned NOT NULL auto_increment,
  cb_nick varchar(20) NOT NULL default '',
  cb_message text NOT NULL,
  cb_datestamp int(10) unsigned NOT NULL default '0',
  cb_blocked tinyint(3) unsigned NOT NULL default '0',
  cb_ip varchar(15) NOT NULL default '',
  PRIMARY KEY  (cb_id)
) TYPE=MyISAM;";

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

$content_table = "CREATE TABLE ".$mySQLprefix."content (
  content_id int(10) unsigned NOT NULL auto_increment,
  content_heading tinytext NOT NULL,
  content_subheading tinytext NOT NULL,
  content_content text NOT NULL,
  content_page tinyint(3) unsigned NOT NULL default '0',
  content_datestamp int(10) unsigned NOT NULL default '0',
  content_author smallint(5) unsigned NOT NULL default '0',
  content_comment tinyint(3) unsigned NOT NULL default '0',
  content_parent int(10) unsigned NOT NULL default '0',
  content_type tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (content_id)
) TYPE=MyISAM;";

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
  PRIMARY KEY  (forum_id)
) TYPE=MyISAM;";

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

$e107_table = "CREATE TABLE ".$mySQLprefix."e107 (
  e107_author varchar(50) NOT NULL default '',
  e107_url varchar(100) NOT NULL default '',
  e107_version varchar(10) NOT NULL default '',
  e107_build varchar(10) NOT NULL default '',
  e107_datestamp int(10) unsigned NOT NULL default '0'
) TYPE=MyISAM;";

$link_category_table = "CREATE TABLE ".$mySQLprefix."link_category (
  link_category_id int(10) unsigned NOT NULL auto_increment,
  link_category_name varchar(100) NOT NULL default '',
  link_category_description varchar(250) NOT NULL default '',
  PRIMARY KEY  (link_category_id)
) TYPE=MyISAM;";

$link_table = "CREATE TABLE ".$mySQLprefix."links (
 link_id int(10) unsigned NOT NULL auto_increment,
  link_name varchar(100) NOT NULL default '',
  link_url varchar(200) NOT NULL default '',
  link_description text NOT NULL,
  link_button varchar(100) NOT NULL default '',
  link_category tinyint(3) unsigned NOT NULL default '0',
  link_order int(10) unsigned NOT NULL default '0',
  link_refer int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (link_id)
) TYPE=MyISAM;";

$menus_table = "CREATE TABLE ".$mySQLprefix."menus (
  menu_id int(10) unsigned NOT NULL auto_increment,
  menu_name varchar(100) NOT NULL default '',
  menu_location tinyint(3) unsigned NOT NULL default '0',
  menu_order tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (menu_id)
) TYPE=MyISAM;";

$news_table = "CREATE TABLE ".$mySQLprefix."news (
  news_id int(10) unsigned NOT NULL auto_increment,
  news_title varchar(200) NOT NULL default '',
  news_body text NOT NULL,
  news_extended text NOT NULL,
  news_datestamp int(10) unsigned NOT NULL default '0',
  news_author tinyint(3) unsigned NOT NULL default '0',
  news_source varchar(200) NOT NULL default '',
  news_url varchar(200) NOT NULL default '',
  news_category tinyint(3) unsigned NOT NULL default '0',
  news_allow_comments tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (news_id)
) TYPE=MyISAM;";

$news_catagory_table = "CREATE TABLE ".$mySQLprefix."news_category (
  category_id int(10) unsigned NOT NULL auto_increment,
  category_name varchar(200) NOT NULL default '',
  category_icon varchar(250) NOT NULL default '',
  PRIMARY KEY  (category_id)
) TYPE=MyISAM;";

$online_table = "CREATE TABLE ".$mySQLprefix."online (
  online_timestamp int(10) unsigned NOT NULL default '0',
  online_flag tinyint(3) unsigned NOT NULL default '0',
  online_user_id  varchar(100) NOT NULL default '',
  online_ip varchar(15) NOT NULL default '',
  online_location varchar(100) NOT NULL default ''
) TYPE=MyISAM;";

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

$prefs_table = "CREATE TABLE ".$mySQLprefix."prefs (
  pref_name varchar(100) NOT NULL default '',
  pref_value text NOT NULL
) TYPE=MyISAM;";

$stat_counter_table = "CREATE TABLE ".$mySQLprefix."stat_counter (
 counter_date date NOT NULL default '0000-00-00',
  counter_url varchar(100) NOT NULL default '',
  counter_unique int(10) unsigned NOT NULL default '0',
  counter_total int(10) unsigned NOT NULL default '0',
  counter_ip text NOT NULL,
  counter_today_total int(10) unsigned NOT NULL default '0',
  counter_today_unique int(10) unsigned NOT NULL default '0'
) TYPE=MyISAM;";

$stat_info_table = "CREATE TABLE ".$mySQLprefix."stat_info (
  info_name text NOT NULL default '',
  info_count int(10) unsigned NOT NULL default '0',
  info_type tinyint(3) unsigned NOT NULL default '0'
) TYPE=MyISAM;";

$stat_last_table = "CREATE TABLE ".$mySQLprefix."stat_last (
  stat_last_date int(11) unsigned NOT NULL default '0',
  stat_last_info text NOT NULL
) TYPE=MyISAM;";

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
  PRIMARY KEY  (user_id),
  UNIQUE KEY user_name (user_name)
) TYPE=MyISAM;";

$wmessage_table = "CREATE TABLE ".$mySQLprefix."wmessage (
  wm_text text NOT NULL,
  wm_active tinyint(3) unsigned NOT NULL default '0'
) TYPE=MyISAM;";

$flood_table = "CREATE TABLE ".$mySQLprefix."flood (
  flood_url text NOT NULL,
  flood_time int(10) unsigned NOT NULL default '0'
) TYPE=MyISAM;";

$welcome_message = addslashes("e107 is what is commonly known as a CMS, or content management system. It gives you a completely interactive website without the need to learn HTML, PHP etc.<br />It has been in developement since July 2002 and is constantly being updated and tweaked for better performance and stability.
Some of the features of e107 are ...<ul><li>Secure administration backend allows you to moderate all aspects of your website, post news items etc</li><li>News item commenting, chatbox, forums, poll etc make your site totally interactive to visitors</li><li>Totally themeable interface, change every aspect of how your site looks</li><li>More themes and plugins available to download, dynamic recognition of new addons means extremely easy installation</li><li>Allow users to register as members on your site, and allow comments from members only or anonymous users</li></ul>Your admin section is located at <a href=\"admin/admin.php\">/admin/admin.php</a>, click to go there now. You will have to login using the name and password you entered during the installation process.
If you would like to see something added to the core, or coded as a plugin please visit <a href=\"http://jalist.com\">jalist.com</a> and leave a message on the Requests forum, or alternatively email the developer jalist (Steve Dunstan) <a href=\"mailto:jalist@jalist.com\">here</a>.
If you have created a theme or plugin for e107 please consider sharing it with the rest of the community - send it to <a href=\"mailto:jalist@jalist.com\">jalist</a> who will upload it to the main e107 site at <a href=\"http://jalist.com\">jalist.com</a>.
Thankyou for trying e107, and have fun with your new website!
(You can delete this message from your admin section.)");

$d1 = "<div class=\"spacer\"><img src=\"themes/e107/images/bullet2.gif\" alt=\"bullet\" /><b>NICKNAME</b><br /><span class=\"smalltext\">DATE</span><br />";
$d2 = "<div class=\"smallblacktext\">MESSAGE</div></div>";
$d3 = "<br />";

if(!mysql_query($admin_table)){	
	$error .= "There was a problem creating the <b>admin</b> mySQL table ...<br />";}else{$noerror .= "admin table ... created";}
if(!mysql_query($banlist_table)){	$error .= "There was a problem creating the <b>banlist</b> mySQL table ...<br />"; }else{echo "banlist table ... created<br />";}
if(!mysql_query($chatbox_table)){	$error .= "There was a problem creating the <b>chatbox</b> mySQL table ...<br />"; }else{echo "chatbox table ... created<br />";}
if(!mysql_query($comments_table)){	$error .= "There was a problem creating the <b>comments</b> mySQL table ...<br />"; }else{echo "comments table ... created<br />";}
if(!mysql_query($content_table)){	$error .= "There was a problem creating the <b>content</b> mySQL table ...<br />"; }else{echo "content table ... created<br />";}
if(!mysql_query($e107_table)){	$error .= "There was a problem creating the <b>e107</b> mySQL table ...<br />"; }else{echo "e107 table ... created<br />";}
if(!mysql_query($forum_table)){	$error .= "There was a problem creating the <b>forum</b> mySQL table ...<br />"; }else{echo "forum table ... created<br />";}
if(!mysql_query($forum_t_table)){	$error .= "There was a problem creating the <b>forum_t</b> mySQL table ...<br />"; }else{echo "forum_t table ... created<br />";}

if(!mysql_query($flood_table)){	$error .= "There was a problem creating the <b>flood</b> mySQL table ...<br />"; }else{echo "flood table ... created<br />";}

if(!mysql_query($headlines_table)){	$error .= "There was a problem creating the <b>headlines_table</b> mySQL table ...<br />"; }else{echo "headlines_table table ... created<br />";}
if(!mysql_query($link_table)){	$error .= "There was a problem creating the <b>link</b> mySQL table ...<br />"; }else{echo "link table ... created<br />";}
if(!mysql_query($link_category_table)){	$error .= "There was a problem creating the <b>link_category</b> mySQL table ...<br />"; }else{echo "link_category table ... created<br />";}
if(!mysql_query($menus_table)){	$error .= "There was a problem creating the <b>menus</b> mySQL table ...<br />"; }else{echo "menus table ... created<br />";}
if(!mysql_query($news_table)){	$error .= "There was a problem creating the <b>news</b> mySQL table ...<br />"; }else{echo "news table ... created<br />";}
if(!mysql_query($news_catagory_table)){	$error .= "There was a problem creating the <b>news_category</b> mySQL table ...<br />"; }else{echo "news_category table ... created<br />";}
if(!mysql_query($online_table)){	$error .= "There was a problem creating the <b>online</b> mySQL table ...<br />"; }else{echo "online table ... created<br />";}
if(!mysql_query($poll_table)){	$error .= "There was a problem creating the <b>poll</b> mySQL table ...<br />"; }else{echo "poll table ... created<br />";}
if(!mysql_query($prefs_table)){	$error .= "There was a problem creating the <b>prefs</b> mySQL table ...<br />"; }else{echo "prefs table ... created<br />";}
if(!mysql_query($submitnews_table)){	$error .= "There was a problem creating the <b>submit_news</b> mySQL table ...<br />"; }else{echo "submitnews table ... created<br />";}
if(!mysql_query($user_table)){	$error .= "There was a problem creating the <b>user</b> mySQL table ...<br />"; }else{echo "user table ... created<br />";}
if(!mysql_query($stat_counter_table)){	$error .= "There was a problem creating the <b>stat_counter</b> mySQL table ...<br />"; }else{echo "stat_counter table ... created<br />";}
if(!mysql_query($stat_info_table)){	$error .= "There was a problem creating the <b>stat_info</b> mySQL table ...<br />"; }else{echo "stat_info table ... created<br />";}
if(!mysql_query($stat_last_table)){	$error .= "There was a problem creating the <b>stat_last</b> mySQL table ...<br />"; }else{echo "stat_info table ... created<br />";}
if(!mysql_query($wmessage_table)){	$error .= "There was a problem creating the <b>wmessage</b> mySQL table ...<br />"; }else{echo "wmessage table ... created<br /><br />";}

$datestamp = time();

mysql_query("INSERT INTO ".$mySQLprefix."content VALUES (0, '$article_heading', '$article_subheading', '$article', '$datestamp', 0, 0) ");
mysql_query("INSERT INTO ".$mySQLprefix."news VALUES (0, 'Welcome to e107', '$welcome_message', '', '$datestamp', '0', '', '', '1', '0') ");
mysql_query("INSERT INTO ".$mySQLprefix."news_category VALUES (0, 'Misc', 'images/bullet1.gif') ");
mysql_query("INSERT INTO ".$mySQLprefix."poll VALUES (0, '$datestamp', 0, 1, 'So what do you think of e107?', 'I\'m not impressed', 'It\'s not bad but I prefer Nuke/Postnuke', 'It\'s good', 'I love it!', 'Grah I hate polls', 'What\'s e107 anyway?', '', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 1) ");
mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Home', 'index.php', '', '', 1, 0, 0) ");
mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Links', 'links.php', '', '', 1, 0, 0) ");
mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Submit News', 'submitnews.php', '', '', 1, 0, 0) ");
mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'jalist.com', 'http://jalist.com', 'Home of the e107 website script', 'button.png', 2, 0, 0) ");
mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Forum', 'forum.php', '', '', 1, 0, 0) ");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('sitename', 'e107 powered site')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('siteurl', 'http://yoursite.com' )");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('sitebutton', 'button.png' )");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('sitetag', 'Website System Version 5.2' )");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('sitedescription', '' )");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('siteadmin', 'Webmaster' )");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('siteadminemail', 'webmaster@yourdomain.com' )");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('sitetheme', 'e107' )");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('sitedisclaimer', 'All trademarks are &copy; their respective owners, all other content is © e107 site.<br />e107 is © jalist.com 2002 and is released under the <a href=\"http://www.gnu.org/\">GNU GPL license</a>. ' )");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('newsposts', '10' )");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('flood_protect', 0)");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('flood_timeout', 5)");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('anon_post', 1)");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('user_reg', 1)");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('use_coppa', 1)");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('profanity_filter', 0)");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('profanity_replace', 'censored')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('chatbox_posts', '10')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('smiley_activate', '0')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('log_activate', '0')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('log_refertype', '0')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('longdate', 'l d F Y - H:i:s')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('shortdate', 'd M : H:i')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('forumdate', 'd-m-Y  g:i a')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('sitelanguage', 'English')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('sitelocale', 'en')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('maintainance_flag', '0')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('time_offset', '0')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('flood_time', '30')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('flood_hits', '100')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('cb_linkc', '- link -')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('cb_wordwrap', '30')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('cb_linkreplace', 'enabl')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('cb_display1', '$d1')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('cb_display2', '$d2')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('cb_display3', '$d3')");
mysql_query("INSERT INTO ".$mySQLprefix."prefs VALUES ('log_lvcount', '10')");
mysql_query("INSERT INTO ".$mySQLprefix."e107 VALUES ('jalist (Steve Dunstan)', 'http://jalist.com', '5.2', '1', '$datestamp')");
mysql_query("INSERT INTO ".$mySQLprefix."link_category VALUES (0, 'Main', 'Any links with this category will be displayed in main navigation bar.')");
mysql_query("INSERT INTO ".$mySQLprefix."link_category VALUES (0, 'Misc', 'Miscellaneous links.')");
mysql_query("INSERT INTO ".$mySQLprefix."wmessage VALUES ('This text (if activated) will appear at the top of your front page all the time.', '0')");
mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'login_menu', 1, 2)");
mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'chatbox_menu', 1, 3)");
mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'sitebutton_menu', 1, 4)");
mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'online_menu', 1, 5)");
mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'compliance_menu', 1, 6)");
mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'articles_menu', 2, 1)");
mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'poll_menu', 2, 2)");
mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'headlines_menu', 2, 3)");
mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'backend_menu', 2, 4)");

mysql_close();
return $error;
}
?>


</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>