<?php

declare(strict_types=1);

namespace E107\PluginMetadata;

/**
 * The plugin.xml convention a pull request touching a bundled plugin has to
 * satisfy: the plugin's version has risen since the last tagged release, and
 * its date is no older than the newest change to the plugin.
 *
 * Pure decision logic, handed facts that somebody else read out of git, so the
 * convention is testable without a repository to read.
 */
final class PluginVersionPolicy
{
    /** The version attribute is absent, or is not a plain dotted number. */
    public const REASON_VERSION_MALFORMED = 'version-malformed';

    /** The version at the last tagged release is not a plain dotted number. */
    public const REASON_BASELINE_MALFORMED = 'baseline-malformed';

    /** The version has not risen above the one at the last tagged release. */
    public const REASON_VERSION_NOT_BUMPED = 'version-not-bumped';

    /** The date attribute is absent, malformed, or older than the newest change. */
    public const REASON_DATE_STALE = 'date-stale';

    /** Versions e107 keeps verbatim: digits separated by dots, nothing else. */
    private const WELL_FORMED = '/^\d+(\.\d+)*\z/';

    private const ISO_DATE = '/^\d{4}-\d{2}-\d{2}\z/';

    private string $baselineTag;

    /** @param string $baselineTag the release the baseline versions were read at, such as "v2.3.12". */
    public function __construct(string $baselineTag)
    {
        $this->baselineTag = $baselineTag;
    }

    /**
     * Every rule the given plugins break, plugin by plugin in the given order.
     *
     * @param list<ChangedPlugin> $plugins
     * @return list<PolicyFailure>
     */
    public function failures(array $plugins): array
    {
        $failures = [];
        foreach ($plugins as $plugin) {
            $versionFailure = $this->versionFailure($plugin);
            if ($versionFailure !== null) {
                $failures[] = $versionFailure;
            }
            $dateFailure = $this->dateFailure($plugin);
            if ($dateFailure !== null) {
                $failures[] = $dateFailure;
            }
        }

        return $failures;
    }

    private function versionFailure(ChangedPlugin $plugin): ?PolicyFailure
    {
        $version = (string) $plugin->headVersion;
        if (preg_match(self::WELL_FORMED, $version) !== 1) {
            $stated = $plugin->headVersion === null
                ? 'the version attribute is missing'
                : 'version "' . $version . '" is not a plain dotted number';

            return new PolicyFailure($plugin->folder, self::REASON_VERSION_MALFORMED,
                $stated . ', and e107 discards everything but digits and dots when it reads the file;'
                . ' write something of the shape version="1.0.1".');
        }

        $baseline = $plugin->baselineVersion;
        if ($baseline === null) {
            return null;
        }

        if (preg_match(self::WELL_FORMED, $baseline) !== 1) {
            $stated = $baseline === ''
                ? 'the plugin.xml at ' . $this->baselineTag . ' declares no version this check can read'
                : 'the version at ' . $this->baselineTag . ' is "' . $baseline
                    . '", which is not a plain dotted number';

            return new PolicyFailure($plugin->folder, self::REASON_BASELINE_MALFORMED,
                $stated . ', so whether this version rose above it cannot be decided here;'
                . ' the plugin needs a version a maintainer has settled.');
        }

        if (self::risesAbove($version, $baseline)) {
            return null;
        }

        return new PolicyFailure($plugin->folder, self::REASON_VERSION_NOT_BUMPED,
            'version "' . $version . '" does not rise above "' . $baseline . '", the version at '
            . $this->baselineTag . ': write version="' . self::nextPatch($baseline) . '".');
    }

    private function dateFailure(ChangedPlugin $plugin): ?PolicyFailure
    {
        $date = (string) $plugin->headDate;
        $real = self::isDate($date);
        if ($real && $date >= $plugin->lastTouchedDate) {
            return null;
        }

        $stated = $real
            ? 'date "' . $date . '" is older than the newest change to the plugin'
            : 'the date attribute is missing or is not a YYYY-MM-DD date';

        return new PolicyFailure($plugin->folder, self::REASON_DATE_STALE,
            $stated . '; write the date of the commit you are about to make, or date="'
            . $plugin->lastTouchedDate . '" if you are amending the one you have.');
    }

    /** Whether the string is a real calendar date written YYYY-MM-DD. */
    private static function isDate(string $date): bool
    {
        if (preg_match(self::ISO_DATE, $date) !== 1) {
            return false;
        }
        [$year, $month, $day] = explode('-', $date);

        return checkdate((int) $month, (int) $day, (int) $year);
    }

    /** Whether the first version is numerically above the second, level by level, so 1.0.0 does not rise above 1.0. */
    private static function risesAbove(string $version, string $baseline): bool
    {
        $left = explode('.', $version);
        $right = explode('.', $baseline);
        $levels = max(count($left), count($right));

        for ($level = 0; $level < $levels; $level++) {
            $here = (int) ($left[$level] ?? 0);
            $there = (int) ($right[$level] ?? 0);
            if ($here !== $there) {
                return $here > $there;
            }
        }

        return false;
    }

    /** The smallest version above the given one: its third level, raised by one. */
    private static function nextPatch(string $version): string
    {
        $parts = explode('.', $version);
        while (count($parts) < 3) {
            $parts[] = '0';
        }
        $last = count($parts) - 1;
        $parts[$last] = (string) ((int) $parts[$last] + 1);

        return implode('.', $parts);
    }
}
