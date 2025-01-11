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

require_once(__DIR__.'/../class2.php');
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
        $ids = array_map('\intval', array_values($selected));
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
