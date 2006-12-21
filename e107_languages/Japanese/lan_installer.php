<?php

define("LANINS_001", "e107のインストール");


define("LANINS_002", "ステージ ");
define("LANINS_003", "1");
define("LANINS_004", "言語の選択");
define("LANINS_005", "インストール手順の間、使用する言語を選んでください");
define("LANINS_006", "言語設定");
define("LANINS_007", "4");
define("LANINS_008", "PHP/MySQLバージョンチェック/ファイル属性のチェック");
define("LANINS_009", "ファイル属性の再テスト");
define("LANINS_010", "書込不可ファイル: ");
define("LANINS_010a", "書込不可フォルダー: ");
define("LANINS_011", "エラー");
define("LANINS_012", "MySQLの機能がありません. これは、多分、MySQL PHP Extensionがインストールされないか、PHPインストールの時MySQLサポートがコンパイルされていないのでしょう."); // help for 012
define("LANINS_013", "MySQLのバージョン番号がわかりませんでした. それで、インストールを続けるてください、しかしe107が正しく機能するにはMySQL >= 3.23に必要なのはを知っておいてください.");
define("LANINS_014", "パーミッション(属性)");
define("LANINS_015", "PHPバージョン");
define("LANINS_016", "MySQL");
define("LANINS_017", "可");
define("LANINS_018", "全てのリストされたファイルが存在して、サーバーで書き込み可能であることを確認してください. これは通常FTPのCHMODコマンドで777に設定することで解決します、しかし問題があるときはあなたのホスト管理者と連絡をとってください.");
define("LANINS_019", "インストールされているPHPのバージョンでは,e107を走らせることができません.e107を正しく走らせるにはPHPのバージョンが4.3.0以上であることが要求されます.PHPのバージョンをグレードアップするか,ホスト管理者に連絡をとりアップグレードしてもらってください.");
define("LANINS_020", "インストールの続き");
define("LANINS_021", "2");
define("LANINS_022", "MySQLサーバー詳細");
define("LANINS_023", "ここでMySQLの設定をおこないます.
			  
ルート権限を持っているならば、チェックBOXにチェック入れることによって新しいデータベースを構築することができます。もしそうでなければ、あなたはデータベースを構築しなければならないか、既存のものを使わなければなりません。

あなたには1つのデータベースだけがあるならば、他のスクリプトが同じデータベースを共有することができるように、接頭辞を使用してください。
あなたが利用しているMySQLを知らないならば、詳細はあなたのウェブホストと連絡をとりなさい。");
define("LANINS_024", "MySQLサーバー:");
define("LANINS_025", "MySQLユーザーネーム:");
define("LANINS_026", "MySQLパスワード:");
define("LANINS_027", "MySQLデータベース:");
define("LANINS_028", "データベースの生成?");
define("LANINS_029", "テーブルの接頭辞:");
define("LANINS_030", "あなたがe107に利用したいMySQLサーバー。また、ポート番号を含むことができます 例えば \"hostname:port\" or a path to a local socket e.g. \":/path/to/socket\" for the localhost.");
define("LANINS_031", "e107がMySQLサーバーに接続するために利用するユーザー名");
define("LANINS_032", "ユーザー名で設定されているパスワード");
define("LANINS_033", "The MySQL database you wish e107 to reside in, sometimes refered to as a schema. If the user has database create permissions you can opt to create the database automatically if it doesn't already exsist.");
define("LANINS_034", "e107が使用するe107テーブルを作成するとき付加したい接頭語。 ひとつのデータベースの中で、複数のe107をインストールするのに役に立ちます。");
define("LANINS_035", "次へ");
define("LANINS_036", "3");
define("LANINS_037", "MySQL 接続確認");
define("LANINS_038", " と データベース生成");
define("LANINS_039", "あなたが全ての項目MySQLサーバー、MySQLユーザー名とMySQLデータベース（これらは、MySQLサーバーをアクセスするには常に必要です）が正しいことを確認してください");
define("LANINS_040", "エラー");
define("LANINS_041", "e107は、あなたが入った情報を使っているMySQLサーバーへの接続を確立することができなかったです。最後のページに戻って、情報が正しいことを確認してください。");
define("LANINS_042", "MySQLサーバーへの接続は確立され、確かめられました。");
define("LANINS_043", "データベースを構築して、どうか、あなたがあなたのサーバーでデータベースを構築するために正しい許可を持っていることを確実とすることができない。");
define("LANINS_044", "データベース生成　成功.");
define("LANINS_045", "次のステージに進むために、ボタンをクリックしてください。");
define("LANINS_046", "5");
define("LANINS_047", "管理人詳細");
define("LANINS_048", "最後のステップに戻ってください");
define("LANINS_049", "あなたが入力した2つのパスワードは同じではありません。 戻って、もう一度ためしてください。");
define("LANINS_050", "XML Extension");
define("LANINS_051", "はインストールされています");
define("LANINS_052", "はインストールされていません");
define("LANINS_053", "e107.700は、PHP XML Extensionがインストールされるのを必要とします。 あなたのホストに連絡するか、または情報を読んでください。");
define("LANINS_054", " 続行の前に");
define("LANINS_055", "インストールの確認");
define("LANINS_056", "6");
define("LANINS_057", " e107の設置を完了するために必要とするすべての情報があります。

データベーステーブルを作成し、全ての設定を保存するために、ボタンをクリックしてください。

");
define("LANINS_058", "7");
define("LANINS_060", "SQLデータファイルを読むことができません

ファイル <b>core_sql.php</b> が <b>/e107_admin/sql</b> ディレクトリの中に存在することを確認してください。");
define("LANINS_061", "e107は必要なデータベーステーブルのすべてを作成することができませんでした.
データベースをクリアして、再試行する前に、あらゆる問題を調整してください.");

define("LANINS_062", "[b]新しいウェブサイトへようこそ![/b]
e107のインストールは成功して、現在、内容を受け入れる準備ができています.<br />管理セクションは [link=e107_admin/admin.php]ここにあります[/link], クリックしてください. あなたがインストールの時に入力した名前とパスワードを使っていログインしてください.

[b]サポート[/b]
e107 本家ホームページ: [link=http://e107.org]http://e107.org[/link], ここにFAQとドキュメンテーションがあります.
フォーラム: [link=http://e107.org/e107_plugins/forum/forum.php]http://e107.org/e107_plugins/forum/forum.php[/link]

[b]ダウンロード[/b]
プラグイン: [link=http://e107coders.org]http://e107coders.org[/link]
テーマ: [link=http://e107styles.org]http://e107styles.org[/link] | [link=http://e107themes.org]http://e107themes.org[/link]

[b]日本語化サポート(非公認)[/b]
e107で遊ぼう屋: [link=http://e107jp.stellar8.com/]http://e107jp.stellar8.com/[/link], ここで日本語化を勝手にやっています.


e107を導入していただきありがとうございます, あなたの望まれるウェブサイトの実現を願っています.
(管理セクションからこのメッセージを削除することができます.)");

define("LANINS_063", "Welcome to e107");

define("LANINS_069", "e107のインストールは成功しました!


セキュリティ上の理由で<b>e107_config.php</b>ファイルにファイル属性を644にする必要があります.

また、install.phpを削除してください。そうすれば、あなたが以下のボタンをクリックした後にe107_はあなたのサーバからディレクトリをインストールします。
");
define("LANINS_070", "e107はあなたのサーバにメインコンフィグファイルを保存することができませんでした.

<b>e107_config.php</b>に正しい属性が設定されているか確認してください");
define("LANINS_071", "インストールの成立");

define("LANINS_072", "管理人ユーザー名");
define("LANINS_073", "これはあなたがサイトにログインするのに使用する名前です。 あなたの表示名としてもこれを使用するのがお望みでしたら");
define("LANINS_074", "管理人表示名");
define("LANINS_075", "ここは、プロフィール、フォーラム、および他の領域に表示される名前です。 ログイン名と同じくらい使用するのがお望みでしたら、これを空白の状態でおいてください。");
define("LANINS_076", "管理人パスワード");
define("LANINS_077", "あなたがここで使用したい管理人パスワードを入力してください。
");
define("LANINS_078", "確認用管理人パスワード");
define("LANINS_079", "確認のためにもう一度管理人パスワードを入力してください。");
define("LANINS_080", "管理人メール");
define("LANINS_081", "メールアドレスを入力してください");

define("LANINS_082", "user@yoursite.com");

// Better table creation error reporting
define("LANINS_083", "MySQLからのエラー報告:");
define("LANINS_084", "インストーラーは、データベースとの接続を確立することができませんでした");
define("LANINS_085", "インストーラーは、データベースを選ぶことができませんでした:");

?>