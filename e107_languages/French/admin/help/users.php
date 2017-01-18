<?php
// Bing-Translated Language file 
// Generated for e107 v2.x by the Multi-Language Plugin
// https://github.com/e107inc/multilan

if (!defined('e107_INIT')) { exit; }

$caption = 'Gestion des utilisateurs';
if (e_QUERY) list($action,$junk) = explode('.',e_QUERY); else $action = 'list';

switch ($action)
{
  case 'create' :
    $text = "Cette page vous permet de créer un utilisateur, qui devient immédiatement un membre normal du site, quel que soit le groupe d'appartenance que vous assignez.<br /><br />
    Si vous cochez la case 'Envoyer le mail de confirmation avec mot de passe au nouvel utilisateur', le nom de connexion et le mot de passe sont envoyés <b>en clair</b>, aussi l'utilisateur doit changer son mot de passe dès réception.";
    break;

  case 'prune' :
	$text = "Supprime en vrac les utilisateurs indésirables de la base de données. Il peut s'agir soit de ceux qui n'ont pas réussi à terminer le processus d'inscription ou ceux dont l'adresse mail est en erreur. Les messages du forum, commentaires, etc. restent et sont marqués comme provenant d'un 'Utilisateur supprimé'.";
	break;

  case 'unverified' :
	$text = "Affiche les membres qui n'ont pas réussi à terminer leur inscription. Les options sont en général comme celles de la liste complète des membres.";
    break;

  case 'options' :
	$text = "Définit diverses options affectant tous les utilisateurs.<br /><br />
	<b>Autoriser le téléchargement d'avatar</b><br />
	Si activé, les utilisateurs peuvent télécharger un avatar de leur choix, lequel sera stocké sur votre serveur. Cela peut avoir des implications de sécurité.<br /><br />
	<b>Autoriser le téléchargement de photo</b><br />
	Si activé, les utilisateurs peuvent télécharger une photo de leur choix, lequel sera stocké sur votre serveur. Cela peut avoir des implications de sécurité.<br /><br />
	<b>Suivi des utilisateurs en ligne</b><br />
    Ceci doit être activé pour conserver une trace de la plupart des activités de l'utilisateur, notamment le comptage des membres en ligne. Ceci augmente significativement l'activité de la base de données.<br /><br />
	<b>Informations sur les membres</b><br />
	Détermine quels groupes de membres peuvent afficher la liste des membres.
    ";
    break;

  default :
	$text = "Cette page affiche une liste de vos membres inscrits. Vous pouvez mettre à jour leurs paramètres, leur donner le statut d'administrateur et définir leur groupe d'appartenance entre autres choses.<br /><br />
	L'ordre de tri peut être modifié en cliquant sur l'en-tête de colonne.<br />
	Les colonnes affichées peuvent être modifiées en cliquant sur 'Modifier Afficher les options', en sélectionnant les colonnes désirées, puis en cliquant sur 'Rechercher/Rafraichir'.<br /><br />
	<b>Informations</b><br />
	Afficher le profil de cet utilisateur (il est également possible de cliquer sur leur nom d'affichage)<br /><br />
    <b>Modifier</b><br />
    Modifier les paramètres pour cet utilisateur.<br /><br />
    <b>Exclure/Ne plus exclure</b><br />
    Déterminer si l'utilisateur peut accéder au site.<br /><br />
	<b>Activer</b><br />
	Ceci active un utilisateur qui aurait dû normalement répondre à l'activation par mail.<br /><br />
	<b>Renvoyer du mail</b><br />
	Renvoyer le mail d'activation à l'utilisateur.<br /><br />
	<b>Mail test</b><br />
	Vérifier la validité de l'adresse email de l'utilisateur (n'envoie pas de mail).<br /><br />
	<b>Définir le groupe</b><br />
	Configurer l'appartenance de groupe pour un utilisateur.<br /><br />
	<b>Supprimer</b><br />
	Supprime totalement l'utilisateur des membres du site (les messages du forum, les commentaires, etc. restent et seront marqués comme provenant d'un 'Utilisateur supprimé'.
	";
}


$ns -> tablerender($caption, $text);
unset($text);
