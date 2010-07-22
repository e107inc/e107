<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvsroot/e107/e107_0.7/e107_languages/ChineseTrad/ChineseTrad.php,v $
|     $Revision: 1.11 $
|     $Date: 2007/08/13 19:56:30 $
|     $Author: Hanklu-www.phpbs.com-正體中文製作$
+----------------------------------------------------------------------------+
*/
setlocale(LC_ALL, 'ChineseTrad');
define("CORE_LC", 'tw');
define("CORE_LC2", 'tw');
// define("TEXTDIRECTION","rtl");
define("CHARSET", "utf-8");  // for a true multi-language site. :)
define("CORE_LAN1","錯誤訊息 : 風格遺失.\\n\\n如果想要變更風格,請在您的維護工具/風格管理中設定 (管理控制台) 或是上傳相關風格檔案於伺服器.");

//v.616
define("CORE_LAN2"," \\1 已寫下:");// "\\1" represents the username.
define("CORE_LAN3","附加檔案功能關閉");
//v0.7+
define("CORE_LAN4", "請刪除伺服器上的 install.php ");
define("CORE_LAN5", "如果您沒有刪除將容易造成駭客入侵");

// v0.7.6
define("CORE_LAN6", "Flood防衛機制已經開啟，如果您想要繼續索取此頁面，您將會被系統自動封鎖.");
define("CORE_LAN7", "核心程式嘗試回溯基本設定於自動備份檔案中");
define("CORE_LAN8", "核心參數錯誤");
define("CORE_LAN9", "核心程式無法從自動備份檔案中回溯.執行已中斷.");
define("CORE_LAN10", "偵測到過期的cookie  - 完成登出.");
// Footer
define("CORE_LAN11", "執行時間: ");
define("CORE_LAN12", " 秒, ");
define("CORE_LAN13", " 查詢數. ");
define("CORE_LAN14", "");			// Used in 0.8
define("CORE_LAN15", "DB 查詢數: ");
define("CORE_LAN16", "記憶體使用: ");

// img.bb
define('CORE_LAN17', '[ 圖片功能關閉 ]');
define('CORE_LAN18', '圖片: ');

define("CORE_LAN_B", "b");
define("CORE_LAN_KB", "kb");
define("CORE_LAN_MB", "Mb");
define("CORE_LAN_GB", "Gb");
define("CORE_LAN_TB", "Tb");


define("LAN_WARNING", "警告!");
define("LAN_ERROR", "錯誤");
define("LAN_ANONYMOUS", "訪客");
define("LAN_EMAIL_SUBS", "-電子信箱-");

?>