<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once("class2.php");
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);

if (!$pref['upload_enabled'] || $pref['upload_class'] == 255)
{
  header("location: ".e_BASE."index.php");
  exit;
}

if (!defined("USER_WIDTH") && defset('BOOTSTRAP')==false){ define("USER_WIDTH","width:97%"); }

require_once(e_HANDLER.'upload_handler.php');

class userUpload
{
	function __construct()
	{
		

		/*
		e107::css('inline', "
			input[type=file] {
			
			
			}
		"); 
		
		e107::js('inline', "

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
			
		");
		*/
   		
		
		
		
	}
	
	
	public function init()
	{
		$ns = e107::getRender();
		
		$uploadAccess = e107::pref('core','upload_class'); 
		
		if(!check_class($uploadAccess))
		{
			$text = "<div style='text-align:center'>".LAN_UL_002."</div>";
			$ns->tablerender(LAN_UL_020, $text);
			return; 
		}	
		
		
		if (isset($_POST['upload']))
		{							
			$this->processUpload();
			return; 			
		}	
		
		$this->renderForm(); 
		
	}
	
	
	function processUpload()
	{
		$ns = e107::getRender();
		$sql = e107::getDb();
		$mes = e107::getMessage();
		$tp = e107::getParser();
		
		$error = false;
		$postemail ='';
						
	    if (($_POST['file_email'] || USER == TRUE) && $_POST['file_name'] && $_POST['file_description'] && $_POST['download_category'])
	    {

	    //	$uploaded = file_upload(e_FILE."public/", "unique");
	    	$fl = e107::getFile();
	    	$uploaded = $fl->getUploaded(e_UPLOAD, "unique", array('max_file_count' => 2, 'extra_file_types' => TRUE));
	    
	   //      $uploaded = process_uploaded_files(e_UPLOAD, "unique", array('max_file_count' => 2, 'extra_file_types' => TRUE));
	
	   	 	// First, see what errors the upload handler picked up
	        if ($uploaded === FALSE)
	        {
	            $error = true;
	            $mes->addError(LAN_UL_021);
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
	              $error = true;
	              $mes->addError($uploaded[0]['message']);
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
	                $error = true;
	                $mes->addError($uploaded[1]['message']);
	            }
	        }
	
	    	// The upload handler checks max file size
	        $downloadCategory = intval($_POST['download_category']);
	        if (!$downloadCategory)
	        {
	            $error = true;
	            $mes->addError(LAN_UL_037);
	        }
	
	   		 // an error - delete the files to keep things tidy
	        if ($error)
	        {
				@unlink($file);
	         	 @unlink($image);
	        }
	        else
	        {
	            if (USER)
	            {
	                $poster = USERID;
	                $row = e107::getUser()->toArray();
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
	                $poster = "0";//.$tp -> toDB($_POST['file_poster']);
	                $postemail = $tp->toDB($_POST['file_email']);
	            }
	            if (($postemail != '-witheld-') && !check_email($postemail))
	            {
	                $error = true;
	                $mes->addError(LAN_UL_001);
	            }
	            else
	            {
	                if ($postemail == '-witheld-') $postemail = '';
					
	                $_POST['file_description'] = $tp->toDB($_POST['file_description']);
					
	                $file_time = time();
					
	                $sql->insert("upload", "0, '".$poster."', '".$postemail."', '".$tp -> toDB($_POST['file_website'])."', '".$file_time."', '".$tp -> toDB($_POST['file_name'])."', '".$tp -> toDB($_POST['file_version'])."', '".$file."', '".$image."', '".$tp -> toDB($_POST['file_description'])."', '".$tp -> toDB($_POST['file_demo'])."', '".$filesize."', 0, '".$downloadCategory."'");
	                
	                $edata_fu = array("upload_user" => $poster, "upload_email" => $postemail, "upload_name" => $tp -> toDB($_POST['file_name']),"upload_file" => $file, "upload_version" => $_POST['file_version'], "upload_description" => $tp -> toDB($_POST['file_description']), "upload_size" => $filesize, "upload_category" => $downloadCategory, "upload_website" => $tp -> toDB($_POST['file_website']), "upload_image" => $image, "upload_demo" => $tp -> toDB($_POST['file_demo']), "upload_time" => $file_time);
	                
					e107::getEvent()->trigger("fileupload", $edata_fu); // BC
					e107::getEvent()->trigger("user_file_upload", $edata_fu);
					
	                $mes->addSuccess(LAN_404);
	            }
	        }
	    }
	    else
	    {	// Error - missing data
			$mes->addError(LAN_ERROR_29); 
	    }
	
