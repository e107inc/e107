<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

abstract class CoreImage
{
    const EXCLUDED_PATHS = [
        'install.php',
        'robots.txt'
    ];

    const EXCLUDED_PATHS_REMOVED = [
        ...self::EXCLUDED_PATHS,
        'e107_plugins/tagwords/',
        'e107_plugins/alt_auth/',
        'e107_plugins/alt_news/',
        'e107_plugins/calendar_menu/',
        'e107_plugins/content/',
        'e107_plugins/integrity_check/',
        'e107_plugins/linkspage/',
        'e107_plugins/newsletter/',
        'e107_plugins/online_extended_menu/',
        'e107_plugins/pdf/',
        'e107_plugins/tree_menu/',
        'e107_plugins/articles_menu/',
        'e107_plugins/backend_menu/',
        'e107_plugins/custom/',
        'e107_plugins/custom_pages/',
        'e107_plugins/fader_menu/',
        'e107_plugins/headlines_menu/',
        'e107_plugins/newforumposts_main/',
        'e107_plugins/newforumposts_menu/',
        'e107_plugins/review_menu/',
        'e107_plugins/ypslide_menu/',
        'e107_plugins/theme_layout/',
        'e107_themes/bootstrap4/',
        'e107_themes/landingzero/',
        'e107_themes/bootstrap/',
        'e107_themes/core/',
        'e107_themes/crahan/',
        'e107_themes/e107v4a/',
        'e107_themes/human_condition/',
        'e107_themes/interfectus/',
        'e107_themes/jayya/',
        'e107_themes/khatru/',
        'e107_themes/kubrick/',
        'e107_themes/lamb/',
        'e107_themes/leaf/',
        'e107_themes/newsroom/',
        'e107_themes/sebes/',
        'e107_themes/templates/',
        'e107_themes/vekna_blue/',
        'e107_themes/reline/',
        'e107_themes/clan/',
        'e107_themes/comfort/',
        'e107_themes/e107/',
        'e107_themes/fiblack3d/',
        'e107_themes/nordranious/',
        'e107_themes/phpbb/',
        'e107_themes/ranyart/',
        'e107_themes/smacks/',
        'e107_themes/soar/',
        'e107_themes/wan/',
        'e107_themes/xog/',
        'e107_themes/blue_patriot/',
        'e107_themes/comfortless/',
        'e107_themes/example/',
        'e107_themes/leap of faith/',
        'e107_themes/nagrunium/',
    ];

    protected function create_image($exportFolder, $tempFolder, $currentVersion)
    {
        echo("[Core-Image] Scanning Dir: " . $exportFolder . "\n");
        $this->generateCurrentChecksums($exportFolder, $currentVersion);

        echo("[Core-Image] Scanning Removed Files from Git" . "\n");
        $this->generateRemovedChecksums($tempFolder);
    }

    protected function generateCurrentChecksums($exportFolder, $currentVersion)
    {
        $absoluteBase = realpath($exportFolder);
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($exportFolder));

