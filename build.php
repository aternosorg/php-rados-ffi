#!/usr/bin/env php
<?php

const HEADER_PATH = __DIR__ . "/build/includes/";
const LIBRADOS_H_PATH = HEADER_PATH . "librados.h";
const ERRNO_H_PATH = HEADER_PATH . "errno.h";
const REPLACE_IMPORTS = [
    "netinet/in.h",
    "sys/types.h",
    "linux/types.h",
    "unistd.h",
    "string.h"
];

function deleteDirectory(string $path): void
{
    if (is_link($path)) {
        unlink($path);
    }

    if (!file_exists($path)) {
        return;
    }

    if (!is_dir($path)) {
        unlink($path);
        return;
    }
    $iterator = new DirectoryIterator($path);
    foreach ($iterator as $fileInfo) {
        if ($fileInfo->isDot()) {
            continue;
        }
        deleteDirectory($fileInfo->getPathname());
    }
    rmdir($path);
}

if (!`which cpp`) {
    echo "No CPP preprocessor found in PATH\n";
    exit(1);
}

$tempDir = sys_get_temp_dir() . "/" . "rados-ffi-" . uniqid();
@mkdir($tempDir, 0777, true);

foreach (REPLACE_IMPORTS as $import) {
    @mkdir(dirname($tempDir . "/" . $import), recursive: true);
    touch($tempDir . "/" . $import);
}

@mkdir(__DIR__ . "/includes", recursive: true);

$source = LIBRADOS_H_PATH;
$target = __DIR__ . "/includes/librados.h";

`cpp -P '{$source}' '{$target}' -isystem '{$tempDir}' -U __cplusplus -U CEPH_LIBRADOS_H -D __linux__`;

deleteDirectory($tempDir);

echo "Preprocessed librados.h\n";

$source = ERRNO_H_PATH;
$target = tempnam(sys_get_temp_dir(), "errno.h");

`cpp -P '{$source}' '{$target}' -dM`;

$content = file_get_contents($target);
unlink($target);

$constants = [];
$content = preg_replace_callback("/^\s*#define\s+(E[A-Z0-9_]+)\s+([0-9]+)\s*$/m", function ($matches) use (&$constants) {
    $constants[] = "    case " . $matches[1] . " = " . $matches[2] . ";";
    return "";
}, $content);

$template = file_get_contents(__DIR__ . "/build/Errno.php.template");
$template = str_replace("{{constants}}", implode("\n", $constants), $template);
$target = __DIR__ . "/src/Generated/Errno.php";
@mkdir(dirname($target), recursive: true);
file_put_contents($target, $template);

echo "Preprocessed errno.h\n";

