<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleMail extends Command
{
    protected $signature = 'make:module-mail {module} {name}';
    protected $description = 'Create a new mailable class inside a module';

    public function handle()
    {
        $module = ucfirst($this->argument('module'));
        $mailClass = Str::studly($this->argument('name'));
        $namespace = "Modules\\{$module}\\src\\Mail";
        $mailPath = base_path("Modules/{$module}/src/Mail/{$mailClass}.php");
        $stubPath = base_path('stubs/mail.stub');

        if (!File::isDirectory(dirname($mailPath))) {
            File::makeDirectory(dirname($mailPath), 0755, true);
        }

        if (File::exists($mailPath)) {
            $this->error("Mail class already exists: {$mailPath}");
            return;
        }

        if (!File::exists($stubPath)) {
            $this->error("Mail stub not found at: {$stubPath}");
            return;
        }

        $stub = File::get($stubPath);
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$namespace, $mailClass],
            $stub
        );

        File::put($mailPath, $stub);

        $this->info("Mailable {$mailClass} created successfully in module {$module}.");
    }
}
