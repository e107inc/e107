# e107 v2 Core Dependencies

## Interfacing with Dependencies

The public interfaces in `./e107_handlers/vendor/` are not considered stable.

Plugins **should not** call code inside `./e107_handlers/vendor/` directly.
They **should** only use interfaces (handlers) provided by the e107 framework in `./e107_handlers/`.

## Compatibility Note

e107 has historically bundled the full source code of external dependencies in the core repository.
Some code, particularly syncing from the GitHub remote, expects dependencies to be included in the core source.

This behavior will be maintained until all existing code depending on the behavior is updated to support resolving dependencies with Composer.

### Transition Plan

|e107 Version|Dependency Location|Managed with Composer?|Dependencies Copied in Core Repository?|Behavior Change|
|---|---|---|---|---|
|`<2.3`|`./e107_handlers/`|No|Yes|Legacy behavior|
|`^2.3`|`./e107_handlers/vendor/`|Yes|Yes|Dependencies begin moving to be managed by Composer.  Dependencies target the lowest version of PHP supported by e107 (`config.platform.php` option in `./composer.json`).|
|`^3`|`./e107_handlers/vendor/`|Yes|No|All dependency code is deleted from the core repository's `./e107_handlers/vendor/` folder.  The e107 installer runs `composer install` at the beginning of the install process.  The e107 self-updater runs `composer install` after deploying the desired e107 version.  Only e107 releases may have dependencies bundled in the release package for offline/Intranet/firewalled installations.

