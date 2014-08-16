<?php
/**
 * This script should be called by the github hook
 */

error_reporting(E_ALL);
$str = file_get_contents("php://input");

$convert = rawurldecode($str);

parse_str($convert);

parse_str($convert);
$payload = json_decode($convert);

if (strstr($_SERVER['SERVER_NAME'], 'play'))
{
	$targetBranch = 'maersk-version-qa';
	$basepath = '/home/play';
}
else
{
	$targetBranch = 'maersk-main';
	$basepath = '/home/staging';
}

if (!strstr($payload->ref, $targetBranch))
{
	echo 'Another branch was updated';

	return true;
}
else
{
	echo $targetBranch . ' was updated';
}

// Update repo
$cmd = 'cd ' . $basepath . '/git/redFORM2.5; git fetch --all 2<&1; ';
$cmd .= 'git reset --hard origin/' . $targetBranch . ' 2<&1; ';
$cmd .= 'git submodule update 2<&1; ';

// Build
$cmd .= 'phing 2<&1; ';

// Update db
$cmd .= 'php ' . $basepath . '/public_html/redformgithub/redInstall.php --extension=redform; ';

$output = shell_exec($cmd);

echo $output;
