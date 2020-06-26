<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	File Upload facility - administration
 *
 */

require_once('../class2.php');
if (!getperms('V')) 
{
  e107::redirect('admin');
  exit;
}

e107::lan('core','upload','admin');

$e_sub_cat = 'upload';


// Generated e107 Plugin Admin Area 

class upload_admin extends e_admin_dispatcher
{

	protected $modes = array(	
	
		'main'	=> array(
			'controller' 	=> 'upload_ui',
			'path' 			=> null,
			'ui' 			=> 'upload_form_ui',
			'uipath' 		=> null
		),

	);	
	
	
	protected $adminMenu = array(

		'main/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'V'),
		// 'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => 'V'),
	//	'main/create'		=> array('caption'=> LAN_CREATE, 'perm' => 'V'),
			
	/*
		'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => 'P'),
		'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
	*/	

	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = LAN_UPLOAD;

	protected $adminMenuIcon = 'e-uploads-24';
}




				
class upload_ui extends e_admin_ui
{
			
		protected $pluginTitle		= LAN_UPLOAD;
		protected $pluginName		= 'core';
		protected $table			= 'upload';
		protected $pid				= 'upload_id';
		protected $perPage 			= 10; 
			
		protected $fields = array (
            'checkboxes'            =>   array ( 'title' => '', 'type' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => true, 'class' => 'center', 'toggle' => 'e-multiselect',  ),
            'upload_id'             =>   array ( 'title' => LAN_ID, 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
            'upload_datestamp'      =>   array ( 'title' => LAN_DATESTAMP, 'type' => 'datestamp', 'data' => 'int', 'width' => '15%', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
            'upload_name'           =>   array ( 'title' => LAN_TITLE, 'type' => 'text', 'data' => 'str', 'width' => '15%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left', 'validate' => true, 'inline' => true),
           'upload_email'          =>   array ( 'title' => LAN_EMAIL, 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
            'upload_website'        =>   array ( 'title' => LAN_URL, 'type' => 'url', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
            'upload_version'        =>   array ( 'title' => LAN_VERSION, 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
            'upload_file'           =>   array ( 'title' => LAN_FILE, 'type' => 'text', 'data' => 'str', 'width' => '15%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left', 'validate' => true ),
            'upload_ss'             =>   array ( 'title' => 'Ss', 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
            'upload_description'    =>   array ( 'title' => LAN_DESCRIPTION, 'type' => 'textarea', 'data' => 'str', 'width' => '30%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
            'upload_poster'         =>   array ( 'title' => UPLLAN_5, 'type' => 'user', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),

            'upload_demo'           =>   array ( 'title' => UPLLAN_14, 'type' => 'url', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
            'upload_filesize'       =>   array ( 'title' => LAN_SIZE, 'type' => 'method', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
            'upload_active'         =>   array ( 'title' => UPLLAN_69, 'type' => 'method', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => array('singleOption' => true), 'class' => 'center', 'thclass' => 'center',  'batch' => true, 'filter'=>true),
            'upload_category'       =>   array ( 'title' => LAN_CATEGORY, 'type' => 'method', 'data' => 'int', 'width' => 'auto', 'batch' => true, 'filter' => true, 'help' => '', 'readParms' => array(), 'writeParms' => array(), 'class' => 'left', 'thclass' => 'left', 'validate' => true ),
            'upload_owner'          =>   array ( 'title' => LAN_OWNER, 'type' => 'text', 'readonly'=>true, 'data' => 'str', 'width' => '10%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),

            'options'               =>   array ( 'title' => LAN_OPTIONS, 'type' => '', 'data' => '', 'width' => '140px', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',  ),
		);		
		
		protected $fieldpref = array('checkboxes', 'upload_datestamp', 'upload_name', 'upload_description', 'upload_file', 'upload_filesize', 'upload_poster','upload_name', 'upload_category', 'upload_owner', 'upload_active', 'options');
/*
		protected $prefs = array(
			'upload_categories'	   	=> array('title'=> 'Display Contact info on Contact form', 'type'=>'checkboxes', 'data'=>'int'),

			//	'classic_look'				=> array('title'=> 'Use Classic Layout', 'type'=>'boolean')
		);
*/


		
	public $categories = array();
		
    // optional
    public function init()
    {

        $this->categories = e107::getAddonConfig('e_upload','','category');

     //   $this->prefs['upload_categories']['writeParms']['optArray'] = array(1,2,3,4);


       /* $qry = "
        SELECT dc.download_category_name, dc.download_category_id
        FROM #download_category AS dc
        WHERE dc.download_category_parent = 0
        ORDER by dc.download_category_order ASC";
        $cats = e107::getDb('dc')->retrieve($qry, null, null, true, 'download_category_id');

        $parentIndex = array_keys($cats);
        $subIndex = array();

        $qry = "
        SELECT dc.download_category_name, dc.download_category_parent, dc.download_category_id
        FROM #download_category AS dc
        WHERE dc.download_category_parent != 0
        ORDER by dc.download_category_order ASC";
        if(e107::getDb('dc')->gen($qry))
        {
            while($row = e107::getDb('dc')->fetch())
            {
                $subIndex[$row['download_category_parent']][] = $row['download_category_id'];
                $cats[$row['download_category_id']] = $row;
            }
        }

        foreach ($parentIndex as $id)
        {
            $parent = $cats[$id];
            $label = e107::getParser()->toHTML($parent['download_category_name'], false, 'TITLE');
            $this->addSubcategories($id, $cats, $subIndex, $label);
        }*/
    }


    private function addSubcategories($parent_id, &$cats, $subIndex, $label)
    {
        if(isset($subIndex[$parent_id]))
        {
            foreach ($subIndex[$parent_id] as $sub_id)
            {
                $cat = $cats[$sub_id];
                $_label = e107::getParser()->toHTML($cat['download_category_name'], false, 'TITLE');
                if($cat['download_category_parent'] && isset($subIndex[$sub_id]))
                {
                    $this->addSubcategories($sub_id, $cats, $subIndex, $label.' / '.$_label);
                }
                else
                {
                    if($this->getAction() == 'list')
                    {
                        $this->fields['upload_category']['writeParms'][$sub_id] = $label.' / '.$_label;
                    }
                    else
                    {
                        $this->fields['upload_category']['writeParms'][$label][$sub_id] = $_label;
                    }
                }
            }
        }
    }

    protected function handleListUploadActiveBatch($selected, $value = null)
    {
        $ids = array_map('intval', array_values($selected));
        foreach ($ids as $id)
        {
            $model = $this->getTreeModel()->getNode($id);
            if($model)
            {
                $data = $model->toArray();
                $data['upload_active'] = 1;
                $this->afterUpdate($data, $data, $id);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeUpdate($new_data, $old_data, $id)
    {

        if($new_data['upload_active'] && !e107::isInstalled('download'))
        {
            $this->getModel()->addValidationError(UPLLAN_62);
			$new_data['upload_active'] = 0;
            return $new_data;
		}

		// Make sure the upload_category contains only integers
		// Make sure the owner correspondents to the category id
		list($catOwner, $catID) = explode("__", $new_data['upload_category'], 2);
		$new_data['upload_category'] = intval($catID);
		$new_data['upload_owner'] = $catOwner;

		return $new_data;
    }

    /**
     * @inheritdoc
     */
    public function afterUpdate($new_data, $old_data, $id)
    {
		
        $did = $this->move2download($new_data);
        $isSession = vartrue($_POST['__after_submit_action']) && !isset($_POST['e__execute_batch']) != 'edit' ? true : false;
        if($did)
        {
            $sql = e107::getDb('activate');
            if(!$sql->update('upload', 'upload_active = 1 WHERE upload_id='.$id))
            {
                e107::getMessage()
                    ->addError(UPLLAN_68.' #'.$sql->getLastErrorNumber().' '.$sql->getLastErrorText(), 'default', $isSession)
                    ->addDebug($sql->getLastQuery(), 'default', $isSession);
            }
            else
            {
                $owner = varset($new_data['upload_owner'],'download');
                $obj = e107::getAddon($owner,'e_upload');
                $config = $obj->config();
                $url = str_replace('{ID}',$did, $config['url']);

                $link = '<br><a href="'.$url.'">'.UPLLAN_64.'</a>'; //FIXME Needs generic LAN for all areas, not just downloads.
                $message = str_replace('[x]', $link, UPLLAN_63);

                e107::getMessage()->addSuccess($message, 'default', $isSession);
            }

        }
    }

    /**
     * @inheritdoc
     */
    public function afterDelete($deleted_data, $id, $deleted_check)
    {
        if($deleted_check)
        {
            $uploadFile = e_UPLOAD.$deleted_data['upload_file'];
            $uploadImage = e_UPLOAD.$deleted_data['upload_ss'];
            @unlink($uploadFile);
            @unlink($uploadImage);
        }
    }

    protected function move2download($upload)
    {

        if(!$upload['upload_active'])
        {
            return 0;
        }

		// Make sure the owner is not empty
        $owner = vartrue($upload['upload_owner'], 'download');

        $uploadObj = e107::getAddon($owner,'e_upload');

        $config =  $uploadObj->config(); // import configuration from e_upload

        $mediaFile = varset($config['media']['file'],'_common_file');
        $mediaImage = varset($config['media']['preview'], '_common_image');


        $media = e107::getMedia();
        $uploadPath = e_UPLOAD;
        if(!file_exists($uploadPath.$upload['upload_file']))
        {
            $this->getModel()->addValidationError(LAN_FILE_NOT_FOUND);
            return false;
        }

        $downloadPath = $media->importFile($upload['upload_file'], $mediaFile, $uploadPath.$upload['upload_file'], array('media_caption' => $upload['upload_name']));
        if(false === $downloadPath)
        {
            $this->getModel()->addValidationError(UPLLAN_66);
            return false;
        }

        $imagePath = null;
        if($upload['upload_ss'] && file_exists($uploadPath.$upload['upload_ss']))
        {
            $imagePath = $media->importFile($upload['upload_ss'], $mediaImage, $uploadPath.$upload['upload_ss'], array('media_caption' => $upload['upload_name'].' '.LAN_PREVIEW));
        }

        $author = $upload['upload_poster'] ? e107::getSystemUser($upload['upload_poster'])->getRealName() : LAN_ANONYMOUS;

		$upload['upload_ss'] = $imagePath;
		$upload['upload_file'] = $downloadPath;
		$upload['upload_poster'] = $author;

        $dl =  $uploadObj->insert($upload);

        $sql = e107::getDb('activate');

        if(!empty($dl) && !empty($config['table']))
        {
             $id = $sql->insert($config['table'], $dl);

	        if(!$id)
	        {
	            $this->getModel()->addValidationError(UPLLAN_68.' #'.$sql->getLastErrorNumber().' '.$sql->getLastErrorText());
	            e107::getMessage()->addDebug($sql->getLastQuery());
	            return null;
	        }

            return $id;
        }
        else
        {
            e107::getMessage()->addDebug('table: '.$config['table']);
            e107::getMessage()->addDebug('data: '.print_a($dl,true));

            return false;
        }


    }

}
				


class upload_form_ui extends e_admin_form_ui
{
	private function findKey($owner, $array,$value)
	{
		$searchKey = $owner."__".$value;

		$ret = null;

		foreach($array as $k=>$v)
		{
			if(is_array($v))
			{
				$ret = $this->findKey($owner,$v,$value);
			}
			elseif($k == $searchKey)
			{
				$ret = $v;
			}

		}

		return $ret;
	//	return print_a($array,true);
	}


    public function upload_category($value, $type, $options = array())
    {

        $opts =  $this->getController()->categories;

        switch($type)
        {
            case 'read':
                  $owner =  $this->getController()->getListModel()->get('upload_owner');
             return $this->findKey($owner, $opts[$owner], $value);
            break;

	        case 'write':
	            $owner =  $this->getController()->getModel()->get('upload_owner');
				//return $value."-- ".$owner; // $this->radio_switch('upload_active', $value, LAN_ACCEPT, LAN_PENDING, $options);
				// make category editable instead of just displaying data
				return e107::getForm()->select('upload_category', $opts, $value);
            break;

            case 'batch':

				return array();

				$pref = e107::getAddonConfig('e_upload');

				$tp = e107::getParser();

				$lan = UPLLAN_70;
				$text = '';
				foreach($pref as $k=>$v)
				{
					$def = $v['name'];
					$diz = $tp->lanVars($lan,$def);
					$text .=  $this->option($diz, 'send_to_'.$k, false, array('other' => 'style="padding-left: 15px"'));
				}

				return $text;
			//	$text =  $this->option(LAN_ACCEPT, 'upload_active', false, array('other' => 'style="padding-left: 15px"'));


          //      return $text; // $this->option('Accept', 'upload_active', false, array('other' => 'style="padding-left: 15px"'));
            break;
        }
    }





    public function upload_active($value, $type, $options = array())
    {
        switch($type)
        {
            case 'write':
                return $this->radio_switch('upload_active', $value, LAN_ACCEPT, LAN_PENDING, $options);
            break;

            case 'read':
                return $value ? ADMIN_TRUE_ICON : ADMIN_FALSE_ICON;
            break;

            case 'batch':
				
				//TODO move all 'downloads' specific code into e_upload.php . 
				/*
				$pref = e107::pref('core', 'e_upload_list');
				foreach($pref as $k=>$v)
				{
					$def = 'LAN_PLUGIN_'.strtoupper($v).'_NAME';
					$text =  $this->option('Send to '.defset($def,$v), 'send_to_'.$k, false, array('other' => 'style="padding-left: 15px"'));
				}
				*/
				
				$text =  $this->option(LAN_ACCEPT, 'upload_active', false, array('other' => 'style="padding-left: 15px"'));
	
				
                return $text; // $this->option('Accept', 'upload_active', false, array('other' => 'style="padding-left: 15px"'));
            break;

	        case 'filter':
	            return array(0=>LAN_NO, 1=>LAN_YES);
	        break;
        }
    }

	public function upload_filesize($value, $type, $options = array())
    {
        switch($type)
        {


            case 'read':
            case 'write':
                return e107::getFile()->file_size_encode($value);
            break;

            case 'batch':

            break;
        }
    }
}

		
new upload_admin();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;


































$action = 'list';			// Default action
if (e_QUERY) 
{
  $tmp = explode('.', e_QUERY);
  $action = $tmp[0];
  $id = varset($tmp[1],0);
}



if ($action == "dis" && isset($_POST['updelete']['upload_'.$id]) ) 
{
	$res = $sql -> db_Select("upload", "*", "upload_id='".intval($id)."'");
	$row = $sql -> db_Fetch();
	if (preg_match("#Binary (.*?)/#", $row['upload_file'], $match)) 
	{
		$sql -> db_Delete("rbinary", "binary_id='".$tp -> toDB($match[1])."'");
	} 
	else if ($row['upload_file'] && file_exists(e_UPLOAD.$row['upload_file'])) 
	{
		unlink(e_UPLOAD.$row['upload_file']);
	}
	if (preg_match("#Binary (.*?)/#", $row['upload_ss'], $match)) 
	{
		$sql -> db_Delete("rbinary", "binary_id='".$tp -> toDB($match[1])."'");
	} 
	else if ($row['upload_ss'] && file_exists(e_FILE."public/".$row['upload_ss'])) 
	{
		unlink(e_UPLOAD.$row['upload_ss']);
	}
	$message = ($sql->db_Delete("upload", "upload_id='".intval($id)."'")) ? UPLLAN_1 : LAN_DELETED_FAILED;
	e107::getLog()->add('UPLOAD_01',$row['upload_file'],E_LOG_INFORMATIVE,'');
}

if ($action == "dlm") 
{
  header("location: ".e_ADMIN."download.php?dlm.".$id);
  exit;
}

if ($action == "news") 
{
  header("location: ".e_ADMIN."newspost.php?create.upload.".$id);
  exit;
}


if ($action == "dl") 
{
	$id = str_replace("%20", " ", $id);

//	if (preg_match("/Binary\s(.*?)\/.*/", $id, $result))
//	{
//		$bid = $result[1];
//		$result = @mysql_query("SELECT * FROM ".MPREFIX."rbinary WHERE binary_id='$bid' ");
//		$binary_data = @mysql_result($result, 0, "binary_data");
//		$binary_filetype = @mysql_result($result, 0, "binary_filetype");
//		$binary_name = @mysql_result($result, 0, "binary_name");
//		header("Content-type: ".$binary_filetype);
//		header("Content-length: ".$download_filesize);
//		header("Content-Disposition: attachment; filename=".$binary_name);
//		header("Content-Description: PHP Generated Data");
//		echo $binary_data;
//		exit;
//	}
//	else
//	{
//		header("location:".e_UPLOAD.str_replace("dl.", "", e_QUERY));
//		exit;
//	}
}

require_once(e_HANDLER.'upload_handler.php');
require_once("auth.php");
require_once(e_HANDLER.'userclass_class.php');
$gen = new convert;
require_once(e_HANDLER.'form_handler.php');
$rs = new form;


// Need the userclass object for class selectors
if (!is_object($e_userclass)) { $e_userclass = new user_class; }


if (isset($_POST['optionsubmit'])) 
{
	$temp = array();
	$temp['upload_storagetype'] = $_POST['upload_storagetype'];
	$temp['upload_maxfilesize'] = $_POST['upload_maxfilesize'];
	$temp['upload_class'] = $_POST['upload_class'];
	$temp['upload_enabled'] = (FILE_UPLOADS ? $_POST['upload_enabled'] : 0);
	if ($temp['upload_enabled'] && !$sql->db_Select("links", "*", "link_url='upload.php' ")) 
	{
	  $sql->db_Insert("links", "0, '".LAN_UPLOAD."', 'upload.php', '', '', 1,0,0,0,0");
	}

	if (!$temp['upload_enabled'] && $sql->db_Select("links", "*", "link_url='upload.php' ")) 
	{
		$sql->db_Delete("links", "link_url='upload.php' ");
	}

	if ($admin_log->logArrayDiffs($temp, $pref, 'UPLOAD_02'))
	{
		save_prefs();		// Only save if changes
		$message = UPLLAN_2;
	}
	else
	{
		$message = UPLLAN_4;
	}
}

if (isset($message)) 
{
  require_once(e_HANDLER.'message_handler.php');
  message_handler("ADMIN_MESSAGE", $message);
}

if (!FILE_UPLOADS) 
{
  message_handler("ADMIN_MESSAGE", UPLLAN_41);
}


switch ($action)
{
  case 'filetypes' :
	if(!getperms('0')) exit;

	$definition_source = LAN_DEFAULT;
	$source_file = '';
	$edit_upload_list = varset($_POST['upload_do_edit'],FALSE);

	if (isset($_POST['generate_filetypes_xml']))
	{  // Write back edited data to filetypes_.xml
	  $file_text = "<e107Filetypes>\n";
	  foreach ($_POST['file_class_select'] as $k => $c)
	  {
		if (!isset($_POST['file_line_delete_'.$c]) && vartrue($_POST['file_type_list'][$k]))
		{
//		  echo "Key: {$k} Class: {$c}  Delete: {$_POST['file_line_delete'][$k]}  List: {$_POST['file_type_list'][$k]}  Size: {$_POST['file_maxupload'][$k]}<br />";
		  $file_text .= "    <class name='{$c}' type='{$_POST['file_type_list'][$k]}' maxupload='".vartrue($_POST['file_maxupload'][$k],ini_get('upload_max_filesize'))."' />\n";
		}
	  }
	  $file_text .= "</e107Filetypes>\n";
	  if ((($handle = fopen(e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES,'wt')) == FALSE) 
	  || (fwrite($handle,$file_text) == FALSE) 
	  || (fclose($handle) == FALSE))
	  {
		$text = UPLLAN_61.e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES;
	  }
	  else
	  {
		$text = '';
		$text .= '<br />'.UPLLAN_59.e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES.'. '.UPLLAN_60.e_ADMIN.e_READ_FILETYPES.'<br />';
	  }
	  $ns->tablerender(LAN_FILETYPES, $text);
	}


    $current_perms = array();
    if (($edit_upload_list && is_readable(e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES)) || (!$edit_upload_list && is_readable(e_ADMIN.e_READ_FILETYPES)))
	{
	  $xml = e107::getXml();
	  $source_file = $edit_upload_list ? e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES : e_ADMIN.e_READ_FILETYPES;
	  $temp_vars = $xml->loadXMLfile($source_file, true, false);
	  if ($temp_vars === FALSE)
	  {
	    echo "Error parsing XML file!";
	  }
	  else
	  {
		foreach ($temp_vars['class'] as $v1)
		{
		  $v = $v1['@attributes'];
		  $current_perms[$v['name']] = array('type' => $v['type'],'maxupload' => $v['maxupload']);
		}
	  }
	}
	elseif (is_readable(e_ADMIN.'filetypes.php'))
	{
	  $source_file = 'filetypes.php';
	  $current_perms[e_UC_MEMBER] = array('type' => implode(',',array_keys(get_allowed_filetypes('filetypes.php', ''))),'maxupload' => '2M');
	  if (is_readable(e_ADMIN.'admin_filetypes.php'))
	  {
		$current_perms[e_UC_ADMIN] = array('type' => implode(',',array_keys(get_allowed_filetypes('admin_filetypes.php', ''))),'maxupload' => '2M');
		$source_file .= ' + admin_filetypes.php';
	  }
	}
	else
	{	// Set a default
	  $current_perms[e_UC_MEMBER] = array('type' => 'zip,tar,gz,jpg,png','maxupload' => '2M');
	}
	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."?filetypes'>
	<table style='".ADMIN_WIDTH."' class='fborder'>
		<colgroup>
		<col style='width:30%' />
		<col style='width:40%' />
		<col style='width:25%' />
		<col style='width:5%' />
		</colgroup>
	  <tr>
		<td class='forumheader3' colspan='4'><input type='hidden' name='upload_do_edit' value='1'>".
			str_replace(array('[x]', '[y]'),array(e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES,e_ADMIN.e_READ_FILETYPES),UPLLAN_52)."</td>
	  </tr>
	  <tr>
		<td class='forumheader3' colspan='4'>".UPLLAN_57.$source_file."</td>
	  </tr>
	  <tr>
		<td class='fcaption'>".LAN_USERCLASS."</td>
		<td class='fcaption'>".UPLLAN_54."</td>
		<td class='fcaption' style='text-align:center'>".UPLLAN_55."</td>
		<td class='fcaption' style='text-align:center'>".LAN_DELETE."</td>
	  </tr>";
	foreach ($current_perms as $uclass => $uinfo)
	{
	  $text .= "
		<tr>
		  <td class='forumheader3'><select name='file_class_select[]' class='tbox'>
		  ".$e_userclass->vetted_tree('file_class_select',array($e_userclass,'select'), $uclass,'member,main,classes,admin, no-excludes')."
		  </select></td>
		  <td class='forumheader3'><input type='text' name='file_type_list[]' value='{$uinfo['type']}' class='tbox' size='40' /></td>
		  <td class='forumheader3' style='text-align:center'><input type='text' name='file_maxupload[]' value='{$uinfo['maxupload']}' class='tbox' size='10' /></td>
		  <td class='forumheader3'><input type='checkbox' value='1' name='file_line_delete_{$uclass}' /></td>
		</tr>";
	}
	// Now put up a box to add a new setting
	$text .= "
	  <tr>
		  <td class='forumheader3'><select name='file_class_select[]' class='tbox'>
		  ".$e_userclass->vetted_tree('file_class_select',array($e_userclass,'select'), '','member,main,classes,admin,blank, no-excludes')."
		  </select></td>
		  <td class='forumheader3'><input type='text' name='file_type_list[]' value='' class='tbox' size='40' /></td>
		  <td class='forumheader3' style='text-align:center'><input type='text' name='file_maxupload[]' value='".ini_get('upload_max_filesize')."' class='tbox' size='10' /></td>
		  <td class='forumheader3'>&nbsp;</td>
	  </tr>";
	$text .= "
	  <tr>
		<td class='forumheader3' style='text-align:center' colspan='4'>
				<input class='btn btn-default btn-secondary button' type='submit' name='generate_filetypes_xml' value='".UPLLAN_56."' />
		</td>
	  </tr>
	</table></form>
	</div>";

	$ns->tablerender(LAN_FILETYPES, $text);
    break;

  case 'options' :
	if(!getperms('0')) exit;
	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."?options'>
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	<td style='width:50%' class='forumheader3'>".UPLLAN_25."<br />
	<span class='smalltext'>".UPLLAN_26."</span></td>
	<td style='width:50%' class='forumheader3'>". ($pref['upload_enabled'] == 1 ? $rs->form_radio("upload_enabled", 1, 1)." ".LAN_YES.$rs->form_radio("upload_enabled", 0)." ".LAN_NO : $rs->form_radio("upload_enabled", 1)." ".LAN_YES.$rs->form_radio("upload_enabled", 0, 1)." ".LAN_NO)."
	</td>
	</tr>

	<tr>
	<td style='width:70%' class='forumheader3'>".UPLLAN_33."<br />
	<span class='smalltext'>".UPLLAN_34." (upload_max_filesize = ".ini_get('upload_max_filesize').", post_max_size = ".ini_get('post_max_size')." )</span></td>
	<td style='width:30%' class='forumheader3'>". $rs->form_text("upload_maxfilesize", 10, $pref['upload_maxfilesize'], 10)."
	</td>
	</tr>

	<tr>
	<td style='width:70%' class='forumheader3'>".UPLLAN_37."<br />
	<span class='smalltext'>".UPLLAN_38."</span></td>
	<td style='width:30%' class='forumheader3'>".r_userclass("upload_class", $pref['upload_class'],"off","nobody,public,guest,member,admin,classes")."

	</td>
	</tr>

	<tr>
	<td colspan='2' class='forumheader' style='text-align:center'>". $rs->form_button("submit", "optionsubmit", LAN_SUBMIT)."
	</td>
	</tr>
	</table>". $rs->form_close()."
	</div>";

	$ns->tablerender(LAN_OPTIONS, $text);
    break;
	
  case 'view' :
	$sql->db_Select('upload', '*', "upload_id='{$id}'");
	$row = $sql->db_Fetch();
	 extract($row);



	$post_author_id = substr($upload_poster, 0, strpos($upload_poster, "."));
	$post_author_name = substr($upload_poster, (strpos($upload_poster, ".")+1));
	$poster = (!$post_author_id ? "<b>".$post_author_name."</b>" : "<a href='".e_BASE."user.php?id.".$post_author_id."'><b>".$post_author_name."</b></a>");
	$upload_datestamp = $gen->convert_date($upload_datestamp, "long");

	$text = "<div style='text-align:center'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<colgroup>
		<col style='width:30%' />
		<col style='width:70%' />
		</colgroup>

		<tr>
		<td class='forumheader3'>".LAN_ID."</td>
		<td class='forumheader3'>{$upload_id}</td>
		</tr>

		<tr>
		<td class='forumheader3'>".LAN_DATE."</td>
		<td class='forumheader3'>{$upload_datestamp}</td>
		</tr>

		<tr>
		<td class='forumheader3'>".UPLLAN_5."</td>
		<td class='forumheader3'>{$poster}</td>
		</tr>

		<tr>
		<td class='forumheader3'>".UPLLAN_6."</td>
		<td class='forumheader3'><a href='mailto:{$upload_email}'>{$upload_email}</td>
		</tr>

		<tr>
		<td class='forumheader3'>".UPLLAN_7."</td>
		<td class='forumheader3'>".($upload_website ? "<a href='{$upload_website}'>{$upload_website}</a>" : " - ")."</td>
		</tr>

		<tr>
		<td class='forumheader3'>".LAN_FILE_NAME."</td>
		<td class='forumheader3'>".($upload_name ? $upload_name: " - ")."</td>
		</tr>

		<tr>
		<td class='forumheader3'>".LAN_VERSION."</td>
		<td class='forumheader3'>".($upload_version ? $upload_version : " - ")."</td>
		</tr>

		<tr>
		<td class='forumheader3'>".LAN_FILE."</td>
		<td class='forumheader3'>".(is_numeric($upload_file) ? "Binary file ID ".$upload_file : "<a href='".e_SELF."?dl.{$upload_file}'>$upload_file</a>")."</td>
		</tr>

		<tr>
		<td class='forumheader3'>".LAN_SIZE."</td>
		<td class='forumheader3'>".$e107->parseMemorySize($upload_filesize)."</td>
		</tr>

		<tr>
		<td class='forumheader3'>".LAN_SCREENSHOT."</td>
		<td class='forumheader3'>".($upload_ss ? "<a href='".e_BASE."request.php?upload.".$upload_id."'>".$upload_ss."</a>" : " - ")."</td>
		</tr>

		<tr>
		<td class='forumheader3'>".LAN_DESCRIPTION."</td>
		<td class='forumheader3'>{$upload_description}</td>
		</tr>

		<tr>
		<td class='forumheader3'>".UPLLAN_14."</td>
		<td class='forumheader3'>".($upload_demo ? $upload_demo : " - ")."</td>
		</tr>

		<tr>
		<td class='forumheader3'>".LAN_OPTIONS."</td>
		<td class='forumheader3'><a href='".e_SELF."?dlm.{$upload_id}'>".UPLAN_COPYTODLM."</a> | <a href='".e_SELF."?news.{$upload_id}'>".UPLLAN_16."</a> | <a href='".e_SELF."?dis.{$upload_id}'>".UPLLAN_17."</a></td>
		</tr>

		</table>
		</div>";

	$ns->tablerender(UPLLAN_18, $text);
	// Intentionally fall through into list mode

  case 'list' :
  default :
	$imgd = e_BASE.$IMAGES_DIRECTORY;
	$text = "<div style='text-align:center'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<colgroup>
		<col style='width:5%' />
		<col style='width:20%' />
		<col style='width:15%' />
		<col style='width:20%' />
		<col style='width:25%' />
		<col style='width:10%' />
		<col style='width:50px;white-space:nowrap' />
		</colgroup>
		<tr>
		<td class='fcaption'>".LAN_ID."</td>
		<td class='fcaption'>".LAN_DATE."</td>
		<td class='fcaption'>".UPLLAN_5."</td>
		<td class='fcaption'>".LAN_NAME."</td>
		<td class='fcaption'>".LAN_FILE_NAME."</td>
		<td class='fcaption'>".LAN_SIZE."</td>
		<td class='fcaption'>".LAN_ACTIONS."</td>
		</tr>";

	$text .= "<tr><td class='forumheader3' style='text-align:center' colspan='6'>";

	if (!$active_uploads = $sql->db_Select("upload", "*", "upload_active=0 ORDER BY upload_id ASC")) 
	{
	  $text .= UPLLAN_19.".\n</td>\n</tr>";
	} 
	else 
	{
	  $activeUploads = $sql -> db_getList();

	  $text .= UPLLAN_20." ".($active_uploads == 1 ? UPLAN_IS : UPLAN_ARE).$active_uploads." ".($active_uploads == 1 ? UPLLAN_21 : UPLLAN_27)." ...";
	  $text .= "</td></tr>";

	  foreach($activeUploads as $row)
	  {
		extract($row);
		$post_author_id = substr($upload_poster, 0, strpos($upload_poster, "."));
		$post_author_name = substr($upload_poster, (strpos($upload_poster, ".")+1));
		$poster = (!$post_author_id ? "<b>".$post_author_name."</b>" : "<a href='".e_BASE."user.php?id.".$post_author_id."'><b>".$post_author_name."</b></a>");
		$upload_datestamp = $gen->convert_date($upload_datestamp, "short");
		$text .= "<tr>
		<td class='forumheader3'>".$upload_id ."</td>
		<td class='forumheader3'>".$upload_datestamp."</td>
		<td class='forumheader3'>".$poster."</td>
		<td class='forumheader3'><a href='".e_SELF."?view.".$upload_id."'>".$upload_name ."</a></td>
		<td class='forumheader3'>".$upload_file ."</td>
		<td class='forumheader3'>".$e107->parseMemorySize($upload_filesize)."</td>
		<td class='forumheader3'>
		<form action='".e_SELF."?dis.{$upload_id}' id='uploadform_{$upload_id}' method='post'>
		<div><a href='".e_SELF."?dlm.{$upload_id}'><img src='".e_IMAGE."admin_images/downloads_16.png' alt='".UPLAN_COPYTODLS."' title='".UPLAN_COPYTODLS."' style='border:0' /></a>
		<a href='".e_SELF."?news.{$upload_id}'><img src='".e_IMAGE."admin_images/news_16.png' alt='".UPLLAN_16."' title='".UPLLAN_16."' style='border:0' /></a>
        <input type='image' title='".LAN_DELETE."' name='updelete[upload_{$upload_id}]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".$tp->toJS(UPLLAN_45." [ {$upload_name} ]")."') \"/>
		</div></form></td>
		</tr>";
	  }
	}
	$text .= "</table>\n</div>";

	$ns->tablerender(LAN_UPLOADS, $text);
}		// end - switch($action)




function upload_adminmenu() 
{
	$action = (e_QUERY) ? e_QUERY : "list";

    $var['list']['text'] = UPLLAN_51;
	$var['list']['link'] = e_SELF."?list";
	$var['list']['perm'] = "V";

	if(getperms("0"))
	{
	  $var['filetypes']['text'] = LAN_FILETYPES;
	  $var['filetypes']['link'] = e_SELF."?filetypes";
   	  $var['filetypes']['perm'] = "0";

	  $var['options']['text'] = LAN_OPTIONS;
	  $var['options']['link'] = e_SELF."?options";
   	  $var['options']['perm'] = "0";
    }
	show_admin_menu(LAN_UPLOADS, $action, $var);
}



require_once("footer.php");

