<?php
//---------------------------------------------------------------
//              BEGIN CONFIGURATION AREA
//---------------------------------------------------------------
if (!defined('e107_INIT')){ exit; } 
e107::includeLan(e_PLUGIN."metaweblog/languages/".e_LANGUAGE.".php");

  $helptitle = XMLRPC_HELP_001;
//
  $helpcapt[] = XMLRPC_HELP_010;
  $helptext[] = XMLRPC_HELP_011;
//
  $helpcapt[] = XMLRPC_HELP_020;
  $helptext[] = XMLRPC_HELP_021;
//
  $helpcapt[] = XMLRPC_HELP_030;
  $helptext[] = XMLRPC_HELP_031;
//
  $helpcapt[] = XMLRPC_HELP_040;
  $helptext[] = XMLRPC_HELP_041;
//
  $helpcapt[] = XMLRPC_HELP_050;
  $helptext[] = XMLRPC_HELP_051;
//
  $helpcapt[] = XMLRPC_HELP_060;
  $helptext[] = XMLRPC_HELP_061;

//---------------------------------------------------------------
//              END OF CONFIGURATION AREA
//---------------------------------------------------------------
  $text2 = "";
  for ($i=0; $i<count($helpcapt); $i++) {
    $text2 .="<b>".$helpcapt[$i]."</b><br />";
  $text2 .=$helptext[$i]."<br /><br />";
  };
$ns -> tablerender($helptitle, $text2);
