<?php
function isFileCorrupted($filePath)
{
    $contents = @file_get_contents($filePath);
    if ($contents === false) {
        // Unable to read the file, possibly due to permissions
        return false;
    }

    // Check for non-printable characters
    if (preg_match('/[^\P{C}\n\r\t]/u', $contents)) {
        return true;
    }

    // Optionally, check if the file is valid UTF-8 encoded
    if (!mb_check_encoding($contents, 'UTF-8')) {
        return true;
    }

    return false;
}

function scanDirectory($directory, $excludeDirs = [])
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            function ($file, $key, $iterator) use ($excludeDirs) {
                // Exclude specified directories
                if ($iterator->hasChildren() && in_array($file->getFilename(), $excludeDirs)) {
                    return false;
                }
                return true;
            }
        )
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $filePath = $file->getRealPath();
            // Skip common binary file types
            $ext = strtolower($file->getExtension());
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'ico', 'zip', 'exe', 'dll',  'swp'])) {
                continue;
            }

            if (isFileCorrupted($filePath)) {
                echo "Possible corrupted file: $filePath\n";
            }
        }
    }
}

// Set the directory to your Laravel project root
$projectDirectory = __DIR__;

// Specify the directory names to exclude
$excludeDirs = ['vendor', '.git', 'storage', 'node_modules'];

scanDirectory($projectDirectory, $excludeDirs);
