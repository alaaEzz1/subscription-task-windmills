<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleListener extends Command
{
    protected $signature = 'make:module-listener 
                            {module : Module name} 
                            {name : Listener class name} 
                            {--event= : Event class the listener handles}';

    protected $description = 'Create a listener class inside a specific module';

    public function handle()
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));
        $event = $this->option('event');

        $path = base_path("Modules/{$module}/src/Listeners");

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
            $this->info("📁 Created directory: {$path}");
        }

        $file = "{$path}/{$name}.php";
        if (File::exists($file)) {
            $this->warn("⚠️ Listener already exists: {$file}");
            return;
        }

        $namespace = "Modules\\{$module}\\src\\Listeners";
        $stubPath = base_path('stubs/module-listener.stub');

        if (!File::exists($stubPath)) {
            $this->error("❌ Stub not found: {$stubPath}");
            return;
        }

        $stub = File::get($stubPath);
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ event }}'],
            [$namespace, $name, $event ?? ''],
            $stub
        );

        File::put($file, $stub);
        $this->info("✅ Listener {$name} created at: {$file}");

        if ($event) {
            $this->registerInEventServiceProvider($module, $event, $name);
        }
    }

    protected function registerInEventServiceProvider(string $module, string $event, string $listener)
    {
        $providerPath = base_path("Modules/{$module}/src/Providers/EventServiceProvider.php");
        $providerNamespace = "Modules\\{$module}\\src\\Providers";

        if (!File::exists($providerPath)) {
            File::put($providerPath, $this->generateEventServiceProvider($providerNamespace));
            $this->info("📦 EventServiceProvider created for module {$module}");
        }

        $content = File::get($providerPath);

        $eventFQCN = '\\' . ltrim($event, '\\');
        $listenerFQCN = "Modules\\{$module}\\src\\Listeners\\{$listener}";

        // Add use statements
        foreach ([$eventFQCN, $listenerFQCN] as $fqcn) {
            if (!str_contains($content, "use {$fqcn};")) {
                $content = preg_replace('/<\?php\s+namespace.*?;/s', "$0\n\nuse {$fqcn};", $content, 1);
            }
        }

        // Register in the listens array
        $listensPattern = '/protected \$listen\s*=\s*\[(.*?)\];/s';
        if (preg_match($listensPattern, $content, $matches)) {
            if (!str_contains($matches[1], $eventFQCN)) {
                $replacement = <<<PHP
protected \$listen = [
        {$eventFQCN}::class => [
            {$listenerFQCN}::class,
        ],
    ];
PHP;
                $content = preg_replace($listensPattern, $replacement, $content);
            }
        }

        File::put($providerPath, $content);
        $this->info("🔗 Listener registered in EventServiceProvider");
    }

    protected function generateEventServiceProvider($namespace): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected \$listen = [
        //
    ];
}
PHP;
    }
}
