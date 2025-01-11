<?php


//v2.x Standard for extending admin areas.


class _blank_admin implements e_admin_addon_interface
{

	/**
	 * Populate custom field values.
	 * @param string $event
	 * @param string $ids
	 * @return array
	 */
	public function load($event, $ids)
	{

	//	$data = e107::getDb()->retrieve("blank","*", "blank_table='".$event."' AND blank_pid IN(".$ids.")",true);

	/*	$ret = array();

		foreach($data as $row)
		{
			$id = (int) $row['can_pid'];
			$ret[$id]['url'] = $row['can_url'];

		}

		return $ret;
	*/



		return array(
			3   => array('url'=>'https://myurl.com'),
		);


	}



	/**
	 * Extend Admin-ui Configuration Parameters eg. Fields etc.
	 * @param $ui admin-ui object
	 * @return array
	 */
	public function config(e_admin_ui $ui)
	{
		$action     = $ui->getAction(); // current mode: create, edit, list
		$type       = $ui->getEventName(); // 'wmessage', 'news' etc. (core or plugin)
		$id         = $ui->getId();

		$config = array();
		$defaultValue = 'https://';

		switch($type)
		{
			case 'news': // hook into the news admin form.
				$config['fields']['url'] =   array ('title' => 'Blank URL', 'type' => 'url', 'tab' =>1, 'writeParms' => array('size' =>'xxlarge', 'placeholder' =>'', 'default' =>$defaultValue), 'width' => 'auto', 'help' => '', 'readParms' => '', 'class' => 'left', 'thclass' => 'left',  );
				$config['fields']['custom'] =   array ('title' => 'Blank Custom', 'type' => 'method', 'tab' =>1, 'writeParms' => array('size' =>'xxlarge', 'placeholder' =>'', 'default' =>$defaultValue), 'width' => 'auto', 'help' => '', 'readParms' => '', 'class' => 'left', 'thclass' => 'left',  );

				$config['batchOptions'] = array('custom'    => 'Custom Batch Command');
			break;

			case 'page':

			break;
		}

		//Note: 'urls' will be returned as $_POST['x__blank_url']. ie. x_{PLUGIN_FOLDER}_{YOURFIELDKEY}

		return $config;

	}


	/**
	 * Process Posted Data.
	 * @param object $ui admin-ui
	 * @param int|array $id - Primary ID of the record being created/edited/deleted or array data of a batch process.
	 */
	public function process(e_admin_ui $ui, $id=null)
	{

		$data       = $ui->getPosted(); // ie $_POST field-data
		$type       = $ui->getEventName(); // eg. 'news'
		$action     = $ui->getAction(); // current mode: create, edit, list, batch
		$changed    = $ui->getModel()->dataHasChanged(); // true when data has changed from what is in the DB.

		switch($action)
		{
			case 'create':
			case 'edit':

					if(!empty($id) && !empty($data['x__blank_url']))
					{
						// eg. Save the data in the 'blank' plugin table.
					}

			break;

			case 'delete':

			break;

			case 'batch':
				$id = (array) $id;
				$arrayOfRecordIds = $id['ids'];
				$command = $id['cmd'];
			break;

			default:
				// code to be executed if n is different from all labels;
		}

	}

}


/**
 * Custom field methods
 */
class _blank_admin_form extends e_form
{
	/**
	 * @param mixed $curval
	 * @param string $mode
	 * @param null|array $att
	 * @return null|string
	 */
	function x__blank_custom($curval, $mode, $att=null) // 'x_' + plugin-folder + custom-field name.
	{
		/** @var e_admin_controller_ui $controller */
		$controller = e107::getAdminUI()->getController();

		$event = $controller->getEventName(); // eg 'news' 'page' etc.

		$text = '';

		switch($mode)
		{
			case "read":
				$field = $event.'_id'; // news_id or page_id etc.
				$text = "<span class='e-tip' title='".$controller->getFieldVar($field)."'>Custom</span>";
			break;

			case "write":
			case "filter":
			case "batch":
			break;

		}

		return $text;

	}


}



