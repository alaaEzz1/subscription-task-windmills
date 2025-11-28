<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleObserver extends Command
{
    protected $signature = 'make:module-observer 
                            {module : Module name} 
                            {name : Model name to observe} 
                            {--register : Auto-register in ServiceProvider}';

    protected $description = 'Create an observer class inside a module and optionally register it';

    public function handle()
    {
        $module = Str::studly($this->argument('module'));
        $model = Str::studly($this->argument('name'));
        $observerClass = "{$model}Observer";

        $observerPath = base_path("Modules/{$module}/src/Observers");
        $observerFile = "{$observerPath}/{$observerClass}.php";
        $stubPath = base_path('stubs/module-observer.stub');
        $providerPath = base_path("Modules/{$module}/src/Providers/{$module}ServiceProvider.php");

        // إنشاء المجلد إذا مش موجود
        if (!File::isDirectory($observerPath)) {
            File::makeDirectory($observerPath, 0755, true);
            $this->info("📁 Created: {$observerPath}");
        }

        // إيقاف لو الملف موجود
        if (File::exists($observerFile)) {
            $this->warn("⚠️ Observer already exists: {$observerFile}");
            return;
        }

        // قراءة القالب
        if (!File::exists($stubPath)) {
            $this->error("❌ Stub file missing at: {$stubPath}");
            return;
        }

        $stub = File::get($stubPath);
        $stub = str_replace(
            ['{{ namespace }}', '{{ observer }}', '{{ model }}', '{{ module }}'],
            ["Modules\\{$module}\\src\\Observers", $observerClass, $model, $module],
            $stub
        );

        File::put($observerFile, $stub);
        $this->info("✅ Created Observer: {$observerFile}");

        // تسجيل تلقائي في ServiceProvider
        if ($this->option('register') && File::exists($providerPath)) {
            $this->registerObserverInProvider($providerPath, $module, $model, $observerClass);
        }
    }

    protected function registerObserverInProvider($providerPath, $module, $model, $observerClass)
    {
        $providerContent = File::get($providerPath);

        $modelFull = "Modules\\{$module}\\src\\Models\\{$model}";
        $observerFull = "Modules\\{$module}\\src\\Observers\\{$observerClass}";

        // التأكد من عدم تكرار التسجيل
        if (str_contains($providerContent, "{$model}::observe({$observerClass}::class)")) {
            $this->warn("⚠️ Observer already registered.");
            return;
        }

        // إضافة use statements لو مش موجودة
        if (!str_contains($providerContent, "use {$modelFull};")) {
            $providerContent = preg_replace('/<\?php\n\n/', "<?php\n\nuse {$modelFull};\nuse {$observerFull};\n", $providerContent, 1);
        }

        // حقن الكود داخل boot()
        $inject = "{$model}::observe({$observerClass}::class);";

        $providerContent = preg_replace_callback(
            '/public function boot\(\)(.*?)\{(.*?)(\n\s*)\}/s',
            fn($m) => "public function boot(){{$m[2]}\n        {$inject}{$m[3]}}",
            $providerContent
        );

        File::put($providerPath, $providerContent);
        $this->info("🧩 Registered observer in ServiceProvider.");
    }
}
