<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/help/banlist.php,v $
 * $Revision: 1.10 $
 * $Date: 2009/02/02 22:01:02 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit(); }

if(ET_e107_Version_7 === true)
{
    // in v0.7 there's only one $text which is moved in 'add' in v0.8
    $action = 'add';
}
else
{
    if (e_QUERY) list($action,$junk) = explode('.',e_QUERY); else $action = 'list';
}

$caption = 'Bannir des utilisateurs';

switch ($action)
{
case 'transfer' :
    $text = 'Exportation et importation des données de la liste noire en fichier CSV (variables séparées par virgules).<br /><br />';
    $text .= '<b>Exportation</b><br />
    Choisissez le type d’export. Les champs sont séparés par le séparateur désiré et optionnellement inclus entre les délimiteurs si précisés.<br /><br />';
    $text .= '<b>Exportation</b><br />
    Vous pouvez choisir si la liste importée doit remplacer l’actuelle ou s’y ajouter. Si les données contiennent les dates et heures d’expiration, vous pouvez choisir de les inclure ou non.<br /><br />';
    $text .= '<b>Format CSV</b><br />
    Le format de chaque ligne du fichier est: IP/email, date, expiration, type, raison, notes.<br />
    Date et expiration sont au format YYYYMMDD_HHMMDD hormis pour les zéros qui sont alors “unknown” ou “indefinite”<br />
    Seules les adresses IP ou emails sont essentielles. Les autres champs sont importés si présents.<br /><br />
    <b>Note:</b> le fichier filetypes.xml doit être modifier en conséquence afin d’autoriser l’import du format CSV.';
  break;
case 'times' :
    $text = 'Paramètres par défaut des divers types de bannissements.<br />
    Si un message est spécifié il est alors affiché à l’utilisateur banni. Dans le cas contraire une page blanche est affichée.<br />
    Le bannissement est en application pour le temps préconisé. Après quoi il est automatiquement supprimé.';
  break;
case 'options' :
    $text = '<b>DNS inverse</b><br />
    Si activé, l’adresse IP de l’utilisateur est comparée au nom de domaine. Pour ce faire, un serveur externe doit être accédé, ce qui peut entrainer un délais d’obtention de l’information, surtout si le serveur est hors-ligne.<br /><br />
    Il est possible de vérifier les noms de domaines pour tout accès au site ou uniquement lors de l’ajout à la liste noire.<br /><br />
    <b>Taux d’accès maximums</b><br />
    Nombre maximum d’accès au site provenant d’une même adresse IP ou d’un même membre dans une période de cinq minutes. Ce nombre est prévu pour détecter les attaques DDOS (déni de service).<br />
    À 90% de la limite choisie, l’utilisateur reçoit un avertissement.<br />
    Lorsque la limite est atteinte il est banni.<br />
    Différent seuils peuvent être définis, pour les invités et les membres connectés.<br /><br />
    <b>Ré-activation de la période de bannissement</b><br />
    Cette option n’est utilisée que si une périodicité est définie dans les options de bannissement. Si activée et si l’utilisateur retente d’accéder au site lors de cette période, le temps préconisé est étendu de la période complète (comme si le bannissement venait juste de prendre effet).
    ';
  break;

case 'edit' :
case 'add' :
    $text = 'Vous pouvez bannir des utilisateurs de votre site à partir de cette page.<br />
    Soit vous entrez l’adresse IP complète, soit vous utilisez un joker pour bannir une plage d’adresses IP. Vous pouvez également entrer une adresse email pour interdire l’inscription comme membre avec cette adresse sur le site.<br /><br />
    <strong>Bannir par adresse IP:</strong><br />
    Entrer l’adresse IP 123.123.123.123 bloque tous les utilisateurs qui tenteront de visiter le site avec cette adresse IP.<br />
    Entrer l’adresse IP 123.123.123.* bloque l’accès au site de quiconque ayant une adresse comprise entre 123.123.123.0 et 123.123.123.255<br /><br />
    <strong>Bannir par adresse email</strong><br />
    Entrer l’adresse email foo@bar.com empêche quiconque d’utiliser cette adresse pour s’inscrire sur le site.<br />
    Entrer l’adresse email *@bar.com empêche quiconque d’utiliser une adresse email du domaine bar.com pour s’inscrire sur le site.<br /><br />
    <strong>Bannir par nom d’utilisateur</strong><br />
    Rendez vous sur la page d’administration des membres';
  break;

case 'whadd' :
case 'whedit' :
    $text = 'Vous pouvez saisir les adresses IP connues comme amicales (généralement celles des admins principaux) afin de leur garantir un accès au site en toutes circonstances.<br />
    Il est conseillé de ne placer qu’un nombre minimal d’adresses pour des raisons de sécurité ainsi que de performance du site.';
  break;
case 'white' :
    $text = 'Liste des adresses IP et email expressément autorisées.<br />
    Cette liste est prioritaire sur la liste noire. Une adresse de cette liste ne devrait donc pas pouvoir être bannie.<br />
    Chaque adresse doit être entrée manuellement.';
  break;
case 'list' :
default :
    $text = 'Liste noire des adresses IP, email et noms de domaine.<br />
    Les utilisateurs bannis se trouvent dans la page administration des membres.<br /><br />
    <b>Bannissements automatiques</b><br />
    e107 banni automatiquement les adresses IP tentant d’inonder (flooder) le site, ainsi que les adresses correspondantes aux erreurs de connexions.<br />
    Ces bannissements apparaissent également dans la liste. Dans les options vous pouvez paramétrer les actions en fonction de chaque type.<br /><br />
    <b>Suppression de bannissements</b><br />
    Il est possible de préciser une période de fin de bannissement pour chaque type. Dans ce cas le bannissement est automatiquement supprimé après cette période, sinon il vous faut le faire manuellement.<br />
    La période est modifiable depuis ici et calculée depuis l’heure de mise à jour.';
}

$ns -> tablerender($caption, $text);
