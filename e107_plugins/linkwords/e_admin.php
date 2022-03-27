<?php


//v2.x Standard for extending admin areas.


class linkwords_admin implements e_admin_addon_interface
{


	public function load($event, $ids)
	{

		switch($event)
		{
			case "news":
				$data = e107::getDb()->retrieve("news","*", "news_id IN(".$ids.")",true);
				foreach($data as $row)
				{
					$id = (int) $row['news_id'];
					$ret[$id]['stats'] = $row['news_body']."\n".$row['news_extended'];

				}
				break;


			default:
				// code to be executed if n is different from all labels;
		}


		return $ret;


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
			case 'news': // hook into the news admin form.
			//	$body = $ui->getListModel()->getData('news_body');
			//	var_dump($body);

				$config['fields']['stats'] =   array ('title' => LAN_PLUGIN_LINKWORDS_NAME, 'type' => 'method', 'tab' =>1, 'noedit'=>true, 'writeParms' => array(), 'width' => 'auto', 'help' => '', 'readParms' => '', 'class' => 'left', 'thclass' => 'left',  );

			//	$config['batchOptions'] = array('custom'    => 'Custom Batch Command');
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

		if($action === 'delete')
		{
			return;
		}

		if($action === 'batch')
		{
			$id = (array) $id;
			$arrayOfRecordIds = $id['ids'];
			$command = $id['cmd'];

			return;
		}

/*
		if(!empty($id) )
		{

			if(!empty($data['x__blank_url']))
			{

				// eg. Save the data in 'blank' plugin table. .

			}

		}
*/


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
/*
			case "write":
				$text = "<table class='table table-striped table-condensed table-bordered'>
				<tr><th class='text-right'>No.</th><th>URL</th><th>Title</th></tr>";

				for($i = 1; $i <= 20; $i++)
				{
					$text .= "<tr>
		            <td class='text-right'>" . $i . "</td>
		             <td>" . $this->text('x_reference_url[url][' . $i . ']', varset($vals['url'][$i]), 255, array('class' => 'x-reference-url', 'id' => 'x-reference-url-url-' . $i, 'size' => 'block-level')) . "</td>
		            <td>" . $this->text('x_reference_url[name][' . $i . ']', varset($vals['name'][$i]), 255, array('id' => 'x-reference-url-name-' . $i, 'size' => 'block-level')) . "</td>
		            </tr>";
				}

				$text .= "</table>";

				$text .= $this->hidden('meta-parse', SITEURLBASE . e_PLUGIN_ABS . "reference/meta.php", array('id' => 'meta-parse'));

				return $text;
				break;*/

			default:
				// code to be executed if n is different from all labels;
		}





		return null;


	}

}


