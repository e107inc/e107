<?php


//v2.x Standard for extending admin areas.


class trackback_admin
{
	private $active = false;


	function __construct()
	{
		$pref = e107::pref('core','trackbackEnabled');
		$this->active = vartrue($pref);
	}


	/**
	 * Extend Admin-ui Parameters
	 * @param $ui admin-ui object
	 * @return array
	 */
	public function config($ui)
	{
		$action     = $ui->getAction(); // current mode: create, edit, list
		$type       = $ui->getEventName(); // 'wmessage', 'news' etc.

		$config = array();

		switch($type)
		{
			case "news":

				if($this->active == true)
				{
					$config['fields']['urls'] =   array ( 'title' =>LAN_NEWS_35, 'type' => 'textarea', 'tab'=>1,  'writeParms'=> array('size'=>'xxlarge', 'placeholder'=>''), 'width' => 'auto', 'help' => '', 'readParms' => '', 'class' => 'left', 'thclass' => 'left',  );
				}
				break;
		}

		//Note: 'urls' will be returned as $_POST['x_trackback_urls']. ie. x_{PLUGIN_FOLDER}_{YOURKEY}

		return $config;

	}


	/**
	 * Process Posted Data.
	 * @param $ui admin-ui object
	 */
	public function process($ui)
	{
		$data = $ui->getPosted();
		e107::getMessage()->addDebug(print_a($data,true));

		if($data['news_id'] && $this->active)
		{
			$excerpt = e107::getParser()->text_truncate(strip_tags(e107::getParser()->post_toHTML($data['news_body'])), 100, '...');

//			$id=mysql_insert_id();
			$permLink = e107::getInstance()->base_path."comment.php?comment.news.".intval($data['news_id']);

			require_once(e_PLUGIN."trackback/trackbackClass.php");
			$trackback = new trackbackClass();

			if($data['x_trackback_urls'])
			{
				$urlArray = explode("\n", $data['x_trackback_urls']);
				foreach($urlArray as $pingurl)
				{
					if(!$terror = $trackback->sendTrackback($permLink, $pingurl, $data['news_title'], $excerpt))
					{

						e107::getMessage()->add("Successfully pinged {$pingurl}.", E_MESSAGE_SUCCESS);
					}
					else
					{

						e107::getMessage()->add("was unable to ping {$pingurl}<br />[ Error message returned was : '{$terror}'. ]", E_MESSAGE_ERROR);
					}
				}
			}

		/*
			if(isset($_POST['pingback_urls']))
			{
				if ($urlArray = $trackback->getPingUrls($data['news_body'])) //FIXME - missing method!!!
				{
					foreach($urlArray as $pingurl)
					{

						if ($trackback->sendTrackback($permLink, $pingurl, $data['news_title'], $excerpt))
						{

							e107::getMessage()->add("Successfully pinged {$pingurl}.", E_MESSAGE_SUCCESS);
						}
						else
						{

							e107::getMessage()->add("Pingback to {$pingurl} failed ...", E_MESSAGE_ERROR);
						}
					}
				}
				else
				{

					e107::getMessage()->add("No pingback addresses were discovered", E_MESSAGE_INFO, $smessages);
				}
			}
		*/
		}



		/* end trackback */
	}



}




