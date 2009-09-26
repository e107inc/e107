<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/integrity_check/languages/ChineseTrad.php,v $
|     $Revision: 1.3 $
|     $Date: 2009-09-26 15:20:56 $
|     $Author: yarodin $
+----------------------------------------------------------------------------+
*/
	
define("Integ_01", "儲存完成");
define("Integ_02", "儲存失敗");
define("Integ_03", "遺失檔案:");
define("Integ_04", "CRC-錯物:");
define("Integ_05", "無法開啟檔案...");
define("Integ_06", "確認檔案完整性");
define("Integ_07", "沒有有效的檔案");
define("Integ_08", "確認完整性");
define("Integ_09", "新增 sfv-檔案");
define("Integ_10", "選擇的資料夾 <u>不能</u> 儲存於 crc-檔案.");
define("Integ_11", "檔名:");
define("Integ_12", "新增 sfv 檔案");
define("Integ_13", "完整性檢查中");
define("Integ_14", "SFV-Creation 不可能, 因為該資料夾 ".e_PLUGIN."integrity_check/<b>{output}</b> 無法寫入. 請 chmod 該資料夾為 777!");
define("Integ_15", "所有檔案已經完成檢查並確認可以.!");
define("Integ_16", "沒有有效的 core-crc-files");
define("Integ_17", "沒有有效的plugin-crc-files ");
define("Integ_18", "新增外掛-CRC-File");
define("Integ_19", "Core-Checksum-Files");
define("Integ_20", "Plugin-Checksum-Files");
define("Integ_21", "選擇外掛您想新增一個 crc-file .");
define("Integ_22", "使用 gzip");
define("Integ_23", "僅確認安裝風格");
define("Integ_24", "管理首頁");
define("Integ_25", "離開管理控制台");
define("Integ_26", "載入網站於正常標題");
define("Integ_27", "使用檔案檢查來確認核心檔案");

	
// define("Integ_29", "<br /><br /><b>*<u>CRC-ERRORS:</u></b><br />These are checksum errors and there are two possible reasons for this:<br />-You changed something within the mentioned file, so it isn't longer the same as the original.<br />-The mentioned file is corrupt, you should reupload it!");
// language file should contain NO html. 

define("Integ_30", "為了減少 cpu的負擔 , 您可以確認 1 - 10 步驟.");
define("Integ_31", "步驟: ");
define("Integ_32", "有一個檔案名稱為 <b>log_crc.txt</b> 於您的 crc-folder. 請刪除! (或是重新整理)");
define("Integ_33", "有一個檔案名稱為 <b>log_miss.txt</b> 於您的  crc-folder. 請刪除! (或是重新整理)");
define("Integ_34", "您的 Crc-folder 無法寫入!");
define("Integ_35", "因為下列理由您必須允許選擇 <b>一</b>步驟 :");
define("Integ_36", "點選這裡,如果您不想等待超過五秒鐘跳到下一步:");
define("Integ_37", "點選我");
define("Integ_38", "剩下 <u><i>{counts}</i></u> 行待完成...");
define("Integ_39", "請刪除檔案:<br />".e_PLUGIN."integrity_check/<u><i>do_core_file.php</i></u>!<br />已經過期並不在釋出了...");
	
?>