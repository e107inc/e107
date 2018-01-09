<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Application store client
 *
 */
 
class e_marketplace
{
	/**
	 * Protocol type, defaults to WSDL, fallback to 'xmlrpc' if soap extension not installed
	 * @var e_marketplace_adapter_abstract
	 */
	protected $adapter = null;
	
	/**
	 * Adapter identifier
	 * @var string wsdl|xmlrpc
	 */
	protected $_adapter_name = null;

	/**
	 * Constructor
	 * @param string $force force adapter wsdl|xmlrpc, omit to switch to auto-detection
	 */
	public function __construct($force = null)
	{
		if(null !== $force)
		{
			$this->_adapter_name = $force === 'wsdl' ? 'wsdl' : 'xmlrpc';
		}
		elseif(!class_exists('SoapClient')) $this->_adapter_name = 'xmlrpc';
		else
		{
			$this->_adapter_name = 'wsdl';
		}

	}
	
	/**
	 * Set authorization key
	 * @deprecated subject of removal
	 */
	public function generateAuthKey($username, $password)
	{
		if(trim($username) == '' || trim($password) == '')
		{
			return false;	
		}
		$this->setAuthKey($this->makeAuthKey($username, $password, true));	
		return $this;
	}
	
	/**
	 * Set authorization key
	 * @deprecated subject of removal
	 */
	public function setAuthKey($authkey)
	{
		$this->adapter->setAuthKey($authkey);	
		return $this;
	}
	
	public function hasAuthKey()
	{
		return $this->adapter->hasAuthKey();
	}
	
	/**
	 * Make authorization key from user credentials
	 * @deprecated subject of removal
	 */
	public function makeAuthKey($username, $password = '', $plain = false)
	{
		$now 	= gmdate('y-m-d H');
		if($plain && !empty($password)) $password = md5($password);
		return sha1($username.$password.$now);
	}



	/**
	 * Have the admin enter their e107.org login details in order to create the authorization key. 
	 * @deprecated subject of removal
	 */	
	public function renderLoginForm()
	{
		
	$text =	'
          <div class="" id="loginModal">
		    <div class="well">
		    <img src="'.e_IMAGE_ABS.'admin_images/credits_logo.png" alt="" style="margin-bottom:15px" />
		    <ul class="nav nav-tabs">
			    <li class="active"><a href="#login" data-toggle="tab">Login</a></li>
			    <li><a href="#create" data-toggle="tab">Create Account</a></li>
		    </ul>
		    <div id="myTabContent" class="tab-content">
		    <div class="tab-pane active in" id="login">
		    <form class="form-horizontal" action="" method="POST">
			    <fieldset>
				    <div id="legend">
				    	<legend class="">Login</legend>
				    </div>
				    
					<div class="control-group">
					    <label class="control-label" for="username">Username</label>
						<div class="controls">
						   	<input type="text" id="username" name="username" placeholder="" class="input-xlarge">
						</div>
					</div>
					
				    <div class="control-group">
					    <label class="control-label" for="password">Password</label>
					    <div class="controls">
					    	<input type="password" id="password" name="password" placeholder="" class="input-xlarge">
					    </div>
				    </div>
				    
				    <div class="control-group">
					    <div class="controls">
					    	<button class="btn btn-success">Login</button>
					    </div>
				    </div>
				    
			    </fieldset>
		    </form>
		    </div>';
	
	//TODO Use Form handler for INPUT tags. 
	//XXX TBD OR do we just redirect to the signup page on the website, in an iframe? 
			
	$text .=	'
		    <div class="tab-pane fade" id="create">
		    <form class="form-horizontal" id="tab">
		     <div class="control-group">
		    	<label class="control-label">Username</label>
		     	<div class="controls">
		    		<input type="text" value="" class="input-xlarge">
		    	</div>
		    </div>
		     <div class="control-group">
		    	<label class="control-label">Password</label>
		    	 <div class="controls">
		   		 <input type="password" value="" class="input-xlarge">
		    	</div>
		    </div>
		     <div class="control-group">
		    	<label class="control-label">Email</label>
		    	 <div class="controls">
		    	<input type="text" value="" class="input-xlarge">
		    	</div>
		    </div>
		    <div class="control-group">
			    <div class="controls">
			   	 <button class="btn btn-primary">Create Account</button>
			    </div>
		    </div>
		    </form>
		    </div>
		    </div>
		    </div>
		  ';	
		
		return $text;
	}

