<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Linkwords plugin - language file (only needed for admin)
 *
 */

//define("LWLAN_1", "Field(s) left blank.");
//define("LWLAN_2", "Link word saved.");
//define("LWLAN_3", "Link word updated.");

return [
    'LWLAN_4' => "No linkwords defined yet.",
    'LWLAN_5' => "Words",
    'LWLAN_6' => "Link",
    'LWLAN_7' => "Active?",
    'LWLAN_21' => "Word to autolink (or comma-separated list of words)",
    'LWLAN_26' => "Areas in which to enable linkwords",
    'LWLAN_28' => "Pages on which to disable linkwords",
    'LWLAN_33' => "Title areas",
    'LWLAN_34' => "Item summaries",
    'LWLAN_35' => "Body text",
    'LWLAN_36' => "Descriptions (links etc)",
    'LWLAN_40' => "User-entered titles (e.g. forum)",
    'LWLAN_41' => "User-entered body text (e.g. forum)",
    'LWLAN_50' => "Tooltip",
    'LWLAN_52' => "Linkword only",
    'LWLAN_53' => "Tooltip only",
    'LWLAN_54' => "Linkword and Tooltip",
    'LWLAN_55' => "Open link in new window",
    'LWLAN_58' => "Pref hook converted to 0.8 format",
    'LWLAN_59' => "Enable Ajax functionality",
    'LWLAN_64' => "Suppress link on current page",
    'LWLAN_66' => "Custom CSS class",
    'LWLAN_67' => "Max. links/tips",
  //  'LAN_LW_HELP_01' => "Many areas of text have an associated 'context', and linkwords will only be displayed in areas matching that context.",
  //  'LAN_LW_HELP_02' => "The tooltips can use Ajax to get information for display. This usually requires some custom coding.",
    'LAN_LW_HELP_03' => "Usually its pointless for the user to be able to click a link if they're already on the page that it links to. When this option is turned ON, the linkwords are not triggered in this case.",
    'LAN_LW_HELP_04' => "Linkwords may be disabled on specific pages, or pages matching a pattern. Enter these here (same syntax as for menu visbility), one pattern per line. If the pattern ends in '!', this corresponds to 'end of query', and is usually an exact match. Otherwise any URL containing the specified string will match.",
    'LAN_LW_HELP_05' => "Will add this class to all generated links.",
    'LAN_LW_HELP_10' => "Here you can define the words which become clickable links, or which display text on mouseover.",
    'LAN_LW_HELP_11' => "This is case-insensitive. For multiple words mapping to the same links and tooltips, separate them with commas (no spaces)",
    'LAN_LW_HELP_12' => "Define a clickable link here. If its an external link, it MUST begin with 'http(s)://'. If its a link within this site, the normal {e_XXX} constants may be used.",
    'LAN_LW_HELP_13' => "Defines which options are active.",
    'LAN_LW_HELP_14' => "This defines the text to be displayed when the user's mouse passes over the word.",
    'LAN_LW_HELP_15' => "Maximum amount of the same linkwords. Must be positive number. Used when the same word is found multiple times in a piece of text.",
    'LAN_LW_HELP_16' => "This defines an optional numeric ID to be used with Ajax processing. Must be a postiive number. If blank, the database record number is used",
    'LAN_LW_HELP_17' => "When turned on, the link is opened in new browser tab/window",
    'LAN_LW_HELP_01' => "<b>Areas to enable</b><br />

  many areas of text have an associated \"context\", and linkwords will only be displayed in areas matching that context.<br /><br />

  <b>Linkwords Disable</b><br />

  Linkwords may be disabled on specific pages, or pages matching a pattern. Enter these here (same syntax as for menus), one pattern per line. 

  If the pattern ends in \"!\", this corresponds to \"end of query\", and is usually an exact match. Otherwise any URL containing the specified string will match.<br />

  Note that linkwords are <i>never</i> displayed on admin pages.<br /><br />

  <b>Enable Ajax Functionality</b><br />

  The tooltips can use Ajax to get information for display. This usually requires some custom coding.<br /><br />

  <b>Suppress link on current page</b><br />

  Usually its pointless for the user to be able to click a link if they\"re already on the page. Tick this box to remove the option,<br />",
    'LAN_LW_HELP_02' => "Define the words which become clickable links, or which display text on mouseover, here<br /><br />
  <b><u>Word to Link</u></b><br />

  This is case-insensitive. For multiple words mapping to the same links and tooltips, separate them with commas (no spaces)<br /><br />

  <b><u>Link</u></b><br />

  Define a clickable link here. If its an external link, it [i]must[/i] begin \"http://\". If its a link within this site, the normal {e_XXX} constants may be used.<br /><br />

  <b><u>Tooltip</u></b><br />

  This defines the text to be displayed when the user\"s mouse passes over the word.<br /><br />

  <b><u>LW ID (Tooltip ID)</u></b><br /><br />

  This defines an optional numeric ID to be used with Ajax processing. If blank, the database record number is used.<br /><br />

  <b><u>Activate?</u></b><br />

  Defines which options are active.",
];
