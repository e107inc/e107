<?php

declare(strict_types=1);

namespace E107\SqliScan;

/**
 * A frozen set of reviewed unsafe-concat call sites that the CI gate tolerates,
 * so the gate fails only on NET-NEW unsafe concatenation rather than the
 * documented residue (foreign-schema importers, vouched whole-query executes,
 * fail-closed dynamic-identifier DDL).
 *
 * A site is keyed by (file, method, sql_excerpt) and counted, deliberately NOT
 * by line number: line numbers churn with every edit, whereas the excerpt and
 * its location are stable, so a baselined site keeps matching when surrounding
 * code moves. If the same (file, method, excerpt) appears more times than the
 * baseline records, the excess counts as new (a freshly added duplicate is a
 * real new site). This mirrors the PHPStan/Psalm baseline model.
 */
final class DeferralBaseline
{
    /**
     * @var list<array{file:string,method:string,sql_excerpt:string,count:int}>
     */
    private array $entries;

    /** @var array<string,int> key => baselined occurrence count */
    private array $counts;

    /**
     * @param list<array{file:string,method:string,sql_excerpt:string,count:int}> $entries
     */
    private function __construct(array $entries)
    {
        $this->entries = $entries;
        $this->counts = [];
        foreach ($entries as $entry) {
            $key = self::keyParts($entry['file'], $entry['method'], $entry['sql_excerpt']);
            $this->counts[$key] = ($this->counts[$key] ?? 0) + $entry['count'];
        }
    }

    /**
     * Build a baseline from the current unsafe-concat sites (the generation path).
     *
     * @param list<CallSite> $sites
     */
    public static function fromSites(array $sites): self
    {
        $byKey = [];
        foreach ($sites as $site) {
            if ($site->safety !== CallSite::SAFETY_UNSAFE) {
                continue;
            }
            $key = self::key($site);
            if (!isset($byKey[$key])) {
                $byKey[$key] = [
                    'file' => $site->file,
                    'method' => $site->method,
                    'sql_excerpt' => $site->sqlExcerpt,
                    'count' => 0,
                ];
            }
            $byKey[$key]['count']++;
        }
        ksort($byKey);

        return new self(array_values($byKey));
    }

    /**
     * Load a baseline from its JSON file.
     *
     * @throws \RuntimeException when the file is missing or malformed.
     */
    public static function load(string $path): self
    {
        if (!is_file($path)) {
            throw new \RuntimeException("baseline file not found: $path");
        }

        $data = json_decode((string) file_get_contents($path), true);
        if (!is_array($data) || !isset($data['entries']) || !is_array($data['entries'])) {
            throw new \RuntimeException("malformed baseline file: $path");
        }

        $entries = [];
        foreach ($data['entries'] as $entry) {
            if (!isset($entry['file'], $entry['method'], $entry['sql_excerpt'])) {
                throw new \RuntimeException("malformed baseline entry in: $path");
            }
            $entries[] = [
                'file' => (string) $entry['file'],
                'method' => (string) $entry['method'],
                'sql_excerpt' => (string) $entry['sql_excerpt'],
                'count' => (int) ($entry['count'] ?? 1),
            ];
        }

        return new self($entries);
    }

    /**
     * The unsafe-concat sites NOT covered by the baseline: brand-new keys, or
     * occurrences of a baselined key beyond its recorded count. These are what
     * the gate fails on.
     *
     * @param list<CallSite> $sites
     * @return list<CallSite>
     */
    public function newUnsafe(array $sites): array
    {
        $remaining = $this->counts;
        $new = [];
        foreach ($sites as $site) {
            if ($site->safety !== CallSite::SAFETY_UNSAFE) {
                continue;
            }
            $key = self::key($site);
            if (($remaining[$key] ?? 0) > 0) {
                $remaining[$key]--;
                continue;
            }
            $new[] = $site;
        }

        return $new;
    }

    /**
     * Baseline entries that no longer match any current unsafe site (the site
     * was migrated or removed). Surfacing these lets the baseline be pruned so
     * it keeps shrinking toward zero.
     *
     * @param list<CallSite> $sites
     * @return list<array{file:string,method:string,sql_excerpt:string,count:int}>
     */
    public function staleEntries(array $sites): array
    {
        $remaining = $this->counts;
        foreach ($sites as $site) {
            if ($site->safety !== CallSite::SAFETY_UNSAFE) {
                continue;
            }
            $key = self::key($site);
            if (($remaining[$key] ?? 0) > 0) {
                $remaining[$key]--;
            }
        }

        $stale = [];
        foreach ($this->entries as $entry) {
            $key = self::keyParts($entry['file'], $entry['method'], $entry['sql_excerpt']);
            $leftover = $remaining[$key] ?? 0;
            if ($leftover > 0) {
                $stale[] = ['file' => $entry['file'], 'method' => $entry['method'],
                    'sql_excerpt' => $entry['sql_excerpt'], 'count' => $leftover];
                $remaining[$key] = 0;
            }
        }

        return $stale;
    }

    /**
     * The gate's verdict for the current sites against this baseline: it passes
     * only when there is neither a net-new unsafe-concat site NOR a stale entry.
     *
     * A net-new site means the injection surface grew. A stale entry means a
     * baselined site was migrated or its SQL changed, so the frozen set no longer
     * matches the code; tolerating that silently would let the baseline drift out
     * of sync. Either way the gate fails until the baseline is re-frozen on
     * purpose (via --write-baseline), so every change to the reviewed residue
     * stays intentional and reviewable.
     *
     * @param list<CallSite> $sites
     */
    public function passes(array $sites): bool
    {
        return $this->newUnsafe($sites) === [] && $this->staleEntries($sites) === [];
    }

    /** Total number of baselined occurrences. */
    public function size(): int
    {
        return array_sum($this->counts);
    }

    /**
     * @return array{entries:list<array{file:string,method:string,sql_excerpt:string,count:int}>}
     */
    public function toArray(): array
    {
        return ['entries' => $this->entries];
    }

    /**
     * Write the baseline to a JSON file (the generation path).
     *
     * @throws \RuntimeException on encode/write failure.
     */
    public function write(string $path): void
    {
        $json = json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \RuntimeException('failed to encode baseline: ' . json_last_error_msg());
        }
        if (file_put_contents($path, $json . "\n") === false) {
            throw new \RuntimeException("failed to write baseline to $path");
        }
    }

    public static function key(CallSite $site): string
    {
        return self::keyParts($site->file, $site->method, $site->sqlExcerpt);
    }

    private static function keyParts(string $file, string $method, string $excerpt): string
    {
        // NUL separates the parts; it can never appear in a file path or a PHP
        // source excerpt, so the key is unambiguous.
        return $file . "\0" . $method . "\0" . $excerpt;
    }
}
