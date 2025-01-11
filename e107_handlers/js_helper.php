<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Javascript Helper
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/js_helper.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/


/**
 *
 */
class e_jshelper
{
    /**
     * Respons actions array
     *
     * @var array
     */
    protected $_response_actions = array();
    
    /**
     * Prefered response type. Possible values 
     * at this time are 'xml', 'text' and 'json'.
     *
     * @var string
     */
    protected $_prefered_response_type;
    
    /**
     * Constructor
     *
     */
    public function __construct()
    {
    	$this->setPreferedResponseType('text'); // TODO - pref
    }
    
    /**
     * Set prefered response type to be used with
     *  {@link sendResponse()}
     *
     * @param string $response_type xml|json|text
     */
    public function setPreferedResponseType($response_type)
    {
    	$this->_prefered_response_type = $response_type;
    }

    /**
     * Add response action & action instructions
     * 'action' equals to e107Ajax JS method (see JS API)
     *
     * @param string $action
     * @param array $data_array item data for the action
     * @return e_jshelper
     */
    public function addResponseAction($action, $data_array)
    {
        if(!isset($this->_response_actions[$action]))
        {
            $this->_response_actions[$action] = array();
        }
        $this->_response_actions[$action] = array_merge($this->_response_actions[$action], $data_array);

        return $this;
    }
    
    /**
     * Attach response Items array to an action
     * Example: addResponseItem('element-invoke-by-id', 'show', array('category-clear','update-category'));
     * will add  array('category-clear','update-category') to ['element-invoke-by-id']['show'] stack
     *
     * @param string $action
     * @param string $subaction
     * @param array $data item data for the action
     * @return e_jshelper
     */
    public function addResponseItem($action, $subaction, $data)
    {
        if(!isset($this->_response_actions[$action]))
        {
            $this->_response_actions[$action] = array();
        }
        if(!isset($this->_response_actions[$action][$subaction]))
        {
            $this->_response_actions[$action][$subaction] = array();
        }
        
        if(is_array($data))
        {
        	$this->_response_actions[$action][$subaction] = array_merge($this->_response_actions[$action][$subaction], $data);
        }
        else
        {
        	$this->_response_actions[$action][$subaction][] = $data;
        }
        

        return $this;
    }

    /**
     * Response array getter
     *
     * @param bool $reset clear current response actions
     * @return array response actions
     */
    public function getResponseActions($reset = false) {
        if($reset)
        {
            $ret = $this->_response_actions;
            $this->_reset();
            return $ret;
        }
        return $this->_response_actions;
    }

    /**
     * Buld XML response parsed by the JS API
     * Quick & dirty, this will be extended to
     * e107 web service standard (communication protocol).
     *
     * @return string XML response
     */
    public function buildXmlResponse()
    {
        $action_array = $this->getResponseActions(true);
        $ret = '<?xml version="1.0"  encoding="'.CHARSET.'" ?>';
        $ret .= "\n<e107response>\n";
        foreach ($action_array as $action => $field_array)
        {
	        $ret .= "\t<e107action name='{$action}'>\n";
            foreach ($field_array as $field => $value)
	        {

	            if (is_numeric($field) || empty($field))
	            {
		            continue;
	            }

	            switch (gettype($value)) {
	            	case 'array':
	            		foreach ($value as $v)
	            		{
	            			if(is_string($v)) { $v = "<![CDATA[{$v}]]>"; }
	            			$ret .= "\t\t<item type='".gettype($v)."' name='{$field}'>{$v}</item>\n";
	            		}
	            	break;
	            	
	            	case 'string':
	            		$value = "<![CDATA[{$value}]]>";
	            		$ret .= "\t\t<item type='".gettype($value)."' name='{$field}'>{$value}</item>\n";
	            	break;
	            	
	            	case 'boolean':
	            	case 'numeric':
	            		$ret .= "\t\t<item type='".gettype($value)."' name='{$field}'>{$value}</item>\n";
	            	break;
	            }
	        }
	        $ret .= "\t</e107action>\n";
        }
        $ret .= '</e107response>';
        return $ret;
    }

