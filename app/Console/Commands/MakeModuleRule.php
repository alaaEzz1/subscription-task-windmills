<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleRule extends Command
{
    protected $signature = 'make:module-rule 
                            {module : Module name} 
                            {name : Rule name (e.g. ValidTitle)}';

    protected $description = 'Create a custom validation rule class inside a specific module';

    public function handle()
    {
        $module = Str::studly($this->argument('module'));
        $ruleName = Str::studly($this->argument('name'));

        $ruleClass = "{$ruleName}";
        $rulesPath = base_path("Modules/{$module}/src/Rules");

        if (!File::isDirectory($rulesPath)) {
            File::makeDirectory($rulesPath, 0755, true);
            $this->info("📁 Created directory: {$rulesPath}");
        }

        $filePath = "{$rulesPath}/{$ruleClass}.php";

        if (File::exists($filePath)) {
            $this->warn("⚠️ Rule already exists: {$filePath}");
            return;
        }

        $namespace = "Modules\\{$module}\\src\\Rules";

        $stub = <<<PHP
<?php

namespace {$namespace};

use Illuminate\Contracts\Validation\Rule;

class {$ruleClass} implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  \$attribute
     * @param  mixed  \$value
     * @return bool
     */
    public function passes(\$attribute, \$value): bool
    {
        // Example: title must start with an uppercase letter
        return preg_match('/^[A-Z]/', \$value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute must start with an uppercase letter.';
    }
}
PHP;

        File::put($filePath, $stub);
        $this->info("✅ Custom Rule {$ruleClass} created at: {$filePath}");
    }
}
