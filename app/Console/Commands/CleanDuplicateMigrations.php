<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanDuplicateMigrations extends Command
{
    protected $signature = 'clean:duplicate-migrations';
    protected $description = 'Remove duplicate migration files from root if they already exist in any module';

    public function handle()
    {
        $rootMigrationPath = base_path('database/migrations');
        $modulesPath = base_path('Modules');

        if (!File::exists($modulesPath)) {
            $this->warn('❌ No Modules directory found.');
            return;
        }

        $this->info('🔍 Scanning modules for migration files...');
        $moduleMigrationFiles = [];

        foreach (File::directories($modulesPath) as $moduleDir) {
            $migrationPath = $moduleDir . '/Database/Migrations';
            if (File::exists($migrationPath)) {
                foreach (File::files($migrationPath) as $file) {
                    $baseName = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $file->getFilename());
                    $moduleMigrationFiles[] = $baseName;
                }
            }
        }

        if (empty($moduleMigrationFiles)) {
            $this->warn('⚠️ No migration files found in modules.');
            return;
        }

        $this->info('✅ Found ' . count($moduleMigrationFiles) . ' migration(s) in modules.');
        $deleted = 0;

        foreach (File::files($rootMigrationPath) as $file) {
            $baseName = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $file->getFilename());
            if (in_array($baseName, $moduleMigrationFiles)) {
                File::delete($file->getPathname());
                $this->info('🗑️ Deleted duplicate: ' . $file->getFilename());
                $deleted++;
            }
        }

        if ($deleted === 0) {
            $this->info('📁 No duplicates found to delete.');
        } else {
            $this->info("🎉 Cleanup complete. $deleted file(s) deleted.");
        }
    }
}
