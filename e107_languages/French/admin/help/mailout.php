<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/help/mailout.php,v $
 * $Revision: 1.8 $
 * $Date: 2008/07/26 21:15:50 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }
if(ET_e107_Version_7 === true)
{
    $text = 'Utiliser cette page pour configurer vos paramètres email pour des fonctions d\'envois massifs de publipostages. Le formulaire d\'envoi de courrier vous permet également de faire un envoi groupé de courrier à tous vos utilisateurs.';
}
else
{
  if (e_QUERY) list($action,$junk) = explode('.',e_QUERY); else $action = 'makemail';

  switch ($action)
  {
	case 'justone' :
	  $text = 'Envoie un mail avec les contraintes spécifiées par un plugin optionnel.';
	  break;
	case 'debug' :
	  $text = 'Pour développeurs uniquement. Un deuxième paramètre de requête correspond au champ gen_type de la table <q>generic</q>. Ignorez les entêtes des colonnes.';
	  break;
	case 'list' :
	  $text = 'Choisir et utiliser un modèle de mail sauvegardé pour un publipostage. La suppression de tous les modèles n\'est plus requis.';
	  break;
	case 'mailouts' :
	  $text = 'Liste des publipostages sauvegardés. Permet de voir s\'ils ont été envoyés et ré-envoie tout email non envoyé.<br />';
	  $text .= 'Affiche également certains détails, en particulier la raison de l\'erreur lors de l\'envoi si c\'est le cas.<br />';
	  $text .= 'Pour ré-envoyer les mails en attente, cliquez sur le bouton <q>Ré-envoyer</q>. Cliquez ensuite sur <q>Exécuter</q> afin d\'ouvrir la fenêtre de progression.<br />';
	  $text .= 'Pour annuler le publipostage il suffit de cliquer sur le bouton <q>Annuler</q>.';
	  break;
	case 'savedmail' :
	case 'makemail' :
	  $text = 'Créez un mail et sélectionnez la liste des destinataires. Vous pouvez enregistrer le mail en tant que modèle pour un usage ultérieur ou l\'envoyer directement.<br />';
	  $text .= 'Toute pièce jointe est sélectionnée dans la liste des téléchargement.';
	  break;
	case 'prefs' :
	  $text = '<b>Configuration des options de publipostage.</b><br />
	  Un mail de test est envoyé en utilisant la méthode et les paramètres en court.<br /><br />';
	  $text .= '<b>Méthode de publipostage</b><br />
	  Pour envoyer des emails utiliser SMTP si possible. Les paramètres dépendent du serveur mail de votre hébergeur.<br /><br />';
	  $text .= '<b>Retours emails</b><br />
	  Retours automatiques de mails non délivrés.<br />
	  Vous pouvez spécifier un compte POP3 afin de récupérer les réponses de mails non délivrés. Il s\'agit normalement d\'un compte standard. N\'utilisez les options TLS que si elles sont explicitement requisent par l\'hébergeur.<br /><br />';
	  $text .= '<b>Sources adresses emails</b><br />
      Si vous avez des plugins gérant les emails, vous pouvez choisir lesquels utiliser pour compléter les listes d\'emails.<br /><br />';
	  $text .= '<b>Journaux</b><br />
      L\'option journal créer un fichier texte dans le dossier log du plugin <q>statisques du site</q>. Il doit être effacé périodiquement. L\'option <q>Journal uniquement</q> permet de visualiser précisément qui pourrait recevoir le mail si réellement envoyé. L\'option <q>avec erreur</q> génère une erreur tous les 7 mails à des fins de tests.
      ';
	  break;
	default :
	  $text = 'Option non documentée.';
  }
}
$ns -> tablerender('Aide publipostage', $text);
