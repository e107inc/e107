<?php

define("LANINS_001", "e107安装");


define("LANINS_002", "步骤");
define("LANINS_003", "1");
define("LANINS_004", "选择语言");
define("LANINS_005", "请选择安装使用的语言");
define("LANINS_006", "设置语言");
define("LANINS_007", "4");
define("LANINS_008", "PHP &amp; MySQL版本检测/文件权限检测");
define("LANINS_009", "重新检测文件权限");
define("LANINS_010", "文件不可写: ");
define("LANINS_010a", "目录不可写: ");
define("LANINS_011", "错误");
define("LANINS_012", "MySQL功能不存在。可能没有安装MySQL的PHP扩展，或者是安装的PHP不支持MySQL。"); // help for 012
define("LANINS_013", "无法检测MySQL版本号。这没有影响，可以继续安装，需要注意的是e107要求MySQL >= 3.23。");
define("LANINS_014", "文件权限");
define("LANINS_015", "PHP版本");
define("LANINS_016", "MySQL");
define("LANINS_017", "通过");
define("LANINS_018", "确认所有列出的文件都存在且可写。通常可以设置CHMOD 777，但与服务器有关 - 如有疑问请联系主机商。");
define("LANINS_019", "您服务器上安装的PHP版本不能运行e107。e107要求PHP版本高于4.3.0。请升级PHP版本，或者联系主机商。");
define("LANINS_020", "继续安装");
define("LANINS_021", "2");
define("LANINS_022", "MySQL服务器信息");
define("LANINS_023", "请在这里输入MySQL设置。

如果您有管理员权限，可以选择复选框创建一个新数据库，否则要手工新建一个数据库或使用已有数据库。

如果您仅有一个数据库，请使用前缀已保证不与其他数据表冲突。
需要MySQL相关数据，请联系您的主机商。");
define("LANINS_024", "MySQL服务器:");
define("LANINS_025", "MySQL用户名:");
define("LANINS_026", "MySQL密码:");
define("LANINS_027", "MySQL数据库:");
define("LANINS_028", "新建数据库?");
define("LANINS_029", "数据表前缀:");
define("LANINS_030", "e107使用的MySQL服务器。可以包含端口，如\"hostname:port\" 或本地socket路径，如\":/path/to/socket\"。");
define("LANINS_031", "e107连接到MySQL服务器的用户名");
define("LANINS_032", "相应的密码");
define("LANINS_033", "e107使用的MySQL数据库，有时称为schema。如果用户有创建数据库权限，可以选择自动创建数据库。");
define("LANINS_034", "创建e107数据表时使用的前缀。适于在同一数据库中安装多个e107。");
define("LANINS_035", "继续");
define("LANINS_036", "3");
define("LANINS_037", "MySQL连接检测");
define("LANINS_038", "和数据库创建");
define("LANINS_039", "请确认输入所有字段，最重要的是: MySQL服务器, MySQL用户名和MySQL数据库 (MySQL服务器都需要)");
define("LANINS_040", "错误");
define("LANINS_041", "e107无法使用输入的数据连接到MySQL数据库。请返回上一页重新输入。");
define("LANINS_042", "成功连接到MySQL服务器");
define("LANINS_043", "无法创建数据库，请确认您有在服务器上创建数据库的权限。");
define("LANINS_044", "成功创建数据库。");
define("LANINS_045", "请点击按钮继续。");
define("LANINS_046", "5");
define("LANINS_047", "管理员资料");
define("LANINS_048", "返回到上一步");
define("LANINS_049", "两次输入的密码不一样。请返回重试。");
define("LANINS_050", "XML扩展");
define("LANINS_051", "已安装");
define("LANINS_052", "未安装");
define("LANINS_053", "e107 .700 要求安装PHP XML扩展。请联系主机商或查看");
define("LANINS_054", "后再继续");
define("LANINS_055", "安装确认");
define("LANINS_056", "6");
define("LANINS_057", " e107已有所有的安装信息。

请点击按钮创建数据表并保存设置。

");
define("LANINS_058", "7");
define("LANINS_060", "无法读取sql数据文件

请确保<b>core_sql.php</b>文件存在于<b>/e107_admin/sql</b>目录下。");
define("LANINS_061", "e107无法创建所有的数据表。
请清除数据库并检查后再试。");

define("LANINS_062", "[b]欢迎来到您的新网站![/b]
e107已经安装成功，可以开始输入内容。<br />管理页面[link=e107_admin/admin.php]在这里[/link]，点击进入。需要用安装时输入的用户名和密码登录。

[b]支持[/b]
e107首页: [link=http://e107.org]http://e107.org[/link]
论坛: [link=http://e107.org/e107_plugins/forum/forum.php]http://e107.org/e107_plugins/forum/forum.php[/link]
e107中文首页: [link=http://www.e107.cn]http://www.e107.cn[/link]
中文论坛: [link=http://www.e107.cn/e107_plugins/forum/forum.php]http://www.e107.cn/e107_plugins/forum/forum.php[/link]

[b]下载[/b]
插件: [link=http://e107coders.org]http://e107coders.org[/link]
布景: [link=http://e107styles.org]http://e107styles.org[/link] | [link=http://e107themes.org]http://e107themes.org[/link]

谢谢使用e107，希望能满足您的要求。
(可以在管理页面下删除本内容。)");

define("LANINS_063", "欢迎来到e107");

define("LANINS_069", "e107安装成功!

出于安全考虑，请马上设置<b>e107_config.php</b>文件权限未644。

点击下面的按钮后，请从服务器上删除install.php文件和e107_install目录
");
define("LANINS_070", "e107无法保存主配置文件。

请确认<b>e107_config.php</b>文件权限正确");
define("LANINS_071", "正在完成安装");

define("LANINS_072", "管理员用户名");
define("LANINS_073", "用于登录网站的用户名，也可用于显示的名称。");
define("LANINS_074", "管理员显示名称");
define("LANINS_075", "这是用户可见的显示在帐号、论坛以前其他地方的名字。如果希望和用户名相同请留空。");
define("LANINS_076", "管理员密码");
define("LANINS_077", "请输入管理员密码");
define("LANINS_078", "管理员密码确认");
define("LANINS_079", "请确认管理员密码");
define("LANINS_080", "管理有电子邮件");
define("LANINS_081", "输入您的电子邮件地址");

define("LANINS_082", "user@yoursite.com");

// Better table creation error reporting
define("LANINS_083", "MySQL报告错误:");
define("LANINS_084", "安装程序无法建立到数据库的连接");
define("LANINS_085", "安装程序无法选择数据库:");

?>