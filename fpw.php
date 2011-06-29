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
define('e_NOCACHE',TRUE);
if(isset($_POST['pwsubmit']) && !isset($_POST['e-token']))
{
	$_POST['e-token'] = '';
}
require_once("class2.php");

if(USER)
{
	header("location:".e_BASE."index.php");
	exit;
}

if($pref['fpwcode'] && extension_loaded("gd"))
{
	define("USE_IMAGECODE",TRUE);
}
else
{
	define("USE_IMAGECODE",FALSE);
}


if (USE_IMAGECODE) 
{
	require_once(e_HANDLER."secure_img_handler.php");
	$sec_img = new secure_image;
}



if ($pref['membersonly_enabled']) 
{
	if (!$FPW_TABLE_HEADER) 
	{
		if (file_exists(THEME."fpw_template.php")) 
		{
			require_once(THEME."fpw_template.php");
		} 
		else 
		{
			require_once(e_THEME."templates/fpw_template.php");
		}
	}
	$HEADER = preg_replace("/\{(.*?)\}/e", '$\1', $FPW_TABLE_HEADER);
	$FOOTER = preg_replace("/\{(.*?)\}/e", '$\1', $FPW_TABLE_FOOTER);
}

require_once(HEADERF);


function fpw_error($txt) 
{
	global $ns;
	$ns->tablerender(LAN_03, "<div style='text-align:center'>".$txt."</div>");
	require_once(FOOTERF);
	exit;
}


//the separator character used
$fpw_sep = "#";



if (e_QUERY) 
{	// User has clicked on the emailed link
	define("FPW_ACTIVE","TRUE");
	$tmp = explode($fpw_sep, e_QUERY);
	$tmpinfo = preg_replace("#[\W_]#", "", $tp -> toDB($tmp[0], true));
	if ($sql->db_Select("tmp", "*", "`tmp_info` LIKE '%{$fpw_sep}{$tmpinfo}' ")) 
	{
		$row = $sql->db_Fetch();
		extract($row);
		$sql->db_Delete("tmp", "`tmp_info` LIKE '%{$fpw_sep}{$tmpinfo}' ");
		$newpw = "";
		$pwlen = rand(8, 12);
		for($a = 0; $a <= $pwlen; $a++) 
		{
			$newpw .= chr(rand(97, 122));
		}
		$mdnewpw = md5($newpw);			// Generate new random password

		list($loginName, $md5) = explode($fpw_sep, $tmp_info);
		$sql->db_Update("user", "`user_password`='{$mdnewpw}', `user_viewed`='' WHERE `user_loginname`='".$tp -> toDB($loginName, true)."' ");
		cookie($pref['cookie_name'], "", (time()-2592000));
		$_SESSION[$pref['cookie_name']] = "";

		$txt = "<div>".LAN_FPW8."<br /><br />
		<table style='width:70%'>
		<tr><td>".LAN_218."</td><td style='font-weight:bold'>{$loginName}</td></tr>
		<tr><td>".LAN_FPW9."</td><td style='font-weight:bold'>{$newpw}</td></tr>
		</table>
		<br /><br />".LAN_FPW10." <a href=\"".e_LOGIN."\">".LAN_FPW11."</a> ".LAN_FPW12."</div>";
		fpw_error($txt);

	} 
	else 
	{
		fpw_error(LAN_FPW7);
	}
}


// Request to reset password
//--------------------------
if (isset($_POST['pwsubmit'])) 
{	// Request for password reset submitted
	require_once(e_HANDLER."mail.php");
	$email = $_POST['email'];

	if ($pref['fpwcode'] && extension_loaded("gd")) 
	{
		if (!$sec_img->verify_code($_POST['rand_num'], $_POST['code_verify'])) 
		{
			fpw_error(LAN_FPW3);
		}
	}

	$clean_email = check_email($tp -> toDB($_POST['email']));
//	$clean_username = $tp -> toDB($_POST['username']);
	$clean_username = 	$temp_name = str_replace('--', '', trim(preg_replace("/[\^\*\|\/;:#=\$'\"!#`\s\(\)%\?<>\\{}]/", '', $_POST['username'])));

	
 	$query = "`user_email`='{$clean_email}' ";
	// Allow admins to remove 'username' from fpw_template.php if they wish.
	$query .= (isset($_POST['username'])) ? " AND `user_loginname`='{$clean_username}'" : "";

	if ($sql->db_Select("user", "*", $query)) 
	{	// Found user in DB
		$row = $sql->db_Fetch();
		 extract($row);

		if ($row['user_admin'] == 1 && $row['user_perms'] == "0") 
		{	// Main admin expected to be competent enough to never forget password! (And its a security check - so warn them)
			sendemail($pref['siteadminemail'], LAN_06, LAN_07."".$e107->getip()." ".LAN_08);
			echo "<script type='text/javascript'>document.location.href='index.php'</script>\n";
			die();
		}

		if ($result = $sql->db_Select("tmp", "*", "`tmp_ip` = 'pwreset' AND `tmp_info` LIKE '{$row['user_loginname']}{$fpw_sep}%'")) 
		{
			fpw_error(LAN_FPW4);
			exit;
		}

		mt_srand ((double)microtime() * 1000000);
		$maxran = 1000000;
		$rand_num = mt_rand(0, $maxran);
		$datekey = date("r");
		$rcode = md5($_SERVER['HTTP_USER_AGENT'] . serialize($pref). $rand_num . $datekey);

		$link = SITEURL."fpw.php?{$rcode}";
		$message = LAN_FPW5." ".SITENAME." ".LAN_FPW14." : ".$e107->getip().".\n\n".LAN_FPW15."\n\n".LAN_FPW16."\n\n".LAN_FPW17."\n\n{$link}";
		//  $message = LAN_FPW5."\n\n{$link}";

		$deltime = time()+86400 * 2;
		//Set timestamp two days ahead so it doesn't get auto-deleted
		$sql->db_Insert("tmp", "'pwreset',{$deltime},'{$row['user_loginname']}{$fpw_sep}{$rcode}'");


		if (sendemail($_POST['email'], "".LAN_09."".SITENAME, $message)) 
		{
			$text = "<div style='text-align:center'>".LAN_FPW6."</div>";
		} 
		else 
		{
			$text = "<div style='text-align:center'>".LAN_02."</div>";
		}

		$ns->tablerender(LAN_03, $text);
		require_once(FOOTERF);
		exit;
	} 
	else 
	{
		$text = LAN_213;
		$ns->tablerender(LAN_214, "<div style='text-align:center'>".$text."</div>");
	}
}


if (USE_IMAGECODE) 
{
	$FPW_TABLE_SECIMG_LAN = LAN_FPW2;
	$FPW_TABLE_SECIMG_HIDDEN = "<input type='hidden' name='rand_num' value='".$sec_img->random_number."' />";
	$FPW_TABLE_SECIMG_SECIMG = $sec_img->r_image();
	$FPW_TABLE_SECIMG_TEXTBOC = "<input class='tbox' type='text' name='code_verify' size='15' maxlength='20' />";
}

if (!$FPW_TABLE) 
{
	if (file_exists(THEME."fpw_template.php")) 
	{
		require_once(THEME."fpw_template.php");
	} 
	else 
	{
		require_once(e_THEME."templates/fpw_template.php");
	}
}
$text = preg_replace("/\{(.*?)\}/e", '$\1', $FPW_TABLE);

$ns->tablerender(LAN_03, $text);
require_once(FOOTERF);

?>