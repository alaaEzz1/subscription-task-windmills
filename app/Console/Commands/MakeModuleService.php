<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleService extends Command
{
    protected $signature = 'make:module-service {module} {name}';
    protected $description = 'Create a new service class inside a specific module';

    public function handle()
    {
        $module = ucfirst($this->argument('module'));
        $name = Str::studly($this->argument('name'));
        $serviceClass = "{$name}Service";

        $servicePath = base_path("Modules/{$module}/src/Services");
        $serviceFile = "{$servicePath}/{$serviceClass}.php";

        if (!File::isDirectory($servicePath)) {
            File::makeDirectory($servicePath, 0755, true);
        }

        if (File::exists($serviceFile)) {
            $this->error("❌ Service {$serviceClass} already exists in module {$module}.");
            return;
        }

        $namespace = "Modules\\{$module}\\src\\Services";

        $stub = <<<PHP
<?php

namespace {$namespace};

class {$serviceClass}
{
    public function __construct()
    {
        //
    }

    // Add your service logic here
}
PHP;

        File::put($serviceFile, $stub);

        $this->info("✅ Service {$serviceClass} created successfully in module {$module}.");
    }
}
