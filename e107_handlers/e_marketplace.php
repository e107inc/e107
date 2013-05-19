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

	
	public function __construct($force = null)
	{
		if(null !== $force)
		{
			$className = 'e_marketplace_adapter_'.$force;
			$this->adapter = new $className();
		}
		elseif(!class_exists('SoapClient')) $this->adapter = new e_marketplace_adapter_xmlrpc();
		else
		{
			$this->adapter = new e_marketplace_adapter_wsdl();
		}
	}
	
	/**
	 * Set authorization key
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
	 */
	public function makeAuthKey($username, $password = '', $plain = false)
	{
		$now 	= gmdate('y-m-d H');
		if($plain && !empty($password)) $password = md5($password);
		return sha1($username.$password.$now);
	}



	/**
	 * Have the admin enter their e107.org login details in order to create the authorization key. 
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
	 */
	public function adapter()
	{
		return $this->adapter;
	}
	
	/**
	 * Retrieve currently used adapter
	 * @param e_marketplace_adapter_abstract
	 */
	public function call($method, $data, $apply = true)
	{
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

}

abstract class e_marketplace_adapter_abstract
{
	/**
	 * e107.org download URL
	 * @var string
	 */
	protected $downloadUrl = 'http://e107.org/request';	
	
	/**
	 * e107.org authorization key
	 * @var string
	 */
	protected $authKey = null;
	
	abstract public function test($input);
	abstract public function call($method, $data, $apply);
	abstract public function fetch($method, &$result);
	
	/**
	 * Set authorization key
	 */
	public function setAuthKey($authkey)
	{
		$this->authKey	= $authkey;
		return $this;
	}
	
	
	public function hasAuthKey()
	{
		return ($this->authKey !== null) ? true : false;	
	}
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
		$id = intval($id);
		$qry = 'id='.$id.'&type='.$type.'&mode='.$mode;
		$remotefile = $this->downloadUrl."?auth=".$this->getAuthKey()."&".$qry;

		$localfile = md5($remotefile.time()).".zip";
		echo "Downloading...<br />"; 
		flush(); 
		// FIXME call the service, check status first, then download (if status OK), else retireve the error break and show it
		
		$result 	= $this->getRemoteFile($remotefile, $localfile);
		
		if(!$result)
		{
			echo "Download Error.<br />"; flush(); 
			if(filesize(e_TEMP.$localfile))
			{
				$contents = file_get_contents(e_TEMP.$localfile);
				$contents = explode('REQ_', $contents);
				echo '[#'.trim($contents[1]).'] '.trim($contents[0]); flush(); 
			}
			@unlink(e_TEMP.$localfile);
			return;
		}
		if(!file_exists(e_TEMP.$localfile))
		{
			//ADMIN_FALSE_ICON
			echo "Automated download not possible. Please <a href='".$remotefile."'>Download Manually</a>"; flush();
			
			if(E107_DEBUG_LEVEL > 0)
			{
				echo '<br />local='.$localfile; flush(); 
			}

			return;
		}
		/*
		else 
				{
					$contents = file_get_contents(e_TEMP.$localfile);
					if(strlen($contents) < 400)
					{
						echo "<script>alert('".$tp->toJS($contents)."')</script>";
						return;	
					}
		}*/
		
		
		chmod(e_TEMP.$localfile, 0755);
		require_once(e_HANDLER."pclzip.lib.php");
		
		$archive 	= new PclZip(e_TEMP.$localfile);
		$unarc 		= ($fileList = $archive -> extract(PCLZIP_OPT_PATH, e_TEMP, PCLZIP_OPT_SET_CHMOD, 0755)); // Store in TEMP first. 
		$dir 		= e107::getFile(true)->getRootFolder($unarc);	
		$destpath 	= ($type == 'theme') ? e_THEME : e_PLUGIN;
		$typeDiz 	= ucfirst($type);
		
		@copy(e_TEMP.$localfile, e_BACKUP.$dir.".zip"); // Make a Backup in the system folder. 
		
		if($dir && is_dir($destpath.$dir))
		{
			echo "(".ucfirst($type).") Already Downloaded - ".basename($destpath).'/'.$dir; flush(); 
			@unlink(e_TEMP.$localfile);
			return;
		}
	
		if($dir == '')
		{
			echo "Couldn't detect the root folder in the zip."; flush();
			@unlink(e_TEMP.$localfile);
			return;		
		}
	
		if(is_dir(e_TEMP.$dir)) 
		{
			echo "Unzipping...<br />";
			if(!rename(e_TEMP.$dir,$destpath.$dir))
			{
				echo "Couldn't Move ".e_TEMP.$dir." to ".$destpath.$dir." Folder"; flush(); usleep(50000);
				@unlink(e_TEMP.$localfile);
				return;
			}	
			
			echo "Download Complete!<br />"; flush();
			
		//	$dir 		= basename($unarc[0]['filename']);
		//	$plugPath	= preg_replace("/[^a-z0-9-\._]/", "-", strtolower($dir));	
			//$status = "Done"; // ADMIN_TRUE_ICON;		
			@unlink(e_TEMP.$localfile);	
			return;
		}
		else 
		{
			//ADMIN_FALSE_ICON.
			echo "<a href='".$remotefile."'>Download Manually</a>"; flush(); usleep(50000);
			if(E107_DEBUG_LEVEL > 0)
			{
				echo print_a($unarc, true); flush();
			}
		}
		
		@unlink(e_TEMP.$localfile);
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
       
        $cp = curl_init($remote_url);
		curl_setopt($cp, CURLOPT_FILE, $fp);
		
		//curl_setopt($ch, CURLOPT_VERBOSE, 1);
		//curl_setopt($ch, CURLOPT_STDERR, $fp1);
		
		curl_setopt($cp, CURLOPT_REFERER, e_REQUEST_HTTP);
		curl_setopt($cp, CURLOPT_HEADER, 0);
		curl_setopt($cp, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
		curl_setopt($cp, CURLOPT_COOKIEFILE, e_SYSTEM.'cookies.txt');

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
	protected $wsdl = 'http://e107.org/service?wsdl';
	
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
		    'connection_timeout' 	=> 60,
		);

		$this->client = new SoapClient($this->wsdl, $options);

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
		$header = new SoapHeader('http://e107.org/services/auth', 'checkAuthHeader', $auth);
		
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
				$result['exception']['trace'] = $e->getTraceAsString(); 
			}
		}
		if(E107_DEBUG_LEVEL)
		{
			$result['exception']['response'] = $this->client->__getLastResponse(); 
			$result['exception']['request'] = $this->client->__getLastRequest(); 
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
	protected $url = 'http://e107.org/xservice';
	
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
		
		// build the request query
		$qry = str_replace(array('s%5B', '%5D'), array('[', ']'), http_build_query($data, null, '&'));
		$url = $this->url.'?'.$qry;
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
				if(is_string($_res)) $_res = trim($res);
				
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
				foreach ($data['param'] as $param) 
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
