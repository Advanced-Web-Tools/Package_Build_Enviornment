<?php

const NAME = '';
const MANIFEST = NAME . '/manifest.json';

const COLOR_RESET = "\033[0m";
const COLOR_RED = "\033[31m";
const COLOR_GREEN = "\033[32m";
const COLOR_YELLOW = "\033[33m";

function color(string $text, string $color): string {
    return $color . $text . COLOR_RESET;
}
