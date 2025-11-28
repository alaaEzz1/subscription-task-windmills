<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MakeModuleController extends Command
{
    protected $signature = 'make:module-controller {module} {name}';
    protected $description = 'Create a new controller inside a module';

    public function handle()
    {
        $module = ucfirst($this->argument('module'));
        $name = ucfirst($this->argument('name'));
        $controllerName = "{$name}Controller";
        $controllerNamespace = "Modules\\{$module}\\src\\Http\\Controllers";
        $stubPath = base_path('stubs/controller.stub');

        $destinationDir = base_path("Modules/{$module}/src/Http/Controllers");
        $destinationPath = "{$destinationDir}/{$controllerName}.php";

        // تأكد أن المجلد موجود
        if (!File::isDirectory($destinationDir)) {
            File::makeDirectory($destinationDir, 0755, true);
        }

        // لا تنشئ لو الملف موجود
        if (File::exists($destinationPath)) {
            $this->error("Controller {$controllerName} already exists.");
            return;
        }

        // جلب الـ stub
        if (!File::exists($stubPath)) {
            $this->error("Stub file not found at: {$stubPath}");
            return;
        }

        $stub = File::get($stubPath);

        // استبدال القوالب
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$controllerNamespace, $controllerName],
            $stub
        );

        // إنشاء الملف النهائي
        File::put($destinationPath, $stub);
        $this->info("Controller {$controllerName} created successfully at: Modules/{$module}/src/Http/Controllers");
    }
}
