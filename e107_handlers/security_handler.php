<?php
	
#############################################################################
#
# Cafe CounterIntelligence SoapCMS Core Security Class
# Copyright 2004 Mike Parniak
# www.cafecounterintelligence.com
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
#
# Purpose: Base flood, XSS and SQL Injection protection
#
# Requires: Nothing.
#
# Usage: create an instance of the soapSecurity object at the beginning of
#                 any publically accessible scripts.  GET, POST, and COOKIE variables
#                 that are strictly numeric should begin with "n_".
#
#############################################################################
// example usage in class2.php:
// require_once(e_HANDLER."security_handler.php");
// $mysecurity = new soapSecurity();
	
	
	
class soapSecurity {
	 
	var $ip;
	var $csUn = "Soap";
	var $vkeyname;
	var $vhash;
	var $vsession;
	var $vsesscook;
	 
	// Initialization function
	 
	function soapSecurity($dosanitize = 1) {
		 
		ini_set("session.use_only_cookies", "1");
		ini_set("session.use_trans_sid", "0");
		 
		$ip = $_SERVER["REMOTE_ADDR"];
		$vkeyname = md5($_SERVER["REMOTE_ADDR"] . $_SERVER["HTTP_HOST"] . $_SERVER["DOCUMENT_ROOT"] . $csUn);
		$vhash = md5($_SERVER["REMOTE_ADDR"] . $_SERVER["HTTP_USER_AGENT"] . $_SERVER["HTTP_HOST"] . $_SERVER["DOCUMENT_ROOT"] . $_SERVER["SERVER_SOFTWARE"] . $_SERVER["PATH"] . $csUn);
		$vsession = md5($_SERVER["REMOTE_ADDR"] . $_SERVER["HTTP_HOST"] . $csUn);
		$vsesscook = md5($_SERVER["REMOTE_ADDR"] . $_SERVER["DOCUMENT_ROOT"] . $_SERVER["HTTP_HOST"]);
		 
		srand(time());
		session_name($vhash);
		session_id($vsession);
		// Begin data-specific session
		session_start();
		 
		if ((!isset($_SESSION["soapsec-rtg"])) || ($_SESSION["soapsec-rtg"] < 1)) {
			$_SESSION["soapsec-rtg"] = rand(3, 5);
			$_SESSION["soapsec-romps"] = 0;
			$_SESSION["soapsec-ourl"] = $_SERVER["REQUEST_URI"];
			$_SESSION["soapsec-rcode"] = md5($_SERVER["REMOTE_ADDR"] . $_SERVER["HTTP_USER_AGENT"] . $_SERVER["HTTP_HOST"] . $_SERVER["DOCUMENT_ROOT"] . $_SERVER["SERVER_SOFTWARE"] . $_SERVER["PATH"] . $_SESSION["soapsec-romps"] . time());
		}
		 
		if (($_SESSION["soapsec-rtg"] > 0) && ($_SESSION["soapsec-romps"] < $_SESSION["soapsec-rtg"])) {
			if (($_GET[$vkeyname] == $_SESSION["soapsec-rcode"]) && ($_GET[$vkeyname] != "")) {
				$_SESSION["soapsec-romps"]++;
			}
			 else $_SESSION["soapsec-errors"] += 2;
			if ($_SESSION["soapsec-romps"] < $_SESSION["soapsec-rtg"]) {
				$_SESSION["soapsec-rcode"] = md5($_SERVER["REMOTE_ADDR"] . $_SERVER["HTTP_USER_AGENT"] . $_SERVER["HTTP_HOST"] . $_SERVER["DOCUMENT_ROOT"] . $_SERVER["SERVER_SOFTWARE"] . $_SERVER["PATH"] . $_SESSION["soapsec-romps"] . time());
				$numromps = $_SESSION["soapsec-romps"];
				session_write_close();
				$thisurl = $_SERVER["REQUEST_URI"];
				$thisurl = eregi_replace("\?.*", "", $thisurl);
				$thisurl = "http://" . $_SERVER["HTTP_HOST"] . $thisurl . "?";
				$outkey = $vkeyname . "=" . $_SESSION["soapsec-rcode"];
				 
				// First romp is less CPU intensive, in cases of weak automated requesters.
				 
				if ($numromps == 1) {
					header("Location: " . $thisurl . $outkey);
					exit();
				}
				 
				// Subsequent romps are tricky, using hard-to-parse javascript.
				 
				$rnu = rand(8, 15);
				$ran = array();
				$jsout = "<SCRIPT LANGUAGE=\"JavaScript\">\n";
				for ($i = 0; $i < $rnu; $i++) {
					$ran[$i] = rand(-65, 65);
					$jsout .= "var " . chr(97+$i) . " = " . $ran[$i] . "; ";
				}
				 
				$outlen = strlen($outkey);
				 
				$jsout .= "var z = new Array(); ";
				$myvars = array();
				 
				$onvar = 0;
				for ($i = 0; $i < $outlen; $i++) {
					if ($onvar >= $rnu) $onvar = 0;
					$thediff = $i - $ran[$onvar];
					$myvars[$i] = "z[" . chr(97+$onvar);
					if ($thediff > 0) $myvars[$i] .= "+";
					if ($thediff <> 0) $myvars[$i] .= $thediff;
					$myvars[$i] .= "] = \"" . $outkey[$i] . "\"; ";
					$onvar++;
				}
				shuffle($myvars);
				$jsout .= implode('', $myvars);
				$jsout .= "var x = z.join(\"\"); ";
				$jsout .= "location.replace(\"" . $thisurl . "\" + x);</SCRIPT><noscript>You must enable Javascript in order to view this webpage.</noscript>";
				echo $jsout;
			} else {
				$thisurl = "http://" . $_SERVER["HTTP_HOST"] . $_SESSION["soapsec-ourl"];
				echo "<SCRIPT LANGUAGE=\"JavaScript\">location.replace(\"$thisurl\");</SCRIPT><noscript>You must enable Javascript in order to view this webpage.</noscript>";
			}
			exit();
		}
		 
		if ($dosanitize) {
			 
			$getvariables = array_keys($_GET);
			$count = 0;
			while ($count < count($getvariables)) {
				$_GET[$getvariables[$count]] = $this->sanitize($_GET[$getvariables[$count]], (strpos($getvariables[$count], "n_") === 0));
				$count++;
			}
			$getvariables = array_keys($_POST);
			$count = 0;
			while ($count < count($getvariables)) {
				$_POST[$getvariables[$count]] = $this->sanitize($_POST[$getvariables[$count]], (strpos($getvariables[$count], "n_") === 0));
				$count++;
			}
			$getvariables = array_keys($_COOKIE);
			$count = 0;
			while ($count < count($getvariables)) {
				$_COOKIE[$getvariables[$count]] = $this->sanitize($_COOKIE[$getvariables[$count]], (strpos($getvariables[$count], "n_") === 0));
				$count++;
			}
		}
		 
		// If server has automatic global creation, destroy automatically created variables.
		// but... make sure that the variable's value matches the request variable's value before destroying it.
		 
		$getvariables = array_keys($_REQUEST);
		$count = 0;
		while ($count < count($getvariables)) {
			if ((isset($getvariables[$count])) && ($GLOBALS[$getvariables[$count]] == $_REQUEST[$getvariables[$count]])) {
				unset($GLOBALS[$getvariables[$count]]);
			}
			$count++;
		}
		 
		// Remove our session and initiate or restore the user session.
		 
		if (isset($_COOKIE["$vsesscook"])) {
			session_write_close();
			session_name($vsesscook);
			session_id($_COOKIE["$vsesscook"]);
			session_start();
			if (!isset($_SESSION["soap-flag"])) {
				setcookie($vsesscook, "", 0, "/");
				session_unset();
				session_destroy();
				unset($_COOKIE["$vsesscook"]);
				Header("Location: http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
				exit();
			}
		} else {
			if ((time()-120) < $_SESSION["soapsec-lastsess"]) {
				if ($_SESSION["soapsec-fastsess"] > 2) {
					$_SESSION["soapsec-lastsess"] = time();
					exit();
				}
			}
			 else $_SESSION["soapsec-fastsess"] = 0;
			 
			$_SESSION["soapsec-lastsess"] = time();
			$_SESSION["soapsec-fastsess"]++;
			session_write_close;
			session_name($vsesscook);
			session_id(md5(uniqid(time())));
			session_start();
			setcookie($vsesscook, session_id(), 0, "/");
			$_SESSION["soap-flag"] = 1;
		}
		 
		if ($this->floodcheck("fastaccess", 3, 6)) exit();
			 
		return;
	}
	 
	// Removes potentially hazardous material from a string (anti-XSS, anti-Injection)
	// Reliable anti-injection requires cgi variables use the n_ naming convention for any
	// variable that is strictly numeric and possibly used in a query.
	 
	function sanitize($tosanitize, $numonly = FALSE) {
		if ($numonly) {
			$tosanitize = eregi_replace("[^0-9\.\-]", "", $tosanitize);
		} else {
			$tosanitize = htmlspecialchars($tosanitize);
			$tosanitize = eregi_replace("javascript:", "java&#00;script:", $tosanitize);
			if (!get_magic_quotes_gpc()) $tosanitize = addslashes($tosanitize);
			}
		return $tosanitize;
	}
	 
	// Generic flood checking routine
	 
	function floodcheck($identifier, $interval, $threshold = 1) {
		$myresult = 0;
		if (isset($_SESSION["soapsec-" . $identifier])) {
			if ($_SESSION["soapsec-" . $identifier] > (time()-$interval)) {
				if ($threshold < 2) {
					$myresult = 1;
				} else {
					if (!isset($_SESSION["soapsec-" . $identifier . "-counter"])) {
						$_SESSION["soapsec-" . $identifier . "-counter"] = 1;
					} else {
						$_SESSION["soapsec-" . $identifier . "-counter"]++;
						if ($_SESSION["soapsec-" . $identifier . "-counter"] >= $threshold) {
							$myresult = 1;
						}
					}
				}
			}
			 else $_SESSION["soapsec-" . $identifier . "-counter"] = 1;
		}
		$_SESSION["soapsec-" . $identifier] = time();
		return $myresult;
	}
	 
	 
	 
}