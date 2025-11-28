<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleFactory extends Command
{
    protected $signature = 'make:module-factory {module} {model}';
    protected $description = 'Create a new model factory inside a module';

    public function handle()
    {
        $module = ucfirst($this->argument('module'));
        $model = Str::studly($this->argument('model'));
        $factoryName = "{$model}Factory";
        $factoryNamespace = "Modules\\{$module}\\database\\factories";
        $modelNamespace = "Modules\\{$module}\\src\\Models\\{$model}";
        $factoryPath = base_path("Modules/{$module}/database/factories/{$factoryName}.php");
        $stubPath = base_path('stubs/factory.stub');

        // التأكد من وجود المسار
        if (!File::isDirectory(dirname($factoryPath))) {
            File::makeDirectory(dirname($factoryPath), 0755, true);
        }

        if (File::exists($factoryPath)) {
            $this->error("Factory already exists: {$factoryName}");
            return;
        }

        if (!File::exists($stubPath)) {
            $this->error("Factory stub not found at: {$stubPath}");
            return;
        }

        $stub = File::get($stubPath);
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ model }}'],
            [$factoryNamespace, $factoryName, $modelNamespace],
            $stub
        );

        File::put($factoryPath, $stub);

        $this->info("Factory {$factoryName} created successfully in module {$module}.");
    }
}
