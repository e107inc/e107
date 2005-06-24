// ** I18N

// Calendar EN language
// Author: Mihai Bazon, <mihai_bazon@yahoo.com>
// Encoding: any
// Distributed under the same terms as the calendar itself.

// For translators: please use UTF-8 if possible.  We strongly believe that
// Unicode is the answer to a real internationalized world.  Also please
// include your contact information in the header, as can be seen above.

// full day names
Calendar._DN = new Array
("Dimanche",
 "Lundi",
 "Mardi",
 "Mercredi",
 "Jeudi",
 "Vendredi",
 "Samedi",
 "Dimanche");

// Please note that the following array of short day names (and the same goes
// for short month names, _SMN) isn't absolutely necessary.  We give it here
// for exemplification on how one can customize the short day names, but if
// they are simply the first N letters of the full name you can simply say:
//
//   Calendar._SDN_len = N; // short day name length
//   Calendar._SMN_len = N; // short month name length
//
// If N = 3 then this is not needed either since we assume a value of 3 if not
// present, to be compatible with translation files that were written before
// this feature.

// short day names
Calendar._SDN = new Array
("Dim",
 "Lun",
 "Mar",
 "Mer",
 "Jeu",
 "Ven",
 "Sam",
 "Dim");

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 0;

// full month names
Calendar._MN = new Array
("Janvier",
 "Février",
 "Mars",
 "Avril",
 "Mai",
 "Juin",
 "Juillet",
 "Août",
 "Septembre",
 "Octobre",
 "Novembre",
 "Décembre");

// short month names
Calendar._SMN = new Array
("Jan",
 "Fév",
 "Mar",
 "Avr",
 "Mai",
 "Jun",
 "Jul",
 "Aou",
 "Sep",
 "Oct",
 "Nov",
 "Déc");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "A propos du calendrier";

Calendar._TT["ABOUT"] =
"Sélecteur DHTML Date/Heure\n" +
"(c) dynarch.com 2002-2005 / Auteur: Mihai Bazon\n" + // don't translate this this ;-)
"Dernière version sur: http://www.dynarch.com/projects/calendar/\n" +
"Distribué sous GNU LGPL.  Voir http://gnu.org/licenses/lgpl.html pour les détails." +
"\n\n" +
"Choix de la Date:\n" +
"- Utilisez les boutons \xab, \xbb pour sélectionner l'année\n" +
"- Utilisez les boutons " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " pour le mois\n" +
"- Laisser cliqué sur ces boutons pour la sélection rapide.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Choix de l'heure:\n" +
"- Cliquez sur les parties de l'heure pour les incrémenter\n" +
"- ou cliquez avec Shift pour les décrémenter\n" +
"- ou cliquez et glisser pour la sélection rapide.";

Calendar._TT["PREV_YEAR"] = "Année Préc. (tenir pour menu)";
Calendar._TT["PREV_MONTH"] = "Mois Préc. (tenir pour menu)";
Calendar._TT["GO_TODAY"] = "Aller à ce Jour";
Calendar._TT["NEXT_MONTH"] = "Mois Suiv. (tenir pour menu)";
Calendar._TT["NEXT_YEAR"] = "Année Suiv. (tenir pour menu)";
Calendar._TT["SEL_DATE"] = "Sélection date";
Calendar._TT["DRAG_TO_MOVE"] = "Glisser pour déplacer";
Calendar._TT["PART_TODAY"] = " (Aujourd'hui)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "Afficher les %s premiers";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Fermer";
Calendar._TT["TODAY"] = "Aujourd'hui";
Calendar._TT["TIME_PART"] = "(Shift-)Click ou glisser pour modifier la valeur";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";

Calendar._TT["WK"] = "sem";
Calendar._TT["TIME"] = "Heure:";
