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
 * $Id*
 */

//define("LWLAN_1", "Field(s) left blank.");
//define("LWLAN_2", "Link word saved.");
//define("LWLAN_3", "Link word updated.");
define("LWLAN_4", "No link words defined yet.");
define("LWLAN_5", "Words");
define("LWLAN_6", "Link");
define("LWLAN_7", "Active?");
//define("LWLAN_8", "Options");
//define("LWLAN_9", "yes");
//define("LWLAN_10", "no");
define("LWLAN_11", "Existing Linkwords");
//define("LWLAN_12", "Yes");
//define("LWLAN_13", "No");
//define("LWLAN_14", "Submit LinkWord");
//define("LWLAN_15", "Update LinkWord");
//define("LWLAN_16", "Edit");
//define("LWLAN_17", "Delete");
define("LWLAN_18", "Are you sure you want to delete this linkword?");
//define("LWLAN_19", "Linkword deleted.");
define("LWLAN_20", "Unable to find that linkword entry.");
define("LWLAN_21", "Word to autolink (or comma-separated list of words)");
define("LWLAN_22", "Activate?");
define("LWLAN_23", "Linkwords Administration");
define("LWLAN_24", "Manage Words");
//define("LWLAN_25", "Options");
define("LWLAN_26", "Areas in which to enable linkwords");
define("LWLAN_27", "This is the 'context' of the displayed text");
define("LWLAN_28", "Pages on which to disable linkwords");
define("LWLAN_29", "Same format as menu visibility control. One match per line. Specify a partial or complete URL. End with '!' for exact match of the end part of the link");
//define("LWLAN_30", "Save options");
define("LWLAN_31", "Add/edit linkword");
//define("LWLAN_32", "Linkword Options");
define("LWLAN_33", "Title areas");
define("LWLAN_34", "Item summaries");
define("LWLAN_35", "Body text");
define("LWLAN_36", "Descriptions (links etc)");
//define("LWLAN_37", "Legacy areas");
//define("LWLAN_38", "Clickable links");
//define("LWLAN_39", "Unprocessed text");
define("LWLAN_40", "User-entered titles (e.g. forum)");
define("LWLAN_41", "User-entered body text (e.g. forum)");
// Reserve numbers for further context strings



define("LWLAN_50", "Tooltip");
define("LWLAN_51", "Inactive");
define("LWLAN_52", "Linkword only");
define("LWLAN_53", "Tooltip only");
define("LWLAN_54", "Linkword and Tooltip");
define("LWLAN_55", "Open link in new window");
define("LWLAN_56", "Open in new page");
//define("LWLAN_57", "Error writing new values");
define("LWLAN_58", "Pref hook converted to 0.8 format");		// Used in 0.7-compatible stub only
define("LWLAN_59", "Enable Ajax functionality");
define("LWLAN_60", "LW ID");
//define("LWLAN_61", "ID");
define("LWLAN_62", "Tooltip ID (LW ID)");
define("LWLAN_63", "Positive integers only");
define("LWLAN_64", "Suppress link on current page");
define("LWLAN_65", "When checked, suppresses clickable link if points to current page");


// Installation-related
//define("LWLANINS_1", "Linkwords");
//define("LWLANINS_2", "This plugin links specified words with a defined link and/or tooltip");
//define("LWLANINS_3", "Configure LinkWords");
//define("LWLANINS_4", "To configure please click on the link in the plugins section of the admin front page");
//define("LWLANINS_5", "Upgrade complete");


// Help text
define("LAN_LW_HELP_00","Linkwords Help");
define("LAN_LW_HELP_01","
  <b>Areas to enable</b><br />
  many areas of text have an associated \"context\", and linkwords will only be displayed in areas matching that context.<br /><br />
  <b>Linkwords Disable</b><br />
  Linkwords may be disabled on specific pages, or pages matching a pattern. Enter these here (same syntax as for menus), one pattern per line. 
  If the pattern ends in \"!\", this corresponds to \"end of query\", and is usually an exact match. Otherwise any URL containing the specified string will match.<br />
  Note that linkwords are <i>never</i> displayed on admin pages.<br /><br />
  <b>Enable Ajax Functionality</b><br />
  The tooltips can use Ajax to get information for display. This usually requires some custom coding.<br /><br />
  <b>Suppress link on current page</b><br />
  Usually its pointless for the user to be able to click a link if they\"re already on the page. Tick this box to remove the option,<br />
  ");
define("LAN_LW_HELP_02","Define the words which become clickable links, or which display text on mouseover, here<br /><br />
  <b><u>Word to Link</u></b><br />
  This is case-insensitive. For multiple words mapping to the same links and tooltips, separate them with commas (no spaces)<br /><br />
  <b><u>Link</u></b><br />
  Define a clickable link here. If its an external link, it [i]must[/i] begin \"http://\". If its a link within this site, the normal {e_XXX} constants may be used.<br /><br />
  <b><u>Tooltip</u></b><br />
  This defines the text to be displayed when the user\"s mouse passes over the word.<br /><br />
  <b><u>LW ID (Tooltip ID)</u></b><br /><br />
  This defines an optional numeric ID to be used with Ajax processing. If blank, the database record number is used.<br /><br />
  <b><u>Activate?</u></b><br />
  Defines which options are active.
  ");





?>