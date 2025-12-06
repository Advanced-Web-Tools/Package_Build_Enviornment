<?php

function loadEnvConfig(): array {
    $devEnvPath = __DIR__ . '/../dev_env.json';
    if (!file_exists($devEnvPath)) {
        exit(color("Error: dev_env.json not found.\n", COLOR_RED));
    }

    $devEnvContent = file_get_contents($devEnvPath);
    $devEnv = json_decode($devEnvContent, true);

    if (!$devEnv) {
        exit(color("Error: Invalid JSON in dev_env.json.\n", COLOR_RED));
    }

    $options = getopt('', ['install', 'fast']);
    $install = isset($options['install']);
    $fast = isset($options['fast']);
    $devEnv['build_mode'] = $fast ? "dev_fast" : "dev";
    $devEnv['install'] = $install;

    return $devEnv;
}
