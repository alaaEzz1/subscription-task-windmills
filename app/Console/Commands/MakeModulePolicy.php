<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModulePolicy extends Command
{
    protected $signature = 'make:module-policy {module} {name} {--model=} {--register}';
    protected $description = 'Create a new policy class inside a specific module (with optional model binding and registration)';

    public function handle()
    {
        $module = ucfirst($this->argument('module'));
        $policyClass = Str::studly($this->argument('name')) . 'Policy';
        $model = $this->option('model');
        $register = $this->option('register');

        $policyPath = base_path("Modules/{$module}/src/Policies/{$policyClass}.php");
        $namespace = "Modules\\{$module}\\src\\Policies";

        // تأكد من وجود مجلد الـ Policies
        if (!File::isDirectory(dirname($policyPath))) {
            File::makeDirectory(dirname($policyPath), 0755, true);
        }

        if (File::exists($policyPath)) {
            $this->error("Policy already exists: {$policyPath}");
            return;
        }

        $stubPath = base_path('stubs/policy.stub');
        if (!File::exists($stubPath)) {
            $this->error("Stub file not found: {$stubPath}");
            return;
        }

        $stub = File::get($stubPath);
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ modelImport }}', '{{ model }}', '{{ modelVariable }}'],
            [
                $namespace,
                $policyClass,
                $model ? "use Modules\\{$module}\\src\\Models\\{$model};" : '',
                $model ?? 'Model',
                $model ? Str::camel($model) : 'model'
            ],
            $stub
        );

        File::put($policyPath, $stub);
        $this->info("✅ Policy {$policyClass} created successfully in module {$module}.");

        // إذا طلب المستخدم تسجيلها تلقائيًا
        if ($register && $model) {
            $this->registerPolicy($module, $model, $policyClass);
        } elseif ($register && !$model) {
            $this->warn("⚠️ Cannot register policy without a model. Use --model=ModelName");
        }
    }

    protected function registerPolicy(string $module, string $model, string $policyClass): void
    {
        $authPath = app_path('Providers/AuthServiceProvider.php');
        if (!File::exists($authPath)) {
            $this->warn("⚠️ AuthServiceProvider not found.");
            return;
        }

        $content = File::get($authPath);
        $modelFQN = "Modules\\{$module}\\src\\Models\\{$model}";
        $policyFQN = "Modules\\{$module}\\src\\Policies\\{$policyClass}";

        // Add use statements if missing
        if (!str_contains($content, "use {$modelFQN};")) {
            $content = preg_replace(
                '/namespace App\\\\Providers;/',
                "namespace App\Providers;\n\nuse {$modelFQN};\nuse {$policyFQN};",
                $content
            );
        }

        // Add to $policies array
        if (!str_contains($content, "{$modelFQN}::class")) {
            $content = preg_replace(
                '/protected \$policies\s+=\s+\[([\s\S]*?)\];/',
                "protected \$policies = [\n        {$modelFQN}::class => {$policyFQN}::class,\n$1];",
                $content
            );

            File::put($authPath, $content);
            $this->info("🔐 Policy registered in AuthServiceProvider.");
        } else {
            $this->warn("⚠️ Policy already registered or similar entry exists.");
        }
    }
}
