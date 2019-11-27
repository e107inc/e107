<?php


//v2.x Standard for extending admin areas.


class social_admin
{
	private $_twitter_active = false;

	private $_default_providers = array('facebook-share'=>'fa-facebook', 'twitter'=>'fa-twitter');


	function __construct()
	{
		$pref = e107::pref('core','social_login');

		if(!empty($pref) && !empty($pref['Twitter']) && is_array($pref['Twitter']))
		{
			$this->_twitter_active = vartrue($pref['Twitter']['keys']['key']);
		}
	}


	/**
	 * Extend Admin-ui Parameters
	 * @param $ui admin-ui object
	 * @return array
	 */
	public function config(e_admin_ui $ui)
	{
		$action     = $ui->getAction(); // current mode: create, edit, list
		$type       = $ui->getEventName(); // 'wmessage', 'news' etc.

		$config = array();

		//TODO Add support for type='method'. (ie. extending the form-handler. )

		switch($type)
		{
			case "page":
			case "news":

				if($this->_twitter_active == true)
				{
					$config['fields']['twitter'] =   array ( 'title' =>LAN_SOCIAL_202, 'type' => 'text', 'tab'=>2,  'writeParms'=> array('size'=>'xxlarge', 'placeholder'=>LAN_SOCIAL_203), 'width' => 'auto', 'help' => '', 'readParms' => '', 'class' => 'left', 'thclass' => 'left',  );
				}
				break;
		}

		//Note: 'twitter' will be returned as $_POST['x_social_twitter']. ie. x_{PLUGIN_FOLDER}_{YOURKEY}

		return $config;

	}


	/**
	 * Process Posted Data.
	 * @param $ui admin-ui object
	 */
	public function process(e_admin_ui $ui, $id=0)
	{
		$data       = $ui->getPosted();
		$action     = $ui->getAction(); // current mode: create, edit, list
		$type       = $ui->getEventName(); // eg. 'news'
		$changed    = $ui->getModel()->dataHasChanged();

		$tp         = e107::getParser();

		//e107::getHybridAuth('twitter');
	//	e107::getMessage()->addDebug("e107_plugins/social/e_admin.php :: process method called.");
	//	e107::getMessage()->addDebug("ID: ".$id);
	//	e107::getMessage()->addDebug("Action: ".$action);
	//	e107::getMessage()->addDebug(print_a($data,true));


		if($changed === false || $type !== 'news' || intval($data['news_class']) !== e_UC_PUBLIC) // social links only when item is public.
		{
			return null;
		}

		if($action === 'create' || $action === 'edit')
		{
			$data['news_id'] = $id;

			$shareData = array(
				'title'     => $tp->post_toHTML($data['news_title']),
				'url'       => e107::getUrl()->create('news/view/item', $data, 'full=1'),
				'hashtags'  => $data['news_meta_keywords']
			);

			$message = '
				
					<div class="well social-plugin" style="width:450px">
						<div class="media">
							<div class="media-left">'.$tp->toImage($data['news_thumbnail'][0], array('w'=>100, 'h'=>100, 'class'=>'media-object')).'</div>
							<div class="media-body">
								<h4 class="media-header">'.$tp->post_toHTML($data['news_title']).'</h4>
								<p><small>'.$tp->post_toHTML($data['news_meta_description'])."</small></p>".$this->share($shareData).'
							</div>	
						</div>
					</div>
				';

			//FIXME setTitle doesn't work across sessions. 
			e107::getMessage()->setTitle(LAN_PLUGIN_SOCIAL_NAME." (".LAN_OPTIONAL.")",E_MESSAGE_INFO)->addInfo($message);
		}

	}

	/**
	 * Build social share links for the admin area.
	 * @param $data
	 * @return string
	 */
	private function share($data)
	{
	//	$pref = e107::pref('social');

		/** @var social_shortcodes $soc */
		$soc = e107::getScBatch('social');
		$tp = e107::getParser();

		$providers  = /*!empty($pref['sharing_providers']) ? array_keys($pref['sharing_providers']) :*/ $this->_default_providers;

		$links = array();

		$allProviders = $soc->getProviders();
		
		$options = array(
			'twitterAccount' => basename(XURL_TWITTER),
			'hashtags'  => $data['hashtags']
		
		);

		foreach($allProviders as $key=>$row)
		{
			if(!array_key_exists($key,$providers))
			{
				continue;
			}
			
			$shareURL = $soc->getShareUrl($key, $row['url'], $data, $options);

			$links[] = "<a class='btn btn-primary btn-xs' target='_blank' href='".$shareURL."'>".$tp->toGlyph($providers[$key]).$row['title']."</a>";

		}

		return implode(" ",$links);
	}

}




