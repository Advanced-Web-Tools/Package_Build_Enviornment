<?php
function loadDevEnvConfig() {
    $devEnvFile = 'build/dev_env.json';

    if (!file_exists($devEnvFile)) {
        exit(color("Error: dev_env.json not found.\n", COLOR_RED));
    }

    $devEnvContent = file_get_contents($devEnvFile);
    $devEnv = json_decode($devEnvContent, true);

    if (!$devEnv) {
        exit(color("Error: Invalid JSON in dev_env.json.\n", COLOR_RED));
    }

    return $devEnv;
}

function loadFileCache(): array
{
    $cacheFile = 'build/.file_cache.json';

    if (!file_exists($cacheFile)) {
        echo color("Cache file not found. Creating new one.\n", COLOR_YELLOW);
        file_put_contents($cacheFile, '{}');
        return [];
    }

    $cacheContent = file_get_contents($cacheFile);
    return json_decode($cacheContent, true) ?: [];
}

function saveFileCache(array $cache): void
{
    $cacheFile = 'build/.file_cache.json';
    file_put_contents($cacheFile, json_encode($cache, JSON_PRETTY_PRINT));
}

function createZip($manifest, $version): string {
    $devEnv = loadDevEnvConfig();

    $cache = loadFileCache();
    
    $buildSaveFormat = $devEnv['build_save_format'] ?? '<name>-<version>';

    $name = $manifest['name'] ?? 'UnknownApp';

    $zipFileName = str_replace(
        ['<name>', '<version>', '<description>', '<author>', '<license>'],
        [
            $name,
            $manifest["version"],
            $manifest['description'] ?? 'No description',
            $manifest['author'] ?? 'Unknown Author',
            $manifest['license'] ?? 'Unknown License'
        ],
        $buildSaveFormat
    );

    $releaseFolder = $devEnv['release_folder'] ?? 'releases';

    if (!is_dir($releaseFolder)) {
        mkdir($releaseFolder, 0777, true);
    }

    if($devEnv["build_mode"] === "dev_fast")
        $zipFileName .= "-dev_fast";

    $zipFilePath = $releaseFolder . DIRECTORY_SEPARATOR . $zipFileName . '.zip';

    $zip = new ZipArchive();

    $rootPath = realpath("./$name");

    if ($rootPath === false) {
        exit(color("Error: Invalid path for '$name'. Make sure the plugin folder exists.\n", COLOR_RED));
    }

    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $filesZipped = [];

        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath) + 1);
            $normalizedRelativePath = str_replace('\\', '/', $relativePath);

            if ($file->isDir()) {
                $zip->addEmptyDir($normalizedRelativePath);
            } else {
                if (($devEnv["build_mode"] ?? "") === "dev_fast") {

                    if(str_ends_with($filePath, 'manifest.json')) {
                        $zip->addFile($filePath, $normalizedRelativePath);
                        $filesZipped[] = $filePath;
                        continue;
                    }

                    if(str_ends_with($filePath, DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "icon" . DIRECTORY_SEPARATOR . $manifest['icon']))
                    {
                        $zip->addFile($filePath, $normalizedRelativePath);
                        $filesZipped[] = $filePath;
                        continue;
                    }

                    $currentHash = hash_file('sha256', $filePath);
                    if (isset($cache[$normalizedRelativePath]) && 
                        $cache[$normalizedRelativePath] === $currentHash) {
                        continue;
                    }
                    $cache[$normalizedRelativePath] = $currentHash;
                }
                $zip->addFile($filePath, $normalizedRelativePath);
                $filesZipped[] = $filePath;
            }
        }

        if (($devEnv["build_mode"] ?? "") === "dev_fast") {
            saveFileCache($cache);
        }

        foreach ($filesZipped as $file) {
            echo color("Added file: $file\n", COLOR_GREEN);
        }

        $zip->close();
        echo color("Folder zipped successfully as '$zipFilePath'.\n", COLOR_GREEN);
    } else {
        exit(color("Failed to create zip file.\n", COLOR_RED));
    }

    return $zipFilePath;
}