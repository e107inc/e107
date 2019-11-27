<?php
/**
 * Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * Latest news menu
 */
if (!defined('e107_INIT')) { exit; }
/**
 * News Grid Menu
 *
 * @param string    $parm['caption']        text or constant - will use tablerender() when set.
 * @param integer   $parm['titleLimit']     number of chars fo news title
 * @param integer   $parm['summaryLimit']   number of chars for new summary
 * @param string    $parm['source']         latest (latest news items) | sticky (news items) | template (assigned to news-grid layout)
 * @param integer   $parm['order']          n.news_datestamp DESC
 * @param integer   $parm['limit']          10
 * @param string   $parm['layout']        default | or any key as defined in news_grid_template.php
 *
 * @example hard-coded {MENU: path=news/news_grid&limit=6&source=latest&featured=2&layout=other}
 * @example admin assigned - Add via Media-Manager and then configure.
 */


$cached = e107::getObject('news')->render_newsgrid($parm);

echo $cached;
