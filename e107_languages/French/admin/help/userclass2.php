<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/help/userclass2.php,v $
 * $Revision: 1.9 $
 * $Date: 2008/07/26 21:15:50 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

$caption = 'Aide groupe utilisateur';
if(ET_e107_Version_7 === true)
{
    $text = 'Vous pouvez créer/éditer/supprimer des groupes.<br />
    Cela est utile pour restreindre les membres à certaines parties du site. Par exemple, vous pouvez créer un groupe appelé TEST, puis créer un forum où seul les membres du groupe TEST sont autorisés.
    ';
}
else
{
    if (e_QUERY)
    {
        $qs = explode('.', e_QUERY);
    }
    switch (varsettrue($qs[0],'config'))
    {
        case 'initial' :
            $text = 'Détermine les groupes pour lesquels un nouveau membre est automatiquement inscrit.<br />
            Si la vérification est activée, cet affectation peut prendre effet soit à son inscription, soit à son activation.<br /><br />
            Si vous utilisez des groupes hiérarchiques, un membre est automatiquement membre des groupes <q>pères</q> de celui sélectionné dans l\'arbre.
            ';
        break;
        case 'options' :
            $text = 'Vous pouvez choisir d\'ajouter une entrée dans les logs admin lorsqu\'un admin modifie les informations d\'un groupe.<br /><br />
            Les options de paramétrage permettent de gérer la hiérarchie par défaut des groupes, visualisable dans l\'arbre des groupes.<br />
            Ceci n\'a aucun effet sur les autres informations des groupes.
            ';
        break;
        case 'membs' :
            $text = 'Ici vous pouvez effectuer des modifications importantes sur les appartenance de groupes.<br />
            Les modifications sur les appartenance de groupes au niveau membre sont à effectuer dans la page <q>Membres</q>.<br /><br />
            Si vous utilisez des groupes hiérarchiques un membre est automatiquement membre des groupes <q>pères</q> de celui sélectionné dans l\'arbre.
            ';
        break;
        case 'debug' :
            $text = 'Pour utilisateur avancé uniquement.<br />
            Affiche la hiérarchie des groupes ainsi que les groupes automatiquement affectés et les groupes auxquels les 20 premiers membres du site ont accès.<br />
            Le nombre en début de chaque groupe est son ID unique. Le groupe <q>Tous le monde</q> à 0 (zéro) pour ID. e107 utilise ces ID pour se référer aux groupes.<br />
            Suivant le nom du groupe se trouve la visibilité et l\'édition, par exemple [vis:253, edit:27]. Se qui signifie que le groupe est visible dans la plupart des sélecteurs si le membre appartient au groupe 253 et signifie également que ce membre peut modifier son appartenance s\'il appartient au groupe 27.<br />
            Pour finir, après le <q>=</q> est affiché une liste de tous les groupes, pères ou fils dans l\'arbre, ainsi que leurs ID. En conséquence un membre appartenant à un groupe particulier l\'est également de tous ceux de cette liste.<br /><br />
            Les 20 premiers membres et leurs dépendances sont affichés à titre d\'exemple et pour aider à la compréhension.<br />
            La première entrée de chaque ligne donne les groupes auxquels appartient le membre.<br />
            La seconde liste les groupes dont il hérite.<br />
            La troisième liste les groupes pour lesquels il peut modifier son appartenance.
            ';
        break;
        case 'test' :
        case 'special' :
            $text = 'Ne pas utiliser!!! Développeurs uniquement!!!
            ';
        break;
        case 'config' :
        default :
            $text = 'Vous pouvez créer/éditer/supprimer des groupes.<br />
            Cela est utile pour restreindre les membres à certaines parties du site. Par exemple, vous pouvez créer un groupe appelé TEST, puis créer un forum où seul les membres du groupe TEST sont autorisés.<br /><br />
            Le nom du groupe est visible dans le menu déroulant avec parfois une description plus détaillée.<br /><br />
            Si renseignée, l\'icône du groupe est visible dans différentes zones du site.<br /><br />
            Pour permettre aux membres de choisir à quels groupes ils peuvent appartenir, autorisez les à les gérer.<br />
            Seuls les admins pourront gérer les appartenances si vous choisissez <q>personne</q>.<br /><br />
            Le champ <q>visibilité</q> permet de cacher le groupe à la plupart des membres.<br /><br />
            <q>Groupe père</q> permet de définir la hiérarchie des groupes.<br />
            Si la <q>tête</q> de la hiérarchie est <q>Tous le monde (public)</q> ou <q>Membre</q>, les groupes fils et suivant hériterons des même droits.<br />
            Si la <q>tête</q> de la hiérarchie est <q>Personne</q> alors le cumule des droits est inverse, c\'est à dire qu\'un groupe hérite des droits de ses fils.<br />
            L\'arbre résultant est affiché en bas de page. Vous pouvez dérouler ou enrouler les branches en cliquant sur <q>+</q> et <q>-</q>.
            ';
    }
}
$ns -> tablerender($caption, $text);
