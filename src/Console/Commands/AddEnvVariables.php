<?php

namespace Kesify\MicroserviceSkeleton\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AddEnvVariables extends Command
{
    /**
     * Der Name und die Beschreibung des Commands.
     *
     * @var string
     */
    protected $signature = 'kesify:add-env-variables';

    protected $description = 'Add required environment variables to the .env file';

    public function handle(): void
    {
        $envPath = dirname(__DIR__, 3) . '/.env';
        $envExamplePath = dirname(__DIR__, 3) . '/.env.example';

        // Definiere die Variablen, die hinzugefügt werden sollen
        $variables = [
            'ORGANIZATION_DB_HOST' => '127.0.0.1',
            'ORGANIZATION_DB_PORT' => '3306',
            'ORGANIZATION_DB_NAME' => 'organization_db',
            'ORGANIZATION_DB_USERNAME' => 'root',
            'ORGANIZATION_DB_PASSWORD' => '',
        ];

        // Füge die Variablen zur .env hinzu
        foreach ($variables as $key => $value) {
            $this->addToEnvFile($key, $value, $envPath);
        }

        // Optional: Füge die Variablen zur .env.example hinzu
        foreach ($variables as $key => $value) {
            $this->addToEnvFile($key, $value, $envExamplePath);
        }

        $this->info('Environment variables have been added to .env and .env.example.');
    }

    private function addToEnvFile($key, $value, $path): void
    {
        if (File::exists($path)) {
            $envContent = File::get($path);

            if (!str_contains($envContent, "$key=")) {
                File::append($path, "\n$key=$value");
            }
        }
    }
}
