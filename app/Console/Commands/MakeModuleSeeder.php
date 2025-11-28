<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleSeeder extends Command
{
    protected $signature = 'make:module-seeder {module} {name}';
    protected $description = 'Create a new seeder file inside a module';

    public function handle()
    {
        $module = ucfirst($this->argument('module'));
        $seederName = Str::studly($this->argument('name'));
        $namespace = "Modules\\{$module}\\database\\seeders";
        $seederPath = base_path("Modules/{$module}/database/seeders/{$seederName}.php");
        $stubPath = base_path('stubs/seeder.stub');

        if (!File::isDirectory(dirname($seederPath))) {
            File::makeDirectory(dirname($seederPath), 0755, true);
        }

        if (File::exists($seederPath)) {
            $this->error("Seeder already exists: {$seederName}");
            return;
        }

        if (!File::exists($stubPath)) {
            $this->error("Seeder stub not found at: {$stubPath}");
            return;
        }

        $stub = File::get($stubPath);
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$namespace, $seederName],
            $stub
        );

        File::put($seederPath, $stub);

        $this->info("Seeder {$seederName} created successfully in module {$module}.");
    }
}
