<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleTest extends Command
{
    protected $signature = 'make:module-test 
                            {module : Module name} 
                            {name : Test class name (e.g. PostTest)} 
                            {--unit : Create a unit test (default: feature)}';

    protected $description = 'Create a test class inside a specific module (Feature or Unit)';

    public function handle()
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));
        $type = $this->option('unit') ? 'Unit' : 'Feature';

        $path = base_path("Modules/{$module}/tests/{$type}");

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
            $this->info("📁 Created directory: {$path}");
        }

        $file = "{$path}/{$name}.php";

        if (File::exists($file)) {
            $this->warn("⚠️ Test already exists: {$file}");
            return;
        }

        $namespace = "Modules\\{$module}\\tests\\{$type}";
        $stubPath = base_path("stubs/module-test.stub");

        if (!File::exists($stubPath)) {
            $this->error("❌ Stub not found: {$stubPath}");
            return;
        }

        $stub = File::get($stubPath);
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$namespace, $name],
            $stub
        );

        File::put($file, $stub);
        $this->info("✅ {$type} Test {$name} created at: {$file}");
    }
}
