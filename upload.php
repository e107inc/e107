<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/upload.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

// Experimental e-token
if(!empty($_POST) && !isset($_POST['e-token']))
{
	// set e-token so it can be processed by class2
	$_POST['e-token'] = '';
}

require_once("class2.php");

if (!$pref['upload_enabled'] || $pref['upload_class'] == 255) 
{
  header("location: ".e_BASE."index.php");
  exit;
}

require_once(HEADERF);

if (!defined("USER_WIDTH")){ define("USER_WIDTH","width:97%"); }

if (!check_class($pref['upload_class'])) 
{
  $text = "<div style='text-align:center'>".LAN_UL_002."</div>";
  $ns->tablerender(LAN_UL_020, $text);
  require_once(FOOTERF);
  exit;
}


$message = '';
$postemail ='';
if (isset($_POST['upload'])) 
{
  if (($_POST['file_email'] || USER == TRUE) && $_POST['file_name'] && $_POST['file_description'] && $_POST['download_category']) 
  {
	require_once(e_HANDLER."upload_handler.php");
//	$uploaded = file_upload(e_FILE."public/", "unique");
	$uploaded = process_uploaded_files(e_FILE."public/", "unique", array('max_file_count' => 2, 'extra_file_types' => TRUE));

// First, see what errors the upload handler picked up
	if ($uploaded === FALSE)
	{
	  $message = LAN_UL_021.'<br />';
	}
	
// Now see if we have a code file
	if (count($uploaded) > 0)
	{
	  if ($uploaded[0]['error'] == 0)
	  {
	  $file = $uploaded[0]['name'];
	  $filesize = $uploaded[0]['size'];
	  }
	  else
	  {
	    $message .= $uploaded[0]['message'].'<br />';
	  }
	}
	
// Now see if we have an image file
	if (count($uploaded) > 1)
	{
	  if ($uploaded[1]['error'] == 0)
	  {
		$image = $uploaded[1]['name'];
	  }
	  else
	  {
	    $message .= $uploaded[1]['message'].'<br />';
	  }
	}

// The upload handler checks max file size
	$downloadCategory = intval($_POST['download_category']);
	if (!$downloadCategory)
	{
		$message .= LAN_UL_037.'<br />';
	}

// $message non-null here indicates an error - delete the files to keep things tidy
	if ($message)
	{
	  @unlink($file);
	  @unlink($image);
	}
	else
	{
	  if (USER)
	  {
		$qry = "SELECT user_hideemail FROM #user WHERE user_id=".USERID;
		if(!$sql->db_Select_gen($qry))
		{
		  echo "Fatal database error!";
		  exit;
		}
	    $poster = USERID.".".USERNAME;
		$row = $sql->db_Fetch();
		if ($row['user_hideemail'])
		{
		  $postemail = '-witheld-';
		}
		else
		{
		  $postemail = USEREMAIL;
		}
	  }
	  else
	  {
	    $poster = "0".$tp -> toDB($_POST['file_poster']);
		$postemail = $tp->toDB($_POST['file_email']);
	  }
	  if (($postemail != '-witheld-') && !check_email($postemail))
	  {
	    $message = LAN_UL_001."<br />";
	  }
	  else
	  {
		if ($postemail == '-witheld-') $postemail = '';
		$_POST['file_description'] = $tp->toDB($_POST['file_description']);
		$file_time = time();
		$sql->db_Insert("upload", "0, '".$poster."', '".$postemail."', '".$tp -> toDB($_POST['file_website'])."', '".$file_time."', '".$tp -> toDB($_POST['file_name'])."', '".$tp -> toDB($_POST['file_version'])."', '".$file."', '".$image."', '".$tp -> toDB($_POST['file_description'])."', '".$tp -> toDB($_POST['file_demo'])."', '".$filesize."', 0, '".$downloadCategory."'");
		$edata_fu = array("upload_user" => $poster, "upload_email" => $postemail, "upload_name" => $tp -> toDB($_POST['file_name']),"upload_file" => $file, "upload_version" => $_POST['file_version'], "upload_description" => $tp -> toDB($_POST['file_description']), "upload_size" => $filesize, "upload_category" => $downloadCategory, "upload_website" => $tp -> toDB($_POST['file_website']), "upload_image" => $image, "upload_demo" => $tp -> toDB($_POST['file_demo']), "upload_time" => $file_time);
		$e_event->trigger("fileupload", $edata_fu);
		$message .= "<br />".LAN_404;
	  }
	} 
  }
  else 
  {	// Error - missing data
	require_once(e_HANDLER."message_handler.php");
	message_handler("ALERT", 5);
  }
}

if ($message)
{
	$text = "<div style=\"text-align:center\"><b>".$message."</b></div>";
	$ns->tablerender("", $text);
	require_once(FOOTERF);
	exit;
}


