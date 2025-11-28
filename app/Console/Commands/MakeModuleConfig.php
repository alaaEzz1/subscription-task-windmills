<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleConfig extends Command
{
    protected $signature = 'make:module-config 
                            {module : The name of the module} 
                            {--file=config : Config filename (without .php, default: config)}';

    protected $description = 'Create a config file for a specific module and auto-load it in the ServiceProvider';

    public function handle()
    {
        $module = ucfirst($this->argument('module'));
        $file = Str::lower($this->option('file') ?? 'config');

        $moduleBasePath = base_path("Modules/{$module}");
        $configDir = "{$moduleBasePath}/config";
        $configFilePath = "{$configDir}/{$file}.php";
        $providerPath = "{$moduleBasePath}/src/Providers/{$module}ServiceProvider.php";

        // 1. Create config directory if needed
        if (!File::isDirectory($configDir)) {
            File::makeDirectory($configDir, 0755, true);
            $this->info("📁 Created directory: {$configDir}");
        }

        // 2. Create config file if not exists
        if (File::exists($configFilePath)) {
            $this->warn("⚠️ Config file already exists: {$configFilePath}");
        } else {
            $stub = <<<PHP
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | {$module} Module Configuration
    |--------------------------------------------------------------------------
    |
    | Define the custom settings for your {$module} module here.
    |
    */

    'enabled' => true,
];
PHP;

            File::put($configFilePath, $stub);
            $this->info("✅ Config file created: {$configFilePath}");
        }

        // 3. Register config inside ServiceProvider
        if (!File::exists($providerPath)) {
            $this->warn("⚠️ Provider not found: {$providerPath}");
            return;
        }

        $providerContent = File::get($providerPath);
        $mergeCode = "\$this->mergeConfigFrom(\"{\$modulePath}/config/{$file}.php\", strtolower('{$module}'));";

        if (!str_contains($providerContent, $mergeCode)) {
            // inject the mergeConfigFrom in boot()
            $updatedProvider = preg_replace_callback(
                '/public function boot\(\)(.*?)\{(.*?)(\n\s*)\}/s',
                function ($matches) use ($mergeCode) {
                    return "public function boot(){{$matches[2]}\n        {$mergeCode}{$matches[3]}}";
                },
                $providerContent
            );

            if ($updatedProvider !== null) {
                File::put($providerPath, $updatedProvider);
                $this->info("🧩 Registered config in ServiceProvider.");
            } else {
                $this->warn("⚠️ Could not inject mergeConfigFrom into the ServiceProvider.");
            }
        } else {
            $this->warn("⚠️ Config already registered in ServiceProvider.");
        }

        $this->info("🎯 Done.");
    }
}
