<?php

declare(strict_types=1);

namespace E107\SqliScan;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;

/**
 * Discovers in-scope PHP files under a scan target (a single file or a
 * directory) and runs the CallSiteVisitor over each, accumulating every
 * classified e_db call site.
 *
 * Scope is the real e107 tree only: excluded directories (vendor, .claude,
 * e107_tests) and the e_db implementation files themselves never contribute.
 * Catalog paths are always reported relative to $root (the repo root) so a
 * single-file scan emits the same path string as a full-tree scan.
 */
final class Scanner
{
    private Parser $parser;

    /** @var list<string> */
    private array $errors = [];

    public function __construct(private readonly CallSiteVisitor $visitor)
    {
        $this->parser = (new ParserFactory())->createForNewestSupportedVersion();
    }

    /**
     * Scan $target (a file or directory) and classify every e_db call site.
     * Paths in the result are relative to $root.
     *
     * @return list<CallSite>
     */
    public function scan(string $target, string $root): array
    {
        $root = rtrim($root, '/');
        $traverser = new NodeTraverser();
        $traverser->addVisitor($this->visitor);

        foreach ($this->phpFiles($target) as $file) {
            $code = @file_get_contents($file);
            if ($code === false) {
                $this->errors[] = "unreadable: $file";
                continue;
            }
            try {
                $ast = $this->parser->parse($code);
            } catch (\Throwable $e) {
                $this->errors[] = "parse error in $file: " . $e->getMessage();
                continue;
            }
            if ($ast === null) {
                continue;
            }
            $this->visitor->setFile($this->relative($file, $root), $ast);
            $traverser->traverse($ast);
        }

        return $this->visitor->callSites();
    }

    /**
     * @return list<string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @return iterable<string> absolute paths to in-scope .php files under the
     *                          target (or the single target file itself)
     */
    private function phpFiles(string $target): iterable
    {
        if (is_file($target)) {
            // A single explicit file is scanned even if it sits in an otherwise
            // excluded location, except the e_db implementation files which are
            // never part of the catalog.
            if (strtolower(pathinfo($target, PATHINFO_EXTENSION)) === 'php'
                && !$this->isExcludedImplementationFile($target)) {
                yield $target;
            }
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveCallbackFilterIterator(
                new \RecursiveDirectoryIterator($target, \FilesystemIterator::SKIP_DOTS),
                function (\SplFileInfo $current): bool {
                    return $this->isAllowed($current);
                }
            )
        );

        foreach ($iterator as $info) {
            /** @var \SplFileInfo $info */
            if ($info->isFile() && strtolower($info->getExtension()) === 'php') {
                yield $info->getPathname();
            }
        }
    }

    /**
     * Directory/file gate applied during recursion (prunes whole subtrees).
     */
    private function isAllowed(\SplFileInfo $info): bool
    {
        if ($info->isDir()) {
            // Prune excluded directories anywhere in the tree.
            return !in_array($info->getFilename(), ['vendor', '.claude', 'e107_tests', '.git'], true);
        }

        return !$this->isExcludedImplementationFile($info->getPathname());
    }

    /**
     * The e_db implementation files themselves - the namespaced e107\Database
     * tree, the flat v2 compatibility and legacy files (e_db_interface.php,
     * e_db_legacy_trait.php, e_db_pdo_class.php) and the mysql backend - are
     * never part of the catalog, even when named as the explicit single-file
     * target.
     */
    private function isExcludedImplementationFile(string $path): bool
    {
        return (bool) preg_match('#/e107_handlers/Database/#', $path)
            || (bool) preg_match('#/e107_handlers/e_db[^/]*\.php$#', $path)
            || (bool) preg_match('#/e107_handlers/mysql_class\.php$#', $path);
    }

    private function relative(string $file, string $root): string
    {
        if (str_starts_with($file, $root . '/')) {
            return substr($file, strlen($root) + 1);
        }
        return $file;
    }
}