		echo e107::getMessage()->render(); 

	}
		
		
		
	
		
	
	function renderForm()
	{
		/*
		$template = "
		<div class='form-group'>
        <label for='firstname' class='col-sm-3 control-label'>First Name:**</label>
        <div class='col-sm-9'>
                <input id='firstname' class=\"form-control tbox\" type=\"text\" name=\"FIRSTNAME\" size=\"20\" maxlength=\"20\" value=\"". $_POST['FIRSTNAME']. "\" required />
        </div>
     	 </div>	";
		 */
		
		$ns = e107::getRender();
		$tp = e107::getParser();		 
				
		$text = "<div style='text-align:center'>
			<form enctype='multipart/form-data' method='post' onsubmit='return frmVerify()' action='".e_SELF."'>
			<table style='".USER_WIDTH."' class='table fborder'>
			<colgroup>
			<col style='width:30%' />
			<col style='width:70%' />
			</colgroup>
			<tr>
			<td class='forumheader3'>".DOWLAN_11.":</td>
			<td class='forumheader3'>";
		
			require_once(e_CORE."shortcodes/batch/download_shortcodes.php");
			$dlparm = (isset($download_category)) ? $download_category : "";
			$text .= $tp->parseTemplate("{DOWNLOAD_CATEGORY_SELECT={$dlparm}}",true,$download_shortcodes); //FIXME - move to e_upload.php
		
		
		$text .= "</td>
			</tr>
		
			<tr>
			<td style='text-align:center' class='forumheader3'>".LAN_419."</td>
			<td class='forumheader3'>";
		
	//	$text .= "<b>".LAN_406."</b><br />".LAN_419.":";
		
		
		
		$a_filetypes = get_filetypes();
		
		if (count($a_filetypes) == 0)
		{
				$ns->tablerender(LAN_417, LAN_UL_025);
				return; 
		}
		
		$max_upload_size = calc_max_upload_size(-1);		// Find overriding maximum upload size
		$max_upload_size = set_max_size($a_filetypes, $max_upload_size);
		
		
		if (ADMIN)
		{
			$upper_limit = calc_max_upload_size();
			$allowed_filetypes = "<table class='table table-striped table-bordered'><tr><th class='text-center'>".LAN_UL_023."&nbsp;&nbsp;</th><th style='text-align:right'>".LAN_UL_024."</th></tr>";
			
			foreach ($a_filetypes as $type => $size)
			{
		    	$allowed_filetypes .= "<tr><td>{$type}</td><td style='text-align:right'>".eHelper::parseMemorySize($size,0)."</td></tr>";
		  	}
			
		  	$allowed_filetypes .= "</table>";
		}
		else
		{
			$a_filetypes = array_keys($a_filetypes);
			$allowed_filetypes = implode(' | ', $a_filetypes);
		}
		
		$text .= " ".$allowed_filetypes;
		
		$text .= "<div class='alert alert-block alert-danger'>".LAN_407."<br />".LAN_418.eHelper::parseMemorySize($max_upload_size,0)." (".LAN_UL_022.")<br />";
		
		$text .= "<span style='text-decoration:underline'>".LAN_408."</span> ".LAN_420;
		
		$text .= "</div>";
		
		$text .= "</td></tr>"; 
		 
		 //TODO Replcae all form inputs with $frm methods. 
				
		if (!USER) // Prompt for name, email
		{	
		  $text .= "<tr>
			<td class='forumheader3'>".LAN_61."</td>
			<td class='forumheader3'><input class='tbox form-control' style='width:90%' name='file_poster' type='text' size='50' maxlength='100' value='{$poster}' /></td>
			</tr>
		
			<tr>
			<td class='forumheader3'><span style='text-decoration:underline'>".LAN_112."</span></td>
			<td class='forumheader3'><input class='tbox form-control' style='width:90%' name='file_email' id='user_email' type='text' size='50' maxlength='100' value='".$postemail."' required /></td>
			</tr>";
		}

		$text .= "
			<tr>
			<td class='forumheader3'><span style='text-decoration:underline'>".LAN_409."</span></td>
			<td class='forumheader3'><input class='tbox form-control' style='width:90%'  name='file_name' id='file_name' type='text' size='50' maxlength='100' required /></td>
			</tr>
		
			<tr>
			<td class='forumheader3'>".LAN_410."</td>
			<td class='forumheader3'><input class='tbox form-control' style='width:90%' name='file_version' type='text' size='10' maxlength='10' /></td>
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
			<td class='forumheader3'><textarea class='tbox form-control' style='width:90%' name='file_description' id='file_description' cols='59' rows='6' required></textarea></td>
			</tr>
		
			<tr>
			<td class='forumheader3'>".LAN_144."</td>
			<td class='forumheader3'><input class='tbox form-control' style='width:90%' name='file_website' type='text' size='50' maxlength='100' value='".(defined(USERURL) ? USERURL : "")."' /></td>
			</tr>
		
			<tr>
			<td class='forumheader3'>".LAN_414."<br /><span class='smalltext'>".LAN_415."</span></td>
			<td class='forumheader3'><input class='tbox form-control' style='width:90%' name='file_demo' type='text' size='50' maxlength='100' /></td>
			</tr>
		
			<tr>
			<td style='text-align:center' colspan='2' class='forumheader'><input class='btn btn-primary button' type='submit' name='upload' value='".LAN_416."' /></td>
			</tr>
			</table>
			</form>
			</div>";
				
		
		$ns->tablerender(LAN_417, $text);
		
	}
	
	
	//TODO Shortcodes for the form elements above. 
	function sc_author()
	{
		
		return "<input class='tbox' style='width:90%' name='file_poster' type='text' size='50' maxlength='100' value='{$poster}' />"; 	
		
	}
	
}

