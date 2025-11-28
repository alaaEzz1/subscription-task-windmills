<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeModuleResource extends Command
{
    protected $signature = 'make:module-resource {module} {name}';
    protected $description = 'Generate model, controller, migration, factory, view for a module';

    public function handle()
    {
        $module = ucfirst($this->argument('module'));
        $name = ucfirst($this->argument('name'));

        $this->line("📦 Generating resources for module: {$module}, name: {$name}");

        // Generate model
        $this->call('make:module-model', [
            'module' => $module,
            'name' => $name,
        ]);

        // Generate controller
        $this->call('make:module-controller', [
            'module' => $module,
            'name' => $name,
        ]);

        // Generate migration
        $tableName = \Illuminate\Support\Str::snake(\Illuminate\Support\Str::pluralStudly($name));
        $migrationName = "create_{$tableName}_table";
        $this->call('make:module-migration', [
            'module' => $module,
            'name' => $migrationName,
        ]);

        // Generate factory
        $this->call('make:module-factory', [
            'module' => $module,
            'model' => $name,
        ]);

        // Generate basic views (index + create + edit)
        foreach (['index', 'create', 'edit', 'show'] as $view) {
            $this->call('make:module-view', [
                'module' => $module,
                'name' => \Illuminate\Support\Str::kebab($name) . '.' . $view,
            ]);
        }

        // Optional: you can auto-create a seeder if needed
        // $this->call('make:module-seeder', [
        //     'module' => $module,
        //     'name' => "{$name}Seeder",
        // ]);

        $this->info("✅ All resources for {$module}::{$name} generated successfully.");
    }
}
