<?php
/**
 * Emits a diagnostic and nothing else: a notice on PHP 5.6 and 7, a warning
 * on PHP 8. The exit status stays 0 either way, which is why the sweep this
 * replaced could not fail on it.
 */

echo $thisVariableWasNeverSet;
