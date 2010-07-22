<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvsroot/e107/e107_0.7/e107_languages/ChineseTrad/lan_installer.php,v $
|     $Revision: 1.2 $
|     $Date: 2006/11/08 12:57:27 $
|     $Author: Hanklu-www.phpbs.com-正體中文製作$
+----------------------------------------------------------------------------+
*/
define("LANINS_001", "e107 安裝指引");


define("LANINS_002", "步驟");
define("LANINS_003", "1");
define("LANINS_004", "選擇語系");
define("LANINS_005", "請選擇安裝介面的語系");
define("LANINS_006", "設定語言");
define("LANINS_007", "4");
define("LANINS_008", "PHP &amp; MySQL 版本確認 / 檔案權限確認");
define("LANINS_009", "重新設定檔案權限");
define("LANINS_010", "無寫入權限的檔案: ");
define("LANINS_010a", "無寫入權限的資料夾: ");
define("LANINS_011", "發生錯誤");
define("LANINS_012", "MySQL 相關函數不存在. 通常是表示 MySQL PHP Extension尚未安裝或是您的 PHP installation 沒有支援 MySQL ."); // help for 012
define("LANINS_013", "無法找出您的 MySQL 版本. 如繼續安裝,這可能會發生錯誤，系統 e107 需要 MySQL >= 3.23 .");
define("LANINS_014", "檔案權限");
define("LANINS_015", "PHP 版本");
define("LANINS_016", "MySQL");
define("LANINS_017", "檢查通過");
define("LANINS_018", "請確定名單上的檔案是否檔案存在,並且相關權限是可以寫入的. 通常需要 CHMOD 為 777, 如果有問題請聯絡您的主機商.");
define("LANINS_019", "您伺服器上的 PHP 版本無法執行 e107. e107 至少需要 PHP 版本於 4.3.0 以上. 請更新您的 PHP 版本, 或請您的主機商更新.");
define("LANINS_020", "繼續安裝");
define("LANINS_021", "2");
define("LANINS_022", "MySQL 伺服器細節");
define("LANINS_023", "請輸入 MySQL 設定.

如果您有root的權限去新增資料庫請勾選此欄位, 假如您必須新增一個資料庫或是使用現有的資料庫.

假如您只有一個資料庫請使用前置文字，這樣就可以安裝其他程式於資料庫中.
假如您不知道 MySQL 資訊請聯絡您的主機商.");
define("LANINS_024", "MySQL 伺服器:");
define("LANINS_025", "MySQL 帳號:");
define("LANINS_026", "MySQL 密碼:");
define("LANINS_027", "MySQL 資料庫:");
define("LANINS_028", "新增資料庫?");
define("LANINS_029", "資料表前置文字:");
define("LANINS_030", "您希望e107連結那一個 MySQL 伺服器. 此設定也可以包含Port. 例如. \"hostname:port\" 或是主機Socket 例如 \":/path/to/socket\" 給 localhost.");
define("LANINS_031", "該帳號是您希望 e107 去連結您的 MySQL 伺服器");
define("LANINS_032", "密碼則是給該帳號使用的");
define("LANINS_033", "您希望讓e107歸屬於那一個資料庫. 如果會員有新增資料庫的權限，您可以自動新增一個未存在資料庫.");
define("LANINS_034", "前置文字是您希望使用 e107 網站並新增已前置文字開頭的 e107 資料表. 這樣可以安裝多個 e107 於同一個資料庫中.");
define("LANINS_035", "繼續");
define("LANINS_036", "3");
define("LANINS_037", "MySQL 連結確認");
define("LANINS_038", " 和資料庫新增");
define("LANINS_039", "請確定您已經填完所有欄位,更重要的是, MySQL 伺服器, MySQL 帳號和 MySQL 資料庫 ( MySQL 伺服器為必備的)");
define("LANINS_040", "錯誤");
define("LANINS_041", "e107 無法建立與 MySQL 伺服器的連線. 請返回上一頁並確認輸入的資料是否正確.");
define("LANINS_042", "連結 MySQL 伺服器已建立並有效.");
define("LANINS_043", "無法新增資料庫,請確定您有資料庫的相關權限.");
define("LANINS_044", "資料庫新增成功.");
define("LANINS_045", "請點選按鈕進行下一頁.");
define("LANINS_046", "5");
define("LANINS_047", "管理員細節");
define("LANINS_048", "回到上一步");
define("LANINS_049", "兩次密碼不一樣. 請重新輸入嘗試.");
define("LANINS_050", "XML Extension");
define("LANINS_051", "已經安裝");
define("LANINS_052", "尚未安裝");
define("LANINS_053", "e107 .700 需要 PHP XML Extension 被安裝. 請聯絡您的主機商或是閱讀資訊 ");
define("LANINS_054", " 之前繼續");
define("LANINS_055", "安裝確認");
define("LANINS_056", "6");
define("LANINS_057", " e107 現在所有資訊已經安裝完成.

請點選按鈕新增資料庫資料表和儲存您所有設定.

");
define("LANINS_058", "7");
define("LANINS_060", "無法讀取 sql 資料檔案

請確認檔案 <b>core_sql.php</b> 存在於 <b>/e107_admin/sql</b> 目錄.");
define("LANINS_061", "e107 無法新增所有必須的資料表.
請清除資料庫和最近發生的任何問題後再嘗試一遍.");

define("LANINS_062", "[b]歡迎來到您的新網站![/b]
e107 已安裝完成並準備接受任何內容.<br />您的管理控制台 [link=e107_admin/admin.php]這裡[/link], 點選前往. 您將會登入使用安裝時所使用的帳號跟密碼.

[b]相關支援[/b]
e107 首頁: [link=http://e107.org]http://e107.org[/link],e107中文支援：[link=http://e107.tw]http://e107.tw[/link][link=http://phpbs.com]PHP黑店[/link],您將可以看到常見問題的文件.
討論區: [link=http://e107.org/e107_plugins/forum/forum.php]http://e107.org/e107_plugins/forum/forum.php[/link]

[b]下載[/b]
外掛: [link=http://e107coders.org]http://e107coders.org[/link]
風格: [link=http://e107styles.org]http://e107styles.org[/link] | [link=http://e107themes.org]http://e107themes.org[/link]

感謝您使用 e107,我們希望您可以感到滿意.
(您可以刪除該訊息於您的管理控制台中.)");

define("LANINS_063", "歡迎使用 e107");

define("LANINS_069", "e107 已完成安裝!

請您重新設定檔案權限 <b>e107_config.php</b> 回到 644.

點選下面連結後，請刪除 install.php
");
define("LANINS_070", "e107 無法儲存主要的設定檔案.

請確認 <b>e107_config.php</b> 沒有正確的權限");
define("LANINS_071", "最後安裝步驟");

define("LANINS_072", "管理員名稱");
define("LANINS_073", "這個名稱是您使用的登入名稱. 您可以跟您的顯示名稱相同");
define("LANINS_074", "管理員顯示名稱");
define("LANINS_075", "這個名稱將會顯示於您的個人資料, 討論區以及其他區域. 假如您想跟您的登入名稱相同請保留空白.");
define("LANINS_076", "站長密碼");
define("LANINS_077", "請輸入您想使用的站長密碼");
define("LANINS_078", "站長密碼確認");
define("LANINS_079", "請再次輸入站長密碼");
define("LANINS_080", "站長的信箱");
define("LANINS_081", "輸入您的電子信箱");

define("LANINS_082", "user@yoursite.com");

// Better table creation error reporting
define("LANINS_083", "MySQL 錯誤報告:");
define("LANINS_084", "無法建立連線");
define("LANINS_085", "無法選擇資料庫:");
define("LANINS_086", "管理員帳號, 密碼和郵件地址為 <b>必填的欄位</b>. 請返回上一頁確認這些資料填寫是否正確.");
define("LANINS_087", "綜合的");
define("LANINS_088", "首頁");
define("LANINS_089", "好康下載");
define("LANINS_090", "會員");
define("LANINS_091", "新聞提供");
define("LANINS_092", "聯絡我們");
define("LANINS_093", "給予進入私人選單項目權限");
define("LANINS_094", "私人討論區權限範例");
define("LANINS_095", "檢查完整性");
define("LANINS_096", '最新評論');
define("LANINS_097", '[閱讀更多 ...]');
define("LANINS_098", '文章');
define("LANINS_099", '文章首頁 ...');
define("LANINS_100", '討論區最新回覆');
define("LANINS_101", '更新選單設定');
define("LANINS_102", '日期 / 時間');
define("LANINS_103", '回顧');
define("LANINS_104", '回到首頁 ...');

define("LANINS_105", '資料庫名稱或是前置文字發生錯誤例如： \'e\' 或是 \'E\' 大小寫的錯誤');
define("LANINS_106", '警告 - E107無法寫入下面資料夾跟檔案. E107安裝將會繼續, 但是將會有部份功能無法使用. 
				您必須變更相關的檔案權限');
// for v0.7.16+ only
define('LANINS_DB_UTF8_CAPTION', 'MySQL 編碼字元:');
define('LANINS_DB_UTF8_LABEL',   '強制UTF-8連線?');
define('LANINS_DB_UTF8_TOOLTIP', '如果選擇, 將會把資料庫中的編碼設定為 UTF-8. UTF-8 資料庫將會是下一e107版本必備選項.');


?>
