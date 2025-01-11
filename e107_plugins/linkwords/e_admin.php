<?php


//v2.x Standard for extending admin areas.


class linkwords_admin implements e_admin_addon_interface
{

	public function load($event, $ids)
	{
		// no table used.
	}


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

		switch($type)
		{
			case 'news': // hook into the news admin area
			case 'page': // hook into the page admin area
				$config['fields']['stats'] =   array ('title' => LAN_PLUGIN_LINKWORDS_NAME, 'type' => 'method', 'tab' =>1, 'noedit'=>true, 'writeParms' => array(), 'width' => 'auto', 'help' => '', 'readParms' => '', 'class' => 'left', 'thclass' => 'left',  );
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
		// no data saved.
	}



}


class linkwords_admin_form extends e_form
{
	/** @var linkwords_parse lw */
	private $lw;

	function __construct()
	{
		$this->lw = e107::getAddon('linkwords','e_parse');
		$this->lw->init();
	}
	/**
	 * @param $curval
	 * @param $mode
	 * @param $att
	 * @return null|string
	 */
	function x_linkwords_stats($curval, $mode, $att=null)
	{
		/** @var e_admin_controller_ui $controller */
		$controller = e107::getAdminUI()->getController();
		$event = $controller->getEventName(); // eg 'news' 'page' etc.

		if($event === 'news')
		{
			$curval = $controller->getFieldVar('news_body')."\n".$controller->getFieldVar('news_extended');
		}
		else
		{
			$curval = $controller->getFieldVar('page_text');
		}

		unset($att);
		$vals = array();

		if(empty($curval))
		{
			return null;
		}

		switch($mode)
		{
			case "read":
				$clsInt = '';
				$clsExt = '';

				$curval = str_replace('&#039;', "'", $curval);
				$this->lw->toHTML($curval,'BODY');

				$stats = $this->lw->getStats();

				if(empty($stats['internal']))
				{
					$clsInt = " style='opacity:0.7'";
				}


				if(empty($stats['external']))
				{
					$clsExt = " style='opacity:0.7'";
				}

				$text = "<div class='text-nowrap'><i class='fas fa-link e-tip' title=\"Internal\"></i> <span{$clsInt}>".$stats['internal']."</span> | ";
				$text .= "<i class='fas fa-external-link-alt e-tip' title=\"External\"></i> <span{$clsExt}>".$stats['external']."</span></div>";

				return $text;
				break;


			default:
				// code to be executed if n is different from all labels;
		}





		return null;


	}

}


