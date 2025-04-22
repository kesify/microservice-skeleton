<?php

namespace Kesify\MicroserviceSkeleton\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Kesify\MicroserviceSkeleton\Models\Organization;
use Kesify\MicroserviceSkeleton\Services\OrganizationService;

class MigrateOrganization extends Command
{
    protected $signature = 'organization:migrate
                        {--organizationId= : The ID of the organization to migrate}
                        {--database= : The database name of the organization to migrate}
                        {--rollback : Rollback the last migration for the organization}
                        {--with-output : Show output of migration command}';

    protected $description = 'Run or rollback migrations for specific organizations or all organizations if no options are provided.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $organizationId = $this->option('organizationId');
        $databaseName = $this->option('database');
        $isRollback = $this->option('rollback');
        $showOutput = $this->option('with-output');
        $organizationService = new OrganizationService();

        if ($databaseName) {
            $this->processDatabase($databaseName, $organizationService, $isRollback, $showOutput);
        } else {
            $this->processOrganizations($organizationId, $organizationService, $isRollback, $showOutput);
        }
    }

    private function processDatabase(string $databaseName, OrganizationService $organizationService, bool $isRollback, bool $showOutput): void
    {
        try {
            $organizationService->setOrganizationDatabase($databaseName);
            $this->runMigrationCommand($databaseName, $isRollback, null, $showOutput);
        } catch (\Exception $e) {
            $this->error("Operation failed for Database: {$databaseName}. Error: " . $e->getMessage());
        }
    }

    private function processOrganizations(?string $organizationId, OrganizationService $organizationService, bool $isRollback, bool $showOutput): void
    {
        $query = Organization::query();

        if ($organizationId) {
            $query->where('id', $organizationId);
        }

        $organizations = $query->get();

        if ($organizations->isEmpty()) {
            $this->info('No matching organizations found for the operation.');
            return;
        }

        foreach ($organizations as $organization) {
            try {
                $organizationService->setOrganizationDatabase($organization->database);
                $this->runMigrationCommand($organization->database, $isRollback, $organization->id, $showOutput);
            } catch (\Exception $e) {
                $this->error("Operation failed for Organization ID: {$organization->id}. Error: " . $e->getMessage());
            }
        }
    }

    private function runMigrationCommand(string $databaseName, bool $isRollback, ?string $organizationId = null, bool $showOutput = false): void
    {
        $operation = $isRollback ? 'migrate:rollback' : 'migrate';
        $path = '/database/migrations/organization';

        Artisan::call($operation, [
            '--path' => $path,
            '--database' => 'organization',
        ]);
        $output = Artisan::output();

        if ($showOutput) {
            $this->line("Artisan Output:\n" . $output);
        }

        $message = $isRollback
            ? "Rollback completed"
            : "Migration completed";

        if ($organizationId) {
            $this->info("{$message} for Organization ID: {$organizationId} (Database: {$databaseName}).");
        } else {
            $this->info("{$message} for Database: {$databaseName}.");
        }

        Log::channel('emergency')->info("Output for {$databaseName}:\n" . $output);
    }
}
