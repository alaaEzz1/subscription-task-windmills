<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleEvent extends Command
{
    protected $signature = 'make:module-event 
                            {module : Module name} 
                            {name : Event class name (e.g. PostPublished)} 
                            {--broadcast : Make this event broadcastable}';

    protected $description = 'Create a custom event class inside a specific module';

    public function handle()
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));
        $path = base_path("Modules/{$module}/src/Events");

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
            $this->info("📁 Created directory: {$path}");
        }

        $file = "{$path}/{$name}.php";

        if (File::exists($file)) {
            $this->warn("⚠️ Event already exists: {$file}");
            return;
        }

        $namespace = "Modules\\{$module}\\src\\Events";
        $stubPath = $this->option('broadcast')
            ? base_path('stubs/module-event-broadcast.stub')
            : base_path('stubs/module-event.stub');

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
        $this->info("✅ Event {$name} created at: {$file}");
    }
}
