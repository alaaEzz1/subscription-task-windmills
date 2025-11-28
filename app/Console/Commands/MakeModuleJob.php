<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleJob extends Command
{
    protected $signature = 'make:module-job 
                            {module : Module name} 
                            {name : Job class name (e.g. PublishPostJob)} 
                            {--sync : Create a synchronous (non-queued) job}';

    protected $description = 'Create a job class inside a specific module';

    public function handle()
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));
        $path = base_path("Modules/{$module}/src/Jobs");

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
            $this->info("📁 Created directory: {$path}");
        }

        $file = "{$path}/{$name}.php";

        if (File::exists($file)) {
            $this->warn("⚠️ Job already exists: {$file}");
            return;
        }

        $namespace = "Modules\\{$module}\\src\\Jobs";
        $stubPath = base_path('stubs/module-job.stub');
        if ($this->option('sync')) {
            $stubPath = base_path('stubs/module-job-sync.stub');
        }

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
        $this->info("✅ Job {$name} created at: {$file}");
    }
}
