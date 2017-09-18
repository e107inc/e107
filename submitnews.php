<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

 
if(!empty($_POST) && !isset($_POST['e-token']))
{
	// set e-token so it can be processed by class2
	$_POST['e-token'] = '';
} 
require_once("class2.php");
e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);

require_once(HEADERF);

if (!isset($pref['subnews_class']))
{
	$pref['subnews_class'] = e_UC_MEMBER;
}


if (!check_class($pref['subnews_class']))
{
	e107::getRender()->tablerender(NWSLAN_12, NWSLAN_11);
	require_once(FOOTERF);
	exit;
}


if (!defined("USER_WIDTH")) { define("USER_WIDTH","width:95%"); }


class submitNews
{

	private $minWidth = 1024;
	private $minHeight = 768;


	function __construct()
	{

		$mes = e107::getMessage();

		$minDimensions = e107::pref('core','subnews_attach_minsize',false);

		if(empty($minDimensions))
		{
			$this->minWidth = 0;
			$this->minHeight = 0;
		}
		else
		{
			$tmp = explode('×',$minDimensions);
			$this->minWidth = intval($tmp[0]);
			$this->minHeight = intval($tmp[1]);
		}

		if(isset($_POST['submitnews_submit']) && !empty($_POST['submitnews_title']) && !empty($_POST['submitnews_item']))
		{
			$this->process();
		}

		echo $mes->render();

		$this->form();
	}


	function process()
	{
		$ip = e107::getIPHandler()->getIP(FALSE);
		$tp = e107::getParser();
		$pref = e107::pref('core');
		$sql = e107::getDb();
		$mes = e107::getMessage();

		$fp = new floodprotect;

		if ($fp->flood("submitnews", "submitnews_datestamp") == false)
		{
			e107::redirect();
			exit;
		}

		$submitnews_user  = (USER ? USERNAME  : trim($tp->toDB($_POST['submitnews_name'])));
		$submitnews_email = (USER ? USEREMAIL : trim(check_email($tp->toDB($_POST['submitnews_email']))));
		$submitnews_title = $tp->filter($_POST['submitnews_title']);
		$submitnews_item  = $tp->filter($_POST['submitnews_item']);
	//	$submitnews_item  = str_replace("src=&quot;e107_images", "src=&quot;".SITEURL."e107_images", $submitnews_item);
		$submitnews_file  = "";
		$submitnews_error = false;
		$submitnews_filearray = array();

		if (!$submitnews_user || !$submitnews_email)
		{
			$message = SUBNEWSLAN_7;
			$submitnews_error = TRUE;
		}

		// ==== Process File Upload ====
		if (FILE_UPLOADS && !empty($_FILES['file_userfile']['name'][0]) && vartrue($pref['subnews_attach']) && vartrue($pref['upload_enabled']) && check_class($pref['upload_class']))
		{
			$uploaded = e107::getFile()->getUploaded(e_UPLOAD, 'unique', array('file_mask' => 'jpg,gif,png', 'max_file_count' => 3));

			if (empty($uploaded)) // Non-specific error
			{
				$submitnews_error = true;
				$message = SUBNEWSLAN_8;
			}
			else
			{
				foreach($uploaded as $c=>$v)
				{
					// Check if images is too small.
					if(!empty($this->minWidth) && !empty($v['img-width']) && (intval($v['img-width']) < $this->minWidth || intval($v['img-width']) < $this->minHeight))
					{
						//TODO Lan and review wording.
						$mes->addWarning("One of your images has dimensions smaller than ".$this->minWidth."px  x ".$this->minHeight."px. Please correct the attachment and submit the form again. ");

						return false;
					}

					if (varset($uploaded[$c]['error'],0) != 0)
					{
						$submitnews_error = TRUE;
						$message = handle_upload_messages($uploaded);
					}
					else
					{
						if (isset($uploaded[$c]['name']) && isset($uploaded[$c]['type']) && isset($uploaded[$c]['size']))
						{
							$filename = $uploaded[$c]['name'];
							$filetype = $uploaded[$c]['type'];
							$filesize = $uploaded[$c]['size'];
							$fileext  = substr(strrchr($filename, "."), 1);

							$today = getdate();

							$submitnews_file = USERID."_".$today[0]."_".$c."_".str_replace(" ", "_", substr($submitnews_title, 0, 6)).".".$fileext;

							if (is_numeric($pref['subnews_resize']) && ($pref['subnews_resize'] > 30)  && ($pref['subnews_resize'] < 5000))
							{
								require_once(e_HANDLER.'resize_handler.php');

								if (!resize_image(e_UPLOAD.$filename, e_UPLOAD.$submitnews_file, $pref['subnews_resize']))
								{
								  rename(e_UPLOAD.$filename, e_UPLOAD.$submitnews_file);
								}
							}
							elseif ($filename)
							{
								rename(e_UPLOAD.$filename, e_UPLOAD.$submitnews_file);
							}
						}
					}

					if ($filename && file_exists(e_UPLOAD.$submitnews_file))
					{
						$submitnews_filearray[] = $submitnews_file;
					}

				}
			}

		}

		if ($submitnews_error === false)
		{

			$insertQry = array(
				'submitnews_id'             => 0,
				'submitnews_name'           => $submitnews_user,
				'submitnews_email'          => $submitnews_email,
				'submitnews_user'           => USERID,
				'submitnews_title'          => $submitnews_title,
				'submitnews_category'       => intval($_POST['cat_id']),
				'submitnews_item'           => $submitnews_item,
				'submitnews_datestamp'      => time(),
				'submitnews_ip'             => $ip,
				'submitnews_auth'           => '0',
				'submitnews_file'           => implode(',',$submitnews_filearray),
				'submitnews_keywords'       => $tp->filter($_POST['submitnews_keywords'], 'str'),
                'submitnews_description'    => $tp->filter($_POST['submitnews_description'], 'str'),
                'submitnews_summary'        => $tp->filter($_POST['submitnews_summary'], 'str'),
                'submitnews_media'          => json_encode($_POST['submitnews_media'],JSON_PRETTY_PRINT)
			);

			if(!$sql->insert("submitnews", $insertQry))
			{
				$mes->addError(LAN_134);
				return false;
			}


		//	$sql->insert("submitnews", "0, '$submitnews_user', '$submitnews_email', '$submitnews_title', '".intval($_POST['cat_id'])."', '$submitnews_item', '".time()."', '$ip', '0', '".implode(',',$submitnews_filearray)."' ");

			$edata_sn = array("user" => $submitnews_user, "email" => $submitnews_email, "itemtitle" => $submitnews_title, "catid" => intval($_POST['cat_id']), "item" => $submitnews_item, "image" => $submitnews_file, "ip" => $ip);

			e107::getEvent()->trigger("subnews", $edata_sn); // bc
			e107::getEvent()->trigger("user_news_submit", $edata_sn);


			$mes->addSuccess(LAN_134);
		//	echo $mes->render();
			unset($_POST);

			// $ns->tablerender(LAN_THANK_YOU, "<div style='text-align:center'>".LAN_134."</div>");

		}
		else
		{
		//	message_handler("P_ALERT", $message);
			$mes->addWarning($message);
		}
	}


