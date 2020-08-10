<?php
/* weblog_pinger.php

Weblog_Pinger PHP Class Library by Rogers Cadenhead
Version 1.3
Web: http://www.cadenhead.org/workbench/weblog-pinger

Copyright (C) 2005 Rogers Cadenhead

The Weblog_Pinger class can send a ping message over XML-RPC to
weblog notification services such as Weblogs.Com, Blo.gs,
and Technorati.

This class should be stored in a directory accessible to
the PHP scripts that will use it.

This software requires the XML-RPC for PHP class library by
Usefulinc: http://xmlrpc.usefulinc.com/php.html.

Example use:

require('weblog_pinger.php');
$pinger = new Weblog_Pinger();
echo $pinger->ping_ping_o_matic("Ekzemplo",
    "http://www.ekzemplo.com/");

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. */

if(!e107::isInstalled('gsitemap'))
{ 
	e107::redirect();
	exit();
}


// include the XML-RPC class library
include (e_HANDLER.'xmlrpc/xmlrpc.inc.php');
include (e_HANDLER.'xmlrpc/xmlrpcs.inc.php');
include (e_HANDLER.'xmlrpc/xmlrpc_wrappers.inc.php');

class Weblog_Pinger {
    // Weblogs.Com XML-RPC settings
    var $weblogs_com_server = "rpc.weblogs.com";
    var $weblogs_com_port = 80;
    var $weblogs_com_path = "/RPC2";
    var $weblogs_com_method = "weblogUpdates.ping";
    var $weblogs_com_extended_method = "weblogUpdates.extendedPing";
    // Blo.gs XML-RPC settings
    var $blo_gs_server = "ping.blo.gs";
    var $blo_gs_port = 80;
    var $blo_gs_path = "/";
    var $blo_gs_method = "weblogUpdates.ping";
    // Ping-o-Matic XML-RPC settings
    var $ping_o_matic_server = "rpc.pingomatic.com";
    var $ping_o_matic_port = 80;
    var $ping_o_matic_path = "/RPC2";
    var $ping_o_matic_method = "weblogUpdates.ping";
    // Technorati XML-RPC settings
    var $technorati_server = "rpc.technorati.com";
    var $technorati_port = 80;
    var $technorati_path = "/rpc/ping";
    var $technorati_method = "weblogUpdates.ping";
    // Audio.Weblogs.Com XML-RPC settings
    var $audio_weblogs_com_server = "audiorpc.weblogs.com";
    var $audio_weblogs_com_port = 80;
    var $audio_weblogs_com_path = "/RPC2";
    var $audio_weblogs_com_method = "weblogUpdates.ping";
    // log settings
    var $log_file = "";
    var $log_level = "full"; // full, short, or none;
    var $smessage = "";
    var $software_version = "1.3";
    var $debug = TRUE;

    // report errors
    function report_error($message) {
        error_log("Weblog Pinger: " . $message);
    }

    /* Ping Weblogs.Com to indicate that a weblog has been updated. Returns true
    on success and false on failure. */
    function ping_weblogs_com($weblog_name, $weblog_url, $changes_url = "", $category = "") {
        return $this->ping($this->weblogs_com_server, $this->weblogs_com_port,
            $this->weblogs_com_path, $this->weblogs_com_method, $weblog_name,
            $weblog_url, $changes_url, $category);
    }

    /* Ping Blo.gs to indicate that a weblog has been updated. Returns true on success
    and false on failure. */
    function ping_blo_gs($weblog_name, $weblog_url, $changes_url = "", $category = "") {
        return $this->ping($this->blo_gs_server, $this->blo_gs_port,
            $this->blo_gs_path, $this->blo_gs_method, $weblog_name, $weblog_url,
            $changes_url, $category);
    }

    /* Ping Technorati to indicate that a weblog has been updated. Returns true on
    success and false on failure. */
    function ping_technorati($weblog_name, $weblog_url, $changes_url = "", $category = "") {
        return $this->ping($this->technorati_server, $this->technorati_port,
            $this->technorati_path, $this->technorati_method, $weblog_name, $weblog_url,
            $changes_url, $category);
    }

    /* Ping all of the above services to indicate that a weblog has been updated.
    Returns true on success and false on failure. */
    function ping_all($weblog_name, $weblog_url, $changes_url = "", $category = "") {
        $error[0] = $this->ping_technorati($weblog_name, $weblog_url, $changes_url, $category);
        $error[1] = $this->ping_weblogs_com($weblog_name, $weblog_url, $changes_url, $category);
        $error[2] = $this->ping_blo_gs($weblog_name, $weblog_url, $changes_url, $category);
	    $all_ok = $error[0] & $error[1] & $error[2];
	    return array($all_ok, $error);
    }

