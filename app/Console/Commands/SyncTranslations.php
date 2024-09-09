<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncTranslations extends Command
{
    protected $signature = 'translations:scan';

    protected $description = 'Scan Blade and PHP files in app and views directories and update translation JSON files';

    public function handle()
    {
        // Define directories to scan
        $directoriesToScan = [
            resource_path('views'), // Blade files
            app_path(),             // PHP files in app directory
        ];

        $translations = [];

        foreach ($directoriesToScan as $directory) {
            $files = File::allFiles($directory);

            foreach ($files as $file) {
                if (preg_match_all('/__\([\'"](.+?)[\'"]\)/', $file->getContents(), $matches)) {
                    foreach ($matches[1] as $key) {
                        $translations[$key] = $key;
                    }
                }
            }
        }

        $languages = ['en', 'ar']; // Add more languages if needed
        foreach ($languages as $lang) {
            $filePath = base_path("lang/{$lang}.json");
            $existingTranslations = [];

            if (File::exists($filePath)) {
                $existingTranslations = json_decode(File::get($filePath), true);
            }

            $updatedTranslations = array_merge($translations, $existingTranslations);
            File::put($filePath, json_encode($updatedTranslations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        $this->info('Translations have been scanned and updated.');
    }
}