$text = "<div style='text-align:center'>
	<form enctype='multipart/form-data' method='post' onsubmit='return frmVerify()' action='".e_SELF."'>
	<table style='".USER_WIDTH."' class='fborder'>
	<colgroup>
	<col style='width:30%' />
	<col style='width:70%' />
	</colgroup>
	<tr>
	<td class='forumheader3'>".DOWLAN_11.":</td>
	<td class='forumheader3'>";

	require_once(e_FILE."shortcode/batch/download_shortcodes.php");
	$dlparm = (isset($download_category)) ? $download_category : "";
	$text .= $tp->parseTemplate("{DOWNLOAD_CATEGORY_SELECT={$dlparm}}",true,$download_shortcodes);


$text .= "
	</td>
	</tr>

	<tr>
	<td style='text-align:center' colspan='2' class='forumheader3'>";

$text .= "<b>".LAN_406."</b><br />".LAN_419.":";

if (is_readable(e_ADMIN.'filetypes.php')) 
{
  $a_filetypes = trim(file_get_contents(e_ADMIN.'filetypes.php'));
  $a_filetypes = explode(',', $a_filetypes);
  foreach ($a_filetypes as $ftype) 
  {
	$sa_filetypes[] = '.'.trim(str_replace('.', '', $ftype));
  }
  $allowed_filetypes = implode(' | ', $sa_filetypes);
}

$text .= " ".$allowed_filetypes."<br />".LAN_407."<br />
	".LAN_418.($pref['upload_maxfilesize'] ? $pref['upload_maxfilesize'] : ini_get('upload_max_filesize'))."<br />";

$text .= "<span style='text-decoration:underline'>".LAN_408."</span> ".LAN_420."</td>
	</tr>";

if (!USER) 
{	// Prompt for name, email
  $text .= "<tr>
	<td class='forumheader3'>".LAN_61."</td>
	<td class='forumheader3'><input class='tbox' style='width:90%' name='file_poster' type='text' size='50' maxlength='100' value='{$poster}' /></td>
	</tr>

	<tr>
	<td class='forumheader3'><span style='text-decoration:underline'>".LAN_112."</span></td>
	<td class='forumheader3'><input class='tbox' style='width:90%' name='file_email' id='user_email' type='text' size='50' maxlength='100' value='".$postemail."' /></td>
	</tr>";
}

$text .= "
	<tr>
	<td class='forumheader3'><span style='text-decoration:underline'>".LAN_409."</span></td>
	<td class='forumheader3'><input class='tbox' style='width:90%'  name='file_name' id='file_name' type='text' size='50' maxlength='100' /></td>
	</tr>

	<tr>
	<td class='forumheader3'>".LAN_410."</td>
	<td class='forumheader3'><input class='tbox' style='width:90%' name='file_version' type='text' size='10' maxlength='10' /></td>
	</tr>


	<tr>
	<td class='forumheader3'><span style='text-decoration:underline'>".LAN_411."</span></td>
	<td class='forumheader3'><input class='tbox' style='width:90%'  id='file_realpath' name='file_userfile[]' type='file' size='47' /></td>
	</tr>

	<tr>
	<td class='forumheader3'>".LAN_412."</td>
	<td class='forumheader3'><input class='tbox' style='width:90%' name='file_userfile[]' type='file' size='47' /></td>
	</tr>

	<tr>
	<td class='forumheader3'><span style='text-decoration:underline'>".LAN_413."</span></td>
	<td class='forumheader3'><textarea class='tbox' style='width:90%' name='file_description' id='file_description' cols='59' rows='6'></textarea></td>
	</tr>

	<tr>
	<td class='forumheader3'>".LAN_144."</td>
	<td class='forumheader3'><input class='tbox' style='width:90%' name='file_website' type='text' size='50' maxlength='100' value='".(defined(USERURL) ? USERURL : "")."' /></td>
	</tr>

	<tr>
	<td class='forumheader3'>".LAN_414."<br /><span class='smalltext'>".LAN_415."</span></td>
	<td class='forumheader3'><input class='tbox' style='width:90%' name='file_demo' type='text' size='50' maxlength='100' /></td>
	</tr>

	<tr>
	<td style='text-align:center' colspan='2' class='forumheader'><input class='button' type='submit' name='upload' value='".LAN_416."' />
	<input type='hidden' name='e-token' value='".e_TOKEN."' /></td>
	</tr>
	</table>
	</form>
	</div>";

$ns->tablerender(LAN_417, $text);

require_once(FOOTERF);


function headerjs()
{
  $script = "<script type=\"text/javascript\">
		function frmVerify()
		{
			var message = '';
			var spacer = '';
			var testObjects = new Array(\"download_category\", \"user_email\", \"file_name\", \"file_realpath\", \"file_description\");
			var errorMessages = new Array('".LAN_UL_032."', '".LAN_UL_033."', '".LAN_UL_034."', '".LAN_UL_036."', '".LAN_UL_035."');
			var temp;
			var i;
			for (i = 0; i < 5; i++)
			{
				temp = document.getElementById(testObjects[i]);
				if (temp && (temp.value == \"\"))
				{
					message = message + spacer + errorMessages[i];
					spacer = '\\n';
				}
			}
			if (message)
			{
				alert(message);
				return false;
			}
		}
		</script>";
    return $script;
}


?>
