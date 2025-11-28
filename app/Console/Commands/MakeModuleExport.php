<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

class MakeModuleExport extends GeneratorCommand
{
    protected $name = 'make:module-export';
    protected $description = 'Create a new export class in the specified module';
    protected $type = 'Export';

    protected string $moduleName = '';

    protected function getStub(): string
    {
        return base_path('stubs/module-export.stub');
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the Export class (ModuleName/ClassName)'],
        ];
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return "$rootNamespace\\Modules\\{$this->moduleName}\\Exports";
    }

    protected function qualifyClass($name): string
    {
        $name = str_replace('/', '\\', $name);

        if (Str::contains($name, '\\')) {
            [$this->moduleName, $className] = explode('\\', $name, 2);
        } else {
            $this->moduleName = $name;
            $className = $name;
        }

        return $this->getDefaultNamespace('App') . '\\' . $className;
    }

    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $this->qualifyClass($name));
        return base_path(str_replace('\\', '/', $name) . '.php');
    }
}
