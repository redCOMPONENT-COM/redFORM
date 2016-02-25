<?php
/**
 * Command line script for executing PHP Debug Checker during a Travis build.
 *
 * This CLI is used instead normal travis.yml execution to avoid error in travis build when
 * PHPMD exits with 2.
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @example: php checkers/phpmd.php component/ libraries/
 */

// Only run on the CLI SAPI
(php_sapi_name() == 'cli' ?: die('CLI only'));

// Script defines
define('REPO_BASE', dirname(__DIR__));

// Welcome message
fwrite(STDOUT, "\033[32;1mInitializing PHP Debug Missed Debug Code Checker.\033[0m\n");

$folders = array(
    '../component/',
    '../plugins/'
);

$exclude = array(
    'PagSeguroHelper.class.php'
);

$exclude = '--exclude=' . (count($exclude) > 1 ? '{' . implode(",", $exclude) . '}' : implode(",", $exclude));

foreach ($folders as $folder)
{
    $folderToCheck = REPO_BASE . '/' . $folder;

    if (!file_exists($folderToCheck))
    {
        fwrite(STDOUT, "\033[32;1mFolder: " . $folder . " does not exist\033[0m\n");
        continue;
    }

    $vardumpCheck = shell_exec('grep -r --include "*.php"' . $exclude . ' var_dump ' . $folderToCheck);
    $consolelogCheck = shell_exec('grep -r --include "*.js"' . $exclude . ' console.log ' . $folderToCheck);

    if ($vardumpCheck)
    {
        fwrite(STDOUT, "\033[31;1mWARNING: Missed Debug code detected: var_dump was found\033[0m\n");
        fwrite(STDOUT, $vardumpCheck);
        exit(1);
    }

    if ($consolelogCheck)
    {
        fwrite(STDOUT, "\033[31;1mWARNING: Missed Debug code detected: console.log was found\033[0m\n");
        fwrite(STDOUT, $consolelogCheck);
        exit(1);
    }
}

exit(0);
