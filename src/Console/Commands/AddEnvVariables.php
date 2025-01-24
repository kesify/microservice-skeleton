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
    protected $signature = 'ms:add-env';

    protected $description = 'Add required environment variables to the .env file';

    public function handle(): void
    {
        $envPath = dirname(__DIR__, 6) . '/.env';
        $envExamplePath = dirname(__DIR__, 6) . '/.env.example';

        // Definiere die Variablen, die hinzugefügt werden sollen
        $variables = [
            'ORGANIZATION_DB_HOST' => 'db',
            'ORGANIZATION_DB_PORT' => '3306',
            'ORGANIZATION_DB_NAME' => 'organization_db',
            'ORGANIZATION_DB_USERNAME' => 'root',
            'ORGANIZATION_DB_PASSWORD' => '',
        ];

        // Füge die Variablen zur .env hinzu
        if(file_exists($envExamplePath)){
            foreach ($variables as $key => $value) {
                $this->addToEnvFile($key, $value, $envPath);
            }
            $this->info('Environment variables have been added to .env.');
        }else{
           $this->error('.env file does not exist.');
        }

        // Optional: Füge die Variablen zur .env.example hinzu
        if(file_exists($envExamplePath)){
            foreach ($variables as $key => $value) {
                $this->addToEnvFile($key, $value, $envExamplePath);
            }
            $this->info('Environment variables have been added to .env.example.');
        }else{
            $this->warn('.env-example file does not exist.');
        }
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
