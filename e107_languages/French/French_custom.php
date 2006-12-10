<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/French_custom.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-12-10 08:43:18 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
/***** Modifiez les termes de cette section pour voir ces termes changer sur tout le site *****
Veillez à mettre les termes en minuscule et à éditer ce fichier avec Notepad++ ou autre éditeur UTF8 sans BOM.
Vous pourrez ensuite utiliser les termes génériques "GLOBAL_LAN_LEMOT_1" pour remplacer le terme dans les fichiers de traduction
"GLOBAL_LAN_LEMOT_1" => actualité
"GLOBAL_LAN_LEMOT_2" => Actualité
"GLOBAL_LAN_LEMOT_3" => actualités
"GLOBAL_LAN_LEMOT_4" => Actualités
Pour les détails linguistiques, utilisez simplement "GLOBAL_LAN_LEMOT"
*/
$custom_lan[news]		= "actualité";
$custom_lan_sub[d_prefix_news]= "'"; //si vous employez un mot comme Manchette, mettez "e ".
$custom_lan_sub[l_prefix_news]= "'"; //si vous employez un mot comme Manchette, mettez "a ".
//**************************************************************
//custom_lan non encore actif 
/*
$custom_lan[chatbox]	= "mégaphone";
$custom_lan[upload]	= "téléversement";
$custom_lan[uploaded]	= "téléversé";
$custom_lan[to_upload]	= "téléverser";
*/

//NE PAS MODIFIER LA SUITE !!!
foreach($custom_lan as $k => $v){
	define("GLOBAL_LAN_".strtoupper($k)."_1", $v);
	define("GLOBAL_LAN_".strtoupper($k)."_2", ucfirst($v));
	if (strrchr($v,'s') !== 's' || strrchr($v,'s') === false){
		define("GLOBAL_LAN_".strtoupper($k)."_3", $v."s");
		define("GLOBAL_LAN_".strtoupper($k)."_4", ucfirst($v)."s");
	}else{
		define("GLOBAL_LAN_".strtoupper($k)."_3", $v);
		define("GLOBAL_LAN_".strtoupper($k)."_4", ucfirst($v));
}	}
foreach($custom_lan_sub as $k => $v){
	define("GLOBAL_LAN_".strtoupper($k), $v);}

?>