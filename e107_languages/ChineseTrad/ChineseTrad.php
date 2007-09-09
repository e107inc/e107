<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/ChineseTrad.php,v $
|     $Revision: 1.2 $
|     $Date: 2007-09-09 07:18:26 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
setlocale(LC_ALL, 'ChineseTrad');
define("CORE_LC", 'tw');
define("CORE_LC2", 'tw');
// define("TEXTDIRECTION","rtl");
define("CHARSET", "utf-8");  // for a true multi-language site. :)
define("CORE_LAN1","錯誤訊息 : 風格已遺失.\\n\\n變更風格於您的一般設定中 (管理控制台) 或上傳相關風格檔案於伺服器中.");

//v.616
define("CORE_LAN2"," \\1 已寫下:");// "\\1" represents the username.
define("CORE_LAN3","附加檔案功能關閉");
//v0.7+
define("CORE_LAN4", "請刪除伺服器上的 install.php ");
define("CORE_LAN5", "如果您沒有刪除將容易造成駭客入侵");

// v0.7.6
define("CORE_LAN6", "Flood防衛機制已經開啟，如果您想要繼續索取此頁面，您將會被系統自動封鎖.");
define("CORE_LAN7", "核心程式嘗試回溯偏好設定於自動備份檔案中");
define("CORE_LAN8", "核心偏好錯誤");
define("CORE_LAN9", "核心程式無法從自動備份檔案中回溯.執行已中斷.");
define("CORE_LAN10", "已偵測到過期的cookie  - 已登出.");


define("LAN_WARNING", "警告!");
define("LAN_ERROR", "錯誤");
define("LAN_ANONYMOUS", "訪客");

?>