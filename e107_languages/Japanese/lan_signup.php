<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Japanese/lan_signup.php,v $
|     $Revision: 1.2 $
|     $Date: 2006-12-21 15:43:44 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
define("PAGE_NAME", "登録");
define("LAN_7", "表示用の名前: ");
define("LAN_8", "公開用の名前");
define("LAN_9", "ログイン名: ");
define("LAN_10", "以前使用したログイン名");
define("LAN_17", "パスワード: ");
define("LAN_103", "そのユーザー名は有効とみなされません。違うユーザー名を選んでください。");
define("LAN_104", "そのログイン名は既にデータベースに登録済みです。違うログイン名を選んでください。");
define("LAN_105", "２つのパスワードは同じでありません。");
define("LAN_106", "正しいメールアドレスではありません。");
define("LAN_107", "ありがとうございます。貴方はRaelian Movementの正規の会員となりました。");
/* RE-LAN_107.. due to the different word order in Japanese and English, this translation will appear to be strange when it is connected with the word coming after "member of" in the same order as in english. In Jps, this word is supposed to be placed within the above Japanese sentence instead of after that. If you want to make it look like fluent and natural Japanese writing on the site when combined with what is supposed to come after "member of", you should probably modify the program or html codes in html file to make it used within the Jps sentence. This means that you must split the jps sentence into 2 parts, and place RM between them. Comment by Humiaki. 日本語のコメント..英文が"of"で終わっていて、その後に何か別の言葉が来るのですが、そこの部分が送られてきたphpファイルには書いてないので、上記の和訳は「。」で終わる形にしましたが、実際にネットでその後に来る言葉がつなげられると、不自然な日本語になる気がします。たぶん、「・・・となりました。ラエリアン・ムーブメント」といような感じに。ここ以外でも同様の例がいくつも出てきそうですが。どうしますか、アポロ。愛、ふみあき*/

