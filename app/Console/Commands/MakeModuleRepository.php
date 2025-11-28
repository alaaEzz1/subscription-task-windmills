<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleRepository extends Command
{
    protected $signature = 'make:module-repository 
                            {module : Module name} 
                            {name : Model name (e.g. Post)} 
                            {--bind : Bind in ServiceProvider automatically}';

    protected $description = 'Create a repository interface and implementation for a module';

    public function handle()
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));
        $interface = "{$name}RepositoryInterface";
        $repository = "{$name}Repository";

        $basePath = base_path("Modules/{$module}/src/Repositories");
        $contractPath = "{$basePath}/Contracts";
        $eloquentPath = "{$basePath}/Eloquent";

        File::makeDirectory($contractPath, 0755, true, true);
        File::makeDirectory($eloquentPath, 0755, true, true);

        $interfaceNamespace = "Modules\\{$module}\\src\\Repositories\\Contracts";
        $eloquentNamespace = "Modules\\{$module}\\src\\Repositories\\Eloquent";

        $interfaceFile = "{$contractPath}/{$interface}.php";
        $repositoryFile = "{$eloquentPath}/{$repository}.php";

        if (File::exists($interfaceFile) || File::exists($repositoryFile)) {
            $this->warn("⚠️ Repository already exists.");
            return;
        }

        // Interface
        $interfaceStub = <<<PHP
<?php

namespace {$interfaceNamespace};

interface {$interface}
{
    public function all();

    public function find(\$id);

    public function create(array \$data);

    public function update(\$id, array \$data);

    public function delete(\$id);
}
PHP;

        File::put($interfaceFile, $interfaceStub);
        $this->info("✅ Created interface: {$interfaceFile}");

        // Repository
        $repositoryStub = <<<PHP
<?php

namespace {$eloquentNamespace};

use {$interfaceNamespace}\\{$interface};
use Modules\\{$module}\\src\\Models\\{$name};

class {$repository} implements {$interface}
{
    public function all()
    {
        return {$name}::all();
    }

    public function find(\$id)
    {
        return {$name}::findOrFail(\$id);
    }

    public function create(array \$data)
    {
        return {$name}::create(\$data);
    }

    public function update(\$id, array \$data)
    {
        \$item = {$name}::findOrFail(\$id);
        \$item->update(\$data);
        return \$item;
    }

    public function delete(\$id)
    {
        return {$name}::destroy(\$id);
    }
}
PHP;

        File::put($repositoryFile, $repositoryStub);
        $this->info("✅ Created repository: {$repositoryFile}");

        // Register in ServiceProvider if --bind passed
        if ($this->option('bind')) {
            $this->registerBinding($module, $interface, $repository);
        }
    }

    protected function registerBinding($module, $interface, $repository)
    {
        $providerPath = base_path("Modules/{$module}/src/Providers/{$module}ServiceProvider.php");

        if (!File::exists($providerPath)) {
            $this->error("❌ ServiceProvider not found.");
            return;
        }

        $interfaceClass = "Modules\\{$module}\\src\\Repositories\\Contracts\\{$interface}";
        $repositoryClass = "Modules\\{$module}\\src\\Repositories\\Eloquent\\{$repository}";

        $content = File::get($providerPath);

        // Inject use statements if not already
        if (!str_contains($content, $interfaceClass)) {
            $content = preg_replace('/<\?php\n\n/', "<?php\n\nuse {$interfaceClass};\nuse {$repositoryClass};\n", $content);
        }

        // Inject bind in register()
        $binding = "\$this->app->bind({$interface}::class, {$repository}::class);";

        $content = preg_replace_callback(
            '/public function register\(\)(.*?)\{(.*?)(\n\s*)\}/s',
            fn($m) => "public function register(){{$m[2]}\n        {$binding}{$m[3]}}",
            $content
        );

        File::put($providerPath, $content);
        $this->info("🔗 Bound {$interface} to {$repository} in ServiceProvider.");
    }
}
