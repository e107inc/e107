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
     * @return mixed
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
}