<?php

namespace App\Support;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class RetryingFilesystem extends Filesystem
{
    public function replace($path, $content, $mode = null)
    {
        clearstatcache(true, $path);

        $directory = dirname($path);

        if (! is_dir($directory)) {
            $this->makeDirectory($directory, 0755, true);
        }

        $temporaryPath = tempnam($directory, basename($path));

        if ($temporaryPath === false) {
            throw new RuntimeException(
                "Unable to create a temporary file in: {$directory}"
            );
        }

        file_put_contents($temporaryPath, $content);

        if ($mode !== null) {
            chmod($temporaryPath, $mode);
        }

        $attempts = 15;
        $delayMicroseconds = 100_000; // 100 ms

        try {
            for ($attempt = 1; $attempt <= $attempts; $attempt++) {
                clearstatcache(true, $temporaryPath);
                clearstatcache(true, $path);

                if (@rename($temporaryPath, $path)) {
                    return;
                }

                if ($attempt < $attempts) {
                    usleep($delayMicroseconds);
                }
            }

            $error = error_get_last();

            throw new RuntimeException(
                $error['message'] ??
                "Unable to replace {$path} after {$attempts} attempts."
            );
        } finally {
            if (is_file($temporaryPath)) {
                @unlink($temporaryPath);
            }
        }
    }
}