<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2019 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

class _blank_print       // plugin-folder name + '_print'.
{
    function config() // Setup
    {
        $print = array();

        $print[] = array(
            'name'            => "Name of my print function",  // Displayed in admin area. .
            'function'        => "myFunction",    // Name of the function which is defined below.
            'description'     => "Description of what my function does"  // Displayed in admin area.
        );

        return $print;
    }

    public function myFunction()
    {
        // Do Something.
    }

}