$up = new userUpload(); 

require_once(HEADERF);

$up->init(); 



require_once(FOOTERF);
exit;




/*

if (!check_class($pref['upload_class']))
{
  $text = "<div style='text-align:center'>".LAN_UL_002."</div>";
  $ns->tablerender(LAN_UL_020, $text);
  require_once(FOOTERF);
  exit;
}
*/






/*









$text = "<div style='text-align:center'>
	<form enctype='multipart/form-data' method='post' onsubmit='return frmVerify()' action='".e_SELF."'>
	<table style='".USER_WIDTH."' class='table fborder'>
	<colgroup>
	<col style='width:30%' />
	<col style='width:70%' />
	</colgroup>
	<tr>
	<td class='forumheader3'>".DOWLAN_11.":</td>
	<td class='forumheader3'>";

	require_once(e_CORE."shortcodes/batch/download_shortcodes.php");
	$dlparm = (isset($download_category)) ? $download_category : "";
	$text .= $tp->parseTemplate("{DOWNLOAD_CATEGORY_SELECT={$dlparm}}",true,$download_shortcodes);


$text .= "
	</td>
	</tr>

	<tr>
	<td style='text-align:center' colspan='2' class='forumheader3'>";

$text .= "<b>".LAN_406."</b><br />".LAN_419.":";

require_once(e_HANDLER.'upload_handler.php');

$a_filetypes = get_filetypes();
if (count($a_filetypes) == 0)
{
  $text .= LAN_UL_025."</td></tr></table>
	</form>
	</div>";
  $ns->tablerender(LAN_417, $text);
  require_once(FOOTERF);
}
$max_upload_size = calc_max_upload_size(-1);		// Find overriding maximum upload size
$max_upload_size = set_max_size($a_filetypes, $max_upload_size);


if (ADMIN)
{
  $upper_limit = calc_max_upload_size();
  $allowed_filetypes = "<table><tr><td>".LAN_UL_023."&nbsp;&nbsp;</td><td style='text-align:right'>".LAN_UL_024."</td></tr>";
  foreach ($a_filetypes as $type => $size)
  {
    $allowed_filetypes .= "<tr><td>{$type}</td><td style='text-align:right'>".$e107->parseMemorySize($size,0)."</td></tr>";
  }
  $allowed_filetypes .= "</table>";
}
else
{
  $a_filetypes = array_keys($a_filetypes);
  $allowed_filetypes = implode(' | ', $a_filetypes);
}

$text .= " ".$allowed_filetypes."<br />".LAN_407."<br />
	".LAN_418.$e107->parseMemorySize($max_upload_size,0)." (".LAN_UL_022.")<br />";

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
	<td style='text-align:center' colspan='2' class='forumheader'><input class='btn btn-default button' type='submit' name='upload' value='".LAN_416."' /></td>
	</tr>
	</table>
	</form>
	</div>";

$ns->tablerender(LAN_417, $text);

require_once(FOOTERF);


*/


?>
