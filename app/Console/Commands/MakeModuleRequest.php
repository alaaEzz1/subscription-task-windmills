<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleRequest extends Command
{
    protected $signature = 'make:module-request 
                            {module : Module name} 
                            {name : The name of the request class (e.g. Post)}';

    protected $description = 'Create a form request class inside a specific module';

    public function handle()
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));
        $requestClass = "{$name}Request";
        $requestPath = base_path("Modules/{$module}/src/Http/Requests");

        if (!File::isDirectory($requestPath)) {
            File::makeDirectory($requestPath, 0755, true);
            $this->info("📁 Created directory: {$requestPath}");
        }

        $filePath = "{$requestPath}/{$requestClass}.php";

        if (File::exists($filePath)) {
            $this->warn("⚠️ Request already exists: {$filePath}");
            return;
        }

        $namespace = "Modules\\{$module}\\src\\Http\\Requests";

        $stub = <<<PHP
<?php

namespace {$namespace};

use Illuminate\Foundation\Http\FormRequest;

class {$requestClass} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Update with authorization logic if needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ];
    }
}
PHP;

        File::put($filePath, $stub);
        $this->info("✅ Created Request: {$filePath}");
    }
}
