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
|     $Source: /cvs_backup/e107_0.7/e107_files/emote_create/emotecreate.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-05-26 16:47:25 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

echo "<?xml version='1.0' encoding='iso-8859-1' ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><e107 resetcore></title>
<link rel="stylesheet" href="style.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-style-type" content="text/css" />
</head>
<body>
<div class='mainbox'>
<img src="images/logo.png" alt="Logo" style="border: 0px;" />
</div>
<br />
<br />


<?php


$emote = new emotec;



class emotec
{

	var $packArray;
	var $path;

	function emotec()
	{
		$this -> path = "../../e107_images/emotes/";
		if(!$handle = @opendir($this -> path))
		{
			$this -> emoteError("Unable to find /e107_files/emote_create/packs/ directory");
		}

		$packArray = array();

		while (false !== ($file = readdir($handle)))
		{
			if($file != "." && $file != ".." && is_dir($this -> path."/".$file))
			{
				$this -> packArray[] = array('file' => $file, 'confexist' => (file_exists($this -> path.$file."/emoteconf.php")));
			}
		}

		if(isset($_POST['subPack']))
		{
			$this -> emoteConf();
		}
		else if(isset($_POST['subConf']))
		{
			$this -> emoteSaveConfig();
		}
		else
		{
			$this -> emoteSetPack();
		}



	}

	function emoteSetPack()
	{

		$text .= "<b>How to use the e107 Emote Pack Creator</b><br />
		<ul>
		";


		$text .= "
		<li>Upload your emote images, in their own folder, to e107_images/emotes/</li>
		<li><b>you need to set the correct permissions of the folder you just uploaded - please chmod to 777</b></li>
		<li>Select the emote pack folder from the list below, and click continue</li>
		<li>Enter the codes to associate with your emote images, eg :) and ;o(</li>
		<li>Click save configuration</li>
		<li>the e107 configuration file is then saved to your emote folder - download and zip the folder, and it is ready to be distributed to other e107 users :)</li>
		</ul>
		<br /><br />

		<form method='post' action='".$_SERVER['PHP_SELF']."'>
		Please select which emote image pack to configure ...<br /><br />
		";

		foreach($this -> packArray as $pack)
		{
			$text .= "<input type='radio' name='packid' value='".$pack['file']."'> ".$pack['file']."<br />\n";
		}

		$text .= "<br />
		<input class='button' type='submit' name='subPack' value='Continue' />
		</form>
		";

		echo $text;
	}


	function emoteConf()
	{

		$emoteImageDir = $this -> path . $_POST['packid'];

		if(!$handle = @opendir($emoteImageDir))
		{
			$this -> emoteError("Unable to find ".$emoteImageDir." directory");
		}

		$imageArray = array();

		while (false !== ($file = readdir($handle)))
		{
			if($file != "." && $file != ".." && $file != "emoteconf.php" && !strstr($file, "template"))
			{
				$imageArray[] = $file;
			}
		}

		if(file_exists($emoteImageDir."/emoteconf.php"))
		{
			include($emoteImageDir."/emoteconf.php");
			$codeArray = unserialize($_emoteconf);
		}
		else
		{
			$codeArray = array();
		}

		$text = "<form method='post' action='".$_SERVER['PHP_SELF']."'>
		<table class='ctable'>
		<tr>
		<td colspan='3' class='colh'>Please enter the emote code you wish to associate with each image<br />(seperate multiple entries with spaces) ...</td>
		</tr>

		<tr>
		<td class='colh'>Name</td>
		<td class='colh'>Image</td>
		<td class='colh'>Code</td>
		</tr>


		";

		foreach($imageArray as $image)
		{

			$ca = str_replace(".", "_", $image);

			$text .= "
			<tr>
			<td class='col1'>$image</td>
			<td class='col2'><img src='".$emoteImageDir . "/" . $image."' alt='' /></td>
			<td class='col3'><input class='tb' type='text' name='$image' value='".$codeArray[$ca]."' maxlength='200' /></td>
			</tr>
			";
		}

		$text .= "
		<tr>
		<td colspan='3' class='colh'><input class='button' type='submit' name='subConf' value='Save Configuration' /></td>
		</tr>
		</table>
		<input type='hidden' name='packID' value='".$_POST['packid']."' />
		</form>";

		echo $text;

	}


	function emoteSaveConfig()
	{

		$filename = $_POST['packID'];
		unset($_POST['subConf'], $_POST['packID']);
		$emoteconf = serialize($_POST);

		$varStart = chr(36);
		$quote = chr(34);

		$data = chr(60)."?php\n". chr(47)."* e107 website system: Emote Pack File:  ".$filename."/emoteconf.php, ".date("F j, Y, g:i a", time())." *". chr(47)."\n\n".$varStart."_emoteconf = <<<EMOTE\n".$emoteconf."\nEMOTE;\n\n\n?".  chr(62);

		if ($handle = fopen($this -> path . $filename . "/emoteconf.php", 'w')) { 
			fwrite($handle, $data);
		}
		fclose($handle);

		echo "Emote configuration file created and saved.";












	}


	function emoteError($error)
	{
		echo "<b>Error!</b><br />".$error;
		echo "</body>\n</html>\n";
		exit;
	}

}







?>



</body>
</html>