	/**
	 * Retrieve currently used adapter
	 * @param e_marketplace_adapter_abstract
	 * @return \e_marketplace_adapter_abstract
	 */
	public function adapter()
	{
		if(null === $this->adapter)
		{
			$className = 'e_marketplace_adapter_'.$this->_adapter_name; 
			$this->adapter = new $className();
		}
		return $this->adapter;
	}
	
	/**
	 * Retrieve currently used adapter
	 * @param e_marketplace_adapter_abstract
	 */
	public function call($method, $data, $apply = true)
	{
		if(E107_DEBUG_LEVEL > 0)
		{
			e107::getDebug()->log("Calling e107.org  using <b> ".$this->_adapter_name."</b> adapter");
		}
		return $this->adapter()->call($method, $data, $apply);
	}
	
	/**
	 * Adapter proxy
	 */
	public function download($id, $mode, $type)
	{
		return $this->adapter()->download($id, $mode, $type);
	}
	
	/**
	 * Direct adapter()->call() execution - experimental stage
	 */
	public function __call($method, $arguments)
	{
		if(strpos($method, 'get') === 0 || strpos($method, 'do') === 0)
		{
			return $this->adapter()->call($method, $arguments);
		}
		throw new Exception("Error Processing Request", 10);
	}
	

	public function __destruct()
	{
		$this->adapter = null;
		//echo "Adapter destroyed", PHP_EOL;
	}


	/**
	 * @param $data - e107.org plugin/theme feed data.
	 * @return bool|string
	 */
	public function getDownloadModal($type='plugin',$data)
	{

		$url = false;

		if($type === 'plugin')
		{

			if(empty($data['plugin_id']))
			{

				$srcData = array(
					'plugin_id'     => $data['params']['id'],
					'plugin_folder' => $data['folder'],
					'plugin_price'  => $data['price'],
					'plugin_mode'   => $data['params']['mode'],
					'plugin_url'    => $data['url'],
				);
			}
			else
			{
				$srcData = $data;
			}

			$d = http_build_query($srcData,false,'&');

		//	if(deftrue('e_DEBUG_PLUGMANAGER'))
			{
				$url = e_ADMIN.'plugin.php?mode=online&action=download&src='.base64_encode($d);
			}
		//	else
			{
			//	$url = e_ADMIN.'plugin.php?mode=download&src='.base64_encode($d);
			}


		}

		if($type === 'theme')
		{
			$srcData = array(
				'id'    => $data['params']['id'],
				'url'   => $data['url'],
				'mode'  => 'addon',
				'price' => $data['price']
			);

			$d = http_build_query($srcData,false,'&');
			$url = e_ADMIN.'theme.php?mode=main&action=download&src='.base64_encode($d);//$url.'&amp;action=download';

		}


		return $url;

	}





	public function getVersionList($type='plugin')
	{
		$cache = e107::getCache();
		$cache->setMD5('_', false);

		$tag = 'Versions_'.$type;

		if($data = $cache->retrieve($tag,(60 * 12), true, true))
		{
			return e107::unserialize($data);
		}

	//	$mp = $this->getMarketplace();
	//	$mp->generateAuthKey($e107SiteUsername, $e107SiteUserpass);
		e107::getDebug()->log("Retrieving ".$type." version list from e107.org");

		$xdata = $this->call('getList', array(
			'type' => $type,
			'params' => array('limit' => 200, 'search' => null, 'from' => 0)
		));

		$arr = array();

		if(!empty($xdata['data']))
		{

			foreach($xdata['data'] as $row)
			{
				$k = $row['folder'];
				$arr[$k] = $row;
			}

		}


		if(empty($arr))
		{
			$arr = array('-unable-to-connect'); // make sure something is cached so further lookups stop.
		}

		$data = e107::serialize($arr, 'json');
		$cache->set($tag, $data, true, true, true);

		return $arr;

	}



}

