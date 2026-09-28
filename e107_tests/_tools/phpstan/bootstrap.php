<?php

declare(strict_types=1);

/**
 * Makes known to PHPStan what e107 declares at run time: the e_db and e_db_common class aliases, and every constant the tree defines.
 */

require_once __DIR__ . '/../../../e107_handlers/e_db_interface.php';

foreach (array_keys(require __DIR__ . '/defined-constants.php') as $name) {
    if (!defined($name)) {
        define($name, null);
    }
}
