<?php

namespace Kesify\MicroserviceSkeleton\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class OrganizationService
{
    public function getOrganization($organizationId = null)
    {
        $organizationId = $organizationId ?? $this->organizationId;
        if($organizationId){
            return Organization::findOrFail($organizationId);
        }
        return null;
    }

    public function setOrganizationDatabase($db): ?true
    {
        if($db){
            \App\Services\config(['database.connections.organization.database' => $db]);
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

    public function migrateOrganizationByOrganizationId($organizationId = null): ?int
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

    public function migrateOrganizationByDatabase($database = null): ?int
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

    public function seedOrganizationByOrganizationId($organizationId = null): ?int
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

    public function seedOrganizationByDatabase($database = null): ?int
    {
        if($database && class_exists('Database\Seeders\OrganizationDatabaseSeeder')){
            $this->setOrganizationDatabase($database);
            return Artisan::call('db:seed',[
                '--class'=>'Database\Seeders\OrganizationDatabaseSeeder',
            ]);
        }
        return null;
    }

    public function switchOrganization($organizationId = null): ?array
    {
        $organizationId = $organizationId ?? $this->organizationId;
        $token = \App\Services\config('session.token');
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
