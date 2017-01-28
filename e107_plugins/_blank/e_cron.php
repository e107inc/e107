<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

class _blank_cron       // plugin-folder name + '_cron'.
{
    function config() // Setup
    {
        $cron = array();

        $cron[] = array(
            'name'            => "Name of my function",  // Displayed in admin area. .
            'function'        => "myFunction",    // Name of the function which is defined below.
            'category'        => 'mail',           // Choose between: mail, user, content, notify, or backup
            'description'     => "Description of what my function does"  // Displayed in admin area.
        );

        return $cron;
    }

    public function myFunction()
    {
        // Do Something.
    }

}