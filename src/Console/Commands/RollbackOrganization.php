<?php

namespace Kesify\MicroserviceSkeleton\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Kesify\MicroserviceSkeleton\Models\Organization;
use Kesify\MicroserviceSkeleton\Services\OrganizationService;

class RollbackOrganization extends Command
{
    protected $signature = 'organization:rollback
                        {--organizationId= : The ID of the organization to rollback}
                        {--database= : The database name of the organization to rollback}
                        {--batch= : The number of batches to rollback}';

    protected $description = 'Rollback migrations for a specific organization or database';

    public function handle()
    {
        $organizationId = $this->option('organizationId');
        $databaseName = $this->option('database');
        $batch = $this->option('batch');
        $organizationService = new OrganizationService();

        if ($databaseName) {
            $this->rollbackDatabase($databaseName, $organizationService,$batch);
        } else {
            $this->rollbackOrganizations($organizationId, $organizationService,$batch);
        }
    }

    protected function rollbackDatabase($databaseName, $organizationService, $batch): void
    {
        try {
            $organizationService->setOrganizationDatabase($databaseName);
            $options = ['--database' => 'organization'];
            if (!is_null($batch)) {
                $options['--step'] = $batch;
            }
            Artisan::call('migrate:rollback', $options);
            $this->info("Migration rollback completed for Database: {$databaseName}.");
        } catch (\Exception $e) {
            $this->error("Migration rollback failed for Database: {$databaseName}. Check the logs for details.");
        }
    }

    protected function rollbackOrganizations($organizationId, $organizationService, $batch): void
    {
        $query = Organization::query();

        if ($organizationId) {
            $query->where('id', $organizationId);
        }

        $organizations = $query->get();

        foreach ($organizations as $organization) {
            try {
                $this->info('Rolling back migrations for Organization ID: ' . $organization->id . ' Database: ' . $organization->database);
                $organizationService->setOrganizationDatabase($organization->database);
                $options = ['--database' => 'organization'];
                if (!is_null($batch)) {
                    $options['--step'] = $batch;
                }
                Artisan::call('migrate:rollback', $options);
                $this->info("Migration rollback completed for Organization ID: {$organization->id}.");
            } catch (\Exception $e) {
                $this->error("Migration rollback failed for Organization ID: {$organization->id}. Check the logs for details.");
                continue;
            }
        }

        if ($organizations->isEmpty()) {
            $this->info('No matching organizations found for rollback.');
        }
    }
}
