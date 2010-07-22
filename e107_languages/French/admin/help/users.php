<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/help/users.php,v $
 * $Revision: 1.9 $
 * $Date: 2009/02/02 22:01:02 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit(); }

$caption = 'Gestion des membres';
if(ET_e107_Version_7 === true)
{
    $text = 'Cette page affiche la liste des membres enregistrés. Vous pouvez modifier leurs paramètres, leur donner le statut d’administrateur, les intégrer à un groupe, etc..';
}
else
{
    if (e_QUERY) list($action,$junk) = explode('.',e_QUERY); else $action = 'list';
    switch ($action)
    {
      case 'create' :
        $text = 'Permet la création de membres automatiquement actifs ainsi que l’appartenance aux groupes.<br /><br />
        Si vous cochez “envoyer un mail de confirmation avec mot de passe”, le nom de connexion et le mot de passe sont envoyés <strong>en clair</strong>. Par conséquent le nouveau membre doit changer son mot de passe lors de sa première connexion.
        ';
        break;

      case 'prune' :
    	$text = 'Purge les membres indésirables de la BdD. Il s’agit de ceux qui n’ont pas confirmé leur inscription ainsi que ceux dont les mails sont retournés comme inconnus. Tous les postes forum, commentaires, etc. sont alloués à “membre supprimé”.';
    	break;

      case 'unverified' :
    	$text = 'Affiche les membres n’ayant pas complétés leur inscription.<br /> Les options sont les mêmes que pour la liste générale des membres.';
        break;

      case 'options' :
    	$text = 'Paramètres de diverses options affectant tous les membres.<br /><br />
    	<b>Autoriser l’upload des avatars</b><br />
    	Si activé les membres peuvent uploader leur avatar sur le serveur. Ceci peut avoir des conséquences sécuritaires.<br /><br />
    	<b>Autoriser l’upload des photos</b><br />
    	Si activé les membres peuvent uploader leur photo sur le serveur. Ceci peut avoir des conséquences sécuritaires.<br /><br />
    	<b>Traçage des utilisateurs</b><br />
        Le traçage des utilisateurs doit être activé pour conserver une trace des activités des utilisateurs, en particulier le compte de membre en ligne. Ceci à un impact non négligeable sur le nombre de requête BdD.<br /><br />
    	<b>Informations membres</b><br />
    	Détermine quel groupe à accès à la liste des membres.
        ';
        break;

      default :
    	$text = 'Cette page affiche la liste des membres enregistrés. Vous pouvez modifier leurs paramètres, leur donner le statut d’administrateur, les intégrer à un groupe, etc..<br /><br />
    	L’ordre de tri peut être modifier en cliquant sur les entêtes de colonnes.<br />
    	Les colonnes affichées peuvent être modifiées en cliquant sur “Éditer les options d’affichage”. Sélectionnez les colonnes désirées puis cliquez “rafraichir”.<br /><br />
    	<b>Informations</b><br />
    	Affiche le profil du membre. Également accessible en cliquant sur le nom d’affichage.<br /><br />
        <b>Éditer</b><br />
        Éditer les paramètre du membre.<br /><br />
        <b>(Dé-)bannir</b><br />
        Autoriser ou interdire l’accès au site à ce membre.<br /><br />
    	<b>Activer</b><br />
    	Activer un membre sans attendre son activation normale.<br /><br />
    	<b>Ré-envoyer le mail d’activation</b><br />
    	Ré-envoyer le mail d’activation au membre.<br /><br />
    	<b>Test email</b><br />
    	Vérifie l’existence de l’adresse email du membre (n’envoi pas de mail).<br /><br />
    	<b>Groupe</b><br />
    	Assigner un membre à des groupes.<br /><br />
    	<b>Supprimer</b><br />
    	Supprime complètement le membre de la BdD. Tous les postes forum, commentaires, etc. sont alloués à “membre supprimé”.
        ';
    }
}

$ns -> tablerender($caption, $text);
unset($text);