abstract class e_marketplace_adapter_abstract
{
	/**
	 * e107.org download URL
	 * @var string
	 */
	protected $downloadUrl = 'https://e107.org/request/';
	
	/**
	 * e107.org service URL [adapter implementation required]
	 * @var string
	 */
	protected $serviceUrl = null;
	
	/**
	 * Request method POST || GET  [adapter implementation required]
	 * @var string
	 */
	public $requestMethod = null;
	

	/**
	 * @var eAuth
	 */
	protected $_auth = null;
	
	/**
	 * e107.org authorization key
	 * @deprecated subject of removal
	 * @var string
	 */
	protected $authKey = null;
	
	abstract public function test($input);
	//abstract public function call($method, $data, $apply);
	abstract public function call($method, $data, $apply = true); // Fix issue #490
	abstract public function fetch($method, &$result);
	
	/**
	 * Authorization object
	 * @return eAuth
	 */
	public function auth()
	{
		if(null === $this->_auth)
		{
			$this->_auth = new eAuth;
			$this->_auth->loadSysCredentials();
			$this->_auth->requestMethod = $this->requestMethod;
		}
		return $this->_auth;
	}
	
	/**
	 * Set authorization key
	 * @deprecated subject of removal
	 */
	public function setAuthKey($authkey)
	{
		$this->authKey = $authkey;
		return $this;
	}
	
	/**
	 * @deprecated subject of removal
	 */
	public function hasAuthKey()
	{
		return ($this->authKey !== null) ? true : false;	
	}
	
	/**
	 * @deprecated subject of removal
	 */
	public function getAuthKey()
	{
		return $this->authKey;	
	}
	
	
	/**
	 * Download a Plugin or Theme to Temp, then test and move to plugin/theme folder and backup to system backup folder. 
	 * XXX better way to return status (e.g. getError(), getStatus() service call before download)
	 * XXX temp is not well cleaned
	 * XXX themes/plugins not well tested after unzip (example - Headline 1.0, non-default structure, same applies to most FS net free themes)
	 * This method is direct outputting the status. If not needed - use buffer
	 * @param string $remotefile URL
	 * @param string $type plugin or theme
	 */
	public function download($id, $mode, $type)
	{
		$tp = e107::getParser();
		$mes = e107::getMessage();
		$fl = e107::getFile();
		
		$id = intval($id);
		$qry = 'id='.$id.'&type='.$type.'&mode='.$mode;
		$remotefile = $this->downloadUrl."?auth=".$this->getAuthKey()."&".$qry;

		$localfile = md5($remotefile.time()).".zip";
		$mes->addSuccess(TPVLAN_81); 
	
		// FIXME call the service, check status first, then download (if status OK), else retireve the error break and show it
		
		$result 	= $this->getRemoteFile($remotefile, $localfile);
		
		if(!$result)
		{
			if(filesize(e_TEMP.$localfile))
			{
				$contents = file_get_contents(e_TEMP.$localfile);
				$contents = explode('REQ_', $contents);
				$mes->addError('[#'.trim($contents[1]).'] '.trim($contents[0])); flush(); 
			}
			
			@unlink(e_TEMP.$localfile);
			return false;
		}

		
		if(!file_exists(e_TEMP.$localfile))
		{
			$srch = array("[", "]");
			$repl = array("<a href='".$remotefile."'>", "</a>");

			$mes->addError( TPVLAN_83." ".str_replace($srch, $repl, TPVLAN_84));
			
			if(E107_DEBUG_LEVEL > 0)
			{
				$mes->addDebug('local='.$localfile); // ; flush(); 
			}

			return false;
		}
		
		
		if($fl->unzipArchive($localfile,$type, true))
		{
			$mes->addSuccess(TPVLAN_82); 
			return true; 
		}
		else 
		{
			$mes->addSuccess( "<a href='".$remotefile."'>".TPVLAN_84."</a>");
		}
		
		return false; 
	}
			
		

