<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/help/admin_log.php,v $
 * $Revision: 1.2 $
 * $Date: 2008/07/26 21:15:50 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

$caption = 'Aide logs système';
if (e_QUERY) list($action,$junk) = explode('.',e_QUERY); else $action = 'list';

function common_filters()
{
  $ret = '
  <br /><br />
  <b>Filtres de données</b><br />
  Vous pouvez spécifier divers filtres diminuant le nombre de données affichées. Les données sont enregistrées dans un cookie jusqu\'à la déconnexion.<br />
  Les filtres date de début et fin doivent être activés par leur case à cocher respective.<br />
  Les autres filtres sont actifs lorsqu\'une valeur est saisie.<br />
  ';
  return $ret;
}


switch ($action)
{
case 'auditlog' :
  $text = 'Log d\'activité des utilisateurs.';
  $text .= common_filters();
  break;
case 'config' :
  $text = 'Page de configuration des diverses options du système de logs.<br /><br />
  <b>Paramètres génériques</b><br />
  Nombre de lignes à afficher.<br /><br />
  <b>Logs admin</b><br />
  Ces logs conservent les évènements tant qu\'ils ne sont pas spécifiquement supprimés.<br />
  Utiliser cette option pour supprimer les anciens évènements.<br /><br />
  <b>Logs audit utilisateurs</b><br />
  Ces logs enregistrent les activités des utilisateurs.<br />
  Seuls les membres du groupe spécifié sont suivis. Ils est possible d\'utiliser le groupe <q>tous le monde</q> ou de créer un groupe spécifique.<br />
  Déterminer ensuite les types d\'évènements que vous souhaitez suivre.<br />
  Les évènements d\'inscriptions peuvent être suivis séparément.<br /><br />
  <b>Logs circulaires</b><br />
  Les logs circulaires affichent divers évènements anormaux et offrent une assistance en mode debug.<br />
  Il est possible de les désactiver.<br />
  Les évènements sont automatiquement supprimés après le temps imparti.';
  break;
case 'rolllog' :
  $text = 'Les logs circulaires affichent divers évènements anormaux.<br />
  Ils peuvent également être utilisés en mode debug.';
  $text .= common_filters();
  break;
case 'downlog' :
  $text = 'Téléchargements effectués par les utilisateurs.';
  $text .= common_filters();
  break;
case 'comments' :
  $text = 'Affichage des commentaires utilisateurs sélectionnables par ID, type et date.<br />
  Les commentaires indésirables peuvent être supprimés.';
  break;
case 'detailed' :
  $text = 'Le système principal de logs enregistre l\'heure avec précision si le serveur le supporte.<br />
  Cette page permet une inspection des entrées dans une fenêtre temporelle relativement petite.<br />
  Les entrées logs admin, audit et circulaires sont fusionnées de façon à visualiser les relations entre évènements.';
  break;
case 'adminlog' :
default :
  $text = 'Log d\'activité administration.<br />
  Actuellement les logs sont encore ajoutés dans le code. En conséquence la liste est incomplète.';
  $text .= common_filters();
}
$ns -> tablerender($caption, $text);
