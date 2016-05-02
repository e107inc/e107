<?php


//v2.x Standard for extending admin areas.


class _blank_admin
{
	/**
	 * Extend Admin-ui Parameters
	 * @param $ui admin-ui object
	 * @return array
	 */
	public function config($ui)
	{
		$action     = $ui->getAction(); // current mode: create, edit, list
		$type       = $ui->getEventName(); // 'wmessage', 'news' etc. (core or plugin)
		$id         = $ui->getId();

		$config = array();
		$defaultValue = 'http://';

		switch($type)
		{
			case "news": // hook into the news admin form.
				$config['fields']['url'] =   array ( 'title' =>"CUstom Field", 'type' => 'url', 'tab'=>1,  'writeParms'=> array('size'=>'xxlarge', 'placeholder'=>'', 'default'=>$defaultValue), 'width' => 'auto', 'help' => '', 'readParms' => '', 'class' => 'left', 'thclass' => 'left',  );
				break;
		}

		//Note: 'urls' will be returned as $_POST['x__blank_url']. ie. x_{PLUGIN_FOLDER}_{YOURFIELDKEY}

		return $config;

	}


	/**
	 * Process Posted Data.
	 * @param object $ui admin-ui
	 * @param int $id - Primary ID of the record being created/edited/deleted
	 */
	public function process($ui, $id=0)
	{

		$data       = $ui->getPosted();
		$type       = $ui->getEventName();
		$action     = $ui->getAction(); // current mode: create, edit, list

		if($action == 'delete')
		{
			return;
		}


		if(!empty($id) )
		{

			if(!empty($data['x__blank_url']))
			{

				// Save the data in 'blank' plugin table. .

			}


		}



	}



}




?>