<?php

declare(strict_types=1);

namespace E107\PluginMetadata;

/**
 * One rule a changed plugin broke, carrying the sentence the contributor reads.
 */
final class PolicyFailure
{
    /** Folder name under e107_plugins/, such as "news". */
    public string $folder;

    /** One of the {@see PluginVersionPolicy} REASON_* constants. */
    public string $reason;

    /** What is wrong and the exact attribute to write, in one sentence. */
    public string $message;

    public function __construct(string $folder, string $reason, string $message)
    {
        $this->folder = $folder;
        $this->reason = $reason;
        $this->message = $message;
    }
}
