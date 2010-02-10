<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Unobtrusive JS support 
 *
 * $Source: /cvs_backup/e107_0.8/e107_files/e_css.php,v $
 * $Revision$
 * $Date$
 * $Author$
*/
if (!defined('e107_INIT')) { exit; }
// No JavaScript support using CSS
// How to use:
// - For elements that are to be permanently hidden when JavaScript is enabled, set CSS class to e-hide-if-js
// - For elements that are to be permanently shown when JavaScript is enabled, set CSS class to e-show-if-js
// - For elements to be temporarily hidden when JavaScript is enabled (so that they may later be shown using JavaScript), set CSS class to e-hideme

//TODO basic stuff so - work in progress - questions
// - Should the CSS just be in two CSS files (more server requests per page load)
// - Should the JavaScript go in the core JS file? Potentially better here to keep this all together
// - Should this even be a separate file? (extra PHP include per page load)
echo "
<style type='text/css' id='e-core-css'>
   /* Used to hide elements when JavaScript is enabled */
   .e-hide-if-js { display: none; }
</style>
<style type='text/css' id='e-js-css'>
   /* Used to show elements when JavaScript is disabled */
   .e-show-if-js { display: none; }
   
   /* Used to hide elements when JavaScript is disabled */
   .e-hide-if-js { display: block; }
   a.e-hide-if-js,
   span.e-hide-if-js,
   button.e-hide-if-js { display: inline; }
</style>
<script type='text/javascript'>
   //$('e-js-css').disabled=true;
   // 'disabled' is not valid style attribute, fails with Konqueror (tested on Fedora) 
   $('e-js-css').remove();
</script>
";
?>