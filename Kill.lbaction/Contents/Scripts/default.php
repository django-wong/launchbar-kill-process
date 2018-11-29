#!/usr/bin/php
<?php

// Use a copy of the original argv:
$args = $argv;

// Remove the first argument (the script's path):
$path = array_shift($args);

$items = [];

$count = count($args);

if (!$count) {
    print json_encode($items);
    exit;
}

$query = array_shift($args);
if ($query) {
	$list = listProcess($query);
	$output = [];
	foreach ($list as $process) {
		preg_match('/.*?\.app\//', $process[3], $app);
		array_push($output, [
			'title' => $process[4],
			'subtitle' => $process[3],
			'alwaysShowsSubtitle' => true,
			'icon' => $process[5],
			'path' => count($app) ? array_shift($app) : $process[3],
			'action' => 'kill.php',
			'actionArgument' => $process[1],
			'actionReturnsItems' => true,
			'badge' => sprintf('CPU: %%%s', $process[2])
		]);
	}
	print json_encode($output);
	exit;
}


/**
 * List all processes
 *
 * @param      string  $name   The name
 *
 * @return     array   [[PS Output, PID, CPU Usage, Path, Name, Icon]]
 */
function listProcess($name = null) {
	$last = exec(
		sprintf('ps -A -o pid -o %%cpu -o comm | sort -k2 -n -r | grep -i [^/]*%s[^/]*$', $name),
		$output, 
		$status
	);

	$returns = [];
	$cache = [];
	foreach ($output as $line) {
		preg_match('/(\d+)\s+(\d+[\.|\,]\d+)\s+(.*)/', $line, $matches);
		if (count($matches) < 4) {
			continue;
		}
		preg_match('/.*?\.app\//', $matches[3], $app);
		$matches[] = array_pop(explode('/', $matches[3]));
		$app = array_shift($app);
		if ($app && is_dir($app)) {
			if (array_key_exists($app, $cache)) {
				$bundleID = $cache[$app];
			} else {
				$bundleID = exec(sprintf('defaults read "%s/Contents/Info" CFBundleIdentifier', $app));
				$cache[$app] = $bundleID;
			}
			$matches[] = $bundleID;
		} else {
			$matches[] = "/System/Library/CoreServices/CoreTypes.bundle/Contents/Resources/ExecutableBinaryIcon.icns";
		}

		array_push($returns, $matches);
		unset($matches);
	}
	return $returns;
}
