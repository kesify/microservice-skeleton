<?php

namespace Kesify\MicroserviceSkeleton\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;
use Kesify\MicroserviceSkeleton\Models\Organization;
use Kesify\MicroserviceSkeleton\Services\OrganizationService;

class SeedOrganization extends Command
{
    protected $signature = 'organization:seed
                            {--organizationId= : The ID of the organization to seed}
                            {--database= : The database name of the organization to seed}
                            {--class=Database\\Seeders\\OrganizationDatabaseSeeder : The seeder class to use}';

    protected $description = 'Seed database for a specific organization or database';

    public function handle(): void
    {
        $organizationId = $this->option('organizationId');
        $databaseName = $this->option('database');
        $seederClass = $this->option('class');
        $organizationService = new OrganizationService();

        if ($databaseName) {
            $this->seedDatabase($databaseName, $organizationService, $seederClass);
        } else {
            $this->seedOrganizations($organizationId, $organizationService, $seederClass);
        }
    }

    /**
     * Seed a specific database.
     *
     * @param string $databaseName
     * @param OrganizationService $organizationService
     * @param string $seederClass The seeder class to use
     */
    protected function seedDatabase(string $databaseName, OrganizationService $organizationService, string $seederClass): void
    {
        try {
            $organizationService->setOrganizationDatabase($databaseName);
            Artisan::call('db:seed', [
                '--class' => $seederClass
            ]);
            $this->info("Database seeding completed for Database: {$databaseName}.");
        } catch (\Exception $e) {
            $this->error("Database seeding failed for Database: {$databaseName}. Error: {$e->getMessage()}");
        }
    }

    /**
     * Seed databases for one or all organizations.
     *
     * @param string|null $organizationId
     * @param OrganizationService $organizationService
     * @param string $seederClass The seeder class to use
     */
    protected function seedOrganizations(?string $organizationId, OrganizationService $organizationService, string $seederClass): void
    {
        $query = Organization::query();

        if ($organizationId) {
            $query->where('id', $organizationId);
        }

        $organizations = $query->get();

        foreach ($organizations as $organization) {
            try {
                $this->info('Seeding database for Organization ID: ' . $organization->id . ' Database: ' . $organization->database);
                $organizationService->setOrganizationDatabase($organization->database);
                Redis::set("organization_seed_data", json_encode(['organization_id'=>$organization->id]));
                Artisan::call('db:seed', [
                    '--class' => $seederClass
                ]);
                Redis::del("organization_seed_data");
                $this->info("Database seeding completed for Organization ID: {$organization->id}.");
            } catch (\Exception $e) {
                $this->error("Database seeding failed for Organization ID: {$organization->id}. Error: {$e->getMessage()}");
                continue;
            }
        }

        if ($organizations->isEmpty()) {
            $this->info('No matching organizations found for seeding.');
        }
    }
}
