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
if (!defined('e107_INIT')) { exit; }

$caption = "Ajuda = Fórum";
$text = "<b>Geral</b><br />
Utilize esta secção para editar os seus fóruns<br />
<br />
<b>Categorias/Fóruns</b><br />
As categorias são utilizadas para agrupar determinados fóruns com o mesmo tipo de assuntos. Desta forma o layout do site torna-se mais simples e os utilizadores conseguem navegar e encontrar os assuntos do seu interesse de uma forma mais rápida.
<br /><br />
<b>Acessibilidade</b>
<br />
Poderá limitar o acesso ao fórum a alguns utilizadores ou visitantes. Assim que tiver a 'classe' definida, poderá associá-la às permissões de acesso do fórum que desejar. Este acesso poderá ser realizado para secções individuais ou para grupos de fóruns.
<br /><br />
<b>Moderadores</b>
<br />
Seleccione a partir da lista de administradores os nomes que pretende dar acesso de moderação ao fórum. Os administradores devem possuir permissões de moderação do fórum para aprecerem listados nesta secção.
<br /><br />
<b>Escalões</b>
<br />
Defina aqui os escalões dos seus utilizadores. Se os campos de imagem não estiverem preenchidos, serão usadas imagens em vez de nomes. Para usar nomes nos escalões, certifique-se que o campo respectivo da imagem está em branco.<br />O diferencial é o número de pontos que o utilizador necessita de obter para mudar de escalão.";
$ns -> tablerender($caption, $text);
unset($text);
?>