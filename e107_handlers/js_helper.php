<?php
/*
 * e107 website system
 * 
 * Copyright (c) 2001-2008 Steve Dunstan (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 * 
 * $Source: /cvs_backup/e107_0.8/e107_handlers/js_helper.php,v $
 * $Revision: 1.1 $
 * $Date: 2008-11-09 20:31:10 $
 * $Author: secretr $
 * 
*/
//PHP <5.2 compatibility
if (!function_exists('json_encode'))
{
    require_once(e_HANDLER.'json_compat_handler.php');
    function json_encode($array) 
    {
        $json = new Services_JSON();
        return $json->encode($array);
    }
    
    function json_decode($json_obj) 
    {
        $json = new Services_JSON();
        return $json->decode($json_obj);
    }
}

class e_jshelper
{
    /**
     * Respons actions array
     *
     * @var array
     */
    var $_response_actions = array();
    
    function addResponseAction($action, $data_array) 
    {
        if(!$action) $action = 'auto';
        if(!isset($this->_response_actions[$action]))
        {
            $this->_response_actions[$action] = array();
        }
        $this->_response_actions[$action] = array_merge($this->_response_actions[$action], $data_array);
        
        return $this;
    }
    
    /**
     * Response array getter
     *
     * @return array response actions
     */
    function getResponseActions() {
        return $this->_response_actions;
    }
    
    /**
     * Buld XML response parsed by the JS API
     * Quick & dirty, this will be extended to 
     * e107 web service standard (communication protocol).
     * 
     * @return string XML response
     */
    function buildXMLResponse()
    {
        $action_array = $this->getResponseActions();
        $ret = "<e107response>\n";
        foreach ($action_array as $action => $field_array) 
        {
	        $ret .= "\t<e107action name='{$action}'>\n";
            foreach ($field_array as $field => $value)
	        {
	            //associative arrays only - no numeric keys!
	            //to speed this up use $sql->db_Fetch(MYSQL_ASSOC); 
	            //when passing large data from the DB 
	            if (is_numeric($field))
	                continue; 
	            $transport_value = $value;
	            if(!is_numeric($value) && !is_bool($value)) { $transport_value = "<![CDATA[{$value}]]>"; }
	            $ret .= "\t<item type='".gettype($value)."' name='{$field}'>{$transport_value}</item>\n";
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
    function sendXMLResponse($action = '', $data_array = array())
    {
        header('Content-type: application/xml; charset='.CHARSET, true);
        if($action)
    	{
    	    $this->addResponseAction($action, $data_array);
    	}
    	
    	echo $this->buildXmlResponse();
    }
    
    /**
     * Build JSON response string
     * 
     * @return string JSON response
     */
    function buildJSONResponse()
    {
        return "/*-secure-\n".json_encode($this->getResponseActions())."\n*/";
    }
    
    /**
     * Convert (optional) and send array as JSON response string
     *
     * @param string $action optional
     * @param array $data_array optional
     */
    function sendJSONResponse($action = '', $data_array = array())
    {
    	header('Content-type: application/json; charset='.CHARSET, true);
        if($action)
    	{
    	    $this->addResponseAction($action, $data_array);
    	}
    	echo $this->buildJSONResponse();
    }
    
    /**
     * Convert (optional) and send array as JSON response string
     *
     * @param string $action optional
     * @param array $data_array optional
     */
    function sendTextResponse($data_text)
    {
    	header('Content-type: text/html; charset='.CHARSET, true);
    	echo $data_text;
    }
    
    /**
     * Send error to the JS Ajax.response object
     *
     * @param integer $errcode
     * @param string $errmessage
     * @param string $errextended
     * @param bool $exit
     */
    function sendAjaxError($errcode, $errmessage, $errextended='', $exit=true)
    {
        header('Content-type: text/html; charset='.CHARSET, true);
        header("HTTP/1.0 {$errcode} {$errmessage}", true);
        header("e107ErrorMessage: {$errmessage}", true);
        header("e107ErrorCode: {$errcode}", true);

        //Safari also needs some kind of output
        echo ($errextended ? $errextended : ' ');
        
        if($exit) exit;
    }
    
    /**
     * Clean string to be used as JS string
     * Should be using for passing strings to e107 JS API - e.g Languages,Templates etc.
     * 
     * @param string $lan_string
     * @return string
     * @access static
     */
    function toString($lan_string) 
    {
        return "'".str_replace(array("\\", "'"), array("", "\\'"), $lan_string)."'";
    }
}
?>