<?php

namespace Kesify\MicroserviceSkeleton\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Kesify\MicroserviceSkeleton\Models\Organization;
use Kesify\MicroserviceSkeleton\Models\OrganizationUser;

class OrganizationService
{
    public function getOrganization(?string $organizationId = null): ?Organization
    {
        $organization = App::get('organization') ?? null;

        if($organizationId){
            return Organization::findOrFail($organizationId);
        }else if(!$organization){
            return $organization;
        }

        return null;
    }

    public function setOrganizationDatabase(string $db): ?true
    {
        if($db){
            Config::set(['database.connections.organization.database' => $db]);
            DB::purge('organization'); // Purges the current connection
            DB::reconnect('organization'); // Reconnects using the modified config
            DB::connection('organization')->setPdo(null);
            DB::setDatabaseName($db);
            return true;
        }
        return null;
    }

    public function migrateAllOrganizations(): void
    {
        $organizations = Organization::all();
        foreach($organizations as $organization){
            $this->migrateOrganizationByDatabase($organization['database']);
        }
    }

    public function seedAllOrganizations(): void
    {
        $organizations = Organization::all();
        foreach($organizations as $organization){
            $this->seedOrganizationByDatabase($organization['database']);
        }
    }

    public function migrateOrganizationByOrganizationId(string $organizationId): ?int
    {
        if($organizationId){
            $organization = $this->getOrganization($organizationId);
            $this->setOrganizationDatabase($organization['database']);
            return Artisan::call('migrate',[
                '--path'=>'/database/migrations/organization',
                '--database'=>'organization',
            ]);
        }

        return null;
    }

    public function migrateOrganizationByDatabase(string $database): ?int
    {
        if($database){
            $this->setOrganizationDatabase($database);
            return Artisan::call('migrate',[
                '--path'=>'/database/migrations/organization',
                '--database'=>'organization',
            ]);
        }
        return null;
    }

    public function seedOrganizationByOrganizationId(string $organizationId): ?int
    {
        if($organizationId && class_exists('Database\Seeders\OrganizationDatabaseSeeder')){
            $organization = $this->getOrganization($organizationId);
            $this->setOrganizationDatabase($organization['database']);
            return Artisan::call('db:seed',[
                '--class'=>'Database\Seeders\OrganizationDatabaseSeeder',
            ]);
        }
        return null;
    }

    public function seedOrganizationByDatabase(string $database): ?int
    {
        if($database && class_exists('Database\Seeders\OrganizationDatabaseSeeder')){
            $this->setOrganizationDatabase($database);
            return Artisan::call('db:seed',[
                '--class'=>'Database\Seeders\OrganizationDatabaseSeeder',
            ]);
        }
        return null;
    }

    public function switchOrganization(string $organizationId): ?array
    {
        $token = Config::get('session.token');
        $organization = $this->getOrganization($organizationId);

        if($token && $organization){
            $key = "at_{$token}_organization";
            $value = json_decode(Redis::get($key),true);
            $organizationUser = OrganizationUser::where(['organization_id'=>$organizationId,'user_id'=>$value['user_id']])->first();
            $ttl = Redis::ttl($key);
            Redis::set($key,json_encode($organizationUser),'EX',$ttl);

            return [
                'organization' => $organization,
                'ttl' => $ttl,
            ];
        }

        return null;
    }
}
