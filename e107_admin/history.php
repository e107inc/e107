<?php

// Generated e107 Plugin Admin Area 

require_once('../class2.php');
if (!getperms('0'))
{
	e107::redirect('admin');
	exit;
}

// e107::lan('history',true);
e107::css('inline', " td.history-data pre { max-width: 800px; } }");

class history_adminArea extends e_admin_dispatcher
{

	protected $modes = array(	
	
		'main'	=> array(
			'controller' 	=> 'admin_history_ui',
			'path' 			=> null,
			'ui' 			=> 'admin_history_form_ui',
			'uipath' 		=> null
		),
		

	);	
	
	
	protected $adminMenu = array(

		'main/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
	//	'main/create'		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),

		// 'main/div0'      => array('divider'=> true),
		// 'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P'),
		
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = 'History';
}




				
class admin_history_ui extends e_admin_ui
{
			
		protected $pluginTitle		= 'History';
		protected $pluginName		= 'myplugin';
	//	protected $eventName		= 'myplugin-admin_history'; // remove comment to enable event triggers in admin. 		
		protected $table			= 'admin_history';
		protected $pid				= 'history_id';
		protected $perPage			= 10; 
		protected $batchDelete		= true;
		protected $batchExport     = true;
		protected $batchCopy		= false;

	//	protected $tabs				= array('tab1'=>'Tab 1', 'tab2'=>'Tab 2'); // Use 'tab'=>'tab1'  OR 'tab'=>'tab2' in the $fields below to enable. 
		
	//	protected $listQry      	= "SELECT * FROM `#tableName` WHERE field != '' "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.
	
		protected $listOrder		= 'history_id DESC';
	
		protected $fields 		= array (
			'checkboxes'              => array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => 'value', 'class' => 'center', 'toggle' => 'e-multiselect', 'readParms' => [], 'writeParms' => [],),
		//	'history_id'              => array ( 'title' => LAN_ID, 'type' => 'number', 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => [], 'writeParms' => [], 'class' => 'left', 'thclass' => 'left',),
			'history_datestamp'       => array ( 'title' => LAN_DATESTAMP, 'type' => 'datestamp', 'data' => 'int', 'width' => '15%', 'filter' => true, 'help' => '', 'readParms' => [], 'writeParms' => [], 'class' => 'left', 'thclass' => 'left',),
			'history_table'           => array ( 'title' => 'Table', 'type' => 'text', 'data' => 'safestr', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => [], 'writeParms' => [], 'class' => 'left', 'thclass' => 'left',),
			'history_record_id'       => array ( 'title' => LAN_ID, 'type' => 'number', 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => [], 'writeParms' => [], 'class' => 'left', 'thclass' => 'left',),
			'history_action'          => array ( 'title' => 'Action', 'type' => 'dropdown', 'data' => 'int', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => [], 'writeParms' => [], 'class' => 'left', 'thclass' => 'left', 'batch' => false,),
			'history_data'            => array ( 'title' => 'Changed Data', 'type' => 'method', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => [], 'writeParms' => [], 'class' => 'history-data left', 'thclass' => 'left', 'filter' => false, 'batch' => false,),
			'history_user_id'         => array ( 'title' => LAN_USER, 'type' => 'user', 'data' => 'int', 'width' => '5%', 'filter' => true, 'help' => '', 'readParms' => [], 'writeParms' => [], 'class' => 'left', 'thclass' => 'left',),
			'history_restored'         => array ( 'title' => "Restored", 'type' => 'datestamp', 'data' => 'int', 'width' => '5%', 'filter' => true, 'help' => '', 'readParms' => [], 'writeParms' => [], 'class' => 'center', 'thclass' => 'center',),

			'options'                 => array ( 'title' => LAN_OPTIONS, 'type' => 'method', 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => 'value', 'readParms' => [], 'writeParms' => [],),
		);		
		
		protected $fieldpref = array( 'history_datestamp', 'history_table', 'history_record_id','history_action', 'history_data', 'history_user_id', 'history_restored');
	

