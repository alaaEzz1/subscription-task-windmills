<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleTranslation extends Command
{
    protected $signature = 'make:module-translation 
        {module : The name of the module} 
        {--lang=ar,en : Comma-separated list of languages (e.g. ar,en)} 
        {--format=json : Translation format (json or php)}';

    protected $description = 'Create translation files for a specific module in either JSON or PHP format';

    public function handle()
    {
        $module = ucfirst($this->argument('module'));
        $langs = explode(',', $this->option('lang'));
        $format = strtolower($this->option('format'));

        if (!in_array($format, ['json', 'php'])) {
            $this->error("❌ Invalid format. Please use --format=json or --format=php.");
            return;
        }

        $baseLangPath = base_path("Modules/{$module}/resources/lang");

        foreach ($langs as $lang) {
            $lang = trim($lang);

            if ($format === 'json') {
                $this->generateJsonTranslation($baseLangPath, $lang);
            } else {
                $this->generatePhpTranslation($baseLangPath, $lang);
            }
        }

        $this->info("🎉 Translation files generated successfully for module '{$module}'.");
    }

    protected function generateJsonTranslation(string $basePath, string $lang): void
    {
        if (!File::isDirectory($basePath)) {
            File::makeDirectory($basePath, 0755, true);
        }

        $filePath = "{$basePath}/{$lang}.json";

        if (!File::exists($filePath)) {
            File::put($filePath, json_encode([
                "example.key" => "Example translation for {$lang}"
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $this->info("✅ Created JSON translation file: {$filePath}");
        } else {
            $this->warn("⚠️ JSON file already exists: {$filePath}");
        }
    }

    protected function generatePhpTranslation(string $basePath, string $lang): void
    {
        $langDir = "{$basePath}/{$lang}";

        if (!File::isDirectory($langDir)) {
            File::makeDirectory($langDir, 0755, true);
        }

        $filePath = "{$langDir}/messages.php";

        if (!File::exists($filePath)) {
            $stub = <<<PHP
<?php

return [
    'example' => 'Example translation for {$lang}',
];
PHP;
            File::put($filePath, $stub);
            $this->info("✅ Created PHP translation file: {$filePath}");
        } else {
            $this->warn("⚠️ PHP translation file already exists: {$filePath}");
        }
    }
}
