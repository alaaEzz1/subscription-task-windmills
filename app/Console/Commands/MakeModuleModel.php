<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleModel extends Command
{
    protected $signature = 'make:module-model {module} {name} {--m}';
    protected $description = 'Create a new model in a specific module';

    public function handle()
    {
        $module = ucfirst($this->argument('module'));
        $name = ucfirst($this->argument('name'));

        $modelName = $name;
        $modelNamespace = "Modules\\{$module}\\src\\Models";
        $modelPath = base_path("Modules/{$module}/src/Models/{$modelName}.php");
        $stubPath = base_path('stubs/model.stub');

        // تأكد أن المسار موجود
        if (!File::isDirectory(dirname($modelPath))) {
            File::makeDirectory(dirname($modelPath), 0755, true);
        }

        if (File::exists($modelPath)) {
            $this->error("Model {$modelName} already exists in module {$module}.");
            return;
        }

        if (!File::exists($stubPath)) {
            $this->error("Stub file not found at: {$stubPath}");
            return;
        }

        // استبدال القيم في القالب
        $stub = File::get($stubPath);
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$modelNamespace, $modelName],
            $stub
        );

        File::put($modelPath, $stub);
        $this->info("Model {$modelName} created successfully in module {$module}.");

        // إنشاء migration تلقائي إذا تم استخدام -m
        if ($this->option('m')) {
            $migrationName = 'create_' . Str::snake(Str::pluralStudly($modelName)) . '_table';

            $this->call('make:module-migration', [
                'module' => $module,
                'name' => $migrationName,
            ]);
        }
    }
}