	// Grab a remote file and save it in the /temp directory. requires CURL
	function getRemoteFile($remote_url, $local_file, $type='temp')
	{
		// FIXME - different methods (see xml handler getRemoteFile()), error handling, appropriate error messages, 
		if (!function_exists("curl_init")) 
		{
			return false;
		}
		$path = ($type == 'media') ? e_MEDIA : e_TEMP; 
		
        $fp = fopen($path.$local_file, 'w'); // media-directory is the root. 
        //$fp1 = fopen(e_TEMP.'/curllog.txt', 'w'); 


        $cp = e107::getFile()->initCurl($remote_url);
        curl_setopt($cp, CURLOPT_FILE, $fp);
     /*   $cp = curl_init($remote_url);

		
		//curl_setopt($ch, CURLOPT_VERBOSE, 1);
		//curl_setopt($ch, CURLOPT_STDERR, $fp1);
		
		curl_setopt($cp, CURLOPT_REFERER, e_REQUEST_HTTP);
		curl_setopt($cp, CURLOPT_HEADER, 0);
		curl_setopt($cp, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
		curl_setopt($cp, CURLOPT_COOKIEFILE, e_SYSTEM.'cookies.txt');*/

        $buffer = curl_exec($cp);
       	
        curl_close($cp);
        fclose($fp);
		//fclose($fp1);
		
		if($buffer)
		{
			$size = filesize($path.$local_file);
			if($size < 400) $buffer = false;
		}
		
        return ($buffer) ? true : false;
    }
}

class e_marketplace_adapter_wsdl extends e_marketplace_adapter_abstract
{
	/**
	 * e107.org WSDL URL
	 * @var string
	 */
	protected $serviceUrl = 'https://e107.org/service?wsdl';
	
	/**
	 * Request method POST || GET
	 * @var string
	 */
	public $requestMethod = 'POST';	
	
	/**
	 * Soap client instance
	 * @var SoapClient
	 */
	 protected $client = null;
	
	public function __construct()
	{
		e107_ini_set('soap.wsdl_cache_enabled', 0);
		e107_ini_set('soap.wsdl_cache_ttl', 0);
		
		$options = array(
			"trace" 				=> true, 
			'exception' 			=> true,
		    "uri" 					=> "http://server.soap.e107.inc.com/",
		    'cache_wsdl'			=> WSDL_CACHE_NONE,
		    'connection_timeout' 	=> 5,
		);


		try
		{
			//libxml_disable_entity_loader(false);
            $this->client = new SoapClient($this->serviceUrl, $options);
        }
        catch (Exception $e)
        {
	        $message = deftrue('LAN_ERROR_CONNECTION', "Unable to connect for updates. Please check firewall and/or internet connection.");
            e107::getMessage()->addInfo($message);
            e107::getMessage()->addDebug($e->getMessage());
        }



		if(function_exists('xdebug_disable'))
		{
			xdebug_disable();
		}
	}
	
	public function test($input)
	{
		try
		{
			$res = $this->client->get_echo($input);
		}
		catch(Exception $e)
		{
			$res = $e->getMessage();
		}
		return $res;
		
	}
	