    /* Ping Pingomatic to indicate that a weblog has been updated. Returns true on success
    and false on failure. */
    function ping_ping_o_matic($weblog_name, $weblog_url, $changes_url = "", $category = "") {
        return $this->ping($this->ping_o_matic_server, $this->ping_o_matic_port,
            $this->ping_o_matic_path, $this->ping_o_matic_method, $weblog_name,
            $weblog_url, $changes_url, $category);
    }

    /* Ping Audio.Weblogs.Com to indicate that a weblog with a podcast has been updated.
    Returns true on success and false on failure. */
    function ping_audio_weblogs_com($weblog_name, $weblog_url, $changes_url = "",
        $category = "") {

        return $this->ping($this->audio_weblogs_com_server, $this->audio_weblogs_com_port,
            $this->audio_weblogs_com_path, $this->audio_weblogs_com_method, $weblog_name,
            $weblog_url, $changes_url, $category);
    }

    /* Ping Weblogs.Com (extended version) to indicate that a weblog has been updated.
    Returns true on success and false on failure. */
    function ping_weblogs_com_extended($weblog_name, $weblog_url, $changes_url, $rss_url) {
        if ($this->debug) $this->report_error(
            "Sending extended ping to Weblogs.Com for "
            . "$weblog_name, $weblog_url, $changes_url, $rss_url");
        return $this->ping($this->weblogs_com_server, $this->weblogs_com_port,
            $this->weblogs_com_path, $this->weblogs_com_extended_method, $weblog_name,
            $weblog_url, $changes_url, $rss_url, true);
    }

    /* Multi-purpose ping for any XML-RPC server that supports the Weblogs.Com interface. */
    function ping($xml_rpc_server, $xml_rpc_port, $xml_rpc_path, $xml_rpc_method, $weblog_name, $weblog_url, $changes_url, $cat_or_rss, $extended = false)
	{

        // build the parameters
        $name_param = new xmlrpcval($weblog_name, 'string');
        $url_param = new xmlrpcval($weblog_url, 'string');
        $changes_param = new xmlrpcval($changes_url, 'string');
        $cat_or_rss_param = new xmlrpcval($cat_or_rss, 'string');
        $method_name = "weblogUpdates.ping";
        if ($extended) $method_name = "weblogUpdates.extendedPing";

        if ($cat_or_rss != "") {
            $params = array($name_param, $url_param, $changes_param, $cat_or_rss_param);
            $call_text = "$method_name(\"$weblog_name\", \"$weblog_url\", \"$changes_url\", \"$cat_or_rss\")";
        } else {
            if ($changes_url != "") {
              $params = array($name_param, $url_param, $changes_param);
              $call_text = "$method_name(\"$weblog_name\", \"$weblog_url\", \"$changes_url\")";
          } else {
              $params = array($name_param, $url_param);
              $call_text = "$method_name(\"$weblog_name\", \"$weblog_url\")";
            }
        }

        // create the message
        $message = new xmlrpcmsg($xml_rpc_method, $params);
        $client = new xmlrpc_client($xml_rpc_path, $xml_rpc_server,
            $xml_rpc_port);
        $response = $client->send($message);
        // log the message
        $this->log_ping("Request: " . $call_text);
        $this->log_ping($message->serialize(), true);
        if ($response == 0) {
            $error_text = "Error: " . $xml_rpc_server . ": " . $client->errno . " "
                . $client->errstring;
            $this->report_error($error_text);
            $this->log_ping($error_text);
            return false;
        }
        if ($response->faultCode() != 0)  {
            $error_text = "Error: " . $xml_rpc_server . ": " . $response->faultCode()
                . " " . $response->faultString();
            $this->report_error($error_text);
            return false;
        }
        $response_value = $response->value();
        if ($this->debug) $this->report_error($response_value->serialize());
        $this->log_ping($response_value->serialize(), true);
        $fl_error = $response_value->structmem('flerror');
        $message = $response_value->structmem('message');

        // read the response
        if ($fl_error->scalarval() != false) {
            $error_text = "Error: " . $xml_rpc_server . ": " . $message->scalarval();
            $this->report_error($error_text);
            $this->log_ping($error_text);
            return false;
        }

        return true;
    }

    // save ping data to a log file
    function log_ping($message, $xml_data = false) {
        $this->smessage = $xml_data." ".$message;
        return;
/*        if ($this->log_level == "none") {
            return;
        }
        if (($this->log_level == "short") & ($xml_data)) {
            return;
        }
        if (!is_writable($this->log_file)) {
            $this->report_error("File {$this->log_file} is not writable");
            return;
        }
        $fhandle = fopen($this->log_file, "a");
        fwrite($fhandle, $message . "\r\n");
        fclose($fhandle);*/
    }
}