        /**
         * @var $file DirectoryIterator
         */
        foreach ($iterator as $file)
        {
            if ($file->isDir()) continue;

            $absolutePath = $file->getRealPath();
            $relativePath = preg_replace("/^" . preg_quote($absoluteBase . "/", "/") . "/", "", $absolutePath);

            if (empty($relativePath) || $relativePath == $absolutePath) continue;
            if ($this->isExcluded($relativePath, self::EXCLUDED_PATHS)) continue;

            $checksum = $this->checksumPath($absolutePath);
            $this->insertChecksumIntoDatabase($relativePath, $checksum, $currentVersion);
        }
    }

    protected function checksumPath($filename)
    {
        return $this->checksum(file_get_contents($filename));
    }

    protected function checksum($body)
    {
        return md5(str_replace(array(chr(13), chr(10)), '', $body));
    }

    abstract protected function insertChecksumIntoDatabase(&$relativePath, &$checksum, &$releaseVersion);

    protected function generateRemovedChecksums($tempFolder)
    {
        $tags = $this->getGitTags();
        $timeMachineFolder = $this->prepTimeMachine($tempFolder);
        $this->generateRemovedChecksumsFromTags($tags, $timeMachineFolder);
    }

    /**
     * @return array
     */
    protected function getGitTags()
    {
        $stdout = '';
        OsHelper::runValidated('git tag --list ' . escapeshellarg("v*"), $stdout);
        $tags = explode("\n", trim($stdout));
        $versions = [];
        foreach ($tags as $tag)
        {
            $versions[] = preg_replace("/^v/", "", $tag);
        }
        $tags = array_combine($tags, $versions);
        unset($versions);
        uasort($tags, function ($a, $b)
        {
            return -version_compare($a, $b);
        });
        $tags = array_filter($tags, function ($version)
        {
            return !preg_match("/[a-z]/i", $version);
        });
        return $tags;
    }

    /**
     * @param $tempFolder
     * @param $repoFolder
     * @return string
     */
    protected function prepTimeMachine($tempFolder)
    {
        $timeMachineFolder = $tempFolder . "/git_time_machine/";
        OsHelper::runValidated('mkdir -p ' . escapeshellarg($timeMachineFolder));
        OsHelper::runValidated('git rev-parse --show-toplevel', $repoFolder);
        $repoFolder = realpath(trim($repoFolder) . "/.git");
        OsHelper::runValidated(
            'cp -a ' .
            escapeshellarg($repoFolder) .
            ' ' .
            escapeshellarg($timeMachineFolder)
        );
        return $timeMachineFolder;
    }

    /**
     * @param array $tags
     * @param $timeMachineFolder
     */
    protected function generateRemovedChecksumsFromTags($tags, $timeMachineFolder)
    {
        foreach ($tags as $tag => $version)
        {
            $stdout = '';
            OsHelper::runValidated(
                'git --no-pager diff --no-renames --name-only --diff-filter D ' . escapeshellarg($tag),
                $stdout
            );
            $removedFiles = explode("\n", trim($stdout));
            OsHelper::runValidated(
                'git -C ' . escapeshellarg($timeMachineFolder) . ' ' .
                'checkout ' . escapeshellarg($tag)
            );
            foreach ($removedFiles as $removedFilePath)
            {
                if ($this->isExcluded($removedFilePath, self::EXCLUDED_PATHS_REMOVED)) continue;

                $checksum = $this->checksumPath($timeMachineFolder . '/' . $removedFilePath);
                $this->insertChecksumIntoDatabase($removedFilePath, $checksum, $version);
            }
        }

        OsHelper::runValidated('rm -rf ' . escapeshellarg($timeMachineFolder));
    }


    protected function generateStub()
    {
        $data = "<?php\n";
        $data .= "/*\n";
        $data .= "+ ----------------------------------------------------------------------------+\n";
        $data .= "|     e107 website system\n";
        $data .= "|\n";
        $data .= "|     Copyright (C) 2008-" . date("Y") . " e107 Inc. \n";
        $data .= "|     http://e107.org\n";
        //	$data .= "|     jalist@e107.org\n";
        $data .= "|\n";
        $data .= "|     Released under the terms and conditions of the\n";
        $data .= "|     GNU General Public License (http://gnu.org).\n";
        $data .= "|\n";
        $data .= "|     \$URL$\n";
        $data .= "|     \$Id$\n";
        $data .= "+----------------------------------------------------------------------------+\n";
        $data .= "*/\n\n";
        $data .= "if (!defined('e107_INIT')) { exit; }\n\n";

        return $data;
    }

    /**
     * Determines whether the provided relative path should make it into the generated integrity database
     * @param string $relativePath Relative path candidate
     * @param string[] $excludedPaths The list of paths to exclude
     * @return bool TRUE if the relative path should not be added to the integrity database; FALSE otherwise
     */
    protected function isExcluded($relativePath, $excludedPaths = self::EXCLUDED_PATHS)
    {
        $excludedFolders = array_filter($excludedPaths, function ($excludedPath)
        {
            $needle = '/';
            return substr($excludedPath, -strlen($needle)) === $needle;
        });
        foreach ($excludedFolders as $excludedFolder)
        {
            if (substr($relativePath, 0, strlen($excludedFolder)) === $excludedFolder) return true;
        }

        $excludedFiles = array_diff($excludedPaths, $excludedFolders);
        return in_array($relativePath, $excludedFiles);
    }
}