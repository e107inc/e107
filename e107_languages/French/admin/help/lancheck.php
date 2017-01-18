<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/help/lancheck.php,v $
 * $Revision: 1.3 $
 * $Date: 2008/06/16 15:03:43 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

$text = '<strong>Attention:</strong> Cet outil est encore en voie de développement. Certaines fonctionnalités peuvent donc créer des surprises. Notamment:<br /><br />
* LC_ALL n\'est pas reconnu si suivi de plus d\'une chaine de caractères, ce qui est le cas dans French.php<br />
* Certaines phrases sont déclarées comme manquantes bien qu\'elles soient vides dans la version anglaise. S\'assurer que la constante soit en rouge en mode édition<br />
* Les chaines sont réécritent entre guillemets doubles. Veillez à protéger ceux inclus dans le texte si besoin <strong>\"</strong><br />
* Cet outil est à éviter sur les fichiers de langue française du noyau.<br />

pour plus d\'informations rendez vous sur le forum de http://etalkers.tuxfamily.org/<br /><br />

<br /><br />eTalkers team';
$ns -> tablerender('Vérification langues', $text);