	/**
	 * Generic call method
	 */
	public function _call($method, $args, $apply = true)
	{
		$result = array(
			'data' => null,
			//'error'=> array('code' => 0, 'message' => null)
		);
		$ret = null;
		
		// authorize on every call, service class decides what to do on every method call
		$auth = new stdClass;
		$auth->authKey = $this->getAuthKey();
		$header = new SoapHeader('https://e107.org/services/auth', 'checkAuthHeader', $auth);

		if(!is_object($this->client))
		{
			$result['exception'] = array();
			$result['exception']['message'] = "Unable to connect at this time.";
			return $result;
		}
		
		try
		{


			$this->client->__setSoapHeaders(array($header));
			if(is_array($args) && $apply)
			{
				$ret = call_user_func_array(array($this->client, $method), $args);
			}
			else $ret = $this->client->$method($args);
			
			$result = $ret;
			if(isset($ret['exception']))
			{
				$result['exception'] = array();
				$result['exception']['message'] = "API Exception [call::{$method}]: (#".$ret['exception']['code'].") ".$ret['exception']['message'];
				$result['exception']['code'] 	= 'API_'.$ret['exception']['code'];
			}
			unset($ret);
		}
		catch(SoapFault $e)
		{
			$result['exception']['message'] = "SoapFault Exception [call::{$method}]: (#".$e->faultcode.") ".$e->faultstring;
			$result['exception']['code'] 	= 'SOAP_'.$e->faultcode;
			if(E107_DEBUG_LEVEL)
			{
				$result['exception']['trace'] = $e->getTraceAsString(); 
				$result['exception']['message'] .= ". Header fault: ".($e->headerfault ? $e->headerfault : 'n/a');
			}
		}
		catch(Exception $e)
		{
			$result['exception']['message'] = "Generic Exception [call::{$method}]: (#".$e->getCode().") ".$e->getMessage();
			$result['exception']['code'] 	= 'GEN_'.$e->getCode();
			if(E107_DEBUG_LEVEL)
			{
				$result['debug']['trace'] = $e->getTraceAsString(); 
			}
		}
		if(E107_DEBUG_LEVEL)
		{
			$result['debug']['response'] = $this->client->__getLastResponse(); 
			$result['debug']['request'] = $this->client->__getLastRequest(); 
			$result['debug']['request_header'] = $this->client->__getLastRequestHeaders(); 
		}
		return $result;
	}
	
	/**
	 * Public call method
	 */
	public function call($method, $data, $apply = true)
	{
		return $this->_call($method, $data, $apply);
	}
	
	/**
	 * Prepare the result, not needed for WSDL
	 * SUBJECT OF REMOVAL
	 */
	public function fetch($method, &$result)
	{
		if(isset($result['error']))
		{
			return $result;
		}
		
		switch ($method) 
		{
			case 'getList':
			break;
		}
		return $result;
	}

	public function __destruct()
	{
		$this->client = null;
		//echo "SOAP Client destroyed", PHP_EOL;
	}
}

class e_marketplace_adapter_xmlrpc extends e_marketplace_adapter_abstract
{
	/**
	 * e107.org XML-rpc service
	 * @var xmlClass
	 */
	protected $serviceUrl = 'https://e107.org/xservice';
	
	/**
	 * Request method POST || GET
	 * @var string
	 */
	public $requestMethod = 'GET';
	
	protected $_forceArray = array();
	protected $_forceNumericalArray = array();
	
	public function __construct()
	{
	}
	
	public function test($input)
	{
		
	}
	
	public function call($method, $data, $apply = true)
	{
		$client = $this->client();

		// settings based on current method
		$this->prepareClient($method, $client);
		
		// authorization data
		$data['auth'] = $this->getAuthKey();
		$data['action'] = $method;

		foreach($data['params'] as $k=>$v)
		{
			$data[$k] = $v;
		}
		unset($data['params']);


		// build the request query
		$qry = str_replace(array('s%5B', '%5D'), array('[', ']'), http_build_query($data, null, '&'));
		$url = $this->serviceUrl.'?'.$qry;
		$result = array();
		
		// call it
		try
		{
			$xmlString = $client->loadXMLfile($url,false);
			$xml = new SimpleXMLIterator($xmlString);
			//$result = $client->loadXMLfile($url, 'advanced');
			$result = $this->fetch($method, $xml);
			if(isset($result['exception']))
			{
				$exception = $result['exception'];
				$result['exception'] = array();
				$result['exception']['message'] = "API Exception [call::{$method}]: (#".$exception['code'].") ".$exception['message'];
				$result['exception']['code'] 	= 'API_'.$exception['code'];
			}
		}
		catch(Exception $e)
		{
			$result['exception']['message'] = "Generic Exception [call::{$method}]: (#".$e->getCode().") ".$e->getMessage();
			$result['exception']['code'] 	= 'GEN_'.$e->getCode();
		}
		return $result;
	}
	
	public function fetch($method, &$result)
	{
		$ret = $this->parse($result);
		$this->fetchParams($ret);
		
		switch ($method) 
		{
			// normalize
			case 'getList':
				$ret['data'] = $ret['data']['item'];
			break;
		}
		return $ret;
	}

