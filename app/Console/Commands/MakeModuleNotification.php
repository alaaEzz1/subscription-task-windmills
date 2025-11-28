<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleNotification extends Command
{
    protected $signature = 'make:module-notification 
                            {module : Module name} 
                            {name : Notification class name (e.g. NewPostNotification)}';

    protected $description = 'Create a new notification class inside a specific module';

    public function handle()
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));
        $path = base_path("Modules/{$module}/src/Notifications");

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
            $this->info("📁 Created directory: {$path}");
        }

        $file = "{$path}/{$name}.php";

        if (File::exists($file)) {
            $this->warn("⚠️ Notification already exists: {$file}");
            return;
        }

        $namespace = "Modules\\{$module}\\src\\Notifications";
        $stubPath = base_path('stubs/module-notification.stub');

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
        $this->info("✅ Notification {$name} created at: {$file}");
    }
}
