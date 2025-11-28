<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleMigration extends Command
{
    protected $signature = 'make:module-migration {module} {name}';
    protected $description = 'Create a new migration file inside a module';

    public function handle()
    {
        $module = ucfirst($this->argument('module'));
        $migrationName = $this->argument('name');

        $migrationPath = base_path("Modules/{$module}/database/migrations");

        if (!File::isDirectory($migrationPath)) {
            File::makeDirectory($migrationPath, 0755, true);
        }

        // Laravel already has logic to generate correct file names
        $timestamp = now()->format('Y_m_d_His');
        $fileName = "{$timestamp}_{$migrationName}.php";
        $filePath = "{$migrationPath}/{$fileName}";

        if (File::exists($filePath)) {
            $this->error("Migration already exists: {$filePath}");
            return;
        }

        $stubPath = base_path('stubs/migration.stub');

        if (!File::exists($stubPath)) {
            $this->error("Migration stub not found at: {$stubPath}");
            return;
        }

        $stub = File::get($stubPath);

        // محاولة تخمين اسم الجدول
        $tableName = Str::snake(Str::pluralStudly(str_replace(['create_', '_table'], '', $migrationName)));

        $stub = str_replace(
            ['{{ class }}', '{{ table }}'],
            [Str::studly($migrationName), $tableName],
            $stub
        );

        File::put($filePath, $stub);

        $this->info("Migration {$fileName} created successfully in module {$module}.");
    }
}
