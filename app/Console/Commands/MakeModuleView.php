<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeModuleView extends Command
{
    protected $signature = 'make:module-view {module} {name}';
    protected $description = 'Create a new blade view file inside a module';

    public function handle()
    {
        $module = ucfirst($this->argument('module'));
        $viewName = $this->argument('name');

        $viewPath = base_path("Modules/{$module}/resources/views/" . str_replace('.', '/', $viewName) . ".blade.php");

        if (File::exists($viewPath)) {
            $this->error("View already exists: {$viewPath}");
            return;
        }

        $dir = dirname($viewPath);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        File::put($viewPath, "<!-- View: {$viewName} -->");

        $this->info("View {$viewName} created successfully at: {$viewPath}");
    }
}
