<?php

// app/Console/Commands/MakeModule.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModule extends Command
{
    protected $signature = 'make:module {name}';
    protected $description = 'Create a new module in the Modules directory';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $basePath = base_path("Modules/{$name}");

        if (File::exists($basePath)) {
            $this->error("Module {$name} already exists!");
            return;
        }

        // Define structure
        $structure = [
            '/src/Http/Controllers',
            '/src/Models',
            '/src/Providers',
            '/config',
            '/database/factories',
            '/database/migrations',
            '/database/seeders',
            '/resources/assets/js',
            '/resources/assets/sass',
            '/resources/views',
            '/routes',
            '/tests/Feature',
            '/tests/Unit',
        ];

        // Create directories
        foreach ($structure as $dir) {
            File::makeDirectory("{$basePath}{$dir}", 0755, true);
        }

        // Create base files
        File::put("{$basePath}/module.json", json_encode([
            'name' => $name,
            'namespace' => "Modules\\{$name}\\src",
            'provider' => "Modules\\{$name}\\src\\Providers\\{$name}ServiceProvider",
        ], JSON_PRETTY_PRINT));


        File::put("{$basePath}/routes/web.php", "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\nRoute::get('/{$name}', fn() => 'Hello from {$name} Module!');");

        File::put("{$basePath}/resources/views/index.blade.php", "<h1>{$name} Module</h1>");
        File::put("{$basePath}/src/Providers/{$name}ServiceProvider.php", $this->getServiceProviderStub($name));
        File::put("{$basePath}/vite.config.js", "// Vite config for {$name} module");

        $this->info("Module {$name} created successfully at Modules/{$name}");
    }

    protected function getServiceProviderStub($name)
    {
        return <<<PHP
<?php

namespace Modules\\{$name}\\src\\Providers;

use Illuminate\Support\ServiceProvider;

class {$name}ServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register bindings here
    }

    public function boot()
    {
        \$modulePath = base_path('Modules/{$name}');

        // Load routes
        \$this->loadRoutesFrom("\$modulePath/routes/web.php");

        // Load views
        \$this->loadViewsFrom("\$modulePath/resources/views", '{$name}');

        // Load migrations
        \$this->loadMigrationsFrom("\$modulePath/database/migrations");
    }
}
PHP;
    }
}
