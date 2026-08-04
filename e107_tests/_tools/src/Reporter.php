<?php

declare(strict_types=1);

namespace E107\SqliScan;

/**
 * Renders the classified call sites: the catalog.json payload and the
 * human-readable per-method summary table printed to stdout.
 */
final class Reporter
{
    /**
     * Encode the call sites as the catalog JSON array.
     *
     * @param list<CallSite> $sites
     */
    public function toJson(array $sites): string
    {
        $payload = array_map(static fn (CallSite $s) => $s->toArray(), $sites);
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \RuntimeException('failed to encode catalog: ' . json_last_error_msg());
        }
        return $json;
    }

    /**
     * Deliberately write the catalog to a file (the master-catalog regen path).
     *
     * @param list<CallSite> $sites
     */
    public function writeCatalog(array $sites, string $path): void
    {
        if (file_put_contents($path, $this->toJson($sites) . "\n") === false) {
            throw new \RuntimeException("failed to write catalog to $path");
        }
    }

    /**
     * Compact gate/verify report: a one-line count followed by the failing
     * (unsafe-concat) sites only. Returns the rendered text.
     *
     * When a deferral baseline is supplied, the reviewed/known sites it records
     * are subtracted: the report lists only the NET-NEW unsafe-concat sites, notes
     * how many were baselined, and lists any stale baseline entries that no longer
     * match. Both the net-new sites and the stale entries fail the gate (see
     * DeferralBaseline::passes()), so the frozen residue stays in sync with the
     * code.
     *
     * @param list<CallSite> $sites
     */
    public function quietReport(array $sites, ?DeferralBaseline $baseline = null): string
    {
        $failing = array_values(array_filter(
            $sites,
            static fn (CallSite $s) => $s->safety === CallSite::SAFETY_UNSAFE
        ));

        if ($baseline === null) {
            return $this->renderUnsafe('unsafe-concat', $failing);
        }

        $new = $baseline->newUnsafe($sites);
        $stale = $baseline->staleEntries($sites);
        $baselined = count($failing) - count($new);

        $out = $this->renderUnsafe('new unsafe-concat', $new);
        $out .= sprintf("baselined (deferred): %d of %d total unsafe-concat\n", $baselined, count($failing));
        if (!empty($stale)) {
            $out .= 'stale baseline entries (no longer present; prune them from the baseline to pass): ' . count($stale) . "\n";
            foreach ($stale as $entry) {
                $out .= sprintf("  %s %s %s\n", $entry['file'], $entry['method'], $entry['sql_excerpt']);
            }
        }

        return $out;
    }

    /**
     * Render a one-line count plus a line per unsafe site.
     *
     * @param list<CallSite> $sites
     */
    private function renderUnsafe(string $label, array $sites): string
    {
        if (empty($sites)) {
            return $label . ": 0 (clean)\n";
        }

        $out = $label . ': ' . count($sites) . "\n";
        foreach ($sites as $s) {
            $tainted = $s->requestTainted ? ' [request-tainted]' : '';
            $out .= sprintf(
                "  %s:%d %s [%s]%s %s\n",
                $s->file,
                $s->line,
                $s->method,
                $s->tier,
                $tainted,
                $s->sqlExcerpt
            );
        }
        return $out;
    }

    /**
     * @param list<CallSite>     $sites
     * @param array<string, int> $methodOrder method => oracle count (for ordering)
     */
    public function summary(array $sites, array $methodOrder): string
    {
        $zero = [
            CallSite::SAFETY_STATIC => 0,
            CallSite::SAFETY_BOUND => 0,
            CallSite::SAFETY_ASSUMED_ARRAY => 0,
            CallSite::SAFETY_UNSAFE => 0,
        ];

        $byMethod = [];
        $files = [];
        $requestTaintedUnsafe = 0;
        foreach ($sites as $site) {
            $byMethod[$site->method] ??= $zero;
            $byMethod[$site->method][$site->safety]++;
            $files[$site->file] = true;
            if ($site->safety === CallSite::SAFETY_UNSAFE && $site->requestTainted) {
                $requestTaintedUnsafe++;
            }
        }

        // Order methods by the supplied oracle ordering, then any extras.
        $methods = array_keys($methodOrder);
        foreach (array_keys($byMethod) as $m) {
            if (!in_array($m, $methods, true)) {
                $methods[] = $m;
            }
        }

        $rows = [];
        $tot = $zero;
        foreach ($methods as $method) {
            $counts = $byMethod[$method] ?? $zero;
            foreach ($zero as $key => $_) {
                $tot[$key] += $counts[$key];
            }
            $rows[] = [
                $method,
                (string) $counts[CallSite::SAFETY_STATIC],
                (string) $counts[CallSite::SAFETY_BOUND],
                (string) $counts[CallSite::SAFETY_ASSUMED_ARRAY],
                (string) $counts[CallSite::SAFETY_UNSAFE],
                (string) array_sum($counts),
            ];
        }

        $header = ['method', 'static', 'bound-safe', 'assumed-safe-array', 'unsafe-concat', 'total'];
        $footer = [
            'TOTAL',
            (string) $tot[CallSite::SAFETY_STATIC],
            (string) $tot[CallSite::SAFETY_BOUND],
            (string) $tot[CallSite::SAFETY_ASSUMED_ARRAY],
            (string) $tot[CallSite::SAFETY_UNSAFE],
            (string) array_sum($tot),
        ];

        $out = $this->renderTable($header, $rows, $footer);
        $out .= "\nDistinct files: " . count($files) . "\n";
        $out .= 'Grand total e_db call sites: ' . array_sum($tot) . "\n";
        $out .= 'unsafe-concat sites (REAL injection surface): ' . $tot[CallSite::SAFETY_UNSAFE] . "\n";
        $out .= '  of which request-tainted (Phase 1 priority): ' . $requestTaintedUnsafe . "\n";
        $out .= $this->topRequestTaintedFiles($sites, 15);
        return $out;
    }

    /**
     * Rank files by their count of request-tainted unsafe-concat sites; these
     * seed the Phase 1 worklist.
     *
     * @param list<CallSite> $sites
     */
    private function topRequestTaintedFiles(array $sites, int $limit): string
    {
        $byFile = [];
        foreach ($sites as $site) {
            if ($site->safety === CallSite::SAFETY_UNSAFE && $site->requestTainted) {
                $byFile[$site->file] = ($byFile[$site->file] ?? 0) + 1;
            }
        }
        if (empty($byFile)) {
            return "\nTop request-tainted files: none\n";
        }
        arsort($byFile);
        $out = "\nTop " . min($limit, count($byFile)) . " request-tainted files by unsafe-concat count:\n";
        $rank = 0;
        foreach ($byFile as $file => $count) {
            if (++$rank > $limit) {
                break;
            }
            $out .= sprintf("  %2d  %s\n", $count, $file);
        }
        return $out;
    }

    /**
     * @param list<string>        $header
     * @param list<list<string>>  $rows
     * @param list<string>        $footer
     */
    private function renderTable(array $header, array $rows, array $footer): string
    {
        $all = array_merge([$header], $rows, [$footer]);
        $widths = [];
        foreach ($all as $row) {
            foreach ($row as $i => $cell) {
                $widths[$i] = max($widths[$i] ?? 0, strlen($cell));
            }
        }

        $fmt = function (array $row) use ($widths): string {
            $cells = [];
            foreach ($row as $i => $cell) {
                $cells[] = $i === 0
                    ? str_pad($cell, $widths[$i])
                    : str_pad($cell, $widths[$i], ' ', STR_PAD_LEFT);
            }
            return '| ' . implode(' | ', $cells) . ' |';
        };

        $sep = function () use ($widths): string {
            $parts = [];
            foreach ($widths as $w) {
                $parts[] = str_repeat('-', $w + 2);
            }
            return '+' . implode('+', $parts) . '+';
        };

        $lines = [$sep(), $fmt($header), $sep()];
        foreach ($rows as $row) {
            $lines[] = $fmt($row);
        }
        $lines[] = $sep();
        $lines[] = $fmt($footer);
        $lines[] = $sep();
        return implode("\n", $lines) . "\n";
    }
}