define("LAN_108", "登録手続きが完了しました。");
define("LAN_109", "このサイトは、児童オンラインプライバシー保護法(1998年制定、略.COPPA)を基準にしていますので、13才未満の子供からの登録は親か保護者からの書面での許可がない限りは受け付け出来ません。詳しくは、この法律をご熟読ください。");
define("LAN_110", "登録");
define("LAN_111", "パスワード再入力: ");
define("LAN_112", "Emailアドレス: ");
define("LAN_113", "メールアドレスを非公開にする: ");
define("LAN_114", "これでサイト上で貴方のEmailアドレスは非公開になりました。");
define("LAN_123", "登録");
define("LAN_185", "まだ未入力の入力欄が残っています。");
define("LAN_201", "はい");
define("LAN_200", "いいえ");
define("LAN_202", "貴方はもう既にアカウントを作成済みです。パスワードを忘れた場合は、\'パスワードを忘れた方へ\' のリンクをクリックしてください。");
define("LAN_309", "以下の入力欄に詳細を入力してください。");
define("LAN_399", "続ける");
define("LAN_400", "ユーザー名とパスワードの文字列は <b>大文字・小文字の区別</b>があります。");
define("LAN_401", "貴方のアカウントは使用可能になりました。");
/* RE-LAN_401_What is "please" after the above sentence? I just omitted the word in Jps. */
define("LAN_402", "登録が有効になりました。");
define("LAN_403", "Raelian Movement へようこそ");
/* RE-LAN_403_ In English, you say Welcome to Raelian Movement, but in Japanese, we say Raelian Movement to Welcome. Therefore, "Raelian Movement" must be placed before the above Japanese words instead of after them. If you would like to change the English words of Raelian Movement to japanese ones, then please use "ラエリアン・ムーブメント". アポロへ→この「へようこそ」が英語のRaelian Movementと自動的に英語の語順でつなげられると変な訳になりますよ。→「へようこそRaelian Movement」というように。
*/
define("LAN_404", "登録内容 - ");
/* RE-LAN_404; This is another problem. In english, it says, 'Registration details for'. and, after this for, registrant's name is placed. However, in Jps word order, it goes like "(person's name) for registration details". Thus, to avoid it from looking odd on the site, I deleted the word "for" from the Japanese translation, and instead, added "-" after the Japanese words for Registration details. アポロへ、この訳も同じく、人の名前が前に来るらしいのですが、サイトでは英語の語順で登録者の名前が後に来るかもしれませんね。実際には、どうなるのでしょう。代わりに、ハイフンを後につけて、人の名前が後ろに来たときに妙な感じにならないようにしています。*/
define("LAN_405", "この段階の登録手続きは完了です。このあと、貴方の元へログイン情報を記載した確認メールが送信されます。そのメールに記載されているリンクをクリックし、サインアップ手続きを完了させ、アカウントを有効にしてください。");
define("LAN_406", "ありがとうございました。");
define("LAN_407", "あとで参照できるように大切に保管しておいてください。貴方のパスワードは暗号化されました。もしパスワードを忘れたり、紛失したりした場合は新しいパスワードの発行手続きをしなければいけません。\n\nご登録ありがとうございました。\n\nFrom");
/* In the original English texts, after changing lines, "From" is written, which means that it goes like "From Raelian Movement". However, in Jps word order, we say "Raelian Movement from". 「From」も日本語では･･･からとなりますが、肝心な･･･の部分が後にくるでしょうから、訳すと変になります。これも語順を変更しないといけないと思いますが、それはプログラム上の問題になりますでしょうか。*/
define("LAN_408", "そのEmailアドレスで既に登録済みのユーザーがいます。'パスワードを忘れた方へ' の画面に進んでパスワードを取得してください。");
define("LAN_SIGNUP_1", "分");
define("LAN_SIGNUP_2", "文字");
define("LAN_SIGNUP_3", "コードの確認に失敗しました。");
define("LAN_SIGNUP_4", "貴方のパスワードは最低 ");
define("LAN_SIGNUP_5", " 文字なければいけません。");
define("LAN_SIGNUP_6", "貴方の ");
define("LAN_SIGNUP_7", " が必要となります。");
define("LAN_SIGNUP_8", "ありがとうございました。");
define("LAN_SIGNUP_9", "継続出来ません。");
define("LAN_SIGNUP_10", "はい");
define("LAN_SIGNUP_11", "。");