    /**
     * Convert (optional) and send array as XML response string
     *
     * @param string $action optional
     * @param array $data_array optional
     */
    public function sendXmlResponse($action = '', $data_array = array())
    {
        header('Content-type: application/xml; charset='.CHARSET);
        if($action)
    	{
    	    $this->addResponseAction($action, $data_array);
    	}

    	if($action !== null)
	    {
		    echo $this->buildXmlResponse();
	    }
    	while (ob_get_level() > 0)
		{
		    ob_end_flush();
		}
    	exit;
    }

    /**
     * Build JSON response string
     *
     * @return string JSON response
     */
    public function buildJsonResponse($data = null)
    {
    	if($data !== null)
	    {
		    return "/*-secure-\n" . json_encode($data) . "\n*/";
	    }
        return "/*-secure-\n".json_encode($this->getResponseActions(true))."\n*/";
    }

    /**
     * Convert (optional) and send array as JSON response string
     *
     * @param string $action optional
     * @param array $data_array optional
     */
    public function sendJsonResponse($action = '', $data_array = array())
    {
    	header('Content-type: application/json; charset='.CHARSET);
        if($action)
    	{
    	    $this->addResponseAction($action, $data_array);
    	}
		if($action !== null)
		{
			echo $this->buildJSONResponse();
		}
		while (ob_get_level() > 0)
		{
		    ob_end_flush();
		}
    	exit;
    }
    
    /**
     * Add text response data
     *
     * @param string $text
     * @return e_jshelper
     */
    public function addTextResponse($text)
    {
    	if($text)
    	{
    		$this->_response_actions['text']['body'][] = $text;
    	}
    	return $this;
    }
    
    /**
     * Build Text response string
     *
     * @return string
     */
    public function buildTextResponse()
    {
    	$content = $this->getResponseActions(true);
    	if(!isset($content['text']) || !isset($content['text']['body']))
    	{
    		return '';
    	}
        return implode('', $content['text']['body']);
    }

	/**
	 * Add content (optional) and send text response
	 *
	 * @param string $data_text
	 */
    public function sendTextResponse($data_text = '')
    { 
    	header('Content-type: text/html; charset='.CHARSET);
    	echo $this->addTextResponse($data_text)->buildTextResponse();
		while (ob_get_level() > 0)
		{
		    ob_end_flush();
		}
    	exit;
    }

	/**
	 * Send Server Response
	 * Sends the response based on $response_type or the system
	 * prefered response type (could be system preference in the future)
	 *
	 * @param string $response_type
	 * @return boolean success
	 */
    public function sendResponse($response_type = '')
    {
    	if(!$response_type)
    	{
    		$response_type = strtolower($this->_prefered_response_type);
    	}
    	$method = "send{$response_type}Response"; 
    	if(method_exists($this, $method))
    	{
    		$this->$method();
    		return true;
    	}
    	
    	return false;
    }
	
    /**
     * Add response by response type
     * 
     * @param mixed $data
     * @param string $action 'text' or response action string
     * @return e_jshelper
     */
    public function addResponse($data, $action = '')
    {
		if(!$action)
    	{
    		$action = 'text'; 
    	}
		if($action === 'text')
		{
			$this->addTextResponse($data);
		}
		else
		{
			$this->addResponseAction($action, $data);
		}
		return $this;
    }

    /**
     * Reset response action array to prevent duplicates
     *
     * @access private
     * @return void
     */
    public function _reset()
    {
        $this->_response_actions = array();
    }

    /**
     * Send error to the JS Ajax.response object
     *
     * @param integer $errcode
     * @param string $errmessage
     * @param string $errextended
     * @access public 
     */
    public static function sendAjaxError($errcode, $errmessage, $errextended = '')
    {
        header('Content-type: text/html; charset='.CHARSET);
        header("HTTP/1.0 {$errcode} {$errmessage}");
        header("e107ErrorMessage: {$errmessage}");
        header("e107ErrorCode: {$errcode}");

        //Safari expects some kind of output, even empty
        echo ($errextended ?: ' ');
		while (ob_get_level() > 0)
		{
		    ob_end_flush();
		}
        exit;
    }

    /**
     * Clean string to be used as JS string
     * Should be using for passing strings to e107 JS API - e.g Languages,Templates etc.
     *
     * @param string $string
     * @return string
     */
    public function toString($string)
    {
        return "'".str_replace(array("\\'", "'"), array("'", "\\'"), $string)."'";
    }
}