	function form()
	{

		$tp = e107::getParser();
		$sql = e107::getDb();
		$ns = e107::getRender();
		$pref = e107::pref('core');
		$frm = e107::getForm();

		$text = "";

		if (!empty($pref['news_subheader']))
		{
			$text .= $tp->toHTML($pref['news_subheader'], true, "BODY");
		}


		$text .= "
			<div>
			  <form id='dataform' method='post' action='".e_SELF."' enctype='multipart/form-data' onsubmit='return frmVerify()'>
			    <table class='table fborder'>";



			if (!USER)
			{
			    $text .= "
				  <tr>
				    <td style='width:20%' class='forumheader3'>".LAN_NAME."</td>
				    <td style='width:80%' class='forumheader3'>
				      <input class='tbox' type='text' name='submitnews_name' size='60' value='".$tp->toHTML($_POST['submitnew_name'],FALSE,'USER_TITLE')."' maxlength='100' required />
				    </td>
				  </tr>
				  <tr>
				    <td style='width:20%' class='forumheader3'>".LAN_EMAIL."</td>
				    <td style='width:80%' class='forumheader3'>
				      <input class='tbox' type='text' name='submitnews_email' size='60' value='".$tp->filter($_POST['submitnews_email'], 'email')."' maxlength='100' required />
				    </td>
				  </tr>";
			}

			$text .= "
			<tr>
			  <td style='width:20%' class='forumheader3'>".LAN_CATEGORY."</td>
				<td style='width:80%' class='forumheader3'>";

			if (!$sql->select("news_category"))
			{
				$text .= NWSLAN_10;
			}
			else
			{
				$text .= "<select name='cat_id' class='tbox form-control'>";
				while (list($cat_id, $cat_name, $cat_icon) = $sql->fetch('num'))
				{
					$sel = (varset($_POST['cat_id'],'') == $cat_id) ? "selected='selected'" : "";
					$text .= "<option value='{$cat_id}' {$sel}>".$tp->toHTML($cat_name, FALSE, "defs")."</option>";
				}
				$text .= "</select>";
			}

			$text .= "
			  </td>
			</tr>
			<tr>
			  <td style='width:20%' class='forumheader3'>".LAN_TITLE."</td>
				<td style='width:80%' class='forumheader3'>".e107::getForm()->text('submitnews_title',$tp->toHTML(vartrue($_POST['submitnews_title']),TRUE,'USER_TITLE'),200, array('required'=>1))."
			    </td>
			</tr>
			<tr>
			    <td style='width:20%' class='forumheader3'>".LAN_135."</td>
				<td style='width:80%' class='forumheader3'>
					".e107::getForm()->bbarea('submitnews_item', $tp->toForm(vartrue($_POST['submitnews_item'])),null, null, 'large')."
				</td>
			</tr>
			";



			/*  submitnews_keywords  varchar(255) NOT NULL default '',
  submitnews_description text NOT NULL,
  submitnews_summary text NOT NULL,
  submitnews_media text NOT NULL,
			*/
			$fields = array();
			$fields['submitnews_keywords']      = array('title'=>SUBNEWSLAN_9, 'type'=>'tags');
			$fields['submitnews_summary']       = array('title'=>LAN_SUMMARY, 'type'=>'text', 'writeParms'=>array('maxlength'=>255, 'size'=>'xxlarge'));
			$fields['submitnews_description']   = array('title'=>SUBNEWSLAN_11, 'type'=>'textarea','writeParms'=>array('placeholder'=>SUBNEWSLAN_12));
			$fields['submitnews_media']         = array('title'=>SUBNEWSLAN_13, 'type'=>'method', 'method'=>'submitNewsForm::submitnews_media');


			foreach($fields as $key=>$fld)
			{
				$text .= "<tr><td style='width:20%' class='forumheader3'>
							".$fld['title']
							."</td>
							<td style='width:80%' class='forumheader3'>".$frm->renderElement($key, '', $fld)."</td>
						</tr>";

			}

			if ($pref['subnews_attach'] && $pref['upload_enabled'] && check_class($pref['upload_class']) && FILE_UPLOADS)
			{
				  $text .= "
				  <tr>
				    <td style='width:20%' class='forumheader3'>".SUBNEWSLAN_5."<br /><span class='smalltext'>".SUBNEWSLAN_6."</span>";




				   $text .= "
				    </td>
				    <td style='width:80%' class='forumheader3'>

				      <input class='tbox' type='file' name='file_userfile[]' multiple='multiple' />
				      ";

				     if(!empty($this->minWidth))
				   {
				        $text .= "<div class='alert alert-warning'>Minimum Dimensions: ".$this->minWidth."px × ".$this->minHeight."px</div>";
				   }

				      $text .= "
				    </td>
				  </tr>";
			}

			$text .= "
			      <tr>
			        <td colspan='2' style='text-align:center' class='forumheader'>
			          <input class='btn btn-success button' type='submit' name='submitnews_submit' value='".LAN_136."' />
			           <input type='hidden' name='e-token' value='".e_TOKEN."' />
			        </td>
			      </tr>
			    </table>
			  </form>
			</div>";

			$ns->tablerender(LAN_136, $text);


	}




}

class submitNewsForm extends e_form
{

	function submitnews_media($cur, $mode, $att)
	{
		$text = '';

		$placeholders = array(
			'eg. http://www.youtube.com/watch?v=Mxhn11_fzJQ',
			'eg. http://path-to-image/image.jpg',
			'eg. http://path-to-audio/file.mp3'
		);

		for($i = 0; $i <8; $i++)
		{
			$help = (isset($placeholders[$i])) ? $placeholders[$i] : '';
			$text .= "<div class='form-group'>";
			$text .= $this->text('submitnews_media['.$i.']', $_POST['submitnews_media'][$i], 255, array('placeholder'=>$help) );
			$text .= "</div>";
		}

		return $text;

	}


}


new submitNews;


if(!vartrue($pref['subnews_htmlarea'])) // check after bbarea is called.
{
	e107::wysiwyg(false);
}

require_once(FOOTERF);



?>
