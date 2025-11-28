<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleChannel extends Command
{
    protected $signature = 'make:module-channel 
                            {module : Module name} 
                            {name : Channel class name (e.g. SmsChannel)}';

    protected $description = 'Create a custom notification channel inside a specific module';

    public function handle()
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));
        $path = base_path("Modules/{$module}/src/Channels");

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
            $this->info("📁 Created directory: {$path}");
        }

        $file = "{$path}/{$name}.php";

        if (File::exists($file)) {
            $this->warn("⚠️ Channel already exists: {$file}");
            return;
        }

        $namespace = "Modules\\{$module}\\src\\Channels";
        $stubPath = base_path('stubs/module-channel.stub');

        if (!File::exists($stubPath)) {
            $this->error("❌ Stub file not found: {$stubPath}");
            return;
        }

        $stub = File::get($stubPath);
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$namespace, $name],
            $stub
        );

        File::put($file, $stub);
        $this->info("✅ Channel {$name} created at: {$file}");
    }
}
