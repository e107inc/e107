<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }
/**
Usage: A file with the following format should be stored on a server, and included in the releaseUrl attribute of theme.xml or plugin.xml
' <?xml version='1.0' encoding='utf-8' ?>
<e107Release>
	<theme name='e107.v4' folder='e107v4a' version='3.1' date='2009-06-11' compatibility='0.8' url='http://www.e107.org/edownload.php?".$_GET['folder']."' />
    <theme name='e107.v5' folder='e107v5a' version='3.6' date='2009-06-11' compatibility='0.8' url='http://www.e107.org/edownload.php' />
</e107Release>

For themes, use <theme name='... etc.
For plugins, use <plugin name='.... etc


*/


// XXX TODO FIXME REQUIRES Overhaul for use with the e107.org 'shop' only. 

class e_upgrade
{
    protected $_options = array();

	/**
	 *
	 * @param string $curFolder - folder name of the plugin or theme to check
	 * @param string $curVersions - installed version of the plugin or theme.
	 * @param string $releaseUrl - url of the XML file in the above format.
	 * @param boolean $cache
	 * @return $this
	 */

    public function setOptions($dataArray)
	{
		$this->_options = $dataArray;
		return $this;
	}

	public function getOption($key, $default = '')
	{
		return varset($this->_options[$key], $default);
	}

	public function releaseCheck($mode='plugin', $cache=TRUE)
	{
		global $e107cache;

	    if(!$this->getOption('releaseUrl'))
		{
	    	return;
		}

        $cacheString = $mode. 'UpdateCheck';

		$e107cache->CachePageMD5 = md5($cacheString.$this->getOption('curFolder').$this->getOption('curVersion', 1.0));

		if(($cache==TRUE) && ($cacheData = $e107cache->retrieve($cacheString, 3600, TRUE)))
		{
			$mes = e107::getMessage();
			$mes->addInfo($cacheData);
			$e107cache->CachePageMD5  = "";
			return;
		}

		$xml = e107::getXml();
        $feed = $this->getOption('releaseUrl');

	 	if(substr($feed,-4) == ".php")
		{
        	 $feed .= "?folder=".$this->getOption('curFolder')."&version=".$this->getOption('curVersion');
		}

		if($rawData = $xml -> loadXMLfile($feed, TRUE))
		{
	    	if(!$rawData[$mode][1])
			{
	        	$rawData[$mode] = $rawData;
			}

	        $txt = "";
            $lan_text = ($mode == "theme") ? ADLAN_162 : ADLAN_163;

	        foreach($rawData[$mode] as $val)
			{
				$name    	= $val['@attributes']['name'];
				$folder    	= $val['@attributes']['folder'];
				$version 	= $val['@attributes']['version'];
				$url 		= $val['@attributes']['url'];

	            if(($folder == $this->getOption('curFolder'))  && version_compare($version,$this->getOption('curVersion'))==1)
				{
	             	$txt .= $lan_text." <a href='".$url."'>".$name ." v".$version."</a><br />";
					break;
				}
	        }

			if($txt)
			{
				$mes->addInfo($txt);
				if($cache==TRUE)
				{
					$e107cache->set($cacheString, $txt, TRUE);

				}
				$e107cache->CachePageMD5  = "";
			}
		}


	}
	
	

    function checkAllPlugins()
	{
		$pref = e107::getPref();
		$sql = e107::getDB();
        if($sql ->gen("SELECT * FROM #plugin WHERE plugin_installflag = 1 AND plugin_releaseUrl !=''"))
		{
	        while($row = $sql->fetch())
	        {
				$options = array('curFolder' => $row['plugin_path'], 'curVersion' => $row['plugin_version'], 'releaseUrl' => $row['plugin_releaseUrl']);
				$this->setOptions($options);
				$this->releaseCheck('plugin',FALSE);
			}

		}
 	}

	function checkSiteTheme()
	{
        $curTheme 	= e107::getPref('sitetheme');
		$curVersion = e107::getPref('sitetheme_version');
		$curUrl 	= e107::getPref('sitetheme_releaseUrl');

        $options = array('curFolder' => $curTheme, 'curVersion' => $curVersion, 'releaseUrl' => $curUrl);
		$this->setOptions($options);
		$this->releaseCheck('theme',FALSE);
	}
	
	function listLangPacks()
	{
		
	}


}