	/**
	 * New experimental XML parser, will be moved to XML handlers soon
	 * XXX replace xmlClass::xml2array() after this one passes all tests
	 * @param SimpleXmlIterator $xml
	 * @param string $parentName parent node name - used currently for debug only
	 * @return array|string
	 */
	public function parse($xml, $parentName = null)
	{
		$ret = array();
		$tags = array_keys(get_object_vars($xml));
		$count = $xml->count();
		$tcount = count($tags);
		
		if($count === 0)
		{
			$attr = (array) $xml->attributes();
			if(!empty($attr))
			{
				$ret['@attributes'] = $attr['@attributes'];
				$ret['@value'] = (string) $xml;
				$ret['@value'] = trim($ret['@value']);
			}
			else
			{
				$ret = (string) $xml;
				$ret = trim($ret);
			}
			return $ret;
		}
		
		/**
		 * <key>
		 * 	<value />
		 * 	<value />
		 * </key>
		 */
		if($tcount === 1 && $count > 1)
		{
			foreach ($xml as $name => $node) 
			{
				$_res = $this->parse($node, $name);
				if(is_string($_res))
				{
					 $_res = trim($_res);
				}
				
				$ret[$name][] = $this->parse($node, $name);
			}
		}
		// default
		else
		{
			foreach ($xml as $name => $node) 
			{
				if(in_array($name, $this->_forceArray))
				{
					$_res = $this->parse($node, $name);
					if(is_string($_res)) $_res = trim($_res);
					
					if(empty($_res)) $ret[$name] = array(); // empty
					elseif(is_string($_res)) $ret[$name][] = $_res; // string
					else 
					{
						if(in_array($name, $this->_forceNumericalArray)) $ret[$name][] = $_res; //array - controlled force numerical array
						else $ret[$name] = $_res; //array, no force
					}
				}
				else $ret[$name] = $this->parse($node, $name);
			}
		}
		

		$attr = (array) $xml->attributes();
		if(!empty($attr))
		{
			$ret['@attributes'] = $attr['@attributes'];
		}

		return $ret;
	}
	
	/**
	 * Normalize parameters/attributes
	 * @param array $result parsed to array XML response data
	 */
	public function fetchParams(&$result)
	{
		foreach ($result as $tag => $data) 
		{
			if($tag === 'params')
			{
				foreach ($data['param'] as $i => $param)
				{
					$result[$tag][$param['@attributes']['name']] = $param['@value'];
					unset($result[$tag]['param'][$i]);
				}
				unset($result[$tag]['param']);
			}
			elseif($tag === 'exception')
			{
				$result['exception'] = array('code' => (int) $result['exception']['@attributes']['code'], 'message' => $result['exception']['@value']);
				//unset($result['exception']);
			}
			elseif($tag === '@attributes')
			{
				$result['params'] = $result['@attributes'];
				unset($result['@attributes']);
			}
			elseif(is_array($result[$tag]))
			{
				$this->fetchParams($result[$tag]);
			}
		}
	}
	
	/**
	 * @param string $method
	 * @param xmlClass $client
	 */
	public function prepareClient($method, &$client)
	{
		switch ($method) 
		{
			case 'getList':
				$this->_forceArray = array('item', 'screenshots', 'image');
				$this->_forceNumericalArray = array('item', 'image');
				//$client->setOptArrayTags('item,screenshots,image')
				//	->setOptStringTags('icon,folder,version,author,authorURL,date,compatibility,url,thumbnail,featured,livedemo,price,name,description,category,image');
			break;
		}
	}
	
	/**
	 * @return xmlClass
	 */
	public function client()
	{
		return e107::getXml(false);
	}
}

class eAuth
{
	
	/**
	 * e107.org manage client credentials (Consumer Key and Secret) URL 
	 * @var string
	 */
	protected $eauthConsumerUrl = 'https://e107.org/eauth/client';
	
	/**
	 * URL used to make temporary credential request (Request Token and Secret) to e107.org before the authorization phase
	 * @var string
	 */
	protected $eauthRequestUrl = 'https://e107.org/eauth/initialize';
	
