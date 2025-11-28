<?php

require_once 'constants.php';
require_once 'env_loader.php';
require_once 'zip_creator.php';
require_once 'installer.php';

$devEnv = loadEnvConfig();

if (!file_exists(MANIFEST)) {
    exit(color("Error: manifest.json not found.\n", COLOR_RED));
}

$manifestContent = file_get_contents(MANIFEST);
$manifest = json_decode($manifestContent, true);

if (!$manifest) {
    exit(color("Error: Invalid JSON in manifest.\n", COLOR_RED));
}

$type = null;
if (isset($manifest['plugin'])) {
    $type = 'plugin';
} elseif (isset($manifest['theme'])) {
    $type = 'theme';
} elseif (isset($manifest['system'])) {
    $type = 'system';
} else {
    exit(color("Error: No valid type (plugin, theme, system) found in manifest.\n", COLOR_RED));
}

echo color("Success: Type $type found in manifest.\n", COLOR_GREEN);

$version = $manifest[$type]['version'] ?? null;
if (!$version) {
    exit(color("Error: Version not found in manifest.\n", COLOR_RED));
}

echo color("Version: $version.\n", COLOR_GREEN);

$zipFile = createZip($manifest[$type], $version);

installPackage($zipFile, $devEnv);
