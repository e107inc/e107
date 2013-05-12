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
	protected $downloadUrl = 'http://e107.org/e107_plugins/addons/request.php';	
	
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
	 * @param string $remotefile URL
	 * @param string $type plugin or theme
	 */
	public function download($id, $type='theme')
	{
		$tp = e107::getParser();
		$id = intval($id);
		$qry = 'id='.$id;
		$remotefile = $this->downloadUrl."?auth=".$this->getAuthKey()."&".$qry;
				
		$localfile = md5($remotefile.time()).".zip";
		$status 	= "Downloading...";
		
		$result 	= $this->getRemoteFile($remotefile, $localfile);
		
		if(!file_exists(e_TEMP.$localfile))
		{
			$status = ADMIN_FALSE_ICON."<br /><a href='".$remotefile."'>Download Manually</a>";
			
			if(E107_DEBUG_LEVEL > 0)
			{
				$status .= 'local='.$localfile;
			}

			echo $status;
			exit;	
		}
		else 
		{
			$contents = file_get_contents(e_TEMP.$localfile);
			if(strlen($contents) < 400)
			{
				echo "<script>alert('".$tp->toJS($contents)."')</script>";
				return;	
			}
		}
		
		chmod(e_TEMP.$localfile, 0755);
		require_once(e_HANDLER."pclzip.lib.php");
		
		$archive 	= new PclZip(e_TEMP.$localfile);
		$unarc 		= ($fileList = $archive -> extract(PCLZIP_OPT_PATH, e_TEMP, PCLZIP_OPT_SET_CHMOD, 0755)); // Store in TEMP first. 
		$dir 		= $this->getRootFolder($unarc);	
		$destpath 	= ($type == 'theme') ? e_THEME : e_PLUGIN;
		$typeDiz 	= ucfirst($type);
		
		@copy(e_TEMP.$localfile, e_BACKUP.$dir.".zip"); // Make a Backup in the system folder. 
		
		if($dir && is_dir($destpath.$dir))
		{
			$alert = $tp->toJS(ucfirst($type)." Already Installed".$destpath.$dir);
			echo "<script>alert('".$alert."')</script>";
			echo "Already Installed";
			@unlink(e_TEMP.$localfile);
			exit;	
		}
	
		if($dir == '')
		{
			echo "<script>alert('Couldn\'t detect the root folder in the zip.')</script>";
			@unlink(e_TEMP.$localfile);
			exit;			
		}
	
		if(is_dir(e_TEMP.$dir)) 
		{
			$status = "Unzipping...";
			if(!rename(e_TEMP.$dir,$destpath.$dir))
			{
				$alert = $tp->toJS("Couldn't Move ".e_TEMP.$dir." to ".$destpath.$dir." Folder");
				echo "<script>alert('".$alert."')</script>";
				@unlink(e_TEMP.$localfile);
				exit;	
			}	
			
			$alert = $tp->toJS("Download Complete!");
			echo "<script>alert('".$alert."')</script>";
			
		//	$dir 		= basename($unarc[0]['filename']);
		//	$plugPath	= preg_replace("/[^a-z0-9-\._]/", "-", strtolower($dir));	
			$status = "Done"; // ADMIN_TRUE_ICON;			
			
		}
		else 
		{
			$status = ADMIN_FALSE_ICON."<br /><a href='".$remotefile."'>Download Manually</a>";
			if(E107_DEBUG_LEVEL > 0)
			{
				$status .= print_a($unarc, true);
			}
		}
		
		echo $status;
		@unlink(e_TEMP.$localfile);
		exit;				
	}

	// Grab a remote file and save it in the /temp directory. requires CURL
	function getRemoteFile($remote_url, $local_file, $type='temp')
	{
		$path = ($type == 'media') ? e_MEDIA : e_TEMP; 
		
        $fp = fopen($path.$local_file, 'w'); // media-directory is the root. 
       
        $cp = curl_init($remote_url);
		curl_setopt($cp, CURLOPT_FILE, $fp);
		curl_setopt($cp, CURLOPT_REFERER, e_REQUEST_HTTP);
		curl_setopt($cp, CURLOPT_HEADER, 0);
		curl_setopt($cp, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
		curl_setopt($cp, CURLOPT_COOKIEFILE, e_SYSTEM.'cookies.txt');

        $buffer = curl_exec($cp);
       
        curl_close($cp);
        fclose($fp);
       
        return ($buffer) ? true : false;
    }
}

class e_marketplace_adapter_wsdl extends e_marketplace_adapter_abstract
{
	/**
	 * e107.org WSDL URL
	 * @var string
	 */
	protected $wsdl = 'http://e107.org/e107_plugins/addons/service.php?wsdl';
	
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
		    'connection_timeout' 	=> 10,
		);

		$this->client = new SoapClient($this->wsdl, $options);

		if(function_exists('xdebug_disable'))
		{
			//xdebug_disable();
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
			
			if(isset($ret['exception']))
			{
				$result['error']['message'] = "API Exception [call::{$method}]: (#".$ret['exception']['code'].") ".$ret['exception']['message'];
				$result['error']['code'] 	= $ret['exception']['code'];
				unset($ret['exception']);
			}
			$result['data'] = $ret['data'];
		}
		catch(SoapFault $e)
		{
			$result['error']['message'] = "SoapFault Exception [call::{$method}]: (#".$e->getCode().") ".$e->getMessage();
			$result['error']['code'] 	= 'SOAP_'.$e->getCode();
		}
		catch(Exception $e)
		{
			$result['error']['message'] = "Generic Exception [call::{$method}]: (#".$e->getCode().") ".$e->getMessage();
			$result['error']['code'] 	= 'GEN_'.$e->getCode();
		}
		if(E107_DEBUG_LEVEL)
		{
			print_a($this->client->__getLastRequest());
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
	protected $url = 'http://e107.org/e107_plugins/addons/xservice.php';
	
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
		$this->prepareClient($client);
		
		// authorization data
		$data['auth'] = $this->getAuthKey();
		$data['action'] = $method;
		
		// build the request query
		$qry = str_replace(array('s%5B', '%5D'), array('[', ']'), http_build_query($data, null, '&'));
		$url = $this->url.'?'.$qry;
		
		// call it
		$result = $client->loadXMLfile($url,'advanced');
		//var_dump($url, $result);
		return $result;
	}
	
	public function fetch($method, &$result)
	{
		
	}
	
	public function prepareClient($method, &$client)
	{
		switch ($method) 
		{
			case 'getList':
				$client->setOptArrayTags('plugin');
			break;
		}
	}
	
	public function client()
	{
		return e107::getXml(false);
	}
}
