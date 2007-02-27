<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/French_config.php,v $
|     $Revision: 1.2 $
|     $Date: 2007-02-27 02:06:12 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
$eplug_admin = TRUE;
require_once("../../class2.php");
if (!getperms("ML")){
	header("location:".e_BASE."index.php");
	exit;}
require_once(e_ADMIN."auth.php");

//***** Ajoutez les termes qui sont modifiables dans la traduction ici *****
//Veillez à mettre les termes en minuscule.
$termes_modifiables = array('news','chatbox');
$suggest = array(
			'news' => 'actualité, manchette, nouvelle, news...',
			'chatbox' => 'mégaphone, shoutbox, tchatche',
			'upload' => 'téléversement',
			'uploaded' => 'téléversé',
			'to_upload' => 'téléverser',
			'username' => 'identifiant'
			);
//********************************************************************************
/*===========Note aux traducteurs======================
Vous pourrez ensuite utiliser les constantes "GLOBAL_LAN_LEMOT_1" pour remplacer le terme dans les fichiers de traduction
"GLOBAL_LAN_LEMOT_1" => actualité
"GLOBAL_LAN_LEMOT_2" => Actualité
"GLOBAL_LAN_LEMOT_3" => actualités
"GLOBAL_LAN_LEMOT_4" => Actualités
Pour les détails linguistiques, utilisez simplement "GLOBAL_LAN_L_PREFIX_LEMOT" ou "GLOBAL_LAN_D_PREFIX_LEMOT"
Exemple : En remplacement de < L'actualité > ou < La news >, vous mettrez  < L".GLOBAL_LAN_L_PREFIX_NEWS.GLOBAL_LAN_NEWS_1." > 
C'est plus long, mais ça permet beaucoup plus de flexibilité par la suite. ^_^
*/

require(e_LANGUAGEDIR."French/French_custom.php"); //requis pour les 2 prochaines actions
if (isset($_POST['update_lan_submit'])){	//Mise à jour du fichier French_custom.php
	if (update_lan_file($termes_modifiables)){
		$text = '<div style="text-align:center">Les définitions ont bien été mises à jour ^_^</div><br />';
	}else{
		$text = '<div style="text-align:center">Un problème est survenu lors de la mise à jour, veuillez réessayer...</div><br />';}
	unset($custom_lan);
	require(e_LANGUAGEDIR."French/French_custom.php"); //capture les modifs
}

//texte de base
if(!is_writable(e_LANGUAGEDIR."French/French_custom.php")){
	$text .= "<br /><div style='text-align:center;font-size:150%'><strong>Attention, vous ne pouvez pas modifier les termes pour le moment, le 
				fichier ".e_LANGUAGEDIR."French/<span style='color:red;'>French_custom.php</span> n'est pas accessible en écriture.
				Veuillez modifier ses attributs (CHMOD) avec un client FTP et les mettre à 666 ou 777.</strong></div><br /><br />";
}
$text .= "Ces termes seront modifier sur l'ensemble du site.<br />nb. Ceci n'affectera que les fichiers de langage, pas les données entrées manuellement par les usagers.<br /><br />
	<form method='post' action='".e_SELF."'><ul>";
foreach($termes_modifiables as $k){
	if(!$custom_lan[$k]) $custom_lan[$k] = array('m',$k);
		$sel_f = ($custom_lan[$k][0] == 'f') ? "selected='selected'" : "";
		$sel_m = ($custom_lan[$k][0] == 'm') ? "selected='selected'" : "";
		$text .= "<li>
		Terme à employer pour '$k' : <input name='$k' value='".$custom_lan[$k][1]."' />
		Ce terme est au <select name='".$k."_genre' class='tbox'><option value='m' $sel_m>Masculin (neutre)</option><option value='f' $sel_f>Féminin</option></select><br />
		".(isset($suggest[$k]) && $suggest[$k] != '' ? "Suggestions : ".$suggest[$k]."<br />" : "")."<br /></li>";
}
$text .= "</ul>
	<div style='text-align:center'><input class='button' type='submit' name='update_lan_submit' value='Enregistrer les modifications' /></div>
	</form>";

$ns->tablerender('Modification des termes des fichiers de langage Francophone', $text);
require_once(e_ADMIN."footer.php");

//+-------------------FUNCTIONS--------------------------------------------+
function update_lan_file($termes_modifiables){
	$definition = "<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Créé automatiquement par le fichier e107_languages/French/French_config.php
| Outil développé par Daddy Cool at e107educ.org
| Modifié par: ".USERNAME."
| Date: ".strftime('%Y-%m-%d, %H:%Mh')."
+---------------------------------------------------------------+
*/
";
	foreach($termes_modifiables as $terme){
		$definition .= "\$custom_lan['$terme']	= array('".$_POST[$terme."_genre"]."','".$_POST[$terme]."');\n";
	}
	foreach($termes_modifiables as $terme){
		$definition .= "\n";
		$definition .= "define(\"GLOBAL_LAN_".strtoupper($terme)."_1\", \"".$_POST[$terme]."\");\n";
		$definition .= "define(\"GLOBAL_LAN_".strtoupper($terme)."_2\", \"".ucfirst($_POST[$terme])."\");\n";
		if (!ereg ("[sx]$|(er|ir)$", $_POST[$terme])){ 						//vérifie si le mot est universellement au pluriel
			$definition .= "define(\"GLOBAL_LAN_".strtoupper($terme)."_3\", \"".$_POST[$terme]."s\");\n";
			$definition .= "define(\"GLOBAL_LAN_".strtoupper($terme)."_4\", \"".ucfirst($_POST[$terme])."s\");\n";
		}else{
			$definition .= "define(\"GLOBAL_LAN_".strtoupper($terme)."_3\", \"".$_POST[$terme]."\");\n";
			$definition .= "define(\"GLOBAL_LAN_".strtoupper($terme)."_4\", \"".ucfirst($_POST[$terme])."\");\n";
		}
		if (ereg ("^[aeiouyàáâäéèêëïôü]", $_POST[$terme])){ 	//crée les préfixes en fonction de la présence de voyelle en début du mot
			$definition .= "define(\"GLOBAL_LAN_D_PREFIX_".strtoupper($terme)."\", \"&#39;\");\n";
			$definition .= "define(\"GLOBAL_LAN_L_PREFIX_".strtoupper($terme)."\", \"&#39;\");\n";
			$definition .= "define(\"GLOBAL_LAN_DU_PREFIX_".strtoupper($terme)."\", \"de l&#39;\");\n";
		}else{
			$definition .= "define(\"GLOBAL_LAN_D_PREFIX_".strtoupper($terme)."\", \"e \");\n";
			if ($_POST[$terme."_genre"] == 'f'){			//crée les préfixes en fonction du genre du mot
				$definition .= "define(\"GLOBAL_LAN_L_PREFIX_".strtoupper($terme)."\", \"a \");\n";
				$definition .= "define(\"GLOBAL_LAN_DU_PREFIX_".strtoupper($terme)."\", \"de la \");\n";
			}else{
				$definition .= "define(\"GLOBAL_LAN_L_PREFIX_".strtoupper($terme)."\", \"e \");\n";
				$definition .= "define(\"GLOBAL_LAN_DU_PREFIX_".strtoupper($terme)."\", \"du \");\n";
	}	}	}
	$definition .= "?>";
	if(file_put_contents(e_LANGUAGEDIR."French/French_custom.php", $definition))
		{return true;}
	else{return false;}
}
?>