<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module-command 
                            {module : Module name} 
                            {name : Command class name (e.g. SyncPostsCommand)} 
                            {--signature= : Artisan command signature (e.g. blog:sync-posts)}';

    protected $description = 'Create an Artisan command inside a specific module';

    public function handle()
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));
        $signatureOption = $this->option('signature') ?? strtolower($module) . ':' . Str::kebab(str_replace('Command', '', $name));

        $path = base_path("Modules/{$module}/src/Console/Commands");

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
            $this->info("📁 Created directory: {$path}");
        }

        $file = "{$path}/{$name}.php";

        if (File::exists($file)) {
            $this->warn("⚠️ Command already exists: {$file}");
            return;
        }

        $namespace = "Modules\\{$module}\\src\\Console\\Commands";

        $stubPath = base_path('stubs/module-command.stub');
        if (!File::exists($stubPath)) {
            $this->error("❌ Stub not found: {$stubPath}");
            return;
        }

        $stub = File::get($stubPath);
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ signature }}'],
            [$namespace, $name, $signatureOption],
            $stub
        );

        File::put($file, $stub);
        $this->info("✅ Command {$name} created at: {$file}");

        $this->registerInProvider($module, $name);
    }

    protected function registerInProvider($module, $class)
    {
        $providerPath = base_path("Modules/{$module}/src/Providers/{$module}ServiceProvider.php");
        $commandClass = "Modules\\{$module}\\src\\Console\\Commands\\{$class}";

        if (!File::exists($providerPath)) {
            $this->warn("⚠️ ServiceProvider not found. Skipping command registration.");
            return;
        }

        $content = File::get($providerPath);

        // Add use statement
        if (!str_contains($content, $commandClass)) {
            $content = preg_replace('/<\?php\n\n/', "<?php\n\nuse {$commandClass};\n", $content);
        }

        // Register command in boot method
        if (!str_contains($content, "\$this->commands([")) {
            $content = preg_replace(
                '/public function boot\(\)\s*\{/',
                "public function boot()\n    {\n        \$this->commands([\n            {$class}::class,\n        ]);",
                $content
            );
        } else {
            $content = preg_replace_callback('/\$this->commands\(\[(.*?)\]\);/s', function ($matches) use ($class) {
                if (str_contains($matches[1], "{$class}::class")) {
                    return $matches[0]; // already added
                }
                return "\$this->commands([\n            {$matches[1]},\n            {$class}::class\n        ]);";
            }, $content);
        }

        File::put($providerPath, $content);
        $this->info("🔗 Command registered in {$module}ServiceProvider");
    }
}