	/**
	 * URL used to redirect and authorize the resource owner (user) on e107.org using temporary (request) token
	 * @var string
	 */
	protected $eauthAuthorizeUrl = 'https://e107.org/eauth/authorize';
	
	/**
	 * URL used to obtain token credentials (Access Token and Secret) from e107.org using temporary (request) token
	 * @var string
	 */
	protected $eauthAccessUrl = 'https://e107.org/eauth/token';
	
	/**
	 * Public client key (generated and obtained from e107.org)
	 * @var string
	 */
	public $eauthConsumerKey = null;
	
	/**
	 * Client shared secret (generated and obtained from e107.org)
	 * @var string
	 */
	public $eauthConsumerSecret = null;
	
	/**
	 * Public temporary request token (generated and obtained from e107.org)
	 * @var string
	 */
	public $eauthRequestKey = null;
	
	/**
	 * Temporary request shared secret (generated and obtained from e107.org)
	 * @var string
	 */
	public $eauthRequestSecret = null;
	
	/**
	 * Public access token (generated and obtained from e107.org)
	 * @var string
	 */
	public $eauthAccessToken = null;
	
	/**
	 * Access shared secret (generated and obtained from e107.org)
	 * @var string
	 */
	public $eauthAccessSecret = null;
	
	/**
	 * Request method POST || GET
	 * @var string
	 */
	public $requestMethod = null;
	
	public function isClient()
	{
		$this->loadSysCredentials();
		return (!empty($this->eauthConsumerKey) && !empty($this->eauthConsumerSecret));
	}
	
	public function isInitialized()
	{
		$this->loadSysCredentials();
		return ($this->isClient() && !empty($this->eauthRequestKey) && !empty($this->eauthRequestSecret));
	}
	
	public function hasAccess()
	{
		$this->loadSysCredentials();
		return ($this->isClient() && !empty($this->eauthAccessToken) && !empty($this->eauthAccessSecret));
	}

	public function serviceAuthData($method, $args, $toObject = true)
	{
		// The client has previously registered with the server and obtained the client identifier dpf43f3p2l4k3l03 and client secret kd94hf93k423kf44. 
		// It has executed the eAuth workflow and obtained an access token nnch734d00sl2jdk and token secret pfkkdhi9sl3r4s00
		
		$date = gmdate('Y-m-d H:i:s');
		$timestamp = $this->gmtTime($date);
		$nonce = $this->nonce($timestamp); // create nonce
		
		$cryptMethod = $this->cryptMethod();
		$authData = array(
			'eauth_consumer_key' 	=> $this->eauthConsumerKey, // (Client Identifier) Application key
			'eauth_token' 			=> $this->eauthAccessToken, // Access Token 
			'eauth_nonce' 			=> $nonce,//'kllo9940pd9333jh' 'nonce' (number used once) string  
			'eauth_timestamp' 		=> $timestamp, // timestamp
			'eauth_signature_method'=> $cryptMethod, // encryption method
			'eauth_version'			=> '1.0', // signature method
		);
		
		// current request parameters
		$args['action'] = $method;
		
		// signature data for building the signature
		$signatureData = $authData;
		
		// add request parameters to the signature array
		$signatureData['eauth_request_params'] = $args;
		
		// sort all
		self::array_kmultisort($signatureData);
		
		// signature base string
		$signatureBaseString = $this->requestMethod.'&'.rawurlencode($this->serviceUrl).'&'.http_build_query($signatureData, false, '&');
		$secretKey = rawurlencode($this->eauthConsumerSecret).'&'.rawurlencode($this->eauthAccessSecret);
		
		// crypt it
		$signature = $this->crypt($signatureBaseString, $secretKey);
		
		//encode it
		$authData['eauth_signature'] = base64_encode($signature);
		if($toObject) return self::toObject($authData);
		
		return $authData;
	}


	public static function toObject($array)
	{
		$obj = new stdClass;
		foreach ($array as $key => $value) 
		{
			$obj->$key = $value;
		}
		return $obj;
	}
	
