<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleMiddleware extends Command
{
    protected $signature = 'make:module-middleware
                            {module : Module name}
                            {name : Middleware class name (e.g. CheckIfAdmin)}
                            {--alias= : Optional alias to register (e.g. admin)}
                            {--group= : Optional middleware group (e.g. web)}';

    protected $description = 'Create a middleware class inside a specific module and optionally register it';

    public function handle()
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));
        $alias = $this->option('alias');
        $group = $this->option('group');

        $path = base_path("Modules/{$module}/src/Http/Middleware");

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
            $this->info("📁 Created directory: {$path}");
        }

        $file = "{$path}/{$name}.php";

        if (File::exists($file)) {
            $this->warn("⚠️ Middleware already exists: {$file}");
            return;
        }

        $namespace = "Modules\\{$module}\\src\\Http\\Middleware";
        $stubPath = base_path('stubs/module-middleware.stub');

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
        $this->info("✅ Middleware {$name} created at: {$file}");

        if ($alias || $group) {
            $this->registerInRouteServiceProvider($module, $name, $alias, $group);
        }
    }

    protected function registerInRouteServiceProvider($module, $class, $alias = null, $group = null)
    {
        $providerPath = base_path("Modules/{$module}/src/Providers/RouteServiceProvider.php");
        $fqcn = "Modules\\{$module}\\src\\Http\\Middleware\\{$class}";

        if (!File::exists($providerPath)) {
            $this->warn("⚠️ RouteServiceProvider not found. Skipping middleware registration.");
            return;
        }

        $content = File::get($providerPath);

        // Add use statement
        if (!str_contains($content, "use {$fqcn};")) {
            $content = preg_replace('/<\?php\n\n/', "<?php\n\nuse {$fqcn};\n", $content, 1);
        }

        // Add middleware registration
        if ($alias) {
            $aliasCode = "\$router->aliasMiddleware('{$alias}', {$class}::class);";
        } elseif ($group) {
            $aliasCode = "\$router->pushMiddlewareToGroup('{$group}', {$class}::class);";
        } else {
            return;
        }

        if (!str_contains($content, $aliasCode)) {
            $content = preg_replace(
                '/public function boot\(\).*?\{/',
                "public function boot()\n    {\n        \$router = \$this->app['router'];\n        {$aliasCode}",
                $content
            );
        }

        File::put($providerPath, $content);
        $this->info("🔗 Middleware registered in RouteServiceProvider");
    }
}
