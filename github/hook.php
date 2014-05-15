<?php
/**
 * This script should be called by the github hook
 */

error_reporting(E_ALL);
$str = file_get_contents("php://input");

$convert = rawurldecode($str);

parse_str($convert);

$targetBranch = 'maersk-main';

// Update repo
$cmd = 'cd /home/staging/git/redFORM2.5; git fetch --all; ';
$cmd .= 'git reset --hard origin/' . $targetBranch . '; ';
$cmd .= 'git submodule update; ';

// Build
$cmd .= 'phing 2<&1; ';

// Update db
$cmd .= 'php /home/staging/public_html/redInstallRedform.php; ';

$output = shell_exec($cmd);

echo $output;