	//	protected $preftabs        = array('General', 'Other' );
		protected $prefs = array(
		); 

	
		public function init()
		{
				$this->fields['history_action']['writeParms']['optArray'] = [
					'delete'    => "<span class='label label-danger'>". LAN_DELETE."</span>",
					'update'    =>  "<span class='label label-success'>". LAN_UPDATE."</span>",
					'restore'    =>  "<span class='label label-warning'>Restore</span>"
				];


			// Check for the "Restore" action
			if (!empty($_POST['restore_deleted']))
			{
				$restoreId = (int) key($_POST['restore_deleted']); // Retrieve the ID of the record to restore
				$this->processRestoreAction($restoreId, 'insert');
			}
			elseif (!empty($_POST['restore_updated']))
			{
				$restoreId = (int) key($_POST['restore_updated']); // Retrieve the ID of the record to restore
				$this->processRestoreAction($restoreId, 'update');
			}
		}

		/**
		 * Restores a previously recorded entry in the administrator history table.
		 *
		 * @param int    $id     The unique identifier of the record to restore.
		 * @param string $action The type of action to perform (e.g., 'insert' or 'update') during restoration.
		 * @return void
		 */
		private function processRestoreAction($id, $action)
		{

			$db = e107::getDb();
			$message = e107::getMessage();

			// Query the admin_history table for the record
			$historyRow = $db->retrieve('admin_history', '*', 'history_id = '.$id);

			if ($historyRow)
			{
				$originalTable = $historyRow['history_table'];                  // The table where this record belongs
				$originalData = json_decode($historyRow['history_data'], true);
				$pid = $historyRow['history_pid'];
				$recordId = $historyRow['history_record_id'];



				if (!empty($originalTable) && !empty($originalData) && !empty($pid) && !empty($recordId))
				{
					if($action === 'insert')
					{
						$originalData[$pid] = (int) $recordId;
						$result = $db->replace($originalTable, $originalData);
					}
					else // update
					{
						$backup = $db->retrieve($originalTable, '*', $pid. ' = '.(int) $recordId);
						if($changes = array_diff_assoc($originalData, $backup))
	                    {
							$old_changed_data = array_intersect_key($backup, $changes);
							$this->backupToHistory($originalTable, $pid, $recordId, 'restore', $old_changed_data, false);
	                    }

						$originalData['WHERE'] = $pid .' = '. (int) $recordId;
						$result = $db->update($originalTable, $originalData);
					}

					if ($result)
					{
						$message->addSuccess("The record (ID: $id) has been successfully restored to the $originalTable table.", 'default', true);
						$db->update('admin_history', "history_restored = ".time()." WHERE history_id = $id");
					}
					elseif($result === 0)
					{
						$message->addInfo("No changes made", 'default', true);
					}
					else
					{
						$message->addError("Failed to restore the record (ID: $id) to the $originalTable table.", 'default', true);
					}
				}
				else
				{
					$message->addError("Restoration data is incomplete or invalid for Record ID: $id.", 'default', true);
				}
			}
			else
			{
				$message->addError("Record ID: $id not found in the admin history table.", 'default', true);
			}

			// Redirect back to avoid multiple form submissions
			e107::getRedirect()->go(e_SELF);
		}


		// ------- Customize Create --------
		
		public function beforeCreate($new_data,$old_data)
		{
			return $new_data;
		}
	

		
		// left-panel help menu area. (replaces e_help.php used in old plugins)
	public function renderHelp()
	{
		$caption = LAN_HELP;
		$text = "
        <p>This page allows you to view the <strong>history of changes</strong> made to records in the system and restore records to a previous state when needed.</p>
        
        <h4>Features of this page:</h4>
        <ul>
            <li><strong>View Changes:</strong> See details of updates and deletions, including who made the changes and when.</li>
            <li><strong>Revert Changes:</strong> Restore a record to its earlier version, undoing accidental or undesired modifications.</li>
            <li><strong>Audit Trail:</strong> Track all actions performed on records for accountability and transparency.</li>
        </ul>
        
        <p>Use the filters to narrow down the history logs or locate specific changes. If a record can be restored, an option will be available in the Options menu.</p>
    ";

		return ['caption' => $caption, 'text' => $text];
	}
			