define("LAN_409", "ユーザー名に無効な文字列が使用されています。");
define("LAN_410", "画像の中の数字を入力して下さい。");
define("LAN_411", "その表示用の名前は既にデータベースに登録済みです。違う名前を使用してください。");
define("LAN_SIGNUP_12", "ユーザー名とパスワードを紛失した場合は回収不可能ですので、紙などに書いて安全に保管してください。");
define("LAN_SIGNUP_13", "ログインボックスか、<a href='".e_BASE."login.php'>こちら</a>のリンクよりログインしてください。");
define("LAN_SIGNUP_14", "こちら");
define("LAN_SIGNUP_15", "当サイトの管理者に問い合わせてください");
define("LAN_SIGNUP_16", "もしヘルプが必要な場合は。");
/* RE_Lan_Signup_15 and 16, in Japanese if-clause usually is used before a sentence, while in english it is used after a sentence. In the above, I wrote the translation of if-clause at No.16. However, if there is no problem for you, I recommend you swop the translations of No.15 and 16 to make them look natural. */
define("LAN_SIGNUP_17", "貴方が13歳以上である事を保証してください。");
define("LAN_SIGNUP_18", "貴方の登録が受理されました。登録内容は以下の通りです･･･");
define("LAN_SIGNUP_19", "ユーザー名:");
define("LAN_SIGNUP_20", "パスワード:");
define("LAN_SIGNUP_21", "貴方のアカウントは現在まだ保留状態です。使用可能にするためには、以下のリンクより先へ進んで下さい･･･");
define("LAN_SIGNUP_22", "こちらをクリックして");
define("LAN_SIGNUP_23", "ログインして下さい。");
define("LAN_SIGNUP_24", "ご登録ありがとうございました。");
define("LAN_SIGNUP_25", "貴方のアバターの画像ファイルをアップロードして下さい。");
define("LAN_SIGNUP_26", "貴方の写真の画像ファイルをアップロードして下さい。");
define("LAN_SIGNUP_27", "表示");
/* In what context is this "Show" used? */
define("LAN_SIGNUP_28", "内容・メールリストの選択");
/* I'm not really sure what "choice of content/mail-lists" is, so the above translation could not be perfect... I wish that I could have a look at the screen. */
define("LAN_SIGNUP_29", "貴方がここで入力するメールアドレスに確認メールが送信されますので、間違いの無いよう入力して下さい。");
define("LAN_SIGNUP_30", "このサイトで自分のメールアドレスを公開したくないのなら、「メールアドレスを非公開にする」ボックスにチェックを入れて下さい。");
define("LAN_SIGNUP_31", "XUPファイルへのURL");
define("LAN_SIGNUP_32", "XUPファイルとは何ですか？");
define("LAN_SIGNUP_33", "パスを直接入力するか、アバターを選択して下さい。");
define("LAN_SIGNUP_34", "注意：このサーバーにアップロードされる画像については、管理者から見て適切でないと判断された場合、即刻削除されます。");
define("LAN_SIGNUP_35", "XUPファイルを使用して登録する場合は、こちらをクリックして下さい。");
define("LAN_SIGNUP_36", "貴方のユーザー情報の作成中にエラーが発生しました。サイト管理者に問い合わせてください。");
define("LAN_LOGINNAME", "ログイン名");
define("LAN_PASSWORD", "パスワード");
define("LAN_USERNAME", "表示名");
define("LAN_EMAIL_01", "親愛な");
/* How is this used? Depending on the situation, its translation could be different. */
define("LAN_EMAIL_04", "このメールは大切に保管しておいて下さい。");
define("LAN_EMAIL_05", "貴方のパスワードは暗号化されましたので、それを紛失したり忘れたりした場合回収不可能となります。その場合新しいパスワードの再発行となりますのでご注意下さい。");
define("LAN_EMAIL_06", "ご登録ありがとうございました。");
define("LAN_SIGNUP_37", "この段階での登録手続きは完了です。貴方の会員資格を当サイトの管理者が承認しなければいけません。その通知メールが貴方のメールアドレスに送信されます。");
define("LAN_SIGNUP_38", "貴方は違うメールアドレスを２つ入力してしまいました。２つのパスワード入力欄に同じ有効なメールアドレスを入力して下さい。");
define("LAN_SIGNUP_39", "メールアドレスの再入力:");

define("LAN_SIGNUP_40", "有効化する必要はありません。");
define("LAN_SIGNUP_41", "貴方のアカウントは既に有効になりました。");
define("LAN_SIGNUP_42", "問題が発生したため、登録メールは送信されませんでした。当ウェブサイトの管理者にお問い合わせ下さい。");
define("LAN_SIGNUP_43", "メール送信終了");
define("LAN_SIGNUP_44", "有効化メールが送信されました。送信宛先:");
define("LAN_SIGNUP_45", "貴方のインボックスを確認してください。");
define("LAN_SIGNUP_47", "有効化メールの再送信をします。");
define("LAN_SIGNUP_48", "ユーザー名かメールアドレス");
define("LAN_SIGNUP_49", "もし登録したメールアドレスが間違っていたら、もう一度こちらに正しいメールアドレスとパスワードを入力してください。:");
define("LAN_SIGNUP_50", "新しいメールアドレス");
define("LAN_SIGNUP_51", "古いパスワード");
define("LAN_SIGNUP_52", "パスワード間違い");
?>