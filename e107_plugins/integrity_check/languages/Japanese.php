<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/integrity_check/languages/Japanese.php,v $
|     $Revision: 1.3 $
|     $Date: 2009-09-26 15:20:56 $
|     $Author: yarodin $
+-----Translation Updated by: difda on 2006/06/30---------------------------+
*/
	
define("Integ_01", "保存に成功しました");
define("Integ_02", "保存に失敗しました");
define("Integ_03", "不足ファイル:");
define("Integ_04", "CRC-エラー:");
define("Integ_05", "ファイルを開くことができません...");
define("Integ_06", "ファイルの整合性チェック");
define("Integ_07", "利用できるファイルはありません");
define("Integ_08", "整合性チェック");
define("Integ_09", "sfv-ファイルの作成");
define("Integ_10", "選ばれたフォルダは、crc-ファイルの範囲内で<u>保存されません</u>.");
define("Integ_11", "ファイル名:");
define("Integ_12", "sfvファイル作成");
define("Integ_13", "整合性チェック");
define("Integ_14", "SFV-作成はできません, なぜならフォルダー ".e_PLUGIN."integrity_check/<b>{output}</b> が書き込み不可だからです. このふぁルだーの chmod を777 にしてください!");
define("Integ_15", "全ファイルともチェックはo.k.でした!");
define("Integ_16", "コアファイル以外-crc-ファイルの利用");
define("Integ_17", "プラグイン以外-crc-ファイルの利用");
define("Integ_18", "プラグイン-CRC-ファイルの作成");
define("Integ_19", "コア-チェックサム-ファイル");
define("Integ_20", "プラグイン-チェックサム-ファイル");
define("Integ_21", "crc-ファイルを作成したいプラグインを選んでください.");
define("Integ_22", "gzipを使用");
define("Integ_23", "インストールされたテーマだけをチェック");
define("Integ_24", "管理フロントページ");
define("Integ_25", "管理エリアから抜ける");
define("Integ_26", "通常ヘッダーにもどります");
define("Integ_27", "USE THE FILE INSPECTOR FOR CHECKING CORE FILES");
	
// define("Integ_29", "<br /><br /><b>*<u>CRC-ERRORS:</u></b><br />These are checksum errors and there are two possible reasons for this:<br />-You changed something within the mentioned file, so it isn't longer the same as the original.<br />-The mentioned file is corrupt, you should reupload it!");
// language file should contain NO html. 

define("Integ_30", "CPU-使用を少なくする為、1 - 10ステップで、チェックをすることができます.");
define("Integ_31", "ステップ: ");
define("Integ_32", "crc-フォルダの<b>log_crc.txt</b>という名前をつけられるファイルがあります。削除してください！（またはリフレッシュしてください）");
define("Integ_33", "crc-フォルダの<b>log_miss.txt</b>という名前をつけられるファイルがあります。削除してください！（またはリフレッシュしてください）");
define("Integ_34", "Crc-フォルダーは書き込み不可です!");
define("Integ_35", "以下の理由のため、あなたは<b>1</b>ステップを選ぶだけでいいです:");
define("Integ_36", "ここをクリック, 次のステップまで5秒待てないとき:");
define("Integ_37", "Click me");
define("Integ_38", "Another <u><i>{counts}</i></u> lines to do...");
define("Integ_39", "次のファイルを削除してください:<br />".e_PLUGIN."integrity_check/<u><i>do_core_file.php</i></u>!<br />それは古い版です、あっても意味をなしません...");
	
?>