	/*	
		// optional - a custom page.  
		public function customPage()
		{
			if($this->getPosted('custom-submit')) // after form is submitted. 
			{
				e107::getMessage()->addSuccess('Changes made: '. $this->getPosted('example'));
			}

			$this->addTitle('My Custom Title');


			$frm = $this->getUI();
			$text = $frm->open('my-form', 'post');

				$tab1 = "<table class='table table-bordered adminform'>
					<colgroup>
						<col class='col-label'>
						<col class='col-control'>
					</colgroup>
					<tr>
						<td>Label ".$frm->help('A help tip')."</td>
						<td>".$frm->text('example', $this->getPosted('example'), 80, ['size'=>'xlarge'])."</td>
					</tr>
					</table>";

			// Display Tab
			$text .= $frm->tabs([
				'general'   => ['caption'=>LAN_GENERAL, 'text' => $tab1],
			]);

			$text .= "<div class='buttons-bar text-center'>".$frm->button('custom-submit', 'submit', 'submit', LAN_CREATE)."</div>";
			$text .= $frm->close();

			return $text;
			
		}
		
	
	 // Handle batch options as defined in admin_history_form_ui::history_data;  'handle' + action + field + 'Batch'
	 // @important $fields['history_data']['batch'] must be true for this method to be detected. 
	 // @param $selected
	 // @param $type
	function handleListHistoryDataBatch($selected, $type)
	{

		$ids = implode(',', $selected);

		switch($type)
		{
			case 'custombatch_1':
				// do something
				e107::getMessage()->addSuccess('Executed custombatch_1');
				break;

			case 'custombatch_2':
				// do something
				e107::getMessage()->addSuccess('Executed custombatch_2');
				break;

		}


	}

	
	 // Handle filter options as defined in admin_history_form_ui::history_data;  'handle' + action + field + 'Filter'
	 // @important $fields['history_data']['filter'] must be true for this method to be detected. 
	 // @param $selected
	 // @param $type
	function handleListHistoryDataFilter($type)
	{

		$this->listOrder = 'history_data ASC';
	
		switch($type)
		{
			case 'customfilter_1':
				// return ' history_data != 'something' '; 
				e107::getMessage()->addSuccess('Executed customfilter_1');
				break;

			case 'customfilter_2':
				// return ' history_data != 'something' '; 
				e107::getMessage()->addSuccess('Executed customfilter_2');
				break;

		}


	}
	
		
		
	*/
			
}
				


class admin_history_form_ui extends e_admin_form_ui
{

	public function options($parms, $value, $id, $att = [])
	{
		$controller = $this->getController();

		$row = $controller->getListModel()->getData();

		// Begin options group
		$text = "<div class='btn-group pull-right'>";

		// Check if the record can be restored
		if (!empty($id))
		{
			// Generate Restore button
			$restoreTitle = "Restore this Record";

			$type = $row['history_action'];
			$name = ($type === 'delete') ? "restore_deleted[$id]" : "restore_updated[$id]";
			$text .= "<button class='btn btn-default' type='submit' name='$name' title='{$restoreTitle}'><i class='admin-ui-option fa fa-undo fa-2x fa-fw'></i></button>";
		}

		$att['readParms']['editClass'] = 999; // disable it.
		$text .= $this->renderValue('options', $value, $att, $id);

		// End options group
		$text .= "</div>";

		return $text;
	}



	// Custom Method/Function 
	function history_data($curVal,$mode)
	{

		switch($mode)
		{
			case 'read': // List Page
			case 'write':

			  return print_a($curVal,true);


			break;

			
			case 'filter':
				return array('customfilter_1' => 'Custom Filter 1', 'customfilter_2' => 'Custom Filter 2');
			break;
			
			case 'batch':
				return array('custombatch_1' => 'Custom Batch 1', 'custombatch_2' => 'Custom Batch 2');
			break;
		}
		
		return null;
	}

}		
		
		
new history_adminArea();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;

