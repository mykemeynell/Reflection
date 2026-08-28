<?php

declare(strict_types=1);

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

function projectPhpFiles(): array
{
    $root = dirname(__DIR__, 2);
    $files = [];

    foreach ([$root.'/src', $root.'/tests'] as $directory) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->getExtension() === 'php') {
                $files[str_replace($root.'/', '', $file->getPathname())] = [$file->getPathname()];
            }
        }
    }

    ksort($files);

    return $files;
}

it('enables strict types in every PHP file', function (string $file): void {
    expect(file_get_contents($file))->toMatch('/^<\?php\s+declare\(strict_types=1\);/');
})->with(projectPhpFiles());
