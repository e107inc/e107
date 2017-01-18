<?php
// Bing-Translated Language file 
// Generated for e107 v2.x by the Multi-Language Plugin
// https://github.com/e107inc/multilan

if (!defined('e107_INIT')) { exit; }

$caption = "Exclure des utilisateurs de votre site";
if (e_QUERY) list($action,$junk) = explode('.',e_QUERY); else $action = 'list';

switch ($action)
{
case 'transfer' :
    $text = "Cette page vous permet de transférer les données de la liste noire depuis et vers votre site sous forme de fichiers CSV (variables séparées par des virgules).<br /><br />";
    $text .= "<b>Export de données</b><br />
    Choisissez les types d'exclusion à exporter. Les champs seront délimités par le séparateur choisi et éventuellement inclus entre les guillemets sélectionnés.<br /><br />";
    $text .= "<b>Import de données</b><br />
    Vous pouvez choisir si les exclusions importées doivent remplacer les exclusions importées existantes ou bien s'ajouter à la liste. Si les données importées contiennent une date/heure d'expiration, vous
	pouvez choisir si c'est elle à utiliser ou bien la valeur définie pour ce site.<br /><br />";
    $text .= "<b>Format CSV</b><br />
    Le format de chaque ligne du fichier est: IP/email, date, expiration, type, raison, notes.<br />
    Date et expiration sont au format YYYYMMDD_HHMMDD, sauf une valeur zéro qui indique alors 'inconnu' ou 'indéfini'<br />
    Seuls les adresses IP ou emails sont essentiels; les autres champs sont importés si présents.<br /><br />
    <b>Remarque :</b> vous devrez modifier le fichier filetypes.xml afin d'autoriser les administrateurs à importer le format 'CSV'.";
  break;
case 'times' :
    $text = "Cette page définit le comportement par défaut pour les différents types d'exclusions.<br />
    Si un message est spécifié, il sera alors affiché à l'utilisateur (à un endroit approprié). Si le message commence avec 'http://' ou 'https://' le contrôle
	est passé à l'URL spécifié. Dans le cas contraire l'utilisateur obtiendra probablement un écran blanc.<br />
    L'exclusion sera en vigueur pendant la durée spécifiée; après quoi il sera effacé la prochaine fois qu'ils accèdent au site.";
  break;
case 'options' :
    $text = "<b>DNS inverse</b><br />
    Si activé, l'adresse IP de l'utilisateur est recherchée pour obtenir le nom de domaine associé. Ce processus accède à un serveur externe, ce qui peut
    entrainer un délai avant que l'information ne soit disponible - et si le serveur est hors ligne, il peut y avoir un très long délai.<br /><br />
    Vous pouvez choisir de rechercher les noms de serveurs pour tous les accès au site ou seulement lors de l'ajout d'une nouvelle exclusion.<br /><br />
    <b>Taux d'accès maximal</b><br />
    Cela définit le nombre maximal d'accès au site autorisé par un même utilisateur ou adresse IP dans une période de cinq minutes, et a pour but
	de détecter les attaques par déni de service. À 90% de la limite choisie, l'utilisateur reçoit un avertissement; lorsque la limite est atteinte, il est exclu.
    Différent seuils peuvent être fixés pour les invités et les membres connectés.<br /><br />
    <b>Réactivation de la période d'exclusion</b><br />
    Cette option n'est pertinente que si l'exclusion sur une période, et non indéfiniment, est défini dans les options. Si activée et si
	l'utilisateur tente d'accéder au site, l'exclusion est prolongée (comme si l'exclusion venait juste de commencer).
    ";
  break;
case 'edit' :
case 'add' :
    $text = "Vous pouvez exclure des utilisateurs de votre site à partir de cette page.<br />
    Entrez leur adresse IP complète ou utilisez un caractère générique pour exclure une plage d'adresses IP. Vous pouvez également entrer une adresse email pour empêcher un utilisateur de s'enregistrer en tant que membre sur votre site.<br /><br />
    <strong>Exclure par adresse IP :</strong><br />
    Entrer l'adresse IP 123.123.123.123 bloquera la visite de votre site par l'utilisateur disposant de cette adresse.<br />
    Entrer une adresse IP avec un ou plusieurs caractères génériques dans les blocs de fin, comme 123.123.123.* ou 214.098.*.*, bloquera la visite de votre site par n'importe quel utilisateur dans cette
	tranche IP. (Notez qu'il doit y avoir exactement quatre groupes de chiffres ou d'astérisques) <br /> <br />
	Les adresses au format IPV6 sont également prises en charge, y compris '::' pour représenter un bloc de valeurs nulles. Chaque paire de chiffres dans les champs de fin peut être un caractère générique séparé<br /><br />
    <strong>Exclure par adresse email</strong><br />
    Entrer l'adresse email foo@bar.com empêchera toute personne utilisant cet email à s'inscrire en tant que membre sur votre site.<br />
    Entrer l'adresse email *@bar.com empêchera toute personne utilisant ce domaine de messagerie à s'inscrire en tant que membre sur votre site.<br /><br />
    <b>Exclure par nom d'utilisateur</b><br />
	Cela se fait à partir de la page d'administration des utilisateurs.<br /><br />";
  break;
case 'whadd' :
case 'whedit' :
    $text = "Vous pouvez spécifier ici des adresses IP que vous savez être 'amicales' - généralement celles des administrateurs principaux, afin de leur garantir un accès au site en toutes circonstances.<br />
    Il est conseillé de limiter le nombre d'adresses de cette liste à un mimimum absolu, tant pour des raisons de sécurité que pour minimiser l'impact sur les performances du site.";
  break;
case 'banlog' :
  $text = "Cette page affiche une liste de tous les accès au site comportant une adresse présente dans la liste des exclusions ou la liste blanche. La colonne 'raison' affiche l'explication.";
  break;
case 'white' :
    $text = "Cette page affiche une liste de toutes les adresses IP et email explicitement autorisées.<br />
    Cette liste est prioritaire sur la liste des exclusions - Il ne devrait pas être possible d'exclure une adresse de cette liste.<br />
    Toutes les adresses doivent être entrées manuellement.";
  break;
case 'list' :
default :
    $text = "Cette page affiche une liste de toutes les adresses IP, noms de domaine et adresses email qui sont interdites.<br />
    (Les utilisateurs exclus sont affichés sur la page d'administration des utilisateurs.<br /><br />
    <b>Exclusions automatiques</b><br />
    e107 exclut automatiquement les adresses IP individuelles si elles tentent d'inonder (flooder) le site, ainsi que les adresses avec des échecs de connexions.<br />
    Ces exclusions figurent également dans cette liste. Vous pouvez sélectionner (sur la page des options) les actions à prendre pour chaque type d'exclusion.<br /><br />
    <b>Enlever une exclusion</b><br />
    Vous pouvez définir un délai d'expiration pour chaque type d'exclusion, auquel cas l'entrée est supprimée une fois expirée la période d'exclusion. Dans le cas contraire,
	l'exclusion subsiste jusqu'à ce que vous le retiriez.<br />
    Vous pouvez modifier la période d'exclusion à partir de cette page - les temps sont calculés à partir de maintenant.";
}

$ns -> tablerender($caption, $text);
