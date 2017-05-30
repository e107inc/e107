<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * non compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/help/fileinspector.php,v $
 * $Revision: 1.8 $
 * $Date: 2008/07/19 15:44:45 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }
if (ET_e107_Version_7 === true)
{
    $fileinspectorIMode = e_IMAGE;
}
else
{
    global $imode;
    $fileinspectorIMode = e_IMAGE.'packs/'.$imode.'/';
}

$text = '
<img src="'.$fileinspectorIMode.'fileinspector/file_core.png" alt="Fichier du noyau" /> Fichier du noyau<br />
<img src="'.$fileinspectorIMode.'fileinspector/file_warning.png" alt="Insécurité connue" /> Insécurité connue<br />
<img src="'.$fileinspectorIMode.'fileinspector/file_check.png" alt="Fichier intègre du noyau" /> Fichier du noyau (intègre)<br />
<img src="'.$fileinspectorIMode.'fileinspector/file_fail.png" alt="Problème d\'intégrité" /> Fichier du noyau (problème d\'intégrité)<br />
<img src="'.$fileinspectorIMode.'fileinspector/file_uncalc.png" alt="Non calculable" /> Fichier du noyau (non calculable)<br />
<img src="'.$fileinspectorIMode.'fileinspector/file_missing.png" alt="manquant" /> Fichier du noyau (Manquant)<br />
<img src="'.$fileinspectorIMode.'fileinspector/file_old.png" alt="Ancien noyau" /> Fichier de l\'ancien noyau<br />
<img src="'.$fileinspectorIMode.'fileinspector/file_unknown.png" alt="Non reconnu" /> Fichier non reconnu par le noyau<br />
';

$ns -> tablerender('Description des symboles utilisés', $text);

$text = 'L\'inspecteur de fichier scanne et analyse les fichiers sur votre serveur.
Quand l\'inspecteur rencontre un fichier du noyau e107, il examine son intégrité afin de s\'assurer qu\'il n\'est pas corrompu.';

if(ET_e107_Version_7 === false)
{
    $text .= '<br /><br />
    <a href="'.e_SELF.'?create">Cliquer ici afin de générer une image de vos plugins pour une utilisation postérieure dans l\'inspecteur de fichiers.</a>';
}
if ($pref['developer'])
{
    $text .= '<br /><br />
    Le moteur additionnel de recherche de chaines de concordances (mode développeur uniquement) permet de parcourir les fichiers sur votre serveur répondant aux critères d\'expressions régulières.
    Le moteur regex utilisé est <a href="http://php.net/pcre">PCRE</a> de PHP (fonctions preg_*), entrez donc votre requête en tant que #modèle# modificateurs dans les champs fournis.';
}

$ns -> tablerender('Aide inspecteur de fichiers', $text);
