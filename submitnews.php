<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

 
require_once("class2.php");
e107::coreLan('submitnews');

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
		$submitnews_item  = $tp->toDB($_POST['submitnews_item']);
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
						$rejected = defset('SUBNEWSLAN_ATTACH_TOO_SMALL', 'One of your images has dimensions smaller than [x]px × [y]px. Please correct the attachment and submit the form again.');
						$mes->addWarning(str_replace(array('[x]', '[y]'), array($this->minWidth, $this->minHeight), $rejected));

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
							$fileext  = (string) substr(strrchr($filename, "."), 1);

							$today = getdate();

							$titleSlug = preg_replace('/[^\w\pL]/u', '_', $tp->usubstr($submitnews_title, 0, 6));
							$submitnews_file = USERID."_".$today[0]."_".$c."_".$titleSlug.".".$fileext;

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

			$insertResult = $sql->createQueryBuilder()->insert('submitnews')
				->valuesTyped($insertQry, $sql->getFieldDefs('submitnews')['_FIELD_TYPES'])
				->execute();

			if(!$insertResult)
			{
				$mes->addError(LAN_134);
				return false;
			}



			$edata_sn = array("user" => $submitnews_user, "email" => $submitnews_email, "itemtitle" => $submitnews_title, "catid" => intval($_POST['cat_id']), "item" => $submitnews_item, "image" => $submitnews_file, "ip" => $ip);

			e107::getEvent()->trigger("subnews", $edata_sn); // bc
			e107::getEvent()->trigger("user_news_submit", $edata_sn);


			$mes->addSuccess(LAN_134);
			unset($_POST);


		}
		else
		{
			$mes->addWarning($message);
		}
	}


	function form()
	{
		$sc = e107::getScBatch('submitnews');

		$sc->setVars(array(
			'categories' => e107::getDb()->createQueryBuilder()
				->select('category_id', 'category_name')->from('news_category')
				->fetchAll(),
			'min_width'  => $this->minWidth,
			'min_height' => $this->minHeight,
		));

		$sc->wrapper('submitnews/form');

		$template = e107::getCoreTemplate('submitnews', null, true, true);

		e107::getRender()->tablerender(LAN_136, e107::getParser()->parseTemplate(varset($template['form'], ''), true, $sc));
	}


}

class submitNewsForm extends e_form
{

	/**
	 * @deprecated 2.4.0 Forwards to {@see submitnews_shortcodes::sc_submitnews_media()}, which the form now calls directly.
	 * @return string
	 */
	function submitnews_media($cur, $mode, $att)
	{
		return e107::getScBatch('submitnews')->sc_submitnews_media();
	}


}


new submitNews;


if(!vartrue($pref['subnews_htmlarea'])) // check after bbarea is called.
{
	e107::wysiwyg(false);
}

require_once(FOOTERF);



?>
