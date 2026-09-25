<?php

declare(strict_types=1);

namespace E107\PluginMetadata;

/**
 * One bundled plugin that a pull request changed, in the terms
 * {@see PluginVersionPolicy::failures()} judges it by.
 */
final class ChangedPlugin
{
    /** Folder name under e107_plugins/, such as "news". */
    public string $folder;

    /** The version at the last tagged release, or null when the plugin is new since it. */
    public ?string $baselineVersion;

    /** The version attribute at the branch head, or null when the file declares none. */
    public ?string $headVersion;

    /** The date attribute at the branch head, or null when the file declares none. */
    public ?string $headDate;

    /** The newest author date, YYYY-MM-DD, among the commits touching the plugin's files. */
    public string $lastTouchedDate;

    public function __construct(
        string $folder,
        ?string $baselineVersion,
        ?string $headVersion,
        ?string $headDate,
        string $lastTouchedDate
    ) {
        $this->folder = $folder;
        $this->baselineVersion = $baselineVersion;
        $this->headVersion = $headVersion;
        $this->headDate = $headDate;
        $this->lastTouchedDate = $lastTouchedDate;
    }
}
