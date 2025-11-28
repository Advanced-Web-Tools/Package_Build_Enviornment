<?php

function installPackage($zipFile, $devEnv): void
{
    echo color("To proceed, please follow these steps:\n", COLOR_YELLOW);
    echo color("1. In 'awt_config.php', set 'REMOTE_INSTALL_FOR_DEVS' to 'true'.\n", COLOR_YELLOW);
    echo color("2. In the same file ('awt_config.php'), set 'DEBUG' to 'true'.\n\n", COLOR_YELLOW);

    echo color("SECURITY WARNING:\n", COLOR_RED);
    echo color("- The 'REMOTE_INSTALL_FOR_DEVS' setting enables remote installation and MUST NEVER be 'true' in a production environment.\n", COLOR_RED);
    echo color("- Ensure 'dev_secret' in 'awt_config.php' matches the one in 'build/dev_env.json'.\n\n", COLOR_RED);

    echo color("Would you like to install this package now? (y/N): ", COLOR_GREEN);

    $handle = fopen("php://stdin", "r");
    $response = trim(fgets($handle));

    if (strtolower($response) === 'y') {
        if (!file_exists($zipFile)) {
            exit(color("Error: ZIP file not found.\n", COLOR_RED));
        }

        echo color("Sending package to installer...\n", COLOR_YELLOW);

        $curl = curl_init();

        $cfile = new CURLFile($zipFile, 'application/zip', basename($zipFile));
        $postFields = [
            'package' => $cfile,
            'devSecret' => $devEnv['devSecret']
        ];

        $url = $devEnv['address'] . $devEnv['remote_install_path'];
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                'Content-Type: multipart/form-data'
            ]
        ]);

        $serverResponse = curl_exec($curl);

        if (curl_errno($curl)) {
            echo color("cURL error: " . curl_error($curl) . "\n", COLOR_RED);
        } else {
            echo color("Package installed successfully.\n", COLOR_GREEN);
            echo "$serverResponse\n";
        }

        curl_close($curl);
    } else {
        echo color("Installation skipped.\n", COLOR_YELLOW);
    }
}

