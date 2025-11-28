<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class ModuleServiceRegistrar
{
    /**
     * Register all module service providers found in Modules module.json
     *
     * @return array
     */
    public static function registerAll(): array
    {
        $modulesPath = base_path('Modules');
        $providers = [];

        if (!File::isDirectory($modulesPath))
            return [];

        foreach (File::directories($modulesPath) as $modulePath) {
            $moduleJsonPath = $modulePath . '/module.json';

            if (File::exists($moduleJsonPath)) {
                $json = json_decode(File::get($moduleJsonPath), true);

                if (isset($json['provider']) && class_exists($json['provider'])) {
                    $providers[] = $json['provider'];
                }
            }
        }

        return $providers;
    }
}
// This code is used to register all module service providers defined in the module.json files of each module.