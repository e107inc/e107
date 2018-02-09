<?php


//v2.x Standard for extending admin areas.


class _blank_admin
{
	/**
	 * Extend Admin-ui Parameters
	 * @param $ui admin-ui object
	 * @return array
	 */
	public function config(e_admin_ui $ui)
	{
		$action     = $ui->getAction(); // current mode: create, edit, list
		$type       = $ui->getEventName(); // 'wmessage', 'news' etc. (core or plugin)
		$id         = $ui->getId();

		$config = array();
		$defaultValue = 'http://';

		switch($type)
		{
			case "news": // hook into the news admin form.
				$config['fields']['url'] =   array ( 'title' =>"Custom Field", 'type' => 'url', 'tab'=>1,  'writeParms'=> array('size'=>'xxlarge', 'placeholder'=>'', 'default'=>$defaultValue), 'width' => 'auto', 'help' => '', 'readParms' => '', 'class' => 'left', 'thclass' => 'left',  );

				$config['batchOptions'] = array('custom'    => 'Custom Batch Command');
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
	public function process(e_admin_ui $ui, $id=0)
	{

		$data       = $ui->getPosted(); // ie $_POST field-data
		$type       = $ui->getEventName(); // eg. 'news'
		$action     = $ui->getAction(); // current mode: create, edit, list, batch
		$changed    = $ui->getModel()->dataHasChanged(); // true when data has changed from what is in the DB.

		if($action === 'delete')
		{
			return;
		}

		if($action === 'batch')
		{
			$arrayOfRecordIds = $id['ids'];
			$command = $id['cmd'];

			return;
		}


		if(!empty($id) )
		{

			if(!empty($data['x__blank_url']))
			{

				// eg. Save the data in 'blank' plugin table. .

			}


		}



	}



}