	/**
	 * Load credentials stored in a system file
	 * @param boolean $force
	 * @return e_marketplace_adapter_abstract adapter instance
	 */
	public function loadSysCredentials($force = false)
	{
		if($force || null === $this->eauthConsumerKey)
		{
			$data = e107::getArrayStorage()->load('eauth');
			if(empty($data)) $data = array();
			$this->eauthConsumerKey = varset($data['consumer_key'], '');
			$this->eauthConsumerSecret = varset($data['consumer_secret'], '');
			$this->eauthAccessToken = varset($data['access_token'], '');
			$this->eauthAccessSecret = varset($data['access_secret'], '');
		}
		return $this;
	}
	
	public function storeSysCredentials($credentials = null)
	{
		if(null === $credentials)
		{
			$credentials = array(
				'consumer_key'		=> $this->eauthConsumerKey,
				'consumer_secret'	=> $this->eauthConsumerSecret,
				'access_token'		=> $this->eauthAccessToken,
				'access_secret'		=> $this->eauthAccessSecret,
			);
		}
		if(!is_array($credentials)) return false;
		
		foreach ($credentials as $key => $value) 
		{
			switch ($key) 
			{
				case 'consumer_key':
				case 'consumer_secret':
				case 'access_token':
				case 'access_secret':
					// OK
				break;
				
				default:
					unset($credentials[$key]);
				break;
			}
		}
		
		return e107::getArrayStorage()->store($credentials, 'eauth');
	}
	
	/**
	 * Retrieve available system credentials or credential value
	 * @param string $key [optional]
	 * return mixed array of all credentials or string credential value
	 */
	public function getCredentials($key = null)
	{
		$this->loadSysCredentials();
		
		$credentials = array(
			'consumer_key'		=> $this->eauthConsumerKey,
			'consumer_secret'	=> $this->eauthConsumerSecret,
			'access_token'		=> $this->eauthAccessToken,
			'access_secret'		=> $this->eauthAccessSecret,
		);
		if(null !== $key) return varset($credentials[$key], null);
		return $credentials;
	}
	
	public function toAuthHeader($params)
	{
		$first = true;
		$realm = isset($params['realm']) ? $params['realm'] : null;
		if($realm)
		{
			$out = 'Authorization: eAuth realm="'.rawurlencode($realm).'"';
			$first = false;
		}
		else
			$out = 'Authorization: eAuth';

		$total = array();
		foreach($params as $k => $v)
		{
			if(substr($k, 0, 5) != "eauth") continue;
			if(is_array($v))
			{
				throw new Exception('Arrays not supported in headers', 200);
			}
			$out .= ($first) ? ' ' : ',';
			$out .= rawurlencode($k).'="'.rawurlencode($v).'"';
			$first = false;
		}
		return $out;
	}
	

	public function cryptMethod()
	{
		return function_exists('hash_hmac') ? 'HMAC-SHA1' : 'SHA1';
	}
	
	function random($bits = 256) 
	{
	    $bytes = ceil($bits / 8);
	    $ret = '';
	    for ($i = 0; $i < $bytes; $i++) 
	    {
	        $ret .= chr(mt_rand(0, 255));
	    }
	    return $ret;
	}
	
	public function crypt($string, $secretKey)
	{
		$cMethod = $this->cryptMethod();
		// Append secret if it's sha1
		if($cMethod == 'SHA1')
		{
			return sha1($string.$secretKey);
		}
		// use secret key if HMAC-SHA1
		return hash_hmac('sha1', $string, $secretKey);
	}
	
	public function nonce($timestamp)
	{
		return $this->crypt($this->random().$timestamp, $this->eauthAccessSecret.$this->eauthConsumerSecret);
	}

	public function gmtTime($string)
	{
		$ret = false;
		// mask - Y-m-d H:i:s
		if(preg_match('#(.*?)-(.*?)-(.*?) (.*?):(.*?):(.*?)$#', $string, $matches))
		{
			$ret = gmmktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
		}
		return $ret;
	}

	public static function array_kmultisort(&$array, $order = 'asc')
	{
		$func = $order == 'asc' ? 'ksort' : 'krsort';
		$func($array);
		foreach ($array as $key => $value) 
		{
			if(is_array($value))
			{
				self::array_kmultisort($value, $order);
				$array[$key] = $value;
			}
		}
	}
}
