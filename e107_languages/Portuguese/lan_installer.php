<?php
/*
+----------------------------------------------------------------------------+
|     e107 website system - PT Language File.
|
|     $Revision: 1.1 $
|     $Date: 2006-04-15 20:48:49 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
define("LANINS_001", "Instalação do e107");

define("LANINS_002", "Fase ");
define("LANINS_003", "1");
define("LANINS_004", "Selecção do idioma");
define("LANINS_005", "Por favor escolha o idioma que será utilizado durante o processo de instalação");
define("LANINS_006", "Definir idioma");
define("LANINS_007", "4");
define("LANINS_008", "Verificação das Versões de PHP &amp; MySQL / Verificação das permissões dos ficheiros");
define("LANINS_009", "Re-testar permissões dos ficheiros");
define("LANINS_010", "Ficheiro sem permissões de escrita: ");
define("LANINS_010a", "Directoria sem permissões de escrita: ");
define("LANINS_011", "Erro");
define("LANINS_012", "Aparentemente as funções de MySQL não existem. Provavelmente não tem a extensão de PHP instalada no MySQL ou a sua instalação de PHP não foi compilada com suporte MySQL."); // help for 012
define("LANINS_013", "A sua versão do MySQL não foi verificada. Não se trata de um erro fatal e poderá continuar o processo de instalação, no entanto tome nota que o e107 necessita da versão MySQL >= 3.23 para funcionar correctamente.");
define("LANINS_014", "Permissões de ficheiros");
define("LANINS_015", "Versão do PHP");
define("LANINS_016", "MySQL");
define("LANINS_017", "PASSOU");
define("LANINS_018", "Por favor certifique-se que todos os ficheiros listados existem e têm permissões de escrita no servidor. Este processo requer normalmente uma operação de CHMOD 777, mas poderá variar de acordo com as caracteristicas do servidor - contacte o adminstrador do seu alojamento caso tenha alguma dúvida.");
define("LANINS_019", "A versão de PHP instalada no seu servidor não tem capcidade para correr o e107. O e107 necessita pelo menos da versão de PHP 4.3.0 para funcionar correctamente. Deverá actualizar a sua versão de PHP ou contactar o adminstrador do seu alojamento no sentido de efectuar um upgrade.");
define("LANINS_020", "Continuar a instalação");
define("LANINS_021", "2");
define("LANINS_022", "Detalhes do servidor MySQL");
define("LANINS_023", "Por favor insira as suas opções do MySQL.

Se possui permissões de root poderá criar uma nova base de dados seleccionando a caixa respectiva, caso contrário devera criar uma base de dados ou utilizar uma já existente.

Se possuir apenas uma base de dados, utilize um prefixo de forma a que as outras aplicações possam partilhar a mesma base de dados.
Na eventualidade de desconhecer os seus detalhes de MySQL, deverá contactar o administrador do alojamento do seu site.");
define("LANINS_024", "Servidor MySQL:");
define("LANINS_025", "Utilizador MySQL:");
define("LANINS_026", "Password MySQL:");
define("LANINS_027", "Base de dados MySQL:");
define("LANINS_028", "Criar base de dados?");
define("LANINS_029", "Prefixo das Tabelas:");
define("LANINS_030", "O servidor MySQL no qual deseja usar o e107. Poderá também incluir um número de porta. p.exo. \"hostname:port\" ou o caminho para um socket local p.exo. \":/caminho/para/socket\" para o localhost.");
define("LANINS_031", "O nome de utilizador que o e107 deverá utilizar para ligar-se ao servidor MySQL");
define("LANINS_032", "A password para o utilizador MySQL");
define("LANINS_033", "A base de dados MySQL na qual o e107 vai residir. Se o utilizador tem permissões para a criação de base de dados, pode optar por criá-la de forma automática caso esta ainda não exista.");
define("LANINS_034", "O prefixo utilizado pelo e107 na criação das suas tabelas. Útil para múltiplas instalações de e107 em conjunto com outras aplicações na mesma base de dados.");
define("LANINS_035", "Continuar");
define("LANINS_036", "3");
define("LANINS_037", "Verificação da ligação MySQL");
define("LANINS_038", " e criação da base de dados");
define("LANINS_039", "Por favor certifique-se que preenche todos os campos, nomeadamente o Servidor MySQL, Utilizador MySQL e Base de dados MySQL (estes dados são sempre necessários para um Servidor MySQL).");
define("LANINS_040", "Erros");
define("LANINS_041", "O e107 não conseguiu estabelecer a ligação ao servidor de MySQL com a informação fornecida. Por favor regresse à página anterior e certifique-se que todos os detalhes estão correctos.");
define("LANINS_042", "Ligação ao servidor de MySQL estabelecida e verificada.");
define("LANINS_043", "Não foi possivel criar a base de dados, por favor certifique-se que possui as permissões correctas para a criação de bases de dados no seu servidor.");
define("LANINS_044", "Base de dados criada com sucesso.");
define("LANINS_045", "Por favor clique no botão para passar á fase seguinte.");
define("LANINS_046", "5");
define("LANINS_047", "Detalhes do administrador");
define("LANINS_048", "Voltar à fase anterior");
define("LANINS_049", "As duas passwords não coincidem. Por favor volte a tentar.");
define("LANINS_050", "Extensão XML");
define("LANINS_051", "Instalado");
define("LANINS_052", "Não instalado");
define("LANINS_053", "O e107 .700 necessita que a extensão XML do PHP esteja instalada. Por favor contacte o adminstrador do seu alojamento ou leia a informação disponibilizada em ");
define("LANINS_054", " antes de prosseguir");
define("LANINS_055", "Confirmação da instalação");
define("LANINS_056", "6");
define("LANINS_057", " O e107 tem toda a informação necessária para terminar o processo de instalação.

Por favor clique no botão para criar as tabelas na base de dados e gravar todas as suas defininções.

");
define("LANINS_058", "7");
define("LANINS_060", "O ficheiro de dados do SQL não foi lido

Por favor certifique-se que o ficheiro <b>core_sql.php</b> existe na directoria <b>/e107_admin/sql</b>.");
define("LANINS_061", "O e107 não conseguiu criar todas as tabelas necessárias na base de dados.
Por favor limpe a base de dados e corrija quaisquer problemas antes de tentar novamente.");

define("LANINS_062", "[b]Bem-vindo ao seu novo site![/b]
O e107 foi instalado com sucesso e está pronto para aceitar informação de conteúdo.<br />A sua seção de administração está localizada [link=e107_admin/admin.php]aqui[/link], poderá clicar para ser redireccionado agora. Terá que utilizar o nome de utilizador e password fornecidos durante o processo de instalação.

[b]Suporte[/b]
Página do e107: [link=http://e107.org]http://e107.org[/link], onde poderá encontrar todos os FAQs e documentação.

[b]Fóruns:[/b] [link=http://e107.org/e107_plugins/forum/forum.php]http://e107.org/e107_plugins/forum/forum.php[/link]

[b]Downloads[/b]
Plugins: [link=http://e107coders.org]http://e107coders.org[/link]
Temas: [link=http://e107styles.org]http://e107styles.org[/link] | [link=http://e107themes.org]http://e107themes.org[/link]

Obrigado por instalar o e107, desejamos que este cumpra os requesitos do seu site.
(Poderá apagar esta mensagem nas sua secção de administração.)");

define("LANINS_063", "Bem-vindo ao e107");

define("LANINS_069", "O e107 foi instalado com sucesso!

Por razões de segurança é conveniente mudar as permissões do ficheiro <b>e107_config.php</b> para 644.

Deverá também apagar do seu servidor o ficheiro <i>install.php</i> e a directoria <i>e107_install</i>, logo após ter clicado no botão seguinte
");
define("LANINS_070", "O e107 não conseguiu gravar as definições principais no seu servidor.

Por favor verifique se o ficheiro <b>e107_config.php</b> tem as permissões correctas");
define("LANINS_071", "A finalizar o processo de instalação");

define("LANINS_072", "Nome de utilizador do Admin");
define("LANINS_073", "Este nome será utilizado para efectuar o login no site.");
define("LANINS_074", "Nome mostrado para o Admin");
define("LANINS_075", "Este é o nome que será mostrado aos utilizadores na sua página de perfil, fórums e outras áreas. Para usar o mesmo nome do seu login deixe este campo em branco.");
define("LANINS_076", "Password do Admin");
define("LANINS_077", "Por favor escreva a password de admin que deseja utilizar");
define("LANINS_078", "Confirmação da password do Admin");
define("LANINS_079", "Por favor insira novamente a password do admin para confirmação");
define("LANINS_080", "Email do Admin");
define("LANINS_081", "Insira o endereço de email do admin");

define("LANINS_082", "utilizador@oseusite.pt");

// Better table creation error reporting
define("LANINS_083", "Erro reportado pelo MySQL:");
define("LANINS_084", "O programa de instalação não conseguiu estabelecer uma ligação com a base de dados");
define("LANINS_085", "O programa de instalação não conseguiu seleccionar a base de dados:");

?>